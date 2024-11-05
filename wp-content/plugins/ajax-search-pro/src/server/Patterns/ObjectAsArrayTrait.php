<?php

namespace WPDRMS\ASP\Patterns;

use stdClass;

/**
 * Trait to allow accessing class attributes via array keys,
 * used with objects implementing ArrayAccess.
 *
 * Properties have to be declared in the implementing class, dynamic properties are not allowed
 * to be accessed or created and will trigger a notice.
 *
 * Property visibility is not respected through array access, so private and protected
 * properties can be read via $obj['property'].
 */
trait ObjectAsArrayTrait {
	/**
	 * DO NOT ADD PROPERTIES!
	 * Classes implementing this trait might want to be casted to an array.
	 * If there is a private or protected property, then it will be casted to [*property] which goes to null.
	 */

	/**
	 * @param array<string, mixed> $args
	 */
	public function __construct( array $args = array() ) {
		foreach ( $args as $property => $value ) {
			if ( isset($this->{$property}) ) {
				$this->{$property} = $value;
			} else {
				trigger_error("Property $property passed to constructor does not exist."); // @phpcs:ignore
			}
		}
	}

	public function offsetSet( $property, $value ): void {
		if ( !is_null($property) && isset($this->{$property}) ) {
			$this->{$property} = $value;
		} else {
			/**
			 * This will notify if a non existing property of the object was being accessed.
			 */
			trigger_error("Property $property does not exist."); // @phpcs:ignore
		}
	}

	public function offsetExists( $property ): bool {
		return isset($this->{$property});
	}

	public function offsetUnset( $property ): void {}

	/**
	 * @param mixed $property
	 * @return mixed|null
	 * @noinspection PhpIssetCanBeReplacedWithCoalesceInspection
	 * @noinspection PhpLanguageLevelInspection
	 */
	#[\ReturnTypeWillChange]
	public function &offsetGet( $property ) {
		/**
		 * Mind, this is a return by reference, therefore:
		 *
		 * 1. This CAN'T be replaced with null coalesce
		 * 2. This CAN'T be simplified to ternary:
		 *      return isset($this->{$property}) ? $this->{$property} : $this->null_ref;
		 *    Reason being that ternary will return a value, not a reference and it violates
		 *    the function return statement.
		 */
		if ( isset($this->{$property}) ) {
			return $this->{$property};
		} else {
			$null = null;
			return $null;
		}
	}
}
