<?php

namespace WPDRMS\ASP\Statistics;

use Exception;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WPDRMS\ASP\Rest\AbstractRest;
use WPDRMS\ASP\Statistics\ORM\Result;
use WPDRMS\ASP\Statistics\ORM\Search;
use WPDRMS\ASP\Statistics\ORM\StatisticsOptions;

class StatisticsRoute extends AbstractRest {
	public function registerRoutes(): void {
		register_rest_route(
			ASP_DIR,
			'options/statistics',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array(
						$this,
						'getStatisticsOptions',
					),
					'permission_callback' => array(
						$this,
						'allowOnlyAdmins',
					),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array(
						$this,
						'saveStatisticsOptions',
					),
					'permission_callback' => array(
						$this,
						'allowOnlyAdmins',
					),
				),
			),
		);

		register_rest_route(
			ASP_DIR,
			'options/statistics/reset',
			array(
				'methods'             => 'POST',
				'callback'            => array(
					$this,
					'resetStatisticsOptions',
				),
				'permission_callback' => array(
					$this,
					'allowOnlyAdmins',
				),
			)
		);

		register_rest_route(
			ASP_DIR,
			'statistics/realtime',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this,
					'getRealtimeStats',
				),
				'permission_callback' => array(
					$this,
					'allowOnlyAdmins',
				),
			)
		);

		register_rest_route(
			ASP_DIR,
			'statistics/searches/latest',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this,
					'getStatisticsLatestSearches',
				),
				'permission_callback' => array(
					$this,
					'allowOnlyAdmins',
				),
			)
		);

		register_rest_route(
			ASP_DIR,
			'statistics/searches/delete',
			array(
				'methods'             => 'POST',
				'callback'            => array(
					$this,
					'deleteStatisticsLatestSearch',
				),
				'permission_callback' => array(
					$this,
					'allowOnlyAdmins',
				),
			)
		);


		register_rest_route(
			ASP_DIR,
			'statistics/searches/volume',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this,
					'getStatisticsSearchesVolume',
				),
				'permission_callback' => array(
					$this,
					'allowOnlyAdmins',
				),
			)
		);

		register_rest_route(
			ASP_DIR,
			'statistics/searches/popular',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this,
					'getStatisticsPopularSearches',
				),
				'permission_callback' => array(
					$this,
					'allowOnlyAdmins',
				),
			)
		);

		register_rest_route(
			ASP_DIR,
			'statistics/searches/volume/csv',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this,
					'exportStatisticsSearchesVolumeToCSV',
				),
				'permission_callback' => array(
					$this,
					'allowOnlyAdmins',
				),
			)
		);

		register_rest_route(
			ASP_DIR,
			'statistics/searches/popular/csv',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this,
					'exportStatisticsPopularSearchesToCSV',
				),
				'permission_callback' => array(
					$this,
					'allowOnlyAdmins',
				),
			)
		);

		register_rest_route(
			ASP_DIR,
			'statistics/searches/latest/csv',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this,
					'exportStatisticsLatestSearchesToCSV',
				),
				'permission_callback' => array(
					$this,
					'allowOnlyAdmins',
				),
			)
		);

		register_rest_route(
			ASP_DIR,
			'statistics/results',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this,
					'getStatisticsResults',
				),
				'permission_callback' => array(
					$this,
					'allowOnlyAdmins',
				),
			)
		);

		register_rest_route(
			ASP_DIR,
			'statistics/results/popular',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this,
					'getStatisticsPopularResults',
				),
				'permission_callback' => array(
					$this,
					'allowOnlyAdmins',
				),
			)
		);

		register_rest_route(
			ASP_DIR,
			'statistics/results/latest',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this,
					'getStatisticsLatestResults',
				),
				'permission_callback' => array(
					$this,
					'allowOnlyAdmins',
				),
			)
		);

		register_rest_route(
			ASP_DIR,
			'statistics/results/latest/csv',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this,
					'exportStatisticsResultsLatestToCSV',
				),
				'permission_callback' => array(
					$this,
					'allowOnlyAdmins',
				),
			)
		);

		register_rest_route(
			ASP_DIR,
			'statistics/results/popular/csv',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this,
					'exportStatisticsResultsPopularToCSV',
				),
				'permission_callback' => array(
					$this,
					'allowOnlyAdmins',
				),
			)
		);

		register_rest_route(
			ASP_DIR,
			'statistics/interactions/latest',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this,
					'getStatisticsInteractionsLatest',
				),
				'permission_callback' => array(
					$this,
					'allowOnlyAdmins',
				),
			)
		);

		register_rest_route(
			ASP_DIR,
			'statistics/interactions/latest/csv',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this,
					'exportStatisticsInteractionsLatestToCSV',
				),
				'permission_callback' => array(
					$this,
					'allowOnlyAdmins',
				),
			)
		);

		// To be removed
		register_rest_route(
			ASP_DIR,
			'statistics/interaction/add',
			array(
				'methods'             => 'POST',
				'callback'            => array(
					$this,
					'registerInteraction',
				),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			ASP_DIR,
			'statistics/reset',
			array(
				'methods'             => 'POST',
				'callback'            => array(
					$this,
					'resetStatistics',
				),
				'permission_callback' => array(
					$this,
					'allowOnlyAdmins',
				),
			)
		);
	}

	public function registerInteraction( WP_REST_Request $request ) {
		try {
			$options = StatisticsOptions::instance();
			if (
				!$options->status->value ||
				!$options->record_results->value ||
				!$options->record_result_interactions->value
			) {
				throw new Exception('Search statistics interaction recording is disabled.');
			}
			$params = $request->get_json_params();
			if ( isset($params['url']) ) {
				$result_id = url_to_postid( $params['url'] );
			} else {
				$result_id = $params['result_id'] ?? 0;
			}
			$interaction = StatisticsService::instance()->addInteraction(
				$params['search_id'] ?? 0,
				$result_id,
				$params['content_type'] ?? 'pagepost',
			);
			if ( $interaction === null ) {
				throw new Exception('Interaction not registered.');
			}
			return new WP_REST_Response(
				$interaction,
				200
			);
		} catch ( Exception $e ) {
			return new WP_Error('interaction_add', $e->getMessage());
		}
	}

	public function getStatisticsOptions() {
		try {
			return new WP_REST_Response(
				StatisticsOptions::instance(),
				200
			);
		} catch ( Exception $e ) {
			return new WP_Error('statistics_options_get', $e->getMessage());
		}
	}

	public function getStatisticsResults( WP_REST_Request $request ) {
		try {
			$search_id = $request->get_param('search_id') ?? 0;
			$results   = Result::findBy(
				array(
					'search_id' => $search_id,
				),
			);
			return new WP_REST_Response(
				array_map(
					function ( $result ) {
						return StatisticsService::instance()->result_query->getResultDetailsForResultObj($result);
					},
					$results
				),
				200
			);
		} catch ( Exception $e ) {
			return new WP_Error('statistics_get_results', $e->getMessage());
		}
	}

	public function getStatisticsPopularResults( WP_REST_Request $request ) {
		try {
			return new WP_REST_Response(
				StatisticsService::instance()->result_query->getPopularResults($request->get_params()),
				200
			);
		} catch ( Exception $e ) {
			return new WP_Error('statistics_popular_results', $e->getMessage());
		}
	}

	public function getStatisticsLatestResults( WP_REST_Request $request ) {
		try {
			return new WP_REST_Response(
				StatisticsService::instance()->result_query->getLatestResults($request->get_params()),
				200
			);
		} catch ( Exception $e ) {
			return new WP_Error('statistics_latest_results', $e->getMessage());
		}
	}

	public function getStatisticsInteractionsLatest( WP_REST_Request $request ) {
		try {
			return new WP_REST_Response(
				StatisticsService::instance()->interaction_query->getLatestRInteractions($request->get_params()),
				200
			);
		} catch ( Exception $e ) {
			return new WP_Error('statistics_latest_interactions', $e->getMessage());
		}
	}

	public function getStatisticsSearchesVolume( WP_REST_Request $request ) {
		try {
			$params = $request->get_params();
			return new WP_REST_Response(
				StatisticsService::instance()->search_query->getSearchesVolume($params),
				200
			);
		} catch ( Exception $e ) {
			return new WP_Error('statistics_daily_searches', $e->getMessage());
		}
	}

	public function getStatisticsPopularSearches( WP_REST_Request $request ) {
		try {
			$params              = $request->get_params();
			$params['variation'] = 'popular';
			return new WP_REST_Response(
				StatisticsService::instance()->search_query->getSearches($params),
				200
			);
		} catch ( Exception $e ) {
			return new WP_Error('statistics_popular_searches', $e->getMessage());
		}
	}

	public function exportStatisticsSearchesVolumeToCSV( WP_REST_Request $request ) {
		try {
			$params = $request->get_params();
			StatisticsService::instance()->search_csv_exporter->getSearchesVolumeToCsv($params);
		} catch ( Exception $e ) {
			return new WP_Error('statistics_searches_volume_export_csv', $e->getMessage());
		}
	}

	public function exportStatisticsPopularSearchesToCSV( WP_REST_Request $request ) {
		try {
			$params              = $request->get_params();
			$params['variation'] = 'popular';
			StatisticsService::instance()->search_csv_exporter->getSearchesToCsv($params);
		} catch ( Exception $e ) {
			return new WP_Error('statistics_popular_searches_export_csv', $e->getMessage());
		}
	}

	public function exportStatisticsLatestSearchesToCSV( WP_REST_Request $request ) {
		try {
			$params              = $request->get_params();
			$params['variation'] = 'latest';
			StatisticsService::instance()->search_csv_exporter->getSearchesToCsv($params);
		} catch ( Exception $e ) {
			return new WP_Error('statistics_latest_searches_export_csv', $e->getMessage());
		}
	}

	public function exportStatisticsResultsLatestToCSV( WP_REST_Request $request ) {
		try {
			StatisticsService::instance()->results_csv_exporter->getLatestResultsToCsv($request->get_params());
		} catch ( Exception $e ) {
			return new WP_Error('statistics_results_latest_export_csv', $e->getMessage());
		}
	}

	public function exportStatisticsResultsPopularToCSV( WP_REST_Request $request ) {
		try {
			StatisticsService::instance()->results_csv_exporter->getPopularResultsToCsv($request->get_params());
		} catch ( Exception $e ) {
			return new WP_Error('statistics_results_popular_export_csv', $e->getMessage());
		}
	}

	public function exportStatisticsInteractionsLatestToCSV( WP_REST_Request $request ) {
		try {
			StatisticsService::instance()->interaction_csv_exporter->getLatestInteractionsToCsv($request->get_params());
		} catch ( Exception $e ) {
			return new WP_Error('statistics_results_popular_export_csv', $e->getMessage());
		}
	}

	public function getStatisticsLatestSearches( WP_REST_Request $request ) {
		try {
			$params              = $request->get_params();
			$params['variation'] = 'latest';
			return new WP_REST_Response(
				StatisticsService::instance()->search_query->getSearches($params),
				200
			);
		} catch ( Exception $e ) {
			return new WP_Error('statistics_latest_searches', $e->getMessage());
		}
	}

	public function deleteStatisticsLatestSearch( WP_REST_Request $request ) {
		try {
			$id = $request->get_param('id') ?? null;
			if ( $id === null ) {
				throw new Exception('Search ID is required.');
			}
			return new WP_REST_Response(
				StatisticsService::instance()->deleteSearch( $id ),
				200
			);
		} catch ( Exception $e ) {
			return new WP_Error('statistics_latest_searches', $e->getMessage());
		}
	}

	public function saveStatisticsOptions( WP_REST_Request $request ) {
		try {
			$params  = $request->get_json_params();
			$options = StatisticsOptions::instance();
			$options->setArgs($params)
				->save()
				->load(); // Reload data from DB just to be sure it is stored
			return new WP_REST_Response(
				$options,
				200
			);
		} catch ( Exception $e ) {
			return new WP_Error('statistics_options_save', $e->getMessage());
		}
	}

	public function resetStatisticsOptions( WP_REST_Request $request ) {
		try {
			$options = StatisticsOptions::instance();
			$options->saveDefaults()->load();
			return new WP_REST_Response(
				$options,
				200
			);
		} catch ( Exception $e ) {
			return new WP_Error('statistics_options_reset', $e->getMessage());
		}
	}
	public function resetStatistics( WP_REST_Request $request ) {
		try {
			StatisticsService::instance()->reset();
			return new WP_REST_Response(
				array(),
				200
			);
		} catch ( Exception $e ) {
			return new WP_Error('statistics_reset', $e->getMessage());
		}
	}

	public function getRealtimeStats() {
		try {
			return new WP_REST_Response(
				StatisticsService::instance()->search_query->getRealtimeStats(),
				200
			);
		} catch ( Exception $e ) {
			return new WP_Error('statistics_options_get', $e->getMessage());
		}
	}
}
