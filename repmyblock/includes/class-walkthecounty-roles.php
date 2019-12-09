<?php
/**
 * Roles and Capabilities
 *
 * @package     WalkTheCounty
 * @subpackage  Classes/WalkTheCounty_Roles
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WalkTheCounty_Roles Class
 *
 * This class handles the role creation and assignment of capabilities for those roles.
 *
 * These roles let us have WalkTheCounty Accountants, WalkTheCounty Workers, etc, each of whom can do
 * certain things within the plugin.
 *
 * @since 1.0
 */
class WalkTheCounty_Roles {

	/**
	 * Class Constructor
	 *
	 * Set up the WalkTheCounty Roles Class.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function __construct() {
		add_filter( 'walkthecounty_map_meta_cap', array( $this, 'meta_caps' ), 10, 4 );
		add_filter( 'woocommerce_disable_admin_bar', array( $this, 'manage_admin_dashboard' ), 10, 1 );
		add_filter( 'woocommerce_prevent_admin_access', array( $this, 'manage_admin_dashboard' ), 10 );
	}

	/**
	 * Add Roles
	 *
	 * Add new shop roles with default WordPress capabilities.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return void
	 */
	public function add_roles() {
		add_role( 'walkthecounty_manager', __( 'WalkTheCountyWP Manager', 'walkthecounty' ), array(
			'read'                   => true,
			'edit_posts'             => true,
			'delete_posts'           => true,
			'unfiltered_html'        => true,
			'upload_files'           => true,
			'export'                 => false,
			'import'                 => false,
			'delete_others_pages'    => false,
			'delete_others_posts'    => false,
			'delete_pages'           => true,
			'delete_private_pages'   => true,
			'delete_private_posts'   => true,
			'delete_published_pages' => true,
			'delete_published_posts' => true,
			'edit_others_pages'      => false,
			'edit_others_posts'      => false,
			'edit_pages'             => true,
			'edit_private_pages'     => true,
			'edit_private_posts'     => true,
			'edit_published_pages'   => true,
			'edit_published_posts'   => true,
			'manage_categories'      => false,
			'manage_links'           => true,
			'moderate_comments'      => true,
			'publish_pages'          => true,
			'publish_posts'          => true,
			'read_private_pages'     => true,
			'read_private_posts'     => true,
		) );

		add_role( 'walkthecounty_accountant', __( 'WalkTheCountyWP Accountant', 'walkthecounty' ), array(
			'read'         => true,
			'edit_posts'   => false,
			'delete_posts' => false,
		) );

		add_role( 'walkthecounty_worker', __( 'WalkTheCountyWP Worker', 'walkthecounty' ), array(
			'read'         => true,
			'edit_posts'   => true,
			'edit_pages'   => true,
			'upload_files' => true,
			'delete_posts' => false,
		) );

		add_role( 'walkthecounty_donor', __( 'WalkTheCountyWP Donor', 'walkthecounty' ), array(
			'read' => true,
		) );

	}

