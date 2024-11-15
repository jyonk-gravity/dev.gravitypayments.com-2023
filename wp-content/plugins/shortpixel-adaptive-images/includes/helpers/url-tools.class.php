<?php

use \ShortPixel\AI\LQIP;
use \ShortPixel\AI\Options;

class ShortPixelUrlTools {
    const PX_ENCODED = 'R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
    const PX_SVG_ENCODED = 'PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxIDEiIHdpZHRoPSIxIiBoZWlnaHQ9IjEiIGRhdGEtYmlwPSIiPjwvc3ZnPg==';
    const PX_SVG_TPL = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %WIDTH% %HEIGHT%" width="%WIDTH%" height="%HEIGHT%" data-bip=""></svg>';

    /* New constants */
    const PX_SVG_TEMPLATE = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %WIDTH% %HEIGHT%" width="%WIDTH%" height="%HEIGHT%" data-u="%URL%" data-w="%WIDTH%" data-h="%HEIGHT%" data-bip=""></svg>';

    public static $PROCESSABLE_EXTENSIONS = [
        'jpg', 'jpeg', 'jpe', 'jfif', 'gif', 'png', 'pdf', 'svg', 'webp', 'avif', 'bmp', 'tiff',
        'css', 'js',
        'eot', 'woff', 'woff2', 'ttf', 'otf' ];
    public static $ONLY_STORE = [ 'svg', 'js', 'eot', 'webp', 'avif', 'woff', 'woff2', 'ttf', 'otf' ];
    private static $SIZE_CACHE = [];

    public static function isValid($url) {
		$url = trim( html_entity_decode($url), " \t\n\r\0\x0B\xC2\xA0'\"" );
		if ( strlen( $url ) == 0 ) {
			return false;
		}

		//handle URLs that contain unencoded UTF8 characters like: https://onlinefox.xyz/wp-content/uploads/2018/10/Kragerup-gods-go-high-klatrepar-åbningstider.jpg"
		$parsed = parse_url( $url );
		$path   = ( isset( $parsed[ 'host' ] ) ? $parsed[ 'host' ] : '' )
		          . ( isset( $parsed[ 'path' ] ) ? implode( '/', array_map( 'urlencode', explode( '/', $parsed[ 'path' ] ) ) ) : '' );

		if ( isset( $parsed[ 'host' ] ) && $parsed[ 'host' ] != '' ) {
			// URL has http/https/...
			$scheme  = isset( $parsed[ 'scheme' ] ) ? $parsed[ 'scheme' ] : self::getCurrentScheme();
			$first   = $scheme . '://' . $parsed[ 'host' ] . $path;
			$url     = $first . substr( $url, strlen( $first ) ); //make sure we keep query or hashtags
			$isValid = !( filter_var( $url, FILTER_VALIDATE_URL ) === false );
		}
		else {
			// PHP filter_var does not support relative urls, so we simulate a full URL
			$url     = $path . substr( $url, strlen( $path ) ); //make sure we keep query or hashtags
			$isValid = !( filter_var( 'http://www.example.com/' . ltrim( $url, '/' ), FILTER_VALIDATE_URL ) === false );
		}
		if ( $isValid && isset( $parsed[ 'path' ] ) ) { //lastly check if is processable by ShortPixel
			$ext = strtolower( pathinfo( $parsed[ 'path' ], PATHINFO_EXTENSION ) );
			//treat the .css, .js and fonts case separately, only for local CSS files
			if ( in_array($ext, [ 'css', 'js', 'woff2', 'woff', 'ttf' ])) {
				if ( !isset( $parsed[ 'host' ] ) || $parsed[ 'host' ] == '' ) {
					return true;
				}
				$parsedHome = parse_url( home_url() );
				//TODO allowed domains unhack
				$cssDomains = ShortPixelAI::_()->settings->areas->css_domains;
				$cssDomains = strlen($cssDomains ?: '') ? explode(',', $cssDomains) : [];
				ShortPixelAILogger::instance()->log( "CSS DOMAIN: {$parsed['host']} EXTRA DOMAINS: ", $cssDomains );

				return isset( $parsedHome[ 'host' ] ) && ( $parsed[ 'host' ] == $parsedHome[ 'host' ] || in_array( $parsed[ 'host' ], $cssDomains ) );
			}
			else {
				return in_array( $ext, self::$PROCESSABLE_EXTENSIONS );
			}
		}

		return false;
	}

