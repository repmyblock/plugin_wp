<?php
/**
 * Dashboard Columns
 *
 * @package     WALKTHECOUNTY
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
 * WalkTheCounty Forms Columns
 *
 * Defines the custom columns and their order
 *
 * @since 1.0
 *
 * @param array $walkthecounty_form_columns Array of forms columns
 *
 * @return array $form_columns Updated array of forms columns
 *  Post Type List Table
 */
function walkthecounty_form_columns( $walkthecounty_form_columns ) {

	// Standard columns
	$walkthecounty_form_columns = array(
		'cb'            => '<input type="checkbox"/>',
		'title'         => __( 'Name', 'walkthecounty' ),
		'form_category' => __( 'Categories', 'walkthecounty' ),
		'form_tag'      => __( 'Tags', 'walkthecounty' ),
		'price'         => __( 'Amount', 'walkthecounty' ),
		'goal'          => __( 'Goal', 'walkthecounty' ),
		'donations'     => __( 'Donations', 'walkthecounty' ),
		'earnings'      => __( 'Income', 'walkthecounty' ),
		'shortcode'     => __( 'Shortcode', 'walkthecounty' ),
		'date'          => __( 'Date', 'walkthecounty' ),
	);

	// Does the user want categories / tags?
	if ( ! walkthecounty_is_setting_enabled( walkthecounty_get_option( 'categories', 'disabled' ) ) ) {
		unset( $walkthecounty_form_columns['form_category'] );
	}
	if ( ! walkthecounty_is_setting_enabled( walkthecounty_get_option( 'tags', 'disabled' ) ) ) {
		unset( $walkthecounty_form_columns['form_tag'] );
	}

	return apply_filters( 'walkthecounty_forms_columns', $walkthecounty_form_columns );
}

add_filter( 'manage_edit-walkthecounty_forms_columns', 'walkthecounty_form_columns' );

/**
 * Render WalkTheCounty Form Columns
 *
 * @since 1.0
 *
 * @param string $column_name Column name
 * @param int    $post_id     WalkTheCounty Form (Post) ID
 *
 * @return void
 */
function walkthecounty_render_form_columns( $column_name, $post_id ) {
	if ( get_post_type( $post_id ) == 'walkthecounty_forms' ) {

		switch ( $column_name ) {
			case 'form_category':
				echo get_the_term_list( $post_id, 'walkthecounty_forms_category', '', ', ', '' );
				break;
			case 'form_tag':
				echo get_the_term_list( $post_id, 'walkthecounty_forms_tag', '', ', ', '' );
				break;
			case 'price':
				if ( walkthecounty_has_variable_prices( $post_id ) ) {
					echo walkthecounty_price_range( $post_id );
				} else {
					echo walkthecounty_price( $post_id, false );
					printf( '<input type="hidden" class="formprice-%1$s" value="%2$s" />', esc_attr( $post_id ), esc_attr( walkthecounty_get_form_price( $post_id ) ) );
				}
				break;
			case 'goal':
				if ( walkthecounty_is_setting_enabled( walkthecounty_get_meta( $post_id, '_walkthecounty_goal_option', true ) ) ) {

					echo walkthecounty_admin_form_goal_stats( $post_id );

				} else {
					_e( 'No Goal Set', 'walkthecounty' );
				}

				printf(
					'<input type="hidden" class="formgoal-%1$s" value="%2$s" />',
					esc_attr( $post_id ),
					walkthecounty_get_form_goal( $post_id )
				);

				break;
			case 'donations':
				if ( current_user_can( 'view_walkthecounty_form_stats', $post_id ) ) {
					printf(
						'<a href="%1$s">%2$s</a>',
						esc_url( admin_url( 'edit.php?post_type=walkthecounty_forms&page=walkthecounty-payment-history&form_id=' . $post_id ) ),
						walkthecounty_get_form_sales_stats( $post_id )
					);
				} else {
					echo '-';
				}
				break;
			case 'earnings':
				if ( current_user_can( 'view_walkthecounty_form_stats', $post_id ) ) {
					printf(
						'<a href="%1$s">%2$s</a>',
						esc_url( admin_url( 'edit.php?post_type=walkthecounty_forms&page=walkthecounty-reports&tab=forms&form-id=' . $post_id ) ),
						walkthecounty_currency_filter( walkthecounty_format_amount( walkthecounty_get_form_earnings_stats( $post_id ), array( 'sanitize' => false ) ) )
					);
				} else {
					echo '-';
				}
				break;
			case 'shortcode':
				$shortcode = sprintf( '[walkthecounty_form id="%s"]', absint( $post_id ) );
				printf(
					'<button type="button" class="button hint-tooltip hint--top js-walkthecounty-shortcode-button" aria-label="%1$s" data-walkthecounty-shortcode="%2$s"><span class="dashicons dashicons-admin-page"></span> %3$s</button>',
					esc_attr( $shortcode ),
					esc_attr( $shortcode ),
					esc_html__( 'Copy Shortcode', 'walkthecounty' )
				);
				break;
		}// End switch().
	}// End if().
}

