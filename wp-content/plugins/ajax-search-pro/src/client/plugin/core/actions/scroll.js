import AjaxSearchPro from "../base.js";
import {default as $} from "domini";

"use strict";
let helpers = AjaxSearchPro.helpers;

AjaxSearchPro.plugin.createResultsScroll = function (type) {
	let $this = this,
		t, $resScroll = $this.n('results');
	type = typeof type == 'undefined' ? 'vertical' : type;
	// noinspection JSUnresolvedVariable

	$resScroll.on('scroll', function () {
		// noinspection JSUnresolvedVariable
		if ($this.o.show_more.infinite) {
			clearTimeout(t);
			t = setTimeout(function () {
				$this.checkAndTriggerInfiniteScroll(type);
			}, 60);
		}
	});
}

AjaxSearchPro.plugin.createVerticalScroll = function () {
	this.createResultsScroll('vertical')
}

AjaxSearchPro.plugin.createHorizontalScroll = function () {
	this.createResultsScroll('horizontal')
}

AjaxSearchPro.plugin.checkAndTriggerInfiniteScroll = function (caller) {
	let $this = this,
		$r = $('.item', $this.n('resultsDiv'));
	caller = typeof caller == 'undefined' ? 'window' : caller;

	// Show more might not even visible
	if ($this.n('showmore').length === 0 || $this.n('showmoreContainer').css('display') === 'none') {
		return false;
	}

	if (caller === 'window' || caller === 'horizontal') {
		// Isotopic pagination present? Abort.
		// noinspection JSUnresolvedVariable
		if (
			$this.o.resultstype === 'isotopic' &&
			$('nav.asp_navigation', $this.n('resultsDiv')).css('display') !== 'none'
		) {
			return false;
		}

		let onViewPort = $r.last().inViewPort(0, $this.n('resultsDiv').get(0)),
			onScreen = $r.last().inViewPort(0);
		if (
			!$this.searching &&
			$r.length > 0 &&
			onViewPort && onScreen
		) {
			$this.n('showmore').find('a.asp_showmore').trigger('click');
		}
	} else if (caller === 'vertical') {
		let $scrollable = $this.n('results');
		if (helpers.isScrolledToBottom($scrollable.get(0), 20)) {
			$this.n('showmore').find('a.asp_showmore').trigger('click');
		}
	} else if (caller === 'isotopic') {
		if (
			!$this.searching &&
			$r.length > 0 &&
			$this.n('resultsDiv').find('nav.asp_navigation ul li').last().hasClass('asp_active')
		) {
			$this.n('showmore').find('a.asp_showmore').trigger('click');
		}
	}
}