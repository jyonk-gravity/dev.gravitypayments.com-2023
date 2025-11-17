{{-- API Reference: Redoc page template --}}

<div class="reference__breadcrumb-bar">
  @php
  $button = get_field('back_button');
  $button_url = $button['url'];
  $button_title = $button['title'];
  @endphp
  <a href="/" class="breadcrumb-link mr-4"><i class="fas fa-home-alt text--secondary"></i> Home</a>
  <a href="/docs/emergepay/" class="breadcrumb-link"><i class="fas fa-chevron-circle-left text--secondary"></i> emergepay</a>
</div>

@if ( get_field('api_reference_yml_url') )
<redoc spec-url="{{ esc_url( get_field('api_reference_yml_url') ) }}" required-props-first hide-download-button></redoc>
<script src="https://cdn.redoc.ly/redoc/latest/bundles/redoc.standalone.js"></script>
@else
Error loading API reference. Please try again later.
@endif
