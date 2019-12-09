<?php
/**
 * Payment Functions
 *
 * @package     WalkTheCounty
 * @subpackage  Payments
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Payments
 *
 * Retrieve payments from the database.
 *
 * Since 1.0, this function takes an array of arguments, instead of individual
 * parameters. All of the original parameters remain, but can be passed in any
 * order via the array.
 *
 * @since 1.0
 *
 * @param array $args     {
 *                        Optional. Array of arguments passed to payments query.
 *
 * @type int    $offset   The number of payments to offset before retrieval.
 *                            Default is 0.
 * @type int    $number   The number of payments to query for. Use -1 to request all
 *                            payments. Default is 20.
 * @type string $mode     Default is 'live'.
 * @type string $order    Designates ascending or descending order of payments.
 *                            Accepts 'ASC', 'DESC'. Default is 'DESC'.
 * @type string $orderby  Sort retrieved payments by parameter. Default is 'ID'.
 * @type string $status   The status of the payments. Default is 'any'.
 * @type string $user     User. Default is null.
 * @type string $meta_key Custom field key. Default is null.
 *
 * }
 *
 * @return array $payments Payments retrieved from the database
 */
function walkthecounty_get_payments( $args = array() ) {

	// Fallback to post objects to ensure backwards compatibility.
	if ( ! isset( $args['output'] ) ) {
		$args['output'] = 'posts';
	}

	$args     = apply_filters( 'walkthecounty_get_payments_args', $args );
	$payments = new WalkTheCounty_Payments_Query( $args );

	return $payments->get_payments();
}

/**
 * Retrieve payment by a walkthecountyn field
 *
 * @since  1.0
 *
 * @param  string $field The field to retrieve the payment with.
 * @param  mixed  $value The value for $field.
 *
 * @return mixed
 */
function walkthecounty_get_payment_by( $field = '', $value = '' ) {

	if ( empty( $field ) || empty( $value ) ) {
		return false;
	}

	switch ( strtolower( $field ) ) {

		case 'id':
			$payment = new WalkTheCounty_Payment( $value );
			$id      = $payment->ID;

			if ( empty( $id ) ) {
				return false;
			}

			break;

		case 'key':
			$payment = walkthecounty_get_payments(
				array(
					'meta_key'       => '_walkthecounty_payment_purchase_key',
					'meta_value'     => $value,
					'posts_per_page' => 1,
					'fields'         => 'ids',
				)
			);

			if ( $payment ) {
				$payment = new WalkTheCounty_Payment( $payment[0] );
			}

			break;

		case 'payment_number':
			$payment = walkthecounty_get_payments(
				array(
					'meta_key'       => '_walkthecounty_payment_number',
					'meta_value'     => $value,
					'posts_per_page' => 1,
					'fields'         => 'ids',
				)
			);

			if ( $payment ) {
				$payment = new WalkTheCounty_Payment( $payment[0] );
			}

			break;

		default:
			return false;
	}// End switch().

	if ( $payment ) {
		return $payment;
	}

	return false;
}

/**
 * Insert Payment
 *
 * @since  1.0
 *
 * @param  array $payment_data Arguments passed.
 *
 * @return int|bool Payment ID if payment is inserted, false otherwise.
 */
function walkthecounty_insert_payment( $payment_data = array() ) {

	if ( empty( $payment_data ) ) {
		return false;
	}

	/**
	 * Fire the filter on donation data before insert.
	 *
	 * @since 1.8.15
	 *
	 * @param array $payment_data Arguments passed.
	 */
	$payment_data = apply_filters( 'walkthecounty_pre_insert_payment', $payment_data );

	$payment    = new WalkTheCounty_Payment();
	$gateway    = ! empty( $payment_data['gateway'] ) ? $payment_data['gateway'] : '';
	$gateway    = empty( $gateway ) && isset( $_POST['walkthecounty-gateway'] ) ? walkthecounty_clean( $_POST['walkthecounty-gateway'] ) : $gateway; // WPCS: input var ok, sanitization ok, CSRF ok.
	$form_id    = isset( $payment_data['walkthecounty_form_id'] ) ? $payment_data['walkthecounty_form_id'] : 0;
	$price_id   = walkthecounty_get_payment_meta_price_id( $payment_data );
	$form_title = isset( $payment_data['walkthecounty_form_title'] ) ? $payment_data['walkthecounty_form_title'] : get_the_title( $form_id );

	// Set properties.
	$payment->total          = $payment_data['price'];
	$payment->status         = ! empty( $payment_data['status'] ) ? $payment_data['status'] : 'pending';
	$payment->currency       = ! empty( $payment_data['currency'] ) ? $payment_data['currency'] : walkthecounty_get_currency( $payment_data['walkthecounty_form_id'], $payment_data );
	$payment->user_info      = $payment_data['user_info'];
	$payment->gateway        = $gateway;
	$payment->form_title     = $form_title;
	$payment->form_id        = $form_id;
	$payment->price_id       = $price_id;
	$payment->donor_id       = ( ! empty( $payment_data['donor_id'] ) ? $payment_data['donor_id'] : '' );
	$payment->user_id        = $payment_data['user_info']['id'];
	$payment->email          = $payment_data['user_email'];
	$payment->first_name     = $payment_data['user_info']['first_name'];
	$payment->last_name      = $payment_data['user_info']['last_name'];
	$payment->title_prefix   = ! empty( $payment_data['user_info']['title'] ) ? $payment_data['user_info']['title'] : '';
	$payment->email          = $payment_data['user_info']['email'];
	$payment->ip             = walkthecounty_get_ip();
	$payment->key            = $payment_data['purchase_key'];
	$payment->mode           = ( ! empty( $payment_data['mode'] ) ? (string) $payment_data['mode'] : ( walkthecounty_is_test_mode() ? 'test' : 'live' ) );
	$payment->parent_payment = ! empty( $payment_data['parent'] ) ? absint( $payment_data['parent'] ) : '';

	// Add the donation.
	$args = array(
		'price'    => $payment->total,
		'price_id' => $payment->price_id,
	);

	$payment->add_donation( $payment->form_id, $args );

	// Set date if present.
	if ( isset( $payment_data['post_date'] ) ) {
		$payment->date = $payment_data['post_date'];
	}

	// Save payment.
	$payment->save();

	// Setup donor id.
	$payment_data['user_info']['donor_id'] = $payment->donor_id;

	// Set donation id to purchase session.
	$purchase_session = WalkTheCounty()->session->get( 'walkthecounty_purchase' );
	$purchase_session['donation_id'] = $payment->ID;
	WalkTheCounty()->session->set( 'walkthecounty_purchase', $purchase_session );

	/**
	 * Fires while inserting payments.
	 *
	 * @since 1.0
	 *
	 * @param int   $payment_id   The payment ID.
	 * @param array $payment_data Arguments passed.
	 */
	do_action( 'walkthecounty_insert_payment', $payment->ID, $payment_data );

	// Return payment ID upon success.
	if ( ! empty( $payment->ID ) ) {
		return $payment->ID;
	}

	// Return false if no payment was inserted.
	return false;

}

