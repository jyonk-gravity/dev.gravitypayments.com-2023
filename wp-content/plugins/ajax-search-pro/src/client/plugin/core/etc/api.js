import AjaxSearchPro from "../base.js";
import {default as $} from "domini";

"use strict";
let helpers = AjaxSearchPro.helpers;

AjaxSearchPro.plugin.searchFor = function (phrase) {
	if (typeof phrase != 'undefined') {
		this.n('text').val(phrase);
	}
	this.n('textAutocomplete').val('');
	this.search(false, false, false, true);
}

AjaxSearchPro.plugin.searchRedirect = function (phrase) {
	let url = this.parseCustomRedirectURL(this.o.trigger.redirect_url, phrase);

	// Is this an URL like xy.com/?x=y
	// noinspection JSUnresolvedVariable
	if (this.o.homeurl.indexOf('?') > 1 && url.indexOf('?') === 0) {
		url = url.replace('?', '&');
	}

	// noinspection JSUnresolvedVariable
	if (this.o.overridewpdefault) {
		// noinspection JSUnresolvedVariable
		if (this.o.override_method === "post") {
			// noinspection JSUnresolvedVariable
			helpers.submitToUrl(this.o.homeurl + url, 'post', {
				asp_active: 1,
				p_asid: this.o.id,
				p_asp_data: $('form', this.n('searchsettings')).serialize()
			});
		} else {
			// noinspection JSUnresolvedVariable
			location.href = this.o.homeurl + url + "&asp_active=1&p_asid=" + this.o.id + "&p_asp_data=1&" + $('form', this.n('searchsettings')).serialize();
		}
	} else {
		// The method is not important, just send the data to memorize settings
		// noinspection JSUnresolvedVariable
		helpers.submitToUrl(this.o.homeurl + url, 'post', {
			np_asid: this.o.id,
			np_asp_data: $('form', this.n('searchsettings')).serialize()
		});
	}
}

AjaxSearchPro.plugin.toggleSettings = function (state) {
	// state explicitly given, force behavior
	if (typeof state != 'undefined') {
		if (state === "show") {
			this.showSettings?.();
		} else {
			this.hideSettings?.();
		}
	} else {
		if (this.n('prosettings').data('opened') === "1") {
			this.hideSettings?.();
		} else {
			this.showSettings?.();
		}
	}
}

AjaxSearchPro.plugin.closeResults = function (clear) {
	if (typeof (clear) != 'undefined' && clear) {
		this.n('text').val("");
		this.n('textAutocomplete').val("");
	}
	this.hideResults();
	this.n('proloading').css('display', 'none');
	this.hideLoader();
	this.searchAbort();
}

AjaxSearchPro.plugin.getStateURL = function () {
	let url = location.href,
		sep;
	url = url.split('p_asid');
	url = url[0];
	url = url.replace('&asp_active=1', '');
	url = url.replace('?asp_active=1', '');
	url = url.slice(-1) === '?' ? url.slice(0, -1) : url;
	url = url.slice(-1) === '&' ? url.slice(0, -1) : url;
	sep = url.indexOf('?') > 1 ? '&' : '?';
	return url + sep + "p_asid=" + this.o.id + "&p_asp_data=1&" + $('form', this.n('searchsettings')).serialize();
}

AjaxSearchPro.plugin.resetSearch = function () {
	this.resetSearchFilters();
}

AjaxSearchPro.plugin.filtersInitial = function () {
	return this.n('searchsettings').find('input[name=filters_initial]').val() === '1';
}

AjaxSearchPro.plugin.filtersChanged = function () {
	return this.n('searchsettings').find('input[name=filters_changed]').val() === '1';
}

export default AjaxSearchPro;