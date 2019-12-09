<?php
/**
 * WALKTHECOUNTY WP_CLI commands
 *
 * @package walkthecounty
 * @since   1.7
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Add walkthecounty command.
WP_CLI::add_command( 'walkthecounty', 'WALKTHECOUNTY_CLI_COMMAND' );


/**
 * Work with WalkTheCounty through WP-CLI
 *
 * Adds CLI support to WalkTheCounty through WP-CLI
 *
 * @since 1.7
 */
class WALKTHECOUNTY_CLI_COMMAND {

	/**
	 * This param uses to count process/step inside loop.
	 *
	 * @var int $counter Counter.
	 */
	private static $counter;

	/**
	 * This helps to get information walkthecounty plugin data.
	 *
	 * @var WalkTheCounty_API Object.
	 */
	private $api;

	/**
	 * This helps to get unique name.
	 *
	 * @since 1.8.17
	 * @var array
	 */
	private $new_donor_names = array();


	/**
	 * WALKTHECOUNTY_CLI_Command constructor.
	 */
	public function __construct() {
		$this->api = new WalkTheCounty_API();
	}


	/**
	 * Get WalkTheCounty logo
	 *
	 * ## OPTIONS
	 *
	 * None. for a fun surprise.
	 *
	 * ## EXAMPLES
	 *
	 * wp walkthecounty logo
	 *
	 * @since         1.7
	 * @access        public
	 *
	 * @param        string $args       Command Data.
	 * @param        array  $assoc_args List of command data.
	 *
	 * @return        void
	 *
	 * @subcommand    ascii
	 */
	public function ascii( $args, $assoc_args ) {
		WP_CLI::log( file_get_contents( WALKTHECOUNTY_PLUGIN_DIR . 'assets/dist/images/walkthecounty-ascii-logo.txt' ) );
	}


	/**
	 * Get WalkTheCounty details
	 *
	 * ## OPTIONS
	 *
	 * None. Returns basic info regarding your WalkTheCounty instance.
	 *
	 * ## EXAMPLES
	 *
	 * wp walkthecounty details
	 *
	 * @since         1.7
	 * @access        public
	 *
	 * @param        string $args       Command Data.
	 * @param        array  $assoc_args List of command data.
	 *
	 * @return        void
	 *
	 * @subcommand    details
	 */
	public function details( $args, $assoc_args ) {

		/**
		 * Plugin Information
		 */
		WP_CLI::log( $this->color_message( __( 'WalkTheCountyWP Version: ', 'walkthecounty' ) ) . WALKTHECOUNTY_VERSION );

		/**
		 * General Information.
		 */
		WP_CLI::log( "\n####   " . $this->color_message( __( 'General information', 'walkthecounty' ) ) . '   ####' );

		$success_page = walkthecounty_get_option( 'success_page' );
		$failure_page = walkthecounty_get_option( 'failure_page' );
		$history_page = walkthecounty_get_option( 'history_page' );

		WP_CLI::log( $this->color_message( sprintf( __( 'Success Page: ', 'walkthecounty' ) ) ) . ( $success_page ? "[{$success_page}] " . get_permalink( $success_page ) : __( 'Not Set', 'walkthecounty' ) ) );
		WP_CLI::log( $this->color_message( __( 'Failed Donation Page: ', 'walkthecounty' ) ) . ( $failure_page ? "[{$failure_page}] " . get_permalink( $failure_page ) : __( 'Not Set', 'walkthecounty' ) ) );
		WP_CLI::log( $this->color_message( __( 'Donation History Page: ', 'walkthecounty' ) ) . ( $history_page ? "[{$history_page}] " . get_permalink( $history_page ) : __( 'Not Set', 'walkthecounty' ) ) );
		WP_CLI::log( $this->color_message( __( 'Country: ', 'walkthecounty' ) ) . walkthecounty_get_country() );

		/**
		 * Currency Information.
		 */
		$default_gateway = walkthecounty_get_option( 'default_gateway' );

		WP_CLI::log( "\n####   " . $this->color_message( __( 'Currency Information', 'walkthecounty' ) ) . '   ####' );

		WP_CLI::log( $this->color_message( __( 'Currency: ', 'walkthecounty' ), walkthecounty_get_currency() ) );
		WP_CLI::log( $this->color_message( __( 'Currency Position: ', 'walkthecounty' ), walkthecounty_get_currency_position() ) );
		WP_CLI::log( $this->color_message( __( 'Thousand Separator: ', 'walkthecounty' ), walkthecounty_get_price_thousand_separator() ) );
		WP_CLI::log( $this->color_message( __( 'Decimal Separator: ', 'walkthecounty' ), walkthecounty_get_price_decimal_separator() ) );
		WP_CLI::log( $this->color_message( __( 'Number of Decimals: ', 'walkthecounty' ), walkthecounty_get_price_decimals() ) );
		WP_CLI::log( $this->color_message( __( 'Test Mode: ', 'walkthecounty' ), ( walkthecounty_get_option( 'test_mode' ) ? __( 'Yes', 'walkthecounty' ) : __( 'No', 'walkthecounty' ) ) ) );
		WP_CLI::log( $this->color_message( __( 'Default Gateway: ', 'walkthecounty' ), ( $default_gateway ? $default_gateway : __( 'Not Set', 'walkthecounty' ) ) ) );

		// Payment gateways Information.
		$gateways = walkthecounty_get_ordered_payment_gateways( walkthecounty_get_payment_gateways() );
		WP_CLI::log( $this->color_message( __( 'Enabled Gateways: ', 'walkthecounty' ) ) );

		if ( ! empty( $gateways ) ) {
			self::$counter = 1;
			foreach ( $gateways as $gateway ) {
				WP_CLI::log( '  ' . $this->color_message( self::$counter, $gateway['admin_label'] ) );
				self::$counter ++;
			}
		} else {
			WP_CLI::log( __( 'Not any payment gateways found', 'walkthecounty' ) );
		}
	}


