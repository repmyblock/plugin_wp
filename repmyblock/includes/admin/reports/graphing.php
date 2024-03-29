<?php
/**
 * Graphing Functions
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
 * Show report graphs
 *
 * @since 1.0
 * @return void
 */
function walkthecounty_reports_graph() {
	// Retrieve the queried dates.
	$donation_stats = new WalkTheCounty_Payment_Stats();
	$dates          = walkthecounty_get_report_dates();

	// Determine graph options.
	switch ( $dates['range'] ) :
		case 'today':
		case 'yesterday':
			$day_by_day = true;
			break;
		case 'last_year':
		case 'this_year':
		case 'last_quarter':
		case 'this_quarter':
			$day_by_day = false;
			break;
		case 'other':
			if ( $dates['m_end'] - $dates['m_start'] >= 2 || $dates['year_end'] > $dates['year'] && ( $dates['m_start'] != '12' && $dates['m_end'] != '1' ) ) {
				$day_by_day = false;
			} else {
				$day_by_day = true;
			}
			break;
		default:
			$day_by_day = true;
			break;
	endswitch;

	$earnings_totals = 0.00; // Total earnings for time period shown.
	$sales_totals    = 0; // Total sales for time period shown.

	$earnings_data = array();
	$sales_data    = array();

	if ( 'today' === $dates['range'] || 'yesterday' === $dates['range'] ) {

		// Hour by hour.
		$hour  = 0;
		$month = date( 'n', current_time( 'timestamp' ) );
		while ( $hour <= 23 ) :

			$start_date = mktime( $hour, 0, 0, $month, $dates['day'], $dates['year'] );
			$end_date   = mktime( $hour, 59, 59, $month, $dates['day'], $dates['year'] );
			$sales      = $donation_stats->get_sales( 0, $start_date, $end_date );
			$earnings   = $donation_stats->get_earnings( 0, $start_date, $end_date );

			$sales_totals    += $sales;
			$earnings_totals += $earnings;

			$sales_data[]    = array( $start_date * 1000, $sales );
			$earnings_data[] = array( $start_date * 1000, $earnings );

			$hour ++;
		endwhile;

	} elseif ( 'this_week' === $dates['range'] || 'last_week' === $dates['range'] ) {

		// Day by day.
		$day     = $dates['day'];
		$day_end = $dates['day_end'];
		$month   = $dates['m_start'];
		while ( $day <= $day_end ) :

			$start_date = mktime( 0, 0, 0, $month, $day, $dates['year'] );
			$end_date   = mktime( 23, 59, 59, $month, $day, $dates['year'] );
			$sales      = $donation_stats->get_sales( 0, $start_date, $end_date );
			$earnings   = $donation_stats->get_earnings( 0, $start_date, $end_date );

			$sales_totals    += $sales;
			$earnings_totals += $earnings;

			$sales_data[]    = array( $start_date * 1000, $sales );
			$earnings_data[] = array( $start_date * 1000, $earnings );
			$day ++;
		endwhile;

	} else {

		$y = $dates['year'];
		while ( $y <= $dates['year_end'] ) :

			if ( $dates['year'] === $dates['year_end'] ) {
				$month_start = $dates['m_start'];
				$month_end   = $dates['m_end'];
			} elseif ( $y === $dates['year'] ) {
				$month_start = $dates['m_start'];
				$month_end   = 12;
			} elseif ( $y === $dates['year_end'] ) {
				$month_start = 1;
				$month_end   = $dates['m_end'];
			} else {
				$month_start = 1;
				$month_end   = 12;
			}

			$i = $month_start;
			while ( $i <= $month_end ) :

				if ( $day_by_day ) {

					if ( $i === $month_end ) {

						$num_of_days = $dates['day_end'];

					} else {

						$num_of_days = cal_days_in_month( CAL_GREGORIAN, $i, $y );

					}

					$d = $dates['day'];

					while ( $d <= $num_of_days ) :

						$start_date = mktime( 0, 0, 0, $i, $d, $y );
						$end_date   = mktime( 23, 59, 59, $i, $d, $y );
						$sales      = $donation_stats->get_sales( 0, $start_date, $end_date );
						$earnings   = $donation_stats->get_earnings( 0, $start_date, $end_date );

						$sales_totals    += $sales;
						$earnings_totals += $earnings;

						$sales_data[]    = array( $start_date * 1000, $sales );
						$earnings_data[] = array( $start_date * 1000, $earnings );

						$d ++;

					endwhile;

				} else {

					// This Quarter, Last Quarter, This Year, Last Year.
					$start_date = mktime( 0, 0, 0, $i, 1, $y );
					$end_date   = mktime( 23, 59, 59, $i + 1, 0, $y );
					$sales      = $donation_stats->get_sales( 0, $start_date, $end_date );
					$earnings   = $donation_stats->get_earnings( 0, $start_date, $end_date );

					$sales_totals    += $sales;
					$earnings_totals += $earnings;

					$sales_data[]    = array( $start_date * 1000, $sales );
					$earnings_data[] = array( $start_date * 1000, $earnings );

				}

				$i ++;

			endwhile;

			$y ++;
		endwhile;

	}

	$data = array(
		__( 'Income', 'walkthecounty' )    => $earnings_data,
		__( 'Donations', 'walkthecounty' ) => $sales_data,
	);

	// start our own output buffer.
	ob_start();
	?>

	<div id="walkthecounty-dashboard-widgets-wrap">
		<div class="metabox-holder" style="padding-top: 0;">
			<div class="postbox">
				<div class="inside">
					<?php walkthecounty_reports_graph_controls(); ?>
					<?php
					$graph = new WalkTheCounty_Graph( $data, array( 'dataType' => array( 'amount', 'count' ) ) );
					$graph->set( 'x_mode', 'time' );
					$graph->set( 'multiple_y_axes', true );
					$graph->display();

					if ( 'this_month' === $dates['range'] ) {
						$estimated = walkthecounty_estimated_monthly_stats();
					}
					?>
				</div>
			</div>
			<table class="widefat reports-table alignleft" style="max-width:450px">
				<tbody>
				<tr>
					<th scope="row"><strong><?php _e( 'Total income for period:', 'walkthecounty' ); ?></strong></th>
					<td><?php echo walkthecounty_currency_filter( walkthecounty_format_amount( $earnings_totals, array( 'sanitize' => false ) ) ); ?></td>
				</tr>
				<tr class="alternate">
					<th scope="row"><strong><?php _e( 'Total donations for period:', 'walkthecounty' ); ?><strong></th>
					<td><?php echo $sales_totals; ?></td>
				</tr>
				<?php if ( 'this_month' === $dates['range'] ) : ?>
					<tr>
						<th scope="row"><strong><?php _e( 'Estimated monthly income:', 'walkthecounty' ); ?></strong></th>
						<td><?php echo walkthecounty_currency_filter( walkthecounty_format_amount( $estimated['earnings'], array( 'sanitize' => false ) ) ); ?></td>
					</tr>
					<tr class="alternate">
						<th scope="row"><strong><?php _e( 'Estimated monthly donations:', 'walkthecounty' ); ?></strong></th>
						<td><?php echo floor( $estimated['sales'] ); ?></td>
					</tr>
				<?php endif; ?>
			</table>

			<?php
			/**
			 * Fires on report graphs widget.
			 *
			 * Allows you to add additional stats to the widget.
			 *
			 * @since 1.0
			 */
			do_action( 'walkthecounty_reports_graph_additional_stats' );
			?>

		</div>
	</div>
	<?php
	// get output buffer contents and end our own buffer.
	$output = ob_get_contents();
	ob_end_clean();

	echo $output;
}

