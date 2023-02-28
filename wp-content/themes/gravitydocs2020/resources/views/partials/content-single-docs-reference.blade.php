@php
// Get current URL
$req_uri = $_SERVER['REQUEST_URI'];

// Get everything after domain
$page_path = substr($req_uri,0,strrpos($req_uri,'/'));
@endphp

<article @php post_class() @endphp>
  <div class="entry-content">
    @if ( get_field('back_button') )
    <div class="reference__breadcrumb-bar">
      @php
      $button = get_field('back_button');
      $button_url = $button['url'];
      $button_title = $button['title'];
      @endphp
      <a href="/" class="breadcrumb-link mr-4"><i class="fas fa-home-alt text--secondary"></i> Home</a>

      <a href="{{ esc_url( $button_url ) }}" class="breadcrumb-link"><i class="fas fa-chevron-circle-left text--secondary"></i> {!! $button_title !!}</a>
    </div>
    @endif

    @if ( get_field('api_reference_url') )
    <redoc spec-url="{{ esc_url( get_field('api_reference_url') ) }}" required-props-first hide-download-button></redoc>
    <script src="https://cdn.jsdelivr.net/npm/redoc@next/bundles/redoc.standalone.js"></script>
    @else
    Error loading API reference. Please try again later.
    @endif
  </div>    
</article>
