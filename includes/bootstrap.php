<?php

/**
 * This file bootsrtaps the plugin
 * 
 * @package PAFD
 */

/**
 * The plugin uses the K framework for fast HTML generation
 */
if ( ! class_exists( 'K') ) {
	require $pafd_includes_dir . 'classes/K/K.php' ;
}

/**
 * The plugin uses Kint for debugging
 */
if ( PAFD_DEBUG && ! class_exists ( 'Kint' ) ) {
	require $pafd_includes_dir . 'classes/kint/Kint.class.php';
}

/**
 * Include the global instructions
 */
include $pafd_includes_dir . 'global.php';
include $pafd_includes_dir . 'functions/global.php';
include $pafd_includes_dir . 'actions/global.php';

/**
 * Include the front-end instructions
 */
if( ! is_admin() ) {
	include $pafd_includes_dir . 'front.php';
	include $pafd_includes_dir . 'actions/front.php';
	include $pafd_includes_dir . 'filters/front.php';
	include $pafd_includes_dir . 'callbacks/front.php';
}

/**
 * Include the back-end instructions
 */
if( is_admin() ) {
	include $pafd_includes_dir . 'admin.php';
	include $pafd_includes_dir . 'actions/admin.php';
	include $pafd_includes_dir . 'filters/admin.php';
	include $pafd_includes_dir . 'callbacks/admin.php';
}
