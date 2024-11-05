import AjaxSearchPro from "../base.js";
import {default as $} from "domini";

"use strict";
let helpers = AjaxSearchPro.helpers;

AjaxSearchPro.plugin.initInputEvents = function () {
	let $this = this, initialized = false;
	let initTriggers = function () {
		$this.n('text').off('mousedown touchstart keydown', initTriggers);
		if (!initialized) {
			$this._initFocusInput();
			if ($this.o.trigger.type) {
				$this._initSearchInput();
			}
			$this._initEnterEvent();
			$this._initFormEvent();
			$this.initAutocompleteEvent?.();
			initialized = true;
		}
	};
	$this.n('text').on('mousedown touchstart keydown', initTriggers, {passive: true});
}

AjaxSearchPro.plugin._initFocusInput = function () {
	let $this = this;

	// Some kind of crazy rev-slider fix
	$this.n('text').on('click', function (e) {
		/**
		 * In some menus the input is wrapped in an <a> tag, which has an event listener attached.
		 * When clicked, the input is blurred. This prevents that.
		 */
		e.stopPropagation();
		e.stopImmediatePropagation();

		$(this).trigger('focus');
		$this.gaEvent?.('focus');

		// Show the results if the query does not change
		if (
			($('form', $this.n('searchsettings')).serialize() + $this.n('text').val().trim()) === $this.lastSuccesfulSearch
		) {
			if (!$this.resultsOpened && !$this.usingLiveLoader()) {
				$this._no_animations = true;
				$this.showResults();
				$this._no_animations = false;
			}
			return false;
		}
	});
	$this.n('text').on('focus input', function () {
		if ($this.searching) {
			return;
		}
		if ($(this).val() !== '') {
			$this.n('proclose').css('display', 'block');
		} else {
			$this.n('proclose').css({
				display: "none"
			});
		}
	});
}

AjaxSearchPro.plugin._initSearchInput = function () {
	let $this = this;

	$this.n('text').on('input', function (e) {
		$this.keycode = e.keyCode || e.which;
		$this.ktype = e.type;

		$this.updateHref();

		// Trigger on redirection/magnifier
		if (!$this.o.trigger.type) {
			$this.searchAbort();
			clearTimeout($this.timeouts.search);
			$this.hideLoader();
			return false;
		}

		$this.hideArrowBox?.();

		// Is the character count sufficient?
		// noinspection JSUnresolvedVariable
		if ($this.n('text').val().length < $this.o.charcount) {
			$this.n('proloading').css('display', 'none');
			if (!$this.att('blocking')) $this.hideSettings?.();
			$this.hideResults(false);
			$this.searchAbort();
			clearTimeout($this.timeouts.search);
			return false;
		}

		$this.searchAbort();
		clearTimeout($this.timeouts.search);
		$this.n('proloading').css('display', 'none');

		$this.timeouts.search = setTimeout(function () {
			// If the user types and deletes, while the last results are open
			if (
				($('form', $this.n('searchsettings')).serialize() + $this.n('text').val().trim()) !== $this.lastSuccesfulSearch ||
				(!$this.resultsOpened && !$this.usingLiveLoader())
			) {
				$this.search();
			} else {
				if ($this.isRedirectToFirstResult())
					$this.doRedirectToFirstResult();
				else
					$this.n('proclose').css('display', 'block');
			}
		}, $this.o.trigger.delay);
	});
}

AjaxSearchPro.plugin._initEnterEvent = function () {
	let $this = this,
		rt, enterRecentlyPressed = false;
	// The return event has to be dealt with on a keyup event, as it does not trigger the input event
	$this.n('text').on('keyup', function (e) {
		$this.keycode = e.keyCode || e.which;
		$this.ktype = e.type;

		// Prevent rapid enter key pressing
		if ($this.keycode === 13) {
			clearTimeout(rt);
			rt = setTimeout(function () {
				enterRecentlyPressed = false;
			}, 300);
			if (enterRecentlyPressed) {
				return false;
			} else {
				enterRecentlyPressed = true;
			}
		}

		let isInput = $(this).hasClass("orig");

		// noinspection JSUnresolvedVariable
		if ($this.n('text').val().length >= $this.o.charcount && isInput && $this.keycode === 13) {
			$this.gaEvent?.('return');
			if ($this.o.redirectOnEnter) {
				if ($this.o.trigger.return !== 'first_result') {
					$this.doRedirectToResults($this.ktype);
				} else {
					$this.search();
				}
			} else if ($this.o.trigger.return === 'ajax_search') {
				if (
					($('form', $this.n('searchsettings')).serialize() + $this.n('text').val().trim()) !== $this.lastSuccesfulSearch ||
					!$this.resultsOpened
				) {
					$this.search();
				}
			}
			clearTimeout($this.timeouts.search);
		}
	});
}

AjaxSearchPro.plugin._initFormEvent = function () {
	let $this = this;
	// Handle the submit/mobile search button event
	$($this.n('text').closest('form').get(0)).on('submit', function (e, args) {
		e.preventDefault();
		// Mobile keyboard search icon and search button
		if (helpers.isMobile()) {
			if ($this.o.redirectOnEnter) {
				let event = new Event("keyup");
				event.keyCode = event.which = 13;
				this.n('text').get(0).dispatchEvent(event);
			} else {
				$this.search();
				document.activeElement.blur();
			}
		} else if (typeof (args) != 'undefined' && args === 'ajax') {
			$this.search();
		}
	});
}

export default AjaxSearchPro;