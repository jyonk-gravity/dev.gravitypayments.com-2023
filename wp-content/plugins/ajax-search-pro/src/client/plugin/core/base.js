const AjaxSearchPro = new function (){
    this.helpers = {};
    this.plugin = {};
    this.addons = {
        addons: [],
        add: function(addon) {
            if ( this.addons.indexOf(addon) === -1 ) {
                let k = this.addons.push(addon);
                this.addons[k-1].init();
            }
        },
        remove: function(name) {
            this.addons.filter(function(addon){
                if ( addon.name === name ) {
                    if ( typeof addon.destroy != 'undefined' ) {
                        addon.destroy();
                    }
                    return false;
                } else {
                    return true;
                }
            });
        }
    }
};
export default AjaxSearchPro;