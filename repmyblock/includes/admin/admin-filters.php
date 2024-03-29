<?php
/**
 * Admin Filters
 *
 * @package     WalkTheCounty
 * @subpackage  Admin/Filters
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitize number of decimals setting field.
 *
 *  1. User can only set absolute integer value as number of decimals.
 *  2. number_decimals setting will be zero if no decimal separator defined
 *
 * @since   1.8
 * @used-by WalkTheCounty_Plugin_Settings::walkthecounty_settings()
 *
 * @param   string $value
 *
 * @return  mixed
 */
function __walkthecounty_sanitize_number_decimals_setting_field( $value ) {
	$value_changed = false;
	$show_notice   = false;
	$old_value     = $value;

	if ( isset( $_POST['decimal_separator'] ) ) {
		$value         = ! empty( $_POST['decimal_separator'] ) ? $value : 0;
		$value_changed = true;
	}

	if ( $value_changed && ( $old_value !== $value ) ) {
		WalkTheCounty_Admin_Settings::add_error( 'walkthecounty-number-decimal', __( 'The \'Number of Decimals\' option has been automatically set to zero because the \'Decimal Separator\' is not set.', 'walkthecounty' ) );
	}

	$value                      = absint( $value );
	$is_currency_set_to_bitcoin = ( 'BTC' === walkthecounty_get_option( 'currency' ) && ! isset( $_POST['currency'] ) ) || 'BTC' === $_POST['currency'];

	if ( $is_currency_set_to_bitcoin && 8 < $value) {
		$value = 8;
		$show_notice = true;
	}elseif ( ! $is_currency_set_to_bitcoin && 6 <= $value ) {
		$value = 5;
		$show_notice = true;
	}

	if( $show_notice ) {
		WalkTheCounty_Admin_Settings::add_error(
			'walkthecounty-number-decimal',
			sprintf(
				__( 'The \'Number of Decimals\' option has been automatically set to %s because you entered a number higher than the maximum allowed.', 'walkthecounty' ),
				$value
			)
		);
	}

	return absint( $value );
}

add_filter( 'walkthecounty_admin_settings_sanitize_option_number_decimals', '__walkthecounty_sanitize_number_decimals_setting_field', 10 );


/**
 * Sanitize number of decimals setting field.
 *
 *  1. User can only set absolute integer value as number of decimals.
 *  2. number_decimals setting will be zero if no decimal separator defined
 *
 * @since   1.8
 * @used-by WalkTheCounty_Plugin_Settings::walkthecounty_settings()
 *
 * @param   string $value
 *
 * @return  mixed
 */
function __walkthecounty_validate_decimal_separator_setting_field( $value ) {
	$thousand_separator = isset( $_POST['thousands_separator'] ) ? walkthecounty_clean( $_POST['thousands_separator'] ) : '';
	$decimal_separator  = isset( $_POST['decimal_separator'] ) ? walkthecounty_clean( $_POST['decimal_separator'] ) : '';

	if ( $decimal_separator === $thousand_separator ) {
		$value                    = '';
		$_POST['number_decimals'] = 0;
		WalkTheCounty_Admin_Settings::add_error( 'walkthecounty-decimal-separator', __( 'The \'Decimal Separator\' option has automatically been set to empty because it can not be equal to the \'Thousand Separator\'', 'walkthecounty' ) );
	}

	return $value;
}

add_filter( 'walkthecounty_admin_settings_sanitize_option_decimal_separator', '__walkthecounty_validate_decimal_separator_setting_field', 10 );

/**
 * Change $delimiter text to symbol.
 *
 * @since 1.8.14
 *
 * @param string $delimiter
 *
 * @return string $delimiter.
 */
function __walkthecounty_import_delimiter_set_callback( $delimiter ) {
	$delimite_type = array(
		'csv'                  => ',',
		'tab-separated-values' => "\t",
	);

	return ( array_key_exists( $delimiter, $delimite_type ) ? $delimite_type[ $delimiter ] : ',' );
}

add_filter( 'walkthecounty_import_delimiter_set', '__walkthecounty_import_delimiter_set_callback', 10 );

/**
 * WalkTheCounty unset the page id from the core setting data from the json files.
 *
 * @since 1.8.17
 *
 * @param array $json_to_array Data from json file
 * @param string $type
 *
 * @return array $json_to_array
 */
function walkthecounty_import_core_settings_merge_pages( $json_to_array, $type ) {
	if ( 'merge' === $type ) {
		unset( $json_to_array['success_page'] );
		unset( $json_to_array['failure_page'] );
		unset( $json_to_array['history_page'] );
	}

	return $json_to_array;
}

