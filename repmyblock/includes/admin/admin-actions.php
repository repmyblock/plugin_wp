<?php
/**
 * Admin Actions
 *
 * @package     WalkTheCounty
 * @subpackage  Admin/Actions
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load wp editor by ajax.
 *
 * @since 1.8
 */
function walkthecounty_load_wp_editor() {
	if ( ! isset( $_POST['wp_editor'] ) || ! current_user_can( 'edit_walkthecounty_forms' ) ) {
		die();
	}

	$wp_editor                     = json_decode( base64_decode( $_POST['wp_editor'] ), true );
	$wp_editor[2]['textarea_name'] = $_POST['textarea_name'];

	wp_editor( $wp_editor[0], $_POST['wp_editor_id'], $wp_editor[2] );

	die();
}

add_action( 'wp_ajax_walkthecounty_load_wp_editor', 'walkthecounty_load_wp_editor' );


/**
 * Redirect admin to clean url walkthecounty admin pages.
 *
 * @return bool
 * @since 1.8
 *
 */
function walkthecounty_redirect_to_clean_url_admin_pages() {
	// WalkTheCounty admin pages.
	$walkthecounty_pages = array(
		'walkthecounty-payment-history',
		'walkthecounty-donors',
		'walkthecounty-reports',
		'walkthecounty-tools',
	);

	// Get current page.
	$current_page = isset( $_GET['page'] ) ? esc_attr( $_GET['page'] ) : '';

	// Bailout.
	if (
		empty( $current_page )
		|| empty( $_GET['_wp_http_referer'] )
		|| ! in_array( $current_page, $walkthecounty_pages )
	) {
		return false;
	}

	/**
	 * Verify current page request.
	 *
	 * @since 1.8
	 */
	$redirect = apply_filters( "walkthecounty_validate_{$current_page}", true );

	if ( $redirect ) {
		// Redirect.
		wp_redirect(
			remove_query_arg(
				array( '_wp_http_referer', '_wpnonce' ),
				wp_unslash( $_SERVER['REQUEST_URI'] )
			)
		);
		exit;
	}
}

add_action( 'admin_init', 'walkthecounty_redirect_to_clean_url_admin_pages' );


/**
 * Hide Outdated PHP Notice Shortly.
 *
 * This code is used with AJAX call to hide outdated PHP notice for a short period of time
 *
 * @return void
 * @since 1.8.9
 *
 */
function walkthecounty_hide_outdated_php_notice() {

	if ( ! isset( $_POST['_walkthecounty_hide_outdated_php_notices_shortly'] ) || ! current_user_can( 'manage_walkthecounty_settings' ) ) {
		walkthecounty_die();
	}

	// Transient key name.
	$transient_key = '_walkthecounty_hide_outdated_php_notices_shortly';

	if ( WalkTheCounty_Cache::get( $transient_key, true ) ) {
		return;
	}

	// Hide notice for 24 hours.
	WalkTheCounty_Cache::set( $transient_key, true, DAY_IN_SECONDS, true );

	walkthecounty_die();

}

add_action( 'wp_ajax_walkthecounty_hide_outdated_php_notice', 'walkthecounty_hide_outdated_php_notice' );

/**
 * Register admin notices.
 *
 * @since 1.8.9
 */
