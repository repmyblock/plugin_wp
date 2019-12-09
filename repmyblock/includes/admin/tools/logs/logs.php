<?php
/**
 * Logs UI
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
 * Renders the logs tab.
 *
 * @since 1.0
 * @return void
 */
function walkthecounty_get_logs_tab() {

	require( WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/tools/logs/logs.php' );

	// Get current section.
	$current_section = $_GET['section'] = walkthecounty_get_current_setting_section();

	/**
	 * Fires the in report page logs view.
	 *
	 * @since 1.0
	 */
	do_action( "walkthecounty_logs_view_{$current_section}" );
}

/**
 * Update Logs
 *
 * @since 2.0.1
 *
 * @return void
 */
function walkthecounty_logs_view_updates() {
	include( WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/tools/logs/class-update-logs-list-table.php' );

	$logs_table = new WalkTheCounty_Update_Log_Table();
	$logs_table->prepare_items();
	?>
	<div class="walkthecounty-log-wrap">

		<?php
		/**
		 * Fires before displaying Payment Error logs.
		 *
		 * @since 2.0.1
		 */
		do_action( 'walkthecounty_logs_update_top' );

		$logs_table->display(); ?>
		<input type="hidden" name="post_type" value="walkthecounty_forms"/>
		<input type="hidden" name="page" value="walkthecounty-tools"/>
		<input type="hidden" name="tab" value="logs"/>
		<input type="hidden" name="section" value="update"/>

		<?php
		/**
		 * Fires after displaying update logs.
		 *
		 * @since 2.0.1
		 */
		do_action( 'walkthecounty_logs_update_bottom' );
		?>

	</div>
	<?php
}

add_action( 'walkthecounty_logs_view_updates', 'walkthecounty_logs_view_updates' );

/**
 * Gateway Error Logs
 *
 * @since 1.0
 * @uses  WalkTheCounty_File_Downloads_Log_Table::prepare_items()
 * @uses  WalkTheCounty_File_Downloads_Log_Table::display()
 * @return void
 */
function walkthecounty_logs_view_gateway_errors() {
	include( WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/tools/logs/class-gateway-error-logs-list-table.php' );

	$logs_table = new WalkTheCounty_Gateway_Error_Log_Table();
	$logs_table->prepare_items();
	?>
	<div class="walkthecounty-log-wrap">

		<?php
		/**
		 * Fires before displaying Payment Error logs.
		 *
		 * @since 1.8.12
		 */
		do_action( 'walkthecounty_logs_payment_error_top' );

		$logs_table->display(); ?>
		<input type="hidden" name="post_type" value="walkthecounty_forms"/>
		<input type="hidden" name="page" value="walkthecounty-tools"/>
		<input type="hidden" name="tab" value="logs"/>
		<input type="hidden" name="section" value="gateway_errors"/>

		<?php
		/**
		 * Fires after displaying Payment Error logs.
		 *
		 * @since 1.8.12
		 */
		do_action( 'walkthecounty_logs_payment_error_bottom' );
		?>

	</div>
	<?php
}

add_action( 'walkthecounty_logs_view_gateway_errors', 'walkthecounty_logs_view_gateway_errors' );

/**
 * API Request Logs
 *
 * @since 1.0
 * @uses  WalkTheCounty_API_Request_Log_Table::prepare_items()
 * @uses  WalkTheCounty_API_Request_Log_Table::display()
 * @return void
 */
function walkthecounty_logs_view_api_requests() {
	include( WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/tools/logs/class-api-requests-logs-list-table.php' );

	$logs_table = new WalkTheCounty_API_Request_Log_Table();
	$logs_table->prepare_items();

	/**
	 * Fires before displaying API requests logs.
	 *
	 * @since 1.0
	 */
	do_action( 'walkthecounty_logs_api_requests_top' );

	$logs_table->search_box( esc_html__( 'Search', 'walkthecounty' ), 'walkthecounty-api-requests' );
	$logs_table->display();
	?>
	<input type="hidden" name="post_type" value="walkthecounty_forms"/>
	<input type="hidden" name="page" value="walkthecounty-tools"/>
	<input type="hidden" name="tab" value="logs"/>
	<input type="hidden" name="section" value="api_requests"/>

	<?php
	/**
	 * Fires after displaying API requests logs.
	 *
	 * @since 1.0
	 */
	do_action( 'walkthecounty_logs_api_requests_bottom' );
}
add_action( 'walkthecounty_logs_view_api_requests', 'walkthecounty_logs_view_api_requests' );

/**
 * Renders the log views drop down.
 *
 * @since 1.0
 * @return void
 */
function walkthecounty_log_views() {
	$current_section = walkthecounty_get_current_setting_section();

	// If there are not any event attach to action then do not show form.
	if ( ! has_action( 'walkthecounty_log_view_actions' ) ) {
		return;
	}
	?>
	<form id="walkthecounty-logs-filter" method="get" action="<?php echo 'edit.php?post_type=walkthecounty_forms&page=walkthecounty-tools&tab=logs&section=' . $current_section; ?>">
		<?php
		/**
		 * Fires after displaying the reports page views drop down.
		 *
		 * Allows you to add view actions.
		 *
		 * @since 1.0
		 */
		do_action( 'walkthecounty_log_view_actions' );
		?>

		<input type="hidden" name="post_type" value="walkthecounty_forms"/>
		<input type="hidden" name="page" value="walkthecounty-tools"/>
		<input type="hidden" name="tab" value="logs"/>

		<?php submit_button( esc_html__( 'Apply', 'walkthecounty' ), 'secondary', 'submit', false ); ?>
	</form>
	<?php
}

/**
 * Set Get form method for tools page.
 *
 * Prevents Tools from displaying a "Settings Saved" notice.
 *
 * @since 1.8.12
 *
 * @return string
 */
function walkthecounty_tools_set_form_method( $method ) {
	return 'get';
}
add_filter( 'walkthecounty-tools_form_method_tab_logs', 'walkthecounty_tools_set_form_method', 10 );
