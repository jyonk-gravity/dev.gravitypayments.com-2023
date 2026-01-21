<?php
/*
 * Settings class for Workflow settings
 *
 * @copyright   Copyright (c) 2016, Nugget Solutions, Inc
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * OW_Workflow_Settings Class
 *
 * @since 2.0
 */
class OW_Workflow_Settings {

	/**
	 * @var string group name
	 */
	protected $ow_workflow_group_name = 'ow-settings-workflow';

	/**
	 * @var string activate workflow option name
	 */
	protected $ow_activate_workflow = 'oasiswf_activate_workflow';

	/**
	 * @var string default due days option name
	 */
	protected $ow_default_due_days_option_name = 'oasiswf_default_due_days';

	/**
	 * @var string show workflow on post types option name
	 */
	protected $ow_show_wfsettings_on_post_types_option_name = 'oasiswf_show_wfsettings_on_post_types';

	/**
	 * @var string priority setting option name
	 */
	protected $ow_priority_setting_option_name = 'oasiswf_priority_setting';

	/**
	 * @var string publish date setting option name
	 */
	protected $ow_publish_date_setting_option_name = 'oasiswf_publish_date_setting';

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
		register_setting( $this->ow_workflow_group_name,
			$this->ow_activate_workflow,
			array( $this, 'validate_activate_workflow_process' ) );

		register_setting( $this->ow_workflow_group_name,
			$this->ow_default_due_days_option_name,
			array( $this, 'validate_default_due_days' ) );

		register_setting( $this->ow_workflow_group_name,
			$this->ow_show_wfsettings_on_post_types_option_name,
			array( $this, 'validate_selected_post_types' ) );

		register_setting( $this->ow_workflow_group_name,
			$this->ow_priority_setting_option_name,
			array( $this, 'validate_priority_setting' ) );

