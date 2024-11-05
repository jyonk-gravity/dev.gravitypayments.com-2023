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
  "default": function() { return /* binding */ asp_settings; }
});

;// CONCATENATED MODULE: external "AjaxSearchPro"
var external_AjaxSearchPro_namespaceObject = Object(window.WPD)["AjaxSearchPro"];
;// CONCATENATED MODULE: external "DoMini"
var external_DoMini_namespaceObject = Object(window.WPD)["DoMini"];
;// CONCATENATED MODULE: ./js/src/plugin/core/actions/settings.js



"use strict";
let helpers = external_AjaxSearchPro_namespaceObject.helpers;
external_AjaxSearchPro_namespaceObject.plugin.showSettings = function(animations) {
  let $this = this;
  $this.initSettings?.();
  animations = typeof animations == "undefined" ? true : animations;
  $this.n("s").trigger("asp_settings_show", [$this.o.id, $this.o.iid], true, true);
  if (!animations) {
    $this.n("searchsettings").css({
      "display": "block",
      "visibility": "visible",
      "opacity": 1
    });
  } else {
    $this.n("searchsettings").css($this.settAnim.showCSS);
    $this.n("searchsettings").removeClass($this.settAnim.hideClass).addClass($this.settAnim.showClass);
  }
  if ($this.o.fss_layout === "masonry" && $this.sIsotope == null && !(helpers.isMobile() && helpers.detectIOS())) {
    if (typeof rpp_isotope !== "undefined") {
      setTimeout(function() {
        let id = $this.n("searchsettings").attr("id");
        $this.n("searchsettings").css("width", "100%");
        $this.sIsotope = new rpp_isotope("#" + id + " form", {
          isOriginLeft: !external_DoMini_namespaceObject("body").hasClass("rtl"),
          itemSelector: "fieldset",
          layoutMode: "masonry",
          transitionDuration: 0,
          masonry: {
            columnWidth: $this.n("searchsettings").find("fieldset:not(.hiddend)").outerWidth()
          }
        });
      }, 20);
    } else {
      return false;
    }
  }
  if (typeof $this.select2jQuery != "undefined") {
    $this.select2jQuery($this.n("searchsettings").get(0)).find(".asp_gochosen,.asp_goselect2").trigger("change.asp_select2");
  }
  $this.n("prosettings").data("opened", 1);
  $this.fixSettingsPosition(true);
  $this.fixSettingsAccessibility();
};
external_AjaxSearchPro_namespaceObject.plugin.hideSettings = function() {
  let $this = this;
  $this.initSettings?.();
  $this.n("s").trigger("asp_settings_hide", [$this.o.id, $this.o.iid], true, true);
  $this.n("searchsettings").removeClass($this.settAnim.showClass).addClass($this.settAnim.hideClass);
  setTimeout(function() {
    $this.n("searchsettings").css($this.settAnim.hideCSS);
  }, $this.settAnim.duration);
  $this.n("prosettings").data("opened", 0);
  if ($this.sIsotope != null) {
    setTimeout(function() {
      $this.sIsotope.destroy();
      $this.sIsotope = null;
    }, $this.settAnim.duration);
  }
  if (typeof $this.select2jQuery != "undefined" && typeof $this.select2jQuery.fn.asp_select2 != "undefined") {
    $this.select2jQuery($this.n("searchsettings").get(0)).find(".asp_gochosen,.asp_goselect2").asp_select2("close");
  }
  $this.hideArrowBox?.();
};
external_AjaxSearchPro_namespaceObject.plugin.reportSettingsValidity = function() {
  let $this = this, valid = true;
  if ($this.n("searchsettings").css("visibility") === "hidden")
    return true;
  $this.n("searchsettings").find("fieldset.asp_required").forEach(function() {
    let $_this = external_DoMini_namespaceObject(this), fieldset_valid = true;
    $_this.find("input[type=text]:not(.asp_select2-search__field)").forEach(function() {
      if (external_DoMini_namespaceObject(this).val() === "") {
        fieldset_valid = false;
      }
    });
    $_this.find("select").forEach(function() {
      if (external_DoMini_namespaceObject(this).val() == null || external_DoMini_namespaceObject(this).val() === "" || external_DoMini_namespaceObject(this).closest("fieldset").is(".asp_filter_tax, .asp_filter_content_type") && parseInt(external_DoMini_namespaceObject(this).val()) === -1) {
        fieldset_valid = false;
      }
    });
    if ($_this.find("input[type=checkbox]").length > 0) {
      if ($_this.find("input[type=checkbox]:checked").length === 0) {
        fieldset_valid = false;
      } else if ($_this.find("input[type=checkbox]:checked").length === 1 && $_this.find("input[type=checkbox]:checked").val() === "") {
        fieldset_valid = false;
      }
    }
    if ($_this.find("input[type=radio]").length > 0) {
      if ($_this.find("input[type=radio]:checked").length === 0) {
        fieldset_valid = false;
      }
      if (fieldset_valid) {
        $_this.find("input[type=radio]").forEach(function() {
          if (external_DoMini_namespaceObject(this).prop("checked") && (external_DoMini_namespaceObject(this).val() === "" || external_DoMini_namespaceObject(this).closest("fieldset").is(".asp_filter_tax, .asp_filter_content_type") && parseInt(external_DoMini_namespaceObject(this).val()) === -1)) {
            fieldset_valid = false;
          }
        });
      }
    }
    if (!fieldset_valid) {
      $_this.addClass("asp-invalid");
      valid = false;
    } else {
      $_this.removeClass("asp-invalid");
    }
  });
  if (!valid) {
    $this.n("searchsettings").find("button.asp_s_btn").prop("disabled", true);
  }
  {
    $this.n("searchsettings").find("button.asp_s_btn").prop("disabled", false);
  }
  return valid;
};
external_AjaxSearchPro_namespaceObject.plugin.showArrowBox = function(element, text) {
  let $this = this, offsetTop, left, $body = external_DoMini_namespaceObject("body"), $box = $body.find(".asp_arrow_box");
  if ($box.length === 0) {
    $body.append("<div class='asp_arrow_box'></div>");
    $box = $body.find(".asp_arrow_box");
    $box.on("mouseout", function() {
      $this.hideArrowBox?.();
    });
  }
  let space = external_DoMini_namespaceObject(element).offset().top - window.scrollY, fixedp = false, n = element;
  while (n) {
    n = n.parentElement;
    if (n != null && window.getComputedStyle(n).position === "fixed") {
      fixedp = true;
      break;
    }
  }
  if (fixedp) {
    $box.css("position", "fixed");
    offsetTop = 0;
  } else {
    $box.css("position", "absolute");
    offsetTop = window.scrollY;
  }
  $box.html(text);
  $box.css("display", "block");
  left = element.getBoundingClientRect().left + external_DoMini_namespaceObject(element).outerWidth() / 2 - $box.outerWidth() / 2 + "px";
  if (space > 100) {
    $box.removeClass("asp_arrow_box_bottom");
    $box.css({
      top: offsetTop + element.getBoundingClientRect().top - $box.outerHeight() - 4 + "px",
      left
    });
  } else {
    $box.addClass("asp_arrow_box_bottom");
    $box.css({
      top: offsetTop + element.getBoundingClientRect().bottom + 4 + "px",
      left
    });
  }
};
external_AjaxSearchPro_namespaceObject.plugin.hideArrowBox = function() {
  external_DoMini_namespaceObject("body").find(".asp_arrow_box").css("display", "none");
};
external_AjaxSearchPro_namespaceObject.plugin.showNextInvalidFacetMessage = function() {
  let $this = this;
  if ($this.n("searchsettings").find(".asp-invalid").length > 0) {
    $this.showArrowBox(
      $this.n("searchsettings").find(".asp-invalid").first().get(0),
      $this.n("searchsettings").find(".asp-invalid").first().data("asp_invalid_msg")
    );
  }
};
external_AjaxSearchPro_namespaceObject.plugin.scrollToNextInvalidFacetMessage = function() {
  let $this = this;
  if ($this.n("searchsettings").find(".asp-invalid").length > 0) {
    let $n = $this.n("searchsettings").find(".asp-invalid").first();
    if (!$n.inViewPort(0)) {
      if (typeof $n.get(0).scrollIntoView != "undefined") {
        $n.get(0).scrollIntoView({ behavior: "smooth", block: "center", inline: "nearest" });
      } else {
        let stop = $n.offset().top - 20, $adminbar = external_DoMini_namespaceObject("#wpadminbar");
        if ($adminbar.length > 0)
          stop -= $adminbar.height();
        stop = stop < 0 ? 0 : stop;
        window.scrollTo({ top: stop, behavior: "smooth" });
      }
    }
  }
};
external_AjaxSearchPro_namespaceObject.plugin.settingsCheckboxToggle = function($node, checkState) {
  let $this = this;
  checkState = typeof checkState == "undefined" ? true : checkState;
  let $parent = $node, $checkbox = $node.find('input[type="checkbox"]'), lvl = parseInt($node.data("lvl")) + 1, i = 0;
  while (true) {
    $parent = $parent.next();
    if ($parent.length > 0 && typeof $parent.data("lvl") != "undefined" && parseInt($parent.data("lvl")) >= lvl) {
      if (checkState && $this.o.settings.unselectChildren) {
        $parent.find('input[type="checkbox"]').prop("checked", $checkbox.prop("checked"));
      }
      if ($this.o.settings.hideChildren) {
        if ($checkbox.prop("checked")) {
          $parent.removeClass("hiddend");
        } else {
          $parent.addClass("hiddend");
        }
      }
    } else {
      break;
    }
    i++;
    if (i > 400) break;
  }
};
/* harmony default export */ var settings = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: external "intervalUntilExecute"
var external_intervalUntilExecute_namespaceObject = Object(window.WPD)["intervalUntilExecute"];
;// CONCATENATED MODULE: ./js/src/plugin/core/events/datepicker.js




