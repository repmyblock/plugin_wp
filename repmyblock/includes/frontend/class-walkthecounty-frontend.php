<?php

/**
 * This class will handle file loading for frontend.
 *
 * @package     WalkTheCounty
 * @subpackage  Frontend
 * @copyright   Copyright (c) 2018, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.4.0
 */
class WalkTheCounty_Frontend {
	/**
	 * Instance.
	 *
	 * @since  2.4.0
	 * @access private
	 * @var
	 */
	static private $instance;

	/**
	 * Singleton pattern.
	 *
	 * @since  2.4.0
	 * @access private
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @since  2.4.0
	 * @access public
	 * @return WalkTheCounty_Frontend
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
			self::$instance = new static();
			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * Setup Admin
	 *
	 * @sinve  2.4.0
	 * @access private
	 */
	private function setup() {
		$this->frontend_loading();

		add_action( 'walkthecounty_init', array( $this, 'bc_240' ), 0 );
	}

	/**
	 *  Load core file
	 *
	 * @since  2.4.0
	 * @access private
	 */
	private function frontend_loading() {
		require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/class-walkthecounty-template-loader.php';
		require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/class-walkthecounty-email-access.php'; // @todo: [refactor] can be load only for success and history page.
	}

	/**
	 * Backward compatibility WALKTHECOUNTY_VERSION < 2.4.0
	 *
	 * @since 2.4.0
	 * @ccess public
	 *
	 * @param WalkTheCounty $walkthecounty
	 */
	public function bc_240( $walkthecounty ) {
		$walkthecounty->template_loader = new WalkTheCounty_Template_Loader();
		$walkthecounty->email_access    = new WalkTheCounty_Email_Access();
	}
}

WalkTheCounty_Frontend::get_instance();
