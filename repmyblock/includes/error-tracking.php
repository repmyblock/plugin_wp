<?php
/**
 * Error Tracking
 *
 * @package     WalkTheCounty
 * @subpackage  Functions/Errors
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Errors
 *
 * Retrieves all error messages stored during the checkout process.
 * If errors exist, they are returned.
 *
 * @since 1.0
 * @uses  WalkTheCounty_Session::get()
 * @return array|bool array if errors are present, false if none found
 */
function walkthecounty_get_errors() {
	return WalkTheCounty()->session->get( 'walkthecounty_errors' );
}

/**
 * Set Error
 *
 * Stores an error in a session var.
 *
 * @since 1.0
 * @uses  WalkTheCounty_Session::get()
 *
 * @param int    $error_id      ID of the error being set.
 * @param string $error_message Message to store with the error.
 * @param array  $notice_args
 *
 * @return void
 */
function walkthecounty_set_error( $error_id, $error_message, $notice_args = array() ) {
	$errors = walkthecounty_get_errors();
	if ( ! $errors ) {
		$errors = array();
	}

	if( is_array( $notice_args ) && ! empty( $notice_args ) ) {
		$errors[ $error_id ] = array(
			'message'     => $error_message,
			'notice_args' => $notice_args,
		);
	} else {
		// Backward compatibility v<1.8.11.
		$errors[ $error_id ] = $error_message;
	}

	WalkTheCounty()->session->set( 'walkthecounty_errors', $errors );
}

/**
 * Clears all stored errors.
 *
 * @since 1.0
 * @uses  WalkTheCounty_Session::set()
 * @return void
 */
function walkthecounty_clear_errors() {
	WalkTheCounty()->session->set( 'walkthecounty_errors', null );
}

/**
 * Removes (unsets) a stored error
 *
 * @since 1.0
 * @uses  WalkTheCounty_Session::set()
 *
 * @param int $error_id ID of the error being set.
 *
 * @return void
 */
function walkthecounty_unset_error( $error_id ) {
	$errors = walkthecounty_get_errors();
	if ( $errors ) {
		/**
		 * Check If $error_id exists in the array.
		 * If exists then unset it.
		 *
		 * @since 1.8.13
		 */
		if ( isset( $errors[ $error_id ] ) ) {
			unset( $errors[ $error_id ] );
		}
		WalkTheCounty()->session->set( 'walkthecounty_errors', $errors );
	}
}

/**
 * Register die handler for walkthecounty_die()
 *
 * @since  1.0
 * @return string
 */
function _walkthecounty_die_handler() {
	if ( defined( 'WALKTHECOUNTY_UNIT_TESTS' ) ) {
		return '_walkthecounty_die_handler';
	} else {
		die();
	}
}

/**
 * Wrapper function for wp_die(). This function adds filters for wp_die() which
 * kills execution of the script using wp_die(). This allows us to then to work
 * with functions using walkthecounty_die() in the unit tests.
 *
 * @since 1.0
 *
 * @param string $message Message to store with the error.
 * @param string $title   Error title.
 * @param int    $status  HTTP status code..
 *
 * @return void
 */
function walkthecounty_die( $message = '', $title = '', $status = 400 ) {
	add_filter( 'wp_die_ajax_handler', '_walkthecounty_die_handler', 10, 3 );
	add_filter( 'wp_die_json_handler', '_walkthecounty_die_handler', 10, 3 );
	add_filter( 'wp_die_handler', '_walkthecounty_die_handler', 10, 3 );
	wp_die( $message, $title, array( 'response' => $status ) );
}