"use strict";
let datepicker_helpers = external_AjaxSearchPro_namespaceObject.helpers;
external_AjaxSearchPro_namespaceObject.plugin.initDatePicker = function() {
  let $this = this;
  external_intervalUntilExecute_namespaceObject(function(_$) {
    function onSelectEvent(dateText, inst, _this, nochange, nochage) {
      let obj;
      if (_this != null) {
        obj = _$(_this);
      } else {
        obj = _$("#" + inst.id);
      }
      let prevValue = _$(".asp_datepicker_hidden", _$(obj).parent()).val(), newValue = "";
      if (obj.datepicker("getDate") == null) {
        _$(".asp_datepicker_hidden", _$(obj).parent()).val("");
      } else {
        let d = String(obj.datepicker("getDate")), date = new Date(d.match(/(.*?)00:/)[1].trim()), year = String(date.getFullYear()), month = ("0" + (date.getMonth() + 1)).slice(-2), day = ("0" + String(date.getDate())).slice(-2);
        newValue = year + "-" + month + "-" + day;
        _$(".asp_datepicker_hidden", _$(obj).parent()).val(newValue);
      }
      if ((typeof nochage == "undefined" || nochange == null) && newValue !== prevValue)
        external_DoMini_namespaceObject(obj.get(0)).trigger("change");
    }
    _$(".asp_datepicker, .asp_datepicker_field", $this.n("searchsettings").get(0)).each(function() {
      let format = _$(".asp_datepicker_format", _$(this).parent()).val(), _this = this, origValue = _$(this).val();
      _$(this).removeClass("hasDatepicker");
      _$(this).datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "yy-mm-dd",
        onSelect: onSelectEvent,
        beforeShow: function() {
          _$("#ui-datepicker-div").addClass("asp-ui");
        }
      });
      if (origValue === "") {
        _$(this).datepicker("setDate", "");
      } else {
        _$(this).datepicker("setDate", origValue);
      }
      _$(this).datepicker("option", "dateFormat", format);
      onSelectEvent(null, null, _this, true);
      _$(this).on("selectnochange", function() {
        onSelectEvent(null, null, _this, true);
      });
      _$(this).on("keyup", function() {
        if (_$(_this).datepicker("getDate") == null) {
          _$(".asp_datepicker_hidden", _$(_this).parent()).val("");
        }
        _$(_this).datepicker("hide");
      });
    });
    if (datepicker_helpers.isMobile() && datepicker_helpers.detectIOS()) {
      _$(window).on("pageshow", function(e) {
        if (e.originalEvent.persisted) {
          setTimeout(function() {
            _$(".asp_datepicker, .asp_datepicker_field", $this.n("searchsettings").get(0)).each(function() {
              let format = _$(this).datepicker("option", "dateFormat");
              _$(this).datepicker("option", "dateFormat", "yy-mm-dd");
              _$(this).datepicker("setDate", _$(this).next(".asp_datepicker_hidden").val());
              _$(this).datepicker("option", "dateFormat", format);
            });
          }, 100);
        }
      });
    }
  }, function() {
    return datepicker_helpers.whichjQuery("datepicker");
  });
};
/* harmony default export */ var datepicker = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/events/facet.js



