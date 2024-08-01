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
  "default": function() { return /* binding */ asp_autocomplete; }
});

;// CONCATENATED MODULE: external "AjaxSearchPro"
var external_AjaxSearchPro_namespaceObject = Object(window.WPD)["AjaxSearchPro"];
;// CONCATENATED MODULE: external "DoMini"
var external_DoMini_namespaceObject = Object(window.WPD)["DoMini"];
;// CONCATENATED MODULE: ./js/src/plugin/core/actions/autocomplete.js



"use strict";
external_AjaxSearchPro_namespaceObject.plugin.autocompleteCheck = function(val = "") {
  if (this.n("text").val() === "") {
    this.n("textAutocomplete").val("");
    return false;
  }
  let autocompleteVal = this.n("textAutocomplete").val();
  return !(autocompleteVal !== "" && autocompleteVal.indexOf(val) === 0);
};
external_AjaxSearchPro_namespaceObject.plugin.autocomplete = function() {
  let $this = this, val = $this.n("text").val();
  if (!$this.autocompleteCheck(val)) {
    return;
  }
  if ($this.n("text").val().length >= $this.o.autocomplete.trigger_charcount) {
    let data = {
      action: "ajaxsearchpro_autocomplete",
      asid: $this.o.id,
      sauto: $this.n("text").val(),
      asp_inst_id: $this.o.rid,
      options: external_DoMini_namespaceObject("form", $this.n("searchsettings")).serialize()
    };
    $this.postAuto = external_DoMini_namespaceObject.fn.ajax({
      "url": ASP.ajaxurl,
      "method": "POST",
      "data": data,
      "success": function(response) {
        if (response.length > 0) {
          response = external_DoMini_namespaceObject("<textarea />").html(response).text();
          response = response.replace(/^\s*[\r\n]/gm, "");
          response = val + response.substring(val.length);
        }
        $this.n("textAutocomplete").val(response);
        $this.fixAutocompleteScrollLeft();
      }
    });
  }
};
external_AjaxSearchPro_namespaceObject.plugin.autocompleteGoogleOnly = function() {
  let $this = this, val = $this.n("text").val();
  if (!$this.autocompleteCheck(val)) {
    return;
  }
  let lang = $this.o.autocomplete.lang;
  ["wpml_lang", "polylang_lang", "qtranslate_lang"].forEach(function(v) {
    if (external_DoMini_namespaceObject('input[name="' + v + '"]', $this.n("searchsettings")).length > 0 && external_DoMini_namespaceObject('input[name="' + v + '"]', $this.n("searchsettings")).val().length > 1) {
      lang = external_DoMini_namespaceObject('input[name="' + v + '"]', $this.n("searchsettings")).val();
    }
  });
  if ($this.n("text").val().length >= $this.o.autocomplete.trigger_charcount) {
    external_DoMini_namespaceObject.fn.ajax({
      url: "https://clients1.google.com/complete/search",
      cors: "no-cors",
      data: {
        q: val,
        hl: lang,
        nolabels: "t",
        client: "hp",
        ds: ""
      },
      success: function(data) {
        if (data[1].length > 0) {
          let response = data[1][0][0].replace(/(<([^>]+)>)/ig, "");
          response = external_DoMini_namespaceObject("<textarea />").html(response).text();
          response = response.substring(val.length);
          $this.n("textAutocomplete").val(val + response);
          $this.fixAutocompleteScrollLeft();
        }
      }
    });
  }
};
external_AjaxSearchPro_namespaceObject.plugin.fixAutocompleteScrollLeft = function() {
  this.n("textAutocomplete").get(0).scrollLeft = this.n("text").get(0).scrollLeft;
};
/* harmony default export */ var autocomplete = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/events/autocomplete.js



"use strict";
let helpers = external_AjaxSearchPro_namespaceObject.helpers;
external_AjaxSearchPro_namespaceObject.plugin.initAutocompleteEvent = function() {
  let $this = this, tt;
  if ($this.o.autocomplete.enabled && !helpers.isMobile() || $this.o.autocomplete.mobile && helpers.isMobile()) {
    $this.n("text").on("keyup", function(e) {
      $this.keycode = e.keyCode || e.which;
      $this.ktype = e.type;
      let thekey = 39;
      if (external_DoMini_namespaceObject("body").hasClass("rtl"))
        thekey = 37;
      if ($this.keycode === thekey && $this.n("textAutocomplete").val() !== "") {
        e.preventDefault();
        $this.n("text").val($this.n("textAutocomplete").val());
        if ($this.o.trigger.type) {
          $this.searchAbort();
          $this.search();
        }
      } else {
        clearTimeout(tt);
        if ($this.postAuto != null) $this.postAuto.abort();
        if ($this.o.autocomplete.googleOnly) {
          $this.autocompleteGoogleOnly();
        } else {
          tt = setTimeout(function() {
            $this.autocomplete();
            tt = null;
          }, $this.o.trigger.autocomplete_delay);
        }
      }
    });
    $this.n("text").on("keyup mouseup input blur select", function() {
      $this.fixAutocompleteScrollLeft();
    });
  }
};
/* harmony default export */ var events_autocomplete = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/bundle/optimized/asp-autocomplete.js




/* harmony default export */ var asp_autocomplete = (external_AjaxSearchPro_namespaceObject);

Object(window.WPD).AjaxSearchPro = __webpack_exports__["default"];
/******/ })()
;