<?php
/**
 * WalkTheCounty Settings Page/Tab
 *
 * @package     WalkTheCounty
 * @subpackage  Classes/WalkTheCounty_Settings_Display
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WalkTheCounty_Settings_Display' ) ) :

	/**
	 * WalkTheCounty_Settings_Display.
	 *
	 * @sine 1.8
	 */
	class WalkTheCounty_Settings_Display extends WalkTheCounty_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'display';
			$this->label = __( 'Display Options', 'walkthecounty' );

			$this->default_tab = 'display-settings';

			parent::__construct();
		}

		/**
		 * Get settings array.
		 *
		 * @since  1.8
		 * @return array
		 */
		public function get_settings() {
			$settings        = array();
			$current_section = walkthecounty_get_current_setting_section();

			switch ( $current_section ) {
				case 'display-settings' :
					$settings = array(
						// Section 1: Display
						array(
							'id'   => 'walkthecounty_title_display_settings_1',
							'type' => 'title',
						),
						array(
							'name'    => __( 'Default WalkTheCountyWP Styles', 'walkthecounty' ),
							'desc'    => __( 'You can disable WalkTheCounty\'s default styles for donation forms and other frontend elements.', 'walkthecounty' ),
							'id'      => 'css',
							'type'    => 'radio_inline',
							'default' => 'enabled',
							'options' => array(
								'enabled'  => __( 'Enabled', 'walkthecounty' ),
								'disabled' => __( 'Disabled', 'walkthecounty' ),
							),
						),
						array(
							'name'    => __( 'Floating Labels', 'walkthecounty' ),
							/* translators: %s: http://docs.walkthecountywp.com/form-floating-labels */
							'desc'    => sprintf( wp_kses( __( '<a href="%s" target="_blank">Floating labels</a> allows your labels to be inset within the form fields to provide a cleaner form appearance. Note that if the "Disable CSS" option is enabled, you will need to style the floating labels yourself.', 'walkthecounty' ), array(
								'a' => array(
									'href'   => array(),
									'target' => array(),
								),
							) ), esc_url( 'http://docs.walkthecountywp.com/form-floating-labels' ) ),
							'id'      => 'floatlabels',
							'type'    => 'radio_inline',
							'default' => 'disabled',
							'options' => array(
								'enabled'  => __( 'Enabled', 'walkthecounty' ),
								'disabled' => __( 'Disabled', 'walkthecounty' ),
							),
						),
						array(
							'name'    => __( 'Name Title Prefix', 'walkthecounty' ),
							'desc'    => __( 'Do you want a Name Title Prefix field to appear before First Name?', 'walkthecounty' ),
							'id'      => 'name_title_prefix',
							'type'    => 'radio_inline',
							'default' => 'disabled',
							'options' => array(
								'disabled' => __( 'Disabled', 'walkthecounty' ),
								'required' => __( 'Required', 'walkthecounty' ),
								'optional' => __( 'Optional', 'walkthecounty' ),
							),
						),
						array(
							'name'          => __( 'Title Prefixes', 'walkthecounty' ),
							'desc'          => __( 'Add or remove salutations from the dropdown using the field above.', 'walkthecounty' ),
							'id'            => 'title_prefixes',
							'type'          => 'chosen',
							'data_type'     => 'multiselect',
							'wrapper_class' => 'walkthecounty-hidden walkthecounty-title-prefixes-settings-wrap',
							'style'         => 'width: 30%',
							'options'       => walkthecounty_get_default_title_prefixes(),
						),
						array(
							'name'    => __( 'Company Field', 'walkthecounty' ),
							'desc'    => __( 'Do you want a Company field to appear after First Name and Last Name fields on all donation forms? You can enable this option per form as well.', 'walkthecounty' ),
							'id'      => 'company_field',
							'type'    => 'radio_inline',
							'default' => 'disabled',
							'options' => array(
								'disabled' => __( 'Disabled', 'walkthecounty' ),
								'required' => __( 'Required', 'walkthecounty' ),
								'optional' => __( 'Optional', 'walkthecounty' ),
							),
						),
						array(
							'name'    => __( 'Anonymous Donations', 'walkthecounty' ),
							'desc'    => __( 'Do you want to provide donors the ability mark themselves anonymous while giving. This will prevent their information from appearing publicly on your website but you will still receive their information for your records in the admin panel.', 'walkthecounty' ),
							'id'      => 'anonymous_donation',
							'type'    => 'radio_inline',
							'default' => 'disabled',
							'options' => array(
								'enabled'  => __( 'Enabled', 'walkthecounty' ),
								'disabled' => __( 'Disabled', 'walkthecounty' ),
							),
						),
						array(
							'name'    => __( 'Donor Comments', 'walkthecounty' ),
							'desc'    => __( 'Do you want to provide donors the ability to add a comment to their donation? The comment will display publicly on the donor wall if they do not select to walkthecounty anonymously.', 'walkthecounty' ),
							'id'      => 'donor_comment',
							'type'    => 'radio_inline',
							'default' => 'disabled',
							'options' => array(
								'enabled'  => __( 'Enabled', 'walkthecounty' ),
								'disabled' => __( 'Disabled', 'walkthecounty' ),
							),
						),
						array(
							'name'  => __( 'Display Settings Docs Link', 'walkthecounty' ),
							'id'    => 'display_settings_docs_link',
							'url'   => esc_url( 'http://docs.walkthecountywp.com/form-display-options' ),
							'title' => __( 'Display Options Settings', 'walkthecounty' ),
							'type'  => 'walkthecounty_docs_link',
						),
						array(
							'id'   => 'walkthecounty_title_display_settings_1',
							'type' => 'sectionend',
						),
					);
					break;

				case 'post-types' :
					$settings = array(
						array(
							'id'   => 'walkthecounty_title_display_settings_2',
							'type' => 'title',
						),
						array(
							'name'    => __( 'Form Single Views', 'walkthecounty' ),
							'desc'    => __( 'By default, all donation form have single views enabled which creates a specific URL on your website for that form. This option disables the singular posts from being publicly viewable. Note: you will need to embed forms using a shortcode or widget if enabled.', 'walkthecounty' ),
							'id'      => 'forms_singular',
							'type'    => 'radio_inline',
							'default' => 'enabled',
							'options' => array(
								'enabled'  => __( 'Enabled', 'walkthecounty' ),
								'disabled' => __( 'Disabled', 'walkthecounty' ),
							),
						),
						array(
							'name'    => __( 'Form Archives', 'walkthecounty' ),
							'desc'    => sprintf( wp_kses( __( 'Archives pages list all the donation forms you have created. This option will disable only the form\'s archive page(s). The single form\'s view will remain in place. Note: you will need to <a href="%s">refresh your permalinks</a> after this option has been enabled.', 'walkthecounty' ), array(
								'a' => array(
									'href'   => array(),
									'target' => array(),
								),
							) ), esc_url( admin_url( 'options-permalink.php' ) ) ),
							'id'      => 'forms_archives',
							'type'    => 'radio_inline',
							'default' => 'enabled',
							'options' => array(
								'enabled'  => __( 'Enabled', 'walkthecounty' ),
								'disabled' => __( 'Disabled', 'walkthecounty' ),
							),
						),
						array(
							'name'    => __( 'Form Excerpts', 'walkthecounty' ),
							'desc'    => __( 'The excerpt is an optional summary or description of a donation form; in short, a summary as to why the user should walkthecounty.', 'walkthecounty' ),
							'id'      => 'forms_excerpt',
							'type'    => 'radio_inline',
							'default' => 'enabled',
							'options' => array(
								'enabled'  => __( 'Enabled', 'walkthecounty' ),
								'disabled' => __( 'Disabled', 'walkthecounty' ),
							),
						),
						array(
							'name'    => __( 'Form Featured Image', 'walkthecounty' ),
							'desc'    => __( 'If you do not wish to use the featured image functionality you can disable it using this option and it will not be displayed for single donation forms.', 'walkthecounty' ),
							'id'      => 'form_featured_img',
							'type'    => 'radio_inline',
							'default' => 'enabled',
							'options' => array(
								'enabled'  => __( 'Enabled', 'walkthecounty' ),
								'disabled' => __( 'Disabled', 'walkthecounty' ),
							),
						),
						array(
							'name'    => __( 'Featured Image Size', 'walkthecounty' ),
							'desc'    => __( 'The Featured Image is an image that is chosen as the representative image for a donation form. Some themes may have custom featured image sizes. Please select the size you would like to display for your single donation form\'s featured image.', 'walkthecounty' ),
							'id'      => 'featured_image_size',
							'type'    => 'select',
							'default' => 'large',
							'options' => walkthecounty_get_featured_image_sizes(),
						),
						array(
							'name'    => __( 'Single Form Sidebar', 'walkthecounty' ),
							'desc'    => __( 'The sidebar allows you to add additional widgets to the WalkTheCountyWP single form view. If you don\'t plan on using the sidebar you may disable it with this option.', 'walkthecounty' ),
							'id'      => 'form_sidebar',
							'type'    => 'radio_inline',
							'default' => 'enabled',
							'options' => array(
								'enabled'  => __( 'Enabled', 'walkthecounty' ),
								'disabled' => __( 'Disabled', 'walkthecounty' ),
							),
						),
						array(
							'name'  => __( 'Post Types Docs Link', 'walkthecounty' ),
							'id'    => 'post_types_settings_docs_link',
							'url'   => esc_url( 'http://docs.walkthecountywp.com/settings-post-types' ),
							'title' => __( 'Post Types Settings', 'walkthecounty' ),
							'type'  => 'walkthecounty_docs_link',
						),
						array(
							'id'   => 'walkthecounty_title_display_settings_2',
							'type' => 'sectionend',
						),
					);
					break;

				case 'taxonomies':
					$settings = array(
						array(
							'id'   => 'walkthecounty_title_display_settings_3',
							'type' => 'title',
						),
						array(
							'name'    => __( 'Form Categories', 'walkthecounty' ),
							'desc'    => __( 'Enable Categories for all WalkTheCountyWP forms.', 'walkthecounty' ),
							'id'      => 'categories',
							'type'    => 'radio_inline',
							'default' => 'disabled',
							'options' => array(
								'enabled'  => __( 'Enabled', 'walkthecounty' ),
								'disabled' => __( 'Disabled', 'walkthecounty' ),
							),
						),
						array(
							'name'    => __( 'Form Tags', 'walkthecounty' ),
							'desc'    => __( 'Enable Tags for all WalkTheCountyWP forms.', 'walkthecounty' ),
							'id'      => 'tags',
							'type'    => 'radio_inline',
							'default' => 'disabled',
							'options' => array(
								'enabled'  => __( 'Enabled', 'walkthecounty' ),
								'disabled' => __( 'Disabled', 'walkthecounty' ),
							),
						),
						array(
							'name'  => __( 'Taxonomies Docs Link', 'walkthecounty' ),
							'id'    => 'taxonomies_settings_docs_link',
							'url'   => esc_url( 'http://docs.walkthecountywp.com/settings-taxonomies' ),
							'title' => __( 'Taxonomies Settings', 'walkthecounty' ),
							'type'  => 'walkthecounty_docs_link',
						),
						array(
							'id'   => 'walkthecounty_title_display_settings_3',
							'type' => 'sectionend',
						),
					);
					break;

				case 'term-and-conditions':
					$settings = array(
						array(
							'id'   => 'walkthecounty_title_display_settings_4',
							'type' => 'title',
						),
						array(
							'name'    => __( 'Terms and Conditions', 'walkthecounty' ),
							'desc'    => __( 'Would you like donors to require that donors agree to your terms when donating? Note: You can enable/disable this option and customize the terms per form as well.', 'walkthecounty' ),
							'id'      => 'terms',
							'type'    => 'radio_inline',
							'default' => 'disabled',
							'options' => array(
								'enabled'  => __( 'Enabled', 'walkthecounty' ),
								'disabled' => __( 'Disabled', 'walkthecounty' ),
							),
						),
						array(
							'name'       => __( 'Agree to Terms Label', 'walkthecounty' ),
							'desc'       => __( 'The label shown next to the agree to terms check box. Customize it here or leave blank to use the default placeholder text. Note: You can customize the label per form.', 'walkthecounty' ),
							'id'         => 'agree_to_terms_label',
							'attributes' => array(
								'placeholder' => esc_attr__( 'Agree to Terms?', 'walkthecounty' ),
								'rows'        => 1
							),
							'type'       => 'textarea',
						),
						array(
							'name' => __( 'Agreement Text', 'walkthecounty' ),
							'desc' => __( 'This is the actual text which the user will be asked to agree to in order to donate. Note: You can customize the content per form as needed.', 'walkthecounty' ),
							'id'   => 'agreement_text',
							'type' => 'wysiwyg',
						),
						array(
							'name'  => __( 'Terms and Conditions Docs Link', 'walkthecounty' ),
							'id'    => 'terms_settings_docs_link',
							'url'   => esc_url( 'http://docs.walkthecountywp.com/settings-terms' ),
							'title' => __( 'Terms and Conditions Settings', 'walkthecounty' ),
							'type'  => 'walkthecounty_docs_link',
						),
						array(
							'id'   => 'walkthecounty_title_display_settings_4',
							'type' => 'sectionend',
						),
					);
					break;
			}

			/**
			 * Filter the display options settings.
			 * Backward compatibility: Please do not use this filter. This filter is deprecated in 1.8
			 */
			$settings = apply_filters( 'walkthecounty_settings_display', $settings );

			/**
			 * Filter the settings.
			 *
			 * @since  1.8
			 *
			 * @param  array $settings
			 */
			$settings = apply_filters( 'walkthecounty_get_settings_' . $this->id, $settings );

			// Output.
			return $settings;
		}

		/**
		 * Get sections.
		 *
		 * @since 1.8
		 * @return array
		 */
		public function get_sections() {
			$sections = array(
				'display-settings'    => __( 'Display', 'walkthecounty' ),
				'post-types'          => __( 'Post Types', 'walkthecounty' ),
				'taxonomies'          => __( 'Taxonomies', 'walkthecounty' ),
				'term-and-conditions' => __( 'Terms and Conditions', 'walkthecounty' ),
			);

			return apply_filters( 'walkthecounty_get_sections_' . $this->id, $sections );
		}
	}

endif;

return new WalkTheCounty_Settings_Display();
