<?php
/**
 * WalkTheCounty - Stripe Frontend Actions
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
 * Stripe uses it's own credit card form because the card details are tokenized.
 *
 * We don't want the name attributes to be present on the fields in order to
 * prevent them from getting posted to the server.
 *
 * @param int  $form_id Donation Form ID.
 * @param int  $args    Donation Form Arguments.
 * @param bool $echo    Status to display or not.
 *
 * @access public
 * @since  1.0
 *
 * @return string $form
 */
function walkthecounty_stripe_credit_card_form( $form_id, $args, $echo = true ) {

	$id_prefix              = ! empty( $args['id_prefix'] ) ? $args['id_prefix'] : '';
	$publishable_key        = walkthecounty_stripe_get_publishable_key();
	$secret_key             = walkthecounty_stripe_get_secret_key();
	$stripe_cc_field_format = walkthecounty_get_option( 'stripe_cc_fields_format', 'multi' );

	ob_start();

	do_action( 'walkthecounty_before_cc_fields', $form_id ); ?>

	<fieldset id="walkthecounty_cc_fields" class="walkthecounty-do-validate">
		<legend>
			<?php esc_attr_e( 'Credit Card Info', 'walkthecounty' ); ?>
		</legend>

		<?php
		if ( is_ssl() ) {
			?>
			<div id="walkthecounty_secure_site_wrapper">
				<span class="walkthecounty-icon padlock"></span>
				<span>
					<?php esc_attr_e( 'This is a secure SSL encrypted payment.', 'walkthecounty' ); ?>
				</span>
			</div>
			<?php
		}

		if (
				! is_ssl() &&
				! walkthecounty_is_test_mode() &&
				(
					empty( $publishable_key ) ||
					empty( $secret_key )
				)
			) {
					WalkTheCounty()->notices->print_frontend_notice(
						sprintf(
							'<strong>%1$s</strong> %2$s',
							esc_html__( 'Notice:', 'walkthecounty' ),
							esc_html__( 'Credit card fields are disabled because Stripe is not connected and your site is not running securely over HTTPS.', 'walkthecounty' )
						)
					);
		} elseif (
			empty( $publishable_key ) ||
			empty( $secret_key )
		) {
			WalkTheCounty()->notices->print_frontend_notice(
				sprintf(
					'<strong>%1$s</strong> %2$s',
					esc_html__( 'Notice:', 'walkthecounty' ),
					esc_html__( 'Credit card fields are disabled because Stripe is not connected.', 'walkthecounty' )
				)
			);
		} elseif ( ! is_ssl() && ! walkthecounty_is_test_mode() ) {
			WalkTheCounty()->notices->print_frontend_notice(
				sprintf(
					'<strong>%1$s</strong> %2$s',
					esc_html__( 'Notice:', 'walkthecounty' ),
					esc_html__( 'Credit card fields are disabled because your site is not running securely over HTTPS.', 'walkthecounty' )
				)
			);
		} else {
			if ( 'single' === $stripe_cc_field_format ) {

				// Display the stripe container which can be occupied by Stripe for CC fields.
				echo '<div id="walkthecounty-stripe-single-cc-fields-' . esc_html( $id_prefix ) . '" class="walkthecounty-stripe-single-cc-field-wrap"></div>';

			} elseif ( 'multi' === $stripe_cc_field_format ) {
				?>
				<div id="walkthecounty-card-number-wrap" class="form-row form-row-two-thirds form-row-responsive walkthecounty-stripe-cc-field-wrap">
					<div>
						<label for="walkthecounty-card-number-field-<?php echo esc_html( $id_prefix ); ?>" class="walkthecounty-label">
							<?php esc_attr_e( 'Card Number', 'walkthecounty' ); ?>
							<span class="walkthecounty-required-indicator">*</span>
							<span class="walkthecounty-tooltip walkthecounty-icon walkthecounty-icon-question"
								data-tooltip="<?php esc_attr_e( 'The (typically) 16 digits on the front of your credit card.', 'walkthecounty' ); ?>"></span>
							<span class="card-type"></span>
						</label>
						<div id="walkthecounty-card-number-field-<?php echo esc_html( $id_prefix ); ?>" class="input empty walkthecounty-stripe-cc-field walkthecounty-stripe-card-number-field"></div>
					</div>
				</div>

				<div id="walkthecounty-card-cvc-wrap" class="form-row form-row-one-third form-row-responsive walkthecounty-stripe-cc-field-wrap">
					<div>
						<label for="walkthecounty-card-cvc-field-<?php echo esc_html( $id_prefix ); ?>" class="walkthecounty-label">
							<?php esc_attr_e( 'CVC', 'walkthecounty' ); ?>
							<span class="walkthecounty-required-indicator">*</span>
							<span class="walkthecounty-tooltip walkthecounty-icon walkthecounty-icon-question"
								data-tooltip="<?php esc_attr_e( 'The 3 digit (back) or 4 digit (front) value on your card.', 'walkthecounty' ); ?>"></span>
						</label>
						<div id="walkthecounty-card-cvc-field-<?php echo esc_html( $id_prefix ); ?>" class="input empty walkthecounty-stripe-cc-field walkthecounty-stripe-card-cvc-field"></div>
					</div>
				</div>

				<div id="walkthecounty-card-name-wrap" class="form-row form-row-two-thirds form-row-responsive">
					<label for="card_name" class="walkthecounty-label">
						<?php esc_attr_e( 'Cardholder Name', 'walkthecounty' ); ?>
						<span class="walkthecounty-required-indicator">*</span>
						<span class="walkthecounty-tooltip walkthecounty-icon walkthecounty-icon-question"
							data-tooltip="<?php esc_attr_e( 'The name of the credit card account holder.', 'walkthecounty' ); ?>"></span>
					</label>
					<input
						type="text"
						autocomplete="off"
						id="card_name"
						name="card_name"
						class="card-name walkthecounty-input required"
						placeholder="<?php esc_attr_e( 'Cardholder Name', 'walkthecounty' ); ?>"
					/>
				</div>

				<?php do_action( 'walkthecounty_before_cc_expiration' ); ?>

				<div id="walkthecounty-card-expiration-wrap" class="card-expiration form-row form-row-one-third form-row-responsive walkthecounty-stripe-cc-field-wrap">
					<div>
						<label for="walkthecounty-card-expiration-field-<?php echo esc_html( $id_prefix ); ?>" class="walkthecounty-label">
							<?php esc_attr_e( 'Expiration', 'walkthecounty' ); ?>
							<span class="walkthecounty-required-indicator">*</span>
							<span class="walkthecounty-tooltip walkthecounty-icon walkthecounty-icon-question"
								data-tooltip="<?php esc_attr_e( 'The date your credit card expires, typically on the front of the card.', 'walkthecounty' ); ?>"></span>
						</label>

						<div id="walkthecounty-card-expiration-field-<?php echo esc_html( $id_prefix ); ?>" class="input empty walkthecounty-stripe-cc-field walkthecounty-stripe-card-expiration-field"></div>
					</div>
				</div>
				<?php
			} // End if().

			/**
			 * This action hook is used to display content after the Credit Card expiration field.
			 *
			 * Note: Kept this hook as it is.
			 *
			 * @since 2.5.0
			 *
			 * @param int   $form_id Donation Form ID.
			 * @param array $args    List of additional arguments.
			 */
			do_action( 'walkthecounty_after_cc_expiration', $form_id, $args );

			/**
			 * This action hook is used to display content after the Credit Card expiration field.
			 *
			 * @since 2.5.0
			 *
			 * @param int   $form_id Donation Form ID.
			 * @param array $args    List of additional arguments.
			 */
			do_action( 'walkthecounty_stripe_after_cc_expiration', $form_id, $args );
		}
		?>
    </fieldset>
	<?php
	// Remove Address Fields if user has option enabled.
	$billing_fields_enabled = walkthecounty_get_option( 'stripe_collect_billing' );
	if ( ! $billing_fields_enabled ) {
		remove_action( 'walkthecounty_after_cc_fields', 'walkthecounty_default_cc_address_fields' );
	}

	do_action( 'walkthecounty_after_cc_fields', $form_id, $args );

	$form = ob_get_clean();

	if ( false !== $echo ) {
		echo $form;
	}

	return $form;
}

