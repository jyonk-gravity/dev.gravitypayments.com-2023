import AjaxSearchPro from "../base.js";
import {default as $} from "domini";

"use strict";
let helpers = AjaxSearchPro.helpers;
/**
 * This function should be called on-demand to init the results events and all. Do not call on init, only when needed.
 */
AjaxSearchPro.plugin.initResults = function () {
	if (!this.resultsInitialized) {
		this.initResultsBox();
		this.initResultsEvents();
		if (this.o.resultstype === "vertical") {
			this.initNavigationEvents?.();
		}
		if (this.o.resultstype === "isotopic") {
			this.initIsotopicPagination?.();
		}
	}
}
AjaxSearchPro.plugin.initResultsBox = function () {
	let $this = this;

	// Calculates the results animation attributes
	$this.initResultsAnimations();

	if (helpers.isMobile() && $this.o.mobile.force_res_hover) {
		$this.o.resultsposition = 'hover';
		//$('body').append($this.n('resultsDiv').detach());
		$this.nodes.resultsDiv = $this.n('resultsDiv').clone();
		$('body').append($this.nodes.resultsDiv);
		$this.n('resultsDiv').css({
			'position': 'absolute'
		});
	} else {
		// Move the results div to the correct position
		if ($this.o.resultsposition === 'hover' && $this.n('resultsAppend').length <= 0) {
			$this.nodes.resultsDiv = $this.n('resultsDiv').clone();
			$('body').append($this.nodes.resultsDiv);
		} else {
			$this.o.resultsposition = 'block';
			$this.n('resultsDiv').css({
				'position': 'static'
			});
			if ($this.n('resultsAppend').length > 0) {
				if ($this.n('resultsAppend').find('.asp_r_' + $this.o.id).length > 0) {
					$this.nodes.resultsDiv = $this.n('resultsAppend').find('.asp_r_' + $this.o.id);
					if (typeof $this.nodes.resultsDiv.get(0).referenced !== 'undefined') {
						++$this.nodes.resultsDiv.get(0).referenced;
					} else {
						$this.nodes.resultsDiv.get(0).referenced = 1;
					}
				} else {
					$this.nodes.resultsDiv = $this.nodes.resultsDiv.clone();
					$this.nodes.resultsAppend.append($this.nodes.resultsDiv);
				}
			}
		}
	}

	$this.nodes.showmore = $('.showmore', $this.nodes.resultsDiv);
	$this.nodes.items = $('.item', $this.n('resultsDiv')).length > 0 ? $('.item', $this.nodes.resultsDiv) : $('.photostack-flip', $this.nodes.resultsDiv);
	$this.nodes.results = $('.results', $this.nodes.resultsDiv);
	$this.nodes.resdrg = $('.resdrg', $this.nodes.resultsDiv);
	$this.nodes.resultsDiv.get(0).id = $this.nodes.resultsDiv.get(0).id.replace('__original__', '');
	$this.detectAndFixFixedPositioning();

	// Init infinite scroll
	$this.initInfiniteScroll();

	$this.resultsInitialized = true;
}

AjaxSearchPro.plugin.initResultsAnimations = function () {
	let $this = this,
		rpos = $this.n('resultsDiv').css('position'),
		blocking = rpos !== 'fixed' && rpos !== 'absolute';
	$this.resAnim = {
		"showClass": "",
		"showCSS": {
			"visibility": "visible",
			"display": "block",
			"opacity": 1,
			"animation-duration": $this.animOptions.results.dur + 'ms'
		},
		"hideClass": "",
		"hideCSS": {
			"visibility": "hidden",
			"opacity": 0,
			"display": "none"
		},
		"duration": $this.animOptions.results.dur + 'ms'
	};

	if ($this.animOptions.results.anim === "fade") {
		$this.resAnim.showClass = "asp_an_fadeIn";
		$this.resAnim.hideClass = "asp_an_fadeOut";
	}

	if ($this.animOptions.results.anim === "fadedrop" && !blocking) {
		$this.resAnim.showClass = "asp_an_fadeInDrop";
		$this.resAnim.hideClass = "asp_an_fadeOutDrop";
	} else if ($this.animOptions.results.anim === "fadedrop") {
		// If does not support transition, or it is blocking layout
		// .. fall back to fade
		$this.resAnim.showClass = "asp_an_fadeIn";
		$this.resAnim.hideClass = "asp_an_fadeOut";
	}

	$this.n('resultsDiv').css({
		"-webkit-animation-duration": $this.resAnim.duration + "ms",
		"animation-duration": $this.resAnim.duration + "ms"
	});
}

export default AjaxSearchPro;