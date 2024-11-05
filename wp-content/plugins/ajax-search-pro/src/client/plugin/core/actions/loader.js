import AjaxSearchPro from "../base.js";
import {default as $} from "domini";

"use strict";

AjaxSearchPro.plugin.showMoreResLoader = function () {
	let $this = this;
	$this.n('resultsDiv').addClass('asp_more_res_loading');
};

AjaxSearchPro.plugin.showLoader = function (recall) {
	let $this = this;
	recall = typeof recall !== 'undefined' ? recall : false;

	// noinspection JSUnresolvedVariable
	if ($this.o.loaderLocation === "none") return;

	// noinspection JSUnresolvedVariable
	if (!$this.n('search').hasClass("hiddend") && ($this.o.loaderLocation !== "results")) {
		$this.n('proloading').css({
			display: "block"
		});
	}

	// stop at this point, if this is a 'load more' call
	if (recall !== false) {
		return false;
	}

	// noinspection JSUnresolvedVariable
	if (($this.n('search').hasClass("hiddend") && $this.o.loaderLocation !== "search") ||
		(!$this.n('search').hasClass("hiddend") && ($this.o.loaderLocation === "both" || $this.o.loaderLocation === "results"))
	) {
		if (!$this.usingLiveLoader()) {
			if ($this.n('resultsDiv').find('.asp_results_top').length > 0)
				$this.n('resultsDiv').find('.asp_results_top').css('display', 'none');
			$this.showResultsBox();
			$(".asp_res_loader", $this.n('resultsDiv')).removeClass("hiddend");
			$this.n('results').css("display", "none");
			$this.n('showmoreContainer').css("display", "none");
			if (typeof $this.hidePagination !== 'undefined') {
				$this.hidePagination();
			}
		}
	}
};

AjaxSearchPro.plugin.hideLoader = function () {
	let $this = this;
	$this.n('proloading').css({
		display: "none"
	});
	$(".asp_res_loader", $this.n('resultsDiv')).addClass("hiddend");
	$this.n('results').css("display", "");
	$this.n('resultsDiv').removeClass('asp_more_res_loading');
};

export default AjaxSearchPro;