<?php

/**
 * Loads the plugin's scripts and styles.
 *
 * Registers and enqueues plugin styles and scripts. Asset versions are based
 * on the current plugin version.
 *
 * All script and style handles should be registered in this class even if they
 * are enqueued dynamically by other classes.
 *
 * @since 2.1.0
 */
class WalkTheCounty_Scripts {

	/**
	 * Whether RTL or not.
	 *
	 * @since  2.1.0
	 * @var    string
	 * @access private
	 */
	private $direction;

	/**
	 * Whether scripts should be loaded in the footer or not.
	 *
	 * @since  2.1.0
	 * @var    bool
	 * @access private
	 */
	private static $scripts_footer;

	/**
	 * Instantiates the Assets class.
	 *
	 * @since 2.1.0
	 */
	public function __construct() {
		$this->direction      = ( is_rtl() || isset( $_GET['d'] ) && 'rtl' === $_GET['d'] ) ? '.rtl' : '';
		self::$scripts_footer = walkthecounty_is_setting_enabled( walkthecounty_get_option( 'scripts_footer' ) ) ? true : false;
		$this->init();
	}

	/**
	 * Fires off hooks to register assets in WordPress.
	 *
	 * @since 2.1.0
	 */
	public function init() {

		add_action( 'admin_enqueue_scripts', array( $this, 'register_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );

		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ) );
			add_action( 'enqueue_block_editor_assets', array( $this, 'gutenberg_admin_scripts' ) );
			add_action( 'admin_head', array( $this, 'global_admin_head' ) );

		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'public_enqueue_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'public_enqueue_scripts' ) );
		}
	}

	/**
	 * Register plugin script.
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @param string $handle Script Handle.
	 * @param string $src    Script Source URL.
	 * @param array  $dep    Dependency on a script.
	 * @param mixed  $ver    Script Version
	 */
	public static function register_script( $handle, $src, $dep = array(), $ver = false ) {
		wp_register_script( $handle, $src, $dep, $ver, self::$scripts_footer );
	}

	/**
	 * Registers all plugin styles.
	 *
	 * @since 2.1.0
	 */
	public function register_styles() {

		// WP-admin.
		wp_register_style( 'walkthecounty-admin-styles', WALKTHECOUNTY_PLUGIN_URL . 'assets/dist/css/admin' . $this->direction . '.css', array(), WALKTHECOUNTY_VERSION );

		// WP-admin: plugin page.
		wp_register_style(
			'plugin-deactivation-survey-css',
			WALKTHECOUNTY_PLUGIN_URL . 'assets/dist/css/plugin-deactivation-survey.css',
			array(),
			WALKTHECOUNTY_VERSION
		);

		// Frontend.
		if ( walkthecounty_is_setting_enabled( walkthecounty_get_option( 'css' ) ) ) {
			wp_register_style( 'walkthecounty-styles', $this->get_frontend_stylesheet_uri(), array(), WALKTHECOUNTY_VERSION, 'all' );
		}
	}

	/**
	 * Registers all plugin scripts.
	 *
	 * @since 2.1.0
	 */
	public function register_scripts() {

		// WP-Admin.
		wp_register_script( 'walkthecounty-admin-scripts', WALKTHECOUNTY_PLUGIN_URL . 'assets/dist/js/admin.js', array(
			'jquery',
			'jquery-ui-datepicker',
			'wp-color-picker',
			'jquery-query',
		), WALKTHECOUNTY_VERSION );

		// WP-admin: plugin page.
		wp_register_script( 'plugin-deactivation-survey-js',
			WALKTHECOUNTY_PLUGIN_URL . 'assets/dist/js/plugin-deactivation-survey.js',
			array( 'jquery' ),
			WALKTHECOUNTY_VERSION,
			true
		);

		// WP-admin: add-ons page.
		wp_register_script( 'admin-add-ons-js',
			WALKTHECOUNTY_PLUGIN_URL . 'assets/dist/js/admin-add-ons.js',
			array( 'jquery' ),
			WALKTHECOUNTY_VERSION,
			true
		);

		// Frontend.
		wp_register_script( 'walkthecounty', WALKTHECOUNTY_PLUGIN_URL . 'assets/dist/js/walkthecounty.js', array( 'jquery' ), WALKTHECOUNTY_VERSION, self::$scripts_footer );
	}

	/**
	 * Enqueues admin styles.
	 *
	 * @since 2.1.0
	 *
	 * @param string $hook Page hook.
	 */
	public function admin_enqueue_styles( $hook ) {
		// WalkTheCounty Admin Only.
		if ( ! apply_filters( 'walkthecounty_load_admin_styles', walkthecounty_is_admin_page(), $hook ) ) {
			return;
		}

		// WalkTheCounty enqueues.
		wp_enqueue_style( 'walkthecounty-admin-styles' );
		wp_enqueue_style( 'walkthecounty-admin-bar-notification' );

		// WP Core enqueues.
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'thickbox' ); // @TODO remove once we have modal API.

	}

	/**
	 * Enqueues admin scripts.
	 *
	 * @since 2.1.0
	 *
	 * @param string $hook Page hook.
	 */
	public function admin_enqueue_scripts( $hook ) {
		global $pagenow;

		// Plugin page script
		if ( 'plugins.php' === $pagenow ) {
			$this->plugin_enqueue_scripts();
		}

		// WalkTheCounty Admin Only.
		if ( ! apply_filters( 'walkthecounty_load_admin_scripts', walkthecounty_is_admin_page(), $hook ) ) {
			return;
		}

		// WP Scripts.
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_media();

		// WalkTheCounty admin scripts.
		wp_enqueue_script( 'walkthecounty-admin-scripts' );

		// Localize admin scripts
		$this->admin_localize_scripts();

		if ( WalkTheCounty_Admin_Settings::is_setting_page( 'licenses'  ) ) {
			wp_enqueue_script( 'admin-add-ons-js' );
			$localized_data = array(
				'notices' => array(
					'invalid_license' => __( 'Sorry, you entered an invalid key.', 'walkthecounty' ),
					'download_file'   => __( 'Success! You have activated your license key and are receiving updates and priority support. <a href="{link}">Click here</a> to download your add-on.', 'walkthecounty' ),
					'addon_activated'   => __( '{pluginName} add-on activated successfully.', 'walkthecounty' ),
					'addon_activation_error'   => __( 'The add-on did not activate successfully.', 'walkthecounty' ),
				),
			);

			wp_localize_script( 'admin-add-ons-js', 'walkthecounty_addon_var', $localized_data );
		}
	}

	/**
	 * Load admin plugin page related scripts, styles and localize param.
	 *
	 * @since  2.2.0
	 * @access private
	 */
	private function plugin_enqueue_scripts() {
		wp_enqueue_style( 'plugin-deactivation-survey-css' );
		wp_enqueue_script( 'plugin-deactivation-survey-js' );

		$localized_data = array(
			'nonce'                           => wp_create_nonce( 'deactivation_survey_nonce' ),
			'cancel'                          => __( 'Cancel', 'walkthecounty' ),
			'deactivation_no_option_selected' => __( 'Error: Please select at least one option.', 'walkthecounty' ),
			'submit_and_deactivate'           => __( 'Submit and Deactivate', 'walkthecounty' ),
			'skip_and_deactivate'             => __( 'Skip and Deactivate', 'walkthecounty' ),
			'please_fill_field'               => __( 'Error: Please complete the required field.', 'walkthecounty' ),

		);

		wp_localize_script( 'plugin-deactivation-survey-js', 'walkthecounty_vars', $localized_data );
	}

	/**
	 * Localize admin scripts.
	 */
	public function admin_localize_scripts() {

		global $post, $pagenow;
		$walkthecounty_options = walkthecounty_get_settings();

		// Price Separators.
		$thousand_separator = walkthecounty_get_price_thousand_separator();
		$decimal_separator  = walkthecounty_get_price_decimal_separator();
		$number_decimals    = walkthecounty_get_price_decimals();

		$stripe_user_id            = walkthecounty_get_option( 'walkthecounty_stripe_user_id', false );
		$disconnect_stripe_message = sprintf(
			/* translators: %s Stripe User ID */
			__( 'Are you sure you want to disconnect WalkTheCountyWP from Stripe? If disconnected, this website and any others sharing the same Stripe account (%s) that are connected to WalkTheCountyWP will need to reconnect in order to process payments.', 'walkthecounty' ),
			$stripe_user_id
		);

		// Localize strings & variables for JS.
		$localized_data = array(
			'post_id'                           => isset( $post->ID ) ? $post->ID : null,
			'walkthecounty_version'                      => WALKTHECOUNTY_VERSION,
			'thousands_separator'               => $thousand_separator,
			'decimal_separator'                 => $decimal_separator,
			'number_decimals'                   => $number_decimals,
			// Use this for number of decimals instead of `currency_decimals`.
			'currency_decimals'                 => $number_decimals,
			// If you find usage of this variable then replace it with `number_decimals`.
			'currency_sign'                     => walkthecounty_currency_filter( '' ),
			'currency_pos'                      => isset( $walkthecounty_options['currency_position'] ) ? $walkthecounty_options['currency_position'] : 'before',
			'quick_edit_warning'                => __( 'Not available for variable priced forms.', 'walkthecounty' ),
			'delete_payment'                    => __( 'Are you sure you want to <strong>permanently</strong> delete this donation?', 'walkthecounty' ),
			'delete_payment_note'               => __( 'Are you sure you want to delete this note?', 'walkthecounty' ),
			'revoke_api_key'                    => __( 'Are you sure you want to revoke this API key?', 'walkthecounty' ),
			'regenerate_api_key'                => __( 'Are you sure you want to regenerate this API key?', 'walkthecounty' ),
			'resend_receipt'                    => __( 'Are you sure you want to resend the donation receipt?', 'walkthecounty' ),
			'disconnect_user'                   => __( 'Are you sure you want to disconnect the user from this donor?', 'walkthecounty' ),
			'one_option'                        => __( 'Choose a form', 'walkthecounty' ),
			'one_or_more_option'                => __( 'Choose one or more forms', 'walkthecounty' ),
			'ok'                                => __( 'Ok', 'walkthecounty' ),
			'cancel'                            => __( 'Cancel', 'walkthecounty' ),
			'success'                           => __( 'Success', 'walkthecounty' ),
			'error'                             => __( 'Error', 'walkthecounty' ),
			'close'                             => __( 'Close', 'walkthecounty' ),
			'confirm'                           => __( 'Confirm', 'walkthecounty' ),
			'copied'                            => __( 'Copied!', 'walkthecounty' ),
			'shortcode_not_copy'                => __( 'Shortcode could not be copied.', 'walkthecounty' ),
			'confirm_action'                    => __( 'Confirm Action', 'walkthecounty' ),
			'confirm_deletion'                  => __( 'Confirm Deletion', 'walkthecounty' ),
			'confirm_delete_donation'           => __( 'Confirm Delete Donation', 'walkthecounty' ),
			'confirm_resend'                    => __( 'Confirm re-send', 'walkthecounty' ),
			'confirm_bulk_action'               => __( 'Confirm bulk action', 'walkthecounty' ),
			'restart_upgrade'                   => __( 'Do you want to restart the update process?', 'walkthecounty' ),
			'restart_update'                    => __( 'It is recommended that you backup your database before proceeding. Do you want to run the update now?', 'walkthecounty' ),
			'stop_upgrade'                      => __( 'Do you want to stop the update process now?', 'walkthecounty' ),
			'import_failed'                     => __( 'Import failed', 'walkthecounty' ),
			'flush_success'                     => __( 'Flush success', 'walkthecounty' ),
			'flush_error'                       => __( 'Flush error', 'walkthecounty' ),
			'no_form_selected'                  => __( 'No form selected', 'walkthecounty' ),
			'batch_export_no_class'             => __( 'You must choose a method.', 'walkthecounty' ),
			'batch_export_no_reqs'              => __( 'Required fields not completed.', 'walkthecounty' ),
			'reset_stats_warn'                  => __( 'Are you sure you want to reset WalkTheCounty? This process is <strong><em>not reversible</em></strong> and will delete all data regardless of test or live mode. Please be sure you have a recent backup before proceeding.', 'walkthecounty' ),
			'delete_test_donor'                 => __( 'Are you sure you want to delete all the test donors? This process will also delete test donations as well.', 'walkthecounty' ),
			'delete_import_donor'               => __( 'Are you sure you want to delete all the imported donors? This process will also delete imported donations as well.', 'walkthecounty' ),
			'delete_donations_only'             => __( 'Are you sure you want to delete all the donations in the specfied date range?', 'walkthecounty' ),
			'price_format_guide'                => sprintf( __( 'Please enter amount in monetary decimal ( %1$s ) format without thousand separator ( %2$s ) .', 'walkthecounty' ), $decimal_separator, $thousand_separator ),
			/* translators : %s: Donation form options metabox */
			'confirm_before_remove_row_text'    => __( 'Do you want to delete this item?', 'walkthecounty' ),
			'matched_success_failure_page'      => __( 'You cannot set the success and failed pages to the same page', 'walkthecounty' ),
			'dismiss_notice_text'               => __( 'Dismiss this notice.', 'walkthecounty' ),
			'search_placeholder'                => __( 'Type to search all forms', 'walkthecounty' ),
			'search_placeholder_donor'          => __( 'Type to search all donors', 'walkthecounty' ),
			'search_placeholder_country'        => __( 'Type to search all countries', 'walkthecounty' ),
			'search_placeholder_state'          => __( 'Type to search all states/provinces', 'walkthecounty' ),
			'unlock_donor_fields_title'         => __( 'Action forbidden', 'walkthecounty' ),
			'unlock_donor_fields_message'       => __( 'To edit first name and last name, please go to user profile of the donor.', 'walkthecounty' ),
			'remove_from_bulk_delete'           => __( 'Remove from Bulk Delete', 'walkthecounty' ),
			'donors_bulk_action'                => array(
				'no_donor_selected'  => array(
					'title' => __( 'No donors selected', 'walkthecounty' ),
					'desc'  => __( 'You must choose at least one or more donors to delete.', 'walkthecounty' ),
				),
				'no_action_selected' => array(
					'title' => __( 'No action selected', 'walkthecounty' ),
					'desc'  => __( 'You must select a bulk action to proceed.', 'walkthecounty' ),
				),
			),
			'donations_bulk_action'             => array(
				'titles'         => array(
					'zero' => __( 'No payments selected', 'walkthecounty' ),
				),
				'delete'         => array(
					'zero'     => __( 'You must choose at least one or more donations to delete.', 'walkthecounty' ),
					'single'   => __( 'Are you sure you want to permanently delete this donation?', 'walkthecounty' ),
					'multiple' => __( 'Are you sure you want to permanently delete the selected {payment_count} donations?', 'walkthecounty' ),
				),
				'resend-receipt' => array(
					'zero'     => __( 'You must choose at least one or more recipients to resend the email receipt.', 'walkthecounty' ),
					'single'   => __( 'Are you sure you want to resend the email receipt to this recipient?', 'walkthecounty' ),
					'multiple' => __( 'Are you sure you want to resend the emails receipt to {payment_count} recipients?', 'walkthecounty' ),
				),
				'set-to-status'  => array(
					'zero'     => __( 'You must choose at least one or more donations to set status to {status}.', 'walkthecounty' ),
					'single'   => __( 'Are you sure you want to set status of this donation to {status}?', 'walkthecounty' ),
					'multiple' => __( 'Are you sure you want to set status of {payment_count} donations to {status}?', 'walkthecounty' ),
				),
			),
			'updates'                           => array(
				'ajax_error' => __( 'Please reload this page and try again', 'walkthecounty' ),
			),
			'metabox_fields'                    => array(
				'media' => array(
					'button_title' => __( 'Choose Image', 'walkthecounty' ),
				),
				'file'  => array(
					'button_title' => __( 'Choose File', 'walkthecounty' ),
				),
			),
			'chosen'                            => array(
				'no_results_msg'  => __( 'No results match {search_term}', 'walkthecounty' ),
				'ajax_search_msg' => __( 'Searching results for match {search_term}', 'walkthecounty' ),
			),
			'db_update_confirmation_msg_button' => __( 'Run Updates', 'walkthecounty' ),
			'db_update_confirmation_msg'        => __( 'The following process will make updates to your site\'s database. Please create a database backup before proceeding with updates.', 'walkthecounty' ),
			'error_message'                     => __( 'Something went wrong kindly try again!', 'walkthecounty' ),
			'walkthecounty_donation_import'              => 'walkthecounty_donation_import',
			'core_settings_import'              => 'walkthecounty_core_settings_import',
			'setting_not_save_message'          => __( 'Changes you made may not be saved.', 'walkthecounty' ),
			'walkthecounty_donation_amounts'             => array(
				'minimum' => apply_filters( 'walkthecounty_donation_minimum_limit', 1 ),
				'maximum' => apply_filters( 'walkthecounty_donation_maximum_limit', 999999.99 ),
			),
			'chosen_add_title_prefix'           => __( 'No result found. Press enter to add', 'walkthecounty' ),
			'db_update_nonce'                   => wp_create_nonce( WalkTheCounty_Updates::$background_updater->get_identifier() ),
			'ajax'                              => walkthecounty_test_ajax_works(),
			'donor_note_confirm_msg'            => __( 'Please confirm you would like to add a donor note. An email notification will be sent to the donor with the note. If you do not want to notify the donor you may add a private note or disable the donor note email.', 'walkthecounty' ),
			'email_notification'                => array(
				'donor_note' => array(
					'status' => WalkTheCounty_Email_Notification_Util::is_email_notification_active( WalkTheCounty_Email_Notification::get_instance( 'donor-note' ) ),
				),
			),
			'disconnect_stripe_title'           => __( 'Confirm Disconnect?', 'walkthecounty' ),
			'disconnect_stripe_message'         => $disconnect_stripe_message,
			'loader_translation'                => array(
				'updating'   => __( 'Updating...', 'walkthecounty' ),
				'loading'    => __( 'Loading...', 'walkthecounty' ),
				'uploading'  => __( 'Uploading...', 'walkthecounty' ),
				'processing' => __( 'Processing...', 'walkthecounty' ),
				'activating' => __( 'Activating...', 'walkthecounty' ),
			),
		);

		wp_localize_script( 'walkthecounty-admin-scripts', 'walkthecounty_vars', $localized_data );
	}

	/**
	 * Global admin head.
	 */
	public function global_admin_head() {
		?>
		<style type="text/css" media="screen">
			@font-face {
				font-family: 'walkthecounty-icomoon';
				src: url('<?php echo WALKTHECOUNTY_PLUGIN_URL . 'assets/dist/fonts/icomoon.eot?ngjl88'; ?>');
				src: url('<?php echo WALKTHECOUNTY_PLUGIN_URL . 'assets/dist/fonts/icomoon.eot?#iefixngjl88'?>') format('embedded-opentype'),
				url('<?php echo WALKTHECOUNTY_PLUGIN_URL . 'assets/dist/fonts/icomoon.woff?ngjl88'; ?>') format('woff'),
				url('<?php echo WALKTHECOUNTY_PLUGIN_URL . 'assets/dist/fonts/icomoon.svg?ngjl88#icomoon'; ?>') format('svg');
				font-weight: normal;
				font-style: normal;
			}

			.dashicons-walkthecounty:before, #adminmenu div.wp-menu-image.dashicons-walkthecounty:before {
				font-family: 'walkthecounty-icomoon';
				font-size: 18px;
				width: 18px;
				height: 18px;
				content: "\e800";
			}
		</style>
		<?php

	}

	/**
	 * Enqueues public styles.
	 *
	 * @since 2.1.0
	 */
	public function public_enqueue_styles() {
		wp_enqueue_style( 'walkthecounty-styles' );
	}


	/**
	 * Enqueues public scripts.
	 *
	 * @since 2.1.0
	 */
	public function public_enqueue_scripts() {

		// Call Babel Polyfill with common handle so that it is compatible with plugins and themes.
		if ( ! wp_script_is( 'babel-polyfill', 'enqueued' )
		     && walkthecounty_is_setting_enabled( walkthecounty_get_option( 'babel_polyfill_script', 'enabled' ) )
		) {
			wp_enqueue_script(
				'babel-polyfill',
				WALKTHECOUNTY_PLUGIN_URL . 'assets/dist/js/babel-polyfill.js',
				array( 'jquery' ),
				WALKTHECOUNTY_VERSION,
				false
			);
		}

		wp_enqueue_script( 'walkthecounty' );

		$this->public_localize_scripts();
	}

	/**
	 * Localize / PHP to AJAX vars.
	 */
	public function public_localize_scripts() {

		/**
		 * Filter to modify access mail send notice
		 *
		 * @param string Send notice message for email access.
		 *
		 * @return  string $message Send notice message for email access.
		 * @since 2.1.3
		 *
		 */
		$message = (string) apply_filters( 'walkthecounty_email_access_mail_send_notice', __( 'Please check your email and click on the link to access your complete donation history.', 'walkthecounty' ) );

		$localize_walkthecounty_vars = apply_filters( 'walkthecounty_global_script_vars', array(
			'ajaxurl'                     => walkthecounty_get_ajax_url(),
			'checkout_nonce'              => wp_create_nonce( 'walkthecounty_checkout_nonce' ),
			// Do not use this nonce. Its deprecated.
			'currency'                    => walkthecounty_get_currency(),
			'currency_sign'               => walkthecounty_currency_filter( '' ),
			'currency_pos'                => walkthecounty_get_currency_position(),
			'thousands_separator'         => walkthecounty_get_price_thousand_separator(),
			'decimal_separator'           => walkthecounty_get_price_decimal_separator(),
			'no_gateway'                  => __( 'Please select a payment method.', 'walkthecounty' ),
			'bad_minimum'                 => __( 'The minimum custom donation amount for this form is', 'walkthecounty' ),
			'bad_maximum'                 => __( 'The maximum custom donation amount for this form is', 'walkthecounty' ),
			'general_loading'             => __( 'Loading...', 'walkthecounty' ),
			'purchase_loading'            => __( 'Please Wait...', 'walkthecounty' ),
			'number_decimals'             => walkthecounty_get_price_decimals(),
			'walkthecounty_version'                => WALKTHECOUNTY_VERSION,
			'magnific_options'            => apply_filters(
				'walkthecounty_magnific_options',
				array(
					'main_class'        => 'walkthecounty-modal',
					'close_on_bg_click' => false,
				)
			),
			'form_translation'            => apply_filters(
				'walkthecounty_form_translation_js',
				array(
					// Field name               Validation message.
					'payment-mode'           => __( 'Please select payment mode.', 'walkthecounty' ),
					'walkthecounty_first'             => __( 'Please enter your first name.', 'walkthecounty' ),
					'walkthecounty_email'             => __( 'Please enter a valid email address.', 'walkthecounty' ),
					'walkthecounty_user_login'        => __( 'Invalid email address or username.', 'walkthecounty' ),
					'walkthecounty_user_pass'         => __( 'Enter a password.', 'walkthecounty' ),
					'walkthecounty_user_pass_confirm' => __( 'Enter the password confirmation.', 'walkthecounty' ),
					'walkthecounty_agree_to_terms'    => __( 'You must agree to the terms and conditions.', 'walkthecounty' ),
				)
			),
			'confirm_email_sent_message'  => $message,
			'ajax_vars'                   => apply_filters( 'walkthecounty_global_ajax_vars', array(
				'ajaxurl'         => walkthecounty_get_ajax_url(),
				'ajaxNonce'       => wp_create_nonce( 'walkthecounty_ajax_nonce' ),
				'loading'         => __( 'Loading', 'walkthecounty' ),
				// General loading message.
				'select_option'   => __( 'Please select an option', 'walkthecounty' ),
				// Variable pricing error with multi-donation option enabled.
				'default_gateway' => walkthecounty_get_default_gateway( null ),
				'permalinks'      => get_option( 'permalink_structure' ) ? '1' : '0',
				'number_decimals' => walkthecounty_get_price_decimals(),
			) ),
			'cookie_hash'                 => COOKIEHASH,
			'session_nonce_cookie_name'   => WalkTheCounty()->session->get_cookie_name( 'nonce' ),
			'session_cookie_name'         => WalkTheCounty()->session->get_cookie_name( 'session' ),
			'delete_session_nonce_cookie' => absint( WalkTheCounty()->session->is_delete_nonce_cookie() ),
		) );

		wp_localize_script( 'walkthecounty', 'walkthecounty_global_vars', $localize_walkthecounty_vars );
	}

	/**
	 * Get the stylesheet URI.
	 *
	 * @since   1.6
	 * @updated 2.0.1 Moved to class and renamed as method.
	 *
	 * @return string
	 */
	public function get_frontend_stylesheet_uri() {

		$file          = 'walkthecounty' . $this->direction . '.css';
		$templates_dir = walkthecounty_get_theme_template_dir_name();

		// Directory paths to CSS files to support checking via file_exists().
		$child_theme_style_sheet    = trailingslashit( get_stylesheet_directory() ) . $templates_dir . $file;
		$child_theme_style_sheet_2  = trailingslashit( get_stylesheet_directory() ) . $templates_dir . 'walkthecounty' . $this->direction . '.css';
		$parent_theme_style_sheet   = trailingslashit( get_template_directory() ) . $templates_dir . $file;
		$parent_theme_style_sheet_2 = trailingslashit( get_template_directory() ) . $templates_dir . 'walkthecounty' . $this->direction . '.css';
		$walkthecounty_plugin_style_sheet    = trailingslashit( WALKTHECOUNTY_PLUGIN_DIR ) . 'assets/dist/css/' . $file;
		$uri                        = false;

		/**
		 * Locate the WalkTheCounty stylesheet:
		 *
		 * a. Look in the child theme directory first, followed by the parent theme
		 * b. followed by the WalkTheCounty core templates directory also look for the min version first,
		 * c. followed by non minified version, even if SCRIPT_DEBUG is not enabled. This allows users to copy just walkthecounty.css to their theme.
		 * d. Finally, fallback to the standard WalkTheCounty version. This is the default styles included within the plugin.
		 */
		if ( file_exists( $child_theme_style_sheet ) || ( ! empty( $suffix ) && ( $nonmin = file_exists( $child_theme_style_sheet_2 ) ) ) ) {
			if ( ! empty( $nonmin ) ) {
				$uri = trailingslashit( get_stylesheet_directory_uri() ) . $templates_dir . 'walkthecounty' . $this->direction . '.css';
			} else {
				$uri = trailingslashit( get_stylesheet_directory_uri() ) . $templates_dir . $file;
			}
		} elseif ( file_exists( $parent_theme_style_sheet ) || ( ! empty( $suffix ) && ( $nonmin = file_exists( $parent_theme_style_sheet_2 ) ) ) ) {
			if ( ! empty( $nonmin ) ) {
				$uri = trailingslashit( get_template_directory_uri() ) . $templates_dir . 'walkthecounty' . $this->direction . '.css';
			} else {
				$uri = trailingslashit( get_template_directory_uri() ) . $templates_dir . $file;
			}
		} elseif ( file_exists( $walkthecounty_plugin_style_sheet ) ) {
			$uri = trailingslashit( WALKTHECOUNTY_PLUGIN_URL ) . 'assets/dist/css/' . $file;
		}

		return apply_filters( 'walkthecounty_get_stylesheet_uri', $uri );

	}

	/**
	 * Gutenberg admin scripts.
	 */
	public function gutenberg_admin_scripts() {

		// Enqueue the bundled block JS file
		//@todo: Update dependencies on 5.0 Stable release
		wp_enqueue_script(
			'walkthecounty-blocks-js',
			WALKTHECOUNTY_PLUGIN_URL . 'assets/dist/js/gutenberg.js',
			array(
				'wp-i18n',
				'wp-element',
				'wp-blocks',
				'wp-components',
				'wp-api',
				'wp-editor',
			),
			WALKTHECOUNTY_VERSION
		);

		// Enqueue the bundled block css file
		wp_enqueue_style(
			'walkthecounty-blocks-css',
			WALKTHECOUNTY_PLUGIN_URL . 'assets/dist/css/gutenberg.css',
			array( 'walkthecounty-styles' ),
			WALKTHECOUNTY_VERSION
		);

	}

}
