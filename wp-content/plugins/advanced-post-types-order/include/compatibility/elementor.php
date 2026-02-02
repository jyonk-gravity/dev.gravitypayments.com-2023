<?php
    
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    
    class APTO_compatibility_elementor
        {
            function __construct()
                {
                    add_filter ( 'elementor/query/query_args', array ( $this, 'elementor_query_query_args' ), 99, 2 );
                }
                
            
            /**
            * Attempt to filter out redundant taxonomy arguments so the ort fall on a simple taxonomy term match
            *     
            * @param mixed $query_args
            * @param mixed $widget_object
            */
            function elementor_query_query_args( $query_args, $widget_object ) 
                {
                    if ( ! isset( $query_args['tax_query'] ) || count( $query_args['tax_query'] ) < 2 ) {
                        return $query_args;
                    }
                    
                    if ( isset( $query_args['tax_query']['relation'] ) && strtolower( $query_args['tax_query']['relation'] ) !== 'and' ) {
                        return $query_args;
                    }
                    
                    $query_tax = $query_args['tax_query'];
                    $relation = isset( $query_tax['relation'] ) ? $query_tax['relation'] : null;
                    if ( isset( $query_tax['relation'] ) ) {
                        unset( $query_tax['relation'] );
                    }
                    
                    // Track which indices to remove
                    $indices_to_remove = [];
                    
                    // Loop through each tax query item
                    foreach ( $query_tax as $index1 => $tax_item1 ) 
                        {
                            if ( ! isset( $tax_item1['taxonomy'] ) ) {
                                continue;
                            }
                            
                            // Check if this index is already marked for removal
                            if ( in_array( $index1, $indices_to_remove ) ) {
                                continue;
                            }
                            
                            // Compare with other items
                            foreach ( $query_tax as $index2 => $tax_item2 ) 
                                {
                                    if ( ! isset( $tax_item2['taxonomy'] ) || $index1 >= $index2 ) {
                                        continue;
                                    }
                                    
                                    // Check if same taxonomy
                                    if ( $tax_item1['taxonomy'] !== $tax_item2['taxonomy'] ) {
                                        continue;
                                    }
                                    
                                    // Normalize terms for comparison
                                    $terms1 = $this->elementor_normalize_terms( $tax_item1 );
                                    $terms2 = $this->elementor_normalize_terms( $tax_item2 );
                                    
                                    // Check if any terms overlap
                                    $overlap = array_intersect( $terms1, $terms2 );
                                    
                                    if ( ! empty( $overlap ) ) 
                                        {
                                            // Keep the one with fewer terms, remove the one with more
                                            if ( count( $terms1 ) < count( $terms2 ) ) {
                                                $indices_to_remove[] = $index2;
                                            } else {
                                                $indices_to_remove[] = $index1;
                                            }
                                        }
                                }
                        }
                    
                    // Remove marked indices
                    foreach ( $indices_to_remove as $index ) {
                        unset( $query_tax[ $index ] );
                    }
                    
                    // Re-index the array
                    $query_tax = array_values( $query_tax );
                    
                    // Add relation back if it existed
                    if ( $relation ) {
                        $query_tax['relation'] = $relation;
                    }
                    
                    $query_args['tax_query'] = $query_tax;
                    
                    return $query_args;
                }

            function elementor_normalize_terms( $tax_item ) 
                {
                    if ( ! isset( $tax_item['terms'] ) ) {
                        return [];
                    }
                    
                    $terms = $tax_item['terms'];
                    if ( ! is_array( $terms ) ) {
                        $terms = [ $terms ];
                    }
                    
                    $field = isset( $tax_item['field'] ) ? $tax_item['field'] : 'term_id';
                    
                    // If field is already term_id, return as is (convert to int for consistency)
                    if ( $field === 'term_id' ) {
                        return array_map( 'intval', $terms );
                    }
                    
                    // Convert from slug, name, or other field to term_id
                    $term_ids = [];
                    foreach ( $terms as $term ) 
                        {
                            $term_obj = get_term_by( $field, $term, $tax_item['taxonomy'] );
                            if ( $term_obj ) {
                                $term_ids[] = intval( $term_obj->term_id );
                            }
                        }
                    
                    return $term_ids;
                }
                
        }
        
    new APTO_compatibility_elementor();