<?php
    
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    
    class APTO_compatibility_jet_engine
        {
            function __construct()
                {
                    add_filter( 'apto/default_interface_sort/taxonomy' ,        array ( $this, 'jet_filtering_taxonomy' ) );
                    add_filter( 'apto/default_interface_sort/term_id' ,         array ( $this, 'jet_filtering_term_id' ) );
                    
                    add_action ( 'apto/query_get_orderby_taxonomy_name/loop/before_terms_posts', array ( $this, 'query_get_orderby_taxonomy_name') );
                    
                    //check for ajax call and if custom sorting filter applied
                    add_filter ( 'apto/ignore_custom_order',                    array ( $this, 'apto_ignore_custom_order' ), 99, 3 );
                }
                
                
            function jet_filtering_taxonomy( $taxonomy )
                {
                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                    if  ( ! is_plugin_active( 'jet-engine/jet-engine.php' ) )
                        return $taxonomy;
                    
                    if ( ! empty ( $taxonomy ) )
                        return $taxonomy;
                    
                    if ( ! isset ( $_GET['jet_engine_filters'] )    ||  ! is_array ( $_GET['jet_engine_filters'] )   || count ( $_GET['jet_engine_filters'] ) !== 1 ) 
                        return $taxonomy;
                    
                    $term_id    =   intval( $_GET['jet_engine_filters'][0] );
                    $term_data  =   get_term ( $term_id );
                    
                    if ( $term_data instanceof WP_Term )
                        $taxonomy   =   $term_data->taxonomy;
                    
                    return $taxonomy;
                }
                
                
            function jet_filtering_term_id( $term_id )
                {
                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                    if  ( ! is_plugin_active( 'jet-engine/jet-engine.php' ) )
                        return $term_id;
                    
                    if ( ! isset ( $_GET['jet_engine_filters'] )    ||  ! is_array ( $_GET['jet_engine_filters']) || count ( $_GET['jet_engine_filters'] ) !== 1 ) 
                        return $term_id;
                    
                    $term_id    =   intval( $_GET['jet_engine_filters'][0] );
                        
                    return $term_id;
                }
                
            /**
            * Elementor and jet-smart-filters fix
            * 
            */
            function query_get_orderby_taxonomy_name()
                {
                    
                    $this->remove_anonymous_object_filter( 'elementor/query/jet-smart-filters', 'Jet_Smart_Filters_Provider_EPro_Posts', 'posts_add_query_args');    
                    
                }
                
                
            function apto_ignore_custom_order( $ignore, $orderBy, $query )
                {
                    if ( ! isset ( $_GET['jsf_ajax'] ) )
                        return $ignore;
                        
                    if ( isset ( $_POST['query'] )&&    is_array ( $_POST['query'] )    &&  isset ( $_POST['query']['_sort_standard'] ) )
                        {
                            $_sort_standard =   json_decode ( stripslashes( $_POST['query']['_sort_standard'] ) );
                            if ( is_object( $_sort_standard ) )
                                {
                                    if ( isset ( $_sort_standard->orderby ) &&  $_sort_standard->orderby !== 'menu_order' )
                                        return TRUE;
                                }
                        }
                        
                    return $ignore;   
                }
                
        }
        
        
    new APTO_compatibility_jet_engine();
        
        
