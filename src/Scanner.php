<?php

namespace AgoLab\Access;

defined( 'ABSPATH' ) || exit;

class Scanner {

    public static function scan(): array {
        $issues = [];
        $score  = 100;

        $url      = home_url();
        $response = wp_remote_get( $url, [ 'timeout' => 10 ] );
        $html     = is_wp_error( $response ) ? '' : wp_remote_retrieve_body( $response );

        if ( empty( $html ) ) {
            return [ 'score' => 0, 'issues' => [ [ 'type' => 'error', 'msg' => __( 'Could not fetch homepage. Try scanning from a browser where your site is accessible.', 'ago-access' ) ] ] ];
        }

        // Lang attribute
        if ( ! preg_match( '/html[^>]*lang=/i', $html ) ) {
            $issues[] = [ 'type' => 'error', 'msg' => __( 'Missing lang attribute on <html> tag', 'ago-access' ), 'fix' => __( 'Enable "Lang Attribute" in auto fixes', 'ago-access' ) ];
            $score -= 10;
        }

        // Viewport meta
        if ( ! preg_match( '/meta[^>]*viewport/i', $html ) ) {
            $issues[] = [ 'type' => 'warning', 'msg' => __( 'Missing viewport meta tag', 'ago-access' ), 'fix' => __( 'Your theme should include a viewport meta tag', 'ago-access' ) ];
            $score -= 5;
        }

        // Skip link
        if ( ! preg_match( '/skip.*content|skip.*nav|skip.*main/i', $html ) ) {
            $issues[] = [ 'type' => 'warning', 'msg' => __( 'No skip-to-content link found', 'ago-access' ), 'fix' => __( 'Enable "Skip Link" in auto fixes', 'ago-access' ) ];
            $score -= 10;
        }

        // Images without alt
        preg_match_all( '/<img[^>]*>/i', $html, $imgs );
        $no_alt = 0;
        foreach ( $imgs[0] ?? [] as $img ) {
            if ( ! preg_match( '/alt\s*=/i', $img ) ) $no_alt++;
        }
        if ( $no_alt > 0 ) {
            /* translators: %d: number of images without alt attribute */
            $issues[] = [ 'type' => 'error', 'msg' => sprintf( __( '%d images without alt attribute', 'ago-access' ), $no_alt ), 'fix' => __( 'Add descriptive alt text to all images', 'ago-access' ) ];
            $score -= min( 20, $no_alt * 5 );
        }

        // Heading structure (h1)
        preg_match_all( '/<h1[^>]*>/i', $html, $h1s );
        $h1_count = count( $h1s[0] );
        if ( $h1_count === 0 ) {
            $issues[] = [ 'type' => 'warning', 'msg' => __( 'No H1 heading found', 'ago-access' ), 'fix' => __( 'Every page should have one H1', 'ago-access' ) ];
            $score -= 10;
        } elseif ( $h1_count > 1 ) {
            /* translators: %d: number of H1 headings found */
            $issues[] = [ 'type' => 'warning', 'msg' => sprintf( __( '%d H1 headings found (recommended: 1)', 'ago-access' ), $h1_count ), 'fix' => __( 'Use only one H1 per page', 'ago-access' ) ];
            $score -= 5;
        }

        // Form inputs without labels
        preg_match_all( '/<input[^>]*type=["\'](?:text|email|password|tel|search|number)["\'][^>]*>/i', $html, $inputs );
        $input_count = count( $inputs[0] );
        preg_match_all( '/<label[^>]*>/i', $html, $labels );
        $label_count = count( $labels[0] );
        if ( $input_count > 0 && $label_count < $input_count ) {
            $missing = $input_count - $label_count;
            /* translators: %d: number of form inputs */
            $issues[] = [ 'type' => 'warning', 'msg' => sprintf( __( '%d form inputs may be missing labels', 'ago-access' ), $missing ), 'fix' => __( 'Ensure every input has an associated label', 'ago-access' ) ];
            $score -= min( 15, $missing * 5 );
        }

        // ARIA landmarks
        if ( ! preg_match( '/role=["\']main["\']/i', $html ) && ! preg_match( '/<main[^>]*>/i', $html ) ) {
            $issues[] = [ 'type' => 'warning', 'msg' => __( 'No main landmark found', 'ago-access' ), 'fix' => __( 'Use <main> tag or role="main"', 'ago-access' ) ];
            $score -= 5;
        }

        // Color contrast (check for very light text)
        if ( preg_match( '/color\s*:\s*#(?:fff|FFF|ffffff|FFFFFF|fefefe|f[0-9a-f]{5})\s*[;}]/i', $html ) ) {
            $issues[] = [ 'type' => 'info', 'msg' => __( 'Potential low contrast text detected', 'ago-access' ), 'fix' => __( 'Ensure text contrast ratio is at least 4.5:1', 'ago-access' ) ];
            $score -= 5;
        }

        // Focus styles
        if ( preg_match( '/outline\s*:\s*(none|0)\s*[;}]/i', $html ) ) {
            $issues[] = [ 'type' => 'error', 'msg' => __( 'Focus outline is being removed (outline:none)', 'ago-access' ), 'fix' => __( 'Enable "Focus Visible" in auto fixes, or replace outline:none with a visible alternative', 'ago-access' ) ];
            $score -= 10;
        }

        $score = max( 0, $score );

        if ( empty( $issues ) ) {
            $issues[] = [ 'type' => 'success', 'msg' => __( 'No major accessibility issues detected', 'ago-access' ) ];
        }

        return [ 'score' => $score, 'issues' => $issues, 'scanned_at' => current_time( 'mysql' ) ];
    }
}
