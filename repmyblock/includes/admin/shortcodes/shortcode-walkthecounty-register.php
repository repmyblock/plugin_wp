<?php
/**
 * The [walkthecounty_register] Shortcode Generator class
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

class WalkTheCounty_Shortcode_Register extends WalkTheCounty_Shortcode_Generator {

	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->shortcode['title'] = esc_html__( 'Register', 'walkthecounty' );
		$this->shortcode['label'] = esc_html__( 'Register', 'walkthecounty' );

		parent::__construct( 'walkthecounty_register' );
	}

	/**
	 * Define the shortcode attribute fields
	 *
	 * @return array
	 */
	public function define_fields() {

		return array(
			array(
				'type' => 'container',
				'html' => sprintf( '<p class="no-margin">%s</p>', esc_html__( 'Redirect URL (optional):', 'walkthecounty' ) ),
			),
			array(
				'type'     => 'textbox',
				'name'     => 'redirect',
				'minWidth' => 320,
				'tooltip'  => esc_attr__( 'Enter an URL here to redirect to after registering.', 'walkthecounty' ),
			),
			array(
				'type' => 'docs_link',
				'text' => esc_html__( 'Learn more about the Register Shortcode', 'walkthecounty' ),
				'link' => 'http://docs.walkthecountywp.com/shortcode-walkthecounty-register',
			),
		);
	}
}

new WalkTheCounty_Shortcode_Register;
