<?php

namespace WPDRMS\ASP\Statistics\ORM;

use WPDRMS\ASP\Options\Data\AbstractOptionDataSiteOption;
use WPDRMS\ASP\Options\Models\BoolOption;
use WPDRMS\ASP\Options\Models\IntOption;
use WPDRMS\ASP\Options\Models\SelectOption;
use WPDRMS\ASP\Options\Models\StringArrayOption;
use WPDRMS\ASP\Options\Models\StringOption;

/**
 * Search instance Option Group
 */
final class StatisticsOptions extends AbstractOptionDataSiteOption {
	public BoolOption $status;
	public BoolOption $record_results;
	public IntOption $max_phrase_length;
	public IntOption $record_results_max_count;
	public BoolOption $record_result_interactions;
	public SelectOption $data_retention_age;
	public IntOption $data_retention_max_searches;
	public StringArrayOption $exclude_phrases_partial;
	public StringArrayOption $exclude_phrases_whole;
	public IntOption $realtime_refresh_rate;
	public StringOption $results_page_dom_selector;

	protected const OPTION_NAME = 'asp_search_statistics';

	protected const OPTIONS = array(
		'status'                      => array(
			'type'         => 'bool',
			'default_args' => array(
				'value' => false,
			),
		),
		'record_results'              => array(
			'type'         => 'bool',
			'default_args' => array(
				'value' => true,
			),
		),
		'max_phrase_length'           => array(
			'type'         => 'int',
			'default_args' => array(
				'value' => 100,
				'min'   => 1,
				'max'   => 255,
			),
		),
		'record_results_max_count'    => array(
			'type'         => 'int',
			'default_args' => array(
				'value' => 50,
				'min'   => 1,
				'max'   => 100,
			),
		),
		'record_result_interactions'  => array(
			'type'         => 'bool',
			'default_args' => array(
				'value' => true,
			),
		),
		'data_retention_age'          => array(
			'type'         => 'select',
			'default_args' => array(
				'value'   => '1 year',
				'options' => array(
					'2 year',
					'1 year',
					'6 month',
					'3 month',
					'1 month',
					'2 week',
					'1 week',
				),
			),
		),
		'data_retention_max_searches' => array(
			'type'         => 'int',
			'default_args' => array(
				'value' => 1000000,
				'min'   => 1000,
			),
		),
		'exclude_phrases_partial'     => array(
			'type'         => 'string_array',
			'default_args' => array(
				'value' => array(),
			),
		),
		'exclude_phrases_whole'       => array(
			'type'         => 'string_array',
			'default_args' => array(
				'value' => array(),
			),
		),
		'realtime_refresh_rate'       => array(
			'type'         => 'int',
			'default_args' => array(
				'value' => 5,
				'min'   => 1,
				'max'   => 1800,
			),
		),
		'results_page_dom_selector'   => array(
			'type'         => 'string',
			'default_args' => array(
				'value' => '',
			),
		),
	);
}
