<?php
/**
 * Class for managing plugin settings cache
 *
 * Note: only use for internal purpose.
 *
 * @package     WalkTheCounty
 * @subpackage  Classes/WalkTheCounty_Cache_Setting
 * @copyright   Copyright (c) 2018, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.4.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WalkTheCounty_Cache_Setting
 */
class WalkTheCounty_Cache_Setting {
	/**
	 * Instance.
	 *
	 * @since  2.4.0
	 * @access private
	 * @var WalkTheCounty_Cache_Setting
	 */
	static private $instance;

	/**
	 * Cache key.
	 *
	 * @since  2.4.0
	 * @access private
	 * @var string
	 */
	private $cache_key = 'walkthecountyAllOptions';


	/**
	 * Cache group.
	 *
	 * @since  2.4.0
	 * @access private
	 * @var string
	 */
	private $cache_group = 'walkthecounty-options';

	/**
	 * Array of cached settings
	 *
	 * @since  2.4.0
	 * @access private
	 * @var array
	 */
	private $settings = array(
		'walkthecounty_settings'                        => array(),
		'walkthecounty_version'                         => '',
		'walkthecounty_completed_upgrades'              => array(),
		'walkthecounty_doing_upgrade'                   => array(),
		'walkthecounty_paused_batches'                  => array(),
		'walkthecounty_install_pages_created'           => '',
		'walkthecounty_show_db_upgrade_complete_notice' => '',
		'walkthecounty_addon_last_activated'            => '',
		'currencies'                           => array(),
		'gateways'                             => array(),
	);

	/**
	 * Array of cached setting db option names
	 *
	 * @since  2.4.0
	 * @access private
	 * @var array
	 */
	private $db_option_ids = array(
		'walkthecounty_settings',
		'walkthecounty_version',
		'walkthecounty_completed_upgrades',
		'walkthecounty_doing_upgrade',
		'walkthecounty_install_pages_created',
		'walkthecounty_show_db_upgrade_complete_notice',
		'walkthecounty_addon_last_activated',
		'walkthecounty_paused_batches',
	);

	/**
	 * Array of cached setting option names
	 *
	 * @since  2.4.0
	 * @access private
	 * @var array
	 */
	static private $all_option_ids;

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
	 * @return WalkTheCounty_Cache_Setting
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
	 * @since  2.4.0
	 * @access private
	 */
	private function setup() {
		self::$all_option_ids = array_keys( $this->settings );

		$this->load_plugin_settings();

		add_action( 'added_option', array( $this, 'reload_plugin_settings' ) );
		add_action( 'updated_option', array( $this, 'reload_plugin_settings' ) );
		add_action( 'deleted_option', array( $this, 'reload_plugin_settings' ) );

		add_action( 'walkthecounty_init', array( $this, 'setup_currencies_list' ), 11 );
		add_action( 'walkthecounty_init', array( $this, 'setup_gateways_list' ), 11 );
	}

	/**
	 * Load plugin settings
	 *
	 * @since  2.4.0
	 * @access private
	 */
	private function load_plugin_settings() {
		global $wpdb;

		/**
		 * Fire the filter
		 *
		 * This filter can be used if admin facing any caching issue.
		 * This is a switch to enable or disable setting cache.
		 * Thus filter can be removed in future.
		 *
		 * @since 2.4.1
		 *
		 */
		if ( ! apply_filters( 'walkthecounty_disable_setting_cache', false ) ) {
			$cache = wp_cache_get( $this->cache_key, $this->cache_group );

			// Load options from cache.
			if ( ! empty( $cache ) ) {
				$this->settings = $cache;

				return;
			}
		}

		$db_option_ids = '\'' . implode( '\',\'', $this->db_option_ids ) . '\'';

		$sql     = "SELECT option_name, option_value FROM $wpdb->options WHERE option_name IN ({$db_option_ids}) ";
		$results = $wpdb->get_results( $sql );

		if ( ! empty( $results ) ) {

			/* @var  stdClass $result */
			foreach ( $results as $result ) {
				$this->settings[ $result->option_name ] = maybe_unserialize( $result->option_value );
			}

			wp_cache_set( $this->cache_key, $this->settings, $this->cache_group );
		}
	}

	/**
	 * Reload option when add, update or delete
	 *
	 * Note: only for internal logic
	 *
	 * @since 2.4.0
	 *
	 * @param $option_name
	 */
	public function reload_plugin_settings( $option_name ) {
		// Bailout.
		if ( ! in_array( $option_name, $this->db_option_ids ) ) {
			return;
		}

		wp_cache_delete( $this->cache_key, $this->cache_group );
		$this->load_plugin_settings();
	}

	/**
	 * Setup currencies list
	 *
	 * @since 2.4.0
	 */
	public function setup_currencies_list() {
		$currencies = require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/currencies-list.php';

		/**
		 * Filter the supported currency list
		 *
		 * @since 2.4.0
		 */
		$currencies = apply_filters( 'walkthecounty_register_currency', $currencies );

		$this->settings['currencies'] = $currencies;
	}


	/**
	 * Setup gateway list
	 *
	 * @since 2.4.0
	 */
	public function setup_gateways_list() {
		// Default, built-in gateways
		$gateways = array(
			'paypal'  => array(
				'admin_label'    => __( 'PayPal Standard', 'walkthecounty' ),
				'checkout_label' => __( 'PayPal', 'walkthecounty' ),
			),
			'manual'  => array(
				'admin_label'    => __( 'Test Donation', 'walkthecounty' ),
				'checkout_label' => __( 'Test Donation', 'walkthecounty' ),
			),
			'offline' => array(
				'admin_label'    => esc_attr__( 'Offline Donation', 'walkthecounty' ),
				'checkout_label' => esc_attr__( 'Offline Donation', 'walkthecounty' ),
			),
		);

		/**
		 * Filter the supported gateways list
		 *
		 * @since 2.4.0
		 */
		$gateways = apply_filters( 'walkthecounty_register_gateway', $gateways );

		$this->settings['gateways'] = $gateways;
	}


	/**
	 * Get option
	 *
	 * @since  2.4.0
	 * @access public
	 *
	 * @param      $option_name
	 * @param bool $default
	 *
	 * @return mixed
	 */
	public static function get_option( $option_name, $default = false ) {
		$value = $default;

		if ( in_array( $option_name, self::$all_option_ids ) ) {
			$value = ! empty( self::$instance->settings[ $option_name ] )
				? self::$instance->settings[ $option_name ]
				: $default;
		}

		return $value;
	}

	/**
	 * Get plugin settings
	 *
	 * @since  2.4.0
	 * @access public
	 */
	public static function get_settings() {

		/**
		 * Filter the plugin setting
		 */
		return (array) apply_filters( 'walkthecounty_get_settings', self::get_option( 'walkthecounty_settings', array() ) );
	}
}

WalkTheCounty_Cache_Setting::get_instance();
