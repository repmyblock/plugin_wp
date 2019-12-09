<?php
/**
 * WalkTheCounty Settings Page/Tab
 *
 * @package     WalkTheCounty
 * @subpackage  Classes/WalkTheCounty_Settings_System_Info
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WalkTheCounty_Settings_System_Info' ) ) :

	/**
	 * WalkTheCounty_Settings_System_Info.
	 *
	 * @sine 1.8
	 */
	class WalkTheCounty_Settings_System_Info extends WalkTheCounty_Settings_Page {
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
			$this->id    = 'system-info';
			$this->label = esc_html__( 'System Info', 'walkthecounty' );

			parent::__construct();

			// Do not use main form for this tab.
			if ( walkthecounty_get_current_setting_tab() === $this->id ) {
				add_action( "walkthecounty-tools_open_form", '__return_empty_string' );
				add_action( "walkthecounty-tools_close_form", '__return_empty_string' );
			}
		}

		/**
		 * Output the settings.
		 *
		 * @since  1.8
		 * @return void
		 */
		public function output() {
			$GLOBALS['walkthecounty_hide_save_button'] = true;
			include_once( 'views/html-admin-page-system-info.php' );
		}
	}

endif;

return new WalkTheCounty_Settings_System_Info();
