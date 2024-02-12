<?php
defined( 'ABSPATH' ) or die( "You can't access this file directly." );

class WD_FrontFilters_Module_PostType extends WD_FrontFilters_Module_Abstract {
    protected $type = 'post_type';
    protected $title = 'Post type filter module';
    protected $icon = ASP_URL . 'backend/settings/assets/wd_frontfilters/img/generic.png';
    protected $output_files = array(
        '/../views/post_type.php'
    );
    protected $css_files = array(
        array(
            'handle'        => 'wd-ff-post-type-module',
            'file'          => ASP_URL . 'backend/settings/assets/wd_frontfilters/css/post_type.css'
        )
    );
    protected $js_files = array(
        array(
            'handle'        => 'wd-ff-post-type-module',
            'file'          => ASP_URL . 'backend/settings/assets/wd_frontfilters/js/post_type.js',
            'dependencies'  => array('jquery', 'wd-ff-main-module')
        )
    );

    protected static $default_data = array(
        'option' => array(
            'id' => false,
            'label' => array(
                'text' => 'Post type filter',
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
            -1 => 'Choose One/Select all'
        )
    );

    public static function getData( $data = array(), $all = false ) {

        // Deep array merge, options in general first, then the individual selections
        $return = wd_array_merge_recursive_distinct( self::$default_data['option'], $data );

        foreach ( $return['items'] as &$item ) {
            $item = array_merge(self::$default_data['item'], $item);
        }

        if ( $all ) {
            $types = get_post_types(array(
                "public" => true,
                "_builtin" => false
            ), "objects", "OR");
            $post_types = array();

            foreach ($types as $k => $v) {
                if ( !in_array($k, wpdreamsType::NON_DISPLAYABLE_POST_TYPES) ) {
                    $post_types[$k] = $v->label;
                }
            }

            $return = array_merge( self::$default_data, array('option' => $return) );
            if ( count($post_types) > 0 ) {
                $return['choices'] = array_merge($return['choices'], $post_types);
            }
        }

        return $return;
    }

    public static function getDataEncoded( $data = array(), $all = false ) {
        return base64_encode( json_encode( static::getData($data, $all) ) );
    }
}