<?php

namespace WPDRMS\ASP\Hooks;

class DeprecatedHooks implements Hook {
	public function register(): void {
		add_filter('asp/assets/load', array( $this, '__handle_asp_asset_load' ));
		add_filter('asp/assets/load/css', array( $this, '__handle_asp_asset_load_css' ));
		add_filter('asp/assets/load/js', array( $this, '__handle_asp_asset_load_js' ));
	}

	public function deregister(): void {
		remove_filter('asp/assets/load', array( $this, '__handle_asp_asset_load' ));
		remove_filter('asp/assets/load/css', array( $this, '__handle_asp_asset_load_css' ));
		remove_filter('asp/assets/load/js', array( $this, '__handle_asp_asset_load_js' ));
	}

	public function __handle_asp_asset_load( bool $load ): bool {
		return !apply_filters_deprecated(
			'asp_load_css_js',
			array( !$load ),
			'4.28',
			'asp/assets/load',
			'Please visit this url for more information: https://knowledgebase.ajaxsearchpro.com/hooks/filters/css-and-js/asp_load_css_js'
		);
	}

	public function __handle_asp_asset_load_css( bool $load ): bool {
		return !apply_filters_deprecated(
			'asp_load_css',
			array( !$load ),
			'4.28',
			'asp/assets/load/css',
			'Please visit this url for more information: https://knowledgebase.ajaxsearchpro.com/hooks/filters/css-and-js/asp_load_css'
		);
	}

	public function __handle_asp_asset_load_js( bool $load ): bool {
		return !apply_filters_deprecated(
			'asp_load_js',
			array( !$load ),
			'4.28',
			'asp/assets/load/js',
			'Please visit this url for more information: https://knowledgebase.ajaxsearchpro.com/hooks/filters/css-and-js/asp_load_js'
		);
	}
}
