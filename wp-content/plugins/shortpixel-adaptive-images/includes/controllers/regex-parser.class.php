<?php
/**
 * User: simon
 * Date: 10.06.2019
 */

use ShortPixel\AI\ActiveIntegrations;
use ShortPixel\AI\AffectedTags;
use \ShortPixel\AI\TagRule;
use ShortPixel\AI\Options;

class ShortPixelRegexParser {
    protected $ctrl;
    private $logger;
    private $cssParser;

    private $scripts;
    private $styles;
    private $CDATAs;
    private $noscripts;
	private $attributes;

	private $tagRule;

    private $isEager = false;
    private $forceEager = false;
    private $isTemplate = false;

    private $regexes = [
        //this one is used for attributes having regular URL inside (img src, etc)
        'url'      => '/\<({{TAG}})(?:\s|\s[^\<\>]*?\s)({{ATTR}})\s*\=\s*(?:(\"|\')([^\>\'\"]+)(?:\'|\")|([^\>\'\"\s]+))(?:.*?)\>/s',
        'data-thumb' => '/\<({{TAG}})[^\<\>]*?\b{{ATTR}}\=(?:\"|\')(.+?)(?:\"|\')(?:.+?)\>/s',
        //this one is used for srcsets
        'srcset'   => '/\<({{TAG}})(?:\s|\s[^\<\>]*?\s)({{ATTR}})\s*\=\s*(?:(\")([^\"\>]+?)\"|(\')([^\'\>]+?)\')(?:.*?)\>/s',
        //this one is used for any tag that has a specific attribute with css background format
        'bg-attr'  => '/\<([\w]+)(?:[^\<\>]*?)\b({{ATTR}})(=(?:"|\'|)[^"\']*?)(url|[\w-]+-gradient)\((?:\'|")?([^\'"\)]*)(\'|")?\s*\)/s',
        //elements with style="background ...."
        'bg-style' => '/\<({{TAG}})([^\<\>]*?)\b({{ATTR}})(\s*:(?:[^;]*?[,\s]|\s*))(url|[\w-]+-gradient)\((?:\'|")?([^\'"\)]*)(\'|")?\s*\)(.*?)\>/s',
        //used for NextGen
        'ahref' => '/\<a[^\<\>]*?\shref\=(\"|\'?)(.+?)(?:\"|\')(?:.+?)\>/s',
    ];


    public function __construct(ShortPixelAI $ctrl)
    {
        $this->ctrl = $ctrl;
        $this->logger = ShortPixelAILogger::instance();
        $this->cssParser = new ShortPixelCssParser($ctrl);
    }