/**
 * Create payment.
 *
 * @param $payment_data
 *
 * @return bool|int
 */
function walkthecounty_create_payment( $payment_data ) {

	$form_id  = intval( $payment_data['post_data']['walkthecounty-form-id'] );
	$price_id = isset( $payment_data['post_data']['walkthecounty-price-id'] ) ? $payment_data['post_data']['walkthecounty-price-id'] : '';

	// Collect payment data.
	$insert_payment_data = array(
		'price'           => $payment_data['price'],
		'walkthecounty_form_title' => $payment_data['post_data']['walkthecounty-form-title'],
		'walkthecounty_form_id'    => $form_id,
		'walkthecounty_price_id'   => $price_id,
		'date'            => $payment_data['date'],
		'user_email'      => $payment_data['user_email'],
		'purchase_key'    => $payment_data['purchase_key'],
		'currency'        => walkthecounty_get_currency( $form_id, $payment_data ),
		'user_info'       => $payment_data['user_info'],
		'status'          => 'pending',
		'gateway'         => 'paypal',
	);

	/**
	 * Filter the payment params.
	 *
	 * @since 1.8
	 *
	 * @param array $insert_payment_data
	 */
	$insert_payment_data = apply_filters( 'walkthecounty_create_payment', $insert_payment_data );

	// Record the pending payment.
	return walkthecounty_insert_payment( $insert_payment_data );
}

/**
 * Updates a payment status.
 *
 * @param  int    $payment_id Payment ID.
 * @param  string $new_status New Payment Status. Default is 'publish'.
 *
 * @since  1.0
 *
 * @return bool
 */
function walkthecounty_update_payment_status( $payment_id, $new_status = 'publish' ) {

	$updated = false;
	$payment = new WalkTheCounty_Payment( $payment_id );

	if ( $payment && $payment->ID > 0 ) {

		$payment->status = $new_status;
		$updated         = $payment->save();

	}

	return $updated;
}


/**
 * Deletes a Donation
 *
 * @since  1.0
 *
 * @param  int  $payment_id   Payment ID (default: 0).
 * @param  bool $update_donor If we should update the donor stats (default:true).
 *
 * @return void
 */
function walkthecounty_delete_donation( $payment_id = 0, $update_donor = true ) {
	$payment = new WalkTheCounty_Payment( $payment_id );

	// Bailout.
	if ( ! $payment->ID ) {
		return;
	}

	$amount = walkthecounty_donation_amount( $payment_id );
	$status = $payment->post_status;
	$donor  = new WalkTheCounty_Donor( $payment->donor_id );

	// Only undo donations that aren't these statuses.
	$dont_undo_statuses = apply_filters(
		'walkthecounty_undo_donation_statuses', array(
			'pending',
			'cancelled',
		)
	);

	if ( ! in_array( $status, $dont_undo_statuses ) ) {
		walkthecounty_undo_donation( $payment_id );
	}

	// Only undo donations that aren't these statuses.
	$status_to_decrease_stats = apply_filters( 'walkthecounty_decrease_donor_statuses', array( 'publish' ) );

	if ( in_array( $status, $status_to_decrease_stats ) ) {

		// Only decrease earnings if they haven't already been decreased (or were never increased for this payment).
		walkthecounty_decrease_total_earnings( $amount );

		// @todo: Refresh only range related stat cache
		walkthecounty_delete_donation_stats();

		if ( $donor->id && $update_donor ) {

			// Decrement the stats for the donor.
			$donor->decrease_donation_count();
			$donor->decrease_value( $amount );

		}
	}

	/**
	 * Fires before deleting payment.
	 *
	 * @param int $payment_id Payment ID.
	 *
	 * @since 1.0
	 */
	do_action( 'walkthecounty_payment_delete', $payment_id );

	if ( $donor->id && $update_donor ) {
		// Remove the payment ID from the donor.
		$donor->remove_payment( $payment_id );
	}

	// Remove the payment.
	wp_delete_post( $payment_id, true );

	WalkTheCounty()->payment_meta->delete_all_meta( $payment_id );

	// Remove related sale log entries.
	WalkTheCounty()->logs->delete_logs( $payment_id );

	/**
	 * Fires after payment deleted.
	 *
	 * @param int $payment_id Payment ID.
	 *
	 * @since 1.0
	 */
	do_action( 'walkthecounty_payment_deleted', $payment_id );
}

/**
 * Undo Donation
 *
 * Undoes a donation, including the decrease of donations and earning stats.
 * Used for when refunding or deleting a donation.
 *
 * @param  int $payment_id Payment ID.
 *
 * @since  1.0
 *
 * @return void
 */
function walkthecounty_undo_donation( $payment_id ) {

	$payment = new WalkTheCounty_Payment( $payment_id );

	$maybe_decrease_earnings = apply_filters( 'walkthecounty_decrease_earnings_on_undo', true, $payment, $payment->form_id );
	if ( true === $maybe_decrease_earnings ) {
		// Decrease earnings.
		walkthecounty_decrease_form_earnings( $payment->form_id, $payment->total, $payment_id );
	}

	$maybe_decrease_donations = apply_filters( 'walkthecounty_decrease_donations_on_undo', true, $payment, $payment->form_id );
	if ( true === $maybe_decrease_donations ) {
		// Decrease donation count.
		walkthecounty_decrease_donation_count( $payment->form_id );
	}

}


/**
 * Count Payments
 *
 * Returns the total number of payments recorded.
 *
 * @param  array $args Arguments passed.
 *
 * @since  1.0
 *
 * @return object $stats Contains the number of payments per payment status.
 */
function walkthecounty_count_payments( $args = array() ) {
	// Backward compatibility.
	if ( ! empty( $args['start-date'] ) ) {
		$args['start_date'] = $args['start-date'];
		unset( $args['start-date'] );
	}

	if ( ! empty( $args['end-date'] ) ) {
		$args['end_date'] = $args['end-date'];
		unset( $args['end-date'] );
	}

	if ( ! empty( $args['form_id'] ) ) {
		$args['walkthecounty_forms'] = $args['form_id'];
		unset( $args['form_id'] );
	}

	// Extract all donations
	$args['number']   = - 1;
	$args['group_by'] = 'post_status';
	$args['count']    = 'true';

	$donations_obj   = new WalkTheCounty_Payments_Query( $args );
	$donations_count = $donations_obj->get_payment_by_group();

	/**
	 * Filter the payment counts group by status
	 *
	 * @since 1.0
	 */
	return (object) apply_filters( 'walkthecounty_count_payments', $donations_count, $args, $donations_obj );
}


/**
 * Check For Existing Payment
 *
 * @param  int $payment_id Payment ID.
 *
 * @since  1.0
 *
 * @return bool $exists True if payment exists, false otherwise.
 */
