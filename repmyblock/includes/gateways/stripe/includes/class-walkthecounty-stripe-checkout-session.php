<?php
/**
 * WalkTheCounty - Stripe | Checkout Session API
 *
 * @package    WalkTheCounty
 * @subpackage Stripe Core
 * @copyright  Copyright (c) 2019, WalkTheCountyWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WalkTheCounty_Stripe_Checkout_Session.
 *
 * @since 2.5.5
 */
class WalkTheCounty_Stripe_Checkout_Session {

	/**
	 * This function is used to create a new Checkout session.
	 *
	 * @param array $args List of arguments to create Checkout session.
	 *
	 * @since  2.5.5
	 * @access public
	 *
	 * @return \Stripe\Checkout\Session|bool
	 */
	public function create( $args ) {

		try {
			/**
			 * This filter will be used to modify create checkout arguments.
			 *
			 * @since 2.5.5
			 */
			$args = apply_filters( 'walkthecounty_stripe_create_checkout_session_args', $args );

			// Add application fee, if the Stripe premium add-on is not active.
			if ( ! defined( 'WALKTHECOUNTY_STRIPE_VERSION' ) ) {
				$args['payment_intent_data']['application_fee_amount'] = walkthecounty_stripe_get_application_fee_amount( $args['line_items'][0]['amount'] );
			}

			// Process Checkout session.
			$session = \Stripe\Checkout\Session::create(
				$args,
				walkthecounty_stripe_get_connected_account_options()
			);

			// Return Checkout Session Object.
			return $session;

		} catch ( Exception $e ) {

			walkthecounty_record_gateway_error(
				__( 'Stripe Error', 'walkthecounty' ),
				sprintf(
					/* translators: %s Exception Message Body */
					__( 'The Stripe Gateway returned an error while creating the Checkout Session. Details: %s', 'walkthecounty' ),
					$e
				)
			);
			walkthecounty_set_error( 'stripe_error', __( 'An occurred while processing the donation with the gateway. Please try your donation again.', 'walkthecounty' ) );
			walkthecounty_send_back_to_checkout( '?payment-mode=' . walkthecounty_clean( $_GET['payment-mode'] ) );
		}

		return false;
	}

	/**
	 * This function is used to retrieve the Checkout session by using Checkout session ID.
	 *
	 * @param int $id Checkout Session ID.
	 *
	 * @since  2.5.5
	 * @access public
	 *
	 * @return \Stripe\Checkout\Session|bool
	 */
	public function retrieve( $id ) {

		try {

			// Process Checkout session.
			$session = \Stripe\Checkout\Session::retrieve(
				$id,
				walkthecounty_stripe_get_connected_account_options()
			);

			// Return Checkout Session Object.
			return $session;

		} catch ( Exception $e ) {

			walkthecounty_record_gateway_error(
				__( 'Stripe Error', 'walkthecounty' ),
				sprintf(
					/* translators: %s Exception Message Body */
					__( 'The Stripe Gateway returned an error while retrieving the Checkout Session. Details: %s', 'walkthecounty' ),
					$e
				)
			);
			walkthecounty_set_error( 'stripe_error', __( 'An occurred while processing the donation with the gateway. Please try your donation again.', 'walkthecounty' ) );
			walkthecounty_send_back_to_checkout( '?payment-mode=' . walkthecounty_clean( $_GET['payment-mode'] ) );
		}

		return false;
	}
}
