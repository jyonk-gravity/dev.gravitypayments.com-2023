;( function( $ ) {
	document.addEventListener( 'DOMContentLoaded', function() {
		var $chart = document.getElementById( 'chart' );

		if ( !( $chart instanceof HTMLElement ) ) {
			return false;
		}

		var $chartWrap = $chart.parentNode;

		var chartMaxDaysAmount = 14;

		// Preparing used arrays (we should slice unnecessary items)
		window.statusBox.chart.cdn.data.mb = window.statusBox.chart.cdn.data.mb.length <= chartMaxDaysAmount ? window.statusBox.chart.cdn.data.mb : window.statusBox.chart.cdn.data.mb.slice( window.statusBox.chart.cdn.data.mb.length - chartMaxDaysAmount );
		window.statusBox.chart.cdn.labels = window.statusBox.chart.cdn.labels.length <= chartMaxDaysAmount ? window.statusBox.chart.cdn.labels : window.statusBox.chart.cdn.labels.slice( window.statusBox.chart.cdn.labels.length - chartMaxDaysAmount );
		window.statusBox.chart.credits.labels = window.statusBox.chart.credits.labels.length <= chartMaxDaysAmount ? window.statusBox.chart.credits.labels : window.statusBox.chart.credits.labels.slice( window.statusBox.chart.credits.labels.length - chartMaxDaysAmount );
		window.statusBox.chart.credits.data.paid = window.statusBox.chart.credits.data.paid.length <= chartMaxDaysAmount ? window.statusBox.chart.credits.data.paid : window.statusBox.chart.credits.data.paid.slice( window.statusBox.chart.credits.data.paid.length - chartMaxDaysAmount );

		// Sorting out... is here a difference in values for datasets?
		if ( window.statusBox.chart.cdn.data.mb.length !== window.statusBox.chart.credits.data.paid.length ) {
			var difference = window.statusBox.chart.cdn.data.mb.length > window.statusBox.chart.credits.data.paid.length
				? window.statusBox.chart.cdn.data.mb.length - window.statusBox.chart.credits.data.paid.length
				: window.statusBox.chart.credits.data.paid.length - window.statusBox.chart.cdn.data.mb.length,
				emptyData  = new Array( difference ).fill( 0 ); // filling the empty array with 0 values

			if ( window.statusBox.chart.cdn.data.mb.length > window.statusBox.chart.credits.data.paid.length ) {
				window.statusBox.chart.credits.data.paid = emptyData.concat( window.statusBox.chart.credits.data.paid );
			}
			else {
				window.statusBox.chart.cdn.data.mb = emptyData.concat( window.statusBox.chart.cdn.data.mb );
			}
		}

		var labels      = window.statusBox.chart.cdn.labels.length >= window.statusBox.chart.credits.labels.length ? window.statusBox.chart.cdn.labels : window.statusBox.chart.credits.labels,
			cdnData     = window.statusBox.chart.cdn.data.mb,
			creditsData = window.statusBox.chart.credits.data.paid;

		if ( !$chartWrap.classList.contains( 'expanded' ) ) {
			labels = labels.slice( labels.length / 2 );
			cdnData = cdnData.slice( cdnData.length / 2 );
			creditsData = creditsData.slice( creditsData.length / 2 );
		}

		window.statusBox.chart.instance = new Chart( document.getElementById( 'chart' ).getContext( '2d' ), {
			type    : 'line',
			data    : {
				labels   : labels,
				datasets : [ {
					label           : window.statusBox.chart.titles.cdn,
					borderColor     : window.statusBox.chart.colors.cdn,
					backgroundColor : window.statusBox.chart.backgrounds.cdn,
					fill            : true,
					pointHitRadius  : 10,
					data            : cdnData,
					yAxisID         : 'cdn'
				}, {
					label           : window.statusBox.chart.titles.credits,
					borderColor     : window.statusBox.chart.colors.credits,
					backgroundColor : window.statusBox.chart.backgrounds.credits,
					fill            : true,
					pointHitRadius  : 10,
					data            : creditsData,
					yAxisID         : 'credits'
				} ]
			},
			options : {
				responsive : true,
				hoverMode  : 'index',
				stacked    : false,
				legend     : {
					position : 'top',
					labels   : {
						boxWidth      : 20,
						usePointStyle : true
					}
				},
				scales     : {
					yAxes : [ {
						type     : 'linear', // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
						display  : true,
						position : 'left',
						id       : 'cdn'
					}, {
						type     : 'linear', // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
						display  : true,
						position : 'right',
						id       : 'credits',

						// grid line settings
						gridLines : {
							drawOnChartArea : false // only want the grid lines for one axis to show up
						}
					} ]
				}
			}
		} );
	} );
	$(function() {
		//open and load news section on page load
		$('.box_dropdown.spai_news').addClass('opened');
		Settings.loadNews();
		if(window.spaiModeSwitchNotification) {
			Settings.showDecoratedNotice(window.spaiModeSwitchNotification);
		}
	});
	var Settings   = {},
		Exclusions = {};

	Settings.noticesQtyAtOneMoment = 3;
	Settings.previousNotices = [];

	Settings.parseTab = function( id ) {
		if ( id.indexOf( '#top' ) === 0 ) {
			id = id.replace( '#top', '' );
		}

		return id.indexOf( '#' ) === 0 ? id : false;
	};

	/**
	 * @param {jQuery} $element
	 */
	Settings.prepareValue = function( $element ) {
		var type    = $element.data( 'type' ),
			tagName = $element.prop( 'tagName' ).toLowerCase();

		switch ( type ) {
			case 'string':
				// current value of textarea retrieves using jQuery.fn.val() function
				return tagName === 'input' || tagName === 'textarea' ? $element.val() : undefined;

			case 'int':
				return tagName === 'input' ? parseInt( $element.val(), 10 ) : ( tagName === 'textarea' ? parseInt( $element.text(), 10 ) : undefined );

			case 'bool':
				return $element.prop( 'checked' );

			default:
				return undefined;
		}
	}
	Settings.showDecoratedNotice = function( noticeText ) {
		const notice = '<div class="notice notice-success is-dismissible" data-icon="none" data-causer=""\
								data-plugin="short-pixel-ai">\
				<div class="body-wrap">\
					<div class="message-wrap"><p>' + noticeText + '</p></div>\
					<div class="buttons-wrap"></div>\
				</div>\
			</div>';
		Settings.showNotice( notice );
	}
	Settings.showNotice = function( noticeBody ) {
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

	Settings.getAjaxUrl = function() {
		return typeof ajaxurl === 'string' && ajaxurl !== '' ? ajaxurl : '/wp-admin/admin-ajax.php';
	}

	Settings.topAction = function($this, action) {
		var actionTexts = {
			default : $this.html(),
			onPress : $this.attr( 'data-pressed-text' )
		};

		$.ajax( {
			method     : 'post',
			url        : Settings.getAjaxUrl(),
			data       : {
				action    : 'shortpixel_ai_handle_page_action',
				page      : 'settings',
				spainonce : $( '[name="spainonce"]' ).val(),
				data      : {
					action : action
				}
			},
			beforeSend : function() {
				//$this.addClass( 'button' );
				$this.text( actionTexts.onPress );
				$this.prop( 'disabled', true );
			},
			success    : function( response ) {
				if ( typeof response.notice === 'string' && response.notice !== '' ) {
					Settings.showNotice( response.notice );
				}
				$this.prop( 'disabled', false );
				if ( response.reload ) {
					window.location.href = 'options-general.php?page=shortpixel-ai-settings';
				}
			},
			complete   : function() {
				$this.html( actionTexts.default );
			}
		} );
	}

	Settings.save = function( event ) {
		event.preventDefault();

		var $form         = $( this ),
			$tabs         = $form.find( '.spai_settings_tab' ),
			$submitButton = $form.find( '[type="submit"]' );

		var options = {};

		var ajax = {
			url    : $form.attr( 'action' ),
			method : $form.attr( 'method' )
		};

		var submitButton = {
			text : {
				default : $submitButton.val(),
				saving  : $submitButton.data( 'saving-text' )
			}
		};

		ajax.url = !ajax.url ? Settings.getAjaxUrl() : ajax.url;
		ajax.method = !ajax.method ? 'post' : ajax.method;

		submitButton.text.default = !submitButton.text.default ? 'Save Changes' : submitButton.text.default;
		submitButton.text.saving = !submitButton.text.saving ? 'Saving...' : submitButton.text.saving;

		$tabs.each( function() {
			var $this         = $( this ),
				$optionFields = $this.find( '[name]' );

			var tab = $this.attr( 'id' );

			if ( tab ) {
				options[ tab ] = {};
			}

			$optionFields.each( function() {
				var $this = $( this );

				var type      = $this.attr( 'type' ),
					name      = $this.attr( 'name' ),
					isChecked = $this.prop( 'checked' );

				if ( type === 'radio' ) {
					if ( isChecked ) {
						options[ tab ][ name ] = Settings.prepareValue( $this );
					}
				}
				else {
					options[ tab ][ name ] = Settings.prepareValue( $this );
				}
			} );
		} );

		$.ajax( {
			url        : ajax.url,
			method     : ajax.method,
			dataType   : 'json',
			data       : {
				action    : 'shortpixel_ai_handle_page_action',
				page      : 'settings',
				spainonce : $form.find( '[name="spainonce"]' ).val(),
				data      : {
					action  : 'save',
					options : JSON.stringify( options )
				}
			},
			beforeSend : function() {
				if ( submitButton.text.saving ) {
					$submitButton.prop( 'disabled', true );
					$submitButton.val( submitButton.text.saving );
				}
			},
			success    : function( response ) {
				if ( typeof response.notice === 'string' && response.notice !== '' ) {
					Settings.showNotice( response.notice );
					if($('div.notice[data-causer="twicelossy"]').length && !$('input#lossy').is(':checked')) {
                        $('div.notice[data-causer="twicelossy"]').remove();
					}
				}
			},
			complete   : function() {
				if ( submitButton.text.default ) {
					$submitButton.prop( 'disabled', false );
					$submitButton.val( submitButton.text.default );
				}
			}
		} );
	}

	Settings.updateChartData = function() {
		var chart       = window.statusBox.chart.instance,
			labels      = window.statusBox.chart.cdn.labels.length >= window.statusBox.chart.credits.labels.length ? window.statusBox.chart.cdn.labels : window.statusBox.chart.credits.labels,
			cdnData     = window.statusBox.chart.cdn.data.mb,
			creditsData = window.statusBox.chart.credits.data.paid;

		if ( !chart.canvas.parentNode.classList.contains( 'expanded' ) ) {
			labels = labels.slice( labels.length / 2 );
			cdnData = cdnData.slice( cdnData.length / 2 );
			creditsData = creditsData.slice( creditsData.length / 2 );
		}

		chart.data.labels = labels;

		chart.data.datasets = [ {
			label           : window.statusBox.chart.titles.cdn,
			borderColor     : window.statusBox.chart.colors.cdn,
			backgroundColor : window.statusBox.chart.backgrounds.cdn,
			fill            : true,
			pointHitRadius  : 10,
			data            : cdnData,
			yAxisID         : 'cdn'
		}, {
			label           : window.statusBox.chart.titles.credits,
			borderColor     : window.statusBox.chart.colors.credits,
			backgroundColor : window.statusBox.chart.backgrounds.credits,
			fill            : true,
			pointHitRadius  : 10,
			data            : creditsData,
			yAxisID         : 'credits'
		} ];
	};
	Settings.loadNews = function () {
		if( !$( '.box_dropdown.spai_news' ).hasClass('processed') ) {
			$( '#spaiNewsFeed' ).addClass( 'loading' );
			const url = `https://shortpixel.com/blog/feed/`;

			fetch(url)
				.then(response => response.text())
				.then(str => new window.DOMParser().parseFromString(str, "text/xml"))
				.then(xml => {
					$( '#spaiNewsFeed' ).removeClass( 'loading' );
					$( '.box_dropdown.spai_news' ).addClass( 'processed' );
					const items = xml.querySelectorAll("item");
					for(let i in items) {
						renderItem(items[i]);
						if(i>1) break;
					}
					//items = Object.entries(items).slice(0,3);
					function renderItem(item) {
						const htmlDescr = item.querySelector("description").textContent;
						const div = document.createElement('div');
						div.innerHTML = htmlDescr;
						let image = div.querySelector("img");
						let img = image ? image.outerHTML : '';
						let text = div.innerText.replace('→', '').substring(0,500) + '…';

						const html = `
<article class="item">
    <h3><a href="${item.querySelector("link").innerHTML}" target="_blank">${item.querySelector("title").innerHTML}</a></h3>
    <div class="image"><a href="${item.querySelector("link").innerHTML}" target="_blank">${img}</a></div>
    <div class="description">${text}</div>
    <div class="link"><a href="${item.querySelector("link").innerHTML}" target="_blank">${document.querySelector(".box_dropdown.spai_news").getAttribute('data-readmore')}</a></div>
</article>`;
						document.querySelector('#spaiNewsFeed').innerHTML += html;
					}

				});
		}
	}
	Exclusions.validateSelector = function( selector ) {
		try {
			selector = selector.replace(/\.([^\.]*)\*/, '.$1');
			document.querySelector( selector );
			return true;
		}
		catch ( e ) {
			return false;
		}
	};

	Exclusions.prepare = function() {
		var $exclusionFields = $( '[ data-setting="exclusion"]' );

		$exclusionFields.each( function() {
			var $field = $( this ),
				texts  = $field.data( 'texts' ),
				value  = $field.val();

			if ( typeof texts !== 'object' ) {
				texts = {
					add  : 'Add',
					save : 'Save'
				};
			}

			var separator     = $field.data( 'separator' ),
				exclusionType = $field.data( 'exclusion-type' );

			separator = typeof separator === 'string' && separator !== '' ? separator : ',';
			exclusionType = typeof exclusionType === 'string' && exclusionType !== '' ? exclusionType : 'selectors';

			var exclusionSelectors     = typeof value === 'string' && value !== '' ? value.split( typeof separator === 'string' && separator !== '' ? separator : ',' ) : [],
				fakeExclusionInnerHtml = '';

			for ( var index = 0, selector = exclusionSelectors[ index ]; index < exclusionSelectors.length; selector = exclusionSelectors[ ++index ] ) {
				fakeExclusionInnerHtml += '<div data-index="' + index + '" data-action="edit"><span class="selector">' + selector + '</span><span data-action="delete"></span></div>';
			}

			var $exclusonWrap = $( '<div class="exclusion-wrap" data-type="' + exclusionType + '">' +
				'<div class="exclusions-content clearfix" data-action="add"><div class="plus"></div>' + fakeExclusionInnerHtml + '</div>' +
				'<div class="buttons-wrap hidden">' +
				'<div class="error-message hidden"></div>' +
				( exclusionType === 'urls' ? '<select><option value="path">Path</option><option value="regex">RegEx</option><option value="http">HTTP</option><option value="https">HTTPS</option><option value="domain">Domain</option></select> : ' : '' ) +
				'<input type="text"><button type="button" class="dark_blue_link" data-action="confirm" data-texts=' + JSON.stringify( texts ) + '>' + texts.add + '</button></div></div>' );

			$field.before( $exclusonWrap );
			$exclusonWrap.append( $field );
		} );
	};

	Exclusions.updateRealField = function( $field, $fakePieces ) {
		var selectors = [],
			separator = $field.data( 'separator' );

		separator = typeof separator === 'string' && separator !== '' ? separator : ',';

		if ( $fakePieces.length > 0 ) {
			$fakePieces.each( function() {
				selectors.push( $( this ).text() );
			} );
		}

		$field.val( selectors.join( separator ) );
	};

	Exclusions.updateWarningMessage = function( $message, currentQty ) {
		var limit = parseInt( $message.data( 'limit' ), 10 );

		$message.find( 'span' ).text( currentQty );

		if ( currentQty > limit ) {
			$message.slideDown( 'fast' );
		}
		else {
			$message.slideUp( 'fast' );
		}
	};

	Exclusions.inputActionsHandler = function( event ) {
		var $this          = $( this ),
			$select        = $this.siblings( 'select' ),
			$options       = $select.find( 'option' ),
			$confirmButton = $this.siblings( 'button' );

		if ( event.type === 'keypress' ) {
			var allowedKeys = [ 'Enter' ];

			if ( allowedKeys.includes( event.key ) ) {
				event.preventDefault();
				event.stopPropagation();

				$confirmButton.click();
			}
		}
		else if ( 'input' || 'focus' || 'blur' || 'change' ) {
			if ( $select.length === 0 ) {
				return;
			}

			var value         = $this.val(),
				possibleCases = [];

			if ( $options.length > 0 ) {
				$options.each( function() {
					possibleCases.push( $( this ).val() );
				} );
			}

			possibleCases.forEach( function( item ) {
				if ( value.indexOf( item + ':' ) === 0 ) {
					$this.val( value.replace( item + ':', '' ) );
					$select.val( item );
				}
			} );
		}
	}

	Exclusions.actionsHandler = function( event ) {
		event.preventDefault();
		event.stopPropagation();

		var $this               = $( this ),
			$tdWrap             = $this.parents( 'td' ),
			$exclusionWarning   = $tdWrap.find( 'p.warning' ),
			$parent             = $this.parents( '.exclusion-wrap' ),
			$textarea           = $parent.find( 'textarea' ),
			$buttonsWrap        = $parent.find( '.buttons-wrap' ),
			$errorMessage       = $buttonsWrap.find( '.error-message' ),
			$input              = $buttonsWrap.find( 'input' ),
			$select             = $buttonsWrap.find( 'select' ),
			$confirmButton      = $buttonsWrap.find( 'button' ),
			$exclusionsContent  = $parent.find( '.exclusions-content' ),
			$exclusionSelectors = $exclusionsContent.find( '.selector' ),
			$exclusionElements  = $exclusionsContent.find( 'div[data-index]' );

		var value  = $input.val(),
			texts  = $confirmButton.attr( 'data-texts' ),
			action = $this.attr( 'data-action' ),
			state  = $this.attr( 'data-state' );

		var exclusionType = $textarea.data( 'exclusion-type' );

		if( value.indexOf('<') >= 0) {
			alert("Invalid input, please check and try again.");
			return;
		}

		value = typeof value === 'string' && value !== '' ? value.trim() : '';
		action = typeof action === 'string' && action !== '' ? action : undefined;
		state = action === 'confirm' && typeof state === 'string' && state !== '' ? state : 'add';
		exclusionType = typeof exclusionType === 'string' && exclusionType !== '' ? exclusionType : 'selectors';

		try {
			texts = JSON.parse( texts );
		}
		catch ( e ) {
			texts = {
				add  : 'Add',
				save : 'Save'
			};
		}

		switch ( action ) {
			case 'add' :
				$confirmButton.text( texts.add ).attr( 'data-state', 'add' ).removeAttr( 'data-editing' );
				$input.removeClass( 'error' ).val( '' );
				$buttonsWrap.slideDown( 'fast' );
				$errorMessage.text( '' ).slideUp( 'fast' );

				// after buttons-wrap has been shown focus on input
				$input.focus();

				break;
			case 'edit' :
				var $selector = $this.find( '.selector' );
				$input.removeClass( 'error' ).val( $selector.text() );
				$confirmButton.text( texts.save ).attr( 'data-state', 'save' ).attr( 'data-editing', $this.attr( 'data-index' ) );
				$buttonsWrap.slideDown( 'fast' );
				$errorMessage.text( '' ).slideUp( 'fast' );

				// after buttons-wrap has been shown focus on input
				$input.focus();

				break;
			case 'delete' :
				$this.parents( 'div[data-index]' ).fadeOut( 'fast', function() {
					$( this ).remove();
					Exclusions.updateWarningMessage( $exclusionWarning, $tdWrap.find( '.selector' ).length );
					Exclusions.updateRealField( $textarea, $exclusionsContent.find( '.selector' ) );
				} );

				$confirmButton.removeAttr( 'data-editing' ).removeAttr( 'data-state' );
				$buttonsWrap.slideUp( 'fast' );
				$input.removeClass( 'error' ).val( '' );
				$errorMessage.text( '' ).slideUp( 'fast' );

				break;
			case 'confirm' :
				var type       = $select.val(),
					hasError   = false,
					/**
					 *
					 * @type {string} Full entered content
					 */
					inputValue = exclusionType === 'urls' ? ( type + ':' + value ) : value,
					// Split the selectors here to avoid adding multiple selectors in a row
					selectors  = exclusionType === 'urls' ? [ inputValue ] : inputValue.split( ',' );

				selectors = selectors.map( function( selector ) {
					return typeof selector === 'string' ? selector.trim() : selector;
				} );

				if ( exclusionType === 'selectors' && !Exclusions.validateSelector( value ) ) {
					$input.addClass( 'error' );
					$errorMessage.html( window.exclusionsL10n.messages.selectors.invalid ).slideDown( 'fast' );
					break;
				}

				for ( var index = 0, $selector = $( $exclusionSelectors[ index ] ); index < $exclusionSelectors.length; $selector = $( $exclusionSelectors[ ++index ] ) ) {
					var currentSelectorContent = $selector.text();

					// filter new selectors list to be selectors unique
					selectors = selectors.filter( function( selector ) {
						return ( state === 'save' && $selector.parent().attr( 'data-index' ) === $this.attr( 'data-editing' ) && currentSelectorContent === selector ) || currentSelectorContent !== selector;
					} );

					if ( selectors.length <= 0 ) {
						hasError = true;

						$input.addClass( 'error' );
						$errorMessage.html( window.exclusionsL10n.messages.selectors.alreadyExists ).slideDown( 'fast' );
						break;
					}
				}

				if ( !!hasError ) {
					break;
				}

				var newSelector = function( selector, key ) {
                    var toAdd = $( '<div data-index="' + ( id + key ) + '" data-action="edit" class="hidden"><span class="selector">' + selector + '</span><span data-action="delete"></span></div>' );
                    $exclusionsContent.append( toAdd );
                    toAdd.fadeIn( 'fast' );
                }

				if ( state === 'add' && value !== '' && selectors.length > 0 ) {
					var id = $exclusionElements.length > 0 ? parseInt( $exclusionElements.last().attr( 'data-index' ), 10 ) + 1 : 0;

					selectors.map( newSelector );
				}
				else if ( state === 'save' && value !== '' && selectors.length > 0 ) {
					var id = $this.attr( 'data-editing' );

					$exclusionsContent.find( 'div[data-index="' + id + '"] .selector' ).text( selectors.shift() );

					if ( selectors.length > 0 ) {
						id = $exclusionElements.length > 0 ? parseInt( $exclusionElements.last().attr( 'data-index' ), 10 ) + 1 : 0;

						selectors.map( newSelector );
					}
				}

				$buttonsWrap.slideUp( 'fast' );
				$input.removeClass( 'error' ).val( '' );
				$errorMessage.text( '' ).slideUp( 'fast' );

				$confirmButton.removeAttr( 'data-editing' ).removeAttr( 'data-state' );
				break;
		}

		Exclusions.updateWarningMessage( $exclusionWarning, $tdWrap.find( '.selector' ).length );
		Exclusions.updateRealField( $textarea, $exclusionsContent.find( '.selector' ) );
	};

	$( function() {
		var $document = $( this );

		if ( document.location.hash !== '' ) {
			var $soughtLink = $( 'a[href="' + document.location.hash + '"]' );

			$( '#wpspai-tabs a.nav-tab' ).removeClass( 'nav-tab-active' );
			$( '.spai_settings_tab' ).removeClass( 'active' );

			var id = Settings.parseTab( $soughtLink.attr( 'href' ) );

			if ( !!id ) {
				var $soughtTab = $( id );

				if ( $soughtTab.length > 0 ) {
					$soughtLink.addClass( 'nav-tab-active' );
					$soughtTab.addClass( 'active' );

					if ( typeof window.Beacon === 'function' && typeof window.beaconConstants === 'object' ) {
						var suggestion = $soughtTab.attr( 'id' );

						if ( typeof window.beaconConstants.suggestions[ suggestion ] !== 'undefined' ) {
							window.Beacon( 'suggest', window.beaconConstants.suggestions[ suggestion ] );
						}
					}
				}
			}
		}

		Exclusions.prepare();

		$document.on( 'click', '.exclusion-wrap [data-action]', Exclusions.actionsHandler )
		$document.on( 'change focus blur input keypress', '.exclusion-wrap input', Exclusions.inputActionsHandler );

		$document.on( 'submit', 'form#settings-form', Settings.save );

		$document.on( 'click', '.chart-wrap .toggle', function() {
			$( this ).parent().toggleClass( 'expanded' );
			Settings.updateChartData();
		} );

		$document.on( 'dblclick', '.chart-wrap canvas', function() {
			if ( window.matchMedia( '(min-width : 850px)' ).matches ) {
				$( this ).parent().toggleClass( 'expanded' );
				Settings.updateChartData();
			}
		} );

		$document.on( 'click', '#wpspai-tabs a.nav-tab', function( event ) {
			var $this = $( this );

			$( '#wpspai-tabs a.nav-tab' ).removeClass( 'nav-tab-active' );
			$( '.spai_settings_tab' ).removeClass( 'active' );

			var id = Settings.parseTab( $this.attr( 'href' ) );

			if ( !!id ) {
				var $soughtTab = $( id );

				if ( $soughtTab.length > 0 ) {
					$this.addClass( 'nav-tab-active' );
					$soughtTab.addClass( 'active' );

					if ( typeof window.Beacon === 'function' && typeof window.beaconConstants === 'object' ) {
						var suggestion = $soughtTab.attr( 'id' );

						if ( typeof window.beaconConstants.suggestions[ suggestion ] !== 'undefined' ) {
							window.Beacon( 'suggest', window.beaconConstants.suggestions[ suggestion ] );
						}
					}
				}
			}
		} );

		$document.on( 'change', 'input[type="radio"]', function() {
			var $this = $( this );

			var value = $this.val();

			if ( typeof value === 'string' && value !== '' ) {
				var $parent              = $this.parent(),
					$targetedExplanation = $parent.siblings( 'p[data-explanation="' + value + '"]' ),
					$explanationSiblings = $parent.siblings( 'p[data-explanation]' ).not( $targetedExplanation ),
					$targetedChildren    = $parent.siblings( '.children-wrap[data-parent="' + value + '"]' ),
					$childrenSiblings    = $parent.siblings( '.children-wrap[data-parent]' ).not( $targetedChildren );

				$explanationSiblings.addClass( 'hidden' );
				$targetedExplanation.removeClass( 'hidden' );
				$childrenSiblings.addClass( 'hidden' );
				$targetedChildren.removeClass( 'hidden' );
			}
		} );

        var spaiNotif = function () {
            var $this = $(this),
                $depended = $this.parent('[data-depended]'),
                $dependedSiblings = $depended.siblings('[data-depended]'),
                $dependedEnabledFields = $dependedSiblings.find('input[type="checkbox"]:checked'),
                $popUp = $this.siblings('.notification_popup');

            if ($this.hasClass('spai_triggers_notification') || ($this.prop('checked') && $popUp.length > 0)) {
                $popUp.removeClass('hidden');
                $this.prop('checked', false);
                $this.removeClass('spai_triggers_notification'); //a text box
            } else {
                var isChecked = $this.parent().find('input[data-depender]:checked').length;

                if (isChecked) {
                    $this.siblings('.children-wrap').removeClass('hidden');
                    $this.siblings('[data-depended]').find('input[type="checkbox"]').prop('checked', true);
                }
                else {
                    $this.siblings('.children-wrap').addClass('hidden');

                    if ($depended.length > 0) {
                        if ($dependedEnabledFields.length === 0) {
                            var depended = $depended.data('depended');
                            depended = typeof depended === 'string' && depended !== '' ? depended.split('|') : [];

                            var dependentFieldName = '';
                            for (let dep in depended) {
                                dependentFieldName += 'input[name="' + depended[dep] + '"],'
                            }
                            var $dependentField = $(dependentFieldName.slice(0, -1));

                            console.log($depended, $dependentField);

                            $dependentField.prop('checked', false)
                                .siblings('.children-wrap').addClass('hidden');
                        }
                    }
                }
            }
        };
		$document.on( 'change', 'input[type="checkbox"]', spaiNotif );
        $document.on( 'keydown', 'input.spai_triggers_notification', spaiNotif ); //text fields notifications
        $document.on( 'paste',   'input.spai_triggers_notification', spaiNotif );

		$document.on( 'click', '.notification_popup input[type="button"]', function() {
			var $this         = $( this ),
				$popUp        = $this.parent( '.notification_popup' ),
				$childrenWrap = $popUp.siblings( '.children-wrap' ),
				$checkBox     = $popUp.siblings( 'input[type="checkbox"]' );

			$checkBox.prop( 'checked', true );
			$childrenWrap.removeClass( 'hidden' );
			$popUp.addClass( 'hidden' );
		} );

		$document.on( 'click', '.box_dropdown .title', function() {
			$( this ).parent().toggleClass( 'opened' );
		} );

		$document.on( 'click', '.spai_statusbox_wrap .title_wrap', function() {
			if ( document.body.clientWidth <= 850 ) {
				var $this       = $( this ),
					$parent     = $this.parent(),
					$boxContent = $parent.find( '.box_content' );

				$parent.toggleClass( 'expanded' );
				$boxContent.slideToggle( 'fast' );
			}
		} );

		$document.on( 'click', '#clear_css_cache', function(event) { Settings.topAction($( this ), 'clear css cache')} );

		$document.on( 'click', '#export_settings, .export-settings', function(event) {
			var spainonce = $( '#import_spainonce' ).val();
			window.location.href = 'admin.php?page=shortpixel-ai-export-settings&spainonce=' + spainonce + '&noheader=true';
		} );

		$document.on( 'change', '#spai_settings_advanced', function(event) {
			if( !$('#spai_settings_advanced').prop('checked') && !$('#spai_settings_advanced').data('popup-confirmed') ) {
				$('#spai_settings_advanced').prop('checked', true);
				jQuery.spaiHelpOpenLocal($(event.target).siblings('.spai_settings_advanced_popup'));
			} else {
				Settings.topAction($('.shortpixel-settings-tabs'), 'enable advanced');
				$('.shortpixel-settings-wrap').fadeOut('fast');
				$('.wpf-settings').addClass('spai-loading');
			}
		} );
		$document.on( 'click', '#spaiHelp .spai_settings_advanced_popup .button-yes', function(event) {
			Settings.topAction($('.shortpixel-settings-tabs'), 'disable advanced');
			$('#spai_settings_advanced').prop('checked', false)
			$('.shortpixel-settings-wrap').fadeOut('fast');
			$('.wpf-settings').addClass('spai-loading');
		});
		$document.on( 'click', '#spaiHelp .spai_settings_advanced_popup .button-no', function(event) {
			jQuery.spaiHelpClose();
		});
		var importer;
		if(importer = document.getElementById("import_settings_file")) {
			importer.addEventListener("change", function () {
				if (this.files && this.files[0]) {
					var reader = new FileReader();
					reader.onload = (e) => {
						try {
							var parsed = JSON.parse(e.target.result);
							if(typeof parsed !== 'object') {
								throw 'noobj';
							}
							if(window.confirm("Load these settings? \n" + e.target.result)) {
								document.getElementById('import_settings_form').submit();
							}
						} catch (xx) {
							alert("This file doesn't look like a SPAI Settings export.");
						}
					};
					reader.readAsText(this.files[0]);
				}
			});
		}

		$document.on( 'submit', 'form#api-key-form', function( event ) {
			event.preventDefault();

			var $form         = $( this ),
				$fields       = $form.find( '[name]:not([type="hidden"])' ),
				$submitButton = $form.find( '[type="submit"]' );

			var ajax = {
				url    : $form.attr( 'action' ),
				method : $form.attr( 'method' )
			};

			var submitButton = {
				text : {
					default : $submitButton.val(),
					saving  : $submitButton.data( 'saving-text' )
				}
			};

			var formHasValues = false;

			$fields.each( function() {
				var $this = $( this ),
					value = $this.val();

				if ( typeof value === 'string' && value !== '' ) {
					formHasValues = true;
				}
			} );

			if ( !formHasValues ) {
				return;
			}

			ajax.url = !ajax.url ? Settings.getAjaxUrl() : ajax.url;
			ajax.method = !ajax.method ? 'post' : ajax.method;

			submitButton.text.default = !submitButton.text.default ? 'Save' : submitButton.text.default;
			submitButton.text.saving = !submitButton.text.saving ? 'Saving...' : submitButton.text.saving;

			$.ajax( {
				method     : ajax.method,
				url        : ajax.url,
				data       : $form.serialize(),
				beforeSend : function() {
					$submitButton.text( submitButton.text.saving );
					$submitButton.prop( 'disabled', true );
				},
				success    : function( response ) {
					if ( response.success ) {
						if ( response.reload ) {
							document.location.reload();
						}
					}
					else if ( response.notice ) {
						Settings.showNotice( response.notice );
					}
				},
				complete   : function() {
					$submitButton.text( submitButton.text.default );
					$submitButton.prop( 'disabled', false );
				}
			} );
		} );

		$document.on( 'click', '.spai_statusbox_wrap div:not(".dismissed-notice") [data-action], .spai_settings_tab div:not(".dismissed-notice") [data-action]', function( event ) {
			event.preventDefault();
			//dismissed notices will be processed by notice.js
			if($(event.target).closest('.dismissed-notice').length>0){
				return;
			}

			var $this = $( this );

			var action  = $this.attr( 'data-action' ),
				tagName = $this.prop( 'tagName' ).toLowerCase();

			action = typeof action === 'string' || action !== '' ? action : undefined;

			$.ajax( {
				method     : 'post',
				url        : Settings.getAjaxUrl(),
				data       : {
					action    : 'shortpixel_ai_handle_page_action',
					page      : 'settings',
					spainonce : $( '[name="spainonce"]' ).val(),
					data      : {
						action : action
					}
				},
				beforeSend : function() {
					if ( tagName === 'button' || tagName === 'input' ) {
						$this.prop( 'disabled', true );
					}
				},
				success    : function( response ) {
					if ( response.success ) {
						if ( response.reload ) {
							document.location.reload();
						}
					}

					if ( response.notice ) {
						Settings.showNotice( response.notice );
					}
				},
				complete   : function() {
					if ( tagName === 'button' || tagName === 'input' ) {
						$this.prop( 'disabled', false );
					}
				}
			} );
		} );
	} );
} )( jQuery );