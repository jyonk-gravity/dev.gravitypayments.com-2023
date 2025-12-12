<?php

namespace WPDRMS\ASP\Statistics\ORM;

use WPDRMS\ASP\ORM\Model;

class Search extends Model {
	protected static string $table_name = 'asp_stat_searches';
	protected static array $columns     = array(
		'id'                => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
		'phrase'            => 'VARCHAR(255) NOT NULL',
		'type'              => 'TINYINT NOT NULL', // 0 results page redirection, 1 live search
		'date'              => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
		'user_id'           => 'BIGINT(20)',
		'asp_id'            => 'BIGINT(20)', // Ajax Search Pro search instance ID
		'blog_id'           => 'BIGINT(20)', // Blog ID, only for multisite setup
		'page'              => 'MEDIUMINT NOT NULL', // Results page number
		'found_results'     => 'MEDIUMINT NOT NULL', // Total results found
		'device_type'       => 'TINYINT NOT NULL', // 1 desktop, 2 tablet, 3 mobile
		'lang'              => 'VARCHAR(5)',       // NULL, "en", "de" etc..
		'referer'           => 'VARCHAR(255)',
		'INDEX idx_phrase'  => '(phrase(50))',
		'INDEX idx_date'    => '(date)',
		'INDEX idx_user_id' => '(user_id)',
		'PRIMARY KEY'       => '(id)',
	);

	public int $id            = 0;
	public string $phrase     = '';
	public ?string $date      = null;
	public int $type          = 0;
	public int $page          = 1;
	public int $found_results = 0;
	public ?int $user_id      = null;
	public ?int $asp_id       = null;
	public ?int $blog_id      = null;
	public ?int $device_type  = 1;
	public ?string $lang      = null;
	public ?string $referer   = null;

	/**
	 * @param string $phrase
	 * @param int    $limit
	 * @return string[]
	 * @noinspection SqlType
	 */
	public static function findPhrases( string $phrase, int $limit = 100 ): array {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_results(
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$wpdb->prepare(
			// phpcs:ignore
				'SELECT DISTINCT(phrase) FROM ' . static::getTableName() . " WHERE phrase LIKE %s LIMIT %d",
				array( $phrase . '%', $limit ),
			),
			ARRAY_A
		);

		if ( !is_array($results) ) {
			return array();
		}

		return array_map(
			function ( $result ) {
				return $result['phrase'];
			},
			$results
		);
	}

	/**
	 * @param string $phrase
	 * @param int    $limit
	 * @return string[]
	 * @noinspection SqlType
	 */
	public static function findReferers( string $phrase, int $limit = 100 ): array {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_results(
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$wpdb->prepare(
			// phpcs:ignore
				'SELECT DISTINCT(referer) FROM ' . static::getTableName() . " WHERE referer LIKE %s LIMIT %d",
				array( '%' . $phrase . '%', $limit ),
			),
			ARRAY_A
		);

		if ( !is_array($results) ) {
			return array();
		}

		return array_map(
			function ( $result ) {
				return $result['referer'];
			},
			$results
		);
	}
}
