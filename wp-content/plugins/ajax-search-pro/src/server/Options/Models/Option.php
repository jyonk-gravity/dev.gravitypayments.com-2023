<?php

namespace WPDRMS\ASP\Options\Models;

interface Option {
	/**
	 * Returns the option value for storage
	 *
	 * @return Array<string, mixed>
	 */
	public function value(): array;
}
