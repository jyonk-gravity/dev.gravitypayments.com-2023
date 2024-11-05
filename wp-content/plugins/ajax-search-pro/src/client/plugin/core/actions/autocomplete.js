import AjaxSearchPro from "../base.js";
import {default as $} from "domini";

"use strict";

/**
 * Checks if the autocomplete should be triggered
 *
 * @param val {string}
 * @returns {boolean}
 */
AjaxSearchPro.plugin.autocompleteCheck = function( val = '' ) {
    if (this.n('text').val() === '') {
        this.n('textAutocomplete').val('');
        return false;
    }
    let autocompleteVal = this.n('textAutocomplete').val();
    return !(autocompleteVal !== '' && autocompleteVal.indexOf(val) === 0);
}

AjaxSearchPro.plugin.autocomplete = function () {
	let $this = this,
		val = $this.n('text').val();

    if ( !$this.autocompleteCheck(val) ) {
        return;
    }

	// noinspection JSUnresolvedVariable
	if ($this.n('text').val().length >= $this.o.autocomplete.trigger_charcount) {
		let data = {
			action: 'ajaxsearchpro_autocomplete',
			asid: $this.o.id,
			sauto: $this.n('text').val(),
			asp_inst_id: $this.o.rid,
			options: $('form', $this.n('searchsettings')).serialize()
		};
		// noinspection JSUnresolvedVariable
		$this.postAuto = $.fn.ajax({
			'url': ASP.ajaxurl,
			'method': 'POST',
			'data': data,
			'success': function (response) {
				if (response.length > 0) {
					response = $('<textarea />').html(response).text();
					response = response.replace(/^\s*[\r\n]/gm, "");
					response = val + response.substring(val.length);
				}
				$this.n('textAutocomplete').val(response);
				$this.fixAutocompleteScrollLeft();
			}
		});
	}
}

// If only google source is used, this is much faster
AjaxSearchPro.plugin.autocompleteGoogleOnly = function () {
	let $this = this,
		val = $this.n('text').val();

    if ( !$this.autocompleteCheck(val) ) {
        return;
    }

	let lang = $this.o.autocomplete.lang;
	['wpml_lang', 'polylang_lang', 'qtranslate_lang'].forEach(function (v) {
		if (
			$('input[name="' + v + '"]', $this.n('searchsettings')).length > 0 &&
			$('input[name="' + v + '"]', $this.n('searchsettings')).val().length > 1
		) {
			lang = $('input[name="' + v + '"]', $this.n('searchsettings')).val();
		}
	});
	// noinspection JSUnresolvedVariable
	if ($this.n('text').val().length >= $this.o.autocomplete.trigger_charcount) {
		$.fn.ajax({
			url: 'https://clients1.google.com/complete/search',
			cors: 'no-cors',
			data: {
				q: val,
				hl: lang,
				nolabels: 't',
				client: 'hp',
				ds: ''
			},
			success: function (data) {
				if (data[1].length > 0) {
					let response = data[1][0][0].replace(/(<([^>]+)>)/ig, "");
					response = $('<textarea />').html(response).text();
					response = response.substring(val.length);
					$this.n('textAutocomplete').val(val + response);
					$this.fixAutocompleteScrollLeft();
				}
			}
		});
	}
}

AjaxSearchPro.plugin.fixAutocompleteScrollLeft = function () {
	this.n('textAutocomplete').get(0).scrollLeft = this.n('text').get(0).scrollLeft;
}

export default AjaxSearchPro;