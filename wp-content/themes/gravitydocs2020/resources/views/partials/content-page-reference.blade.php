{{-- API Reference: Redoc page template --}}

@if ( get_field('api_reference_yml_url') )
<redoc spec-url="{{ esc_url( get_field('api_reference_yml_url') ) }}" required-props-first hide-download-button></redoc>
<script src="https://cdn.jsdelivr.net/npm/redoc@next/bundles/redoc.standalone.js"></script>
@else
Error loading API reference. Please try again later.
@endif
