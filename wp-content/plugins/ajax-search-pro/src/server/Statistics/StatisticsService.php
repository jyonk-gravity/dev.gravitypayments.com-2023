<?php

namespace WPDRMS\ASP\Statistics;

use wpdb;
use WPDRMS\ASP\Models\SearchQueryArgs;
use WPDRMS\ASP\Patterns\SingletonTrait;
use WPDRMS\ASP\Query\SearchQuery as ASP_SearchQuery;
use WPDRMS\ASP\Statistics\Exporters\InteractionCSVExporter;
use WPDRMS\ASP\Statistics\Exporters\ResultsCSVExporter;
use WPDRMS\ASP\Statistics\Exporters\SearchCSVExporter;
use WPDRMS\ASP\Statistics\ORM\Interaction;
use WPDRMS\ASP\Statistics\ORM\Result;
use WPDRMS\ASP\Statistics\ORM\Search;
use WPDRMS\ASP\Statistics\ORM\StatisticsOptions;
use WPDRMS\ASP\Statistics\Queries\InteractionQuery;
use WPDRMS\ASP\Statistics\Queries\ResultQuery;
use WPDRMS\ASP\Statistics\Queries\SearchQuery;
use WPDRMS\ASP\Utils\MB;
use WPDRMS\ASP\Utils\Server;

class StatisticsService {
	use SingletonTrait;

	public StatisticsOptions $options;

	public static array $result_type_arr = array(
		'pagepost'        => 1,
		'term'            => 2,
		'user'            => 3,
		'blog'            => 4,
		'bp_group'        => 5,
		'bp_activity'     => 6,
		'comment'         => 7,
		'attachment'      => 8,
		'peepso_activity' => 9,
		'peepso_group'    => 10,
	);

	public static int $last_search_id = 0;

	/**
	 * @var SearchQuery
	 * @readonly
	 */
	public SearchQuery $search_query;

	public ResultQuery $result_query;
	public InteractionQuery $interaction_query;

	public SearchCSVExporter $search_csv_exporter;

	public ResultsCSVExporter $results_csv_exporter;
	public InteractionCSVExporter $interaction_csv_exporter;

	private function __construct() {
		$this->search_query             = SearchQuery::instance();
		$this->result_query             = ResultQuery::instance();
		$this->interaction_query        = InteractionQuery::instance();
		$this->search_csv_exporter      = SearchCSVExporter::instance();
		$this->interaction_csv_exporter = InteractionCSVExporter::instance();
	}

	public function loadHooks(): void {
		$this->options = StatisticsOptions::instance();
		if ( $this->options->status->value ) {
			add_action(
				'wp_footer',
				function () {
					?>
					<div id="asp-statistics" data-statistics-id="<?php echo esc_attr(self::$last_search_id); ?>" style="display:none;"></div>
					<?php
				}
			);
			add_action('asp/search/results', array( $this, 'registerSearchAndResultsHook' ), 99, 4);
			add_action(
				'asp/statistics/retention',
				function () {
					$this->enforceRetention(
						$this->options->data_retention_age->value,
						$this->options->data_retention_max_searches->value
					);
				}
			);
			if ( ! wp_next_scheduled( 'asp/statistics/retention' ) ) {
				wp_schedule_event( time() + 30, 'hourly', 'asp/statistics/retention' );
			}
		} else {
			if ( wp_next_scheduled( 'asp/statistics/retention' ) ) {
				wp_unschedule_event( wp_next_scheduled( 'asp/statistics/retention' ), 'asp/statistics/retention');
			}
			wp_clear_scheduled_hook('asp/statistics/retention');
		}
	}


	/**
	 * @uses apply_filters('asp/search/results', $results, SearchQueryArgs $args, SearchQuery $query);
	 *
	 * @param array           $results
	 * @param SearchQueryArgs $args
	 * @param SearchQuery     $query
	 * @return array
	 */
	public function registerSearchAndResultsHook( array $results, SearchQueryArgs $args, ASP_SearchQuery $query ) {
		if ( !$args->record_statistics ) {
			return $results;
		}

		/**
		 * Exclude requests from admin preview
		 */
		$referer = Server::getCleanReferrerPath();
		if ( str_starts_with($referer ?? '', Server::getCleanUrlPath( get_admin_url() ) ?? 'wp-admin/admin.php' ) ) {
			return $results;
		}

		/**
		 * Excluded keywords
		 */
		if ( $args->s !== '' ) {
			foreach ( $this->options->exclude_phrases_partial->value as $excluded_phrase ) {
				if ( str_contains($args->s, $excluded_phrase) ) {
					return $results;
				}
			}

			if ( in_array($args->s, $this->options->exclude_phrases_whole->value, true) ) {
				return $results;
			}
		}

		$search          = new Search();
		$search->phrase  = MB::substr(sanitize_text_field($args->s), 0, $this->options->max_phrase_length->value);
		$search->type    = $args->_ajax_search ? 1 : 0;
		$search->user_id = get_current_user_id();
		$search->page    = $args->_ajax_search ? ( $args->_call_num === 0 ? 1 : $args->_call_num + 1 ) : $args->page;
		$search->asp_id  = $args->_id > 0 ? $args->_id : null;
		if ( is_multisite() ) {
			$search->blog_id = get_current_blog_id();
		}
		switch ( $args->device_type ) {
			case 'tablet':
				$search->device_type = 2;
				break;
			case 'mobile':
				$search->device_type = 3;
				break;
			default:
				$search->device_type = 1;
		}
		if ( $args->_wpml_lang !== '' ) {
			$search->lang = $args->_wpml_lang;
		} elseif ( $args->_polylang_lang !== '' ) {
			$search->lang = $args->_polylang_lang;
		}
		$search->found_results = $query->found_posts;
		$search->referer       = $referer;
		$search                = $search->save();  // Save and return updated model
		self::$last_search_id  = $search->id;

		if ( !$search ) {
			return $results;
		}

		if ( $this->options->record_results->value ) {
			$results_arr = array();
			$count       = 0;
			$max_count   = $this->options->record_results_max_count->value;
			foreach ( $results as $result ) {
				$r            = new Result();
				$r->search_id = $search->id;
				$r->result_id = $result->id ?? $result->ID ?? 0;
				if ( $r->result_id === 0 ) {
					continue;
				}
				$r->result_type = isset($result->content_type, self::$result_type_arr[ $result->content_type ]) ?
					self::$result_type_arr[ $result->content_type ] : 1;
				$results_arr[]  = $r;
				++$count;
				if ( $count >= $max_count ) {
					break;
				}
			}
			if ( !empty($results_arr) ) {
				Result::bulkInsert($results_arr);
			}
		}

		return $results;
	}

