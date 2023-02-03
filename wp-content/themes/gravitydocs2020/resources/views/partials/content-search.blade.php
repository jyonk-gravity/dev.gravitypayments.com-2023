<article @php post_class() @endphp>
  <header>
    <h2 class="entry-title h4"><a href="{{ get_permalink() }}">{!! get_the_title() !!}</a></h2>
    {{-- @if (get_post_type() === 'post')
      @include('partials/entry-meta')
    @endif --}}
  </header>
	@if ( get_the_excerpt() )
	  <div class="entry-summary">
	    	{!! the_excerpt() !!}
	  </div>
	@elseif ( get_post_meta(get_the_ID(), '_yoast_wpseo_metadesc', true ) )
		<div class="entry-summary">
			{!! get_post_meta(get_the_ID(), '_yoast_wpseo_metadesc', true ) !!}
		</div>
	@endif
</article>
