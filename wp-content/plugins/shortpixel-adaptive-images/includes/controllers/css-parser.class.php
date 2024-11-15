<?php
/**
 * User: simon
 * Date: 18.07.2019
 */

class ShortPixelCssParser {
    //                          v- changed to also parse --background-image CSS variable used by Blocksy builder
    const BG_ATTRS = 'mask|-webkit-mask|mask-image|-webkit-mask-image|background-image|background|content';
    const REGEX_CSS = '/([\s{;-]|^)()(' . self::BG_ATTRS . ')(\s*:(?:[^;}]*[,\s]|\s*))(url)\(\s*(?:\'|")?([^\'"\)]+)(\'|"|)?\s*\)/s';
    // tried (url|[\w-]+-gradient) instead of (url) but it breaks when the gradient is before a url() (HS#71821) so reverted it. To be seen if the gradient problem for which it was added in the first place reappears.
    const REGEX_URL = '/((url)|\s*(var))\s*\([\'"]?([^\'"\)]*?)([\'"]?)\)/s';

	//extract content of @font-face
	const REGEX_FONT_FACE = '/@font-face.*{([^}]+)[}]/sU';
	//extract src of @font-face (may be multiple)
	const REGEX_FONT_FACE_SRC = '/src\s*:([^;}]+)[;}]/s';
	//extract url part of src
	const REGEX_FONT_FACE_SRC_URL = '/url\((.*)\)/sU';


	const REGEX_IN_TAG = '/\<([\w]+)([^\<\>]*?)(' . self::BG_ATTRS . ')(\s*:(?:[^;}]*[,\s]|\s*))(url)\(\s*(?:\'|")?([^\'"\)]+)(\'|"|)?\s*\)/s';
    /**** BEWARE, THE ABOVE IS NOT USED ANY MORE, the bg-attr regex from RegexParser is used instead  ****/

    const NESTED_RULE = 1;
    const NESTED_RULE_END = 0;
	/**
	 * @var null|ShortPixelAI
	 */
    private $ctrl;
    private $logger;

    private $crowd2replaced;
    private $isPseudoSelector = false;

    public $cssFilePath = false;

    public function __construct($controller) {
        $this->ctrl = $controller;
        $this->logger = ShortPixelAILogger::instance();
    }


    public function replace_inline_style_backgrounds($style) {
        //$tokens = $this->get_all_tokens($style);

        $style = preg_replace_callback('/([^{};>]+\s*){([^{}]+\s*)}/s', function($matches) {
            preg_match('/:(before|after)\b/s', $matches[1], $pseudo);
            $this->isPseudoSelector = isset($pseudo[1]);
            $rules = preg_replace_callback(self::REGEX_CSS,
                array(&$this, 'replace_background_image_from_style'), $matches[2]);
            if (\ShortPixel\AI\ActiveIntegrations::_()->get('theme') == 'CROWD 2' && strpos($rules, '--img-') !== false) {
                $this->logger->log("CROWD2 - inline stile block has --img-");
                $rules = $this->replace_crowd2_img_styles($rules);
            }
            $this->isPseudoSelector = false;
            return $matches[1] . '{' . $rules . '}';
        }, $style);
        return $style;
    }

	public function replace_inline_style_fonts($style) {

        $this->logger->log("REPLACE INLINE FONTS:", substr($style, 0, 1000));

		if($this->ctrl->settings->areas->parse_css_files <= 0) {
			$this->logger->log("REPLACE INLINE FONT - PARSE CSS OPTION IS OFF");
			return $style;
		}
		$style = preg_replace_callback(self::REGEX_FONT_FACE, function($matches) {
			$srcs = preg_replace_callback(self::REGEX_FONT_FACE_SRC, function($matches1) {
				$urls = preg_replace_callback(self::REGEX_FONT_FACE_SRC_URL, function($matches2) {
					$url = trim($matches2[1],'"\'');

					$url = ShortPixelUrlTools::absoluteUrl($url);
					if(   $this->ctrl->urlIsApi($url)
					      || !ShortPixelUrlTools::isValid($url)
					      || $this->ctrl->urlIsExcluded($url)) {
						$this->logger->log("REPLACE INLINE FONT - IS INVALID OR EXCLUDED");
						return $matches2[0];
					}
					$apiUrl = $this->ctrl->get_api_url($url, false, false, $this->ctrl->get_extension($url), false);
					$retUrl = '"' . $apiUrl . '"';

					return str_replace($matches2[1],$retUrl, $matches2[0]);
				},$matches1[1]);
				return  str_replace($matches1[1], $urls, $matches1[0]);
			},$matches[1]);

			return  str_replace($matches[1], $srcs, $matches[0]);
		}, $style);

		return $style;
	}

