<?php

namespace WPDRMS\ASP\Core\Models;

use ArrayAccess;
use WPDRMS\ASP\Options\Data\SearchOptions;
use WPDRMS\ASP\Patterns\ObjectAsArrayTrait;

/**
 * @implements ArrayAccess<string, mixed>
 */
class SearchInstance implements ArrayAccess {
	use ObjectAsArrayTrait;

	public string $name;

	/**
	 * @var Array<string, mixed>
	 */
	public array $raw_data;

	/**
	 * @var Array<string, mixed>
	 */
	public array $data;

	public int $id;

	public SearchOptions $options;

	/**
	 * @param Array<string, mixed> $args
	 */
	public function __construct( array $args = array() ) {
		$this->name = $args['name'] ?? '';
		$this->id   = $args['id'] ?? 0;
		$this->data = $args['data'] ?? array();
		if ( isset($args['options']) ) {
			$this->options = new SearchOptions($args['options']);
		}
	}
}
