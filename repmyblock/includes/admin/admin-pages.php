<?php
/**
 * Admin Pages
 *
 * @package     WalkTheCounty
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates the admin submenu pages under the WalkTheCounty menu and assigns their
 * links to global variables
 *
 * @since 1.0
 *
 * @global $walkthecounty_settings_page
 * @global $walkthecounty_payments_page
 * @global $walkthecounty_reports_page
 * @global $walkthecounty_donors_page
 *
 * @return void
 */
function walkthecounty_add_options_links() {
	global $walkthecounty_settings_page, $walkthecounty_payments_page, $walkthecounty_reports_page, $walkthecounty_donors_page, $walkthecounty_tools_page;

	//Payments
	/* @var WP_Post_Type $walkthecounty_payment */
	$walkthecounty_payment       = get_post_type_object( 'walkthecounty_payment' );
	$walkthecounty_payments_page = add_submenu_page(
		'edit.php?post_type=walkthecounty_forms',
		$walkthecounty_payment->labels->name,
		$walkthecounty_payment->labels->menu_name,
		'edit_walkthecounty_payments',
		'walkthecounty-payment-history',
		'walkthecounty_payment_history_page'
	);

	//Donors
	$walkthecounty_donors_page = add_submenu_page(
		'edit.php?post_type=walkthecounty_forms',
		esc_html__( 'Donors', 'walkthecounty' ),
		esc_html__( 'Donors', 'walkthecounty' ),
		'view_walkthecounty_reports',
		'walkthecounty-donors',
		'walkthecounty_donors_page'
	);

	//Reports
	$walkthecounty_reports_page = add_submenu_page(
		'edit.php?post_type=walkthecounty_forms',
		esc_html__( 'Donation Reports', 'walkthecounty' ),
		esc_html__( 'Reports', 'walkthecounty' ),
		'view_walkthecounty_reports',
		'walkthecounty-reports',
		array(
			WalkTheCounty()->walkthecounty_settings,
			'output',
		)
	);

	//Settings
	$walkthecounty_settings_page = add_submenu_page(
		'edit.php?post_type=walkthecounty_forms',
		esc_html__( 'WalkTheCountyWP Settings', 'walkthecounty' ),
		esc_html__( 'Settings', 'walkthecounty' ),
		'manage_walkthecounty_settings',
		'walkthecounty-settings',
		array(
			WalkTheCounty()->walkthecounty_settings,
			'output',
		)
	);

	//Tools.
	$walkthecounty_tools_page = add_submenu_page(
		'edit.php?post_type=walkthecounty_forms',
		esc_html__( 'WalkTheCountyWP Tools', 'walkthecounty' ),
		esc_html__( 'Tools', 'walkthecounty' ),
		'manage_walkthecounty_settings',
		'walkthecounty-tools',
		array(
			WalkTheCounty()->walkthecounty_settings,
			'output',
		)
	);
}

add_action( 'admin_menu', 'walkthecounty_add_options_links', 10 );



/**
 * Creates the admin add-ons submenu page under the WalkTheCounty menu and assigns their
 * link to global variable
 *
 * @since 2.5.0
 *
 * @global $walkthecounty_add_ons_page
 *
 * @return void
 */
function walkthecounty_add_add_ons_option_link(){
	global $walkthecounty_add_ons_page;

	//Add-ons
	$walkthecounty_add_ons_page = add_submenu_page(
		'edit.php?post_type=walkthecounty_forms',
		esc_html__( 'WalkTheCountyWP Add-ons', 'walkthecounty' ),
		esc_html__( 'Add-ons', 'walkthecounty' ),
		'install_plugins',
		'walkthecounty-addons',
		'walkthecounty_add_ons_page'
	);

}
add_action( 'admin_menu', 'walkthecounty_add_add_ons_option_link', 999999 );

/**
 *  Determines whether the current admin page is a WalkTheCounty admin page.
 *
 *  Only works after the `wp_loaded` hook, & most effective
 *  starting on `admin_menu` hook.
 *
 * @since 1.0
 * @since 2.1 Simplified function.
 *
 * @param string $passed_page Optional. Main page's slug
 * @param string $passed_view Optional. Page view ( ex: `edit` or `delete` )
 *
 * @return bool True if WalkTheCounty admin page.
 */
