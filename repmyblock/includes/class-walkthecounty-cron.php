<?php
/**
 * Cron
 *
 * @package     WalkTheCounty
 * @subpackage  Classes/WalkTheCounty_Cron
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.3.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * WalkTheCounty_Cron Class
 *
 * This class handles scheduled events.
 *
 * @since 1.3.2
 */
class WalkTheCounty_Cron {

	/**
	 * Instance.
	 *
	 * @since  1.8.13
	 * @access private
	 * @var
	 */
	private static $instance;

	/**
	 * Singleton pattern.
	 *
	 * @since  1.8.13
	 * @access private
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @return static
	 * @since  1.8.13
	 * @access public
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
			self::$instance = new static();
			self::$instance->setup();
		}

		return self::$instance;
	}


	/**
	 * Setup
	 *
	 * @since 1.8.13
	 */
	private function setup() {
		add_filter( 'cron_schedules', array( self::$instance, '__add_schedules' ) );
		add_action( 'wp', array( self::$instance, '__schedule_events' ) );
	}

	/**
	 * Registers new cron schedules
	 *
	 * @param array $schedules An array of non-default cron schedules.
	 *
	 * @return array            An array of non-default cron schedules.
	 * @since  1.3.2
	 * @access public
	 */
	public function __add_schedules( $schedules = array() ) {
		// Adds once weekly to the existing schedules.
		$schedules['weekly'] = array(
			'interval' => 604800, // 7 * 24 * 3600
			'display'  => __( 'Once Weekly', 'walkthecounty' ),
		);

		// Adds once weekly to the existing schedules.
		$schedules['monthly'] = array(
			'interval' => 2592000, // 30 * 24 * 3600
			'display'  => __( 'Once Monthly', 'walkthecounty' ),
		);

		return $schedules;
	}

	/**
	 * Schedules our events
	 *
	 * @return void
	 * @since  1.3.2
	 * @access public
	 */
	public function __schedule_events() {
		$this->monthly_events();
		$this->weekly_events();
		$this->daily_events();
	}

	/**
	 * Schedule monthly events
	 *
	 * @return void
	 * @since  2.5.0
	 * @access private
	 */
	private function monthly_events() {
		if ( ! wp_next_scheduled( 'walkthecounty_monthly_scheduled_events' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'monthly', 'walkthecounty_monthly_scheduled_events' );
		}
	}

	/**
	 * Schedule weekly events
	 *
	 * @return void
	 * @since  1.3.2
	 * @access private
	 */
	private function weekly_events() {
		if ( ! wp_next_scheduled( 'walkthecounty_weekly_scheduled_events' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'weekly', 'walkthecounty_weekly_scheduled_events' );
		}
	}

	/**
	 * Schedule daily events
	 *
	 * @return void
	 * @since  1.3.2
	 * @access private
	 */
	private function daily_events() {
		if ( ! wp_next_scheduled( 'walkthecounty_daily_scheduled_events' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'daily', 'walkthecounty_daily_scheduled_events' );
		}
	}

	/**
	 * get cron job action name
	 *
	 * @param string $type
	 *
	 * @return string
	 * @since  1.8.13
	 * @access public
	 */
	public static function get_cron_action( $type = 'weekly' ) {
		$cron_action = '';

		switch ( $type ) {
			case 'daily':
				$cron_action = 'walkthecounty_daily_scheduled_events';
				break;

			case 'monthly':
				$cron_action = 'walkthecounty_monthly_scheduled_events';
				break;

			case 'weekly':
				$cron_action = 'walkthecounty_weekly_scheduled_events';
				break;
		}

		return $cron_action;
	}

	/**
	 * Add action to cron action
	 *
	 * @param string $callback
	 * @param string $type
	 *
	 * @since  1.8.13
	 * @access private
	 */
	private static function add_event( $callback, $type = 'weekly' ) {
		$cron_event = self::get_cron_action( $type );
		add_action( $cron_event, $callback );
	}

	/**
	 * Add weekly event
	 *
	 * @param string $callback
	 *
	 * @since  1.8.13
	 * @access public
	 */
	public static function add_weekly_event( $callback ) {
		self::add_event( $callback );
	}

	/**
	 * Add daily event
	 *
	 * @param $callback
	 *
	 * @since  1.8.13
	 * @access public
	 */
	public static function add_daily_event( $callback ) {
		self::add_event( $callback, 'daily' );
	}

	/**
	 * Add monthly event
	 *
	 * @param $callback
	 *
	 * @since  2.5.0
	 * @access public
	 */
	public static function add_monthly_event( $callback ) {
		self::add_event( $callback, 'monthly' );
	}
}

// Initiate class.
WalkTheCounty_Cron::get_instance();
