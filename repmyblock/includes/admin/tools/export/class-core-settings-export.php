<?php
/**
 * Core Settings Export Class
 *
 * This class handles the export of WalkTheCounty's core settings
 *
 * @package     WalkTheCounty
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.8.17
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WalkTheCounty_Core_Settings_Export Class
 *
 * @since 1.8.17
 */
class WalkTheCounty_Core_Settings_Export extends WalkTheCounty_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var string
	 * @since 1.8.17
	 */
	public $export_type = 'settings';

	/**
	 * Set the export headers
	 *
	 * @access public
	 * @since  1.8.17
	 * @return void
	 */
	public function headers() {
		walkthecounty_ignore_user_abort();

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . apply_filters( 'walkthecounty_core_settings_export_filename', 'walkthecounty-export-' . $this->export_type . '-' . date( 'n' ) . '-' . date( 'Y' ) ) . '.json' );
		header( 'Expires: 0' );
	}

	/**
	 * Prints WalkTheCounty's core settings in JSON format
	 *
	 * @access public
	 * @since 1.8.17
	 */
	public function json_core_settings_export() {
		$settings_excludes = isset( $_POST['settings_export_excludes'] ) ? walkthecounty_clean( $_POST['settings_export_excludes'] ) : array();
		$walkthecounty_settings     = WalkTheCounty_Cache_Setting::get_settings();

		if ( is_array( $settings_excludes ) && ! empty( $settings_excludes ) ) {
			foreach ( $settings_excludes as $key => $value ) {
				if ( walkthecounty_is_setting_enabled( $value ) ) {
					unset( $walkthecounty_settings[ $key ] );
				}
			}
		}

		echo wp_json_encode( $walkthecounty_settings );
	}

	/**
	 * Get the Export Data
	 *
	 * @access public
	 * @since  1.8.17
	 */
	public function export() {
		if ( ! $this->can_export() ) {
			wp_die(
				esc_html__( 'You do not have permission to export data.', 'walkthecounty' ),
				esc_html__( 'Error', 'walkthecounty' ),
				array( 'response' => 403 )
			);
		}

		// Set headers.
		$this->headers();

		$this->json_core_settings_export();

		walkthecounty_die();
	}
}
