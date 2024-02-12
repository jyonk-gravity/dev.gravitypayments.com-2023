<?php
defined( 'ABSPATH' ) or die( "You can't access this file directly." );

class WD_FrontFilters_Module_Custom_Field extends WD_FrontFilters_Module_Abstract {
    protected $type = 'custom_field';
    protected $title = 'Custom field filter module';
    protected $icon = ASP_URL . 'backend/settings/assets/wd_frontfilters/img/generic.png';
    protected $output_files = array(
        '/../views/custom_field.php'
    );
    protected $css_files = array(
        array(
            'handle'        => 'wd-ff-custom_field-module',
            'file'          => ASP_URL . 'backend/settings/assets/wd_frontfilters/css/custom_field.css'
        )
    );
    protected $js_files = array(
        array(
            'handle'        => 'wd-ff-custom_field-module',
            'file'          => ASP_URL . 'backend/settings/assets/wd_frontfilters/js/custom_field.js',
            'dependencies'  => array('jquery', 'wd-ff-main-module')
        )
    );

    protected static $default_data = array(
        'option' => array(
            'id' => false,
            'label' => array(
                'text' => 'Filter by Custom Field',
                'visible' => true
            ),
            'display_mode' => 'checkboxes',
            'display_mode_args' => array(
                'checkboxes' => array(
                    'logic' => 'OR', // OR, AND
                    'hide_children_on_unchecked' => true
                ),
                'dropdown' => array(
                    'multi' => false,
                    'searchable' => false,
                    'placeholder' => '',
                    'logic' => 'OR', // OR, AND, ANDSE
                    'multiple' => false
                ),
                'slider' => array(
                    'slider_prefix' => '-,',
                    'slider_suffix' => '.',
                    'slider_step' => 1,
                    'slider_from' => 1,
                    'slider_to'   => 1000,
                    'slider_decimals' => 0,
                    'slider_t_separator' => ' ',
                    'operator' => 'let'
                ),
                'range' => array(
                    'range_prefix' => '-,',
                    'range_suffix' => '.',
                    'range_step' => 1,
                    'range_from' => 1,
                    'range_to'   => 1000,
                    'range_decimals' => 0,
                    'range_t_separator' => ' '
                ),
                'datepicker' => array(
                    'placeholder' => '',
                    'date_format' => 'dd/mm/yy',
                    'date_store_format' => 'datetime' // datetime, acf, timestamp
                )
            ),
            'select_all' => array(
                'enabled' => true,
                'text' => 'Select all/one'
            ),
            'value_display' => array(
                'selects' => '||Choose one/Any**',  // Select types: checkbox, dropdown, multiselect, multisearch etc..
                'text'    => '',                    // Textarea, input type text, input type hidden
                'slider'   => '',
                'range1'   => '',
                'range2'   => '',
                'datepicker' => ''
            ),
            'value' => '',                          // Defaults are shown, the values is copied here (hidden)
            'field' => 'field_name',
            'field_source' => 'postmeta',   // postmeta, usermeta
            'operator' => 'like',
            'required' => false,
            'required_text' => 'Please select an option!',
            'allow_empty' => false,  // allow results where the field is not associated with the post type object
            'column' => 1,
            'row' => 1, // Not used yet
            'position' => 1 // Position within column -> row
        )
    );

    public static function getData( $data = array(), $all = false ) {

        // Deep array merge, options in general first, then the individual selections
        $return = wd_array_merge_recursive_distinct( self::$default_data['option'], $data );

        foreach ( $return['items'] as &$item ) {
            $item = array_merge(self::$default_data['item'], $item);
        }

        if ( $all ) {
            $return = array_merge( self::$default_data, array('option' => $return) );
        }

        return $return;
    }

    public static function getDataEncoded( $data = array(), $all = false ) {
        return base64_encode( json_encode( static::getData($data, $all) ) );
    }

    public static function ajax() {
        if ( !has_action('wp_ajax_wd_ff_get_taxonomy_terms') )
            add_action('wp_ajax_wd_ff_get_taxonomy_terms', get_called_class().'::getTerms');
        if ( !has_action('wp_ajax_wd_ff_get_selected_taxonomy_terms') )
            add_action('wp_ajax_wd_ff_get_selected_taxonomy_terms',  get_called_class().'::getSelectedTerms');
    }
}