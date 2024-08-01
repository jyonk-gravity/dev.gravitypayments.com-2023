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

;// CONCATENATED MODULE: external "AjaxSearchPro"
var external_AjaxSearchPro_namespaceObject = Object(window.WPD)["AjaxSearchPro"];
;// CONCATENATED MODULE: external "DoMini"
var external_DoMini_namespaceObject = Object(window.WPD)["DoMini"];
;// CONCATENATED MODULE: ./js/src/plugin/core/actions/results_horizontal.js



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
  if ($this.o.highlight) {
    external_DoMini_namespaceObject("div.item", $this.n("resultsDiv")).highlight(
      $this.n("text").val().split(" "),
      { element: "span", className: "highlighted", wordsOnly: !!$this.o.highlightWholewords }
    );
  }
  if ($this.call_num < 1) {
    let $container = $this.n("results");
    $container.get(0).scrollLeft = 0;
    if ($this.o.scrollBar.horizontal.enabled) {
      let prevDelta = 0, prevTime = Date.now();
      $container.off("mousewheel");
      $container.on("mousewheel", function(e) {
        let deltaFactor = typeof e.deltaFactor != "undefined" ? e.deltaFactor : 65, delta = e.deltaY > 0 ? 1 : -1, diff = Date.now() - prevTime, speed = diff > 100 ? 1 : 3 - 2 * diff / 100;
        if (prevDelta !== e.deltaY)
          speed = 1;
        external_DoMini_namespaceObject(this).animate(false).animate({
          "scrollLeft": this.scrollLeft + delta * deltaFactor * 2 * speed
        }, 250, "easeOutQuad");
        prevDelta = e.deltaY;
        prevTime = Date.now();
        if (!(helpers.isScrolledToRight($container.get(0)) && delta === 1 || helpers.isScrolledToLeft($container.get(0)) && delta === -1))
          e.preventDefault();
      });
    }
  }
  $this.showResultsBox();
  $this.addAnimation();
  $this.searching = false;
};
/* harmony default export */ var results_horizontal = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/bundle/optimized/asp-results-horizontal.js



/* harmony default export */ var asp_results_horizontal = (external_AjaxSearchPro_namespaceObject);

Object(window.WPD).AjaxSearchPro = __webpack_exports__["default"];
/******/ })()
;