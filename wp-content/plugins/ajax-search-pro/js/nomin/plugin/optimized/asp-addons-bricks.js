/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	// The require scope
/******/ 	var __webpack_require__ = {};
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	!function() {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = function(module) {
/******/ 			var getter = module && module.__esModule ?
/******/ 				function() { return module['default']; } :
/******/ 				function() { return module; };
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	}();
/******/ 	
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
  "default": function() { return /* binding */ asp_addons_bricks; }
});

;// external "AjaxSearchPro"
var external_AjaxSearchPro_namespaceObject = Object(window.WPD)["AjaxSearchPro"];
var external_AjaxSearchPro_default = /*#__PURE__*/__webpack_require__.n(external_AjaxSearchPro_namespaceObject);
;// ./src/client/addons/bricks.ts


const helpers = (external_AjaxSearchPro_default()).helpers;
class BricksAddon {
  name = "Elementor Widget Fixes";
  init() {
    const { Hooks } = helpers;
    Hooks.addFilter("asp/live_load/finished", this.fixImages.bind(this), 11, this);
  }
  fixImages(url, obj) {
    window?.bricksLazyLoad?.();
  }
}
external_AjaxSearchPro_default().addons.add(new BricksAddon());
/* harmony default export */ var bricks = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/bundle/optimized/asp-addons-bricks.js



/* harmony default export */ var asp_addons_bricks = (external_AjaxSearchPro_namespaceObject);

Object(window.WPD).AjaxSearchPro = __webpack_exports__["default"];
/******/ })()
;