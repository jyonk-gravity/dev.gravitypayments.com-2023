import AjaxSearchPro from "../base.js";
import {default as $} from "domini";

"use strict";
let helpers = AjaxSearchPro.helpers;
AjaxSearchPro.plugin.initSettingsSwitchEvents = function () {
	let $this = this;
	$this.n('prosettings').on("click", function () {
		if ($this.n('prosettings').data('opened') === '0') {
			$this.showSettings?.();
		} else {
			$this.hideSettings?.();
		}
	});

	// noinspection JSUnresolvedVariable
	if (helpers.isMobile()) {
		// noinspection JSUnresolvedVariable
		if (
			$this.o.mobile.force_sett_state === "open" ||
			($this.o.mobile.force_sett_state === "none" && $this.o.settingsVisible)
		) {
			$this.showSettings?.(false);
		}
	} else {
		// noinspection JSUnresolvedVariable
		if ($this.o.settingsVisible) {
			$this.showSettings?.(false);
		}
	}
}

AjaxSearchPro.plugin.initSettingsEvents = function () {
	let $this = this, t;
	let formDataHandler = function () {
		// Let everything initialize (datepicker etc..), then get the form data
		if (typeof $this.originalFormData === 'undefined') {
			$this.originalFormData = helpers.formData($('form', $this.n('searchsettings')));
		}
		$this.n('searchsettings').off('mousedown touchstart mouseover', formDataHandler);
	};
	$this.n('searchsettings').on('mousedown touchstart mouseover', formDataHandler);

	let handler = function (e) {
		if ($(e.target).closest('.asp_w').length === 0) {
			if (
				!$this.att('blocking') &&
				!$this.dragging &&
				$(e.target).closest('.ui-datepicker').length === 0 &&
				$(e.target).closest('.noUi-handle').length === 0 &&
				$(e.target).closest('.asp_select2').length === 0 &&
				$(e.target).closest('.asp_select2-container').length === 0
			) {
				$this.hideSettings?.();
			}
		}
	};
	$this.documentEventHandlers.push({
		'node': document,
		'event': $this.clickTouchend,
		'handler': handler
	});
	$(document).on($this.clickTouchend, handler);

	const setOptionCheckedClass = () => {
		$this.n('searchsettings').find('.asp_option, .asp_label').forEach(function (el) {
			if ($(el).find('input').prop("checked")) {
				$(el).addClass('asp_option_checked');
			} else {
				$(el).removeClass('asp_option_checked');
			}
		});
	};
	setOptionCheckedClass();

	// Note if the settings have changed
	$this.n('searchsettings').on('click', function () {
		$this.settingsChanged = true;
	});

	$this.n('searchsettings').on($this.clickTouchend, function (e) {
		if (!$this.dragging) {
			$this.updateHref();
		}

		/**
		 * Stop propagation on settings clicks, except the noUiSlider handler event.
		 * If noUiSlider event propagation is stopped, then the: set, end, change events does not fire properly.
		 */
		if (typeof e.target != 'undefined' && !$(e.target).hasClass('noUi-handle')) {
			e.stopImmediatePropagation();
		} else {
			// For noUI case, still cancel if this is a click (desktop device)
			if (e.type === 'click')
				e.stopImmediatePropagation();
		}
	});

	// Category level automatic checking and hiding
	$('.asp_option_cat input[type="checkbox"]', $this.n('searchsettings')).on('asp_chbx_change', function () {
		$this.settingsCheckboxToggle($(this).closest('.asp_option_cat'));
		setOptionCheckedClass();
	});

	// Radio clicks
	$('input[type="radio"]', $this.n('searchsettings')).on('change', function () {
		setOptionCheckedClass();
	});

	// Init the hide settings
	$('.asp_option_cat', $this.n('searchsettings')).forEach(function (el) {
		$this.settingsCheckboxToggle($(el), false);
	});

	// Emulate click on checkbox on the whole option
	//$('div.asp_option', $this.nodes.searchsettings).on('mouseup touchend', function(e){
	$('div.asp_option', $this.n('searchsettings')).on($this.mouseupTouchend, function (e) {
		e.preventDefault(); // Stop firing twice on mouseup and touchend on mobile devices
		e.stopImmediatePropagation();

		if ($this.dragging) {
			return false;
		}
		$(this).find('input[type="checkbox"]').prop("checked", !$(this).find('input[type="checkbox"]').prop("checked"));

		// Trigger a custom change event, for max compatibility
		// .. the original change is buggy for some installations.
		clearTimeout(t);
		let _this = this;
		t = setTimeout(function () {
			$(_this).find('input[type="checkbox"]').trigger('asp_chbx_change');
		}, 50);
	});

	// Tabbed element selection with enter or spacebar
	$('div.asp_option', $this.n('searchsettings')).on('keyup', function (e) {
		e.preventDefault();
		let keycode = e.keyCode || e.which;
		if (keycode === 13 || keycode === 32) {
			$(this).trigger('mouseup');
		}
	});

	// Change the state of the choose any option if all of them are de-selected
	$('fieldset.asp_checkboxes_filter_box', $this.n('searchsettings')).forEach(function () {
		let all_unchecked = true;
		$(this).find('.asp_option:not(.asp_option_selectall) input[type="checkbox"]').forEach(function () {
			if ($(this).prop('checked')) {
				all_unchecked = false;
				return false;
			}
		});
		if (all_unchecked) {
			$(this).find('.asp_option_selectall input[type="checkbox"]').prop('checked', false).removeAttr('data-origvalue');
		}
	});

	// Mark last visible options
	$('fieldset', $this.n('searchsettings')).forEach(function () {
		$(this).find('.asp_option:not(.hiddend)').last().addClass("asp-o-last");
	});

	// Select all checkboxes
	$('.asp_option input[type="checkbox"]', $this.n('searchsettings')).on('asp_chbx_change', function () {
		let className = $(this).data("targetclass");
		if (typeof className == 'string' && className !== '') {
			$("input." + className, $this.n('searchsettings')).prop("checked", $(this).prop("checked"));
		}
		setOptionCheckedClass();
	});
}
export default AjaxSearchPro;