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
  "default": function() { return /* binding */ asp_addons_woocommerce; }
});

;// external "AjaxSearchPro"
var external_AjaxSearchPro_namespaceObject = Object(window.WPD)["AjaxSearchPro"];
;// ./src/client/addons/woocommerce.js


const helpers = external_AjaxSearchPro_namespaceObject.helpers;
class WooCommerceAddToCartAddon {
  init() {
    helpers.Hooks.addFilter("asp/search/end", this.finished.bind(this), 10, this);
  }
  finished($this) {
    if (typeof wc_add_to_cart_params === "undefined" || typeof jQuery === "undefined") {
      return;
    }
    this.requests = [];
    this.addRequest = this.addRequest.bind(this);
    this.run = this.run.bind(this);
    this.$liveRegion = this.createLiveRegion();
    jQuery($this.n("resdrg").get(0)).find(".add-to-cart-button:not(.wc-interactive)").off().on("click", { addToCartHandler: this }, this.onAddToCart);
  }
  /**
   * Add add-to-cart event to the queue.
   */
  addRequest(request) {
    this.requests.push(request);
    if (this.requests.length === 1) {
      this.run();
    }
  }
  /**
   * Run add-to-cart events in sequence.
   */
  run() {
    const requestManager = this;
    const originalCallback = requestManager.requests[0].complete;
    requestManager.requests[0].complete = function() {
      if (typeof originalCallback === "function") {
        originalCallback();
      }
      requestManager.requests.shift();
      if (requestManager.requests.length > 0) {
        requestManager.run();
      }
    };
    jQuery.ajax(this.requests[0]);
  }
  /**
   * Handle the add to cart event.
   */
  onAddToCart(e) {
    const $thisbutton = jQuery(this);
    if ($thisbutton.is(".ajax-add-to-cart")) {
      if (!$thisbutton.attr("data-product_id")) {
        return true;
      }
      e.data.addToCartHandler.$liveRegion.text("").removeAttr("aria-relevant");
      e.preventDefault();
      $thisbutton.removeClass("added");
      $thisbutton.addClass("loading");
      if (false === jQuery(document.body).triggerHandler("should_send_ajax_request.adding_to_cart", [$thisbutton])) {
        jQuery(document.body).trigger("ajax_request_not_sent.adding_to_cart", [false, false, $thisbutton]);
        return true;
      }
      const data = {};
      jQuery.each($thisbutton.data(), function(key, value) {
        data[key] = value;
      });
      jQuery.each($thisbutton[0].dataset, function(key, value) {
        data[key] = value;
      });
      const $quantityButton = $thisbutton.closest(".add-to-cart-container").find(".add-to-cart-quantity");
      if ($quantityButton.length > 0) {
        data.quantity = $quantityButton.get(0).value;
      }
      jQuery(document.body).trigger("adding_to_cart", [$thisbutton, data]);
      e.data.addToCartHandler.addRequest({
        type: "POST",
        url: wc_add_to_cart_params.wc_ajax_url.toString().replace("%%endpoint%%", "add_to_cart"),
        data,
        success: function(response) {
          if (!response) {
            return;
          }
          if (response.error && response.product_url) {
            window.location = response.product_url;
            return;
          }
          if (wc_add_to_cart_params.cart_redirect_after_add === "yes") {
            window.location = wc_add_to_cart_params.cart_url;
            return;
          }
          jQuery(document.body).trigger("added_to_cart", [response.fragments, response.cart_hash, $thisbutton]);
        },
        dataType: "json"
      });
    }
  }
  /**
   * Add live region into the body element.
   */
  createLiveRegion() {
    const existingLiveRegion = jQuery(".widget_shopping_cart_live_region");
    if (existingLiveRegion.length) {
      return existingLiveRegion;
    }
    return jQuery('<div class="widget_shopping_cart_live_region screen-reader-text" role="status"></div>').appendTo("body");
  }
}
external_AjaxSearchPro_namespaceObject.addons.add(new WooCommerceAddToCartAddon());
/* harmony default export */ var woocommerce = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/bundle/optimized/asp-addons-woocommerce.js



/* harmony default export */ var asp_addons_woocommerce = (external_AjaxSearchPro_namespaceObject);

Object(window.WPD).AjaxSearchPro = __webpack_exports__["default"];
/******/ })()
;