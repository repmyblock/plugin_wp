<?php
/**
 * WalkTheCounty Shortcodes
 *
 * @package     WalkTheCounty
 * @subpackage  Shortcodes
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Donation History Shortcode
 *
 * Displays a user's donation history.
 *
 * @since  1.0
 *
 * @param array       $atts
 * @param string|bool $content
 *
 * @return string|bool
 */
function walkthecounty_donation_history( $atts, $content = false ) {

	$donation_history_args = shortcode_atts(
		array(
			'id'             => true,
			'date'           => true,
			'donor'          => false,
			'amount'         => true,
			'status'         => false,
			'payment_method' => false,
		), $atts, 'donation_history'
	);

	// Always show receipt link.
	$donation_history_args['details'] = true;

	// Set Donation History Shortcode Arguments in session variable.
	WalkTheCounty()->session->set( 'walkthecounty_donation_history_args', $donation_history_args );

	$get_data = walkthecounty_clean( filter_input_array( INPUT_GET ) );

	// If payment_key query arg exists, return receipt instead of donation history.
	if (
		! empty( $get_data['donation_id'] ) ||
		(
			! empty( $get_data['action'] ) &&
			'view_in_browser' === $get_data['action']
		)
	) {
		ob_start();

		echo walkthecounty_receipt_shortcode( array() );

		// Display donation history link only if Receipt Access Session is available.
		if ( walkthecounty_get_receipt_session() || is_user_logged_in() ) {
			echo sprintf(
				'<a href="%s">%s</a>',
				esc_url( walkthecounty_get_history_page_uri() ),
				__( '&laquo; Return to All Donations', 'walkthecounty' )
			);
		}

		return ob_get_clean();
	}

	$email_access = walkthecounty_get_option( 'email_access' );

	ob_start();

	/**
	 * Determine access
	 *
	 * A. Check if a user is logged in or does a session exists.
	 * B. Does an email-access token exist?
	 */
	if (
		is_user_logged_in()
		|| false !== WalkTheCounty()->session->get_session_expiration()
		|| ( walkthecounty_is_setting_enabled( $email_access ) && WalkTheCounty()->email_access->token_exists )
		|| true === walkthecounty_get_history_session()
	) {
		walkthecounty_get_template_part( 'history', 'donations' );

		if ( ! empty( $content ) ) {
			echo do_shortcode( $content );
		}
	} elseif ( walkthecounty_is_setting_enabled( $email_access ) ) {
		// Is Email-based access enabled?
		walkthecounty_get_template_part( 'email', 'login-form' );

	} else {

		echo apply_filters( 'walkthecounty_donation_history_nonuser_message', WalkTheCounty_Notices::print_frontend_notice( __( 'You must be logged in to view your donation history. Please login using your account or create an account using the same email you used to donate with.', 'walkthecounty' ), false ) );
		echo do_shortcode( '[walkthecounty_login]' );
	}

	/**
	 * Filter to modify donation history HTMl
	 *
	 * @since 2.1
	 *
	 * @param string HTML content
	 * @param array  $atts
	 * @param string $content content pass between enclose content
	 *
	 * @return string HTML content
	 */
	return apply_filters( 'walkthecounty_donation_history_shortcode_html', ob_get_clean(), $atts, $content );
}

add_shortcode( 'donation_history', 'walkthecounty_donation_history' );

/**
 * Donation Form Shortcode
 *
 * Show the WalkTheCounty donation form.
 *
 * @since  1.0
 *
 * @param  array $atts Shortcode attributes
 *
 * @return string
 */
function walkthecounty_form_shortcode( $atts ) {
	$atts = shortcode_atts( walkthecounty_get_default_form_shortcode_args(), $atts, 'walkthecounty_form' );

	// Convert string to bool.
	$atts['show_title'] = filter_var( $atts['show_title'], FILTER_VALIDATE_BOOLEAN );
	$atts['show_goal']  = filter_var( $atts['show_goal'], FILTER_VALIDATE_BOOLEAN );

	// Fetch the WalkTheCounty Form.
	ob_start();
	walkthecounty_get_donation_form( $atts );
	$final_output = ob_get_clean();

	return apply_filters( 'walkthecounty_donate_form', $final_output, $atts );
}