	public function createTables(): void {
		Search::createTable();
		Result::createTable();
		Interaction::createTable();
	}

	public function dropTables(): void {
		Search::dropTable();
		Result::dropTable();
		Interaction::dropTable();
	}


	/**
	 * Add an interaction
	 *
	 * @param int    $search_id
	 * @param int    $result_id
	 * @param string $result_type
	 * @return ?Interaction The saved Interaction model
	 */
	public function addInteraction( int $search_id, int $result_id, string $result_type ): ?Interaction {
		if ( $result_id < 1 ) {
			return null;
		}

		$type    = self::$result_type_arr[ $result_type ] ?? 1;
		$results = Result::findBy(
			array(
				'search_id'   => $search_id,
				'result_id'   => $result_id,
				'result_type' => $type,
			),
			1
		);
		if ( empty($results) ) {
			return null;
		}
		$result                 = $results[0];
		$interaction            = new Interaction();
		$interaction->result_id = $result->id;
		$interaction->date      = current_time('mysql'); // @phpstan-ignore-line

		return $interaction->save();
	}

	public function deleteSearch( int $id ): bool {
		global $wpdb;
		$search = Search::find($id);
		if ( $search === null ) {
			return false;
		}
		$wpdb->query(
			$wpdb->prepare(
				'DELETE FROM ' . Interaction::getTableName() . ' WHERE result_id IN (
					SELECT id FROM ' . Result::getTableName() . ' WHERE search_id = %d
				)',
				$search->id
			)
		);
		$wpdb->query(
			$wpdb->prepare(
				'DELETE FROM ' . Result::getTableName() . ' WHERE search_id = %d',
				$search->id
			)
		);
		$search->delete();

		return true;
	}

	public function enforceRetention( string $max_age = '1 year', int $max_searches = 1000000 ): void {
		/**
		 * @var wpdb $wpdb;
		 */
		global $wpdb;

		$allowed_max_age = array( '1 year', '2 year', '1 month', '3 month', '6 month', '1 week', '2 week' );
		if ( !in_array($max_age, $allowed_max_age, true ) ) {
			$max_age = '1 year';
		}

		// phpcs:disable
		$wpdb->query(
			'DELETE FROM ' . Interaction::getTableName() . ' WHERE result_id IN (
					SELECT id FROM ' . Result::getTableName() . ' WHERE search_id IN (
                         SELECT id FROM ' . Search::getTableName() . ' WHERE date < DATE_SUB(NOW(), INTERVAL ' . $max_age . ')
                     )
			)'
		);

		// phpcs:disable
		$wpdb->query(
			'DELETE FROM ' . Result::getTableName() . ' WHERE search_id IN (
            SELECT id FROM ' . Search::getTableName() . ' WHERE date < DATE_SUB(NOW(), INTERVAL ' . $max_age . ')
        )'
		);



		$wpdb->query(
			'DELETE FROM ' . Search::getTableName() . ' WHERE date < DATE_SUB(NOW(), INTERVAL ' . $max_age . ')'
		);

		$count = $wpdb->get_var('SELECT COUNT(*) FROM ' . Search::getTableName());
		if ($count > $max_searches) {
			$excess = $count - $max_searches;

			// Use JOIN for interactions deletion
			$wpdb->query(
				$wpdb->prepare(
					'DELETE i FROM ' . Interaction::getTableName() . ' i
				 JOIN (
                   SELECT * FROM ' . Result::getTableName() . '
                 ) r ON i.result_id = r.id
                 JOIN (
                   SELECT id as sid FROM ' . Search::getTableName() . ' ORDER BY date ASC LIMIT %d
                 ) s ON r.search_id = s.sid',
					$excess
				)
			);

			// Use JOIN for results deletion to avoid subquery LIMIT limitation
			$wpdb->query(
				$wpdb->prepare(
					'DELETE r FROM ' . Result::getTableName() . ' r
                 JOIN (
                   SELECT id FROM ' . Search::getTableName() . ' ORDER BY date ASC LIMIT %d
                 ) s ON r.search_id = s.id',
					$excess
				)
			);

			// Direct DELETE for searches (no subquery issue)
			$wpdb->query(
				$wpdb->prepare(
					'DELETE FROM ' . Search::getTableName() . ' ORDER BY date ASC LIMIT %d',
					$excess
				)
			);
		}
		// phpcs:enable
	}

	/**
	 * Deletes all data from the tables with an auto-increment reset
	 *
	 * @return void
	 */
	public function reset(): void {
		Search::truncateTable();
		Result::truncateTable();
		Interaction::truncateTable();
	}
}
