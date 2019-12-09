<?php
/**
 * WalkTheCounty Settings Page/Tab
 *
 * @package     WalkTheCounty
 * @subpackage  Classes/WalkTheCounty_Settings_Logs
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WalkTheCounty_Settings_Logs' ) ) :

	/**
	 * WalkTheCounty_Settings_Logs.
	 *
	 * @sine 1.8
	 */
	class WalkTheCounty_Settings_Logs extends WalkTheCounty_Settings_Page {
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
			$this->id    = 'logs';
			$this->label = __( 'Logs', 'walkthecounty' );

			$this->default_tab = 'gateway_errors';

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
			$settings = apply_filters( 'walkthecounty_settings_logs', array(
				array(
					'id'         => 'walkthecounty_tools_logs',
					'type'       => 'title',
					'table_html' => false,
				),
				array(
					'id'   => 'logs',
					'name' => __( 'Log', 'walkthecounty' ),
					'type' => 'logs',

				),
				array(
					'id'         => 'walkthecounty_tools_logs',
					'type'       => 'sectionend',
					'table_html' => false,
				),
			) );

			/**
			 * Filter the settings.
			 *
			 * @since  1.8
			 *
			 * @param  array $settings
			 */
			$settings = apply_filters( 'walkthecounty_get_settings_' . $this->id, $settings );

			// Output.
			return $settings;
		}

		/**
		 * Get sections.
		 *
		 * @since 1.8
		 * @return array
		 */
		public function get_sections() {
			$sections = array(
				'gateway_errors' => __( 'Payment Errors', 'walkthecounty' ),
				'api_requests'   => __( 'API Requests', 'walkthecounty' ),
				'updates'   => __( 'Updates', 'walkthecounty' ),
			);

			$sections = apply_filters( 'walkthecounty_log_views', $sections );

			return apply_filters( 'walkthecounty_get_sections_' . $this->id, $sections );
		}
	}

endif;

return new WalkTheCounty_Settings_Logs();
