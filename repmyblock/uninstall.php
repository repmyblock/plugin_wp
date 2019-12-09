<?php
/**
 * Uninstall WalkTheCounty
 *
 * @package     WalkTheCounty
 * @subpackage  Uninstall
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Load WalkTheCounty file.
include_once( 'walkthecounty.php' );

global $wpdb, $wp_roles;


if ( walkthecounty_is_setting_enabled( walkthecounty_get_option( 'uninstall_on_delete' ) ) ) {

	// Delete All the Custom Post Types.
	$walkthecounty_taxonomies = array( 'form_category', 'form_tag' );
	$walkthecounty_post_types = array( 'walkthecounty_forms', 'walkthecounty_payment' );
	foreach ( $walkthecounty_post_types as $post_type ) {

		$walkthecounty_taxonomies = array_merge( $walkthecounty_taxonomies, get_object_taxonomies( $post_type ) );
		$items           = get_posts( array(
			'post_type'   => $post_type,
			'post_status' => 'any',
			'numberposts' => - 1,
			'fields'      => 'ids',
		) );

		if ( $items ) {
			foreach ( $items as $item ) {
				wp_delete_post( $item, true );
			}
		}
	}

	// Delete All the Terms & Taxonomies.
	foreach ( array_unique( array_filter( $walkthecounty_taxonomies ) ) as $taxonomy ) {

		$terms = $wpdb->get_results( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('%s') ORDER BY t.name ASC", $taxonomy ) );

		// Delete Terms.
		if ( $terms ) {
			foreach ( $terms as $term ) {
				$wpdb->delete( $wpdb->term_taxonomy, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
				$wpdb->delete( $wpdb->terms, array( 'term_id' => $term->term_id ) );
			}
		}

		// Delete Taxonomies.
		$wpdb->delete( $wpdb->term_taxonomy, array( 'taxonomy' => $taxonomy ), array( '%s' ) );
	}

	// Delete the Plugin Pages.
	$walkthecounty_created_pages = array( 'success_page', 'failure_page', 'history_page' );
	foreach ( $walkthecounty_created_pages as $p ) {
		$page = walkthecounty_get_option( $p, false );
		if ( $page ) {
			wp_delete_post( $page, true );
		}
	}

	// Delete Capabilities.
	WalkTheCounty()->roles = new WalkTheCounty_Roles();
	WalkTheCounty()->roles->remove_caps();

	// Delete the Roles.
	$walkthecounty_roles = array( 'walkthecounty_manager', 'walkthecounty_accountant', 'walkthecounty_worker', 'walkthecounty_donor' );
	foreach ( $walkthecounty_roles as $role ) {
		remove_role( $role );
	}

	// Remove all database tables.
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}walkthecounty_donors" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}walkthecounty_donormeta" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}walkthecounty_donationmeta" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}walkthecounty_formmeta" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}walkthecounty_logs" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}walkthecounty_logmeta" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}walkthecounty_comments" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}walkthecounty_commentmeta" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}walkthecounty_sequential_ordering" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}walkthecounty_sessions" );

	// Remove tables which are supported with backward compatibility.
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}walkthecounty_customers" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}walkthecounty_customermeta" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}walkthecounty_paymentmeta" );

	// Cleanup Cron Events.
	wp_clear_scheduled_hook( 'walkthecounty_daily_scheduled_events' );
	wp_clear_scheduled_hook( 'walkthecounty_weekly_scheduled_events' );
	wp_clear_scheduled_hook( 'walkthecounty_daily_cron' );
	wp_clear_scheduled_hook( 'walkthecounty_weekly_cron' );

	// Get all options.
	$walkthecounty_option_names = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT option_name FROM {$wpdb->options} where option_name LIKE '%%%s%%'",
			'walkthecounty'
		)
	);

	if ( ! empty( $walkthecounty_option_names ) ) {
		// Convert option name to transient or option name.
		$new_walkthecounty_option_names = array();

		// Delete all the Plugin Options.
		foreach ( $walkthecounty_option_names as $option ) {
			if ( false !== strpos( $option, 'walkthecounty_cache' ) ) {
				WalkTheCounty_Cache::delete( $option );
			} else {
				delete_option( $option );
			}
		}
	}
}
