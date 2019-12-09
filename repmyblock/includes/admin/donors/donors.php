<?php
/**
 * Donors.
 *
 * @package     WalkTheCounty
 * @subpackage  Admin/Donors
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Get formatted address
 *
 * @since 2.0
 *
 * @param array $address
 * @param array $address_args
 *
 * @return string
 */
function __walkthecounty_get_format_address( $address, $address_args = array() ) {
	$address_html = '';
	$address_args = wp_parse_args(
		$address_args,
		array(
			'type'            => '',
			'id'              => null,
			'index'           => null,
			'default_address' => false,
		)
	);

	$address_id = $address_args['type'];

	// Bailout.
	if ( empty( $address ) || ! is_array( $address ) ) {
		return $address_html;
	}

	// Address html.
	$address_html = '';
	$address_html .= sprintf(
		'<span data-address-type="line1">%1$s</span>%2$s',
		$address['line1'],
		( ! empty( $address['line2'] ) ? '<br>' : '' )
	);
	$address_html .= sprintf(
		'<span data-address-type="line2">%1$s</span>%2$s',
		$address['line2'],
		( ! empty( $address['city'] ) ? '<br>' : '' )
	);
	$address_html .= sprintf(
		'<span data-address-type="city">%1$s</span><span data-address-type="state">%2$s</span><span data-address-type="zip">%3$s</span>%4$s',
		$address['city'],
		( ! empty( $address['state'] ) ? ", {$address['state']}" : '' ),
		( ! empty( $address['zip'] ) ? " {$address['zip']}" : '' ),
		( ! empty( $address['country'] ) ? '<br>' : '' )
	);
	$address_html .= sprintf(
		'<span data-address-type="country">%s</span><br>',
		$address['country']
	);

	// Address action.
	$address_html .= sprintf(
		'<br><a href="#" class="js-edit">%1$s</a> | <a href="#" class="js-remove">%2$s</a>',
		__( 'Edit', 'walkthecounty' ),
		__( 'Remove', 'walkthecounty' )
	);

	/**
	 * Filter the address label
	 *
	 * @since 2.0
	 */
	$address_label = apply_filters( "walkthecounty_donor_{$address_args['type']}_address_label", ucfirst( $address_args['type'] ), $address_args );

	// Set unique id and index for multi type address.
	if ( isset( $address_args['index'] ) ) {
		$address_label = "{$address_label} #{$address_args['index']}";
	}

	if ( isset( $address_args['id'] ) ) {
		$address_id = "{$address_id}_{$address_args['id']}";
	}

	// Add address wrapper.
	$address_html = sprintf(
		'<div class="walkthecounty-grid-col-4"><div data-address-id="%s" class="address"><span class="alignright address-number-label">%s</span>%s</div></div>',
		$address_id,
		$address_label,
		$address_html
	);

	return $address_html;
}

/**
 * Donors Page.
 *
 * Renders the donors page contents.
 *
 * @since  1.0
 * @return void
 */
function walkthecounty_donors_page() {
	$default_views  = walkthecounty_donor_views();
	$requested_view = isset( $_GET['view'] ) ? sanitize_text_field( $_GET['view'] ) : 'donors';
	if ( array_key_exists( $requested_view, $default_views ) && function_exists( $default_views[ $requested_view ] ) ) {
		walkthecounty_render_donor_view( $requested_view, $default_views );
	} else {
		walkthecounty_donors_list();
	}
}

/**
 * Register the views for donor management.
 *
 * @since  1.0
 * @return array Array of views and their callbacks.
 */
function walkthecounty_donor_views() {

	$views = array();

	return apply_filters( 'walkthecounty_donor_views', $views );

}

/**
 * Register the tabs for donor management.
 *
 * @since  1.0
 * @return array Array of tabs for the donor.
 */
function walkthecounty_donor_tabs() {

	$tabs = array();

	return apply_filters( 'walkthecounty_donor_tabs', $tabs );

}

/**
 * List table of donors.
 *
 * @since  1.0
 * @return void
 */
function walkthecounty_donors_list() {

	include WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/donors/class-donor-table.php';

	$donors_table = new WalkTheCounty_Donor_List_Table();
	$donors_table->prepare_items();
	?>
	<div class="wrap">
		<h1 class="wp-heading-inline"><?php echo get_admin_page_title(); ?></h1>
		<?php
		/**
		 * Fires in donors screen, above the table.
		 *
		 * @since 1.0
		 */
		do_action( 'walkthecounty_donors_table_top' );
		?>

		<hr class="wp-header-end">
		<form id="walkthecounty-donors-filter" method="get" action="<?php echo admin_url( 'edit.php?post_type=walkthecounty_forms&page=walkthecounty-donors' ); ?>">
			<?php
			$donors_table->advanced_filters();
			$donors_table->display();
			?>
			<input type="hidden" name="post_type" value="walkthecounty_forms"/>
			<input type="hidden" name="page" value="walkthecounty-donors"/>
			<input type="hidden" name="view" value="donors"/>
		</form>
		<?php
		/**
		 * Fires in donors screen, below the table.
		 *
		 * @since 1.0
		 */
		do_action( 'walkthecounty_donors_table_bottom' );
		?>
	</div>
	<?php
}

