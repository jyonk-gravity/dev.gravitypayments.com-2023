@extends('layouts.reference')

@section('content')
  @while(have_posts()) @php the_post() @endphp
    @include('partials.content-single-docs-reference')
  @endwhile
@endsection
