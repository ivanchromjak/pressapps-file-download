<?php

/**
 * Backend filters
 * 
 * Filters (functions) used by the plugin ine the back-end
 *
 * @package PAFD
 */

function pafd_filter_redirect_location( $location, $post_id ) {
	
	global $pafd_helper;

	$upload_error = K::get_var( 'upload_error', $pafd_helper );

	// No error, so let's return the unmodified URI 
	if( ! $upload_error ) {
		return $location;
	}

	// Which error
	switch ( $upload_error ) {
	case __( 'Invalid file type' ):
		$upload_error_code = PAFD_ERROR_INVALID_FILE_TYPE;
		break;
	default:
		$upload_error_code = PAFD_ERROR_UNKNOWN;
		break;
	}
	
	// Add the error code to the url
	$location = add_query_arg( array( 'pafd_upload_error' => $upload_error_code  ), $location );

	return esc_url( $location );
}

add_filter( 'mce_external_languages', 'pafd_filter_mce_external_languages', 10, 1 );
function pafd_filter_mce_external_languages( $locales ) {
	
	global $pafd_includes_dir;

	$locales [] =  $pafd_includes_dir . 'tinymce.php';
	return $locales;
}