add_shortcode( 'walkthecounty_form', 'walkthecounty_form_shortcode' );

/**
 * Donation Form Goal Shortcode.
 *
 * Show the WalkTheCounty donation form goals.
 *
 * @since  1.0
 *
 * @param  array $atts Shortcode attributes.
 *
 * @return string
 */
function walkthecounty_goal_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'id'        => '',
			'show_text' => true,
			'show_bar'  => true,
		), $atts, 'walkthecounty_goal'
	);

	// get the WalkTheCounty Form.
	ob_start();

	// Sanity check 1: ensure there is an ID Provided.
	if ( empty( $atts['id'] ) ) {
		WalkTheCounty_Notices::print_frontend_notice( __( 'The shortcode is missing Donation Form ID attribute.', 'walkthecounty' ), true );
	}

	// Sanity check 2: Check the form even has Goals enabled.
	if ( ! walkthecounty_is_setting_enabled( walkthecounty_get_meta( $atts['id'], '_walkthecounty_goal_option', true ) ) ) {

		WalkTheCounty_Notices::print_frontend_notice( __( 'The form does not have Goals enabled.', 'walkthecounty' ), true );
	} else {
		// Passed all sanity checks: output Goal.
		walkthecounty_show_goal_progress( $atts['id'], $atts );
	}

	$final_output = ob_get_clean();

	return apply_filters( 'walkthecounty_goal_shortcode_output', $final_output, $atts );
}

add_shortcode( 'walkthecounty_goal', 'walkthecounty_goal_shortcode' );


/**
 * Login Shortcode.
 *
 * Shows a login form allowing users to users to log in. This function simply
 * calls the walkthecounty_login_form function to display the login form.
 *
 * @since  1.0
 *
 * @param  array $atts Shortcode attributes.
 *
 * @uses   walkthecounty_login_form()
 *
 * @return string
 */
function walkthecounty_login_form_shortcode( $atts ) {

	$atts = shortcode_atts(
		array(
			// Add backward compatibility for redirect attribute.
			'redirect'        => '',
			'login-redirect'  => '',
			'logout-redirect' => '',
		), $atts, 'walkthecounty_login'
	);

	// Check login-redirect attribute first, if it empty or not found then check for redirect attribute and add value of this to login-redirect attribute.
	$atts['login-redirect'] = ! empty( $atts['login-redirect'] ) ? $atts['login-redirect'] : ( ! empty( $atts['redirect'] ) ? $atts['redirect'] : '' );

	return walkthecounty_login_form( $atts['login-redirect'], $atts['logout-redirect'] );
}

add_shortcode( 'walkthecounty_login', 'walkthecounty_login_form_shortcode' );

/**
 * Register Shortcode.
 *
 * Shows a registration form allowing users to users to register for the site.
 *
 * @since  1.0
 *
 * @param  array $atts Shortcode attributes.
 *
 * @uses   walkthecounty_register_form()
 *
 * @return string
 */
function walkthecounty_register_form_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'redirect' => '',
		), $atts, 'walkthecounty_register'
	);

	return walkthecounty_register_form( $atts['redirect'] );
}

add_shortcode( 'walkthecounty_register', 'walkthecounty_register_form_shortcode' );

/**
 * Receipt Shortcode.
 *
 * Shows a donation receipt.
 *
 * @since  1.0
 *
 * @param  array $atts Shortcode attributes.
 *
 * @return string
 */
