<?php
/**
 * Upgrade Functions
 *
 * @package     WalkTheCounty
 * @subpackage  Admin/Upgrades
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 *
 * NOTICE: When adding new upgrade notices, please be sure to put the action into the upgrades array during install:
 * /includes/install.php @ Appox Line 156
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Perform automatic database upgrades when necessary.
 *
 * @since  1.6
 * @return void
 */
function walkthecounty_do_automatic_upgrades() {
	$did_upgrade  = false;
	$walkthecounty_version = preg_replace( '/[^0-9.].*/', '', WalkTheCounty_Cache_Setting::get_option( 'walkthecounty_version' ) );

	if ( ! $walkthecounty_version ) {
		// 1.0 is the first version to use this option so we must add it.
		$walkthecounty_version = '1.0';
	}

	switch ( true ) {

		case version_compare( $walkthecounty_version, '1.6', '<' ):
			walkthecounty_v16_upgrades();
			$did_upgrade = true;

		case version_compare( $walkthecounty_version, '1.7', '<' ):
			walkthecounty_v17_upgrades();
			$did_upgrade = true;

		case version_compare( $walkthecounty_version, '1.8', '<' ):
			walkthecounty_v18_upgrades();
			$did_upgrade = true;

		case version_compare( $walkthecounty_version, '1.8.7', '<' ):
			walkthecounty_v187_upgrades();
			$did_upgrade = true;

		case version_compare( $walkthecounty_version, '1.8.8', '<' ):
			walkthecounty_v188_upgrades();
			$did_upgrade = true;

		case version_compare( $walkthecounty_version, '1.8.9', '<' ):
			walkthecounty_v189_upgrades();
			$did_upgrade = true;

		case version_compare( $walkthecounty_version, '1.8.12', '<' ):
			walkthecounty_v1812_upgrades();
			$did_upgrade = true;

		case version_compare( $walkthecounty_version, '1.8.13', '<' ):
			walkthecounty_v1813_upgrades();
			$did_upgrade = true;

		case version_compare( $walkthecounty_version, '1.8.17', '<' ):
			walkthecounty_v1817_upgrades();
			$did_upgrade = true;

		case version_compare( $walkthecounty_version, '1.8.18', '<' ):
			walkthecounty_v1818_upgrades();
			$did_upgrade = true;

		case version_compare( $walkthecounty_version, '2.0', '<' ):
			walkthecounty_v20_upgrades();
			$did_upgrade = true;

		case version_compare( $walkthecounty_version, '2.0.1', '<' ):
			// Do nothing on fresh install.
			if ( ! doing_action( 'walkthecounty_upgrades' ) ) {
				walkthecounty_v201_create_tables();
				WalkTheCounty_Updates::get_instance()->__health_background_update( WalkTheCounty_Updates::get_instance() );
				WalkTheCounty_Updates::$background_updater->dispatch();
			}

			$did_upgrade = true;

		case version_compare( $walkthecounty_version, '2.0.2', '<' ):
			// Remove 2.0.1 update to rerun on 2.0.2
			$completed_upgrades = walkthecounty_get_completed_upgrades();
			$v201_updates       = array(
				'v201_upgrades_payment_metadata',
				'v201_add_missing_donors',
				'v201_move_metadata_into_new_table',
				'v201_logs_upgrades',
			);

			foreach ( $v201_updates as $v201_update ) {
				if ( in_array( $v201_update, $completed_upgrades ) ) {
					unset( $completed_upgrades[ array_search( $v201_update, $completed_upgrades ) ] );
				}
			}

			update_option( 'walkthecounty_completed_upgrades', $completed_upgrades, false );

			// Do nothing on fresh install.
			if ( ! doing_action( 'walkthecounty_upgrades' ) ) {
				walkthecounty_v201_create_tables();
				WalkTheCounty_Updates::get_instance()->__health_background_update( WalkTheCounty_Updates::get_instance() );
				WalkTheCounty_Updates::$background_updater->dispatch();
			}

			$did_upgrade = true;

		case version_compare( $walkthecounty_version, '2.0.3', '<' ):
			walkthecounty_v203_upgrades();
			$did_upgrade = true;

		case version_compare( $walkthecounty_version, '2.2.0', '<' ):
			walkthecounty_v220_upgrades();
			$did_upgrade = true;

		case version_compare( $walkthecounty_version, '2.2.1', '<' ):
			walkthecounty_v221_upgrades();
			$did_upgrade = true;

		case version_compare( $walkthecounty_version, '2.3.0', '<' ):
			walkthecounty_v230_upgrades();
			$did_upgrade = true;

		case version_compare( $walkthecounty_version, '2.5.0', '<' ):
			walkthecounty_v250_upgrades();
			$did_upgrade = true;

		case version_compare( $walkthecounty_version, '2.5.8', '<' ):
			walkthecounty_v258_upgrades();
			$did_upgrade = true;
	}

	if ( $did_upgrade || version_compare( $walkthecounty_version, WALKTHECOUNTY_VERSION, '<' ) ) {
		update_option( 'walkthecounty_version', preg_replace( '/[^0-9.].*/', '', WALKTHECOUNTY_VERSION ), false );
	}
}

add_action( 'admin_init', 'walkthecounty_do_automatic_upgrades', 0 );
add_action( 'walkthecounty_upgrades', 'walkthecounty_do_automatic_upgrades', 0 );

/**
 * Display Upgrade Notices.
 *
 * IMPORTANT: ALSO UPDATE INSTALL.PHP WITH THE ID OF THE UPGRADE ROUTINE SO IT DOES NOT AFFECT NEW INSTALLS.
 *
 * @since 1.0
 * @since 1.8.12 Update new update process code.
 *
 * @param WalkTheCounty_Updates $walkthecounty_updates
 *
 * @return void
 */
function walkthecounty_show_upgrade_notices( $walkthecounty_updates ) {
	// v1.3.2 Upgrades
	$walkthecounty_updates->register(
		array(
			'id'       => 'upgrade_walkthecounty_payment_customer_id',
			'version'  => '1.3.2',
			'callback' => 'walkthecounty_v132_upgrade_walkthecounty_payment_customer_id',
		)
	);

	// v1.3.4 Upgrades ensure the user has gone through 1.3.4.
	$walkthecounty_updates->register(
		array(
			'id'       => 'upgrade_walkthecounty_offline_status',
			'depend'   => 'upgrade_walkthecounty_payment_customer_id',
			'version'  => '1.3.4',
			'callback' => 'walkthecounty_v134_upgrade_walkthecounty_offline_status',
		)
	);

	// v1.8 form metadata upgrades.
	$walkthecounty_updates->register(
		array(
			'id'       => 'v18_upgrades_form_metadata',
			'version'  => '1.8',
			'callback' => 'walkthecounty_v18_upgrades_form_metadata',
		)
	);

	// v1.8.9 Upgrades
	$walkthecounty_updates->register(
		array(
			'id'       => 'v189_upgrades_levels_post_meta',
			'version'  => '1.8.9',
			'callback' => 'walkthecounty_v189_upgrades_levels_post_meta_callback',
		)
	);

	// v1.8.12 Upgrades
	$walkthecounty_updates->register(
		array(
			'id'       => 'v1812_update_amount_values',
			'version'  => '1.8.12',
			'callback' => 'walkthecounty_v1812_update_amount_values_callback',
		)
	);

	// v1.8.12 Upgrades
	$walkthecounty_updates->register(
		array(
			'id'       => 'v1812_update_donor_purchase_values',
			'version'  => '1.8.12',
			'callback' => 'walkthecounty_v1812_update_donor_purchase_value_callback',
		)
	);

	// v1.8.13 Upgrades for donor
	$walkthecounty_updates->register(
		array(
			'id'       => 'v1813_update_donor_user_roles',
			'version'  => '1.8.13',
			'callback' => 'walkthecounty_v1813_update_donor_user_roles_callback',
		)
	);

	// v1.8.17 Upgrades for donations.
	$walkthecounty_updates->register(
		array(
			'id'       => 'v1817_update_donation_iranian_currency_code',
			'version'  => '1.8.17',
			'callback' => 'walkthecounty_v1817_update_donation_iranian_currency_code',
		)
	);

	// v1.8.17 Upgrades for cleanup of user roles.
	$walkthecounty_updates->register(
		array(
			'id'       => 'v1817_cleanup_user_roles',
			'version'  => '1.8.17',
			'callback' => 'walkthecounty_v1817_cleanup_user_roles',
		)
	);

	// v1.8.18 Upgrades for assigning custom amount to existing set donations.
	$walkthecounty_updates->register(
		array(
			'id'       => 'v1818_assign_custom_amount_set_donation',
			'version'  => '1.8.18',
			'callback' => 'walkthecounty_v1818_assign_custom_amount_set_donation',
		)
	);

	// v1.8.18 Cleanup the WalkTheCounty Worker Role Caps.
	$walkthecounty_updates->register(
		array(
			'id'       => 'v1818_walkthecounty_worker_role_cleanup',
			'version'  => '1.8.18',
			'callback' => 'walkthecounty_v1818_walkthecounty_worker_role_cleanup',
		)
	);

	// v2.0.0 Upgrades
	$walkthecounty_updates->register(
		array(
			'id'       => 'v20_upgrades_form_metadata',
			'version'  => '2.0.0',
			'callback' => 'walkthecounty_v20_upgrades_form_metadata_callback',
		)
	);

	// v2.0.0 User Address Upgrades
	$walkthecounty_updates->register(
		array(
			'id'       => 'v20_upgrades_user_address',
			'version'  => '2.0.0',
			'callback' => 'walkthecounty_v20_upgrades_user_address',
		)
	);

	// v2.0.0 Upgrades
	$walkthecounty_updates->register(
		array(
			'id'       => 'v20_upgrades_payment_metadata',
			'version'  => '2.0.0',
			'callback' => 'walkthecounty_v20_upgrades_payment_metadata_callback',
		)
	);

	// v2.0.0 Upgrades
	$walkthecounty_updates->register(
		array(
			'id'       => 'v20_logs_upgrades',
			'version'  => '2.0.0',
			'callback' => 'walkthecounty_v20_logs_upgrades_callback',

		)
	);

	// v2.0.0 Donor Name Upgrades
	$walkthecounty_updates->register(
		array(
			'id'       => 'v20_upgrades_donor_name',
			'version'  => '2.0.0',
			'callback' => 'walkthecounty_v20_upgrades_donor_name',
		)
	);

	// v2.0.0 Upgrades
	$walkthecounty_updates->register(
		array(
			'id'       => 'v20_move_metadata_into_new_table',
			'version'  => '2.0.0',
			'callback' => 'walkthecounty_v20_move_metadata_into_new_table_callback',
			'depend'   => array( 'v20_upgrades_payment_metadata', 'v20_upgrades_form_metadata' ),
		)
	);

	// v2.0.0 Upgrades
	$walkthecounty_updates->register(
		array(
			'id'       => 'v20_rename_donor_tables',
			'version'  => '2.0.0',
			'callback' => 'walkthecounty_v20_rename_donor_tables_callback',
			'depend'   => array(
				'v20_move_metadata_into_new_table',
				'v20_logs_upgrades',
				'v20_upgrades_form_metadata',
				'v20_upgrades_payment_metadata',
				'v20_upgrades_user_address',
				'v20_upgrades_donor_name',
			),
		)
	);

	// v2.0.1 Upgrades
	$walkthecounty_updates->register(
		array(
			'id'       => 'v201_upgrades_payment_metadata',
			'version'  => '2.0.1',
			'callback' => 'walkthecounty_v201_upgrades_payment_metadata_callback',
		)
	);

	// v2.0.1 Upgrades
	$walkthecounty_updates->register(
		array(
			'id'       => 'v201_add_missing_donors',
			'version'  => '2.0.1',
			'callback' => 'walkthecounty_v201_add_missing_donors_callback',
		)
	);

	// Run v2.0.0 Upgrades again in 2.0.1
	$walkthecounty_updates->register(
		array(
			'id'       => 'v201_move_metadata_into_new_table',
			'version'  => '2.0.1',
			'callback' => 'walkthecounty_v201_move_metadata_into_new_table_callback',
			'depend'   => array( 'v201_upgrades_payment_metadata', 'v201_add_missing_donors' ),
		)
	);

	// Run v2.0.0 Upgrades again in 2.0.1
	$walkthecounty_updates->register(
		array(
			'id'       => 'v201_logs_upgrades',
			'version'  => '2.0.1',
			'callback' => 'walkthecounty_v201_logs_upgrades_callback',
		)
	);

	// v2.1 Verify Form Status Upgrade.
	$walkthecounty_updates->register(
		array(
			'id'       => 'v210_verify_form_status_upgrades',
			'version'  => '2.1.0',
			'callback' => 'walkthecounty_v210_verify_form_status_upgrades_callback',
		)
	);

	// v2.1.3 Delete non attached donation meta.
	$walkthecounty_updates->register(
		array(
			'id'       => 'v213_delete_donation_meta',
			'version'  => '2.1.3',
			'callback' => 'walkthecounty_v213_delete_donation_meta_callback',
			'depends'  => array( 'v201_move_metadata_into_new_table' ),
		)
	);

	// v2.1.5 Add additional capability to the walkthecounty_manager role.
	$walkthecounty_updates->register(
		array(
			'id'       => 'v215_update_donor_user_roles',
			'version'  => '2.1.5',
			'callback' => 'walkthecounty_v215_update_donor_user_roles_callback',
		)
	);

	// v2.2.4 set each donor to anonymous by default.
	$walkthecounty_updates->register(
		array(
			'id'       => 'v224_update_donor_meta',
			'version'  => '2.2.4',
			'callback' => 'walkthecounty_v224_update_donor_meta_callback',
		)
	);

	// v2.2.4 Associate form IDs with donor meta of anonymous donations.
	$walkthecounty_updates->register(
		array(
			'id'       => 'v224_update_donor_meta_forms_id',
			'version'  => '2.2.4',
			'callback' => 'walkthecounty_v224_update_donor_meta_forms_id_callback',
			'depend'   => 'v224_update_donor_meta',
		)
	);

	// v2.3.0 Move donor notes to custom comment table.
	$walkthecounty_updates->register(
		array(
			'id'       => 'v230_move_donor_note',
			'version'  => '2.3.0',
			'callback' => 'walkthecounty_v230_move_donor_note_callback',
		)
	);

	// v2.3.0 Move donation notes to custom comment table.
	$walkthecounty_updates->register(
		array(
			'id'       => 'v230_move_donation_note',
			'version'  => '2.3.0',
			'callback' => 'walkthecounty_v230_move_donation_note_callback',
		)
	);

	// v2.3.0 remove donor wall related donor meta data.
	$walkthecounty_updates->register(
		array(
			'id'       => 'v230_delete_donor_wall_related_donor_data',
			'version'  => '2.3.0',
			'depend'   => array(
				'v224_update_donor_meta',
				'v224_update_donor_meta_forms_id',
				'v230_move_donor_note',
				'v230_move_donation_note'
			),
			'callback' => 'walkthecounty_v230_delete_dw_related_donor_data_callback',
		)
	);

	// v2.3.0 remove donor wall related comment meta data.
	$walkthecounty_updates->register(
		array(
			'id'       => 'v230_delete_donor_wall_related_comment_data',
			'version'  => '2.3.0',
			'callback' => 'walkthecounty_v230_delete_dw_related_comment_data_callback',
			'depend'   => array(
				'v230_move_donor_note',
				'v230_move_donation_note'
			),
		)
	);

	// v2.4.0 Update donation form goal progress data.
	$walkthecounty_updates->register(
		array(
			'id'       => 'v240_update_form_goal_progress',
			'version'  => '2.4.0',
			'callback' => 'walkthecounty_v240_update_form_goal_progress_callback',
		)
	);

	// v2.4.1 Update to remove sale type log
	$walkthecounty_updates->register(
		array(
			'id'       => 'v241_remove_sale_logs',
			'version'  => '2.4.1',
			'callback' => 'walkthecounty_v241_remove_sale_logs_callback',
			'depend'   => array( 'v201_logs_upgrades' ),
		)
	);

}

