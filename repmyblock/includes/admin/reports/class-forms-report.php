<?php
/**
 * WalkTheCounty Reports Page/Tab
 *
 * @package     WalkTheCounty
 * @subpackage  Classes/WalkTheCounty_Forms_Report
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WalkTheCounty_Forms_Report' ) ) :

	/**
	 * WalkTheCounty_Forms_Report.
	 *
	 * @sine 1.8
	 */
	class WalkTheCounty_Forms_Report extends WalkTheCounty_Settings_Page {
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
			$this->id    = 'forms';
			$this->label = esc_html__( 'Forms', 'walkthecounty' );

			parent::__construct();

			add_action( 'walkthecounty_admin_field_report_forms', array( $this, 'render_report_forms_field' ), 10, 2 );

			// Do not use main form for this tab.
			if ( walkthecounty_get_current_setting_tab() === $this->id ) {
				add_action( 'walkthecounty-reports_open_form', '__return_empty_string' );
				add_action( 'walkthecounty-reports_close_form', '__return_empty_string' );
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
			 *
			 * @param  array $settings
			 */
			$settings = apply_filters(
				'walkthecounty_get_settings_' . $this->id,
				array(
					array(
						'id'         => 'walkthecounty_reports_forms',
						'type'       => 'title',
						'table_html' => false,
					),
					array(
						'id'   => 'forms',
						'name' => esc_html__( 'Forms', 'walkthecounty' ),
						'type' => 'report_forms',
					),
					array(
						'id'         => 'walkthecounty_reports_forms',
						'type'       => 'sectionend',
						'table_html' => false,
					),
				)
			);

			// Output.
			return $settings;
		}

		/**
		 * Render report forms field
		 *
		 * @since  1.8
		 * @access public
		 *
		 * @param $field
		 * @param $option_value
		 */
		public function render_report_forms_field( $field, $option_value ) {
			do_action( 'walkthecounty_reports_view_forms' );
		}
	}

endif;

return new WalkTheCounty_Forms_Report();
