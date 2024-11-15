;( function() {
	window.SPAIFront = function() {
		this.regExp = {
			hoverRule     : /.+::?hover\s.+/,
			backgroundUrl : /url\((?:`|'|")?([^`'"\)]+\.(?:jpe?g|png|gif)|data:image\/svg\+xml;base64[^`'"\)]+)(`|'|"|)?\s*\)/,
			imageUrl      : /(?:https?:\\?\/\\?\/|\\?\/\\?\/)[^\s'"`]*\.(?:jpe?g|png|gif)/gmu
		};

		this.recommendedOptions = {
			'lazy-load-backgrounds' : false, // lazy-load backgrounds in inline styles
			'parse-css'             : false, // parse CSS option
			'parse-json'            : false, // parse in JSON blocks
			'parse-js'              : false, // parse in JS blocks
			'hover-handling'        : false // images hover handling
		};

		this.enabledOptions = {};

		this.deferredSelectors = [];

		this.skippedURLs = [];

		this.cssFilesContent = [];
	};

	SPAIFront.prototype.htmlToElement = function( html ) {
		var template = document.createElement( 'template' );
		html = html.trim(); // Never return a text node of whitespace as the result
		template.innerHTML = html;
		return template.content.firstChild;
	}

	/**
	 * Method checks is the presented URL is an inline image
	 *
	 * @param {string} url
	 * @returns {boolean}
	 */
	SPAIFront.prototype.isOtherInlineImage = function( url ) {
		if(url.indexOf( 'data:image' ) === 0) {
			let parsed = typeof ShortPixelAI.replacerService === 'object' ? ShortPixelAI.replacerService.parseInlinePseudoSrc(url) : ShortPixelAI.parsePseudoSrc(url);
			if(parsed.src)
				return false;
		}
		return typeof url !== 'string' || url === '' ? false : url.indexOf( 'data:image' ) === 0;
	};

	SPAIFront.prototype.getCSSRules = function( styleContent ) {
		var doc          = document.implementation.createHTMLDocument( '' ),
			styleElement = document.createElement( 'style' );

		styleElement.textContent = styleContent;
		// the style will only be parsed once it is added to a document
		doc.body.appendChild( styleElement );

		return styleElement.sheet.cssRules;
	};

	SPAIFront.prototype.parseStyles = function( styleContent ) {
		var rules = window.SPAIFrontWorker.getCSSRules( styleContent );

		if ( rules.length > 0 ) {
			for ( var index = 0, rule = rules[ index ]; index < rules.length; rule = rules[ ++index ] ) {

				if ( typeof rule.style === 'object' && typeof rule.selectorText === 'string' && rule.selectorText !== '' ) {
					if ( window.SPAIFrontWorker.regExp.hoverRule.test( rule.selectorText ) && rule.style.display !== '' ) {
						var excludedSelectors = rule.selectorText.split( ',' );

						excludedSelectors.forEach( function( selector ) {
							selector = selector.trim();

							if ( window.SPAIFrontWorker.regExp.hoverRule.test( selector ) ) {
								selector = selector.replace( /::?hover/, '' );
								window.SPAIFrontWorker.deferredSelectors.push( selector );
							}
						} );
					}
				}

				if ( !window.SPAIFrontWorker.recommendedOptions[ 'parse-css' ] ) {
					if ( typeof rule.style === 'object' && rule.style.backgroundImage !== '' && window.SPAIFrontWorker.regExp.backgroundUrl.test( rule.style.backgroundImage ) ) {
						var match = window.SPAIFrontWorker.regExp.backgroundUrl.exec( rule.style.backgroundImage );

						if ( match !== null ) {
							// second group is a url without "url(" at the start and )" at the end
							if ( !window.SPAIFrontWorker.isOtherInlineImage( match[ 1 ] )
								&& match[ 1 ].indexOf( window.SPAIFrontConstants.folderUrls.plugins ) === -1
								&& match[ 1 ].indexOf( window.SPAIFrontConstants.folderUrls.includes ) === -1 ) {
								window.SPAIFrontWorker.recommendedOptions[ 'parse-css' ] = true;
							}
						}
					}
				}
			}
		}
	};

	SPAIFront.prototype.parseImgURLs = function() {
		var $img = document.querySelectorAll( 'img' );

		if ( $img.length > 0 ) {
			$img.forEach( function( element, index ) {
				var source = element.getAttribute( 'src' );

				source = typeof source !== 'string' ? '' : source;

				if ( typeof window.SPAIFrontConstants === 'object' ) {
					var hasApiURL = source.indexOf( window.SPAIFrontConstants.apiUrl ) >= 0;

					if ( !hasApiURL ) {
						var imgInformation = {
							element : element,
							url     : source
						};

						if ( window.SPAIFrontWorker.skippedURLs instanceof Array ) {
							window.SPAIFrontWorker.skippedURLs.push( imgInformation );
						}
					}
				}
			} );
		}
	}

	SPAIFront.prototype.parseCSS = function() {
		var $styles   = document.querySelectorAll( 'style' ),
			$cssLinks = document.querySelectorAll( 'link[rel="stylesheet"]' );

		if ( $styles.length === 0 && $cssLinks.length === 0 ) {
			return;
		}

		$cssLinks.forEach( function( $element ) {
			var fileUrl = $element.getAttribute( 'href' );

			//   v--      HS#42141       --v
			if ( typeof fileUrl === 'string'
				//Makes no sense to check inside the CSS that is already on CDN
				&& fileUrl.indexOf(ShortPixelAI.getApiUrl().split('/').splice(0,4).join('/')) < 0
				&& fileUrl.indexOf( document.location.host ) >= 0
				&& fileUrl.indexOf( document.location.host + '/' + window.SPAIFrontConstants.folderUrls.includes ) === -1
				&& fileUrl.indexOf( document.location.host + '/' + window.SPAIFrontConstants.folderUrls.content + window.SPAIFrontConstants.folderUrls.plugins ) === -1
				&& /\.css/.test( fileUrl ) ) {

				var xhr = new XMLHttpRequest();

				xhr.onloadend = function() {
					if ( window.SPAIFrontWorker.cssFilesContent ) {
						window.SPAIFrontWorker.cssFilesContent.push( {
							url     : fileUrl,
							content : xhr.response
						} );
					}

					window.SPAIFrontWorker.parseStyles( xhr.response );
				};

				xhr.open( 'get', fileUrl, false );
				try {
					xhr.send();
				} catch(err){
					console.log(err.name + ' thrown	 by xhr.send: ' + err.message);
				}
			}
		} );

		$styles.forEach( function( $element ) {
			window.SPAIFrontWorker.parseStyles( $element.textContent );
		} );

	}

	SPAIFront.prototype.parseInlineStyles = function() {
		var $elements = document.querySelectorAll( 'div, span, body, article, section, i, p, h1, h2, h3, h4, h5, h6, form, img, figure, a' );

		if ( $elements.length > 0 ) {
			for ( var index = 0, $element = $elements[ index ]; index < $elements.length; $element = $elements[ ++index ] ) {
				if ( $element.style.backgroundImage !== '' && window.SPAIFrontWorker.regExp.backgroundUrl.test( $element.style.backgroundImage ) ) {
					window.SPAIFrontWorker.recommendedOptions[ 'lazy-load-backgrounds' ] = true;
					break;
				}
			}
		}
	};

	SPAIFront.prototype.parseScripts = function() {
		var $scripts = document.querySelectorAll( 'script' );

		if ( $scripts.length > 0 ) {
			for ( var index = 0, $script = $scripts[ index ]; index < $scripts.length; $script = $scripts[ ++index ] ) {
				if ( $script.textContent !== '' ) {
					// set the start point of regex to the zero
					SPAIFrontWorker.regExp.imageUrl.lastIndex = 0;

					do {
						if ( window.SPAIFrontWorker.regExp.imageUrl.test( $script.textContent ) ) {
							var scriptType = $script.getAttribute( 'type' );

							if ( typeof scriptType === 'string' && scriptType !== ''
								&& ( scriptType.indexOf( 'application/ld+json' ) !== -1
									|| scriptType.indexOf( 'application/json' ) !== -1 ) ) {

                                //for ld+json, let's check if it's not a structured data JS, in this case we don't recommend
								var val = true;
								if(scriptType.indexOf( 'application/ld+json' ) !== -1) {
									try {
                                        var parsed = JSON.parse($script.innerText);
                                        if(parsed['@context'] !== undefined && parsed['@graph'] !== undefined) {
                                            val = false;
                                        }
									} catch (whatever) {
									}
								}
								window.SPAIFrontWorker.recommendedOptions[ 'parse-json' ] = val;
							}
							else {
								window.SPAIFrontWorker.recommendedOptions[ 'parse-js' ] = true;
							}

							// if image url has been found break the testing loop
							break;
						}
					}
					while ( window.SPAIFrontWorker.regExp.imageUrl.lastIndex >= $script.textContent.length )

					// if all all recommended options have been proposed break the script parsing loop
					if ( !!window.SPAIFrontWorker.recommendedOptions[ 'parse-json' ] && !!window.SPAIFrontWorker.recommendedOptions[ 'parse-js' ] ) {
						break;
					}
				}
			}
		}
	};

	SPAIFront.prototype.testDeferredSelectors = function() {
		if ( window.SPAIFrontWorker.deferredSelectors.length === 0 ) {
			return;
		}

		window.SPAIFrontWorker.deferredSelectors.forEach( function( selector ) {
			var $elements = document.querySelectorAll( selector );

			if ( $elements.length > 0 ) {
				$elements.forEach( function( $element ) {
					if ( $element instanceof HTMLElement && $element.tagName === 'IMG' ) {
						window.SPAIFrontWorker.recommendedOptions[ 'hover-handling' ] = true;
					}
				} );
			}
		} );
	}

	SPAIFront.prototype.prepareOptions = function() {
		var $optionsWrap        = document.querySelector( '.spai-fw-sidebar__body .spai__recommended-options-wrap' ), // Options wrap
			$options            = $optionsWrap.querySelectorAll( '.option' ), // Options by themselves
			$hasRecommendations = document.querySelector( '.spai-fw-sidebar__body .has-recommendations' ), // "Has recommendations" wrap
			$noRecommendations  = document.querySelector( '.spai-fw-sidebar__body .no-recommendations' ), // "No recommendations" wrap
			$noOptionsMessage   = document.querySelector( '.spai-fw-sidebar__body .no-options' ), // "No recommendations" wrap
			$popUp              = document.querySelector( '.spai-popup__wrap' ); // Very first time popup

		var hasRecommendations = false,
			allOptionsEnabled  = true,
			hasOptions         = $options instanceof NodeList && $options.length > 0;

		if ( $popUp instanceof HTMLElement ) {
			$popUp.classList.add( 'hidden' );
		}

		for ( var optionType in window.SPAIFrontWorker.recommendedOptions ) {
			var $option = document.querySelector( '.spai__recommended-options-wrap .option[data-option="' + optionType + '"]' );
			if ($option === null) continue;

			var	$toggle = $option.querySelector( 'input[name="' + optionType + '"]' );

			if ( $option instanceof HTMLElement ) {
				if ( !window.SPAIFrontWorker.recommendedOptions[ optionType ] ) {
					$option.parentNode.removeChild( $option );
				}
				else {
					// register all enabled options
					window.SPAIFrontWorker.enabledOptions[ optionType ] = $toggle.checked;

					$option.classList.remove( 'hidden' );

					if ( $toggle instanceof HTMLElement ) {
						if ( !$toggle.checked ) {
							hasRecommendations = true;
						}
					}
					else {
						hasRecommendations = true;
					}
				}
			}
		}

		for ( var optionType in window.SPAIFrontWorker.enabledOptions ) {
			if ( window.SPAIFrontWorker.enabledOptions[ optionType ] === false ) {
				allOptionsEnabled = false;
				break;
			}
		}

		if ( hasRecommendations && hasOptions ) {
			if ( $hasRecommendations instanceof HTMLElement ) {
				$hasRecommendations.classList.remove( 'hidden' );
			}
		}
		else if ( !hasRecommendations && hasOptions ) {
			if ( $noRecommendations instanceof HTMLElement ) {
				$noRecommendations.classList.remove( 'hidden' );
			}
		}

		if ( allOptionsEnabled ) {
			if ( $noOptionsMessage instanceof HTMLElement ) {
				$noOptionsMessage.classList.remove( 'hidden' );
			}
		}

		document.dispatchEvent( new Event( 'show-sidebar' ) );
	};

	SPAIFront.prototype.scrollToBottom = function() {
		var intervalID;

		// scrolling to the bottom of the page
		intervalID = setInterval( function() {
			if ( window.scrollY + window.screen.availHeight < document.body.scrollHeight ) {
				window.scrollTo( 0, window.scrollY + window.screen.availHeight > document.body.scrollHeight ? document.body.scrollHeight : window.scrollY + window.screen.availHeight );
			}
			else {
				clearInterval( intervalID );
				document.dispatchEvent( new Event( 'run-check' ) );
				window.scrollTo( 0, 0 );
			}
		}, 100 );
	}

	SPAIFront.prototype.init = function() {
	    if(typeof ShortPixelAI === 'undefined') {
	        setTimeout(SPAIFront.prototype.init, 200);
	        return;
        }
		var $siteContentWrap = document.createElement( 'div' ),
			$sideBar         = document.querySelector( '.spai-fw-sidebar' );

		$siteContentWrap.className = 'spai-fw-body-content';

		document.addEventListener( 'show-sidebar', function() {
			// Move the body's children into this wrapper
			while ( document.body.firstChild ) {
				$siteContentWrap.appendChild( document.body.firstChild );
			}

			// Append the wrapper to the body
			document.body.appendChild( $siteContentWrap );

			// Insert before the body's content
			document.body.insertBefore( $sideBar, $siteContentWrap );

			// Removing the class to show sidebar
			document.body.classList.remove( 'spai-fw-sidebar-hidden' );
		} );

		window.SPAIFrontWorker = new SPAIFront();

		window.SPAIFrontWorker.scrollToBottom();
	}

    if (document.readyState === "complete") {
        setTimeout(SPAIFront.prototype.init, 1);
    } else {
        if (document.addEventListener) {
            document.addEventListener("DOMContentLoaded", SPAIFront.prototype.init, false);
        } else {
            document.attachEvent("onreadystatechange", SPAIFront.prototype.init);
        }
    }
} )();

;( function() {
	var xhrHasBeenSent = false;

	document.addEventListener( 'run-check', function() {
		window.SPAIFrontWorker.parseImgURLs();
		window.SPAIFrontWorker.parseInlineStyles();
		window.SPAIFrontWorker.parseCSS();
		window.SPAIFrontWorker.parseScripts();
		window.SPAIFrontWorker.testDeferredSelectors();
		window.SPAIFrontWorker.prepareOptions();
	} );

	document.addEventListener( 'change', function( event ) {
		var $target = event.target;

		if ( $target.matches( '.option input.tgl' ) ) {
			var $activateButtons    = $target.parentElement.parentElement.querySelectorAll( '.option input.tgl' ),
				$revertMessageWrap  = $target.parentElement.parentElement.querySelector( '.revert-confirmation' ),
				$generalButtonsWrap = $target.parentElement.parentElement.parentElement.querySelector( '.buttons-wrap[data-mission="general"]' ),
				$cacheWarnMessage   = $target.parentElement.parentElement.querySelector( '.cache-message' ),
				$hasRecommendations = $target.parentElement.parentElement.querySelector( '.has-recommendations' ),
				$noRecommendations  = $target.parentElement.parentElement.querySelector( '.no-recommendations' ),
				$noOptionsMessage   = $target.parentElement.parentElement.querySelector( '.no-options' );

			var recommendedOptionsEnabled = true;

			if ( $target.checked === false ) {
				if ( $revertMessageWrap instanceof HTMLElement ) {
					$target.checked = true;
					$target.parentElement.parentElement.insertBefore( $revertMessageWrap, $target.parentElement.nextSibling );
					$revertMessageWrap.setAttribute( 'data-option', $target.getAttribute( 'name' ) );
					$revertMessageWrap.classList.remove( 'hidden' );
					return false;
				}
			}

			if ( $activateButtons.length > 0 ) {
				$activateButtons.forEach( function( $button ) {
					$button.disabled = true;
				} );
			}

			if ( $cacheWarnMessage instanceof HTMLElement ) {
				$cacheWarnMessage.classList.remove( 'hidden' );
			}

			var xhr      = new XMLHttpRequest(),
				formData = new FormData(),
				option   = $target.parentElement.getAttribute( 'data-option' ),
				value    = $target.checked;

			formData.append( 'action', 'shortpixel_ai_handle_page_action' );
			formData.append( 'page', 'frontWorker' );
			formData.append( 'spainonce', document.querySelector('.spai__recommended-options-wrap').dataset.spainonce );
			formData.append( 'data[option]', option );
			formData.append( 'data[value]', value );

			xhr.onloadend = function() {
				var response = JSON.parse( xhr.response );

				if ( response.success ) {
					if ( $activateButtons.length > 0 ) {
						$activateButtons.forEach( function( $button ) {
							if ( $button.checked === false ) {
								recommendedOptionsEnabled = false;
							}
							$button.disabled = false;
						} );
					}

					if ( $generalButtonsWrap instanceof HTMLElement ) {
						$generalButtonsWrap.classList.remove( 'hidden' );
					}

					if ( !!option ) {
						window.SPAIFrontWorker.enabledOptions[ option ] = $target.checked;
					}

					for ( var optionType in window.SPAIFrontWorker.enabledOptions ) {
						if ( window.SPAIFrontWorker.enabledOptions[ optionType ] === false && $noOptionsMessage instanceof HTMLElement ) {
							$noOptionsMessage.classList.add( 'hidden' );
							break;
						}
					}

					if ( !!recommendedOptionsEnabled ) {
						if ( $hasRecommendations instanceof HTMLElement ) {
							$hasRecommendations.classList.add( 'hidden' );
						}

						if ( $noRecommendations instanceof HTMLElement ) {
							$noRecommendations.classList.remove( 'hidden' );
						}
					}
					else {
						if ( $hasRecommendations instanceof HTMLElement ) {
							$hasRecommendations.classList.remove( 'hidden' );
						}

						if ( $noRecommendations instanceof HTMLElement ) {
							$noRecommendations.classList.add( 'hidden' );
						}
					}
				}

				xhrHasBeenSent = false;
			}

			if ( !xhrHasBeenSent ) {
				let ajaxUrl = ShortPixelAI.getAjaxUrl();
				xhr.open( 'post', !ajaxUrl ? document.location.origin + '/wp-admin/admin-ajax.php' : ajaxUrl );
				xhr.send( formData );

				xhrHasBeenSent = true;
			}
		}
	} );

	document.addEventListener( 'click', function( event ) {
		var $target = event.target;

		if ( $target.matches( '.spai-fw-sidebar__footer .collapse-button' ) || $target.matches( '.spai-fw-sidebar__footer .collapse-button *' ) ) {
			// hiding the sidebar
			document.body.classList.add( 'spai-fw-sidebar-hidden' );
		}

		if ( $target.matches( '.spai-fw-maximize-button' ) || $target.matches( '.spai-fw-maximize-button *' ) ) {
			// showing the sidebar
			document.body.classList.remove( 'spai-fw-sidebar-hidden' );
		}

		if ( $target.matches( '.buttons-wrap button.cancel' ) ) {
			$target.parentElement.parentElement.removeAttribute( 'data-option' );
			$target.parentElement.parentElement.classList.add( 'hidden' );
		}

		if ( $target.matches( '.buttons-wrap button.reload' ) ) {
			$target.parentElement.classList.add( 'hidden' );
			document.location.reload();
		}

		if ( $target.matches( '.buttons-wrap button.revert' ) ) {
			var option = $target.parentElement.parentElement.getAttribute( 'data-option' );
			var $soughtCheckbox     = document.querySelector( 'input[name="' + option + '"]' ),
				$cacheWarnMessage   = $target.parentElement.parentElement.parentElement.querySelector( '.cache-message' ),
				$noOptionsMessage   = $target.parentElement.parentElement.parentElement.querySelector( '.no-options' ),
				$hasRecommendations = $target.parentElement.parentElement.parentElement.querySelector( '.has-recommendations' ),
				$noRecommendations  = $target.parentElement.parentElement.parentElement.querySelector( '.no-recommendations' ),
				$generalButtonsWrap = $target.parentElement.parentElement.parentElement.parentElement.querySelector( '.buttons-wrap[data-mission="general"]' ),
				$activateButtons    = $target.parentElement.parentElement.parentElement.querySelectorAll( '.option input.tgl' );

			if ( $activateButtons.length > 0 ) {
				$activateButtons.forEach( function( $button ) {
					$button.disabled = true;
				} );
			}

			if ( $soughtCheckbox instanceof HTMLElement ) {
				var xhr      = new XMLHttpRequest(),
					formData = new FormData();

				formData.append( 'action', 'shortpixel_ai_handle_page_action' );
				formData.append( 'page', 'frontWorker' );
				formData.append( 'spainonce', document.querySelector('.spai__recommended-options-wrap').dataset.spainonce );
				formData.append( 'data[option]', option );
				formData.append( 'data[value]', false );

				xhr.onloadend = function() {
					var response = JSON.parse( xhr.response );

					if ( response.success ) {
						$soughtCheckbox.checked = false;
						$target.parentElement.parentElement.removeAttribute( 'data-option' );
						$target.parentElement.parentElement.classList.add( 'hidden' );

						if ( $activateButtons.length > 0 ) {
							$activateButtons.forEach( function( $button ) {
								$button.disabled = false;
							} );
						}

						if ( $generalButtonsWrap instanceof HTMLElement ) {
							$generalButtonsWrap.classList.remove( 'hidden' );
						}

						if ( $cacheWarnMessage instanceof HTMLElement ) {
							$cacheWarnMessage.classList.remove( 'hidden' );
						}

						if ( !!option ) {
							window.SPAIFrontWorker.enabledOptions[ option ] = false;
						}

						if ( $noOptionsMessage instanceof HTMLElement ) {
							$noOptionsMessage.classList.add( 'hidden' );
						}

						if ( $hasRecommendations instanceof HTMLElement ) {
							$hasRecommendations.classList.remove( 'hidden' );
						}

						if ( $noRecommendations instanceof HTMLElement ) {
							$noRecommendations.classList.add( 'hidden' );
						}
					}

					xhrHasBeenSent = false;
				}

				if ( !xhrHasBeenSent ) {
                    let ajaxUrl = ShortPixelAI.getAjaxUrl();
                    xhr.open( 'post', !ajaxUrl ? document.location.origin + '/wp-admin/admin-ajax.php' : ajaxUrl );
					xhr.send( formData );

					xhrHasBeenSent = true;
				}
			}

			$target.parentElement.parentElement.removeAttribute( 'data-option' );
			$target.parentElement.parentElement.classList.add( 'hidden' );
		}

		if ( $target.matches( '.buttons-wrap button.continue' ) ) {
			var $optionToggles = $target.parentElement.parentElement.querySelectorAll( '.option:not([data-activated]) input.tgl' );

			if ( $optionToggles.length > 0 ) {
				$optionToggles.forEach( function( $toggle ) {
					$toggle.disabled = false;
				} );
			}
			else {
				var $options           = $target.parentElement.parentElement.querySelectorAll( '.option[data-activated]' ),
					$closeButton       = $target.parentElement.querySelector( 'button.close' ),
					$noRecommendations = $target.parentElement.parentElement.querySelector( '.spai__no-recommendations' ),
					$optionsWrap       = $target.parentElement.parentElement.querySelector( '.spai__recommended-options-wrap' );

				$options.forEach( function( $option ) {
					$option.parentNode.removeChild( $option );
				} );

				$optionsWrap.classList.add( 'hidden' );
				$noRecommendations.classList.remove( 'hidden' );
				$target.classList.add( 'hidden' );
				$closeButton.classList.remove( 'hidden' );
			}
		}

		if ( $target.matches( '.buttons-wrap button.close' ) ) {
			$target.parentElement.parentElement.parentElement.parentElement.classList.add( 'hidden' );
		}

		if ( $target.matches( '.buttons-wrap button.back' ) ) {
			var xhr      = new XMLHttpRequest(),
				formData = new FormData();

			formData.append( 'action', 'shortpixel_ai_handle_page_action' );
			formData.append( 'page', 'frontWorker' );
			formData.append( 'spainonce', document.querySelector('.spai__recommended-options-wrap').dataset.spainonce );
			formData.append( 'data[action]', 'done' );

			xhr.onloadend = function() {
				var response = JSON.parse( xhr.response );

				if ( response.success ) {
					if ( response.cookie !== '' && typeof window.Cookies === 'object' && typeof window.Cookies.get === 'function' && typeof window.Cookies.remove === 'function' ) {
						window.Cookies.remove( response.cookie );
					}

					if ( typeof response.redirect === 'object' ) {
						if ( !!response.redirect.allowed ) {
							document.location.href = response.redirect.url;
						}
					}

					$target.parentElement.parentElement.parentElement.parentElement.classList.add( 'hidden' );
				}

				xhrHasBeenSent = false;
			}

			if ( !xhrHasBeenSent ) {
                let ajaxUrl = ShortPixelAI.getAjaxUrl();
                xhr.open( 'post', !ajaxUrl ? document.location.origin + '/wp-admin/admin-ajax.php' : ajaxUrl );
				xhr.send( formData );

				xhrHasBeenSent = true;
			}
		}
	} );
} )();