<?php
/**
 * Donation Receipt Email
 *
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

if ( ! class_exists( 'WalkTheCounty_Donation_Receipt_Email' ) ) :

	/**
	 * WalkTheCounty_Donation_Receipt_Email
	 *
	 * @abstract
	 * @since       2.0
	 */
	class WalkTheCounty_Donation_Receipt_Email extends WalkTheCounty_Email_Notification {
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
				'id'                   => 'donation-receipt',
				'label'                => __( 'Donation Receipt', 'walkthecounty' ),
				'description'          => __( 'Sent to the donor when their donation completes or a pending donation is marked as complete.', 'walkthecounty' ),
				'notification_status'  => 'enabled',
				'form_metabox_setting' => true,
				'recipient_group_name' => __( 'Donor', 'walkthecounty' ),
				'default_email_subject' => esc_attr__( 'Donation Receipt', 'walkthecounty' ),
				'default_email_message' => walkthecounty_get_default_donation_receipt_email(),
				'default_email_header'  => __( 'Donation Receipt', 'walkthecounty' ),
			) );

			add_action( "walkthecounty_{$this->config['id']}_email_notification", array( $this, 'send_donation_receipt' ) );
			add_action( 'walkthecounty_email_links', array( $this, 'resend_donation_receipt' ) );
		}

		/**
		 * Get email subject.
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @param int $form_id
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
			 * Filters the donation email receipt subject.
			 * Note: This filter will deprecate soon.
			 *
			 * @since 1.0
			 */
			$subject = apply_filters(
				'walkthecounty_donation_subject',
				$subject,
				$this->payment->ID
			);

			/**
			 * Filters the donation email receipt subject.
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
		 * Get email message.
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @param int $form_id
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
			 * Filter message on basis of email template
			 * Note: This filter will deprecate soon.
			 *
			 * @since 1.0
			 */
			$message = apply_filters(
				'walkthecounty_donation_receipt_' . WalkTheCounty()->emails->get_template(),
				$message,
				$this->payment->ID,
				$this->payment->payment_meta
			);

			/**
			 * Filter the message
			 * Note: This filter will deprecate soon.
			 *
			 * @since 1.0
			 */
			$message = apply_filters(
				'walkthecounty_donation_receipt',
				$message,
				$this->payment->ID,
				$this->payment->payment_meta
			);

			/**
			 * Filter the message
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
		 * Get the recipient attachments.
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @param int $form_id
		 * @return array
		 */
		public function get_email_attachments( $form_id = null) {
			/**
			 * Filter the attachments.
			 * Note: this filter will deprecate soon.
			 *
			 * @since 1.0
			 */
			$attachments = apply_filters(
				'walkthecounty_receipt_attachments',
				array(),
				$this->payment->ID,
				$this->payment->payment_meta
			);

			/**
			 * Filter the attachments.
			 *
			 * @since 2.0
			 */
			$attachments = apply_filters(
				"walkthecounty_{$this->config['id']}_get_email_attachments",
				$attachments,
				$this,
				$form_id
			);

			return $attachments;
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
			 * @param int   $payment_id   Payment id.
			 * @param mixed $payment_data Payment meta data.
			 *
			 * @since 1.0
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
			 * @param int   $payment_id   Payment id.
			 * @param mixed $payment_data Payment meta data.
			 *
			 * @since 1.0
			 */
			$from_email = apply_filters(
				'walkthecounty_donation_from_address',
				WalkTheCounty()->emails->get_from_address(),
				$this->payment->ID,
				$this->payment->payment_meta
			);

			WalkTheCounty()->emails->__set( 'from_name', $from_name );
			WalkTheCounty()->emails->__set( 'from_email', $from_email );

			/**
			 * Filters the donation receipt's email headers.
			 *
			 * @param int   $payment_id   Payment id.
			 * @param mixed $payment_data Payment meta data.
			 *
			 * @since 1.0
			 */
			$headers = apply_filters(
				'walkthecounty_receipt_headers',
				WalkTheCounty()->emails->get_headers(),
				$this->payment->ID,
				$this->payment->payment_meta
			);

			WalkTheCounty()->emails->__set( 'headers', $headers );
		}

		/**
		 * Send donation receipt
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @param $payment_id
		 */
		public function send_donation_receipt( $payment_id ) {
			$this->payment = new WalkTheCounty_Payment( $payment_id );

			// Setup email data.
			$this->setup_email_data();

			// Send email.
			$this->send_email_notification( array(
				'payment_id' => $this->payment->ID,
			) );
		}

		/**
		 * Resend payment receipt by row action.
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @param array $data Donation details.
		 */
		public function resend_donation_receipt( $data ) {
			$purchase_id = absint( $data['purchase_id'] );

			if ( empty( $purchase_id ) ) {
				return;
			}

			// Get donation payment information.
			$this->payment = new WalkTheCounty_Payment( $purchase_id );

			if ( ! current_user_can( 'edit_walkthecounty_payments', $this->payment->ID ) ) {
				wp_die( esc_html__( 'You do not have permission to edit donations.', 'walkthecounty' ), esc_html__( 'Error', 'walkthecounty' ), array(
					'response' => 403,
				) );
			}

			// Setup email data.
			$this->setup_email_data();

			// Send email.
			$this->send_email_notification( array(
				'payment_id' => $this->payment->ID,
			) );

			wp_redirect( add_query_arg( array(
				'walkthecounty-messages[]' => 'email-sent',
				'walkthecounty-action'     => false,
				'purchase_id'     => false,
			) ) );
			exit;
		}
	}

endif; // End class_exists check

return WalkTheCounty_Donation_Receipt_Email::get_instance();
