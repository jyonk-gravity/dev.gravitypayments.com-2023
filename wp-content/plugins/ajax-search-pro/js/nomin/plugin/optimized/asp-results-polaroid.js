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
  "default": function() { return /* binding */ asp_results_polaroid; }
});

;// CONCATENATED MODULE: external "AjaxSearchPro"
var external_AjaxSearchPro_namespaceObject = Object(window.WPD)["AjaxSearchPro"];
;// CONCATENATED MODULE: external "DoMini"
var external_DoMini_namespaceObject = Object(window.WPD)["DoMini"];
;// CONCATENATED MODULE: ./js/src/plugin/core/actions/results_polaroid.js



"use strict";
external_AjaxSearchPro_namespaceObject.plugin.showPolaroidResults = function() {
  let $this = this;
  this.loadASPFonts?.();
  $this.n("results").addClass("photostack");
  external_DoMini_namespaceObject(".photostack>nav", $this.n("resultsDiv")).remove();
  let figures = external_DoMini_namespaceObject("figure", $this.n("resultsDiv"));
  $this.showResultsBox();
  if (figures.length > 0) {
    $this.n("results").css({
      height: $this.o.prescontainerheight
    });
    if ($this.o.highlight) {
      external_DoMini_namespaceObject("figcaption", $this.n("resultsDiv")).highlight($this.n("text").val().split(" "), {
        element: "span",
        className: "highlighted",
        wordsOnly: $this.o.highlightWholewords
      });
    }
    if (typeof Photostack !== "undefined") {
      $this.ptstack = new Photostack($this.n("results").get(0), {
        callback: function(item) {
        }
      });
    } else {
      return false;
    }
  }
  if (figures.length === 0) {
    $this.n("results").css({
      height: "11110px"
    });
    $this.n("results").css({
      height: "auto"
    });
  }
  $this.addAnimation();
  $this.fixResultsPosition(true);
  $this.searching = false;
  $this.initPolaroidEvents(figures);
};
external_AjaxSearchPro_namespaceObject.plugin.initPolaroidEvents = function(figures) {
  let $this = this, i = 1, span = ".photostack>nav span";
  figures.forEach(function() {
    if (i > 1)
      external_DoMini_namespaceObject(this).removeClass("photostack-current");
    external_DoMini_namespaceObject(this).attr("idx", i);
    i++;
  });
  figures.on("click", function(e) {
    if (external_DoMini_namespaceObject(this).hasClass("photostack-current")) return;
    e.preventDefault();
    let idx = external_DoMini_namespaceObject(this).attr("idx");
    external_DoMini_namespaceObject(".photostack>nav span:nth-child(" + idx + ")", $this.n("resultsDiv")).trigger("click", [], true);
  });
  const left_handler = () => {
    if (external_DoMini_namespaceObject(span + ".current", $this.n("resultsDiv")).next().length > 0) {
      external_DoMini_namespaceObject(span + ".current", $this.n("resultsDiv")).next().trigger("click", [], true);
    } else {
      external_DoMini_namespaceObject(span + ":nth-child(1)", $this.n("resultsDiv")).trigger("click", [], true);
    }
  };
  const right_handler = () => {
    if (external_DoMini_namespaceObject(span + ".current", $this.n("resultsDiv")).prev().length > 0) {
      external_DoMini_namespaceObject(span + ".current", $this.n("resultsDiv")).prev().trigger("click", [], true);
    } else {
      external_DoMini_namespaceObject(span + ":nth-last-child(1)", $this.n("resultsDiv")).trigger("click", [], true);
    }
  };
  figures.on("mousewheel", function(e) {
    e.preventDefault();
    let delta = e.deltaY > 0 ? 1 : -1;
    if (delta >= 1) {
      left_handler();
    } else {
      right_handler();
    }
  });
  $this.n("resultsDiv").on("swiped-left", left_handler);
  $this.n("resultsDiv").on("swiped-right", right_handler);
};
/* harmony default export */ var results_polaroid = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/bundle/optimized/asp-results-polaroid.js



/* harmony default export */ var asp_results_polaroid = (external_AjaxSearchPro_namespaceObject);

Object(window.WPD).AjaxSearchPro = __webpack_exports__["default"];
/******/ })()
;