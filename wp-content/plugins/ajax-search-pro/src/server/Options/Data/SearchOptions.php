<?php

namespace WPDRMS\ASP\Options\Data;

/**
 * Search instance Option Group
 */
final class SearchOptions extends AbstractOptionData {
	protected const OPTIONS = array(
		'attachment_exclude_directories' => array(
			'type' => 'directory_list',
		),
		'attachment_include_directories' => array(
			'type' => 'directory_list',
		),
	);
}
