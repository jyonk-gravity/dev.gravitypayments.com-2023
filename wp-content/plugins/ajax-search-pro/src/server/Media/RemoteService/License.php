<?php
namespace WPDRMS\ASP\Media\RemoteService;

use WPDRMS\ASP\Patterns\SingletonTrait;

if ( !defined('ABSPATH') ) {
	die('-1');
}

/**
 * @phpstan-type LicenseStatsArr array{
 *      free: bool,
 *      max_files_usage: int,
 *      max_files: int|'unlimited',
 *      max_filesize: int
 *  }
 * @phpstan-type LicenseDataArr array{
 *     license: string,
 *     active: bool,
 *     last_check: int,
 *     stats: LicenseStatsArr
 * }
 */
class License {
	use SingletonTrait;

	/**
	 * @var LicenseDataArr
	 */
	private array $data;
	private string $url = 'https://media1.ajaxsearchpro.com/';

	private function __construct() {
		$this->data = get_option(
			'_asp_media_service_data',
			array(
				'license' => '',
				'active'  => false,
				'stats'   => array(
					'free'            => true,
					'max_files_usage' => 1,
					'max_files'       => 0,
					'max_filesize'    => 0,
				),
			)
		);
		$this->refresh();
	}

	public function active(): bool {
		return $this->data['active'] && $this->get() !== '';
	}

	public function isFree(): bool {
		return $this->data['stats']['free'];
	}

	public function valid(): bool {
		if ( $this->data['stats']['max_files'] === 'unlimited' ) {
			return true;
		}
		if (
			(int) $this->data['stats']['max_files_usage'] >= (int) $this->data['stats']['max_files']
		) {
			/**
			 * The "stats" are updated ONLY during indexing. If the max_file threshold was met during a recent
			 * index, then max_files < max_files_usage forever, and this function would return "false" all the time.
			 * If the last check was performed over 5 minutes ago, the report "true" even if the files
			 * threshold was met, so a request will be made to the media server to verify that.
			 */
			if ( ( time() - (int) $this->data['last_check'] ) > 300 ) {
				return true;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}

	public function refresh(): void {
		if ( $this->active() ) {
			if ( ( time() - (int) $this->data['last_check'] ) > 300 ) {
				$this->activate($this->data['license']);
			}
		}
	}

	/**
	 * @param string $license
	 * @return array{
	 *     success: 1|0,
	 *     text: string
	 * }
	 */
	public function activate( string $license ): array {
		$success = 0;
		if (
			strlen($license) === 36 ||
			preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $license) === 1
		) {
			$response = wp_safe_remote_post(
				$this->url,
				array(
					'body' => array(
						'license' => $license,
					),
				)
			);
			if ( !is_wp_error($response) ) {
				$data = json_decode($response['body'], true); // @phpstan-ignore-line
				if ( !$data['success'] ) {
					$text = $data['text'];
				} else {
					$this->set($license, true, $data['stats']);
					$success = 1;
					$text    = 'License successfully activated!';
				}
			} else {
				$text = $response->get_error_message(); // @phpstan-ignore-line
			}
		} else {
			$text = __('Invalid license key length or missing characters. Please make sure to copy the 36 character license key here.', 'ajax-search-pro');
		}

		return array(
			'success' => $success,
			'text'    => $text,
		);
	}

	public function deactivate(): void {
		$this->data['active'] = false;
		update_option('_asp_media_service_data', $this->data);
	}

	public function delete(): void {
		delete_option('_asp_media_service_data');
	}

	public function get(): string {
		return !empty($this->data['license']) ? $this->data['license'] : '';
	}

	/**
	 * @return LicenseDataArr
	 */
	public function getData(): array {
		return $this->data;
	}

	/**
	 * @param string          $license
	 * @param bool            $active
	 * @param LicenseStatsArr $stats
	 * @return void
	 */
	public function set( string $license, bool $active, array $stats ) {
		$this->data = array(
			'license'    => $license,
			'active'     => $active,
			'last_check' => time(),
			'stats'      => $stats,
		);
		update_option('_asp_media_service_data', $this->data);
	}

	/**
	 * @param LicenseStatsArr|false|array{} $stats
	 * @return void
	 */
	public function setStats( $stats = false ) {
		if ( $stats !== false && count($stats) > 0 && $this->data['license'] !== false ) {
			$this->data['stats'] = $stats;
			update_option(
				'_asp_media_service_data',
				array(
					'license'    => $this->data['license'],
					'active'     => $this->data['active'],
					'last_check' => time(),
					'stats'      => $stats,
				)
			);
		}
	}
}
