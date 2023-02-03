<?php
/**
 * Cloner Background Process base class.
 *
 * @package NS_Cloner
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * NS_Cloner_Process base class.
 *
 * Extends WP_Background_Process with progress-tracking framework to enable individual
 * Cloner child processes to automatically track and report their progress.
 */
abstract class NS_Cloner_Process extends WP_Background_Process {

	/**
	 * Prefix for saved options
	 *
	 * @var string
	 */
	protected $prefix = 'ns_cloner';

	/**
	 * Unique key for the current batch
	 *
	 * @var string
	 */
	protected $batch_key = '';

	/**
	 * Unique value for transient lock to differentiate dispatches/instances
	 *
	 * @var string
	 */
	protected $lock_id = '';

	/**
	 * Number of completed tasks in the current session
	 *
	 * @var int
	 */
	protected $task_count = 0;

	/**
	 * User friendly label for what type of object this process handles
	 *
	 * @var string
	 */
	public $report_label = '';

	/**
	 * Push to queue, modified to limit to a maximum batch size.
	 *
	 * @param mixed $data Data.
	 *
	 * @return $this
	 */
	public function push_to_queue( $data ) {
		$this->data[] = $data;

		// Check for exceeding maximum batch size.
		$max_batch = apply_filters( $this->identifier . '_max_batch', 5000 );
		if ( count( $this->data ) >= $max_batch ) {
			ns_cloner()->log->log( "REACHING max batch of *$max_batch* in queue - autosaving and starting new" );
			$this->save();
		}

		return $this;
	}

	/**
	 * Save queue
	 *
	 * @return $this
	 */
	public function save() {
		$this->batch_key = $this->generate_key();
		if ( ! empty( $this->data ) ) {
			// Save the batch/queue items themselves.
			update_site_option( $this->batch_key, $this->data );
			ns_cloner()->log->log( "SAVING batch for $this->identifier with " . count( $this->data ) . ' items.' );
			ns_cloner()->log->handle_any_db_errors();
			// Save progress data for this batch.
			// If we want to have an item whose progress is not tracked, we can add 'ignore_progress' to it.
			// Only items without an ignore_progress key will affect the total and completed progress values.
			$tracked_items = array_filter(
				$this->data,
				function( $item ) {
					return ! isset( $item['ignore_progress'] );
				}
			);
			$this->update_batch_progress(
				$this->batch_key,
				array(
					'total'     => count( $tracked_items ),
					'completed' => 0,
				)
			);
			// Clear the data in case this process is reused on the same request for another batch.
			$this->data = array();
		}
		return $this;
	}

	/**
	 * Get a nonce for dispatching this process.
	 *
	 * @return string
	 */
	public function get_nonce() {
		return wp_create_nonce( $this->identifier );
	}

	/**
	 * Dispatch process
	 *
	 * Modify this to immediately run complete if the queue is empty,
	 * so that other dependent background processes can begin.
	 */
	public function dispatch() {
		if ( $this->is_queue_empty() ) {
			$this->complete();
		} else {
			update_site_option( "{$this->identifier}_dispatched", time() );
			ns_cloner()->log->log( "DISPATCHING *$this->identifier* to " . add_query_arg( $this->get_query_args(), $this->get_query_url() ) );
			$response = parent::dispatch();
			ns_cloner()->log->log( array( 'RECEIVED response to dispatch', $response ) );
		}
		return $this;
	}