	static function absoluteUrl($url, $cssPath = false) {
        $url = trim($url);
        $URI = parse_url($url);
        $retURL = false;
        $cssUrl = parse_url($cssPath);
        $isCssUrl = isset($cssUrl['host']) && strlen($cssUrl['host']);
        $crtProto = is_ssl() ? 'https' : 'http';
        $cssUrlProto = isset($cssUrl['scheme']) && strlen($cssUrl['scheme']) ? $cssUrl['scheme'] : $crtProto;

        if(isset($URI['host']) && strlen($URI['host'])) {
            if(!isset($URI['scheme']) || !strlen($URI['scheme'])) {
                $url = $crtProto . '://' . ltrim($url, '/');
            }
            $retURL = $url;
        } elseif(substr($url, 0, 1) === '/') {
            if($isCssUrl) {
                $retURL = $cssUrlProto . '://' . $cssUrl['host'] . $url;
            }
            else {
                $home = parse_url(home_url());
                $retURL = $home['scheme'] . '://' . $home['host'] . $url;
            }
        } else {
            if($cssPath) {
                $homePath = self::get_home_path();
                if(strpos($cssPath, $homePath) !== false) {
                    $url = self::normalizePath($cssPath . $url);
                    $retURL = str_replace( $homePath, trailingslashit(get_home_url()), $url);
                } elseif($isCssUrl) {
                    $retURL = self::normalizePath(trailingslashit(dirname($cssPath)) . $url);
                }else {
                    $retURL = $url;
                }
            } else {
                global $wp;
                $retURL =  trailingslashit(home_url($wp->request)) . $url;
            }
        }
        return apply_filters('shortpixel/ai/originalUrl', $retURL); //# 37750
    }

    /**
     * this is a copy of the wp-admin/includes/file.php - we don't want to include it in the front-end
     * @return string
     */
    static function get_home_path() {
        $home    = set_url_scheme( get_home_url(), 'http' );
        $siteurl = set_url_scheme( get_site_url(), 'http' );
        if ( ! empty( $home ) && 0 !== strcasecmp( $home, $siteurl ) ) {
            $wp_path_rel_to_home = str_ireplace( $home, '', $siteurl ); /* $siteurl - $home */
            $pos = strripos( str_replace( '\\', '/', $_SERVER['SCRIPT_FILENAME'] ), trailingslashit( $wp_path_rel_to_home ) );
            $home_path = substr( $_SERVER['SCRIPT_FILENAME'], 0, $pos );
            $home_path = trailingslashit( $home_path );
        } else {
            $home_path = ABSPATH;
        }

        return str_replace( '\\', '/', $home_path );
    }


    /**
     * remove the a/b/../.. parts
     * @param $path
     * @return null|string|string[]
     */
    public static function normalizePath($path) {
        do {
            $path = preg_replace(
                array('#//|/\./#', '#/([^/.]+)/\.\./#'),
                '/', $path, -1, $count
            );
        } while($count > 0);
        return $path;
    }

    /**
     * remove fragment from the beginning and from the end, if found. If $frag is array, do this for each item
     * @param string $text
     * @param string|array $frag
     * @return string
     */
    public static function trimSubstring($text, $frag) {
        if(is_array($frag)) {
            foreach($frag as $f) {
                $text = self::trimSubstring($text, $f);
            }
            return $text;
        }
        $fragLen = strlen($frag);
        if(0 === strpos($text, $frag)) {
            $text = substr($text, $fragLen).'';
        }
        $textLen = strlen($text);
        if ($textLen - $fragLen === strrpos($text, $frag)) {
            $text = substr($text, 0, $textLen - $fragLen);
        }
        return $text;
    }

    public static function getCurrentScheme() {
        if (isset($_SERVER['HTTPS']) &&
            ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
            isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
            $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            return 'https';
        }
        else {
            return 'http';
        }
    }

