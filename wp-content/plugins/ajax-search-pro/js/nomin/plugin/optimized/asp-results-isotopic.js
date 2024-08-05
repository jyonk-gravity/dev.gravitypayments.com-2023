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
  "default": function() { return /* binding */ asp_results_isotopic; }
});

;// CONCATENATED MODULE: external "AjaxSearchPro"
var external_AjaxSearchPro_namespaceObject = Object(window.WPD)["AjaxSearchPro"];
;// CONCATENATED MODULE: external "DoMini"
var external_DoMini_namespaceObject = Object(window.WPD)["DoMini"];
;// CONCATENATED MODULE: ./js/src/plugin/core/events/isotopic.js



"use strict";
let helpers = external_AjaxSearchPro_namespaceObject.helpers;
external_AjaxSearchPro_namespaceObject.plugin.initIsotopicPagination = function() {
  let $this = this;
  $this.n("resultsDiv").on($this.clickTouchend + " click_trigger", "nav>a", function(e) {
    e.preventDefault();
    e.stopImmediatePropagation();
    let $li = external_DoMini_namespaceObject(this).closest("nav").find("li.asp_active");
    let direction = external_DoMini_namespaceObject(this).hasClass("asp_prev") ? "prev" : "next";
    if (direction === "next") {
      if ($li.next("li").length > 0) {
        $li.next("li").trigger("click");
      } else {
        external_DoMini_namespaceObject(this).closest("nav").find("li").first().trigger("click");
      }
    } else {
      if ($li.prev("li").length > 0) {
        $li.prev("li").trigger("click");
      } else {
        external_DoMini_namespaceObject(this).closest("nav").find("li").last().trigger("click");
      }
    }
  });
  $this.n("resultsDiv").on($this.clickTouchend + " click_trigger", "nav>ul li", function(e) {
    e.preventDefault();
    e.stopImmediatePropagation();
    let _this = this, timeout = 1;
    if (helpers.isMobile()) {
      $this.n("text").trigger("blur");
      timeout = 300;
    }
    setTimeout(function() {
      $this.currentPage = parseInt(external_DoMini_namespaceObject(_this).find("span").html(), 10);
      external_DoMini_namespaceObject("nav>ul li", $this.n("resultsDiv")).removeClass("asp_active");
      external_DoMini_namespaceObject("nav", $this.n("resultsDiv")).forEach(function(el) {
        external_DoMini_namespaceObject(external_DoMini_namespaceObject(el).find("ul li").get($this.currentPage - 1)).addClass("asp_active");
      });
      if (e.type === "click_trigger") {
        $this.isotopic.arrange({
          transitionDuration: 0,
          filter: $this.filterFns["number"]
        });
      } else {
        $this.isotopic.arrange({
          transitionDuration: 400,
          filter: $this.filterFns["number"]
        });
      }
      $this.isotopicPagerScroll();
      $this.removeAnimation();
      $this.n("resultsDiv").trigger("nav_switch");
    }, timeout);
  });
};
/* harmony default export */ var isotopic = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/actions/results_isotopic.js



