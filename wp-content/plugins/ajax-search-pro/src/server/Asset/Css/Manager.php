<?php
namespace WPDRMS\ASP\Asset\Css;

use WPDRMS\ASP\Asset\AssetManager;
use WPDRMS\ASP\Asset\ManagerInterface;
use WPDRMS\ASP\Patterns\SingletonTrait;
use WPDRMS\ASP\Utils\Html;

if ( !defined('ABSPATH') ) {
	die('-1');
}

class Manager extends AssetManager implements ManagerInterface {
	use SingletonTrait;

	private
		$method,	// file, optimized, inline
		$force_inline = false,	// When "file" is the $method, but the files can't be created
		$media_query,
		$minify;
	public
		$generator;

	function __construct() {
		$comp_settings = wd_asp()->o['asp_compatibility'];
		$this->method = $comp_settings['css_loading_method']; // optimized, inline, file
		$this->minify = $comp_settings['css_minify'];
		$this->media_query = get_site_option("asp_media_query", "defncss");
		$this->generator = new Generator( $this->minify );

		$this->adjustOptionsForCompatibility();

		if ( $this->method == 'optimized' || $this->method == 'file' ) {
			if ( !$this->generator->verifyFiles() ) {
				$this->generator->generate();
				if ( !$this->generator->verifyFiles() ) {
					// Swap to inline if the files were not generated
					$this->force_inline = true;
				}
			}
		}

		/**
		 * Call order:
		 *  wp_enqueue_scripts 			-> enqueue()
		 *  wp_head 					-> headerStartBuffer()  -> start buffer
		 *  shutdown				 	-> print()				-> end buffer trigger
		 */
	}

	/**
	 * Called at wp_enqueue_scripts
	 */
	function enqueue( $force = false ): void {
		if ( $force || $this->method == 'file' ) {
			if ( !$this->generator->verifyFiles() ) {
				$this->generator->generate();
				if ( !$this->generator->verifyFiles() ) {
					$this->force_inline = true;
				}
			}
			// Still enqueue to the head, but the file was not possible to create.
			if ( $this->force_inline ) {
				add_action('wp_head', function(){
					echo $this->getBasic();
					echo $this->getInstances();
				}, 999);
			} else {
				wp_enqueue_style('asp-instances', $this->url('instances'), array(), $this->media_query);	
			}
		}
	}

	// asp_ob_end
	function injectToBuffer($buffer, $instances): string {
		if ( $this->method != 'file' ) {
			$output = $this->getBasic();
			$output .= $this->getInstances( $instances );
			Html::inject($output, $buffer);
		}
		return $buffer;
	}

	/**
	 * Called at shutdown, after asp_ob_end, checks if the items were printed
	 */
	function printInline( $instances = array() ): void {
		if ( $this->method != 'file' ) {
			echo $this->getBasic();
			echo $this->getInstances($instances);
		}
	}

	private function getBasic(): string {
		$output = '';


		if ( $this->method == 'inline' || $this->force_inline ) {
			$css = get_site_option('asp_css', array('basic' => '', 'instances' => array()));
			if ( $css['basic'] != '' ) {
				$output .= "<style id='asp-basic'>" . $css['basic'] . "</style>";
			}
		} else 	if ( $this->method == 'optimized' ) {
			$output = '<link rel="stylesheet" id="asp-basic" href="' . $this->url('basic') . '?mq='.$this->media_query.'" media="all" />';
		}
		return $output;
	}

	private function adjustOptionsForCompatibility() {
		if ( defined('SiteGround_Optimizer\VERSION') ) {
			// SiteGround Optimized CSS combine does not pick up the CSS files when injected
			if ( $this->method == 'optimized' ) {
				$this->method = 'inline';
			}
		}
		
		if ( $this->conflict() ) {
			$this->method = 'file';
		}
	}

	private function getInstances( $instances = false ): string {
		$css = get_site_option('asp_css', array('basic' => '', 'instances' => array()));
		$output = '';
		$instances = $instances === false ? array_keys($css['instances']) : $instances;
		foreach ($instances as $search_id) {
			if ( isset($css['instances'][$search_id]) && $css['instances'][$search_id] != '' ) {
				$output .= "<style id='asp-instance-$search_id'>" . $css['instances'][$search_id] . "</style>";
			}
		}
		return $output;
	}

	private function url( $handle ): string {
		if ( '' != $file = $this->generator->filename($handle) ) {
			return wd_asp()->cache_url . $file;
		} else {
			return '';
		}
	}
}