<?php
/**
 * Created by: simon
 * Date: 26.01.2021
 */

namespace ShortPixel\AI;

class TagRules
{
    private static $instance = false;

    private $ctrl;
    private $items;
    private $frontEndItems;

    public static function _()
    {
        if (self::$instance === false) {
            self::$instance = new TagRules();
        }
        return self::$instance;
    }

    protected function __construct() {
        $integrations = ActiveIntegrations::_( true )->getAll();
        $this->ctrl = \ShortPixelAI::_();
        $settings = $this->ctrl->settings;
        $logger = \ShortPixelAILogger::instance();
        $logger->log("BUILDING TAG RULES ....");
        if($settings->behaviour->replace_method !== 'srcset') {
            $imgTagRule = new TagRule('img|amp-img', 'src', false, false, false,
                /*mergeAttr: */($settings->behaviour->replace_method === 'src' ? 'srcset' : false), $settings->behaviour->replace_method === 'both');
        }

        $frontEndItems = array();
        $regexItems = array(
            new TagRule('img|div', 'data-src', false, false, false, false, false, 'url', false, false, true), //CHANGED ORDER for images which have both src and data-src - TODO better solution
//            new TagRule('img', 'data-srcset', false, false, false, false, true,
//                'srcset', 'replace_custom_srcset'),

            $imgTagRule,
            new TagRule('img', 'data-large-image'),
            new TagRule('a', 'href', 'media-gallery-link'), //this one seems generally related to sliders, see HS 972394549
            new TagRule('link', 'href', false, 'rel', 'icon', false, true),
            new TagRule('input', 'src', false, 'type', 'image', false), // only this case so far: HS#39219
            new TagRule('video|source', 'poster'),
            new TagRule('source', 'src', false, false, false, false, true),
            //for images that have only the srcset attribute present
            new TagRule('img', 'srcset', false, false, false, false, true,
                'srcset', 'replace_custom_srcset'),
            new TagRule('source', 'srcset', false, false, false, false, false,
                'srcset', 'replace_custom_srcset'),
            new TagRule('div|figure|li', 'data-thumb(?:nail|)', false, false, false, false, false,
                'data-thumb', 'replace_wc_gallery_thumbs')
        );
        if ($integrations['nextgen']) {
            //add |a to the img|div rule
            foreach($regexItems as $item) {
                if($item->tag === 'img|div') {
                    $item->tag = 'img|div|a';
                    break;
                }
            }
            $regexItems[] = new TagRule('a', 'data-thumbnail');
            //moved from RegexParser - Nextgen - check for <a href's>
            $regexItems[] = new TagRule('a', 'href', false, false, false, false, $settings->behaviour->nojquery > 0,
                'ahref', 'replace_link_href');
        }
        if ($integrations['oxygen']) {
            $regexItems[] = new TagRule('a', 'href', 'oxy-gallery-item', false, false, false, true);
            array_unshift($regexItems, new TagRule('img', 'data-original-src', false, false, false, false, true));
        }
        if ($integrations['modula']) {
            $regexItems[] = new TagRule('a', 'href', false, 'data-lightbox'); //fourth param filters by attribute
            $regexItems[] = new TagRule('a', 'href', false, 'data-fancybox');
            $regexItems[] = new TagRule('a', 'href', 'modula-item-link');
            $regexItems[] = new TagRule('img', 'data-full', false, false, false, false, true);
        }
        if ($integrations['elementor']) {
            // For the data-elementor-open-lightbox, we need to use eager as it conflicts with elementor's lazy-loading, if it's a lazy placeholder, their JS will break
            $openLB = new TagRule('a', 'href', false, 'data-elementor-open-lightbox', false, false,  true); //fourth param filters by attribute
            $openLB->noFront = true;
            $regexItems[] = $openLB;
            $regexItems[] = new TagRule('a', 'href', 'viba-portfolio-media-link'); //third param filters by class
            $regexItems[] = new TagRule('header|section|div', 'data-settings|data-options', false, false, false, false, false,
                'srcset', 'replace_custom_json_attr');
        }
        if ($integrations['elementor-addons']) {
            $regexItems[] = new TagRule('a', 'href', 'eael-magnific-link'); //fourth param filters by attribute
        }
        if ($integrations['viba-portfolio'] && !$integrations['elementor']) {
            $regexItems[] = new TagRule('a', 'href', 'viba-portfolio-media-link'); //third param filters by class
        }
        if ($integrations['slider-revolution']) {
            $regexItems[] = new TagRule('img', 'data-lazyload', false, false, false, false, true);
        }
        if ($integrations['envira']) {
            $regexItems[] = new TagRule('img', 'data-envira-src');
            $regexItems[] = new TagRule('img', 'data-safe-src');
            $regexItems[] = new TagRule('a', 'href', 'envira-gallery-link'); //third param filters by class
            $regexItems[] = new TagRule('img', 'data-envira-srcset', false, false, false, false, false,
                'srcset', 'replace_custom_srcset');
        }
        if ($integrations['everest']) {
            $regexItems[] = new TagRule('a', 'href', false, 'data-lightbox-type'); //fourth param filters by attribute
        }
        if ($integrations['wp-bakery']) {
            $regexItems[] = new TagRule('span', 'data-element-bg', 'dima-testimonial-image');  //third param filters by class
        }
        if ($integrations['foo']) {
            $regexItems[] = new TagRule('img', 'data-src-fg', 'fg-image', false, false, false, true);
            $regexItems[] = new TagRule('a', 'href', 'fg-thumb', 'data-attachment-id');
        }
        if ($integrations['global-gallery']) {
            for($i = 0; $i < count($regexItems); $i++) {
                if($regexItems[$i]->tag === 'a' && $regexItems[$i]->attr === 'href') {
                    unset($regexItems[$i]);
                }
            }
            $regexItems[] = new TagRule('a', 'href', false, false, false, true);
        }
        if ($integrations['essential-grid']) {
            $regexItems[] = new TagRule('img', 'data-lazythumb', false, false, false, false, true);
            $regexItems[] = new TagRule('img', 'data-lazysrc', false, false, false, false, true);
        }
        if ($integrations['smart-slider']) {
            $regexItems[] = new TagRule('div', 'data-desktop', 'n2-ss-slide-background-image');  //third param filters by class
        }
        if ($integrations['wp-grid-builder']) {
            $regexItems[] = new TagRule('div', 'data-wpgb-src');
        }
        if ($integrations['content-views']) {
            $regexItems[] = new TagRule('img', 'data-cvpsrc');
        }
        if ($integrations['the-grid']) {
            $regexItems[] = new TagRule('a', 'data-tolb-src');
        }
        /*
        if ($integrations['visual-product-configurator']) {
            $regexItems[] = new TagRule('input', 'data-img');
        }*/
        if ($integrations['acf']) {
            $regexItems[] = new TagRule('header', 'data-background');
        }
        if ($integrations['wpc-variations']) {
            $regexItems[] = new TagRule('option', 'data-imagesrc', false, false, false, false, false && true);
        }
        if ($integrations['soliloquy']) {
            $regexItems[] = new TagRule('img', 'data-soliloquy-src', false, false, false, false, true);
            $regexItems[] = new TagRule('img', 'data-soliloquy-src-mobile', false, false, false, false, true);
        }
        if ($integrations['jetpack']) {
            $regexItems[] = new TagRule('img', 'data-orig-file', false, false, false, false, true);
        }
        if($integrations['wp-bakery']) {
            SHORTPIXEL_AI_DEBUG && $logger->log("WP BAKERY data-ultimate-bg");
            $regexItems[] = new TagRule('', 'data-ultimate-bg', false, false, false, false, false,
                'bg-attr', 'replace_bg_attr');
        }

        if($integrations['woocommerce']) {
            $variationTags = 'form|div';
            $quickMatch = '/\<(' . $variationTags . ')\b[^>]+\bdata-product_variations=/is';
            //there are product variations
            $regexItems[] = new TagRule($variationTags, 'data-product_variations', false, false, false, false, false,
                'srcset', 'replace_product_variations', $quickMatch);
        }

        if($integrations['real3d-flipbook']) {
            $logger->log( 'REAL 3D FLIPBOOK: ENABLED' );
            $regexItems[] = new TagRule('div', 'data-flipbook-options', false, false, false, false, true,
                'srcset', 'replace_custom_json_attr');
        }

        if($integrations['acf']) {
            //ACF moves src's from hidden images to FIGURE backgrounds
            $this->ctrl->affectedTags->add('figure', AffectedTags::CSS_ATTR);
        }

        if($integrations['beaver-builder']) {
            $logger->log( 'BEAVER BUILDER: ENABLED' );
            $regexItems[] = new TagRule('div', 'data-parallax-image');
        }

        if($integrations['featherlight']) {
            $logger->log( 'FeatherLight: FRONT RULE ENABLED' );
            //{selectors: ['a'], type: 'attr', lazy: false, resize: false, origAttr: 'href', targetAttr: 'href', attrType: 'url', filter: [{attrName: 'data-featherlight', attrValue:'image'}]}
            $frontEndItems[] = new TagRule('a', 'href', false, 'data-featherlight', 'image', false, true,
                'url', false, false, true, false, false);
        }

        if($integrations['lightbox-photoswipe']) {
            $logger->log( 'Photoswipe: RULES ENABLED' );
            //{selectors: ['a'], type: 'attr', lazy: false, resize: false, origAttr: 'href', targetAttr: 'href', attrType: 'url', filter: [{attrName: 'data-featherlight', attrValue:'image'}]}
            $regexItems[] = new TagRule('a', 'href', false, 'data-lbwps-srcsmall');
            $regexItems[] = new TagRule('a', 'data-lbwps-srcsmall');
        }

        //BACKGROUND IMAGES
        //we need to specify the list of tags because on some situations PHP regex implementation throws catastrophic backtracking if we use [\w]+ instead...
        $bgTagsList = 'div|span|body|article|section|i|p|h[1-6]|form|img|figure|a|header|li';
        if($integrations['slider-revolution']) {
            $bgTagsList .= '|rs-bg-elem';
        }
        $regexItems[] = new TagRule($bgTagsList, 'background-image|background', false, false, false, false, false,
            'bg-style', 'replace_background_image_from_tag');

        //SPECIAL SETTINGS:
        if($settings->behaviour->replace_method !== 'src') { //srcset has to be checked too, in some cases the srcset wp hook isn't called...
            $regexItems[] = new TagRule('img', 'srcset', false, false, false, false, false,
                'srcset', 'replace_custom_srcset');
        }
        if ( $settings->areas->parse_css_files > 0)
        {
            $replaceUrls = ($settings->behaviour->nojquery <= 0 || !$settings->areas->backgrounds_lazy_style)
                && !( $integrations[ 'wp-rocket' ][ 'minify-css' ] && $integrations[ 'wp-rocket' ][ 'css-filter' ] )
                && !$integrations[ 'wp-fastest-cache' ] && !$integrations[ 'w3-total-cache' ]  && !$integrations[ 'wp-optimize']['enable_css'];

            $logger->log("CSS FILES TO CDN" . ($replaceUrls ? ' AND REPLACE URLs' : ''));

            $ssRule = new TagRule('link', 'href', false, 'rel', 'stylesheet',false,  true);
            if(!$replaceUrls) $ssRule->setCustomCompression('orig'); //only works with eager true
            if($settings->behaviour->nojquery > 0) $ssRule->setPostProcessor([$this, 'addCrossOrigin']);
            $regexItems[] = $ssRule;

            $preloadHrefRule = new TagRule('link', 'href', false, 'rel', 'preload', false, true);
            $preloadSetRule = new TagRule('link', 'imagesrcset', false, 'rel', 'preload', false, true,
                'srcset', 'replace_custom_srcset');
            if(!$replaceUrls) {
                $preloadHrefRule->setCustomCompression('orig');
                $preloadSetRule->setCustomCompression('orig');
            }
            if($settings->behaviour->nojquery > 0) {
                $preloadHrefRule->setPostProcessor([$this, 'addCrossOrigin']);
                $preloadSetRule->setPostProcessor([$this, 'addCrossOrigin']);
            }
            $regexItems[] = $preloadHrefRule;
            $regexItems[] = $preloadSetRule;
        }

        if ( !!$settings->areas->lity ) {
            $logger->log( 'LITY LIBRARY INTEGRATION: ENABLED' );
            $regexItems[] = new TagRule( 'a', 'href', false, 'data-lity' );
            $regexItems[] = new TagRule( 'a', 'data-lity-target', false, 'data-lity' );
        }

        //WPZOOM themes
        if($integrations['wpzoom-theme']) {
            $logger->log( 'WPZoom theme: ENABLED' );
            $regexItems[] = new TagRule('div|li', 'data-bigimg', false, false, false, false, true);
            $regexItems[] = new TagRule('div|li', 'data-smallimg', false, false, false, false, true);
        }

        //THEMES:
        $theme = $integrations['theme'];
        if(strpos($theme, 'Blocksy') === 0) {
            $logger->log("BLOCKSY ON");
            $regexItems[] = new TagRule('a', 'href', 'ct-image-container', false, false, false, true);
        }
        elseif(strpos($theme, 'Uncode') === 0) {
            $logger->log("Uncode ON");
            $regexItems[] = new TagRule('a', 'href', false, 'data-lbox', false, false, true);
        }
        elseif($theme == 'CROWD 2' || $theme == 'Lovely 2') {
            $regexItems[] = new TagRule('div|a|span', 'style', false, false, false, false, false,
                'srcset', 'replace_crowd2_img_styles');
        }
        elseif(strpos($theme, 'Jupiter') === 0) {
            //Jupiter has the mk slider in it, which uses srcsets encoded as JSON in data-mk-image-src-set atributes. How cool is that.
            $regexItems[] = new TagRule('img', 'data-mk-image-src-set', false, false, false, false, false,
                'srcset', 'replace_custom_json_attr');
            $regexItems[] = new TagRule('div', 'data-mk-img-set', false, false, false, false, false,
                'srcset', 'replace_custom_json_attr');
        }
        elseif(strpos($theme, 'Divi') === 0) {
            $this->ctrl->affectedTags->add('div', AffectedTags::CSS_ATTR);
            //Divi - Elegant Themes' custom attribute
            // changed to eager because in newer Divi versions the JS is adding a srcset that is not being replaced by the vanilla JS
            $regexItems[] = new TagRule('img', 'data-et-multi-view', false, false, false, false, true,
                'srcset', 'replace_custom_json_attr');
        }
        elseif(strpos($theme, 'Stack') === 0) {
            // Stack moves srcs from images to background-images of divs...
            $this->ctrl->affectedTags->add('div', AffectedTags::CSS_ATTR);
        }

        //Various modules that are included in themes
        if($integrations['avia-gallery-module']) { //the Avia gallery is included for example in the Enfold theme
            //it also has lightbox class - $regexItems[] = new TagRule('a', 'href', 'avia-gallery-big', false, false, false, true);
            $regexItems[] = new TagRule('a', 'href', 'lightbox', false, false, false, true);
            $regexItems[] = new TagRule('a', 'data-prev-img', 'lightbox', false, false, false, true);
            $regexItems[] = new TagRule('a', 'data-srcset', 'lightbox', false, false, false, true,
                'srcset', 'replace_custom_srcset');
        }

        $logger->log("FILTER TAG RULES");
        $this->items = $this->applyFilterAndVerify($regexItems, 'shortpixel/ai/customRules');
        $logger->log("TAG RULES: ", $this->items);
        $this->frontEndItems = $this->applyFilterAndVerify($frontEndItems, 'shortpixel/ai/customFrontendRules');
        $logger->log("FRONTEND TAG RULES: ", $this->frontEndItems);
    }

