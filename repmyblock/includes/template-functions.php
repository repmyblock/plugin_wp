<?php
/**
 * Template Functions
 *
 * @package     WalkTheCounty
 * @subpackage  Functions/Templates
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns the path to the WalkTheCounty templates directory
 *
 * @since 1.0
 * @return string
 */
function walkthecounty_get_templates_dir() {
	return WALKTHECOUNTY_PLUGIN_DIR . 'templates';
}

/**
 * Returns the URL to the WalkTheCounty templates directory
 *
 * @since 1.0
 * @return string
 */
function walkthecounty_get_templates_url() {
	return WALKTHECOUNTY_PLUGIN_URL . 'templates';
}

/**
 * Get other templates, passing attributes and including the file.
 *
 * @since 1.6
 *
 * @param string $template_name Template file name.
 * @param array  $args          Passed arguments. Default is empty array().
 * @param string $template_path Template file path. Default is empty.
 * @param string $default_path  Default path. Default is empty.
 */
function walkthecounty_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	if ( ! empty( $args ) && is_array( $args ) ) {
		extract( $args );
	}

	$template_names = "{$template_name}.php";

	$located = walkthecounty_get_locate_template( $template_names, $template_path, $default_path );

	if ( ! file_exists( $located ) ) {
		/* translators: %s: the template */
		WalkTheCounty_Notices::print_frontend_notice( sprintf( __( 'The %s template was not found.', 'walkthecounty' ), $located ), true );

		return;
	}

	// Allow 3rd party plugin filter template file from their plugin.
	$located = apply_filters( 'walkthecounty_get_template', $located, $template_name, $args, $template_path, $default_path );

	/**
	 * Fires in walkthecounty template, before the file is included.
	 *
	 * Allows you to execute code before the file is included.
	 *
	 * @since 1.6
	 *
	 * @param string $template_name Template file name.
	 * @param string $template_path Template file path.
	 * @param string $located       Template file filter by 3rd party plugin.
	 * @param array  $args          Passed arguments.
	 */
	do_action( 'walkthecounty_before_template_part', $template_name, $template_path, $located, $args );

	include( $located );

	/**
	 * Fires in walkthecounty template, after the file is included.
	 *
	 * Allows you to execute code after the file is included.
	 *
	 * @since 1.6
	 *
	 * @param string $template_name Template file name.
	 * @param string $template_path Template file path.
	 * @param string $located       Template file filter by 3rd party plugin.
	 * @param array  $args          Passed arguments.
	 */
	do_action( 'walkthecounty_after_template_part', $template_name, $template_path, $located, $args );
}

/**
 * Retrieves a template part
 *
 * Taken from bbPress.
 *
 * @since 1.0
 *
 * @param string $slug Template part file slug {slug}.php.
 * @param string $name Optional. Template part file name {slug}-{name}.php. Default is null.
 * @param bool   $load If true the template file will be loaded, if it is found.
 *
 * @return string
 */
function walkthecounty_get_template_part( $slug, $name = null, $load = true ) {

	/**
	 * Fires in walkthecounty template part, before the template part is retrieved.
	 *
	 * Allows you to execute code before retrieving the template part.
	 *
	 * @since 1.0
	 *
	 * @param string $slug Template part file slug {slug}.php.
	 * @param string $name Template part file name {slug}-{name}.php.
	 */
	do_action( "get_template_part_{$slug}", $slug, $name );

	// Setup possible parts
	$templates = array();
	if ( isset( $name ) ) {
		$templates[] = $slug . '-' . $name . '.php';
	}
	$templates[] = $slug . '.php';

	// Allow template parts to be filtered
	$templates = apply_filters( 'walkthecounty_get_template_part', $templates, $slug, $name );

	// Return the part that is found
	return walkthecounty_locate_template( $templates, $load, false );
}

/**
 * Retrieve the name of the highest priority template file that exists.
 *
 * Searches in the STYLESHEETPATH before TEMPLATEPATH so that themes which
 * inherit from a parent theme can just overload one file. If the template is
 * not found in either of those, it looks in the theme-compat folder last.
 *
 * Forked from bbPress
 *
 * @since 1.0
 *
 * @param string|array $template_names Template file(s) to search for, in order.
 * @param bool         $load           If true the template file will be loaded if it is found.
 * @param bool         $require_once   Whether to require_once or require. Default true.
 *                                     Has no effect if $load is false.
 *
 * @return string The template filename if one is located.
 */