function walkthecounty_check_for_existing_payment( $payment_id ) {
	global $wpdb;

	return (bool) $wpdb->get_var(
		$wpdb->prepare(
			"
			SELECT ID
			FROM {$wpdb->posts}
			WHERE ID=%s
			AND post_status=%s
			",
			$payment_id,
			'publish'
		)
	);
}

/**
 * Get Payment Status
 *
 * @param WP_Post|WalkTheCounty_Payment|int $payment_id      Payment object or payment ID.
 * @param bool                     $return_label Whether to return the translated status label instead of status value.
 *                                               Default false.
 *
 * @since 1.0
 *
 * @return bool|mixed True if payment status exists, false otherwise.
 */
function walkthecounty_get_payment_status( $payment_id, $return_label = false ) {

	if ( ! is_numeric( $payment_id ) ) {
		if (
			$payment_id instanceof  WalkTheCounty_Payment
			|| $payment_id instanceof WP_Post
		) {
			$payment_id = $payment_id->ID;
		}
	}

	if ( ! $payment_id > 0 ) {
		return false;
	}

	$payment_status = get_post_status( $payment_id );

	$statuses = walkthecounty_get_payment_statuses();

	if ( empty( $payment_status ) || ! is_array( $statuses ) || empty( $statuses ) ) {
		return false;
	}

	if ( array_key_exists( $payment_status, $statuses ) ) {
		if ( true === $return_label ) {
			// Return translated status label.
			return $statuses[ $payment_status ];
		} else {
			// Account that our 'publish' status is labeled 'Complete'
			$post_status = 'publish' === $payment_status ? 'Complete' : $payment_status;

			// Make sure we're matching cases, since they matter
			return array_search( strtolower( $post_status ), array_map( 'strtolower', $statuses ) );
		}
	}

	return false;
}

/**
 * Retrieves all available statuses for payments.
 *
 * @since  1.0
 *
 * @return array $payment_status All the available payment statuses.
 */
function walkthecounty_get_payment_statuses() {
	$payment_statuses = array(
		'pending'     => __( 'Pending', 'walkthecounty' ),
		'publish'     => __( 'Complete', 'walkthecounty' ),
		'refunded'    => __( 'Refunded', 'walkthecounty' ),
		'failed'      => __( 'Failed', 'walkthecounty' ),
		'cancelled'   => __( 'Cancelled', 'walkthecounty' ),
		'abandoned'   => __( 'Abandoned', 'walkthecounty' ),
		'preapproval' => __( 'Pre-Approved', 'walkthecounty' ),
		'processing'  => __( 'Processing', 'walkthecounty' ),
		'revoked'     => __( 'Revoked', 'walkthecounty' ),
	);

	return apply_filters( 'walkthecounty_payment_statuses', $payment_statuses );
}

/**
 * Get Payment Status Keys
 *
 * Retrieves keys for all available statuses for payments
 *
 * @since 1.0
 *
 * @return array $payment_status All the available payment statuses.
 */
function walkthecounty_get_payment_status_keys() {
	$statuses = array_keys( walkthecounty_get_payment_statuses() );
	asort( $statuses );

	return array_values( $statuses );
}

/**
 * Get Earnings By Date
 *
 * @param int $day       Day number. Default is null.
 * @param int $month_num Month number. Default is null.
 * @param int $year      Year number. Default is null.
 * @param int $hour      Hour number. Default is null.
 *
 * @since 1.0
 *
 * @return int $earnings Earnings
 */
function walkthecounty_get_earnings_by_date( $day = null, $month_num, $year = null, $hour = null ) {
	// This is getting deprecated soon. Use WalkTheCounty_Payment_Stats with the get_earnings() method instead.
	global $wpdb;

	$args = array(
		'post_type'              => 'walkthecounty_payment',
		'nopaging'               => true,
		'year'                   => $year,
		'monthnum'               => $month_num,
		'post_status'            => array( 'publish' ),
		'fields'                 => 'ids',
		'update_post_term_cache' => false,
	);
	if ( ! empty( $day ) ) {
		$args['day'] = $day;
	}

	if ( isset( $hour ) ) {
		$args['hour'] = $hour;
	}

	$args = apply_filters( 'walkthecounty_get_earnings_by_date_args', $args );
	$key  = WalkTheCounty_Cache::get_key( 'walkthecounty_stats', $args );

	if ( ! empty( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'walkthecounty-refresh-reports' ) ) {
		$earnings = false;
	} else {
		$earnings = WalkTheCounty_Cache::get( $key );
	}

	if ( false === $earnings ) {
		$donations = get_posts( $args );
		$earnings  = 0;

		$donation_table     = WalkTheCounty()->payment_meta->table_name;
		$donation_table_col = WalkTheCounty()->payment_meta->get_meta_type() . '_id';

		if ( $donations ) {
			$donations      = implode( ',', $donations );
			$earning_totals = $wpdb->get_var( "SELECT SUM(meta_value) FROM {$donation_table} WHERE meta_key = '_walkthecounty_payment_total' AND {$donation_table_col} IN ({$donations})" );

			/**
			 * Filter The earnings by dates.
			 *
			 * @since 1.8.17
			 *
			 * @param float $earning_totals Total earnings between the dates.
			 * @param array $donations      Donations lists.
			 * @param array $args           Donation query args.
			 */
			$earnings = apply_filters( 'walkthecounty_get_earnings_by_date', $earning_totals, $donations, $args );
		}
		// Cache the results for one hour.
		WalkTheCounty_Cache::set( $key, $earnings, HOUR_IN_SECONDS );
	}

	return round( $earnings, 2 );
}

/**
 * Get Donations (sales) By Date
 *
 * @param int $day       Day number. Default is null.
 * @param int $month_num Month number. Default is null.
 * @param int $year      Year number. Default is null.
 * @param int $hour      Hour number. Default is null.
 *
 * @since 1.0
 *
 * @return int $count Sales
 */
function walkthecounty_get_sales_by_date( $day = null, $month_num = null, $year = null, $hour = null ) {

	// This is getting deprecated soon. Use WalkTheCounty_Payment_Stats with the get_sales() method instead.
	$args = array(
		'post_type'              => 'walkthecounty_payment',
		'nopaging'               => true,
		'year'                   => $year,
		'fields'                 => 'ids',
		'post_status'            => array( 'publish' ),
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	);

	$show_free = apply_filters( 'walkthecounty_sales_by_date_show_free', true, $args );

	if ( false === $show_free ) {
		$args['meta_query'] = array(
			array(
				'key'     => '_walkthecounty_payment_total',
				'value'   => 0,
				'compare' => '>',
				'type'    => 'NUMERIC',
			),
		);
	}

	if ( ! empty( $month_num ) ) {
		$args['monthnum'] = $month_num;
	}

	if ( ! empty( $day ) ) {
		$args['day'] = $day;
	}

	if ( isset( $hour ) ) {
		$args['hour'] = $hour;
	}

	$args = apply_filters( 'walkthecounty_get_sales_by_date_args', $args );

	$key = WalkTheCounty_Cache::get_key( 'walkthecounty_stats', $args );

	if ( ! empty( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'walkthecounty-refresh-reports' ) ) {
		$count = false;
	} else {
		$count = WalkTheCounty_Cache::get( $key );
	}

	if ( false === $count ) {
		$donations = new WP_Query( $args );
		$count     = (int) $donations->post_count;
		// Cache the results for one hour.
		WalkTheCounty_Cache::set( $key, $count, HOUR_IN_SECONDS );
	}

	return $count;
}

