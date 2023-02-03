export default {
  init() {
    // JavaScript to be fired on all pages
    let $path = window.location.pathname;
    let $path_name = $path.substring(0, $path.lastIndexOf('/') + 1);

    // Dropdown Section
    $('.dropdown-section__select').on('change', function() {
      let $dropdown_section = $(this).val();
      let $selection_text = this.options[this.selectedIndex].text;
      let $section_title = $(this).parents('.doc-block__dropdown-section').find('.dropdown-section__heading').text();

      console.log('Documentation Page: '+ $path_name);
      console.log('Documentation Dropdown Section ID: '+ $(this).data('content-block-id'));
      console.log('Documentation Dropdown Section Title:'+ $section_title);
      console.log('Documentation Dropdown Value: '+ $selection_text);

      // wp.mixpanel.track('Dropdown Selection Change', {
      // 'Documentation Page': $path_name,
      // 'Documentation Dropdown Location': $(this).data('content-block-id'),
      // 'Documentation Dropdown Value': $dropdown_section});

      $('.dropdown-section__content-blocks__block').removeClass('active');      
      $('.dropdown-section__content-blocks__block[data-blockid="'+ $dropdown_section +'"]').addClass('active');

    });

    // Mobile Menu
    $('.btn--mobile-trigger, .btn--mobile-trigger--close').on('click', function(e) {
      e.preventDefault();

      $('.mobile-menu').toggleClass('active');
    });

    $('.btn--mobile-trigger--search, .btn--mobile-trigger--search--close').on('click', function(e) {
      e.preventDefault();

      $('.mobile-search').toggleClass('active');
    });

    $('.mobile-trigger--main-sidebar--open-close').on('click', function(e) {
      e.preventDefault();

      $('.main-sidebar').toggleClass('active');
      $('.mobile-trigger--main-sidebar').toggleClass('open');
    });

  },
  finalize() {
    // JavaScript to be fired on all pages, after page specific JS is fired
  },
};
