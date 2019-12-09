<?php
/**
 * This template is used to display the login form with [walkthecounty_login]
 */

$get_data = walkthecounty_clean( filter_input_array( INPUT_GET ) );

if ( ! is_user_logged_in() ) {

	if ( ! empty( $get_data['donation_id'] ) ) {
		$walkthecounty_login_redirect = add_query_arg(
			'donation_id', $get_data['donation_id'],
			walkthecounty_get_history_page_uri()
		);
	}

	// Show any error messages after form submission.
	WalkTheCounty()->notices->render_frontend_notices( 0 ); ?>
	<form id="walkthecounty-login-form" class="walkthecounty-form" action="" method="post">
		<fieldset>
			<legend><?php esc_html_e( 'Log into Your Account', 'walkthecounty' ); ?></legend>
			<?php
			/**
			 * Fires in the login shortcode, before the login fields.
			 *
			 * Allows you to add new fields before the default fields.
			 *
			 * @since 1.0
			 */
			do_action( 'walkthecounty_login_fields_before' );
			?>
			<div class="walkthecounty-login-username walkthecounty-login">
				<label for="walkthecounty_user_login"><?php esc_html_e( 'Username', 'walkthecounty' ); ?></label>
				<input name="walkthecounty_user_login" id="walkthecounty_user_login" class="walkthecounty-required walkthecounty-input" type="text" required aria-required="true" />
			</div>

			<div class="walkthecounty-login-password walkthecounty-login">
				<label for="walkthecounty_user_pass"><?php esc_html_e( 'Password', 'walkthecounty' ); ?></label>
				<input name="walkthecounty_user_pass" id="walkthecounty_user_pass" class="walkthecounty-password walkthecounty-required walkthecounty-input" type="password" required aria-required="true" />
			</div>

			<div class="walkthecounty-login-submit walkthecounty-login">
				<input type="hidden" name="walkthecounty_login_redirect" value="<?php echo esc_url( $walkthecounty_login_redirect ); ?>" />
				<input type="hidden" name="walkthecounty_login_nonce" value="<?php echo wp_create_nonce( 'walkthecounty-login-nonce' ); ?>" />
				<input type="hidden" name="walkthecounty_action" value="user_login" />
				<input id="walkthecounty_login_submit" type="submit" class="walkthecounty_submit" value="<?php esc_html_e( 'Log In', 'walkthecounty' ); ?>" />
			</div>

			<div class="walkthecounty-lost-password walkthecounty-login">
				<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>">
					<?php esc_html_e( 'Reset Password', 'walkthecounty' ); ?>
				</a>
			</div>
			<?php
			/**
			 * Fires in the login shortcode, after the login fields.
			 *
			 * Allows you to add new fields after the default fields.
			 *
			 * @since 1.0
			 */
			do_action( 'walkthecounty_login_fields_after' );
			?>
		</fieldset>
	</form>
	<?php
} elseif ( isset( $get_data['walkthecounty-login-success'] ) && true === (bool) $get_data['walkthecounty-login-success'] ) {

	WalkTheCounty_Notices::print_frontend_notice(
		apply_filters( 'walkthecounty_successful_login_message', esc_html__( 'Login successful. Welcome!', 'walkthecounty' ) ),
		true,
		'success'
	);
} else {
	WalkTheCounty_Notices::print_frontend_notice(
		apply_filters( 'walkthecounty_already_logged_in_message', sprintf(
			/* translators: %s Redirect URL. */
			__( 'You are already logged in to the site. <a href="%s">Click here</a> to log out.', 'walkthecounty' ),
			esc_url(  wp_logout_url() )
		) ),
		true,
		'warning'
	);
}
