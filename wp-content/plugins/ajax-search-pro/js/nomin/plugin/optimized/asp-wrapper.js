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

;// external "DoMini"
var external_DoMini_namespaceObject = DoMini;
var external_DoMini_default = /*#__PURE__*/__webpack_require__.n(external_DoMini_namespaceObject);
;// ./src/client/plugin/wrapper/instances.js


window._asp_instances_storage = window._asp_instances_storage || [];
const instances = {
  instances: window._asp_instances_storage,
  get: function(id, instance) {
    this.clean();
    if (typeof id === "undefined" || id === 0) {
      return this.instances;
    } else {
      if (typeof instance === "undefined") {
        let ret = [];
        for (let i = 0; i < this.instances.length; i++) {
          if (parseInt(this.instances[i].o.id) === id) {
            ret.push(this.instances[i]);
          }
        }
        return ret.length > 0 ? ret : false;
      } else {
        for (let i = 0; i < this.instances.length; i++) {
          if (parseInt(this.instances[i].o.id) === id && parseInt(this.instances[i].o.iid) === instance) {
            return this.instances[i];
          }
        }
      }
    }
    return false;
  },
  set: function(obj) {
    if (!this.exist(obj.o.id, obj.o.iid)) {
      this.instances.push(obj);
      return true;
    } else {
      return false;
    }
  },
  exist: function(id, instance) {
    this.clean();
    for (let i = 0; i < this.instances.length; i++) {
      if (parseInt(this.instances[i].o.id) === parseInt(id)) {
        if (typeof instance === "undefined") {
          return true;
        } else if (parseInt(this.instances[i].o.iid) === parseInt(instance)) {
          return true;
        }
      }
    }
    return false;
  },
  clean: function() {
    let unset = [], _this = this;
    this.instances.forEach(function(v, k) {
      if (external_DoMini_namespaceObject(".asp_m_" + v.o.rid).length === 0) {
        unset.push(k);
      }
    });
    unset.forEach(function(k) {
      if (typeof _this.instances[k] !== "undefined") {
        _this.instances[k].destroy();
        _this.instances.splice(k, 1);
      }
    });
  },
  destroy: function(id, instance) {
    let i = this.get(id, instance);
    if (i !== false) {
      if (Array.isArray(i)) {
        i.forEach(function(s) {
          s.destroy();
        });
        this.instances = [];
      } else {
        let u = 0;
        this.instances.forEach(function(v, k) {
          if (parseInt(v.o.id) === id && parseInt(v.o.iid) === instance) {
            u = k;
          }
        });
        i.destroy();
        this.instances.splice(u, 1);
      }
    }
  }
};
/* harmony default export */ var wrapper_instances = (instances);

;// ./src/client/plugin/wrapper/api.ts


function api() {
  "use strict";
  const a4 = function(id, instance, func, args) {
    let s = wrapper_instances.get(id, instance);
    return s !== false && s[func].apply(s, [args]);
  }, a3 = function(id, func, args) {
    let s;
    if (typeof func === "number" && isFinite(func)) {
      s = wrapper_instances.get(id, func);
      return s !== false && s[args].apply(s);
    } else if (typeof func === "string") {
      s = wrapper_instances.get(id);
      return s !== false && s.forEach(function(i) {
        const f = i[func];
        if (typeof f === "function") {
          f.apply(i, [args]);
        }
      });
    }
  }, a2 = function(id, func) {
    let s;
    if (func === "exists") {
      return wrapper_instances.exist(id);
    }
    s = wrapper_instances.get(id);
    return s !== false && s.forEach(function(i) {
      const f = i[func];
      if (typeof f === "function") {
        f.apply(i);
      }
    });
  };
  if (arguments.length === 4) {
    return a4.apply(this, arguments);
  } else if (arguments.length === 3) {
    return a3.apply(this, arguments);
  } else if (arguments.length === 2) {
    return a2.apply(this, arguments);
  } else if (arguments.length === 0) {
    console.log("Usage: ASP.api(id, [optional]instance, function, [optional]args);");
    console.log("For more info: https://knowledgebase.ajaxsearchpro.com/other/javascript-api");
  }
}

;// external "window.WPD.Base64"
var external_window_WPD_Base64_namespaceObject = window.WPD.Base64;
var external_window_WPD_Base64_default = /*#__PURE__*/__webpack_require__.n(external_window_WPD_Base64_namespaceObject);
;// ./src/client/global/utils/browser.ts



