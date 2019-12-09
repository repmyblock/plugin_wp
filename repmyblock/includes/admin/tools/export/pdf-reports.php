<?php
/**
 * PDF Report Generation Functions.
 *
 * @package     WalkTheCounty
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly..
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generate PDF Reports.
 *
 * Generates PDF report on donations and income for all forms for the current year.
 *
 * @since  1.0
 *
 * @param string $data Data.
 *
 * @uses   walkthecounty_pdf
 */
function walkthecounty_generate_pdf( $data ) {

	if ( ! current_user_can( 'view_walkthecounty_reports' ) ) {
		wp_die( __( 'You do not have permission to generate PDF sales reports.', 'walkthecounty' ), __( 'Error', 'walkthecounty' ), array( 'response' => 403 ) );
	}

	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'walkthecounty_generate_pdf' ) ) {
		wp_die( __( 'We\'re unable to recognize your session. Please refresh the screen to try again; otherwise contact your website administrator for assistance.', 'walkthecounty' ), __( 'Error', 'walkthecounty' ), array( 'response' => 403 ) );
	}

	if ( ! file_exists( WALKTHECOUNTY_PLUGIN_DIR . '/includes/libraries/walkthecounty-pdf.php' ) ) {
		wp_die( __( 'Dependency missing.', 'walkthecounty' ), __( 'Error', 'walkthecounty' ), array( 'response' => 403 ) );
	}

	require_once WALKTHECOUNTY_PLUGIN_DIR . '/includes/libraries/walkthecounty-pdf.php';

	$daterange = utf8_decode(
		sprintf(
		/* translators: 1: start date 2: end date */
			__( '%1$s to %2$s', 'walkthecounty' ),
			date_i18n( walkthecounty_date_format(), mktime( 0, 0, 0, 1, 1, date( 'Y' ) ) ),
			date_i18n( walkthecounty_date_format() )
		)
	);

	$categories_enabled = walkthecounty_is_setting_enabled( walkthecounty_get_option( 'categories', 'disabled' ) );
	$tags_enabled       = walkthecounty_is_setting_enabled( walkthecounty_get_option( 'tags', 'disabled' ) );

	$pdf          = new WalkTheCounty_PDF( 'L', 'mm', 'A', true, 'UTF-8', false );
	$default_font = apply_filters( 'walkthecounty_pdf_default_font', 'Helvetica' );
	$custom_font  = 'dejavusans';
	$font_style   = '';
	$font_path    = '';

	if ( file_exists( WALKTHECOUNTY_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
		$font_path = WALKTHECOUNTY_PLUGIN_DIR . '/vendor/tecnickcom/tcpdf/fonts/CODE2000.TTF';
	} else {
		$font_path = WALKTHECOUNTY_PLUGIN_DIR . '/includes/libraries/tcpdf/fonts/CODE2000.TTF';
	}

	if (
		file_exists( $font_path ) &&

		// RIAL exist for backward compatibility.
		in_array( walkthecounty_get_currency(), array( 'RIAL', 'RUB', 'IRR' ) )
	) {
		TCPDF_FONTS::addTTFfont( $font_path, '' );
		$custom_font = 'CODE2000';
		$font_style  = 'B';
	}

	$pdf->AddPage( 'L', 'A4' );
	$pdf->setImageScale( 1.5 );
	$pdf->SetTitle( utf8_decode( __( 'Donation report for the current year for all forms', 'walkthecounty' ) ) );
	$pdf->SetAuthor( utf8_decode( __( 'WalkTheCountyWP - Democratizing Generosity', 'walkthecounty' ) ) );
	$pdf->SetCreator( utf8_decode( __( 'WalkTheCountyWP - Democratizing Generosity', 'walkthecounty' ) ) );

	// Image URL should have absolute path. @see https://tcpdf.org/examples/example_009/.
	$pdf->Image( apply_filters( 'walkthecounty_pdf_export_logo', WALKTHECOUNTY_PLUGIN_DIR . 'assets/dist/images/walkthecounty-logo-small.png' ), 247, 8 );

	$pdf->SetMargins( 8, 8, 8 );
	$pdf->SetX( 8 );

	$pdf->SetFont( $default_font, '', 16 );
	$pdf->SetTextColor( 50, 50, 50 );
	$pdf->Cell( 0, 3, utf8_decode( __( 'Donation report for the current year for all forms', 'walkthecounty' ) ), 0, 2, 'L', false );

	$pdf->SetFont( $default_font, '', 13 );
	$pdf->SetTextColor( 150, 150, 150 );
	$pdf->Ln( 1 );
	$pdf->Cell( 0, 6, utf8_decode( __( 'Date Range: ', 'walkthecounty' ) ) . $daterange, 0, 2, 'L', false );
	$pdf->Ln();
	$pdf->SetTextColor( 50, 50, 50 );
	$pdf->SetFont( $default_font, '', 14 );
	$pdf->Cell( 0, 10, utf8_decode( __( 'Table View', 'walkthecounty' ) ), 0, 2, 'L', false );
	$pdf->SetFont( $default_font, '', 12 );

	$pdf->SetFillColor( 238, 238, 238 );
	$pdf->SetTextColor( 0, 0, 0, 100 ); // Set Black color.
	$pdf->Cell( 50, 6, utf8_decode( __( 'Form Name', 'walkthecounty' ) ), 1, 0, 'L', true );
	$pdf->Cell( 50, 6, utf8_decode( __( 'Price', 'walkthecounty' ) ), 1, 0, 'L', true );

	// Display Categories Heading only, if user has opted for it.
	if ( $categories_enabled ) {
		$pdf->Cell( 45, 6, utf8_decode( __( 'Categories', 'walkthecounty' ) ), 1, 0, 'L', true );
	}

	// Display Tags Heading only, if user has opted for it.
	if ( $tags_enabled ) {
		$pdf->Cell( 45, 6, utf8_decode( __( 'Tags', 'walkthecounty' ) ), 1, 0, 'L', true );
	}

	$pdf->Cell( 45, 6, utf8_decode( __( 'Number of Donations', 'walkthecounty' ) ), 1, 0, 'L', true );
	$pdf->Cell( 45, 6, utf8_decode( __( 'Income to Date', 'walkthecounty' ) ), 1, 1, 'L', true );

	// Set Custom Font to support various currencies.
	$pdf->SetFont( apply_filters( 'walkthecounty_pdf_custom_font', $custom_font ), $font_style, 12 );

	// Object for getting stats.
	$donation_stats = new WalkTheCounty_Payment_Stats();

	$walkthecounty_forms = get_posts( array(
		'post_type'        => 'walkthecounty_forms',
		'posts_per_page'   => - 1,
		'suppress_filters' => false,
	) );

	if ( $walkthecounty_forms ) {
		$pdf->SetWidths( array( 50, 50, 45, 45, 45, 45 ) );

		foreach ( $walkthecounty_forms as $form ):
			$pdf->SetFillColor( 255, 255, 255 );

			$title = $form->post_title;

			if ( walkthecounty_has_variable_prices( $form->ID ) ) {
				$price = html_entity_decode( walkthecounty_price_range( $form->ID, false ), ENT_COMPAT, 'UTF-8' );
			} else {
				$price = walkthecounty_currency_filter( walkthecounty_get_form_price( $form->ID ), array( 'decode_currency' => true ) );
			}

			// Display Categories Data only, if user has opted for it.
			$categories = array();
			if ( $categories_enabled ) {
				$categories = get_the_term_list( $form->ID, 'walkthecounty_forms_category', '', ', ', '' );
				$categories = ! is_wp_error( $categories ) ? strip_tags( $categories ) : '';
			}

			// Display Tags Data only, if user has opted for it.
			$tags = array();
			if ( $tags_enabled ) {
				$tags = get_the_term_list( $form->ID, 'walkthecounty_forms_tag', '', ', ', '' );
				$tags = ! is_wp_error( $tags ) ? strip_tags( $tags ) : '';
			}

			$sales    = $donation_stats->get_sales( $form->ID, 'this_year' );
			$earnings = walkthecounty_currency_filter( walkthecounty_format_amount( $donation_stats->get_earnings( $form->ID, 'this_year' ), array( 'sanitize' => false, ) ), array( 'decode_currency' => true ) );

			// This will help filter data before appending it to PDF Receipt.
			$prepare_pdf_data   = array();
			$prepare_pdf_data[] = $title;
			$prepare_pdf_data[] = $price;

			// Append Categories Data only, if user has opted for it.
			if ( $categories_enabled ) {
				$prepare_pdf_data[] = $categories;
			}

			// Append Tags Data only, if user has opted for it.
			if ( $tags_enabled ) {
				$prepare_pdf_data[] = $tags;
			}

			$prepare_pdf_data[] = $sales;
			$prepare_pdf_data[] = $earnings;

			$pdf->Row( $prepare_pdf_data );

		endforeach;
	} else {

		// Fix: Minor Styling Alignment Issue for PDF.
		if ( $categories_enabled && $tags_enabled ) {
			$no_found_width = 280;
		} elseif ( $categories_enabled || $tags_enabled ) {
			$no_found_width = 235;
		} else {
			$no_found_width = 190;
		}
		$title = utf8_decode( __( 'No forms found.', 'walkthecounty' ) );
		$pdf->MultiCell( $no_found_width, 5, $title, 1, 'C', false, 1, '', '', true, 0, false, true, 0, 'T', false );
	}// End if().
	$pdf->Ln();
	$pdf->SetTextColor( 50, 50, 50 );
	$pdf->SetFont( $default_font, '', 14 );

	// Output Graph on a new page.
	$pdf->AddPage( 'L', 'A4' );
	$pdf->Cell( 0, 10, utf8_decode( __( 'Graph View', 'walkthecounty' ) ), 0, 2, 'L', false );
	$pdf->SetFont( $default_font, '', 12 );

	$image = html_entity_decode( urldecode( walkthecounty_draw_chart_image() ) );
	$image = str_replace( ' ', '%20', $image );

	$pdf->SetX( 25 );
	$pdf->Image( $image . '&file=.png' );
	$pdf->Ln( 7 );
	$pdf->Output( apply_filters( 'walkthecounty_sales_earnings_pdf_export_filename', 'walkthecounty-report-' . date_i18n( 'Y-m-d' ) ) . '.pdf', 'D' );
	exit();
}

