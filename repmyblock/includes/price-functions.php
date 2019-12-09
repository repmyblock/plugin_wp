<?php
/**
 * Price Functions
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
 * Checks to see if a WalkTheCounty form has variable prices enabled.
 *
 * @since 1.0
 *
 * @param int $form_id ID number of the form to check
 *
 * @return bool true if has variable prices, false otherwise
 */
function walkthecounty_has_variable_prices( $form_id = 0 ) {

	if ( empty( $form_id ) ) {
		return false;
	}

	$form = new WalkTheCounty_Donate_Form( $form_id );

	return $form->has_variable_prices();
}


/**
 * Retrieves the variable prices for a form
 *
 * @since 1.0
 *
 * @param int $form_id ID of the WalkTheCounty form
 *
 * @return array|bool Variable prices
 */
function walkthecounty_get_variable_prices( $form_id = 0 ) {

	if ( empty( $form_id ) ) {
		return false;
	}

	$form = new WalkTheCounty_Donate_Form( $form_id );

	return $form->prices;

}

/**
 * Retrieves the variable price ids for a form
 *
 * @since 1.8.8
 *
 * @param int $form_id ID of the WalkTheCounty form
 *
 * @return array Variable prices
 */
function walkthecounty_get_variable_price_ids( $form_id = 0 ) {
	if( ! ( $prices = walkthecounty_get_variable_prices( $form_id ) ) ) {
		return array();
	}

	$price_ids = array();
	foreach ( $prices as $price ){
		$price_ids[] = $price['_walkthecounty_id']['level_id'];
	}

	return $price_ids;
}


/**
 * Get the default amount for multi-level forms
 *
 * @access public
 * @since  1.0
 *
 * @param int $form_id
 *
 * @return string $default_price
 */
function walkthecounty_get_default_multilevel_amount( $form_id ) {
	$default_price = '1.00';

	// Get default level price data.
	$default_level = walkthecounty_form_get_default_level( $form_id );
	$default_price = isset( $default_level['_walkthecounty_amount'] ) ? $default_level['_walkthecounty_amount'] : $default_price;

	return $default_price;
}


/**
 * Get Default Form Amount
 *
 * Grabs the default amount for set and level forms
 *
 * @param int $form_id
 *
 * @return string $default_price
 * @since      1.0
 */
function walkthecounty_get_default_form_amount( $form_id ) {

	if ( walkthecounty_has_variable_prices( $form_id ) ) {

		$default_amount = walkthecounty_get_default_multilevel_amount( $form_id );

	} else {

		$default_amount = walkthecounty_get_meta( $form_id, '_walkthecounty_set_price', true );

	}

	return apply_filters( 'walkthecounty_default_form_amount', $default_amount, $form_id );

}


/**
 * Determine if custom price mode is enabled or disabled.
 *
 * This function is wrapper function to WalkTheCounty_Donate_Form::is_custom_price_mode()
 *
 * @since 1.6
 *
 * @param int $form_id Form ID.
 *
 * @use   WalkTheCounty_Donate_Form::is_custom_price_mode()
 *
 * @return bool
 */
function walkthecounty_is_custom_price_mode( $form_id = 0 ) {

	if ( empty( $form_id ) ) {
		return false;
	}

	$form = new WalkTheCounty_Donate_Form( $form_id );

	return $form->is_custom_price_mode();
}
