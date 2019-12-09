<?php
/**
 * Donations Import Class
 *
 * This class handles donations import.
 *
 * @package     WalkTheCounty
 * @subpackage  Classes/WalkTheCounty_Import_Donations
 * @copyright   Copyright (c) 2017, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.8.14
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WalkTheCounty_Import_Donations' ) ) {

	/**
	 * WalkTheCounty_Import_Donations.
	 *
	 * @since 1.8.14
	 */
	final class WalkTheCounty_Import_Donations {

		/**
		 * Importer type
		 *
		 * @since 1.8.13
		 * @var string
		 */
		private $importer_type = 'import_donations';

		/**
		 * Instance.
		 *
		 * @since
		 * @access private
		 * @var
		 */
		static private $instance;

		/**
		 * Importing donation per page.
		 *
		 * @since 1.8.14
		 *
		 * @var   int
		 */
		public static $per_page = 25;

		/**
		 * Importing donation per page.
		 *
		 * @since 2.1
		 *
		 * @var   int
		 */
		public $is_csv_valid = false;

		/**
		 * Singleton pattern.
		 *
		 * @since
		 * @access private
		 */
		private function __construct() {
			self::$per_page = ! empty( $_GET['per_page'] ) ? absint( $_GET['per_page'] ) : self::$per_page;
		}

		/**
		 * Get instance.
		 *
		 * @since
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
		 * @since 1.8.14
		 *
		 * @return void
		 */
		public function setup() {
			$this->setup_hooks();
		}


		/**
		 * Setup Hooks.
		 *
		 * @since 1.8.14
		 *
		 * @return void
		 */
		private function setup_hooks() {
			if ( ! $this->is_donations_import_page() ) {
				return;
			}

			// Do not render main import tools page.
			remove_action( 'walkthecounty_admin_field_tools_import', array( 'WalkTheCounty_Settings_Import', 'render_import_field', ) );


			// Render donation import page
			add_action( 'walkthecounty_admin_field_tools_import', array( $this, 'render_page' ) );

			// Print the HTML.
			add_action( 'walkthecounty_tools_import_donations_form_start', array( $this, 'html' ), 10 );

			// Run when form submit.
			add_action( 'walkthecounty-tools_save_import', array( $this, 'save' ) );

			add_action( 'walkthecounty-tools_update_notices', array( $this, 'update_notices' ), 11, 1 );

			// Used to add submit button.
			add_action( 'walkthecounty_tools_import_donations_form_end', array( $this, 'submit' ), 10 );
		}

		/**
		 * Update notice
		 *
		 * @since 1.8.14
		 *
		 * @param $messages
		 *
		 * @return mixed
		 */
		public function update_notices( $messages ) {
			if ( ! empty( $_GET['tab'] ) && 'import' === walkthecounty_clean( $_GET['tab'] ) ) {
				unset( $messages['walkthecounty-setting-updated'] );
			}

			return $messages;
		}

		/**
		 * Print submit and nonce button.
		 *
		 * @since 1.8.14
		 */
		public function submit() {
			wp_nonce_field( 'walkthecounty-save-settings', '_walkthecounty-save-settings' );
			?>
			<input type="hidden" class="import-step" id="import-step" name="step"
				   value="<?php echo $this->get_step(); ?>"/>
			<input type="hidden" class="importer-type" value="<?php echo $this->importer_type; ?>"/>
			<?php
		}

		/**
		 * Print the HTML for importer.
		 *
		 * @since 1.8.14
		 */
		public function html() {
			$step = $this->get_step();

			// Show progress.
			$this->render_progress();
			?>
			<section>
				<table
						class="widefat export-options-table walkthecounty-table <?php echo "step-{$step}"; ?> <?php echo( 1 === $step && ! empty( $this->is_csv_valid ) ? 'walkthecounty-hidden' : '' ); ?>  "
						id="<?php echo "step-{$step}"; ?>">
					<tbody>
					<?php
					switch ( $step ) {
						case 1:
							$this->render_media_csv();
							break;

						case 2:
							$this->render_dropdown();
							break;

						case 3:
							$this->start_import();
							break;

						case 4:
							$this->import_success();
					}
					if ( false === $this->check_for_dropdown_or_import() ) {
						?>
						<tr valign="top">
							<th>
								<input type="submit"
									   class="button button-primary button-large button-secondary <?php echo "step-{$step}"; ?>"
									   id="recount-stats-submit"
									   value="
									       <?php
								       /**
								        * Filter to modify donation importer submit button text.
								        *
								        * @since 2.1
								        */
								       echo apply_filters( 'walkthecounty_import_donation_submit_button_text', __( 'Submit', 'walkthecounty' ) );
								       ?>
											"/>
							</th>
							<th>
								<?php
								/**
								 * Action to add submit button description.
								 *
								 * @since 2.1
								 */
								do_action( 'walkthecounty_import_donation_submit_button' );
								?>
							</th>
						</tr>
						<?php
					}
					?>
					</tbody>
				</table>
			</section>
			<?php
		}

		/**
		 * Show success notice
		 *
		 * @since 1.8.14
		 */
		public function import_success() {

			$delete_csv = ( ! empty( $_GET['delete_csv'] ) ? absint( $_GET['delete_csv'] ) : false );
			$csv        = ( ! empty( $_GET['csv'] ) ? absint( $_GET['csv'] ) : false );
			if ( ! empty( $delete_csv ) && ! empty( $csv ) ) {
				wp_delete_attachment( $csv, true );
			}

			$report = walkthecounty_import_donation_report();

			$report_html = array(
				'duplicate_donor'    => array(
					__( '%s duplicate %s detected', 'walkthecounty' ),
					__( '%s duplicate %s detected', 'walkthecounty' ),
					__( 'donor', 'walkthecounty' ),
					__( 'donors', 'walkthecounty' ),
				),
				'create_donor'       => array(
					__( '%s %s created', 'walkthecounty' ),
					__( '%s %s will be created', 'walkthecounty' ),
					__( 'donor', 'walkthecounty' ),
					__( 'donors', 'walkthecounty' ),
				),
				'create_form'        => array(
					__( '%s donation %s created', 'walkthecounty' ),
					__( '%s donation %s will be created', 'walkthecounty' ),
					__( 'form', 'walkthecounty' ),
					__( 'forms', 'walkthecounty' ),
				),
				'duplicate_donation' => array(
					__( '%s duplicate %s detected', 'walkthecounty' ),
					__( '%s duplicate %s detected', 'walkthecounty' ),
					__( 'donation', 'walkthecounty' ),
					__( 'donations', 'walkthecounty' ),
				),
				'create_donation'    => array(
					__( '%s %s imported', 'walkthecounty' ),
					__( '%s %s will be imported', 'walkthecounty' ),
					__( 'donation', 'walkthecounty' ),
					__( 'donations', 'walkthecounty' ),
				),
			);
			$total       = (int) $_GET['total'];
			-- $total;
			$success = (bool) $_GET['success'];
			$dry_run = empty( $_GET['dry_run'] ) ? 0 : absint( $_GET['dry_run'] );
			?>
			<tr valign="top" class="walkthecounty-import-dropdown">
				<th colspan="2">
					<h2>
						<?php
						if ( $success ) {
							if ( $dry_run ) {
								printf(
									_n( 'Dry run import complete! %s donation processed', 'Dry run import complete! %s donations processed', $total, 'walkthecounty' ),
									"<strong>{$total}</strong>"
								);
							} else {
								printf(
									_n( 'Import complete! %s donation processed', 'Import complete! %s donations processed', $total, 'walkthecounty' ),
									"<strong>{$total}</strong>"
								);
							}
						} else {
							printf(
								_n( 'Failed to import %s donation', 'Failed to import %s donations', $total, 'walkthecounty' ),
								"<strong>{$total}</strong>"
							);
						}
						?>
					</h2>

					<?php
					$text      = __( 'Import Donation', 'walkthecounty' );
					$query_arg = array(
						'post_type' => 'walkthecounty_forms',
						'page'      => 'walkthecounty-tools',
						'tab'       => 'import',
					);
					if ( $success ) {


						if ( $dry_run ) {
							$query_arg = array(
								'post_type'     => 'walkthecounty_forms',
								'page'          => 'walkthecounty-tools',
								'tab'           => 'import',
								'importer-type' => 'import_donations',
							);

							$text = __( 'Start Import', 'walkthecounty' );
						} else {
							$query_arg = array(
								'post_type' => 'walkthecounty_forms',
								'page'      => 'walkthecounty-payment-history',
							);
							$text      = __( 'View Donations', 'walkthecounty' );
						}
					}

					foreach ( $report as $key => $value ) {
						if ( array_key_exists( $key, $report_html ) && ! empty( $value ) ) {
							$key_name = $report_html[ $key ][2];
							if ( $value > 1 ) {
								$key_name = $report_html[ $key ][3];
							}
							?>
							<p>
								<?php printf( $report_html[ $key ][ $dry_run ], $value, $key_name ); ?>
							</p>
							<?php
						}
					}
					?>

					<p>
						<a class="button button-large button-secondary"
						   href="<?php echo add_query_arg( $query_arg, admin_url( 'edit.php' ) ); ?>"><?php echo $text; ?></a>
					</p>
				</th>
			</tr>
			<?php
		}

		/**
		 * Will start Import
		 *
		 * @since 1.8.14
		 */
		public function start_import() {
			// Reset the donation form report.
			walkthecounty_import_donation_report_reset();

			$csv         = absint( $_REQUEST['csv'] );
			$delimiter   = ( ! empty( $_REQUEST['delimiter'] ) ? walkthecounty_clean( $_REQUEST['delimiter'] ) : 'csv' );
			$index_start = 1;
			$next        = true;
			$total       = self::get_csv_total( $csv );
			if ( self::$per_page < $total ) {
				$total_ajax = ceil( $total / self::$per_page );
				$index_end  = self::$per_page;
			} else {
				$total_ajax = 1;
				$index_end  = $total;
				$next       = false;
			}
			$current_percentage = 100 / ( $total_ajax + 1 );

			?>
			<tr valign="top" class="walkthecounty-import-dropdown">
				<th colspan="2">
					<h2 id="walkthecounty-import-title"><?php _e( 'Importing', 'walkthecounty' ) ?></h2>
					<p class="walkthecounty-field-description"><?php _e( 'Your donations are now being imported...', 'walkthecounty' ) ?></p>
				</th>
			</tr>

			<tr valign="top" class="walkthecounty-import-dropdown">
				<th colspan="2">
					<span class="spinner is-active"></span>
					<div class="walkthecounty-progress"
						 data-current="1"
						 data-total_ajax="<?php echo absint( $total_ajax ); ?>"
						 data-start="<?php echo absint( $index_start ); ?>"
						 data-end="<?php echo absint( $index_end ); ?>"
						 data-next="<?php echo absint( $next ); ?>"
						 data-total="<?php echo absint( $total ); ?>"
						 data-per_page="<?php echo absint( self::$per_page ); ?>">

						<div style="width: <?php echo (float) $current_percentage; ?>%"></div>
					</div>
					<input type="hidden" value="3" name="step">
					<input type="hidden" value='<?php echo esc_attr( maybe_serialize( $_REQUEST['mapto'] ) ); ?>' name="mapto" class="mapto">
					<input type="hidden" value="<?php echo $csv; ?>" name="csv" class="csv">
					<input type="hidden" value="<?php echo esc_attr( $_REQUEST['mode'] ); ?>" name="mode" class="mode">
					<input type="hidden" value="<?php echo esc_attr( $_REQUEST['create_user'] ); ?>" name="create_user" class="create_user">
					<input type="hidden" value="<?php echo esc_attr( $_REQUEST['delete_csv'] ); ?>" name="delete_csv" class="delete_csv">
					<input type="hidden" value="<?php echo esc_attr( $delimiter ); ?>" name="delimiter">
					<input type="hidden" value="<?php echo absint( $_REQUEST['dry_run'] ); ?>" name="dry_run">
					<input type="hidden" value='<?php echo esc_attr( maybe_serialize( self::get_importer( $csv, 0, $delimiter ) ) ); ?>' name="main_key" class="main_key">
				</th>
			</tr>
			<?php
		}

		/**
		 * Will return true if importing can be started or not else false.
		 *
		 * @since 1.8.14
		 */
		public function check_for_dropdown_or_import() {
			$return = true;
			if ( isset( $_REQUEST['mapto'] ) ) {
				$mapto = (array) $_REQUEST['mapto'];
				if ( false === in_array( 'form_title', $mapto ) && false === in_array( 'form_id', $mapto ) ) {
					WalkTheCounty_Admin_Settings::add_error( 'walkthecounty-import-csv-form', __( 'In order to import donations, a column must be mapped to either the "Donation Form Title" or "Donation Form ID" field. Please map a column to one of those fields.', 'walkthecounty' ) );
					$return = false;
				}

				if ( false === in_array( 'amount', $mapto ) ) {
					WalkTheCounty_Admin_Settings::add_error( 'walkthecounty-import-csv-amount', __( 'In order to import donations, a column must be mapped to the "Amount" field. Please map a column to that field.', 'walkthecounty' ) );
					$return = false;
				}

				if ( false === in_array( 'email', $mapto ) && false === in_array( 'donor_id', $mapto ) ) {
					WalkTheCounty_Admin_Settings::add_error( 'walkthecounty-import-csv-donor', __( 'In order to import donations, a column must be mapped to either the "Donor Email" or "Donor ID" field. Please map a column to that field.', 'walkthecounty' ) );
					$return = false;
				}
			} else {
				$return = false;
			}

			return $return;
		}

		/**
		 * Print the Dropdown option for CSV.
		 *
		 * @since 1.8.14
		 */
		public function render_dropdown() {
			$csv       = (int) $_GET['csv'];
			$delimiter = ( ! empty( $_GET['delimiter'] ) ? walkthecounty_clean( $_GET['delimiter'] ) : 'csv' );

			// TO check if the CSV files that is being add is valid or not if not then redirect to first step again
			if ( ! $this->is_valid_csv( $csv ) ) {
				$url = walkthecounty_import_page_url();
				?>
				<input type="hidden" name="csv_not_valid" class="csv_not_valid" value="<?php echo $url; ?>"/>
				<?php
			} else {
				?>
				<tr valign="top" class="walkthecounty-import-dropdown">
					<th colspan="2">
						<h2 id="walkthecounty-import-title"><?php _e( 'Map CSV fields to donations', 'walkthecounty' ) ?></h2>

						<p class="walkthecounty-import-donation-required-fields-title"><?php _e( 'Required Fields' ); ?></p>

						<p class="walkthecounty-field-description"><?php _e( 'These fields are required for the import to submitted' ); ?></p>

						<ul class="walkthecounty-import-donation-required-fields">
							<li class="walkthecounty-import-donation-required-email"
								title="Please configure all required fields to start the import process.">
								<span class="walkthecounty-import-donation-required-symbol dashicons dashicons-no-alt"></span>
								<span class="walkthecounty-import-donation-required-text">
									<?php
									_e( 'Email Address', 'walkthecounty' );
									?>
								</span>
							</li>

							<li class="walkthecounty-import-donation-required-first"
								title="Please configure all required fields to start the import process.">
								<span class="walkthecounty-import-donation-required-symbol dashicons dashicons-no-alt"></span>
								<span class="walkthecounty-import-donation-required-text">
									<?php
									_e( 'First Name', 'walkthecounty' );
									?>
								</span>
							</li>

							<li class="walkthecounty-import-donation-required-amount"
								title="Please configure all required fields to start the import process.">
								<span class="walkthecounty-import-donation-required-symbol dashicons dashicons-no-alt"></span>
								<span class="walkthecounty-import-donation-required-text">
									<?php
									_e( 'Donation Amount', 'walkthecounty' );
									?>
								</span>
							</li>

							<li class="walkthecounty-import-donation-required-form"
								title="Please configure all required fields to start the import process.">
								<span class="walkthecounty-import-donation-required-symbol dashicons dashicons-no-alt"></span>
								<span class="walkthecounty-import-donation-required-text">
									<?php
									_e( 'Form Title or ID', 'walkthecounty' );
									?>
								</span>
							</li>
						</ul>

						<p class="walkthecounty-field-description"><?php _e( 'Select fields from your CSV file to map against donations fields or to ignore during import.', 'walkthecounty' ) ?></p>
					</th>
				</tr>

				<tr valign="top" class="walkthecounty-import-dropdown">
					<th><b><?php _e( 'Column name', 'walkthecounty' ); ?></b></th>
					<th><b><?php _e( 'Map to field', 'walkthecounty' ); ?></b></th>
				</tr>

				<?php
				$raw_key = $this->get_importer( $csv, 0, $delimiter );
				$mapto   = (array) ( isset( $_REQUEST['mapto'] ) ? $_REQUEST['mapto'] : array() );

				foreach ( $raw_key as $index => $value ) {
					?>
					<tr valign="middle" class="walkthecounty-import-option">
						<th><?php echo $value; ?></th>
						<th>
							<?php
							$this->get_columns( $index, $value, $mapto );
							?>
						</th>
					</tr>
					<?php
				}
			}
		}

		/**
		 * @param $option_value
		 * @param $value
		 *
		 * @return string
		 */
		public function selected( $option_value, $value ) {
			$option_value = strtolower( $option_value );
			$value        = strtolower( $value );

			$selected = '';
			if ( stristr( $value, $option_value ) ) {
				$selected = 'selected';
			} elseif ( strrpos( $value, 'walkthecounty_' ) && stristr( $option_value, __( 'Import as Meta', 'walkthecounty' ) ) ) {
				$selected = 'selected';
			}

			return $selected;
		}

		/**
		 * Print the columns from the CSV.
		 *
		 * @since  1.8.14
		 * @access private
		 *
		 * @param string $index
		 * @param bool   $value
		 * @param array  $mapto
		 *
		 * @return void
		 */
		private function get_columns( $index, $value = false, $mapto = array() ) {
			$default       = walkthecounty_import_default_options();
			$current_mapto = (string) ( ! empty( $mapto[ $index ] ) ? $mapto[ $index ] : '' );
			?>
			<select name="mapto[<?php echo $index; ?>]">
				<?php $this->get_dropdown_option_html( $default, $current_mapto, $value ); ?>

				<optgroup label="<?php _e( 'Donations', 'walkthecounty' ); ?>">
					<?php
					$this->get_dropdown_option_html( walkthecounty_import_donations_options(), $current_mapto, $value );
					?>
				</optgroup>

				<optgroup label="<?php _e( 'Donors', 'walkthecounty' ); ?>">
					<?php
					$this->get_dropdown_option_html( walkthecounty_import_donor_options(), $current_mapto, $value );
					?>
				</optgroup>

				<optgroup label="<?php _e( 'Forms', 'walkthecounty' ); ?>">
					<?php
					$this->get_dropdown_option_html( walkthecounty_import_donation_form_options(), $current_mapto, $value );
					?>
				</optgroup>

				<?php
				/**
				 * Fire the action
				 * You can use this filter to add new options.
				 *
				 * @since 1.8.15
				 */
				do_action( 'walkthecounty_import_dropdown_option', $index, $value, $mapto, $current_mapto );
				?>
			</select>
			<?php
		}

		/**
		 * Print the option html for select in importer
		 *
		 * @since  1.8.15
		 * @access public
		 *
		 * @param  array $options
		 * @param  string $current_mapto
		 * @param bool $value
		 *
		 * @return void
		 */
		public function get_dropdown_option_html( $options, $current_mapto, $value = false ) {

			foreach ( $options as $option => $option_value ) {
				$ignore = array();
				if ( isset( $option_value['ignore'] ) && is_array( $option_value['ignore'] ) ) {
					$ignore = $option_value['ignore'];
					unset( $option_value['ignore'] );
				}

				$option_value_texts = (array) $option_value;
				$option_text        = $option_value_texts[0];

				$checked = ( ( $current_mapto === $option ) ? 'selected' : false );
				if ( empty( $checked ) && ! in_array( $value, $ignore ) ) {
					foreach ( $option_value_texts as $option_value_text ) {
						$checked = $this->selected( $option_value_text, $value );
						if ( $checked ) {
							break;
						}
					}
				}

				echo sprintf(
					'<option value="%1$s" %2$s >%3$s</option>',
					$option,
					$checked,
					$option_text
				);
			}
		}

		/**
		 * Get column count of csv file.
		 *
		 * @since 1.8.14
		 *
		 * @param $file_id
		 *
		 * @return bool|int
		 */
		public function get_csv_total( $file_id ) {
			$total = false;
			if ( $file_id ) {
				$file_dir = get_attached_file( $file_id );
				if ( $file_dir ) {
					$total = $this->get_csv_data_from_file_dir( $file_dir );
				}
			}

			return $total;
		}

		/**
		 * Get data from File
		 *
		 * @since 2.1
		 *
		 * @param $file_dir
		 *
		 * @return bool|int
		 */
		public function get_csv_data_from_file_dir( $file_dir ) {
			$total = false;
			if ( $file_dir ) {
				$file = new SplFileObject( $file_dir, 'r' );
				$file->seek( PHP_INT_MAX );
				$total = $file->key() + 1;
			}

			return $total;
		}

		/**
		 * Get the CSV fields title from the CSV.
		 *
		 * @since 1.8.14
		 *
		 * @param (int) $file_id
		 * @param int    $index
		 * @param string $delimiter
		 *
		 * @return array|bool $raw_data title of the CSV file fields
		 */
		public function get_importer( $file_id, $index = 0, $delimiter = 'csv' ) {
			/**
			 * Filter to modify delimiter of Import.
			 *
			 * @since 1.8.14
			 *
			 * Return string $delimiter.
			 */
			$delimiter = (string) apply_filters( 'walkthecounty_import_delimiter_set', $delimiter );

			$raw_data = false;
			$file_dir = get_attached_file( $file_id );
			if ( $file_dir ) {
				if ( false !== ( $handle = fopen( $file_dir, 'r' ) ) ) {
					$raw_data = fgetcsv( $handle, $index, $delimiter );
					// Remove BOM signature from the first item.
					if ( isset( $raw_data[0] ) ) {
						$raw_data[0] = $this->remove_utf8_bom( $raw_data[0] );
					}
				}
			}

			return $raw_data;
		}

		/**
		 * Remove UTF-8 BOM signature.
		 *
		 * @since 1.8.14
		 *
		 * @param  string $string String to handle.
		 *
		 * @return string
		 */
		public function remove_utf8_bom( $string ) {
			if ( 'efbbbf' === substr( bin2hex( $string ), 0, 6 ) ) {
				$string = substr( $string, 3 );
			}

			return $string;
		}

		/**
		 * Is used to show the process when user upload the donor form.
		 *
		 * @since 1.8.14
		 */
		public function render_progress() {
			$step = $this->get_step();
			?>
			<ol class="walkthecounty-progress-steps">
				<li class="<?php echo( 1 === $step ? 'active' : '' ); ?>">
					<?php _e( 'Upload CSV file', 'walkthecounty' ); ?>
				</li>
				<li class="<?php echo( 2 === $step ? 'active' : '' ); ?>">
					<?php _e( 'Column mapping', 'walkthecounty' ); ?>
				</li>
				<li class="<?php echo( 3 === $step ? 'active' : '' ); ?>">
					<?php _e( 'Import', 'walkthecounty' ); ?>
				</li>
				<li class="<?php echo( 4 === $step ? 'active' : '' ); ?>">
					<?php _e( 'Done!', 'walkthecounty' ); ?>
				</li>
			</ol>
			<?php
		}

		/**
		 * Will return the import step.
		 *
		 * @since 1.8.14
		 *
		 * @return int $step on which step doest the import is on.
		 */
		public function get_step() {
			$step    = (int) ( isset( $_REQUEST['step'] ) ? walkthecounty_clean( $_REQUEST['step'] ) : 0 );
			$on_step = 1;

			if ( empty( $step ) || 1 === $step ) {
				$on_step = 1;
			} elseif ( $this->check_for_dropdown_or_import() ) {
				$on_step = 3;
			} elseif ( 2 === $step ) {
				$on_step = 2;
			} elseif ( 4 === $step ) {
				$on_step = 4;
			}

			return $on_step;
		}

		/**
		 * Render donations import page
		 *
		 * @since 1.8.14
		 */
		public function render_page() {
			include_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/tools/views/html-admin-page-import-donations.php';
		}

		/**
		 * Print Dry Run HTML on donation import page
		 *
		 * @since 2.1
		 */
		public function walkthecounty_import_donation_submit_button_render_media_csv() {
			$dry_run = isset( $_POST['dry_run'] ) ? absint( $_POST['dry_run'] ) : 1;
			?>
			<div>
				<label for="dry_run">
					<input type="hidden" name="dry_run" value="0"/>
					<input type="checkbox" name="dry_run" id="dry_run" class="dry_run"
						   value="1" <?php checked( 1, $dry_run ); ?> >
					<strong><?php _e( 'Dry Run', 'walkthecounty' ); ?></strong>
				</label>
				<p class="walkthecounty-field-description">
					<?php
					_e( 'Preview what the import would look like without making any default changes to your site or your database.', 'walkthecounty' );
					?>
				</p>
			</div>
			<?php
		}

		/**
		 * Change submit button text on first step of importing donation.
		 *
		 * @since 2.1
		 *
		 * @param $text
		 *
		 * @return string
		 */
		function walkthecounty_import_donation_submit_text_render_media_csv( $text ) {
			return __( 'Begin Import', 'walkthecounty' );
		}

		/**
		 * Add CSV upload HTMl
		 *
		 * Print the html of the file upload from which CSV will be uploaded.
		 *
		 * @since 1.8.14
		 * @return void
		 */
		public function render_media_csv() {
			add_filter( 'walkthecounty_import_donation_submit_button_text', array(
				$this,
				'walkthecounty_import_donation_submit_text_render_media_csv'
			) );
			add_action( 'walkthecounty_import_donation_submit_button', array(
				$this,
				'walkthecounty_import_donation_submit_button_render_media_csv'
			) );
			?>
			<tr valign="top">
				<th colspan="2">
					<h2 id="walkthecounty-import-title"><?php _e( 'Import donations from a CSV file', 'walkthecounty' ) ?></h2>
					<p class="walkthecounty-field-description"><?php _e( 'This tool allows you to import or add donation data to your walkthecounty form(s) via a CSV file.', 'walkthecounty' ) ?></p>
				</th>
			</tr>
			<?php
			$csv         = ( isset( $_POST['csv'] ) ? walkthecounty_clean( $_POST['csv'] ) : '' );
			$csv_id      = ( isset( $_POST['csv_id'] ) ? walkthecounty_clean( $_POST['csv_id'] ) : '' );
			$delimiter   = ( isset( $_POST['delimiter'] ) ? walkthecounty_clean( $_POST['delimiter'] ) : 'csv' );
			$mode        = empty( $_POST['mode'] ) ?
				'disabled' :
				( walkthecounty_is_setting_enabled( walkthecounty_clean( $_POST['mode'] ) ) ? 'enabled' : 'disabled' );
			$create_user = empty( $_POST['create_user'] ) ?
				'disabled' :
				( walkthecounty_is_setting_enabled( walkthecounty_clean( $_POST['create_user'] ) ) ? 'enabled' : 'disabled' );
			$delete_csv  = empty( $_POST['delete_csv'] ) ?
				'enabled' :
				( walkthecounty_is_setting_enabled( walkthecounty_clean( $_POST['delete_csv'] ) ) ? 'enabled' : 'disabled' );

			// Reset csv and csv_id if csv
			if ( empty( $csv_id ) || ! $this->is_valid_csv( $csv_id, $csv ) ) {
				$csv_id = $csv = '';
			}
			$per_page = isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : self::$per_page;

			$sample_file_text = sprintf(
				'%s <a href="%s">%s</a>.',
				__( 'Download the sample file', 'walkthecounty' ),
				esc_url( WALKTHECOUNTY_PLUGIN_URL . 'sample-data/sample-data.csv' ),
				__( 'here', 'walkthecounty' )
			);

			$csv_description = sprintf(
				'%1$s %2$s',
				__( 'The file must be a Comma Seperated Version (CSV) file type only.', 'walkthecounty' ),
				$sample_file_text
			);

			$settings = array(
				array(
					'id'          => 'csv',
					'name'        => __( 'Choose a CSV file:', 'walkthecounty' ),
					'type'        => 'file',
					'attributes'  => array( 'editing' => 'false', 'library' => 'text' ),
					'description' => $csv_description,
					'fvalue'      => 'url',
					'default'     => $csv,
				),
				array(
					'id'    => 'csv_id',
					'type'  => 'hidden',
					'value' => $csv_id,
				),
				array(
					'id'          => 'delimiter',
					'name'        => __( 'CSV Delimiter:', 'walkthecounty' ),
					'description' => __( 'In case your CSV file supports a different type of separator (or delimiter) -- like a tab or space -- you can set that here.', 'walkthecounty' ),
					'default'     => $delimiter,
					'type'        => 'select',
					'options'     => array(
						'csv'                  => __( 'Comma', 'walkthecounty' ),
						'tab-separated-values' => __( 'Tab', 'walkthecounty' ),
					),
				),
				array(
					'id'          => 'mode',
					'name'        => __( 'Test Mode:', 'walkthecounty' ),
					'description' => __( 'Select whether you would like these donations to be marked as "test" donations within the database. By default, they will be marked as live donations.', 'walkthecounty' ),
					'default'     => $mode,
					'type'        => 'radio_inline',
					'options'     => array(
						'enabled'  => __( 'Enabled', 'walkthecounty' ),
						'disabled' => __( 'Disabled', 'walkthecounty' ),
					),
				),
				array(
					'id'          => 'create_user',
					'name'        => __( 'Create WP users for new donors:', 'walkthecounty' ),
					'description' => __( 'The importer can create WordPress user accounts based on the names and email addresses of the donations in your CSV file. Enable this option if you\'d like the importer to do that.', 'walkthecounty' ),
					'default'     => $create_user,
					'type'        => 'radio_inline',
					'options'     => array(
						'enabled'  => __( 'Enabled', 'walkthecounty' ),
						'disabled' => __( 'Disabled', 'walkthecounty' ),
					),
				),
				array(
					'id'          => 'delete_csv',
					'name'        => __( 'Delete CSV after import:', 'walkthecounty' ),
					'description' => __( 'Your CSV file will be uploaded via the WordPress Media Library. It\'s a good idea to delete it after the import is finished so that your sensitive data is not accessible on the web. Disable this only if you plan to delete the file manually later.', 'walkthecounty' ),
					'default'     => $delete_csv,
					'type'        => 'radio_inline',
					'options'     => array(
						'enabled'  => __( 'Enabled', 'walkthecounty' ),
						'disabled' => __( 'Disabled', 'walkthecounty' ),
					),
				),
				array(
					'id'          => 'per_page',
					'name'        => __( 'Process Rows Per Batch:', 'walkthecounty' ),
					'type'        => 'number',
					'description' => __( 'Determine how many rows you would like to import per cycle.', 'walkthecounty' ),
					'default'     => $per_page,
					'class'       => 'walkthecounty-text-small',
				),
			);

			$settings = apply_filters( 'walkthecounty_import_file_upload_html', $settings );

			if ( empty( $this->is_csv_valid ) ) {
				WalkTheCounty_Admin_Settings::output_fields( $settings, 'walkthecounty_settings' );
			} else {
				?>
				<input type="hidden" name="is_csv_valid" class="is_csv_valid"
					   value="<?php echo $this->is_csv_valid; ?>">
				<?php
			}
		}

		/**
		 * Run when user click on the submit button.
		 *
		 * @since 1.8.14
		 */
		public function save() {
			// Get the current step.
			$step = $this->get_step();

			// Validation for first step.
			if ( 1 === $step ) {
				$csv_id = absint( $_POST['csv_id'] );

				if ( $this->is_valid_csv( $csv_id, esc_url( $_POST['csv'] ) ) ) {

					$url = walkthecounty_import_page_url( (array) apply_filters( 'walkthecounty_import_step_two_url', array(
						'step'          => '2',
						'importer-type' => $this->importer_type,
						'csv'           => $csv_id,
						'delimiter'     => isset( $_REQUEST['delimiter'] ) ? walkthecounty_clean( $_REQUEST['delimiter'] ) : 'csv',
						'mode'          => empty( $_POST['mode'] ) ?
							'0' :
							( walkthecounty_is_setting_enabled( walkthecounty_clean( $_POST['mode'] ) ) ? '1' : '0' ),
						'create_user'   => empty( $_POST['create_user'] ) ?
							'0' :
							( walkthecounty_is_setting_enabled( walkthecounty_clean( $_POST['create_user'] ) ) ? '1' : '0' ),
						'delete_csv'    => empty( $_POST['delete_csv'] ) ?
							'1' :
							( walkthecounty_is_setting_enabled( walkthecounty_clean( $_POST['delete_csv'] ) ) ? '1' : '0' ),
						'per_page'      => isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : self::$per_page,
						'dry_run'       => isset( $_POST['dry_run'] ) ? absint( $_POST['dry_run'] ) : 0,
					) ) );

					$this->is_csv_valid = $url;
				}
			}
		}

		/**
		 * Check if user uploaded csv is valid or not.
		 *
		 * @since  1.8.14
		 * @access public
		 *
		 * @param mixed  $csv       ID of the CSV files.
		 * @param string $match_url ID of the CSV files.
		 *
		 * @return bool $has_error CSV is valid or not.
		 */
		private function is_valid_csv( $csv = false, $match_url = '' ) {
			$is_valid_csv = true;

			if ( $csv ) {
				$csv_url = wp_get_attachment_url( $csv );

				$delimiter = ( ! empty( $_REQUEST['delimiter'] ) ? walkthecounty_clean( $_REQUEST['delimiter'] ) : 'csv' );

				if (
					! $csv_url ||
					( ! empty( $match_url ) && ( $csv_url !== $match_url ) ) ||
					( ( $mime_type = get_post_mime_type( $csv ) ) && ! strpos( $mime_type, $delimiter ) )
				) {
					$is_valid_csv = false;
					WalkTheCounty_Admin_Settings::add_error( 'walkthecounty-import-csv', __( 'Please upload or provide a valid CSV file.', 'walkthecounty' ) );
				}
			} else {
				$is_valid_csv = false;
				WalkTheCounty_Admin_Settings::add_error( 'walkthecounty-import-csv', __( 'Please upload or provide a valid CSV file.', 'walkthecounty' ) );
			}

			return $is_valid_csv;
		}


		/**
		 * Render report import field
		 *
		 * @since  1.8.14
		 * @access public
		 *
		 * @param $field
		 * @param $option_value
		 */
		public function render_import_field( $field, $option_value ) {
			include_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/tools/views/html-admin-page-imports.php';
		}

		/**
		 * Get if current page import donations page or not
		 *
		 * @since 1.8.14
		 * @return bool
		 */
		private function is_donations_import_page() {
			return 'import' === walkthecounty_get_current_setting_tab() &&
			       isset( $_GET['importer-type'] ) &&
			       $this->importer_type === walkthecounty_clean( $_GET['importer-type'] );
		}
	}

	WalkTheCounty_Import_Donations::get_instance()->setup();
}
