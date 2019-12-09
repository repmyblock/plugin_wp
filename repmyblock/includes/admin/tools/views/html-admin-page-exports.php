<?php
/**
 * Admin View: Exports
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<div id="poststuff">
	<div id="walkthecounty-dashboard-widgets-wrap">
		<div id="post-body">
			<div id="post-body-content">

				<?php
				/**
				 * Fires before the reports export tab.
				 *
				 * @since 1.0
				 */
				do_action( 'walkthecounty_tools_tab_export_content_top' );
				?>

				<table class="widefat export-options-table walkthecounty-table striped">
					<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Export Type', 'walkthecounty' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Export Options', 'walkthecounty' ); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php
					/**
					 * Fires in the reports export tab.
					 *
					 * Allows you to add new TR elements to the table before
					 * other elements.
					 *
					 * @since 1.0
					 */
					do_action( 'walkthecounty_tools_tab_export_table_top' );
					?>

					<tr class="walkthecounty-export-donations-history">
						<td scope="row" class="row-title">
							<h3>
								<span><?php esc_html_e( 'Export Donation History', 'walkthecounty' ); ?></span>
							</h3>
							<p><?php esc_html_e( 'Download a CSV of all donations recorded.', 'walkthecounty' ); ?></p>
						</td>
						<td>
							<a class="button" href="<?php echo add_query_arg( array( 'type' => 'export_donations' ) ); ?>">
								<?php esc_html_e( 'Generate CSV', 'walkthecounty' ); ?>
							</a>
						</td>
					</tr>

					<tr class="walkthecounty-export-pdf-sales-earnings">
						<td scope="row" class="row-title">
							<h3>
								<span><?php esc_html_e( 'Export PDF of Donations and Income', 'walkthecounty' ); ?></span>
							</h3>
							<p><?php esc_html_e( 'Download a PDF of Donations and Income reports for all forms for the current year.', 'walkthecounty' ); ?></p>
						</td>
						<td>
							<a class="button" href="<?php echo wp_nonce_url( add_query_arg( array( 'walkthecounty-action' => 'generate_pdf' ) ), 'walkthecounty_generate_pdf' ); ?>">
								<?php esc_html_e( 'Generate PDF', 'walkthecounty' ); ?>
							</a>
						</td>
					</tr>
					<tr class="walkthecounty-export-sales-earnings">
						<td scope="row" class="row-title">
							<h3>
								<span><?php esc_html_e( 'Export Income and Donation Stats', 'walkthecounty' ); ?></span>
							</h3>
							<p><?php esc_html_e( 'Download a CSV of income and donations over time.', 'walkthecounty' ); ?></p>
						</td>
						<td>
							<form method="post">
								<?php
								printf(
								/* translators: 1: start date dropdown 2: end date dropdown */
									esc_html__( '%1$s to %2$s', 'walkthecounty' ),
									WalkTheCounty()->html->year_dropdown( 'start_year' ) . ' ' . WalkTheCounty()->html->month_dropdown( 'start_month' ),
									WalkTheCounty()->html->year_dropdown( 'end_year' ) . ' ' . WalkTheCounty()->html->month_dropdown( 'end_month' )
								);
								?>
								<input type="hidden" name="walkthecounty-action" value="earnings_export"/>
								<input type="submit" value="<?php esc_attr_e( 'Generate CSV', 'walkthecounty' ); ?>" class="button-secondary"/>
							</form>
						</td>
					</tr>

					<tr class="walkthecounty-export-donors">
						<td scope="row" class="row-title">
							<h3>
								<span><?php esc_html_e( 'Export Donors', 'walkthecounty' ); ?></span>
							</h3>
							<p><?php esc_html_e( 'Download a CSV of donors. Column values reflect totals across all donation forms by default, or a single donation form if selected.', 'walkthecounty' ); ?></p>
						</td>
						<td>
							<form method="post" id="walkthecounty_donor_export" class="walkthecounty-export-form">
								<?php
								// Start Date form field for donors.
								echo WalkTheCounty()->html->date_field( array(
									'id'           => 'walkthecounty_donor_export_start_date',
									'name'         => 'donor_export_start_date',
									'placeholder'  => esc_attr__( 'Start Date', 'walkthecounty' ),
									'autocomplete' => 'off',
								) );

								// End Date form field for donors.
								echo WalkTheCounty()->html->date_field( array(
									'id'           => 'walkthecounty_donor_export_end_date',
									'name'         => 'donor_export_end_date',
									'placeholder'  => esc_attr__( 'End Date', 'walkthecounty' ),
									'autocomplete' => 'off',
								) );

								// Donation forms dropdown for donors export.
								echo WalkTheCounty()->html->forms_dropdown( array(
									'name'   => 'forms',
									'id'     => 'walkthecounty_donor_export_form',
									'chosen' => true,
									'class'  => 'walkthecounty-width-25em',
								) );
								?>
								<br>
								<input type="submit" value="<?php esc_attr_e( 'Generate CSV', 'walkthecounty' ); ?>" class="button-secondary"/>

								<div id="export-donor-options-wrap" class="walkthecounty-clearfix">
									<p><?php esc_html_e( 'Export Columns:', 'walkthecounty' ); ?></p>
									<ul id="walkthecounty-export-option-ul">
										<?php
										$donor_export_columns = walkthecounty_export_donors_get_default_columns();

										foreach ( $donor_export_columns as $column_name => $column_label ) {
											?>
											<li>
												<label for="walkthecounty-export-<?php echo esc_attr( $column_name ); ?>">
													<input
															type="checkbox"
															checked
															name="walkthecounty_export_option[<?php echo esc_attr( $column_name ); ?>]"
															id="walkthecounty-export-<?php echo esc_attr( $column_name ); ?>"
													/>
													<?php echo esc_attr( $column_label ); ?>
												</label>
											</li>
											<?php
										}
										?>
									</ul>
								</div>
								<?php wp_nonce_field( 'walkthecounty_ajax_export', 'walkthecounty_ajax_export' ); ?>
								<input type="hidden" name="walkthecounty-export-class" value="WalkTheCounty_Batch_Donors_Export"/>
								<input type="hidden" name="walkthecounty_export_option[query_id]" value="<?php echo uniqid( 'walkthecounty_' ); ?>"/>
							</form>
						</td>
					</tr>

					<tr class="walkthecounty-export-core-settings">
						<td scope="row" class="row-title">
							<h3>
								<span><?php esc_html_e( 'Export WalkTheCountyWP Settings', 'walkthecounty' ); ?></span>
							</h3>
							<p><?php esc_html_e( 'Download an export of WalkTheCounty\'s settings and import it in a new WordPress installation.', 'walkthecounty' ); ?></p>
						</td>
						<td>
							<form method="post">
								<?php
								$export_excludes = apply_filters( 'walkthecounty_settings_export_excludes', array() );
								if ( ! empty( $export_excludes ) ) {
									?>
									<i class="settings-excludes-title"><?php esc_html_e( 'Checked options from the list will not be exported.', 'walkthecounty' ); ?></i>
									<ul class="settings-excludes-list">
										<?php foreach ( $export_excludes as $option_key => $option_label ) { ?>
											<li>
												<label for="settings_export_excludes[<?php echo $option_key ?>]">
													<input
															type="checkbox"
															checked
															name="settings_export_excludes[<?php echo $option_key ?>]"
															id="settings_export_excludes[<?php echo $option_key ?>]"
													/>
													<?php echo esc_html( $option_label ); ?>
												</label>
											</li>
										<?php } ?>
									</ul>
								<?php } ?>
								<input type="hidden" name="walkthecounty-action" value="core_settings_export"/>
								<input type="submit" value="<?php esc_attr_e( 'Export JSON', 'walkthecounty' ); ?>" class="button-secondary"/>
							</form>
						</td>
					</tr>
					<?php
					/**
					 * Fires in the reports export tab.
					 *
					 * Allows you to add new TR elements to the table after
					 * other elements.
					 *
					 * @since 1.0
					 */
					do_action( 'walkthecounty_tools_tab_export_table_bottom' );
					?>
					</tbody>
				</table>

				<?php
				/**
				 * Fires after the reports export tab.
				 *
				 * @since 1.0
				 */
				do_action( 'walkthecounty_tools_tab_export_content_bottom' );
				?>

			</div>
			<!-- .post-body-content -->
		</div>
		<!-- .post-body -->
	</div><!-- #walkthecounty-dashboard-widgets-wrap -->
</div><!-- #poststuff -->
