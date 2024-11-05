import AjaxSearchPro from "../base.js";
import {default as $} from "domini";

"use strict";
let helpers = AjaxSearchPro.helpers;

AjaxSearchPro.plugin.initFacetEvents = function () {
	let $this = this,
		gtagTimer = null,
		inputCorrectionTimer = null;

	$('.asp_custom_f input[type=text]:not(.asp_select2-search__field):not(.asp_datepicker_field):not(.asp_datepicker)', $this.n('searchsettings')).on('input', function (e) {
		let code = e.keyCode || e.which,
			_this = this;
		$this.ktype = e.type;
		if (code === 13) {
			e.preventDefault();
			e.stopImmediatePropagation();
		}
		if ($(this).data('asp-type') === 'number') {
			if (this.value !== '') {
				let inputVal = this.value.replaceAll($(this).data('asp-tsep'), '');
				let correctedVal = helpers.inputToFloat(this.value);
				let _this = this;
				_this.value = correctedVal;
				correctedVal = correctedVal < parseFloat($(this).data('asp-min')) ? $(this).data('asp-min') : correctedVal;
				correctedVal = correctedVal > parseFloat($(this).data('asp-max')) ? $(this).data('asp-max') : correctedVal;
				clearTimeout(inputCorrectionTimer);
				inputCorrectionTimer = setTimeout(function () {
					_this.value = helpers.addThousandSeparators(correctedVal, $(_this).data('asp-tsep'));
				}, 400);
				if (correctedVal.toString() !== inputVal) {
					return false;
				}
			}
		}
		clearTimeout(gtagTimer);
		gtagTimer = setTimeout(function () {
			$this.gaEvent?.('facet_change', {
				'option_label': $(_this).closest('fieldset').find('legend').text(),
				'option_value': $(_this).val()
			});
		}, 1400);
		$this.n('searchsettings').find('input[name=filters_changed]').val(1);
		$this.setFilterStateInput(65);
		if ($this.o.trigger.facet)
			$this.searchWithCheck(240);
	});

	// Add the thousand separators
	$this.n('searchsettings').find('.asp-number-range[data-asp-tsep]').forEach(function () {
		this.value = helpers.addThousandSeparators(this.value, $(this).data('asp-tsep'));
	});

	// This needs to be here, submit prevention on input text fields is still needed
	if (!$this.o.trigger.facet) return;

	// Dropdown
	$('select', $this.n('searchsettings')).on('change slidechange', function (e) {
		$this.ktype = e.type;
		$this.n('searchsettings').find('input[name=filters_changed]').val(1);
		$this.gaEvent?.('facet_change', {
			'option_label': $(this).closest('fieldset').find('legend').text(),
			'option_value': $(this).find('option:checked').get().map(function (item) {
				return item.text;
			}).join()
		});
		$this.setFilterStateInput(65);
		$this.searchWithCheck(80);
		if ($this.sIsotope != null) {
			$this.sIsotope.arrange();
		}
	});
	// Any other
	//$('input[type!=checkbox][type!=text][type!=radio]', $this.n('searchsettings')).on('change slidechange', function(){
	$('input:not([type=checkbox]):not([type=text]):not([type=radio])', $this.n('searchsettings')).on('change slidechange', function (e) {
		$this.ktype = e.type;
		$this.n('searchsettings').find('input[name=filters_changed]').val(1);
		$this.gaEvent?.('facet_change', {
			'option_label': $(this).closest('fieldset').find('legend').text(),
			'option_value': $(this).val()
		});
		$this.setFilterStateInput(65);
		$this.searchWithCheck(80);
	});

	// Radio
	$('input[type=radio]', $this.n('searchsettings')).on('change slidechange', function (e) {
		$this.ktype = e.type;
		$this.n('searchsettings').find('input[name=filters_changed]').val(1);
		$this.gaEvent?.('facet_change', {
			'option_label': $(this).closest('fieldset').find('legend').text(),
			'option_value': $(this).closest('label').text()
		});
		$this.setFilterStateInput(65);
		$this.searchWithCheck(80);
	});

	$('input[type=checkbox]', $this.n('searchsettings')).on('asp_chbx_change', function (e) {
		$this.ktype = e.type;
		$this.n('searchsettings').find('input[name=filters_changed]').val(1);
		$this.gaEvent?.('facet_change', {
			'option_label': $(this).closest('fieldset').find('legend').text(),
			'option_value': $(this).closest('.asp_option').find('.asp_option_label').text() + ($(this).prop('checked') ? '(checked)' : '(unchecked)')
		});
		$this.setFilterStateInput(65);
		$this.searchWithCheck(80);
	});
	$('input.asp_datepicker, input.asp_datepicker_field', $this.n('searchsettings')).on('change', function (e) {
		$this.ktype = e.type;
		$this.n('searchsettings').find('input[name=filters_changed]').val(1);
		$this.gaEvent?.('facet_change', {
			'option_label': $(this).closest('fieldset').find('legend').text(),
			'option_value': $(this).val()
		});
		$this.setFilterStateInput(65);
		$this.searchWithCheck(80);
	});
	$('div[id*="-handles"]', $this.n('searchsettings')).forEach(function (e) {
		$this.ktype = e.type;
		if (typeof this.noUiSlider != 'undefined') {
			this.noUiSlider.on('change', function (values) {
				let target = typeof this.target != 'undefined' ? this.target : this;
				$this.gaEvent?.('facet_change', {
					'option_label': $(target).closest('fieldset').find('legend').text(),
					'option_value': values
				});
				$this.n('searchsettings').find('input[name=filters_changed]').val(1);
				// Gtag analytics is handled on the update event, not here
				$this.setFilterStateInput(65);
				$this.searchWithCheck(80);
			});
		}
	});
}

export default AjaxSearchPro;