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
        $modir = WP_LANG_DIR . '/plugins/';
        foreach ( [ 'es_ES', 'pt_BR' ] as $loc ) {
            $src = AGO_ACCESS_PATH . "languages/ago-access-{$loc}.l10n.php";
            $dest = $modir . "ago-access-{$loc}.l10n.php";
            if ( file_exists( $src ) && ! file_exists( $dest ) ) @copy( $src, $dest );
        }
        load_plugin_textdomain( 'ago-access', false, dirname( plugin_basename( AGO_ACCESS_FILE ) ) . '/languages' );
    }

    public function admin_menu(): void {
        if ( empty( $GLOBALS['admin_page_hooks']['ago-tools'] ) ) {
            add_menu_page( __( 'aGo Tools', 'ago-access' ), __( 'aGo Tools', 'ago-access' ), 'manage_options', 'ago-tools', '__return_null', 'dashicons-hammer', 81 );
        }
        add_submenu_page( 'ago-tools', __( 'aGo Access', 'ago-access' ), __( 'Accessibility', 'ago-access' ), 'manage_options', 'ago-access', [ Admin\Settings::class, 'render' ] );
        remove_submenu_page( 'ago-tools', 'ago-tools' );
    }

    public function admin_assets( string $hook ): void {
        if ( ! str_ends_with( $hook, '_page_ago-access' ) ) return;
        wp_enqueue_style( 'ago-access-admin', AGO_ACCESS_URL . 'assets/css/admin.css', [], AGO_ACCESS_VERSION );
        wp_enqueue_script( 'ago-access-admin', AGO_ACCESS_URL . 'assets/js/admin.js', [], AGO_ACCESS_VERSION, true );
        wp_localize_script( 'ago-access-admin', 'agoAccess', [
            'restUrl' => rest_url( 'ago-access/v1' ),
            'nonce'   => wp_create_nonce( 'wp_rest' ),
        ] );
    }

    public function frontend_assets(): void {
        $s = self::get_settings();
        if ( empty( $s['enabled'] ) ) return;
        wp_enqueue_style( 'ago-access-toolbar', AGO_ACCESS_URL . 'assets/css/toolbar.css', [], AGO_ACCESS_VERSION );
        wp_enqueue_script( 'ago-access-toolbar', AGO_ACCESS_URL . 'assets/js/toolbar.js', [], AGO_ACCESS_VERSION, true );
        wp_localize_script( 'ago-access-toolbar', 'agoAccessCfg', [
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

        // Skip link
        if ( ! empty( $fixes['skip_link'] ) ) {
            echo '<a href="#content" class="ago-skip-link" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;z-index:99999;padding:8px 16px;background:#1d2327;color:#fff;font-size:14px;text-decoration:none;border-radius:0 0 4px 0" onfocus="this.style.cssText=\'position:fixed;left:0;top:0;width:auto;height:auto;overflow:visible;z-index:99999;padding:8px 16px;background:#1d2327;color:#fff;font-size:14px;text-decoration:none;border-radius:0 0 4px 0\'" onblur="this.style.cssText=\'position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden\'">' . esc_html__( 'Skip to content', 'ago-access' ) . '</a>';
        }

        // Focus visible
        if ( ! empty( $fixes['focus_visible'] ) ) {
            echo '<style>:focus-visible{outline:3px solid #2271b1!important;outline-offset:2px!important;}</style>';
        }

        // Tab order fix (remove positive tabindex)
        if ( ! empty( $fixes['tab_order'] ) ) {
            echo '<script>document.querySelectorAll("[tabindex]").forEach(function(el){var t=parseInt(el.getAttribute("tabindex"),10);if(t>0)el.setAttribute("tabindex","0");});</script>';
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
        update_option( 'ago_access_settings', self::sanitize( $data ) );
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
        return wp_parse_args( get_option( 'ago_access_settings', [] ), $defaults );
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
