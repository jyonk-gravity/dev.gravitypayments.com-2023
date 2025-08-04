<?php

namespace WPDRMS\ASP\Patterns;

use Closure;

/**
 * Used in classes implementing JsonSerializable
 *
 * When using this trait the class is serialized by returning all public properties.
 * Useful in Model classes when JSON representation of the Model needs to be sent to the front-end via REST.
 */
trait JsonSerializablePublicProperties {
	/**
	 * Returns all public properties from the class
	 *
	 * @return Array<string, mixed>
	 */
	public function publicProperties(): array {
		/**
		 * The get_object_vars($this) would return all properties, including private/protected
		 * This solution creates a closure then "calls" it with $this as the argument, outside
		 * of the scope of the current object.
		 */
		return Closure::fromCallable('get_object_vars')->__invoke($this);
	}

	public function jsonSerialize(): array {
		return $this->publicProperties();
	}

	public function toJson(): string {
		$res = wp_json_encode($this);
		return $res === false ? '{}' : $res;
	}
}
