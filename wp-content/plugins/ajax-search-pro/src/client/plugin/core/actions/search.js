import AjaxSearchPro from "../base.js";
import {default as $} from "domini";

"use strict";
let helpers = AjaxSearchPro.helpers;
AjaxSearchPro.plugin.isDuplicateSearchTriggered = function () {
	let $this = this;
	for (let i = 0; i < 25; i++) {
		let id = $this.o.id + '_' + i;
		if (id !== $this.o.rid) {
			if (window.ASP.instances.get($this.o.id, i) !== false) {
				return window.ASP.instances.get($this.o.id, i).searching;
			}
		}
	}
	return false;
}

AjaxSearchPro.plugin.searchAbort = function () {
	let $this = this;
	if ($this.post != null) {
		$this.post.abort();
		$this.isAutoP = false;
	}
}

AjaxSearchPro.plugin.searchWithCheck = function (timeout) {
	let $this = this;
	if (typeof timeout == 'undefined')
		timeout = 50;
	if ($this.n('text').val().length < $this.o.charcount) return;
	$this.searchAbort();

	clearTimeout($this.timeouts.searchWithCheck);
	$this.timeouts.searchWithCheck = setTimeout(function () {
		$this.search();
	}, timeout);
}

AjaxSearchPro.plugin.search = function (count, order, recall, apiCall, supressInvalidMsg) {
	let $this = this,
		abort = false;
	if ($this.isDuplicateSearchTriggered())
		return false;

	recall = typeof recall == "undefined" ? false : recall;
	apiCall = typeof apiCall == "undefined" ? false : apiCall;
	supressInvalidMsg = typeof supressInvalidMsg == "undefined" ? false : supressInvalidMsg;

	let data = {
		action: 'ajaxsearchpro_search',
		aspp: $this.n('text').val(),
		asid: $this.o.id,
		asp_inst_id: $this.o.rid,
		options: $('form', $this.n('searchsettings')).serialize()
	};

	data = helpers.Hooks.applyFilters('asp_search_data', data, $this.o.id, $this.o.iid);

	$this.hideArrowBox?.();
	if (typeof $this.reportSettingsValidity != 'undefined' && !$this.isAutoP && !$this.reportSettingsValidity()) {
		if (!supressInvalidMsg) {
			$this.showNextInvalidFacetMessage?.();
			$this.scrollToNextInvalidFacetMessage?.();
		}
		abort = true;
	}

	if ($this.isAutoP) {
		data.autop = 1;
	}

	if (!recall && !apiCall && (JSON.stringify(data) === JSON.stringify($this.lastSearchData))) {
		if (!$this.resultsOpened && !$this.usingLiveLoader()) {
			$this.showResults();
		}
		if ($this.isRedirectToFirstResult()) {
			$this.doRedirectToFirstResult();
			return false;
		}
		abort = true;
	}

	if (abort) {
		$this.hideLoader();
		$this.searchAbort();
		return false;
	}

	$this.n('s').trigger("asp_search_start", [$this.o.id, $this.o.iid, $this.n('text').val()], true, true);

	$this.searching = true;

	$this.n('proclose').css({
		display: "none"
	});

	$this.showLoader(recall);

	// If blocking, or hover but facetChange activated, dont hide the settings for better UI
	if (!$this.att('blocking') && !$this.o.trigger.facet) $this.hideSettings?.();

	if (recall) {
		$this.call_num++;
		data.asp_call_num = $this.call_num;
		/**
		 * The original search started with an auto populate, so set the call number correctly
		 */
		if ($this.autopStartedTheSearch) {
			data.options += '&' + $.fn.serializeObject($this.autopData);
			--data.asp_call_num;
		}
	} else {
		$this.call_num = 0;
		/**
		 * Mark the non search phrase type of auto populate.
		 * In that case, we need to pass the post IDs to exclude, as well as the next
		 * "load more" query has to act as the first call (call_num=0)
		 */
		$this.autopStartedTheSearch = !!data.autop;
	}

	let $form = $('form[name="asp_data"]');
	if ($form.length > 0) {
		data.asp_preview_options = $form.serialize();
	}

	if (typeof count != "undefined" && count !== false) {
		data.options += "&force_count=" + parseInt(count);
	}
	if (typeof order != "undefined" && order !== false) {
		data.options += "&force_order=" + parseInt(order);
	}

	$this.gaEvent?.('search_start');

	if ($('.asp_es_' + $this.o.id).length > 0) {
		$this.liveLoad('.asp_es_' + $this.o.id, $this.getCurrentLiveURL(), $this.o.trigger.update_href);
	} else if ($this.o.resPage.useAjax) {
		$this.liveLoad($this.o.resPage.selector, $this.getRedirectURL());
	} else if ($this.o.wooShop.useAjax) {
		$this.liveLoad($this.o.wooShop.selector, $this.getLiveURLbyBaseLocation($this.o.wooShop.url));
	} else if ($this.o.taxArchive.useAjax) {
		$this.liveLoad($this.o.taxArchive.selector, $this.getLiveURLbyBaseLocation($this.o.taxArchive.url));
	} else if ($this.o.cptArchive.useAjax) {
		$this.liveLoad($this.o.cptArchive.selector, $this.getLiveURLbyBaseLocation($this.o.cptArchive.url));
	} else {
		$this.post = $.fn.ajax({
			'url': window.ASP.ajaxurl,
			'method': 'POST',
			'data': data,
			'success': function (response) {
				$this.searching = false;
				response = response.replace(/^\s*[\r\n]/gm, "");
				let html_response = response.match(/___ASPSTART_HTML___(.*[\s\S]*)___ASPEND_HTML___/),
					data_response = response.match(/___ASPSTART_DATA___(.*[\s\S]*)___ASPEND_DATA___/);

				if (html_response == null || typeof (html_response) != "object" || typeof (html_response[1]) == "undefined") {
					$this.hideLoader();
					alert('Ajax Search Pro Error:\r\n\r\nPlease look up "The response data is missing" from the documentation at\r\n\r\n documentation.ajaxsearchpro.com');
					return false;
				} else {
					html_response = html_response[1];
					html_response = helpers.Hooks.applyFilters('asp_search_html', html_response, $this.o.id, $this.o.iid);
				}
				data_response = JSON.parse(data_response[1]);
				$this.n('s').trigger("asp_search_end", [$this.o.id, $this.o.iid, $this.n('text').val(), data_response], true, true);

				if ($this.autopStartedTheSearch) {
					// This is an auto populate query (first on page load only)
					if (typeof data.autop != 'undefined') {
						$this.autopData['not_in'] = {};
						$this.autopData['not_in_count'] = 0;
						if (typeof data_response.results != 'undefined') {
							let res = [];
							if (typeof data_response.results.groups != 'undefined') {
								Object.keys(data_response.results.groups).forEach(function (k) {
									if (typeof data_response.results.groups[k].items != 'undefined') {
										let group = data_response.results.groups[k].items;
										if (Array.isArray(group)) {
											group.forEach(function (result) {
												res.push(result);
											})
										}
									}
								});
							} else {
								res = Array.isArray(data_response.results) ? data_response.results : res;
							}
							res.forEach(function (r) {
								if (typeof $this.autopData['not_in'][r['content_type']] == 'undefined') {
									$this.autopData['not_in'][r['content_type']] = [];
								}
								$this.autopData['not_in'][r['content_type']].push(r['id']);
								++$this.autopData['not_in_count'];
							});
						}
					} else {
						// In subsequent queries adjust, because this is goint to be deducted in the query
						data_response.full_results_count += $this.autopData['not_in_count'];
					}
				}

				if (!recall) {
					$this.initResults();
					$this.n('resdrg').html("");
					$this.n('resdrg').html(html_response);
					$this.results_num = data_response.results_count;
					if ($this.o.statistics)
						$this.stat_addKeyword($this.o.id, $this.n('text').val());
				} else {
					$this.updateResults(html_response);
					$this.results_num += data_response.results_count;
				}

				$this.updateNoResultsHeader();

				$this.nodes.items = $('.item', $this.n('resultsDiv')).length > 0 ? $('.item', $this.n('resultsDiv')) : $('.photostack-flip', $this.n('resultsDiv'));

				$this.addHighlightString();

				$this.gaEvent?.('search_end', {'results_count': $this.n('items').length});

				if ($this.isRedirectToFirstResult()) {
					$this.doRedirectToFirstResult();
					return false;
				}
				$this.hideLoader();
				$this.showResults();
				if (
					window.location.hash !== '' &&
					window.location.hash.indexOf('#asp-res-') > -1 &&
					$(window.location.hash).length > 0
				) {
					$this.scrollToResult(window.location.hash);
				} else {
					$this.scrollToResults();
				}

				$this.lastSuccesfulSearch = $('form', $this.n('searchsettings')).serialize() + $this.n('text').val().trim();
				$this.lastSearchData = data;

				$this.updateInfoHeader(data_response.full_results_count);

				$this.updateHref();

				if ($this.n('showmore').length > 0) {
					if (
						$('span', $this.n('showmore')).length > 0 &&
						data_response.results_count > 0 &&
						(data_response.full_results_count - $this.results_num) > 0
					) {
						if ($this.n('showmore').data('text') === '') {
							$this.n('showmore').data('text', $this.n('showmore').html());
						}
						$this.n('showmore').html($this.n('showmore').data('text').replaceAll('{phrase}', helpers.escapeHtml($this.n('text').val())));
						$this.n('showmoreContainer').css("display", "block");
						$this.n('showmore').css("display", "block");
						$('span', $this.n('showmore')).html("(" + (data_response.full_results_count - $this.results_num) + ")");

						let $a = $('a', $this.n('showmore'));
						$a.attr('href', "");
						$a.off();
						$a.on($this.clickTouchend, function (e) {
							e.preventDefault();
							e.stopImmediatePropagation();   // Stopping either click or touchend

							if ($this.o.show_more.action === "ajax") {
								// Prevent duplicate triggering, don't use .off, as re-opening the results box this will fail
								if ($this.searching)
									return false;
								$this.showMoreResLoader();
								$this.search(false, false, true);
							} else {
								let url, base_url;
								// Prevent duplicate triggering
								$(this).off();
								if ($this.o.show_more.action === 'results_page') {
									url = '?s=' + helpers.nicePhrase($this.n('text').val());
								} else if ($this.o.show_more.action === 'woo_results_page') {
									url = '?post_type=product&s=' + helpers.nicePhrase($this.n('text').val());
								} else {
									if ($this.o.show_more.action === 'elementor_page') {
										url = $this.parseCustomRedirectURL($this.o.show_more.elementor_url, $this.n('text').val());
									} else {
										url = $this.parseCustomRedirectURL($this.o.show_more.url, $this.n('text').val());
									}
									url = $('<textarea />').html(url).text();
								}

								// Is this an URL like xy.com/?x=y
								if ($this.o.show_more.action !== 'elementor_page' && $this.o.homeurl.indexOf('?') > 1 && url.indexOf('?') === 0) {
									url = url.replace('?', '&');
								}

								base_url = $this.o.show_more.action === 'elementor_page' ? url : $this.o.homeurl + url;
								if ($this.o.overridewpdefault) {
									if ($this.o.override_method === "post") {
										helpers.submitToUrl(base_url, 'post', {
											asp_active: 1,
											p_asid: $this.o.id,
											p_asp_data: $('form', $this.n('searchsettings')).serialize()
										}, $this.o.show_more.location);
									} else {
										let final = base_url + "&asp_active=1&p_asid=" + $this.o.id + "&p_asp_data=1&" + $('form', $this.n('searchsettings')).serialize();
										if ($this.o.show_more.location === 'same') {
											location.href = final;
										} else {
											helpers.openInNewTab(final);
										}
									}
								} else {
									// The method is not important, just send the data to memorize settings
									helpers.submitToUrl(base_url, 'post', {
										np_asid: $this.o.id,
										np_asp_data: $('form', $this.n('searchsettings')).serialize()
									}, $this.o.show_more.location);
								}
							}
						});
					} else {
						$this.n('showmoreContainer').css("display", "none");
						$('span', $this.n('showmore')).html("");
					}
				}
				$this.isAutoP = false;
			},
			'fail': function (jqXHR) {
				if (jqXHR.aborted)
					return;
				$this.n('resdrg').html("");
				$this.n('resdrg').html('<div class="asp_nores">The request failed. Please check your connection! Status: ' + jqXHR.status + '</div>');
				$this.nodes.item = $('.item', $this.n('resultsDiv')).length > 0 ? $('.item', $this.n('resultsDiv')) : $('.photostack-flip', $this.n('resultsDiv'));
				$this.results_num = 0;
				$this.searching = false;
				$this.hideLoader();
				$this.showResults();
				$this.scrollToResults();
				$this.isAutoP = false;
			}
		});
	}
}
export default AjaxSearchPro;