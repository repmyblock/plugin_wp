<?php
/**
 * Email Functions
 *
 * @package     WalkTheCounty
 * @subpackage  Emails
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email Donation Receipt.
 *
 * Email the donation confirmation to the donor via the customizable "Donation Receipt" settings.
 *
 * @param int  $payment_id   Payment ID.
 * @param bool $admin_notice Whether to send the admin email notification or not (default: true).
 *
 * @return void
 * @since 1.0
 *
 */
function walkthecounty_email_donation_receipt( $payment_id, $admin_notice = true ) {
	/**
	 * Fire the action
	 */
	do_action( 'walkthecounty_donation-receipt_email_notification', $payment_id );

	// If admin notifications are on, send the admin notice.
	if ( $admin_notice && walkthecounty_is_setting_enabled( WalkTheCounty_Email_Notification::get_instance( 'new-donation' )->get_notification_status() ) ) {
		/**
		 * Fires in the donation email receipt.
		 *
		 * When admin email notices are not disabled, you can add new email notices.
		 *
		 * @param int   $payment_id   Payment id.
		 * @param mixed $payment_data Payment meta data.
		 *
		 * @since 1.0
		 *
		 */
		do_action( 'walkthecounty_new-donation_email_notification', $payment_id, walkthecounty_get_payment_meta( $payment_id ) );
	}
}

/**
 * Sends the Admin Sale Notification Email
 *
 * @param int $payment_id Payment ID (default: 0)
 *
 * @return void
 * @since 1.0
 *
 */
function walkthecounty_admin_email_notice( $payment_id ) {
	/**
	 * Fires in the donation email receipt.
	 *
	 * When admin email notices are not disabled, you can add new email notices.
	 *
	 * @param int   $payment_id   Payment id.
	 * @param mixed $payment_data Payment meta data.
	 *
	 * @since 1.0
	 *
	 */
	do_action( 'walkthecounty_new-donation_email_notification', $payment_id );
}

add_action( 'walkthecounty_admin_donation_email', 'walkthecounty_admin_email_notice' );


/**
 * Get default donation notification email text
 *
 * Returns the stored email text if available, the standard email text if not
 *
 * @return string $message
 * @since  1.0
 */
function walkthecounty_get_default_donation_notification_email() {

	$default_email_body = __( 'Hi there,', 'walkthecounty' ) . "\n\n";
	$default_email_body .= __( 'This email is to inform you that a new donation has been made on your website:', 'walkthecounty' ) . ' {site_url}' . ".\n\n";
	$default_email_body .= '<strong>' . __( 'Donor:', 'walkthecounty' ) . '</strong> {name}' . "\n";
	$default_email_body .= '<strong>' . __( 'Donation:', 'walkthecounty' ) . '</strong> {donation}' . "\n";
	$default_email_body .= '<strong>' . __( 'Amount:', 'walkthecounty' ) . '</strong> {amount}' . "\n";
	$default_email_body .= '<strong>' . __( 'Payment Method:', 'walkthecounty' ) . '</strong> {payment_method}' . "\n\n";
	$default_email_body .= __( 'Thank you,', 'walkthecounty' ) . "\n\n";
	$default_email_body .= '{sitename}' . "\n";

	return apply_filters( 'walkthecounty_default_donation_notification_email', $default_email_body );
}


/**
 * Get default donation receipt email text
 *
 * Returns the stored email text if available, the standard email text if not
 *
 * @return string $message
 * @since  1.3.7
 */