	/**
	 * Add Capabilities
	 *
	 * Add new shop-specific capabilities.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @global WP_Roles $wp_roles
	 *
	 * @return void
	 */
	public function add_caps() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}
		}

		if ( is_object( $wp_roles ) ) {
			$wp_roles->add_cap( 'walkthecounty_manager', 'view_walkthecounty_reports' );
			$wp_roles->add_cap( 'walkthecounty_manager', 'view_walkthecounty_sensitive_data' );
			$wp_roles->add_cap( 'walkthecounty_manager', 'export_walkthecounty_reports' );
			$wp_roles->add_cap( 'walkthecounty_manager', 'manage_walkthecounty_settings' );
			$wp_roles->add_cap( 'walkthecounty_manager', 'view_walkthecounty_payments' );

			$wp_roles->add_cap( 'administrator', 'view_walkthecounty_reports' );
			$wp_roles->add_cap( 'administrator', 'view_walkthecounty_sensitive_data' );
			$wp_roles->add_cap( 'administrator', 'export_walkthecounty_reports' );
			$wp_roles->add_cap( 'administrator', 'manage_walkthecounty_settings' );
			$wp_roles->add_cap( 'administrator', 'view_walkthecounty_payments' );

			// Add the main post type capabilities.
			$capabilities = $this->get_core_caps();
			foreach ( $capabilities as $cap_group ) {
				foreach ( $cap_group as $cap ) {
					$wp_roles->add_cap( 'administrator', $cap );
					$wp_roles->add_cap( 'walkthecounty_manager', $cap );
				}
			}

			// Add Capabilities to WalkTheCounty Workers User Role.
			$wp_roles->add_cap( 'walkthecounty_worker', 'edit_walkthecounty_payments' );
			$wp_roles->add_cap( 'walkthecounty_worker', 'delete_walkthecounty_forms' );
			$wp_roles->add_cap( 'walkthecounty_worker', 'delete_others_walkthecounty_forms' );
			$wp_roles->add_cap( 'walkthecounty_worker', 'delete_private_walkthecounty_forms' );
			$wp_roles->add_cap( 'walkthecounty_worker', 'delete_published_walkthecounty_forms' );
			$wp_roles->add_cap( 'walkthecounty_worker', 'edit_walkthecounty_forms' );
			$wp_roles->add_cap( 'walkthecounty_worker', 'edit_others_walkthecounty_forms' );
			$wp_roles->add_cap( 'walkthecounty_worker', 'edit_private_walkthecounty_forms' );
			$wp_roles->add_cap( 'walkthecounty_worker', 'edit_published_walkthecounty_forms' );
			$wp_roles->add_cap( 'walkthecounty_worker', 'publish_walkthecounty_forms' );
			$wp_roles->add_cap( 'walkthecounty_worker', 'read_private_walkthecounty_forms' );

			// Add Capabilities to WalkTheCounty Accountant User Role.
			$wp_roles->add_cap( 'walkthecounty_accountant', 'edit_walkthecounty_forms' );
			$wp_roles->add_cap( 'walkthecounty_accountant', 'read_private_walkthecounty_forms' );
			$wp_roles->add_cap( 'walkthecounty_accountant', 'view_walkthecounty_reports' );
			$wp_roles->add_cap( 'walkthecounty_accountant', 'export_walkthecounty_reports' );
			$wp_roles->add_cap( 'walkthecounty_accountant', 'edit_walkthecounty_payments' );
			$wp_roles->add_cap( 'walkthecounty_accountant', 'view_walkthecounty_payments' );

		}
	}

	/**
	 * Get Core Capabilities
	 *
	 * Retrieve core post type capabilities.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return array $capabilities Core post type capabilities.
	 */
	public function get_core_caps() {
		$capabilities = array();

		$capability_types = array( 'walkthecounty_form', 'walkthecounty_payment' );

		foreach ( $capability_types as $capability_type ) {
			$capabilities[ $capability_type ] = array(
				// Post type.
				"edit_{$capability_type}",
				"read_{$capability_type}",
				"delete_{$capability_type}",
				"edit_{$capability_type}s",
				"edit_others_{$capability_type}s",
				"publish_{$capability_type}s",
				"read_private_{$capability_type}s",
				"delete_{$capability_type}s",
				"delete_private_{$capability_type}s",
				"delete_published_{$capability_type}s",
				"delete_others_{$capability_type}s",
				"edit_private_{$capability_type}s",
				"edit_published_{$capability_type}s",

				// Terms / taxonomies.
				"manage_{$capability_type}_terms",
				"edit_{$capability_type}_terms",
				"delete_{$capability_type}_terms",
				"assign_{$capability_type}_terms",

				// Custom capabilities.
				"view_{$capability_type}_stats",
				"import_{$capability_type}s",
			);
		}

		return $capabilities;
	}

	/**
	 * Meta Capabilities
	 *
	 * Map meta capabilities to primitive capabilities.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array  $caps    Returns the user's actual capabilities.
	 * @param  string $cap     Capability name.
	 * @param  int    $user_id The user ID.
	 * @param  array  $args    Adds the context to the cap. Typically the object ID.
	 *
	 * @return array  $caps    Meta capabilities.
	 */
	public function meta_caps( $caps, $cap, $user_id, $args ) {

		switch ( $cap ) {

			case 'view_walkthecounty_form_stats' :

				if ( empty( $args[0] ) ) {
					break;
				}

				$form = get_post( $args[0] );
				if ( empty( $form ) ) {
					break;
				}

				if ( user_can( $user_id, 'view_walkthecounty_reports' ) || $user_id == $form->post_author ) {
					$caps = array();
				}

				break;
		}

		return $caps;

	}

	/**
	 * Remove Capabilities
	 *
	 * Remove core post type capabilities (called on uninstall).
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @global WP_Roles $wp_roles
	 *
	 * @return void
	 */
	public function remove_caps() {

		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}
		}

		if ( is_object( $wp_roles ) ) {
			// WalkTheCounty Manager Capabilities.
			$wp_roles->remove_cap( 'walkthecounty_manager', 'view_walkthecounty_reports' );
			$wp_roles->remove_cap( 'walkthecounty_manager', 'view_walkthecounty_sensitive_data' );
			$wp_roles->remove_cap( 'walkthecounty_manager', 'export_walkthecounty_reports' );
			$wp_roles->remove_cap( 'walkthecounty_manager', 'manage_walkthecounty_settings' );

			// Site Administrator Capabilities.
			$wp_roles->remove_cap( 'administrator', 'view_walkthecounty_reports' );
			$wp_roles->remove_cap( 'administrator', 'view_walkthecounty_sensitive_data' );
			$wp_roles->remove_cap( 'administrator', 'export_walkthecounty_reports' );
			$wp_roles->remove_cap( 'administrator', 'manage_walkthecounty_settings' );
			$wp_roles->remove_cap( 'administrator', 'view_walkthecounty_payments' );

			// Remove the Main Post Type Capabilities.
			$capabilities = $this->get_core_caps();

			foreach ( $capabilities as $cap_group ) {
				foreach ( $cap_group as $cap ) {
					$wp_roles->remove_cap( 'walkthecounty_manager', $cap );
					$wp_roles->remove_cap( 'administrator', $cap );

				}
			}

			// Remove capabilities from the WalkTheCounty Worker role.
			$wp_roles->remove_cap( 'walkthecounty_worker', 'edit_walkthecounty_payments' );
			$wp_roles->remove_cap( 'walkthecounty_worker', 'delete_walkthecounty_forms' );
			$wp_roles->remove_cap( 'walkthecounty_worker', 'delete_others_walkthecounty_forms' );
			$wp_roles->remove_cap( 'walkthecounty_worker', 'delete_private_walkthecounty_forms' );
			$wp_roles->remove_cap( 'walkthecounty_worker', 'delete_published_walkthecounty_forms' );
			$wp_roles->remove_cap( 'walkthecounty_worker', 'edit_walkthecounty_forms' );
			$wp_roles->remove_cap( 'walkthecounty_worker', 'edit_others_walkthecounty_forms' );
			$wp_roles->remove_cap( 'walkthecounty_worker', 'edit_private_walkthecounty_forms' );
			$wp_roles->remove_cap( 'walkthecounty_worker', 'edit_published_walkthecounty_forms' );
			$wp_roles->remove_cap( 'walkthecounty_worker', 'publish_walkthecounty_forms' );
			$wp_roles->remove_cap( 'walkthecounty_worker', 'read_private_walkthecounty_forms' );

			// Remove Capabilities from WalkTheCounty Accountant User Role.
			$wp_roles->remove_cap( 'walkthecounty_accountant', 'edit_walkthecounty_forms' );
			$wp_roles->remove_cap( 'walkthecounty_accountant', 'read_private_walkthecounty_forms' );
			$wp_roles->remove_cap( 'walkthecounty_accountant', 'view_walkthecounty_reports' );
			$wp_roles->remove_cap( 'walkthecounty_accountant', 'export_walkthecounty_reports' );
			$wp_roles->remove_cap( 'walkthecounty_accountant', 'edit_walkthecounty_payments' );
			$wp_roles->remove_cap( 'walkthecounty_accountant', 'view_walkthecounty_payments' );

		}
	}

	/**
	 * Allow admin dashboard to User with WalkTheCounty Accountant Role.
	 *
	 * Note: WooCommerce doesn't allow the user to access the WP dashboard who holds "WalkTheCounty Accountant" role.
	 *
	 * @since 1.8.14
	 * @updated 1.8.18 - Fixed WalkTheCounty conflicting by not returning $show_admin_bar https://github.com/impress-org/walkthecounty/issues/2539
	 *
	 * @param bool
	 *
	 * @return bool
	 */
	public function manage_admin_dashboard($show_admin_bar) {

		// Get the current logged user.
		$current_user = wp_get_current_user();

		// If user with "WalkTheCounty Accountant" user role is logged-in .
		if ( 0 !== $current_user->ID && in_array( 'walkthecounty_accountant', (array) $current_user->roles, true ) ) {

			// Return false, means no prevention.
			return false;
		}

		return $show_admin_bar;

	}
}
