import {ASP_Data, ASP_Full} from "./typings";
import Base64 from "./external/helpers/base64";
import AjaxSearchPro from "./plugin/core/base";

export {};

declare global {
	interface Window {
		ajaxurl: string;
		ASP: ASP_Full;
		WPD: {
			Base64: typeof Base64,
			AjaxSearchPro: typeof AjaxSearchPro,
		},
		jQuery: JQueryStatic
	}

	interface JQuery<T=HTMLElement> {
		css<T>(args:T):JQuery;
		spectrum(args?: object):JQuery;
		spectrum(method: string, value: never):JQuery;

		datepicker(args?: object):JQuery;
		datepicker(method: string, value: string):JQuery;

		sortable(args?: object):JQuery;
		draggable(args?: object):JQuery;
		disableSelection();

		select2(args?: object):JQuery;
		select2(method: string, value: never):JQuery;
	}
}