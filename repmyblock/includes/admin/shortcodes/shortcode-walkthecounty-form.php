<?php
/**
 * The [walkthecounty_form] Shortcode Generator class
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

/**
 * Class WalkTheCounty_Shortcode_Donation_Form
 */
class WalkTheCounty_Shortcode_Donation_Form extends WalkTheCounty_Shortcode_Generator {

	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->shortcode['title'] = esc_html__( 'Donation Form', 'walkthecounty' );
		$this->shortcode['label'] = esc_html__( 'Donation Form', 'walkthecounty' );

		parent::__construct( 'walkthecounty_form' );
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
					'post_type' => 'walkthecounty_forms',
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
				'name'    => 'show_title',
				'label'   => esc_attr__( 'Show Title', 'walkthecounty' ),
				'tooltip' => esc_attr__( 'Do you want to display the form title?', 'walkthecounty' ),
				'options' => array(
					'true'  => esc_html__( 'Show', 'walkthecounty' ),
					'false' => esc_html__( 'Hide', 'walkthecounty' ),
				),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'show_goal',
				'label'   => esc_attr__( 'Show Goal', 'walkthecounty' ),
				'tooltip' => esc_attr__( 'Do you want to display the donation goal?', 'walkthecounty' ),
				'options' => array(
					'true'  => esc_html__( 'Show', 'walkthecounty' ),
					'false' => esc_html__( 'Hide', 'walkthecounty' ),
				),
			),
			array(
				'type'     => 'listbox',
				'name'     => 'show_content',
				'minWidth' => 240,
				'label'    => esc_attr__( 'Display Content', 'walkthecounty' ),
				'tooltip'  => esc_attr__( 'Do you want to display the form content?', 'walkthecounty' ),
				'options'  => array(
					'none'  => esc_html__( 'No Content', 'walkthecounty' ),
					'above' => esc_html__( 'Display content ABOVE the fields', 'walkthecounty' ),
					'below' => esc_html__( 'Display content BELOW the fields', 'walkthecounty' ),
				),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'display_style',
				'classes' => 'walkthecounty-display-style',
				'label'   => esc_attr__( 'Display Options', 'walkthecounty' ),
				'tooltip' => esc_attr__( 'How would you like to display donation information?', 'walkthecounty' ),
				'options' => array(
					'onpage' => esc_html__( 'All Fields', 'walkthecounty' ),
					'modal'  => esc_html__( 'Modal', 'walkthecounty' ),
					'reveal' => esc_html__( 'Reveal', 'walkthecounty' ),
					'button' => esc_html__( 'Button', 'walkthecounty' ),
				),
			),
			array(
				'type'    => 'textbox',
				'classes' => 'walkthecounty-hidden walkthecounty-continue-button-title',
				'name'    => 'continue_button_title',
				'label'   => esc_attr__( 'Button Text', 'walkthecounty' ),
				'tooltip' => esc_attr__( 'The button label for displaying the additional payment fields.', 'walkthecounty' ),
			),
			array(
				'type' => 'docs_link',
				'text' => esc_html__( 'Learn more about the Donation Form Shortcode', 'walkthecounty' ),
				'link' => 'http://docs.walkthecountywp.com/shortcode-walkthecounty-forms',
			),
		);
	}
}

new WalkTheCounty_Shortcode_Donation_Form();
