<?php
/**
 * This template is used to display the registration form with [walkthecounty_register]
 */
WalkTheCounty()->notices->render_frontend_notices( 0 ); ?>

<form id="walkthecounty-register-form" class="walkthecounty-form" action="" method="post">
	<?php
	/**
	 * Fires in the registration shortcode, to the form top.
	 *
	 * Allows you to add elements to the form top.
	 *
	 * @since 1.0
	 */
	do_action( 'walkthecounty_register_form_fields_top' );
	?>

	<fieldset>
		<legend><?php esc_html_e( 'Register a New Account', 'walkthecounty' ); ?></legend>

		<?php
		/**
		 * Fires in the registration shortcode, before the registration fields.
		 *
		 * Allows you to add elements to the fieldset, before the fields.
		 *
		 * @since 1.0
		 */
		do_action( 'walkthecounty_register_form_fields_before' );
		?>

		<div class="form-row form-row-first form-row-responsive">
			<label for="walkthecounty-user-login"><?php esc_html_e( 'Username', 'walkthecounty' ); ?></label>
			<input id="walkthecounty-user-login" class="required walkthecounty-input" type="text" name="walkthecounty_user_login" required aria-required="true" />
		</div>

		<div class="form-row form-row-last form-row-responsive">
			<label for="walkthecounty-user-email"><?php esc_html_e( 'Email', 'walkthecounty' ); ?></label>
			<input id="walkthecounty-user-email" class="required walkthecounty-input" type="email" name="walkthecounty_user_email" required aria-required="true" />
		</div>

		<div class="form-row form-row-first form-row-responsive">
			<label for="walkthecounty-user-pass"><?php esc_html_e( 'Password', 'walkthecounty' ); ?></label>
			<input id="walkthecounty-user-pass" class="password required walkthecounty-input" type="password" name="walkthecounty_user_pass" required aria-required="true" />
		</div>

		<div class="form-row form-row-last form-row-responsive">
			<label for="walkthecounty-user-pass2"><?php esc_html_e( 'Confirm PW', 'walkthecounty' ); ?></label>
			<input id="walkthecounty-user-pass2" class="password required walkthecounty-input" type="password" name="walkthecounty_user_pass2" required aria-required="true" />
		</div>

		<?php
		/**
		 * Fires in the registration shortcode, before submit button.
		 *
		 * Allows you to add elements before submit button.
		 *
		 * @since 1.0
		 */
		do_action( 'walkthecounty_register_form_fields_before_submit' );
		?>

		<div class="walkthecounty-hidden">
			<input type="hidden" name="walkthecounty_honeypot" value="" />
			<input type="hidden" name="walkthecounty_action" value="user_register" />
			<input type="hidden" name="walkthecounty_redirect" value="<?php echo esc_url( $walkthecounty_register_redirect ); ?>" />
		</div>

		<div class="form-row">
			<input class="button" name="walkthecounty_register_submit" type="submit" value="<?php esc_attr_e( 'Register', 'walkthecounty' ); ?>" />
		</div>

		<?php
		/**
		 * Fires in the registration shortcode, after the registration fields.
		 *
		 * Allows you to add elements to the fieldset, after the fields and the submit button.
		 *
		 * @since 1.0
		 */
		do_action( 'walkthecounty_register_form_fields_after' );
		?>

	</fieldset>

	<?php
	/**
	 * Fires in the registration shortcode, to the form bottom.
	 *
	 * Allows you to add elements to the form bottom.
	 *
	 * @since 1.0
	 */
	do_action( 'walkthecounty_register_form_fields_bottom' );
	?>
</form>
