@extends('layouts.app')

@section('content')
  @while(have_posts()) @php the_post() @endphp
    @if ( post_password_required() )
      {!! get_the_password_form() !!}
    @else
      @include('partials.content-single-'.get_post_type())
    @endif
  @endwhile
@endsection
