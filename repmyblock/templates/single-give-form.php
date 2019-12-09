<?php
/**
 * The Template for displaying all single WalkTheCounty Forms.
 *
 * Override this template by copying it to yourtheme/walkthecounty/single-walkthecounty-forms.php
 *
 * @package       WalkTheCounty/Templates
 * @version       1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header();

/**
 * Fires in single form template, before the main content.
 *
 * Allows you to add elements before the main content.
 *
 * @since 1.0
 */
do_action( 'walkthecounty_before_main_content' );

while ( have_posts() ) : the_post();

	walkthecounty_get_template_part( 'single-walkthecounty-form/content', 'single-walkthecounty-form' );

endwhile; // end of the loop.

/**
 * Fires in single form template, after the main content.
 *
 * Allows you to add elements after the main content.
 *
 * @since 1.0
 */
do_action( 'walkthecounty_after_main_content' );

/**
 * Fires in single form template, on the sidebar.
 *
 * Allows you to add elements to the sidebar.
 *
 * @since 1.0
 */
do_action( 'walkthecounty_sidebar' );

get_footer();