function walkthecounty_is_admin_page( $passed_page = '', $passed_view = '' ) {
	global $pagenow, $typenow;

	$found          = true;
	$get_query_args = ! empty( $_GET ) ? @array_map( 'strtolower', $_GET ) : array();

	// Set default argument, if not passed.
	$query_args = wp_parse_args( $get_query_args, array_fill_keys( array( 'post_type', 'action', 'taxonomy', 'page', 'view', 'tab' ), false ) );

	switch ( $passed_page ) {
		case 'categories':
		case 'tags':
			$has_view = in_array( $passed_view, array( 'list-table', 'edit', 'new' ), true );

			if (
				! in_array( $query_args['taxonomy'], array( 'walkthecounty_forms_category', 'walkthecounty_forms_tag' ), true ) &&
				'edit-tags.php' !== $pagenow &&
				(
					$has_view ||
					(
						( in_array( $passed_view, array( 'list-table', 'new' ), true ) && 'edit' === $query_args['action'] ) ||
						( 'edit' !== $passed_view && 'edit' !== $query_args['action'] ) &&
						! $has_view
					)
				)
			) {
				$found = false;
			}
			break;
		// WalkTheCounty Donation form page.
		case 'walkthecounty_forms':
			$has_view = in_array( $passed_view, array( 'new', 'list-table', 'edit' ), true );

			if (
				'walkthecounty_forms' !== $typenow &&
				(
					( 'list-table' !== $passed_view && 'edit.php' !== $pagenow ) &&
					( 'edit' !== $passed_view && 'post.php' !== $pagenow ) &&
					( 'new' !== $passed_view && 'post-new.php' !== $pagenow )
				) ||
				(
					! $has_view &&
					( 'post-new.php' !== $pagenow && 'walkthecounty_forms' !== $query_args['post_type'] )
				)
			) {
				$found = false;
			}
			break;
		// WalkTheCounty Donors page.
		case 'donors':
			$has_view = array_intersect( array( $passed_view, $query_args['view'] ), array( 'list-table', 'overview', 'notes' ) );

			if (
				( 'walkthecounty-donors' !== $query_args['page'] || 'edit.php' !== $pagenow ) &&
				(
					( $passed_view !== $query_args['view'] || ! empty( $has_view ) ) ||
					( false !== $query_args['view'] && 'list-table' !== $passed_view )
				)
			) {
				$found = false;
			}
			break;
		// WalkTheCounty Donations page.
		case 'payments':
			if (
				( 'walkthecounty-payment-history' !== $query_args['page'] || 'edit.php' !== $pagenow ) &&
				(
					! in_array( $passed_view, array( 'list-table', 'edit' ), true ) ||
					(
						( 'list-table' !== $passed_view && false !== $query_args['view'] ) ||
						( 'edit' !== $passed_view && 'view-payment-details' !== $query_args['view'] )
					)
				)
			) {
				$found = false;
			}
			break;
		case 'reports':
		case 'settings':
		case 'addons':
			// Get current tab.
			$current_tab       = empty( $passed_view ) ? $query_args['tab'] : $passed_view;
			$walkthecounty_setting_page = in_array( $query_args['page'], array( 'walkthecounty-reports', 'walkthecounty-settings', 'walkthecounty-addons' ), true );

			// Check if it's WalkTheCounty Setting page or not.
			if (
				( 'edit.php' !== $pagenow || ! $walkthecounty_setting_page ) &&
				! WalkTheCounty_Admin_Settings::is_setting_page( $current_tab )
			) {
				$found = false;
			}
			break;
		default:
			global $walkthecounty_payments_page, $walkthecounty_settings_page, $walkthecounty_reports_page, $walkthecounty_system_info_page, $walkthecounty_add_ons_page, $walkthecounty_settings_export, $walkthecounty_donors_page, $walkthecounty_tools_page;
			$admin_pages = apply_filters( 'walkthecounty_admin_pages', array(
				$walkthecounty_payments_page,
				$walkthecounty_settings_page,
				$walkthecounty_reports_page,
				$walkthecounty_system_info_page,
				$walkthecounty_add_ons_page,
				$walkthecounty_settings_export,
				$walkthecounty_donors_page,
				$walkthecounty_tools_page,
				'widgets.php',
			) );

			$found = ( 'walkthecounty_forms' === $typenow || in_array( $pagenow, array_merge( $admin_pages, array( 'index.php', 'post-new.php', 'post.php' ) ), true ) ) ? true : false;
	}
	return (bool) apply_filters( 'walkthecounty_is_admin_page', $found, $query_args['page'], $query_args['view'], $passed_page, $passed_view );
}

