<?php
/**
 * WalkTheCounty - Stripe Core Admin Helper Functions.
 *
 * @since 2.5.4
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
 * This function is used to get a list of slug which are supported by payment gateways.
 *
 * @since 2.5.5
 *
 * @return array
 */
function walkthecounty_stripe_supported_payment_methods() {
	return array(
		'stripe',
		'stripe_ach',
		'stripe_ideal',
		'stripe_google_pay',
		'stripe_apple_pay',
		'stripe_checkout',
	);
}

/**
 * This function is used to check whether a payment method supported by Stripe with WalkTheCounty is active or not.
 *
 * @since 2.5.5
 *
 * @return bool
 */
function walkthecounty_stripe_is_any_payment_method_active() {

	// Get settings.
	$settings = walkthecounty_get_settings();
	$gateways = isset( $settings['gateways'] ) ? $settings['gateways'] : array();

	// Loop through gateways list.
	foreach ( array_keys( $gateways ) as $gateway ) {

		// Return true, if even single payment method is active.
		if ( in_array( $gateway, walkthecounty_stripe_supported_payment_methods(), true ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Get Settings for the Stripe account connected via Connect API.
 *
 * @since 2.5.0
 *
 * @return mixed
 */
function walkthecounty_stripe_get_connect_settings() {

	$options = array(
		'connected_status'     => walkthecounty_get_option( 'walkthecounty_stripe_connected' ),
		'user_id'              => walkthecounty_get_option( 'walkthecounty_stripe_user_id' ),
		'access_token'         => walkthecounty_get_option( 'live_secret_key' ),
		'access_token_test'    => walkthecounty_get_option( 'test_secret_key' ),
		'publishable_key'      => walkthecounty_get_option( 'live_publishable_key' ),
		'publishable_key_test' => walkthecounty_get_option( 'test_publishable_key' ),
	);

	/**
	 * This filter hook is used to override the existing stripe connect settings stored in DB.
	 *
	 * @param array $options List of Stripe Connect settings required to make functionality work.
	 *
	 * @since 2.5.0
	 */
	return apply_filters( 'walkthecounty_stripe_get_connect_settings', $options );
}

/**
 * Is Stripe connected using Connect API?
 *
 * @since 2.5.0
 *
 * @return bool
 */
function walkthecounty_stripe_is_connected() {

	$settings = walkthecounty_stripe_get_connect_settings();

	$user_api_keys_enabled = walkthecounty_is_setting_enabled( walkthecounty_get_option( 'stripe_user_api_keys' ) );

	// Return false, if manual API keys are used to configure Stripe.
	if ( $user_api_keys_enabled ) {
		return false;
	}

	// Check all the necessary options.
	if (
		! empty( $settings['connected_status'] ) && '1' === $settings['connected_status']
		&& ! empty( $settings['user_id'] )
		&& ! empty( $settings['access_token'] )
		&& ! empty( $settings['access_token_test'] )
		&& ! empty( $settings['publishable_key'] )
		&& ! empty( $settings['publishable_key_test'] )
	) {
		return true;
	}

	// Default return value.
	return false;
}

/**
 * Is Stripe Checkout Enabled?
 *
 * @since 2.5.0
 *
 * @return bool
 */
function walkthecounty_stripe_is_checkout_enabled() {
	return walkthecounty_is_setting_enabled( walkthecounty_get_option( 'stripe_checkout_enabled', 'disabled' ) );
}

/**
 * Displays Stripe Connect Button.
 *
 * @since 2.5.0
 *
 * @return string
 */
function walkthecounty_stripe_connect_button() {

	$connected = walkthecounty_get_option( 'walkthecounty_stripe_connected' );

	// Prepare Stripe Connect URL.
	$link = add_query_arg(
		array(
			'stripe_action'         => 'connect',
			'mode'                  => walkthecounty_is_test_mode() ? 'test' : 'live',
			'return_url'            => rawurlencode( admin_url( 'edit.php?post_type=walkthecounty_forms&page=walkthecounty-settings&tab=gateways&section=stripe-settings' ) ),
			'website_url'           => get_bloginfo( 'url' ),
			'walkthecounty_stripe_connected' => ! empty( $connected ) ? '1' : '0',
		),
		esc_url_raw( 'https://connect.walkthecountywp.com/stripe/connect.php' )
	);

	return sprintf(
		'<a href="%1$s" id="walkthecounty-stripe-connect"><span>%2$s</span></a>',
		esc_url( $link ),
        esc_html__( 'Connect with Stripe', 'walkthecounty' )
	);
}

/**
 * Stripe Disconnect URL.
 *
 * @since 2.5.0
 *
 * @return void
 */
function walkthecounty_stripe_disconnect_url() {

	// Prepare Stripe Disconnect URL.
	$link = add_query_arg(
		array(
			'stripe_action'  => 'disconnect',
			'mode'           => walkthecounty_is_test_mode() ? 'test' : 'live',
			'stripe_user_id' => walkthecounty_get_option( 'walkthecounty_stripe_user_id' ),
			'return_url'     => rawurlencode( admin_url( 'edit.php?post_type=walkthecounty_forms&page=walkthecounty-settings&tab=gateways&section=stripe-settings' ) ),
		),
		esc_url_raw( 'https://connect.walkthecountywp.com/stripe/connect.php' )
	);

	echo esc_url( $link );
}

/**
 * Delete all the WalkTheCounty settings options for Stripe Connect.
 *
 * @since 2.5.0
 *
 * @return void
 */
function walkthecounty_stripe_connect_delete_options() {

	// Disconnection successful.
	// Remove the connect options within the db.
	walkthecounty_delete_option( 'walkthecounty_stripe_connected' );
	walkthecounty_delete_option( 'walkthecounty_stripe_user_id' );
	walkthecounty_delete_option( 'live_secret_key' );
	walkthecounty_delete_option( 'test_secret_key' );
	walkthecounty_delete_option( 'live_publishable_key' );
	walkthecounty_delete_option( 'test_publishable_key' );
}
