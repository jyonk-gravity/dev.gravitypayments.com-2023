;( function( $ ) {
	var OnBoarding = {};

	OnBoarding.noticesQtyAtOneMoment = 3;
	OnBoarding.previousNotices = [];

	OnBoarding.showNotice = function( noticeBody ) {
		if ( typeof noticeBody !== 'string' || noticeBody === '' ) {
			return;
		}

		var $wpBodyTitle     = $( '#wpbody .wrap h1' ),
			$existingNotices = $( '.notice' ),
			$notice          = $( noticeBody ).hide();

		// pulling the result notice after the heading
		if ( $existingNotices.length > 0 ) {
			$existingNotices.last().after( $notice );
		}
		else {
			$wpBodyTitle.after( $notice );
		}

		// adding the notice to the array of added by this page
		this.previousNotices.push( $notice );

		if ( this.previousNotices.length > this.noticesQtyAtOneMoment ) {
			for ( var index = 0; index < this.previousNotices.length - this.noticesQtyAtOneMoment; index++ ) {
				var $previousNotice = this.previousNotices[ index ];

				if ( $previousNotice === $notice ) continue;

				$previousNotice.slideUp( 'fast', function() {
					$previousNotice.remove();
				} );

				this.previousNotices.splice( index, 1 );
			}
		}

		// Fires "wp-updates-notice-added" event, to WordPress could parse and add the standard dismiss button and pin "click" event on it
		$( document ).trigger( 'wp-updates-notice-added' );

		$notice.slideDown( 'fast', function() {
			$notice.removeAttr( 'style' );
			$( 'html:not(:animated), body:not(:animated)' ).animate( {
				scrollTop : $wpBodyTitle.offset().top
			}, 500 );
		} );

		setTimeout( function() {
			$notice.slideUp( 'fast', function() {
				$notice.remove();
			} );
		}, 20000 );
	};

	$( function() {
		var $document = $( this );

		$document.on( 'click', '.shortpixel-on-boarding-wrap [data-action]', function() {
			var $this       = $( this ),
				$actionWrap = $this.parents( '.action-wrap' );

			var data = {}, action = $this.attr( 'data-action' );

			data.action = typeof action === 'string' && action !== '' ? action : undefined;

			if ( $actionWrap.length > 0 ) {
				var fields = $actionWrap.find( 'input[name], textarea[name]' );

				fields.each( function() {
					var $field = $( this ), name = $field.attr( 'name' );
					data[ name ] = $field.val();
				} );
			}

			let spaiNonce = $( '.shortpixel-steps' ).attr( 'data-spainonce' );

			$.ajax( {
				method     : 'post',
				url        : 'admin-ajax.php',
				data       : {
					action    : 'shortpixel_ai_handle_page_action',
					page      : 'onBoarding',
					spainonce : spaiNonce,
					data      : data
				},
				beforeSend : function() {
					$this.prop( 'disabled', true );
				},
				success    : function( response ) {
					if ( response.success ) {
						if ( typeof response.cookie === 'string' && response.cookie !== '' && typeof window.Cookies.set === 'function' ) {
							window.Cookies.set( response.cookie, '1' );
						}

						if ( typeof response.redirect === 'object' ) {
							if ( typeof response.redirect.allowed === 'boolean' && response.redirect.allowed ) {
								document.location.href = response.redirect.url;
							}
						}
					}
					else {
						$this.removeAttr( 'disabled' );
					}

					if ( typeof response.notice === 'string' && response.notice !== '' ) {
						OnBoarding.showNotice( response.notice );
					}

					if ( !!response.reload ) {
						document.location.reload();
					}
				},
				error : function() {
					$this.removeAttr( 'disabled' );
				}
			} );
		} );

		$document.on( 'click', '.shortpixel-on-boarding-wrap .dark_blue_link', function() {
			var $this        = $( this ),
				$stepsWrap   = $( '.shortpixel-steps' ),
				$steps       = $( '.shortpixel-steps .step' ),
				$messageWrap = $( '.step-message-wrap' );

			var currentStep = parseInt( $stepsWrap.attr( 'data-step' ), 10 ),
				nextStep    = currentStep + 1 >= $steps.length ? currentStep : currentStep + 1,
				spaiNonce   = $stepsWrap.attr( 'data-spainonce' );

			$.ajax( {
				method     : 'post',
				url        : 'admin-ajax.php',
				data       : {
					action    : 'shortpixel_ai_handle_page_action',
					page      : 'onBoarding',
					spainonce : spaiNonce,
					data      : {
						    step : currentStep
					}
				},
				beforeSend : function() {
					$this.prop( 'disabled', true );
				},
				success    : function( response ) {
					if ( response.success ) {
						if ( typeof response.redirect === 'object' ) {
							if ( typeof response.redirect.allowed === 'boolean' && response.redirect.allowed ) {
								document.location.href = response.redirect.url;
							}
						}

						$stepsWrap.attr( 'data-step', nextStep );
						$steps.each( function( index ) {
							var $this = $( this );

							if ( index < nextStep ) {
								$this.removeClass( 'active' ).addClass( 'passed' );
							}
							else if ( index === nextStep ) {
								$this.addClass( 'active' );
							}
						} );

						if ( typeof response.message === 'string' && response.message !== '' ) {
							$messageWrap.fadeOut( 250, function() {
								$messageWrap.html( response.message ).fadeIn( 250 );
							} );
						}
					}
				}
			} );
		} );
	} );
} )( jQuery );