<?php
/**
 * WalkTheCounty Settings Page/Tab
 *
 * @package     WalkTheCounty
 * @subpackage  Classes/WalkTheCounty_Settings_Data
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WalkTheCounty_Settings_Data' ) ) :

	/**
	 * WalkTheCounty_Settings_Data.
	 *
	 * @sine 1.8
	 */
	class WalkTheCounty_Settings_Data extends WalkTheCounty_Settings_Page {

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
			$this->id    = 'data';
			$this->label = esc_html__( 'Data', 'walkthecounty' );

			parent::__construct();

			// Do not use main form for this tab.
			if( walkthecounty_get_current_setting_tab() === $this->id ) {
				add_action( "walkthecounty-tools_open_form", '__return_empty_string' );
				add_action( "walkthecounty-tools_close_form", '__return_empty_string' );
			}
		}

		/**
		 * Get settings array.
		 *
		 * @since  1.8
		 * @return array
		 */
		public function get_settings() {
			// Get settings.
			$settings = apply_filters( 'walkthecounty_settings_data', array(
				array(
					'id'   => 'walkthecounty_tools_tools',
					'type' => 'title',
					'table_html' => false
				),
				array(
					'id'   => 'api',
					'name' => esc_html__( 'Tools', 'walkthecounty' ),
					'type' => 'data',
				),
				array(
					'id'   => 'walkthecounty_tools_tools',
					'type' => 'sectionend',
					'table_html' => false
				)
			));

			/**
			 * Filter the settings.
			 *
			 * @since  1.8
			 * @param  array $settings
			 */
			$settings = apply_filters( 'walkthecounty_get_settings_' . $this->id, $settings );

			// Output.
			return $settings;
		}
	}

endif;

return new WalkTheCounty_Settings_Data();