add_action( 'walkthecounty_register_updates', 'walkthecounty_show_upgrade_notices' );

/**
 * Triggers all upgrade functions
 *
 * This function is usually triggered via AJAX
 *
 * @since 1.0
 * @return void
 */
function walkthecounty_trigger_upgrades() {

	if ( ! current_user_can( 'manage_walkthecounty_settings' ) ) {
		wp_die(
			esc_html__( 'You do not have permission to do WalkTheCountyWP upgrades.', 'walkthecounty' ), esc_html__( 'Error', 'walkthecounty' ), array(
				'response' => 403,
			)
		);
	}

	$walkthecounty_version = get_option( 'walkthecounty_version' );

	if ( ! $walkthecounty_version ) {
		// 1.0 is the first version to use this option so we must add it.
		$walkthecounty_version = '1.0';
		add_option( 'walkthecounty_version', $walkthecounty_version, '', false );
	}

	update_option( 'walkthecounty_version', WALKTHECOUNTY_VERSION, false );
	delete_option( 'walkthecounty_doing_upgrade' );

	if ( DOING_AJAX ) {
		die( 'complete' );
	} // End if().
}

add_action( 'wp_ajax_walkthecounty_trigger_upgrades', 'walkthecounty_trigger_upgrades' );


/**
 * Upgrades the
 *
 * Standardizes the discrepancies between two metakeys `_walkthecounty_payment_customer_id` and `_walkthecounty_payment_donor_id`
 *
 * @since      1.3.2
 */
function walkthecounty_v132_upgrade_walkthecounty_payment_customer_id() {
	global $wpdb;

	/* @var WalkTheCounty_Updates $walkthecounty_updates */
	$walkthecounty_updates = WalkTheCounty_Updates::get_instance();

	// UPDATE DB METAKEYS.
	$sql   = "UPDATE $wpdb->postmeta SET meta_key = '_walkthecounty_payment_customer_id' WHERE meta_key = '_walkthecounty_payment_donor_id'";
	$query = $wpdb->query( $sql );

	$walkthecounty_updates->percentage = 100;
	walkthecounty_set_upgrade_complete( 'upgrade_walkthecounty_payment_customer_id' );
}


/**
 * Upgrades the Offline Status
 *
 * Reverses the issue where offline donations in "pending" status where inappropriately marked as abandoned
 *
 * @since      1.3.4
 */
function walkthecounty_v134_upgrade_walkthecounty_offline_status() {
	global $wpdb;

	/* @var WalkTheCounty_Updates $walkthecounty_updates */
	$walkthecounty_updates = WalkTheCounty_Updates::get_instance();

	// Get abandoned offline payments.
	$select = "SELECT ID FROM $wpdb->posts p ";
	$join   = "LEFT JOIN $wpdb->postmeta m ON p.ID = m.post_id ";
	$where  = "WHERE p.post_type = 'walkthecounty_payment' ";
	$where  .= "AND ( p.post_status = 'abandoned' )";
	$where  .= "AND ( m.meta_key = '_walkthecounty_payment_gateway' AND m.meta_value = 'offline' )";

	$sql            = $select . $join . $where;
	$found_payments = $wpdb->get_col( $sql );

	foreach ( $found_payments as $payment ) {

		// Only change ones marked abandoned since our release last week because the admin may have marked some abandoned themselves.
		$modified_time = get_post_modified_time( 'U', false, $payment );

		// 1450124863 =  12/10/2015 20:42:25.
		if ( $modified_time >= 1450124863 ) {

			walkthecounty_update_payment_status( $payment, 'pending' );

		}
	}

	$walkthecounty_updates->percentage = 100;
	walkthecounty_set_upgrade_complete( 'upgrade_walkthecounty_offline_status' );
}


/**
 * Cleanup User Roles
 *
 * This upgrade routine removes unused roles and roles with typos
 *
 * @since      1.5.2
 */
function walkthecounty_v152_cleanup_users() {

	$walkthecounty_version = get_option( 'walkthecounty_version' );

	if ( ! $walkthecounty_version ) {
		// 1.0 is the first version to use this option so we must add it.
		$walkthecounty_version = '1.0';
	}

	$walkthecounty_version = preg_replace( '/[^0-9.].*/', '', $walkthecounty_version );

	// v1.5.2 Upgrades
	if ( version_compare( $walkthecounty_version, '1.5.2', '<' ) || ! walkthecounty_has_upgrade_completed( 'upgrade_walkthecounty_user_caps_cleanup' ) ) {

		// Delete all caps with "ss".
		// Also delete all unused "campaign" roles.
		$delete_caps = array(
			'delete_walkthecounty_formss',
			'delete_others_walkthecounty_formss',
			'delete_private_walkthecounty_formss',
			'delete_published_walkthecounty_formss',
			'read_private_forms',
			'edit_walkthecounty_formss',
			'edit_others_walkthecounty_formss',
			'edit_private_walkthecounty_formss',
			'edit_published_walkthecounty_formss',
			'publish_walkthecounty_formss',
			'read_private_walkthecounty_formss',
			'assign_walkthecounty_campaigns_terms',
			'delete_walkthecounty_campaigns',
			'delete_walkthecounty_campaigns_terms',
			'delete_walkthecounty_campaignss',
			'delete_others_walkthecounty_campaignss',
			'delete_private_walkthecounty_campaignss',
			'delete_published_walkthecounty_campaignss',
			'edit_walkthecounty_campaigns',
			'edit_walkthecounty_campaigns_terms',
			'edit_walkthecounty_campaignss',
			'edit_others_walkthecounty_campaignss',
			'edit_private_walkthecounty_campaignss',
			'edit_published_walkthecounty_campaignss',
			'manage_walkthecounty_campaigns_terms',
			'publish_walkthecounty_campaignss',
			'read_walkthecounty_campaigns',
			'read_private_walkthecounty_campaignss',
			'view_walkthecounty_campaigns_stats',
			'delete_walkthecounty_paymentss',
			'delete_others_walkthecounty_paymentss',
			'delete_private_walkthecounty_paymentss',
			'delete_published_walkthecounty_paymentss',
			'edit_walkthecounty_paymentss',
			'edit_others_walkthecounty_paymentss',
			'edit_private_walkthecounty_paymentss',
			'edit_published_walkthecounty_paymentss',
			'publish_walkthecounty_paymentss',
			'read_private_walkthecounty_paymentss',
		);

		global $wp_roles;
		foreach ( $delete_caps as $cap ) {
			foreach ( array_keys( $wp_roles->roles ) as $role ) {
				$wp_roles->remove_cap( $role, $cap );
			}
		}

		// Create WalkTheCounty plugin roles.
		$roles = new WalkTheCounty_Roles();
		$roles->add_roles();
		$roles->add_caps();

		// The Update Ran.
		update_option( 'walkthecounty_version', preg_replace( '/[^0-9.].*/', '', WALKTHECOUNTY_VERSION ), false );
		walkthecounty_set_upgrade_complete( 'upgrade_walkthecounty_user_caps_cleanup' );
		delete_option( 'walkthecounty_doing_upgrade' );

	}// End if().

}

add_action( 'admin_init', 'walkthecounty_v152_cleanup_users' );

/**
 * 1.6 Upgrade routine to create the customer meta table.
 *
 * @since  1.6
 * @return void
 */
function walkthecounty_v16_upgrades() {
	// Create the donor databases.
	$donors_db = new WalkTheCounty_DB_Donors();
	$donors_db->create_table();
	$donor_meta = new WalkTheCounty_DB_Donor_Meta();
	$donor_meta->create_table();
}

/**
 * 1.7 Upgrades.
 *
 * a. Update license api data for plugin addons.
 * b. Cleanup user roles.
 *
 * @since  1.7
 * @return void
 */
function walkthecounty_v17_upgrades() {
	// Upgrade license data.
	walkthecounty_v17_upgrade_addon_license_data();
	walkthecounty_v17_cleanup_roles();
}

/**
 * Upgrade license data
 *
 * @since 1.7
 */
function walkthecounty_v17_upgrade_addon_license_data() {
	$walkthecounty_options = walkthecounty_get_settings();

	$api_url = 'https://walkthecountywp.com/walkthecounty-sl-api/';

	// Get addons license key.
	$addons = array();
	foreach ( $walkthecounty_options as $key => $value ) {
		if ( false !== strpos( $key, '_license_key' ) ) {
			$addons[ $key ] = $value;
		}
	}

	// Bailout: We do not have any addon license data to upgrade.
	if ( empty( $addons ) ) {
		return false;
	}

	foreach ( $addons as $key => $addon_license ) {

		// Get addon shortname.
		$shortname = str_replace( '_license_key', '', $key );

		// Addon license option name.
		$addon_license_option_name = $shortname . '_license_active';

		// bailout if license is empty.
		if ( empty( $addon_license ) ) {
			delete_option( $addon_license_option_name );
			continue;
		}

		// Get addon name.
		$addon_name       = array();
		$addon_name_parts = explode( '_', str_replace( 'walkthecounty_', '', $shortname ) );
		foreach ( $addon_name_parts as $name_part ) {

			// Fix addon name
			switch ( $name_part ) {
				case 'authorizenet':
					$name_part = 'authorize.net';
					break;
			}

			$addon_name[] = ucfirst( $name_part );
		}

		$addon_name = implode( ' ', $addon_name );

		// Data to send to the API.
		$api_params = array(
			'edd_action' => 'activate_license', // never change from "edd_" to "walkthecounty_"!
			'license'    => $addon_license,
			'item_name'  => urlencode( $addon_name ),
			'url'        => home_url(),
		);

		// Call the API.
		$response = wp_remote_post(
			$api_url,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params,
			)
		);

		// Make sure there are no errors.
		if ( is_wp_error( $response ) ) {
			delete_option( $addon_license_option_name );
			continue;
		}

		// Tell WordPress to look for updates.
		set_site_transient( 'update_plugins', null );

		// Decode license data.
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		update_option( $addon_license_option_name, $license_data, false );
	}// End foreach().
}


/**
 * Cleanup User Roles.
 *
 * This upgrade routine removes unused roles and roles with typos.
 *
 * @since      1.7
 */
function walkthecounty_v17_cleanup_roles() {

	// Delete all caps with "_walkthecounty_forms_" and "_walkthecounty_payments_".
	// These roles have no usage; the proper is singular.
	$delete_caps = array(
		'view_walkthecounty_forms_stats',
		'delete_walkthecounty_forms_terms',
		'assign_walkthecounty_forms_terms',
		'edit_walkthecounty_forms_terms',
		'manage_walkthecounty_forms_terms',
		'view_walkthecounty_payments_stats',
		'manage_walkthecounty_payments_terms',
		'edit_walkthecounty_payments_terms',
		'assign_walkthecounty_payments_terms',
		'delete_walkthecounty_payments_terms',
	);

	global $wp_roles;
	foreach ( $delete_caps as $cap ) {
		foreach ( array_keys( $wp_roles->roles ) as $role ) {
			$wp_roles->remove_cap( $role, $cap );
		}
	}

	// Set roles again.
	$roles = new WalkTheCounty_Roles();
	$roles->add_roles();
	$roles->add_caps();

}

/**
 * 1.8 Upgrades.
 *
 * a. Upgrade checkbox settings to radio button settings.
 * a. Update form meta for new metabox settings.
 *
 * @since  1.8
 * @return void
 */
function walkthecounty_v18_upgrades() {
	// Upgrade checkbox settings to radio button settings.
	walkthecounty_v18_upgrades_core_setting();
}

/**
 * Upgrade core settings.
 *
 * @since  1.8
 * @return void
 */
function walkthecounty_v18_upgrades_core_setting() {
	// Core settings which changes from checkbox to radio.
	$core_setting_names = array_merge(
		array_keys( walkthecounty_v18_renamed_core_settings() ),
		array(
			'uninstall_on_delete',
			'scripts_footer',
			'test_mode',
			'email_access',
			'terms',
			'walkthecounty_offline_donation_enable_billing_fields',
		)
	);

	// Bailout: If not any setting define.
	if ( $walkthecounty_settings = get_option( 'walkthecounty_settings' ) ) {

		$setting_changed = false;

		// Loop: check each setting field.
		foreach ( $core_setting_names as $setting_name ) {
			// New setting name.
			$new_setting_name = preg_replace( '/^(enable_|disable_)/', '', $setting_name );

			// Continue: If setting already set.
			if (
				array_key_exists( $new_setting_name, $walkthecounty_settings )
				&& in_array( $walkthecounty_settings[ $new_setting_name ], array( 'enabled', 'disabled' ) )
			) {
				continue;
			}

			// Set checkbox value to radio value.
			$walkthecounty_settings[ $setting_name ] = ( ! empty( $walkthecounty_settings[ $setting_name ] ) && 'on' === $walkthecounty_settings[ $setting_name ] ? 'enabled' : 'disabled' );

			// @see https://github.com/impress-org/walkthecounty/issues/1063.
			if ( false !== strpos( $setting_name, 'disable_' ) ) {

				$walkthecounty_settings[ $new_setting_name ] = ( walkthecounty_is_setting_enabled( $walkthecounty_settings[ $setting_name ] ) ? 'disabled' : 'enabled' );
			} elseif ( false !== strpos( $setting_name, 'enable_' ) ) {

				$walkthecounty_settings[ $new_setting_name ] = ( walkthecounty_is_setting_enabled( $walkthecounty_settings[ $setting_name ] ) ? 'enabled' : 'disabled' );
			}

			// Tell bot to update core setting to db.
			if ( ! $setting_changed ) {
				$setting_changed = true;
			}
		}

		// Update setting only if they changed.
		if ( $setting_changed ) {
			update_option( 'walkthecounty_settings', $walkthecounty_settings, false );
		}
	}// End if().

	walkthecounty_set_upgrade_complete( 'v18_upgrades_core_setting' );
}

