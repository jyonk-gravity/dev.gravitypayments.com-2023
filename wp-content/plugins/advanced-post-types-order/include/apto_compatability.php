<?php
    
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    
    class APTO_compatibility
        {
            
            function __construct()
                {
                    
                    if ( ! function_exists( 'is_plugin_active' ) )
                        require_once ABSPATH . 'wp-admin/includes/plugin.php';
                    
                    //woocomerce archive fix
                    add_action ('apto/order_updated',                           array ( $this, 'wooc_apto_order_update_hierarchical' ), 10, 3 );
                    
                    //woocommerce grouped / simple icons
                    add_filter ('apto_reorder_item_additional_details',         array ( $this, 'wooc_apto_reorder_item_additional_details' ), 10, 2);
                    

                    
                    //ignore the gallery edit images order which is set locally, independent from images archvie order
                    // add_filter('ajax_query_attachments_args',                array ( $this, 'apto_ajax_query_attachments_args' ), 99);
                    
                    //Shopp plugin compatibility    
                    add_filter('shopp_collection_query',                        array ( $this, 'apto_shopp_collection_query' ) );
                    
                    /**
                    * Turn off the custom sorting when using "YITH WooCommerce Ajax Search Premium"  on AJAX calls
                    */
                    add_filter( 'ywcas_query_arguments',                        array ( $this, 'apto_ywcas_query_arguments' ), 99, 2 );
                           
                    
                    /**
                    * Unset tm_global_cp post type when the WooCommerce TM Extra Product Options plugin is active    
                    * 
                    */
                    add_filter ('apto/query_get_post_types',                    array ( $this, '_tm_woo_extra_product_options_apto_query_get_post_types' ), 99, 3 );
                    
                    add_filter( 'apto/ignore_get_orderby' ,                     array ( $this, '_wpdm_sort_apply' ), 10, 3 );
                    
                    add_filter( 'apto/get_post_types' ,                         array ( $this, '_apto_get_post_types' ) );
                    
                                        
                    add_action ( 'pre_get_posts',                               array ( $this, 'beaver_pre_get_posts' ), 999 );
                    
                                        
                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                    
                    if ( is_plugin_active( 'woocommerce/woocommerce.php' ) )
                        include_once( APTO_PATH . 'include/compatibility/woocommerce.php' );
                    
                    if ( is_plugin_active( 'wp-grid-builder/wp-grid-builder.php' ) )
                        include_once( APTO_PATH . 'include/compatibility/wp-grid-builder.php' );
                        
                    if ( is_plugin_active( 'jet-engine/jet-engine.php' ) )
                        include_once( APTO_PATH . 'include/compatibility/jet-engine.php' );
                        
                    if ( is_plugin_active( 'facetwp/index.php' ) )
                        include_once( APTO_PATH . 'include/compatibility/facet-wp.php' );
                        
                    if ( is_plugin_active( 'admin-columns-pro/admin-columns-pro.php' ) )
                        include_once( APTO_PATH . 'include/compatibility/admin-columns-pro.php' );
                        
                    if ( is_plugin_active( 'translatepress-business/index.php' ) || is_plugin_active( 'translatepress-multilingual/index.php' ) )
                        include_once( APTO_PATH . 'include/compatibility/translatepress.php' );
                        
                    if ( is_plugin_active( 'elementor/elementor.php' ) )
                        include_once( APTO_PATH . 'include/compatibility/elementor.php' );
                        
                        
                    if ( is_plugin_active( 'essential-grid/essential-grid.php' ) )
                        {
                            //clean the terms as essential-grid bulk serialize all...
                            add_action ( 'pre_get_posts',                               array ( $this, 'essential_grid_pre_get_posts' ), 999 );
                        }
 
                        
                    if ( is_plugin_active( 'product-extras-for-woocommerce/product-extras-for-woocommerce.php' ) )
                        {
                            add_action ( 'pewc_global_group_order',                               array ( $this, 'pewc_global_group_order' ) );
                        }
                        
                    
                    
                }
    
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
        
            function wooc_apto_reorder_item_additional_details($additiona_details, $post_data)
                {
                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                    
                    if ($post_data->post_type != "product" || ! is_plugin_active( 'woocommerce/woocommerce.php' ) )
                        return $additiona_details;
                    
                    //to be updated
                                    
                    return $additiona_details;
                }
    
            
    
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
                            $options['orderby'] =   " FIELD(p.ID, ". implode(",", $posts_list) .") ASC";  
                        }
                    
                    return $options;
                }
    
        
    
            function apto_ywcas_query_arguments( $args, $search_key )
                {
                    
                    $args['ignore_custom_sort']   = TRUE;
                    
                    return $args;
                        
                }
        
       
    
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
        
        
    
            function  _apto_get_post_types ( $all_post_types )
                {
                    
                    if ( is_plugin_active( 'woocommerce-product-addons/woocommerce-product-addons.php' ) )
                        {
                            if (isset ( $all_post_types['global_product_addon'] ) )
                                unset ( $all_post_types['global_product_addon'] );
                        }
                    
                    return $all_post_types;   
                }
                            
                
            function beaver_pre_get_posts( $object )
                {
                    if ( ! isset ( $object->query_vars['wpgb_beaver_builder'] ) )
                        return $object;
                        
                    if ( ! isset ( $object->query_vars['post_type'] ) )
                        $object->query_vars['post_type']    =   '';
                    
                    return $object;
                }
                
                
                
             /**
            * Replace a filter / action from anonymous object
            * 
            * @param mixed $tag
            * @param mixed $class
            * @param mixed $method
            * @param mixed $priority
            */
            function remove_anonymous_object_filter( $tag, $class, $method, $priority = '' ) 
                {
                    $filters = false;

                    if ( isset( $GLOBALS['wp_filter'][$tag] ) )
                        $filters = $GLOBALS['wp_filter'][$tag];

                    if ( $filters )
                    foreach ( $filters as $filter_priority => $filter ) 
                        {
                            if ( ! empty ( $priority )  &&   $priority != $filter_priority )
                                continue;
                                
                            foreach ( $filter as $identifier => $function ) 
                                {                                   
                                    if ( ! isset ( $function['function'] ) || ! is_array ( $function['function'] ) )
                                        continue;
                                    
                                    if ( is_string( $function['function'][0] )  &&  $function['function'][0]    == $class   &&  $function['function'][1]    ==  $method )
                                        remove_filter($tag, array( $function['function'][0], $method ), $filter_priority );
                                    else if ( is_object( $function['function'][0] )  &&  get_class( $function['function'][0] )    == $class   &&  $function['function'][1]    ==  $method ) 
                                        remove_filter($tag, array( $function['function'][0], $method ), $filter_priority );
                                }
                        }
                }
                
                
                
            function essential_grid_pre_get_posts( $object )
                {
                    if ( ! isset ( $object->query['tax_query'] ) || count ( $object->tax_query->queries )   <   1 )
                        return $object;
                    
                    foreach ( $object->query['tax_query']   as  $key    =>  $data )
                        {
                            $taxonomy   =   isset ( $data['taxonomy'] ) ?   $data['taxonomy']   :   FALSE;
                            $terms      =   isset ( $data['terms'] ) ?   $data['terms']   :   FALSE;
                            $field_type =   isset ( $data['field'] ) ?   $data['field']   :   'id';
                            
                            if ( $taxonomy  === FALSE   ||  $terms  === FALSE   ||  ! is_array( $terms ) )
                                continue;
                                
                            foreach ( $terms    as  $term_key   =>  $term_item )
                                {
                                    $term_data  =   get_term_by( $field_type, $term_item, $taxonomy );
                                    if ( $term_data === FALSE )
                                        unset ( $terms[ $term_key ] );
                                }
                                
                            $terms  =   array_values ( $terms );
                            
                            $object->query['tax_query'][ $key ]['terms']    =   $terms;
                        }
                        
                    foreach ( $object->query_vars['tax_query']   as  $key    =>  $data )
                        {
                            $taxonomy   =   isset ( $data['taxonomy'] ) ?   $data['taxonomy']   :   FALSE;
                            $terms      =   isset ( $data['terms'] ) ?   $data['terms']   :   FALSE;
                            $field_type =   isset ( $data['field'] ) ?   $data['field']   :   'id';
                            
                            if ( $taxonomy  === FALSE   ||  $terms  === FALSE   ||  ! is_array( $terms ) )
                                continue;
                                
                            foreach ( $terms    as  $term_key   =>  $term_item )
                                {
                                    $term_data  =   get_term_by( $field_type, $term_item, $taxonomy );
                                    if ( $term_data === FALSE )
                                        unset ( $terms[ $term_key ] );
                                }
                                
                            $terms  =   array_values ( $terms );
                            
                            $object->query_vars['tax_query'][ $key ]['terms']    =   $terms;
                        }
                        
                    foreach ( $object->tax_query->queries   as  $key    =>  $data )
                        {
                            if ( ! is_array ( $data ) )
                                continue;
                            
                            $taxonomy   =   isset ( $data['taxonomy'] ) ?   $data['taxonomy']   :   FALSE;
                            $terms      =   isset ( $data['terms'] ) ?   $data['terms']   :   FALSE;
                            $field_type =   isset ( $data['field'] ) ?   $data['field']   :   'id';
                            
                            if ( $taxonomy  === FALSE   ||  $terms  === FALSE   ||  ! is_array( $terms ) )
                                continue;
                                
                            foreach ( $terms    as  $term_key   =>  $term_item )
                                {
                                    $term_data  =   get_term_by( $field_type, $term_item, $taxonomy );
                                    if ( $term_data === FALSE )
                                        unset ( $terms[ $term_key ] );
                                }
                                
                            $terms  =   array_values ( $terms );
                            
                            $object->tax_query->queries[ $key ]['terms']    =   $terms;
                        }
                    
                    return $object;
                }
                
                
            
                
                
            /**
            * WooCommerce Product Add-Ons Ultimate 
            * Return the objects in the customised order, if set. 
            * 
            * @param mixed $global_order
            */
            function pewc_global_group_order( $global_order )
                {
                    $args   =   array(
                                        '_autosort' =>  array('yes'),
                                        '_view_type' =>  array('multiple')
                                        );
                    $available_sorts    =   APTO_functions::get_sorts_by_filters( $args );
                    
                    if ( count ( $available_sorts ) < 1 )
                        return $global_order;
                        
                    reset ( $available_sorts );
                    $sortID =   current ( $available_sorts )->ID;
                    
                    $attr = array(
                                    '_view_selection'   =>  'archive',
                                    '_view_language'    =>  APTO_functions::get_blog_language()
                                    );

                    $sort_view_id   =   APTO_functions::get_sort_view_id_by_attributes( $sortID, $attr );
                    
                    if ( empty ( $sort_view_id ) )
                        return $global_order;
                    
                    $order_list  = APTO_functions::get_order_list( $sort_view_id );
                    
                    $unsorted_objects   =   explode ( "," , $global_order );
                    
                    $common = array_intersect( $order_list, $unsorted_objects );
    
                    $diff = array_diff( $unsorted_objects, $order_list );

                    $unsorted_objects   =   array_merge( $common, $diff );
                    
                    $global_order   =   implode ( ",", $unsorted_objects );
                        
                    return $global_order;    
                }
                
                
            
                
        }
        
    new APTO_compatibility();    
    
?>