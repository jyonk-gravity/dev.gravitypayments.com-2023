// @ts-expect-error domini types not available :(
// noinspection TypeScriptCheckImport
import DoMini from "domini";
import ASP_EXTENDED from "./asp.ts";
import intervalUntilExecute from "../../external/helpers/interval-until-execute.js";

export default function load() {

	if ( typeof window.WPD.AjaxSearchPro != 'undefined' ) {
		DoMini._fn.plugin('ajaxsearchpro', window.WPD.AjaxSearchPro.plugin);
	}

	// ASP is already defined within the window scope, extend it with the new functions
	window.ASP = {...window.ASP, ...ASP_EXTENDED}

	// Call within arrow function directly, otherwise "this" is propagated to window
	intervalUntilExecute(()=>window.ASP.init(), function () {
		return typeof window.ASP.version != 'undefined';
	});
}