    public static function get_image_size($url) {
        if(isset(self::$SIZE_CACHE[$url])) {
            return self::$SIZE_CACHE[$url];
        }
        if(defined('SPAI_FILENAME_RESOLUTION_UNSAFE')) { // cases like painting-cm-80x110.jpg - bragetto.it - 80x110 are the original painting size in cm :)
            $matches = array();
        } else {
            //HS#50381 - changed to avoid being fooled by filenames like Poster-42x59.4-сm-1.jpeg
            preg_match("/-([0-9]+)x([0-9]+)\.[a-zA-Z0-9]+(?:|\?.*)\s*$/", $url, $matches); //the filename suffix way
        }
        if(isset($matches[1]) && isset($matches[2]) && $matches[2] < 10000) { //encountered cases when the height was set to 99999 in the name, rely on actual image sizes in that case
            //ShortPixelAILogger::instance()->log("Sizes from filename: {$matches[1]} x {$matches[2]}");
            $sizes = array($matches[1], $matches[2], 'thumb' => true); //true means that it was from the name - it's a thumbnail
        } elseif(!($sizes = self::url_to_path_to_sizes($url)) && Options::_()->settings_behaviour_sizespostmeta) { //the file way
            //The postmeta way, as a last resort and only if the option is active
            $sizes = self::url_to_metadata_to_sizes($url);//the DB way
            ShortPixelAILogger::instance()->log("Sizes from DB: {$sizes[0]} x {$sizes[1]}");
        }
        if(!isset($sizes[0])) {
            $sizes = [1,1];
        }
        self::$SIZE_CACHE[$url] = $sizes;
        return $sizes;
    }

    public static function url_to_path_to_sizes($image_url) {
        //get rid of query if present
        $image_url = explode('?', $image_url)[0];
        $path = '';
        $updir = wp_upload_dir();
        $baseUrl = parse_url($updir['baseurl']);
        if(!isset($baseUrl['host'])) {
            $updir['baseurl'] = home_url() . $updir['baseurl'];
        }
        $urlParsed = parse_url($image_url);
        if(!isset($urlParsed['host'])) {
            $image_url = self::absoluteUrl($image_url);
        }
        $baseUrlPattern = "/" . str_replace("/", "\/", preg_replace("/^http[s]{0,1}:\/\//", "^http[s]{0,1}://", $updir['baseurl'])) . "/";

        SHORTPIXEL_AI_DEBUG && ShortPixelAILogger::instance()->log("MATCH? $image_url PATTERN: $baseUrlPattern UPDIR: " . json_encode($updir), FILE_APPEND);

        if(preg_match($baseUrlPattern, $image_url)) {
            $path = preg_replace($baseUrlPattern, $updir['basedir'], $image_url);
            SHORTPIXEL_AI_DEBUG && ShortPixelAILogger::instance()->log("TAKE1: $path");
        }
        if(!file_exists($path)) { //search in the wp-content directory too
            $baseUrlPattern = "/" . str_replace("/", "\/", preg_replace("/^http[s]{0,1}:\/\//", "^http[s]{0,1}://", dirname($updir['baseurl']))) . "/";
            SHORTPIXEL_AI_DEBUG && ShortPixelAILogger::instance()->log("BASE URL PATTERN2: $baseUrlPattern");
            if(preg_match($baseUrlPattern, $image_url)) {
                $path = preg_replace($baseUrlPattern, dirname($updir['basedir']), $image_url);
                ShortPixelAILogger::instance()->log("TAKE2: $path");
            }
            if(!file_exists($path)) {
                //that's the root folder, it will match the ABSPATH
                $absPath = untrailingslashit(function_exists('get_home_path') ? get_home_path() : ABSPATH);
                $baseUrlPattern = "/" . str_replace("/", "\/", preg_replace("/^http[s]{0,1}:\/\//", "^http[s]{0,1}://", dirname(dirname($updir['baseurl'])))) . "/";
                if(preg_match($baseUrlPattern, $image_url)) {
                    $path = preg_replace($baseUrlPattern, $absPath, $image_url);
                    SHORTPIXEL_AI_DEBUG && ShortPixelAILogger::instance()->log("TAKE3: $path");
                }
                //cases for multisite when the URL contains an extra element but the local path doesn't (eg food here: path: https://stagegie.wpengine.com/food/wp-content/uploads/sites/5/2020/01/Healthy-Chicken-Sheet-Pan-Fajitas-Get-Inspired-Everyday-5.jpg)
                if(!file_exists($path)) {
                    $baseDir = explode(DIRECTORY_SEPARATOR, $updir['basedir']);
                    $contentUploads = implode('/', array_slice($baseDir, -2, 2));
                    $contentSubdir = explode($contentUploads, $image_url);
                    if(isset($contentSubdir[1])) {
                        $path = $updir['basedir'] . $contentSubdir[1];
                        SHORTPIXEL_AI_DEBUG && ShortPixelAILogger::instance()->log("TAKE3BIS: $path");
                        if(!file_exists($path) && preg_match('/https?:\/\//', $image_url) === false) {
                            $path = $absPath . ($image_url[0] == '/' ? '' : '/') . $image_url;
                            SHORTPIXEL_AI_DEBUG && ShortPixelAILogger::instance()->log("TAKE4: $path");
                            if(!file_exists($path)) {
                                //for cases when the WP directory is a subdir of the root
                                $path = dirname($absPath) . ($image_url[0] == '/' ? '' : '/') . $image_url;
                                SHORTPIXEL_AI_DEBUG && ShortPixelAILogger::instance()->log("TAKE5: $path");
                            }
                        }
                    }
                }
            }
        }

        SHORTPIXEL_AI_DEBUG && ShortPixelAILogger::instance()->log("URL TO PATH TO SIZES, checking: " . $path . ' EXISTS? ' . (file_exists($path) ? 'YEE, sizes: ' . json_encode(getimagesize($path)) : 'Nope. UPLOAD url:' . $updir['baseurl'] . ' BaseUrlPattern:' . $baseUrlPattern));

        if(@file_exists($path)) {
            return self::getimagesizeOrSvg($path);
        } elseif (@file_exists(urldecode($path))) {
            return self::getimagesizeOrSvg(urldecode($path));
        } else {
            //LOG open_basedir warnings: https://secure.helpscout.net/conversation/1229180365/34854/
            SHORTPIXEL_AI_DEBUG && !self::check_open_basedir($path) && ShortPixelAILogger::instance()->log('URL TO PATH TO SIZES: OPEN_BASEDIR (' . ini_get('open_basedir') . ') restriction in effect!');

            //try the default location for cases like this one which had wrong baseurl so the replace above did not work: https://secure.helpscout.net/conversation/943639884/20602?folderId=1117588
            $path = trailingslashit(ABSPATH) . 'wp-content/uploads/' . wp_basename(dirname(dirname($image_url))) . '/' . wp_basename(dirname($image_url)) . '/' . wp_basename($image_url);
            if(@file_exists($path)) {
                return self::getimagesizeOrSvg($path);
            } elseif (@file_exists(urldecode($path))) {
                return self::getimagesizeOrSvg(urldecode($path));
            }
        }
        return false;
    }