function _walkthecounty_register_admin_notices() {
	// Bailout.
	if ( ! is_admin() ) {
		return;
	}

	// Bulk action notices.
	if (
		isset( $_GET['action'] ) &&
		! empty( $_GET['action'] )
	) {

		// Add payment bulk notice.
		if (
			current_user_can( 'edit_walkthecounty_payments' ) &&
			isset( $_GET['payment'] ) &&
			! empty( $_GET['payment'] )
		) {
			$payment_count = isset( $_GET['payment'] ) ? count( $_GET['payment'] ) : 0;

			switch ( $_GET['action'] ) {
				case 'delete':
					WalkTheCounty()->notices->register_notice(
						array(
							'id'          => 'bulk_action_delete',
							'type'        => 'updated',
							'description' => sprintf(
								_n(
									'Successfully deleted one donation.',
									'Successfully deleted %d donations.',
									$payment_count,
									'walkthecounty'
								),
								$payment_count
							),
							'show'        => true,
						)
					);

					break;

				case 'resend-receipt':
					WalkTheCounty()->notices->register_notice(
						array(
							'id'          => 'bulk_action_resend_receipt',
							'type'        => 'updated',
							'description' => sprintf(
								_n(
									'Successfully sent email receipt to one recipient.',
									'Successfully sent email receipts to %d recipients.',
									$payment_count,
									'walkthecounty'
								),
								$payment_count
							),
							'show'        => true,
						)
					);
					break;

				case 'set-status-publish':
				case 'set-status-pending':
				case 'set-status-processing':
				case 'set-status-refunded':
				case 'set-status-revoked':
				case 'set-status-failed':
				case 'set-status-cancelled':
				case 'set-status-abandoned':
				case 'set-status-preapproval':
					WalkTheCounty()->notices->register_notice(
						array(
							'id'          => 'bulk_action_status_change',
							'type'        => 'updated',
							'description' => _n(
								'Donation status updated successfully.',
								'Donation statuses updated successfully.',
								$payment_count,
								'walkthecounty'
							),
							'show'        => true,
						)
					);
					break;
			}// End switch().
		}// End if().
	}// End if().

	// Add walkthecounty message notices.
	$message_notices = walkthecounty_get_admin_messages_key();
	if ( ! empty( $message_notices ) ) {
		foreach ( $message_notices as $message_notice ) {
			// Donation reports errors.
			if ( current_user_can( 'view_walkthecounty_reports' ) ) {
				switch ( $message_notice ) {
					case 'donation-deleted':
						WalkTheCounty()->notices->register_notice(
							array(
								'id'          => 'walkthecounty-donation-deleted',
								'type'        => 'updated',
								'description' => __( 'The donation has been deleted.', 'walkthecounty' ),
								'show'        => true,
							)
						);
						break;
					case 'email-sent':
						WalkTheCounty()->notices->register_notice(
							array(
								'id'          => 'walkthecounty-email-sent',
								'type'        => 'updated',
								'description' => __( 'The donation receipt has been resent.', 'walkthecounty' ),
								'show'        => true,
							)
						);
						break;
					case 'refreshed-reports':
						WalkTheCounty()->notices->register_notice(
							array(
								'id'          => 'walkthecounty-refreshed-reports',
								'type'        => 'updated',
								'description' => __( 'The reports cache has been cleared.', 'walkthecounty' ),
								'show'        => true,
							)
						);
						break;
					case 'donation-note-deleted':
						WalkTheCounty()->notices->register_notice(
							array(
								'id'          => 'walkthecounty-donation-note-deleted',
								'type'        => 'updated',
								'description' => __( 'The donation note has been deleted.', 'walkthecounty' ),
								'show'        => true,
							)
						);
						break;
				}// End switch().
			}// End if().

			// WalkTheCounty settings notices and errors.
			if ( current_user_can( 'manage_walkthecounty_settings' ) ) {
				switch ( $message_notice ) {
					case 'settings-imported':
						WalkTheCounty()->notices->register_notice(
							array(
								'id'          => 'walkthecounty-settings-imported',
								'type'        => 'updated',
								'description' => __( 'The settings have been imported.', 'walkthecounty' ),
								'show'        => true,
							)
						);
						break;
					case 'api-key-generated':
						WalkTheCounty()->notices->register_notice(
							array(
								'id'          => 'walkthecounty-api-key-generated',
								'type'        => 'updated',
								'description' => __( 'API keys have been generated.', 'walkthecounty' ),
								'show'        => true,
							)
						);
						break;
					case 'api-key-exists':
						WalkTheCounty()->notices->register_notice(
							array(
								'id'          => 'walkthecounty-api-key-exists',
								'type'        => 'updated',
								'description' => __( 'The specified user already has API keys.', 'walkthecounty' ),
								'show'        => true,
							)
						);
						break;
					case 'api-key-regenerated':
						WalkTheCounty()->notices->register_notice(
							array(
								'id'          => 'walkthecounty-api-key-regenerated',
								'type'        => 'updated',
								'description' => __( 'API keys have been regenerated.', 'walkthecounty' ),
								'show'        => true,
							)
						);
						break;
					case 'api-key-revoked':
						WalkTheCounty()->notices->register_notice(
							array(
								'id'          => 'walkthecounty-api-key-revoked',
								'type'        => 'updated',
								'description' => __( 'API keys have been revoked.', 'walkthecounty' ),
								'show'        => true,
							)
						);
						break;
					case 'sent-test-email':
						WalkTheCounty()->notices->register_notice(
							array(
								'id'          => 'walkthecounty-sent-test-email',
								'type'        => 'updated',
								'description' => __( 'The test email has been sent.', 'walkthecounty' ),
								'show'        => true,
							)
						);
						break;
					case 'matched-success-failure-page':
						WalkTheCounty()->notices->register_notice(
							array(
								'id'          => 'walkthecounty-matched-success-failure-page',
								'type'        => 'updated',
								'description' => __( 'You cannot set the success and failed pages to the same page', 'walkthecounty' ),
								'show'        => true,
							)
						);
						break;
				}// End switch().
			}// End if().

			// Payments errors.
			if ( current_user_can( 'edit_walkthecounty_payments' ) ) {
				switch ( $message_notice ) {
					case 'note-added':
						WalkTheCounty()->notices->register_notice(
							array(
								'id'          => 'walkthecounty-note-added',
								'type'        => 'updated',
								'description' => __( 'The donation note has been added.', 'walkthecounty' ),
								'show'        => true,
							)
						);
						break;
					case 'payment-updated':
						WalkTheCounty()->notices->register_notice(
							array(
								'id'          => 'walkthecounty-payment-updated',
								'type'        => 'updated',
								'description' => __( 'The donation has been updated.', 'walkthecounty' ),
								'show'        => true,
							)
						);
						break;
				}// End switch().
			}// End if().

			// Donor Notices.
			if ( current_user_can( 'edit_walkthecounty_payments' ) ) {
				switch ( $message_notice ) {
					case 'donor-deleted':
						WalkTheCounty()->notices->register_notice(
							array(
								'id'          => 'walkthecounty-donor-deleted',
								'type'        => 'updated',
								'description' => __( 'The selected donor(s) has been deleted.', 'walkthecounty' ),
								'show'        => true,
							)
						);
						break;

					case 'donor-donations-deleted':
						WalkTheCounty()->notices->register_notice(
							array(
								'id'          => 'walkthecounty-donor-donations-deleted',
								'type'        => 'updated',
								'description' => __( 'The selected donor(s) and the associated donation(s) has been deleted.', 'walkthecounty' ),
								'show'        => true,
							)
						);
						break;

					case 'confirm-delete-donor':
						WalkTheCounty()->notices->register_notice(
							array(
								'id'          => 'walkthecounty-confirm-delete-donor',
								'type'        => 'updated',
								'description' => __( 'You must confirm to delete the selected donor(s).', 'walkthecounty' ),
								'show'        => true,
							)
						);
						break;

					case 'invalid-donor-id':
						WalkTheCounty()->notices->register_notice(
							array(
								'id'          => 'walkthecounty-invalid-donor-id',
								'type'        => 'updated',
								'description' => __( 'Invalid Donor ID.', 'walkthecounty' ),
								'show'        => true,
							)
						);
						break;

					case 'donor-delete-failed':
						WalkTheCounty()->notices->register_notice(
							array(
								'id'          => 'walkthecounty-donor-delete-failed',
								'type'        => 'error',
								'description' => __( 'Unable to delete selected donor(s).', 'walkthecounty' ),
								'show'        => true,
							)
						);
						break;

					case 'email-added':
						WalkTheCounty()->notices->register_notice(
							array(
								'id'          => 'walkthecounty-email-added',
								'type'        => 'updated',
								'description' => __( 'Donor email added.', 'walkthecounty' ),
								'show'        => true,
							)
						);
						break;

					case 'email-removed':
						WalkTheCounty()->notices->register_notice(
							array(
								'id'          => 'walkthecounty-email-removed',
								'type'        => 'updated',
								'description' => __( 'Donor email removed.', 'walkthecounty' ),
								'show'        => true,
							)
						);
						break;

					case 'email-remove-failed':
						WalkTheCounty()->notices->register_notice(
							array(
								'id'          => 'walkthecounty-email-remove-failed',
								'type'        => 'updated',
								'description' => __( 'Failed to remove donor email.', 'walkthecounty' ),
								'show'        => true,
							)
						);
						break;

					case 'primary-email-updated':
						WalkTheCounty()->notices->register_notice(
							array(
								'id'          => 'walkthecounty-primary-email-updated',
								'type'        => 'updated',
								'description' => __( 'Primary email updated for donor.', 'walkthecounty' ),
								'show'        => true,
							)
						);
						break;

					case 'primary-email-failed':
						WalkTheCounty()->notices->register_notice(
							array(
								'id'          => 'walkthecounty-primary-email-failed',
								'type'        => 'updated',
								'description' => __( 'Failed to set primary email.', 'walkthecounty' ),
								'show'        => true,
							)
						);
						break;

					case 'reconnect-user':
						WalkTheCounty()->notices->register_notice(
							array(
								'id'          => 'walkthecounty-reconnect-user',
								'type'        => 'updated',
								'description' => __( 'User has been successfully connected with Donor.', 'walkthecounty' ),
								'show'        => true,
							)
						);
						break;

					case 'disconnect-user':
						WalkTheCounty()->notices->register_notice(
							array(
								'id'          => 'walkthecounty-disconnect-user',
								'type'        => 'updated',
								'description' => __( 'User has been successfully disconnected from donor.', 'walkthecounty' ),
								'show'        => true,
							)
						);
						break;

					case 'profile-updated':
						WalkTheCounty()->notices->register_notice(
							array(
								'id'          => 'walkthecounty-profile-updated',
								'type'        => 'updated',
								'description' => __( 'Donor information updated successfully.', 'walkthecounty' ),
								'show'        => true,
							)
						);
						break;
				}// End switch().
			}// End if().
		}
	}
}

