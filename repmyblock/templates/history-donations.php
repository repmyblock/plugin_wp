<?php
/**
 * This template is used to display the donation history of the current user.
 */

$donations             = array();
$donation_history_args = WalkTheCounty()->session->get( 'walkthecounty_donation_history_args' );

// User's Donations.
if ( is_user_logged_in() ) {
	$donations = walkthecounty_get_users_donations( get_current_user_id(), 20, true, 'any' );
} elseif ( WalkTheCounty()->email_access->token_exists ) {
	// Email Access Token?
	$donations = walkthecounty_get_users_donations( 0, 20, true, 'any' );
} elseif (
	false !== WalkTheCounty()->session->get_session_expiration() ||
	true === walkthecounty_get_history_session()
) {
	// Session active?
	$email           = WalkTheCounty()->session->get( 'walkthecounty_email' );
	$donor           = WalkTheCounty()->donors->get_donor_by( 'email', $email );
	$donations_count = count( explode( ',', $donor->payment_ids ) );

	if ( $donations_count > walkthecounty_get_limit_display_donations() ) {

		// Restrict Security Email Access option, if donation count of a donor is less than or equal to limit.
		if ( true !== WalkTheCounty_Cache::get( "walkthecounty_cache_email_throttle_limit_exhausted_{$donor->id}" ) ) {
			add_action( 'walkthecounty_donation_history_table_end', 'walkthecounty_donation_history_table_end' );
		} else {
			$value = WalkTheCounty()->email_access->verify_throttle / 60;

			/**
			 * Filter to modify email access exceed notices message.
			 *
			 * @since 2.1.3
			 *
			 * @param string $message email access exceed notices message
			 * @param int $value email access exceed times
			 *
			 * @return string $message email access exceed notices message
			 */
			$message = (string) apply_filters(
				'walkthecounty_email_access_requests_exceed_notice',
				sprintf(
					__( 'Too many access email requests detected. Please wait %s before requesting a new donation history access link.', 'walkthecounty' ),
					sprintf( _n( '%s minute', '%s minutes', $value, 'walkthecounty' ), $value )
				),
				$value
			);

			walkthecounty_set_error( 'walkthecounty-limited-throttle',
				$message
			);
		}

		$donations = walkthecounty_get_users_donations( $email, walkthecounty_get_limit_display_donations(), true, 'any' );
	} else {
		$donations = walkthecounty_get_users_donations( $email, 20, true, 'any' );
	}
}

WalkTheCounty()->notices->render_frontend_notices( 0 );

