<?php
/**
 * Exports Functions
 *
 * These functions are used for exporting data from WalkTheCounty
 *
 * @package     WalkTheCounty
 * @subpackage  Admin/Export
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



/**
 * Process batch exports via ajax
 *
 * @since 1.5
 * @return void
 */
function walkthecounty_do_ajax_export() {

	require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/tools/export/class-batch-export.php';

	parse_str( $_POST['form'], $form );

	$_REQUEST = $form = (array) $form;

	if (
		! wp_verify_nonce( $_REQUEST['walkthecounty_ajax_export'], 'walkthecounty_ajax_export' )
		|| ! current_user_can( 'manage_walkthecounty_settings' )
	) {
		die( '-2' );
	}

	/**
	 * Fires before batch export.
	 *
	 * @since 1.5
	 *
	 * @param string $class Export class.
	 */
	do_action( 'walkthecounty_batch_export_class_include', $form['walkthecounty-export-class'] );

	$step   = absint( $_POST['step'] );
	$class  = sanitize_text_field( $form['walkthecounty-export-class'] );

	/* @var WalkTheCounty_Batch_Export $export */
	$export = new $class( $step );

	if ( ! $export->can_export() ) {
		die( '-1' );
	}

	if ( ! $export->is_writable ) {
		$json_args = array(
			'error'   => true,
			'message' => esc_html__( 'Export location or file not writable.', 'walkthecounty' )
		);
		echo json_encode($json_args);
		exit;
	}

	$export->set_properties( walkthecounty_clean( $_REQUEST ) );

	$export->pre_fetch();

	$ret = $export->process_step();

	$percentage = $export->get_percentage_complete();

	if ( $ret ) {

		$step += 1;
		$json_data = array(
			'step' => $step,
			'percentage' => $percentage
		);

	} elseif ( true === $export->is_empty ) {

		$json_data = array(
			'error'   => true,
			'message' => esc_html__( 'No data found for export parameters.', 'walkthecounty' )
		);

	} elseif ( true === $export->done && true === $export->is_void ) {

		$message = ! empty( $export->message ) ?
			$export->message :
			esc_html__( 'Batch Processing Complete', 'walkthecounty' );

		$json_data = array(
			'success' => true,
			'message' => $message
		);

	} else {

		$args = array_merge( $_REQUEST, array(
			'step'        => $step,
			'class'       => $class,
			'nonce'       => wp_create_nonce( 'walkthecounty-batch-export' ),
			'walkthecounty_action' => 'form_batch_export',
		) );

		$json_data = array(
			'step' => 'done',
			'url' => add_query_arg( $args, admin_url() )
		);

	}

	$export->unset_properties( walkthecounty_clean( $_REQUEST ), $export );
	echo json_encode( $json_data );
	exit;
}

add_action( 'wp_ajax_walkthecounty_do_ajax_export', 'walkthecounty_do_ajax_export' );


/**
 * This function is used to define default columns for export.
 *
 * Note: This function is for internal purposes only.
 * Use filter "walkthecounty_export_donors_get_default_columns" instead.
 *
 * @since 2.2.6
 *
 * @return array
 */
function walkthecounty_export_donors_get_default_columns() {

	$default_columns = array(
		'full_name'          => __( 'Name', 'walkthecounty' ),
		'email'              => __( 'Email', 'walkthecounty' ),
		'address'            => __( 'Address', 'walkthecounty' ),
		'userid'             => __( 'User ID', 'walkthecounty' ),
		'donor_created_date' => __( 'Donor Created Date', 'walkthecounty' ),
		'donations'          => __( 'Number of donations', 'walkthecounty' ),
		'donation_sum'       => __( 'Total Donated', 'walkthecounty' ),
	);

	/**
	 * This filter will be used to define default columns for export.
	 *
	 * @since 2.2.6
	 */
	return apply_filters( 'walkthecounty_export_donors_get_default_columns', $default_columns );
}