/**
 * Upgrade form metadata for new metabox settings.
 *
 * @since  1.8
 * @return void
 */
function walkthecounty_v18_upgrades_form_metadata() {
	/* @var WalkTheCounty_Updates $walkthecounty_updates */
	$walkthecounty_updates = WalkTheCounty_Updates::get_instance();

	// form query
	$forms = new WP_Query(
		array(
			'paged'          => $walkthecounty_updates->step,
			'status'         => 'any',
			'order'          => 'ASC',
			'post_type'      => 'walkthecounty_forms',
			'posts_per_page' => 20,
		)
	);

	if ( $forms->have_posts() ) {
		$walkthecounty_updates->set_percentage( $forms->found_posts, ( $walkthecounty_updates->step * 20 ) );

		while ( $forms->have_posts() ) {
			$forms->the_post();

			// Form content.
			// Note in version 1.8 display content setting split into display content and content placement setting.
			// You can delete _walkthecounty_content_option in future.
			$show_content = walkthecounty_get_meta( get_the_ID(), '_walkthecounty_content_option', true );
			if ( $show_content && ! walkthecounty_get_meta( get_the_ID(), '_walkthecounty_display_content', true ) ) {
				$field_value = ( 'none' !== $show_content ? 'enabled' : 'disabled' );
				walkthecounty_update_meta( get_the_ID(), '_walkthecounty_display_content', $field_value );

				$field_value = ( 'none' !== $show_content ? $show_content : 'walkthecounty_pre_form' );
				walkthecounty_update_meta( get_the_ID(), '_walkthecounty_content_placement', $field_value );
			}

			// "Disable" Guest Donation. Checkbox.
			// See: https://github.com/impress-org/walkthecounty/issues/1470.
			$guest_donation        = walkthecounty_get_meta( get_the_ID(), '_walkthecounty_logged_in_only', true );
			$guest_donation_newval = ( in_array( $guest_donation, array( 'yes', 'on' ) ) ? 'disabled' : 'enabled' );
			walkthecounty_update_meta( get_the_ID(), '_walkthecounty_logged_in_only', $guest_donation_newval );

			// Offline Donations.
			// See: https://github.com/impress-org/walkthecounty/issues/1579.
			$offline_donation = walkthecounty_get_meta( get_the_ID(), '_walkthecounty_customize_offline_donations', true );
			if ( 'no' === $offline_donation ) {
				$offline_donation_newval = 'global';
			} elseif ( 'yes' === $offline_donation ) {
				$offline_donation_newval = 'enabled';
			} else {
				$offline_donation_newval = 'disabled';
			}
			walkthecounty_update_meta( get_the_ID(), '_walkthecounty_customize_offline_donations', $offline_donation_newval );

			// Convert yes/no setting field to enabled/disabled.
			$form_radio_settings = array(
				// Custom Amount.
				'_walkthecounty_custom_amount',

				// Donation Gaol.
				'_walkthecounty_goal_option',

				// Close Form.
				'_walkthecounty_close_form_when_goal_achieved',

				// Term & conditions.
				'_walkthecounty_terms_option',

				// Billing fields.
				'_walkthecounty_offline_donation_enable_billing_fields_single',
			);

			foreach ( $form_radio_settings as $meta_key ) {
				// Get value.
				$field_value = walkthecounty_get_meta( get_the_ID(), $meta_key, true );

				// Convert meta value only if it is in yes/no/none.
				if ( in_array( $field_value, array( 'yes', 'on', 'no', 'none' ) ) ) {

					$field_value = ( in_array( $field_value, array( 'yes', 'on' ) ) ? 'enabled' : 'disabled' );
					walkthecounty_update_meta( get_the_ID(), $meta_key, $field_value );
				}
			}
		}// End while().

		wp_reset_postdata();

	} else {
		// No more forms found, finish up.
		walkthecounty_set_upgrade_complete( 'v18_upgrades_form_metadata' );
	}
}


/**
 * Get list of core setting renamed in version 1.8.
 *
 * @since  1.8
 * @return array
 */
function walkthecounty_v18_renamed_core_settings() {
	return array(
		'disable_paypal_verification' => 'paypal_verification',
		'disable_css'                 => 'css',
		'disable_welcome'             => 'welcome',
		'disable_forms_singular'      => 'forms_singular',
		'disable_forms_archives'      => 'forms_archives',
		'disable_forms_excerpt'       => 'forms_excerpt',
		'disable_form_featured_img'   => 'form_featured_img',
		'disable_form_sidebar'        => 'form_sidebar',
		'disable_admin_notices'       => 'admin_notices',
		'disable_the_content_filter'  => 'the_content_filter',
		'enable_floatlabels'          => 'floatlabels',
		'enable_categories'           => 'categories',
		'enable_tags'                 => 'tags',
	);
}


/**
 * Upgrade core settings.
 *
 * @since  1.8.7
 * @return void
 */
function walkthecounty_v187_upgrades() {
	global $wpdb;

	/**
	 * Upgrade 1: Remove stat and cache transients.
	 */
	$cached_options = $wpdb->get_col(
		$wpdb->prepare(
			"
					SELECT *
					FROM {$wpdb->options}
					WHERE (
					option_name LIKE %s
					OR option_name LIKE %s
					OR option_name LIKE %s
					OR option_name LIKE %s
					OR option_name LIKE %s
					OR option_name LIKE %s
					OR option_name LIKE %s
					OR option_name LIKE %s
					OR option_name LIKE %s
					OR option_name LIKE %s
					OR option_name LIKE %s
					OR option_name LIKE %s
					OR option_name LIKE %s
					)
					",
			array(
				'%_transient_walkthecounty_stats_%',
				'walkthecounty_cache%',
				'%_transient_walkthecounty_add_ons_feed%',
				'%_transient__walkthecounty_ajax_works' .
				'%_transient_walkthecounty_total_api_keys%',
				'%_transient_walkthecounty_i18n_walkthecounty_promo_hide%',
				'%_transient_walkthecounty_contributors%',
				'%_transient_walkthecounty_estimated_monthly_stats%',
				'%_transient_walkthecounty_earnings_total%',
				'%_transient_walkthecounty_i18n_walkthecounty_%',
				'%_transient__walkthecounty_installed%',
				'%_transient__walkthecounty_activation_redirect%',
				'%_transient__walkthecounty_hide_license_notices_shortly_%',
				'%walkthecounty_income_total%',
			)
		),
		1
	);

	// User related transients.
	$user_apikey_options = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT user_id, meta_key
			FROM $wpdb->usermeta
			WHERE meta_value=%s",
			'walkthecounty_user_public_key'
		),
		ARRAY_A
	);

	if ( ! empty( $user_apikey_options ) ) {
		foreach ( $user_apikey_options as $user ) {
			$cached_options[] = '_transient_' . md5( 'walkthecounty_api_user_' . $user['meta_key'] );
			$cached_options[] = '_transient_' . md5( 'walkthecounty_api_user_public_key' . $user['user_id'] );
			$cached_options[] = '_transient_' . md5( 'walkthecounty_api_user_secret_key' . $user['user_id'] );
		}
	}

	if ( ! empty( $cached_options ) ) {
		foreach ( $cached_options as $option ) {
			switch ( true ) {
				case ( false !== strpos( $option, 'transient' ) ):
					$option = str_replace( '_transient_', '', $option );
					delete_transient( $option );
					break;

				default:
					delete_option( $option );
			}
		}
	}
}

/**
 * Update Capabilities for WalkTheCounty_Worker User Role.
 *
 * This upgrade routine will update access rights for WalkTheCounty_Worker User Role.
 *
 * @since      1.8.8
 */
function walkthecounty_v188_upgrades() {

	global $wp_roles;

	// Get the role object.
	$walkthecounty_worker = get_role( 'walkthecounty_worker' );

	// A list of capabilities to add for walkthecounty workers.
	$caps_to_add = array(
		'edit_posts',
		'edit_pages',
	);

	foreach ( $caps_to_add as $cap ) {
		// Add the capability.
		$walkthecounty_worker->add_cap( $cap );
	}

}

/**
 * Update Post meta for minimum and maximum amount for multi level donation forms
 *
 * This upgrade routine adds post meta for walkthecounty_forms CPT for multi level donation form.
 *
 * @since      1.8.9
 */
function walkthecounty_v189_upgrades_levels_post_meta_callback() {
	/* @var WalkTheCounty_Updates $walkthecounty_updates */
	$walkthecounty_updates = WalkTheCounty_Updates::get_instance();

	// form query.
	$donation_forms = new WP_Query(
		array(
			'paged'          => $walkthecounty_updates->step,
			'status'         => 'any',
			'order'          => 'ASC',
			'post_type'      => 'walkthecounty_forms',
			'posts_per_page' => 20,
		)
	);

	if ( $donation_forms->have_posts() ) {
		$walkthecounty_updates->set_percentage( $donation_forms->found_posts, ( $walkthecounty_updates->step * 20 ) );

		while ( $donation_forms->have_posts() ) {
			$donation_forms->the_post();
			$form_id = get_the_ID();

			// Remove formatting from _walkthecounty_set_price.
			update_post_meta(
				$form_id,
				'_walkthecounty_set_price',
				walkthecounty_sanitize_amount( get_post_meta( $form_id, '_walkthecounty_set_price', true ) )
			);

			// Remove formatting from _walkthecounty_custom_amount_minimum.
			update_post_meta(
				$form_id,
				'_walkthecounty_custom_amount_minimum',
				walkthecounty_sanitize_amount( get_post_meta( $form_id, '_walkthecounty_custom_amount_minimum', true ) )
			);

			// Bailout.
			if ( 'set' === get_post_meta( $form_id, '_walkthecounty_price_option', true ) ) {
				continue;
			}

			$donation_levels = get_post_meta( $form_id, '_walkthecounty_donation_levels', true );

			if ( ! empty( $donation_levels ) ) {

				foreach ( $donation_levels as $index => $donation_level ) {
					if ( isset( $donation_level['_walkthecounty_amount'] ) ) {
						$donation_levels[ $index ]['_walkthecounty_amount'] = walkthecounty_sanitize_amount( $donation_level['_walkthecounty_amount'] );
					}
				}

				update_post_meta( $form_id, '_walkthecounty_donation_levels', $donation_levels );

				$donation_levels_amounts = wp_list_pluck( $donation_levels, '_walkthecounty_amount' );

				$min_amount = min( $donation_levels_amounts );
				$max_amount = max( $donation_levels_amounts );

				// Set Minimum and Maximum amount for Multi Level Donation Forms
				walkthecounty_update_meta( $form_id, '_walkthecounty_levels_minimum_amount', $min_amount ? walkthecounty_sanitize_amount( $min_amount ) : 0 );
				walkthecounty_update_meta( $form_id, '_walkthecounty_levels_maximum_amount', $max_amount ? walkthecounty_sanitize_amount( $max_amount ) : 0 );
			}
		}

		/* Restore original Post Data */
		wp_reset_postdata();
	} else {
		// The Update Ran.
		walkthecounty_set_upgrade_complete( 'v189_upgrades_levels_post_meta' );
	}

}


/**
 * WalkTheCounty version 1.8.9 upgrades
 *
 * @since      1.8.9
 */
function walkthecounty_v189_upgrades() {
	/**
	 * 1. Remove user license related notice show blocked ( WalkTheCounty_Notice will handle )
	 */
	global $wpdb;

	// Delete permanent notice blocker.
	$wpdb->query(
		$wpdb->prepare(
			"
					DELETE FROM $wpdb->usermeta
					WHERE meta_key
					LIKE '%%%s%%'
					",
			'_walkthecounty_hide_license_notices_permanently'
		)
	);

	// Delete short notice blocker.
	$wpdb->query(
		$wpdb->prepare(
			"
					DELETE FROM $wpdb->options
					WHERE option_name
					LIKE '%%%s%%'
					",
			'__walkthecounty_hide_license_notices_shortly_'
		)
	);
}

/**
 * 2.0 Upgrades.
 *
 * @since  2.0
 * @return void
 */
function walkthecounty_v20_upgrades() {
	// Update cache setting.
	walkthecounty_update_option( 'cache', 'enabled' );

	// Upgrade email settings.
	walkthecounty_v20_upgrades_email_setting();
}

/**
 * Move old email api settings to new email setting api for following emails:
 *    1. new offline donation         [This was hard coded]
 *    2. offline donation instruction
 *    3. new donation
 *    4. donation receipt
 *
 * @since 2.0
 */
function walkthecounty_v20_upgrades_email_setting() {
	$all_setting = walkthecounty_get_settings();

	// Bailout on fresh install.
	if ( empty( $all_setting ) ) {
		return;
	}

	$settings = array(
		'offline_donation_subject'      => 'offline-donation-instruction_email_subject',
		'global_offline_donation_email' => 'offline-donation-instruction_email_message',
		'donation_subject'              => 'donation-receipt_email_subject',
		'donation_receipt'              => 'donation-receipt_email_message',
		'donation_notification_subject' => 'new-donation_email_subject',
		'donation_notification'         => 'new-donation_email_message',
		'admin_notice_emails'           => array(
			'new-donation_recipient',
			'new-offline-donation_recipient',
			'new-donor-register_recipient',
		),
		'admin_notices'                 => 'new-donation_notification',
	);

	foreach ( $settings as $old_setting => $new_setting ) {
		// Do not update already modified
		if ( ! is_array( $new_setting ) ) {
			if ( array_key_exists( $new_setting, $all_setting ) || ! array_key_exists( $old_setting, $all_setting ) ) {
				continue;
			}
		}

		switch ( $old_setting ) {
			case 'admin_notices':
				$notification_status = walkthecounty_get_option( $old_setting, 'enabled' );

				walkthecounty_update_option( $new_setting, $notification_status );

				// @todo: Delete this option later ( version > 2.0 ), We need this for per form email addon.
				// walkthecounty_delete_option( $old_setting );
				break;

			// @todo: Delete this option later ( version > 2.0 ) because we need this for backward compatibility walkthecounty_get_admin_notice_emails.
			case 'admin_notice_emails':
				$recipients = walkthecounty_get_admin_notice_emails();

				foreach ( $new_setting as $setting ) {
					// bailout if setting already exist.
					if ( array_key_exists( $setting, $all_setting ) ) {
						continue;
					}

					walkthecounty_update_option( $setting, $recipients );
				}
				break;

			default:
				walkthecounty_update_option( $new_setting, walkthecounty_get_option( $old_setting ) );
				walkthecounty_delete_option( $old_setting );
		}
	}
}

/**
 * WalkTheCounty version 1.8.9 upgrades
 *
 * @since 1.8.9
 */
