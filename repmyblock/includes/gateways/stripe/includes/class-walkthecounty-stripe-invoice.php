<?php
/**
 * WalkTheCounty - Stripe Core Gateway
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
 * Check for class WalkTheCounty_Stripe_Invoices exists.
 *
 * @since 2.5.0
 */
if ( ! class_exists( 'WalkTheCounty_Stripe_Invoice' ) ) {

	class WalkTheCounty_Stripe_Invoice {

		/**
		 * Retrieve Invoice/
		 *
		 * @param string $id Invoice ID.
		 *
		 * @return \Stripe\Invoice
		 */
		public function retrieve( $id ) {
			try {

				// Set Application Information.
				walkthecounty_stripe_set_app_info();

				// Retrieve Invoice by ID.
				$invoice = \Stripe\Invoice::retrieve( $id );
			} catch( Exception $e ) {

				// Something went wrong outside of Stripe.
				walkthecounty_record_gateway_error(
					__( 'Stripe - Invoices Error', 'walkthecounty' ),
					sprintf(
						/* translators: %s Exception Message. */
						__( 'An error while retrieving invoice. Details: %s', 'walkthecounty' ),
						$e->getMessage()
					)
				);
				walkthecounty_set_error( 'Stripe Error', __( 'An error occurred while retrieving invoice. Please try again.', 'walkthecounty' ) );
				walkthecounty_send_back_to_checkout( '?payment-mode=' . walkthecounty_clean( $_GET['payment-mode'] ) );

				 return false;
			}

			return $invoice;
		}
	}
}
