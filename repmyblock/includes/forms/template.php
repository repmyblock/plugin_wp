<?php
/**
 * WalkTheCounty Form Template
 *
 * @package     WalkTheCounty
 * @subpackage  Forms
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Donation Form.
 *
 * @param array $args An array of form arguments.
 *
 * @return string Donation form.
 * @since 1.0
 *
 */
function walkthecounty_get_donation_form( $args = array() ) {

	global $post;
	static $count = 1;

	$args = wp_parse_args( $args, walkthecounty_get_default_form_shortcode_args() );

	// Backward compatibility for `form_id` function param.
	// If are calling this function directly with `form_id` the use `id` instead.
	$args['id'] = ! empty( $args['form_id'] ) ? absint( $args['form_id'] ) : $args['id'];

	// If `id` does not set then maybe we are single donation form page, so lets render form.
	if ( empty( $args['id'] ) && is_object( $post ) && $post->ID ) {
		$args['id'] = $post->ID;
	}

	// set `form_id` for backward compatibility because many filter and function  using it.
	$args['form_id'] = $args['id'];

	/**
	 * Fire the filter
	 * Note: we will deprecated this filter soon. Use walkthecounty_get_default_form_shortcode_args instead
	 *
	 * @deprecated 2.4.1
	 */
	$args = apply_filters( 'walkthecounty_form_args_defaults', $args );

	$form = new WalkTheCounty_Donate_Form( $args['id'] );

	// Bail out, if no form ID.
	if ( empty( $form->ID ) ) {
		return false;
	}

	$args['id_prefix'] = "{$form->ID}-{$count}";
	$payment_mode      = walkthecounty_get_chosen_gateway( $form->ID );

	$form_action = add_query_arg(
		apply_filters(
			'walkthecounty_form_action_args', array(
				'payment-mode' => $payment_mode,
			)
		),
		walkthecounty_get_current_page_url()
	);

	// Sanity Check: Donation form not published or user doesn't have permission to view drafts.
	if (
		( 'publish' !== $form->post_status && ! current_user_can( 'edit_walkthecounty_forms', $form->ID ) )
		|| ( 'trash' === $form->post_status )
	) {
		return false;
	}

	// Get the form wrap CSS classes.
	$form_wrap_classes = $form->get_form_wrap_classes( $args );

	// Get the <form> tag wrap CSS classes.
	$form_classes = $form->get_form_classes( $args );

	ob_start();

	/**
	 * Fires while outputting donation form, before the form wrapper div.
	 *
	 * @param int   WalkTheCounty_Donate_Form::ID The form ID.
	 * @param array $args An array of form arguments.
	 *
	 * @since 1.0
	 *
	 */
	do_action( 'walkthecounty_pre_form_output', $form->ID, $args, $form );

	?>
	<div id="walkthecounty-form-<?php echo $form->ID; ?>-wrap" class="<?php echo $form_wrap_classes; ?>">
		<?php
		if ( $form->is_close_donation_form() ) {

			$form_title = ! is_singular( 'walkthecounty_forms' ) ? apply_filters( 'walkthecounty_form_title', '<h2 class="walkthecounty-form-title">' . get_the_title( $form->ID ) . '</h2>' ) : '';

			// Get Goal thank you message.
			$goal_achieved_message = get_post_meta( $form->ID, '_walkthecounty_form_goal_achieved_message', true );
			$goal_achieved_message = ! empty( $goal_achieved_message ) ? $form_title . apply_filters( 'the_content', $goal_achieved_message ) : '';

			// Print thank you message.
			echo apply_filters( 'walkthecounty_goal_closed_output', $goal_achieved_message, $form->ID, $form );

		} else {
			/**
			 * Show form title:
			 * 1. if admin set form display_style to button or modal
			 */
			$form_title = apply_filters( 'walkthecounty_form_title', '<h2 class="walkthecounty-form-title">' . get_the_title( $form->ID ) . '</h2>' );

			if ( ! doing_action( 'walkthecounty_single_form_summary' ) && true === $args['show_title'] ) {
				echo $form_title;
			}

			/**
			 * Fires while outputting donation form, before the form.
			 *
			 * @param int              WalkTheCounty_Donate_Form::ID The form ID.
			 * @param array            $args An array of form arguments.
			 * @param WalkTheCounty_Donate_Form $form Form object.
			 *
			 * @since 1.0
			 *
			 */
			do_action( 'walkthecounty_pre_form', $form->ID, $args, $form );

			// Set form html tags.
			$form_html_tags = array(
				'id'      => "walkthecounty-form-{$args['id_prefix']}",
				'class'   => $form_classes,
				'action'  => esc_url_raw( $form_action ),
				'data-id' => $args['id_prefix'],
			);

			/**
			 * Filter the form html tags.
			 *
			 * @param array            $form_html_tags Array of form html tags.
			 * @param WalkTheCounty_Donate_Form $form           Form object.
			 *
			 * @since 1.8.17
			 *
			 */
			$form_html_tags = apply_filters( 'walkthecounty_form_html_tags', (array) $form_html_tags, $form );
			?>
			<form <?php echo walkthecounty_get_attribute_str( $form_html_tags ); ?> method="post">
				<!-- The following field is for robots only, invisible to humans: -->
				<span class="walkthecounty-hidden" style="display: none !important;">
					<label for="walkthecounty-form-honeypot-<?php echo $form->ID; ?>"></label>
					<input id="walkthecounty-form-honeypot-<?php echo $form->ID; ?>" type="text" name="walkthecounty-honeypot"
					       class="walkthecounty-honeypot walkthecounty-hidden"/>
				</span>

				<?php
				/**
				 * Fires while outputting donation form, before all other fields.
				 *
				 * @param int              WalkTheCounty_Donate_Form::ID The form ID.
				 * @param array            $args An array of form arguments.
				 * @param WalkTheCounty_Donate_Form $form Form object.
				 *
				 * @since 1.0
				 *
				 */
				do_action( 'walkthecounty_donation_form_top', $form->ID, $args, $form );

				/**
				 * Fires while outputting donation form, for payment gateway fields.
				 *
				 * @param int              WalkTheCounty_Donate_Form::ID The form ID.
				 * @param array            $args An array of form arguments.
				 * @param WalkTheCounty_Donate_Form $form Form object.
				 *
				 * @since 1.7
				 *
				 */
				do_action( 'walkthecounty_payment_mode_select', $form->ID, $args, $form );

				/**
				 * Fires while outputting donation form, after all other fields.
				 *
				 * @param int              WalkTheCounty_Donate_Form::ID The form ID.
				 * @param array            $args An array of form arguments.
				 * @param WalkTheCounty_Donate_Form $form Form object.
				 *
				 * @since 1.0
				 *
				 */
				do_action( 'walkthecounty_donation_form_bottom', $form->ID, $args, $form );

				?>
			</form>

			<?php
			/**
			 * Fires while outputting donation form, after the form.
			 *
			 * @param int              WalkTheCounty_Donate_Form::ID The form ID.
			 * @param array            $args An array of form arguments.
			 * @param WalkTheCounty_Donate_Form $form Form object.
			 *
			 * @since 1.0
			 *
			 */
			do_action( 'walkthecounty_post_form', $form->ID, $args, $form );

		}
		?>

	</div><!--end #walkthecounty-form-<?php echo absint( $form->ID ); ?>-->
	<?php

	/**
	 * Fires while outputting donation form, after the form wrapper div.
	 *
	 * @param int   WalkTheCounty_Donate_Form::ID The form ID.
	 * @param array $args An array of form arguments.
	 *
	 * @since 1.0
	 *
	 */
	do_action( 'walkthecounty_post_form_output', $form->ID, $args );

	$final_output = ob_get_clean();
	$count ++;

	echo apply_filters( 'walkthecounty_donate_form', $final_output, $args );
}

/**
 * WalkTheCounty Show Donation Form.
 *
 * Renders the Donation Form, hooks are provided to add to the checkout form.
 * The default Donation Form rendered displays a list of the enabled payment
 * gateways, a user registration form (if enable) and a credit card info form
 * if credit cards are enabled.
 *
 * @param int $form_id The form ID.
 *
 * @return string
 * @since  1.0
 *
 */
function walkthecounty_show_purchase_form( $form_id, $args ) {

	$payment_mode = walkthecounty_get_chosen_gateway( $form_id );

	if ( ! isset( $form_id ) && isset( $_POST['walkthecounty_form_id'] ) ) {
		$form_id = $_POST['walkthecounty_form_id'];
	}

	/**
	 * Fire before donation form render.
	 *
	 * @since 1.7
	 */
	do_action( 'walkthecounty_payment_fields_top', $form_id );

	if ( walkthecounty_can_checkout() && isset( $form_id ) ) {

		/**
		 * Fires while displaying donation form, before registration login.
		 *
		 * @since 1.7
		 */
		do_action( 'walkthecounty_donation_form_before_register_login', $form_id, $args );

		/**
		 * Fire when register/login form fields render.
		 *
		 * @since 1.7
		 */
		do_action( 'walkthecounty_donation_form_register_login_fields', $form_id, $args );

		/**
		 * Fire when credit card form fields render.
		 *
		 * @since 1.7
		 */
		do_action( 'walkthecounty_donation_form_before_cc_form', $form_id, $args );

		// Load the credit card form and allow gateways to load their own if they wish.
		if ( has_action( 'walkthecounty_' . $payment_mode . '_cc_form' ) ) {
			/**
			 * Fires while displaying donation form, credit card form fields for a walkthecountyn gateway.
			 *
			 * @param int $form_id The form ID.
			 *
			 * @since 1.0
			 *
			 */
			do_action( "walkthecounty_{$payment_mode}_cc_form", $form_id, $args );
		} else {
			/**
			 * Fires while displaying donation form, credit card form fields.
			 *
			 * @param int $form_id The form ID.
			 *
			 * @since 1.0
			 *
			 */
			do_action( 'walkthecounty_cc_form', $form_id, $args );
		}

		/**
		 * Fire after credit card form fields render.
		 *
		 * @since 1.7
		 */
		do_action( 'walkthecounty_donation_form_after_cc_form', $form_id, $args );

	} else {
		/**
		 * Fire if user can not donate.
		 *
		 * @since 1.7
		 */
		do_action( 'walkthecounty_donation_form_no_access', $form_id );

	}

	/**
	 * Fire after donation form rendered.
	 *
	 * @since 1.7
	 */
	do_action( 'walkthecounty_payment_fields_bottom', $form_id, $args );
}

add_action( 'walkthecounty_donation_form', 'walkthecounty_show_purchase_form', 10, 2 );

/**
 * WalkTheCounty Show Login/Register Form Fields.
 *
 * @param int $form_id The form ID.
 *
 * @return void
 * @since  1.4.1
 *
 */
function walkthecounty_show_register_login_fields( $form_id ) {

	$show_register_form = walkthecounty_show_login_register_option( $form_id );

	if ( ( $show_register_form === 'registration' || ( $show_register_form === 'both' && ! isset( $_GET['login'] ) ) ) && ! is_user_logged_in() ) :
		?>
		<div id="walkthecounty-checkout-login-register-<?php echo $form_id; ?>">
			<?php
			/**
			 * Fire if user registration form render.
			 *
			 * @since 1.7
			 */
			do_action( 'walkthecounty_donation_form_register_fields', $form_id );
			?>
		</div>
	<?php
	elseif ( ( $show_register_form === 'login' || ( $show_register_form === 'both' && isset( $_GET['login'] ) ) ) && ! is_user_logged_in() ) :
		?>
		<div id="walkthecounty-checkout-login-register-<?php echo $form_id; ?>">
			<?php
			/**
			 * Fire if user login form render.
			 *
			 * @since 1.7
			 */
			do_action( 'walkthecounty_donation_form_login_fields', $form_id );
			?>
		</div>
	<?php
	endif;

	if ( ( ! isset( $_GET['login'] ) && is_user_logged_in() ) || ! isset( $show_register_form ) || 'none' === $show_register_form || 'login' === $show_register_form ) {
		/**
		 * Fire when user info render.
		 *
		 * @since 1.7
		 */
		do_action( 'walkthecounty_donation_form_after_user_info', $form_id );
	}
}

