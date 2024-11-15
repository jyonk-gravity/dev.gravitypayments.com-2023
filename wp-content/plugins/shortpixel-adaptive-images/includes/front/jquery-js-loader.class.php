<?php
/**
 * Created by simon
 * Date: 19.01.2021
 */

namespace ShortPixel\AI;

class JqueryJsLoader extends JsLoader {

    protected function __construct() {
        parent::__construct();
    }

    public function enqueue() {
        $testFE = false;
        if ( !!$this->ctrl->options->tests_frontEnd_enqueued ) {
            $testFE = true;
            $this->ctrl->register_js('spai-tests', 'ai.tests', true, false, true, [], true);
        }

        $noresize_selectors = $this->ctrl->splitSelectors( $this->settings->exclusions->noresize_selectors, ',' );
        $eager_selectors = $this->ctrl->splitSelectors( $this->settings->exclusions->eager_selectors, ',' );
        if(ActiveIntegrations::_()->has('modula')) {
            //This is for the creative gallery, because it sets the images positions outside of the view and they are never replaced
            $noresize_selectors[] = '.modula-creative-gallery img.pic';
            $eager_selectors[] = '.modula-creative-gallery img.pic';
        }

        $this->ctrl->register_js('spai-scripts', 'ai-' . \ShortPixelAI::AI_JS_VERSION, false, false, false, ['jquery']);
        $nextgen = (!!$this->settings->compression->webp || !!$this->settings->compression->avif);
        wp_localize_script( 'spai-scripts', 'spai_settings', [
            'api_domain'            =>  parse_url($this->settings->behaviour->api_url, PHP_URL_HOST),
            'api_url'               => $this->ctrl->get_api_url(false, '%WIDTH%', '%HEIGHT%', 'noauto'), //the noauto type gets rid of the to_auto - the jQuery JS handles it based on extensions_to_nextgenimg
            'api_short_url'         => $this->ctrl->get_api_url(false, false, false, 'svg' ),
            'method'                => $this->settings->behaviour->replace_method,
            'crop'                  => !!$this->settings->behaviour->crop,
            'size_breakpoints'      => (object)['on' => $this->settings->behaviour->size_breakpoints, 'base' => $this->settings->behaviour->size_breakpoints_base, 'rate' => $this->settings->behaviour->size_breakpoints_rate],
            'lqip'                  => !!$this->settings->behaviour->lqip,
            'lazy_threshold'        => (int) is_int( $this->settings->behaviour->lazy_threshold ) && $this->settings->behaviour->lazy_threshold >= 0 ? $this->settings->behaviour->lazy_threshold : 500,
            'hover_handling'        => !!$this->settings->behaviour->hover_handling,
            'native_lazy'           => !!$this->settings->areas->native_lazy,
            'serve_svg'             => true, //!!$this->settings->areas->serve_svg,
            'debug'                 => SHORTPIXEL_AI_DEBUG,
            'site_url'              => apply_filters('shortpixel/ai/originalUrl', home_url()),
            'plugin_url'            => SHORTPIXEL_AI_PLUGIN_BASEURL,
            'version'               => SHORTPIXEL_AI_VERSION,
            'excluded_selectors'    => $this->ctrl->splitSelectors( $this->settings->exclusions->excluded_selectors, ',' ),
            'eager_selectors'       => $eager_selectors,
            'noresize_selectors'    => $noresize_selectors,
            'alter2wh'               => !!$this->settings->behaviour->alter2wh,
            'use_first_sizes'       => ActiveIntegrations::_()->getUseFirstSizes(),
            'lazy_bg_style'         => !!$this->settings->areas->backgrounds_lazy_style,
            'active_integrations'   => ActiveIntegrations::_()->getAll(),
            'parse_css_files'       => $this->settings->areas->parse_css_files > 0,
            'backgrounds_max_width' => (int) is_int( $this->settings->areas->backgrounds_max_width ) && $this->settings->areas->backgrounds_max_width >= 0 ? $this->settings->areas->backgrounds_max_width : 1920,
            'sep'                   => \ShortPixelAI::SEP, //separator
            'webp'                  => !!$this->settings->compression->webp,
            'avif'                  => !!$this->settings->compression->avif,
            'webp_detect'           => !$this->ctrl->varyCacheSupport,
            'extensions_to_nextgenimg'    => [
                'png' => $nextgen  && !!$this->settings->compression->png_to_webp,
                'jpg' => $nextgen && !!$this->settings->compression->jpg_to_webp,
                'gif' => $nextgen && !!$this->settings->compression->gif_to_webp,
            ],
            'sniper'                => $this->ctrl->plugin_url . 'assets/img/target.cur',
            'affected_tags'         => '{{SPAI-AFFECTED-TAGS}}',
            'ajax_url'              => admin_url( 'admin-ajax.php' ),
            'ajax_nonce'            => wp_create_nonce('shortpixel-ai-settings'),
            //**** LET THIS ONE BE LAST - SWIFT Performance HTML optimize bug when their Fix Invalid HTML option is on
            //the excluded_paths can contain URLs so we base64 encode them in order to pass our own JS parser :)
            'excluded_paths'        => array_map( 'base64_encode', $this->ctrl->splitSelectors( $this->settings->exclusions->excluded_paths, PHP_EOL ) ),
        ] );

        wp_enqueue_script( 'spai-scripts' );

        if(ActiveIntegrations::_()->has('wp-rocket')) {
            add_filter('rocket_defer_inline_exclusions', [$this, 'wp_rocket_no_defer_spai_settings']);
        }

        parent::enqueue();
    }

    public function wp_rocket_no_defer_spai_settings($regex) {
        if( is_string( $regex ) ){
            return $regex . '|spai_settings';
        }
        $regex[] = 'spai_settings';
        return $regex;
    }

    public function addTagData($content)
    {
        if ( strpos( $content, '{{SPAI-AFFECTED-TAGS}}' ) ) {
            $this->logger->log( "AFFECTED TAGS PLACEHOLDER FOUND: ", $this->ctrl->affectedTags->getAll() );
            $content = str_replace( '{{SPAI-AFFECTED-TAGS}}', addslashes( json_encode( $this->ctrl->affectedTags->getAll() ) ), $content );
        }
        else {
            $this->logger->log( "AFFECTED TAGS PLACEHOLDER NOT FOUND: ", $this->ctrl->affectedTags->getAll() );
            $content = str_replace( '</body>', '<script>var spai_affectedTags = "' . addslashes( json_encode( $this->ctrl->affectedTags->getAll() ) ) . '";</script></body>', $content );
        }
        return $content;
    }

    public function check($content)
    {
        return wp_script_is('spai-scripts');
    }

    public function fadeInCss()
    {
        wp_register_style( 'spai-fadein', false );
        wp_enqueue_style( 'spai-fadein' );
        //Exclude the .zoomImg's as it conflicts with rules of WooCommerce.
        wp_add_inline_style( 'spai-fadein',
            'img[data-spai]{'
            . 'opacity: 0;'
            . '} '
            . 'div.woocommerce-product-gallery img[data-spai]{' //exclusions
            . 'opacity: 1;'
            . '} '
            . 'img[data-spai-egr],'
            . 'img[data-spai-lazy-loaded],'
            . 'img[data-spai-upd] {'
            . 'transition: opacity .5s linear .2s;'
            . '-webkit-transition: opacity .5s linear .2s;'
            . '-moz-transition: opacity .5s linear .2s;'
            . '-o-transition: opacity .5s linear .2s;'
            . ' opacity: 1;'
            . '}');
    }
}