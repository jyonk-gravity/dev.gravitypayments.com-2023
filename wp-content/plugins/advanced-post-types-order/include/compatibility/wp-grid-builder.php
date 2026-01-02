<?php
    
    use WP_Grid_Builder\Includes\Database;
    
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    
    class APTO_compatibility_wp_grid_builder
        {
            function __construct()
                {
                    add_filter ( 'apto/query_match_sort_id',                    array ( $this, 'wp_grid_builder_query_match_sort_id'), 99, 4 );
                }
                
                
            function wp_grid_builder_query_match_sort_id( $sort_view_id, $orderBy, $query, $sorts_match_filter )
                {
                    
                    if ( ! isset ( $query->query['wp_grid_builder'] ) )    
                        return $sort_view_id;
                        
                    $facet      =   '';
                    $term_name  =   '';
                    
                    if ( isset ( $_GET['wpgb-ajax'] ) )
                        {
                            $wpgb   =   isset ( $_POST['wpgb'] )    ?   json_decode ( stripslashes (  $_POST['wpgb']  ) )  :   '';
                            
                            if ( empty ( $wpgb ) )
                                return $sort_view_id;
                                                    
                            $facet_id   =           (int)$wpgb->id;
                            if ( empty ( $facet_id ) )
                                {
                                    $facets =   wpgb_get_facet_instances ( $wpgb->facets );
                                    if ( empty ( $facets ) )
                                        return $sort_view_id;
                                    
                                    $facet_id =   '';
                                    $_get_data  =   $_GET;
                                    if ( isset ( $_get_data[ 'wpgb-ajax' ] ) )
                                        unset (  $_get_data[ 'wpgb-ajax' ] );
                                        
                                    foreach ( $_get_data as  $key    =>  $value ) 
                                        {
                                            $slug   =   ltrim ( $key, "_" );
                                            
                                            foreach ( $facets   as  $facet_key  =>  $facet_data )
                                                {
                                                    if ( $slug ===  $facet_data['slug'] )
                                                        $facet_id =   $facet_key;
                                                }
                                        }
                                        
                                    if ( empty ( $facet_id ) )
                                        return $sort_view_id;
                                }
                            
                            $facets =   wpgb_get_facet_instances ( $facet_id );
                            $facet  =   $facets[ $facet_id ];
                                
                            $term_name  =   $_GET[ '_' . $facet['slug'] ];
                        }
                    
                    if ( empty ( $facet )   ||  empty ( $term_name ) )
                        {
                            //check if direct url access
                            $results = Database::query_results(
        [
                                    'select'  => 'id, slug, type, source, settings',
                                    'from'    => 'facets',
                                    'orderby' => 'type DESC',
                                    'id'      => [],
                                ]
                            );
                  
                            $facets = wpgb_normalize_facets( $results, [] );
                            
                            if ( count ( $facets )  <   1 )
                                return $sort_view_id;
                                
                            foreach ( $facets   as  $key    =>  $facet )
                                {
                                    if ( isset ( $_GET[ '_' . $facet['slug']  ] ) )
                                        {
                                            $term_name  =   sanitize_text_field ( $_GET[ '_' . $facet['slug'] ] );
                                            break;
                                        }
                                } 
                        }

                    if ( empty ( $facet )   ||  empty ( $term_name ) )
                        return $sort_view_id;                        
                    
                    $term   =   get_term_by ( 'slug', $term_name, $facet['taxonomy'] );
                    if ( ! is_object ( $term ) )
                        return $sort_view_id;
                    
                    $query_args =   $query->query;
                    $query_args['tax_query']   = array(
                                                        array(
                                                                'taxonomy' => $facet['taxonomy'],
                                                                'field' => 'slug',
                                                                'terms' => $term_name,
                                                                )
                                                        );
                    unset( $query_args['wp_grid_builder'] );
                                            
                    $custom_query           =   new WP_Query( $query_args );
                                        
                    global $APTO;
                    $query_match_sort_id    =   $APTO->functions->query_match_sort_id ( $custom_query, array() );
                    
                    if ( $query_match_sort_id > 0 )
                        return $query_match_sort_id;
                        
                    return $sort_view_id;
                }
        }
        
        
    new APTO_compatibility_wp_grid_builder();