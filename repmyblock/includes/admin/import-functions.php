<?php
/**
 * Import Functions
 *
 * @package     WalkTheCounty
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.8.14
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the Import report of the donations
 *
 * @since 1.8.13
 */
function walkthecounty_import_donation_report() {
	return get_option( 'walkthecounty_import_donation_report', array() );
}


/**
 * Update the Import report of the donations
 *
 * @since 1.8.13
 */
function walkthecounty_import_donation_report_update( $value = array() ) {
	update_option( 'walkthecounty_import_donation_report', $value, false );
}

/**
 * Delete the Import report of the donations
 *
 * @since 1.8.13
 */
function walkthecounty_import_donation_report_reset() {
	update_option( 'walkthecounty_import_donation_report', array(), false );
}

/**
 * WalkTheCounty get form data from csv if not then create and form and return the form value.
 *
 * @since 1.8.13.
 *
 * @param $data .
 *
 * @return array|bool|WalkTheCounty_Donate_Form|int|null|WP_Post
 */
function walkthecounty_import_get_form_data_from_csv( $data, $import_setting = array() ) {
	$new_form = false;
	$dry_run  = isset( $import_setting['dry_run'] ) ? $import_setting['dry_run'] : false;

	// Get the import report
	$report = walkthecounty_import_donation_report();

	$form = false;
	$meta = array();

	if ( ! empty( $data['form_id'] ) ) {
		$form = new WalkTheCounty_Donate_Form( $data['form_id'] );
		// Add support to older php version.
		$form_id = $form->get_ID();
		if ( empty( $form_id ) ) {
			$form = false;
		} else {
			$report['duplicate_form'] = ( ! empty( $report['duplicate_form'] ) ? ( absint( $report['duplicate_form'] ) + 1 ) : 1 );
		}
	}

	if ( false === $form && ! empty( $data['form_title'] ) ) {
		$form = get_page_by_title( $data['form_title'], OBJECT, 'walkthecounty_forms' );

		if ( ! empty( $form->ID ) ) {

			$report['duplicate_form'] = ( ! empty( $report['duplicate_form'] ) ? ( absint( $report['duplicate_form'] ) + 1 ) : 1 );

			$form = new WalkTheCounty_Donate_Form( $form->ID );
		} else {
			$form = new WalkTheCounty_Donate_Form();
			$args = array(
				'post_title'  => $data['form_title'],
				'post_status' => 'publish',
			);

			if ( empty( $dry_run ) ) {
				$form = $form->create( $args );
			}

			$report['create_form'] = ( ! empty( $report['create_form'] ) ? ( absint( $report['create_form'] ) + 1 ) : 1 );
			$new_form              = true;

		}

		$form = get_page_by_title( $data['form_title'], OBJECT, 'walkthecounty_forms' );
		if ( ! empty( $form->ID ) ) {
			$form = new WalkTheCounty_Donate_Form( $form->ID );
		}
	}

	if ( ! empty( $form ) && $form->get_ID() && ! empty( $data['form_level'] ) && empty( $dry_run ) ) {

		$price_option = 'set';
		$form_level   = strtolower( preg_replace( '/\s+/', '', $data['form_level'] ) );

		if ( 'custom' !== $form_level ) {
			$prices     = (array) $form->get_prices();
			$price_text = array();
			foreach ( $prices as $key => $price ) {
				if ( isset( $price['_walkthecounty_id']['level_id'] ) ) {
					$price_text[ $price['_walkthecounty_id']['level_id'] ] = ( ! empty( $price['_walkthecounty_text'] ) ? strtolower( preg_replace( '/\s+/', '', $price['_walkthecounty_text'] ) ) : '' );
				}
			}

			if ( ! in_array( $form_level, $price_text ) ) {

				// For generating unquiet level id.
				$count     = 1;
				$new_level = count( $prices ) + $count;
				while ( array_key_exists( $new_level, $price_text ) ) {
					$count ++;
					$new_level = count( $prices ) + $count;
				}

				$multi_level_donations = array(
					array(
						'_walkthecounty_id'     => array(
							'level_id' => $new_level,
						),
						'_walkthecounty_amount' => walkthecounty_sanitize_amount_for_db( $data['amount'] ),
						'_walkthecounty_text'   => $data['form_level'],
					),
				);

				$price_text[ $new_level ] = strtolower( preg_replace( '/\s+/', '', $data['form_level'] ) );

				if ( ! empty( $prices ) && is_array( $prices ) && ! empty( $prices[0] ) ) {
					$prices = wp_parse_args( $multi_level_donations, $prices );

					// Sort $prices by amount in ascending order.
					$prices = wp_list_sort( $prices, '_walkthecounty_amount', 'ASC' );
				} else {
					$prices = $multi_level_donations;
				}

				// Unset _walkthecounty_default key from $prices.
				foreach ( $prices as $key => $price ) {
					if ( isset( $prices[ $key ]['_walkthecounty_default'] ) ) {
						unset( $prices[ $key ]['_walkthecounty_default'] );
					}
				}

				// Set the first $price of the $prices as default.
				$prices[0]['_walkthecounty_default'] = 'default';
			}
			$form->price_id = array_search( $form_level, $price_text );

			$donation_levels_amounts = wp_list_pluck( $prices, '_walkthecounty_amount' );
			$min_amount              = min( $donation_levels_amounts );
			$max_amount              = max( $donation_levels_amounts );

			$meta = array(
				'_walkthecounty_levels_minimum_amount' => $min_amount,
				'_walkthecounty_levels_maximum_amount' => $max_amount,
				'_walkthecounty_donation_levels'       => array_values( $prices ),
			);

			$price_option = 'multi';
		} else {
			$form->price_id = 'custom';
		}

		$defaults = array(
			'_walkthecounty_set_price' => walkthecounty_sanitize_amount_for_db( $data['amount'] ),
		);

		// If new form is created.
		if ( ! empty( $new_form ) ) {
			$new_form = array(
				'_walkthecounty_custom_amount_text'          => ( ! empty( $data['form_custom_amount_text'] ) ? $data['form_custom_amount_text'] : 'custom' ),
				'_walkthecounty_logged_in_only'              => 'enabled',
				'_walkthecounty_custom_amount'               => 'enabled',
				'_walkthecounty_payment_import'              => true,
				'_walkthecounty_display_style'               => 'radios',
				'_walkthecounty_payment_display'             => 'onpage',
				'walkthecounty_product_notes'                => 'Donation Notes',
				'_walkthecounty_product_type'                => 'default',
				'_walkthecounty_default_gateway'             => 'global',
				'_walkthecounty_customize_offline_donations' => 'global',
				'_walkthecounty_show_register_form'          => 'both',
				'_walkthecounty_price_option'                => $price_option,
			);
			$defaults = wp_parse_args( $defaults, $new_form );
		}

		$meta = wp_parse_args( $meta, $defaults );

		foreach ( $meta as $key => $value ) {
			walkthecounty_update_meta( $form->get_ID(), $key, $value );
		}
	}

	// update the report
	walkthecounty_import_donation_report_update( $report );

	return $form;
}

