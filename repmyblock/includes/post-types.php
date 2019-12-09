<?php
/**
 * Post Type Functions
 *
 * @package     WalkTheCounty
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and sets up the Donation Forms (walkthecounty_forms) custom post type
 *
 * @return void
 * @since 1.0
 */
function walkthecounty_setup_post_types() {

	// WalkTheCounty Forms single post and archive options.
	$walkthecounty_forms_singular = walkthecounty_is_setting_enabled( walkthecounty_get_option( 'forms_singular' ) );
	$walkthecounty_forms_archives = walkthecounty_is_setting_enabled( walkthecounty_get_option( 'forms_archives' ) );

	// Enable/Disable walkthecounty_forms links if form is saving.
	if ( WalkTheCounty_Admin_Settings::is_saving_settings() ) {
		if ( isset( $_POST['forms_singular'] ) ) {
			$walkthecounty_forms_singular = walkthecounty_is_setting_enabled( walkthecounty_clean( $_POST['forms_singular'] ) );
			flush_rewrite_rules();
		}

		if ( isset( $_POST['forms_archives'] ) ) {
			$walkthecounty_forms_archives = walkthecounty_is_setting_enabled( walkthecounty_clean( $_POST['forms_archives'] ) );
			flush_rewrite_rules();
		}
	}

	$walkthecounty_forms_slug = defined( 'WALKTHECOUNTY_SLUG' ) ? WALKTHECOUNTY_SLUG : 'donations';
	// Support for old 'WALKTHECOUNTY_FORMS_SLUG' constant
	if ( defined( 'WALKTHECOUNTY_FORMS_SLUG' ) ) {
		$walkthecounty_forms_slug = WALKTHECOUNTY_FORMS_SLUG;
	}

	$walkthecounty_forms_rewrite = defined( 'WALKTHECOUNTY_DISABLE_FORMS_REWRITE' ) && WALKTHECOUNTY_DISABLE_FORMS_REWRITE ? false : array(
		'slug'       => $walkthecounty_forms_slug,
		'with_front' => false,
	);

	$walkthecounty_forms_labels = apply_filters( 'walkthecounty_forms_labels', array(
		'name'               => __( 'Rep My Block Pages', 'walkthecounty' ),
		'singular_name'      => __( 'Rep My Block Page', 'walkthecounty' ),
		'add_new'            => __( 'Add Page', 'walkthecounty' ),
		'add_new_item'       => __( 'Add New Campaign Form', 'walkthecounty' ),
		'edit_item'          => __( 'Edit Campaign Form', 'walkthecounty' ),
		'new_item'           => __( 'New Form', 'walkthecounty' ),
		'all_items'          => __( 'All Forms', 'walkthecounty' ),
		'view_item'          => __( 'View Form', 'walkthecounty' ),
		'search_items'       => __( 'Search Forms', 'walkthecounty' ),
		'not_found'          => __( 'No forms found.', 'walkthecounty' ),
		'not_found_in_trash' => __( 'No forms found in Trash.', 'walkthecounty' ),
		'parent_item_colon'  => '',
		'menu_name'          => apply_filters( 'walkthecounty_menu_name', __( 'Rep My Block', 'walkthecounty' ) ),
		'name_admin_bar'     => apply_filters( 'walkthecounty_name_admin_bar_name', __( 'Rep My Block pages', 'walkthecounty' ) ),
	) );

	// Default walkthecounty_forms supports.
	$walkthecounty_form_supports = array(
		'title',
		'thumbnail',
		'excerpt',
		'revisions',
		'author',
	);

	// Has the user disabled the excerpt?
	if ( ! walkthecounty_is_setting_enabled( walkthecounty_get_option( 'forms_excerpt' ) ) ) {
		unset( $walkthecounty_form_supports[2] );
	}

	// Has user disabled the featured image?
	if ( ! walkthecounty_is_setting_enabled( walkthecounty_get_option( 'form_featured_img' ) ) ) {
		unset( $walkthecounty_form_supports[1] );
		remove_action( 'walkthecounty_before_single_form_summary', 'walkthecounty_show_form_images' );
	}

	$walkthecounty_forms_args = array(
		'labels'          => $walkthecounty_forms_labels,
		'public'          => $walkthecounty_forms_singular,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'show_in_rest'    => true,
		'query_var'       => true,
		'rewrite'         => $walkthecounty_forms_rewrite,
		'map_meta_cap'    => true,
		'capability_type' => 'walkthecounty_form',
		'has_archive'     => $walkthecounty_forms_archives,
		'menu_icon'       => 'dashicons-walkthecounty',
		'hierarchical'    => false,
		'supports'        => apply_filters( 'walkthecounty_forms_supports', $walkthecounty_form_supports ),
	);
	register_post_type( 'walkthecounty_forms', apply_filters( 'walkthecounty_forms_post_type_args', $walkthecounty_forms_args ) );

	/** Donation Post Type */
	$payment_labels = array(
		'name'               => _x( 'Donations', 'post type general name', 'walkthecounty' ),
		'singular_name'      => _x( 'Donation', 'post type singular name', 'walkthecounty' ),
		'add_new'            => __( 'Add New', 'walkthecounty' ),
		'add_new_item'       => __( 'Add New Donation', 'walkthecounty' ),
		'edit_item'          => __( 'Edit Donation', 'walkthecounty' ),
		'new_item'           => __( 'New Donation', 'walkthecounty' ),
		'all_items'          => __( 'All Donations', 'walkthecounty' ),
		'view_item'          => __( 'View Donation', 'walkthecounty' ),
		'search_items'       => __( 'Search Donations', 'walkthecounty' ),
		'not_found'          => __( 'No donations found.', 'walkthecounty' ),
		'not_found_in_trash' => __( 'No donations found in Trash.', 'walkthecounty' ),
		'parent_item_colon'  => '',
		'menu_name'          => __( 'Donations', 'walkthecounty' ),
	);

	$payment_args = array(
		'labels'          => apply_filters( 'walkthecounty_payment_labels', $payment_labels ),
		'public'          => false,
		'query_var'       => false,
		'rewrite'         => false,
		'map_meta_cap'    => true,
		'capability_type' => 'walkthecounty_payment',
		'supports'        => array( 'title' ),
		'can_export'      => true,
	);
	register_post_type( 'walkthecounty_payment', $payment_args );

}

