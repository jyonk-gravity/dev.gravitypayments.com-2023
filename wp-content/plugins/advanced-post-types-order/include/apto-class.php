<?php

    class APTO
        {
            var $functions  =   '';
            var $conditional_rules  = '';
            
            private $cache      =   array();
            
            var $licence;
            
            function init()
                {
                    $this->functions            =   new APTO_functions();
                    $this->licence              =   new APTO_licence();
                                        
                    //setup conditional on a later fitler to allow custom code within theme to run
                    add_action('after_setup_theme', array($this, 'after_setup_theme'), 99);
                                        
                    //set the start session log
                    $this->create_start_log(); 
                    
                    $this->setup_debug_marks(); 
                    
                    $this->_init_rest();                   
                }
                
            
            /**
            * Add REST endpoints
            *                 
            */
            function _init_rest()
                {
                    //prepare rest data
                    new APTO_rest();   
                    
                }
            
            
                
            function after_setup_theme()
                {
                    $this->conditional_rules    =   new APTO_conditionals();
                }
                
            function pre_get_posts($query)
                {
                    //Check for the ignore_custom_sort argument. Usefull when Autosort is ON                        
                    if (isset($query->query_vars['ignore_custom_sort']) && $query->query_vars['ignore_custom_sort'] === TRUE)
                        return $query;
                        
                    $settings = $this->functions->get_settings();
                    if (is_admin() && !defined('DOING_AJAX'))
                        {
                            //no need if it's admin interface
                            return $query;   
                        }
                    
                    
                    //force a suppress filters false, used mainly for get_posts function
                    if (isset($settings['ignore_supress_filters']) && $settings['ignore_supress_filters'] == "1")
                        $query->query_vars['suppress_filters'] = FALSE;
                     
                    //ignore the sticky if setting set for true
                    if (isset($settings['ignore_sticky_posts']) && $settings['ignore_sticky_posts'] == "1")
                        {
                            $query->query_vars['ignore_sticky_posts'] = TRUE;
                        }
                         
                    if($this->functions->exists_sorts_with_autosort_on() === FALSE)
                        return $query;
                    
                    //remove the supresed filters;
                    if (isset($query->query['suppress_filters']))
                        $query->query['suppress_filters'] = FALSE;    
                    
                    

                    return $query;
                }

            function posts_orderby($orderBy, $query) 
                {
                    //check for single object query on which no sort apply is necesarelly
                    if(
                            (
                                $query->is_single() || ( $query->is_page() && ! $query->is_archive() ) || $query->is_singular() || $query->is_preview() || $query->is_attachment()
                            )
                        )
                        return $orderBy; 
                    
                    //check for orderby GET paramether in which case return default data
                    if ( ! defined('DOING_AJAX') && isset($_GET['orderby']) && $_GET['orderby'] !=  'menu_order' && apply_filters('apto/ignore_get_orderby', TRUE, $orderBy, $query ) )
                        return $orderBy;
                                            
                    if (isset($query->query_vars['ignore_custom_sort']) && $query->query_vars['ignore_custom_sort'] === TRUE)
                        return $orderBy;
                          
                    if (apto_is_plugin_active('bbpress/bbpress.php') && isset($query->query_vars['post_type']) && ((is_array($query->query_vars['post_type']) && in_array("reply", $query->query_vars['post_type'])) || ($query->query_vars['post_type'] == "reply")))
                        return $orderBy;
                                           
                    if( apply_filters('apto/ignore_custom_order', FALSE, $orderBy, $query) )
                        return $orderBy;
                    
                    global $wpdb;
                                                    
                    //check if menu_order provided through the query params
                    if (    ( isset($query->query['orderby']) && $query->query['orderby'] == 'menu_order' )  ||  ( isset($query->query_vars['orderby']) && $query->query_vars['orderby'] == 'menu_order' ) )
                        {
                            $orderBy    =   $this->functions->query_get_orderby($orderBy, $query);
                                
                            return($orderBy);   
                        }

                    //ignore search
                    if( $query->is_search()  &&  isset( $query->query['s'] )   &&  ! empty ( $query->query['s'] )   &&  apply_filters('apto/ignore_search', TRUE, $orderBy, $query ) )
                        return( $orderBy );
                        
                    $default_orderBy = $orderBy;

                    if (is_admin() && !defined('DOING_AJAX'))
                            {

                                //force to use the custom order
                                //$orderBy = $wpdb->posts.".menu_order, " . $wpdb->posts.".post_date DESC"; 
                                $args   =   array(
                                                    '_adminsort' =>  array('yes')  
                                                    );
                                $orderBy    =   $this->functions->query_get_orderby($orderBy, $query, $args);
                                
                                if($orderBy == '')
                                    $orderBy = $default_orderBy;

                            }
                        else
                            {
                             
                                //check against any Autosort On sort list                    
                                $args   =   array(
                                                            '_autosort' =>  array('yes')  
                                                            );
                                $orderBy    =   $this->functions->query_get_orderby($orderBy, $query , $args);

                                return($orderBy);

                            }

                    return($orderBy);
                }
                
                
            function APTO_posts_groupby($groupby, $query) 
                {
                    
                    if (isset($query->query_vars['ignore_custom_sort']) && $query->query_vars['ignore_custom_sort'] === TRUE)
                        return $groupby;
                    
                    //not for search queries
                    if( $query->is_search() )
                        return $groupby;
                    
                    //check for NOT IN taxonomy operator
                    if(isset($query->tax_query->queries) && APTO_query_utils::tax_queries_count($query->tax_query->queries) == 1 )
                        {
                            if(isset($query->tax_query->queries[0]['operator']) && $query->tax_query->queries[0]['operator'] == 'NOT IN')
                                $groupby = '';
                        }
                       
                    return $groupby;
                    
                }
                
            function APTO_posts_distinct($distinct, $query) 
                {
                   
                    if (isset($query->query_vars['ignore_custom_sort']) && $query->query_vars['ignore_custom_sort'] === TRUE)
                        return $distinct;
                    
                    //check for NOT IN taxonomy operator
                    if(isset($query->tax_query->queries) && APTO_query_utils::tax_queries_count($query->tax_query->queries) == 1 )
                        {
                            if(isset($query->tax_query->queries[0]['operator']) && $query->tax_query->queries[0]['operator'] == 'NOT IN')
                                $distinct = 'DISTINCT';
                        }
                           
                    return($distinct);
                }
                
            
            /**
            * Add to internal cache
            * 
            * The key can be a single string key or as path for multidimensional
            *   e.g.  single_key
            *   e.g.  parent_key/child_keye
            * 
            * @param mixed $key
            * @param mixed $value
            */
            function cache_add_key( $key, $value )
                {
                    
                    $key_path   =   $this->cache_get_key_path( $key );
                    
                    $cache_path_data =  &$this->cache;
                    
                    foreach ($key_path  as  $key )
                        {
                            if  ( ! is_array($cache_path_data))
                                {
                                    settype( $cache_path_data , 'array' );
                                    $cache_path_data    =   array_filter($cache_path_data);
                                }
                            
                            if ( ! isset( $cache_path_data[ $key ]))
                                $cache_path_data[ $key ]    =   '';
                                
                            $cache_path_data    =   &$cache_path_data[ $key ];
                        }
                    
                    $cache_path_data    =   $value;
                    
                    unset ( $cache_path_data );
                       
                }
                
                
            
            /**
            * Get key from internal cache if exists
            * The key can be a single string key or as path for multidimensional
            *   e.g.  single_key
            *   e.g.  parent_key/child_keye
            * 
            * @param mixed $key
            */
            function cache_get_key( $key )
                {
                    
                    $key_path   =   $this->cache_get_key_path( $key );
                    
                    $cache_path_data =  $this->cache;
                    foreach ($key_path  as  $key )
                        {
                            if ( ! isset( $cache_path_data[ $key ]))
                                return FALSE;
                                
                            $cache_path_data    =   $cache_path_data[ $key ];
                        }
                        
                    return $cache_path_data;   
                    
                }
                
                
            /**
            * Check if the key exists within internal cache
            * The key can be a single string key or as path for multidimensional
            *   e.g.  single_key
            *   e.g.  parent_key/child_keye
            * 
            * @param mixed $key
            */
            function cache_key_exists( $key )
                {
                    
                    $key_path   =   $this->cache_get_key_path( $key );
                    
                    $cache_path_data =  $this->cache;
                    foreach ($key_path  as  $key )
                        {
                            if ( ! isset( $cache_path_data[ $key ]))
                                return FALSE;
                                
                            $cache_path_data    =   $cache_path_data[ $key ];
                        }
                        
                    return TRUE;
                }
                
                
                
            /**
            * Delete a key from internal cache
            * The key can be a single string key or as path for multidimensional
            *   e.g.  single_key
            *   e.g.  parent_key/child_keye
            * 
            * @param mixed $key
            */
            function cache_delete_key( $key )
                {
                    
                    $key_path   =   $this->cache_get_key_path( $key );
                    
                    $cache_path_data =  &$this->cache;
                    foreach ($key_path  as  $key )
                        {
                            if ( ! isset( $cache_path_data[ $key ]))
                                return FALSE;
                                
                            $cache_path_data    =   &$cache_path_data[ $key ];
                        }
                        
                    unset ( $cache_path_data[ $key ] );    
                    
                }
                
                
                
            /**
            * Extract the path from the proikey
            * Used Internally
            * The key can be a single string key or as path for multidimensional
            *   e.g.  single_key
            *   e.g.  parent_key/child_keye 
            * 
            * @param mixed $key
            */
            private function cache_get_key_path ( $key ) 
                {
                    
                    $path   =   explode( '/', $key );
                    
                    return $path;
                    
                }
                  
                
            function create_start_log()
                {
                    $settings   =   $this->functions->get_settings();
                    if (!isset($settings['create_logs']) || $settings['create_logs'] != "1")
                        return FALSE;
                    
                    $this->functions->save_log('log_start', array());
                }
                
            
            /**
            * Load the debug marks dependencies
            *     
            */
            function setup_debug_marks ()
                {
                    $settings   =   $this->functions->get_settings();
                    if (!isset($settings['debug_marks']) || $settings['debug_marks'] != "1")
                        return FALSE;    
                    
                    if ( is_admin() ||  wp_doing_ajax() ||  count( $_POST ) > 0 || wp_is_json_request()  )
                        return FALSE;
                        
                    if  (  ! is_user_logged_in() )
                        return FALSE;
                        
                    include_once(APTO_PATH . '/include/apto_debug_marks-class.php');
                    
                }
            
            
        }

?>