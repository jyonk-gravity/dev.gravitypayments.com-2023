{{-- Page Content Blocks --}}

@if ( get_field('background_image') )
  @php
  $image = get_field('background_image');
  $image_url = $image['sizes']['large'];
  $image_alt = $image['alt'];

  $image_opacity = get_field('background_image_opacity');
  $image_opacity = $image_opacity / 100;

  $image_top_gap = get_field('background_image_top_gap');
  @endphp

  <div class="page__background-image" style="background-image: url({{ esc_url($image_url) }}); opacity: {{ $image_opacity }}; {{ $image_top_gap ? 'top: 40px;' : '' }}">
    @if ( !empty($image_alt) )
    <span role="img" aria-label="{{ $image_alt }}"></span>
    @endif
  </div>
@endif



@if ( have_rows('page_content_blocks') )
	
	@while ( have_rows('page_content_blocks') )
		@php the_row(); @endphp

		@if ( get_row_layout() == 'intro' )
			<section class="page-block__intro">
				<div class="row align-items-center">
					<div class="col-xl-7">
						@if ( get_sub_field('heading') )
							<h1>{!! get_sub_field('heading') !!}</h1>
						@endif

						@if ( get_sub_field('content') )
							<div class="intro__content">
								{!! get_sub_field('content') !!}
							</div>
						@endif

						@if ( get_sub_field('cards') )
							<div class="intro__cards">
								@while ( have_rows('cards') )
									@php
									the_row();

									$card_icon = get_sub_field('card_icon');
									$card_icon_url = $card_icon['url'];
									$card_icon_alt = $card_icon['alt'];
									@endphp

									<a class="intro__card" href="{!! esc_url(get_sub_field('card_link')) !!}" title="View Documentation" target="_self">
										<div class="intro__card__icon">
											<img src="{!! esc_url($card_icon_url) !!}" alt="{!! $card_icon_alt !!}" class="img-fluid">
										</div>

										<div class="intro__card__content">
											<h2>{!! get_sub_field('card_title') !!}</h2>
											<h3 class="eyebrow-text">{!! get_sub_field('card_subtitle') !!}</h3>							
											{!! get_sub_field('card_content') !!}

											<div class="intro__link">
												<div><span>View Documentation</span> <i class="far fa-chevron-right"></i></div>
											</div>
										</div>
									</a>
								@endwhile
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
											<h3 class="h4">{!! get_sub_field('title_above_button') !!}</h3>
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

						<div class="col-xl-5">
							<img src="{{ esc_url($image_url) }}" alt="{{ esc_attr($image_alt) }}" class="img-fluid">
						</div>
					@endif
				</div>
			</section>

		@elseif ( get_row_layout() == 'three_column_image_plus_content' )
			<section class="page-block__three-column--image-plus-content" style="padding: 3rem 3.5rem 3.5rem;">
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

										<img src="{{ esc_url($image_url) }}" alt="{{ esc_attr($image_alt) }}" class="d-block mb-4" style="width: 100%; border-radius: 0.375rem; box-shadow: inset 0 0 0 1px rgba(0,0,0,0.1);">

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
				@php
				$step_counter = 1;
				@endphp

				@while ( have_rows('steps') )
					@php the_row(); @endphp
					<div class="row align-items-lg-center mb-5">
						<div class="col-lg-5">
							<h2><span class="step-counter">{{ $step_counter }}</span> {!! get_sub_field('step_heading') !!}</h2>
							<div class="step__content">
								{!! get_sub_field('step_content') !!}
							</div>
						</div>
						@if ( get_sub_field('step_image') )
						@php 
						$image_id = get_sub_field('step_image')['ID'];
						$image_alt = get_sub_field('image')['alt'];
						@endphp
						<div class="col-lg-4">
							<img class="img-fluid" {{ awesome_acf_responsive_image( $image_id,'large','960px' ) }} alt="{{ esc_attr($image_alt) }}">
						</div>
						@endif
					</div>

					@php
					$step_counter++;
					@endphp	
				@endwhile
			</section>

		@elseif ( get_row_layout() == 'timeline' )

			<section class="page-block__timeline">
				@php
				$step_counter = 1;
				@endphp

				@while ( have_rows('steps') )
					@php the_row(); @endphp
					<div class="row align-items-lg-center pb-5">
						<div class="col-lg-5">
							<h2><span class="step-counter">{{ $step_counter }}</span> {!! get_sub_field('step_heading') !!}</h2>
							<div class="step__content">
								{!! get_sub_field('step_content') !!}
							</div>
						</div>
						@if ( get_sub_field('step_image') )
						@php 
						$image_id = get_sub_field('step_image')['ID'];
						$image_alt = get_sub_field('image')['alt'];
						@endphp
						<div class="col-lg-4">
							<img class="img-fluid" {{ awesome_acf_responsive_image( $image_id,'large','960px' ) }} alt="{{ esc_attr($image_alt) }}">
						</div>
						@endif
					</div>

					@php
					$step_counter++;
					@endphp	
				@endwhile

				<div class="timeline__end"><i class="fas fa-check-circle"></i></div>
			</section>

		@elseif ( get_row_layout() == 'form' )
			<section class="page-block__form">
				<div class="row">
					<div class="col-lg-8">
						@if ( get_sub_field('form') )
							{!! do_shortcode('[gravityform id="'. get_sub_field('form') .'" title="false" description="false" ajax="true"]') !!}
						@endif
					</div>
				</div>
			</section>

		@elseif ( get_row_layout() == 'icon_content_columns' )
			<section class="page-block__icon-content-columns">
				<div class="row">
					@php
					$columns = '';

					$column_count = count(get_sub_field('columns'));

					if ( $column_count == 1 ) {
						$columns = 'col-lg-9';
					} elseif ( $column_count == 2 ) {
						$columns = 'col-lg-6';
					} elseif ( $column_count == 3 ) {
						$columns = 'col-lg-4';
					} elseif ( $column_count == 4 ) {
						$columns = 'col-lg-3';
					}
					@endphp
					@if ( have_rows('columns') )

						@while ( have_rows('columns') )
							@php
							the_row();
							@endphp

							<div class="col-12 icon-content-column {{ $columns }}">
								@if ( get_sub_field('link') )
									@php
									$link = get_sub_field('link');
									$link_url = $link['url'];
									$link_title = $link['title'];
									$link_target = $link['target'] ? $link['target'] : '_self';
									@endphp
								@endif

								<a class="icon-content-column__card" href="{{ esc_url($link_url) }}" title="{{ $link_title }}" target="{{ $link_target }}">
									<div class="icon-content-column__card__icon">
										@if ( get_sub_field('icon_type') == 'icon-type--custom' )
											@php
											$icon = get_sub_field('custom_icon_image');
											$icon_url = $icon['sizes']['medium_large'];
											$icon_alt = $icon['alt'];
											@endphp

											<img src="{{ esc_url($icon_url) }}" alt="{{ esc_attr($icon_alt) }}" class="img-fluid">
										@endif
									</div>

									<div class="icon-content-column__card__content">
										<h2>{!! get_sub_field('heading') !!}</h2>
										
										@if ( get_sub_field('subheading') )
										<h3 class="eyebrow-text">{!! get_sub_field('subheading') !!}</h3>
										@endif
										
										{!! get_sub_field('content') !!}

										@if ( get_sub_field('link') )
											<div class="icon-content-column__link">
												<div><span>{!! $link_title !!}</span> <i class="far fa-chevron-right"></i></div>
											</div>
										@endif
									</div>
								</a>
							</div>
						@endwhile
					@endif
				</div>
			</section>

		@elseif ( get_row_layout() == 'checklist' )
			<section class="page-block__checklist">
				<div class="row">
					<div class="col-12 col-md-9">
						@while ( have_rows('checklist_items') )
							@php
							the_row();
							@endphp

							<div class="checklist-items">
								<div class="checklist-item">
									@if ( get_sub_field('heading') )
									<h3>{!! get_sub_field('heading') !!}</h3>
									@endif

									@if ( get_sub_field('content') )
									<div class="checklist-item__content">
										{!! get_sub_field('content') !!}
									</div>
									@endif
								</div>
							</div>
						@endwhile
					</div>
				</div>
			</section>	

		@endif

	@endwhile

@endif	
