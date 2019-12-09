<?php
/**
 * WalkTheCounty - Stripe Core Admin Actions
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
 * This function is used to save the parameters returned after successfull connection of Stripe account.
 *
 * @since 2.5.0
 *
 * @return void
 */
function walkthecounty_stripe_connect_save_options() {
	// Is user have permission to edit walkthecounty setting.
	if ( ! current_user_can( 'manage_walkthecounty_settings' ) ) {
		return;
	}

	$get_vars = walkthecounty_clean( $_GET );

	// If we don't have values here, bounce.
	if (
		! isset( $get_vars['stripe_publishable_key'] )
		|| ! isset( $get_vars['stripe_user_id'] )
		|| ! isset( $get_vars['stripe_access_token'] )
		|| ! isset( $get_vars['stripe_access_token_test'] )
		|| ! isset( $get_vars['connected'] )
	) {
		return;
	}

	// Update keys.
	walkthecounty_update_option( 'walkthecounty_stripe_connected', $get_vars['connected'] );
	walkthecounty_update_option( 'walkthecounty_stripe_user_id', $get_vars['stripe_user_id'] );
	walkthecounty_update_option( 'live_secret_key', $get_vars['stripe_access_token'] );
	walkthecounty_update_option( 'test_secret_key', $get_vars['stripe_access_token_test'] );
	walkthecounty_update_option( 'live_publishable_key', $get_vars['stripe_publishable_key'] );
	walkthecounty_update_option( 'test_publishable_key', $get_vars['stripe_publishable_key_test'] );

	// Delete option for user API key.
	walkthecounty_delete_option( 'stripe_user_api_keys' );

}
add_action( 'admin_init', 'walkthecounty_stripe_connect_save_options' );

/**
 * Disconnects user from the WalkTheCounty Stripe Connected App.
 */
function walkthecounty_stripe_connect_deauthorize() {

	$get_vars = walkthecounty_clean( $_GET );

	// Be sure only to deauthorize when param present.
	if ( ! isset( $get_vars['stripe_disconnected'] ) ) {
		return false;
	}

	// Show message if NOT disconnected.
	if (
		'false' === $get_vars['stripe_disconnected']
		&& isset( $get_vars['error_code'] )
	) {

		$class   = 'notice notice-warning walkthecounty-stripe-disconnect-message';
		$message = sprintf(
			/* translators: %s Error Message */
			__( '<strong>Error:</strong> WalkTheCountyWP could not disconnect from the Stripe API. Reason: %s', 'walkthecounty' ),
			esc_html( $get_vars['error_message'] )
		);

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message );

	}

	// If user disconnects, remove the options regardless.
	// They can always click reconnect even if connected.
	walkthecounty_stripe_connect_delete_options();

}
add_action( 'admin_notices', 'walkthecounty_stripe_connect_deauthorize' );

/**
 * This function will display field to opt for refund in Stripe.
 *
 * @param int $donation_id Donation ID.
 *
 * @since 2.5.0
 *
 * @return void
 */
function walkthecounty_stripe_opt_refund( $donation_id ) {

	$processed_gateway = WalkTheCounty()->payment_meta->get_meta( $donation_id, '_walkthecounty_payment_gateway', true );

	// Bail out, if the donation is not processed with Stripe payment gateway.
	if ( 'stripe' !== $processed_gateway ) {
		return;
	}
	?>
	<div id="walkthecounty-stripe-opt-refund-wrap" class="walkthecounty-stripe-opt-refund walkthecounty-admin-box-inside walkthecounty-hidden">
		<p>
			<input type="checkbox" id="walkthecounty-stripe-opt-refund" name="walkthecounty_stripe_opt_refund" value="1"/>
			<label for="walkthecounty-stripe-opt-refund">
				<?php esc_html_e( 'Refund Charge in Stripe?', 'walkthecounty' ); ?>
			</label>
		</p>
	</div>

	<?php
}

add_action( 'walkthecounty_view_donation_details_totals_after', 'walkthecounty_stripe_opt_refund', 10, 1 );

/**
 * Process refund in Stripe.
 *
 * @since  2.5.0
 * @access public
 *
 * @param int    $donation_id Donation ID.
 * @param string $new_status  New Donation Status.
 * @param string $old_status  Old Donation Status.
 *
 * @return void
 */
