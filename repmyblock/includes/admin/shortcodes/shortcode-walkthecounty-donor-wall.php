<?php
/**
 * The [walkthecounty_donor_grid] Shortcode Generator class
 *
 * @package     WalkTheCounty
 * @subpackage  Admin
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WalkTheCounty_Shortcode_Donor_Wall
 */
class WalkTheCounty_Shortcode_Donor_Wall extends WalkTheCounty_Shortcode_Generator {

	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->shortcode['title'] = esc_html__( 'Donor Wall', 'walkthecounty' );
		$this->shortcode['label'] = esc_html__( 'Donor Wall', 'walkthecounty' );

		parent::__construct( 'walkthecounty_donor_wall' );
	}

	/**
	 * Define the shortcode attribute fields
	 *
	 * @return array
	 */
	public function define_fields() {
		return array(
			array(
				'type'        => 'post',
				'query_args'  => array(
					'post_type' => 'walkthecounty_forms',
				),
				'name'        => 'form_id',
				'label'       => esc_attr__( 'Form:', 'walkthecounty' ),
				'tooltip'     => esc_attr__( 'Filters donors by form. By default, all donations except for anonymous donations are displayed.', 'walkthecounty' ),
				'placeholder' => esc_attr__( 'All Forms', 'walkthecounty' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'orderby',
				'label'       => esc_attr__( 'Order By:', 'walkthecounty' ),
				'tooltip'     => esc_attr__( 'Different parameters to set the order in which donors appear.', 'walkthecounty' ),
				'options'     => array(
					'donation_amount' => esc_html__( 'Donation Amount', 'walkthecounty' ),
				),
				'placeholder' => esc_html__( 'Date Created', 'walkthecounty' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'order',
				'label'       => esc_attr__( 'Order:', 'walkthecounty' ),
				'tooltip'     => esc_attr__( 'Sets the order in which donors appear.', 'walkthecounty' ),
				'options'     => array(
					'ASC' => esc_html__( 'Ascending', 'walkthecounty' ),
				),
				'placeholder' => esc_html__( 'Descending', 'walkthecounty' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'only_comments',
				'label'       => esc_attr__( 'Donors:', 'walkthecounty' ),
				'tooltip'     => esc_attr__( 'Determines whether to display all donors or only donors with comments.', 'walkthecounty' ),
				'options'     => array(
					'true' => esc_html__( 'Donors with Comments', 'walkthecounty' ),
				),
				'placeholder' => esc_html__( 'All Donors', 'walkthecounty' ),
			),
			array(
				'type'        => 'textbox',
				'name'        => 'donors_per_page',
				'label'       => esc_attr__( 'Donors Per Page:', 'walkthecounty' ),
				'tooltip'     => esc_attr__( 'Sets the number of donors per page.', 'walkthecounty' ),
				'placeholder' => '12',
			),
			array(
				'type'        => 'textbox',
				'name'        => 'comment_length',
				'label'       => esc_attr__( 'Comment Length:', 'walkthecounty' ),
				'tooltip'     => esc_attr__( 'Sets the number of characters to display before the comment is truncated.', 'walkthecounty' ),
				'placeholder' => '140',
			),
			array(
				'type'        => 'textbox',
				'name'        => 'readmore_text',
				'label'       => esc_attr__( 'Read More Text:', 'walkthecounty' ),
				'tooltip'     => esc_attr__( 'Defines the text that appears if a comment is truncated.', 'walkthecounty' ),
				'placeholder' => esc_html__( 'Read more', 'walkthecounty' ),
			),
			array(
				'type'        => 'textbox',
				'name'        => 'loadmore_text',
				'label'       => esc_attr__( 'Load More Text:', 'walkthecounty' ),
				'tooltip'     => esc_attr__( 'Defines the button text used for pagination.', 'walkthecounty' ),
				'placeholder' => esc_html__( 'Load more', 'walkthecounty' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'columns',
				'label'       => esc_attr__( 'Columns:', 'walkthecounty' ),
				'tooltip'     => esc_attr__( 'Sets the number of donors per row.', 'walkthecounty' ),
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
				'name'        => 'anonymous',
				'label'       => esc_attr__( 'Anonymous:', 'walkthecounty' ),
				'tooltip'     => esc_attr__( 'Determines whether anonymous donations are included.', 'walkthecounty' ),
				'options'     => array(
					'false' => esc_html__( 'Hide', 'walkthecounty' ),
				),
				'placeholder' => esc_html__( 'Show', 'walkthecounty' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'show_avatar',
				'label'       => esc_attr__( 'Donor Avatar:', 'walkthecounty' ),
				'tooltip'     => esc_attr__( 'Determines whether the avatar is visible.', 'walkthecounty' ),
				'options'     => array(
					'false' => esc_html__( 'Hide', 'walkthecounty' ),
				),
				'placeholder' => esc_html__( 'Show', 'walkthecounty' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'show_name',
				'label'       => esc_attr__( 'Donor Name:', 'walkthecounty' ),
				'tooltip'     => esc_attr__( 'Determines whether the name is visible.', 'walkthecounty' ),
				'options'     => array(
					'false' => esc_html__( 'Hide', 'walkthecounty' ),
				),
				'placeholder' => esc_html__( 'Show', 'walkthecounty' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'show_total',
				'label'       => esc_attr__( 'Donation Total:', 'walkthecounty' ),
				'tooltip'     => esc_attr__( 'Determines whether the donation total is visible.', 'walkthecounty' ),
				'options'     => array(
					'false' => esc_html__( 'Hide', 'walkthecounty' ),
				),
				'placeholder' => esc_html__( 'Show', 'walkthecounty' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'show_time',
				'label'       => esc_attr__( 'Donation Date:', 'walkthecounty' ),
				'tooltip'     => esc_attr__( 'Determines whether the date of the donation is visible.', 'walkthecounty' ),
				'options'     => array(
					'false' => esc_html__( 'Hide', 'walkthecounty' ),
				),
				'placeholder' => esc_html__( 'Show', 'walkthecounty' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'show_comments',
				'label'       => esc_attr__( 'Donor Comment:', 'walkthecounty' ),
				'tooltip'     => esc_attr__( 'Determines whether the comment is visible.', 'walkthecounty' ),
				'options'     => array(
					'false' => esc_html__( 'Hide', 'walkthecounty' ),
				),
				'placeholder' => esc_html__( 'Show', 'walkthecounty' ),
			),
			array(
				'type' => 'docs_link',
				'text' => esc_html__( 'Learn more about the Donor Wall Shortcode', 'walkthecounty' ),
				'link' => 'http://docs.walkthecountywp.com/shortcode-donor-wall',
			),
		);
	}
}

new WalkTheCounty_Shortcode_Donor_Wall();
