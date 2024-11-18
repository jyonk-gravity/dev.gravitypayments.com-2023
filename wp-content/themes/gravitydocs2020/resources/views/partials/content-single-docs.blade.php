@php
// Get current URL
$req_uri = $_SERVER['REQUEST_URI'];

// Get everything after domain
$page_path = substr($req_uri,0,strrpos($req_uri,'/'));
@endphp

@if ( get_field('background_image') )
  @php
  $image = get_field('background_image');
  $image_url = $image['sizes']['large'];
  $image_alt = $image['alt'];

  $image_opacity = get_field('background_image_opacity');
  $image_opacity = $image_opacity / 100;

  $image_top_gap = get_field('background_image_top_gap');
  @endphp
@endif

<article @php post_class() @endphp>
  @if ( get_field('background_image') )
  <div class="doc__background-image" style="background-image: url({{ esc_url($image_url) }}); opacity: {{ $image_opacity }}; {{ $image_top_gap ? 'top: 40px;' : '' }}">
    @if ( !empty($image_alt) )
    <span role="img" aria-label="{{ $image_alt }}"></span>
    @endif
  </div>
  @endif

  <div class="entry-content">
    <div class="row">
      <div class="col-lg-9">
        @if ( !get_field('hide_page_title') )
        <header class="doc-block__header">
          {{-- @if ( is_singular() && $post->post_parent )
            @php
            $ancestors = get_post_ancestors( $post->ID );
            $root = count( $ancestors ) - 1;
            $parent = $ancestors[$root];
            @endphp
          <h3 class="eyebrow-text">{!! get_the_title( $parent ) !!}</h3>
          @endif --}}

          {!! easy_breadcrumbs($post) !!}
          <h1 class="entry-title">{!! get_the_title() !!}</h1>
          {{-- @include('partials/entry-meta') --}}
        </header>
        @endif
        
        @if ( have_rows('doc_content_blocks') )

          @php $section_id = 1; @endphp

          @while ( have_rows('doc_content_blocks') )
            @php the_row() @endphp

            @if ( get_row_layout() == 'text' )
              @php
              $content_block_id = '';

              if ( get_sub_field('heading') ) {
                $content_block_id = get_sub_field('heading');
                //Lower case everything
                $content_block_id = strtolower($content_block_id);
                //Make alphanumeric (removes all other characters)
                $content_block_id = preg_replace("/[^a-z0-9_\s-]/", "", $content_block_id);
                //Clean up multiple dashes or whitespaces
                $content_block_id = preg_replace("/[\s-]+/", " ", $content_block_id);
                //Convert whitespaces and underscore to dash
                $content_block_id = preg_replace("/[\s_]/", "-", $content_block_id);
              }
              @endphp

              <section id="{{ $content_block_id }}" class="doc-block__text" style="padding-bottom: 2rem;" data-content-block-id="{{ $section_id }}">
                @if ( $content_block_id != '' && !get_sub_field('hide_from_table_of_contents') )
                <a href="{{ esc_url( get_the_permalink() . '#' . $content_block_id ) }}" class="section-link"><i class="far fa-link"></i></a>
                @endif

                @if ( get_sub_field('eyebrow_text') )
                  <h3 class="eyebrow-text">{!! get_sub_field('eyebrow_text') !!}</h3>
                @endif

                @if ( get_sub_field('heading') )
                  <h2>{!! get_sub_field('heading') !!}</h2>
                @endif

                @if ( get_sub_field('subheading') )
                  <p class="lead">{!! get_sub_field('subheading') !!}</p>
                @endif

                @if ( get_sub_field('content') )
                  <div class="doc-block__text__content">{!! get_sub_field('content') !!}</div>
                @endif
              </section>  

            @elseif ( get_row_layout() == 'info_box' )
              @php
              $label = get_sub_field('info_box_type');
              @endphp

              <section class="doc-block__info-box" style="margin-bottom: 2rem;" data-content-block-id="{{ $section_id }}">
                <div class="info-box__label info-box__label--{{ $label }}">
                  @if ( $label == 'info' )
                    <i class="fas fa-info"></i>
                  @elseif ( $label == 'tip' )
                    <i class="fas fa-check"></i>
                  @elseif ( $label == 'warning' )
                    <i class="fas fa-exclamation"></i>
                  @elseif ( $label == 'alert' )
                    <i class="fas fa-hand-paper"></i>
                  @endif
                </div>

                <div class="info-box__inner">
                  @if ( get_sub_field('info_box_heading') )
                    <h3>{!! get_sub_field('info_box_heading') !!}</h3>
                  @endif

                  @if ( get_sub_field('info_box_content') )
                    <div class="info-box__content">
                      {!! get_sub_Field('info_box_content') !!}
                    </div>
                  @endif
                </div>
              </section>

            @elseif ( get_row_layout() == 'media' )

              <section class="doc-block__media" style="padding-bottom: 2rem;" data-content-block-id="{{ $section_id }}">
                @if ( get_sub_field('media_type') == 'image' )
                  @php
                    $image = get_sub_field('image');
                    $image_url = $image['sizes']['large'];
                    $image_alt = $image['alt'];
                  @endphp
                  <img src="{{ esc_url( $image_url ) }}" alt="{{ esc_attr( $image_alt ) }}" class="img-fluid">
                @elseif ( get_sub_field('media_type') == 'video')
                  <div class="embed-responsive embed-responsive-16by9">
                    <iframe class="embed-responsive-item" src="{{ esc_url( get_sub_field('video', false, false) ) }}"></iframe>
                  </div>
                @endif
              </section>

            @elseif ( get_row_layout() == 'numbered_list' )

              <section class="doc-block__numbered-list" style="padding-bottom: 2rem;" data-content-block-id="{{ $section_id }}">
                <ol class="numbered-list">
                @while ( have_rows('list_items') )
                  @php
                    the_row();
                  @endphp

                  <li class="numbered-list__item">
                    @if ( get_sub_field('list_item_heading') )
                      <h3>{!! get_sub_field('list_item_heading') !!}</h3>
                    @endif

                    @if ( get_sub_field('list_item_content') )
                      <div class="numbered-list__item__content">
                        {!! get_sub_field('list_item_content') !!}
                      </div>
                    @endif
                  </li>

                @endwhile
                </ol>
              </section>

            @elseif ( get_row_layout() == 'code_box' )

              <section class="doc-block__code-box" style="padding-bottom: 2rem;" data-content-block-id="{{ $section_id }}">
                <ul class="nav nav-tabs code-box__nav-tabs">
                @php
                $counter = 0;
                @endphp

                @while ( have_rows('code_samples') )
                  @php
                    the_row();

                    $coding_language = get_sub_field('coding_language');
                    
                    if ( get_sub_field('custom_title') ) {
                      $coding_language_label = get_sub_field('custom_title');
                    } else {
                      $coding_language_label = $coding_language['label'];
                    }

                    $coding_language_value = $coding_language['value'];
                  @endphp

                  <li class="nav-item">
                    {{-- <a class="nav-link {{ $counter == 0 ? 'active' : '' }}" data-toggle="tab" href="#{{ $coding_language_value }}--{{ $counter }}--{{ $section_id }}">{!! $coding_language_label !!}</a> --}}

                    <a class="nav-link {{ $counter == 0 ? 'active' : '' }}" data-toggle="tab"
                        href="#{{ $coding_language_value }}--{{ $counter }}--{{ $section_id }}">{!! $coding_language_label !!}
                    </a>
                  </li>

                  @php
                  $counter++;
                  @endphp
                @endwhile
                </ul>

                
                <div class="tab-content">
                @php
                $counter = 0;
                @endphp

                @while ( have_rows('code_samples') )
                  @php
                    the_row();

                    $coding_language = get_sub_field('coding_language');
                    $coding_language_value = $coding_language['value'];
                    $code_sample = str_replace('<','&lt;', get_sub_field('code_sample'));
                    $code_sample = str_replace('>','&gt;', $code_sample);

                  @endphp

                  <div id="{{ $coding_language_value }}--{{ $counter }}--{{ $section_id }}" class="tab-pane {{ $counter == 0 ? 'active' : '' }}">
                    <pre class="line-numbers"><code class="language-{{ $coding_language_value }}">{!! $code_sample !!}</code></pre>
                  </div>

                  @php
                  $counter++;
                  @endphp
                @endwhile
                </div>
              </section>

            @elseif ( get_row_layout() == 'table' )
              <section class="doc-block__table" style="padding-bottom: 2rem;" data-content-block-id="{{ $section_id }}">
                <div class="table-responsive">
                  @php
                  $table_style = get_sub_field('table_style');
                  $table_class = 'table';
                  $table_head_class = '';

                  if ( $table_style == 'table--block-color' ) {
                    $table_class = 'table table-striped';
                    $table_head_class = 'thead-dark';
                  }

                  $table = get_sub_field('table');
                  echo '<table border="0" class="'. $table_class .'">';

                      if ( ! empty( $table['header'] ) ) {

                          echo '<thead class="' . $table_head_class . '">';

                              echo '<tr>';

                                  foreach ( $table['header'] as $th ) {

                                      echo '<th>';
                                          echo nl2br( $th['c'] );
                                      echo '</th>';
                                  }

                              echo '</tr>';

                          echo '</thead>';
                      }

                      echo '<tbody>';

                          foreach ( $table['body'] as $tr ) {

                              echo '<tr>';

                                  foreach ( $tr as $td ) {

                                      echo '<td>';
                                          echo nl2br( $td['c'] );
                                      echo '</td>';
                                  }

                              echo '</tr>';
                          }

                      echo '</tbody>';

                  echo '</table>';
                  @endphp
                </div>
              </section>

            @elseif ( get_row_layout() == 'divider_line' )
              <section class="doc-block__divider-line" style="margin: 3rem 0;" data-content-block-id="{{ $section_id }}">
                <hr class="divider--long divider--dark divider--thick">
              </section>


            @elseif ( get_row_layout() == 'dropdown_section' )
              <section class="doc-block__dropdown-section" data-content-block-id="{{ $section_id }}">
                @php $dropdown_section_id = 0; @endphp

                @if ( get_sub_field('heading') )
                <h2 class="dropdown-section__heading">{!! get_sub_field('heading') !!}</h2>
                @endif

                @if ( get_sub_field('content') )
                <div class="dropdown-section__content">
                  {!! get_sub_field('content') !!}
                </div>
                @endif

                @if ( have_rows('dropdowns') )
                  <form>
                    <select class="dropdown-section__select custom-select" data-content-block-id="{{ $section_id }}">
                      <option>Make a selection</option>
                    @while ( have_rows('dropdowns') )
                      @php 
                      the_row(); 

                      $option_value = get_sub_field('heading');
                      //Lower case everything
                      $option_value = strtolower($option_value);
                      //Make alphanumeric (removes all other characters)
                      $option_value = preg_replace("/[^a-z0-9_\s-]/", "", $option_value);
                      //Clean up multiple dashes or whitespaces
                      $option_value = preg_replace("/[\s-]+/", " ", $option_value);
                      //Convert whitespaces and underscore to dash
                      $option_value = preg_replace("/[\s_]/", "-", $option_value);
                      @endphp

                      <option value="{{ $option_value }}">{!! get_sub_field('heading') !!}</option>
                    @endwhile
                    </select>
                  </form>
                @endif

                @if ( have_rows('dropdowns') )
                <div class="dropdown-section__content-blocks">
                  @while ( have_rows('dropdowns') )
                    @php 
                    the_row(); 

                    $option_value = get_sub_field('heading');
                    //Lower case everything
                    $option_value = strtolower($option_value);
                    //Make alphanumeric (removes all other characters)
                    $option_value = preg_replace("/[^a-z0-9_\s-]/", "", $option_value);
                    //Clean up multiple dashes or whitespaces
                    $option_value = preg_replace("/[\s-]+/", " ", $option_value);
                    //Convert whitespaces and underscore to dash
                    $option_value = preg_replace("/[\s_]/", "-", $option_value);
                    @endphp

                    <div class="dropdown-section__content-blocks__block" data-blockid="{{ $option_value }}">
                      <h3>{!! get_sub_field('heading') !!}</h3>
                      @if ( have_rows('content_blocks') )
                        @while ( have_rows('content_blocks') )
                          @php the_row(); @endphp

                          @if ( get_row_layout() == 'text' )
                            <div class="doc-block__text">
                              @if ( get_sub_field('heading') )
                              <h3>{!! get_sub_field('heading') !!}</h3>
                              @endif
                              {!! get_sub_field('content') !!}
                            </div>

                          @elseif ( get_row_layout() == 'info_box')
                            @php
                            $label = get_sub_field('info_box_type');
                            @endphp

                            <div class="doc-block__info-box" style="margin-bottom: 2rem;">
                              <div class="info-box__label info-box__label--{{ $label }}">
                                @if ( $label == 'info' )
                                  <i class="fas fa-info"></i>
                                @elseif ( $label == 'tip' )
                                  <i class="fas fa-check"></i>
                                @elseif ( $label == 'warning' )
                                  <i class="fas fa-exclamation"></i>
                                @elseif ( $label == 'alert' )
                                  <i class="fas fa-hand-paper"></i>
                                @endif
                              </div>

                              <div class="info-box__inner">
                                @if ( get_sub_field('info_box_heading') )
                                  <h3>{!! get_sub_field('info_box_heading') !!}</h3>
                                @endif

                                @if ( get_sub_field('info_box_content') )
                                  <div class="info-box__content">
                                    {!! get_sub_Field('info_box_content') !!}
                                  </div>
                                @endif
                              </div>
                            </div>

                          @elseif ( get_row_layout() == 'code_box' )
                            <div class="doc-block__code-box" style="padding-bottom: 2rem;">
                              <ul class="nav nav-tabs code-box__nav-tabs">
                              @php
                              $counter = 0;
                              @endphp

                              @while ( have_rows('code_samples') )
                                @php
                                  the_row();

                                  $coding_language = get_sub_field('coding_language');

                                  if ( get_sub_field('custom_title') ) {
                                    $coding_language_label = get_sub_field('custom_title');
                                  } else {
                                    $coding_language_label = $coding_language['label'];
                                  }

                                  $coding_language_value = $coding_language['value'];
                                @endphp

                                <li class="nav-item">
                                  {{-- <a class="nav-link {{ $counter == 0 ? 'active' : '' }}" data-toggle="tab" href="#{{ $coding_language_value }}--{{ $section_id }}{{ $dropdown_section_id }}">{!! $coding_language_label !!}</a> --}}

                                  <a class="nav-link {{ $counter == 0 ? 'active' : '' }}" data-toggle="tab"
                                      href="#{{ $coding_language_value }}--{{ $section_id }}{{ $dropdown_section_id }}">{!! $coding_language_label !!}
                                  </a>
                                </li>



                                @php
                                $counter++;
                                @endphp
                              @endwhile
                              </ul>

                              
                              <div class="tab-content">
                              @php
                              $counter = 0;
                              @endphp

                              @while ( have_rows('code_samples') )
                                @php
                                  the_row();

                                  $coding_language = get_sub_field('coding_language');
                                  $coding_language_value = $coding_language['value'];
                                  $code_sample = str_replace('<','&lt;', get_sub_field('code_sample'));
                                  $code_sample = str_replace('>','&gt;', $code_sample);
                                @endphp

                                <div id="{{ $coding_language_value }}--{{ $section_id }}{{ $dropdown_section_id }}" class="tab-pane {{ $counter == 0 ? 'active' : '' }}">
                                  <pre class="line-numbers"><code class="language-{{ $coding_language_value }}">{!! $code_sample !!}</code></pre>
                                </div>

                                @php
                                $counter++;
                                @endphp
                              @endwhile
                              </div>
                            </div>

                          @elseif ( get_row_layout() == 'table' )
                            <div class="doc-block__table" style="padding-bottom: 2rem;">
                              <div class="table-responsive">
                                @php
                                $table_style = get_sub_field('table_style');
                                $table_class = 'table';
                                $table_head_class = '';

                                if ( $table_style == 'table--block-color' ) {
                                  $table_class = 'table table-striped';
                                  $table_head_class = 'thead-dark';
                                }

                                $table = get_sub_field('table');
                                echo '<table border="0" class="'. $table_class .'">';

                                    if ( ! empty( $table['header'] ) ) {

                                        echo '<thead class="' . $table_head_class . '">';

                                            echo '<tr>';

                                                foreach ( $table['header'] as $th ) {

                                                    echo '<th>';
                                                        echo nl2br( $th['c'] );
                                                    echo '</th>';
                                                }

                                            echo '</tr>';

                                        echo '</thead>';
                                    }

                                    echo '<tbody>';

                                        foreach ( $table['body'] as $tr ) {

                                            echo '<tr>';

                                                foreach ( $tr as $td ) {

                                                    echo '<td>';
                                                        echo nl2br( $td['c'] );
                                                    echo '</td>';
                                                }

                                            echo '</tr>';
                                        }

                                    echo '</tbody>';

                                echo '</table>';
                                @endphp
                              </div>
                            </div>    
                          @endif
                      @endwhile
                      </div>
                    @endif

                    @php $dropdown_section_id = $dropdown_section_id + 1; @endphp
                  @endwhile
                </div>
                @endif

              </section>

            @elseif ( get_row_layout() == "tiles" )
              <section class="doc-block__tiles" style="padding-bottom: 2rem;" data-content-block-id="{{ $section_id }}">
                <div class="row">
                  @php
                  $layout = get_sub_field('layout');
                  @endphp

                  @while ( have_rows('tiles') )
                    @php 
                    the_row(); 
                    @endphp

                    @if ( $layout == 'tiles--two-up' )
                    <div class="col-md-6 mb-3">
                    @else
                    <div class="col-md-6 col-lg-4 mb-3">
                    @endif
                      <a href="{{ esc_url(get_sub_field('tile_link')) }}" class="tile">
                        @if ( get_sub_field('tile_image') )
                          @php
                          $image = get_sub_field('tile_image');
                          $image_url = $image['sizes']['large'];
                          $image_alt = $image['alt'];
                          @endphp
                          
                          <div class="tile__image mb-4">
                            <img src="{{ esc_url( $image_url ) }}" class="img-fluid" alt="{{ $image_alt }}">
                          </div>
                        @endif

                        <h3 class="h4 tile__heading">{!! get_sub_field('tile_heading') !!}</h3>
                        
                        @if ( get_sub_field('tile_content') )
                        <div class="tile__content">{!! get_sub_field('tile_content') !!}</div>
                        @endif
                      </a>
                    </div>
                  @endwhile
                </div>
              </section>

            @elseif ( get_row_layout() == 'checklist' )
              <section class="doc-block__checklist" data-content-block-id="{{ $section_id }}">
                <div class="row">
                  <div class="col-12 col-md-9">
                    @while ( have_rows('checklist_items') )
                      @php
                      the_row();
                      @endphp

                      <div class="checklist-items">
                        <div class="checklist-item">
                          @if ( get_sub_field('heading') )
                          <h3>{!! get_sub_field('heading') !!}</h3>
                          @endif

                          @if ( get_sub_field('content') )
                          <div class="checklist-item__content">
                            {!! get_sub_field('content') !!}
                          </div>
                          @endif
                        </div>
                      </div>
                    @endwhile
                  </div>
                </div>
              </section>

            @elseif ( get_row_layout() == 'error_codes_table' )
              <section class="doc-block__error-codes-table" data-content-block-id="{{ $section_id }}">
                <div class="table-responsive">
                  <table border="0" class="table table--not-fixed">
                    <thead>
                      <tr>
                        <th>Request Code</th>
                        <th>Status Code</th>
                        <th>Error Message</th>
                        <th>Endpoint</th>
                        <th>Cause</th>
                      </tr>
                    </thead>

                    <tbody>
                    @while ( have_rows('error_codes') )
                      @php
                      the_row()
                      @endphp

                      <tr>
                        <td>{!! get_sub_field('request_code') !!}</td>
                        <td>{!! get_sub_field('status_code') !!}</td>
                        <td>{!! get_sub_field('error_message') !!}</td>
                        <td>{!! get_sub_field('endpoint') !!}</td>
                        <td>{!! get_sub_field('cause') !!}</td>
                      </tr>

                    @endwhile
                    </tbody>

                  </table>
                </div>
              </section>
            
            @elseif ( get_row_layout() == 'timeline' )
              <section class="doc-block__timeline">
                @php
                $step_counter = 1;
                @endphp

                @while ( have_rows('steps') )
                  @php the_row(); @endphp
                  <div class="row align-items-lg-center pb-5">
                    <div class="col-lg-6">
                      <h2><span class="step-counter">{{ $step_counter }}</span> {!! get_sub_field('step_heading') !!}</h2>
                      <div class="step__content">
                        {!! get_sub_field('step_content') !!}
                      </div>
                    </div>
                    @if ( get_sub_field('step_image') )
                    @php 
                    $image_id = get_sub_field('step_image')['ID'];
                    $image_alt = get_sub_field('image')['alt'];
                    @endphp
                    <div class="col-lg-6">
                      <img class="img-fluid" {{ awesome_acf_responsive_image( $image_id,'large','960px' ) }} alt="{{ esc_attr($image_alt) }}">
                    </div>
                    @endif
                  </div>

                  @php
                  $step_counter++;
                  @endphp 
                @endwhile

                <div class="timeline__end"><i class="fas fa-check-circle"></i></div>
              </section>

            @elseif ( get_row_layout() == 'api_reference' )
              <section class="doc-block__api-reference">
                <redoc spec-url="{{ esc_url( get_sub_field('api_reference_url') ) }}" required-props-first hide-download-button></redoc>
                <script src="https://cdn.jsdelivr.net/npm/redoc@next/bundles/redoc.standalone.js"></script>
              </section>

              <style>
                .single-docs.reference .main-sidebar {
                  display: none;
                }

                .single-docs.reference .docs-content {
                  margin-left: 0;
                  padding: 0;
                }

                .single-docs.reference .doc-block__header {
                  display: none;
                }
              </style>
            @endif

            @php $section_id = $section_id + 1; @endphp
          @endwhile

        @endif

      </div>
      @if ( get_field('include_table_of_contents') )
      <div class="col-lg-3 d-none d-lg-block">
        @include('partials.table-of-contents')
      </div>
      @endif
    </div>    
  </div>
  <footer class="footer--docs">
    <div class="row">
      <div class="col-md-7">
        <div class="footer__helpful-form">
          {!! do_shortcode('[gravityform id="2" title="false" description="false"]') !!}
        </div>
        <p class="text-muted">
          @php 
          $last_modified = get_the_modified_date( 'F j, Y' ); 
          @endphp
          <small><strong>Last Modified:</strong> {!! $last_modified !!}</small>
        </p>
      </div>
    </div>
  </footer>
</article>
