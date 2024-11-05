import AjaxSearchPro from "../base.js";
import {default as $} from "domini";

"use strict";
AjaxSearchPro.plugin.initResultsEvents = function () {
	let $this = this;

	$this.n('resultsDiv').css({
		opacity: "0"
	});
	let handler = function (e) {
		let keycode = e.keyCode || e.which,
			ktype = e.type;

		if ($(e.target).closest('.asp_w').length === 0) {
			$this.hideOnInvisibleBox();

			// Any hints
			$this.hideArrowBox?.();

			// If not right click
			if (ktype !== 'click' || ktype !== 'touchend' || keycode !== 3) {
				if ($this.o.compact.enabled) {
					let compact = $this.n('search').attr('data-asp-compact') || 'closed';
					// noinspection JSUnresolvedVariable
					if ($this.o.compact.closeOnDocument && compact === 'open' && !$this.resultsOpened) {
						$this.closeCompact();
						$this.searchAbort();
						$this.hideLoader();
					}
				} else {
					// noinspection JSUnresolvedVariable
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
		'node': document,
		'event': $this.clickTouchend,
		'handler': handler
	});
	$(document).on($this.clickTouchend, handler);


	// GTAG on results click
	$this.n('resultsDiv').on('click', '.results .item', function () {
		if ($(this).attr('id') !== '') {
			$this.updateHref('#' + $(this).attr('id'));
		}

		$this.gaEvent?.('result_click', {
			'result_title': $(this).find('a.asp_res_url').text(),
			'result_url': $(this).find('a.asp_res_url').attr('href')
		});
	});

	// Isotope results swipe event
	// noinspection JSUnresolvedVariable
	if ($this.o.resultstype === "isotopic") {
		$this.n('resultsDiv').on("swiped-left", function () {
			if ($this.visiblePagination())
				$this.n('resultsDiv').find("a.asp_next").trigger('click');
		});
		$this.n('resultsDiv').on("swiped-right", function () {
			if ($this.visiblePagination())
				$this.n('resultsDiv').find("a.asp_prev").trigger('click');
		});
	}
}
export default AjaxSearchPro;