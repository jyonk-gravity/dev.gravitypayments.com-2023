<?php
	if ( !class_exists( 'MatthiasMullie\Minify\Minify' ) ) {
		$libs_dir = SHORTPIXEL_AI_PLUGIN_DIR . '/includes/libs';

        if( !class_exists( 'Psr\Cache\CacheException' ) && !interface_exists( 'Psr\Cache\CacheException' ) ) {
            // psr\cache
            require_once $libs_dir . '/psr/cache/src/CacheException.php';
            require_once $libs_dir . '/psr/cache/src/CacheItemInterface.php';
            require_once $libs_dir . '/psr/cache/src/CacheItemPoolInterface.php';
            require_once $libs_dir . '/psr/cache/src/InvalidArgumentException.php';
        }

		// path-converter
		require_once $libs_dir . '/path-converter/src/ConverterInterface.php';
		require_once $libs_dir . '/path-converter/src/NoConverter.php';
		require_once $libs_dir . '/path-converter/src/Converter.php';

		// minify
		require_once $libs_dir . '/minify/src/Minify.php';
		require_once $libs_dir . '/minify/src/JS.php';
		require_once $libs_dir . '/minify/src/CSS.php';
		require_once $libs_dir . '/minify/src/Exception.php';
		require_once $libs_dir . '/minify/src/Exceptions/BasicException.php';
		require_once $libs_dir . '/minify/src/Exceptions/FileImportException.php';
		require_once $libs_dir . '/minify/src/Exceptions/IOException.php';
	}

	use MatthiasMullie\Minify;

	class ShortPixelJsParser {
		protected $ctrl;
		protected $lazy;
		private   $logger;

		public function __construct(ShortPixelAI $ctrl, $lazy = false ) {
			$this->ctrl   = $ctrl;
			$this->lazy   = $lazy === false ? !!$ctrl->settings->areas->parse_js_lazy : $lazy;
			$this->logger = ShortPixelAILogger::instance();
		}

		public function parse( $script ) {

			if ( preg_match( '/(\<script[^>]*\>)(.*)(<\/script>)/sU', $script, $matches ) ) {
				if ( !empty( $matches[ 2 ] ) ) {
                    //TODO ditch minifier and just remove comments
                    $minifier = new Minify\JS( $matches[ 2 ] );
					$minified_js = $minifier->minify();

					$replacedUrls = $this->replaceUrls( $minified_js );

					$this->logger->log( 'JS Parser [ After minify and replace (script with tag) ]: ' . $replacedUrls );

					return $matches[1] . $replacedUrls . '</script>';
				}
			}
			// if just JS content has been provided
			else {
                $this->logger->log( 'JS Parser [ Before minify (script content) ] ' );
                //TODO ditch minifier and just remove comments
                $minifier = new Minify\JS( $script );
				$minified_js = $minifier->minify();

                $this->logger->log( 'JS Parser [ Before replace (script content) ]: ' );
				$replacedUrls = $this->replaceUrls( $minified_js );

				$this->logger->log( 'JS Parser [ After minify and replace (script content) ]: ' . $replacedUrls );

				return $replacedUrls;
			}

			return $script;
		}

		protected function replaceUrls( $text ) {
			$this->logger->log( "try replace URLs in $text \n\n" );

			//this expression is better because it works with characters such as three bytes accented letters (é vs. é - notice any difference? the first is on three bytes, second is UTF8...)
			// original pattern
			//			$pattern = '/(?:https?:\/\/)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,})))(?::\d{2,5})?(?:\/[^\s]*)?\.(?:jpe?g|png|gif)\b/su';

			//			$pattern2 = '/(?:https?:\\\\?\\/\\\\?\\/)(?:\\S+(?::\\S*)?@)?(?:(?!10(?:\\.\\d{1,3}){3})(?!127(?:\\.\\d{1,3}){3})(?!169\\.254(?:\\.\\d{1,3}){2})(?!192\\.168(?:\\.\\d{1,3}){2})(?!172\\.(?:1[6-9]|2\\d|3[0-1])(?:\\.\\d{1,3}){2})(?:[1-9]\\d?|1\\d\\d|2[01]\\d|22[0-3])(?:\\.(?:1?\\d{1,2}|2[0-4]\\d|25[0-5])){2}(?:\\.(?:[1-9]\\d?|1\\d\\d|2[0-4]\\d|25[0-4]))|(?:(?:[a-z\\x{00a1}-\\x{ffff}0-9]+-?)*[a-z\\x{00a1}-\\x{ffff}0-9]+)(?:\\.(?:[a-z\\x{00a1}-\\x{ffff}0-9]+-?)*[a-z\\x{00a1}-\\x{ffff}0-9]+)*(?:\\.(?:[a-z\\x{00a1}-\\x{ffff}]{2,})))(?::\\d{2,5})?(?:\\\\?\\/[^\\s]*)?\\.(?:jpe?g|png|gif)\\b/su';

			//$pattern = '/(?:https?:\\\\?\\/\\\\?\\/|\\\\?\\/\\\\?\\/)(?:\\S+(?::\\S*)?@)?(?:(?!10(?:\\.\\d{1,3}){3})(?!127(?:\\.\\d{1,3}){3})(?!169\\.254(?:\\.\\d{1,3}){2})(?!192\\.168(?:\\.\\d{1,3}){2})(?!172\\.(?:1[6-9]|2\\d|3[0-1])(?:\\.\\d{1,3}){2})(?:[1-9]\\d?|1\\d\\d|2[01]\\d|22[0-3])(?:\\.(?:1?\\d{1,2}|2[0-4]\\d|25[0-5])){2}(?:\\.(?:[1-9]\\d?|1\\d\\d|2[0-4]\\d|25[0-4]))|(?:(?:[a-z\\x{00a1}-\\x{ffff}0-9]+-?)*[a-z\\x{00a1}-\\x{ffff}0-9]+)(?:\\.(?:[a-z\\x{00a1}-\\x{ffff}0-9]+-?)*[a-z\\x{00a1}-\\x{ffff}0-9]+)*(?:\\.(?:[a-z\\x{00a1}-\\x{ffff}]{2,})))(?::\\d{2,5})?(?:\\\\?\\/[^\\s]*)?\\.(?:jpe?g|png|gif)\\b/su';
			//$pattern = "/(https?:|)\/\/(?:www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,4}\b(?:[-a-zA-Z0-9@:%_\+.~#?&\/\/=\(\)]*)\.(jpe?g|png|gif)\b/s";

			//			$simple_pattern = '/(?P<protocol>https?:\\?\/\\?\/|\\?\/\\?\/)[^\s\'\"\`]+\.(?P<extension>jpe?g|png|gif)/us';

			// pattern - /(?:https?:\\?\/\\?\/|\\?\/\\?\/)[^\s\'\"`]*\.(?:jpeg|png|gif)/us
			$ret = preg_replace_callback( '/(?:https?:\\\\?\/\\\\?\/|\\\\?\/\\\\?\/)[^\s\'\"`]*\.(?:jpe?g|png|gif)/us',
				function( $matches ) {
					// strip slashes because it corrupts a functionality
					if ( strpos( $matches[ 0 ], '\\' ) !== false ) {
						$matches[ 0 ] = stripslashes( $matches[ 0 ] );
					}

					$pattern = '/(?:https?:\/\/|\/\/)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,})))(?::\d{2,5})?(?:\/[^\s]*)?\.(?:jpe?g|png|gif)\b/su';

					return preg_replace_callback( $pattern, [ $this, 'replaceUrl' ], $matches[ 0 ] );
				},
				$text );

			if ( $this->lazy ) {
				//the text might be HTML, we need to mark the possible tags that have lazy replacements
				preg_match_all( '/\<([\w]+)[^\>]+data:image\/svg\+xml;/s', stripslashes( $ret ), $matches );
				if ( isset( $matches[ 1 ] ) ) {
					foreach ( $matches[ 1 ] as $tag ) {
						if ( strtolower( $tag ) !== 'img' ) {
                            $flags = \ShortPixel\AI\AffectedTags::SRC_ATTR | \ShortPixel\AI\AffectedTags::CSS_ATTR;
                            $this->ctrl->affectedTags->add($tag, $flags);
						}
					}
				}
			}
			return $ret;
		}

		protected function replaceUrl( $match ) {
			$this->logger->log( 'Matches' . json_encode( $match ) . "\n" );
			if ( strpos( $match[ 0 ], $this->ctrl->settings->behaviour->api_url ) === false ) {
				$url = ShortPixelUrlTools::absoluteUrl( $match[ 0 ] );
				if($this->ctrl->urlIsExcluded($url) || !ShortPixelUrlTools::isValid($url)) {
				    return $match[0];
                }
				if ( $this->lazy ) {
					$sizes = ShortPixelUrlTools::get_image_size( $url );
					$ret   = ShortPixelUrlTools::generate_placeholder_svg( isset( $sizes[ 0 ] ) ? $sizes[ 0 ] : false, isset( $sizes[ 1 ] ) ? $sizes[ 1 ] : false, $url );
				}
				else {
					$ret = $this->ctrl->get_api_url($url, false, false, $this->ctrl->get_extension($url) );
				}
				$this->logger->log( "Changing to $ret.\n\n" );

				return $ret;
			}
			else {
				return $match[ 0 ];
			}
		}
	}