add_action( 'manage_posts_custom_column', 'walkthecounty_render_form_columns', 10, 2 );

/**
 * Registers the sortable columns in the list table
 *
 * @since 1.0
 *
 * @param array $columns Array of the columns
 *
 * @return array $columns Array of sortable columns
 */
function walkthecounty_sortable_form_columns( $columns ) {
	$columns['price']     = 'amount';
	$columns['sales']     = 'sales';
	$columns['earnings']  = 'earnings';
	$columns['goal']      = 'goal';
	$columns['donations'] = 'donations';

	return $columns;
}

add_filter( 'manage_edit-walkthecounty_forms_sortable_columns', 'walkthecounty_sortable_form_columns' );

/**
 * Sorts Columns in the Forms List Table
 *
 * @since 1.0
 *
 * @param array $vars Array of all the sort variables.
 *
 * @return array $vars Array of all the sort variables.
 */
function walkthecounty_sort_forms( $vars ) {
	// Check if we're viewing the "walkthecounty_forms" post type.
	if ( ! isset( $vars['post_type'] ) || ! isset( $vars['orderby'] ) || 'walkthecounty_forms' !== $vars['post_type'] ) {
		return $vars;
	}

	switch ( $vars['orderby'] ) {
		// Check if 'orderby' is set to "sales".
		case 'sales':
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => '_walkthecounty_form_sales',
					'orderby'  => 'meta_value_num',
				)
			);
			break;

		// Check if "orderby" is set to "earnings".
		case 'earnings':
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => '_walkthecounty_form_earnings',
					'orderby'  => 'meta_value_num',
				)
			);
			break;

		// Check if "orderby" is set to "price/amount".
		case 'amount':
			$multi_level_meta_key = ( 'asc' === $vars['order'] ) ? '_walkthecounty_levels_minimum_amount' : '_walkthecounty_levels_maximum_amount';

			$vars['orderby']    = 'meta_value_num';
			$vars['meta_query'] = array(
				'relation' => 'OR',
				array(
					'key'  => $multi_level_meta_key,
					'type' => 'NUMERIC',
				),
				array(
					'key'  => '_walkthecounty_set_price',
					'type' => 'NUMERIC',
				),
			);

			break;

		// Check if "orderby" is set to "goal".
		case 'goal':
			$meta_key = walkthecounty_has_upgrade_completed( 'v240_update_form_goal_progress' )
				? '_walkthecounty_form_goal_progress'
				: '_walkthecounty_set_goal'; // Backward compatibility

			$vars = array_merge(
				$vars,
				array(
					'meta_key' => $meta_key,
					'orderby'  => 'meta_value_num',
				)
			);
			break;

		// Check if "orderby" is set to "donations".
		case 'donations':
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => '_walkthecounty_form_sales',
					'orderby'  => 'meta_value_num',
				)
			);
			break;
	}// End switch().

	return $vars;
}

