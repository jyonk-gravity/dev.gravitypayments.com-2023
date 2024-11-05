import AjaxSearchPro from "../base.js";
import {default as $} from "domini";

"use strict";
let helpers = AjaxSearchPro.helpers;

AjaxSearchPro.plugin.showSettings = function (animations) {
	let $this = this;

	$this.initSettings?.();

	animations = typeof animations == 'undefined' ? true : animations;
	$this.n('s').trigger("asp_settings_show", [$this.o.id, $this.o.iid], true, true);

	if (!animations) {
		$this.n('searchsettings').css({
			'display': 'block',
			'visibility': 'visible',
			'opacity': 1
		});
	} else {
		$this.n('searchsettings').css($this.settAnim.showCSS);
		$this.n('searchsettings').removeClass($this.settAnim.hideClass).addClass($this.settAnim.showClass);
	}

	// noinspection JSUnresolvedVariable
	if ($this.o.fss_layout === "masonry" && $this.sIsotope == null && !(helpers.isMobile() && helpers.detectIOS())) {
		if (typeof rpp_isotope !== 'undefined') {
			setTimeout(function () {
				let id = $this.n('searchsettings').attr('id');
				$this.n('searchsettings').css("width", "100%");
				// noinspection JSPotentiallyInvalidConstructorUsage
				$this.sIsotope = new rpp_isotope("#" + id + " form", {
					isOriginLeft: !$('body').hasClass('rtl'),
					itemSelector: 'fieldset',
					layoutMode: 'masonry',
					transitionDuration: 0,
					masonry: {
						columnWidth: $this.n('searchsettings').find('fieldset:not(.hiddend)').outerWidth()
					}
				});
			}, 20);
		} else {
			// Isotope is not included within the scripts, alert the user!
			return false;
		}
	}

	if (typeof $this.select2jQuery != 'undefined') {
		$this.select2jQuery($this.n('searchsettings').get(0)).find('.asp_gochosen,.asp_goselect2').trigger("change.asp_select2");
	}

	$this.n('prosettings').data('opened', 1);

	$this.fixSettingsPosition(true);
	$this.fixSettingsAccessibility();
}
AjaxSearchPro.plugin.hideSettings = function () {
	let $this = this;

	$this.initSettings?.();

	$this.n('s').trigger("asp_settings_hide", [$this.o.id, $this.o.iid], true, true);

	$this.n('searchsettings').removeClass($this.settAnim.showClass).addClass($this.settAnim.hideClass);
	setTimeout(function () {
		$this.n('searchsettings').css($this.settAnim.hideCSS);
	}, $this.settAnim.duration);

	$this.n('prosettings').data('opened', 0);

	if ($this.sIsotope != null) {
		setTimeout(function () {
			$this.sIsotope.destroy();
			$this.sIsotope = null;
		}, $this.settAnim.duration);
	}

	if (typeof $this.select2jQuery != 'undefined' && typeof $this.select2jQuery.fn.asp_select2 != 'undefined') {
		$this.select2jQuery($this.n('searchsettings').get(0)).find('.asp_gochosen,.asp_goselect2').asp_select2('close');
	}

	$this.hideArrowBox?.();
}
AjaxSearchPro.plugin.reportSettingsValidity = function () {
	let $this = this,
		valid = true;

	// Automatically valid, when settings can be closed, or are hidden
	if ($this.n('searchsettings').css('visibility') === 'hidden')
		return true;

	$this.n('searchsettings').find('fieldset.asp_required').forEach(function () {
		let $_this = $(this),
			fieldset_valid = true;
		// Text input
		$_this.find('input[type=text]:not(.asp_select2-search__field)').forEach(function () {
			if ($(this).val() === '') {
				fieldset_valid = false;
			}
		});
		// Select drop downs
		$_this.find('select').forEach(function () {
			if (
				$(this).val() == null || $(this).val() === '' ||
				($(this).closest('fieldset').is('.asp_filter_tax, .asp_filter_content_type') && parseInt($(this).val()) === -1)
			) {
				fieldset_valid = false;
			}
		});
		// Check for checkboxes
		if ($_this.find('input[type=checkbox]').length > 0) {
			// Check if all of them are checked
			if ($_this.find('input[type=checkbox]:checked').length === 0) {
				fieldset_valid = false;
			} else if (
				$_this.find('input[type=checkbox]:checked').length === 1 &&
				$_this.find('input[type=checkbox]:checked').val() === ''
			) {
				// Select all checkbox
				fieldset_valid = false;
			}
		}
		// Check for checkboxes
		if ($_this.find('input[type=radio]').length > 0) {
			// Check if all of them are checked
			if ($_this.find('input[type=radio]:checked').length === 0) {
				fieldset_valid = false;
			}
			if (fieldset_valid) {
				$_this.find('input[type=radio]').forEach(function () {
					if (
						$(this).prop('checked') &&
						(
							$(this).val() === '' ||
							(
								$(this).closest('fieldset').is('.asp_filter_tax, .asp_filter_content_type') &&
								parseInt($(this).val()) === -1
							)
						)
					) {
						fieldset_valid = false;
					}
				});
			}
		}

		if (!fieldset_valid) {
			$_this.addClass('asp-invalid');
			valid = false;
		} else {
			$_this.removeClass('asp-invalid');
		}
	});

	if (!valid) {
		$this.n('searchsettings').find('button.asp_s_btn').prop('disabled', true);
	}
	{
		$this.n('searchsettings').find('button.asp_s_btn').prop('disabled', false);
	}

	return valid;
}

