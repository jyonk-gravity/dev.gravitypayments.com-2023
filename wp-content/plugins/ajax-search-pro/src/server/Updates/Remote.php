<?php /** @noinspection PhpWrongStringConcatenationInspection */

namespace WPDRMS\ASP\Updates;

use WP_Error;
use WPDRMS\ASP\Patterns\SingletonTrait;

class Remote {
	use SingletonTrait;

	private string $url = 'https://update.wp-dreams.com/products/info/3357410';

	// 3 seconds of timeout, no need to hold up the back-end
	private int $timeout = 3;

	private int $interval = 7200;

	private string $option_name = 'asp_updates_json';

	private array $data = array();

	private int $version             = ASP_CURR_VER;
	private string $version_string   = ASP_CURR_VER_STRING;
	private string $requires_version = '4.9';
	private string $tested_version;
	private int $downloaded_count = 10000;

	private string $last_updated;

	// -------------------------------------------- Auto Updater Stuff here---------------------------------------------
	public $title = 'Ajax Search Pro';

	function __construct() {
		global $wp_version;
		$this->tested_version = $wp_version;
		$this->last_updated   = date('Y-m-d');

		if (
			defined('ASP_BLOCK_EXTERNAL') ||
			( defined('WP_HTTP_BLOCK_EXTERNAL') && WP_HTTP_BLOCK_EXTERNAL )
		) {
			return false;
		}

		$this->getData();
		$this->processData();

		return true;
	}

	public function getData( $force_update = false ) {
		// Redundant: Let's make sure, that the version check is not executed during Ajax requests, by any chance
		if ( !( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			$last_checked = get_option($this->option_name . '_lc', time() - $this->interval - 500);

			if ( !empty($this->data) && $force_update === false ) {
				return;
			}

			if (
				( ( time() - $this->interval ) > $last_checked ) ||
				$force_update
			) {
				$response = wp_remote_get( $this->url );
				if ( $response instanceof WP_Error ) {
					$this->data = get_option($this->option_name, array());
				} else {
					$data = json_decode($response['body'], true);
					if ( isset($data['version']) ) {
						$this->data = json_decode($response['body'], true);
						update_option($this->option_name, $this->data);
					}
				}
				/**
				 * Any case, success or failure, the last checked timer should be updated, otherwise if the remote server
				 * is offline, it will block each back-end page load every time for 'timeout' seconds
				 */
				update_option($this->option_name . '_lc', time());
			} else {
				$this->data = get_option($this->option_name, array());
			}
		} else {
			$this->data = get_option($this->option_name, array());
		}
	}

	function processData(): bool {
		if ( empty($this->data) ) {
			return false;
		}

		$this->version          = $this->data['version'] ?? $this->version;
		$this->version_string   = $this->data['version_string'] ?? $this->version_string;
		$this->requires_version = $this->data['requires_version'] ?? $this->requires_version;
		$this->tested_version   = $this->data['tested_version'] ?? $this->tested_version;
		$this->downloaded_count = $this->data['downloaded_count'] ?? $this->downloaded_count;
		$this->last_updated     = $this->data['last_updated'] ?? $this->last_updated;

		return true;
	}

	public function refresh() {
		$this->getData(true );
		$this->processData();
	}

	public function getVersion() {
		return $this->version;
	}

	public function getVersionString() {
		return $this->version_string;
	}

	public function needsUpdate( $refresh = false ) {
		if ( $refresh ) {
			$this->refresh();
		}

		if ( $this->version != '' ) {
			if ( $this->version > ASP_CURR_VER ) {
				return true;
			}
		}

		return false;
	}

	public function printUpdateMessage() {
		?>
		<p class='infoMsgBox'>
			<?php
			printf(
				__('Ajax Search Pro version <strong>%s</strong> is available.', 'ajax-search-pro'),
				$this->getVersionString() 
			);
			?>
			<a target="_blank" href="https://documentation.ajaxsearchpro.com/plugin-updates">
				<?php echo __('How to update?', 'ajax-search-pro'); ?>
			</a>
		</p>
		<?php
	}

	public function getRequiresVersion() {
		return $this->requires_version;
	}

	public function getTestedVersion() {
		return $this->tested_version;
	}

	public function getDownloadedCount() {
		return $this->downloaded_count;
	}

	public function getLastUpdated() {
		return $this->last_updated;
	}
}