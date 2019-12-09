<?php
/**
 * Email Notification
 *
 * This class handles all email notification settings.
 *
 * @package     WalkTheCounty
 * @subpackage  Classes/Emails
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.0
 */

/**
 * Class WalkTheCounty_Email_Notifications
 */
class WalkTheCounty_Email_Notifications {
	/**
	 * Instance.
	 *
	 * @since  2.0
	 * @access static
	 * @var
	 */
	static private $instance;

	/**
	 * Array of email notifications.
	 *
	 * @since  2.0
	 * @access private
	 * @var array
	 */
	private $emails = array();

	/**
	 * Singleton pattern.
	 *
	 * @since  2.0
	 * @access private
	 * WalkTheCounty_Email_Notifications constructor.
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @since  2.0
	 * @access static
	 * @return static
	 */
	static function get_instance() {
		if ( null === static::$instance ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	/**
	 * Setup dependencies
	 *
	 * @since 2.0
	 */
	public function init() {
		// Load files.
		require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/emails/ajax-handler.php';
		require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/emails/class-email-setting-field.php';
		require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/emails/filters.php';

		// Load email notifications.
		$this->add_emails_notifications();

		add_filter( 'walkthecounty_metabox_form_data_settings', array( $this, 'add_metabox_setting_fields' ), 10, 2 );
		add_action( 'init', array( $this, 'preview_email' ) );
		add_action( 'init', array( $this, 'send_preview_email' ) );
		add_action( 'admin_init', array( $this, 'validate_settings' ) );

		/* @var WalkTheCounty_Email_Notification $email */
		foreach ( $this->get_email_notifications() as $email ) {
			// Setup email section.
			if( WalkTheCounty_Email_Notification_Util::is_show_on_emails_setting_page( $email ) ) {
				add_filter( 'walkthecounty_get_sections_emails', array( $email, 'add_section' ) );
				add_filter( "walkthecounty_hide_section_{$email->config['id']}_on_emails_page", array( $email, 'hide_section' ) );
			}

			// Setup email preview.
			if ( WalkTheCounty_Email_Notification_Util::is_email_preview_has_header( $email ) ) {
				add_action( "walkthecounty_{$email->config['id']}_email_preview", array( $this, 'email_preview_header' ) );
				add_filter( "walkthecounty_{$email->config['id']}_email_preview_data", array( $this, 'email_preview_data' ) );
				add_filter( "walkthecounty_{$email->config['id']}_email_preview_message", array( $this, 'email_preview_message' ), 1, 2 );
			}
		}
	}


	/**
	 * Add setting to metabox.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param array $settings
	 * @param int   $post_id
	 *
	 * @return array
	 */
	public function add_metabox_setting_fields( $settings, $post_id ) {
		$emails = $this->get_email_notifications();

		// Bailout.
		if ( empty( $emails ) ) {
			return $settings;
		}

		// Email notification setting.
		$settings['email_notification_options'] = array(
			'id'         => 'email_notification_options',
			'title'      => __( 'Email Notifications', 'walkthecounty' ),
			'icon-html' => '<span class="dashicons dashicons-email-alt"></span>',
			'fields'     => array(
				array(
					'name'        => __( 'Email Options', 'walkthecounty' ),
					'id'          => '_walkthecounty_email_options',
					'type'        => 'radio_inline',
					'default'     => 'global',
					'options'     => array(
						'global'   => __( 'Global Options' ),
						'enabled'  => __( 'Customize', 'walkthecounty' ),
					),
				),
				array(
					'id'      => '_walkthecounty_email_template',
					'name'    => esc_html__( 'Email Template', 'walkthecounty' ),
					'desc'    => esc_html__( 'Choose your template from the available registered template types.', 'walkthecounty' ),
					'type'    => 'select',
					'default' => 'default',
					'options' => walkthecounty_get_email_templates(),
				),
				array(
					'id'   => '_walkthecounty_email_logo',
					'name' => esc_html__( 'Logo', 'walkthecounty' ),
					'desc' => esc_html__( 'Upload or choose a logo to be displayed at the top of the donation receipt emails. Displayed on HTML emails only.', 'walkthecounty' ),
					'type' => 'file',
				),
				array(
					'id'      => '_walkthecounty_from_name',
					'name'    => esc_html__( 'From Name', 'walkthecounty' ),
					'desc'    => esc_html__( 'The name which appears in the "From" field in all WalkTheCountyWP donation emails.', 'walkthecounty' ),
					'default' => get_bloginfo( 'name' ),
					'type'    => 'text',
				),
				array(
					'id'      => '_walkthecounty_from_email',
					'name'    => esc_html__( 'From Email', 'walkthecounty' ),
					'desc'    => esc_html__( 'Email address from which all WalkTheCountyWP emails are sent from. This will act as the "from" and "reply-to" email address.', 'walkthecounty' ),
					'default' => get_bloginfo( 'admin_email' ),
					'type'    => 'text',
				),
				array(
					'name'  => 'email_notification_docs',
					'type'  => 'docs_link',
					'url'   => 'http://docs.walkthecountywp.com/email-notification',
					'title' => __( 'Email Notification', 'walkthecounty' ),
				),
			),

			/**
			 * Filter the email notification settings.
			 *
			 * @since 2.0
			 */
			'sub-fields' => apply_filters( 'walkthecounty_email_notification_options_metabox_fields', array(), $post_id ),
		);

		return $settings;
	}

	/**
	 * Add email notifications
	 *
	 * @since  2.0
	 * @access private
	 */
	private function add_emails_notifications() {
		$this->emails = array(
			include WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/emails/class-new-donation-email.php',
			include WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/emails/class-donation-receipt-email.php',
			include WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/emails/class-new-offline-donation-email.php',
			include WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/emails/class-offline-donation-instruction-email.php',
			include WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/emails/class-new-donor-register-email.php',
			include WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/emails/class-donor-register-email.php',
			include WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/emails/class-donor-note-email.php',
			include WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/emails/class-email-access-email.php',
		);

		/**
		 * Filter the email notifications.
		 *
		 * @since 2.0
		 */
		$this->emails = apply_filters( 'walkthecounty_email_notifications', $this->emails, $this );

		// Bailout.
		if ( empty( $this->emails ) ) {
			return;
		}

		// Initiate email notifications.
		foreach ( $this->emails as $email ) {
			$email->init();
		}
	}


	/**
	 * Get list of email notifications.
	 *
	 * @since  2.0
	 * @access public
	 * @return array
	 */
	public function get_email_notifications() {
		return $this->emails;
	}


	/**
	 * Displays the email preview
	 *
	 * @since  2.0
	 * @access public
	 * @return bool|null
	 */
	public function preview_email() {
		// Bailout.
		if ( ! WalkTheCounty_Email_Notification_Util::can_preview_email() ) {
			return false;
		}

		// Security check.
		walkthecounty_validate_nonce( $_GET['_wpnonce'], 'walkthecounty-preview-email' );

		// Get email type.
		$email_type = isset( $_GET['email_type'] ) ? esc_attr( $_GET['email_type'] ) : '';

		/* @var WalkTheCounty_Email_Notification $email */
		foreach ( $this->get_email_notifications() as $email ) {
			if ( $email_type !== $email->config['id'] ) {
				continue;
			}

			// Set form id.
			$form_id = empty( $_GET['form_id'] ) ? null : absint( $_GET['form_id'] );

			// Call setup email data to apply filter and other thing to email.
			$email->send_preview_email( false );

			// Decode message.
			$email_message = $email->preview_email_template_tags( $email->get_email_message( $form_id ) );

			// Show formatted text in browser even text/plain content type set for an email.
			WalkTheCounty()->emails->html = true;

			WalkTheCounty()->emails->form_id = $form_id;

			if ( 'text/plain' === $email->config['content_type'] ) {
				// WalkTheCounty()->emails->__set( 'html', false );
				WalkTheCounty()->emails->__set( 'template', 'none' );
			}

			if ( $email_message = WalkTheCounty()->emails->build_email( $email_message ) ) {

				/**
				 * Filter the email preview data
				 *
				 * @since 2.0
				 *
				 * @param array
				 */
				$email_preview_data = apply_filters( "walkthecounty_{$email_type}_email_preview_data", array() );

				/**
				 * Fire the walkthecounty_{$email_type}_email_preview action
				 *
				 * @since 2.0
				 */
				do_action( "walkthecounty_{$email_type}_email_preview", $email );

				/**
				 * Filter the email message
				 *
				 * @since 2.0
				 *
				 * @param string                  $email_message
				 * @param array                   $email_preview_data
				 * @param WalkTheCounty_Email_Notification $email
				 */
				echo apply_filters( "walkthecounty_{$email_type}_email_preview_message", $email_message, $email_preview_data, $email );

				exit();
			}
		}// End foreach().
	}


	/**
	 * Add header to donation receipt email preview
	 *
	 * @since   2.0
	 * @access  public
	 *
	 * @param WalkTheCounty_Email_Notification $email
	 */
	public function email_preview_header( $email ) {
		/**
		 * Filter the all email preview headers.
		 *
		 * @since 2.0
		 *
		 * @param WalkTheCounty_Email_Notification $email
		 */
		$email_preview_header = apply_filters( 'walkthecounty_email_preview_header', walkthecounty_get_preview_email_header(), $email );

		echo $email_preview_header;
	}

	/**
	 * Add email preview data
	 *
	 * @since   2.0
	 * @access  public
	 *
	 * @param array $email_preview_data
	 *
	 * @return array
	 */
	public function email_preview_data( $email_preview_data ) {
		$email_preview_data['payment_id'] = absint( walkthecounty_check_variable( walkthecounty_clean( $_GET ), 'isset', 0, 'preview_id' ) );
		$email_preview_data['user_id']    = absint( walkthecounty_check_variable( walkthecounty_clean( $_GET ), 'isset', 0, 'user_id' ) );

		return $email_preview_data;
	}

	/**
	 * Replace email template tags.
	 *
	 * @since   2.0
	 * @access  public
	 *
	 * @param string $email_message
	 * @param array  $email_preview_data
	 *
	 * @return string
	 */
	public function email_preview_message( $email_message, $email_preview_data ) {
		if (
			! empty( $email_preview_data['payment_id'] )
			|| ! empty( $email_preview_data['user_id'] )
		) {
			$email_message = walkthecounty_do_email_tags( $email_message, $email_preview_data );
		}

		return $email_message;
	}

	/**
	 * Displays the email preview
	 *
	 * @since  2.0
	 * @access public
	 * @return bool|null
	 */
	public function send_preview_email() {
		// Bailout.
		if ( ! WalkTheCounty_Email_Notification_Util::can_send_preview_email() ) {
			return false;
		}

		// Security check.
		walkthecounty_validate_nonce( $_GET['_wpnonce'], 'walkthecounty-send-preview-email' );

		// Get email type.
		$email_type = walkthecounty_check_variable( walkthecounty_clean( $_GET ), 'isset', '', 'email_type' );

		/* @var WalkTheCounty_Email_Notification $email */
		foreach ( $this->get_email_notifications() as $email ) {
			if ( $email_type === $email->config['id'] && WalkTheCounty_Email_Notification_Util::is_email_preview( $email ) ) {
				$email->send_preview_email();
				break;
			}
		}

		// Remove the test email query arg.
		wp_redirect( remove_query_arg( 'walkthecounty_action' ) );
		exit;
	}


	/**
	 * Load WalkTheCounty_Email_Notifications
	 *
	 * @since  2.0
	 * @access public
	 */
	public function load() {
		add_action( 'init', array( $this, 'init' ), -1 );
	}


	/**
	 * Verify email setting before saving
	 *
	 * @since  2.0
	 * @access public
	 */
	public function validate_settings() {
		// Bailout.
		if (
			! WalkTheCounty_Admin_Settings::is_saving_settings() ||
			'emails' !== walkthecounty_get_current_setting_tab() ||
			! isset( $_GET['section'] )
		) {
			return;
		}

		// Get email type.
		$email_type = walkthecounty_get_current_setting_section();

		if ( ! empty( $_POST["{$email_type}_recipient"] ) ) {
			$_POST["{$email_type}_recipient"] = array_unique( array_filter( $_POST["{$email_type}_recipient"] ) );
		}
	}
}

// Helper class.
require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/emails/abstract-email-notification.php';
require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/emails/class-email-notification-util.php';

// Add backward compatibility.
require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/emails/backward-compatibility.php';

/**
 * Initialize functionality.
 */
WalkTheCounty_Email_Notifications::get_instance()->load();
