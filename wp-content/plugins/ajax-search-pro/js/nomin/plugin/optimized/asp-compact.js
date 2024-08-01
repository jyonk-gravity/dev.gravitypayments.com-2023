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
  "default": function() { return /* binding */ asp_compact; }
});

;// CONCATENATED MODULE: external "AjaxSearchPro"
var external_AjaxSearchPro_namespaceObject = Object(window.WPD)["AjaxSearchPro"];
;// CONCATENATED MODULE: external "DoMini"
var external_DoMini_namespaceObject = Object(window.WPD)["DoMini"];
;// CONCATENATED MODULE: ./js/src/plugin/core/init/compact.js



"use strict";
external_AjaxSearchPro_namespaceObject.plugin.initCompact = function() {
  let $this = this;
  if ($this.o.compact.enabled && $this.o.compact.position !== "fixed") {
    $this.o.compact.overlay = 0;
  }
  if ($this.o.compact.enabled) {
    $this.n("trythis").css({
      display: "none"
    });
  }
  if ($this.o.compact.enabled && $this.o.compact.position === "fixed") {
    window.WPD.intervalUntilExecute(function() {
      let $body = external_DoMini_namespaceObject("body");
      $this.nodes["container"] = $this.n("search").closest(".asp_w_container");
      $body.append($this.n("search").detach());
      $body.append($this.n("trythis").detach());
      $this.n("search").css({
        top: $this.n("search").position().top + "px"
      });
    }, function() {
      return $this.n("search").css("position") === "fixed";
    });
  }
};
/* harmony default export */ var compact = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/actions/compact.js



"use strict";
let helpers = external_AjaxSearchPro_namespaceObject.helpers;
external_AjaxSearchPro_namespaceObject.plugin.openCompact = function() {
  let $this = this;
  if (!$this.n("search").is("[data-asp-compact-w]")) {
    $this.n("probox").attr("data-asp-compact-w", $this.n("probox").innerWidth());
    $this.n("search").attr("data-asp-compact-w", $this.n("search").innerWidth());
  }
  $this.n("search").css({
    "width": $this.n("search").width() + "px"
  });
  $this.n("probox").css({ width: "auto" });
  setTimeout(function() {
    $this.n("search").find(".probox>div:not(.promagnifier)").removeClass("hiddend");
  }, 80);
  clearTimeout($this.timeouts.compactBeforeOpen);
  $this.timeouts.compactBeforeOpen = setTimeout(function() {
    let width;
    if (helpers.deviceType() === "phone") {
      width = $this.o.compact.width_phone;
    } else if (helpers.deviceType() === "tablet") {
      width = $this.o.compact.width_tablet;
    } else {
      width = $this.o.compact.width;
    }
    width = helpers.Hooks.applyFilters("asp_compact_width", width, $this.o.id, $this.o.iid);
    width = !isNaN(width) ? width + "px" : width;
    if ($this.o.compact.position !== "static") {
      $this.n("search").css({
        "max-width": width,
        "width": width
      });
    } else {
      $this.n("container").css({
        "max-width": width,
        "width": width
      });
      $this.n("search").css({
        "max-width": "100%",
        "width": "100%"
      });
    }
    if ($this.o.compact.overlay) {
      $this.n("search").css("z-index", 999999);
      $this.n("searchsettings").css("z-index", 999999);
      $this.n("resultsDiv").css("z-index", 999999);
      $this.n("trythis").css("z-index", 999998);
      external_DoMini_namespaceObject("#asp_absolute_overlay").css({
        "opacity": 1,
        "width": "100%",
        "height": "100%",
        "z-index": 999990
      });
    }
    $this.n("search").attr("data-asp-compact", "open");
  }, 50);
  clearTimeout($this.timeouts.compactAfterOpen);
  $this.timeouts.compactAfterOpen = setTimeout(function() {
    $this.resize();
    $this.n("trythis").css({
      display: "block"
    });
    if ($this.o.compact.enabled && $this.o.compact.position !== "static") {
      $this.n("trythis").css({
        top: $this.n("search").offset().top + $this.n("search").outerHeight(true) + "px",
        left: $this.n("search").offset().left + "px"
      });
    }
    if ($this.o.compact.focus) {
      $this.n("text").get(0).focus();
    }
    $this.n("text").trigger("focus");
    $this.scrolling();
  }, 500);
};
external_AjaxSearchPro_namespaceObject.plugin.closeCompact = function() {
  let $this = this;
  clearTimeout($this.timeouts.compactBeforeOpen);
  clearTimeout($this.timeouts.compactAfterOpen);
  $this.timeouts.compactBeforeOpen = setTimeout(function() {
    $this.n("search").attr("data-asp-compact", "closed");
  }, 50);
  $this.n("search").find(".probox>div:not(.promagnifier)").addClass("hiddend");
  if ($this.o.compact.position !== "static") {
    $this.n("search").css({ width: "auto" });
  } else {
    $this.n("container").css({ width: "auto" });
    $this.n("search").css({
      "max-width": "unset",
      "width": "auto"
    });
  }
  $this.n("probox").css({ width: $this.n("probox").attr("data-asp-compact-w") + "px" });
  $this.n("trythis").css({
    left: $this.n("search").position().left,
    display: "none"
  });
  if ($this.o.compact.overlay) {
    $this.n("search").css("z-index", "");
    $this.n("searchsettings").css("z-index", "");
    $this.n("resultsDiv").css("z-index", "");
    $this.n("trythis").css("z-index", "");
    external_DoMini_namespaceObject("#asp_absolute_overlay").css({
      "opacity": 0,
      "width": 0,
      "height": 0,
      "z-index": 0
    });
  }
};
/* harmony default export */ var actions_compact = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/events/compact.js



"use strict";
external_AjaxSearchPro_namespaceObject.plugin.initCompactEvents = function() {
  let $this = this, scrollTopx = 0;
  $this.n("promagnifier").on("click", function() {
    let compact = $this.n("search").attr("data-asp-compact") || "closed";
    scrollTopx = window.scrollY;
    $this.hideSettings?.();
    $this.hideResults();
    if (compact === "closed") {
      $this.openCompact();
      $this.n("text").trigger("focus");
    } else {
      if (!$this.o.compact.closeOnMagnifier) return;
      $this.closeCompact();
      $this.searchAbort();
      $this.n("proloading").css("display", "none");
    }
  });
};
/* harmony default export */ var events_compact = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/bundle/optimized/asp-compact.js





/* harmony default export */ var asp_compact = (external_AjaxSearchPro_namespaceObject);

Object(window.WPD).AjaxSearchPro = __webpack_exports__["default"];
/******/ })()
;