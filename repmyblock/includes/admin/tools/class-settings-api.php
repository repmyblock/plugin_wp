<?php
/**
 * WalkTheCounty Settings Page/Tab
 *
 * @package     WalkTheCounty
 * @subpackage  Classes/WalkTheCounty_Settings_API
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WalkTheCounty_Settings_API' ) ) :

	/**
	 * WalkTheCounty_Settings_API.
	 *
	 * @sine 1.8
	 */
	class WalkTheCounty_Settings_API extends WalkTheCounty_Settings_Page {
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
			$this->id    = 'api';
			$this->label = esc_html__( 'API', 'walkthecounty' );

			parent::__construct();
		}

		/**
		 * Get settings array.
		 *
		 * @since  1.8
		 * @return array
		 */
		public function get_settings() {
			// Get settings.
			$settings = apply_filters( 'walkthecounty_settings_api', array(
				array(
					'id'   => 'walkthecounty_tools_api',
					'type' => 'title',
					'table_html' => false
				),
				array(
					'id'   => 'api',
					'name' => esc_html__( 'API', 'walkthecounty' ),
					'type' => 'api',
				),
				array(
					'id'   => 'walkthecounty_tools_api',
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

return new WalkTheCounty_Settings_API();