add_action( 'walkthecounty_donation_form_register_login_fields', 'walkthecounty_show_register_login_fields' );

/**
 * Donation Amount Field.
 *
 * Outputs the donation amount field that appears at the top of the donation forms. If the user has custom amount
 * enabled the field will output as a customizable input.
 *
 * @param int   $form_id The form ID.
 * @param array $args    An array of form arguments.
 *
 * @return void
 * @since  1.0
 *
 */
function walkthecounty_output_donation_amount_top( $form_id = 0, $args = array() ) {

	$walkthecounty_options        = walkthecounty_get_settings();
	$variable_pricing    = walkthecounty_has_variable_prices( $form_id );
	$allow_custom_amount = walkthecounty_get_meta( $form_id, '_walkthecounty_custom_amount', true );
	$currency_position   = isset( $walkthecounty_options['currency_position'] ) ? $walkthecounty_options['currency_position'] : 'before';
	$symbol              = walkthecounty_currency_symbol( walkthecounty_get_currency( $form_id, $args ) );
	$currency_output     = '<span class="walkthecounty-currency-symbol walkthecounty-currency-position-' . $currency_position . '">' . $symbol . '</span>';
	$default_amount      = walkthecounty_format_amount(
		walkthecounty_get_default_form_amount( $form_id ), array(
			'sanitize' => false,
			'currency' => walkthecounty_get_currency( $form_id ),
		)
	);
	$custom_amount_text  = walkthecounty_get_meta( $form_id, '_walkthecounty_custom_amount_text', true );

	/**
	 * Fires while displaying donation form, before donation level fields.
	 *
	 * @param int   $form_id The form ID.
	 * @param array $args    An array of form arguments.
	 *
	 * @since 1.0
	 *
	 */
	do_action( 'walkthecounty_before_donation_levels', $form_id, $args );

	// Set Price, No Custom Amount Allowed means hidden price field.
	if ( ! walkthecounty_is_setting_enabled( $allow_custom_amount ) ) {
		?>
		<label class="walkthecounty-hidden" for="walkthecounty-amount"><?php esc_html_e( 'Donation Amount:', 'walkthecounty' ); ?></label>
		<input id="walkthecounty-amount" class="walkthecounty-amount-hidden" type="hidden" name="walkthecounty-amount"
		       value="<?php echo $default_amount; ?>" required aria-required="true"/>
		<div class="set-price walkthecounty-donation-amount form-row-wide">
			<?php
			if ( 'before' === $currency_position ) {
				echo $currency_output;
			}
			?>
			<span id="walkthecounty-amount-text" class="walkthecounty-text-input walkthecounty-amount-top"><?php echo $default_amount; ?></span>
			<?php
			if ( 'after' === $currency_position ) {
				echo $currency_output;
			}
			?>
		</div>
		<?php
	} else {
		// Custom Amount Allowed.
		?>
		<div class="walkthecounty-total-wrap">
			<div class="walkthecounty-donation-amount form-row-wide">
				<?php
				if ( 'before' === $currency_position ) {
					echo $currency_output;
				}
				?>
				<label class="walkthecounty-hidden" for="walkthecounty-amount"><?php esc_html_e( 'Donation Amount:', 'walkthecounty' ); ?></label>
				<input class="walkthecounty-text-input walkthecounty-amount-top" id="walkthecounty-amount" name="walkthecounty-amount" type="tel"
				       placeholder="" value="<?php echo $default_amount; ?>" autocomplete="off">
				<?php
				if ( 'after' === $currency_position ) {
					echo $currency_output;
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Fires while displaying donation form, after donation amounf field(s).
	 *
	 * @param int   $form_id The form ID.
	 * @param array $args    An array of form arguments.
	 *
	 * @since 1.0
	 *
	 */
	do_action( 'walkthecounty_after_donation_amount', $form_id, $args );

	// Custom Amount Text
	if ( ! $variable_pricing && walkthecounty_is_setting_enabled( $allow_custom_amount ) && ! empty( $custom_amount_text ) ) {
		?>
		<p class="walkthecounty-custom-amount-text"><?php echo $custom_amount_text; ?></p>
		<?php
	}

	// Output Variable Pricing Levels.
	if ( $variable_pricing ) {
		walkthecounty_output_levels( $form_id );
	}

	/**
	 * Fires while displaying donation form, after donation level fields.
	 *
	 * @param int   $form_id The form ID.
	 * @param array $args    An array of form arguments.
	 *
	 * @since 1.0
	 *
	 */
	do_action( 'walkthecounty_after_donation_levels', $form_id, $args );
}

add_action( 'walkthecounty_donation_form_top', 'walkthecounty_output_donation_amount_top', 10, 2 );

/**
 * Outputs the Donation Levels in various formats such as dropdown, radios, and buttons.
 *
 * @param int $form_id The form ID.
 *
 * @return string Donation levels.
 * @since  1.0
 *
 */
function walkthecounty_output_levels( $form_id ) {

	/**
	 * Filter the variable pricing
	 *
	 * @param array $prices Array of variable prices.
	 * @param int   $form   Form ID.
	 *
	 * @since          1.0
	 * @deprecated     2.2 Use walkthecounty_get_donation_levels filter instead of walkthecounty_form_variable_prices.
	 *                 Check WalkTheCounty_Donate_Form::get_prices().
	 *
	 */
	$prices = apply_filters( 'walkthecounty_form_variable_prices', walkthecounty_get_variable_prices( $form_id ), $form_id );

	$display_style      = walkthecounty_get_meta( $form_id, '_walkthecounty_display_style', true );
	$custom_amount      = walkthecounty_get_meta( $form_id, '_walkthecounty_custom_amount', true );
	$custom_amount_text = walkthecounty_get_meta( $form_id, '_walkthecounty_custom_amount_text', true );

	if ( empty( $custom_amount_text ) ) {
		$custom_amount_text = esc_html__( 'WalkTheCounty a Custom Amount', 'walkthecounty' );
	}

	$output = '';

	switch ( $display_style ) {
		case 'buttons':
			$output .= '<ul id="walkthecounty-donation-level-button-wrap" class="walkthecounty-donation-levels-wrap walkthecounty-list-inline">';

			foreach ( $prices as $price ) {
				$level_text    = apply_filters( 'walkthecounty_form_level_text', ! empty( $price['_walkthecounty_text'] ) ? $price['_walkthecounty_text'] : walkthecounty_currency_filter( walkthecounty_format_amount( $price['_walkthecounty_amount'], array( 'sanitize' => false ) ), array( 'currency_code' => walkthecounty_get_currency( $form_id ) ) ), $form_id, $price );
				$level_classes = apply_filters( 'walkthecounty_form_level_classes', 'walkthecounty-donation-level-btn walkthecounty-btn walkthecounty-btn-level-' . $price['_walkthecounty_id']['level_id'] . ' ' . ( walkthecounty_is_default_level_id( $price ) ? 'walkthecounty-default-level' : '' ), $form_id, $price );

				$formatted_amount = walkthecounty_format_amount(
					$price['_walkthecounty_amount'], array(
						'sanitize' => false,
						'currency' => walkthecounty_get_currency( $form_id ),
					)
				);

				$output .= sprintf(
					'<li><button type="button" data-price-id="%1$s" class="%2$s" value="%3$s" data-default="%4$s">%5$s</button></li>',
					$price['_walkthecounty_id']['level_id'],
					$level_classes,
					$formatted_amount,
					array_key_exists( '_walkthecounty_default', $price ) ? 1 : 0,
					$level_text
				);
			}

			// Custom Amount.
			if (
				walkthecounty_is_setting_enabled( $custom_amount )
				&& ! empty( $custom_amount_text )
			) {

				$output .= sprintf(
					'<li><button type="button" data-price-id="custom" class="walkthecounty-donation-level-btn walkthecounty-btn walkthecounty-btn-level-custom" value="custom">%1$s</button></li>',
					$custom_amount_text
				);
			}

			$output .= '</ul>';

			break;

		case 'radios':
			$output .= '<ul id="walkthecounty-donation-level-radio-list" class="walkthecounty-donation-levels-wrap">';

			foreach ( $prices as $price ) {
				$level_text    = apply_filters( 'walkthecounty_form_level_text', ! empty( $price['_walkthecounty_text'] ) ? $price['_walkthecounty_text'] : walkthecounty_currency_filter( walkthecounty_format_amount( $price['_walkthecounty_amount'], array( 'sanitize' => false ) ), array( 'currency_code' => walkthecounty_get_currency( $form_id ) ) ), $form_id, $price );
				$level_classes = apply_filters( 'walkthecounty_form_level_classes', 'walkthecounty-radio-input walkthecounty-radio-input-level walkthecounty-radio-level-' . $price['_walkthecounty_id']['level_id'] . ( walkthecounty_is_default_level_id( $price ) ? ' walkthecounty-default-level' : '' ), $form_id, $price );

				$formatted_amount = walkthecounty_format_amount(
					$price['_walkthecounty_amount'], array(
						'sanitize' => false,
						'currency' => walkthecounty_get_currency( $form_id ),
					)
				);

				$output .= sprintf(
					'<li><input type="radio" data-price-id="%1$s" class="%2$s" value="%3$s" name="walkthecounty-radio-donation-level" id="walkthecounty-radio-level-%1$s" %4$s data-default="%5$s"><label for="walkthecounty-radio-level-%1$s">%6$s</label></li>',
					$price['_walkthecounty_id']['level_id'],
					$level_classes,
					$formatted_amount,
					( walkthecounty_is_default_level_id( $price ) ? 'checked="checked"' : '' ),
					array_key_exists( '_walkthecounty_default', $price ) ? 1 : 0,
					$level_text
				);
			}

			// Custom Amount.
			if (
				walkthecounty_is_setting_enabled( $custom_amount )
				&& ! empty( $custom_amount_text )
			) {
				$output .= sprintf(
					'<li><input type="radio" data-price-id="custom" class="walkthecounty-radio-input walkthecounty-radio-input-level walkthecounty-radio-level-custom" name="walkthecounty-radio-donation-level" id="walkthecounty-radio-level-custom" value="custom"><label for="walkthecounty-radio-level-custom">%1$s</label></li>',
					$custom_amount_text
				);
			}

			$output .= '</ul>';

			break;

		case 'dropdown':
			$output .= '<label for="walkthecounty-donation-level-select-' . $form_id . '" class="walkthecounty-hidden">' . esc_html__( 'Choose Your Donation Amount', 'walkthecounty' ) . ':</label>';
			$output .= '<select id="walkthecounty-donation-level-select-' . $form_id . '" class="walkthecounty-select walkthecounty-select-level walkthecounty-donation-levels-wrap">';

			// first loop through prices.
			foreach ( $prices as $price ) {
				$level_text    = apply_filters( 'walkthecounty_form_level_text', ! empty( $price['_walkthecounty_text'] ) ? $price['_walkthecounty_text'] : walkthecounty_currency_filter( walkthecounty_format_amount( $price['_walkthecounty_amount'], array( 'sanitize' => false ) ), array( 'currency_code' => walkthecounty_get_currency( $form_id ) ) ), $form_id, $price );
				$level_classes = apply_filters(
					'walkthecounty_form_level_classes', 'walkthecounty-donation-level-' . $price['_walkthecounty_id']['level_id'] . ( walkthecounty_is_default_level_id( $price ) ? ' walkthecounty-default-level' : '' ), $form_id,
					$price
				);

				$formatted_amount = walkthecounty_format_amount(
					$price['_walkthecounty_amount'], array(
						'sanitize' => false,
						'currency' => walkthecounty_get_currency( $form_id ),
					)
				);

				$output .= sprintf(
					'<option data-price-id="%1$s" class="%2$s" value="%3$s" %4$s data-default="%5$s">%6$s</option>',
					$price['_walkthecounty_id']['level_id'],
					$level_classes,
					$formatted_amount,
					( walkthecounty_is_default_level_id( $price ) ? 'selected="selected"' : '' ),
					array_key_exists( '_walkthecounty_default', $price ) ? 1 : 0,
					$level_text
				);
			}

			// Custom Amount.
			if ( walkthecounty_is_setting_enabled( $custom_amount ) && ! empty( $custom_amount_text ) ) {
				$output .= sprintf(
					'<option data-price-id="custom" class="walkthecounty-donation-level-custom" value="custom">%1$s</option>',
					$custom_amount_text
				);
			}

			$output .= '</select>';

			break;
	}

	echo apply_filters( 'walkthecounty_form_level_output', $output, $form_id );
}

/**
 * Display Reveal & Lightbox Button.
 *
 * Outputs a button to reveal form fields.
 *
 * @param int   $form_id The form ID.
 * @param array $args    An array of form arguments.
 *
 * @return string Checkout button.
 * @since  1.0
 *
 */
function walkthecounty_display_checkout_button( $form_id, $args ) {

	$display_option = ( isset( $args['display_style'] ) && ! empty( $args['display_style'] ) )
		? $args['display_style']
		: walkthecounty_get_meta( $form_id, '_walkthecounty_payment_display', true );

	if ( 'button' === $display_option ) {
		$display_option = 'modal';
	} elseif ( $display_option === 'onpage' ) {
		return '';
	}

	$display_label_field = walkthecounty_get_meta( $form_id, '_walkthecounty_reveal_label', true );
	$display_label       = ! empty( $args['continue_button_title'] ) ? $args['continue_button_title'] : ( ! empty( $display_label_field ) ? $display_label_field : esc_html__( 'Donate Now', 'walkthecounty' ) );

	$output = '<button type="button" class="walkthecounty-btn walkthecounty-btn-' . $display_option . '">' . $display_label . '</button>';

	echo apply_filters( 'walkthecounty_display_checkout_button', $output );
}

add_action( 'walkthecounty_after_donation_levels', 'walkthecounty_display_checkout_button', 10, 2 );

/**
 * Shows the User Info fields in the Personal Info box, more fields can be added via the hooks provided.
 *
 * @param int $form_id The form ID.
 *
 * @return void
 * @see    For Pattern Attribute: https://developer.mozilla.org/en-US/docs/Learn/HTML/Forms/Form_validation
 *
 * @since  1.0
 *
 */
function walkthecounty_user_info_fields( $form_id ) {

	// Get user info.
	$walkthecounty_user_info = _walkthecounty_get_prefill_form_field_values( $form_id );
	$title          = ! empty( $walkthecounty_user_info['walkthecounty_title'] ) ? $walkthecounty_user_info['walkthecounty_title'] : '';
	$first_name     = ! empty( $walkthecounty_user_info['walkthecounty_first'] ) ? $walkthecounty_user_info['walkthecounty_first'] : '';
	$last_name      = ! empty( $walkthecounty_user_info['walkthecounty_last'] ) ? $walkthecounty_user_info['walkthecounty_last'] : '';
	$company_name   = ! empty( $walkthecounty_user_info['company_name'] ) ? $walkthecounty_user_info['company_name'] : '';
	$email          = ! empty( $walkthecounty_user_info['walkthecounty_email'] ) ? $walkthecounty_user_info['walkthecounty_email'] : '';
	$title_prefixes = walkthecounty_get_name_title_prefixes( $form_id );

	/**
	 * Fire before user personal information fields
	 *
	 * @since 1.7
	 */
	do_action( 'walkthecounty_donation_form_before_personal_info', $form_id );

	$title_prefix_classes = '';
	if ( walkthecounty_is_name_title_prefix_enabled( $form_id ) ) {
		$title_prefix_classes = 'walkthecounty-title-prefix-wrap';
	}
	?>
	<fieldset id="walkthecounty_checkout_user_info" class="<?php echo esc_html( $title_prefix_classes ); ?>">
		<legend>
			<?php echo esc_html( apply_filters( 'walkthecounty_checkout_personal_info_text', __( 'Personal Info', 'walkthecounty' ) ) ); ?>
		</legend>

		<?php if ( walkthecounty_is_name_title_prefix_enabled( $form_id ) && is_array( $title_prefixes ) && count( $title_prefixes ) > 0 ) { ?>
			<p id="walkthecounty-title-wrap" class="form-row form-row-title form-row-responsive">
				<label class="walkthecounty-label" for="walkthecounty-title">
					<?php esc_attr_e( 'Title', 'walkthecounty' ); ?>
					<?php if ( walkthecounty_field_is_required( 'walkthecounty_title', $form_id ) ) : ?>
						<span class="walkthecounty-required-indicator">*</span>
					<?php endif ?>
					<?php echo WalkTheCounty()->tooltips->render_help( __( 'Title is used to personalize your donation record..', 'walkthecounty' ) ); ?>
				</label>
				<select
					class="walkthecounty-input required"
					type="text"
					name="walkthecounty_title"
					id="walkthecounty-title"
					<?php echo( walkthecounty_field_is_required( 'walkthecounty_title', $form_id ) ? ' required aria-required="true" ' : '' ); ?>
				>
					<?php foreach ( $title_prefixes as $key => $value ) { ?>
						<option
							value="<?php echo esc_html( $value ); ?>" <?php selected( $value, $title, true ); ?>><?php echo esc_html( $value ); ?></option>
					<?php } ?>
				</select>
			</p>
		<?php } ?>

		<p id="walkthecounty-first-name-wrap" class="form-row form-row-first form-row-responsive">
			<label class="walkthecounty-label" for="walkthecounty-first">
				<?php esc_attr_e( 'First Name', 'walkthecounty' ); ?>
				<?php if ( walkthecounty_field_is_required( 'walkthecounty_first', $form_id ) ) : ?>
					<span class="walkthecounty-required-indicator">*</span>
				<?php endif ?>
				<?php echo WalkTheCounty()->tooltips->render_help( __( 'First Name is used to personalize your donation record.', 'walkthecounty' ) ); ?>
			</label>
			<input
				class="walkthecounty-input required"
				type="text"
				name="walkthecounty_first"
				autocomplete="walkthecountyn-name"
				placeholder="<?php esc_attr_e( 'First Name', 'walkthecounty' ); ?>"
				id="walkthecounty-first"
				value="<?php echo esc_html( $first_name ); ?>"
				<?php echo( walkthecounty_field_is_required( 'walkthecounty_first', $form_id ) ? ' required aria-required="true" ' : '' ); ?>
			/>
		</p>

		<p id="walkthecounty-last-name-wrap" class="form-row form-row-last form-row-responsive">
			<label class="walkthecounty-label" for="walkthecounty-last">
				<?php esc_attr_e( 'Last Name', 'walkthecounty' ); ?>
				<?php if ( walkthecounty_field_is_required( 'walkthecounty_last', $form_id ) ) : ?>
					<span class="walkthecounty-required-indicator">*</span>
				<?php endif ?>
				<?php echo WalkTheCounty()->tooltips->render_help( __( 'Last Name is used to personalize your donation record.', 'walkthecounty' ) ); ?>
			</label>

			<input
				class="walkthecounty-input<?php echo( walkthecounty_field_is_required( 'walkthecounty_last', $form_id ) ? ' required' : '' ); ?>"
				type="text"
				name="walkthecounty_last"
				autocomplete="family-name"
				id="walkthecounty-last"
				placeholder="<?php esc_attr_e( 'Last Name', 'walkthecounty' ); ?>"
				value="<?php echo esc_html( $last_name ); ?>"
				<?php echo( walkthecounty_field_is_required( 'walkthecounty_last', $form_id ) ? ' required aria-required="true" ' : '' ); ?>
			/>
		</p>

		<?php if ( walkthecounty_is_company_field_enabled( $form_id ) ) : ?>
			<?php $walkthecounty_company = walkthecounty_field_is_required( 'walkthecounty_company_name', $form_id ); ?>
			<p id="walkthecounty-company-wrap" class="form-row form-row-wide">
				<label class="walkthecounty-label" for="walkthecounty-company">
					<?php esc_attr_e( 'Company Name', 'walkthecounty' ); ?>
					<?php if ( $walkthecounty_company ) : ?>
						<span class="walkthecounty-required-indicator">*</span>
					<?php endif; ?>
					<?php echo WalkTheCounty()->tooltips->render_help( __( 'Donate on behalf of Company', 'walkthecounty' ) ); ?>
				</label>
				<input
					class="walkthecounty-input<?php echo( $walkthecounty_company ? ' required' : '' ); ?>"
					type="text"
					name="walkthecounty_company_name"
					placeholder="<?php esc_attr_e( 'Company Name', 'walkthecounty' ); ?>"
					id="walkthecounty-company"
					value="<?php echo esc_html( $company_name ); ?>"
					<?php echo( $walkthecounty_company ? ' required aria-required="true" ' : '' ); ?>
				/>
			</p>
		<?php endif ?>

		<?php
		/**
		 * Fire before user email field
		 *
		 * @since 1.7
		 */
		do_action( 'walkthecounty_donation_form_before_email', $form_id );
		?>
		<p id="walkthecounty-email-wrap" class="form-row form-row-wide">
			<label class="walkthecounty-label" for="walkthecounty-email">
				<?php esc_attr_e( 'Email Address', 'walkthecounty' ); ?>
				<?php if ( walkthecounty_field_is_required( 'walkthecounty_email', $form_id ) ) { ?>
					<span class="walkthecounty-required-indicator">*</span>
				<?php } ?>
				<?php echo WalkTheCounty()->tooltips->render_help( __( 'We will send the donation receipt to this address.', 'walkthecounty' ) ); ?>
			</label>
			<input
				class="walkthecounty-input required"
				type="email"
				name="walkthecounty_email"
				autocomplete="email"
				placeholder="<?php esc_attr_e( 'Email Address', 'walkthecounty' ); ?>"
				id="walkthecounty-email"
				value="<?php echo esc_html( $email ); ?>"
				<?php echo( walkthecounty_field_is_required( 'walkthecounty_email', $form_id ) ? ' required aria-required="true" ' : '' ); ?>
			/>

		</p>

		<?php if ( walkthecounty_is_anonymous_donation_field_enabled( $form_id ) ) : ?>
			<?php $is_anonymous_donation = isset( $_POST['walkthecounty_anonymous_donation'] ) ? absint( $_POST['walkthecounty_anonymous_donation'] ) : 0; ?>
			<p id="walkthecounty-anonymous-donation-wrap" class="form-row form-row-wide">
				<label class="walkthecounty-label" for="walkthecounty-anonymous-donation">
					<input
						type="checkbox"
						class="walkthecounty-input<?php echo( walkthecounty_field_is_required( 'walkthecounty_anonymous_donation', $form_id ) ? ' required' : '' ); ?>"
						name="walkthecounty_anonymous_donation"
						id="walkthecounty-anonymous-donation"
						value="1"
						<?php echo( walkthecounty_field_is_required( 'walkthecounty_anonymous_donation', $form_id ) ? ' required aria-required="true" ' : '' ); ?>
						<?php checked( 1, $is_anonymous_donation ); ?>
					>
					<?php
					/**
					 * Filters the checkbox label.
					 *
					 * @since 2.4.1
					 */
					echo apply_filters( 'walkthecounty_anonymous_donation_checkbox_label', __( 'Make this an anonymous donation.', 'walkthecounty' ), $form_id );

					if ( walkthecounty_field_is_required( 'walkthecounty_comment', $form_id ) ) {
						?>
						<span class="walkthecounty-required-indicator">*</span>
					<?php } ?>
					<?php
					// Conditional tooltip text when comments enabled:
					// https://github.com/impress-org/walkthecounty/issues/3911
					$anonymous_donation_tooltip = walkthecounty_is_donor_comment_field_enabled( $form_id ) ? esc_html__( 'Would you like to prevent your name, image, and comment from being displayed publicly?', 'walkthecounty' ) : esc_html__( 'Would you like to prevent your name and image from being displayed publicly?', 'walkthecounty' );

					echo WalkTheCounty()->tooltips->render_help( $anonymous_donation_tooltip );
					?>

				</label>
			</p>
		<?php endif; ?>

		<?php if ( walkthecounty_is_donor_comment_field_enabled( $form_id ) ) : ?>
			<p id="walkthecounty-comment-wrap" class="form-row form-row-wide">
				<label class="walkthecounty-label" for="walkthecounty-comment">
					<?php _e( 'Comment', 'walkthecounty' ); ?>
					<?php if ( walkthecounty_field_is_required( 'walkthecounty_comment', $form_id ) ) { ?>
						<span class="walkthecounty-required-indicator">*</span>
					<?php } ?>
					<?php echo WalkTheCounty()->tooltips->render_help( __( 'Would you like to add a comment to this donation?', 'walkthecounty' ) ); ?>
				</label>

				<textarea
					class="walkthecounty-input<?php echo( walkthecounty_field_is_required( 'walkthecounty_comment', $form_id ) ? ' required' : '' ); ?>"
					name="walkthecounty_comment"
					placeholder="<?php _e( 'Leave a comment', 'walkthecounty' ); ?>"
					id="walkthecounty-comment"
					<?php echo( walkthecounty_field_is_required( 'walkthecounty_comment', $form_id ) ? ' required aria-required="true" ' : '' ); ?>
				><?php echo isset( $_POST['walkthecounty_comment'] ) ? walkthecounty_clean( $_POST['walkthecounty_comment'] ) : ''; ?></textarea>

			</p>
		<?php endif; ?>
		<?php
		/**
		 * Fire after user email field
		 *
		 * @since 1.7
		 */
		do_action( 'walkthecounty_donation_form_after_email', $form_id );

		/**
		 * Fire after personal email field
		 *
		 * @since 1.7
		 */
		do_action( 'walkthecounty_donation_form_user_info', $form_id );
		?>
	</fieldset>
	<?php
	/**
	 * Fire after user personal information fields
	 *
	 * @since 1.7
	 */
	do_action( 'walkthecounty_donation_form_after_personal_info', $form_id );
}

add_action( 'walkthecounty_donation_form_after_user_info', 'walkthecounty_user_info_fields' );
add_action( 'walkthecounty_register_fields_before', 'walkthecounty_user_info_fields' );

/**
 * Renders the credit card info form.
 *
 * @param int $form_id The form ID.
 *
 * @return void
 * @since  1.0
 *
 */
function walkthecounty_get_cc_form( $form_id ) {

	ob_start();

	/**
	 * Fires while rendering credit card info form, before the fields.
	 *
	 * @param int $form_id The form ID.
	 *
	 * @since 1.0
	 *
	 */
	do_action( 'walkthecounty_before_cc_fields', $form_id );
	?>
	<fieldset id="walkthecounty_cc_fields-<?php echo $form_id; ?>" class="walkthecounty-do-validate">
		<legend><?php echo apply_filters( 'walkthecounty_credit_card_fieldset_heading', esc_html__( 'Credit Card Info', 'walkthecounty' ) ); ?></legend>
		<?php if ( is_ssl() ) : ?>
			<div id="walkthecounty_secure_site_wrapper-<?php echo $form_id; ?>">
				<span class="walkthecounty-icon padlock"></span>
				<span><?php _e( 'This is a secure SSL encrypted payment.', 'walkthecounty' ); ?></span>
			</div>
		<?php endif; ?>
		<p id="walkthecounty-card-number-wrap-<?php echo $form_id; ?>" class="form-row form-row-two-thirds form-row-responsive">
			<label for="card_number-<?php echo $form_id; ?>" class="walkthecounty-label">
				<?php _e( 'Card Number', 'walkthecounty' ); ?>
				<span class="walkthecounty-required-indicator">*</span>
				<?php echo WalkTheCounty()->tooltips->render_help( __( 'The (typically) 16 digits on the front of your credit card.', 'walkthecounty' ) ); ?>
				<span class="card-type"></span>
			</label>

			<input type="tel" autocomplete="off" name="card_number" id="card_number-<?php echo $form_id; ?>"
			       class="card-number walkthecounty-input required" placeholder="<?php _e( 'Card number', 'walkthecounty' ); ?>"
			       required aria-required="true"/>
		</p>

		<p id="walkthecounty-card-cvc-wrap-<?php echo $form_id; ?>" class="form-row form-row-one-third form-row-responsive">
			<label for="card_cvc-<?php echo $form_id; ?>" class="walkthecounty-label">
				<?php _e( 'CVC', 'walkthecounty' ); ?>
				<span class="walkthecounty-required-indicator">*</span>
				<?php echo WalkTheCounty()->tooltips->render_help( __( 'The 3 digit (back) or 4 digit (front) value on your card.', 'walkthecounty' ) ); ?>
			</label>

			<input type="tel" size="4" autocomplete="off" name="card_cvc" id="card_cvc-<?php echo $form_id; ?>"
			       class="card-cvc walkthecounty-input required" placeholder="<?php _e( 'Security code', 'walkthecounty' ); ?>"
			       required aria-required="true"/>
		</p>

		<p id="walkthecounty-card-name-wrap-<?php echo $form_id; ?>" class="form-row form-row-two-thirds form-row-responsive">
			<label for="card_name-<?php echo $form_id; ?>" class="walkthecounty-label">
				<?php _e( 'Cardholder Name', 'walkthecounty' ); ?>
				<span class="walkthecounty-required-indicator">*</span>
				<?php echo WalkTheCounty()->tooltips->render_help( __( 'The name of the credit card account holder.', 'walkthecounty' ) ); ?>
			</label>

			<input type="text" autocomplete="off" name="card_name" id="card_name-<?php echo $form_id; ?>"
			       class="card-name walkthecounty-input required" placeholder="<?php esc_attr_e( 'Cardholder Name', 'walkthecounty' ); ?>"
			       required aria-required="true"/>
		</p>
		<?php
		/**
		 * Fires while rendering credit card info form, before expiration fields.
		 *
		 * @param int $form_id The form ID.
		 *
		 * @since 1.0
		 *
		 */
		do_action( 'walkthecounty_before_cc_expiration' );
		?>
		<p class="card-expiration form-row form-row-one-third form-row-responsive">
			<label for="card_expiry-<?php echo $form_id; ?>" class="walkthecounty-label">
				<?php _e( 'Expiration', 'walkthecounty' ); ?>
				<span class="walkthecounty-required-indicator">*</span>
				<?php echo WalkTheCounty()->tooltips->render_help( __( 'The date your credit card expires, typically on the front of the card.', 'walkthecounty' ) ); ?>
			</label>

			<input type="hidden" id="card_exp_month-<?php echo $form_id; ?>" name="card_exp_month"
			       class="card-expiry-month"/>
			<input type="hidden" id="card_exp_year-<?php echo $form_id; ?>" name="card_exp_year"
			       class="card-expiry-year"/>

			<input type="tel" autocomplete="off" name="card_expiry" id="card_expiry-<?php echo $form_id; ?>"
			       class="card-expiry walkthecounty-input required" placeholder="<?php esc_attr_e( 'MM / YY', 'walkthecounty' ); ?>"
			       required aria-required="true"/>
		</p>
		<?php
		/**
		 * Fires while rendering credit card info form, after expiration fields.
		 *
		 * @param int $form_id The form ID.
		 *
		 * @since 1.0
		 *
		 */
		do_action( 'walkthecounty_after_cc_expiration', $form_id );
		?>
	</fieldset>
	<?php
	/**
	 * Fires while rendering credit card info form, before the fields.
	 *
	 * @param int $form_id The form ID.
	 *
	 * @since 1.0
	 *
	 */
	do_action( 'walkthecounty_after_cc_fields', $form_id );

	echo ob_get_clean();
}

add_action( 'walkthecounty_cc_form', 'walkthecounty_get_cc_form' );

/**
 * Outputs the default credit card address fields.
 *
 * @param int $form_id The form ID.
 *
 * @return void
 * @since  1.0
 *
 */
function walkthecounty_default_cc_address_fields( $form_id ) {
	// Get user info.
	$walkthecounty_user_info = _walkthecounty_get_prefill_form_field_values( $form_id );

	ob_start();
	?>
	<fieldset id="walkthecounty_cc_address" class="cc-address">
		<legend><?php echo apply_filters( 'walkthecounty_billing_details_fieldset_heading', esc_html__( 'Billing Details', 'walkthecounty' ) ); ?></legend>
		<?php
		/**
		 * Fires while rendering credit card billing form, before address fields.
		 *
		 * @param int $form_id The form ID.
		 *
		 * @since 1.0
		 *
		 */
		do_action( 'walkthecounty_cc_billing_top' );

		// For Country.
		$selected_country = walkthecounty_get_country();
		if ( ! empty( $walkthecounty_user_info['billing_country'] ) && '*' !== $walkthecounty_user_info['billing_country'] ) {
			$selected_country = $walkthecounty_user_info['billing_country'];
		}
		$countries = walkthecounty_get_country_list();

		// For state.
		$selected_state = '';
		if ( $selected_country === walkthecounty_get_country() ) {
			// Get default selected state by admin.
			$selected_state = walkthecounty_get_state();
		}
		// Get the last payment made by user states.
		if ( ! empty( $walkthecounty_user_info['card_state'] ) && '*' !== $walkthecounty_user_info['card_state'] ) {
			$selected_state = $walkthecounty_user_info['card_state'];
		}
		// Get the country code.
		if ( ! empty( $walkthecounty_user_info['billing_country'] ) && '*' !== $walkthecounty_user_info['billing_country'] ) {
			$selected_country = $walkthecounty_user_info['billing_country'];
		}


		// Get the country list that does not require city.
		$city_required = ! array_key_exists( $selected_country, walkthecounty_city_not_required_country_list() );

		?>
		<p id="walkthecounty-card-country-wrap" class="form-row form-row-wide">
			<label for="billing_country" class="walkthecounty-label">
				<?php esc_html_e( 'Country', 'walkthecounty' ); ?>
				<?php if ( walkthecounty_field_is_required( 'billing_country', $form_id ) ) : ?>
					<span class="walkthecounty-required-indicator">*</span>
				<?php endif; ?>
				<span class="walkthecounty-tooltip walkthecounty-icon walkthecounty-icon-question"
				      data-tooltip="<?php esc_attr_e( 'The country for your billing address.', 'walkthecounty' ); ?>"></span>
			</label>

			<select
				name="billing_country"
				autocomplete="country"
				id="billing_country"
				class="billing-country billing_country walkthecounty-select<?php echo( walkthecounty_field_is_required( 'billing_country', $form_id ) ? ' required' : '' ); ?>"
				<?php echo( walkthecounty_field_is_required( 'billing_country', $form_id ) ? ' required aria-required="true" ' : '' ); ?>
			>
				<?php
				foreach ( $countries as $country_code => $country ) {
					echo '<option value="' . esc_attr( $country_code ) . '"' . selected( $country_code, $selected_country, false ) . '>' . $country . '</option>';
				}
				?>
			</select>
		</p>

		<p id="walkthecounty-card-address-wrap" class="form-row form-row-wide">
			<label for="card_address" class="walkthecounty-label">
				<?php _e( 'Address 1', 'walkthecounty' ); ?>
				<?php
				if ( walkthecounty_field_is_required( 'card_address', $form_id ) ) :
					?>
					<span class="walkthecounty-required-indicator">*</span>
				<?php endif; ?>
				<?php echo WalkTheCounty()->tooltips->render_help( __( 'The primary billing address for your credit card.', 'walkthecounty' ) ); ?>
			</label>

			<input
				type="text"
				id="card_address"
				name="card_address"
				autocomplete="address-line1"
				class="card-address walkthecounty-input<?php echo( walkthecounty_field_is_required( 'card_address', $form_id ) ? ' required' : '' ); ?>"
				placeholder="<?php _e( 'Address line 1', 'walkthecounty' ); ?>"
				value="<?php echo isset( $walkthecounty_user_info['card_address'] ) ? $walkthecounty_user_info['card_address'] : ''; ?>"
				<?php echo( walkthecounty_field_is_required( 'card_address', $form_id ) ? '  required aria-required="true" ' : '' ); ?>
			/>
		</p>

		<p id="walkthecounty-card-address-2-wrap" class="form-row form-row-wide">
			<label for="card_address_2" class="walkthecounty-label">
				<?php _e( 'Address 2', 'walkthecounty' ); ?>
				<?php if ( walkthecounty_field_is_required( 'card_address_2', $form_id ) ) : ?>
					<span class="walkthecounty-required-indicator">*</span>
				<?php endif; ?>
				<?php echo WalkTheCounty()->tooltips->render_help( __( '(optional) The suite, apartment number, post office box (etc) associated with your billing address.', 'walkthecounty' ) ); ?>
			</label>

			<input
				type="text"
				id="card_address_2"
				name="card_address_2"
				autocomplete="address-line2"
				class="card-address-2 walkthecounty-input<?php echo( walkthecounty_field_is_required( 'card_address_2', $form_id ) ? ' required' : '' ); ?>"
				placeholder="<?php _e( 'Address line 2', 'walkthecounty' ); ?>"
				value="<?php echo isset( $walkthecounty_user_info['card_address_2'] ) ? $walkthecounty_user_info['card_address_2'] : ''; ?>"
				<?php echo( walkthecounty_field_is_required( 'card_address_2', $form_id ) ? ' required aria-required="true" ' : '' ); ?>
			/>
		</p>

		<p id="walkthecounty-card-city-wrap" class="form-row form-row-wide">
			<label for="card_city" class="walkthecounty-label">
				<?php _e( 'City', 'walkthecounty' ); ?>
				<?php if ( walkthecounty_field_is_required( 'card_city', $form_id ) ) : ?>
					<span class="walkthecounty-required-indicator <?php echo( $city_required ? '' : 'walkthecounty-hidden' ); ?>">*</span>
				<?php endif; ?>
				<?php echo WalkTheCounty()->tooltips->render_help( __( 'The city for your billing address.', 'walkthecounty' ) ); ?>
			</label>
			<input
				type="text"
				id="card_city"
				name="card_city"
				autocomplete="address-level2"
				class="card-city walkthecounty-input<?php echo( walkthecounty_field_is_required( 'card_city', $form_id ) ? ' required' : '' ); ?>"
				placeholder="<?php _e( 'City', 'walkthecounty' ); ?>"
				value="<?php echo( isset( $walkthecounty_user_info['card_city'] ) ? $walkthecounty_user_info['card_city'] : '' ); ?>"
				<?php echo( walkthecounty_field_is_required( 'card_city', $form_id ) && $city_required ? ' required aria-required="true" ' : '' ); ?>
			/>
		</p>

		<?php
		/**
		 * State field logic.
		 */
		$state_label  = __( 'State', 'walkthecounty' );
		$states_label = walkthecounty_get_states_label();
		// Check if $country code exists in the array key for states label.
		if ( array_key_exists( $selected_country, $states_label ) ) {
			$state_label = $states_label[ $selected_country ];
		}
		$states = walkthecounty_get_states( $selected_country );
		// Get the country list that do not have any states.
		$no_states_country = walkthecounty_no_states_country_list();
		// Get the country list that does not require states.
		$states_not_required_country_list = walkthecounty_states_not_required_country_list();
		// Used to determine if state is required.
		$require_state = ! array_key_exists( $selected_country, $no_states_country ) && walkthecounty_field_is_required( 'card_state', $form_id );

		?>
		<p id="walkthecounty-card-state-wrap"
		   class="form-row form-row-first form-row-responsive <?php echo ( ! empty( $selected_country ) && ! $require_state ) ? 'walkthecounty-hidden' : ''; ?> ">
			<label for="card_state" class="walkthecounty-label">
				<span class="state-label-text"><?php echo $state_label; ?></span>
				<span
					class="walkthecounty-required-indicator <?php echo array_key_exists( $selected_country, $states_not_required_country_list ) ? 'walkthecounty-hidden' : ''; ?> ">*</span>
				<span class="walkthecounty-tooltip walkthecounty-icon walkthecounty-icon-question"
				      data-tooltip="<?php esc_attr_e( 'The state, province, or county for your billing address.', 'walkthecounty' ); ?>"></span>
			</label>
			<?php

			if ( ! empty( $states ) ) :
				?>
				<select
					name="card_state"
					autocomplete="address-level1"
					id="card_state"
					class="card_state walkthecounty-select<?php echo $require_state ? ' required' : ''; ?>"
					<?php echo $require_state ? ' required aria-required="true" ' : ''; ?>>
					<?php
					foreach ( $states as $state_code => $state ) {
						echo '<option value="' . $state_code . '"' . selected( $state_code, $selected_state, false ) . '>' . $state . '</option>';
					}
					?>
				</select>
			<?php else : ?>
				<input type="text" size="6" name="card_state" id="card_state" class="card_state walkthecounty-input"
				       placeholder="<?php echo $state_label; ?>" value="<?php echo $selected_state; ?>"
					<?php echo $require_state ? ' required aria-required="true" ' : ''; ?>
				/>
			<?php endif; ?>
		</p>

		<p id="walkthecounty-card-zip-wrap" class="form-row <?php echo $require_state ? 'form-row-last' : ''; ?> form-row-responsive">
			<label for="card_zip" class="walkthecounty-label">
				<?php _e( 'Zip / Postal Code', 'walkthecounty' ); ?>
				<?php if ( walkthecounty_field_is_required( 'card_zip', $form_id ) ) : ?>
					<span class="walkthecounty-required-indicator">*</span>
				<?php endif; ?>
				<?php echo WalkTheCounty()->tooltips->render_help( __( 'The zip or postal code for your billing address.', 'walkthecounty' ) ); ?>
			</label>

			<input
				type="text"
				size="4"
				id="card_zip"
				name="card_zip"
				autocomplete="postal-code"
				class="card-zip walkthecounty-input<?php echo( walkthecounty_field_is_required( 'card_zip', $form_id ) ? ' required' : '' ); ?>"
				placeholder="<?php _e( 'Zip / Postal Code', 'walkthecounty' ); ?>"
				value="<?php echo isset( $walkthecounty_user_info['card_zip'] ) ? $walkthecounty_user_info['card_zip'] : ''; ?>"
				<?php echo( walkthecounty_field_is_required( 'card_zip', $form_id ) ? ' required aria-required="true" ' : '' ); ?>
			/>
		</p>
		<?php
		/**
		 * Fires while rendering credit card billing form, after address fields.
		 *
		 * @param int $form_id The form ID.
		 *
		 * @since 1.0
		 *
		 */
		do_action( 'walkthecounty_cc_billing_bottom' );
		?>
	</fieldset>
	<?php
	echo ob_get_clean();
}

add_action( 'walkthecounty_after_cc_fields', 'walkthecounty_default_cc_address_fields' );


/**
 * Renders the user registration fields. If the user is logged in, a login form is displayed other a registration form
 * is provided for the user to create an account.
 *
 * @param int $form_id The form ID.
 *
 * @return string
 * @since  1.0
 *
 */
function walkthecounty_get_register_fields( $form_id ) {

	global $user_ID;

	if ( is_user_logged_in() ) {
		$user_data = get_userdata( $user_ID );
	}

	$show_register_form = walkthecounty_show_login_register_option( $form_id );

	ob_start();
	?>
	<fieldset id="walkthecounty-register-fields-<?php echo $form_id; ?>">

		<?php
		/**
		 * Fires while rendering user registration form, before registration fields.
		 *
		 * @param int $form_id The form ID.
		 *
		 * @since 1.0
		 *
		 */
		do_action( 'walkthecounty_register_fields_before', $form_id );
		?>

		<fieldset id="walkthecounty-register-account-fields-<?php echo $form_id; ?>">
			<?php
			/**
			 * Fires while rendering user registration form, before account fields.
			 *
			 * @param int $form_id The form ID.
			 *
			 * @since 1.0
			 *
			 */
			do_action( 'walkthecounty_register_account_fields_before', $form_id );

			$class = ( 'registration' === $show_register_form ) ? 'form-row-wide' : 'form-row-first';
			?>
			<div id="walkthecounty-create-account-wrap-<?php echo $form_id; ?>"
			     class="form-row <?php echo esc_attr( $class ); ?> form-row-responsive">
				<label for="walkthecounty-create-account-<?php echo $form_id; ?>">
					<?php
					// Add attributes to checkbox, if Guest Checkout is disabled.
					$is_guest_checkout = walkthecounty_get_meta( $form_id, '_walkthecounty_logged_in_only', true );
					$id                = 'walkthecounty-create-account-' . $form_id;
					if ( ! walkthecounty_is_setting_enabled( $is_guest_checkout ) ) {
						echo WalkTheCounty()->tooltips->render(
							array(
								'tag_content' => sprintf(
									'<input type="checkbox" name="walkthecounty_create_account" value="on" id="%s" class="walkthecounty-input walkthecounty-disabled" checked />',
									$id
								),
								'label'       => __( 'Registration is required to donate.', 'walkthecounty' ),
							)
						);
					} else {
						?>
						<input type="checkbox" name="walkthecounty_create_account" value="on" id="<?php echo $id; ?>"
						       class="walkthecounty-input"/>
						<?php
					}

					_e( 'Create an account', 'walkthecounty' );
					echo WalkTheCounty()->tooltips->render_help( __( 'Create an account on the site to see and manage donation history.', 'walkthecounty' ) );
					echo str_replace(
						'/>',
						'data-time="' . time() . '" data-nonce-life="' . walkthecounty_get_nonce_life() . '"/>',
						walkthecounty_get_nonce_field( "walkthecounty_form_create_user_nonce_{$form_id}", 'walkthecounty-form-user-register-hash', false )
					);
					?>
				</label>
			</div>

			<?php if ( 'both' === $show_register_form ) { ?>
				<div class="walkthecounty-login-account-wrap form-row form-row-last form-row-responsive">
					<p class="walkthecounty-login-message"><?php esc_html_e( 'Already have an account?', 'walkthecounty' ); ?>&nbsp;
						<a href="<?php echo esc_url( add_query_arg( 'login', 1 ) ); ?>" class="walkthecounty-checkout-login"
						   data-action="walkthecounty_checkout_login"><?php esc_html_e( 'Login', 'walkthecounty' ); ?></a>
					</p>
					<p class="walkthecounty-loading-text">
						<span class="walkthecounty-loading-animation"></span>
					</p>
				</div>
			<?php } ?>

			<?php
			/**
			 * Fires while rendering user registration form, after account fields.
			 *
			 * @param int $form_id The form ID.
			 *
			 * @since 1.0
			 *
			 */
			do_action( 'walkthecounty_register_account_fields_after', $form_id );
			?>
		</fieldset>

		<?php
		/**
		 * Fires while rendering user registration form, after registration fields.
		 *
		 * @param int $form_id The form ID.
		 *
		 * @since 1.0
		 *
		 */
		do_action( 'walkthecounty_register_fields_after', $form_id );
		?>

		<input type="hidden" name="walkthecounty-purchase-var" value="needs-to-register"/>

		<?php
		/**
		 * Fire after register or login form render
		 *
		 * @since 1.7
		 */
		do_action( 'walkthecounty_donation_form_user_info', $form_id );
		?>

	</fieldset>
	<?php
	echo ob_get_clean();
}

add_action( 'walkthecounty_donation_form_register_fields', 'walkthecounty_get_register_fields' );

/**
 * Gets the login fields for the login form on the checkout. This function hooks
 * on the walkthecounty_donation_form_login_fields to display the login form if a user already
 * had an account.
 *
 * @param int $form_id The form ID.
 *
 * @return string
 * @since  1.0
 *
 */
function walkthecounty_get_login_fields( $form_id ) {

	$form_id            = isset( $_POST['form_id'] ) ? $_POST['form_id'] : $form_id;
	$show_register_form = walkthecounty_show_login_register_option( $form_id );

	ob_start();
	?>
	<fieldset id="walkthecounty-login-fields-<?php echo $form_id; ?>">
		<legend>
			<?php
			echo apply_filters( 'walkthecounty_account_login_fieldset_heading', __( 'Log In to Your Account', 'walkthecounty' ) );
			if ( ! walkthecounty_logged_in_only( $form_id ) ) {
				echo ' <span class="sub-text">' . __( '(optional)', 'walkthecounty' ) . '</span>';
			}
			?>
		</legend>
		<?php if ( $show_register_form == 'both' ) { ?>
			<p class="walkthecounty-new-account-link">
				<?php _e( 'Don\'t have an account?', 'walkthecounty' ); ?>&nbsp;
				<a href="<?php echo remove_query_arg( 'login' ); ?>" class="walkthecounty-checkout-register-cancel"
				   data-action="walkthecounty_checkout_register">
					<?php
					if ( walkthecounty_logged_in_only( $form_id ) ) {
						_e( 'Register as a part of your donation &raquo;', 'walkthecounty' );
					} else {
						_e( 'Register or donate as a guest &raquo;', 'walkthecounty' );
					}
					?>
				</a>
			</p>
			<p class="walkthecounty-loading-text">
				<span class="walkthecounty-loading-animation"></span>
			</p>
		<?php } ?>
		<?php
		/**
		 * Fires while rendering checkout login form, before the fields.
		 *
		 * @param int $form_id The form ID.
		 *
		 * @since 1.0
		 *
		 */
		do_action( 'walkthecounty_donation_form_login_fields_before', $form_id );
		?>
		<div class="walkthecounty-user-login-fields-container">
			<div id="walkthecounty-user-login-wrap-<?php echo $form_id; ?>" class="form-row form-row-first form-row-responsive">
				<label class="walkthecounty-label" for="walkthecounty-user-login-<?php echo $form_id; ?>">
					<?php _e( 'Username or Email Address', 'walkthecounty' ); ?>
					<?php if ( walkthecounty_logged_in_only( $form_id ) ) { ?>
						<span class="walkthecounty-required-indicator">*</span>
					<?php } ?>
				</label>

				<input class="walkthecounty-input<?php echo ( walkthecounty_logged_in_only( $form_id ) ) ? ' required' : ''; ?>"
				       type="text"
				       name="walkthecounty_user_login" id="walkthecounty-user-login-<?php echo $form_id; ?>" value=""
				       placeholder="<?php _e( 'Your username or email', 'walkthecounty' ); ?>"<?php echo ( walkthecounty_logged_in_only( $form_id ) ) ? ' required aria-required="true" ' : ''; ?>/>
			</div>

			<div id="walkthecounty-user-pass-wrap-<?php echo $form_id; ?>"
			     class="walkthecounty_login_password form-row form-row-last form-row-responsive">
				<label class="walkthecounty-label" for="walkthecounty-user-pass-<?php echo $form_id; ?>">
					<?php _e( 'Password', 'walkthecounty' ); ?>
					<?php if ( walkthecounty_logged_in_only( $form_id ) ) { ?>
						<span class="walkthecounty-required-indicator">*</span>
					<?php } ?>
				</label>
				<input class="walkthecounty-input<?php echo ( walkthecounty_logged_in_only( $form_id ) ) ? ' required' : ''; ?>"
				       type="password" name="walkthecounty_user_pass" id="walkthecounty-user-pass-<?php echo $form_id; ?>"
				       placeholder="<?php _e( 'Your password', 'walkthecounty' ); ?>"<?php echo ( walkthecounty_logged_in_only( $form_id ) ) ? ' required aria-required="true" ' : ''; ?>/>
				<?php if ( walkthecounty_logged_in_only( $form_id ) ) : ?>
					<input type="hidden" name="walkthecounty-purchase-var" value="needs-to-login"/>
				<?php endif; ?>
			</div>

			<div id="walkthecounty-forgot-password-wrap-<?php echo $form_id; ?>" class="walkthecounty_login_forgot_password">
				 <span class="walkthecounty-forgot-password ">
					 <a href="<?php echo wp_lostpassword_url(); ?>"
					    target="_blank"><?php _e( 'Reset Password', 'walkthecounty' ); ?></a>
				 </span>
			</div>
		</div>


		<div id="walkthecounty-user-login-submit-<?php echo $form_id; ?>" class="walkthecounty-clearfix">
			<input type="submit" class="walkthecounty-submit walkthecounty-btn button" name="walkthecounty_login_submit"
			       value="<?php _e( 'Login', 'walkthecounty' ); ?>"/>
			<?php if ( $show_register_form !== 'login' ) { ?>
				<input type="button" data-action="walkthecounty_cancel_login"
				       class="walkthecounty-cancel-login walkthecounty-checkout-register-cancel walkthecounty-btn button" name="walkthecounty_login_cancel"
				       value="<?php _e( 'Cancel', 'walkthecounty' ); ?>"/>
			<?php } ?>
			<span class="walkthecounty-loading-animation"></span>
		</div>
		<?php
		/**
		 * Fires while rendering checkout login form, after the fields.
		 *
		 * @param int $form_id The form ID.
		 *
		 * @since 1.0
		 *
		 */
		do_action( 'walkthecounty_donation_form_login_fields_after', $form_id );
		?>
	</fieldset><!--end #walkthecounty-login-fields-->
	<?php
	echo ob_get_clean();
}

add_action( 'walkthecounty_donation_form_login_fields', 'walkthecounty_get_login_fields', 10, 1 );

/**
 * Payment Mode Select.
 *
 * Renders the payment mode form by getting all the enabled payment gateways and
 * outputting them as radio buttons for the user to choose the payment gateway. If
 * a default payment gateway has been chosen from the WalkTheCounty Settings, it will be
 * automatically selected.
 *
 * @param int $form_id The form ID.
 *
 * @return void
 * @since  1.0
 *
 */
function walkthecounty_payment_mode_select( $form_id, $args ) {

	$gateways  = walkthecounty_get_enabled_payment_gateways( $form_id );
	$id_prefix = ! empty( $args['id_prefix'] ) ? $args['id_prefix'] : '';

	/**
	 * Fires while selecting payment gateways, before the fields.
	 *
	 * @param int $form_id The form ID.
	 *
	 * @since 1.7
	 *
	 */
	do_action( 'walkthecounty_payment_mode_top', $form_id );
	?>

	<fieldset id="walkthecounty-payment-mode-select"
		<?php
		if ( count( $gateways ) <= 1 ) {
			echo 'style="display: none;"';
		}
		?>
	>
		<?php
		/**
		 * Fires while selecting payment gateways, before the wrap div.
		 *
		 * @param int $form_id The form ID.
		 *
		 * @since 1.7
		 *
		 */
		do_action( 'walkthecounty_payment_mode_before_gateways_wrap' );
		?>
		<legend
			class="walkthecounty-payment-mode-label"><?php echo apply_filters( 'walkthecounty_checkout_payment_method_text', esc_html__( 'Select Payment Method', 'walkthecounty' ) ); ?>
			<span class="walkthecounty-loading-text"><span
					class="walkthecounty-loading-animation"></span>
			</span>
		</legend>

		<div id="walkthecounty-payment-mode-wrap">
			<?php
			/**
			 * Fires while selecting payment gateways, before the gateways list.
			 *
			 * @since 1.7
			 */
			do_action( 'walkthecounty_payment_mode_before_gateways' )
			?>
			<ul id="walkthecounty-gateway-radio-list">
				<?php
				/**
				 * Loop through the active payment gateways.
				 */
				$selected_gateway              = walkthecounty_get_chosen_gateway( $form_id );
				$walkthecounty_settings                 = walkthecounty_get_settings();
				$gateways_label                = array_key_exists( 'gateways_label', $walkthecounty_settings ) ?
					$walkthecounty_settings['gateways_label'] :
					array();

				foreach ( $gateways as $gateway_id => $gateway ) :
					// Determine the default gateway.
					$checked = checked( $gateway_id, $selected_gateway, false );
					$checked_class             = $checked ? ' class="walkthecounty-gateway-option-selected"' : '';
					$is_payment_method_visible = isset( $gateway['is_visible'] ) ? $gateway['is_visible'] : true;

					if ( true === $is_payment_method_visible ) {
						?>
						<li<?php echo $checked_class; ?>>
							<input type="radio" name="payment-mode" class="walkthecounty-gateway"
							       id="walkthecounty-gateway-<?php echo esc_attr( $gateway_id . '-' . $id_prefix ); ?>"
							       value="<?php echo esc_attr( $gateway_id ); ?>"<?php echo $checked; ?>>

							<?php
							$label = $gateway['checkout_label'];
							if ( ! empty( $gateways_label[ $gateway_id ] ) ) {
								$label = $gateways_label[ $gateway_id ];
							}
							?>
							<label for="walkthecounty-gateway-<?php echo esc_attr( $gateway_id . '-' . $id_prefix ); ?>"
							       class="walkthecounty-gateway-option"
							       id="walkthecounty-gateway-option-<?php echo esc_attr( $gateway_id ); ?>"> <?php echo esc_html( $label ); ?></label>
						</li>
						<?php
					}
				endforeach;
				?>
			</ul>
			<?php
			/**
			 * Fires while selecting payment gateways, before the gateways list.
			 *
			 * @since 1.7
			 */
			do_action( 'walkthecounty_payment_mode_after_gateways' );
			?>
		</div>
		<?php
		/**
		 * Fires while selecting payment gateways, after the wrap div.
		 *
		 * @param int $form_id The form ID.
		 *
		 * @since 1.7
		 *
		 */
		do_action( 'walkthecounty_payment_mode_after_gateways_wrap' );
		?>
	</fieldset>

	<?php
	/**
	 * Fires while selecting payment gateways, after the fields.
	 *
	 * @param int $form_id The form ID.
	 *
	 * @since 1.7
	 *
	 */
	do_action( 'walkthecounty_payment_mode_bottom', $form_id );
	?>

	<div id="walkthecounty_purchase_form_wrap">

		<?php
		/**
		 * Fire after payment field render.
		 *
		 * @since 1.7
		 */
		do_action( 'walkthecounty_donation_form', $form_id, $args );
		?>

	</div>

	<?php
	/**
	 * Fire after donation form render.
	 *
	 * @since 1.7
	 */
	do_action( 'walkthecounty_donation_form_wrap_bottom', $form_id );
}

add_action( 'walkthecounty_payment_mode_select', 'walkthecounty_payment_mode_select', 10, 2 );

/**
 * Renders the Checkout Agree to Terms, this displays a checkbox for users to
 * agree the T&Cs set in the WalkTheCounty Settings. This is only displayed if T&Cs are
 * set in the WalkTheCounty Settings.
 *
 * @param int $form_id The form ID.
 *
 * @return bool
 * @since  1.0
 *
 */
function walkthecounty_terms_agreement( $form_id ) {
	$form_option = walkthecounty_get_meta( $form_id, '_walkthecounty_terms_option', true );

	// Bailout if per form and global term and conditions is not setup.
	if (
		walkthecounty_is_setting_enabled( $form_option, 'global' )
		&& walkthecounty_is_setting_enabled( walkthecounty_get_option( 'terms' ) )
	) {
		$label         = walkthecounty_get_option( 'agree_to_terms_label', esc_html__( 'Agree to Terms?', 'walkthecounty' ) );
		$terms         = $terms = walkthecounty_get_option( 'agreement_text', '' );
		$edit_term_url = admin_url( 'edit.php?post_type=walkthecounty_forms&page=walkthecounty-settings&tab=display&section=term-and-conditions' );

	} elseif ( walkthecounty_is_setting_enabled( $form_option ) ) {
		$label         = ( $label = walkthecounty_get_meta( $form_id, '_walkthecounty_agree_label', true ) ) ? stripslashes( $label ) : esc_html__( 'Agree to Terms?', 'walkthecounty' );
		$terms         = walkthecounty_get_meta( $form_id, '_walkthecounty_agree_text', true );
		$edit_term_url = admin_url( 'post.php?post=' . $form_id . '&action=edit#form_terms_options' );

	} else {
		return false;
	}

	// Bailout: Check if term and conditions text is empty or not.
	if ( empty( $terms ) ) {
		if ( is_user_logged_in() && current_user_can( 'edit_walkthecounty_forms' ) ) {
			echo sprintf( __( 'Please enter valid terms and conditions in <a href="%s">this form\'s settings</a>.', 'walkthecounty' ), $edit_term_url );
		}

		return false;
	}

	/**
	 * Filter the form term content
	 *
	 * @since  2.1.5
	 */
	$terms = apply_filters( 'walkthecounty_the_term_content', wpautop( do_shortcode( $terms ) ), $terms, $form_id );

	?>
	<fieldset id="walkthecounty_terms_agreement">
		<legend><?php echo apply_filters( 'walkthecounty_terms_agreement_text', esc_html__( 'Terms', 'walkthecounty' ) ); ?></legend>
		<div id="walkthecounty_terms" class="walkthecounty_terms-<?php echo $form_id; ?>" style="display:none;">
			<?php
			/**
			 * Fires while rendering terms of agreement, before the fields.
			 *
			 * @since 1.0
			 */
			do_action( 'walkthecounty_before_terms' );

			echo $terms;
			/**
			 * Fires while rendering terms of agreement, after the fields.
			 *
			 * @since 1.0
			 */
			do_action( 'walkthecounty_after_terms' );
			?>
		</div>
		<div id="walkthecounty_show_terms">
			<a href="#" class="walkthecounty_terms_links walkthecounty_terms_links-<?php echo $form_id; ?>" role="button"
			   aria-controls="walkthecounty_terms"><?php esc_html_e( 'Show Terms', 'walkthecounty' ); ?></a>
			<a href="#" class="walkthecounty_terms_links walkthecounty_terms_links-<?php echo $form_id; ?>" role="button"
			   aria-controls="walkthecounty_terms" style="display:none;"><?php esc_html_e( 'Hide Terms', 'walkthecounty' ); ?></a>
		</div>

		<input name="walkthecounty_agree_to_terms" class="required" type="checkbox"
		       id="walkthecounty_agree_to_terms-<?php echo $form_id; ?>" value="1" required aria-required="true"/>
		<label for="walkthecounty_agree_to_terms-<?php echo $form_id; ?>"><?php echo $label; ?></label>

	</fieldset>
	<?php
}

add_action( 'walkthecounty_donation_form_after_cc_form', 'walkthecounty_terms_agreement', 8888, 1 );

/**
 * Checkout Final Total.
 *
 * Shows the final donation total at the bottom of the checkout page.
 *
 * @param int $form_id The form ID.
 *
 * @return void
 * @since  1.0
 *
 */
function walkthecounty_checkout_final_total( $form_id ) {

	$total = isset( $_POST['walkthecounty_total'] ) ?
		apply_filters( 'walkthecounty_donation_total', walkthecounty_maybe_sanitize_amount( $_POST['walkthecounty_total'] ) ) :
		walkthecounty_get_default_form_amount( $form_id );

	// Only proceed if walkthecounty_total available.
	if ( empty( $total ) ) {
		return;
	}
	?>
	<p id="walkthecounty-final-total-wrap" class="form-wrap ">
		<?php
		/**
		 * Fires before the donation total label
		 *
		 * @since 2.0.5
		 */
		do_action( 'walkthecounty_donation_final_total_label_before', $form_id );
		?>
		<span class="walkthecounty-donation-total-label">
			<?php echo apply_filters( 'walkthecounty_donation_total_label', esc_html__( 'Donation Total:', 'walkthecounty' ) ); ?>
		</span>
		<span class="walkthecounty-final-total-amount"
		      data-total="<?php echo walkthecounty_format_amount( $total, array( 'sanitize' => false ) ); ?>">
			<?php
			echo walkthecounty_currency_filter(
				walkthecounty_format_amount(
					$total, array(
						'sanitize' => false,
						'currency' => walkthecounty_get_currency( $form_id ),
					)
				), array( 'currency_code' => walkthecounty_get_currency( $form_id ) )
			);
			?>
		</span>
		<?php
		/**
		 * Fires after the donation final total label
		 *
		 * @since 2.0.5
		 */
		do_action( 'walkthecounty_donation_final_total_label_after', $form_id );
		?>
	</p>
	<?php
}

add_action( 'walkthecounty_donation_form_before_submit', 'walkthecounty_checkout_final_total', 999 );

/**
 * Renders the Checkout Submit section.
 *
 * @param int   $form_id The donation form ID.
 * @param array $args    List of arguments.
 *
 * @return void
 * @since  1.0
 *
 */
function walkthecounty_checkout_submit( $form_id, $args ) {
	?>
	<fieldset id="walkthecounty_purchase_submit" class="walkthecounty-donation-submit">
		<?php
		/**
		 * Fire before donation form submit.
		 *
		 * @since 1.7
		 */
		do_action( 'walkthecounty_donation_form_before_submit', $form_id, $args );

		walkthecounty_checkout_hidden_fields( $form_id );

		echo walkthecounty_get_donation_form_submit_button( $form_id, $args );

		/**
		 * Fire after donation form submit.
		 *
		 * @since 1.7
		 */
		do_action( 'walkthecounty_donation_form_after_submit', $form_id, $args );
		?>
	</fieldset>
	<?php
}

add_action( 'walkthecounty_donation_form_after_cc_form', 'walkthecounty_checkout_submit', 9999, 2 );

/**
 * WalkTheCounty Donation form submit button.
 *
 * @param int   $form_id The form ID.
 * @param array $args
 *
 * @return string
 * @since  1.8.8
 *
 */
function walkthecounty_get_donation_form_submit_button( $form_id, $args = array() ) {

	$display_label_field = walkthecounty_get_meta( $form_id, '_walkthecounty_checkout_label', true );
    $display_label_field = apply_filters( 'walkthecounty_donation_form_submit_button_text', $display_label_field, $form_id, $args );
	$display_label       = ( ! empty( $display_label_field ) ? $display_label_field : esc_html__( 'Donate Now', 'walkthecounty' ) );
	ob_start();
	?>
	<div class="walkthecounty-submit-button-wrap walkthecounty-clearfix">
		<input type="submit" class="walkthecounty-submit walkthecounty-btn" id="walkthecounty-purchase-button" name="walkthecounty-purchase"
		       value="<?php echo $display_label; ?>" data-before-validation-label="<?php echo $display_label; ?>"/>
		<span class="walkthecounty-loading-animation"></span>
	</div>
	<?php
	return apply_filters( 'walkthecounty_donation_form_submit_button', ob_get_clean(), $form_id, $args );
}

/**
 * Show WalkTheCounty Goals.
 *
 * @param int   $form_id The form ID.
 * @param array $args    An array of form arguments.
 *
 * @return mixed
 * @since        1.6   Add template for WalkTheCounty Goals Shortcode.
 *               More info is on https://github.com/impress-org/walkthecounty/issues/411
 *
 * @since        1.0
 */
function walkthecounty_show_goal_progress( $form_id, $args = array() ) {

	ob_start();
	walkthecounty_get_template(
		'shortcode-goal', array(
			'form_id' => $form_id,
			'args'    => $args,
		)
	);

	/**
	 * Filter progress bar output
	 *
	 * @since 2.0
	 */
	echo apply_filters( 'walkthecounty_goal_output', ob_get_clean(), $form_id, $args );

	return true;
}

add_action( 'walkthecounty_pre_form', 'walkthecounty_show_goal_progress', 10, 2 );

/**
 * Show WalkTheCounty Totals Progress.
 *
 * @param int $total      Total amount based on shortcode parameter.
 * @param int $total_goal Total Goal amount passed by Admin.
 *
 * @return mixed
 * @since  2.1
 *
 */
function walkthecounty_show_goal_totals_progress( $total, $total_goal ) {

	// Bail out if total goal is set as an array.
	if ( isset( $total_goal ) && is_array( $total_goal ) ) {
		return false;
	}

	ob_start();
	walkthecounty_get_template(
		'shortcode-totals-progress', array(
			'total'      => $total,
			'total_goal' => $total_goal,
		)
	);

	echo apply_filters( 'walkthecounty_total_progress_output', ob_get_clean() );

	return true;
}

add_action( 'walkthecounty_pre_form', 'walkthecounty_show_goal_totals_progress', 10, 2 );

/**
 * Get form content position.
 *
 * @param  $form_id
 * @param  $args
 *
 * @return mixed|string
 * @since  1.8
 *
 */
function walkthecounty_get_form_content_placement( $form_id, $args ) {
	$show_content = '';

	if ( isset( $args['show_content'] ) && ! empty( $args['show_content'] ) ) {
		// Content positions.
		$content_placement = array(
			'above' => 'walkthecounty_pre_form',
			'below' => 'walkthecounty_post_form',
		);

		// Check if content position already decoded.
		if ( in_array( $args['show_content'], $content_placement ) ) {
			return $args['show_content'];
		}

		$show_content = ( 'none' !== $args['show_content'] ? $content_placement[ $args['show_content'] ] : '' );

	} elseif ( walkthecounty_is_setting_enabled( walkthecounty_get_meta( $form_id, '_walkthecounty_display_content', true ) ) ) {
		$show_content = walkthecounty_get_meta( $form_id, '_walkthecounty_content_placement', true );

	}

	return $show_content;
}

/**
 * Adds Actions to Render Form Content.
 *
 * @param int   $form_id The form ID.
 * @param array $args    An array of form arguments.
 *
 * @return void|bool
 * @since  1.0
 *
 */
function walkthecounty_form_content( $form_id, $args ) {

	$show_content = walkthecounty_get_form_content_placement( $form_id, $args );

	// Bailout.
	if ( empty( $show_content ) ) {
		return false;
	}

	// Add action according to value.
	add_action( $show_content, 'walkthecounty_form_display_content', 10, 2 );
}

add_action( 'walkthecounty_pre_form_output', 'walkthecounty_form_content', 10, 2 );

/**
 * Renders Post Form Content.
 *
 * Displays content for WalkTheCounty forms; fired by action from walkthecounty_form_content.
 *
 * @param int   $form_id The form ID.
 * @param array $args    An array of form arguments.
 *
 * @return void
 * @since  1.0
 *
 */
function walkthecounty_form_display_content( $form_id, $args ) {
	$content      = walkthecounty_get_meta( $form_id, '_walkthecounty_form_content', true );
	$show_content = walkthecounty_get_form_content_placement( $form_id, $args );

	if ( walkthecounty_is_setting_enabled( walkthecounty_get_option( 'the_content_filter' ) ) ) {

		// Do not restore wpautop if we are still parsing blocks.
		$priority = has_filter( 'the_content', '_restore_wpautop_hook' );
		if ( false !== $priority && doing_filter( 'the_content' ) ) {
			remove_filter( 'the_content', '_restore_wpautop_hook', $priority );
		}

		$content = apply_filters( 'the_content', $content );

		// Restore wpautop after done with blocks parsing.
		if ( $priority ) {
			// Run wpautop manually if parsing block
			$content = wpautop( $content );

			add_filter( 'the_content', '_restore_wpautop_hook', $priority );
		}
	} else {
		$content = wpautop( do_shortcode( $content ) );
	}

	$output = sprintf(
		'<div id="walkthecounty-form-content-%s" class="walkthecounty-form-content-wrap %s-content">%s</div>',
		$form_id,
		$show_content,
		$content
	);

	/**
	 * Filter form content html
	 *
	 * @param string $output
	 * @param int    $form_id
	 * @param array  $args
	 *
	 * @since 1.0
	 *
	 */
	echo apply_filters( 'walkthecounty_form_content_output', $output, $form_id, $args );

	// remove action to prevent content output on addition forms on page.
	// @see: https://github.com/impress-org/walkthecounty/issues/634.
	remove_action( $show_content, 'walkthecounty_form_display_content' );
}

/**
 * Renders the hidden Checkout fields.
 *
 * @param int $form_id The form ID.
 *
 * @return void
 * @since 1.0
 *
 */
function walkthecounty_checkout_hidden_fields( $form_id ) {

	/**
	 * Fires while rendering hidden checkout fields, before the fields.
	 *
	 * @param int $form_id The form ID.
	 *
	 * @since 1.0
	 *
	 */
	do_action( 'walkthecounty_hidden_fields_before', $form_id );

	if ( is_user_logged_in() ) {
		?>
		<input type="hidden" name="walkthecounty-user-id" value="<?php echo get_current_user_id(); ?>"/>
	<?php } ?>
	<input type="hidden" name="walkthecounty_action" value="purchase"/>
	<input type="hidden" name="walkthecounty-gateway" value="<?php echo walkthecounty_get_chosen_gateway( $form_id ); ?>"/>
	<?php
	/**
	 * Fires while rendering hidden checkout fields, after the fields.
	 *
	 * @param int $form_id The form ID.
	 *
	 * @since 1.0
	 *
	 */
	do_action( 'walkthecounty_hidden_fields_after', $form_id );

}

/**
 * Filter Success Page Content.
 *
 * Applies filters to the success page content.
 *
 * @param string $content Content before filters.
 *
 * @return string $content Filtered content.
 * @since 1.0
 *
 */
function walkthecounty_filter_success_page_content( $content ) {

	$walkthecounty_options = walkthecounty_get_settings();

	if ( isset( $walkthecounty_options['success_page'] ) && isset( $_GET['payment-confirmation'] ) && is_page( $walkthecounty_options['success_page'] ) ) {
		if ( has_filter( 'walkthecounty_payment_confirm_' . $_GET['payment-confirmation'] ) ) {
			$content = apply_filters( 'walkthecounty_payment_confirm_' . $_GET['payment-confirmation'], $content );
		}
	}

	return $content;
}

add_filter( 'the_content', 'walkthecounty_filter_success_page_content' );

/**
 * Test Mode Frontend Warning.
 *
 * Displays a notice on the frontend for donation forms.
 *
 * @since 1.1
 */
function walkthecounty_test_mode_frontend_warning() {

	if ( walkthecounty_is_test_mode() ) {
		echo '<div class="walkthecounty_error walkthecounty_warning" id="walkthecounty_error_test_mode"><p><strong>' . esc_html__( 'Notice:', 'walkthecounty' ) . '</strong> ' . esc_html__( 'Test mode is enabled. While in test mode no live donations are processed.', 'walkthecounty' ) . '</p></div>';
	}
}

add_action( 'walkthecounty_pre_form', 'walkthecounty_test_mode_frontend_warning', 10 );

/**
 * Members-only Form.
 *
 * If "Disable Guest Donations" and "Display Register / Login" is set to none.
 *
 * @param string $final_output
 * @param array  $args
 *
 * @return string
 * @since  1.4.1
 *
 */
function walkthecounty_members_only_form( $final_output, $args ) {

	$form_id = isset( $args['form_id'] ) ? $args['form_id'] : 0;

	// Sanity Check: Must have form_id & not be logged in.
	if ( empty( $form_id ) || is_user_logged_in() ) {
		return $final_output;
	}

	// Logged in only and Register / Login set to none.
	if ( walkthecounty_logged_in_only( $form_id ) && walkthecounty_show_login_register_option( $form_id ) == 'none' ) {

		$final_output = WalkTheCounty_Notices::print_frontend_notice( esc_html__( 'Please log in in order to complete your donation.', 'walkthecounty' ), false );

		return apply_filters( 'walkthecounty_members_only_output', $final_output, $form_id );

	}

	return $final_output;

}

add_filter( 'walkthecounty_donate_form', 'walkthecounty_members_only_form', 10, 2 );


/**
 * Add donation form hidden fields.
 *
 * @param int              $form_id
 * @param array            $args
 * @param WalkTheCounty_Donate_Form $form
 *
 * @since 1.8.17
 *
 */
function __walkthecounty_form_add_donation_hidden_field( $form_id, $args, $form ) {
	$id_prefix = ! empty( $args['id_prefix'] ) ? $args['id_prefix'] : '';
	?>
	<input type="hidden" name="walkthecounty-form-id-prefix" value="<?php echo $id_prefix; ?>"/>
	<input type="hidden" name="walkthecounty-form-id" value="<?php echo intval( $form_id ); ?>"/>
	<input type="hidden" name="walkthecounty-form-title" value="<?php echo esc_html( $form->post_title ); ?>"/>
	<input type="hidden" name="walkthecounty-current-url" value="<?php echo esc_url( walkthecounty_get_current_page_url() ); ?>"/>
	<input type="hidden" name="walkthecounty-form-url" value="<?php echo esc_url( walkthecounty_get_current_page_url() ); ?>"/>
	<?php
	// Get the custom option amount.
	$custom_amount = walkthecounty_get_meta( $form_id, '_walkthecounty_custom_amount', true );

	// If custom amount enabled.
	if ( walkthecounty_is_setting_enabled( $custom_amount ) ) {
		?>
		<input type="hidden" name="walkthecounty-form-minimum"
		       value="<?php echo walkthecounty_maybe_sanitize_amount( walkthecounty_get_form_minimum_price( $form_id ) ); ?>"/>
		<input type="hidden" name="walkthecounty-form-maximum"
		       value="<?php echo walkthecounty_maybe_sanitize_amount( walkthecounty_get_form_maximum_price( $form_id ) ); ?>"/>
		<?php
	}

	$data_attr = sprintf(
		'data-time="%1$s" data-nonce-life="%2$s" data-donor-session="%3$s"',
		time(),
		walkthecounty_get_nonce_life(),
		absint( WalkTheCounty()->session->has_session() )
	);

	// WP nonce field.
	echo str_replace(
		'/>',
		"{$data_attr}/>",
		walkthecounty_get_nonce_field( "walkthecounty_donation_form_nonce_{$form_id}", 'walkthecounty-form-hash', false )
	);

	// Price ID hidden field for variable (multi-level) donation forms.
	if ( walkthecounty_has_variable_prices( $form_id ) ) {
		// Get the default price ID.
		$default_price = walkthecounty_form_get_default_level( $form_id );
		$price_id      = isset( $default_price['_walkthecounty_id']['level_id'] ) ? $default_price['_walkthecounty_id']['level_id'] : 0;

		echo sprintf(
			'<input type="hidden" name="walkthecounty-price-id" value="%s"/>',
			$price_id
		);
	}
}

add_action( 'walkthecounty_donation_form_top', '__walkthecounty_form_add_donation_hidden_field', 0, 3 );

/**
 * Add currency settings on donation form.
 *
 * @param array            $form_html_tags
 * @param WalkTheCounty_Donate_Form $form
 *
 * @return array
 * @since 1.8.17
 *
 */
function __walkthecounty_form_add_currency_settings( $form_html_tags, $form ) {
	$form_currency     = walkthecounty_get_currency( $form->ID );
	$currency_settings = walkthecounty_get_currency_formatting_settings( $form_currency );

	// Check if currency exist.
	if ( empty( $currency_settings ) ) {
		return $form_html_tags;
	}

	$form_html_tags['data-currency_symbol'] = walkthecounty_currency_symbol( $form_currency );
	$form_html_tags['data-currency_code']   = $form_currency;

	if ( ! empty( $currency_settings ) ) {
		foreach ( $currency_settings as $key => $value ) {
			$form_html_tags["data-{$key}"] = $value;
		}
	}

	return $form_html_tags;
}

add_filter( 'walkthecounty_form_html_tags', '__walkthecounty_form_add_currency_settings', 0, 2 );

/**
 * Adds classes to progress bar container.
 *
 * @param string $class_goal
 *
 * @return string
 * @since 2.1
 *
 */
function add_walkthecounty_goal_progress_class( $class_goal ) {
	$class_goal = 'progress progress-striped active';

	return $class_goal;
}

/**
 * Adds classes to progress bar span tag.
 *
 * @param string $class_bar
 *
 * @return string
 * @since 2.1
 *
 */
function add_walkthecounty_goal_progress_bar_class( $class_bar ) {
	$class_bar = 'bar';

	return $class_bar;
}

/**
 * Add a class to the form wrap on the grid page.
 *
 * @param array $class Array of form wrapper classes.
 * @param int   $id    ID of the form.
 * @param array $args  Additional args.
 *
 * @return array
 * @since 2.1
 *
 */
function add_class_for_form_grid( $class, $id, $args ) {
	$class[] = 'walkthecounty-form-grid-wrap';

	foreach ( $class as $index => $item ) {
		if ( false !== strpos( $item, 'walkthecounty-display-' ) ) {
			unset( $class[ $index ] );
		}
	}

	return $class;
}

/**
 * Add hidden field to Form Grid page
 *
 * @param int              $form_id The form ID.
 * @param array            $args    An array of form arguments.
 * @param WalkTheCounty_Donate_Form $form    Form object.
 *
 * @since 2.1
 */
function walkthecounty_is_form_grid_page_hidden_field( $id, $args, $form ) {
	echo '<input type="hidden" name="is-form-grid" value="true" />';
}

/**
 * Redirect to the same paginated URL on the Form Grid page
 * and adds query parameters to open the popup again after
 * redirection.
 *
 * @param string $redirect URL for redirection.
 * @param array  $args     Array of additional args.
 *
 * @return string
 * @since 2.1
 */
function walkthecounty_redirect_and_popup_form( $redirect, $args ) {

	// Check the page has Form Grid.
	$is_form_grid = isset( $_POST['is-form-grid'] ) ? walkthecounty_clean( $_POST['is-form-grid'] ) : '';

	if ( 'true' === $is_form_grid ) {

		$payment_mode = walkthecounty_clean( $_POST['payment-mode'] );
		$form_id      = $args['form-id'];

		// Get the URL without Query parameters.
		$redirect = strtok( $redirect, '?' );

		// Add query parameters 'form-id' and 'payment-mode'.
		$redirect = add_query_arg(
			array(
				'form-id'      => $form_id,
				'payment-mode' => $payment_mode,
			), $redirect
		);
	}

	// Return the modified URL.
	return $redirect;
}

add_filter( 'walkthecounty_send_back_to_checkout', 'walkthecounty_redirect_and_popup_form', 10, 2 );