"use strict";
let facet_helpers = external_AjaxSearchPro_namespaceObject.helpers;
external_AjaxSearchPro_namespaceObject.plugin.initFacetEvents = function() {
  let $this = this, gtagTimer = null, inputCorrectionTimer = null;
  external_DoMini_namespaceObject(".asp_custom_f input[type=text]:not(.asp_select2-search__field):not(.asp_datepicker_field):not(.asp_datepicker)", $this.n("searchsettings")).on("input", function(e) {
    let code = e.keyCode || e.which, _this = this;
    $this.ktype = e.type;
    if (code === 13) {
      e.preventDefault();
      e.stopImmediatePropagation();
    }
    if (external_DoMini_namespaceObject(this).data("asp-type") === "number") {
      if (this.value !== "") {
        let inputVal = this.value.replaceAll(external_DoMini_namespaceObject(this).data("asp-tsep"), "");
        let correctedVal = facet_helpers.inputToFloat(this.value);
        let _this2 = this;
        _this2.value = correctedVal;
        correctedVal = correctedVal < parseFloat(external_DoMini_namespaceObject(this).data("asp-min")) ? external_DoMini_namespaceObject(this).data("asp-min") : correctedVal;
        correctedVal = correctedVal > parseFloat(external_DoMini_namespaceObject(this).data("asp-max")) ? external_DoMini_namespaceObject(this).data("asp-max") : correctedVal;
        clearTimeout(inputCorrectionTimer);
        inputCorrectionTimer = setTimeout(function() {
          _this2.value = facet_helpers.addThousandSeparators(correctedVal, external_DoMini_namespaceObject(_this2).data("asp-tsep"));
        }, 400);
        if (correctedVal.toString() !== inputVal) {
          return false;
        }
      }
    }
    clearTimeout(gtagTimer);
    gtagTimer = setTimeout(function() {
      $this.gaEvent?.("facet_change", {
        "option_label": external_DoMini_namespaceObject(_this).closest("fieldset").find("legend").text(),
        "option_value": external_DoMini_namespaceObject(_this).val()
      });
    }, 1400);
    $this.n("searchsettings").find("input[name=filters_changed]").val(1);
    $this.setFilterStateInput(65);
    if ($this.o.trigger.facet)
      $this.searchWithCheck(240);
  });
  $this.n("searchsettings").find(".asp-number-range[data-asp-tsep]").forEach(function() {
    this.value = facet_helpers.addThousandSeparators(this.value, external_DoMini_namespaceObject(this).data("asp-tsep"));
  });
  if (!$this.o.trigger.facet) return;
  external_DoMini_namespaceObject("select", $this.n("searchsettings")).on("change slidechange", function(e) {
    $this.ktype = e.type;
    $this.n("searchsettings").find("input[name=filters_changed]").val(1);
    $this.gaEvent?.("facet_change", {
      "option_label": external_DoMini_namespaceObject(this).closest("fieldset").find("legend").text(),
      "option_value": external_DoMini_namespaceObject(this).find("option:checked").get().map(function(item) {
        return item.text;
      }).join()
    });
    $this.setFilterStateInput(65);
    $this.searchWithCheck(80);
    if ($this.sIsotope != null) {
      $this.sIsotope.arrange();
    }
  });
  external_DoMini_namespaceObject("input:not([type=checkbox]):not([type=text]):not([type=radio])", $this.n("searchsettings")).on("change slidechange", function(e) {
    $this.ktype = e.type;
    $this.n("searchsettings").find("input[name=filters_changed]").val(1);
    $this.gaEvent?.("facet_change", {
      "option_label": external_DoMini_namespaceObject(this).closest("fieldset").find("legend").text(),
      "option_value": external_DoMini_namespaceObject(this).val()
    });
    $this.setFilterStateInput(65);
    $this.searchWithCheck(80);
  });
  external_DoMini_namespaceObject("input[type=radio]", $this.n("searchsettings")).on("change slidechange", function(e) {
    $this.ktype = e.type;
    $this.n("searchsettings").find("input[name=filters_changed]").val(1);
    $this.gaEvent?.("facet_change", {
      "option_label": external_DoMini_namespaceObject(this).closest("fieldset").find("legend").text(),
      "option_value": external_DoMini_namespaceObject(this).closest("label").text()
    });
    $this.setFilterStateInput(65);
    $this.searchWithCheck(80);
  });
  external_DoMini_namespaceObject("input[type=checkbox]", $this.n("searchsettings")).on("asp_chbx_change", function(e) {
    $this.ktype = e.type;
    $this.n("searchsettings").find("input[name=filters_changed]").val(1);
    $this.gaEvent?.("facet_change", {
      "option_label": external_DoMini_namespaceObject(this).closest("fieldset").find("legend").text(),
      "option_value": external_DoMini_namespaceObject(this).closest(".asp_option").find(".asp_option_label").text() + (external_DoMini_namespaceObject(this).prop("checked") ? "(checked)" : "(unchecked)")
    });
    $this.setFilterStateInput(65);
    $this.searchWithCheck(80);
  });
  external_DoMini_namespaceObject("input.asp_datepicker, input.asp_datepicker_field", $this.n("searchsettings")).on("change", function(e) {
    $this.ktype = e.type;
    $this.n("searchsettings").find("input[name=filters_changed]").val(1);
    $this.gaEvent?.("facet_change", {
      "option_label": external_DoMini_namespaceObject(this).closest("fieldset").find("legend").text(),
      "option_value": external_DoMini_namespaceObject(this).val()
    });
    $this.setFilterStateInput(65);
    $this.searchWithCheck(80);
  });
  external_DoMini_namespaceObject('div[id*="-handles"]', $this.n("searchsettings")).forEach(function(e) {
    $this.ktype = e.type;
    if (typeof this.noUiSlider != "undefined") {
      this.noUiSlider.on("change", function(values) {
        let target = typeof this.target != "undefined" ? this.target : this;
        $this.gaEvent?.("facet_change", {
          "option_label": external_DoMini_namespaceObject(target).closest("fieldset").find("legend").text(),
          "option_value": values
        });
        $this.n("searchsettings").find("input[name=filters_changed]").val(1);
        $this.setFilterStateInput(65);
        $this.searchWithCheck(80);
      });
    }
  });
};
/* harmony default export */ var facet = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/events/noui.js



