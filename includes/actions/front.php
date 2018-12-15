<?php

/**
 * Frontend actions
 * 
 * Actions (functions) used by the plugin ine the front-end
 *
 * @package PAFD
 */

/**
 * Adds the shortcode
 */
function pafd_action_add_shortcode() {
	add_shortcode( 'pa_file',         'pafd_shortcode' );
}
