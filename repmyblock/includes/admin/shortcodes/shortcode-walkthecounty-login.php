<?php
/**
 * The [walkthecounty_login] Shortcode Generator class
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

class WalkTheCounty_Shortcode_Login extends WalkTheCounty_Shortcode_Generator {

	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->shortcode['title'] = esc_html__( 'Login', 'walkthecounty' );
		$this->shortcode['label'] = esc_html__( 'Login', 'walkthecounty' );

		parent::__construct( 'walkthecounty_login' );
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
				'html' => sprintf( '<p class="no-margin">%s</p>', esc_html__( 'Login Redirect URL (optional):', 'walkthecounty' ) ),
			),
			array(
				'type'     => 'textbox',
				'name'     => 'login-redirect',
				'minWidth' => 320,
				'tooltip'  => esc_attr__( 'Enter an URL here to redirect to after login.', 'walkthecounty' ),
			),
			array(
				'type' => 'container',
				'html' => sprintf( '<p class="no-margin">%s</p>', esc_html__( 'Logout Redirect URL (optional):', 'walkthecounty' ) ),
			),
			array(
				'type'     => 'textbox',
				'name'     => 'logout-redirect',
				'minWidth' => 320,
				'tooltip'  => esc_attr__( 'Enter an URL here to redirect to after logout.', 'walkthecounty' ),
			),
			array(
				'type' => 'docs_link',
				'text' => esc_html__( 'Learn more about the Login Shortcode', 'walkthecounty' ),
				'link' => 'http://docs.walkthecountywp.com/shortcode-walkthecounty-login',
			),
		);
	}
}

new WalkTheCounty_Shortcode_Login;
