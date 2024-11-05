import AjaxSearchPro from "../plugin/core/base.js";
import {default as $} from "domini";

const helpers = AjaxSearchPro.helpers;
class DiviAddon {
    name = "Divi Widget Fixes";
    init(){
        helpers.Hooks.addFilter('asp/init/etc', this.diviBodyCommerceResultsPage, 10, this);
    }
    diviBodyCommerceResultsPage( $this ) {
        if ( $this.o.divi.bodycommerce && $this.o.is_results_page ) {
            window.WPD.intervalUntilExecute(function($){
                setTimeout(function(){
                    $('#divi_filter_button').trigger('click');
                }, 50);
            }, function() {
                return typeof jQuery !== "undefined" ? jQuery : false;
            });
        }

        // Need to return the first argument, as this is a FILTER hook with OBJECT reference argument, and will override with the return value
        return $this;
    }
}
AjaxSearchPro.addons.add(new DiviAddon());
export default AjaxSearchPro;