/**
 * WalkTheCounty get user details if not then create a user. Used in Import Donation CSV.
 *
 * @since 1.8.13
 *
 * @param $data
 *
 * @return bool|false|WP_User
 */
function walkthecounty_import_get_user_from_csv( $data, $import_setting = array() ) {
	$report               = walkthecounty_import_donation_report();
	$dry_run              = isset( $import_setting['dry_run'] ) ? $import_setting['dry_run'] : false;
	$dry_run_donor_create = false;
	$donor_data           = array();
	$donor_id             = false;

	// check if donor id is not empty
	if ( ! empty( $data['donor_id'] ) ) {
		$donor_data = new WalkTheCounty_Donor( (int) $data['donor_id'] );
		if ( ! empty( $donor_data->id ) ) {
			$report['duplicate_donor'] = ( ! empty( $report['duplicate_donor'] ) ? ( absint( $report['duplicate_donor'] ) + 1 ) : 1 );
		}
	}

	if ( empty( $donor_data->id ) && ! empty( $data['user_id'] ) ) {
		$user_id    = (int) $data['user_id'];
		$donor_data = new WalkTheCounty_Donor( $user_id, true );


		if ( empty( $donor_data->id ) ) {
			$donor_data = get_user_by( 'id', $user_id );

			// if no wp user is found then no donor is create with that user id
			if ( ! empty( $donor_data->ID ) ) {

				if ( empty( $dry_run ) ) {
					$first_name = ( ! empty( $data['first_name'] ) ? $data['first_name'] : $donor_data->user_nicename );
					$last_name  = ( ! empty( $data['last_name'] ) ? $data['last_name'] : ( ( $lastname = get_user_meta( $donor_data->ID, 'last_name', true ) ) ? $lastname : '' ) );
					$name       = $first_name . ' ' . $last_name;
					$user_email = $donor_data->user_email;
					$donor_args = array(
						'name'    => $name,
						'email'   => $user_email,
						'user_id' => $user_id,
					);

					$donor_data = new WalkTheCounty_Donor();
					$donor_data->create( $donor_args );

					// Adding notes that donor is being imported from CSV.
					$current_user = wp_get_current_user();
					$donor_data->add_note( wp_sprintf( __( 'This donor was imported by %s', 'walkthecounty' ), $current_user->user_email ) );

					// Add is used to ensure duplicate emails are not added
					if ( $user_email != $data['email'] && ! empty( $data['email'] ) ) {
						$donor_data->add_meta( 'additional_email', $data['email'] );
					}
				} else {
					$dry_run_donor_create = true;
					$donor_data           = array( 'id' => 1 );
				}

				$report['create_donor'] = ( ! empty( $report['create_donor'] ) ? ( absint( $report['create_donor'] ) + 1 ) : 1 );
			} elseif ( $dry_run ) {
				$donor_data = array();
			}
		} else {
			// Add is used to ensure duplicate emails are not added
			if ( $donor_data->email != $data['email'] && empty( $dry_run ) ) {
				$donor_data->add_meta( 'additional_email', ( ! empty( $data['email'] ) ? $data['email'] : $donor_data->email ) );
			}
			$report['duplicate_donor'] = ( ! empty( $report['duplicate_donor'] ) ? ( absint( $report['duplicate_donor'] ) + 1 ) : 1 );
		}
	}

	if ( empty( $donor_data->id ) && ! empty( $data['email'] ) && empty( $dry_run_donor_create ) ) {

		$donor_data = new WalkTheCounty_Donor( $data['email'] );
		if ( empty( $donor_data->id ) ) {
			$donor_data = get_user_by( 'email', $data['email'] );

			if ( empty( $donor_data->ID ) && isset( $import_setting['create_user'] ) && 1 === absint( $import_setting['create_user'] ) ) {
				$data['first_name'] = ( ! empty( $data['first_name'] ) ? $data['first_name'] : $data['email'] );
				$data['last_name']  = ( ! empty( $data['last_name'] ) ? $data['last_name'] : '' );
				$walkthecounty_role          = (array) walkthecounty_get_option( 'donor_default_user_role', get_option( 'default_role', ( ( $walkthecounty_donor = wp_roles()->is_role( 'walkthecounty_donor' ) ) && ! empty( $walkthecounty_donor ) ? 'walkthecounty_donor' : 'subscriber' ) ) );
				$donor_args         = array(
					'user_login'      => $data['email'],
					'user_email'      => $data['email'],
					'user_registered' => date( 'Y-m-d H:i:s' ),
					'user_first'      => $data['first_name'],
					'user_last'       => $data['last_name'],
					'user_pass'       => wp_generate_password( 8, true ),
					'role'            => $walkthecounty_role,
				);

				/**
				 * Filter to modify user data before new user id register.
				 *
				 * @since 1.8.13
				 */
				$donor_args = (array) apply_filters( 'walkthecounty_import_insert_user_args', $donor_args, $data, $import_setting );

				if ( empty( $dry_run ) ) {

					// This action was added to remove the login when using the walkthecounty register function.
					add_filter( 'walkthecounty_log_user_in_on_register', 'walkthecounty_log_user_in_on_register_callback', 11 );
					$donor_id = walkthecounty_register_and_login_new_user( $donor_args );
					remove_filter( 'walkthecounty_log_user_in_on_register', 'walkthecounty_log_user_in_on_register_callback', 11 );

					$donor_data = new WalkTheCounty_Donor( $donor_id, true );
					$donor_data->update_meta( '_walkthecounty_payment_import', true );

				} else {
					$dry_run_donor_create   = true;
					$report['create_donor'] = ( ! empty( $report['create_donor'] ) ? ( absint( $report['create_donor'] ) + 1 ) : 1 );
				}
			} else {
				$donor_id = ( ! empty( $donor_data->ID ) ? $donor_data->ID : false );
			}

			if ( empty( $dry_run_donor_create ) && ( ! empty( $donor_id ) || ( isset( $import_setting['create_user'] ) && 0 === absint( $import_setting['create_user'] ) ) ) ) {
				$donor_data = new WalkTheCounty_Donor( $donor_id, true );

				if ( empty( $donor_data->id ) ) {

					if ( ! empty( $data['form_id'] ) ) {
						$form = new WalkTheCounty_Donate_Form( $data['form_id'] );
					}

					if ( empty( $dry_run ) ) {
						$payment_title = ( isset( $data['form_title'] ) ? $data['form_title'] : ( isset( $form ) ? $form->get_name() : __( 'New Form', 'walkthecounty' ) ) );
						$donor_args    = array(
							'name'  => ! is_email( $payment_title ) ? $data['first_name'] . ' ' . $data['last_name'] : '',
							'email' => $data['email'],
						);
						if ( ! empty( $donor_id ) ) {
							$donor_args['user_id'] = $donor_id;
						}
						$donor_data->create( $donor_args );

						// Adding notes that donor is being imported from CSV.
						$current_user = wp_get_current_user();
						$donor_data->add_note( wp_sprintf( __( 'This donor was imported by %s', 'walkthecounty' ), $current_user->user_email ) );
					} else {
						$dry_run_donor_create = true;
					}
					$report['create_donor'] = ( ! empty( $report['create_donor'] ) ? ( absint( $report['create_donor'] ) + 1 ) : 1 );
				} else {
					$report['duplicate_donor'] = ( ! empty( $report['duplicate_donor'] ) ? ( absint( $report['duplicate_donor'] ) + 1 ) : 1 );
				}
			}
		} else {
			$report['duplicate_donor'] = ( ! empty( $report['duplicate_donor'] ) ? ( absint( $report['duplicate_donor'] ) + 1 ) : 1 );
		}
	}

	// update the report
	walkthecounty_import_donation_report_update( $report );

	return $donor_data;
}

