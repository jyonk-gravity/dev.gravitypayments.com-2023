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

;// ./src/client/plugin/core/base.js

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

;// external "DoMini"
var external_DoMini_namespaceObject = Object(window.WPD)["DoMini"];
;// ./src/client/plugin/core/etc/helpers.js



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

;// ./src/client/plugin/core/actions/animation.js



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

;// ./src/client/plugin/core/actions/filters.js



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
  $this.n("textAutocomplete").val("");
  $this.n("proloading").css("display", "none");
  $this.hideLoader();
  $this.searchAbort();
  $this.setFilterStateInput(0);
  $this.n("searchsettings").trigger("set_option_checked");
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

;// ./src/client/plugin/core/actions/loader.js



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

;// ./node_modules/@tannin/postfix/index.js
var PRECEDENCE, OPENERS, TERMINATORS, PATTERN;
PRECEDENCE = {
  "(": 9,
  "!": 8,
  "*": 7,
  "/": 7,
  "%": 7,
  "+": 6,
  "-": 6,
  "<": 5,
  "<=": 5,
  ">": 5,
  ">=": 5,
  "==": 4,
  "!=": 4,
  "&&": 3,
  "||": 2,
  "?": 1,
  "?:": 1
};
OPENERS = ["(", "?"];
TERMINATORS = {
  ")": ["("],
  ":": ["?", "?:"]
};
PATTERN = /<=|>=|==|!=|&&|\|\||\?:|\(|!|\*|\/|%|\+|-|<|>|\?|\)|:/;
function postfix(expression) {
  var terms = [], stack = [], match, operator, term, element;
  while (match = expression.match(PATTERN)) {
    operator = match[0];
    term = expression.substr(0, match.index).trim();
    if (term) {
      terms.push(term);
    }
    while (element = stack.pop()) {
      if (TERMINATORS[operator]) {
        if (TERMINATORS[operator][0] === element) {
          operator = TERMINATORS[operator][1] || operator;
          break;
        }
      } else if (OPENERS.indexOf(element) >= 0 || PRECEDENCE[element] < PRECEDENCE[operator]) {
        stack.push(element);
        break;
      }
      terms.push(element);
    }
    if (!TERMINATORS[operator]) {
      stack.push(operator);
    }
    expression = expression.substr(match.index + operator.length);
  }
  expression = expression.trim();
  if (expression) {
    terms.push(expression);
  }
  return terms.concat(stack.reverse());
}

;// ./node_modules/@tannin/evaluate/index.js
var OPERATORS = {
  "!": function(a) {
    return !a;
  },
  "*": function(a, b) {
    return a * b;
  },
  "/": function(a, b) {
    return a / b;
  },
  "%": function(a, b) {
    return a % b;
  },
  "+": function(a, b) {
    return a + b;
  },
  "-": function(a, b) {
    return a - b;
  },
  "<": function(a, b) {
    return a < b;
  },
  "<=": function(a, b) {
    return a <= b;
  },
  ">": function(a, b) {
    return a > b;
  },
  ">=": function(a, b) {
    return a >= b;
  },
  "==": function(a, b) {
    return a === b;
  },
  "!=": function(a, b) {
    return a !== b;
  },
  "&&": function(a, b) {
    return a && b;
  },
  "||": function(a, b) {
    return a || b;
  },
  "?:": function(a, b, c) {
    if (a) {
      throw b;
    }
    return c;
  }
};
function evaluate(postfix, variables) {
  var stack = [], i, j, args, getOperatorResult, term, value;
  for (i = 0; i < postfix.length; i++) {
    term = postfix[i];
    getOperatorResult = OPERATORS[term];
    if (getOperatorResult) {
      j = getOperatorResult.length;
      args = Array(j);
      while (j--) {
        args[j] = stack.pop();
      }
      try {
        value = getOperatorResult.apply(null, args);
      } catch (earlyReturn) {
        return earlyReturn;
      }
    } else if (variables.hasOwnProperty(term)) {
      value = variables[term];
    } else {
      value = +term;
    }
    stack.push(value);
  }
  return stack[0];
}

;// ./node_modules/@tannin/compile/index.js


function compile(expression) {
  var terms = postfix(expression);
  return function(variables) {
    return evaluate(terms, variables);
  };
}

;// ./node_modules/@tannin/plural-forms/index.js

function pluralForms(expression) {
  var evaluate = compile(expression);
  return function(n) {
    return +evaluate({ n });
  };
}

;// ./node_modules/tannin/index.js

var DEFAULT_OPTIONS = {
  contextDelimiter: "",
  onMissingKey: null
};
function getPluralExpression(pf) {
  var parts, i, part;
  parts = pf.split(";");
  for (i = 0; i < parts.length; i++) {
    part = parts[i].trim();
    if (part.indexOf("plural=") === 0) {
      return part.substr(7);
    }
  }
}
function Tannin(data, options) {
  var key;
  this.data = data;
  this.pluralForms = {};
  this.options = {};
  for (key in DEFAULT_OPTIONS) {
    this.options[key] = options !== void 0 && key in options ? options[key] : DEFAULT_OPTIONS[key];
  }
}
Tannin.prototype.getPluralForm = function(domain, n) {
  var getPluralForm = this.pluralForms[domain], config, plural, pf;
  if (!getPluralForm) {
    config = this.data[domain][""];
    pf = config["Plural-Forms"] || config["plural-forms"] || // Ignore reason: As known, there's no way to document the empty
    // string property on a key to guarantee this as metadata.
    // @ts-ignore
    config.plural_forms;
    if (typeof pf !== "function") {
      plural = getPluralExpression(
        config["Plural-Forms"] || config["plural-forms"] || // Ignore reason: As known, there's no way to document the empty
        // string property on a key to guarantee this as metadata.
        // @ts-ignore
        config.plural_forms
      );
      pf = pluralForms(plural);
    }
    getPluralForm = this.pluralForms[domain] = pf;
  }
  return getPluralForm(n);
};
Tannin.prototype.dcnpgettext = function(domain, context, singular, plural, n) {
  var index, key, entry;
  if (n === void 0) {
    index = 0;
  } else {
    index = this.getPluralForm(domain, n);
  }
  key = singular;
  if (context) {
    key = context + this.options.contextDelimiter + singular;
  }
  entry = this.data[domain][key];
  if (entry && entry[index]) {
    return entry[index];
  }
  if (this.options.onMissingKey) {
    this.options.onMissingKey(singular, domain);
  }
  return index === 0 ? singular : plural;
};

