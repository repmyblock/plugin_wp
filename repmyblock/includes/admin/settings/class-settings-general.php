<?php
/**
 * WalkTheCounty Settings Page/Tab
 *
 * @package     WalkTheCounty
 * @subpackage  Classes/WalkTheCounty_Settings_General
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WalkTheCounty_Settings_General' ) ) :

	/**
	 * WalkTheCounty_Settings_General.
	 *
	 * @sine 1.8
	 */
	class WalkTheCounty_Settings_General extends WalkTheCounty_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'general';
			$this->label = __( 'General', 'walkthecounty' );

			$this->default_tab = 'general-settings';

			if ( $this->id === walkthecounty_get_current_setting_tab() ) {
				add_action( 'walkthecounty_save_settings_walkthecounty_settings', array( $this, '_walkthecounty_change_donation_stating_number' ), 10, 3 );
				add_action( 'walkthecounty_admin_field_walkthecounty_sequential_donation_code_preview', array( $this, '_render_walkthecounty_sequential_donation_code_preview' ), 10, 3 );
				add_action( 'walkthecounty_admin_field_walkthecounty_currency_preview', array( $this, '_render_walkthecounty_currency_preview' ), 10, 2 );
				add_action( 'walkthecounty_admin_field_walkthecounty_unlock_all_settings', array( $this, '_render_walkthecounty_unlock_all_settings' ), 10, 3 );
			}

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
				case 'access-control':
					$settings = array(
						// Section 3: Access control.
						array(
							'id'   => 'walkthecounty_title_session_control_1',
							'type' => 'title',
						),
						array(
							'id'      => 'session_lifetime',
							'name'    => __( 'Session Lifetime', 'walkthecounty' ),
							'desc'    => __( 'The length of time a user\'s session is kept alive. WalkTheCountyWP starts a new session per user upon donation. Sessions allow donors to view their donation receipts without being logged in.', 'walkthecounty' ),
							'type'    => 'select',
							'options' => array(
								'86400'  => __( '24 Hours', 'walkthecounty' ),
								'172800' => __( '48 Hours', 'walkthecounty' ),
								'259200' => __( '72 Hours', 'walkthecounty' ),
								'604800' => __( '1 Week', 'walkthecounty' ),
							),
						),
						array(
							'id'         => 'limit_display_donations',
							'name'       => __( 'Limit Donations Displayed', 'walkthecounty' ),
							'desc'       => __( 'Adjusts the number of donations displayed to a non logged-in user when they attempt to access the Donation History page without an active session. For security reasons, it\'s best to leave this at 1-3 donations.', 'walkthecounty' ),
							'default'    => '1',
							'type'       => 'number',
							'css'        => 'width:50px;',
							'attributes' => array(
								'min' => '1',
								'max' => '10',
							),
						),
						array(
							'name'    => __( 'Email Access', 'walkthecounty' ),
							'desc'    => __( 'Would you like your donors to be able to access their donation history using only email? Donors whose sessions have expired and do not have an account may still access their donation history via a temporary email access link.', 'walkthecounty' ),
							'id'      => 'email_access',
							'type'    => 'radio_inline',
							'default' => 'disabled',
							'options' => array(
								'enabled'  => __( 'Enabled', 'walkthecounty' ),
								'disabled' => __( 'Disabled', 'walkthecounty' ),
							),
						),
						array(
							'name'    => __( 'Enable reCAPTCHA', 'walkthecounty' ),
							'desc'    => __( 'Would you like to enable the reCAPTCHA feature?', 'walkthecounty' ),
							'id'      => 'enable_recaptcha',
							'type'    => 'radio_inline',
							'default' => 'disabled',
							'options' => array(
								'enabled'  => __( 'Enabled', 'walkthecounty' ),
								'disabled' => __( 'Disabled', 'walkthecounty' ),
							),
						),
						array(
							'id'      => 'recaptcha_key',
							'name'    => __( 'reCAPTCHA Site Key', 'walkthecounty' ),
							/* translators: %s: https://www.google.com/recaptcha/ */
							'desc'    => sprintf( __( 'If you would like to prevent spam on the email access form navigate to <a href="%s" target="_blank">the reCAPTCHA website</a> and sign up for an API key and paste your reCAPTCHA site key here. The reCAPTCHA uses Google\'s user-friendly single click verification method.', 'walkthecounty' ), esc_url( 'http://docs.walkthecountywp.com/recaptcha' ) ),
							'default' => '',
							'type'    => 'text',
						),
						array(
							'id'      => 'recaptcha_secret',
							'name'    => __( 'reCAPTCHA Secret Key', 'walkthecounty' ),
							'desc'    => __( 'Please paste the reCAPTCHA secret key here from your  reCAPTCHA API Keys panel.', 'walkthecounty' ),
							'default' => '',
							'type'    => 'text',
						),
						array(
							'name'  => __( 'Access Control Docs Link', 'walkthecounty' ),
							'id'    => 'access_control_docs_link',
							'url'   => esc_url( 'http://docs.walkthecountywp.com/settings-access-control' ),
							'title' => __( 'Access Control', 'walkthecounty' ),
							'type'  => 'walkthecounty_docs_link',
						),
						array(
							'id'   => 'walkthecounty_title_session_control_1',
							'type' => 'sectionend',
						),
					);
					break;

				case 'currency-settings' :
					$currency_position_before = __( 'Before - %s&#x200e;10', 'walkthecounty' );
					$currency_position_after  = __( 'After - 10%s&#x200f;', 'walkthecounty' );

					$settings = array(
						// Section 2: Currency
						array(
							'type' => 'title',
							'id'   => 'walkthecounty_title_general_settings_2',
						),
						array(
							'name' => __( 'Currency Settings', 'walkthecounty' ),
							'desc' => '',
							'type' => 'walkthecounty_title',
							'id'   => 'walkthecounty_title_general_settings_2',
						),
						array(
							'name'    => __( 'Currency', 'walkthecounty' ),
							'desc'    => __( 'The donation currency. Note that some payment gateways have currency restrictions.', 'walkthecounty' ),
							'id'      => 'currency',
							'class'   => 'walkthecounty-select-chosen',
							'type'    => 'select',
							'options' => walkthecounty_get_currencies(),
							'default' => 'USD',
							'attributes' => array(
								'data-formatting-setting' => esc_js( wp_json_encode( walkthecounty_get_currencies_list() ))
							)
						),
						array(
							'name'       => __( 'Currency Position', 'walkthecounty' ),
							'desc'       => __( 'The position of the currency symbol.', 'walkthecounty' ),
							'id'         => 'currency_position',
							'type'       => 'select',
							'options'    => array(
								/* translators: %s: currency symbol */
								'before' => sprintf( $currency_position_before, walkthecounty_currency_symbol( walkthecounty_get_currency() ) ),
								/* translators: %s: currency symbol */
								'after'  => sprintf( $currency_position_after, walkthecounty_currency_symbol( walkthecounty_get_currency() ) ),
							),
							'default'    => 'before',
							'attributes' => array(
								'data-before-template' => sprintf( $currency_position_before, '{currency_pos}' ),
								'data-after-template'  => sprintf( $currency_position_after, '{currency_pos}' ),
							),
						),
						array(
							'name'    => __( 'Thousands Separator', 'walkthecounty' ),
							'desc'    => __( 'The symbol (typically , or .) to separate thousands.', 'walkthecounty' ),
							'id'      => 'thousands_separator',
							'type'    => 'text',
							'default' => ',',
							'css'     => 'width:12em;',
						),
						array(
							'name'    => __( 'Decimal Separator', 'walkthecounty' ),
							'desc'    => __( 'The symbol (usually , or .) to separate decimal points.', 'walkthecounty' ),
							'id'      => 'decimal_separator',
							'type'    => 'text',
							'default' => '.',
							'css'     => 'width:12em;',
						),
						array(
							'name'    => __( 'Number of Decimals', 'walkthecounty' ),
							'desc'    => __( 'The number of decimal points displayed in amounts.', 'walkthecounty' ),
							'id'      => 'number_decimals',
							'type'    => 'text',
							'default' => 2,
							'css'     => 'width:12em;',
						),
						array(
							'name'    => __( 'Currency Preview', 'walkthecounty' ),
							'desc'    => __( 'A preview of the formatted currency. This preview cannot be edited directly as it is generated from the settings above.', 'walkthecounty' ),
							'id'      => 'currency_preview',
							'type'    => 'walkthecounty_currency_preview',
							'default' => walkthecounty_format_amount( 123456.12345,
								array(
									'sanitize' => false,
									'currency' => walkthecounty_get_option( 'currency' ),
								)
							),
							'css'     => 'width:12em;',
						),
						array(
							'name'  => __( 'Currency Options Docs Link', 'walkthecounty' ),
							'id'    => 'currency_settings_docs_link',
							'url'   => esc_url( 'http://docs.walkthecountywp.com/settings-currency' ),
							'title' => __( 'Currency Settings', 'walkthecounty' ),
							'type'  => 'walkthecounty_docs_link',
						),
						array(
							'type' => 'sectionend',
							'id'   => 'walkthecounty_title_general_settings_2',
						),
					);

					break;

				case 'general-settings':
					// Get default country code.
					$countries = walkthecounty_get_country();

					// get the list of the states of which default country is selected.
					$states = walkthecounty_get_states( $countries );

					// Get the country list that does not have any states init.
					$no_states_country = walkthecounty_no_states_country_list();

					$states_label = walkthecounty_get_states_label();
					$country      = walkthecounty_get_country();
					$label        = __( 'State', 'walkthecounty' );
					// Check if $country code exists in the array key for states label.
					if ( array_key_exists( $country, $states_label ) ) {
						$label = $states_label[ $country ];
					}


					$settings = array(
						// Section 1: General.
						array(
							'type' => 'title',
							'id'   => 'walkthecounty_title_general_settings_1',
						),
						array(
							'name' => __( 'General Settings', 'walkthecounty' ),
							'desc' => '',
							'type' => 'walkthecounty_title',
							'id'   => 'walkthecounty_title_general_settings_1',
						),
						array(
							'name'       => __( 'Success Page', 'walkthecounty' ),
							/* translators: %s: [walkthecounty_receipt] */
							'desc'       => sprintf( __( 'The page donors are sent to after completing their donations. The %s shortcode should be on this page.', 'walkthecounty' ), '<code>[walkthecounty_receipt]</code>' ),
							'id'         => 'success_page',
							'class'      => 'walkthecounty-select walkthecounty-select-chosen',
							'type'       => 'select',
							'options'    => walkthecounty_cmb2_get_post_options( array(
								'post_type'   => 'page',
								'numberposts' => 30,
							) ),
							'attributes' => array(
								'data-search-type' => 'pages',
								'data-placeholder' => esc_html__('Choose a page', 'walkthecounty'),
							)
						),
						array(
							'name'       => __( 'Failed Donation Page', 'walkthecounty' ),
							'desc'       => __( 'The page donors are sent to if their donation is cancelled or fails.', 'walkthecounty' ),
							'class'      => 'walkthecounty-select walkthecounty-select-chosen',
							'id'         => 'failure_page',
							'type'       => 'select',
							'options'    => walkthecounty_cmb2_get_post_options( array(
								'post_type'   => 'page',
								'numberposts' => 30,
							) ),
							'attributes' => array(
								'data-search-type' => 'pages',
								'data-placeholder' => esc_html__('Choose a page', 'walkthecounty'),
							)
						),
						array(
							'name'       => __( 'Donation History Page', 'walkthecounty' ),
							/* translators: %s: [donation_history] */
							'desc'       => sprintf( __( 'The page showing a complete donation history for the current user. The %s shortcode should be on this page.', 'walkthecounty' ), '<code>[donation_history]</code>' ),
							'id'         => 'history_page',
							'class'      => 'walkthecounty-select walkthecounty-select-chosen',
							'type'       => 'select',
							'options'    => walkthecounty_cmb2_get_post_options( array(
								'post_type'   => 'page',
								'numberposts' => 30,
							) ),
							'attributes' => array(
								'data-search-type' => 'pages',
								'data-placeholder' => esc_html__('Choose a page', 'walkthecounty'),
							)
						),
						array(
							'name'       => __( 'Base Country', 'walkthecounty' ),
							'desc'       => __( 'The country your site operates from.', 'walkthecounty' ),
							'id'         => 'base_country',
							'type'       => 'select',
							'options'    => walkthecounty_get_country_list(),
							'class'      => 'walkthecounty-select walkthecounty-select-chosen',
							'attributes' => array(
								'data-search-type' => 'no_ajax'
							),
							'default'    => $country,
						),
						/**
						 * Add base state to walkthecounty setting
						 *
						 * @since 1.8.14
						 */
						array(
							'wrapper_class' => ( array_key_exists( $countries, $no_states_country ) ? 'walkthecounty-hidden' : '' ),
							'name'          => __( 'Base State/Province', 'walkthecounty' ),
							'desc'          => __( 'The state/province your site operates from.', 'walkthecounty' ),
							'id'            => 'base_state',
							'type'          => ( empty( $states ) ? 'text' : 'select' ),
							'class'         => ( empty( $states ) ? '' : 'walkthecounty-select walkthecounty-select-chosen' ),
							'options'       => $states,
							'attributes'    => array(
								'data-search-type' => 'no_ajax',
								'data-placeholder' => $label,
							),
						),
						array(
							'name'  => __( 'General Options Docs Link', 'walkthecounty' ),
							'id'    => 'general_options_docs_link',
							'url'   => esc_url( 'http://docs.walkthecountywp.com/settings-general' ),
							'title' => __( 'General Options', 'walkthecounty' ),
							'type'  => 'walkthecounty_docs_link',
						),
						array(
							'type' => 'sectionend',
							'id'   => 'walkthecounty_title_general_settings_1',
						),
					);
					break;

				case 'sequential-ordering':
					$settings = array(

						// Section 4: Sequential Ordering

						array(
							'id'   => 'walkthecounty_title_general_settings_4',
							'type' => 'title'
						),
						array(
							'name'    => __( 'Sequential Ordering', 'walkthecounty' ),
							'id'      => "{$current_section}_status",
							'desc'    => __( 'Custom donation numbering that increases sequentially to prevent gaps between donation IDs. If disabled, then donation numbers are generated from WordPress post IDs, which will result in gaps between numbers.', 'walkthecounty' ),
							'type'    => 'radio_inline',
							'default' => 'disabled',
							'options' => array(
								'enabled'  => __( 'Enabled', 'walkthecounty' ),
								'disabled' => __( 'Disabled', 'walkthecounty' )
							)
						),
						array(
							'name' => __( 'Next Donation Number', 'walkthecounty' ),
							'id'   => "{$current_section}_number",
							'desc' => sprintf(
								__( 'The number used to generate the next donation ID. This value must be greater than or equal to %s to avoid conflicts with existing donation IDs.', 'walkthecounty' ),
								'<code>' . WalkTheCounty()->seq_donation_number->get_next_number() . '</code>'
							),
							'type' => 'number',
						),
						array(
							'name' => __( 'Number Prefix', 'walkthecounty' ),
							'id'   => "{$current_section}_number_prefix",
							'desc' => sprintf(
								__( 'The prefix appended to all sequential donation numbers. Spaces are replaced by %s.', 'walkthecounty' ),
								'<code>-</code>'
							),
							'type' => 'text',
						),
						array(
							'name' => __( 'Number Suffix', 'walkthecounty' ),
							'id'   => "{$current_section}_number_suffix",
							'desc' => sprintf(
								__( 'The suffix appended to all sequential donation numbers. Spaces are replaced by %s.', 'walkthecounty' ),
								'<code>-</code>'
							),
							'type' => 'text',
						),
						array(
							'name'    => __( 'Number Padding', 'walkthecounty' ),
							'id'      => "{$current_section}_number_padding",
							'desc'    => sprintf(
								__( 'The minimum number of digits in the sequential donation number. Enter %s to display %s as %s.', 'walkthecounty' ),
								'<code>4</code>',
								'<code>1</code>',
								'<code>0001</code>'
							),
							'type'    => 'number',
							'default' => '0',
						),
						array(
							'name' => __( 'Donation ID Preview', 'walkthecounty' ),
							'id'   => "{$current_section}_preview",
							'type' => 'walkthecounty_sequential_donation_code_preview',
							'desc' => __( 'A preview of the next sequential donation ID. This preview cannot be edited directly as it is generated from the settings above.', 'walkthecounty' ),
						),
						array(
							'name'  => __( 'Sequential Ordering Docs Link', 'walkthecounty' ),
							'id'    => "{$current_section}_doc link",
							'url'   => esc_url( 'http://docs.walkthecountywp.com/settings-sequential-ordering' ),
							'title' => __( 'Sequential Ordering', 'walkthecounty' ),
							'type'  => 'walkthecounty_docs_link',
						),
						array(
							'id'   => 'walkthecounty_title_general_settings_4',
							'type' => 'sectionend'
						)
					);
			}

			/**
			 * Filter the general settings.
			 * Backward compatibility: Please do not use this filter. This filter is deprecated in 1.8
			 */
			$settings = apply_filters( 'walkthecounty_settings_general', $settings );

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
				'general-settings'    => __( 'General', 'walkthecounty' ),
				'currency-settings'   => __( 'Currency', 'walkthecounty' ),
				'access-control'      => __( 'Access Control', 'walkthecounty' ),
				'sequential-ordering' => __( 'Sequential Ordering', 'walkthecounty' ),
			);

			return apply_filters( 'walkthecounty_get_sections_' . $this->id, $sections );
		}


		/**
		 * Set flag to reset sequestion donation number starting point when "Sequential Starting Number" value changes
		 *
		 * @since  2.1
		 * @access public
		 *
		 * @param $update_options
		 * @param $option_name
		 * @param $old_options
		 *
		 * @return bool
		 */
		public function _walkthecounty_change_donation_stating_number( $update_options, $option_name, $old_options ) {
			if ( ! isset( $_POST['sequential-ordering_number'] ) ) {
				return false;
			}

			if ( ( $next_number = WalkTheCounty()->seq_donation_number->get_next_number() ) > $update_options['sequential-ordering_number'] ) {
				walkthecounty_update_option( 'sequential-ordering_number', $next_number );

				WalkTheCounty_Admin_Settings::add_error(
					'walkthecounty-invalid-sequential-starting-number',
					sprintf(
						__( 'Next Donation Number must be equal to or larger than %s to avoid conflicts with existing donation IDs.', 'walkthecounty' ),
						$next_number
					)
				);
			} elseif ( $update_options['sequential-ordering_number'] !== $old_options['sequential-ordering_number'] ) {
				update_option( '_walkthecounty_reset_sequential_number', 1, false );
			}

			return true;
		}

		/**
		 * Render walkthecounty_sequential_donation_code_preview field type
		 *
		 * @since  2.1.0
		 * @access public
		 *
		 * @param $field
		 */
		public function _render_walkthecounty_sequential_donation_code_preview( $field ) {
			?>
			<tr valign="top" <?php echo ! empty( $field['wrapper_class'] ) ? 'class="' . $field['wrapper_class'] . '"' : '' ?>>
				<th scope="row" class="titledesc">
					<label
						for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['name'] ) ?></label>
				</th>
				<td class="walkthecounty-forminp">
					<input id="<?php echo esc_attr( $field['id'] ); ?>" class="walkthecounty-input-field" type="text" disabled>
					<?php echo WalkTheCounty_Admin_Settings::get_field_description( $field ); ?>
				</td>
			</tr>
			<?php
		}

		/**
		 * Render walkthecounty_currency_code_preview field type
		 *
		 * @since  2.3.0
		 * @access public
		 *
		 * @param array $field Field Attributes array.
		 *
		 * @return void
		 */
		public function _render_walkthecounty_currency_preview( $field, $value ) {
			$currency          = walkthecounty_get_currency();
			$currency_position = walkthecounty_get_currency_position();
			$currency_symbol   = walkthecounty_currency_symbol( $currency, false );
			$formatted_currency = ( 'before' === $currency_position )
				? sprintf( '%1$s%2$s', esc_html( $currency_symbol ), esc_html( $field['default'] ) )
				: sprintf( '%1$s%2$s', esc_html( $field['default'] ), esc_html( $currency_symbol ) );
			?>
			<tr valign="top" <?php echo ! empty( $field['wrapper_class'] ) ? 'class="' . $field['wrapper_class'] . '"' : '' ?>>
				<th scope="row" class="titledesc">
					<label
						for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['name'] ) ?></label>
				</th>
				<td class="walkthecounty-forminp">
					<input id="<?php echo esc_attr( $field['id'] ); ?>" class="walkthecounty-input-field" type="text" disabled value="<?php echo esc_attr( $formatted_currency ); ?>">
					<?php echo WalkTheCounty_Admin_Settings::get_field_description( $field ); ?>
				</td>
			</tr>
			<?php
		}

		/**
		 * Render walkthecounty_unlock_all_settings field type
		 *
		 * @since  2.1.0
		 * @access public
		 *
		 * @param $field
		 */
		public function _render_walkthecounty_unlock_all_settings( $field ) {
			?>
			<tr valign="top" <?php echo ! empty( $field['wrapper_class'] ) ? 'class="' . $field['wrapper_class'] . '"' : '' ?>>
				<th scope="row" class="titledesc">
					<label
						for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['name'] ) ?></label>
				</th>
				<td class="walkthecounty-forminp">
					<?php echo WalkTheCounty_Admin_Settings::get_field_description( $field ); ?>
					<a href="" id="<?php echo $field['id']; ?>" data-message="<?php echo $field['confirmation_msg'] ?>"><?php echo __( 'Unlock all settings', 'walkthecounty' ); ?></a>
				</td>
			</tr>
			<?php
		}
	}

endif;

return new WalkTheCounty_Settings_General();
