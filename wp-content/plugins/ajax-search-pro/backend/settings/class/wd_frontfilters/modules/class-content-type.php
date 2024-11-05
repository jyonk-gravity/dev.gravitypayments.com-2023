<?php
defined( 'ABSPATH' ) or die( "You can't access this file directly." );

class WD_FrontFilters_Module_ContentType extends WD_FrontFilters_Module_Abstract {
    protected $type = 'content_type';
    protected $title = 'Content type filter module';
    protected $icon = ASP_URL . 'backend/settings/assets/wd_frontfilters/img/generic.png';
    protected $output_files = array(
        '/../views/content_type.php'
    );
    protected $css_files = array(
        array(
            'handle'        => 'wd-ff-content-type-module',
            'file'          => ASP_URL . 'backend/settings/assets/wd_frontfilters/css/content_type.css'
        )
    );
    protected $js_files = array(
        array(
            'handle'        => 'wd-ff-content-type-module',
            'file'          => ASP_URL . 'backend/settings/assets/wd_frontfilters/js/content_type.js',
            'dependencies'  => array('jquery', 'wd-ff-main-module')
        )
    );

    protected static $default_data = array(
        'option' => array(
            'id' => false,
            'label' => array(
                'text' => 'Content type filters',
                'visible' => true
            ),
            'type' => '',
            'display_mode' => 'checkboxes',
            'items' => array()
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
            'cpt' => 'Custom post types',
            'comments' => 'Comments',
            'taxonomies' => 'Taxonomies',
            'users' => 'Users',
            'blogs' => 'Multisite blogs',
            'buddypress' => 'BuddyPress content',
            'attachments' => 'Attachments'
        )
    );

    public static function getData( $data = array(), $all = false ) {

        // Deep array merge, options in general first, then the individual selections
        $return = array_merge( self::$default_data['option'], $data );

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