    static function getimagesizeOrSvg($path) {
        if(strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'svg') {
            $svg = file_get_contents($path);
            preg_match('/viewBox=[\'"]\s*\d+\.?\d*\s+\d+\.?\d*\s+(\d+\.?\d*)\s+(\d+\.?\d*)\s*[\'"]/s', $svg, $matches);
            if(count($matches) == 3) {
                return [round(doubleval($matches[1])), round(doubleval($matches[2]))];
            } else {
                //try width and height
                preg_match('/<svg[^>]*\bwidth=[\'"]\s*(\d+\.?\d*)\s*(?:px|)\s*[\'"]/s', $svg, $matchesWidth);
                preg_match('/<svg[^>]*\bheight=[\'"]\s*(\d+\.?\d*)\s*(?:px|)\s*[\'"]/s', $svg, $matchesHeight);
                if(count($matchesWidth) == 2 && count($matchesHeight) == 2) {
                    return [round(doubleval($matchesWidth[1])), round(doubleval($matchesHeight[1]))];
                }
            }
            return [1,1];
        } else {
            $sz = getimagesize($path);
            if(!is_array($sz)) {
                //ShortPixelAILogger::instance()->logAnyway("getimagesizeOrSvg: getimagesize($path) returned " . var_export($sz, true));
            }
            return $sz;
        }
    }

