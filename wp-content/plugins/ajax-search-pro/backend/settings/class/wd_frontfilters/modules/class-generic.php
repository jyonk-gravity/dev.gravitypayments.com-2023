<?php
defined( 'ABSPATH' ) or die( "You can't access this file directly." );

class WD_FrontFilters_Module_Generic extends WD_FrontFilters_Module_Abstract {
    protected $type = 'generic';
    protected $title = 'Generic filter module';
    protected $icon = ASP_URL . 'backend/settings/assets/wd_frontfilters/img/generic.png';
    protected $output_files = array(
        '/../views/generic.php'
    );
    protected $css_files = array(
        array(
            'handle'        => 'wd-ff-generic-module',
            'file'          => ASP_URL . 'backend/settings/assets/wd_frontfilters/css/generic.css'
        )
    );
    protected $js_files = array(
        array(
            'handle'        => 'wd-ff-generic-module',
            'file'          => ASP_URL . 'backend/settings/assets/wd_frontfilters/js/generic.js',
            'dependencies'  => array('jquery', 'wd-ff-main-module')
        )
    );

    protected static $default_data = array(
        'option' => array(
            'id' => false,
            'label' => array(
                'text' => 'Generic filter',
                'visible' => true
            ),
            'type' => '',
            'display_mode' => 'checkboxes',
            'items' => array(),
            'required' => false,
            'required_text' => 'Please select an option!',
            'column' => 1,
            'row' => 1, // Not used yet
            'position' => 1 // Position within column -> row
        ),
        'item' => array(
            'label' => '',
            'selected' => false,
            'field' => -1,  // Can be numeric, as well as a field name etc..
            'level' => 0,
            'default' => false
        ),
        'choices' => array(
            -1 => 'Choose One/Select all',
            'exact' => 'Exact matches only',
            'title' => 'Search in title',
            'content' => 'Search in content',
            'excerpt' => 'Search in excerpt'
        )
    );

    public static function getData( $data = array(), $all = false ) {

        // Deep array merge, options in general first, then the individual selections
        $return = wd_array_merge_recursive_distinct( self::$default_data['option'], $data );

        foreach ( $return['items'] as &$item ) {
            $item = array_merge(self::$default_data['item'], $item);
        }

        if ( $all ) {
            return array_merge( self::$default_data, array('option' => $return) );
        } else {
            return $return;
        }
    }

    public static function getDataEncoded( $data = array(), $all = false ) {
        return base64_encode( json_encode( static::getData($data, $all) ) );
    }
}