/**
 * Return the option that are default options.
 *
 * @since 1.8.13
 */
function walkthecounty_import_default_options() {
	/**
	 * Filter to modify default option in the import dropdown
	 *
	 * @since 1.8.13
	 *
	 * @return array
	 */
	return (array) apply_filters( 'walkthecounty_import_default_options', array(
		'' => __( 'Do not import', 'walkthecounty' ),
	) );
}

/**
 * Return the option that are related to donations.
 *
 * @since 1.8.13
 */
function walkthecounty_import_donations_options() {
	/**
	 * Filter to modify donations option in the import dropdown
	 *
	 * @since 1.8.13
	 *
	 * @return array
	 */
	return (array) apply_filters( 'walkthecounty_import_donations_options', array(
		'id'           => __( 'Donation ID', 'walkthecounty' ),
		'amount'       => array(
			__( 'Donation Amount', 'walkthecounty' ),
			__( 'Amount', 'walkthecounty' ),
			__( 'Donation Total', 'walkthecounty' ),
			__( 'Total', 'walkthecounty' ),
		),
		'currency'     => array(
			__( 'Donation Currencies', 'walkthecounty' ),
			__( 'Currencies', 'walkthecounty' ),
			__( 'Currencies Code', 'walkthecounty' ),
			__( 'Currency Code', 'walkthecounty' ),
			__( 'Code', 'walkthecounty' ),
		),
		'post_date'    => array(
			__( 'Donation Date', 'walkthecounty' ),
			__( 'Date', 'walkthecounty' ),
		),
		'post_time'    => array(
			__( 'Donation Time', 'walkthecounty' ),
			__( 'Time', 'walkthecounty' ),
		),
		'title_prefix' => array(
			__( 'Title Prefix', 'walkthecounty' ),
			__( 'Prefix', 'walkthecounty' ),
		),
		'first_name'   => array(
			__( 'Donor First Name', 'walkthecounty' ),
			__( 'First Name', 'walkthecounty' ),
			__( 'Name', 'walkthecounty' ),
			__( 'First', 'walkthecounty' ),
		),
		'last_name'    => array(
			__( 'Donor Last Name', 'walkthecounty' ),
			__( 'Last Name', 'walkthecounty' ),
			__( 'Last', 'walkthecounty' ),
		),
		'company_name' => array(
			__( 'Company Name', 'walkthecounty' ),
			__( 'Donor Company Name', 'walkthecounty' ),
			__( 'Donor Company', 'walkthecounty' ),
			__( 'Company', 'walkthecounty' ),
		),
		'line1'        => array(
			__( 'Address 1', 'walkthecounty' ),
			__( 'Address', 'walkthecounty' ),
		),
		'line2'        => __( 'Address 2', 'walkthecounty' ),
		'city'         => __( 'City', 'walkthecounty' ),
		'state'        => array(
			__( 'State', 'walkthecounty' ),
			__( 'Province', 'walkthecounty' ),
			__( 'County', 'walkthecounty' ),
			__( 'Region', 'walkthecounty' ),
		),
		'country'      => __( 'Country', 'walkthecounty' ),
		'zip'          => array(
			__( 'Zip Code', 'walkthecounty' ),
			__( 'Zip', 'walkthecounty' ),
			__( 'zipcode', 'walkthecounty' ),
			__( 'Postal Code', 'walkthecounty' ),
			__( 'Postal', 'walkthecounty' ),
		),
		'email'        => array(
			__( 'Donor Email', 'walkthecounty' ),
			__( 'Email', 'walkthecounty' ),
			__( 'Email Address', 'walkthecounty' ),
		),
		'post_status'  => array(
			__( 'Donation Status', 'walkthecounty' ),
			__( 'Status', 'walkthecounty' ),
		),
		'gateway'      => array(
			__( 'Payment Method', 'walkthecounty' ),
			__( 'Method', 'walkthecounty' ),
			__( 'Payment Gateway', 'walkthecounty' ),
			__( 'Gateway', 'walkthecounty' ),
		),
		'notes'        => __( 'Notes', 'walkthecounty' ),
		'mode'         => array(
			__( 'Payment Mode', 'walkthecounty' ),
			__( 'Mode', 'walkthecounty' ),
			__( 'Test Mode', 'walkthecounty' ),
		),
		'donor_ip'     => __( 'Donor IP Address', 'walkthecounty' ),
		'post_meta'    => __( 'Import as Meta', 'walkthecounty' ),
	) );
}