const isSafari = () => {
  return /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
};
const whichjQuery = (plugin) => {
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
const formData = function(form, d) {
  let els = form.find("input,textarea,select,button").get();
  if (arguments.length === 1) {
    const data = {};
    els.forEach(function(el) {
      if (el.name && !el.disabled && (el.checked || /select|textarea/i.test(el.nodeName) || /text/i.test(el.type) || $(el).hasClass("hasDatepicker") || $(el).hasClass("asp_slider_hidden"))) {
        if (data[el.name] === void 0) {
          data[el.name] = [];
        }
        if ($(el).hasClass("hasDatepicker")) {
          data[el.name].push($(el).parent().find(".asp_datepicker_hidden").val());
        } else {
          data[el.name].push($(el).val());
        }
      }
    });
    return JSON.stringify(data);
  } else if (d !== void 0) {
    const data = typeof d != "object" ? JSON.parse(d) : d;
    els.forEach(function(el) {
      if (el.name) {
        if (data[el.name]) {
          let names = data[el.name], _this = $(el);
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
            if ($(el).hasClass("asp_gochosen") || $(el).hasClass("asp_goselect2")) {
              intervalUntilExecute(function(_$) {
                _$(el).trigger("change.asp_select2");
              }, function() {
                return whichjQuery("asp_select2");
              }, 50, 3);
            } else if ($(el).hasClass("hasDatepicker")) {
              intervalUntilExecute(function(_$) {
                const node = _this.get(0);
                if (node === void 0) {
                  return;
                }
                let value = names[0], format = _$(node).datepicker("option", "dateFormat");
                _$(node).datepicker("option", "dateFormat", "yy-mm-dd");
                _$(node).datepicker("setDate", value);
                _$(node).datepicker("option", "dateFormat", format);
                _$(node).trigger("selectnochange");
              }, function() {
                return whichjQuery("datepicker");
              }, 50, 3);
            }
          }
        } else {
          if (el.type === "checkbox" || el.type === "radio") {
            $(el).prop("checked", false);
          }
        }
      }
    });
    return form;
  }
};
const submitToUrl = function(action, method, input, target = "self") {
  let form;
  form = $('<form style="display: none;" />');
  form.attr("action", action);
  form.attr("method", method);
  $("body").append(form);
  if (typeof input !== "undefined" && input !== null) {
    Object.keys(input).forEach(function(name) {
      let value = input[name];
      let $input = $('<input type="hidden" />');
      $input.attr("name", name);
      $input.attr("value", value);
      form.append($input);
    });
  }
  if (target == "new") {
    form.attr("target", "_blank");
  }
  form.get(0).submit();
};
const openInNewTab = function(url) {
  Object.assign(document.createElement("a"), { target: "_blank", href: url }).click();
};
const scrollToFirstVisibleElement = function(elements, offset = 0) {
  for (const element2 of elements) {
    if (recursiveCheckVisibility(element2)) {
      window.scrollTo({
        top: element2.getBoundingClientRect().top - 120 + window.pageYOffset + offset,
        behavior: "smooth"
      });
      return true;
    }
  }
  return false;
};
const recursiveCheckVisibility = function(element2) {
  if (typeof element2.checkVisibility === "undefined") {
    return true;
  }
  let el = element2, visible = true;
  while (el !== null) {
    if (!el.checkVisibility({
      opacityProperty: true,
      visibilityProperty: true,
      contentVisibilityAuto: true
    })) {
      visible = false;
      break;
    }
    el = el.parentElement;
  }
  return visible;
};

;// ./src/client/utils/onSafeDocumentReady.ts

const onSafeDocumentReady = (callback) => {
  let wasExecuted = false;
  const isDocumentReady = () => {
    return document.readyState === "complete" || document.readyState === "interactive" || document.readyState === "loaded";
  };
  const removeListeners = () => {
    window.removeEventListener("DOMContentLoaded", onDOMContentLoaded);
    document.removeEventListener("readystatechange", onReadyStateChange);
  };
  const runCallback = () => {
    if (!wasExecuted) {
      wasExecuted = true;
      callback();
      removeListeners();
    }
  };
  const onDOMContentLoaded = () => {
    runCallback();
  };
  const onReadyStateChange = () => {
    if (isDocumentReady()) {
      runCallback();
    }
  };
  if (isDocumentReady()) {
    runCallback();
  } else {
    window.addEventListener("DOMContentLoaded", onDOMContentLoaded);
    document.addEventListener("readystatechange", onReadyStateChange);
  }
};
/* harmony default export */ var utils_onSafeDocumentReady = (onSafeDocumentReady);

