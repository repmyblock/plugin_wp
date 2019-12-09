<?php
/**
 * Install Function
 *
 * @package     WalkTheCounty
 * @subpackage  Functions/Install
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Install
 *
 * Runs on plugin install by setting up the post types, custom taxonomies, flushing rewrite rules to initiate the new
 * 'donations' slug and also creates the plugin and populates the settings fields for those plugin pages. After
 * successful install, the user is redirected to the WalkTheCounty Welcome screen.
 *
 * @since 1.0
 *
 * @param bool $network_wide
 *
 * @global     $wpdb
 * @return void
 */
function walkthecounty_install( $network_wide = false ) {

	global $wpdb;

	if ( is_multisite() && $network_wide ) {

		foreach ( $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs LIMIT 100" ) as $blog_id ) {

			switch_to_blog( $blog_id );
			walkthecounty_run_install();
			restore_current_blog();

		}

	} else {

		walkthecounty_run_install();

	}

}

/**
 * Run the WalkTheCounty Install process.
 *
 * @since  1.5
 * @return void
 */
function walkthecounty_run_install() {
	$walkthecounty_options = walkthecounty_get_settings();

	// Setup the WalkTheCounty Custom Post Types.
	walkthecounty_setup_post_types();

	// Add Upgraded From Option.
	$current_version = get_option( 'walkthecounty_version' );
	if ( $current_version ) {
		update_option( 'walkthecounty_version_upgraded_from', $current_version, false );
	}

	// Setup some default options.
	$options = array();

	//Fresh Install? Setup Test Mode, Base Country (US), Test Gateway, Currency.
	if ( empty( $current_version ) ) {
		$options = array_merge( $options, walkthecounty_get_default_settings() );
	}

	// Populate the default values.
	update_option( 'walkthecounty_settings', array_merge( $walkthecounty_options, $options ), false );

	/**
	 * Run plugin upgrades.
	 *
	 * @since 1.8
	 */
	do_action( 'walkthecounty_upgrades' );

	if ( WALKTHECOUNTY_VERSION !== get_option( 'walkthecounty_version' ) ) {
		update_option( 'walkthecounty_version', WALKTHECOUNTY_VERSION, false );
	}

	// Create WalkTheCounty roles.
	$roles = new WalkTheCounty_Roles();
	$roles->add_roles();
	$roles->add_caps();

	// Set api version, end point and refresh permalink.
	$api = new WalkTheCounty_API();
	$api->add_endpoint();
	update_option( 'walkthecounty_default_api_version', 'v' . $api->get_version(), false );

	flush_rewrite_rules();

	// Create databases.
	__walkthecounty_register_tables();

	// Add a temporary option to note that WalkTheCounty pages have been created.
	WalkTheCounty_Cache::set( '_walkthecounty_installed', $options, 30, true );

	if ( ! $current_version ) {

		require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-functions.php';

		// When new upgrade routines are added, mark them as complete on fresh install.
		$upgrade_routines = array(
			'upgrade_walkthecounty_user_caps_cleanup',
			'upgrade_walkthecounty_payment_customer_id',
			'upgrade_walkthecounty_offline_status',
			'v18_upgrades_core_setting',
			'v18_upgrades_form_metadata',
			'v189_upgrades_levels_post_meta',
			'v1812_update_amount_values',
			'v1812_update_donor_purchase_values',
			'v1813_update_user_roles',
			'v1813_update_donor_user_roles',
			'v1817_update_donation_iranian_currency_code',
			'v1817_cleanup_user_roles',
			'v1818_assign_custom_amount_set_donation',
			'v1818_walkthecounty_worker_role_cleanup',
			'v20_upgrades_form_metadata',
			'v20_logs_upgrades',
			'v20_move_metadata_into_new_table',
			'v20_rename_donor_tables',
			'v20_upgrades_donor_name',
			'v20_upgrades_user_address',
			'v20_upgrades_payment_metadata',
			'v201_upgrades_payment_metadata',
			'v201_add_missing_donors',
			'v201_move_metadata_into_new_table',
			'v201_logs_upgrades',
			'v210_verify_form_status_upgrades',
			'v213_delete_donation_meta',
			'v215_update_donor_user_roles',
			'v220_rename_donation_meta_type',
			'v224_update_donor_meta',
			'v224_update_donor_meta_forms_id',
			'v230_move_donor_note',
			'v230_move_donation_note',
			'v230_delete_donor_wall_related_donor_data',
			'v230_delete_donor_wall_related_comment_data',
			'v240_update_form_goal_progress',
			'v241_remove_sale_logs'
		);

		foreach ( $upgrade_routines as $upgrade ) {
			walkthecounty_set_upgrade_complete( $upgrade );
		}
	}

	// Bail if activating from network, or bulk.
	if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
		return;
	}

	// Add the transient to redirect.
	WalkTheCounty_Cache::set( '_walkthecounty_activation_redirect', true, 30, true );
}

