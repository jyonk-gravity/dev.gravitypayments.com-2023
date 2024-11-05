import AjaxSearchPro from "../base.js";
import {default as $} from "domini";

"use strict";

AjaxSearchPro.plugin.addAnimation = function () {
    let $this = this,
        i = 0,
        j = 1,
        delay = 25,
        checkViewport = true;

    // No animation for the new elements via more results link
    if ( $this.call_num > 0 || $this._no_animations ) {
        $this.n('results').find('.item, .asp_group_header').removeClass("opacityZero").removeClass("asp_an_" + $this.animOptions.items);
        return false;
    }

    $this.n('results').find('.item, .asp_group_header').forEach(function () {
        let x = this;
        // The first item must be in the viewport, if not, then we won't use this at all
        if ( j === 1) {
            checkViewport = $(x).inViewPort(0);
        }

        // No need to animate everything
        if (
            ( j > 1 && checkViewport && !$(x).inViewPort(0) ) ||
            j > 80
        ) {
            $(x).removeClass("opacityZero");
            return true;
        }

        // noinspection JSUnresolvedVariable
        if ($this.o.resultstype === 'isotopic' && j>$this.il.itemsPerPage) {
            // Remove this from the ones not affected by the animation
            $(x).removeClass("opacityZero");
            return;
        }

        setTimeout(function () {
            $(x).addClass("asp_an_" + $this.animOptions.items);
            /**
             * The opacityZero class must be removed just a little bit after
             * the animation starts. This way the opacity is not reset to 1 yet,
             * and not causing flashing effect on the results.
             *
             * If the opacityZero is not removed, the after the removeAnimation()
             * call the opacity flashes back to 0 - window rezise or pagination events
             */
            $(x).removeClass("opacityZero");
        }, (i + delay));
        i = i + 45;
        j++;
    });
}

AjaxSearchPro.plugin.removeAnimation = function () {
    let $this = this;
    this.n('items').forEach(function () {
        $(this).removeClass("asp_an_" + $this.animOptions.items);
    });
}

export default AjaxSearchPro;