<?php
/**
 * Admin View: Exports
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_walkthecounty_settings' ) ) {
	return;
}

/**
 * Fires before displaying the recount stats tools.
 *
 * @since 1.5
 */
do_action( 'walkthecounty_tools_recount_stats_before' );
?>
	<div id="poststuff" class="walkthecounty-clearfix">
		<div class="postbox">

			<h2 class="hndle ui-sortable-handle"><span><?php esc_html_e( 'Recount Stats', 'walkthecounty' ); ?></span></h2>

			<div class="inside recount-stats-controls">
				<p><?php esc_html_e( 'Use these tools to recount stats, delete test transactions, or reset stats.', 'walkthecounty' ); ?></p>
				<form method="post" id="walkthecounty-tools-recount-form" class="walkthecounty-export-form">

					<?php wp_nonce_field( 'walkthecounty_ajax_export', 'walkthecounty_ajax_export' ); ?>

					<select name="walkthecounty-export-class" id="recount-stats-type">
						<option value="0" selected="selected" disabled="disabled"><?php esc_html_e( 'Please select an option', 'walkthecounty' ); ?></option>
						<option data-type="recount-stats" value="WalkTheCounty_Tools_Recount_Income"><?php esc_html_e( 'Recalculate Total Donation Income Amount', 'walkthecounty' ); ?></option>
						<option data-type="recount-form" value="WalkTheCounty_Tools_Recount_Form_Stats"><?php esc_html_e( 'Recalculate Income Amount and Donation Counts for a Form', 'walkthecounty' ); ?></option>
						<option data-type="recount-all" value="WalkTheCounty_Tools_Recount_All_Stats"><?php esc_html_e( 'Recalculate Income Amount and Donation Counts for All Forms', 'walkthecounty' ); ?></option>
						<option data-type="recount-donor-stats" value="WalkTheCounty_Tools_Recount_Donor_Stats"><?php esc_html_e( 'Recalculate Donor Statistics', 'walkthecounty' ); ?></option>
						<option data-type="delete-test-transactions" value="WalkTheCounty_Tools_Delete_Test_Transactions"><?php esc_html_e( 'Delete Test Donations', 'walkthecounty' ); ?></option>
						<option data-type="delete-donations" value="WalkTheCounty_Tools_Delete_Donations"><?php esc_html_e( 'Delete Live and Test Donations', 'walkthecounty' ); ?></option>
						<option data-type="delete-test-donors"   value="WalkTheCounty_Tools_Delete_Donors"><?php esc_html_e( 'Delete Test Donors and Donations', 'walkthecounty' ); ?></option>
						<option data-type="delete-import-donors"   value="WalkTheCounty_Tools_Import_Donors"><?php esc_html_e( 'Delete Imported Donors and Donations', 'walkthecounty' ); ?></option>
						<option data-type="reset-stats" value="WalkTheCounty_Tools_Reset_Stats"><?php esc_html_e( 'Delete All Data', 'walkthecounty' ); ?></option>
						<?php
						/**
						 * Fires in the recount stats selectbox.
						 *
						 * Allows you to add new recount tool option elements.
						 *
						 * @since 1.5
						 */
						do_action( 'walkthecounty_recount_tool_options' );
						?>
					</select>

					<span class="tools-form-dropdown tools-form-dropdown-recount-form" style="display: none">
						<?php
						$args = array(
							'class'       => 'tools-form-dropdown-recount-form-select',
							'name'        => 'form_id',
							'chosen'      => true,
							'placeholder' => esc_attr__( 'Select Form', 'walkthecounty' ),
						);
						echo WalkTheCounty()->html->forms_dropdown( $args );
						?>
					</span>

					<span class="tools-date-dropdown tools-date-dropdown-delete-donations" style="display: none">
						<?php
						echo WalkTheCounty()->html->date_field( array(
							'id'           => 'walkthecounty_delete_donations_start_date',
							'name'         => 'delete_donations_start_date',
							'placeholder'  => esc_attr__( 'Start date', 'walkthecounty' ),
							'autocomplete' => 'off',
						) );

						echo WalkTheCounty()->html->date_field( array(
							'id'           => 'walkthecounty_delete_donations_end_date',
							'name'         => 'delete_donations_end_date',
							'placeholder'  => esc_attr__( 'End date', 'walkthecounty' ),
							'autocomplete' => 'off',
						) );
						?>
					</span>

					<span class="tools-form-dropdown tools-form-dropdown-delete-import-donors" style="display: none">
						<label for="delete-import-donors">
							<?php
							echo WalkTheCounty()->html->checkbox( array( 'name' => 'delete-import-donors' ) );
							esc_html_e( 'Delete imported WordPress users', 'walkthecounty' );
							?>
						</label>
					</span>

					<input type="submit" id="recount-stats-submit" value="<?php esc_attr_e( 'Submit', 'walkthecounty' ); ?>" class="button-secondary"/>

					<br/>

					<span class="walkthecounty-recount-stats-descriptions">
						<span id="recount-stats"><?php esc_html_e( 'Recalculates the overall donation income amount.', 'walkthecounty' ); ?></span>
						<span id="recount-form"><?php esc_html_e( 'Recalculates the donation and income stats for a specific form.', 'walkthecounty' ); ?></span>
						<span id="recount-all"><?php esc_html_e( 'Recalculates the earnings and sales stats for all forms.', 'walkthecounty' ); ?></span>
						<span id="recount-customer-stats"><?php esc_html_e( 'Recalculates the lifetime value and donation counts for all donors.', 'walkthecounty' ); ?></span>
						<?php
						/**
						 * Fires in the recount stats description area.
						 *
						 * Allows you to add new recount tool description text.
						 *
						 * @since 1.5
						 */
						do_action( 'walkthecounty_recount_tool_descriptions' );
						?>
						<span id="delete-test-transactions"><strong><?php esc_html_e( 'Deletes', 'walkthecounty' ); ?></strong> <?php esc_html_e( 'all TEST donations, donors, and related log entries.', 'walkthecounty' ); ?></span>
						<span id="delete-donations"><strong><?php esc_html_e( 'Deletes', 'walkthecounty' ); ?></strong> <?php esc_html_e( 'all LIVE and TEST donations within a specified date range. If date range is not set then all donations are deleted.	', 'walkthecounty' ); ?></span>
						<span id="reset-stats"><strong><?php esc_html_e( 'Deletes', 'walkthecounty' ); ?></strong> <?php esc_html_e( 'ALL donations, donors, and related log entries regardless of TEST or LIVE mode.', 'walkthecounty' ); ?></span>
					</span>

					<span class="spinner"></span>

				</form>
				<?php
				/**
				 * Fires after the recount form.
				 *
				 * Allows you to add new elements after the form.
				 *
				 * @since 1.5
				 */
				do_action( 'walkthecounty_tools_recount_forms' );
				?>
			</div><!-- .inside -->
		</div><!-- .postbox -->
	</div><!-- #poststuff -->
<?php
/**
 * Fires after displaying the recount stats tools.
 *
 * @since 1.5
 */
do_action( 'walkthecounty_tools_recount_stats_after' );