add_action( 'walkthecounty_generate_pdf', 'walkthecounty_generate_pdf' );

/**
 * Draws Chart for PDF Report.
 *
 * Draws the sales and earnings chart for the PDF report and then retrieves the
 * URL of that chart to display on the PDF Report.
 *
 * @since  1.1.4.0
 * @uses   GoogleChart
 * @uses   GoogleChartData
 * @uses   GoogleChartShapeMarker
 * @uses   GoogleChartTextMarker
 * @uses   GoogleChartAxis
 * @return string $chart->getUrl() URL for the Google Chart
 */
function walkthecounty_draw_chart_image() {
	require_once WALKTHECOUNTY_PLUGIN_DIR . '/includes/libraries/googlechartlib/GoogleChart.php';
	require_once WALKTHECOUNTY_PLUGIN_DIR . '/includes/libraries/googlechartlib/markers/GoogleChartShapeMarker.php';
	require_once WALKTHECOUNTY_PLUGIN_DIR . '/includes/libraries/googlechartlib/markers/GoogleChartTextMarker.php';

	$chart = new GoogleChart( 'lc', 900, 330 );

	$i        = 1;
	$earnings = "";
	$sales    = "";

	while ( $i <= 12 ) :
		$earnings .= walkthecounty_get_earnings_by_date( null, $i, date( 'Y' ) ) . ",";
		$sales    .= walkthecounty_get_sales_by_date( null, $i, date( 'Y' ) ) . ",";
		$i ++;
	endwhile;

	$earnings_array = explode( ",", $earnings );
	$sales_array    = explode( ",", $sales );

	$i = 0;
	while ( $i <= 11 ) {
		if ( empty( $sales_array[ $i ] ) ) {
			$sales_array[ $i ] = 0;
		}
		$i ++;
	}

	$min_earnings   = 0;
	$max_earnings   = max( $earnings_array );
	$earnings_scale = round( $max_earnings, - 1 );

	$data = new GoogleChartData( array(
		$earnings_array[0],
		$earnings_array[1],
		$earnings_array[2],
		$earnings_array[3],
		$earnings_array[4],
		$earnings_array[5],
		$earnings_array[6],
		$earnings_array[7],
		$earnings_array[8],
		$earnings_array[9],
		$earnings_array[10],
		$earnings_array[11],
	) );

	$data->setLegend( __( 'Income', 'walkthecounty' ) );
	$data->setColor( '1b58a3' );
	$chart->addData( $data );

	$shape_marker = new GoogleChartShapeMarker( GoogleChartShapeMarker::CIRCLE );
	$shape_marker->setColor( '000000' );
	$shape_marker->setSize( 7 );
	$shape_marker->setBorder( 2 );
	$shape_marker->setData( $data );
	$chart->addMarker( $shape_marker );

	$value_marker = new GoogleChartTextMarker( GoogleChartTextMarker::VALUE );
	$value_marker->setColor( '000000' );
	$value_marker->setData( $data );
	$chart->addMarker( $value_marker );

	$data = new GoogleChartData( array(
		$sales_array[0],
		$sales_array[1],
		$sales_array[2],
		$sales_array[3],
		$sales_array[4],
		$sales_array[5],
		$sales_array[6],
		$sales_array[7],
		$sales_array[8],
		$sales_array[9],
		$sales_array[10],
		$sales_array[11],
	) );
	$data->setLegend( __( 'Donations', 'walkthecounty' ) );
	$data->setColor( 'ff6c1c' );
	$chart->addData( $data );

	$chart->setTitle( __( 'Donations by Month for all WalkTheCountyWP Forms', 'walkthecounty' ), '336699', 18 );

	$chart->setScale( 0, $max_earnings );

	$y_axis = new GoogleChartAxis( 'y' );
	$y_axis->setDrawTickMarks( true )->setLabels( array( 0, $max_earnings ) );
	$chart->addAxis( $y_axis );

	$x_axis = new GoogleChartAxis( 'x' );
	$x_axis->setTickMarks( 5 );
	$x_axis->setLabels( array(
		__( 'Jan', 'walkthecounty' ),
		__( 'Feb', 'walkthecounty' ),
		__( 'Mar', 'walkthecounty' ),
		__( 'Apr', 'walkthecounty' ),
		__( 'May', 'walkthecounty' ),
		__( 'June', 'walkthecounty' ),
		__( 'July', 'walkthecounty' ),
		__( 'Aug', 'walkthecounty' ),
		__( 'Sept', 'walkthecounty' ),
		__( 'Oct', 'walkthecounty' ),
		__( 'Nov', 'walkthecounty' ),
		__( 'Dec', 'walkthecounty' ),
	) );
	$chart->addAxis( $x_axis );

	$shape_marker = new GoogleChartShapeMarker( GoogleChartShapeMarker::CIRCLE );
	$shape_marker->setSize( 6 );
	$shape_marker->setBorder( 2 );
	$shape_marker->setData( $data );
	$chart->addMarker( $shape_marker );

	$value_marker = new GoogleChartTextMarker( GoogleChartTextMarker::VALUE );
	$value_marker->setData( $data );
	$chart->addMarker( $value_marker );

	return $chart->getUrl();
}