/**
 * Network Activated New Site Setup.
 *
 * When a new site is created when WalkTheCounty is network activated this function runs the appropriate install function to set
 * up the site for WalkTheCounty.
 *
 * @since      1.3.5
 *
 * @param  int    $blog_id The Blog ID created.
 * @param  int    $user_id The User ID set as the admin.
 * @param  string $domain  The URL.
 * @param  string $path    Site Path.
 * @param  int    $site_id The Site ID.
 * @param  array  $meta    Blog Meta.
 */
function walkthecounty_on_create_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

	if ( is_plugin_active_for_network( WALKTHECOUNTY_PLUGIN_BASENAME ) ) {

		switch_to_blog( $blog_id );
		walkthecounty_install();
		restore_current_blog();

	}

}

add_action( 'wpmu_new_blog', 'walkthecounty_on_create_blog', 10, 6 );


/**
 * Drop WalkTheCounty's custom tables when a mu site is deleted.
 *
 * @since  1.4.3
 *
 * @param  array $tables  The tables to drop.
 * @param  int   $blog_id The Blog ID being deleted.
 *
 * @return array          The tables to drop.
 */
function walkthecounty_wpmu_drop_tables( $tables, $blog_id ) {

	switch_to_blog( $blog_id );
	$custom_tables = __walkthecounty_get_tables();

	/* @var WalkTheCounty_DB $table */
	foreach ( $custom_tables as $table ) {
		if ( $table->installed() ) {
			$tables[] = $table->table_name;
		}
	}

	restore_current_blog();

	return $tables;

}

add_filter( 'wpmu_drop_tables', 'walkthecounty_wpmu_drop_tables', 10, 2 );

/**
 * Post-installation
 *
 * Runs just after plugin installation and exposes the walkthecounty_after_install hook.
 *
 * @since 1.0
 * @return void
 */
function walkthecounty_after_install() {

	if ( ! is_admin() ) {
		return;
	}

	$walkthecounty_options     = WalkTheCounty_Cache::get( '_walkthecounty_installed', true );
	$walkthecounty_table_check = get_option( '_walkthecounty_table_check', false );

	if ( false === $walkthecounty_table_check || current_time( 'timestamp' ) > $walkthecounty_table_check ) {

		if ( ! @WalkTheCounty()->donor_meta->installed() ) {

			// Create the donor meta database.
			// (this ensures it creates it on multisite instances where it is network activated).
			@WalkTheCounty()->donor_meta->create_table();

		}

		if ( ! @WalkTheCounty()->donors->installed() ) {
			// Create the donor database.
			// (this ensures it creates it on multisite instances where it is network activated).
			@WalkTheCounty()->donors->create_table();

			/**
			 * Fires after plugin installation.
			 *
			 * @since 1.0
			 *
			 * @param array $walkthecounty_options WalkTheCounty plugin options.
			 */
			do_action( 'walkthecounty_after_install', $walkthecounty_options );
		}

		update_option( '_walkthecounty_table_check', ( current_time( 'timestamp' ) + WEEK_IN_SECONDS ), false );

	}

	// Delete the transient
	if ( false !== $walkthecounty_options ) {
		WalkTheCounty_Cache::delete( WalkTheCounty_Cache::get_key( '_walkthecounty_installed' ) );
	}


}

add_action( 'admin_init', 'walkthecounty_after_install' );


/**
 * Install user roles on sub-sites of a network
 *
 * Roles do not get created when WalkTheCounty is network activation so we need to create them during admin_init
 *
 * @since 1.0
 * @return void
 */
