<?php
/**
 * The [walkthecounty_goal] Shortcode Generator class
 *
 * @package     WalkTheCounty/Admin/Shortcodes
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WalkTheCounty_Shortcode_Donation_Form_Goal
 */
class WalkTheCounty_Shortcode_Donation_Form_Goal extends WalkTheCounty_Shortcode_Generator {

	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->shortcode['title'] = esc_html__( 'Donation Form Goal', 'walkthecounty' );
		$this->shortcode['label'] = esc_html__( 'Donation Form Goal', 'walkthecounty' );

		parent::__construct( 'walkthecounty_goal' );
	}

	/**
	 * Define the shortcode attribute fields
	 *
	 * @return array
	 */
	public function define_fields() {

		$create_form_link = sprintf(
		/* translators: %s: create new form URL */
			__( '<a href="%s">Create</a> a new Donation Form.', 'walkthecounty' ),
			admin_url( 'post-new.php?post_type=walkthecounty_forms' )
		);

		return array(
			array(
				'type'        => 'post',
				'query_args'  => array(
					'post_type'  => 'walkthecounty_forms',
					'meta_key'   => '_walkthecounty_goal_option',
					'meta_value' => 'enabled',
				),
				'name'        => 'id',
				'tooltip'     => esc_attr__( 'Select a Donation Form', 'walkthecounty' ),
				'placeholder' => '- ' . esc_attr__( 'Select a Donation Form', 'walkthecounty' ) . ' -',
				'required'    => array(
					'alert' => esc_html__( 'You must first select a Form!', 'walkthecounty' ),
					'error' => sprintf( '<p class="strong">%s</p><p class="no-margin">%s</p>', esc_html__( 'No forms found.', 'walkthecounty' ), $create_form_link ),
				),
			),
			array(
				'type' => 'container',
				'html' => sprintf( '<p class="strong margin-top">%s</p>', esc_html__( 'Optional settings', 'walkthecounty' ) ),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'show_text',
				'label'   => esc_attr__( 'Show Text:', 'walkthecounty' ),
				'tooltip' => esc_attr__( 'This text displays the amount of income raised compared to the goal.', 'walkthecounty' ),
				'options' => array(
					'true'  => esc_html__( 'Show', 'walkthecounty' ),
					'false' => esc_html__( 'Hide', 'walkthecounty' ),
				),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'show_bar',
				'label'   => esc_attr__( 'Show Progress Bar:', 'walkthecounty' ),
				'tooltip' => esc_attr__( 'Do you want to display the goal\'s progress bar?', 'walkthecounty' ),
				'options' => array(
					'true'  => esc_html__( 'Show', 'walkthecounty' ),
					'false' => esc_html__( 'Hide', 'walkthecounty' ),
				),
			),
			array(
				'type' => 'docs_link',
				'text' => esc_html__( 'Learn more about the Goal Shortcode', 'walkthecounty' ),
				'link' => 'http://docs.walkthecountywp.com/shortcode-walkthecounty-goal',
			),
		);
	}
}

new WalkTheCounty_Shortcode_Donation_Form_Goal;
