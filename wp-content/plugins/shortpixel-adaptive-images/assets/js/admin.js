;( function( $, d, w ) {
	HTMLElement.prototype.isScrollable = function() {
		return {
			horizontal : this.scrollWidth > this.clientWidth,
			vertical   : this.scrollHeight > this.clientHeight
		}
	};

	$.fn.isScrollable = function() {
		return {
			horizontal : this.length > 0 ? this[ 0 ].scrollWidth > this[ 0 ].clientWidth : false,
			vertical   : this.length > 0 ? this[ 0 ].scrollHeight > this[ 0 ].clientHeight : false
		}
	};

	$.spaiProposeUpgrade = function( $data ) {
		//first open the popup window with the spinner
		jQuery("#spaiProposeUpgrade .spai-modal-body").addClass('spai-modal-spinner');
		jQuery("#spaiProposeUpgrade .spai-modal-body").html("");
		jQuery("#spaiProposeUpgradeShade").css("display", "block");
		jQuery("#spaiProposeUpgrade").removeClass('spai-hide');
		jQuery("#spaiProposeUpgradeShade").on('click', this.closeProposeUpgrade);
		//get proposal from server
		var browseData = { 'action': 'spai_propose_upgrade', nonce: jQuery("#spaiProposeUpgradeShade").data('ajaxnonce') };
		jQuery.ajax({
			type: "POST",
			url: jQuery("#spaiProposeUpgradeShade").data('ajaxurl'),
			data: browseData,
			success: function(response) {
				jQuery("#spaiProposeUpgrade").removeClass('spai-hide');
				jQuery("#spaiProposeUpgrade .spai-modal-body").removeClass('spai-modal-spinner');
				jQuery("#spaiProposeUpgrade .spai-modal-body").html(response);
			},
			complete: function(response, status)
			{
				//console.log(response, status);

			}
		});
	}
	$.spaiCloseProposeUpgrade = function() {
		jQuery("#spaiProposeUpgradeShade").css("display", "none");
		jQuery("#spaiProposeUpgrade").addClass('spai-hide');
		jQuery("button[data-action=spaiProposeUpgrade]").removeAttr('disabled');
		if(jQuery('.shortpixel-button-waiting').length) {
			jQuery('button[data-action=check]').click();
		}
	}

	$.spaiHelpInit = function(){
		jQuery('div.spai-inline-help span').on('click', function(elm){ jQuery.spaiHelpOpen(elm)});
		jQuery('div.spai-modal-shade').on('click', function(elm){ jQuery.spaiHelpClose()});
	}

	$.spaiHelpOpen = function(evt) {
        //$("#shortPixelProposeUpgrade .spai-modal-body").html("");
        $("#spaiHelpShade").css("display", "block");
		$("#spaiHelp").removeClass('local');
        $("#spaiHelp .spai-modal-body .local-content").addClass('hidden');
        $("#spaiHelp .spai-modal-body iframe").removeClass('hidden').attr('src',  evt.target.dataset.link);
        $("#spaiHelp").removeClass('spai-hide');
    }
	$.spaiHelpOpenLocal = function(element) {
		$("#spaiHelpShade").css("display", "block");
		$("#spaiHelp").addClass('local');
		$("#spaiHelp .spai-modal-body iframe").addClass('hidden');
		$("#spaiHelp .spai-modal-body .local-content").removeClass('hidden').append(element.clone().removeClass('hidden'));
		$("#spaiHelp").removeClass('spai-hide');
		jQuery('div.spai-modal-shade').unbind('click');
	}

    $.spaiHelpClose = function(){
        jQuery("#spaiHelpShade").css("display", "none");
        $("#spaiHelp .spai-modal-body iframe").attr('src',  'about:blank');
		$("#spaiHelp .spai-modal-body .local-content").html('');
        jQuery("#spaiHelp").addClass('spai-hide');
		jQuery('div.spai-modal-body').unbind('click');
	}

	/**
	 * Method shows scroll button if overlay is scrollable
	 *
	 * @param {boolean=false} onResize
	 */
	$.showOverlayScroll = function( onResize ) {
		var $popUpOverlay = $( '.deactivation-popup .overlay' ),
			$scrollButton = $popUpOverlay.find( '.scroll-down' );

		onResize = typeof onResize !== 'boolean' ? false : onResize;

		var shown  = $scrollButton.attr( 'data-shown' ),
			status = $scrollButton.attr( 'data-status' );

		if ( $popUpOverlay.isScrollable().vertical ) {
			if ( !shown ) {
				$scrollButton.removeClass( 'hidden' );
				$scrollButton.attr( 'data-shown', true );
			}
		}
		else {
			$scrollButton.addClass( 'hidden' );

			if ( status !== 'closed' && onResize ) {
				$scrollButton.removeAttr( 'data-shown' );
			}
		}
	};

	$.removeOverlayScroll = function() {
		var $scrollButton = $( '.scroll-down' );

		$scrollButton.addClass( 'hidden' );
		$scrollButton.attr( 'data-status', 'closed' );
	};

	$( function() {
		var $document          = $( this ),
			$window            = $( window ),
			$deactivationPopUp = $( '.deactivation-popup' ),
			$popUpOverlay      = $deactivationPopUp.find( '.overlay' );

		$.showOverlayScroll();

		$window.on( 'resize', function() {
			$.showOverlayScroll( true );
		} );

		$popUpOverlay.on( 'scroll', function() {
			$.removeOverlayScroll();
		} );

		$document.on( 'click', '.scroll-down', function() {
			$.removeOverlayScroll();

			$popUpOverlay.animate( {
				scrollTop : $popUpOverlay[ 0 ].scrollHeight - $popUpOverlay[ 0 ].clientHeight
			}, 500 );
		} );

		$document.on( 'click', '.deactivation-popup .close, .deactivation-popup', function( event ) {
			if ( this === event.target ) {
				$deactivationPopUp.addClass( 'hidden' );
			}
		} );

		$document.on( 'click', 'tr[data-slug="shortpixel-adaptive-images"] .deactivate a', function( event ) {
			event.preventDefault();
			$deactivationPopUp.removeClass( 'hidden' );
		} );

		$document.on( 'change', '.deactivation-popup input[name][type="radio"]', function() {
			var $this = $( this );

			var value = $this.val(),
				name  = $this.attr( 'name' );

			value = typeof value === 'string' && value !== '' ? value : undefined;
			name = typeof name === 'string' && name !== '' ? name : undefined;

			if ( value === undefined || name === undefined ) {
				return;
			}

			var $targetedMessage = $( 'p[data-' + name + '="' + value + '"]' ),
				$relatedSections = $this.parents( '.body' ).find( 'section[data-' + name + ']' ),
				$relatedMessages = $this.parents( '.body' ).find( 'p[data-' + name + ']:not(p[data-' + name + '="' + value + '"])' );

			$relatedMessages.addClass( 'hidden' );
			$targetedMessage.removeClass( 'hidden' );
			$relatedSections.removeClass( 'hidden' );

			$.showOverlayScroll();
		} );

		$document.on( 'keyup', '.deactivation-popup input[name], .deactivation-popup textarea[name]', function( event ) {
			var allowed = [ 'Enter', 'Escape' ];

			if ( !allowed.includes( event.key ) ) {
				return;
			}

			event.preventDefault();
			event.stopPropagation();

			if ( event.key === allowed[ 0 ] ) {
				$( '.deactivation-popup [data-action="deactivation"]' ).click();
			}
			else if ( event.key === allowed[ 1 ] ) {
				$( '.deactivation-popup .close' ).click();
			}
		} );

		$document.on( 'click', '.deactivation-popup button[data-action]', function( event ) {
			var $this            = $( this ),
				$optionsWrappers = $this.parents( '.body' ).find( '.options-wrap' ),
				$toggle          = $optionsWrappers.find( 'input[name][type="checkbox"]:checked, input[name][type="radio"]:checked' ),
				$fields          = $optionsWrappers.find( 'input[name], textarea[name]' ).not( 'input[type="checkbox"], input[type="radio"]' );

			var data = {
				action : $this.data( 'action' )
			};

			data.action = typeof data.action === 'string' && data.action !== '' ? data.action : undefined;

			if ( $toggle.length > 0 ) {
				$toggle.each( function() {
					var $this = $( this ),
						value = $this.val(),
						key   = $this.attr( 'name' );

					if ( typeof value === 'string' && value !== '' && typeof key === 'string' && key !== '' ) {
						data[ key ] = value;
					}
				} );
			}

			if ( $fields.length > 0 ) {
				$fields.each( function() {
					var $this = $( this ),
						value = $this.val(),
						key   = $this.attr( 'name' );

					if ( typeof value === 'string' && value !== '' && typeof key === 'string' && key !== '' ) {
						data[ key ] = value;
					}
				} )
			}

			$.ajax( {
				url        : typeof ajaxurl === 'string' && ajaxurl !== '' ? ajaxurl : '/wp-admin/admin-ajax.php',
				method     : 'post',
				data       : {
					action : 'shortpixel_ai_handle_feedback_action',
					data   : data
				},
				beforeSend : function() {
					$this.prop( 'disabled', true );
				},
				error      : function( error ) {
					console.log( error );
				},
				complete   : function(jqxr, textStatus) {
					$deactivationPopUp.addClass( 'hidden' );
					$this.prop( 'disabled', false );

					var $deactivateLink = $( 'tr[data-slug="shortpixel-adaptive-images"] .deactivate a' );

					if ( $deactivateLink.length > 0 ) {
						var deactivateUrl = $deactivateLink.attr( 'href' );

						if ( typeof deactivateUrl === 'string' && deactivateUrl !== '' ) {
							d.location.href = deactivateUrl;
						}
						else {
							d.location.reload();
						}
					}
				}
			} );
		} );

		$document.on( 'click', '#screen-meta [data-plugin="shortpixel-adaptive-images"][data-action]', function() {
			var $this = $( this );

			var tagName     = $this.prop( 'tagName' ),
				allowedTags = [ 'input', 'button' ],
				data        = {
					action : $this.data( 'action' )
				};

			tagName = typeof tagName === 'string' && tagName !== '' ? tagName.toLowerCase() : undefined;
			data.action = typeof data.action === 'string' && data.action !== '' ? data.action : undefined;

			$.ajax( {
				url        : typeof ajaxurl === 'string' && ajaxurl !== '' ? ajaxurl : '/wp-admin/admin-ajax.php',
				method     : 'post',
				data       : {
					action : 'shortpixel_ai_handle_help_action',
					data   : data
				},
				beforeSend : function() {
					if ( allowedTags.includes( tagName ) ) {
						$this.prop( 'disabled', true );
					}
				},
				success    : function( response ) {
					if ( response.success ) {
						if ( typeof response.reload === 'object' && !!response.reload.allowed ) {
							d.location.reload();
						}

						if ( typeof response.redirect === 'object' && typeof response.redirect.url === 'string' ) {
							d.location.href = response.redirect.url;
						}
					}
				},
				error      : function( error ) {
					console.log( error );
				},
				complete   : function() {
					if ( allowedTags.includes( tagName ) ) {
						$this.prop( 'disabled', false );
					}
				}
			} );
		} );

		// Tooltips
		if ( typeof tippy === 'function' ) {
			w.tooltips = tippy( '[data-tippy-content]', {
				animateFill : true,
				maxWidth    : 250,
				inertia     : true,
				allowHTML   : true
			} );
		}

		function popupWindow( url, title, w, h ) {
			// Fixes dual-screen position                             Most browsers      Firefox
			var dualScreenLeft = window.screenLeft !== undefined ? window.screenLeft : window.screenX;
			var dualScreenTop = window.screenTop !== undefined ? window.screenTop : window.screenY;

			var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
			var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

			var systemZoom = width / window.screen.availWidth;
			var left = ( width - w ) / 2 / systemZoom + dualScreenLeft
			var top = ( height - h ) / 2 / systemZoom + dualScreenTop
			var newWindow = window.open( url, title, 'toolbar=0,status=0,resizable=1,width=' + w / systemZoom + ',height=' + h / systemZoom + ',top=' + top + ',left=' + left );

			if ( window.focus ) newWindow.focus();
		}

		// Socials
		$document.on( 'click', '.socials-block [data-social]', function( event ) {
			event.preventDefault();

			var $this = $( this );

			popupWindow( $this.attr( 'href' ), 'Sharer', 640, 440 );
		} );

		$('.btn_topup').click(function() {
			$('.modal').on('shown.bs.modal',function(){      //correct here use 'shown.bs.modal' event which comes in bootstrap3
				$(this).find('iframe').attr('src','http://www.google.com')
			})
		})

	} );
} )( jQuery, document, window );