    static function get_full_size_image_url($url) {


        if(!defined('SPAI_FILENAME_RESOLUTION_UNSAFE')) {
            //TODO UPDATE ::THUMBNAIL_REGEX and use here too
            $no_thumb_url = preg_replace('/-\d+x\d+(?:_c|_tl|_tr|_bl|_br|)(\.\w+)$/', '$1', $url);
        }
        if(Options::_()->settings_behaviour_sizespostmeta) {
            $parent_url = $url;
            if(attachment_url_to_postid($no_thumb_url)) {
                $parent_url = $no_thumb_url;
            }
            elseif (attachment_url_to_postid($absolute_url = ShortPixelUrlTools::absoluteUrl($no_thumb_url))) {
                $parent_url = $absolute_url;
            }
            elseif ($url != $no_thumb_url && attachment_url_to_postid($absolute_url = ShortPixelUrlTools::absoluteUrl($url))) {
                $parent_url = $absolute_url;
            }
        }
        else {
            $parent_url = ShortPixelUrlTools::absoluteUrl($no_thumb_url);
        }

        return $parent_url;

    }

    static function get_size_breakpoint($resolution, $base = 50, $multiplier = 1.1 ) {
        if($resolution <= $base) return $base;
        $exponent = floor(log(doubleval($resolution) / $base, $multiplier));
        if($resolution <= ceil($base * pow($multiplier, $exponent))) return ceil($resolution);
        return ceil($base * pow($multiplier, $exponent + 1));
    }

    static function check_open_basedir($path) {
        if(strlen(ini_get('open_basedir'))) {
            $basedirs = preg_split('/[:;]/', ini_get('open_basedir'));
            $allowed = false;
            foreach($basedirs as $basedir) {
                if (strpos($path, $basedir) === 0) {
                    $allowed = true;
                    break;
                }
            }
            if(!$allowed) {
                return false;
            }
        }
        return true;
    }

    static function get_from_meta_by_guid($image_url, $fuzzy = false) {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $url = ShortPixelUrlTools::absoluteUrl($image_url);
        $postId = attachment_url_to_postid($url);

        //ShortPixelAILogger::instance()->log("GET Post FROM META: $sqlPosts $condition");

        $meta = false;
        if(!empty($postId)) {
            $sqlMeta = "SELECT meta_value FROM {$prefix}postmeta WHERE meta_key = '_wp_attachment_metadata' AND post_id =";
            $meta = $wpdb->get_var($wpdb->prepare("$sqlMeta %d;", $postId ));

            //ShortPixelAILogger::instance()->log("GET FROM META: $sqlMeta $postId");
        }
        return $meta;
    }
    /**
     * @param $image_url
     * @return array
     */
    public static function url_to_metadata_to_sizes ( $image_url ) {
        //TODO be smart. If a certain url's domain is not found in the metadata, doesn't make sense to search for the other URLs on the same domain
        // Thx to https://github.com/kylereicks/picturefill.js.wp/blob/master/inc/class-model-picturefill-wp.php

        $original_image_url = $image_url;
        //TODO merge with the regex in get_full_size_image_url
        $image_url = preg_replace('/^(.+?)(-\d+x\d+)?\.(jpg|jpeg?|png|gif)((?:\?|#).+)?$/i', '$1.$3', $image_url);

        $meta = self::get_from_meta_by_guid($image_url);

        //previous joined query - slower in some cases?
        //$sql = "SELECT m.meta_value FROM {$prefix}posts p INNER JOIN {$prefix}postmeta m on p.id = m.post_id WHERE m.meta_key = '_wp_attachment_metadata' AND ";
        //$meta = $wpdb->get_var($wpdb->prepare("$sql p.guid='%s';", $image_url ));

        //try the other proto (https - http) if full urls are used
        if ( empty($meta) && strpos($image_url, 'http://') === 0 ) {
            $image_url_other_proto =  strpos($image_url, 'https') === 0 ?
                str_replace('https://', 'http://', $image_url) :
                str_replace('http://', 'https://', $image_url);
            $meta = self::get_from_meta_by_guid($image_url_other_proto);
        }

        //try using only path
        if (empty($meta) ) {
            $image_path = parse_url($image_url, PHP_URL_PATH); //some sites have different domains in posts guid (site changes, etc.)
            //keep only the last two elements of the path because some CDN's add path elements in front ( Google Cloud adds the project name, etc. )
            $image_path_elements = explode('/', $image_path);
            $image_path_elements = array_slice($image_path_elements, max(0, count($image_path_elements) - 3));
            $meta = self::get_from_meta_by_guid(implode ('/', $image_path_elements), true);
            //$meta = $wpdb->get_var($wpdb->prepare("$sql p.guid like'%%%s';", implode('/', $image_path_elements) ));
        }

        //try using the initial URL
        if ( empty($meta) ) {
            $meta = self::get_from_meta_by_guid($original_image_url);
            //$meta = $wpdb->get_var($wpdb->prepare("$sql p.guid='%s';", $original_image_url ));
        }

        if(!empty($meta)) { //get the sizes from meta
            $meta = unserialize($meta);
            if(strlen(@$meta['file']) && preg_match("/".preg_quote($meta['file'], '/') . "$/", $original_image_url)) {
                return array($meta['width'], $meta['height']);
            }
            if(is_array(@$meta['sizes'])) {
                foreach($meta['sizes'] as $size) {
                    if($size['file'] == wp_basename($original_image_url)) {
                        return array($size['width'], $size['height']);
                    }
                }
            }
        }
        return array(1, 1);
    }