	/**
	 * Is process running
	 *
	 * Change this from protected in the parent to public visibility here,
	 * and use lock method that does hard write to the db to prevent overlap.
	 *
	 * @return bool
	 */
	public function is_process_running() {
		ns_cloner()->process_manager->doing_cloning();
		$lock_value = ns_cloner()->db->get_var(
			ns_prepare_option_query(
				'SELECT {value} FROM {table} WHERE {key} = %s',
				$this->identifier . '_lock'
			)
		);
		// phpcs:ignore WordPress.Security.NonceVerification -- this is only called after nonce validation
		if ( isset( $_REQUEST['force_process'] ) ) {
			ns_cloner()->log->log( "FORCING manual run for *$this->identifier* - overriding any existing instances" );
			if ( $lock_value ) {
				ns_cloner()->log->log( "FOUND existing lock $lock_value so deleting it" );
				ns_cloner()->db->query(
					ns_prepare_option_query(
						'DELETE FROM {table} WHERE {key} = %s',
						$this->identifier . '_lock'
					)
				);
			}
		} elseif ( empty( $lock_value ) ) {
			ns_cloner()->log->log( "CHECKING for running *$this->identifier* - none found" );
			return false;
		} elseif ( $lock_value === $this->lock_id ) {
			ns_cloner()->log->log( "CHECKING for running *$this->identifier* - the running process is the current one ($this->lock_id)" );
			return false;
		} else {
			ns_cloner()->log->log( "CHECKING for running *$this->identifier* - found $lock_value" );
			return true;
		}
	}

	/**
	 * Lock process
	 *
	 * Add delay to locking to prevent race conditions, and modified to use direct database
	 * calls rather than transients, because transients can caching
	 */
	protected function lock_process() {
		// Set start time so we can track and avoid timeout.
		$this->start_time = time();
		// Generate unique id for this lock/instance - lets us check in is_process_running()
		// whether the set lock belongs to the current session/instance or another one.
		$this->lock_id = wp_generate_password();
		// Save the lock to db.
		ns_cloner()->log->log( "LOCKING *$this->identifier* with id $this->lock_id" );
		ns_cloner()->db->query(
			ns_prepare_option_query(
				'INSERT INTO {table} ({key}, {value}) VALUES (%s, %s)',
				array( $this->identifier . '_lock', $this->lock_id )
			)
		);
		// Then wait 0.5 seconds to make sure a simultaneous lock hasn't been set.
		// Query DB directly because cache won't know if another instance overwrote the lock.
		// If the set lock isn't from this (earlier) instance, bail and let the later instance take over.
		usleep( apply_filters( 'ns_cloner_process_lock_delay', 0.5 * 1000000 ) );
		if ( $this->get_lock() !== $this->lock_id ) {
			ns_cloner()->log->log( "DETECTED simultaneous *$this->identifier* - ending" );
			exit;
		}
	}

	/**
	 * Get the current lock instance id for a process, if present
	 *
	 * @return string|null
	 */
	protected function get_lock() {
		return ns_cloner()->db->get_var(
			ns_prepare_option_query(
				'SELECT {value} FROM {table} WHERE {key} = %s',
				$this->identifier . '_lock'
			)
		);
	}

	/**
	 * Handle
	 *
	 * Pass each queue item to the task handler, while remaining
	 * within server memory and time limit constraints.
	 */
	public function handle() {
		// Initialize sections because this is what all the section hooks get set up on.
		ns_cloner()->process_manager->doing_cloning();
		ns_cloner()->log->log_break();
		ns_cloner()->log->log( "HANDLING <b>$this->action</b> async request" );
		// phpcs:ignore WordPress.Security.NonceVerification -- no harm to checking for flag
		ns_cloner()->log->log( array( 'DISPATCHED from:', isset( $_REQUEST['ajax'] ) ? 'client' : 'server' ) );
		// Remove dispatched flag/timestamp so frontend won't keep dispatching it.
		delete_site_option( "{$this->identifier}_dispatched" );
		// Pass back to parent for handling.
		parent::handle();
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
		// If we want to have an item whose progress is not tracked, we can add 'ignore_progress' to it.
		// Only items without an ignore_progress key will affect the total and completed progress values.
		if ( ! isset( $item['ignore_progress'] ) ) {
			$this->task_count++;
		}
		// Update task count only if it's above threshold. Update too often and you lose performance,
		// update too seldom and you lose responsiveness in the progress UI.
		$progress_update_interval = apply_filters( 'ns_cloner_progress_update_interval', 5, $this->identifier );
		if ( $this->task_count >= $progress_update_interval ) {
			$progress = $this->get_batch_progress( $this->batch_key );
			$this->update_batch_progress(
				$this->batch_key,
				array( 'completed' => $progress['completed'] + $this->task_count )
			);
			$this->task_count = 0;
		}
		return false;
	}

