<?php

namespace WPDRMS\ASP\Options\Data;

use WPDRMS\ASP\Options\Models\Option;

/**
 * Base interface for Option Group Storage
 */
interface OptionData {
	/**
	 * Reset the options and the data with new
	 *
	 * Ex.: when used in previews etc..
	 *
	 * @param Array<string, string> $data
	 */
	public function setData( array $data ): void;

	public function get( string $option_name ): Option;

	/**
	 * @return Option[]
	 */
	public function getAll(): array;
}
