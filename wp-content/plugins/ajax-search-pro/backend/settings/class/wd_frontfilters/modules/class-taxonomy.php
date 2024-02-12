<?php

use WPDRMS\ASP\Utils\Ajax;

defined( 'ABSPATH' ) or die( "You can't access this file directly." );

class WD_FrontFilters_Module_Taxonomy extends WD_FrontFilters_Module_Abstract {
    protected $type = 'taxonomy';
    protected $title = 'Taxonomy terms filter module';
    protected $icon = ASP_URL . 'backend/settings/assets/wd_frontfilters/img/generic.png';
    protected $output_files = array(
        '/../views/taxonomy.php'
    );
    protected $css_files = array(
        array(
            'handle'        => 'wd-ff-taxonomy-module',
            'file'          => ASP_URL . 'backend/settings/assets/wd_frontfilters/css/taxonomy.css'
        )
    );
    protected $js_files = array(
        array(
            'handle'        => 'wd-ff-taxonomy-module',
            'file'          => ASP_URL . 'backend/settings/assets/wd_frontfilters/js/taxonomy.js',
            'dependencies'  => array('jquery', 'wd-ff-main-module')
        )
    );

    protected static $default_data = array(
        'option' => array(
            'id' => false,
            'label' => array(
                'text' => 'Filter by Categories',
                'visible' => true
            ),
            'display_mode' => 'checkboxes',
            'display_mode_args' => array(
                'checkboxes' => array(
                    'default_state' => 'unchecked', // checked, unchecked
                    'hide_children_on_unchecked' => true
                ),
                'dropdown' => array(
                    'default' => 'first' // Select all (-1), first, last or a term ID
                )
            ),
            'select_all' => array(
                'enabled' => true,
                'text' => 'Select all/one'
            ),
            'items' => array(),
            'taxonomy' => 'select',
            'mode' => 'exclude',
            'required' => false,
            'required_text' => 'Please select an option!',
            'hide_empty' => false,
            'maintain_hierarchy' => true,
            'term_orderby' => 'name',   //name, count, ID,
            'term_order' => 'ASC',      // ASC DESC,
            'allow_empty' => false,     // allow results where the terms are not associated with the post type object
            'term_logic' => 'and',      // and, or, andex
            'column' => 1,
            'row' => 1, // Not used yet
            'position' => 1 // Position within column -> row
        ),
        'item' => array(
            'label' => '',
            'selected' => true,
            'field' => -1,  // Can be numeric, as well as a field name etc..
            'level' => 0,
            'default' => false
        ),
        'choices' => array()
    );

    public function getTaxonomiesList() {
        $taxonomies = get_taxonomies(array(), 'objects');
        $taxonomies_array = array();
        foreach ( $taxonomies as $k => $taxonomy ) {
            $taxonomies_array[$taxonomy->name] =
                $taxonomy->name . ' - ' . $taxonomy->labels->name .
                ( isset($taxonomy->object_type[0]) ? ' [' . implode(', ', $taxonomy->object_type) . ']' : '' );
        }

        return $taxonomies_array;
    }

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

    public static function getSelectedTerms() {
        $taxonomy = $_POST['wd_taxonomy'];
        $items = $_POST['wd_items'];
        $selected = self::$default_data['item'];

        foreach($items as $k => &$t) {
            if ( $t['id'] == -200 && $taxonomy == 'post_format' ) {
                $t = apply_filters('asp_post_format_standard', $t);
                $t['label'] = 'Standard';
            } else {
                $term = get_term($t['id'], $taxonomy);
                if ( empty($term) || is_wp_error($term) ) {
                    unset($items[$k]);
                    continue;
                }
                $t['label'] = $term->name;

                // WPML
                $language_code = apply_filters( 'wpml_element_language_code', null, array( 'element_id'=> (int)$term->term_id, 'element_type'=> $term->taxonomy ) );
                if ( defined('ICL_SITEPRESS_VERSION') && !empty($language_code) ) {
                  $t['label'] .= ' [' . $language_code . ']';
                }
            }
        }

        if ( isset($_POST['wd_selected']) ) {
            $term = get_term($_POST['wd_selected'], $taxonomy);
            if ( !empty($term) && !is_wp_error($term) ) {
                $selected['label'] = $term->name;
                $selected['id'] = $term->term_id;

                // WPML
                $language_code = apply_filters( 'wpml_element_language_code', null, array( 'element_id'=> (int)$term->term_id, 'element_type'=> $term->taxonomy ) );
                if ( defined('ICL_SITEPRESS_VERSION') && !empty($language_code) ) {
                    $selected['label'] .= ' [' . $language_code . ']';
                }
            }
        }

        print_r("!!ASPSTART_HTML!!" . json_encode($items) . "!!ASPEND_HTML!!");
        print_r("!!ASPSTART_SELECTED!!" . json_encode($selected) . "!!ASPEND_SELECTED!!");
        die();
    }

    public static function getTerms() {
        $ret = array();
        $taxonomy = $_POST['wd_taxonomy'];

        // WPMl?
        $languages = apply_filters( 'wpml_active_languages', NULL, 'orderby=id&order=desc' );
        $terms = array();

        $args = array(
          'taxonomy' => $taxonomy,
          'hide_empty' => false
        );

        // This is a search too
        if ( isset($_POST['wd_phrase']) ) {
            $args = array_merge($args, array(
                'search' => trim($_POST['wd_phrase']),
                'number' => 15
            ));
        }

        if (  defined('ICL_SITEPRESS_VERSION') && !empty( $languages ) ) {
            foreach( $languages as $l ) {
                if ( isset($l['language_code']) ) {
                  do_action( 'wpml_switch_language', $l['language_code'] );

                  $gterms = get_terms($taxonomy, $args);
                  if ( !is_wp_error($gterms) && count($gterms) > 0 ) {
                    $terms = array_merge( $terms, $gterms );
                    foreach ( $gterms as &$term ) {
                      $term->name = $term->name . ' [' . $l['language_code'] . ']';
                    }
                  }
                }
            }
        } else {
            $terms = get_terms($taxonomy, $args);
        }

        if ( $taxonomy == 'post_format' && !is_wp_error($terms) && !empty($terms) ) {
            $std_term = new stdClass();
            $std_term->term_id = -200;
            $std_term->taxonomy = 'post_format';
            $std_term->children = array();
            $std_term->name = 'Standard';
            $std_term->label = 'Standard';
            $std_term->parent = 0;
            $std_term = apply_filters('asp_post_format_standard', $std_term);
            array_unshift($terms, $std_term);
        }
		Ajax::prepareHeaders();

        if ( !empty($terms) && is_array($terms) ) {
            $termsHierarchical = array();
            wd_sort_terms_hierarchicaly($terms, $termsHierarchical);
            wd_flatten_hierarchical_terms($termsHierarchical, $terms);
            foreach ( $terms as $term ) {
                $ret[] = array_merge(self::$default_data['item'], array(
                    'id' => $term->term_id,
                    'label' => $term->name,
                    'level' => $term->level
                ));
            }
        }

        print_r("!!ASPSTART_HTML!!" . json_encode($ret) . "!!ASPEND_HTML!!");
        die();
    }

    public static function ajax() {
        if ( !has_action('wp_ajax_wd_ff_get_taxonomy_terms') )
            add_action('wp_ajax_wd_ff_get_taxonomy_terms', get_called_class().'::getTerms');
        if ( !has_action('wp_ajax_wd_ff_get_selected_taxonomy_terms') )
            add_action('wp_ajax_wd_ff_get_selected_taxonomy_terms',  get_called_class().'::getSelectedTerms');
    }
}