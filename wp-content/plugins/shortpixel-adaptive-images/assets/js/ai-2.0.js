function SPAI() {}

SPAI.prototype = {
    /*DEBUG*/stop: 100000,
    //*DEBUG*/observedMutations: 0,
    //*DEBUG*/handledMutations: 0,
    //*DEBUG*/parsedMutations: 0,
    //*DEBUG*/modifiedMutations: 0,

    modules : {},

    fancyboxId: "",
    fancyboxHooked: "none",
    mutationsCount: 0,
    mutationsList: {},
    timeOutHandle: false,
    mutationsLastProcessed: 0,
    updatedUrlsCount: 0,

    mutationObserver: false,
    intersectionObserver: false,
    intersectionMargin : 500,

    initialized: false,
    bodyHandled: false,
    bodyCount: 0,

    conversionType: false, //if converting, can be 'to_webp' or 'to_auto'

    urlRegister: [], //keep a register with all URLs and sizes
    callbacks: [],

    sniperOn: false,

    debugInfo: {log: ''},
    loadTs: Date.now(),

    NORESIZE: 1,
    EXCLUDED: 2,
    EAGER: 4
};


SPAI.prototype.init = function(){
    if(typeof window.IntersectionObserver !== 'function') {
        jQuery.getScript(spai_settings.plugin_url + '/assets/js/intersection.min.js?' + spai_settings.version, ShortPixelAI.setupIntersectionObserverAndParse);
    } else {
        ShortPixelAI.setupIntersectionObserverAndParse(); //this also parses the document for the first time
    }
};

//proxy function for the shortpixel_ai_sniper
function spaiSniperClick() {
    SpaiSniper(1);
}

SPAI.prototype.record = function(action, type, value) {
    if(spai_settings.debug) {
        switch (action) {
            case 'count':
                if(typeof ShortPixelAI.debugInfo[type] === 'undefined') ShortPixelAI.debugInfo[type] = 0;
                ShortPixelAI.debugInfo[type] += value;
                break;
            case 'log':
            case 'logX':
                if(typeof ShortPixelAI.debugInfo[type] === 'undefined') ShortPixelAI.debugInfo[type] = '';
                ShortPixelAI.debugInfo[type] += (new Date().getTime()) + ' - ' + (action === 'log' ? value : ShortPixelAI.xpath(value)) + '\n';
        }
    }
}

/**
 * New grouped log method
 * @param {*} [data]
 */
SPAI.prototype.gLog = function( data ) {
    if ( arguments.length === 0 ) {
        return;
    }

    var timeAfterLoad = Date.now() - this.loadTs;

    console.groupCollapsed( '🤖 SPAI Debug' );

    console.log( '%cTime after init: %c' + timeAfterLoad + '%c ms', 'font-weight:bold', 'color:#4caf50;font-weight:bold', 'color:inherit;font-weight:bold' );

    for ( var index = 0; index < arguments.length; index++ ) {
        console.log( arguments[ index ] );
    }

    console.groupEnd();
}

SPAI.prototype.getApiUrl = function() {
    return spai_settings.api_url;
}

SPAI.prototype.getAjaxUrl = function() {
    return spai_settings.ajax_url;
}

SPAI.prototype.log = function(msg) {
    var ms = Date.now() - ShortPixelAI.loadTs;
    var log = ms + 'ms - ' + msg;
    if(ms < 2000) { //TODO remove - this is for puppeteer that doesn't show the first ~1 sec of console output
        ShortPixelAI.debugInfo['log'] += log + '\n';
        return;
    }
    if(ShortPixelAI.debugInfo['log'] !== ''){
        console.log(ShortPixelAI.debugInfo['log']);
        ShortPixelAI.debugInfo['log'] = '';
    }
    console.log(log);
}

SPAI.prototype.handleBody = function(){
    //console.log("handleBody " + ShortPixelAI.bodyCount);

    var affectedTags = {};

    if ( spai_settings.affected_tags !== '{{SPAI-AFFECTED-TAGS}}' ) {
        try {
            affectedTags = JSON.parse( spai_settings.affected_tags )
        }
        catch ( e ) {
            console.log( e );
        }
    }
    else if ( typeof spai_affectedTags !== 'undefined' ) {
        try {
            affectedTags = JSON.parse( spai_affectedTags )
            spai_settings.affected_tags = spai_affectedTags;
        }
        catch ( e ) {
            console.log( e );
        }
    }
    spai_settings.affected_tags_map = affectedTags;

    var theParent = jQuery('body');
    ShortPixelAI.bodyCount = 1; //Yes, there is a concurency problem but as this is only a trick to stop this from relaunching forever in case the placeholder is never loaded, it will work anyway
    try {
        ShortPixelAI.handleUpdatedImageUrls(true, theParent, true, false);
        ShortPixelAI.bodyHandled = true;
        ShortPixelAI.triggerEvent('spai-body-handled', theParent[0] );
        //console.log("body handled " + ShortPixelAI.bodyCount);
    } catch(error) {
        if(error == 'defer_all' && ShortPixelAI.bodyCount < 20) {
            //spai_settings.debug && ShortPixelAI.log("handleBody - DEFER ALL");
            setTimeout(ShortPixelAI.handleBody, 20 * ShortPixelAI.bodyCount );
            ShortPixelAI.bodyCount++;
        } else {
            spai_settings.debug && ShortPixelAI.log("handleBody - error " + error /* + (typeof e.stack !== 'undefined'? ' stack ' + e.stack : 'no stack')*/);
            throw error;
        }
    }
}

/**This was created for iPhone on which the placeholders are not .complete when the DOMLoaded event is triggered, on first page load on that phone.
 * defer_all is thrown by updateImageUrl.
 * @param theParent
 * @param hasMutationObserver
 * @param fromIntersection
 */
SPAI.prototype.handleUpdatedImageUrlsWithRetry = function(initial, theParent, hasMutationObserver, fromIntersection) {
    try {
        ShortPixelAI.handleUpdatedImageUrls(initial, theParent, hasMutationObserver, fromIntersection);
        //console.log("body handled " + ShortPixelAI.bodyCount);
    } catch(error) {
        if(error == 'defer_all' && ShortPixelAI.bodyCount < 30) {
            spai_settings.debug && ShortPixelAI.log("handleUpdatedImageUrlsWRetry - DEFER ALL");
            setTimeout( function() {
                ShortPixelAI.handleUpdatedImageUrls(initial, theParent, hasMutationObserver, fromIntersection);
            }, 20 * ShortPixelAI.bodyCount );
            ShortPixelAI.bodyCount++;
        } else {
            spai_settings.debug && ShortPixelAI.log("handleUpdatedImageUrlsWRetry - error " + error.description);
            throw error;
        }
    }

}

SPAI.prototype.handleUpdatedImageUrls = function(initial, theParent, hasMutationObserver, fromIntersection){
    //*DEBUG*/ShortPixelAI.record('count', 'observedMutations', 1);
    //*DEBUG*/if(ShortPixelAI.observedMutations > ShortPixelAI.stop) return;
    //*DEBUG*/ShortPixelAI.record('count', 'handledMutations', 1);
    //*DEBUG*/var parsed = 0, modified = 0, divModified = 0;
    /*
        if(theParent.is('body')) { //some of the excludes were not caught server side, catch them in browser and replace with the original URL
            for(var i = 0; i < spai_settings.excluded_selectors.length; i++) {
                var selector = spai_settings.excluded_selectors[i];
                jQuery(selector).each(function(elm){
                    var src = elm.attr('src');
                    if(typeof src !== 'undefined' &&  ShortPixelAI.containsPseudoSrc(src) >=0 ) {
                        var data = ShortPixelAI.parsePseudoSrc(elm.attr('href'));
                        elm.attr('src', data.src);
                    }
                });
            }
        }
    */
    if(!initial && !ShortPixelAI.bodyHandled) {
        //spai_settings.debug && ShortPixelAI.log("handleUpdatedImageUrls return 1");
        return; //not called through handleBody and handleBody wasn't yet successfully ran
    }
    if(theParent.is('img,amp-img')) {
        ShortPixelAI.updateImageUrl(theParent, hasMutationObserver, fromIntersection);
        return;
    }

    ShortPixelAI.updatedUrlsCount = 0;
    if(spai_settings.method !== 'srcset') {
        jQuery('img,amp-img', theParent).each(function(){
            var elm = jQuery(this);
            ShortPixelAI.updateImageUrl(elm, hasMutationObserver, fromIntersection);
        });
    }

    // var affectedTags = spai_settings.affected_tags !== '{{SPAI-AFFECTED-TAGS}}' ? JSON.parse(spai_settings.affected_tags)
    //     : ( typeof spai_affectedTags !== 'undefined' ? JSON.parse(spai_affectedTags) : {});

    //if(fromIntersection && (theParent.is('a') || theParent.is('div') || theParent.is('li') || theParent.is('header'))) { //will handle the div parents only if they're from intersection OR mutation
    if(fromIntersection) { //will handle the div parents only if they're from intersection OR mutation
        for(var tag in spai_settings.affected_tags_map) {
            if(theParent.is(tag)) {
                ShortPixelAI.updateDivUrl(theParent, hasMutationObserver, fromIntersection);
                break;
            }
        }
    }

    var affectedTagsList = '';
    for(var tag in spai_settings.affected_tags_map) {
        affectedTagsList += ',' + tag;
    }
    affectedTagsList = affectedTagsList.replace(/^,/, '');
    //jQuery('a,div,li,header,span,section,article', theParent).each(function(){
    jQuery(affectedTagsList, theParent).each(function(){
        //*DEBUG*/parsed = 1;
        var elm = jQuery(this);
        if(elm[0].tagName === 'VIDEO' || elm[0].tagName === 'SOURCE') {
            ShortPixelAI.updateVideoPoster(elm, hasMutationObserver);
        } else {
            ShortPixelAI.updateDivUrl(elm, hasMutationObserver, fromIntersection);
        }
    });

    //Check if integration is active and update lightbox URLs for each supported gallery
    //the media-gallery-link is present in custom solutions
    ShortPixelAI.updateAHrefForIntegration('CORE', theParent, 'a.media-gallery-link');
    //Envira
    ShortPixelAI.updateAHrefForIntegration('envira', theParent, 'a.envira-gallery-link');
    //Modula
    ShortPixelAI.updateAHrefForIntegration('modula', theParent, 'div.modula-gallery a[data-lightbox]');
    //Essential addons for Elementor
    ShortPixelAI.updateAHrefForIntegration('elementor-addons', theParent, 'div.eael-filter-gallery-wrapper a.eael-magnific-link');
    //Elementor
    ShortPixelAI.updateAHrefForIntegration('elementor', theParent, 'a[data-elementor-open-lightbox]');
    //Viba Portfolio
    ShortPixelAI.updateAHrefForIntegration('viba-portfolio', theParent, 'a.viba-portfolio-media-link');
    //Everest gallery - seems that it's not necessary, the url for the lightbox is parsed from the data:image on the lightbox's <img> creation
    //ShortPixelAI.updateAHrefForIntegration('everest', theParent, 'div.eg-each-item a[data-lightbox-type]');
    //WP Bakery Testimonials
    if(spai_settings.active_integrations['wp-bakery']) {
        jQuery('span.dima-testimonial-image', theParent).each(function(){
            ShortPixelAI.updateWpBakeryTestimonial(jQuery(this), hasMutationObserver);
        });
//        jQuery('div[data-ultimate-bg]', theParent).each(function(){
//            ShortPixelAI.updateWpBakeryTestimonial(jQuery(this));
//        });
    }
    if(spai_settings.active_integrations['social-pug']) {
        //Pinterest buttons are created from pseudo-src's (or api URLs with WebP) by Mediavine Grow, restore the original URL
        jQuery('a.dpsp-pin-it-button', theParent).each(function(){
            var elm = jQuery(this);
            var match = false;
            elm.attr('href', elm.attr('href').replace(/media=(data:image\/svg\+xml;.*)&url=/, function(matched, pseudoSrc, pos){
                match = true;
                return 'media=' + ShortPixelAI.parsePseudoSrc(pseudoSrc).src + '&url=';
            }));
            if(!match) {
                var regex = spai_settings.api_url.substr(0, spai_settings.api_url.lastIndexOf('/') + 1).replace('/', '\\/') + '[^\\/]+\\/';
                elm.attr('href', elm.attr('href').replace(new RegExp(regex), ''));
            }
        });
    }
    //Foo gallery
    ShortPixelAI.updateAHrefForIntegration('foo', theParent, 'div.fg-item a.fg-thumb');
    //NextGen
    if(spai_settings.active_integrations.nextgen) {
        //provide the URL to the fancybox (which doesn't understand the data: inline images) before it tries to preload the image.
        jQuery('a.ngg-fancybox, a.ngg-simplelightbox', theParent).each(function(){
            var elm = jQuery(this);
            if(!ShortPixelAI.isFullPseudoSrc(elm.attr('href'))) {
                return;
            }

            var data = ShortPixelAI.parsePseudoSrc(elm.attr('href'));
            elm.attr('href', ShortPixelAI.composeApiUrl(false, data.src, 'DEFER', false, false));
            if(elm.hasClass('ngg-fancybox')) {
                elm.mousedown(function(){
                    //this will calculate the width when the link is clicked just before fancybox uses the same algorithm to determine the width of the box and to preload the image...
                    ShortPixelAI.fancyboxUpdateWidth(elm);
                    return true;
                });
            }
        });
    }

    if(ShortPixelAI.updatedUrlsCount > 0) {
        spai_settings.debug && ShortPixelAI.log("trigger spai-block-handled event for " + ShortPixelAI.updatedUrlsCount + " URLs on " + theParent[0].tagName);
        ShortPixelAI.triggerEvent('spai-block-handled', theParent[0]);
    }

    //*DEBUG*/ShortPixelAI.parsedMutations += parsed;
    //*DEBUG*/ShortPixelAI.modifiedMutations += Math.max(modified, divModified);
};

