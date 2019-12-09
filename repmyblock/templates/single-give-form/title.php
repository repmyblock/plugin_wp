<?php
/**
 * Single WalkTheCounty Form Title
 * 
 * Displays the main title for the single donation form - Override this template by copying it to yourtheme/walkthecounty/single-walkthecounty-form/title.php
 * 
 * @package     WalkTheCounty
 * @subpackage  templates/single-walkthecounty-form
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
} ?>
<h1 itemprop="name" class="walkthecounty-form-title entry-title"><?php the_title(); ?></h1>
