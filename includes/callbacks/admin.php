<?php

/**
 * Callback functions instructions
 * 
 * Example  metaboxes, etc...
 * 
 * @package PAFD
 */
function pafd_options_page_html() {

	global $pafd_options, $pafd_textdomain;
	?>
	<div class="wrap">
		<h2><?php _e( 'PA File Download Settings', $pafd_textdomain ); ?></h2>
		<form action="options.php" method="post">
			<?php settings_fields( 'pafd_options_group' ); ?>
			<?php do_settings_sections( 'pafd_settings' ); ?>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php if ( PAFD_DEBUG ) { ?>
		<div class="wrap"><?php !d( $pafd_options ); ?></div>
	<?php } 
}

/**
 * Helper function for generating settings fields
 * 
 * Wrap $name and $section_name in __(), no need to pass the textdomain,
 * the function takes care of that.
 * 
 */
function pafd_add_settings_field( $field_id, $field_name, $section_id = 'default', $section_name = null, $args = array() ) {

	global $pafd_textdomain, $pafd_helper;

	$_args = get_defined_vars();

	$field_id = "pafd_setting_{$field_id}";
	$field_name = __( $field_name, $pafd_textdomain );
	$section_id = "pafd_section_{$section_id}";

	if ( function_exists( "{$field_id}_html" ) ) {
		$field_callback = "{$field_id}_html";
	} else {
		$field_callback = 'pafd_setting_html';
		$args = $_args;
	}

	/**
	 * Create the section if needed
	 */
	if ( ! empty( $section_name ) ) {
		$section_name = __( $section_name, $pafd_textdomain );
	}
	$section_callback = function_exists( "{$section_id}_html" ) ? "{$section_id}_html" : null;
	if ( empty ( $pafd_helper[ 'sections' ] ) || ! in_array( $section_id, $pafd_helper[ 'sections' ] ) ) {
		add_settings_section( $section_id, $section_name, $section_callback, 'pafd_settings' );
		$pafd_helper[ 'sections' ][] = $section_id;
	}

	/**
	 * Register the setting
	 */
	add_settings_field( $field_id, $field_name, $field_callback, 'pafd_settings', $section_id, $args);
}

/**
 * Outputs input fields for settings fields
 * 
 * The function is used as a default callback function in add_settings_field()
 * 
 * @param array $args parameters that were given to pafd_add_settings_field(), 
 * 	if the first arguement ends with _color, a color picker will be used
 */
function pafd_setting_html( $args ) {

	global $pafd_options, $pafd_textdomain;

	$field_html_id = "pafd_setting_${args['field_id']}";
	$field_html_name = "pafd_options[pafd_setting_${args['field_id']}]";
	$field_html_value = '';
	$use_color_picker = '_color' === substr( $field_html_id, -6 );
	$use_textarea = '_code' === substr( $field_html_id, -5 );

	if ( ! empty ( $pafd_options["pafd_setting_${args['field_id']}"] ) ) {
		$field_html_value = $pafd_options["pafd_setting_${args['field_id']}"];
	}
	if ( $use_textarea ) {
		printf( '<textarea id="%s" name="%s" cols="50" rows="8" class="large-text code" >%s</textarea>', $field_html_id, $field_html_name, $field_html_value);
	} else {
		printf( '<input id="%s" name="%s" size="40" type="text" value="%s" %s/>', $field_html_id, $field_html_name, $field_html_value, $use_color_picker ? 'class="pafd-color-picker"' : '' );
	}
}