;// ./node_modules/@wordpress/api-fetch/node_modules/@wordpress/i18n/build-module/create-i18n.js

const DEFAULT_LOCALE_DATA = {
  "": {
    plural_forms(n) {
      return n === 1 ? 0 : 1;
    }
  }
};
const I18N_HOOK_REGEXP = /^i18n\.(n?gettext|has_translation)(_|$)/;
const createI18n = (initialData, initialDomain, hooks) => {
  const tannin = new Tannin({});
  const listeners = /* @__PURE__ */ new Set();
  const notifyListeners = () => {
    listeners.forEach((listener) => listener());
  };
  const subscribe = (callback) => {
    listeners.add(callback);
    return () => listeners.delete(callback);
  };
  const getLocaleData = (domain = "default") => tannin.data[domain];
  const doSetLocaleData = (data, domain = "default") => {
    tannin.data[domain] = {
      ...tannin.data[domain],
      ...data
    };
    tannin.data[domain][""] = {
      ...DEFAULT_LOCALE_DATA[""],
      ...tannin.data[domain]?.[""]
    };
    delete tannin.pluralForms[domain];
  };
  const setLocaleData = (data, domain) => {
    doSetLocaleData(data, domain);
    notifyListeners();
  };
  const addLocaleData = (data, domain = "default") => {
    tannin.data[domain] = {
      ...tannin.data[domain],
      ...data,
      // Populate default domain configuration (supported locale date which omits
      // a plural forms expression).
      "": {
        ...DEFAULT_LOCALE_DATA[""],
        ...tannin.data[domain]?.[""],
        ...data?.[""]
      }
    };
    delete tannin.pluralForms[domain];
    notifyListeners();
  };
  const resetLocaleData = (data, domain) => {
    tannin.data = {};
    tannin.pluralForms = {};
    setLocaleData(data, domain);
  };
  const dcnpgettext = (domain = "default", context, single, plural, number) => {
    if (!tannin.data[domain]) {
      doSetLocaleData(void 0, domain);
    }
    return tannin.dcnpgettext(domain, context, single, plural, number);
  };
  const getFilterDomain = (domain) => domain || "default";
  const __ = (text, domain) => {
    let translation = dcnpgettext(domain, void 0, text);
    if (!hooks) {
      return translation;
    }
    translation = hooks.applyFilters(
      "i18n.gettext",
      translation,
      text,
      domain
    );
    return hooks.applyFilters(
      "i18n.gettext_" + getFilterDomain(domain),
      translation,
      text,
      domain
    );
  };
  const _x = (text, context, domain) => {
    let translation = dcnpgettext(domain, context, text);
    if (!hooks) {
      return translation;
    }
    translation = hooks.applyFilters(
      "i18n.gettext_with_context",
      translation,
      text,
      context,
      domain
    );
    return hooks.applyFilters(
      "i18n.gettext_with_context_" + getFilterDomain(domain),
      translation,
      text,
      context,
      domain
    );
  };
  const _n = (single, plural, number, domain) => {
    let translation = dcnpgettext(
      domain,
      void 0,
      single,
      plural,
      number
    );
    if (!hooks) {
      return translation;
    }
    translation = hooks.applyFilters(
      "i18n.ngettext",
      translation,
      single,
      plural,
      number,
      domain
    );
    return hooks.applyFilters(
      "i18n.ngettext_" + getFilterDomain(domain),
      translation,
      single,
      plural,
      number,
      domain
    );
  };
  const _nx = (single, plural, number, context, domain) => {
    let translation = dcnpgettext(
      domain,
      context,
      single,
      plural,
      number
    );
    if (!hooks) {
      return translation;
    }
    translation = hooks.applyFilters(
      "i18n.ngettext_with_context",
      translation,
      single,
      plural,
      number,
      context,
      domain
    );
    return hooks.applyFilters(
      "i18n.ngettext_with_context_" + getFilterDomain(domain),
      translation,
      single,
      plural,
      number,
      context,
      domain
    );
  };
  const isRTL = () => {
    return "rtl" === _x("ltr", "text direction");
  };
  const hasTranslation = (single, context, domain) => {
    const key = context ? context + "" + single : single;
    let result = !!tannin.data?.[domain ?? "default"]?.[key];
    if (hooks) {
      result = hooks.applyFilters(
        "i18n.has_translation",
        result,
        single,
        context,
        domain
      );
      result = hooks.applyFilters(
        "i18n.has_translation_" + getFilterDomain(domain),
        result,
        single,
        context,
        domain
      );
    }
    return result;
  };
  if (initialData) {
    setLocaleData(initialData, initialDomain);
  }
  if (hooks) {
    const onHookAddedOrRemoved = (hookName) => {
      if (I18N_HOOK_REGEXP.test(hookName)) {
        notifyListeners();
      }
    };
    hooks.addAction("hookAdded", "core/i18n", onHookAddedOrRemoved);
    hooks.addAction("hookRemoved", "core/i18n", onHookAddedOrRemoved);
  }
  return {
    getLocaleData,
    setLocaleData,
    addLocaleData,
    resetLocaleData,
    subscribe,
    __,
    _x,
    _n,
    _nx,
    isRTL,
    hasTranslation
  };
};


;// ./node_modules/@wordpress/api-fetch/node_modules/@wordpress/hooks/build-module/validateNamespace.js
function validateNamespace(namespace) {
  if ("string" !== typeof namespace || "" === namespace) {
    console.error("The namespace must be a non-empty string.");
    return false;
  }
  if (!/^[a-zA-Z][a-zA-Z0-9_.\-\/]*$/.test(namespace)) {
    console.error(
      "The namespace can only contain numbers, letters, dashes, periods, underscores and slashes."
    );
    return false;
  }
  return true;
}
var validateNamespace_default = validateNamespace;


;// ./node_modules/@wordpress/api-fetch/node_modules/@wordpress/hooks/build-module/validateHookName.js
function validateHookName(hookName) {
  if ("string" !== typeof hookName || "" === hookName) {
    console.error("The hook name must be a non-empty string.");
    return false;
  }
  if (/^__/.test(hookName)) {
    console.error("The hook name cannot begin with `__`.");
    return false;
  }
  if (!/^[a-zA-Z][a-zA-Z0-9_.-]*$/.test(hookName)) {
    console.error(
      "The hook name can only contain numbers, letters, dashes, periods and underscores."
    );
    return false;
  }
  return true;
}
var validateHookName_default = validateHookName;


