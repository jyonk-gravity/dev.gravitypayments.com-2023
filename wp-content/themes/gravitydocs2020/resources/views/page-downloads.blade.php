@extends('layouts.app')

@section('content')
  @while(have_posts()) @php the_post() @endphp
    {{-- @include('partials.page-header') --}}
    @if ( post_password_required() )
      {!! get_the_password_form() !!}
    @else
      @include('partials.content-page-downloads')
    @endif
  @endwhile
@endsection