function walkthecounty_receipt_shortcode( $atts ) {

	global $walkthecounty_receipt_args;

	$walkthecounty_receipt_args = shortcode_atts(
		array(
			'error'          => __( 'You are missing the donation id to view this donation receipt.', 'walkthecounty' ),
			'price'          => true,
			'donor'          => true,
			'date'           => true,
			'payment_method' => true,
			'payment_id'     => true,
			'payment_status' => false,
			'company_name'   => false,
			'status_notice'  => true,
		), $atts, 'walkthecounty_receipt'
	);

	ob_start();

	$donation_id  = false;
	$receipt_type = false;
	$get_data     = walkthecounty_clean( filter_input_array( INPUT_GET ) );
	$session      = walkthecounty_get_purchase_session();

	if ( ! empty( $get_data['donation_id'] ) ) {
		$donation_id = $get_data['donation_id'];
	} elseif ( ! empty( $get_data['action'] ) && 'view_in_browser' === $get_data['action'] ) {
		$receipt_type = 'view_in_browser';
	    $donation_id  = $get_data['_walkthecounty_hash'];
    } else if ( isset( $session['donation_id'] ) ) {
		$donation_id = $session['donation_id'];
	} elseif ( ! empty( $walkthecounty_receipt_args['id'] ) ) {
		$donation_id = $walkthecounty_receipt_args['id'];
	}

	// Display donation receipt placeholder while loading receipt via AJAX.
	if ( ! wp_doing_ajax() ) {
		walkthecounty_get_template_part( 'receipt/placeholder' );

		return sprintf(
			'<div id="walkthecounty-receipt" data-shortcode="%1$s" data-receipt-type="%2$s" data-donation-key="%3$s" >%4$s</div>',
			htmlspecialchars( wp_json_encode( $walkthecounty_receipt_args ) ),
			$receipt_type,
			$donation_id,
			ob_get_clean()
		);
	}

	return walkthecounty_display_donation_receipt( $atts );
}

add_shortcode( 'walkthecounty_receipt', 'walkthecounty_receipt_shortcode' );

/**
 * Profile Editor Shortcode.
 *
 * Outputs the WalkTheCounty Profile Editor to allow users to amend their details from the
 * front-end. This function uses the WalkTheCounty templating system allowing users to
 * override the default profile editor template. The profile editor template is located
 * under templates/shortcode-profile-editor.php, however, it can be altered by creating a
 * file called shortcode-profile-editor.php in the walkthecounty_template directory in your active theme's
 * folder. Please visit the WalkTheCounty Documentation for more information on how the
 * templating system is used.
 *
 * @since  1.0
 *
 * @param  array $atts Shortcode attributes.
 *
 * @return string Output generated from the profile editor
 */
function walkthecounty_profile_editor_shortcode( $atts ) {

	ob_start();

	// Restrict access to donor profile, if donor and user are disconnected.
	$is_donor_disconnected = get_user_meta( get_current_user_id(), '_walkthecounty_is_donor_disconnected', true );
	if ( is_user_logged_in() && $is_donor_disconnected ) {
		WalkTheCounty_Notices::print_frontend_notice( __( 'Your Donor and User profile are no longer connected. Please contact the site administrator.', 'walkthecounty' ), true, 'error' );

		return false;
	}

	walkthecounty_get_template_part( 'shortcode', 'profile-editor' );

	return ob_get_clean();
}

add_shortcode( 'walkthecounty_profile_editor', 'walkthecounty_profile_editor_shortcode' );

/**
 * Process Profile Updater Form.
 *
 * Processes the profile updater form by updating the necessary fields.
 *
 * @since  1.0
 *
 * @param  array $data Data sent from the profile editor.
 *
 * @return bool
 */
