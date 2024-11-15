!function( e, t, n ) {
	function a() {
		var e = t.getElementsByTagName( 'script' )[ 0 ], n = t.createElement( 'script' );

		n.type = 'text/javascript';
		n.async = true;
		n.src = 'https://beacon-v2.helpscout.net'
		e.parentNode.insertBefore( n, e );
	}

	if ( e.Beacon = n = function( t, n, a ) {
		e.Beacon.readyQueue.push( { method : t, options : n, data : a } )
	}, n.readyQueue = [], 'complete' === t.readyState ) {
		return a();
	}
	e.attachEvent ? e.attachEvent( 'onload', a ) : e.addEventListener( 'load', a, !1 )
}( window, document, window.Beacon || function() {
} );

;( function( w, d ) {
	w.quriobotLoaded = function() {
		function handleQBShow() {
			if ( typeof w.Beacon !== 'function' ) return;

			w.Beacon( 'close' );
			d.querySelector( '.shortpixel-ai-beacon' ).classList.remove( 'visible' );
		}

		function handleQBHide() {
			if ( typeof w.Beacon !== 'function' ) return;

			w.Beacon( 'close' ); // unnecessary but MAYBE in some cases would be useful
			d.querySelector( '.shortpixel-ai-beacon' ).classList.add( 'visible' );
		}

		// on show events
		w.quriobot.onLoad = handleQBShow;
		w.quriobot.onStart = handleQBShow;
		w.quriobot.onQuestion = handleQBShow;
		w.quriobot.onAnswer = handleQBShow;
		w.quriobot.onReturn = handleQBShow;

		w.quriobot.onFinish = handleQBHide;
		w.quriobot.onExit = handleQBHide;
		w.quriobot.onLeave = handleQBHide;

		w.quriobot.init();
	};

	if ( typeof w.beaconConstants !== 'object' || typeof w.Beacon !== 'function' ) return;

	w.Beacon( 'init', w.beaconConstants.initID );

	w.Beacon( 'identify', {
		email  : w.beaconConstants.identity.email,
		apiKey : w.beaconConstants.identity.apiKey
	} );

	w.Beacon( 'suggest', w.beaconConstants.suggestions.compression );

	setTimeout( function() {
		d.querySelector( '.shortpixel-ai-beacon' ).classList.add( 'visible' );
	}, 1500 );

	document.addEventListener( 'click', function( event ) {
		if ( event.target.matches( '.shortpixel-ai-beacon' ) ) {
			w.Beacon( 'toggle' );
		}
	} );

	// compatibility with Quriobot
	w.Beacon( 'on', 'open', function() {
		if ( typeof w.quriobot === 'object' ) {
			w.quriobot.hide();
		}
	} );

	w.Beacon( 'on', 'close', function() {
		if ( typeof w.quriobot === 'object' ) {
			w.quriobot.show();
		}
	} );
} )( window, document );