/**
 * Renders the donor view wrapper.
 *
 * @since  1.0
 *
 * @param  string $view The View being requested.
 * @param  array $callbacks The Registered views and their callback functions.
 *
 * @return void
 */
function walkthecounty_render_donor_view( $view, $callbacks ) {

	$render = true;

	$donor_view_role = apply_filters( 'walkthecounty_view_donors_role', 'view_walkthecounty_reports' );

	if ( ! current_user_can( $donor_view_role ) ) {
		walkthecounty_set_error( 'walkthecounty-no-access', __( 'You are not permitted to view this data.', 'walkthecounty' ) );
		$render = false;
	}

	if ( ! isset( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
		walkthecounty_set_error( 'walkthecounty-invalid_donor', __( 'Invalid Donor ID.', 'walkthecounty' ) );
		$render = false;
	}

	$donor_id          = (int) $_GET['id'];
	$reconnect_user_id = ! empty( $_GET['user_id'] ) ? (int) $_GET['user_id'] : '';
	$donor             = new WalkTheCounty_Donor( $donor_id );

	// Reconnect User with Donor profile.
	if ( $reconnect_user_id ) {
		walkthecounty_connect_user_donor_profile( $donor, array( 'user_id' => $reconnect_user_id ), array() );
	}

	if ( empty( $donor->id ) ) {
		walkthecounty_set_error( 'walkthecounty-invalid_donor', __( 'Invalid Donor ID.', 'walkthecounty' ) );
		$render = false;
	}

	?>

	<div class='wrap'>

		<h1 class="wp-heading-inline">
			<?php
			printf(
			/* translators: %s: donor first name */
				__( 'Edit Donor: %s %s', 'walkthecounty' ),
				$donor->get_first_name(),
				$donor->get_last_name()
			);
			?>
		</h1>

		<hr class="wp-header-end">

		<?php if ( walkthecounty_get_errors() ) : ?>
			<div class="error settings-error">
				<?php WalkTheCounty()->notices->render_frontend_notices( 0 ); ?>
			</div>
		<?php endif; ?>

		<?php if ( $donor && $render ) : ?>

			<div class="nav-tab-wrapper walkthecounty-nav-tab-wrapper">
				<?php

				$donor_tabs = walkthecounty_donor_tabs();

				foreach ( $donor_tabs as $key => $tab ) :
					$active = $key === $view ? true : false;
					$class  = $active ? 'nav-tab nav-tab-active' : 'nav-tab';
					printf(
						'<a href="%1$s" class="%2$s">%3$s</a>' . "\n",
						esc_url( admin_url( 'edit.php?post_type=walkthecounty_forms&page=walkthecounty-donors&view=' . $key . '&id=' . $donor->id ) ),
						esc_attr( $class ),
						esc_html( $tab['title'] )
					);
				endforeach;
				?>
			</div>

			<div id="walkthecounty-donor-card-wrapper">
				<?php $callbacks[ $view ]( $donor ) ?>
			</div>

		<?php endif; ?>

	</div>
	<?php

}


/**
 * View a donor
 *
 * @since  1.0
 *
 * @param  WalkTheCounty_Donor $donor The Donor object being displayed.
 *
 * @return void
 */
function walkthecounty_donor_view( $donor ) {

	$donor_edit_role = apply_filters( 'walkthecounty_edit_donors_role', 'edit_walkthecounty_payments' );

	/**
	 * Fires in donor profile screen, above the donor card.
	 *
	 * @since 1.0
	 *
	 * @param object $donor The donor object being displayed.
	 */
	do_action( 'walkthecounty_donor_card_top', $donor );

	// Set Read only to the fields which needs to be locked.
	$read_only = '';
	if ( $donor->user_id ) {
		$read_only = 'readonly="readonly"';
	}

	// List of title prefixes.
	$title_prefixes = walkthecounty_get_name_title_prefixes();

	// Prepend title prefix to name if it is set.
	$title_prefix              = WalkTheCounty()->donor_meta->get_meta( $donor->id, '_walkthecounty_donor_title_prefix', true );
	$donor_name_without_prefix = $donor->name;
	$donor->name               = walkthecounty_get_donor_name_with_title_prefixes( $title_prefix, $donor->name );
	?>
	<div id="donor-summary" class="info-wrapper donor-section postbox">
		<form id="edit-donor-info" method="post"
		      action="<?php echo esc_url( admin_url( 'edit.php?post_type=walkthecounty_forms&page=walkthecounty-donors&view=overview&id=' . $donor->id ) ); ?>">
			<div class="donor-info">
				<div class="donor-bio-header clearfix">
					<div class="avatar-wrap left" id="donor-avatar">
						<?php

						// Check whether a Gravatar exists for a donor or not.
						$validate_gravatar_image = walkthecounty_validate_gravatar( $donor->email );

						// Get donor's initials for non-gravatars
						$donor_name_array             = explode( " ", $donor_name_without_prefix );
						$donor_name_args['firstname'] = ! empty( $donor_name_array[0] ) ? $donor_name_array[0] : '';
						$donor_name_args['lastname']  = ! empty( $donor_name_array[1] ) ? $donor_name_array[1] : '';
						$donor_name_initial           = walkthecounty_get_name_initial( $donor_name_args );

						// Gravatars image for donor
						if ( $validate_gravatar_image ) {
							$donor_gravatar_image = get_avatar( $donor->email );
						} else {
							$donor_gravatar_image = '<div class="walkthecounty-donor-admin-avatar">' . $donor_name_initial . '</div>';
						}

						echo $donor_gravatar_image;
						?>
					</div>
					<div id="donor-name-wrap" class="left">
						<span class="donor-name info-item edit-item">
							<select name="donor_info[title]">
								<option disabled value="0"><?php esc_html_e( 'Title', 'walkthecounty' ); ?></option>
								<option value="">&nbsp;</option>
								<?php
								if ( is_array( $title_prefixes ) && count( $title_prefixes ) > 0 ) {
									foreach ( $title_prefixes as $title ) {
										echo sprintf(
											'<option %1$s value="%2$s">%2$s</option>',
											selected( $title_prefix, $title, false ),
											esc_html( $title )
										);
									}
								}
								?>
							</select>
							<input <?php echo $read_only; ?> size="15" data-key="first_name"
							                                 name="donor_info[first_name]" type="text"
							                                 value="<?php echo esc_html( $donor->get_first_name() ); ?>"
							                                 placeholder="<?php esc_html_e( 'First Name', 'walkthecounty' ); ?>"/>
							<?php if ( $donor->user_id ) : ?>
								<a href="#" class="walkthecounty-lock-block">
									<i class="walkthecounty-icon walkthecounty-icon-locked"></i>
								</a>
							<?php endif; ?>
							<input <?php echo $read_only; ?> size="15" data-key="last_name"
							                                 name="donor_info[last_name]" type="text"
							                                 value="<?php echo esc_html( $donor->get_last_name() ); ?>"
							                                 placeholder="<?php esc_html_e( 'Last Name', 'walkthecounty' ); ?>"/>
							<?php if ( $donor->user_id ) : ?>
								<a href="#" class="walkthecounty-lock-block">
									<i class="walkthecounty-icon walkthecounty-icon-locked"></i>
								</a>
							<?php endif; ?>
						</span>
						<span class="donor-name info-item editable">
							<span data-key="name"><?php echo esc_html( $donor->name ); ?></span>
						</span>
					</div>
					<p class="donor-since info-item">
						<?php esc_html_e( 'Donor since', 'walkthecounty' ); ?>
						<?php echo date_i18n( walkthecounty_date_format(), strtotime( $donor->date_created ) ) ?>
					</p>
					<?php if ( current_user_can( $donor_edit_role ) ) : ?>
						<a href="#" id="edit-donor" class="button info-item editable donor-edit-link">
							<?php esc_html_e( 'Edit Donor', 'walkthecounty' ); ?>
						</a>
					<?php endif; ?>
				</div>
				<!-- /donor-bio-header -->

				<div class="donor-main-wrapper">

					<table class="widefat striped">
						<tbody>
						<tr>
							<th scope="col"><label for="tablecell"><?php esc_html_e( 'Donor ID:', 'walkthecounty' ); ?></label>
							</th>
							<td><?php echo intval( $donor->id ); ?></td>
						</tr>
						<tr>
							<th scope="col"><label for="tablecell"><?php esc_html_e( 'User ID:', 'walkthecounty' ); ?></label>
							</th>
							<td>
									<span class="donor-user-id info-item edit-item">
										<?php

										$user_id = $donor->user_id > 0 ? $donor->user_id : '';

										$data_atts = array(
											'key'         => 'user_login',
											'search-type' => 'user',
										);
										$user_args = array(
											'name'  => 'donor_info[user_id]',
											'class' => 'walkthecounty-user-dropdown',
											'data'  => $data_atts,
										);

										if ( ! empty( $user_id ) ) {
											$userdata              = get_userdata( $user_id );
											$user_args['selected'] = $user_id;
										}

										echo WalkTheCounty()->html->ajax_user_search( $user_args );
										?>
									</span>

								<span class="donor-user-id info-item editable">
										<?php if ( ! empty( $userdata ) ) : ?>
											<span
												data-key="user_id">#<?php echo $donor->user_id . ' - ' . $userdata->display_name; ?></span>
										<?php else : ?>
											<span
												data-key="user_id"><?php esc_html_e( 'Unregistered', 'walkthecounty' ); ?></span>
										<?php endif; ?>
									<?php if ( current_user_can( $donor_edit_role ) && intval( $donor->user_id ) > 0 ) :

										echo sprintf(
											'- <span class="disconnect-user">
												<a id="disconnect-donor" href="#disconnect" aria-label="%1$s">%2$s</a>
											</span> | 
											<span class="view-user-profile">
												<a id="view-user-profile" href="%3$s" aria-label="%4$s">%5$s</a>
											</span>',
											esc_html__( 'Disconnects the current user ID from this donor record.', 'walkthecounty' ),
											esc_html__( 'Disconnect User', 'walkthecounty' ),
											esc_url( 'user-edit.php?user_id=' . $donor->user_id ),
											esc_html__( 'View User Profile of current user ID.', 'walkthecounty' ),
											esc_html__( 'View User Profile', 'walkthecounty' )
										);

									endif; ?>
									</span>
							</td>
						</tr>

						<?php
						$donor_company = $donor->get_meta( '_walkthecounty_donor_company', true );
						?>
						<tr class="alternate">
							<th scope="col">
								<label for="tablecell"><?php esc_html_e( 'Company Name:', 'walkthecounty' ); ?></label>
							</th>
							<td>
								<span class="donor-user-id info-item edit-item">
									<input name="walkthecounty_donor_company" value="<?php echo $donor_company ?>" type="text">
								</span>

								<span class="donor-user-id info-item editable">
									<?php echo $donor_company; ?>
								</span>
							</td>
						</tr>
						</tbody>
					</table>
				</div>

			</div>

			<span id="donor-edit-actions" class="edit-item">
				<input type="hidden" data-key="id" name="donor_info[id]" value="<?php echo intval( $donor->id ); ?>"/>
				<?php wp_nonce_field( 'edit-donor', '_wpnonce', false, true ); ?>
				<input type="hidden" name="walkthecounty_action" value="edit-donor"/>
				<input type="submit" id="walkthecounty-edit-donor-save" class="button-secondary"
				       value="<?php esc_html_e( 'Update Donor', 'walkthecounty' ); ?>"/>
				<a id="walkthecounty-edit-donor-cancel" href="" class="delete"><?php esc_html_e( 'Cancel', 'walkthecounty' ); ?></a>
			</span>

		</form>

	</div>

	<?php
	/**
	 * Fires in donor profile screen, above the stats list.
	 *
	 * @since 1.0
	 *
	 * @param WalkTheCounty_Donor $donor The donor object being displayed.
	 */
	do_action( 'walkthecounty_donor_before_stats', $donor );
	?>

	<div id="donor-stats-wrapper" class="donor-section postbox clear">
		<ul>
			<li>
				<a href="<?php echo admin_url( 'edit.php?post_type=walkthecounty_forms&page=walkthecounty-payment-history&donor=' . absint( $donor->id ) ); ?>">
					<span class="dashicons dashicons-heart"></span>
					<?php
					// Completed Donations.
					$completed_donations_text = sprintf( _n( '%d Completed Donation', '%d Completed Donations', $donor->purchase_count, 'walkthecounty' ), $donor->purchase_count );
					echo apply_filters( 'walkthecounty_donor_completed_donations', $completed_donations_text, $donor );
					?>
				</a>
			</li>
			<li>
				<span class="dashicons dashicons-chart-area"></span>
				<?php echo walkthecounty_currency_filter( walkthecounty_format_amount( $donor->get_total_donation_amount(), array( 'sanitize' => false ) ) ); ?> <?php _e( 'Lifetime Donations', 'walkthecounty' ); ?>
			</li>
			<?php
			/**
			 * Fires in donor profile screen, in the stats list.
			 *
			 * Allows you to add more list items to the stats list.
			 *
			 * @since 1.0
			 *
			 * @param object $donor The donor object being displayed.
			 */
			do_action( 'walkthecounty_donor_stats_list', $donor );
			?>
		</ul>
	</div>

	<?php
	/**
	 * Fires in donor profile screen, above the address list.
	 *
	 * @since 1.8.14
	 *
	 * @param WalkTheCounty_Donor $donor The donor object being displayed.
	 */
	do_action( 'walkthecounty_donor_before_address', $donor );
	?>

	<div id="donor-address-wrapper" class="donor-section clear">
		<h3><?php _e( 'Addresses', 'walkthecounty' ); ?></h3>

		<div class="postbox walkthecounty-donor-addresses">
			<div class="walkthecounty-spinner-wrapper">
				<span class="walkthecounty-spinner spinner aligncenter"></span>
			</div>
			<div class="inside">
				<div class="all-address">
					<div class="walkthecounty-grid-row">
						<?php
						if ( ! empty( $donor->address ) ) :
							// Default address always will be at zero array index.
							$is_set_as_default = null;

							foreach ( $donor->address as $address_type => $addresses ) {

								switch ( true ) {
									case is_array( end( $addresses ) ):
										$index = 1;
										foreach ( $addresses as $id => $address ) {
											echo __walkthecounty_get_format_address(
												$address,
												array(
													'type'  => $address_type,
													'id'    => $id,
													'index' => $index,
												)
											);

											$index ++;
										}
										break;

									case is_string( end( $addresses ) ):
										echo __walkthecounty_get_format_address(
											$addresses,
											array(
												'type' => $address_type,
											)
										);
										break;
								}
							}
						endif;
						?>
					</div>
					<span class="walkthecounty-no-address-message<?php if ( ! empty( $donor->address ) ) {
						echo ' walkthecounty-hidden';
					} ?>">
						<?php _e( 'This donor does not have any addresses saved.', 'walkthecounty' ); ?>
					</span>
					<button class="button add-new-address">
						<?php _e( 'Add Address', 'walkthecounty' ); ?>
					</button>
				</div>

				<div class="address-form add-new-address-form-hidden">
					<form action="" method="post">
						<table class="widefat striped">
							<tbody>
							<tr>
								<th class="col">
									<label class="country"><?php esc_html_e( 'Country:', 'walkthecounty' ); ?></label>
								</th>
								<td>
									<?php
									echo WalkTheCounty()->html->select( array(
										'options'          => walkthecounty_get_country_list(),
										'name'             => 'country',
										'selected'         => walkthecounty_get_option( 'base_country' ),
										'show_option_all'  => false,
										'show_option_none' => false,
										'chosen'           => true,
										'placeholder'      => esc_attr__( 'Select a country', 'walkthecounty' ),
										'data'             => array( 'search-type' => 'no_ajax' ),
										'autocomplete'     => 'country',
									) );
									?>
								</td>
							</tr>
							<tr>
								<th class="col">
									<label for="line1"><?php esc_html_e( 'Address 1:', 'walkthecounty' ); ?></label>
								</th>
								<td>
									<input id="line1" name="line1" type="text" class="medium-text"/>
								</td>
							</tr>
							<tr>
								<th class="col">
									<label for="line2"><?php esc_html_e( 'Address 2:', 'walkthecounty' ); ?></label>
								</th>
								<td>
									<input id="line2" type="text" name="line2" value="" class="medium-text"/>

								</td>
							</tr>
							<tr>
								<th class="col">
									<label for="city"><?php esc_html_e( 'City:', 'walkthecounty' ); ?></label>
								</th>
								<td>
									<input id="city" type="text" name="city" value="" class="medium-text"/>
								</td>
							</tr>
							<?php
							$no_states_country = walkthecounty_no_states_country_list();
							$base_country      = walkthecounty_get_option( 'base_country' );
							if ( ! array_key_exists( $base_country, $no_states_country ) ) {
								?>
								<tr class="walkthecounty-field-wrap">
									<th class="col">
										<label
											for="state"><?php esc_html_e( 'State / Province / County:', 'walkthecounty' ); ?></label>
									</th>
									<td>
										<?php
										$states     = walkthecounty_get_states( $base_country );
										$state_args = array(
											'name'         => 'state',
											'class'        => 'regular-text',
											'autocomplete' => 'address-level1',
										);

										if ( empty( $states ) ) {

											// Show Text field, if empty states.
											$state_args = wp_parse_args( $state_args, array(
												'value' => walkthecounty_get_option( 'base_state' ),
											) );
											echo WalkTheCounty()->html->text( $state_args );
										} else {

											// Show Chosen DropDown, if states are not empty.
											$state_args = wp_parse_args( $state_args, array(
												'options'          => $states,
												'selected'         => walkthecounty_get_option( 'base_state' ),
												'show_option_all'  => false,
												'show_option_none' => false,
												'chosen'           => true,
												'placeholder'      => __( 'Select a state', 'walkthecounty' ),
												'data'             => array( 'search-type' => 'no_ajax' ),
											) );
											echo WalkTheCounty()->html->select( $state_args );
										}
										?>
									</td>
								</tr>
								<?php
							}
							?>
							<tr>
								<th class="col">
									<label for="zip"><?php esc_html_e( 'Zip / Postal Code:', 'walkthecounty' ); ?></label>
								</th>
								<td>
									<input id="zip" type="text" name="zip" value="" class="medium-text"/>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<?php wp_nonce_field( 'walkthecounty-manage-donor-addresses', '_wpnonce', false ); ?>
									<input type="hidden" name="address-action" value="add">
									<input type="hidden" name="address-id" value="">
									<input type="submit" class="button button-primary js-save"
									       value="<?php _e( 'Save', 'walkthecounty' ); ?>">&nbsp;&nbsp;<button
										class="button js-cancel"><?php _e( 'Cancel', 'walkthecounty' ); ?></button>
								</td>
							</tr>
							</tbody>
						</table>
					</form>
				</div>
			</div>
		</div>
	</div>

	<?php
	/**
	 * Fires in donor profile screen, above the tables wrapper.
	 *
	 * @since 1.0
	 *
	 * @param WalkTheCounty_Donor $donor The donor object being displayed.
	 */
	do_action( 'walkthecounty_donor_before_tables_wrapper', $donor );
	?>

	<div id="donor-tables-wrapper" class="donor-section">

		<?php
		/**
		 * Fires in donor profile screen, above the tables.
		 *
		 * @since 1.0
		 *
		 * @param object $donor The donor object being displayed.
		 */
		do_action( 'walkthecounty_donor_before_tables', $donor );
		?>

		<h3><?php _e( 'Donor Emails', 'walkthecounty' ); ?></h3>

		<table class="wp-list-table widefat striped emails">
			<thead>
			<tr>
				<th><?php _e( 'Email', 'walkthecounty' ); ?></th>
				<th><?php _e( 'Actions', 'walkthecounty' ); ?></th>
			</tr>
			</thead>

			<tbody>
			<?php if ( ! empty( $donor->emails ) ) { ?>

				<?php foreach ( $donor->emails as $key => $email ) : ?>
					<tr data-key="<?php echo $key; ?>">
						<td>
							<?php echo $email; ?>
							<?php if ( 'primary' === $key ) : ?>
								<span class="dashicons dashicons-star-filled primary-email-icon"></span>
							<?php endif; ?>
						</td>
						<td>
							<?php if ( 'primary' !== $key ) : ?>
								<?php
								$base_url    = admin_url( 'edit.php?post_type=walkthecounty_forms&page=walkthecounty-donors&view=overview&id=' . $donor->id );
								$promote_url = wp_nonce_url( add_query_arg( array(
									'email'       => rawurlencode( $email ),
									'walkthecounty_action' => 'set_donor_primary_email',
								), $base_url ), 'walkthecounty-set-donor-primary-email' );
								$remove_url  = wp_nonce_url( add_query_arg( array(
									'email'       => rawurlencode( $email ),
									'walkthecounty_action' => 'remove_donor_email',
								), $base_url ), 'walkthecounty-remove-donor-email' );
								?>
								<a href="<?php echo $promote_url; ?>"><?php _e( 'Make Primary', 'walkthecounty' ); ?></a>
								&nbsp;|&nbsp;
								<a href="<?php echo $remove_url; ?>" class="delete"><?php _e( 'Remove', 'walkthecounty' ); ?></a>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>

				<tr class="add-donor-email-row">
					<td colspan="2" class="add-donor-email-td">
						<div class="add-donor-email-wrapper">
							<input type="hidden" name="donor-id" value="<?php echo $donor->id; ?>"/>
							<?php wp_nonce_field( 'walkthecounty_add_donor_email', 'add_email_nonce', false, true ); ?>
							<input type="email" name="additional-email" value=""
							       placeholder="<?php _e( 'Email Address', 'walkthecounty' ); ?>"/>&nbsp;
							<input type="checkbox" name="make-additional-primary" value="1"
							       id="make-additional-primary"/>&nbsp;<label
								for="make-additional-primary"><?php _e( 'Make Primary', 'walkthecounty' ); ?></label>
							<button class="button-secondary walkthecounty-add-donor-email"
							        id="add-donor-email"><?php _e( 'Add Email', 'walkthecounty' ); ?></button>
							<span class="spinner"></span>
						</div>
						<div class="notice-wrap"></div>
					</td>
				</tr>
			<?php } else { ?>
				<tr>
					<td colspan="2"><?php _e( 'No Emails Found', 'walkthecounty' ); ?></td>
				</tr>
			<?php }// End if().
			?>
			</tbody>
		</table>

		<h3><?php _e( 'Recent Donations', 'walkthecounty' ); ?></h3>
		<?php
		$payment_ids = explode( ',', $donor->payment_ids );
		$payments    = walkthecounty_get_payments( array(
			'post__in' => $payment_ids,
		) );
		$payments    = array_slice( $payments, 0, 10 );
		?>
		<table class="wp-list-table widefat striped payments">
			<thead>
			<tr>
				<th scope="col"><?php _e( 'ID', 'walkthecounty' ); ?></th>
				<th scope="col"><?php _e( 'Amount', 'walkthecounty' ); ?></th>
				<th scope="col"><?php _e( 'Date', 'walkthecounty' ); ?></th>
				<th scope="col"><?php _e( 'Status', 'walkthecounty' ); ?></th>
				<th scope="col"><?php _e( 'Actions', 'walkthecounty' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php if ( ! empty( $payments ) ) { ?>
				<?php foreach ( $payments as $payment ) : ?>
					<tr>
						<td><?php echo WalkTheCounty()->seq_donation_number->get_serial_code( $payment->ID ); ?></td>
						<td><?php echo walkthecounty_donation_amount( $payment->ID, array(
								'currency' => true,
								'amount'   => true,
								'type'     => 'donor'
							) ); ?></td>
						<td><?php echo date_i18n( walkthecounty_date_format(), strtotime( $payment->post_date ) ); ?></td>
						<td><?php echo walkthecounty_get_payment_status( $payment, true ); ?></td>
						<td>
							<?php
							printf(
								'<a href="%1$s" aria-label="%2$s">%3$s</a>',
								admin_url( 'edit.php?post_type=walkthecounty_forms&page=walkthecounty-payment-history&view=view-payment-details&id=' . $payment->ID ),
								sprintf(
								/* translators: %s: Donation ID */
									esc_attr__( 'View Donation %s.', 'walkthecounty' ),
									$payment->ID
								),
								__( 'View Donation', 'walkthecounty' )
							);
							?>

							<?php
							/**
							 * Fires in donor profile screen, in the recent donations tables action links.
							 *
							 * Allows you to add more action links for each donation, after the 'View Donation' action link.
							 *
							 * @since 1.0
							 *
							 * @param object $donor The donor object being displayed.
							 * @param object $payment The payment object being displayed.
							 */
							do_action( 'walkthecounty_donor_recent_purchases_actions', $donor, $payment );
							?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php } else { ?>
				<tr>
					<td colspan="5"><?php _e( 'No donations found.', 'walkthecounty' ); ?></td>
				</tr>
			<?php }// End if().
			?>
			</tbody>
		</table>

		<h3><?php _e( 'Completed Forms', 'walkthecounty' ); ?></h3>
		<?php
		$donations = walkthecounty_get_users_completed_donations( $donor->email );
		?>
		<table class="wp-list-table widefat striped donations">
			<thead>
			<tr>
				<th scope="col"><?php _e( 'Form', 'walkthecounty' ); ?></th>
				<th scope="col" width="120px"><?php _e( 'Actions', 'walkthecounty' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php if ( ! empty( $donations ) ) { ?>
				<?php foreach ( $donations as $donation ) : ?>
					<tr>
						<td><?php echo $donation->post_title; ?></td>
						<td>
							<?php
							printf(
								'<a href="%1$s" aria-label="%2$s">%3$s</a>',
								esc_url( admin_url( 'post.php?action=edit&post=' . $donation->ID ) ),
								sprintf(
								/* translators: %s: form name */
									esc_attr__( 'View Form %s.', 'walkthecounty' ),
									$donation->post_title
								),
								__( 'View Form', 'walkthecounty' )
							);
							?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php } else { ?>
				<tr>
					<td colspan="2"><?php _e( 'No completed donations found.', 'walkthecounty' ); ?></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
		<?php
		/**
		 * Fires in donor profile screen, below the tables.
		 *
		 * @since 1.0
		 *
		 * @param object $donor The donor object being displayed.
		 */
		do_action( 'walkthecounty_donor_after_tables', $donor );
		?>

	</div>

	<?php
	/**
	 * Fires in donor profile screen, below the donor card.
	 *
	 * @since 1.0
	 *
	 * @param object $donor The donor object being displayed.
	 */
	do_action( 'walkthecounty_donor_card_bottom', $donor );

}

/**
 * View the notes of a donor.
 *
 * @since  1.0
 *
 * @param  WalkTheCounty_Donor $donor The donor object being displayed.
 *
 * @return void
 */
function walkthecounty_donor_notes_view( $donor ) {

	$paged       = isset( $_GET['paged'] ) && is_numeric( $_GET['paged'] ) ? $_GET['paged'] : 1;
	$paged       = absint( $paged );
	$note_count  = $donor->get_notes_count();
	$per_page    = apply_filters( 'walkthecounty_donor_notes_per_page', 20 );
	$total_pages = ceil( $note_count / $per_page );
	$donor_notes = $donor->get_notes( $per_page, $paged );
	?>

	<div id="donor-notes-wrapper">
		<div class="donor-notes-header">
			<?php echo get_avatar( $donor->email, 30 ); ?> <span><?php echo $donor->name; ?></span>
		</div>
		<h3><?php _e( 'Notes', 'walkthecounty' ); ?></h3>

		<?php if ( 1 == $paged ) : ?>
			<div style="display: block; margin-bottom: 55px;">
				<form id="walkthecounty-add-donor-note" method="post"
				      action="<?php echo admin_url( 'edit.php?post_type=walkthecounty_forms&page=walkthecounty-donors&view=notes&id=' . $donor->id ); ?>">
					<textarea id="donor-note" name="donor_note" class="donor-note-input" rows="10"></textarea>
					<br/>
					<input type="hidden" id="donor-id" name="customer_id" value="<?php echo $donor->id; ?>"/>
					<input type="hidden" name="walkthecounty_action" value="add-donor-note"/>
					<?php wp_nonce_field( 'add-donor-note', 'add_donor_note_nonce', true, true ); ?>
					<input id="add-donor-note" class="right button-primary" type="submit" value="Add Note"/>
				</form>
			</div>
		<?php endif; ?>

		<?php
		$pagination_args = array(
			'base'     => '%_%',
			'format'   => '?paged=%#%',
			'total'    => $total_pages,
			'current'  => $paged,
			'show_all' => true,
		);

		echo paginate_links( $pagination_args );
		?>

		<div id="walkthecounty-donor-notes" class="postbox">
			<?php if ( count( $donor_notes ) > 0 ) { ?>
				<?php foreach ( $donor_notes as $key => $note ) : ?>
					<div class="donor-note-wrapper dashboard-comment-wrap comment-item">
					<span class="note-content-wrap">
						<?php echo stripslashes( $note ); ?>
					</span>
					</div>
				<?php endforeach; ?>
			<?php } else { ?>
				<div class="walkthecounty-no-donor-notes">
					<?php _e( 'No donor notes found.', 'walkthecounty' ); ?>
				</div>
			<?php } ?>
		</div>

		<?php echo paginate_links( $pagination_args ); ?>

	</div>

	<?php
}

/**
 * The donor delete view.
 *
 * @since  1.0
 *
 * @param  object $donor The donor object being displayed.
 *
 * @return void
 */
function walkthecounty_donor_delete_view( $donor ) {

	$donor_edit_role = apply_filters( 'walkthecounty_edit_donors_role', 'edit_walkthecounty_payments' );

	/**
	 * Fires in donor delete screen, above the content.
	 *
	 * @since 1.0
	 *
	 * @param object $donor The donor object being displayed.
	 */
	do_action( 'walkthecounty_donor_delete_top', $donor );
	?>

	<div class="info-wrapper donor-section">

		<form id="delete-donor" method="post"
		      action="<?php echo admin_url( 'edit.php?post_type=walkthecounty_forms&page=walkthecounty-donors&view=delete&id=' . $donor->id ); ?>">

			<div class="donor-notes-header">
				<?php echo get_avatar( $donor->email, 30 ); ?> <span><?php echo $donor->name; ?></span>
			</div>


			<div class="donor-info delete-donor">

				<span class="delete-donor-options">
					<p>
						<?php echo WalkTheCounty()->html->checkbox( array(
							'name' => 'walkthecounty-donor-delete-confirm',
						) ); ?>
						<label
							for="walkthecounty-donor-delete-confirm"><?php _e( 'Are you sure you want to delete this donor?', 'walkthecounty' ); ?></label>
					</p>

					<p>
						<?php echo WalkTheCounty()->html->checkbox( array(
							'name'    => 'walkthecounty-donor-delete-records',
							'options' => array(
								'disabled' => true,
							),
						) ); ?>
						<label
							for="walkthecounty-donor-delete-records"><?php _e( 'Delete all associated donations and records?', 'walkthecounty' ); ?></label>
					</p>

					<?php
					/**
					 * Fires in donor delete screen, bellow the delete inputs.
					 *
					 * Allows you to add custom delete inputs.
					 *
					 * @since 1.0
					 *
					 * @param object $donor The donor object being displayed.
					 */
					do_action( 'walkthecounty_donor_delete_inputs', $donor );
					?>
				</span>

				<span id="donor-edit-actions">
					<input type="hidden" name="donor_id" value="<?php echo $donor->id; ?>"/>
					<?php wp_nonce_field( 'walkthecounty-delete-donor', '_wpnonce', false, true ); ?>
					<input type="hidden" name="walkthecounty_action" value="delete_donor"/>
					<input type="submit" disabled="disabled" id="walkthecounty-delete-donor" class="button-primary"
					       value="<?php _e( 'Delete Donor', 'walkthecounty' ); ?>"/>
					<a id="walkthecounty-delete-donor-cancel"
					   href="<?php echo admin_url( 'edit.php?post_type=walkthecounty_forms&page=walkthecounty-donors&view=overview&id=' . $donor->id ); ?>"
					   class="delete"><?php _e( 'Cancel', 'walkthecounty' ); ?></a>
				</span>

			</div>

		</form>
	</div>

	<?php
	/**
	 * Fires in donor delete screen, bellow the content.
	 *
	 * @since 1.0
	 *
	 * @param object $donor The donor object being displayed.
	 */
	do_action( 'walkthecounty_donor_delete_bottom', $donor );
}
