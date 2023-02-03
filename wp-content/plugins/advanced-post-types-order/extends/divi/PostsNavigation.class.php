<?php
 
    class ET_Builder_Module_Apto_Posts_Navigation extends ET_Builder_Module 
        {
            function init() {
                $this->name             = esc_html__( 'APTO - Post Navigation', 'et_builder' );
                $this->plural           = esc_html__( 'APTO - Post Navigations', 'et_builder' );
                $this->slug             = 'et_pb_apto_post_nav';
                $this->vb_support       = 'on';
                $this->main_css_element = '.et_pb_posts_nav%%order_class%%';

                $this->defaults = array();

                $this->settings_modal_toggles = array(
                    'general'  => array(
                        'toggles' => array(
                            'main_content' => esc_html__( 'Text', 'et_builder' ),
                            'sort'          => esc_html__( 'Sort', 'et_builder' ),
                            'navigation'   => esc_html__( 'Navigation', 'et_builder' ),
                        ),
                    ),
                );

                $this->advanced_fields = array(
                    'fonts'                 => array(
                        'title' => array(
                            'label'    => esc_html__( 'Links', 'et_builder' ),
                            'css'      => array(
                                'main' => "{$this->main_css_element} span a, {$this->main_css_element} span a span",
                            ),
                            'line_height' => array(
                                'default' => '1em',
                            ),
                            'font_size' => array(
                                'default' => '14px',
                            ),
                            'letter_spacing' => array(
                                'default' => '0px',
                            ),
                            'hide_text_align' => true,
                        ),
                    ),
                    'margin_padding' => array(
                        'css' => array(
                            'main' => "{$this->main_css_element} span.nav-previous a, {$this->main_css_element} span.nav-next a",
                        ),
                    ),
                    'background'            => array(
                        'css' => array(
                            'main' => "{$this->main_css_element} a",
                        ),
                    ),
                    'borders'               => array(
                        'default' => array(
                            'css' => array(
                                'main' => array(
                                    'border_radii'  => "{$this->main_css_element} span.nav-previous a, {$this->main_css_element} span.nav-next a",
                                    'border_styles' => "{$this->main_css_element} span.nav-previous a, {$this->main_css_element} span.nav-next a",
                                ),
                            ),
                        ),
                    ),
                    'box_shadow'            => array(
                        'default' => array(
                            'css' => array(
                                'main'         => '%%order_class%% .nav-previous, %%order_class%% .nav-next',
                                'overlay' => 'inset',
                                'important'    => true,
                            ),
                        ),
                    ),
                    'text'                  => false,
                    'button'                => false,
                    'link_options'          => false,
                );

                $this->custom_css_fields = array(
                    'links' => array(
                        'label'    => esc_html__( 'Links', 'et_builder' ),
                        'selector' => 'span a',
                    ),
                    'prev_link' => array(
                        'label'    => esc_html__( 'Previous Link', 'et_builder' ),
                        'selector' => 'span.nav-previous a',
                    ),
                    'prev_link_arrow' => array(
                        'label'    => esc_html__( 'Previous Link Arrow', 'et_builder' ),
                        'selector' => 'span.nav-previous a span',
                    ),
                    'next_link' => array(
                        'label'    => esc_html__( 'Next Link', 'et_builder' ),
                        'selector' => 'span.nav-next a',
                    ),
                    'next_link_arrow' => array(
                        'label'    => esc_html__( 'Next Link Arrow', 'et_builder' ),
                        'selector' => 'span.nav-next a span',
                    ),
                );
    
            }

            function get_fields() {
                $fields = array(
         
                    'show_prev' => array(
                        'label'           => esc_html__( 'Show Previous Post Link', 'et_builder' ),
                        'type'            => 'yes_no_button',
                        'option_category' => 'configuration',
                        'options'         => array(
                            'on'  => esc_html__( 'Yes', 'et_builder' ),
                            'off' => esc_html__( 'No', 'et_builder' ),
                        ),
                        'default_on_front' => 'on',
                        'affects'           => array(
                            'prev_text',
                        ),
                        'toggle_slug'       => 'navigation',
                        'description'       => esc_html__( 'Turn this on to show the previous post link', 'et_builder' ),
                        'mobile_options'    => true,
                        'hover'             => 'tabs',
                    ),
                    'show_next' => array(
                        'label'           => esc_html__( 'Show Next Post Link', 'et_builder' ),
                        'type'            => 'yes_no_button',
                        'option_category' => 'configuration',
                        'options'         => array(
                            'on'  => esc_html__( 'Yes', 'et_builder' ),
                            'off' => esc_html__( 'No', 'et_builder' ),
                        ),
                        'default_on_front' => 'on',
                        'affects'           => array(
                            'next_text',
                        ),
                        'toggle_slug'       => 'navigation',
                        'description'       => esc_html__( 'Turn this on to show the next post link', 'et_builder' ),
                        'mobile_options'    => true,
                        'hover'             => 'tabs',
                    ),
                    'prev_text' => array(
                        'label'           => esc_html__( 'Previous Link', 'et_builder' ),
                        'type'            => 'text',
                        'option_category' => 'configuration',
                        'depends_show_if' => 'on',
                        'computed_affects' => array(
                            '__posts_navigation',
                        ),
                        'description'     => et_get_safe_localization( __( 'Define custom text for the previous link. You can use the <strong>%title</strong> variable to include the post title. Leave blank for default.', 'et_builder' ) ),
                        'toggle_slug'     => 'main_content',
                        'mobile_options'  => true,
                        'hover'           => 'tabs',
                    ),
                    'next_text' => array(
                        'label'           => esc_html__( 'Next Link', 'et_builder' ),
                        'type'            => 'text',
                        'option_category' => 'configuration',
                        'depends_show_if' => 'on',
                        'computed_affects' => array(
                            '__posts_navigation',
                        ),
                        'description'     => et_get_safe_localization( __( 'Define custom text for the next link. You can use the <strong>%title</strong> variable to include the post title. Leave blank for default.', 'et_builder' ) ),
                        'toggle_slug'     => 'main_content',
                        'mobile_options'  => true,
                        'hover'           => 'tabs',
                    ),
                    
                    'sort_id' => array(
                        'label'             => esc_html__( 'Sort ID', 'et_builder' ),
                        'type'              => 'select',
                        'option_category'   => 'configuration',
                        'options'           => ET_Builder_Module_Apto_Posts_Navigation::get_sorts(),
                        'description'       => esc_html__( 'Choose the sort ID to apply. If the custom post object assigned to a category, the primary will be used when applying for the order.', 'et_builder' ),
                 
                        'toggle_slug'       => 'sort',
                        'default'           => 'a',
                  
                    ),
                    
                    'taxonomy_name' => array(
                        'label'             => esc_html__( 'Sort on Taxonomy', 'et_builder' ),
                        'type'              => 'select',
                        'option_category'   => 'configuration',
                        'options'           => ET_Builder_Module_Apto_Posts_Navigation::get_taxonomies(),
                        'description'       => esc_html__( 'Choose the Taxoomy Name from which the order should be used.', 'et_builder' ),
                 
                        'toggle_slug'       => 'sort',
                        'default'           => 'a',
                  
                    ),
                    
                    '__posts_navigation' => array(
                        'type' => 'computed',
                        'computed_callback' => array( 'ET_Builder_Module_Apto_Posts_Navigation', 'get_posts_navigation' ),
                        'computed_depends_on' => array(
                            'in_same_term',
                            'taxonomy_name',
                            'prev_text',
                            'next_text'
                        ),
                    ),
                );
                return $fields;
            }
            
            
            
            static function get_sorts()
                {
                    
                    global $APTO;
                    
                    $available_sorts    =   $APTO->functions->get_sorts_by_filters();   
                    
                    $sorts  =   array();
                    
                    if ( count ( $available_sorts ) >   0 )
                        {
                            foreach  ( $available_sorts as  $sort_item )
                                {
                                    $sorts[]    =   $sort_item->ID;
                                }
                        }
                    
                    return $sorts;
                    
                }
                
                
            static function get_taxonomies()
                {
                    $taonomies  =   array();
                    
                    $available_taxonomies =   get_taxonomies(array(), 'objects'); 
                    
                    $taonomies  =   array( 'archive' );
                    
                    foreach ($available_taxonomies as $taxonomy ) 
                        {
                            $taonomies[]    =    $taxonomy->name;
                        }
                    
                    return $taonomies;
                }

            /**
             * Get prev and next post link data for frontend builder's post navigation module component
             *
             * @param int    post ID
             * @param bool   show posts which uses same link only or not
             * @param string excluded terms name
             * @param string taxonomy name for in_same_terms
             *
             * @return string JSON encoded array of post's next and prev link
             */
            static function get_posts_navigation( $args = array(), $conditional_tags = array(), $current_page = array() ) {
                global $post;

                $defaults = array(
                    'sort_id'       =>  '',
                    'taxonomy_name'  => 'category',
                    'prev_text'      => '%title',
                    'next_text'      => '%title',
                );

                $args = wp_parse_args( $args, $defaults );
         
                et_core_nonce_verified_previously();
                if ( ! isset( $post ) && defined( 'DOING_AJAX' ) && DOING_AJAX && ! empty( $_POST['et_post_id'] ) ) {
                    $post_id = sanitize_text_field( $_POST['et_post_id'] );
                } else if ( isset( $current_page['id'] ) ) {
                    // Overwrite global $post value in this scope
                    $post_id = intval( $current_page['id'] );
                } else if ( is_object( $post ) && isset( $post->ID ) ) {
                    $post_id = $post->ID;
                } else {
                    return array(
                        'next' => '',
                        'prev' => '',
                    );
                }

                // Set current post as global $post
                $post = get_post( $post_id ); // phpcs:ignore WordPress.Variables.GlobalVariables.OverrideProhibited

                $post_first_term    =   FALSE;
                
                //get the first term
                $post_terms =   wp_get_post_terms ( $post->ID, $args['taxonomy_name'] );
                if ( count ( $post_terms ) > 0 )
                    {
                        reset($post_terms);
                        $first_term =   current($post_terms);
                        
                        $post_first_term =  $first_term->term_id;
                    }
                    
                    
                $next_post  =   FALSE;
                
                // Get next post
                if ( $post_first_term )
                    {
                        $sort_args  =   array(
                                                'sort_id'   =>  $args['sort_id'],
                                                'taxonomy'  =>  $args['taxonomy_name'],
                                                'term_id'   =>  $post_first_term 
                                                );
                        $next_post = apto_get_adjacent_post( $sort_args, TRUE);
                    }

                $next = new stdClass();

                if ( ! empty( $next_post ) ) {

                    $next_title = isset($next_post->post_title) ? esc_html( $next_post->post_title ) : esc_html__( 'Next Post' );

                    $next_date = mysql2date( get_option( 'date_format' ), $next_post->post_date );
                    $next_permalink = isset($next_post->ID) ? esc_url( get_the_permalink( $next_post->ID ) ) : '';

                    $next_processed_title = '' === $args['next_text'] ? '%title' : $args['next_text'];

                    // process Wordpress' wildcards
                    $next_processed_title = str_replace( '%title', $next_title, $next_processed_title );
                    $next_processed_title = str_replace( '%date', $next_date, $next_processed_title );
                    $next_processed_title = str_replace( '%link', $next_permalink, $next_processed_title );

                    $next->title = $next_processed_title;
                    $next->id = isset($next_post->ID) ? intval( $next_post->ID ) : '';
                    $next->permalink = $next_permalink;
                }

                
                $prev_post  =   FALSE;
                
                // Get prev post
                if ( $post_first_term )
                    {
                        $sort_args  =   array(
                                                'sort_id'   =>  $args['sort_id'],
                                                'taxonomy'  =>  $args['taxonomy_name'],
                                                'term_id'   =>  $post_first_term 
                                                );
                        $prev_post = apto_get_adjacent_post( $sort_args, FALSE);
                    }

                $prev = new stdClass();

                if ( ! empty( $prev_post ) ) {

                    $prev_title = isset($prev_post->post_title) ? esc_html( $prev_post->post_title ) : esc_html__( 'Previous Post' );

                    $prev_date = mysql2date( get_option( 'date_format' ), $prev_post->post_date );

                    $prev_permalink = isset($prev_post->ID) ? esc_url( get_the_permalink( $prev_post->ID ) ) : '';

                    $prev_processed_title = '' === $args['prev_text'] ? '%title' : $args['prev_text'];

                    // process Wordpress' wildcards
                    $prev_processed_title = str_replace( '%title', $prev_title, $prev_processed_title );
                    $prev_processed_title = str_replace( '%date', $prev_date, $prev_processed_title );
                    $prev_processed_title = str_replace( '%link', $prev_permalink, $prev_processed_title );

                    $prev->title = $prev_processed_title;
                    $prev->id = isset($prev_post->ID) ? intval( $prev_post->ID ) : '';
                    $prev->permalink = $prev_permalink;
                }

                // Formatting returned value
                $posts_navigation = array(
                    'next' => $next,
                    'prev' => $prev,
                );

                return $posts_navigation;
            }

            function render( $attrs, $content = null, $render_slug ) {
                $multi_view     = et_pb_multi_view_options( $this );
                $sort_id        = $this->props['sort_id'];
                $taxonomy_name  = $this->props['taxonomy_name'];
                $show_prev      = $this->props['show_prev'];
                $show_next      = $this->props['show_next'];
                $prev_text      = $this->props['prev_text'];
                $next_text      = $this->props['next_text'];

                // do not output anything if both prev and next links are disabled
                if ( ! $multi_view->has_value( 'show_prev', 'on' ) && ! $multi_view->has_value( 'show_next', 'on' ) ) {
                    return;
                }

                $video_background          = $this->video_background();
                $parallax_image_background = $this->get_parallax_image_background();

                $posts_navigation = self::get_posts_navigation( array(
                    'sort_id'       => $sort_id,
                    'taxonomy_name' => $taxonomy_name,
                    'prev_text'     => $prev_text,
                    'next_text'     => $next_text,
                ) );

                ob_start();

                $background_classname = array();

                if ( '' !== $video_background ) {
                    $background_classname[] = 'et_pb_section_video';
                    $background_classname[] = 'et_pb_preload';

                }

                if ( '' !== $parallax_image_background ) {
                    $background_classname[] = 'et_pb_section_parallax';
                }

                $background_class_attr = empty( $background_classname ) ? '' : sprintf( ' class="%s"', esc_attr( implode( ' ', $background_classname ) ) );

                if ( $multi_view->has_value( 'show_prev', 'on' ) && ! empty( $posts_navigation['prev']->permalink ) ) {
                    ?>
                        <span class="nav-previous"<?php $multi_view->render_attrs( array(
                                'visibility' => array(
                                    'show_prev' => 'on',
                                ),
                            ), true ); ?>>
                            <a href="<?php echo esc_url( $posts_navigation['prev']->permalink ); ?>" rel="prev"<?php echo et_core_esc_previously( $background_class_attr ); ?>>
                                <?php
                                    echo et_core_esc_previously( $parallax_image_background );
                                    echo et_core_esc_previously( $video_background );
                                ?>
                                <span class="meta-nav">&larr; </span><span class="nav-label"<?php $multi_view->render_attrs( array( 'content' => '{{prev_text}}' ), true ); ?>><?php echo esc_html( $posts_navigation['prev']->title ); ?></span>
                            </a>
                        </span>
                    <?php
                }

                if ( $multi_view->has_value( 'show_next', 'on' ) && ! empty( $posts_navigation['next']->permalink ) ) {
                    ?>
                        <span class="nav-next"<?php $multi_view->render_attrs( array( 
                                'visibility' => array(
                                    'show_next' => 'on',
                                ),
                            ), true ); ?>>
                            <a href="<?php echo esc_url( $posts_navigation['next']->permalink ); ?>" rel="next"<?php echo et_core_esc_previously( $background_class_attr ); ?>>
                                <?php
                                    echo et_core_esc_previously( $parallax_image_background );
                                    echo et_core_esc_previously( $video_background );
                                ?>
                                <span class="nav-label"<?php $multi_view->render_attrs( array( 'content' => '{{next_text}}' ), true ); ?>><?php echo esc_html( $posts_navigation['next']->title ); ?></span><span class="meta-nav"> &rarr;</span>
                            </a>
                        </span>
                    <?php
                }

                $page_links = ob_get_contents();

                ob_end_clean();

                // Module classname
                $this->add_classname( array(
                    'et_pb_posts_nav',
                    'et_pb_apto_posts_nav',
                    'nav-single',
                ) );

                // Remove automatically added module classname
                $this->remove_classname( array(
                    $render_slug,
                    'et_pb_section_video',
                    'et_pb_preload',
                    'et_pb_section_parallax',
                ) );

                $output = sprintf(
                    '<div class="%2$s"%1$s>
                        %3$s
                    </div>',
                    $this->module_id(),
                    $this->module_classname( $render_slug ),
                    $page_links
                );

                return $output;
            }
        }


    new ET_Builder_Module_Apto_Posts_Navigation;
    
?>