import AjaxSearchPro from "../base.js";
import {default as $} from "domini";

"use strict";
let helpers = AjaxSearchPro.helpers;

AjaxSearchPro.plugin.showResults = function () {
	let $this = this;

	helpers.Hooks.applyFilters('asp/results/show/start', $this);

	$this.initResults();

	// Create the scrollbars if needed
	// noinspection JSUnresolvedVariable
	if ($this.o.resultstype === 'horizontal') {
		$this.createHorizontalScroll();
	} else {
		// noinspection JSUnresolvedVariable
		if ($this.o.resultstype === 'vertical') {
			$this.createVerticalScroll();
		}
	}

	// noinspection JSUnresolvedVariable
	switch ($this.o.resultstype) {
		case 'horizontal':
			$this.showHorizontalResults();
			break;
		case 'vertical':
			$this.showVerticalResults();
			break;
		case 'polaroid':
			$this.showPolaroidResults();
			//$this.disableMobileScroll = true;
			break;
		case 'isotopic':
			$this.showIsotopicResults();
			break;
		default:
			$this.showHorizontalResults();
			break;
	}

	$this.showAnimatedImages();
	$this.hideLoader();

	$this.n('proclose').css({
		display: "block"
	});

	// When opening the results box only
	// noinspection JSUnresolvedVariable
	if (helpers.isMobile() && $this.o.mobile.hide_keyboard && !$this.resultsOpened)
		document.activeElement.blur();

	// noinspection JSUnresolvedVariable
	if ($this.o.settingsHideOnRes && $this.att('blocking') === false)
		$this.hideSettings?.();

	$this.eh.resulsDivHoverMouseEnter = $this.eh.resulsDivHoverMouseEnter || function () {
		$('.item', $this.n('resultsDiv')).removeClass('hovered');
		$(this).addClass('hovered');
	};
	$this.eh.resulsDivHoverMouseLeave = $this.eh.resulsDivHoverMouseLeave || function () {
		$('.item', $this.n('resultsDiv')).removeClass('hovered');
	};
	$this.n('resultsDiv').find('.item').on('mouseenter', $this.eh.resulsDivHoverMouseEnter);
	$this.n('resultsDiv').find('.item').on('mouseleave', $this.eh.resulsDivHoverMouseLeave);

	$this.fixSettingsAccessibility();
	$this.resultsOpened = true;

	helpers.Hooks.addFilter('asp/results/show/end', $this);
}

AjaxSearchPro.plugin.hideResults = function (blur) {
	let $this = this;
	blur = typeof blur == 'undefined' ? true : blur;

	$this.initResults();

	if (!$this.resultsOpened) return false;

	$this.n('resultsDiv').removeClass($this.resAnim.showClass).addClass($this.resAnim.hideClass);
	setTimeout(function () {
		$this.n('resultsDiv').css($this.resAnim.hideCSS);
	}, $this.resAnim.duration);

	$this.n('proclose').css({
		display: "none"
	});

	if (helpers.isMobile() && blur)
		document.activeElement.blur();

	$this.resultsOpened = false;
	// Re-enable mobile scrolling, in case it was disabled
	//$this.disableMobileScroll = false;

	if (typeof $this.ptstack != "undefined")
		delete $this.ptstack;

	$this.hideArrowBox?.();

	$this.n('s').trigger("asp_results_hide", [$this.o.id, $this.o.iid], true, true);
}

