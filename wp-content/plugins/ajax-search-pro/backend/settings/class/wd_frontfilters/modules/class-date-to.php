<?php
defined( 'ABSPATH' ) or die( "You can't access this file directly." );

class WD_FrontFilters_Module_DateTo extends WD_FrontFilters_Module_DateFrom {
    protected $type = 'date_to';
    protected $title = 'Before date filter module';
    protected $icon = ASP_URL . 'backend/settings/assets/wd_frontfilters/img/generic.png';
    protected $output_files = array(
        '/../views/date_to.php'
    );
    protected $css_files = array(
        array(
            'handle'        => 'wd-ff-date-to-module',
            'file'          => ASP_URL . 'backend/settings/assets/wd_frontfilters/css/date_to.css'
        )
    );
    protected $js_files = array(
        array(
            'handle'        => 'wd-ff-date-to-module',
            'file'          => ASP_URL . 'backend/settings/assets/wd_frontfilters/js/date_to.js',
            'dependencies'  => array('jquery', 'wd-ff-main-module', 'wd-ff-date-from-module')
        )
    );
}