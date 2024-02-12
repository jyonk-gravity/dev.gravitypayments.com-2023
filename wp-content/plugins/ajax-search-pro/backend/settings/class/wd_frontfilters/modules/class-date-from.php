<?php
defined( 'ABSPATH' ) or die( "You can't access this file directly." );

class WD_FrontFilters_Module_DateFrom extends WD_FrontFilters_Module_Abstract {
    protected $type = 'date_from';
    protected $title = 'After date filter module';
    protected $icon = ASP_URL . 'backend/settings/assets/wd_frontfilters/img/generic.png';
    protected $output_files = array(
        '/../views/date_from.php'
    );
    protected $css_files = array(
        array(
            'handle'        => 'wd-ff-date-from-module',
            'file'          => ASP_URL . 'backend/settings/assets/wd_frontfilters/css/date_from.css'
        )
    );
    protected $js_files = array(
        array(
            'handle'        => 'wd-ff-date-from-module',
            'file'          => ASP_URL . 'backend/settings/assets/wd_frontfilters/js/date_from.js',
            'dependencies'  => array('jquery', 'wd-ff-main-module')
        )
    );

    protected static $default_data = array(
        'option' => array(
            'id' => false,
            'label' => array(
                'text' => 'Date filter',
                'visible' => true
            ),
            'type' => '',
            'display_mode' => 'relative_date', // relative_date, date
            'date' => '',
            'relative_date' => array(
                'year' => 0,
                'month' => 0,
                'day' => 0
            ),
            'placeholder' => 'Choose a date',
            'date_format' => 'dd-mm-yy',
        )
    );

    public static function getData( $data = array(), $all = false ) {

        // Deep array merge, options in general first, then the individual selections
        $return = wd_array_merge_recursive_distinct( self::$default_data['option'], $data );

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