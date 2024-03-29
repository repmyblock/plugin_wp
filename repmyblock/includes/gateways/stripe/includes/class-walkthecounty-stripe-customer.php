<?php
/**
 * WalkTheCounty - Stripe Customer
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
 * Class WalkTheCounty_Stripe_Customer.
 *
 * @since 2.5.0
 */
class WalkTheCounty_Stripe_Customer {

	/**
	 * Stripe Customer ID.
	 *
	 * @since  2.5.0
	 * @access private
	 *
	 * @var string
	 */
	private $id = '';

	/**
	 * Stripe Payment Method ID.
	 *
	 * @since  2.5.0
	 * @access private
	 *
	 * @var string
	 */
	private $payment_method_id = '';

	/**
	 * Donor Email.
	 *
	 * @since  2.5.0
	 * @access private
	 *
	 * @var string
	 */
	private $donor_email = '';

	/**
	 * Stripe Customer Data.
	 *
	 * @since  2.5.0
	 * @access private
	 *
	 * @var \Stripe\Customer
	 */
	public $customer_data = array();

	/**
	 * Stripe Gateway Object.
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @var array|WalkTheCounty_Stripe_Gateway
	 */
	public $stripe_gateway = array();

	/**
	 * Attached Payment Method to Customer.
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @var array|\Stripe\PaymentMethod
	 */
	public $attached_payment_method = array();

	/**
	 * Check for card existence for customer.
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @var bool
	 */
	public $is_card_exists = false;

	/**
	 * WalkTheCounty_Stripe_Customer constructor.
	 *
	 * @param string $email             Donor Email.
	 * @param string $payment_method_id Stripe Payment Method ID.
	 *
	 * @since  2.5.0
	 * @access public
	 */
	public function __construct( $email, $payment_method_id = '' ) {

		$this->donor_email       = $email;
		$this->payment_method_id = $payment_method_id;
		$this->stripe_gateway    = new WalkTheCounty_Stripe_Gateway();

		$this->set_id( walkthecounty_stripe_get_customer_id( $email ) );
		$this->get_or_create_customer();
	}

	/**
	 * Get Stripe customer ID.
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Set Stripe customer ID.
	 *
	 * @param string $id Stripe Customer ID.
	 *
	 * @since  2.5.0
	 * @access public
	 */
	public function set_id( $id ) {
		$this->id = walkthecounty_clean( $id );
	}

	/**
	 * Store data from the Stripe API about this customer
	 *
	 * @param /Stripe/Customer $data Stripe Customer Object.
	 *
	 * @since  2.5.0
	 * @access public
	 */
	public function set_customer_data( $data ) {
		$this->customer_data = $data;
	}

	/**
	 * Get the Stripe customer object. If not found, create the customer with Stripe's API.
	 * Save the customer ID appropriately in the database.
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @return bool|\Stripe\Customer
	 */
	public function get_or_create_customer() {

		$customer = false;

		// No customer ID found, look up based on the email.
		$stripe_customer_id = walkthecounty_stripe_get_customer_id( $this->donor_email );

		// There is a customer ID. Check if it is active still in Stripe.
		if ( ! empty( $stripe_customer_id ) ) {

			// Set Application Info.
			walkthecounty_stripe_set_app_info();

			try {

				// Retrieve the customer to ensure the customer has not been deleted.
				$customer = \Stripe\Customer::retrieve( $stripe_customer_id, walkthecounty_stripe_get_connected_account_options() );

				if ( isset( $customer->deleted ) && $customer->deleted ) {

					// This customer was deleted.
					$customer = false;
				}
			} catch ( \Stripe\Error\InvalidRequest $e ) {

				$error_object = $e->getJsonBody();

				if ( $this->is_no_such_customer_error( $error_object['error'] ) ) {
					$customer = $this->create_customer();
				} else {

					// Record Log.
					walkthecounty_stripe_record_log(
						__( 'Stripe - Customer Creation Error', 'walkthecounty' ),
						$e->getMessage()
					);
				}
			} catch ( Exception $e ) {
				$customer = false;
			}
		}

		// Create the Stripe customer if not present.
		if ( ! $customer ) {
			$customer = $this->create_customer();
		}

		$this->set_id( $customer->id );
		$this->set_customer_data( $customer );

		// Proceed only, if the source is not empty.
		if ( ! empty( $this->payment_method_id ) ) {
			// Attach source/payment method to customer.
			if (walkthecounty_stripe_is_source_type($this->payment_method_id, 'pm')) {
				$this->attach_payment_method();
			} else {
				$this->attach_source();
			}
		}

		return $customer;

	}

