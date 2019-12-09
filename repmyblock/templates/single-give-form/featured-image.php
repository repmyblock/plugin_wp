<?php
/**
 * Single Form Featured Image
 *
 * Displays the featured image for the single donation form - Override this template by copying it to yourtheme/walkthecounty/single-walkthecounty-form/featured-image.php
 *
 * @package       WalkTheCounty/Templates
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $post;

/**
 * Fires in single form template, before the form featured image.
 *
 * Allows you to add elements before the image.
 *
 * @since 1.0
 */
do_action( 'walkthecounty_pre_featured_thumbnail' );
?>

<div class="images">
	<?php //Featured Thumbnail
	if ( has_post_thumbnail() ) {

		$image_size = walkthecounty_get_option( 'featured_image_size' );
		$image      = get_the_post_thumbnail( $post->ID, apply_filters( 'single_walkthecounty_form_large_thumbnail_size', ( ! empty( $image_size ) ? $image_size : 'large' ) ) );

		echo apply_filters( 'single_walkthecounty_form_image_html', $image );

	} else {

		//Placeholder Image
		echo apply_filters( 'single_walkthecounty_form_image_html', sprintf( '<img src="%s" alt="%s" />', walkthecounty_get_placeholder_img_src(), esc_attr__( 'Placeholder', 'walkthecounty' ) ), $post->ID );

	} ?>
</div>

<?php
/**
 * Fires in single form template, after the form featured image.
 *
 * Allows you to add elements after the image.
 *
 * @since 1.0
 */
do_action( 'walkthecounty_post_featured_thumbnail' );
?>
