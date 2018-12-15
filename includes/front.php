<?php

/**
 * Front-end instructions
 * 
 * This file regroups front-end instructions like buiding the pa_file post
 * 
 * @package PAFD
 */

add_action( 'init',               'pafd_action_add_shortcode') ;
add_filter( 'the_content',        'pafd_filter_post_content' );
add_action( 'the_posts',          'pafd_filter_download_revision', 10, 2 );