	/**
	 * This function is used to update the customer records in Stripe.
	 *
	 * @param string $id   Customer ID provided by Stripe.
	 * @param array  $args List of arguments to update customer details.
	 *
	 * @since 2.5.10
	 *
	 * @return bool|\Stripe\Customer
	 */
	public function update_customer( $id, $args ) {

		walkthecounty_stripe_set_app_info();

		$customer = false;

		try {
			$customer = \Stripe\Customer::update( $id, $args );
		} catch( Exception $e ) {
			// Record Log.
			walkthecounty_stripe_record_log(
				__( 'Stripe - Customer Update Error', 'walkthecounty' ),
				$e->getMessage()
			);
		}

		return $customer;
	}

	/**
	 * This function is used to set the payment method as default.
	 *
	 * @param string $id          Payment Method ID provided by Stripe.
	 * @param string $customer_id Customer ID provided by Stripe.
	 *
	 * @since 2.5.10
	 *
	 * @return \Stripe\Customer
	 */
	public function set_default_payment_method( $id, $customer_id ) {

		$update_args = array(
			'invoice_settings' => array(
				'default_payment_method' => $id,
			),
		);

		return $this->update_customer( $customer_id, $update_args );
	}

	/**
	 * Create a Customer in Stripe.
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @return bool|\Stripe\Customer
	 */
	public function create_customer() {

		$customer     = false;
		$post_data    = walkthecounty_clean( $_POST );
		$payment_mode = ! empty( $post_data['walkthecounty-gateway'] ) ? $post_data['walkthecounty-gateway'] : '';
		$form_id      = ! empty( $post_data['walkthecounty-form-id'] ) ? $post_data['walkthecounty-form-id'] : false;
		$first_name   = ! empty( $post_data['walkthecounty_first'] ) ? $post_data['walkthecounty_first'] : '';
		$last_name    = ! empty( $post_data['walkthecounty_last'] ) ? $post_data['walkthecounty_last'] : '';
		$full_name    = ! empty( $last_name ) ? "{$first_name} {$last_name}" : $first_name;

		// Set Application Info.
		walkthecounty_stripe_set_app_info();

		try {

			$metadata = array(
				'first_name' => $first_name,
				'last_name'  => $last_name,
				'created_by' => $post_data['walkthecounty-form-title'],
			);

			// Add address to customer metadata if present.
			if ( ! empty( $post_data['billing_country'] ) ) {
				$metadata['address_line1']   = isset( $post_data['card_address'] ) ? $post_data['card_address'] : '';
				$metadata['address_line2']   = isset( $post_data['card_address_2'] ) ? $post_data['card_address_2'] : '';
				$metadata['address_city']    = isset( $post_data['card_city'] ) ? $post_data['card_city'] : '';
				$metadata['address_state']   = isset( $post_data['card_state'] ) ? $post_data['card_state'] : '';
				$metadata['address_country'] = isset( $post_data['billing_country'] ) ? $post_data['billing_country'] : '';
				$metadata['address_zip']     = isset( $post_data['card_zip'] ) ? $post_data['card_zip'] : '';
			}

			// Add company name to customer metadata.
			if ( walkthecounty_is_company_field_enabled( $form_id ) ) {
				$metadata['company_name'] = ! empty( $post_data['walkthecounty_company_name'] ) ? $post_data['walkthecounty_company_name'] : '';
			}

			/**
			 * This filter will be used to modify customer arguments based on the need.
			 *
			 * @param array $args List of customer arguments from Stripe.
			 *
			 * @since 2.5.0
			 */
			$args = apply_filters(
				'walkthecounty_stripe_customer_args',
				array(
					'description'    => sprintf(
						/* translators: %s Site URL */
						__( 'Stripe Customer generated by WalkTheCountyWP via %s', 'walkthecounty' ),
						get_bloginfo( 'url' )
					),
					'name'           => $full_name,
					'email'          => $this->donor_email,
					'metadata'       => apply_filters( 'walkthecounty_stripe_customer_metadata', $metadata, $post_data ),
				)
			);

			// Add these parameters when payment method/source id exists.
			if ( ! empty( $this->payment_method_id ) ) {
				if ( walkthecounty_stripe_is_source_type( $this->payment_method_id, 'pm' ) ) {
					$args['payment_method'] = $this->payment_method_id;
				} else {
					$args['source'] = $this->payment_method_id;
				}
			}

			// Create a customer first so we can retrieve them later for future payments.
			$customer = \Stripe\Customer::create( $args, walkthecounty_stripe_get_connected_account_options() );

		} catch ( \Stripe\Error\Base $e ) {
			// Record Log.
			walkthecounty_stripe_record_log(
				__( 'Stripe - Customer Creation Error', 'walkthecounty' ),
				$e->getMessage()
			);

		} catch ( Exception $e ) {
			walkthecounty_record_gateway_error(
				__( 'Stripe Error', 'walkthecounty' ),
				sprintf(
					/* translators: %s Exception Message Body */
					__( 'The Stripe Gateway returned an error while creating the customer. Details: %s', 'walkthecounty' ),
					$e->getMessage()
				)
			);
			walkthecounty_set_error( 'stripe_error', __( 'An occurred while processing the donation with the gateway. Please try your donation again.', 'walkthecounty' ) );
			walkthecounty_send_back_to_checkout( "?payment-mode={$payment_mode}&form_id={$post_data['post_data']['walkthecounty-form-id']}" );
		} // End try().

		if ( ! empty( $customer->id ) ) {
			// Return obj.
			return $customer;
		} else {
			return false;
		}

	}

