<?php
/**
 * Email Actions.
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
 * Triggers a donation receipt to be sent after the payment status is updated.
 *
 * @since 1.0
 *
 * @param int $payment_id Payment ID
 *
 * @return void
 */
function walkthecounty_trigger_donation_receipt( $payment_id ) {
	// Make sure we don't send a receipt while editing a donation.
	if ( isset( $_POST['walkthecounty-action'] ) && 'edit_payment' == $_POST['walkthecounty-action'] ) {
		return;
	}

	// Send email.
	walkthecounty_email_donation_receipt( $payment_id );
}

add_action( 'walkthecounty_complete_donation', 'walkthecounty_trigger_donation_receipt', 999, 1 );
