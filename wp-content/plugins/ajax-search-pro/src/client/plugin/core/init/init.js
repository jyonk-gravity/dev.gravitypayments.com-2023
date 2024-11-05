import AjaxSearchPro from "../base.js";
import {default as $} from "domini";

"use strict";
let helpers = AjaxSearchPro.helpers;
AjaxSearchPro.plugin.init = function (options, elem) {
	let $this = this;

	$this.searching = false;
	$this.triggerPrevState = false;

	$this.isAutoP = false;
	$this.autopStartedTheSearch = false;
	$this.autopData = {};

	$this.settingsInitialized = false;
	$this.resultsInitialized = false;
	$this.settingsChanged = false;
	$this.resultsOpened = false;
	$this.post = null;
	$this.postAuto = null;
	$this.savedScrollTop = 0;   // Save the window scroll on IOS devices
	$this.savedContainerTop = 0;
	$this.disableMobileScroll = false;
	/**
	 * on IOS touch (iPhone, iPad etc..) the 'click' event does not fire, when not bound to a clickable element
	 * like a link, so instead, use touchend
	 * Stupid solution, but it works..
	 */
	$this.clickTouchend = 'click touchend';
	$this.mouseupTouchend = 'mouseup touchend';
	// NoUiSliders storage
	$this.noUiSliders = [];

	// An object to store various timeout events across methods
	$this.timeouts = {
		"compactBeforeOpen": null,
		"compactAfterOpen": null,
		"search": null,
		"searchWithCheck": null
	};

	$this.eh = {}; // this.EventHandlers -> storage for event handler references
	// Document and Window event handlers. Used to detach them in the destroy() method
	$this.documentEventHandlers = [
		/**
		 * {"node": document|window, "event": event_name, "handler": function()..}
		 */
	];

	$this.currentPage = 1;
	$this.currentPageURL = location.href;
	$this.isotopic = null;
	$this.sIsotope = null;
	$this.lastSuccesfulSearch = ''; // Holding the last phrase that returned results
	$this.lastSearchData = {};      // Store the last search information
	$this._no_animations = false; // Force override option to show animations
	// Repetitive call related
	$this.call_num = 0;
	$this.results_num = 0;

	// this.n and this.o available afterwards
	// also, it corrects the clones and fixes the node varialbes
	$this.o = $.fn.extend({}, options);
	$this.dynamicAtts = {};
	$this.nodes = {};
	$this.nodes.search = $(elem);


	// Make parsing the animation settings easier
	if (helpers.isMobile())
		$this.animOptions = $this.o.animations.mob;
	else
		$this.animOptions = $this.o.animations.pc;

	// Fill up the this.n and correct the cloned notes as well

	$this.initNodeVariables();
	/**
	 * Default animation opacity. 0 for IN types, 1 for all the other ones. This ensures the fluid
	 * animation. Wrong opacity causes flashes.
	 */
	$this.animationOpacity = $this.animOptions.items.indexOf("In") < 0 ? "opacityOne" : "opacityZero";

	// Result page live loader disabled for compact layout modes
	$this.o.resPage.useAjax = $this.o.compact.enabled ? 0 : $this.o.resPage.useAjax;
	// Mobile changes
	if (helpers.isMobile()) {
		$this.o.trigger.type = $this.o.mobile.trigger_on_type;
		$this.o.trigger.click = $this.o.mobile.click_action;
		$this.o.trigger.click_location = $this.o.mobile.click_action_location;
		$this.o.trigger.return = $this.o.mobile.return_action;
		$this.o.trigger.return_location = $this.o.mobile.return_action_location;
		$this.o.trigger.redirect_url = $this.o.mobile.redirect_url;
		$this.o.trigger.elementor_url = $this.o.mobile.elementor_url;
	}
	$this.o.redirectOnClick = $this.o.trigger.click !== 'ajax_search' && $this.o.trigger.click !== 'nothing';
	$this.o.redirectOnEnter = $this.o.trigger.return !== 'ajax_search' && $this.o.trigger.return !== 'nothing';
	if ($this.usingLiveLoader()) {
		$this.o.trigger.type = $this.o.resPage.trigger_type;
		$this.o.trigger.facet = $this.o.resPage.trigger_facet;
		if ($this.o.resPage.trigger_magnifier) {
			$this.o.redirectOnClick = 0;
			$this.o.trigger.click = 'ajax_search';
		}

		if ($this.o.resPage.trigger_return) {
			$this.o.redirectOnEnter = 0;
			$this.o.trigger.return = 'ajax_search';
		}
	}

	// Reset autocomplete
	//$this.nodes.textAutocomplete.val('');

	if ($this.o.compact.overlay && $("#asp_absolute_overlay").length === 0) {
		$('body').append("<div id='asp_absolute_overlay'></div>");
	}

	if ($this.usingLiveLoader()) {
		$this.initLiveLoaderPopState?.();
	}

	// Fixes the fixed layout mode if compact mode is active and touch device fixes
	if (typeof $this.initCompact !== "undefined") {
		$this.initCompact();
	}

	// Try detecting a parent fixed position, and change the results and settings position accordingly
	// $this.detectAndFixFixedPositioning();

	// Sets $this.dragging to true if the user is dragging on a touch device
	$this.monitorTouchMove();

	// Rest of the events
	$this.initEvents();

	// Auto populate init
	$this.initAutop();

	// Etc stuff..
	$this.initEtc();

	// Custom hooks
	$this.hooks();

	// Init complete event trigger
	$this.n('s').trigger("asp_init_search_bar", [$this.o.id, $this.o.iid], true, true);

	return this;
}

