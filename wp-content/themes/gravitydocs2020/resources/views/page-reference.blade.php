@extends('layouts.reference')

@section('content')
  @while(have_posts()) @php the_post() @endphp
    @if ( post_password_required() )
      {!! get_the_password_form() !!}
    @else
      @include('partials.content-page-reference')
    @endif
  @endwhile
@endsection
