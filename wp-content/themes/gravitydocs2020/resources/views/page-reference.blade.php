@extends('layouts.reference')

@section('content')
  @while(have_posts()) @php the_post() @endphp
    @include('partials.content-page-reference')
  @endwhile
@endsection