;// ./node_modules/@wordpress/api-fetch/node_modules/@wordpress/hooks/build-module/createAddHook.js


function createAddHook(hooks, storeKey) {
  return function addHook(hookName, namespace, callback, priority = 10) {
    const hooksStore = hooks[storeKey];
    if (!validateHookName_default(hookName)) {
      return;
    }
    if (!validateNamespace_default(namespace)) {
      return;
    }
    if ("function" !== typeof callback) {
      console.error("The hook callback must be a function.");
      return;
    }
    if ("number" !== typeof priority) {
      console.error(
        "If specified, the hook priority must be a number."
      );
      return;
    }
    const handler = { callback, priority, namespace };
    if (hooksStore[hookName]) {
      const handlers = hooksStore[hookName].handlers;
      let i;
      for (i = handlers.length; i > 0; i--) {
        if (priority >= handlers[i - 1].priority) {
          break;
        }
      }
      if (i === handlers.length) {
        handlers[i] = handler;
      } else {
        handlers.splice(i, 0, handler);
      }
      hooksStore.__current.forEach((hookInfo) => {
        if (hookInfo.name === hookName && hookInfo.currentIndex >= i) {
          hookInfo.currentIndex++;
        }
      });
    } else {
      hooksStore[hookName] = {
        handlers: [handler],
        runs: 0
      };
    }
    if (hookName !== "hookAdded") {
      hooks.doAction(
        "hookAdded",
        hookName,
        namespace,
        callback,
        priority
      );
    }
  };
}
var createAddHook_default = createAddHook;


;// ./node_modules/@wordpress/api-fetch/node_modules/@wordpress/hooks/build-module/createRemoveHook.js


function createRemoveHook(hooks, storeKey, removeAll = false) {
  return function removeHook(hookName, namespace) {
    const hooksStore = hooks[storeKey];
    if (!validateHookName_default(hookName)) {
      return;
    }
    if (!removeAll && !validateNamespace_default(namespace)) {
      return;
    }
    if (!hooksStore[hookName]) {
      return 0;
    }
    let handlersRemoved = 0;
    if (removeAll) {
      handlersRemoved = hooksStore[hookName].handlers.length;
      hooksStore[hookName] = {
        runs: hooksStore[hookName].runs,
        handlers: []
      };
    } else {
      const handlers = hooksStore[hookName].handlers;
      for (let i = handlers.length - 1; i >= 0; i--) {
        if (handlers[i].namespace === namespace) {
          handlers.splice(i, 1);
          handlersRemoved++;
          hooksStore.__current.forEach((hookInfo) => {
            if (hookInfo.name === hookName && hookInfo.currentIndex >= i) {
              hookInfo.currentIndex--;
            }
          });
        }
      }
    }
    if (hookName !== "hookRemoved") {
      hooks.doAction("hookRemoved", hookName, namespace);
    }
    return handlersRemoved;
  };
}
var createRemoveHook_default = createRemoveHook;


;// ./node_modules/@wordpress/api-fetch/node_modules/@wordpress/hooks/build-module/createHasHook.js
function createHasHook(hooks, storeKey) {
  return function hasHook(hookName, namespace) {
    const hooksStore = hooks[storeKey];
    if ("undefined" !== typeof namespace) {
      return hookName in hooksStore && hooksStore[hookName].handlers.some(
        (hook) => hook.namespace === namespace
      );
    }
    return hookName in hooksStore;
  };
}
var createHasHook_default = createHasHook;


;// ./node_modules/@wordpress/api-fetch/node_modules/@wordpress/hooks/build-module/createRunHook.js
function createRunHook(hooks, storeKey, returnFirstArg, async) {
  return function runHook(hookName, ...args) {
    const hooksStore = hooks[storeKey];
    if (!hooksStore[hookName]) {
      hooksStore[hookName] = {
        handlers: [],
        runs: 0
      };
    }
    hooksStore[hookName].runs++;
    const handlers = hooksStore[hookName].handlers;
    if (false) // removed by dead control flow
{}
    if (!handlers || !handlers.length) {
      return returnFirstArg ? args[0] : void 0;
    }
    const hookInfo = {
      name: hookName,
      currentIndex: 0
    };
    async function asyncRunner() {
      try {
        hooksStore.__current.add(hookInfo);
        let result = returnFirstArg ? args[0] : void 0;
        while (hookInfo.currentIndex < handlers.length) {
          const handler = handlers[hookInfo.currentIndex];
          result = await handler.callback.apply(null, args);
          if (returnFirstArg) {
            args[0] = result;
          }
          hookInfo.currentIndex++;
        }
        return returnFirstArg ? result : void 0;
      } finally {
        hooksStore.__current.delete(hookInfo);
      }
    }
    function syncRunner() {
      try {
        hooksStore.__current.add(hookInfo);
        let result = returnFirstArg ? args[0] : void 0;
        while (hookInfo.currentIndex < handlers.length) {
          const handler = handlers[hookInfo.currentIndex];
          result = handler.callback.apply(null, args);
          if (returnFirstArg) {
            args[0] = result;
          }
          hookInfo.currentIndex++;
        }
        return returnFirstArg ? result : void 0;
      } finally {
        hooksStore.__current.delete(hookInfo);
      }
    }
    return (async ? asyncRunner : syncRunner)();
  };
}
var createRunHook_default = createRunHook;


;// ./node_modules/@wordpress/api-fetch/node_modules/@wordpress/hooks/build-module/createCurrentHook.js
function createCurrentHook(hooks, storeKey) {
  return function currentHook() {
    const hooksStore = hooks[storeKey];
    const currentArray = Array.from(hooksStore.__current);
    return currentArray.at(-1)?.name ?? null;
  };
}
var createCurrentHook_default = createCurrentHook;


;// ./node_modules/@wordpress/api-fetch/node_modules/@wordpress/hooks/build-module/createDoingHook.js
function createDoingHook(hooks, storeKey) {
  return function doingHook(hookName) {
    const hooksStore = hooks[storeKey];
    if ("undefined" === typeof hookName) {
      return hooksStore.__current.size > 0;
    }
    return Array.from(hooksStore.__current).some(
      (hook) => hook.name === hookName
    );
  };
}
var createDoingHook_default = createDoingHook;