	/**
	 * Get the forms currently posted on your WalkTheCounty site
	 *
	 * ## OPTIONS
	 *
	 * [--id=<form_id>]
	 * : A specific form ID to retrieve
	 *
	 * [--number=<form_count>]
	 * : Number of form to retrieve
	 *
	 * ## EXAMPLES
	 *
	 * wp walkthecounty forms
	 * wp walkthecounty forms --id=103
	 * wp walkthecounty forms --number=103
	 *
	 * @since         1.7
	 * @access        public
	 *
	 * @param        string $args       Command Data.
	 * @param        array  $assoc_args List of command data.
	 *
	 * @return        void
	 *
	 * @subcommand    forms
	 */
	public function forms( $args, $assoc_args ) {
		global $wp_query;
		$form_id = isset( $assoc_args ) && array_key_exists( 'id', $assoc_args ) ? absint( $assoc_args['id'] ) : false;
		$number  = isset( $assoc_args ) && array_key_exists( 'number', $assoc_args ) ? absint( $assoc_args['number'] ) : 10;
		$start   = time();

		// Cache previous number query var.
		$is_set_number = $cache_per_page = false;
		if ( isset( $wp_query->query_vars['number'] ) ) {
			$cache_per_page = $wp_query->query_vars['number'];
			$is_set_number  = true;
		}

		// Change number query var.
		$wp_query->query_vars['number'] = $number;

		// Get forms.
		$forms = $form_id ? $this->api->get_forms( $form_id ) : $this->api->get_forms();

		// Reset number query var.
		if ( $is_set_number ) {
			$wp_query->query_vars['number'] = $cache_per_page;
		}

		// Bailout.
		if ( array_key_exists( 'error', $forms ) ) {

			WP_CLI::warning( $forms['error'] );

			return;
		} elseif ( empty( $forms['forms'] ) ) {

			WP_CLI::error( __( 'No forms found.', 'walkthecounty' ) );

			return;
		}

		// Param to check if form typeis already showed or not.
		$is_show_form_type = false;

		if ( 1 === count( $forms ) && $form_id ) {
			// Show single form.
			foreach ( $forms['forms'][0] as $key => $info ) {
				switch ( $key ) {
					case 'stats':
						$this->color_main_heading( ucfirst( $key ) );

						foreach ( $info as $heading => $data ) {
							$this->color_sub_heading( ucfirst( $heading ) );
							switch ( $heading ) {
								default:
									foreach ( $data as $subheading => $subdata ) {

										switch ( $subheading ) {
											case 'earnings':
												WP_CLI::log( $this->color_message( $subheading . ': ', walkthecounty_currency_filter( $subdata, array( 'decode_currency' => true ) ) ) );
												break;
											default:
												WP_CLI::log( $this->color_message( $subheading . ': ', $subdata ) );
										}
									}
							}
						}
						break;

					case 'pricing':
					case 'info':
					default:
						$this->color_main_heading( ucfirst( $key ) );

						// Show form type.
						if ( ! $is_show_form_type ) {
							$form              = new WalkTheCounty_Donate_Form( $form_id );
							$is_show_form_type = true;

							WP_CLI::log( $this->color_message( __( 'form type', 'walkthecounty' ), $form->get_type() ) );
						}

						foreach ( $info as $heading => $data ) {

							switch ( $heading ) {
								case 'id':
									WP_CLI::log( $this->color_message( $heading, $data ) );
									break;

								default:
									$data = empty( $data ) ? __( 'Not set', 'walkthecounty' ) : $data;
									WP_CLI::log( $this->color_message( $heading, $data ) );
							}
						}
				}// End switch().
			}// End foreach().
		} else {
			// Show multiple form.
			$table_data             = array();
			$is_table_first_row_set = false;
			$table_column_count     = 0;

			WP_CLI::line( $this->color_message( sprintf( __( '%d donation forms found', 'walkthecounty' ), count( $forms['forms'] ) ), '', false ) );

			foreach ( $forms['forms'] as $index => $form_data ) {

				// Default table data.
				$table_first_row = array();
				$table_row       = array();

				foreach ( $form_data['info'] as $key => $form ) {

					// Do not show thumbnail, content and link in table.
					if ( in_array( $key, array( 'content', 'thumbnail', 'link' ), true ) ) {
						continue;
					}

					if ( ! $is_table_first_row_set ) {
						$table_first_row[] = $key;
					}

					$table_row[] = $form;

					if ( 'status' === $key ) {
						// First array item will be an form id in our case.
						$form = new WalkTheCounty_Donate_Form( absint( $table_row[0] ) );

						$table_row[] = $form->get_type();
					}
				}

				// Set table first row.
				if ( ! $is_table_first_row_set ) {

					// Add extra column to table.
					$table_first_row[] = 'type';

					$table_data[]           = $table_first_row;
					$is_table_first_row_set = true;
				}

				// set table data.
				$table_data[] = $table_row;
			}// End foreach().

			$this->display_table( $table_data );
		}// End if().
	}


