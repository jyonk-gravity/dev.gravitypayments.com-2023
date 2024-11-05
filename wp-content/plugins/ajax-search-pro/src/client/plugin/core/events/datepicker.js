import AjaxSearchPro from "../base.js";
import {default as $} from "domini";
import intervalUntilExecute from "../../../external/helpers/interval-until-execute.js";

"use strict";
let helpers = AjaxSearchPro.helpers;

AjaxSearchPro.plugin.initDatePicker = function () {
	let $this = this;
	intervalUntilExecute(function (_$) {
		function onSelectEvent(dateText, inst, _this, nochange, nochage) {
			let obj;
			if (_this != null) {
				obj = _$(_this);
			} else {
				obj = _$("#" + inst.id);
			}

			let prevValue = _$(".asp_datepicker_hidden", _$(obj).parent()).val(),
				newValue = '';

			if (obj.datepicker("getDate") == null) {
				_$(".asp_datepicker_hidden", _$(obj).parent()).val('');
			} else {
				// noinspection RegExpRedundantEscape
				let d = String(obj.datepicker("getDate")),
					date = new Date(d.match(/(.*?)00:/)[1].trim()),
					year = String(date.getFullYear()),
					month = ("0" + (date.getMonth() + 1)).slice(-2),
					day = ("0" + String(date.getDate())).slice(-2);
				newValue = year + '-' + month + '-' + day;
				_$(".asp_datepicker_hidden", _$(obj).parent()).val(newValue);
			}

			// Trigger change event. $ scope is used ON PURPOSE
			// ..otherwise scoped version would not trigger!
			if ((typeof nochage == "undefined" || nochange == null) && newValue !== prevValue)
				$(obj.get(0)).trigger('change');
		}

		_$(".asp_datepicker, .asp_datepicker_field", $this.n('searchsettings').get(0)).each(function () {
			let format = _$(".asp_datepicker_format", _$(this).parent()).val(),
				_this = this,
				origValue = _$(this).val();
			_$(this).removeClass('hasDatepicker'); // Cloned versions can already have the date picker class
			_$(this).datepicker({
				changeMonth: true,
				changeYear: true,
				dateFormat: 'yy-mm-dd',
				onSelect: onSelectEvent,
				beforeShow: function () {
					_$('#ui-datepicker-div').addClass("asp-ui");
				}
			});
			// Set to empty date if the field is empty
			if (origValue === "") {
				_$(this).datepicker("setDate", "");
			} else {
				_$(this).datepicker("setDate", origValue);
			}
			_$(this).datepicker("option", "dateFormat", format);
			// Call the select event to refresh the date pick value
			onSelectEvent(null, null, _this, true);

			// Assign the no change select event to a new triggerable event
			_$(this).on('selectnochange', function () {
				onSelectEvent(null, null, _this, true);
			});

			// When the user deletes the value, empty the hidden field as well
			_$(this).on('keyup', function () {
				if (_$(_this).datepicker("getDate") == null) {
					_$(".asp_datepicker_hidden", _$(_this).parent()).val('');
				}
				_$(_this).datepicker("hide");
			});
		});
		// IOS Safari backwards button reinit
		if (helpers.isMobile() && helpers.detectIOS()) {
			_$(window).on('pageshow', function (e) {
				if (e.originalEvent.persisted) {
					setTimeout(function () {
						_$(".asp_datepicker, .asp_datepicker_field", $this.n('searchsettings').get(0)).each(function () {
							let format = _$(this).datepicker("option", 'dateFormat');
							_$(this).datepicker("option", 'dateFormat', 'yy-mm-dd');
							_$(this).datepicker("setDate", _$(this).next('.asp_datepicker_hidden').val());
							_$(this).datepicker("option", 'dateFormat', format);
						});
					}, 100);
				}
			});
		}
	}, function () {
		return helpers.whichjQuery('datepicker');
	});
}

export default AjaxSearchPro;