add_action( 'walkthecounty_stripe_cc_form', 'walkthecounty_stripe_credit_card_form', 10, 3 );

/**
 * Add an errors div per form.
 *
 * @param int   $form_id Donation Form ID.
 * @param array $args    List of Donation Arguments.
 *
 * @access public
 * @since  2.5.0
 *
 * @return void
 */
function walkthecounty_stripe_add_stripe_errors( $form_id, $args ) {
	echo '<div id="walkthecounty-stripe-payment-errors-' . esc_html( $args['id_prefix'] ) . '"></div>';
}

add_action( 'walkthecounty_donation_form_after_cc_form', 'walkthecounty_stripe_add_stripe_errors', 8899, 2 );

/**
 * Add secret source field to apply the source generated on donation submit.
 *
 * @param int   $form_id Donation Form ID.
 * @param array $args    List of arguments.
 *
 * @since 2.5.0
 *
 * @return void
 */
function walkthecounty_stripe_add_secret_payment_method_field( $form_id, $args ) {

	$id_prefix = ! empty( $args['id_prefix'] ) ? $args['id_prefix'] : 0;

	echo sprintf(
		'<input id="walkthecounty-stripe-payment-method-%1$s" type="hidden" name="walkthecounty_stripe_payment_method" value="">',
		esc_html( $id_prefix )
	);

}
add_action( 'walkthecounty_donation_form_top', 'walkthecounty_stripe_add_secret_payment_method_field', 10, 2 );