add_action( 'admin_notices', '_walkthecounty_register_admin_notices', - 1 );


/**
 * Display admin bar when active.
 *
 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
 *
 * @return bool
 */
function _walkthecounty_show_test_mode_notice_in_admin_bar( $wp_admin_bar ) {
	$is_test_mode = ! empty( $_POST['test_mode'] ) ?
		walkthecounty_is_setting_enabled( $_POST['test_mode'] ) :
		walkthecounty_is_test_mode();

	if (
		! current_user_can( 'view_walkthecounty_reports' ) ||
		! $is_test_mode
	) {
		return false;
	}

	// Add the main site admin menu item.
	$wp_admin_bar->add_menu(
		array(
			'id'     => 'walkthecounty-test-notice',
			'href'   => admin_url( 'edit.php?post_type=walkthecounty_forms&page=walkthecounty-settings&tab=gateways' ),
			'parent' => 'top-secondary',
			'title'  => __( 'WalkTheCountyWP Test Mode Active', 'walkthecounty' ),
			'meta'   => array(
				'class' => 'walkthecounty-test-mode-active',
			),
		)
	);

	return true;
}

add_action( 'admin_bar_menu', '_walkthecounty_show_test_mode_notice_in_admin_bar', 1000, 1 );

/**
 * Outputs the WalkTheCounty admin bar CSS.
 */
function _walkthecounty_test_mode_notice_admin_bar_css() {
	if ( ! walkthecounty_is_test_mode() ) {
		return;
	}
	?>
	<style>
		#wpadminbar .walkthecounty-test-mode-active > .ab-item {
			color: #fff;
			background-color: #ffba00;
		}

		#wpadminbar .walkthecounty-test-mode-active:hover > .ab-item, #wpadminbar .walkthecounty-test-mode-active:hover > .ab-item {
			background-color: rgba(203, 144, 0, 1) !important;
			color: #fff !important;
		}
	</style>
	<?php
}

add_action( 'admin_head', '_walkthecounty_test_mode_notice_admin_bar_css' );


/**
 * Add Link to Import page in from donation archive and donation single page
 *
 * @since 1.8.13
 */
