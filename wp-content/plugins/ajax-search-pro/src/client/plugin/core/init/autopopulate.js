import AjaxSearchPro from "../base.js";

"use strict";
AjaxSearchPro.plugin.initAutop = function () {
	let $this = this;
	if ($this.o.autop.state === "disabled") return false;

	let location = window.location.href;
	// Correct previous query arguments (in case of paginated results)
	let stop = location.indexOf('asp_ls=') > -1 || location.indexOf('asp_ls&') > -1;
	if (stop) {
		return false;
	}
	// noinspection JSUnresolvedVariable
	let count = $this.o.show_more.enabled && $this.o.show_more.action === 'ajax' ? false : $this.o.autop.count;
	$this.isAutoP = true;
	if ($this.o.compact.enabled) {
		$this.openCompact();
	}
	if ($this.o.autop.state === "phrase") {
		if (!$this.o.is_results_page) {
			$this.n('text').val($this.o.autop.phrase);
		}
		$this.search(count);
	} else if ($this.o.autop.state === "latest") {
		$this.search(count, 1);
	} else {
		$this.search(count, 2);
	}
}
export default AjaxSearchPro;