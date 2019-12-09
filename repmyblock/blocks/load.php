<?php
// Exit if access directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Custom Block Category for WalkTheCounty blocks
 */
function walkthecounty_blocks_category( $categories, $post ) {
    return array_merge(
        $categories,
        array(
            array(
                'slug' => 'walkthecounty',
                'title' => __( 'WalkTheCounty', 'walkthecounty' ),
            ),
        )
    );
}
add_filter( 'block_categories', 'walkthecounty_blocks_category', 10, 2 );

/**
* Blocks
*/
require_once WALKTHECOUNTY_PLUGIN_DIR . 'blocks/donation-form/class-walkthecounty-donation-form-block.php';
require_once WALKTHECOUNTY_PLUGIN_DIR . 'blocks/donation-form-grid/class-walkthecounty-donation-form-grid-block.php';
require_once WALKTHECOUNTY_PLUGIN_DIR . 'blocks/donor-wall/class-walkthecounty-donor-wall.php';