    public function replace_in_tag_style_backgrounds($style) {
        if(strpos($style, 'background') === false) return $style;
        return preg_replace_callback(
            self::REGEX_IN_TAG,
            array(&$this, 'replace_background_image_from_tag'),
            $style);
    }

    public function add_class($ret, $spaiClass) {
        if(preg_match('/\sclass=("[^"]+"|\'[^\']+\'|[^\s\'""]+)/s', $ret->text, $classes)) {
            $this->logger->log("REPLACE BK CLASS: ", $classes);
        }
        if(count($classes)) {
            $full = $classes[0];
            $cls = $classes[1];
            $this->logger->log("REPLACE BK BITS: full: $full cls: $cls");
            if(strpos( $cls, 'spai-bg-on') === false) {
                $hasSep = ($cls[0] === '"' || $cls[0] === "'");
                $cls = ($hasSep ? substr($cls, 1, strlen($cls) - 2) : $cls) . $spaiClass;
                $this->logger->log("REPLACE BK BITS2: cls: $cls");
                $ret->text = str_replace($full, ' class="' . $cls . '"', $ret->text);
            }
        }
        else {
            $ret->text = '<' . $ret->tag . ' class="'. $spaiClass . '"' . substr($ret->text, strlen($ret->tag) + 1);
        }
        return $ret->text;
    }

    public function add_spai_attr($ret, $attr) {
        if(!preg_match('/\b' . $attr . '=/s', $ret->text)) {
            $ret->text = preg_replace('/<' . $ret->tag . '\b/s', '<' . $ret->tag . ' ' . $attr . '="1"', $ret->text);
        }
        return $ret->text;
    }

    public function replace_background_image_from_tag($matches, &$tagRule = false, $ignoreLazyNotice = false) {
        $this->logger->log("REPLACE TAG BK RECEIVES: ", $matches);
        $ret = $this->replace_background_image($matches, $this->ctrl->settings->areas->backgrounds_lazy && !$tagRule->eager, $ignoreLazyNotice);

        if(($this->ctrl->settings->areas->backgrounds_lazy || $this->ctrl->settings->behaviour->nojquery > 0) && $ret->replaced) {
            SHORTPIXEL_AI_DEBUG && $this->logger->log("CSS in-tag affected tag: " . $ret->tag);
            $this->ctrl->affectedTags->add($ret->tag, \ShortPixel\AI\AffectedTags::CSS_ATTR);
            if($tagRule) $tagRule->used[$ret->tag] = \ShortPixel\AI\AffectedTags::CSS_ATTR;

            if($this->ctrl->settings->areas->backgrounds_lazy && !$tagRule->eager && !$ret->eager) {
                $ret->text = $this->add_spai_attr($ret, 'data-spai-bg-on');
            }
            elseif($this->ctrl->settings->behaviour->nojquery > 0) {
                $ret->text = $this->add_spai_attr($ret, ' data-spai-bg-prepared');
            }
        }
        elseif (!$ret->replaced && $this->ctrl->settings->behaviour->nojquery > 0) {
            $this->logger->log("SOME DIFFERENT KIND OF BG - gradient-image?");
            $ret->text = $this->add_spai_attr($ret, ' data-spai-bg-prepared'); //we still need to flag it to be visible
        }
        $this->logger->log("REPLACE TAG BG RETURNS: ", $ret);
        return $ret->text;
    }

