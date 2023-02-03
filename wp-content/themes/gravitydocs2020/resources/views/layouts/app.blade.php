<!doctype html>
<html {!! get_language_attributes() !!}>
  @include('partials.head')
  <body @php body_class() @endphp>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5PMH993"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    @php do_action('get_header') @endphp
    @include('partials.header')
    <div class="wrap" role="document">
      @include('partials.main-sidebar')

      @if ( is_singular('docs') )
      <div class="docs-content">
      @else
      <div class="page-content">
      @endif
        <main class="main">
          <div class="main__content">
            @yield('content')
          </div>
        </main>
      </div>
    </div>
    @php do_action('get_footer') @endphp
    @include('partials.footer')
    @php wp_footer() @endphp
  </body>
</html>
