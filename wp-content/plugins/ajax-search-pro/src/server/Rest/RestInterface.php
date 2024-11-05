<?php

namespace WPDRMS\ASP\Rest;

interface RestInterface {
	/**
	 * @return self
	 */
	public static function instance();

	public function registerRoutes(): void;
}