if ( $donations ) : ?>
	<?php
	$table_headings = array(
		'id'             => __( 'ID', 'walkthecounty' ),
		'date'           => __( 'Date', 'walkthecounty' ),
		'donor'          => __( 'Donor', 'walkthecounty' ),
		'amount'         => __( 'Amount', 'walkthecounty' ),
		'status'         => __( 'Status', 'walkthecounty' ),
		'payment_method' => __( 'Payment Method', 'walkthecounty' ),
		'details'        => __( 'Details', 'walkthecounty' ),
	);
	?>
	<div class="walkthecounty_user_history_main" >
		<div class="walkthecounty_user_history_notice"></div>
		<table id="walkthecounty_user_history" class="walkthecounty-table">
			<thead>
			<tr class="walkthecounty-donation-row">
				<?php
				/**
				 * Fires in current user donation history table, before the header row start.
				 *
				 * Allows you to add new <th> elements to the header, before other headers in the row.
				 *
				 * @since 1.7
				 */
				do_action( 'walkthecounty_donation_history_header_before' );

				foreach ( $donation_history_args as $index => $value ) {
					if ( filter_var( $donation_history_args[ $index ], FILTER_VALIDATE_BOOLEAN ) ) :
						echo sprintf(
							'<th scope="col" class="walkthecounty-donation-%1$s>">%2$s</th>',
							$index,
							$table_headings[ $index ]
						);
					endif;
				}

				/**
				 * Fires in current user donation history table, after the header row ends.
				 *
				 * Allows you to add new <th> elements to the header, after other headers in the row.
				 *
				 * @since 1.7
				 */
				do_action( 'walkthecounty_donation_history_header_after' );
				?>
			</tr>
			</thead>
			<?php foreach ( $donations as $post ) :
				setup_postdata( $post );
				$donation_data = walkthecounty_get_payment_meta( $post->ID ); ?>
				<tr class="walkthecounty-donation-row">
					<?php
					/**
					 * Fires in current user donation history table, before the row statrs.
					 *
					 * Allows you to add new <td> elements to the row, before other elements in the row.
					 *
					 * @since 1.7
					 *
					 * @param int   $post_id       The ID of the post.
					 * @param mixed $donation_data Payment meta data.
					 */
					do_action( 'walkthecounty_donation_history_row_start', $post->ID, $donation_data );

					if ( filter_var( $donation_history_args['id'], FILTER_VALIDATE_BOOLEAN ) ) :
						echo sprintf(
							'<td class="walkthecounty-donation-id"><span class="walkthecounty-mobile-title">%2$s</span>%1$s</td>',
							walkthecounty_get_payment_number( $post->ID ), esc_html( $table_headings['id'] )
						);
					endif;

					if ( filter_var( $donation_history_args['date'], FILTER_VALIDATE_BOOLEAN ) ) :
						echo sprintf(
							'<td class="walkthecounty-donation-date"><span class="walkthecounty-mobile-title">%2$s</span>%1$s</td>',
							date_i18n( walkthecounty_date_format(), strtotime( get_post_field( 'post_date', $post->ID ) ) ), esc_html( $table_headings['date'] )
						);
					endif;

					if ( filter_var( $donation_history_args['donor'], FILTER_VALIDATE_BOOLEAN ) ) :
						echo sprintf(
							'<td class="walkthecounty-donation-donor"><span class="walkthecounty-mobile-title">%2$s</span>%1$s</td>',
							walkthecounty_get_donor_name_by( $post->ID ), $table_headings['donor']
						);
					endif;
					?>

					<?php if ( filter_var( $donation_history_args['amount'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
						<td class="walkthecounty-donation-amount">
						<?php printf( '<span class="walkthecounty-mobile-title">%1$s</span>', esc_html( $table_headings['amount'] ) ); ?>
						<span class="walkthecounty-donation-amount">
							<?php
							$currency_code   = walkthecounty_get_payment_currency_code( $post->ID );
							$donation_amount = walkthecounty_donation_amount( $post->ID, true );

							/**
							 * Filters the donation amount on Donation History Page.
							 *
							 * @param int $donation_amount Donation Amount.
							 * @param int $post_id         Donation ID.
							 *
							 * @since 1.8.13
							 *
							 * @return int
							 */
							echo apply_filters( 'walkthecounty_donation_history_row_amount', $donation_amount, $post->ID );
							?>
						</span>
						</td>
					<?php endif; ?>

					<?php
					if ( filter_var( $donation_history_args['status'], FILTER_VALIDATE_BOOLEAN ) ) :
						echo sprintf(
							'<td class="walkthecounty-donation-status"><span class="walkthecounty-mobile-title">%2$s</span>%1$s</td>',
							walkthecounty_get_payment_status( $post, true ),
							esc_html( $table_headings['status'] )
						);
					endif;

					if ( filter_var( $donation_history_args['payment_method'], FILTER_VALIDATE_BOOLEAN ) ) :
						echo sprintf(
							'<td class="walkthecounty-donation-payment-method"><span class="walkthecounty-mobile-title">%2$s</span>%1$s</td>',
							walkthecounty_get_gateway_checkout_label( walkthecounty_get_payment_gateway( $post->ID ) ),
							esc_html( $table_headings['payment_method'] )
						);
					endif;
					?>
					<td class="walkthecounty-donation-details">
						<?php
						// Display View Receipt or.
						if ( 'publish' !== $post->post_status && 'subscription' !== $post->post_status ) :
							echo sprintf(
								'<span class="walkthecounty-mobile-title">%4$s</span><a href="%1$s"><span class="walkthecounty-donation-status %2$s">%3$s</span></a>',
								esc_url(
									add_query_arg(
										'donation_id',
										$post->ID,
										walkthecounty_get_history_page_uri()
									)
								),
								$post->post_status,
								__( 'View', 'walkthecounty' ) . ' ' . walkthecounty_get_payment_status( $post, true ) . ' &raquo;',
								esc_html( $table_headings['details'] )
							);

						else :
							echo sprintf(
								'<span class="walkthecounty-mobile-title">%3$s</span><a href="%1$s">%2$s</a>',
								esc_url(
									add_query_arg(
										'donation_id',
										$post->ID,
										walkthecounty_get_history_page_uri()
									)
								),
								__( 'View Receipt &raquo;', 'walkthecounty' ),
								esc_html( $table_headings['details'] )
							);

						endif;
						?>
					</td>
					<?php
					/**
					 * Fires in current user donation history table, after the row ends.
					 *
					 * Allows you to add new <td> elements to the row, after other elements in the row.
					 *
					 * @since 1.7
					 *
					 * @param int   $post_id       The ID of the post.
					 * @param mixed $donation_data Payment meta data.
					 */
					do_action( 'walkthecounty_donation_history_row_end', $post->ID, $donation_data );
					?>
				</tr>
			<?php endforeach; ?>

			<?php
			/**
			 * Fires in footer of user donation history table.
			 *
			 * Allows you to add new <tfoot> elements to the row, after other elements in the row.
			 *
			 * @since 1.8.17
			 */
			do_action( 'walkthecounty_donation_history_table_end' );
			?>
		</table>
		<div id="walkthecounty-donation-history-pagination" class="walkthecounty_pagination navigation">
			<?php
			$big = 999999;
			echo paginate_links( array(
				'base'    => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
				'format'  => '?paged=%#%',
				'current' => max( 1, get_query_var( 'paged' ) ),
				'total'   => ceil( walkthecounty_count_donations_of_donor() / 20 ), // 20 items per page
			) );
			?>
		</div>
	</div>
	<?php wp_reset_postdata(); ?>
<?php else : ?>
	<?php WalkTheCounty_Notices::print_frontend_notice( __( 'It looks like you haven\'t made any donations.', 'walkthecounty' ), true, 'success' ); ?>
<?php endif;