    public function replace_background_image_from_style($matches) {
        $this->logger->log("REPLACE STYLE BK RECEIVES: ", $matches);
        //if($this->cssFilePath) {
        //    $this->logger->log('URL is ' . $matches[4] . ' (homepath: ' . get_home_path() . ' , css File path: ' . $this->cssFilePath . ') and will be converted to ' . ShortPixelUrlTools::absoluteUrl($matches[4], $this->cssFilePath));
        //}
        //doesn't make sense to replace lazily in <style> blocks
        //actually it does because otherwise we don't have WebP
        $ret = $this->replace_background_image($matches, !$this->isPseudoSelector && $this->ctrl->settings->areas->backgrounds_lazy_style/*false*/);
        if($ret->replaced) {
            $this->ctrl->affectedTags->add('script', \ShortPixel\AI\AffectedTags::SCRIPT_ATTR);
        }
        return $ret->text;
    }

    public function replace_wp_bakery_data_ultimate_bg($matches, $tagRule = false) {
        $this->logger->log("REPLACE BAKERY BK RECEIVES: ", $matches);
        $ret = $this->replace_background_image_from_tag([$matches[0], $matches[1], $matches[2], '', $matches[3], 'url', $matches[4], $matches[5]], $tagRule, true);
        $this->logger->log("REPLACE BAKERY BK RETURNS: ", $ret);
        return $ret;
    }

    public function replace_background_image($matches, $lazy = true, $ignoreLazyNotice = false) {
        $text = $matches[0];
        //The URL matches are not always correct, for example when it's an *-image-set and there are
        // several url()'s only the first is matched, so we use a regex that matches only the url() part
        $replaced = false;
        if(!isset($matches[5])) {
            $this->logger->log("REPLACE BG - NO URL", $matches);
            return (object)array('text' => $text, 'replaced' => false);
        }
        $tag = trim($matches[1]);
        $attr = $matches[3]; //this mostly is background-image or background
        $extra = $matches[4]; //what lies between the type and url()
        $type = $matches[5];
        $eager = false;
        $changed = preg_replace_callback(self::REGEX_URL, function($matches) use ($text, $type, $lazy, $ignoreLazyNotice, &$replaced, &$eager) {
            $unchanged = $matches[0];
            $url = trim($matches[4]);
            $q = isset($matches[5]) ? $matches[5] : '';

            if($type !== 'url') {
                //it's gradient
                $this->logger->log("REPLACE BG - GRADIENT");
                return $unchanged;
            }
            $pristineUrl = $url;
            //WP is encoding some characters, like & ( to &#038; )
            $url = trim(html_entity_decode($url, ENT_QUOTES));
            //some URLs in css are delimited by &quot; which becomes " after html_entity_decode
            $urlUnquot = trim($url, '"');
            if($urlUnquot !== $url) {
                $this->logger->log('Removed double quote ' . $urlUnquot);
                $url = $urlUnquot;
                $pristineUrl = trim($pristineUrl, '"');
            }
            //Other URLs are delimited by &#039; which decodes to ' (HS#50033)
            $urlUnquot = trim($url, '\'');
            if($urlUnquot !== $url) {
                $this->logger->log('Removed quote ' . $urlUnquot);
                $url = $urlUnquot;
                $pristineUrl = trim($pristineUrl, '\'');
            }

            $tagEager = !$lazy || $this->ctrl->tagIs('eager', $text);
            //        if(strpos($url, 'data:image/svg+xml;u=') !== false) { // old implementation
            if(($decodedUrl = ShortPixelUrlTools::url_from_placeholder_svg($url)) !== false) {
                if(!$tagEager) {
                    $this->logger->log("REPLACE BG - IS PLACEHOLDER");
                    return $unchanged;
                } else {
                    //this is collected CSS, need to change it back and make it eager
                    $url = $decodedUrl;

                }
            }
            if(strpos($url, $this->ctrl->settings->behaviour->api_url) !== false) {
                $this->logger->log("REPLACE BG - IS PLACEHOLDER");
                return $unchanged;
            }
            if ( !$ignoreLazyNotice && !$this->ctrl->lazyNoticeThrown && ( strpos( $text, 'data-bg=' ) !== false ) ) {
                set_transient( "shortpixelai_thrown_notice", [ 'when' => 'lazy', 'extra' => false, 'causer' => 'css parser', 'text' => $text ], 86400 );
                $this->ctrl->lazyNoticeThrown = true;
            }
            if(!$ignoreLazyNotice && $this->ctrl->lazyNoticeThrown) {
                $this->logger->log("REPLACE BG - IS LAZYNOTICE");
                return $unchanged;
            }
            if($this->ctrl->tagIs('excluded', $text)) {
                $this->logger->log("REPLACE BG - IS EXCLUDED");
                return $unchanged;
            }

            $this->logger->log('******** REPLACE BACKGROUND IMAGE ' . ($lazy ? 'LAZILY ' : 'EAGERLY ') . $url);

            if(   $this->ctrl->urlIsApi($url)
                || !ShortPixelUrlTools::isValid($url)
                || $this->ctrl->urlIsExcluded($url)) {
                $this->logger->log("REPLACE BG - IS INVALID OR EXCLUDED", $url);
                return $unchanged;
            }

            $absoluteUrl = ShortPixelUrlTools::absoluteUrl($url, $this->cssFilePath ? $this->cssFilePath : false);
            if($tagEager) {
                $width = $this->ctrl->settings->areas->backgrounds_max_width ? $this->ctrl->settings->areas->backgrounds_max_width : false;
                //cssFilePath present means that's a CSS file from the cache plugin (WP Rocket)
                $inlinePlaceholder = $this->ctrl->get_api_url($absoluteUrl, $width, false, $this->ctrl->get_extension( $url ));
                $this->logger->log("API URL: " . $inlinePlaceholder);
            } else {
                $sizes = ShortPixelUrlTools::get_image_size($url);
                $inlinePlaceholder = isset($sizes[0]) ? ShortPixelUrlTools::generate_placeholder_svg($sizes[0], $sizes[1], $absoluteUrl) : ShortPixelUrlTools::generate_placeholder_svg(false, false, $absoluteUrl);
            }

//        $this->logger->log("REPLACE REGEX: " . '/' . $attr . '\s*:' . preg_quote($extra, '/') . 'url\(\s*' . preg_quote($q . $pristineUrl . $q, '/') . '/'
//              . " WITH: " . ' '. $attr . ':' . $extra . 'url(' . $q . $inlinePlaceholder . $q);
            //removed the ' ' . in front because it did not work with --background-images
            $replacement =  'url(' . $q . $inlinePlaceholder . $q;
            $str = preg_replace('/url\(\s*' . preg_quote($q . $pristineUrl . $q, '/') . '/',
                $replacement, $unchanged);

            $this->logger->log('******** WITH ', $replacement);
            $replaced = true;
            $eager = $tagEager;
            return $str;
        }, $text);

        return (object)array('text' => $changed, 'replaced' => $replaced, 'eager' => $eager, 'tag' => $tag);// . "<!-- original url: $url -->";
    }