"use strict";
let results_isotopic_helpers = external_AjaxSearchPro_namespaceObject.helpers;
external_AjaxSearchPro_namespaceObject.plugin.showIsotopicResults = function() {
  let $this = this;
  if ($this._no_animations) {
    $this.showResultsBox();
    $this.addAnimation();
    $this.searching = false;
    return true;
  }
  $this.preProcessIsotopicResults();
  $this.showResultsBox();
  if ($this.n("items").length > 0) {
    $this.n("results").css({
      height: "auto"
    });
    if ($this.o.highlight) {
      external_DoMini_namespaceObject("div.item", $this.n("resultsDiv")).highlight($this.n("text").val().split(" "), {
        element: "span",
        className: "highlighted",
        wordsOnly: $this.o.highlightWholewords
      });
    }
  }
  if ($this.call_num === 0)
    $this.calculateIsotopeRows();
  $this.showPagination();
  $this.isotopicPagerScroll();
  if ($this.n("items").length === 0) {
    $this.n("results").css({
      height: "11110px"
    });
    $this.n("results").css({
      height: "auto"
    });
    $this.n("resdrg").css({
      height: "auto"
    });
  } else {
    if (typeof rpp_isotope !== "undefined") {
      if ($this.isotopic != null && typeof $this.isotopic.destroy != "undefined" && $this.call_num === 0)
        $this.isotopic.destroy();
      if ($this.call_num === 0 || $this.isotopic == null) {
        let selector = "#ajaxsearchprores" + $this.o.rid + " .resdrg";
        if (external_DoMini_namespaceObject(selector).length === 0) {
          selector = "div[id^=ajaxsearchprores" + $this.o.id + "] .resdrg";
        }
        $this.isotopic = new rpp_isotope(selector, {
          // options
          isOriginLeft: !external_DoMini_namespaceObject("body").hasClass("rtl"),
          itemSelector: "div.item",
          layoutMode: "masonry",
          filter: $this.filterFns["number"],
          masonry: {
            "gutter": $this.o.isotopic.gutter
          }
        });
      }
    } else {
      return false;
    }
  }
  $this.addAnimation();
  $this.initIsotopicClick();
  $this.searching = false;
};
external_AjaxSearchPro_namespaceObject.plugin.initIsotopicClick = function() {
  let $this = this;
  $this.eh.isotopicClickhandle = $this.eh.isotopicClickhandle || function(e) {
    if (!$this.dragging) {
      let $a = external_DoMini_namespaceObject(this).find(".asp_content a.asp_res_url");
      let url = $a.attr("href");
      if (url !== "") {
        e.preventDefault();
        if (e.which === 2 || $a.attr("target") === "_blank") {
          results_isotopic_helpers.openInNewTab(url);
        } else {
          location.href = url;
        }
      }
    }
  };
  $this.n("resultsDiv").find(".asp_isotopic_item").on("click", $this.eh.isotopicClickhandle);
};
external_AjaxSearchPro_namespaceObject.plugin.preProcessIsotopicResults = function() {
  let $this = this, j = 0, overlay = "";
  if ($this.o.isotopic.showOverlay && $this.n("aspItemOverlay").length > 0)
    overlay = $this.n("aspItemOverlay").get(0).outerHTML;
  $this.n("items").forEach(function(el) {
    let image = "", overlayImage = "", hasImage = external_DoMini_namespaceObject(el).find(".asp_image").length > 0, $img = external_DoMini_namespaceObject(el).find(".asp_image");
    if (hasImage) {
      let src = $img.data("src"), filter = $this.o.isotopic.blurOverlay && !results_isotopic_helpers.isMobile() ? "aspblur" : "no_aspblur";
      overlayImage = external_DoMini_namespaceObject("<div data-src='" + src + "' ></div>");
      overlayImage.css({
        "background-image": "url(" + src + ")"
      });
      overlayImage.css({
        "filter": "url(#" + filter + ")",
        "-webkit-filter": "url(#" + filter + ")",
        "-moz-filter": "url(#" + filter + ")",
        "-o-filter": "url(#" + filter + ")",
        "-ms-filter": "url(#" + filter + ")"
      }).addClass("asp_item_overlay_img");
      overlayImage = overlayImage.get(0).outerHTML;
    }
    external_DoMini_namespaceObject(el).prepend(overlayImage + overlay + image);
    external_DoMini_namespaceObject(el).attr("data-itemnum", j);
    j++;
  });
};
external_AjaxSearchPro_namespaceObject.plugin.isotopicPagerScroll = function() {
  let $this = this;
  if (external_DoMini_namespaceObject("nav>ul li.asp_active", $this.n("resultsDiv")).length <= 0)
    return false;
  let $activeLeft = external_DoMini_namespaceObject("nav>ul li.asp_active", $this.n("resultsDiv")).offset().left, $activeWidth = external_DoMini_namespaceObject("nav>ul li.asp_active", $this.n("resultsDiv")).outerWidth(true), $nextLeft = external_DoMini_namespaceObject("nav>a.asp_next", $this.n("resultsDiv")).offset().left, $prevLeft = external_DoMini_namespaceObject("nav>a.asp_prev", $this.n("resultsDiv")).offset().left;
  if ($activeWidth <= 0) return;
  let toTheLeft = Math.ceil(($prevLeft - $activeLeft + 2 * $activeWidth) / $activeWidth);
  if (toTheLeft > 0) {
    if (external_DoMini_namespaceObject("nav>ul li.asp_active", $this.n("resultsDiv")).prev().length === 0) {
      external_DoMini_namespaceObject("nav>ul", $this.n("resultsDiv")).css({
        "left": $activeWidth + "px"
      });
      return;
    }
    external_DoMini_namespaceObject("nav>ul", $this.n("resultsDiv")).css({
      "left": external_DoMini_namespaceObject("nav>ul", $this.n("resultsDiv")).position().left + $activeWidth * toTheLeft + "px"
    });
  } else {
    let toTheRight;
    if (external_DoMini_namespaceObject("nav>ul li.asp_active", $this.n("resultsDiv")).next().length === 0) {
      toTheRight = Math.ceil(($activeLeft - $nextLeft + $activeWidth) / $activeWidth);
    } else {
      toTheRight = Math.ceil(($activeLeft - $nextLeft + 2 * $activeWidth) / $activeWidth);
    }
    if (toTheRight > 0) {
      external_DoMini_namespaceObject("nav>ul", $this.n("resultsDiv")).css({
        "left": external_DoMini_namespaceObject("nav>ul", $this.n("resultsDiv")).position().left - $activeWidth * toTheRight + "px"
      });
    }
  }
};
external_AjaxSearchPro_namespaceObject.plugin.showPagination = function(force_refresh) {
  let $this = this;
  force_refresh = typeof force_refresh !== "undefined" ? force_refresh : false;
  if (!$this.o.isotopic.pagination) {
    if ($this.isotopic != null && force_refresh)
      $this.isotopic.arrange({
        transitionDuration: 0,
        filter: $this.filterFns["number"]
      });
    return false;
  }
  if ($this.call_num < 1 || force_refresh)
    external_DoMini_namespaceObject("nav.asp_navigation ul li", $this.n("resultsDiv")).remove();
  external_DoMini_namespaceObject("nav.asp_navigation", $this.n("resultsDiv")).css("display", "none");
  if ($this.n("items").length > 0) {
    let start = 1;
    if ($this.call_num > 0 && !force_refresh) {
      start = $this.n("resultsDiv").find("nav.asp_navigation ul").first().find("li").length + 1;
    }
    let pages = Math.ceil($this.n("items").length / $this.il.itemsPerPage);
    if (pages > 1) {
      let newPage = force_refresh && $this.il.lastVisibleItem > 0 ? Math.ceil($this.il.lastVisibleItem / $this.il.itemsPerPage) : 1;
      newPage = newPage <= 0 ? 1 : newPage;
      for (let i = start; i <= pages; i++) {
        if (i === newPage)
          external_DoMini_namespaceObject("nav.asp_navigation ul", $this.n("resultsDiv")).append("<li class='asp_active'><span>" + i + "</span></li>");
        else
          external_DoMini_namespaceObject("nav.asp_navigation ul", $this.n("resultsDiv")).append("<li><span>" + i + "</span></li>");
      }
      external_DoMini_namespaceObject("nav.asp_navigation", $this.n("resultsDiv")).css("display", "block");
      if (force_refresh)
        external_DoMini_namespaceObject("nav.asp_navigation ul li.asp_active", $this.n("resultsDiv")).trigger("click_trigger");
      else
        external_DoMini_namespaceObject("nav.asp_navigation ul li.asp_active", $this.n("resultsDiv")).trigger("click");
    } else {
      if ($this.isotopic != null && force_refresh)
        $this.isotopic.arrange({
          transitionDuration: 0,
          filter: $this.filterFns["number"]
        });
    }
  }
};
external_AjaxSearchPro_namespaceObject.plugin.hidePagination = function() {
  let $this = this;
  external_DoMini_namespaceObject("nav.asp_navigation", $this.n("resultsDiv")).css("display", "none");
};
external_AjaxSearchPro_namespaceObject.plugin.visiblePagination = function() {
  let $this = this;
  return external_DoMini_namespaceObject("nav.asp_navigation", $this.n("resultsDiv")).css("display") !== "none";
};
external_AjaxSearchPro_namespaceObject.plugin.calculateIsotopeRows = function() {
  let $this = this, itemWidth, itemHeight, containerWidth = parseFloat($this.n("results").width());
  if (results_isotopic_helpers.deviceType() === "desktop") {
    itemWidth = results_isotopic_helpers.getWidthFromCSSValue($this.o.isotopic.itemWidth, containerWidth);
    itemHeight = results_isotopic_helpers.getWidthFromCSSValue($this.o.isotopic.itemHeight, containerWidth);
  } else if (results_isotopic_helpers.deviceType() === "tablet") {
    itemWidth = results_isotopic_helpers.getWidthFromCSSValue($this.o.isotopic.itemWidthTablet, containerWidth);
    itemHeight = results_isotopic_helpers.getWidthFromCSSValue($this.o.isotopic.itemHeightTablet, containerWidth);
  } else {
    itemWidth = results_isotopic_helpers.getWidthFromCSSValue($this.o.isotopic.itemWidthPhone, containerWidth);
    itemHeight = results_isotopic_helpers.getWidthFromCSSValue($this.o.isotopic.itemHeightPhone, containerWidth);
  }
  let realColumnCount = containerWidth / itemWidth, gutterWidth = $this.o.isotopic.gutter, floorColumnCount = Math.floor(realColumnCount);
  if (floorColumnCount <= 0)
    floorColumnCount = 1;
  if (Math.abs(containerWidth / floorColumnCount - itemWidth) > Math.abs(containerWidth / (floorColumnCount + 1) - itemWidth)) {
    floorColumnCount++;
  }
  let newItemW = containerWidth / floorColumnCount - (floorColumnCount - 1) * gutterWidth / floorColumnCount, newItemH = newItemW / itemWidth * itemHeight;
  $this.il.columns = floorColumnCount;
  $this.il.itemsPerPage = floorColumnCount * $this.il.rows;
  $this.il.lastVisibleItem = 0;
  $this.n("results").find(".asp_isotopic_item").forEach(function(el, index) {
    if (external_DoMini_namespaceObject(el).css("display") !== "none") {
      $this.il.lastVisibleItem = index;
    }
  });
  if (!isNaN($this.il.columns) && !isNaN($this.il.itemsPerPage)) {
    $this.n("resultsDiv").data("colums", $this.il.columns);
    $this.n("resultsDiv").data("itemsperpage", $this.il.itemsPerPage);
  }
  $this.currentPage = 1;
  $this.n("items").css({
    width: Math.floor(newItemW) + "px",
    height: Math.floor(newItemH) + "px"
  });
};
/* harmony default export */ var results_isotopic = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/bundle/optimized/asp-results-isotopic.js




/* harmony default export */ var asp_results_isotopic = (external_AjaxSearchPro_namespaceObject);

Object(window.WPD).AjaxSearchPro = __webpack_exports__["default"];
/******/ })()
;