function walkthecounty_install_roles_on_network() {

	global $wp_roles;

	if ( ! is_object( $wp_roles ) ) {
		return;
	}

	if ( ! array_key_exists( 'walkthecounty_manager', $wp_roles->roles ) ) {

		// Create WalkTheCounty plugin roles
		$roles = new WalkTheCounty_Roles();
		$roles->add_roles();
		$roles->add_caps();

	}

}

add_action( 'admin_init', 'walkthecounty_install_roles_on_network' );

/**
 * Default core setting values.
 *
 * @since 1.8
 * @return array
 */
function walkthecounty_get_default_settings() {

	$options = array(
		// General.
		'base_country'                                => 'US',
		'test_mode'                                   => 'enabled',
		'currency'                                    => 'USD',
		'currency_position'                           => 'before',
		'session_lifetime'                            => '604800',
		'email_access'                                => 'enabled',
		'thousands_separator'                         => ',',
		'decimal_separator'                           => '.',
		'number_decimals'                             => 2,
		'sequential-ordering_status'                  => 'enabled',

		// Display options.
		'css'                                         => 'enabled',
		'floatlabels'                                 => 'disabled',
		'welcome'                                     => 'enabled',
		'company_field'                               => 'disabled',
		'name_title_prefix'                           => 'disabled',
		'forms_singular'                              => 'enabled',
		'forms_archives'                              => 'enabled',
		'forms_excerpt'                               => 'enabled',
		'form_featured_img'                           => 'enabled',
		'form_sidebar'                                => 'enabled',
		'categories'                                  => 'disabled',
		'tags'                                        => 'disabled',
		'terms'                                       => 'disabled',
		'admin_notices'                               => 'enabled',
		'cache'                                       => 'enabled',
		'uninstall_on_delete'                         => 'disabled',
		'the_content_filter'                          => 'enabled',
		'scripts_footer'                              => 'disabled',
		'agree_to_terms_label'                        => __( 'Agree to Terms?', 'walkthecounty' ),
		'agreement_text'                              => walkthecounty_get_default_agreement_text(),
		'babel_polyfill_script'                       => 'enabled',

		// Paypal IPN verification.
		'paypal_verification'                         => 'enabled',

		// Default is manual gateway.
		'gateways'                                    => array( 'manual' => 1, 'offline' => 1, 'stripe' => 1 ),
		'default_gateway'                             => 'manual',

		// Offline gateway setup.
		'global_offline_donation_content'             => walkthecounty_get_default_offline_donation_content(),
		'global_offline_donation_email'               => walkthecounty_get_default_offline_donation_content(),

		// Billing address.
		'walkthecounty_offline_donation_enable_billing_fields' => 'disabled',

		// Default donation notification email.
		'donation_notification'                       => walkthecounty_get_default_donation_notification_email(),

		// Default email receipt message.
		'donation_receipt'                            => walkthecounty_get_default_donation_receipt_email(),

		'donor_default_user_role'                     => 'walkthecounty_donor',

	);

	return $options;
}

/**
 * Default terms and conditions.
 */
function walkthecounty_get_default_agreement_text() {

	$org_name = get_bloginfo( 'name' );

	$agreement = sprintf(
		'<p>Acceptance of any contribution, gift or grant is at the discretion of the %1$s. The  %1$s will not accept any gift unless it can be used or expended consistently with the purpose and mission of the  %1$s.</p>
				<p>No irrevocable gift, whether outright or life-income in character, will be accepted if under any reasonable set of circumstances the gift would jeopardize the donor’s financial security.</p>
				<p>The %1$s will refrain from providing advice about the tax or other treatment of gifts and will encourage donors to seek guidance from their own professional advisers to assist them in the process of making their donation.</p>
				<p>The %1$s will accept donations of cash or publicly traded securities. Gifts of in-kind services will be accepted at the discretion of the %1$s.</p>
				<p>Certain other gifts, real property, personal property, in-kind gifts, non-liquid securities, and contributions whose sources are not transparent or whose use is restricted in some manner, must be reviewed prior to acceptance due to the special obligations raised or liabilities they may pose for %1$s.</p>
				<p>The %1$s will provide acknowledgments to donors meeting tax requirements for property received by the charity as a gift. However, except for gifts of cash and publicly traded securities, no value shall be ascribed to any receipt or other form of substantiation of a gift received by %1$s.</p>
				<p>The %1$s will respect the intent of the donor relating to gifts for restricted purposes and those relating to the desire to remain anonymous. With respect to anonymous gifts, the %1$s will restrict information about the donor to only those staff members with a need to know.</p>
				<p>The %1$s will not compensate, whether through commissions, finders\' fees, or other means, any third party for directing a gift or a donor to the %1$s.</p>',
		$org_name
	);

	return apply_filters( 'walkthecounty_get_default_agreement_text', $agreement, $org_name );
}