    public function parse($content) {
        $this->logger->log("******** REGEX PARSER BB ********* ");

        //add the preconnect header for faster loading of the images
        $apiUrl = parse_url($this->ctrl->settings->behaviour->api_url);
        $content = str_replace('</head>', '<link href="' . (isset($apiUrl['scheme'])?$apiUrl['scheme']: 'https') . '://' . $apiUrl['host'] . '" rel="preconnect" crossorigin></head>',
            $content);
        // EXTRACT all CDATA and inline script to be reinserted after the replaces
        // -----------------------------------------------------------------------

        $this->CDATAs = array();
        $content = preg_replace('/\/\/\s*<!\[CDATA\[/s', '/* <![CDATA[ */', $content);
        $content = preg_replace('/\/\/\]\]>/s', '/* ]]> */', $content);
        $content = $this->extract_blocks($content, '__sp_cdata', '<![CDATA[', ']]>', $this->CDATAs, true, '<![CDATA[');

        //<noscript> blocks will have URLs replaced but eagerly
        $this->noscripts = array();
        $content = $this->extract_blocks($content, '__sp_noscript', '<noscript.', '</noscript>', $this->noscripts);

        $this->scripts = array();
        $content = $this->extract_blocks($content, '__sp_script', '<script.', '</script>', $this->scripts);
        //$content = preg_replace_callback(
        ////     this part matches the scripts of the page, we don't replace inside JS
        //    '/\<script(.*)\<\/script\>/sU', // U flag - UNGREEDY
        //    array($this, 'replace_scripts'),
        //    $content
        //);
        $this->styles = array();
        $content = $this->extract_blocks($content, '__sp_style', '<style.', '</style>', $this->styles);
/*        $content = preg_replace_callback(
        //     this part matches the styles of the page, we replace inside CSS afterwards.
            '/\<style(.*)\<\/style\>/sU', // U flag - UNGREEDY
            array($this, 'replace_styles'),
            $content
        );
*/
	    /**
	     * Isolate the attributes which contain the ">" sign
	     * to avoid the parse conflicts and corrupt the DOM
	     * if "Generate Noscript" options is enabled
	     */
	    $this->attributes = [];
	    $content = $this->isolate_attributes( $content );
	    /*
	     * Old implementation using RegEx
	    $content = preg_replace_callback( '/=(["\'`])(?!\1)((?:(?![^\\\\]\1).)*?(?:>|>.*?(?:[^\\\\])))\1/s', [ $this, 'isolate_attributes' ], $content );
	    */

	    SHORTPIXEL_AI_DEBUG && $this->logger->log( "CHECK 1: " . strlen( $content ) );

        // Replace different cases of image URL usages
        // -------------------------------------------

        /* $content = preg_replace_callback(
        //     this part matches URLs without quotes
            '/\<img[^\<\>]*?\ssrc\=([^\s\'"][^\s>]*)(?:.+?)\>/s',
            array( $this, 'replace_images_no_quotes' ),
            $content
        ); */

        //In some cases the regex fails with this limit reached (default is 1000000) so make it larger (HS#75940)
        ini_set('pcre.backtrack_limit', 2000000);

        foreach (\ShortPixel\AI\TagRules::_()->items() as $tagRule) {
            SHORTPIXEL_AI_DEBUG && $this->logger->log("******** TAG RULE IS : ", $tagRule);
            if($tagRule->quickMatch && !preg_match($tagRule->quickMatch, $content)) continue;
            $regex = str_replace(array('{{TAG}}', '{{ATTR}}'), array($tagRule->tag, $tagRule->attr), $this->regexes[$tagRule->type]);
            //$this->logger->log("REGEX: $regex");
            $this->tagRule = $tagRule;
            $this->isEager = $this->forceEager || $tagRule->eager;

    	    SHORTPIXEL_AI_DEBUG && $this->logger->log("******** REGEX IS : " . $regex
                . ($this->tagRule->classFilter ? " CLASS FILTER: " . $this->tagRule->classFilter : '')
                . ($this->tagRule->attrFilter ? " ATTR FILTER: " . $this->tagRule->attrFilter . ($this->tagRule->attrValFilter ? '=' . $this->tagRule->attrValFilter : '') : '')
                . " Content len: " . strlen($content));

    	    $callback = $tagRule->callback ? $tagRule->callback : 'replace_images';
    	    $contentReplaced = preg_replace_callback($regex,
                array($this, $callback),
                $content
            );

            if(strlen($contentReplaced)) { // better safe than sorry
                $content = $contentReplaced;
            } else {
                //this can happen if there are HUGE tags attributes like for example a <form with data-product_variations having a large number of variations (HS# 59186)
                SHORTPIXEL_AI_DEBUG && $this->logger->log("******** REGEX PARSER FAILED using regex: $regex, PREG ERROR:"
                    . preg_last_error() . " bkt.limit:" . ini_get('pcre.backtrack_limit') . " rec.limit:" . ini_get('pcre.recursion_limit')
                    . " TAG RULE: ", $tagRule);
            }
        }

        //reset the current tag rule
        $this->tagRule = new TagRule(false, false);

        SHORTPIXEL_AI_DEBUG && $this->logger->log("******** REGEX PARSER put back the styles, (no)scripts and CDATAs. Content len: " . strlen($content));

        // put the isolated attributes back
        $content = $this->revert_attributes( $content );

        //put back the styles, scripts and CDATAs.
        for ($i = 0; $i < count($this->styles); $i++) {
            $style = $this->styles[$i];
            //$this->logger->log("STYLE $i: $style");
            //replace all the background-images
            $style = $this->cssParser->replace_inline_style_backgrounds($style);
			//replace inline fonts
	        $style = $this->cssParser->replace_inline_style_fonts($style);

            SHORTPIXEL_AI_DEBUG && $this->logger->log("STYLE $i REPLACED:  Content len: " . strlen($content));
            (SHORTPIXEL_AI_DEBUG & ShortPixelAILogger::DEBUG_AREA_CSS)
            && $this->logger->log("STYLE: " . $style);

            $content = str_replace("<style>__sp_style_plAc3h0ldR_$i</style>", $style, $content);
        }

        //handle the noscripts in a simpler (only <img>) and eager way.
        for ($i = 0; $i < count($this->noscripts); $i++) {
            $noscript = $this->noscripts[$i];
            $regex = str_replace(array('{{TAG}}', '{{ATTR}}'), array('img', 'src'), $this->regexes['url']);
            $this->logger->log("NOSCRIPT: $noscript");
            $this->tagRule = new TagRule(false, false);
            $noscript = preg_replace_callback($regex,
                array($this, 'replace_images'),
                $noscript
            );
            SHORTPIXEL_AI_DEBUG && $this->logger->log("NOSCRIPT SRC: $noscript Content len: " . strlen($content));
            $regex = str_replace(array('{{TAG}}', '{{ATTR}}'), array('img', 'srcset'), $this->regexes['srcset']);
            $noscript = preg_replace_callback($regex,
                array($this, 'replace_custom_srcset'),
                $noscript
            );
            SHORTPIXEL_AI_DEBUG && $this->logger->log("NOSCRIPT SRCSET: $noscript Content len: " . strlen($content));
            $content = str_replace("<noscript>__sp_noscript_plAc3h0ldR_$i</noscript>", $noscript, $content);
        }

        for ($i = 0; $i < count($this->scripts); $i++) {
            SHORTPIXEL_AI_DEBUG && $this->logger->log("SCRIPT $i Content len: " . strlen($content));
            $script = $this->scripts[$i];

            if ( $this->ctrl->settings->areas->js2cdn ) {
                //the URL if it's not an inline script
                $regex = str_replace(array('{{TAG}}', '{{ATTR}}'), array('script', 'src'), $this->regexes['url']);
                if(preg_match($regex, $script,$matches)) {
                    (SHORTPIXEL_AI_DEBUG & ShortPixelAILogger::DEBUG_AREA_JSON) && $this->logger->log("IS EXT JS:", $matches);
                    $url = isset( $matches[ 5 ] ) ? $matches[ 5 ] : $matches[ 4 ];
                    if(   strpos($url, parse_url($this->ctrl->settings->behaviour->api_url, PHP_URL_HOST)) === false
                        && strpos($url, ShortPixelAI::DEFAULT_API_AI) === false
                        && parse_url($url, PHP_URL_HOST) === ShortPixelDomainTools::get_site_domain()) {
                        //we set any CSS or JS to ret_wait because of the many CORS issues we've seen with these.
                        $absoluteUrl = ShortPixelUrlTools::absoluteUrl($url);
                        $cdnUrl = $this->ctrl->get_api_url( $absoluteUrl,false, false, $this->ctrl->get_extension( $url ), false,  true, $this->ctrl->cssCacheVer);
                        SHORTPIXEL_AI_DEBUG && $this->logger->log("API URL: " . $this->ctrl->settings->behaviour->api_url . "JS URL: " . $url . " Replacing with: " . $cdnUrl);
                        $script = str_replace($url, $cdnUrl, $script);
                    }
                }
            }
            //now the content, if it's an inline script
            if($this->ctrl->settings->areas->parse_json && preg_match('/^(\<script[^>]*(?:\btype=(?:"|\')application\/(?:ld\+|)json(?:"|\'))[^>]*\>)/sU', $script)) {
                (SHORTPIXEL_AI_DEBUG & ShortPixelAILogger::DEBUG_AREA_JSON) && $this->logger->log("IS APP LD JSON.");
                $script = preg_replace_callback('/^(\<script[^>]*(?:\btype=(?:"|\')application\/(?:ld\+|)json(?:"|\'))[^>]*\>)(.*)\<\/script\>/sU',
                    array($this, 'replace_application_json_script'),
                    $script
                );
            }
            else if ( $this->ctrl->settings->areas->parse_js ) {
				if ( strpos( $script, '__sp_cdata_plAc3h0ldR_' ) ) {
                    (SHORTPIXEL_AI_DEBUG & ShortPixelAILogger::DEBUG_AREA_JSON) && $this->logger->log("IS JS CDATA PLACEHOLDER.", $script);
					$script = preg_replace_callback( '/__sp_cdata_plAc3h0ldR_([0-9]+)/s',
						array( $this, 'replace_cdata_js' ),
						$script
					);
				}
				else {
                    $script = trim($script);
				    $openingTagEnd = strpos($script, '>') + 1;
				    $openingTag = substr($script, 0, $openingTagEnd);
				    if(preg_match('/\btype=["\'](application|text)\/javascript/', $openingTag) || !preg_match('/\btype=/', $openingTag))
				    { // either it's type text/javascript or type not defined. Outstanding example that should not be parsed as JS:  type=text/template (HS#51163)
                        (SHORTPIXEL_AI_DEBUG & ShortPixelAILogger::DEBUG_AREA_JSON) && $this->logger->log("IS app/text JS: ", $script);
                        $jsParser = new ShortPixelJsParser( $this->ctrl );

                        $parsed_script = $jsParser->parse( $script );

                        $script = empty( $parsed_script ) ? $script : $parsed_script;
                    }
                    else if(preg_match('/\btype=["\']text\/template/', $openingTag)) {
                        (SHORTPIXEL_AI_DEBUG & ShortPixelAILogger::DEBUG_AREA_JSON) && $this->logger->log("IS TEXT/TEMPLATE: ", $script);
                        $endTagStart = strrpos($script, "</script");
                        if($endTagStart > $openingTagEnd) {
                            $tpl = trim(substr($script, $openingTagEnd, $endTagStart - $openingTagEnd));
                            (SHORTPIXEL_AI_DEBUG & ShortPixelAILogger::DEBUG_AREA_JSON) && $this->logger->log("ZE TEMPLATE: " . $tpl);
                            $parser = new ShortPixelRegexParser($this->ctrl);
                            $tplDec = json_decode($tpl);
                            if($tplDec === NULL) {
                                //IF the template is not a json, in most cases it's html_entity_encoded, so we decode it.
                                $isEncoded = preg_match('/^\s*&lt;/', $tpl);
                                $tpl = $isEncoded ? html_entity_decode($tpl) : $tpl;
                                (SHORTPIXEL_AI_DEBUG & ShortPixelAILogger::DEBUG_AREA_JSON) && $this->logger->log("DECODED TEMPLATE: " . $tpl);
                                $parsed_script = $parser->parse($tpl);
                                (SHORTPIXEL_AI_DEBUG & ShortPixelAILogger::DEBUG_AREA_JSON) && $this->logger->log("PARSED TEMPLATE: " . $parsed_script);
                                $parsed_script = $isEncoded ? htmlentities($parsed_script) : $parsed_script;
                            } elseif(is_array($tplDec) || is_object($tplDec)) {
                                $parserJson = new ShortPixelJsonParser($this->ctrl);
                                $parsed_script = json_encode($parserJson->parse($tplDec));
                            } else {
                                $parsed_script = json_encode($parser->parse($tplDec));
                            }
                            (SHORTPIXEL_AI_DEBUG & ShortPixelAILogger::DEBUG_AREA_JSON) && $this->logger->log("TEXT TEMPLATE BY REGEX: " . $parsed_script);
                            $script = empty( $parsed_script ) ? $script : $openingTag . $parsed_script . '</script>';
                        }
                    }
                    else if(preg_match('/\btype=["\']text\/x-jsrender/', $openingTag)) {
                        (SHORTPIXEL_AI_DEBUG & ShortPixelAILogger::DEBUG_AREA_JSON) && $this->logger->log("TEXT X-JSRENDER TEMPLATE: " . $openingTag);
                        $endTagStart = strrpos($script, "</script");
                        if($endTagStart > $openingTagEnd) {
                            $tpl = trim(substr($script, $openingTagEnd, $endTagStart - $openingTagEnd));
                            $parser = new ShortPixelRegexParser($this->ctrl);
                            $parser->isTemplate = true;
                            $parser->forceEager = true;
                            $parsed_script = $parser->parse($tpl);
                            (SHORTPIXEL_AI_DEBUG & ShortPixelAILogger::DEBUG_AREA_JSON) && $this->logger->log("TEXT X-JSRENDER TEMPLATE BY REGEX: " . $parsed_script);
                            $script = empty( $parsed_script ) ? $script : $openingTag . $parsed_script . '</script>';
                        }
                    }
				}
			}

            $content = str_replace("<script>__sp_script_plAc3h0ldR_$i</script>", $script, $content);
        }

	    for ( $i = 0; $i < count( $this->CDATAs ); $i++ ) {
		    $content = str_replace( "<![CDATA[\n__sp_cdata_plAc3h0ldR_$i\n//]]>", $this->CDATAs[ $i ], $content );
            $content = str_replace( "<![CDATA[\n__sp_cdata_plAc3h0ldR_$i\n]]>", $this->CDATAs[ $i ], $content );
	    }

	    // $content = str_replace('{{SPAI-AFFECTED-TAGS}}', implode(',', array_keys($this->ctrl->affectedTags)), $content);

	    if($this->ctrl->settings->behaviour->nojquery <= 0) $this->ctrl->affectedTags->remove( 'img' );

        $content = \ShortPixel\AI\JsLoader::_($this->ctrl)->addTagData($content);

        SHORTPIXEL_AI_DEBUG && $this->logger->log("******** REGEX PARSER RETURN ********* " . strlen($content) . (SHORTPIXEL_AI_DEBUG & ShortPixelAILogger::DEBUG_INCLUDE_CONTENT ? $content : ''));

        return $content;
    }