AjaxSearchPro.plugin.n = function (k) {
	if (typeof this.nodes[k] !== 'undefined') {
		return this.nodes[k];
	} else {
		switch (k) {
			case 's':
				this.nodes[k] = this.nodes.search;
				break;
			case 'container':
				this.nodes[k] = this.nodes.search.closest('.asp_w_container');
				break;
			case 'searchsettings':
				this.nodes[k] = $('.asp_ss', this.n('container'));
				break;
			case 'resultsDiv':
				this.nodes[k] = $('.asp_r', this.n('container'));
				break;
			case 'probox':
				this.nodes[k] = $('.probox', this.nodes.search);
				break;
			case 'proinput':
				this.nodes[k] = $('.proinput', this.nodes.search);
				break;
			case 'text':
				this.nodes[k] = $('.proinput input.orig', this.nodes.search);
				break;
			case 'textAutocomplete':
				this.nodes[k] = $('.proinput input.autocomplete', this.nodes.search);
				break;
			case 'proloading':
				this.nodes[k] = $('.proloading', this.nodes.search);
				break;
			case 'proclose':
				this.nodes[k] = $('.proclose', this.nodes.search);
				break;
			case 'promagnifier':
				this.nodes[k] = $('.promagnifier', this.nodes.search);
				break;
			case 'prosettings':
				this.nodes[k] = $('.prosettings', this.nodes.search);
				break;
			case 'settingsAppend':
				this.nodes[k] = $('#wpdreams_asp_settings_' + this.o.id);
				break;
			case 'resultsAppend':
				this.nodes[k] = $('#wpdreams_asp_results_' + this.o.id);
				break;
			case 'trythis':
				this.nodes[k] = $("#asp-try-" + this.o.rid);
				break;
			case 'hiddenContainer':
				this.nodes[k] = $('.asp_hidden_data', this.n('container'));
				break;
			case 'aspItemOverlay':
				this.nodes[k] = $('.asp_item_overlay', this.n('hiddenContainer'));
				break;
			case 'showmoreContainer':
				this.nodes[k] = $('.asp_showmore_container', this.n('resultsDiv'));
				break;
			case 'showmore':
				this.nodes[k] = $('.showmore', this.n('resultsDiv'));
				break;
			case 'items':
				this.nodes[k] = $('.item', this.n('resultsDiv')).length > 0 ? $('.item', this.n('resultsDiv')) : $('.photostack-flip', this.n('resultsDiv'));
				break;
			case 'results':
				this.nodes[k] = $('.results', this.n('resultsDiv'));
				break;
			case 'resdrg':
				this.nodes[k] = $('.resdrg', this.n('resultsDiv'));
				break;
		}
		return this.nodes[k];
	}
}

AjaxSearchPro.plugin.att = function (k) {
	if (typeof this.dynamicAtts[k] !== 'undefined') {
		return this.dynamicAtts[k];
	} else {
		switch (k) {
			case 'blocking':
				this.dynamicAtts[k] = this.n('searchsettings').hasClass('asp_sb');
		}
	}
	return this.dynamicAtts[k];
}

AjaxSearchPro.plugin.initNodeVariables = function () {
	let $this = this;

	$this.o.id = $this.nodes.search.data('id');
	$this.o.iid = $this.nodes.search.data('instance');
	$this.o.rid = $this.o.id + "_" + $this.o.iid;
	// Fix any potential clones and adjust the variables
	$this.fixClonedSelf();
}

AjaxSearchPro.plugin.initEvents = function () {
	this.initSettingsSwitchEvents?.();
	this.initOtherEvents();
	this.initTryThisEvents();
	this.initMagnifierEvents();
	this.initInputEvents();
	if (this.o.compact.enabled) {
		this.initCompactEvents();
	}
}
export default AjaxSearchPro;