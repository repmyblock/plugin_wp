<?php
/**
 * WalkTheCounty Settings Page/Tab
 *
 * @package     WalkTheCounty
 * @subpackage  Classes/WalkTheCounty_Settings_Email
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WalkTheCounty_Settings_Email' ) ) :

	/**
	 * WalkTheCounty_Settings_Email.
	 *
	 * @sine 1.8
	 */
	class WalkTheCounty_Settings_Email extends WalkTheCounty_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'emails';
			$this->label = esc_html__( 'Emails', 'walkthecounty' );

			$this->default_tab = 'donor-email';

			parent::__construct();

			$this->enable_save = ! ( WalkTheCounty_Admin_Settings::is_setting_page( 'emails', 'donor-email' ) || WalkTheCounty_Admin_Settings::is_setting_page( 'emails', 'admin-email' ) );

			add_action( 'walkthecounty_admin_field_email_notification', array( $this, 'email_notification_setting' ) );
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
				case 'email-settings' :
					$settings = array(

						// Section 1: Email Sender Setting
						array(
							'id'    => 'walkthecounty_title_email_settings_1',
							'type'  => 'title',
						),
						array(
							'id'      => 'email_template',
							'name'    => esc_html__( 'Email Template', 'walkthecounty' ),
							'desc'    => esc_html__( 'Choose your template from the available registered template types.', 'walkthecounty' ),
							'type'    => 'select',
							'options' => walkthecounty_get_email_templates(),
						),
						array(
							'id'   => 'email_logo',
							'name' => esc_html__( 'Logo', 'walkthecounty' ),
							'desc' => esc_html__( 'Upload or choose a logo to be displayed at the top of the donation receipt emails. Displayed on HTML emails only.', 'walkthecounty' ),
							'type' => 'file',
						),
						array(
							'id'      => 'from_name',
							'name'    => esc_html__( 'From Name', 'walkthecounty' ),
							'desc'    => esc_html__( 'The name which appears in the "From" field in all WalkTheCountyWP donation emails.', 'walkthecounty' ),
							'default' => get_bloginfo( 'name' ),
							'type'    => 'text',
						),
						array(
							'id'      => 'from_email',
							'name'    => esc_html__( 'From Email', 'walkthecounty' ),
							'desc'    => esc_html__( 'Email address from which all WalkTheCountyWP emails are sent from. This will act as the "from" and "reply-to" email address.', 'walkthecounty' ),
							'default' => get_bloginfo( 'admin_email' ),
							'type'    => 'text',
						),
						array(
							'name'  => esc_html__( 'Donation Notification Settings Docs Link', 'walkthecounty' ),
							'id'    => 'donation_notification_settings_docs_link',
							'url'   => esc_url( 'http://docs.walkthecountywp.com/settings-donation-notification' ),
							'title' => __( 'Donation Notification Settings', 'walkthecounty' ),
							'type'  => 'walkthecounty_docs_link',
						),
						array(
							'id'   => 'walkthecounty_title_email_settings_3',
							'type' => 'sectionend',
						),
					);
					break;

				case 'donor-email' :
					$settings = array(

						// Section 1: Donor Email Notification Listing.
						array(
							'desc'       => __( 'Email notifications sent from WalkTheCountyWP for donor are listed below. Click on an email to configure it.', 'walkthecounty' ),
							'type'       => 'title',
							'id'         => 'walkthecounty_donor_email_notification_settings',
							'table_html' => false,
						),
						array(
							'type' => 'email_notification',
						),
						array(
							'type' => 'sectionend',
							'id'   => 'walkthecounty_donor_email_notification_settings',
						),

					);
					break;

				case 'admin-email' :
					$settings = array(

						// Section 1: Admin Email Notification Listing.
						array(
							'desc'       => __( 'Email notifications sent from WalkTheCountyWP for admin are listed below. Click on an email to configure it.', 'walkthecounty' ),
							'type'       => 'title',
							'id'         => 'walkthecounty_admin_email_notification_settings',
							'table_html' => false,
						),
						array(
							'type' => 'email_notification',
						),
						array(
							'type' => 'sectionend',
							'id'   => 'walkthecounty_admin_email_notification_settings',
						),

					);
					break;

				case 'contact':
					$settings = array(

						array(
							'id'   => 'walkthecounty_title_general_settings_5',
							'type' => 'title'
						),
						array(
							'name'    => __( 'Admin Email Address', 'walkthecounty' ),
							'id'      => "contact_admin_email",
							'desc'    => sprintf( '%1$s <code>{admin_email}</code> %2$s', __( 'By default, the', 'walkthecounty' ), __( 'tag will use your WordPress admin email. If you would like to customize this address you can do so in the field above.', 'walkthecounty' ) ),
							'type'    => 'text',
							'default' => walkthecounty_email_admin_email(),

						),
						array(
							'name'    => __( 'Offline Mailing Address', 'walkthecounty' ),
							'id'      => "contact_offline_mailing_address",
							'desc'    => sprintf( '%1$s <code>{offline_mailing_address}</code> %2$s', __( 'Set the mailing address to where you would like your donors to send their offline donations. This will customize the', 'walkthecounty' ), __( 'email tag for the Offline Donations payment gateway.', 'walkthecounty' ) ),
							'type'    => 'wysiwyg',
							'default' => '&nbsp;&nbsp;&nbsp;&nbsp;<em>' . get_bloginfo( 'sitename' ) . '</em><br>&nbsp;&nbsp;&nbsp;&nbsp;<em>111 Not A Real St.</em><br>&nbsp;&nbsp;&nbsp;&nbsp;<em>Anytown, CA 12345 </em><br>',
						),
						array(
							'id'   => 'walkthecounty_title_general_settings_4',
							'type' => 'sectionend'
						)
					);

					break;
			}// End switch().

			/**
			 * Filter the emails settings.
			 * Backward compatibility: Please do not use this filter. This filter is deprecated in 1.8
			 */
			$settings = apply_filters( 'walkthecounty_settings_emails', $settings );

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
				'donor-email'    => esc_html__( 'Donor Emails', 'walkthecounty' ),
				'admin-email'    => esc_html__( 'Admin Emails', 'walkthecounty' ),
				'email-settings' => esc_html__( 'Email Settings', 'walkthecounty' ),
				'contact'        => esc_html__( 'Contact Information', 'walkthecounty' ),
			);

			return apply_filters( 'walkthecounty_get_sections_' . $this->id, $sections );
		}

		/**
		 * Render email_notification field type
		 *
		 * @since  2.0
		 * @access public
		 */
		public function email_notification_setting() {
			// Load email notification table.
			require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/emails/class-email-notification-table.php';

			// Init table.
			$email_notifications_table = new WalkTheCounty_Email_Notification_Table();

			// Print table.
			$email_notifications_table->prepare_items();
			$email_notifications_table->display();
		}

		/**
		 * Output the settings.
		 *
		 * Note: if you want to overwrite this function then manage show/hide save button in your class.
		 *
		 * @since  1.8
		 * @return void
		 */
		public function output() {
			if ( $this->enable_save ) {
				$GLOBALS['walkthecounty_hide_save_button'] = apply_filters( 'walkthecounty_hide_save_button_on_email_admin_setting_page', false );
			}

			$settings = $this->get_settings();

			WalkTheCounty_Admin_Settings::output_fields( $settings, 'walkthecounty_settings' );
		}
	}

endif;

return new WalkTheCounty_Settings_Email();
