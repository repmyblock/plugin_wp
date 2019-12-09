<?php
/**
 * Earnings Export Class
 *
 * This class handles earnings export
 *
 * @package     WalkTheCounty
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WalkTheCounty_Earnings_Export Class
 *
 * @since 1.0
 */
class WalkTheCounty_Earnings_Export extends WalkTheCounty_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 * @var string
	 * @since 1.0
	 */
	public $export_type = 'earnings';

	/**
	 * Set the export headers
	 *
	 * @access public
	 * @since  1.6
	 * @return void
	 */
	public function headers() {
		walkthecounty_ignore_user_abort();

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . apply_filters( 'walkthecounty_earnings_export_filename', 'walkthecounty-export-' . $this->export_type . '-' . date( 'n' ) . '-' . date( 'Y' ) ) . '.csv' );
		header( "Expires: 0" );

	}

	/**
	 * Set the CSV columns
	 *
	 * @access public
	 * @since  1.0
	 * @return array $cols All the columns
	 */
	public function csv_cols() {

		$cols = array(
			'date'      => __( 'Date', 'walkthecounty' ),
			'donations' => __( 'Donations', 'walkthecounty' ),
			/* translators: %s: currency */
			'earnings'  => sprintf( __( 'Income (%s)', 'walkthecounty' ), walkthecounty_currency_symbol('', true) )
		);

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @access public
	 * @since  1.0
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {

		$start_year  = isset( $_POST['start_year'] ) ? absint( $_POST['start_year'] ) : date( 'Y' );
		$end_year    = isset( $_POST['end_year'] ) ? absint( $_POST['end_year'] ) : date( 'Y' );
		$start_month = isset( $_POST['start_month'] ) ? absint( $_POST['start_month'] ) : date( 'n' );
		$end_month   = isset( $_POST['end_month'] ) ? absint( $_POST['end_month'] ) : date( 'n' );

		$data  = array();
		$year  = $start_year;
		$stats = new WalkTheCounty_Payment_Stats;

		while ( $year <= $end_year ) {

			if ( $year == $start_year && $year == $end_year ) {

				$m1 = $start_month;
				$m2 = $end_month;

			} elseif ( $year == $start_year ) {

				$m1 = $start_month;
				$m2 = 12;

			} elseif ( $year == $end_year ) {

				$m1 = 1;
				$m2 = $end_month;

			} else {

				$m1 = 1;
				$m2 = 12;

			}

			while ( $m1 <= $m2 ) {

				$date1 = mktime( 0, 0, 0, $m1, 1, $year );
				$date2 = mktime( 0, 0, 0, $m1, cal_days_in_month( CAL_GREGORIAN, $m1, $year ), $year );

				$data[] = array(
					'date'      => date_i18n( 'F Y', $date1 ),
					'donations' => $stats->get_sales( 0, $date1, $date2 ),
					'earnings'  => walkthecounty_format_amount( $stats->get_earnings( 0, $date1, $date2 ), array( 'sanitize' => false ) ),
				);

				$m1 ++;

			}


			$year ++;

		}

		$data = apply_filters( 'walkthecounty_export_get_data', $data );
		$data = apply_filters( "walkthecounty_export_get_data_{$this->export_type}", $data );

		return $data;
	}
}
