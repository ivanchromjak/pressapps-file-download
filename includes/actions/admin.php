<?php

/**
 * Backend actions
 * 
 * Actions (functions) used by the plugin ine the back-end
 *
 * @package PAFD
 */

/**
  * Removes unwanted metaboxes
  */
function pafd_action_remove_metaboxes() {

	// Remove the status metabox, the one with the default WordPress behaviour
	remove_meta_box(
		'tagsdiv-pafd_status'
		, 'pa_file'
		, 'core'
	);
}

/**
 * Enqueues admin scripts/styles
 */
function pafd_action_admin_enqueue_scripts() {

	global $pafd_scripts_dir, $pafd_styles_dir, $pafd_textdomain;

	// CSS
	wp_enqueue_style( 'pafd-admin', $pafd_styles_dir . 'admin.css' );

	$screen = get_current_screen();

	// PAFD pages
	if( in_array( $screen->id, array( 'pa_file', 'settings_page_pafd_settings' ) ) ) {

		// pa_file CPT page JS
		if( 'pa_file' === $screen->id ) {
			wp_enqueue_script( 'pafd-admin-file-post', $pafd_scripts_dir . 'admin-file-post.js' );
			wp_localize_script( 'pafd-admin-file-post', 'pafd_admin_file_post', array(
				'confirm_revision_deletion' => __( 'Do you really want to delete this revision?', $pafd_textdomain ),
				'remove' => __( 'Remove', $pafd_textdomain ),
				'selected_file' => __( 'Selected file', $pafd_textdomain ),
			) );
		}

		// PAFD settings page JS
		if( 'settings_page_pafd_settings' === $screen->id ) {
			wp_enqueue_script( 'pafd-admin-settings', $pafd_scripts_dir . 'admin-settings.js' );
		}
	}
}

/**
 * Brings the file metabox to the top by default
 */
function pafd_filter_order_columns( $order ) {
	
	// Make sure variable are defined
	if( empty( $order ) ) {
		$order = array();
	}
	$order_normal = K::get_var( 'normal', $order, '' );

	if( empty( $order_normal ) ) {
		$order[ 'normal' ] = 'customdiv-pafd-file';
	}

	return $order;
}

/**
 * Adds the metaboxes needed
 */
function pafd_action_add_metaboxes() {

	global $pafd_textdomain;

	// Add the file metabox
	add_meta_box(
		'customdiv-pafd-file'
		, __( 'File', $pafd_textdomain )
		, 'pafd_metabox_file_cb'
		, 'pa_file'
		, 'normal'
		, 'core'
	);

	// Add the file status metabox
	add_meta_box(
		'customdiv-pafd-status'
		, __( 'File Status', $pafd_textdomain )
		, 'pafd_metabox_status_cb'
		, 'pa_file'
		, 'side'
		, 'core'
	);
}

/**
 * Saves status meta box data
 */
function pafd_action_save_status() {

	if ( 'pa_file' == K::get_var( 'post_type', $_POST ) ) {

		global $post;

		$nonce = K::get_var( 'pafd-status-nonce', $_POST );
		$action = 'pafd_save_status';
		$status = (int) K::get_var( 'pafd-status', $_POST);

		// Check nonce
		if ( ! wp_verify_nonce( $nonce, $action ) ) {
			return;
		}

		// Ignore autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Save the status
		wp_set_object_terms( $post->ID, $status, 'pafd_status' );
	}
}

/**
 * Saves file meta box data
 */
