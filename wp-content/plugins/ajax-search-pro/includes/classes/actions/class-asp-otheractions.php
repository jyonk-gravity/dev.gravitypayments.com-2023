<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_OtherActions_Action")) {
    /**
     * Class WD_ASP_OtherActions_Action
     *
     * Other stuff
     *
     * @class         WD_ASP_OtherActions_Action
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Actions
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_OtherActions_Action extends WD_ASP_Action_Abstract {
        /**
         * Static instance storage
         *
         * @var self
         */
        protected static $_instance;

        public function handle() {}

        public function on_save_post() {
            // Clear all the cache just in case
            $ch = new WD_ASP_Deletecache_Handler();
            $ch->handle(false);
        }

        /**
         * Fix for 'WP External Links' plugin
         * https://wordpress.org/plugins/wp-external-links/
         *
         * @param $link
         */
        public function plug_WPExternalLinks_fix( $link ) {
            // ignore links with class "asp_showmore"
            if ( $link->has_attr_value( 'class', 'asp_showmore' ) ) {
                $link->set_ignore();
            }
        }

        public function pll_init_string_translations() {
            WD_ASP_PLL_Strings::init();
        }

        public function pll_save_string_translations() {
            // Save any possible PLL translation strings stack
            WD_ASP_PLL_Strings::save();
        }

        public function pll_register_string_translations() {
            WD_ASP_PLL_Strings::register();
        }

        /**
         * Triggers when asp_scheduled_activation_events is triggered (during activation only)
         */
        public function scheduledActivationEvents() {
            $index = new asp_indexTable();
            $index->scheduled();
        }

        // ------------------------------------------------------------
        //   ---------------- SINGLETON SPECIFIC --------------------
        // ------------------------------------------------------------
        public static function getInstance() {
            if ( ! ( self::$_instance instanceof self ) ) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }
    }
}