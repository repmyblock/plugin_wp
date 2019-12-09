<?php
/**
 * WalkTheCounty Export Donations Functions
 */


/**
 * Return of meta keys for a donation form.
 *
 * @see http://wordpress.stackexchange.com/questions/58834/echo-all-meta-keys-of-a-custom-post-type
 */
function walkthecounty_export_donations_get_custom_fields() {
	global $wpdb;

	if( ! current_user_can( 'export_walkthecounty_reports' ) ){
		wp_send_json_error();
	}

	$post_type              = 'walkthecounty_payment';
	$responses              = array();
	$donationmeta_table_key = WalkTheCounty()->payment_meta->get_meta_type() . '_id';

	$form_id = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : '';

	if ( empty( $form_id ) ) {
		wp_send_json_error();
	}

	$args = array(
		'walkthecounty_forms'     => array( $form_id ),
		'posts_per_page' => - 1,
		'fields'         => 'ids',
	);

	$donation_list = implode( ',', (array) walkthecounty_get_payments( $args ) );

	$query_and = sprintf(
		"AND $wpdb->posts.ID IN (%s) 
		AND $wpdb->donationmeta.meta_key != '' 
		AND $wpdb->donationmeta.meta_key NOT RegExp '(^[_0-9].+$)'",
		$donation_list
	);

	$query = "
        SELECT DISTINCT($wpdb->donationmeta.meta_key) 
        FROM $wpdb->posts 
        LEFT JOIN $wpdb->donationmeta 
        ON $wpdb->posts.ID = {$wpdb->donationmeta}.{$donationmeta_table_key}
        WHERE $wpdb->posts.post_type = '%s'
    " . $query_and;

	$meta_keys = $wpdb->get_col( $wpdb->prepare( $query, $post_type ) );

	if ( ! empty( $meta_keys ) ) {
		$responses['standard_fields'] = array_values( $meta_keys );
	}

	$query_and = sprintf(
		"AND $wpdb->posts.ID IN (%s) 
		AND $wpdb->donationmeta.meta_key != '' 
		AND $wpdb->donationmeta.meta_key NOT RegExp '^[^_]'",
		$donation_list
	);

	$query = "
        SELECT DISTINCT($wpdb->donationmeta.meta_key) 
        FROM $wpdb->posts 
        LEFT JOIN $wpdb->donationmeta 
        ON $wpdb->posts.ID = {$wpdb->donationmeta}.{$donationmeta_table_key} 
        WHERE $wpdb->posts.post_type = '%s'
    " . $query_and;

	$hidden_meta_keys = $wpdb->get_col( $wpdb->prepare( $query, $post_type ) );

	/**
	 * Filter to modify hidden keys that are going to be ignore when displaying the hidden keys
	 *
	 * @param array $ignore_hidden_keys Hidden keys that are going to be ignore
	 * @param array $form_id            Donation form id
	 *
	 * @return array $ignore_hidden_keys Hidden keys that are going to be ignore
	 * @since 2.1
	 *
	 */
	$ignore_hidden_keys = apply_filters( 'walkthecounty_export_donations_ignore_hidden_keys', array(
		'_walkthecounty_payment_meta',
		'_walkthecounty_payment_gateway',
		'_walkthecounty_payment_mode',
		'_walkthecounty_payment_form_title',
		'_walkthecounty_payment_form_id',
		'_walkthecounty_payment_price_id',
		'_walkthecounty_payment_user_id',
		'_walkthecounty_payment_user_email',
		'_walkthecounty_payment_user_ip',
		'_walkthecounty_payment_customer_id',
		'_walkthecounty_payment_total',
		'_walkthecounty_completed_date',
		'_walkthecounty_donation_company',
		'_walkthecounty_donor_billing_first_name',
		'_walkthecounty_donor_billing_last_name',
		'_walkthecounty_payment_donor_email',
		'_walkthecounty_payment_donor_id',
		'_walkthecounty_payment_date',
		'_walkthecounty_donor_billing_address1',
		'_walkthecounty_donor_billing_address2',
		'_walkthecounty_donor_billing_city',
		'_walkthecounty_donor_billing_zip',
		'_walkthecounty_donor_billing_state',
		'_walkthecounty_donor_billing_country',
		'_walkthecounty_payment_import',
		'_walkthecounty_payment_currency',
		'_walkthecounty_payment_import_id',
		'_walkthecounty_payment_donor_ip',
		'_walkthecounty_payment_donor_title_prefix',
	),
		$form_id
	);

	// Unset ignored hidden keys.
	foreach ( $ignore_hidden_keys as $key ) {
		if ( ( $key = array_search( $key, $hidden_meta_keys ) ) !== false ) {
			unset( $hidden_meta_keys[ $key ] );
		}
	}

	if ( ! empty( $hidden_meta_keys ) ) {
		$responses['hidden_fields'] = array_values( $hidden_meta_keys );
	}

	/**
	 * Filter to modify custom fields when select donation forms,
	 *
	 * @param array $responses Contain all the fields that need to be display when donation form is display
	 * @param int   $form_id   Donation Form ID
	 *
	 * @return array $responses
	 * @since 2.1
	 *
	 */
	wp_send_json( (array) apply_filters( 'walkthecounty_export_donations_get_custom_fields', $responses, $form_id ) );

}

