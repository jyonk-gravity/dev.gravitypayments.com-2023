import {default as $} from "domini";
"use strict";
$(function(){
    // Top and latest searches widget
    $(".ajaxsearchprotop").forEach(function () {
        let params = JSON.parse( $(this).data("aspdata") ),
            id = params.id;

        if (params.action === 0) {
            $('a', $(this)).on('click', function (e) {
                e.preventDefault();
            });
        } else if (params.action === 2) {
            $('a', $(this)).on('click', function (e) {
                e.preventDefault();
                window.ASP.api(id, 'searchFor', $(this).html());
                $('html').animate({
                    scrollTop: $('div[id*=ajaxsearchpro' + id + '_]').first().offset().top - 40
                }, 500);
            });
        } else if (params.action === 1) {
            $('a', $(this)).on('click', function (e) {
                if ( window.ASP.api(id, 'exists') ) {
                    e.preventDefault();
                    return window.ASP.api(id, 'searchRedirect', $(this).html());
                }
            });
        }
    });
});