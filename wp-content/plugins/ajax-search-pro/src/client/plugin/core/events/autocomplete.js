import AjaxSearchPro from "../base.js";
import {default as $} from "domini";

"use strict";
let helpers = AjaxSearchPro.helpers;
AjaxSearchPro.plugin.initAutocompleteEvent = function () {
	let $this = this,
		tt;
	if (
		($this.o.autocomplete.enabled && !helpers.isMobile()) ||
		($this.o.autocomplete.mobile && helpers.isMobile())
	) {
		$this.n('text').on('keyup', function (e) {
			$this.keycode = e.keyCode || e.which;
			$this.ktype = e.type;

			let thekey = 39;
			// Let's change the keykode if the direction is rtl
			if ($('body').hasClass('rtl'))
				thekey = 37;
			if ($this.keycode === thekey && $this.n('textAutocomplete').val() !== "") {
				e.preventDefault();
				$this.n('text').val($this.n('textAutocomplete').val());
				if ($this.o.trigger.type) {
					$this.searchAbort();
					$this.search();
				}
			} else {
				clearTimeout(tt);
				if ($this.postAuto != null) $this.postAuto.abort();
				if ($this.o.autocomplete.googleOnly) {
					$this.autocompleteGoogleOnly();
				} else {
					tt = setTimeout(function () {
						$this.autocomplete();
						tt = null;
					}, $this.o.trigger.autocomplete_delay);
				}
			}
		});
		$this.n('text').on('keyup mouseup input blur select', function () {
			$this.fixAutocompleteScrollLeft();
		});
	}
}

export default AjaxSearchPro;