/**
 * Checks whether a payment has been marked as complete.
 *
 * @param int $payment_id Payment ID to check against.
 *
 * @since 1.0
 *
 * @return bool $ret True if complete, false otherwise.
 */
function walkthecounty_is_payment_complete( $payment_id ) {
	$ret            = false;
	$payment_status = '';

	if ( $payment_id > 0 && 'walkthecounty_payment' === get_post_type( $payment_id ) ) {
		$payment_status = get_post_status( $payment_id );

		if ( 'publish' === $payment_status ) {
			$ret = true;
		}
	}

	/**
	 * Filter the flag
	 *
	 * @since 1.0
	 */
	return apply_filters( 'walkthecounty_is_payment_complete', $ret, $payment_id, $payment_status );
}

/**
 * Get Total Donations.
 *
 * @since 1.0
 *
 * @return int $count Total number of donations.
 */
function walkthecounty_get_total_donations() {

	$payments = walkthecounty_count_payments();

	return $payments->publish;
}

/**
 * Get Total Earnings
 *
 * @param bool $recalculate Recalculate earnings forcefully.
 *
 * @since 1.0
 *
 * @return float $total Total earnings.
 */
function walkthecounty_get_total_earnings( $recalculate = false ) {

	$total      = get_option( 'walkthecounty_earnings_total', 0 );
	$meta_table = __walkthecounty_v20_bc_table_details( 'payment' );

	// Calculate total earnings.
	if ( ! $total || $recalculate ) {
		global $wpdb;

		$total = (float) 0;

		$args = apply_filters(
			'walkthecounty_get_total_earnings_args', array(
				'offset' => 0,
				'number' => - 1,
				'status' => array( 'publish' ),
				'fields' => 'ids',
			)
		);

		$payments = walkthecounty_get_payments( $args );
		if ( $payments ) {

			/**
			 * If performing a donation, we need to skip the very last payment in the database,
			 * since it calls walkthecounty_increase_total_earnings() on completion,
			 * which results in duplicated earnings for the very first donation.
			 */
			if ( did_action( 'walkthecounty_update_payment_status' ) ) {
				array_pop( $payments );
			}

			if ( ! empty( $payments ) ) {
				$payments = implode( ',', $payments );
				$total   += $wpdb->get_var( "SELECT SUM(meta_value) FROM {$meta_table['name']} WHERE meta_key = '_walkthecounty_payment_total' AND {$meta_table['column']['id']} IN({$payments})" );
			}
		}

		update_option( 'walkthecounty_earnings_total', $total, false );
	}

	if ( $total < 0 ) {
		$total = 0; // Don't ever show negative earnings.
	}

	return apply_filters( 'walkthecounty_total_earnings', round( $total, walkthecounty_get_price_decimals() ), $total );
}

/**
 * Increase the Total Earnings
 *
 * @param int $amount The amount you would like to increase the total earnings by. Default is 0.
 *
 * @since 1.0
 *
 * @return float $total Total earnings.
 */
function walkthecounty_increase_total_earnings( $amount = 0 ) {
	$total  = walkthecounty_get_total_earnings();
	$total += $amount;
	update_option( 'walkthecounty_earnings_total', $total, false );

	return $total;
}

/**
 * Decrease the Total Earnings
 *
 * @param int $amount The amount you would like to decrease the total earnings by.
 *
 * @since 1.0
 *
 * @return float $total Total earnings.
 */
function walkthecounty_decrease_total_earnings( $amount = 0 ) {
	$total  = walkthecounty_get_total_earnings();
	$total -= $amount;
	if ( $total < 0 ) {
		$total = 0;
	}
	update_option( 'walkthecounty_earnings_total', $total, false );

	return $total;
}

/**
 * Get Payment Meta for a specific Payment
 *
 * @param int    $payment_id Payment ID.
 * @param string $meta_key   The meta key to pull.
 * @param bool   $single     Pull single meta entry or as an object.
 *
 * @since 1.0
 *
 * @return mixed $meta Payment Meta.
 */
function walkthecounty_get_payment_meta( $payment_id = 0, $meta_key = '_walkthecounty_payment_meta', $single = true ) {
	return walkthecounty_get_meta( $payment_id, $meta_key, $single );
}

/**
 * Update the meta for a payment
 *
 * @param  int    $payment_id Payment ID.
 * @param  string $meta_key   Meta key to update.
 * @param  string $meta_value Value to update to.
 * @param  string $prev_value Previous value.
 *
 * @return mixed Meta ID if successful, false if unsuccessful.
 */
function walkthecounty_update_payment_meta( $payment_id = 0, $meta_key = '', $meta_value = '', $prev_value = '' ) {
	return walkthecounty_update_meta( $payment_id, $meta_key, $meta_value );
}

/**
 * Get the user_info Key from Payment Meta
 *
 * @param int $payment_id Payment ID.
 *
 * @since 1.0
 *
 * @return array $user_info User Info Meta Values.
 */
function walkthecounty_get_payment_meta_user_info( $payment_id ) {
	$donor_id   = 0;
	$donor_info = array(
		'first_name' => walkthecounty_get_meta( $payment_id, '_walkthecounty_donor_billing_first_name', true ),
		'last_name'  => walkthecounty_get_meta( $payment_id, '_walkthecounty_donor_billing_last_name', true ),
		'email'      => walkthecounty_get_meta( $payment_id, '_walkthecounty_donor_billing_donor_email', true ),
	);

	if ( empty( $donor_info['first_name'] ) ) {
		$donor_id                 = walkthecounty_get_payment_donor_id( $payment_id );
		$donor_info['first_name'] = WalkTheCounty()->donor_meta->get_meta( $donor_id, '_walkthecounty_donor_first_name', true );
	}

	if ( empty( $donor_info['last_name'] ) ) {
		$donor_id                = $donor_id ? $donor_id : walkthecounty_get_payment_donor_id( $payment_id );
		$donor_info['last_name'] = WalkTheCounty()->donor_meta->get_meta( $donor_id, '_walkthecounty_donor_last_name', true );
	}

	if ( empty( $donor_info['email'] ) ) {
		$donor_id            = $donor_id ? $donor_id : walkthecounty_get_payment_donor_id( $payment_id );
		$donor_info['email'] = WalkTheCounty()->donors->get_column_by( 'email', 'id', $donor_id );
	}

	$donor_info['title'] = WalkTheCounty()->donor_meta->get_meta( $donor_id, '_walkthecounty_donor_title_prefix', true );

	$donor_info['address']  = walkthecounty_get_donation_address( $payment_id );
	$donor_info['id']       = walkthecounty_get_payment_user_id( $payment_id );
	$donor_info['donor_id'] = walkthecounty_get_payment_donor_id( $payment_id );

	return $donor_info;
}