	/**
	 * Unlock process
	 *
	 * Unlock the process so that other instances can spawn.
	 * Modified to include after_handle() - see that function for details.
	 *
	 * @return $this
	 */
	protected function unlock_process() {
		$this->after_handle();
		ns_cloner()->db->query(
			ns_prepare_option_query(
				'DELETE FROM {table} WHERE {key} = %s',
				$this->identifier . '_lock'
			)
		);
		return $this;
	}

	/**
	 * Run actions after completing a set of tasks.
	 *
	 * This is so we have a way to do a complete-type action that runs not just at the veru end but after each
	 * session of the process (i.e. after resources limits are reached and it's going to dispatch a new
	 * version of itself). That's useful for submitting remote requests, saving progress, anything that
	 * needs current state of cross-task variables.
	 */
	protected function after_handle() {
		$progress = $this->get_batch_progress( $this->batch_key );
		$this->update_batch_progress(
			$this->batch_key,
			array( 'completed' => $progress['completed'] + $this->task_count )
		);
	}

	/**
	 * Complete.
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		ns_cloner()->log->log( "COMPLETING *$this->identifier*" );
		parent::complete();
		// Add action so that dependent processes can start.
		do_action( $this->identifier . '_complete' );
		// Check if this was the last process to complete, and the operation can be finished.
		ns_cloner()->process_manager->maybe_finish();
	}

	/**
	 * Cancel
	 *
	 * Different from cancel_process in parent. This removes ALL batches,
	 * not just the top one, and it removes saved progress for batches that
	 * have been completed (thus should be called after progress data is no
	 * longer needed - i.e. reporting has already been made). It also clears
	 * any scheduled cron health check in the future.
	 */
	public function cancel() {
		ns_cloner()->db->query(
			ns_prepare_option_query(
				'DELETE FROM {table} WHERE {key} LIKE %s',
				$this->identifier . '_%'
			)
		);
		wp_clear_scheduled_hook( $this->cron_hook_identifier );
		ns_cloner()->log->log( "ENDING $this->action background process and clearing data." );
	}

	/**
	 * Is queue empty
	 *
	 * Change this from protected in the parent to public visibility here
	 *
	 * @return bool
	 */
	public function is_queue_empty() { // phpcs:ignore -- needed to change visibility
		return parent::is_queue_empty();
	}

	/**
	 * Time exceeded.
	 *
	 * Uses parent's time checking, but adds checking for if the cloner has exited.
	 * This is because a process could load a large batch of items into it's memory,
	 * and then just keep looping through them even after 'exit' has been called,
	 * which can make the manual cancel button not work. Still, we don't want to
	 * check EVERY single item because that will decrease performance. Checking
	 * every 5 items is the compromise.
	 *
	 * @return bool
	 */
	protected function time_exceeded() {
		$exceeded = parent::time_exceeded();
		if ( $exceeded ) {
			ns_cloner()->log->log( "EXCEEDED TIME for $this->identifier" );
		}
		// Task count will count from 1-5 and then start over - see task() - so check for exit flag every 5th task.
		if ( 1 === $this->task_count ) {
			// Check exited flag directly to bypass options cache.
			$exited = ns_cloner()->db->get_var(
				ns_prepare_option_query(
					'SELECT {value} FROM {table} WHERE {key} = %s',
					'ns_cloner_exited'
				)
			);
			if ( $exited ) {
				// Need to call cancel, even though it will have already been called once,
				// to erase the extra progress records that may after been recorded since then.
				$this->cancel();
				return true;
			}
		}
		// Normally just use inherited time checking.
		return $exceeded;
	}

	/**
	 * Memory exceeded
	 *
	 * Simply add logging to parent method.
	 *
	 * @return bool
	 */
	protected function memory_exceeded() {
		$exceeded = parent::memory_exceeded();
		if ( $exceeded ) {
			ns_cloner()->log->log( "EXCEEDED MEMORY for $this->identifier with usage of " . size_format( memory_get_usage( true ) ) );
		}
		return $exceeded;
	}