function walkthecounty_v1812_upgrades() {
	/**
	 * Validate number format settings.
	 */
	$walkthecounty_settings        = walkthecounty_get_settings();
	$walkthecounty_setting_updated = false;

	if ( $walkthecounty_settings['thousands_separator'] === $walkthecounty_settings['decimal_separator'] ) {
		$walkthecounty_settings['number_decimals']   = 0;
		$walkthecounty_settings['decimal_separator'] = '';
		$walkthecounty_setting_updated               = true;

	} elseif ( empty( $walkthecounty_settings['decimal_separator'] ) ) {
		$walkthecounty_settings['number_decimals'] = 0;
		$walkthecounty_setting_updated             = true;

	} elseif ( 6 < absint( $walkthecounty_settings['number_decimals'] ) ) {
		$walkthecounty_settings['number_decimals'] = 5;
		$walkthecounty_setting_updated             = true;
	}

	if ( $walkthecounty_setting_updated ) {
		update_option( 'walkthecounty_settings', $walkthecounty_settings, false );
	}
}


/**
 * WalkTheCounty version 1.8.12 update
 *
 * Standardized amount values to six decimal
 *
 * @see        https://github.com/impress-org/walkthecounty/issues/1849#issuecomment-315128602
 *
 * @since      1.8.12
 */
function walkthecounty_v1812_update_amount_values_callback() {
	/* @var WalkTheCounty_Updates $walkthecounty_updates */
	$walkthecounty_updates = WalkTheCounty_Updates::get_instance();

	// form query.
	$donation_forms = new WP_Query(
		array(
			'paged'          => $walkthecounty_updates->step,
			'status'         => 'any',
			'order'          => 'ASC',
			'post_type'      => array( 'walkthecounty_forms', 'walkthecounty_payment' ),
			'posts_per_page' => 20,
		)
	);
	if ( $donation_forms->have_posts() ) {
		$walkthecounty_updates->set_percentage( $donation_forms->found_posts, ( $walkthecounty_updates->step * 20 ) );

		while ( $donation_forms->have_posts() ) {
			$donation_forms->the_post();
			global $post;

			$meta = get_post_meta( $post->ID );

			switch ( $post->post_type ) {
				case 'walkthecounty_forms':
					// _walkthecounty_set_price.
					if ( ! empty( $meta['_walkthecounty_set_price'][0] ) ) {
						update_post_meta( $post->ID, '_walkthecounty_set_price', walkthecounty_sanitize_amount_for_db( $meta['_walkthecounty_set_price'][0] ) );
					}

					// _walkthecounty_custom_amount_minimum.
					if ( ! empty( $meta['_walkthecounty_custom_amount_minimum'][0] ) ) {
						update_post_meta( $post->ID, '_walkthecounty_custom_amount_minimum', walkthecounty_sanitize_amount_for_db( $meta['_walkthecounty_custom_amount_minimum'][0] ) );
					}

					// _walkthecounty_levels_minimum_amount.
					if ( ! empty( $meta['_walkthecounty_levels_minimum_amount'][0] ) ) {
						update_post_meta( $post->ID, '_walkthecounty_levels_minimum_amount', walkthecounty_sanitize_amount_for_db( $meta['_walkthecounty_levels_minimum_amount'][0] ) );
					}

					// _walkthecounty_levels_maximum_amount.
					if ( ! empty( $meta['_walkthecounty_levels_maximum_amount'][0] ) ) {
						update_post_meta( $post->ID, '_walkthecounty_levels_maximum_amount', walkthecounty_sanitize_amount_for_db( $meta['_walkthecounty_levels_maximum_amount'][0] ) );
					}

					// _walkthecounty_set_goal.
					if ( ! empty( $meta['_walkthecounty_set_goal'][0] ) ) {
						update_post_meta( $post->ID, '_walkthecounty_set_goal', walkthecounty_sanitize_amount_for_db( $meta['_walkthecounty_set_goal'][0] ) );
					}

					// _walkthecounty_form_earnings.
					if ( ! empty( $meta['_walkthecounty_form_earnings'][0] ) ) {
						update_post_meta( $post->ID, '_walkthecounty_form_earnings', walkthecounty_sanitize_amount_for_db( $meta['_walkthecounty_form_earnings'][0] ) );
					}

					// _walkthecounty_custom_amount_minimum.
					if ( ! empty( $meta['_walkthecounty_donation_levels'][0] ) ) {
						$donation_levels = unserialize( $meta['_walkthecounty_donation_levels'][0] );

						foreach ( $donation_levels as $index => $level ) {
							if ( empty( $level['_walkthecounty_amount'] ) ) {
								continue;
							}

							$donation_levels[ $index ]['_walkthecounty_amount'] = walkthecounty_sanitize_amount_for_db( $level['_walkthecounty_amount'] );
						}

						$meta['_walkthecounty_donation_levels'] = $donation_levels;
						update_post_meta( $post->ID, '_walkthecounty_donation_levels', $meta['_walkthecounty_donation_levels'] );
					}

					break;

				case 'walkthecounty_payment':
					// _walkthecounty_payment_total.
					if ( ! empty( $meta['_walkthecounty_payment_total'][0] ) ) {
						update_post_meta( $post->ID, '_walkthecounty_payment_total', walkthecounty_sanitize_amount_for_db( $meta['_walkthecounty_payment_total'][0] ) );
					}

					break;
			}
		}

		/* Restore original Post Data */
		wp_reset_postdata();
	} else {
		// The Update Ran.
		walkthecounty_set_upgrade_complete( 'v1812_update_amount_values' );
	}
}


/**
 * WalkTheCounty version 1.8.12 update
 *
 * Standardized amount values to six decimal for donor
 *
 * @see        https://github.com/impress-org/walkthecounty/issues/1849#issuecomment-315128602
 *
 * @since      1.8.12
 */
function walkthecounty_v1812_update_donor_purchase_value_callback() {
	/* @var WalkTheCounty_Updates $walkthecounty_updates */
	$walkthecounty_updates = WalkTheCounty_Updates::get_instance();

	// form query.
	$donors = WalkTheCounty()->donors->get_donors(
		array(
			'number' => 20,
			'offset' => $walkthecounty_updates->get_offset( 20 ),
		)
	);

	if ( ! empty( $donors ) ) {
		$walkthecounty_updates->set_percentage( WalkTheCounty()->donors->count(), $walkthecounty_updates->get_offset( 20 ) );

		/* @var Object $donor */
		foreach ( $donors as $donor ) {
			WalkTheCounty()->donors->update( $donor->id, array( 'purchase_value' => walkthecounty_sanitize_amount_for_db( $donor->purchase_value ) ) );
		}
	} else {
		// The Update Ran.
		walkthecounty_set_upgrade_complete( 'v1812_update_donor_purchase_values' );
	}
}

/**
 * Upgrade routine for updating user roles for existing donors.
 *
 * @since 1.8.13
 */
function walkthecounty_v1813_update_donor_user_roles_callback() {
	/* @var WalkTheCounty_Updates $walkthecounty_updates */
	$walkthecounty_updates = WalkTheCounty_Updates::get_instance();

	// Fetch all the existing donors.
	$donors = WalkTheCounty()->donors->get_donors(
		array(
			'number' => 20,
			'offset' => $walkthecounty_updates->get_offset( 20 ),
		)
	);

	if ( ! empty( $donors ) ) {
		$walkthecounty_updates->set_percentage( WalkTheCounty()->donors->count(), $walkthecounty_updates->get_offset( 20 ) );

		/* @var Object $donor */
		foreach ( $donors as $donor ) {
			$user_id = $donor->user_id;

			// Proceed, if donor is attached with user.
			if ( $user_id ) {
				$user = get_userdata( $user_id );

				// Update user role, if user has subscriber role.
				if ( is_array( $user->roles ) && in_array( 'subscriber', $user->roles ) ) {
					wp_update_user(
						array(
							'ID'   => $user_id,
							'role' => 'walkthecounty_donor',
						)
					);
				}
			}
		}
	} else {
		// The Update Ran.
		walkthecounty_set_upgrade_complete( 'v1813_update_donor_user_roles' );
	}
}


/**
 * Version 1.8.13 automatic updates
 *
 * @since 1.8.13
 */
function walkthecounty_v1813_upgrades() {
	// Update admin setting.
	walkthecounty_update_option( 'donor_default_user_role', 'walkthecounty_donor' );

	// Update WalkTheCounty roles.
	$roles = new WalkTheCounty_Roles();
	$roles->add_roles();
	$roles->add_caps();
}

/**
 * Correct currency code for "Iranian Currency" for all of the payments.
 *
 * @since 1.8.17
 */
function walkthecounty_v1817_update_donation_iranian_currency_code() {
	/* @var WalkTheCounty_Updates $walkthecounty_updates */
	$walkthecounty_updates = WalkTheCounty_Updates::get_instance();

	// form query.
	$payments = new WP_Query(
		array(
			'paged'          => $walkthecounty_updates->step,
			'status'         => 'any',
			'order'          => 'ASC',
			'post_type'      => array( 'walkthecounty_payment' ),
			'posts_per_page' => 100,
		)
	);

	if ( $payments->have_posts() ) {
		$walkthecounty_updates->set_percentage( $payments->found_posts, ( $walkthecounty_updates->step * 100 ) );

		while ( $payments->have_posts() ) {
			$payments->the_post();

			$payment_meta = walkthecounty_get_payment_meta( get_the_ID() );

			if ( 'RIAL' === $payment_meta['currency'] ) {
				$payment_meta['currency'] = 'IRR';
				walkthecounty_update_meta( get_the_ID(), '_walkthecounty_payment_meta', $payment_meta );
			}
		}
	} else {
		// The Update Ran.
		walkthecounty_set_upgrade_complete( 'v1817_update_donation_iranian_currency_code' );
	}
}

/**
 * Correct currency code for "Iranian Currency" in WalkTheCounty setting.
 * Version 1.8.17 automatic updates
 *
 * @since 1.8.17
 */
function walkthecounty_v1817_upgrades() {
	$walkthecounty_settings = walkthecounty_get_settings();

	if ( 'RIAL' === $walkthecounty_settings['currency'] ) {
		$walkthecounty_settings['currency'] = 'IRR';
		update_option( 'walkthecounty_settings', $walkthecounty_settings, false );
	}
}

/**
 * Process Clean up of User Roles for more flexibility.
 *
 * @since 1.8.17
 */
function walkthecounty_v1817_process_cleanup_user_roles() {

	global $wp_roles;

	if ( ! ( $wp_roles instanceof WP_Roles ) ) {
		return;
	}

	// Add Capabilities to user roles as required.
	$add_caps = array(
		'administrator' => array(
			'view_walkthecounty_payments',
		),
	);

	// Remove Capabilities to user roles as required.
	$remove_caps = array(
		'walkthecounty_manager' => array(
			'edit_others_pages',
			'edit_others_posts',
			'delete_others_pages',
			'delete_others_posts',
			'manage_categories',
			'import',
			'export',
		),
	);

	foreach ( $add_caps as $role => $caps ) {
		foreach ( $caps as $cap ) {
			$wp_roles->add_cap( $role, $cap );
		}
	}

	foreach ( $remove_caps as $role => $caps ) {
		foreach ( $caps as $cap ) {
			$wp_roles->remove_cap( $role, $cap );
		}
	}

}

/**
 * Upgrade Routine - Clean up of User Roles for more flexibility.
 *
 * @since 1.8.17
 */
function walkthecounty_v1817_cleanup_user_roles() {
	/* @var WalkTheCounty_Updates $walkthecounty_updates */
	$walkthecounty_updates = WalkTheCounty_Updates::get_instance();

	walkthecounty_v1817_process_cleanup_user_roles();

	$walkthecounty_updates->percentage = 100;

	// Create WalkTheCounty plugin roles.
	$roles = new WalkTheCounty_Roles();
	$roles->add_roles();
	$roles->add_caps();

	walkthecounty_set_upgrade_complete( 'v1817_cleanup_user_roles' );
}

/**
 * Automatic Upgrade for release 1.8.18.
 *
 * @since 1.8.18
 */
function walkthecounty_v1818_upgrades() {

	// Remove email_access_installed from walkthecounty_settings.
	walkthecounty_delete_option( 'email_access_installed' );
}

/**
 * Upgrade Routine - Assigns Custom Amount to existing donation of type set donation.
 *
 * @since 1.8.18
 */
function walkthecounty_v1818_assign_custom_amount_set_donation() {

	/* @var WalkTheCounty_Updates $walkthecounty_updates */
	$walkthecounty_updates = WalkTheCounty_Updates::get_instance();

	$donations = new WP_Query(
		array(
			'paged'          => $walkthecounty_updates->step,
			'status'         => 'any',
			'order'          => 'ASC',
			'post_type'      => array( 'walkthecounty_payment' ),
			'posts_per_page' => 100,
		)
	);

	if ( $donations->have_posts() ) {
		$walkthecounty_updates->set_percentage( $donations->found_posts, $walkthecounty_updates->step * 100 );

		while ( $donations->have_posts() ) {
			$donations->the_post();

			$form          = new WalkTheCounty_Donate_Form( walkthecounty_get_meta( get_the_ID(), '_walkthecounty_payment_form_id', true ) );
			$donation_meta = walkthecounty_get_payment_meta( get_the_ID() );

			// Update Donation meta with price_id set as custom, only if it is:
			// 1. Donation Type = Set Donation.
			// 2. Donation Price Id is not set to custom.
			// 3. Form has not enabled custom price and donation amount assures that it is custom amount.
			if (
				$form->ID &&
				$form->is_set_type_donation_form() &&
				( 'custom' !== $donation_meta['price_id'] ) &&
				$form->is_custom_price( walkthecounty_get_meta( get_the_ID(), '_walkthecounty_payment_total', true ) )
			) {
				$donation_meta['price_id'] = 'custom';
				walkthecounty_update_meta( get_the_ID(), '_walkthecounty_payment_meta', $donation_meta );
				walkthecounty_update_meta( get_the_ID(), '_walkthecounty_payment_price_id', 'custom' );
			}
		}

		wp_reset_postdata();
	} else {
		// Update Ran Successfully.
		walkthecounty_set_upgrade_complete( 'v1818_assign_custom_amount_set_donation' );
	}
}

/**
 * Upgrade Routine - Removed WalkTheCounty Worker caps.
 *
 * See: https://github.com/impress-org/walkthecounty/issues/2476
 *
 * @since 1.8.18
 */
