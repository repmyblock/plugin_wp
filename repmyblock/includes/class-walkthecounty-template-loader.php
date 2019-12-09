<?php
/**
 * Template Loader
 *
 * @package     WalkTheCounty
 * @subpackage  Classes/WalkTheCounty_Template_Loader
 * @copyright   Copyright (c) 2016, WalkTheCounty
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WalkTheCounty_Template_Loader Class
 *
 * Base class template loader.
 *
 * @since 1.0
 */
class WalkTheCounty_Template_Loader {

	/**
	 * Class Constructor
	 *
	 * Set up the template loader Class.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function __construct() {

		/**
		 * Templates
		 */
		add_filter( 'template_include', array( __CLASS__, 'template_loader' ) );

		/**
		 * Content Wrappers
		 */
		add_action( 'walkthecounty_before_main_content', 'walkthecounty_output_content_wrapper', 10 );
		add_action( 'walkthecounty_after_main_content', 'walkthecounty_output_content_wrapper_end', 10 );

		/**
		 * Entry Summary Classes
		 */
		add_filter( 'walkthecounty_forms_single_summary_classes', array( $this, 'walkthecounty_set_single_summary_classes' ) );

		/**
		 * Sidebar
		 */
		add_action( 'walkthecounty_before_single_form_summary', array( $this, 'walkthecounty_output_sidebar_option' ), 1 );

		/**
		 * Single Forms Summary Box
		 */
		add_action( 'walkthecounty_single_form_summary', 'walkthecounty_template_single_title', 5 );
		add_action( 'walkthecounty_single_form_summary', 'walkthecounty_get_donation_form', 10 );

	}

	/**
	 * WalkTheCounty Set Single Summary Classes
	 *
	 * Determines if the single form should be full width or with a sidebar.
	 *
	 * @access public
	 *
	 * @param  string $classes List of space separated class names.
	 *
	 * @return string $classes List of space separated class names.
	 */
	public function walkthecounty_set_single_summary_classes( $classes ) {

		//Add full width class when feature image is disabled AND no widgets are present
		if ( ! walkthecounty_is_setting_enabled( walkthecounty_get_option( 'form_sidebar' ) ) ) {
			$classes .= ' walkthecounty-full-width';
		}

		return $classes;

	}

	/**
	 * Output sidebar option
	 *
	 * Determines whether the user has enabled or disabled the sidebar for Single WalkTheCounty forms.
	 *
	 * @since  1.3
	 * @access public
	 *
	 * @return void
	 */
	public function walkthecounty_output_sidebar_option() {

		//Add full width class when feature image is disabled AND no widgets are present
		if ( walkthecounty_is_setting_enabled( walkthecounty_get_option( 'form_sidebar' ) ) ) {
			add_action( 'walkthecounty_before_single_form_summary', 'walkthecounty_left_sidebar_pre_wrap', 5 );
			add_action( 'walkthecounty_before_single_form_summary', 'walkthecounty_show_form_images', 10 );
			add_action( 'walkthecounty_before_single_form_summary', 'walkthecounty_get_forms_sidebar', 20 );
			add_action( 'walkthecounty_before_single_form_summary', 'walkthecounty_left_sidebar_post_wrap', 30 );
		}

	}

	/**
	 * Load a template.
	 *
	 * Handles template usage so that we can use our own templates instead of the themes.
	 *
	 * Templates are in the 'templates' folder. WalkTheCounty looks for theme
	 * overrides in /theme/walkthecounty/ by default.
	 *
	 * For beginners, it also looks for a walkthecounty.php template first. If the user adds this
	 * to the theme (containing walkthecounty() inside) this will be used for all walkthecounty templates.
	 *
	 * @access public
	 *
	 * @param  mixed  $template 
	 *
	 * @return string $template
	 */
	public static function template_loader( $template ) {
		$find = array( 'walkthecounty.php' );
		$file = '';

		if ( is_single() && get_post_type() == 'walkthecounty_forms' ) {
			$file   = 'single-walkthecounty-form.php';
			$find[] = $file;
			$find[] = apply_filters( 'walkthecounty_template_path', 'walkthecounty/' ) . $file;
		}

		if ( $file ) {
			$template = locate_template( array_unique( $find ) );
			if ( ! $template ) {
				$template = WALKTHECOUNTY_PLUGIN_DIR . '/templates/' . $file;
			}
		}

		return $template;
	}

}