;// ./node_modules/@wordpress/api-fetch/node_modules/@wordpress/hooks/build-module/createDidHook.js

function createDidHook(hooks, storeKey) {
  return function didHook(hookName) {
    const hooksStore = hooks[storeKey];
    if (!validateHookName_default(hookName)) {
      return;
    }
    return hooksStore[hookName] && hooksStore[hookName].runs ? hooksStore[hookName].runs : 0;
  };
}
var createDidHook_default = createDidHook;


;// ./node_modules/@wordpress/api-fetch/node_modules/@wordpress/hooks/build-module/createHooks.js







class _Hooks {
  actions;
  filters;
  addAction;
  addFilter;
  removeAction;
  removeFilter;
  hasAction;
  hasFilter;
  removeAllActions;
  removeAllFilters;
  doAction;
  doActionAsync;
  applyFilters;
  applyFiltersAsync;
  currentAction;
  currentFilter;
  doingAction;
  doingFilter;
  didAction;
  didFilter;
  constructor() {
    this.actions = /* @__PURE__ */ Object.create(null);
    this.actions.__current = /* @__PURE__ */ new Set();
    this.filters = /* @__PURE__ */ Object.create(null);
    this.filters.__current = /* @__PURE__ */ new Set();
    this.addAction = createAddHook_default(this, "actions");
    this.addFilter = createAddHook_default(this, "filters");
    this.removeAction = createRemoveHook_default(this, "actions");
    this.removeFilter = createRemoveHook_default(this, "filters");
    this.hasAction = createHasHook_default(this, "actions");
    this.hasFilter = createHasHook_default(this, "filters");
    this.removeAllActions = createRemoveHook_default(this, "actions", true);
    this.removeAllFilters = createRemoveHook_default(this, "filters", true);
    this.doAction = createRunHook_default(this, "actions", false, false);
    this.doActionAsync = createRunHook_default(this, "actions", false, true);
    this.applyFilters = createRunHook_default(this, "filters", true, false);
    this.applyFiltersAsync = createRunHook_default(this, "filters", true, true);
    this.currentAction = createCurrentHook_default(this, "actions");
    this.currentFilter = createCurrentHook_default(this, "filters");
    this.doingAction = createDoingHook_default(this, "actions");
    this.doingFilter = createDoingHook_default(this, "filters");
    this.didAction = createDidHook_default(this, "actions");
    this.didFilter = createDidHook_default(this, "filters");
  }
}
function createHooks() {
  return new _Hooks();
}
var createHooks_default = createHooks;


;// ./node_modules/@wordpress/api-fetch/node_modules/@wordpress/hooks/build-module/index.js


const defaultHooks = createHooks_default();
const {
  addAction,
  addFilter,
  removeAction,
  removeFilter,
  hasAction,
  hasFilter,
  removeAllActions,
  removeAllFilters,
  doAction,
  doActionAsync,
  applyFilters,
  applyFiltersAsync,
  currentAction,
  currentFilter,
  doingAction,
  doingFilter,
  didAction,
  didFilter,
  actions,
  filters: build_module_filters
} = defaultHooks;


;// ./node_modules/@wordpress/api-fetch/node_modules/@wordpress/i18n/build-module/default-i18n.js


const i18n = createI18n(void 0, void 0, defaultHooks);
var default_i18n_default = (/* unused pure expression or super */ null && (i18n));
const getLocaleData = i18n.getLocaleData.bind(i18n);
const setLocaleData = i18n.setLocaleData.bind(i18n);
const resetLocaleData = i18n.resetLocaleData.bind(i18n);
const subscribe = i18n.subscribe.bind(i18n);
const __ = i18n.__.bind(i18n);
const _x = i18n._x.bind(i18n);
const _n = i18n._n.bind(i18n);
const _nx = i18n._nx.bind(i18n);
const isRTL = i18n.isRTL.bind(i18n);
const hasTranslation = i18n.hasTranslation.bind(i18n);


;// ./node_modules/@wordpress/api-fetch/node_modules/@wordpress/i18n/build-module/index.js





;// ./node_modules/@wordpress/api-fetch/build-module/middlewares/nonce.js
function createNonceMiddleware(nonce) {
  const middleware = (options, next) => {
    const { headers = {} } = options;
    for (const headerName in headers) {
      if (headerName.toLowerCase() === "x-wp-nonce" && headers[headerName] === middleware.nonce) {
        return next(options);
      }
    }
    return next({
      ...options,
      headers: {
        ...headers,
        "X-WP-Nonce": middleware.nonce
      }
    });
  };
  middleware.nonce = nonce;
  return middleware;
}
var nonce_default = createNonceMiddleware;


