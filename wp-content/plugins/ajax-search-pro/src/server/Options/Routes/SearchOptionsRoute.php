<?php

namespace WPDRMS\ASP\Options\Routes;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WPDRMS\ASP\Rest\AbstractRest;

class SearchOptionsRoute extends AbstractRest {
	public function registerRoutes(): void {
		register_rest_route(
			ASP_DIR,
			'options/search_instance/get',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this,
					'getSearchInstanceOptions',
				),
				'permission_callback' => array(
					$this,
					'allowOnlyAdmins',
				),
			)
		);

		/**
		 * List all search instances without options
		 */
		register_rest_route(
			ASP_DIR,
			'options/search_instance/list',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this,
					'listSearchInstances',
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
	public function getSearchInstanceOptions( WP_REST_Request $request ): WP_REST_Response {
		try {
			$id = $request->get_param('id') ?? 0;
			if ( !wd_asp()->instances->exists($id) ) {
				return new WP_REST_Response(
					new WP_Error('search_instantes_get', 'Search instance does not exist.'),
					400
				);
			}
			$data                                     = wd_asp()->instances->get($id)['data'];
			$data['advtitlefield']                    = stripcslashes($data['advtitlefield']);
			$data['advdescriptionfield']              = stripcslashes($data['advdescriptionfield']);
			$data['user_search_advanced_title_field'] = stripcslashes($data['user_search_advanced_title_field']);
			$data['user_search_advanced_description_field'] =
				stripcslashes($data['user_search_advanced_description_field']);
			return new WP_REST_Response(
				$data,
				200
			);
		} catch ( \Exception $e ) {
			return new WP_REST_Response(
				new WP_Error('taxonomy_terms_get', $e->getMessage()),
				400
			);
		}
	}

	/**
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function listSearchInstances( WP_REST_Request $request ): WP_REST_Response {
		try {
			$data = wd_asp()->instances->getWithoutData() ?? array();
			return new WP_REST_Response(
				array_values($data),
				200
			);
		} catch ( \Exception $e ) {
			return new WP_REST_Response(
				new WP_Error('taxonomy_terms_get', $e->getMessage()),
				400
			);
		}
	}
}