function walkthecounty_get_default_donation_receipt_email() {

	$default_email_body = __( 'Dear', 'walkthecounty' ) . " {name},\n\n";
	$default_email_body .= __( 'Thank you for your donation. Your generosity is appreciated! Here are the details of your donation:', 'walkthecounty' ) . "\n\n";
	$default_email_body .= '<strong>' . __( 'Donor:', 'walkthecounty' ) . '</strong> {fullname}' . "\n";
	$default_email_body .= '<strong>' . __( 'Donation:', 'walkthecounty' ) . '</strong> {donation}' . "\n";
	$default_email_body .= '<strong>' . __( 'Donation Date:', 'walkthecounty' ) . '</strong> {date}' . "\n";
	$default_email_body .= '<strong>' . __( 'Amount:', 'walkthecounty' ) . '</strong> {amount}' . "\n";
	$default_email_body .= '<strong>' . __( 'Payment Method:', 'walkthecounty' ) . '</strong> {payment_method}' . "\n";
	$default_email_body .= '<strong>' . __( 'Payment ID:', 'walkthecounty' ) . '</strong> {payment_id}' . "\n\n";
	$default_email_body .= '{receipt_link}' . "\n\n";
	$default_email_body .= "\n\n";
	$default_email_body .= __( 'Sincerely,', 'walkthecounty' ) . "\n";
	$default_email_body .= '{sitename}' . "\n";

	return apply_filters( 'walkthecounty_default_donation_receipt_email', $default_email_body );
}

/**
 * Get various correctly formatted names used in emails
 *
 * @param array             $user_info List of User Information.
 * @param WalkTheCounty_Payment|bool $payment   Payment Object.
 *
 * @return array $email_names
 * @since 1.0
 *
 */
function walkthecounty_get_email_names( $user_info, $payment = false ) {
	$email_names = array();

	if ( is_a( $payment, 'WalkTheCounty_Payment' ) ) {

		if ( $payment->user_id > 0 ) {

			$user_data               = get_userdata( $payment->user_id );
			$email_names['name']     = $payment->first_name;
			$email_names['fullname'] = trim( $payment->first_name . ' ' . $payment->last_name );
			$email_names['username'] = $user_data->user_login;

		} elseif ( ! empty( $payment->first_name ) ) {

			$email_names['name']     = $payment->first_name;
			$email_names['fullname'] = trim( $payment->first_name . ' ' . $payment->last_name );
			$email_names['username'] = $payment->first_name;

		} else {

			$email_names['name']     = $payment->email;
			$email_names['username'] = $payment->email;

		}
	} else {

		// Support for old serialized data.
		if ( is_serialized( $user_info ) ) {

			// Security check.
			preg_match( '/[oO]\s*:\s*\d+\s*:\s*"\s*(?!(?i)(stdClass))/', $user_info, $matches );
			if ( ! empty( $matches ) ) {
				return array(
					'name'     => '',
					'fullname' => '',
					'username' => '',
				);
			} else {
				$user_info = maybe_unserialize( $user_info );
			}
		}

		if ( isset( $user_info['id'] ) && $user_info['id'] > 0 && isset( $user_info['first_name'] ) ) {
			$user_data               = get_userdata( $user_info['id'] );
			$email_names['name']     = $user_info['first_name'];
			$email_names['fullname'] = $user_info['first_name'] . ' ' . $user_info['last_name'];
			$email_names['username'] = $user_data->user_login;
		} elseif ( isset( $user_info['first_name'] ) ) {
			$email_names['name']     = $user_info['first_name'];
			$email_names['fullname'] = $user_info['first_name'] . ' ' . $user_info['last_name'];
			$email_names['username'] = $user_info['first_name'];
		} else {
			$email_names['name']     = $user_info['email'];
			$email_names['username'] = $user_info['email'];
		}
	} // End if().

	// Set title prefix to name, if non empty.
	if ( ! empty( $user_info['title'] ) && ! empty( $user_info['last_name'] ) ) {
		$email_names['name'] = walkthecounty_get_donor_name_with_title_prefixes( $user_info['title'], $user_info['last_name'] );
	}

	// Set title prefix to fullname, if non empty.
	if ( ! empty( $user_info['title'] ) && ! empty( $email_names['fullname'] ) ) {
		$email_names['fullname'] = walkthecounty_get_donor_name_with_title_prefixes( $user_info['title'], $email_names['fullname'] );
	}

	return $email_names;
}
