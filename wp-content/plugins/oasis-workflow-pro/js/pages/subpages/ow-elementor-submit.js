jQuery(document).ready(function () {
    jQuery(document).on('click', '#elementorSubmitSave', function () {
        // validate if all the required fields have data
        if (!validateRequiredFormFields()) {
            return false;
        }

        // looks like we are good, lets show the loading icon and hide the "submit" button,
        // so that the user cannot click twice
        jQuery('.changed-data-set span').addClass('loading');
        jQuery(this).hide();

        // validate and get selected actors
        var actors = validateAndGetSelectedActors();

        // if no actors found, return
        if (!actors)
            return;

        jQuery('#elementor-panel-footer-sub-menu-item-save-draft').click();

        // adding a timeout to allow elementor or another plugin to save the post before submitting to task.
        window.setTimeout(function () {
            var postId = '';
            // If enable elementor editor
            if (owf_process === 'submit') {
                postId = get_post_id_from_query_string();
            } else {
                postId = jQuery('#post_ID').val();
            }

            var user_specified_publish_date = '';

            // set user specified publish date into hidden field
            if (typeof jQuery('#publish-date').val() != 'undefined' && jQuery('#publish-date').val() != '') {
                user_specified_publish_date = jQuery('#publish-date').val() + ' @ ' + jQuery('#publish-hour').val() + ':' + jQuery('#publish-min').val();
            }

            // TODO: should work with Teams add on too.

            // TODO: pre publish checklist - displayed from the checklist add-on
            var selected_pre_publish_conditions = [];

            var elementor_submit_to_workflow_data = {
                action: 'elementor_submit_to_workflow',
                post_id: postId,
                step_id: jQuery('#step-select').val(),
                workflow_id: jQuery('#workflow-select').val(),
                priority_select: jQuery('#priority-select').val(),
                actor_ids: actors,
                is_team_available: jQuery('#hi_is_team').val(),
                due_date: jQuery('#due-date').val(),
                publish_datetime: user_specified_publish_date,
                comment: jQuery('#workflowComments').val(),
                security: jQuery('#owf_signoff_ajax_nonce').val()
            };

            jQuery.post(ajaxurl, elementor_submit_to_workflow_data, function (response) {
                if (response == -1) {
                    return false; // Invalid nonce
                }

                // if response is false then there are no users for given role..!
                if (!response.success) {
                    displayWorkflowSubmitErrorMessages(response.data.errorMessage);

                    // display the "submit" button again
                    jQuery('#elementorSubmitSave').show();

                    return false;
                }
                if (response.success) {
                    jQuery.modal.close();
                    //Hide submit to workflow button
                    jQuery('#elementor-panel-footer-sub-menu-item-submit-workflow').hide();

                    swal(owf_submit_workflow_vars.elementorWorkflowSubmitText, {
                        button: {
                            text: owf_submit_workflow_vars.elementorExitButtonText,
                            className: 'owf-elementor-redirect'
                        }
                    }).then(function () {
                        // Redirect the user as per default or user provided link
                        window.location.href = response.data.redirectLink;
                    });
                }
            });
        }, 2000);
    });

    function validateRequiredFormFields () {
        if (!jQuery('#workflow-select').val()) {
            alert(owf_submit_workflow_vars.selectWorkflow);
            return false;
        }

        if (!jQuery('#step-select').val()) {
            alert(owf_submit_workflow_vars.selectStep);
            return false;
        }

        if (jQuery('#step-select').val() == 'nodefine') {
            alert(owf_submit_workflow_vars.stepNotDefined);
            return false;
        }

        if (!jQuery('#workflowComments').val() && owf_submit_workflow_vars.isCommentsMandotory === 'mandatory') {
            alert(owf_submit_workflow_vars.emptyComments);
            return false;
        }

        /*  if reminder email checkbox is selected in workflow email settings.
         *  then Due Date is Required Else Not
         */
        if (owf_submit_workflow_vars.drdb != '' || owf_submit_workflow_vars.drda != '' || owf_submit_workflow_vars.defaultDueDays != '') {
            if (jQuery('#due-date').val() == '') {
                alert(owf_submit_workflow_vars.dueDateRequired);
                return false;
            }
        }

        return true;
    }

    function validateAndGetSelectedActors () {
        var is_assigned_to_all = parseInt(jQuery('#assign_to_all').val());

        // Case 1: teams add-on is active
        // Validate if team is selected
        // if not, display error message.
        if (owf_submit_workflow_vars.workflowTeamsAvailable == 'yes') {
            var optionNum = jQuery('#teams-list-select').val();
            if (optionNum == '') {
                jQuery('.changed-data-set span').removeClass('loading');
                jQuery('#submitSave').show();
                alert(owf_submit_workflow_vars.noTeamSelected);
                return false;
            }

            // If assign to all is checked simply send team_id
            var team_id = jQuery('#teams-list-select').val();
            if (team_id && is_assigned_to_all === 1) {
                jQuery('#hi_is_team').val('true');
                return team_id;
            } else if (team_id && is_assigned_to_all !== 1) {
                // If assign to all is uncheck send multi actors
                var selectedOptionCount = jQuery('#actors-set-select option').length;
                if (!selectedOptionCount) {
                    alert(owf_submit_workflow_vars.noAssignedActors);
                    jQuery('.changed-data-set span').removeClass('loading');
                    jQuery('#submitSave').show();
                    return false;
                }
                var multi_actors = '', i = 1;
                jQuery('#actors-set-select option').each(function () {
                    if (i == selectedOptionCount)
                        multi_actors += jQuery(this).val();
                    else
                        multi_actors += jQuery(this).val() + '@';
                    i++;
                });
                if (multi_actors) {
                    jQuery('#hi_is_team').val(team_id);
                    return multi_actors;
                } else {
                    return false;
                }
            }
        }

        // Case 2: Assign to all is checked
        // nothing to validates, simply return true
        if (is_assigned_to_all === 1) {
            return true;
        }

        // Case 3: Regular multi-actor selection
        // Validate if, at least one user is selected,
        // if not, display error message
        // if yes, return the selected actor(s)

        var selectedOptionCount = jQuery('#actors-set-select option').length;
        if (!selectedOptionCount && is_assigned_to_all !== 1) {
            alert(owf_submit_workflow_vars.noAssignedActors);
            jQuery('.changed-data-set span').removeClass('loading');
            jQuery('#submitSave').show();
            return false;
        }
        var multi_actors = '', i = 1;
        jQuery('#actors-set-select option').each(function () {
            if (i == selectedOptionCount)
                multi_actors += jQuery(this).val();
            else
                multi_actors += jQuery(this).val() + '@';
            i++;
        });
        if (multi_actors && is_assigned_to_all !== 1) {
            return multi_actors;
        } else {
            return false;
        }
    }

    function displayWorkflowSubmitErrorMessages (errorMessages) {
        jQuery('.error').hide();
        jQuery('#ow-step-messages').html(errorMessages);
        jQuery('#ow-step-messages').removeClass('owf-hidden');

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