/**
 * Get the donations Key from Payment Meta
 *
 * Retrieves the form_id from a (Previously titled walkthecounty_get_payment_meta_donations)
 *
 * @param int $payment_id Payment ID.
 *
 * @since 1.0
 *
 * @return int $form_id Form ID.
 */
function walkthecounty_get_payment_form_id( $payment_id ) {
	return (int) walkthecounty_get_meta( $payment_id, '_walkthecounty_payment_form_id', true );
}

/**
 * Get the user email associated with a payment
 *
 * @param int $payment_id Payment ID.
 *
 * @since 1.0
 *
 * @return string $email User email.
 */
function walkthecounty_get_payment_user_email( $payment_id ) {
	$email = walkthecounty_get_meta( $payment_id, '_walkthecounty_payment_donor_email', true );

	if ( empty( $email ) && ( $donor_id = walkthecounty_get_payment_donor_id( $payment_id ) ) ) {
		$email = WalkTheCounty()->donors->get_column( 'email', $donor_id );
	}

	return $email;
}

/**
 * Is the payment provided associated with a user account
 *
 * @param int $payment_id The payment ID.
 *
 * @since 1.3
 *
 * @return bool $is_guest_payment If the payment is associated with a user (false) or not (true)
 */
function walkthecounty_is_guest_payment( $payment_id ) {
	$payment_user_id  = walkthecounty_get_payment_user_id( $payment_id );
	$is_guest_payment = ! empty( $payment_user_id ) && $payment_user_id > 0 ? false : true;

	return (bool) apply_filters( 'walkthecounty_is_guest_payment', $is_guest_payment, $payment_id );
}

/**
 * Get the user ID associated with a payment
 *
 * @param int $payment_id Payment ID.
 *
 * @since 1.3
 *
 * @return int $user_id User ID.
 */
function walkthecounty_get_payment_user_id( $payment_id ) {
	global $wpdb;
	$paymentmeta_table = WalkTheCounty()->payment_meta->table_name;
	$donationmeta_primary_key = WalkTheCounty()->payment_meta->get_meta_type() . '_id';

	return (int) $wpdb->get_var(
		$wpdb->prepare(
			"
			SELECT user_id
			FROM $wpdb->donors
			WHERE id=(
				SELECT meta_value
				FROM $paymentmeta_table
				WHERE {$donationmeta_primary_key}=%s
				AND meta_key=%s
			)
			",
			$payment_id,
			'_walkthecounty_payment_donor_id'
		)
	);
}

/**
 * Get the donor ID associated with a payment.
 *
 * @param int $payment_id Payment ID.
 *
 * @since 1.0
 *
 * @return int $payment->customer_id Donor ID.
 */
function walkthecounty_get_payment_donor_id( $payment_id ) {
	return walkthecounty_get_meta( $payment_id, '_walkthecounty_payment_donor_id', true );
}

/**
 * Get the donor email associated with a donation.
 *
 * @param int $payment_id Payment ID.
 *
 * @since 2.1.0
 *
 * @return string
 */
function walkthecounty_get_donation_donor_email( $payment_id ) {
	return walkthecounty_get_meta( $payment_id, '_walkthecounty_payment_donor_email', true );
}

/**
 * Get the IP address used to make a donation
 *
 * @param int $payment_id Payment ID.
 *
 * @since 1.0
 *
 * @return string $ip User IP.
 */
function walkthecounty_get_payment_user_ip( $payment_id ) {
	return walkthecounty_get_meta( $payment_id, '_walkthecounty_payment_donor_ip', true );
}

/**
 * Get the date a payment was completed
 *
 * @param int $payment_id Payment ID.
 *
 * @since 1.0
 *
 * @return string $date The date the payment was completed.
 */
function walkthecounty_get_payment_completed_date( $payment_id = 0 ) {
	return walkthecounty_get_meta( $payment_id, '_walkthecounty_completed_date', true );
}

/**
 * Get the gateway associated with a payment
 *
 * @param int $payment_id Payment ID.
 *
 * @since 1.0
 *
 * @return string $gateway Gateway.
 */
function walkthecounty_get_payment_gateway( $payment_id ) {
	return walkthecounty_get_meta( $payment_id, '_walkthecounty_payment_gateway', true );
}

/**
 * Check if donation have specific gateway or not
 *
 * @since 2.1.0
 *
 * @param int|WalkTheCounty_Payment $donation_id Donation ID
 * @param string           $gateway_id  Gateway ID
 *
 * @return bool
 */
function walkthecounty_has_payment_gateway( $donation_id, $gateway_id ) {
	$donation_gateway = $donation_id instanceof WalkTheCounty_Payment ?
		$donation_id->gateway :
		walkthecounty_get_payment_gateway( $donation_id );

	return $gateway_id === $donation_gateway;
}

/**
 * Get the currency code a payment was made in
 *
 * @param int $payment_id Payment ID.
 *
 * @since 1.0
 *
 * @return string $currency The currency code.
 */
function walkthecounty_get_payment_currency_code( $payment_id = 0 ) {
	return walkthecounty_get_meta( $payment_id, '_walkthecounty_payment_currency', true );
}

/**
 * Get the currency name a payment was made in
 *
 * @param int $payment_id Payment ID.
 *
 * @since 1.0
 *
 * @return string $currency The currency name.
 */
function walkthecounty_get_payment_currency( $payment_id = 0 ) {
	$currency = walkthecounty_get_payment_currency_code( $payment_id );

	return apply_filters( 'walkthecounty_payment_currency', walkthecounty_get_currency_name( $currency ), $payment_id );
}

/**
 * Get the key for a donation
 *
 * @param int $payment_id Payment ID.
 *
 * @since 1.0
 *
 * @return string $key Donation key.
 */
function walkthecounty_get_payment_key( $payment_id = 0 ) {
	return walkthecounty_get_meta( $payment_id, '_walkthecounty_payment_purchase_key', true );
}

/**
 * Get the payment order number
 *
 * This will return the payment ID if sequential order numbers are not enabled or the order number does not exist
 *
 * @param int $payment_id Payment ID.
 *
 * @since 1.0
 *
 * @return string $number Payment order number.
 */
function walkthecounty_get_payment_number( $payment_id = 0 ) {
	return WalkTheCounty()->seq_donation_number->get_serial_code( $payment_id );
}


/**
 * Get Donation Amount
 *
 * Get the fully formatted or unformatted donation amount which is sent through walkthecounty_currency_filter()
 * and walkthecounty_format_amount() to format the amount correctly in case of formatted amount.
 *
 * @param int|WalkTheCounty_Payment $donation_id Donation ID or Donation Object.
 * @param bool|array       $format_args Currency Formatting Arguments.
 *
 * @since 1.0
 * @since 1.8.17 Added filter and internally use functions.
 *
 * @return string $amount Fully formatted donation amount.
 */
