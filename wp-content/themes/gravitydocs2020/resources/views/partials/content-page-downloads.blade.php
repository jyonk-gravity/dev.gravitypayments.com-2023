{{-- Page Content Blocks --}}

@if ( have_rows('page_content_blocks') )
	
	@while ( have_rows('page_content_blocks') )
		@php the_row(); @endphp

		@if ( get_row_layout() == 'intro' )
			<section class="page-block__intro" style="padding: 2.5rem 0 2.5rem 3.5rem; background: linear-gradient(180deg, rgba(236, 245, 253, 0.6) 0%, #FFFFFF 100%);">
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

<section class="page-block__downloads-list">
	<div class="row">
		<div class="col-12">
			@php
			$args = array(
				'post_type' => 'download',
				'posts_per_page' => -1,
				'post_status' => 'publish',
				'orderby' => 'menu_order',
				'order' => 'ASC',
				// 'tax_query' => array(
				// 	array(
				// 		'taxonomy' => 'download_category',
				// 		'field' => 'slug',
				// 		'terms' => 'limited-release',
				// 		'operator' => 'NOT IN',
				// 	),
				// ),
			);

			$query = new WP_Query($args);
			@endphp

			@if ( $query->have_posts() )
				<div class="downloads-list">
					<div class="downloads-list__header row">
						<div class="downloads-list__header__label col-lg-2">
							Product(s)
						</div>

						<div class="downloads-list__header__label col-lg-3">
							Version
						</div>

						<div class="downloads-list__header__label col-lg-2">
							Release Date
						</div>

						<div class="downloads-list__header__label col-lg-3">
							Release Notes
						</div>

						<div class="downloads-list__header__label col-lg-2">
							File Download
						</div>
					</div>

				@while ( $query->have_posts() )
					@php
					$query->the_post();
					@endphp

					@if ( !get_field('category') )
					<div class="downloads-list__item row">
						<div class="downloads-list__item__products col-lg-2">
							@php
							$product_list = '';
							@endphp

							@while ( have_rows('products') )
								@php 
								the_row(); 
								
								$product_list .= get_sub_field('product_name') . ', ';
								@endphp
							@endwhile

							@php
							$product_list = substr( $product_list, 0, -2 );
							@endphp

							{!! $product_list !!}
						</div>
						<div class="downloads-list__item__version col-lg-3">
							{!! get_field('version') !!}

							@if ( get_field('version_notes') )
								{!! get_field('version_notes') !!}
							@endif
						</div>
						<div class="downloads-list__item__release-date col-lg-2">
							{!! get_field('release_date') !!}
						</div>
						<div class="downloads-list__item__release-notes col-lg-3">
							{!! get_field('release_notes') !!}
						</div>
						<div class="downloads-list__item__download-file col-lg-2">
							@if ( get_field('download_link') )
								@php
								// $file = get_field('download_file');
								$download_link = get_field('download_link');
								@endphp

								<a href="{{ esc_url($download_link) }}" 
									download 
									class="btn btn--teal btn-sm d-block btn-download">Download</a>

								@if ( get_field('download_hash') )
								<p class="download-hash mt-2"><strong>SHA-256 hash:</strong> {!! get_field('download_hash') !!}</p>
								@endif
							@endif
						</div>	
					</div>
					@endif

				@endwhile
				
				</div>
				
				@php
				wp_reset_postdata();
				@endphp

			@endif
		</div>
	</div>

	<div class="row" style="margin-top: 5rem;">
		<div class="col-12">
			@php
			$args = array(
				'post_type' => 'download',
				'posts_per_page' => -1,
				'post_status' => 'publish',
				'orderby' => 'menu_order',
				// 'order' => 'ASC',
				// 'tax_query' => array(
				// 	array(
				// 		'taxonomy' => 'download_category',
				// 		'field' => 'slug',
				// 		'terms' => array('limited-release'),
				// 	),
				// ),
			);

			$query = new WP_Query($args);
			@endphp

			@if ( $query->have_posts() )
				<h2 class="mb-4">Limited Release Downloads</h2>
				<div class="downloads-list">
					<div class="downloads-list__header row">
						<div class="downloads-list__header__label col-lg-2">
							Product(s)
						</div>

						<div class="downloads-list__header__label col-lg-3">
							Version
						</div>

						<div class="downloads-list__header__label col-lg-2">
							Release Date
						</div>

						<div class="downloads-list__header__label col-lg-3">
							Release Notes
						</div>

						<div class="downloads-list__header__label col-lg-2">
							File Download
						</div>
					</div>

				@while ( $query->have_posts() )
					@php
					$query->the_post();
					@endphp

					@if ( get_field('category') )
					<div class="downloads-list__item row">
						<div class="downloads-list__item__products col-lg-2">
							@php
							$product_list = '';
							@endphp

							@while ( have_rows('products') )
								@php 
								the_row(); 
								
								$product_list .= get_sub_field('product_name') . ', ';
								@endphp
							@endwhile

							@php
							$product_list = substr( $product_list, 0, -2 );
							@endphp

							{!! $product_list !!}
						</div>
						<div class="downloads-list__item__version col-lg-3">
							{!! get_field('version') !!}

							@if ( get_field('version_notes') )
								{!! get_field('version_notes') !!}
							@endif
						</div>
						<div class="downloads-list__item__release-date col-lg-2">
							{!! get_field('release_date') !!}
						</div>
						<div class="downloads-list__item__release-notes col-lg-3">
							{!! get_field('release_notes') !!}
						</div>
						<div class="downloads-list__item__download-file col-lg-2">
							@if ( get_field('download_link') )
								@php
								// $file = get_field('download_file');
								$download_link = get_field('download_link');
								@endphp

								<a href="{{ esc_url($download_link) }}" 
									download 
									class="btn btn--teal btn-sm d-block btn-download">Download</a>
							@endif
						</div>	
					</div>
					@endif

				@endwhile
				
				</div>
				

				@php
				wp_reset_postdata();
				@endphp
			@endif
		</div>
	</div>

</section>
