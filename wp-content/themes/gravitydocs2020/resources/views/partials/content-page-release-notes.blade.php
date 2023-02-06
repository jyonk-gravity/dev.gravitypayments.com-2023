{{-- Page Content Blocks --}}

@if ( have_rows('page_content_blocks') )
	
	@while ( have_rows('page_content_blocks') )
		@php the_row(); @endphp

		@if ( get_row_layout() == 'intro' )
			<section class="page-block__intro" style="padding: 2.5rem 0 2.5rem 3.5rem;">
				<div class="row align-items-center">
					<div class="col-lg-6">
						@if ( get_sub_field('heading') )
							<h1>{!! get_sub_field('heading') !!}</h1>
						@endif

						@if ( get_sub_field('content') )
							<div class="intro__content">
								{!! get_sub_field('content') !!}
							</div>
						@endif

						@if ( have_rows('buttons') )
							<div class="intro-buttons">
								@while ( have_rows('buttons') )
									@php 
									the_row();

									$link = get_sub_field('button_link');
									$link_url = $link['url'];
									$link_title = $link['title'];
									$link_target = $link['target'] ? $link['target'] : '_self';
									$button_style = get_sub_field('button_style');
									@endphp

									<div class="intro-buttons__button">
										@if ( get_sub_field('title_above_button') )
											<h3>{!! get_sub_field('title_above_button') !!}</h3>
										@endif

										@if ( $link )
										<a href="{{ esc_url( $link_url ) }}" title="{{ $link_title }}" target="{{ $link_target }}" class="btn btn-md {{ $button_style }}">{!! $link_title !!}</a>
										@endif
									</div>
								@endwhile
							</div>
						@endif
					</div>

					@if ( get_sub_field('image') )
						@php
						$image = get_sub_field('image');
						$image_url = $image['sizes']['large'];
						$image_alt = $image['alt'];
						@endphp

						<div class="col-lg-6">
							<img src="{{ esc_url($image_url) }}" alt="{{ esc_attr($image_alt) }}" class="img-fluid">
						</div>
					@endif
				</div>
			</section>

		@elseif ( get_row_layout() == 'three_column_image_plus_content' )
			<section class="page-block__three-column--image-plus-content" style="padding: 0 3.5rem 3.5rem;">
				<div class="row">
					@while ( have_rows('columns') )
						@php 
						the_row(); 

						if ( get_sub_field('column_link') ) {
							$link = get_sub_field('column_link');
							$link_url = $link['url'];
							$link_title = $link['title'];
							$link_target = $link['target'] ? $link['target'] : '_self';
						}
						@endphp

						<div class="col-lg-4">
							@if ( get_sub_field('column_image') )
								@php
								$image = get_sub_field('column_image');
								$image_url = $image['sizes']['large'];
								$image_alt = $image['alt'];
								@endphp

								<div class="column__image">
									@if ( get_sub_field('column_link') )
									<a href="{{ esc_url($link_url) }}" title="{{ $link_title }}" target="{{ $link_target }}">
									@endif

										<img src="{{ esc_url($image_url) }}" alt="{{ esc_attr($image_alt) }}" class="d-block mb-3" style="width: 100%; border-radius: 0.375rem; box-shadow: inset 0 0 0 1px rgba(0,0,0,0.1);">

									@if ( get_sub_field('column_link') )
									</a>
									@endif
								</div>
							@endif

							@if ( get_sub_field('column_heading') )
								<h2>{!! get_sub_field('column_heading') !!}</h2>
							@endif

							@if ( get_sub_field('column_content') )
								<div class="column__content">
									{!! get_sub_field('column_content') !!}
								</div>
							@endif

							@if ( get_sub_field('column_link') )
								<div class="column__link">
									<a href="{{ esc_url($link_url) }}" title="{{ $link_title }}" target="{{ $link_target }}"><span>{!! $link_title !!}</span> <i class="far fa-chevron-right"></i></a>
								</div>
							@endif
						</div>
					@endwhile
				</div>
			</section>

		@elseif ( get_row_layout() == 'numbered_steps_image_plus_content' )

			<section class="page-block__numbered-steps--image-plus-content" style="padding: 0 0 2.5rem 3.5rem;">
				@while ( have_rows('steps') )
					@php the_row(); @endphp
					<div class="row">
						<div class="col-lg-6">
							<h2>{!! get_sub_field('step_heading') !!}</h2>
							<div class="step__content">
								{!! get_sub_field('step_content') !!}
							</div>
						</div>
						<div class="col-lg-6"></div>
					</div>
				@endwhile
			</section>

		@elseif ( get_row_layout() == 'form' )
			<section class="page-block__form" style="padding: 0 0 2.5rem 3.5rem;">
				<div class="row">
					<div class="col-lg-8">
						@if ( get_sub_field('form') )
							{!! do_shortcode('[gravityform id="'. get_sub_field('form') .'" title="false" description="false" ajax="true"]') !!}
						@endif
					</div>
				</div>
			</section>
		@endif

	@endwhile