/**
 * Return the option that are related to donations.
 *
 * @since 1.8.13
 */
function walkthecounty_import_donor_options() {
	/**
	 * Filter to modify donors option in the import dropdown
	 *
	 * @since 1.8.13
	 *
	 * @return array
	 */
	return (array) apply_filters( 'walkthecounty_import_donor_options', array(
		'donor_id' => __( 'Donor ID', 'walkthecounty' ),
		'user_id'  => __( 'User ID', 'walkthecounty' ),
	) );
}

/**
 * Return the option that are related to donations.
 *
 * @since 1.8.13
 */
function walkthecounty_import_donation_form_options() {
	/**
	 * Filter to modify form option in the import dropdown
	 *
	 * @since 1.8.13
	 *
	 * @return array
	 */
	return (array) apply_filters( 'walkthecounty_import_donation_form_options', array(
		'form_title'              => array(
			__( 'Donation Form Title', 'walkthecounty' ),
			__( 'Donation Form', 'walkthecounty' ),
			__( 'Form Name', 'walkthecounty' ),
			__( 'Title', 'walkthecounty' ),
			__( 'Form Title', 'walkthecounty' ),
			'ignore' => array(
				__( 'Title Prefix', 'walkthecounty' ),
				__( 'Prefix', 'walkthecounty' ),
			),
		),
		'form_id'                 => array(
			__( 'Donation Form ID', 'walkthecounty' ),
			__( 'Form ID', 'walkthecounty' ),
		),
		'form_level'              => array(
			__( 'Donation Level', 'walkthecounty' ),
			__( 'Level', 'walkthecounty' ),
			__( 'Level Title', 'walkthecounty' ),
		),
		'form_custom_amount_text' => __( 'Custom Amount Text', 'walkthecounty' ),
	) );
}

/**
 * Import CSV in DB
 *
 * @param int    $file_id   CSV id.
 * @param int    $start     Start from which csv line.
 * @param int    $end       End from which csv line.
 * @param string $delimiter CSV delimeter.
 *
 * @return array
 */
function walkthecounty_get_donation_data_from_csv( $file_id, $start, $end, $delimiter = 'csv' ) {
	/**
	 * Filter to modify delimiter of Import
	 *
	 * @since 1.8.14
	 *
	 * @param string $delimiter
	 *
	 * @return string $delimiter
	 */
	$delimiter = (string) apply_filters( 'walkthecounty_import_delimiter_set', $delimiter );

	$file_dir = walkthecounty_get_file_data_by_file_id( $file_id );

	return walkthecounty_get_raw_data_from_file( $file_dir, $start, $end, $delimiter );
}

