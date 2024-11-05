import $ from "domini";
import instances from "./instances.js";
import {api} from "./api";
import {ASP_Extended, ASP_Data, ScriptStack, SearchInstance} from "../../types/typings";
import Base64 from "../../external/helpers/base64";

/**
 * The data sent via the enqueued script before the actual script
 */
const ASP: ASP_Data = window.ASP;

/**
 * Later this is merged into window.ASP as well
 */
const ASP_EXTENDED: ASP_Extended = {
	instances: instances,
	instance_args: [],
	api: api,
	initialized: false,

	initializeAllSearches: function () {
		const instances = this.getInstances();
		instances.forEach(function (data, i) {
			// noinspection JSUnusedAssignment
			$.fn._('.asp_m_' + i).forEach(function (el) {
				if (typeof el.hasAsp != 'undefined') {
					return true;
				}
				el.hasAsp = true;
				return $(el).ajaxsearchpro(data);
			});
		});
	},

	initializeSearchByID: function (id: number, instance: number = 0) {
		const data = this.getInstance(id);
		const selector = instance === 0 ? '.asp_m_' + id : '.asp_m_' + id + '_' + instance;
		// noinspection JSUnusedAssignment
		$.fn._(selector).forEach(function (el) {
			if (typeof el.hasAsp != 'undefined') {
				return true;
			}
			el.hasAsp = true;
			return $(el).ajaxsearchpro(data);
		});
	},

	getInstances: function (): SearchInstance[] {
		$.fn._('.asp_init_data').forEach((el)=>{
			const id = parseInt(el.dataset['aspId'] || '');
			let data;
			if (typeof el.dataset['aspdata'] != 'undefined') {
				data = Base64.decode(el.dataset['aspdata']);
			}
			if (typeof data === "undefined" || data === "") return true;
			this.instance_args[id] = JSON.parse(data);
		});
		return this.instance_args;
	},

	getInstance: function(id: number): SearchInstance {
		if ( typeof this.instance_args[id] !== 'undefined' ) {
			return this.instance_args[id];
		}
		return this.getInstances()[id];
	},

	initialize: function (id?: number) {
		// Some weird ajax loader problem prevention
		if (typeof ASP.version == 'undefined') {
			return false;
		}

		if (ASP.script_async_load || ASP.init_only_in_viewport) {
			const searches = document.querySelectorAll('.asp_w_container');
			if (searches.length) {
				const observer = new IntersectionObserver((entries) => {
					entries.forEach((entry) => {
						if (entry.isIntersecting) {
							const id = parseInt((entry.target as HTMLElement).dataset.id ?? '0');
							const instance = parseInt((entry.target as HTMLElement).dataset.instance ?? '0');
							this.initializeSearchByID(id, instance);
							observer.unobserve(entry.target);
						}
					});
				});
				searches.forEach(function (search: Element & {_is_observed: boolean} ) {
					if ( typeof search._is_observed !== 'undefined' ) {
						return;
					}
					search._is_observed = true;
					observer.observe(search);
				});
			}
			this.getInstances().forEach((inst, id) => {
				/**
				 * Compact layout is problematic for intersection observer,
				 * so regardless of the position, it should be loaded immediately.
				 */
				if ( inst.compact.enabled ) {
					this.initializeSearchByID(id);
				}
			});
		} else {
			if ( typeof id === 'undefined' ) {
				this.initializeAllSearches();
			} else {
				this.initializeSearchByID(id);
			}
		}


		this.initializeMutateDetector();
		this.initializeHighlight();
		this.initializeOtherEvents();

		this.initialized = true;

		return true;
	},

	initializeHighlight: function () {
		if (ASP.highlight.enabled) {
			const data = ASP.highlight.data;
			let	selector = data.selector !== '' && $(data.selector).length > 0 ? data.selector : 'article',
				$highlighted;
			selector = $(selector).length > 0 ? selector : 'body';

			const s = new URLSearchParams(location.search),
				  phrase = s.get('s') || s.get('asp_highlight');
			$(selector).unhighlight({className: 'asp_single_highlighted_' + data.id});
			if (phrase !== null && phrase.trim() !== '') {
				// noinspection JSUnresolvedVariable
				$(selector).highlight(phrase.trim().split(' '), {
					element: 'span',
					className: 'asp_single_highlighted_' + data.id,
					wordsOnly: data.whole,
					excludeParents: '.asp_w, .asp-try'
				});
				$highlighted = $('.asp_single_highlighted_' + data.id);
				if (data.scroll && $highlighted.length > 0) {
					let stop = $highlighted.offset().top - 120;
					const $adminbar = $("#wpadminbar");
					if ($adminbar.length > 0)
						stop -= $adminbar.height();
					// noinspection JSUnresolvedVariable
					stop = stop + data.scroll_offset;
					stop = stop < 0 ? 0 : stop;
					$('html').animate({
						"scrollTop": stop
					}, 500);
				}
			}
			return false;
		}
		return false;
	},

	initializeOtherEvents: function () {
		let ttt: ReturnType<typeof setTimeout>, ts: string;
		const $body = $('body');
		// Known slide-out and other type of menus to initialize on click
		ts = '#menu-item-search, .fa-search, .fa, .fas';
		// Avada theme
		ts = ts + ', .fusion-flyout-menu-toggle, .fusion-main-menu-search-open';
		// Be theme
		ts = ts + ', #search_button';
		// The 7 theme
		ts = ts + ', .mini-search.popup-search';
		// Flatsome theme
		ts = ts + ', .icon-search';
		// Enfold theme
		ts = ts + ', .menu-item-search-dropdown';
		// Uncode theme
		ts = ts + ', .mobile-menu-button';
		// Newspaper theme
		ts = ts + ', .td-icon-search, .tdb-search-icon';
		// Bridge theme
		ts = ts + ', .side_menu_button, .search_button';
		// Jupiter theme
		ts = ts + ', .raven-search-form-toggle';
		// Elementor trigger lightbox & other elementor stuff
		ts = ts + ', [data-elementor-open-lightbox], .elementor-button-link, .elementor-button';
		ts = ts + ', i[class*=-search], a[class*=-search]';

		// Attach this to the document ready, as it may not attach if this is loaded early
		$body.on('click touchend', ts, ()=>{
			clearTimeout(ttt);
			ttt = setTimeout(()=>{
				this.initializeAllSearches();
			}, 300);
		});

		// Elementor popup events (only works with jQuery)
		if (typeof window.jQuery != 'undefined') {
			window.jQuery(document).on('elementor/popup/show', ()=>{
				setTimeout(()=>{
					this.initializeAllSearches();
				}, 10);
			});
		}
	},

	initializeMutateDetector: function () {
		let t: ReturnType<typeof setTimeout>;
		if (typeof ASP.detect_ajax != "undefined" && ASP.detect_ajax) {
			const o = new MutationObserver(()=>{
				clearTimeout(t);
				t = setTimeout( ()=>{
					this.initializeAllSearches();
				}, 500);
			});
			const body = document.querySelector("body");
			if ( body == null ) {
				return;
			}
			o.observe(body, {subtree: true, childList: true});
		}
	},

	loadScriptStack: function (stack: ScriptStack) {
		let scriptTag;
		if (stack.length > 0) {
			const script = stack.shift();
			if ( script === undefined ) {
				return;
			}
			scriptTag = document.createElement('script');
			scriptTag.src = script['src'];
			scriptTag.onload = ()=>{
				if (stack.length > 0) {
					this.loadScriptStack(stack)
				} else {
					if (typeof window.WPD.AjaxSearchPro != 'undefined') {
						$._fn.plugin('ajaxsearchpro', window.WPD.AjaxSearchPro.plugin);
					}
					this.ready();
				}
			}
			document.body.appendChild(scriptTag);
		}
	},

	ready: function () {
		// 'interactive' is alternative to DOMContentLoaded, 'complete' is load
		const documentReady = () => (document.readyState === 'complete' || document.readyState === 'interactive' || document.readyState === "loaded");
		// @ts-expect-error At this stage all of these are possible
		if ( documentReady() ) {
			// document is already ready to go
			this.initialize();
		} else {
			// Call within arrow function directly, otherwise "this" is propagated to window
			window.addEventListener("DOMContentLoaded", () => {
				this.initialize();
			});
			// Cloudflare Rocket Loader fiddles with DOMContentLoaded, so it needs to be solved via statechange
			document.addEventListener("readystatechange", () => {;
				if ( documentReady() ) {
					this.initialize();
				}
			});
		}
	},

	init: function () {
		// noinspection JSUnresolvedVariable
		if (ASP.script_async_load) {   // Opimized Normal
			// noinspection JSUnresolvedVariable
			this.loadScriptStack(ASP.additional_scripts);
		} else {
			if (typeof window.WPD.AjaxSearchPro !== 'undefined') {   // Classic normal
				this.ready();
			}
		}
	},
}

export default ASP_EXTENDED;