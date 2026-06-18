<?php

namespace AgoLab\Access;

defined( 'ABSPATH' ) || exit;

class Plugin {

    private static ?self $instance = null;

    public static function instance(): self {
        if ( null === self::$instance ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init', [ $this, 'load_textdomain' ] );
        add_action( 'admin_menu', [ $this, 'admin_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_assets' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'frontend_assets' ] );
        add_action( 'wp_footer', [ $this, 'render_toolbar' ], 99 );
        add_action( 'wp_head', [ $this, 'auto_fixes_head' ], 1 );
        add_action( 'wp_footer', [ $this, 'auto_fixes_footer' ], 1 );
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function load_textdomain(): void {
        load_plugin_textdomain( 'ago-access', false, dirname( plugin_basename( AGOACCESS_FILE ) ) . '/languages' );
    }

    public function admin_menu(): void {
        if ( empty( $GLOBALS['admin_page_hooks']['agolab-tools'] ) ) {
            add_menu_page( __( 'aGo Tools', 'ago-access' ), __( 'aGo Tools', 'ago-access' ), 'manage_options', 'agolab-tools', '__return_null', 'dashicons-hammer', 81 );
        }
        add_submenu_page( 'agolab-tools', __( 'aGo Access', 'ago-access' ), __( 'Accessibility', 'ago-access' ), 'manage_options', 'agoaccess', [ Admin\Settings::class, 'render' ] );
        remove_submenu_page( 'agolab-tools', 'agolab-tools' );
    }

    public function admin_assets( string $hook ): void {
        if ( ! str_ends_with( $hook, '_page_agoaccess' ) ) return;
        wp_enqueue_style( 'agoaccess-admin', AGOACCESS_URL . 'assets/css/admin.css', [], AGOACCESS_VERSION );
        wp_enqueue_script( 'agoaccess-admin', AGOACCESS_URL . 'assets/js/admin.js', [], AGOACCESS_VERSION, true );
        wp_localize_script( 'agoaccess-admin', 'agoaccessAdmin', [
            'restUrl' => rest_url( 'ago-access/v1' ),
            'nonce'   => wp_create_nonce( 'wp_rest' ),
        ] );
    }

    public function frontend_assets(): void {
        $s = self::get_settings();
        if ( is_admin() ) return;

        $fixes = $s['auto_fixes'] ?? self::default_fixes();

        if ( ! empty( $fixes['focus_visible'] ) || ! empty( $fixes['tab_order'] ) || ! empty( $fixes['skip_link'] ) ) {
            wp_register_style( 'agoaccess-fixes', false, [], AGOACCESS_VERSION );
            wp_enqueue_style( 'agoaccess-fixes' );
            if ( ! empty( $fixes['skip_link'] ) ) {
                wp_add_inline_style( 'agoaccess-fixes', '.ago-skip-link{position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;z-index:99999}.ago-skip-link:focus{position:fixed;left:0;top:0;width:auto;height:auto;overflow:visible;padding:8px 16px;background:#1d2327;color:#fff;font-size:14px;text-decoration:none;border-radius:0 0 4px 0}' );
            }
            if ( ! empty( $fixes['focus_visible'] ) ) {
                wp_add_inline_style( 'agoaccess-fixes', ':focus-visible{outline:3px solid #2271b1 !important;outline-offset:2px !important;}' );
            }
            if ( ! empty( $fixes['tab_order'] ) ) {
                wp_register_script( 'agoaccess-fixes', false, [], AGOACCESS_VERSION, true );
                wp_enqueue_script( 'agoaccess-fixes' );
                wp_add_inline_script( 'agoaccess-fixes', 'document.querySelectorAll("[tabindex]").forEach(function(el){var t=parseInt(el.getAttribute("tabindex"),10);if(t>0)el.setAttribute("tabindex","0");});' );
            }
        }

        if ( empty( $s['enabled'] ) ) return;
        wp_enqueue_style( 'agoaccess-toolbar', AGOACCESS_URL . 'assets/css/toolbar.css', [], AGOACCESS_VERSION );
        wp_enqueue_script( 'agoaccess-toolbar', AGOACCESS_URL . 'assets/js/toolbar.js', [], AGOACCESS_VERSION, true );
        wp_localize_script( 'agoaccess-toolbar', 'agoaccessCfg', [
            'position' => $s['position'] ?? 'bottom-right',
            'shape'    => $s['shape'] ?? 'circle',
            'icon'     => $s['icon'] ?? 'person',
            'color'    => $s['color'] ?? '#2271b1',
            'tools'    => $s['tools'] ?? self::default_tools(),
            'i18n'     => [
                'title'            => __( 'Accessibility', 'ago-access' ),
                'close'            => __( 'Close', 'ago-access' ),
                'reset'            => __( 'Reset All', 'ago-access' ),
                'fontSize'         => __( 'Text Size', 'ago-access' ),
                'contrast'         => __( 'Contrast', 'ago-access' ),
                'dyslexiaFont'     => __( 'Dyslexia Friendly', 'ago-access' ),
                'spacing'          => __( 'Text Spacing', 'ago-access' ),
                'bigCursor'        => __( 'Big Cursor', 'ago-access' ),
                'readingGuide'     => __( 'Reading Guide', 'ago-access' ),
                'highlightLinks'   => __( 'Highlight Links', 'ago-access' ),
                'stopAnimations'   => __( 'Stop Animations', 'ago-access' ),
                'grayscale'        => __( 'Grayscale', 'ago-access' ),
                'hideImages'       => __( 'Hide Images', 'ago-access' ),
                'textToSpeech'     => __( 'Read Aloud', 'ago-access' ),
                'contrastNormal'   => __( 'Normal', 'ago-access' ),
                'contrastHigh'     => __( 'High', 'ago-access' ),
                'contrastInverted' => __( 'Inverted', 'ago-access' ),
                'contrastDark'     => __( 'Dark', 'ago-access' ),
                'ttsPlay'          => __( 'Select text, then click to read aloud', 'ago-access' ),
                'ttsStop'          => __( 'Stop reading', 'ago-access' ),
            ],
        ] );
    }

    public function render_toolbar(): void {
        $s = self::get_settings();
        if ( empty( $s['enabled'] ) ) return;
        if ( is_admin() ) return;
        echo '<div id="ago-access-toolbar"></div>';
    }

    /* ───── Auto Fixes ───── */

    public function auto_fixes_head(): void {
        $s = self::get_settings();
        $fixes = $s['auto_fixes'] ?? self::default_fixes();

        // Lang attribute
        if ( ! empty( $fixes['lang_attr'] ) ) {
            add_filter( 'language_attributes', function( $output ) {
                if ( strpos( $output, 'lang=' ) === false ) {
                    $output .= ' lang="' . esc_attr( get_bloginfo( 'language' ) ) . '"';
                }
                return $output;
            } );
        }
    }

    public function auto_fixes_footer(): void {
        $s = self::get_settings();
        $fixes = $s['auto_fixes'] ?? self::default_fixes();

        if ( ! empty( $fixes['skip_link'] ) ) {
            printf(
                '<a href="#content" class="ago-skip-link">%s</a>',
                esc_html__( 'Skip to content', 'ago-access' )
            );
        }
    }

    /* ───── REST API ───── */

    public function register_routes(): void {
        $ns = 'ago-access/v1';
        register_rest_route( $ns, '/settings', [
            [ 'methods' => 'GET', 'callback' => [ $this, 'rest_get_settings' ], 'permission_callback' => [ $this, 'can_manage' ] ],
            [ 'methods' => 'POST', 'callback' => [ $this, 'rest_save_settings' ], 'permission_callback' => [ $this, 'can_manage' ] ],
        ] );
        register_rest_route( $ns, '/scan', [
            'methods' => 'GET', 'callback' => [ $this, 'rest_scan' ], 'permission_callback' => [ $this, 'can_manage' ],
        ] );
    }

    public function can_manage(): bool { return current_user_can( 'manage_options' ); }

    public function rest_get_settings(): \WP_REST_Response {
        return new \WP_REST_Response( [ 'settings' => self::get_settings() ] );
    }

    public function rest_save_settings( \WP_REST_Request $request ): \WP_REST_Response {
        $data = $request->get_json_params();
        update_option( 'agoaccess_settings', self::sanitize( $data ) );
        return new \WP_REST_Response( [ 'saved' => true ] );
    }

    public function rest_scan(): \WP_REST_Response {
        return new \WP_REST_Response( Scanner::scan() );
    }

    /* ───── Settings ───── */

    public static function get_settings(): array {
        $defaults = [
            'enabled'    => true,
            'position'   => 'bottom-right',
            'shape'      => 'circle',
            'icon'       => 'person',
            'color'      => '#2271b1',
            'tools'      => self::default_tools(),
            'auto_fixes' => self::default_fixes(),
        ];
        return wp_parse_args( get_option( 'agoaccess_settings', [] ), $defaults );
    }

    public static function default_tools(): array {
        return [
            'fontSize'       => true,
            'contrast'       => true,
            'dyslexiaFont'   => true,
            'spacing'        => true,
            'bigCursor'      => true,
            'readingGuide'   => true,
            'highlightLinks' => true,
            'stopAnimations' => true,
            'grayscale'      => true,
            'hideImages'     => true,
            'textToSpeech'   => true,
        ];
    }

    public static function default_fixes(): array {
        return [
            'skip_link'      => true,
            'focus_visible'  => true,
            'aria_landmarks' => true,
            'lang_attr'      => true,
            'form_labels'    => true,
            'alt_check'      => true,
            'tab_order'      => true,
        ];
    }

    private static function sanitize( array $d ): array {
        $clean = [];
        $clean['enabled']  = ! empty( $d['enabled'] );
        $clean['position'] = in_array( $d['position'] ?? '', [ 'top-left', 'top-right', 'middle-left', 'middle-right', 'bottom-left', 'bottom-right' ], true ) ? $d['position'] : 'bottom-right';
        $clean['shape']    = in_array( $d['shape'] ?? '', [ 'circle', 'rounded' ], true ) ? $d['shape'] : 'circle';
        $clean['icon']     = in_array( $d['icon'] ?? '', [ 'person', 'wheelchair', 'eye', 'universal' ], true ) ? $d['icon'] : 'person';
        $clean['color']    = sanitize_hex_color( $d['color'] ?? '#2271b1' ) ?: '#2271b1';

        $clean['tools'] = [];
        $allowed_tools = array_keys( self::default_tools() );
        foreach ( $allowed_tools as $tool ) {
            $clean['tools'][ $tool ] = ! empty( $d['tools'][ $tool ] );
        }

        $clean['auto_fixes'] = [];
        $allowed_fixes = array_keys( self::default_fixes() );
        foreach ( $allowed_fixes as $fix ) {
            $clean['auto_fixes'][ $fix ] = ! empty( $d['auto_fixes'][ $fix ] );
        }

        return $clean;
    }
}