/**
 * Get raw data from file data
 *
 * @since 2.1
 *
 * @param $file_dir
 * @param $start
 * @param $end
 * @param $delimiter
 *
 * @return array
 */
function walkthecounty_get_raw_data_from_file( $file_dir, $start, $end, $delimiter ) {
	$raw_data = array();

	$count    = 0;
	if ( false !== ( $handle = fopen( $file_dir, 'r' ) ) ) {
		while ( false !== ( $row = fgetcsv( $handle, 0, $delimiter ) ) ) {
			if ( $count >= $start && $count <= $end ) {
				$raw_data[] = $row;
			}
			$count ++;
		}
		fclose( $handle );
	}

	return $raw_data;
}

/**
 * Get content from the attachment id of CSV
 *
 * @since 2.1
 *
 * @param $file_id
 *
 * @return false|string file content
 */
function walkthecounty_get_file_data_by_file_id( $file_id ) {
	return get_attached_file( $file_id );
}


/**
 * Remove login when user register with walkthecounty functions.
 *
 * @since 1.8.13
 *
 * @param $value
 *
 * @return bool
 */
function walkthecounty_log_user_in_on_register_callback( $value ) {
	return false;
}

/**
 * Add import Donation forms, donations , donor from CSV to database
 *
 * @since 1.8.13
 *
 * @param array $raw_key Setup bu user at step 2.
 * @param array $row_data Feilds that are being imported from CSV
 * @param array $main_key First row from the CSV
 * @param array $import_setting Contain the global variable.
 *
 * @return bool
 */
