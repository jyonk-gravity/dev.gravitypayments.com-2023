<?php

    
    /**
    * 
    * Walker_CategoryDropdown extension for sort area Taxonomy selections
    * 
    */
    class APTO_Walker_TaxonomiesTermsDropdownCategories extends Walker_CategoryDropdown
        {
            function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
                    $pad = str_repeat('&nbsp;', $depth * 2);
                    $cat_name = apply_filters('list_cats', $category->name, $category);
                                        
                    global $wpdb;
                    
                    $sort_settings  =   APTO_functions::get_sort_settings( $args['sortID'] );
                    
                    $object_ids =   array();
                    
                    if ( isset ( $sort_settings['_rules'] ) &&  isset ( $sort_settings['_rules']['post_type'] )   &&  count ( $sort_settings['_rules']['post_type'] ) > 0 )
                        {
                            $post_type  = "'" . implode( "', '", array_map( 'esc_sql', $sort_settings['_rules']['post_type'] ) ) . "'";
                            $taxonomy   = "'" . implode( "', '", array_map( 'esc_sql', (array)$args['taxonomy'] ) ) . "'";
                            $term_id    = "'" . implode( "', '", (array)$category->term_id ) . "'";
                            $sql        = "SELECT tr.object_id FROM $wpdb->term_relationships AS tr 
                                                INNER JOIN $wpdb->term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id 
                                                INNER JOIN $wpdb->posts AS p ON tr.object_id = p.ID
                                                
                                                WHERE tt.taxonomy IN ($taxonomy) AND tt.term_id IN ($term_id) AND p.post_type IN ($post_type)";
                            $object_ids = $wpdb->get_col( $sql );
                        }
          
                    $link_argv  =   array(
                                            'sort_id'           =>  $args['sortID'],
                                            'taxonomy'          =>  $category->taxonomy,
                                            'term_id'           =>  $category->term_id
                                            );
                    
                    if($args['apto_interface']->is_shortcode_interface === FALSE)
                        {
                            $link_argv['page'] =   'apto_' . $args['apto_interface']->interface_helper->get_current_menu_location_slug();
                            $value  =    $args['apto_interface']->interface_helper->get_tab_link($link_argv) ;
                        }
                        else
                        {
                            global $post;
                            $link_argv['base_url']      =   get_permalink($post->ID);
                            $value  =    $args['apto_interface']->interface_helper->get_item_link($link_argv) ;                            
                        }

                    $output .= "\t<option class=\"level-$depth\" value=\"" .$value."\"";
                    if ( (int)$category->term_id === (int) $args['selected'] )
                        { 
                            $output .= ' selected="selected"';
                        }
                    $output .= '>';
                    $output .= $pad . $cat_name;
                    
             
                    if ( $args['show_count'] )
                        $output .= '&nbsp;&nbsp;('. count( $object_ids ) .')';
             

                    $output .= "</option>\n";
                }
        }
        


?>