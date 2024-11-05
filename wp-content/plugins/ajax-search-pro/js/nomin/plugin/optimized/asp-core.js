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
  "default": function() { return /* binding */ asp_core; }
});

;// CONCATENATED MODULE: ./js/src/plugin/core/base.js

const base_AjaxSearchPro = new function() {
  this.helpers = {};
  this.plugin = {};
  this.addons = {
    addons: [],
    add: function(addon) {
      if (this.addons.indexOf(addon) === -1) {
        let k = this.addons.push(addon);
        this.addons[k - 1].init();
      }
    },
    remove: function(name) {
      this.addons.filter(function(addon) {
        if (addon.name === name) {
          if (typeof addon.destroy != "undefined") {
            addon.destroy();
          }
          return false;
        } else {
          return true;
        }
      });
    }
  };
}();
/* harmony default export */ var base = (base_AjaxSearchPro);

;// CONCATENATED MODULE: external "DoMini"
var external_DoMini_namespaceObject = Object(window.WPD)["DoMini"];
;// CONCATENATED MODULE: ./js/src/plugin/core/etc/helpers.js



"use strict";
base.helpers.Hooks = window.WPD.Hooks;
base.helpers.deviceType = function() {
  let w = window.innerWidth;
  if (w <= 640) {
    return "phone";
  } else if (w <= 1024) {
    return "tablet";
  } else {
    return "desktop";
  }
};
base.helpers.detectIOS = function() {
  if (typeof window.navigator != "undefined" && typeof window.navigator.userAgent != "undefined")
    return window.navigator.userAgent.match(/(iPod|iPhone|iPad)/) != null;
  return false;
};
base.helpers.isMobile = function() {
  try {
    document.createEvent("TouchEvent");
    return true;
  } catch (e) {
    return false;
  }
};
base.helpers.isTouchDevice = function() {
  return "ontouchstart" in window;
};
base.helpers.isSafari = function() {
  return /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
};
base.helpers.escapeHtml = function(unsafe) {
  return unsafe.replaceAll("&", "&amp;").replaceAll("<", "&lt;").replaceAll(">", "&gt;").replaceAll('"', "&quot;").replaceAll("'", "&#039;");
};
base.helpers.whichjQuery = function(plugin) {
  let jq = false;
  if (typeof window.$ != "undefined") {
    if (typeof plugin === "undefined") {
      jq = window.$;
    } else {
      if (typeof window.$.fn[plugin] != "undefined") {
        jq = window.$;
      }
    }
  }
  if (jq === false && typeof window.jQuery != "undefined") {
    jq = window.jQuery;
    if (typeof plugin === "undefined") {
      jq = window.jQuery;
    } else {
      if (typeof window.jQuery.fn[plugin] != "undefined") {
        jq = window.jQuery;
      }
    }
  }
  return jq;
};
base.helpers.formData = function(form, data) {
  let $this = this, els = form.find("input,textarea,select,button").get();
  if (arguments.length === 1) {
    data = {};
    els.forEach(function(el) {
      if (el.name && !el.disabled && (el.checked || /select|textarea/i.test(el.nodeName) || /text/i.test(el.type) || external_DoMini_namespaceObject(el).hasClass("hasDatepicker") || external_DoMini_namespaceObject(el).hasClass("asp_slider_hidden"))) {
        if (data[el.name] === void 0) {
          data[el.name] = [];
        }
        if (external_DoMini_namespaceObject(el).hasClass("hasDatepicker")) {
          data[el.name].push(external_DoMini_namespaceObject(el).parent().find(".asp_datepicker_hidden").val());
        } else {
          data[el.name].push(external_DoMini_namespaceObject(el).val());
        }
      }
    });
    return JSON.stringify(data);
  } else {
    if (typeof data != "object") {
      data = JSON.parse(data);
    }
    els.forEach(function(el) {
      if (el.name) {
        if (data[el.name]) {
          let names = data[el.name], _this = external_DoMini_namespaceObject(el);
          if (Object.prototype.toString.call(names) !== "[object Array]") {
            names = [names];
          }
          if (el.type === "checkbox" || el.type === "radio") {
            let val = _this.val(), found = false;
            for (let i = 0; i < names.length; i++) {
              if (names[i] === val) {
                found = true;
                break;
              }
            }
            _this.prop("checked", found);
          } else {
            _this.val(names[0]);
            if (external_DoMini_namespaceObject(el).hasClass("asp_gochosen") || external_DoMini_namespaceObject(el).hasClass("asp_goselect2")) {
              WPD.intervalUntilExecute(function(_$) {
                _$(el).trigger("change.asp_select2");
              }, function() {
                return $this.whichjQuery("asp_select2");
              }, 50, 3);
            } else if (external_DoMini_namespaceObject(el).hasClass("hasDatepicker")) {
              WPD.intervalUntilExecute(function(_$) {
                let value = names[0], format = _$(_this.get(0)).datepicker("option", "dateFormat");
                _$(_this.get(0)).datepicker("option", "dateFormat", "yy-mm-dd");
                _$(_this.get(0)).datepicker("setDate", value);
                _$(_this.get(0)).datepicker("option", "dateFormat", format);
                _$(_this.get(0)).trigger("selectnochange");
              }, function() {
                return $this.whichjQuery("datepicker");
              }, 50, 3);
            }
          }
        } else {
          if (el.type === "checkbox" || el.type === "radio") {
            external_DoMini_namespaceObject(el).prop("checked", false);
          }
        }
      }
    });
    return form;
  }
};
base.helpers.submitToUrl = function(action, method, input, target) {
  let form;
  form = external_DoMini_namespaceObject('<form style="display: none;" />');
  form.attr("action", action);
  form.attr("method", method);
  external_DoMini_namespaceObject("body").append(form);
  if (typeof input !== "undefined" && input !== null) {
    Object.keys(input).forEach(function(name) {
      let value = input[name];
      let $input = external_DoMini_namespaceObject('<input type="hidden" />');
      $input.attr("name", name);
      $input.attr("value", value);
      form.append($input);
    });
  }
  if (typeof target != "undefined" && target === "new") {
    form.attr("target", "_blank");
  }
  form.get(0).submit();
};
base.helpers.openInNewTab = function(url) {
  Object.assign(document.createElement("a"), { target: "_blank", href: url }).click();
};
base.helpers.isScrolledToBottom = function(el, tolerance) {
  return el.scrollHeight - el.scrollTop - external_DoMini_namespaceObject(el).outerHeight() < tolerance;
};
base.helpers.getWidthFromCSSValue = function(width, containerWidth) {
  let min = 100, ret;
  width = width + "";
  if (width.indexOf("px") > -1) {
    ret = parseInt(width, 10);
  } else if (width.indexOf("%") > -1) {
    if (typeof containerWidth != "undefined" && containerWidth != null) {
      ret = Math.floor(parseInt(width, 10) / 100 * containerWidth);
    } else {
      ret = parseInt(width, 10);
    }
  } else {
    ret = parseInt(width, 10);
  }
  return ret < 100 ? min : ret;
};
base.helpers.nicePhrase = function(s) {
  return encodeURIComponent(s).replace(/\%20/g, "+");
};
base.helpers.inputToFloat = function(input) {
  return input.replace(/^[.]/g, "").replace(/[^0-9.-]/g, "").replace(/^[-]/g, "x").replace(/[-]/g, "").replace(/[x]/g, "-").replace(/(\..*?)\..*/g, "$1");
};
base.helpers.addThousandSeparators = function(n, s) {
  if (s !== "") {
    s = s || ",";
    return String(n).replace(/(?:^|[^.\d])\d+/g, function(n2) {
      return n2.replace(/\B(?=(?:\d{3})+\b)/g, s);
    });
  } else {
    return n;
  }
};
base.helpers.decodeHTMLEntities = function(str) {
  let element = document.createElement("div");
  if (str && typeof str === "string") {
    str = str.replace(/<script[^>]*>([\S\s]*?)<\/script>/gmi, "");
    str = str.replace(/<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gmi, "");
    element.innerHTML = str;
    str = element.textContent;
    element.textContent = "";
  }
  return str;
};
base.helpers.isScrolledToRight = function(el) {
  return el.scrollWidth - external_DoMini_namespaceObject(el).outerWidth() === el.scrollLeft;
};
base.helpers.isScrolledToLeft = function(el) {
  return el.scrollLeft === 0;
};
/* harmony default export */ var helpers = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/actions/animation.js



"use strict";
base.plugin.addAnimation = function() {
  let $this = this, i = 0, j = 1, delay = 25, checkViewport = true;
  if ($this.call_num > 0 || $this._no_animations) {
    $this.n("results").find(".item, .asp_group_header").removeClass("opacityZero").removeClass("asp_an_" + $this.animOptions.items);
    return false;
  }
  $this.n("results").find(".item, .asp_group_header").forEach(function() {
    let x = this;
    if (j === 1) {
      checkViewport = external_DoMini_namespaceObject(x).inViewPort(0);
    }
    if (j > 1 && checkViewport && !external_DoMini_namespaceObject(x).inViewPort(0) || j > 80) {
      external_DoMini_namespaceObject(x).removeClass("opacityZero");
      return true;
    }
    if ($this.o.resultstype === "isotopic" && j > $this.il.itemsPerPage) {
      external_DoMini_namespaceObject(x).removeClass("opacityZero");
      return;
    }
    setTimeout(function() {
      external_DoMini_namespaceObject(x).addClass("asp_an_" + $this.animOptions.items);
      external_DoMini_namespaceObject(x).removeClass("opacityZero");
    }, i + delay);
    i = i + 45;
    j++;
  });
};
base.plugin.removeAnimation = function() {
  let $this = this;
  this.n("items").forEach(function() {
    external_DoMini_namespaceObject(this).removeClass("asp_an_" + $this.animOptions.items);
  });
};
/* harmony default export */ var animation = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/actions/filters.js



"use strict";
let filters_helpers = base.helpers;
base.plugin.setFilterStateInput = function(timeout) {
  let $this = this;
  if (typeof timeout == "undefined") {
    timeout = 65;
  }
  let process = function() {
    if (JSON.stringify($this.originalFormData) !== JSON.stringify(filters_helpers.formData(external_DoMini_namespaceObject("form", $this.n("searchsettings"))))) {
      $this.n("searchsettings").find("input[name=filters_initial]").val(0);
    } else {
      $this.n("searchsettings").find("input[name=filters_initial]").val(1);
    }
  };
  if (timeout === 0) {
    process();
  } else {
    setTimeout(function() {
      process();
    }, timeout);
  }
};
base.plugin.resetSearchFilters = function() {
  let $this = this;
  filters_helpers.formData(external_DoMini_namespaceObject("form", $this.n("searchsettings")), $this.originalFormData);
  $this.resetNoUISliderFilters();
  if (typeof $this.select2jQuery != "undefined") {
    $this.select2jQuery($this.n("searchsettings").get(0)).find(".asp_gochosen,.asp_goselect2").trigger("change.asp_select2");
  }
  $this.n("text").val("");
  $this.n("proloading").css("display", "none");
  $this.hideLoader();
  $this.searchAbort();
  $this.setFilterStateInput(0);
};
base.plugin.resetNoUISliderFilters = function() {
  if (this.noUiSliders.length > 0) {
    this.noUiSliders.forEach(function(slider) {
      if (typeof slider.noUiSlider != "undefined") {
        let vals = [];
        external_DoMini_namespaceObject(slider).parent().find(".asp_slider_hidden").forEach(function(el) {
          vals.push(external_DoMini_namespaceObject(el).val());
        });
        if (vals.length > 0) {
          slider.noUiSlider.set(vals);
        }
      }
    });
  }
};
/* harmony default export */ var filters = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/actions/loader.js