function walkthecounty_save_import_donation_to_db( $raw_key, $row_data, $main_key = array(), $import_setting = array() ) {
	$data                          = array_combine( $raw_key, $row_data );
	$price_id                      = false;
	$donor_id                      = 0;
	$donor_data                    = array();
	$form                          = array();
	$import_setting['create_user'] = isset( $import_setting['create_user'] ) ? $import_setting['create_user'] : 1;
	$dry_run                       = isset( $import_setting['dry_run'] ) ? $import_setting['dry_run'] : false;
	$_dry_run_is_duplicate         = false;
	$dry_run_duplicate_form        = false;
	$dry_run_duplicate_donor       = false;
	$donation_key                  = empty( $import_setting['donation_key'] ) ? 1 : (int) $import_setting['donation_key'];
	$payment_id                    = false;

	$data = (array) apply_filters( 'walkthecounty_save_import_donation_to_db', $data );

	$data['amount'] = walkthecounty_maybe_sanitize_amount( $data['amount'] );
	$diff           = array();

	if ( ! empty( $dry_run ) && 1 !== $donation_key ) {
		$csv_raw_data = empty( $import_setting['csv_raw_data'] ) ? array() : $import_setting['csv_raw_data'];
		$donors_list  = empty( $import_setting['donors_list'] ) ? array() : $import_setting['donors_list'];
		$key          = $donation_key - 1;
		for ( $i = 0; $i < $key; $i ++ ) {
			$csv_data           = array_combine( $raw_key, $csv_raw_data[ $i ] );
			$csv_data['amount'] = walkthecounty_maybe_sanitize_amount( $csv_data['amount'] );
			// check for duplicate donations
			$diff = array_diff( $csv_data, $data );
			if ( empty( $diff ) ) {
				$_dry_run_is_duplicate   = true;
				$dry_run_duplicate_form  = true;
				$dry_run_duplicate_donor = true;
			} else {
				// check for duplicate donation form with form id
				if ( ! empty( $csv_data['form_id'] ) && ! empty( $data['form_id'] ) && $csv_data['form_id'] === $data['form_id'] ) {
					$form    = new WalkTheCounty_Donate_Form( $data['form_id'] );
					$form_id = $form->get_ID();
					if ( ! empty( $form_id ) ) {
						$dry_run_duplicate_form = true;
					}
				}
				// check for duplicate donation form with form title
				if ( empty( $dry_run_duplicate_form ) && ! empty( $csv_data['form_title'] ) && ! empty( $data['form_title'] ) && $csv_data['form_title'] === $data['form_title'] ) {
					$dry_run_duplicate_form = true;
				}


				// check for duplicate donor by donor id
				if ( ! empty( $csv_data['donor_id'] ) && ! empty( $data['donor_id'] ) && $csv_data['donor_id'] === $data['donor_id'] ) {
					$donor = array_search( (int) $data['donor_id'], array_column( 'id', $donors_list ) );
					if ( ! empty( $donor ) ) {
						$dry_run_duplicate_donor = true;
					}
				}

				// check for duplicate donor by user id
				if ( empty( $dry_run_duplicate_donor ) && ! empty( $csv_data['user_id'] ) && ! empty( $data['user_id'] ) && $csv_data['user_id'] === $data['user_id'] ) {
					$donor = array_search( (int) $data['user_id'], array_column( 'user_id', $donors_list ) );
					if ( ! empty( $donor ) ) {
						$dry_run_duplicate_donor = true;
					} else {
						$donor = get_user_by( 'id', $csv_data['user_id'] );
						if ( ! empty( $donor->ID ) ) {
							$dry_run_duplicate_donor = true;
						}
					}
				}

				// check for duplicate donor by donor id
				if ( empty( $dry_run_duplicate_donor ) && ! empty( $csv_data['email'] ) && ! empty( $data['email'] ) && $csv_data['email'] === $data['email'] ) {
					$dry_run_duplicate_donor = true;
				}
			}
		}
	}

	if ( empty( $dry_run_duplicate_donor ) ) {
		// Here come the login function.
		$donor_data = walkthecounty_import_get_user_from_csv( $data, $import_setting );
		if ( empty( $dry_run ) ) {
			if ( ! empty( $donor_data->id ) ) {
				$donor_id = $donor_data->id;
			} else {
				return $payment_id;
			}
		}
	} else {
		// Get the report
		$report                    = walkthecounty_import_donation_report();
		$report['duplicate_donor'] = ( ! empty( $report['duplicate_donor'] ) ? ( absint( $report['duplicate_donor'] ) + 1 ) : 1 );
		// update the report
		walkthecounty_import_donation_report_update( $report );
	}

	if ( empty( $dry_run_duplicate_form ) ) {
		// get form data or register a form data.
		$form = walkthecounty_import_get_form_data_from_csv( $data, $import_setting );
		if ( false == $form && empty( $dry_run ) ) {
			return $payment_id;
		} else {
			$price_id = ( ! empty( $form->price_id ) ) ? $form->price_id : false;
		}
	} else {
		// Get the report
		$report                   = walkthecounty_import_donation_report();
		$report['duplicate_form'] = ( ! empty( $report['duplicate_form'] ) ? ( absint( $report['duplicate_form'] ) + 1 ) : 1 );
		// update the report
		walkthecounty_import_donation_report_update( $report );
	}

	// Get the report
	$report = walkthecounty_import_donation_report();


	$status  = walkthecounty_import_donation_get_status( $data );
	$country = ( ! empty( $data['country'] ) ? ( ( $country_code = array_search( $data['country'], walkthecounty_get_country_list() ) ) ? $country_code : $data['country'] ) : '' );
	$state   = ( ! empty( $data['state'] ) ? ( ( $state_code = array_search( $data['state'], walkthecounty_get_states( $country ) ) ) ? $state_code : $data['state'] ) : '' );

	$address = array(
		'line1'   => ( ! empty( $data['line1'] ) ? walkthecounty_clean( $data['line1'] ) : '' ),
		'line2'   => ( ! empty( $data['line2'] ) ? walkthecounty_clean( $data['line2'] ) : '' ),
		'city'    => ( ! empty( $data['city'] ) ? walkthecounty_clean( $data['city'] ) : '' ),
		'zip'     => ( ! empty( $data['zip'] ) ? walkthecounty_clean( $data['zip'] ) : '' ),
		'state'   => $state,
		'country' => $country,
	);

	$test_mode = array( 'test', 'true', );
	$post_date = current_time( 'mysql' );
	if ( ! empty( $data['post_date'] ) ) {
		if ( ! empty( $data['post_time'] ) ) {
			$post_date = mysql2date( 'Y-m-d', $data['post_date'] );
			$post_date = mysql2date( 'Y-m-d H:i:s', $post_date . ' ' . $data['post_time'] );
		} else {
			$post_date = mysql2date( 'Y-m-d H:i:s', $data['post_date'] );
		}
	}

	//Create payment_data array
	$payment_data = array(
		'donor_id'        => $donor_id,
		'price'           => $data['amount'],
		'status'          => $status,
		'currency'        => ! empty( $data['currency'] ) && array_key_exists( $data['currency'], walkthecounty_get_currencies_list() ) ? $data['currency'] : walkthecounty_get_currency(),
		'user_info'       => array(
			'id'         => $donor_id,
			'email'      => ( ! empty( $data['email'] ) ? $data['email'] : ( isset( $donor_data->email ) ? $donor_data->email : false ) ),
			'first_name' => ( ! empty( $data['first_name'] ) ? $data['first_name'] : ( ! empty( $donor_id ) && ( $first_name = get_user_meta( $donor_id, 'first_name', true ) ) ? $first_name : $donor_data->name ) ),
			'last_name'  => ( ! empty( $data['last_name'] ) ? $data['last_name'] : ( ! empty( $donor_id ) && ( $last_name = get_user_meta( $donor_id, 'last_name', true ) ) ? $last_name : $donor_data->name ) ),
			'address'    => $address,
			'title'      => ! empty( $data['title_prefix'] ) ? $data['title_prefix'] : '',
		),
		'gateway'         => ( ! empty( $data['gateway'] ) ? strtolower( $data['gateway'] ) : 'manual' ),
		'walkthecounty_form_title' => ( ! empty( $data['form_title'] ) ? $data['form_title'] : ( method_exists( $form, 'get_name' ) ? $form->get_name() : '' ) ),
		'walkthecounty_form_id'    => method_exists( $form, 'get_ID' ) ? $form->get_ID() : '',
		'walkthecounty_price_id'   => $price_id,
		'purchase_key'    => strtolower( md5( uniqid() ) ),
		'user_email'      => $data['email'],
		'post_date'       => $post_date,
		'mode'            => ( ! empty( $data['mode'] ) ? ( in_array( strtolower( $data['mode'] ), $test_mode ) ? 'test' : 'live' ) : ( isset( $import_setting['mode'] ) ? ( true == (bool) $import_setting['mode'] ? 'test' : 'live' ) : ( walkthecounty_is_test_mode() ? 'test' : 'live' ) ) ),
	);

	/**
	 * Filter to modify payment Data before getting imported.
	 *
	 * @since 2.1.0
	 *
	 * @param array $payment_data payment data
	 * @param array $payment_data donation data
	 * @param array $donor_data donor data
	 * @param object $donor_data form object
	 *
	 * @return array $payment_data payment data
	 */
	$payment_data = apply_filters( 'walkthecounty_import_before_import_payment', $payment_data, $data, $donor_data, $form );

	// Get the report
	$report = walkthecounty_import_donation_report();

	// Check for duplicate code.
	$donation_duplicate = walkthecounty_check_import_donation_duplicate( $payment_data, $data, $form, $donor_data );
	if ( false !== $donation_duplicate || ! empty( $_dry_run_is_duplicate ) ) {
		$report['donation_details'][ $import_setting['donation_key'] ]['duplicate'] = $donation_duplicate;
		$report['duplicate_donation']                                               = ( ! empty( $report['duplicate_donation'] ) ? ( absint( $report['duplicate_donation'] ) + 1 ) : 1 );
	} else {

		if ( empty( $dry_run ) ) {
			add_action( 'walkthecounty_update_payment_status', 'walkthecounty_donation_import_insert_default_payment_note', 1, 1 );
			add_filter( 'walkthecounty_insert_payment_args', 'walkthecounty_donation_import_walkthecounty_insert_payment_args', 11, 2 );
			add_filter( 'walkthecounty_update_donor_information', 'walkthecounty_donation_import_update_donor_information', 11, 3 );
			add_action( 'walkthecounty_insert_payment', 'walkthecounty_import_donation_insert_payment', 11, 2 );
			add_filter( 'walkthecounty_is_stop_email_notification', '__return_true' );

			// if it status is other then pending then first change the donation status to pending and after adding the payment meta update the donation status.
			if ( 'pending' !== $status ) {
				unset( $payment_data['status'] );
			}

			$payment_id = walkthecounty_insert_payment( $payment_data );
			remove_action( 'walkthecounty_update_payment_status', 'walkthecounty_donation_import_insert_default_payment_note', 1 );
			remove_filter( 'walkthecounty_insert_payment_args', 'walkthecounty_donation_import_walkthecounty_insert_payment_args', 11 );
			remove_filter( 'walkthecounty_update_donor_information', 'walkthecounty_donation_import_update_donor_information', 11 );
			remove_action( 'walkthecounty_insert_payment', 'walkthecounty_import_donation_insert_payment', 11 );
			remove_filter( 'walkthecounty_is_stop_email_notification', '__return_true' );

			if ( $payment_id ) {

				$payment = new WalkTheCounty_Payment( $payment_id );

				$report['create_donation'] = ( ! empty( $report['create_donation'] ) ? ( absint( $report['create_donation'] ) + 1 ) : 1 );

				$payment->update_meta( '_walkthecounty_payment_import', true );

				if ( ! empty( $import_setting['csv'] ) ) {
					$payment->update_meta( '_walkthecounty_payment_import_id', $import_setting['csv'] );
				}

				// Insert Company Name.
				if ( ! empty( $data['company_name'] ) ) {
					$payment->update_meta( '_walkthecounty_donation_company', $data['company_name'] );
					$donor_data->update_meta( '_walkthecounty_donor_company', $data['company_name'] );
				}

				// Insert Donor IP address.
				if ( ! empty( $data['donor_ip'] ) ) {
					$payment->update_meta( '_walkthecounty_payment_donor_ip', $data['donor_ip'] );
				}

				// Insert Notes.
				if ( ! empty( $data['notes'] ) ) {
					$payment->add_note( $data['notes'] );
				}

				$meta_exists = array_keys( $raw_key, 'post_meta' );
				if ( ! empty( $main_key ) && ! empty( $meta_exists ) ) {
					foreach ( $meta_exists as $meta_exist ) {
						if ( ! empty( $main_key[ $meta_exist ] ) && ! empty( $row_data[ $meta_exist ] ) ) {
							$payment->update_meta( $main_key[ $meta_exist ], $row_data[ $meta_exist ] );
						}
					}
				}

				// update the donation status if it's other then pending
				if ( 'pending' !== $status ) {
					$payment->update_status( $status );
				}

			} else {
				$report['failed_donation'] = ( ! empty( $report['failed_donation'] ) ? ( absint( $report['failed_donation'] ) + 1 ) : 1 );
				$payment_id                = false;
			}

			/**
			 * Fire after payment is imported and payment meta is also being imported.
			 *
			 * @since 2.1.0
			 *
			 * @param int $payment payment id
			 * @param array $payment_data payment data
			 * @param array $payment_data donation data
			 * @param array $donor_data donor data
			 * @param object $donor_data form object
			 */
			do_action( 'walkthecounty_import_after_import_payment', $payment, $payment_data, $data, $donor_data, $form );
		} else {
			$report['create_donation'] = ( ! empty( $report['create_donation'] ) ? ( absint( $report['create_donation'] ) + 1 ) : 1 );
			$payment_id                = true;
		}
	}

	// update the report
	walkthecounty_import_donation_report_update( $report );

	return $payment_id;
}