function walkthecounty_donation_amount( $donation_id, $format_args = array() ) {
	if ( ! $donation_id ) {
		return '';
	} elseif ( ! is_numeric( $donation_id ) && ( $donation_id instanceof WalkTheCounty_Payment ) ) {
		$donation_id = $donation_id->ID;
	}

	$amount        = $formatted_amount = walkthecounty_get_payment_total( $donation_id );
	$currency_code = walkthecounty_get_payment_currency_code( $donation_id );

	if ( is_bool( $format_args ) ) {
		$format_args = array(
			'currency' => (bool) $format_args,
			'amount'   => (bool) $format_args,
		);
	}

	$format_args = wp_parse_args(
		$format_args,
		array(
			'currency' => false,
			'amount'   => false,

			// Define context of donation amount, by default keep $type as blank.
			// Pass as 'stats' to calculate donation report on basis of base amount for the Currency-Switcher Add-on.
			// For Eg. In Currency-Switcher add on when donation has been made through
			// different currency other than base currency, in that case for correct
			// report calculation based on base currency we will need to return donation
			// base amount and not the converted amount .
			'type'     => '',
		)
	);

	if ( $format_args['amount'] || $format_args['currency'] ) {

		if ( $format_args['amount'] ) {

			$formatted_amount = walkthecounty_format_amount(
				$amount,
				! is_array( $format_args['amount'] ) ?
					array(
						'sanitize' => false,
						'currency' => $currency_code,
					) :
					$format_args['amount']
			);
		}

		if ( $format_args['currency'] ) {
			$formatted_amount = walkthecounty_currency_filter(
				$formatted_amount,
				! is_array( $format_args['currency'] ) ?
					array( 'currency_code' => $currency_code ) :
					$format_args['currency']
			);
		}
	}

	/**
	 * Filter Donation amount.
	 *
	 * @since 1.8.17
	 *
	 * @param string $formatted_amount Formatted/Un-formatted amount.
	 * @param float  $amount           Donation amount.
	 * @param int    $donation_id      Donation ID.
	 * @param string $type             Donation amount type.
	 */
	return apply_filters( 'walkthecounty_donation_amount', (string) $formatted_amount, $amount, $donation_id, $format_args );
}

/**
 * Payment Subtotal
 *
 * Retrieves subtotal for payment and then returns a full formatted amount. This
 * function essentially calls walkthecounty_get_payment_subtotal()
 *
 * @param int $payment_id Payment ID.
 *
 * @since 1.5
 *
 * @see   walkthecounty_get_payment_subtotal()
 *
 * @return array Fully formatted payment subtotal.
 */
function walkthecounty_payment_subtotal( $payment_id = 0 ) {
	$subtotal = walkthecounty_get_payment_subtotal( $payment_id );

	return walkthecounty_currency_filter( walkthecounty_format_amount( $subtotal, array( 'sanitize' => false ) ), array( 'currency_code' => walkthecounty_get_payment_currency_code( $payment_id ) ) );
}

/**
 * Get Payment Subtotal
 *
 * Retrieves subtotal for payment and then returns a non formatted amount.
 *
 * @param int $payment_id Payment ID.
 *
 * @since 1.5
 *
 * @return float $subtotal Subtotal for payment (non formatted).
 */
function walkthecounty_get_payment_subtotal( $payment_id = 0 ) {
	$payment = new WalkTheCounty_Payment( $payment_id );

	return $payment->subtotal;
}

/**
 * Retrieves the donation ID
 *
 * @param int $payment_id Payment ID.
 *
 * @since  1.0
 *
 * @return string The donation ID.
 */
function walkthecounty_get_payment_transaction_id( $payment_id = 0 ) {
	$transaction_id = walkthecounty_get_meta( $payment_id, '_walkthecounty_payment_transaction_id', true );

	if ( empty( $transaction_id ) ) {
		$gateway        = walkthecounty_get_payment_gateway( $payment_id );
		$transaction_id = apply_filters( "walkthecounty_get_payment_transaction_id-{$gateway}", $payment_id );
	}

	return $transaction_id;
}

/**
 * Sets a Transaction ID in post meta for the walkthecountyn Payment ID.
 *
 * @param int    $payment_id     Payment ID.
 * @param string $transaction_id The transaction ID from the gateway.
 *
 * @since  1.0
 *
 * @return bool|mixed
 */
function walkthecounty_set_payment_transaction_id( $payment_id = 0, $transaction_id = '' ) {

	if ( empty( $payment_id ) || empty( $transaction_id ) ) {
		return false;
	}

	$transaction_id = apply_filters( 'walkthecounty_set_payment_transaction_id', $transaction_id, $payment_id );

	return walkthecounty_update_payment_meta( $payment_id, '_walkthecounty_payment_transaction_id', $transaction_id );
}

/**
 * Retrieve the donation ID based on the key
 *
 * @param string $key  the key to search for.
 *
 * @since 1.0
 * @global object $wpdb Used to query the database using the WordPress Database API.
 *
 * @return int $purchase Donation ID.
 */
function walkthecounty_get_donation_id_by_key( $key ) {
	global $wpdb;

	$meta_table = __walkthecounty_v20_bc_table_details( 'payment' );

	$purchase = $wpdb->get_var(
		$wpdb->prepare(
			"
				SELECT {$meta_table['column']['id']}
				FROM {$meta_table['name']}
				WHERE meta_key = '_walkthecounty_payment_purchase_key'
				AND meta_value = %s
				ORDER BY {$meta_table['column']['id']} DESC
				LIMIT 1
				",
			$key
		)
	);

	if ( $purchase != null ) {
		return $purchase;
	}

	return 0;
}


/**
 * Retrieve the donation ID based on the transaction ID
 *
 * @param string $key  The transaction ID to search for.
 *
 * @since 1.3
 * @global object $wpdb Used to query the database using the WordPress Database API.
 *
 * @return int $purchase Donation ID.
 */
function walkthecounty_get_purchase_id_by_transaction_id( $key ) {
	global $wpdb;
	$meta_table = __walkthecounty_v20_bc_table_details( 'payment' );

	$purchase = $wpdb->get_var( $wpdb->prepare( "SELECT {$meta_table['column']['id']} FROM {$meta_table['name']} WHERE meta_key = '_walkthecounty_payment_transaction_id' AND meta_value = %s LIMIT 1", $key ) );

	if ( $purchase != null ) {
		return $purchase;
	}

	return 0;
}

/**
 * Retrieve all notes attached to a donation
 *
 * @param int    $payment_id The donation ID to retrieve notes for.
 * @param string $search     Search for notes that contain a search term.
 *
 * @since 1.0
 *
 * @return array $notes Donation Notes
 */
function walkthecounty_get_payment_notes( $payment_id = 0, $search = '' ) {
	return WalkTheCounty_Comment::get( $payment_id,'payment', array(), $search );
}


