<?php

global $pafd_textdomain;

$strings = 'tinyMCE.addI18n(
	"' . _WP_Editors::$mce_locale . '.pafd",
	{
		insertFile: "' . esc_js( __( 'Insert File', $pafd_textdomain ) ) . '"
		, singleFile: "' . esc_js( __( 'Single File', $pafd_textdomain ) ) . '"
		, fileCategoryOrCategories: "' . esc_js( __( 'File Category', $pafd_textdomain ) ) . '"
		, chooseFiles: "' . esc_js( __( 'Choose Files', $pafd_textdomain ) ) . '"
		, chooseCategories: "' . esc_js( __( 'Choose Categories', $pafd_textdomain ) ) . '"
	}
)';