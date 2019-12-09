<?php
/**
 * View Donation Details
 *
 * @package     WalkTheCounty
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'view_walkthecounty_payments' ) ) {
	wp_die(
		__( 'Sorry, you are not allowed to access this page.', 'walkthecounty' ), __( 'Error', 'walkthecounty' ), array(
			'response' => 403,
		)
	);
}

/**
 * View donation details page
 *
 * @since 1.0
 * @return void
 */
if ( ! isset( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
	wp_die( __( 'Donation ID not supplied. Please try again.', 'walkthecounty' ), __( 'Error', 'walkthecounty' ), array( 'response' => 400 ) );
}

// Setup the variables
$payment_id = absint( $_GET['id'] );
$payment    = new WalkTheCounty_Payment( $payment_id );

// Sanity check... fail if donation ID is invalid
$payment_exists = $payment->ID;
if ( empty( $payment_exists ) ) {
	wp_die( __( 'The specified ID does not belong to a donation. Please try again.', 'walkthecounty' ), __( 'Error', 'walkthecounty' ), array( 'response' => 400 ) );
}

$number       = $payment->number;
$payment_meta = $payment->get_meta();

$company_name   = ! empty( $payment_meta['_walkthecounty_donation_company'] ) ? esc_attr( $payment_meta['_walkthecounty_donation_company'] ) : '';
$transaction_id = esc_attr( $payment->transaction_id );
$user_id        = $payment->user_id;
$donor_id       = $payment->customer_id;
$payment_date   = strtotime( $payment->date );
$user_info      = walkthecounty_get_payment_meta_user_info( $payment_id );
$address        = $payment->address;
$currency_code  = $payment->currency;
$gateway        = $payment->gateway;
$currency_code  = $payment->currency;
$payment_mode   = $payment->mode;
$base_url       = admin_url( 'edit.php?post_type=walkthecounty_forms&page=walkthecounty-payment-history' );

?>
<div class="wrap walkthecounty-wrap">

	<h1 id="transaction-details-heading" class="wp-heading-inline">
		<?php
		printf(
		/* translators: %s: donation number */
			esc_html__( 'Donation %s', 'walkthecounty' ),
			$number
		);
		if ( $payment_mode == 'test' ) {
			echo WalkTheCounty()->tooltips->render_span(array(
				'label' => __( 'This donation was made in test mode.', 'walkthecounty' ),
				'tag_content' => __( 'Test Donation', 'walkthecounty' ),
				'position'=> 'right',
				'attributes' => array(
					'id' => 'test-payment-label',
					'class' => 'walkthecounty-item-label walkthecounty-item-label-orange'
				)
			));
		}
		?>
	</h1>

	<?php
	/**
	 * Fires in donation details page, before the page content and after the H1 title output.
	 *
	 * @since 1.0
	 *
	 * @param int $payment_id Payment id.
	 */
	do_action( 'walkthecounty_view_donation_details_before', $payment_id );
	?>

	<hr class="wp-header-end">

	<form id="walkthecounty-edit-order-form" method="post">
		<?php
		/**
		 * Fires in donation details page, in the form before the order details.
		 *
		 * @since 1.0
		 *
		 * @param int $payment_id Payment id.
		 */
		do_action( 'walkthecounty_view_donation_details_form_top', $payment_id );
		?>
		<div id="poststuff" class="walkthecounty-clearfix">
			<div id="walkthecounty-dashboard-widgets-wrap">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="postbox-container-1" class="postbox-container">
						<div id="side-sortables" class="meta-box-sortables ui-sortable">

							<?php
							/**
							 * Fires in donation details page, before the sidebar.
							 *
							 * @since 1.0
							 *
							 * @param int $payment_id Payment id.
							 */
							do_action( 'walkthecounty_view_donation_details_sidebar_before', $payment_id );
							?>

							<div id="walkthecounty-order-update" class="postbox walkthecounty-order-data">

								<div class="walkthecounty-order-top">
									<h3 class="hndle"><?php _e( 'Update Donation', 'walkthecounty' ); ?></h3>

									<?php
									if ( current_user_can( 'view_walkthecounty_payments' ) ) {
										echo sprintf(
											'<span class="delete-donation" id="delete-donation-%d"><a class="delete-single-donation delete-donation-button dashicons dashicons-trash" href="%s" aria-label="%s"></a></span>',
											$payment_id,
											wp_nonce_url(
												add_query_arg(
													array(
														'walkthecounty-action' => 'delete_payment',
														'purchase_id' => $payment_id,
													), $base_url
												), 'walkthecounty_donation_nonce'
											),
											sprintf( __( 'Delete Donation %s', 'walkthecounty' ), $payment_id )
										);
									}
									?>
								</div>

								<div class="inside">
									<div class="walkthecounty-admin-box">

										<?php
										/**
										 * Fires in donation details page, before the sidebar update-payment metabox.
										 *
										 * @since 1.0
										 *
										 * @param int $payment_id Payment id.
										 */
										do_action( 'walkthecounty_view_donation_details_totals_before', $payment_id );
										?>

										<div class="walkthecounty-admin-box-inside">
											<p>
												<label for="walkthecounty-payment-status" class="strong"><?php _e( 'Status:', 'walkthecounty' ); ?></label>&nbsp;
												<select id="walkthecounty-payment-status" name="walkthecounty-payment-status" class="medium-text">
													<?php foreach ( walkthecounty_get_payment_statuses() as $key => $status ) : ?>
														<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $payment->status, $key, true ); ?>><?php echo esc_html( $status ); ?></option>
													<?php endforeach; ?>
												</select>
												<span class="walkthecounty-donation-status status-<?php echo sanitize_title( $payment->status ); ?>"><span class="walkthecounty-donation-status-icon"></span></span>
											</p>
										</div>

										<div class="walkthecounty-admin-box-inside">
											<?php $date_format = walkthecounty_date_format(); ?>
											<p>
												<label for="walkthecounty-payment-date" class="strong"><?php _e( 'Date:', 'walkthecounty' ); ?></label>&nbsp;
												<input type="text" id="walkthecounty-payment-date" name="walkthecounty-payment-date" data-standard-date="<?php echo esc_attr( date( 'Y-m-d', $payment_date ) ); ?>" value="<?php echo esc_attr( date_i18n( $date_format, $payment_date ) ); ?>" autocomplete="off" class="medium-text walkthecounty_datepicker" placeholder="<?php _e( 'Date', 'walkthecounty' ); ?>"/>
											</p>
										</div>

										<div class="walkthecounty-admin-box-inside">
											<p>
												<label for="walkthecounty-payment-time-hour" class="strong"><?php _e( 'Time:', 'walkthecounty' ); ?></label>&nbsp;
												<input type="number" step="1" max="24" id="walkthecounty-payment-time-hour" name="walkthecounty-payment-time-hour" value="<?php echo esc_attr( date_i18n( 'H', $payment_date ) ); ?>" class="small-text walkthecounty-payment-time-hour"/>&nbsp;:&nbsp;
												<input type="number" step="1" max="59" id="walkthecounty-payment-time-min" name="walkthecounty-payment-time-min" value="<?php echo esc_attr( date( 'i', $payment_date ) ); ?>" class="small-text walkthecounty-payment-time-min"/>
											</p>
										</div>

										<?php
										/**
										 * Fires in donation details page, in the sidebar update-payment metabox.
										 *
										 * Allows you to add new inner items.
										 *
										 * @since 1.0
										 *
										 * @param int $payment_id Payment id.
										 */
										do_action( 'walkthecounty_view_donation_details_update_inner', $payment_id );
										?>

										<div class="walkthecounty-order-payment walkthecounty-admin-box-inside">
											<p>
												<label for="walkthecounty-payment-total" class="strong"><?php _e( 'Total Donation:', 'walkthecounty' ); ?></label>&nbsp;
												<?php echo walkthecounty_currency_symbol( $payment->currency ); ?>
												&nbsp;<input id="walkthecounty-payment-total" name="walkthecounty-payment-total" type="text" class="small-text walkthecounty-price-field" value="<?php echo esc_attr( walkthecounty_format_decimal( array( 'donation_id' => $payment_id ) ) ); ?>"/>
											</p>
										</div>

										<?php
										/**
										 * Fires in donation details page, after the sidebar update-donation metabox.
										 *
										 * @since 1.0
										 *
										 * @param int $payment_id Payment id.
										 */
										do_action( 'walkthecounty_view_donation_details_totals_after', $payment_id );
										?>

									</div>
									<!-- /.walkthecounty-admin-box -->

								</div>
								<!-- /.inside -->

								<div class="walkthecounty-order-update-box walkthecounty-admin-box">
									<?php
									/**
									 * Fires in donation details page, before the sidebar update-payment metabox actions buttons.
									 *
									 * @since 1.0
									 *
									 * @param int $payment_id Payment id.
									 */
									do_action( 'walkthecounty_view_donation_details_update_before', $payment_id );
									?>

									<div id="major-publishing-actions">
										<div id="publishing-action">
											<input type="submit" class="button button-primary right" value="<?php esc_attr_e( 'Save Donation', 'walkthecounty' ); ?>"/>
											<?php
											if ( walkthecounty_is_payment_complete( $payment_id ) ) {
												$url = add_query_arg(
													array(
														'walkthecounty-action' => 'email_links',
														'purchase_id' => $payment_id,
													),
													admin_url( 'edit.php?post_type=walkthecounty_forms&page=walkthecounty-payment-history&view=view-payment-details&id=' . $payment_id )
												);

												echo sprintf(
													'<a href="%1$s" id="walkthecounty-resend-receipt" class="button-secondary right">%2$s</a>',
													esc_url( $url ),
													esc_html__( 'Resend Receipt', 'walkthecounty' )
												);
											}
											?>
										</div>
										<div class="clear"></div>
									</div>
									<?php
									/**
									 * Fires in donation details page, after the sidebar update-payment metabox actions buttons.
									 *
									 * @since 1.0
									 *
									 * @param int $payment_id Payment id.
									 */
									do_action( 'walkthecounty_view_donation_details_update_after', $payment_id );
									?>

								</div>
								<!-- /.walkthecounty-order-update-box -->

							</div>
							<!-- /#walkthecounty-order-data -->

							<div id="walkthecounty-order-details" class="postbox walkthecounty-order-data">

								<h3 class="hndle"><?php _e( 'Donation Meta', 'walkthecounty' ); ?></h3>

								<div class="inside">
									<div class="walkthecounty-admin-box">

										<?php
										/**
										 * Fires in donation details page, before the donation-meta metabox.
										 *
										 * @since 1.0
										 *
										 * @param int $payment_id Payment id.
										 */
										do_action( 'walkthecounty_view_donation_details_payment_meta_before', $payment_id );

										$gateway = walkthecounty_get_payment_gateway( $payment_id );
										if ( $gateway ) :
											?>
											<div class="walkthecounty-order-gateway walkthecounty-admin-box-inside">
												<p>
													<strong><?php _e( 'Gateway:', 'walkthecounty' ); ?></strong>&nbsp;
													<?php echo walkthecounty_get_gateway_admin_label( $gateway ); ?>
												</p>
											</div>
										<?php endif; ?>

										<div class="walkthecounty-order-payment-key walkthecounty-admin-box-inside">
											<p>
												<strong><?php _e( 'Key:', 'walkthecounty' ); ?></strong>&nbsp;
												<?php echo walkthecounty_get_payment_key( $payment_id ); ?>
											</p>
										</div>

										<div class="walkthecounty-order-ip walkthecounty-admin-box-inside">
											<p>
												<strong><?php _e( 'IP:', 'walkthecounty' ); ?></strong>&nbsp;
												<?php echo esc_html( walkthecounty_get_payment_user_ip( $payment_id ) ); ?>
											</p>
										</div>

										<?php
										// Display the transaction ID present.
										// The transaction ID is the charge ID from the gateway.
										// For instance, stripe "ch_BzvwYCchqOy5Nt".
										if ( $transaction_id != $payment_id ) : ?>
											<div class="walkthecounty-order-tx-id walkthecounty-admin-box-inside">
												<p>
													<strong><?php _e( 'Transaction ID:', 'walkthecounty' ); ?> <span class="walkthecounty-tooltip walkthecounty-icon walkthecounty-icon-question"  data-tooltip="<?php echo sprintf( esc_attr__( 'The transaction ID within %s.', 'walkthecounty' ), $gateway); ?>"></span></strong>&nbsp;
													<?php echo apply_filters( "walkthecounty_payment_details_transaction_id-{$gateway}", $transaction_id, $payment_id ); ?>
												</p>
											</div>
										<?php endif; ?>

										<?php
										/**
										 * Fires in donation details page, after the donation-meta metabox.
										 *
										 * @since 1.0
										 *
										 * @param int $payment_id Payment id.
										 */
										do_action( 'walkthecounty_view_donation_details_payment_meta_after', $payment_id );
										?>

										<div class="walkthecounty-admin-box-inside">
											<p><?php $purchase_url = admin_url( 'edit.php?post_type=walkthecounty_forms&page=walkthecounty-payment-history&donor=' . absint( walkthecounty_get_payment_donor_id( $payment_id ) ) ); ?>
												<a href="<?php echo $purchase_url; ?>"><?php _e( 'View all donations for this donor &raquo;', 'walkthecounty' ); ?></a>
											</p>
										</div>
										
									</div>
									<!-- /.column-container -->

								</div>
								<!-- /.inside -->

							</div>
							<!-- /#walkthecounty-order-data -->

							<?php
							/**
							 * Fires in donation details page, after the sidebar.
							 *
							 * @since 1.0
							 *
							 * @param int $payment_id Payment id.
							 */
							do_action( 'walkthecounty_view_donation_details_sidebar_after', $payment_id );
							?>

						</div>
						<!-- /#side-sortables -->
					</div>
					<!-- /#postbox-container-1 -->

					<div id="postbox-container-2" class="postbox-container">

						<div id="normal-sortables" class="meta-box-sortables ui-sortable">

							<?php
							/**
							 * Fires in donation details page, before the main area.
							 *
							 * @since 1.0
							 *
							 * @param int $payment_id Payment id.
							 */
							do_action( 'walkthecounty_view_donation_details_main_before', $payment_id );
							?>

							<?php $column_count = 'columns-3'; ?>
							<div id="walkthecounty-donation-overview" class="postbox <?php echo $column_count; ?>">
								<h3 class="hndle"><?php _e( 'Donation Information', 'walkthecounty' ); ?></h3>

								<div class="inside">

									<div class="column-container">
										<div class="column">
											<p>
												<strong><?php _e( 'Donation Form ID:', 'walkthecounty' ); ?></strong><br>
												<?php
												if ( $payment->form_id ) :
													printf(
														'<a href="%1$s">%2$s</a>',
														admin_url( 'post.php?action=edit&post=' . $payment->form_id ),
														$payment->form_id
													);
												endif;
												?>
											</p>
											<p>
												<strong><?php esc_html_e( 'Donation Form Title:', 'walkthecounty' ); ?></strong><br>
												<?php
												echo WalkTheCounty()->html->forms_dropdown(
													array(
														'selected' => $payment->form_id,
														'name' => 'walkthecounty-payment-form-select',
														'id'   => 'walkthecounty-payment-form-select',
														'chosen' => true,
														'placeholder' => '',
													)
												);
												?>
											</p>
										</div>
										<div class="column">
											<p>
												<strong><?php _e( 'Donation Date:', 'walkthecounty' ); ?></strong><br>
												<?php echo date_i18n( walkthecounty_date_format(), $payment_date ); ?>
											</p>
											<p>
												<strong><?php _e( 'Donation Level:', 'walkthecounty' ); ?></strong><br>
												<span class="walkthecounty-donation-level">
													<?php
													$var_prices = walkthecounty_has_variable_prices( $payment->form_id );
													if ( empty( $var_prices ) ) {
														_e( 'n/a', 'walkthecounty' );
													} else {
														$prices_atts = array();
														if ( $variable_prices = walkthecounty_get_variable_prices( $payment->form_id ) ) {
															foreach ( $variable_prices as $variable_price ) {
																$prices_atts[ $variable_price['_walkthecounty_id']['level_id'] ] = walkthecounty_format_amount( $variable_price['_walkthecounty_amount'], array( 'sanitize' => false ) );
															}
														}
														// Variable price dropdown options.
														$variable_price_dropdown_option = array(
															'id'               => $payment->form_id,
															'name'             => 'walkthecounty-variable-price',
															'chosen'           => true,
															'show_option_all'  => '',
															'show_option_none' => ( '' === $payment->price_id ? __( 'None', 'walkthecounty' ) : '' ),
															'select_atts'      => 'data-prices=' . esc_attr( wp_json_encode( $prices_atts ) ),
															'selected'         => $payment->price_id,
														);
														// Render variable prices select tag html.
														walkthecounty_get_form_variable_price_dropdown( $variable_price_dropdown_option, true );
													}
													?>
												</span>
											</p>
										</div>
										<div class="column">
											<p>
												<strong><?php esc_html_e( 'Total Donation:', 'walkthecounty' ); ?></strong><br>
												<?php echo walkthecounty_donation_amount( $payment, true ); ?>
											</p>

											<?php if ( walkthecounty_is_anonymous_donation_field_enabled( $payment->form_id ) ):  ?>
												<div>
													<strong><?php esc_html_e( 'Anonymous Donation:', 'walkthecounty' ); ?></strong>
													<ul class="walkthecounty-radio-inline">
														<li>
															<label>
																<input
																	name="walkthecounty_anonymous_donation"
																	value="1"
																	type="radio"
																	<?php checked( 1, absint( walkthecounty_get_meta( $payment_id, '_walkthecounty_anonymous_donation', true ) ) ) ?>
																><?php _e( 'Yes', 'walkthecounty' ); ?>
															</label>
														</li>
														<li>
															<label>
																<input
																	name="walkthecounty_anonymous_donation"
																	value="0"
																	type="radio"
																	<?php checked( 0, absint( walkthecounty_get_meta( $payment_id, '_walkthecounty_anonymous_donation', true ) ) ) ?>
																><?php _e( 'No', 'walkthecounty' ); ?>
															</label>
														</li>
													</ul>
												</div>
											<?php endif; ?>
											<p>
												<?php
												/**
												 * Fires in donation details page, in the donation-information metabox, before the head elements.
												 *
												 * Allows you to add new TH elements at the beginning.
												 *
												 * @since 1.0
												 *
												 * @param int $payment_id Payment id.
												 */
												do_action( 'walkthecounty_donation_details_thead_before', $payment_id );


												/**
												 * Fires in donation details page, in the donation-information metabox, after the head elements.
												 *
												 * Allows you to add new TH elements at the end.
												 *
												 * @since 1.0
												 *
												 * @param int $payment_id Payment id.
												 */
												do_action( 'walkthecounty_donation_details_thead_after', $payment_id );

												/**
												 * Fires in donation details page, in the donation-information metabox, before the body elements.
												 *
												 * Allows you to add new TD elements at the beginning.
												 *
												 * @since 1.0
												 *
												 * @param int $payment_id Payment id.
												 */
												do_action( 'walkthecounty_donation_details_tbody_before', $payment_id );

												/**
												 * Fires in donation details page, in the donation-information metabox, after the body elements.
												 *
												 * Allows you to add new TD elements at the end.
												 *
												 * @since 1.0
												 *
												 * @param int $payment_id Payment id.
												 */
												do_action( 'walkthecounty_donation_details_tbody_after', $payment_id );
												?>
											</p>
										</div>
									</div>

								</div>
								<!-- /.inside -->

							</div>
							<!-- /#walkthecounty-donation-overview -->

							<?php
							/**
							 * Fires on the donation details page.
							 *
							 * @since 1.0
							 *
							 * @param int $payment_id Payment id.
							 */
							do_action( 'walkthecounty_view_donation_details_donor_detail_before', $payment_id );
							?>

							<div id="walkthecounty-donor-details" class="postbox">
								<h3 class="hndle"><?php _e( 'Donor Details', 'walkthecounty' ); ?></h3>

								<div class="inside">

									<?php $donor = new WalkTheCounty_Donor( $donor_id ); ?>

									<div class="column-container donor-info">
										<div class="column">
											<p>
												<strong><?php esc_html_e( 'Donor ID:', 'walkthecounty' ); ?></strong><br>
												<?php
												if ( ! empty( $donor->id ) ) {
													printf(
														'<a href="%1$s">%2$s</a>',
														esc_url( admin_url( 'edit.php?post_type=walkthecounty_forms&page=walkthecounty-donors&view=overview&id=' . $donor->id ) ),
														intval( $donor->id )
													);
												}
												?>
												<span>(<a href="#new" class="walkthecounty-payment-new-donor"><?php esc_html_e( 'Create New Donor', 'walkthecounty' ); ?></a>)</span>
											</p>
											<p>
												<strong><?php esc_html_e( 'Donor Since:', 'walkthecounty' ); ?></strong><br>
												<?php echo date_i18n( walkthecounty_date_format(), strtotime( $donor->date_created ) ) ?>
											</p>
										</div>
										<div class="column">
											<p>
												<strong><?php esc_html_e( 'Donor Name:', 'walkthecounty' ); ?></strong><br>
												<?php
												$donor_billing_name = walkthecounty_get_donor_name_by( $payment_id, 'donation' );
												$donor_name         = walkthecounty_get_donor_name_by( $donor_id, 'donor' );

												// Check whether the donor name and WP_User name is same or not.
												if ( $donor_billing_name !== $donor_name ) {
													echo sprintf(
														'%1$s (<a href="%2$s" target="_blank">%3$s</a>)',
														esc_html( $donor_billing_name ),
														esc_url( admin_url( "edit.php?post_type=walkthecounty_forms&page=walkthecounty-donors&view=overview&id={$donor_id}" ) ),
														esc_html( $donor_name )
													);
												} else {
													echo esc_html( $donor_name );
												}
												?>
											</p>
											<p>
												<strong><?php esc_html_e( 'Donor Email:', 'walkthecounty' ); ?></strong><br>
												<?php
												// Show Donor donation email first and Primary email on parenthesis if not match both email.
												echo hash_equals( $donor->email, $payment->email )
													? $payment->email
													: sprintf(
														'%1$s (<a href="%2$s" target="_blank">%3$s</a>)',
														$payment->email,
														esc_url( admin_url( "edit.php?post_type=walkthecounty_forms&page=walkthecounty-donors&view=overview&id={$donor_id}" ) ),
														$donor->email
													);
												?>
											</p>
										</div>
										<div class="column">
											<p>
												<strong><?php esc_html_e( 'Change Donor:', 'walkthecounty' ); ?></strong><br>
												<?php
												echo WalkTheCounty()->html->donor_dropdown(
													array(
														'selected' => $donor->id,
														'name' => 'donor-id',
													)
												);
												?>
											</p>
											<p>
												<?php if ( ! empty( $company_name ) ) {
													?>
													<strong><?php esc_html_e( 'Company Name:', 'walkthecounty' ); ?></strong><br>
													<?php
													echo $company_name;
												} ?>
											</p>
										</div>
									</div>

									<div class="column-container new-donor" style="display: none">
										<div class="column">
											<p>
												<label for="walkthecounty-new-donor-first-name"><?php _e( 'New Donor First Name:', 'walkthecounty' ); ?></label>
												<input id="walkthecounty-new-donor-first-name" type="text" name="walkthecounty-new-donor-first-name" value="" class="medium-text"/>
											</p>
										</div>
										<div class="column">
											<p>
												<label for="walkthecounty-new-donor-last-name"><?php _e( 'New Donor Last Name:', 'walkthecounty' ); ?></label>
												<input id="walkthecounty-new-donor-last-name" type="text" name="walkthecounty-new-donor-last-name" value="" class="medium-text"/>
											</p>
										</div>
										<div class="column">
											<p>
												<label for="walkthecounty-new-donor-email"><?php _e( 'New Donor Email:', 'walkthecounty' ); ?></label>
												<input id="walkthecounty-new-donor-email" type="email" name="walkthecounty-new-donor-email" value="" class="medium-text"/>
											</p>
										</div>
										<div class="column">
											<p>
												<input type="hidden" name="walkthecounty-current-donor" value="<?php echo $donor->id; ?>"/>
												<input type="hidden" id="walkthecounty-new-donor" name="walkthecounty-new-donor" value="0"/>
												<a href="#cancel" class="walkthecounty-payment-new-donor-cancel walkthecounty-delete"><?php _e( 'Cancel', 'walkthecounty' ); ?></a>
												<br>
												<em><?php _e( 'Click "Save Donation" to create new donor.', 'walkthecounty' ); ?></em>
											</p>
										</div>
									</div>
									<?php
									/**
									 * Fires on the donation details page, in the donor-details metabox.
									 *
									 * The hook is left here for backwards compatibility.
									 *
									 * @since 1.7
									 *
									 * @param array $payment_meta Payment meta.
									 * @param array $user_info    User information.
									 */
									do_action( 'walkthecounty_payment_personal_details_list', $payment_meta, $user_info );

									/**
									 * Fires on the donation details page, in the donor-details metabox.
									 *
									 * @since 1.7
									 *
									 * @param int $payment_id Payment id.
									 */
									do_action( 'walkthecounty_payment_view_details', $payment_id );
									?>

								</div>
								<!-- /.inside -->
							</div>
							<!-- /#walkthecounty-donor-details -->

							<?php
							/**
							 * Fires on the donation details page, before the billing metabox.
							 *
							 * @since 1.0
							 *
							 * @param int $payment_id Payment id.
							 */
							do_action( 'walkthecounty_view_donation_details_billing_before', $payment_id );
							?>

							<div id="walkthecounty-billing-details" class="postbox">
								<h3 class="hndle"><?php _e( 'Billing Address', 'walkthecounty' ); ?></h3>

								<div class="inside">

									<div id="walkthecounty-order-address">

										<div class="order-data-address">
											<div class="data column-container">

												<?php
												$address['country'] = ( ! empty( $address['country'] ) ? $address['country'] : walkthecounty_get_country() );

												$address['state'] = ( ! empty( $address['state'] ) ? $address['state'] : '' );

												// Get the country list that does not have any states init.
												$no_states_country = walkthecounty_no_states_country_list();
												?>

												<div class="row">
													<div id="walkthecounty-order-address-country-wrap">
														<label class="order-data-address-line"><?php _e( 'Country:', 'walkthecounty' ); ?></label>
														<?php
														echo WalkTheCounty()->html->select(
															array(
																'options'          => walkthecounty_get_country_list(),
																'name'             => 'walkthecounty-payment-address[0][country]',
																'selected'         => $address['country'],
																'show_option_all'  => false,
																'show_option_none' => false,
																'chosen'           => true,
																'placeholder'      => esc_attr__( 'Select a country', 'walkthecounty' ),
																'data'             => array( 'search-type' => 'no_ajax' ),
																'autocomplete'     => 'country',
															)
														);
														?>
													</div>
												</div>

												<div class="row">
													<div class="walkthecounty-wrap-address-line1">
														<label for="walkthecounty-payment-address-line1" class="order-data-address"><?php _e( 'Address 1:', 'walkthecounty' ); ?></label>
														<input id="walkthecounty-payment-address-line1" type="text" name="walkthecounty-payment-address[0][line1]" value="<?php echo esc_attr( $address['line1'] ); ?>" class="medium-text"/>
													</div>
												</div>

												<div class="row">
													<div class="walkthecounty-wrap-address-line2">
														<label for="walkthecounty-payment-address-line2" class="order-data-address-line"><?php _e( 'Address 2:', 'walkthecounty' ); ?></label>
														<input id="walkthecounty-payment-address-line2" type="text" name="walkthecounty-payment-address[0][line2]" value="<?php echo esc_attr( $address['line2'] ); ?>" class="medium-text"/>
													</div>
												</div>

												<div class="row">
													<div class="walkthecounty-wrap-address-city">
														<label for="walkthecounty-payment-address-city" class="order-data-address-line"><?php esc_html_e( 'City:', 'walkthecounty' ); ?></label>
														<input id="walkthecounty-payment-address-city" type="text" name="walkthecounty-payment-address[0][city]" value="<?php echo esc_attr( $address['city'] ); ?>" class="medium-text"/>
													</div>
												</div>

												<?php
												$state_exists = ( ! empty( $address['country'] ) && array_key_exists( $address['country'], $no_states_country ) ? true : false );
												?>
												<div class="row">
													<div class="<?php echo( ! empty( $state_exists ) ? 'column-full' : 'column' ); ?> walkthecounty-column walkthecounty-column-state">
														<div id="walkthecounty-order-address-state-wrap" class="<?php echo( ! empty( $state_exists ) ? 'walkthecounty-hidden' : '' ); ?>">
															<label for="walkthecounty-payment-address-state" class="order-data-address-line"><?php esc_html_e( 'State / Province / County:', 'walkthecounty' ); ?></label>
															<?php
															$states = walkthecounty_get_states( $address['country'] );
															if ( ! empty( $states ) ) {
																echo WalkTheCounty()->html->select(
																	array(
																		'options'          => $states,
																		'name'             => 'walkthecounty-payment-address[0][state]',
																		'selected'         => $address['state'],
																		'show_option_all'  => false,
																		'show_option_none' => false,
																		'chosen'           => true,
																		'placeholder'      => esc_attr__( 'Select a state', 'walkthecounty' ),
																		'data'             => array( 'search-type' => 'no_ajax' ),
																		'autocomplete' => 'address-level1',
																	)
																);
															} else {
																?>
																<input id="walkthecounty-payment-address-state" type="text" name="walkthecounty-payment-address[0][state]" autocomplete="address-line1" value="<?php echo esc_attr( $address['state'] ); ?>" class="medium-text"/>
																<?php
															}
															?>
														</div>
													</div>

													<div class="<?php echo( ! empty( $state_exists ) ? 'column-full' : 'column' ); ?> walkthecounty-column walkthecounty-column-zip">
														<div class="walkthecounty-wrap-address-zip">
															<label for="walkthecounty-payment-address-zip" class="order-data-address-line"><?php _e( 'Zip / Postal Code:', 'walkthecounty' ); ?></label>
															<input id="walkthecounty-payment-address-zip" type="text" name="walkthecounty-payment-address[0][zip]" value="<?php echo esc_attr( $address['zip'] ); ?>" class="medium-text"/>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<!-- /#walkthecounty-order-address -->

									<?php
									/**
									 * Fires in donation details page, in the billing metabox, after all the fields.
									 *
									 * Allows you to insert new billing address fields.
									 *
									 * @since 1.7
									 *
									 * @param int $payment_id Payment id.
									 */
									do_action( 'walkthecounty_payment_billing_details', $payment_id );
									?>

								</div>
								<!-- /.inside -->
							</div>
							<!-- /#walkthecounty-billing-details -->

							<?php
							/**
							 * Fires on the donation details page, after the billing metabox.
							 *
							 * @since 1.0
							 *
							 * @param int $payment_id Payment id.
							 */
							do_action( 'walkthecounty_view_donation_details_billing_after', $payment_id );
							?>

							<div id="walkthecounty-payment-notes" class="postbox">
								<h3 class="hndle"><?php _e( 'Donation Notes', 'walkthecounty' ); ?></h3>

								<div class="inside">
									<div id="walkthecounty-payment-notes-inner">
										<?php
										$notes = walkthecounty_get_payment_notes( $payment_id );
										if ( ! empty( $notes ) ) {
											$no_notes_display = ' style="display:none;"';
											foreach ( $notes as $note ) :

												echo walkthecounty_get_payment_note_html( $note, $payment_id );

											endforeach;
										} else {
											$no_notes_display = '';
										}

										echo '<p class="walkthecounty-no-payment-notes"' . $no_notes_display . '>' . esc_html__( 'No donation notes.', 'walkthecounty' ) . '</p>';
										?>
									</div>
									<textarea name="walkthecounty-payment-note" id="walkthecounty-payment-note" class="large-text"></textarea>

									<div class="walkthecounty-clearfix">
										<p>
											<label for="donation_note_type" class="screen-reader-text"><?php _e( 'Note type', 'walkthecounty' ); ?></label>
											<select name="donation_note_type" id="donation_note_type">
												<option value=""><?php _e( 'Private note', 'walkthecounty' ); ?></option>
												<option value="donor"><?php _e( 'Note to donor', 'walkthecounty' ); ?></option>
											</select>
											<button id="walkthecounty-add-payment-note" class="button button-secondary button-small" data-payment-id="<?php echo absint( $payment_id ); ?>"><?php _e( 'Add Note', 'walkthecounty' ); ?></button>
										</p>
									</div>

								</div>
								<!-- /.inside -->
							</div>
							<!-- /#walkthecounty-payment-notes -->

							<?php
							/**
							 * Fires on the donation details page, after the main area.
							 *
							 * @since 1.0
							 *
							 * @param int $payment_id Payment id.
							 */
							do_action( 'walkthecounty_view_donation_details_main_after', $payment_id );
							?>

							<?php if ( walkthecounty_is_donor_comment_field_enabled( $payment->form_id ) ) : ?>
								<div id="walkthecounty-payment-donor-comment" class="postbox">
									<h3 class="hndle"><?php _e( 'Donor Comment', 'walkthecounty' ); ?></h3>

									<div class="inside">
										<div id="walkthecounty-payment-donor-comment-inner">
											<p>
												<?php
												$donor_comment = walkthecounty_get_donor_donation_comment( $payment_id, $payment->donor_id );

												echo sprintf(
													'<input type="hidden" name="walkthecounty_comment_id" value="%s">',
													$donor_comment instanceof WP_Comment // Backward compatibility.
														|| $donor_comment instanceof stdClass
															? $donor_comment->comment_ID : 0
												);

												echo sprintf(
													'<textarea name="walkthecounty_comment" id="walkthecounty_comment" placeholder="%s" class="large-text">%s</textarea>',
													__( 'Add a comment', 'walkthecounty' ),
													$donor_comment instanceof WP_Comment // Backward compatibility.
													|| $donor_comment instanceof stdClass
														? $donor_comment->comment_content : ''
												);
												?>
											</p>
										</div>

									</div>
									<!-- /.inside -->
								</div>
							<?php endif; ?>
							<!-- /#walkthecounty-payment-notes -->

							<?php
							/**
							 * Fires on the donation details page, after the main area.
							 *
							 * @since 1.0
							 *
							 * @param int $payment_id Payment id.
							 */
							do_action( 'walkthecounty_view_donation_details_main_after', $payment_id );
							?>

						</div>
						<!-- /#normal-sortables -->
					</div>
					<!-- #postbox-container-2 -->
				</div>
				<!-- /#post-body -->
			</div>
			<!-- #walkthecounty-dashboard-widgets-wrap -->
		</div>
		<!-- /#post-stuff -->

		<?php
		/**
		 * Fires in donation details page, in the form after the order details.
		 *
		 * @since 1.0
		 *
		 * @param int $payment_id Payment id.
		 */
		do_action( 'walkthecounty_view_donation_details_form_bottom', $payment_id );

		wp_nonce_field( 'walkthecounty_update_payment_details_nonce' );
		?>
		<input type="hidden" name="walkthecounty_payment_id" value="<?php echo esc_attr( $payment_id ); ?>"/>
		<input type="hidden" name="walkthecounty_action" value="update_payment_details"/>
	</form>
	<?php
	/**
	 * Fires in donation details page, after the order form.
	 *
	 * @since 1.0
	 *
	 * @param int $payment_id Payment id.
	 */
	do_action( 'walkthecounty_view_donation_details_after', $payment_id );
	?>
</div><!-- /.wrap -->
