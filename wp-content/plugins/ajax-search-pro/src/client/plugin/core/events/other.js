import AjaxSearchPro from "../base.js";
import {default as $} from "domini";

"use strict";
let helpers = AjaxSearchPro.helpers;
AjaxSearchPro.plugin.initOtherEvents = function () {
	let $this = this, handler, handler2;

	/**
	 * Prevent parent events in Menus or similar. This argument is passed
	 * when the plugin is used via a navigation menu.
	 */
	if ($this.o.preventEvents && typeof jQuery !== 'undefined') {
		jQuery($this.n('search').get(0)).closest('a, li').off();
	}

	if (helpers.isMobile() && helpers.detectIOS()) {
		/**
		 * Memorize the scroll top when the input is focused on IOS
		 * as fixed elements scroll freely, resulting in incorrect scroll value
		 */
		$this.n('text').on('touchstart', function () {
			$this.savedScrollTop = window.scrollY;
			$this.savedContainerTop = $this.n('search').offset().top;
		});
	}

	if ($this.o.focusOnPageload) {
		$(window).on('load', function () {
			$this.n('text').get(0).focus();
		}, {'options': {'once': true}});
	}

	$this.n('proclose').on($this.clickTouchend, function (e) {
		//if ($this.resultsOpened == false) return;
		e.preventDefault();
		e.stopImmediatePropagation();
		$this.n('text').val("");
		$this.n('textAutocomplete').val("");
		$this.hideResults();
		$this.n('text').trigger('focus');

		$this.n('proloading').css('display', 'none');
		$this.hideLoader();
		$this.searchAbort();


		if ($('.asp_es_' + $this.o.id).length > 0) {
			$this.showLoader();
			$this.liveLoad('.asp_es_' + $this.o.id, $this.getCurrentLiveURL(), $this.o.trigger.update_href);
		} else {
			const array = ['resPage', 'wooShop', 'taxArchive', 'cptArchive'];
			for (let i = 0; i < array.length; i++) {
				if ($this.o[array[i]].useAjax) {
					$this.showLoader();
					$this.liveLoad($this.o[array[i]].selector, $this.getCurrentLiveURL());
					break;
				}
			}
		}

		$this.n('text').get(0).focus();
	});

	if (helpers.isMobile()) {
		handler = function () {
			$this.orientationChange();
			// Fire once more a bit delayed, some mobile browsers need to re-zoom etc..
			setTimeout(function () {
				$this.orientationChange();
			}, 600);
		};
		$this.documentEventHandlers.push({
			'node': window,
			'event': 'orientationchange',
			'handler': handler
		});
		$(window).on("orientationchange", handler);
	} else {
		handler = function () {
			$this.resize();
		};
		$this.documentEventHandlers.push({
			'node': window,
			'event': 'resize',
			'handler': handler
		});
		$(window).on("resize", handler, {passive: true});
	}

	handler2 = function () {
		$this.scrolling(false);
	};
	$this.documentEventHandlers.push({
		'node': window,
		'event': 'scroll',
		'handler': handler2
	});
	$(window).on('scroll', handler2, {passive: true});

	// Mobile navigation focus
	// noinspection JSUnresolvedVariable
	if (helpers.isMobile() && $this.o.mobile.menu_selector !== '') {
		// noinspection JSUnresolvedVariable
		$($this.o.mobile.menu_selector).on('touchend', function () {
			let _this = this;
			setTimeout(function () {
				let $input = $(_this).find('input.orig');
				$input = $input.length === 0 ? $(_this).next().find('input.orig') : $input;
				$input = $input.length === 0 ? $(_this).parent().find('input.orig') : $input;
				$input = $input.length === 0 ? $this.n('text') : $input;
				if ($this.n('search').inViewPort()) {
					$input.get(0).focus();
				}
			}, 300);
		});
	}

	// Prevent zoom on IOS
	if (helpers.detectIOS() && helpers.isMobile() && helpers.isTouchDevice()) {
		if (parseInt($this.n('text').css('font-size')) < 16) {
			$this.n('text').data('fontSize', $this.n('text').css('font-size')).css('font-size', '16px');
			$this.n('textAutocomplete').css('font-size', '16px');
			$('body').append('<style>#ajaxsearchpro' + $this.o.rid + ' input.orig::-webkit-input-placeholder{font-size: 16px !important;}</style>');
		}
	}
}

AjaxSearchPro.plugin.orientationChange = function () {
	let $this = this;
	$this.detectAndFixFixedPositioning();
	$this.fixSettingsPosition();
	$this.fixResultsPosition();
	$this.fixTryThisPosition();

	if ($this.o.resultstype === "isotopic" && $this.n('resultsDiv').css('visibility') === 'visible') {
		$this.calculateIsotopeRows();
		$this.showPagination(true);
		$this.removeAnimation();
	}
}

AjaxSearchPro.plugin.resize = function () {
	this.hideArrowBox?.();
	this.orientationChange();
}

AjaxSearchPro.plugin.scrolling = function (ignoreVisibility) {
	let $this = this;
	$this.detectAndFixFixedPositioning();
	$this.hideOnInvisibleBox();
	$this.fixSettingsPosition(ignoreVisibility);
	$this.fixResultsPosition(ignoreVisibility);
}

AjaxSearchPro.plugin.initTryThisEvents = function () {
	let $this = this;
	// Try these search button events
	if ($this.n('trythis').find('a').length > 0) {
		$this.n('trythis').find('a').on('click touchend', function (e) {
			e.preventDefault();
			e.stopImmediatePropagation();

			if ($this.o.compact.enabled) {
				let state = $this.n('search').attr('data-asp-compact') || 'closed';
				if (state === 'closed')
					$this.n('promagnifier').trigger('click');
			}
			document.activeElement.blur();
			$this.n('textAutocomplete').val('');
			$this.n('text').val($(this).html());
			$this.gaEvent?.('try_this');
			if ($this.o.trigger.type) {
				$this.searchWithCheck(80);
			}
		});

		// Make the try-these keywords visible, this makes sure that the styling occurs before visibility
		$this.n('trythis').css({
			visibility: "visible"
		});
	}
}

AjaxSearchPro.plugin.initSelect2 = function () {
	let $this = this;
	window.WPD.intervalUntilExecute(function (jq) {
		if (typeof jq.fn.asp_select2 !== 'undefined') {
			$this.select2jQuery = jq;
			$('select.asp_gochosen, select.asp_goselect2', $this.n('searchsettings')).forEach(function () {
				$(this).removeAttr('data-asp_select2-id'); // Duplicate init protection
				$(this).find('option[value=""]').val('__any__');
				$this.select2jQuery(this).asp_select2({
					width: '100%',
					theme: 'flat',
					allowClear: $(this).find('option[value=""]').length > 0,
					"language": {
						"noResults": function () {
							return $this.o.select2.nores;
						}
					}
				});
				// Trigger WPD dom change on the original jQuery change event
				$this.select2jQuery(this).on('change', function () {
					$(this).trigger('change');
				});
			});
		}
	}, function () {
		return helpers.whichjQuery('asp_select2');
	});
}
export default AjaxSearchPro;