/**
 * Get Donation form status
 *
 * @since 2.0.2
 *
 * @param array $data donation data that is goingt o get imported
 *
 * @return string $status Donation status.
 */
function walkthecounty_import_donation_get_status( $data ) {
	if ( empty( $data['post_status'] ) ) {
		return 'publish';
	}

	$status = 'publish';

	$donation_status = trim( $data['post_status'] );
	$donation_status_key      = strtolower( preg_replace( '/\s+/', '', $donation_status ) );

	foreach ( walkthecounty_get_payment_statuses() as $key => $value ) {
		$match = false;
		if ( $key === $donation_status_key ) {
			$match = true;
		} else if ( stristr( $donation_status, $value ) ) {
			$match = true;
		}

		if ( ! empty( $match ) ) {
			$status = $key;
			break;
		}
	}

	return $status;
}

/**
 * Alter donor information when importing donations from CSV
 *
 * @since 1.8.13
 *
 * @param $donor
 * @param $payment_id
 * @param $payment_data
 *
 * @return WalkTheCounty_Donor
 */
function walkthecounty_donation_import_update_donor_information( $donor, $payment_id, $payment_data ) {
	$old_donor = $donor;
	if ( ! empty( $payment_data['donor_id'] ) ) {
		$donor_id = absint( $payment_data['donor_id'] );
		$donor    = new WalkTheCounty_Donor( $donor_id );
		if ( ! empty( $donor->id ) ) {
			return $donor;
		}
	}

	return $old_donor;
}