function walkthecounty_import_page_link_callback() {
	?>
	<a href="<?php echo esc_url( walkthecounty_import_page_url() ); ?>"
	   class="page-import-action page-title-action"><?php _e( 'Import Donations', 'walkthecounty' ); ?></a>

	<?php
	// Check if view donation single page only.
	if ( ! empty( $_REQUEST['view'] ) && 'view-payment-details' === (string) walkthecounty_clean( $_REQUEST['view'] ) && 'walkthecounty-payment-history' === walkthecounty_clean( $_REQUEST['page'] ) ) {
		?>
		<style type="text/css">
			.wrap #transaction-details-heading {
				display: inline-block;
			}
		</style>
		<?php
	}
}

add_action( 'walkthecounty_payments_page_top', 'walkthecounty_import_page_link_callback', 11 );

/**
 * Load donation import ajax callback
 * Fire when importing from CSV start
 *
 * @since  1.8.13
 */
function walkthecounty_donation_import_callback() {
	// Bailout.
	if ( ! current_user_can( 'manage_walkthecounty_settings' ) ) {
		walkthecounty_die();
	}

	// Disable WalkTheCounty cache
	WalkTheCounty_Cache::get_instance()->disable();

	$import_setting = array();
	$fields         = isset( $_POST['fields'] ) ? $_POST['fields'] : null;

	parse_str( $fields, $output );

	$import_setting['create_user'] = $output['create_user'];
	$import_setting['mode']        = $output['mode'];
	$import_setting['delimiter']   = $output['delimiter'];
	$import_setting['csv']         = $output['csv'];
	$import_setting['delete_csv']  = $output['delete_csv'];
	$import_setting['dry_run']     = $output['dry_run'];

	// Parent key id.
	$main_key = maybe_unserialize( $output['main_key'] );

	$current    = absint( $_REQUEST['current'] );
	$total_ajax = absint( $_REQUEST['total_ajax'] );
	$start      = absint( $_REQUEST['start'] );
	$end        = absint( $_REQUEST['end'] );
	$next       = absint( $_REQUEST['next'] );
	$total      = absint( $_REQUEST['total'] );
	$per_page   = absint( $_REQUEST['per_page'] );
	if ( empty( $output['delimiter'] ) ) {
		$delimiter = ',';
	} else {
		$delimiter = $output['delimiter'];
	}

	// Processing done here.
	$raw_data                  = walkthecounty_get_donation_data_from_csv( $output['csv'], $start, $end, $delimiter );
	$raw_key                   = maybe_unserialize( $output['mapto'] );
	$import_setting['raw_key'] = $raw_key;

	if ( ! empty( $output['dry_run'] ) ) {
		$import_setting['csv_raw_data'] = walkthecounty_get_donation_data_from_csv( $output['csv'], 1, $end, $delimiter );

		$import_setting['donors_list'] = WalkTheCounty()->donors->get_donors(
			array(
				'number' => - 1,
				'fields' => array( 'id', 'user_id', 'email' ),
			)
		);
	}

	// Prevent normal emails.
	remove_action( 'walkthecounty_complete_donation', 'walkthecounty_trigger_donation_receipt', 999 );
	remove_action( 'walkthecounty_insert_user', 'walkthecounty_new_user_notification', 10 );
	remove_action( 'walkthecounty_insert_payment', 'walkthecounty_payment_save_page_data' );

	$current_key = $start;
	foreach ( $raw_data as $row_data ) {
		$import_setting['donation_key'] = $current_key;
		walkthecounty_save_import_donation_to_db( $raw_key, $row_data, $main_key, $import_setting );
		$current_key ++;
	}

	// Check if function exists or not.
	if ( function_exists( 'walkthecounty_payment_save_page_data' ) ) {
		add_action( 'walkthecounty_insert_payment', 'walkthecounty_payment_save_page_data' );
	}
	add_action( 'walkthecounty_insert_user', 'walkthecounty_new_user_notification', 10, 2 );
	add_action( 'walkthecounty_complete_donation', 'walkthecounty_trigger_donation_receipt', 999 );

	if ( $next == false ) {
		$json_data = array(
			'success' => true,
			'message' => __( 'All donation uploaded successfully!', 'walkthecounty' ),
		);
	} else {
		$index_start = $start;
		$index_end   = $end;
		$last        = false;
		$next        = true;
		if ( $next ) {
			$index_start = $index_start + $per_page;
			$index_end   = $per_page + ( $index_start - 1 );
		}
		if ( $index_end >= $total ) {
			$index_end = $total;
			$last      = true;
		}
		$json_data = array(
			'raw_data' => $raw_data,
			'raw_key'  => $raw_key,
			'next'     => $next,
			'start'    => $index_start,
			'end'      => $index_end,
			'last'     => $last,
		);
	}

	$url              = walkthecounty_import_page_url(
		array(
			'step'          => '4',
			'importer-type' => 'import_donations',
			'csv'           => $output['csv'],
			'total'         => $total,
			'delete_csv'    => $import_setting['delete_csv'],
			'success'       => ( isset( $json_data['success'] ) ? $json_data['success'] : '' ),
			'dry_run'       => $output['dry_run'],
		)
	);
	$json_data['url'] = $url;

	$current ++;
	$json_data['current'] = $current;

	$percentage              = ( 100 / ( $total_ajax + 1 ) ) * $current;
	$json_data['percentage'] = $percentage;

	// Enable WalkTheCounty cache
	WalkTheCounty_Cache::get_instance()->enable();

	$json_data = apply_filters( 'walkthecounty_import_ajax_responces', $json_data, $fields );
	wp_die( json_encode( $json_data ) );
}

add_action( 'wp_ajax_walkthecounty_donation_import', 'walkthecounty_donation_import_callback' );

/**
 * Load core settings import ajax callback
 * Fire when importing from JSON start
 *
 * @since  1.8.17
 */