/**
 * Sets restrictions on author of Forms List Table
 *
 * @since  1.0
 *
 * @param  array $vars Array of all sort variables.
 *
 * @return array       Array of all sort variables.
 */
function walkthecounty_filter_forms( $vars ) {
	if ( isset( $vars['post_type'] ) && 'walkthecounty_forms' == $vars['post_type'] ) {

		// If an author ID was passed, use it
		if ( isset( $_REQUEST['author'] ) && ! current_user_can( 'view_walkthecounty_reports' ) ) {

			$author_id = $_REQUEST['author'];
			if ( (int) $author_id !== get_current_user_id() ) {
				wp_die(
					esc_html__( 'You do not have permission to view this data.', 'walkthecounty' ), esc_html__( 'Error', 'walkthecounty' ), array(
						'response' => 403,
					)
				);
			}
			$vars = array_merge(
				$vars,
				array(
					'author' => get_current_user_id(),
				)
			);

		}
	}

	return $vars;
}

/**
 * Form Load
 *
 * Sorts the form columns.
 *
 * @since 1.0
 * @return void
 */
function walkthecounty_forms_load() {
	add_filter( 'request', 'walkthecounty_sort_forms' );
	add_filter( 'request', 'walkthecounty_filter_forms' );
}

add_action( 'load-edit.php', 'walkthecounty_forms_load', 9999 );

/**
 * Remove Forms Month Filter
 *
 * Removes the default drop down filter for forms by date.
 *
 * @since  1.0
 *
 * @param array $dates   The preset array of dates.
 *
 * @global      $typenow The post type we are viewing.
 * @return array Empty array disables the dropdown.
 */
function walkthecounty_remove_month_filter( $dates ) {
	global $typenow;

	if ( $typenow == 'walkthecounty_forms' ) {
		$dates = array();
	}

	return $dates;
}

add_filter( 'months_dropdown_results', 'walkthecounty_remove_month_filter', 99 );

/**
 * Updates price when saving post
 *
 * @since 1.0
 * @since 2.1.4 If the donation amount is less than the Minimum amount then set the donation amount as Donation minimum amount.
 *
 * @param int $post_id Download (Post) ID
 *
 * @return int|null
 */
function walkthecounty_price_save_quick_edit( $post_id ) {
	if ( ! isset( $_POST['post_type'] ) || 'walkthecounty_forms' !== $_POST['post_type'] ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}

	if ( isset( $_REQUEST['_walkthecounty_regprice'] ) ) {
		walkthecounty_update_meta( $post_id, '_walkthecounty_set_price', walkthecounty_sanitize_amount_for_db( strip_tags( stripslashes( $_REQUEST['_walkthecounty_regprice'] ) ) ) );
	}

	// Override the Donation minimum amount.
	if (
		isset( $_REQUEST['_walkthecounty_custom_amount'], $_REQUEST['_walkthecounty_set_price'], $_REQUEST['_walkthecounty_price_option'], $_REQUEST['_walkthecounty_custom_amount_range'] )
		&& 'set' === $_REQUEST['_walkthecounty_price_option']
		&& walkthecounty_is_setting_enabled( $_REQUEST['_walkthecounty_custom_amount'] )
		&& walkthecounty_maybe_sanitize_amount( $_REQUEST['_walkthecounty_set_price'] ) < walkthecounty_maybe_sanitize_amount( $_REQUEST['_walkthecounty_custom_amount_range']['minimum'] )
	) {
		walkthecounty_update_meta( $post_id, '_walkthecounty_custom_amount_range_minimum', walkthecounty_sanitize_amount_for_db( $_REQUEST['_walkthecounty_set_price'] ) );
	}
}

add_action( 'save_post', 'walkthecounty_price_save_quick_edit' );

/**
 * Function is used to filter the query for search result.
 *
 * @since 2.4.0
 *
 * @param $wp WP WordPress environment instance (passed by reference).
 */
