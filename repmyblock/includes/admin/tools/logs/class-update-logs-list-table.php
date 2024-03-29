<?php
/**
 * Update Log View Class
 *
 * @package     WalkTheCounty
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.0.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * WalkTheCounty_Update_Log_Table List Table Class
 *
 * Renders the update log list table
 *
 * @since 2.0.1
 */
class WalkTheCounty_Update_Log_Table extends WP_List_Table {
	/**
	 * Number of items per page
	 *
	 * @var int
	 * @since 2.0.1
	 */
	public $per_page = 30;

	/**
	 * Get things started
	 *
	 * @since 2.0.1
	 * @see   WP_List_Table::__construct()
	 */
	public function __construct() {
		global $status, $page;

		// Set parent defaults
		parent::__construct( array(
			'singular' => walkthecounty_get_forms_label_singular(),    // Singular name of the listed records
			'plural'   => walkthecounty_get_forms_label_plural(),        // Plural name of the listed records
			'ajax'     => false, // Does this table support ajax?
		) );
	}

	/**
	 * Show the search field
	 *
	 * @since  2.0.1
	 * @access public
	 *
	 * @param string $text     Label for the search box
	 * @param string $input_id ID of the search box
	 *
	 * @return void
	 */
	public function search_box( $text, $input_id ) {
	}

	/**
	 * Retrieve the table columns
	 *
	 * @access public
	 * @since  2.0.1
	 *
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'ID'      => __( 'Log ID', 'walkthecounty' ),
			'date'    => __( 'Date', 'walkthecounty' ),
			'details' => __( 'Process Details', 'walkthecounty' ),
		);

		return $columns;
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access public
	 * @since  2.0.1
	 *
	 * @param array  $item        Contains all the data of the discount code
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'ID':
				return sprintf(
					'<span class="walkthecounty-item-label walkthecounty-item-label-gray">%1$s</span> %2$s',
					esc_attr( $item[ $column_name ] ),
					esc_attr( $item['title'] )
				);

			default:
				return esc_attr( $item[ $column_name ] );
		}
	}

	/**
	 * Output Error Message column
	 *
	 * @access public
	 * @since  2.0.1
	 *
	 * @param array $item Contains all the data of the log
	 *
	 * @return void
	 */
	public function column_details( $item ) {
		echo WalkTheCounty()->tooltips->render_link( array(
			'label'       => __( 'View Update Log', 'walkthecounty' ),
			'tag_content' => '<span class="dashicons dashicons-visibility"></span>',
			'link'        => "#TB_inline?width=640&amp;inlineId=log-details-{$item['ID']}",
			'attributes'  => array(
				'class' => 'thickbox walkthecounty-error-log-details-link button button-small',
			),
		) );
		?>
		<div id="log-details-<?php echo esc_attr( $item['ID'] ); ?>" style="display:none;">
			<?php

			// Print Log Content, if not empty.
			if ( ! empty( $item['log_content'] ) ) {
				echo sprintf(
					'<p><pre>%1$s</pre></div>',
					esc_html( $item['log_content'] )
				);
			}
			?>
		</div>
		<?php
	}


	/**
	 * Display Tablenav (extended)
	 *
	 * Display the table navigation above or below the table even when no items in the logs, so nav doesn't disappear
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
	 * Retrieve the current page number
	 *
	 * @access public
	 * @since  2.0.1
	 *
	 * @return int Current page number
	 */
	public function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}

	/**
	 * Outputs the log views
	 *
	 * @param string $which Top or Bottom.
	 *
	 * @access public
	 * @since  2.0.1
	 *
	 * @return void
	 */
	function bulk_actions( $which = '' ) {
	}

	/**
	 * Gets the log entries for the current view
	 *
	 * @access public
	 * @since  2.0.1
	 *
	 * @return array $logs_data Array of all the Log entires
	 */
	public function get_logs() {
		$logs_data = array();
		$paged     = $this->get_paged();
		$log_query = array(
			'log_type'       => 'update',
			'paged'          => $paged,
			'posts_per_page' => $this->per_page,
		);

		$logs = WalkTheCounty()->logs->get_connected_logs( $log_query );

		if ( $logs ) {
			foreach ( $logs as $log ) {

				$logs_data[] = array(
					'ID'          => $log->ID,
					'title'       => $log->log_title,
					'date'        => $log->log_date,
					'log_content' => $log->log_content,
					'log_date'    => $log->log_date,
				);
			}
		}

		return $logs_data;
	}

	/**
	 * Setup the final data for the table
	 *
	 * @access public
	 * @since  2.0.1
	 * @uses   WalkTheCounty_Update_Log_Table::get_columns()
	 * @uses   WP_List_Table::get_sortable_columns()
	 * @uses   WalkTheCounty_Update_Log_Table::get_pagenum()
	 * @uses   WalkTheCounty_Update_Log_Table::get_logs()
	 * @uses   WalkTheCounty_Update_Log_Table::get_log_count()
	 *
	 * @return void
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array(); // No hidden columns
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->get_logs();
		$total_items           = WalkTheCounty()->logs->get_log_count( 0, 'update' );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $this->per_page,
			'total_pages' => ceil( $total_items / $this->per_page ),
		) );
	}
}
