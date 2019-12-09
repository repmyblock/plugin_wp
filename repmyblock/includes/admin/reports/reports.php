<?php
/**
 * Admin Reports Page
 *
 * Language Changes from EDD:
 * 1. "Report Type" stays
 * 2. "Earnings" changes to "Income"
 * 3. "Donors" changes to "Donors"
 * 4. "Payment Method" stays.
 *
 * @package     WalkTheCounty
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reports Page
 *
 * Renders the reports page contents.
 *
 * @since 1.0
 * @return void
 */
function walkthecounty_reports_page() {
	$current_page = admin_url( 'edit.php?post_type=walkthecounty_forms&page=walkthecounty-reports' );
	$active_tab   = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'earnings';
	$views        = walkthecounty_reports_default_views();
	?>
	<div class="wrap walkthecounty-settings-page">

		<h1 class="screen-reader-text"><?php echo get_admin_page_title(); ?></h1>

		<h2 class="nav-tab-wrapper">
			<?php foreach ( $views as $tab => $label ) { ?>
				<a href="<?php echo esc_url( add_query_arg( array(
					'tab'              => $tab,
					'settings-updated' => false,
				), $current_page ) ); ?>" class="nav-tab <?php echo $tab === $active_tab ? esc_attr( 'nav-tab-active' ) : ''; ?>"><?php echo esc_html( $label ); ?></a>
			<?php } ?>
			<?php if ( current_user_can( 'export_walkthecounty_reports' ) ) { ?>
				<a href="<?php echo esc_url( add_query_arg( array(
					'tab'              => 'export',
					'settings-updated' => false,
				), $current_page ) ); ?>" class="nav-tab <?php echo 'export' === $active_tab ? esc_attr( 'nav-tab-active' ) : ''; ?>"><?php esc_html_e( 'Export', 'walkthecounty' ); ?></a>
			<?php }
			/**
			 * Fires in the report tabs.
			 *
			 * Allows you to add new report tabs.
			 *
			 * @since 1.0
			 */
			do_action( 'walkthecounty_reports_tabs' );
			?>
		</h2>

		<?php
		/**
		 * Fires before the report page.
		 *
		 * @since 1.0
		 */
		do_action( 'walkthecounty_reports_page_top' );

		// Set $active_tab prior to hook firing.
		if ( in_array( $active_tab, array_keys( $views ) ) ) {
			$active_tab = 'reports';
		}

		/**
		 * Fires the report page active tab.
		 *
		 * @since 1.0
		 */
		do_action( "walkthecounty_reports_tab_{$active_tab}" );

		/**
		 * Fires after the report page.
		 *
		 * @since 1.0
		 */
		do_action( 'walkthecounty_reports_page_bottom' );
		?>
	</div><!-- .wrap -->
	<?php
}

/**
 * Default Report Views
 *
 * @since 1.0
 * @return array $views Report Views
 */
function walkthecounty_reports_default_views() {
	$views = array(
		'earnings' => esc_html__( 'Income', 'walkthecounty' ),
		'forms'    => esc_html__( 'Forms', 'walkthecounty' ),
		'gateways' => esc_html__( 'Donation Methods', 'walkthecounty' ),
	);

	$views = apply_filters( 'walkthecounty_report_views', $views );

	return $views;
}

/**
 * Default Report Views
 *
 * Checks the $_GET['view'] parameter to ensure it exists within the default allowed views.
 *
 * @param string $default Default view to use.
 *
 * @since 1.0
 * @return string $view Report View
 */
function walkthecounty_get_reporting_view( $default = 'earnings' ) {

	if ( ! isset( $_GET['view'] ) || ! in_array( $_GET['view'], array_keys( walkthecounty_reports_default_views() ) ) ) {
		$view = $default;
	} else {
		$view = $_GET['view'];
	}

	return apply_filters( 'walkthecounty_get_reporting_view', $view );
}

/**
 * Renders the Reports page
 *
 * @since 1.0
 * @return void
 */
function walkthecounty_reports_tab_reports() {

	if( ! current_user_can( 'view_walkthecounty_reports' ) ) {
		wp_die( __( 'You do not have permission to access this report', 'walkthecounty' ), __( 'Error', 'walkthecounty' ), array( 'response' => 403 ) );
	}

	$current_view = 'earnings';
	$views        = walkthecounty_reports_default_views();

	if ( isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $views ) ) {
		$current_view = $_GET['tab'];
	}

	/**
	 * Fires the report page view.
	 *
	 * @since 1.0
	 */
	do_action( "walkthecounty_reports_view_{$current_view}" );
}

add_action( 'walkthecounty_reports_tab_reports', 'walkthecounty_reports_tab_reports' );

/**
 * Renders the Reports Page Views Drop Downs
 *
 * @since 1.0
 * @return void
 */
