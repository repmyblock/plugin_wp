<?php
/**
 * Dashboard Widgets
 *
 * @package     WalkTheCounty
 * @subpackage  Admin/Dashboard
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the dashboard widgets
 *
 * @since  1.0
 * @return void
 */
function walkthecounty_register_dashboard_widgets() {
	if ( current_user_can( apply_filters( 'walkthecounty_dashboard_stats_cap', 'view_walkthecounty_reports' ) ) ) {
		wp_add_dashboard_widget( 'walkthecounty_dashboard_sales', __( 'WalkTheCounty: Donation Statistics', 'walkthecounty' ), 'walkthecounty_render_dashboard_stats_widget' );
	}
}

add_action( 'wp_dashboard_setup', 'walkthecounty_register_dashboard_widgets', 10 );


/**
 * Sales Summary Dashboard Widget render callback
 * Note: only for internal use
 *
 * Builds and renders the ajaxify statistics dashboard widget.
 * This widget displays the current month's donations.
 *
 * @since  2.4.0
 * @return void
 */
function walkthecounty_render_dashboard_stats_widget() {
	if ( ! current_user_can( apply_filters( 'walkthecounty_dashboard_stats_cap', 'view_walkthecounty_reports' ) ) ) {
		return;
	}

	?>
	<div id="walkthecounty-dashboard-sales-widget">
		<span class="spinner is-active" style="float: none;margin: auto 50%;padding-bottom: 15px;"></span>

		<script>
			jQuery(document).ready(function () {
				jQuery.ajax({
					url: ajaxurl,
					data: {
						action: 'walkthecounty_render_dashboard_stats_widget'
					},
					success: function (response) {
						jQuery('#walkthecounty-dashboard-sales-widget').html(response);
					}
				});
			})
		</script>
	</div>
	<?php
}

/**
 * Ajax handler for dashboard statistic widget render
 * Note: only for internal use
 *
 * @since 2.4.0
 */
function walkthecounty_ajax_render_dashboard_stats_widget(){
	ob_start();
	walkthecounty_dashboard_stats_widget();

	wp_send_json( ob_get_clean() );

}
add_action( 'wp_ajax_walkthecounty_render_dashboard_stats_widget', 'walkthecounty_ajax_render_dashboard_stats_widget' );

/**
 * Sales Summary Dashboard Widget
 *
 * Builds and renders the statistics dashboard widget. This widget displays the current month's donations.
 *
 * @since       1.0
 * @return void
 */
function walkthecounty_dashboard_stats_widget() {

	if ( ! current_user_can( apply_filters( 'walkthecounty_dashboard_stats_cap', 'view_walkthecounty_reports' ) ) ) {
		return;
	}
	$stats = new WalkTheCounty_Payment_Stats(); ?>

	<div class="walkthecounty-dashboard-widget">

		<div class="walkthecounty-dashboard-today walkthecounty-clearfix">
			<h3 class="walkthecounty-dashboard-date-today"><?php echo date_i18n( _x( 'F j, Y', 'dashboard widget', 'walkthecounty' ) ); ?></h3>

			<p class="walkthecounty-dashboard-happy-day"><?php
				printf(
				/* translators: %s: day of the week */
					__( 'Happy %s!', 'walkthecounty' ),
					date_i18n( 'l', current_time( 'timestamp' ) )
				);
			?></p>

			<p class="walkthecounty-dashboard-today-earnings"><?php
				$earnings_today = $stats->get_earnings( 0, 'today', false );
				echo walkthecounty_currency_filter( walkthecounty_format_amount( $earnings_today, array( 'sanitize' => false ) ) );
			?></p>

			<p class="walkthecounty-donations-today"><?php
				$donations_today = $stats->get_sales( 0, 'today', false );
				printf(
					/* translators: %s: daily donation count */
					__( '%s donations today', 'walkthecounty' ),
					walkthecounty_format_amount( $donations_today, array( 'decimal' => false, 'sanitize' => false ) )
				);
			?></p>

		</div>


		<table class="walkthecounty-table-stats">
			<thead style="display: none;">
			<tr>
				<th><?php _e( 'This Week', 'walkthecounty' ); ?></th>
				<th><?php _e( 'This Month', 'walkthecounty' ); ?></th>
				<th><?php _e( 'Past 30 Days', 'walkthecounty' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<tr id="walkthecounty-table-stats-tr-1">
				<td>
					<p class="walkthecounty-dashboard-stat-total"><?php echo walkthecounty_currency_filter( walkthecounty_format_amount( $stats->get_earnings( 0, 'this_week' ), array( 'sanitize' => false ) ) ); ?></p>

					<p class="walkthecounty-dashboard-stat-total-label"><?php _e( 'This Week', 'walkthecounty' ); ?></p>
				</td>
				<td>
					<p class="walkthecounty-dashboard-stat-total"><?php echo walkthecounty_currency_filter( walkthecounty_format_amount( $stats->get_earnings( 0, 'this_month' ), array( 'sanitize' => false ) ) ); ?></p>

					<p class="walkthecounty-dashboard-stat-total-label"><?php _e( 'This Month', 'walkthecounty' ); ?></p>
				</td>
			</tr>
			<tr id="walkthecounty-table-stats-tr-2">
				<td>
					<p class="walkthecounty-dashboard-stat-total"><?php echo walkthecounty_currency_filter( walkthecounty_format_amount( $stats->get_earnings( 0, 'last_month' ), array( 'sanitize' => false ) ) ) ?></p>

					<p class="walkthecounty-dashboard-stat-total-label"><?php _e( 'Last Month', 'walkthecounty' ); ?></p>
				</td>
				<td>
					<p class="walkthecounty-dashboard-stat-total"><?php echo walkthecounty_currency_filter( walkthecounty_format_amount( $stats->get_earnings( 0, 'this_quarter' ), array( 'sanitize' => false ) ) ) ?></p>

					<p class="walkthecounty-dashboard-stat-total-label"><?php _e( 'This Quarter', 'walkthecounty' ); ?></p>
				</td>
			</tr>
			</tbody>
		</table>

	</div>

	<?php
}

/**
 * Add donation forms count to dashboard "At a glance" widget
 *
 * @since  1.0
 *
 * @param $items
 *
 * @return array
 */
function walkthecounty_dashboard_at_a_glance_widget( $items ) {

	$num_posts = wp_count_posts( 'walkthecounty_forms' );

	if ( $num_posts && $num_posts->publish ) {

		$text = sprintf(
			/* translators: %s: number of posts published */
			_n( '%s WalkTheCountyWP Form', '%s WalkTheCountyWP Forms', $num_posts->publish, 'walkthecounty' ),
			$num_posts->publish
		);

		$text = sprintf( $text, number_format_i18n( $num_posts->publish ) );

		if ( current_user_can( 'edit_walkthecounty_forms', get_current_user_id() ) ) {
			$text = sprintf(
				'<a class="walkthecounty-forms-count" href="%1$s">%2$s</a>',
				admin_url( 'edit.php?post_type=walkthecounty_forms' ),
				$text
			);
		} else {
			$text = sprintf(
				'<span class="walkthecounty-forms-count">%1$s</span>',
				$text
			);
		}

		$items[] = $text;
	}

	return $items;
}

add_filter( 'dashboard_glance_items', 'walkthecounty_dashboard_at_a_glance_widget', 1, 1 );
