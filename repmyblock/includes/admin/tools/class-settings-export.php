<?php
/**
 * WalkTheCounty Exports Tab
 *
 * @package     WalkTheCounty
 * @subpackage  Classes/WalkTheCounty_Settings_Export
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WalkTheCounty_Settings_Export' ) ) :

	/**
	 * WalkTheCounty_Settings_Export.
	 *
	 * @sine 1.8
	 */
	class WalkTheCounty_Settings_Export extends WalkTheCounty_Settings_Page {
		/**
		 * Flag to check if enable saving option for setting page or not
		 *
		 * @since 1.8.17
		 * @var bool
		 */
		protected $enable_save = false;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'export';
			$this->label = __( 'Export', 'walkthecounty' );

			parent::__construct();

			add_action( 'walkthecounty_admin_field_tools_export', array( 'WalkTheCounty_Settings_Export', 'render_export_field' ), 10, 2 );

			// Do not use main donor for this tab.
			if( walkthecounty_get_current_setting_tab() === $this->id ) {
				add_action( 'walkthecounty-tools_open_form', '__return_empty_string' );
				add_action( 'walkthecounty-tools_close_form', '__return_empty_string' );


				require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/tools/export/class-walkthecounty-export-donations.php';
			}
		}

		/**
		 * Get settings array.
		 *
		 * @since  1.8
		 * @return array
		 */
		public function get_settings() {
			/**
			 * Filter the settings.
			 *
			 * @since  1.8
			 * @param  array $settings
			 */
			$settings = apply_filters(
				'walkthecounty_get_settings_' . $this->id,
				array(
					array(
						'id'   => 'walkthecounty_tools_export',
						'type' => 'title',
						'table_html' => false
					),
					array(
						'id'   => 'export',
						'name' => __( 'Export', 'walkthecounty' ),
						'type' => 'tools_export',
					),
					array(
						'id'   => 'walkthecounty_tools_export',
						'type' => 'sectionend',
						'table_html' => false
					)
				)
			);

			// Output.
			return $settings;
		}

		/**
		 * Render report export field
		 *
		 * @since  1.8
		 * @access public
		 *
		 * @param $field
		 * @param $option_value
		 */
		public static function render_export_field( $field, $option_value ) {
			include_once( 'views/html-admin-page-exports.php' );
		}
	}

endif;

return new WalkTheCounty_Settings_Export();
