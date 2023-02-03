<?php


    class APTO_Polylang
        {
            
            function __construct()
                {
                
                    add_filter('apto/admin/sort-taxonomies',                    array($this, 'apto_admin_sort_taxonomies'), 10, 2);
                    add_filter('apto/query-utils/get_tax_queries',              array($this, 'apto_query_utils_get_tax_queries'), 10, 2);
                    
                    add_filter('apto/default_interface_sort/filtered_query',    array($this, 'apto_default_interface_sort_filtered_query'));
                                    
                }
                
                
                
            function apto_admin_sort_taxonomies( $taxonomies, $sortID )
                {
                    
                    //replace the language taxonomy
                    foreach ( $taxonomies   as  $key    =>  $taxonomy)
                        {
                            
                            if(in_array($taxonomy, array('language', 'post_translations')))
                                unset($taxonomies[$key]);
                            
                        }
                        
                    $taxonomies =   array_values($taxonomies);
                    
                    return $taxonomies;
                       
                }   
            
            
            function apto_query_utils_get_tax_queries($filtred_queries, $args)
                {
                    
                    //filter out the language taxonomy if Polylang
                    if(!isset($args['clean_polylang_queries'])  ||  (isset($args['clean_polylang_queries'])   &&  $args['clean_polylang_queries'] !== FALSE))
                        {
                            foreach($filtred_queries    as  $key    =>  $filtred_query)
                                {
                                    if($filtred_query['taxonomy']   ==  'language')
                                        unset($filtred_queries[$key]);
                                }
                                
                            $filtred_queries    =   array_values($filtred_queries);
                        }
                        
                    return $filtred_queries;
                       
                }
                
                
            /**
            * return active languages
            * 
            */
            static function get_languages()
                {
                    $languages = pll_languages_list();
                    
                    return $languages;    
                    
                }
                
                
            /**
            * Check if the post type is translatable or not
            * 
            * @param mixed $post_type
            */
            static function is_translatable_post_type( $post_type )
                {
                    
                    $is_translatable    =   pll_is_translated_post_type( $post_type );
                    
                    return $is_translatable;
                       
                }
                
            /**
            * Check if taxonomy is translatable or not
            * 
            * @param mixed $post_type
            */
            static function is_translatable_taxonomy( $taxonomy )
                {
                    
                    $is_translatable    =   pll_is_translated_taxonomy( $taxonomy  );
                    
                    return $is_translatable;
                       
                }
                
                
            static function translate_sort_rules($rules, $language_code)
                {
                    $translated_rules   =   $rules;

                    $translated_rules['taxonomy']   =   array();

                    foreach($rules['taxonomy']  as  $key    =>  $taxonomy_data)
                        {
                            $translated_taxonomy_data   =   $taxonomy_data;
                            $translated_taxonomy_data['terms']   =   array();
                            
                            foreach($taxonomy_data['terms'] as  $term_id)
                                {
                                    $term_id_translation    =   pll_get_term($term_id, $language_code);
                                    if(empty($term_id_translation))
                                        {
                                            $translated_rules   =   FALSE;
                                            break 2;
                                        }
                                        
                                    $translated_taxonomy_data['terms'][]    =   $term_id_translation;
                                }
                                
                            $translated_rules['taxonomy'][$key] =   $translated_taxonomy_data;
                        }
                    
                    return $translated_rules;
                       
                }
                
                
            /**
            * Attempt to create a list of translated objects
            * 
            * @param mixed $data_list
            * @param mixed $sortID
            * @param mixed $sort_view_id
            */
            static function translate_objects_to_language($data_list, $lang_code)
                {
                    $translate_list =   array();
                    
                    foreach($data_list as $post_id => $parent_id)   
                        {
                            $post_data  =   get_post($post_id);
                            
                            $translated_post_id =   pll_get_post( $post_id, $lang_code );
                            
                            //check if translated
                            if($translated_post_id < 1)
                                return FALSE;
                            
                            $translate_list[$translated_post_id]    =   "null";
                        }
                    
                    return $translate_list;
                }
                
                
            static function translate_sticky_list($_data_sticky, $data_list, $translated_objects)
                {
                    $lang_data_sticky   =   array();   
                    
                    if(count($_data_sticky) < 1)
                        return $lang_data_sticky;
                        
                    $translated_objects_keys    =   array_keys($translated_objects);
                        
                    foreach($_data_sticky   as  $position   =>  $_data_sticky_item)
                        {
                            $key    =   array_search($_data_sticky_item, array_keys($data_list));
                            $lang_data_sticky[$position] =   $translated_objects_keys[$key];
                        }
                    
                    return $lang_data_sticky;
                }
                
                
            function apto_default_interface_sort_filtered_query ( $wp_query ) 
                {
                    
                    $queries      =   $wp_query->tax_query->queries;
                    
                    foreach( $queries    as  $key    =>  $query )
                        {
                            if( ! isset( $query['taxonomy'] )   ||  $query['taxonomy']  !=  'language')
                                continue;
                                
                            unset( $queries[$key] );
                            
                        }
                        
                    $wp_query->tax_query->queries   =   $queries;
                        
                    return $wp_query;
                       
                }
            
            
        }


    new APTO_Polylang();



?>