<?php
/**
 * The template for displaying form content in the single-walkthecounty-form.php template
 *
 * Override this template by copying it to yourtheme/walkthecounty/single-walkthecounty-form/content-single-walkthecounty-form.php
 *
 * @package       WalkTheCounty/Templates
 * @version       1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Fires in single form template, before the form.
 *
 * Allows you to add elements before the form.
 *
 * @since 1.0
 */
do_action( 'walkthecounty_before_single_form' );

if ( post_password_required() ) {
	echo get_the_password_form();
	return;
}
?>

	<div id="walkthecounty-form-<?php the_ID(); ?>-content" <?php post_class(); ?>>

		<?php
		/**
		 * Fires in single form template, before the form summary.
		 *
		 * Allows you to add elements before the form summary.
		 *
		 * @since 1.0
		 */
		do_action( 'walkthecounty_before_single_form_summary' );
		?>

		<div class="<?php echo apply_filters( 'walkthecounty_forms_single_summary_classes', 'summary entry-summary' ); ?>">

			<?php
			/**
			 * Fires in single form template, to the form summary.
			 *
			 * Allows you to add elements to the form summary.
			 *
			 * @since 1.0
			 */
			do_action( 'walkthecounty_single_form_summary' );
			?>

		</div>
		<!-- .summary -->

		<?php
		/**
		 * Fires in single form template, after the form summary.
		 *
		 * Allows you to add elements after the form summary.
		 *
		 * @since 1.0
		 */
		do_action( 'walkthecounty_after_single_form_summary' );
		?>

	</div><!-- #walkthecounty-form-<?php the_ID(); ?> -->

<?php
/**
 * Fires in single form template, after the form.
 *
 * Allows you to add elements after the form.
 *
 * @since 1.0
 */
do_action( 'walkthecounty_after_single_form' );
?>
