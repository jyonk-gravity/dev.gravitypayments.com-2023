<?php

namespace WPDRMS\ASP\Options\Data;

use WPDRMS\ASP\Options\Models\Option;

/**
 * Base interface for Option Object
 */
interface OptionData {
	/**
	 * Set the option arguments
	 *
	 * Ex.: when used in previews etc..
	 *
	 * @param Array<string, string> $args
	 * @param bool                  $merge Merges with existing arguments if true, resets arguments if false
	 * @return static
	 */
	public function setArgs( array $args, bool $merge = true ): self;

	public function get( string $option_name ): Option;

	/**
	 * @return Option[]
	 */
	public function getAll(): array;

	public function getDefault( string $option_name ): Option;

	/**
	 * @return Option[]
	 */
	public function getDefaults(): array;
}