function walkthecounty_report_views() {
	$views        = walkthecounty_reports_default_views();
	$current_view = isset( $_GET['view'] ) ? $_GET['view'] : 'earnings';
	/**
	 * Fires before the report page actions form.
	 *
	 * @since 1.0
	 */
	do_action( 'walkthecounty_report_view_actions_before' );
	?>
	<form id="walkthecounty-reports-filter" method="get">
		<select id="walkthecounty-reports-view" name="view">
			<option value="-1"><?php esc_html_e( 'Report Type', 'walkthecounty' ); ?></option>
			<?php foreach ( $views as $view_id => $label ) : ?>
				<option value="<?php echo esc_attr( $view_id ); ?>" <?php selected( $view_id, $current_view ); ?>><?php echo $label; ?></option>
			<?php endforeach; ?>
		</select>

		<?php
		/**
		 * Fires in the report page actions area.
		 *
		 * Allows you to add new elements/actions after the "Report Type" drop down.
		 *
		 * @since 1.0
		 */
		do_action( 'walkthecounty_report_view_actions' );
		?>

		<input type="hidden" name="post_type" value="walkthecounty_forms"/>
		<input type="hidden" name="page" value="walkthecounty-reports"/>
		<?php submit_button( esc_html__( 'Show', 'walkthecounty' ), 'secondary', 'submit', false ); ?>
	</form>
	<?php
	/**
	 * Fires after the report page actions form.
	 *
	 * @since 1.0
	 */
	do_action( 'walkthecounty_report_view_actions_after' );
}

/**
 * Renders the Reports WalkTheCounty Form Table
 *
 * @since 1.0
 * @uses  WalkTheCounty_Form_Reports_Table::prepare_items()
 * @uses  WalkTheCounty_Form_Reports_Table::display()
 * @return void
 */
function walkthecounty_reports_forms_table() {

	if ( isset( $_GET['form-id'] ) ) {
		return;
	}

	include WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/reports/class-form-reports-table.php';

	$walkthecounty_table = new WalkTheCounty_Form_Reports_Table();
	$walkthecounty_table->prepare_items();
	$walkthecounty_table->display();
	?>
	<input type="hidden" name="post_type" value="walkthecounty_forms"/>
	<input type="hidden" name="page" value="walkthecounty-reports"/>
	<input type="hidden" name="tab" value="forms"/>
	<?php
}

add_action( 'walkthecounty_reports_view_forms', 'walkthecounty_reports_forms_table' );

/**
 * Renders the detailed report for a specific walkthecounty form.
 *
 * @since 1.0
 * @return void
 */
function walkthecounty_reports_form_details() {
	if ( ! isset( $_GET['form-id'] ) ) {
		return;
	}
	?>
	<div class="tablenav top reports-forms-details-wrap">
		<div class="actions bulkactions">
			<button onclick="history.go(-1);" class="button-secondary"><?php esc_html_e( 'Go Back', 'walkthecounty' ); ?></button>
		</div>
	</div>
	<?php
	walkthecounty_reports_graph_of_form( absint( $_GET['form-id'] ) );
}

add_action( 'walkthecounty_reports_view_forms', 'walkthecounty_reports_form_details' );

/**
 * Renders the Gateways Table
 *
 * @since 1.3
 * @uses  WalkTheCounty_Gateway_Reports_Table::prepare_items()
 * @uses  WalkTheCounty_Gateway_Reports_Table::display()
 * @return void
 */
function walkthecounty_reports_gateways_table() {
	include WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/reports/class-gateways-reports-table.php';

	$walkthecounty_table = new WalkTheCounty_Gateway_Reports_Table();
	$walkthecounty_table->prepare_items();
	$walkthecounty_table->display();
}

add_action( 'walkthecounty_reports_view_gateways', 'walkthecounty_reports_gateways_table' );

/**
 * Renders the Reports Earnings Graphs
 *
 * @since 1.0
 * @return void
 */
function walkthecounty_reports_earnings() {
	?>
	<div class="tablenav top reports-table-nav">
		<h2 class="reports-earnings-title screen-reader-text"><?php _e( 'Income Report', 'walkthecounty' ); ?></h2>
	</div>
	<?php
	walkthecounty_reports_graph();
}

add_action( 'walkthecounty_reports_view_earnings', 'walkthecounty_reports_earnings' );


/**
 * Retrieves estimated monthly earnings and sales
 *
 * @since 1.0
 * @return array
 */
function walkthecounty_estimated_monthly_stats() {

	$estimated = WalkTheCounty_Cache::get( 'walkthecounty_estimated_monthly_stats', true );

	if ( false === $estimated ) {

		$estimated = array(
			'earnings' => 0,
			'sales'    => 0,
		);

		$stats = new WalkTheCounty_Payment_Stats;

		$to_date_earnings = $stats->get_earnings( 0, 'this_month' );
		$to_date_sales    = $stats->get_sales( 0, 'this_month' );

		$current_day   = date( 'd', current_time( 'timestamp' ) );
		$current_month = date( 'n', current_time( 'timestamp' ) );
		$current_year  = date( 'Y', current_time( 'timestamp' ) );
		$days_in_month = cal_days_in_month( CAL_GREGORIAN, $current_month, $current_year );

		$estimated['earnings'] = ( $to_date_earnings / $current_day ) * $days_in_month;
		$estimated['sales']    = ( $to_date_sales / $current_day ) * $days_in_month;

		// Cache for one day
		WalkTheCounty_Cache::set( 'walkthecounty_estimated_monthly_stats', $estimated, DAY_IN_SECONDS, true );
	}

	return maybe_unserialize( $estimated );
}

/**
 * Assign Get form method for reporting tabs
 *
 * @since 1.8.12
 *
 * @return string
 */
function walkthecounty_reports_set_form_method() {
	return 'get';
}
add_filter( 'walkthecounty-reports_form_method_tab_forms', 'walkthecounty_reports_set_form_method', 10 );
add_filter( 'walkthecounty-reports_form_method_tab_donors', 'walkthecounty_reports_set_form_method', 10 );

// @TODO: After release 1.8 Donations -> Reports generates with new setting api, so we can remove some old code from this file.