function walkthecounty_v1818_walkthecounty_worker_role_cleanup() {

	/* @var WalkTheCounty_Updates $walkthecounty_updates */
	$walkthecounty_updates = WalkTheCounty_Updates::get_instance();

	global $wp_roles;

	if ( ! ( $wp_roles instanceof WP_Roles ) ) {
		return;
	}

	// Remove Capabilities to user roles as required.
	$remove_caps = array(
		'walkthecounty_worker' => array(
			'delete_walkthecounty_payments',
			'delete_others_walkthecounty_payments',
			'delete_private_walkthecounty_payments',
			'delete_published_walkthecounty_payments',
			'edit_others_walkthecounty_payments',
			'edit_private_walkthecounty_payments',
			'edit_published_walkthecounty_payments',
			'read_private_walkthecounty_payments',
		),
	);

	foreach ( $remove_caps as $role => $caps ) {
		foreach ( $caps as $cap ) {
			$wp_roles->remove_cap( $role, $cap );
		}
	}

	$walkthecounty_updates->percentage = 100;

	// Create WalkTheCounty plugin roles.
	$roles = new WalkTheCounty_Roles();
	$roles->add_roles();
	$roles->add_caps();

	walkthecounty_set_upgrade_complete( 'v1818_walkthecounty_worker_role_cleanup' );
}

/**
 *
 * Upgrade form metadata for new metabox settings.
 *
 * @since  2.0
 * @return void
 */
function walkthecounty_v20_upgrades_form_metadata_callback() {
	$walkthecounty_updates = WalkTheCounty_Updates::get_instance();

	// form query
	$forms = new WP_Query(
		array(
			'paged'          => $walkthecounty_updates->step,
			'status'         => 'any',
			'order'          => 'ASC',
			'post_type'      => 'walkthecounty_forms',
			'posts_per_page' => 100,
		)
	);

	if ( $forms->have_posts() ) {
		$walkthecounty_updates->set_percentage( $forms->found_posts, ( $walkthecounty_updates->step * 100 ) );

		while ( $forms->have_posts() ) {
			$forms->the_post();
			global $post;

			// Update offline instruction email notification status.
			$offline_instruction_notification_status = get_post_meta( get_the_ID(), '_walkthecounty_customize_offline_donations', true );
			$offline_instruction_notification_status = walkthecounty_is_setting_enabled(
				$offline_instruction_notification_status, array(
					'enabled',
					'global',
				)
			)
				? $offline_instruction_notification_status
				: 'global';
			update_post_meta( get_the_ID(), '_walkthecounty_offline-donation-instruction_notification', $offline_instruction_notification_status );

			// Update offline instruction email message.
			update_post_meta(
				get_the_ID(),
				'_walkthecounty_offline-donation-instruction_email_message',
				get_post_meta(
					get_the_ID(),
					// @todo: Delete this option later ( version > 2.0 ).
					'_walkthecounty_offline_donation_email',
					true
				)
			);

			// Update offline instruction email subject.
			update_post_meta(
				get_the_ID(),
				'_walkthecounty_offline-donation-instruction_email_subject',
				get_post_meta(
					get_the_ID(),
					// @todo: Delete this option later ( version > 2.0 ).
					'_walkthecounty_offline_donation_subject',
					true
				)
			);

		}// End while().

		wp_reset_postdata();
	} else {
		// No more forms found, finish up.
		walkthecounty_set_upgrade_complete( 'v20_upgrades_form_metadata' );
	}
}


/**
 * Upgrade payment metadata for new metabox settings.
 *
 * @since  2.0
 * @global wpdb $wpdb
 * @return void
 */
function walkthecounty_v20_upgrades_payment_metadata_callback() {
	global $wpdb;
	$walkthecounty_updates = WalkTheCounty_Updates::get_instance();

	// form query
	$forms = new WP_Query(
		array(
			'paged'          => $walkthecounty_updates->step,
			'status'         => 'any',
			'order'          => 'ASC',
			'post_type'      => 'walkthecounty_payment',
			'posts_per_page' => 100,
		)
	);

	if ( $forms->have_posts() ) {
		$walkthecounty_updates->set_percentage( $forms->found_posts, ( $walkthecounty_updates->step * 100 ) );

		while ( $forms->have_posts() ) {
			$forms->the_post();
			global $post;

			// Split _walkthecounty_payment_meta meta.
			// @todo Remove _walkthecounty_payment_meta after releases 2.0
			$payment_meta = walkthecounty_get_meta( $post->ID, '_walkthecounty_payment_meta', true );

			if ( ! empty( $payment_meta ) ) {
				_walkthecounty_20_bc_split_and_save_walkthecounty_payment_meta( $post->ID, $payment_meta );
			}

			$deprecated_meta_keys = array(
				'_walkthecounty_payment_customer_id' => '_walkthecounty_payment_donor_id',
				'_walkthecounty_payment_user_email'  => '_walkthecounty_payment_donor_email',
				'_walkthecounty_payment_user_ip'     => '_walkthecounty_payment_donor_ip',
			);

			foreach ( $deprecated_meta_keys as $old_meta_key => $new_meta_key ) {
				// Do not add new meta key if already exist.
				if ( $wpdb->get_var( $wpdb->prepare( "SELECT meta_id FROM $wpdb->postmeta WHERE post_id=%d AND meta_key=%s", $post->ID, $new_meta_key ) ) ) {
					continue;
				}

				$wpdb->insert(
					$wpdb->postmeta,
					array(
						'post_id'    => $post->ID,
						'meta_key'   => $new_meta_key,
						'meta_value' => walkthecounty_get_meta( $post->ID, $old_meta_key, true ),
					)
				);
			}

			// Bailout
			if ( $donor_id = walkthecounty_get_meta( $post->ID, '_walkthecounty_payment_donor_id', true ) ) {
				/* @var WalkTheCounty_Donor $donor */
				$donor = new WalkTheCounty_Donor( $donor_id );

				$address['line1']   = walkthecounty_get_meta( $post->ID, '_walkthecounty_donor_billing_address1', true, '' );
				$address['line2']   = walkthecounty_get_meta( $post->ID, '_walkthecounty_donor_billing_address2', true, '' );
				$address['city']    = walkthecounty_get_meta( $post->ID, '_walkthecounty_donor_billing_city', true, '' );
				$address['state']   = walkthecounty_get_meta( $post->ID, '_walkthecounty_donor_billing_state', true, '' );
				$address['zip']     = walkthecounty_get_meta( $post->ID, '_walkthecounty_donor_billing_zip', true, '' );
				$address['country'] = walkthecounty_get_meta( $post->ID, '_walkthecounty_donor_billing_country', true, '' );

				// Save address.
				$donor->add_address( 'billing[]', $address );
			}
		}// End while().

		wp_reset_postdata();
	} else {
		// @todo Delete user id meta after releases 2.0
		// $wpdb->get_var( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE meta_key=%s", '_walkthecounty_payment_user_id' ) );
		// No more forms found, finish up.
		walkthecounty_set_upgrade_complete( 'v20_upgrades_payment_metadata' );
	}
}


/**
 * Upgrade logs data.
 *
 * @since  2.0
 * @return void
 */
function walkthecounty_v20_logs_upgrades_callback() {
	global $wpdb;
	$walkthecounty_updates = WalkTheCounty_Updates::get_instance();

	// form query
	$forms = new WP_Query(
		array(
			'paged'          => $walkthecounty_updates->step,
			'order'          => 'DESC',
			'post_type'      => 'walkthecounty_log',
			'post_status'    => 'any',
			'posts_per_page' => 100,
		)
	);

	if ( $forms->have_posts() ) {
		$walkthecounty_updates->set_percentage( $forms->found_posts, $walkthecounty_updates->step * 100 );

		while ( $forms->have_posts() ) {
			$forms->the_post();
			global $post;

			if ( $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}walkthecounty_logs WHERE ID=%d", $post->ID ) ) ) {
				continue;
			}

			$term      = get_the_terms( $post->ID, 'walkthecounty_log_type' );
			$term      = ! is_wp_error( $term ) && ! empty( $term ) ? $term[0] : array();
			$term_name = ! empty( $term ) ? $term->slug : '';

			$log_data = array(
				'ID'           => $post->ID,
				'log_title'    => $post->post_title,
				'log_content'  => $post->post_content,
				'log_parent'   => 0,
				'log_type'     => $term_name,
				'log_date'     => $post->post_date,
				'log_date_gmt' => $post->post_date_gmt,
			);
			$log_meta = array();

			if ( $old_log_meta = get_post_meta( $post->ID ) ) {
				foreach ( $old_log_meta as $meta_key => $meta_value ) {
					switch ( $meta_key ) {
						case '_walkthecounty_log_payment_id':
							$log_data['log_parent']        = current( $meta_value );
							$log_meta['_walkthecounty_log_form_id'] = $post->post_parent;
							break;

						default:
							$log_meta[ $meta_key ] = current( $meta_value );
					}
				}
			}

			if ( 'api_request' === $term_name ) {
				$log_meta['_walkthecounty_log_api_query'] = $post->post_excerpt;
			}

			$wpdb->insert( "{$wpdb->prefix}walkthecounty_logs", $log_data );

			if ( ! empty( $log_meta ) ) {
				foreach ( $log_meta as $meta_key => $meta_value ) {
					WalkTheCounty()->logs->logmeta_db->update_meta( $post->ID, $meta_key, $meta_value );
				}
			}

			$logIDs[] = $post->ID;
		}// End while().

		wp_reset_postdata();
	} else {
		// @todo: Delete terms and taxonomy after releases 2.0.
		/*
		$terms = get_terms( 'walkthecounty_log_type', array( 'fields' => 'ids', 'hide_empty' => false ) );
		if ( ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				wp_delete_term( $term, 'walkthecounty_log_type' );
			}
		}*/

		// @todo: Delete logs after releases 2.0.
		/*
		$logIDs = get_posts( array(
				'order'          => 'DESC',
				'post_type'      => 'walkthecounty_log',
				'post_status'    => 'any',
				'posts_per_page' => - 1,
				'fields'         => 'ids',
			)
		);*/

		/*
		if ( ! empty( $logIDs ) ) {
			foreach ( $logIDs as $log ) {
				// Delete term relationship and posts.
				wp_delete_object_term_relationships( $log, 'walkthecounty_log_type' );
				wp_delete_post( $log, true );
			}
		}*/

		// @todo: Unregister taxonomy after releases 2.0.
		/*unregister_taxonomy( 'walkthecounty_log_type' );*/

		// Delete log cache.
		WalkTheCounty()->logs->delete_cache();

		// No more forms found, finish up.
		walkthecounty_set_upgrade_complete( 'v20_logs_upgrades' );
	}
}


/**
 * Move payment and form metadata to new table
 *
 * @since  2.0
 * @return void
 */
function walkthecounty_v20_move_metadata_into_new_table_callback() {
	global $wpdb;
	$walkthecounty_updates = WalkTheCounty_Updates::get_instance();

	// form query
	$payments = new WP_Query(
		array(
			'paged'          => $walkthecounty_updates->step,
			'status'         => 'any',
			'order'          => 'ASC',
			'post_type'      => array( 'walkthecounty_forms', 'walkthecounty_payment' ),
			'posts_per_page' => 100,
		)
	);

	if ( $payments->have_posts() ) {
		$walkthecounty_updates->set_percentage( $payments->found_posts, $walkthecounty_updates->step * 100 );

		while ( $payments->have_posts() ) {
			$payments->the_post();
			global $post;

			$meta_data = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM $wpdb->postmeta where post_id=%d",
					get_the_ID()
				),
				ARRAY_A
			);

			if ( ! empty( $meta_data ) ) {
				foreach ( $meta_data as $index => $data ) {
					// Check for duplicate meta values.
					if ( $result = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . ( 'walkthecounty_forms' === $post->post_type ? $wpdb->formmeta : $wpdb->paymentmeta ) . ' WHERE meta_id=%d', $data['meta_id'] ), ARRAY_A ) ) {
						continue;
					}

					switch ( $post->post_type ) {
						case 'walkthecounty_forms':
							$data['form_id'] = $data['post_id'];
							unset( $data['post_id'] );

							WalkTheCounty()->form_meta->insert( $data );
							// @todo: delete form meta from post meta table after releases 2.0.
							/*delete_post_meta( get_the_ID(), $data['meta_key'] );*/

							break;

						case 'walkthecounty_payment':
							$data['payment_id'] = $data['post_id'];
							unset( $data['post_id'] );

							WalkTheCounty()->payment_meta->insert( $data );

							// @todo: delete donation meta from post meta table after releases 2.0.
							/*delete_post_meta( get_the_ID(), $data['meta_key'] );*/

							break;
					}
				}
			}
		}// End while().

		wp_reset_postdata();
	} else {
		// No more forms found, finish up.
		walkthecounty_set_upgrade_complete( 'v20_move_metadata_into_new_table' );
	}

}

/**
 * Upgrade routine for splitting donor name into first name and last name.
 *
 * @since 2.0
 *
 * @return void
 */
function walkthecounty_v20_upgrades_donor_name() {
	/* @var WalkTheCounty_Updates $walkthecounty_updates */
	$walkthecounty_updates = WalkTheCounty_Updates::get_instance();

	$donors = WalkTheCounty()->donors->get_donors(
		array(
			'paged'  => $walkthecounty_updates->step,
			'number' => 100,
		)
	);

	if ( $donors ) {
		$walkthecounty_updates->set_percentage( count( $donors ), $walkthecounty_updates->step * 100 );
		// Loop through Donors
		foreach ( $donors as $donor ) {

			$donor_name       = explode( ' ', $donor->name, 2 );
			$donor_first_name = WalkTheCounty()->donor_meta->get_meta( $donor->id, '_walkthecounty_donor_first_name' );
			$donor_last_name  = WalkTheCounty()->donor_meta->get_meta( $donor->id, '_walkthecounty_donor_last_name' );

			// If first name meta of donor is not created, then create it.
			if ( ! $donor_first_name && isset( $donor_name[0] ) ) {
				WalkTheCounty()->donor_meta->add_meta( $donor->id, '_walkthecounty_donor_first_name', $donor_name[0] );
			}

			// If last name meta of donor is not created, then create it.
			if ( ! $donor_last_name && isset( $donor_name[1] ) ) {
				WalkTheCounty()->donor_meta->add_meta( $donor->id, '_walkthecounty_donor_last_name', $donor_name[1] );
			}

			// If Donor is connected with WP User then update user meta.
			if ( $donor->user_id ) {
				if ( isset( $donor_name[0] ) ) {
					update_user_meta( $donor->user_id, 'first_name', $donor_name[0] );
				}
				if ( isset( $donor_name[1] ) ) {
					update_user_meta( $donor->user_id, 'last_name', $donor_name[1] );
				}
			}
		}
	} else {
		// The Update Ran.
		walkthecounty_set_upgrade_complete( 'v20_upgrades_donor_name' );
	}

}

/**
 * Upgrade routine for user addresses.
 *
 * @since 2.0
 * @global wpdb $wpdb
 *
 * @return void
 */