function walkthecounty_core_settings_import_callback() {
	// Bailout.
	if ( ! current_user_can( 'manage_walkthecounty_settings' ) ) {
		walkthecounty_die();
	}

	$fields = isset( $_POST['fields'] ) ? $_POST['fields'] : null;
	parse_str( $fields, $fields );

	$json_data['success'] = false;

	/**
	 * Filter to Modify fields that are being pass by the ajax before importing of the core setting start.
	 *
	 * @access public
	 *
	 * @param array $fields
	 *
	 * @return array $fields
	 * @since  1.8.17
	 *
	 */
	$fields = (array) apply_filters( 'walkthecounty_import_core_settings_fields', $fields );

	$file_name = ( ! empty( $fields['file_name'] ) ? walkthecounty_clean( $fields['file_name'] ) : false );

	if ( ! empty( $file_name ) ) {
		$type = ( ! empty( $fields['type'] ) ? walkthecounty_clean( $fields['type'] ) : 'merge' );

		// Get the json data from the file and then alter it in array format
		$json_string   = walkthecounty_get_core_settings_json( $file_name );
		$json_to_array = json_decode( $json_string, true );

		// get the current setting from the options table.
		$host_walkthecounty_options = WalkTheCounty_Cache_Setting::get_settings();

		// Save old settins for backup.
		update_option( 'walkthecounty_settings_old', $host_walkthecounty_options, false );

		/**
		 * Filter to Modify Core Settings that are being going to get import in options table as walkthecounty settings.
		 *
		 * @access public
		 *
		 * @param array $json_to_array     Setting that are being going to get imported
		 * @param array $type              Type of Import
		 * @param array $host_walkthecounty_options Setting old setting that used to be in the options table.
		 * @param array $fields            Data that is being send from the ajax
		 *
		 * @return array $json_to_array Setting that are being going to get imported
		 * @since  1.8.17
		 *
		 */
		$json_to_array = (array) apply_filters( 'walkthecounty_import_core_settings_data', $json_to_array, $type, $host_walkthecounty_options, $fields );

		update_option( 'walkthecounty_settings', $json_to_array, false );

		$json_data['success'] = true;
	}

	$json_data['percentage'] = 100;

	/**
	 * Filter to Modify core import setting page url
	 *
	 * @access public
	 *
	 * @return array $url
	 * @since  1.8.17
	 *
	 */
	$json_data['url'] = walkthecounty_import_page_url(
		(array) apply_filters(
			'walkthecounty_import_core_settings_success_url', array(
				'step'          => ( empty( $json_data['success'] ) ? '1' : '3' ),
				'importer-type' => 'import_core_setting',
				'success'       => ( empty( $json_data['success'] ) ? '0' : '1' ),
			)
		)
	);

	wp_send_json( $json_data );
}

add_action( 'wp_ajax_walkthecounty_core_settings_import', 'walkthecounty_core_settings_import_callback' );

/**
 * Initializes blank slate content if a list table is empty.
 *
 * @since 1.8.13
 */
function walkthecounty_blank_slate() {
	$blank_slate = new WalkTheCounty_Blank_Slate();
	$blank_slate->init();
}

add_action( 'current_screen', 'walkthecounty_blank_slate' );

/**
 * Validate Fields of User Profile
 *
 * @param object   $errors Object of WP Errors.
 * @param int|bool $update True or False.
 * @param object   $user   WP User Data.
 *
 * @return mixed
 * @since 2.0
 *
 */
function walkthecounty_validate_user_profile( $errors, $update, $user ) {

	if ( ! empty( $_POST['action'] ) && ( 'adduser' === $_POST['action'] || 'createuser' === $_POST['action'] ) ) {
		return;
	}

	if ( ! empty( $user->ID ) ) {
		$donor = WalkTheCounty()->donors->get_donor_by( 'user_id', $user->ID );

		if ( $donor ) {
			// If Donor is attached with User, then validate first name.
			if ( empty( $_POST['first_name'] ) ) {
				$errors->add(
					'empty_first_name',
					sprintf(
						'<strong>%1$s:</strong> %2$s',
						__( 'ERROR', 'walkthecounty' ),
						__( 'Please enter your first name.', 'walkthecounty' )
					)
				);
			}
		}
	}

}

add_action( 'user_profile_update_errors', 'walkthecounty_validate_user_profile', 10, 3 );

/**
 * Show Donor Information on User Profile Page.
 *
 * @param object $user User Object.
 *
 * @since 2.0
 */
function walkthecounty_donor_information_profile_fields( $user ) {
	$donor = WalkTheCounty()->donors->get_donor_by( 'user_id', $user->ID );

	// Display Donor Information, only if donor is attached with User.
	if ( ! empty( $donor->user_id ) ) {
		?>
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row"><?php _e( 'Donor', 'walkthecounty' ); ?></th>
				<td>
					<a href="<?php echo admin_url( 'edit.php?post_type=walkthecounty_forms&page=walkthecounty-donors&view=overview&id=' . $donor->id ); ?>">
						<?php _e( 'View Donor Information', 'walkthecounty' ); ?>
					</a>
				</td>
			</tr>
			</tbody>
		</table>
		<?php
	}
}

add_action( 'personal_options', 'walkthecounty_donor_information_profile_fields' );
/**
 * Get Array of WP User Roles.
 *
 * @return array
 * @since 1.8.13
 *
 */
function walkthecounty_get_user_roles() {
	$user_roles = array();

	// Loop through User Roles.
	foreach ( get_editable_roles() as $role_name => $role_info ) :
		$user_roles[ $role_name ] = $role_info['name'];
	endforeach;

	return $user_roles;
}


