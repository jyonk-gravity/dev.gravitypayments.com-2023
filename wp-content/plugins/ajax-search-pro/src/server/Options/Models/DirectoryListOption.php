<?php

namespace WPDRMS\ASP\Options\Models;

/**
 * @phpstan-type DirectoryListOptionArgs array{
 *     directories: string[],
 * }
 * @extends AbstractOption<DirectoryListOptionArgs>
 */
class DirectoryListOption extends AbstractOption {
	protected array $defaults = array(
		'directories' => array(),
	);

	/**
	 * @var string[]
	 */
	public array $directories;

	public function __construct( array $args ) {
		parent::__construct($args);
		$this->directories = array_filter($this->args['directories']);
	}
}