function walkthecounty_stripe_process_refund( $donation_id, $new_status, $old_status ) {

	$stripe_opt_refund_value = ! empty( $_POST['walkthecounty_stripe_opt_refund'] ) ? walkthecounty_clean( $_POST['walkthecounty_stripe_opt_refund'] ) : '';
	$can_process_refund      = ! empty( $stripe_opt_refund_value ) ? $stripe_opt_refund_value : false;

	// Only move forward if refund requested.
	if ( ! $can_process_refund ) {
		return;
	}

	// Verify statuses.
	$should_process_refund = 'publish' !== $old_status ? false : true;
	$should_process_refund = apply_filters( 'walkthecounty_stripe_should_process_refund', $should_process_refund, $donation_id, $new_status, $old_status );

	if ( false === $should_process_refund ) {
		return;
	}

	if ( 'refunded' !== $new_status ) {
		return;
	}

	$charge_id = walkthecounty_get_payment_transaction_id( $donation_id );

	// If no charge ID, look in the payment notes.
	if ( empty( $charge_id ) || $charge_id == $donation_id ) {
		$charge_id = walkthecounty_stripe_get_payment_txn_id_fallback( $donation_id );
	}

	// Bail if no charge ID was found.
	if ( empty( $charge_id ) ) {
		return;
	}

	try {

		$refund = \Stripe\Refund::create( array(
			'charge' => $charge_id,
		) );

		if ( isset( $refund->id ) ) {
			walkthecounty_insert_payment_note(
				$donation_id,
				sprintf(
					/* translators: 1. Refund ID */
					esc_html__( 'Charge refunded in Stripe: %s', 'walkthecounty' ),
					$refund->id
				)
			);
		}
	} catch ( \Stripe\Error\Base $e ) {
		// Refund issue occurred.
		$log_message = __( 'The Stripe payment gateway returned an error while refunding a donation.', 'walkthecounty' ) . '<br><br>';
		$log_message .= sprintf( esc_html__( 'Message: %s', 'walkthecounty' ), $e->getMessage() ) . '<br><br>';
		$log_message .= sprintf( esc_html__( 'Code: %s', 'walkthecounty' ), $e->getCode() );

		// Log it with DB.
		walkthecounty_record_gateway_error( __( 'Stripe Error', 'walkthecounty' ), $log_message );

	} catch ( Exception $e ) {

		// some sort of other error.
		$body = $e->getJsonBody();
		$err  = $body['error'];

		if ( isset( $err['message'] ) ) {
			$error = $err['message'];
		} else {
			$error = esc_html__( 'Something went wrong while refunding the charge in Stripe.', 'walkthecounty' );
		}

		wp_die( $error, esc_html__( 'Error', 'walkthecounty' ), array(
			'response' => 400,
		) );

	} // End try().

	do_action( 'walkthecounty_stripe_donation_refunded', $donation_id );

}

add_action( 'walkthecounty_update_payment_status', 'walkthecounty_stripe_process_refund', 200, 3 );

/**
 * Displays the "WalkTheCounty Connect" banner.
 *
 * @since 2.5.0
 *
 * @see: https://stripe.com/docs/connect/reference
 *
 * @return bool
 */
function walkthecounty_stripe_show_connect_banner() {

	$status                       = true;
	$stripe_payment_methods       = array( 'stripe', 'stripe_ach', 'stripe_google_pay', 'stripe_apple_pay', 'stripe_ideal' );
	$is_any_stripe_gateway_active = array_map( 'walkthecounty_is_gateway_active', $stripe_payment_methods );

	// Don't show banner, if all the stripe gateways are disabled.
	if ( ! in_array( true, $is_any_stripe_gateway_active, true ) ) {
		$status = false;
	}

	// Don't show if already connected.
	if ( walkthecounty_stripe_is_connected() ) {
		$status = false;
	}

	$hide_on_pages = array( 'walkthecounty-about', 'walkthecounty-getting-started', 'walkthecounty-credits', 'walkthecounty-addons' );

	// Don't show if on the about page.
	if ( in_array( walkthecounty_get_current_setting_page(), $hide_on_pages, true ) ) {
		$status = false;
	}

	$hide_on_sections = array( 'stripe-settings', 'gateways-settings', 'stripe-ach-settings' );
	$current_section  = walkthecounty_get_current_setting_section();

	// Don't show if on the payment settings section.
	if (
		'gateways' === walkthecounty_get_current_setting_tab() &&
		(
			empty( $current_section ) ||
			in_array( $current_section, $hide_on_sections, true )
		)
	) {
		$status = false;
	}

	// Don't show for non-admins.
	if ( ! current_user_can( 'update_plugins' ) ) {
		$status = false;
	}

	/**
	 * This filter hook is used to decide whether the connect button banner need to be displayed or not.
	 *
	 * @since 2.5.0
	 */
	$status = apply_filters( 'walkthecounty_stripe_connect_banner_status', $status );

	// Bailout, if status is false.
	if ( false === $status ) {
		return $status;
	}

	$connect_link = walkthecounty_stripe_connect_button();

	// Default message.
	$main_text = __( 'The Stripe gateway is enabled but you\'re not connected. Connect to Stripe to start accepting credit card donations directly on your website.', 'walkthecounty' );

	/**
	 * This filter hook is used to change the text of the connect banner.
	 *
	 * @param string $main_text Text to be displayed on the connect banner.
	 *
	 * @since 2.5.0
	 */
	$main_text = apply_filters( 'walkthecounty_stripe_change_connect_banner_text', $main_text );

	$message = sprintf(
		/* translators: 1. Main Text, 2. Connect Link */
        __( '<p><strong>Stripe Connect:</strong> %1$s </p>%2$s', 'walkthecounty' ),
		$main_text,
		$connect_link
	);

	// Register Notice.
	WalkTheCounty()->notices->register_notice( array(
		'id'               => 'walkthecounty-stripe-connect-banner',
		'description'      => $message,
		'type'             => 'warning',
		'dismissible_type' => 'user',
		'dismiss_interval' => 'shortly',
	) );
}

add_action( 'admin_notices', 'walkthecounty_stripe_show_connect_banner' );