function walkthecounty_form_search_query_filter( $wp ) {

	if (
		isset( $wp->query_vars['post_type'] )
		 && 'walkthecounty_forms' == $wp->query_vars['post_type']
		&& isset( $_GET['walkthecounty-forms-goal-filter'] )
	) {

		$wp->query_vars['date_query'] =
			array(
				'after'     => ! empty( $_GET['start-date'] ) ? date( 'Y-m-d', strtotime( walkthecounty_clean( $_GET['start-date'] ) ) ) : false,
				'before'    => ! empty( $_GET['end-date'] ) ? date( 'Y-m-d 23:59:59 ', strtotime( walkthecounty_clean( $_GET['end-date'] ) ) ) : false,
				'inclusive' => true,
			);
		switch ( $_GET['walkthecounty-forms-goal-filter'] ) {
			case 'goal_in_progress':
				$wp->query_vars['meta_query'] =
					array(
						'relation' => 'AND',
						array(
							'key'     => '_walkthecounty_form_goal_progress',
							'value'   => array( 1, 99 ),
							'compare' => 'BETWEEN',
							'type'    => 'NUMERIC',
						),
					);

				break;
			case 'goal_achieved':
				$wp->query_vars['meta_query'] =
					array(
						'relation' => 'AND',
						array(
							'key'     => '_walkthecounty_form_goal_progress',
							'value'   => 100,
							'compare' => '>=',
							'type'    => 'NUMERIC',
						),
					);
				break;
			case 'goal_not_set':
				$wp->query_vars['meta_query'] =
					array(
						'relation' => 'OR',
						array(
							'key'     => '_walkthecounty_goal_option',
							'value'   => 'disabled',
							'compare' => '=',
						),
						array(
							'key'     => '_walkthecounty_goal_option',
							'compare' => 'NOT EXISTS',
						),
					);
				break;
		}
	}
}

add_action( 'parse_request', 'walkthecounty_form_search_query_filter' );

/**
 * function is used to search walkthecounty forms by ID or title.
 *
 * @since 2.4.0
 *
 * @param $query WP_Query the WP_Query instance (passed by reference).
 */

function walkthecounty_search_form_by_id( $query ) {
	// Verify that we are on the walkthecounty forms list page.
	if (
		empty( $query->query_vars['post_type'] )
		|| 'walkthecounty_forms' !== $query->query_vars['post_type']
	) {
		return;
	}

	if ( '' !== $query->query_vars['s'] && is_search() ) {
		if ( absint( $query->query_vars['s'] ) ) {
			// Set the post id value
			$query->set( 'p', $query->query_vars['s'] );
			// Reset the search value
			$query->set( 's', '' );
		}
	}
}

add_filter( 'pre_get_posts', 'walkthecounty_search_form_by_id' );

/**
 * Outputs advanced filter html in WalkTheCounty forms list admin screen.
 *
 * @sicne 2.4.0
 *
 * @param $which
 */
