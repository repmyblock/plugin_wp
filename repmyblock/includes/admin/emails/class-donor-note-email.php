<?php
/**
 * Donor Note Email
 *
 *
 * @package     WalkTheCounty
 * @subpackage  Classes/Emails
 * @copyright   Copyright (c) 2018, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.3.0
 */

// Exit if access directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WalkTheCounty_Donor_Note_Email' ) ) :

	/**
	 * WalkTheCounty_Donor_Note_Email
	 *
	 * @abstract
	 * @since       2.3.0
	 */
	class WalkTheCounty_Donor_Note_Email extends WalkTheCounty_Email_Notification {
		/* @var WalkTheCounty_Payment $payment */
		public $payment;

		/**
		 * Create a class instance.
		 *
		 * @access  public
		 * @since   2.3.0
		 */
		public function init() {
			// Initialize empty payment.
			$this->payment = new WalkTheCounty_Payment( 0 );

			$this->load( array(
				'id'                    => 'donor-note',
				'label'                 => __( 'Donation Note', 'walkthecounty' ),
				'description'           => __( 'Sent when a donation note is added to a donation payment.', 'walkthecounty' ),
				'notification_status'   => 'enabled',
				'recipient_group_name'  => __( 'Donor', 'walkthecounty' ),
				'default_email_subject' => sprintf(
					esc_attr__( 'Note added to your %s donation from %s', 'walkthecounty' ),
					'{donation}',
					'{date}'
				),
				'default_email_message' => sprintf(
					"Dear %s,\n\nA note has just been added to your donation:\n\n%s\n\nFor your reference, you may view your donation details by clicking the link below:\n%s\n\nThank you,\n%s",
					'{name}',
					'{donor_note}',
					'{receipt_link}',
					'{sitename}'
				),
				'default_email_header'  => __( 'New Donation Note Added', 'walkthecounty' ),
				'form_metabox_setting'  => false,
			) );

			add_action( "walkthecounty_{$this->config['id']}_email_notification", array( $this, 'send_note' ), 10, 2 );
		}

		/**
		 * Send donor note
		 *
		 * @since  2.3.0
		 * @access public
		 *
		 * @param int $donation_id Donation ID.
		 * @param int $note_id     Donor comment.
		 */
		public function send_note( $note_id, $donation_id ) {
			$this->recipient_email = walkthecounty_get_donation_donor_email( $donation_id );

			// Send email.
			$this->send_email_notification( array(
				'payment_id' => $donation_id,
				'note_id'    => $note_id,
			) );
		}
	}

endif; // End class_exists check

return WalkTheCounty_Donor_Note_Email::get_instance();
