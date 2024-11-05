import instances from "./instances.js";
import {ASP_Full, SearchInstance} from "../../types/typings";

/**
 * Executes an API function on the search instance
 *
 * @param id search ID
 * @param instance search instance
 * @param func function to execute
 * @param args function arguments
 */
export function api(id: number, instance: number, func: string, args: unknown): unknown
export function api(id: number, instance: number, func: string): unknown;
export function api(id: number, func: string, args: unknown): unknown;
export function api(id: number, func: string): unknown;

export function api(this: ASP_Full): unknown {
    "use strict";

    const a4 = function(id: number, instance: number, func: string, args: unknown) {
        let s = instances.get(id, instance);
        return s !== false && s[func].apply(s, [args]);
    },
    a3 = function(id: number, func: string|number, args: string|unknown) {
        let s;
        if ( typeof func === 'number' && isFinite(func) ) {
            s = instances.get(id, func);
            return s !== false && s[args].apply(s);
        } else if ( typeof func === 'string' ) {
            s = instances.get(id);
            return s !== false && s.forEach(function(i: SearchInstance){
                const f = i[func as keyof SearchInstance];
                if ( typeof f === 'function' ) {
                    f.apply(i, [args]);
                }
            });
        }
    },
    a2 = function(id: number, func: string) {
        let s;
        if ( func === 'exists' ) {
            return instances.exist(id);
        }
        s = instances.get(id);
        return s !== false && s.forEach(function(i: SearchInstance){
            const f = i[func as keyof SearchInstance];
            if ( typeof f === 'function' ) {
                f.apply(i);
            }
        });
    };

    if ( arguments.length === 4 ){
        return(
            a4.apply( this, arguments as unknown as [number, number, string, unknown] )
        );
    } else if ( arguments.length === 3 ) {
        return(
            a3.apply( this, arguments as unknown as [number, string|number, string|unknown] )
        );
    } else if ( arguments.length === 2 ) {
        return(
            a2.apply( this, arguments as unknown as [number, string] )
        );
    } else if ( arguments.length === 0 ) {
        console.log("Usage: ASP.api(id, [optional]instance, function, [optional]args);");
        console.log("For more info: https://knowledgebase.ajaxsearchpro.com/other/javascript-api");
    }
}