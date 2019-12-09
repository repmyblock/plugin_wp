<?php
/**
 * Login / Register Functions
 *
 * @package     WalkTheCounty
 * @subpackage  Functions/Login
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Login Form
 *
 * @since 1.0
 * @global       $walkthecounty_login_redirect
 * @global       $walkthecounty_logout_redirect
 *
 * @param string $login_redirect  Login redirect page URL
 * @param string $logout_redirect Logout redirect page URL
 *
 * @return string Login form
 */
function walkthecounty_login_form( $login_redirect = '', $logout_redirect = '' ) {

	if ( empty( $login_redirect ) ) {
		$login_redirect = add_query_arg( 'walkthecounty-login-success', 'true', walkthecounty_get_history_page_uri() );
	}

	if ( empty( $logout_redirect ) ) {
		$logout_redirect = add_query_arg( 'walkthecounty-logout-success', 'true', walkthecounty_get_current_page_url() );
	}

	// Add user_logout action to logout url.
	$logout_redirect = add_query_arg(
		array(
			'walkthecounty_action'          => 'user_logout',
			'walkthecounty_logout_nonce'    => wp_create_nonce( 'walkthecounty-logout-nonce' ),
			'walkthecounty_logout_redirect' => urlencode( $logout_redirect ),
		),
		home_url( '/' )
	);

	ob_start();

	walkthecounty_get_template(
		'shortcode-login',
		array(
			'walkthecounty_login_redirect'  => $login_redirect,
			'walkthecounty_logout_redirect' => $logout_redirect,
		)
	);

	return apply_filters( 'walkthecounty_login_form', ob_get_clean() );
}

/**
 * Registration Form
 *
 * @since 2.0
 * @global       $walkthecounty_register_redirect
 *
 * @param string $redirect Redirect page URL
 *
 * @return string Register form
 */
function walkthecounty_register_form( $redirect = '' ) {
	if ( empty( $redirect ) ) {
		$redirect = walkthecounty_get_current_page_url();
	}

	ob_start();

	if ( ! is_user_logged_in() ) {
		walkthecounty_get_template(
			'shortcode-register',
			array(
				'walkthecounty_register_redirect' => $redirect,
			)
		);
	}

	return apply_filters( 'walkthecounty_register_form', ob_get_clean() );
}

/**
 * Process Login Form
 *
 * @since 1.0
 *
 * @param array $data Data sent from the login form
 *
 * @return void
 */
function walkthecounty_process_login_form( $data ) {

	if ( wp_verify_nonce( $data['walkthecounty_login_nonce'], 'walkthecounty-login-nonce' ) ) {

		// Set Receipt Access Session.
		if ( ! empty( $_GET['donation_id'] ) ) {
			WalkTheCounty()->session->set( 'receipt_access', true );
		}

		$user_data = get_user_by( 'login', $data['walkthecounty_user_login'] );

		if ( ! $user_data ) {
			$user_data = get_user_by( 'email', $data['walkthecounty_user_login'] );
		}

		if ( $user_data ) {

			$user_id = $user_data->ID;

			if ( wp_check_password( $data['walkthecounty_user_pass'], $user_data->user_pass, $user_id ) ) {
				walkthecounty_log_user_in( $user_data->ID, $data['walkthecounty_user_login'], $data['walkthecounty_user_pass'] );
			} else {
				walkthecounty_set_error( 'password_incorrect', __( 'The password you entered is incorrect.', 'walkthecounty' ) );
			}
		} else {
			walkthecounty_set_error( 'username_incorrect', __( 'The username you entered does not exist.', 'walkthecounty' ) );
		}

		// Check for errors and redirect if none present.
		$errors = walkthecounty_get_errors();

		if ( ! $errors ) {
			$redirect = apply_filters( 'walkthecounty_login_redirect', $data['walkthecounty_login_redirect'], $user_id );
			wp_redirect( $redirect );
			walkthecounty_die();
		}
	}
}

add_action( 'walkthecounty_user_login', 'walkthecounty_process_login_form' );


/**
 * Process User Logout
 *
 * @since 1.0
 *
 * @param array $data Data sent from the walkthecounty login form page
 *
 * @return void
 */
