<?php

/**
 * Formats bytes to bytes, KB, MB, GB or TB
 */
function pafd_format_bytes( $bytes, $precision = 2) {
    $units = array( 'B', 'KB', 'MB', 'GB', 'TB' );

    $bytes = max( $bytes, 0);
    $pow = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
    $pow = min( $pow, count( $units ) - 1 );
    $bytes /= ( 1 << ( 10 * $pow ) );

    return round( $bytes, $precision ) . ' ' . $units[ $pow ];
}

/**
 * Formats a date and time
 */
function pafd_datetime( $timestamp ) {
	$date = get_date_from_gmt ( date( 'Y-m-d H:i:s', ( int ) $timestamp ), get_option( 'date_format' ) );
	$time = get_date_from_gmt ( date( 'Y-m-d H:i:s', ( int ) $timestamp ), get_option( 'time_format' ) );
	return sprintf( '%s &ndash; %s', $date, $time );
}