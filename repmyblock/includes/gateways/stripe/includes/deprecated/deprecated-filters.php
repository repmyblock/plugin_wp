<?php
/**
 * WalkTheCounty - Stripe Core | Deprecated Filter Hooks
 *
 * @since 2.5.0
 *
 * @package    WalkTheCounty
 * @subpackage Stripe Core
 * @copyright  Copyright (c) 2019, WalkTheCountyWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 */

// Bailout, if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$walkthecounty_stripe_map_deprecated_filters = walkthecounty_stripe_deprecated_filters();

foreach ( $walkthecounty_stripe_map_deprecated_filters as $new => $old ) {
	add_filter( $new, 'walkthecounty_stripe_deprecated_filter_mapping', 10, 4 );
}

/**
 * Deprecated filters.
 *
 * @return array An array of deprecated WalkTheCounty filters.
 */
function walkthecounty_stripe_deprecated_filters() {

	$deprecated_filters = array(
		// New filter hook                    Old filter hook.
		'walkthecounty_stripe_get_connect_settings' => 'get_walkthecounty_stripe_connect_options',
	);

	return $deprecated_filters;
}

/**
 * Deprecated filter mapping.
 *
 * @param mixed  $data
 * @param string $arg_1 Passed filter argument 1.
 * @param string $arg_2 Passed filter argument 2.
 * @param string $arg_3 Passed filter argument 3.
 *
 * @return mixed
 */
function walkthecounty_stripe_deprecated_filter_mapping( $data, $arg_1 = '', $arg_2 = '', $arg_3 = '' ) {
	$walkthecounty_stripe_map_deprecated_filters = walkthecounty_stripe_deprecated_filters();
	$filter                             = current_filter();

	if ( isset( $walkthecounty_stripe_map_deprecated_filters[ $filter ] ) ) {
		if ( has_filter( $walkthecounty_stripe_map_deprecated_filters[ $filter ] ) ) {
			$data = apply_filters( $walkthecounty_stripe_map_deprecated_filters[ $filter ], $data, $arg_1, $arg_2, $arg_3 );

			if ( ! defined( 'DOING_AJAX' ) ) {
				_walkthecounty_deprecated_function( sprintf( /* translators: %s: filter name */
					__( 'The %s filter' ), $walkthecounty_stripe_map_deprecated_filters[ $filter ] ), '2.5.0', $filter );
			}
		}
	}

	return $data;
}
