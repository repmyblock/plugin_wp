<?php
/**
 * User Functions
 *
 * Functions related to users / donors
 *
 * @package     WalkTheCounty
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Users Donations
 *
 * Retrieves a list of all donations by a specific user.
 *
 * @param int    $user       User ID or email address.
 * @param int    $number     Number of donations to retrieve.
 * @param bool   $pagination Enable/Disable Pagination.
 * @param string $status     Donation Status.
 *
 * @since  1.0
 *
 * @return bool|array List of all user donations.
 */
function walkthecounty_get_users_donations( $user = 0, $number = 20, $pagination = false, $status = 'complete' ) {

	if ( empty( $user ) ) {
		$user = get_current_user_id();
	}

	if ( 0 === $user && ! WalkTheCounty()->email_access->token_exists ) {
		return false;
	}

	$status = ( 'complete' === $status ) ? 'publish' : $status;
	$paged = 1;

	if ( $pagination ) {
		if ( get_query_var( 'paged' ) ) {
			$paged = get_query_var( 'paged' );
		} elseif ( get_query_var( 'page' ) ) {
			$paged = get_query_var( 'page' );
		}
	}

	$args = apply_filters( 'walkthecounty_get_users_donations_args', array(
		'user'    => $user,
		'number'  => $number,
		'status'  => $status,
		'orderby' => 'date',
	) );

	if ( $pagination ) {
		$args['page'] = $paged;
	} else {
		$args['nopaging'] = true;
	}

	$by_user_id = is_numeric( $user ) ? true : false;
	$donor   = new WalkTheCounty_Donor( $user, $by_user_id );

	if ( ! empty( $donor->payment_ids ) ) {

		unset( $args['user'] );
		$args['post__in'] = array_map( 'absint', explode( ',', $donor->payment_ids ) );

	}

	$donations = walkthecounty_get_payments( apply_filters( 'walkthecounty_get_users_donations_args', $args ) );

	// No donations.
	if ( ! $donations ) {
		return false;
	}

	return $donations;
}

/**
 * Get Users Donations
 *
 * Returns a list of unique donation forms walkthecountyn to by a specific user.
 *
 * @param int    $user   User ID or email address
 * @param string $status Donation Status.
 *
 * @since 1.0
 *
 * @return bool|object List of unique forms donated by user
 */
function walkthecounty_get_users_completed_donations( $user = 0, $status = 'complete' ) {
	if ( empty( $user ) ) {
		$user = get_current_user_id();
	}

	if ( empty( $user ) ) {
		return false;
	}

	$by_user_id = is_numeric( $user ) ? true : false;

	$donor = new WalkTheCounty_Donor( $user, $by_user_id );

	if ( empty( $donor->payment_ids ) ) {
		return false;
	}

	// Get all the items donated.
	$payment_ids    = array_reverse( explode( ',', $donor->payment_ids ) );
	$limit_payments = apply_filters( 'walkthecounty_users_completed_donations_payments', 50 );

	if ( ! empty( $limit_payments ) ) {
		$payment_ids = array_slice( $payment_ids, 0, $limit_payments );
		WalkTheCounty_Payments_Query::update_meta_cache( $payment_ids );
	}

	$donation_data = array();
	foreach ( $payment_ids as $payment_id ) {
		$donation_data[] = walkthecounty_get_payment_meta( $payment_id );
	}

	if ( empty( $donation_data ) ) {
		return false;
	}

	// Grab only the post ids "form_id" of the forms donated on this order.
	$completed_donations_ids = array();
	foreach ( $donation_data as $donation_meta ) {
		$completed_donations_ids[] = isset( $donation_meta['form_id'] ) ? $donation_meta['form_id'] : '';
	}

	if ( empty( $completed_donations_ids ) ) {
		return false;
	}

	// Only include each donation once.
	$form_ids = array_unique( $completed_donations_ids );

	// Make sure we still have some products and a first item.
	if ( empty( $form_ids ) || ! isset( $form_ids[0] ) ) {
		return false;
	}

	$post_type = get_post_type( $form_ids[0] );

	$args = apply_filters( 'walkthecounty_get_users_completed_donations_args', array(
		'include'        => $form_ids,
		'post_type'      => $post_type,
		'posts_per_page' => - 1,
	) );

	return apply_filters( 'walkthecounty_users_completed_donations_list', get_posts( $args ) );
}


/**
 * Has donations
 *
 * Checks to see if a user has donated to at least one form.
 *
 * @param int $user_id The ID of the user to check.
 *
 * @access public
 * @since  1.0
 *
 * @return bool True if has donated, false other wise.
 */
function walkthecounty_has_donations( $user_id = null ) {
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	if ( walkthecounty_get_users_donations( $user_id, 1 ) ) {
		return true; // User has at least one donation.
	}

	// User has never donated anything.
	return false;
}


