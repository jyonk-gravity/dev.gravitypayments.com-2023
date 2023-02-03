<?php
    
    //woocomerce archive fix
    add_action ('apto/order_updated', 'wooc_apto_order_update_hierarchical', 10, 3 );
    function wooc_apto_order_update_hierarchical( $sortID, $sort_view_id, $data )
        {
            global $wpdb, $blog_id;
                       
            //return if not woocommerce
            if (APTO_functions::is_woocommerce($sortID) === FALSE )
                return;
            
            extract( $data);    
            
            $sort_view_settings =   APTO_functions::get_sort_view_settings($sort_view_id);
            
            //only for parents
            if ( ($is_hierarhical === TRUE   ||  $is_woocommerce_archive ) &&  ( $sort_view_settings['_view_selection'] == 'archive' ||  $sort_view_settings['_view_selection'] == 'simple') )
                {
                    // Clear product specific transients
                    $post_transients_to_clear = array(
                                                        //old field name
                                                        '_transient_wc_product_children_ids_',
                                                        
                                                        //new field name
                                                        '_transient_timeout_wc_product_children_'
                                                    );

                    foreach( $post_transients_to_clear as $transient ) 
                        {
                            $wpdb->query( $wpdb->prepare( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE %s", $transient . '%' ) );
                        }

                    //clean_post_cache( $data['post_id'] );
                }
        }
        
    //woocommerce grouped / simple icons
    add_filter ('apto_reorder_item_additional_details', 'wooc_apto_reorder_item_additional_details', 10, 2);
    function wooc_apto_reorder_item_additional_details($additiona_details, $post_data)
        {
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            
            if ($post_data->post_type != "product" || ! is_plugin_active( 'woocommerce/woocommerce.php' ) )
                return $additiona_details;
            
            //to be updated
                            
            return $additiona_details;
        }
    
    /**
    * Unnest query if WooCommerce. This is created by product_category shortcode
    * Conditions: only if nested include a single tax array
    * 
    * Why they do that ?!
    */
    add_filter('apto/query_filter_valid_data', 'woocommerce_replace_nested_query', 5);
    function woocommerce_replace_nested_query( $query )
        {
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            if  ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) )
                return $query;

            if(empty($query->query_vars['post_type']))
                return $query;
                
            if( (is_string($query->query_vars['post_type'])   &&  $query->query_vars['post_type']   !=  'product' )
                    ||
                (is_array($query->query_vars['post_type'])   &&  ( count($query->query_vars['post_type']) > 1 ||  count($query->query_vars['post_type']) < 1 ||   $query->query_vars['post_type'][0]   !=  'product' ))
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
    
    
    //WooCommerce 3.0 and up fix
    add_filter('apto/query_filter_valid_data', 'woocommerce_query_filter_valid_data');
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
            $query->tax_query->queries   =   woocommerce_query_filter_process ( $query->tax_query->queries );
            
            
            return $query;
                
        }
        
        
    function woocommerce_query_filter_process( $queries )
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
 
 
    //WooCommerce allow sort to apply for wc_get_products()
    add_filter('apto/query_filter_valid_data', 'woocommerce_apto_query_filter_valid_data');
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
    
 
    
    /**
    * Clear the woocommerce shortcodes cache data on a re-order update
    */
    //add_action('apto/default-interface/order_update_complete', 'woocommerce_apto_order_update_complete');
    add_action('apto/reorder-interface/order_update_complete', 'woocommerce_apto_order_update_complete');
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
        
        
        
    /**
    * Apply WooCommerce category order while using visual attributes filtering    
    */
    add_filter('apto/query_filter_valid_data', 'woocommerce_cat_order_query_filter_valid_data', 99);
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
 
        
    //ignore the gallery edit images order which is set locally, independent from images archvie order
   // add_filter('ajax_query_attachments_args', 'apto_ajax_query_attachments_args', 99);
    function apto_ajax_query_attachments_args($query)
        {
            //ensure not a happyfiles_category tax
            if ( isset ( $query['tax_query'] ) )
                {
                    $found = FALSE;
                    
                    foreach ( $query['tax_query'] as $item )
                        {
                            if ( is_array ( $item ) &&  isset ( $item['taxonomy'] ) &&  $item['taxonomy']   ==  'happyfiles_category' )
                                $found = TRUE;
                        }
                        
                    if ( $found === TRUE )
                        return $query;
                    
                }
            
            $query['ignore_custom_sort'] = TRUE;

            return $query;    
        }
        
    //Shopp plugin compatibility    
    add_filter('shopp_collection_query', 'apto_shopp_collection_query');
    function apto_shopp_collection_query($options)
        {
            $orderby = shopp_setting('product_image_orderby');
            if($orderby !=  "sortorder")
                return $options;
            
            //create a csutom query then use the results as order
            $argv =     array(
                                'post_type'         =>  'shopp_product',
                                'posts_per_page'    =>  -1,
                                'fields'            =>  'ids'
                                );
            
            if(isset($options['joins']['wp_term_taxonomy']))
                {
                    preg_match('/.*tt.term_id=([0-9]+)?.*/i', $options['joins']['wp_term_taxonomy'], $matches);
                    if(isset($matches[1]))
                        {
                            $term_id = $matches[1];
                            
                            $argv['tax_query'] = array(
                                                            array(
                                                                'taxonomy' => 'shopp_category',
                                                                'field'    => 'term_id',
                                                                'terms'    => array($term_id),
                                                            ),
                                                        );    
                        }
                }
                
            $custom_query   =   new WP_Query($argv);
            if(!$custom_query->have_posts())
                return $options;    
            
            $posts_list =    $custom_query->posts;
            
            if(count($posts_list) > 0)
                {
                    global $wpdb; 
                    
                    $options['orderby'] =   " FIELD(p.ID, ". implode(",", $posts_list) .") ASC";
                    
                }
            
            return $options;
        }
    
        
    /**
    * Turn off the custom sorting when using "YITH WooCommerce Ajax Search Premium"  on AJAX calls
    */
    add_filter( 'ywcas_query_arguments', 'apto_ywcas_query_arguments', 99, 2 );
    function apto_ywcas_query_arguments( $args, $search_key )
        {
            
            $args['ignore_custom_sort']   = TRUE;
            
            return $args;
                
        }
        
        
    /**
    * FacetWP Fix
    * For some reasons the WP_Query created by FacetWP is set as search TRUE, creating issues for other plugins including this
    * 
    */
    add_filter('facetwp_query_args', 'theme_facetwp_query_args', 99, 2);
    function theme_facetwp_query_args( $query_args, $class )
        {
            if ( isset ( $query_args['s'] )  &&  empty ( $query_args['s']  ) )
                unset ( $query_args['s'] );
            
            return $query_args;   
        }
        
        
    /**
    * Unset tm_global_cp post type when the WooCommerce TM Extra Product Options plugin is active    
    * 
    */
    add_filter ('apto/query_get_post_types', '_tm_woo_extra_product_options_apto_query_get_post_types', 99, 3 );
    function _tm_woo_extra_product_options_apto_query_get_post_types( $query_post_types, $query, $_if_empty_set_post_types )
        {
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            
            if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) || ! is_plugin_active( 'woocommerce-tm-extra-product-options/tm-woo-extra-product-options.php' ) )
                return $query_post_types;    
            
            if  ( is_array( $query_post_types ) &&  count ( $query_post_types ) == 2 && array_search( 'product', $query_post_types ) !== FALSE &&   array_search( 'tm_global_cp', $query_post_types ) !== FALSE )
                {
                    unset (  $query_post_types[ array_search( 'tm_global_cp', $query_post_types ) ] );
                }
            
            return $query_post_types;   
        }
    
    add_filter( 'apto/ignore_get_orderby' , '_wpdm_sort_apply', 10, 3 );
    function _wpdm_sort_apply( $do_ignore_order, $orderBy, $query )
        {
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            
            if ( ! is_plugin_active( 'wpdm-extended-shortcodes/wpdm-extended-shortcodes.php' ) )
                return $do_ignore_order;
            
            //not for admin
            if  ( strpos( $_SERVER['REQUEST_URI'], 'wp-admin' ) !== FALSE )
                return $do_ignore_order;
            
            $do_ignore_order    =   FALSE;
               
            return $do_ignore_order;
               
        }
        
        
    add_filter( 'apto/get_post_types' , '_apto_get_post_types');
    function  _apto_get_post_types ( $all_post_types )
        {
            
            if ( is_plugin_active( 'woocommerce-product-addons/woocommerce-product-addons.php' ) )
                {
                    if (isset ( $all_post_types['global_product_addon'] ) )
                        unset ( $all_post_types['global_product_addon'] );
                }
            
            return $all_post_types;   
        }

?>