    protected static function applyFilterAndVerify($items, $filter) {
        $filteredItems = apply_filters( $filter, $items);
        $validatedItems = [];
        foreach($filteredItems as $extItem)
        {
            if($extItem instanceof TagRule) {
                $validatedItems[] = $extItem;
            }
            elseif(is_array($extItem) && count($extItem) >= 2) {
                //valid item
                $validatedItems[] = new TagRule($extItem[0], $extItem[1], isset($extItem[2]) ? $extItem[2] : false, isset($extItem[3]) ? $extItem[3] : false,
                    isset($extItem[5]) ? $extItem[5] : false, false, isset($extItem[6]) ? $extItem[6] : true);
            }
        }
        return $validatedItems;
    }

    /**
     * @return TagRule[]
     */
    public function items(){
        return $this->items;
    }

    public function frontEndItems() {
        return $this->frontEndItems;
    }

    public function addCrossOrigin($tag) {
        //add the crossorigin attribute but only if it's from our CDN domain, otherwise it's an external CSS that we don't want to mess with.
        if(   strpos($tag, 'crossorigin=') === false
           && preg_match('/href=[\'"]?' . preg_quote($this->ctrl->settings->behaviour->api_url, '/') . '/', $tag)) {
            $tag = preg_replace('/^\s*<(\w+)/', '<$1 crossorigin="anonymous"', $tag);
        }
        return $tag;
    }

