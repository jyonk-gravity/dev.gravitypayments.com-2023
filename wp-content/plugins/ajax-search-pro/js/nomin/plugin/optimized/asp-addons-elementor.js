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
  "default": function() { return /* binding */ asp_addons_elementor; }
});

;// CONCATENATED MODULE: external "AjaxSearchPro"
var external_AjaxSearchPro_namespaceObject = Object(window.WPD)["AjaxSearchPro"];
;// CONCATENATED MODULE: external "DoMini"
var external_DoMini_namespaceObject = Object(window.WPD)["DoMini"];
;// CONCATENATED MODULE: ./js/src/addons/elementor.js



const helpers = external_AjaxSearchPro_namespaceObject.helpers;
class ElementorAddon {
  name = "Elementor Widget Fixes";
  init() {
    helpers.Hooks.addFilter("asp/init/etc", this.fixElementorPostPagination, 10, this);
    helpers.Hooks.addFilter("asp/live_load/selector", this.fixSelector, 10, this);
    helpers.Hooks.addFilter("asp/live_load/url", this.url, 10, this);
    helpers.Hooks.addFilter("asp/live_load/start", this.start, 10, this);
    helpers.Hooks.addFilter("asp/live_load/replacement_node", this.fixElementorLoadMoreResults, 10, this);
    helpers.Hooks.addFilter("asp/live_load/finished", this.finished, 10, this);
  }
  fixSelector(selector) {
    if (selector.indexOf("asp_es_") > -1) {
      selector += " .elementor-widget-container";
    }
    return selector;
  }
  url(url, obj, selector, widget) {
    if (url.indexOf("asp_force_reset_pagination=1") >= 0) {
      url = url.replace(/\?product\-page\=[0-9]+\&/, "?");
    }
    return url;
  }
  start(url, obj, selector, widget) {
    let isNewSearch = external_DoMini_namespaceObject("form", obj.n("searchsettings")).serialize() + obj.n("text").val().trim() != obj.lastSuccesfulSearch;
    if (!isNewSearch && external_DoMini_namespaceObject(widget).find(".e-load-more-spinner").length > 0) {
      external_DoMini_namespaceObject(widget).css("opacity", 1);
    }
  }
  finished(url, obj, selector, widget) {
    let $el = external_DoMini_namespaceObject(widget);
    if (selector.indexOf("asp_es_") !== false && typeof elementorFrontend != "undefined" && typeof elementorFrontend.init != "undefined" && $el.find(".asp_elementor_nores").length == 0) {
      let widgetType = $el.parent().data("widget_type");
      if (widgetType != "" && typeof jQuery != "undefined") {
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
    let $first = $el.find(".elementor-post, .product").first();
    if ($first.length && !$first.inViewPort(40)) {
      $first.get(0).scrollIntoView({ behavior: "smooth", block: "center", inline: "nearest" });
    }
  }
  fixElementorPostPagination(obj, url) {
    let $this = obj, _this = this, $es = external_DoMini_namespaceObject(".asp_es_" + $this.o.id);
    url = typeof url == "undefined" ? location.href : url;
    if ($es.length > 0) {
      _this.elementorHideSpinner($es.get(0));
      let i = url.indexOf("?");
      if (i >= 0) {
        let queryString = url.substring(i + 1);
        if (queryString) {
          queryString = queryString.replace(/&asp_force_reset_pagination=1/gmi, "");
          if ($es.find(".e-load-more-anchor").length > 0 && $es.find(".elementor-pagination a").length == 0) {
            let handler = function(e) {
              e.preventDefault();
              e.stopPropagation();
              if (!obj.searching) {
                let page = $es.data("page") == "" ? 2 : parseInt($es.data("page")) + 1;
                let newQS = queryString.split("&page=");
                $es.data("page", page);
                $this.showLoader();
                _this.elementorShowSpinner($es.get(0));
                $this.liveLoad(
                  ".asp_es_" + $this.o.id,
                  url.split("?")[0] + "?" + newQS[0] + "&page=" + page,
                  false,
                  true
                );
              }
            };
            $es.find(".e-load-more-anchor").next(".elementor-button-wrapper").find("a").attr("href", "");
            $es.find(".e-load-more-anchor").next(".elementor-button-wrapper").offForced().on("click", handler);
            $es.find(".asp_e_load_more_anchor").on("asp_e_load_more", handler);
          } else {
            $es.find(".elementor-pagination a, .elementor-widget-container .woocommerce-pagination a").each(function() {
              let a = external_DoMini_namespaceObject(this).attr("href");
              if (a.indexOf("asp_ls=") < 0 && a.indexOf("asp_ls&") < 0) {
                if (a.indexOf("?") < 0) {
                  external_DoMini_namespaceObject(this).attr("href", a + "?" + queryString);
                } else {
                  external_DoMini_namespaceObject(this).attr("href", a + "&" + queryString);
                }
              } else {
                external_DoMini_namespaceObject(this).attr("href", external_DoMini_namespaceObject(this).attr("href").replace(/&asp_force_reset_pagination=1/gmi, ""));
              }
            });
            $es.find(".elementor-pagination a, .elementor-widget-container .woocommerce-pagination a").on("click", function(e) {
              e.preventDefault();
              e.stopImmediatePropagation();
              e.stopPropagation();
              $this.showLoader();
              $this.liveLoad(".asp_es_" + $this.o.id, external_DoMini_namespaceObject(this).attr("href"), false, true);
            });
          }
        }
      }
    }
    return $this;
  }
  fixElementorLoadMoreResults(replacementNode, obj, originalNode, data) {
    let settings = external_DoMini_namespaceObject(originalNode).closest("div[data-settings]").data("settings"), $aspLoadMoreAnchor = external_DoMini_namespaceObject(originalNode).find(".asp_e_load_more_anchor");
    if (settings != null && settings != "") {
      settings = JSON.parse(settings);
      if (settings.pagination_type == "load_more_infinite_scroll" && $aspLoadMoreAnchor.length == 0) {
        external_DoMini_namespaceObject(".e-load-more-anchor").css("display", "none");
        external_DoMini_namespaceObject(originalNode).append('<div class="asp_e_load_more_anchor"></div>');
        $aspLoadMoreAnchor = external_DoMini_namespaceObject(originalNode).find(".asp_e_load_more_anchor");
        let handler = function() {
          if ($aspLoadMoreAnchor.inViewPort(50)) {
            $aspLoadMoreAnchor.trigger("asp_e_load_more");
            $aspLoadMoreAnchor.remove();
          }
        };
        obj.documentEventHandlers.push({
          "node": window,
          "event": "scroll",
          "handler": handler
        });
        external_DoMini_namespaceObject(window).on("scroll", handler);
      }
      if (external_DoMini_namespaceObject(replacementNode).find(".e-load-more-spinner").length > 0) {
        external_DoMini_namespaceObject(originalNode).removeClass("e-load-more-pagination-loading");
        let isNewSearch = external_DoMini_namespaceObject("form", obj.n("searchsettings")).serialize() + obj.n("text").val().trim() != obj.lastSuccesfulSearch, $loadMoreButton = external_DoMini_namespaceObject(originalNode).find(".e-load-more-anchor").next(".elementor-button-wrapper"), $loadMoreMessage = external_DoMini_namespaceObject(originalNode).find(".e-load-more-message"), $article = external_DoMini_namespaceObject(replacementNode).find("article");
        if ($article.length > 0 && $article.parent().length > 0 && external_DoMini_namespaceObject(originalNode).find("article").parent().length > 0) {
          let newData = $article.get(0).innerHTML, previousData = external_DoMini_namespaceObject(originalNode).data("asp-previous-data");
          if (previousData == "" || isNewSearch) {
            external_DoMini_namespaceObject(originalNode).find("article").parent().get(0).innerHTML = newData;
            external_DoMini_namespaceObject(originalNode).data("asp-previous-data", newData);
            $loadMoreButton.css("display", "block");
            $loadMoreMessage.css("display", "none");
          } else if (previousData == newData) {
            $loadMoreButton.css("display", "none");
            $loadMoreMessage.css("display", "block");
            $aspLoadMoreAnchor.remove();
          } else {
            external_DoMini_namespaceObject(originalNode).find("article").parent().get(0).innerHTML += newData;
            external_DoMini_namespaceObject(originalNode).data("asp-previous-data", newData);
          }
        } else {
          $loadMoreButton.css("display", "none");
          $loadMoreMessage.css("display", "block");
          $aspLoadMoreAnchor.remove();
        }
        return null;
      }
    }
    return replacementNode;
  }
  elementorShowSpinner(widget) {
    external_DoMini_namespaceObject(widget).addClass("e-load-more-pagination-loading");
    external_DoMini_namespaceObject(widget).find(".e-load-more-spinner>*").addClass("eicon-animation-spin");
    external_DoMini_namespaceObject(widget).css("opacity", 1);
  }
  elementorHideSpinner(widget) {
    external_DoMini_namespaceObject(widget).removeClass("e-load-more-pagination-loading");
    external_DoMini_namespaceObject(widget).find(".eicon-animation-spin").removeClass("eicon-animation-spin");
  }
}
external_AjaxSearchPro_namespaceObject.addons.add(new ElementorAddon());
/* harmony default export */ var elementor = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/bundle/optimized/asp-addons-elementor.js



/* harmony default export */ var asp_addons_elementor = (external_AjaxSearchPro_namespaceObject);

Object(window.WPD).AjaxSearchPro = __webpack_exports__["default"];
/******/ })()
;