    protected function replace_background_image_from_tag($matches) {
        return $this->cssParser->replace_background_image_from_tag($matches, $this->tagRule);
    }

    protected function replace_bg_attr($matches) {
        return $this->cssParser->replace_wp_bakery_data_ultimate_bg($matches, $this->tagRule);
    }

	protected function replace_cdata_js( $matches ) {
		$index    = (int) $matches[ 1 ];
		$script   = $this->CDATAs[ $index ];

		if ( strpos( $script, 'var spai_settings' ) === false ) { //don't parse our own JS block...
            $jsParser = new ShortPixelJsParser( $this->ctrl );
			$parsed_script = $jsParser->parse( $script );

			$this->CDATAs[ $index ] = empty( $parsed_script ) ? $script : $parsed_script;
		}

		return $matches[ 0 ];
	}

//    protected function replace_cdata_js($matches) {
//        $jsParser = new ShortPixelJsParser($this->ctrl);
//        $key = '__sp_cdata_plAc3h0ldR_' . $matches[1];
//        $script = $this->CDATAs[$key];
//        if(strpos($script, 'var spai_settings') === false) { //don't parse our own JS block...
//            $this->CDATAs[$key] = $jsParser->parseJsonBlocks($script, 'cdata');
//        }
//        return $matches[0];
//    }

    public function replace_crowd2_img_styles($matches) {
        $text = $matches[0];
        if(isset($matches[5]) && $matches[5] == '\'') {
            $matches[3] = $matches[5];
            $matches[4] = $matches[6];
        }
        $style = $matches[4];
        if(strpos($style, '--img-') === false) return $text;
        $qm = strlen($matches[3]) ? $matches[3] : '"';

        $pattern = $matches[2] . '=' . $matches[3] . $matches[4] . $matches[3];
        $pos = strpos($text, $pattern);
        if($pos === false) return $text;

        $replacedStyle = $this->cssParser->replace_crowd2_img_styles($matches[4]);
        $replacement = ' ' . $matches[2] . '=' . $qm . $replacedStyle . $qm;

        $this->logger->log("CROWD2 - style found, string $pattern is replaced by $replacement");

        $str = substr($text, 0, $pos) . $replacement . substr($text, $pos + strlen($pattern));
        return $str;
    }

