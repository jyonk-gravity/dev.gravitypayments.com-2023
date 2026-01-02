<?php
    
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    
    class APTO_compatibility_woocommerce
        {
            function __construct()
                {
                    /**
                    * Unnest query if WooCommerce. This is created by product_category shortcode
                    * Conditions: only if nested include a single tax array
                    * 
                    * Why they do that ?!
                    */
                    add_filter('apto/query_filter_valid_data',                  array ( $this, 'woocommerce_replace_nested_query' ), 5);
                    
                    //WooCommerce 3.0 and up fix
                    add_filter('apto/query_filter_valid_data',                  array ( $this, 'woocommerce_query_filter_valid_data' ) );
                    
                    //WooCommerce allow sort to apply for wc_get_products()
                    add_filter('apto/query_filter_valid_data',                  array ( $this, 'woocommerce_apto_query_filter_valid_data' ) );
                    
                    /**
                    * Clear the woocommerce shortcodes cache data on a re-order update
                    */
                    //add_action('apto/default-interface/order_update_complete', 'woocommerce_apto_order_update_complete');
                    add_action('apto/reorder-interface/order_update_complete',  array ( $this, 'woocommerce_apto_order_update_complete' ) );
                    
                    /**
                    * Apply WooCommerce category order while using visual attributes filtering    
                    */
                    add_filter('apto/query_filter_valid_data',                  array ( $this, 'woocommerce_cat_order_query_filter_valid_data' ), 99);
                }

                
            function woocommerce_replace_nested_query( $query )
                {
                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                    if  ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) )
                        return $query;

                    if(empty($query->query_vars['post_type']))
                        return $query;
                        
                    if( (is_string($query->query_vars['post_type'])   &&  $query->query_vars['post_type']   !=  'product' )
                            ||
                        ( is_array($query->query_vars)   && is_array($query->query_vars['post_type'])   &&  ( count($query->query_vars['post_type']) > 1 ||  count($query->query_vars['post_type']) < 1 ||   (  isset ( $query->query_vars['post_type'][0] ) &&  $query->query_vars['post_type'][0]   !=  'product' ) ) )
                        )
                        {
                            return $query;
                        }
                        
                    //we ned 2 taxo arrays
                    $tax_data   =   $query->tax_query->queries;
                    if(isset($tax_data['relation']))
                        unset($tax_data['relation']);
                    
                    //expect 2 elements
                    if(count($tax_data) !=  2)
                        return $query;
                    
                    foreach($tax_data   as  $key    =>  $data)
                        {
                            if(isset($data['relation']))
                                unset( $tax_data[$key]['relation'] );
                        }
                    
                    //unnest
                    $unnested   =   FALSE;
                    $nested_key     =   '';
                    foreach($tax_data   as  $key    =>  $data)
                        {
                            if ( is_array($data)    &&  count ( $data ) == 1 ) 
                                {
                                    reset($data);
                                    $nested_key =   key ( $data );
                                    if ( is_numeric($nested_key))
                                        {
                                            $tax_data[$key] =   $data[$nested_key];
                                            $unnested   =   TRUE;
                                        }
                                }
                        }
                    
                    $preserved_tax_data =   $tax_data;
                    
                    $found_tax_visibility   =   TRUE;
                    foreach($tax_data   as  $key    =>  $data)
                        {
                            if(isset($data['taxonomy']) &&  $data['taxonomy']   ==  'product_visibility')
                                {
                                    unset($tax_data[ $key ]);
                                    $found_tax_visibility   =   TRUE;
                                }
                        }
                        
                    if ( $unnested  &&  $found_tax_visibility)
                        {
                            $query->tax_query->queries  =   $preserved_tax_data;
                        }
                        
                    //expect 1 elements
                    if(count($tax_data) !==  1)
                        return $query;
                        
                    //check if nested
                    $found_nested   =   TRUE;
                    reset($tax_data);
                    foreach(current($tax_data)  as  $data)
                        {
                            if(!is_array($data))
                                $found_nested   =   FALSE;
                        }
                        
                    if($found_nested    === FALSE)
                        return $query;

                    //if multiple items in nested, ingore
                    if(count(current($tax_data))    !== 1)
                        return $query;
                        
                    $root_key   =   key($tax_data);
                    
                    $nested_data    =   current($tax_data);
                    reset($nested_data);
                    
                    $query->tax_query->queries[ $root_key ] =  current($nested_data);
                    
                    return $query;
                        
                }
                
                
            function woocommerce_query_filter_valid_data( $query )
                {
                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                    if  ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) )
                        return $query;
                        
                    //only WooCommerce 3.0 and up
                    global $woocommerce;
                    if( version_compare( $woocommerce->version, '3.0', "<" ) ) 
                        return $query;
                        
                    //we ned 2 taxo arrays
                    $query->tax_query->queries   =   self::woocommerce_query_filter_process ( $query->tax_query->queries );
                    
                    
                    return $query;
                        
                }
                
                
            function woocommerce_apto_query_filter_valid_data( $query )
                {
                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                    if  ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) )
                        return $query;
                        
                    //only WooCommerce 3.0 and up
                    if (  ! APTO_functions::check_backtrace_for_caller( array ( array ( 'get_products', 'WC_Product_Query') ) ) )
                        return $query;
                    
                    $tax_queries    =   $query->tax_query->queries;
                    $tax_queries_count  =   0;
                    foreach( $tax_queries   as  $key    =>  $data )
                        {
                            if ( ! is_array ( $data ) ||    ! isset ( $data['taxonomy'] ) )
                                continue;
                                
                            $tax_queries_count++;
                            
                            if ( $data['taxonomy']  ==  'product_type' )
                                unset ( $tax_queries[ $key ] );
                        }
                        
                    if ( $tax_queries_count ==  2 )
                        $query->tax_query->queries  =   $tax_queries;    
                    
                    return $query;
                        
                }
                
                
            function woocommerce_apto_order_update_complete( $sort_view_id )
                {
                    
                    //ensure WooCommerce is active
                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                    if  ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) )
                        return;
                     
                    global $APTO;
                    
                    $sort_view_data     =   get_post($sort_view_id);
                    if($sort_view_data->post_parent > 0)
                        $sortID             =   $sort_view_data->post_parent;
                        else
                        $sortID             =   $sort_view_id;
                        
                    $sort_settings          =   $APTO->functions->get_sort_settings($sortID);
                    $sort_post_types        =   isset($sort_settings['_rules']['post_type']) ?  $sort_settings['_rules']['post_type']   :   array();
                    
                    //continue only if post type rules is 'product'
                    if( count($sort_post_types) !==    1    ||  array_search('product', $sort_post_types)   === FALSE )
                        return;
                                 
                    global $wpdb;
                    
                    $mysql_query            =   "DELETE FROM " . $wpdb->options . "
                                                    WHERE `option_name` LIKE '%_wc_loop%'";
                    $results                =   $wpdb->get_results( $mysql_query );
                    
                    $mysql_query            =   "DELETE FROM " . $wpdb->options . "
                                                    WHERE `option_name` LIKE '%_wc_product_loop%'";
                    $results                =   $wpdb->get_results( $mysql_query );
                        
                }
                

            function woocommerce_cat_order_query_filter_valid_data( $query )
                {
                    
                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                    if  ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) )
                        return $query;
                        
                    global $APTO;
                            
                    $site_settings          =   $APTO->functions->get_settings();
                    
                    if( $site_settings['woocommerce_apply_sort_when_using_filters']   !=  '1'    )
                        return $query;
                        
                    $tax_data   =   $query->tax_query->queries;
                    
                    if(isset($tax_data['relation']))
                        unset($tax_data['relation']);
                    
                    $found  =   FALSE;    
                    //ensure the query include a product_cat taxonomy
                    foreach( $tax_data  as  $key    =>  $item_tax_data )
                        {
                            if ( !isset ( $item_tax_data['taxonomy'] ) )
                                return $query;
                                
                            if ( $item_tax_data['taxonomy']  !=  'product_cat' )
                                continue;
                            
                            if( count((array)$item_tax_data['terms'])    !=  1 )
                                return $query;
                        
                            $found  =   TRUE;
                        }
                        
                    if ( $found === FALSE )
                        return $query;
                        
                    //unest all other taxonomies
                    foreach ( $query->tax_query->queries    as  $key    =>  $item_tax_data )
                        {
                            if ( ! is_int($key) )
                                continue;
                                
                            if ( $item_tax_data['taxonomy']  !=  'product_cat' )
                                unset ( $query->tax_query->queries[$key] );
                            
                        }
                    
                    return $query;
                    
                }
                
                
            static public function woocommerce_query_filter_process( $queries )
                {
                    $tax_data   =   $queries;
                    
                    if(isset($tax_data['relation']))
                        unset($tax_data['relation']);
                    
                    /**
                    * We expect 2 txonomies, product_visibility and a custom one
                    */
                          
                    //we need product_cat and product_visibility taxonomy
                    //format is taxonomy => number of terms it should expect to be found, or false to replace
                    $search_for_taxonomies  =   array();
                    $product_taxonomies                          =   get_object_taxonomies( 'product');
                    foreach ($product_taxonomies as  $key    =>  $product_taxonomy)
                        {
                            $search_for_taxonomies[ $product_taxonomy ] =   1;
                        }
                    $search_for_taxonomies['product_visibility']    =   FALSE;
                    
                    $search_for_taxonomies  =   apply_filters( 'apto/query_filter_valid_data/woocommerce_taxonomies', $search_for_taxonomies, $queries );
                    
                    foreach($tax_data   as  $key    =>  $item_tax_data)
                        {
                            if ( ! is_array ( $item_tax_data )  ||  ! isset ( $item_tax_data['taxonomy'] ) )
                                continue;
                                
                            if(isset($search_for_taxonomies[ $item_tax_data['taxonomy'] ]))
                                {
                                    $expected_terms =   $search_for_taxonomies[ $item_tax_data['taxonomy'] ];
                                    if($expected_terms  !== FALSE)
                                        {
                                            if(count((array)$item_tax_data['terms'])    !=  $expected_terms)
                                                return $queries;
                                        }
                                        
                                    unset( $tax_data[ $key ] );
                                }
                            
                        }
                    
                    //we expect an empty array
                    if ( count($tax_data)   !== 0 )
                        return $queries;
                        
                    //At this point we are sure is the query we looking for. Unset the product_visibility
                    foreach($queries   as  $key    =>  $item_tax_data)
                        {
                            if(is_array($item_tax_data) &&  isset($item_tax_data['taxonomy'])   &&  $item_tax_data['taxonomy']   ==  'product_visibility')
                                {
                                     unset($queries[$key]);
                                }
                        }
                        
                    return $queries;    
                }
                
                
            static public function woocommerce_query_filter_visibility( $tax_query )
                {
                    if ( ! is_array ( $tax_query )  ||  count ( $tax_query )    <   1  )
                        return $tax_query;
                        
                    foreach  ( $tax_query   as  $key    =>  $tax_query_item )
                        {
                            if ( ! is_array ( $tax_query_item ) ||  ! isset ( $tax_query_item['taxonomy'] ) )
                                continue;
                                
                            
                            if ( $tax_query_item['taxonomy']    ==  'product_visibility' )
                                {
                                    unset ( $tax_query[ $key ] );
                                    break;
                                }
                            
                        }
                        
                    return $tax_query;
                    
                }
                
        }
        
        
    new APTO_compatibility_woocommerce();
        
        
