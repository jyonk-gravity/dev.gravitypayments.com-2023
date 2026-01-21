jQuery(document).ready(function ($) {
   jQuery("#chk_reminder_day").click(function () {
      if (jQuery(this).is(":checked")) {
         jQuery("#oasiswf_reminder_days").prop("disabled", false);
      } else {
         jQuery("#oasiswf_reminder_days").val('');
         jQuery("#oasiswf_reminder_days").prop("disabled", true);
      }
   });

   jQuery("#chk_reminder_day_after").click(function () {
      if (jQuery(this).is(":checked")) {
         jQuery("#oasiswf_reminder_days_after").prop("disabled", false);
      } else {
         jQuery("#oasiswf_reminder_days_after").val('');
         jQuery("#oasiswf_reminder_days_after").prop("disabled", true);
      }
   });

   jQuery("#emailSettingSave").click(function () {
      if (jQuery("#chk_reminder_day").is(":checked")) {
         if (!jQuery("#oasiswf_reminder_days").val()) {
            alert("Please enter the number of days for reminder email before due date.");
            return false;
         }
         if (isNaN(jQuery("#oasiswf_reminder_days").val())) {
            alert("Please enter a numeric value for reminder email before due date.");
            return false;
         }
      }

      if (jQuery("#chk_reminder_day_after").is(":checked")) {
         if (!jQuery("#oasiswf_reminder_days_after").val()) {
            alert("Please enter the number of days for reminder email after due date.");
            return false;
         }
         if (isNaN(jQuery("#oasiswf_reminder_days_after").val())) {
            alert("Please enter a numeric value for reminder email after due date.");
            return false;
         }
      }
   });
});