	/**
	 * This function is used to attach source to the customer, if not exists.
	 *
	 * @since  2.1
	 * @access public
	 *
	 * @return void
	 */
	public function attach_source() {

		if ( ! empty( $this->payment_method_id ) && ! empty( $this->customer_data ) ) {

			$card        = '';
			$card_exists = false;
			$new_card    = '';
			$all_sources = $this->customer_data->sources->all();

			// Fetch the new card or source object to match with customer attached card fingerprint.
			if ( walkthecounty_stripe_is_source_type( $this->payment_method_id, 'tok' ) ) {
				$token_details = $this->stripe_gateway->get_token_details( $this->payment_method_id );
				$new_card = $token_details->card;
			} elseif ( walkthecounty_stripe_is_source_type( $this->payment_method_id, 'src' ) ) {
				$source_details = $this->stripe_gateway->get_source_details( $this->payment_method_id );
				$new_card = $source_details->card;
			}

			/**
			 * This filter hook is used to get new card details.
			 *
			 * @since 2.5.0
			 */
			$new_card = apply_filters( 'walkthecounty_stripe_get_new_card_details', $new_card, $this->payment_method_id, $this->stripe_gateway );

			// Check to ensure that new card is already attached with customer or not.
			if ( count( $all_sources->data ) > 0 ) {
				foreach ( $all_sources->data as $source_item ) {

					if (
						(
							isset( $source_item->card->fingerprint ) &&
							$source_item->card->fingerprint === $new_card->fingerprint
						) ||
						(
							isset( $source_item->fingerprint ) &&
							$source_item->fingerprint === $new_card->fingerprint
						)
					) {

						// Set the existing card as default source.
						$this->customer_data->default_source = $source_item->id;
						$this->customer_data->save();
						$card                 = $source_item;
						$card_exists          = true;
						$this->is_card_exists = true;
						break;
					}
				}
			}

			// Create the card, if none found above.
			if ( ! $card_exists ) {
				try {

					$card = $this->customer_data->sources->create( array(
						'source' => $this->payment_method_id,
					) );

					$this->customer_data->default_source = $card->id;
					$this->customer_data->save();

				} catch ( \Stripe\Error\Base $e ) {
					WalkTheCounty_Stripe_Logger::log_error( $e, 'stripe' );
				} catch ( Exception $e ) {
					walkthecounty_record_gateway_error(
						__( 'Stripe Error', 'walkthecounty' ),
						sprintf(
							/* translators: %s Exception Message Body */
							__( 'The Stripe Gateway returned an error while creating the customer. Details: %s', 'walkthecounty' ),
							$e->getMessage()
						)
					);
					walkthecounty_set_error( 'stripe_error', __( 'An occurred while processing the donation with the gateway. Please try your donation again.', 'walkthecounty' ) );
					walkthecounty_send_back_to_checkout( '?payment-mode=' . walkthecounty_clean( $_GET['payment-mode'] ) );
					return false;
				}
			}

			// Return Card Details, if exists.
			if ( ! empty( $card->id ) ) {
				$this->attached_payment_method = $card;
			} else {
				walkthecounty_set_error( 'stripe_error', __( 'An error occurred while processing the donation. Please try again.', 'walkthecounty' ) );
				walkthecounty_record_gateway_error( __( 'Stripe Error', 'walkthecounty' ), __( 'An error occurred retrieving or creating the ', 'walkthecounty' ) );
				walkthecounty_send_back_to_checkout( '?payment-mode=' . walkthecounty_clean( $_GET['payment-mode'] ) );
				$this->attached_payment_method = false;
			}
		} // End if().
	}