AjaxSearchPro.plugin.updateResults = function (html) {
	let $this = this;
	if (
		html.replace(/^\s*[\r\n]/gm, "") === "" ||
		$(html).hasClass('asp_nores') ||
		$(html).find('.asp_nores').length > 0
	) {
		// Something went wrong, as the no-results container was returned
		$this.n('showmoreContainer').css("display", "none");
		$('span', $this.n('showmore')).html("");
	} else {
		// noinspection JSUnresolvedVariable
		if (
			$this.o.resultstype === 'isotopic' &&
			$this.call_num > 0 &&
			$this.isotopic != null &&
			typeof $this.isotopic.appended != 'undefined' &&
			$this.n('items').length > 0
		) {
			let $items = $(html),
				$last = $this.n('items').last(),
				last = parseInt($this.n('items').last().attr('data-itemnum'));
			$items.get().forEach(function (el) {
				$(el).attr('data-itemnum', ++last).css({
					'width': $last.css('width'),
					'height': $last.css('height')
				})
			});
			$this.n('resdrg').append($items);

			$this.isotopic.appended($items.get());
			$this.nodes.items = $('.item', $this.n('resultsDiv')).length > 0 ? $('.item', $this.n('resultsDiv')) : $('.photostack-flip', $this.n('resultsDiv'));
		} else {
			// noinspection JSUnresolvedVariable
			if ($this.call_num > 0 && $this.o.resultstype === 'vertical') {
				$this.n('resdrg').html($this.n('resdrg').html() + '<div class="asp_v_spacer"></div>' + html);
			} else {
				$this.n('resdrg').html($this.n('resdrg').html() + html);
			}
		}
	}
}

AjaxSearchPro.plugin.showResultsBox = function () {
	let $this = this;

	$this.initResults();

	$this.n('s').trigger("asp_results_show", [$this.o.id, $this.o.iid], true, true);

	$this.n('resultsDiv').css({
		display: 'block',
		height: 'auto'
	});

	$this.n('results').find('.item, .asp_group_header').addClass($this.animationOpacity);

	$this.n('resultsDiv').css($this.resAnim.showCSS);
	$this.n('resultsDiv').removeClass($this.resAnim.hideClass).addClass($this.resAnim.showClass);

	$this.fixResultsPosition(true);
}