AjaxSearchPro.plugin.showArrowBox = function (element, text) {
	let $this = this,
		offsetTop, left,
		$body = $('body'),
		$box = $body.find('.asp_arrow_box');
	if ($box.length === 0) {
		$body.append("<div class='asp_arrow_box'></div>");
		$box = $body.find('.asp_arrow_box');
		$box.on('mouseout', function () {
			$this.hideArrowBox?.();
		});
	}

	// getBoundingClientRect() is not giving correct values, use different method
	let space = $(element).offset().top - window.scrollY,
		fixedp = false,
		n = element;

	while (n) {
		n = n.parentElement;
		if (n != null && window.getComputedStyle(n).position === 'fixed') {
			fixedp = true;
			break;
		}
	}

	if (fixedp) {
		$box.css('position', 'fixed');
		offsetTop = 0;
	} else {
		$box.css('position', 'absolute');
		offsetTop = window.scrollY;
	}
	$box.html(text);
	$box.css('display', 'block');

	// Count after text is added
	left = (element.getBoundingClientRect().left + ($(element).outerWidth() / 2) - ($box.outerWidth() / 2)) + 'px';

	if (space > 100) {
		$box.removeClass('asp_arrow_box_bottom');
		$box.css({
			top: offsetTop + element.getBoundingClientRect().top - $box.outerHeight() - 4 + 'px',
			left: left
		});
	} else {
		$box.addClass('asp_arrow_box_bottom');
		$box.css({
			top: offsetTop + element.getBoundingClientRect().bottom + 4 + 'px',
			left: left
		});
	}
}

AjaxSearchPro.plugin.hideArrowBox = function () {
	$('body').find('.asp_arrow_box').css('display', 'none');
}

AjaxSearchPro.plugin.showNextInvalidFacetMessage = function () {
	let $this = this;
	if ($this.n('searchsettings').find('.asp-invalid').length > 0) {
		$this.showArrowBox(
			$this.n('searchsettings').find('.asp-invalid').first().get(0),
			$this.n('searchsettings').find('.asp-invalid').first().data('asp_invalid_msg')
		);
	}
}

AjaxSearchPro.plugin.scrollToNextInvalidFacetMessage = function () {
	let $this = this;
	if ($this.n('searchsettings').find('.asp-invalid').length > 0) {
		let $n = $this.n('searchsettings').find('.asp-invalid').first();
		if (!$n.inViewPort(0)) {
			if (typeof $n.get(0).scrollIntoView != "undefined") {
				$n.get(0).scrollIntoView({behavior: "smooth", block: "center", inline: "nearest"});
			} else {
				let stop = $n.offset().top - 20,
					$adminbar = $("#wpadminbar");
				// noinspection JSJQueryEfficiency
				if ($adminbar.length > 0)
					stop -= $adminbar.height();
				stop = stop < 0 ? 0 : stop;
				window.scrollTo({top: stop, behavior: "smooth"});
			}
		}
	}
}

AjaxSearchPro.plugin.settingsCheckboxToggle = function ($node, checkState) {
	let $this = this;

	checkState = typeof checkState == 'undefined' ? true : checkState;
	let $parent = $node,
		$checkbox = $node.find('input[type="checkbox"]'),
		lvl = parseInt($node.data("lvl")) + 1,
		i = 0;
	while (true) {
		$parent = $parent.next();
		if ($parent.length > 0 &&
			typeof $parent.data("lvl") != "undefined" &&
			parseInt($parent.data("lvl")) >= lvl
		) {
			if (checkState && $this.o.settings.unselectChildren) {
				$parent.find('input[type="checkbox"]').prop("checked", $checkbox.prop("checked"));
			}
			// noinspection JSUnresolvedVariable
			if ($this.o.settings.hideChildren) {
				if ($checkbox.prop("checked")) {
					$parent.removeClass("hiddend");
				} else {
					$parent.addClass("hiddend");
				}
			}
		} else {
			break;
		}
		i++;
		if (i > 400) break; // safety first
	}
}

export default AjaxSearchPro;