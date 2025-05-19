<?php
namespace WPDRMS\ASP\Shortcodes;

use WPDRMS\ASP\Patterns\SingletonTrait;

if ( !defined('ABSPATH') ) {
	die('-1');
}

class AjaxSearchLite extends AbstractShortcode {
	use SingletonTrait;

	public function handle( $atts ): string {
		foreach ( wd_asp()->instances->get() as $instance ) {
			return do_shortcode("[wd_asp id={$instance['id']}]");
		}
		return '';
	}
}
