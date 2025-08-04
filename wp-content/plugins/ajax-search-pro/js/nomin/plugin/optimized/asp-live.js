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
  "default": function() { return /* binding */ asp_live; }
});

;// external "AjaxSearchPro"
var external_AjaxSearchPro_namespaceObject = Object(window.WPD)["AjaxSearchPro"];
;// external "DoMini"
var external_DoMini_namespaceObject = Object(window.WPD)["DoMini"];
;// ./src/client/plugin/core/actions/live.js



"use strict";
let helpers = external_AjaxSearchPro_namespaceObject.helpers;
external_AjaxSearchPro_namespaceObject.plugin.liveLoad = function(origSelector, url, updateLocation, forceAjax, cache) {
  let selector = origSelector;
  if (selector === "body" || selector === "html") {
    console.log("Ajax Search Pro: Do not use html or body as the live loader selector.");
    return false;
  }
  let $this = this;
  if (ASP.pageHTML !== "") {
    $this.setLiveLoadCache(ASP.pageHTML, origSelector);
  }
  function process(html) {
    let data = helpers.Hooks.applyFilters("asp/live_load/raw_data", html, $this);
    let parser = new DOMParser();
    let dataNode = parser.parseFromString(data, "text/html");
    let $dataNode = external_DoMini_namespaceObject(dataNode);
    if ($this.o.statistics) {
      $this.stat_addKeyword($this.o.id, $this.n("text").val());
    }
    if (data !== "" && $dataNode.length > 0 && $dataNode.find(selector).length > 0) {
      data = data.replace(/&asp_force_reset_pagination=1/gmi, "");
      data = data.replace(/%26asp_force_reset_pagination%3D1/gmi, "");
      data = data.replace(/&#038;asp_force_reset_pagination=1/gmi, "");
      if (helpers.isSafari()) {
        data = data.replace(/srcset/gmi, "nosrcset");
      }
      data = helpers.Hooks.applyFilters("asp_live_load_html", data, $this.o.id, $this.o.iid);
      $dataNode = external_DoMini_namespaceObject(parser.parseFromString(data, "text/html"));
      let replacementNode = $dataNode.find(selector).get(0);
      replacementNode = helpers.Hooks.applyFilters("asp/live_load/replacement_node", replacementNode, $this, $el.get(0), data);
      if (replacementNode != null) {
        $el.get(0).parentNode.replaceChild(replacementNode, $el.get(0));
      }
      $el = external_DoMini_namespaceObject(selector).first();
      if (updateLocation) {
        document.title = dataNode.title;
        history.pushState({}, null, url);
      }
      external_DoMini_namespaceObject(selector).first().find(".woocommerce-ordering select.orderby").on("change", function() {
        if (external_DoMini_namespaceObject(this).closest("form").length > 0) {
          external_DoMini_namespaceObject(this).closest("form").get(0).submit();
        }
      });
      if ($this.o.highlight) {
        $el.highlight(
          $this.n("text").val().replace(/["']/g, "").split(" "),
          { element: "span", className: "asp_single_highlighted_" + $this.o.id, wordsOnly: !!$this.o.highlightWholewords }
        );
      }
      $this.addHighlightString(external_DoMini_namespaceObject(selector).find("a"));
      helpers.Hooks.applyFilters("asp/live_load/finished", url, $this, selector, $el.get(0));
      ASP.initialize();
      $this.lastSuccesfulSearch = external_DoMini_namespaceObject("form", $this.n("searchsettings")).serialize() + $this.n("text").val().trim();
      $this.lastSearchData = data;
      $this.setLiveLoadCache(html, origSelector);
    }
    $this.n("s").trigger("asp_search_end", [$this.o.id, $this.o.iid, $this.n("text").val(), data], true, true);
    $this.gaEvent?.("search_end", { "results_count": "unknown" });
    $this.hideLoader();
    $el.css("opacity", 1);
    $this.searching = false;
    if ($this.n("text").val() !== "") {
      $this.n("proclose").css({
        display: "block"
      });
    }
  }
  updateLocation = typeof updateLocation == "undefined" ? true : updateLocation;
  forceAjax = typeof forceAjax == "undefined" ? true : forceAjax;
  let altSel = $this.getLiveLoadAltSelectors();
  if (selector !== "#main")
    altSel.unshift("#main");
  if (external_DoMini_namespaceObject(selector).length < 1) {
    for (const s of altSel) {
      if (external_DoMini_namespaceObject(s).length > 0) {
        selector = s;
        break;
      }
    }
    if (external_DoMini_namespaceObject(selector).length < 1) {
      console.log("Ajax Search Pro: The live search selector does not exist on the page.");
      return false;
    }
  }
  selector = helpers.Hooks.applyFilters("asp/live_load/selector", selector, this);
  let $el = external_DoMini_namespaceObject(selector).first();
  $this.searchAbort();
  $el.css("opacity", 0.4);
  url = helpers.Hooks.applyFilters("asp/live_load/url", url, $this, selector, $el.get(0));
  helpers.Hooks.applyFilters("asp/live_load/start", url, $this, selector, $el.get(0));
  if (!forceAjax && $this.n("searchsettings").find("input[name=filters_initial]").val() === "1" && $this.n("text").val() === "") {
    window.WPD.intervalUntilExecute(function() {
      process(ASP.pageHTML);
    }, function() {
      return ASP.pageHTML !== "";
    });
  } else {
    if (typeof cache != "undefined") {
      process(cache.html);
    } else {
      $this.searching = true;
      $this.post = external_DoMini_namespaceObject.fn.ajax({
        url,
        method: "GET",
        success: function(data) {
          process(data);
          $this.isAutoP = false;
        },
        dataType: "html",
        fail: function(jqXHR) {
          $el.css("opacity", 1);
          if (jqXHR.aborted) {
            return;
          }
          $el.html("This request has failed. Please check your connection.");
          $this.hideLoader();
          $this.searching = false;
          $this.n("proclose").css({
            display: "block"
          });
          $this.isAutoP = false;
        }
      });
    }
  }
};
external_AjaxSearchPro_namespaceObject.plugin.getLiveLoadAltSelectors = function() {
  return [
    ".search-content",
    "#content #posts-container",
    "#content",
    "#Content",
    "div[role=main]",
    "main[role=main]",
    "div.theme-content",
    "div.td-ss-main-content",
    "main#page-content",
    "main.l-content",
    "#primary",
    "#main-content",
    ".main-content",
    ".search section .bde-post-loop",
    // breakdance posts loop section search archive
    ".archive section .bde-post-loop",
    // breakdance posts loop section general archive
    ".search section .bde-post-list",
    // breakdance posts list section search archive
    ".archive section .bde-post-list",
    // breakdance posts list section general archive
    "main .wp-block-query",
    // block themes
    "main"
    // fallback
  ];
};
external_AjaxSearchPro_namespaceObject.plugin.usingLiveLoader = function() {
  const $this = this;
  if ($this._usingLiveLoader !== void 0) return $this._usingLiveLoader;
  const o = $this.o;
  const idClass = "asp_es_" + o.id;
  const altSelectors = this.getLiveLoadAltSelectors().join(",");
  if (document.getElementsByClassName(idClass).length) {
    return $this._usingLiveLoader = true;
  }
  const options = ["resPage", "wooShop", "cptArchive", "taxArchive"];
  $this._usingLiveLoader = options.some((key) => {
    const opt = o[key];
    return opt.useAjax && (document.querySelector(opt.selector) || altSelectors && document.querySelector(altSelectors));
  });
  return $this._usingLiveLoader;
};
external_AjaxSearchPro_namespaceObject.plugin.getLiveURLbyBaseLocation = function(location) {
  let $this = this, url = "asp_ls=" + helpers.nicePhrase($this.n("text").val()), start = "&";
  if (location.indexOf("?") === -1) {
    start = "?";
  }
  let final = location + start + url + "&asp_active=1&asp_force_reset_pagination=1&p_asid=" + $this.o.id + "&p_asp_data=1&" + external_DoMini_namespaceObject("form", $this.n("searchsettings")).serialize();
  final = final.replace("?&", "?");
  final = final.replace("&&", "&");
  return final;
};
external_AjaxSearchPro_namespaceObject.plugin.getCurrentLiveURL = function() {
  const $this = this;
  const url = new URL(window.location.href);
  let location;
  url.hash = "";
  location = url.href;
  location = location.replace(/([?&])query-\w+-page=\d+/, "$1");
  location = location.indexOf("asp_ls=") > -1 ? location.slice(0, location.indexOf("asp_ls=")) : location;
  location = location.indexOf("asp_ls&") > -1 ? location.slice(0, location.indexOf("asp_ls&")) : location;
  location = location.indexOf("p_asid=") > -1 ? location.slice(0, location.indexOf("p_asid=")) : location;
  location = location.indexOf("asp_") > -1 ? location.slice(0, location.indexOf("asp_")) : location;
  return $this.getLiveURLbyBaseLocation(location);
};
external_AjaxSearchPro_namespaceObject.plugin.initLiveLoaderPopState = function() {
  let $this = this;
  $this.liveLoadCache = [];
  window.addEventListener("popstate", () => {
    let data = $this.getLiveLoadCache();
    if (data !== false) {
      $this.n("text").val(data.phrase);
      helpers.formData(external_DoMini_namespaceObject("form", $this.n("searchsettings")), data.settings);
      $this.resetNoUISliderFilters();
      $this.liveLoad(data.selector, document.location.href, false, false, data);
    }
  });
  if (ASP.pageHTML === "") {
    if (typeof ASP._ajax_page_html === "undefined") {
      ASP._ajax_page_html = true;
      external_DoMini_namespaceObject.fn.ajax({
        url: $this.currentPageURL,
        method: "GET",
        success: function(data) {
          ASP.pageHTML = data;
        },
        dataType: "html"
      });
    }
  }
};
external_AjaxSearchPro_namespaceObject.plugin.setLiveLoadCache = function(html, selector) {
  let $this = this;
  if ($this.liveLoadCache.filter((item) => {
    return item.href === document.location.href;
  }).length === 0) {
    $this.liveLoadCache.push({
      "href": html === ASP.pageHTML ? $this.currentPageURL : document.location.href,
      "phrase": html === ASP.pageHTML ? "" : $this.n("text").val(),
      "selector": selector,
      "html": html,
      "settings": html === ASP.pageHTML ? $this.originalFormData : helpers.formData(external_DoMini_namespaceObject("form", $this.n("searchsettings")))
    });
  }
};
external_AjaxSearchPro_namespaceObject.plugin.getLiveLoadCache = function() {
  let $this = this;
  let res = $this.liveLoadCache.filter((item) => {
    return item.href === document.location.href;
  });
  return res.length > 0 ? res[0] : false;
};
/* harmony default export */ var live = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/bundle/optimized/asp-live.js



/* harmony default export */ var asp_live = (external_AjaxSearchPro_namespaceObject);

Object(window.WPD).AjaxSearchPro = __webpack_exports__["default"];
/******/ })()
;