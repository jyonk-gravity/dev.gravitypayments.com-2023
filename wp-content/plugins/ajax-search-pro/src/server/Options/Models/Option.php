<?php

namespace WPDRMS\ASP\Options\Models;

interface Option {
	/**
	 * Returns all public properties from the option
	 *
	 * @return Array<string, mixed>
	 */
	public function value(): array;
}
