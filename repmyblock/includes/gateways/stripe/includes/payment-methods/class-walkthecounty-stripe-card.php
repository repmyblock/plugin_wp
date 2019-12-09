<?php
/**
 * WalkTheCounty - Stripe Card Payments
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
 * Check for WalkTheCounty_Stripe_Card existence.
 *
 * @since 2.5.0
 */
if ( ! class_exists( 'WalkTheCounty_Stripe_Card' ) ) {

	/**
	 * Class WalkTheCounty_Stripe_Card.
	 *
	 * @since 2.5.0
	 */
	class WalkTheCounty_Stripe_Card extends WalkTheCounty_Stripe_Gateway {

		/**
		 * WalkTheCounty_Stripe_Card constructor.
		 *
		 * @since  2.5.0
		 * @access public
		 */
		public function __construct() {

			$this->id = 'stripe';

			parent::__construct();
		}

		/**
		 * Check for the Stripe Source.
		 *
		 * @param array $donation_data List of Donation Data.
		 *
		 * @since 2.0.6
		 *
		 * @return string
		 */
		public function check_for_source( $donation_data ) {

			$source_id          = $donation_data['post_data']['walkthecounty_stripe_payment_method'];
			$stripe_js_fallback = walkthecounty_get_option( 'stripe_js_fallback' );

			if ( ! isset( $source_id ) ) {

				// check for fallback mode.
				if ( ! empty( $stripe_js_fallback ) ) {

					$card_data = $this->prepare_card_data( $donation_data );

					// Set Application Info.
					walkthecounty_stripe_set_app_info();

					try {

						$source = \Stripe\Source::create( array(
							'card' => $card_data,
						) );
						$source_id = $source->id;

					} catch ( \Stripe\Error\Base $e ) {
						$this->log_error( $e );

					} catch ( Exception $e ) {

						walkthecounty_record_gateway_error(
							__( 'Stripe Error', 'walkthecounty' ),
							sprintf(
								/* translators: %s Exception Message Body */
								__( 'The Stripe Gateway returned an error while creating the customer payment source. Details: %s', 'walkthecounty' ),
								$e->getMessage()
							)
						);
						walkthecounty_set_error( 'stripe_error', __( 'An occurred while processing the donation with the gateway. Please try your donation again.', 'walkthecounty' ) );
						walkthecounty_send_back_to_checkout( "?payment-mode={$this->id}&form_id={$donation_data['post_data']['walkthecounty-form-id']}" );
					}
				} elseif ( ! $this->is_stripe_popup_enabled() ) {

					// No Stripe source and fallback mode is disabled.
					walkthecounty_set_error( 'no_token', __( 'Missing Stripe Source. Please contact support.', 'walkthecounty' ) );
					walkthecounty_record_gateway_error( __( 'Missing Stripe Source', 'walkthecounty' ), __( 'A Stripe token failed to be generated. Please check Stripe logs for more information.', 'walkthecounty' ) );

				}
			} // End if().

			return $source_id;

		}

		/**
		 * Process the POST Data for the Credit Card Form, if a source was not supplied.
		 *
		 * @since 2.5.0
		 *
		 * @param array $donation_data List of donation data.
		 *
		 * @return array The credit card data from the $_POST
		 */
		public function prepare_card_data( $donation_data ) {

			$card_data = array(
				'number'          => $donation_data['card_info']['card_number'],
				'name'            => $donation_data['card_info']['card_name'],
				'exp_month'       => $donation_data['card_info']['card_exp_month'],
				'exp_year'        => $donation_data['card_info']['card_exp_year'],
				'cvc'             => $donation_data['card_info']['card_cvc'],
				'address_line1'   => $donation_data['card_info']['card_address'],
				'address_line2'   => $donation_data['card_info']['card_address_2'],
				'address_city'    => $donation_data['card_info']['card_city'],
				'address_zip'     => $donation_data['card_info']['card_zip'],
				'address_state'   => $donation_data['card_info']['card_state'],
				'address_country' => $donation_data['card_info']['card_country'],
			);

			return $card_data;
		}

		/**
		 * This function will be used for donation processing.
		 *
		 * @param array $donation_data List of donation data.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @return void
		 */
		public function process_payment( $donation_data ) {

			// Bailout, if the current gateway and the posted gateway mismatched.
			if ( 'stripe' !== $donation_data['post_data']['walkthecounty-gateway'] ) {
				return;
			}

			// Make sure we don't have any left over errors present.
			walkthecounty_clear_errors();

			$payment_method_id = ! empty( $donation_data['post_data']['walkthecounty_stripe_payment_method'] )
				? $donation_data['post_data']['walkthecounty_stripe_payment_method']
				: $this->check_for_source( $donation_data );

			// Any errors?
			$errors = walkthecounty_get_errors();

			// No errors, proceed.
			if ( ! $errors ) {

				$form_id          = ! empty( $donation_data['post_data']['walkthecounty-form-id'] ) ? intval( $donation_data['post_data']['walkthecounty-form-id'] ) : 0;
				$price_id         = ! empty( $donation_data['post_data']['walkthecounty-price-id'] ) ? $donation_data['post_data']['walkthecounty-price-id'] : 0;
				$donor_email      = ! empty( $donation_data['post_data']['walkthecounty_email'] ) ? $donation_data['post_data']['walkthecounty_email'] : 0;
				$donation_summary = walkthecounty_payment_gateway_donation_summary( $donation_data, false );

				// Get an existing Stripe customer or create a new Stripe Customer and attach the source to customer.
				$walkthecounty_stripe_customer = new WalkTheCounty_Stripe_Customer( $donor_email, $payment_method_id );
				$stripe_customer      = $walkthecounty_stripe_customer->customer_data;
				$stripe_customer_id   = $walkthecounty_stripe_customer->get_id();

				// We have a Stripe customer, charge them.
				if ( $stripe_customer_id ) {

					// Proceed to get stripe source/payment method details.
					$payment_method    = $walkthecounty_stripe_customer->attached_payment_method;
					$payment_method_id = $payment_method->id;

					// Setup the payment details.
					$payment_data = array(
						'price'           => $donation_data['price'],
						'walkthecounty_form_title' => $donation_data['post_data']['walkthecounty-form-title'],
						'walkthecounty_form_id'    => $form_id,
						'walkthecounty_price_id'   => $price_id,
						'date'            => $donation_data['date'],
						'user_email'      => $donation_data['user_email'],
						'purchase_key'    => $donation_data['purchase_key'],
						'currency'        => walkthecounty_get_currency( $form_id ),
						'user_info'       => $donation_data['user_info'],
						'status'          => 'pending',
						'gateway'         => $this->id,
					);

					// Record the pending payment in WalkTheCounty.
					$donation_id = walkthecounty_insert_payment( $payment_data );

					// Return error, if donation id doesn't exists.
					if ( ! $donation_id ) {
						walkthecounty_record_gateway_error(
							__( 'Donation creating error', 'walkthecounty' ),
							sprintf(
								/* translators: %s Donation Data */
								__( 'Unable to create a pending donation. Details: %s', 'walkthecounty' ),
								wp_json_encode( $donation_data )
							)
						);
						walkthecounty_set_error( 'stripe_error', __( 'The Stripe Gateway returned an error while creating a pending donation.', 'walkthecounty' ) );
						walkthecounty_send_back_to_checkout( '?payment-mode=' . walkthecounty_clean( $_GET['payment-mode'] ) );
						return false;
					}

					// Assign required data to array of donation data for future reference.
					$donation_data['donation_id'] = $donation_id;
					$donation_data['description'] = $donation_summary;
					$donation_data['source_id']   = $payment_method_id;

					// Save Stripe Customer ID to Donation note, Donor and Donation for future reference.
					walkthecounty_insert_payment_note( $donation_id, 'Stripe Customer ID: ' . $stripe_customer_id );
					$this->save_stripe_customer_id( $stripe_customer_id, $donation_id );
					walkthecounty_update_meta( $donation_id, '_walkthecounty_stripe_customer_id', $stripe_customer_id );

					// Save Source ID to donation note and DB.
					walkthecounty_insert_payment_note( $donation_id, 'Stripe Source/Payment Method ID: ' . $payment_method_id );
					walkthecounty_update_meta( $donation_id, '_walkthecounty_stripe_source_id', $payment_method_id );

					// Save donation summary to donation.
					walkthecounty_update_meta( $donation_id, '_walkthecounty_stripe_donation_summary', $donation_summary );

					/**
					 * This filter hook is used to update the payment intent arguments.
					 *
					 * @since 2.5.0
					 */
					$intent_args = apply_filters(
						'walkthecounty_stripe_create_intent_args',
						array(
							'amount'               => $this->format_amount( $donation_data['price'] ),
							'currency'             => walkthecounty_get_currency( $form_id ),
							'payment_method_types' => [ 'card' ],
							'statement_descriptor' => walkthecounty_stripe_get_statement_descriptor(),
							'description'          => walkthecounty_payment_gateway_donation_summary( $donation_data ),
							'metadata'             => $this->prepare_metadata( $donation_id ),
							'customer'             => $stripe_customer_id,
							'payment_method'       => $payment_method_id,
							'confirm'              => true,
							'return_url'           => walkthecounty_get_success_page_uri(),
						)
					);

					// Send Stripe Receipt emails when enabled.
					if ( walkthecounty_is_setting_enabled( walkthecounty_get_option( 'stripe_receipt_emails' ) ) ) {
						$intent_args['receipt_email'] = $donation_data['user_email'];
					}

					$intent = $this->payment_intent->create( $intent_args );

					// Save Payment Intent Client Secret to donation note and DB.
					walkthecounty_insert_payment_note( $donation_id, 'Stripe Payment Intent Client Secret: ' . $intent->client_secret );
					walkthecounty_update_meta( $donation_id, '_walkthecounty_stripe_payment_intent_client_secret', $intent->client_secret );

					// Set Payment Intent ID as transaction ID for the donation.
					walkthecounty_set_payment_transaction_id( $donation_id, $intent->id );
					walkthecounty_insert_payment_note( $donation_id, 'Stripe Charge/Payment Intent ID: ' . $intent->id );

					// Process additional steps for SCA or 3D secure.
					walkthecounty_stripe_process_additional_authentication( $donation_id, $intent );

					// Send them to success page.
					walkthecounty_send_to_success_page();


				} else {

					// No customer, failed.
					walkthecounty_record_gateway_error(
						__( 'Stripe Customer Creation Failed', 'walkthecounty' ),
						sprintf(
							/* translators: %s Donation Data */
							__( 'Customer creation failed while processing the donation. Details: %s', 'walkthecounty' ),
							wp_json_encode( $donation_data )
						)
					);
					walkthecounty_set_error( 'stripe_error', __( 'The Stripe Gateway returned an error while processing the donation.', 'walkthecounty' ) );
					walkthecounty_send_back_to_checkout( '?payment-mode=' . walkthecounty_clean( $_GET['payment-mode'] ) );

				} // End if().
			} else {
				walkthecounty_send_back_to_checkout( '?payment-mode=' . walkthecounty_clean( $_GET['payment-mode'] ) );
			} // End if().
		}
	}
}
return new WalkTheCounty_Stripe_Card();