/**
 * Show report graphs of a specific donation form.
 *
 * @since 1.0
 *
 * @param int $form_id
 *
 * @return void
 */
function walkthecounty_reports_graph_of_form( $form_id = 0 ) {
	// Retrieve the queried dates.
	$dates = walkthecounty_get_report_dates();

	// Determine graph options.
	switch ( $dates['range'] ) :
		case 'today':
		case 'yesterday':
			$day_by_day = true;
			break;
		case 'last_year':
			$day_by_day = false;
			break;
		case 'this_year':
			$day_by_day = false;
			break;
		case 'last_quarter':
			$day_by_day = false;
			break;
		case 'this_quarter':
			$day_by_day = false;
			break;
		case 'other':
			if ( $dates['m_end'] - $dates['m_start'] >= 2 || $dates['year_end'] > $dates['year'] ) {
				$day_by_day = false;
			} else {
				$day_by_day = true;
			}
			break;
		default:
			$day_by_day = true;
			break;
	endswitch;

	$earnings_totals = (float) 0.00; // Total earnings for time period shown.
	$sales_totals    = 0;            // Total sales for time period shown.

	$earnings_data = array();
	$sales_data    = array();
	$stats         = new WalkTheCounty_Payment_Stats();

	if ( $dates['range'] == 'today' || $dates['range'] == 'yesterday' ) {

		// Hour by hour
		$month  = $dates['m_start'];
		$hour   = 0;
		$minute = 0;
		$second = 0;
		while ( $hour <= 23 ) :

			if ( $hour == 23 ) {
				$minute = $second = 59;
			}

			$date     = mktime( $hour, $minute, $second, $month, $dates['day'], $dates['year'] );
			$date_end = mktime( $hour + 1, $minute, $second, $month, $dates['day'], $dates['year'] );

			$sales         = $stats->get_sales( $form_id, $date, $date_end );
			$sales_totals += $sales;

			$earnings         = $stats->get_earnings( $form_id, $date, $date_end );
			$earnings_totals += $earnings;

			$sales_data[]    = array( $date * 1000, $sales );
			$earnings_data[] = array( $date * 1000, $earnings );

			$hour ++;
		endwhile;

	} elseif ( $dates['range'] == 'this_week' || $dates['range'] == 'last_week' ) {

		// Day by day.
		$day     = $dates['day'];
		$day_end = $dates['day_end'];
		$month   = $dates['m_start'];
		while ( $day <= $day_end ) :

			$date          = mktime( 0, 0, 0, $month, $day, $dates['year'] );
			$date_end      = mktime( 0, 0, 0, $month, $day + 1, $dates['year'] );
			$sales         = $stats->get_sales( $form_id, $date, $date_end );
			$sales_totals += $sales;

			$earnings         = $stats->get_earnings( $form_id, $date, $date_end );
			$earnings_totals += $earnings;

			$sales_data[]    = array( $date * 1000, $sales );
			$earnings_data[] = array( $date * 1000, $earnings );

			$day ++;
		endwhile;

	} else {

		$y = $dates['year'];

		while ( $y <= $dates['year_end'] ) :

			$last_year = false;

			if ( $dates['year'] == $dates['year_end'] ) {
				$month_start = $dates['m_start'];
				$month_end   = $dates['m_end'];
				$last_year   = true;
			} elseif ( $y == $dates['year'] ) {
				$month_start = $dates['m_start'];
				$month_end   = 12;
			} else {
				$month_start = 1;
				$month_end   = 12;
			}

			$i = $month_start;
			while ( $i <= $month_end ) :

				if ( $day_by_day ) {

					if ( $i == $month_end && $last_year ) {

						$num_of_days = $dates['day_end'];

					} else {

						$num_of_days = cal_days_in_month( CAL_GREGORIAN, $i, $y );

					}

					$d = $dates['day'];
					while ( $d <= $num_of_days ) :

						$date     = mktime( 0, 0, 0, $i, $d, $y );
						$end_date = mktime( 23, 59, 59, $i, $d, $y );

						$sales         = $stats->get_sales( $form_id, $date, $end_date );
						$sales_totals += $sales;

						$earnings         = $stats->get_earnings( $form_id, $date, $end_date );
						$earnings_totals += $earnings;

						$sales_data[]    = array( $date * 1000, $sales );
						$earnings_data[] = array( $date * 1000, $earnings );
						$d ++;

					endwhile;

				} else {

					$num_of_days = cal_days_in_month( CAL_GREGORIAN, $i, $y );

					$date     = mktime( 0, 0, 0, $i, 1, $y );
					$end_date = mktime( 23, 59, 59, $i, $num_of_days, $y );

					$sales         = $stats->get_sales( $form_id, $date, $end_date );
					$sales_totals += $sales;

					$earnings         = $stats->get_earnings( $form_id, $date, $end_date );
					$earnings_totals += $earnings;

					$sales_data[]    = array( $date * 1000, $sales );
					$earnings_data[] = array( $date * 1000, $earnings );

				}

				$i ++;

			endwhile;

			$y ++;
		endwhile;

	}

	$data = array(
		__( 'Income', 'walkthecounty' )    => $earnings_data,
		__( 'Donations', 'walkthecounty' ) => $sales_data,
	);

	?>
	<h3><span>
	<?php
			printf(
				/* translators: %s: form title */
				esc_html__( 'Income Report for %s', 'walkthecounty' ),
				get_the_title( $form_id )
			);
			?>
			</span></h3>
	<div id="walkthecounty-dashboard-widgets-wrap">
		<div class="metabox-holder" style="padding-top: 0;">
			<div class="postbox">
				<div class="inside">
					<?php walkthecounty_reports_graph_controls(); ?>
					<?php
					$graph = new WalkTheCounty_Graph( $data, array( 'dataType' => array( 'amount', 'count' ) ) );
					$graph->set( 'x_mode', 'time' );
					$graph->set( 'multiple_y_axes', true );
					$graph->display();
					?>
				</div>
			</div>
			<!--/.postbox -->
			<table class="widefat reports-table alignleft" style="max-width:450px">
				<tbody>
				<tr>
					<th scope="row"><strong><?php _e( 'Total income for period:', 'walkthecounty' ); ?></strong></th>
					<td><?php echo walkthecounty_currency_filter( walkthecounty_format_amount( $earnings_totals, array( 'sanitize' => false ) ) ); ?></td>
				</tr>
				<tr class="alternate">
					<th scope="row"><strong><?php _e( 'Total donations for period:', 'walkthecounty' ); ?></strong></th>
					<td><?php echo $sales_totals; ?></td>
				</tr>
				<tr>
					<th scope="row"><strong><?php _e( 'Average monthly income:', 'walkthecounty' ); ?></strong></th>
					<td><?php echo walkthecounty_currency_filter( walkthecounty_format_amount( walkthecounty_get_average_monthly_form_earnings( $form_id ), array( 'sanitize' => false ) ) ); ?></td>
				</tr>
				<tr class="alternate">
					<th scope="row"><strong><?php _e( 'Average monthly donations:', 'walkthecounty' ); ?></strong></th>
					<td><?php echo number_format( walkthecounty_get_average_monthly_form_sales( $form_id ), 0 ); ?></td>
				</tr>
				</tbody>
			</table>

			<?php
			/**
			 * Fires on report graphs widget.
			 *
			 * Allows you to add additional stats to the widget.
			 *
			 * @since 1.0
			 */
			do_action( 'walkthecounty_reports_graph_additional_stats' );
			?>

		</div>
	</div>
	<?php
	echo ob_get_clean();
}