SPAI.prototype.updateImageUrl = function(elm, hasMutationObserver, fromIntersection){
    ///*DEBUG*/parsed = 1;

    if (!ShortPixelAI.containsPseudoSrc(elm[0].outerHTML)){
        return;
    }                                                       // vv this is because sometimes JS updates the URL from another property which is replaced by SPAI by a lazy-loaded placeholder too
    if (typeof elm.attr('data-spai-upd') !== 'undefined' && !ShortPixelAI.containsPseudoSrc(elm.attr('src'))){
        return;
    }

    var exclusions = ShortPixelAI.is(elm, ShortPixelAI.EXCLUDED | ShortPixelAI.EAGER | ShortPixelAI.NORESIZE);
    if(spai_settings.native_lazy == '1') {
        exclusions |= ShortPixelAI.EAGER;
    }

    //flag 4 means eager, don't observe eager elements, just replace them right away
    if(!(exclusions & ShortPixelAI.EAGER) && !fromIntersection && !ShortPixelAI.elementInViewport(elm[0])) {
        //spai_settings.debug && ShortPixelAI.log("Observing image: " + ShortPixelAI.parsePseudoSrc(elm[0].src).src);
        //will handle this with the intersectionObserver
        ShortPixelAI.intersectionObserver.observe(elm[0]);
        return;
    }

    var w = 0, h = 0, wPad = 0, hPad = 0;
    if((exclusions & (ShortPixelAI.EXCLUDED | ShortPixelAI.NORESIZE)) == 0) { //flags for do not resize and for exclude completely
        try {
            //var sizeInfo = ShortPixelAI.getSizes(elm[0], hasMutationObserver);
            var sizeInfo = ShortPixelAI.getSizesRecursive(elm, hasMutationObserver);
            w = sizeInfo.width;
            h = sizeInfo.height;
            for(var idx in spai_settings.use_first_sizes) {
                var size = spai_settings.use_first_sizes[idx];
                if(elm.is(idx)) {
                    if(size.width < w && size.height < h) {
                        spai_settings.use_first_sizes[idx] = {width: w, height: h};
                    } else {
                        w = size.width;
                        h = size.height;
                    }
                }
            }
            wPad = Math.round(w + sizeInfo.padding);
            hPad = Math.round(h + sizeInfo.padding_height);
        } catch (err) {
            if(!elm[0].complete) {
                //on iPhone on first page load, the placeholders are not rendered when it gets here, so defer the parsing of the page altogether
                throw 'defer_all';
            }
            if (typeof err.type !== 'undefined' && err.type == 'defer' && hasMutationObserver && !(exclusions & ShortPixelAI.EAGER)) {
                spai_settings.debug && ShortPixelAI.log("Defer " + err.cause + ' ' + ShortPixelAI.parsePseudoSrc(elm[0].src).src);
                // binding the mouseover event on deferred elements (e.g. which were hid on load)
                if ( !elm.is( ':visible' ) && !!spai_settings.hover_handling ) {
                    spai_settings.debug && ShortPixelAI.gLog( 'Attach mouseover to it' );
                    elm.off( 'mouseover', ShortPixelAI.mouseOverHandler ); //make sure we don't attach several times
                    elm.on( 'mouseover', ShortPixelAI.mouseOverHandler );
                }

                return;
            }
        }
    }

    ShortPixelAI.record('count', 'modifiedImg', 1);
    ShortPixelAI.record('logX', 'modifiedImgURL', elm[0]);
    //*DEBUG*/modified = 1;

    //TODO future dev: clone()/replaceWith()
    //var newElm = elm.clone();

    if(wPad && spai_settings.alter2wh && elm.attr('width') && wPad < elm.attr('width') ) {
        if(elm.attr('height')) {
            //make it proportionally smaller
            elm.attr('height', Math.round(elm.attr('height') * wPad / elm.attr('width')))
        }
        elm.attr('width', wPad);
    }
    else if(hPad && spai_settings.alter2wh && elm.attr('height') && hPad < elm.attr('height')) {
        elm.attr('height', hPad);
    }

    var origData = ShortPixelAI.updateSrc(elm, 'src', w, h, (spai_settings.method == 'src' || spai_settings.method == 'both') && ((exclusions & ShortPixelAI.EXCLUDED) == 0));
    ShortPixelAI.updateSrc(elm, 'data-src', false, false, ((exclusions & ShortPixelAI.EXCLUDED) == 0));
    ShortPixelAI.updateSrc(elm, 'data-large_image', false, false, ((exclusions & ShortPixelAI.EXCLUDED) == 0));
    if(spai_settings.active_integrations.envira) {
        ShortPixelAI.updateSrc(elm, 'data-envira-src', false, false, ((exclusions & ShortPixelAI.EXCLUDED) == 0));
        ShortPixelAI.updateSrc(elm, 'data-safe-src', w, h, ((exclusions & ShortPixelAI.EXCLUDED) == 0));
    }
    if(spai_settings.active_integrations.foo) {
        ShortPixelAI.updateSrc(elm, 'data-src-fg', w, h, ((exclusions & ShortPixelAI.EXCLUDED) == 0));
    }
    if(spai_settings.method == 'src') {
        ShortPixelAI.removeSrcSet(elm);
    } else {
        ShortPixelAI.updateSrcSet(elm, w, origData);
    }
    if(spai_settings.native_lazy == '1') {
        elm.attr('loading', 'lazy');
    }

    if(spai_settings.active_integrations.woocommerce && elm.hasClass('attachment-shop_thumbnail')) {
        //this is a hack needed because WooCommerce searches the images of the product variations by thumbnail... URL :(
        //search for the form
        var variationsForms = jQuery('form.variations_form');
        for(var i = 0; i < variationsForms.length; i++) {
            var variationsForm = jQuery(variationsForms[i]);
            if(variationsForm.attr('data-product_variations')) {
                var pVar = variationsForm.data('product_variations');
                for(var idx in pVar) {
                    if(pVar[idx].image.gallery_thumbnail_src.indexOf(origData.src) >= 0) {
                        pVar[idx].image.gallery_thumbnail_src = origData.newSrc;
                    }
                }
                variationsForm.data('product_variations', pVar);
            }
        }
    }

    //elm.replaceWith(newElm);
    ShortPixelAI.elementUpdated(elm, w);
    elm.off('mouseover', ShortPixelAI.mouseOverHandler);
};

SPAI.prototype.mouseOverHandler = function() {
    var $this = jQuery( this );
    spai_settings.debug && ShortPixelAI.log("Mouseover triggered on " + ShortPixelAI.parsePseudoSrc(this.src).src);

    if ( $this.is( ':visible' ) ) {
        spai_settings.debug && ShortPixelAI.log("updateImageURL");

        var width  = $this.width(),
            height = $this.height();
        ShortPixelAI.updateImageUrl( $this, true, true );
    }
}

SPAI.prototype.updateWpBakeryTestimonial = function(elm, hasMutationObserver) {
    if (typeof elm.attr('data-spai-upd') !== 'undefined'){
        return;
    }
    ShortPixelAI.updateAttr(elm, 'data-element-bg');

    var w = 0, h = 0, sizes = [];
    var exclusions = ShortPixelAI.is(elm, ShortPixelAI.EXCLUDED | ShortPixelAI.NORESIZE | ShortPixelAI.EAGER);
    if((exclusions & (ShortPixelAI.EXCLUDED | ShortPixelAI.NORESIZE)) == 0) { //do not resize and exclude altogether
        try {
            //sizes = ShortPixelAI.getSizes(elm[0], hasMutationObserver);
            sizes = ShortPixelAI.getSizesRecursive(elm, hasMutationObserver);
            w = sizes.width;
            h = sizes.height;
        } catch (err) {
            if(typeof err.type !== 'undefined' && err.type == 'defer' && hasMutationObserver && !(exclusions & ShortPixelAI.EAGER)) {
                return;
            }
        }
    }
    ShortPixelAI.updateInlineStyle(elm, w, h, (exclusions & ShortPixelAI.EXCLUDED) == 0, false);
    ShortPixelAI.elementUpdated(elm, w);
};

SPAI.prototype.updateVideoPoster = function(elm, hasMutationObserver) {
    if (typeof elm.attr('data-spai-upd') !== 'undefined'){
        return;
    }
    var w = 0, h = 0, sizes = [];
    var exclusions = ShortPixelAI.is(elm, ShortPixelAI.EXCLUDED | ShortPixelAI.NORESIZE | ShortPixelAI.EAGER);
    if((exclusions & (ShortPixelAI.EXCLUDED | ShortPixelAI.NORESIZE)) == 0) { //do not resize and exclude altogether
        try {
            //will take sizes from the parent if the element that contains the poster is a <source> inside the <video> tag (because the source is not visible)
            sizes = ShortPixelAI.getSizesRecursive(elm[0].tagName === 'VIDEO' ? elm : elm.getParent(), hasMutationObserver);
            w = sizes.width;
            h = sizes.height;
        } catch (err) {
            if(typeof err.type !== 'undefined' && err.type == 'defer' && hasMutationObserver && !(exclusions & ShortPixelAI.EAGER)) {
                return;
            }
        }
    }
    ShortPixelAI.updateSrc(elm, 'poster', w, h, (exclusions & ShortPixelAI.EXCLUDED) == 0);
    ShortPixelAI.elementUpdated(elm, w);
};

SPAI.prototype.updateDivUrl = function(elm, hasMutationObserver, fromIntersection) {
    if (typeof elm.attr('data-spai-upd') !== 'undefined'){
        return;
    }
    if(   typeof elm.attr('src') === 'undefined' && typeof elm.attr('data-src') === 'undefined' && typeof elm.attr('data-thumb') === 'undefined'
        && !ShortPixelAI.getBackgroundPseudoImages(elm.attr('style'))) {
        return;
    }
    if(!fromIntersection && !ShortPixelAI.elementInViewport(elm[0])) {
        //will handle this with the intersectionObserver
        ShortPixelAI.intersectionObserver.observe(elm[0]);
        return;
    }
    var w = 0, h = 0, wPad = 0, hPad = 0, sizes = [];
    var exclusions = ShortPixelAI.is(elm, ShortPixelAI.EXCLUDED | ShortPixelAI.NORESIZE | ShortPixelAI.EAGER);
    if((exclusions & (ShortPixelAI.EXCLUDED | ShortPixelAI.NORESIZE)) == 0) {
        try {
            //TODO if practice proves the need - discrete function for widths: Math.ceil( w / Math.ceil( w / 20 ) ) * Math.ceil( w / 20 )
            //sizes = ShortPixelAI.getSizes(elm[0], hasMutationObserver);
            sizes = ShortPixelAI.getSizesRecursive(elm, hasMutationObserver);
            w = sizes.width;
            h = sizes.height;
            wPad = Math.round(w + sizes.padding);
            hPad = Math.round(h + sizes.padding_height);
        } catch (err) {
            if(typeof err.type !== 'undefined' && err.type == 'defer' && hasMutationObserver && !(exclusions & ShortPixelAI.EAGER)) {
                return;
            }
        }
    }
    ShortPixelAI.updateSrc(elm, 'src', w, h, ((exclusions & ShortPixelAI.EXCLUDED) == 0));
    ShortPixelAI.updateSrc(elm, 'data-src', w, h, ((exclusions & ShortPixelAI.EXCLUDED) == 0));
    //*DEBUG*/divModified =
    ShortPixelAI.updateSrc(elm, 'data-thumb', false, false, ((exclusions & ShortPixelAI.EXCLUDED) == 0));
    //*DEBUG*/? 1 : 0;
    //ShortPixelAI.updateInlineStyle(elm, w, Math.ceil(sizes.height), true);
    ShortPixelAI.updateInlineStyle(elm, wPad, hPad, ((exclusions & ShortPixelAI.EXCLUDED) == 0), spai_settings.crop ? ShortPixelAI.spaiCalculateBgClipResize(elm[0]) : false);
    ShortPixelAI.elementUpdated(elm, w);
};