/**
 * Get Donation Status for User.
 *
 * Retrieves the donation count and the total amount spent for a specific user.
 *
 * @param int|string $user The ID or email of the donor to retrieve stats for.
 *
 * @access public
 * @since  1.0
 *
 * @return array
 */
function walkthecounty_get_donation_stats_by_user( $user = '' ) {

	$field = '';

	if ( is_email( $user ) ) {
		$field = 'email';
	} elseif ( is_numeric( $user ) ) {
		$field = 'user_id';
	}

	$stats    = array();
	$donor = WalkTheCounty()->donors->get_donor_by( $field, $user );

	if ( $donor ) {
		$donor = new WalkTheCounty_Donor( $donor->id );
		$stats['purchases']   = absint( $donor->purchase_count );
		$stats['total_spent'] = walkthecounty_maybe_sanitize_amount( $donor->get_total_donation_amount() );
	}

	/**
	 * Filter the donation stats.
	 *
	 * @since 1.7
	 */
	$stats = (array) apply_filters( 'walkthecounty_donation_stats_by_user', $stats, $user );

	return $stats;
}


/**
 * Count number of donations of a donor.
 *
 * Returns total number of donations a donor has made.
 *
 * @param int|string $user The ID or email of the donor.
 *
 * @access public
 * @since  1.0
 *
 * @return int The total number of donations.
 */
function walkthecounty_count_donations_of_donor( $user = null ) {

	// Logged in?
	if ( empty( $user ) ) {
		$user = get_current_user_id();
	}

	// Email access?
	if ( empty( $user ) && WalkTheCounty()->email_access->token_email ) {
		$user = WalkTheCounty()->email_access->token_email;
	}

	$stats = ! empty( $user ) ? walkthecounty_get_donation_stats_by_user( $user ) : false;

	return isset( $stats['purchases'] ) ? $stats['purchases'] : 0;
}

/**
 * Calculates the total amount spent by a user.
 *
 * @param int|string $user The ID or email of the donor.
 *
 * @access public
 * @since  1.0
 *
 * @return float The total amount the user has spent
 */
function walkthecounty_donation_total_of_user( $user = null ) {

	$stats = walkthecounty_get_donation_stats_by_user( $user );

	return $stats['total_spent'];
}


/**
 * Validate a potential username.
 *
 * @param string $username The username to validate.
 * @param int    $form_id  Donation Form ID.
 *
 * @since 1.0
 *
 * @return bool
 */
function walkthecounty_validate_username( $username, $form_id = 0 ) {
	$valid = true;

	// Validate username.
	if ( ! empty( $username ) ) {

		// Sanitize username.
		$sanitized_user_name = sanitize_user( $username, false );

		// We have an user name, check if it already exists.
		if ( username_exists( $username ) ) {
			// Username already registered.
			walkthecounty_set_error( 'username_unavailable', __( 'Username already taken.', 'walkthecounty' ) );
			$valid = false;

			// Check if it's valid.
		} elseif ( $sanitized_user_name !== $username ) {
			// Invalid username.
			if ( is_multisite() ) {
				walkthecounty_set_error( 'username_invalid', __( 'Invalid username. Only lowercase letters (a-z) and numbers are allowed.', 'walkthecounty' ) );
				$valid = false;
			} else {
				walkthecounty_set_error( 'username_invalid', __( 'Invalid username.', 'walkthecounty' ) );
				$valid = false;
			}
		}
	} else {
		// Username is empty.
		walkthecounty_set_error( 'username_empty', __( 'Enter a username.', 'walkthecounty' ) );
		$valid = false;

		// Check if guest checkout is disable for form.
		if ( $form_id && walkthecounty_logged_in_only( $form_id ) ) {
			walkthecounty_set_error( 'registration_required', __( 'You must register or login to complete your donation.', 'walkthecounty' ) );
			$valid = false;
		}
	}

	/**
	 * Filter the username validation result.
	 *
	 * @param bool   $valid    Username is valid or not.
	 * @param string $username Username to check.
	 * @param bool   $form_id  Donation Form ID.
	 *
	 * @since 1.8
	 */
	$valid = (bool) apply_filters( 'walkthecounty_validate_username', $valid, $username, $form_id );

	return $valid;
}


/**
 * Validate user email.
 *
 * @param string $email                User email.
 * @param bool   $registering_new_user Flag to check user register or not.
 *
 * @since 1.8
 *
 * @return bool
 */