function walkthecounty_locate_template( $template_names, $load = false, $require_once = true ) {
	// No file found yet
	$located = false;

	$theme_template_paths = walkthecounty_get_theme_template_paths();

	// Try to find a template file
	foreach ( (array) $template_names as $template_name ) {

		// Continue if template is empty
		if ( empty( $template_name ) ) {
			continue;
		}

		// Trim off any slashes from the template name
		$template_name = ltrim( $template_name, '/' );

		// try locating this template file by looping through the template paths
		foreach ( $theme_template_paths as $template_path ) {

			if ( file_exists( $template_path . $template_name ) ) {
				$located = $template_path . $template_name;
				break;
			}
		}

		if ( $located ) {
			break;
		}
	}

	if ( ( true == $load ) && ! empty( $located ) ) {
		load_template( $located, $require_once );
	}

	return $located;
}

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 *        yourtheme        /    $template_path    /    $template_name
 *        yourtheme        /    $template_name
 *        $default_path    /    $template_name
 *
 * @since  2.0.3
 * @access public
 *
 * @param string $template_name
 * @param string $template_path (default: '')
 * @param string $default_path  (default: '')
 *
 * @return string
 */
function walkthecounty_get_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	if ( ! $template_path ) {
		$template_path = walkthecounty_get_theme_template_dir_name() . '/';
	}

	if ( ! $default_path ) {
		$default_path = WALKTHECOUNTY_PLUGIN_DIR . 'templates/';
	}

	// Look within passed path within the theme - this is priority.
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name,
		)
	);

	// Get default template/
	if ( ! $template ) {
		$template = $default_path . $template_name;
	}

	/**
	 * Filter the template
	 *
	 * @since 2.0.3
	 */
	return apply_filters( 'walkthecounty_get_locate_template', $template, $template_name, $template_path );
}

/**
 * Returns a list of paths to check for template locations
 *
 * @since 1.0
 * @return array
 */
function walkthecounty_get_theme_template_paths() {

	$template_dir = walkthecounty_get_theme_template_dir_name();

	$file_paths = array(
		1   => trailingslashit( get_stylesheet_directory() ) . $template_dir,
		10  => trailingslashit( get_template_directory() ) . $template_dir,
		100 => walkthecounty_get_templates_dir(),
	);

	$file_paths = apply_filters( 'walkthecounty_template_paths', $file_paths );

	// sort the file paths based on priority
	ksort( $file_paths, SORT_NUMERIC );

	return array_map( 'trailingslashit', $file_paths );
}

/**
 * Returns the template directory name.
 *
 * Themes can filter this by using the walkthecounty_templates_dir filter.
 *
 * @since 1.0
 * @return string
 */
function walkthecounty_get_theme_template_dir_name() {
	return trailingslashit( apply_filters( 'walkthecounty_templates_dir', 'walkthecounty' ) );
}

/**
 * Adds WalkTheCounty Version to the <head> tag
 *
 * @since 1.0
 * @return void
 */
function walkthecounty_version_in_header() {
	echo '<meta name="generator" content="WalkTheCounty v' . WALKTHECOUNTY_VERSION . '" />' . "\n";
}

add_action( 'wp_head', 'walkthecounty_version_in_header' );

/**
 * Determines if we're currently on the Donations History page.
 *
 * @since 1.0
 * @return bool True if on the Donations History page, false otherwise.
 */
function walkthecounty_is_donation_history_page() {

	$ret = is_page( walkthecounty_get_option( 'history_page' ) );

	return apply_filters( 'walkthecounty_is_donation_history_page', $ret );
}

/**
 * Adds body classes for WalkTheCounty pages
 *
 * @since 1.0
 *
 * @param array $class current classes
 *
 * @return array Modified array of classes
 */
function walkthecounty_add_body_classes( $class ) {
	$classes = (array) $class;

	if ( walkthecounty_is_success_page() ) {
		$classes[] = 'walkthecounty-success';
		$classes[] = 'walkthecounty-page';
	}

	if ( walkthecounty_is_failed_transaction_page() ) {
		$classes[] = 'walkthecounty-failed-transaction';
		$classes[] = 'walkthecounty-page';
	}

	if ( walkthecounty_is_donation_history_page() ) {
		$classes[] = 'walkthecounty-donation-history';
		$classes[] = 'walkthecounty-page';
	}

	if ( walkthecounty_is_test_mode() ) {
		$classes[] = 'walkthecounty-test-mode';
		$classes[] = 'walkthecounty-page';
	}

	// Theme-specific Classes used to prevent conflicts via CSS
	/* @var WP_Theme $current_theme */
	$current_theme = wp_get_theme();

	switch ( $current_theme->get_template() ) {

		case 'Divi':
			$classes[] = 'walkthecounty-divi';
			break;
		case 'Avada':
			$classes[] = 'walkthecounty-avada';
			break;
		case 'twentysixteen':
			$classes[] = 'walkthecounty-twentysixteen';
			break;
		case 'twentyseventeen':
			$classes[] = 'walkthecounty-twentyseventeen';
			break;
		case 'twentynineteen':
			$classes[] = 'walkthecounty-twentynineteen';
			break;

	}

	return array_unique( $classes );
}

add_filter( 'body_class', 'walkthecounty_add_body_classes' );


/**
 * Add Post Class Filter
 *
 * Adds extra post classes for forms
 *
 * @since       1.0
 *
 * @param array        $classes
 * @param string|array $class
 * @param int|string   $post_id
 *
 * @return array
 */
