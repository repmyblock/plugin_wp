<?php
/**
 * Offline Donation Instruction Email
 *
 * This class handles all email notification settings.
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

if ( ! class_exists( 'WalkTheCounty_Offline_Donation_Instruction_Email' ) ) :

	/**
	 * WalkTheCounty_Offline_Donation_Instruction_Email
	 *
	 * @abstract
	 * @since       2.0
	 */
	class WalkTheCounty_Offline_Donation_Instruction_Email extends WalkTheCounty_Email_Notification {
		/* @var WalkTheCounty_Payment $payment */
		public $payment;

		/**
		 * Create a class instance.
		 *
		 * @access  public
		 * @since   2.0
		 */
		public function init() {
			// Initialize empty payment.
			$this->payment = new WalkTheCounty_Payment( 0 );

			$this->load( array(
				'id'                           => 'offline-donation-instruction',
				'label'                        => __( 'Offline Donation Instructions', 'walkthecounty' ),
				'description'                  => __( 'Sent to the donor when they submit an offline donation.', 'walkthecounty' ),
				'notification_status'          => walkthecounty_is_gateway_active( 'offline' ) ? 'enabled' : 'disabled',
				'form_metabox_setting'         => true,
				'notification_status_editable' => false,
				'preview_email_tag_values'     => array(
					'payment_method' => esc_html__( 'Offline', 'walkthecounty' ),
				),
				'default_email_subject'        => esc_attr__( '{donation} - Offline Donation Instructions', 'walkthecounty' ),
				'default_email_message'        => walkthecounty_get_default_offline_donation_email_content(),
				'default_email_header'         => __( 'Offline Donation Instructions', 'walkthecounty' ),
				'notices' => array(
					'non-notification-status-editable' => sprintf(
						'%1$s <a href="%2$s">%3$s &raquo;</a>',
						__( 'This notification is automatically toggled based on whether the gateway is enabled or not.', 'walkthecounty' ),
						esc_url( admin_url('edit.php?post_type=walkthecounty_forms&page=walkthecounty-settings&tab=gateways&section=offline-donations') ),
						__( 'Edit Setting', 'walkthecounty' )
					)
				),
			) );

			add_action( 'walkthecounty_insert_payment', array( $this, 'setup_email_notification' ) );
			add_action( 'walkthecounty_save_settings_walkthecounty_settings', array( $this, 'set_notification_status' ), 10, 2 );
		}

		/**
		 * Get email message
		 *
		 * @since 2.0
		 *
		 * @param int $form_id
		 *
		 * @return string
		 */
		public function get_email_message( $form_id = null ) {
			$message = WalkTheCounty_Email_Notification_Util::get_value(
				$this,
				WalkTheCounty_Email_Setting_Field::get_prefix( $this, $form_id ) . 'email_message',
				$form_id,
				$this->config['default_email_message']
			);

			/**
			 * Filter the email message.
			 *
			 * @since 2.0
			 */
			$message = apply_filters(
				"walkthecounty_{$this->config['id']}_get_email_message",
				$message,
				$this,
				$form_id
			);

			return $message;
		}

		/**
		 * Get email message
		 *
		 * @since 2.0
		 *
		 * @param int $form_id
		 *
		 * @return string
		 */
		public function get_email_subject( $form_id = null ) {
			$subject = wp_strip_all_tags(
				WalkTheCounty_Email_Notification_Util::get_value(
					$this,
					WalkTheCounty_Email_Setting_Field::get_prefix( $this, $form_id ) . 'email_subject',
					$form_id,
					$this->config['default_email_subject']
				)
			);

			/**
			 * Filter the email subject.
			 *
			 * @since 2.0
			 */
			$subject = apply_filters(
				"walkthecounty_{$this->config['id']}_get_email_subject",
				$subject,
				$this,
				$form_id
			);

			return $subject;
		}

		/**
		 * Get attachments.
		 *
		 * @since 2.0
		 *
		 * @param int $form_id
		 * @return array
		 */
		public function get_email_attachments( $form_id = null ) {
			/**
			 * Filter the attachments.
			 * Note: This filter will deprecate soon.
			 *
			 * @since 1.0
			 */
			$attachment = apply_filters(
				'walkthecounty_offline_donation_attachments',
				array(),
				$this->payment->ID,
				$this->payment->payment_meta
			);

			/**
			 * Filter the email attachment.
			 *
			 * @since 2.0
			 */
			$attachment = apply_filters(
				"walkthecounty_{$this->config['id']}_get_email_attachment",
				$attachment,
				$this,
				$form_id
			);

			return $attachment;
		}


		/**
		 * Set email data.
		 *
		 * @since 2.0
		 */
		public function setup_email_data() {
			// Set recipient email.
			$this->recipient_email = $this->payment->email;

			/**
			 * Filters the from name.
			 *
			 * @since 1.7
			 */
			$from_name = apply_filters(
				'walkthecounty_donation_from_name',
				WalkTheCounty()->emails->get_from_name(),
				$this->payment->ID,
				$this->payment->payment_meta
			);

			/**
			 * Filters the from email.
			 *
			 * @since 1.7
			 */
			$from_email = apply_filters(
				'walkthecounty_donation_from_address',
				WalkTheCounty()->emails->get_from_address(),
				$this->payment->ID,
				$this->payment->payment_meta
			);

			WalkTheCounty()->emails->__set( 'from_name', $from_name );
			WalkTheCounty()->emails->__set( 'from_email', $from_email );
			WalkTheCounty()->emails->__set( 'headers', apply_filters( 'walkthecounty_receipt_headers', WalkTheCounty()->emails->get_headers(), $this->payment->ID, $this->payment->payment_meta ) );
		}

		/**
		 * Setup email notification.
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @param int $payment_id
		 */
		public function setup_email_notification( $payment_id ) {
			$this->payment = new WalkTheCounty_Payment( $payment_id );

			// Exit if not donation was not with offline donation.
			if ( 'offline' !== $this->payment->gateway ) {
				return;
			}

			// Set email data.
			$this->setup_email_data();

			// Send email.
			$this->send_email_notification( array(
				'payment_id' => $this->payment->ID,
			) );
		}

		/**
		 * Set notification status
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @param $update_options
		 * @param $option_name
		 */
		public function set_notification_status( $update_options, $option_name ) {
			// Get updated settings.
			$update_options = walkthecounty_get_settings();

			$notification_status = isset( $update_options['gateways']['offline'] ) ? 'enabled' : 'disabled';

			if (
				empty( $update_options["{$this->config['id']}_notification"] )
				|| $notification_status !== $update_options["{$this->config['id']}_notification"]
			) {
				$update_options["{$this->config['id']}_notification"] = $notification_status;
				update_option( $option_name, $update_options, false );
			}
		}


		/**
		 * Register email settings to form metabox.
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @param array $settings
		 * @param int   $form_id
		 *
		 * @return array
		 */
		public function add_metabox_setting_field( $settings, $form_id ) {
			if ( in_array( 'offline', array_keys( walkthecounty_get_enabled_payment_gateways($form_id) ) ) ) {
				$settings[] = array(
					'id'     => $this->config['id'],
					'title'  => $this->config['label'],
					'fields' => $this->get_setting_fields( $form_id ),
				);
			}

			return $settings;
		}
	}

endif; // End class_exists check

return WalkTheCounty_Offline_Donation_Instruction_Email::get_instance();
