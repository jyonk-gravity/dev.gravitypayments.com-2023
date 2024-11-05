import AjaxSearchPro from "../plugin/core/base.js";
import $ from "domini";

// Declare global variables to avoid TypeScript errors
declare var elementorFrontend: any;
declare var jQuery: JQueryStatic;

const helpers = AjaxSearchPro.helpers;

class ElementorAddon {
	name: string = "Elementor Widget Fixes";

	init(): void {
		const { Hooks } = helpers;
		Hooks.addFilter('asp/init/etc', this.fixElementorPostPagination.bind(this), 10, this);
		Hooks.addFilter('asp/live_load/selector', this.fixSelector.bind(this), 10, this);
		Hooks.addFilter('asp/live_load/start', this.start.bind(this), 10, this);
		Hooks.addFilter('asp/live_load/finished', this.finished.bind(this), 10, this);
	}

	fixSelector(selector: string): string {
		if (selector.includes('asp_es_')) {
			selector += ' .elementor-widget-container';
		}
		return selector;
	}

	start(url: string, obj: any, selector: string, widget: HTMLElement): void {
		const searchSettingsSerialized = obj.n('searchsettings').find('form').serialize();
		const textValue = obj.n('text').val().trim();
		const isNewSearch = (searchSettingsSerialized + textValue) !== obj.lastSuccesfulSearch;

		if (!isNewSearch && $(widget).find('.e-load-more-spinner').length > 0) {
			$(widget).css('opacity', '1');
		}
		$(selector).parent().removeClass('e-load-more-pagination-end');
	}

	finished(url: string, obj: any, selector: string, widget: HTMLElement): void {
		const $el = $(widget);
		if (
			selector.includes('asp_es_') &&
			typeof elementorFrontend !== 'undefined' &&
			typeof elementorFrontend.init !== 'undefined' &&
			$el.find('.asp_elementor_nores').length === 0
		) {
			const widgetType = $el.parent().data('widget_type') || '';
			if (widgetType !== '' && typeof jQuery !== 'undefined') {
				elementorFrontend.hooks.doAction('frontend/element_ready/' + widgetType, jQuery($el.parent().get(0) as HTMLElement));
			}

			// Fix Elementor Pagination
			this.fixElementorPostPagination(obj, url);

			if (obj.o.scrollToResults.enabled) {
				this.scrollToResultsIfNeeded($el);
			}

			// Elementor results action
			obj.n('s').trigger("asp_elementor_results", [obj.o.id, obj.o.iid, $el.parent().get(0)], true, true);
		}
	}

	scrollToResultsIfNeeded($el): void {
		const $first = $el.find('.elementor-post, .product').first();
		if ($first.length && !$first.isInViewport(40)) {
			($first.get(0) as HTMLElement).scrollIntoView({ behavior: "smooth", block: "center", inline: "nearest" });
		}
	}

	fixElementorPostPagination(obj: any, url?: string): any {
		const $es = $('.asp_es_' + obj.o.id);
		url = url || location.href;

		if (!$es.length) {
			return obj;
		}

		const urlObj = new URL(url);
		if (!urlObj.searchParams.size) {
			return obj;
		}

		this.elementorHideSpinner($es.get(0) as HTMLElement);
		urlObj.searchParams.delete('asp_force_reset_pagination');

		const $loadMoreAnchor = $es.find('.e-load-more-anchor');
		const paginationLinks = $es.find('.elementor-pagination a, .elementor-widget-container .woocommerce-pagination a');

		if ($loadMoreAnchor.length > 0 && !paginationLinks.length) {
			const $widgetContainer = $es.find('.elementor-widget-container').get(0);

			const fixAnchor = () => {
				const pageData = $loadMoreAnchor.data('page');
				const page = pageData ? parseInt(pageData, 10) + 1 : 2;
				urlObj.searchParams.set('page', page.toString());
				$loadMoreAnchor.data('next-page', urlObj.href);
				$loadMoreAnchor
					.next('.elementor-button-wrapper')
					.find('a')
					.attr('href', urlObj.href);
			};

			if ($widgetContainer) {
				const observer = new MutationObserver(() => {
					fixAnchor();
					console.log('Mutation observed: fixing anchor.');
				});
				observer.observe($widgetContainer, {
					childList: true,
					subtree: true,
				});
			}

			fixAnchor();
		} else {
			paginationLinks.each(function () {
				const $link = $(this);
				const href = $link.attr('href') || '';
				const itemUrlObj = new URL(href, window.location.origin);
				if (!itemUrlObj.searchParams.has('asp_ls')) {
					urlObj.searchParams.forEach((value, key) => itemUrlObj.searchParams.set(key, value));
				} else {
					itemUrlObj.searchParams.delete('asp_force_reset_pagination');
				}
				$link.attr('href', itemUrlObj.href);
			});
		}

		// Return the modified object as required by the filter hook
		return obj;
	}

	elementorHideSpinner(widget: HTMLElement): void {
		$(widget)
			.removeClass('e-load-more-pagination-loading')
			.find('.eicon-animation-spin')
			.removeClass('eicon-animation-spin');
	}
}

AjaxSearchPro.addons.add(new ElementorAddon());
export default AjaxSearchPro;
