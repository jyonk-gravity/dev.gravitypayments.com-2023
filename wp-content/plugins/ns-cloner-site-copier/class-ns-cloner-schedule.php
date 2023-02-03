<?php
/**
 * Cloner Scheduling / cron class.
 *
 * @package NS_Cloner
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class NS_Cloner_Schedule
 *
 * Manages the cron schedule for cloning operations.
 * Mainly used right now for registration templates.
 */
class NS_Cloner_Schedule {

	/**
	 * Cron hook id for scheduled events.
	 *
	 * @var string
	 */
	private $cron_id = 'ns_cloner_cron';

	/**
	 * Option/sitemeta key for scheduled event queue.
	 *
	 * @var string
	 */
	private $cron_data_key = 'ns_cloner_scheduled';

	/**
	 * NS_Cloner_Schedule constructor.
	 */
	public function __construct() {
		// Register handler for cloner cron events.
		add_action( $this->cron_id, array( $this, 'handle' ) );
		// Add new (default 2 min) interval to existing wp cron intervals.
		// phpcs:ignore WordPress.WP.CronInterval -- intentional addition
		add_filter( 'cron_schedules', array( $this, 'register_interval' ) );
	}

	/**
	 * Register new WP cron interval
	 *
	 * @param array $schedules Existing schedule intervals.
	 * @return array
	 */
	public function register_interval( $schedules ) {
		$ref               = $this->cron_id . '_interval';
		$minutes           = apply_filters( 'ns_cloner_cron_interval', 2 );
		$schedules[ $ref ] = array(
			'interval' => MINUTE_IN_SECONDS * $minutes,
			'display'  => sprintf( 'Every %d Minutes', $minutes ),
		);
		return $schedules;
	}

	/**
	 * Schedule a cloning operation for the future
	 *
	 * @param array  $request Request to set for scheduled operation.
	 * @param int    $time Time to schedule event.
	 * @param string $caller Description of calling source, for debugging/management.
	 */
	public function add( $request, $time, $caller ) {
		// Add timing info to the request for info / debugging.
		$request = array_merge(
			$request,
			array(
				'user_id'    => get_current_user_id(),
				'_caller'    => $caller,
				'_created'   => time(),
				'_scheduled' => $time,
			)
		);
		// Add the scheduled request to the queue of scheduled operations.
		$scheduled          = $this->get();
		$scheduled[ $time ] = $request;
		// Sort by key (time) to save operations in the order they were scheduled, if there's more than one.
		ksort( $scheduled );
		$this->update( $scheduled );
		// Process event - decide whether run now or later.
		if ( $time <= time() && ! ns_cloner()->process_manager->is_in_progress() ) {
			// Run now if applicable (no need to wait for scheduler).
			$this->handle();
		} else {
			// Schedule for future.
			$next = wp_next_scheduled( $this->cron_id );
			if ( ! $next || $next > $time ) {
				wp_schedule_event( $time, $this->cron_id . '_interval', $this->cron_id );
			}
		}
	}

	/**
	 * Get all scheduled clone requests
	 *
	 * @return array
	 */
	public function get() {
		return get_site_option( $this->cron_data_key, array() );
	}

	/**
	 * Update the queue of scheduled operations.
	 *
	 * @param array $scheduled Queue of operation requests.
	 */
	public function update( $scheduled ) {
		update_site_option( $this->cron_data_key, $scheduled );
	}

	/**
	 * Remove an item or items from the scheduled queue of cloning operations.
	 *
	 * @param int|string $index Index of operation to delete, defaults to all of them.
	 */
	public function delete( $index = null ) {
		if ( ! $index ) {
			delete_site_option( $this->cron_data_key );
		} else {
			$scheduled = $this->get();
			if ( isset( $scheduled[ $index ] ) ) {
				unset( $scheduled[ $index ] );
				$this->update( $scheduled );
			}
		}
	}

	/**
	 * Handle the fulfillment of a scheduled cron cloner operation
	 */
	public function handle() {
		// Ensure that plugin functions are available in cron environment.
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		$scheduled = $this->get();
		if ( ns_cloner()->process_manager->is_in_progress() ) {
			// Do wrap up for any hanging operations that didn't call their own finish() for one reason or another.
			ns_cloner()->process_manager->maybe_finish();
		} elseif ( empty( $this->get() ) ) {
			// Cancel the cron event if there are no scheduled operations to come back for.
			wp_clear_scheduled_hook( $this->cron_id );
		} else {
			// There is at least one operation, and no cloning is in progress, so get started.
			$next_request = array_shift( $scheduled );
			foreach ( $next_request as $key => $value ) {
				ns_cloner_request()->set( $key, $value );
			}
			ns_cloner_request()->set_up_vars();
			// Run init to begin.
			ns_cloner()->process_manager->init();
			// Update -  using array_shift() means scheduled_ops already had the current operation removed from it.
			$this->update( $scheduled );
		}
	}

}
