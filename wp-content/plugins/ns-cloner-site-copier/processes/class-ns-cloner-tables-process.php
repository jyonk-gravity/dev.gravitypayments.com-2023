<?php
/**
 * Copy Tables Background Process
 *
 * @package NS_Cloner
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * NS_Cloner_Tables_Process class.
 *
 * Processes a queue of tables, and delegates and dispatches a new row process for each one.
 */
class NS_Cloner_Tables_Process extends NS_Cloner_Process {

	/**
	 * Ajax action hook
	 *
	 * @var string
	 */
	protected $action = 'tables_process';

	/**
	 * Initialize and set label
	 */
	public function __construct() {
		parent::__construct();
		$this->report_label = __( 'Tables', 'ns-cloner-site-copier' );
	}

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $item Queue item to iterate over.
	 *
	 * @return mixed
	 */
	protected function task( $item ) {

		$rows_process  = ns_cloner()->get_process( 'rows' );
		$source_prefix = ns_cloner()->db->get_blog_prefix( $item['source_id'] );
		$target_prefix = ns_cloner()->db->get_blog_prefix( $item['target_id'] );
		$source_table  = $item['source_table'];
		$target_table  = $item['target_table'];

		// Implement filter to enable leaving a target table in place, if needed.
		// Otherwise, target table will be dropped and replaced.
		if ( apply_filters( 'ns_cloner_do_drop_target_table', true, $target_table ) ) {
			$drop_query  = "DROP TABLE IF EXISTS `$target_table`";
			$drop_result = ns_cloner()->db->query( $drop_query );
			ns_cloner()->log->handle_any_db_errors();
			$create_query = ns_sql_create_table_query( $source_table, $target_table, $source_prefix, $target_prefix );
			// If it was a view, the create query will be returned empty, so skip.
			if ( empty( $create_query ) ) {
				return false;
			}
			$create_result = ns_cloner()->db->query( $create_query );
			ns_cloner()->log->handle_any_db_errors();
			// Abandon this item if table could not be created -
			// don't mark complete by calling parent::task(), or queue rows.
			if ( false === $create_result ) {
				ns_cloner()->log->log( "SKIPPING TABLE *$source_table*. Could not create table." );
				return false;
			}
		}

		// Save row process batches that will actually do the cloning queries.
		// Note that it saves but doesn't dispatch here, because that would cause
		// multiple async requests for this same process, and race conditions.
		// Instead, we'll dispatch it once at the end in the complete() method.
		$where      = apply_filters( 'ns_cloner_rows_where', 'WHERE 1=1', $source_table, $source_prefix );
		$count_rows = ns_cloner()->db->get_var( "SELECT COUNT(*) rows_qty FROM `$source_table` $where" );
		ns_cloner()->log->log( array( "SELECTED $count_rows with query:", "SELECT COUNT(*) rows_qty FROM `$source_table` $where" ) );
		if ( $count_rows > 0 ) {
			// Enable picking up a partially-queued table if it was massive and had to cut out in the middle.
			if ( isset( $item['next_row'] ) ) {
				$next_row = $item['next_row'];
				ns_cloner()->log->log( "RESTARTING partially queued table at row *$next_row*" );
			} else {
				$next_row = 0;
			}
			$next_row = isset( $item['next_row'] ) ? $item['next_row'] : 0;
			// Add a rows process item for each found row in the table.
			for ( $i = $next_row; $i < $count_rows; $i++ ) {
				$row_data = array(
					'row_num'      => $i,
					'source_table' => $source_table,
					'target_table' => $target_table,
					'source_id'    => $item['source_id'],
					'target_id'    => $item['target_id'],
				);
				$rows_process->push_to_queue( $row_data );
				// Check every 5000 rows for timeout.
				if ( $i && 0 === $i % 5000 ) {
					if ( $this->time_exceeded() || $this->memory_exceeded() ) {
						// Return item to be re-processed, with indicator of how many rows were already queued.
						ns_cloner()->log->log( "STOPPING partially queued table at row $i due to resource limits" );
						$item['next_row'] = $i + 1;
						return $item;
					}
				}
			}
			$rows_process->save();
			ns_cloner()->log->log( "QUEUEING *$count_rows* rows from *$source_table* to *$target_table*" );
		} else {
			ns_cloner()->log->log( "SKIPPING TABLE *$source_table*, 0 rows found." );
		}

		return parent::task( $item );

	}

}
