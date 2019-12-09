<?php
/**
 * Single WalkTheCounty Form Sidebar
 *
 * Adds a dynamic sidebar to single WalkTheCounty Forms (singular post type for walkthecounty_forms) - Override this template by copying it to yourtheme/walkthecounty/single-walkthecounty-form/sidebar.php
 *
 * @package     WalkTheCounty
 * @subpackage  Templates/Single-WalkTheCounty-Form
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( is_active_sidebar( 'walkthecounty-forms-sidebar' ) ) {
	dynamic_sidebar( 'walkthecounty-forms-sidebar' );
}
