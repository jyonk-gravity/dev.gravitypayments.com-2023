<?php

namespace WPDRMS\ASP\Options\Routes;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WPDRMS\ASP\Rest\AbstractRest;

class IndexTableOptionsRoute extends AbstractRest {
	public function registerRoutes(): void {
		register_rest_route(
			ASP_DIR,
			'options/index_table/get',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this,
					'getIndexTableOptions',
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
	public function getIndexTableOptions( WP_REST_Request $request ): WP_REST_Response {
		try {
			$data = wd_asp()->o['asp_it_options'];
			return new WP_REST_Response(
				$data,
				200
			);
		} catch ( \Exception $e ) {
			return new WP_REST_Response(
				new WP_Error('asp_it_options_get', $e->getMessage()),
				400
			);
		}
	}
}
