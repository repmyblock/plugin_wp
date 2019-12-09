<?php
/**
 * Upgrades Completed Screen
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
?>
<div id="walkthecounty-updates" class="wrap walkthecounty-settings-page">

	<div class="walkthecounty-settings-header">
		<h1 id="walkthecounty-updates-h1"
		    class="wp-heading-inline"><?php echo sprintf( __( 'WalkTheCountyWP %s Updates Complete', 'walkthecounty' ), '<span class="walkthecounty-settings-heading-sep dashicons dashicons-arrow-right-alt2"></span>' ); ?></h1>
	</div>

	<div id="walkthecounty-updates-content">
		<div id="poststuff" class="walkthecounty-update-panel-content walkthecounty-clearfix">
			<p>
				<?php echo '🎉 '; ?>
				<?php esc_html_e( 'Congratulations! You are all up to date and running the latest versions of WalkTheCountyWP and its add-ons.', 'walkthecounty' ); ?>
			</p>
		</div>
	</div>

</div>
