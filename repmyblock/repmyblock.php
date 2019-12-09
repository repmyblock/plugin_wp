<?php
/**
 * Plugin Name: Repmyblock
 * Plugin URI: https://RepMyBlock.net
 * Description: Plugin to connect the Rep My Block tools to a Word Press website.
 * Author: WalkTheCounty 
 * Author URI: https://WalkTheCounty.org/
 * Version: 1.0
 * Text Domain: repmyblock
 * Domain Path: /languages
 *
 * WalkTheCounty is free software: you can redistribute it and/or modify as you see fit
 *
 * WalkTheCounty is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * A Tribute to Open Source:
 *
 * "Open source software is software that can be freely used, changed, and shared (in modified or unmodified form) by anyone. Open
 * source software is made by many people, and distributed under licenses that comply with the Open Source Definition."
 *
 * -- The Open Source Initiative
 *
 * WalkTheCounty is a tribute to the spirit and philosophy of Open Source. We at WalkTheCounty gladly embrace the Open Source philosophy both
 * in how WalkTheCounty itself was developed, and how we hope to see others build more from our code base.
 *
 * WalkTheBlock would not have been possible without the tireless efforts of WordPress, WalkTheCountyWP Team and the surrounding Open Source projects and their talented developers. Thank you all for your contribution to WordPress.
 *
 * - The WalkTheCounty Team
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WalkTheCounty' ) ) :

	/**
	 * Main WalkTheCounty Class
	 *
	 * @since 1.0
	 */
	final class WalkTheCounty {

		/** Singleton *************************************************************/

		/**
		 * WalkTheCounty Instance
		 *
		 * @since  1.0
		 * @access private
		 *
		 * @var    WalkTheCounty() The one true WalkTheCounty
		 */
		protected static $_instance;

		/**
		 * WalkTheCounty Roles Object
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @var    WalkTheCounty_Roles object
		 */
		public $roles;

		/**
		 * WalkTheCounty Settings Object
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @var    WalkTheCounty_Admin_Settings object
		 */
		public $WalkTheCounty_settings;

		/**
		 * WalkTheCounty Session Object
		 *
		 * This holds donation data for user's session.
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @var    WalkTheCounty_Session object
		 */
		public $session;

		/**
		 * WalkTheCounty Session DB Object
		 *
		 * This holds donation data for user's session.
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @var    WalkTheCounty_DB_Sessions object
		 */
		public $session_db;

		/**
		 * WalkTheCounty HTML Element Helper Object
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @var    WalkTheCounty_HTML_Elements object
		 */
		public $html;

		/**
		 * WalkTheCounty Emails Object
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @var    WalkTheCounty_Emails object
		 */
		public $emails;

		/**
		 * WalkTheCounty Email Template Tags Object
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @var    WalkTheCounty_Email_Template_Tags object
		 */
		public $email_tags;

		/**
		 * WalkTheCounty Donors DB Object
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @var    WalkTheCounty_DB_Donors object
		 */
		public $donors;

		/**
		 * WalkTheCounty Donor meta DB Object
		 *
		 * @since  1.6
		 * @access public
		 *
		 * @var    WalkTheCounty_DB_Donor_Meta object
		 */
		public $donor_meta;

		/**
		 * WalkTheCounty Sequential Donation DB Object
		 *
		 * @since  2.1.0
		 * @access public
		 *
		 * @var    WalkTheCounty_DB_Sequential_Ordering object
		 */
		public $sequential_donation_db;

		/**
		 * WalkTheCounty API Object
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @var    WalkTheCounty_API object
		 */
		public $api;

		/**
		 * WalkTheCounty Template Loader Object
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @var    WalkTheCounty_Template_Loader object
		 */
		public $template_loader;

		/**
		 * WalkTheCounty No Login Object
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @var    WalkTheCounty_Email_Access object
		 */
		public $email_access;

		/**
		 * WalkTheCounty_tooltips Object
		 *
		 * @since  1.8.9
		 * @access public
		 *
		 * @var    WalkTheCounty_Tooltips object
		 */
		public $tooltips;

		/**
		 * WalkTheCounty notices Object
		 *
		 * @var    WalkTheCounty_Notices $notices
		 */
		public $notices;


		/**
		 * WalkTheCounty logging Object
		 *
		 * @var    WalkTheCounty_Logging $logs
		 */
		public $logs;

		/**
		 * WalkTheCounty log db Object
		 *
		 * @var    WalkTheCounty_DB_Logs $log_db
		 */
		public $log_db;

		/**
		 * WalkTheCounty log meta db Object
		 *
		 * @var    WalkTheCounty_DB_Log_Meta $logmeta_db
		 */
		public $logmeta_db;

		/**
		 * WalkTheCounty payment Object
		 *
		 * @var    WalkTheCounty_DB_Payment_Meta $payment_meta
		 */
		public $payment_meta;

		/**
		 * WalkTheCounty form Object
		 *
		 * @var    WalkTheCounty_DB_Form_Meta $form_meta
		 */
		public $form_meta;

		/**
		 * WalkTheCounty form Object
		 *
		 * @var    WalkTheCounty_Async_Process $async_process
		 */
		public $async_process;

		/**
		 * WalkTheCounty scripts Object.
		 *
		 * @var WalkTheCounty_Scripts
		 */
		public $scripts;

		/**
		 * WalkTheCounty_Seq_Donation_Number Object.
		 *
		 * @var WalkTheCounty_Sequential_Donation_Number
		 */
		public $seq_donation_number;

		/**
		 * WalkTheCounty_Comment Object
		 *
		 * @var WalkTheCounty_Comment
		 */
		public $comment;

		/**
		 * WalkTheCounty_Stripe Object.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @var WalkTheCounty_Stripe
		 */
		public $stripe;

		/**
		 * Main WalkTheCounty Instance
		 *
		 * Ensures that only one instance of WalkTheCounty exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since     1.0
		 * @access    public
		 *
		 * @static
		 * @see       WalkTheCounty()
		 *
		 * @return    WalkTheCounty
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * WalkTheCounty Constructor.
		 */
		public function __construct() {
			// PHP version
			if ( ! defined( 'WALKTHECOUNTY_REQUIRED_PHP_VERSION' ) ) {
				define( 'WALKTHECOUNTY_REQUIRED_PHP_VERSION', '5.4.0' );
			}

			// Bailout: Need minimum php version to load plugin.
			if ( function_exists( 'phpversion' ) && version_compare( WALKTHECOUNTY_REQUIRED_PHP_VERSION, phpversion(), '>' ) ) {
				add_action( 'admin_notices', array( $this, 'minimum_phpversion_notice' ) );

				return;
			}

			// Add compatibility notice for recurring and stripe support with WalkTheCounty 2.5.0.
			add_action( 'admin_notices', array( $this, 'display_old_recurring_compatibility_notice' ) );

			$this->setup_constants();
			$this->includes();
			$this->init_hooks();

			do_action( 'walkthecounty_loaded' );
		}

		/**
		 * Hook into actions and filters.
		 *
		 * @since  1.8.9
		 */
		private function init_hooks() {
			register_activation_hook( WALKTHECOUNTY_PLUGIN_FILE, 'walkthecounty_install' );
			add_action( 'plugins_loaded', array( $this, 'init' ), 0 );
		}


		/**
		 * Init WalkTheCounty when WordPress Initializes.
		 *
		 * @since 1.8.9
		 */
		public function init() {

			/**
			 * Fires before the WalkTheCounty core is initialized.
			 *
			 * @since 1.8.9
			 */
			do_action( 'before_walkthecounty_init' );

			// Set up localization.
			$this->load_textdomain();

			$this->roles                  = new WalkTheCounty_Roles();
			$this->api                    = new WalkTheCounty_API();
			$this->walkthecounty_settings          = new WalkTheCounty_Admin_Settings();
			$this->emails                 = new WalkTheCounty_Emails();
			$this->email_tags             = new WalkTheCounty_Email_Template_Tags();
			$this->html                   = WalkTheCounty_HTML_Elements::get_instance();
			$this->donors                 = new WalkTheCounty_DB_Donors();
			$this->donor_meta             = new WalkTheCounty_DB_Donor_Meta();
			$this->tooltips               = new WalkTheCounty_Tooltips();
			$this->notices                = new WalkTheCounty_Notices();
			$this->payment_meta           = new WalkTheCounty_DB_Payment_Meta();
			$this->log_db                 = new WalkTheCounty_DB_Logs();
			$this->logmeta_db             = new WalkTheCounty_DB_Log_Meta();
			$this->logs                   = new WalkTheCounty_Logging();
			$this->form_meta              = new WalkTheCounty_DB_Form_Meta();
			$this->sequential_donation_db = new WalkTheCounty_DB_Sequential_Ordering();
			$this->async_process          = new WalkTheCounty_Async_Process();
			$this->scripts                = new WalkTheCounty_Scripts();
			$this->seq_donation_number    = WalkTheCounty_Sequential_Donation_Number::get_instance();
			$this->comment                = WalkTheCounty_Comment::get_instance();
			$this->session_db             = new WalkTheCounty_DB_Sessions();
			$this->session                = WalkTheCounty_Session::get_instance();

			/**
			 * Fire the action after WalkTheCounty core loads.
			 *
			 * @param WalkTheCounty class instance.
			 *
			 * @since 1.8.7
			 */
			do_action( 'walkthecounty_init', $this );

		}

		/**
		 * Throw error on object clone
		 *
		 * The whole idea of the singleton design pattern is that there is a single
		 * object, therefore we don't want the object to be cloned.
		 *
		 * @since  1.0
		 * @access protected
		 *
		 * @return void
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden.
			walkthecounty_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'walkthecounty' ), '1.0' );
		}

		/**
		 * Disable unserializing of the class
		 *
		 * @since  1.0
		 * @access protected
		 *
		 * @return void
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden.
			walkthecounty_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'walkthecounty' ), '1.0' );
		}

		/**
		 * Setup plugin constants
		 *
		 * @since  1.0
		 * @access private
		 *
		 * @return void
		 */
		private function setup_constants() {

			// Plugin version.
			if ( ! defined( 'WALKTHECOUNTY_VERSION' ) ) {
				define( 'WALKTHECOUNTY_VERSION', '1.0.0' );
			}

			// Plugin Root File.
			if ( ! defined( 'WALKTHECOUNTY_PLUGIN_FILE' ) ) {
				define( 'WALKTHECOUNTY_PLUGIN_FILE', __FILE__ );
			}

			// Plugin Folder Path.
			if ( ! defined( 'WALKTHECOUNTY_PLUGIN_DIR' ) ) {
				define( 'WALKTHECOUNTY_PLUGIN_DIR', plugin_dir_path( WALKTHECOUNTY_PLUGIN_FILE ) );
			}

			// Plugin Folder URL.
			if ( ! defined( 'WALKTHECOUNTY_PLUGIN_URL' ) ) {
				define( 'WALKTHECOUNTY_PLUGIN_URL', plugin_dir_url( WALKTHECOUNTY_PLUGIN_FILE ) );
			}

			// Plugin Basename aka: "walkthecounty/walkthecounty.php".
			if ( ! defined( 'WALKTHECOUNTY_PLUGIN_BASENAME' ) ) {
				define( 'WALKTHECOUNTY_PLUGIN_BASENAME', plugin_basename( WALKTHECOUNTY_PLUGIN_FILE ) );
			}

			// Make sure CAL_GREGORIAN is defined.
			if ( ! defined( 'CAL_GREGORIAN' ) ) {
				define( 'CAL_GREGORIAN', 1 );
			}
		}

		/**
		 * Include required files
		 *
		 * @since  1.0
		 * @access private
		 *
		 * @return void
		 */
		private function includes() {
			global $walkthecounty_options;

			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/class-walkthecounty-cache-setting.php';


			/**
			 * Load libraries.
			 */
			if ( ! class_exists( 'WP_Async_Request' ) ) {
				include_once( WALKTHECOUNTY_PLUGIN_DIR . 'includes/libraries/wp-async-request.php' );
			}

			if ( ! class_exists( 'WP_Background_Process' ) ) {
				include_once( WALKTHECOUNTY_PLUGIN_DIR . 'includes/libraries/wp-background-process.php' );
			}

			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/setting-functions.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/country-functions.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/template-functions.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/misc-functions.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/forms/functions.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/ajax-functions.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/currency-functions.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/price-functions.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/user-functions.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/donors/frontend-donor-functions.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/payments/functions.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/gateways/functions.php';

			/**
			 * Load plugin files
			 */
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/class-admin-settings.php';
			$walkthecounty_options = walkthecounty_get_settings();

			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/class-walkthecounty-cron.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/class-walkthecounty-async-process.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/class-walkthecounty-cache.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/post-types.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/filters.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/api/class-walkthecounty-api.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/api/class-walkthecounty-api-v2.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/class-walkthecounty-tooltips.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/class-notices.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/class-walkthecounty-translation.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/class-walkthecounty-license-handler.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/class-walkthecounty-html-elements.php';


			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/class-walkthecounty-scripts.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/class-walkthecounty-roles.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/class-walkthecounty-donate-form.php';

			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/database/class-walkthecounty-db.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/database/class-walkthecounty-db-meta.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/database/class-walkthecounty-db-comments.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/database/class-walkthecounty-db-comments-meta.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/database/class-walkthecounty-db-donors.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/database/class-walkthecounty-db-donor-meta.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/database/class-walkthecounty-db-form-meta.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/database/class-walkthecounty-db-sequential-ordering.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/database/class-walkthecounty-db-logs.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/database/class-walkthecounty-db-logs-meta.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/database/class-walkthecounty-db-sessions.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/database/class-walkthecounty-db-payment-meta.php';

			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/class-walkthecounty-donor.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/class-walkthecounty-stats.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/class-walkthecounty-session.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/class-walkthecounty-logging.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/class-walkthecounty-comment.php';

			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/class-walkthecounty-donor-wall-widget.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/forms/widget.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/forms/class-walkthecounty-forms-query.php';


			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/forms/template.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/shortcodes.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/formatting.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/error-tracking.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/login-register.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/plugin-compatibility.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/deprecated/deprecated-classes.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/deprecated/deprecated-functions.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/deprecated/deprecated-actions.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/deprecated/deprecated-filters.php';

			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/process-donation.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/payments/backward-compatibility.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/payments/actions.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/payments/class-payment-stats.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/payments/class-payments-query.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/payments/class-walkthecounty-payment.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/payments/class-walkthecounty-sequential-donation-number.php';

			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/gateways/actions.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/gateways/paypal-standard.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/gateways/offline-donations.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/gateways/manual.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/emails/class-walkthecounty-emails.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/emails/class-walkthecounty-email-tags.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/emails/class-email-notifications.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/emails/functions.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/emails/template.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/emails/actions.php';

			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/donors/class-walkthecounty-donors-query.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/donors/class-walkthecounty-donor-wall.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/donors/class-walkthecounty-donor-stats.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/donors/backward-compatibility.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/donors/actions.php';

			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/upgrades/class-walkthecounty-updates.php';

			require_once WALKTHECOUNTY_PLUGIN_DIR . 'blocks/load.php';

			if ( defined( 'WP_CLI' ) && WP_CLI ) {
				require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/class-walkthecounty-cli-commands.php';
			}

			// Load file for frontend
			if( $this->is_request('frontend' ) ) {
				require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/frontend/class-walkthecounty-frontend.php';
			}

			if ( $this->is_request( 'admin' ) || $this->is_request( 'wpcli' ) ) {
				require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/class-walkthecounty-admin.php';
			}// End if().

			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/actions.php';
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/install.php';

			// This conditional check will add backward compatibility to older Stripe versions (i.e. < 2.2.0) when used with WalkTheCounty 2.5.0.
			if (
				! defined( 'WALKTHECOUNTY_STRIPE_VERSION' ) ||
				(
					defined( 'WALKTHECOUNTY_STRIPE_VERSION' ) &&
					version_compare( WALKTHECOUNTY_STRIPE_VERSION, '2.2.0', '>=' )
				)
			) {
				require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/gateways/stripe/class-walkthecounty-stripe.php';
			}

		}

		/**
		 * Loads the plugin language files.
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @return void
		 */
		public function load_textdomain() {

			// Set filter for WalkTheCounty's languages directory
			$walkthecounty_lang_dir = dirname( plugin_basename( WALKTHECOUNTY_PLUGIN_FILE ) ) . '/languages/';
			$walkthecounty_lang_dir = apply_filters( 'walkthecounty_languages_directory', $walkthecounty_lang_dir );

			// Traditional WordPress plugin locale filter.
			$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
			$locale = apply_filters( 'plugin_locale', $locale, 'walkthecounty' );

			unload_textdomain( 'walkthecounty' );
			load_textdomain( 'walkthecounty', WP_LANG_DIR . '/walkthecounty/walkthecounty-' . $locale . '.mo' );
			load_plugin_textdomain( 'walkthecounty', false, $walkthecounty_lang_dir );

		}


		/**
		 *  Show minimum PHP version notice.
		 *
		 * @since  1.8.12
		 * @access public
		 */
		public function minimum_phpversion_notice() {
			// Bailout.
			if ( ! is_admin() ) {
				return;
			}

			$notice_desc  = '<p><strong>' . __( 'Your site could be faster and more secure with a newer PHP version.', 'walkthecounty' ) . '</strong></p>';
			$notice_desc .= '<p>' . __( 'Hey, we\'ve noticed that you\'re running an outdated version of PHP. PHP is the programming language that WordPress and WalkTheCountyWP are built on. The version that is currently used for your site is no longer supported. Newer versions of PHP are both faster and more secure. In fact, your version of PHP no longer receives security updates, which is why we\'re sending you this notice.', 'walkthecounty' ) . '</p>';
			$notice_desc .= '<p>' . __( 'Hosts have the ability to update your PHP version, but sometimes they don\'t dare to do that because they\'re afraid they\'ll break your site.', 'walkthecounty' ) . '</p>';
			$notice_desc .= '<p><strong>' . __( 'To which version should I update?', 'walkthecounty' ) . '</strong></p>';
			$notice_desc .= '<p>' . __( 'You should update your PHP version to either 5.6 or to 7.0 or 7.1. On a normal WordPress site, switching to PHP 5.6 should never cause issues. We would however actually recommend you switch to PHP7. There are some plugins that are not ready for PHP7 though, so do some testing first. PHP7 is much faster than PHP 5.6. It\'s also the only PHP version still in active development and therefore the better option for your site in the long run.', 'walkthecounty' ) . '</p>';
			$notice_desc .= '<p><strong>' . __( 'Can\'t update? Ask your host!', 'walkthecounty' ) . '</strong></p>';
			$notice_desc .= '<p>' . sprintf( __( 'If you cannot upgrade your PHP version yourself, you can send an email to your host. If they don\'t want to upgrade your PHP version, we would suggest you switch hosts. Have a look at one of the recommended %1$sWordPress hosting partners%2$s.', 'walkthecounty' ), sprintf( '<a href="%1$s" target="_blank">', esc_url( 'https://wordpress.org/hosting/' ) ), '</a>' ) . '</p>';

			echo sprintf(
				'<div class="notice notice-error">%1$s</div>',
				wp_kses_post( $notice_desc )
			);
		}

		/**
		 * Display compatibility notice for WalkTheCounty 2.5.0 and Recurring 1.8.13 when Stripe premium is not active.
		 *
		 * @since 2.5.0
		 *
		 * @return void
		 */
		public function display_old_recurring_compatibility_notice() {

			// Show notice, if incompatibility found.
			if (
				defined( 'WALKTHECOUNTY_RECURRING_VERSION' )
				&& version_compare( WALKTHECOUNTY_RECURRING_VERSION, '1.9.0', '<' )
				&& defined( 'WALKTHECOUNTY_STRIPE_VERSION' )
				&& version_compare( WALKTHECOUNTY_STRIPE_VERSION, '2.2.0', '<' )
			) {

				$message = sprintf(
					__( '<strong>Attention:</strong> WalkTheCountyWP 2.5.0+ requires the latest version of the Recurring Donations add-on to process payments properly with Stripe. Please update to the latest version add-on to resolve compatibility issues. If your license is active, you should see the update available in WordPress. Otherwise, you can access the latest version by <a href="%1$s" target="_blank">logging into your account</a> and visiting <a href="%1$s" target="_blank">your downloads</a> page on the WalkTheCountyWP website.', 'walkthecounty' ),
					esc_url( 'https://walkthecountywp.com/wp-login.php' ),
					esc_url( 'https://walkthecountywp.com/my-account/#tab_downloads' )
				);

				WalkTheCounty()->notices->register_notice(
					array(
						'id'               => 'walkthecounty-compatibility-with-old-recurring',
						'description'      => $message,
						'dismissible_type' => 'user',
						'dismiss_interval' => 'shortly',
					)
				);
			}

		}

		/**
		 * What type of request is this?
		 *
		 * @since 2.4.0
		 *
		 * @param  string $type admin, ajax, cron or frontend.
		 * @return bool
		 */
		private function is_request( $type ) {
			switch ( $type ) {
				case 'admin':
					return is_admin();
				case 'ajax':
					return defined( 'DOING_AJAX' );
				case 'cron':
					return defined( 'DOING_CRON' );
				case 'frontend':
					return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) && ! defined( 'REST_REQUEST' );
				case 'wpcli':
					return defined( 'WP_CLI' ) && WP_CLI;
			}
		}

	}

endif; // End if class_exists check


/**
 * Start WalkTheCounty
 *
 * The main function responsible for returning the one true WalkTheCounty instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $walkthecounty = WalkTheCounty(); ?>
 *
 * @since 1.0
 * @return object|WalkTheCounty
 */
function WalkTheCounty() {
	return WalkTheCounty::instance();
}

WalkTheCounty();