    public static function is($url, $extension) {
        if(is_array($extension)) {
            foreach($extension as $ext) {
                if(self::is($url, $ext)) {
                    return true;
                }
            }
            return false;
        } else {
            return substr(trim($url), - strlen($extension) - 1 ) === '.' . $extension;
        }
    }

	/**
	 * Method retrieves filename from the provided image URL if URL is valid
	 *
	 * @param string $url
	 *
	 * @return bool|string
	 */
	public static function retrieve_name( $url ) {
		$url = trim( $url );

		$slash_pos = mb_strrpos( $url, '/' );

		return empty( $url ) || !self::isValid( $url ) ? false : ( $slash_pos === false ? $url : mb_substr( $url, $slash_pos + 1 ) );
	}

	/**
	 * Method generates the SVG placeholder using new template
	 *
	 * @param bool|int    $width
	 * @param bool|int    $height
	 * @param bool|string $url
	 *
	 * @return string
	 */
	public static function generate_placeholder_svg( $width = false, $height = false, $url = false ) {
		$defaults = [
			'width'  => 1,
			'height' => 1,
		];

		$data = 'data:image/svg+xml;base64,';
		$lqip = false;
        $logger = ShortPixelAILogger::instance();

		if ( Options::_()->settings_behaviour_lqip ) {
			$lqip = LQIP::_()->get( $url );

			if(!$lqip && !empty($url) /* && LQIP::_()->process_way === LQIP::USE_CRON */) {
			    $bip = ['source' => $url, 'url' => self::absoluteUrl($url), 'referer' => false];
			    SHORTPIXEL_AI_DEBUG && $logger->log('ADD BIP TO BE LQIP\'d: ', $bip);
                ShortPixelAI::_()->blankInlinePlaceholders[$url] = $bip;
            }

			$logger->log( 'LQIP SOURCE: ', var_export( $url, true ) );
			$logger->log( 'LQIP BODY: ', var_export( $lqip, true ) );
		}

		return empty( $width ) && empty( $height ) && empty( $url ) ?
			$data . self::PX_SVG_ENCODED :
			( $width > 1 || $height > 1 ?
				$data . base64_encode( str_replace( [ '%WIDTH%', '%HEIGHT%', '%URL%' ], [ empty( $width ) ? $defaults[ 'width' ] : $width, empty( $height ) ? $defaults[ 'height' ] : $height, empty( $url ) ? '' : urlencode( $url ) ], ( !$lqip ? self::PX_SVG_TEMPLATE : $lqip ) ) ) :
				$data . base64_encode( str_replace( [ '%WIDTH%', '%HEIGHT%', '%URL%' ], [ $defaults[ 'width' ], $defaults[ 'height' ], empty( $url ) ? '' : urlencode( $url ) ], ( !$lqip ? self::PX_SVG_TEMPLATE : $lqip ) ) ) );
	}

    /**
     * Method generates the SVG placeholder using new template
     * OLD IMPLEMENTATION
     * public static function generate_placeholder_svg($width = false, $height = false, $url = false) {
     * $ret = 'data:image/svg+xml;base64,' . self::PX_SVG_ENCODED;
     * if($width && $height && ($width > 1 || $height > 1)) {
     * $ret = self::_generate_placeholder_svg($width, $height, $url);
     * } elseif ($url) { //external images - we don't know the width...
     * $ret = 'data:image/svg+xml' . ';u=' . base64_encode(urlencode($url)) . ';base64,' . self::PX_SVG_ENCODED;
     * }
     * //self::log('GENERATE for ' . $url . ' : ' . $ret);
     * return $ret;
     * }
     *
     * protected static function _generate_placeholder_svg($width, $height, $url) {
     * return 'data:image/svg+xml;u=' . base64_encode(urlencode($url)) . ";w=$width;h=$height;base64,"
     * . base64_encode(str_replace('%WIDTH%', $width, str_replace('%HEIGHT%', $height, self::PX_SVG_TPL)));
     * }*/

