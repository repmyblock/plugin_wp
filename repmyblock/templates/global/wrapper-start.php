<?php
/**
 * Content wrappers
 *
 * @package     WalkTheCounty
 * @subpackage  Templates/Global
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$template = strtolower( get_option( 'template' ) );

switch ( $template ) {
	case 'twentyeleven' :
		echo '<div id="primary" class="walkthecounty-wrap"><div id="content" role="main" class="twentyeleven">';
		break;
	case 'twentytwelve' :
		echo '<div id="primary" class="site-content walkthecounty-wrap"><div id="content" role="main" class="twentytwelve">';
		break;
	case 'twentythirteen' :
		echo '<div id="primary" class="site-content walkthecounty-wrap"><div id="content" role="main" class="entry-content twentythirteen">';
		break;
	case 'twentyfourteen' :
		echo '<div id="primary" class="content-area walkthecounty-wrap"><div id="content" role="main" class="site-content twentyfourteen"><div class="tfwalkthecounty">';
		break;
	case 'twentyfifteen' :
		echo '<div id="primary" role="main" class="content-area twentyfifteen walkthecounty-wrap"><div id="main" class="site-main t15walkthecounty">';
		break;
	case 'twentyseventeen' :
		echo '<div class="wrap walkthecounty-wrap">';
		break;
	case 'twentynineteen' :
		echo '<div class="entry"><div class="entry-content"><div class="walkthecounty-wrap">';
		break;
	case 'flatsome' :
		echo '<div id="container" class="row product-page walkthecounty-wrap"><div id="content" role="main">';
		break;
	case 'divi' :
		echo '<div id="main-content"><div class="container walkthecounty-wrap" role="main">';
		break;
	case 'salient' :
		echo '<div class="container-wrap"><div id="container" class="walkthecounty-wrap container"><div id="content" role="main">';
		break;
	case 'x' :
		echo '<div id="container" class="x-container-fluid max width offset cf walkthecounty-wrap"><div class="x-main full" role="main"><div class="entry-wrap"><div class="entry-content">';
		break;
	case 'jupiter' :
		echo '<div id="theme-page" class="walkthecounty-wrap"><div class="theme-page-wrapper mk-blog-single full-layout vc_row-fluid mk-grid"><div class="theme-content">';
		break;
	case 'avada' :
		echo '<div class="walkthecounty-wrap"><div id="content" role="main" style="width:100%">';
		break;
	case 'philanthropy-parent' :
		echo '<div id="main" class="site-main" role="main"><div class="container"><div class="row"><div class="col-sm-12 col-xs-12 content-area">';
		break;
	case 'zerif-lite' :
		echo '</header><div id="content" class="site-content"><div class="container"><div class="content-left-wrap col-md-12"><div id="primary" class="content-area"><main id="main" class="site-main" role="main"><article>';
		break;
	case 'customizr' :
		echo '<div id="main-wrapper" class="container">';
		break;
	case 'catch-evolution' :
		echo '<div class="wrapper hentry" style="box-sizing: border-box;">';
		break;
	default :
		echo apply_filters( 'walkthecounty_default_wrapper_start', '<div id="container" class="walkthecounty-wrap container"><div id="content" role="main">' );
		break;
}
