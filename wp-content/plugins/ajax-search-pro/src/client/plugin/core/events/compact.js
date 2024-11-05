import AjaxSearchPro from "../base.js";
import {default as $} from "domini";

"use strict";
AjaxSearchPro.plugin.initCompactEvents = function () {
	let $this = this,
		scrollTopx = 0;

	$this.n('promagnifier').on('click', function () {
		let compact = $this.n('search').attr('data-asp-compact') || 'closed';

		scrollTopx = window.scrollY;
		$this.hideSettings?.();
		$this.hideResults();

		if (compact === 'closed') {
			$this.openCompact();
			$this.n('text').trigger('focus');
		} else {
			// noinspection JSUnresolvedVariable
			if (!$this.o.compact.closeOnMagnifier) return;
			$this.closeCompact();
			$this.searchAbort();
			$this.n('proloading').css('display', 'none');
		}
	});

}

export default AjaxSearchPro;