function walkthecounty_process_profile_editor_updates( $data ) {
	// Profile field change request.
	if ( empty( $_POST['walkthecounty_profile_editor_submit'] ) && ! is_user_logged_in() ) {
		return false;
	}

	// Nonce security.
	if ( ! wp_verify_nonce( $data['walkthecounty_profile_editor_nonce'], 'walkthecounty-profile-editor-nonce' ) ) {
		return false;
	}

	$user_id       = get_current_user_id();
	$old_user_data = get_userdata( $user_id );

	/* @var WalkTheCounty_Donor $donor */
	$donor            = new WalkTheCounty_Donor( $user_id, true );
	$old_company_name = $donor->get_company_name();

	$display_name     = isset( $data['walkthecounty_display_name'] ) ? sanitize_text_field( $data['walkthecounty_display_name'] ) : $old_user_data->display_name;
	$first_name       = isset( $data['walkthecounty_first_name'] ) ? sanitize_text_field( $data['walkthecounty_first_name'] ) : $old_user_data->first_name;
	$last_name        = isset( $data['walkthecounty_last_name'] ) ? sanitize_text_field( $data['walkthecounty_last_name'] ) : $old_user_data->last_name;
	$company_name     = ! empty( $data['walkthecounty_company_name'] ) ? sanitize_text_field( $data['walkthecounty_company_name'] ) : $old_company_name;
	$email            = isset( $data['walkthecounty_email'] ) ? sanitize_email( $data['walkthecounty_email'] ) : $old_user_data->user_email;
	$password         = ! empty( $data['walkthecounty_new_user_pass1'] ) ? $data['walkthecounty_new_user_pass1'] : '';
	$confirm_password = ! empty( $data['walkthecounty_new_user_pass2'] ) ? $data['walkthecounty_new_user_pass2'] : '';

	$userdata = array(
		'ID'           => $user_id,
		'first_name'   => $first_name,
		'last_name'    => $last_name,
		'display_name' => $display_name,
		'user_email'   => $email,
		'user_pass'    => $password,
		'company_name' => $company_name,
	);

	/**
	 * Fires before updating user profile.
	 *
	 * @since 1.0
	 *
	 * @param int   $user_id  The ID of the user.
	 * @param array $userdata User info, including ID, first name, last name, display name and email.
	 */
	do_action( 'walkthecounty_pre_update_user_profile', $user_id, $userdata );

	// Make sure to validate first name of existing donors.
	if ( empty( $first_name ) ) {
		// Empty First Name.
		walkthecounty_set_error( 'empty_first_name', __( 'Please enter your first name.', 'walkthecounty' ) );
	}

	// Make sure to validate passwords for existing Donors.
	walkthecounty_validate_user_password( $password, $confirm_password );

	if ( empty( $email ) ) {
		// Make sure email should not be empty.
		walkthecounty_set_error( 'email_empty', __( 'The email you entered is empty.', 'walkthecounty' ) );

	} elseif ( ! is_email( $email ) ) {
		// Make sure email should be valid.
		walkthecounty_set_error( 'email_not_valid', __( 'The email you entered is not valid. Please use another', 'walkthecounty' ) );

	} elseif ( $email !== $old_user_data->user_email ) {
		// Make sure the new email doesn't belong to another user.
		if ( email_exists( $email ) ) {
			walkthecounty_set_error( 'user_email_exists', __( 'The email you entered belongs to another user. Please use another.', 'walkthecounty' ) );
		} elseif ( WalkTheCounty()->donors->get_donor_by( 'email', $email ) ) {
			// Make sure the new email doesn't belong to another user.
			walkthecounty_set_error( 'donor_email_exists', __( 'The email you entered belongs to another donor. Please use another.', 'walkthecounty' ) );
		}
	}

	// Check for errors.
	$errors = walkthecounty_get_errors();

	if ( $errors ) {
		// Send back to the profile editor if there are errors.
		wp_redirect( $data['walkthecounty_redirect'] );
		walkthecounty_die();
	}

	// Update Donor First Name and Last Name.
	WalkTheCounty()->donors->update(
		$donor->id, array(
			'name' => trim( "{$first_name} {$last_name}" ),
		)
	);
	WalkTheCounty()->donor_meta->update_meta( $donor->id, '_walkthecounty_donor_first_name', $first_name );
	WalkTheCounty()->donor_meta->update_meta( $donor->id, '_walkthecounty_donor_last_name', $last_name );
	WalkTheCounty()->donor_meta->update_meta( $donor->id, '_walkthecounty_donor_company', $company_name );

	$current_user = wp_get_current_user();

	// Compares new values with old values to detect change in values.
	$email_update        = ( $email !== $current_user->user_email ) ? true : false;
	$display_name_update = ( $display_name !== $current_user->display_name ) ? true : false;
	$first_name_update   = ( $first_name !== $current_user->first_name ) ? true : false;
	$last_name_update    = ( $last_name !== $current_user->last_name ) ? true : false;
	$company_name_update = ( $company_name !== $old_company_name ) ? true : false;
	$update_code         = 0;

	/**
	 * True if update is done in display name, first name, last name or email.
	 *
	 * @var boolean
	 */
	$profile_update = ( $email_update || $display_name_update || $first_name_update || $last_name_update || $company_name_update );

	/**
	 * True if password fields are filled.
	 *
	 * @var boolean
	 */
	$password_update = ( ! empty( $password ) && ! empty( $confirm_password ) );

	if ( $profile_update ) {

		// If only profile fields are updated.
		$update_code = '1';

		if ( $password_update ) {

			// If profile fields AND password both are updated.
			$update_code = '2';
		}
	} elseif ( $password_update ) {

		// If only password is updated.
		$update_code = '3';
	}

	// Update the user.
	$updated = wp_update_user( $userdata );

	if ( $updated ) {

		/**
		 * Fires after updating user profile.
		 *
		 * @since 1.0
		 *
		 * @param int   $user_id  The ID of the user.
		 * @param array $userdata User info, including ID, first name, last name, display name and email.
		 */
		do_action( 'walkthecounty_user_profile_updated', $user_id, $userdata );

		$profile_edit_redirect_args = array(
			'updated'     => 'true',
			'update_code' => $update_code,
		);

		/**
		 * Update codes '2' and '3' indicate a password change.
		 * If the password is changed, then logout and redirect to the same page.
		 */
		if ( '2' === $update_code || '3' === $update_code ) {
			wp_logout( wp_redirect( add_query_arg( $profile_edit_redirect_args, $data['walkthecounty_redirect'] ) ) );
		} else {
			wp_redirect( add_query_arg( $profile_edit_redirect_args, $data['walkthecounty_redirect'] ) );
		}

		walkthecounty_die();
	}

	return false;
}

