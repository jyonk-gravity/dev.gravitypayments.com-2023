import AjaxSearchPro from "../base.js";
import {default as $} from "domini";

"use strict";
let helpers = AjaxSearchPro.helpers;

AjaxSearchPro.plugin.liveLoad = function (origSelector, url, updateLocation, forceAjax, cache) {
	let selector = origSelector;
	if (selector === 'body' || selector === 'html') {
		console.log('Ajax Search Pro: Do not use html or body as the live loader selector.');
		return false;
	}

	let $this = this;

	if (ASP.pageHTML !== "") {
		$this.setLiveLoadCache(ASP.pageHTML, origSelector);
	}

	function process(html) {
		let data = helpers.Hooks.applyFilters('asp/live_load/raw_data', html, $this);
		let parser = new DOMParser;
		let dataNode = parser.parseFromString(data, "text/html");
		let $dataNode = $(dataNode);

		// noinspection JSUnresolvedVariable
		if ($this.o.statistics) {
			$this.stat_addKeyword($this.o.id, $this.n('text').val());
		}
		if (data !== '' && $dataNode.length > 0 && $dataNode.find(selector).length > 0) {
			data = data.replace(/&asp_force_reset_pagination=1/gmi, '');
			data = data.replace(/%26asp_force_reset_pagination%3D1/gmi, '');
			data = data.replace(/&#038;asp_force_reset_pagination=1/gmi, '');

			// Safari having issues with srcset when ajax loading
			if (helpers.isSafari()) {
				data = data.replace(/srcset/gmi, 'nosrcset');
			}

			data = helpers.Hooks.applyFilters('asp_live_load_html', data, $this.o.id, $this.o.iid);
			$dataNode = $(parser.parseFromString(data, "text/html"));

			//$el.replaceWith($dataNode.find(selector).first());
			let replacementNode = $dataNode.find(selector).get(0);
			replacementNode = helpers.Hooks.applyFilters('asp/live_load/replacement_node', replacementNode, $this, $el.get(0), data);
			if (replacementNode != null) {
				$el.get(0).parentNode.replaceChild(replacementNode, $el.get(0));
			}

			// get the element again, as it no longer exists
			$el = $(selector).first();
			if (updateLocation) {
				document.title = dataNode.title;
				history.pushState({}, null, url);
			}

			// WooCommerce ordering fix
			$(selector).first().find(".woocommerce-ordering select.orderby").on("change", function () {
				if ($(this).closest("form").length > 0) {
					$(this).closest("form").get(0).submit();
				}
			});

			$this.addHighlightString($(selector).find('a'));

			helpers.Hooks.applyFilters('asp/live_load/finished', url, $this, selector, $el.get(0));

			// noinspection JSUnresolvedVariable
			ASP.initialize();
			$this.lastSuccesfulSearch = $('form', $this.n('searchsettings')).serialize() + $this.n('text').val().trim();
			$this.lastSearchData = data;

			$this.setLiveLoadCache(html, origSelector);
		}
		$this.n('s').trigger("asp_search_end", [$this.o.id, $this.o.iid, $this.n('text').val(), data], true, true);
		$this.gaEvent?.('search_end', {'results_count': 'unknown'});
		$this.hideLoader();
		$el.css('opacity', 1);
		$this.searching = false;
		if ($this.n('text').val() !== '') {
			$this.n('proclose').css({
				display: "block"
			});
		}
	}

	updateLocation = typeof updateLocation == 'undefined' ? true : updateLocation;
	forceAjax = typeof forceAjax == 'undefined' ? false : forceAjax;

	// Alternative possible selectors from famous themes
	let altSel = [
		'.search-content',
		'#content', '#Content', 'div[role=main]',
		'main[role=main]', 'div.theme-content', 'div.td-ss-main-content',
		'main.l-content', '#primary'
	];
	if (selector !== '#main')
		altSel.unshift('#main');

	if ($(selector).length < 1) {
		altSel.forEach(function (s) {
			if ($(s).length > 0) {
				selector = s;
				return false;
			}
		});
		if ($(selector).length < 1) {
			console.log('Ajax Search Pro: The live search selector does not exist on the page.');
			return false;
		}
	}

	selector = helpers.Hooks.applyFilters('asp/live_load/selector', selector, this);

	let $el = $(selector).first();

	$this.searchAbort();
	$el.css('opacity', 0.4);

	url = helpers.Hooks.applyFilters('asp/live_load/url', url, $this, selector, $el.get(0));
	helpers.Hooks.applyFilters('asp/live_load/start', url, $this, selector, $el.get(0));

	if (
		!forceAjax &&
		$this.n('searchsettings').find('input[name=filters_initial]').val() === '1' &&
		$this.n('text').val() === ''
	) {
		window.WPD.intervalUntilExecute(function () {
			process(ASP.pageHTML);
		}, function () {
			return ASP.pageHTML !== ''
		});
	} else {
		if (typeof cache != 'undefined') {
			process(cache.html);
		} else {
			$this.searching = true;
			$this.post = $.fn.ajax({
				url: url,
				method: 'GET',
				success: function (data) {
					process(data);
					$this.isAutoP = false;
				},
				dataType: 'html',
				fail: function (jqXHR) {
					$el.css('opacity', 1);
					if (jqXHR.aborted) {
						return;
					}
					$el.html("This request has failed. Please check your connection.");
					$this.hideLoader();
					$this.searching = false;
					$this.n('proclose').css({
						display: "block"
					});
					$this.isAutoP = false;
				}
			});
		}
	}
}
AjaxSearchPro.plugin.getLiveLoadAltSelectors = function() {
	// Alternative possible selectors from famous themes
	return [
		'.search-content',
		'#content #posts-container',
		'#content', '#Content', 'div[role=main]',
		'main[role=main]', 'div.theme-content', 'div.td-ss-main-content',
		'main#page-content',
		'main.l-content', '#primary', '#main-content', '.main-content',
		'.search section .bde-post-loop', // breakdance posts loop section search archive
		'.archive section .bde-post-loop', // breakdance posts loop section general archive
		'.search section .bde-post-list', // breakdance posts list section search archive
		'.archive section .bde-post-list', // breakdance posts list section general archive
	];
}

AjaxSearchPro.plugin.usingLiveLoader = function () {
	const $this = this;
	if ($this._usingLiveLoader !== undefined) return $this._usingLiveLoader;

	const o = $this.o;
	const idClass = 'asp_es_' + o.id;
	const altSelectors = this.getLiveLoadAltSelectors().join(',');

	if (document.getElementsByClassName(idClass).length) {
		return $this._usingLiveLoader = true;
	}

	const options = ['resPage', 'wooShop', 'cptArchive', 'taxArchive'];
	$this._usingLiveLoader = options.some(key => {
		const opt = o[key];
		return opt.useAjax &&
			(
				document.querySelector(opt.selector) ||
				(altSelectors && document.querySelector(altSelectors))
			);
	});

	return $this._usingLiveLoader;
};
AjaxSearchPro.plugin.getLiveURLbyBaseLocation = function (location) {
	let $this = this,
		url = 'asp_ls=' + helpers.nicePhrase($this.n('text').val()),
		start = '&';

	if (location.indexOf('?') === -1) {
		start = '?';
	}

	let final = location + start + url + "&asp_active=1&asp_force_reset_pagination=1&p_asid=" +
		$this.o.id + "&p_asp_data=1&" + $('form', $this.n('searchsettings')).serialize();
	// Possible issue when the URL ends with '?' and the start is '&'
	final = final.replace('?&', '?');
	final = final.replace('&&', '&');

	return final;
}
AjaxSearchPro.plugin.getCurrentLiveURL = function () {
	const $this = this;
	const url = new URL(window.location.href);
	let location;

	// Removing hash via a new URL object
	url.hash = '';
	location = url.href;

	// Correct previous query arguments (in case of paginated results)
	location = location.indexOf('asp_ls=') > -1 ? location.slice(0, location.indexOf('asp_ls=')) : location;
	location = location.indexOf('asp_ls&') > -1 ? location.slice(0, location.indexOf('asp_ls&')) : location;

	// Was asp_ls missing but there are ASP related arguments? (ex. when using ASP.api('getStateURL'))
	location = location.indexOf('p_asid=') > -1 ? location.slice(0, location.indexOf('p_asid=')) : location;
	location = location.indexOf('asp_') > -1 ? location.slice(0, location.indexOf('asp_')) : location;

	return $this.getLiveURLbyBaseLocation(location);
}
AjaxSearchPro.plugin.initLiveLoaderPopState = function () {
	let $this = this;
	$this.liveLoadCache = [];
	window.addEventListener('popstate', () => {
		let data = $this.getLiveLoadCache();
		if (data !== false) {
			$this.n('text').val(data.phrase);
			helpers.formData($('form', $this.n('searchsettings')), data.settings);
			$this.resetNoUISliderFilters();
			$this.liveLoad(data.selector, document.location.href, false, false, data);
		}
	});

	// Store the current page HTML for the live loaders
	// It needs to be requested here as the dom does store the processed HTML, and it is no good.
	if (ASP.pageHTML === "") {
		if (typeof ASP._ajax_page_html === 'undefined') {
			ASP._ajax_page_html = true;
			$.fn.ajax({
				url: $this.currentPageURL,
				method: 'GET',
				success: function (data) {
					ASP.pageHTML = data;
				},
				dataType: 'html'
			});
		}
	}
}

AjaxSearchPro.plugin.setLiveLoadCache = function (html, selector) {
	let $this = this;
	if ($this.liveLoadCache.filter((item) => {
		return item.href === document.location.href;
	}).length === 0) {
		$this.liveLoadCache.push({
			'href': html === ASP.pageHTML ? $this.currentPageURL : document.location.href,
			'phrase': html === ASP.pageHTML ? '' : $this.n('text').val(),
			'selector': selector,
			'html': html,
			'settings': html === ASP.pageHTML ? $this.originalFormData : helpers.formData($('form', $this.n('searchsettings')))
		});
	}
}

AjaxSearchPro.plugin.getLiveLoadCache = function () {
	let $this = this;
	let res = $this.liveLoadCache.filter((item) => {
		return item.href === document.location.href;
	});
	return res.length > 0 ? res[0] : false;
}

export default AjaxSearchPro;