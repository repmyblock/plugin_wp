<?php
/**
 * Handle renamed actions.
 */
$walkthecounty_map_deprecated_actions = walkthecounty_deprecated_actions();

foreach ( $walkthecounty_map_deprecated_actions as $new => $old ) {
	add_action( $new, 'walkthecounty_deprecated_action_mapping', 10, 4 );
}

/**
 * Deprecated actions.
 *
 * @return array An array of deprecated WalkTheCounty actions.
 */
function walkthecounty_deprecated_actions() {

	$walkthecounty_deprecated_actions = array(
		// New action hook                               Old action hook.
		'walkthecounty_donation_form_login_fields'                => 'walkthecounty_purchase_form_login_fields',
		'walkthecounty_donation_form_register_login_fields'       => 'walkthecounty_purchase_form_register_login_fields',
		'walkthecounty_donation_form_before_register_login'       => 'walkthecounty_purchase_form_before_register_login',
		'walkthecounty_donation_form_before_cc_form'              => 'walkthecounty_purchase_form_before_cc_form',
		'walkthecounty_donation_form_after_cc_form'               => 'walkthecounty_purchase_form_after_cc_form',
		'walkthecounty_donation_form_no_access'                   => 'walkthecounty_purchase_form_no_access',
		'walkthecounty_donation_form_register_fields'             => 'walkthecounty_purchase_form_register_fields',
		'walkthecounty_donation_form_after_user_info'             => 'walkthecounty_purchase_form_after_user_info',
		'walkthecounty_donation_form_before_personal_info'        => 'walkthecounty_purchase_form_before_personal_info',
		'walkthecounty_donation_form_before_email'                => 'walkthecounty_purchase_form_before_email',
		'walkthecounty_donation_form_after_email'                 => 'walkthecounty_purchase_form_after_email',
		'walkthecounty_donation_form_user_info'                   => 'walkthecounty_purchase_form_user_info',
		'walkthecounty_donation_form_after_personal_info'         => 'walkthecounty_purchase_form_after_personal_info',
		'walkthecounty_donation_form'                             => 'walkthecounty_purchase_form',
		'walkthecounty_donation_form_wrap_bottom'                 => 'walkthecounty_purchase_form_wrap_bottom',
		'walkthecounty_donation_form_before_submit'               => 'walkthecounty_purchase_form_before_submit',
		'walkthecounty_donation_form_after_submit'                => 'walkthecounty_purchase_form_after_submit',
		'walkthecounty_donation_history_header_before'            => 'walkthecounty_purchase_history_header_before',
		'walkthecounty_donation_history_header_after'             => 'walkthecounty_purchase_history_header_after',
		'walkthecounty_donation_history_row_start'                => 'walkthecounty_purchase_history_row_start',
		'walkthecounty_donation_history_row_end'                  => 'walkthecounty_purchase_history_row_end',
		'walkthecounty_payment_form_top'                          => 'walkthecounty_purchase_form_top',
		'walkthecounty_payment_form_bottom'                       => 'walkthecounty_purchase_form_bottom',
		'walkthecounty_pre_process_donation'                      => 'walkthecounty_pre_process_purchase',
		'walkthecounty_complete_donation'                         => 'walkthecounty_complete_purchase',
		'walkthecounty_ajax_donation_errors'                      => 'walkthecounty_ajax_checkout_errors',
		'walkthecounty_admin_donation_email'                      => 'walkthecounty_admin_sale_notice',
		'walkthecounty_tools_tab_export_content_top'              => 'walkthecounty_reports_tab_export_content_top',
		'walkthecounty_tools_tab_export_table_top'                => 'walkthecounty_reports_tab_export_table_top',
		'walkthecounty_tools_tab_export_table_bottom'             => 'walkthecounty_reports_tab_export_table_bottom',
		'walkthecounty_tools_tab_export_content_bottom'           => 'walkthecounty_report_tab_export_table_bottom',
		'walkthecounty_pre_edit_donor'                            => 'walkthecounty_pre_edit_customer',
		'walkthecounty_post_edit_donor'                           => 'walkthecounty_post_edit_customer',
		'walkthecounty_pre_donor_disconnect_user_id'              => 'walkthecounty_pre_customer_disconnect_user_id',
		'walkthecounty_post_donor_disconnect_user_id'             => 'walkthecounty_post_customer_disconnect_user_id',
		'walkthecounty_update_donor_email_on_user_update'         => 'walkthecounty_update_customer_email_on_user_update',
		'walkthecounty_pre_insert_donor'                          => 'walkthecounty_pre_insert_customer',
		'walkthecounty_post_insert_donor'                         => 'walkthecounty_post_insert_customer',
		'walkthecounty_donor_pre_create'                          => 'walkthecounty_customer_pre_create',
		'walkthecounty_donor_post_create'                         => 'walkthecounty_customer_post_create',
		'walkthecounty_donor_pre_update'                          => 'walkthecounty_customer_pre_update',
		'walkthecounty_donor_post_update'                         => 'walkthecounty_customer_post_update',
		'walkthecounty_donor_pre_attach_payment'                  => 'walkthecounty_customer_pre_attach_payment',
		'walkthecounty_donor_post_attach_payment'                 => 'walkthecounty_customer_post_attach_payment',
		'walkthecounty_donor_pre_remove_payment'                  => 'walkthecounty_customer_pre_remove_payment',
		'walkthecounty_donor_post_remove_payment'                 => 'walkthecounty_customer_post_remove_payment',
		'walkthecounty_donor_pre_increase_donation_count'         => 'walkthecounty_customer_pre_increase_purchase_count',
		'walkthecounty_donor_post_increase_donation_count'        => 'walkthecounty_customer_post_increase_purchase_count',
		'walkthecounty_donor_pre_decrease_donation_count'         => 'walkthecounty_customer_pre_decrease_purchase_count',
		'walkthecounty_donor_post_decrease_donation_count'        => 'walkthecounty_customer_post_decrease_purchase_count',
		'walkthecounty_donor_pre_increase_value'                  => 'walkthecounty_customer_pre_increase_value',
		'walkthecounty_donor_post_increase_value'                 => 'walkthecounty_customer_post_increase_value',
		'walkthecounty_donor_pre_decrease_value'                  => 'walkthecounty_customer_pre_decrease_value',
		'walkthecounty_donor_post_decrease_value'                 => 'walkthecounty_customer_post_decrease_value',
		'walkthecounty_donor_pre_add_note'                        => 'walkthecounty_customer_pre_add_note',
		'walkthecounty_donor_post_add_note'                       => 'walkthecounty_customer_post_add_note',
		'walkthecounty_donor_pre_add_email'                       => 'walkthecounty_customer_pre_add_email',
		'walkthecounty_donor_post_add_email'                      => 'walkthecounty_customer_post_add_email',
		'walkthecounty_donor_pre_remove_email'                    => 'walkthecounty_customer_pre_remove_email',
		'walkthecounty_donor_post_remove_email'                   => 'walkthecounty_customer_post_remove_email',
		'walkthecounty_donor_pre_set_primary_email'               => 'walkthecounty_customer_pre_set_primary_email',
		'walkthecounty_donor_post_set_primary_email'              => 'walkthecounty_customer_post_set_primary_email',
		'walkthecounty_donation_form_top'                         => 'walkthecounty_checkout_form_top',
		'walkthecounty_donation_form_bottom'                      => 'walkthecounty_checkout_form_bottom',
		'walkthecounty_donor_delete_top'                          => 'walkthecounty_customer_delete_top',
		'walkthecounty_donor_delete_bottom'                       => 'walkthecounty_customer_delete_bottom',
		'walkthecounty_donor_delete_inputs'                       => 'walkthecounty_customer_delete_inputs',
		'walkthecounty_pre_insert_donor_note'                     => 'walkthecounty_pre_insert_customer_note',
		'walkthecounty_pre_delete_donor'                          => 'walkthecounty_pre_delete_customer',
		'walkthecounty_post_add_donor_email'                      => 'walkthecounty_post_add_customer_email',
		'walkthecounty_update_edited_donation'                    => 'walkthecounty_update_edited_purchase',
		'walkthecounty_updated_edited_donation'                   => 'walkthecounty_updated_edited_purchase',
		'walkthecounty_pre_complete_donation'                     => 'walkthecounty_pre_complete_purchase',
		'walkthecounty_profile_editor_after_email'                => 'walkthecounty_profile_editor_address',
		'walkthecounty_pre_refunded_payment'                      => 'walkthecounty_pre_refund_payment',
		'walkthecounty_post_refunded_payment'                     => 'walkthecounty_post_refund_payment',
		'walkthecounty_view_donation_details_billing_before'      => 'walkthecounty_view_order_details_billing_before',
		'walkthecounty_view_donation_details_billing_after'       => 'walkthecounty_view_order_details_billing_after',
		'walkthecounty_view_donation_details_main_before'         => 'walkthecounty_view_order_details_main_before',
		'walkthecounty_view_donation_details_main_after'          => 'walkthecounty_view_order_details_main_after',
		'walkthecounty_view_donation_details_form_top'            => 'walkthecounty_view_order_details_form_top',
		'walkthecounty_view_donation_details_form_bottom'         => 'walkthecounty_view_order_details_form_bottom',
		'walkthecounty_view_donation_details_before'              => 'walkthecounty_view_order_details_before',
		'walkthecounty_view_donation_details_after'               => 'walkthecounty_view_order_details_after',
		'walkthecounty_view_donation_details_donor_before'        => 'walkthecounty_view_order_details_files_after',
		'walkthecounty_view_donation_details_sidebar_before'      => 'walkthecounty_view_order_details_sidebar_before',
		'walkthecounty_view_donation_details_sidebar_after'       => 'walkthecounty_view_order_details_sidebar_after',
		'walkthecounty_view_donation_details_totals_before'       => 'walkthecounty_view_order_details_totals_before',
		'walkthecounty_view_donation_details_totals_after'        => 'walkthecounty_view_order_details_totals_after',
		'walkthecounty_view_donation_details_update_before'       => 'walkthecounty_view_order_details_update_before',
		'walkthecounty_view_donation_details_update_after'        => 'walkthecounty_view_order_details_update_after',
		'walkthecounty_view_donation_details_payment_meta_before' => 'walkthecounty_view_order_details_payment_meta_before',
		'walkthecounty_view_donation_details_payment_meta_after'  => 'walkthecounty_view_order_details_payment_meta_after',
		'walkthecounty_view_donation_details_update_inner'        => 'walkthecounty_view_order_details_update_inner',
		'walkthecounty_donor_delete'                              => 'walkthecounty_process_donor_deletion',
		'walkthecounty_delete_donor'                              => 'walkthecounty_process_donor_deletion',
		'walkthecounty_checkout_login_fields_before'              => 'walkthecounty_donation_form_login_fields_before',
		'walkthecounty_checkout_login_fields_after'               => 'walkthecounty_donation_form_login_fields_after',
	);

	return $walkthecounty_deprecated_actions;
}

/**
 * Deprecated action mapping.
 *
 * @param mixed  $data
 * @param string $arg_1
 * @param string $arg_2
 * @param string $arg_3
 *
 * @return mixed|void
 */
function walkthecounty_deprecated_action_mapping( $data, $arg_1 = '', $arg_2 = '', $arg_3 = '' ) {
	$walkthecounty_map_deprecated_actions = walkthecounty_deprecated_actions();
	$action                      = current_filter();

	if ( isset( $walkthecounty_map_deprecated_actions[ $action ] ) ) {
		if ( has_action( $walkthecounty_map_deprecated_actions[ $action ] ) ) {
			do_action( $walkthecounty_map_deprecated_actions[ $action ], $data, $arg_1, $arg_2, $arg_3 );

			if ( ! defined( 'DOING_AJAX' ) ) {
				// translators: %s: action name.
				_walkthecounty_deprecated_function( sprintf( __( 'The %s action' ), $walkthecounty_map_deprecated_actions[ $action ] ), '1.7', $action );
			}
		}
	}
}
