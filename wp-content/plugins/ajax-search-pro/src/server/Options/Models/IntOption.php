<?php

namespace WPDRMS\ASP\Options\Models;

/**
 * @phpstan-type IntOptionArgs array{
 *     value: integer,
 *     min: integer,
 *     max: integer,
 * }
 * @extends AbstractOption<IntOptionArgs>
 */
class IntOption extends AbstractOption {
	protected array $defaults = array(
		'value' => 0,
		'min'   => PHP_INT_MIN,
		'max'   => PHP_INT_MAX,
	);

	public int $value;

	public function __construct( array $args ) {
		parent::__construct($args);
		$this->value = intval($this->args['value']);
		$this->value = max($this->args['min'], min($this->args['max'], $this->value));
	}
}
