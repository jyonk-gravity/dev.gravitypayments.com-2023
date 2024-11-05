import AjaxSearchPro from "../base.js";
import {default as $} from "domini";

"use strict";

AjaxSearchPro.plugin.loadASPFonts = function () {
	if (ASP.font_url !== false) {
		let font = new FontFace(
			'asppsicons2',
			'url(' + ASP.font_url + ')',
			{style: 'normal', weight: 'normal', display: 'swap'}
		);
		font.load().then(function (loaded_face) {
			document.fonts.add(loaded_face);
		}).catch(function (er) {
		});
		ASP.font_url = false;
	}
};

/**
 * Updates the document address bar with the ajax live search attributes, without push state
 */
AjaxSearchPro.plugin.updateHref = function (anchor) {
	anchor = anchor || window.location.hash;
	if (this.o.trigger.update_href && !this.usingLiveLoader()) {
		if (!window.location.origin) {
			window.location.origin = window.location.protocol + "//" + window.location.hostname + (window.location.port ? ':' + window.location.port : '');
		}
		let url = this.getStateURL() + (this.resultsOpened ? '&asp_s=' : '&asp_ls=') + this.n('text').val() + anchor;
		history.replaceState('', '', url.replace(location.origin, ''));
	}
};

AjaxSearchPro.plugin.stat_addKeyword = function (id, keyword) {
	let data = {
		action: 'ajaxsearchpro_addkeyword',
		id: id,
		keyword: keyword
	};
	// noinspection JSUnresolvedVariable
	$.fn.ajax({
		'url': ASP.ajaxurl,
		'method': 'POST',
		'data': data,
		'success': function (response) {
		}
	})
};
/**
 * Checks if an element with the same ID and Instance was already registered
 */
AjaxSearchPro.plugin.fixClonedSelf = function () {
	let $this = this,
		old_instance_id = String($this.o.iid),
		old_real_id = String($this.o.rid);
	while (!ASP.instances.set($this)) {
		++$this.o.iid;
		if ($this.o.iid > 50) {
			break;
		}
	}
	// oof, this was cloned
	if (old_instance_id !== $this.o.iid) {
		$this.o.rid = $this.o.id + '_' + $this.o.iid;
		$this.n('search').get(0).id = "ajaxsearchpro" + $this.o.rid;
		$this.n('search').removeClass('asp_m_' + old_real_id).addClass('asp_m_' + $this.o.rid).data('instance', $this.o.iid);
		$this.n('searchsettings').get(0).id = $this.n('searchsettings').get(0).id.replace('settings' + old_real_id, 'settings' + $this.o.rid);
		if ($this.n('searchsettings').hasClass('asp_s_' + old_real_id)) {
			$this.n('searchsettings').removeClass('asp_s_' + old_real_id)
				.addClass('asp_s_' + $this.o.rid).data('instance', $this.o.iid);
		} else {
			$this.n('searchsettings').removeClass('asp_sb_' + old_real_id)
				.addClass('asp_sb_' + $this.o.rid).data('instance', $this.o.iid);
		}
		$this.n('resultsDiv').get(0).id = $this.n('resultsDiv').get(0).id.replace('prores' + old_real_id, 'prores' + $this.o.rid);
		$this.n('resultsDiv').removeClass('asp_r_' + old_real_id)
			.addClass('asp_r_' + $this.o.rid).data('instance', $this.o.iid);
		$this.n('container').find('.asp_init_data').data('instance', $this.o.iid);
		$this.n('container').find('.asp_init_data').get(0).id =
			$this.n('container').find('.asp_init_data').get(0).id.replace('asp_init_id_' + old_real_id, 'asp_init_id_' + $this.o.rid);

		$this.n('prosettings').data('opened', 0);
	}
};
AjaxSearchPro.plugin.destroy = function () {
	let $this = this;
	Object.keys($this.nodes).forEach(function (k) {
		$this.nodes[k].off?.();
	});
	if (typeof $this.n('searchsettings').get(0).referenced !== 'undefined') {
		--$this.n('searchsettings').get(0).referenced;
		if ($this.n('searchsettings').get(0).referenced < 0) {
			$this.n('searchsettings').remove();
		}
	} else {
		$this.n('searchsettings').remove();
	}
	if (typeof $this.n('resultsDiv').get(0).referenced !== 'undefined') {
		--$this.n('resultsDiv').get(0).referenced;
		if ($this.n('resultsDiv').get(0).referenced < 0) {
			$this.n('resultsDiv').remove?.();
		}
	} else {
		$this.n('resultsDiv').remove?.();
	}
	$this.n('trythis').remove?.();
	$this.n('search').remove?.();
	$this.n('container').remove?.();
	$this.documentEventHandlers.forEach(function (h) {
		$(h.node).off(h.event, h.handler);
	});
};

export default AjaxSearchPro;