<?php

namespace WPDRMS\ASP\Hooks\Filters;

class JsCompatibility extends AbstractFilter {

	public function handle() {
		// TODO: Implement handle() method.
	}

	/**
	 * Rocket WP js exclude
	 *
	 * @param mixed $handles
	 * @return mixed
	 */
	public function rocket_exclude_js( $handles ) {
		$rules = array(
			'/asp/asp-(.*).js',
			'/ajax-search-pro/(.*)/asp-(.*).js',
		);
		if ( is_null($handles) ) {
			return $rules;
		}
		if ( is_array($handles) ) {
			return array_merge($handles, $rules);
		}
		return $handles;
	}

	/**
	 * Rocket WP inline js exclude by content match
	 *
	 * @param mixed $handles
	 * @return mixed
	 */
	public function rocket_excluded_inline_js_content( $handles ) {
		$rules = array(
			'ajax-search-pro',
		);
		if ( is_null($handles) ) {
			return $rules;
		}
		if ( is_array($handles) ) {
			return array_merge($handles, $rules);
		}
		return $handles;
	}
}
