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
  "default": function() { return /* binding */ asp_results_horizontal; }
});

;// external "AjaxSearchPro"
var external_AjaxSearchPro_namespaceObject = Object(window.WPD)["AjaxSearchPro"];
;// external "DoMini"
var external_DoMini_namespaceObject = Object(window.WPD)["DoMini"];
;// ./src/client/plugin/core/actions/results_horizontal.js



"use strict";
let helpers = external_AjaxSearchPro_namespaceObject.helpers;
external_AjaxSearchPro_namespaceObject.plugin.showHorizontalResults = function() {
  let $this = this;
  $this.showResultsBox();
  $this.n("items").css("opacity", $this.animationOpacity);
  if ($this.o.resultsposition === "hover") {
    $this.n("resultsDiv").css(
      "width",
      //($this.n('search').width() - ($this.n('resultsDiv').outerWidth(true) - $this.n('resultsDiv').innerWidth())) + 'px'
      $this.n("search").width() - ($this.n("resultsDiv").outerWidth(true) - $this.n("resultsDiv").width()) + "px"
    );
  }
  if ($this.n("items").length > 0 && $this.o.scrollBar.horizontal.enabled) {
    let el_m = parseInt($this.n("items").css("marginLeft")), el_w = $this.n("items").outerWidth() + el_m * 2;
    $this.n("resdrg").css("width", $this.n("items").length * el_w + el_m * 2 + "px");
  } else {
    $this.n("results").css("overflowX", "hidden");
    $this.n("resdrg").css("width", "auto");
  }
  $this.keywordHighlight();
  if ($this.call_num < 1) {
    let $container = $this.n("results");
    $container.get(0).scrollLeft = 0;
    if ($this.o.scrollBar.horizontal.enabled) {
      $container.off("wheel");
      let scrollLeft = 0;
      let wheelTimeout;
      let wheelJustStarted = true;
      $container.on("wheel", function(e) {
        if (Math.abs(e.deltaX) > Math.abs(e.deltaY)) {
          scrollLeft = this.scrollLeft;
          return;
        }
        if (wheelJustStarted) {
          scrollLeft = this.scrollLeft;
        }
        let deltaY = parseInt(e.deltaY ?? 0);
        let tolerance = Math.abs(deltaY);
        if (wheelJustStarted && tolerance > 10) {
          $container.css("scrollBehavior", "smooth");
        }
        scrollLeft += deltaY;
        scrollLeft = deltaY < 0 && scrollLeft > this.scrollLeft + tolerance ? this.scrollLeft + tolerance : scrollLeft;
        scrollLeft = scrollLeft < 0 ? 0 : scrollLeft;
        this.scrollLeft = scrollLeft;
        wheelJustStarted = false;
        if (!(helpers.isScrolledToRight($container.get(0)) && e.deltaY > 0 || helpers.isScrolledToLeft($container.get(0)) && e.deltaY <= 0)) {
          e.preventDefault();
        }
        clearTimeout(wheelTimeout);
        wheelTimeout = setTimeout(() => {
          wheelJustStarted = true;
        }, 200);
      });
    }
  }
  $this.showResultsBox();
  $this.addAnimation();
  $this.searching = false;
};
/* harmony default export */ var results_horizontal = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/bundle/optimized/asp-results-horizontal.js



/* harmony default export */ var asp_results_horizontal = (external_AjaxSearchPro_namespaceObject);

Object(window.WPD).AjaxSearchPro = __webpack_exports__["default"];
/******/ })()
;