SPAI.prototype.updateAHref = function(elm, hasMutationObserver, fromIntersection) {
    ShortPixelAI.updateAttr(elm, 'href');
};

SPAI.prototype.updateAttr = function(elm, attr) {
    if (typeof elm.attr('data-spai-upd') !== 'undefined'){
        return;
    }
    if( typeof elm.attr(attr) === 'undefined' ) {
        return;
    }
    var data = ShortPixelAI.updateSrc(elm, attr, window.screen.availWidth, window.screen.availHeight, !ShortPixelAI.is(elm, ShortPixelAI.EXCLUDED), true);
    ShortPixelAI.elementUpdated(elm, data.newWidth);
};

SPAI.prototype.is = function(elm, types) {
    var excluded = 0;
    if(types & ShortPixelAI.EAGER) {
        for(var i = 0; i < spai_settings.eager_selectors.length; i++) { //.elementor-section-stretched img.size-full
            var selector = spai_settings.eager_selectors[i];
            try {if(elm.is(selector)) excluded |= ShortPixelAI.EAGER;} catch (xc){spai_settings.debug && ShortPixelAI.log("eager:" + xc.message)} //we don't bother about wrong selectors at this stage
        }
    }

    if(types & ShortPixelAI.EXCLUDED) {
        for(var i = 0; i < spai_settings.excluded_selectors.length; i++) { //.elementor-section-stretched img.size-full
            var selector = spai_settings.excluded_selectors[i];
            try {if(elm.is(selector)) excluded |= ShortPixelAI.EXCLUDED;} catch (xc){spai_settings.debug && ShortPixelAI.log("excluded:" + xc.message)}
        }
    }

    if(types & ShortPixelAI.NORESIZE) {
        for(var i = 0; i < spai_settings.noresize_selectors.length; i++) { //.elementor-section-stretched img.size-full
            var selector = spai_settings.noresize_selectors[i];
            try {if(elm.is(selector)) excluded |= ShortPixelAI.NORESIZE;} catch (xc){spai_settings.debug && ShortPixelAI.log("noresize:" + xc.message)}
        }
    }
    return excluded;
};

SPAI.prototype.updateAHrefForIntegration = function(integration, theParent, query) {
    if(integration == 'CORE' || spai_settings.active_integrations[integration]) {
        jQuery(query, theParent).each(function(){
            var elm = jQuery(this);
            ShortPixelAI.updateAHref(elm);
        });
    }
};

SPAI.prototype.setupDOMChangeObserver = function() {
    //setup DOM change observer
    //TODO vezi de ce nu merge la localadventurer pe versiunea polyfill MutationObserver - caruselul de sus
    //TODO adauga optiune in settings sa nu foloseasca polyfill si sa inlocuiasca din prima (pentru browserele vechi - daca polyfill-ul lui MutationObserver nu se descurca)
    ShortPixelAI.mutationObserver = new MutationObserver(function(mutations) {
        if(ShortPixelAI.sniperOn) return;
        mutations.forEach(function(mutation) {
            //*DEBUG*/ console.log("Mutation type: " + mutation.type + " target Id: " + jQuery(mutation.target).attr("id") + " target: " + jQuery(mutation.target).html());
            if(mutation.type === 'attributes' && mutation.attributeName === 'id') {
                // a hack to mitigate the fact that the jQuery .init() method is triggering DOM modifications. Happens in jQuery 1.12.4 in the Sizzle function. Comment of jQuery Devs:
                // qSA works strangely on Element-rooted queries
                // We can work around this by specifying an extra ID on the root
                // and working up from there (Thanks to Andrew Dupont for the technique)
                // IE 8 doesn't work on object elements
                return;
            }

            //new nodes added by JS
            if(mutation.addedNodes.length) {
                //*DEBUG*/ console.log(mutation.addedNodes[0]);
                for(var i = 0; i < mutation.addedNodes.length; i++) {
                    //TODO if practice proves necessary: window.requestIdleCallback()?
                    try {
                        ShortPixelAI.handleUpdatedImageUrls(false, jQuery(mutation.addedNodes[i]), true, false);
                    }catch(error) {
                        if (error == 'defer_all') { //defer_all means the images are not ready.
                            setTimeout(ShortPixelAI.handleBody, 20 * ShortPixelAI.bodyCount);
                        } else {
                            throw error; //not ours
                        }
                    }
                }
            }

            //attributes changes
            if(mutation.type == 'attributes') {
                var attrClass = mutation.target.getAttribute('class');
                attrClass = (typeof attrClass === 'undefined' || attrClass === null) ? '' : attrClass;
                if(mutation.target.nodeName === 'BODY' && ShortPixelAI.containsPseudoSrc(attrClass) > 0) {
                    //this is because the body seems to become a zombie and fires mutations at will when under Developer Console
                    return;
                }
                if(jQuery(mutation.target).attr('id') == 'fancybox-wrap' && ShortPixelAI.fancyboxId != ShortPixelAI.fancyboxHooked) {
                    //NextGen specific (which uses fancybox for displaying a gallery slideshow popup)
                    ShortPixelAI.hookIntoFancybox(mutation.target);
                } else {
                    if(ShortPixelAI.timeOutHandle) {
                        clearTimeout(ShortPixelAI.timeOutHandle);
                        if((new Date()).getTime() - ShortPixelAI.mutationsLastProcessed > 100) {
                            ShortPixelAI.processMutations();
                        }
                    }
                    else {
                        ShortPixelAI.mutationsLastProcessed = (new Date()).getTime();
                    }
                    /*DEBUG*/ShortPixelAI.observedMutations++;

                    //images having width 0 are deferred for further replacement, so keep a list of mutations and analyze them with a delay (setTimeout)
                    ShortPixelAI.mutationsList[ShortPixelAI.xpath(mutation.target)] = {target: mutation.target, time: (new Date).getTime()};
                    ShortPixelAI.timeOutHandle = setTimeout(ShortPixelAI.processMutations, 50);
                }
            }
        });
    });
    var target = document.querySelector('body');
    var config = { attributes: true, childList: true, subtree: true, characterData: true }
    ShortPixelAI.mutationObserver.observe(target, config);
};

SPAI.prototype.processMutations = function() {
    //TODO if practice proves necessary: window.requestIdleCallback()?
    var mutationsLeft = 0;
    for(var mutationTarget in ShortPixelAI.mutationsList) {
        var mutationTargetJQ = jQuery(ShortPixelAI.mutationsList[mutationTarget].target);
        if(ShortPixelAI.mutationsList[mutationTarget].time + 50 > (new Date).getTime()) {
            //mutations having less than 50ms of age, don't process them yet as they might not be ready - for example a jQuery animate.
            mutationsLeft++;
            continue;
        }
        var outerHTML = mutationTargetJQ[0].outerHTML;
        if (mutationTargetJQ.length && (typeof spai_settings.affected_tags_map.script !== 'undefined' || ShortPixelAI.containsPseudoSrc(outerHTML) > 0 )) { // added the affected_tags_map.script condition because of HS# 60350 - mutations that have the style in a different place
            //console.log(" PROCESS MUTATIONS " + mutationTarget);
            //Changed fromIntersection to false to load the modifications images lazily too.
            //TODO TEST well (ref.: HS 986527864)
            ShortPixelAI.handleUpdatedImageUrlsWithRetry(false, mutationTargetJQ, true, false);
            if (outerHTML.indexOf('background') > 0) {
                ShortPixelAI.updateInlineStyle(mutationTargetJQ, false, false, !ShortPixelAI.is(mutationTargetJQ, ShortPixelAI.EXCLUDED), false);
            }
        }
        delete ShortPixelAI.mutationsList[mutationTarget];
    }
    ShortPixelAI.mutationsLastProcessed = (new Date()).getTime();
    if(mutationsLeft > 0) {
        ShortPixelAI.timeOutHandle = setTimeout(ShortPixelAI.processMutations, 50);
    }
}

SPAI.prototype.setupIntersectionObserverAndParse = function() {
    var options = {
        rootMargin: ShortPixelAI.intersectionMargin + 'px',
        threshold: 0
    };
    ShortPixelAI.intersectionObserver = new IntersectionObserver(function(entries, observer){
        //spai_settings.debug && ShortPixelAI.log("Intersection Observer called, scroll: " + (ShortPixelAI.getScroll().join(', ')));
        entries.forEach(function(entry) {
            if(entry.isIntersecting) {
                var elm = jQuery(entry.target);
                //spai_settings.debug && ShortPixelAI.log(elm[0].nodeName + " is intersecting - " + ShortPixelAI.parsePseudoSrc(elm[0].src).src);
                ShortPixelAI.handleUpdatedImageUrlsWithRetry(false, elm, true, true);
                if(window.getComputedStyle(entry.target)['background-image']) {
                    //var Sizes = ShortPixelAI.getSizesRecursive(elm, true);
                    ShortPixelAI.calcSizeAndUpdateInlineStyle(elm);
                }
                observer.unobserve(entry.target);
                ShortPixelAI.triggerEvent('spai-element-handled', entry.target);
            } else if (spai_settings.debug){
                var elm = jQuery(entry.target);
                ShortPixelAI.log(elm[0].nodeName + " is NOT intersecting - " + ShortPixelAI.parsePseudoSrc(elm[0].src).src);
            }
        });
    }, options);

    //function to return all elements affected by a certain inline style rule
    function ruleToDeclarations(style) {
        var rule = /([^{}]*?)\s*{.*background(-image|)\s*:([^;]*[,\s]|\s*)url\(['"]?(data:image\/svg\+xml[^'"\)]*?)(['"]?)\)/m
        //var rule = /([\s\S]*?)\s*{.*background(-image|)\s*:([^;]*[,\s]|\s*)url\(['"]?(data:image\/svg\+xml[^'"\)]*?)(['"]?)\)([\s\S]*?)(!important)?;/m
        var matches = style.match(rule);
        if(!matches || matches.length < 2) {
            return false;
        }
        return document.querySelectorAll(matches[1]);
    }

    function parseCssRule(rule, unmatchedRules) {
        if (typeof rule.cssText !== 'undefined' && ShortPixelAI.containsPseudoSrc(rule.cssText) > 0) {
            var elements = ruleToDeclarations(rule.cssText);
            if (elements && elements.length > 0) {
                for (var index = 0; index < elements.length; index++) {
                    var elm = elements[index];
                    if(ShortPixelAI.elementInViewport(elm)) {
                        ShortPixelAI.calcSizeAndUpdateInlineStyle(jQuery(elm));
                    } else {
                        ShortPixelAI.intersectionObserver.observe(elm);
                    }
                }
                //console.log(elements);
            } else {
                unmatchedRules.push(rule);
            }
        }
    }

    function handleUnmatchedRules(unmatchedRules) {
        if(unmatchedRules.length) {
            var cssText = '';
            for(var i = 0; i < unmatchedRules.length; i++) {
                let rule = unmatchedRules[i];
                var result = ShortPixelAI.replaceBackgroundPseudoSrc(rule.cssText, false);
                if (result.replaced) {
                    cssText += result.text;
                }
            }
            var css = document.createElement('style');
            css.type = 'text/css';
            css.innerText = cssText;
            document.getElementsByTagName("head")[0].appendChild(css);
        }
    }

    function parseCssStyleSheets(style) {
        var styleSheetIndex = 0;
        var ruleKeyChunk = 0;
        var unmatchedRules = [];
        //handle the stylesheets rules asynchronously in chunks of 1000
        var chunkInterval = setInterval(function(){
            var style = document.styleSheets[styleSheetIndex];
            if(typeof style === 'undefined') {
                clearInterval(chunkInterval);
                handleUnmatchedRules(unmatchedRules);
                return;
            }
            try {
                for(var ruleKey = ruleKeyChunk * 1000; ruleKey < (ruleKeyChunk + 1) * 1000; ruleKey++) {
                    var rule = style.rules[ruleKey];
                    if(typeof rule === 'undefined') {
                        styleSheetIndex++;
                        ruleKeyChunk = 0;
                        return;
                    }
                    if(rule instanceof CSSStyleRule) {
                        parseCssRule(rule, unmatchedRules);
                    } else if(typeof rule.cssRules !== 'undefined') {
                        for (var subRuleKey in rule.cssRules) {
                            var subRule = rule.cssRules[subRuleKey];
                            parseCssRule(subRule);
                        }
                    }
                }
                ruleKeyChunk++;
            } catch (dex) {
                //sometimes it throws this exception:
                //DOMException: Failed to read the 'rules' property from 'CSSStyleSheet': Cannot access rules at CSSStyleSheet.invokeGetter
                //console.log(dex.message);
                styleSheetIndex++;
            }
        }, 0);
    }

    //body
    //initial parse of the document
    ShortPixelAI.handleBody();

    //check the stylesheets, some optimizers (for example Swift Performance) extract the inline CSS into .css files
    // Do not keep the condition for iPhones/pads because now we don't change the css any more. But keep the code here just in case for now. TODO: remove
    //if(true || !navigator.platform || !/iPad|iPhone|iPod/.test(navigator.platform)) {
    if(spai_settings.lazy_bg_style || !!spai_settings.affected_tags_map.div && spai_settings.affected_tags_map.div & 2) {
        parseCssStyleSheets();
    }

    //setup the mutation observer here too, because if the IntersectionObserver polyfill is needed, it should be done after that one is loaded.
    if(typeof window.MutationObserver !== 'function') {
        jQuery.getScript(spai_settings.plugin_url + '/assets/js/MutationObserver.min.js?' + spai_settings.version, ShortPixelAI.setupDOMChangeObserver);
    } else {
        ShortPixelAI.setupDOMChangeObserver();
    }
};


