<?php
/**
 * Login/Password Functions
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       WPAS 4.0
 */
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

add_action('init', 'emd_form_builder_login_actions');

function emd_form_builder_login_actions(){
	$emd_action = isset( $_POST['emd_action'] ) ? sanitize_text_field( wp_unslash( $_POST['emd_action'] ) ) : '';

	if ( ! empty( $emd_action ) && preg_match( '/_user_register/', $emd_action )
		&& isset( $_POST['emd_register_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['emd_register_nonce'] ) ), 'emd-register-nonce' ) ) {
		$app = preg_replace( '/_user_register/', '', $emd_action );
		emd_process_register( $_POST, $app );
	}
	// LOGIN
	if ( 'login' === $emd_action ) {
		$user_login = isset( $_POST['emd_user_login'] ) ? wp_unslash( $_POST['emd_user_login'] ) : '';
		// Password is intentionally not unslashed or sanitized, same as WordPress core wp_signon().
		$user_pass = isset( $_POST['emd_user_pass'] ) ? $_POST['emd_user_pass'] : '';
		// If the user wants ssl but the session is not ssl, force a secure cookie.
		$secure_cookie = '';
		if ( '' !== $user_login && ! force_ssl_admin() ) {
			$user_name = sanitize_user( $user_login );
			$user      = get_user_by( 'login', $user_name );

			if ( ! $user && false !== strpos( $user_name, '@' ) ) {
				$user = get_user_by( 'email', $user_name );
			}
			if ( $user && get_user_option( 'use_ssl', $user->ID ) ) {
				$secure_cookie = true;
				force_ssl_admin( true );
			}
		}
		$redirect_to = '';
		if ( ! empty( $_REQUEST['redirect_to'] ) ) {
			// esc_url_raw() keeps & intact for redirects; wp_validate_redirect() blocks external (open) redirects.
			$redirect_to = wp_validate_redirect( esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) ), '' );
			// Redirect to HTTPS if user wants SSL.
			if ( $secure_cookie && false !== strpos( $redirect_to, 'wp-admin' ) ) {
				$redirect_to = preg_replace( '|^http://|', 'https://', $redirect_to );
			}
		}
		$reauth = ! empty( $_REQUEST['reauth'] );

		$user = wp_signon(
			array(
				'user_login'    => $user_login,
				'user_password' => $user_pass,
			),
			$secure_cookie
		);

		if ( empty( $_COOKIE[ LOGGED_IN_COOKIE ] ) ) {
			if ( headers_sent() ) {
				/* translators: 1: Browser cookie documentation URL, 2: Support forums URL */
				$user = new WP_Error( 'test_cookie', sprintf( __( '<strong>ERROR</strong>: Cookies are blocked due to unexpected output. For help, please see <a href="%1$s">this documentation</a> or try the <a href="%2$s">support forums</a>.','youtube-showcase' ),
					__( 'https://codex.wordpress.org/Cookies', 'youtube-showcase' ), __( 'https://wordpress.org/support/','youtube-showcase' ) ) );
			} elseif ( isset( $_POST['testcookie'] ) && empty( $_COOKIE[ TEST_COOKIE ] ) ) {
				// If cookies are disabled we can't log in even with a valid user+pass
				/* translators: 1: Browser cookie documentation URL */
				$user = new WP_Error( 'test_cookie', sprintf( __( '<strong>ERROR</strong>: Cookies are blocked or not supported by your browser. You must <a href="%s">enable cookies</a> to use WordPress.','youtube-showcase' ),
					__( 'https://codex.wordpress.org/Cookies','youtube-showcase' ) ) );
			}
		}
		$err_code = '';
		if ( is_wp_error( $user ) ) {
			$err_code = $user->get_error_code();
		} elseif ( ! $reauth ) {
			$emd_status = get_user_meta( $user->ID, 'emd_status', true );
			if ( 'draft' === $emd_status ) {
				// Email not validated yet. wp_signon() already set the auth cookie, so remove it.
				wp_clear_auth_cookie();
				$err_code = 'validate_email';
			} else {
				if ( ! empty( $redirect_to ) ) {
					// Request value is already validated above; settings value is admin-defined.
					wp_redirect( $redirect_to );
					exit;
				}
				$redirect_to = home_url();
				wp_safe_redirect( $redirect_to );
				exit;
			}
		}

		$url = remove_query_arg( 'emd_action' );
		if ( in_array( $err_code, array( 'incorrect_password', 'invalid_username', 'invalid_email', 'invalidcombo' ), true ) ) {
			$err_code = 'invalid_login';
		}
		if ( ! empty( $err_code ) ) {
			$url = add_query_arg( array( 'emd_error' => $err_code ), $url );
		}
		wp_safe_redirect( $url );
		exit;
	}

	// FOR REDIRECTS WHEN USER LOGGED IN
	// fix logout error from woocommerce my account page, changed redirect_to to emd_redirect_to
	if ( ! empty( $_GET['emd_redirect_to'] ) && is_user_logged_in() ) {
		wp_safe_redirect( esc_url_raw( wp_unslash( $_GET['emd_redirect_to'] ) ) );
		exit;
	}
}
/**
 * Process registration form  which displays on form pages
 *
 * @since WPAS 5.3
 * @param string $app
 * @param array $data
 *
 */