function walkthecounty_process_user_logout( $data ) {
	if ( wp_verify_nonce( $data['walkthecounty_logout_nonce'], 'walkthecounty-logout-nonce' ) && is_user_logged_in() ) {

		// Prevent occurring of any custom action on wp_logout.
		remove_all_actions( 'wp_logout' );

		/**
		 * Fires before processing user logout.
		 *
		 * @since 1.0
		 */
		do_action( 'walkthecounty_before_user_logout' );

		// Logout user.
		wp_logout();

		/**
		 * Fires after processing user logout.
		 *
		 * @since 1.0
		 */
		do_action( 'walkthecounty_after_user_logout' );

		wp_redirect( $data['walkthecounty_logout_redirect'] );
		walkthecounty_die();
	}
}

add_action( 'walkthecounty_user_logout', 'walkthecounty_process_user_logout' );

/**
 * Log User In
 *
 * @since 1.0
 *
 * @param int    $user_id    User ID
 * @param string $user_login Username
 * @param string $user_pass  Password
 *
 * @return bool
 */
function walkthecounty_log_user_in( $user_id, $user_login, $user_pass ) {

	if ( $user_id < 1 ) {
		return false;
	}

	wp_set_auth_cookie( $user_id );
	wp_set_current_user( $user_id, $user_login );

	/**
	 * Fires after the user has successfully logged in.
	 *
	 * @since 1.0
	 *
	 * @param string $user_login Username.
	 * @param WP_User $$user      WP_User object of the logged-in user.
	 */
	do_action( 'wp_login', $user_login, get_userdata( $user_id ) );

	/**
	 * Fires after walkthecounty user has successfully logged in.
	 *
	 * @since 1.0
	 *
	 * @param int    $$user_id   User id.
	 * @param string $user_login Username.
	 * @param string $user_pass  User password.
	 */
	do_action( 'walkthecounty_log_user_in', $user_id, $user_login, $user_pass );
}


/**
 * Process Register Form
 *
 * @since 2.0
 *
 * @param array $data Data sent from the register form
 *
 * @return bool
 */
function walkthecounty_process_register_form( $data ) {

	if ( is_user_logged_in() ) {
		return false;
	}

	if ( empty( $_POST['walkthecounty_register_submit'] ) ) {
		return false;
	}

	/**
	 * Fires before processing user registration.
	 *
	 * @since 1.0
	 */
	do_action( 'walkthecounty_pre_process_register_form' );

	if ( empty( $data['walkthecounty_user_login'] ) ) {
		walkthecounty_set_error( 'empty_username', esc_html__( 'Invalid username.', 'walkthecounty' ) );
	}

	if ( username_exists( $data['walkthecounty_user_login'] ) ) {
		walkthecounty_set_error( 'username_unavailable', esc_html__( 'Username already taken.', 'walkthecounty' ) );
	}

	if ( ! validate_username( $data['walkthecounty_user_login'] ) ) {
		walkthecounty_set_error( 'username_invalid', esc_html__( 'Invalid username.', 'walkthecounty' ) );
	}

	if ( email_exists( $data['walkthecounty_user_email'] ) ) {
		walkthecounty_set_error( 'email_unavailable', esc_html__( 'Email address already taken.', 'walkthecounty' ) );
	}

	if ( empty( $data['walkthecounty_user_email'] ) || ! is_email( $data['walkthecounty_user_email'] ) ) {
		walkthecounty_set_error( 'email_invalid', esc_html__( 'Invalid email.', 'walkthecounty' ) );
	}

	if ( ! empty( $data['walkthecounty_payment_email'] ) && $data['walkthecounty_payment_email'] != $data['walkthecounty_user_email'] && ! is_email( $data['walkthecounty_payment_email'] ) ) {
		walkthecounty_set_error( 'payment_email_invalid', esc_html__( 'Invalid payment email.', 'walkthecounty' ) );
	}

	if ( empty( $_POST['walkthecounty_user_pass'] ) ) {
		walkthecounty_set_error( 'empty_password', esc_html__( 'Please enter a password.', 'walkthecounty' ) );
	}

	if ( ( ! empty( $_POST['walkthecounty_user_pass'] ) && empty( $_POST['walkthecounty_user_pass2'] ) ) || ( $_POST['walkthecounty_user_pass'] !== $_POST['walkthecounty_user_pass2'] ) ) {
		walkthecounty_set_error( 'password_mismatch', esc_html__( 'Passwords don\'t match.', 'walkthecounty' ) );
	}

	/**
	 * Fires while processing user registration.
	 *
	 * @since 1.0
	 */
	do_action( 'walkthecounty_process_register_form' );

	// Check for errors and redirect if none present
	$errors = walkthecounty_get_errors();

	if ( empty( $errors ) ) {

		$redirect = apply_filters( 'walkthecounty_register_redirect', $data['walkthecounty_redirect'] );

		walkthecounty_register_and_login_new_user( array(
			'user_login'      => $data['walkthecounty_user_login'],
			'user_pass'       => $data['walkthecounty_user_pass'],
			'user_email'      => $data['walkthecounty_user_email'],
			'user_registered' => date( 'Y-m-d H:i:s' ),
			'role'            => get_option( 'default_role' ),
		) );

		wp_redirect( $redirect );
		walkthecounty_die();
	}
}

