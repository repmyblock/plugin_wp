<?php
/**
 * Email access notification
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

if ( ! class_exists( 'WalkTheCounty_Email_Access_Email' ) ) :

	/**
	 * WalkTheCounty_Email_Access_Email
	 *
	 * @abstract
	 * @since 2.0
	 */
	class WalkTheCounty_Email_Access_Email extends WalkTheCounty_Email_Notification {
		/**
		 * Create a class instance.
		 *
		 * @access public
		 * @since  2.0
		 */
		public function init() {
			$this->load( array(
				'id'                           => 'email-access',
				'label'                        => __( 'Email access', 'walkthecounty' ),
				'description'                  => __( 'Sent when donors request access to their donation history using only their email as verification. (See Settings > General > Access Control)', 'walkthecounty' ),
				'notification_status'          => walkthecounty_get_option( 'email_access', 'disabled' ),
				'form_metabox_setting'         => false,
				'notification_status_editable' => false,
				'email_tag_context'            => 'donor',
				'recipient_group_name'         => __( 'Donor', 'walkthecounty' ),
				'default_email_subject'        => sprintf( __( 'Please confirm your email for %s', 'walkthecounty' ), get_bloginfo( 'url' ) ),
				'default_email_message'        => $this->get_default_email_message(),
				'default_email_header'         => __( 'Confirm Email', 'walkthecounty' ),
				'notices' => array(
					'non-notification-status-editable' => sprintf(
						'%1$s <a href="%2$s">%3$s &raquo;</a>',
						__( 'This notification is automatically toggled based on whether the email access is enabled or not.', 'walkthecounty' ),
						esc_url( admin_url('edit.php?post_type=walkthecounty_forms&page=walkthecounty-settings&tab=general&section=access-control') ),
						__( 'Edit Setting', 'walkthecounty' )
					)
				),
			) );

			add_filter( "walkthecounty_{$this->config['id']}_email_notification", array( $this, 'setup_email_notification' ), 10, 2 );
			add_action( 'walkthecounty_save_settings_walkthecounty_settings', array( $this, 'set_notification_status' ), 10, 2 );
			add_filter( 'walkthecounty_email_preview_header', array( $this, 'email_preview_header' ), 10, 2 );
		}

		/**
		 * Get email subject.
		 *
		 * @since  2.0
		 * @access public
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
			 * Filters the donation notification subject.
			 * Note: This filter will deprecate soon.
			 *
			 * @since 1.0
			 */
			$subject = apply_filters( 'walkthecounty_email_access_token_subject', $subject );

			/**
			 * Filters the donation notification subject.
			 *
			 * @since 2.0
			 */
			$subject = apply_filters( "walkthecounty_{$this->config['id']}_get_email_subject", $subject, $this, $form_id );

			return $subject;
		}


		/**
		 * Get email attachment.
		 *
		 * @since  2.0
		 * @access public
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
			 * Filter the email message
			 * Note: This filter will deprecate soon.
			 *
			 * @since 1.0
			 */
			$message = apply_filters( 'walkthecounty_email_access_token_message', $message );

			/**
			 * Filter the email message
			 *
			 * @since 2.0
			 */
			$message = apply_filters( "walkthecounty_{$this->config['id']}_get_default_email_message", $message, $this, $form_id );

			return $message;
		}


		/**
		 * Get email attachment.
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @param int $form_id
		 * @return array
		 */
		public function get_email_attachments( $form_id = null ) {
			/**
			 * Filters the donation notification email attachments.
			 * By default, there is no attachment but plugins can hook in to provide one more multiple.
			 * Note: This filter will deprecate soon.
			 *
			 * @since 1.0
			 */
			$attachments = apply_filters( 'walkthecounty_admin_donation_notification_attachments', array() );

			/**
			 * Filters the donation notification email attachments.
			 * By default, there is no attachment but plugins can hook in to provide one more multiple.
			 *
			 * @since 2.0
			 */
			$attachments = apply_filters( "walkthecounty_{$this->config['id']}_get_email_attachments", $attachments, $this, $form_id );

			return $attachments;
		}


		/**
		 * Get default email message.
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @return string
		 */
		public function get_default_email_message() {
			$message = __( 'Please click the link to access your donation history on {site_url}. If you did not request this email, please contact {admin_email}.', 'walkthecounty' ) . "\n\n";
			$message .= '{email_access_link}' . "\n\n";
			$message .= "\n\n";
			$message .= __( 'Sincerely,', 'walkthecounty' ) . "\n";
			$message .= get_bloginfo( 'name' ) . "\n";

			/**
			 * Filter the new donation email message
			 *
			 * @since 2.0
			 *
			 * @param string $message
			 */
			return apply_filters( "walkthecounty_{$this->config['id']}_get_default_email_message", $message, $this );
		}


		/**
		 * Get email header
		 *
		 * @since 2.2.1
		 * @access public
		 *
		 * @param null $form_id
		 *
		 * @return string
		 */
		public function get_email_header( $form_id = null ) {
			$subject = parent::get_email_header( $form_id );

			/**
			 * Filter the email header
			 *
			 * @since 1.0
			 */
			$subject  =  apply_filters( 'walkthecounty_email_access_token_heading', $subject );

			return  $subject;
		}


		/**
		 * Set email data
		 *
		 * @since 2.0
		 */
		public function setup_email_data() {
			/**
			 * Filters the from name.
			 * Note: This filter will deprecate soon.
			 *
			 * @since 1.0
			 */
			$from_name = apply_filters( 'walkthecounty_donation_from_name', WalkTheCounty()->emails->get_from_name() );

			/**
			 * Filters the from email.
			 * Note: This filter will deprecate soon.
			 *
			 * @since 1.0
			 */
			$from_email = apply_filters( 'walkthecounty_donation_from_address', WalkTheCounty()->emails->get_from_address() );

			WalkTheCounty()->emails->__set( 'from_name', $from_name );
			WalkTheCounty()->emails->__set( 'from_email', $from_email );

			/**
			 * Filters the donation notification email headers.
			 *
			 * @since 1.0
			 */
			$headers = apply_filters( 'walkthecounty_admin_donation_notification_headers', WalkTheCounty()->emails->get_headers() );

			WalkTheCounty()->emails->__set( 'headers', $headers );
		}

		/**
		 * Setup email notification.
		 *
		 * @param int    $donor_id Donor ID.
		 * @param string $email    Donor Email.
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @return bool
		 */
		public function setup_email_notification( $donor_id, $email ) {
			$donor = WalkTheCounty()->donors->get_donor_by( 'email', $email );
			$this->recipient_email = $email;

			// Set email data.
			$this->setup_email_data();

			// Send email.
			return $this->send_email_notification(
				array(
					'donor_id' => $donor_id,
					'user_id'  => $donor->user_id
				)
			);
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

			if (
				! empty( $update_options['email_access'] )
				&& ! empty( $update_options[ "{$this->config['id']}_notification" ] )
				&& $update_options['email_access'] !== $update_options[ "{$this->config['id']}_notification" ]
			) {
				$update_options[ "{$this->config['id']}_notification" ] = $update_options['email_access'];
				update_option( $option_name, $update_options, false );
			}
		}


		/**
		 * email preview header.
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @param string                  $email_preview_header
		 * @param WalkTheCounty_Email_Access_Email $email
		 * @return string
		 */
		public function email_preview_header( $email_preview_header, $email ) {
			if( $this->config['id'] === $email->config['id'] ) {
				$email_preview_header = '';
			}

			return $email_preview_header;
		}
	}

endif; // End class_exists check

return WalkTheCounty_Email_Access_Email::get_instance();
