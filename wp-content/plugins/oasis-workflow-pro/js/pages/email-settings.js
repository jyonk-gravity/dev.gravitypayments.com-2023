jQuery(document).ready(function ($) {
    jQuery('#chk_reminder_day').click(function () {

        if (jQuery(this).is(':checked')) {
            jQuery('#oasiswf_reminder_days').prop('disabled', false);
        } else {
            jQuery('#oasiswf_reminder_days').val('');
            jQuery('#oasiswf_reminder_days').prop('disabled', true);
        }
    });

    jQuery('#chk_reminder_day_after').click(function () {
        if (jQuery(this).is(':checked')) {
            jQuery('#oasiswf_reminder_days_after').prop('disabled', false);
        } else {
            jQuery('#oasiswf_reminder_days_after').val('');
            jQuery('#oasiswf_reminder_days_after').prop('disabled', true);
        }
    });

    jQuery('#emailSettingSave').click(function () {
        if (jQuery('#chk_reminder_day').is(':checked')) {
            if (!jQuery('#oasiswf_reminder_days').val()) {
                alert('Please enter the number of days for reminder email before due date.');
                return false;
            }
            if (isNaN(jQuery('#oasiswf_reminder_days').val())) {
                alert('Please enter a numeric value for reminder email before due date.');
                return false;
            }
        }

        if (jQuery('#chk_reminder_day_after').is(':checked')) {
            if (!jQuery('#oasiswf_reminder_days_after').val()) {
                alert('Please enter the number of days for reminder email after due date.');
                return false;
            }
            if (isNaN(jQuery('#oasiswf_reminder_days_after').val())) {
                alert('Please enter a numeric value for reminder email after due date.');
                return false;
            }
        }
    });

    // Email type select change show the template accordingly
    jQuery('.email-template').hide();
    jQuery('#post_publish').show();
    jQuery('#email-type-select').change(function () {
        jQuery('.email-template').hide();
        jQuery('#' + jQuery(this).val()).show();
    });

    // Initialize select2 plugin
    jQuery('#post_publish_email_actors,#revised_post_email_actors,#unauthorized_update_email_actors,#task_claim_email_actors,#post_submit_email_actors,#workflow_abort_email_actors').select2({
        placeholder: 'Select email recipients',
        allowClear: true,
        closeOnSelect: false,
        formatSelection: formatAssigneeSelection //display whether the select option is a role, user or group
    });

    // attach select2 drop down to the available actors select box.
    jQuery('#post_publish_cc_email_actors, #post_publish_bcc_email_actors, #revised_post_cc_email_actors, #revised_post_bcc_email_actors, #unauthorized_cc_email_actors, #unauthorized_bcc_email_actors, #claim_cc_email_actors, #claim_bcc_email_actors, #post_submit_cc_email_actors, #post_submit_bcc_email_actors, #abort_cc_email_actors, #abort_bcc_email_actors').select2({
        theme: 'classic',
        placeholder: 'Select email recipients',
        allowClear: true,
        closeOnSelect: false,
        formatSelection: formatAssigneeSelection //display whether the select option is a role, user or group
    });

    // change the display to show if the selected option is user, group or role.
    function formatAssigneeSelection (val) {
        var assign_type = val.id.slice(0, 2);
        switch (assign_type) {
            case 'u@': // user
                assign_type = ' (user)';
                break;
            case 'r@': // user roles
                assign_type = ' (role)';
                break;
            case 'e@': // external users
                assign_type = ' (external user)';
                break;
        }
        return val.text + assign_type;
    }

});
