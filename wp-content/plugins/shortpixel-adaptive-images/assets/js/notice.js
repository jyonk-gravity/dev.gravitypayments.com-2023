( function( $, d, w ) {
	$( function() {
		var $document = $( this );

		var hasAjaxBeenFired = false; // flag to fire AJAX once while server won't response

		$document.on( 'click', '.notice[data-plugin="short-pixel-ai"] .buttons-wrap [data-action], .notice[data-plugin="short-pixel-ai"] button.notice-dismiss, .dismissed-notice[data-plugin="short-pixel-ai"] .buttons-wrap [data-action], .dismissed-notice[data-plugin="short-pixel-ai"] .buttons-wrap button.notice-dismiss', function() {

			var $this   = $( this ),
				$notice = $this.parents( '.notice[data-plugin="short-pixel-ai"], .dismissed-notice[data-plugin="short-pixel-ai"]' );

			// Disabling the button to prevent multiple clicks
			$this.prop( 'disabled', true );

			var causer     = $notice.attr( 'data-causer' ),
				type       = $this.attr( 'data-type' ),
				action     = $this.attr( 'data-action' ),
				additional = $this.attr( 'data-additional' );

			causer = typeof causer === 'string' && causer !== '' ? causer : undefined;
			action = typeof action === 'string' && action !== '' ? action : ( causer !== undefined && $this.hasClass( 'notice-dismiss' ) ? 'dismiss' : undefined );

			// trying to parse attribute
			try {
				additional = JSON.parse( additional );
			}
			catch ( error ) {
				additional = undefined;
			}

			if ( causer !== undefined ) {
				if(type === 'js') {
					// custom JS action
					if ( typeof jQuery[ action ] === 'function' ) {
						jQuery[ action ]( additional );
					}
				}
				else if ( !hasAjaxBeenFired ) {
					// AJAX action
					$.ajax( {
						method     : 'post',
						url        : typeof ajaxurl === 'string' && ajaxurl !== '' ? ajaxurl : '/wp-admin/admin-ajax.php',
						data       : {
							// this is AJAX action
							action : 'shortpixel_ai_handle_notice_action',
							causer : $notice.attr( 'data-causer' ),
							data   : {
								// action to be handled
								action     : action,
								additional : additional
							}
						},
						beforeSend : function() {
							hasAjaxBeenFired = true;
						},
						success    : function( response ) {
							$notice.slideUp( 'fast', function() {
								if ( typeof response.notice === 'string' && response.notice !== '' ) {
									var $resultNotice = $( response.notice ).hide();

									$notice.after( $resultNotice );

									// Fires "wp-updates-notice-added" event, to WordPress could parse and add the standard dismiss button and pin "click" event on it
									$document.trigger( 'wp-updates-notice-added' );

									$resultNotice.slideDown( 'fast', function() {
										$resultNotice.removeAttr( 'style' );
									} );
								}

								$notice.remove();
							} );

							if ( typeof response.redirect !== 'undefined' && !!response.redirect.allowed ) {
								if ( typeof response.redirect.url === 'string' && response.redirect.url !== '' ) {
									if ( !!response.redirect.blank ) {
										w.open( response.redirect.url );
									}
									else {
										d.location.href = response.redirect.url;
									}
								}
							}

							if ( typeof response.reload !== 'undefined' && !!response.reload.allowed ) {
								d.location.reload();
							}
						},
						complete   : function() {
							hasAjaxBeenFired = false;
						}
					} );
				}
			}
		} );
		// feedback survey handler
		$document.on( 'click', '.notice[data-causer="recommend_survey"] .survey-rating-btn', function(e){
			e.preventDefault();
			var $btn    = $( this ),
				rating  = $btn.data('rating'),
				$notice = $btn.closest('.notice[data-causer="recommend_survey"]');

			$notice.find('.survey-rating-btn').removeClass('selected');
			$btn.addClass('selected');
			// send (only) rating first
			$.post( ajaxurl, {
				action: 'shortpixel_ai_send_rating',
				data:   { rating: rating }
			});
			// for 9–10: show inline thank-you + review prompt for WP pagre
			if ( rating >= 9 ) {
				$notice.find('h3, .message-wrap > p').slideUp();

				var promptHtml =
					'<p>Thank you for your high rating! ' +
					'If you have a moment, the ShortPixel team would be very happy ' +
					'if you could leave a short review for our plugin.</p>';

				var reviewLinkHtml =
					'<p>' +
					'<button id="survey-feedback-submit" class="button button-primary" '+
					'onclick="window.open(\'https://wordpress.org/support/plugin/shortpixel-adaptive-images/reviews/?filter=5\', \'_blank\')">' +
					'Leave a review' +
					'</button>' +
					'</p>';

				$notice
					.find('#survey-feedback-area')
					.html( promptHtml + reviewLinkHtml )
					.slideDown();

				return;
			}
			// 1–8: close title + buttons & open feedback area
			$notice.find('h3').slideUp();
			$notice.find('.survey-rating-btn').closest('p').slideUp();
			var prompt = rating <= 4
				? 'Thank you for your feedback! If you have encountered any issues with our plugin, we are more than eager to help solving them! Please give us more details:'
				: 'Thank you for your feedback! Please let us know what we can improve:';

			$notice.find('#survey-feedback-prompt').text( prompt );
			$notice.find('#survey-feedback-area').slideDown();
		});

		// now, when click on submit button send 2nd ajax with feedback
		$document.on( 'click', '.notice[data-causer="recommend_survey"] #survey-feedback-submit', function(e){
			e.preventDefault();
			var $notice      = $( this ).closest( '.notice[data-causer="recommend_survey"]'),
				rating       = $notice.find('.survey-rating-btn.selected').data('rating'),
				feedbackText = $notice.find('#survey-feedback').val() || '';

			$.post( ajaxurl, {
				action: 'shortpixel_ai_send_feedback',
				data:   {
					rating:   rating,
					feedback: feedbackText
				}
			})
				.done(function( resp ){
						var $msgWrap = $notice.find('.message-wrap');
						if ( resp.success ) {
							$msgWrap.html('<p>Thank you for your feedback!</p>');
							$notice.find('#survey-feedback-area, .buttons-wrap').slideUp();
						}
						else {
							var error = resp.data || 'Something went wrong sending feedback!';
							$msgWrap.html('<p>' + error + '</p>');
					}
				});
		});

	} );
} )( jQuery, document, window );