<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;
/**
 * WordPress session managment.
 *
 * Standardizes WordPress session data and uses either database transients or in-memory caching
 * for storing user session information.
 *
 */

/**
 * Return the current cache expire setting.
 *
 * @return int
 */
function emd_wp_session_cache_expire() {
	$emd_wp_session = EMD_WP_Session::get_instance();

	return $emd_wp_session->cache_expiration();
}

/**
 * Alias of emd_wp_session_write_close()
 */
function emd_wp_session_commit() {
	emd_wp_session_write_close();
}

/**
 * Load a JSON-encoded string into the current session.
 *
 * @param string $data
 */
function emd_wp_session_decode( $data ) {
	$emd_wp_session = EMD_WP_Session::get_instance();

	return $emd_wp_session->json_in( $data );
}

/**
 * Encode the current session's data as a JSON string.
 *
 * @return string
 */
function emd_wp_session_encode() {
	$emd_wp_session = EMD_WP_Session::get_instance();

	return $emd_wp_session->json_out();
}

/**
 * Regenerate the session ID.
 *
 * @param bool $delete_old_session
 *
 * @return bool
 */
function emd_wp_session_regenerate_id( $delete_old_session = false ) {
	$emd_wp_session = EMD_WP_Session::get_instance();

	$emd_wp_session->regenerate_id( $delete_old_session );

	return true;
}

/**
 * Start new or resume existing session.
 *
 * Resumes an existing session based on a value sent by the _emd_wp_session cookie.
 *
 * @return bool
 */
function emd_wp_session_start() {
	$emd_wp_session = EMD_WP_Session::get_instance();

    /**
     * Session has started
     *
     * Allow other plugins to hook in once the session store has been
     * initialized.
     */
	do_action( 'emd_wp_session_start' );

	return $emd_wp_session->session_started();
}
if ( ! defined( 'WP_CLI' ) || false === WP_CLI ) {
	add_action( 'plugins_loaded', 'emd_wp_session_start' );
}

/**
 * Return the current session status.
 *
 * @return int
 */
function emd_wp_session_status() {
	$emd_wp_session = EMD_WP_Session::get_instance();

	if ( $emd_wp_session->session_started() ) {
		return PHP_SESSION_ACTIVE;
	}

	return PHP_SESSION_NONE;
}

/**
 * Unset all session variables.
 */
function emd_wp_session_unset() {
	$emd_wp_session = EMD_WP_Session::get_instance();

	$emd_wp_session->reset();
}

/**
 * Write session data and end session
 */
function emd_wp_session_write_close() {
	$emd_wp_session = EMD_WP_Session::get_instance();

	$emd_wp_session->write_data();

    /**
     * Session has been written to the database
     *
     * The session needs to be persisted to the database automatically
     * when the request closes. Once data has been written, other operations
     * might need to run to clean things up and purge memory. Give them the
     * opportunity to clean up after commit.
     */
	do_action( 'emd_wp_session_commit' );
}
if ( ! defined( 'WP_CLI' ) || false === WP_CLI ) {
	add_action( 'shutdown', 'emd_wp_session_write_close' );
}

/**
 * Clean up expired sessions by removing data and their expiration entries from
 * the WordPress options table.
 *
 * This method should never be called directly and should instead be triggered as part
 * of a scheduled task or cron job.
 */
function emd_wp_session_cleanup() {
	if ( defined( 'WP_SETUP_CONFIG' ) ) {
		return;
	}

	if ( ! defined( 'WP_INSTALLING' ) ) {
		/**
		 * Determine the size of each batch for deletion.
		 *
		 * @param int
		 */
		$batch_size = apply_filters( 'emd_wp_session_delete_batch_size', 1000 );

		// Delete a batch of old sessions
		EMD_WP_Session_Utils::delete_old_sessions( $batch_size );
	}

    /**
     * Allow other plugins to hook in to the garbage collection process.
     */
	do_action( 'emd_wp_session_cleanup' );
}
add_action( 'emd_wp_session_garbage_collection', 'emd_wp_session_cleanup' );

/**
 * Register the garbage collector as a twice daily event.
 */
function emd_wp_session_register_garbage_collection() {
	if ( ! wp_next_scheduled( 'emd_wp_session_garbage_collection' ) ) {
		wp_schedule_event( time(), 'hourly', 'emd_wp_session_garbage_collection' );
	}
}
add_action( 'wp', 'emd_wp_session_register_garbage_collection' );