add_action( 'wp_ajax_walkthecounty_export_donations_get_custom_fields', 'walkthecounty_export_donations_get_custom_fields' );

/**
 * Register the payments batch exporter
 *
 * @since  1.0
 */
function walkthecounty_register_export_donations_batch_export() {
	add_action( 'walkthecounty_batch_export_class_include', 'walkthecounty_export_donations_include_export_class', 10, 1 );
}

add_action( 'walkthecounty_register_batch_exporter', 'walkthecounty_register_export_donations_batch_export', 10 );


/**
 * Includes the WalkTheCounty Export Donations Custom Exporter Class.
 *
 * @param $class WalkTheCounty_Export_Donations_CSV
 */
function walkthecounty_export_donations_include_export_class( $class ) {
	if ( 'WalkTheCounty_Export_Donations_CSV' === $class ) {
		require_once WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/tools/export/walkthecounty-export-donations-exporter.php';
	}
}


/**
 * Create column key.
 *
 * @param $string
 *
 * @return string
 */
function walkthecounty_export_donations_create_column_key( $string ) {
	return sanitize_key( str_replace( ' ', '_', $string ) );
}

/**
 * Filter to modify donation search form when search through AJAX
 *
 * @since 2.1
 *
 * @param $args
 *
 * @return array
 */
function walkthecounty_export_donation_form_search_args( $args ) {
	if ( empty( $_POST['fields'] ) ) {
		return $args;
	}

	$fields = isset( $_POST['fields'] ) ? $_POST['fields'] : '';
	$fields = array_map( 'walkthecounty_clean', wp_parse_args( $fields, array() ) );

	if ( ! empty( $fields['walkthecounty_forms_categories'] ) || ! empty( $fields['walkthecounty_forms_tags'] ) ) {
		$args['posts_per_page'] = - 1;
	}

	if ( ! empty( $fields['walkthecounty_forms_categories'] ) && ! empty( $fields['walkthecounty_forms_tags'] ) ) {
		$args['tax_query']['relation'] = 'AND';
	}

	if ( ! empty( $fields['walkthecounty_forms_categories'] ) ) {
		$args['tax_query'][] = array(
			'taxonomy' => 'walkthecounty_forms_category',
			'field'    => 'term_id',
			'terms'    => $fields['walkthecounty_forms_categories'],
			'operator' => 'AND',
		);
	}

	if ( ! empty( $fields['walkthecounty_forms_tags'] ) ) {
		$args['tax_query'][] = array(
			'taxonomy' => 'walkthecounty_forms_tag',
			'field'    => 'term_id',
			'terms'    => $fields['walkthecounty_forms_tags'],
			'operator' => 'AND',
		);
	}

	return $args;
}

add_filter( 'walkthecounty_ajax_form_search_args', 'walkthecounty_export_donation_form_search_args' );

/**
 * Add Donation standard fields in export donation page
 *
 * @since 2.1
 */
