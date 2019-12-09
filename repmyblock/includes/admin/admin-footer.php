<?php
/**
 * Admin Footer
 *
 * @package     WalkTheCounty
 * @subpackage  Admin/Footer
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add rating links to the admin dashboard
 *
 * @param string         $footer_text The existing footer text
 *
 * @return      string
 * @since        1.0
 * @global        string $typenow
 *
 */
function walkthecounty_admin_rate_us( $footer_text ) {
	global $typenow;

	$page        = isset( $_GET['page'] ) ? $_GET['page'] : '';
	$show_footer = array( 'walkthecounty-getting-started', 'walkthecounty-changelog', 'walkthecounty-credits' );

	if ( 'walkthecounty_forms' === $typenow || in_array( $page, $show_footer ) ) {
		$rate_text = sprintf(
		/* translators: %s: Link to 5 star rating */
			__( 'If you like <strong>WalkTheCounty</strong> please leave us a %s rating. It takes a minute and helps a lot. Thanks in advance!', 'walkthecounty' ),
			'<a href="https://wordpress.org/support/view/plugin-reviews/walkthecounty?filter=5#postform" target="_blank" class="walkthecounty-rating-link" style="text-decoration:none;" data-rated="' . esc_attr__( 'Thanks :)', 'walkthecounty' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
		);

		return $rate_text;
	} else {
		return $footer_text;
	}
}

add_filter( 'admin_footer_text', 'walkthecounty_admin_rate_us' );
