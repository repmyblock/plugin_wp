<?php
/**
 * Metabox Functions
 *
 * @package     WalkTheCounty
 * @subpackage  Admin/Forms
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Add Shortcode Copy Field to Publish Metabox
 *
 * @since: 1.0
 */
function walkthecounty_add_shortcode_to_publish_metabox() {

	if ( 'walkthecounty_forms' !== get_post_type() ) {
		return false;
	}
	global $post;

	//Only enqueue scripts for CPT on post type screen
	if ( 'walkthecounty_forms' === $post->post_type ) {
		//Shortcode column with select all input
		$shortcode = sprintf( '[walkthecounty_form id="%s"]', absint( $post->ID ) );
		printf(
			'<div class="misc-pub-section"><button type="button" class="button hint-tooltip hint--top js-walkthecounty-shortcode-button" aria-label="%1$s" data-walkthecounty-shortcode="%2$s"><span class="dashicons dashicons-admin-page"></span> %3$s</button></div>',
			esc_attr( $shortcode ),
			esc_attr( $shortcode ),
			esc_html__( 'Copy Shortcode', 'walkthecounty' )
		);
	}

}

add_action( 'post_submitbox_misc_actions', 'walkthecounty_add_shortcode_to_publish_metabox' );
