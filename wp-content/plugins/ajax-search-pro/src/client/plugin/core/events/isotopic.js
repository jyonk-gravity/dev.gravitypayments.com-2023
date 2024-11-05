import AjaxSearchPro from "../base.js";
import {default as $} from "domini";

"use strict";
let helpers = AjaxSearchPro.helpers;
AjaxSearchPro.plugin.initIsotopicPagination = function () {
	let $this = this;
	$this.n('resultsDiv').on($this.clickTouchend + ' click_trigger', 'nav>a', function (e) {
		e.preventDefault();
		e.stopImmediatePropagation();
		let $li = $(this).closest('nav').find('li.asp_active');
		let direction = $(this).hasClass('asp_prev') ? 'prev' : 'next';
		if (direction === "next") {
			if ($li.next('li').length > 0) {
				$li.next('li').trigger('click');
			} else {
				$(this).closest('nav').find('li').first().trigger('click');
			}
		} else {
			if ($li.prev('li').length > 0) {
				$li.prev('li').trigger('click');
			} else {
				$(this).closest('nav').find('li').last().trigger('click');
			}
		}
	});
	$this.n('resultsDiv').on($this.clickTouchend + ' click_trigger', 'nav>ul li', function (e) {
		e.preventDefault();
		e.stopImmediatePropagation();
		let _this = this,
			timeout = 1;
		if (helpers.isMobile()) {
			$this.n('text').trigger('blur');
			timeout = 300;
		}
		setTimeout(function () {
			$this.currentPage = parseInt($(_this).find('span').html(), 10);
			$('nav>ul li', $this.n('resultsDiv')).removeClass('asp_active');
			$('nav', $this.n('resultsDiv')).forEach(function (el) {
				$($(el).find('ul li').get($this.currentPage - 1)).addClass('asp_active');
			});
			if (e.type === 'click_trigger') {
				$this.isotopic.arrange({
					transitionDuration: 0,
					filter: $this.filterFns['number']
				});
			} else {
				$this.isotopic.arrange({
					transitionDuration: 400,
					filter: $this.filterFns['number']
				});
			}
			$this.isotopicPagerScroll();
			$this.removeAnimation();

			$this.n('resultsDiv').trigger('nav_switch');
		}, timeout);
	});
}
export default AjaxSearchPro;