/**
 * Add setting tab to walkthecounty-settings page
 *
 * @since  1.8
 * @param  array $settings
 * @return array
 */
function walkthecounty_settings_page_pages( $settings ) {
	include( 'abstract-admin-settings-page.php' );

	$settings = array(
		// General settings.
		include( WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/settings/class-settings-general.php' ),

		// Payment Gateways Settings.
		include( WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/settings/class-settings-gateways.php' ),

		// Display settings.
		include( WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/settings/class-settings-display.php' ),

		// Emails settings.
		include( WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/settings/class-settings-email.php' ),

		// Addons settings.
		include( WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/settings/class-settings-addon.php' ),

		// License settings.
		include( WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/settings/class-settings-license.php' ),

		// Advanced settings.
		include( WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/settings/class-settings-advanced.php' ),
	);

	// Output.
	return $settings;
}
add_filter( 'walkthecounty-settings_get_settings_pages', 'walkthecounty_settings_page_pages', 0, 1 );


/**
 * Add setting tab to walkthecounty-settings page
 *
 * @since  1.8
 * @param  array $settings
 * @return array
 */
function walkthecounty_reports_page_pages( $settings ) {
	include( 'abstract-admin-settings-page.php' );

	$settings = array(
		// Earnings.
		include( 'reports/class-earnings-report.php' ),

		// Forms.
		include( 'reports/class-forms-report.php' ),

		// Gateways.
		include( 'reports/class-gateways-report.php' ),

	);

	// Output.
	return $settings;
}
add_filter( 'walkthecounty-reports_get_settings_pages', 'walkthecounty_reports_page_pages', 0, 1 );

/**
 * Add setting tab to walkthecounty-settings page
 *
 * @since  1.8
 * @param  array $settings
 * @return array
 */
function walkthecounty_tools_page_pages( $settings ) {
	include( 'abstract-admin-settings-page.php' );

	$settings = array(

		// Export.
		include( WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/tools/class-settings-export.php' ),

		// Import
		include_once( WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/tools/class-settings-import.php' ),

		// Logs.
		include( WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/tools/class-settings-logs.php' ),

		// API.
		include( WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/tools/class-settings-api.php' ),

		// Data.
		include( WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/tools/class-settings-data.php' ),

		// System Info.
		include( WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/tools/class-settings-system-info.php' ),
	);

	// Output.
	return $settings;
}
add_filter( 'walkthecounty-tools_get_settings_pages', 'walkthecounty_tools_page_pages', 0, 1 );

/**
 * Set default tools page tab.
 *
 * @since  1.8
 * @param  string $default_tab Default tab name.
 * @return string
 */
function walkthecounty_set_default_tab_form_tools_page( $default_tab ) {
	return 'export';
}
add_filter( 'walkthecounty_default_setting_tab_walkthecounty-tools', 'walkthecounty_set_default_tab_form_tools_page', 10, 1 );


/**
 * Set default reports page tab.
 *
 * @since  1.8
 * @param  string $default_tab Default tab name.
 * @return string
 */
function walkthecounty_set_default_tab_form_reports_page( $default_tab ) {
	return 'earnings';
}
add_filter( 'walkthecounty_default_setting_tab_walkthecounty-reports', 'walkthecounty_set_default_tab_form_reports_page', 10, 1 );


/**
 * Add a page display state for special WalkTheCounty pages in the page list table.
 *
 * @since 1.8.18
 *
 * @param array $post_states An array of post display states.
 * @param WP_Post $post The current post object.
 *
 * @return array
 */
function walkthecounty_add_display_page_states( $post_states, $post ) {

	switch ( $post->ID ) {
		case walkthecounty_get_option( 'success_page' ):
			$post_states['walkthecounty_successfully_page'] = __( 'Donation Success Page', 'walkthecounty' );
			break;

		case walkthecounty_get_option( 'failure_page' ):
			$post_states['walkthecounty_failure_page'] = __( 'Donation Failed Page', 'walkthecounty' );
			break;

		case walkthecounty_get_option( 'history_page' ):
			$post_states['walkthecounty_history_page'] = __( 'Donation History Page', 'walkthecounty' );
			break;
	}

	return $post_states;
}

// Add a post display state for special WalkTheCounty pages.
add_filter( 'display_post_states', 'walkthecounty_add_display_page_states', 10, 2 );