	/**
	 * Get the donors currently on your WalkTheCounty site. Can also be used to create donors records
	 *
	 * ## OPTIONS
	 *
	 * [--id=<donor_id>]
	 * : A specific donor ID to retrieve
	 *
	 * [--email=<donor_email>]
	 * : The email address of the donor to retrieve
	 *
	 * [--number=<donor_count>]
	 * : The number of donor to retrieve
	 *
	 * [--create=<number>]
	 * : The number of arbitrary donors to create. Leave as 1 or blank to create a
	 * donor with a specific email
	 *
	 * [--form-id=<donation_form_id>]
	 * : Get list of donors of specific donation form
	 *
	 * [--name=<name_of_donor>]
	 * : Name with which you want to create new donor
	 *
	 * [--format=<output_format>]
	 * : In which format you want to see results. Valid formats: table, json, csv
	 *
	 * ## EXAMPLES
	 *
	 * wp walkthecounty donors
	 * wp walkthecounty donors --id=103
	 * wp walkthecounty donors --email=john@test.com
	 * wp walkthecounty donors --create=1 --email=john@test.com
	 * wp walkthecounty donors --create=1 --email=john@test.com --name="John Doe"
	 * wp walkthecounty donors --create=1000
	 * wp walkthecounty donors --number=1000
	 * wp walkthecounty donors --form-id=1024
	 *
	 * @since         1.7
	 * @access        public
	 *
	 * @param        string $args       Command Data.
	 * @param        array  $assoc_args List of command data.
	 *
	 * @return        void
	 *
	 * @subcommand    donors
	 */
	public function donors( $args, $assoc_args ) {
		global $wp_query;
		$donor_id = isset( $assoc_args ) && array_key_exists( 'id', $assoc_args ) ? absint( $assoc_args['id'] ) : false;
		$email    = isset( $assoc_args ) && array_key_exists( 'email', $assoc_args ) ? $assoc_args['email'] : false;
		$name     = isset( $assoc_args ) && array_key_exists( 'name', $assoc_args ) ? $assoc_args['name'] : '';
		$create   = isset( $assoc_args ) && array_key_exists( 'create', $assoc_args ) ? $assoc_args['create'] : false;
		$number   = isset( $assoc_args ) && array_key_exists( 'number', $assoc_args ) ? $assoc_args['number'] : 10;
		$form_id  = isset( $assoc_args ) && array_key_exists( 'form-id', $assoc_args ) ? $assoc_args['form-id'] : 0;
		$format   = isset( $assoc_args ) && array_key_exists( 'format', $assoc_args ) ? $assoc_args['format'] : 'table';
		$start    = time();

		if ( $create ) {
			if ( 80 < $create ) {
				WP_CLI::warning( 'Currently we can only generate maximum 80 donors.', 'walkthecounty' );
				$create = 80;
			}

			$number = 1;

			if ( isset( $assoc_args['email'] ) && ! is_email( $email ) ) {
				WP_CLI::warning( 'Wrong email address provided.', 'walkthecounty' );

				return;
			}

			// Create one or more donors.
			if ( ! $email ) {
				// If no email is specified, look to see if we are generating arbitrary donor accounts.
				$number = is_numeric( $create ) ? absint( $create ) : 1;
			}

			for ( $i = 0; $i < $number; $i ++ ) {
				$name  = $name ? $name : $this->get_random_name();
				$email = $email ? $email : $this->get_random_email( $name );

				$args = array(
					'email' => $email,
					'name'  => $name,
				);

				$donor_id = WalkTheCounty()->donors->add( $args );

				if ( $donor_id ) {
					WP_CLI::line( $this->color_message( sprintf( __( 'Donor #%d created successfully', 'walkthecounty' ), $donor_id ) ) );
				} else {
					WP_CLI::error( __( 'Failed to create donor', 'walkthecounty' ) );
				}

				// Reset email and name to false so it is generated on the next loop (if creating donors).
				$email = $name = false;
			}

			WP_CLI::line( $this->color_message( sprintf( __( '%1$d donors created in %2$d seconds', 'walkthecounty' ), $number, time() - $start ) ) );

		} else {
			// Counter.
			self::$counter = 1;

			// Search for customers.
			$search = $donor_id ? $donor_id : $email;

			/**
			 * Get donors.
			 */
			// Cache previous number query var.
			$is_set_number = $cache_per_page = false;
			if ( isset( $wp_query->query_vars['number'] ) ) {
				$cache_per_page = $wp_query->query_vars['number'];
				$is_set_number  = true;
			}

			// Change number query var.
			$wp_query->query_vars['number'] = $number;

			// Get donors.
			if ( $form_id ) {
				// @TODO: Allow user to get a list of donors by donation status.
				$donors = $this->get_donors_by_form_id( $form_id );
			} else {
				$donors = $this->api->get_donors( $search );
			}

			// Reset number query var.
			if ( $is_set_number ) {
				$wp_query->query_vars['number'] = $cache_per_page;
			}

			if ( isset( $donors['error'] ) ) {
				WP_CLI::error( $donors['error'] );
			}

			if ( empty( $donors ) ) {
				WP_CLI::error( __( 'No donors found.', 'walkthecounty' ) );

				return;
			}

			$table_data             = array();
			$is_table_first_row_set = false;

			foreach ( $donors['donors'] as $donor_data ) {
				// Set default table row data.
				$table_first_row = array( __( 's_no', 'walkthecounty' ) );
				$table_row       = array( self::$counter );

				foreach ( $donor_data as $key => $donor ) {
					switch ( $key ) {
						case 'stats':
							foreach ( $donor as $heading => $data ) {

								// Get first row.
								if ( ! $is_table_first_row_set ) {
									$table_first_row[] = $heading;
								}

								switch ( $heading ) {
									case 'total_spent':
										$table_row[] = walkthecounty_currency_filter( $data, array( 'decode_currency' => true ) );
										break;

									default:
										$table_row[] = $data;
								}
							}
							break;

						case 'address':
							break;

						case 'info':
						default:
							foreach ( $donor as $heading => $data ) {

								// Get first row.
								if ( ! $is_table_first_row_set ) {
									$table_first_row[] = $heading;
								}

								$table_row[] = $data;
							}
					}
				}

				// Add first row data to table data.
				if ( ! $is_table_first_row_set ) {
					$table_data[]           = $table_first_row;
					$is_table_first_row_set = true;
				}

				// Add table row data.
				$table_data[] = $table_row;

				// Increase counter.
				self::$counter ++;
			}// End foreach().

			switch ( $format ) {
				case 'json':
					$table_column_name = $table_data[0];
					unset( $table_data[0] );

					$new_table_data = array();
					foreach ( $table_data as $index => $data ) {
						foreach ( $data as $key => $value ) {
							$new_table_data[ $index ][ $table_column_name[ $key ] ] = $value;
						}
					}

					WP_CLI::log( json_encode( $new_table_data ) );
					break;

				case 'csv':
					$file_path = trailingslashit( WP_CONTENT_DIR ) . 'uploads/walkthecounty_donors_' . date( 'Y_m_d_s', current_time( 'timestamp' ) ) . '.csv';
					$fp        = fopen( $file_path, 'w' );

					if ( is_writable( $file_path ) ) {
						foreach ( $table_data as $fields ) {
							fputcsv( $fp, $fields );
						}

						fclose( $fp );

						WP_CLI::success( "Donors list csv created successfully: {$file_path}" );
					} else {
						WP_CLI::warning( "Unable to create donors list csv file: {$file_path} (May folder do not have write permission)" );
					}

					break;

				default:
					$this->display_table( $table_data );
			}// End switch().
		}// End if().
	}


