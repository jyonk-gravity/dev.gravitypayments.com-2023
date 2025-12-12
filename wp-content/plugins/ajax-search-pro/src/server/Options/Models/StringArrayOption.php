<?php

namespace WPDRMS\ASP\Options\Models;

use WPDRMS\ASP\Utils\Str;

/**
 * @phpstan-type StringArrayOptionArgs array{
 *     value: string[],
 *     default_value: string[],
 * }
 * @extends AbstractOption<StringArrayOptionArgs>
 */
class StringArrayOption extends AbstractOption {
	protected array $defaults = array(
		'value'         => array(),
		'default_value' => array(),
	);

	/**
	 * @var string[]
	 */
	public array $value;

	public function __construct( array $args ) {
		parent::__construct($args);
		$this->value = array_map(
			fn ( $v ) => Str::anyToString($v),
			$this->args['value']
		);
	}
}
