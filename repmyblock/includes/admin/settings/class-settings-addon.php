<?php
/**
 * WalkTheCounty Settings Page/Tab
 *
 * @package     WalkTheCounty
 * @subpackage  Classes/WalkTheCounty_Settings_Addon
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WalkTheCounty_Settings_Addon' ) ) :

	/**
	 * WalkTheCounty_Settings_Addon.
	 *
	 * @sine 1.8
	 */
	class WalkTheCounty_Settings_Addon extends WalkTheCounty_Settings_Page {
		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'addons';
			$this->label = esc_html__( 'Add-ons', 'walkthecounty' );

			parent::__construct();
		}

		/**
		 * Default setting tab.
		 *
		 * @since  1.8
		 * @param  $setting_tab
		 * @return string
		 */
		function set_default_setting_tab( $setting_tab ) {
			$default_tab = '';

			// Set default tab to first setting tab.
			if( $sections = array_keys( $this->get_sections() ) ) {
				$default_tab = current( $sections );
			}
			return $default_tab;
		}

		/**
		 * Add this page to settings.
		 *
		 * @since  1.8
		 * @param  array $pages Lst of pages.
		 * @return array
		 */
		public function add_settings_page( $pages ) {
			$sections = $this->get_sections();

			// Bailout: Do not add addons setting tab if it does not contain any setting fields.
			if( ! empty( $sections ) ) {
				$pages[ $this->id ] = $this->label;
			}

			return $pages;
		}

		/**
		 * Get settings array.
		 *
		 * @since  1.8
		 * @return array
		 */
		public function get_settings() {
			$settings = array();

			/**
			 * Filter the addons settings.
			 * Backward compatibility: Please do not use this filter. This filter is deprecated in 1.8
			 */
			$settings = apply_filters( 'walkthecounty_settings_addons', $settings );

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

return new WalkTheCounty_Settings_Addon();