/**
 * Ajax handle for donor address.
 *
 * @return string
 * @since 2.0
 *
 */
function __walkthecounty_ajax_donor_manage_addresses() {
	// Bailout.
	if (
		empty( $_POST['form'] ) ||
		empty( $_POST['donorID'] )
	) {
		wp_send_json_error(
			array(
				'error' => 1,
			)
		);
	}

	$post                  = walkthecounty_clean( wp_parse_args( $_POST ) );
	$donorID               = absint( $post['donorID'] );
	$form_data             = walkthecounty_clean( wp_parse_args( $post['form'] ) );
	$is_multi_address_type = ( 'billing' === $form_data['address-id'] || false !== strpos( $form_data['address-id'], '_' ) );
	$exploded_address_id   = explode( '_', $form_data['address-id'] );
	$address_type          = false !== strpos( $form_data['address-id'], '_' ) ?
		array_shift( $exploded_address_id ) :
		$form_data['address-id'];
	$address_id            = false !== strpos( $form_data['address-id'], '_' ) ?
		array_pop( $exploded_address_id ) :
		null;
	$response_data         = array(
		'action' => $form_data['address-action'],
		'id'     => $form_data['address-id'],
	);

	// Security check.
	if ( ! wp_verify_nonce( $form_data['_wpnonce'], 'walkthecounty-manage-donor-addresses' ) ) {
		wp_send_json_error(
			array(
				'error'     => 1,
				'error_msg' => wp_sprintf(
					'<div class="notice notice-error"><p>%s</p></div>',
					__( 'Error: Security issue.', 'walkthecounty' )
				),
			)
		);
	}

	$donor = new WalkTheCounty_Donor( $donorID );

	// Verify donor.
	if ( ! $donor->id ) {
		wp_send_json_error(
			array(
				'error' => 3,
			)
		);
	}

	// Unset all data except address.
	unset(
		$form_data['_wpnonce'],
		$form_data['address-action'],
		$form_data['address-id']
	);

	// Process action.
	switch ( $response_data['action'] ) {

		case 'add':
			if ( ! $donor->add_address( "{$address_type}[]", $form_data ) ) {
				wp_send_json_error(
					array(
						'error'     => 1,
						'error_msg' => wp_sprintf(
							'<div class="notice notice-error"><p>%s</p></div>',
							__( 'Error: Unable to save the address. Please check if address already exist.', 'walkthecounty' )
						),
					)
				);
			}

			$total_addresses = count( $donor->address[ $address_type ] );

			$address_index = $is_multi_address_type ?
				$total_addresses - 1 :
				$address_type;

			$array_keys = array_keys( $donor->address[ $address_type ] );

			$address_id = $is_multi_address_type ?
				end( $array_keys ) :
				$address_type;

			$response_data['address_html'] = __walkthecounty_get_format_address(
				end( $donor->address['billing'] ),
				array(
					// We can add only billing address from donor screen.
					'type'  => 'billing',
					'id'    => $address_id,
					'index' => ++ $address_index,
				)
			);
			$response_data['success_msg']  = wp_sprintf(
				'<div class="notice updated"><p>%s</p></div>',
				__( 'Successfully added a new address to the donor.', 'walkthecounty' )
			);

			if ( $is_multi_address_type ) {
				$response_data['id'] = "{$response_data['id']}_{$address_index}";
			}

			break;

		case 'remove':
			if ( ! $donor->remove_address( $response_data['id'] ) ) {
				wp_send_json_error(
					array(
						'error'     => 2,
						'error_msg' => wp_sprintf(
							'<div class="notice notice-error"><p>%s</p></div>',
							__( 'Error: Unable to delete address.', 'walkthecounty' )
						),
					)
				);
			}

			$response_data['success_msg'] = wp_sprintf(
				'<div class="notice updated"><p>%s</p></div>',
				__( 'Successfully removed a address of donor.', 'walkthecounty' )
			);

			break;

		case 'update':
			if ( ! $donor->update_address( $response_data['id'], $form_data ) ) {
				wp_send_json_error(
					array(
						'error'     => 3,
						'error_msg' => wp_sprintf(
							'<div class="notice notice-error"><p>%s</p></div>',
							__( 'Error: Unable to update address. Please check if address already exist.', 'walkthecounty' )
						),
					)
				);
			}

			$response_data['address_html'] = __walkthecounty_get_format_address(
				$is_multi_address_type ?
					$donor->address[ $address_type ][ $address_id ] :
					$donor->address[ $address_type ],
				array(
					'type'  => $address_type,
					'id'    => $address_id,
					'index' => $address_id,
				)
			);
			$response_data['success_msg']  = wp_sprintf(
				'<div class="notice updated"><p>%s</p></div>',
				__( 'Successfully updated a address of donor', 'walkthecounty' )
			);

			break;
	}// End switch().

	wp_send_json_success( $response_data );
}

add_action( 'wp_ajax_donor_manage_addresses', '__walkthecounty_ajax_donor_manage_addresses' );

/**
 * Admin donor billing address label
 *
 * @param string $address_label
 *
 * @return string
 * @since 2.0
 *
 */
function __walkthecounty_donor_billing_address_label( $address_label ) {
	$address_label = __( 'Billing Address', 'walkthecounty' );

	return $address_label;
}

add_action( 'walkthecounty_donor_billing_address_label', '__walkthecounty_donor_billing_address_label' );

/**
 * Admin donor personal address label
 *
 * @param string $address_label
 *
 * @return string
 * @since 2.0
 *
 */
