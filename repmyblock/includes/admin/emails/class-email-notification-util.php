<?php
/**
 * Email Notification Util
 *
 * This class contains helper functions for  email notification.
 *
 * @package     WalkTheCounty
 * @subpackage  Classes/Emails
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.0
 */

// Exit if access directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WalkTheCounty_Email_Notification_Util {
	/**
	 * Instance.
	 *
	 * @since  2.0
	 * @access static
	 * @var
	 */
	static private $instance;

	/**
	 * Singleton pattern.
	 *
	 * @since  2.0
	 * @access private
	 * WalkTheCounty_Email_Notification_Util constructor.
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @since  2.0
	 * @access static
	 * @return static
	 */
	static function get_instance() {
		if ( null === static::$instance ) {
			self::$instance = new static();
		}

		return self::$instance;
	}


	/**
	 * Check if notification has preview field or not.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param WalkTheCounty_Email_Notification $email
	 *
	 * @return bool
	 */
	public static function has_preview( WalkTheCounty_Email_Notification $email ) {
		return $email->config['has_preview'];
	}

	/**
	 * Check if notification has recipient field or not.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param WalkTheCounty_Email_Notification $email
	 *
	 * @return bool
	 */
	public static function has_recipient_field( WalkTheCounty_Email_Notification $email ) {
		return $email->config['has_recipient_field'];
	}

	/**
	 * Check if admin can edit notification status or not.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param WalkTheCounty_Email_Notification $email
	 *
	 * @return bool
	 */
	public static function is_notification_status_editable( WalkTheCounty_Email_Notification $email ) {
		$user_can_edit = $email->config['notification_status_editable'];

		return (bool) $user_can_edit;
	}

	/**
	 * Check if admin can edit notification status or not.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param WalkTheCounty_Email_Notification $email
	 *
	 * @return bool
	 */
	public static function is_content_type_editable( WalkTheCounty_Email_Notification $email ) {
		return $email->config['content_type_editable'];
	}

	/**
	 * Check email preview header active or not.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param WalkTheCounty_Email_Notification $email
	 *
	 * @return bool
	 */
	public static function is_email_preview_has_header( WalkTheCounty_Email_Notification $email ) {
		return $email->config['has_preview_header'];
	}

	/**
	 * Check email preview header active or not.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param WalkTheCounty_Email_Notification $email
	 *
	 * @return bool
	 */
	public static function is_email_preview( WalkTheCounty_Email_Notification $email ) {
		return $email->config['has_preview'];
	}

	/**
	 * Check if email notification setting appear on emails setting page or not.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param WalkTheCounty_Email_Notification $email
	 *
	 * @return bool
	 */
	public static function is_show_on_emails_setting_page( WalkTheCounty_Email_Notification $email ){
		return $email->config['show_on_emails_setting_page'];
	}

	/**
	 * Check if we can use form email options.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param WalkTheCounty_Email_Notification $email
	 * @param int $form_id
	 *
	 * @return bool
	 */
	public static function can_use_form_email_options( WalkTheCounty_Email_Notification $email, $form_id = null ){
		return walkthecounty_is_setting_enabled( walkthecounty_get_meta( $form_id, '_walkthecounty_email_options', true ) );
	}

	/**
	 * Check email active or not.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param WalkTheCounty_Email_Notification $email
	 * @param int $form_id
	 *
	 * @return string
	 */
	public static function is_email_notification_active( WalkTheCounty_Email_Notification $email, $form_id = null ) {
		$notification_status = $email->get_notification_status( $form_id );


		$notification_status = empty( $form_id )
			? walkthecounty_is_setting_enabled( $notification_status )
			: walkthecounty_is_setting_enabled( walkthecounty_get_option( "{$email->config['id']}_notification", $email->config['notification_status'] ) ) && walkthecounty_is_setting_enabled( $notification_status, array( 'enabled', 'global' ) );
			// To check if email notification is active or not on a per-form basis, email notification must be globally active—otherwise it will be considered disabled.

		/**
		 * Filter to modify is email active notification
		 *
		 * @since 2.1.3
		 *
		 * @param bool $notification_status True if notification is enable and false when disable
		 * @param WalkTheCounty_Email_Notification $email Class instances WalkTheCounty_Email_Notification.
		 * @param int                     $form_id Donation Form ID.
		 *
		 * @param bool $notification_status True if notification is enable and false when disable
		 */
		return apply_filters( "walkthecounty_{$email->config['id']}_is_email_notification_active", $notification_status, $email, $form_id );
	}

	/**
	 * Check if admin preview email or not
	 *
	 * @since  2.0
	 * @access public
	 * @return bool   $is_preview
	 */
	public static function can_preview_email() {
		$is_preview = false;

		if (
			current_user_can( 'manage_walkthecounty_settings' )
			&& ! empty( $_GET['walkthecounty_action'] )
			&& 'preview_email' === $_GET['walkthecounty_action']
		) {
			$is_preview = true;
		}

		return $is_preview;
	}

	/**
	 * Check if admin preview email or not
	 *
	 * @since  2.0
	 * @access public
	 * @return bool   $is_preview
	 */
	public static function can_send_preview_email() {
		$is_preview = false;

		if (
			current_user_can( 'manage_walkthecounty_settings' )
			&& ! empty( $_GET['walkthecounty_action'] )
			&& 'send_preview_email' === $_GET['walkthecounty_action']
		) {
			$is_preview = true;
		}

		return $is_preview;
	}


	/**
	 * Get formatted text for email content type.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param string $content_type
	 *
	 * @return string
	 */
	public static function get_formatted_email_type( $content_type ) {
		$email_contents = array(
			'text/html'  => __( 'HTML', 'walkthecounty' ),
			'text/plain' => __( 'Plain', 'walkthecounty' ),
		);

		return $email_contents[ $content_type ];
	}


	/**
	 * Get email notification option value.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param WalkTheCounty_Email_Notification $email
	 * @param string                   $option_name
	 * @param int                      $form_id
	 * @param mixed                    $default
	 *
	 * @return mixed
	 */
	public static function get_value( WalkTheCounty_Email_Notification $email, $option_name, $form_id = null, $default = false ) {
		// If form id set then option name can be contain _walkthecounty_ prefix which is only used for meta key,
		// So make sure you are using correct option name.
		$global_option_name = ( 0 === strpos( $option_name, '_walkthecounty_' )
			? str_replace( '_walkthecounty_', '', $option_name )
			: $option_name );
		$option_value = walkthecounty_get_option( $global_option_name, $default );

		if (
			! empty( $form_id )
			&& walkthecounty_is_setting_enabled(
				walkthecounty_get_meta(
					$form_id,
					WalkTheCounty_Email_Setting_Field::get_prefix( $email, $form_id ) . 'notification',
					true,
					'global'
				)
			)
		) {
			$option_value = get_post_meta( $form_id, $option_name, true );

			// Get only email field value from recipients setting.
			if( WalkTheCounty_Email_Setting_Field::get_prefix( $email, $form_id ) . 'recipient' === $option_name ) {
				$option_value = wp_list_pluck( $option_value, 'email' );
			}
		}

		$option_value = empty( $option_value ) ? $default : $option_value;

		/**
		 * Filter the setting value
		 *
		 * @since 2.0
		 */
		return apply_filters( 'walkthecounty_email_setting_value', $option_value, $option_name, $email, $form_id, $default );
	}


	/**
	 * Get email logo.
	 *
	 * @since  2.1.5
	 *
	 * @access public
	 *
	 * @param integer $form_id FOrm ID.
	 *
	 * @return string
	 */
	public static function get_email_logo( $form_id ) {

		// Email logo tag.
		$header_img = $form_id && walkthecounty_is_setting_enabled( walkthecounty_get_meta( $form_id, '_walkthecounty_email_options', true ) )
			? walkthecounty_get_meta( $form_id, '_walkthecounty_email_logo', true )
			: walkthecounty_get_option( 'email_logo', '' );

		return $header_img;
	}
}
