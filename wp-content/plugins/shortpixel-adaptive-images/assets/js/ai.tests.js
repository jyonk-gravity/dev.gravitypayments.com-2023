( function( d, w ) {
	w.SPAITests = function() {
		/**
		 * Default parameters
		 */
		this.jQChecker = undefined;
	}

	/**
	 * Method checks is there jQuery library
	 * Otherwise it send request to server to deactivate the SPAI
	 *
	 * @param {number|string|undefined} [interval=5000]
	 * @param {boolean|undefined} [once=true]
	 */
	SPAITests.prototype.hasJQ = function( interval, once ) {
		interval = typeof interval !== 'number' ? ( typeof interval === 'string' ? parseInt( interval, 10 ) : 5000 ) : typeof Math === 'object' ? Math.floor( interval ) : 5000;
		interval = isNaN( interval ) ? 5000 : interval;

		once = typeof once !== 'boolean' ? true : once;

		var xhr = new XMLHttpRequest(),
			action,
			url = typeof spai_settings === 'undefined'
				? (typeof spaiData === 'undefined' ? '/wp-admin/admin-ajax.php' : spaiData.ajax_url)
				: spai_settings.ajax_url;

		if ( typeof w.jQuery !== 'function' ) {
			action = 'shortpixel_deactivate_ai';

			xhr.onload = function() {
				if ( xhr.status === 200 ) {
					if ( !xhr.response.front.reload ) {
						console.log( '⚡ SPAI: %cMissing jQuery library%c!', 'color:#e57373;font-weight:bold', 'color:inherit' );
					}
					else {
						try {
							var url      = new URL( document.location ),
								returnTo = url.searchParams.get( 'return_to' );

							if ( typeof returnTo === 'string' && returnTo !== '' ) {
								document.location.href = document.location.protocol + url.searchParams.get( 'return_to' );
							}
							else {
								document.location.reload();
							}
						}
						catch ( e ) {
							document.location.reload();
						}
					}
				}
				else {
					console.log( '⚡ SPAI: %cMissing jQuery library%c! XHR returned code: ' + xhr.status, 'color:#e2062e;font-weight:bold', 'color:initial' );
				}
			};

			xhr.onerror = function() {
				console.error( 'XHR error! Check your connection!' );
			};

			xhr.onloadend = function() {
				if ( !once && typeof w.ShortPixelAI === 'object' && w.ShortPixelAI instanceof SPAI && typeof this.jQChecker === 'number' ) {
					clearInterval( this.jQChecker );
				}
			};
		}
		else {
			action = 'shortpixel_activate_ai';

			console.log( '⚡ SPAI: jQuery (' + w.jQuery.fn.jquery + ') is %cOK', 'color:#4caf50;font-weight:bold' );
		}

		xhr.responseType = 'json';

		xhr.open( 'post', url + '?action=' + action + '&spainonce=' + spai_settings.ajax_nonce);
		xhr.send();

		if ( !once && w.ShortPixelAI instanceof SPAI && typeof this.jQChecker !== 'number' ) {
			this.jQChecker = setInterval( this.hasJQ, interval );
		}
	};

	SPAITests.prototype.init = function() {
		var instance = new SPAITests();

		instance.hasJQ();
	}

	if(document.readyState === 'loading') {
		document.addEventListener("DOMContentLoaded", function() {
			SPAITests.prototype.init();
		});
	} else {
		SPAITests.prototype.init();
	}
} )( document, window );