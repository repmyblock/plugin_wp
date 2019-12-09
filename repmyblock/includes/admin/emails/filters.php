<?php
/**
 * Filter for Email Notification
 *
 * @package     WalkTheCounty
 * @subpackage  Classes/Emails
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.0
 */

/**
 * Add extra row actions to email notification table.
 *
 * @since 2.0
 *
 * @param array                   $row_actions
 * @param WalkTheCounty_Email_Notification $email
 *
 * @return array
 */
function walkthecounty_email_notification_row_actions_callback( $row_actions, $email ) {
	if( WalkTheCounty_Email_Notification_Util::is_email_preview( $email ) ) {
		$preview_link = sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			wp_nonce_url(
				add_query_arg(
					array( 'walkthecounty_action' => 'preview_email', 'email_type' => $email->config['id'] ),
					home_url()
				), 'walkthecounty-preview-email'
			),
			__( 'Preview', 'walkthecounty' )
		);

		$send_preview_email_link = sprintf(
			'<a href="%1$s">%2$s</a>',
			wp_nonce_url(
				add_query_arg( array(
					'walkthecounty_action'  => 'send_preview_email',
					'email_type' => $email->config['id'],
					'walkthecounty-messages[]' => 'sent-test-email',
				) ), 'walkthecounty-send-preview-email' ),
			__( 'Send test email', 'walkthecounty' )
		);

		$row_actions['email_preview'] = $preview_link;
		$row_actions['send_preview_email'] = $send_preview_email_link;
	}

	return $row_actions;
}
add_filter( 'walkthecounty_email_notification_row_actions', 'walkthecounty_email_notification_row_actions_callback', 10, 2 );

/**
 * This help to decode all email template tags.
 *
 * @since 2.0
 *
 * @param string      $message
 * @param WalkTheCounty_Emails $email_obj
 *
 * @return string
 */
function walkthecounty_decode_email_tags( $message, $email_obj ) {
	if ( ! empty( $email_obj->tag_args ) ) {
		$message = walkthecounty_do_email_tags( $message, $email_obj->tag_args );
	}

	return $message;
}

add_filter( 'walkthecounty_email_message', 'walkthecounty_decode_email_tags', 10, 2 );