;// ./node_modules/@wordpress/api-fetch/build-module/middlewares/namespace-endpoint.js
const namespaceAndEndpointMiddleware = (options, next) => {
  let path = options.path;
  let namespaceTrimmed, endpointTrimmed;
  if (typeof options.namespace === "string" && typeof options.endpoint === "string") {
    namespaceTrimmed = options.namespace.replace(/^\/|\/$/g, "");
    endpointTrimmed = options.endpoint.replace(/^\//, "");
    if (endpointTrimmed) {
      path = namespaceTrimmed + "/" + endpointTrimmed;
    } else {
      path = namespaceTrimmed;
    }
  }
  delete options.namespace;
  delete options.endpoint;
  return next({
    ...options,
    path
  });
};
var namespace_endpoint_default = namespaceAndEndpointMiddleware;


;// ./node_modules/@wordpress/api-fetch/build-module/middlewares/root-url.js

const createRootURLMiddleware = (rootURL) => (options, next) => {
  return namespace_endpoint_default(options, (optionsWithPath) => {
    let url = optionsWithPath.url;
    let path = optionsWithPath.path;
    let apiRoot;
    if (typeof path === "string") {
      apiRoot = rootURL;
      if (-1 !== rootURL.indexOf("?")) {
        path = path.replace("?", "&");
      }
      path = path.replace(/^\//, "");
      if ("string" === typeof apiRoot && -1 !== apiRoot.indexOf("?")) {
        path = path.replace("?", "&");
      }
      url = apiRoot + path;
    }
    return next({
      ...optionsWithPath,
      url
    });
  });
};
var root_url_default = createRootURLMiddleware;


;// ./node_modules/@wordpress/url/build-module/normalize-path.js
function normalizePath(path) {
  const split = path.split("?");
  const query = split[1];
  const base = split[0];
  if (!query) {
    return base;
  }
  return base + "?" + query.split("&").map((entry) => entry.split("=")).map((pair) => pair.map(decodeURIComponent)).sort((a, b) => a[0].localeCompare(b[0])).map((pair) => pair.map(encodeURIComponent)).map((pair) => pair.join("=")).join("&");
}


;// ./node_modules/@wordpress/url/build-module/safe-decode-uri-component.js
function safeDecodeURIComponent(uriComponent) {
  try {
    return decodeURIComponent(uriComponent);
  } catch (uriComponentError) {
    return uriComponent;
  }
}


;// ./node_modules/@wordpress/url/build-module/get-query-string.js
function getQueryString(url) {
  let query;
  try {
    query = new URL(url, "http://example.com").search.substring(1);
  } catch (error) {
  }
  if (query) {
    return query;
  }
}


;// ./node_modules/@wordpress/url/build-module/get-query-args.js


function setPath(object, path, value) {
  const length = path.length;
  const lastIndex = length - 1;
  for (let i = 0; i < length; i++) {
    let key = path[i];
    if (!key && Array.isArray(object)) {
      key = object.length.toString();
    }
    key = ["__proto__", "constructor", "prototype"].includes(key) ? key.toUpperCase() : key;
    const isNextKeyArrayIndex = !isNaN(Number(path[i + 1]));
    object[key] = i === lastIndex ? (
      // If at end of path, assign the intended value.
      value
    ) : (
      // Otherwise, advance to the next object in the path, creating
      // it if it does not yet exist.
      object[key] || (isNextKeyArrayIndex ? [] : {})
    );
    if (Array.isArray(object[key]) && !isNextKeyArrayIndex) {
      object[key] = { ...object[key] };
    }
    object = object[key];
  }
}
function getQueryArgs(url) {
  return (getQueryString(url) || "").replace(/\+/g, "%20").split("&").reduce((accumulator, keyValue) => {
    const [key, value = ""] = keyValue.split("=").filter(Boolean).map(safeDecodeURIComponent);
    if (key) {
      const segments = key.replace(/\]/g, "").split("[");
      setPath(accumulator, segments, value);
    }
    return accumulator;
  }, /* @__PURE__ */ Object.create(null));
}


;// ./node_modules/@wordpress/url/build-module/build-query-string.js
function buildQueryString(data) {
  let string = "";
  const stack = Object.entries(data);
  let pair;
  while (pair = stack.shift()) {
    let [key, value] = pair;
    const hasNestedData = Array.isArray(value) || value && value.constructor === Object;
    if (hasNestedData) {
      const valuePairs = Object.entries(value).reverse();
      for (const [member, memberValue] of valuePairs) {
        stack.unshift([`${key}[${member}]`, memberValue]);
      }
    } else if (value !== void 0) {
      if (value === null) {
        value = "";
      }
      string += "&" + [key, String(value)].map(encodeURIComponent).join("=");
    }
  }
  return string.substr(1);
}


;// ./node_modules/@wordpress/url/build-module/get-fragment.js
function getFragment(url) {
  const matches = /^\S+?(#[^\s\?]*)/.exec(url);
  if (matches) {
    return matches[1];
  }
}


;// ./node_modules/@wordpress/url/build-module/add-query-args.js



function addQueryArgs(url = "", args) {
  if (!args || !Object.keys(args).length) {
    return url;
  }
  const fragment = getFragment(url) || "";
  let baseUrl = url.replace(fragment, "");
  const queryStringIndex = url.indexOf("?");
  if (queryStringIndex !== -1) {
    args = Object.assign(getQueryArgs(url), args);
    baseUrl = baseUrl.substr(0, queryStringIndex);
  }
  return baseUrl + "?" + buildQueryString(args) + fragment;
}


;// ./node_modules/@wordpress/api-fetch/build-module/middlewares/preloading.js

function createPreloadingMiddleware(preloadedData) {
  const cache = Object.fromEntries(
    Object.entries(preloadedData).map(([path, data]) => [
      normalizePath(path),
      data
    ])
  );
  return (options, next) => {
    const { parse = true } = options;
    let rawPath = options.path;
    if (!rawPath && options.url) {
      const { rest_route: pathFromQuery, ...queryArgs } = getQueryArgs(
        options.url
      );
      if (typeof pathFromQuery === "string") {
        rawPath = addQueryArgs(pathFromQuery, queryArgs);
      }
    }
    if (typeof rawPath !== "string") {
      return next(options);
    }
    const method = options.method || "GET";
    const path = normalizePath(rawPath);
    if ("GET" === method && cache[path]) {
      const cacheData = cache[path];
      delete cache[path];
      return prepareResponse(cacheData, !!parse);
    } else if ("OPTIONS" === method && cache[method] && cache[method][path]) {
      const cacheData = cache[method][path];
      delete cache[method][path];
      return prepareResponse(cacheData, !!parse);
    }
    return next(options);
  };
}
function prepareResponse(responseData, parse) {
  if (parse) {
    return Promise.resolve(responseData.body);
  }
  try {
    return Promise.resolve(
      new window.Response(JSON.stringify(responseData.body), {
        status: 200,
        statusText: "OK",
        headers: responseData.headers
      })
    );
  } catch {
    Object.entries(
      responseData.headers
    ).forEach(([key, value]) => {
      if (key.toLowerCase() === "link") {
        responseData.headers[key] = value.replace(
          /<([^>]+)>/,
          (_, url) => `<${encodeURI(url)}>`
        );
      }
    });
    return Promise.resolve(
      parse ? responseData.body : new window.Response(JSON.stringify(responseData.body), {
        status: 200,
        statusText: "OK",
        headers: responseData.headers
      })
    );
  }
}
var preloading_default = createPreloadingMiddleware;


;// ./node_modules/@wordpress/api-fetch/build-module/middlewares/fetch-all-middleware.js


const modifyQuery = ({ path, url, ...options }, queryArgs) => ({
  ...options,
  url: url && addQueryArgs(url, queryArgs),
  path: path && addQueryArgs(path, queryArgs)
});
const parseResponse = (response) => response.json ? response.json() : Promise.reject(response);
const parseLinkHeader = (linkHeader) => {
  if (!linkHeader) {
    return {};
  }
  const match = linkHeader.match(/<([^>]+)>; rel="next"/);
  return match ? {
    next: match[1]
  } : {};
};
const getNextPageUrl = (response) => {
  const { next } = parseLinkHeader(response.headers.get("link"));
  return next;
};
const requestContainsUnboundedQuery = (options) => {
  const pathIsUnbounded = !!options.path && options.path.indexOf("per_page=-1") !== -1;
  const urlIsUnbounded = !!options.url && options.url.indexOf("per_page=-1") !== -1;
  return pathIsUnbounded || urlIsUnbounded;
};
const fetchAllMiddleware = async (options, next) => {
  if (options.parse === false) {
    return next(options);
  }
  if (!requestContainsUnboundedQuery(options)) {
    return next(options);
  }
  const response = await index_default({
    ...modifyQuery(options, {
      per_page: 100
    }),
    // Ensure headers are returned for page 1.
    parse: false
  });
  const results = await parseResponse(response);
  if (!Array.isArray(results)) {
    return results;
  }
  let nextPage = getNextPageUrl(response);
  if (!nextPage) {
    return results;
  }
  let mergedResults = [].concat(results);
  while (nextPage) {
    const nextResponse = await index_default({
      ...options,
      // Ensure the URL for the next page is used instead of any provided path.
      path: void 0,
      url: nextPage,
      // Ensure we still get headers so we can identify the next page.
      parse: false
    });
    const nextResults = await parseResponse(nextResponse);
    mergedResults = mergedResults.concat(nextResults);
    nextPage = getNextPageUrl(nextResponse);
  }
  return mergedResults;
};
var fetch_all_middleware_default = fetchAllMiddleware;


;// ./node_modules/@wordpress/api-fetch/build-module/middlewares/http-v1.js
const OVERRIDE_METHODS = /* @__PURE__ */ new Set(["PATCH", "PUT", "DELETE"]);
const DEFAULT_METHOD = "GET";
const httpV1Middleware = (options, next) => {
  const { method = DEFAULT_METHOD } = options;
  if (OVERRIDE_METHODS.has(method.toUpperCase())) {
    options = {
      ...options,
      headers: {
        ...options.headers,
        "X-HTTP-Method-Override": method,
        "Content-Type": "application/json"
      },
      method: "POST"
    };
  }
  return next(options);
};
var http_v1_default = httpV1Middleware;


;// ./node_modules/@wordpress/url/build-module/get-query-arg.js

function getQueryArg(url, arg) {
  return getQueryArgs(url)[arg];
}


;// ./node_modules/@wordpress/url/build-module/has-query-arg.js

function hasQueryArg(url, arg) {
  return getQueryArg(url, arg) !== void 0;
}


;// ./node_modules/@wordpress/api-fetch/build-module/middlewares/user-locale.js

const userLocaleMiddleware = (options, next) => {
  if (typeof options.url === "string" && !hasQueryArg(options.url, "_locale")) {
    options.url = addQueryArgs(options.url, { _locale: "user" });
  }
  if (typeof options.path === "string" && !hasQueryArg(options.path, "_locale")) {
    options.path = addQueryArgs(options.path, { _locale: "user" });
  }
  return next(options);
};
var user_locale_default = userLocaleMiddleware;


;// ./node_modules/@wordpress/api-fetch/build-module/utils/response.js

async function parseJsonAndNormalizeError(response) {
  try {
    return await response.json();
  } catch {
    throw {
      code: "invalid_json",
      message: __("The response is not a valid JSON response.")
    };
  }
}
async function parseResponseAndNormalizeError(response, shouldParseResponse = true) {
  if (!shouldParseResponse) {
    return response;
  }
  if (response.status === 204) {
    return null;
  }
  return await parseJsonAndNormalizeError(response);
}
async function parseAndThrowError(response, shouldParseResponse = true) {
  if (!shouldParseResponse) {
    throw response;
  }
  throw await parseJsonAndNormalizeError(response);
}


;// ./node_modules/@wordpress/api-fetch/build-module/middlewares/media-upload.js


function isMediaUploadRequest(options) {
  const isCreateMethod = !!options.method && options.method === "POST";
  const isMediaEndpoint = !!options.path && options.path.indexOf("/wp/v2/media") !== -1 || !!options.url && options.url.indexOf("/wp/v2/media") !== -1;
  return isMediaEndpoint && isCreateMethod;
}
const mediaUploadMiddleware = (options, next) => {
  if (!isMediaUploadRequest(options)) {
    return next(options);
  }
  let retries = 0;
  const maxRetries = 5;
  const postProcess = (attachmentId) => {
    retries++;
    return next({
      path: `/wp/v2/media/${attachmentId}/post-process`,
      method: "POST",
      data: { action: "create-image-subsizes" },
      parse: false
    }).catch(() => {
      if (retries < maxRetries) {
        return postProcess(attachmentId);
      }
      next({
        path: `/wp/v2/media/${attachmentId}?force=true`,
        method: "DELETE"
      });
      return Promise.reject();
    });
  };
  return next({ ...options, parse: false }).catch((response) => {
    if (!(response instanceof globalThis.Response)) {
      return Promise.reject(response);
    }
    const attachmentId = response.headers.get(
      "x-wp-upload-attachment-id"
    );
    if (response.status >= 500 && response.status < 600 && attachmentId) {
      return postProcess(attachmentId).catch(() => {
        if (options.parse !== false) {
          return Promise.reject({
            code: "post_process",
            message: __(
              "Media upload failed. If this is a photo or a large image, please scale it down and try again."
            )
          });
        }
        return Promise.reject(response);
      });
    }
    return parseAndThrowError(response, options.parse);
  }).then(
    (response) => parseResponseAndNormalizeError(response, options.parse)
  );
};
var media_upload_default = mediaUploadMiddleware;


;// ./node_modules/@wordpress/url/build-module/remove-query-args.js


function removeQueryArgs(url, ...args) {
  const fragment = url.replace(/^[^#]*/, "");
  url = url.replace(/#.*/, "");
  const queryStringIndex = url.indexOf("?");
  if (queryStringIndex === -1) {
    return url + fragment;
  }
  const query = getQueryArgs(url);
  const baseURL = url.substr(0, queryStringIndex);
  args.forEach((arg) => delete query[arg]);
  const queryString = buildQueryString(query);
  const updatedUrl = queryString ? baseURL + "?" + queryString : baseURL;
  return updatedUrl + fragment;
}


;// ./node_modules/@wordpress/api-fetch/build-module/middlewares/theme-preview.js

const createThemePreviewMiddleware = (themePath) => (options, next) => {
  if (typeof options.url === "string") {
    const wpThemePreview = getQueryArg(
      options.url,
      "wp_theme_preview"
    );
    if (wpThemePreview === void 0) {
      options.url = addQueryArgs(options.url, {
        wp_theme_preview: themePath
      });
    } else if (wpThemePreview === "") {
      options.url = removeQueryArgs(
        options.url,
        "wp_theme_preview"
      );
    }
  }
  if (typeof options.path === "string") {
    const wpThemePreview = getQueryArg(
      options.path,
      "wp_theme_preview"
    );
    if (wpThemePreview === void 0) {
      options.path = addQueryArgs(options.path, {
        wp_theme_preview: themePath
      });
    } else if (wpThemePreview === "") {
      options.path = removeQueryArgs(
        options.path,
        "wp_theme_preview"
      );
    }
  }
  return next(options);
};
var theme_preview_default = createThemePreviewMiddleware;


;// ./node_modules/@wordpress/api-fetch/build-module/index.js











const DEFAULT_HEADERS = {
  // The backend uses the Accept header as a condition for considering an
  // incoming request as a REST request.
  //
  // See: https://core.trac.wordpress.org/ticket/44534
  Accept: "application/json, */*;q=0.1"
};
const build_module_DEFAULT_OPTIONS = {
  credentials: "include"
};
const middlewares = [
  user_locale_default,
  namespace_endpoint_default,
  http_v1_default,
  fetch_all_middleware_default
];
function registerMiddleware(middleware) {
  middlewares.unshift(middleware);
}
const defaultFetchHandler = (nextOptions) => {
  const { url, path, data, parse = true, ...remainingOptions } = nextOptions;
  let { body, headers } = nextOptions;
  headers = { ...DEFAULT_HEADERS, ...headers };
  if (data) {
    body = JSON.stringify(data);
    headers["Content-Type"] = "application/json";
  }
  const responsePromise = globalThis.fetch(
    // Fall back to explicitly passing `window.location` which is the behavior if `undefined` is passed.
    url || path || window.location.href,
    {
      ...build_module_DEFAULT_OPTIONS,
      ...remainingOptions,
      body,
      headers
    }
  );
  return responsePromise.then(
    (response) => {
      if (!response.ok) {
        return parseAndThrowError(response, parse);
      }
      return parseResponseAndNormalizeError(response, parse);
    },
    (err) => {
      if (err && err.name === "AbortError") {
        throw err;
      }
      if (!globalThis.navigator.onLine) {
        throw {
          code: "offline_error",
          message: __(
            "Unable to connect. Please check your Internet connection."
          )
        };
      }
      throw {
        code: "fetch_error",
        message: __(
          "Could not get a valid response from the server."
        )
      };
    }
  );
};
let fetchHandler = defaultFetchHandler;
function setFetchHandler(newFetchHandler) {
  fetchHandler = newFetchHandler;
}
const apiFetch = (options) => {
  const enhancedHandler = middlewares.reduceRight(
    (next, middleware) => {
      return (workingOptions) => middleware(workingOptions, next);
    },
    fetchHandler
  );
  return enhancedHandler(options).catch((error) => {
    if (error.code !== "rest_cookie_invalid_nonce") {
      return Promise.reject(error);
    }
    return globalThis.fetch(apiFetch.nonceEndpoint).then((response) => {
      if (!response.ok) {
        return Promise.reject(error);
      }
      return response.text();
    }).then((text) => {
      apiFetch.nonceMiddleware.nonce = text;
      return apiFetch(options);
    });
  });
};
apiFetch.use = registerMiddleware;
apiFetch.setFetchHandler = setFetchHandler;
apiFetch.createNonceMiddleware = nonce_default;
apiFetch.createPreloadingMiddleware = preloading_default;
apiFetch.createRootURLMiddleware = root_url_default;
apiFetch.fetchAllMiddleware = fetch_all_middleware_default;
apiFetch.mediaUploadMiddleware = media_upload_default;
apiFetch.createThemePreviewMiddleware = theme_preview_default;
var index_default = apiFetch;



;// ./src/client/plugin/core/actions/other.js




"use strict";
base.plugin.loadASPFonts = function() {
  if (ASP.font_url !== false) {
    let font = new FontFace(
      "asppsicons2",
      "url(" + ASP.font_url.replace("http:", "") + ")",
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
    $this.n("container").removeClass("asp_w_container_" + old_real_id).addClass("asp_w_container_" + $this.o.rid).data("instance", $this.o.iid);
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

;// ./src/client/plugin/core/actions/redirect.js



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

;// ./src/client/plugin/core/actions/results.js



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
base.plugin.keywordHighlight = function() {
  const $this = this;
  if (!$this.o.highlight) {
    return;
  }
  const phrase = $this.n("text").val().replace(/["']/g, "");
  if (phrase === "" || phrase.length < $this.o.trigger.minWordLength) {
    return;
  }
  const words = phrase.trim().split(" ").filter((s) => s.length >= $this.o.trigger.minWordLength);
  $this.n("resultsDiv").find("figcaption, div.item").highlight([phrase.trim()], {
    element: "span",
    className: "highlighted",
    wordsOnly: $this.o.highlightWholewords
  });
  if (words.length > 0) {
    $this.n("resultsDiv").find("figcaption, div.item").highlight(words, {
      element: "span",
      className: "highlighted",
      wordsOnly: $this.o.highlightWholewords
    });
  }
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

;// ./src/client/plugin/core/actions/scroll.js



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

;// ./src/client/plugin/core/actions/search.js



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
  this.updateSettingsDeviceField?.();
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
  data.version = ASP.version;
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
        const data_response = JSON.parse(response);
        if (data_response.html === void 0) {
          $this.hideLoader();
          alert('Ajax Search Pro Error:\r\n\r\nPlease look up "The response data is missing" from the documentation at\r\n\r\n documentation.ajaxsearchpro.com');
          return false;
        }
        let html_response = search_helpers.Hooks.applyFilters("asp_search_html", data_response.html, $this.o.id, $this.o.iid);
        $this.n("s").trigger("asp_search_end", [$this.o.id, $this.o.iid, $this.n("text").val(), data_response], true, true);
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
        $this.statisticsID = data_response?.statistics_id ?? 0;
        if ($this.autopStartedTheSearch) {
          if (typeof data.autop != "undefined") {
            $this.autopData["not_in"] = {};
            $this.autopData["not_in_count"] = 0;
            if (typeof data_response.results != "undefined") {
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
        search_helpers.Hooks.applyFilters("asp/search/end", $this, data);
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

;// ./src/client/plugin/core/etc/api.js



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

;// ./src/client/plugin/core/etc/position.js



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
        "z-index": 2147483646
      });
    }
    if (!$this.att("blocking")) {
      $this.n("searchsettings").css({
        "position": "fixed",
        "z-index": 2147483646
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

;// ./src/client/plugin/core/events/button.js



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

;// ./src/client/plugin/core/events/input.js



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
    $this.n("textAutocomplete").val("");
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

;// ./src/client/plugin/core/events/navigation.js



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

;// ./src/client/global/utils/device.ts

const deviceType = () => {
  let w = window.innerWidth;
  if (w <= 640) {
    return "phone";
  } else if (w <= 1024) {
    return "tablet";
  } else {
    return "desktop";
  }
};
const detectIOS = () => {
  if (typeof window.navigator != "undefined" && typeof window.navigator.userAgent != "undefined")
    return window.navigator.userAgent.match(/(iPod|iPhone|iPad)/) != null;
  return false;
};
const isMobile = () => {
  try {
    document.createEvent("TouchEvent");
    return true;
  } catch (e) {
    return false;
  }
};
const isTouchDevice = () => {
  return "ontouchstart" in window;
};

;// ./src/client/utils/browser.ts


const isFirefox = navigator.userAgent.toLowerCase().includes("firefox");
const ua = navigator.userAgent;
const isWebKit = /AppleWebKit/.test(ua) && !/Edge/.test(ua);
let fakeInput;
const focusInput = (targetInput) => {
  if (!detectIOS()) {
    targetInput?.focus();
    return;
  }
  if (targetInput === void 0 || fakeInput === void 0) {
    fakeInput = document.createElement("input");
    fakeInput.setAttribute("type", "text");
    fakeInput.style.position = "absolute";
    fakeInput.style.opacity = "0";
    fakeInput.style.height = "0";
    fakeInput.style.fontSize = "16px";
    document.body.prepend(fakeInput);
  }
  if (targetInput === void 0) {
    fakeInput.focus();
  } else {
    targetInput.focus();
  }
};


;// ./src/client/plugin/core/events/other.js




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
    external_DoMini_namespaceObject($this.o.mobile.menu_selector).on("touchend", function(e) {
      let _this = this;
      focusInput();
      setTimeout(function() {
        let $input = external_DoMini_namespaceObject(_this).find("input.orig");
        $input = $input.length === 0 ? external_DoMini_namespaceObject(_this).next().find("input.orig") : $input;
        $input = $input.length === 0 ? external_DoMini_namespaceObject(_this).parent().find("input.orig") : $input;
        $input = $input.length === 0 ? $this.n("text") : $input;
        if ($this.n("search").inViewPort()) {
          focusInput($input.get(0));
        }
      }, 1e3);
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
  const $this = this;
  $this.detectAndFixFixedPositioning();
  $this.fixSettingsPosition();
  $this.fixResultsPosition();
  $this.fixTryThisPosition();
  $this.updateSettingsDeviceField?.();
  if ($this.o.resultstype === "isotopic" && $this.n("resultsDiv").css("visibility") === "visible") {
    $this.calculateIsotopeRows();
    $this.showPagination(true);
    $this.removeAnimation();
  }
};
base.plugin.resize = function() {
  this.hideArrowBox?.();
  this.orientationChange();
  this.updateSettingsDeviceField?.();
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
      $this.n("text").val(external_DoMini_namespaceObject(this).text());
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

;// ./src/client/plugin/core/events/results.js



"use strict";
base.plugin.initResultsEvents = function() {
  let $this = this;
  $this.n("resultsDiv").css({
    opacity: "0"
  });
  let handler = function(e) {
    let keycode = e.keyCode || e.which, ktype = e.type;
    if (external_DoMini_namespaceObject(e.target).closest(".asp_w, .asp-sl-overlay, .asp-simple-lightbox").length === 0) {
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
  const recordInteractions = ASP.statistics.enabled && ASP.statistics.record_results && ASP.statistics.record_result_interactions;
  $this.n("resultsDiv").on("click", ".results .item", function(e) {
    if ($this.o.results.disableClick) {
      e.preventDefault();
      return false;
    }
    if (external_DoMini_namespaceObject(this).attr("id") !== "") {
      $this.updateHref("#" + external_DoMini_namespaceObject(this).attr("id"));
    }
    if (recordInteractions) {
      ASP.registerInteraction(this, $this.statisticsID);
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

;// ./src/client/plugin/core/events/touch.js



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

;// ./src/client/plugin/core/init/autopopulate.js


let autopopulate_helpers = base.helpers;
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
      $this.n("text").val(autopopulate_helpers.decodeHTMLEntities($this.o.autop.phrase));
    }
    $this.search(count);
  } else if ($this.o.autop.state === "latest") {
    $this.search(count, 1);
  } else {
    $this.search(count, 2);
  }
};
/* harmony default export */ var autopopulate = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/init/etc.js



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

;// ./src/client/plugin/core/init/init.js



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
  $this.statisticsID = $this.n("container").data("statistics-id");
  $this.statisticsID = $this.statisticsID === "" ? 0 : parseInt($this.statisticsID);
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

;// ./src/client/plugin/core/init/results.js



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

;// ./src/client/plugin/widgets/widgets.js


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

;// ./src/client/bundle/optimized/asp-core.js
























/* harmony default export */ var asp_core = (base);

Object(window.WPD).AjaxSearchPro = __webpack_exports__["default"];
/******/ })()
;