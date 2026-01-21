<?php
/*
 * Settings class for Workflow Email settings
 *
 * @copyright   Copyright (c) 2015, Nugget Solutions, Inc
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
   exit;
}

/**
 * OW_Email_Settings Class
 *
 * @since 2.0
 */
class OW_Email_Settings {

	// email settings option name
	protected $ow_email_option_name = "oasiswf_email_settings";

	// TODO : merge this into oasiswf_email_settings
	// email reminder day before option name
	protected $ow_email_reminder_day_before_option_name = "oasiswf_reminder_days";

	// TODO : merge this into oasiswf_email_settings
	// email reminder day after option name
	protected $ow_email_reminder_day_after_option_name = "oasiswf_reminder_days_after";

	// default values
	protected $ow_email_option_default_value = array (
		'from_name' => "",
		'from_email_address' => "",
		'assignment_emails' => "",
		'reminder_emails' => "",
		'post_publish_emails' => "",
		'unauthorized_post_update_emails' => "",
		'abort_email_to_author' => ""	,
      'submit_to_workflow_email' => ""
	);

	/**
	 * Set things up.
	 *
	 * @since 2.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'init_settings' ) );
	}

	// White list our options using the Settings API
	public function init_settings() {
		register_setting( 'ow-settings-email', $this->ow_email_option_name, array($this, 'validate_email_settings') );

		//TODO : merge this into oasiswf_email_settings
		register_setting( 'ow-settings-email', $this->ow_email_reminder_day_before_option_name, array($this, 'validate_reminder_days_before') );

		//TODO : merge this into oasiswf_email_settings
		register_setting( 'ow-settings-email', $this->ow_email_reminder_day_after_option_name, array($this, 'validate_reminder_days_after') );
	}

	/**
	 * sanitize and validate the input (if required)
	 *
	 * @since 2.0
	 */
	public function validate_email_settings( $input ) {

		$email_settings = array();
		$email_settings['from_name'] = sanitize_text_field( $input['from_name'] );
		$email_settings['from_email_address'] = sanitize_text_field( $input['from_email_address'] );
		if ( array_key_exists( 'assignment_emails', $input ) ) {
			$email_settings['assignment_emails'] = sanitize_text_field( $input['assignment_emails'] );
		} else {
			$email_settings['assignment_emails'] = $this->ow_email_option_default_value['assignment_emails'];
		}

		if ( array_key_exists( 'reminder_emails', $input ) ) {
			$email_settings['reminder_emails'] = sanitize_text_field( $input['reminder_emails'] );
		} else {
			$email_settings['reminder_emails'] = $this->ow_email_option_default_value['reminder_emails'];
		}

		if ( array_key_exists( 'post_publish_emails', $input ) ) {
			$email_settings['post_publish_emails'] = sanitize_text_field( $input['post_publish_emails'] );
		} else {
			$email_settings['post_publish_emails'] = $this->ow_email_option_default_value['post_publish_emails'];
		}

		if ( array_key_exists( 'unauthorized_post_update_emails', $input ) ) {
			$email_settings['unauthorized_post_update_emails'] = sanitize_text_field( $input['unauthorized_post_update_emails'] );
		} else {
			$email_settings['unauthorized_post_update_emails'] = $this->ow_email_option_default_value['unauthorized_post_update_emails'];
		}

		if ( array_key_exists( 'abort_email_to_author', $input ) ) {
			$email_settings['abort_email_to_author'] = sanitize_text_field( $input['abort_email_to_author'] );
		} else {
			$email_settings['abort_email_to_author'] = $this->ow_email_option_default_value['abort_email_to_author'];
		}

      if ( array_key_exists( 'submit_to_workflow_email', $input ) ) {
			$email_settings['submit_to_workflow_email'] = sanitize_text_field( $input['submit_to_workflow_email'] );
		} else {
			$email_settings['submit_to_workflow_email'] = $this->ow_email_option_default_value['submit_to_workflow_email'];
		}

		return $email_settings;
	}

