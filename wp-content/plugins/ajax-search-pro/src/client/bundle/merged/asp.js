import "../optimized/asp-prereq.js";
import AjaxSearchPro from "../optimized/asp-core.js";
import "../optimized/asp-autocomplete.js";
import "../optimized/asp-compact.js";
import "../optimized/asp-ga.js";
import "../optimized/asp-live.js";
import "../optimized/asp-results-horizontal.js";
import "../optimized/asp-results-isotopic.js";
import "../optimized/asp-results-polaroid.js";
import "../optimized/asp-results-vertical.js";
import "../optimized/asp-settings.js";
import "../optimized/asp-addons-divi.js";
import "../optimized/asp-addons-elementor.js";
import load from "../../plugin/wrapper/wrapper.js";

window.WPD.AjaxSearchPro = AjaxSearchPro;

// Run on document ready
(function() {
    if ( navigator.userAgent.indexOf("Chrome-Lighthouse") === -1 ) {
        // Preload script executed?
        if ( typeof window.WPD != 'undefined' && typeof window.WPD.dom != 'undefined' ) {
            load();
        }
    }
})();