/*
 * WalkTheCounty update purchase_count of walkthecounty customer.
 *
 * @since 1.8.13
 */
function walkthecounty_import_donation_insert_payment( $payment_id, $payment_data ) {
	// Update WalkTheCounty Customers purchase_count
	if ( ! empty( $payment_data['status'] ) && ( 'complete' === (string) $payment_data['status'] || 'publish' === (string) $payment_data['status'] ) ) {
		$donor_id = (int) get_post_meta( $payment_id, '_walkthecounty_payment_customer_id', true );
		if ( ! empty( $donor_id ) ) {
			$donor = new WalkTheCounty_Donor( $donor_id );
			$donor->increase_purchase_count();
		}
	}
}

/**
 * Add author id in in donation post
 *
 * @since 1.8.13
 */
function walkthecounty_donation_import_walkthecounty_insert_payment_args( $args, $payment_data ) {
	if ( ! empty( $payment_data['user_info']['id'] ) ) {
		$args['post_author'] = (int) $payment_data['user_info']['id'];
	}

	return $args;
}

/**
 * Check if Import donation is duplicate
 *
 * @since 1.8.13
 */
function walkthecounty_check_import_donation_duplicate( $payment_data, $data, $form, $donor_data ) {
	$return = false;
	if ( ! empty( $data['post_date'] ) ) {
		$post_date = mysql2date( 'Y-m-d-H-i-s', $payment_data['post_date'] );
		$post_date = explode( '-', $post_date );
		$args      = array(
			'output'                 => 'post',
			'cache_results'          => false,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
			'date_query'             => array(
				array(
					'year'   => $post_date[0],
					'month'  => $post_date[1],
					'day'    => $post_date[2],
					'hour'   => $post_date[3],
					'minute' => $post_date[4],
					'second' => $post_date[5],
				),
			),
			'meta_query'             => array(
				array(
					'key'     => '_walkthecounty_payment_total',
					'value'   => preg_replace( '/[\$,]/', '', $payment_data['price'] ),
					'compare' => 'LIKE',
				),
				array(
					'key'     => '_walkthecounty_payment_form_id',
					'value'   => $payment_data['walkthecounty_form_id'],
					'type'    => 'numeric',
					'compare' => '=',
				),
				array(
					'key'     => '_walkthecounty_payment_gateway',
					'value'   => $payment_data['gateway'],
					'compare' => '=',
				),
				array(
					'key'     => '_walkthecounty_payment_donor_id',
					'value'   => isset( $donor_data->id ) ? $donor_data->id : '',
					'compare' => '=',
				),
				array(
					'key'     => '_walkthecounty_payment_mode',
					'value'   => $payment_data['mode'],
					'compare' => '=',
				),
			),
		);

		$payments  = new WalkTheCounty_Payments_Query( $args );
		$donations = $payments->get_payments();
		if ( ! empty( $donations ) ) {
			$return = $donations;
		}
	}

	/**
	 * Filter to modify donation which is getting add is duplicate or not.
	 *
	 * @since 1.8.18
	 */
	return apply_filters( 'walkthecounty_check_import_donation_duplicate', $return, $payment_data, $data, $form, $donor_data );
}

/**
 * Record payment notes that is being imported from CSV.
 *
 * @since  1.8.13
 *
 * @param  int $payment_id The ID number of the payment.
 *
 * @return void
 */
function walkthecounty_donation_import_insert_default_payment_note( $payment_id ) {
	$current_user = wp_get_current_user();
	walkthecounty_insert_payment_note( $payment_id, wp_sprintf( __( 'This donation was imported by %s', 'walkthecounty' ), $current_user->user_email ) );
}

/**
 * Return Import Page URL
 *
 * @since 1.8.13
 *
 * @param array $parameter
 *
 * @return string URL
 */
function walkthecounty_import_page_url( $parameter = array() ) {
	$defalut_query_arg = array(
		'post_type'     => 'walkthecounty_forms',
		'page'          => 'walkthecounty-tools',
		'tab'           => 'import',
		'importer-type' => 'import_donations',
	);
	$import_query_arg  = wp_parse_args( $parameter, $defalut_query_arg );

	return add_query_arg( $import_query_arg, admin_url( 'edit.php' ) );
}