function walkthecounty_export_donation_standard_fields() {
	?>
	<tr>
		<td scope="row" class="row-title">
			<label><?php _e( 'Standard Columns:', 'walkthecounty' ); ?></label>
		</td>
		<td>
			<div class="walkthecounty-clearfix">
				<ul class="walkthecounty-export-option">
					<li class="walkthecounty-export-option-fields walkthecounty-export-option-payment-fields">
						<ul class="walkthecounty-export-option-payment-fields-ul">

							<li class="walkthecounty-export-option-label walkthecounty-export-option-donation-label">
								<span>
									<?php _e( 'Donation Payment Fields', 'walkthecounty' ); ?>
								</span>
							</li>

							<li class="walkthecounty-export-option-start">
								<label for="walkthecounty-export-donation-id">
									<input type="checkbox" checked
									       name="walkthecounty_walkthecounty_donations_export_option[donation_id]"
									       id="walkthecounty-export-donation-id"><?php _e( 'Donation ID', 'walkthecounty' ); ?>
								</label>
							</li>

							<?php
							if ( walkthecounty_is_setting_enabled( walkthecounty_get_option( 'sequential-ordering_status', 'disabled' ) ) ) {
								?>
								<li>
									<label for="walkthecounty-export-seq-id">
										<input type="checkbox" checked
										       name="walkthecounty_walkthecounty_donations_export_option[seq_id]"
										       id="walkthecounty-export-seq-id"><?php _e( 'Donation Number', 'walkthecounty' ); ?>
									</label>
								</li>
								<?php
							}
							?>

							<li>
								<label for="walkthecounty-export-donation-sum">
									<input type="checkbox" checked
									       name="walkthecounty_walkthecounty_donations_export_option[donation_total]"
									       id="walkthecounty-export-donation-sum"><?php _e( 'Donation Total', 'walkthecounty' ); ?>
								</label>
							</li>

							<li>
								<label for="walkthecounty-export-donation-currency_code">
									<input type="checkbox" checked
									       name="walkthecounty_walkthecounty_donations_export_option[currency_code]"
									       id="walkthecounty-export-donation-currency_code"><?php _e( 'Currency Code', 'walkthecounty' ); ?>
								</label>
							</li>

							<li>
								<label for="walkthecounty-export-donation-currency_symbol">
									<input type="checkbox" checked
									       name="walkthecounty_walkthecounty_donations_export_option[currency_symbol]"
									       id="walkthecounty-export-donation-currency_symbol"><?php _e( 'Currency Symbol', 'walkthecounty' ); ?>
								</label>
							</li>

							<li>
								<label for="walkthecounty-export-donation-status">
									<input type="checkbox" checked
									       name="walkthecounty_walkthecounty_donations_export_option[donation_status]"
									       id="walkthecounty-export-donation-status"><?php _e( 'Donation Status', 'walkthecounty' ); ?>
								</label>
							</li>

							<li>
								<label for="walkthecounty-export-donation-date">
									<input type="checkbox" checked
									       name="walkthecounty_walkthecounty_donations_export_option[donation_date]"
									       id="walkthecounty-export-donation-date"><?php _e( 'Donation Date', 'walkthecounty' ); ?>
								</label>
							</li>

							<li>
								<label for="walkthecounty-export-donation-time">
									<input type="checkbox" checked
									       name="walkthecounty_walkthecounty_donations_export_option[donation_time]"
									       id="walkthecounty-export-donation-time"><?php _e( 'Donation Time', 'walkthecounty' ); ?>
								</label>
							</li>

							<li>
								<label for="walkthecounty-export-payment-gateway">
									<input type="checkbox" checked
									       name="walkthecounty_walkthecounty_donations_export_option[payment_gateway]"
									       id="walkthecounty-export-payment-gateway"><?php _e( 'Payment Gateway', 'walkthecounty' ); ?>
								</label>
							</li>

							<li>
								<label for="walkthecounty-export-payment-mode">
									<input type="checkbox" checked
									       name="walkthecounty_walkthecounty_donations_export_option[payment_mode]"
									       id="walkthecounty-export-payment-mode"><?php _e( 'Payment Mode', 'walkthecounty' ); ?>
								</label>
							</li>

							<li>
								<label for="walkthecounty-export-donation-note-private">
									<input type="checkbox"
									       name="walkthecounty_walkthecounty_donations_export_option[donation_note_private]"
									       id="walkthecounty-export-donation-note-private"><?php _e( 'Donation Note (private)', 'walkthecounty' ); ?>
								</label>
							</li>

							<li>
								<label for="walkthecounty-export-donation-note-to-donor">
									<input type="checkbox"
									       name="walkthecounty_walkthecounty_donations_export_option[donation_note_to_donor]"
									       id="walkthecounty-export-donation-note-to-donor"><?php _e( 'Donation Note (to donor)', 'walkthecounty' ); ?>
								</label>
							</li>

							<?php
							/*
							 * Action to add extra columns in standard payment fields
							 *
							 * @since 2.1
							 */
							do_action( 'walkthecounty_export_donation_standard_payment_fields' );
							?>
						</ul>
					</li>

					<li class="walkthecounty-export-option-fields walkthecounty-export-option-form-fields">
						<ul class="walkthecounty-export-option-form-fields-ul">

							<li class="walkthecounty-export-option-label walkthecounty-export-option-Form-label">
								<span>
									<?php _e( 'Donation Form Fields', 'walkthecounty' ); ?>
								</span>
							</li>


							<li class="walkthecounty-export-option-start">
								<label for="walkthecounty-export-donation-form-id">
									<input type="checkbox" checked
									       name="walkthecounty_walkthecounty_donations_export_option[form_id]"
									       id="walkthecounty-export-donation-form-id"><?php _e( 'Donation Form ID', 'walkthecounty' ); ?>
								</label>
							</li>

							<li>
								<label for="walkthecounty-export-donation-form-title">
									<input type="checkbox" checked
									       name="walkthecounty_walkthecounty_donations_export_option[form_title]"
									       id="walkthecounty-export-donation-form-title"><?php _e( 'Donation Form Title', 'walkthecounty' ); ?>
								</label>
							</li>

							<li>
								<label for="walkthecounty-export-donation-form-level-id">
									<input type="checkbox" checked
									       name="walkthecounty_walkthecounty_donations_export_option[form_level_id]"
									       id="walkthecounty-export-donation-form-level-id"><?php _e( 'Donation Form Level ID', 'walkthecounty' ); ?>
								</label>
							</li>

							<li>
								<label for="walkthecounty-export-donation-form-level-title">
									<input type="checkbox" checked
									       name="walkthecounty_walkthecounty_donations_export_option[form_level_title]"
									       id="walkthecounty-export-donation-form-level-title"><?php _e( 'Donation Form Level Title', 'walkthecounty' ); ?>
								</label>
							</li>

							<?php
							/*
							 * Action to add extra columns in standard form fields
							 *
							 * @since 2.1
							 */
							do_action( 'walkthecounty_export_donation_standard_form_fields' );
							?>
						</ul>
					</li>

					<li class="walkthecounty-export-option-fields walkthecounty-export-option-donor-fields">
						<ul class="walkthecounty-export-option-donor-fields-ul">

							<li class="walkthecounty-export-option-label walkthecounty-export-option-donor-label">
								<span>
									<?php _e( 'Donor Fields', 'walkthecounty' ); ?>
								</span>
							</li>

							<li class="walkthecounty-export-option-start">
								<label for="walkthecounty-export-title-prefix">
									<input type="checkbox" checked
											name="walkthecounty_walkthecounty_donations_export_option[title_prefix]"
											id="walkthecounty-export-title-prefix"><?php esc_html_e( 'Donor\'s Title Prefix', 'walkthecounty' ); ?>
								</label>
							</li>

							<li class="walkthecounty-export-option-start">
								<label for="walkthecounty-export-first-name">
									<input type="checkbox" checked
									       name="walkthecounty_walkthecounty_donations_export_option[first_name]"
									       id="walkthecounty-export-first-name"><?php _e( 'Donor\'s First Name', 'walkthecounty' ); ?>
								</label>
							</li>

							<li>
								<label for="walkthecounty-export-last-name">
									<input type="checkbox" checked
									       name="walkthecounty_walkthecounty_donations_export_option[last_name]"
									       id="walkthecounty-export-last-name"><?php _e( 'Donor\'s Last Name', 'walkthecounty' ); ?>
								</label>
							</li>

							<li>
								<label for="walkthecounty-export-email">
									<input type="checkbox" checked
									       name="walkthecounty_walkthecounty_donations_export_option[email]"
									       id="walkthecounty-export-email"><?php _e( 'Donor\'s Email', 'walkthecounty' ); ?>
								</label>
							</li>

							<li>
								<label for="walkthecounty-export-company">
									<input type="checkbox" checked
									       name="walkthecounty_walkthecounty_donations_export_option[company]"
									       id="walkthecounty-export-company"><?php _e( 'Company Name', 'walkthecounty' ); ?>
								</label>
							</li>

							<li>
								<label for="walkthecounty-export-address">
									<input type="checkbox" checked
									       name="walkthecounty_walkthecounty_donations_export_option[address]"
									       id="walkthecounty-export-address"><?php _e( 'Donor\'s Billing Address', 'walkthecounty' ); ?>
								</label>
							</li>

							<li>
								<label for="walkthecounty-export-comment">
									<input type="checkbox" checked
									       name="walkthecounty_walkthecounty_donations_export_option[comment]"
									       id="walkthecounty-export-comment"><?php _e( 'Donor\'s Comment', 'walkthecounty' ); ?>
								</label>
							</li>

							<li>
								<label for="walkthecounty-export-userid">
									<input type="checkbox" checked
									       name="walkthecounty_walkthecounty_donations_export_option[userid]"
									       id="walkthecounty-export-userid"><?php _e( 'User ID', 'walkthecounty' ); ?>
								</label>
							</li>

							<li>
								<label for="walkthecounty-export-donorid">
									<input type="checkbox" checked
									       name="walkthecounty_walkthecounty_donations_export_option[donorid]"
									       id="walkthecounty-export-donorid"><?php _e( 'Donor ID', 'walkthecounty' ); ?>
								</label>
							</li>

							<li>
								<label for="walkthecounty-export-donor-ip">
									<input type="checkbox" checked
									       name="walkthecounty_walkthecounty_donations_export_option[donor_ip]"
									       id="walkthecounty-export-donor-ip"><?php _e( 'Donor IP Address', 'walkthecounty' ); ?>
								</label>
							</li>

							<?php
							/*
							 * Action to add extra columns in standard donor fields
							 *
							 * @since 2.1
							 */
							do_action( 'walkthecounty_export_donation_standard_donor_fields' );
							?>
						</ul>
					</li>

					<?php
					/**
					 * Action to add custom export column.
					 *
					 * @since 2.1.4
					 */
					do_action( 'walkthecounty_export_donation_add_custom_column' );
					?>
				</ul>
			</div>
		</td>
	</tr>
	<?php
}

