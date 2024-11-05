import AjaxSearchPro from "../base.js";
import {default as $} from "domini";

"use strict";
let helpers = AjaxSearchPro.helpers;

AjaxSearchPro.plugin.initMagnifierEvents = function () {
	let $this = this, t;
	$this.n('promagnifier').on('click', function (e) {
		let compact = $this.n('search').attr('data-asp-compact') || 'closed';
		$this.keycode = e.keyCode || e.which;
		$this.ktype = e.type;

		// If compact closed or click on magnifier in opened compact mode, when closeOnMagnifier enabled
		if ($this.o.compact.enabled) {
			// noinspection JSUnresolvedVariable
			if (
				compact === 'closed' ||
				($this.o.compact.closeOnMagnifier && compact === 'open')
			) {
				return false;
			}
		}

		$this.gaEvent?.('magnifier');

		// If redirection is set to the results page, or custom URL
		// noinspection JSUnresolvedVariable
		if (
			$this.n('text').val().length >= $this.o.charcount &&
			$this.o.redirectOnClick &&
			$this.o.trigger.click !== 'first_result'
		) {
			$this.doRedirectToResults('click');
			clearTimeout(t);
			return false;
		}

		if (!($this.o.trigger.click === 'ajax_search' || $this.o.trigger.click === 'first_result')) {
			return false;
		}

		$this.searchAbort();
		clearTimeout($this.timeouts.search);
		$this.n('proloading').css('display', 'none');

		if ($this.n('text').val().length >= $this.o.charcount) {
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
		}
	});
}
AjaxSearchPro.plugin.initButtonEvents = function () {
	let $this = this;

	$this.n('searchsettings').find('button.asp_s_btn').on('click', function (e) {
		$this.ktype = 'button';
		e.preventDefault();
		// noinspection JSUnresolvedVariable
		if ($this.n('text').val().length >= $this.o.charcount) {
			// noinspection JSUnresolvedVariable
			if ($this.o.sb.redirect_action !== 'ajax_search') {
				// noinspection JSUnresolvedVariable
				if ($this.o.sb.redirect_action !== 'first_result') {
					$this.doRedirectToResults('button');
				} else {
					if ($this.isRedirectToFirstResult()) {
						$this.doRedirectToFirstResult();
						return false;
					}
					$this.search();
				}
			} else {
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

	$this.n('searchsettings').find('button.asp_r_btn').on('click', function (e) {
		let currentFormData = helpers.formData($('form', $this.n('searchsettings'))),
			lastPhrase = $this.n('text').val();

		e.preventDefault();
		$this.resetSearchFilters();
		// noinspection JSUnresolvedVariable
		if ($this.o.rb.action === 'live' &&
			(
				JSON.stringify(currentFormData) !== JSON.stringify(helpers.formData($('form', $this.n('searchsettings')))) ||
				lastPhrase !== ''
			)
		) {
			$this.search(false, false, false, true, true);
		} else { // noinspection JSUnresolvedVariable
			if ($this.o.rb.action === 'close') {
				$this.hideResults();
			}
		}
	});
}

export default AjaxSearchPro;