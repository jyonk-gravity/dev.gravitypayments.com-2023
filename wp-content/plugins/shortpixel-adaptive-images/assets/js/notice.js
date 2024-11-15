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
	} );
} )( jQuery, document, window );