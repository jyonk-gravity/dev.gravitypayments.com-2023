<?php
namespace WPDRMS\ASP\Hooks\Actions;

if ( !defined('ABSPATH') ) {
	die('-1');
}

class Footer extends AbstractAction {
	public function handle(): void {
		$load_assets = apply_filters('asp/assets/load', true);
		$load_css    = apply_filters('asp/assets/load/css', true);

		if ( !$load_assets || !$load_css ) {
			return;
		}

		// Blur for isotopic
		?>
		<div class='asp_hidden_data' id="asp_hidden_data" style="display: none !important;">
			<svg style="position:absolute" height="0" width="0">
				<filter id="aspblur">
					<feGaussianBlur in="SourceGraphic" stdDeviation="4"/>
				</filter>
			</svg>
			<svg style="position:absolute" height="0" width="0">
				<filter id="no_aspblur"></filter>
			</svg>
		</div>
		<?php
	}
}