    /**
     * extract the specific block into the store array. Preferred over the out-of-the box preg_replace_callback because as the content of the blocks must be un-greedy, in many cases when the blocks are large
     * a "catastrophic backtrace" exception is thrown by the PHP function.
     * @param $content
     * @param $id - code for the block - will be put in the replacement
     * @param $startMarker eg. <![CDATA[ or <noscript>. If the last character is '.' then the string without it will be searched but replaced with the string ending in '>' instead of '.' ( search for <script and replace with <script>)
     * @param $endMarker eg ]]> or </noscript>
     * @param $store - by ref. the array in which the extracted blocks will be put
     * @param bool $newLine if true it adds a new line after/before the start/end markers, needed for CDATA
     * @param bool $endSafeguard - a safeguard if the $endMarker is missing, as for example another [CDATA[ for a [CDATA[ block.
     * @return string - the changed $content
     */
	public function extract_blocks( $content, $id, $startMarker, $endMarker, &$store, $newLine = false, $endSafeguard = false ) {
        $matches = array();
        $startMarkerRepl = $startMarker;
        if(substr($startMarker, -1) == '.') {
            $startMarker = substr($startMarker, 0, -1);
            $startMarkerRepl = $startMarker . '>';
        }
        $startMarkerLen = strlen($startMarker);
        $endMarkerLen = strlen($endMarker);
        $endSafeguardLen = $endSafeguard ? strlen($endSafeguard) : 0;
        for($idx = 0, $match = false, $len = strlen($content); $idx < $len - $endMarkerLen + 1; $idx++) {
            if($match) {
                if(substr( $content, $idx, $endMarkerLen) == $endMarker) {
                    //end of CDATA block
                    $matches[] = (object)array('start' => $match ? $match : 0, 'end' => $idx + $endMarkerLen - 1);
                    $idx += $endMarkerLen - 1;
                    $match = false;
                }
                elseif($endSafeguard && substr( $content, $idx, $endSafeguardLen) == $endSafeguard) {
                    $this->logger->log(" OOPS, $endSafeguard ENCOUNTERED, dropping current block.");
                    $match = false;
                    $idx--;
                }
            } else {
                if(substr($content, $idx, $startMarkerLen) == $startMarker) {
                    $match = $idx;
                    $idx += $startMarkerLen - 1;
                }
            }
        }
        SHORTPIXEL_AI_DEBUG && $this->logger->log(" MATCHED $startMarker BLOCKS: " . json_encode($matches));
        $replacedContent = '';
        $nl = ($newLine ? "\n" : '');
        for($idx = 0; $idx < count($matches); $idx++) {
            $start = isset($matches[$idx - 1]) ? $matches[$idx - 1]->end + 1 : 0;
            $placeholder = $startMarkerRepl . $nl . $id . '_plAc3h0ldR_' .$idx . $nl . $endMarker;
            $replacedContent .= substr($content, $start, $matches[$idx]->start - $start) . $placeholder;
            $cdata = substr($content, $matches[$idx]->start, $matches[$idx]->end - $matches[$idx]->start + 1);
            SHORTPIXEL_AI_DEBUG && $this->logger->log(" MATCHED AND EXTRACTED $idx: " . $cdata . " WITH: " . $placeholder);
            $store[] = $cdata;
        }
        $replacedContent .= substr($content, isset($matches[$idx - 1]) ? $matches[$idx - 1]->end + 1 : 0);
        return $replacedContent;
    }

	/*
	 * Old implementation
	 *
	private function isolate_attributes( $matches ) {
		$index = count( $this->attributes );

		$this->logger->log( 'ISOLATED ATTRIBUTE BODY:', $matches[ 2 ] ); // second group is a body of the affected attribute

		$this->attributes[] = $matches[ 2 ]; // second group is a body of the affected attribute

		return str_replace( $matches[ 2 ], '__sp_attribute_plAc3h0ldR_' . $index, $matches[ 0 ] );
	}
	*/

	/**
	 * Method parses & isolates attributes which could affect the DOM for future parsing, notably attributes containing <tags>
	 *
	 * @param string $content HTML content
	 *
	 * @return string content with placeholders instead of the extracted attributes
	 */
	private function isolate_attributes( $content ) {
        //first mark down each attribute that contains >
        for ($attributes = [], $attributesContent = [],
             $inTag = false, $attrStartMarker = 0, $inAttr = false, $toBeExtracted = false, $attrDelim = false,
             $len = strlen($content),
             $index = 0; $index < $len; $index++) {
            if(!$inTag ) {
                if( $content[$index] == '<') {
                    //start of a tag
                    $inTag = true;
                } // else we just increment no matter what
            }
            else {
                if(!$inAttr ) {
                    if($content[$index] == '>') {
                        //end of a tag
                        $inTag = false;
                    }
                    elseif(($content[$index] == '"' | $content[$index] == '\'') && self::previousNonWhite($content, $index) == '=' ) {
                        $attrStartMarker = $index;
                        $attrDelim = $content[$index];
                        $inAttr = true;
                    }
                }
                else {
                    if($content[$index] == '>') {
                        //that's our attribute, this one will need to be replaced
                        $toBeExtracted = true;
                    } elseif($content[$index] == $attrDelim && $content[$index - 1] != '\\') {
                        //the attribute just ended
                        $inAttr = false;
                        if($toBeExtracted) {
                            $toBeExtracted = false;
                            $attributes[] = (object)['start' => $attrStartMarker + 1, 'end' => $index];
                            $attributesContent[] =  substr($content, $attrStartMarker + 1, $index - $attrStartMarker - 1);
                        }
                    }

                }
            }
        }
        //now replace them
        if(count($attributes)) {
            for ($parsedContent = '', $index = 0, $prev = 0; $index < count($attributes); $index++) {
                $parsedContent .= substr($content, $prev, $attributes[$index]->start - $prev) . '__sp_attribute_plAc3h0ldR_' . $index . '_';
                $prev = $attributes[$index]->end;
            }
            $parsedContent .= substr($content, $prev);
            $this->attributes = $attributesContent;
            return $parsedContent;
        }

		return $content;
	}

    /**
     * returns the previous non-white char before the given index in the content string.
     * @param $content
     * @param $index
     * @return false|string the previous non-white char
     */
    public static function previousNonWhite($content, $index) {
        for($i = $index - 1; $i >= 0; $i--) {
            if(!ctype_space($content[$i])) return $content[$i];
        }
        return false;
    }