function walkthecounty_validate_user_email( $email, $registering_new_user = false ) {
	$valid = true;

	if ( empty( $email ) ) {
		// No email.
		walkthecounty_set_error( 'email_empty', __( 'Enter an email.', 'walkthecounty' ) );
		$valid = false;

	} elseif ( email_exists( $email ) ) {
		// Email already exists.
		walkthecounty_set_error( 'email_exists', __( 'Email already exists.', 'walkthecounty' ) );
		$valid = false;

	} elseif ( ! is_email( $email ) ) {
		// Validate email.
		walkthecounty_set_error( 'email_invalid', __( 'Invalid email.', 'walkthecounty' ) );
		$valid = false;

	} elseif ( $registering_new_user ) {

		// If donor email is not primary.
		if ( ! email_exists( $email ) && walkthecounty_donor_email_exists( $email ) && walkthecounty_is_additional_email( $email ) ) {
			// Check if email exists.
			walkthecounty_set_error( 'email_used', __( 'The email address provided is already active for another user.', 'walkthecounty' ) );
			$valid = false;
		}
	}

	/**
	 * Filter the email validation result.
	 *
	 * @param bool   $valid                Email is valid or not.
	 * @param string $email                Email to check.
	 * @param bool   $registering_new_user Registering New or Existing User.
	 *
	 * @since 1.8
	 */
	$valid = (bool) apply_filters( 'walkthecounty_validate_user_email', $valid, $email, $registering_new_user );

	return $valid;
}

/**
 * Validate password.
 *
 * @param string $password             Password to Validate.
 * @param string $confirm_password     Password to Confirm Validation.
 * @param bool   $registering_new_user Registering New or Existing User.
 *
 * @since 1.8
 *
 * @return bool
 */
function walkthecounty_validate_user_password( $password = '', $confirm_password = '', $registering_new_user = false ) {
	$valid = true;

	// Passwords Validation For New Donors Only.
	if ( $registering_new_user ) {
		// Password or confirmation missing.
		if ( ! $password ) {
			// The password is invalid.
			walkthecounty_set_error( 'password_empty', __( 'Enter a password.', 'walkthecounty' ) );
			$valid = false;
		} elseif ( ! $confirm_password ) {
			// Confirmation password is invalid.
			walkthecounty_set_error( 'confirmation_empty', __( 'Enter the password confirmation.', 'walkthecounty' ) );
			$valid = false;
		}
	}
	// Passwords Validation For New Donors as well as Existing Donors.
	if ( $password || $confirm_password ) {
		if ( strlen( $password ) < 6 || strlen( $confirm_password ) < 6 ) {
			// Seems Weak Password.
			walkthecounty_set_error( 'password_weak', __( 'Passwords should have at least 6 characters.', 'walkthecounty' ) );
			$valid = false;
		}
		if ( $password && $confirm_password ) {
			// Verify confirmation matches.
			if ( $password !== $confirm_password ) {
				// Passwords do not match.
				walkthecounty_set_error( 'password_mismatch', __( 'Passwords you entered do not match. Please try again.', 'walkthecounty' ) );
				$valid = false;
			}
		}
	}

	/**
	 * Filter the password validation result.
	 *
	 * @param bool   $valid                Password is Valid or not.
	 * @param string $password             Password to check validation.
	 * @param string $confirm_password     Password to confirm validation.
	 * @param bool   $registering_new_user Registering New or Existing User.
	 *
	 * @since 1.8
	 */
	$valid = (bool) apply_filters( 'walkthecounty_validate_user_email', $valid, $password, $confirm_password, $registering_new_user );

	return $valid;
}

/**
 * Counts the total number of donors.
 *
 * @access public
 * @since  1.0
 *
 * @return int The total number of donors.
 */
function walkthecounty_count_total_donors() {
	return WalkTheCounty()->donors->count();
}

/**
 * Returns the saved address for a donor
 *
 * @access public
 * @since  1.0
 *
 * @param int/null $donor_id Donor ID.
 * @param array $args         {
 *
 * @type bool   $by_user_id   Flag to validate find donor by donor ID or user ID
 * @type string $address_type Optional. Which type of donor address this function will return.
 * }
 *
 * @return array The donor's address, if any
 */
function walkthecounty_get_donor_address( $donor_id = null, $args = array() ) {
	$default_args = array(
		'by_user_id'   => false,
		'address_type' => 'billing',
	);

	$default_address = array(
		'line1'   => '',
		'line2'   => '',
		'city'    => '',
		'state'   => '',
		'country' => '',
		'zip'     => '',
	);

	$address = array();
	$args    = wp_parse_args( $args, $default_args );

	// Set user id if donor is empty.
	if ( empty( $donor_id ) ) {
		$donor_id           = get_current_user_id();
		$args['by_user_id'] = true;
	}

	// Backward compatibility.
	if ( ! walkthecounty_has_upgrade_completed( 'v20_upgrades_user_address' ) && $by_user_id ) {
		return wp_parse_args(
			(array) get_user_meta( $donor_id, '_walkthecounty_user_address', true ),
			$default_address
		);
	}

	$donor = new WalkTheCounty_Donor( $donor_id, (bool) $args['by_user_id'] );

	if (
		! $donor->id ||
		empty( $donor->address ) ||
		! array_key_exists( $args['address_type'], $donor->address )
	) {
		return $default_address;
	}

	switch ( true ) {
		case is_string( end( $donor->address[ $args['address_type'] ] ) ):
			$address = wp_parse_args( $donor->address[ $args['address_type'] ], $default_address );
			break;

		case is_array( end( $donor->address[ $args['address_type'] ] ) ):
			$address = wp_parse_args( array_shift( $donor->address[ $args['address_type'] ] ), $default_address );
			break;
	}

	return $address;
}

