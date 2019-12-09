<?php
/**
 * The [walkthecounty_totals] Shortcode Generator class
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
 * Class WalkTheCounty_Shortcode_Totals
 */
class WalkTheCounty_Shortcode_Totals extends WalkTheCounty_Shortcode_Generator {

	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->shortcode['title'] = __( 'WalkTheCountyWP Totals', 'walkthecounty' );
		$this->shortcode['label'] = __( 'WalkTheCountyWP Totals', 'walkthecounty' );

		parent::__construct( 'walkthecounty_totals' );
	}

	/**
	 * Define the shortcode attribute fields
	 *
	 * @since 2.1
	 * @return array
	 */
	public function define_fields() {

		$category_options = array();
		$category_lists   = array();
		$categories       = get_terms( 'walkthecounty_forms_category', apply_filters( 'walkthecounty_forms_category_dropdown', array() ) );
		if ( walkthecounty_is_setting_enabled( walkthecounty_get_option( 'categories' ) ) && ! is_wp_error( $categories ) ) {
			foreach ( $categories as $category ) {
				$category_options[ absint( $category->term_id ) ] = esc_html( $category->name );
			}

			$category_lists['type']    = 'listbox';
			$category_lists['name']    = 'cats';
			$category_lists['label']   = __( 'Select a Donation Form Category:', 'walkthecounty' );
			$category_lists['tooltip'] = __( 'Select a Donation Form Category', 'walkthecounty' );
			$category_lists['options'] = $category_options;
		}

		$tag_options = array();
		$tag_lists   = array();
		$tags        = get_terms( 'walkthecounty_forms_tag', apply_filters( 'walkthecounty_forms_tag_dropdown', array() ) );
		if ( walkthecounty_is_setting_enabled( walkthecounty_get_option( 'tags' ) ) && ! is_wp_error( $tags ) ) {
			$tags = get_terms( 'walkthecounty_forms_tag', apply_filters( 'walkthecounty_forms_tag_dropdown', array() ) );
			foreach ( $tags as $tag ) {
				$tag_options[ absint( $tag->term_id ) ] = esc_html( $tag->name );
			}

			$tag_lists['type']    = 'listbox';
			$tag_lists['name']    = 'tags';
			$tag_lists['label']   = __( 'Select a Donation Form Tag:', 'walkthecounty' );
			$tag_lists['tooltip'] = __( 'Select a Donation Form Tag', 'walkthecounty' );
			$tag_lists['options'] = $tag_options;
		}

		return array(
			array(
				'type' => 'container',
				'html' => sprintf( '<p class="walkthecounty-totals-shortcode-container-message">%s</p>',
					__( 'This shortcode shows the total amount raised towards a custom goal for one or several forms regardless of whether they have goals enabled or not.', 'walkthecounty' )
				),
			),
			array(
				'type' => 'container',
				'html' => sprintf( '<p class="strong margin-top">%s</p>', __( 'Shortcode Configuration', 'walkthecounty' ) ),
			),
			array(
				'type'    => 'textbox',
				'name'    => 'ids',
				'label'   => __( 'Donation Form IDs:', 'walkthecounty' ),
				'tooltip' => __( 'Enter the IDs separated by commas for the donation forms you would like to combine within the totals.', 'walkthecounty' ),
			),
			$category_lists,
			$tag_lists,
			array(
				'type'     => 'textbox',
				'name'     => 'total_goal',
				'label'    => __( 'Total Goal:', 'walkthecounty' ),
				'tooltip'  => __( 'Enter the total goal amount that you would like to display.', 'walkthecounty' ),
				'required' => array(
					'alert' => esc_html__( 'Please enter a valid total goal amount.', 'walkthecounty' ),
				),
			),
			array(
				'type'      => 'textbox',
				'name'      => 'message',
				'label'     => __( 'Message:', 'walkthecounty' ),
				'tooltip'   => __( 'Enter a message to display encouraging donors to support the goal.', 'walkthecounty' ),
				'value'     => apply_filters( 'walkthecounty_totals_message', __( 'Hey! We\'ve raised {total} of the {total_goal} we are trying to raise for this campaign!', 'walkthecounty' ) ),
				'multiline' => true,
				'minWidth'  => 300,
				'minHeight' => 60,
			),
			array(
				'type'    => 'textbox',
				'name'    => 'link',
				'label'   => __( 'Link:', 'walkthecounty' ),
				'tooltip' => __( 'Enter a link to the main campaign donation form.', 'walkthecounty' ),
			),
			array(
				'type'    => 'textbox',
				'name'    => 'link_text',
				'label'   => __( 'Link Text:', 'walkthecounty' ),
				'tooltip' => __( 'Enter hyperlink text for the link to the main campaign donation form.', 'walkthecounty' ),
				'value'   => __( 'Donate!', 'walkthecounty' ),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'progress_bar',
				'label'   => __( 'Show Progress Bar:', 'walkthecounty' ),
				'tooltip' => __( 'Select whether you would like to show a goal progress bar.', 'walkthecounty' ),
				'options' => array(
					'true'  => __( 'Show', 'walkthecounty' ),
					'false' => __( 'Hide', 'walkthecounty' ),
				),
				'value'   => 'true',
			),
			array(
				'type' => 'docs_link',
				'text' => esc_html__( 'Learn more about the Donation Totals Shortcode', 'walkthecounty' ),
				'link' => 'http://docs.walkthecountywp.com/shortcode-donation-totals',
			),
		);
	}
}

new WalkTheCounty_Shortcode_Totals;
