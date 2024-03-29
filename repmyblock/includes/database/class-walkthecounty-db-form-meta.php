<?php
/**
 * Form Meta DB class
 *
 * @package     WalkTheCounty
 * @subpackage  Classes/DB Form Meta
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WalkTheCounty_DB_Form_Meta
 *
 * This class is for interacting with the form meta database table.
 *
 * @since 2.0
 */
class WalkTheCounty_DB_Form_Meta extends WalkTheCounty_DB_Meta {
	/**
	 * Post type
	 *
	 * @since  2.0
	 * @access protected
	 * @var bool
	 */
	protected $post_type = 'walkthecounty_forms';

	/**
	 * Meta type
	 *
	 * @since  2.0
	 * @access protected
	 * @var bool
	 */
	protected $meta_type = 'form';

	/**
	 * WalkTheCounty_DB_Form_Meta constructor.
	 *
	 * @access  public
	 * @since   2.0
	 */
	public function __construct() {
		/* @var WPDB $wpdb */
		global $wpdb;

		$wpdb->formmeta    = $this->table_name = $wpdb->prefix . 'walkthecounty_formmeta';
		$this->primary_key = 'meta_id';
		$this->version     = '1.0';

		parent::__construct();
	}

	/**
	 * Get table columns and data types.
	 *
	 * @access  public
	 * @since   2.0
	 *
	 * @return  array  Columns and formats.
	 */
	public function get_columns() {
		return array(
			'meta_id'    => '%d',
			'form_id'    => '%d',
			'meta_key'   => '%s',
			'meta_value' => '%s',
		);
	}

	/**
	 * check if custom meta table enabled or not.
	 *
	 * @since  2.0
	 * @access protected
	 * @return bool
	 */
	protected function is_custom_meta_table_active() {
		return walkthecounty_has_upgrade_completed( 'v20_move_metadata_into_new_table' );
	}
}
