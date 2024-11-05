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
  "default": function() { return /* binding */ asp_addons_elementor; }
});

;// CONCATENATED MODULE: external "AjaxSearchPro"
var external_AjaxSearchPro_namespaceObject = Object(window.WPD)["AjaxSearchPro"];
var external_AjaxSearchPro_default = /*#__PURE__*/__webpack_require__.n(external_AjaxSearchPro_namespaceObject);
;// CONCATENATED MODULE: external "DoMini"
var external_DoMini_namespaceObject = Object(window.WPD)["DoMini"];
var external_DoMini_default = /*#__PURE__*/__webpack_require__.n(external_DoMini_namespaceObject);
;// CONCATENATED MODULE: ./src/client/addons/elementor.ts


const helpers = (external_AjaxSearchPro_default()).helpers;
class ElementorAddon {
  name = "Elementor Widget Fixes";
  init() {
    const { Hooks } = helpers;
    Hooks.addFilter("asp/init/etc", this.fixElementorPostPagination.bind(this), 10, this);
    Hooks.addFilter("asp/live_load/selector", this.fixSelector.bind(this), 10, this);
    Hooks.addFilter("asp/live_load/start", this.start.bind(this), 10, this);
    Hooks.addFilter("asp/live_load/finished", this.finished.bind(this), 10, this);
  }
  fixSelector(selector) {
    if (selector.includes("asp_es_")) {
      selector += " .elementor-widget-container";
    }
    return selector;
  }
  start(url, obj, selector, widget) {
    const searchSettingsSerialized = obj.n("searchsettings").find("form").serialize();
    const textValue = obj.n("text").val().trim();
    const isNewSearch = searchSettingsSerialized + textValue !== obj.lastSuccesfulSearch;
    if (!isNewSearch && external_DoMini_default()(widget).find(".e-load-more-spinner").length > 0) {
      external_DoMini_default()(widget).css("opacity", "1");
    }
    external_DoMini_default()(selector).parent().removeClass("e-load-more-pagination-end");
  }
  finished(url, obj, selector, widget) {
    const $el = external_DoMini_default()(widget);
    if (selector.includes("asp_es_") && typeof elementorFrontend !== "undefined" && typeof elementorFrontend.init !== "undefined" && $el.find(".asp_elementor_nores").length === 0) {
      const widgetType = $el.parent().data("widget_type") || "";
      if (widgetType !== "" && typeof jQuery !== "undefined") {
        elementorFrontend.hooks.doAction("frontend/element_ready/" + widgetType, jQuery($el.parent().get(0)));
      }
      this.fixElementorPostPagination(obj, url);
      if (obj.o.scrollToResults.enabled) {
        this.scrollToResultsIfNeeded($el);
      }
      obj.n("s").trigger("asp_elementor_results", [obj.o.id, obj.o.iid, $el.parent().get(0)], true, true);
    }
  }
  scrollToResultsIfNeeded($el) {
    const $first = $el.find(".elementor-post, .product").first();
    if ($first.length && !$first.isInViewport(40)) {
      $first.get(0).scrollIntoView({ behavior: "smooth", block: "center", inline: "nearest" });
    }
  }
  fixElementorPostPagination(obj, url) {
    const $es = external_DoMini_default()(".asp_es_" + obj.o.id);
    url = url || location.href;
    if (!$es.length) {
      return obj;
    }
    const urlObj = new URL(url);
    if (!urlObj.searchParams.size) {
      return obj;
    }
    this.elementorHideSpinner($es.get(0));
    urlObj.searchParams.delete("asp_force_reset_pagination");
    const $loadMoreAnchor = $es.find(".e-load-more-anchor");
    const paginationLinks = $es.find(".elementor-pagination a, .elementor-widget-container .woocommerce-pagination a");
    if ($loadMoreAnchor.length > 0 && !paginationLinks.length) {
      const $widgetContainer = $es.find(".elementor-widget-container").get(0);
      const fixAnchor = () => {
        const pageData = $loadMoreAnchor.data("page");
        const page = pageData ? parseInt(pageData, 10) + 1 : 2;
        urlObj.searchParams.set("page", page.toString());
        $loadMoreAnchor.data("next-page", urlObj.href);
        $loadMoreAnchor.next(".elementor-button-wrapper").find("a").attr("href", urlObj.href);
      };
      if ($widgetContainer) {
        const observer = new MutationObserver(() => {
          fixAnchor();
          console.log("Mutation observed: fixing anchor.");
        });
        observer.observe($widgetContainer, {
          childList: true,
          subtree: true
        });
      }
      fixAnchor();
    } else {
      paginationLinks.each(function() {
        const $link = external_DoMini_default()(this);
        const href = $link.attr("href") || "";
        const itemUrlObj = new URL(href, window.location.origin);
        if (!itemUrlObj.searchParams.has("asp_ls")) {
          urlObj.searchParams.forEach((value, key) => itemUrlObj.searchParams.set(key, value));
        } else {
          itemUrlObj.searchParams.delete("asp_force_reset_pagination");
        }
        $link.attr("href", itemUrlObj.href);
      });
    }
    return obj;
  }
  elementorHideSpinner(widget) {
    external_DoMini_default()(widget).removeClass("e-load-more-pagination-loading").find(".eicon-animation-spin").removeClass("eicon-animation-spin");
  }
}
external_AjaxSearchPro_default().addons.add(new ElementorAddon());
/* harmony default export */ var elementor = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./src/client/bundle/optimized/asp-addons-elementor.js


/* harmony default export */ var asp_addons_elementor = (external_AjaxSearchPro_namespaceObject);

Object(window.WPD).AjaxSearchPro = __webpack_exports__["default"];
/******/ })()
;