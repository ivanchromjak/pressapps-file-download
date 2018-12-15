<?php

/**
 * Callback functions instructions
 * 
 * Example  file output, etc...
 * 
 * @package PAFD
 */

function pafd_get_files_table_html( $ids, $options = array() ) {
	
	global $pafd_options, $pafd_styles_dir, $pafd_helper;

	$rand = mt_rand( 100000, 999999 );

	if( K::get_var( 'pafd_setting_hide_table_header', $pafd_options ) ) {
		$files_table_tpl = '<table class="pafd-revisions">:files_rows</table>';
	} else {
		$files_table_tpl = '
			<table class="pafd-revisions">
				<thead><tr>:header_cells</tr></thead>
				:files_rows
			</table>
		';
	}

	// Add replacement lock
	$files_table_html = str_replace(
		array( ':header_cells', ':files_rows' )
		, array( ":header_cells_$rand", ":files_rows_$rand" )
		, $files_table_tpl
	);

	// Add header cells
	$files_table_html = str_replace(
		":header_cells_$rand"
		, pafd_get_files_header_cells_html()
		, $files_table_html
	);

	// Add files rows
	$files_table_html = str_replace(
		":files_rows_$rand"
		, pafd_get_files_rows_html( $ids, $options )
		, $files_table_html
	);

	// CSS @todo Move to shortcode callback
	wp_enqueue_style( 'pafd-front', $pafd_styles_dir . 'front.css' );
	// CSS
	$css = '';
	if( ! K::get_var( 'css_done', $pafd_helper ) ) {
		$pafd_helper[ 'css_done' ] = true;
		$css = '<style>' . K::get_var( 'pafd_setting_custom_css_code', $pafd_options ) . '</style>';
	}
	// HTML
	return $css . $files_table_html;
}

function pafd_get_files_header_cells_html() {

	global $pafd_helper, $pafd_options;

	$columns = array_keys(
		K::get_var(
			'pafd_setting_columns',
			$pafd_options,
			array( 'name' => 'on', 'description' => 'on' , 'download' => 'on' )
		)
	);
	$rand = mt_rand( 100000, 999999 );
	$header_cells_tpl = ":header_cells_$rand";
	$header_cells_html = $header_cells_tpl;

	// Add header cells
	foreach ( $columns as $column ) {
		$header_cells_html = str_replace(
			":header_cells_$rand",
			"<th>{$pafd_helper[ 'strings' ][$column]}</th>:header_cells_$rand",
			$header_cells_html
		);
	}

	// Remove replacement lock
	$header_cells_html = str_replace( ":header_cells_$rand", '', $header_cells_html );

	return $header_cells_html;
}

function pafd_get_files_rows_html( $ids, $options = array() ) {

	global $pafd_options, $pafd_textdomain;
 
	$ids = explode( ',', $ids );
	$columns = array_keys(
		K::get_var(
			'pafd_setting_columns',
			$pafd_options,
			array( 'name' => 'on', 'description' => 'on' , 'download' => 'on' )
		)
	);

	$files_rows_html = '';
	foreach ( $ids as $id ) {
		$post = get_post( $id );
		$post_statuses = wp_get_post_terms( $post->ID, 'pafd_status', array( 'fields' => 'names' ) );
		$post_status = array_pop( $post_statuses );

		if ( ! $post OR 'pa_file' !== $post->post_type OR 'publish' !== $post->post_status ) {
			// Show error message if this is not a valid pa_file
			$error = sprintf( __( 'File post (#%s) does not exist.', $pafd_textdomain ) , $id );
			$files_rows_html .= '<tr><td colspan = "' . count( $columns ) . '"><h3>' . $error . '</h3></td></tr>';
		} else {
			if( K::get_var( 'pafd_setting_show_file_status', $pafd_options ) ) {
				$files_rows_html .= '<tr>
					<td colspan = "' . ( count( $columns ) ) . '"><h3>' . $post->post_title . '</h3>
						<p style="margin: 0; font-size: 0.8em">' . __( 'Status', $pafd_textdomain ) .': ' . $post_status . '</p>
					</td>
				</tr>';
			} else {
				$files_rows_html .= '<tr><td colspan = "' . count( $columns ) . '"><h3>' . $post->post_title . '</h3></td></tr>';
			}
			$files_rows_html .= pafd_get_file_revisions_rows_html( $id, $options );
		}
	}
	return $files_rows_html;
}

