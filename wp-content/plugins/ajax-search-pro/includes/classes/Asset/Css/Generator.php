<?php
namespace WPDRMS\ASP\Asset\Css;

use WPDRMS\ASP\Asset\GeneratorInterface;
use WPDRMS\ASP\Asset\Script\Requirements;
use WPDRMS\ASP\Utils\FileManager;
use WPDRMS\ASP\Utils\Str;
use WPDRMS\ASP\Utils\Css;

defined('ABSPATH') or die("You can't access this file directly.");

if ( !class_exists(__NAMESPACE__ . '\Generator') ) {
	class Generator implements GeneratorInterface {
		private
			$basic_flags_string = '',
			$minify;

		function __construct( $minify = false ) {
			$this->minify = $minify;
		}

		function generate(): string {
			if ( wd_asp()->instances->exists() ) {
				$basic_css = $this->generateBasic();
				$instance_css_arr = $this->generateInstances();

				return $this->saveFiles($basic_css, $instance_css_arr);
			}
			return '';
		}

		function verifyFiles(): bool {
			if (
				!file_exists(wd_asp()->cache_path . $this->filename('basic')) ||
				!file_exists(wd_asp()->cache_path . $this->filename('instances')) ||
				@filesize(wd_asp()->cache_path . $this->filename('instances')) < 1025
			) {
				return false;
			} else {
				return true;
			}
		}

		function filename( $handle ) {
			$media_flags = get_site_option('asp_css_flags', array(
				'basic' => ''
			));
			$flag = Str::anyToString($media_flags['basic']);
			$files = array(
				'basic' => 'style.basic'.$flag.'.css',
				'wpdreams-asp-basics' => 'style.basic'.$flag.'.css',
				'instances' => 'style.instances'.$flag.'.css',
				'wpdreams-ajaxsearchpro-instances' => 'style.instances'.$flag.'.css'
			);
			return $files[$handle] ?? 'search' . $handle . '.css';
		}

		private function generateBasic() {
			// Basic CSS
			ob_start();
			include(ASP_PATH . "/css/style.basic.css.php");
			$basic_css = ob_get_clean();
			$unused_assets = Requirements::getUnusedAssets(false);
			foreach ( $unused_assets['internal'] as $flag ) {
				// Remove unneccessary CSS
				$basic_css = asp_get_outer_substring($basic_css, '/*[' . $flag . ']*/');
				$this->basic_flags_string .= '-' . substr($flag, 0, 2);
			}
			foreach ( $unused_assets['external'] as $flag ) {
				// Remove unneccessary CSS
				$basic_css = asp_get_outer_substring($basic_css, '/*[' . $flag . ']*/');
				$this->basic_flags_string .= '-' . substr($flag, 0, 2);
			}

			return $basic_css;
		}

		private function generateInstances(): array {
			// Instances CSS
			$css_arr = array();
			foreach (wd_asp()->instances->get() as $s) {
				// $style and $id needed in the include
				$style = &$s['data'];
				$id = $s['id'];
				ob_start();
				include(ASP_PATH . "/css/style.css.php");
				$out = ob_get_contents();
				$css_arr[$id] = $out;
				ob_end_clean();
			}
			return $css_arr;
		}

		private function saveFiles($basic_css, $instance_css_arr): string {
			// Too big, disabled...
			$css = implode(" ", $instance_css_arr);

			// Individual CSS rules by search ID
			foreach ($instance_css_arr as $sid => &$c) {
				if ( $this->minify ) {
					$c = Css::Minify($c);
				}
				FileManager::_o()->write(wd_asp()->cache_path . "search" . $sid . ".css", $c);
			}

			// Save the style instances file nevertheless, even if async enabled
			if ( $this->minify ) {
				$css = Css::Minify($css);
				$basic_css = Css::Minify($basic_css);
			}

			FileManager::_o()->write(wd_asp()->cache_path . "style.instances.css", $basic_css . $css);
			FileManager::_o()->write(wd_asp()->cache_path . "style.basic.css", $basic_css);
			if ( $this->basic_flags_string != '' ) {
				FileManager::_o()->write(wd_asp()->cache_path . "style.basic" . $this->basic_flags_string . ".css", $basic_css);
				FileManager::_o()->write(wd_asp()->cache_path . "style.instances" . $this->basic_flags_string . ".css", $basic_css . $css);
			}

			update_site_option('asp_css_flags', array(
				'basic' => $this->basic_flags_string
			));


			update_site_option("asp_media_query", asp_gen_rnd_str());

			update_site_option('asp_css', array(
				'basic' => $basic_css,
				'instances' => $instance_css_arr
			));

			return $basic_css . $css;
		}
	}
}