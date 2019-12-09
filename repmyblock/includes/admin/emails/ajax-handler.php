<?php
/**
 * This file contain code to handle email notification setting ajax.
 *
 * Register settings Include and setup custom metaboxes and fields.
 *
 * @package    WalkTheCounty
 * @subpackage Classes/Emails
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 * @link       https://github.com/webdevstudios/Custom-Metaboxes-and-Fields-for-WordPress
 */

/**
 * Enabled & disable notification
 *
 * @since 2.0
 */
function walkthecounty_set_notification_status_handler() {
	// Is user have permission to edit walkthecounty setting.
	if ( ! current_user_can( 'manage_walkthecounty_settings' ) ) {
		return;
	}

	$notification_id = isset( $_POST['notification_id'] ) ? walkthecounty_clean( $_POST['notification_id'] ) : '';
	if ( ! empty( $notification_id ) && walkthecounty_update_option( "{$notification_id}_notification", walkthecounty_clean( $_POST['status'] ) ) ) {
		wp_send_json_success();
	}

	wp_send_json_error();
}

add_action( 'wp_ajax_walkthecounty_set_notification_status', 'walkthecounty_set_notification_status_handler' );
