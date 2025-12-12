<?php

namespace WPDRMS\ASP\Options\Factories;

use WPDRMS\ASP\Options\Models\BoolOption;
use WPDRMS\ASP\Options\Models\BorderOption;
use WPDRMS\ASP\Options\Models\BoxShadowOption;
use WPDRMS\ASP\Options\Models\DirectoryListOption;
use WPDRMS\ASP\Options\Models\IntArrayOption;
use WPDRMS\ASP\Options\Models\IntOption;
use WPDRMS\ASP\Options\Models\Option;
use WPDRMS\ASP\Options\Models\SelectOption;
use WPDRMS\ASP\Options\Models\StringArrayOption;
use WPDRMS\ASP\Options\Models\StringOption;
use WPDRMS\ASP\Patterns\SingletonTrait;
use InvalidArgumentException;

class OptionFactory {
	use SingletonTrait;

	/**
	 * @var array<string, class-string>
	 */
	private const TYPES = array(
		'bool'           => BoolOption::class,
		'int'            => IntOption::class,
		'string'         => StringOption::class,
		'select'         => SelectOption::class,
		'border'         => BorderOption::class,
		'box_shadow'     => BoxShadowOption::class,
		'directory_list' => DirectoryListOption::class,
		'string_array'   => StringArrayOption::class,
		'int_array'      => IntArrayOption::class,
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
			throw new InvalidArgumentException("Invalid option type: $type"); // phpcs:ignore
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