/**
 * Produces the HTML source code for the file
 * 
 * @param integer $id the pa_file post ID
 * @return string the html
 */
function pafd_get_file_revisions_rows_html( $id, $options = array() ) {

	global $pafd_helper, $pafd_options, $pafd_images_dir, $pafd_styles_dir, $pafd_textdomain;

	$options += array(
		'show_all_revisions' => false,
	);

	$post = get_post( ( int ) $id );
	$post_meta = get_post_meta( $post->ID );
	$revisions = get_post_meta( $post->ID, 'pafd-file' );
	$revisions = array_reverse( $revisions );
	$columns = array_keys( K::get_var(
		'pafd_setting_columns',
		$pafd_options,
		array( 'name' => 'on', 'description' => 'on' , 'download' => 'on' )
	) );
	$rand = mt_rand( 100000, 999999 );

	// Show message if there are no revisions yet in the file
	if ( ! $revisions ) {

		$error = sprintf( 
			__( "%s This file post (#%s) is empty, no revision has been uploaded to it yet.", $pafd_textdomain )
			, '<div class="dashicons dashicons-flag"></div>'
			, $id
		);
		
		return sprintf(
			'<tr><td colspan="%s">%s</td></tr>'
			, count( $columns )
			, $error
		);
	}

	// Add CSS
	wp_enqueue_style( 'pafd-front', $pafd_styles_dir . 'front.css' );

	// Add revision rows
	$revision_row_tpl = "<tr :current>:data_cells_$rand</tr>";
	$revisions_rows = '';
	foreach ( $revisions as $revision ) {

		$is_current_revision = $post_meta[ 'pafd-file-current' ][ 0 ] === $revision[ 'hash' ];

		// Skip if not current revision or if not showing all revisions
		if ( ! $is_current_revision && ! K::get_var( 'pafd_setting_show_all_revisions', $pafd_options ) && 'true' !== $options[ 'show_all_revisions' ] ) {
			continue;
		}

		// Add one row tempate
		$revisions_rows .= str_replace(
			':current'
			, $is_current_revision ? 'class="pafd-current-revision"' : ''
			, $revision_row_tpl
		);
		$revision[ 'file' ] = basename( $revision[ 'file' ] );
		if( K::get_var( 'pafd_setting_show_icons', $pafd_options ) ) {
			
			$extension = wp_check_filetype( ABSPATH . $revision[ 'file' ] );
			$extension = array_values( $extension );
			$extension = array_shift( $extension );

			$icon_url = 
				$pafd_images_dir
				. 'mime-types/'
				. (
					in_array( $extension, array( '3gp', '7z', 'ace', 'ai', 'aif', 'aiff', 'amr', 'asf', 'asx', 'bat', 'bin', 'bmp', 'bup', 'cab', 'cbr', 'cda', 'cdl', 'cdr', 'chm', 'dat', 'divx', 'dll', 'dmg', 'doc', 'dss', 'dvf', 'dwg', 'eml', 'eps', 'exe', 'fla', 'flv', 'gif', 'gz', 'hqx', 'htm', 'html', 'ifo', 'indd', 'iso', 'jar', 'jpeg', 'jpg', 'lnk', 'log', 'm4a', 'm4b', 'm4p', 'm4v', 'mcd', 'mdb', 'mid', 'mov', 'mp2', 'mp3', 'mp4', 'mpeg', 'mpg', 'msi', 'mswmm', 'ogg', 'pdf', 'png', 'pps', 'ps', 'psd', 'pst', 'ptb', 'pub', 'qbb', 'qbw', 'qxd', 'ram', 'rar', 'rm', 'rmvb', 'rtf', 'script', 'sea', 'ses', 'sit', 'sitx', 'ss', 'swf', 'tgz', 'thm', 'tif', 'tmp', 'torrent', 'ttf', 'txt', 'vcd', 'vob', 'wav', 'wma', 'wmv', 'wps', 'xls', 'xpi', 'zip' ) ) 
						? $extension
						: 'script' 
					)
				. '.png'
			;

			$revision[ 'file' ] = 
				sprintf(
					'<img src="%s" title="%s" /> '
					, $icon_url
					, $revision[ 'type' ]
				)
				. $revision[ 'file' ]
			;
		}
		$revision[ 'size' ] = pafd_format_bytes( $revision['size'] );
		$revision[ 'author' ] = get_userdata( $revision[ 'author_ID' ] )->display_name;
		$revision[ 'uploaded' ] = pafd_datetime( $revision[ 'timestamp' ] );

		switch ( K::get_var( 'pafd_setting_download_link', $pafd_options ) ) {
			case 'circle':
				$download_link_string_format = '<img src="' . $pafd_images_dir . 'download-circle.png' . '" title="%s" />';
				break;
			case 'square':
				$download_link_string_format = '<img src="' . $pafd_images_dir . 'download-square.png' . '" title="%s" />';
				break;
			default:
				$download_link_string_format = '%s';
				break;
		}
		$download_link_string = sprintf( $download_link_string_format, __( 'Download', $pafd_textdomain ) ) ;
		;

		$revision[ 'download' ] = sprintf(
			'<a href="%s"/>%s</a>'
			, esc_url( add_query_arg( 'download', $revision[ 'hash' ], get_permalink( $id ) ) )
			, $download_link_string
		);

		foreach ( $columns as $column ) {

			$revisions_rows = str_replace(
				":data_cells_$rand",
				"<td>{$revision[$column]}</td>:data_cells_$rand",
				$revisions_rows
			);
		}
		$revisions_rows = str_replace( ":data_cells_$rand", '', $revisions_rows );
	}
	return $revisions_rows;
}

