import load from "../../plugin/wrapper/wrapper.js";

// Run on document ready
(function() {
    if ( navigator.userAgent.indexOf("Chrome-Lighthouse") === -1 ) {
        // Preload script executed?
        if ( typeof window.WPD != 'undefined' && typeof window.WPD.dom != 'undefined' ) {
            window.WPD.AjaxSearchPro._load = load;
            load();
        }
    }
})();