	/**
	 * This function is used to attach source to the customer, if not exists.
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @return void
	 */
	public function attach_payment_method() {

		if ( ! empty( $this->payment_method_id ) && ! empty( $this->customer_data ) ) {

			$payment_method     = '';
			$payment_methods    = $this->stripe_gateway->payment_method->list_all( $this->id ); // All payment methods.
			$new_payment_method = $this->stripe_gateway->payment_method->retrieve( $this->payment_method_id );

			/**
			 * This filter hook is used to get new card details.
			 *
			 * @since 2.5.0
			 */
			$new_payment_method = apply_filters( 'walkthecounty_stripe_get_new_card_details', $new_payment_method, $this->payment_method_id, $this->stripe_gateway );

			// Check to ensure that new card is already attached with customer's payment methods or not.
			if ( count( $payment_methods->data ) > 0 ) {
				foreach ( $payment_methods->data as $card_details ) {

					// If fingerprint of new and existing payment method doesn't match then continue to next iteration.
					if ( $card_details->card->fingerprint !== $new_payment_method->card->fingerprint ) {
						continue;
					}

					if (
						$card_details->card->exp_month !== $new_payment_method->card->exp_month ||
						$card_details->card->exp_year !== $new_payment_method->card->exp_year
					) {

						// Set updated expiry date to the existing card.
						$this->stripe_gateway->payment_method->update(
							$card_details->id,
							array(
								'card' => array(
									'exp_month' => $new_payment_method->card->exp_month,
									'exp_year'  => $new_payment_method->card->exp_year,
								),
							)
						);
					}

					// Set existing card as default payment method.
					$this->set_default_payment_method( $card_details->id, $this->id );

					$payment_method       = $card_details;
					$this->is_card_exists = true;

				}
			}

			// Create the card, if none found above.
			if ( ! $this->is_card_exists ) {

				// Set new card as default payment method.
				$this->set_default_payment_method( $this->payment_method_id, $this->id );

				// Assign the new payment method.
				$payment_method = $new_payment_method;
			}

			// Return Card Details, if exists.
			if ( ! empty( $payment_method->id ) ) {
				$this->attached_payment_method = $payment_method;
			} else {

				walkthecounty_set_error( 'stripe_error', __( 'An error occurred while processing the donation. Please try again.', 'walkthecounty' ) );
				walkthecounty_record_gateway_error( __( 'Stripe Error', 'walkthecounty' ), __( 'An error occurred retrieving or creating the ', 'walkthecounty' ) );
				walkthecounty_send_back_to_checkout( '?payment-mode=stripe' );

				$this->attached_payment_method = false;
			}
		} // End if().
	}

	/**
	 * This function will check whether the error says no such customer.
	 *
	 * @param \Stripe\Error\InvalidRequest $error Invalid Request Error.
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @return bool
	 */
	public function is_no_such_customer_error( $error ) {
		return (
			$error &&
			'invalid_request_error' === $error['type'] &&
			preg_match( '/No such customer/i', $error['message'] )
		);
	}

	/**
	 * This function will check whether the ID provided is Card ID?
	 *
	 * @param string $id Card ID.
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @return bool
	 */
	public function is_card( $id ) {
		return walkthecounty_stripe_is_source_type( $id, 'card' );
	}

	/**
	 * This function will check whether the ID provided is Source ID?
	 *
	 * @param string $id Source ID.
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @return bool
	 */
	public function is_source( $id ) {
		return walkthecounty_stripe_is_source_type( $id, 'src' );
	}

	/**
	 * This function will check whether the ID provided is Bank Account ID?
	 *
	 * @param string $id Source ID.
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @return bool
	 */
	public function is_bank_account( $id ) {
		return walkthecounty_stripe_is_source_type( $id, 'ba' );
	}
}