;// ./src/client/plugin/wrapper/asp.ts







const ASP = window.ASP;
const ASP_EXTENDED = {
  instances: wrapper_instances,
  instance_args: [],
  api: api,
  initialized: false,
  initializeAllSearches: function() {
    const instances2 = this.getInstances();
    instances2.forEach(function(data, i) {
      external_DoMini_default().fn._(".asp_m_" + i).forEach(function(el) {
        if (typeof el.hasAsp != "undefined") {
          return true;
        }
        el.hasAsp = true;
        return external_DoMini_default()(el).ajaxsearchpro(data);
      });
    });
  },
  initializeSearchByID: function(id, instance = 0) {
    const data = this.getInstance(id);
    const selector = instance === 0 ? ".asp_m_" + id : ".asp_m_" + id + "_" + instance;
    external_DoMini_default().fn._(selector).forEach(function(el) {
      if (typeof el.hasAsp != "undefined") {
        return true;
      }
      el.hasAsp = true;
      return external_DoMini_default()(el).ajaxsearchpro(data);
    });
  },
  getInstances: function() {
    external_DoMini_default().fn._(".asp_init_data").forEach((el) => {
      const id = parseInt(el.dataset["aspId"] || "");
      let data;
      if (typeof el.dataset["aspdata"] != "undefined") {
        data = external_window_WPD_Base64_default().decode(el.dataset["aspdata"]);
      }
      if (typeof data === "undefined" || data === "") return true;
      this.instance_args[id] = JSON.parse(data);
    });
    return this.instance_args;
  },
  getInstance: function(id) {
    if (typeof this.instance_args[id] !== "undefined") {
      return this.instance_args[id];
    }
    return this.getInstances()[id];
  },
  initialize: function(id) {
    if (typeof ASP.version == "undefined") {
      return false;
    }
    if (ASP.script_async_load || ASP.init_only_in_viewport) {
      const searches = document.querySelectorAll(".asp_w_container");
      if (searches.length) {
        const observer = new IntersectionObserver((entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting) {
              const id2 = parseInt(entry.target.dataset.id ?? "0");
              const instance = parseInt(entry.target.dataset.instance ?? "0");
              this.initializeSearchByID(id2, instance);
              observer.unobserve(entry.target);
            }
          });
        });
        searches.forEach(function(search) {
          if (typeof search._is_observed !== "undefined") {
            return;
          }
          search._is_observed = true;
          observer.observe(search);
        });
      }
      this.getInstances().forEach((inst, id2) => {
        if (inst.compact.enabled) {
          this.initializeSearchByID(id2);
        }
      });
    } else {
      if (typeof id === "undefined") {
        this.initializeAllSearches();
      } else {
        this.initializeSearchByID(id);
      }
    }
    this.initializeMutateDetector();
    this.initializeHighlight();
    this.initializeOtherEvents();
    this.initialized = true;
    return true;
  },
  initializeHighlight: function() {
    if (!ASP.highlight.enabled) {
      return;
    }
    const data = ASP.highlight.data;
    let selector = data.selector !== "" && external_DoMini_default()(data.selector).length > 0 ? data.selector : "article", $highlighted, phrase;
    selector = external_DoMini_default()(selector).length > 0 ? selector : "body";
    const s = new URLSearchParams(location.search);
    phrase = s.get("s") ?? s.get("asp_highlight") ?? s.get("asp_s") ?? s.get("asp_ls") ?? "";
    external_DoMini_default()(selector).unhighlight({ className: "asl_single_highlighted" });
    if (phrase === null) {
      return;
    }
    phrase = phrase.trim();
    if (phrase === "") {
      return;
    }
    const words = phrase.trim().split(" ").map((s2) => s2.trim(".")).filter((s2) => s2.length >= data.minWordLength);
    external_DoMini_default()(selector).highlight([phrase.trim()], {
      element: "span",
      className: "asp_single_highlighted_" + data.id + " asp_single_highlighted_exact",
      wordsOnly: data.whole,
      excludeParents: ".asp_w, .asp-try"
    });
    if (words.length > 0) {
      external_DoMini_default()(selector).highlight(words, {
        element: "span",
        className: "asp_single_highlighted_" + data.id,
        wordsOnly: data.whole,
        excludeParents: ".asp_w, .asp-try, .asp_single_highlighted_" + data.id
      });
    }
    if (data.scroll) {
      if (!scrollToFirstVisibleElement(external_DoMini_default()(".asp_single_highlighted_" + data.id + ".asp_single_highlighted_exact").get(), data.scroll_offset)) {
        scrollToFirstVisibleElement(external_DoMini_default()(".asp_single_highlighted_" + data.id).get(), data.scroll_offset);
      }
    }
  },
  initializeOtherEvents: function() {
    let ttt, ts;
    const $body = external_DoMini_default()("body");
    ts = "#menu-item-search, .fa-search, .fa, .fas";
    ts = ts + ", .fusion-flyout-menu-toggle, .fusion-main-menu-search-open";
    ts = ts + ", #search_button";
    ts = ts + ", .mini-search.popup-search";
    ts = ts + ", .icon-search";
    ts = ts + ", .menu-item-search-dropdown";
    ts = ts + ", .mobile-menu-button";
    ts = ts + ", .td-icon-search, .tdb-search-icon";
    ts = ts + ", .side_menu_button, .search_button";
    ts = ts + ", .raven-search-form-toggle";
    ts = ts + ", [data-elementor-open-lightbox], .elementor-button-link, .elementor-button";
    ts = ts + ", i[class*=-search], a[class*=-search]";
    $body.on("click touchend", ts, () => {
      clearTimeout(ttt);
      ttt = setTimeout(() => {
        this.initializeAllSearches();
      }, 300);
    });
    if (typeof window.jQuery != "undefined") {
      window.jQuery(document).on("elementor/popup/show", () => {
        setTimeout(() => {
          this.initializeAllSearches();
        }, 10);
      });
    }
  },
  initializeMutateDetector: function() {
    let t;
    if (typeof ASP.detect_ajax != "undefined" && ASP.detect_ajax) {
      const o = new MutationObserver(() => {
        clearTimeout(t);
        t = setTimeout(() => {
          this.initializeAllSearches();
        }, 500);
      });
      const body = document.querySelector("body");
      if (body == null) {
        return;
      }
      o.observe(body, { subtree: true, childList: true });
    }
  },
  loadScriptStack: function(stack) {
    let scriptTag;
    if (stack.length > 0) {
      const script = stack.shift();
      if (script === void 0) {
        return;
      }
      scriptTag = document.createElement("script");
      scriptTag.src = script["src"];
      scriptTag.onload = () => {
        if (stack.length > 0) {
          this.loadScriptStack(stack);
        } else {
          if (typeof window.WPD.AjaxSearchPro != "undefined") {
            external_DoMini_default()._fn.plugin("ajaxsearchpro", window.WPD.AjaxSearchPro.plugin);
          }
          this.ready();
        }
      };
      document.body.appendChild(scriptTag);
    }
  },
  ready: function() {
    const $this = this;
    utils_onSafeDocumentReady(() => {
      $this.initialize();
    });
  },
  init: function() {
    if (ASP.script_async_load) {
      this.loadScriptStack(ASP.additional_scripts);
    } else {
      if (typeof window.WPD.AjaxSearchPro !== "undefined") {
        this.ready();
      }
    }
  }
};
/* harmony default export */ var asp = (ASP_EXTENDED);

;// external "window.WPD.intervalUntilExecute"
var external_window_WPD_intervalUntilExecute_namespaceObject = window.WPD.intervalUntilExecute;
;// ./src/client/plugin/wrapper/wrapper.js




function load() {
  if (typeof window.WPD.AjaxSearchPro != "undefined") {
    external_DoMini_namespaceObject._fn.plugin("ajaxsearchpro", window.WPD.AjaxSearchPro.plugin);
  }
  window.ASP = { ...window.ASP, ...asp };
  external_window_WPD_intervalUntilExecute_namespaceObject(() => window.ASP.init(), function() {
    return typeof window.ASP.version != "undefined";
  });
}

;// ./src/client/bundle/optimized/asp-wrapper.js


(function() {
  if (navigator.userAgent.indexOf("Chrome-Lighthouse") === -1) {
    if (typeof window.WPD != "undefined" && typeof window.WPD.dom != "undefined") {
      window.WPD.AjaxSearchPro._load = load;
      load();
    }
  }
})();

/******/ })()
;