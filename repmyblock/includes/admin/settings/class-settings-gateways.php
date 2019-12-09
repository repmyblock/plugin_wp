<?php
/**
 * WalkTheCounty Settings Page/Tab
 *
 * @package     WalkTheCounty
 * @subpackage  Classes/WalkTheCounty_Settings_Gateways
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WalkTheCounty_Settings_Gateways' ) ) :

	/**
	 * WalkTheCounty_Settings_Gateways.
	 *
	 * @sine 1.8
	 */
	class WalkTheCounty_Settings_Gateways extends WalkTheCounty_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'gateways';
			$this->label = esc_html__( 'Payment Gateways', 'walkthecounty' );

			$this->default_tab = 'gateways-settings';

			parent::__construct();

			// Do not use main form for this tab.
			if ( walkthecounty_get_current_setting_tab() === $this->id ) {
				add_action( 'walkthecounty_admin_field_gateway_notice', array( $this, 'render_gateway_notice' ), 10, 2 );
				add_action( 'walkthecounty_admin_field_enabled_gateways', array( $this, 'render_enabled_gateways' ), 10, 2 );
			}
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
				case 'paypal-standard':
					$settings = array(
						// Section 2: PayPal Standard.
						array(
							'type' => 'title',
							'id'   => 'walkthecounty_title_gateway_settings_2',
						),
						array(
							'name' => __( 'PayPal Email', 'walkthecounty' ),
							'desc' => __( 'Enter the email address associated with your PayPal account to connect with the gateway.', 'walkthecounty' ),
							'id'   => 'paypal_email',
							'type' => 'email',
						),
						array(
							'name' => __( 'PayPal Page Style', 'walkthecounty' ),
							'desc' => __( 'Enter the name of the PayPal page style to use, or leave blank to use the default.', 'walkthecounty' ),
							'id'   => 'paypal_page_style',
							'type' => 'text',
						),
						array(
							'name'    => __( 'PayPal Transaction Type', 'walkthecounty' ),
							'desc'    => __( 'Nonprofits must verify their status to withdraw donations they receive via PayPal. PayPal users that are not verified nonprofits must demonstrate how their donations will be used, once they raise more than $10,000. By default, WalkTheCountyWP transactions are sent to PayPal as donations. You may change the transaction type using this option if you feel you may not meet PayPal\'s donation requirements.', 'walkthecounty' ),
							'id'      => 'paypal_button_type',
							'type'    => 'radio_inline',
							'options' => array(
								'donation' => __( 'Donation', 'walkthecounty' ),
								'standard' => __( 'Standard Transaction', 'walkthecounty' )
							),
							'default' => 'donation',
						),
						array(
							'name'    => __( 'Billing Details', 'walkthecounty' ),
							'desc'    => __( 'This option will enable the billing details section for PayPal Standard which requires the donor\'s address to complete the donation. These fields are not required by PayPal to process the transaction, but you may have a need to collect the data.', 'walkthecounty' ),
							'id'      => 'paypal_standard_billing_details',
							'type'    => 'radio_inline',
							'default' => 'disabled',
							'options' => array(
								'enabled'  => __( 'Enabled', 'walkthecounty' ),
								'disabled' => __( 'Disabled', 'walkthecounty' ),
							)
						),
						array(
							'name'    => __( 'PayPal IPN Verification', 'walkthecounty' ),
							'desc'    => __( 'If donations are not getting marked as complete, use a slightly less secure method of verifying donations.', 'walkthecounty' ),
							'id'      => 'paypal_verification',
							'type'    => 'radio_inline',
							'default' => 'enabled',
							'options' => array(
								'enabled'  => __( 'Enabled', 'walkthecounty' ),
								'disabled' => __( 'Disabled', 'walkthecounty' ),
							)
						),
						array(
							'id'      => 'paypal_invoice_prefix',
							'name'    => esc_html__( 'Invoice ID Prefix', 'walkthecounty' ),
							'desc'    => esc_html__( 'Please enter a prefix for your invoice numbers. If you use your PayPal account for multiple stores ensure this prefix is unique as PayPal will not allow orders with the same invoice number.', 'walkthecounty' ),
							'type'    => 'text',
							'default' => 'WALKTHECOUNTY-',
						),
						array(
							'name'  => __( 'PayPal Standard Gateway Settings Docs Link', 'walkthecounty' ),
							'id'    => 'paypal_standard_gateway_settings_docs_link',
							'url'   => esc_url( 'http://docs.walkthecountywp.com/settings-gateway-paypal-standard' ),
							'title' => __( 'PayPal Standard Gateway Settings', 'walkthecounty' ),
							'type'  => 'walkthecounty_docs_link',
						),
						array(
							'type' => 'sectionend',
							'id'   => 'walkthecounty_title_gateway_settings_2',
						)
					);
					break;

				case 'offline-donations' :
					$settings = array(
						// Section 3: Offline gateway.
						array(
							'type' => 'title',
							'id'   => 'walkthecounty_title_gateway_settings_3',
						),
						array(
							'name'    => __( 'Collect Billing Details', 'walkthecounty' ),
							'desc'    => __( 'Enable to request billing details for offline donations. Will appear above offline donation instructions. Can be enabled/disabled per form.', 'walkthecounty' ),
							'id'      => 'walkthecounty_offline_donation_enable_billing_fields',
							'type'    => 'radio_inline',
							'default' => 'disabled',
							'options' => array(
								'enabled'  => __( 'Enabled', 'walkthecounty' ),
								'disabled' => __( 'Disabled', 'walkthecounty' )
							)
						),
						array(
							'name'    => __( 'Offline Donation Instructions', 'walkthecounty' ),
							'desc'    => __( 'The following content will appear for all forms when the user selects the offline donation payment option. Note: You may customize the content per form as needed.', 'walkthecounty' ),
							'id'      => 'global_offline_donation_content',
							'default' => walkthecounty_get_default_offline_donation_content(),
							'type'    => 'wysiwyg',
							'options' => array(
								'textarea_rows' => 6,
							)
						),
						array(
							'name'  => esc_html__( 'Offline Donations Settings Docs Link', 'walkthecounty' ),
							'id'    => 'offline_gateway_settings_docs_link',
							'url'   => esc_url( 'http://docs.walkthecountywp.com/offlinegateway' ),
							'title' => __( 'Offline Gateway Settings', 'walkthecounty' ),
							'type'  => 'walkthecounty_docs_link',
						),
						array(
							'type' => 'sectionend',
							'id'   => 'walkthecounty_title_gateway_settings_3',
						)
					);
					break;

				case 'gateways-settings':
					$settings = array(
						// Section 1: Gateways.
						array(
							'id'   => 'walkthecounty_title_gateway_settings_1',
							'type' => 'title'
						),
						array(
							'id'   => 'gateway_notice',
							'type' => 'gateway_notice',
						),
						array(
							'name'    => __( 'Test Mode', 'walkthecounty' ),
							'desc'    => __( 'While in test mode no live donations are processed. To fully use test mode, you must have a sandbox (test) account for the payment gateway you are testing.', 'walkthecounty' ),
							'id'      => 'test_mode',
							'type'    => 'radio_inline',
							'default' => 'disabled',
							'options' => array(
								'enabled'  => __( 'Enabled', 'walkthecounty' ),
								'disabled' => __( 'Disabled', 'walkthecounty' ),
							)
						),
						array(
							'name' => __( 'Enabled Gateways', 'walkthecounty' ),
							'desc' => __( 'Enable your payment gateway. Can be ordered by dragging.', 'walkthecounty' ),
							'id'   => 'gateways',
							'type' => 'enabled_gateways'
						),

						/**
						 * "Enabled Gateways" setting field contains gateways label setting but when you save gateway settings then label will not save
						 *  because this is not registered setting API and code will not recognize them.
						 *
						 * This setting will not render on admin setting screen but help internal code to recognize "gateways_label"  setting and add them to walkthecounty setting when save.
						 */
						array(
							'name' => __( 'Gateways Label', 'walkthecounty' ),
							'desc' => '',
							'id'   => 'gateways_label',
							'type' => 'gateways_label_hidden'
						),

						/**
						 * "Enabled Gateways" setting field contains default gateway setting but when you save gateway settings then this setting will not save
						 *  because this is not registered setting API and code will not recognize them.
						 *
						 * This setting will not render on admin setting screen but help internal code to recognize "default_gateway"  setting and add them to walkthecounty setting when save.
						 */
						array(
							'name' => __( 'Default Gateway', 'walkthecounty' ),
							'desc' => __( 'The gateway that will be selected by default.', 'walkthecounty' ),
							'id'   => 'default_gateway',
							'type' => 'default_gateway_hidden'
						),

						array(
							'name'  => __( 'Gateways Docs Link', 'walkthecounty' ),
							'id'    => 'gateway_settings_docs_link',
							'url'   => esc_url( 'http://docs.walkthecountywp.com/settings-gateways' ),
							'title' => __( 'Gateway Settings', 'walkthecounty' ),
							'type'  => 'walkthecounty_docs_link',
						),
						array(
							'id'   => 'walkthecounty_title_gateway_settings_1',
							'type' => 'sectionend'
						),
					);
					break;
			}

			/**
			 * Filter the payment gateways settings.
			 * Backward compatibility: Please do not use this filter. This filter is deprecated in 1.8
			 */
			$settings = apply_filters( 'walkthecounty_settings_gateways', $settings );

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
				'gateways-settings' => __( 'Gateways', 'walkthecounty' ),
				'paypal-standard'   => __( 'PayPal Standard', 'walkthecounty' ),
				'offline-donations' => __( 'Offline Donations', 'walkthecounty' )
			);

			return apply_filters( 'walkthecounty_get_sections_' . $this->id, $sections );
		}


		/**
		 * Render Gateway Notice
		 *
		 * @since  2.3.0
		 * @access public
		 *
		 * @param $field
		 * @param $settings
		 */
		public function render_gateway_notice( $field, $settings ) {

			$gateways = walkthecounty_get_payment_gateways();

			// Only display notice if no active gateways are installed. Filter provided for developers to configure display.
			if ( apply_filters( 'walkthecounty_gateway_upsell_notice_conditions', count( $gateways ) <= 4 ) && ! walkthecounty_stripe_is_connected() ) {
				?>
				<div class="walkthecounty-gateways-notice">
					<div class="walkthecounty-gateways-cc-icon">
						<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="35" height="29" viewBox="0 0 35 29">
							<defs>
								<path id="credit-card-a" d="M32.0772569,3.88888889 L2.92274306,3.88888889 C1.30642361,3.88888889 0,5.1953125 0,6.80555556 L0,28.1944444 C0,29.8046875 1.30642361,31.1111111 2.92274306,31.1111111 L32.0772569,31.1111111 C33.6935764,31.1111111 35,29.8046875 35,28.1944444 L35,6.80555556 C35,5.1953125 33.6935764,3.88888889 32.0772569,3.88888889 Z M3.28732639,6.80555556 L31.7126736,6.80555556 C31.9131944,6.80555556 32.0772569,6.96961806 32.0772569,7.17013889 L32.0772569,9.72222222 L2.92274306,9.72222222 L2.92274306,7.17013889 C2.92274306,6.96961806 3.08680556,6.80555556 3.28732639,6.80555556 Z M31.7126736,28.1944444 L3.28732639,28.1944444 C3.08680556,28.1944444 2.92274306,28.0303819 2.92274306,27.8298611 L2.92274306,17.5 L32.0772569,17.5 L32.0772569,27.8298611 C32.0772569,28.0303819 31.9131944,28.1944444 31.7126736,28.1944444 Z M11.6666667,22.1180556 L11.6666667,24.5486111 C11.6666667,24.9496528 11.3385417,25.2777778 10.9375,25.2777778 L6.5625,25.2777778 C6.16145833,25.2777778 5.83333333,24.9496528 5.83333333,24.5486111 L5.83333333,22.1180556 C5.83333333,21.7170139 6.16145833,21.3888889 6.5625,21.3888889 L10.9375,21.3888889 C11.3385417,21.3888889 11.6666667,21.7170139 11.6666667,22.1180556 Z M23.3333333,22.1180556 L23.3333333,24.5486111 C23.3333333,24.9496528 23.0052083,25.2777778 22.6041667,25.2777778 L14.3402778,25.2777778 C13.9392361,25.2777778 13.6111111,24.9496528 13.6111111,24.5486111 L13.6111111,22.1180556 C13.6111111,21.7170139 13.9392361,21.3888889 14.3402778,21.3888889 L22.6041667,21.3888889 C23.0052083,21.3888889 23.3333333,21.7170139 23.3333333,22.1180556 Z"/>
							</defs>
							<g fill="none" fill-rule="evenodd" opacity=".341" transform="translate(0 -3)">
								<mask id="credit-card-b" fill="#fff">
									<use xlink:href="#credit-card-a"/>
								</mask>
								<g fill="#242A42" mask="url(#credit-card-b)">
									<rect width="35" height="35"/>
								</g>
							</g>
						</svg>
					</div>

					<p class="walkthecounty-gateways-notice-title">
						<strong>
							<?php esc_html_e( 'Want to accept credit card donations directly on your website?', 'walkthecounty' ); ?>
						</strong>
					</p>

					<p class="walkthecounty-gateways-notice-message">
						<?php
						printf(
							__( 'Activate the free Stripe payment gateway %1$s or a premium gateway like <a href="%2$s" target="_blank">PayPal Pro</a>, <a href="%3$s" target="_blank">Authorize.net</a>, or <a href="%4$s" target="_blank">Stripe Premium</a> for no added fees and priority support.', 'walkthecounty' ),
							WalkTheCounty()->tooltips->render_help( __( 'The free version of Stripe includes an additional 2% processing fee in addition to Stripe\'s normal fees for one-time donations. This ensures we can fully support the plugin for the future. Upgrade to the premium Stripe add-on for no added fees.', 'walkthecounty' ) ),
							'https://walkthecountywp.com/addons/paypal-pro-gateway/?utm_source=WP%20Admin%20%3E%20Donations%20%3E%20Settings%20%3E%20Gateways&utm_medium=banner',
							'https://walkthecountywp.com/addons/authorize-net-gateway/?utm_source=WP%20Admin%20%3E%20Donations%20%3E%20Settings%20%3E%20Gateways&utm_medium=banner',
							'https://walkthecountywp.com/addons/stripe-gateway/?utm_source=WP%20Admin%20%3E%20Donations%20%3E%20Settings%20%3E%20Gateways&utm_medium=banner'
						);
						?>
					</p>

					<div class="walkthecounty-gateways-notice-button">
						<?php echo walkthecounty_stripe_connect_button(); ?>
						<a href="https://walkthecountywp.com/addons/category/payment-gateways/?utm_source=WP%20Admin%20%3E%20Donations%20%3E%20Settings%20%3E%20Gateways&utm_medium=banner" target="_blank" class="walkthecounty-view-gateways-btn button">
							<?php esc_html_e( 'View Premium Gateways', 'walkthecounty' ); ?>
						</a>
					</div>
				</div>
				<?php
			}
		}

		/**
		 * Render enabled gateways
		 *
		 * @since  2.0.5
		 * @access public
		 *
		 * @param $field
		 * @param $settings
		 */
		public function render_enabled_gateways( $field, $settings ) {
			$id              = $field['id'];
			$gateways        = walkthecounty_get_ordered_payment_gateways( walkthecounty_get_payment_gateways() );
			$gateways_label  = walkthecounty_get_option( 'gateways_label', array() );
			$default_gateway = walkthecounty_get_option( 'default_gateway', current( array_keys( $gateways ) ) );

			ob_start();

			echo '<div class="gateway-enabled-wrap">';

			echo '<div class="gateway-enabled-settings-title">';
			printf(
				'
						<span></span>
						<span>%1$s</span>
						<span>%2$s</span>
						<span>%3$s</span>
						<span>%4$s</span>
						',
				__( 'Gateway', 'walkthecounty' ),
				__( 'Label', 'walkthecounty' ),
				__( 'Default', 'walkthecounty' ),
				__( 'Enabled', 'walkthecounty' )
			);
			echo '</div>';

			echo '<ul class="walkthecounty-checklist-fields walkthecounty-payment-gatways-list">';
			foreach ( $gateways as $key => $option ) :
				$enabled = null;
				if ( is_array( $settings ) && array_key_exists( $key, $settings ) ) {
					$enabled = '1';
				}

				echo '<li>';
				printf( '<span class="walkthecounty-drag-handle"><span class="dashicons dashicons-menu"></span></span>' );
				printf(
					'<span class="admin-label">%1$s %2$s</span>',
					esc_html( $option['admin_label'] ),
					! empty( $option['admin_tooltip'] ) ? WalkTheCounty()->tooltips->render_help( esc_attr( $option['admin_tooltip'] ) ) : ''
				);

				$label = '';
				if ( ! empty( $gateways_label[ $key ] ) ) {
					$label = $gateways_label[ $key ];
				}

				printf(
					'<input class="checkout-label" type="text" id="%1$s[%2$s]" name="%1$s[%2$s]" value="%3$s" placeholder="%4$s"/>',
					'gateways_label',
					esc_attr( $key ),
					esc_html( $label ),
					esc_html( $option['checkout_label'] )
				);

				printf(
					'<input class="gateways-radio" type="radio" name="%1$s" value="%2$s" %3$s %4$s>',
					'default_gateway',
					$key,
					checked( $key, $default_gateway, false ),
					disabled( null, $enabled, false )
				);

				printf(
					'<input class="gateways-checkbox" name="%1$s[%2$s]" id="%1$s[%2$s]" type="checkbox" value="1" %3$s data-payment-gateway="%4$s"/>',
					esc_attr( $id ),
					esc_attr( $key ),
					checked( '1', $enabled, false ),
					esc_html( $option['admin_label'] )
				);
				echo '</li>';
			endforeach;
			echo '</ul>';

			echo '</div>'; // end gateway-enabled-wrap.

			printf(
				'<tr><th>%1$s</th><td>%2$s</td></tr>',
				$field['title'],
				ob_get_clean()
			);
		}
	}

endif;

return new WalkTheCounty_Settings_Gateways();