add_action( 'walkthecounty_user_register', 'walkthecounty_process_register_form' );


/**
 * Email access login form.
 *
 * @since 1.8.17
 *
 * @return bool
 */
function walkthecounty_email_access_login() {

	// Verify nonce.
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'walkthecounty' ) ) {
		return false;
	}

	// Need email to proceed.
	$email = isset( $_POST['walkthecounty_email'] ) ? walkthecounty_clean( $_POST['walkthecounty_email'] ) : '';
	if ( empty( $email ) ) {
		walkthecounty_set_error( 'walkthecounty_empty_email', __( 'Please enter the email address you used for your donation.', 'walkthecounty' ) );
	}

	$recaptcha_key    = walkthecounty_get_option( 'recaptcha_key' );
	$recaptcha_secret = walkthecounty_get_option( 'recaptcha_secret' );
	$enable_recaptcha = ( walkthecounty_is_setting_enabled( walkthecounty_get_option( 'enable_recaptcha' ) ) ) && ! empty( $recaptcha_key ) && ! empty( $recaptcha_secret ) ? true : false;

	// Use reCAPTCHA.
	if ( $enable_recaptcha ) {

		$args = array(
			'secret'   => $recaptcha_secret,
			'response' => $_POST['g-recaptcha-response'],
			'remoteip' => $_POST['walkthecounty_ip'],
		);

		if ( ! empty( $args['response'] ) ) {
			$request = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array(
				'body' => $args,
			) );
			if ( ! is_wp_error( $request ) || 200 == wp_remote_retrieve_response_code( $request ) ) {

				$response = json_decode( $request['body'], true );

				// reCAPTCHA fail.
				if ( ! $response['success'] ) {
					walkthecounty_set_error( 'walkthecounty_recaptcha_test_failed', apply_filters( 'walkthecounty_recaptcha_test_failed_message', __( 'reCAPTCHA test failed.', 'walkthecounty' ) ) );
				}
			} else {

				// Connection issue.
				walkthecounty_set_error( 'walkthecounty_recaptcha_connection_issue', apply_filters( 'walkthecounty_recaptcha_connection_issue_message', __( 'Unable to connect to reCAPTCHA server.', 'walkthecounty' ) ) );

			}  // End if().
		} else {

			walkthecounty_set_error( 'walkthecounty_recaptcha_failed', apply_filters( 'walkthecounty_recaptcha_failed_message', __( 'It looks like the reCAPTCHA test has failed.', 'walkthecounty' ) ) );

		}  // End if().
	}  // End if().

	// If no errors or only expired token key error - then send email.
	if ( ! walkthecounty_get_errors() ) {

		$donor = WalkTheCounty()->donors->get_donor_by( 'email', $email );
		WalkTheCounty()->email_access->init();

		// Verify that donor object is present and donor is connected with its user profile or not.
		if ( is_object( $donor ) ) {

			// Verify that email can be sent.
			if ( ! WalkTheCounty()->email_access->can_send_email( $donor->id ) ) {

				$_POST['email-access-exhausted'] = true;

				return false;

			} else {
				// Send the email. Requests not
				$email_sent = WalkTheCounty()->email_access->send_email( $donor->id, $donor->email );

				if ( ! $email_sent ) {
					walkthecounty_set_error( 'walkthecounty_email_access_send_issue', __( 'Unable to send email. Please try again.', 'walkthecounty' ) );
					return false;
				}

				$_POST['email-access-sent'] = true;

				return true;
			}
		} else {

			walkthecounty_set_error( 'walkthecounty-no-donations', __( 'We were unable to find any donations associated with the email address provided. Please try again using another email.', 'walkthecounty' ) );

		}
	} // End if().

}

add_action( 'walkthecounty_email_access_form_login', 'walkthecounty_email_access_login' );
