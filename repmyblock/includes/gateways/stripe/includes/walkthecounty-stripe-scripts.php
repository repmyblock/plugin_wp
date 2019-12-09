<?php
/**
 * WalkTheCounty - Stripe Scripts
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
 * Load Frontend javascript
 *
 * @since 2.5.0
 *
 * @return void
 */
function walkthecounty_stripe_frontend_scripts() {

	// Get publishable key.
	$publishable_key = walkthecounty_stripe_get_publishable_key();

	// Checkout options.
	// @TODO: convert checkboxes to radios.
	$zip_option      = walkthecounty_is_setting_enabled( walkthecounty_get_option( 'stripe_checkout_zip_verify' ) );
	$remember_option = walkthecounty_is_setting_enabled( walkthecounty_get_option( 'stripe_checkout_remember_me' ) );

	$stripe_card_update = false;
	$get_data           = walkthecounty_clean( filter_input_array( INPUT_GET ) );

	if ( isset( $get_data['action'] ) &&
		'update' === $get_data['action'] &&
		isset( $get_data['subscription_id'] ) &&
		is_numeric( $get_data['subscription_id'] )
	) {
		$stripe_card_update = true;
	}

	// Set vars for AJAX.
	$stripe_vars = array(
		'zero_based_currency'          => walkthecounty_is_zero_based_currency(),
		'zero_based_currencies_list'   => walkthecounty_get_zero_based_currencies(),
		'sitename'                     => walkthecounty_get_option( 'stripe_checkout_name' ),
		'publishable_key'              => $publishable_key,
		'checkout_image'               => walkthecounty_get_option( 'stripe_checkout_image' ),
		'checkout_address'             => walkthecounty_get_option( 'stripe_collect_billing' ),
		'checkout_processing_text'     => walkthecounty_get_option( 'stripe_checkout_processing_text', __( 'Donation Processing...', 'walkthecounty' ) ),
		'zipcode_option'               => $zip_option,
		'remember_option'              => $remember_option,
		'walkthecounty_version'                 => get_option( 'walkthecounty_version' ),
		'cc_fields_format'             => walkthecounty_get_option( 'stripe_cc_fields_format', 'multi' ),
		'card_number_placeholder_text' => __( 'Card Number', 'walkthecounty' ),
		'card_cvc_placeholder_text'    => __( 'CVC', 'walkthecounty' ),
		'donate_button_text'           => __( 'Donate Now', 'walkthecounty' ),
		'element_font_styles'          => walkthecounty_stripe_get_element_font_styles(),
		'element_base_styles'          => walkthecounty_stripe_get_element_base_styles(),
		'element_complete_styles'      => walkthecounty_stripe_get_element_complete_styles(),
		'element_empty_styles'         => walkthecounty_stripe_get_element_empty_styles(),
		'element_invalid_styles'       => walkthecounty_stripe_get_element_invalid_styles(),
		'float_labels'                 => walkthecounty_is_float_labels_enabled( array(
			'form_id' => get_the_ID(),
		) ),
		'base_country'                 => walkthecounty_get_option( 'base_country' ),
		'stripe_card_update'           => $stripe_card_update,
		'stripe_account_id'            => walkthecounty_stripe_is_connected() ? walkthecounty_get_option( 'walkthecounty_stripe_user_id' ) : false,
		'preferred_locale'             => walkthecounty_stripe_get_preferred_locale(),
	);

	// Load third-party stripe js when required gateways are active.
	if ( apply_filters( 'walkthecounty_stripe_js_loading_conditions', walkthecounty_stripe_is_any_payment_method_active() ) ) {
		WalkTheCounty_Scripts::register_script( 'walkthecounty-stripe-js', 'https://js.stripe.com/v3/', array(), WALKTHECOUNTY_VERSION );
		wp_enqueue_script( 'walkthecounty-stripe-js' );
		wp_localize_script( 'walkthecounty-stripe-js', 'walkthecounty_stripe_vars', $stripe_vars );
	}

	// Load legacy Stripe checkout when the checkout type is `modal`.
	if ( 'modal' === walkthecounty_stripe_get_checkout_type() ) {

		// Stripe checkout js.
		WalkTheCounty_Scripts::register_script( 'walkthecounty-stripe-checkout-js', 'https://checkout.stripe.com/checkout.js', array( 'jquery' ), WALKTHECOUNTY_VERSION );
		wp_enqueue_script( 'walkthecounty-stripe-checkout-js' );

		$deps = array(
			'jquery',
			'walkthecounty',
			'walkthecounty-stripe-checkout-js',
		);

		// WalkTheCounty Stripe Checkout JS.
		WalkTheCounty_Scripts::register_script( 'walkthecounty-stripe-popup-js', WALKTHECOUNTY_PLUGIN_URL . 'assets/dist/js/walkthecounty-stripe-checkout.js', $deps, WALKTHECOUNTY_VERSION );
		wp_enqueue_script( 'walkthecounty-stripe-popup-js' );
		wp_localize_script( 'walkthecounty-stripe-popup-js', 'walkthecounty_stripe_vars', $stripe_vars );
	}

	// Load Stripe onpage credit card JS when Stripe credit card payment method is active.
	if ( walkthecounty_is_gateway_active( 'stripe' ) ) {
		WalkTheCounty_Scripts::register_script( 'walkthecounty-stripe-onpage-js', WALKTHECOUNTY_PLUGIN_URL . 'assets/dist/js/walkthecounty-stripe.js', array( 'walkthecounty-stripe-js' ), WALKTHECOUNTY_VERSION );
		wp_enqueue_script( 'walkthecounty-stripe-onpage-js' );
	}
}

add_action( 'wp_enqueue_scripts', 'walkthecounty_stripe_frontend_scripts' );

/**
 * WooCommerce checkout compatibility.
 *
 * This prevents WalkTheCounty from outputting scripts on Woo's checkout page.
 *
 * @since 1.4.3
 *
 * @param bool $ret JS compatibility status.
 *
 * @return bool
 */
function walkthecounty_stripe_woo_script_compatibility( $ret ) {

	if (
		function_exists( 'is_checkout' )
		&& is_checkout()
	) {
		return false;
	}

	return $ret;

}

add_filter( 'walkthecounty_stripe_js_loading_conditions', 'walkthecounty_stripe_woo_script_compatibility', 10, 1 );


/**
 * EDD checkout compatibility.
 *
 * This prevents WalkTheCounty from outputting scripts on EDD's checkout page.
 *
 * @since 1.4.6
 *
 * @param bool $ret JS compatibility status.
 *
 * @return bool
 */
function walkthecounty_stripe_edd_script_compatibility( $ret ) {

	if (
		function_exists( 'edd_is_checkout' )
		&& edd_is_checkout()
	) {
		return false;
	}

	return $ret;

}

add_filter( 'walkthecounty_stripe_js_loading_conditions', 'walkthecounty_stripe_edd_script_compatibility', 10, 1 );