SPAI.prototype.replaceBackgroundPseudoSrc = function(text, cssFile){
    var replaced = false;
    //regexps are identical, need to duplicate them because the first will use is internal pointer to replace all
    text.replace(        /background(-image|)\s*:([^;]*[,\s]|\s*)url\(['"]?(data:image\/svg\+xml[^'"\)]*?)(['"]?)\)/gm, function(item){
        var oneMatcher = /background(-image|)\s*:([^;]*[,\s]|\s*)url\(['"]?(data:image\/svg\+xml[^'"\)]*?)(['"]?)\)/m;
        var match = oneMatcher.exec(item);
        var parsed = ShortPixelAI.parsePseudoSrc(match[3]);
        if(!parsed.src) return; //not our inline image
        //devicePixelRatio is applied in composeApiUrl
        var screenWidth = window.screen.width;
        var setMaxWidth = spai_settings.backgrounds_max_width ? spai_settings.backgrounds_max_width : 99999;

        var newSrc;
        if(cssFile !== false && parsed.src.indexOf('../') === 0) {//relative path in a CSS file
            var absSrc = cssFile.substr(0,cssFile.lastIndexOf('/')) + '/' + parsed.src;
            var l = document.createElement("a");
            l.href = absSrc;
            parsed.src = l.href;
        }
        newSrc = ShortPixelAI.composeApiUrl(false, parsed.src, Math.min(parsed.origWidth, screenWidth, setMaxWidth), false, false);
        text = text.replace(match[3], newSrc);
        replaced = true;
    });
    return {text: text, replaced: replaced};
};

//TODO sa luam de la WP Rocket versiunea mai versatila?
SPAI.prototype.elementInViewport = function(el) {
    if(!( el.offsetWidth || el.offsetHeight || el.getClientRects().length )) {
        return false;
    }
    var rect = el.getBoundingClientRect();
    var threshold = ShortPixelAI.intersectionMargin;

    return (
        rect.bottom + threshold    >= 0
        && rect.right + threshold   >= 0
        && rect.top - threshold <= (window.innerHeight || document.documentElement.clientHeight)
        && rect.left - threshold <= (window.innerWidth || document.documentElement.clientWidth)
    );
};

SPAI.prototype.hookIntoFancybox = function(theParent){
    if(ShortPixelAI.fancyboxId.length == 0 || ShortPixelAI.fancyboxHooked !== 'none') {
        return;
    }
    //console.log("HookIntoFancybox");
    var theOverlay = jQuery(theParent);
    var elm = jQuery('a#fancybox-right', theOverlay);
    elm.mousedown(function(e){
        var newId = ShortPixelAI.fancyboxChangeId(1);
        //console.log("right " + newId);
        var nextElm = jQuery('div#' + newId + " a.ngg-fancybox");
        if(nextElm.length) {
            ShortPixelAI.fancyboxUpdateWidth(nextElm);
        }
    });
    var elm = jQuery('a#fancybox-left', theOverlay);
    elm.mousedown(function(e){
        var newId = ShortPixelAI.fancyboxChangeId(-1);
        //console.log("left " + newId);
        var prevElm = jQuery('div#' + newId + " a.ngg-fancybox");
        if(prevElm.length) {
            ShortPixelAI.fancyboxUpdateWidth(prevElm);
        }
    });
    ShortPixelAI.fancyboxHooked = ShortPixelAI.fancyboxId;
};

SPAI.prototype.fancyboxChangeId = function(delta) {
    var parts = ShortPixelAI.fancyboxId.match(/(.*)([0-9]+)$/);
    return parts[1] + (parseInt(parts[2]) + delta);
};

