export default {
  init() {
    // JavaScript to be fired on the release notes page
    $('.release-note-filters input[type="radio"]').on('change', function(e) {
		e.preventDefault();
		let $filter_value = $(this).val();

		if ( $filter_value == 'view-all' ) {
			$('.release-note').show();
			$('.monthly-title').show();
		} else {
			// Filter by selected category
			$('.release-note').hide();
			$('.release-note[data-category="'+ $filter_value +'"]').show();

			$('.monthly-posts').each(function() {
				// If a month has no posts showing, hide the month title. Else, show the month title.
				if ( $(this).children(':visible').length == 0 ) {
					let date = $(this).data('posts-date');
					$('.monthly-title[data-title-date="'+ date +'"]').hide();
				} else {
					let date = $(this).data('posts-date');
					$('.monthly-title[data-title-date="'+ date +'"]').show();
				}
			});
		}
    });
  },
};
