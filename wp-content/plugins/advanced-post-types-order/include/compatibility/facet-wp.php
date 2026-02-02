<?php
    
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    
    class APTO_compatibility_facet_wp
        {
            function __construct()
                {
                    add_filter ( 'apto/query_match_sort_id',                    array ( $this, 'facetwp_query_match_sort_id'), 99, 4 );
                    add_filter( 'facetwp_query_args',                           array ( $this, 'facetwp_query_args' ), 99, 2);
                    
                    //Attempt to re-order the post ids
                    add_filter( 'facetwp_filtered_post_ids',                    array ( $this, 'facetwp_filtered_post_ids' ), 99, 2);
                    add_filter( 'facetwp_filtered_query_args',                  array ( $this, 'facetwp_filtered_query_args' ), 99, 2 );
                }
                
                
            function facetwp_filtered_post_ids( $post_ids, $class )
                {
                    if ( ! is_array ( $post_ids ) ||    count ( $post_ids ) < 1 )
                        return $post_ids;
                        
                    //check if using a menu_order sort by
                    if ( isset ( $class->template['query_obj'] )    &&  isset ( $class->template['query_obj']['orderby'] ) )
                        {
                            $facet_order    =   $class->template['query_obj']['orderby'];
                            
                            if ( is_array ( $facet_order )  &&  count ( $facet_order ) > 0 )
                                {
                                    $found  =   FALSE;
                                    foreach ( $facet_order  as  $facet_order_item )    
                                        {
                                            if ( $facet_order_item['key']   === 'menu_order' )
                                                $found  =   TRUE;
                                        }
                                        
                                    if ( ! $found )
                                        return $post_ids;
                                }
                        }
                        
                    //attempt to identify if using a taxonomy term
                    $facets =   $class->facets;
                    
                    reset ( $facets );
                    $facet_name = key ( $facets );
                    $selected_values = [];

                    if ( ! empty( $class->facets[ $facet_name ]['selected_values'] ) ) {
                        $selected_values = (array) $class->facets[ $facet_name ]['selected_values'];
                    }

                    if ( empty( $selected_values ) && ! empty( $class->ajax_params['facets'] ) ) {
                        foreach ( $class->ajax_params['facets'] as $f ) {
                            if ( isset( $f['facet_name'] ) && $f['facet_name'] === $facet_name ) {
                                $selected_values = ! empty( $f['selected_values'] ) ? (array) $f['selected_values'] : [];
                                break;
                            }
                        }
                    }
                    
                    if ( count ( $selected_values ) > 1 ||  count ( $selected_values ) < 1 )
                        return $post_ids;
                    
                    reset ( $selected_values );
                    $term_slug  =   current ( $selected_values );
                    
                    $taxonomy_name  =   '';
                    if (  isset ( $facets[ $facet_name ]['source'] )    &&  strpos( $facets[ $facet_name ]['source'], 'tax/' )  === 0 )
                        $taxonomy_name  =   str_replace ( 'tax/', '', $facets[ $facet_name ]['source'] );
                    
                    global $wpdb;
                    $query_variables    =   array();
                    $mysql_query        =   "SELECT tt.term_taxonomy_id, tt.taxonomy FROM " . $wpdb->terms . " AS t
                                                                        JOIN " . $wpdb->term_taxonomy  ." AS tt ON t.term_id = tt.term_id
                                                                        WHERE t.slug = %s ";
                    $query_variables[]  =  $term_slug;
                    
                    if ( ! empty ( $taxonomy_name ) )
                        {
                            $mysql_query        .= " AND tt.taxonomy = %s";
                            $query_variables[]  =  $taxonomy_name;
                        }
                                        
                    $term_data =    $wpdb->get_row ( $wpdb->prepare( $mysql_query, ...$query_variables ) );

                    $term = get_term_by( 'term_taxonomy_id', $term_data->term_taxonomy_id, $term_data->taxonomy );

                    $taxonomy = get_taxonomy( $term->taxonomy );
                    $post_types  =   isset ( $taxonomy->object_type ) ? $taxonomy->object_type  :   FALSE;
                    
                    if ( $post_types    === FALSE   ||  ! is_array ( $post_types ) )
                        return $post_ids;
                    
                    reset ( $post_types );
                    $post_type  =   current ( $post_types );
                    
                    $query_args =   array  (
                                                'post_type'     =>  $post_type,
                                                
                                                
                                                'orderby'       =>  'menu_order',
                                                
                                                'tax_query'                 => array(
                                                                                        array(
                                                                                            'taxonomy'      => $term->taxonomy,
                                                                                            'field'         => 'term_id',
                                                                                            'terms'         => $term->term_id
                                                                                            )
                                                                                        ),
                                                                                        
                                                'fields'        =>                      'ids'     
                                                );
                    
                    $wp_query   =   new WP_Query ( $query_args );
                    $posts      =   $wp_query->posts;
                    
                    if ( count ( $posts ) > 0 )
                        {
                            //re-arrange the array
                            $new_post_ids;
                            
                            foreach ( $posts    as  $key    =>  $post_id )
                                {
                                    if ( ! in_array ( $post_id, $post_ids ) )
                                        continue;
                                        
                                    $new_post_ids[] =   $post_id;
                                    
                                    unset ( $post_ids[ array_search ( $post_id, $post_ids ) ] );
                                }
                                
                            if ( count ( $post_ids ) > 0 )
                                {
                                    foreach ( $post_ids as $post_id )
                                        {
                                            $new_post_ids[] =   $post_id;   
                                        }
                                }
                                
                            return $new_post_ids;
                        }
                        
                    return $post_ids;
                }
                
                
            function facetwp_query_args( $query_args, $class )
                {
                    if ( isset ( $query_args['s'] )  &&  empty ( $query_args['s']  ) )
                        unset ( $query_args['s'] );
                    
                    return $query_args;   
                }
                
                
            function facetwp_query_match_sort_id( $sort_view_id, $orderBy, $query, $sorts_match_filter )
                {
                    if ( isset ( $query->query['post__in'] )    &&  is_array ( $query->query['post__in'] )  &&  count ( $query->query['post__in'] ) >   0 )
                        return "";
                    
                    return $sort_view_id;   
                }
                
            
            function facetwp_filtered_query_args( $query_args, $object )
                {
                    if ( ! isset ( $query_args['post__in'] ) ||  ! is_array ( $query_args['post__in'] )  ||  count ( $query_args['post__in'] )   <   1  )
                        return $query_args;
                        
                    $query_args['orderby']  =   'post__in';                        
                        
                    return $query_args;   
                }
                
        }
        
        
    new APTO_compatibility_facet_wp();
        
        
