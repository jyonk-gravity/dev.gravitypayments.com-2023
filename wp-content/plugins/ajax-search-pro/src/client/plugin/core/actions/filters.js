import AjaxSearchPro from "../base.js";
import {default as $} from "domini";

"use strict";
let helpers = AjaxSearchPro.helpers;

AjaxSearchPro.plugin.setFilterStateInput = function (timeout) {
	let $this = this;
	if (typeof timeout == 'undefined') {
		timeout = 65;
	}
	let process = function () {
		if (
			JSON.stringify($this.originalFormData) !== JSON.stringify(helpers.formData($('form', $this.n('searchsettings'))))
		) {
			$this.n('searchsettings').find('input[name=filters_initial]').val(0);
		} else {
			$this.n('searchsettings').find('input[name=filters_initial]').val(1);
		}
	};
	if (timeout === 0) {
		process();
	} else {
		// Need a timeout > 50, as some checkboxes are delayed (parent-child selection)
		setTimeout(function () {
			process();
		}, timeout);
	}
}

AjaxSearchPro.plugin.resetSearchFilters = function () {
	let $this = this;
	helpers.formData($('form', $this.n('searchsettings')), $this.originalFormData);
	// Reset the sliders first
	$this.resetNoUISliderFilters();

	if (typeof $this.select2jQuery != "undefined") {
		$this.select2jQuery($this.n('searchsettings').get(0)).find('.asp_gochosen,.asp_goselect2').trigger("change.asp_select2");
	}
	$this.n('text').val('');
	$this.n('proloading').css('display', 'none');
	$this.hideLoader();
	$this.searchAbort();
	$this.setFilterStateInput(0);
}

AjaxSearchPro.plugin.resetNoUISliderFilters = function () {
	if (this.noUiSliders.length > 0) {
		this.noUiSliders.forEach(function (slider) {
			if (typeof slider.noUiSlider != 'undefined') {
				let vals = [];
				$(slider).parent().find('.asp_slider_hidden').forEach(function (el) {
					vals.push($(el).val());
				});
				if (vals.length > 0) {
					slider.noUiSlider.set(vals);
				}
			}
		});
	}
}

export default AjaxSearchPro;