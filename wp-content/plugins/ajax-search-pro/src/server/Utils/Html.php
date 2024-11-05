<?php
namespace WPDRMS\ASP\Utils;

defined('ABSPATH') or die("You can't access this file directly.");

if ( !class_exists(__NAMESPACE__ . '\Html') ) {
	class Html {
		public static function stripTags($string, $allowable_tags = '', $white_space = ' ') {
			// Remove inline styles and scripts
			$string = preg_replace( array(
				'#<script(.*?)>(.*?)</script>#is',
				'#<style(.*?)>(.*?)</style>#is'
			), '', $string );

			$string = str_replace('<', $white_space . '<', $string);
			// Non breakable spaces to regular spaces
			$string = preg_replace('/\xc2\xa0/', ' ', $string);
			// Duplicated spaces
			$string = preg_replace('/\s+/', " ", $string);
			$string = strip_tags($string, $allowable_tags);
			$string = trim($string);
	
			return $string;
		}

		public static function toTxt( string $document ) {
			$search = array(
				'@<script[^>]*?>.*?</script>@si', // Strip out javascript
				'@<style[^>]*?>.*?</style>@si', // Strip style tags properly
				'@<![\s\S]*?--[ \t\n\r]*>@', // Strip multi-line comments including CDATA
				'@<[\/\!]*?[^<>]*?>@si' // Strip out HTML tags
			);
			return preg_replace( $search, ' ', $document );
		}

		public static function inject( $html, &$source, $injection_points = array(), $first = true ): bool {
			if ( $html !== '' && $source != '' ) {
				$injection_points = empty($injection_points) ? array(
					"</head>", "<meta", "<link rel=\"stylesheet\"", "<script", "<style", "<link"
				) : $injection_points;
				foreach ( $injection_points as $injection_point ) {
					if ( $first ) {
						$pos = strpos($source, $injection_point);
					} else {
						$pos = strrpos($source, $injection_point);
					}
					if ( $pos !== false ) {
						$source = substr_replace($source, $html, $pos, 0);
						return true;
					}
				}
			}
			return false;
		}

		/** @noinspection HtmlRequiredLangAttribute */
		public static function extractIframeContent(string $str ): string {
			/** @noinspection All */
			preg_match_all('/\<iframe.+?src=[\'"]([^"\']+)["\']/', $str, $match);
			if ( isset($match[1]) ) {
				$ret = '';
				foreach($match[1] as $link) {
					$s = wp_remote_get($link);
					if ( !is_wp_error($s) ) {
						$xs = explode('<body', $s['body']);
						$final = $s['body'];
						if ( isset($xs[1]) ) {
							$final = '<html><body ' . $xs[1];
						}
						$ret .= ' ' . Str::stripTagsWithContent($final, array('head','script', 'style', 'img', 'input'));
					}
				}
				return $ret;
			}
			return '';
		}
	}
}