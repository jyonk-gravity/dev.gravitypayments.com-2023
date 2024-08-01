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
  "default": function() { return /* binding */ asp_addons_divi; }
});

;// CONCATENATED MODULE: external "AjaxSearchPro"
var external_AjaxSearchPro_namespaceObject = Object(window.WPD)["AjaxSearchPro"];
;// CONCATENATED MODULE: external "DoMini"
var external_DoMini_namespaceObject = Object(window.WPD)["DoMini"];
;// CONCATENATED MODULE: ./js/src/addons/divi.js



const helpers = external_AjaxSearchPro_namespaceObject.helpers;
class DiviAddon {
  name = "Divi Widget Fixes";
  init() {
    helpers.Hooks.addFilter("asp/init/etc", this.diviBodyCommerceResultsPage, 10, this);
  }
  diviBodyCommerceResultsPage($this) {
    if ($this.o.divi.bodycommerce && $this.o.is_results_page) {
      window.WPD.intervalUntilExecute(function($2) {
        setTimeout(function() {
          $2("#divi_filter_button").trigger("click");
        }, 50);
      }, function() {
        return typeof jQuery !== "undefined" ? jQuery : false;
      });
    }
    return $this;
  }
}
external_AjaxSearchPro_namespaceObject.addons.add(new DiviAddon());
/* harmony default export */ var divi = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/bundle/optimized/asp-addons-divi.js



/* harmony default export */ var asp_addons_divi = (external_AjaxSearchPro_namespaceObject);

Object(window.WPD).AjaxSearchPro = __webpack_exports__["default"];
/******/ })()
;