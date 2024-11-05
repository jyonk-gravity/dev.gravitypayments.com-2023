import AjaxSearchPro from "../base.js";
import {default as $} from "domini";

"use strict";

AjaxSearchPro.helpers.Hooks = window.WPD.Hooks;

AjaxSearchPro.helpers.deviceType = function () {
	let w = window.innerWidth;
	if (w <= 640) {
		return 'phone';
	} else if (w <= 1024) {
		return 'tablet';
	} else {
		return 'desktop';
	}
}
AjaxSearchPro.helpers.detectIOS = function () {
	if (
		typeof window.navigator != "undefined" &&
		typeof window.navigator.userAgent != "undefined"
	)
		return window.navigator.userAgent.match(/(iPod|iPhone|iPad)/) != null;
	return false;
}

AjaxSearchPro.helpers.isMobile = function () {
	try {
		document.createEvent("TouchEvent");
		return true;
	} catch (e) {
		return false;
	}
}
AjaxSearchPro.helpers.isTouchDevice = function () {
	return "ontouchstart" in window;
}

AjaxSearchPro.helpers.isSafari = function () {
	return (/^((?!chrome|android).)*safari/i).test(navigator.userAgent);
}

AjaxSearchPro.helpers.escapeHtml = function (unsafe) {
	return unsafe.replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
}

/**
 * Gets the jQuery object, if "plugin" defined, then also checks if the plugin exists
 * @param plugin
 * @returns {boolean|function}
 */
AjaxSearchPro.helpers.whichjQuery = function (plugin) {
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
}
AjaxSearchPro.helpers.formData = function (form, data) {
	let $this = this,
		els = form.find('input,textarea,select,button').get();
	if (arguments.length === 1) {
		// return all data
		data = {};

		els.forEach(function (el) {
			if (el.name && !el.disabled && (el.checked
				|| /select|textarea/i.test(el.nodeName)
				|| /text/i.test(el.type)
				|| $(el).hasClass('hasDatepicker')
				|| $(el).hasClass('asp_slider_hidden'))
			) {
				if (data[el.name] === undefined) {
					data[el.name] = [];
				}
				if ($(el).hasClass('hasDatepicker')) {
					data[el.name].push($(el).parent().find('.asp_datepicker_hidden').val());
				} else {
					data[el.name].push($(el).val());
				}
			}
		});
		return JSON.stringify(data);
	} else {
		if (typeof data != "object") {
			data = JSON.parse(data);
		}
		els.forEach(function (el) {
			if (el.name) {
				if (data[el.name]) {
					let names = data[el.name],
						_this = $(el);
					if (Object.prototype.toString.call(names) !== '[object Array]') {
						names = [names]; //backwards compat to old version of this code
					}
					if (el.type === 'checkbox' || el.type === 'radio') {
						let val = _this.val(),
							found = false;
						for (let i = 0; i < names.length; i++) {
							if (names[i] === val) {
								found = true;
								break;
							}
						}
						_this.prop("checked", found);
					} else {
						_this.val(names[0]);

						if ($(el).hasClass('asp_gochosen') || $(el).hasClass('asp_goselect2')) {
							WPD.intervalUntilExecute(function (_$) {
								_$(el).trigger("change.asp_select2");
							}, function () {
								return $this.whichjQuery('asp_select2');
							}, 50, 3);
						} else if ($(el).hasClass('hasDatepicker')) {
							WPD.intervalUntilExecute(function (_$) {
								let value = names[0],
									format = _$(_this.get(0)).datepicker("option", 'dateFormat');
								_$(_this.get(0)).datepicker("option", 'dateFormat', 'yy-mm-dd');
								_$(_this.get(0)).datepicker("setDate", value);
								_$(_this.get(0)).datepicker("option", 'dateFormat', format);
								_$(_this.get(0)).trigger('selectnochange');
							}, function () {
								return $this.whichjQuery('datepicker');
							}, 50, 3);
						}
					}
				} else {
					if (el.type === 'checkbox' || el.type === 'radio') {
						$(el).prop("checked", false);
					}
				}
			}
		});

		return form;
	}
}
AjaxSearchPro.helpers.submitToUrl = function (action, method, input, target) {
	let form;
	form = $('<form style="display: none;" />');
	form.attr('action', action);
	form.attr('method', method);
	$('body').append(form);
	if (typeof input !== 'undefined' && input !== null) {
		Object.keys(input).forEach(function (name) {
			let value = input[name];
			let $input = $('<input type="hidden" />');
			$input.attr('name', name);
			$input.attr('value', value);
			form.append($input);
		});
	}
	if (typeof (target) != 'undefined' && target === 'new') {
		form.attr('target', '_blank');
	}
	form.get(0).submit();
}
AjaxSearchPro.helpers.openInNewTab = function (url) {
	Object.assign(document.createElement('a'), {target: '_blank', href: url}).click();
}
AjaxSearchPro.helpers.isScrolledToBottom = function (el, tolerance) {
	return el.scrollHeight - el.scrollTop - $(el).outerHeight() < tolerance;
}
AjaxSearchPro.helpers.getWidthFromCSSValue = function (width, containerWidth) {
	let min = 100,
		ret;

	width = width + '';
	// Pixel value
	if (width.indexOf('px') > -1) {
		ret = parseInt(width, 10);
	} else if (width.indexOf('%') > -1) {
		// % value, calculate against the container
		if (typeof containerWidth != 'undefined' && containerWidth != null) {
			ret = Math.floor(parseInt(width, 10) / 100 * containerWidth);
		} else {
			ret = parseInt(width, 10);
		}
	} else {
		ret = parseInt(width, 10);
	}

	return ret < 100 ? min : ret;
}

AjaxSearchPro.helpers.nicePhrase = function (s) {
	// noinspection RegExpRedundantEscape
	return encodeURIComponent(s).replace(/\%20/g, '+');
}

/**
 * Used for input fields to only restrict to valid number user inputs
 */
AjaxSearchPro.helpers.inputToFloat = function (input) {
	return input.replace(/^[.]/g, '').replace(/[^0-9.-]/g, '').replace(/^[-]/g, 'x').replace(/[-]/g, '').replace(/[x]/g, '-').replace(/(\..*?)\..*/g, '$1');
}

AjaxSearchPro.helpers.addThousandSeparators = function (n, s) {
	if (s !== '') {
		s = s || ",";
		return String(n).replace(/(?:^|[^.\d])\d+/g, function (n) {
			return n.replace(/\B(?=(?:\d{3})+\b)/g, s);
		});
	} else {
		return n;
	}
}

AjaxSearchPro.helpers.decodeHTMLEntities = function (str) {
	let element = document.createElement('div');
	if (str && typeof str === 'string') {
		// strip script/html tags
		str = str.replace(/<script[^>]*>([\S\s]*?)<\/script>/gmi, '');
		str = str.replace(/<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gmi, '');
		element.innerHTML = str;
		str = element.textContent;
		element.textContent = '';
	}
	return str;
}

AjaxSearchPro.helpers.isScrolledToRight = function (el) {
	return el.scrollWidth - $(el).outerWidth() === el.scrollLeft;
}

AjaxSearchPro.helpers.isScrolledToLeft = function (el) {
	return el.scrollLeft === 0;
}

export default AjaxSearchPro;