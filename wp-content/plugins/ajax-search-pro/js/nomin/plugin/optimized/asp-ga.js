/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	// The require scope
/******/ 	var __webpack_require__ = {};
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	!function() {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = function(exports, definition) {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};

// EXPORTS
__webpack_require__.d(__webpack_exports__, {
  "default": function() { return /* binding */ asp_ga; }
});

;// CONCATENATED MODULE: external "AjaxSearchPro"
var external_AjaxSearchPro_namespaceObject = Object(window.WPD)["AjaxSearchPro"];
;// CONCATENATED MODULE: external "DoMini"
var external_DoMini_namespaceObject = Object(window.WPD)["DoMini"];
;// CONCATENATED MODULE: ./js/src/plugin/core/actions/ga_events.js



"use strict";
external_AjaxSearchPro_namespaceObject.plugin.gaEvent = function(which, data) {
  let $this = this;
  let tracking_id = $this.gaGetTrackingID();
  if (typeof ASP.analytics == "undefined" || ASP.analytics.method !== "event")
    return false;
  let _gtag = typeof window.gtag === "function" ? window.gtag : false;
  if (_gtag === false && typeof window.dataLayer === "undefined")
    return false;
  if (typeof ASP.analytics.event[which] !== "undefined" && ASP.analytics.event[which].active) {
    let def_data = {
      "search_id": $this.o.id,
      "search_name": $this.n("search").data("name"),
      "phrase": $this.n("text").val(),
      "option_name": "",
      "option_value": "",
      "result_title": "",
      "result_url": "",
      "results_count": ""
    };
    let event = {
      "event_category": ASP.analytics.event[which].category,
      "event_label": ASP.analytics.event[which].label,
      "value": ASP.analytics.event[which].value
    };
    data = external_DoMini_namespaceObject.fn.extend(def_data, data);
    Object.keys(data).forEach(function(k) {
      let v = data[k];
      v = String(v).replace(/[\s\n\r]+/g, " ").trim();
      Object.keys(event).forEach(function(kk) {
        let regex = new RegExp("{" + k + "}", "gmi");
        event[kk] = event[kk].replace(regex, v);
      });
    });
    if (_gtag !== false) {
      if (tracking_id !== false) {
        tracking_id.forEach(function(id) {
          event.send_to = id;
          _gtag("event", ASP.analytics.event[which].action, event);
        });
      } else {
        _gtag("event", ASP.analytics.event[which].action, event);
      }
    } else if (typeof window.dataLayer.push != "undefined") {
      window.dataLayer.push({
        "event": "asp_event",
        "event_name": ASP.analytics.event[which].action,
        "event_category": event.event_category,
        "event_label": event.event_label,
        "event_value": event.value
      });
    }
  }
};
external_AjaxSearchPro_namespaceObject.plugin.gaGetTrackingID = function() {
  let ret = false;
  if (typeof ASP.analytics == "undefined")
    return ret;
  if (typeof ASP.analytics.tracking_id != "undefined" && ASP.analytics.tracking_id !== "") {
    return [ASP.analytics.tracking_id];
  } else {
    let _gtag = typeof window.gtag == "function" ? window.gtag : false;
    if (_gtag === false && typeof window.ga != "undefined" && typeof window.ga.getAll != "undefined") {
      let id = [];
      window.ga.getAll().forEach(function(tracker) {
        id.push(tracker.get("trackingId"));
      });
      return id.length > 0 ? id : false;
    }
  }
  return ret;
};
/* harmony default export */ var ga_events = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/bundle/optimized/asp-ga.js



/* harmony default export */ var asp_ga = (external_AjaxSearchPro_namespaceObject);

Object(window.WPD).AjaxSearchPro = __webpack_exports__["default"];
/******/ })()
;