/**
 * WalkTheCounty New User Notification
 *
 * Sends the new user notification email when a user registers within the donation form
 *
 * @param int   $donation_id   Donation ID.
 * @param array $donation_data An Array of Donation Data.
 *
 * @access public
 * @since  1.0
 *
 * @return void
 */
function walkthecounty_new_user_notification( $donation_id = 0, $donation_data = array() ) {
	// Bailout.
	if (
		empty( $donation_id )
		|| empty( $donation_data )
		|| ! isset( $_POST['walkthecounty_create_account'] )
		|| 'on' !== walkthecounty_clean( $_POST['walkthecounty_create_account'] )
	) {
		return;
	}

	// For backward compatibility
	$user = get_user_by( 'ID', $donation_data['user_info']['id'] );

	$donation_data['user_info'] = array_merge(
		$donation_data['user_info'],
		array(
			'user_id'    => $donation_data['user_info']['id'],
			'user_first' => $donation_data['user_info']['first_name'],
			'user_last'  => $donation_data['user_info']['last_name'],
			'user_email' => $donation_data['user_info']['email'],
			'user_login' => $user->user_login,
		)
	);

	do_action( 'walkthecounty_new-donor-register_email_notification', $donation_data['user_info']['id'], $donation_data['user_info'], $donation_id );
	do_action( 'walkthecounty_donor-register_email_notification', $donation_data['user_info']['id'], $donation_data['user_info'], $donation_id );
}

add_action( 'walkthecounty_insert_payment', 'walkthecounty_new_user_notification', 10, 2 );


/**
 * Get Donor Name By
 *
 * Retrieves the donor name based on the id and the name of the user or donation
 *
 * @param int    $id   The ID of donation or donor.
 * @param string $from From will be a string to be passed as donation or donor.
 *
 * @access public
 * @since  1.8.9
 *
 * @return string
 */
function walkthecounty_get_donor_name_by( $id = 0, $from = 'donation' ) {

	// ID shouldn't be empty.
	if ( empty( $id ) ) {
		return '';
	}

	$name         = '';
	$title_prefix = '';

	switch ( $from ) {

		case 'donation':
			$title_prefix  = walkthecounty_get_meta( $id, '_walkthecounty_payment_donor_title_prefix', true );
			$first_name    = walkthecounty_get_meta( $id, '_walkthecounty_donor_billing_first_name', true );
			$last_name     = walkthecounty_get_meta( $id, '_walkthecounty_donor_billing_last_name', true );

			$name = "{$first_name} {$last_name}";

			break;

		case 'donor':
			$name         = WalkTheCounty()->donors->get_column( 'name', $id );
			$title_prefix = WalkTheCounty()->donor_meta->get_meta( $id, '_walkthecounty_donor_title_prefix', true );

			break;

	}

	// If title prefix is set then prepend it to name.
	$name = walkthecounty_get_donor_name_with_title_prefixes( $title_prefix, $name );

	return $name;

}

/**
 * Checks whether the walkthecountyn donor email exists in users as well as additional_email of donors.
 *
 * @param string $email Donor Email.
 *
 * @since 1.8.9
 *
 * @return boolean  The user's ID on success, and false on failure.
 */
function walkthecounty_donor_email_exists( $email ) {
	if ( WalkTheCounty()->donors->get_donor_by( 'email', $email ) ) {
		return true;
	}
	return false;
}

/**
 * This function will check whether the donor email is primary or additional.
 *
 * @param string $email Donor Email.
 *
 * @since 1.8.13
 *
 * @return bool
 */
function walkthecounty_is_additional_email( $email ) {
	global $wpdb;

	$meta_table = WalkTheCounty()->donor_meta->table_name;
	$meta_type  = WalkTheCounty()->donor_meta->meta_type;
	$donor_id   = $wpdb->get_var( $wpdb->prepare( "SELECT {$meta_type}_id FROM {$meta_table} WHERE meta_key = 'additional_email' AND meta_value = %s LIMIT 1", $email ) );

	if ( empty( $donor_id ) ) {
		return false;
	}

	return true;
}
