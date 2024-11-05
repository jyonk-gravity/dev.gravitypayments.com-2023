const Hooks = {
    filters: {},
    /**
     * Adds a callback function to a specific programmatically triggered tag (hook)
     *
     * @param tag - the hook name
     * @param callback - the callback function variable name
     * @param priority - (optional) default=10
     * @param scope - (optional) function scope. When a function is executed within an object scope, the object variable should be passed.
     */
    addFilter: function( tag, callback, priority, scope ) {
        priority = typeof priority === "undefined" ? 10 : priority;
        scope = typeof scope === "undefined" ? null : scope;
        Hooks.filters[ tag ] = Hooks.filters[ tag ] || [];
        Hooks.filters[ tag ].push( { priority: priority, scope: scope, callback: callback } );
    },

    /**
     * Removes a callback function from a hook
     *
     * @param tag - the hook name
     * @param callback - the callback function variable
     */
    removeFilter: function( tag, callback ) {
        if ( typeof Hooks.filters[ tag ] != 'undefined' ) {
            if ( typeof callback == "undefined" ) {
                Hooks.filters[tag] = [];
            } else {
                Hooks.filters[tag].forEach(function (filter, i) {
                    if (filter.callback === callback) {
                        Hooks.filters[tag].splice(i, 1);
                    }
                });
            }
        }
    },
    applyFilters: function( tag ) {
        let filters = [],
            args = Array.prototype.slice.call(arguments),
            value = arguments[1];
        if( typeof Hooks.filters[ tag ] !== "undefined" && Hooks.filters[ tag ].length > 0 ) {
            Hooks.filters[ tag ].forEach( function( hook ) {
                filters[ hook.priority ] = filters[ hook.priority ] || [];
                filters[ hook.priority ].push( {
                    scope: hook.scope,
                    callback: hook.callback
                } );
            } );
            args.splice(0, 2);
            filters.forEach( function( hooks ) {
                hooks.forEach( function( obj ) {
                    /**
                     * WARNING!
                     * If, this function is called with a referanced parameter like OBJECT or ARRAY argument
                     * as the first argument - then the callback function MUST return that value, otherwise
                     * it is overwritten with NULL!
                     * Ex.:
                     * Hooks.applyFilters('my_filter', object);
                     * Hooks.addFilter('my_filter', function(obj){
                     *     do things..
                     *     return obj; <--- IMPORTANT IN EVERY CASE
                     * });
                     */
                    value = obj.callback.apply( obj.scope, [value].concat(args) );
                } );
            } );
        }
        return value;
    }
};

export default Hooks;