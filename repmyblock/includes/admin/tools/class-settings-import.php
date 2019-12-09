<?php
/**
 * WalkTheCounty Settings Page/Tab
 *
 * @package     WalkTheCounty
 * @subpackage  Classes/WalkTheCounty_Settings_Import
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WalkTheCounty_Settings_Import' ) ) {

	/**
	 * WalkTheCounty_Settings_Import.
	 *
	 * Add a submenu page in walkthecounty tools menu called Import donations which import the donations from the CSV files.
	 *
	 * @since 1.8.13
	 */
	class WalkTheCounty_Settings_Import extends WalkTheCounty_Settings_Page {
		/**
		 * Flag to check if enable saving option for setting page or not
		 *
		 * @since 1.8.17
		 * @var bool
		 */
		protected $enable_save = false;

		/**
		 * Importing donation per page.
		 *
		 * @since 1.8.13
		 *
		 * @var   int
		 */
		public static $per_page = 5;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'import';
			$this->label = __( 'Import', 'walkthecounty' );

			parent::__construct();

			// Will display html of the import donation.
			add_action( 'walkthecounty_admin_field_tools_import', array(
				'WalkTheCounty_Settings_Import',
				'render_import_field',
			), 10, 2 );

			// Do not use main form for this tab.
			if ( walkthecounty_get_current_setting_tab() === $this->id ) {
				add_action( "walkthecounty-tools_open_form", '__return_empty_string' );
				add_action( "walkthecounty-tools_close_form", '__return_empty_string' );

				require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/tools/import/class-walkthecounty-import-donations.php';
				require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/tools/import/class-walkthecounty-import-core-settings.php';
			}
		}

		/**
		 * Get settings array.
		 *
		 * @since  1.8.13
		 * @return array
		 */
		public function get_settings() {
			/**
			 * Filter the settings.
			 *
			 * @since  1.8.13
			 *
			 * @param  array $settings
			 */
			$settings = apply_filters(
				'walkthecounty_get_settings_' . $this->id,
				array(
					array(
						'id'         => 'walkthecounty_tools_import',
						'type'       => 'title',
						'table_html' => false,
					),
					array(
						'id'   => 'import',
						'name' => __( 'Import', 'walkthecounty' ),
						'type' => 'tools_import',
					),
					array(
						'name'  => esc_html__( 'Import Docs Link', 'walkthecounty' ),
						'id'    => 'import_docs_link',
						'url'   => esc_url( 'http://docs.walkthecountywp.com/tools-importer' ),
						'title' => __( 'Import Tab', 'walkthecounty' ),
						'type'  => 'walkthecounty_docs_link',
					),
					array(
						'id'         => 'walkthecounty_tools_import',
						'type'       => 'sectionend',
						'table_html' => false,
					),
				)
			);

			// Output.
			return $settings;
		}

		/**
		 * Render report import field
		 *
		 * @since  1.8.13
		 * @access public
		 *
		 * @param $field
		 * @param $option_value
		 */
		public static function render_import_field( $field, $option_value ) {
			include_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/tools/views/html-admin-page-imports.php';
		}
	}
}
return new WalkTheCounty_Settings_Import();
