<?php

namespace WPDRMS\ASP\Hooks\Filters;

class JsCompatibility extends AbstractFilter {

	public function handle() {
		// TODO: Implement handle() method.
	}

	/**
	 * Rocket WP
	 *
	 * @param $handles
	 * @return mixed
	 */
	public function pre_get_rocket_option_exclude_js( $handles ) {
		if ( is_null($handles) ) {
			return 'asp-(.*).js';
		}
		if ( is_array($handles) ) {
			$handles[] = 'asp-(.*).js';
		} elseif ( is_string($handles) ) {
			$handles .= '
asp-(.*).js';
		}
		return $handles;
	}
}

