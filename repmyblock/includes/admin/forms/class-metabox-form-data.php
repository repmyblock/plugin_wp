<?php
/**
 * Donation Form Data
 *
 * Displays the form data box, tabbed, with several panels.
 *
 * @package     WalkTheCounty
 * @subpackage  Classes/WalkTheCounty_MetaBox_Form_Data
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

/**
 * WalkTheCounty_Meta_Box_Form_Data Class.
 */
class WalkTheCounty_MetaBox_Form_Data {

	/**
	 * Meta box settings.
	 *
	 * @since 1.8
	 * @var   array
	 */
	private $settings = array();

	/**
	 * Metabox ID.
	 *
	 * @since 1.8
	 * @var   string
	 */
	private $metabox_id;

	/**
	 * Metabox Label.
	 *
	 * @since 1.8
	 * @var   string
	 */
	private $metabox_label;


	/**
	 * WalkTheCounty_MetaBox_Form_Data constructor.
	 */
	function __construct() {
		$this->metabox_id    = 'walkthecounty-metabox-form-data';
		$this->metabox_label = __( 'Voters Questions', 'walkthecounty' );

		// Setup.
		add_action( 'admin_init', array( $this, 'setup' ) );

		// Add metabox.
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ), 10 );

		// Save form meta.
		add_action( 'save_post_walkthecounty_forms', array( $this, 'save' ), 10, 2 );

		// cmb2 old setting loaders.
		// add_filter( 'walkthecounty_metabox_form_data_settings', array( $this, 'cmb2_metabox_settings' ) );
		// Add offline donations options.
		add_filter( 'walkthecounty_metabox_form_data_settings', array( $this, 'add_offline_donations_setting_tab' ), 0, 1 );

		// Maintain active tab query parameter after save.
		add_filter( 'redirect_post_location', array( $this, 'maintain_active_tab' ), 10, 2 );
	}

	/**
	 * Setup metabox related data.
	 *
	 * @since 1.8
	 *
	 * @return void
	 */
	function setup() {
		$this->settings = $this->get_settings();
	}


	/**
	 * Get metabox settings
	 *
	 * @since 1.8
	 *
	 * @return array
	 */
	function get_settings() {
		$post_id           = walkthecounty_get_admin_post_id();
		$price_placeholder = walkthecounty_format_decimal( '1.00', false, false );

		// Start with an underscore to hide fields from custom fields list
		$prefix = '_walkthecounty_';

		$settings = array(
			/**
			 * Repeatable Field Groups
			 */
			'form_field_options'    => apply_filters( 'walkthecounty_forms_field_options', array(
				'id'        => 'form_field_options',
				'title'     => __( 'Donation Options', 'walkthecounty' ),
				'icon-html' => '<span class="walkthecounty-icon walkthecounty-icon-heart"></span>',
				'fields'    => apply_filters( 'walkthecounty_forms_donation_form_metabox_fields', array(
					// Donation Option.
					array(
						'name'        => __( 'Donation Option', 'walkthecounty' ),
						'description' => __( 'Do you want this form to have one set donation price or multiple levels (for example, $10, $20, $50)?', 'walkthecounty' ),
						'id'          => $prefix . 'price_option',
						'type'        => 'radio_inline',
						'default'     => 'multi',
						'options'     => apply_filters( 'walkthecounty_forms_price_options', array(
							'multi' => __( 'Multi-level Donation', 'walkthecounty' ),
							'set'   => __( 'Set Donation', 'walkthecounty' ),
						) ),
					),
					array(
						'name'        => __( 'Set Donation', 'walkthecounty' ),
						'description' => __( 'This is the set donation amount for this form. If you have a "Custom Amount Minimum" set, make sure it is less than this amount.', 'walkthecounty' ),
						'id'          => $prefix . 'set_price',
						'type'        => 'text_small',
						'data_type'   => 'price',
						'attributes'  => array(
							'placeholder' => $price_placeholder,
							'class'       => 'walkthecounty-money-field',
						),
					),
					// Display Style.
					array(
						'name'          => __( 'Display Style', 'walkthecounty' ),
						'description'   => __( 'Set how the donations levels will display on the form.', 'walkthecounty' ),
						'id'            => $prefix . 'display_style',
						'type'          => 'radio_inline',
						'default'       => 'buttons',
						'options'       => array(
							'buttons'  => __( 'Buttons', 'walkthecounty' ),
							'radios'   => __( 'Radios', 'walkthecounty' ),
							'dropdown' => __( 'Dropdown', 'walkthecounty' ),
						),
						'wrapper_class' => 'walkthecounty-hidden',
					),
					// Custom Amount.
					array(
						'name'        => __( 'Custom Amount', 'walkthecounty' ),
						'description' => __( 'Do you want the user to be able to input their own donation amount?', 'walkthecounty' ),
						'id'          => $prefix . 'custom_amount',
						'type'        => 'radio_inline',
						'default'     => 'disabled',
						'options'     => array(
							'enabled'  => __( 'Enabled', 'walkthecounty' ),
							'disabled' => __( 'Disabled', 'walkthecounty' ),
						),
					),
					array(
						'name'          => __( 'Donation Limit', 'walkthecounty' ),
						'description'   => __( 'Set the minimum and maximum amount for all gateways.', 'walkthecounty' ),
						'id'            => $prefix . 'custom_amount_range',
						'type'          => 'donation_limit',
						'wrapper_class' => 'walkthecounty-hidden',
						'data_type'     => 'price',
						'attributes'    => array(
							'placeholder' => $price_placeholder,
							'class'       => 'walkthecounty-money-field',
						),
						'options'       => array(
							'display_label' => __( 'Donation Limits: ', 'walkthecounty' ),
							'minimum'       => walkthecounty_format_decimal( '1.00', false, false ),
							'maximum'       => walkthecounty_format_decimal( '999999.99', false, false ),
						),
					),
					array(
						'name'          => __( 'Custom Amount Text', 'walkthecounty' ),
						'description'   => __( 'This text appears as a label below the custom amount field for set donation forms. For multi-level forms the text will appear as it\'s own level (ie button, radio, or select option).', 'walkthecounty' ),
						'id'            => $prefix . 'custom_amount_text',
						'type'          => 'text_medium',
						'attributes'    => array(
							'rows'        => 3,
							'placeholder' => __( 'WalkTheCounty a Custom Amount', 'walkthecounty' ),
						),
						'wrapper_class' => 'walkthecounty-hidden',
					),
					// Donation Levels.
					array(
						'id'            => $prefix . 'donation_levels',
						'type'          => 'group',
						'options'       => array(
							'add_button'    => __( 'Add Level', 'walkthecounty' ),
							'header_title'  => __( 'Donation Level', 'walkthecounty' ),
							'remove_button' => '<span class="dashicons dashicons-no"></span>',
						),
						'wrapper_class' => 'walkthecounty-hidden',
						// Fields array works the same, except id's only need to be unique for this group.
						// Prefix is not needed.
						'fields'        => apply_filters( 'walkthecounty_donation_levels_table_row', array(
							array(
								'name' => __( 'ID', 'walkthecounty' ),
								'id'   => $prefix . 'id',
								'type' => 'levels_id',
							),
							array(
								'name'       => __( 'Amount', 'walkthecounty' ),
								'id'         => $prefix . 'amount',
								'type'       => 'text_small',
								'data_type'  => 'price',
								'attributes' => array(
									'placeholder' => $price_placeholder,
									'class'       => 'walkthecounty-money-field',
								),
							),
							array(
								'name'       => __( 'Text', 'walkthecounty' ),
								'id'         => $prefix . 'text',
								'type'       => 'text',
								'attributes' => array(
									'placeholder' => __( 'Donation Level', 'walkthecounty' ),
									'class'       => 'walkthecounty-multilevel-text-field',
								),
							),
							array(
								'name' => __( 'Default', 'walkthecounty' ),
								'id'   => $prefix . 'default',
								'type' => 'walkthecounty_default_radio_inline',
							),
						) ),
					),
					array(
						'name'  => 'donation_options_docs',
						'type'  => 'docs_link',
						'url'   => 'http://docs.walkthecountywp.com/form-donation-options',
						'title' => __( 'Donation Options', 'walkthecounty' ),
					),
				),
					$post_id
				),
			) ),

			/**
			 * Display Options
			 */
			'form_display_options'  => apply_filters( 'walkthecounty_form_display_options', array(
					'id'        => 'form_display_options',
					'title'     => __( 'Form Display', 'walkthecounty' ),
					'icon-html' => '<span class="walkthecounty-icon walkthecounty-icon-display"></span>',
					'fields'    => apply_filters( 'walkthecounty_forms_display_options_metabox_fields', array(
						array(
							'name'    => __( 'Display Options', 'walkthecounty' ),
							'desc'    => sprintf( __( 'How would you like to display donation information for this form?', 'walkthecounty' ), '#' ),
							'id'      => $prefix . 'payment_display',
							'type'    => 'radio_inline',
							'options' => array(
								'onpage' => __( 'All Fields', 'walkthecounty' ),
								'modal'  => __( 'Modal', 'walkthecounty' ),
								'reveal' => __( 'Reveal', 'walkthecounty' ),
								'button' => __( 'Button', 'walkthecounty' ),
							),
							'default' => 'onpage',
						),
						array(
							'id'            => $prefix . 'reveal_label',
							'name'          => __( 'Continue Button', 'walkthecounty' ),
							'desc'          => __( 'The button label for displaying the additional payment fields.', 'walkthecounty' ),
							'type'          => 'text_small',
							'attributes'    => array(
								'placeholder' => __( 'Donate Now', 'walkthecounty' ),
							),
							'wrapper_class' => 'walkthecounty-hidden',
						),
						array(
							'id'         => $prefix . 'checkout_label',
							'name'       => __( 'Submit Button', 'walkthecounty' ),
							'desc'       => __( 'The button label for completing a donation.', 'walkthecounty' ),
							'type'       => 'text_small',
							'attributes' => array(
								'placeholder' => __( 'Donate Now', 'walkthecounty' ),
							),
						),
						array(
							'name' => __( 'Default Gateway', 'walkthecounty' ),
							'desc' => __( 'By default, the gateway for this form will inherit the global default gateway (set under WalkTheCountyWP > Settings > Payment Gateways). This option allows you to customize the default gateway for this form only.', 'walkthecounty' ),
							'id'   => $prefix . 'default_gateway',
							'type' => 'default_gateway',
						),
						array(
							'name'    => __( 'Name Title Prefix', 'walkthecounty' ),
							'desc'    => __( 'Do you want to add a name title prefix dropdown field before the donor\'s first name field? This will display a dropdown with options such as Mrs, Miss, Ms, Sir, and Dr for donor to choose from.', 'walkthecounty' ),
							'id'      => $prefix . 'name_title_prefix',
							'type'    => 'radio_inline',
							'options' => array(
								'global' => __( 'Global Option', 'walkthecounty' ),
								'required' => __( 'Required', 'walkthecounty' ),
								'optional' => __( 'Optional', 'walkthecounty' ),
								'disabled' => __( 'Disabled', 'walkthecounty' ),
							),
							'default' => 'global',
						),
						array(
							'name'          => __( 'Title Prefixes', 'walkthecounty' ),
							'desc'          => __( 'Add or remove salutations from the dropdown using the field above.', 'walkthecounty' ),
							'id'            => $prefix . 'title_prefixes',
							'type'          => 'chosen',
							'data_type'     => 'multiselect',
							'style'         => 'width: 100%',
							'wrapper_class' => 'walkthecounty-hidden walkthecounty-title-prefixes-wrap',
							'options'       => walkthecounty_get_default_title_prefixes(),
						),
						array(
							'name'    => __( 'Company Donations', 'walkthecounty' ),
							'desc'    => __( 'Do you want a Company field to appear after First Name and Last Name?', 'walkthecounty' ),
							'id'      => $prefix . 'company_field',
							'type'    => 'radio_inline',
							'default' => 'global',
							'options' => array(
								'global'   => __( 'Global Option', 'walkthecounty' ),
								'required' => __( 'Required', 'walkthecounty' ),
								'optional' => __( 'Optional', 'walkthecounty' ),
								'disabled' => __( 'Disabled', 'walkthecounty' ),

							),
						),
						array(
							'name'    => __( 'Anonymous Donations', 'walkthecounty' ),
							'desc'    => __( 'Do you want to provide donors the ability mark themselves anonymous while giving. This will prevent their information from appearing publicly on your website but you will still receive their information for your records in the admin panel.', 'walkthecounty' ),
							'id'      => "{$prefix}anonymous_donation",
							'type'    => 'radio_inline',
							'default' => 'global',
							'options' => array(
								'global'   => __( 'Global Option', 'walkthecounty' ),
								'enabled'  => __( 'Enabled', 'walkthecounty' ),
								'disabled' => __( 'Disabled', 'walkthecounty' ),
							),
						),
						array(
							'name'    => __( 'Donor Comments', 'walkthecounty' ),
							'desc'    => __( 'Do you want to provide donors the ability to add a comment to their donation? The comment will display publicly on the donor wall if they do not select to walkthecounty anonymously.', 'walkthecounty' ),
							'id'      => "{$prefix}donor_comment",
							'type'    => 'radio_inline',
							'default' => 'global',
							'options' => array(
								'global'   => __( 'Global Option', 'walkthecounty' ),
								'enabled'  => __( 'Enabled', 'walkthecounty' ),
								'disabled' => __( 'Disabled', 'walkthecounty' ),
							),
						),
						array(
							'name'    => __( 'Guest Donations', 'walkthecounty' ),
							'desc'    => __( 'Do you want to allow non-logged-in users to make donations?', 'walkthecounty' ),
							'id'      => $prefix . 'logged_in_only',
							'type'    => 'radio_inline',
							'default' => 'enabled',
							'options' => array(
								'enabled'  => __( 'Enabled', 'walkthecounty' ),
								'disabled' => __( 'Disabled', 'walkthecounty' ),
							),
						),
						array(
							'name'    => __( 'Registration', 'walkthecounty' ),
							'desc'    => __( 'Display the registration and login forms in the payment section for non-logged-in users.', 'walkthecounty' ),
							'id'      => $prefix . 'show_register_form',
							'type'    => 'radio',
							'options' => array(
								'none'         => __( 'None', 'walkthecounty' ),
								'registration' => __( 'Registration', 'walkthecounty' ),
								'login'        => __( 'Login', 'walkthecounty' ),
								'both'         => __( 'Registration + Login', 'walkthecounty' ),
							),
							'default' => 'none',
						),
						array(
							'name'    => __( 'Floating Labels', 'walkthecounty' ),
							/* translators: %s: forms http://docs.walkthecountywp.com/form-floating-labels */
							'desc'    => sprintf( __( 'Select the <a href="%s" target="_blank">floating labels</a> setting for this WalkTheCountyWP form. Be aware that if you have the "Disable CSS" option enabled, you will need to style the floating labels yourself.', 'walkthecounty' ), esc_url( 'http://docs.walkthecountywp.com/form-floating-labels' ) ),
							'id'      => $prefix . 'form_floating_labels',
							'type'    => 'radio_inline',
							'options' => array(
								'global'   => __( 'Global Option', 'walkthecounty' ),
								'enabled'  => __( 'Enabled', 'walkthecounty' ),
								'disabled' => __( 'Disabled', 'walkthecounty' ),
							),
							'default' => 'global',
						),
						array(
							'name'  => 'form_display_docs',
							'type'  => 'docs_link',
							'url'   => 'http://docs.walkthecountywp.com/form-display-options',
							'title' => __( 'Form Display', 'walkthecounty' ),
						),
					),
						$post_id
					),
				)
			),

			/**
			 * Donation Goals
			 */
			'donation_goal_options' => apply_filters( 'walkthecounty_donation_goal_options', array(
				'id'        => 'donation_goal_options',
				'title'     => __( 'Donation Goal', 'walkthecounty' ),
				'icon-html' => '<span class="walkthecounty-icon walkthecounty-icon-target"></span>',
				'fields'    => apply_filters( 'walkthecounty_forms_donation_goal_metabox_fields', array(
					// Goals
					array(
						'name'        => __( 'Donation Goal', 'walkthecounty' ),
						'description' => __( 'Do you want to set a donation goal for this form?', 'walkthecounty' ),
						'id'          => $prefix . 'goal_option',
						'type'        => 'radio_inline',
						'default'     => 'disabled',
						'options'     => array(
							'enabled'  => __( 'Enabled', 'walkthecounty' ),
							'disabled' => __( 'Disabled', 'walkthecounty' ),
						),
					),

					array(
						'name'        => __( 'Goal Format', 'walkthecounty' ),
						'description' => __( 'Do you want to display the total amount raised based on your monetary goal or a percentage? For instance, "$500 of $1,000 raised" or "50% funded" or "1 of 5 donations". You can also display a donor-based goal, such as "100 of 1,000 donors have walkthecountyn".', 'walkthecounty' ),
						'id'          => $prefix . 'goal_format',
						'type'        => 'donation_form_goal',
						'default'     => 'amount',
						'options'     => array(
							'amount'     => __( 'Amount Raised', 'walkthecounty' ),
							'percentage' => __( 'Percentage Raised', 'walkthecounty' ),
							'donation'   => __( 'Number of Donations', 'walkthecounty' ),
							'donors'     => __( 'Number of Donors', 'walkthecounty' ),
						),
					),

					array(
						'name'          => __( 'Goal Amount', 'walkthecounty' ),
						'description'   => __( 'This is the monetary goal amount you want to reach for this form.', 'walkthecounty' ),
						'id'            => $prefix . 'set_goal',
						'type'          => 'text_small',
						'data_type'     => 'price',
						'attributes'    => array(
							'placeholder' => $price_placeholder,
							'class'       => 'walkthecounty-money-field',
						),
						'wrapper_class' => 'walkthecounty-hidden',
					),
					array(
						'id'         => $prefix . 'number_of_donation_goal',
						'name'       => __( 'Donation Goal', 'walkthecounty' ),
						'desc'       => __( 'Set the total number of donations as a goal.', 'walkthecounty' ),
						'type'       => 'number',
						'default'    => 1,
						'attributes' => array(
							'placeholder' => 1,
						),
					),
					array(
						'id'         => $prefix . 'number_of_donor_goal',
						'name'       => __( 'Donor Goal', 'walkthecounty' ),
						'desc'       => __( 'Set the total number of donors as a goal.', 'walkthecounty' ),
						'type'       => 'number',
						'default'    => 1,
						'attributes' => array(
							'placeholder' => 1,
						),
					),
					array(
						'name'          => __( 'Progress Bar Color', 'walkthecounty' ),
						'desc'          => __( 'Customize the color of the goal progress bar.', 'walkthecounty' ),
						'id'            => $prefix . 'goal_color',
						'type'          => 'colorpicker',
						'default'       => '#2bc253',
						'wrapper_class' => 'walkthecounty-hidden',
					),

					array(
						'name'          => __( 'Close Form', 'walkthecounty' ),
						'desc'          => __( 'Do you want to close the donation forms and stop accepting donations once this goal has been met?', 'walkthecounty' ),
						'id'            => $prefix . 'close_form_when_goal_achieved',
						'type'          => 'radio_inline',
						'default'       => 'disabled',
						'options'       => array(
							'enabled'  => __( 'Enabled', 'walkthecounty' ),
							'disabled' => __( 'Disabled', 'walkthecounty' ),
						),
						'wrapper_class' => 'walkthecounty-hidden',
					),
					array(
						'name'          => __( 'Goal Achieved Message', 'walkthecounty' ),
						'desc'          => __( 'Do you want to display a custom message when the goal is closed?', 'walkthecounty' ),
						'id'            => $prefix . 'form_goal_achieved_message',
						'type'          => 'wysiwyg',
						'default'       => __( 'Thank you to all our donors, we have met our fundraising goal.', 'walkthecounty' ),
						'wrapper_class' => 'walkthecounty-hidden',
					),
					array(
						'name'  => 'donation_goal_docs',
						'type'  => 'docs_link',
						'url'   => 'http://docs.walkthecountywp.com/form-donation-goal',
						'title' => __( 'Donation Goal', 'walkthecounty' ),
					),
				),
					$post_id
				),
			) ),

			/**
			 * Content Field
			 */
			'form_content_options'  => apply_filters( 'walkthecounty_forms_content_options', array(
				'id'        => 'form_content_options',
				'title'     => __( 'Form Content', 'walkthecounty' ),
				'icon-html' => '<span class="walkthecounty-icon walkthecounty-icon-edit"></span>',
				'fields'    => apply_filters( 'walkthecounty_forms_content_options_metabox_fields', array(

					// Donation content.
					array(
						'name'        => __( 'Display Content', 'walkthecounty' ),
						'description' => __( 'Do you want to add custom content to this form?', 'walkthecounty' ),
						'id'          => $prefix . 'display_content',
						'type'        => 'radio_inline',
						'options'     => array(
							'enabled'  => __( 'Enabled', 'walkthecounty' ),
							'disabled' => __( 'Disabled', 'walkthecounty' ),
						),
						'default'     => 'disabled',
					),

					// Content placement.
					array(
						'name'          => __( 'Content Placement', 'walkthecounty' ),
						'description'   => __( 'This option controls where the content appears within the donation form.', 'walkthecounty' ),
						'id'            => $prefix . 'content_placement',
						'type'          => 'radio_inline',
						'options'       => apply_filters( 'walkthecounty_forms_content_options_select', array(
								'walkthecounty_pre_form'  => __( 'Above fields', 'walkthecounty' ),
								'walkthecounty_post_form' => __( 'Below fields', 'walkthecounty' ),
							)
						),
						'default'       => 'walkthecounty_pre_form',
						'wrapper_class' => 'walkthecounty-hidden',
					),
					array(
						'name'          => __( 'Content', 'walkthecounty' ),
						'description'   => __( 'This content will display on the single walkthecounty form page.', 'walkthecounty' ),
						'id'            => $prefix . 'form_content',
						'type'          => 'wysiwyg',
						'wrapper_class' => 'walkthecounty-hidden',
					),
					array(
						'name'  => 'form_content_docs',
						'type'  => 'docs_link',
						'url'   => 'http://docs.walkthecountywp.com/form-content',
						'title' => __( 'Form Content', 'walkthecounty' ),
					),
				),
					$post_id
				),
			) ),

			/**
			 * Terms & Conditions
			 */
			'form_terms_options'    => apply_filters( 'walkthecounty_forms_terms_options', array(
				'id'        => 'form_terms_options',
				'title'     => __( 'Terms & Conditions', 'walkthecounty' ),
				'icon-html' => '<span class="walkthecounty-icon walkthecounty-icon-checklist"></span>',
				'fields'    => apply_filters( 'walkthecounty_forms_terms_options_metabox_fields', array(
					// Donation Option
					array(
						'name'        => __( 'Terms and Conditions', 'walkthecounty' ),
						'description' => __( 'Do you want to require the donor to accept terms prior to being able to complete their donation?', 'walkthecounty' ),
						'id'          => $prefix . 'terms_option',
						'type'        => 'radio_inline',
						'options'     => apply_filters( 'walkthecounty_forms_content_options_select', array(
								'global'   => __( 'Global Option', 'walkthecounty' ),
								'enabled'  => __( 'Customize', 'walkthecounty' ),
								'disabled' => __( 'Disable', 'walkthecounty' ),
							)
						),
						'default'     => 'global',
					),
					array(
						'id'            => $prefix . 'agree_label',
						'name'          => __( 'Agreement Label', 'walkthecounty' ),
						'desc'          => __( 'The label shown next to the agree to terms check box. Add your own to customize or leave blank to use the default text placeholder.', 'walkthecounty' ),
						'type'          => 'textarea',
						'attributes'    => array(
							'placeholder' => __( 'Agree to Terms?', 'walkthecounty' ),
							'rows'        => 1
						),
						'wrapper_class' => 'walkthecounty-hidden',
					),
					array(
						'id'            => $prefix . 'agree_text',
						'name'          => __( 'Agreement Text', 'walkthecounty' ),
						'desc'          => __( 'This is the actual text which the user will have to agree to in order to make a donation.', 'walkthecounty' ),
						'default'       => walkthecounty_get_option( 'agreement_text' ),
						'type'          => 'wysiwyg',
						'wrapper_class' => 'walkthecounty-hidden',
					),
					array(
						'name'  => 'terms_docs',
						'type'  => 'docs_link',
						'url'   => 'http://docs.walkthecountywp.com/form-terms',
						'title' => __( 'Terms and Conditions', 'walkthecounty' ),
					),
				),
					$post_id
				),
			) ),
		);

		/**
		 * Filter the metabox tabbed panel settings.
		 */
		$settings = apply_filters( 'walkthecounty_metabox_form_data_settings', $settings, $post_id );

		// Output.
		return $settings;
	}

	/**
	 * Add metabox.
	 *
	 * @since 1.8
	 *
	 * @return void
	 */
	public function add_meta_box() {
		add_meta_box(
			$this->get_metabox_ID(),
			$this->get_metabox_label(),
			array( $this, 'output' ),
			array( 'walkthecounty_forms' ),
			'normal',
			'high'
		);

		// Show Goal Metabox only if goal is enabled.
		if ( walkthecounty_is_setting_enabled( walkthecounty_get_meta( walkthecounty_get_admin_post_id(), '_walkthecounty_goal_option', true ) ) ) {
			add_meta_box(
				'walkthecounty-form-goal-stats',
				__( 'Goal Statistics', 'walkthecounty' ),
				array( $this, 'output_goal' ),
				array( 'walkthecounty_forms' ),
				'side',
				'low'
			);
		}

	}


	/**
	 * Enqueue scripts.
	 *
	 * @since 1.8
	 *
	 * @return void
	 */
	function enqueue_script() {
		global $post;

		if ( is_object( $post ) && 'walkthecounty_forms' === $post->post_type ) {

		}
	}

	/**
	 * Get metabox id.
	 *
	 * @since 1.8
	 *
	 * @return string
	 */
	function get_metabox_ID() {
		return $this->metabox_id;
	}

	/**
	 * Get metabox label.
	 *
	 * @since 1.8
	 *
	 * @return string
	 */
	function get_metabox_label() {
		return $this->metabox_label;
	}


	/**
	 * Get metabox tabs.
	 *
	 * @since 1.8
	 *
	 * @return array
	 */
	public function get_tabs() {
		$tabs = array();

		if ( ! empty( $this->settings ) ) {
			foreach ( $this->settings as $setting ) {
				if ( ! isset( $setting['id'] ) || ! isset( $setting['title'] ) ) {
					continue;
				}
				$tab = array(
					'id'        => $setting['id'],
					'label'     => $setting['title'],
					'icon-html' => ( ! empty( $setting['icon-html'] ) ? $setting['icon-html'] : '' ),
				);

				if ( $this->has_sub_tab( $setting ) ) {
					if ( empty( $setting['sub-fields'] ) ) {
						$tab = array();
					} else {
						foreach ( $setting['sub-fields'] as $sub_fields ) {
							$tab['sub-fields'][] = array(
								'id'        => $sub_fields['id'],
								'label'     => $sub_fields['title'],
								'icon-html' => ( ! empty( $sub_fields['icon-html'] ) ? $sub_fields['icon-html'] : '' ),
							);
						}
					}
				}

				if ( ! empty( $tab ) ) {
					$tabs[] = $tab;
				}
			}
		}

		return $tabs;
	}

	/**
	 * Output metabox settings.
	 *
	 * @since 1.8
	 *
	 * @return void
	 */
	public function output() {
		// Bailout.
		if ( $form_data_tabs = $this->get_tabs() ) :
			$active_tab = ! empty( $_GET['walkthecounty_tab'] ) ? walkthecounty_clean( $_GET['walkthecounty_tab'] ) : 'form_field_options';
			wp_nonce_field( 'walkthecounty_save_form_meta', 'walkthecounty_form_meta_nonce' );
			?>
			<input id="walkthecounty_form_active_tab" type="hidden" name="walkthecounty_form_active_tab">
			<div class="walkthecounty-metabox-panel-wrap">
				<ul class="walkthecounty-form-data-tabs walkthecounty-metabox-tabs">
					<?php foreach ( $form_data_tabs as $index => $form_data_tab ) : ?>
						<?php
						// Determine if current tab is active.
						$is_active = $active_tab === $form_data_tab['id'] ? true : false;
						?>
						<li class="<?php echo "{$form_data_tab['id']}_tab" . ( $is_active ? ' active' : '' ) . ( $this->has_sub_tab( $form_data_tab ) ? ' has-sub-fields' : '' ); ?>">
							<a href="#<?php echo $form_data_tab['id']; ?>"
							   data-tab-id="<?php echo $form_data_tab['id']; ?>">
								<?php if ( ! empty( $form_data_tab['icon-html'] ) ) : ?>
									<?php echo $form_data_tab['icon-html']; ?>
								<?php else : ?>
									<span class="walkthecounty-icon walkthecounty-icon-default"></span>
								<?php endif; ?>
								<span class="walkthecounty-label"><?php echo $form_data_tab['label']; ?></span>
							</a>
							<?php if ( $this->has_sub_tab( $form_data_tab ) ) : ?>
								<ul class="walkthecounty-metabox-sub-tabs walkthecounty-hidden">
									<?php foreach ( $form_data_tab['sub-fields'] as $sub_tab ) : ?>
										<li class="<?php echo "{$sub_tab['id']}_tab"; ?>">
											<a href="#<?php echo $sub_tab['id']; ?>"
											   data-tab-id="<?php echo $sub_tab['id']; ?>">
												<?php if ( ! empty( $sub_tab['icon-html'] ) ) : ?>
													<?php echo $sub_tab['icon-html']; ?>
												<?php else : ?>
													<span class="walkthecounty-icon walkthecounty-icon-default"></span>
												<?php endif; ?>
												<span class="walkthecounty-label"><?php echo $sub_tab['label']; ?></span>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>

				<?php foreach ( $this->settings as $setting ) : ?>
					<?php do_action( "walkthecounty_before_{$setting['id']}_settings" ); ?>
					<?php
					// Determine if current panel is active.
					$is_active = $active_tab === $setting['id'] ? true : false;
					?>
					<div id="<?php echo $setting['id']; ?>"
						 class="panel walkthecounty_options_panel<?php echo( $is_active ? ' active' : '' ); ?>">
						<?php if ( ! empty( $setting['fields'] ) ) : ?>
							<?php foreach ( $setting['fields'] as $field ) : ?>
								<?php walkthecounty_render_field( $field ); ?>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
					<?php do_action( "walkthecounty_after_{$setting['id']}_settings" ); ?>


					<?php if ( $this->has_sub_tab( $setting ) ) : ?>
						<?php if ( ! empty( $setting['sub-fields'] ) ) : ?>
							<?php foreach ( $setting['sub-fields'] as $index => $sub_fields ) : ?>
								<div id="<?php echo $sub_fields['id']; ?>" class="panel walkthecounty_options_panel walkthecounty-hidden">
									<?php if ( ! empty( $sub_fields['fields'] ) ) : ?>
										<?php foreach ( $sub_fields['fields'] as $sub_field ) : ?>
											<?php walkthecounty_render_field( $sub_field ); ?>
										<?php endforeach; ?>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		<?php
		endif; // End if().
	}

	/**
	 * Output Goal meta-box settings.
	 *
	 * @param object $post Post Object.
	 *
	 * @access public
	 * @since  2.1.0
	 *
	 * @return void
	 */
	public function output_goal( $post ) {

		echo walkthecounty_admin_form_goal_stats( $post->ID );

	}

	/**
	 * Check if setting field has sub tabs/fields
	 *
	 * @param array $field_setting Field Settings.
	 *
	 * @since 1.8
	 *
	 * @return bool
	 */
	private function has_sub_tab( $field_setting ) {
		$has_sub_tab = false;
		if ( array_key_exists( 'sub-fields', $field_setting ) ) {
			$has_sub_tab = true;
		}

		return $has_sub_tab;
	}

	/**
	 * CMB2 settings loader.
	 *
	 * @since 1.8
	 *
	 * @return array
	 */
	function cmb2_metabox_settings() {
		$all_cmb2_settings   = apply_filters( 'cmb2_meta_boxes', array() );
		$walkthecounty_forms_settings = $all_cmb2_settings;

		// Filter settings: Use only walkthecounty forms related settings.
		foreach ( $all_cmb2_settings as $index => $setting ) {
			if ( ! in_array( 'walkthecounty_forms', $setting['object_types'] ) ) {
				unset( $walkthecounty_forms_settings[ $index ] );
			}
		}

		return $walkthecounty_forms_settings;

	}

	/**
	 * Check if we're saving, the trigger an action based on the post type.
	 *
	 * @param int        $post_id Post ID.
	 * @param int|object $post    Post Object.
	 *
	 * @since 1.8
	 *
	 * @return void
	 */
	public function save( $post_id, $post ) {

		// $post_id and $post are required.
		if ( empty( $post_id ) || empty( $post ) ) {
			return;
		}

		// Don't save meta boxes for revisions or autosaves.
		if ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}

		// Check the nonce.
		if ( empty( $_POST['walkthecounty_form_meta_nonce'] ) || ! wp_verify_nonce( $_POST['walkthecounty_form_meta_nonce'], 'walkthecounty_save_form_meta' ) ) {
			return;
		}

		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events.
		if ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_id ) {
			return;
		}

		// Check user has permission to edit.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Fire action before saving form meta.
		do_action( 'walkthecounty_pre_process_walkthecounty_forms_meta', $post_id, $post );

		/**
		 * Filter the meta key to save.
		 * Third party addon developer can remove there meta keys from this array to handle saving data on there own.
		 */
		$form_meta_keys = apply_filters( 'walkthecounty_process_form_meta_keys', $this->get_meta_keys_from_settings() );

		// Save form meta data.
		if ( ! empty( $form_meta_keys ) ) {
			foreach ( $form_meta_keys as $form_meta_key ) {

				// Set default value for checkbox fields.
				if (
					! isset( $_POST[ $form_meta_key ] ) &&
					in_array( $this->get_field_type( $form_meta_key ), array( 'checkbox', 'chosen' ) )
				) {
					$_POST[ $form_meta_key ] = '';
				}

				if ( isset( $_POST[ $form_meta_key ] ) ) {
					$setting_field = $this->get_setting_field( $form_meta_key );
					if ( ! empty( $setting_field['type'] ) ) {
						switch ( $setting_field['type'] ) {
							case 'textarea':
							case 'wysiwyg':
								$form_meta_value = wp_kses_post( $_POST[ $form_meta_key ] );
								break;

							case 'donation_limit' :
								$form_meta_value = $_POST[ $form_meta_key ];
								break;

							case 'group':
								$form_meta_value = array();

								foreach ( $_POST[ $form_meta_key ] as $index => $group ) {

									// Do not save template input field values.
									if ( '{{row-count-placeholder}}' === $index ) {
										continue;
									}

									$group_meta_value = array();
									foreach ( $group as $field_id => $field_value ) {
										switch ( $this->get_field_type( $field_id, $form_meta_key ) ) {
											case 'wysiwyg':
												$group_meta_value[ $field_id ] = wp_kses_post( $field_value );
												break;

											default:
												$group_meta_value[ $field_id ] = walkthecounty_clean( $field_value );
										}
									}

									if ( ! empty( $group_meta_value ) ) {
										$form_meta_value[ $index ] = $group_meta_value;
									}
								}

								// Arrange repeater field keys in order.
								$form_meta_value = array_values( $form_meta_value );
								break;

							default:
								$form_meta_value = walkthecounty_clean( $_POST[ $form_meta_key ] );
						}// End switch().

						/**
						 * Filter the form meta value before saving
						 *
						 * @since 1.8.9
						 */
						$form_meta_value = apply_filters(
							'walkthecounty_pre_save_form_meta_value',
							$this->sanitize_form_meta( $form_meta_value, $setting_field ),
							$form_meta_key,
							$this,
							$post_id
						);

						// Range slider.
						if ( 'donation_limit' === $setting_field['type'] ) {

							// Sanitize amount for db.
							$form_meta_value = array_map( 'walkthecounty_sanitize_amount_for_db', $form_meta_value );

							// Store it to form meta.
							walkthecounty_update_meta( $post_id, $form_meta_key . '_minimum', $form_meta_value['minimum'] );
							walkthecounty_update_meta( $post_id, $form_meta_key . '_maximum', $form_meta_value['maximum'] );
						} else {
							// Save data.
							walkthecounty_update_meta( $post_id, $form_meta_key, $form_meta_value );
						}

						// Verify and delete form meta based on the form status.
						walkthecounty_set_form_closed_status( $post_id );

						// Fire after saving form meta key.
						do_action( "walkthecounty_save_{$form_meta_key}", $form_meta_key, $form_meta_value, $post_id, $post );
					}// End if().
				}// End if().
			}// End foreach().
		}// End if().

		// Update the goal progress for donation form.
		walkthecounty_update_goal_progress( $post_id );

		// Fire action after saving form meta.
		do_action( 'walkthecounty_post_process_walkthecounty_forms_meta', $post_id, $post );
	}


	/**
	 * Get field ID.
	 *
	 * @param array $field Array of Fields.
	 *
	 * @since 1.8
	 *
	 * @return string
	 */
	private function get_field_id( $field ) {
		$field_id = '';

		if ( array_key_exists( 'id', $field ) ) {
			$field_id = $field['id'];

		}

		return $field_id;
	}

	/**
	 * Get fields ID.
	 *
	 * @param array $setting Array of settings.
	 *
	 * @since 1.8
	 *
	 * @return array
	 */
	private function get_fields_id( $setting ) {
		$meta_keys = array();

		if (
			! empty( $setting )
			&& array_key_exists( 'fields', $setting )
			&& ! empty( $setting['fields'] )
		) {
			foreach ( $setting['fields'] as $field ) {
				if ( $field_id = $this->get_field_id( $field ) ) {
					$meta_keys[] = $field_id;
				}
			}
		}

		return $meta_keys;
	}

	/**
	 * Get sub fields ID.
	 *
	 * @param array $setting Array of settings.
	 *
	 * @since 1.8
	 *
	 * @return array
	 */
	private function get_sub_fields_id( $setting ) {
		$meta_keys = array();

		if ( $this->has_sub_tab( $setting ) && ! empty( $setting['sub-fields'] ) ) {
			foreach ( $setting['sub-fields'] as $fields ) {
				if ( ! empty( $fields['fields'] ) ) {
					foreach ( $fields['fields'] as $field ) {
						if ( $field_id = $this->get_field_id( $field ) ) {
							$meta_keys[] = $field_id;
						}
					}
				}
			}
		}

		return $meta_keys;
	}


	/**
	 * Get all setting field ids.
	 *
	 * @since 1.8
	 *
	 * @return array
	 */
	private function get_meta_keys_from_settings() {
		$meta_keys = array();

		foreach ( $this->settings as $setting ) {
			$meta_key = $this->get_fields_id( $setting );

			if ( $this->has_sub_tab( $setting ) ) {
				$meta_key = array_merge( $meta_key, $this->get_sub_fields_id( $setting ) );
			}

			$meta_keys = array_merge( $meta_keys, $meta_key );
		}

		return $meta_keys;
	}


	/**
	 * Get field type.
	 *
	 * @param string $field_id Field ID.
	 * @param string $group_id Field Group ID.
	 *
	 * @since 1.8
	 *
	 * @return string
	 */
	function get_field_type( $field_id, $group_id = '' ) {
		$field = $this->get_setting_field( $field_id, $group_id );

		$type = array_key_exists( 'type', $field )
			? $field['type']
			: '';

		return $type;
	}


	/**
	 * Get Field
	 *
	 * @param array  $setting  Settings array.
	 * @param string $field_id Field ID.
	 *
	 * @since 1.8
	 *
	 * @return array
	 */
	private function get_field( $setting, $field_id ) {
		$setting_field = array();

		if ( ! empty( $setting['fields'] ) ) {
			foreach ( $setting['fields'] as $field ) {
				if ( array_key_exists( 'id', $field ) && $field['id'] === $field_id ) {
					$setting_field = $field;
					break;
				}
			}
		}

		return $setting_field;
	}

	/**
	 * Get Sub Field
	 *
	 * @param array  $setting  Settings array.
	 * @param string $field_id Field ID.
	 *
	 * @since 1.8
	 *
	 * @return array
	 */
	private function get_sub_field( $setting, $field_id ) {
		$setting_field = array();

		if ( ! empty( $setting['sub-fields'] ) ) {
			foreach ( $setting['sub-fields'] as $fields ) {
				if ( $field = $this->get_field( $fields, $field_id ) ) {
					$setting_field = $field;
					break;
				}
			}
		}

		return $setting_field;
	}

	/**
	 * Get setting field.
	 *
	 * @param string $field_id Field ID.
	 * @param string $group_id Get sub field from group.
	 *
	 * @since 1.8
	 *
	 * @return array
	 */
	function get_setting_field( $field_id, $group_id = '' ) {
		$setting_field = array();

		$_field_id = $field_id;
		$field_id  = empty( $group_id ) ? $field_id : $group_id;

		if ( ! empty( $this->settings ) ) {
			foreach ( $this->settings as $setting ) {
				if (
					( $this->has_sub_tab( $setting ) && ( $setting_field = $this->get_sub_field( $setting, $field_id ) ) )
					|| ( $setting_field = $this->get_field( $setting, $field_id ) )
				) {
					break;
				}
			}
		}

		// Get field from group.
		if ( ! empty( $group_id ) ) {
			foreach ( $setting_field['fields'] as $field ) {
				if ( array_key_exists( 'id', $field ) && $field['id'] === $_field_id ) {
					$setting_field = $field;
				}
			}
		}

		return $setting_field;
	}


	/**
	 * Add offline donations setting tab to donation form options metabox.
	 *
	 * @param array $settings List of form settings.
	 *
	 * @since 1.8
	 *
	 * @return mixed
	 */
	function add_offline_donations_setting_tab( $settings ) {
		if ( walkthecounty_is_gateway_active( 'offline' ) ) {
			$settings['offline_donations_options'] = apply_filters( 'walkthecounty_forms_offline_donations_options', array(
				'id'        => 'offline_donations_options',
				'title'     => __( 'Offline Donations', 'walkthecounty' ),
				'icon-html' => '<span class="walkthecounty-icon walkthecounty-icon-purse"></span>',
				'fields'    => apply_filters( 'walkthecounty_forms_offline_donations_metabox_fields', array() ),
			) );
		}

		return $settings;
	}


	/**
	 * Sanitize form meta values before saving.
	 *
	 * @param mixed $meta_value    Meta Value for sanitizing before saving.
	 * @param array $setting_field Setting Field.
	 *
	 * @since  1.8.9
	 * @access public
	 *
	 * @return mixed
	 */
	function sanitize_form_meta( $meta_value, $setting_field ) {
		switch ( $setting_field['type'] ) {
			case 'group':
				if ( ! empty( $setting_field['fields'] ) ) {
					foreach ( $setting_field['fields'] as $field ) {
						if ( empty( $field['data_type'] ) || 'price' !== $field['data_type'] ) {
							continue;
						}

						foreach ( $meta_value as $index => $meta_data ) {
							if ( ! isset( $meta_value[ $index ][ $field['id'] ] ) ) {
								continue;
							}

							$meta_value[ $index ][ $field['id'] ] = ! empty( $meta_value[ $index ][ $field['id'] ] ) ?
								walkthecounty_sanitize_amount_for_db( $meta_value[ $index ][ $field['id'] ] ) :
								( ( '_walkthecounty_amount' === $field['id'] && empty( $field_value ) ) ?
									walkthecounty_sanitize_amount_for_db( '1.00' ) :
									0 );
						}
					}
				}
				break;

			default:
				if ( ! empty( $setting_field['data_type'] ) && 'price' === $setting_field['data_type'] ) {
					$meta_value = $meta_value ?
						walkthecounty_sanitize_amount_for_db( $meta_value ) :
						( in_array( $setting_field['id'], array(
							'_walkthecounty_set_price',
							'_walkthecounty_custom_amount_minimum',
							'_walkthecounty_set_goal'
						) ) ?
							walkthecounty_sanitize_amount_for_db( '1.00' ) :
							0 );
				}
		}

		return $meta_value;
	}

	/**
	 * Maintain the active tab after save.
	 *
	 * @param string $location The destination URL.
	 * @param int    $post_id  The post ID.
	 *
	 * @since  1.8.13
	 * @access public
	 *
	 * @return string The URL after redirect.
	 */
	public function maintain_active_tab( $location, $post_id ) {
		if (
			'walkthecounty_forms' === get_post_type( $post_id ) &&
			! empty( $_POST['walkthecounty_form_active_tab'] )
		) {
			$location = add_query_arg( 'walkthecounty_tab', walkthecounty_clean( $_POST['walkthecounty_form_active_tab'] ), $location );
		}

		return $location;
	}
}

new WalkTheCounty_MetaBox_Form_Data();