function pafd_setting_columns_html() {

	global $pafd_helper, $pafd_options, $pafd_textdomain;

	$pafd_setting_columns = K::get_var( 'pafd_setting_columns', $pafd_options, array() );

	$columns = array(
		'version' => 'dummy',
		// 'version_id' => 'dummy',
		'name' => 'dummy',
		'file' => 'dummy',
		'author' => 'dummy',
		'description' => 'dummy',
		'size' => 'dummy',
		'uploaded' => 'dummy',
		'downloaded' => 'dummy',
		'download' => 'dummy',
	);

	$columns_sorted = $pafd_setting_columns + $columns;

	echo '<div id="pafd_setting_columns">';
	foreach ( $columns_sorted as $column => $unused ) {
		// Make sure the column is defined by the plugin
		if( ! K::get_var( $column, $columns ) ) {
			continue;
		}
		// Get the column name from the helper
		$column_name = $pafd_helper[ 'strings' ][ $column ];
		// Output the checkbox
		K::input(
			"pafd_options[pafd_setting_columns][$column]"
			, array(
				'type' => 'checkbox',
				'checked' => K::get_var( $column, $pafd_setting_columns ),
			)
			, array(
				'format' => sprintf(
					'<div class="pafd-movable button">%s<label>:input%s</label>%s</div>'
					, '<a href="#" class="pafd-move_up"><div class="dashicons dashicons-arrow-left" style="vertical-align:middle"></div></a>'
					, $column_name
					, '<a href="#" class="pafd-move_down"><div class="dashicons dashicons-arrow-right" style="vertical-align:middle"></div></a>'
				),
			)
		);
	}
	echo '</div>';
}

function pafd_setting_download_link_html() {

	global $pafd_helper, $pafd_options, $pafd_textdomain;

	$choices = array(
		'text' => __( 'Text', $pafd_textdomain ),
		'circle' => __( 'Circle icon', $pafd_textdomain ),
		'square' => __( 'Square icon', $pafd_textdomain ),
	);

	foreach ( $choices as $value => $text ) {
		// Output the radio
		K::input(
			"pafd_options[pafd_setting_download_link]"
			, array(
				'type' => 'radio',
				'value' => $value,
				'checked' => ( $value === K::get_var( 'pafd_setting_download_link', $pafd_options ) )
					? 'checked'
					: null
				,
			)
			, array(
				'format' => sprintf(
					'<div class="button pafd-movable"><label>:input%s</label></div>'
					, $text
				),
			)
		);
	}
}

function pafd_setting_order_by_html() {

	global $pafd_options, $pafd_textdomain;

	K::select(
		'pafd_options[pafd_setting_order_by]'
		, array()
		, array(
			'options' => array(
				'title'      => __( 'Title', $pafd_textdomain ),
				'downloaded' => __( 'Downloads', $pafd_textdomain ),
				// 'created'    => __( 'Creation date', $pafd_textdomain ),
				// 'updated'    => __( 'Last modification date', $pafd_textdomain ),
			),
			'selected' => K::get_var( 'pafd_setting_order_by', $pafd_options ),
		)
	);
}

function pafd_setting_show_all_revisions_html() {

	global $pafd_options, $pafd_textdomain;

	K::input(
		'pafd_options[pafd_setting_show_all_revisions]'
		, array(
			'type' => 'checkbox',
			'checked' => K::get_var( 'pafd_setting_show_all_revisions', $pafd_options ) 
				? 'checked'
				: null
			,
		)
	);
}

function pafd_setting_show_file_status_html() {

	global $pafd_options, $pafd_textdomain;

	K::input(
		'pafd_options[pafd_setting_show_file_status]'
		, array(
			'type' => 'checkbox',
			'checked' => K::get_var( 'pafd_setting_show_file_status', $pafd_options ) 
				? 'checked'
				: null
			,
		)
	);
}

function pafd_setting_hide_table_header_html() {

	global $pafd_options, $pafd_textdomain;

	K::input(
		'pafd_options[pafd_setting_hide_table_header]'
		, array(
			'type' => 'checkbox',
			'checked' => K::get_var( 'pafd_setting_hide_table_header', $pafd_options ) 
				? 'checked'
				: null
			,
		)
	);
}

function pafd_setting_show_icons_html() {

	global $pafd_options, $pafd_textdomain;

	K::input(
		'pafd_options[pafd_setting_show_icons]'
		, array(
			'type' => 'checkbox',
			'checked' => K::get_var( 'pafd_setting_show_icons', $pafd_options ) 
				? 'checked'
				: null
			,
		)
		, array(
		)
	);
}