function pafd_action_save_file() {

	if ( 'pa_file' != K::get_var( 'post_type', $_POST ) ) {
		return;
	}

	global $post, $pafd_textdomain, $pafd_helper;

	$current_user = wp_get_current_user();

	$nonce = K::get_var( 'pafd-file-nonce', $_POST );
	$action = 'pafd_save_file';
	$file = K::get_var( 'pafd-file', $_POST);
	$file[ 'upload' ] = K::get_var( 'pafd-file', $_FILES );

	// Check nonce
	if ( ! wp_verify_nonce( $nonce, $action ) ) {
		return;
	}

	// Ignore autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check there is a file
	if ( empty( $file[ 'upload' ][ 'name' ] ) ) {
		return;
	}

	// Load previous revisions
	$revisions = get_post_meta( $post->ID, 'pafd-file' );

	// Upload the file
	$pafd_helper[ 'pafd_alter_upload_dir' ] = true;
	$upload = wp_upload_bits(
		$file[ 'upload' ][ 'name' ]
		, null
		, file_get_contents( $file[ 'upload' ][ 'tmp_name' ] )
	);
	$pafd_helper[ 'pafd_alter_upload_dir' ] = false;
	$error = K::get_var( 'error', $upload );

	if( $error ) {
		$pafd_helper[ 'upload_error' ] = $error;
	} else {
		// save the file details 
		$file[ 'name' ] = K::get_var( 'name', $file ) ? K::get_var( 'name', $file ) : __( 'Untitled' );
		$file[ 'file' ] = preg_replace( '|^' . ABSPATH. '|', '', $upload[ 'file' ] );
		$file[ 'timestamp' ] = current_time( 'timestamp' );
		$file[ 'hash' ] = md5( str_replace(
			array(
				':post_ID',
				':name',
				':authorID',
				':timestamp',
			),
			array(
				$post->ID,
				$file[ 'name' ],
				$current_user->ID,
				$file[ 'timestamp' ],
			),
			':post_ID::name::author_ID::timestamp'
		) );

		// Save file information
		add_post_meta(
			$post->ID,
			'pafd-file',
			array(
				'version_id' => 1 + count( $revisions ),
				'author_ID' => $current_user->ID,
				'timestamp' => $file[ 'timestamp' ],
				'description' => $file[ 'description' ],
				'name' => $file[ 'name' ],
				'version' => $file[ 'version' ],
				'downloaded' => 0,
				'file' => $file[ 'file' ],
				'size' => $file[ 'upload' ][ 'size' ],
				'url' => $upload[ 'url' ],
				'hash' => $file[ 'hash' ],
				'type' => $file[ 'upload' ][ 'type' ],
			)
		);

		// Set file as current
		delete_post_meta( $post->ID, 'pafd-file-current' );
		add_post_meta( $post->ID, 'pafd-file-current', $file[ 'hash' ], true );

		// Set current file size for easier access
		delete_post_meta( $post->ID, 'pafd-file-size' );
		add_post_meta( $post->ID, 'pafd-file-size', $file[ 'upload' ][ 'size' ], true );
	}
}

function pafd_action_admin_notices() {

	global $pafd_textdomain;

	$message_tpl = K::wrap( 
		'<p>' . __( 'Error saving file:', $pafd_textdomain ) . ' <strong>:error</strong></p>',
		array( 'class' => 'error' ),
		array( 'return' => true )
	);

	$upload_error = K::get_var( 'pafd_upload_error', $_GET );
	
	// Continue only if there was an error
	if ( ! $upload_error ) {
		return;
	}

	// Set the error message
	switch ( $upload_error ) {
	case PAFD_ERROR_INVALID_FILE_TYPE:
		$error = __( 'Invalid file type' );
		break;
	default:
		$error = __( 'Unknown error', $pafd_textdomain );
		break;
	}

	// Build the notice
	echo $message = str_replace( ':error', $error, $message_tpl );
}

/**
 * Prints the ouput for the custom status metabox
 * 
 * This is a callback function used with add_meta_box()
 */
function pafd_metabox_status_cb() {

	global $post, $pafd_textdomain;

	// Prepare status for use with K
	$statuses[ '' ] = __( 'None', $pafd_textdomain );
	$status_terms = get_terms( 'pafd_status', 'hide_empty=0' ); 
	foreach ( $status_terms as $status_term ) {
		$statuses[ $status_term->term_id ] = $status_term->name;
	}

	// Get the file current status id
	if ( $post ) {
		$post_status_terms = wp_get_object_terms( $post->ID, 'pafd_status' );
		$post_status_term = array_shift( $post_status_terms );
		if( ! empty( $post_status_term ) ) {
			$post_status_id = $post_status_term->term_id;
		} else {
			$post_status_id = '';
		}
	}

	// Print the statuses
	K::select(
		'pafd-status',
		null,
		array(
			'options' => $statuses,
			'selected' => $post_status_id,
		)
	);

	// Print a nonce field
	K::input(
		'pafd-status-nonce',
		array(
			'type' => 'hidden',
			'value' => wp_create_nonce( 'pafd_save_status' ),
		)
	);
}

/**
 * Prints the ouput for the main file metabox
 * 
 * This is a callback function used with add_meta_box()
 */
