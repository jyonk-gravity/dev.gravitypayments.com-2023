<?php

namespace WPDRMS\ASP\Options\Factories;

use WPDRMS\ASP\Options\Models\BorderOption;
use WPDRMS\ASP\Options\Models\BoxShadowOption;
use WPDRMS\ASP\Options\Models\Option;
use WPDRMS\ASP\Patterns\SingletonTrait;
use InvalidArgumentException;

class OptionFactory {
	use SingletonTrait;

	/**
	 * @var array<string, class-string>
	 */
	private const TYPES = array(
		'border'    => BorderOption::class,
		'boxshadow' => BoxShadowOption::class,
	);

	/**
	 * @param string $type
	 * @param mixed  ...$args
	 *
	 * @return Option
	 * @throws InvalidArgumentException
	 */
	public function create( string $type, ...$args ): Option {
		if ( !isset(self::TYPES[ $type ]) ) {
			throw new InvalidArgumentException('woop');
		}

		$class = self::TYPES[ $type ];

		/**
		* Unfortunately there is no better way for now to intelliJ to recognize
		* type hints based on the return value.
		* A proper solution would be: https://phpstan.org/r/a01e1e49-6f05-43a8-aac7-aded770cd88a
		* But in that case OptionFactory::instance()->create("className")->attr type hint is not working
		* Maybe in the future of IntelliJ?
		*/
		return new $class(...$args);
	}
}
