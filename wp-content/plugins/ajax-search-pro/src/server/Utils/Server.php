<?php
namespace WPDRMS\ASP\Utils;

defined('ABSPATH') or die("You can't access this file directly.");

/**
 * Utility class for environment checks and other helper methods.
 */
class Server {
	/**
	 * Determines if the current environment is a local development environment.
	 *
	 * This method checks multiple indicators to ascertain if the environment
	 * is local. It checks:
	 * 1. If WP_DEBUG is defined and true.
	 * 2. If the site URL contains common local development domains.
	 * 3. If the server name is localhost or a loopback address.
	 * 4. If an environment variable (e.g., WP_ENV) is set to 'development'.
	 *
	 * @param bool|null                  $wp_debug Optional. WP_DEBUG value. Default null.
	 * @param string|null                $site_url Optional. Site URL. Default null.
	 * @param array<string, string>|null $server   Optional. Server variables. Default null.
	 * @param string|null                $wp_env   Optional. WP_ENV value. Default null.
	 *
	 * @return bool True if it's a local development environment, false otherwise.
	 * @noinspection HttpUrlsUsage
	 */
	public static function isLocalEnvironment(
		?bool $wp_debug = null,
		?string $site_url = null,
		?array $server = null,
		?string $wp_env = null
	): bool {
		// 1. Check WP_DEBUG
		if ( $wp_debug === null ) {
			$wp_debug = defined('WP_DEBUG') && WP_DEBUG;
		}
		if ( $wp_debug ) {
			return true;
		}

		// 2. Check site_url
		if ( $site_url === null && function_exists('site_url') ) {
			$site_url = site_url();
		}
		if ( $site_url !== null ) {
			$local_domains = array(
				'dev',
				'develop',
				'development',
				'test',
				'testing',
				'qa',
				'staging',
				'stage',
				'sandbox',
				'preprod',
				'uat',
				'beta',
				'ci',
				'cd',
				'integration',
				'preview',
				'next',
				'feature',
				'canary',
				'rc',
				'experimental',
				'edge',
				'perf',
				'loadtest',
				'regression',
				'support',
				'internal',
				'fix',
				'patch',
				'mock',
				'acceptance',
				'release',
				'interim',
				'alpha',
				'emulation',
				'new',
				'v1',
				'v2',
				'maintenance',
				'bugfix',
				'prelaunch',
				'quality',
				'verification',
				'pilot',
			);
			/**
			 * Matches only if they start with https://{domain}...
			 */
			foreach ( $local_domains as $domain ) {
				$variations = array(
					$domain . '.',
					$domain . '-',
					$domain . '_',
					$domain . '0',
					$domain . '1',
					$domain . '2',
					$domain . '3',
					$domain . '4',
					$domain . '5',
					$domain . '6',
					$domain . '7',
					$domain . '8',
					$domain . '9',
				);
				foreach ( $variations as $variation ) {
					if ( strpos($site_url, '://' . $variation) !== false ) {
						return true;
					}
				}
			}

			/**
			 * Matches if contains it anywhere
			 */
			$known_test_domains = array(
				'http://',
				'localhost',
				'127.0.0.1',
				'::1',
				'wpengine.com',
				'.wpenginepowered.com',
				'.kinsta.cloud',
				'.local/',
				'.test/',
			);
			foreach ( $known_test_domains as $domain ) {
				if ( strpos($site_url, $domain) !== false ) {
					return true;
				}
			}

			/**
			 * Matches if ends with
			 */
			$domain_endings = array(
				'.test',
				'.local',
			);
			foreach ( $domain_endings as $domain ) {
				if ( str_ends_with($site_url, $domain) ) {
					return true;
				}
			}
		}

		// 3. Check server name
		if ( $server === null ) {
			$server = $_SERVER;
		}
		$server_name        = isset($server['SERVER_NAME']) ? strtolower($server['SERVER_NAME']) : '';
		$local_server_names = array( 'localhost', '127.0.0.1', '::1' );

		if ( in_array($server_name, $local_server_names, true) ) {
			return true;
		}

		// 4. Check WP_ENV
		if ( $wp_env === null ) {
			$wp_env = getenv('WP_ENV');
		}
		if ( $wp_env && strtolower($wp_env) === 'development' ) {
			return true;
		}

		// Not a local environment
		return false;
	}

	public static function getCleanUrlPath( string $url ): ?string {
		$path = wp_parse_url( $url, PHP_URL_PATH );
		if ( ! $path ) {
			return null;
		}

		// Normalize path
		$path = ltrim( $path, '/' );
		$path = untrailingslashit( $path );

		return $path === '/' || $path === '' ? null : $path;
	}

	public static function getCleanReferrerPath(): ?string {
		$referer = wp_get_referer();
		if ( ! $referer ) {
			return null;
		}

		return self::getCleanUrlPath($referer);
	}
}
