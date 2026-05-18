<?php

namespace AgoLab\Access\Admin;

use AgoLab\Access\Plugin;

defined( 'ABSPATH' ) || exit;

class Settings {

    public static function render(): void {
        $settings = Plugin::get_settings();
        $tools = $settings['tools'] ?? Plugin::default_tools();
        $fixes = $settings['auto_fixes'] ?? Plugin::default_fixes();
        ?>
        <div class="wrap">
            <h1>
                <img src="<?php echo esc_url( AGO_ACCESS_URL . 'assets/img/agolab.webp' ); ?>" alt="aGo Lab" style="height:28px;width:auto;vertical-align:middle;margin-right:8px">
                <?php esc_html_e( 'aGo Access', 'ago-access' ); ?>
                <span style="font-size:12px;color:#999;margin-left:8px">v<?php echo esc_html( AGO_ACCESS_VERSION ); ?></span>
            </h1>

            <div class="ago-layout">
                <div class="ago-main">

                    <!-- Score -->
                    <div class="card ago-card">
                        <h2><?php esc_html_e( 'Accessibility Score', 'ago-access' ); ?></h2>
                        <p style="font-size:13px;color:#666"><?php esc_html_e( 'Scan your homepage for common accessibility issues.', 'ago-access' ); ?></p>
                        <button id="ago-scan-btn" class="button button-primary"><?php esc_html_e( 'Scan Now', 'ago-access' ); ?></button>
                        <div id="ago-scan-results" style="margin-top:16px"></div>
                        <p style="font-size:11.5px;color:#777;margin-top:14px;padding:8px 10px;background:#f6f7f7;border-left:3px solid #c3c4c7;border-radius:0 3px 3px 0;line-height:1.5">
                            <strong><?php esc_html_e( 'Note:', 'ago-access' ); ?></strong>
                            <?php esc_html_e( 'Heuristic scan based on WCAG 2.1 (A/AA) on your homepage. Runs locally on your server, sends no data to third parties. It does not replace a manual audit with a screen reader.', 'ago-access' ); ?>
                        </p>
                    </div>

                    <!-- Toolbar -->
                    <div class="card ago-card">
                        <h2><?php esc_html_e( 'Accessibility Toolbar', 'ago-access' ); ?></h2>
                        <p style="font-size:13px;color:#666"><?php esc_html_e( 'A floating toolbar on your frontend that lets visitors adjust the experience to their needs.', 'ago-access' ); ?></p>
                        <table class="form-table">
                            <tr>
                                <th><?php esc_html_e( 'Enable Toolbar', 'ago-access' ); ?></th>
                                <td><label><input type="checkbox" id="ago-enabled" <?php checked( ! empty( $settings['enabled'] ) ); ?>> <?php esc_html_e( 'Show accessibility toolbar on frontend', 'ago-access' ); ?></label></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Position', 'ago-access' ); ?></th>
                                <td>
                                    <select id="ago-position">
                                        <option value="top-left" <?php selected( $settings['position'], 'top-left' ); ?>><?php esc_html_e( 'Top Left', 'ago-access' ); ?></option>
                                        <option value="top-right" <?php selected( $settings['position'], 'top-right' ); ?>><?php esc_html_e( 'Top Right', 'ago-access' ); ?></option>
                                        <option value="middle-left" <?php selected( $settings['position'], 'middle-left' ); ?>><?php esc_html_e( 'Middle Left', 'ago-access' ); ?></option>
                                        <option value="middle-right" <?php selected( $settings['position'], 'middle-right' ); ?>><?php esc_html_e( 'Middle Right', 'ago-access' ); ?></option>
                                        <option value="bottom-left" <?php selected( $settings['position'], 'bottom-left' ); ?>><?php esc_html_e( 'Bottom Left', 'ago-access' ); ?></option>
                                        <option value="bottom-right" <?php selected( $settings['position'], 'bottom-right' ); ?>><?php esc_html_e( 'Bottom Right', 'ago-access' ); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Color', 'ago-access' ); ?></th>
                                <td><input type="color" id="ago-color" value="<?php echo esc_attr( $settings['color'] ); ?>"></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Button Shape', 'ago-access' ); ?></th>
                                <td>
                                    <select id="ago-shape">
                                        <option value="circle" <?php selected( $settings['shape'] ?? 'circle', 'circle' ); ?>><?php esc_html_e( 'Circle', 'ago-access' ); ?></option>
                                        <option value="rounded" <?php selected( $settings['shape'] ?? '', 'rounded' ); ?>><?php esc_html_e( 'Rounded Rectangle', 'ago-access' ); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Button Icon', 'ago-access' ); ?></th>
                                <td>
                                    <select id="ago-icon">
                                        <option value="person" <?php selected( $settings['icon'] ?? 'person', 'person' ); ?>><?php esc_html_e( 'Person (accessibility symbol)', 'ago-access' ); ?></option>
                                        <option value="wheelchair" <?php selected( $settings['icon'] ?? '', 'wheelchair' ); ?>><?php esc_html_e( 'Wheelchair', 'ago-access' ); ?></option>
                                        <option value="eye" <?php selected( $settings['icon'] ?? '', 'eye' ); ?>><?php esc_html_e( 'Eye', 'ago-access' ); ?></option>
                                        <option value="universal" <?php selected( $settings['icon'] ?? '', 'universal' ); ?>><?php esc_html_e( 'Universal Access', 'ago-access' ); ?></option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        <h3 style="margin-top:16px"><?php esc_html_e( 'Toolbar Tools', 'ago-access' ); ?></h3>
                        <p style="font-size:13px;color:#666"><?php esc_html_e( 'Enable or disable individual accessibility tools in the toolbar.', 'ago-access' ); ?></p>
                        <table class="wp-list-table widefat striped">
                            <thead><tr><th><?php esc_html_e( 'Tool', 'ago-access' ); ?></th><th style="width:60px"><?php esc_html_e( 'Enabled', 'ago-access' ); ?></th></tr></thead>
                            <tbody>
                            <?php
                            $tool_labels = [
                                'fontSize'       => __( 'Text Size (increase/decrease)', 'ago-access' ),
                                'contrast'       => __( 'Contrast Modes (high, inverted, dark)', 'ago-access' ),
                                'dyslexiaFont'   => __( 'Dyslexia-Friendly Font', 'ago-access' ),
                                'spacing'        => __( 'Text Spacing (line height, letter spacing)', 'ago-access' ),
                                'bigCursor'      => __( 'Big Cursor', 'ago-access' ),
                                'readingGuide'   => __( 'Reading Guide (horizontal line follows mouse)', 'ago-access' ),
                                'highlightLinks' => __( 'Highlight Links', 'ago-access' ),
                                'stopAnimations' => __( 'Stop Animations (GIFs, videos, CSS)', 'ago-access' ),
                                'grayscale'      => __( 'Grayscale Mode', 'ago-access' ),
                                'hideImages'     => __( 'Hide Images', 'ago-access' ),
                                'textToSpeech'   => __( 'Read Aloud (Web Speech API)', 'ago-access' ),
                            ];
                            foreach ( $tool_labels as $key => $label ) : ?>
                                <tr><td><?php echo esc_html( $label ); ?></td><td><input type="checkbox" data-tool="<?php echo esc_attr( $key ); ?>" <?php checked( ! empty( $tools[ $key ] ) ); ?>></td></tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Auto Fixes -->
                    <div class="card ago-card">
                        <h2><?php esc_html_e( 'Automatic Fixes', 'ago-access' ); ?></h2>
                        <p style="font-size:13px;color:#666"><?php esc_html_e( 'Background fixes applied automatically to improve accessibility without any visible change.', 'ago-access' ); ?></p>
                        <table class="wp-list-table widefat striped">
                            <thead><tr><th><?php esc_html_e( 'Fix', 'ago-access' ); ?></th><th style="width:60px"><?php esc_html_e( 'Enabled', 'ago-access' ); ?></th></tr></thead>
                            <tbody>
                            <?php
                            $fix_labels = [
                                'skip_link'      => __( 'Skip-to-Content Link (keyboard navigation)', 'ago-access' ),
                                'focus_visible'   => __( 'Focus Visible Outline (keyboard users)', 'ago-access' ),
                                'aria_landmarks' => __( 'ARIA Landmarks (screen readers)', 'ago-access' ),
                                'lang_attr'      => __( 'Language Attribute on <html>', 'ago-access' ),
                                'form_labels'    => __( 'Form Labels Check', 'ago-access' ),
                                'alt_check'      => __( 'Alt Text Warning in Admin', 'ago-access' ),
                                'tab_order'      => __( 'Fix Positive Tabindex (tab order)', 'ago-access' ),
                            ];
                            foreach ( $fix_labels as $key => $label ) : ?>
                                <tr><td><?php echo esc_html( $label ); ?></td><td><input type="checkbox" data-fix="<?php echo esc_attr( $key ); ?>" <?php checked( ! empty( $fixes[ $key ] ) ); ?>></td></tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <button id="ago-save-settings" class="button button-primary button-hero"><?php esc_html_e( 'Save Settings', 'ago-access' ); ?></button>
                    <div id="ago-status" style="display:none"></div>

                </div>

                <!-- Sidebar -->
                <div class="ago-sidebar">
                    <div class="card ago-card">
                        <h3><?php esc_html_e( 'About', 'ago-access' ); ?></h3>
                        <p style="font-size:13px;color:#666"><?php esc_html_e( 'Accessibility toolbar and automatic fixes for WordPress. WCAG 2.2 oriented, 100% free, no limitations.', 'ago-access' ); ?></p>
                        <ul class="ago-features">
                            <li><?php esc_html_e( '11 accessibility tools', 'ago-access' ); ?></li>
                            <li><?php esc_html_e( '7 automatic background fixes', 'ago-access' ); ?></li>
                            <li><?php esc_html_e( 'Accessibility score scanner', 'ago-access' ); ?></li>
                            <li><?php esc_html_e( 'Text-to-speech (no API cost)', 'ago-access' ); ?></li>
                            <li><?php esc_html_e( 'Dyslexia-friendly font', 'ago-access' ); ?></li>
                            <li><?php esc_html_e( 'Preferences saved per visitor', 'ago-access' ); ?></li>
                            <li><?php esc_html_e( 'WCAG 2.2 oriented', 'ago-access' ); ?></li>
                            <li><?php esc_html_e( 'Lightweight (< 10KB)', 'ago-access' ); ?></li>
                        </ul>
                    </div>
                    <div class="card ago-card ago-donation">
                        <h3><?php esc_html_e( 'Support Open Source', 'ago-access' ); ?></h3>
                        <p style="font-size:13px;color:#666"><?php esc_html_e( 'This plugin is 100% free. If it helps you, consider supporting our work.', 'ago-access' ); ?></p>
                        <div class="ago-donation-amounts">
                            <a href="https://paypal.me/sixtovaldes/3" class="ago-amount" target="_blank" rel="noopener">$3</a>
                            <a href="https://paypal.me/sixtovaldes/5" class="ago-amount" target="_blank" rel="noopener">$5</a>
                            <a href="https://paypal.me/sixtovaldes/10" class="ago-amount" target="_blank" rel="noopener">$10</a>
                        </div>
                        <a href="https://paypal.me/sixtovaldes" class="ago-coffee-btn" target="_blank" rel="noopener"><span class="dashicons dashicons-coffee" style="margin-right:6px"></span><?php esc_html_e( 'Buy us a coffee', 'ago-access' ); ?></a>
                        <p class="ago-donation-note"><?php esc_html_e( 'Voluntary donation. Thank you!', 'ago-access' ); ?></p>
                    </div>
                    <div class="ago-footer">
                        <a href="https://ago.cl" target="_blank" rel="noopener" class="ago-footer-logo"><img src="<?php echo esc_url( AGO_ACCESS_URL . 'assets/img/agolab.webp' ); ?>" alt="aGo Lab" style="height:40px;width:auto"></a>
                        <p><?php /* translators: 1: heart icon HTML, 2: aGo Lab link HTML */ echo wp_kses_post( sprintf( __( 'Developed with %1$s by %2$s', 'ago-access' ), '<span style="color:#e25555">&#10084;</span>', '<a href="https://ago.cl" target="_blank" rel="noopener"><strong>aGo Lab</strong></a>' ) ); ?></p>
                        <p style="font-size:11px;color:#999"><?php esc_html_e( 'Building tools for the web, one plugin at a time.', 'ago-access' ); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
