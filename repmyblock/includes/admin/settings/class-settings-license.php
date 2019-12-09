<?php
/**
 * WalkTheCounty Settings Page/Tab
 *
 * @package     WalkTheCounty
 * @subpackage  Classes/WalkTheCounty_Settings_License
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WalkTheCounty_Settings_License' ) ) :

	/**
	 * WalkTheCounty_Settings_License.
	 *
	 * @sine 1.8
	 */
	class WalkTheCounty_Settings_License extends WalkTheCounty_Settings_Page {
		protected $enable_save = false;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'licenses';
			$this->label = esc_html__( 'Licenses', 'walkthecounty' );

			parent::__construct();

			// Do not use main form for this tab.
			if ( walkthecounty_get_current_setting_tab() === $this->id ) {

				// Remove default parent form.
				add_action( 'walkthecounty-settings_open_form', '__return_empty_string' );
				add_action( 'walkthecounty-settings_close_form', '__return_empty_string' );

				// Refresh licenses when visit license setting page.
				walkthecounty_refresh_licenses();
			}
		}

		/**
		 * Get settings array.
		 *
		 * @return array
		 * @since  1.8
		 */
		public function get_settings() {
			$settings = array();

			/**
			 * Filter the licenses settings.
			 * Backward compatibility: Please do not use this filter. This filter is deprecated in 1.8
			 */
			$settings = apply_filters( 'walkthecounty_settings_licenses', $settings );

			/**
			 * Filter the settings.
			 *
			 * @param array $settings
			 *
			 * @since  1.8
			 */
			$settings = apply_filters( 'walkthecounty_get_settings_' . $this->id, $settings );

			// Output.
			return $settings;
		}

		/**
		 * Render  license key field
		 *
		 * @since 2.5.0
		 */
		public function output() {
			ob_start();
			?>
			<div class="walkthecounty-license-settings-wrap">

				<div class="walkthecounty-grid-row">

					<div class="walkthecounty-grid-col-6">
						<div id="walkthecounty-license-activator-wrap" class="walkthecounty-license-top-widget">
							<div id="walkthecounty-license-activator-inner">

								<h2 class="walkthecounty-license-widget-heading">
									<span class="dashicons dashicons-plugins-checked"></span>
									<?php _e( 'Activate an Add-on License', 'walkthecounty' ); ?>
								</h2>

								<p class="walkthecounty-field-description">
									<?php
									printf(
										__( 'Enter your license key below to unlock your WalkTheCountyWP add-ons. You can access your licenses anytime from the <a href="%1$s" target="_blank">My Account</a> section on the WalkTheCountyWP website. ', 'walkthecounty' ),
										WalkTheCounty_License::get_account_url()
									);
									?>
								</p>

								<form method="post" action="" class="walkthecounty-license-activation-form">

									<div class="walkthecounty-license-notices"></div>

									<?php wp_nonce_field( 'walkthecounty-license-activator-nonce', 'walkthecounty_license_activator_nonce' ); ?>

									<label
										for="walkthecounty-license-activator"
										class="screen-reader-text">
										<?php _e( 'Activate License', 'walkthecounty' ); ?>
									</label>

									<input
										id="walkthecounty-license-activator"
										type="text"
										name="walkthecounty_license_key"
										placeholder="<?php _e( 'Enter your license key', 'walkthecounty' ); ?>"
									/>

									<input
										data-activate="<?php _e( 'Activate License', 'walkthecounty' ); ?>"
										data-activating="<?php _e( 'Verifying License...', 'walkthecounty' ); ?>"
										value="<?php _e( 'Activate License', 'walkthecounty' ); ?>"
										type="submit"
										class="button button-primary"
									/>

								</form>

							</div>
						</div>
					</div><!-- /.walkthecounty-grid-col-6 -->

					<div class="walkthecounty-grid-col-6">
						<div id="walkthecounty-addon-uploader-wrap" class="walkthecounty-license-top-widget"
						     ondragover="event.preventDefault()">
							<div id="walkthecounty-addon-uploader-inner">
								<h2 class="walkthecounty-license-widget-heading">
									<span class="dashicons dashicons-upload"></span>
									<?php _e( 'Upload and Activate an Add-on', 'walkthecounty' ); ?>
								</h2>

								<?php if( ! is_multisite() ) :  ?>

									<p class="walkthecounty-field-description">
										<?php
										printf(
											__( 'Drag an add-on zip file below to upload and activate it. Access your downloads by activating a license or via the <a href="%1$s" target="_blank">My Downloads</a> section on the WalkTheCountyWP website. ', 'walkthecounty' ),
											WalkTheCounty_License::get_downloads_url()
										);
										?>
									</p>

									<?php if ( 'direct' !== get_filesystem_method() ) : ?>
										<div class="walkthecounty-notice notice notice-error inline">
											<p>
												<?php
												echo sprintf(
													__( 'Sorry, you can not upload plugin from here because we do not have direct access to file system. Please <a href="%1$s" target="_blank">click here</a> to upload WalkTheCountyWP Add-on.', 'walkthecounty' ),
													admin_url( 'plugin-install.php?tab=upload' )
												);
												?>
											</p>
										</div>
									<?php else : ?>
										<div class="walkthecounty-upload-addon-form-wrap">
											<form
												method="post"
												enctype="multipart/form-data"
												class="walkthecounty-upload-addon-form"
												action="/">

												<div class="walkthecounty-addon-upload-notices"></div>

												<div class="walkthecounty-activate-addon-wrap">
													<p><span
															class="dashicons dashicons-yes"></span> <?php _e( 'Add-on succesfully uploaded.', 'walkthecounty' ); ?>
													</p>
													<button
														class="walkthecounty-activate-addon-btn button-primary"
														data-activate="<?php _e( 'Activate Add-on', 'walkthecounty' ); ?>"
														data-activating="<?php _e( 'Activating Add-on...', 'walkthecounty' ); ?>"
													><?php _e( 'Activate Add-on', 'walkthecounty' ); ?></button>
												</div>

												<?php wp_nonce_field( 'walkthecounty-upload-addon', '_walkthecounty_upload_addon' ); ?>

												<p class="walkthecounty-upload-addon-instructions">
													<?php _e( 'Drag a plugin zip file here to upload', 'walkthecounty' ); ?><br>
													<span><?php _e( 'or', 'walkthecounty' ); ?></span>
												</p>

												<label for="walkthecounty-upload-addon-file-select" class="button button-small">
													<?php _e( 'Select a File', 'walkthecounty' ); ?>
												</label>

												<input
													id="walkthecounty-upload-addon-file-select"
													type="file"
													name="addon"
													value="<?php _e( 'Select File', 'walkthecounty' ); ?>"
												/>

											</form>
										</div>
									<?php endif; ?>
									<?php else:
									printf(
										__( 'Because of security reasons you can not upload add-ons from here. Please <a href="%1$s" target="_blank">visit network plugin install page</a> to install add-ons.' ),
										network_admin_url( 'plugin-install.php' )
									);
									?>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>

				<div class="walkthecounty-grid-row<?php echo get_option( 'walkthecounty_licenses', array() ) ? '' : ' walkthecounty-hidden' ?>">
					<div class="walkthecounty-grid-col-12">

						<div class="walkthecounty-licenses-list-header walkthecounty-clearfix">
							<h2><?php _e( 'Licenses and Add-ons', 'walkthecounty' ); ?></h2>

							<?php
							$refresh_status   = WalkTheCounty_License::refresh_license_status();
							$is_allow_refresh = ( $refresh_status['compare'] === date( 'Ymd' ) && 5 > $refresh_status['count'] ) || ( $refresh_status['compare'] < date( 'Ymd' ) );
							$button_title     = __( 'Refresh limit reached. Licenses can only be refreshed 5 times per day.', 'walkthecounty' );
							$local_date       = strtotime( get_date_from_gmt( date( 'Y-m-d H:i:s', $refresh_status['time'] ) ) );
							?>

							<div id="walkthecounty-refresh-button-wrap">
								<button id="walkthecounty-button__refresh-licenses"
								        class="button-secondary"
								        data-activate="<?php _e( 'Refresh All Licenses', 'walkthecounty' ); ?>"
								        data-activating="<?php _e( 'Refreshing All Licenses...', 'walkthecounty' ); ?>"
								        data-nonce="<?php echo wp_create_nonce( 'walkthecounty-refresh-all-licenses' ); ?>"
									<?php echo $is_allow_refresh ? '' : 'disabled'; ?>
									<?php echo $is_allow_refresh ? '' : sprintf( 'title="%1$s"', $button_title ); ?>>
									<?php _e( 'Refresh All Licenses', 'walkthecounty' ); ?>
								</button>
								<span id="walkthecounty-last-refresh-notice">
									<?php echo sprintf(
										__( 'Last refreshed on %1$s at %2$s', 'walkthecounty' ),
										date( walkthecounty_date_format(), $local_date ),
										date( 'g:i a', $local_date )
									); ?>
									</span>
							</div>

							<hr>
							<p class="walkthecounty-field-description"><?php _e('The following list displays your add-ons and their corresponding activation and license statuses.', 'walkthecounty'); ?></p>

						</div>

						<section id="walkthecounty-licenses-container">
							<?php echo WalkTheCounty_License::render_licenses_list(); ?>
						</section>

					</div>
				</div>
			</div>

			<?php
			echo ob_get_clean();
		}
	}

endif;

return new WalkTheCounty_Settings_License();
