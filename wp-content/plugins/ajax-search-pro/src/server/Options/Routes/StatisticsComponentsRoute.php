<?php

namespace WPDRMS\ASP\Options\Routes;

use Exception;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WPDRMS\ASP\Rest\AbstractRest;
use WPDRMS\ASP\Statistics\ORM\Search;

class StatisticsComponentsRoute extends AbstractRest {
	public function registerRoutes(): void {
		register_rest_route(
			ASP_DIR,
			'options/statistics/search_phrases',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this,
					'getPhrases',
				),
				'permission_callback' => array(
					$this,
					'allowOnlyAdmins',
				),
			)
		);

		register_rest_route(
			ASP_DIR,
			'options/statistics/search_referers',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this,
					'getReferers',
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
	 * @return WP_REST_Response|WP_Error
	 */
	public function getPhrases( WP_REST_Request $request ) {
		try {
			$number = intval($request->get_param('number') ?? 100);
			$search = strval($request->get_param('search') ?? '');

			$searches = Search::findPhrases($search, $number);
			return new WP_REST_Response(
				$searches,
				is_wp_error($searches) ? 500 : 200
			);
		} catch ( Exception $e ) {
			return new WP_Error('options_phrases_get', $e->getMessage());
		}
	}

	/**
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function getReferers( WP_REST_Request $request ) {
		try {
			$number = intval($request->get_param('number') ?? 100);
			$search = strval($request->get_param('search') ?? '');

			$searches = Search::findReferers($search, $number);
			return new WP_REST_Response(
				$searches,
				is_wp_error($searches) ? 500 : 200
			);
		} catch ( Exception $e ) {
			return new WP_Error('options_referers_get', $e->getMessage());
		}
	}
}
