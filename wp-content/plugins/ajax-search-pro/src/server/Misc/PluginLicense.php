<?php /** @noinspection HttpUrlsUsage */

namespace WPDRMS\ASP\Misc;

use WP_Error;

if ( !defined('ABSPATH') ) {
	die('-1');
}


class PluginLicense {

	static $url = 'https://update.wp-dreams.com/';

	private const OPTION_NAME = 'asp_license_data';

	public static function activate( $license_key ) {
		$url      = isset($_SERVER['HTTP_HOST']) ? rawurlencode($_SERVER['HTTP_HOST']) : 'https://unkwown.domain';
		$key      = rawurlencode( $license_key );
		$url      = self::$url . "license/activate/$key/?url=" . $url;
		$response = wp_remote_post( $url );
		if ( $response instanceof WP_Error ) {
			return false;
		}

		$data = json_decode( $response['body'], true );

		// something went wrong
		if ( empty($data) ) {
			return false;
		}

		if ( isset($data['status']) && $data['status'] == 1 ) {
			update_site_option(
				self::OPTION_NAME,
				array(
					'key'  => $license_key,
					'host' => $_SERVER['HTTP_HOST'] ?? 'unkwown.domain',
				)
			);
		}

		return $data;
	}

	public static function deactivate( $remote_check = true ) {
		$data = false;
		if ( $remote_check ) {
			$key = self::isActivated();
			if ( false !== $key ) {
				$url      = isset($_SERVER['HTTP_HOST']) ? rawurlencode($_SERVER['HTTP_HOST']) : 'unkwown.domain';
				$key      = rawurlencode($key);
				$url      = self::$url . "license/deactivate/$key?url=" . $url;
				$response = wp_remote_request($url, array( 'method' =>'PATCH' ));
				if ( $response instanceof WP_Error ) {
					return false;
				}
				$data = json_decode($response['body'], true);
			}
		}
		delete_site_option(self::OPTION_NAME);
		return $data;
	}

	public static function isActivated( bool $remote_check = false ) {
		self::convertToSiteOption();
		$data = get_site_option(self::OPTION_NAME);
		if ( $data === false || !isset($data['host']) || !isset($data['key']) ) {
			return false;
		}

		if ( $remote_check ) {
			$url      = isset($_SERVER['HTTP_HOST']) ? rawurlencode($_SERVER['HTTP_HOST']) : 'unknown.domain';
			$key      = rawurlencode( $data['key'] );
			$url      = self::$url . "license/is_active/$key/?url=" . $url;
			$response = wp_remote_get( $url );
			if ( $response instanceof WP_Error ) {
				return false;
			}
			$rdata = json_decode( $response['body'], true );
			if (
				isset($rdata['message']) && (
					str_contains($rdata['message'], 'invalid format') ||
					str_contains($rdata['message'], 'refunded') ||
					str_contains($rdata['message'], 'canceled')
				)
			) {
				self::deactivate( false );
				return false;
			}
			return $rdata['status'] ? $data['key'] : false;
		}

		return $data['key'];
	}

	private static function convertToSiteOption() {
		// Old option with the old key
		$data = get_option('asp_update_data');
		if ( $data !== false ) {
			update_site_option(self::OPTION_NAME, $data);
			delete_option('asp_update_data');
		}
	}
}
