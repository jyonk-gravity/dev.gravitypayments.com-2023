<?php

namespace WPDRMS\ASP\Options\Data;

use WPDRMS\ASP\Options\Models\DirectoryListOption;

/**
 * Search instance Option Group
 */
final class IndexTableOptions extends AbstractOptionData {
	public DirectoryListOption $attachment_exclude_directories;
	public DirectoryListOption $attachment_include_directories;

	protected const OPTIONS = array(
		'attachment_exclude_directories' => array(
			'type' => 'directory_list',
		),
		'attachment_include_directories' => array(
			'type' => 'directory_list',
		),
	);
}