@endif	

<section class="page-block__release-notes" style="padding: 0 3.5rem 2.5rem;">
	<div class="row">
		<div class="col-lg-12">
			@php
			$args = array(
				'post_type' => 'release_notes',
				'posts_per_page' => -1,
				'post_status' => 'publish',
				'orderby' => 'date',
			);

			$month_query = new WP_Query($args);

			/** Check to ensure that there are articles for this month... */
			if($month_query->have_posts()) :

			    $month_titles = array();
			    $close_div = false;
			    
			    
			    //echo '<ul style="padding-left: 250px;" id="monthly-posts">';
			    
			    /** Set the attributes for displaying the title as an attribute */
			    $title_attribute_args = array(
			        'before'    => 'Visit article \'',
			        'after'     => '\' ',
			        'echo'      => false
			    );      
			    
			    /** Loop through each post for this month... */
			    while($month_query->have_posts()) : $month_query->the_post();
			    
			        /** Check the month/year of the current post */
			        $month_title = date('F Y', strtotime(get_the_date('Y-m-d H:i:s')));

			        //Lower case everything
					$month_slug = strtolower($month_title);
					//Make alphanumeric (removes all other characters)
					$month_slug = preg_replace("/[^a-z0-9_\s-]/", "", $month_slug);
					//Clean up multiple dashes or whitespaces
					$month_slug = preg_replace("/[\s-]+/", " ", $month_slug);
					//Convert whitespaces and underscore to dash
					$month_slug = preg_replace("/[\s_]/", "-", $month_slug);
			        
			        /** Maybe output a human friendly date, if it's not already been output */
			        if(!in_array($month_title, $month_titles)) :
			        
			            if($close_div) echo '</div>';                                                             // Check if the unordered list of posts should be closed (it shouldn't for the first '$monthe_title')
			            echo '<h3 class="monthly-title" data-title-date="' . $month_slug . '">' . $month_title . '</h3>';   // Output the '$month_title'
			            echo '<div class="monthly-posts" data-posts-date="' . $month_slug . '">';                            // Open an unordered lists for the posts that are to come
			            $month_titles[] = $month_title;                                                         // Add this `$month_title' to the array of `$month_titles` so that it is not repeated
			            $close_div = true;                                                                       // Indicate that the unordered list should be closed at the next oppurtunity
			            
			        endif;

			        /** Output each article for this month */
			        $category = wp_get_post_terms(get_the_ID(), 'release_notes_category');
					$category_color = get_field('category_color', 'release_notes_category_' . $category[0]->term_id );

			        echo '<div class="release-note" data-category="'. $category[0]->slug .'">';
					echo '	<div class="release-note__category release-note__category--'. $category[0]->slug .'">';
					echo '		<span style="color: ' . $category_color . '">' . $category[0]->name . '</span>';
					echo '	</div>';
					echo '	<div class="release-note__content">';
								if ( get_field('version') ) {
									echo '<div class="release-note__content__version mb-2" style="color: ' . $category_color . '">' . get_field('version') . '</div>';
								}
					echo 		get_the_content();
					echo '	</div>';
					echo '</div>';
			        
			    endwhile;
			    
			    if($close_div) echo '</div>'; // Close the last unordered list of posts (if there are any shown)
			    
			endif;

			/** Reset the query so that WP doesn't do funky stuff */
			wp_reset_query();
			@endphp

		</div>
	</div>
</section>