function __walkthecounty_donor_personal_address_label( $address_label ) {
	$address_label = __( 'Personal Address', 'walkthecounty' );

	return $address_label;
}

add_action( 'walkthecounty_donor_personal_address_label', '__walkthecounty_donor_personal_address_label' );

/**
 * Update Donor Information when User Profile is updated from admin.
 * Note: for internal use only.
 *
 * @param int $user_id
 *
 * @access public
 * @return bool
 * @since  2.0
 *
 */
function walkthecounty_update_donor_name_on_user_update( $user_id = 0 ) {

	if ( current_user_can( 'edit_user', $user_id ) ) {

		$donor = new WalkTheCounty_Donor( $user_id, true );

		// Bailout, if donor doesn't exists.
		if ( ! $donor ) {
			return false;
		}

		// Get User First name and Last name.
		$first_name = ( $_POST['first_name'] ) ? walkthecounty_clean( $_POST['first_name'] ) : get_user_meta( $user_id, 'first_name', true );
		$last_name  = ( $_POST['last_name'] ) ? walkthecounty_clean( $_POST['last_name'] ) : get_user_meta( $user_id, 'last_name', true );
		$full_name  = strip_tags( wp_unslash( trim( "{$first_name} {$last_name}" ) ) );

		// Assign User First name and Last name to Donor.
		WalkTheCounty()->donors->update(
			$donor->id, array(
				'name' => $full_name,
			)
		);
		WalkTheCounty()->donor_meta->update_meta( $donor->id, '_walkthecounty_donor_first_name', $first_name );
		WalkTheCounty()->donor_meta->update_meta( $donor->id, '_walkthecounty_donor_last_name', $last_name );

	}
}

add_action( 'edit_user_profile_update', 'walkthecounty_update_donor_name_on_user_update', 10 );
add_action( 'personal_options_update', 'walkthecounty_update_donor_name_on_user_update', 10 );


/**
 * Updates the email address of a donor record when the email on a user is updated
 * Note: for internal use only.
 *
 * @param int          $user_id       User ID.
 * @param WP_User|bool $old_user_data User data.
 *
 * @return bool
 * @since  1.4.3
 * @access public
 *
 */
function walkthecounty_update_donor_email_on_user_update( $user_id = 0, $old_user_data = false ) {

	$donor = new WalkTheCounty_Donor( $user_id, true );

	if ( ! $donor ) {
		return false;
	}

	$user = get_userdata( $user_id );

	if ( ! empty( $user ) && $user->user_email !== $donor->email ) {

		$success = WalkTheCounty()->donors->update(
			$donor->id, array(
				'email' => $user->user_email,
			)
		);

		if ( $success ) {
			// Update some payment meta if we need to
			$payments_array = explode( ',', $donor->payment_ids );

			if ( ! empty( $payments_array ) ) {

				foreach ( $payments_array as $payment_id ) {

					walkthecounty_update_payment_meta( $payment_id, 'email', $user->user_email );

				}
			}

			/**
			 * Fires after updating donor email on user update.
			 *
			 * @param WP_User    $user  WordPress User object.
			 * @param WalkTheCounty_Donor $donor WalkTheCounty donor object.
			 *
			 * @since 1.4.3
			 *
			 */
			do_action( 'walkthecounty_update_donor_email_on_user_update', $user, $donor );

		}
	}

}

add_action( 'profile_update', 'walkthecounty_update_donor_email_on_user_update', 10, 2 );


/**
 * Flushes WalkTheCounty's cache.
 */
function walkthecounty_cache_flush() {
	if ( ! current_user_can( 'manage_walkthecounty_settings' ) ) {
		wp_die();
	}

	$result = WalkTheCounty_Cache::flush_cache();

	if ( $result ) {
		wp_send_json_success(
			array(
				'message' => __( 'Cache flushed successfully.', 'walkthecounty' ),
			)
		);
	} else {
		wp_send_json_error(
			array(
				'message' => __( 'An error occurred while flushing the cache.', 'walkthecounty' ),
			)
		);
	}
}

add_action( 'wp_ajax_walkthecounty_cache_flush', 'walkthecounty_cache_flush', 10, 0 );

/**
 * Admin notices for errors
 * note: only for internal use
 *
 * @access public
 * @return void
 * @since  2.5.0
 */
