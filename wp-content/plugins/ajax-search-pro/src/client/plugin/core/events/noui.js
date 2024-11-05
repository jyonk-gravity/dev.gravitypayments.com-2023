import AjaxSearchPro from "../base.js";
import {default as $} from "domini";

"use strict";
AjaxSearchPro.plugin.initNoUIEvents = function () {
	let $this = this,
		$sett = $this.nodes.searchsettings,
		slider;

	$sett.find("div[class*=noui-slider-json]").forEach(function (el, index) {

		let jsonData = $(this).data("aspnoui");
		if (typeof jsonData === "undefined") return false;

		jsonData = WPD.Base64.decode(jsonData);
		if (typeof jsonData === "undefined" || jsonData === "") return false;

		let args = JSON.parse(jsonData);
		Object.keys(args.links).forEach(function (k) {
			args.links[k].target = '#' + $sett.get(0).id + ' ' + args.links[k].target;
		});
		if ($(args.node, $sett).length > 0) {
			slider = $(args.node, $sett).get(0);
			// Initialize the main
			let $handles = $(el).parent().find('.asp_slider_hidden');
			if ($handles.length > 1) {
				args.main.start = [$handles.first().val(), $handles.last().val()];
			} else {
				args.main.start = [$handles.first().val()];
			}
			if (typeof noUiSlider !== 'undefined') {
				if (typeof slider.noUiSlider != 'undefined') {
					slider.noUiSlider.destroy();
				}
				slider.innerHTML = '';
				noUiSlider.create(slider, args.main);
			} else {
				// NoUiSlider is not included within the scripts, alert the user!
				return false;
			}

			$this.noUiSliders[index] = slider;

			slider.noUiSlider.on('update', function (values, handle) {
				let value = values[handle];
				if (handle) { // true when 1, if upper
					// Params: el, i, arr
					args.links.forEach(function (el) {
						let wn = wNumb(el.wNumb);
						if (el.handle === "upper") {
							if ($(el.target, $sett).is('input'))
								$(el.target, $sett).val(value);
							else
								$(el.target, $sett).html(wn.to(parseFloat(value)));
						}
						$(args.node, $sett).on('slide', function (e) {
							e.preventDefault();
						});
					});
				} else {        // 0, lower
					// Params: el, i, arr
					args.links.forEach(function (el) {
						let wn = wNumb(el.wNumb);
						if (el.handle === "lower") {
							if ($(el.target, $sett).is('input'))
								$(el.target, $sett).val(value);
							else
								$(el.target, $sett).html(wn.to(parseFloat(value)));
						}
						$(args.node, $sett).on('slide', function (e) {
							e.preventDefault();
						});
					});
				}
			});
		}
	});

}
export default AjaxSearchPro;