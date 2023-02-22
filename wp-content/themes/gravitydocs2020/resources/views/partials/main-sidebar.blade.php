<aside class="main-sidebar">
  <div class="mobile-trigger--main-sidebar">
    <a href="#" class="mobile-trigger--main-sidebar--open-close">
      <i class="far fa-chevron-right"></i>
    </a>
  </div>

  @php
  global $post;
  $current_page = $post->post_name;
  @endphp

  @if ( is_page() || is_search() )
    <a href="/" class="breadcrumb-link"><i class="fas fa-home-alt text--secondary"></i> Home</a>

    @if ( is_page('release-notes') )
      <h4 class="main-sidebar__section-title">Filter Release Notes</h4>

      @php
      $release_notes_categories = get_terms( array(
          'taxonomy' => 'release_notes_category',
          'hide_empty' => true,
        )
      );
      @endphp 

      @if ( $release_notes_categories )
      <form class="release-note-filters">
        <div class="form-check">
          <input type="radio" id="view-all" class="form-check-input" value="view-all" name="release-notes-category-filter" checked>
          <label for="view-all" class="form-check-label">View All</label>
        </div>
        @foreach ( $release_notes_categories as $category )
        <div class="form-check">
          <input type="radio" id="{{ $category->slug }}" class="form-check-input" value="{{ $category->slug }}" name="release-notes-category-filter">
          <label for="{{ $category->slug }}" class="form-check-label">{!! $category->name !!}</label>
        </div>
        @endforeach
      </form>
      @endif

    @elseif ( is_page('downloads') || is_page('signup') || is_page('developer-support') || is_page('thanks') )

    @else 
    <nav class="nav--sidebar">
      @if (has_nav_menu('sidebar_navigation'))
        {!! wp_nav_menu(['theme_location' => 'sidebar_navigation', 'menu_class' => 'nav']) !!}
      @endif
    </nav>
    @endif

  @elseif ( is_singular() && !$post->post_parent )
    <a href="/" class="breadcrumb-link"><i class="fas fa-home-alt text--secondary"></i> Home</a>
    @php
      $args = array(
        'posts_per_page' => -1,
        'post_parent' => $post->ID,
        'post_type' => 'docs',
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'post_status' => 'publish'
      );
      $children = get_children($args);
    @endphp

    @if ( !get_field('hide_sidebar_nav') )
      @if ( $children )
      <h4 class="main-sidebar__section-title">{!! get_the_title( $post->post_parent) !!}</h4>
      <nav class="main-sidebar__child-pages">
        @foreach ( $children as $child_page )
          @if ( !get_field( 'hide_in_left_sidebar_nav', $child_page->ID ) )
            <a href="{{ esc_url( get_the_permalink( $child_page->ID ) ) }}">{!! get_the_title( $child_page->ID ) !!}</a>
          @endif
        @endforeach
      </nav>
      @endif

      @if ( have_rows('custom_links') )
        <nav class="main-sidebar__child-pages">
          @while ( have_rows('custom_links') )
            @php 
            the_row(); 

            $link = get_sub_field('custom_link');
            $link_url = $link['url'];
            $link_title = $link['title'];
            $link_target = $link['target'] ? $link['target'] : '_self';

            var_dump( $link );
            @endphp

            <a href="{{ esc_url($link_url) }}" target="{{ esc_attr($link_target) }}">{!! $link_title !!}</a>
          @endwhile
        </nav>
      @endif
    @endif

    @if ( get_field('include_demo_link') )
      @php
      $link = get_field('demo_link');
      $link_url = $link['url'];
      $link_title = $link['title'];
      $link_target = $link['target'] ? $link['target'] : '_self';
      @endphp
      <a href="{{ esc_url($link_url) }}" target="{{ esc_attr($link_target) }}" class="demo-link"><i class="fas fa-cog"></i> {!! $link_title !!} <i class="far fa-external-link"></i></a>
    @endif

    @if ( get_field('include_api_link') )
      @php
      $link = get_field('api_link');
      $link_url = $link['url'];
      $link_title = $link['title'];
      $link_target = $link['target'] ? $link['target'] : '_self';
      @endphp
      <a href="{{ esc_url($link_url) }}" target="{{ esc_attr($link_target) }}" class="api-link"><i class="fas fa-file-code"></i> {!! $link_title !!}</a>
    @endif

  @elseif ( is_singular() && $post->post_parent )
    <div class="home-link mb-4">
      <a href="/" class="breadcrumb-link"><i class="fas fa-home-alt text--secondary"></i> Home</a>
    </div>

    <a href="{{ esc_url( get_the_permalink( $post->post_parent ) ) }}" class="breadcrumb-link"><i class="fas fa-chevron-circle-left text--secondary"></i> {!! get_the_title( $post->post_parent) !!}</a>

    @php
      $args = array(
        'posts_per_page' => -1,
        // 'post_parent' => $post->ID,
        'post_parent' => $post->post_parent,
        'post_type' => 'docs',
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'post_status' => 'publish'
      );
      $children = get_children($args);
    @endphp

    @if ( !get_field('hide_sidebar_nav') )
      @if ( $children )
      <nav class="main-sidebar__child-pages">
        @foreach ( $children as $child_page )
          @if ( !get_field( 'hide_in_left_sidebar_nav', $child_page->ID ) )
          @php
          $page_slug = $child_page->post_name;
          @endphp
          <a href="{{ esc_url( get_the_permalink( $child_page->ID ) ) }}" class="child-page-link {{ $page_slug === $current_page ? 'child-page-link--current' : '' }}">{!! get_the_title( $child_page->ID ) !!}</a>
          @endif
        @endforeach
      </nav>
      @endif

      @if ( have_rows('custom_links') )
        <nav class="main-sidebar__child-pages">
          @while ( have_rows('custom_links') )
            @php 
            the_row(); 

            $link = get_sub_field('custom_link');
            $link_url = $link['url'];
            $link_title = $link['title'];
            $link_target = $link['target'] ? $link['target'] : '_self';
            @endphp

            <a href="{{ esc_url($link_url) }}" target="{{ esc_attr($link_target) }}">{!! $link_title !!}</a>
          @endwhile
        </nav>
      @endif
    @endif

    @if ( get_field('include_demo_link') )
      @php
      $link = get_field('demo_link');
      $link_url = $link['url'];
      $link_title = $link['title'];
      $link_target = $link['target'] ? $link['target'] : '_self';
      @endphp
      <a href="{{ esc_url($link_url) }}" target="{{ esc_attr($link_target) }}" class="demo-link"><i class="fas fa-cog"></i> {!! $link_title !!} <i class="far fa-external-link"></i></a>
    @endif

    @if ( get_field('include_api_link') )
      @php
      $link = get_field('api_link');
      $link_url = $link['url'];
      $link_title = $link['title'];
      $link_target = $link['target'] ? $link['target'] : '_self';
      @endphp
      <a href="{{ esc_url($link_url) }}" target="{{ esc_attr($link_target) }}" class="api-link"><i class="fas fa-file-code"></i> {!! $link_title !!}</a>
    @endif
  @endif

</aside>