	/**
	 * Method reverts isolated attributes which could affect the DOM
	 *
	 * @param string $content HTML content which has been parsed
	 *
	 * @return string
	 */
	private function revert_attributes( $content ) {
		if ( !empty( $this->attributes ) ) {
			foreach ( $this->attributes as $index => $attribute ) {
				SHORTPIXEL_AI_DEBUG && $this->logger->log( 'REVERTED ATTRIBUTE VALUE: ' . $attribute );

				$content = str_replace( '__sp_attribute_plAc3h0ldR_' . $index . '_', $attribute, $content );
			}
		}

		return $content;
	}

    public function replace_scripts($matches)
    {
        $index = count($this->scripts);
        $this->scripts[] = $matches[0];
        return "<script>__sp_script_plAc3h0ldR_$index</script>";
    }

    public function replace_styles($matches)
    {
        //$this->logger->log("STYLE: " . $matches[0]);
        $index = count($this->styles);
        $this->styles[] = $matches[0];
        return "<style>__sp_style_plAc3h0ldR_$index</style>";
    }

    public function replace_images($matches)
    {
        $text = $matches[0];
        $tag = $matches[ 1 ];
        $attr = $matches[ 2 ];
        $url = isset( $matches[ 5 ] ) ? $matches[ 5 ] : $matches[ 4 ];
        $q = $matches[ 3 ]; //$matches[3] will be either " or '

        if (count($matches) < 5 ||  preg_match('/\s' . $attr . '=["\']{0,1}data\:image\/svg\+xml;/s', $text)) {
            //avoid duplicated replaces due to filters interference
            return $text;
        }
        if ($this->tagRule->classFilter && !preg_match('/\bclass=(\"|\').*?\b' . $this->tagRule->classFilter . '\b.*?(\"|\')/s', $text)) {
            return $text;
        }

	    if ( $this->tagRule->attrFilter ) {
		    if ( $this->tagRule->attrValFilter ) {
			    if ( !preg_match( '/\b' . $this->tagRule->attrFilter . '=((\"|\'|\`)[^\"|\'|\`]*\b|)' . preg_quote( $this->tagRule->attrValFilter, '/' ) . '/s', $text ) ) {
				    return $text;
			    }
		    }
		    else {
			    $stripped = preg_replace( '/(\"|\'|\`).*?(\"|\'|\`)/s', ' ', $text ); //keep only the attribute's names
			    if ( !preg_match( '/\b' . $this->tagRule->attrFilter . '(?=\s|=|\/?>)/s', $stripped ) ) {
				    return $text;
			    }
		    }
	    }

	    $noresize = true; //it's a return param actually, if not modified, don't add the noscript tag
	    $processed_tag = $this->_replace_images( $tag, $attr, $text, $url, $q, $noresize );

        $postProcessor = $this->tagRule->getPostProcessor();
        if($postProcessor && is_array($postProcessor) && count($postProcessor) == 2 && method_exists($postProcessor[0], $postProcessor[1])) {
            $processed_tag = call_user_func($postProcessor, $processed_tag);
        }

	    // ADD noscript
	    if (   $tag == 'img' //noscript makes sense only for img tags
	        && $this->ctrl->options->settings_behaviour_replaceMethod === 'src'
            && !!$this->ctrl->options->settings_behaviour_generateNoscript
            && !$noresize) { // don't add noscript if the image is eager. (HS#41939)
	        $eager = $this->isEager;
            $this->isEager = true; //temporarily force is Eager for noscript
            SHORTPIXEL_AI_DEBUG && $this->logger->log("NOSCRIPT TAG FOR " . $url);
            //replace the URLs with eager URLs inside noscript.
		    $processed_tag .= '<noscript data-spai="1">' . $this->_replace_images($tag, $attr, $text, $url, $q) . '</noscript>';
		    $this->isEager = $eager;
	    }

	    return $processed_tag;
    }