    /* svg pair and placeholder_gif are obsolete TODO remove completely
    public static function generate_placeholder_svg_pair($width = false, $height = false, $url = false) {
        $ret = (object)array('image' => 'data:image/svg+xml;base64,' . self::PX_SVG_ENCODED, 'meta' => false);
        if($width && $height && ($width > 1 || $height > 1)) {
            $ret = self::_generate_placeholder_svg_pair($width, $height, $url);
        } elseif ($url) { //external images - we don't know the width...
            $ret = (object)array('image' => 'data:image/svg+xml;base64,' . self::PX_SVG_ENCODED, 'meta' => 'u=' . base64_encode(urlencode($url)));
        }
        //self::log('GENERATE for ' . $url . ' : ' . $ret);
        return $ret;
    }

    protected static function _generate_placeholder_svg_pair($width, $height, $url) {
        //ShortPixelAILogger::instance()->log("GENERATE SVG PAIR sizes: $width $height");
        return (object)array(
            'image' => 'data:image/svg+xml;base64,' . base64_encode(str_replace('%WIDTH%', $width, str_replace('%HEIGHT%', $height, self::PX_SVG_TPL))),
            'meta' => 'u=' . base64_encode(urlencode($url)) . ";w=$width;h=$height");
    }

    public static function generate_placeholder_gif($width = false, $height = false, $url = false) {
        if($width && $height && ($width > 1 || $height > 1)) {
            return self::_generate_placeholder_gif($width, $height, $url);
        } elseif ($url) { //external images - we don't know the width...
            return 'data:image/gif' . ';u=' . base64_encode(urlencode($url)) . ';base64,' . self::PX_ENCODED;
        }
        return 'data:image/gif;base64,' . self::PX_ENCODED;
    }

    protected static function _generate_placeholder_gif($width, $height, $data = '') {
        $pseudoData = strlen($data) ? ";u=" . base64_encode(urlencode($data)) . ";w=$width;h=$height" : '';
        $pxGif = base64_decode(self::PX_ENCODED);
        $pxGif[6] = chr(intval($width) % 256);
        $pxGif[7] = chr(intval($width) / 256);
        $pxGif[8] = chr(intval($height) % 256);
        $pxGif[9] = chr(intval($height) / 256);
        return 'data:image/gif' . $pseudoData . ';base64' . ',' . base64_encode($pxGif);
    }
    */

    /**
     * OLD IMPLEMENTATION
     *
     */
//    public static function url_from_placeholder_svg($url) {
//        $parts = explode(',', $url);
//        if(count($parts) == 2) {
//            $url = $parts[0];
//            $subparts = explode(';', $url);
//            array_shift($subparts);
//            if(strpos($subparts[0], 'u=') == 0) {
//                $url = urldecode(base64_decode(substr($subparts[0], 2)));
//            }
//        }
//        return $url;
//    }

    /**
     * Method parses base64 encoded string and returns the url if it was specified
     * !!!! also present server-side in commons/bl/url-tools.php
     *
     * @param string $encoded
     *
     * @return bool|string|string[]
     */
    public static function url_from_placeholder_svg( $encoded )
    {
        $reg_exes = array(
            'base64' => '/(?:[A-Za-z0-9+]{4})*(?:[A-Za-z0-9+]{2}==|[A-Za-z0-9+]{3}=)?$/',
            'url' => '/data-u="[^"]+/',
        );

        $matches = array();

        preg_match($reg_exes['base64'], $encoded, $matches);

        if (count($matches) !== 1) {
            return false;
        }

        $decoded = base64_decode($matches[0]);
        preg_match($reg_exes['url'], $decoded, $matches);

        if (count($matches) !== 1) {
            return false;
        }

        return urldecode(str_replace('data-u="', '', $matches[0]));
    }
}