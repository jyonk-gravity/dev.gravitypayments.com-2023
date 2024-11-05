<?php
namespace WPDRMS\ASP\Utils;

defined('ABSPATH') or die("You can't access this file directly.");

class Css {
	/**
	 * Helper method to be used before printing the font styles. Converts font families to apostrophed versions.
	 *
	 * @param $font
	 * @return mixed
	 */
	public static function font($font) {
		preg_match("/family:(.*?);/", $font, $fonts);
		if ( isset($fonts[1]) ) {
			$f = explode(',', str_replace(array('"', "'"), '', $fonts[1]));
			foreach ($f as &$_f) {
				if ( trim($_f) != 'inherit' )
					$_f = '"' . trim($_f) . '"';
				else
					$_f = trim($_f);
			}
			$f = implode(',', $f);
			$ret = preg_replace("/family:(.*?);/", 'family:'.$f.';', $font);
		} else {
			$ret = $font;
		}

		return apply_filters('asp_fonts_css', $ret);
	}

	public static function minify($css) {
		// Normalize whitespace
		$css = preg_replace( '/\s+/', ' ', $css );
		// Remove spaces before and after comment
		$css = preg_replace( '/(\s+)(\/\*(.*?)\*\/)(\s+)/', '$2', $css );
		// Remove comment blocks, everything between /* and */, unless
		// preserved with /*! ... */ or /** ... */
		$css = preg_replace( '~/\*(?![\!|\*])(.*?)\*/~', '', $css );
		// Remove space after , : ; { } */ >
		$css = preg_replace( '/(,|:|;|\{|}|\*\/|>) /', '$1', $css );
		// Remove space before , ; { } ( ) >
		$css = preg_replace( '/ (,|;|\{|}|\(|\)|>)/', '$1', $css );
		// Add back the space for media queries operator
		$css = preg_replace( '/and\(/', 'and (', $css );
		// Strips leading 0 on decimal values (converts 0.5px into .5px)
		$css = preg_replace( '/(:| )0\.([0-9]+)(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}.${2}${3}', $css );
		// Strips units if value is 0 (converts 0px to 0)
		$css = preg_replace( '/(:| )(\.?)0(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}0', $css );
		// Converts all zeros value into short-hand
		$css = preg_replace( '/0 0 0 0;/', '0;', $css );
		$css = preg_replace( '/0 0 0 0\}/', '0}', $css );
		// Invisible inset box shadow
		$css = preg_replace( '/box-shadow:0 0 0(?: 0)? [a-fA-F0-9()#,rgb]+(?: inset)?([};])/i', 'box-shadow:none${1}', $css );
		// Transparent box shadow
		$css = preg_replace( '/box-shadow:[0-9px ]+ (transparent inset|transparent)([};])/i', 'box-shadow:none${2}', $css );
		// Invisible text shadow
		$css = preg_replace( '/text-shadow:0 0(?: 0)? [a-fA-F0-9()#,rgb]+([};])/i', 'text-shadow:none${1}', $css );
		// Transparent text shadow
		$css = preg_replace( '/text-shadow:[0-9px ]+ transparent([};])/i', 'text-shadow:none${1}', $css );
		// Shorten 6-character hex color codes to 3-character where possible
		$css = preg_replace( '/#([a-f0-9])\\1([a-f0-9])\\2([a-f0-9])\\3/i', '#\1\2\3', $css );
		// Remove ; before }
		$css = preg_replace( '/;(?=\s*})/', '', $css );
		return trim( $css );
	}
}