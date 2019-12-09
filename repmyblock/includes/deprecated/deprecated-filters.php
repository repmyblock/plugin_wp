<?php
/**
 * Handle renamed filters
 *
 * @package WalkTheCounty
 */

$walkthecounty_map_deprecated_filters = walkthecounty_deprecated_filters();

foreach ( $walkthecounty_map_deprecated_filters as $new => $old ) {
	add_filter( $new, 'walkthecounty_deprecated_filter_mapping', 10, 4 );
}

/**
 * Deprecated filters.
 *
 * @return array An array of deprecated WalkTheCounty filters.
 */
function walkthecounty_deprecated_filters() {

	$walkthecounty_deprecated_filters = array(
		// New filter hook                                 Old filter hook.
		'walkthecounty_donation_data_before_gateway'                => 'walkthecounty_purchase_data_before_gateway',
		'walkthecounty_donation_form_required_fields'               => 'walkthecounty_purchase_form_required_fields',
		'walkthecounty_donation_stats_by_user'                      => 'walkthecounty_purchase_stats_by_user',
		'walkthecounty_donation_from_name'                          => 'walkthecounty_purchase_from_name',
		'walkthecounty_donation_from_address'                       => 'walkthecounty_purchase_from_address',
		'walkthecounty_get_users_donations_args'                    => 'walkthecounty_get_users_purchases_args',
		'walkthecounty_recount_donors_donation_statuses'            => 'walkthecounty_recount_customer_payment_statuses',
		'walkthecounty_donor_recount_should_process_donation'       => 'walkthecounty_customer_recount_should_process_payment',
		'walkthecounty_reset_items'                                 => 'walkthecounty_reset_store_items',
		'walkthecounty_decrease_donations_on_undo'                  => 'walkthecounty_decrease_sales_on_undo',
		'walkthecounty_decrease_earnings_on_pending'                => 'walkthecounty_decrease_store_earnings_on_pending',
		'walkthecounty_decrease_donor_value_on_pending'             => 'walkthecounty_decrease_customer_value_on_pending',
		'walkthecounty_decrease_donors_donation_count_on_pending'   => 'walkthecounty_decrease_customer_purchase_count_on_pending',
		'walkthecounty_decrease_earnings_on_cancelled'              => 'walkthecounty_decrease_store_earnings_on_cancelled',
		'walkthecounty_decrease_donor_value_on_cancelled'           => 'walkthecounty_decrease_customer_value_on_cancelled',
		'walkthecounty_decrease_donors_donation_count_on_cancelled' => 'walkthecounty_decrease_customer_purchase_count_on_cancelled',
		'walkthecounty_decrease_earnings_on_revoked'                => 'walkthecounty_decrease_store_earnings_on_revoked',
		'walkthecounty_decrease_donor_value_on_revoked'             => 'walkthecounty_decrease_customer_value_on_revoked',
		'walkthecounty_decrease_donors_donation_count_on_revoked'   => 'walkthecounty_decrease_customer_purchase_count_on_revoked',
		'walkthecounty_edit_donors_role'                            => 'walkthecounty_edit_customers_role',
		'walkthecounty_edit_donor_info'                             => 'walkthecounty_edit_customer_info',
		'walkthecounty_edit_donor_address'                          => 'walkthecounty_edit_customer_address',
		'walkthecounty_donor_tabs'                                  => 'walkthecounty_customer_tabs',
		'walkthecounty_donor_views'                                 => 'walkthecounty_customer_views',
		'walkthecounty_view_donors_role'                            => 'walkthecounty_view_customers_role',
		'walkthecounty_report_donor_columns'                        => 'walkthecounty_report_customer_columns',
		'walkthecounty_report_sortable_donor_columns'               => 'walkthecounty_report_sortable_customer_columns',
		'walkthecounty_undo_donation_statuses'                      => 'walkthecounty_undo_purchase_statuses',
		'walkthecounty_donor_recount_should_increase_value'         => 'walkthecounty_customer_recount_sholud_increase_value',
		'walkthecounty_donor_recount_should_increase_count'         => 'walkthecounty_customer_recount_should_increase_count',
		'walkthecounty_donation_amount'                             => 'walkthecounty_payment_amount',
		'walkthecounty_get_donation_form_title'                     => 'walkthecounty_get_payment_form_title',
		'walkthecounty_decrease_earnings_on_refunded'               => 'walkthecounty_decrease_store_earnings_on_refund',
		'walkthecounty_decrease_donor_value_on_refunded'            => 'walkthecounty_decrease_customer_value_on_refund',
		'walkthecounty_decrease_donors_donation_count_on_refunded'  => 'walkthecounty_decrease_customer_purchase_count_on_refund',
		'walkthecounty_should_process_refunded'                     => 'walkthecounty_should_process_refund',
		'walkthecounty_settings_export_excludes'                    => 'settings_export_excludes',
		'walkthecounty_ajax_form_search_response'                   => 'walkthecounty_ajax_form_search_responce'
	);

	return $walkthecounty_deprecated_filters;
}

/**
 * Deprecated filter mapping.
 *
 * @param mixed  $data
 * @param string $arg_1 Passed filter argument 1.
 * @param string $arg_2 Passed filter argument 2.
 * @param string $arg_3 Passed filter argument 3.
 *
 * @return mixed
 */
function walkthecounty_deprecated_filter_mapping( $data, $arg_1 = '', $arg_2 = '', $arg_3 = '' ) {
	$walkthecounty_map_deprecated_filters = walkthecounty_deprecated_filters();
	$filter                      = current_filter();

	if ( isset( $walkthecounty_map_deprecated_filters[ $filter ] ) ) {
		if ( has_filter( $walkthecounty_map_deprecated_filters[ $filter ] ) ) {
			$data = apply_filters( $walkthecounty_map_deprecated_filters[ $filter ], $data, $arg_1, $arg_2, $arg_3 );

			if ( ! defined( 'DOING_AJAX' ) ) {
				_walkthecounty_deprecated_function( sprintf( /* translators: %s: filter name */
					__( 'The %s filter' ), $walkthecounty_map_deprecated_filters[ $filter ] ), '1.7', $filter );
			}
		}
	}

	return $data;
}
