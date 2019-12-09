<?php
/**
 * WalkTheCounty - Stripe Core Frontend Filters
 *
 * @since 2.5.0
 *
 * @package    WalkTheCounty
 * @subpackage Stripe Core
 * @copyright  Copyright (c) 2019, WalkTheCountyWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 */

// Exit, if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Use walkthecounty_get_payment_transaction_id() first.
 *
 * WalkTheCountyn a Payment ID, extract the transaction ID from Stripe and update the payment meta.
 *
 * @param string $payment_id Payment ID.
 *
 * @since 2.5.0
 *
 * @return string Transaction ID
 */
function walkthecounty_stripe_get_payment_txn_id_fallback( $payment_id ) {

	$notes          = walkthecounty_get_payment_notes( $payment_id );
	$transaction_id = '';

	foreach ( $notes as $note ) {
		if ( preg_match( '/^Stripe Charge ID: ([^\s]+)/', $note->comment_content, $match ) ) {
			$transaction_id = $match[1];
			update_post_meta( $payment_id, '_walkthecounty_payment_transaction_id', $transaction_id );
			continue;
		}
	}

	return apply_filters( 'walkthecounty_stripe_get_payment_txn_id_fallback', $transaction_id, $payment_id );
}

add_filter( 'walkthecounty_get_payment_transaction_id-stripe', 'walkthecounty_stripe_get_payment_txn_id_fallback', 10, 1 );
add_filter( 'walkthecounty_get_payment_transaction_id-stripe_ach', 'walkthecounty_stripe_get_payment_txn_id_fallback', 10, 1 );
