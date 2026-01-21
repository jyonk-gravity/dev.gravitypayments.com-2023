jQuery(document).ready(function () {
    /**
     * Add external users
     * @since 7.2
     */
    jQuery(document).on('click', '.add-external-users', function (event) {
        event.preventDefault();
        jQuery('div.no-external-user').hide();

        // step 1. get the parent of button ie <div>
        var parent = jQuery(this).parent().parent();

        // <div>'s first child always be hidden so clone it.
        var copy_wrapper = parent.children(':first')
            .clone()
            .removeClass('owf-hidden')
            .addClass('owf-external-users')
            .fadeIn('slow', function () {
                jQuery(this).delay(800);
            });

        // now append the data before the add button
        jQuery(copy_wrapper).insertBefore(parent.children(':last'));
    });

    /**
     * Remove external users
     * @since 7.2
     */
    jQuery(document).on('click', '.remove-external-users', function (event) {
        event.preventDefault();

        // get parent of clicked icon
        var parent = jQuery(this).parent();

        parent.addClass('remove-external-users').fadeOut(1000, function () {
            jQuery(this).remove();
        });
    });

});