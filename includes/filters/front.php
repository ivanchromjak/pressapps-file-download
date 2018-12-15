<?php

/**
 * Frontend filters
 * 
 * Filters (functions) used by the plugin ine the front-end
 *
 * @package PAFD
 */

/**
 * Generates the content of the pa_file post
 */
function pafd_filter_post_content ( $content ) {
	
	global $post;

	if( 'pa_file' === $post->post_type ) {
		$content .= pafd_get_files_table_html( $post->id );
	}

	return $content;
}

/**
 * Send a revision download
 */
function pafd_filter_download_revision( $wp_posts, $wp_query ) {

	// Check a hash was provided (download=[md5 hash])
	if ( ! preg_match( '/^[a-f0-9]{32}$/', K::get_var( 'download', $_GET ) ) ) {
		return $wp_posts;
	}

	// Check it's a single post
	if (! is_single() ) {
		return $wp_posts;
	}

	// Check it's a pa_file
	if( 'pa_file' !== $wp_query->query_vars[ 'post_type' ] ) {
		return $wp_posts;
	}

	// Check a post was found
	if( ! $wp_query->found_posts ) {
		return $wp_posts;
	}

	// post  shortcut
	$post = $wp_posts[0];

	/**
	  * Get requested revision and update its download counter
	  */
	$post_meta = get_post_meta( $post->ID );
	$revisions = get_post_meta( $post->ID, 'pafd-file' );
	$downloaded_total = 0;
	foreach ( $revisions as $r => $revision ) {
		if( $revision[ 'hash' ] == K::get_var( 'download', $_GET ) ) {
			$revisions[ $r ][ 'downloaded' ] += 1;
			$download = $revisions[ $r ];
		}
		$downloaded_total += $revisions[ $r ][ 'downloaded' ];
	}
	
	// Update pafd-file to save the download count
	delete_post_meta( $post->ID, 'pafd-file' );
	reset( $revisions );
	foreach ( $revisions as $revision ) {	
		
		add_post_meta( $post->ID, 'pafd-file', $revision );
	}

	// Update the total downloads count
	delete_post_meta( $post->ID, 'pafd-file-downloaded' );
	add_post_meta( $post->ID, 'pafd-file-downloaded', $downloaded_total );

	// Send requested download
	header( 'Content-Type: application/octet-stream' );
	header( 'Content-Transfer-Encoding: Binary' );
	header( 'Content-disposition: attachment; filename="' . basename( $download[ 'url' ] ) . '"' );
	readfile( ABSPATH . $download[ 'file' ] );
	exit;
}
