<?php
abstract class WD_FrontFilters_Module_Abstract {
    protected $name = 'abstract';  // Short module name - must be DISTINCTIVE, used in data manipulation
    protected $title = 'Module label';
    protected $icon = 'module.png';
    protected $output = '';

    protected $version = '1.0'; // Used in CSS and JS enqueue
    protected $output_files = array(/*
        '/../views/file1.php',
        '/../views/file2.php',
        ...
    */);
    protected $css_files = array(/*
        array(
            'handle'        => 'script-handle-name',
            'file'          => 'file.js',
            'dependencies'  => array('jquery'),
            'media'         => 'all'
        ),
        ...
    */);
    protected $js_files = array(/*
        array(
            'handle'        => 'script-handle-name',
            'file'          => 'file.js',
            'dependencies'  => array('jquery'),
            'in_footer'     => true
        ),
        ...
    */);

    /**
     * const = FINAL in this case, DO NOT OVERRIDE IN INHERENT CLASSES
     * This data is shared across all modules. It is merged into static::$default_data
     */
    const DEFAULT_SHARED_DATA = array(
        'option' => array(
            'visibility' => array(
                'desktop'   => true,
                'tablet'    => true,
                'mobile'    => true
            ),
            'required'  => false,
            'required_text' => 'Please select an option!',
            'column' => 1,
            'row' => 1, // Not used yet
            'position' => 1 // Position within column -> row
        )
    );

    protected static $default_data = array(
        /*
        'option' => array(      -> 'option' key is REQUIRED
            'items' => ...,
            'option1' => ...,
        ),
        'item' => array( ... )
        ...
        */
    );

    public final function __construct() {
        if ( !empty($this->type) && $this->type != 'abstract' ) {
            $this->loadAssets();
            $this->loadOutput();
            static::mergeSharedDefaultData();
        } else {
            throw new Exception(static::class . ': Module not loaded, name should be set.');
        }
    }

    public final function getOutput() {
        return $this->output;
    }

    public final function getType() {
        return $this->type;
    }

    public final function getTitle() {
        return $this->title;
    }

    public final function getIcon() {
        return $this->icon;
    }

    public static function getData( $data = array(), $all = false ) {
        // self = this class, static = inherent class where it is used
        return array_merge(static::$default_data, $data);
    }

    public static function ajax() {
        // Register ajax handlers here
    }

    protected final static function mergeSharedDefaultData() {
        static::$default_data['option'] =
            isset(static::$default_data['option']) ? static::$default_data['option'] : array();
        static::$default_data['option'] = wd_array_merge_recursive_distinct(
            static::$default_data['option'], self::DEFAULT_SHARED_DATA['option']
        );
    }

    protected function loadAssets(){
        foreach ( $this->css_files as $css ) {
            $css = array_merge(array(
                'dependencies'  => array(),
                'version'       => 1,
                'media'         => 'all'
            ), $css);
            wp_enqueue_style($css['handle'], $css['file'], $css['dependencies'], $this->version, $css['media']);
        }

        foreach ( $this->js_files as $js ) {
            $js = array_merge(array(
                'dependencies'  => array('jquery', 'wd-ff-main-module'),
                'version'       => 1,
                'in_footer'     => true
            ), $js);
            wp_enqueue_script($js['handle'], $js['file'], $js['dependencies'], $this->version, $js['in_footer']);
        }
    }

    protected function loadOutput(){
        if ( !empty($this->output_files) ) {
            foreach ( $this->output_files as $file ) {
                ob_start();
                require_once(dirname(__FILE__) . $file);
                $this->output .= ob_get_clean();
            }
        }
    }
}