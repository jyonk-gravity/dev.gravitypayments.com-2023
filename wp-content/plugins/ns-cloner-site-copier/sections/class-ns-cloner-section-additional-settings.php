<?php
/**
 * Additional Settings Section class
 *
 * @package NS_Cloner
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class NS_Cloner_Section_Additional_Settings
 *
 * Adds extra settings box to all modes (checkbox to enable additional detailed debug logging, etc.
 */
class NS_Cloner_Section_Additional_Settings extends NS_Cloner_Section {

	/**
	 * Mode ids that this section should be visible and active for.
	 *
	 * @var array
	 */
	public $modes_supported = array( 'core', 'clone_over', 'search_replace', 'clone_teleport' );

	/**
	 * DOM id for section box.
	 *
	 * @var string
	 */
	public $id = 'additional_settings';

	/**
	 * Priority relative to other section boxes in UI.
	 *
	 * @var int
	 */
	public $ui_priority = 1000;

	/**
	 * Do any setup before starting the cloning process (like hooks to modify the process).
	 */
	public function process_init() {
		add_filter( 'ns_cloner_rows_per_query', array( $this, 'filter_rows_per_query' ) );
		add_filter( 'ns_cloner_progress_update_interval', array( $this, 'filter_progress_update_interval' ) );
		add_filter( 'ns_cloner_skip_views', array( $this, 'filter_skip_views' ) );
		add_filter( 'ns_cloner_skip_constraints', array( $this, 'filter_skip_constraints' ) );
	}

	/**
	 * Output content for section settings box on admin page.
	 */
	public function render() {
		$this->open_section_box( __( 'Additional Settings', 'ns-cloner-site-copier' ) );
		?>
		<h5><?php esc_html_e( 'Debugging', 'ns-cloner-site-copier' ); ?></h5>
		<label>
			<input type="checkbox" name="debug" />
			<?php esc_html_e( 'Enable logging', 'ns-cloner-site-copier' ); ?>
		</label>
		<p class="description">
			<strong>
			<?php esc_html_e( 'Logs may contain sensitive information from your database.', 'ns-cloner-site-copier' ); ?>
			<?php esc_html_e( 'If you enable logging, it\'s recommended to go to NS Cloner > Logs and clear your logs when you are finished.', 'ns-cloner-site-copier' ); ?>
			</strong>
		</p>
		<h5><?php esc_html_e( 'Performance', 'ns-cloner-site-copier' ); ?></h5>
		<div class="ns-cloner-input-group">
			<label class="before"><?php esc_html_e( 'Rows per query', 'ns-cloner-site-copier' ); ?></label>
			<input type="number" name="rows_per_query" placeholder="50" />
		</div>
		<p class="description">
			<?php esc_html_e( 'This controls how many database records will be copied at one time.', 'ns-cloner-site-copier' ); ?>
			<?php esc_html_e( 'You can make cloning faster by increasing this number, but if it\'s too large for your server to handle you\'ll see SQL errors and need to reduce this setting again.', 'ns-cloner-site-copier' ); ?>
		</p>
		<div class="ns-cloner-input-group">
			<label class="before"><?php esc_html_e( 'Progress update interval', 'ns-cloner-site-copier' ); ?></label>
			<input type="number" name="progress_update_interval" placeholder="5" />
		</div>
		<p class="description">
			<?php esc_html_e( 'This is the number of items (rows or files) to clone in between updating the progress values.', 'ns-cloner-site-copier' ); ?>
			<?php esc_html_e( 'You can make cloning faster by increasing this number, but the higher it is the more jumpy and less smooth/accurate the progress bar will be.', 'ns-cloner-site-copier' ); ?>
		</p>
		<label>
			<input type="checkbox" name="flush_cache"/>
			<?php esc_html_e( 'Flush object cache after cloning', 'ns-cloner-site-copier' ); ?>
		</label>
		<p class="description">
			<strong>
				<?php esc_html_e( 'Check this option to clear the object cache after cloning. This will cause a temporary performance decrease on your site.', 'ns-cloner-site-copier' ); ?>
				<?php if ( is_multisite() ) : ?>
					<?php esc_html_e( 'For WordPress multisite instances using a persistent object cache, flushing the object cache will typically flush the cache for all sites.', 'ns-cloner-site-copier' ); ?>
				<?php endif; ?>
			</strong>
		</p>
		<h5><?php esc_html_e( 'Database', 'ns-cloner-site-copier' ); ?></h5>
		<label>
			<input type="checkbox" name="skip_views"/>
			<?php esc_html_e( 'Skip views?', 'ns-cloner-site-copier' ); ?>
		</label>
		<p class="description">
			<strong>
				<?php esc_html_e( 'This will prevent attempting to clone any SQL views along with tables, which can be required by plugins but can sometimes result in permissions errors.', 'ns-cloner-site-copier' ); ?>
			</strong>
		</p>
		<label>
			<input type="checkbox" name="skip_constraints"/>
			<?php esc_html_e( 'Skip constraints?', 'ns-cloner-site-copier' ); ?>
		</label>
		<p class="description">
			<strong>
				<?php esc_html_e( 'This will prevent trying to re-apply any SQL constraints to cloned tables, which help ensure data integrity but can sometimes result in permissions errors.', 'ns-cloner-site-copier' ); ?>
			</strong>
		</p>
		<?php
		$this->close_section_box();
	}

	/**
	 * Adjust the number of records to include in a single query.
	 *
	 * @param int $default Number of rows.
	 * @return int
	 */
	public function filter_rows_per_query( $default ) {
		$custom = (int) ns_cloner_request()->get( 'rows_per_query' );
		return $custom > 0 ? $custom : $default;
	}

	/**
	 * Adjust the number of items to clone in between updating progress.
	 *
	 * @param int $default Number of items.
	 * @return int
	 */
	public function filter_progress_update_interval( $default ) {
		$custom = (int) ns_cloner_request()->get( 'progress_update_interval' );
		return $custom > 0 ? $custom : $default;
	}

	/**
	 * Apply checkbox value to whether to skip SQL views when cloning tables.
	 *
	 * @param bool $default Whether to skip.
	 * @return bool
	 */
	public function filter_skip_views( $default ) {
		return (bool) ns_cloner_request()->get( 'skip_views', false );
	}

	/**
	 * Apply checkbox value to whether to skip re-applying SQL constraints to cloned tables.
	 *
	 * @param bool $default The unmodified default.
	 * @return bool
	 */
	public function filter_skip_constraints( $default ) {
		return (bool) ns_cloner_request()->get( 'skip_constraints', false );
	}

}