AjaxSearchPro.plugin.addHighlightString = function ($items) {
	// Results highlight on results page
	let $this = this,
		phrase = $this.n('text').val().replace(/["']/g, '');

	$items = typeof $items == 'undefined' ? $this.n('items').find('a.asp_res_url') : $items;
	if ($this.o.singleHighlight && phrase !== '' && $items.length > 0) {
		$items.forEach(function () {
			try {
				const url = new URL($(this).attr('href'));
				url.searchParams.set('asp_highlight', phrase);
				url.searchParams.set('p_asid', $this.o.id);
				$(this).attr('href', url.href);
			} catch (e) {
			}
		});
	}
}

AjaxSearchPro.plugin.scrollToResults = function () {
	let $this = this,
		tolerance = Math.floor(window.innerHeight * 0.1),
		stop;

	if (
		!$this.resultsOpened ||
		$this.call_num > 0 ||
		!$this.o.scrollToResults.enabled ||
		$this.n('search').closest(".asp_preview_data").length > 0 ||
		$this.o.compact.enabled ||
		$this.n('resultsDiv').inViewPort(tolerance)
	) return;

	if ($this.o.resultsposition === "hover") {
		stop = $this.n('probox').offset().top - 20;
	} else {
		stop = $this.n('resultsDiv').offset().top - 20;
	}
	stop = stop + $this.o.scrollToResults.offset;

	let $adminbar = $("#wpadminbar");
	if ($adminbar.length > 0)
		stop -= $adminbar.height();
	stop = stop < 0 ? 0 : stop;
	window.scrollTo({top: stop, behavior: "smooth"});
}

AjaxSearchPro.plugin.scrollToResult = function (id) {
	let $el = $(id);
	if ($el.length && !$el.inViewPort(40)) {
		$el.get(0).scrollIntoView({behavior: "smooth", block: "center", inline: "nearest"});
	}
}

AjaxSearchPro.plugin.showAnimatedImages = function () {
	let $this = this;
	$this.n('items').forEach(function () {
		let $image = $(this).find('.asp_image[data-src]'),
			src = $image.data('src');
		if (typeof src != 'undefined' && src != null && src !== '' && src.indexOf('.gif') > -1) {
			if ($image.find('canvas').length === 0) {
				$image.prepend($('<div class="asp_item_canvas"><canvas></canvas></div>').get(0));
				let c = $(this).find('canvas').get(0),
					$cc = $(this).find('.asp_item_canvas'),
					ctx = c.getContext("2d"),
					img = new Image;
				img.crossOrigin = "anonymous";
				img.onload = function () {
					$(c).attr({
						"width": img.width,
						"height": img.height
					});
					ctx.drawImage(img, 0, 0, img.width, img.height); // Or at whatever offset you like
					$cc.css({
						"background-image": 'url(' + c.toDataURL() + ')'
					});
				};
				img.src = src;
			}
		}
	});
}

AjaxSearchPro.plugin.updateNoResultsHeader = function () {
	let $this = this,
		$new_nores = $this.n('resdrg').find('.asp_nores'), $old_nores;

	if ($new_nores.length > 0) {
		$new_nores = $new_nores.detach();
	}

	$old_nores = $this.n('resultsDiv').find('.asp_nores')
	if ($old_nores.length > 0) {
		$old_nores.remove();
	}

	if ($new_nores.length > 0) {
		$this.n('resultsDiv').prepend($new_nores);

		$this.n('resultsDiv').find(".asp_keyword").on('click', function () {
			$this.n('text').val(helpers.decodeHTMLEntities($(this).text()));
			$this.n('textAutocomplete').val('');
			// Is any ajax trigger enabled?
			if (
				!$this.o.redirectOnClick ||
				!$this.o.redirectOnEnter ||
				$this.o.trigger.type
			) {
				$this.search();
			}
		});
	}
}

AjaxSearchPro.plugin.updateInfoHeader = function (totalCount) {
	let $this = this,
		content = '',
		$rt = $this.n('resultsDiv').find('.asp_results_top'),
		phrase = $this.n('text').val().trim();

	if ($rt.length > 0) {
		if ($this.n('items').length <= 0 || $this.n('resultsDiv').find('.asp_nores').length > 0) {
			$rt.css('display', 'none');
		} else {
			/**
			 * Results information box original texts, the
			 *  - resInfoBoxTxt
			 *  - resInfoBoxTxtNoPhrase
			 *  variables have to be static, so they are shared across instances. Reason being
			 *  when a custom results position is used, then the same results container is shared
			 *  The second time the
			 *  find('.asp_results_top .asp_rt_phrase') is empty, so it should be shared.
			 */
			if (typeof $this.updateInfoHeader.resInfoBoxTxt == 'undefined') {
				$this.updateInfoHeader.resInfoBoxTxt =
					$this.n('resultsDiv').find('.asp_results_top .asp_rt_phrase').length > 0 ?
						$this.n('resultsDiv').find('.asp_results_top .asp_rt_phrase').html() : '';
				$this.updateInfoHeader.resInfoBoxTxtNoPhrase =
					$this.n('resultsDiv').find('.asp_results_top .asp_rt_nophrase').length > 0 ?
						$this.n('resultsDiv').find('.asp_results_top .asp_rt_nophrase').html() : '';
			}

			if (phrase !== '' && $this.updateInfoHeader.resInfoBoxTxt !== '') {
				content = $this.updateInfoHeader.resInfoBoxTxt;
			} else if (phrase === '' && $this.updateInfoHeader.resInfoBoxTxtNoPhrase !== '') {
				content = $this.updateInfoHeader.resInfoBoxTxtNoPhrase;
			}

			if ( content === undefined ) {
				return;
			}

			if (content !== '') {
				content = content.replaceAll('{phrase}', helpers.escapeHtml($this.n('text').val()));
				content = content.replaceAll('{results_count}', $this.n('items').length);
				content = content.replaceAll('{results_count_total}', totalCount);
				$rt.html(content);
				$rt.css('display', 'block');
			} else {
				$rt.css('display', 'none');
			}
		}
	}
}