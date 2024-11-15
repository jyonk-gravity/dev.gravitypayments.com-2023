/**
 * Low quality image placeholders module class
 * @constructor
 */
function LQIP() {
	/* CONSTANTS */
	/**
	 * This is a global "this" (instance)
	 * @type {LQIP}
	 */
	var THIS = this;

	/**
	 * Quantity of urls to send in one time
	 * @type {number}
	 */
	var BUNDLE_CAPACITY = ( typeof window.lqipConstants === 'undefined' ? 5 : ( typeof window.lqipConstants.processWay === 'string' ? ( window.lqipConstants.processWay === 'cron' ? 20 : 5 ) : 5 ) );

	/**
	 * Does the storage enabled?
	 * @type {boolean}
	 */
	var STORAGE_ENABLED = ( typeof window.lqipConstants === 'undefined' ? false : ( typeof window.lqipConstants.localStorage === 'undefined' ? false : !!window.lqipConstants.localStorage ) );

	/**
	 * Awaiting urls to send
	 * @type {number}
	 */
	this.await = 0;

	/**
	 * Collection of images urls
	 * @type {*[]}
	 */
	this.collection = [];

	/**
	 * Observer ID
	 * @type {undefined}
	 */
	this.OID = undefined;

	/**
	 * Does the script a request right now?
	 * @type {boolean}
	 */
	this.ajax = false;

	/**
	 * Server's responses
	 * @type {*[]}
	 */
	this.responses = [];

	/**
	 * Method collects the urls (images)
	 * @param {string} url
	 * @param {string} source
	 */
	this.add = function( url, source ) {
		var exists    = this.exists( url ),
			extension = this.getExtension( url );

		if ( extension !== 'svg' && !exists ) {
			this.collection.push( {
				url    : url,
				source : source,
				sent   : false
			} );
		}

		this.filter();
		this.count();

		// send images to the back-end if 20 or more images wait for creating the lqip
		if ( this.await >= BUNDLE_CAPACITY ) {
			this.send();
		}
	};

	/**
	 * Method sends collection to endpoint
	 */
	this.send = function() {
		if ( this.ajax ) {
			return;
		}

		var formData = new FormData(),
			xhr      = new XMLHttpRequest(),
			url      = typeof spai_settings === 'undefined' ? 'wp-admin/admin-ajax.php' : spai_settings.ajax_url;

		formData.append( 'action', typeof window.lqipConstants === 'object' && ( typeof window.lqipConstants.action === 'string' && window.lqipConstants.action !== '' ) ? window.lqipConstants.action : 'shortpixel_ai_handle_lqip_action' );
		formData.append( 'data[action]', 'collect' );
		formData.append( 'data[referer]', window.location.href);

		if ( this.collection.length > 0 ) {
			var formCollectionIndex = 0;

			for ( var index = 0, item = this.collection[ index ]; index < this.collection.length; item = this.collection[ ++index ] ) {
				if ( !item.sent ) {
					formData.append( 'data[collection][' + formCollectionIndex + '][url]', item.url );
					formData.append( 'data[collection][' + formCollectionIndex + '][source]', item.source );

					this.collection[ index ].toSend = true;

					formCollectionIndex++;
				}

				if ( formCollectionIndex >= BUNDLE_CAPACITY ) {
					break;
				}
			}
		}

		xhr.timeout = 20000;
		xhr.open( 'post', url );

		xhr.onload = function() {
			var response;

			try {
				response = JSON.parse( xhr.response );
			}
			catch ( e ) {
				response = { success : false };
				console.log( e );
			}

			THIS.responses.push( response );

			THIS.collection.forEach( function( item ) {
				if ( item.toSend ) {
					item.sent = response.success;

					delete item.toSend;
				}
			} );

			if ( response.success ) {
				THIS.ajax = false;
				THIS.count();
				THIS.store();
			}
		}

		xhr.onloadend = function() {
			THIS.ajax = false;
		}

		this.ajax = true;

		xhr.send( formData );
	};

	this.filter = function() {
		this.collection = this.collection.filter( function( item, index, array ) {
			var pass = true;

			if ( array.length > 1 ) {
				array.forEach( function( element, position ) {
					var duplicates = 0;

					if ( index !== position ) {
						if ( item.url === element.url ) duplicates++;
					}

					pass = duplicates === 0;
				} );
			}

			return pass;
		} );
	};

	/**
	 * Method counts current awaiting items
	 */
	this.count = function() {
		if ( this.collection.length > 0 ) {
			this.await = 0;

			for ( var index = 0; index < this.collection.length; index++ ) {
				if ( this.await < this.collection.length && !this.collection[ index ].sent ) {
					this.await++;
				}
			}
		}
	};

	/**
	 * Method stores current collection into user's localStorage
	 */
	this.store = function() {
		if ( !localStorage instanceof Storage || !STORAGE_ENABLED ) {
			return false;
		}

		try {
			var localCollection = JSON.parse( localStorage.lqipCollection );
		}
		catch ( e ) {
			localCollection = [];
		}

		localCollection.concat( THIS.collection );
		THIS.filter();
		THIS.count();

		localStorage.lqipCollection = JSON.stringify( THIS.collection );
		return true;
	};

	this.exists = function( url ) {
		if ( this.collection.length > 0 ) {
			for ( var index = 0; index < this.collection.length; index++ ) {
				if ( this.collection[ index ].url === url ) {
					return true;
				}
			}
		}

		return false;
	};

	this.getExtension = function( url ) {
		var extensionRegEx = /(?:\.([^.\/\?]+))(?:$|\?.*)/;

		extensionRegEx.lastIndex = 0;

		var extensionMatches = extensionRegEx.exec( url ),
			extension        = typeof extensionMatches === 'object' && extensionMatches !== null && typeof extensionMatches[ 1 ] === 'string' && extensionMatches[ 1 ] !== '' ? extensionMatches[ 1 ] : undefined;

		return extension === 'jpeg' ? 'jpg' : extension;
	};

	/**
	 * Method observes the awaiting images
	 * @param {int} interval
	 */
	( this.observer = function( interval ) {
		interval = parseInt( interval, 10 );
		interval = isNaN( interval ) ? 5000 : interval;

		if ( THIS.await > 0 ) {
			THIS.send();
		}

		if ( !THIS.OID ) {
			THIS.OID = setInterval( THIS.observer, interval );
		}
	} )();

	/**
	 * Method retrieves user's localStorage into the current collection
	 */
	( this.retrieve = function() {
		if ( !localStorage instanceof Storage || !STORAGE_ENABLED ) {
			return false;
		}

		try {
			var localCollection = JSON.parse( localStorage.lqipCollection );
		}
		catch ( e ) {
			localCollection = [];
		}

		THIS.collection = THIS.collection.concat( localCollection );
		THIS.filter();
		THIS.count();
		return true;
	} )();
}