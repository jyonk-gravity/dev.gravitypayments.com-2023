import AjaxSearchPro from "../base.js";
import {default as $} from "domini";

"use strict";
let helpers = AjaxSearchPro.helpers;

AjaxSearchPro.plugin.isRedirectToFirstResult = function () {
	let $this = this;
	// noinspection JSUnresolvedVariable
	return (
			$('.asp_res_url', $this.n('resultsDiv')).length > 0 ||
			$('.asp_es_' + $this.o.id + ' a').length > 0 ||
			($this.o.resPage.useAjax && $($this.o.resPage.selector + 'a').length > 0)
		) &&
		(
			($this.o.redirectOnClick && $this.ktype === 'click' && $this.o.trigger.click === 'first_result') ||
			($this.o.redirectOnEnter && ($this.ktype === 'input' || $this.ktype === 'keyup') && $this.keycode === 13 && $this.o.trigger.return === 'first_result') ||
			($this.ktype === 'button' && $this.o.sb.redirect_action === 'first_result')
		);
};

AjaxSearchPro.plugin.doRedirectToFirstResult = function () {
	let $this = this,
		_loc, url;

	if ($this.ktype === 'click') {
		_loc = $this.o.trigger.click_location;
	} else if ($this.ktype === 'button') {
		// noinspection JSUnresolvedVariable
		_loc = $this.o.sb.redirect_location;
	} else {
		_loc = $this.o.trigger.return_location;
	}

	if ($('.asp_res_url', $this.n('resultsDiv')).length > 0) {
		url = $($('.asp_res_url', $this.n('resultsDiv')).get(0)).attr('href');
	} else if ($('.asp_es_' + $this.o.id + ' a').length > 0) {
		url = $($('.asp_es_' + $this.o.id + ' a').get(0)).attr('href');
	} else if ($this.o.resPage.useAjax && $($this.o.resPage.selector + 'a').length > 0) {
		url = $($($this.o.resPage.selector + 'a').get(0)).attr('href');
	}

	if (url !== '') {
		if (_loc === 'same') {
			location.href = url;
		} else {
			helpers.openInNewTab(url);
		}

		$this.hideLoader();
		$this.hideResults();
	}
	return false;
};

