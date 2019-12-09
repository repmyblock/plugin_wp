<?php
/**
 * The [walkthecounty_profile_editor] Shortcode Generator class
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

class WalkTheCounty_Shortcode_Profile_Editor extends WalkTheCounty_Shortcode_Generator {

	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->shortcode['label'] = esc_html__( 'Profile Editor', 'walkthecounty' );

		parent::__construct( 'walkthecounty_profile_editor' );
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
				'text' => esc_html__( 'Learn more about the Donation Profile Editor Shortcode', 'walkthecounty' ),
				'link' => 'http://docs.walkthecountywp.com/shortcode-profile-editor',
			),
		);
	}
}

new WalkTheCounty_Shortcode_Profile_Editor;
