<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;
/**
 * EMD Session Manager
 *
 * @package     EMD
 * @copyright   Copyright (c) 2016,  Emarket Design
 * @since       5.3
 */
// let users change the session cookie name
if( ! defined( 'EMD_WP_SESSION_COOKIE' ) ) {
	define( 'EMD_WP_SESSION_COOKIE', '_emd_wp_session' );
}

if ( ! class_exists( 'Recursive_ArrayAccess' ) ) {
	include 'includes/class-recursive-arrayaccess.php';
}

// Include utilities class
if ( ! class_exists( 'EMD_WP_Session_Utils' ) ) {
	include 'includes/class-emd-wp-session-utils.php';
}

// Only include the functionality if it's not pre-defined.
if ( ! class_exists( 'EMD_WP_Session' ) ) {
	include 'includes/class-emd-wp-session.php';
	include 'includes/emd-wp-session.php';
}

// Create the required table.
add_action('admin_init', 'create_emd_sessions_table');
add_action('emd_wp_session_init', 'create_emd_sessions_table');

/**
 * Create the new table for housing session data if we're not still using
 * the legacy options mechanism. This code should be invoked before
 * instantiating the singleton session manager to ensure the table exists
 * before trying to use it.
 *
 */
function create_emd_sessions_table() {
    if (defined('EMD_WP_SESSION_USE_OPTIONS') && EMD_WP_SESSION_USE_OPTIONS) {
        return;
    }

	$current_db_version = '1';
	$created_db_version = get_option('emd_session_db_version', '0' );

	global $wpdb;
	$table_name = $wpdb->prefix . 'emd_sessions';
	if ( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name ) {
		$collate = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		$table = "CREATE TABLE {$wpdb->prefix}emd_sessions (
		  session_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		  session_key char(32) NOT NULL,
		  session_value LONGTEXT NOT NULL,
		  session_expiry BIGINT(20) UNSIGNED NOT NULL,
		  PRIMARY KEY  (session_key),
		  UNIQUE KEY session_id (session_id)
		) $collate;";
	
		require_once( EMD_ADMIN_DIR . '/includes/upgrade.php' );
		dbDelta( $table );

		add_option('emd_session_db_version', '1', '', 'no');

		EMD_WP_Session_Utils::delete_all_sessions_from_options();
	}
}
