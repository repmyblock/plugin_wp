<?php
/**
 * The [donation_history] Shortcode Generator class
 *
 * @package     WalkTheCounty
 * @subpackage  Admin
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WalkTheCounty_Shortcode_Donation_History extends WalkTheCounty_Shortcode_Generator {

	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->shortcode['label'] = esc_html__( 'Donation History', 'walkthecounty' );

		parent::__construct( 'donation_history' );
	}

	/**
	 * Define the shortcode attribute fields
	 *
	 * @since 2.5.0
	 * @return array
	 */
	public function define_fields() {
		return array(
			array(
				'type' => 'docs_link',
				'text' => esc_html__( 'Learn more about the Donation History Shortcode', 'walkthecounty' ),
				'link' => 'http://docs.walkthecountywp.com/shortcode-donation-history',
			),
		);
	}
}

new WalkTheCounty_Shortcode_Donation_History;