"use strict";
base.plugin.showMoreResLoader = function() {
  let $this = this;
  $this.n("resultsDiv").addClass("asp_more_res_loading");
};
base.plugin.showLoader = function(recall) {
  let $this = this;
  recall = typeof recall !== "undefined" ? recall : false;
  if ($this.o.loaderLocation === "none") return;
  if (!$this.n("search").hasClass("hiddend") && $this.o.loaderLocation !== "results") {
    $this.n("proloading").css({
      display: "block"
    });
  }
  if (recall !== false) {
    return false;
  }
  if ($this.n("search").hasClass("hiddend") && $this.o.loaderLocation !== "search" || !$this.n("search").hasClass("hiddend") && ($this.o.loaderLocation === "both" || $this.o.loaderLocation === "results")) {
    if (!$this.usingLiveLoader()) {
      if ($this.n("resultsDiv").find(".asp_results_top").length > 0)
        $this.n("resultsDiv").find(".asp_results_top").css("display", "none");
      $this.showResultsBox();
      external_DoMini_namespaceObject(".asp_res_loader", $this.n("resultsDiv")).removeClass("hiddend");
      $this.n("results").css("display", "none");
      $this.n("showmoreContainer").css("display", "none");
      if (typeof $this.hidePagination !== "undefined") {
        $this.hidePagination();
      }
    }
  }
};
base.plugin.hideLoader = function() {
  let $this = this;
  $this.n("proloading").css({
    display: "none"
  });
  external_DoMini_namespaceObject(".asp_res_loader", $this.n("resultsDiv")).addClass("hiddend");
  $this.n("results").css("display", "");
  $this.n("resultsDiv").removeClass("asp_more_res_loading");
};
/* harmony default export */ var loader = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/actions/other.js



"use strict";
base.plugin.loadASPFonts = function() {
  if (ASP.font_url !== false) {
    let font = new FontFace(
      "asppsicons2",
      "url(" + ASP.font_url + ")",
      { style: "normal", weight: "normal", display: "swap" }
    );
    font.load().then(function(loaded_face) {
      document.fonts.add(loaded_face);
    }).catch(function(er) {
    });
    ASP.font_url = false;
  }
};
base.plugin.updateHref = function(anchor) {
  anchor = anchor || window.location.hash;
  if (this.o.trigger.update_href && !this.usingLiveLoader()) {
    if (!window.location.origin) {
      window.location.origin = window.location.protocol + "//" + window.location.hostname + (window.location.port ? ":" + window.location.port : "");
    }
    let url = this.getStateURL() + (this.resultsOpened ? "&asp_s=" : "&asp_ls=") + this.n("text").val() + anchor;
    history.replaceState("", "", url.replace(location.origin, ""));
  }
};
base.plugin.stat_addKeyword = function(id, keyword) {
  let data = {
    action: "ajaxsearchpro_addkeyword",
    id,
    keyword
  };
  external_DoMini_namespaceObject.fn.ajax({
    "url": ASP.ajaxurl,
    "method": "POST",
    "data": data,
    "success": function(response) {
    }
  });
};
base.plugin.fixClonedSelf = function() {
  let $this = this, old_instance_id = String($this.o.iid), old_real_id = String($this.o.rid);
  while (!ASP.instances.set($this)) {
    ++$this.o.iid;
    if ($this.o.iid > 50) {
      break;
    }
  }
  if (old_instance_id !== $this.o.iid) {
    $this.o.rid = $this.o.id + "_" + $this.o.iid;
    $this.n("search").get(0).id = "ajaxsearchpro" + $this.o.rid;
    $this.n("search").removeClass("asp_m_" + old_real_id).addClass("asp_m_" + $this.o.rid).data("instance", $this.o.iid);
    $this.n("searchsettings").get(0).id = $this.n("searchsettings").get(0).id.replace("settings" + old_real_id, "settings" + $this.o.rid);
    if ($this.n("searchsettings").hasClass("asp_s_" + old_real_id)) {
      $this.n("searchsettings").removeClass("asp_s_" + old_real_id).addClass("asp_s_" + $this.o.rid).data("instance", $this.o.iid);
    } else {
      $this.n("searchsettings").removeClass("asp_sb_" + old_real_id).addClass("asp_sb_" + $this.o.rid).data("instance", $this.o.iid);
    }
    $this.n("resultsDiv").get(0).id = $this.n("resultsDiv").get(0).id.replace("prores" + old_real_id, "prores" + $this.o.rid);
    $this.n("resultsDiv").removeClass("asp_r_" + old_real_id).addClass("asp_r_" + $this.o.rid).data("instance", $this.o.iid);
    $this.n("container").find(".asp_init_data").data("instance", $this.o.iid);
    $this.n("container").find(".asp_init_data").get(0).id = $this.n("container").find(".asp_init_data").get(0).id.replace("asp_init_id_" + old_real_id, "asp_init_id_" + $this.o.rid);
    $this.n("prosettings").data("opened", 0);
  }
};
base.plugin.destroy = function() {
  let $this = this;
  Object.keys($this.nodes).forEach(function(k) {
    $this.nodes[k].off?.();
  });
  if (typeof $this.n("searchsettings").get(0).referenced !== "undefined") {
    --$this.n("searchsettings").get(0).referenced;
    if ($this.n("searchsettings").get(0).referenced < 0) {
      $this.n("searchsettings").remove();
    }
  } else {
    $this.n("searchsettings").remove();
  }
  if (typeof $this.n("resultsDiv").get(0).referenced !== "undefined") {
    --$this.n("resultsDiv").get(0).referenced;
    if ($this.n("resultsDiv").get(0).referenced < 0) {
      $this.n("resultsDiv").remove?.();
    }
  } else {
    $this.n("resultsDiv").remove?.();
  }
  $this.n("trythis").remove?.();
  $this.n("search").remove?.();
  $this.n("container").remove?.();
  $this.documentEventHandlers.forEach(function(h) {
    external_DoMini_namespaceObject(h.node).off(h.event, h.handler);
  });
};
/* harmony default export */ var other = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/actions/redirect.js



