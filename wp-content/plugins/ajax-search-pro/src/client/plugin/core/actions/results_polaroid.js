import AjaxSearchPro from "../base.js";
import {default as $} from "domini";

"use strict";
AjaxSearchPro.plugin.showPolaroidResults = function () {
	let $this = this;

	this.loadASPFonts?.();

	$this.n('results').addClass('photostack');

	$('.photostack>nav', $this.n('resultsDiv')).remove();
	let figures = $('figure', $this.n('resultsDiv'));

	$this.showResultsBox();

	if (figures.length > 0) {
		// noinspection JSUnresolvedVariable
		$this.n('results').css({
			height: $this.o.prescontainerheight
		});

		if ($this.o.highlight) {
			// noinspection JSUnresolvedVariable,JSUnresolvedFunction
			$("figcaption", $this.n('resultsDiv')).highlight($this.n('text').val().split(" "), {
				element: 'span', className: 'highlighted', wordsOnly: $this.o.highlightWholewords
			});
		}

		// Initialize the main
		// noinspection JSUnresolvedVariable,JSUnresolvedFunction
		if (typeof Photostack !== 'undefined') {
			// noinspection JSUnresolvedVariable,JSUnresolvedFunction
			$this.ptstack = new Photostack($this.n('results').get(0), {
				callback: function (item) {
				}
			});
		} else {
			// PhotoStack is not included within the scripts, alert the user!;
			return false;
		}
	}
	if (figures.length === 0) {
		$this.n('results').css({
			height: '11110px'
		});
		$this.n('results').css({
			height: "auto"
		});
	}
	$this.addAnimation();
	$this.fixResultsPosition(true);
	$this.searching = false;
	$this.initPolaroidEvents(figures);
}

AjaxSearchPro.plugin.initPolaroidEvents = function (figures) {
	let $this = this,
		i = 1,
		span = '.photostack>nav span';
	figures.forEach(function () {
		if (i > 1)
			$(this).removeClass('photostack-current');
		$(this).attr('idx', i);
		i++;
	});

	figures.on('click', function (e) {
		if ($(this).hasClass("photostack-current")) return;
		e.preventDefault();
		let idx = $(this).attr('idx');
		$('.photostack>nav span:nth-child(' + idx + ')', $this.n('resultsDiv')).trigger('click', [], true);
	});

	const left_handler = () => {
		if ($(span + '.current', $this.n('resultsDiv')).next().length > 0) {
			$(span + '.current', $this.n('resultsDiv')).next().trigger('click', [], true);
		} else {
			$(span + ':nth-child(1)', $this.n('resultsDiv')).trigger('click', [], true);
		}
	}

	const right_handler = () => {
		if ($(span + '.current', $this.n('resultsDiv')).prev().length > 0) {
			$(span + '.current', $this.n('resultsDiv')).prev().trigger('click', [], true);
		} else {
			$(span + ':nth-last-child(1)', $this.n('resultsDiv')).trigger('click', [], true);
		}
	}

	figures.on('mousewheel', function (e) {
		e.preventDefault();
		let delta = e.deltaY > 0 ? 1 : -1
		if (delta >= 1) {
			left_handler();
		} else {
			right_handler();
		}
	});

	$this.n('resultsDiv').on("swiped-left", left_handler);
	$this.n('resultsDiv').on("swiped-right", right_handler);
}

export default AjaxSearchPro;