    public function replace_crowd2_img_styles($style) {
        // CROWD2 uses --img-small --img-medium and --img-large styles
        return preg_replace_callback(
            '/(--img-small|--img-medium|--img-large)(\s*:(?:[^;]*?[,\s]|\s*))url\((?:\'|")?([^\'"\)]+)(\'|"|)?/s',
            array(&$this, 'replace_crowd2_img_style'),
            $style);
    }

    /**
     * extracts the tokens marking down which are css rules and which are nested blocks (will be ignored by the replacer)
     * @param $style - css style
     * @return array of tokens
     */
    public function get_all_tokens($style) {
        //TODO
    }

    protected function replace_crowd2_img_style($matches) {
        $text = $matches[0];
        $type = trim($matches[1]);
        $extra = trim($matches[2]);
        $url = trim($matches[3]);
        $q = isset($matches[4]) ? $matches[4] : '';
        if($this->ctrl->urlIsApi($url)) {
            return $text;
        }
        $this->crowd2replaced = true;
        $absoluteUrl = ShortPixelUrlTools::absoluteUrl($url, $this->cssFilePath ? $this->cssFilePath : false);
        $inlinePlaceholder = $this->ctrl->get_api_url($absoluteUrl, false, false, $this->ctrl->get_extension( $url ));
        return preg_replace('/' . $type . preg_quote($extra, '/') . 'url\(\s*' . preg_quote($q . $url . $q, '/') . '/',
            ' '. $type . $extra . 'url(' . $q . $inlinePlaceholder . $q, $text);

    }
}