"use strict";
external_AjaxSearchPro_namespaceObject.plugin.initNoUIEvents = function() {
  let $this = this, $sett = $this.nodes.searchsettings, slider;
  $sett.find("div[class*=noui-slider-json]").forEach(function(el, index) {
    let jsonData = external_DoMini_namespaceObject(this).data("aspnoui");
    if (typeof jsonData === "undefined") return false;
    jsonData = WPD.Base64.decode(jsonData);
    if (typeof jsonData === "undefined" || jsonData === "") return false;
    let args = JSON.parse(jsonData);
    Object.keys(args.links).forEach(function(k) {
      args.links[k].target = "#" + $sett.get(0).id + " " + args.links[k].target;
    });
    if (external_DoMini_namespaceObject(args.node, $sett).length > 0) {
      slider = external_DoMini_namespaceObject(args.node, $sett).get(0);
      let $handles = external_DoMini_namespaceObject(el).parent().find(".asp_slider_hidden");
      if ($handles.length > 1) {
        args.main.start = [$handles.first().val(), $handles.last().val()];
      } else {
        args.main.start = [$handles.first().val()];
      }
      if (typeof noUiSlider !== "undefined") {
        if (typeof slider.noUiSlider != "undefined") {
          slider.noUiSlider.destroy();
        }
        slider.innerHTML = "";
        noUiSlider.create(slider, args.main);
      } else {
        return false;
      }
      $this.noUiSliders[index] = slider;
      slider.noUiSlider.on("update", function(values, handle) {
        let value = values[handle];
        if (handle) {
          args.links.forEach(function(el2) {
            let wn = wNumb(el2.wNumb);
            if (el2.handle === "upper") {
              if (external_DoMini_namespaceObject(el2.target, $sett).is("input"))
                external_DoMini_namespaceObject(el2.target, $sett).val(value);
              else
                external_DoMini_namespaceObject(el2.target, $sett).html(wn.to(parseFloat(value)));
            }
            external_DoMini_namespaceObject(args.node, $sett).on("slide", function(e) {
              e.preventDefault();
            });
          });
        } else {
          args.links.forEach(function(el2) {
            let wn = wNumb(el2.wNumb);
            if (el2.handle === "lower") {
              if (external_DoMini_namespaceObject(el2.target, $sett).is("input"))
                external_DoMini_namespaceObject(el2.target, $sett).val(value);
              else
                external_DoMini_namespaceObject(el2.target, $sett).html(wn.to(parseFloat(value)));
            }
            external_DoMini_namespaceObject(args.node, $sett).on("slide", function(e) {
              e.preventDefault();
            });
          });
        }
      });
    }
  });
};
/* harmony default export */ var noui = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/events/settings.js