    protected function _replace_images($tag, $attr, $text, $url, $q = '', &$eager = false) {
        SHORTPIXEL_AI_DEBUG && $this->logger->log("******** REPLACE IMAGE IN $tag ATTRIBUTE $attr: '" . $url . "'"
            . ($this->tagRule->mergeAttr ? ' AND MERGE ' . $this->tagRule->mergeAttr : ''));

	    if (
		    !$this->ctrl->lazyNoticeThrown && substr( $url, 0, 10 ) == 'data:image'
		    && ( strpos( $text, 'data-lazy-src=' ) !== false
                 || strpos( $text, 'data-lazy=' ) !== false
		         || strpos( $text, 'data-layzr=' ) !== false
		         || strpos( $text, 'data-src=' ) !== false
		         || ( strpos( $text, 'data-orig-src=' ) !== false && strpos( $text, 'lazyload' ) ) //found for Avada theme with Fusion Builder
		    )
	    ) {
		    set_transient( "shortpixelai_thrown_notice", [ 'when' => 'lazy', 'extra' => false, 'causer' => 'regex parser', 'text' => $text ], 86400 );
		    $this->ctrl->lazyNoticeThrown = true;
	    }
	    //if($this->ctrl->lazyNoticeThrown) {
        //    $this->logger->log("Lazy notice thrown");
        //    return $text;
        //}

        if($this->ctrl->urlIsApi($url)) {$this->logger->log('IS API');return $text;}
        if(   !ShortPixelUrlTools::isValid($url)
           && (!$this->isTemplate || strpos($url, '{{:') === false)) {$this->logger->log('NOT VALID');return $text;}
        if($this->ctrl->urlIsExcluded($url)) {$this->logger->log('EXCLUDED');return $text;}

        //custom exclusion for SliderRevolution TODO unhack
        if(ActiveIntegrations::_()->get('slider-revolution') && preg_match('/plugins\/revslider\/.*\/dummy.png$/', $url )) {
            return $text;
        }

        $pristineUrl = $url;
        //WP is encoding some characters, like & ( to &#038; ), and oh, some URLs end in spaces...
        $url = trim(html_entity_decode($url));

        //early check for the excluded selectors - only the basic cases when the selector is img.class img#id or img[attr]
        if($this->ctrl->tagIs('excluded', $text)) {
            $this->logger->log("Excluding: " . $text);
            return $text;
        }
        //prevent cases when html code including data-spai attributes gets copied into new articles
        if(strpos($text, 'data-spai') > 0) {
            //  this way it works if we have two rules for the same tag, eg: img:src and img:data-src etc. - fix for
            if(preg_match('/\s' . $attr . '=["\']{0,1}data\:image\/svg\+xml;/s', $text)) {
                //for cases when the src is pseudo
                //Seems that Thrive Architect is doing something like this under the hood? (see https://secure.helpscout.net/conversation/862862953/16430/)
                $this->logger->log("Ignoring $tag - $attr because it's already data:img: " . $url);
                return $text;
            }
            //for cases when it's normal URL, just get rid of data-spai's
            $text = preg_replace('/data-spai(-upd|)=["\'][0-9]*["\']/s', '', $text);
        }

        $eager = $this->isEager || $this->ctrl->tagIs('eager', $text);

        SHORTPIXEL_AI_DEBUG && $this->logger->log("Including: " . $url . ($eager ? ' EAGER' : ' LAZY'));

	    //some particular cases are hardcoded here...
	    // 1. Revolution Slider (rev-slidebg) Glow Pro's Swiper slider (attachment-glow-featured-slider-img) and Optimizer PRO's frontpage slider (stat_bg_img)
	    if ( strpos( $text, 'attachment-glow-featured-slider-img' ) ) {
		    $this->ctrl->affectedTags->add( 'figure', AffectedTags::SRC_ATTR); //the Glow Pro moves the image in <figure> tags
            $this->tagRule->used['figure'] = AffectedTags::SRC_ATTR;
	    }

        $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
        //Get current image size
        if($ext === 'css' || $ext === 'js' | $this->isTemplate) { //no need to calculate the image size if it's CSS
            $sizes = [1, 1, 'thumb' => false];
            $AR = 1;
        } else {
            $sizes = ShortPixelUrlTools::get_image_size($url);
            $AR = (isset($sizes[0]) && $sizes[0] && $sizes[1]) ? $sizes[0] / $sizes[1] : 1; //it appears that in some circumstances sizes[0] could be 0 and sizes[1] > 0... HS#42421
            $this->logger->log('Got Sizes: ', $sizes);
        }
        $qex = strlen($q) ? '' : '"';
        $qm = strlen($q) ? $q : '"';
        $atirReplacement = '';
        $absoluteUrl = ShortPixelUrlTools::absoluteUrl($url);

        if($this->tagRule->mergeAttr && (isset($sizes['thumb']) || $eager || preg_match('/@2x\./', $text))) {
            $this->logger->log('Integrate and remove: ' . $this->tagRule->mergeAttr . '. Sizes: ', $sizes);
            $matches = false;
            $this->logger->log('TEXT: ' . $text);
            if(preg_match('/\s' . $this->tagRule->mergeAttr . '=(\"|\')(.*?)(?:\"|\')/s', $text, $matches) && isset($matches[2]) && strlen(trim($matches[2]))){
                $this->logger->log('Matched mergeAttr:', $matches);
                $items = explode(',', $matches[2]);
                foreach($items as $item) {
                    $parts = explode(' ', trim($item));

                    if($eager) {
                        //we keep the srcset, but we replace the URLs inside with the API URLs
                        $apiUrl = $this->ctrl->get_api_url(ShortPixelUrlTools::absoluteUrl($parts[0]), false, false, false, $this->tagRule->getCustomCompression());
                        $atirReplacement .= (strlen($atirReplacement) ? ', ': ' ' . $this->tagRule->mergeAttr . '="')
                            . ($this->ctrl->urlIsApi($parts[0]) ? $parts[0] : $apiUrl)
                            . (isset($parts[1]) ? ' ' . $parts[1] : '');
                    }
                    elseif(isset($sizes['thumb'])
                        //also look for a @2x and no sizes inside the URL, this image could be larger than the original
                        ||     preg_match('/@2x\.\w+(\?|$)/', $parts[0])
                           && !preg_match('/-([0-9]+)x([0-9]+)@2x\.\w+(\?|$)/', $parts[0]))
                    {
                        $partSizes = ShortPixelUrlTools::get_image_size($parts[0]);
                        if(isset($partSizes[0]) && isset($partSizes[1]) && $partSizes [0] > 0 && $partSizes [1] > 0) {
                            $partAR = $partSizes[0] / $partSizes[1];
                            if($partSizes[0] > $sizes[0]
                                && abs(($partAR - $AR) / $AR) < 0.03) {
                                $sizes = $partSizes;
                                $url = $parts[0];
                            }
                        }
                    }
                }
                if(strlen($atirReplacement)) {
                    $atirReplacement .= '"';
                }
                else {
                    //we need to keep the attribute empty because some plugins as woocommerce are checking for it when doing variations
                    $atirReplacement = ' ' . $this->tagRule->mergeAttr . '=" "';
                }
            }
        }

        $spaiMeta = ''; $spaiMarker = ' data-spai=' . $qm . '1' . $qm; $dataLityTarget = '';

        $url = apply_filters('shortpixel/ai/originalUrl', $url); //# 37750

        //remove loading="lazy" if present (either we do our own JS lazy loading or the $eager flag is set).
        $text = preg_replace('/\s\bloading=["\']?lazy["\']?/s', '', $text);

        if($eager) {
            //we set any CSS or JS to ret_wait because of the many CORS issues we've seen with these.
            $wait = $ext == 'css' || $ext == 'js';
            $cacheVer = $wait ? $this->ctrl->cssCacheVer : false;
            if($this->isTemplate && strpos($url, '{{:') === 0) {
                $inlinePlaceholder = $this->ctrl->get_api_url( false, false, false, false, $this->tagRule->getCustomCompression(), $wait, $cacheVer)
                    . '/' . $url;
            } else {
                $inlinePlaceholder = $this->ctrl->get_api_url( $absoluteUrl, false, false, $this->ctrl->get_extension( $url ), $this->tagRule->getCustomCompression(), $wait, $cacheVer);
            }
            $spaiMarker = ' data-spai-egr=' . $qm . '1' . $qm;
        } else {
            //lazy
            //handle the data-lity TODO add this as a tag rule instead...
            if ( $tag == 'img' && $attr == 'src' && !!$this->ctrl->settings->areas->lity && preg_match('/\bdata-lity\b/', $text) ) {
                $dataLityTarget = ' data-lity-target="' . $this->ctrl->get_api_url( $absoluteUrl,false, false, $this->ctrl->get_extension( $url )) . '"';
                SHORTPIXEL_AI_DEBUG && $this->logger->log("LITY TARGET " . $dataLityTarget);
            }
            $inlinePlaceholder = isset($sizes[0]) ? ShortPixelUrlTools::generate_placeholder_svg($sizes[0], $sizes[1], /*$this->absoluteUrl(*/$url) : ShortPixelUrlTools::generate_placeholder_svg(false, false, $url);
        }
        $pattern = '/\s' . $attr . '\s*=\s*' . preg_quote($q . $pristineUrl . $q, '/') . '/';
        $replacement = ' '. $attr . '=' . $qm . $inlinePlaceholder . $qm . $spaiMarker . $spaiMeta . $dataLityTarget;

        SHORTPIXEL_AI_DEBUG && $this->logger->log("REPLACING: Pattern " . $pattern . " with " . $replacement . " in " . $text);

        $str = preg_replace($pattern, $replacement, $text);

        if($this->tagRule->mergeAttr) {
            $str = preg_replace('/\s' . $this->tagRule->mergeAttr . '=(\"|\').*?(\"|\')/s', $atirReplacement, $str);
            if($this->tagRule->mergeAttr == 'srcset' && $atirReplacement == '') {
                //also make sure we remove the sizes if srcset was removed (HS# 59394)
                $str = preg_replace('/\bsizes=(\"|\').*?(\"|\')/s', '', $str);
            }
        }
        $this->ctrl->affectedTags->add($tag, AffectedTags::SRC_ATTR);
        $this->tagRule->used[$tag] = AffectedTags::SRC_ATTR;
        SHORTPIXEL_AI_DEBUG && $this->logger->log("Replaced pattern: $pattern with $replacement. RESULTED TAG: $str\n\n");
        return $str;// . "<!-- original url: $url -->";
    }