	/**
	 * Get the recent donations for your WalkTheCounty site
	 *
	 * ## OPTIONS
	 *
	 * [--number=<donation_count>]
	 * : The number of donations to retrieve
	 *
	 *
	 * ## EXAMPLES
	 *
	 * wp walkthecounty donations
	 * wp walkthecounty donations --number=100
	 *
	 * @since         1.7
	 * @access        public
	 *
	 * @param        string $args       Command Data.
	 * @param        array  $assoc_args List of command data.
	 *
	 * @return        void
	 *
	 * @subcommand    donations
	 */
	public function donations( $args, $assoc_args ) {
		global $wp_query;
		$number = isset( $assoc_args ) && array_key_exists( 'number', $assoc_args ) ? $assoc_args['number'] : 10;

		// Cache previous number query var.
		$is_set_number = $cache_per_page = false;
		if ( isset( $wp_query->query_vars['number'] ) ) {
			$cache_per_page = $wp_query->query_vars['number'];
			$is_set_number  = true;
		}

		// Change number query var.
		$wp_query->query_vars['number'] = $number;

		// Get donations.
		$donations = $this->api->get_recent_donations();

		// Reset number query var.
		if ( $is_set_number ) {
			$wp_query->query_vars['number'] = $cache_per_page;
		}

		if ( empty( $donations ) ) {
			WP_CLI::error( __( 'No donations found.', 'walkthecounty' ) );

			return;
		}

		self::$counter = 1;

		foreach ( $donations['donations'] as $key => $donation ) {
			$this->color_main_heading( sprintf( __( '%1$s. Donation #%2$s', 'walkthecounty' ), self::$counter, $donation['ID'] ), 'Y' );
			self::$counter ++;

			foreach ( $donation as $column => $data ) {

				if ( is_array( $data ) ) {
					$this->color_sub_heading( $column );
					foreach ( $data as $subcolumn => $subdata ) {

						// Decode html codes.
						switch ( $subcolumn ) {
							case 'name':
								$subdata = html_entity_decode( $subdata );
								break;
						}

						// @TODO Check if multi dimension array information is importent to show or not. For example inside donation array we have array for fees data inside payment meta.
						if ( is_array( $subdata ) ) {
							continue;
						}

						WP_CLI::log( $this->color_message( $subcolumn, $subdata ) );
					}
					continue;
				}

				WP_CLI::log( $this->color_message( $column, $data ) );
			}
		}
	}

