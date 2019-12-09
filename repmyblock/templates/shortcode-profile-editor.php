<?php
/**
 * Profile Editor
 *
 * This template is used to display the profile editor with [walkthecounty_profile_editor]
 *
 * @copyright    Copyright (c) 2016, WalkTheCountyWP
 * @license      https://opensource.org/licenses/gpl-license GNU Public License
 */

$current_user = wp_get_current_user();

if ( is_user_logged_in() ) :
	$user_id      = get_current_user_id();
	$first_name   = get_user_meta( $user_id, 'first_name', true );
	$last_name    = get_user_meta( $user_id, 'last_name', true );
	$last_name    = get_user_meta( $user_id, 'last_name', true );
	$display_name = $current_user->display_name;
	$donor        = new WalkTheCounty_Donor( $user_id, true );
	$address      = $donor->get_donor_address( array( 'address_type' => 'personal' ) );
	$company_name = $donor->get_meta( '_walkthecounty_donor_company', true );

	if ( isset( $_GET['updated'] ) && 'true' === $_GET['updated'] && ! walkthecounty_get_errors() ) {
		if ( isset( $_GET['update_code'] ) ) {
			if ( 1 === absint( $_GET['update_code'] ) ) {
				printf( '<p class="walkthecounty_success"><strong>%1$s</strong> %2$s</p>', esc_html__( 'Success:', 'walkthecounty' ), esc_html__( 'Your profile has been updated.', 'walkthecounty' ) );
			}
		}
	}

	WalkTheCounty()->notices->render_frontend_notices( 0 );

	/**
	 * Fires in the profile editor shortcode, before the form.
	 *
	 * Allows you to add new elements before the form.
	 *
	 * @since 1.0
	 */
	do_action( 'walkthecounty_profile_editor_before' );
	?>

	<form id="walkthecounty_profile_editor_form" class="walkthecounty-form" action="<?php echo walkthecounty_get_current_page_url(); ?>" method="post">
		<fieldset>
			<legend id="walkthecounty_profile_name_label"><?php _e( 'Profile', 'walkthecounty' ); ?></legend>

			<h3 id="walkthecounty_personal_information_label"
				class="walkthecounty-section-break"><?php _e( 'Change your Name', 'walkthecounty' ); ?></h3>

			<p id="walkthecounty_profile_first_name_wrap" class="form-row form-row-first form-row-responsive">
				<label for="walkthecounty_first_name">
					<?php _e( 'First Name', 'walkthecounty' ); ?>
					<span class="walkthecounty-required-indicator  ">*</span>
				</label>
				<input name="walkthecounty_first_name" id="walkthecounty_first_name" class="text walkthecounty-input" type="text"
					   value="<?php echo esc_attr( $first_name ); ?>"/>
			</p>

			<p id="walkthecounty_profile_last_name_wrap" class="form-row form-row-last form-row-responsive">
				<label for="walkthecounty_last_name"><?php _e( 'Last Name', 'walkthecounty' ); ?></label>
				<input name="walkthecounty_last_name" id="walkthecounty_last_name" class="text walkthecounty-input" type="text"
					   value="<?php echo esc_attr( $last_name ); ?>"/>
			</p>

			<?php if ( ! empty( $company_name ) ) : ?>
				<p id="walkthecounty_profile_company_name_wrap" class="form-row form-row-wide">
					<label for="walkthecounty_company_name"><?php _e( 'Company Name', 'walkthecounty' ); ?></label>
					<input name="walkthecounty_company_name" id="walkthecounty_company_name" class="text walkthecounty-input" type="text"
						   value="<?php echo esc_attr( $company_name ); ?>"/>
				</p>
			<?php endif; ?>

			<p id="walkthecounty_profile_display_name_wrap" class="form-row form-row-first form-row-responsive">
				<label for="walkthecounty_display_name"><?php _e( 'Display Name', 'walkthecounty' ); ?></label>
				<select name="walkthecounty_display_name" id="walkthecounty_display_name" class="select walkthecounty-select">
					<?php if ( ! empty( $current_user->first_name ) ) : ?>
						<option <?php selected( $display_name, $current_user->first_name ); ?>
							value="<?php echo esc_attr( $current_user->first_name ); ?>"><?php echo esc_html( $current_user->first_name ); ?></option>
					<?php endif; ?>
					<option <?php selected( $display_name, $current_user->user_nicename ); ?>
						value="<?php echo esc_attr( $current_user->user_nicename ); ?>"><?php echo esc_html( $current_user->user_nicename ); ?></option>
					<?php if ( ! empty( $current_user->last_name ) ) : ?>
						<option <?php selected( $display_name, $current_user->last_name ); ?>
							value="<?php echo esc_attr( $current_user->last_name ); ?>"><?php echo esc_html( $current_user->last_name ); ?></option>
					<?php endif; ?>
					<?php if ( ! empty( $current_user->first_name ) && ! empty( $current_user->last_name ) ) : ?>
						<option <?php selected( $display_name, $current_user->first_name . ' ' . $current_user->last_name ); ?>
							value="<?php echo esc_attr( $current_user->first_name . ' ' . $current_user->last_name ); ?>"><?php echo esc_html( $current_user->first_name . ' ' . $current_user->last_name ); ?></option>
						<option <?php selected( $display_name, $current_user->last_name . ' ' . $current_user->first_name ); ?>
							value="<?php echo esc_attr( $current_user->last_name . ' ' . $current_user->first_name ); ?>"><?php echo esc_html( $current_user->last_name . ' ' . $current_user->first_name ); ?></option>
					<?php endif; ?>
				</select>
				<?php
				/**
				 * Fires in the profile editor shortcode, to the name section.
				 *
				 * Allows you to add new elements to the name section.
				 *
				 * @since 1.0
				 */
				do_action( 'walkthecounty_profile_editor_name' );
				?>
			</p>

			<?php
			/**
			 * Fires in the profile editor shortcode, after the name field.
			 *
			 * Allows you to add new fields after the name field.
			 *
			 * @since 1.0
			 */
			do_action( 'walkthecounty_profile_editor_after_name' );
			?>

			<p class="form-row form-row-last form-row-responsive">
				<label for="walkthecounty_email">
					<?php _e( 'Email Address', 'walkthecounty' ); ?>
					<span class="walkthecounty-required-indicator  ">*</span>
				</label>
				<input name="walkthecounty_email" id="walkthecounty_email" class="text walkthecounty-input required" type="email"
					   value="<?php echo esc_attr( $current_user->user_email ); ?>" required aria-required="true"/>
				<?php
				/**
				 * Fires in the profile editor shortcode, to the email section.
				 *
				 * Allows you to add new elements to the email section.
				 *
				 * @since 1.0
				 */
				do_action( 'walkthecounty_profile_editor_email' );
				?>
			</p>

			<?php
			/**
			 * Fires in the profile editor shortcode, after the email field.
			 *
			 * Allows you to add new fields after the email field.
			 *
			 * @since 1.0
			 */
			do_action( 'walkthecounty_profile_editor_after_email' );
			?>

			<h3 id="walkthecounty_profile_password_label"
				class="walkthecounty-section-break"><?php _e( 'Change your Password', 'walkthecounty' ); ?></h3>

			<div id="walkthecounty_profile_password_wrap" class="walkthecounty-clearfix">
				<p id="walkthecounty_profile_password_wrap_1" class="form-row form-row-first form-row-responsive">
					<label for="walkthecounty_new_user_pass1"><?php _e( 'New Password', 'walkthecounty' ); ?></label>
					<input name="walkthecounty_new_user_pass1" id="walkthecounty_new_user_pass1" class="password walkthecounty-input"
						   type="password"/>
				</p>

				<p id="walkthecounty_profile_password_wrap_2" class="form-row form-row-last form-row-responsive">
					<label for="walkthecounty_new_user_pass2"><?php _e( 'Re-enter Password', 'walkthecounty' ); ?></label>
					<input name="walkthecounty_new_user_pass2" id="walkthecounty_new_user_pass2" class="password walkthecounty-input"
						   type="password"/>
					<?php
					/**
					 * Fires in the profile editor shortcode, to the password section.
					 *
					 * Allows you to add new elements to the password section.
					 *
					 * @since 1.0
					 */
					do_action( 'walkthecounty_profile_editor_password' );
					?>
				</p>
			</div>

			<p class="walkthecounty_password_change_notice"><?php _e( 'Please note after changing your password, you must log back in.', 'walkthecounty' ); ?></p>

			<?php
			/**
			 * Fires in the profile editor shortcode, after the password field.
			 *
			 * Allows you to add new fields after the password field.
			 *
			 * @since 1.0
			 */
			do_action( 'walkthecounty_profile_editor_after_password' );
			?>

			<p id="walkthecounty_profile_submit_wrap">
				<input type="hidden" name="walkthecounty_profile_editor_nonce"
					   value="<?php echo wp_create_nonce( 'walkthecounty-profile-editor-nonce' ); ?>"/>
				<input type="hidden" name="walkthecounty_action" value="edit_user_profile"/>
				<input type="hidden" name="walkthecounty_redirect"
					   value="<?php echo esc_url( walkthecounty_get_current_page_url() ); ?>"/>
				<input name="walkthecounty_profile_editor_submit" id="walkthecounty_profile_editor_submit" type="submit"
					   class="walkthecounty_submit" value="<?php _e( 'Save Changes', 'walkthecounty' ); ?>"/>
			</p>

		</fieldset>

	</form><!-- #walkthecounty_profile_editor_form -->

	<?php
	/**
	 * Fires in the profile editor shortcode, after the form.
	 *
	 * Allows you to add new elements after the form.
	 *
	 * @since 1.0
	 */
	do_action( 'walkthecounty_profile_editor_after' );
	?>

<?php
else :
	if (
		isset( $_GET['updated'] )
		&& 'true' === $_GET['updated']
		&& ! walkthecounty_get_errors()
	) {
		if ( isset( $_GET['update_code'] ) ) {
			switch ( $_GET['update_code'] ) {
				case '2':
					printf( '<p class="walkthecounty_success"><strong>%1$s</strong> %2$s</p>', esc_html__( 'Success:', 'walkthecounty' ), esc_html__( 'Your profile and password has been updated.', 'walkthecounty' ) );
					_e( 'Log in with your new credentials.', 'walkthecounty' );
					echo walkthecounty_login_form();
					break;

				case '3':
					printf( '<p class="walkthecounty_success"><strong>%1$s</strong> %2$s</p>', esc_html__( 'Success:', 'walkthecounty' ), esc_html__( 'Your password has been updated.', 'walkthecounty' ) );
					_e( 'Log in with your new credentials.', 'walkthecounty' );
					echo walkthecounty_login_form();
					break;

				default:
					break;
			}
		}
	} else {
		_e( 'You need to log in to edit your profile.', 'walkthecounty' );
		echo walkthecounty_login_form();
	}
endif;