/**
 * This function will install walkthecounty related page which is not created already.
 *
 * @since 1.8.11
 *
 * @return void
 */
function walkthecounty_create_pages() {

	// Bailout if pages already created.
	if ( WalkTheCounty_Cache_Setting::get_option( 'walkthecounty_install_pages_created' ) ) {
		return;
	}

	$options = array();

	// Checks if the Success Page option exists AND that the page exists.
	if ( ! get_post( walkthecounty_get_option( 'success_page' ) ) ) {

		// Donation Confirmation (Success) Page
		$success = wp_insert_post(
			array(
				'post_title'     => esc_html__( 'Donation Confirmation', 'walkthecounty' ),
				'post_content'   => '[walkthecounty_receipt]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		// Store our page IDs
		$options['success_page'] = $success;
	}

	// Checks if the Failure Page option exists AND that the page exists.
	if ( ! get_post( walkthecounty_get_option( 'failure_page' ) ) ) {

		// Failed Donation Page
		$failed = wp_insert_post(
			array(
				'post_title'     => esc_html__( 'Donation Failed', 'walkthecounty' ),
				'post_content'   => esc_html__( 'We\'re sorry, your donation failed to process. Please try again or contact site support.', 'walkthecounty' ),
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		$options['failure_page'] = $failed;
	}

	// Checks if the History Page option exists AND that the page exists.
	if ( ! get_post( walkthecounty_get_option( 'history_page' ) ) ) {
		// Donation History Page
		$history = wp_insert_post(
			array(
				'post_title'     => esc_html__( 'Donation History', 'walkthecounty' ),
				'post_content'   => '[donation_history]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		$options['history_page'] = $history;
	}

	if ( ! empty( $options ) ) {
		update_option( 'walkthecounty_settings', array_merge( walkthecounty_get_settings(), $options ), false );
	}

	add_option( 'walkthecounty_install_pages_created', 1, '', false );
}

// @TODO we can add this hook only when plugin activate instead of every admin page load.
// @see known issue https://github.com/impress-org/walkthecounty/issues/1848
add_action( 'admin_init', 'walkthecounty_create_pages', - 1 );


/**
 * Install tables on plugin update if missing
 * Note: only for internal use
 *
 * @since 2.4.1
 *
 * @param string $old_version
 */
function walkthecounty_install_tables_on_plugin_update( $old_version ) {
	update_option( 'walkthecounty_version_upgraded_from', $old_version, false );
	__walkthecounty_register_tables();
}

add_action( 'update_option_walkthecounty_version', 'walkthecounty_install_tables_on_plugin_update', 0, 2 );


/**
 * Get array of table class objects
 *
 * Note: only for internal purpose use
 *
 * @sice 2.3.1
 *
 */
function __walkthecounty_get_tables() {
	$tables = array(
		'donors_db'       => new WalkTheCounty_DB_Donors(),
		'donor_meta_db'   => new WalkTheCounty_DB_Donor_Meta(),
		'comment_db'      => new WalkTheCounty_DB_Comments(),
		'comment_db_meta' => new WalkTheCounty_DB_Comment_Meta(),
		'walkthecounty_session'    => new WalkTheCounty_DB_Sessions(),
		'log_db'          => new WalkTheCounty_DB_Logs(),
		'logmeta_db'      => new WalkTheCounty_DB_Log_Meta(),
		'formmeta_db'     => new WalkTheCounty_DB_Form_Meta(),
		'sequential_db'   => new WalkTheCounty_DB_Sequential_Ordering(),
		'donation_meta'   => new WalkTheCounty_DB_Payment_Meta(),
	);

	return $tables;
}

/**
 * Register classes
 * Note: only for internal purpose use
 *
 * @sice 2.3.1
 *
 */
function __walkthecounty_register_tables() {
	$tables = __walkthecounty_get_tables();

	/* @var WalkTheCounty_DB $table */
	foreach ( $tables  as $table ) {
		if( ! $table->installed() ) {
			$table->register_table();
		}
	}
}
