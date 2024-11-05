import AjaxSearchPro from "../base.js";
import {default as $} from "domini";

"use strict";
let helpers = AjaxSearchPro.helpers;
AjaxSearchPro.plugin.initEtc = function () {
	let $this = this;

	// Isotopic Layout variables
	$this.il = {
		columns: 3,
		rows: $this.o.isotopic.pagination ? $this.o.isotopic.rows : 10000,
		itemsPerPage: 6,
		lastVisibleItem: -1
	};
	// Isotopic filter functions
	$this.filterFns = {
		number: function (i, el) {
			if (typeof el === 'undefined' || typeof i === 'object') {
				el = i;
			}
			const number = $(el).attr('data-itemnum'),
				currentPage = $this.currentPage,
				itemsPerPage = $this.il.itemsPerPage;

			if ((number % ($this.il.columns * $this.il.rows)) < ($this.il.columns * ($this.il.rows - 1)))
				$(el).addClass('asp_gutter_bottom');
			else
				$(el).removeClass('asp_gutter_bottom');

			return (
				(parseInt(number, 10) < itemsPerPage * currentPage) &&
				(parseInt(number, 10) >= itemsPerPage * (currentPage - 1))
			);
		}
	};

	helpers.Hooks.applyFilters('asp/init/etc', $this);
}

AjaxSearchPro.plugin.initInfiniteScroll = function () {
	// NOTE: Custom Scrollbar triggers are under the scrollbar script callbacks -> OnTotalScroll callbacks
	let $this = this;

	// noinspection JSUnresolvedVariable
	if ($this.o.show_more.infinite && $this.o.resultstype !== 'polaroid') {
		// Vertical & Horizontal: Regular scroll + when custom scrollbar scroll is not present
		// Isotopic: Regular scroll on non-paginated layout
		let t, handler;
		handler = function () {
			clearTimeout(t);
			t = setTimeout(function () {
				$this.checkAndTriggerInfiniteScroll('window');
			}, 80);
		};
		$this.documentEventHandlers.push({
			'node': window,
			'event': 'scroll',
			'handler': handler
		});
		$(window).on('scroll', handler);
		$this.n('results').on('scroll', handler);

		let tt;
		$this.n('resultsDiv').on('nav_switch', function () {
			// Delay this a bit, in case the user quick-switches
			clearTimeout(tt);
			tt = setTimeout(function () {
				$this.checkAndTriggerInfiniteScroll('isotopic');
			}, 800);
		});
	}
}

AjaxSearchPro.plugin.hooks = function () {
	let $this = this;

	// After elementor results get printed
	$this.n('s').on('asp_elementor_results', function (e, id) {
		if (parseInt($this.o.id) === parseInt(id)) {
			// Lazy load for jetpack
			// noinspection JSUnresolvedVariable
			if (typeof window.jetpackLazyImagesModule == 'function') {
				setTimeout(function () {
					// noinspection JSUnresolvedFunction
					window.jetpackLazyImagesModule();
				}, 300);
			}
		}
	});
}
export default AjaxSearchPro;