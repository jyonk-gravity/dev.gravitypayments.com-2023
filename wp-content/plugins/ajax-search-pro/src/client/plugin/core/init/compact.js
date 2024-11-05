import AjaxSearchPro from "../base.js";
import {default as $} from "domini";

"use strict";
AjaxSearchPro.plugin.initCompact = function () {
	let $this = this;

	// Reset the overlay no matter what, if the is not fixed
	if ($this.o.compact.enabled && $this.o.compact.position !== 'fixed') {
		$this.o.compact.overlay = 0;
	}

	if ($this.o.compact.enabled) {
		$this.n('trythis').css({
			display: "none"
		});
	}

	if ($this.o.compact.enabled && $this.o.compact.position === 'fixed') {

		/**
		 * If the conditional CSS loader is enabled, the required
		 * search CSS file is not present when this code is executed.
		 */
		window.WPD.intervalUntilExecute(function () {
			let $body = $('body');
			// Save the container element, otherwise it will get lost
			$this.nodes['container'] = $this.n('search').closest('.asp_w_container');
			$body.append($this.n('search').detach());
			$body.append($this.n('trythis').detach());
			// Fix the container position to a px value, even if it is set to % value initially, for better compatibility
			$this.n('search').css({
				top: ($this.n('search').position().top) + 'px'
			});
		}, function () {
			return $this.n('search').css('position') === "fixed"
		});
	}
}
export default AjaxSearchPro;