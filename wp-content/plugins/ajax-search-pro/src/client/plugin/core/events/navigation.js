import AjaxSearchPro from "../base.js";
import {default as $} from "domini";

"use strict";
AjaxSearchPro.plugin.initNavigationEvents = function () {
	let $this = this;

	let handler = function (e) {
		let keycode = e.keyCode || e.which;
		// noinspection JSUnresolvedVariable
		if (
			$('.item', $this.n('resultsDiv')).length > 0 && $this.n('resultsDiv').css('display') !== 'none' &&
			$this.o.resultstype === "vertical"
		) {
			if (keycode === 40 || keycode === 38) {
				let $hovered = $this.n('resultsDiv').find('.item.hovered');
				$this.n('text').trigger('blur');
				if ($hovered.length === 0) {
					$this.n('resultsDiv').find('.item').first().addClass('hovered');
				} else {
					if (keycode === 40) {
						if ($hovered.next('.item').length === 0) {
							$this.n('resultsDiv').find('.item').removeClass('hovered').first().addClass('hovered');
						} else {
							$hovered.removeClass('hovered').next('.item').addClass('hovered');
						}
					}
					if (keycode === 38) {
						if ($hovered.prev('.item').length === 0) {
							$this.n('resultsDiv').find('.item').removeClass('hovered').last().addClass('hovered');
						} else {
							$hovered.removeClass('hovered').prev('.item').addClass('hovered');
						}
					}
				}
				e.stopPropagation();
				e.preventDefault();
				if (!$this.n('resultsDiv').find('.resdrg .item.hovered').inViewPort(50, $this.n('resultsDiv').get(0))) {
					let n = $this.n('resultsDiv').find('.resdrg .item.hovered').get(0);
					if (n != null && typeof n.scrollIntoView != "undefined") {
						n.scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"});
					}
				}
			}

			// Trigger click on return key
			if (keycode === 13 && $('.item.hovered', $this.n('resultsDiv')).length > 0) {
				e.stopPropagation();
				e.preventDefault();
				$('.item.hovered a.asp_res_url', $this.n('resultsDiv')).get(0).click();
			}

		}
	};
	$this.documentEventHandlers.push({
		'node': document,
		'event': 'keydown',
		'handler': handler
	});
	$(document).on('keydown', handler);
}
export default AjaxSearchPro;