    public  function replace_wc_gallery_thumbs( $matches ) {
        SHORTPIXEL_AI_DEBUG && $this->logger->log("replace_wc_gallery_thumbs: received ", $matches);
        $tag = $matches[1];
        if(strpos($matches[2], '%3C') === 0) { //it's HTML urlencoded, HS #38440
            $parser = new ShortPixelRegexParser($this->ctrl);
            $ret = str_replace($matches[2], rawurlencode($parser->parse(urldecode($matches[2]))), $matches[0]);
            SHORTPIXEL_AI_DEBUG && $this->logger->log("replace_wc_gallery_thumbs: found HTML urlencoded, RETURN ", $ret);
            return $ret;
        }
        if(!ShortPixelUrlTools::isValid($matches[2])) {
            SHORTPIXEL_AI_DEBUG && $this->logger->log("replace_wc_gallery_thumbs: INVALID URL " . $matches[2] . ", do nothing");
            return $matches[0];
        }
        $url = ShortPixelUrlTools::absoluteUrl($matches[2]);
        $str = str_replace($matches[2], $this->ctrl->get_api_url( $url, false, false, $this->ctrl->get_extension( $url )), $matches[0]);
        if($str != $matches[0]) {
            $this->ctrl->affectedTags->add($tag, AffectedTags::SRC_ATTR);
            $this->tagRule->used[$tag] = AffectedTags::SRC_ATTR;
        }
        return $str;
    }

    /**
     * for data-envira-srcset currently
     * @param $matches
     * @return null|string|string[]
     */
    public function replace_custom_srcset($matches)
    {
        $this->logger->log("REPLACE CUSTOM SRCSET ", $matches);
        if(isset($matches[5]) && $matches[5] == '\'') {
            $matches[3] = $matches[5];
            $matches[4] = $matches[6];
        }
        $qm = strlen($matches[3]) ? $matches[3] : '"';
        $text = $matches[0];
        if(strlen(trim($matches[4])) == 0) return $text; //it's an empty srcset attribute: srcset=" " - this is used by SPAI too for woocommerce variations JS in some cases, that would break otherwise
        $pattern = $matches[2] . '=' . $matches[3] . $matches[4] . $matches[3];
        $replacement = ' ' . $matches[2] . '=' . $qm . $this->replace_srcset($matches[4]) . $qm;
        if($this->ctrl->settings->behaviour->replace_method === 'src'
            && strpos($text, 'loading=') === false
            && !$this->isEager && !$this->ctrl->tagIs('eager', $text)) {
            $replacement .= ' loading="lazy"';
        }
        $pos = strpos($text, $pattern);
        if($pos === false) return $text;
        $str = substr($text, 0, $pos) . $replacement . substr($text, $pos + strlen($pattern));
        return $str;// . "<!-- original url: $url -->";
    }

    /**
     * parses a <script type="application/json">
     * @param $matches
     */
    public function replace_application_json_script($matches) {
        $lazy = strpos($matches[1], 'application/ld+json') === false ? $this->ctrl->settings->areas->parse_json_lazy : 0; //ld+json is structured data, don't lazy-load (0 marks not lazy-load
        $jsonParser = new ShortPixelJsonParser($this->ctrl, $this->tagRule, $lazy);
        $dataObj = json_decode(str_replace('&quot;', '"', $matches[2]));
        if(json_last_error() === JSON_ERROR_SYNTAX) {
            $this->logger->log("APPLICATION JSON syntax error", $matches);
            return $matches[0];
        }
        $this->logger->log("APPLICATION / JSON", $dataObj);
        return $matches[1] . json_encode($jsonParser->parse($dataObj)) . '</script>';
    }

    /**
     * for data-envira-srcset currently
     * @param $matches
     * @return null|string|string[]
     */
    public function replace_custom_json_attr($matches)
    {
        $qm = strlen($matches[3]) ? $matches[3] : '"';
        $text = $matches[0];
        $tag = $matches[1];
        $attr = $matches[2];
        if(isset($matches[5]) && $matches[5] == '\'') {
            $matches[3] = $matches[5];
            $matches[4] = $matches[6];
        }
        $jsonParser = new ShortPixelJsonParser($this->ctrl, $this->tagRule);
        $parsed = json_decode(str_replace('&quot;', '"', $matches[4]));
        if(json_last_error() === JSON_ERROR_SYNTAX) return $text;
        $replaced = str_replace('"', '&quot;',json_encode($jsonParser->parse($parsed), JSON_UNESCAPED_SLASHES));
        if($matches[4] == $replaced) return $text;
        $pattern = $attr . '=' . $matches[3] . $matches[4] . $matches[3];
        $replacement = ' ' . $attr . '=' . $qm . $replaced . $qm . ' data-spai-egr='. $qm . '1' . $qm;
        $pos = strpos($text, $pattern);
        if($pos === false) return $text;
        $str = substr($text, 0, $pos) . $replacement . substr($text, $pos + strlen($pattern));
        if(strtolower($tag) == 'section') {
            SHORTPIXEL_AI_DEBUG && $this->logger->log("JSON affected tag: DIV");
            $flags = AffectedTags::SRC_ATTR | AffectedTags::CSS_ATTR;
            $this->ctrl->affectedTags->add('div', $flags);
            $this->tagRule->used['div'] = $flags;
        }
        return $str;// . "<!-- original url: $url -->";
    }

