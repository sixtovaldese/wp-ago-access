<?php
/**
 * Plugin Name: aGo Access
 * Plugin URI:  https://ago.cl/herramientas/
 * Description: Accessibility toolbar and automatic fixes for WordPress. WCAG 2.2 oriented, 100% free.
 * Version:     1.0.0
 * Author:      aGo Lab
 * Author URI:  https://github.com/agolab
 * License:     GPL-2.0-or-later
 * Text Domain: ago-access
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.1
 */

defined( 'ABSPATH' ) || exit;

define( 'AGO_ACCESS_VERSION', '1.0.0' );
define( 'AGO_ACCESS_FILE', __FILE__ );
define( 'AGO_ACCESS_PATH', plugin_dir_path( __FILE__ ) );
define( 'AGO_ACCESS_URL', plugin_dir_url( __FILE__ ) );

spl_autoload_register( function ( string $class ) {
    $prefix = 'AgoLab\\Access\\';
    if ( strncmp( $class, $prefix, strlen( $prefix ) ) !== 0 ) return;
    $file = AGO_ACCESS_PATH . 'src/' . str_replace( '\\', '/', substr( $class, strlen( $prefix ) ) ) . '.php';
    if ( file_exists( $file ) ) require $file;
});

add_action( 'plugins_loaded', function () {
    \AgoLab\Access\Plugin::instance();
});