		register_setting( $this->ow_workflow_group_name,
			$this->ow_publish_date_setting_option_name,
			array( $this, 'validate_publish_date_setting' ) );
	}

	/**
	 * sanitize user data
	 *
	 * @param string $is_activated
	 *
	 * @return string
	 */
	public function validate_activate_workflow_process( $is_activated ) {
		return sanitize_text_field( $is_activated );
	}

	/**
	 * Validate due days
	 *
	 * @param string or int $default_due_days
	 *
	 * @return int
	 */
	public function validate_default_due_days( $default_due_days ) {
		// If due days is not empty then do validate and sanitize user input
		$due_days = '';
		if ( ! empty( $default_due_days ) ) {
			if ( is_numeric( $default_due_days ) ) {
				$due_days = intval( sanitize_text_field( $default_due_days ) );
			} else if ( ! is_numeric( $default_due_days ) ) {
				add_settings_error( $this->ow_workflow_group_name,
					$this->ow_default_due_days_option_name,
					esc_html__( 'Please enter a numeric value for default due date.', 'oasisworkflow' ), 'error' );
			} else {
				add_settings_error( $this->ow_workflow_group_name,
					$this->ow_default_due_days_option_name,
					esc_html__( 'Please enter the number of days for default due date.', 'oasisworkflow' ), 'error' );
			}
		}

		return $due_days;
	}

	/**
	 * do validate and sanitize selected post types
	 *
	 * @param array $selected_post_types
	 *
	 * @return array
	 */
	public function validate_selected_post_types( $selected_post_types ) {
		$post_type = array();
		if ( is_array( $selected_post_types ) && count( $selected_post_types ) > 0 ) {

			// Sanitize the value
			$selected_post_types = array_map( 'esc_attr', $selected_post_types );

			foreach ( $selected_post_types as $selected_post_type ) {
				array_push( $post_type, $selected_post_type );
			}
		}

		return $post_type;
	}


	/**
	 * sanitize data
	 *
	 * @param string $priority_setting
	 *
	 * @return string
	 */
	public function validate_priority_setting( $priority_setting ) {
		return sanitize_text_field( $priority_setting );
	}

	/**
	 * sanitize data
	 *
	 * @param string $publish_date_setting
	 *
	 * @return string
	 */
	public function validate_publish_date_setting( $publish_date_setting ) {
		return sanitize_text_field( $publish_date_setting );
	}

	/**
	 * generate the page
	 *
	 * @since 2.0
	 */
	public function add_settings_page() {
		$is_activated_workflow         = get_option( $this->ow_activate_workflow );
		$default_due_days              = get_option( $this->ow_default_due_days_option_name );
		$show_wfsettings_on_post_types = get_option( $this->ow_show_wfsettings_on_post_types_option_name );

		$priority_setting     = get_option( $this->ow_priority_setting_option_name );
		$publish_date_setting = get_option( $this->ow_publish_date_setting_option_name );
		?>
        <form id="wf_settings_form" method="post" action="options.php">
			<?php
			settings_fields( $this->ow_workflow_group_name ); // adds nonce for current settings page
			?>
            <div id="workflow-setting">
                <div id="settingstuff">
                    <div class="select-info">
                        <label class="settings-title">
                            <input type="checkbox"
                                   name="<?php echo $this->ow_activate_workflow; // phpcs:ignore ?>"
                                   value="active"
								<?php checked( $is_activated_workflow, 'active' ); ?> />
                            &nbsp;&nbsp;<?php esc_html_e( "Activate Workflow process ?", "oasisworkflow" ); ?>
                        </label>
                        <br/>
                        <span class="description">
                        <?php esc_html_e( "(After you are done setting up your editorial workflow, make it available for use by activating the workflow process.)", "oasisworkflow" ); ?>
                      </span>
                    </div>

                    <div class="select-info">
                        <label class="settings-title">
                            <input type="checkbox" id="chk_default_due_days"
								<?php echo ( $default_due_days ) ? 'checked' : ''; ?> />
                            &nbsp;&nbsp;
							<?php esc_html_e( "Set default Due date as CURRENT DATE + ", "oasisworkflow" ); ?>
                        </label>
                        <input type="text" id="default_due_days"
                               name="<?php echo $this->ow_default_due_days_option_name; // phpcs:ignore ?>"
                               size="4" class="default_due_days"
                               value="<?php echo esc_attr( $default_due_days ); ?>"
                               maxlength=2/>
                        <label class="settings-title"><?php esc_html_e( "day(s).", "oasisworkflow" ); ?></label>
                    </div>
					<?php
					// phpcs:ignore
					echo <<<TRIGGER_EVENT
                        <script>
                           jQuery(document).ready(function() {
                              jQuery(function(){
                                 jQuery("#chk_default_due_days").click(function(){
                                     if(!jQuery(this).is(":checked")){ //checks if the checkbox/this is selected or not
                                       jQuery("#default_due_days").val(""); //empty the input value
                                     }
                                 });
                              });
                           });
                        </script>
TRIGGER_EVENT;
					?>

                    <div class="select-info">
                        <div class="list-section-heading">
                            <label>
								<?php esc_html_e( "Show Workflow options for the following post/page types:", "oasisworkflow" ) ?>
                            </label>
                        </div>
						<?php
						OW_Utility::instance()->owf_checkbox_post_types_multi(
							$this->ow_show_wfsettings_on_post_types_option_name . '[]', $show_wfsettings_on_post_types );
						?>
                    </div>

                    <div class="select-info">
                        <label class="settings-title">
                            <input type="checkbox" id="enable_priority"
                                   name="<?php echo $this->ow_priority_setting_option_name; // phpcs:ignore ?>"
                                   value="enable_priority"
								<?php checked( $priority_setting, 'enable_priority' ); ?>/>
							<?php esc_html_e( 'Enable workflow task priority.', 'oasisworkflow' ); ?>
                        </label>
                        <br/>
                        <span class="description">
                           <?php esc_html_e( "(Allows user to specify priority when signing off the task.)", "oasisworkflow" ); ?>
                      </span>
                    </div>

                    <div class="select-info">
                        <label class="settings-title">
                            <input type="checkbox" id="hide_publish_date"
                                   name="<?php echo $this->ow_publish_date_setting_option_name; // phpcs:ignore ?>"
                                   value="hide"
								<?php checked( $publish_date_setting, 'hide' ); ?>/>
							<?php esc_html_e( 'Hide Publish Date field on "Submit to Workflow".', 'oasisworkflow' ); ?>
                        </label>
                    </div>


                    <div class="select-info full-width">
                        <div id="owf_settings_button_bar">
                            <input type="submit" id="settingSave"
                                   class="button button-primary button-large"
                                   value="<?php esc_attr_e( "Save", "oasisworkflow" ); ?>"/>
                        </div>
                    </div>
                </div>
            </div>
        </form>
		<?php
	}

}

$ow_workflow_settings = new OW_Workflow_Settings();
