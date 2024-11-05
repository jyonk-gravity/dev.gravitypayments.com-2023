import AjaxSearchPro from "../base.js";
import {default as $} from "domini";

"use strict";
AjaxSearchPro.plugin.monitorTouchMove = function () {
	let $this = this;
	$this.dragging = false;
	$("body").on("touchmove", function () {
		$this.dragging = true;
	}).on("touchstart", function () {
		$this.dragging = false;
	});
}
export default AjaxSearchPro;