function walkthecounty_v20_upgrades_user_address() {
	global $wpdb;

	/* @var WalkTheCounty_Updates $walkthecounty_updates */
	$walkthecounty_updates = WalkTheCounty_Updates::get_instance();

	/* @var WP_User_Query $user_query */
	$user_query = new WP_User_Query(
		array(
			'number' => 100,
			'paged'  => $walkthecounty_updates->step,
		)
	);

	$users = $user_query->get_results();

	if ( $users ) {
		$walkthecounty_updates->set_percentage( $user_query->get_total(), $walkthecounty_updates->step * 100 );

		// Loop through Donors
		foreach ( $users as $user ) {
			/* @var WalkTheCounty_Donor $donor */
			$donor = new WalkTheCounty_Donor( $user->ID, true );

			if ( ! $donor->id ) {
				continue;
			}

			$address = $wpdb->get_var(
				$wpdb->prepare(
					"
					SELECT meta_value FROM {$wpdb->usermeta}
					WHERE user_id=%s
					AND meta_key=%s
					",
					$user->ID,
					'_walkthecounty_user_address'
				)
			);

			if ( ! empty( $address ) ) {
				$address = maybe_unserialize( $address );
				$donor->add_address( 'personal', $address );
				$donor->add_address( 'billing[]', $address );

				// @todo: delete _walkthecounty_user_address from user meta after releases 2.0.
				/*delete_user_meta( $user->ID, '_walkthecounty_user_address' );*/
			}
		}
	} else {
		// The Update Ran.
		walkthecounty_set_upgrade_complete( 'v20_upgrades_user_address' );
	}

}

/**
 * Upgrade logs data.
 *
 * @since  2.0
 * @global wpdb $wpdb
 * @return void
 */
function walkthecounty_v20_rename_donor_tables_callback() {
	global $wpdb;

	/* @var WalkTheCounty_Updates $walkthecounty_updates */
	$walkthecounty_updates = WalkTheCounty_Updates::get_instance();

	$tables = array(
		"{$wpdb->prefix}walkthecounty_customers"    => "{$wpdb->prefix}walkthecounty_donors",
		"{$wpdb->prefix}walkthecounty_customermeta" => "{$wpdb->prefix}walkthecounty_donormeta",
	);

	// Alter customer table
	foreach ( $tables as $old_table => $new_table ) {
		if (
			$wpdb->query( $wpdb->prepare( 'SHOW TABLES LIKE %s', $old_table ) ) &&
			! $wpdb->query( $wpdb->prepare( 'SHOW TABLES LIKE %s', $new_table ) )
		) {
			$wpdb->query( "ALTER TABLE {$old_table} RENAME TO {$new_table}" );

			if ( "{$wpdb->prefix}walkthecounty_donormeta" === $new_table ) {
				$wpdb->query( "ALTER TABLE {$new_table} CHANGE COLUMN customer_id donor_id bigint(20)" );
			}
		}
	}

	$walkthecounty_updates->percentage = 100;

	// No more forms found, finish up.
	walkthecounty_set_upgrade_complete( 'v20_rename_donor_tables' );

	// Re initiate donor classes.
	WalkTheCounty()->donors     = new WalkTheCounty_DB_Donors();
	WalkTheCounty()->donor_meta = new WalkTheCounty_DB_Donor_Meta();
}


/**
 * Create missing meta tables.
 *
 * @since  2.0.1
 * @global wpdb $wpdb
 * @return void
 */
function walkthecounty_v201_create_tables() {
	global $wpdb;

	if ( ! $wpdb->query( $wpdb->prepare( 'SHOW TABLES LIKE %s', "{$wpdb->prefix}walkthecounty_paymentmeta" ) ) ) {
		WalkTheCounty()->payment_meta->create_table();
	}

	if ( ! $wpdb->query( $wpdb->prepare( 'SHOW TABLES LIKE %s', "{$wpdb->prefix}walkthecounty_formmeta" ) ) ) {
		WalkTheCounty()->form_meta->create_table();
	}

	if ( ! $wpdb->query( $wpdb->prepare( 'SHOW TABLES LIKE %s', "{$wpdb->prefix}walkthecounty_logs" ) ) ) {
		WalkTheCounty()->logs->log_db->create_table();
	}

	if ( ! $wpdb->query( $wpdb->prepare( 'SHOW TABLES LIKE %s', "{$wpdb->prefix}walkthecounty_logmeta" ) ) ) {
		WalkTheCounty()->logs->logmeta_db->create_table();
	}
}

/**
 * Upgrade payment metadata for new metabox settings.
 *
 * @since  2.0.1
 * @global wpdb $wpdb
 * @return void
 */
function walkthecounty_v201_upgrades_payment_metadata_callback() {
	global $wpdb, $post;
	$walkthecounty_updates = WalkTheCounty_Updates::get_instance();
	walkthecounty_v201_create_tables();

	$payments = $wpdb->get_col(
		"
			SELECT ID FROM $wpdb->posts
			WHERE 1=1
			AND ( 
  				$wpdb->posts.post_date >= '2018-01-08 00:00:00'
			)
			AND $wpdb->posts.post_type = 'walkthecounty_payment'
			AND {$wpdb->posts}.post_status IN ('" . implode( "','", array_keys( walkthecounty_get_payment_statuses() ) ) . "')
			ORDER BY $wpdb->posts.post_date ASC 
			LIMIT 100
			OFFSET " . $walkthecounty_updates->get_offset( 100 )
	);

	if ( ! empty( $payments ) ) {
		$walkthecounty_updates->set_percentage( walkthecounty_get_total_post_type_count( 'walkthecounty_payment' ), ( $walkthecounty_updates->step * 100 ) );

		foreach ( $payments as $payment_id ) {
			$post = get_post( $payment_id );
			setup_postdata( $post );

			// Do not add new meta keys if already refactored.
			if ( $wpdb->get_var( $wpdb->prepare( "SELECT meta_id FROM $wpdb->postmeta WHERE post_id=%d AND meta_key=%s", $post->ID, '_walkthecounty_payment_donor_id' ) ) ) {
				continue;
			}

			// Split _walkthecounty_payment_meta meta.
			// @todo Remove _walkthecounty_payment_meta after releases 2.0
			$payment_meta = walkthecounty_get_meta( $post->ID, '_walkthecounty_payment_meta', true );

			if ( ! empty( $payment_meta ) ) {
				_walkthecounty_20_bc_split_and_save_walkthecounty_payment_meta( $post->ID, $payment_meta );
			}

			$deprecated_meta_keys = array(
				'_walkthecounty_payment_customer_id' => '_walkthecounty_payment_donor_id',
				'_walkthecounty_payment_user_email'  => '_walkthecounty_payment_donor_email',
				'_walkthecounty_payment_user_ip'     => '_walkthecounty_payment_donor_ip',
			);

			foreach ( $deprecated_meta_keys as $old_meta_key => $new_meta_key ) {
				// Do not add new meta key if already exist.
				if ( $wpdb->get_var( $wpdb->prepare( "SELECT meta_id FROM $wpdb->postmeta WHERE post_id=%d AND meta_key=%s", $post->ID, $new_meta_key ) ) ) {
					continue;
				}

				$wpdb->insert(
					$wpdb->postmeta,
					array(
						'post_id'    => $post->ID,
						'meta_key'   => $new_meta_key,
						'meta_value' => walkthecounty_get_meta( $post->ID, $old_meta_key, true ),
					)
				);
			}

			// Bailout
			if ( $donor_id = walkthecounty_get_meta( $post->ID, '_walkthecounty_payment_donor_id', true ) ) {
				/* @var WalkTheCounty_Donor $donor */
				$donor = new WalkTheCounty_Donor( $donor_id );

				$address['line1']   = walkthecounty_get_meta( $post->ID, '_walkthecounty_donor_billing_address1', true, '' );
				$address['line2']   = walkthecounty_get_meta( $post->ID, '_walkthecounty_donor_billing_address2', true, '' );
				$address['city']    = walkthecounty_get_meta( $post->ID, '_walkthecounty_donor_billing_city', true, '' );
				$address['state']   = walkthecounty_get_meta( $post->ID, '_walkthecounty_donor_billing_state', true, '' );
				$address['zip']     = walkthecounty_get_meta( $post->ID, '_walkthecounty_donor_billing_zip', true, '' );
				$address['country'] = walkthecounty_get_meta( $post->ID, '_walkthecounty_donor_billing_country', true, '' );

				// Save address.
				$donor->add_address( 'billing[]', $address );
			}
		}// End while().

		wp_reset_postdata();
	} else {
		// @todo Delete user id meta after releases 2.0
		// $wpdb->get_var( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE meta_key=%s", '_walkthecounty_payment_user_id' ) );
		// No more forms found, finish up.
		walkthecounty_set_upgrade_complete( 'v201_upgrades_payment_metadata' );
	}
}

/**
 * Move payment and form metadata to new table
 *
 * @since  2.0.1
 * @return void
 */
function walkthecounty_v201_move_metadata_into_new_table_callback() {
	global $wpdb, $post;
	$walkthecounty_updates = WalkTheCounty_Updates::get_instance();
	walkthecounty_v201_create_tables();

	$payments = $wpdb->get_col(
		"
			SELECT ID FROM $wpdb->posts 
			WHERE 1=1
			AND ( $wpdb->posts.post_type = 'walkthecounty_payment' OR $wpdb->posts.post_type = 'walkthecounty_forms' )
			AND {$wpdb->posts}.post_status IN ('" . implode( "','", array_keys( walkthecounty_get_payment_statuses() ) ) . "')
			ORDER BY $wpdb->posts.post_date ASC 
			LIMIT 100
			OFFSET " . $walkthecounty_updates->get_offset( 100 )
	);

	if ( ! empty( $payments ) ) {
		$walkthecounty_updates->set_percentage(
			walkthecounty_get_total_post_type_count(
				array(
					'walkthecounty_forms',
					'walkthecounty_payment',
				)
			), $walkthecounty_updates->step * 100
		);

		foreach ( $payments as $payment_id ) {
			$post = get_post( $payment_id );
			setup_postdata( $post );

			$meta_data = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM $wpdb->postmeta where post_id=%d",
					get_the_ID()
				),
				ARRAY_A
			);

			if ( ! empty( $meta_data ) ) {
				foreach ( $meta_data as $index => $data ) {
					// Check for duplicate meta values.
					if ( $result = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . ( 'walkthecounty_forms' === $post->post_type ? $wpdb->formmeta : $wpdb->paymentmeta ) . ' WHERE meta_id=%d', $data['meta_id'] ), ARRAY_A ) ) {
						continue;
					}

					switch ( $post->post_type ) {
						case 'walkthecounty_forms':
							$data['form_id'] = $data['post_id'];
							unset( $data['post_id'] );

							WalkTheCounty()->form_meta->insert( $data );
							// @todo: delete form meta from post meta table after releases 2.0.
							/*delete_post_meta( get_the_ID(), $data['meta_key'] );*/

							break;

						case 'walkthecounty_payment':
							$data['payment_id'] = $data['post_id'];
							unset( $data['post_id'] );

							WalkTheCounty()->payment_meta->insert( $data );

							// @todo: delete donation meta from post meta table after releases 2.0.
							/*delete_post_meta( get_the_ID(), $data['meta_key'] );*/

							break;
					}
				}
			}
		}// End while().

		wp_reset_postdata();
	} else {
		// No more forms found, finish up.
		walkthecounty_set_upgrade_complete( 'v201_move_metadata_into_new_table' );
	}

}

/**
 * Move data to new log table.
 *
 * @since  2.0.1
 * @return void
 */
function walkthecounty_v201_logs_upgrades_callback() {
	global $wpdb, $post;
	$walkthecounty_updates = WalkTheCounty_Updates::get_instance();
	walkthecounty_v201_create_tables();

	$logs = $wpdb->get_col(
		"
			SELECT ID FROM $wpdb->posts 
			WHERE 1=1
			AND $wpdb->posts.post_type = 'walkthecounty_log'
			AND {$wpdb->posts}.post_status IN ('" . implode( "','", array_keys( walkthecounty_get_payment_statuses() ) ) . "')
			ORDER BY $wpdb->posts.post_date ASC 
			LIMIT 100
			OFFSET " . $walkthecounty_updates->get_offset( 100 )
	);

	if ( ! empty( $logs ) ) {
		$walkthecounty_updates->set_percentage( walkthecounty_get_total_post_type_count( 'walkthecounty_log' ), $walkthecounty_updates->step * 100 );

		foreach ( $logs as $log_id ) {
			$post = get_post( $log_id );
			setup_postdata( $post );

			if ( $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}walkthecounty_logs WHERE ID=%d", $post->ID ) ) ) {
				continue;
			}

			$term      = get_the_terms( $post->ID, 'walkthecounty_log_type' );
			$term      = ! is_wp_error( $term ) && ! empty( $term ) ? $term[0] : array();
			$term_name = ! empty( $term ) ? $term->slug : '';

			$log_data = array(
				'ID'           => $post->ID,
				'log_title'    => $post->post_title,
				'log_content'  => $post->post_content,
				'log_parent'   => 0,
				'log_type'     => $term_name,
				'log_date'     => $post->post_date,
				'log_date_gmt' => $post->post_date_gmt,
			);
			$log_meta = array();

			if ( $old_log_meta = get_post_meta( $post->ID ) ) {
				foreach ( $old_log_meta as $meta_key => $meta_value ) {
					switch ( $meta_key ) {
						case '_walkthecounty_log_payment_id':
							$log_data['log_parent']        = current( $meta_value );
							$log_meta['_walkthecounty_log_form_id'] = $post->post_parent;
							break;

						default:
							$log_meta[ $meta_key ] = current( $meta_value );
					}
				}
			}

			if ( 'api_request' === $term_name ) {
				$log_meta['_walkthecounty_log_api_query'] = $post->post_excerpt;
			}

			$wpdb->insert( "{$wpdb->prefix}walkthecounty_logs", $log_data );

			if ( ! empty( $log_meta ) ) {
				foreach ( $log_meta as $meta_key => $meta_value ) {
					WalkTheCounty()->logs->logmeta_db->update_meta( $post->ID, $meta_key, $meta_value );
				}
			}

			$logIDs[] = $post->ID;
		}// End while().

		wp_reset_postdata();
	} else {
		// Delete log cache.
		WalkTheCounty()->logs->delete_cache();

		// No more forms found, finish up.
		walkthecounty_set_upgrade_complete( 'v201_logs_upgrades' );
	}
}


/**
 * Add missing donor.
 *
 * @since  2.0.1
 * @return void
 */