"use strict";
let settings_helpers = external_AjaxSearchPro_namespaceObject.helpers;
external_AjaxSearchPro_namespaceObject.plugin.initSettingsSwitchEvents = function() {
  let $this = this;
  $this.n("prosettings").on("click", function() {
    if ($this.n("prosettings").data("opened") === "0") {
      $this.showSettings?.();
    } else {
      $this.hideSettings?.();
    }
  });
  if (settings_helpers.isMobile()) {
    if ($this.o.mobile.force_sett_state === "open" || $this.o.mobile.force_sett_state === "none" && $this.o.settingsVisible) {
      $this.showSettings?.(false);
    }
  } else {
    if ($this.o.settingsVisible) {
      $this.showSettings?.(false);
    }
  }
};
external_AjaxSearchPro_namespaceObject.plugin.initSettingsEvents = function() {
  let $this = this, t;
  let formDataHandler = function() {
    if (typeof $this.originalFormData === "undefined") {
      $this.originalFormData = settings_helpers.formData(external_DoMini_namespaceObject("form", $this.n("searchsettings")));
    }
    $this.n("searchsettings").off("mousedown touchstart mouseover", formDataHandler);
  };
  $this.n("searchsettings").on("mousedown touchstart mouseover", formDataHandler);
  let handler = function(e) {
    if (external_DoMini_namespaceObject(e.target).closest(".asp_w").length === 0) {
      if (!$this.att("blocking") && !$this.dragging && external_DoMini_namespaceObject(e.target).closest(".ui-datepicker").length === 0 && external_DoMini_namespaceObject(e.target).closest(".noUi-handle").length === 0 && external_DoMini_namespaceObject(e.target).closest(".asp_select2").length === 0 && external_DoMini_namespaceObject(e.target).closest(".asp_select2-container").length === 0) {
        $this.hideSettings?.();
      }
    }
  };
  $this.documentEventHandlers.push({
    "node": document,
    "event": $this.clickTouchend,
    "handler": handler
  });
  external_DoMini_namespaceObject(document).on($this.clickTouchend, handler);
  const setOptionCheckedClass = () => {
    $this.n("searchsettings").find(".asp_option, .asp_label").forEach(function(el) {
      if (external_DoMini_namespaceObject(el).find("input").prop("checked")) {
        external_DoMini_namespaceObject(el).addClass("asp_option_checked");
      } else {
        external_DoMini_namespaceObject(el).removeClass("asp_option_checked");
      }
    });
  };
  setOptionCheckedClass();
  $this.n("searchsettings").on("click", function() {
    $this.settingsChanged = true;
  });
  $this.n("searchsettings").on($this.clickTouchend, function(e) {
    if (!$this.dragging) {
      $this.updateHref();
    }
    if (typeof e.target != "undefined" && !external_DoMini_namespaceObject(e.target).hasClass("noUi-handle")) {
      e.stopImmediatePropagation();
    } else {
      if (e.type === "click")
        e.stopImmediatePropagation();
    }
  });
  external_DoMini_namespaceObject('.asp_option_cat input[type="checkbox"]', $this.n("searchsettings")).on("asp_chbx_change", function() {
    $this.settingsCheckboxToggle(external_DoMini_namespaceObject(this).closest(".asp_option_cat"));
    setOptionCheckedClass();
  });
  external_DoMini_namespaceObject('input[type="radio"]', $this.n("searchsettings")).on("change", function() {
    setOptionCheckedClass();
  });
  external_DoMini_namespaceObject(".asp_option_cat", $this.n("searchsettings")).forEach(function(el) {
    $this.settingsCheckboxToggle(external_DoMini_namespaceObject(el), false);
  });
  external_DoMini_namespaceObject("div.asp_option", $this.n("searchsettings")).on($this.mouseupTouchend, function(e) {
    e.preventDefault();
    e.stopImmediatePropagation();
    if ($this.dragging) {
      return false;
    }
    external_DoMini_namespaceObject(this).find('input[type="checkbox"]').prop("checked", !external_DoMini_namespaceObject(this).find('input[type="checkbox"]').prop("checked"));
    clearTimeout(t);
    let _this = this;
    t = setTimeout(function() {
      external_DoMini_namespaceObject(_this).find('input[type="checkbox"]').trigger("asp_chbx_change");
    }, 50);
  });
  external_DoMini_namespaceObject("div.asp_option", $this.n("searchsettings")).on("keyup", function(e) {
    e.preventDefault();
    let keycode = e.keyCode || e.which;
    if (keycode === 13 || keycode === 32) {
      external_DoMini_namespaceObject(this).trigger("mouseup");
    }
  });
  external_DoMini_namespaceObject("fieldset.asp_checkboxes_filter_box", $this.n("searchsettings")).forEach(function() {
    let all_unchecked = true;
    external_DoMini_namespaceObject(this).find('.asp_option:not(.asp_option_selectall) input[type="checkbox"]').forEach(function() {
      if (external_DoMini_namespaceObject(this).prop("checked")) {
        all_unchecked = false;
        return false;
      }
    });
    if (all_unchecked) {
      external_DoMini_namespaceObject(this).find('.asp_option_selectall input[type="checkbox"]').prop("checked", false).removeAttr("data-origvalue");
    }
  });
  external_DoMini_namespaceObject("fieldset", $this.n("searchsettings")).forEach(function() {
    external_DoMini_namespaceObject(this).find(".asp_option:not(.hiddend)").last().addClass("asp-o-last");
  });
  external_DoMini_namespaceObject('.asp_option input[type="checkbox"]', $this.n("searchsettings")).on("asp_chbx_change", function() {
    let className = external_DoMini_namespaceObject(this).data("targetclass");
    if (typeof className == "string" && className !== "") {
      external_DoMini_namespaceObject("input." + className, $this.n("searchsettings")).prop("checked", external_DoMini_namespaceObject(this).prop("checked"));
    }
    setOptionCheckedClass();
  });
};
/* harmony default export */ var events_settings = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/init/settings.js



