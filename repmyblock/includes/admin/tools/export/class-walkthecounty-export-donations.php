<?php
/**
 * WalkTheCounty Export Donations Settings
 *
 * @package     WalkTheCounty
 * @subpackage  Classes/WalkTheCounty_Settings_Data
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WalkTheCounty_Export_Donations' ) ) {

	/**
	 * Class WalkTheCounty_Export_Donations
	 *
	 * @sine 2.1
	 */
	final class WalkTheCounty_Export_Donations {

		/**
		 * Importer type
		 *
		 * @since 2.1
		 *
		 * @var string
		 */
		private $exporter_type = 'export_donations';

		/**
		 * Instance.
		 *
		 * @since 2.1
		 */
		static private $instance;

		/**
		 * Singleton pattern.
		 *
		 * @since 2.1
		 *
		 * @access private
		 */
		private function __construct() {
		}

		/**
		 * Get instance.
		 *
		 * @since 2.1
		 *
		 * @access public
		 *
		 * @return static
		 */
		public static function get_instance() {
			if ( null === static::$instance ) {
				self::$instance = new static();
			}

			return self::$instance;
		}

		/**
		 * Setup
		 *
		 * @since 2.1
		 *
		 * @return void
		 */
		public function setup() {
			$this->setup_hooks();
		}


		/**
		 * Setup Hooks.
		 *
		 * @since 2.1
		 *
		 * @return void
		 */
		private function setup_hooks() {
			if ( ! $this->is_donations_export_page() ) {
				return;
			}

			// Do not render main export tools page.
			remove_action(
				'walkthecounty_admin_field_tools_export',
				array(
					'WalkTheCounty_Settings_Export',
					'render_export_field',
				),
				10
			);

			// Render donation export page
			add_action( 'walkthecounty_admin_field_tools_export', array( $this, 'render_page' ) );

			// Print the HTML.
			add_action( 'walkthecounty_tools_export_donations_form_start', array( $this, 'html' ) );
		}

		/**
		 * Filter to modity the Taxonomy args
		 *
		 * @since 2.1
		 *
		 * @param array $args args for Taxonomy
		 *
		 * @return array args for Taxonomy
		 */
		function walkthecounty_forms_taxonomy_dropdown( $args ) {
			$args['number'] = 30;

			return $args;
		}

		/**
		 * Print the HTML for core setting exporter.
		 *
		 * @since 2.1
		 */
		public function html() {
			?>
			<section id="walkthecounty-export-donations">
				<table class="widefat export-options-table walkthecounty-table">
					<tbody>
					<tr class="top">
						<td colspan="2">
							<h2 id="walkthecounty-export-title"><?php _e( 'Export Donation History and Custom Fields to CSV', 'walkthecounty' ); ?></h2>
							<p class="walkthecounty-field-description"><?php _e( 'Download an export of donors for specific donation forms with the option to include custom fields.', 'walkthecounty' ); ?></p>
						</td>
					</tr>

					<?php
					if ( walkthecounty_is_setting_enabled( walkthecounty_get_option( 'categories' ) ) ) {
						add_filter( 'walkthecounty_forms_category_dropdown', array( $this, 'walkthecounty_forms_taxonomy_dropdown' ) );
						?>
						<tr>
							<td scope="row" class="row-title">
								<label
									for="walkthecounty_forms_categories"><?php _e( 'Filter by Categories:', 'walkthecounty' ); ?></label>
							</td>
							<td class="walkthecounty-field-wrap">
								<div class="walkthecounty-clearfix">
									<?php
									echo WalkTheCounty()->html->category_dropdown(
										'walkthecounty_forms_categories[]',
										0,
										array(
											'id'          => 'walkthecounty_forms_categories',
											'class'       => 'walkthecounty_forms_categories',
											'chosen'      => true,
											'multiple'    => true,
											'selected'    => array(),
											'show_option_all' => false,
											'placeholder' => __( 'Choose one or more from categories', 'walkthecounty' ),
											'data'        => array( 'search-type' => 'categories' ),
										)
									);
									?>
								</div>
							</td>
						</tr>
						<?php
						remove_filter( 'walkthecounty_forms_category_dropdown', array( $this, 'walkthecounty_forms_taxonomy_dropdown' ) );
					}

					if ( walkthecounty_is_setting_enabled( walkthecounty_get_option( 'tags' ) ) ) {
						add_filter( 'walkthecounty_forms_tag_dropdown', array( $this, 'walkthecounty_forms_taxonomy_dropdown' ) );
						?>
						<tr>
							<td scope="row" class="row-title">
								<label
									for="walkthecounty_forms_tags"><?php _e( 'Filter by Tags:', 'walkthecounty' ); ?></label>
							</td>
							<td class="walkthecounty-field-wrap">
								<div class="walkthecounty-clearfix">
									<?php
									echo WalkTheCounty()->html->tags_dropdown(
										'walkthecounty_forms_tags[]',
										0,
										array(
											'id'          => 'walkthecounty_forms_tags',
											'class'       => 'walkthecounty_forms_tags',
											'chosen'      => true,
											'multiple'    => true,
											'selected'    => array(),
											'show_option_all' => false,
											'placeholder' => __( 'Choose one or more from tags', 'walkthecounty' ),
											'data'        => array( 'search-type' => 'tags' ),
										)
									);
									?>
								</div>
							</td>
						</tr>
						<?php
						remove_filter( 'walkthecounty_forms_tag_dropdown', array( $this, 'walkthecounty_forms_taxonomy_dropdown' ) );
					}
					?>

					<tr class="walkthecounty-export-donation-form">
						<td scope="row" class="row-title">
							<label
								for="walkthecounty_payment_form_select"><?php _e( 'Filter by Donation Form:', 'walkthecounty' ); ?></label>
						</td>
						<td class="walkthecounty-field-wrap">
							<div class="walkthecounty-clearfix">
								<?php
								$args = array(
									'name'        => 'forms',
									'id'          => 'walkthecounty-payment-form-select',
									'class'       => 'walkthecounty-width-25em',
									'chosen'      => true,
									'placeholder' => __( 'All Forms', 'walkthecounty' ),
									'data'        => array( 'no-form' => __( 'No donation forms found', 'walkthecounty' ) ),
								);
								echo WalkTheCounty()->html->forms_dropdown( $args );
								?>

								<input type="hidden" name="form_ids" class="form_ids" />
							</div>
						</td>
					</tr>

					<tr>
						<td scope="row" class="row-title">
							<label for="walkthecounty-payment-export-start"><?php _e( 'Filter by Date:', 'walkthecounty' ); ?></label>
						</td>
						<td class="walkthecounty-field-wrap">
							<div class="walkthecounty-clearfix">
								<?php
								$args = array(
									'id'           => 'walkthecounty-payment-export-start',
									'name'         => 'start',
									'placeholder'  => __( 'Start Date', 'walkthecounty' ),
									'autocomplete' => 'off',
								);
								echo WalkTheCounty()->html->date_field( $args );
								?>
								<?php
								$args = array(
									'id'           => 'walkthecounty-payment-export-end',
									'name'         => 'end',
									'placeholder'  => __( 'End Date', 'walkthecounty' ),
									'autocomplete' => 'off'
								);
								echo WalkTheCounty()->html->date_field( $args );
								?>
							</div>
						</td>
					</tr>

					<tr>
						<td scope="row" class="row-title">
							<label
								for="walkthecounty-export-donations-status"><?php _e( 'Filter by Status:', 'walkthecounty' ); ?></label>
						</td>
						<td>
							<div class="walkthecounty-clearfix">
								<select name="status" id="walkthecounty-export-donations-status">
									<option value="any"><?php _e( 'All Statuses', 'walkthecounty' ); ?></option>
									<?php
									$statuses = walkthecounty_get_payment_statuses();
									foreach ( $statuses as $status => $label ) {
										echo '<option value="' . $status . '">' . $label . '</option>';
									}
									?>
								</select>
							</div>
						</td>
					</tr>

					<?php
					/**
					 * Add fields columns that are going to be exported when exporting donations
					 *
					 * @since 2.1
					 */
					do_action( 'walkthecounty_export_donation_fields' );
					?>

					<tr class="end">
						<td>
						</td>
						<td>
							<?php wp_nonce_field( 'walkthecounty_ajax_export', 'walkthecounty_ajax_export' ); ?>
							<input type="hidden" name="walkthecounty-export-class" value="WalkTheCounty_Export_Donations_CSV"/>
							<input type="button" value="<?php esc_attr_e( 'Deselect All Fields', 'walkthecounty' ); ?>" data-value="<?php esc_attr_e( 'Select All Fields', 'walkthecounty' ); ?>" class="walkthecounty-toggle-checkbox-selection button button-secondary">
							<input type="submit" value="<?php esc_attr_e( 'Generate CSV', 'walkthecounty' ); ?>" class="walkthecounty-export-donation-button button button-primary">
							<div class="add-notices"></div>
						</td>
					</tr>
					</tbody>
				</table>
			</section>
			<?php
		}

		/**
		 * Render donations export page
		 *
		 * @since 2.1
		 */
		public function render_page() {
			/**
			 * Fires before displaying the export div tools.
			 *
			 * @since 2.1
			 */
			do_action( 'walkthecounty_tools_export_donations_main_before' );
			?>
			<div id="poststuff" class="walkthecounty-clearfix">
				<div class="postbox">
					<h1 class="walkthecounty-export-h1" align="center"><?php _e( 'Export Donations', 'walkthecounty' ); ?></h1>
					<div class="inside walkthecounty-tools-setting-page-export walkthecounty-export_donations">
						<?php
						/**
						 * Fires before from start.
						 *
						 * @since 2.1
						 */
						do_action( 'walkthecounty_tools_export_donations_form_before_start' );
						?>
						<form method="post"
						      id="walkthecounty-export_donations-form"
							  class="walkthecounty-export-form tools-setting-page-export tools-setting-page-export"
							  enctype="multipart/form-data">

							<?php
							/**
							 * Fires just after form start.
							 *
							 * @since 2.1
							 */
							do_action( 'walkthecounty_tools_export_donations_form_start' );
							?>

							<?php
							/**
							 * Fires just after before form end.
							 *
							 * @since 2.1
							 */
							do_action( 'walkthecounty_tools_export_donations_form_end' );
							?>
						</form>
						<?php
						/**
						 * Fires just after form end.
						 *
						 * @since 2.1
						 */
						do_action( 'walkthecounty_tools_export_donations_form_after_end' );
						?>
					</div><!-- .inside -->
				</div><!-- .postbox -->
			</div><!-- #poststuff -->
			<?php
			/**
			 * Fires after displaying the export div tools.
			 *
			 * @since 2.1
			 */
			do_action( 'walkthecounty_tools_export_donations_main_after' );
		}

		/**
		 * Get if current page export donations page or not
		 *
		 * @since 2.1
		 *
		 * @return bool
		 */
		private function is_donations_export_page() {
			return 'export' === walkthecounty_get_current_setting_tab() && isset( $_GET['type'] ) && $this->exporter_type === walkthecounty_clean( $_GET['type'] );
		}
	}

	WalkTheCounty_Export_Donations::get_instance()->setup();
}