add_action( 'walkthecounty_edit_user_profile', 'walkthecounty_process_profile_editor_updates' );

/**
 * WalkTheCounty totals Shortcode.
 *
 * Shows a donation total.
 *
 * @since  2.1
 *
 * @param  array $atts Shortcode attributes.
 *
 * @return string
 */
function walkthecounty_totals_shortcode( $atts ) {
	$total = get_option( 'walkthecounty_earnings_total', false );

	$message = apply_filters( 'walkthecounty_totals_message', __( 'Hey! We\'ve raised {total} of the {total_goal} we are trying to raise for this campaign!', 'walkthecounty' ) );

	$atts = shortcode_atts(
		array(
			'total_goal'   => 0, // integer.
			'ids'          => 0, // integer|array.
			'cats'         => 0, // integer|array.
			'tags'         => 0, // integer|array.
			'message'      => $message,
			'link'         => '', // URL.
			'link_text'    => __( 'Donate Now', 'walkthecounty' ), // string,
			'progress_bar' => true, // boolean.
		), $atts, 'walkthecounty_totals'
	);

	// Total Goal.
	$total_goal = walkthecounty_maybe_sanitize_amount( $atts['total_goal'] );

	/**
	 * WalkTheCounty Action fire before the shortcode is rendering is started.
	 *
	 * @since 2.1.4
	 *
	 * @param array $atts shortcode attribute.
	 */
	do_action( 'walkthecounty_totals_goal_shortcode_before_render', $atts );

	// Build query based on cat, tag and Form ids.
	if ( ! empty( $atts['cats'] ) || ! empty( $atts['tags'] ) || ! empty( $atts['ids'] ) ) {

		$form_ids = array();
		if ( ! empty( $atts['ids'] ) ) {
			$form_ids = array_filter( array_map( 'trim', explode( ',', $atts['ids'] ) ) );
		}

		/**
		 * Filter to modify WP Query for Total Goal.
		 *
		 * @since 2.1.4
		 *
		 * @param array WP query argument for Total Goal.
		 */
		$form_args = array(
			'post_type'      => 'walkthecounty_forms',
			'post_status'    => 'publish',
			'post__in'       => $form_ids,
			'posts_per_page' => - 1,
			'fields'         => 'ids',
			'tax_query'      => array(
				'relation' => 'AND',
			),
		);

		if ( ! empty( $atts['cats'] ) ) {
			$cats                     = array_filter( array_map( 'trim', explode( ',', $atts['cats'] ) ) );
			$form_args['tax_query'][] = array(
				'taxonomy' => 'walkthecounty_forms_category',
				'terms'    => $cats,
			);
		}

		if ( ! empty( $atts['tags'] ) ) {
			$tags                     = array_filter( array_map( 'trim', explode( ',', $atts['tags'] ) ) );
			$form_args['tax_query'][] = array(
				'taxonomy' => 'walkthecounty_forms_tag',
				'terms'    => $tags,
			);
		}

		/**
		 * Filter to modify WP Query for Total Goal.
		 *
		 * @since 2.1.4
		 *
		 * @param array $form_args WP query argument for Total Goal.
		 *
		 * @return array $form_args WP query argument for Total Goal.
		 */
		$form_args = (array) apply_filters( 'walkthecounty_totals_goal_shortcode_query_args', $form_args );

		$forms = new WP_Query( $form_args );

		if ( isset( $forms->posts ) ) {
			$total = 0;
			foreach ( $forms->posts as $post ) {
				$form_earning = walkthecounty_get_meta( $post, '_walkthecounty_form_earnings', true );
				$form_earning = ! empty( $form_earning ) ? $form_earning : 0;

				/**
				 * Update Form earnings.
				 *
				 * @since 2.1
				 *
				 * @param int    $post         Form ID.
				 * @param string $form_earning Total earning of Form.
				 * @param array $atts shortcode attributes.
				 */
				$total += apply_filters( 'walkthecounty_totals_form_earning', $form_earning, $post, $atts );
			}
		}
	} // End if().

	// Append link with text.
	$donate_link = '';
	if ( ! empty( $atts['link'] ) ) {
		$donate_link = sprintf( ' <a class="walkthecounty-totals-text-link" href="%1$s">%2$s</a>', esc_url( $atts['link'] ), esc_html( $atts['link_text'] ) );
	}

	// Replace {total} in message.
	$message = str_replace(
		'{total}', walkthecounty_currency_filter(
			walkthecounty_format_amount(
				$total,
				array( 'sanitize' => false )
			)
		), esc_html( $atts['message'] )
	);

	// Replace {total_goal} in message.
	$message = str_replace(
		'{total_goal}', walkthecounty_currency_filter(
			walkthecounty_format_amount(
				$total_goal,
				array( 'sanitize' => true )
			)
		), $message
	);

	/**
	 * Update WalkTheCounty totals shortcode output.
	 *
	 * @since 2.1
	 *
	 * @param string $message Shortcode Message.
	 * @param array $atts ShortCode attributes.
	 */
	$message = apply_filters( 'walkthecounty_totals_shortcode_message', $message, $atts );

	ob_start();
	?>
	<div class="walkthecounty-totals-shortcode-wrap">
		<?php
		// Show Progress Bar if progress_bar set true.
		$show_progress_bar = isset( $atts['progress_bar'] ) ? filter_var( $atts['progress_bar'], FILTER_VALIDATE_BOOLEAN ) : true;
		if ( $show_progress_bar ) {
			walkthecounty_show_goal_totals_progress( $total, $total_goal );
		}

		echo sprintf( $message ) . $donate_link;
		?>
	</div>
	<?php
	$walkthecounty_totals_output = ob_get_clean();

	/**
	 * WalkTheCounty Action fire after the total goal shortcode rendering is end.
	 *
	 * @since 2.1.4
	 *
	 * @param array  $atts               shortcode attribute.
	 * @param string $walkthecounty_totals_output shortcode output.
	 */
	do_action( 'walkthecounty_totals_goal_shortcode_after_render', $atts, $walkthecounty_totals_output );

	/**
	 * WalkTheCounty Totals Shortcode output.
	 *
	 * @since 2.1
	 *
	 * @param string $walkthecounty_totals_output
	 */
	return apply_filters( 'walkthecounty_totals_shortcode_output', $walkthecounty_totals_output );

}

