<?php
/**
 * Admin Add-ons
 *
 * @package     WalkTheCounty
 * @subpackage  Admin/Add-ons
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add-ons Page
 *
 * Renders the add-ons page content.
 *
 * @return void
 * @since 1.0
 */
function walkthecounty_add_ons_page() {
	?>
	<div class="wrap" id="walkthecounty-addons">

		<div class="walkthecounty-addons-header">

			<div class="walkthecounty-admin-logo walkthecounty-addon-h1">
				<a href="https://walkthecountywp.com/&utm_campaign=admin&utm_source=addons&utm_medium=imagelogo"
				   class="walkthecounty-admin-logo-link" target="_blank"><img
						src="<?php echo WALKTHECOUNTY_PLUGIN_URL . 'assets/dist/images/walkthecounty-logo-large-no-tagline.png'; ?>"
						alt="<?php _e( 'Click to Visit WalkTheCountyWP in a new tab.', 'walkthecounty' ); ?>"/><span><?php echo esc_html( get_admin_page_title() ); ?></span></a>
			</div>
		</div>

		<div class="walkthecounty-subheader walkthecounty-clearfix">

			<h1>WalkTheCounty Add-ons</h1>

			<p class="walkthecounty-subheader-right-text"><?php esc_html_e( 'Maximize your fundraising potential with official add-ons from WalkTheCountyWP.com.', 'walkthecounty' ); ?></p>

			<div class="walkthecounty-hidden">
				<hr class="wp-header-end">
			</div>
		</div>
		<div class="walkthecounty-price-bundles-wrap walkthecounty-clearfix">
			<?php walkthecounty_add_ons_feed( 'price-bundle' ); ?>
		</div>

		<div class="walkthecounty-addons-directory-wrap walkthecounty-clearfix">
			<?php walkthecounty_add_ons_feed( 'addons-directory' ); ?>
		</div>
	</div>
	<?php

}

/**
 * Enqueue WalkTheCountyWP font family for just the add-ons page.
 *
 * @param $hook
 */
function walkthecounty_addons_enqueue_scripts( $hook ) {

	// Only enqueue on the addons page.
	if ( 'walkthecounty_forms_page_walkthecounty-addons' !== $hook ) {
		return;
	}

	// https://fonts.google.com/specimen/Montserrat?selection.family=Montserrat:400,400i,600,600i,700,700i,800,800i
	wp_register_style( 'walkthecounty_addons_font_families', 'https://fonts.googleapis.com/css?family=Montserrat:400,400i,600,600i,700,700i,800,800i', false );
	wp_enqueue_style( 'walkthecounty_addons_font_families' );
}

add_action( 'admin_enqueue_scripts', 'walkthecounty_addons_enqueue_scripts' );