function walkthecounty_add_post_class( $classes, $class = '', $post_id = '' ) {
	if ( ! $post_id || 'walkthecounty_forms' !== get_post_type( $post_id ) ) {
		return $classes;
	}

	//@TODO: Add classes for custom taxonomy and form configurations (multi vs single donations, etc).

	if ( false !== ( $key = array_search( 'hentry', $classes ) ) ) {
		unset( $classes[ $key ] );
	}

	return $classes;
}


add_filter( 'post_class', 'walkthecounty_add_post_class', 20, 3 );

/**
 * Get the placeholder image URL for forms etc
 *
 * @access public
 * @return string
 */
function walkthecounty_get_placeholder_img_src() {

	$placeholder_url = '//placehold.it/600x600&text=' . urlencode( esc_attr__( 'WalkTheCountyWP Placeholder Image', 'walkthecounty' ) );

	return apply_filters( 'walkthecounty_placeholder_img_src', $placeholder_url );
}


/**
 * Global
 */
if ( ! function_exists( 'walkthecounty_output_content_wrapper' ) ) {

	/**
	 * Output the start of the page wrapper.
	 */
	function walkthecounty_output_content_wrapper() {
		walkthecounty_get_template_part( 'global/wrapper-start' );
	}
}
if ( ! function_exists( 'walkthecounty_output_content_wrapper_end' ) ) {

	/**
	 * Output the end of the page wrapper.
	 */
	function walkthecounty_output_content_wrapper_end() {
		walkthecounty_get_template_part( 'global/wrapper-end' );
	}
}

/**
 * Single WalkTheCounty Form
 */
if ( ! function_exists( 'walkthecounty_left_sidebar_pre_wrap' ) ) {
	function walkthecounty_left_sidebar_pre_wrap() {
		echo apply_filters( 'walkthecounty_left_sidebar_pre_wrap', '<div id="walkthecounty-sidebar-left" class="walkthecounty-sidebar walkthecounty-single-form-sidebar-left">' );
	}
}

if ( ! function_exists( 'walkthecounty_left_sidebar_post_wrap' ) ) {
	function walkthecounty_left_sidebar_post_wrap() {
		echo apply_filters( 'walkthecounty_left_sidebar_post_wrap', '</div>' );
	}
}

if ( ! function_exists( 'walkthecounty_get_forms_sidebar' ) ) {
	function walkthecounty_get_forms_sidebar() {
		walkthecounty_get_template_part( 'single-walkthecounty-form/sidebar' );
	}
}

if ( ! function_exists( 'walkthecounty_show_form_images' ) ) {

	/**
	 * Output the donation form featured image.
	 */
	function walkthecounty_show_form_images() {
		if ( walkthecounty_is_setting_enabled( walkthecounty_get_option( 'form_featured_img' ) ) ) {
			walkthecounty_get_template_part( 'single-walkthecounty-form/featured-image' );
		}
	}
}

if ( ! function_exists( 'walkthecounty_template_single_title' ) ) {

	/**
	 * Output the form title.
	 */
	function walkthecounty_template_single_title() {
		walkthecounty_get_template_part( 'single-walkthecounty-form/title' );
	}
}

/**
 * Conditional Functions
 */

if ( ! function_exists( 'is_walkthecounty_form' ) ) {

	/**
	 * is_walkthecounty_form
	 *
	 * Returns true when viewing a single form.
	 *
	 * @since 1.6
	 *
	 * @return bool
	 */
	function is_walkthecounty_form() {
		return is_singular( array( 'walkthecounty_form' ) );
	}
}

if ( ! function_exists( 'is_walkthecounty_category' ) ) {

	/**
	 * is_walkthecounty_category
	 *
	 * Returns true when viewing walkthecounty form category archive.
	 *
	 * @since 1.6
	 *
	 * @param string $term The term slug your checking for.
	 *                     Leave blank to return true on any.
	 *                     Default is blank.
	 *
	 * @return bool
	 */
	function is_walkthecounty_category( $term = '' ) {
		return is_tax( 'walkthecounty_forms_category', $term );
	}
}

if ( ! function_exists( 'is_walkthecounty_tag' ) ) {

	/**
	 * is_walkthecounty_tag
	 *
	 * Returns true when viewing walkthecounty form tag archive.
	 *
	 * @since 1.6
	 *
	 * @param string $term The term slug your checking for.
	 *                     Leave blank to return true on any.
	 *                     Default is blank.
	 *
	 * @return bool
	 */
	function is_walkthecounty_tag( $term = '' ) {
		return is_tax( 'walkthecounty_forms_tag', $term );
	}
}

if ( ! function_exists( 'is_walkthecounty_taxonomy' ) ) {

	/**
	 * is_walkthecounty_taxonomy
	 *
	 * Returns true when viewing a walkthecounty form taxonomy archive.
	 *
	 * @since 1.6
	 *
	 * @return bool
	 */
	function is_walkthecounty_taxonomy() {
		return is_tax( get_object_taxonomies( 'walkthecounty_form' ) );
	}
}