"use strict";
let redirect_helpers = base.helpers;
base.plugin.isRedirectToFirstResult = function() {
  let $this = this;
  return (external_DoMini_namespaceObject(".asp_res_url", $this.n("resultsDiv")).length > 0 || external_DoMini_namespaceObject(".asp_es_" + $this.o.id + " a").length > 0 || $this.o.resPage.useAjax && external_DoMini_namespaceObject($this.o.resPage.selector + "a").length > 0) && ($this.o.redirectOnClick && $this.ktype === "click" && $this.o.trigger.click === "first_result" || $this.o.redirectOnEnter && ($this.ktype === "input" || $this.ktype === "keyup") && $this.keycode === 13 && $this.o.trigger.return === "first_result" || $this.ktype === "button" && $this.o.sb.redirect_action === "first_result");
};
base.plugin.doRedirectToFirstResult = function() {
  let $this = this, _loc, url;
  if ($this.ktype === "click") {
    _loc = $this.o.trigger.click_location;
  } else if ($this.ktype === "button") {
    _loc = $this.o.sb.redirect_location;
  } else {
    _loc = $this.o.trigger.return_location;
  }
  if (external_DoMini_namespaceObject(".asp_res_url", $this.n("resultsDiv")).length > 0) {
    url = external_DoMini_namespaceObject(external_DoMini_namespaceObject(".asp_res_url", $this.n("resultsDiv")).get(0)).attr("href");
  } else if (external_DoMini_namespaceObject(".asp_es_" + $this.o.id + " a").length > 0) {
    url = external_DoMini_namespaceObject(external_DoMini_namespaceObject(".asp_es_" + $this.o.id + " a").get(0)).attr("href");
  } else if ($this.o.resPage.useAjax && external_DoMini_namespaceObject($this.o.resPage.selector + "a").length > 0) {
    url = external_DoMini_namespaceObject(external_DoMini_namespaceObject($this.o.resPage.selector + "a").get(0)).attr("href");
  }
  if (url !== "") {
    if (_loc === "same") {
      location.href = url;
    } else {
      redirect_helpers.openInNewTab(url);
    }
    $this.hideLoader();
    $this.hideResults();
  }
  return false;
};
base.plugin.doRedirectToResults = function(ktype) {
  let $this = this, _loc;
  if (typeof $this.reportSettingsValidity != "undefined" && !$this.reportSettingsValidity()) {
    $this.showNextInvalidFacetMessage?.();
    return false;
  }
  if (ktype === "click") {
    _loc = $this.o.trigger.click_location;
  } else if (ktype === "button") {
    _loc = $this.o.sb.redirect_location;
  } else {
    _loc = $this.o.trigger.return_location;
  }
  let url = $this.getRedirectURL(ktype);
  if ($this.o.overridewpdefault) {
    if ($this.o.resPage.useAjax) {
      $this.hideResults();
      $this.liveLoad($this.o.resPage.selector, url);
      $this.showLoader();
      if ($this.att("blocking") === false) {
        $this.hideSettings?.();
      }
      return false;
    }
    if ($this.o.override_method === "post") {
      redirect_helpers.submitToUrl(url, "post", {
        asp_active: 1,
        p_asid: $this.o.id,
        p_asp_data: external_DoMini_namespaceObject("form", $this.n("searchsettings")).serialize()
      }, _loc);
    } else {
      if (_loc === "same") {
        location.href = url;
      } else {
        redirect_helpers.openInNewTab(url);
      }
    }
  } else {
    redirect_helpers.submitToUrl(url, "post", {
      np_asid: $this.o.id,
      np_asp_data: external_DoMini_namespaceObject("form", $this.n("searchsettings")).serialize()
    }, _loc);
  }
  $this.n("proloading").css("display", "none");
  $this.hideLoader();
  if ($this.att("blocking") === false) $this.hideSettings?.();
  $this.hideResults();
  $this.searchAbort();
};
base.plugin.getRedirectURL = function(ktype) {
  let $this = this, url, source, final, base_url;
  ktype = typeof ktype !== "undefined" ? ktype : "enter";
  if (ktype === "click") {
    source = $this.o.trigger.click;
  } else if (ktype === "button") {
    source = $this.o.sb.redirect_action;
  } else {
    source = $this.o.trigger.return;
  }
  if (source === "results_page") {
    url = "?s=" + redirect_helpers.nicePhrase($this.n("text").val());
  } else if (source === "woo_results_page") {
    url = "?post_type=product&s=" + redirect_helpers.nicePhrase($this.n("text").val());
  } else {
    if (ktype === "button") {
      base_url = source === "elementor_page" ? $this.o.sb.elementor_url : $this.o.sb.redirect_url;
      base_url = redirect_helpers.decodeHTMLEntities(base_url);
      url = $this.parseCustomRedirectURL(base_url, $this.n("text").val());
    } else {
      base_url = source === "elementor_page" ? $this.o.trigger.elementor_url : $this.o.trigger.redirect_url;
      base_url = redirect_helpers.decodeHTMLEntities(base_url);
      url = $this.parseCustomRedirectURL(base_url, $this.n("text").val());
    }
  }
  if ($this.o.homeurl.indexOf("?") > 1 && url.indexOf("?") === 0) {
    url = url.replace("?", "&");
  }
  if ($this.o.overridewpdefault && $this.o.override_method !== "post") {
    let start = "&";
    if (($this.o.homeurl.indexOf("?") === -1 || source === "elementor_page") && url.indexOf("?") === -1) {
      start = "?";
    }
    let addUrl = url + start + "asp_active=1&p_asid=" + $this.o.id + "&p_asp_data=1&" + external_DoMini_namespaceObject("form", $this.n("searchsettings")).serialize();
    if (source === "elementor_page") {
      final = addUrl;
    } else {
      final = $this.o.homeurl + addUrl;
    }
  } else {
    if (source === "elementor_page") {
      final = url;
    } else {
      final = $this.o.homeurl + url;
    }
  }
  final = final.replace("https://", "https:///");
  final = final.replace("http://", "http:///");
  final = final.replace(/\/\//g, "/");
  return redirect_helpers.Hooks.applyFilters("asp_redirect_url", final, $this.o.id, $this.o.iid);
};
base.plugin.parseCustomRedirectURL = function(url, phrase) {
  let $this = this, u = redirect_helpers.decodeHTMLEntities(url).replace(/{phrase}/g, redirect_helpers.nicePhrase(phrase)), items = u.match(/{(.*?)}/g);
  if (items !== null) {
    items.forEach(function(v) {
      v = v.replace(/[{}]/g, "");
      let node = external_DoMini_namespaceObject('input[type=radio][name*="aspf[' + v + '_"]:checked', $this.n("searchsettings"));
      if (node.length === 0)
        node = external_DoMini_namespaceObject('input[type=text][name*="aspf[' + v + '_"]', $this.n("searchsettings"));
      if (node.length === 0)
        node = external_DoMini_namespaceObject('input[type=hidden][name*="aspf[' + v + '_"]', $this.n("searchsettings"));
      if (node.length === 0)
        node = external_DoMini_namespaceObject('select[name*="aspf[' + v + '_"]:not([multiple])', $this.n("searchsettings"));
      if (node.length === 0)
        node = external_DoMini_namespaceObject('input[type=radio][name*="termset[' + v + '"]:checked', $this.n("searchsettings"));
      if (node.length === 0)
        node = external_DoMini_namespaceObject('input[type=text][name*="termset[' + v + '"]', $this.n("searchsettings"));
      if (node.length === 0)
        node = external_DoMini_namespaceObject('input[type=hidden][name*="termset[' + v + '"]', $this.n("searchsettings"));
      if (node.length === 0)
        node = external_DoMini_namespaceObject('select[name*="termset[' + v + '"]:not([multiple])', $this.n("searchsettings"));
      if (node.length === 0)
        return true;
      let val = node.val();
      val = "" + val;
      u = u.replace("{" + v + "}", val);
    });
  }
  return u;
};
/* harmony default export */ var redirect = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/actions/results.js



"use strict";
let results_helpers = base.helpers;
base.plugin.showResults = function() {
  let $this = this;
  results_helpers.Hooks.applyFilters("asp/results/show/start", $this);
  $this.initResults();
  if ($this.o.resultstype === "horizontal") {
    $this.createHorizontalScroll();
  } else {
    if ($this.o.resultstype === "vertical") {
      $this.createVerticalScroll();
    }
  }
  switch ($this.o.resultstype) {
    case "horizontal":
      $this.showHorizontalResults();
      break;
    case "vertical":
      $this.showVerticalResults();
      break;
    case "polaroid":
      $this.showPolaroidResults();
      break;
    case "isotopic":
      $this.showIsotopicResults();
      break;
    default:
      $this.showHorizontalResults();
      break;
  }
  $this.showAnimatedImages();
  $this.hideLoader();
  $this.n("proclose").css({
    display: "block"
  });
  if (results_helpers.isMobile() && $this.o.mobile.hide_keyboard && !$this.resultsOpened)
    document.activeElement.blur();
  if ($this.o.settingsHideOnRes && $this.att("blocking") === false)
    $this.hideSettings?.();
  $this.eh.resulsDivHoverMouseEnter = $this.eh.resulsDivHoverMouseEnter || function() {
    external_DoMini_namespaceObject(".item", $this.n("resultsDiv")).removeClass("hovered");
    external_DoMini_namespaceObject(this).addClass("hovered");
  };
  $this.eh.resulsDivHoverMouseLeave = $this.eh.resulsDivHoverMouseLeave || function() {
    external_DoMini_namespaceObject(".item", $this.n("resultsDiv")).removeClass("hovered");
  };
  $this.n("resultsDiv").find(".item").on("mouseenter", $this.eh.resulsDivHoverMouseEnter);
  $this.n("resultsDiv").find(".item").on("mouseleave", $this.eh.resulsDivHoverMouseLeave);
  $this.fixSettingsAccessibility();
  $this.resultsOpened = true;
  results_helpers.Hooks.addFilter("asp/results/show/end", $this);
};
base.plugin.hideResults = function(blur) {
  let $this = this;
  blur = typeof blur == "undefined" ? true : blur;
  $this.initResults();
  if (!$this.resultsOpened) return false;
  $this.n("resultsDiv").removeClass($this.resAnim.showClass).addClass($this.resAnim.hideClass);
  setTimeout(function() {
    $this.n("resultsDiv").css($this.resAnim.hideCSS);
  }, $this.resAnim.duration);
  $this.n("proclose").css({
    display: "none"
  });
  if (results_helpers.isMobile() && blur)
    document.activeElement.blur();
  $this.resultsOpened = false;
  if (typeof $this.ptstack != "undefined")
    delete $this.ptstack;
  $this.hideArrowBox?.();
  $this.n("s").trigger("asp_results_hide", [$this.o.id, $this.o.iid], true, true);
};
base.plugin.updateResults = function(html) {
  let $this = this;
  if (html.replace(/^\s*[\r\n]/gm, "") === "" || external_DoMini_namespaceObject(html).hasClass("asp_nores") || external_DoMini_namespaceObject(html).find(".asp_nores").length > 0) {
    $this.n("showmoreContainer").css("display", "none");
    external_DoMini_namespaceObject("span", $this.n("showmore")).html("");
  } else {
    if ($this.o.resultstype === "isotopic" && $this.call_num > 0 && $this.isotopic != null && typeof $this.isotopic.appended != "undefined" && $this.n("items").length > 0) {
      let $items = external_DoMini_namespaceObject(html), $last = $this.n("items").last(), last = parseInt($this.n("items").last().attr("data-itemnum"));
      $items.get().forEach(function(el) {
        external_DoMini_namespaceObject(el).attr("data-itemnum", ++last).css({
          "width": $last.css("width"),
          "height": $last.css("height")
        });
      });
      $this.n("resdrg").append($items);
      $this.isotopic.appended($items.get());
      $this.nodes.items = external_DoMini_namespaceObject(".item", $this.n("resultsDiv")).length > 0 ? external_DoMini_namespaceObject(".item", $this.n("resultsDiv")) : external_DoMini_namespaceObject(".photostack-flip", $this.n("resultsDiv"));
    } else {
      if ($this.call_num > 0 && $this.o.resultstype === "vertical") {
        $this.n("resdrg").html($this.n("resdrg").html() + '<div class="asp_v_spacer"></div>' + html);
      } else {
        $this.n("resdrg").html($this.n("resdrg").html() + html);
      }
    }
  }
};
base.plugin.showResultsBox = function() {
  let $this = this;
  $this.initResults();
  $this.n("s").trigger("asp_results_show", [$this.o.id, $this.o.iid], true, true);
  $this.n("resultsDiv").css({
    display: "block",
    height: "auto"
  });
  $this.n("results").find(".item, .asp_group_header").addClass($this.animationOpacity);
  $this.n("resultsDiv").css($this.resAnim.showCSS);
  $this.n("resultsDiv").removeClass($this.resAnim.hideClass).addClass($this.resAnim.showClass);
  $this.fixResultsPosition(true);
};
base.plugin.addHighlightString = function($items) {
  let $this = this, phrase = $this.n("text").val().replace(/["']/g, "");
  $items = typeof $items == "undefined" ? $this.n("items").find("a.asp_res_url") : $items;
  if ($this.o.singleHighlight && phrase !== "" && $items.length > 0) {
    $items.forEach(function() {
      try {
        const url = new URL(external_DoMini_namespaceObject(this).attr("href"));
        url.searchParams.set("asp_highlight", phrase);
        url.searchParams.set("p_asid", $this.o.id);
        external_DoMini_namespaceObject(this).attr("href", url.href);
      } catch (e) {
      }
    });
  }
};
base.plugin.scrollToResults = function() {
  let $this = this, tolerance = Math.floor(window.innerHeight * 0.1), stop;
  if (!$this.resultsOpened || $this.call_num > 0 || !$this.o.scrollToResults.enabled || $this.n("search").closest(".asp_preview_data").length > 0 || $this.o.compact.enabled || $this.n("resultsDiv").inViewPort(tolerance)) return;
  if ($this.o.resultsposition === "hover") {
    stop = $this.n("probox").offset().top - 20;
  } else {
    stop = $this.n("resultsDiv").offset().top - 20;
  }
  stop = stop + $this.o.scrollToResults.offset;
  let $adminbar = external_DoMini_namespaceObject("#wpadminbar");
  if ($adminbar.length > 0)
    stop -= $adminbar.height();
  stop = stop < 0 ? 0 : stop;
  window.scrollTo({ top: stop, behavior: "smooth" });
};
base.plugin.scrollToResult = function(id) {
  let $el = external_DoMini_namespaceObject(id);
  if ($el.length && !$el.inViewPort(40)) {
    $el.get(0).scrollIntoView({ behavior: "smooth", block: "center", inline: "nearest" });
  }
};
base.plugin.showAnimatedImages = function() {
  let $this = this;
  $this.n("items").forEach(function() {
    let $image = external_DoMini_namespaceObject(this).find(".asp_image[data-src]"), src = $image.data("src");
    if (typeof src != "undefined" && src != null && src !== "" && src.indexOf(".gif") > -1) {
      if ($image.find("canvas").length === 0) {
        $image.prepend(external_DoMini_namespaceObject('<div class="asp_item_canvas"><canvas></canvas></div>').get(0));
        let c = external_DoMini_namespaceObject(this).find("canvas").get(0), $cc = external_DoMini_namespaceObject(this).find(".asp_item_canvas"), ctx = c.getContext("2d"), img = new Image();
        img.crossOrigin = "anonymous";
        img.onload = function() {
          external_DoMini_namespaceObject(c).attr({
            "width": img.width,
            "height": img.height
          });
          ctx.drawImage(img, 0, 0, img.width, img.height);
          $cc.css({
            "background-image": "url(" + c.toDataURL() + ")"
          });
        };
        img.src = src;
      }
    }
  });
};
base.plugin.updateNoResultsHeader = function() {
  let $this = this, $new_nores = $this.n("resdrg").find(".asp_nores"), $old_nores;
  if ($new_nores.length > 0) {
    $new_nores = $new_nores.detach();
  }
  $old_nores = $this.n("resultsDiv").find(".asp_nores");
  if ($old_nores.length > 0) {
    $old_nores.remove();
  }
  if ($new_nores.length > 0) {
    $this.n("resultsDiv").prepend($new_nores);
    $this.n("resultsDiv").find(".asp_keyword").on("click", function() {
      $this.n("text").val(results_helpers.decodeHTMLEntities(external_DoMini_namespaceObject(this).text()));
      $this.n("textAutocomplete").val("");
      if (!$this.o.redirectOnClick || !$this.o.redirectOnEnter || $this.o.trigger.type) {
        $this.search();
      }
    });
  }
};
base.plugin.updateInfoHeader = function(totalCount) {
  let $this = this, content = "", $rt = $this.n("resultsDiv").find(".asp_results_top"), phrase = $this.n("text").val().trim();
  if ($rt.length > 0) {
    if ($this.n("items").length <= 0 || $this.n("resultsDiv").find(".asp_nores").length > 0) {
      $rt.css("display", "none");
    } else {
      if (typeof $this.updateInfoHeader.resInfoBoxTxt == "undefined") {
        $this.updateInfoHeader.resInfoBoxTxt = $this.n("resultsDiv").find(".asp_results_top .asp_rt_phrase").length > 0 ? $this.n("resultsDiv").find(".asp_results_top .asp_rt_phrase").html() : "";
        $this.updateInfoHeader.resInfoBoxTxtNoPhrase = $this.n("resultsDiv").find(".asp_results_top .asp_rt_nophrase").length > 0 ? $this.n("resultsDiv").find(".asp_results_top .asp_rt_nophrase").html() : "";
      }
      if (phrase !== "" && $this.updateInfoHeader.resInfoBoxTxt !== "") {
        content = $this.updateInfoHeader.resInfoBoxTxt;
      } else if (phrase === "" && $this.updateInfoHeader.resInfoBoxTxtNoPhrase !== "") {
        content = $this.updateInfoHeader.resInfoBoxTxtNoPhrase;
      }
      if (content === void 0) {
        return;
      }
      if (content !== "") {
        content = content.replaceAll("{phrase}", results_helpers.escapeHtml($this.n("text").val()));
        content = content.replaceAll("{results_count}", $this.n("items").length);
        content = content.replaceAll("{results_count_total}", totalCount);
        $rt.html(content);
        $rt.css("display", "block");
      } else {
        $rt.css("display", "none");
      }
    }
  }
};

;// CONCATENATED MODULE: ./js/src/plugin/core/actions/scroll.js



"use strict";
let scroll_helpers = base.helpers;
base.plugin.createResultsScroll = function(type) {
  let $this = this, t, $resScroll = $this.n("results");
  type = typeof type == "undefined" ? "vertical" : type;
  $resScroll.on("scroll", function() {
    if ($this.o.show_more.infinite) {
      clearTimeout(t);
      t = setTimeout(function() {
        $this.checkAndTriggerInfiniteScroll(type);
      }, 60);
    }
  });
};
base.plugin.createVerticalScroll = function() {
  this.createResultsScroll("vertical");
};
base.plugin.createHorizontalScroll = function() {
  this.createResultsScroll("horizontal");
};
base.plugin.checkAndTriggerInfiniteScroll = function(caller) {
  let $this = this, $r = external_DoMini_namespaceObject(".item", $this.n("resultsDiv"));
  caller = typeof caller == "undefined" ? "window" : caller;
  if ($this.n("showmore").length === 0 || $this.n("showmoreContainer").css("display") === "none") {
    return false;
  }
  if (caller === "window" || caller === "horizontal") {
    if ($this.o.resultstype === "isotopic" && external_DoMini_namespaceObject("nav.asp_navigation", $this.n("resultsDiv")).css("display") !== "none") {
      return false;
    }
    let onViewPort = $r.last().inViewPort(0, $this.n("resultsDiv").get(0)), onScreen = $r.last().inViewPort(0);
    if (!$this.searching && $r.length > 0 && onViewPort && onScreen) {
      $this.n("showmore").find("a.asp_showmore").trigger("click");
    }
  } else if (caller === "vertical") {
    let $scrollable = $this.n("results");
    if (scroll_helpers.isScrolledToBottom($scrollable.get(0), 20)) {
      $this.n("showmore").find("a.asp_showmore").trigger("click");
    }
  } else if (caller === "isotopic") {
    if (!$this.searching && $r.length > 0 && $this.n("resultsDiv").find("nav.asp_navigation ul li").last().hasClass("asp_active")) {
      $this.n("showmore").find("a.asp_showmore").trigger("click");
    }
  }
};

;// CONCATENATED MODULE: ./js/src/plugin/core/actions/search.js



"use strict";
let search_helpers = base.helpers;
base.plugin.isDuplicateSearchTriggered = function() {
  let $this = this;
  for (let i = 0; i < 25; i++) {
    let id = $this.o.id + "_" + i;
    if (id !== $this.o.rid) {
      if (window.ASP.instances.get($this.o.id, i) !== false) {
        return window.ASP.instances.get($this.o.id, i).searching;
      }
    }
  }
  return false;
};
base.plugin.searchAbort = function() {
  let $this = this;
  if ($this.post != null) {
    $this.post.abort();
    $this.isAutoP = false;
  }
};
base.plugin.searchWithCheck = function(timeout) {
  let $this = this;
  if (typeof timeout == "undefined")
    timeout = 50;
  if ($this.n("text").val().length < $this.o.charcount) return;
  $this.searchAbort();
  clearTimeout($this.timeouts.searchWithCheck);
  $this.timeouts.searchWithCheck = setTimeout(function() {
    $this.search();
  }, timeout);
};
base.plugin.search = function(count, order, recall, apiCall, supressInvalidMsg) {
  let $this = this, abort = false;
  if ($this.isDuplicateSearchTriggered())
    return false;
  recall = typeof recall == "undefined" ? false : recall;
  apiCall = typeof apiCall == "undefined" ? false : apiCall;
  supressInvalidMsg = typeof supressInvalidMsg == "undefined" ? false : supressInvalidMsg;
  let data = {
    action: "ajaxsearchpro_search",
    aspp: $this.n("text").val(),
    asid: $this.o.id,
    asp_inst_id: $this.o.rid,
    options: external_DoMini_namespaceObject("form", $this.n("searchsettings")).serialize()
  };
  data = search_helpers.Hooks.applyFilters("asp_search_data", data, $this.o.id, $this.o.iid);
  $this.hideArrowBox?.();
  if (typeof $this.reportSettingsValidity != "undefined" && !$this.isAutoP && !$this.reportSettingsValidity()) {
    if (!supressInvalidMsg) {
      $this.showNextInvalidFacetMessage?.();
      $this.scrollToNextInvalidFacetMessage?.();
    }
    abort = true;
  }
  if ($this.isAutoP) {
    data.autop = 1;
  }
  if (!recall && !apiCall && JSON.stringify(data) === JSON.stringify($this.lastSearchData)) {
    if (!$this.resultsOpened && !$this.usingLiveLoader()) {
      $this.showResults();
    }
    if ($this.isRedirectToFirstResult()) {
      $this.doRedirectToFirstResult();
      return false;
    }
    abort = true;
  }
  if (abort) {
    $this.hideLoader();
    $this.searchAbort();
    return false;
  }
  $this.n("s").trigger("asp_search_start", [$this.o.id, $this.o.iid, $this.n("text").val()], true, true);
  $this.searching = true;
  $this.n("proclose").css({
    display: "none"
  });
  $this.showLoader(recall);
  if (!$this.att("blocking") && !$this.o.trigger.facet) $this.hideSettings?.();
  if (recall) {
    $this.call_num++;
    data.asp_call_num = $this.call_num;
    if ($this.autopStartedTheSearch) {
      data.options += "&" + external_DoMini_namespaceObject.fn.serializeObject($this.autopData);
      --data.asp_call_num;
    }
  } else {
    $this.call_num = 0;
    $this.autopStartedTheSearch = !!data.autop;
  }
  let $form = external_DoMini_namespaceObject('form[name="asp_data"]');
  if ($form.length > 0) {
    data.asp_preview_options = $form.serialize();
  }
  if (typeof count != "undefined" && count !== false) {
    data.options += "&force_count=" + parseInt(count);
  }
  if (typeof order != "undefined" && order !== false) {
    data.options += "&force_order=" + parseInt(order);
  }
  $this.gaEvent?.("search_start");
  if (external_DoMini_namespaceObject(".asp_es_" + $this.o.id).length > 0) {
    $this.liveLoad(".asp_es_" + $this.o.id, $this.getCurrentLiveURL(), $this.o.trigger.update_href);
  } else if ($this.o.resPage.useAjax) {
    $this.liveLoad($this.o.resPage.selector, $this.getRedirectURL());
  } else if ($this.o.wooShop.useAjax) {
    $this.liveLoad($this.o.wooShop.selector, $this.getLiveURLbyBaseLocation($this.o.wooShop.url));
  } else if ($this.o.taxArchive.useAjax) {
    $this.liveLoad($this.o.taxArchive.selector, $this.getLiveURLbyBaseLocation($this.o.taxArchive.url));
  } else if ($this.o.cptArchive.useAjax) {
    $this.liveLoad($this.o.cptArchive.selector, $this.getLiveURLbyBaseLocation($this.o.cptArchive.url));
  } else {
    $this.post = external_DoMini_namespaceObject.fn.ajax({
      "url": window.ASP.ajaxurl,
      "method": "POST",
      "data": data,
      "success": function(response) {
        $this.searching = false;
        response = response.replace(/^\s*[\r\n]/gm, "");
        let html_response = response.match(/___ASPSTART_HTML___(.*[\s\S]*)___ASPEND_HTML___/), data_response = response.match(/___ASPSTART_DATA___(.*[\s\S]*)___ASPEND_DATA___/);
        if (html_response == null || typeof html_response != "object" || typeof html_response[1] == "undefined") {
          $this.hideLoader();
          alert('Ajax Search Pro Error:\r\n\r\nPlease look up "The response data is missing" from the documentation at\r\n\r\n documentation.ajaxsearchpro.com');
          return false;
        } else {
          html_response = html_response[1];
          html_response = search_helpers.Hooks.applyFilters("asp_search_html", html_response, $this.o.id, $this.o.iid);
        }
        data_response = JSON.parse(data_response[1]);
        $this.n("s").trigger("asp_search_end", [$this.o.id, $this.o.iid, $this.n("text").val(), data_response], true, true);
        if ($this.autopStartedTheSearch) {
          if (typeof data.autop != "undefined") {
            $this.autopData["not_in"] = {};
            $this.autopData["not_in_count"] = 0;
            if (typeof data_response.results != "undefined") {
              let res = [];
              if (typeof data_response.results.groups != "undefined") {
                Object.keys(data_response.results.groups).forEach(function(k) {
                  if (typeof data_response.results.groups[k].items != "undefined") {
                    let group = data_response.results.groups[k].items;
                    if (Array.isArray(group)) {
                      group.forEach(function(result) {
                        res.push(result);
                      });
                    }
                  }
                });
              } else {
                res = Array.isArray(data_response.results) ? data_response.results : res;
              }
              res.forEach(function(r) {
                if (typeof $this.autopData["not_in"][r["content_type"]] == "undefined") {
                  $this.autopData["not_in"][r["content_type"]] = [];
                }
                $this.autopData["not_in"][r["content_type"]].push(r["id"]);
                ++$this.autopData["not_in_count"];
              });
            }
          } else {
            data_response.full_results_count += $this.autopData["not_in_count"];
          }
        }
        if (!recall) {
          $this.initResults();
          $this.n("resdrg").html("");
          $this.n("resdrg").html(html_response);
          $this.results_num = data_response.results_count;
          if ($this.o.statistics)
            $this.stat_addKeyword($this.o.id, $this.n("text").val());
        } else {
          $this.updateResults(html_response);
          $this.results_num += data_response.results_count;
        }
        $this.updateNoResultsHeader();
        $this.nodes.items = external_DoMini_namespaceObject(".item", $this.n("resultsDiv")).length > 0 ? external_DoMini_namespaceObject(".item", $this.n("resultsDiv")) : external_DoMini_namespaceObject(".photostack-flip", $this.n("resultsDiv"));
        $this.addHighlightString();
        $this.gaEvent?.("search_end", { "results_count": $this.n("items").length });
        if ($this.isRedirectToFirstResult()) {
          $this.doRedirectToFirstResult();
          return false;
        }
        $this.hideLoader();
        $this.showResults();
        if (window.location.hash !== "" && window.location.hash.indexOf("#asp-res-") > -1 && external_DoMini_namespaceObject(window.location.hash).length > 0) {
          $this.scrollToResult(window.location.hash);
        } else {
          $this.scrollToResults();
        }
        $this.lastSuccesfulSearch = external_DoMini_namespaceObject("form", $this.n("searchsettings")).serialize() + $this.n("text").val().trim();
        $this.lastSearchData = data;
        $this.updateInfoHeader(data_response.full_results_count);
        $this.updateHref();
        if ($this.n("showmore").length > 0) {
          if (external_DoMini_namespaceObject("span", $this.n("showmore")).length > 0 && data_response.results_count > 0 && data_response.full_results_count - $this.results_num > 0) {
            if ($this.n("showmore").data("text") === "") {
              $this.n("showmore").data("text", $this.n("showmore").html());
            }
            $this.n("showmore").html($this.n("showmore").data("text").replaceAll("{phrase}", search_helpers.escapeHtml($this.n("text").val())));
            $this.n("showmoreContainer").css("display", "block");
            $this.n("showmore").css("display", "block");
            external_DoMini_namespaceObject("span", $this.n("showmore")).html("(" + (data_response.full_results_count - $this.results_num) + ")");
            let $a = external_DoMini_namespaceObject("a", $this.n("showmore"));
            $a.attr("href", "");
            $a.off();
            $a.on($this.clickTouchend, function(e) {
              e.preventDefault();
              e.stopImmediatePropagation();
              if ($this.o.show_more.action === "ajax") {
                if ($this.searching)
                  return false;
                $this.showMoreResLoader();
                $this.search(false, false, true);
              } else {
                let url, base_url;
                external_DoMini_namespaceObject(this).off();
                if ($this.o.show_more.action === "results_page") {
                  url = "?s=" + search_helpers.nicePhrase($this.n("text").val());
                } else if ($this.o.show_more.action === "woo_results_page") {
                  url = "?post_type=product&s=" + search_helpers.nicePhrase($this.n("text").val());
                } else {
                  if ($this.o.show_more.action === "elementor_page") {
                    url = $this.parseCustomRedirectURL($this.o.show_more.elementor_url, $this.n("text").val());
                  } else {
                    url = $this.parseCustomRedirectURL($this.o.show_more.url, $this.n("text").val());
                  }
                  url = external_DoMini_namespaceObject("<textarea />").html(url).text();
                }
                if ($this.o.show_more.action !== "elementor_page" && $this.o.homeurl.indexOf("?") > 1 && url.indexOf("?") === 0) {
                  url = url.replace("?", "&");
                }
                base_url = $this.o.show_more.action === "elementor_page" ? url : $this.o.homeurl + url;
                if ($this.o.overridewpdefault) {
                  if ($this.o.override_method === "post") {
                    search_helpers.submitToUrl(base_url, "post", {
                      asp_active: 1,
                      p_asid: $this.o.id,
                      p_asp_data: external_DoMini_namespaceObject("form", $this.n("searchsettings")).serialize()
                    }, $this.o.show_more.location);
                  } else {
                    let final = base_url + "&asp_active=1&p_asid=" + $this.o.id + "&p_asp_data=1&" + external_DoMini_namespaceObject("form", $this.n("searchsettings")).serialize();
                    if ($this.o.show_more.location === "same") {
                      location.href = final;
                    } else {
                      search_helpers.openInNewTab(final);
                    }
                  }
                } else {
                  search_helpers.submitToUrl(base_url, "post", {
                    np_asid: $this.o.id,
                    np_asp_data: external_DoMini_namespaceObject("form", $this.n("searchsettings")).serialize()
                  }, $this.o.show_more.location);
                }
              }
            });
          } else {
            $this.n("showmoreContainer").css("display", "none");
            external_DoMini_namespaceObject("span", $this.n("showmore")).html("");
          }
        }
        $this.isAutoP = false;
      },
      "fail": function(jqXHR) {
        if (jqXHR.aborted)
          return;
        $this.n("resdrg").html("");
        $this.n("resdrg").html('<div class="asp_nores">The request failed. Please check your connection! Status: ' + jqXHR.status + "</div>");
        $this.nodes.item = external_DoMini_namespaceObject(".item", $this.n("resultsDiv")).length > 0 ? external_DoMini_namespaceObject(".item", $this.n("resultsDiv")) : external_DoMini_namespaceObject(".photostack-flip", $this.n("resultsDiv"));
        $this.results_num = 0;
        $this.searching = false;
        $this.hideLoader();
        $this.showResults();
        $this.scrollToResults();
        $this.isAutoP = false;
      }
    });
  }
};
/* harmony default export */ var search = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/etc/api.js



"use strict";
let api_helpers = base.helpers;
base.plugin.searchFor = function(phrase) {
  if (typeof phrase != "undefined") {
    this.n("text").val(phrase);
  }
  this.n("textAutocomplete").val("");
  this.search(false, false, false, true);
};
base.plugin.searchRedirect = function(phrase) {
  let url = this.parseCustomRedirectURL(this.o.trigger.redirect_url, phrase);
  if (this.o.homeurl.indexOf("?") > 1 && url.indexOf("?") === 0) {
    url = url.replace("?", "&");
  }
  if (this.o.overridewpdefault) {
    if (this.o.override_method === "post") {
      api_helpers.submitToUrl(this.o.homeurl + url, "post", {
        asp_active: 1,
        p_asid: this.o.id,
        p_asp_data: external_DoMini_namespaceObject("form", this.n("searchsettings")).serialize()
      });
    } else {
      location.href = this.o.homeurl + url + "&asp_active=1&p_asid=" + this.o.id + "&p_asp_data=1&" + external_DoMini_namespaceObject("form", this.n("searchsettings")).serialize();
    }
  } else {
    api_helpers.submitToUrl(this.o.homeurl + url, "post", {
      np_asid: this.o.id,
      np_asp_data: external_DoMini_namespaceObject("form", this.n("searchsettings")).serialize()
    });
  }
};
base.plugin.toggleSettings = function(state) {
  if (typeof state != "undefined") {
    if (state === "show") {
      this.showSettings?.();
    } else {
      this.hideSettings?.();
    }
  } else {
    if (this.n("prosettings").data("opened") === "1") {
      this.hideSettings?.();
    } else {
      this.showSettings?.();
    }
  }
};
base.plugin.closeResults = function(clear) {
  if (typeof clear != "undefined" && clear) {
    this.n("text").val("");
    this.n("textAutocomplete").val("");
  }
  this.hideResults();
  this.n("proloading").css("display", "none");
  this.hideLoader();
  this.searchAbort();
};
base.plugin.getStateURL = function() {
  let url = location.href, sep;
  url = url.split("p_asid");
  url = url[0];
  url = url.replace("&asp_active=1", "");
  url = url.replace("?asp_active=1", "");
  url = url.slice(-1) === "?" ? url.slice(0, -1) : url;
  url = url.slice(-1) === "&" ? url.slice(0, -1) : url;
  sep = url.indexOf("?") > 1 ? "&" : "?";
  return url + sep + "p_asid=" + this.o.id + "&p_asp_data=1&" + external_DoMini_namespaceObject("form", this.n("searchsettings")).serialize();
};
base.plugin.resetSearch = function() {
  this.resetSearchFilters();
};
base.plugin.filtersInitial = function() {
  return this.n("searchsettings").find("input[name=filters_initial]").val() === "1";
};
base.plugin.filtersChanged = function() {
  return this.n("searchsettings").find("input[name=filters_changed]").val() === "1";
};
/* harmony default export */ var api = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/etc/position.js



"use strict";
let position_helpers = base.helpers;
base.plugin.detectAndFixFixedPositioning = function() {
  let $this = this, fixedp = false, n = $this.n("search").get(0);
  while (n) {
    n = n.parentElement;
    if (n != null && window.getComputedStyle(n).position === "fixed") {
      fixedp = true;
      break;
    }
  }
  if (fixedp || $this.n("search").css("position") === "fixed") {
    if ($this.n("resultsDiv").css("position") === "absolute") {
      $this.n("resultsDiv").css({
        "position": "fixed",
        "z-index": 2147483647
      });
    }
    if (!$this.att("blocking")) {
      $this.n("searchsettings").css({
        "position": "fixed",
        "z-index": 2147483647
      });
    }
  } else {
    if ($this.n("resultsDiv").css("position") === "fixed")
      $this.n("resultsDiv").css("position", "absolute");
    if (!$this.att("blocking"))
      $this.n("searchsettings").css("position", "absolute");
  }
};
base.plugin.fixSettingsAccessibility = function() {
  let $this = this;
  $this.n("searchsettings").find("input.asp_select2-search__field").attr("aria-label", "Select2 search");
};
base.plugin.fixTryThisPosition = function() {
  let $this = this;
  $this.n("trythis").css({
    left: $this.n("search").position().left
  });
};
base.plugin.fixResultsPosition = function(ignoreVisibility) {
  ignoreVisibility = typeof ignoreVisibility == "undefined" ? false : ignoreVisibility;
  let $this = this, $body = external_DoMini_namespaceObject("body"), bodyTop = 0, rpos = $this.n("resultsDiv").css("position");
  if (external_DoMini_namespaceObject._fn.bodyTransformY() !== 0 || $body.css("position") !== "static") {
    bodyTop = $body.offset().top;
  }
  if (external_DoMini_namespaceObject._fn.bodyTransformY() !== 0 && rpos === "fixed") {
    rpos = "absolute";
    $this.n("resultsDiv").css("position", "absolute");
  }
  if (rpos === "fixed") {
    bodyTop = 0;
  }
  if (rpos !== "fixed" && rpos !== "absolute") {
    return;
  }
  if (ignoreVisibility || $this.n("resultsDiv").css("visibility") === "visible") {
    let _rposition = $this.n("search").offset(), bodyLeft = 0;
    if (external_DoMini_namespaceObject._fn.bodyTransformX() !== 0 || $body.css("position") !== "static") {
      bodyLeft = $body.offset().left;
    }
    if (typeof _rposition != "undefined") {
      let vwidth, adjust = 0;
      if (position_helpers.deviceType() === "phone") {
        vwidth = $this.o.results.width_phone;
      } else if (position_helpers.deviceType() === "tablet") {
        vwidth = $this.o.results.width_tablet;
      } else {
        vwidth = $this.o.results.width;
      }
      if (vwidth === "auto") {
        vwidth = $this.n("search").outerWidth() < 240 ? 240 : $this.n("search").outerWidth();
      }
      $this.n("resultsDiv").css("width", !isNaN(vwidth) ? vwidth + "px" : vwidth);
      if ($this.o.resultsSnapTo === "right") {
        adjust = $this.n("resultsDiv").outerWidth() - $this.n("search").outerWidth();
      } else if ($this.o.resultsSnapTo === "center") {
        adjust = Math.floor(($this.n("resultsDiv").outerWidth() - parseInt($this.n("search").outerWidth())) / 2);
      }
      $this.n("resultsDiv").css({
        top: _rposition.top + $this.n("search").outerHeight(true) - bodyTop + "px",
        left: _rposition.left - adjust - bodyLeft + "px"
      });
    }
  }
};
base.plugin.fixSettingsPosition = function(ignoreVisibility) {
  ignoreVisibility = typeof ignoreVisibility == "undefined" ? false : ignoreVisibility;
  let $this = this, $body = external_DoMini_namespaceObject("body"), bodyTop = 0, settPos = $this.n("searchsettings").css("position");
  if (external_DoMini_namespaceObject._fn.bodyTransformY() !== 0 || $body.css("position") !== "static") {
    bodyTop = $body.offset().top;
  }
  if (external_DoMini_namespaceObject._fn.bodyTransformY() !== 0 && settPos === "fixed") {
    settPos = "absolute";
    $this.n("searchsettings").css("position", "absolute");
  }
  if (settPos === "fixed") {
    bodyTop = 0;
  }
  if ((ignoreVisibility || $this.n("prosettings").data("opened") === "1") && $this.att("blocking") !== true) {
    let $n, sPosition, top, left, bodyLeft = 0;
    if (external_DoMini_namespaceObject._fn.bodyTransformX() !== 0 || $body.css("position") !== "static") {
      bodyLeft = $body.offset().left;
    }
    $this.fixSettingsWidth();
    if ($this.n("prosettings").css("display") !== "none") {
      $n = $this.n("prosettings");
    } else {
      $n = $this.n("promagnifier");
    }
    sPosition = $n.offset();
    top = sPosition.top + $n.height() - 2 - bodyTop + "px";
    left = $this.o.settingsimagepos === "left" ? sPosition.left : sPosition.left + $n.width() - $this.n("searchsettings").width();
    left = left - bodyLeft + "px";
    $this.n("searchsettings").css({
      display: "block",
      top,
      left
    });
  }
};
base.plugin.fixSettingsWidth = function() {
  let $this = this;
  if ($this.att("blocking") || $this.o.fss_layout === "masonry") return;
  $this.n("searchsettings").css({ "width": "100%" });
  if ($this.n("searchsettings").width() % external_DoMini_namespaceObject("fieldset", $this.n("searchsettings")).outerWidth(true) > 10) {
    let newColumnCount = Math.floor($this.n("searchsettings").width() / external_DoMini_namespaceObject("fieldset", $this.n("searchsettings")).outerWidth(true));
    newColumnCount = newColumnCount <= 0 ? 1 : newColumnCount;
    $this.n("searchsettings").css({
      "width": newColumnCount * external_DoMini_namespaceObject("fieldset", $this.n("searchsettings")).outerWidth(true) + 8 + "px"
    });
  }
};
base.plugin.hideOnInvisibleBox = function() {
  let $this = this;
  if ($this.o.detectVisibility && !$this.o.compact.enabled && !$this.n("search").hasClass("hiddend") && !$this.n("search").isVisible()) {
    $this.hideSettings?.();
    $this.hideResults();
  }
};
/* harmony default export */ var position = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/events/button.js



"use strict";
let button_helpers = base.helpers;
base.plugin.initMagnifierEvents = function() {
  let $this = this, t;
  $this.n("promagnifier").on("click", function(e) {
    let compact = $this.n("search").attr("data-asp-compact") || "closed";
    $this.keycode = e.keyCode || e.which;
    $this.ktype = e.type;
    if ($this.o.compact.enabled) {
      if (compact === "closed" || $this.o.compact.closeOnMagnifier && compact === "open") {
        return false;
      }
    }
    $this.gaEvent?.("magnifier");
    if ($this.n("text").val().length >= $this.o.charcount && $this.o.redirectOnClick && $this.o.trigger.click !== "first_result") {
      $this.doRedirectToResults("click");
      clearTimeout(t);
      return false;
    }
    if (!($this.o.trigger.click === "ajax_search" || $this.o.trigger.click === "first_result")) {
      return false;
    }
    $this.searchAbort();
    clearTimeout($this.timeouts.search);
    $this.n("proloading").css("display", "none");
    if ($this.n("text").val().length >= $this.o.charcount) {
      $this.timeouts.search = setTimeout(function() {
        if (external_DoMini_namespaceObject("form", $this.n("searchsettings")).serialize() + $this.n("text").val().trim() !== $this.lastSuccesfulSearch || !$this.resultsOpened && !$this.usingLiveLoader()) {
          $this.search();
        } else {
          if ($this.isRedirectToFirstResult())
            $this.doRedirectToFirstResult();
          else
            $this.n("proclose").css("display", "block");
        }
      }, $this.o.trigger.delay);
    }
  });
};
base.plugin.initButtonEvents = function() {
  let $this = this;
  $this.n("searchsettings").find("button.asp_s_btn").on("click", function(e) {
    $this.ktype = "button";
    e.preventDefault();
    if ($this.n("text").val().length >= $this.o.charcount) {
      if ($this.o.sb.redirect_action !== "ajax_search") {
        if ($this.o.sb.redirect_action !== "first_result") {
          $this.doRedirectToResults("button");
        } else {
          if ($this.isRedirectToFirstResult()) {
            $this.doRedirectToFirstResult();
            return false;
          }
          $this.search();
        }
      } else {
        if (external_DoMini_namespaceObject("form", $this.n("searchsettings")).serialize() + $this.n("text").val().trim() !== $this.lastSuccesfulSearch || !$this.resultsOpened) {
          $this.search();
        }
      }
      clearTimeout($this.timeouts.search);
    }
  });
  $this.n("searchsettings").find("button.asp_r_btn").on("click", function(e) {
    let currentFormData = button_helpers.formData(external_DoMini_namespaceObject("form", $this.n("searchsettings"))), lastPhrase = $this.n("text").val();
    e.preventDefault();
    $this.resetSearchFilters();
    if ($this.o.rb.action === "live" && (JSON.stringify(currentFormData) !== JSON.stringify(button_helpers.formData(external_DoMini_namespaceObject("form", $this.n("searchsettings")))) || lastPhrase !== "")) {
      $this.search(false, false, false, true, true);
    } else {
      if ($this.o.rb.action === "close") {
        $this.hideResults();
      }
    }
  });
};
/* harmony default export */ var events_button = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/events/input.js



"use strict";
let input_helpers = base.helpers;
base.plugin.initInputEvents = function() {
  let $this = this, initialized = false;
  let initTriggers = function() {
    $this.n("text").off("mousedown touchstart keydown", initTriggers);
    if (!initialized) {
      $this._initFocusInput();
      if ($this.o.trigger.type) {
        $this._initSearchInput();
      }
      $this._initEnterEvent();
      $this._initFormEvent();
      $this.initAutocompleteEvent?.();
      initialized = true;
    }
  };
  $this.n("text").on("mousedown touchstart keydown", initTriggers, { passive: true });
};
base.plugin._initFocusInput = function() {
  let $this = this;
  $this.n("text").on("click", function(e) {
    e.stopPropagation();
    e.stopImmediatePropagation();
    external_DoMini_namespaceObject(this).trigger("focus");
    $this.gaEvent?.("focus");
    if (external_DoMini_namespaceObject("form", $this.n("searchsettings")).serialize() + $this.n("text").val().trim() === $this.lastSuccesfulSearch) {
      if (!$this.resultsOpened && !$this.usingLiveLoader()) {
        $this._no_animations = true;
        $this.showResults();
        $this._no_animations = false;
      }
      return false;
    }
  });
  $this.n("text").on("focus input", function() {
    if ($this.searching) {
      return;
    }
    if (external_DoMini_namespaceObject(this).val() !== "") {
      $this.n("proclose").css("display", "block");
    } else {
      $this.n("proclose").css({
        display: "none"
      });
    }
  });
};
base.plugin._initSearchInput = function() {
  let $this = this;
  $this.n("text").on("input", function(e) {
    $this.keycode = e.keyCode || e.which;
    $this.ktype = e.type;
    $this.updateHref();
    if (!$this.o.trigger.type) {
      $this.searchAbort();
      clearTimeout($this.timeouts.search);
      $this.hideLoader();
      return false;
    }
    $this.hideArrowBox?.();
    if ($this.n("text").val().length < $this.o.charcount) {
      $this.n("proloading").css("display", "none");
      if (!$this.att("blocking")) $this.hideSettings?.();
      $this.hideResults(false);
      $this.searchAbort();
      clearTimeout($this.timeouts.search);
      return false;
    }
    $this.searchAbort();
    clearTimeout($this.timeouts.search);
    $this.n("proloading").css("display", "none");
    $this.timeouts.search = setTimeout(function() {
      if (external_DoMini_namespaceObject("form", $this.n("searchsettings")).serialize() + $this.n("text").val().trim() !== $this.lastSuccesfulSearch || !$this.resultsOpened && !$this.usingLiveLoader()) {
        $this.search();
      } else {
        if ($this.isRedirectToFirstResult())
          $this.doRedirectToFirstResult();
        else
          $this.n("proclose").css("display", "block");
      }
    }, $this.o.trigger.delay);
  });
};
base.plugin._initEnterEvent = function() {
  let $this = this, rt, enterRecentlyPressed = false;
  $this.n("text").on("keyup", function(e) {
    $this.keycode = e.keyCode || e.which;
    $this.ktype = e.type;
    if ($this.keycode === 13) {
      clearTimeout(rt);
      rt = setTimeout(function() {
        enterRecentlyPressed = false;
      }, 300);
      if (enterRecentlyPressed) {
        return false;
      } else {
        enterRecentlyPressed = true;
      }
    }
    let isInput = external_DoMini_namespaceObject(this).hasClass("orig");
    if ($this.n("text").val().length >= $this.o.charcount && isInput && $this.keycode === 13) {
      $this.gaEvent?.("return");
      if ($this.o.redirectOnEnter) {
        if ($this.o.trigger.return !== "first_result") {
          $this.doRedirectToResults($this.ktype);
        } else {
          $this.search();
        }
      } else if ($this.o.trigger.return === "ajax_search") {
        if (external_DoMini_namespaceObject("form", $this.n("searchsettings")).serialize() + $this.n("text").val().trim() !== $this.lastSuccesfulSearch || !$this.resultsOpened) {
          $this.search();
        }
      }
      clearTimeout($this.timeouts.search);
    }
  });
};
base.plugin._initFormEvent = function() {
  let $this = this;
  external_DoMini_namespaceObject($this.n("text").closest("form").get(0)).on("submit", function(e, args) {
    e.preventDefault();
    if (input_helpers.isMobile()) {
      if ($this.o.redirectOnEnter) {
        let event = new Event("keyup");
        event.keyCode = event.which = 13;
        this.n("text").get(0).dispatchEvent(event);
      } else {
        $this.search();
        document.activeElement.blur();
      }
    } else if (typeof args != "undefined" && args === "ajax") {
      $this.search();
    }
  });
};
/* harmony default export */ var input = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/events/navigation.js



"use strict";
base.plugin.initNavigationEvents = function() {
  let $this = this;
  let handler = function(e) {
    let keycode = e.keyCode || e.which;
    if (external_DoMini_namespaceObject(".item", $this.n("resultsDiv")).length > 0 && $this.n("resultsDiv").css("display") !== "none" && $this.o.resultstype === "vertical") {
      if (keycode === 40 || keycode === 38) {
        let $hovered = $this.n("resultsDiv").find(".item.hovered");
        $this.n("text").trigger("blur");
        if ($hovered.length === 0) {
          $this.n("resultsDiv").find(".item").first().addClass("hovered");
        } else {
          if (keycode === 40) {
            if ($hovered.next(".item").length === 0) {
              $this.n("resultsDiv").find(".item").removeClass("hovered").first().addClass("hovered");
            } else {
              $hovered.removeClass("hovered").next(".item").addClass("hovered");
            }
          }
          if (keycode === 38) {
            if ($hovered.prev(".item").length === 0) {
              $this.n("resultsDiv").find(".item").removeClass("hovered").last().addClass("hovered");
            } else {
              $hovered.removeClass("hovered").prev(".item").addClass("hovered");
            }
          }
        }
        e.stopPropagation();
        e.preventDefault();
        if (!$this.n("resultsDiv").find(".resdrg .item.hovered").inViewPort(50, $this.n("resultsDiv").get(0))) {
          let n = $this.n("resultsDiv").find(".resdrg .item.hovered").get(0);
          if (n != null && typeof n.scrollIntoView != "undefined") {
            n.scrollIntoView({ behavior: "smooth", block: "start", inline: "nearest" });
          }
        }
      }
      if (keycode === 13 && external_DoMini_namespaceObject(".item.hovered", $this.n("resultsDiv")).length > 0) {
        e.stopPropagation();
        e.preventDefault();
        external_DoMini_namespaceObject(".item.hovered a.asp_res_url", $this.n("resultsDiv")).get(0).click();
      }
    }
  };
  $this.documentEventHandlers.push({
    "node": document,
    "event": "keydown",
    "handler": handler
  });
  external_DoMini_namespaceObject(document).on("keydown", handler);
};
/* harmony default export */ var navigation = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/events/other.js



"use strict";
let other_helpers = base.helpers;
base.plugin.initOtherEvents = function() {
  let $this = this, handler, handler2;
  if ($this.o.preventEvents && typeof jQuery !== "undefined") {
    jQuery($this.n("search").get(0)).closest("a, li").off();
  }
  if (other_helpers.isMobile() && other_helpers.detectIOS()) {
    $this.n("text").on("touchstart", function() {
      $this.savedScrollTop = window.scrollY;
      $this.savedContainerTop = $this.n("search").offset().top;
    });
  }
  if ($this.o.focusOnPageload) {
    external_DoMini_namespaceObject(window).on("load", function() {
      $this.n("text").get(0).focus();
    }, { "options": { "once": true } });
  }
  $this.n("proclose").on($this.clickTouchend, function(e) {
    e.preventDefault();
    e.stopImmediatePropagation();
    $this.n("text").val("");
    $this.n("textAutocomplete").val("");
    $this.hideResults();
    $this.n("text").trigger("focus");
    $this.n("proloading").css("display", "none");
    $this.hideLoader();
    $this.searchAbort();
    if (external_DoMini_namespaceObject(".asp_es_" + $this.o.id).length > 0) {
      $this.showLoader();
      $this.liveLoad(".asp_es_" + $this.o.id, $this.getCurrentLiveURL(), $this.o.trigger.update_href);
    } else {
      const array = ["resPage", "wooShop", "taxArchive", "cptArchive"];
      for (let i = 0; i < array.length; i++) {
        if ($this.o[array[i]].useAjax) {
          $this.showLoader();
          $this.liveLoad($this.o[array[i]].selector, $this.getCurrentLiveURL());
          break;
        }
      }
    }
    $this.n("text").get(0).focus();
  });
  if (other_helpers.isMobile()) {
    handler = function() {
      $this.orientationChange();
      setTimeout(function() {
        $this.orientationChange();
      }, 600);
    };
    $this.documentEventHandlers.push({
      "node": window,
      "event": "orientationchange",
      "handler": handler
    });
    external_DoMini_namespaceObject(window).on("orientationchange", handler);
  } else {
    handler = function() {
      $this.resize();
    };
    $this.documentEventHandlers.push({
      "node": window,
      "event": "resize",
      "handler": handler
    });
    external_DoMini_namespaceObject(window).on("resize", handler, { passive: true });
  }
  handler2 = function() {
    $this.scrolling(false);
  };
  $this.documentEventHandlers.push({
    "node": window,
    "event": "scroll",
    "handler": handler2
  });
  external_DoMini_namespaceObject(window).on("scroll", handler2, { passive: true });
  if (other_helpers.isMobile() && $this.o.mobile.menu_selector !== "") {
    external_DoMini_namespaceObject($this.o.mobile.menu_selector).on("touchend", function() {
      let _this = this;
      setTimeout(function() {
        let $input = external_DoMini_namespaceObject(_this).find("input.orig");
        $input = $input.length === 0 ? external_DoMini_namespaceObject(_this).next().find("input.orig") : $input;
        $input = $input.length === 0 ? external_DoMini_namespaceObject(_this).parent().find("input.orig") : $input;
        $input = $input.length === 0 ? $this.n("text") : $input;
        if ($this.n("search").inViewPort()) {
          $input.get(0).focus();
        }
      }, 300);
    });
  }
  if (other_helpers.detectIOS() && other_helpers.isMobile() && other_helpers.isTouchDevice()) {
    if (parseInt($this.n("text").css("font-size")) < 16) {
      $this.n("text").data("fontSize", $this.n("text").css("font-size")).css("font-size", "16px");
      $this.n("textAutocomplete").css("font-size", "16px");
      external_DoMini_namespaceObject("body").append("<style>#ajaxsearchpro" + $this.o.rid + " input.orig::-webkit-input-placeholder{font-size: 16px !important;}</style>");
    }
  }
};
base.plugin.orientationChange = function() {
  let $this = this;
  $this.detectAndFixFixedPositioning();
  $this.fixSettingsPosition();
  $this.fixResultsPosition();
  $this.fixTryThisPosition();
  if ($this.o.resultstype === "isotopic" && $this.n("resultsDiv").css("visibility") === "visible") {
    $this.calculateIsotopeRows();
    $this.showPagination(true);
    $this.removeAnimation();
  }
};
base.plugin.resize = function() {
  this.hideArrowBox?.();
  this.orientationChange();
};
base.plugin.scrolling = function(ignoreVisibility) {
  let $this = this;
  $this.detectAndFixFixedPositioning();
  $this.hideOnInvisibleBox();
  $this.fixSettingsPosition(ignoreVisibility);
  $this.fixResultsPosition(ignoreVisibility);
};
base.plugin.initTryThisEvents = function() {
  let $this = this;
  if ($this.n("trythis").find("a").length > 0) {
    $this.n("trythis").find("a").on("click touchend", function(e) {
      e.preventDefault();
      e.stopImmediatePropagation();
      if ($this.o.compact.enabled) {
        let state = $this.n("search").attr("data-asp-compact") || "closed";
        if (state === "closed")
          $this.n("promagnifier").trigger("click");
      }
      document.activeElement.blur();
      $this.n("textAutocomplete").val("");
      $this.n("text").val(external_DoMini_namespaceObject(this).html());
      $this.gaEvent?.("try_this");
      if ($this.o.trigger.type) {
        $this.searchWithCheck(80);
      }
    });
    $this.n("trythis").css({
      visibility: "visible"
    });
  }
};
base.plugin.initSelect2 = function() {
  let $this = this;
  window.WPD.intervalUntilExecute(function(jq) {
    if (typeof jq.fn.asp_select2 !== "undefined") {
      $this.select2jQuery = jq;
      external_DoMini_namespaceObject("select.asp_gochosen, select.asp_goselect2", $this.n("searchsettings")).forEach(function() {
        external_DoMini_namespaceObject(this).removeAttr("data-asp_select2-id");
        external_DoMini_namespaceObject(this).find('option[value=""]').val("__any__");
        $this.select2jQuery(this).asp_select2({
          width: "100%",
          theme: "flat",
          allowClear: external_DoMini_namespaceObject(this).find('option[value=""]').length > 0,
          "language": {
            "noResults": function() {
              return $this.o.select2.nores;
            }
          }
        });
        $this.select2jQuery(this).on("change", function() {
          external_DoMini_namespaceObject(this).trigger("change");
        });
      });
    }
  }, function() {
    return other_helpers.whichjQuery("asp_select2");
  });
};
/* harmony default export */ var events_other = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/events/results.js



"use strict";
base.plugin.initResultsEvents = function() {
  let $this = this;
  $this.n("resultsDiv").css({
    opacity: "0"
  });
  let handler = function(e) {
    let keycode = e.keyCode || e.which, ktype = e.type;
    if (external_DoMini_namespaceObject(e.target).closest(".asp_w").length === 0) {
      $this.hideOnInvisibleBox();
      $this.hideArrowBox?.();
      if (ktype !== "click" || ktype !== "touchend" || keycode !== 3) {
        if ($this.o.compact.enabled) {
          let compact = $this.n("search").attr("data-asp-compact") || "closed";
          if ($this.o.compact.closeOnDocument && compact === "open" && !$this.resultsOpened) {
            $this.closeCompact();
            $this.searchAbort();
            $this.hideLoader();
          }
        } else {
          if (!$this.resultsOpened || !$this.o.closeOnDocClick) return;
        }
        if (!$this.dragging) {
          $this.hideLoader();
          $this.searchAbort();
          $this.hideResults();
        }
      }
    }
  };
  $this.documentEventHandlers.push({
    "node": document,
    "event": $this.clickTouchend,
    "handler": handler
  });
  external_DoMini_namespaceObject(document).on($this.clickTouchend, handler);
  $this.n("resultsDiv").on("click", ".results .item", function() {
    if (external_DoMini_namespaceObject(this).attr("id") !== "") {
      $this.updateHref("#" + external_DoMini_namespaceObject(this).attr("id"));
    }
    $this.gaEvent?.("result_click", {
      "result_title": external_DoMini_namespaceObject(this).find("a.asp_res_url").text(),
      "result_url": external_DoMini_namespaceObject(this).find("a.asp_res_url").attr("href")
    });
  });
  if ($this.o.resultstype === "isotopic") {
    $this.n("resultsDiv").on("swiped-left", function() {
      if ($this.visiblePagination())
        $this.n("resultsDiv").find("a.asp_next").trigger("click");
    });
    $this.n("resultsDiv").on("swiped-right", function() {
      if ($this.visiblePagination())
        $this.n("resultsDiv").find("a.asp_prev").trigger("click");
    });
  }
};
/* harmony default export */ var results = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/events/touch.js



"use strict";
base.plugin.monitorTouchMove = function() {
  let $this = this;
  $this.dragging = false;
  external_DoMini_namespaceObject("body").on("touchmove", function() {
    $this.dragging = true;
  }).on("touchstart", function() {
    $this.dragging = false;
  });
};
/* harmony default export */ var touch = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/init/autopopulate.js


"use strict";
base.plugin.initAutop = function() {
  let $this = this;
  if ($this.o.autop.state === "disabled") return false;
  let location = window.location.href;
  let stop = location.indexOf("asp_ls=") > -1 || location.indexOf("asp_ls&") > -1;
  if (stop) {
    return false;
  }
  let count = $this.o.show_more.enabled && $this.o.show_more.action === "ajax" ? false : $this.o.autop.count;
  $this.isAutoP = true;
  if ($this.o.compact.enabled) {
    $this.openCompact();
  }
  if ($this.o.autop.state === "phrase") {
    if (!$this.o.is_results_page) {
      $this.n("text").val($this.o.autop.phrase);
    }
    $this.search(count);
  } else if ($this.o.autop.state === "latest") {
    $this.search(count, 1);
  } else {
    $this.search(count, 2);
  }
};
/* harmony default export */ var autopopulate = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/init/etc.js



"use strict";
let etc_helpers = base.helpers;
base.plugin.initEtc = function() {
  let $this = this;
  $this.il = {
    columns: 3,
    rows: $this.o.isotopic.pagination ? $this.o.isotopic.rows : 1e4,
    itemsPerPage: 6,
    lastVisibleItem: -1
  };
  $this.filterFns = {
    number: function(i, el) {
      if (typeof el === "undefined" || typeof i === "object") {
        el = i;
      }
      const number = external_DoMini_namespaceObject(el).attr("data-itemnum"), currentPage = $this.currentPage, itemsPerPage = $this.il.itemsPerPage;
      if (number % ($this.il.columns * $this.il.rows) < $this.il.columns * ($this.il.rows - 1))
        external_DoMini_namespaceObject(el).addClass("asp_gutter_bottom");
      else
        external_DoMini_namespaceObject(el).removeClass("asp_gutter_bottom");
      return parseInt(number, 10) < itemsPerPage * currentPage && parseInt(number, 10) >= itemsPerPage * (currentPage - 1);
    }
  };
  etc_helpers.Hooks.applyFilters("asp/init/etc", $this);
};
base.plugin.initInfiniteScroll = function() {
  let $this = this;
  if ($this.o.show_more.infinite && $this.o.resultstype !== "polaroid") {
    let t, handler;
    handler = function() {
      clearTimeout(t);
      t = setTimeout(function() {
        $this.checkAndTriggerInfiniteScroll("window");
      }, 80);
    };
    $this.documentEventHandlers.push({
      "node": window,
      "event": "scroll",
      "handler": handler
    });
    external_DoMini_namespaceObject(window).on("scroll", handler);
    $this.n("results").on("scroll", handler);
    let tt;
    $this.n("resultsDiv").on("nav_switch", function() {
      clearTimeout(tt);
      tt = setTimeout(function() {
        $this.checkAndTriggerInfiniteScroll("isotopic");
      }, 800);
    });
  }
};
base.plugin.hooks = function() {
  let $this = this;
  $this.n("s").on("asp_elementor_results", function(e, id) {
    if (parseInt($this.o.id) === parseInt(id)) {
      if (typeof window.jetpackLazyImagesModule == "function") {
        setTimeout(function() {
          window.jetpackLazyImagesModule();
        }, 300);
      }
    }
  });
};
/* harmony default export */ var etc = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/init/init.js



"use strict";
let init_helpers = base.helpers;
base.plugin.init = function(options, elem) {
  let $this = this;
  $this.searching = false;
  $this.triggerPrevState = false;
  $this.isAutoP = false;
  $this.autopStartedTheSearch = false;
  $this.autopData = {};
  $this.settingsInitialized = false;
  $this.resultsInitialized = false;
  $this.settingsChanged = false;
  $this.resultsOpened = false;
  $this.post = null;
  $this.postAuto = null;
  $this.savedScrollTop = 0;
  $this.savedContainerTop = 0;
  $this.disableMobileScroll = false;
  $this.clickTouchend = "click touchend";
  $this.mouseupTouchend = "mouseup touchend";
  $this.noUiSliders = [];
  $this.timeouts = {
    "compactBeforeOpen": null,
    "compactAfterOpen": null,
    "search": null,
    "searchWithCheck": null
  };
  $this.eh = {};
  $this.documentEventHandlers = [
    /**
     * {"node": document|window, "event": event_name, "handler": function()..}
     */
  ];
  $this.currentPage = 1;
  $this.currentPageURL = location.href;
  $this.isotopic = null;
  $this.sIsotope = null;
  $this.lastSuccesfulSearch = "";
  $this.lastSearchData = {};
  $this._no_animations = false;
  $this.call_num = 0;
  $this.results_num = 0;
  $this.o = external_DoMini_namespaceObject.fn.extend({}, options);
  $this.dynamicAtts = {};
  $this.nodes = {};
  $this.nodes.search = external_DoMini_namespaceObject(elem);
  if (init_helpers.isMobile())
    $this.animOptions = $this.o.animations.mob;
  else
    $this.animOptions = $this.o.animations.pc;
  $this.initNodeVariables();
  $this.animationOpacity = $this.animOptions.items.indexOf("In") < 0 ? "opacityOne" : "opacityZero";
  $this.o.resPage.useAjax = $this.o.compact.enabled ? 0 : $this.o.resPage.useAjax;
  if (init_helpers.isMobile()) {
    $this.o.trigger.type = $this.o.mobile.trigger_on_type;
    $this.o.trigger.click = $this.o.mobile.click_action;
    $this.o.trigger.click_location = $this.o.mobile.click_action_location;
    $this.o.trigger.return = $this.o.mobile.return_action;
    $this.o.trigger.return_location = $this.o.mobile.return_action_location;
    $this.o.trigger.redirect_url = $this.o.mobile.redirect_url;
    $this.o.trigger.elementor_url = $this.o.mobile.elementor_url;
  }
  $this.o.redirectOnClick = $this.o.trigger.click !== "ajax_search" && $this.o.trigger.click !== "nothing";
  $this.o.redirectOnEnter = $this.o.trigger.return !== "ajax_search" && $this.o.trigger.return !== "nothing";
  if ($this.usingLiveLoader()) {
    $this.o.trigger.type = $this.o.resPage.trigger_type;
    $this.o.trigger.facet = $this.o.resPage.trigger_facet;
    if ($this.o.resPage.trigger_magnifier) {
      $this.o.redirectOnClick = 0;
      $this.o.trigger.click = "ajax_search";
    }
    if ($this.o.resPage.trigger_return) {
      $this.o.redirectOnEnter = 0;
      $this.o.trigger.return = "ajax_search";
    }
  }
  if ($this.o.compact.overlay && external_DoMini_namespaceObject("#asp_absolute_overlay").length === 0) {
    external_DoMini_namespaceObject("body").append("<div id='asp_absolute_overlay'></div>");
  }
  if ($this.usingLiveLoader()) {
    $this.initLiveLoaderPopState?.();
  }
  if (typeof $this.initCompact !== "undefined") {
    $this.initCompact();
  }
  $this.monitorTouchMove();
  $this.initEvents();
  $this.initAutop();
  $this.initEtc();
  $this.hooks();
  $this.n("s").trigger("asp_init_search_bar", [$this.o.id, $this.o.iid], true, true);
  return this;
};
base.plugin.n = function(k) {
  if (typeof this.nodes[k] !== "undefined") {
    return this.nodes[k];
  } else {
    switch (k) {
      case "s":
        this.nodes[k] = this.nodes.search;
        break;
      case "container":
        this.nodes[k] = this.nodes.search.closest(".asp_w_container");
        break;
      case "searchsettings":
        this.nodes[k] = external_DoMini_namespaceObject(".asp_ss", this.n("container"));
        break;
      case "resultsDiv":
        this.nodes[k] = external_DoMini_namespaceObject(".asp_r", this.n("container"));
        break;
      case "probox":
        this.nodes[k] = external_DoMini_namespaceObject(".probox", this.nodes.search);
        break;
      case "proinput":
        this.nodes[k] = external_DoMini_namespaceObject(".proinput", this.nodes.search);
        break;
      case "text":
        this.nodes[k] = external_DoMini_namespaceObject(".proinput input.orig", this.nodes.search);
        break;
      case "textAutocomplete":
        this.nodes[k] = external_DoMini_namespaceObject(".proinput input.autocomplete", this.nodes.search);
        break;
      case "proloading":
        this.nodes[k] = external_DoMini_namespaceObject(".proloading", this.nodes.search);
        break;
      case "proclose":
        this.nodes[k] = external_DoMini_namespaceObject(".proclose", this.nodes.search);
        break;
      case "promagnifier":
        this.nodes[k] = external_DoMini_namespaceObject(".promagnifier", this.nodes.search);
        break;
      case "prosettings":
        this.nodes[k] = external_DoMini_namespaceObject(".prosettings", this.nodes.search);
        break;
      case "settingsAppend":
        this.nodes[k] = external_DoMini_namespaceObject("#wpdreams_asp_settings_" + this.o.id);
        break;
      case "resultsAppend":
        this.nodes[k] = external_DoMini_namespaceObject("#wpdreams_asp_results_" + this.o.id);
        break;
      case "trythis":
        this.nodes[k] = external_DoMini_namespaceObject("#asp-try-" + this.o.rid);
        break;
      case "hiddenContainer":
        this.nodes[k] = external_DoMini_namespaceObject(".asp_hidden_data", this.n("container"));
        break;
      case "aspItemOverlay":
        this.nodes[k] = external_DoMini_namespaceObject(".asp_item_overlay", this.n("hiddenContainer"));
        break;
      case "showmoreContainer":
        this.nodes[k] = external_DoMini_namespaceObject(".asp_showmore_container", this.n("resultsDiv"));
        break;
      case "showmore":
        this.nodes[k] = external_DoMini_namespaceObject(".showmore", this.n("resultsDiv"));
        break;
      case "items":
        this.nodes[k] = external_DoMini_namespaceObject(".item", this.n("resultsDiv")).length > 0 ? external_DoMini_namespaceObject(".item", this.n("resultsDiv")) : external_DoMini_namespaceObject(".photostack-flip", this.n("resultsDiv"));
        break;
      case "results":
        this.nodes[k] = external_DoMini_namespaceObject(".results", this.n("resultsDiv"));
        break;
      case "resdrg":
        this.nodes[k] = external_DoMini_namespaceObject(".resdrg", this.n("resultsDiv"));
        break;
    }
    return this.nodes[k];
  }
};
base.plugin.att = function(k) {
  if (typeof this.dynamicAtts[k] !== "undefined") {
    return this.dynamicAtts[k];
  } else {
    switch (k) {
      case "blocking":
        this.dynamicAtts[k] = this.n("searchsettings").hasClass("asp_sb");
    }
  }
  return this.dynamicAtts[k];
};
base.plugin.initNodeVariables = function() {
  let $this = this;
  $this.o.id = $this.nodes.search.data("id");
  $this.o.iid = $this.nodes.search.data("instance");
  $this.o.rid = $this.o.id + "_" + $this.o.iid;
  $this.fixClonedSelf();
};
base.plugin.initEvents = function() {
  this.initSettingsSwitchEvents?.();
  this.initOtherEvents();
  this.initTryThisEvents();
  this.initMagnifierEvents();
  this.initInputEvents();
  if (this.o.compact.enabled) {
    this.initCompactEvents();
  }
};
/* harmony default export */ var init = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/init/results.js



"use strict";
let init_results_helpers = base.helpers;
base.plugin.initResults = function() {
  if (!this.resultsInitialized) {
    this.initResultsBox();
    this.initResultsEvents();
    if (this.o.resultstype === "vertical") {
      this.initNavigationEvents?.();
    }
    if (this.o.resultstype === "isotopic") {
      this.initIsotopicPagination?.();
    }
  }
};
base.plugin.initResultsBox = function() {
  let $this = this;
  $this.initResultsAnimations();
  if (init_results_helpers.isMobile() && $this.o.mobile.force_res_hover) {
    $this.o.resultsposition = "hover";
    $this.nodes.resultsDiv = $this.n("resultsDiv").clone();
    external_DoMini_namespaceObject("body").append($this.nodes.resultsDiv);
    $this.n("resultsDiv").css({
      "position": "absolute"
    });
  } else {
    if ($this.o.resultsposition === "hover" && $this.n("resultsAppend").length <= 0) {
      $this.nodes.resultsDiv = $this.n("resultsDiv").clone();
      external_DoMini_namespaceObject("body").append($this.nodes.resultsDiv);
    } else {
      $this.o.resultsposition = "block";
      $this.n("resultsDiv").css({
        "position": "static"
      });
      if ($this.n("resultsAppend").length > 0) {
        if ($this.n("resultsAppend").find(".asp_r_" + $this.o.id).length > 0) {
          $this.nodes.resultsDiv = $this.n("resultsAppend").find(".asp_r_" + $this.o.id);
          if (typeof $this.nodes.resultsDiv.get(0).referenced !== "undefined") {
            ++$this.nodes.resultsDiv.get(0).referenced;
          } else {
            $this.nodes.resultsDiv.get(0).referenced = 1;
          }
        } else {
          $this.nodes.resultsDiv = $this.nodes.resultsDiv.clone();
          $this.nodes.resultsAppend.append($this.nodes.resultsDiv);
        }
      }
    }
  }
  $this.nodes.showmore = external_DoMini_namespaceObject(".showmore", $this.nodes.resultsDiv);
  $this.nodes.items = external_DoMini_namespaceObject(".item", $this.n("resultsDiv")).length > 0 ? external_DoMini_namespaceObject(".item", $this.nodes.resultsDiv) : external_DoMini_namespaceObject(".photostack-flip", $this.nodes.resultsDiv);
  $this.nodes.results = external_DoMini_namespaceObject(".results", $this.nodes.resultsDiv);
  $this.nodes.resdrg = external_DoMini_namespaceObject(".resdrg", $this.nodes.resultsDiv);
  $this.nodes.resultsDiv.get(0).id = $this.nodes.resultsDiv.get(0).id.replace("__original__", "");
  $this.detectAndFixFixedPositioning();
  $this.initInfiniteScroll();
  $this.resultsInitialized = true;
};
base.plugin.initResultsAnimations = function() {
  let $this = this, rpos = $this.n("resultsDiv").css("position"), blocking = rpos !== "fixed" && rpos !== "absolute";
  $this.resAnim = {
    "showClass": "",
    "showCSS": {
      "visibility": "visible",
      "display": "block",
      "opacity": 1,
      "animation-duration": $this.animOptions.results.dur + "ms"
    },
    "hideClass": "",
    "hideCSS": {
      "visibility": "hidden",
      "opacity": 0,
      "display": "none"
    },
    "duration": $this.animOptions.results.dur + "ms"
  };
  if ($this.animOptions.results.anim === "fade") {
    $this.resAnim.showClass = "asp_an_fadeIn";
    $this.resAnim.hideClass = "asp_an_fadeOut";
  }
  if ($this.animOptions.results.anim === "fadedrop" && !blocking) {
    $this.resAnim.showClass = "asp_an_fadeInDrop";
    $this.resAnim.hideClass = "asp_an_fadeOutDrop";
  } else if ($this.animOptions.results.anim === "fadedrop") {
    $this.resAnim.showClass = "asp_an_fadeIn";
    $this.resAnim.hideClass = "asp_an_fadeOut";
  }
  $this.n("resultsDiv").css({
    "-webkit-animation-duration": $this.resAnim.duration + "ms",
    "animation-duration": $this.resAnim.duration + "ms"
  });
};
/* harmony default export */ var init_results = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/widgets/widgets.js


"use strict";
external_DoMini_namespaceObject(function() {
  external_DoMini_namespaceObject(".ajaxsearchprotop").forEach(function() {
    let params = JSON.parse(external_DoMini_namespaceObject(this).data("aspdata")), id = params.id;
    if (params.action === 0) {
      external_DoMini_namespaceObject("a", external_DoMini_namespaceObject(this)).on("click", function(e) {
        e.preventDefault();
      });
    } else if (params.action === 2) {
      external_DoMini_namespaceObject("a", external_DoMini_namespaceObject(this)).on("click", function(e) {
        e.preventDefault();
        window.ASP.api(id, "searchFor", external_DoMini_namespaceObject(this).html());
        external_DoMini_namespaceObject("html").animate({
          scrollTop: external_DoMini_namespaceObject("div[id*=ajaxsearchpro" + id + "_]").first().offset().top - 40
        }, 500);
      });
    } else if (params.action === 1) {
      external_DoMini_namespaceObject("a", external_DoMini_namespaceObject(this)).on("click", function(e) {
        if (window.ASP.api(id, "exists")) {
          e.preventDefault();
          return window.ASP.api(id, "searchRedirect", external_DoMini_namespaceObject(this).html());
        }
      });
    }
  });
});

;// CONCATENATED MODULE: ./js/src/bundle/optimized/asp-core.js
























/* harmony default export */ var asp_core = (base);

Object(window.WPD).AjaxSearchPro = __webpack_exports__["default"];
/******/ })()
;