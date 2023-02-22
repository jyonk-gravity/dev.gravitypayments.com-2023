<aside class="table-of-contents">
  <h5>Page Contents</h5>

  @if ( have_rows('doc_content_blocks') )
  <ul>
    @while ( have_rows('doc_content_blocks') )
      @php the_row(); @endphp

      @if ( get_row_layout() == 'text' )

        @if ( !get_sub_field('hide_from_table_of_contents') )
          @if ( get_sub_field('heading') )
            @php
            $content_block_id = get_sub_field('heading');
            //Lower case everything
            $content_block_id = strtolower($content_block_id);
            //Make alphanumeric (removes all other characters)
            $content_block_id = preg_replace("/[^a-z0-9_\s-]/", "", $content_block_id);
            //Clean up multiple dashes or whitespaces
            $content_block_id = preg_replace("/[\s-]+/", " ", $content_block_id);
            //Convert whitespaces and underscore to dash
            $content_block_id = preg_replace("/[\s_]/", "-", $content_block_id);
            @endphp

            <li><a href="#{{ $content_block_id }}" class="table-of-contents__link">{!! get_sub_field('heading') !!}</a></li>
          @endif
        @endif
      @endif
    @endwhile
  </ul>
  @endif
</aside>