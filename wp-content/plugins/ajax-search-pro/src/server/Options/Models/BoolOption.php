<?php

namespace WPDRMS\ASP\Options\Models;

/**
 * @phpstan-type BoolOptionArgs array{
 *     value: boolean,
 * }
 * @extends AbstractOption<BoolOptionArgs>
 */
class BoolOption extends AbstractOption {
	protected array $defaults = array(
		'value' => false,
	);

	public bool $value;

	public function __construct( array $args ) {
		parent::__construct($args);
		$this->value = boolval($this->args['value']);
	}
}
