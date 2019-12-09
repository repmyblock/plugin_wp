<?php
/**
 * WalkTheCounty Reports Page/Tab
 *
 * @package     WalkTheCounty
 * @subpackage  Classes/WalkTheCounty_Gateways_Report
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WalkTheCounty_Gateways_Report' ) ) :

	/**
	 * WalkTheCounty_Gateways_Report.
	 *
	 * @sine 1.8
	 */
	class WalkTheCounty_Gateways_Report extends WalkTheCounty_Settings_Page {
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
			$this->id    = 'gateways';
			$this->label = esc_html__( 'Donation Methods', 'walkthecounty' );

			parent::__construct();

			add_action( 'walkthecounty_admin_field_report_gateways', array( $this, 'render_report_gateways_field' ), 10, 2 );

			// Do not use main form for this tab.
			if ( walkthecounty_get_current_setting_tab() === $this->id ) {
				add_action( 'walkthecounty-reports_open_form', '__return_empty_string' );
				add_action( 'walkthecounty-reports_close_form', '__return_empty_string' );
			}
		}


		/**
		 * Get sections.
		 *
		 * @since  1.8.17
		 * @access public
		 *
		 * @return array
		 */
		public function get_sections() {
			return array();
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
			 *
			 * @param  array $settings
			 */
			$settings = apply_filters(
				'walkthecounty_get_settings_' . $this->id,
				array(
					array(
						'id'         => 'walkthecounty_reports_gateways',
						'type'       => 'title',
						'table_html' => false,
					),
					array(
						'id'   => 'gateways',
						'name' => esc_html__( 'Gateways', 'walkthecounty' ),
						'type' => 'report_gateways',
					),
					array(
						'id'         => 'walkthecounty_reports_gateways',
						'type'       => 'sectionend',
						'table_html' => false,
					),
				)
			);

			// Output.
			return $settings;
		}

		/**
		 * Render earning field
		 *
		 * @since  1.8
		 * @access public
		 *
		 * @param $field
		 * @param $option_value
		 */
		public function render_report_gateways_field( $field, $option_value ) {
			do_action( 'walkthecounty_reports_view_gateways' );
		}
	}

endif;

return new WalkTheCounty_Gateways_Report();