SPAI.prototype.prepareUrl = function( url ) {
    if ( !url.match( /^http[s]{0,1}:\/\/|^\/\// ) ) {
        if ( url.startsWith( '/' ) ) {
            if ( typeof ShortPixelAI.aHref === 'undefined' ) {
                ShortPixelAI.aHref = document.createElement( 'a' );
            }

            ShortPixelAI.aHref.href = spai_settings.site_url;
            url = ShortPixelAI.aHref.protocol + '//' + ShortPixelAI.aHref.hostname + url;
        }
        else {
            var href = window.location.href.split( '#' )[ 0 ].split( '?' )[ 0 ]; //get rid of hash and query string
            if ( !href.endsWith( '/' ) ) {
                //fix the problem of relative paths to paths not ending in '/' - remove the last base path item
                var hrefp = href.split( '/' );
                hrefp.pop();
                href = hrefp.join( '/' ) + '/';
            }
            url = href + url;
            if ( url.indexOf( '../' ) > 0 ) {
                //normalize the URL
                var l = document.createElement( 'a' );
                l.href = url;
                url = l.protocol + '//' + l.hostname + ( l.pathname.startsWith( '/' ) ? '' : '/' ) + l.pathname + l.search + l.hash;

            }
        }
    }

    return url;
}

SPAI.prototype.composeApiUrl = function(doRegister, src, w, h, specialCrop) {

    src = this.prepareUrl( src );

    //get the image extension
    var extensionRegEx = /(?:\.([^.\/\?]+))(?:$|\?.*)/;
    extensionRegEx.lastIndex = 0;
    var extensionMatches = extensionRegEx.exec( src );
    var ext = typeof extensionMatches === 'object' && extensionMatches !== null && typeof extensionMatches[ 1 ] === 'string' && extensionMatches[ 1 ] !== '' ? extensionMatches[ 1 ] : 'jpg';
    ext = ext === 'jpeg' ? 'jpg' : ext;
    if ( ext === 'svg' || ext === 'webp' || ext === 'avif') {
        w = h = 0; // no need to add size parameters to a SVG and WebP is only stored
    }
    var apiUrl = spai_settings.api_url;

    if(w > 1 && w < 99999) {
        var pixelRatio = (typeof window.devicePixelRatio === 'undefined') ? 1 : window.devicePixelRatio;

        //discrete values for widths based on an exponential of the defined rate
        if(spai_settings.size_breakpoints.on) {
            let wbp = ShortPixelAI.getSizeBreakpoint(w, spai_settings.size_breakpoints.base, 1 + spai_settings.size_breakpoints.rate / 100);
            if(h) {
                //adjust the height accordingly.
                h = Math.round(wbp / w * h);
            }
            w = wbp;
        }

        w = Math.round(w * pixelRatio);
        h = h ? Math.round(h * pixelRatio) : undefined;
        //use a register to keep all the SRCs already resized to a specific sizes, if it's already there with a larger width, then use that width, if not add/update it.
        if(ShortPixelAI.urlRegister[src] === undefined || ShortPixelAI.urlRegister[src].width < w ) {
            if(doRegister) { //only the img src's are registered as the others might not get loaded...
                ShortPixelAI.urlRegister[src] = {width: w, height: h};
            }
        } else if (   !ShortPixelAI.urlRegister[src].height && !h
                   || !!ShortPixelAI.urlRegister[src].height && !!h && (Math.abs(1.0 - w * ShortPixelAI.urlRegister[src].height / h / ShortPixelAI.urlRegister[src].width ) < 0.005))  { //same aspect ratio
            h = ShortPixelAI.urlRegister[src].height;
            w = ShortPixelAI.urlRegister[src].width;
        }
        if(!specialCrop) {
            apiUrl = apiUrl.replace("%WIDTH%", "" + w + (h ? "+h_" + h : ""));
        }
    }
    else if(!specialCrop){
            apiUrl = apiUrl.replace("w_%WIDTH%" + spai_settings.sep, '');
            apiUrl = apiUrl.replace("w_%WIDTH%", ''); //maybe it's the last param, no separator...
    }

    let convType = ShortPixelAI.conversionType;
    let nextgen = spai_settings.extensions_to_nextgenimg;
    let srcProto = src.split('://')[0];
    let srcSfx = (srcProto == 'https' ? '' : '+p_h') + '/' + src.replace(/^https?:\/\//, '');

    switch ( ext ) {
        case 'svg':
        case 'webp':
        case 'avif':
            apiUrl = spai_settings.serve_svg ? ( spai_settings.api_short_url + srcSfx ) : src; //TODO remove after a while - SVG and other non-optimizable resources are by default stored on CDN
            break;
        case 'png':
            apiUrl = apiUrl + ( convType && nextgen.png ? spai_settings.sep + convType : '' ) + srcSfx;
            break;
        case 'jpg':
            apiUrl = apiUrl + ( convType && nextgen.jpg ? spai_settings.sep + convType : '' ) + srcSfx;
            break;
        case 'gif':
            apiUrl = apiUrl + ( convType && nextgen.gif ? spai_settings.sep + convType : '' ) + srcSfx;
            break;
        default: //just to be sure...
            apiUrl = apiUrl + ( convType ? spai_settings.sep + convType : '' ) + srcSfx;
            break;
    }

    if(specialCrop) {
        var sc = "sc_" + specialCrop.clip.offsetX + "x" + specialCrop.clip.offsetY +
            ":" + specialCrop.clip.width + "x" + specialCrop.clip.height +
            ":" + (specialCrop.viewport.width * pixelRatio) + "x" + (specialCrop.viewport.height * pixelRatio);

        apiUrl = apiUrl.replace("w_%WIDTH%", sc);

        //apiUrl = apiUrl.replace( "+",  sc + "+");
    }

    return apiUrl;
};

/**
 * calculate a size breakpoint based on an exponential series having the first element base and the growth rate
 * @param size
 * @param base - first and minimum size
 * @param rate - the growth factor
 * @returns {number}
 */
SPAI.prototype.getSizeBreakpoint = function (size, base, rate) {
    if(size <= base) return base;
    let exponent = Math.floor(Math.log(size / base) / Math.log(rate));
    if(size <= Math.ceil(base * Math.pow(rate, exponent))) return Math.ceil(size);
    return Math.ceil(base * Math.pow(rate, exponent + 1));
}

SPAI.prototype.isFullPseudoSrc = function(pseudoSrc) {
    //return pseudoSrc.indexOf('data:image/gif;u=') >= 0;
    return this.parsePseudoSrc(pseudoSrc).full;
};

SPAI.prototype.containsPseudoSrc = function( pseudoSrc ) {
	return typeof pseudoSrc === 'string' ? pseudoSrc.indexOf( 'data:image/svg+xml;base64' ) >= 0 : false;
};

/**
 * New implementation
 *
 * @param {string} pseudoSrc
 * @returns {{origHeight: number, src: boolean, origWidth: number, full: boolean}}
 */
SPAI.prototype.parsePseudoSrc = function( pseudoSrc ) {
	var prepared     = {
			src        : false,
            bip        : true,
			full       : false,
            origWidth  : 0,
            origHeight : 0
		},
		base64RegExp = /([A-Za-z0-9+/]{4})*([A-Za-z0-9+/]{3}=|[A-Za-z0-9+/]{2}==)?$/;

	if ( typeof pseudoSrc === 'undefined' || !this.containsPseudoSrc( pseudoSrc ) ) {
		return prepared;
	}

	var $svgElement, svgDecoded, svgEncoded;
	pseudoSrc = pseudoSrc.trim();

	if ( typeof pseudoSrc === 'string' ) {
		svgEncoded = pseudoSrc.match( base64RegExp );
		svgEncoded = svgEncoded.length > 0 ? svgEncoded[ 0 ] : undefined;

		if ( typeof svgEncoded === 'string' ) {
			svgDecoded = atob( svgEncoded );
		}
	}

	try {
		if ( typeof svgDecoded === 'string' ) {
			$svgElement = jQuery( svgDecoded );
		}
	}
	catch ( x ) {
		spai_settings.debug && ShortPixelAI.log( 'svgDecoded: ' + svgDecoded );
		return prepared;
	}

	if ( $svgElement instanceof jQuery ) {
        var metaData = {
            url    : $svgElement.data( 'u' ),
            bip    : $svgElement.data( 'bip' ),
            width  : $svgElement.data( 'w' ),
            height : $svgElement.data( 'h' )
        };

		prepared.src = metaData.url === undefined || metaData.url === '' ? prepared.src : ShortPixelAI.urldecode( metaData.url );
		prepared.bip = typeof metaData.bip !== 'undefined';

		if ( prepared.src !== '' && typeof prepared.src === 'string' ) {
		    // Needed for LQIP module. Because backend doesn't modify the source (url)
		    var originalSrc = prepared.src;

			if ( prepared.src.lastIndexOf( '//', 0 ) === 0 ) {
				// if the url doesn't have the protocol, use the current one
				prepared.src = window.location.protocol + prepared.src;
			}

            if (   !!window.spai_settings.lqip
                && typeof this.modules.lqip === 'object'
                && !!prepared.bip ) {
                this.modules.lqip.add( this.prepareUrl( prepared.src ), originalSrc );
            }
		}

		prepared.origWidth = metaData.width === undefined || metaData.width === '' ? prepared.origWidth : metaData.width;
		prepared.origHeight = metaData.height === undefined || metaData.height === '' ? prepared.origHeight : metaData.height;
		prepared.full = typeof prepared.src === 'string' && prepared.src !== '';
	}

	return prepared;
};

/**
 * OLD IMPLEMENTATION
 *
 * @param pseudoSrc
 * @returns {{origHeight: number, src: boolean, origWidth: number, full: number}|boolean}
 *
SPAI.prototype.parsePseudoSrc = function(pseudoSrc) {
    var src = false;
    var origWidth = 0, origHeight = 0, full = false;
    var full = ShortPixelAI.isFullPseudoSrc(pseudoSrc) ? 1 : 0;

    if(full) {
        var parts = pseudoSrc.split(',');
        if(parts.length == 2) {
            full = true;
            pseudoSrc = parts[0];
            var subparts = pseudoSrc.split(';');
            subparts.shift();
        } else {
            return false;
        }
    } else {
        var subparts = pseudoSrc.split(';');
    }
    if(subparts[0].indexOf('u=') == 0) {
        src = ShortPixelAI.urldecode(atob(subparts[0].substring(2)));
        if(src.lastIndexOf('//', 0) == 0) {
            //if the url doesn't have the protocol, use the current one
            src = window.location.protocol + src;
        }
    }
    if(subparts.length >= 2 + full) {// if full we have the last part: base64 so count one more
        var p2 = subparts[1].split('=');
        if(p2.length == 2 && p2[0] == 'w') {
            origWidth = p2[1];
        } else {
            origWidth = 99999;
        }
    }
    if(subparts.length >= 3 + full) {
        var p2 = subparts[2].split('=');
        if(p2.length == 2 && p2[0] == 'h') {
            origHeight = p2[1];
        } else {
            origHeight = 99999;
        }
    }
    return { src: src, origWidth: origWidth, origHeight: origHeight, full: full};
};
*/

SPAI.prototype.updateSrc = function(elm, attr, w, h, isApi, maxHeight) {
    var pseudoSrc = elm.attr('data-spai-' + attr + '-meta');
    if(typeof pseudoSrc === 'undefined') {
        var pseudoSrc = elm.attr(attr);
        if(typeof pseudoSrc === 'undefined') {
            return false;
        }
    }
    var data = ShortPixelAI.parsePseudoSrc(pseudoSrc);
    var src = data ? data.src : false;
    if(!src) {
        return false;
    }

    data.crop = false;
    var forceCrop = elm.attr('data-spai-crop');
    if( (typeof forceCrop !== 'undefined') && (forceCrop === 'true')) {
        data.crop = true;
    }

    if(typeof maxHeight === 'undefined' || !maxHeight) {
        //make sure that if the image container has an imposed (min)height, it's taken into account
        if( h > data.origHeight * w / data.origWidth ) {
            if(spai_settings.crop === "1") {
                data.crop = true;
            } else {
                w = Math.round(data.origWidth * h / data.origHeight);
            }
        }
    } else {
        //make sure that if the image container has an imposed (max)height, it's taken into account
        if( h < data.origHeight * w / data.origWidth ) {
            w = Math.round(data.origWidth * h / data.origHeight);
        }
    }
    data.newWidth = data.origWidth > 1 ? Math.min(data.origWidth, w) : w;
    var newSrc = isApi ? ShortPixelAI.composeApiUrl(attr == 'src' && elm.is('img'), src, data.newWidth, data.crop ? h : false, false) : src;
    data.newSrc = newSrc;

    //load images and wait until they've succesfully loaded
    elm.attr(attr, newSrc);
    elm.removeAttr('data-spai-' + attr + '-meta');

    return data;
};

SPAI.prototype.updateSrcSet = function(elm, w, origData) {
    var srcSet = elm.attr('srcset');
    var sizes = elm.attr('sizes');
    var updated = ShortPixelAI.parsePseudoSrcSet(srcSet, sizes, w, origData);
    if(updated.srcSet.length) {
        elm.attr('srcset', updated.srcSet);
    }
    if(updated.sizes.length) {
        elm.attr('sizes', updated.sizes);
    }
};

SPAI.prototype.parsePseudoSrcSet = function(srcSet, sizes, w, origData) {
    var newSrcSet = '';
    var newSizes = '';
    if(srcSet) {
        var srcList = srcSet.match(/[^\s,][^\s]+(\s+[0-9wpx]+\s*,?|\s*,|\s*$)/g); //split(", ");
        for(var i = 0; i < srcList.length; i++) {
            var item = srcList[i].replace(/,$/, '').trim();
            var newItem = '';
            var itemParts = item.split(/\s+/);
            if(this.isFullPseudoSrc(itemParts[0])) {
                var itemParts = item.split(/\s+/);
                if(itemParts.length >= 2) {
                    var itemData = ShortPixelAI.parsePseudoSrc(itemParts[0]);
                    if(itemData.src) {
                        newItem = ShortPixelAI.composeApiUrl(false, itemData.src, false, false, false) + " " + itemParts[1];
                    }
                    if(w == parseInt(itemParts[1])) {
                        origData = false; //no need to add the original as it's already in the srcset
                    }
                    else if(origData && w < parseInt(itemParts[1])) {
                        newSrcSet += ShortPixelAI.composeApiUrl(false, origData.src, w, false, false) + " " + w + 'w,';
                        origData = false;
                    }
                }
            }
            if(!newItem.length) {
                newItem = item;
            }
            newSrcSet += newItem + ', ';
        }
        newSrcSet = newSrcSet.replace(/,+\s+$/, '');
    }
    else if (origData && (spai_settings.method == 'srcset') && w < origData.origWidth * 0.9) {
        newSrcSet = ShortPixelAI.composeApiUrl(false, origData.src, w, false, false) + " " + w + 'w, ' + origData.src + " " + origData.origWidth + "w";
        newSizes = Math.round(100 * w / origData.origWidth) + "vw, 100vw";
    }
    return {srcSet: newSrcSet, sizes: newSizes};
}

SPAI.prototype.removeSrcSet = function(elm) {
    var srcSet = elm.attr('srcset');
    if(typeof srcSet !== 'undefined' && srcSet.length) {
        elm.attr('srcset', '');
        elm.attr('sizes', '');
    }
};

SPAI.prototype.calcSizeAndUpdateInlineStyle = function(elm) {
    var cssData = ShortPixelAI.spaiCalculateBgClipResize(elm[0]);
    for (var key in cssData) {
        var width = !!cssData[key].viewport ? cssData[key].viewport.width : cssData[key].width;
        var height = !!cssData[key].viewport ? cssData[key].viewport.height : cssData[key].height;
        ShortPixelAI.updateInlineStyle(elm, width, height, !ShortPixelAI.is(elm, ShortPixelAI.EXCLUDED), !!cssData[key].viewport ? cssData: false);
    }
}

SPAI.prototype.updateInlineStyle = function(elm, w, h, isApi, cssData) {
    var backgroundStyle = window.getComputedStyle(elm[0])['backgroundImage'];
    var bgStyleOrig = backgroundStyle;
    var pseudoSrcs = cssData ? Object.keys(cssData) : ShortPixelAI.getBackgroundPseudoImages('background-image:' + backgroundStyle);
    var affectedData = [];

    if ( !pseudoSrcs ) return;

    for ( var index = 0; index < pseudoSrcs.length; index++ ) {
        var pS = pseudoSrcs[index];
        var data = cssData && !!cssData[pS] && !!cssData[pS].originalUrl
            ? {src: cssData[pS].originalUrl, origWidth: cssData[pS].originalWidth, origHeight: cssData[pS].originalHeight}
            : ShortPixelAI.parsePseudoSrc(pS);
        var src = data ? data.src : false;

        if(src){
            //remove the " from beginning and end, happens when the original URL is surrounded by &quot;
            while(src.charAt(0) == '"'){
                src = src.substring(1);
            }
            while(src.charAt(src.length-1)=='"') {
                src = src.substring(0,src.length-1);
            }
        } else {
            return false;
        }

        //devicePixelRatio is applied in composeApiUrl
        var setMaxWidth = spai_settings.backgrounds_max_width ? spai_settings.backgrounds_max_width : 99999;
        var origWidth = data.origWidth > 0 ? data.origWidth : 99999;
        var origHeight = data.origHeight > 0 ? data.origHeight : 99999;

        /*if(spai_settings.TODO_PASS_SETTING && w !== false && w > origWidth) {
            //if viewport width is larger than the original width AND it's a thumbnail, remove the thumbnail suffix from URL
            var suf = src.match(/(-[0-9]+x[0-9]+)(\.[a-zA-Z0-9]+)$/);
            if(!!suf){
                src = src.replace(suf[0], suf[2]);
                origWidth = origHeight = 99999;
            }
        }*/

        var cappedWidth = Math.min(origWidth , window.screen.width, w ? w : 99999, setMaxWidth);
        //if no original height, then it doesn't make sense to calculate the capped height as we can't determine the aspect ratio
        var cappedHeight = origHeight < 99999 ? Math.min(origHeight , window.screen.height, h ? h : 99999) : 99999;

        var specialCrop = false;
        if(spai_settings.crop == "1") {
            for (var key in cssData) {
                if( !!cssData[key].originalWidth && cssData[key].viewport
                   && (cssData[key].originalWidth != cssData[key].viewport.width || cssData[key].originalHeight != cssData[key].viewport.height)) {
                    specialCrop = {
                        alterCss: cssData[key].alterCss,
                        clip : cssData[key].clip,
                        viewport : cssData[key].viewport
                    };
                    if(cssData[key].alterCss)
                        elm.css(cssData[key].alterCss);
                }
            }
        }

        var newSrc = isApi
            ? ShortPixelAI.composeApiUrl(false, src,
                cappedWidth < 99999 ? cappedWidth: false,
                (!!spai_settings.crop) && cappedHeight < 99999 ? cappedHeight: false,
                specialCrop)
            : src;

        //elm.css('background-image', 'url(' + newSrc + ')');
        backgroundStyle = backgroundStyle.replace(pseudoSrcs[index], newSrc);

        // getting current styles again after pass
        //style = elm.attr( 'style' );

        affectedData.push( data );
    }
    elm[0].style.setProperty('background-image', backgroundStyle);
    if(bgStyleOrig === getComputedStyle(elm[0])['backgroundImage']) {
        //means that the original style has !important, set it as well
        elm[0].style.setProperty('background-image', backgroundStyle, 'important');
    }

    return affectedData.length > 0 ? affectedData : false;
};

/**
 * Method parses inline styles and returns an array of urls of placeholders to be replaced
 * Otherwise returns false
 *
 * @param {string} style Inline styles
 * @returns {boolean|array}
 */
SPAI.prototype.getBackgroundPseudoImages = function( style ) {
    if ( typeof style === 'undefined' || style.indexOf( 'background' ) < 0 ) {
        return false;
    }
    var regExp        = /(background-image|background)\s*:([^;]*[,\s]|\s*)url\(['"]?([^'"\)]*?)(['"]?)\)/gm,
        matches,
        pseudoSources = [];

    while ( ( matches = regExp.exec( style ) ) !== null ) {
        if ( !matches || matches.length < 3 ) {
            return false;
        }

        if ( matches[ 3 ].indexOf( 'data:image' ) >= 0 ) {
            pseudoSources.push( matches[ 3 ] );
        }
    }

    return pseudoSources.length > 0 ? pseudoSources : false;
};

SPAI.prototype.urldecode = function(str) {
    return decodeURIComponent((str + '').replace(/\+/g, '%20'));
};

/**
 * New version of getSizesRecursive, no recursive needed, no jQuery
 * TODO remove, it seems less effective than the recursive version - for example it doesn't work properly for the MyListings theme's backgrounds.
 * @param elm
 * @param deferHidden
 * @returns {{status: string, width: number, height: number, padding: number, padding_height: number}}
SPAI.prototype.getSizes = function(elm, deferHidden) {
    var style = getComputedStyle(elm);
    if ( deferHidden && !( elm.offsetWidth || elm.offsetHeight || elm.getClientRects().length ) && ( style.visibility !== 'visible' || style.display === 'none' || style.opacity < 0.02 ) ) {
        throw { type : 'defer', cause : 'invisible' };
    }
    var containerWidth = elm.clientWidth;
    var optWidth = elm.attributes.width ? elm.attributes.width.value : 'auto';
    var optHeight = elm.attributes.height ? elm.attributes.height.value : 'auto';
    var containerHeight = optWidth === 'auto' || optHeight === 'auto' || optWidth === '100%' || optHeight === '100%' ? elm.clientHeight : parseFloat(optHeight / optWidth * containerWidth);
    containerWidth = parseFloat(containerWidth);
    containerHeight = parseFloat(containerHeight);
    if (isNaN(containerHeight)) {
        containerHeight = 0;
    }
    var ret = {
        status: 'success',
        width: containerWidth - ShortPixelAI.percent2px(style['padding-left'], containerWidth) - ShortPixelAI.percent2px(style['padding-right'], containerWidth),
        height: containerHeight - ShortPixelAI.percent2px(style['padding-top'], containerHeight) - ShortPixelAI.percent2px(style['padding-bottom'], containerHeight),
        padding: 0,
        padding_height: 0
    };
    return ret;
}
*/

/**
 * @type {{width, padding}}
 */
SPAI.prototype.getSizesRecursive = function(elm, deferHidden) {
    if(!elm.is(':visible') && deferHidden) {
        throw {type: 'defer', cause: 'invisible'};
    }
    var computedStyle = window.getComputedStyle(elm[0]);
    var width = computedStyle['width'];
    var height = computedStyle['height'];
    var w = parseFloat(width);
    var h = parseFloat(height);
    if(width == '0px' && elm[0].nodeName !== 'A') {
        //will need to delay the URL replacement as the element will probably be rendered by JS later on...
        //but skip <a>'s because these haven't got any size
        throw {type: 'defer', cause: 'width 0'};
    }
    if(width.slice(-1) == '%') {
        if(typeof elm.parent() === 'undefined') return {width: -1};
        var parentSizes = ShortPixelAI.getSizesRecursive(elm.parent(), deferHidden);
        if(parentSizes == -1) return {width: -1, padding: 0};
        w = parentSizes.width * w / 100;
        if(height.slice(-1) == '%') {
            h = parentSizes.height * h / 100;
        }
    }
    else if(w <= 1) {
        if(elm[0].tagName === 'IMG' && typeof elm.attr('width') !== "undefined" && typeof elm.attr('height') !== "undefined") {
            w = parseInt(elm.attr('width'));
            h = parseInt(elm.attr('height'));
        } else {
            if(typeof elm.parent() === 'undefined') return {width: -1, padding: 0};
            var parentSizes = ShortPixelAI.getSizesRecursive(elm.parent(), deferHidden);
            if(parentSizes.width == -1) return {width: -1, padding: 0};
            w = parentSizes.width;
            h = parentSizes.height;
        }
        w -= ShortPixelAI.percent2px(computedStyle['margin-left'], w) + ShortPixelAI.percent2px(computedStyle['margin-right'], w);
        h -= ShortPixelAI.percent2px(computedStyle['margin-top'], h) + ShortPixelAI.percent2px(computedStyle['margin-bottom'], h);
    }
    var pw = ShortPixelAI.percent2px(computedStyle['padding-left'], w) + ShortPixelAI.percent2px(computedStyle['padding-right'], w)
        + ShortPixelAI.percent2px(computedStyle['border-left-width'], w) + ShortPixelAI.percent2px(computedStyle['border-right-width'], w);
    var ph = ShortPixelAI.percent2px(computedStyle['padding-top'], h) + ShortPixelAI.percent2px(computedStyle['padding-bottom'], h)
        + ShortPixelAI.percent2px(computedStyle['border-top-width'], h) + ShortPixelAI.percent2px(computedStyle['border-bottom-width'], h);
    //h = Math.round(h);
    return {
        status: 'success',
        width: w - pw,
        height: h - ph,
        padding: pw,
        padding_height: ph,
    }
};

/**
 *
 * @param elm
 * @param parseImageUrl - function that returns the sizes and original URL
 * @returns {Array}
 */
SPAI.prototype.spaiCalculateBgClipResize = function(elm) {
    var style = getComputedStyle(elm);

    if(style.backgroundRepeat.indexOf('round') >= 0) {
        throw "Unsupported background-repeat: round";
    }

    var elmW = elm.offsetWidth,
        elmH = elm.offsetHeight,
        bgImg = style.backgroundImage;
    if(bgImg === "none") {
        //lazy loading might be in place, try the direct style.
        bgImg = elm.style.backgroundImage;
    }

    if(!bgImg.match(/url\s*\(/)) return [];

    for(var i = 0, bgImgs = {}; i < bgImg.length; i++) {
        //var match = bgImg.match(/url\s*\(["'](.+?)["']\)/);
        var match = bgImg.match(/\w*-gradient\s*\((?:[^\(\)]+|\([^\(\)]+\))*\)|none|initial|inherit|url\s*\(["'](.+?)["']\)/);
        if(match == null) break;
        if(match[0].substr(0,3) !== 'url') {
            bgImg = bgImg.substr(match.index + match[0].length);
            continue;
        }

        var parsedSrc = ShortPixelAI.parsePseudoSrc(match[1]);

        if(parsedSrc.origWidth === false || parsedSrc.origHeight === false || parsedSrc.origWidth == 1 || parsedSrc.origHeight == 1) {
            bgImgs[match[1]] = {/*style: match,*/
                width: (elmW && ( idxVal(style.backgroundSize, i) == 'cover' || idxVal(style.backgroundSize, i) == 'contain') ? elmW : false),
                height: false, originalUrl: parsedSrc.src};
        }
        else {
            var imgItem = {width: 0, height: 0};
            imgItem.originalUrl = parsedSrc.src;
            imgItem.originalWidth = imgItem.width = parsedSrc.origWidth;
            imgItem.originalHeight = imgItem.height = parsedSrc.origHeight;
            var data = calculateBgClipResize(i, elm, imgItem, style);
            if(imgItem.width == imgItem.originalWidth && imgItem.height == imgItem.originalHeight) {
                imgItem.width = imgItem.height = false;
            }
            if(data.clip.offsetX == 0 && data.clip.offsetY == 0
            && data.clip.width == imgItem.originalWidth && data.clip.height == imgItem.originalHeight
            && Math.abs(imgItem.originalHeight * data.clip.width / data.clip.height - imgItem.originalWidth) <= 2) {
                //the image is just scaled
                imgItem.width = data.viewport.width;
                imgItem.height = false;
            } else {
                imgItem.clip = data.clip;
                imgItem.viewport = data.viewport;
                imgItem.alterCss = data.alterCss;
            }
            //imgItem.style = match;
            bgImgs[match[1]] = imgItem;

        }

        bgImg = bgImg.substr(match.index + match[0].length);
    }
    return bgImgs;

    function    calculateBgClipResize(index, elm, imgItem, style) {
        var data = {
            clip: {
                width: imgItem.width,
                height: imgItem.height,
                offsetX: 0,
                offsetY: 0
            },
            viewport:{
                width:Math.min(elm.offsetWidth, imgItem.width),
                height: Math.min(elm.offsetHeight, imgItem.height)
            },
            alterCss: {}
        };

        //1. Determine the scaling of the image
        var imgItemScaled = getScaleSize(index, elm, imgItem, style);

        getTranslation(index, elm, imgItem, imgItemScaled, style);

        var vPort = getViewport(index, elm, imgItem, imgItemScaled, style);

        imgItemScaled = normalizeOffsets(index, imgItemScaled, vPort);

        var intersected = intersect(index, imgItemScaled, vPort);

        data.clip.width = Math.round(intersected.width / imgItemScaled.scalex);
        data.clip.height = Math.round(intersected.height / imgItemScaled.scaley);
        data.clip.offsetX = ( intersected.width < imgItemScaled.width && isRepeat(style, index, 'x')
        || imgItemScaled.offsetx < intersected.x && !isRepeat(style, index, 'x')
            ? Math.round(Math.max(0, (intersected.x-imgItemScaled.offsetx) / imgItemScaled.scalex)) : 0);
        data.clip.offsetY = ( intersected.height < imgItemScaled.height && isRepeat(style, index, 'y')
        || imgItemScaled.offsety < intersected.y && !isRepeat(style, index, 'y')
            ? Math.round(Math.max(0, (intersected.y -imgItemScaled.offsety) / imgItemScaled.scaley)) : 0);
        data.viewport.width = Math.round(intersected.width / Math.max(1, imgItemScaled.scalex));
        data.viewport.height = Math.round(intersected.height / Math.max(1, imgItemScaled.scaley));
        data.alterCss = intersected.alterCss;

        return data;
    }

    function getScaleSize(index, elm, imgItem, style) {
        var bgSize = idxVal(style.backgroundSize, index), //auto, contain, cover, 200%, 300px 500px,
            elmBgWidth = elmAxisSize(index, elm, style, 'x'),
            elmBgHeight = elmAxisSize(index, elm, style, 'y'),
            imgAR = imgItem.width / imgItem.height,
            elAR = style.backgroundAttachment === 'fixed' ? imgAR : elmBgWidth / elmBgHeight,
            imgItemScaled = {src: imgItem.src, width: imgItem.width, height: imgItem.height, scalex: 1, scaley: 1};

        switch (bgSize) {
            case 'contain':
            case 'cover':
                if(bgSize === 'contain' && elAR <= imgAR || bgSize === 'cover' && elAR >= imgAR) {
                    //will fit width
                    imgItemScaled.width = elmBgWidth;
                    imgItemScaled.height = elmBgWidth / imgAR;
                } else {
                    imgItemScaled.height = elmBgHeight;
                    imgItemScaled.width = elmBgHeight * imgAR;
                }
                imgItemScaled.scalex = imgItemScaled.scaley = imgItemScaled.width / imgItem.width;
                return imgItemScaled;
            case 'auto, auto':
            case 'auto':
                //the image is original size, we only need to crop it to match what's displayed and to check the offsets
                return imgItemScaled;
            default:
                //sizes are specified numerically, 200%; 100px 30%; etc.
                var bgSizes = bgSize.split(' ');
                var hasWidth = false;
                if(bgSizes[0] !== 'auto') {
                    imgItemScaled.width = sizeToPx(index, bgSizes[0], elm, false, 'x');
                    imgItemScaled.scalex = imgItemScaled.width / imgItem.width;
                    hasWidth = true;
                }

                if(typeof bgSizes[1] !== 'undefined') {
                    imgItemScaled.height = sizeToPx(index, bgSizes[1], elm, false, 'y');
                    if(!hasWidth) {
                        imgItemScaled.width = imgItem.width * imgItemScaled.height / imgItem.height;
                        imgItemScaled.scalex = imgItemScaled.width / imgItem.width;
                    }
                }
                else {
                    imgItemScaled.height = imgItem.height * imgItemScaled.width / imgItem.width;
                }
                imgItemScaled.scaley = imgItemScaled.height / imgItem.height;
                return imgItemScaled;
        }
    }

    function sizeToPx(index, size, elm, core, axis) {
        var ps = parseSize(size),
            sizeVal = ps.val,
            sizeType = ps.type,
            coreSize = (core === false ? 0 : core[axisName(axis)]);

        //relative
        if (sizeType == '%') {
            sizeVal = (elmAxisSize(index, elm, style, axis) - coreSize) * sizeVal / 100;
            if(coreSize > 0) {
                sizeVal %= coreSize;
            }
            return sizeVal;
        }  else if(sizeType == 'em') {
            return sizeVal * sizeToPx(index, style.fontSize, elm.parentElement, false, axis);
        } else if (sizeType == 'rem') {
            return sizeVal * parseFloat(getComputedStyle(document.documentElement).fontSize); //it's always px
        } else if (sizeType == 'vw') {
            return sizeVal * window.innerWidth / 100;
        } else if (sizeType == 'vh') {
            return sizeVal * window.innerHeight / 100;
        } else if (sizeType == 'vmin') {
            return sizeVal * Math.min(window.innerWidth, window.innerHeight) / 100;
        } else if (sizeType == 'vmax') {
            return sizeVal * Math.max(window.innerWidth, window.innerHeight) / 100;
        }
        //absolute
        else if (sizeType == 'px') {
            return sizeVal;
        } else if (sizeType == 'in') {
            return sizeVal * 96;
        } else if (sizeType == 'pt') {
            return sizeVal * 4 / 3;
        } else if (sizeType == 'pc') {
            return sizeVal * 16;
        } else if (sizeType == 'mm') {
            return sizeVal * 3.7795275591;
        } else if (sizeType == 'cm') {
            return sizeVal * 37.795275591;
        }
        else {
            throw "Unsupported unit";
        }
    }

    function elmAxisSize(index, elm, style, axis) {
        var origin = idxVal(style.backgroundOrigin, index);
        return elm[(origin == 'border-box' ? 'offset' : 'client') + axisNameCap(axis)]
            //- (style.origin == 'content-box'
            //    ? sizeToPx(index, style['border' + padName(axis) + '-width'], elm, false, axis) + sizeToPx(index, style['border' + padOther(axis) + '-width'], elm, false, axis) : 0)
            - (origin == 'content-box'
                ? sizeToPx(index, style['padding' + padName(axis)], elm, false, axis) + sizeToPx(index, style['padding' + padOther(axis)], elm, false, axis) : 0);
    }

    function elmAxisOriginVal (index, elm, style, axis) {
        var origin = idxVal(style.backgroundOrigin, index);
        return ((origin == 'padding-box' || origin == 'content-box') ? sizeToPx(index, style['border' + padName(axis) + '-width'], elm, false, axis) : 0)
            + (origin == 'content-box' ? sizeToPx(index, style['padding' + padName(axis)], elm, false, axis) : 0);
    }

    function axisName(axis) {return (axis == 'x' ? 'width' : 'height')}

    function axisNameCap(axis){return (axis == 'x' ? 'Width' : 'Height')}

    function padName(axis){return (axis == 'x' ? '-left' : '-top')}

    function padOther(axis){return (axis == 'x' ? '-right' : '-bottom')}

    function idxVal(list, index) {
        if(typeof list === 'undefined') return list;
        list = list.split(',');
        return list[Math.min(index, list.length - 1)].trim();
    }

    function idxReplace(list, index, val) {
        list = list.split(',');
        var index = Math.min(index, list.length);
        list[index] = val;
        return list.join(', ');
    }

    function isRepeat(style, index, axis) {
        return (idxVal(style.backgroundRepeat, index) == 'repeat' || idxVal(style['background-repeat-' + axis], index) == 'repeat')
    }

    function normalizeOffsets(index, imgItem, vPort) {
        var offsetx = imgItem.offsetx,
            offsety = imgItem.offsety;
        imgItem.isRepeatX = isRepeat(style, index, 'x');
        imgItem.isRepeatY = isRepeat(style, index, 'y');

        if(imgItem.isRepeatX) offsetx %= imgItem.width;
        if(imgItem.isRepeatY) offsety %= imgItem.height;

        if(offsetx >= vPort.width) {
            if(imgItem.isRepeatX) offsetx -= imgItem.width;
            else {//image is off the element, invisible
                imgItem.width = imgItem.height = false;
            }
        }
        if(offsety >= vPort.height) {
            if(imgItem.isRepeatY) offsety -= imgItem.height;
            else {//image is off the element, invisible
                imgItem.width = imgItem.height = false;
            }
        }
        imgItem.offsetx = offsetx;
        imgItem.offsety = offsety;
        return imgItem;
    }

    function intersect(index, img, vPort) {
        var x = Math.max(img.offsetx, vPort.x),
            y = Math.max(img.offsety, vPort.y),
            width = Math.min(img.width + img.offsetx, vPort.width + vPort.x) - x,
            height = Math.min(img.height + img.offsety, vPort.height + vPort.y) - y,
            alterCss = {};

        if( img.isRepeatX && (img.offsetx > 0 || img.offsetx + img.width < vPort.width) ) {
            width = img.width;
        }
        //if it's a percent leave it as it is
        else if(idxVal(style.backgroundPositionX, index).match(/^[0-9]+%$/) === null && img.offsetx < vPort.x) {
            //if it's a percent leave it as it is
            alterCss['background-position-x'] = idxReplace(style.backgroundPositionX, index, vPort.x > 0 ? vPort.x + 'px' : '0%');
        }
        if( img.isRepeatY && (img.offsety > 0 || img.offsety + img.height < vPort.height) ) {
            height = img.height;
        } else if(idxVal(style.backgroundPositionY, index).match(/^[0-9]+%$/) === null && img.offsety < vPort.y) {
            alterCss['background-position-y'] = idxReplace(style.backgroundPositionY, index, vPort.y > 0 ? vPort.y + 'px' : '0%');
        }
        var bkSize =idxVal(style.backgroundSize, index);
        if(bkSize !== 'auto' && bkSize !== '100%' && bkSize !== 'cover' && bkSize !== 'contain') {
            alterCss['background-size'] = idxReplace(style.backgroundSize, index, 'auto');
        }
        return {x: x, y: y, width: width, height: height, alterCss: alterCss};
    }

    function getTranslation(index, elm, imgItem, imgItemScaled, style) {
        var bgSize = idxVal(style.backgroundSize, index); //auto, contain, cover, 200%, 300px 500px

        imgItemScaled.offsetx = imgItemScaled.offsety = 0;
        if(bgSize === 'contain') {
            return imgItemScaled; //in this case we don't care about offsets
        }

        imgItemScaled.offsetx = bgCalcOffset(index, elm, imgItemScaled, style , 'x');
        imgItemScaled.offsety = bgCalcOffset(index, elm, imgItemScaled, style, 'y');

        return imgItemScaled;
    }

    function getViewport(index, elm, imgItem, imgItemScaled, style) {
        if(idxVal(style.backgroundAttachment, index) == 'fixed') {
            //doesn't scroll so we provide full width and enough height
            return {x: 0, y: 0, width: screen.width, height: 99999};
        } else {
            var clip = idxVal(style.backgroundClip, index);
            var pl = sizeToPx(index, style.paddingLeft, elm, false, 'w'),
                pt = sizeToPx(index, style.paddingTop, elm, false, 'h');
            return {
                x: (clip == 'border-box' ? 0 : sizeToPx(index, style.borderLeftWidth, elm, false, 'w') + (clip == 'content-box' ? pl : 0)),
                y: (clip == 'border-box' ? 0 : sizeToPx(index, style.borderTopWidth, elm, false, 'w') + (clip == 'content-box' ? pt : 0)),
                width:  (clip == 'border-box' ? elm.offsetWidth : elm.clientWidth
                    - (clip == 'content-box' ? pl + sizeToPx(index, style.paddingRight, elm, false, 'w') : 0)),
                height: (clip == 'border-box' ? elm.offsetHeight : elm.clientHeight
                    - (clip == 'content-box' ? pt + sizeToPx(index, style.paddingBottom, elm, false, 'h') : 0))
            };

        }
    }

    function bgCalcOffset(index, elm, core, style, axis) {
        var bgPos = idxVal(style['background-position-' + axis], index);
        return elmAxisOriginVal(index, elm, style,  axis) + (parseInt(bgPos) !== 0 ? sizeToPx(index, bgPos, elm, core, axis) : 0);
    }

    function parseSize(size) {
        var sizeVal = parseFloat(size),
            sizeType = size.substr(('' + sizeVal).length);
        return {val: sizeVal, type: sizeType}
    }
}

/**
 * if data is % then use the width to calculate its equivalent in px
 * @param data - the CSS string (200px, 30%)
 * @param width - the element width
 * @returns px equivalent of data
 */
SPAI.prototype.percent2px = function(data, width){
    return (data.slice(-1) == '%' ? width * parseFloat(data) / 100 : parseFloat(data))
};

//this is reverse engineered from jQuery.fancybox...
SPAI.prototype.fancyboxUpdateWidth = function(elm) {
    //TODO de ce se afiseaza imaginile mai mici?
    //debugger;
    var fancyParams = jQuery.extend({}, jQuery.fn.fancybox.defaults, typeof elm.data("fancybox") == "undefined" ? {} : elm.data("fancybox"));
    var viewport = [jQuery(window).width() - fancyParams.margin * 2, jQuery(window).height() - fancyParams.margin * 2, jQuery(document).scrollLeft() + fancyParams.margin, jQuery(document).scrollTop() + fancyParams.margin];
    var k = fancyParams.padding * 2;

    var maxWidth = viewport[0] - k;
    var maxHeight = viewport[1] - k;
    var aspectRatio = fancyParams.width / fancyParams.height;
    var screenRatio = maxWidth / maxHeight;

    var width = 0;
    var height = 0;
    if(aspectRatio > screenRatio) {
        width = maxWidth;
    } else {
        height = maxHeight;
        width = Math.round(maxHeight * aspectRatio);
    }

    /*		var width = fancyParams.width.toString().indexOf("%") > -1 ? parseInt(viewport[0] * parseFloat(fancyParams.width) / 100, 10)
                    : maxWidth;
        var height = fancyParams.height.toString().indexOf("%") > -1 ? parseInt(a[1] * parseFloat(fancyParams.height) / 100, 10)
            : maxHeight;

        if (fancyParams.autoScale && (width > viewport[0] || height > viewport[1])) {
            if (width > viewport[0]) {
                width = viewport[0];
            }
            if (height > viewport[1]) {
                width = parseInt((viewport[1] - k) * g + k, 10)
            }
        }
    */
    //use rounded widths, what is below 700 rounds up to multiples of 50, what is above to multiples of 100
    width = width < 700 ? Math.floor((width + 49) / 50) * 50 : Math.floor((width + 99) / 100) * 100;
    var href = elm.attr('href');
    if( href.indexOf('w_DEFER') > 0) {
        var newHref = href.replace('w_DEFER', 'w_' + width);
        //console.log('replace DEFER: ' + newHref);
        elm.attr('href', newHref);
    }
    else {
        var matches = href.match(/\/w_([0-9]+),._/g);
        if (matches !== null && matches[2] < width) {
            var newHref = href.replace(/\/w_[0-9]+,/, '/w_' + width + ',');
            //console.log('replace ' + href + ' with ' + newHref);
            elm.attr('href', newHref);
        } else {
            return;
        }
    }
    ShortPixelAI.fancyboxId = elm.parent().parent().attr('id');
};

SPAI.prototype.xpath = function(el) {
    if (typeof el == "string") return document.evaluate(el, document, null, 0, null);
    if (!el || el.nodeType != 1) return '';
    if (el.id) return "//*[@id='" + el.id + "']";
    var sames = [];
    try {
        sames = (el.parentNode === null || typeof el.parentNode.children === 'undefined' ? [] : [].filter.call(el.parentNode.children, function (x) { return x.tagName == el.tagName }))
    } catch(err) {
        //console.log(err.message);
    }
    return (el.parentNode === null ? '' : ShortPixelAI.xpath(el.parentNode) + '/') + el.tagName.toLowerCase() + (sames.length > 1 ? '['+([].indexOf.call(sames, el)+1)+']' : '')
};

/*SPAI.prototype.identifyImage = function() {
    document.getElementsByTagName("body")[0].style.cursor = "url('" + spai_settings.sniper + "'), auto";
}*/

SPAI.prototype.registerCallback = function(when, callback) {
    ShortPixelAI.callbacks[when] = callback;
}

SPAI.prototype.elementUpdated = function(elm, w) {
    elm.attr('data-spai-upd', Math.round(w));
    ShortPixelAI.updatedUrlsCount++;
    if(typeof ShortPixelAI.callbacks['element-updated'] !== 'undefined') {
        ShortPixelAI.callbacks['element-updated'](elm);
    }
}

SPAI.prototype.triggerEvent = function(name, elem) {
    const event = document.createEvent('Event');
    event.initEvent(name, true, true);
    spai_settings.debug && console.log("Event " + name + " triggered on " + elem.tagName)
    elem.dispatchEvent(event);
}

// document.documentElement.addEventListener('spai-body-handled', function(evt){console.log('HANDLING spai-body-handled on ' + evt.target.tagName)}, false);
// document.documentElement.addEventListener('spai-block-handled', function(evt){console.log('HANDLING spai-block-handled on ' + evt.target.tagName)}, false);
// document.documentElement.addEventListener('spai-element-handled', function(evt){console.log('HANDLING spai-element-handled on ' + evt.target.tagName)}, false);

/* //used only for debug
SPAI.prototype.getScroll = function() {
    if (window.pageYOffset != undefined) {
        return [pageXOffset, pageYOffset];
    } else {
        var sx, sy, d = document,
            r = d.documentElement,
            b = d.body;
        sx = r.scrollLeft || b.scrollLeft || 0;
        sy = r.scrollTop || b.scrollTop || 0;
        return [sx, sy];
    }
}
*/

//Polyfill for MSIE
if (!String.prototype.startsWith) {
    String.prototype.startsWith = function(searchString, position){
        position = position || 0;
        return this.substr(position, searchString.length) === searchString;
    };
}

var shortPixelAIonDOMLoadedTimeout = false;
var shortPixelAIonDOMLoadedCounter = 0;
window.ShortPixelAI = new SPAI();

function shortPixelAIonDOMLoaded() {
    if(ShortPixelAI.initialized) return;
    if(typeof spai_settings === "undefined") {
        if(shortPixelAIonDOMLoadedCounter > 50) {
            throw "The ShortPixel settings are missing. Do you use a plugin that defers the inline JS?";
        }
        clearTimeout(shortPixelAIonDOMLoadedTimeout);
        shortPixelAIonDOMLoadedTimeout = setTimeout(shortPixelAIonDOMLoaded, 10 + shortPixelAIonDOMLoadedCounter + (shortPixelAIonDOMLoadedCounter > 40 ? 100 : 0));
        shortPixelAIonDOMLoadedCounter++;
        return;
    }

    //the excluded_paths can contain URLs so they are base64 encoded in order to pass our own JS parser :)
    spai_settings.excluded_paths = spai_settings.excluded_paths.map(atob);

    if(spai_settings.native_lazy != '1') {
        //detect if it's a bot, in which case force native lazy loading
        var botPattern = "(googlebot\/|Googlebot-Mobile|Googlebot-Image|Google favicon|Mediapartners-Google|bingbot|slurp|java|wget|curl|Commons-HttpClient|Python-urllib|libwww|httpunit|nutch|phpcrawl|msnbot|jyxobot|FAST-WebCrawler|FAST Enterprise Crawler|biglotron|teoma|convera|seekbot|gigablast|exabot|ngbot|ia_archiver|GingerCrawler|webmon |httrack|webcrawler|grub.org|UsineNouvelleCrawler|antibot|netresearchserver|speedy|fluffy|bibnum.bnf|findlink|msrbot|panscient|yacybot|AISearchBot|IOI|ips-agent|tagoobot|MJ12bot|dotbot|woriobot|yanga|buzzbot|mlbot|yandexbot|purebot|Linguee Bot|Voyager|CyberPatrol|voilabot|baiduspider|citeseerxbot|spbot|twengabot|postrank|turnitinbot|scribdbot|page2rss|sitebot|linkdex|Adidxbot|blekkobot|ezooms|dotbot|Mail.RU_Bot|discobot|heritrix|findthatfile|europarchive.org|NerdByNature.Bot|sistrix crawler|ahrefsbot|Aboundex|domaincrawler|wbsearchbot|summify|ccbot|edisterbot|seznambot|ec2linkfinder|gslfbot|aihitbot|intelium_bot|facebookexternalhit|yeti|RetrevoPageAnalyzer|lb-spider|sogou|lssbot|careerbot|wotbox|wocbot|ichiro|DuckDuckBot|lssrocketcrawler|drupact|webcompanycrawler|acoonbot|openindexspider|gnam gnam spider|web-archive-net.com.bot|backlinkcrawler|coccoc|integromedb|content crawler spider|toplistbot|seokicks-robot|it2media-domain-crawler|ip-web-crawler.com|siteexplorer.info|elisabot|proximic|changedetection|blexbot|arabot|WeSEE:Search|niki-bot|CrystalSemanticsBot|rogerbot|360Spider|psbot|InterfaxScanBot|Lipperhey SEO Service|CC Metadata Scaper|g00g1e.net|GrapeshotCrawler|urlappendbot|brainobot|fr-crawler|binlar|SimpleCrawler|Livelapbot|Twitterbot|cXensebot|smtbot|bnf.fr_bot|A6-Indexer|ADmantX|Facebot|Twitterbot|OrangeBot|memorybot|AdvBot|MegaIndex|SemanticScholarBot|ltx71|nerdybot|xovibot|BUbiNG|Qwantify|archive.org_bot|Applebot|TweetmemeBot|crawler4j|findxbot|SemrushBot|yoozBot|lipperhey|y!j-asr|Domain Re-Animator Bot|AddThis)";
        var re = new RegExp(botPattern, 'i');
        var userAgent = navigator.userAgent;
        if (re.test(userAgent)) {
            spai_settings.native_lazy = '1';
        }
    } else if (!('loading' in document.createElement('img'))) {
        spai_settings.native_lazy = ''; // if the browser doesn't support native lazy loading, use the JS approach
    }

    if ( !!window.spai_settings.lqip && typeof LQIP === 'function' ) {
        ShortPixelAI.modules.lqip = new LQIP();
    }

    if ( !!window.spai_settings.lazy_threshold ) {
        var lazyThreshold = parseInt( spai_settings.lazy_threshold, 10 );

        ShortPixelAI.intersectionMargin = !isNaN( lazyThreshold ) && lazyThreshold >= 0 ? lazyThreshold : ShortPixelAI.intersectionMargin;
    }

    ShortPixelAI.initialized = true;

    //detect if the browser supports WebP/AVIF
    if (spai_settings.webp_detect && (spai_settings.webp == '1' || spai_settings.avif == '1') && self.createImageBitmap) {
        /**
         * Checks if WebP/AVIF is supported by the browser
         */
        var spaiHasNG = (function() {
            var images = {
                webp: "data:image/webp;base64,UklGRjIAAABXRUJQVlA4ICYAAACyAgCdASoCAAEALmk0mk0iIiIiIgBoSygABc6zbAAA/v56QAAAAA==",
                losslessWebp: "data:image/webp;base64,UklGRh4AAABXRUJQVlA4TBEAAAAvAQAAAAfQ//73v/+BiOh/AAA=",
                avif: 'data:image/avif;base64,AAAAIGZ0eXBhdmlmAAAAAGF2aWZtaWYxbWlhZk1BMUIAAADybWV0YQAAAAAAAAAoaGRscgAAAAAAAAAAcGljdAAAAAAAAAAAAAAAAGxpYmF2aWYAAAAADnBpdG0AAAAAAAEAAAAeaWxvYwAAAABEAAABAAEAAAABAAABGgAAAB0AAAAoaWluZgAAAAAAAQAAABppbmZlAgAAAAABAABhdjAxQ29sb3IAAAAAamlwcnAAAABLaXBjbwAAABRpc3BlAAAAAAAAAAIAAAACAAAAEHBpeGkAAAAAAwgICAAAAAxhdjFDgQ0MAAAAABNjb2xybmNseAACAAIAAYAAAAAXaXBtYQAAAAAAAAABAAEEAQKDBAAAACVtZGF0EgAKCBgANogQEAwgMg8f8D///8WfhwB8+ErK42A='
            };

            return function(feature) {
                function Deferred(){
                    this._done = [];
                    this._fail = [];
                }
                Deferred.prototype = {
                    execute: function(list, args){
                        var i = list.length;

                        // convert arguments to an array
                        // so they can be sent to the
                        // callbacks via the apply method
                        args = Array.prototype.slice.call(args);

                        while(i--) list[i].apply(null, args);
                    },
                    promise: function(){
                        return this;
                    },
                    resolve: function(){
                        this.execute(this._done, arguments);
                    },
                    reject: function(){
                        this.execute(this._fail, arguments);
                    },
                    then: function(doneFilter, failFilter) {
                        this._done.push(doneFilter);
                        this._fail.push(failFilter);
                    },
                    done: function(callback){
                        this._done.push(callback);
                        return this;
                    },
                    fail: function(callback){
                        this._fail.push(callback);
                        return this;
                    }
                }

                var deferred = new Deferred();

                var image = new Image();
                image.onload = function() {
                    if(this.width === 2 && this.height > 0) {
                        deferred.resolve();
                    } else {
                        deferred.reject();
                    }
                };
                image.onerror = function(){deferred.reject()};
                image.src = images[feature || "webp"];

                return deferred.promise();
            }
        })();

        //can also call hasWebP('lossless') to check if the newer lossless WebP is supported
        spaiHasNG('webp').then(
            function(){
                ShortPixelAI.conversionType = (spai_settings.webp == '1' ? 'to_webp' : '');
                if(spai_settings.avif == '1') {
                    spaiHasNG('avif').then(function(){
                        ShortPixelAI.conversionType = 'to_avif';
                        ShortPixelAI.init();
                    },function(){
                        ShortPixelAI.init();
                    });
                } else {
                    ShortPixelAI.init();
                }
            },
            function(){
                ShortPixelAI.init();
            });
    } else {
        if((spai_settings.webp == '1' || spai_settings.avif == '1') && self.createImageBitmap){
            //means there was no need to detect, using our CDN
            if(spai_settings.webp == '1' && spai_settings.avif == '1') {
                ShortPixelAI.conversionType = 'to_auto';
            } else {
                ShortPixelAI.conversionType = (spai_settings.webp == '1' ? 'to_webp' : 'to_avif');
            }
        }

        ShortPixelAI.init();
    }
}

//jQuery(document).ready(function () {
if(document.readyState === 'loading') {
    document.addEventListener("DOMContentLoaded", function() {
        shortPixelAIonDOMLoaded();
    });
} else {
    shortPixelAIonDOMLoaded();
}