/**
 * Add a note to a payment
 *
 * @param int    $payment_id The payment ID to store a note for.
 * @param string $note       The note to store.
 *
 * @since 1.0
 *
 * @return int The new note ID
 */
function walkthecounty_insert_payment_note( $payment_id = 0, $note = '' ) {
	return WalkTheCounty_Comment::add( $payment_id, $note, 'payment' );
}

/**
 * Deletes a payment note
 *
 * @param int $comment_id The comment ID to delete.
 * @param int $payment_id The payment ID the note is connected to.
 *
 * @since 1.0
 *
 * @return bool True on success, false otherwise.
 */
function walkthecounty_delete_payment_note( $comment_id = 0, $payment_id = 0 ) {
	return WalkTheCounty_Comment::delete( $comment_id, $payment_id, 'payment' );
}

/**
 * Gets the payment note HTML
 *
 * @param object|int $note       The comment object or ID.
 * @param int        $payment_id The payment ID the note is connected to.
 *
 * @since 1.0
 *
 * @return string
 */
function walkthecounty_get_payment_note_html( $note, $payment_id = 0 ) {

	if ( is_numeric( $note ) ) {
		if ( ! walkthecounty_has_upgrade_completed( 'v230_move_donor_note' ) ) {
			$note = get_comment( $note );
		} else{
			$note = WalkTheCounty()->comment->db->get( $note );
		}
	}

	if ( ! empty( $note->user_id ) ) {
		$user = get_userdata( $note->user_id );
		$user = $user->display_name;
	} else {
		$user = __( 'System', 'walkthecounty' );
	}

	$date_format = walkthecounty_date_format() . ', ' . get_option( 'time_format' );

	$delete_note_url = wp_nonce_url(
		add_query_arg(
			array(
				'walkthecounty-action' => 'delete_payment_note',
				'note_id'     => $note->comment_ID,
				'payment_id'  => $payment_id,
			)
		), 'walkthecounty_delete_payment_note_' . $note->comment_ID
	);

	$note_html  = '<div class="walkthecounty-payment-note" id="walkthecounty-payment-note-' . $note->comment_ID . '">';
	$note_html .= '<p>';
	$note_html .= '<strong>' . $user . '</strong>&nbsp;&ndash;&nbsp;<span style="color:#aaa;font-style:italic;">' . date_i18n( $date_format, strtotime( $note->comment_date ) ) . '</span><br/>';
	$note_html .= nl2br( $note->comment_content );
	$note_html .= '&nbsp;&ndash;&nbsp;<a href="' . esc_url( $delete_note_url ) . '" class="walkthecounty-delete-payment-note" data-note-id="' . absint( $note->comment_ID ) . '" data-payment-id="' . absint( $payment_id ) . '" aria-label="' . __( 'Delete this donation note.', 'walkthecounty' ) . '">' . __( 'Delete', 'walkthecounty' ) . '</a>';
	$note_html .= '</p>';
	$note_html .= '</div>';

	return $note_html;

}


/**
 * Filter where older than one week
 *
 * @param string $where Where clause.
 *
 * @access public
 * @since  1.0
 *
 * @return string $where Modified where clause.
 */
function walkthecounty_filter_where_older_than_week( $where = '' ) {
	// Payments older than one week.
	$start  = date( 'Y-m-d', strtotime( '-7 days' ) );
	$where .= " AND post_date <= '{$start}'";

	return $where;
}


/**
 * Get Payment Form ID.
 *
 * Retrieves the form title and appends the level name if present.
 *
 * @param int|WalkTheCounty_Payment $donation_id Donation Data Object.
 * @param array            $args     a. only_level = If set to true will only return the level name if multi-level
 *                                   enabled. b. separator  = The separator between the Form Title and the Donation
 *                                   Level.
 *
 * @since 1.5
 *
 * @return string $form_title Returns the full title if $only_level is false, otherwise returns the levels title.
 */
function walkthecounty_get_donation_form_title( $donation_id, $args = array() ) {
	// Backward compatibility.
	if ( ! is_numeric( $donation_id ) && $donation_id instanceof WalkTheCounty_Payment ) {
		$donation_id = $donation_id->ID;
	}

	if ( ! $donation_id ) {
		return '';
	}

	$defaults = array(
		'only_level' => false,
		'separator'  => '',
	);

	$args = wp_parse_args( $args, $defaults );

	$form_id     = walkthecounty_get_payment_form_id( $donation_id );
	$price_id    = walkthecounty_get_meta( $donation_id, '_walkthecounty_payment_price_id', true );
	$form_title  = walkthecounty_get_meta( $donation_id, '_walkthecounty_payment_form_title', true );
	$only_level  = $args['only_level'];
	$separator   = $args['separator'];
	$level_label = '';

	$cache_key = WalkTheCounty_Cache::get_key(
		'walkthecounty_forms',
		array(
			$form_id,
			$price_id,
			$form_title,
			$only_level,
			$separator,
		), false
	);

	$form_title_html = WalkTheCounty_Cache::get_db_query( $cache_key );

	if ( is_null( $form_title_html ) ) {
		if ( true === $only_level ) {
			$form_title = '';
		}

		$form_title_html = $form_title;

		if ( 'custom' === $price_id ) {

			$custom_amount_text = walkthecounty_get_meta( $form_id, '_walkthecounty_custom_amount_text', true );
			$level_label        = ! empty( $custom_amount_text ) ? $custom_amount_text : __( 'Custom Amount', 'walkthecounty' );

			// Show custom amount level only in backend otherwise hide it.
			if ( 'set' === walkthecounty_get_meta( $form_id, '_walkthecounty_price_option', true ) && ! is_admin() ) {
				$level_label = '';
			}
		} elseif ( walkthecounty_has_variable_prices( $form_id ) ) {
			$level_label = walkthecounty_get_price_option_name( $form_id, $price_id, $donation_id, false );
		}

		// Only add separator if there is a form title.
		if (
			! empty( $form_title_html ) &&
			! empty( $level_label )
		) {
			$form_title_html .= " {$separator} ";
		}

		$form_title_html .= "<span class=\"donation-level-text-wrap\">{$level_label}</span>";
		WalkTheCounty_Cache::set_db_query( $cache_key, $form_title_html );
	}

	/**
	 * Filter form title with level html
	 *
	 * @since 1.0
	 * @todo: remove third param after 2.1.0
	 */
	return apply_filters( 'walkthecounty_get_donation_form_title', $form_title_html, $donation_id, '' );
}

/**
 * Get Price ID
 *
 * Retrieves the Price ID when provided a proper form ID and price (donation) total
 *
 * @param int    $form_id Form ID.
 * @param string $price   Donation Amount.
 *
 * @return string $price_id
 */
