<?php
/**
 * Manual Gateway
 *
 * @package     WalkTheCounty
 * @subpackage  Gateways
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manual Gateway does not need a CC form, so remove it.
 *
 * @since 1.0
 * @return void
 */
add_action( 'walkthecounty_manual_cc_form', '__return_false' );

/**
 * Processes the donation data and uses the Manual Payment gateway to record
 * the donation in the Donation History
 *
 * @since 1.0
 *
 * @param array $purchase_data Donation Data
 *
 * @return void
 */
function walkthecounty_manual_payment( $purchase_data ) {

	if ( ! wp_verify_nonce( $purchase_data['gateway_nonce'], 'walkthecounty-gateway' ) ) {
		wp_die( esc_html__( 'We\'re unable to recognize your session. Please refresh the screen to try again; otherwise contact your website administrator for assistance.', 'walkthecounty' ), esc_html__( 'Error', 'walkthecounty' ), array( 'response' => 403 ) );
	}

	//Create payment_data array
	$payment_data = array(
		'price'           => $purchase_data['price'],
		'walkthecounty_form_title' => $purchase_data['post_data']['walkthecounty-form-title'],
		'walkthecounty_form_id'    => intval( $purchase_data['post_data']['walkthecounty-form-id'] ),
		'walkthecounty_price_id'   => isset($purchase_data['post_data']['walkthecounty-price-id']) ? $purchase_data['post_data']['walkthecounty-price-id'] : '',
		'date'            => $purchase_data['date'],
		'user_email'      => $purchase_data['user_email'],
		'purchase_key'    => $purchase_data['purchase_key'],
		'currency'        => walkthecounty_get_currency( $purchase_data['post_data']['walkthecounty-form-id'], $purchase_data ),
		'user_info'       => $purchase_data['user_info'],
		'status'          => 'pending'
	);
	// Record the pending payment
	$payment = walkthecounty_insert_payment( $payment_data );

	if ( $payment ) {
		walkthecounty_update_payment_status( $payment, 'publish' );
		walkthecounty_send_to_success_page();
	} else {
		walkthecounty_record_gateway_error(
			esc_html__( 'Payment Error', 'walkthecounty' ),
			sprintf(
				/* translators: %s: payment data */
				esc_html__( 'The payment creation failed while processing a manual (free or test) donation. Payment data: %s', 'walkthecounty' ),
				json_encode( $payment_data )
			),
			$payment
		);
		// If errors are present, send the user back to the donation page so they can be corrected
		walkthecounty_send_back_to_checkout( '?payment-mode=' . $purchase_data['post_data']['walkthecounty-gateway'] );
	}
}

add_action( 'walkthecounty_gateway_manual', 'walkthecounty_manual_payment' );