AjaxSearchPro.plugin.doRedirectToResults = function (ktype) {
	let $this = this,
		_loc;

	if (typeof $this.reportSettingsValidity != 'undefined' && !$this.reportSettingsValidity()) {
		$this.showNextInvalidFacetMessage?.();
		return false;
	}

	if (ktype === 'click') {
		_loc = $this.o.trigger.click_location;
	} else if (ktype === 'button') {
		// noinspection JSUnresolvedVariable
		_loc = $this.o.sb.redirect_location;
	} else {
		_loc = $this.o.trigger.return_location;
	}
	let url = $this.getRedirectURL(ktype);

	// noinspection JSUnresolvedVariable
	if ($this.o.overridewpdefault) {
		// noinspection JSUnresolvedVariable
		if ($this.o.resPage.useAjax) {
			$this.hideResults();
			// noinspection JSUnresolvedVariable
			$this.liveLoad($this.o.resPage.selector, url);
			$this.showLoader();
			if ($this.att('blocking') === false) {
				$this.hideSettings?.();
			}
			return false;
		}
		// noinspection JSUnresolvedVariable
		if ($this.o.override_method === "post") {
			helpers.submitToUrl(url, 'post', {
				asp_active: 1,
				p_asid: $this.o.id,
				p_asp_data: $('form', $this.n('searchsettings')).serialize()
			}, _loc);
		} else {
			if (_loc === 'same') {
				location.href = url;
			} else {
				helpers.openInNewTab(url);
			}
		}
	} else {
		// The method is not important, just send the data to memorize settings
		helpers.submitToUrl(url, 'post', {
			np_asid: $this.o.id,
			np_asp_data: $('form', $this.n('searchsettings')).serialize()
		}, _loc);
	}

	$this.n('proloading').css('display', 'none');
	$this.hideLoader();
	if ($this.att('blocking') === false) $this.hideSettings?.();
	$this.hideResults();
	$this.searchAbort();
};
AjaxSearchPro.plugin.getRedirectURL = function (ktype) {
	let $this = this,
		url, source, final, base_url;
	ktype = typeof ktype !== 'undefined' ? ktype : 'enter';

	if (ktype === 'click') {
		source = $this.o.trigger.click;
	} else if (ktype === 'button') {
		source = $this.o.sb.redirect_action;
	} else {
		source = $this.o.trigger.return;
	}

	if (source === 'results_page') {
		url = '?s=' + helpers.nicePhrase($this.n('text').val());
	} else if (source === 'woo_results_page') {
		url = '?post_type=product&s=' + helpers.nicePhrase($this.n('text').val());
	} else {
		if (ktype === 'button') {
			base_url = source === 'elementor_page' ? $this.o.sb.elementor_url : $this.o.sb.redirect_url;
			// This function is heavy, do not do it on init
			base_url = helpers.decodeHTMLEntities(base_url);
			url = $this.parseCustomRedirectURL(base_url, $this.n('text').val());
		} else {
			base_url = source === 'elementor_page' ? $this.o.trigger.elementor_url : $this.o.trigger.redirect_url;
			// This function is heavy, do not do it on init
			base_url = helpers.decodeHTMLEntities(base_url);
			url = $this.parseCustomRedirectURL(base_url, $this.n('text').val());
		}
	}
	// Is this a URL like xy.com/?x=y
	if ($this.o.homeurl.indexOf('?') > 1 && url.indexOf('?') === 0) {
		url = url.replace('?', '&');
	}

	if ($this.o.overridewpdefault && $this.o.override_method !== 'post') {
		// We are about to add a query string to the URL, so it has to contain the '?' character somewhere.
		// ..if not, it has to be added
		let start = '&';
		if (($this.o.homeurl.indexOf('?') === -1 || source === 'elementor_page') && url.indexOf('?') === -1) {
			start = '?';
		}
		let addUrl = url + start + "asp_active=1&p_asid=" + $this.o.id + "&p_asp_data=1&" + $('form', $this.n('searchsettings')).serialize();
		if (source === 'elementor_page') {
			final = addUrl;
		} else {
			final = $this.o.homeurl + addUrl;
		}
	} else {
		if (source === 'elementor_page') {
			final = url;
		} else {
			final = $this.o.homeurl + url;
		}
	}

	// Double backslashes - negative lookbehind (?<!:) is not supported in all browsers yet, ECMA2018
	// This section should be only: final.replace(//(?<!:)\/\//g, '/');
	// Bypass solution, but it works at least everywhere
	final = final.replace('https://', 'https:///');
	final = final.replace('http://', 'http:///');
	final = final.replace(/\/\//g, '/');

	return helpers.Hooks.applyFilters('asp_redirect_url', final, $this.o.id, $this.o.iid);
};
AjaxSearchPro.plugin.parseCustomRedirectURL = function (url, phrase) {
	let $this = this,
		u = helpers.decodeHTMLEntities(url).replace(/{phrase}/g, helpers.nicePhrase(phrase)),
		items = u.match(/{(.*?)}/g);
	if (items !== null) {
		items.forEach(function (v) {
			v = v.replace(/[{}]/g, '');
			let node = $('input[type=radio][name*="aspf\[' + v + '_"]:checked', $this.n('searchsettings'));
			if (node.length === 0)
				node = $('input[type=text][name*="aspf\[' + v + '_"]', $this.n('searchsettings'));
			if (node.length === 0)
				node = $('input[type=hidden][name*="aspf\[' + v + '_"]', $this.n('searchsettings'));
			if (node.length === 0)
				node = $('select[name*="aspf\[' + v + '_"]:not([multiple])', $this.n('searchsettings'));
			if (node.length === 0)
				node = $('input[type=radio][name*="termset\[' + v + '"]:checked', $this.n('searchsettings'));
			if (node.length === 0)
				node = $('input[type=text][name*="termset\[' + v + '"]', $this.n('searchsettings'));
			if (node.length === 0)
				node = $('input[type=hidden][name*="termset\[' + v + '"]', $this.n('searchsettings'));
			if (node.length === 0)
				node = $('select[name*="termset\[' + v + '"]:not([multiple])', $this.n('searchsettings'));
			if (node.length === 0)
				return true; // Continue

			let val = node.val();
			val = "" + val; // Convert anything to string, okay-ish method
			u = u.replace('{' + v + '}', val);
		});
	}
	return u;
};

export default AjaxSearchPro;