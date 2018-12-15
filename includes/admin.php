<?php

/**
 * Back-end instructions
 * 
 * This file contains back-end instructions like metaboxes, etc...
 * 
 * @package PAFD
 */

/**
 * Hook actions
 */
add_action( 'admin_menu',             'pafd_action_add_admin_page' );
add_action( 'admin_menu',             'pafd_action_remove_metaboxes' );
add_action( 'add_meta_boxes',         'pafd_action_add_metaboxes' );
add_action( 'save_post',              'pafd_action_save_file' );
add_action( 'save_post',              'pafd_action_save_status' );
add_action( 'admin_enqueue_scripts',  'pafd_action_admin_enqueue_scripts' );
add_action( 'admin_notices',          'pafd_action_admin_notices' );
add_action( 'admin_init',             'pafd_action_settings_page' );
add_action( 'admin_init',             'pafd_action_revision_actions' );
add_action( 'before_delete_post',     'pafd_action_delete_revisions' );
add_action( 'admin_head',             'pafd_action_pafd_button' );
add_action( 'wp_ajax_pafd_files_dropdown', 'pafd_action_ajax_files_dropdown');
add_action( 'wp_ajax_pafd_categories_dropdown', 'pafd_action_ajax_categories_dropdown');

/**
 * Hook Filters
 */
add_filter( 'redirect_post_location', 'pafd_filter_redirect_location', 10, 2 );
add_filter( 'upload_dir',             'pafd_filter_upload_dir' );
add_filter( 'get_user_option_meta-box-order_pa_file', 'pafd_filter_order_columns' );

/**
 * 
 */
function pafd_action_pafd_button() {

	$screen = get_current_screen();

	// Post pages (new/edit) only
	if( 'post' !== $screen->base ) {
		return;
	}

	// Exclude pa_file
	if( 'pa_file' === $screen->post_type ) {
		return;
	}
		
	// Do nothing if WYSIWYG is disabled for user
	if ( 'true' !== get_user_option( 'rich_editing' ) ) {
		return;
	}

	// OK, add the filters reponsible for adding the button
	add_filter( 'mce_external_plugins', 'pafd_filter_add_buttons' );
	add_filter( 'mce_buttons_3',        'pafd_filter_register_buttons' );
}

/**
 * 
 */
function pafd_filter_add_buttons( $plugins ) {

	global $pafd_scripts_dir;

	$plugins[ 'pa_file' ] = $pafd_scripts_dir . 'admin-post-tinyMCE.js';
	return $plugins;
}

/**
 * 
 */
function pafd_filter_register_buttons( $buttons ) {

	$buttons[] = 'pa_file';
	return $buttons;
}
