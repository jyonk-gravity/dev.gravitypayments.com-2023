<?php
namespace WPDRMS\ASP\Patterns;

/**
 * This can be used in Abstract classes, can handle heritage by storing the singleton data in an array
 */
trait SingletonTrait {
	/**
	 * Use a very unspecific name for this to prevent any conflicts of attribute names
	 *
	 * @var array<static>
	 */
	protected static $singleton__object_instances__array = array();

	final public static function getInstance( ...$args ): self {
		$class = get_called_class();
		if ( !isset(static::$singleton__object_instances__array[ $class ]) ) {
			static::$singleton__object_instances__array[ $class ] = new $class(...$args);
		}
		return static::$singleton__object_instances__array[ $class ];
	}

	final public static function instance( ...$args ): self {
		return static::getInstance( ...$args );
	}

	private function __construct() {}

	final public function __wakeup() {}

	final public function __clone() {}
}
