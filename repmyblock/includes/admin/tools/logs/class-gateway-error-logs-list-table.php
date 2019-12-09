<?php
/**
 * Gateway Error Log View Class.
 *
 * @package     WalkTheCounty
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load WP_List_Table if not loaded.
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * WalkTheCounty_Gateway_Error_Log_Table Class.
 *
 * Renders the gateway errors list table.
 *
 * @access      private
 * @since       1.0
 */
class WalkTheCounty_Gateway_Error_Log_Table extends WP_List_Table {

	/**
	 * Number of items per page.
	 *
	 * @var int
	 * @since 1.0
	 */
	public $per_page = 30;

	/**
	 * Get things started.
	 *
	 * @since 1.0
	 * @see   WP_List_Table::__construct()
	 */
	public function __construct() {
		// Set parent defaults.
		parent::__construct( array(
			'singular' => walkthecounty_get_forms_label_singular(),    // Singular name of the listed records.
			'plural'   => walkthecounty_get_forms_label_plural(),        // Plural name of the listed records.
			'ajax'     => false,// Does this table support ajax?.
		) );
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param array  $item        Contains all the data of the log item.
	 * @param string $column_name The name of the column.
	 *
	 * @return string Column Name.
	 */
	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'ID' :
				return $item['ID_label'];
			case 'payment_id' :
				return empty( $item['payment_id'] ) ? esc_html__( 'n/a', 'walkthecounty' ) : sprintf( "<a href=\"%s\" target=\"_blank\">{$item['payment_id']}</a>", get_edit_post_link( $item['payment_id'] ) );
			case 'gateway' :
				return empty( $item['gateway'] ) ? esc_html__( 'n/a', 'walkthecounty' ) : $item['gateway'];
			case 'error' :
				return esc_html( $item['log_title'] );
			default:
				return $item[ $column_name ];
		}
	}

	/**
	 * Output Error Message Column.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param array $item Contains all the data of the log.
	 *
	 * @return void
	 */
	public function column_message( $item ) {
		?>
		<?php
		echo WalkTheCounty()->tooltips->render_link( array(
			'label'       => __( 'View Log Message', 'walkthecounty' ),
			'tag_content' => '<span class="dashicons dashicons-visibility"></span>',
			'link'        => "#TB_inline?width=640&amp;inlineId=log-message-{$item['ID']}",
			'attributes'  => array(
				'class' => 'thickbox walkthecounty-error-log-details-link button button-small',
			),
		) );
		?>
		<div id="log-message-<?php echo $item['ID']; ?>" style="display:none;">
			<?php

			$serialized = strpos( $item['log_content'], '{"' );

			// Check to see if the log message contains serialized information
			if ( $serialized !== false ) {
				$length = strlen( $item['log_content'] ) - $serialized;
				$intro  = substr( $item['log_content'], 0, - $length );
				$data   = substr( $item['log_content'], $serialized, strlen( $item['log_content'] ) - 1 );

				echo wpautop( $intro );
				echo wpautop( '<strong>' . esc_html__( 'Log data:', 'walkthecounty' ) . '</strong>' );
				echo '<div style="word-wrap: break-word;">' . wpautop( $data ) . '</div>';
			} else {
				// No serialized data found
				echo wpautop( $item['log_content'] );
			}
			?>
		</div>
		<?php
	}

	/**
	 * Retrieve the table columns.
	 *
	 * @access public
	 * @since  1.0
	 * @return array $columns Array of all the list table columns.
	 */
	public function get_columns() {
		$columns = array(
			'ID'         => esc_html__( 'Log ID', 'walkthecounty' ),
			'error'      => esc_html__( 'Error', 'walkthecounty' ),
			'gateway'    => esc_html__( 'Gateway', 'walkthecounty' ),
			'payment_id' => esc_html__( 'Donation ID', 'walkthecounty' ),
			'date'       => esc_html__( 'Date', 'walkthecounty' ),
			'message'    => esc_html__( 'Details', 'walkthecounty' ),
		);

		return $columns;
	}

	/**
	 * Retrieve the current page number
	 *
	 * @access public
	 * @since  1.0
	 * @return int Current page number
	 */
	public function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}

	/**
	 * Outputs the log views
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function bulk_actions( $which = '' ) {
		walkthecounty_log_views();
	}

	/**
	 * Gets the log entries for the current view.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return array $logs_data Array of all the Log entries.
	 */
	public function get_logs() {
		// Prevent the queries from getting cached.
		// Without this there are occasional memory issues for some installs.
		wp_suspend_cache_addition( true );

		$logs_data = array();
		$paged     = $this->get_paged();
		$log_query = array(
			'log_type'       => 'gateway_error',
			'paged'          => $paged,
			'posts_per_page' => $this->per_page,
		);

		$logs = WalkTheCounty()->logs->get_connected_logs( $log_query );

		if ( $logs ) {
			foreach ( $logs as $log ) {

				$logs_data[] = array(
					'ID'          => $log->ID,
					'ID_label'    => '<span class=\'walkthecounty-item-label walkthecounty-item-label-gray\'>' . $log->ID . '</span>',
					'payment_id'  => $log->log_parent,
					'error'       => 'error',
					'gateway'     => walkthecounty_get_payment_gateway( $log->log_parent ),
					'date'        => $log->log_date,
					'log_content' => $log->log_content,
					'log_title'   => $log->log_title,
				);
			}
		}

		return $logs_data;
	}

	/**
	 * Display Tablenav (extended).
	 *
	 * Display the table navigation above or below the table even when no items in the logs,
	 * so nav doesn't disappear.
	 *
	 * @see    : https://github.com/impress-org/walkthecounty/issues/564
	 *
	 * @since  1.4.1
	 * @access protected
	 *
	 * @param string $which
	 */
	protected function display_tablenav( $which ) {
		if ( 'top' === $which ) {
			wp_nonce_field( 'bulk-' . $this->_args['plural'] );
		}
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">

			<div class="alignleft actions bulkactions">
				<?php $this->bulk_actions( $which ); ?>
			</div>
			<?php
			$this->extra_tablenav( $which );
			$this->pagination( $which );
			?>

			<br class="clear"/>
		</div>
		<?php
	}

	/**
	 * Setup the final data for the table
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @uses   WalkTheCounty_Gateway_Error_Log_Table::get_columns()
	 * @uses   WP_List_Table::get_sortable_columns()
	 * @uses   WalkTheCounty_Gateway_Error_Log_Table::get_pagenum()
	 * @uses   WalkTheCounty_Gateway_Error_Log_Table::get_logs()
	 * @uses   WalkTheCounty_Gateway_Error_Log_Table::get_log_count()
	 * @return void
	 */
	public function prepare_items() {

		$columns               = $this->get_columns();
		$hidden                = array(); // No hidden columns
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->get_logs();
		$total_items           = WalkTheCounty()->logs->get_log_count( 0, 'gateway_error' );

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $this->per_page,
				'total_pages' => ceil( $total_items / $this->per_page ),
			)
		);
	}
}