	/**
	 * sanitize and validate the input (if required)
	 *
	 * @since 2.0
	 */
	public function validate_reminder_days_before( $reminder_days_before ) {
		$reminder_days_before = intval( sanitize_text_field( $reminder_days_before ) );

		if ( $reminder_days_before == 0 ){ //blank out the value
			return "";      
      }
		return $reminder_days_before;
	}

	/**
	 * sanitize and validate the input (if required)
	 *
	 * @since 2.0
	 */
	public function validate_reminder_days_after( $reminder_days_after ) {
		$reminder_days_after = intval( sanitize_text_field( $reminder_days_after ) );

		if ( $reminder_days_after == 0 ){ //blank out the value
			return "";
      }
		return $reminder_days_after;
	}

	/**
	 * generate the page
	 *
	 * @since 2.0
	 */
	public function add_settings_page() {
		$email_options = get_option( $this->ow_email_option_name );
		$email_reminder_days_before_option = get_option( $this->ow_email_reminder_day_before_option_name );
		$email_reminder_days_after_option = get_option( $this->ow_email_reminder_day_after_option_name );
		?>
		<form id="wf_settings_form" method="post" action="options.php">
    	<?php
    	// adds nonce and option_page fields for the settings page
    	settings_fields('ow-settings-email');
    	?>
        <div id="workflow-email-setting">
            <div id="settingstuff">
                <div class="select-info">
                    <label class="settings-title">
                        <?php esc_html_e( "From Name:", "oasisworkflow" ); ?>
                    </label>
                    <input type="text" class="regular-text" name="<?php echo esc_attr( $this->ow_email_option_name ); ?>[from_name]" value="<?php echo esc_attr( $email_options['from_name'] ); ?>" />
                    <br/>
                    <span class="description"><?php esc_html_e( "(Name to be used for sending the workflow related emails. If left blank, the emails will be sent from the blog name.)", "oasisworkflow" ); ?></span>
                </div>
                <div class="select-info">
                    <label class="settings-title">
                        <?php esc_html_e( "From Email:", "oasisworkflow" ); ?>
                    </label>
                    <input type="text" class="regular-text" name="<?php echo esc_attr( $this->ow_email_option_name ); ?>[from_email_address]" value="<?php echo esc_attr( $email_options['from_email_address'] ); ?>" />
                    <br/>
                    <span class="description"><?php esc_html_e( "(Email address to be used for sending the workflow related emails. If left blank, the default email will be used.)", "oasisworkflow" ); ?></span>
                </div>
                <hr/>
                
                <div class="select-info">
                    <input type="checkbox" id="assignment_emails" name="<?php echo esc_attr( $this->ow_email_option_name ); ?>[assignment_emails]" value="yes"<?php checked( $email_options['assignment_emails'], 'yes' ); ?> />&nbsp;&nbsp;
                    <label class="settings-title" for="assignment_emails"><?php esc_html_e( "Check this box if you want to send emails when tasks are assigned.", "oasisworkflow" ); ?> </label>
                </div>
                
                <div class="select-info">
                    <input type="checkbox" id="post_publish_emails" name="<?php echo esc_attr( $this->ow_email_option_name ); ?>[post_publish_emails]" value="yes"<?php checked( $email_options['post_publish_emails'], 'yes' ); ?> />&nbsp;&nbsp;
                    <label class="settings-title" for="post_publish_emails"><?php esc_html_e( "Check this box if you want to send an email to the author when post/page is published.", "oasisworkflow" ); ?> </label>
                </div>
                
                <div class="select-info">
                   <input type="checkbox" id="unauthorized_post_update_emails" name="<?php echo esc_attr( $this->ow_email_option_name ); ?>[unauthorized_post_update_emails]" value="yes"<?php checked( $email_options['unauthorized_post_update_emails'], 'yes' ); ?> />&nbsp;&nbsp;
                   <label class="settings-title" for="unauthorized_post_update_emails"><?php esc_html_e( "Check this box if you want to send an alert email to the current assignees when a non-assignee updates the article outside the workflow.", "oasisworkflow" ); ?> </label>
                </div>
                
                <div class="select-info">
                   <input type="checkbox" id="abort_email_to_author" name="<?php echo esc_attr( $this->ow_email_option_name ); ?>[abort_email_to_author]" value="yes"<?php checked( $email_options['abort_email_to_author'], 'yes' ); ?> />&nbsp;&nbsp;
                   <label class="settings-title" for="abort_email_to_author"><?php esc_html_e( "Check this box if you want to send email to the author when the workflow is aborted/cancelled.", "oasisworkflow" ); ?> </label>
                </div>
                
                <div class="select-info">
                   <input type="checkbox" id="submit_to_workflow_email" name="<?php echo esc_attr( $this->ow_email_option_name ); ?>[submit_to_workflow_email]" value="yes"<?php checked( $email_options['submit_to_workflow_email'], 'yes' ); ?> />&nbsp;&nbsp;
                   <label class="settings-title" for="submit_to_workflow_email"><?php esc_html_e( "Check this box if you want to send email to the submitter when post/page is submitted to workflow.", "oasisworkflow" ); ?> </label>
                </div>
                
                <fieldset class="owf_fieldset">
                    <legend><?php esc_html_e( "Task reminder settings", "oasisworkflow" ); ?></legend>
                    <span class="description"><?php esc_html_e( "(Applicable only if reminder email configuration is completed during workflow setup.)", "oasisworkflow" ); ?></span>
                    <div class="select-info">
                       <input type="checkbox" id="reminder_emails" name="<?php echo esc_attr( $this->ow_email_option_name ); ?>[reminder_emails]" value="yes"<?php checked( $email_options['reminder_emails'], 'yes' ); ?> />&nbsp;&nbsp;
                       <label class="settings-title" for="reminder_emails"><?php esc_html_e( "Check this box if you want to send reminder emails about a pending task.", "oasisworkflow" ); ?> </label>
                        <br/>
                    </div>
                    <div class="select-info">
                        <label class="settings-title">
                            <input type="checkbox" id="chk_reminder_day" <?php echo ( $email_reminder_days_before_option ) ? "checked" : ""; ?> />&nbsp;&nbsp;
                            <?php esc_html_e( " Send Reminder Email", "oasisworkflow" ); ?>
                        </label>
                        <input type="text" size="4" class="reminder_days" id="<?php echo esc_attr( $this->ow_email_reminder_day_before_option_name ); ?>" name="<?php echo esc_attr( $this->ow_email_reminder_day_before_option_name ); ?>" value="<?php echo esc_attr( $email_reminder_days_before_option); ?>" maxlength=2 />
                        <label class="settings-title"><?php esc_html_e( "day(s) before due date.", "oasisworkflow" ); ?></label>
                    </div>
                    <div class="select-info">
                        <label class="settings-title">
                            <input type="checkbox" id="chk_reminder_day_after" <?php echo ($email_reminder_days_after_option) ? "checked" : ""; ?> />&nbsp;&nbsp;
                            <?php esc_html_e( " Send Reminder Email", "oasisworkflow" ); ?>
                        </label>
                        <input type="text" size="4" class="reminder_days" id="<?php echo esc_attr( $this->ow_email_reminder_day_after_option_name ); ?>" name="<?php echo esc_attr( $this->ow_email_reminder_day_after_option_name ); ?>" value="<?php echo esc_attr( $email_reminder_days_after_option ); ?>" maxlength=2 />
                        <label class="settings-title"><?php esc_html_e( "day(s) after due date.", "oasisworkflow" ); ?></label>
                    </div>
                </fieldset>
                <div class="select-info full-width">
	                <div id="owf_settings_button_bar">
	                    <input type="submit" id="emailSettingSave"
	                           class="button button-primary button-large"
	                           value="<?php esc_attr_e( "Save", "oasisworkflow" ); ?>" />
	                </div>
	            </div>
            </div>
        </div>
		</form>
	<?php
	}
}

$ow_email_settings = new OW_Email_Settings();
