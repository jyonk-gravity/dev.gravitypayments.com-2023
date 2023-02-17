<header class="banner">
  <div class="container-fluid">
  	<div class="row align-items-center">
  		<div class="col-8 col-lg-4">
    		<a class="brand" href="{{ home_url('/') }}"><img src="@asset('images/gravity-documentation-logo.svg')" alt="Gravity Payments Developer Documentation"></a>
    		{{-- @if ( is_page('reference') )
    		<a href="/" class="btn btn-light btn-sm ml-3 px-3 d-inline-flex align-items-center" style="border-radius: 40px; min-width: 0;"><i class="far fa-angle-left mr-1"></i> <span>Back</span></a>
    		@endif --}}
    	</div>
    	<div class="d-none d-lg-block col-4 justify-content-center">
    	@if ( !is_page('reference') )
    		<div class="header__search d-flex">
    			{!! do_shortcode("[wd_asp elements='search' ratio='100%' id=1]") !!}
	    		{{-- <form role="search" method="get" class="search-form" action="http://dev.gravity2020dev.local/">
					<label>
						<span class="screen-reader-text">Search for:</span>
						<input type="search" class="search-field" placeholder="Search …" value="" name="s" data-swplive="true" data-swpengine="default" data-swpconfig="default" autocomplete="off" aria-describedby="searchwp_live_search_results_5f638b39c3c1f_instructions" aria-owns="searchwp_live_search_results_5f638b39c3c1f" aria-autocomplete="both"><p class="searchwp-live-search-instructions screen-reader-text" id="searchwp_live_search_results_5f638b39c3c1f_instructions">When autocomplete results are available use up and down arrows to review and enter to go to the desired page. Touch device users, explore by touch or with swipe gestures.</p>
						<button type="submit" class="search-submit"><i class="far fa-search"></i></button>
					</label>
				</form> --}}
	    	</div>
    	@endif
    	</div>
    	<div class="d-none d-lg-block col-4 justify-content-end">
    		<div class="header__nav">
			    <nav class="nav-primary">
			      @if (has_nav_menu('primary_navigation'))
			        {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'nav']) !!}
			      @endif
			    </nav>
			</div>
		</div>
		<div class="d-lg-none col-4 justify-content-end text-right text-light">
			<a href="#" class="btn--mobile-trigger--search mr-3"><i class="far fa-search"></i></a>
			<a href="#" class="btn--mobile-trigger"><i class="far fa-bars"></i></a>
		</div>
  </div>

  <div class="mobile-menu d-lg-none">
  	<div class="mobile-menu__inner">
  		<div class="mobile-menu__close-button text-right">
  			<a href="#" class="btn--mobile-trigger--close"><i class="fal fa-times"></i></a>
  		</div>
	  	@if (has_nav_menu('primary_navigation'))
	    	{!! wp_nav_menu(['theme_location' => 'mobile_navigation', 'menu_class' => 'nav']) !!}
	  	@endif
  	</div>
  </div>

  <div class="mobile-search d-lg-none">
  	<div class="mobile-search__close-button">
  		<a href="#" class="btn--mobile-trigger--search--close"><i class="fal fa-times"></i></a>
  	</div>
  	<div class="mobile-search__inner d-flex align-items-start justify-content-center">
  		{!! do_shortcode("[wd_asp elements='search' ratio='100%' id=1]") !!}
  	</div>
  </div>
</header>
