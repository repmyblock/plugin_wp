<?php
/**
 * Upgrade/Updates Screen
 *
 * Displays both add-on updates for files and database upgrades
 *
 * @package     WalkTheCounty
 * @subpackage  Admin/Upgrades
 * @copyright   Copyright (c) 2017, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.8.12
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$walkthecounty_updates = WalkTheCounty_Updates::get_instance();
?>
<div id="walkthecounty-updates" class="wrap walkthecounty-settings-page">

	<div class="walkthecounty-settings-header">
		<h1 id="walkthecounty-updates-h1"
		    class="wp-heading-inline"><?php echo sprintf( __( 'WalkTheCountyWP %s Updates', 'walkthecounty' ), '<span class="walkthecounty-settings-heading-sep dashicons dashicons-arrow-right-alt2"></span>' ); ?></h1>
	</div>

	<?php $db_updates = $walkthecounty_updates->get_pending_db_update_count(); ?>

	<div id="walkthecounty-updates-content">

		<div id="poststuff" class="walkthecounty-clearfix">

		<?php
		/**
		 * Database Upgrades
		 */
		if ( ! empty( $db_updates ) ) : ?>
			<?php
			$is_doing_updates = $walkthecounty_updates->is_doing_updates();
			$db_update_url    = add_query_arg( array( 'type' => 'database' ) );
			$resume_updates   = get_option( 'walkthecounty_doing_upgrade' );
			$width            = ! empty( $resume_updates ) ? $resume_updates['percentage'] : 0;
			?>
			<div class="walkthecounty-update-panel-content">
				<p><?php printf( __( 'WalkTheCountyWP regularly receives new features, bug fixes, and enhancements. It is important to always stay up-to-date with latest version of WalkTheCountyWP core and its add-ons.  <strong>If you do not have a backup already, please create a full backup before updating.</strong> To update add-ons be sure your <a href="%1$s">license keys</a> are activated.', 'walkthecounty' ), admin_url('') ); ?></p>
			</div>

			<div id="walkthecounty-db-updates" data-resume-update="<?php echo absint( $walkthecounty_updates->is_doing_updates() ); ?>">
				<div class="postbox-container">
					<div class="postbox">
						<h2 class="hndle"><?php _e( 'Database Updates', 'walkthecounty' ); ?></h2>
						<div class="inside">
							<div class="panel-content">
								<p class="walkthecounty-update-button">
									<?php
									if ( ! walkthecounty_test_ajax_works() ) {
										echo sprintf(
											'<div class="notice notice-warning inline"><p>%s</p></div>',
											__( 'WalkTheCountyWP is currently updating the database. Please do not refresh or leave this page while the update is in progress.', 'walkthecounty' )
										);
									}
									?>
									<span
										class="walkthecounty-doing-update-text-p" <?php echo WalkTheCounty_Updates::$background_updater->is_paused_process() ? 'style="display:none;"' : ''; ?>>
										<?php
										echo sprintf(
											__( '%1$s <a href="%2$s" class="walkthecounty-update-now %3$s">%4$s</a>', 'walkthecounty' ),
											$is_doing_updates
												? sprintf(
												'%s%s',
												__( 'WalkTheCountyWP is currently updating the database', 'walkthecounty' ),
												walkthecounty_test_ajax_works() ? ' ' . __( 'in the background.', 'walkthecounty' ) : '.'
											)
												: __( 'WalkTheCountyWP needs to update the database.', 'walkthecounty' ),
											$db_update_url,
											( $is_doing_updates ? 'walkthecounty-hidden' : '' ),
											__( 'Update now', 'walkthecounty' )
										);
										?>
									</span>
									<span
										class="walkthecounty-update-paused-text-p" <?php echo ! WalkTheCounty_Updates::$background_updater->is_paused_process() ? 'style="display:none;"' : ''; ?>>
										<?php if ( get_option( 'walkthecounty_upgrade_error' ) ) : ?>
											&nbsp<?php _e( 'An unexpected issue occurred during the database update which caused it to stop automatically. Please contact support for assistance.', 'walkthecounty' ); ?>
										<?php else : ?>
											<?php _e( 'The updates have been paused.', 'walkthecounty' ); ?>
										<?php endif; ?>
									</span>

									<?php if ( WalkTheCounty_Updates::$background_updater->is_paused_process() ) : ?>
										<?php $is_disabled = isset( $_GET['walkthecounty-restart-db-upgrades'] ) ? ' disabled' : ''; ?>
										<button id="walkthecounty-restart-upgrades" class="button button-primary alignright"
										        data-redirect-url="<?php echo esc_url( admin_url( '/edit.php?post_type=walkthecounty_forms&page=walkthecounty-updates&walkthecounty-restart-db-upgrades=1' ) ); ?>"<?php echo $is_disabled; ?>><?php _e( 'Restart Upgrades', 'walkthecounty' ); ?></button>
									<?php elseif ( $walkthecounty_updates->is_doing_updates() ) : ?>
										<?php $is_disabled = isset( $_GET['walkthecounty-pause-db-upgrades'] ) ? ' disabled' : ''; ?>
										<button id="walkthecounty-pause-upgrades" class="button button-primary alignright"
										        data-redirect-url="<?php echo esc_url( admin_url( '/edit.php?post_type=walkthecounty_forms&page=walkthecounty-updates&walkthecounty-pause-db-upgrades=1' ) ); ?>"<?php echo $is_disabled; ?>>
											<?php _e( 'Pause Upgrades', 'walkthecounty' ); ?>
										</button>
									<?php endif; ?>
								</p>
							</div>
							<div class="progress-container<?php echo $is_doing_updates ? '' : ' walkthecounty-hidden'; ?>">
								<p class="update-message">
									<strong>
										<?php
										echo sprintf(
											__( 'Update %1$s of %2$s', 'walkthecounty' ),
											$walkthecounty_updates->get_running_db_update(),
											$walkthecounty_updates->get_total_new_db_update_count()
										);
										?>
									</strong>
								</p>
								<div class="progress-content">
									<?php if ( $is_doing_updates ) : ?>
										<div class="notice-wrap walkthecounty-clearfix">

											<?php if ( ! WalkTheCounty_Updates::$background_updater->is_paused_process() ) : ?>
												<span class="spinner is-active"></span>
											<?php endif; ?>

											<div class="walkthecounty-progress">
												<div style="width: <?php echo $width; ?>%;"></div>
											</div>
										</div>
									<?php endif; ?>
								</div>
							</div>
							<?php if ( ! $is_doing_updates ) : ?>
								<div class="walkthecounty-run-database-update"></div>
							<?php endif; ?>
						</div>
						<!-- .inside -->
					</div><!-- .postbox -->
				</div> <!-- .post-container -->
			</div>
		<?php endif; ?>

		<?php
		/**
		 * Add-on Updates
		 */
		$plugin_updates = $walkthecounty_updates->get_total_plugin_update_count();
		if ( ! empty( $plugin_updates ) ) : ?>
			<?php
			$plugin_update_url = add_query_arg(
				array(
					'plugin_status' => 'walkthecounty',
				), admin_url( '/plugins.php' )
			);
			?>
			<div id="walkthecounty-plugin-updates">
				<div class="postbox-container">
					<div class="postbox">
						<h2 class="hndle"><?php _e( 'Add-on Updates', 'walkthecounty' ); ?></h2>
						<div class="inside">
							<div class="panel-content">
								<p>
									<?php
									printf(
										_n(
											'There is %1$d WalkTheCountyWP addon that needs to be updated. <a href="%2$s">Update now</a>',
											'There are %1$d WalkTheCountyWP addons that need to be updated. <a href="%2$s">Update now</a>',
											$plugin_updates,
											'walkthecounty'
										),
										$plugin_updates,
										$plugin_update_url
									);
									?>
								</p>
								<?php include_once 'plugins-update-section.php'; ?>
							</div>
						</div>
						<!-- .inside -->
					</div><!-- .postbox -->
				</div>
			</div>
		<?php endif; ?>

		</div><!-- /#poststuff -->

	</div><!-- /#walkthecounty-updates-content -->

</div><!-- /#walkthecounty-updates -->
