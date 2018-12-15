<?php

/**
 * Plugin Name: PA File Download
 * Plugin URI: http://pressapps.co/
 * Description: Add donwnload files to your pages and posts.
 * Author: PressApps Team
 * Text Domain: pressapps
 * Version: 1.0.2
 */

/**
 * PAFD main file
 * 
 * @package PAFD
 */

/**
 * Debugging flag
 * @const PAFD_DEBUG
 */
define( 'PAFD_DEBUG', false );

/**
 * Error code for invalid file type
 * @const PAFD_INVALID_FILE_TYPE
 */
define( 'PAFD_ERROR_INVALID_FILE_TYPE', 1 );

/**
 * Error code for unkown errors
 * @const  PAFD_ERROR_UNKOWN
 */
define( 'PAFD_ERROR_UNKNOWN', 999 );

/**
 * The plugin textdomain
 * @global string $pafd_textdomain
 */
$pafd_textdomain = 'pressapps';

/**
 * PAFD main plugin file path relative to wp-content/plugins
 * @global string $pafd_basename
 */
$pafd_basename = plugin_basename( __FILE__ );

/**
 * PAFD directory
 * @global string $pafd_dir
 */
$pafd_dir = plugin_dir_path( __FILE__ );

/**
 * PAFD directory relative to WP_PLUGIN_DIR
 * @global string $pafd_dir_rel
 */
$pafd_dir_rel = dirname( plugin_basename( __FILE__ ) ) . '/';

/**
 * PAFD directory URL
 * @global string $pafd_dir
 */
$pafd_dir_url = plugin_dir_url( __FILE__ );

/**
 * The directory where PAFD actions, filter and other functions reside
 * @global string $pafd_includes_dir
 */
$pafd_includes_dir = $pafd_dir . 'includes/';

/**
 * The directory where PAFD  stylesheets reside
 * @global string $pafd_styles_dir
 */
$pafd_styles_dir = $pafd_dir_url . 'assets/css/';

/**
 * The directory where PAFD Javascript files reside
 * @global string $pafd_scripts_dir
 */
$pafd_scripts_dir = $pafd_dir_url . 'assets/js/';

/**
 * The directory where PAFD images files reside
 * @global string $pafd_images_dir
 */
$pafd_images_dir = $pafd_dir_url . 'assets/img/';

/**
 * PAFD options, we make theme available from the beginning
 * and make sur the variable is always an array
 * 
 * @global array $pafd_options
 */
$pafd_options = get_option( 'pafd_options' );
if ( empty( $pafd_options ) ) {
	$pafd_options = array();
}

/**
 * Since we are not using a class, we need
 * a way to allow functions to trasnmit data
 * 
 * @global array $pafd_helper
 */
$pafd_helper = array( 'upload_dir' => wp_upload_dir() );

/**
 * Bootstrap the plugin
 */
include $pafd_includes_dir . 'bootstrap.php' ;