add_shortcode( 'walkthecounty_totals', 'walkthecounty_totals_shortcode' );


/**
 * Displays donation forms in a grid layout.
 *
 * @since  2.1.0
 *
 * @param array $atts                {
 *                                   Optional. Attributes of the form grid shortcode.
 *
 * @type int    $forms_per_page      Number of forms per page. Default '12'.
 * @type bool   $paged               Whether to paginate forms. Default 'true'.
 * @type string $ids                 A comma-separated list of form IDs to display. Default empty.
 * @type string exclude              A comma-separated list of form IDs to exclude from display. Default empty.
 * @type string $cats                A comma-separated list of form categories to display. Default empty.
 * @type string $tags                A comma-separated list of form tags to display. Default empty.
 * @type string $columns             Maximum columns to display. Default 'best-fit'.
 *                                       Accepts 'best-fit', '1', '2', '3', '4'.
 * @type bool   $show_title          Whether to display form title. Default 'true'.
 * @type bool   $show_goal           Whether to display form goal. Default 'true'.
 * @type bool   $show_excerpt        Whether to display form excerpt. Default 'true'.
 * @type bool   $show_featured_image Whether to display featured image. Default 'true'.
 * @type string $image_size          Featured image size. Default 'medium'. Accepts WordPress image sizes.
 * @type string $image_height        Featured image height. Default 'auto'. Accepts valid CSS heights.
 * @type int    $excerpt_length      Number of words before excerpt is truncated. Default '16'.
 * @type string $display_style       How the form is displayed, either in new page or modal popup.
 *                                       Default 'redirect'. Accepts 'redirect', 'modal'.
 * }
 * @return string|bool The markup of the form grid or false.
 */