function walkthecounty_v201_add_missing_donors_callback() {
	global $wpdb;
	walkthecounty_v201_create_tables();

	$walkthecounty_updates = WalkTheCounty_Updates::get_instance();

	// Bailout.
	if ( ! $wpdb->query( $wpdb->prepare( 'SHOW TABLES LIKE %s', "{$wpdb->prefix}walkthecounty_customers" ) ) ) {
		WalkTheCounty_Updates::get_instance()->percentage = 100;
		walkthecounty_set_upgrade_complete( 'v201_add_missing_donors' );
	}

	$total_customers = $wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}walkthecounty_customers " );
	$customers       = wp_list_pluck( $wpdb->get_results( "SELECT id FROM {$wpdb->prefix}walkthecounty_customers LIMIT 20 OFFSET " . $walkthecounty_updates->get_offset( 20 ) ), 'id' );
	$donors          = wp_list_pluck( $wpdb->get_results( "SELECT id FROM {$wpdb->prefix}walkthecounty_donors" ), 'id' );

	if ( ! empty( $customers ) ) {
		$walkthecounty_updates->set_percentage( $total_customers, ( $walkthecounty_updates->step * 20 ) );

		$missing_donors = array_diff( $customers, $donors );
		$donor_data     = array();

		if ( $missing_donors ) {
			foreach ( $missing_donors as $donor_id ) {
				$donor_data[] = array(
					'info' => $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}walkthecounty_customers WHERE id=%d", $donor_id ) ),
					'meta' => $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}walkthecounty_customermeta WHERE customer_id=%d", $donor_id ) ),

				);
			}
		}

		if ( ! empty( $donor_data ) ) {
			$donor_table_name      = WalkTheCounty()->donors->table_name;
			$donor_meta_table_name = WalkTheCounty()->donor_meta->table_name;

			WalkTheCounty()->donors->table_name     = "{$wpdb->prefix}walkthecounty_donors";
			WalkTheCounty()->donor_meta->table_name = "{$wpdb->prefix}walkthecounty_donormeta";

			foreach ( $donor_data as $donor ) {
				$donor['info'][0] = (array) $donor['info'][0];

				// Prevent duplicate meta id issue.
				if ( $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}walkthecounty_donors WHERE id=%d", $donor['info'][0]['id'] ) ) ) {
					continue;
				}

				$donor_id = WalkTheCounty()->donors->add( $donor['info'][0] );

				if ( ! empty( $donor['meta'] ) ) {
					foreach ( $donor['meta'] as $donor_meta ) {
						$donor_meta = (array) $donor_meta;

						// Prevent duplicate meta id issue.
						if ( $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}walkthecounty_donormeta WHERE meta_id=%d", $donor_meta['meta_id'] ) ) ) {
							unset( $donor_meta['meta_id'] );
						}

						$donor_meta['donor_id'] = $donor_meta['customer_id'];
						unset( $donor_meta['customer_id'] );

						WalkTheCounty()->donor_meta->insert( $donor_meta );
					}
				}

				/**
				 * Fix donor name and address
				 */
				$address = $wpdb->get_var(
					$wpdb->prepare(
						"
					SELECT meta_value FROM {$wpdb->usermeta}
					WHERE user_id=%s
					AND meta_key=%s
					",
						$donor['info'][0]['user_id'],
						'_walkthecounty_user_address'
					)
				);

				$donor = new WalkTheCounty_Donor( $donor_id );

				if ( ! empty( $address ) ) {
					$address = maybe_unserialize( $address );
					$donor->add_address( 'personal', $address );
					$donor->add_address( 'billing[]', $address );
				}

				$donor_name       = explode( ' ', $donor->name, 2 );
				$donor_first_name = WalkTheCounty()->donor_meta->get_meta( $donor->id, '_walkthecounty_donor_first_name' );
				$donor_last_name  = WalkTheCounty()->donor_meta->get_meta( $donor->id, '_walkthecounty_donor_last_name' );

				// If first name meta of donor is not created, then create it.
				if ( ! $donor_first_name && isset( $donor_name[0] ) ) {
					WalkTheCounty()->donor_meta->add_meta( $donor->id, '_walkthecounty_donor_first_name', $donor_name[0] );
				}

				// If last name meta of donor is not created, then create it.
				if ( ! $donor_last_name && isset( $donor_name[1] ) ) {
					WalkTheCounty()->donor_meta->add_meta( $donor->id, '_walkthecounty_donor_last_name', $donor_name[1] );
				}

				// If Donor is connected with WP User then update user meta.
				if ( $donor->user_id ) {
					if ( isset( $donor_name[0] ) ) {
						update_user_meta( $donor->user_id, 'first_name', $donor_name[0] );
					}
					if ( isset( $donor_name[1] ) ) {
						update_user_meta( $donor->user_id, 'last_name', $donor_name[1] );
					}
				}
			}

			WalkTheCounty()->donors->table_name     = $donor_table_name;
			WalkTheCounty()->donor_meta->table_name = $donor_meta_table_name;
		}
	} else {
		walkthecounty_set_upgrade_complete( 'v201_add_missing_donors' );
	}
}


/**
 * Version 2.0.3 automatic updates
 *
 * @since 2.0.3
 */
function walkthecounty_v203_upgrades() {
	global $wpdb;

	// Do not auto load option.
	$wpdb->update( $wpdb->options, array( 'autoload' => 'no' ), array( 'option_name' => 'walkthecounty_completed_upgrades' ) );

	// Remove from cache.
	$all_options = wp_load_alloptions();

	if ( isset( $all_options['walkthecounty_completed_upgrades'] ) ) {
		unset( $all_options['walkthecounty_completed_upgrades'] );
		wp_cache_set( 'alloptions', $all_options, 'options' );
	}

}


/**
 * Version 2.2.0 automatic updates
 *
 * @since 2.2.0
 */
function walkthecounty_v220_upgrades() {
	global $wpdb;

	/**
	 * Update 1
	 *
	 * Delete wp session data
	 */
	walkthecounty_v220_delete_wp_session_data();

	/**
	 * Update 2
	 *
	 * Rename payment table
	 */
	walkthecounty_v220_rename_donation_meta_type_callback();

	/**
	 * Update 2
	 *
	 * Set autoload to no to reduce result weight from WordPress query
	 */

	$options = array(
		'walkthecounty_settings',
		'walkthecounty_version',
		'walkthecounty_version_upgraded_from',
		'walkthecounty_default_api_version',
		'walkthecounty_site_address_before_migrate',
		'_walkthecounty_table_check',
		'walkthecounty_recently_activated_addons',
		'walkthecounty_is_addon_activated',
		'walkthecounty_last_paypal_ipn_received',
		'walkthecounty_use_php_sessions',
		'walkthecounty_subscriptions',
		'_walkthecounty_subscriptions_edit_last',
	);

	// Add all table version option name
	// Add banner option *_active_by_user
	$option_like = $wpdb->get_col(
		"
		SELECT option_name
		FROM $wpdb->options
		WHERE option_name like '%walkthecounty%'
		AND (
			option_name like '%_db_version%'
			OR option_name like '%_active_by_user%'
			OR option_name like '%_license_active%'
		)
		"
	);

	if ( ! empty( $option_like ) ) {
		$options = array_merge( $options, $option_like );
	}

	$options_str = '\'' . implode( "','", $options ) . '\'';

	$wpdb->query(
		"
		UPDATE $wpdb->options
		SET autoload = 'no'
		WHERE option_name IN ( {$options_str} )
		"
	);
}

/**
 * Version 2.2.1 automatic updates
 *
 * @since 2.2.1
 */
function walkthecounty_v221_upgrades() {
	global $wpdb;

	/**
	 * Update  1
	 *
	 * Change column length
	 */
	$wpdb->query( "ALTER TABLE $wpdb->donors MODIFY email varchar(255) NOT NULL" );
}

/**
 * Version 2.3.0 automatic updates
 *
 * @since 2.3.0
 */
function walkthecounty_v230_upgrades() {

	$options_key = array(
		'walkthecounty_temp_delete_form_ids', // delete import donor
		'walkthecounty_temp_delete_donation_ids', // delete import donor
		'walkthecounty_temp_delete_step', // delete import donor
		'walkthecounty_temp_delete_donor_ids', // delete import donor
		'walkthecounty_temp_delete_step_on', // delete import donor
		'walkthecounty_temp_delete_donation_ids', // delete test donor
		'walkthecounty_temp_delete_donor_ids', // delete test donor
		'walkthecounty_temp_delete_step', // delete test donor
		'walkthecounty_temp_delete_step_on', // delete test donor
		'walkthecounty_temp_delete_test_ids', // delete test donations
		'walkthecounty_temp_all_payments_data', // delete all stats
		'walkthecounty_recount_all_total', // delete all stats
		'walkthecounty_temp_recount_all_stats', // delete all stats
		'walkthecounty_temp_payment_items', // delete all stats
		'walkthecounty_temp_form_ids', // delete all stats
		'walkthecounty_temp_processed_payments', // delete all stats
		'walkthecounty_temp_recount_form_stats', // delete form stats
		'walkthecounty_temp_recount_earnings', // recount income
		'walkthecounty_recount_earnings_total', // recount income
		'walkthecounty_temp_reset_ids', // reset stats
	);

	$options_key = '\'' . implode( "','", $options_key ) . '\'';

	global $wpdb;

	/**
	 * Update  1
	 *
	 * delete unwanted key from option table
	 */
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name IN ( {$options_key} )" );
}

/**
 * Upgrade routine for 2.1 to set form closed status for all the donation forms.
 *
 * @since 2.1
 */
function walkthecounty_v210_verify_form_status_upgrades_callback() {

	$walkthecounty_updates = WalkTheCounty_Updates::get_instance();

	// form query.
	$donation_forms = new WP_Query(
		array(
			'paged'          => $walkthecounty_updates->step,
			'status'         => 'any',
			'order'          => 'ASC',
			'post_type'      => 'walkthecounty_forms',
			'posts_per_page' => 20,
		)
	);

	if ( $donation_forms->have_posts() ) {
		$walkthecounty_updates->set_percentage( $donation_forms->found_posts, ( $walkthecounty_updates->step * 20 ) );

		while ( $donation_forms->have_posts() ) {
			$donation_forms->the_post();
			$form_id = get_the_ID();

			$form_closed_status = walkthecounty_get_meta( $form_id, '_walkthecounty_form_status', true );
			if ( empty( $form_closed_status ) ) {
				walkthecounty_set_form_closed_status( $form_id );
			}
		}

		/* Restore original Post Data */
		wp_reset_postdata();

	} else {

		// The Update Ran.
		walkthecounty_set_upgrade_complete( 'v210_verify_form_status_upgrades' );
	}
}

/**
 * Upgrade routine for 2.1.3 to delete meta which is not attach to any donation.
 *
 * @since 2.1
 */
function walkthecounty_v213_delete_donation_meta_callback() {
	global $wpdb;
	$walkthecounty_updates        = WalkTheCounty_Updates::get_instance();
	$donation_meta_table = WalkTheCounty()->payment_meta->table_name;

	$donations = $wpdb->get_col(
		"
		SELECT DISTINCT payment_id
		FROM {$donation_meta_table}
		LIMIT 20
		OFFSET {$walkthecounty_updates->get_offset( 20 )}
		"
	);

	if ( ! empty( $donations ) ) {
		foreach ( $donations as $donation ) {
			$donation_obj = get_post( $donation );

			if ( ! $donation_obj instanceof WP_Post ) {
				WalkTheCounty()->payment_meta->delete_all_meta( $donation );
			}
		}
	} else {

		// The Update Ran.
		walkthecounty_set_upgrade_complete( 'v213_delete_donation_meta' );
	}
}

/**
 * Rename donation meta type
 *
 * @see   https://github.com/restrictcontentpro/restrict-content-pro/issues/1656
 *
 * @since 2.2.0
 */
function walkthecounty_v220_rename_donation_meta_type_callback() {
	global $wpdb;

	// Check upgrade before running.
	if (
		walkthecounty_has_upgrade_completed( 'v220_rename_donation_meta_type' )
		|| ! $wpdb->query( $wpdb->prepare( 'SHOW TABLES LIKE %s', "{$wpdb->prefix}walkthecounty_paymentmeta" ) )
	) {
		// Complete update if skip somehow
		walkthecounty_set_upgrade_complete( 'v220_rename_donation_meta_type' );

		return;
	}

	$wpdb->query( "ALTER TABLE {$wpdb->prefix}walkthecounty_paymentmeta CHANGE COLUMN payment_id donation_id bigint(20)" );
	$wpdb->query( "ALTER TABLE {$wpdb->prefix}walkthecounty_paymentmeta RENAME TO {$wpdb->prefix}walkthecounty_donationmeta" );

	walkthecounty_set_upgrade_complete( 'v220_rename_donation_meta_type' );
}

/**
 * Adds 'view_walkthecounty_payments' capability to 'walkthecounty_manager' user role.
 *
 * @since 2.1.5
 */
function walkthecounty_v215_update_donor_user_roles_callback() {

	$role = get_role( 'walkthecounty_manager' );
	$role->add_cap( 'view_walkthecounty_payments' );

	walkthecounty_set_upgrade_complete( 'v215_update_donor_user_roles' );
}


/**
 * Remove all wp session data from the options table, regardless of expiration.
 *
 * @since 2.2.0
 *
 * @global wpdb $wpdb
 */
function walkthecounty_v220_delete_wp_session_data() {
	global $wpdb;

	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_wp_session_%'" );
}


/**
 * Update donor meta
 * Set "_walkthecounty_anonymous_donor" meta key to "0" if not exist
 *
 * @since 2.2.4
 */
function walkthecounty_v224_update_donor_meta_callback() {
	/* @var WalkTheCounty_Updates $walkthecounty_updates */
	$walkthecounty_updates = WalkTheCounty_Updates::get_instance();

	$donor_count = WalkTheCounty()->donors->count(
		array(
			'number' => - 1,
		)
	);

	$donors = WalkTheCounty()->donors->get_donors(
		array(
			'paged'  => $walkthecounty_updates->step,
			'number' => 100,
		)
	);

	if ( $donors ) {
		$walkthecounty_updates->set_percentage( $donor_count, $walkthecounty_updates->step * 100 );
		// Loop through Donors
		foreach ( $donors as $donor ) {
			$anonymous_metadata = WalkTheCounty()->donor_meta->get_meta( $donor->id, '_walkthecounty_anonymous_donor', true );

			// If first name meta of donor is not created, then create it.
			if ( ! in_array( $anonymous_metadata, array( '0', '1' ) ) ) {
				WalkTheCounty()->donor_meta->add_meta( $donor->id, '_walkthecounty_anonymous_donor', '0' );
			}
		}
	} else {
		// The Update Ran.
		walkthecounty_set_upgrade_complete( 'v224_update_donor_meta' );
	}
}

/** Update donor meta
 * Set "_walkthecounty_anonymous_donor_forms" meta key if not exist
 *
 *
 * @since 2.2.4
 */
