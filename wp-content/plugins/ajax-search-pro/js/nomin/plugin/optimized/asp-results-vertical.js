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
  "default": function() { return /* binding */ asp_results_vertical; }
});

;// CONCATENATED MODULE: external "AjaxSearchPro"
var external_AjaxSearchPro_namespaceObject = Object(window.WPD)["AjaxSearchPro"];
;// CONCATENATED MODULE: external "DoMini"
var external_DoMini_namespaceObject = Object(window.WPD)["DoMini"];
;// CONCATENATED MODULE: ./js/src/plugin/core/actions/results_vertical.js



"use strict";
external_AjaxSearchPro_namespaceObject.plugin.showVerticalResults = function() {
  let $this = this;
  $this.showResultsBox();
  if ($this.n("items").length > 0) {
    let count = $this.n("items").length < $this.o.itemscount ? $this.n("items").length : $this.o.itemscount;
    count = count <= 0 ? 9999 : count;
    let groups = external_DoMini_namespaceObject(".asp_group_header", $this.n("resultsDiv"));
    if ($this.o.itemscount === 0 || $this.n("items").length <= $this.o.itemscount) {
      $this.n("results").css({
        height: "auto"
      });
    } else {
      if ($this.call_num < 1)
        $this.n("results").css({
          height: "30px"
        });
      if ($this.call_num < 1) {
        let i = 0, h = 0, final_h = 0, highest = 0;
        $this.n("items").forEach(function() {
          h += external_DoMini_namespaceObject(this).outerHeight(true);
          if (external_DoMini_namespaceObject(this).outerHeight(true) > highest)
            highest = external_DoMini_namespaceObject(this).outerHeight(true);
          i++;
        });
        final_h = highest * count;
        if (final_h > h)
          final_h = h;
        i = i < 1 ? 1 : i;
        h = h / i * count;
        if (groups.length > 0) {
          groups.forEach(function(el, index) {
            let position = Array.prototype.slice.call(el.parentNode.children).indexOf(el), group_position = position - index - Math.floor(position / 3);
            if (group_position < count) {
              final_h += external_DoMini_namespaceObject(this).outerHeight(true);
            }
          });
        }
        $this.n("results").css({
          height: final_h + "px"
        });
      }
    }
    $this.n("items").last().addClass("asp_last_item");
    $this.n("results").find(".asp_group_header").prev(".item").addClass("asp_last_item");
    if ($this.o.highlight) {
      external_DoMini_namespaceObject("div.item", $this.n("resultsDiv")).highlight($this.n("text").val().split(" "), {
        element: "span",
        className: "highlighted",
        wordsOnly: $this.o.highlightWholewords
      });
    }
  }
  $this.resize();
  if ($this.n("items").length === 0) {
    $this.n("results").css({
      height: "auto"
    });
  }
  if ($this.call_num < 1) {
    $this.n("results").get(0).scrollTop = 0;
  }
  if ($this.o.preventBodyScroll) {
    let t, $body = external_DoMini_namespaceObject("body"), bodyOverflow = $body.css("overflow"), bodyHadNoStyle = typeof $body.attr("style") === "undefined";
    $this.n("results").off("touchstart");
    $this.n("results").off("touchend");
    $this.n("results").on("touchstart", function() {
      clearTimeout(t);
      external_DoMini_namespaceObject("body").css("overflow", "hidden");
    }).on("touchend", function() {
      clearTimeout(t);
      t = setTimeout(function() {
        if (bodyHadNoStyle) {
          external_DoMini_namespaceObject("body").removeAttr("style");
        } else {
          external_DoMini_namespaceObject("body").css("overflow", bodyOverflow);
        }
      }, 300);
    });
  }
  $this.addAnimation();
  $this.fixResultsPosition(true);
  $this.searching = false;
};
/* harmony default export */ var results_vertical = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/bundle/optimized/asp-results-vertical.js



/* harmony default export */ var asp_results_vertical = (external_AjaxSearchPro_namespaceObject);

Object(window.WPD).AjaxSearchPro = __webpack_exports__["default"];
/******/ })()
;