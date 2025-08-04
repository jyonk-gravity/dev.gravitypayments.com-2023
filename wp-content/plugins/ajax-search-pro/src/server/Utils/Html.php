<?php
namespace WPDRMS\ASP\Utils;

if ( !defined('ABSPATH') ) {
	die("You can't access this file directly.");
}

class Html {
	public static function stripTags( string $s, string $allowable_tags = '', string $white_space = ' ' ) {
		// Remove inline styles and scripts
		$s = preg_replace(
			array(
				'#<script(.*?)>(.*?)</script>#is',
				'#<style(.*?)>(.*?)</style>#is',
			),
			'',
			$s
		);

		$s = str_replace('<', $white_space . '<', $s);
		// Non breakable spaces to regular spaces
		$s = preg_replace('/\xc2\xa0/', ' ', $s);
		// Duplicated spaces
		$s = preg_replace('/\s+/', ' ', $s);
		$s = strip_tags($s, $allowable_tags);
		$s = trim($s);

		return $s;
	}

	public static function toTxt( string $document ) {
		$search = array(
			'@<script[^>]*?>.*?</script>@si', // Strip out javascript
			'@<style[^>]*?>.*?</style>@si', // Strip style tags properly
			'@<![\s\S]*?--[ \t\n\r]*>@', // Strip multi-line comments including CDATA
			'@<[\/\!]*?[^<>]*?>@si', // Strip out HTML tags
		);
		return preg_replace( $search, ' ', $document );
	}

	public static function inject( $html, &$source, $injection_points = array(), $first = true ): bool {
		if ( $html !== '' && $source !== '' ) {
			$injection_points = empty($injection_points) ? array(
				'</head>',
				'<meta',
				'<link rel="stylesheet"', // @phpcs:ignore
				'<script',
				'<style',
				'<link',
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

	public static function extractIframeContent( string $str ): string {
		/** @noinspection All */
		preg_match_all('/\<iframe.+?src=[\'"]([^"\']+)["\']/', $str, $match);
		if ( isset($match[1]) ) {
			$ret = '';
			foreach ( $match[1] as $link ) {
				$s = wp_remote_get($link);
				if ( !is_wp_error($s) ) {
					$xs    = explode('<body', $s['body']);
					$final = $s['body'];
					if ( isset($xs[1]) ) {
						$final = '<html><body ' . $xs[1];
					}
					$ret .= ' ' . Str::stripTagsWithContent($final, array( 'head', 'script', 'style', 'img', 'input' ));
				}
			}
			return $ret;
		}
		return '';
	}

	/**
	 * Optimizes HTML by removing empty spaces
	 *
	 * @param string $output
	 * @return string
	 */
	public static function optimize( string $output ): string {
		$search  = array(
			'/>\s+</s',         // whitespaces between tags
			'/\r|\n|\r\n/s',    // Any remaining line breaks
			'/\s+/s',           // Any double spaces
		);
		$replace = array(
			'><',
			'',
			' ',
		);
		return preg_replace($search, $replace, $output) ?? '';
	}
}