function walkthecounty_v224_update_donor_meta_forms_id_callback() {
	$walkthecounty_updates = WalkTheCounty_Updates::get_instance();

	$donations = new WP_Query( array(
			'paged'          => $walkthecounty_updates->step,
			'status'         => 'any',
			'order'          => 'ASC',
			'post_type'      => array( 'walkthecounty_payment' ),
			'posts_per_page' => 20,
		)
	);

	if ( $donations->have_posts() ) {
		$walkthecounty_updates->set_percentage( $donations->found_posts, $walkthecounty_updates->step * 20 );

		while ( $donations->have_posts() ) {
			$donations->the_post();

			$donation_id = get_the_ID();

			$form_id                 = walkthecounty_get_payment_form_id( $donation_id );
			$donor_id                = walkthecounty_get_payment_donor_id( $donation_id );
			$is_donated_as_anonymous = walkthecounty_is_anonymous_donation( $donation_id );

			$is_anonymous_donor = WalkTheCounty()->donor_meta->get_meta( $donor_id, "_walkthecounty_anonymous_donor_form_{$form_id}", true );
			$is_edit_donor_meta = ! in_array( $is_anonymous_donor, array( '0', '1' ) )
				? true
				: ( 0 !== absint( $is_anonymous_donor ) );

			if ( $is_edit_donor_meta ) {
				WalkTheCounty()->donor_meta->update_meta( $donor_id, "_walkthecounty_anonymous_donor_form_{$form_id}", absint( $is_donated_as_anonymous ) );
			}
		}

		wp_reset_postdata();
	} else {
		walkthecounty_set_upgrade_complete( 'v224_update_donor_meta_forms_id' );
	}
}

/**
 * Add custom comment table
 *
 * @since 2.4.0
 */
function  walkthecounty_v230_add_missing_comment_tables(){
	$custom_tables = array(
		WalkTheCounty()->comment->db,
		WalkTheCounty()->comment->db_meta,
	);

	/* @var WalkTheCounty_DB $table */
	foreach ( $custom_tables as $table ) {
		if ( ! $table->installed() ) {
			$table->register_table();
		}
	}
}


/**
 * Move donor notes to comment table
 *
 * @since 2.3.0
 */
function walkthecounty_v230_move_donor_note_callback() {
	// Add comment table if missing.
	walkthecounty_v230_add_missing_comment_tables();

	/* @var WalkTheCounty_Updates $walkthecounty_updates */
	$walkthecounty_updates = WalkTheCounty_Updates::get_instance();

	$donor_count = WalkTheCounty()->donors->count( array(
		'number' => - 1,
	) );

	$donors = WalkTheCounty()->donors->get_donors( array(
		'paged'  => $walkthecounty_updates->step,
		'number' => 100,
	) );

	if ( $donors ) {
		$walkthecounty_updates->set_percentage( $donor_count, $walkthecounty_updates->step * 100 );
		// Loop through Donors
		foreach ( $donors as $donor ) {
			$notes = trim( WalkTheCounty()->donors->get_column( 'notes', $donor->id ) );

			// If first name meta of donor is not created, then create it.
			if ( ! empty( $notes ) ) {
				$notes = array_values( array_filter( array_map( 'trim', explode( "\n", $notes ) ), 'strlen' ) );

				foreach ( $notes as $note ) {
					$note      = array_map( 'trim', explode( '-', $note ) );
					$timestamp = strtotime( $note[0] );

					WalkTheCounty()->comment->db->add(
						array(
							'comment_content'  => $note[1],
							'user_id'          => absint( WalkTheCounty()->donors->get_column_by( 'user_id', 'id', $donor->id ) ),
							'comment_date'     => date( 'Y-m-d H:i:s', $timestamp ),
							'comment_date_gmt' => get_gmt_from_date( date( 'Y-m-d H:i:s', $timestamp ) ),
							'comment_parent'   => $donor->id,
							'comment_type'     => 'donor',
						)
					);
				}
			}
		}

	} else {
		// The Update Ran.
		walkthecounty_set_upgrade_complete( 'v230_move_donor_note' );
	}
}

/**
 * Move donation notes to comment table
 *
 * @since 2.3.0
 */
function walkthecounty_v230_move_donation_note_callback() {
	global $wpdb;

	// Add comment table if missing.
	walkthecounty_v230_add_missing_Comment_tables();

	/* @var WalkTheCounty_Updates $walkthecounty_updates */
	$walkthecounty_updates = WalkTheCounty_Updates::get_instance();

	$donation_note_count = $wpdb->get_var(
		$wpdb->prepare(
			"
			SELECT count(*)
			FROM {$wpdb->comments}
			WHERE comment_type=%s
			",
			'walkthecounty_payment_note'
		)
	);

	$query = $wpdb->prepare(
		"
			SELECT *
			FROM {$wpdb->comments}
			WHERE comment_type=%s
			ORDER BY comment_ID ASC
			LIMIT 100
			OFFSET %d
			",
		'walkthecounty_payment_note',
		$walkthecounty_updates->get_offset( 100 )
	);

	$comments = $wpdb->get_results( $query );

	if ( $comments ) {
		$walkthecounty_updates->set_percentage( $donation_note_count, $walkthecounty_updates->step * 100 );

		// Loop through Donors
		foreach ( $comments as $comment ) {
			$donation_id = $comment->comment_post_ID;
			$form_id     = walkthecounty_get_payment_form_id( $donation_id );

			$comment_id = WalkTheCounty()->comment->db->add(
				array(
					'comment_content'  => $comment->comment_content,
					'user_id'          => $comment->user_id,
					'comment_date'     => date( 'Y-m-d H:i:s', strtotime( $comment->comment_date ) ),
					'comment_date_gmt' => get_gmt_from_date( date( 'Y-m-d H:i:s', strtotime( $comment->comment_date_gmt ) ) ),
					'comment_parent'   => $comment->comment_post_ID,
					'comment_type'     => is_numeric( get_comment_meta( $comment->comment_ID, '_walkthecounty_donor_id', true ) )
						? 'donor_donation'
						: 'donation',
				)
			);

			if( ! $comment_id ) {
				continue;
			}

			// @see https://github.com/impress-org/walkthecounty/issues/3737#issuecomment-428460802
			$restricted_meta_keys = array(
				'akismet_result',
				'akismet_as_submitted',
				'akismet_history'
			);

			if ( $comment_meta = get_comment_meta( $comment->comment_ID ) ) {
				foreach ( $comment_meta as $meta_key => $meta_value ) {
					// Skip few comment meta keys.
					if( in_array( $meta_key, $restricted_meta_keys) ) {
						continue;
					}

					$meta_value = maybe_unserialize( $meta_value );
					$meta_value = is_array( $meta_value ) ? current( $meta_value ) : $meta_value;

					WalkTheCounty()->comment->db_meta->update_meta( $comment_id, $meta_key, $meta_value );
				}
			}

			WalkTheCounty()->comment->db_meta->update_meta( $comment_id, '_walkthecounty_form_id', $form_id );

			// Delete comment.
			update_comment_meta( $comment->comment_ID, '_walkthecounty_comment_moved', 1 );
		}

	} else {
		$comment_ids = $wpdb->get_col(
			$wpdb->prepare(
					"
				SELECT DISTINCT comment_id
				FROM {$wpdb->commentmeta}
				WHERE meta_key=%s
				AND meta_value=%d
				",
				'_walkthecounty_comment_moved',
				1
			)
		);

		if( ! empty( $comment_ids ) ) {
			$comment_ids = "'" . implode( "','", $comment_ids ). "'";

			$wpdb->query( "DELETE FROM {$wpdb->comments} WHERE comment_ID IN ({$comment_ids})" );
			$wpdb->query( "DELETE FROM {$wpdb->commentmeta} WHERE comment_id IN ({$comment_ids})" );
		}

		// The Update Ran.
		walkthecounty_set_upgrade_complete( 'v230_move_donation_note' );
	}
}

/**
 * Delete donor wall related donor meta data
 *
 * @since 2.3.0
 *
 */
function walkthecounty_v230_delete_dw_related_donor_data_callback(){
	global $wpdb;

	$walkthecounty_updates = WalkTheCounty_Updates::get_instance();

	$wpdb->query( "DELETE FROM {$wpdb->donormeta} WHERE meta_key LIKE '%_walkthecounty_anonymous_donor%' OR meta_key='_walkthecounty_has_comment';" );

	$walkthecounty_updates->percentage = 100;

	// The Update Ran.
	walkthecounty_set_upgrade_complete( 'v230_delete_donor_wall_related_donor_data' );
}

/**
 * Delete donor wall related comment meta data
 *
 * @since 2.3.0
 *
 */
function walkthecounty_v230_delete_dw_related_comment_data_callback(){
	global $wpdb;

	$walkthecounty_updates = WalkTheCounty_Updates::get_instance();

	$wpdb->query( "DELETE FROM {$wpdb->walkthecounty_commentmeta} WHERE meta_key='_walkthecounty_anonymous_donation';" );

	$walkthecounty_updates->percentage = 100;

	// The Update Ran.
	walkthecounty_set_upgrade_complete( 'v230_delete_donor_wall_related_comment_data' );
}

/**
 * Update donation form goal progress data.
 *
 * @since 2.4.0
 *
 */
function walkthecounty_v240_update_form_goal_progress_callback() {

	/* @var WalkTheCounty_Updates $walkthecounty_updates */
	$walkthecounty_updates = WalkTheCounty_Updates::get_instance();

	// form query
	$forms = new WP_Query(
		array(
			'paged'          => $walkthecounty_updates->step,
			'status'         => 'any',
			'order'          => 'ASC',
			'post_type'      => 'walkthecounty_forms',
			'posts_per_page' => 20,
		)
	);

	if ( $forms->have_posts() ) {
		while ( $forms->have_posts() ) {
			$forms->the_post();

			// Update the goal progress for donation form.
			walkthecounty_update_goal_progress( get_the_ID() );

		}// End while().

		wp_reset_postdata();

	} else {

		// No more forms found, finish up.
		walkthecounty_set_upgrade_complete( 'v240_update_form_goal_progress' );

	}
}


/**
 * Manual update handler for v241_remove_sale_logs
 *
 * @since 2.4.1
 */
function walkthecounty_v241_remove_sale_logs_callback() {
	global $wpdb;

	$log_table      = WalkTheCounty()->logs->log_db->table_name;
	$log_meta_table = WalkTheCounty()->logs->logmeta_db->table_name;

	$sql = "DELETE {$log_table}, {$log_meta_table}
		FROM {$log_table}
		INNER JOIN  {$log_meta_table} ON {$log_meta_table}.log_id={$log_table}.ID
		WHERE log_type='sale'
		";

	// Remove donation logs.
	$wpdb->query( $sql );

	walkthecounty_set_upgrade_complete( 'v241_remove_sale_logs' );
}


/**
 * DB upgrades for WalkTheCounty 2.5.0
 *
 * @since 2.5.0
 */
function walkthecounty_v250_upgrades() {
	global $wpdb;

	$old_license   = array();
	$new_license   = array();
	$walkthecounty_licenses = get_option( 'walkthecounty_licenses', array() );
	$walkthecounty_options  = walkthecounty_get_settings();

	// Get add-ons license key.
	$addons = array();
	foreach ( $walkthecounty_options as $key => $value ) {
		if ( false !== strpos( $key, '_license_key' ) ) {
			$addons[ $key ] = $value;
		}
	}

	// Bailout: We do not have any add-on license data to upgrade.
	if ( empty( $addons ) ) {
		return false;
	}

	foreach ( $addons as $key => $license_key ) {

		// Get addon shortname.
		$addon_shortname = str_replace( '_license_key', '', $key );

		// Addon license option name.
		$addon_shortname    = "{$addon_shortname}_license_active";
		$addon_license_data = get_option( "{$addon_shortname}_license_active", array() );

		if (
			! $license_key
			|| array_key_exists( $license_key, $walkthecounty_licenses )
		) {
			continue;
		}

		$old_license[ $license_key ] = $addon_license_data;
	}

	// Bailout.
	if ( empty( $old_license ) ) {
		return false;
	}

	/* @var stdClass $data */
	foreach ( $old_license as $key => $data ) {
		$tmp = WalkTheCounty_License::request_license_api( array(
			'edd_action' => 'check_license',
			'license'    => $key,
		), true );

		if ( is_wp_error( $tmp ) || ! $tmp['success'] ) {
			continue;
		}

		$new_license[ $key ] = $tmp;
	}

	// Bailout.
	if ( empty( $new_license ) ) {
		return false;
	}

	$walkthecounty_licenses = array_merge( $walkthecounty_licenses, $new_license );

	update_option( 'walkthecounty_licenses', $walkthecounty_licenses );

	/**
	 * Delete data.
	 */

	// 1. license keys
	foreach ( get_option( 'walkthecounty_settings' ) as $index => $setting ) {
		if ( false !== strpos( $index, '_license_key' ) ) {
			walkthecounty_delete_option( $index );
		}
	}

	// 2. license api data
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name like '%_license_active%' AND option_name like 'walkthecounty_%'" );

	// 3. subscriptions data
	delete_option( '_walkthecounty_subscriptions_edit_last' );
	delete_option( 'walkthecounty_subscriptions' );

	// 4. misc
	delete_option( 'walkthecounty_is_addon_activated' );

	walkthecounty_refresh_licenses();
}

/**
 * DB upgrades for WalkTheCounty 2.5.8
 *
 * @since 2.5.8
 */
function walkthecounty_v258_upgrades() {

	$is_checkout_enabled = walkthecounty_is_setting_enabled( walkthecounty_get_option( 'stripe_checkout_enabled', 'disabled' ) );

	// Bailout, if stripe checkout is not active as a gateway.
	if ( ! $is_checkout_enabled  ) {
		return;
	}

	$enabled_gateways = walkthecounty_get_option( 'gateways', array() );

	// Bailout, if Stripe Checkout is already enabled.
	if ( ! empty( $enabled_gateways['stripe_checkout'] ) ) {
		return;
	}

	$gateways_label  = walkthecounty_get_option( 'gateways_label', array() );
	$default_gateway = walkthecounty_get_option( 'default_gateway' );

	// Set Stripe Checkout as active gateway.
	$enabled_gateways['stripe_checkout']  = 1;

	// Unset Stripe - Credit Card as an active gateway.
	unset( $enabled_gateways['stripe'] );

	// Set Stripe Checkout same as Stripe as they have enabled Stripe Checkout under Stripe using same label.
	$gateways_label['stripe_checkout'] = $gateways_label['stripe'];
	walkthecounty_update_option( 'gateways_label', $gateways_label );

	// If default gateway selected is `stripe` then set `stripe checkout` as default.
	if ( 'stripe' === $default_gateway ) {
		walkthecounty_update_option( 'default_gateway', 'stripe_checkout' );
	}

	// Update the enabled gateways in database.
	walkthecounty_update_option( 'gateways', $enabled_gateways );

	// Delete the old legacy settings.
	walkthecounty_delete_option( 'stripe_checkout_enabled' );
}
