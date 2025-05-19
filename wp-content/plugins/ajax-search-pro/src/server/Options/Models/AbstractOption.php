<?php

namespace WPDRMS\ASP\Options\Models;

use Closure;
use JsonSerializable;
use WPDRMS\ASP\Utils\ArrayUtils;

/**
 * @template T of Array<string, mixed>
 */
class AbstractOption implements Option, JsonSerializable {
	/**
	 * @var T
	 */
	protected array $defaults = array();

	/**
	 * The arguments from the constructor, merged and corrected with the defaults
	 *
	 * @var T
	 */
	protected array $args = array();

	/**
	 * @param Array<string, mixed> $args
	 */
	public function __construct( array $args ) {
		$this->args = ArrayUtils::arrayMergeRecursiveDistinct($this->defaults, $args);
	}
	/**
	 * Returns all public properties from the option
	 *
	 * @return Array<string, mixed>
	 */
	public function value(): array {
		/**
		 * The get_object_vars($this) would return all properties, including private/protected
		 * This solution creates a closure then "calls" it with $this as the argument, outside
		 * of the scope of the current object.
		 */
		return Closure::fromCallable('get_object_vars')->__invoke($this);
	}

	/**
	 * @return T
	 */
	public function jsonSerialize(): array {
		return $this->value();
	}

	public function toJson(): string {
		$res = wp_json_encode($this);
		return $res === false ? '{}' : $res;
	}
}