function walkthecounty_get_price_id( $form_id, $price ) {
	$price_id = null;

	if ( walkthecounty_has_variable_prices( $form_id ) ) {

		$levels = walkthecounty_get_meta( $form_id, '_walkthecounty_donation_levels', true );

		foreach ( $levels as $level ) {

			$level_amount = walkthecounty_maybe_sanitize_amount( $level['_walkthecounty_amount'] );

			// Check that this indeed the recurring price.
			if ( $level_amount == $price ) {

				$price_id = $level['_walkthecounty_id']['level_id'];
				break;

			}
		}

		if ( is_null( $price_id ) && walkthecounty_is_custom_price_mode( $form_id ) ) {
			$price_id = 'custom';
		}
	}

	// Price ID must be numeric or string.
	$price_id = ! is_numeric( $price_id ) && ! is_string( $price_id ) ? 0 : $price_id;

	/**
	 * Filter the price id
	 *
	 * @since 2.0
	 *
	 * @param string $price_id
	 * @param int    $form_id
	 */
	return apply_filters( 'walkthecounty_get_price_id', $price_id, $form_id );
}

/**
 * Get/Print walkthecounty form dropdown html
 *
 * This function is wrapper to public method forms_dropdown of WalkTheCounty_HTML_Elements class to get/print form dropdown html.
 * WalkTheCounty_HTML_Elements is defined in includes/class-walkthecounty-html-elements.php.
 *
 * @param array $args Arguments for form dropdown.
 * @param bool  $echo This parameter decides if print form dropdown html output or not.
 *
 * @since 1.6
 *
 * @return string
 */
function walkthecounty_get_form_dropdown( $args = array(), $echo = false ) {
	$form_dropdown_html = WalkTheCounty()->html->forms_dropdown( $args );

	if ( ! $echo ) {
		return $form_dropdown_html;
	}

	echo $form_dropdown_html;
}

/**
 * Get/Print walkthecounty form variable price dropdown html
 *
 * @param array $args Arguments for form dropdown.
 * @param bool  $echo This parameter decide if print form dropdown html output or not.
 *
 * @since 1.6
 *
 * @return string|bool
 */
function walkthecounty_get_form_variable_price_dropdown( $args = array(), $echo = false ) {

	// Check for walkthecounty form id.
	if ( empty( $args['id'] ) ) {
		return false;
	}

	$form = new WalkTheCounty_Donate_Form( $args['id'] );

	// Check if form has variable prices or not.
	if ( ! $form->ID || ! $form->has_variable_prices() ) {
		return false;
	}

	$variable_prices        = $form->get_prices();
	$variable_price_options = array();

	// Check if multi donation form support custom donation or not.
	if ( $form->is_custom_price_mode() ) {
		$variable_price_options['custom'] = _x( 'Custom', 'custom donation dropdown item', 'walkthecounty' );
	}

	// Get variable price and ID from variable price array.
	foreach ( $variable_prices as $variable_price ) {
		$variable_price_options[ $variable_price['_walkthecounty_id']['level_id'] ] = ! empty( $variable_price['_walkthecounty_text'] ) ? $variable_price['_walkthecounty_text'] : walkthecounty_currency_filter( walkthecounty_format_amount( $variable_price['_walkthecounty_amount'], array( 'sanitize' => false ) ) );
	}

	// Update options.
	$args = array_merge(
		$args, array(
			'options' => $variable_price_options,
		)
	);

	// Generate select html.
	$form_dropdown_html = WalkTheCounty()->html->select( $args );

	if ( ! $echo ) {
		return $form_dropdown_html;
	}

	echo $form_dropdown_html;
}

/**
 * Get the price_id from the payment meta.
 *
 * Some gateways use `walkthecounty_price_id` and others were using just `price_id`;
 * This checks for the difference and falls back to retrieving it from the form as a last resort.
 *
 * @param array $payment_meta Payment Meta.
 *
 * @since 1.8.6
 *
 * @return string
 */
function walkthecounty_get_payment_meta_price_id( $payment_meta ) {

	if ( isset( $payment_meta['walkthecounty_price_id'] ) ) {
		$price_id = $payment_meta['walkthecounty_price_id'];
	} elseif ( isset( $payment_meta['price_id'] ) ) {
		$price_id = $payment_meta['price_id'];
	} else {
		$price_id = walkthecounty_get_price_id( $payment_meta['walkthecounty_form_id'], $payment_meta['price'] );
	}

	/**
	 * Filter the price id
	 *
	 * @since 1.8.6
	 *
	 * @param string $price_id
	 * @param array  $payment_meta
	 */
	return apply_filters( 'walkthecounty_get_payment_meta_price_id', $price_id, $payment_meta );

}


/**
 * Get payment total amount
 *
 * @since 2.1.0
 *
 * @param int $payment_id
 *
 * @return float
 */
function walkthecounty_get_payment_total( $payment_id = 0 ) {
	return round(
		floatval( walkthecounty_get_meta( $payment_id, '_walkthecounty_payment_total', true ) ),
		walkthecounty_get_price_decimals( $payment_id )
	);
}

/**
 * Get donation address
 *
 * since 2.1.0
 *
 * @param int $donation_id
 *
 * @return array
 */
function walkthecounty_get_donation_address( $donation_id ) {
	$address['line1']   = walkthecounty_get_meta( $donation_id, '_walkthecounty_donor_billing_address1', true, '' );
	$address['line2']   = walkthecounty_get_meta( $donation_id, '_walkthecounty_donor_billing_address2', true, '' );
	$address['city']    = walkthecounty_get_meta( $donation_id, '_walkthecounty_donor_billing_city', true, '' );
	$address['state']   = walkthecounty_get_meta( $donation_id, '_walkthecounty_donor_billing_state', true, '' );
	$address['zip']     = walkthecounty_get_meta( $donation_id, '_walkthecounty_donor_billing_zip', true, '' );
	$address['country'] = walkthecounty_get_meta( $donation_id, '_walkthecounty_donor_billing_country', true, '' );

	return $address;
}


/**
 *  Check if donation completed or not
 *
 * @since 2.1.0
 *
 * @param int $donation_id
 *
 * @return bool
 */
function walkthecounty_is_donation_completed( $donation_id ) {
	global $wpdb;

	/**
	 * Filter the flag
	 *
	 * @since 2.1.0
	 *
	 * @param bool
	 * @param int $donation_id
	 */
	return apply_filters(
		'walkthecounty_is_donation_completed', (bool) $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT meta_value
				FROM {$wpdb->donationmeta}
				WHERE EXISTS (
					SELECT ID
					FROM {$wpdb->posts}
					WHERE post_status=%s
					AND ID=%d
				)
				AND {$wpdb->donationmeta}.meta_key=%s
				",
				'publish',
				$donation_id,
				'_walkthecounty_completed_date'
			)
		), $donation_id
	);
}

/**
 * Verify if donation anonymous or not
 *
 * @since 2.2.1
 * @param $donation_id
 *
 * @return bool
 */
function walkthecounty_is_anonymous_donation( $donation_id ) {
	$value = false;

	if( (int) walkthecounty_get_meta( $donation_id, '_walkthecounty_anonymous_donation', true ) ){
		$value = true;
	}

	return $value;
}
