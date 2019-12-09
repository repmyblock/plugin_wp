<?php
/**
 * The [walkthecounty_donation_grid] Shortcode Generator class
 *
 * @package     WalkTheCounty/Admin/Shortcodes
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WalkTheCounty_Shortcode_Donation_Form_Goal
 */
class WalkTheCounty_Shortcode_Donation_Grid extends WalkTheCounty_Shortcode_Generator {

	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->shortcode['title'] = esc_html__( 'Donation Form Grid', 'walkthecounty' );
		$this->shortcode['label'] = esc_html__( 'Donation Form Grid', 'walkthecounty' );

		parent::__construct( 'walkthecounty_form_grid' );
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
				'html' => sprintf( '<p class="strong margin-top">%s</p>', esc_html__( 'Optional settings', 'walkthecounty' ) ),
			),
			array(
				'type'        => 'textbox',
				'name'        => 'ids',
				'label'       => esc_attr__( 'Form IDs:', 'walkthecounty' ),
				'tooltip'     => esc_attr__( 'Enter a comma-separated list of form IDs. If empty, all published forms are displayed.', 'walkthecounty' ),
				'placeholder' => esc_html__( 'All Forms', 'walkthecounty' ),
			),
			array(
				'type'        => 'textbox',
				'name'        => 'exclude',
				'label'       => esc_attr__( 'Excluded Form IDs:', 'walkthecounty' ),
				'tooltip'     => esc_attr__( 'Enter a comma-separated list of form IDs to exclude those from the grid.', 'walkthecounty' ),
				'placeholder' => esc_html__( 'Excluded Forms', 'walkthecounty' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'orderby',
				'label'       => esc_attr__( 'Order By:', 'walkthecounty' ),
				'tooltip'     => esc_attr__( 'Different parameter to set the order for the forms display in the form grid.', 'walkthecounty' ),
				'options'     => array(
					'name'             => esc_html__( 'Form Name', 'walkthecounty' ),
					'amount_donated'   => esc_html__( 'Amount Donated', 'walkthecounty' ),
					'number_donations' => esc_html__( 'Number of Donations', 'walkthecounty' ),
					'menu_order'       => esc_html__( 'Menu Order', 'walkthecounty' ),
					'post__in'         => esc_html__( 'Provided Form IDs', 'walkthecounty' ),
					'closest_to_goal'  => esc_html__( 'Closest To Goal', 'walkthecounty' ),
				),
				'placeholder' => esc_html__( 'Date Created', 'walkthecounty' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'order',
				'label'       => esc_attr__( 'Order:', 'walkthecounty' ),
				'tooltip'     => esc_attr__( 'Display forms based on order.', 'walkthecounty' ),
				'options'     => array(
					'ASC' => esc_html__( 'Ascending', 'walkthecounty' ),
				),
				'placeholder' => esc_html__( 'Descending', 'walkthecounty' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'columns',
				'label'       => esc_attr__( 'Columns:', 'walkthecounty' ),
				'tooltip'     => esc_attr__( 'Sets the number of forms per row.', 'walkthecounty' ),
				'options'     => array(
					'1' => esc_html__( '1', 'walkthecounty' ),
					'2' => esc_html__( '2', 'walkthecounty' ),
					'3' => esc_html__( '3', 'walkthecounty' ),
					'4' => esc_html__( '4', 'walkthecounty' ),
				),
				'placeholder' => esc_html__( 'Best Fit', 'walkthecounty' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'show_goal',
				'label'       => esc_attr__( 'Show Goal:', 'walkthecounty' ),
				'tooltip'     => __( 'Do you want to display the goal\'s progress bar?', 'walkthecounty' ),
				'options'     => array(
					'false' => esc_html__( 'Hide', 'walkthecounty' ),
				),
				'placeholder' => esc_html__( 'Show', 'walkthecounty' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'show_excerpt',
				'label'       => esc_attr__( 'Show Excerpt:', 'walkthecounty' ),
				'tooltip'     => esc_attr__( 'Do you want to display the excerpt?', 'walkthecounty' ),
				'options'     => array(
					'false' => esc_html__( 'Hide', 'walkthecounty' ),
				),
				'placeholder' => esc_html__( 'Show', 'walkthecounty' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'show_featured_image',
				'label'       => esc_attr__( 'Show Featured Image:', 'walkthecounty' ),
				'tooltip'     => esc_attr__( 'Do you want to display the featured image?', 'walkthecounty' ),
				'options'     => array(
					'false' => esc_html__( 'Hide', 'walkthecounty' ),
				),
				'placeholder' => esc_html__( 'Show', 'walkthecounty' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'display_style',
				'label'       => esc_attr__( 'Display Style:', 'walkthecounty' ),
				'tooltip'     => esc_attr__( 'Show form as modal window or redirect to a new page?', 'walkthecounty' ),
				'options'     => array(
					'redirect' => esc_html__( 'Redirect', 'walkthecounty' ),
				),
				'placeholder' => esc_html__( 'Modal', 'walkthecounty' ),
			),
			array(
				'type'    => 'textbox',
				'name'    => 'forms_per_page',
				'label'   => esc_attr__( 'Forms Per Page:', 'walkthecounty' ),
				'tooltip' => esc_attr__( 'Sets the number of forms to display per page.', 'walkthecounty' ),
				'value'   => 12,
			),
			array(
				'type' => 'docs_link',
				'text' => esc_html__( 'Learn more about the Donation Form Grid Shortcode', 'walkthecounty' ),
				'link' => 'http://docs.walkthecountywp.com/shortcode-form-grid',
			),
		);
	}
}

new WalkTheCounty_Shortcode_Donation_Grid();
