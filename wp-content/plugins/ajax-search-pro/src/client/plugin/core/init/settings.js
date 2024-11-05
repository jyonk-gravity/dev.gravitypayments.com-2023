import AjaxSearchPro from "../base.js";
import {default as $} from "domini";

"use strict";
let helpers = AjaxSearchPro.helpers;
/**
 * This function should be called on-demand to init the settings. Do not call on init, only when needed.
 */
AjaxSearchPro.plugin.initSettings = function () {
	if (!this.settingsInitialized) {
		this.loadASPFonts?.();
		this.initSettingsBox?.();
		this.initSettingsEvents?.();
		this.initButtonEvents?.();
		this.initNoUIEvents?.();
		this.initDatePicker?.();
		this.initSelect2?.();
		this.initFacetEvents?.();
	}
}
AjaxSearchPro.plugin.initSettingsBox = function () {
	let $this = this;
	let appendSettingsTo = function ($el) {
		let old = $this.n('searchsettings').get(0);
		$this.nodes.searchsettings = $this.nodes.searchsettings.clone();
		$el.append($this.nodes.searchsettings);


		$(old).find('*[id]').forEach(function (el) {
			if (el.id.indexOf('__original__') < 0) {
				el.id = '__original__' + el.id;
			}
		});
		$this.n('searchsettings').find('*[id]').forEach(function (el) {
			if (el.id.indexOf('__original__') > -1) {
				el.id = el.id.replace('__original__', '');
			}
		});
	}
	let makeSetingsBlock = function () {
		$this.n('searchsettings').attr(
			"id",
			$this.n('searchsettings').attr("id").replace('prosettings', 'probsettings')
		);
		$this.n('searchsettings').removeClass('asp_s asp_s_' + $this.o.id + ' asp_s_' + $this.o.rid)
			.addClass('asp_sb asp_sb_' + $this.o.id + ' asp_sb_' + $this.o.rid);
		$this.dynamicAtts['blocking'] = true;
	}
	let makeSetingsHover = function () {
		$this.n('searchsettings').attr(
			"id",
			$this.n('searchsettings').attr("id").replace('probsettings', 'prosettings')
		);
		$this.n('searchsettings').removeClass('asp_sb asp_sb_' + $this.o.id + ' asp_sb_' + $this.o.rid)
			.addClass('asp_s asp_s_' + $this.o.id + ' asp_s_' + $this.o.rid);
		$this.dynamicAtts['blocking'] = false;
	}


	// Calculates the settings animation attributes
	$this.initSettingsAnimations?.();

	// noinspection JSUnresolvedVariable
	if (
		($this.o.compact.enabled && $this.o.compact.position === 'fixed') ||
		(helpers.isMobile() && $this.o.mobile.force_sett_hover)
	) {
		makeSetingsHover();
		appendSettingsTo($('body'));

		$this.n('searchsettings').css({
			'position': 'absolute'
		});
		$this.dynamicAtts['blocking'] = false;
	} else {
		if ($this.n('settingsAppend').length > 0) {
			// There is already a results box there
			if ($this.n('settingsAppend').find('.asp_ss_' + $this.o.id).length > 0) {
				$this.nodes.searchsettings = $this.nodes.settingsAppend.find('.asp_ss_' + $this.o.id);
				if (typeof $this.nodes.searchsettings.get(0).referenced !== 'undefined') {
					++$this.nodes.searchsettings.get(0).referenced;
				} else {
					$this.nodes.searchsettings.get(0).referenced = 1;
				}
			} else {
				if (!$this.att('blocking')) {
					makeSetingsBlock();
				}
				appendSettingsTo($this.nodes.settingsAppend);
			}

		} else if (!$this.att('blocking')) {
			appendSettingsTo($('body'));
		}
	}
	$this.n('searchsettings').get(0).id = $this.n('searchsettings').get(0).id.replace('__original__', '');
	$this.detectAndFixFixedPositioning();

	$this.settingsInitialized = true;
}
AjaxSearchPro.plugin.initSettingsAnimations = function () {
	let $this = this;
	$this.settAnim = {
		"showClass": "",
		"showCSS": {
			"visibility": "visible",
			"display": "block",
			"opacity": 1,
			"animation-duration": $this.animOptions.settings.dur + 'ms'
		},
		"hideClass": "",
		"hideCSS": {
			"visibility": "hidden",
			"opacity": 0,
			"display": "none"
		},
		"duration": $this.animOptions.settings.dur + 'ms'
	};

	if ($this.animOptions.settings.anim === "fade") {
		$this.settAnim.showClass = "asp_an_fadeIn";
		$this.settAnim.hideClass = "asp_an_fadeOut";
	}

	if ($this.animOptions.settings.anim === "fadedrop" &&
		!$this.att('blocking')) {
		$this.settAnim.showClass = "asp_an_fadeInDrop";
		$this.settAnim.hideClass = "asp_an_fadeOutDrop";
	} else if ($this.animOptions.settings.anim === "fadedrop") {
		// If does not support transition, or it is blocking layout, fall back to fade
		$this.settAnim.showClass = "asp_an_fadeIn";
		$this.settAnim.hideClass = "asp_an_fadeOut";
	}

	$this.n('searchsettings').css({
		"-webkit-animation-duration": $this.settAnim.duration + "ms",
		"animation-duration": $this.settAnim.duration + "ms"
	});
}
export default AjaxSearchPro;