	/**
	 * Get walkthecounty plugin report.
	 *
	 * ## OPTIONS
	 *
	 * [--id=<donation_form_id>]
	 * : The ID of a specific donation_form to retrieve stats for, or all
	 *
	 * [--date=<range|this_month|last_month|today|yesterday|this_quarter|last_quarter|this_year|last_year>]
	 * : A specific date range to retrieve stats for
	 *
	 * [--start-date=<date>]
	 * : The start date of a date range to retrieve stats for. Date format is MM/DD/YYYY
	 *
	 * [--end-date=<date>]
	 * : The end date of a date range to retrieve stats for. Date format is MM/DD/YYYY
	 *
	 * ## EXAMPLES
	 *
	 * wp walkthecounty report
	 * wp walkthecounty report --date=this_month
	 * wp walkthecounty report --start-date=01/02/2014 --end-date=02/23/2014
	 * wp walkthecounty report --date=last_year
	 * wp walkthecounty report --date=last_year --id=15
	 *
	 * @since         1.7
	 * @access        public
	 *
	 * @param        string $args       Command Data.
	 * @param        array  $assoc_args List of command data.
	 *
	 * @subcommand    report
	 *
	 * @return        void
	 */
	public function report( $args, $assoc_args ) {
		$stats      = new WalkTheCounty_Payment_Stats();
		$date       = isset( $assoc_args ) && array_key_exists( 'date', $assoc_args ) ? $assoc_args['date'] : false;
		$start_date = isset( $assoc_args ) && array_key_exists( 'start-date', $assoc_args ) ? $assoc_args['start-date'] : false;
		$end_date   = isset( $assoc_args ) && array_key_exists( 'end-date', $assoc_args ) ? $assoc_args['end-date'] : false;
		$form_id    = isset( $assoc_args ) && array_key_exists( 'id', $assoc_args ) ? $assoc_args['id'] : 0;

		if ( ! empty( $date ) ) {
			$start_date = $date;
			$end_date   = false;
		} elseif ( empty( $date ) && empty( $start_date ) ) {
			$start_date = 'this_month';
			$end_date   = false;
		}

		// Get stats.
		$earnings = $stats->get_earnings( $form_id, $start_date, $end_date );
		$sales    = $stats->get_sales( $form_id, $start_date, $end_date );

		WP_CLI::line( $this->color_message( __( 'Earnings', 'walkthecounty' ), walkthecounty_currency_filter( $earnings, array( 'decode_currency' => true ) ) ) );
		WP_CLI::line( $this->color_message( __( 'Sales', 'walkthecounty' ), $sales ) );
	}


	/**
	 * Delete cache (transient).
	 *
	 * ## OPTIONS
	 *
	 * --action=<cache_action>
	 * : Value of this parameter can be delete (in case you want to delete all stat cache).
	 *
	 * ## EXAMPLES
	 *
	 *    # See form report
	 *    wp walkthecounty cache --action=delete
	 *
	 * @since         1.7
	 * @access        public
	 *
	 * @param        string $args       Command Data.
	 * @param        array  $assoc_args List of command data.
	 *
	 * @return        void
	 *
	 * @subcommand    cache
	 */
	public function cache( $args, $assoc_args ) {
		$action = isset( $assoc_args ) && array_key_exists( 'action', $assoc_args ) ? $assoc_args['action'] : false;

		// Bailout.
		if ( ! $action || ! in_array( $action, array( 'delete' ), true ) ) {
			WP_CLI::warning( __( 'Type wp walkthecounty cache --action=delete to delete all stat transients', 'walkthecounty' ) );

			return;
		}

		switch ( $action ) {
			case 'delete':
				// Reset counter.
				self::$counter = 1;

				if ( $this->delete_stats_transients() ) {
					// Report .eading.
					WP_CLI::success( 'WalkTheCountyWP cache deleted.' );
				} else {
					// Report .eading.
					WP_CLI::warning( 'We did not find any WalkTheCountyWP plugin cache to delete.' );
				}
				break;
		}

	}

