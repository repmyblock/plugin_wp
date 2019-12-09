<?php
/**
 * Front-end Actions
 *
 * @package     WalkTheCounty
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

WalkTheCounty_Cron::add_monthly_event( 'walkthecounty_refresh_licenses' );

/**
 * Hooks WalkTheCounty actions, when present in the $_GET superglobal. Every walkthecounty_action
 * present in $_GET is called using WordPress's do_action function. These
 * functions are called on init.
 *
 * @since  1.0
 *
 * @return void
 */
function walkthecounty_get_actions() {

	$get_data = walkthecounty_clean( $_GET ); // WPCS: input var ok, sanitization ok, CSRF ok.

	$_get_action = ! empty( $get_data['walkthecounty_action'] ) ? $get_data['walkthecounty_action'] : null;

	// Add backward compatibility to walkthecounty-action param ( $_GET ).
	if ( empty( $_get_action ) ) {
		$_get_action = ! empty( $get_data['walkthecounty-action'] ) ? $get_data['walkthecounty-action'] : null;
	}

	if ( isset( $_get_action ) ) {
		/**
		 * Fires in WordPress init or admin init, when walkthecounty_action is present in $_GET.
		 *
		 * @since 1.0
		 *
		 * @param array $_GET Array of HTTP GET variables.
		 */
		do_action( "walkthecounty_{$_get_action}", $get_data );
	}

}

add_action( 'init', 'walkthecounty_get_actions' );

/**
 * Hooks WalkTheCounty actions, when present in the $_POST super global. Every walkthecounty_action
 * present in $_POST is called using WordPress's do_action function. These
 * functions are called on init.
 *
 * @since  1.0
 *
 * @return void
 */
function walkthecounty_post_actions() {

	$post_data = walkthecounty_clean( $_POST ); // WPCS: input var ok, sanitization ok, CSRF ok.

	$_post_action = ! empty( $post_data['walkthecounty_action'] ) ? $post_data['walkthecounty_action'] : null;

	// Add backward compatibility to walkthecounty-action param ( $_POST ).
	if ( empty( $_post_action ) ) {
		$_post_action = ! empty( $post_data['walkthecounty-action'] ) ? $post_data['walkthecounty-action'] : null;
	}

	if ( isset( $_post_action ) ) {
		/**
		 * Fires in WordPress init or admin init, when walkthecounty_action is present in $_POST.
		 *
		 * @since 1.0
		 *
		 * @param array $_POST Array of HTTP POST variables.
		 */
		do_action( "walkthecounty_{$_post_action}", $post_data );
	}

}

add_action( 'init', 'walkthecounty_post_actions' );

/**
 * Connect WordPress user with Donor.
 *
 * @param  int   $user_id   User ID.
 * @param  array $user_data User Data.
 *
 * @since  1.7
 *
 * @return void
 */
function walkthecounty_connect_donor_to_wpuser( $user_id, $user_data ) {
	/* @var WalkTheCounty_Donor $donor */
	$donor = new WalkTheCounty_Donor( $user_data['user_email'] );

	// Validate donor id and check if do nor is already connect to wp user or not.
	if ( $donor->id && ! $donor->user_id ) {

		// Update donor user_id.
		if ( $donor->update( array( 'user_id' => $user_id ) ) ) {
			$donor_note = sprintf( esc_html__( 'WordPress user #%d is connected to #%d', 'walkthecounty' ), $user_id, $donor->id );
			$donor->add_note( $donor_note );

			// Update user_id meta in payments.
			// if( ! empty( $donor->payment_ids ) && ( $donations = explode( ',', $donor->payment_ids ) ) ) {
			// 	foreach ( $donations as $donation  ) {
			// 		walkthecounty_update_meta( $donation, '_walkthecounty_payment_user_id', $user_id );
			// 	}
			// }
			// Do not need to update user_id in payment because we will get user id from donor id now.
		}
	}
}

add_action( 'walkthecounty_insert_user', 'walkthecounty_connect_donor_to_wpuser', 10, 2 );


/**
 * Processing after donor batch export complete
 *
 * @since 1.8
 *
 * @param $data
 */