	/**
	 * Get memory limit
	 *
	 * Override because parent doesn't handle values for other units than MB.
	 *
	 * @return int
	 */
	protected function get_memory_limit() {
		if ( function_exists( 'ini_get' ) ) {
			$memory_limit = ini_get( 'memory_limit' );
			if ( ! $memory_limit || -1 === intval( $memory_limit ) ) {
				// Unlimited, set to 32GB.
				$memory_limit = '32G';
			}
		} else {
			// Sensible default.
			$memory_limit = '128M';
		}

		return wp_convert_hr_to_bytes( $memory_limit );
	}

	/**
	 * Get the next batch for this process, and save the unique batch key to reference progress
	 *
	 * @return stdClass
	 */
	public function get_batch() {
		// Use this as a convenient hook to check for a newer log file, and keep long running
		// background processes from writing to the same log file forever and making it gigantic.
		ns_cloner()->log->refresh();
		// Get batch and store key.
		$batch           = parent::get_batch();
		$this->batch_key = $batch->key;
		return $batch;
	}

	/**
	 * Get progress of all existing background process batches.
	 *
	 * Query for progress rows, not the batch data entries themselves, because the batch
	 * data will be deleted by handle() once the batch is complete, but the batch
	 * progress will remain saved for reference until it is cleared with cancel().
	 *
	 * Note that the result uses the batch key, but the value is the value of the
	 * progress record. Essentially this takes the two records that get saved for
	 * every batch - one to store the data and one to store the completion progress
	 * of that batch - and combines them with they key of one and that value of the
	 * other, so the data could easily be retrieved using the batch key (if it hasn't
	 * been completed and deleted yet), but the more often used progress data is the
	 * the easiest to access (provided right in the returned result).
	 *
	 * @return array
	 */
	private function get_batches() {
		$batches = array();
		// Get all progress records for this bg process.
		$progress_rows = ns_cloner()->db->get_results(
			ns_prepare_option_query(
				"SELECT {key} as 'key', {value} as 'value' FROM {table} WHERE {key} LIKE %s	ORDER BY {id} ASC",
				$this->identifier . '_progress_%'
			)
		);
		foreach ( $progress_rows as $row ) {
			$batch_key = str_replace( 'progress', 'batch', $row->key );
			$progress  = json_decode( $row->value, true );
			// Add to results - keyed by the *batch key* but the value is the *progress value*.
			$batches[ $batch_key ] = $progress;
		}
		return $batches;
	}

	/**
	 * Update information about this process' current progress
	 *
	 * @param string $batch_key Unique key of batch to get progress for.
	 * @param array  $data Progress data to update.
	 */
	public function update_batch_progress( $batch_key, $data ) {
		$progress_key = str_replace( 'batch', 'progress', $batch_key );
		if ( empty( $data ) ) {
			// Delete the progress it if was set to a blank value.
			delete_site_option( $progress_key );
			ns_cloner()->log->log( "DELETING progress for <b>$batch_key</b>" );
		} else {
			// Otherwise, update progress with the provided values.
			$progress     = $this->get_batch_progress( $batch_key );
			$new_progress = wp_json_encode( array_merge( $progress, $data ) );
			update_site_option( $progress_key, $new_progress );
			ns_cloner()->log->log( array( 'UPDATING ' . $progress_key, $new_progress ) );
		}
	}

	/**
	 * Get information about this process' current progress
	 *
	 * @param string $batch_key Unique key of batch to get progress for.
	 * @return array|mixed
	 */
	public function get_batch_progress( $batch_key ) {
		$progress_key = str_replace( 'batch', 'progress', $batch_key );
		$progress     = json_decode( get_site_option( $progress_key ), true );
		if ( ! is_array( $progress ) ) {
			$progress = array(
				'total'     => 0,
				'completed' => 0,
			);
		}
		return $progress;
	}

	/**
	 * Get total progress of all batches, not just the current one
	 *
	 * @return array|mixed
	 */
	public function get_total_progress() {
		$progress = array(
			'completed' => 0,
			'total'     => 0,
		);
		// Loop through each batch and aggregate progress data.
		foreach ( $this->get_batches() as $batch_key => $batch_progress ) {
			// Add item counts together.
			$progress['completed'] += $batch_progress['completed'];
			$progress['total']     += $batch_progress['total'];
		}
		return $progress;
	}

}

