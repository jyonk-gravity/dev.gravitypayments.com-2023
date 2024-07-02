<?php /** @noinspection HttpUrlsUsage */

namespace WPDRMS\ASP\Misc;

use WP_Error;

if ( !defined('ABSPATH') ) {
	die('-1');
}


class EnvatoLicense {

	static $url = 'https://update.wp-dreams.com/';

	static function activate( $license_key ) {
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
			update_option(
				'asp_update_data',
				array(
					'key'  => $license_key,
					'host' => $_SERVER['HTTP_HOST'] ?? 'unkwown.domain',
				)
			);
		}

		return $data;
	}

	static function deactivate( $remote_check = true ) {
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
		delete_option('asp_update_data');
		return $data;
	}

	static function isActivated( $remote_check = false, $auto_local_deactivate = false ) {
		$data = get_option('asp_update_data');
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
			$ret   = $rdata['status'] == 1 ? $data['key'] : false;
			if ( $auto_local_deactivate && $ret === false ) {
				self::deactivate( false );
			}
			return $rdata['status'] == 1 ? $data['key'] : false;
		}

		return $data['key'];
	}
}
