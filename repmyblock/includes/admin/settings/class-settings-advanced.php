<?php
/**
 * WalkTheCounty Settings Page/Tab
 *
 * @package     WalkTheCounty
 * @subpackage  Classes/WalkTheCounty_Settings_Advanced
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WalkTheCounty_Settings_Advanced' ) ) :

	/**
	 * WalkTheCounty_Settings_Advanced.
	 *
	 * @sine 1.8
	 */
	class WalkTheCounty_Settings_Advanced extends WalkTheCounty_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'advanced';
			$this->label = __( 'Advanced', 'walkthecounty' );

			$this->default_tab = 'advanced-options';

			if ( $this->id === walkthecounty_get_current_setting_tab() ) {
				add_action( 'walkthecounty_admin_field_remove_cache_button', array( $this, 'render_remove_cache_button' ), 10, 1 );
				add_action( 'walkthecounty_save_settings_walkthecounty_settings', array( $this, 'validate_settngs' ) );
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
			$settings = array();

			$current_section = walkthecounty_get_current_setting_section();

			switch ( $current_section ) {
				case 'advanced-options':
					$settings = array(
						array(
							'id'   => 'walkthecounty_title_data_control_2',
							'type' => 'title',
						),
						array(
							'name'    => __( 'Remove Data on Uninstall', 'walkthecounty' ),
							'desc'    => __( 'When the plugin is deleted, completely remove all WalkTheCountyWP data. This includes all WalkTheCountyWP settings, forms, form meta, donor, donor data, donations. Everything.', 'walkthecounty' ),
							'id'      => 'uninstall_on_delete',
							'type'    => 'radio_inline',
							'default' => 'disabled',
							'options' => array(
								'enabled'  => __( 'Yes, Remove all data', 'walkthecounty' ),
								'disabled' => __( 'No, keep my WalkTheCountyWP settings and donation data', 'walkthecounty' ),
							),
						),
						array(
							'name'    => __( 'Default User Role', 'walkthecounty' ),
							'desc'    => __( 'Assign default user roles for donors when donors opt to register as a WP User.', 'walkthecounty' ),
							'id'      => 'donor_default_user_role',
							'type'    => 'select',
							'default' => 'walkthecounty_donor',
							'options' => walkthecounty_get_user_roles(),
						),
						array(
							/* translators: %s: the_content */
							'name'    => sprintf( __( '%s filter', 'walkthecounty' ), '<code>the_content</code>' ),
							/* translators: 1: https://codex.wordpress.org/Plugin_API/Filter_Reference/the_content 2: the_content */
							'desc'    => sprintf( __( 'If you are seeing extra social buttons, related posts, or other unwanted elements appearing within your forms then you can disable WordPress\' content filter. <a href="%1$s" target="_blank">Learn more</a> about %2$s filter.', 'walkthecounty' ), esc_url( 'https://codex.wordpress.org/Plugin_API/Filter_Reference/the_content' ), '<code>the_content</code>' ),
							'id'      => 'the_content_filter',
							'default' => 'enabled',
							'type'    => 'radio_inline',
							'options' => array(
								'enabled'  => __( 'Enabled', 'walkthecounty' ),
								'disabled' => __( 'Disabled', 'walkthecounty' ),
							),
						),
						array(
							'name'    => __( 'Script Loading Location', 'walkthecounty' ),
							'desc'    => __( 'This allows you to load your WalkTheCountyWP scripts either in the <code>&lt;head&gt;</code> or footer of your website.', 'walkthecounty' ),
							'id'      => 'scripts_footer',
							'type'    => 'radio_inline',
							'default' => 'disabled',
							'options' => array(
								'disabled' => __( 'Head', 'walkthecounty' ),
								'enabled'  => __( 'Footer', 'walkthecounty' ),
							),
						),
						array(
							'name'    => __( 'Babel Polyfill Script', 'walkthecounty' ),
							'desc'    => __( 'Decide whether to load the Babel polyfill, which provides backwards compatibility for older browsers such as IE 11. The polyfill may be disabled to avoid conflicts with other themes or plugins that load the same script.', 'walkthecounty' ),
							'id'      => 'babel_polyfill_script',
							'type'    => 'radio_inline',
							'default' => 'enabled',
							'options' => array(
								'enabled'  => __( 'Enabled', 'walkthecounty' ),
								'disabled' => __( 'Disabled', 'walkthecounty' ),
							),
						),
						array(
							'name'    => __( 'Akismet SPAM Protection', 'walkthecounty' ),
							'desc'    => __( 'Add a layer of SPAM protection to your donation submissions with Akismet. When enabled, donation submissions will be first sent to Akismet\'s API if you have the plugin activated and configured.', 'walkthecounty' ),
							'id'      => 'akismet_spam_protection',
							'type'    => 'radio_inline',
							'default' => ( walkthecounty_check_akismet_key() ) ? 'enabled' : 'disabled',
							'options' => array(
								'enabled'  => __( 'Enabled', 'walkthecounty' ),
								'disabled' => __( 'Disabled', 'walkthecounty' ),
							),
						),
						array(
							'name'    => __( 'Welcome Screen', 'walkthecounty' ),
							/* translators: %s: about page URL */
							'desc'    => sprintf( wp_kses( __( 'Enable this option if you would like to disable the <a href="%s" target="_blank">WalkTheCountyWP Welcome screen</a> that displays each time WalkTheCountyWP is activated or updated.', 'walkthecounty' ), array(
								'a' => array(
									'href'   => array(),
									'target' => array(),
								),
							) ), esc_url( admin_url( 'index.php?page=walkthecounty-getting-started' ) ) ),
							'id'      => 'welcome',
							'type'    => 'radio_inline',
							'default' => 'enabled',
							'options' => array(
								'enabled'  => __( 'Enabled', 'walkthecounty' ),
								'disabled' => __( 'Disabled', 'walkthecounty' ),
							),
						),
						array(
							'name'        => 'WalkTheCountyWP Cache',
							'id'          => 'walkthecounty-clear-cache',
							'buttonTitle' => __( 'Clear Cache', 'walkthecounty' ),
							'desc'        => __( 'Click this button if you want to clear WalkTheCounty\'s cache. The plugin stores common settings and queries in cache to optimize performance. Clearing cache will remove and begin rebuilding these saved queries.', 'walkthecounty' ),
							'type'        => 'remove_cache_button'
						),
						array(
							'name'  => __( 'Advanced Settings Docs Link', 'walkthecounty' ),
							'id'    => 'advanced_settings_docs_link',
							'url'   => esc_url( 'http://docs.walkthecountywp.com/settings-advanced' ),
							'title' => __( 'Advanced Settings', 'walkthecounty' ),
							'type'  => 'walkthecounty_docs_link',
						),
						array(
							'id'   => 'walkthecounty_title_data_control_2',
							'type' => 'sectionend',
						),
					);
					break;
			}

			/**
			 * Hide caching setting by default.
			 *
			 * @since 2.0
			 */
			if ( apply_filters( 'walkthecounty_settings_advanced_show_cache_setting', false ) ) {
				array_splice( $settings, 1, 0, array(
					array(
						'name'    => __( 'Cache', 'walkthecounty' ),
						'desc'    => __( 'If caching is enabled the plugin will start caching custom post type related queries and reduce the overall load time.', 'walkthecounty' ),
						'id'      => 'cache',
						'type'    => 'radio_inline',
						'default' => 'enabled',
						'options' => array(
							'enabled'  => __( 'Enabled', 'walkthecounty' ),
							'disabled' => __( 'Disabled', 'walkthecounty' ),
						),
					)
				) );
			}


			/**
			 * Filter the advanced settings.
			 * Backward compatibility: Please do not use this filter. This filter is deprecated in 1.8
			 */
			$settings = apply_filters( 'walkthecounty_settings_advanced', $settings );

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
				'advanced-options' => __( 'Advanced Options', 'walkthecounty' ),
			);

			return apply_filters( 'walkthecounty_get_sections_' . $this->id, $sections );
		}


		/**
		 *  Render remove_cache_button field type
		 *
		 * @since  2.1
		 * @access public
		 *
		 * @param array $field
		 */
		public function render_remove_cache_button( $field ) {
			?>
			<tr valign="top" <?php echo ! empty( $field['wrapper_class'] ) ? 'class="' . $field['wrapper_class'] . '"' : '' ?>>
				<th scope="row" class="titledesc">
					<label
						for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['name'] ) ?></label>
				</th>
				<td class="walkthecounty-forminp">
					<button type="button" id="<?php echo esc_attr( $field['id'] ); ?>"
					        class="button button-secondary"><?php echo esc_html( $field['buttonTitle'] ) ?></button>
					<?php echo WalkTheCounty_Admin_Settings::get_field_description( $field ); ?>
				</td>
			</tr>
			<?php
		}


		/**
		 * Validate setting
		 *
		 * @since  2.2.0
		 * @access public
		 *
		 * @param array $options
		 */
		public function validate_settngs( $options ) {
			// Sanitize data.
			$akismet_spam_protection = isset( $options['akismet_spam_protection'] )
				? $options['akismet_spam_protection']
				: ( walkthecounty_check_akismet_key() ? 'enabled' : 'disabled' );

			// Show error message if Akismet not configured and Admin try to save 'enabled' option.
			if (
				walkthecounty_is_setting_enabled( $akismet_spam_protection )
				&& ! walkthecounty_check_akismet_key()
			) {
				WalkTheCounty_Admin_Settings::add_error(
					'walkthecounty-akismet-protection',
					__( 'Please properly configure Akismet to enable SPAM protection.', 'walkthecounty' )
				);

				walkthecounty_update_option( 'akismet_spam_protection', 'disabled' );
			}
		}
	}

endif;

return new WalkTheCounty_Settings_Advanced();