function pafd_metabox_file_cb() {

	global $pafd_textdomain, $post, $post_meta;

	$post_meta = get_post_meta( $post->ID);
	$revisions = get_post_meta( $post->ID, 'pafd-file' );
	$revisions = array_reverse( $revisions );

	$rand = mt_rand( 100000, 999999 );

	// Maximum upload size 
	$max_upload_size = wp_max_upload_size();
	if ( ! $max_upload_size ) {
		$max_upload_size = 0;
	}
	
	$info = K::wrap(
		'<i class="dashicons dashicons-info"></i> ' 
		. sprintf( 
			__( 'Allowed file size is &lt; <strong>%s</strong><br /><i class="dashicons dashicons-info"></i> Allowed file types are listed in <a target= "_blank" href="%s">the WordPress Codex</a>.' , $pafd_textdomain ),
			pafd_format_bytes( $max_upload_size ),
			'http://codex.wordpress.org/Uploading_Files#About_Uploading_Files_on_Dashboard'
		)
		, null
		, array( 'in' => 'em', 'return' => true )
	);

	// Print a nonce field
	K::input(
		'pafd-file-nonce',
		array( 'type' => 'hidden', 'value' => wp_create_nonce( 'pafd_save_file' ) )	
	);

	// Upload label
	K::wrap( __( 'Upload New Revision', $pafd_textdomain ), null , array( 'in' => 'h2' ) );


	// Print the revision name and version fields
	echo 
		'<div class="alignleft" style="width: 47.5%;" >'
		. K::input(
			'pafd-file[name]',
			array(
				'class' => 'large-text',
			),
			array(
				'format' => '<p><label><strong>' . __( 'Revision Name', $pafd_textdomain ) . '</strong><br />:input</label></p>',
				'return' => true,
			)
		)
		. K::input(
			'pafd-file[version]',
			array(
				'class' => 'large-text',
			),
			array(
				'format' => '<p><label><strong>' . __( 'Version', $pafd_textdomain ) . '</strong><br />:input</label></p>',
				'return' => true,
			)
		)
		. '</div>'
	;

	// Print the file field
	K::input(
		'pafd-file',
		array(
			'enctype' => 'multipart/form-data',
			'id' => 'pafd-file',
			'style' => '
				height: 500px;
				left: 0;
				margin: 0;
				opacity: 0;
				padding: 0;
				position: absolute;
				right: 0;
				top: 0;
				width: 100%;
			',
			'type' => 'file',
		),
		array( 'format' => sprintf( '
			<div class="alignright" style="width: 47.5%%;">
				<div>
					<strong>%s</strong><br />
					<label style="
						border: dashed 3px #2EA2CC;
						display: block;
						height: 200px;
						overflow: hidden;
						padding: 2%%;
						position: relative;
						width: calc( 96%% - 6px );
					">
						<p style="font-size: 1.5em; text-align: center;">%s</p>
						<p style="text-align: center;" >%s <span class="button">%s</span></p>
						<p style="
							border-top: solid 1px #CCC;
							display: none;
							font-size: 1.1em;
							padding-top: 1em;
							z-index: 999;
							position: absolute;
							bottom: 0px;
							" id="pafd-file-name">
						</p>
						:input
					</label>
				</div>
				<p>%s</p>
			</div>'
			, __( 'File', $pafd_textdomain )
			, __( 'Drop file here', $pafd_textdomain )
			, __( 'or', $pafd_textdomain )
			, __( 'Select file', $pafd_textdomain )
			, $info
		) )
	);

	// Print the file description field
	K::textarea(
		'pafd-file[description]',
		null,
		array(
			'format' => '<div class="clear"><strong>' . __( 'Revision Description', $pafd_textdomain ) . '</strong><br />:textarea</div>',
			'editor' => true,
		)
	);
	
	// Revisions List Label
	K::wrap( __( 'Revisions', $pafd_textdomain ) , null , array( 'in' => 'h2' ) );

	// Prepare Revisions List
	if( $revisions ) {
		$revisions_list_tpl = '
			<table id="pafd-revisions" class="wp-list-table widefat">
				<thead>
					<tr>
						<th>' . __( 'Name', $pafd_textdomain ) . '</th>
						<th>' . __( 'Version', $pafd_textdomain ) . '</th>
						<th>' . __( 'Description', $pafd_textdomain ) . '</th>
						<th>' . __( 'Information/Download', $pafd_textdomain ) . '</th>
					</tr>
				</thead>
				<tbody>
					:revisions
				</tbody>
			</table>
		';
		$revision_row_tpl = '
			<tr class=":current">
				<td>:name</td>
				<td>:version</td>
				<td>:description</td>
				<td>:information</td>
			</tr>
		';
		$revisions_list = str_replace( ':revisions', ":revisions_$rand", $revisions_list_tpl );
		foreach ( $revisions as $r => $revision ) {

			$revision[ 'author_display_name' ] = get_userdata( $revision[ 'author_ID' ] )->display_name;
			$revision[ 'date' ] = get_date_from_gmt ( date( 'Y-m-d H:i:s', ( int ) $revision[ 'timestamp' ] ), get_option( 'date_format' ) );
			$revision[ 'time' ] = get_date_from_gmt ( date( 'Y-m-d H:i:s', ( int ) $revision[ 'timestamp' ] ), get_option( 'time_format' ) );

			$revisions_list = str_replace(
				":revisions_$rand",
				str_replace(
					array(
						':current',
						':version',
						':name',
						':description',
						':information',
					),
					array( 
						( $post_meta[ 'pafd-file-current' ][0] == $revision[ 'hash' ] ) ? 'pafd-current-revision' : '',
						$revision[ 'version' ],
						$revision[ 'name' ],
						$revision[ 'description' ],
						sprintf( ''
							. '<i class="dashicons dashicons-admin-users"></i> %s<br />'
							. '<i class="dashicons dashicons-clock"></i> %s &ndash; %s <em>(%s ago)</em><br />'
							. '<i class="dashicons dashicons-chart-bar"></i> %s<br /><br />'
							. '<a class="button button-large pafd-download-link" href="%s">' . __( 'Download', $pafd_textdomain ) . ' (%s)</a> '
							. '<a class="button button-large button-primary pafd-restore-link" href="%s">' . __( 'Restore', $pafd_textdomain ) . '</a> '
							. '<a class="button button-large pafd-delete-link" href="%s">' . __( 'Delete', $pafd_textdomain ) . '</a> '
							, $revision[ 'author_display_name' ]
							, $revision[ 'date' ]
							, $revision[ 'time' ]
							, human_time_diff( $revision[ 'timestamp' ] )
							, sprintf( _n( '<span class="pafd-download-counter">1</span> Download', '<span class="pafd-download-counter">%s</span> Downloads', $revision[ 'downloaded' ], $pafd_textdomain ), $revision[ 'downloaded' ] )
							, wp_nonce_url( esc_url_raw( add_query_arg(
								array(
									'pafd_action' => 'download',
									'pafd_revision_hash' => $revision[ 'hash' ],
								),
								get_edit_post_link()
							) ), 'pafd_action_download', 'pafd_nonce_action_download' )
							, pafd_format_bytes( $revision[ 'size' ] )
							, wp_nonce_url( esc_url_raw( add_query_arg(
								array(
									'pafd_action' => 'restore',
									'pafd_revision_hash' => $revision[ 'hash' ],
								),
								get_edit_post_link()
							) ), 'pafd_action_restore', 'pafd_nonce_action_restore' )
							, wp_nonce_url( esc_url_raw( add_query_arg(
								array(
									'pafd_action' => 'delete',
									'pafd_revision_hash' => $revision[ 'hash' ],
								),
								get_edit_post_link()
							) ), 'pafd_action_delete', 'pafd_nonce_action_delete' )
						),
					),
					$revision_row_tpl) . ":revisions_$rand",
				$revisions_list
			);
		}
		$revisions_list = str_replace( ":revisions_$rand", '', $revisions_list );
	}

	// Output
	if( $revisions ) {
		echo $revisions_list;
	} else {
		K::wrap(
			'<i class="btn dashicons dashicons-info"></i> ' 
			. __( 'No revisions uploaded yet.', $pafd_textdomain )
			, null
			, array( 'in' => 'em' )
		);
	}

	// Debugging information
	if ( PAFD_DEBUG ) {
		
		// Debugging Label
		K::wrap( __( 'Debugging', $pafd_textdomain ) , null , array( 'in' => 'h2' ) );

		!d( $post );
		!d( $post_meta );
	}
}

