<?php
/**
 * Background Process
 *
 * Uses https://github.com/A5hleyRich/wp-background-processing to handle DB
 * updates in the background.
 *
 * @class    WalkTheCounty_Async_Request
 * @version  2.0.0
 * @package  WalkTheCounty/Classes
 * @category Class
 * @author   WalkTheCountyWP
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WalkTheCounty_Background_Updater Class.
 */
class WalkTheCounty_Async_Process extends WP_Async_Request {
	/**
	 * Prefix
	 *
	 *
	 * @var string
	 * @access protected
	 */
	protected $prefix = 'walkthecounty';

	/**
	 * Dispatch updater.
	 *
	 * Updater will still run via cron job if this fails for any reason.
	 */
	public function dispatch() {
		/* @var WP_Async_Request $dispatched */
		parent::dispatch();
	}

	/**
	 * Handle
	 *
	 * Override this method to perform any actions required
	 * during the async request.
	 */
	protected function handle() {
		/*
		 * $data = array(
		 *  'hook'     => '', // required
		 *  'data' => {mixed} // required
		 * )
		 */

		$_post = walkthecounty_clean( $_POST );

		if ( empty( $_post ) || empty( $_post['data'] ) || empty( $_post['hook'] ) ) {
			exit();
		}

		/**
		 * Fire the hook.
		 */
		do_action( $_post['hook'], $_post['data'] );

		exit();
	}
}
