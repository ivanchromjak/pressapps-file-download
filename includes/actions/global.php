<?php

/**
 * Plugin lobal function 
 * 
 * Actions (functions) used by the plugin globally (back-end, front-end, ...)
 *
 * @package PAFD
 */

/**
 * Registers miscellaneous plugin CPT
 * 
 * At this time, the only CPT is a file (pa_file)
 */
function pafd_action_cpt() {

	global $pafd_textdomain;

	/**
	 * Register the taxonomy pafd_category for the CPT pa_file
	 */
	register_taxonomy(
		'pafd_category',
		'pa_file',
		array(
			'labels' => array(
				'name' => _x( 'Categories', 'taxonomy general name', $pafd_textdomain ),
				'singular_name' => _x( 'Category', 'taxonomy singular name', $pafd_textdomain ),
				'menu_name' => __( 'Categories', $pafd_textdomain ),
			),
			'rewrite' => array( 'slug' => 'pafd_category' ),
			'hierarchical' => true,
			'show_admin_column' => true,
		)
	);

	/**
	 * Register the taxonomy pafd_status for the CPT pa_file
	 */
	register_taxonomy(
		'pafd_status',
		'pa_file',
		array(
			'label' => __( 'File status', $pafd_textdomain ),
			'rewrite' => array( 'slug' => 'pafd_status' ),
			'hierarchical' => false,
			'show_admin_column' => true,
		)
	);

	/**
	 * Register the PAFD CPT
	 */
	$args = array(
		'public' => true,
		'menu_icon' => 'dashicons-download',
		'label' => __( 'Files', $pafd_textdomain ),
		'taxonomies' => array( 'pafd_category', 'pafd_status' ),
		'supports' => array( 'title', 'author' ),
	);
	register_post_type( 'pa_file', $args );
}

/**
 * Loads plugin textdomain and adds some strigns
 */
function pafd_action_load_textdomain() {

	global $pafd_textdomain, $pafd_dir_rel, $pafd_helper;

	load_plugin_textdomain( $pafd_textdomain, false, $pafd_dir_rel . 'lang' );

	$pafd_helper[ 'strings' ] = array(
		'version'     => __( 'Version', $pafd_textdomain ),
		'version_id'  => __( 'Version ID', $pafd_textdomain ),
		'name'        => __( 'Name', $pafd_textdomain ),
		'file'        => __( 'File', $pafd_textdomain ),
		'author'      => __( 'Author', $pafd_textdomain ),
		'description' => __( 'Description', $pafd_textdomain ),
		'size'        => __( 'Size', $pafd_textdomain ),
		'uploaded'    => __( 'Uploaded', $pafd_textdomain ),
		'downloaded'  => __( 'Downloads', $pafd_textdomain ),
		'download'    => __( 'Download', $pafd_textdomain ),
	);
}