function emd_process_register($data,$app){
	if( is_user_logged_in() ) {
		return;
	}
	// $app comes from the posted form, and strtoupper($app)() calls a function by that name.
	// Only allow real EMD apps (they always have an {app}_ent_list option), otherwise any
	// visitor could make the site run any zero-argument PHP or WordPress function.
	$app      = sanitize_key( $app );
	$fname    = strtoupper( $app );
	$ent_list = ! empty( $app ) ? get_option( $app . '_ent_list' ) : false;
	if ( empty( $ent_list ) || ! is_array( $ent_list ) || ! function_exists( $fname ) ) {
		return;
	}
	$session_class = call_user_func( $fname );
	if ( ! is_object( $session_class ) || ! isset( $session_class->session ) ) {
		return;
	}

	$user_login = isset( $data['emd_user_login'] ) ? trim( wp_unslash( $data['emd_user_login'] ) ) : '';
	$user_email = isset( $data['emd_user_email'] ) ? sanitize_email( wp_unslash( $data['emd_user_email'] ) ) : '';
	// Passwords are kept raw (not unslashed) to match how wp_signon() checks them in the login handler above.
	$user_pass  = isset( $data['emd_user_pass'] ) ? $data['emd_user_pass'] : '';
	$user_pass2 = isset( $data['emd_user_pass2'] ) ? $data['emd_user_pass2'] : '';

	// First error wins, so the user sees the problems in form order.
	$error = '';
	if ( '' === $user_login || ! validate_username( $user_login ) ) {
		$error = __( 'Invalid username', 'youtube-showcase' );
	} elseif ( username_exists( $user_login ) ) {
		$error = __( 'Username already taken', 'youtube-showcase' );
	} elseif ( empty( $user_email ) || ! is_email( $user_email ) ) {
		$error = __( 'Invalid Email', 'youtube-showcase' );
	} elseif ( email_exists( $user_email ) ) {
		$error = __( 'Email address already taken', 'youtube-showcase' );
	} elseif ( '' === $user_pass ) {
		$error = __( 'Please enter a password', 'youtube-showcase' );
	} elseif ( $user_pass !== $user_pass2 ) {
		$error = __( 'Passwords do not match', 'youtube-showcase' );
	}

	if ( ! empty( $error ) ) {
		$session_class->session->set( 'login_reg_errors', $error );
		return;
	}
	$user_args = apply_filters( 'emd_insert_user_args', array(
		'user_login'      => $user_login,
		'user_pass'       => $user_pass,
		'user_email'      => $user_email,
		'first_name'      => isset( $data['emd_user_first'] ) ? sanitize_text_field( wp_unslash( $data['emd_user_first'] ) ) : '',
		'last_name'       => isset( $data['emd_user_last'] ) ? sanitize_text_field( wp_unslash( $data['emd_user_last'] ) ) : '',
		'user_registered' => gmdate( 'Y-m-d H:i:s' ),
	), $data );

	// Front-end registration must never mint a privileged account, even if a
	// filter or bad option value tried to. Fall back to subscriber.
	$user_args['role'] = emd_get_safe_register_role();

	// Insert new user
	$user_id = wp_insert_user( $user_args );

	// Validate inserted user
	if ( is_wp_error( $user_id ) ) {
		$session_class->session->set( 'login_reg_errors', $user_id->get_error_message() );
		return;
	}
	$user = get_userdata( $user_id );
	if ( ! $user ) {
		return;
	}

	$signon = wp_signon(
		array(
			// Use the stored login: wp_insert_user() may have sanitized what was posted.
			'user_login'    => $user->user_login,
			'user_password' => $user_pass,
		),
		is_ssl()
	);
	if ( is_wp_error( $signon ) ) {
		// Something on the authenticate filter refused the sign on. Set the cookie directly
		// so registration still ends up logged in, and leave a note for debugging.
		error_log( 'emd_process_register: wp_signon failed for user ' . $user_id . ': ' . $signon->get_error_code() );
		wp_set_auth_cookie( $user_id, true, is_ssl() );
		wp_set_current_user( $user_id, $user->user_login );
		do_action( 'wp_login', $user->user_login, $user );
	} else {
		// wp_signon() sets the auth cookie and fires wp_login itself.
		//wp_set_current_user( $signon->ID, $signon->user_login );
	}

	$emd_status = get_user_meta( $user->ID, 'emd_status', true );
	if ( 'draft' === $emd_status ) {
		// Email not validated yet. wp_signon() already set the auth cookie, so remove it.
		wp_clear_auth_cookie();
		$err_code = 'validate_email';
	} else {
		// Validate the redirect target: same-host only, no open redirect.
		$redirect = isset( $data['emd_redirect'] ) ? esc_url_raw( wp_unslash( $data['emd_redirect'] ) ) : '';
		$redirect = wp_validate_redirect( $redirect, home_url( '/' ) );
		if ( empty( $redirect ) ) {
			// An empty target would make wp_safe_redirect() do nothing and leave a blank page.
			$redirect = home_url( '/' );
		}
		wp_safe_redirect( $redirect );
		exit;
	}
}
/**
 * Role for front-end registrations. Uses the site's default role only if it
 * exists and has no admin-level or editor-level capabilities.
 *
 * @return string
 */
function emd_get_safe_register_role() {
	$role_name = get_option( 'default_role' );
	$role      = $role_name ? get_role( $role_name ) : null;
	if ( ! $role ) {
		return 'subscriber';
	}
	$dangerous_caps = array(
		'manage_options', 'edit_users', 'create_users', 'promote_users', 'delete_users',
		'install_plugins', 'activate_plugins', 'edit_plugins', 'edit_themes', 'edit_theme_options',
		'unfiltered_html', 'edit_others_posts', 'edit_others_pages',
	);
	foreach ( $dangerous_caps as $cap ) {
		if ( $role->has_cap( $cap ) ) {
			return 'subscriber';
		}
	}
	return $role_name;
}
