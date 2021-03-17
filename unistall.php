<?php
/**
 * ShMapper
 *
 * @package teplitsa
 */

if ( ! defined('WP_UNINSTALL_PLUGIN') ) {
	exit;
}

global $wpdb;
if ( ! is_multisite() ) {
	delete_option( SHMAPPER );
} else {
	$blog_ids         = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
	$original_blog_id = get_current_blog_id();
	foreach ( $blog_ids as $blog_id ) {
		switch_to_blog( $blog_id );
		delete_site_option( SHMAPPER );
	}
	switch_to_blog( $original_blog_id );
}
