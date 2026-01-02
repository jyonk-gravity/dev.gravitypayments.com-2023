<?php

    /**
    * 
    * Post Types Order Walker Class
    * 
    */
    class Post_Types_Order_Walker extends Walker 
        {

            var $db_fields = array ('parent' => 'post_parent', 'id' => 'ID');
            var $is_woocommerce;

            /**
            * Starts the list before the elements are added.
            *
            * @see Walker::start_lvl()
            *
            * @since 3.0.0
            *
            * @param string $output Passed by reference. Used to append additional content.
            * @param int    $depth  Depth of menu item. Used for padding.
            * @param array  $args   An array of arguments. @see wp_nav_menu()
            */
            function start_lvl(&$output, $depth = 0, $args = array()) 
                {
                    extract($args, EXTR_SKIP);
                      
                    $indent = str_repeat("\t", $depth);
                    $output .= "\n$indent<". $html_list_type ." class='children'>\n";
                }

            /**
            * Ends the list of after the elements are added.
            *
            * @see Walker::end_lvl()
            *
            * @since 3.0.0
            *
            * @param string $output Passed by reference. Used to append additional content.
            * @param int    $depth  Depth of menu item. Used for padding.
            * @param array  $args   An array of arguments. @see wp_nav_menu()
            */
            function end_lvl(&$output, $depth = 0, $args = array()) 
                {
                    extract($args, EXTR_SKIP);
                           
                    $indent = str_repeat("\t", $depth);
                    $output .= "$indent</" . $html_list_type . ">\n";
                }

            /**
            * Start the element output.
            *
            * @see Walker::start_el()
            *
            * @since 3.0.0
            *
            * @param string $output Passed by reference. Used to append additional content.
            * @param object $post_info   Menu item data object.
            * @param int    $depth  Depth of menu item. Used for padding.
            * @param array  $args   An array of arguments. @see wp_nav_menu()
            * @param int    $id     Current item ID.
            */ 
            function start_el(&$output, $post_data, $depth = 0, $args = array(), $id = 0) 
                {
                    if ( $depth )
                        $indent = str_repeat("\t", $depth);
                    else
                        $indent = '';
                          
                    if ($post_data->post_type == 'attachment')
                        $post_data->post_parent = null;
                        
                    extract($args, EXTR_SKIP);
                    
                    $is_woocommerce_archive = $this->is_woocommerce_archive_list( $args['sort_id'], $args['sort_view_id'] );                
                                        
                    global $APTO;
                    $sort_settings      =   $APTO->functions->get_sort_settings($args['sort_id']);
                    $sort_view_settings =   $APTO->functions->get_sort_view_settings($sort_view_id); 
                    
                    //check post thumbnail
                    if (function_exists('get_post_thumbnail_id'))
                            {
                                if($post_data->post_type == 'attachment')
                                    $image_id = $post_data->ID;
                                    else
                                    $image_id = get_post_thumbnail_id( $post_data->ID , 'medium' );
                            }
                        else
                            {
                                $image_id = NULL;    
                            }
                    if ($image_id > 0)
                        {
                            $image = wp_get_attachment_image_src( $image_id , array(195,195)); 
                            if($image !== FALSE)
                                $image_html =  '<img src="'. $image[0] .'" alt="" />';
                                else
                                $image_html =  '<img src="'. APTO_URL .'/images/nt.png" alt="" />'; 
                        }
                        else
                            {
                                $image_html =  '<img src="'. APTO_URL .'/images/nt.png" alt="" />';    
                            }
                    
                    
                    //allow the thumbnail image to be changed through a filter
                    $image_html = apply_filters( 'apto/reorder_item_thumbnail', $image_html, $post_data->ID );
                    
                    $noNestingClass = '';
                    if(!post_type_exists($post_data->post_type))
                        $post_type_data = get_post_type_object($post_data->post_type);
                        
                    if (isset($post_type_data->hierarchical) && $post_type_data->hierarchical !== TRUE && $is_woocommerce === FALSE)
                        $noNestingClass = ' no-nesting';
                    
                    $is_sticky  =   FALSE;
                    if(isset($sort_view_settings['_sticky_data']) && is_array($sort_view_settings['_sticky_data']) && array_search($post_data->ID, $sort_view_settings['_sticky_data']) !== FALSE)
                        $is_sticky  =   TRUE;
                    
                    $output .= $indent . '<li class="'.$noNestingClass.'" id="item_'.$post_data->ID.'">';
                    
                    if($is_sticky)
                        {
                            $output .=  '<div class="a_sticky"><input type="text" onblur="APTO.sticky_change(this)" name="p_sticky_val" value="'. array_search($post_data->ID, $sort_view_settings['_sticky_data']) .'" class="sticky-input"></div>';   
                        }
                    
                    $output .=  '<div class="item';
                    
                    if($is_sticky)
                        $output .= ' is-sticky';
                    
                    $output .=  '"><div class="post_type_thumbnail"';
                    
                    if ($sort_settings['_show_thumbnails']  == 'yes')
                        $output .= ' style="display: block"';
                        
                    $output .= '>'. $image_html .'</div>';
                    
                    $item_output    =   '';
                    $item_output .= '<div class="options">';
                    
                    $option_items                   = array();
                    $option_items['move_top']       = '<span class="option move_top dashicons dashicons-arrow-up-alt2" title="'. __( "Move to Top", 'apto' ) .'"></span>';
                    $option_items['move_bottom']       = '<span class="option move_bottom dashicons dashicons-arrow-down-alt2" title="'. __( "Move to Bottom", 'apto' ) .'"></span>';
                    $option_items['sticky']       = '<span class="option sticky dashicons dashicons-admin-post" title="'. __( "Make Sticky", 'apto' ) .'"></span>';
                    $option_items['edit']       = '<a target="_blank" href="' . admin_url( 'post.php?post='.$post_data->ID.'&action=edit' ) .'"><span class="option sticky dashicons dashicons-edit" title="'. __( "Edit", 'apto' ) .'"></span></a>';
                    
                    $option_items                   = apply_filters('apto/reorder_item_additional_options', $option_items, $post_data );
                    
                    $item_output .= implode(" ", $option_items);
                    
                    $item_output .= '</div>';
                    
                    
                    $item_output .= '<span class="i_description"><i>';
                    
                    $additiona_details  =   apply_filters( 'the_title', $post_data->post_title, $post_data->ID )    .   ' ('.$post_data->ID.')';
                    
                    $additiona_details  = apply_filters('apto/reorder_item_additional_details', $additiona_details, $post_data);
                    
                    $item_output        .= $additiona_details;
                    
                    if ($post_data->post_status != 'publish')
                        $item_output .= ' <span class="item-status">'.$post_data->post_status.'</span>';
                        
                    $sticky_list = get_option('sticky_posts');
                    
                    if(is_array($sticky_list) && count($sticky_list) > 0)
                        {
                            if(in_array($post_data->ID, $sticky_list))
                                $item_output .= ' <span class="item-status">'. __( "Sticky", 'apto' ) .'</span>';
                        }
                     
                    $item_output .= '</i></span>';
                    
                    
                    
                    $item_output .= '</div>';
                    
                    $output .= $item_output;
                }

            /**
            * Ends the element output, if needed.
            *
            * @see Walker::end_el()
            *
            * @since 3.0.0
            *
            * @param string $output Passed by reference. Used to append additional content.
            * @param object $item   Page data object. Not used.
            * @param int    $depth  Depth of page. Not Used.
            * @param array  $args   An array of arguments. @see wp_nav_menu()
            */
            function end_el(&$output, $post_data, $depth = 0, $args = array()) 
                {
                    $output .= "</li>\n";
                }
            
                
            function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) 
                {
                    if ( !$element )
                        return;

                    $id_field = $this->db_fields['id'];

                    if(is_object($element)  &&  isset($element->ID))
                        $element = get_post($element->ID);
                        else
                        $element = get_post($element);
                    
                    //display this element
                    if ( is_array( $args[0] ) )
                        $args[0]['has_children'] = ! empty( $children_elements[$element->$id_field] );
                    $cb_args = array_merge( array(&$output, $element, $depth), $args);
                    call_user_func_array(array($this, 'start_el'), $cb_args);

                    $id = $element->$id_field;

                    // descend only when the depth is right and there are childrens for this element
                    if ( ($max_depth == 0 || $max_depth > $depth+1 ) && isset( $children_elements[$id]) ) 
                        {

                            foreach( $children_elements[ $id ] as $child )
                                {

                                    if ( !isset($newlevel) ) 
                                        {
                                            $newlevel = true;
                                            //start the child delimiter
                                            $cb_args = array_merge( array(&$output, $depth), $args);
                                            call_user_func_array(array($this, 'start_lvl'), $cb_args);
                                        }
                                    $this->display_element( $child, $children_elements, $max_depth, $depth + 1, $args, $output );
                                }
                            unset( $children_elements[ $id ] );
                        }

                    if ( isset($newlevel) && $newlevel )
                        {
                            //end the child delimiter
                            $cb_args = array_merge( array(&$output, $depth), $args);
                            call_user_func_array(array($this, 'end_lvl'), $cb_args);
                        }

                    //end this element
                    $cb_args = array_merge( array(&$output, $element, $depth), $args);
                    call_user_func_array(array($this, 'end_el'), $cb_args);
                }
                
                
                
            /**
            * Display array of elements hierarchically.
            *
            * Does not assume any existing order of elements.
            *
            * $max_depth = -1 means flatly display every element.
            * $max_depth = 0 means display all levels.
            * $max_depth > 0 specifies the number of display levels.
            *
            * @since 2.1.0
            *
            * @param array $elements  An array of elements.
            * @param int   $max_depth The maximum hierarchical depth.
            * @return string The hierarchical item output.
            */
            public function walk( $elements, $max_depth, ...$args ) 
                {
                    $output = '';
                    
                    //check if woocommerce and archive to change max_depth for grouped products re-order
                    $is_woocommerce_archive = $this->is_woocommerce_archive_list( $args[0]['sort_id'], $args[0]['sort_view_id'] );
                    if ( $is_woocommerce_archive )
                        $max_depth  =   0;
                            
                    //invalid parameter or nothing to walk
                    if ( $max_depth < -1 || empty( $elements ) ) {
                    return $output;
                    }

                    $parent_field = $this->db_fields['parent'];

                    // flat display
                    if ( -1 == $max_depth ) 
                        {
                            $empty_array = array();
                            foreach ( $elements as $e )
                                $this->display_element( $e, $empty_array, 1, 0, $args, $output );
                                
                            return $output;
                        }

                    /*
                    * Need to display in hierarchical order.
                    * Separate elements into two buckets: top level and children elements.
                    * Children_elements is two dimensional array, eg.
                    * Children_elements[10][] contains all sub-elements whose parent is 10.
                    */
                    $top_level_elements = array();
                    $children_elements  = array();
                    foreach ( $elements as $e) 
                        {
                            if ( empty( $e->$parent_field ) )
                                $top_level_elements[] = $e;
                            else
                                $children_elements[ $e->$parent_field ][] = $e;
                        }
                    
                    //add child grouped products if woocommerce
                    if  ( $is_woocommerce_archive  )
                        {
                            
                            //deprecated hierarchy for olde WooCOmmerce < v4
                            //Put as parent if not already
                            foreach ( $children_elements    as  $parent_id  =>  $children_block )
                                {
                                    foreach ( $children_block   as  $child_item)
                                        {
                                            $child_item->post_parent    =   0;
                                            $top_level_elements[]   =   $child_item;
                                        }
                                }
                            $children_elements  = array();
                            
                            foreach ( $top_level_elements   as $key =>  $data )
                                {
                                    //check if any child
                                    $product_grouped_children   =   get_post_meta($data->ID    ,   '_children' , TRUE );
                                    if ( empty ($product_grouped_children ) ||  ! is_array($product_grouped_children) )
                                        continue;
                                        
                                    foreach ($product_grouped_children as   $child_element )
                                        {
                                            $children_elements[ $data->ID ][] = $child_element;   
                                        }
                                    
                                }
                        }

                    /*
                    * When none of the elements is top level.
                    * Assume the first one must be root of the sub elements.
                    */
                    if ( empty($top_level_elements) ) 
                        {

                            $first = array_slice( $elements, 0, 1 );
                            $root = $first[0];

                            $top_level_elements = array();
                            $children_elements  = array();
                            foreach ( $elements as $e) 
                                {
                                    if ( $root->$parent_field == $e->$parent_field )
                                        $top_level_elements[] = $e;
                                    else
                                        $children_elements[ $e->$parent_field ][] = $e;
                                }
                        }

                    foreach ( $top_level_elements as $e )
                        $this->display_element( $e, $children_elements, $max_depth, 0, $args, $output );

                    /*
                    * If we are displaying all levels, and remaining children_elements is not empty,
                    * then we got orphans, which should be displayed regardless.
                    */
                    if ( ( $max_depth == 0 ) && count( $children_elements ) > 0 ) 
                        {
                            $empty_array = array();
                            foreach ( $children_elements as $orphans )
                                {
                                    foreach ( $orphans as $op )
                                        $this->display_element( $op, $empty_array, 1, 0, $args, $output );
                                }
                        }

                    return $output;
                }
                
                
            function is_woocommerce_archive_list( $sort_id, $sort_view_id )
                {
                    
                    if  ( is_bool($this->is_woocommerce))
                        return $this->is_woocommerce;
                        
                        
                    global $APTO;
                    
                    $this->is_woocommerce   =   $APTO->functions->is_woocommerce_archive_list( $sort_id, $sort_view_id );
                    
                    return $this->is_woocommerce;   
                    
                }

        }

?>