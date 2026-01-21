jQuery(document).ready(function () {
    var select_id = jQuery('#reassign_actors').val();

    modal_close = function () {
        jQuery(document).find('#reassign-div').html('');
        jQuery.modal.close();
    };
    jQuery(document).on('click', '#reassignCancel, .modalCloseImg', function () {
        modal_close();
    });

    jQuery(document).on('click', '#reassignSave', function () {
        if (0 === jQuery('#reassign-actors-set-select option').length) {
            alert(owf_reassign_task_vars.selectUser);
            return false;
        }
        if (!jQuery('#reassign_comments').val() && owf_reassign_task_vars.isCommentsMandotory === 'mandatory') {
            alert(owf_reassign_task_vars.emptyComments);
            return false;
        }

        var actors = [];
        jQuery('#reassign-actors-set-select option').each(function () {
            actors.push(jQuery(this).val());
        });

        var obj = this;
        jQuery(this).parent().children('span').addClass('loading');
        jQuery(this).hide();

        var task_user = jQuery('#task_user_inbox').val();
        // If we are on post edit page
        if (jQuery('#hi_task_user').length !== 0) {
            task_user = jQuery('#hi_task_user').val();
        }

        data = {
            action: 'reassign_process',
            oasiswf: jQuery('#action_history_id').val(),
            reassign_id: actors,
            reassign_comments: jQuery('#reassign_comments').val(),
            task_user: task_user,
            security: jQuery('#owf_reassign_ajax_nonce').val()
        };
        jQuery.post(ajaxurl, data, function (response) {
            if (!response.success) {
                displayWorkflowReassignErrorMessages(response.data.errorMessage);

                jQuery(obj).parent().children('span').removeClass('loading');
                jQuery('#reassignSave').show();
                return false;
            } else {

                if( jQuery('body').hasClass('elementor-editor-active') ) {
                    jQuery.modal.close();
                    //If elementor editor active
                    //Hide signoff button
                    jQuery('#elementor-panel-footer-sub-menu-item-reassign-workflow').hide();
                    swal(owf_submit_step_vars.elementorReassignText, {
                        button: {
                            text: owf_submit_step_vars.elementorExitButtonText,
                            className: 'owf-elementor-redirect'
                        },
                    }).then(function () {
                        // Redirect the user to inbox page
                        window.location.href = 'admin.php?page=oasiswf-inbox';
                    });
                    if( jQuery('button.save_order').length !== 0 ) {
                        jQuery('button.save_order').trigger('click');
                    }
                    if( jQuery('#save-post').length !== 0 ) {
                        jQuery('#save-post').click();
                    }
                } else {
                    modal_close();
                    location.reload();
                }

            }
        });
    });

    // assign users to reassign
    jQuery(document).on('click', '#reassign-assignee-set-point', function () {
        jQuery('#reassign-actors-list-select option:selected').each(function () {
            var v = jQuery(this).val();
            var t = jQuery(this).text();
            addRemoveOptions('reassign-actors-list-select', 'reassign-actors-set-select', v, t);
        });
        return false;
    });

    //unassign users from the reassign list
    jQuery(document).on('click', '#reassign-assignee-unset-point', function () {
        jQuery('#reassign-actors-set-select option:selected').each(function () {
            var v = jQuery(this).val();
            var t = jQuery(this).text();
            addRemoveOptions('reassign-actors-set-select', 'reassign-actors-list-select', v, t);
        });
    });

    function addRemoveOptions (removeSelector, appendSelector, val, text) {
        if (typeof val !== 'undefined') {
            jQuery('#' + removeSelector + ' option[value=\'' + val + '\']').remove();
            jQuery('#' + appendSelector).append('<option value=' + val + '>' + text + '</option>');
        }
    }

    function displayWorkflowReassignErrorMessages (errorMessages) {
        jQuery('#ow-reassign-messages').html(errorMessages);
        jQuery('#ow-reassign-messages').removeClass('owf-hidden');

        // scroll to the top of the window to display the error messages
        jQuery('.simplemodal-wrap').css('overflow', 'hidden');
        jQuery('.simplemodal-wrap').animate({ scrollTop: 0 }, 'slow');
        jQuery('.simplemodal-wrap').css('overflow', 'scroll');
        jQuery('.changed-data-set span').removeClass('loading');
        jQuery('#simplemodal-container').css('max-height', '90%');

        // call modal.setPosition, so that the window height can adjust automatically depending on the displayed fields.
        jQuery.modal.setPosition();
    }

});