function walkthecounty_form_grid_shortcode( $atts ) {

	$walkthecounty_settings = walkthecounty_get_settings();

	$atts = shortcode_atts(
		array(
			'forms_per_page'      => 12,
			'paged'               => true,
			'ids'                 => '',
			'exclude'             => '',
			'orderby'             => 'date',
			'order'               => 'DESC',
			'cats'                => '',
			'tags'                => '',
			'columns'             => 'best-fit',
			'show_title'          => true,
			'show_goal'           => true,
			'show_excerpt'        => true,
			'show_featured_image' => true,
			'image_size'          => 'medium',
			'image_height'        => 'auto',
			'excerpt_length'      => 16,
			'display_style'       => 'modal_reveal',
			'status'              => '', // open or closed.
		), $atts
	);

	// Validate integer attributes.
	$atts['forms_per_page'] = intval( $atts['forms_per_page'] );
	$atts['excerpt_length'] = intval( $atts['excerpt_length'] );

	// Validate boolean attributes.
	$boolean_attributes = array(
		'paged',
		'show_title',
		'show_goal',
		'show_excerpt',
		'show_featured_image',
	);

	foreach ( $boolean_attributes as $att ) {
		$atts[ $att ] = filter_var( $atts[ $att ], FILTER_VALIDATE_BOOLEAN );
	}

	// Set default form query args.
	$form_args = array(
		'post_type'      => 'walkthecounty_forms',
		'post_status'    => 'publish',
		'posts_per_page' => $atts['forms_per_page'],
		'orderby'        => $atts['orderby'],
		'order'          => $atts['order'],
		'tax_query'      => array(
			'relation' => 'AND',
		),
	);

	// Filter results of form grid based on form status.
	$form_closed_status = trim( $atts['status'] );

	if ( ! empty( $form_closed_status ) ) {
		$form_args['meta_query'] = array(
			array(
				'key'   => '_walkthecounty_form_status',
				'value' => $form_closed_status,
			),
		);
	}

	// Maybe add pagination.
	if ( true === $atts['paged'] ) {
		$form_args['paged'] = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
	}

	// Maybe filter forms by IDs.
	if ( ! empty( $atts['ids'] ) ) {
		$form_args['post__in'] = array_filter( array_map( 'trim', explode( ',', $atts['ids'] ) ) );
	}

	// Convert comma-separated form IDs into array.
	if ( ! empty( $atts['exclude'] ) ) {
		$form_args['post__not_in'] = array_filter(
			array_map(
				function( $item ) {
					return intval( trim( $item ) );
				}, explode( ',', $atts['exclude'] )
			)
		);
	}

	// Maybe filter by form category.
	if ( ! empty( $atts['cats'] ) ) {
		$cats = array_filter( array_map( 'trim', explode( ',', $atts['cats'] ) ) );

		// Backward compatibility for term_ids.
		$term_ids = array_unique( array_filter( $cats, 'is_numeric' ) );
		if ( $term_ids ) {
			$form_args['tax_query'][] = array(
				'taxonomy' => 'walkthecounty_forms_category',
				'terms'    => $term_ids,
			);

		}

		$term_slug = array_unique( array_filter( array_diff( $cats, $term_ids ) ) );
		if ( $term_slug ) {
			$form_args['tax_query'][] = array(
				'taxonomy' => 'walkthecounty_forms_category',
				'field'    => 'slug',
				'terms'    => $term_slug,
			);

		}
	}

	// Maybe filter by form tag.
	if ( ! empty( $atts['tags'] ) ) {
		$tags = array_filter( array_map( 'trim', explode( ',', $atts['tags'] ) ) );

		// Backward compatibility for term_ids.
		$tag_ids = array_unique( array_filter( $tags, 'is_numeric' ) );
		if ( $tag_ids ) {
			$form_args['tax_query'][] = array(
				'taxonomy' => 'walkthecounty_forms_tag',
				'terms'    => $tag_ids,
			);

		}

		$tag_slug = array_unique( array_filter( array_diff( $tags, $tag_ids ) ) );
		if ( $tag_slug ) {
			$form_args['tax_query'][] = array(
				'taxonomy' => 'walkthecounty_forms_tag',
				'field'    => 'slug',
				'terms'    => $tag_slug,
			);

		}
	}

	/**
	 * Filter to modify WP Query for Total Goal.
	 *
	 * @since 2.1.4
	 *
	 * @param array $form_args WP query argument for Grid.
	 *
	 * @return array $form_args WP query argument for Grid.
	 */
	$form_args = (array) apply_filters( 'walkthecounty_form_grid_shortcode_query_args', $form_args );

	// Maybe filter by form Amount Donated or Number of Donations.
	switch ( $atts['orderby'] ) {
		case 'amount_donated':
			$form_args['meta_key'] = '_walkthecounty_form_earnings';
			$form_args['orderby']  = 'meta_value_num';
			break;
		case 'number_donations':
			$form_args['meta_key'] = '_walkthecounty_form_sales';
			$form_args['orderby']  = 'meta_value_num';
			break;
		case 'closest_to_goal':
			if ( walkthecounty_has_upgrade_completed( 'v240_update_form_goal_progress' ) ) {
				$form_args['meta_key'] = '_walkthecounty_form_goal_progress';
				$form_args['orderby']  = 'meta_value_num';
			}
			break;
	}

	// Query to output donation forms.
	$form_query = new WP_Query( $form_args );

	if ( $form_query->have_posts() ) {
		ob_start();

		add_filter( 'add_walkthecounty_goal_progress_class', 'add_walkthecounty_goal_progress_class', 10, 1 );
		add_filter( 'add_walkthecounty_goal_progress_bar_class', 'add_walkthecounty_goal_progress_bar_class', 10, 1 );
		add_filter( 'walkthecounty_form_wrap_classes', 'add_class_for_form_grid', 10, 3 );
		add_action( 'walkthecounty_donation_form_top', 'walkthecounty_is_form_grid_page_hidden_field', 10, 3 );

		echo '<div class="walkthecounty-wrap">';
		echo '<div class="walkthecounty-grid walkthecounty-grid--' . esc_attr( $atts['columns'] ) . '">';

		while ( $form_query->have_posts() ) {
			$form_query->the_post();

			// WalkTheCounty/templates/shortcode-form-grid.php.
			walkthecounty_get_template( 'shortcode-form-grid', array( $walkthecounty_settings, $atts ) );

		}

		wp_reset_postdata();

		echo '</div><!-- .walkthecounty-grid -->';

		remove_filter( 'add_walkthecounty_goal_progress_class', 'add_walkthecounty_goal_progress_class' );
		remove_filter( 'add_walkthecounty_goal_progress_bar_class', 'add_walkthecounty_goal_progress_bar_class' );
		remove_filter( 'walkthecounty_form_wrap_classes', 'add_class_for_form_grid', 10 );
		remove_action( 'walkthecounty_donation_form_top', 'walkthecounty_is_form_grid_page_hidden_field', 10 );

		if ( false !== $atts['paged'] ) {
			$paginate_args = array(
				'current'   => max( 1, get_query_var( 'paged' ) ),
				'total'     => $form_query->max_num_pages,
				'show_all'  => false,
				'end_size'  => 1,
				'mid_size'  => 2,
				'prev_next' => true,
				'prev_text' => __( '« Previous', 'walkthecounty' ),
				'next_text' => __( 'Next »', 'walkthecounty' ),
				'type'      => 'plain',
				'add_args'  => false,
			);

			printf(
				'<div class="walkthecounty-page-numbers">%s</div>',
				paginate_links( $paginate_args )
			);
		}
		echo '</div><!-- .walkthecounty-wrap -->';

		return ob_get_clean();
	} // End if().
}

add_shortcode( 'walkthecounty_form_grid', 'walkthecounty_form_grid_shortcode' );
