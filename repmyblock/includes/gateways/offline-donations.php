<?php
/**
 * Offline Donations Gateway
 *
 * @package     WalkTheCounty
 * @subpackage  Gateways
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

/**
 * Add our payment instructions to the checkout
 *
 * @since  1.0
 *
 * @param  int $form_id WalkTheCounty form id.
 *
 * @return void
 */
function walkthecounty_offline_payment_cc_form( $form_id ) {
	// Get offline payment instruction.
	$offline_instructions = walkthecounty_get_offline_payment_instruction( $form_id, true );

	ob_start();

	/**
	 * Fires before the offline info fields.
	 *
	 * @since 1.0
	 *
	 * @param int $form_id WalkTheCounty form id.
	 */
	do_action( 'walkthecounty_before_offline_info_fields', $form_id );
	?>
    <fieldset id="walkthecounty_offline_payment_info">
		<?php echo stripslashes( $offline_instructions ); ?>
    </fieldset>
	<?php
	/**
	 * Fires after the offline info fields.
	 *
	 * @since 1.0
	 *
	 * @param int $form_id WalkTheCounty form id.
	 */
	do_action( 'walkthecounty_after_offline_info_fields', $form_id );

	echo ob_get_clean();
}

add_action( 'walkthecounty_offline_cc_form', 'walkthecounty_offline_payment_cc_form' );

/**
 * WalkTheCounty Offline Billing Field
 *
 * @param $form_id
 */
function walkthecounty_offline_billing_fields( $form_id ) {
	//Enable Default CC fields (billing info)
	$post_offline_cc_fields        = walkthecounty_get_meta( $form_id, '_walkthecounty_offline_donation_enable_billing_fields_single', true );
	$post_offline_customize_option = walkthecounty_get_meta( $form_id, '_walkthecounty_customize_offline_donations', true, 'global' );

	$global_offline_cc_fields = walkthecounty_get_option( 'walkthecounty_offline_donation_enable_billing_fields' );

	//Output CC Address fields if global option is on and user hasn't elected to customize this form's offline donation options
	if (
		( walkthecounty_is_setting_enabled( $post_offline_customize_option, 'global' ) && walkthecounty_is_setting_enabled( $global_offline_cc_fields ) )
		|| ( walkthecounty_is_setting_enabled( $post_offline_customize_option, 'enabled' ) && walkthecounty_is_setting_enabled( $post_offline_cc_fields ) )
	) {
		walkthecounty_default_cc_address_fields( $form_id );
	}
}

add_action( 'walkthecounty_before_offline_info_fields', 'walkthecounty_offline_billing_fields', 10, 1 );

/**
 * Process the payment
 *
 * @since  1.0
 *
 * @param $purchase_data
 *
 * @return void
 */
function walkthecounty_offline_process_payment( $purchase_data ) {

	// Setup the payment details.
	$payment_data = array(
		'price'           => $purchase_data['price'],
		'walkthecounty_form_title' => $purchase_data['post_data']['walkthecounty-form-title'],
		'walkthecounty_form_id'    => intval( $purchase_data['post_data']['walkthecounty-form-id'] ),
		'walkthecounty_price_id'   => isset( $purchase_data['post_data']['walkthecounty-price-id'] ) ? $purchase_data['post_data']['walkthecounty-price-id'] : '',
		'date'            => $purchase_data['date'],
		'user_email'      => $purchase_data['user_email'],
		'purchase_key'    => $purchase_data['purchase_key'],
		'currency'        => walkthecounty_get_currency( $purchase_data['post_data']['walkthecounty-form-id'], $purchase_data ),
		'user_info'       => $purchase_data['user_info'],
		'status'          => 'pending',
		'gateway'         => 'offline',
	);


	// record the pending payment
	$payment = walkthecounty_insert_payment( $payment_data );

	if ( $payment ) {
		walkthecounty_send_to_success_page();
	} else {
		// if errors are present, send the user back to the donation form so they can be corrected
		walkthecounty_send_back_to_checkout( '?payment-mode=' . $purchase_data['post_data']['walkthecounty-gateway'] );
	}

}

