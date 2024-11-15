<?php
/**
 * Created by simon
 * Date: 19.01.2021
 */

namespace ShortPixel\AI;

abstract class JsLoader {

    private static $instance = false;

    /**
     * @var \ShortPixelAI
     */
    protected $ctrl = false;
    protected $settings = false;
    protected $logger = false;

    /**
     * @param \ShortPixelAI $ctrl
     * @return bool|JqueryJsLoader|VanillaJsLoader
     * @throws \Exception
     */
    public static function _($ctrl = false) {
        if( self::$instance !== false) {
            return self::$instance;
        }
        if($ctrl === false) {
            throw new \Exception("JsLoader cannot be constructed without controller");
        }

        $loader = ($ctrl->options->settings_behaviour_nojquery > 0 ? new VanillaJsLoader() : new JqueryJsLoader() );
        $loader->ctrl = $ctrl;
        $loader->settings = $ctrl->settings;
        self::$instance = $loader;
        return self::$instance;
    }

    protected function __construct() {
        $this->logger = \ShortPixelAILogger::instance();
    }

    /**
     * @return bool true if SPAI is setup correctly in the buffered HTML (js not dequeued, etc.)
     */
    public abstract function check($content);

    /**
     * add the SPAI JS
     * @return null
     */
    public function enqueue() {
        $current_user_login = wp_get_current_user()->ID;
        if ( !empty( $current_user_login ) && isset( $_COOKIE[ 'shortpixel-ai-front-worker' ] ) && !!$_COOKIE[ 'shortpixel-ai-front-worker' ])
        {
            $front_worker = Options::_()->get( 'front_worker', [ 'pages', 'on_boarding' ], Options\Option::_() );
            $front_worker = $front_worker instanceof Options\Option ? $front_worker : Options\Option::_();

            $front_worker->{$current_user_login} = $front_worker->{$current_user_login} instanceof Options\Option ? $front_worker->{$current_user_login} : Options\Option::_();

            if (
                \ShortPixelAI::userCan( 'manage_options' )
                && !!$front_worker->{$current_user_login}->enabled && $front_worker->{$current_user_login}->token === $this->ctrl->get_user_token()
            ) {
                add_filter( 'body_class', function( $classes ) {
                    $classes[] = 'spai-fw-sidebar-hidden';

                    return $classes;
                } );

                add_action( 'wp_head', function() {
                    Page::_( $this->ctrl )->render( 'front-checker.tpl.php' );
                } );

                wp_deregister_script( 'js-cookie');
                $this->ctrl->register_js('js-cookie', 'libs/js.cookie', true, true, '3.0.0-rc.0');
                $this->ctrl->register_js( 'spai-front-worker', 'front.worker', false);

                wp_localize_script( 'spai-front-worker', 'SPAIFrontConstants', [
                    'apiUrl'     => $this->settings->behaviour->api_url,
                    'folderUrls' => [
                        'plugins'  => str_replace( [ WP_CONTENT_URL, '/' ], '', WP_PLUGIN_URL ) . '/',
                        'content'  => str_replace( [ site_url(), '/' ], '', WP_CONTENT_URL ) . '/',
                        'includes' => WPINC . '/',
                    ],
                ] );
                wp_enqueue_script( 'spai-front-worker' );
            }
        }
    }

    /**
     * adds the info about tags that need to be handled by the JS because they have been modified server-side
     * @param $content
     * @return string updated content
     */
    public abstract function addTagData($content);

    /**
     * adds the fade-in CSS
     * @return null
     */
    public abstract function fadeInCss();

}