/**
 * Show report graph date filters
 *
 * @since 1.0.0
 * @since 1.8.0 The hidden `view` field is replaced with `tab` field.
 *
 * @return void
 */
function walkthecounty_reports_graph_controls() {
	$date_options = apply_filters(
		'walkthecounty_report_date_options', array(
			'today'        => __( 'Today', 'walkthecounty' ),
			'yesterday'    => __( 'Yesterday', 'walkthecounty' ),
			'this_week'    => __( 'This Week', 'walkthecounty' ),
			'last_week'    => __( 'Last Week', 'walkthecounty' ),
			'this_month'   => __( 'This Month', 'walkthecounty' ),
			'last_month'   => __( 'Last Month', 'walkthecounty' ),
			'this_quarter' => __( 'This Quarter', 'walkthecounty' ),
			'last_quarter' => __( 'Last Quarter', 'walkthecounty' ),
			'this_year'    => __( 'This Year', 'walkthecounty' ),
			'last_year'    => __( 'Last Year', 'walkthecounty' ),
			'other'        => __( 'Custom', 'walkthecounty' ),
		)
	);

	$dates   = walkthecounty_get_report_dates();
	$display = $dates['range'] == 'other' ? '' : 'display: none;';
	$tab     = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'earnings';

	if ( empty( $dates['day_end'] ) ) {
		$dates['day_end'] = cal_days_in_month( CAL_GREGORIAN, date( 'n' ), date( 'Y' ) );
	}

	/**
	 * Fires before displaying report graph date filters.
	 *
	 * @since 1.0
	 */
	do_action( 'walkthecounty_report_graph_controls_before' );
	?>
	<form id="walkthecounty-graphs-filter" method="get">
		<div class="tablenav top">
			<div class="actions">

				<input type="hidden" name="post_type" value="walkthecounty_forms" />
				<input type="hidden" name="page" value="walkthecounty-reports" />
				<input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>" />

				<?php if ( isset( $_GET['form-id'] ) ) : ?>
					<input type="hidden" name="form-id" value="<?php echo absint( $_GET['form-id'] ); ?>" />
				<?php endif; ?>

				<div id="walkthecounty-graphs-date-options-wrap">
					<select id="walkthecounty-graphs-date-options" name="range">
						<?php foreach ( $date_options as $key => $option ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $key, $dates['range'] ); ?>><?php echo esc_html( $option ); ?></option>
						<?php endforeach; ?>
					</select>

					<div id="walkthecounty-date-range-options" style="<?php echo esc_attr( $display ); ?>">
						<span class="screen-reader-text"><?php _e( 'From', 'walkthecounty' ); ?>&nbsp;</span>
						<select id="walkthecounty-graphs-month-start" name="m_start" aria-label="Start Month">
							<?php for ( $i = 1; $i <= 12; $i ++ ) : ?>
								<option value="<?php echo absint( $i ); ?>" <?php echo esc_attr( selected( $i, $dates['m_start'] ) ); ?>><?php echo esc_html( walkthecounty_month_num_to_name( $i ) ); ?></option>
							<?php endfor; ?>
						</select>
						<select id="walkthecounty-graphs-day-start" name="day" aria-label="Start Day">
							<?php for ( $i = 1; $i <= 31; $i ++ ) : ?>
								<option value="<?php echo absint( $i ); ?>" <?php echo esc_attr( selected( $i, $dates['day'] ) ); ?>><?php echo esc_html( $i ); ?></option>
							<?php endfor; ?>
						</select>
						<select id="walkthecounty-graphs-year-start" name="year" aria-label="Start Year">
							<?php for ( $i = 2007; $i <= date( 'Y' ); $i ++ ) : ?>
								<option value="<?php echo absint( $i ); ?>" <?php echo esc_attr( selected( $i, $dates['year'] ) ); ?>><?php echo esc_html( $i ); ?></option>
							<?php endfor; ?>
						</select>
						<span class="screen-reader-text"><?php esc_html_e( 'To', 'walkthecounty' ); ?>&nbsp;</span>
						<span>&ndash;</span>
						<select id="walkthecounty-graphs-month-end" name="m_end" aria-label="End Month">
							<?php for ( $i = 1; $i <= 12; $i ++ ) : ?>
								<option value="<?php echo absint( $i ); ?>" <?php echo esc_attr( selected( $i, $dates['m_end'] ) ); ?>><?php echo esc_html( walkthecounty_month_num_to_name( $i ) ); ?></option>
							<?php endfor; ?>
						</select>
						<select id="walkthecounty-graphs-day-end" name="day_end" aria-label="End Day">
							<?php for ( $i = 1; $i <= 31; $i ++ ) : ?>
								<option value="<?php echo absint( $i ); ?>" <?php echo esc_attr( selected( $i, $dates['day_end'] ) ); ?>><?php echo esc_html( $i ); ?></option>
							<?php endfor; ?>
						</select>
						<select id="walkthecounty-graphs-year-end" name="year_end" aria-label="End Year">
							<?php for ( $i = 2007; $i <= date( 'Y' ); $i ++ ) : ?>
								<option value="<?php echo absint( $i ); ?>" <?php echo esc_attr( selected( $i, $dates['year_end'] ) ); ?>><?php echo esc_html( $i ); ?></option>
							<?php endfor; ?>
						</select>
					</div>

					<input type="submit" class="button-secondary" value="<?php _e( 'Filter', 'walkthecounty' ); ?>" />
				</div>

				<input type="hidden" name="walkthecounty_action" value="filter_reports" />
			</div>
		</div>
	</form>
	<?php
	/**
	 * Fires after displaying report graph date filters.
	 *
	 * @since 1.0
	 */
	do_action( 'walkthecounty_report_graph_controls_after' );
}

