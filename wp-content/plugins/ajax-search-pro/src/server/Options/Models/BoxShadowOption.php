<?php

namespace WPDRMS\ASP\Options\Models;

/**
 * @phpstan-type BoxShadowOptionArgs array{
 *     left: int,
 *     right: int
 * }
 * @extends AbstractOption<BoxShadowOptionArgs>
 */
class BoxShadowOption extends AbstractOption {
	protected array $defaults = array(
		'left'  => 2,
		'right' => 3,
	);
	public int $left          = 2;
	public int $right         = 3;

	public function __construct( array $args ) {
		parent::__construct($args);
		$this->left  = $this->args['left'];
		$this->right = $this->args['right'];
	}
}