	/**
	 * Delete all form stat transient
	 *
	 * @since     1.7
	 * @access    private
	 *
	 * @return    bool
	 */
	private function delete_stats_transients() {
		global $wpdb;

		$stat_option_names = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name FROM {$wpdb->options} where (option_name LIKE '%%%s%%' OR option_name LIKE '%%%s%%')",
				array(
					'_transient_walkthecounty_stats_',
					'walkthecounty_cache',
				)
			),
			ARRAY_A
		);

		if ( ! empty( $stat_option_names ) ) {

			foreach ( $stat_option_names as $option_name ) {
				$error       = false;
				$option_name = $option_name['option_name'];

				switch ( true ) {
					case ( false !== strpos( $option_name, 'transient' ) ):
						$option_name = str_replace( '_transient_', '', $option_name );
						$error       = delete_transient( $option_name );
						break;

					default:
						$error = delete_option( $option_name );
				}

				if ( $error ) {
					WP_CLI::log( $this->color_message( self::$counter, $option_name ) );
					self::$counter ++;
				} else {
					WP_CLI::log( $this->color_message( __( 'Error while deleting this transient', 'walkthecounty' ), $option_name ) );
				}
			}

			return true;
		}

		return false;
	}


	/**
	 * Return colored message
	 *
	 * @param    string $heading Message heading.
	 * @param    string $message Message content.
	 * @param    bool   $colon   Check if add colon between heading and message.
	 * @param    string $color   Heading color.
	 *
	 * @return   string
	 */
	private function color_message( $heading, $message = '', $colon = true, $color = 'g' ) {
		// Add colon.
		if ( $colon ) {
			$heading = $heading . ': ';
		}

		return WP_CLI::colorize( "%{$color}" . $heading . '%n' ) . $message;
	}


	/**
	 * Output section heading.
	 *
	 * @since     1.7
	 * @access    private
	 *
	 * @param    string $heading Heading.
	 * @param    string $color   Color.
	 *
	 * @return    void
	 */
	private function color_main_heading( $heading, $color = 'g' ) {
		WP_CLI::log( "\n######   " . $this->color_message( $heading, '', false, $color ) . '   ######' );
	}

	/**
	 * Output section sub heading.
	 *
	 * @since     1.7
	 * @access    private
	 *
	 * @param    string $subheading Sub heading.
	 *
	 * @return    void
	 */
	private function color_sub_heading( $subheading ) {
		WP_CLI::log( "\n--->" . $subheading . '', '', false );
	}


	/**
	 * Display data in table format.
	 *
	 * @since     1.7
	 * @access    private
	 *
	 * @param    array $data Array of table data.
	 *
	 * @return    void
	 */
	private function display_table( $data ) {
		$table = new \cli\Table();

		// Set table header.
		$table->setHeaders( $data[0] );

		// Remove table header.
		unset( $data[0] );

		// Set table data.
		$table->setRows( $data );

		// Display table.
		$table->display();
	}


	/**
	 * Get donors by form id.
	 *
	 * @since 1.8
	 *
	 * @param int $form_id From id.
	 *
	 * @return array
	 */

	private function get_donors_by_form_id( $form_id ) {
		$donors = array();

		$donations = new WalkTheCounty_Payments_Query(
			array(
				'walkthecounty_forms' => array( $form_id ),
				'number'     => - 1,
				'status'     => array( 'publish' ),
			)
		);

		$donations   = $donations->get_payments();
		$skip_donors = array();

		/* @var WalkTheCounty_Payment|object $donation Payment object. */
		foreach ( $donations as $donation ) {

			if ( in_array( $donation->customer_id, $skip_donors, true ) ) {
				continue;
			}

			if ( ! empty( $donors ) ) {
				$donors['donors'][] = current( current( $this->api->get_donors( (int) $donation->customer_id ) ) );
			} else {
				$donors = array_merge( $donors, $this->api->get_donors( (int) $donation->customer_id ) );
			}

			$skip_donors[] = $donation->customer_id;
		}

		return $donors;
	}

	/**
	 * Get random user name
	 *
	 * @since 1.8.17
	 * @return string
	 */
	private function get_random_name() {
		// First names.
		$names = array(
			'Devin',
			'Christopher',
			'Ryan',
			'Ethan',
			'John',
			'Zoey',
			'Sarah',
			'Michelle',
			'Samantha',
		);

		// Surnames.
		$surnames = array(
			'Walker',
			'Josh',
			'Thompson',
			'Anderson',
			'Johnson',
			'Tremblay',
			'Peltier',
			'Cunningham',
			'Simpson',
			'Mercado',
			'Sellers',
		);

		// Generate a random forename.
		$random_name = $names[ mt_rand( 0, sizeof( $names ) - 1 ) ];

		// Generate a random surname.
		$random_surname = $surnames[ mt_rand( 0, sizeof( $surnames ) - 1 ) ];

		// Generate name.
		$name = "{$random_name} {$random_surname}";

		if ( in_array( $name, $this->new_donor_names ) ) {
			$name = $this->get_random_name();
		}

		// Collect new donor names.
		$this->new_donor_names[] = $name;

		return $name;
	}

	/**
	 * Get random email
	 *
	 * @since 1.8.17
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	private function get_random_email( $name ) {
		return implode( '.', explode( ' ', strtolower( $name ) ) ) . '@test.com';
	}

	/**
	 * Toggle settings for WalkTheCounty's test mode.
	 *
	 * [--enable]
	 * : Enable WalkTheCounty's test mode
	 *
	 * [--disable]
	 * : Enable WalkTheCounty's test mode
	 *
	 * @when after_wp_load
	 * @subcommand test-mode
	 */
	public function test_mode( $args, $assoc ) {

		// Return if associative arguments are not specified.
		if ( empty( $assoc ) ) {
			WP_CLI::error( "--enable or --disable flag is missing." );
			return;
		}

		$enabled_gateways = walkthecounty_get_option( 'gateways' );
		$default_gateway  = walkthecounty_get_option( 'default_gateway' );


		// Enable Test Mode.
		if ( true === WP_CLI\Utils\get_flag_value( $assoc, 'enable' ) ) {

			// Set `Test Mode` to `enabled`.
			walkthecounty_update_option( 'test_mode', 'enabled' );


			// Enable `Test Donation` gateway.
			$enabled_gateways['manual'] = "1";
			walkthecounty_update_option( 'gateways', $enabled_gateways );


			// Set `Test Donation` as default gateway.
			add_option( 'walkthecounty_test_mode_default_gateway', $default_gateway );
			walkthecounty_update_option( 'default_gateway', 'manual' );


			// Show success message on completion.
			WP_CLI::success( 'WalkTheCountyWP Test mode enabled' );
		}

		// Disable Test Mode.
		if ( true === WP_CLI\Utils\get_flag_value( $assoc, 'disable' ) ) {

			// Set `Test Mode` to `disabled`.
			walkthecounty_update_option( 'test_mode', 'disabled' );


			// Disable `Test Donation` gateway.
			unset( $enabled_gateways['manual'] );
			walkthecounty_update_option( 'gateways', $enabled_gateways );


			// Backup `Default Gateway` setting for restore on test mode disable.
			$default_gateway_backup = get_option( 'walkthecounty_test_mode_default_gateway' );
			walkthecounty_update_option( 'default_gateway', $default_gateway_backup );
			delete_option( 'walkthecounty_test_mode_default_gateway' );


			// Show success message on completion.
			WP_CLI::success( 'WalkTheCountyWP Test mode disabled' );
		}
	}


	/**
	 * Checks if the walkthecountyn path has a walkthecounty repository installed
	 * or not.
	 *
	 * @param string $repo_path Path to a WalkTheCounty Addon.
	 *
	 * @since 2.1.3
	 *
	 * @return boolean
	 */
	private function is_git_repo( $repo_path ) {
		if ( is_dir( "{$repo_path}.git" ) ) {
			return true;
		}

		return false;
	}


	/**
	 * Gets the current branch name of a WalkTheCounty Addon.
	 *
	 * @param string $repo_path Path to a WalkTheCounty Addon.
	 *
	 * @since 2.1.3
	 *
	 * @return string
	 */
	private function get_git_current_branch( $repo_path ) {

		exec( "cd $repo_path && git branch | grep '\*'", $branch_names );

		$branch_name = trim( strtolower( str_replace( '* ', '', $branch_names[0] ) ) );

		return $branch_name;
	}


	/**
	 * Updates the current branch of WalkTheCounty Addons.
	 * Uses the remote origin to pull the latest code.
	 *
	 * ## OPTIONS
	 *
	 * [--name=<name>]
	 * : Update a single add-on.
	 *
	 * [--exclude=<names>]
	 * : Names of add-ons that should be excluded from updating.
	 *
	 * ## EXAMPLES
	 * 	wp walkthecounty add-on-update
	 * 	wp walkthecounty add-on-update --name="WalkTheCounty-Stripe"
	 * 	wp walkthecounty add-on-update --exclude="WalkTheCounty-Stripe, WalkTheCounty-Recurring-Donations"
	 *
	 * @param array $pos   Array of positional arguments.
	 * @param array $assoc Array of associative arguments.
	 *
	 * @since 2.1.3
	 *
	 * @subcommand add-on-update
	 */
	public function addon_update( $pos, $assoc ) {

		/**
		 * Only 1 associative argument should be passed.
		 * It can be either `--name` or `--exclude`
		 */
		if ( count( $assoc ) > 1 ) {
			WP_CLI::error( __( 'Too many associative arguments.', 'walkthecounty' ) );
		}

		/**
		 * Update a single WalkTheCounty addon.
		 */
		if ( false !== ( $addon_name = WP_CLI\Utils\get_flag_value( $assoc, 'name', false ) ) ) {
			$walkthecounty_addon_path = glob( WP_CONTENT_DIR . "/plugins/$addon_name/" , GLOB_ONLYDIR );

			/**
			 * Display error if the plugin (addon) name entered does
			 * not exist.
			 */
			if ( empty( $walkthecounty_addon_path ) ) {
				WP_CLI::error( sprintf( __( "The WalkTheCountyWP add-on '%s' does not exist.", 'walkthecounty' ), $addon_name ) );
			}

			/**
			 * If the directory does not contain a Git
			 * repository, then display error and halt.
			 */
			if ( ! $this->is_git_repo( $walkthecounty_addon_path[0] ) ) {
				WP_CLI::error( __( 'This is not a Git repo', 'walkthecounty' ) );
			}

			/**
			 * Get the current branch name. This branch will be updated next.
			 */
			$branch_name = $this->get_git_current_branch( $walkthecounty_addon_path[0] );

			/**
			 * Take the latest pull of the current branch, i.e.;
			 * sync it with origin.
			 */
			passthru( "cd $walkthecounty_addon_path[0] && git pull origin $branch_name", $return_var );

			/**
			 * Show success/error messages depending on whether the
			 * current branch of the addon was updated or not.
			 */
			if ( 0 === $return_var ) {
				WP_CLI::success( sprintf( __( "The WalkTheCountyWP add-on '%s' is up-to-date with origin." ), $addon_name ) );

				return;
			} elseif ( 1 === $return_var ) {
				WP_CLI::error( sprintf( __( "The WalkTheCountyWP add-on '%s' was not updated." ), $addon_name ) );
			}
		}

		/**
		 * Convert the comma-separated string of WalkTheCounty-addons in the
		 * excluded list into array.
		 */
		$addon_names = WP_CLI\Utils\get_flag_value( $assoc, 'exclude', array() );
		if ( ! empty( $addon_names ) ) {
			$addon_names = array_map( 'trim', explode( ',', $addon_names ) );
		}

		/**
		 * Get directory paths of all the addons including
		 * WalkTheCounty Core.
		 */
		$walkthecounty_addon_directories = glob( WP_CONTENT_DIR . '/plugins/[gG]ive*/' , GLOB_ONLYDIR );

		foreach ( $walkthecounty_addon_directories as $repo ) {

			/**
			 * Extract the plugin/addon folder name
			 * from the absolute path.
			 */
			$plugin_name = basename( $repo );

			/**
			 * If the WalkTheCounty addon directory does not contain
			 * a Git repo, then continue.
			 */
			if ( ! $this->is_git_repo( $repo ) ) {
				WP_CLI::line(
					sprintf(
						__( "%s: '%s' does not contain git repo.", 'walkthecounty' ),
						WP_CLI::colorize( '%RError%n' ),
						$plugin_name
					)
				);

				continue;
			}

			/**
			 * Continue if the WalkTheCounty addon name is in the exlusion list.
			 */
			if ( in_array( $plugin_name, $addon_names, true ) ) {
				continue;
			}

			/* Get the current branch name */
			$branch_name = $this->get_git_current_branch( $repo );

			/**
			 * Show a colorized (CYAN) title for each addon/plugin
			 * before a pull.
			 */
			WP_CLI::line( WP_CLI::colorize( "> %CUpdating $plugin_name | $branch_name%n" ) );

			/**
			 * Git pull from the current branch using
			 * remote `origin`.
			 */
			if ( ! empty( $branch_name ) ) {
				passthru( "cd $repo && git pull origin $branch_name", $return_var );
			}

			$items[] = array(
				'WalkTheCountyWP Addon' => $plugin_name,
				'Branch'     => $branch_name,
				'Remote'     => 'origin',
				'Status'     => ( 0 === $return_var )
					? __( 'Success', 'walkthecounty' )
					: __( 'Failed', 'walkthecounty' ),
			);

			/**
			 * Leave a blank line for aesthetics.
			 */
			WP_CLI::line();
		}

		/**
		 * Display final results in a tabular format.
		 */
		WP_CLI\Utils\format_items(
			'table',
			$items,
			array(
				'WalkTheCountyWP Addon',
				'Branch',
				'Remote',
				'Status',
			)
		);
	}
}