/**
 * Sets up the dates used to filter graph data
 *
 * Date sent via $_GET is read first and then modified (if needed) to match the
 * selected date-range (if any)
 *
 * @since 1.0
 *
 * @return array
 */
function walkthecounty_get_report_dates() {
	$dates = array();

	$current_time = current_time( 'timestamp' );

	$dates['range']    = isset( $_GET['range'] ) ? $_GET['range'] : 'this_month';
	$dates['year']     = isset( $_GET['year'] ) ? $_GET['year'] : date( 'Y' );
	$dates['year_end'] = isset( $_GET['year_end'] ) ? $_GET['year_end'] : date( 'Y' );
	$dates['m_start']  = isset( $_GET['m_start'] ) ? $_GET['m_start'] : 1;
	$dates['m_end']    = isset( $_GET['m_end'] ) ? $_GET['m_end'] : 12;
	$dates['day']      = isset( $_GET['day'] ) ? $_GET['day'] : 1;
	$dates['day_end']  = isset( $_GET['day_end'] ) ? $_GET['day_end'] : cal_days_in_month( CAL_GREGORIAN, $dates['m_end'], $dates['year'] );

	// Modify dates based on predefined ranges.
	switch ( $dates['range'] ) :

		case 'this_month':
			$dates['m_start']  = date( 'n', $current_time );
			$dates['m_end']    = date( 'n', $current_time );
			$dates['day']      = 1;
			$dates['day_end']  = cal_days_in_month( CAL_GREGORIAN, $dates['m_end'], $dates['year'] );
			$dates['year']     = date( 'Y' );
			$dates['year_end'] = date( 'Y' );
			break;

		case 'last_month':
			if ( date( 'n' ) == 1 ) {
				$dates['m_start']  = 12;
				$dates['m_end']    = 12;
				$dates['year']     = date( 'Y', $current_time ) - 1;
				$dates['year_end'] = date( 'Y', $current_time ) - 1;
			} else {
				$dates['m_start']  = date( 'n' ) - 1;
				$dates['m_end']    = date( 'n' ) - 1;
				$dates['year_end'] = $dates['year'];
			}
			$dates['day_end'] = cal_days_in_month( CAL_GREGORIAN, $dates['m_end'], $dates['year'] );
			break;

		case 'today':
			$dates['day']      = date( 'd', $current_time );
			$dates['day_end']  = date( 'd', $current_time );
			$dates['m_start']  = date( 'n', $current_time );
			$dates['m_end']    = date( 'n', $current_time );
			$dates['year']     = date( 'Y', $current_time );
			$dates['year_end'] = date( 'Y', $current_time );
			break;

		case 'yesterday':
			$year  = date( 'Y', $current_time );
			$month = date( 'n', $current_time );
			$day   = date( 'd', $current_time );

			if ( $month == 1 && $day == 1 ) {

				$year -= 1;
				$month = 12;
				$day   = cal_days_in_month( CAL_GREGORIAN, $month, $year );

			} elseif ( $month > 1 && $day == 1 ) {

				$month -= 1;
				$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );

			} else {

				$day -= 1;

			}

			$dates['day']      = $day;
			$dates['m_start']  = $month;
			$dates['m_end']    = $month;
			$dates['year']     = $year;
			$dates['year_end'] = $year;
			break;

		case 'this_week':
			$dates['day']     = date( 'd', $current_time - ( date( 'w', $current_time ) - 1 ) * 60 * 60 * 24 ) - 1;
			$dates['day']    += get_option( 'start_of_week' );
			$dates['day_end'] = $dates['day'] + 6;
			$dates['m_start'] = date( 'n', $current_time );
			$dates['m_end']   = date( 'n', $current_time );
			$dates['year']    = date( 'Y', $current_time );
			break;

		case 'last_week':
			$dates['day']     = date( 'd', $current_time - ( date( 'w' ) - 1 ) * 60 * 60 * 24 ) - 8;
			$dates['day']    += get_option( 'start_of_week' );
			$dates['day_end'] = $dates['day'] + 6;
			$dates['year']    = date( 'Y' );

			if ( date( 'j', $current_time ) <= 7 ) {
				$dates['m_start'] = date( 'n', $current_time ) - 1;
				$dates['m_end']   = date( 'n', $current_time ) - 1;
				if ( $dates['m_start'] <= 1 ) {
					$dates['year']     = date( 'Y', $current_time ) - 1;
					$dates['year_end'] = date( 'Y', $current_time ) - 1;
				}
			} else {
				$dates['m_start'] = date( 'n', $current_time );
				$dates['m_end']   = date( 'n', $current_time );
			}
			break;

		case 'this_quarter':
			$month_now     = date( 'n', $current_time );
			$dates['year'] = date( 'Y', $current_time );

			if ( $month_now <= 3 ) {

				$dates['m_start'] = 1;
				$dates['m_end']   = 4;

			} elseif ( $month_now <= 6 ) {

				$dates['m_start'] = 4;
				$dates['m_end']   = 7;

			} elseif ( $month_now <= 9 ) {

				$dates['m_start'] = 7;
				$dates['m_end']   = 10;

			} else {

				$dates['m_start']  = 10;
				$dates['m_end']    = 1;
				$dates['year_end'] = date( 'Y', $current_time ) + 1;

			}
			break;

		case 'last_quarter':
			$month_now         = date( 'n', $current_time );
			$dates['year']     = date( 'Y', $current_time );
			$dates['year_end'] = date( 'Y', $current_time );

			if ( $month_now <= 3 ) {

				$dates['m_start'] = 10;
				$dates['m_end']   = 1;
				$dates['year']    = date( 'Y', $current_time ) - 1; // Previous year.

			} elseif ( $month_now <= 6 ) {

				$dates['m_start'] = 1;
				$dates['m_end']   = 4;

			} elseif ( $month_now <= 9 ) {

				$dates['m_start'] = 4;
				$dates['m_end']   = 7;

			} else {

				$dates['m_start'] = 7;
				$dates['m_end']   = 10;

			}
			break;

		case 'this_year':
			$dates['m_start']  = 1;
			$dates['m_end']    = 12;
			$dates['year']     = date( 'Y', $current_time );
			$dates['year_end'] = date( 'Y', $current_time );
			break;

		case 'last_year':
			$dates['m_start']  = 1;
			$dates['m_end']    = 12;
			$dates['year']     = date( 'Y', $current_time ) - 1;
			$dates['year_end'] = date( 'Y', $current_time ) - 1;
			break;

	endswitch;

	return apply_filters( 'walkthecounty_report_dates', $dates );
}

