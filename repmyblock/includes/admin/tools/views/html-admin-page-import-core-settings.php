<?php
/**
 * Admin View: Import Core Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_walkthecounty_settings' ) ) {
	return;
}

/**
 * Fires before displaying the import div tools.
 *
 * @since 1.8.17
 */
do_action( 'walkthecounty_tools_import_core_settings_main_before' );
?>
	<div id="poststuff" class="walkthecounty-clearfix">
		<div class="postbox">
			<h1 class="walkthecounty-importer-h1" align="center"><?php esc_html_e( 'Import Settings', 'walkthecounty' ); ?></h1>
			<div class="inside walkthecounty-tools-setting-page-import walkthecounty-import-core-settings">
				<?php
				/**
				 * Fires before from start.
				 *
				 * @since 1.8.17
				 */
				do_action( 'walkthecounty_tools_import_core_settings_form_before_start' );
				?>
				<form method="post" id="walkthecounty-import-core-settings-form"
				      class="walkthecounty-import-form tools-setting-page-import tools-setting-page-import"
				      enctype="multipart/form-data">

					<?php
					/**
					 * Fires just after form start.
					 *
					 * @since 1.8.17
					 */
					do_action( 'walkthecounty_tools_import_core_settings_form_start' );
					?>

					<?php
					/**
					 * Fires just after before form end.
					 *
					 * @since 1.8.17
					 */
					do_action( 'walkthecounty_tools_import_core_settings_form_end' );
					?>
				</form>
				<?php
				/**
				 * Fires just after form end.
				 *
				 * @since 1.8.17
				 */
				do_action( 'walkthecounty_tools_import_core_settings_form_after_end' );
				?>
			</div><!-- .inside -->
		</div><!-- .postbox -->
	</div><!-- #poststuff -->
<?php
/**
 * Fires after displaying the import div tools.
 *
 * @since 1.8.17
 */
do_action( 'walkthecounty_tools_import_core_settings_main_after' );
