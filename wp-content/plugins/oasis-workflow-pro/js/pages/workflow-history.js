jQuery(document).ready(function () {
    jQuery(document).on('click', '#owf-delete-history', function () {
        jQuery('#delete-history-div').owfmodal();
    });

    jQuery(document).on('click', '#deleteHistoryConfirm', function () {
        data = {
            action: 'purge_workflow_history',
            range: jQuery('#delete-history-range-select').val(),
            security: jQuery('#owf_workflow_history_nonce').val()
        };

        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: data,
            success: function (response) {
                jQuery.modal.close();
                window.location = 'admin.php?page=oasiswf-history&trashed=' + response.data.result;
            }
        });
    });

    jQuery(document).on('click', '#deleteHistoryCancel', function () {
        jQuery.modal.close();
    });
    
    jQuery(document).on('click', '#owf_history_filter', function (e) {
        e.preventDefault();

        let current = jQuery(this),
            href = current.attr('href'),
            post_id = jQuery('#post_filter').val();

            console.log("post_id", post_id);

            // replace post parameter value with post_id from href
            href = href.replace(/post=[^&]+/, 'post=' + post_id);

        window.location.href = href;
    });

});