function walkthecounty_donor_batch_export_complete( $data ) {
	// Remove donor ids cache.
	if (
		isset( $data['class'] )
		&& 'WalkTheCounty_Batch_Donors_Export' === $data['class']
		&& ! empty( $data['forms'] )
		&& isset( $data['walkthecounty_export_option']['query_id'] )
	) {
		WalkTheCounty_Cache::delete( WalkTheCounty_Cache::get_key( $data['walkthecounty_export_option']['query_id'] ) );
	}
}

add_action( 'walkthecounty_file_export_complete', 'walkthecounty_donor_batch_export_complete' );


/**
 * Set Donation Amount for Multi Level Donation Forms
 *
 * @param int $form_id Donation Form ID.
 *
 * @since 1.8.9
 *
 * @return void
 */
function walkthecounty_set_donation_levels_max_min_amount( $form_id ) {
	if (
		( 'set' === $_POST['_walkthecounty_price_option'] ) ||
		( in_array( '_walkthecounty_donation_levels', $_POST ) && count( $_POST['_walkthecounty_donation_levels'] ) <= 0 ) ||
		! ( $donation_levels_amounts = wp_list_pluck( $_POST['_walkthecounty_donation_levels'], '_walkthecounty_amount' ) )
	) {
		// Delete old meta.
		walkthecounty_delete_meta( $form_id, '_walkthecounty_levels_minimum_amount' );
		walkthecounty_delete_meta( $form_id, '_walkthecounty_levels_maximum_amount' );

		return;
	}

	// Sanitize donation level amounts.
	$donation_levels_amounts = array_map( 'walkthecounty_maybe_sanitize_amount', $donation_levels_amounts );

	$min_amount = min( $donation_levels_amounts );
	$max_amount = max( $donation_levels_amounts );

	// Set Minimum and Maximum amount for Multi Level Donation Forms.
	walkthecounty_update_meta( $form_id, '_walkthecounty_levels_minimum_amount', $min_amount ? walkthecounty_sanitize_amount_for_db( $min_amount ) : 0 );
	walkthecounty_update_meta( $form_id, '_walkthecounty_levels_maximum_amount', $max_amount ? walkthecounty_sanitize_amount_for_db( $max_amount ) : 0 );
}

add_action( 'walkthecounty_pre_process_walkthecounty_forms_meta', 'walkthecounty_set_donation_levels_max_min_amount', 30 );


/**
 * Save donor address when donation complete
 *
 * @since 2.0
 *
 * @param int $payment_id
 */
function _walkthecounty_save_donor_billing_address( $payment_id ) {
	$donor_id  = absint( walkthecounty_get_payment_donor_id( $payment_id ));

	// Bailout
	if ( ! $donor_id ) {
		return;
	}


	/* @var WalkTheCounty_Donor $donor */
	$donor = new WalkTheCounty_Donor( $donor_id );

	// Save address.
	$donor->add_address( 'billing[]', walkthecounty_get_donation_address( $payment_id ) );
}

add_action( 'walkthecounty_complete_donation', '_walkthecounty_save_donor_billing_address', 9999 );


/**
 * Update form id in payment logs
 *
 * @since 2.0
 *
 * @param array $args
 */
function walkthecounty_update_log_form_id( $args ) {
	$new_form_id = absint( $args[0] );
	$payment_id  = absint( $args[1] );
	$logs        = WalkTheCounty()->logs->get_logs( $payment_id );

	// Bailout.
	if ( empty( $logs ) ) {
		return;
	}

	/* @var object $log */
	foreach ( $logs as $log ) {
		WalkTheCounty()->logs->logmeta_db->update_meta( $log->ID, '_walkthecounty_log_form_id', $new_form_id );
	}

	// Delete cache.
	WalkTheCounty()->logs->delete_cache();
}

add_action( 'walkthecounty_update_log_form_id', 'walkthecounty_update_log_form_id' );

/**
 * Verify addon dependency before addon update
 *
 * @since 2.1.4
 *
 * @param $error
 * @param $hook_extra
 *
 * @return WP_Error
 */