function walkthecounty_license_notices() {

	if ( ! current_user_can( 'manage_walkthecounty_settings' ) ) {
		return;
	}

	// Do not show licenses notices on license tab.
	if ( WalkTheCounty_Admin_Settings::is_setting_page( 'licenses' ) ) {
		return;
	}

	$walkthecounty_plugins          = walkthecounty_get_plugins( array( 'only_premium_add_ons' => true ) );
	$walkthecounty_licenses         = get_option( 'walkthecounty_licenses', array() );
	$notice_data           = array();
	$license_data          = array();
	$invalid_license_count = 0;
	$addons_with_license   = array();

	// Loop through WalkTheCounty licenses to find license status.
	foreach ( $walkthecounty_licenses as $key => $walkthecounty_license ) {
		if ( empty( $license_data[ $walkthecounty_license['license'] ] ) ) {
			$license_data[ $walkthecounty_license['license'] ] = array(
				'count'   => 0,
				'add-ons' => array(),
			);
		}

		// Setup data for all access pass.
		if ( $walkthecounty_license['is_all_access_pass'] ) {
			$addons_list = wp_list_pluck( $walkthecounty_license['download'], 'plugin_slug' );
			foreach ( $addons_list as $item ) {
				$license_data[ $walkthecounty_license['license'] ]['add-ons'][] = $addons_with_license[] = $item;
			}
		} else {
			$license_data[ $walkthecounty_license['license'] ]['add-ons'][] = $addons_with_license[] = $walkthecounty_license['plugin_slug'];
		}

		$license_data[ $walkthecounty_license['license'] ]['count'] += 1;
	}

	// Set data for inactive add-ons.
	$inactive_addons = array_diff( wp_list_pluck( $walkthecounty_plugins, 'Dir' ), $addons_with_license );

	$license_data['inactive'] = array(
		'count'   => count( $inactive_addons ),
		'add-ons' => array_values( $inactive_addons ),
	);

	// Unset active license add-ons as not required.
	unset( $license_data['valid'] );

	// Combine site inactive with inactive and unset site_inactive because already merged information with inactive
	if ( ! empty( $license_data['site_inactive'] ) ) {
		$license_data['inactive']['count']   += $license_data['site_inactive']['count'];
		$license_data['inactive']['add-ons'] += $license_data['site_inactive']['add-ons'];

		unset( $license_data['site_inactive'] );
	}

	// Loop through license data.
	foreach ( $license_data as $key => $license ) {
		if ( ! $license['count'] ) {
			continue;
		}

		$notice_data[ $key ] = sprintf(
			'%1$s %2$s',
			$license['count'],
			$key
		);

		// This will contain sum of count expect license with valid status.
		$invalid_license_count += $license['count'];
	}

	// Prepare license notice description.
	$prepared_notice_status = implode( ' , ', $notice_data );
	$prepared_notice_status = 2 <= count( $notice_data )
		? substr_replace( $prepared_notice_status, 'and', strrpos( $prepared_notice_status, ',' ), 1 )
		: $prepared_notice_status;

	$notice_description = sprintf(
		_n(
			'Your WalkTheCountyWP add-on is not receiving critical updates and new features because you have %1$s license key. Please <a href="%2$s" title="%3$s">activate your license</a> to receive updates and <a href="%4$s" target="_blank" title="%5$s">priority support</a>',
			'Your WalkTheCountyWP add-ons are not receiving critical updates and new features because you have %1$s license keys. Please <a href="%2$s" title="%3$s">activate your license</a> to receive updates and <a href="%4$s" target="_blank" title="%5$s">priority support</a>',
			$invalid_license_count,
			'walkthecounty'
		),
		$prepared_notice_status,
		admin_url( 'edit.php?post_type=walkthecounty_forms&page=walkthecounty-settings&tab=licenses' ),
		__( 'Activate License', 'walkthecounty' ),
		esc_url( 'https://walkthecountywp.com/priority-support/' ),
		__( 'Priority Support', 'walkthecounty' )
	);

	// Check by add-on if any walkthecounty add-on activated without license.
	// Do not show this notice if add-on activated with in 3 days.
	$is_required_days_past = current_time( 'timestamp' ) > ( WalkTheCounty_Cache_Setting::get_option( 'walkthecounty_addon_last_activated' ) + ( 3 * DAY_IN_SECONDS ) );

	// Default license notice arguments.
	$license_notice_args = array(
		'id'               => 'walkthecounty-invalid-expired-license',
		'type'             => 'error',
		'description'      => $notice_description,
		'dismissible_type' => 'user',
		'dismiss_interval' => 'shortly',
	);

	// Register Notices.
	if ( $invalid_license_count && $is_required_days_past ) {
		WalkTheCounty()->notices->register_notice( $license_notice_args );
	}
}

add_action( 'admin_notices', 'walkthecounty_license_notices' );


/**
 * Log walkthecounty addon activation time
 *
 * @param $plugin
 * @param $network_wide
 *
 * @since 2.5.0
 */
function walkthecounty_log_addon_activation_time( $plugin, $network_wide ) {
	if ( $network_wide ) {
		return;
	}

	$plugin_data = walkthecounty_get_plugins( array( 'only_premium_add_ons' => true ) );
	$plugin_data = ! empty( $plugin_data[ $plugin ] ) ? $plugin_data[ $plugin ] : array();

	if ( $plugin_data ) {
		update_option( 'walkthecounty_addon_last_activated', current_time( 'timestamp' ), 'no' );
	}
}

add_action( 'activate_plugin', 'walkthecounty_log_addon_activation_time', 10, 2 );


/**
 * Hide all admin notice from add-ons page
 *
 * Note: only for internal use
 *
 * @since 2.5.0
 */
function walkthecounty_hide_notices_on_add_ons_page() {
	$page = ! empty( $_GET['page'] ) ? walkthecounty_clean( $_GET['page'] ) : '';

	// Bailout.
	if ( 'walkthecounty-addons' !== $page ) {
		return;
	}

	remove_all_actions( 'admin_notices' );
}

add_action( 'in_admin_header', 'walkthecounty_hide_notices_on_add_ons_page', 999 );


/**
 * Admin JS
 *
 * @since 2.5.0
 */
function walkthecounty_admin_quick_js() {
	if ( is_multisite() && is_blog_admin() ) {
		?>
		<script>
			jQuery( document ).ready( function( $ ) {
				var $updateNotices = $( '[id$="-update"] ', '.wp-list-table' );

				if ( $updateNotices.length ) {
					$.each( $updateNotices, function( index, $updateNotice ) {
						$updateNotice = $( $updateNotice );
						$updateNotice.prev().addClass( 'update' );
					} );
				}
			} );
		</script>
		<?php
	}
}

add_action( 'admin_head', 'walkthecounty_admin_quick_js' );


