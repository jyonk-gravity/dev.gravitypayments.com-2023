<?php
/**
 * Copy Files Background Process
 *
 * @package NS_Cloner
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * NS_Cloner_Files_Process class.
 *
 * Processes a queue of files and copies them to the uploads directory of a target site.
 */
class NS_Cloner_Files_Process extends NS_Cloner_Process {

	/**
	 * Ajax action.
	 *
	 * @var string
	 */
	protected $action = 'files_process';

	/**
	 * Initialize and set label
	 */
	public function __construct() {
		parent::__construct();
		$this->report_label = __( 'Files', 'ns-cloner-site-copier' );

		// Set a lower maximum batch size for files since queue items are bigger (more text for paths).
		add_filter( $this->identifier . '_max_batch', array( $this, 'max_batch' ) );
	}

	/**
	 * Process item.
	 *
	 * @param mixed $item Queue item to process.
	 * @return bool
	 */
	protected function task( $item ) {
		$source      = $item['source'];
		$destination = $item['destination'];

		// Make sure source file exists and destination filed does NOT exist.
		if ( ! is_file( $source ) ) {
			ns_cloner()->log->log( "Source file <b>$source</b> not found. Skipping copy." );
			return false;
		} elseif ( is_file( $destination ) && ! ns_cloner_request()->is_mode( 'clone_over' ) ) {
			ns_cloner()->log->log( "Destination file <b>$destination</b> already exists. Skipping copy." );
			return parent::task( $item );
		}

		// Create destination directory if it doesn't exist already.
		$destination_dir = dirname( $destination );
		if ( ! is_dir( $destination_dir ) ) {
			$missing_dirs = array();
			// Go back up the tree until we get to a dir that DOES exist.
			while ( ! is_dir( $destination_dir ) ) {
				$missing_dirs[]  = $destination_dir;
				$destination_dir = dirname( $destination_dir );
			}
			// Fill in all levels of missing directories.
			foreach ( array_reverse( $missing_dirs ) as $dir ) {
				if ( mkdir( $dir ) ) {
					ns_cloner()->log->log( "Creating directory $dir" );
				} else {
					ns_cloner()->log->log( "Could not create destination $dir. Skipping copy of $source." );
					return false;
				}
			}
		}

		// Attempt to copy file.
		if ( copy( $source, $destination ) ) {
			ns_cloner()->log->log( "Copied file <b>$source</b> to <b>$destination</b>." );
			return parent::task( $item );
		} else {
			ns_cloner()->log->log( "Failed copying file <b>$source</b> to <b>$destination</b>." );
			return false;
		}

	}

	/**
	 * Set a lower maximum batch size for files since queue items are bigger (more text for paths).
	 *
	 * @param int $max Default maximum number of batch items.
	 * @return int
	 */
	public function max_batch( $max ) {
		return 2500;
	}

}