function __walkthecounty_verify_addon_dependency_before_update( $error, $hook_extra ) {
	// Bailout.
	if (
		is_wp_error( $error )
		|| ! array_key_exists( 'plugin', $hook_extra )
	) {
		return $error;
	}

	$plugin_base    = strtolower( $hook_extra['plugin'] );
	$licensed_addon = array_map( 'strtolower', WalkTheCounty_License::get_licensed_addons() );

	// Skip if not a WalkTheCounty addon.
	if ( ! in_array( $plugin_base, $licensed_addon ) ) {
		return $error;
	}

	// Load file.
	if( ! class_exists( 'WalkTheCounty_Readme_Parser' ) ) {
		require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/class-walkthecounty-readme-parser.php';
	}

	$plugin_base = strtolower( $plugin_base );
	$plugin_slug = str_replace( '.php', '', basename( $plugin_base ) );

	$url = walkthecounty_get_addon_readme_url( $plugin_slug );

	$parser           = new WalkTheCounty_Readme_Parser( $url );
	$walkthecounty_min_version = $parser->requires_at_least();


	if ( version_compare( WALKTHECOUNTY_VERSION, $walkthecounty_min_version, '<' ) ) {
		return new WP_Error(
			'WalkTheCounty_Addon_Update_Error',
			sprintf(
				__( 'WalkTheCountyWP version %s is required to update this add-on.', 'walkthecounty' ),
				$walkthecounty_min_version
			)
		);
	}

	return $error;
}

add_filter( 'upgrader_pre_install', '__walkthecounty_verify_addon_dependency_before_update', 10, 2 );

/**
 * Function to add suppress_filters param if WPML add-on is activated.
 *
 * @since 2.1.4
 *
 * @param array WP query argument for Total Goal.
 *
 * @return array WP query argument for Total Goal.
 */
function __walkthecounty_wpml_total_goal_shortcode_agrs( $args ) {
	$args['suppress_filters'] = true;

	return $args;
}

/**
 * Function to remove WPML post where filter in goal total amount shortcode.
 *
 * @since 2.1.4
 * @global SitePress $sitepress
 */
function __walkthecounty_remove_wpml_parse_query_filter() {
	global $sitepress;
	remove_action('parse_query', array($sitepress, 'parse_query'));
}


/**
 * Function to add WPML post where filter in goal total amount shortcode.
 *
 * @since 2.1.4
 * @global SitePress $sitepress
 */
function __walkthecounty_add_wpml_parse_query_filter() {
	global $sitepress;
	add_action('parse_query', array($sitepress, 'parse_query'));
}

/**
 * Action all the hook that add support for WPML.
 *
 * @since 2.1.4
 */
function walkthecounty_add_support_for_wpml() {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}


	if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {

		add_filter( 'walkthecounty_totals_goal_shortcode_query_args', '__walkthecounty_wpml_total_goal_shortcode_agrs' );

		// @see https://wpml.org/forums/topic/problem-with-query-filter-in-get_posts-function/#post-271309
		add_action( 'walkthecounty_totals_goal_shortcode_before_render', '__walkthecounty_remove_wpml_parse_query_filter', 99 );
		add_action( 'walkthecounty_totals_goal_shortcode_after_render', '__walkthecounty_add_wpml_parse_query_filter', 99 );
	}
}

add_action( 'walkthecounty_init', 'walkthecounty_add_support_for_wpml', 1000 );

/**
 * Backward compatibility for email_access property
 * Note: only for internal purpose
 *
 * @todo: Need to decide when to remove this backward compatibility.
 *        We decided to load WalkTheCounty()->email_access on for frontend but some of email tags is still using this. Since we have option to resend email in admin then
 *        this cause of fatal error because that property does not load in backend. This is a temporary solution to prevent fatal error when resend receipt.
 *        ref: https://github.com/impress-org/walkthecounty/issues/4068
 *
 * @since 2.4.5
 */
function walkthecounty_set_email_access_property(){
	if( ! ( WalkTheCounty()->email_access instanceof WalkTheCounty_Email_Access )  ){
		require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/class-walkthecounty-email-access.php';
		WalkTheCounty()->email_access =  new WalkTheCounty_Email_Access();
	}
}
add_action( 'walkthecounty_email_links', 'walkthecounty_set_email_access_property', -1 );
add_action( 'walkthecounty_donation-receipt_email_notification', 'walkthecounty_set_email_access_property', -1 );