/**
 * Changes the path from uploads to uploads/pafd-files
 * when pafd_alter_upload_dir is true
 */
function pafd_filter_upload_dir() {

	global $pafd_helper, $post;
	$upload_dir = $pafd_helper[ 'upload_dir' ];

	// If pafd_alter_upload_dir is false then don't touch the path
	if ( ! K::get_var( 'pafd_alter_upload_dir', $pafd_helper ) ) {
		return $upload_dir;
	}

	// Add global and local file suffix
	$suffix = '/pafd-files' . ( $post ? '/'.$post->ID : '' );
	$upload_dir[ 'subdir' ] = $suffix;
	$upload_dir[ 'basedir' ] .= $suffix;
	$upload_dir[ 'baseurl' ] .= $suffix;
	$upload_dir[ 'path' ] = $upload_dir[ 'basedir' ];
	$upload_dir[ 'url' ] = $upload_dir[ 'baseurl' ];

	return $upload_dir;
}

/**
 * Restores a revision
 */
function pafd_action_revision_actions() {
	
	// Only continue if we are editing a pa_file
	$ok = false;
	if( 'edit' == K::get_var( 'action', $_GET ) && $post_ID = K::get_var( 'post', $_GET ) ) {	
		$post = get_post( $post_ID );
		if( $post && 'pa_file' ===  $post->post_type ) {
			$ok = true;
		}
	}
	if( ! $ok ) {
		return;
	}

	// Restore revision
	if( 'restore' === K::get_var( 'pafd_action', $_GET ) ) {

		// Verify nonce
		if ( 
			! K::get_var( 'pafd_nonce_action_restore', $_GET )
			|| ! wp_verify_nonce( $_GET['pafd_nonce_action_restore'], 'pafd_action_restore' ) 
		) {
			return;
		}

		// Verify hash format (lowercase, 32 characters, hexadecimal)
		if ( ! preg_match( '/^[a-f0-9]{32}$/', K::get_var( 'pafd_revision_hash', $_GET ) ) ) {
			return;
		}

		// Set file as current
		delete_post_meta( $post->ID, 'pafd-file-current' );
		add_post_meta( $post->ID, 'pafd-file-current', K::get_var( 'pafd_revision_hash', $_GET ), true );
	}

	// Download revision
	if( 'download' == K::get_var( 'pafd_action', $_GET ) ) {

		// Verify nonce
		if ( 
			! K::get_var( 'pafd_nonce_action_download', $_GET )
			|| ! wp_verify_nonce( $_GET['pafd_nonce_action_download'], 'pafd_action_download' ) 
		) {
			wp_die( __( 'Wrong download link', $pafd_textdomain ) );
		}

		/**
		  * Get requested revision and update its download counter
		  */
		$post_meta = get_post_meta( $post->ID );
		$revisions = get_post_meta( $post->ID, 'pafd-file' );
		$downloaded_total = 0;
		foreach ( $revisions as $r => $revision ) {
			if( $revision[ 'hash' ] == K::get_var( 'pafd_revision_hash', $_GET ) ) {
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

	// Delete revision 
	if( 'delete' == K::get_var( 'pafd_action', $_GET ) ) {

		// Verify nonce
		if ( 
			! K::get_var( 'pafd_nonce_action_delete', $_GET )
			|| ! wp_verify_nonce( $_GET['pafd_nonce_action_delete'], 'pafd_action_delete' ) 
		) {
			wp_die( __( 'Wrong deletion link', $pafd_textdomain ) );
		}

		/**
		  * Get the requested revision and update its download counter
		  */
		$post_meta = get_post_meta( $post->ID );
		$revisions = get_post_meta( $post->ID, 'pafd-file' );

		// Delete the revision requested and save others
		delete_post_meta( $post->ID, 'pafd-file' );
		foreach ( $revisions as $r => $revision ) {
			if( $revision[ 'hash' ] == K::get_var( 'pafd_revision_hash', $_GET ) ) {
				unlink( ABSPATH . $revision[ 'file' ] );
			} else {
				add_post_meta( $post->ID, 'pafd-file', $revision );
			}
		}

		// Set new current, if the deleted was current
		if ( K::get_var( 'pafd_revision_hash', $_GET ) == $post_meta[ 'pafd-file-current' ][0] ) {
			$revisions = array_reverse( $revisions );
			reset( $revisions );
			foreach ( $revisions as $r => $revision ) {
				if( $revision[ 'hash' ] == K::get_var( 'pafd_revision_hash', $_GET ) ) {
					// This was deleted
				} else {
					// Set first available as current then exit loop
					delete_post_meta( $post->ID, 'pafd-file-current' );
					add_post_meta( $post->ID, 'pafd-file-current', $revision[ 'hash' ], true );
					break;
				}
			}
		}
	}
}

/**
 * Deletes a pa_file post revisions
 */
function pafd_action_delete_revisions( $post_ID ) {

	global $pafd_helper, $post;

	/**
	 * Get the uploads basedir,
	 * don't add $post_ID when global $post is not empty
	 */
	$pafd_helper[ 'pafd_alter_upload_dir' ] = true;
	$upload_dir = wp_upload_dir();
	if( empty( $post ) ) {
		$post_upload_basedir = $upload_dir[ 'basedir' ] . "/$post_ID";
	} else {
		$post_upload_basedir = $upload_dir[ 'basedir' ];
	}
	$pafd_helper[ 'pafd_alter_upload_dir' ] = false;

	// Do nothing if directory doesn't exist
	if ( ! is_dir( $post_upload_basedir ) ) {
		return;
	}

	// Delete files
	$files = glob( $post_upload_basedir . '/*' );
	foreach ( $files as $file ) {
		unlink( $file );
	}

	// Delete directory "wp-content/uploads/pafd-files/$post_ID"
	rmdir( $post_upload_basedir );
}

/**
 * Adds the admin page and it's menu under the Settings menu
 */
function pafd_action_add_admin_page() {
	global $pafd_textdomain;
	add_options_page(
		__( 'PA File Download Settings', $pafd_textdomain ),
		__( 'PA File Download', $pafd_textdomain ),
		'manage_options',
		'pafd_settings',
		'pafd_options_page_html'
	);
}

/**
 * Adds settings to the settings page
 */
function pafd_action_settings_page() {

	global $pafd_textdomain;
	
	register_setting( 'pafd_options_group', 'pafd_options' );

	pafd_add_settings_field( 'columns', __( 'Visible columns', $pafd_textdomain ) );
	pafd_add_settings_field( 'download_link', __( 'Download link', $pafd_textdomain ) );
	pafd_add_settings_field( 'hide_table_header', __( 'Hide table header', $pafd_textdomain ) );
	pafd_add_settings_field( 'show_all_revisions', __( 'Show all file revisions', $pafd_textdomain ) );
	pafd_add_settings_field( 'show_file_status', __( 'Show file status', $pafd_textdomain ) );
	pafd_add_settings_field( 'show_icons', __( 'Show mime-type icons', $pafd_textdomain ) );
	pafd_add_settings_field( 'order_by', __( 'Order files by' ), $pafd_textdomain );
	pafd_add_settings_field( 'custom_css_code', __( 'Custom CSS code' ), $pafd_textdomain );
}

function pafd_action_ajax_files_dropdown() {
	
	global $pafd_textdomain;

	$protocol = is_ssl() ? 'https' : 'http';

	// Files
	$pafd_files_tinymce = array();
	$pafd_files = get_posts( array(
		'numberposts' => -1,
		'post_type'   => 'pa_file',
	) );
	foreach ($pafd_files as $pafd_file ) {
		$pafd_files_tinymce[ $pafd_file->ID ] = $pafd_file->post_title;
	}

	echo '<html style="height: inherit"><body class="wp-core-ui" style="height: inherit; padding: 1em;">
			<head>
				<link rel="stylesheet" href="' . admin_url( 'css/wp-admin.css' ) . '" type="text/css" media="all" />
				<link rel="stylesheet" href="' . includes_url() . 'css/buttons.css" type="text/css" media="all" />
				<link rel="stylesheet" href="' . includes_url( 'css/dashicons.css' ) . '" type="text/css" media="all" />';

	// Add jQuery and select 2
	printf( '<style>@import url("%s")</style><script src="%s"></script><script src="%s"></script><script>
			jQuery( function ( $ ) {
				$select = $( "select" );
				$select.select2(); 
				$( "[type=submit]" ).click( function() {
					$( ":selected", $select ).length &&
					parent.tinymce.activeEditor.execCommand( "mceInsertContent", false, "[pa_file files=" + $( ":selected", $select ).map( function() { return this.value; } ).get().join() + ( $( "[name=pafd_show_all_revisions]:checked" ).length ? " show_all_revisions=true" : "" ) +"]" ) ;
					parent.tinymce.activeEditor.windowManager.close( window );
				} );
			} );
		</script>'
		, $protocol . '://cdnjs.cloudflare.com/ajax/libs/select2/3.5.0/select2.min.css'
		, includes_url( 'js/jquery/jquery.js' )
		, $protocol . '://cdnjs.cloudflare.com/ajax/libs/select2/3.5.0/select2.min.js'
	);

	echo '</head><body>';

	K::select(
		'pafd_files'
		, array(
			'multiple' => 'multiple',
			'placeholder' => __( 'Type a file name...', $pafd_textdomain ),
			'style' => 'width: 100%;',
		)
		, array( 
			'options' => $pafd_files_tinymce,
			'format' => '<p style="margin-bottom: 30px">:select</p>'
		)
	);

	K::input(
		'pafd_show_all_revisions'
		, array(
			'type' => 'checkbox',
		)
		, array( 
			'format' => '<p style="margin-bottom: 30px"><label>:input ' . __( 'Show all file revisions', $pafd_textdomain ) . '</label></p>'
		)
	);

	K::input(
		'pafd_select_file'
		, array(
			'type' => 'submit',
			'value' => __( 'Insert File', $pafd_textdomain ),
			'class' => 'button button-primary',
		)
		, array( 
			'format' => '<p>:input</p>'
		)
	);

	echo '</body></html>';

	die();
}

function pafd_action_ajax_categories_dropdown() {
	
	global $pafd_textdomain;

	$protocol = is_ssl() ? 'https' : 'http';

	// Categories
	$pafd_categories_tinymce = array();
	$pafd_categories = get_terms( 'pafd_category', 'hide_empty=0' );
	foreach ($pafd_categories as $pafd_category ) {
		$pafd_categories_tinymce[ $pafd_category->term_id ] = $pafd_category->name;
	}

	echo '<html style="height: inherit"><body class="wp-core-ui" style="height: inherit; padding: 1em;">
			<head>
				<link rel="stylesheet" href="' . admin_url( 'css/wp-admin.css' ) . '" type="text/css" media="all" />
				<link rel="stylesheet" href="' . includes_url() . 'css/buttons.css" type="text/css" media="all" />
				<link rel="stylesheet" href="' . includes_url( 'css/dashicons.css' ) . '" type="text/css" media="all" />';

	// Add jQuery and select 2
	printf( '<style>@import url("%s")</style><script src="%s"></script><script src="%s"></script><script>
			jQuery( function ( $ ) {
				$select = $( "select" );
				$select.select2(); 
				$( "[type=submit]" ).click( function() {
					$( ":selected", $select ).length &&
					parent.tinymce.activeEditor.execCommand( "mceInsertContent", false, "[pa_file categories=" + $( ":selected", $select ).map( function() { return this.value; } ).get().join() + ( $( "[name=pafd_show_all_revisions]:checked" ).length ? " show_all_revisions=true" : "" ) +"]" ) ;
					parent.tinymce.activeEditor.windowManager.close( window );
				} );
			} );
		</script>'
		, $protocol . '://cdnjs.cloudflare.com/ajax/libs/select2/3.5.0/select2.min.css'
		, includes_url( 'js/jquery/jquery.js' )
		, $protocol . '://cdnjs.cloudflare.com/ajax/libs/select2/3.5.0/select2.min.js'
	);

	echo '</head><body>';

	K::select(
		'pafd_categories'
		, array(
			'multiple' => 'multiple',
			'placeholder' => __( 'Type a category name...', $pafd_textdomain ),
			'style' => 'width: 100%;',
		)
		, array( 
			'options' => $pafd_categories_tinymce,
			'format' => '<p style="margin-bottom: 30px">:select</p>'
		)
	);

	K::input(
		'pafd_show_all_revisions'
		, array(
			'type' => 'checkbox',
		)
		, array( 
			'format' => '<p style="margin-bottom: 30px"><label>:input ' . __( 'Show all file revisions', $pafd_textdomain ) . '</label></p>'
		)
	);

	K::input(
		'pafd_select_category'
		, array(
			'type' => 'submit',
			'value' => __( 'Insert Category', $pafd_textdomain ),
			'class' => 'button button-primary',
		)
		, array( 
			'format' => '<p>:input</p>'
		)
	);

	echo '</body></html>';

	die();
}