/**
 * Creates the output for [pa_file]
 * 
 * @return string the shortcode generated HTML code
 */
function pafd_shortcode( $atts ) {

	global $pafd_options;

	$atts_options = $atts;
	if( $ids = K::get_var( 'files', $atts ) ) {
		unset( $atts_options[ 'files' ] );
		return pafd_get_files_table_html( $ids, $atts_options );
	} else if ( $category_ids = K::get_var( 'categories', $atts ) ) {
		unset( $atts_options[ 'categories' ] );
		$args = array(
			'post_type' => 'pa_file',
			'posts_per_page' => -1,
			'tax_query' => array(
				array(
					'taxonomy' => 'pafd_category',
					'terms' => $category_ids,
				),
			),
		);
		// Compute orderby
		switch ( K::get_var( 'pafd_setting_order_by', $pafd_options ) ) {
			case 'updated':
				$args[ 'orderby' ] = 'modified';
				break;
			case 'created':
				$args[ 'orderby' ] = 'date';
				break;
			case 'downloaded':
				$args[ 'orderby' ] = 'meta_value_num';
				$args[ 'meta_key' ] = 'pafd-file-downloaded';
				break;
			default:
				$args[ 'orderby' ] = 'title';
				$args[ 'order' ] = 'ASC';
				break;
		}
		// Get posts
		$posts = get_posts( $args );
		// Build IDs list
		$ids = '';
		foreach ( $posts as $post ) {
			$ids .= $post->ID . ',';
		}
		// Remove last comma
		$ids = preg_replace( '/,$/', '', $ids );
		// Show files
		return pafd_get_files_table_html( $ids, $atts_options );
	}
}