"use strict";
let init_settings_helpers = external_AjaxSearchPro_namespaceObject.helpers;
external_AjaxSearchPro_namespaceObject.plugin.initSettings = function() {
  if (!this.settingsInitialized) {
    this.loadASPFonts?.();
    this.initSettingsBox?.();
    this.initSettingsEvents?.();
    this.initButtonEvents?.();
    this.initNoUIEvents?.();
    this.initDatePicker?.();
    this.initSelect2?.();
    this.initFacetEvents?.();
  }
};
external_AjaxSearchPro_namespaceObject.plugin.initSettingsBox = function() {
  let $this = this;
  let appendSettingsTo = function($el) {
    let old = $this.n("searchsettings").get(0);
    $this.nodes.searchsettings = $this.nodes.searchsettings.clone();
    $el.append($this.nodes.searchsettings);
    external_DoMini_namespaceObject(old).find("*[id]").forEach(function(el) {
      if (el.id.indexOf("__original__") < 0) {
        el.id = "__original__" + el.id;
      }
    });
    $this.n("searchsettings").find("*[id]").forEach(function(el) {
      if (el.id.indexOf("__original__") > -1) {
        el.id = el.id.replace("__original__", "");
      }
    });
  };
  let makeSetingsBlock = function() {
    $this.n("searchsettings").attr(
      "id",
      $this.n("searchsettings").attr("id").replace("prosettings", "probsettings")
    );
    $this.n("searchsettings").removeClass("asp_s asp_s_" + $this.o.id + " asp_s_" + $this.o.rid).addClass("asp_sb asp_sb_" + $this.o.id + " asp_sb_" + $this.o.rid);
    $this.dynamicAtts["blocking"] = true;
  };
  let makeSetingsHover = function() {
    $this.n("searchsettings").attr(
      "id",
      $this.n("searchsettings").attr("id").replace("probsettings", "prosettings")
    );
    $this.n("searchsettings").removeClass("asp_sb asp_sb_" + $this.o.id + " asp_sb_" + $this.o.rid).addClass("asp_s asp_s_" + $this.o.id + " asp_s_" + $this.o.rid);
    $this.dynamicAtts["blocking"] = false;
  };
  $this.initSettingsAnimations?.();
  if ($this.o.compact.enabled && $this.o.compact.position === "fixed" || init_settings_helpers.isMobile() && $this.o.mobile.force_sett_hover) {
    makeSetingsHover();
    appendSettingsTo(external_DoMini_namespaceObject("body"));
    $this.n("searchsettings").css({
      "position": "absolute"
    });
    $this.dynamicAtts["blocking"] = false;
  } else {
    if ($this.n("settingsAppend").length > 0) {
      if ($this.n("settingsAppend").find(".asp_ss_" + $this.o.id).length > 0) {
        $this.nodes.searchsettings = $this.nodes.settingsAppend.find(".asp_ss_" + $this.o.id);
        if (typeof $this.nodes.searchsettings.get(0).referenced !== "undefined") {
          ++$this.nodes.searchsettings.get(0).referenced;
        } else {
          $this.nodes.searchsettings.get(0).referenced = 1;
        }
      } else {
        if (!$this.att("blocking")) {
          makeSetingsBlock();
        }
        appendSettingsTo($this.nodes.settingsAppend);
      }
    } else if (!$this.att("blocking")) {
      appendSettingsTo(external_DoMini_namespaceObject("body"));
    }
  }
  $this.n("searchsettings").get(0).id = $this.n("searchsettings").get(0).id.replace("__original__", "");
  $this.detectAndFixFixedPositioning();
  $this.settingsInitialized = true;
};
external_AjaxSearchPro_namespaceObject.plugin.initSettingsAnimations = function() {
  let $this = this;
  $this.settAnim = {
    "showClass": "",
    "showCSS": {
      "visibility": "visible",
      "display": "block",
      "opacity": 1,
      "animation-duration": $this.animOptions.settings.dur + "ms"
    },
    "hideClass": "",
    "hideCSS": {
      "visibility": "hidden",
      "opacity": 0,
      "display": "none"
    },
    "duration": $this.animOptions.settings.dur + "ms"
  };
  if ($this.animOptions.settings.anim === "fade") {
    $this.settAnim.showClass = "asp_an_fadeIn";
    $this.settAnim.hideClass = "asp_an_fadeOut";
  }
  if ($this.animOptions.settings.anim === "fadedrop" && !$this.att("blocking")) {
    $this.settAnim.showClass = "asp_an_fadeInDrop";
    $this.settAnim.hideClass = "asp_an_fadeOutDrop";
  } else if ($this.animOptions.settings.anim === "fadedrop") {
    $this.settAnim.showClass = "asp_an_fadeIn";
    $this.settAnim.hideClass = "asp_an_fadeOut";
  }
  $this.n("searchsettings").css({
    "-webkit-animation-duration": $this.settAnim.duration + "ms",
    "animation-duration": $this.settAnim.duration + "ms"
  });
};
/* harmony default export */ var init_settings = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/bundle/optimized/asp-settings.js








/* harmony default export */ var asp_settings = (external_AjaxSearchPro_namespaceObject);

Object(window.WPD).AjaxSearchPro = __webpack_exports__["default"];
/******/ })()
;