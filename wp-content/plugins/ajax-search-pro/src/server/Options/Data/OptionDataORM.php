<?php

namespace WPDRMS\ASP\Options\Data;

/**
 * Base interface for Option Object with persistent storage support
 */
interface OptionDataORM extends OptionData {
	/**
	 * Loads object from persistent storage
	 *
	 * @return static
	 */
	public function load(): self;

	/**
	 * Saves object to persistent storage
	 *
	 * @return static
	 */
	public function save(): self;

	/**
	 * Saves default object to persistent storage
	 *
	 * @return static
	 */
	public function saveDefaults(): self;
}