function walkthecounty_forms_advanced_filter( $which ) {
	/* @var stdClass $screen */
	$screen = get_current_screen();

	if ( 'edit' !== $screen->parent_base || 'walkthecounty_forms' !== $screen->post_type ) {
		return;
	}

	// Apply this only on a specific post type
	if ( 'top' !== $which ) {
		return;
	}

	$start_date             = isset( $_GET['start-date'] ) ? strtotime( walkthecounty_clean( $_GET['start-date'] ) ) : '';
	$end_date               = isset( $_GET['end-date'] ) ? strtotime( walkthecounty_clean( $_GET['end-date'] ) ) : '';
	$search                 = isset( $_GET['s'] ) ? walkthecounty_clean( $_GET['s'] ) : '';
	$walkthecounty_forms_goal_filter = isset( $_GET['walkthecounty-forms-goal-filter'] ) ? $_GET['walkthecounty-forms-goal-filter'] : '';
	?>
	<div id="walkthecounty-forms-advanced-filter" class="walkthecounty-filters">
		<div class="walkthecounty-filter walkthecounty-filter-search">
			<input type="text" id="walkthecounty-forms-search-input" placeholder="<?php _e( 'Form Name or ID', 'walkthecounty' ); ?>" name="s" value="<?php echo $search; ?>">
			<?php
			submit_button(
				__( 'Search', 'walkthecounty' ), 'button', false, false, array(
					'ID' => 'form-search-submit',
				)
			);
			?>
		</div>
		<div id="walkthecounty-payment-date-filters">
			<div class="walkthecounty-filter walkthecounty-filter-half">
				<label for="start-date"
					   class="walkthecounty-start-date-label"><?php _e( 'Start Date', 'walkthecounty' ); ?></label>
				<input type="text"
				       id="start-date"
				       name="start-date"
				       class="walkthecounty_datepicker"
				       autocomplete="off"
					   value="<?php echo $start_date ? date_i18n( walkthecounty_date_format(), $start_date ) : ''; ?>"
					   data-standard-date="<?php echo $start_date ? date( 'Y-m-d', $start_date ) : $start_date; ?>"
					   placeholder="<?php _e( 'Start Date', 'walkthecounty' ); ?>"
				/>
			</div>
			<div class="walkthecounty-filter walkthecounty-filter-half">
				<label for="end-date" class="walkthecounty-end-date-label"><?php _e( 'End Date', 'walkthecounty' ); ?></label>
				<input type="text"
				       id="end-date"
				       name="end-date"
				       class="walkthecounty_datepicker"
				       autocomplete="off"
				       value="<?php echo $end_date ? date_i18n( walkthecounty_date_format(), $end_date ) : ''; ?>"
				       data-standard-date="<?php echo $end_date ? date( 'Y-m-d', $end_date ) : $end_date; ?>"
					   placeholder="<?php _e( 'End Date', 'walkthecounty' ); ?>"
				/>
			</div>
		</div>
		<div id="walkthecounty-payment-form-filter" class="walkthecounty-filter">
			<label for="walkthecounty-donation-forms-filter"
				   class="walkthecounty-donation-forms-filter-label"><?php _e( 'Goal', 'walkthecounty' ); ?></label>
			<select id="walkthecounty-forms-goal-filter" name="walkthecounty-forms-goal-filter" class="walkthecounty-forms-goal-filter">
				<option value="any_goal_status" 
				<?php
				if ( 'any_goal_status' === $walkthecounty_forms_goal_filter ) {
					echo 'selected';
				}
				?>
				><?php _e( 'Any Goal Status', 'walkthecounty' ); ?></option>
				<option value="goal_achieved" 
				<?php
				if ( 'goal_achieved' === $walkthecounty_forms_goal_filter ) {
					echo 'selected';
				}
				?>
				><?php _e( 'Goal Achieved', 'walkthecounty' ); ?></option>
				<option value="goal_in_progress" 
				<?php
				if ( 'goal_in_progress' === $walkthecounty_forms_goal_filter ) {
					echo 'selected';
				}
				?>
				><?php _e( 'Goal In Progress', 'walkthecounty' ); ?></option>
				<option value="goal_not_set" 
				<?php
				if ( 'goal_not_set' === $walkthecounty_forms_goal_filter ) {
					echo 'selected';
				}
				?>
				><?php _e( 'Goal Not Set', 'walkthecounty' ); ?></option>
			</select>
		</div>
		<div class="walkthecounty-filter">
			<?php submit_button( __( 'Apply', 'walkthecounty' ), 'secondary', '', false ); ?>
			<?php
			// Clear active filters button.
			if ( ! empty( $start_date ) || ! empty( $end_date ) || ! empty( $search ) || ! empty( $walkthecounty_forms_goal_filter ) ) :
				?>
				<a href="<?php echo admin_url( 'edit.php?post_type=walkthecounty_forms' ); ?>"
				   class="button walkthecounty-clear-filters-button"><?php _e( 'Clear Filters', 'walkthecounty' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

add_action( 'manage_posts_extra_tablenav', 'walkthecounty_forms_advanced_filter', 10, 1 );
