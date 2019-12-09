<?php
/**
 * Admin View: Imports
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<div id="poststuff" class="walkthecounty-clearfix">
	<div id="walkthecounty-dashboard-widgets-wrap">
		<div id="post-body">
			<div id="post-body-content">

				<?php
				/**
				 * Fires before the reports Import tab.
				 *
				 * @since 1.8.14
				 */
				do_action( 'walkthecounty_tools_tab_import_content_top' );
				?>

				<table class="widefat Import-options-table walkthecounty-table">
					<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Import Type', 'walkthecounty' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Import Options', 'walkthecounty' ); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php
					/**
					 * Fires in the reports Import tab.
					 *
					 * Allows you to add new TR elements to the table before
					 * other elements.
					 *
					 * @since 1.8.14
					 */
					do_action( 'walkthecounty_tools_tab_import_table_top' );
					?>

					<tr class="walkthecounty-Import-pdf-sales-earnings">
						<td scope="row" class="row-title">
							<h3>
								<span><?php esc_html_e( 'Import Donations', 'walkthecounty' ); ?></span>
							</h3>
							<p><?php esc_html_e( 'Import a CSV of Donations.', 'walkthecounty' ); ?></p>
						</td>
						<td>
							<a class="button" href="<?php echo add_query_arg( array( 'importer-type' => 'import_donations' ) ); ?>">
								<?php esc_html_e( 'Import CSV', 'walkthecounty' ); ?>
							</a>
						</td>
					</tr>

					<tr class="walkthecounty-import-core-settings">
						<td scope="row" class="row-title">
							<h3>
								<span><?php esc_html_e( 'Import WalkTheCountyWP Settings', 'walkthecounty' ); ?></span>
							</h3>
							<p><?php esc_html_e( 'Import WalkTheCounty\'s settings in JSON format.', 'walkthecounty' ); ?></p>
						</td>
						<td>
							<a class="button" href="<?php echo add_query_arg( array( 'importer-type' => 'import_core_setting' ) ); ?>">
								<?php esc_html_e( 'Import JSON', 'walkthecounty' ); ?>
							</a>
						</td>
					</tr>

					<?php
					/**
					 * Fires in the reports Import tab.
					 *
					 * Allows you to add new TR elements to the table after
					 * other elements.
					 *
					 * @since 1.8.14
					 */
					do_action( 'walkthecounty_tools_tab_import_table_bottom' );
					?>
					</tbody>
				</table>

				<?php
				/**
				 * Fires after the reports Import tab.
				 *
				 * @since 1.8.14
				 */
				do_action( 'walkthecounty_tools_tab_import_content_bottom' );
				?>

			</div>
			<!-- .post-body-content -->
		</div>
		<!-- .post-body -->
	</div><!-- #walkthecounty-dashboard-widgets-wrap -->
</div><!-- #poststuff -->
