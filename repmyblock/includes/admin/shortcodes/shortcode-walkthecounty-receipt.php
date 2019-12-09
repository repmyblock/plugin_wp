<?php
/**
 * The [walkthecounty_receipt] Shortcode Generator class
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
 * Class WalkTheCounty_Shortcode_Donation_Receipt
 */
class WalkTheCounty_Shortcode_Donation_Receipt extends WalkTheCounty_Shortcode_Generator {

	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->shortcode['title'] = esc_html__( 'Donation Receipt', 'walkthecounty' );
		$this->shortcode['label'] = esc_html__( 'Donation Receipt', 'walkthecounty' );

		parent::__construct( 'walkthecounty_receipt' );
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
				'html' => sprintf( '<p class="strong">%s</p>', esc_html__( 'Optional settings', 'walkthecounty' ) ),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'price',
				'label'   => esc_html__( 'Show Donation Amount:', 'walkthecounty' ),
				'options' => array(
					'false' => esc_html__( 'Hide', 'walkthecounty' ),
				),
				'placeholder' => esc_html__( 'Show', 'walkthecounty' ),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'donor',
				'label'   => esc_html__( 'Show Donor Name:', 'walkthecounty' ),
				'options' => array(
					'true'  => esc_html__( 'Show', 'walkthecounty' ),
					'false' => esc_html__( 'Hide', 'walkthecounty' ),
				),
				'placeholder' => esc_html__( 'Show', 'walkthecounty' ),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'date',
				'label'   => esc_html__( 'Show Date:', 'walkthecounty' ),
				'options' => array(
					'false' => esc_html__( 'Hide', 'walkthecounty' ),
				),
				'placeholder' => esc_html__( 'Show', 'walkthecounty' ),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'payment_method',
				'label'   => esc_html__( 'Show Payment Method:', 'walkthecounty' ),
				'options' => array(
					'false' => esc_html__( 'Hide', 'walkthecounty' ),
				),
				'placeholder' => esc_html__( 'Show', 'walkthecounty' ),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'payment_id',
				'label'   => esc_html__( 'Show Payment ID:', 'walkthecounty' ),
				'options' => array(
					'false' => esc_html__( 'Hide', 'walkthecounty' ),
				),
				'placeholder' => esc_html__( 'Show', 'walkthecounty' ),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'company_name',
				'label'   => esc_html__( 'Company Name:', 'walkthecounty' ),
				'options' => array(
					'true' => esc_html__( 'Show', 'walkthecounty' ),
				),
				'placeholder' => esc_html__( 'Hide', 'walkthecounty' ),
			),
			array(
				'type' => 'docs_link',
				'text' => esc_html__( 'Learn more about the Donation Receipt Shortcode', 'walkthecounty' ),
				'link' => 'http://docs.walkthecountywp.com/shortcode-donation-receipt',
			),
		);
	}
}

new WalkTheCounty_Shortcode_Donation_Receipt;