add_filter( 'walkthecounty_import_core_settings_data', 'walkthecounty_import_core_settings_merge_pages', 11, 2 );

/**
 * WalkTheCounty check the image size from the core setting data from the json files.
 *
 * @since 1.8.17
 *
 * @param $json_to_array
 * @param string $type
 *
 * @return array $json_to_array
 */
function walkthecounty_import_core_settings_merge_image_size( $json_to_array, $type ) {
	if ( 'merge' === $type ) {
		// Featured image sizes import under Display Options > Post Types > Featured Image Size.
		if (
			! empty( $json_to_array['form_featured_img'] )
			&& ! empty( $json_to_array['featured_image_size'] )
			&& walkthecounty_is_setting_enabled( $json_to_array['form_featured_img'] )
		) {
			$images_sizes = get_intermediate_image_sizes();

			if ( ! in_array( $json_to_array['featured_image_size'], $images_sizes, true ) ) {
				unset( $json_to_array['featured_image_size'] );
			}
		}
	}

	return $json_to_array;
}

add_filter( 'walkthecounty_import_core_settings_data', 'walkthecounty_import_core_settings_merge_image_size', 12, 2 );

/**
 * WalkTheCounty upload the image logo from the core setting data from the json files.
 *
 * @since 1.8.17
 *
 * @param $json_to_array
 * @param string $type
 *
 * @return array $json_to_array
 */
function walkthecounty_import_core_settings_merge_upload_image( $json_to_array, $type ) {
	if ( 'merge' === $type ) {
		// Emails > Email Settings > Logo.
		if ( ! empty( $json_to_array['email_logo'] ) ) {

			// Need to require these files.
			if ( ! function_exists( 'media_handle_upload' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
				require_once( ABSPATH . 'wp-admin/includes/media.php' );
			}

			$url     = $json_to_array['email_logo'];
			$new_url = media_sideload_image( $url, 0, null, 'src' );
			if ( ! is_wp_error( $new_url ) ) {
				$json_to_array['email_logo'] = $new_url;
			} else {
				unset( $json_to_array['email_logo'] );
			}
		}
	}

	return $json_to_array;
}

add_filter( 'walkthecounty_import_core_settings_data', 'walkthecounty_import_core_settings_merge_upload_image', 13, 2 );

/**
 * WalkTheCounty unset the license key from the core setting data from the json files.
 *
 * @since 1.8.17
 *
 * @param array $json_to_array Data from json file
 * @param string $type
 *
 * @return array $json_to_array
 */
function walkthecounty_import_core_settings_merge_license_key( $json_to_array, $type ) {
	if ( 'merge' === $type ) {
		foreach ( $json_to_array as $key => $value ) {
			$is_license_key = strpos( '_license_key', $key );
			if ( ! empty( $is_license_key ) ) {
				unset( $json_to_array[ $key ] );
			}
		}
	}

	return $json_to_array;
}

add_filter( 'walkthecounty_import_core_settings_data', 'walkthecounty_import_core_settings_merge_license_key', 14, 2 );

/**
 * WalkTheCounty merge the json data and setting data.
 *
 * @since 1.8.17
 *
 * @param $json_to_array
 * @param $type
 * @param $host_walkthecounty_options
 *
 * @return array $json_to_array
 */
function walkthecounty_import_core_settings_merge_data( $json_to_array, $type, $host_walkthecounty_options ) {
	if ( 'merge' === $type ) {
		$json_to_array_merge = array_merge( $host_walkthecounty_options, $json_to_array );
		$json_to_array       = $json_to_array_merge;
	}

	return $json_to_array;
}

add_filter( 'walkthecounty_import_core_settings_data', 'walkthecounty_import_core_settings_merge_data', 1000, 3 );

/**
 * Backward Compatibility - Cleanup User Roles.
 *
 * @param array $caps List of capabilities.
 *
 * @since 1.8.17
 *
 * @return mixed
 */
function walkthecounty_bc_1817_cleanup_user_roles( $caps ) {

	if (
		! walkthecounty_has_upgrade_completed( 'v1817_cleanup_user_roles' ) &&
		! isset( $caps['view_walkthecounty_payments'] )
	) {
		walkthecounty_v1817_process_cleanup_user_roles();
	}

	return $caps;
}

add_filter( 'user_has_cap', 'walkthecounty_bc_1817_cleanup_user_roles' );