add_action( 'init', 'walkthecounty_setup_post_types', 1 );


/**
 * WalkTheCounty Setup Taxonomies
 *
 * Registers the custom taxonomies for the walkthecounty_forms custom post type
 *
 * @return void
 * @since      1.0
 */
function walkthecounty_setup_taxonomies() {

	$slug = defined( 'WALKTHECOUNTY_FORMS_SLUG' ) ? WALKTHECOUNTY_FORMS_SLUG : 'donations';

	/** Categories */
	$category_labels = array(
		'name'              => _x( 'Form Categories', 'taxonomy general name', 'walkthecounty' ),
		'singular_name'     => _x( 'Category', 'taxonomy singular name', 'walkthecounty' ),
		'search_items'      => __( 'Search Categories', 'walkthecounty' ),
		'all_items'         => __( 'All Categories', 'walkthecounty' ),
		'parent_item'       => __( 'Parent Category', 'walkthecounty' ),
		'parent_item_colon' => __( 'Parent Category:', 'walkthecounty' ),
		'edit_item'         => __( 'Edit Category', 'walkthecounty' ),
		'update_item'       => __( 'Update Category', 'walkthecounty' ),
		'add_new_item'      => __( 'Add New Category', 'walkthecounty' ),
		'new_item_name'     => __( 'New Category Name', 'walkthecounty' ),
		'menu_name'         => __( 'Categories', 'walkthecounty' ),
	);

	$category_args = apply_filters( 'walkthecounty_forms_category_args', array(
			'hierarchical' => true,
			'labels'       => apply_filters( 'walkthecounty_forms_category_labels', $category_labels ),
			'show_ui'      => true,
			'query_var'    => 'walkthecounty_forms_category',
			'rewrite'      => array(
				'slug'         => $slug . '/category',
				'with_front'   => false,
				'hierarchical' => true,
			),
			'capabilities' => array(
				'manage_terms' => 'manage_walkthecounty_form_terms',
				'edit_terms'   => 'edit_walkthecounty_form_terms',
				'assign_terms' => 'assign_walkthecounty_form_terms',
				'delete_terms' => 'delete_walkthecounty_form_terms',
			),
		)
	);

	/** Tags */
	$tag_labels = array(
		'name'                  => _x( 'Form Tags', 'taxonomy general name', 'walkthecounty' ),
		'singular_name'         => _x( 'Tag', 'taxonomy singular name', 'walkthecounty' ),
		'search_items'          => __( 'Search Tags', 'walkthecounty' ),
		'all_items'             => __( 'All Tags', 'walkthecounty' ),
		'parent_item'           => __( 'Parent Tag', 'walkthecounty' ),
		'parent_item_colon'     => __( 'Parent Tag:', 'walkthecounty' ),
		'edit_item'             => __( 'Edit Tag', 'walkthecounty' ),
		'update_item'           => __( 'Update Tag', 'walkthecounty' ),
		'add_new_item'          => __( 'Add New Tag', 'walkthecounty' ),
		'new_item_name'         => __( 'New Tag Name', 'walkthecounty' ),
		'menu_name'             => __( 'Tags', 'walkthecounty' ),
		'choose_from_most_used' => __( 'Choose from most used tags.', 'walkthecounty' ),
	);

	$tag_args = apply_filters( 'walkthecounty_forms_tag_args', array(
			'hierarchical' => false,
			'labels'       => apply_filters( 'walkthecounty_forms_tag_labels', $tag_labels ),
			'show_ui'      => true,
			'query_var'    => 'walkthecounty_forms_tag',
			'rewrite'      => array( 'slug' => $slug . '/tag', 'with_front' => false, 'hierarchical' => true ),
			'capabilities' => array(
				'manage_terms' => 'manage_walkthecounty_form_terms',
				'edit_terms'   => 'edit_walkthecounty_form_terms',
				'assign_terms' => 'assign_walkthecounty_form_terms',
				'delete_terms' => 'delete_walkthecounty_form_terms',
			),
		)
	);

	// Does the user want category?
	$enable_category = walkthecounty_is_setting_enabled( walkthecounty_get_option( 'categories', 'disabled' ) );

	// Does the user want tag?
	$enable_tag = walkthecounty_is_setting_enabled( walkthecounty_get_option( 'tags', 'disabled' ) );

	// Enable/Disable category and tag if form is saving.
	if ( WalkTheCounty_Admin_Settings::is_saving_settings() ) {
		if ( isset( $_POST['categories'] ) ) {
			$enable_category = walkthecounty_is_setting_enabled( walkthecounty_clean( $_POST['categories'] ) );
			flush_rewrite_rules();
		}

		if ( isset( $_POST['tags'] ) ) {
			$enable_tag = walkthecounty_is_setting_enabled( walkthecounty_clean( $_POST['tags'] ) );
			flush_rewrite_rules();
		}
	}

	if ( $enable_category ) {
		register_taxonomy( 'walkthecounty_forms_category', array( 'walkthecounty_forms' ), $category_args );
		register_taxonomy_for_object_type( 'walkthecounty_forms_category', 'walkthecounty_forms' );
	}

	if ( $enable_tag ) {
		register_taxonomy( 'walkthecounty_forms_tag', array( 'walkthecounty_forms' ), $tag_args );
		register_taxonomy_for_object_type( 'walkthecounty_forms_tag', 'walkthecounty_forms' );
	}
}

