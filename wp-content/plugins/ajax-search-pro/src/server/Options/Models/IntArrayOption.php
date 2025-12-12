<?php

namespace WPDRMS\ASP\Options\Models;

/**
 * @phpstan-type IntArrayOptionArgs array{
 *     value: int[],
 *     default_value: int[],
 * }
 * @extends AbstractOption<IntArrayOptionArgs>
 */
class IntArrayOption extends AbstractOption {
	protected array $defaults = array(
		'value'         => array(),
		'default_value' => array(),
	);

	/**
	 * @var int[]
	 */
	public array $value;

	public function __construct( array $args ) {
		parent::__construct($args);
		$this->value = array_map(
			fn ( $v ) => intval($v),
			$this->args['value']
		);
	}
}
