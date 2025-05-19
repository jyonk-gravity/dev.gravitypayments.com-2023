<?php
// see https://confluence.jetbrains.com/display/PhpStorm/PhpStorm+Advanced+Metadata
namespace PHPSTORM_META {
	override(\WPDRMS\ASP\Patterns\SingletonTrait::instance(0), map([
		'border' => \WPDRMS\ASP\Options\Models\BorderOption::class,
		'boxshadow' => \WPDRMS\ASP\Options\Models\BoxShadowOption::class,
		'' => \WPDRMS\ASP\Options\Models\BoxShadowOption::class,
	]));
}