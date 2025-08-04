<?php

namespace WPDRMS\ASP\Options\Routes;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WPDRMS\ASP\Rest\AbstractRest;
use WPDRMS\ASP\Utils\FileManager;

class DirectoriesRoute extends AbstractRest {
	public function registerRoutes(): void {
		register_rest_route(
			ASP_DIR,
			'options/directories/get',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this,
					'getDirectories',
				),
				'permission_callback' => array(
					$this,
					'allowOnlyAdmins',
				),
			)
		);
	}

	/**
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function getDirectories( WP_REST_Request $request ): WP_REST_Response {
		try {
			$source = $request->get_param('source');
			switch ( $source ) {
				case 'root':
					$source = ABSPATH;
					break;
				case 'wp-content':
					$source = WP_CONTENT_DIR;
					break;
				case 'uploads':
					$uploads = wp_get_upload_dir();
					if ( false === $uploads['error'] ) {
						$source = $uploads['basedir'];
					} else {
						$source = WP_CONTENT_DIR;
					}
					break;
				default:
					$source = WP_CONTENT_DIR;

			}
			$directories = FileManager::instance()->safeListDirectories($source);
			return new WP_REST_Response(
				$directories,
				is_wp_error($directories) ? 500 : 200
			);
		} catch ( \Exception $e ) {
			return new WP_REST_Response(
				new WP_Error('asp_directories_get', $e->getMessage()),
				400
			);
		}
	}
}