add_action( 'walkthecounty_gateway_offline', 'walkthecounty_offline_process_payment' );


/**
 * Send Offline Donation Instructions
 *
 * Sends a notice to the donor with offline instructions; can be customized per form
 *
 * @param int $payment_id
 *
 * @since       1.0
 * @return void
 */
function walkthecounty_offline_send_donor_instructions( $payment_id = 0 ) {

	$payment_data                      = walkthecounty_get_payment_meta( $payment_id );
	$post_offline_customization_option = walkthecounty_get_meta( $payment_data['form_id'], '_walkthecounty_customize_offline_donations', true );

	//Customize email content depending on whether the single form has been customized
	$email_content = walkthecounty_get_option( 'global_offline_donation_email' );

	if ( walkthecounty_is_setting_enabled( $post_offline_customization_option, 'enabled' ) ) {
		$email_content = walkthecounty_get_meta( $payment_data['form_id'], '_walkthecounty_offline_donation_email', true );
	}

	$from_name = walkthecounty_get_option( 'from_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );

	/**
	 * Filters the from name.
	 *
	 * @since 1.7
	 */
	$from_name = apply_filters( 'walkthecounty_donation_from_name', $from_name, $payment_id, $payment_data );

	$from_email = walkthecounty_get_option( 'from_email', get_bloginfo( 'admin_email' ) );

	/**
	 * Filters the from email.
	 *
	 * @since 1.7
	 */
	$from_email = apply_filters( 'walkthecounty_donation_from_address', $from_email, $payment_id, $payment_data );

	$to_email = walkthecounty_get_payment_user_email( $payment_id );

	$subject = walkthecounty_get_option( 'offline_donation_subject', __( 'Offline Donation Instructions', 'walkthecounty' ) );
	if ( walkthecounty_is_setting_enabled( $post_offline_customization_option, 'enabled' ) ) {
		$subject = walkthecounty_get_meta( $payment_data['form_id'], '_walkthecounty_offline_donation_subject', true );
	}

	$subject = apply_filters( 'walkthecounty_offline_donation_subject', wp_strip_all_tags( $subject ), $payment_id );
	$subject = walkthecounty_do_email_tags( $subject, $payment_id );

	$attachments = apply_filters( 'walkthecounty_offline_donation_attachments', array(), $payment_id, $payment_data );
	$message     = walkthecounty_do_email_tags( $email_content, $payment_id );

	$emails = WalkTheCounty()->emails;

	$emails->__set( 'from_name', $from_name );
	$emails->__set( 'from_email', $from_email );
	$emails->__set( 'heading', __( 'Offline Donation Instructions', 'walkthecounty' ) );

	$headers = apply_filters( 'walkthecounty_receipt_headers', $emails->get_headers(), $payment_id, $payment_data );
	$emails->__set( 'headers', $headers );

	$emails->send( $to_email, $subject, $message, $attachments );

}


/**
 * Send Offline Donation Admin Notice.
 *
 * Sends a notice to site admins about the pending donation.
 *
 * @since       1.0
 *
 * @param int $payment_id
 *
 * @return void
 *
 */
function walkthecounty_offline_send_admin_notice( $payment_id = 0 ) {

	/* Send an email notification to the admin */
	$admin_email = walkthecounty_get_admin_notice_emails();
	$user_info   = walkthecounty_get_payment_meta_user_info( $payment_id );

	if ( isset( $user_info['id'] ) && $user_info['id'] > 0 ) {
		$user_data = get_userdata( $user_info['id'] );
		$name      = $user_data->display_name;
	} elseif ( isset( $user_info['first_name'] ) && isset( $user_info['last_name'] ) ) {
		$name = $user_info['first_name'] . ' ' . $user_info['last_name'];
	} else {
		$name = $user_info['email'];
	}

	$amount = walkthecounty_donation_amount( $payment_id );

	$admin_subject = apply_filters( 'walkthecounty_offline_admin_donation_notification_subject', __( 'New Pending Donation', 'walkthecounty' ), $payment_id );

	$admin_message = __( 'Dear Admin,', 'walkthecounty' ) . "\n\n";
	$admin_message .= sprintf(__( 'A new offline donation has been made on your website for %s.', 'walkthecounty' ), $amount) . "\n\n";
	$admin_message .= __( 'The donation is in a pending status and is awaiting payment. Donation instructions have been emailed to the donor. Once you receive payment, be sure to mark the donation as complete using the link below.', 'walkthecounty' ) . "\n\n";


	$admin_message .= '<strong>' . __( 'Donor:', 'walkthecounty' ) . '</strong> {fullname}' . "\n";
	$admin_message .= '<strong>' . __( 'Amount:', 'walkthecounty' ) . '</strong> {amount}' . "\n\n";

	$admin_message .= sprintf(
		                  '<a href="%1$s">%2$s</a>',
		                  admin_url( 'edit.php?post_type=walkthecounty_forms&page=walkthecounty-payment-history&view=view-payment-details&id=' . $payment_id ),
		                  __( 'View Donation Details &raquo;', 'walkthecounty' )
	                  ) . "\n\n";

	$admin_message = apply_filters( 'walkthecounty_offline_admin_donation_notification', $admin_message, $payment_id );
	$admin_message = walkthecounty_do_email_tags( $admin_message, $payment_id );

	$attachments   = apply_filters( 'walkthecounty_offline_admin_donation_notification_attachments', array(), $payment_id );
	$admin_headers = apply_filters( 'walkthecounty_offline_admin_donation_notification_headers', array(), $payment_id );

	//Send Email
	$emails = WalkTheCounty()->emails;
	$emails->__set( 'heading', __( 'New Offline Donation', 'walkthecounty' ) );

	if ( ! empty( $admin_headers ) ) {
		$emails->__set( 'headers', $admin_headers );
	}

	$emails->send( $admin_email, $admin_subject, $admin_message, $attachments );

}


/**
 * Register gateway settings.
 *
 * @param $settings
 *
 * @return array
 */
function walkthecounty_offline_add_settings( $settings ) {

	// Bailout: Do not show offline gateways setting in to metabox if its disabled globally.
	if ( in_array( 'offline', (array) walkthecounty_get_option( 'gateways' ) ) ) {
		return $settings;
	}

	//Vars
	$prefix = '_walkthecounty_';

	$is_gateway_active = walkthecounty_is_gateway_active( 'offline' );

	//this gateway isn't active
	if ( ! $is_gateway_active ) {
		//return settings and bounce
		return $settings;
	}

	//Fields
	$check_settings = array(

		array(
			'name'    => __( 'Offline Donations', 'walkthecounty' ),
			'desc'    => __( 'Do you want to customize the donation instructions for this form?', 'walkthecounty' ),
			'id'      => $prefix . 'customize_offline_donations',
			'type'    => 'radio_inline',
			'default' => 'global',
			'options' => apply_filters( 'walkthecounty_forms_content_options_select', array(
					'global'   => __( 'Global Option', 'walkthecounty' ),
					'enabled'  => __( 'Customize', 'walkthecounty' ),
					'disabled' => __( 'Disable', 'walkthecounty' ),
				)
			),
		),
		array(
			'name'        => __( 'Billing Fields', 'walkthecounty' ),
			'desc'        => __( 'This option will enable the billing details section for this form\'s offline donation payment gateway. The fieldset will appear above the offline donation instructions.', 'walkthecounty' ),
			'id'          => $prefix . 'offline_donation_enable_billing_fields_single',
			'row_classes' => 'walkthecounty-subfield walkthecounty-hidden',
			'type'        => 'radio_inline',
			'default'     => 'disabled',
			'options'     => array(
				'enabled'  => __( 'Enabled', 'walkthecounty' ),
				'disabled' => __( 'Disabled', 'walkthecounty' ),
			),
		),
		array(
			'id'          => $prefix . 'offline_checkout_notes',
			'name'        => __( 'Donation Instructions', 'walkthecounty' ),
			'desc'        => __( 'Enter the instructions you want to display to the donor during the donation process. Most likely this would include important information like mailing address and who to make the check out to.', 'walkthecounty' ),
			'default'     => walkthecounty_get_default_offline_donation_content(),
			'type'        => 'wysiwyg',
			'row_classes' => 'walkthecounty-subfield walkthecounty-hidden',
			'options'     => array(
				'textarea_rows' => 6,
			)
		),
		array(
			'name'  => 'offline_docs',
			'type'  => 'docs_link',
			'url'   => 'http://docs.walkthecountywp.com/settings-gateway-offline-donations',
			'title' => __( 'Offline Donations', 'walkthecounty' ),
		),
	);

	return array_merge( $settings, $check_settings );
}

add_filter( 'walkthecounty_forms_offline_donations_metabox_fields', 'walkthecounty_offline_add_settings' );


/**
 * Offline Donation Content
 *
 * Get default offline donation text
 *
 * @return string
 */
function walkthecounty_get_default_offline_donation_content() {
	$default_text = '<p>' . __( 'In order to make an offline donation we ask that you please follow these instructions', 'walkthecounty' ) . ': </p>';
	$default_text .= '<ol>';
	$default_text .= '<li>';
	$default_text .= sprintf(
	/* translators: %s: site name */
		__( 'Make a check payable to "{sitename}"', 'walkthecounty' ) );
	$default_text .= '</li>';
	$default_text .= '<li>';
	$default_text .= sprintf(
	/* translators: %s: site name */
		__( 'On the memo line of the check, please indicate that the donation is for "{sitename}"', 'walkthecounty' ) );
	$default_text .= '</li>';
	$default_text .= '<li>' . __( 'Please mail your check to:', 'walkthecounty' ) . '</li>';
	$default_text .= '</ol>';
	$default_text .= '{offline_mailing_address}<br>';
	$default_text .= '<p>' . __( 'All contributions will be gratefully acknowledged and are tax deductible.', 'walkthecounty' ) . '</p>';

	return apply_filters( 'walkthecounty_default_offline_donation_content', $default_text );

}

/**
 * Offline Donation Email Content
 *
 * Gets the default offline donation email content
 *
 * @return string
 */
function walkthecounty_get_default_offline_donation_email_content() {
	$default_text = '<p>' . __( 'Dear {name},', 'walkthecounty' ) . '</p>';
	$default_text .= '<p>' . __( 'Thank you for your offline donation request! Your generosity is greatly appreciated. In order to make an offline donation we ask that you please follow these instructions:', 'walkthecounty' ) . '</p>';
	$default_text .= '<ol>';
	$default_text .= '<li>';
	$default_text .= sprintf(
	/* translators: %s: site name */
		__( 'Make a check payable to "{sitename}"', 'walkthecounty' )
	);
	$default_text .= '</li>';
	$default_text .= '<li>';
	$default_text .= sprintf(
		__( 'On the memo line of the check, please indicate that the donation is for "{sitename}"', 'walkthecounty' )
	);
	$default_text .= '</li>';
	$default_text .= '<li>' . __( 'Please mail your check to:', 'walkthecounty' ) . '</li>';
	$default_text .= '</ol>';
	$default_text .= '{offline_mailing_address}<br>';
	$default_text .= '<p>' . __( 'Once your donation has been received we will mark it as complete and you will receive an email receipt for your records. Please contact us with any questions you may have!', 'walkthecounty' ) . '</p>';
	$default_text .= '<p>' . __( 'Sincerely,', 'walkthecounty' ) . '</p>';
	$default_text .= '<p>{sitename}</p>';

	return apply_filters( 'walkthecounty_default_offline_donation_content', $default_text );

}

/**
 * Get offline payment instructions.
 *
 * @since 1.7
 *
 * @param int  $form_id
 * @param bool $wpautop
 *
 * @return string
 */
function walkthecounty_get_offline_payment_instruction( $form_id, $wpautop = false ) {
	// Bailout.
	if ( ! $form_id ) {
		return '';
	}

	$post_offline_customization_option = walkthecounty_get_meta( $form_id, '_walkthecounty_customize_offline_donations', true );
	$post_offline_instructions         = walkthecounty_get_meta( $form_id, '_walkthecounty_offline_checkout_notes', true );
	$global_offline_instruction        = walkthecounty_get_option( 'global_offline_donation_content' );
	$offline_instructions              = $global_offline_instruction;

	if ( walkthecounty_is_setting_enabled( $post_offline_customization_option ) ) {
		$offline_instructions = $post_offline_instructions;
	}

	$settings_url = admin_url( 'post.php?post=' . $form_id . '&action=edit&message=1' );

	/* translators: %s: form settings url */
	$offline_instructions = ! empty( $offline_instructions )
		? $offline_instructions
		: sprintf(
			__( 'Please enter offline donation instructions in <a href="%s">this form\'s settings</a>.', 'walkthecounty' ),
			$settings_url
		);

	$offline_instructions = walkthecounty_do_email_tags( $offline_instructions, null );

	$formmated_offline_instructions = $wpautop
		? wpautop( do_shortcode( $offline_instructions ) )
		: $offline_instructions;

	/**
	 * Filter the offline instruction content
	 *
	 * @since 2.2.0
	 *
	 */
	$formmated_offline_instructions = apply_filters(
		'walkthecounty_the_offline_instructions_content',
		$formmated_offline_instructions,
		$offline_instructions,
		$form_id,
		$wpautop
	);

	return $formmated_offline_instructions;
}


/**
 * Remove offline gateway from gateway list of offline disable for form.
 *
 * @since  1.8
 *
 * @param  array $gateway_list
 * @param        $form_id
 *
 * @return array
 */
function walkthecounty_filter_offline_gateway( $gateway_list, $form_id ) {
	if (
		// Show offline payment gateway if enable for new donation form.
		( false === strpos( $_SERVER['REQUEST_URI'], '/wp-admin/post-new.php?post_type=walkthecounty_forms' ) )
		&& $form_id
		&& ! walkthecounty_is_setting_enabled( walkthecounty_get_meta( $form_id, '_walkthecounty_customize_offline_donations', true, 'global' ), array( 'enabled', 'global' ) )
	) {
		unset( $gateway_list['offline'] );
	}

	// Output.
	return $gateway_list;
}

add_filter( 'walkthecounty_enabled_payment_gateways', 'walkthecounty_filter_offline_gateway', 10, 2 );

/**
 * Set default gateway to global default payment gateway
 * if current default gateways selected offline and offline payment gateway is disabled.
 *
 * @since 1.8
 *
 * @param  string $meta_key   Meta key.
 * @param  string $meta_value Meta value.
 * @param  int    $postid     Form ID.
 *
 * @return void
 */
function _walkthecounty_customize_offline_donations_on_save_callback( $meta_key, $meta_value, $postid ) {
	if (
		! walkthecounty_is_setting_enabled( $meta_value, array( 'global', 'enabled' ) )
		&& ( 'offline' === walkthecounty_get_meta( $postid, '_walkthecounty_default_gateway', true ) )
	) {
		walkthecounty_update_meta( $postid, '_walkthecounty_default_gateway', 'global' );
	}
}

add_filter( 'walkthecounty_save__walkthecounty_customize_offline_donations', '_walkthecounty_customize_offline_donations_on_save_callback', 10, 3 );
