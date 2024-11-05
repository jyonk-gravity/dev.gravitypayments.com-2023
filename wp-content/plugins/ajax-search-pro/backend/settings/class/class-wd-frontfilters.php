<?php

use WPDRMS\ASP\Utils\Script;

defined('ABSPATH') or die("You can't access this file directly.");

if ( !class_exists("wd_frontFilters") ) {
    class wd_frontFilters extends wpdreamsType {

        /* Data structure actually used in the output option */
        private static $default_data = array(
            // The column options
            'columns' => array(),

            // Array of active filters
            'filters' => array(/*
                array(
                    'module_name' => 'generic',
                    'data' => array(..)
                ),
                ...
            */),
        );

        /* Additional default data, not used in the output */
        private static $default_data_addition = array(
            // Each item in the array of active filters
            'columns_item' => array(
                'min-width'=> '180px',
                'max-width'=> '320px'
            ),

            // Each item in the array of active filters
            'filters_item' => array(
                'module_name' => 'generic',
                'data' => array()
            ),
        );

        private $processed_data;

        private $version = '1.0'; // Used in CSS and JS enqueue
        private $css_files = array(
            array(
                'handle'        => 'wd-ff-main-module',
                'file'          => ASP_URL . 'backend/settings/assets/wd_frontfilters/css/main.css'
            )
        );
        private $js_files = array(
            array(
                'handle'        => 'wd-ff-main-module',
                'file'          => ASP_URL . 'backend/settings/assets/wd_frontfilters/js/main.js',
                'dependencies'  => array('jquery')
            )
        );

        private static $default = array(
            // Content type filter defaults
            'content_type' => array(
                'option' => array(
                    'label' => 'Filter by content type',
                    'display_mode' => 'checkboxes',
                    'items' => array(),
                    'required' => false,
                    'required_text' => 'Please select an option!'
                ),
                'item' => array(
                    'label' => '',
                    'selected' => false,
                    'id' => 0,  // Can be numeric, as well as a field name etc..
                    'level' => 0,
                    'default' => false
                ),
                'choices' => array(
                    -1 => 'Choose One/Select all',
                    'cpt' => 'Custom post types',
                    'comments' => 'Comments',
                    'taxonomies' => 'Taxonomy terms',
                    'users' => 'Users',
                    'blogs' => 'Multisite blogs',
                    'buddypress' => 'BuddyPress content',
                    'attachments' => 'Attachments'
                )
            ),
            // Post type filter defaults
            'post_type' => array(
                'option' => array(
                    'label' => 'Filter by Custom Post Type',
                    'display_mode' => 'checkboxes',
                    'items' => array(),
                    'required' => false,
                    'required_text' => 'Please select an option!'
                ),
                'item' => array(
                    'label' => '',
                    'selected' => false,
                    'id' => 0,  // Can be numeric, as well as a field name etc..
                    'level' => 0,
                    'default' => false
                ),
                'choices' => array(
                    -1 => 'Choose One/Select all',
                    'post' => 'Posts',
                    'pages' => 'Pages'
                )
            ),
            // From date filter defaults
            'date_from' => array(
                'option' => array(
                    'label' => 'Content from',
                    'placeholder' => 'Choose date',
                    'required' => false,
                    'required_text' => 'Please select an option!',
                    'date_format' => 'dd-mm-yy',
                    'date_type' => 'relative_date',  // date or relative_date
                    'date' => '2020-01-01',
                    'relative_date' => array(2,0,0) // Y, M, D before current
                )
            ),
            // To date filter defaults
            'date_to' => array(
                'option' => array(
                    'label' => 'Content to',
                    'placeholder' => 'Choose date',
                    'required' => false,
                    'required_text' => 'Please select an option!',
                    'date_format' => 'dd-mm-yy',
                    'date_type' => 'relative_date',  // date or relative_date
                    'date' => '2020-01-01',
                    'relative_date' => array(0,0,0) // Y, M, D before current
                )
            ),
            'taxonomy' => array(
                'option' => array(
                    'label' => 'Filter by Categories',
                    'display_mode' => 'checkboxes',
                    'display_mode_args' => array(),
                    'type' => 'exclude', // include (exclude all, include selected), exclude (include all, exclude selected)
                    'items' => array(),
                    'taxonomy' => 'category',
                    'required' => false,
                    'required_text' => 'Please select an option!',
                    'hide_empty' => false,
                    'maintain_hierarchy' => true,
                    'term_orderby' => 'name', //name, count, ID,
                    'term_order' => 'ASC', // ASC DESC,
                    'allow_empty' => false  // allow results where the terms are not associated with the post type object
                ),
                'display_mode_args' => array(
                    'checkboxes' => array(
                        'default_state' => 'unchecked', // checked, unchecked
                        'hide_children_on_unchecked' => true
                    )
                ),
                'item' => array(
                    'label' => '',
                    'selected' => false,
                    'id' => 0,  // Can be numeric, as well as a field name etc..
                    'level' => 0,
                    'default' => false
                ),
                'choices' => array(
                    -1 => 'Choose One/Select all',
                    0 => 'Display all from taxonomy'
                )
            ),
            // Custom field filter defaults
            'custom_field' => array(
                'option' => array(
                    'label' => 'Filter by Custom Field',
                    'display_mode' => 'checkboxes',
                    'display_mode_args' => array(),
                    'items' => array(),
                    'field' => 'field_name',
                    'field_source' => 'postmeta',   // postmeta, usermeta
                    'operator' => 'like',
                    'required' => false,
                    'required_text' => 'Please select an option!',
                    'allow_empty' => false  // allow results where the field is not associated with the post type object
                ),
                'display_mode_args' => array(
                    'checkboxes' => array(
                        'logic' => 'OR', // OR, AND
                        'hide_children_on_unchecked' => true
                    ),
                    'dropdown' => array(
                        'placeholder' => '',
                        'logic' => 'OR', // OR, AND, ANDSE
                        'multiple' => false
                    ),
                    'dropdownsearch' => array(
                        'placeholder' => ''
                    ),
                    'multisearch' => array(
                        'placeholder' => ''
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
                    ),
                ),
                'item' => array(
                    'label' => '',
                    'selected' => false,
                    'id' => 0,  // Can be numeric, as well as a field name etc..
                    'level' => 0,
                    'default' => false
                ),
                'choices' => array(
                    -1 => 'Choose One/Select all'
                )
            ),
            'button' => array(
                'option' => array(
                    'label' => 'Reset',
                    'type' => 'reset' // reset, search
                ),
            )
        );

        private static $core_modules = array(
            'WD_FrontFilters_Module_Abstract' => array(
                'name' => 'abstract',
                'file' => 'class-abstract.php'
            ),
            'WD_FrontFilters_Module_Generic' => array(
                'name' => 'generic',    // must be the same as the module name (new Module_Class)->name
                'file' => 'class-generic.php'
            ),
            'WD_FrontFilters_Module_DateFrom' => array(
                'name' => 'date_from',
                'file' => 'class-date-from.php'
            ),
            'WD_FrontFilters_Module_DateTo' => array(
                'name' => 'date_to',
                'file' => 'class-date-to.php'
            ),
            'WD_FrontFilters_Module_ContentType' => array(
                'name' => 'content_type',
                'file' => 'class-content-type.php'
            ),
            'WD_FrontFilters_Module_PostType' => array(
                'name' => 'post_type',
                'file' => 'class-post-type.php'
            ),
            'WD_FrontFilters_Module_Taxonomy' => array(
                'name' => 'taxonomy',
                'file' => 'class-taxonomy.php'
            )
        );
        private static $core_modules_noinit = array(
            'WD_FrontFilters_Module_Abstract'
        );

        private $modules = array();

        public function getType() {
            spl_autoload_register(array(static::class, 'autoload'));
            parent::getType();
            $this->processData();

            // Modules
            try {
                foreach ( array_diff(array_keys(self::$core_modules), self::$core_modules_noinit) as $module ) {
                    $o = new $module;
                    if ( !isset($this->modules[$o->getType()]) ) {
                        $this->modules[$o->getType()] = $o;
                    } else {
                        throw new Exception('Module with name ' . $o->getType() . ' already exists.');
                    }
                }
            } catch (Exception $e) {
                echo $e->getMessage();
            }

            $this->loadAssets();

            // Output
            ob_start();
            include('wd_frontfilters/views/wd-frontfilters.php');
            echo ob_get_clean();
        }

        public static function autoload( $class ) {
            if ( isset(self::$core_modules[$class]) ) {
                include_once('wd_frontfilters/modules/' . self::$core_modules[$class]['file']);
            }
        }

        public static function manualLoad() {
            if ( wd_asp()->manager->getContext() == 'special' ) {
                foreach (self::$core_modules as $class => $data) {
                    include_once('wd_frontfilters/modules/' . self::$core_modules[$class]['file']);
                    $class::ajax();
                }
            }
        }

        public static function getDataByOptions( $options = array() ) {
            spl_autoload_register(array(static::class, 'autoload'));
            $return = array_merge(self::$default_data, $options);

            // Column data
            foreach ( $return['columns'] as &$column ) {
                $column = array_merge(self::$default_data_addition['columns_item'], $column);
            }

            // Module data
            foreach ( $return['filters'] as &$filter ) {
                $filter = array_merge(self::$default_data_addition['filters_item'], $filter);
                foreach ( self::$core_modules as $class => $module ) {
                    if ( $module['name'] == $filter['module_name'] ) {
                       $filter['data'] = $class::getData($filter['data']);
                       break;
                    }
                }
            }

            return $return;
        }

        public static function getFiltersByOptions( $options = array(), $column = false,  $parse = true ) {
            if ( $parse ) {
                $options = static::getDataByOptions( $options );
            }
            if ( $column === false ) {
                return $options['filters'];
            } else {
                $filters = array();
                foreach ( $options['filters'] as $filter ) {
                    if ( $filter['column'] == $column ) {
                        $filters[] = $filter;
                    }
                }
                return $filters;
            }
        }

        public function getFilters( $column = false ) {
            if ( $column === false ) {
                return $this->processed_data['filters'];
            } else {
                $filters = array();
                foreach ( $this->processed_data['filters'] as $filter ) {
                    if ( $filter['column'] == $column ) {
                        $filters[] = $filter;
                    }
                }
                return $filters;
            }
        }

        private function getModules() {
            return $this->modules;
        }

        private function loadAssets(){
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
                    'dependencies'  => array(),
                    'version'       => 1,
                    'in_footer'     => true
                ), $js);
                wp_enqueue_script($js['handle'], $js['file'], $js['dependencies'], $this->version, $js['in_footer']);
            }
            $modules_default = array();
            foreach ( $this->modules as $module ) {
                $modules_default[$module->getType()] = array(
                    'data' => $module->getData(array(), true)
                );
            }
            Script::objectToInlineScript('wd-ff-main-module', 'WD_FrontFilters', array(
                'modulesDefault' => $modules_default
            ));
        }

        private function processData() {
            // Make sure that the correct variables are used
            $this->processed_data = static::getDataByOptions( $this->decode_param($this->data) );
            $this->data = $this->encode_param($this->processed_data);
        }
    }

    // Trigger manual loader of module clases, in case of Ajax Requests
    wd_frontFilters::manualLoad();
}
