<?php

/**
 * Global instructions
 * 
 * This file regroups global instructions like CPT registration
 * 
 * @package PAFD
 */

/**
 * Register CPTs and their taxonomies
 */
add_action( 'init', 'pafd_action_cpt' );

/**
 * Load textdomain
 */
add_action( 'plugins_loaded', 'pafd_action_load_textdomain' );
