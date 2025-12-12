<?php

namespace WPDRMS\ASP\Statistics\ORM;

use WPDRMS\ASP\ORM\Model;


class Result extends Model {
	protected static string $table_name = 'asp_stat_results';
	protected static array $columns     = array(
		'id'                    => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
		'search_id'             => 'BIGINT(20) NOT NULL',
		'result_id'             => 'BIGINT(20) NOT NULL',
		'result_type'           => 'TINYINT NOT NULL',
		'PRIMARY KEY'           => '(id)',
		'INDEX idx_result_id'   => '(result_id)',
		'INDEX idx_result_type' => '(result_type)',
		/**
		 * No need for a separate index for search_id as composite indexes speed up
		 * the left-most columns only with "AND", ex.:
		 * col1=val1, col1=val1 & col2=val2, col1=val1 & col2=val2 & ... & colN=valN
		 *
		 * @see https://dev.mysql.com/doc/refman/8.4/en/multiple-column-indexes.html
		 */
		'INDEX idx_rid_sid_rty' => '(search_id, result_type)',
	);

	public int $id        = 0;
	public int $search_id = 0;
	public int $result_id = 0;

	/**
	 * Corresponds to $r->content_type
	 * 1 - "pagepost"
	 * 2 - "term"
	 * 3 - "user"
	 * 4 - "blog"
	 * 5 - "bp_group"
	 * 6 - "bp_activity"
	 * 7 - "comment"
	 * 8 - "attachment"
	 * 9 - "peepso_activity"
	 * 10 - "peepso_group"
	 *
	 * @var int $result_type
	 */
	public int $result_type = 1;
}