add_action( 'init', 'walkthecounty_setup_taxonomies', 0 );


/**
 * Get Default Form Labels
 *
 * @return array $defaults Default labels
 * @since 1.0
 */
function walkthecounty_get_default_form_labels() {
	$defaults = array(
		'singular' => __( 'Form', 'walkthecounty' ),
		'plural'   => __( 'Forms', 'walkthecounty' ),
	);

	return apply_filters( 'walkthecounty_default_form_name', $defaults );
}

/**
 * Get Singular Forms Label
 *
 * @param bool $lowercase
 *
 * @return string $defaults['singular'] Singular label
 * @since 1.0
 *
 */
function walkthecounty_get_forms_label_singular( $lowercase = false ) {
	$defaults = walkthecounty_get_default_form_labels();

	return ( $lowercase ) ? strtolower( $defaults['singular'] ) : $defaults['singular'];
}

/**
 * Get Plural Forms Label
 *
 * @return string $defaults['plural'] Plural label
 * @since 1.0
 */
function walkthecounty_get_forms_label_plural( $lowercase = false ) {
	$defaults = walkthecounty_get_default_form_labels();

	return ( $lowercase ) ? strtolower( $defaults['plural'] ) : $defaults['plural'];
}

/**
 * Change default "Enter title here" input
 *
 * @param string $title Default title placeholder text
 *
 * @return string $title New placeholder text
 * @since 1.0
 *
 */