    public function map() {
        $rules = $this->items;
        $tree = array();
        foreach($rules as $rule) {
            $tags = explode("|", $rule[0]);
            foreach($tags as $tag) {
                if(!isset($tree[$tag])) {
                    $tree[$tag] = array();
                }
                $ruleNode = array('attr' => $rule[1]);
                $ruleNode['classFilter'] = isset($rule[2]) ? $rule[2] : false;
                $ruleNode['attrFilter'] = isset($rule[3]) ? $rule[3] : false;
                $ruleNode['attrValFilter'] = isset($rule[5]) ? $rule[5] : false;
                $ruleNode['mergeAttr'] = isset($rule[4]) ? $rule[4] : false;
                $ruleNode['lazy'] = !isset($rule[6]) || ! $rule[6]? true : false;
                $tree[$tag][] = (object)$ruleNode;
            }
        }
        //add also the rule for background image
        $cssParser = $this->ctrl->getCssParser();
        $tree['*'] = array((object)array('attr' => 'style', 'lazy' => false, 'customReplacer' => array($cssParser, 'replace_in_tag_style_backgrounds')));
        return $tree;
    }

    public function usedLazy() {
        $used = [];
        foreach($this->items as $item) {
            if(count($item->used) && !$item->eager && !$item->noFront) {
                $used[] = $item;
            }
        }
        return $used;
    }

    public function setUsed($tag, $attr, $flags) {
        foreach($this->items as $item)  {
            if($item->attr == $attr && in_array($tag, explode('|', $item->tag))) {
                $item->used[$tag] = $flags;
                return;
            }
        }
    }
}