    /**
     * for data-product_variations
     * @param $matches
     * @return null|string|string[]
     */
    public function replace_product_variations($matches)
    {
        SHORTPIXEL_AI_DEBUG && $this->logger->log("PRODUCT VARIATION", $matches);
        if(isset($matches[5]) && $matches[5] == '\'') {
            $matches[3] = $matches[5];
            $matches[4] = $matches[6];
        }
        $qm = strlen($matches[3]) ? $matches[3] : '"';
        SHORTPIXEL_AI_DEBUG && $this->logger->log("PRODUCT VARIATION QM ", $qm);
        $parsed = json_decode(str_replace('&quot;', '"', $matches[4]));
        SHORTPIXEL_AI_DEBUG && $this->logger->log("PRODUCT VARIATION PARSED: ", $parsed);
        $text = $matches[0];
        SHORTPIXEL_AI_DEBUG && $this->logger->log("PRODUCT VARIATION JSON LAST ERR: ", function_exists('json_last_error') ? json_last_error() : 'function missing');
        if(($err = json_last_error()) === JSON_ERROR_SYNTAX) {
            SHORTPIXEL_AI_DEBUG && $this->logger->log("PRODUCT VARIATIONS - JSON PARSE ERROR " . $err);
            return $text;
        }
        if(!is_array($parsed)) {
            SHORTPIXEL_AI_DEBUG && $this->logger->log("PRODUCT VARIATIONS - NOT AN ARRAY " . get_class($parsed));
            return $text;
        }
        $this->logger->log('PRODUCT VARIATIONS - replacing');
        $aiUrl = $this->ctrl->get_api_url(false, false);
        for($i = 0; $i < count($parsed); $i++)
        {
            SHORTPIXEL_AI_DEBUG && $this->logger->log("VARIATIONS - image:");
            $this->replace_product_variation_attrs($parsed[$i]->image, $aiUrl);

            if(isset($parsed[$i]->jck_additional_images) && is_array($parsed[$i]->jck_additional_images)) {
                $this->replace_product_variation_list_attrs($parsed[$i]->jck_additional_images, $aiUrl);
            }

            if(isset($parsed[$i]->variation_gallery_images) && is_array($parsed[$i]->variation_gallery_images)) {
                $this->replace_product_variation_list_attrs($parsed[$i]->variation_gallery_images, $aiUrl);
            }
        }
        $replaced = str_replace('"', '&quot;',json_encode($parsed, JSON_UNESCAPED_SLASHES));
        if($matches[4] == $replaced) return $text;
        $pattern = $matches[2] . '=' . $matches[3] . $matches[4] . $matches[3];
        $replacement = $matches[2] . '=' . $qm . $replaced . $qm . ' data-spai-egr=' . $qm . '1' . $qm;
        $pos = strpos($text, $pattern);
        if($pos === false) return $text;
        $str = substr($text, 0, $pos) . $replacement . substr($text, $pos + strlen($pattern));
        return $str;// . "<!-- original url: $url -->";
    }

    public function replace_product_variation_list_attrs($list, $aiUrl) {
        foreach ($list as $var) {
            $this->replace_product_variation_attrs($var, $aiUrl);
        }
    }

    protected function replace_product_variation_attrs($var, $aiUrl) {
        $this->replace_product_variation_attr($var, 'src', $aiUrl);
        $this->replace_product_variation_attr($var, 'full_src', $aiUrl);
        $this->replace_product_variation_attr($var, 'large_src', $aiUrl);
        $this->replace_product_variation_attr($var, 'thumb_src', $aiUrl);
        $this->replace_product_variation_attr($var, 'archive_src', $aiUrl);
        $this->replace_product_variation_attr($var, 'gallery_thumbnail_src', $aiUrl);
        $this->replace_product_variation_attr($var, 'url', $aiUrl);
        $this->replace_product_variation_set($var, 'srcset', $aiUrl);
        $this->replace_product_variation_set($var, 'large_srcset', $aiUrl);
        $this->replace_product_variation_set($var, 'gallery_thumbnail_srcset', $aiUrl);
    }

    protected function replace_product_variation_attr($var, $attr, $aiUrl)
    {
        if(!empty($var->$attr)) {
            SHORTPIXEL_AI_DEBUG && $this->logger->log(" -- ATTR - $attr " . $var->$attr);
            $var->$attr = $aiUrl . '/' . ShortPixelUrlTools::absoluteUrl(trim($var->$attr));
        }
    }

    protected function replace_product_variation_set($var, $set, $aiUrl)
    {
        if(!empty($var->$set)) {
            SHORTPIXEL_AI_DEBUG && $this->logger->log(" -- ATTR - $set " . $var->$set);
            // $var->srcset = preg_replace_callback('/data:image\/svg\+xml;u=[^\s]*/s', // old
            $var->$set = preg_replace_callback('/data:image\/svg\+xml;base64[^\s]*/s', // new
                array($this, 'pseudo_url_to_api_url'),
                $var->$set
            );
            SHORTPIXEL_AI_DEBUG && $this->logger->log(" -- ATTR - $set REPLACED placeholders? " . $var->$set);
            $var->$set = $this->replace_srcset( $var->$set );
        }
    }

    public function pseudo_url_to_api_url($match){
        $this->logger->log("VARIATIONS SRCSET ITEM", $match);
        $url = ShortPixelUrlTools::url_from_placeholder_svg($match[0]);
        $this->logger->log("VARIATIONS SRCSET URL", $url);
        return $this->ctrl->get_api_url(ShortPixelUrlTools::absoluteUrl($url), false, false, $this->ctrl->get_extension( $url ));
    }

    public function replace_srcset($srcset) {
        $aiSrcsetItems = array();
        $aiUrl = $this->ctrl->get_api_url(false, false);
        $aiUrlBase = $this->ctrl->settings->behaviour->api_url;
        $srcsetItems = explode(',', $srcset);
        foreach($srcsetItems as $srcsetItem) {
            $srcsetItem = trim($srcsetItem);
            $srcsetItemParts = explode(' ', $srcsetItem);
            if($this->ctrl->urlIsExcluded($srcsetItemParts[0])) {
                //if any of the items are excluded, don't replace
                SHORTPIXEL_AI_DEBUG && $this->logger->log("REPLACE SRCSET abort - excluded: " . $srcsetItem);
                return $srcset;
            }
            if(strpos($srcsetItem, $aiUrlBase) !== false || strpos($srcsetItem, 'data:image/') === 0) {
                SHORTPIXEL_AI_DEBUG && $this->logger->log("REPLACE SRCSET abort - AI url: " . $srcsetItem);
                return $srcset; //already parsed by the hook.
            }
            $prefix = strpos($aiUrl, 'http') === 0 ? '' : 'http:';
            $aiSrcsetItems[] = $prefix . $aiUrl .'/' . ShortPixelUrlTools::absoluteUrl(trim($srcsetItem));
        }
        return implode(', ', $aiSrcsetItems);
    }

    //NextGen specific
    //TODO make gallery specific
    public function replace_link_href($matches)
    {
        if (count($matches) < 3 || strpos($matches[0], 'href=' . $matches[1] . 'data:image/svg+xml;') // I think "u=" can be removed too
            || strpos($matches[0], 'ngg-fancybox') === false && strpos($matches[0], 'ngg-simplelightbox') === false) { //this is to limit replacing the href to NextGen's fancybox links
            //avoid duplicated replaces due to filters interference
            return $matches[0];
        }
        //$matches[1] will be either " or '
        return $this->_replace_images('a', 'href', $matches[0], $matches[2], $matches[1]);
    }
}