function walkthecounty_change_default_title( $title ) {
	// If a frontend plugin uses this filter (check extensions before changing this function)
	if ( ! is_admin() ) {
		$title = __( 'Enter form title here', 'walkthecounty' );

		return $title;
	}

	$screen = get_current_screen();

	if ( 'walkthecounty_forms' == $screen->post_type ) {
		$title = __( 'Enter form title here', 'walkthecounty' );
	}

	return $title;
}

add_filter( 'enter_title_here', 'walkthecounty_change_default_title' );

/**
 * Registers Custom Post Statuses which are used by the Payments
 *
 * @return void
 * @since 1.0
 */
function walkthecounty_register_post_type_statuses() {
	// Payment Statuses
	register_post_status( 'refunded', array(
		'label'                     => __( 'Refunded', 'walkthecounty' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Refunded <span class="count">(%s)</span>', 'Refunded <span class="count">(%s)</span>', 'walkthecounty' ),
	) );
	register_post_status( 'failed', array(
		'label'                     => __( 'Failed', 'walkthecounty' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Failed <span class="count">(%s)</span>', 'Failed <span class="count">(%s)</span>', 'walkthecounty' ),
	) );
	register_post_status( 'revoked', array(
		'label'                     => __( 'Revoked', 'walkthecounty' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Revoked <span class="count">(%s)</span>', 'Revoked <span class="count">(%s)</span>', 'walkthecounty' ),
	) );
	register_post_status( 'cancelled', array(
		'label'                     => __( 'Cancelled', 'walkthecounty' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Cancelled <span class="count">(%s)</span>', 'Cancelled <span class="count">(%s)</span>', 'walkthecounty' ),
	) );
	register_post_status( 'abandoned', array(
		'label'                     => __( 'Abandoned', 'walkthecounty' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Abandoned <span class="count">(%s)</span>', 'Abandoned <span class="count">(%s)</span>', 'walkthecounty' ),
	) );
	register_post_status( 'processing', array(
		'label'                     => _x( 'Processing', 'Processing payment status', 'walkthecounty' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Processing <span class="count">(%s)</span>', 'Processing <span class="count">(%s)</span>', 'walkthecounty' )
	) );

	register_post_status( 'preapproval', array(
		'label'                     => _x( 'Preapproval', 'Preapproval payment status', 'walkthecounty' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Preapproval <span class="count">(%s)</span>', 'Preapproval <span class="count">(%s)</span>', 'walkthecounty' ),
	) );

}

add_action( 'init', 'walkthecounty_register_post_type_statuses' );

/**
 * Updated Messages
 *
 * Returns an array of with all updated messages.
 *
 * @param array $messages Post updated message
 *
 * @return array $messages New post updated messages
 * @since 1.0
 *
 */
function walkthecounty_updated_messages( $messages ) {
	global $post, $post_ID;

	if ( ! walkthecounty_is_setting_enabled( walkthecounty_get_option( 'forms_singular' ) ) ) {

		$messages['walkthecounty_forms'] = array(
			1 => __( 'Form updated.', 'walkthecounty' ),
			4 => __( 'Form updated.', 'walkthecounty' ),
			6 => __( 'Form published.', 'walkthecounty' ),
			7 => __( 'Form saved.', 'walkthecounty' ),
			8 => __( 'Form submitted.', 'walkthecounty' ),
		);

	} else {

		$messages['walkthecounty_forms'] = array(
			1 => sprintf( '%1$s <a href="%2$s">%3$s</a>', __( 'Form updated.', 'walkthecounty' ), get_permalink( $post_ID ), __( 'View Form', 'walkthecounty' ) ),
			4 => sprintf( '%1$s <a href="%2$s">%3$s</a>', __( 'Form updated.', 'walkthecounty' ), get_permalink( $post_ID ), __( 'View Form', 'walkthecounty' ) ),
			6 => sprintf( '%1$s <a href="%2$s">%3$s</a>', __( 'Form published.', 'walkthecounty' ), get_permalink( $post_ID ), __( 'View Form', 'walkthecounty' ) ),
			7 => sprintf( '%1$s <a href="%2$s">%3$s</a>', __( 'Form saved.', 'walkthecounty' ), get_permalink( $post_ID ), __( 'View Form', 'walkthecounty' ) ),
			8 => sprintf( '%1$s <a href="%2$s">%3$s</a>', __( 'Form submitted.', 'walkthecounty' ), get_permalink( $post_ID ), __( 'View Form', 'walkthecounty' ) ),
		);

	}

	return $messages;
}

add_filter( 'post_updated_messages', 'walkthecounty_updated_messages' );

/**
 * Ensure post thumbnail support is turned on
 */
function walkthecounty_add_thumbnail_support() {
	if ( ! walkthecounty_is_setting_enabled( walkthecounty_get_option( 'form_featured_img' ) ) ) {
		return;
	}

	if ( ! current_theme_supports( 'post-thumbnails' ) ) {
		add_theme_support( 'post-thumbnails' );
	}

	add_post_type_support( 'walkthecounty_forms', 'thumbnail' );
}

add_action( 'after_setup_theme', 'walkthecounty_add_thumbnail_support', 10 );

/**
 * WalkTheCounty Sidebars
 *
 * This option adds WalkTheCounty sidebars; registered late so it display last in list
 */
function walkthecounty_widgets_init() {

	// Single WalkTheCounty Forms (disabled if single turned off in settings)
	if (
		walkthecounty_is_setting_enabled( walkthecounty_get_option( 'forms_singular' ) )
		&& walkthecounty_is_setting_enabled( walkthecounty_get_option( 'form_sidebar' ) )
	) {

		register_sidebar( apply_filters( 'walkthecounty_forms_single_sidebar', array(
			'name'          => __( 'WalkTheCountyWP Single Form Sidebar', 'walkthecounty' ),
			'id'            => 'walkthecounty-forms-sidebar',
			'description'   => __( 'Widgets in this area will be shown on the single WalkTheCountyWP forms aside area. This sidebar will not display for embedded forms.', 'walkthecounty' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widgettitle widget-title">',
			'after_title'   => '</h3>',
		) ) );

	}
}

add_action( 'widgets_init', 'walkthecounty_widgets_init', 999 );


/**
 * Remove "Quick Edit" for the walkthecounty_forms CPT.
 *
 * @param array $actions
 * @param null  $post
 *
 * @return array
 * @since 2.3.0
 *
 */
function walkthecounty_forms_disable_quick_edit( $actions = array(), $post = null ) {

	// Abort if the post type is not "walkthecounty_forms".
	if ( ! is_post_type_archive( 'walkthecounty_forms' ) ) {
		return $actions;
	}

	// Remove the Quick Edit link.
	if ( isset( $actions['inline hide-if-no-js'] ) ) {
		unset( $actions['inline hide-if-no-js'] );
	}

	// Return the set of links without Quick Edit.
	return $actions;

}

add_filter( 'post_row_actions', 'walkthecounty_forms_disable_quick_edit', 10, 2 );

/**
 * Removes the screen options pull down. It is reset later in a different position.
 *
 * @param bool      $display_boolean  Whether to display screen options.
 * @param WP_Screen $wp_screen_object The screen object.
 *
 * @return bool Whether to display screen options.
 * @since 2.5.0
 *
 */
function walkthecounty_remove_screen_options( $display_boolean, $wp_screen_object ) {

	if ( false !== strpos( $wp_screen_object->id, 'walkthecounty' ) ) {
		return false;
	}

	// Don't mess with other screens.
	return $display_boolean;
}

//add_filter( 'screen_options_show_screen', 'walkthecounty_remove_screen_options', 10, 2 );

/**
 * Renders the screen options back after admin bar to ensure it pushes down the banner rather than overlaps them as is default in WordPress.
 *
 * @since  2.5.0
 */
function walkthecounty_render_screen_options() {
	if( ! is_admin() ) {
		return;
	}

	$current_screen = get_current_screen();

	if ( empty ( $current_screen ) ) {
		return;
	}

	if ( false !== strpos( $current_screen->id, 'walkthecounty' ) ) {
		// Render Screen Options above the banner.
		$current_screen->render_screen_meta();
	}
}

add_action( 'wp_after_admin_bar_render', 'walkthecounty_render_screen_options' );
