<?php

namespace WPDRMS\ASP\Statistics\ORM;

use WPDRMS\ASP\ORM\Model;

class Interaction extends Model {
	protected static string $table_name = 'asp_stat_interactions';
	protected static array $columns     = array(
		'id'                 => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
		'result_id'          => 'BIGINT(20) NOT NULL',
		'date'               => 'DATETIME NOT NULL',
		'INDEX idx_date'     => '(date)',
		'INDEX rid_rid_date' => '(result_id, date)',
		'PRIMARY KEY'        => '(id)',
	);

	public int $id        = 0;
	public int $result_id = 0;
	public string $date   = '';
}
