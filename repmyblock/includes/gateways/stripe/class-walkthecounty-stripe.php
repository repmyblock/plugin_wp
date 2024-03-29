<?php
/**
 * WalkTheCounty - Stripe Core
 *
 * @since 2.5.0
 *
 * @package    WalkTheCounty
 * @subpackage Stripe Core
 * @copyright  Copyright (c) 2019, WalkTheCountyWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 */

// Exit, if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WalkTheCounty_Stripe' ) ) {

	/**
	 * Class WalkTheCounty_Stripe
	 */
	class WalkTheCounty_Stripe {

		/**
		 * WalkTheCounty_Stripe() constructor.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @return void
		 */
		public function __construct() {

			add_filter( 'walkthecounty_payment_gateways', array( $this, 'register_gateway' ) );

			/**
			 * Using hardcoded constant for backward compatibility of WalkTheCounty 2.5.0 with Recurring 1.8.13 when Stripe Premium is not active.
			 *
			 * This code will handle extreme rare scenario.
			 *
			 * @since 2.5.0
			 *
			 * @todo Remove this constant declaration after 2-3 WalkTheCounty core minor releases.
			 */
			if ( ! defined( 'WALKTHECOUNTY_STRIPE_BASENAME' ) ) {
				define( 'WALKTHECOUNTY_STRIPE_BASENAME', 'walkthecounty-stripe/walkthecounty-stripe.php' );
			}

			$this->includes();
		}

		/**
		 * This function is used to include the related Stripe core files.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @return void
		 */
		public function includes() {

			// Include files which are necessary to load in admin but not in context of `is_admin`.
			$this->include_admin_files();

			// Load files which are necessary for front as well as admin end.
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/gateways/stripe/includes/walkthecounty-stripe-helpers.php';

			// Bailout, if any of the Stripe gateways are not active.
			if ( ! walkthecounty_stripe_is_any_payment_method_active() ) {

				// Hardcoded recurring plugin basename to show notice even when recurring addon is deactivated.
				$recurring_plugin_basename = 'walkthecounty-recurring/walkthecounty-recurring.php';
				$recurring_file_path       = WP_CONTENT_DIR . '/plugins/' . $recurring_plugin_basename;

				// If recurring donations add-on exists.
				if ( file_exists( $recurring_file_path ) ) {

					// If `get_plugin_data` fn not exists then include the file.
					if ( ! function_exists( 'get_plugin_data' ) ) {
						require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
					}

					$recurring_plugin_data = get_plugin_data($recurring_file_path);

					// Avoid fatal error for smooth update for customers.
					if (
						isset( $recurring_plugin_data['Version'] ) &&
						version_compare( '1.9.3', $recurring_plugin_data['Version'], '>=' )
					) {

						// Load Stripe SDK.
						walkthecounty_stripe_load_stripe_sdk();

						// Include frontend files.
						$this->include_frontend_files();

						add_action('admin_notices', function() {

							// Register error notice.
							WalkTheCounty()->notices->register_notice(
								array(
									'id'          => 'walkthecounty-recurring-fatal-error',
									'type'        => 'error',
									'description' => sprintf(
										__( '<strong>Action Needed:</strong> Please update the Recurring Donations add-on to version <strong>1.9.4+</strong> in order to be compatible with WalkTheCountyWP <strong>2.5.5+</strong>. If you are experiencing any issues please rollback WalkTheCountyWP to 2.5.4 or below using the <a href="%s" target="_blank">WP Rollback</a> plugin and <a href="%s" target="_blank">contact support</a> for prompt assistance.', 'walkthecounty'),
										'https://wordpress.org/plugins/wp-rollback/',
										'https://walkthecountywp.com/support/'
									),
									'show'        => true,
								)
							);
						});
					}
				}

				return;
			}

			// Load Stripe SDK.
			walkthecounty_stripe_load_stripe_sdk();

			// Include frontend files.
			$this->include_frontend_files();
		}

		/**
		 * This function is used to include admin files.
		 *
		 * @since  2.6.0
		 * @access public
		 *
		 * @return void
		 */
		public function include_admin_files() {
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/gateways/stripe/includes/admin/admin-helpers.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/gateways/stripe/includes/admin/admin-actions.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/gateways/stripe/includes/admin/admin-filters.php';

			// Load these files when accessed from admin.
			if ( is_admin() ) {
				require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/gateways/stripe/includes/admin/class-walkthecounty-stripe-admin-settings.php';
				require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/gateways/stripe/includes/admin/class-walkthecounty-stripe-logs.php';
			}
		}

		/**
		 * This function will be used to load frontend files.
		 *
		 * @since  2.6.0
		 * @access public
		 *
		 * @return void
		 */
		public function include_frontend_files() {

			// General.
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/gateways/stripe/includes/actions.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/gateways/stripe/includes/walkthecounty-stripe-scripts.php';

			// Classes.
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/gateways/stripe/includes/class-walkthecounty-stripe-logger.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/gateways/stripe/includes/class-walkthecounty-stripe-invoice.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/gateways/stripe/includes/class-walkthecounty-stripe-customer.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/gateways/stripe/includes/class-walkthecounty-stripe-payment-intent.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/gateways/stripe/includes/class-walkthecounty-stripe-payment-method.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/gateways/stripe/includes/class-walkthecounty-stripe-checkout-session.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/gateways/stripe/includes/class-walkthecounty-stripe-gateway.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/gateways/stripe/includes/class-walkthecounty-stripe-webhooks.php';

			// Payment Methods.
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/gateways/stripe/includes/payment-methods/class-walkthecounty-stripe-card.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/gateways/stripe/includes/payment-methods/class-walkthecounty-stripe-checkout.php';

			// Deprecations.
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/gateways/stripe/includes/deprecated/deprecated-functions.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/gateways/stripe/includes/deprecated/deprecated-filters.php';
		}

		/**
		 * Register the payment methods supported by Stripe.
		 *
		 * @access public
		 * @since  2.5.0
		 *
		 * @param array $gateways List of registered gateways.
		 *
		 * @return array
		 */
		public function register_gateway( $gateways ) {

			// Stripe - On page credit card.
			$gateways['stripe'] = array(
				'admin_label'    => __( 'Stripe - Credit Card', 'walkthecounty' ),
				'checkout_label' => __( 'Credit Card', 'walkthecounty' ),
			);

			// Stripe - Off page credit card (also known as Checkout).
			$gateways['stripe_checkout'] = array(
				'admin_label'    => __( 'Stripe - Checkout', 'walkthecounty' ),
				'checkout_label' => __( 'Credit Card', 'walkthecounty' ),
			);

			return $gateways;
		}
	}
}

new WalkTheCounty_Stripe();