/**
 * Grabs all of the selected date info and then redirects appropriately
 *
 * @since 1.0.0
 * @since 1.8.0 The `tab` query arg is added to the redirect.
 *
 * @param $data
 */
function walkthecounty_parse_report_dates( $data ) {
	$dates = walkthecounty_get_report_dates();

	$view = walkthecounty_get_reporting_view();
	$tab  = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'earnings';
	$id   = isset( $_GET['form-id'] ) ? $_GET['form-id'] : null;

	wp_redirect( add_query_arg( $dates, admin_url( 'edit.php?post_type=walkthecounty_forms&page=walkthecounty-reports&tab=' . esc_attr( $tab ) . '&view=' . esc_attr( $view ) . '&form-id=' . absint( $id ) ) ) );
	walkthecounty_die();
}

add_action( 'walkthecounty_filter_reports', 'walkthecounty_parse_report_dates' );


/**
 * WalkTheCounty Reports Refresh Button
 *
 * Outputs a "Refresh Reports" button for graphs
 *
 * @since      1.3
 */
function walkthecounty_reports_refresh_button() {

	$url = wp_nonce_url(
		add_query_arg(
			array(
				'walkthecounty_action'     => 'refresh_reports_transients',
				'walkthecounty-messages[]' => 'refreshed-reports',
			)
		), 'walkthecounty-refresh-reports'
	);

	echo WalkTheCounty()->tooltips->render_link(
		array(
			'label'       => esc_attr__( 'Clicking this will clear the reports cache.', 'walkthecounty' ),
			'tag_content' => '<span class="walkthecounty-admin-button-icon walkthecounty-admin-button-icon-update"></span>' . esc_html__( 'Refresh Report Data', 'walkthecounty' ),
			'link'        => $url,
			'position'    => 'left',
			'attributes'  => array(
				'class' => 'button alignright walkthecounty-admin-button',
			),
		)
	);
}

add_action( 'walkthecounty_reports_graph_additional_stats', 'walkthecounty_reports_refresh_button' );

/**
 * Trigger the refresh of reports transients
 *
 * @param array $data Parameters sent from Settings page.
 *
 * @since 1.3
 *
 * @return void
 */
function walkthecounty_run_refresh_reports_transients( $data ) {

	if ( ! wp_verify_nonce( $data['_wpnonce'], 'walkthecounty-refresh-reports' ) ) {
		return;
	}

	// Monthly stats.
	WalkTheCounty_Cache::delete( WalkTheCounty_Cache::get_key( 'walkthecounty_estimated_monthly_stats' ) );

	// Total earning.
	delete_option( 'walkthecounty_earnings_total' );

	// @todo: Refresh only range related stat cache
	walkthecounty_delete_donation_stats();
}

add_action( 'walkthecounty_refresh_reports_transients', 'walkthecounty_run_refresh_reports_transients' );