add_action( 'walkthecounty_export_donation_fields', 'walkthecounty_export_donation_standard_fields', 10 );

/**
 * Add Donation Custom fields in export donation page
 *
 * @since 2.1
 */
function walkthecounty_export_donation_custom_fields() {
	?>
	<tr
		class="walkthecounty-hidden walkthecounty-export-donations-hide walkthecounty-export-donations-standard-fields">
		<td scope="row" class="row-title">
			<label><?php _e( 'Custom Field Columns:', 'walkthecounty' ); ?></label>
		</td>
		<td class="walkthecounty-field-wrap">
			<div class="walkthecounty-clearfix">
				<ul class="walkthecounty-export-option-ul"></ul>
				<p class="walkthecounty-field-description"><?php _e( 'The following fields may have been created by custom code, or another plugin.', 'walkthecounty' ); ?></p>
			</div>
		</td>
	</tr>
	<?php
}

add_action( 'walkthecounty_export_donation_fields', 'walkthecounty_export_donation_custom_fields', 30 );


/**
 * Add Donation hidden fields in export donation page
 *
 * @since 2.1
 */
function walkthecounty_export_donation_hidden_fields() {
	?>

	<tr class="walkthecounty-hidden walkthecounty-export-donations-hide walkthecounty-export-donations-hidden-fields">
		<td scope="row" class="row-title">
			<label><?php _e( 'Hidden Custom Field Columns:', 'walkthecounty' ); ?></label>
		</td>
		<td class="walkthecounty-field-wrap">
			<div class="walkthecounty-clearfix">
				<ul class="walkthecounty-export-option-ul"></ul>
				<p class="walkthecounty-field-description"><?php _e( 'The following hidden custom fields contain data created by WalkTheCountyWP Core, a WalkTheCountyWP Add-on, another plugin, etc.<br/>Hidden fields are generally used for programming logic, but you may contain data you would like to export.', 'walkthecounty' ); ?></p>
			</div>
		</td>
	</tr>
	<?php
}

add_action( 'walkthecounty_export_donation_fields', 'walkthecounty_export_donation_hidden_fields', 40 );

