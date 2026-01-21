<?php
/*
 * Settings class for Workflow Terminology settings
 *
 * @copyright   Copyright (c) 2016, Nugget Solutions, Inc
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * OW_Workflow_Terminology_Settings Class
 *
 * @since 2.0
 */
class OW_Workflow_Terminology_Settings {

	/**
	 * @var string workflow terminology option name
	 */
	protected $ow_workflow_terminology_option_name = 'oasiswf_custom_workflow_terminology';
	// default values
	protected $ow_workflow_terminology_option_default_value = array(
		'submitToWorkflowText' => "",
		'signOffText'          => "",
		'assignActorsText'     => "",
		'dueDateText'          => "",
		'publishDateText'      => "",
		'abortWorkflowText'    => "",
		'workflowHistoryText'  => "",
		'taskPriorityText'     => ""
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

		register_setting( 'ow-settings-workflow-terminology',
			esc_attr( $this->ow_workflow_terminology_option_name ),
			array( $this, 'validate_workflow_terminogy_settings' ) );
	}

	/**
	 * Validate and sanitize all user input data
	 *
	 * @param array $input
	 *
	 * @return array
	 */
	public function validate_workflow_terminogy_settings( array $input ) {
		$workflow_terminology_settings = array();

		if ( array_key_exists( 'submitToWorkflowText', $input ) ) {
			$workflow_terminology_settings['submitToWorkflowText'] = sanitize_text_field( $input['submitToWorkflowText'] );
		} else {
			$workflow_terminology_settings['submitToWorkflowText'] = $this->ow_workflow_terminology_option_default_value['submitToWorkflowText'];
		}

		if ( array_key_exists( 'signOffText', $input ) ) {
			$workflow_terminology_settings['signOffText'] = sanitize_text_field( $input['signOffText'] );
		} else {
			$workflow_terminology_settings['signOffText'] = $this->ow_workflow_terminology_option_default_value['signOffText'];
		}

		if ( array_key_exists( 'assignActorsText', $input ) ) {
			$workflow_terminology_settings['assignActorsText'] = sanitize_text_field( $input['assignActorsText'] );
		} else {
			$workflow_terminology_settings['assignActorsText'] = $this->ow_workflow_terminology_option_default_value['assignActorsText'];
		}

		if ( array_key_exists( 'dueDateText', $input ) ) {
			$workflow_terminology_settings['dueDateText'] = sanitize_text_field( $input['dueDateText'] );
		} else {
			$workflow_terminology_settings['dueDateText'] = $this->ow_workflow_terminology_option_default_value['dueDateText'];
		}

		if ( array_key_exists( 'publishDateText', $input ) ) {
			$workflow_terminology_settings['publishDateText'] = sanitize_text_field( $input['publishDateText'] );
		} else {
			$workflow_terminology_settings['publishDateText'] = $this->ow_workflow_terminology_option_default_value['publishDateText'];
		}

		if ( array_key_exists( 'abortWorkflowText', $input ) ) {
			$workflow_terminology_settings['abortWorkflowText'] = sanitize_text_field( $input['abortWorkflowText'] );
		} else {
			$workflow_terminology_settings['abortWorkflowText'] = $this->ow_workflow_terminology_option_default_value['abortWorkflowText'];
		}

		if ( array_key_exists( 'workflowHistoryText', $input ) ) {
			$workflow_terminology_settings['workflowHistoryText'] = sanitize_text_field( $input['workflowHistoryText'] );
		} else {
			$workflow_terminology_settings['workflowHistoryText'] = $this->ow_workflow_terminology_option_default_value['workflowHistoryText'];
		}

		if ( array_key_exists( 'taskPriorityText', $input ) ) {
			$workflow_terminology_settings['taskPriorityText'] = sanitize_text_field( $input['taskPriorityText'] );
		} else {
			$workflow_terminology_settings['taskPriorityText'] = $this->ow_workflow_terminology_option_default_value['taskPriorityText'];
		}

		$workflow_terminology_settings = apply_filters( 'owf_sanitize_workflow_terminology', $workflow_terminology_settings, $input );

		return $workflow_terminology_settings;
	}

	/**
	 * generate the page
	 *
	 * @since 2.0
	 */
	public function add_settings_page() {
		$workflow_terminology_option = OW_Utility::instance()->get_custom_workflow_terminology();

		$submit_to_workflow = $workflow_terminology_option['submitToWorkflowText'];
		$sign_off           = $workflow_terminology_option['signOffText'];
		$assign_actors      = $workflow_terminology_option['assignActorsText'];
		$due_date           = $workflow_terminology_option['dueDateText'];
		$publish_date       = $workflow_terminology_option['publishDateText'];
		$abort_workflow     = $workflow_terminology_option['abortWorkflowText'];
		$workflow_history   = $workflow_terminology_option['workflowHistoryText'];
		$task_priority      = $workflow_terminology_option['taskPriorityText'];
		?>
        <form id="wf_settings_form" method="post" action="options.php">
			<?php
			// adds nonce and option_page fields for the settings page
			settings_fields( 'ow-settings-workflow-terminology' );
			?>
            <div id="workflow-terminology-setting">
                <div id="settingstuff">
                    <div class="select-info">
                        <div class="half-width left">
                            <label for="submitToWorkflowText"><?php esc_html_e( "Submit to Workflow", "oasisworkflow" ) . esc_html__( " Label", "oasisworkflow" ) ?></label>
                        </div>
                        <div class="half-width left">
                            <input type="text" class="regular-text"
                                   name="<?php echo esc_attr( $this->ow_workflow_terminology_option_name ); ?>[submitToWorkflowText]"
                                   value="<?php echo esc_attr( $submit_to_workflow ); ?>"/>
                            <p class="description"
                               id="tagline-description"><?php esc_html_e( "Label for \"Submit to Workflow\" button/link.", "oasisworkflow" ); ?></p>
                        </div>
                        <br class="clear">
                    </div>
                    <div class="select-info">
                        <div class="half-width left">
                            <label for="signOffText"><?php esc_html_e( "Sign Off", "oasisworkflow" ) . esc_html__( " Label", "oasisworkflow" ) ?></label>
                        </div>
                        <div class="half-width left">
                            <input type="text" class="regular-text"
                                   name="<?php echo esc_attr( $this->ow_workflow_terminology_option_name ); ?>[signOffText]"
                                   value="<?php echo esc_attr( $sign_off ); ?>"/>
                            <p class="description"
                               id="tagline-description"><?php esc_html_e( "Label for \"Sign Off\" button/link.", "oasisworkflow" ); ?></p>
                        </div>
                        <br class="clear">
                    </div>
                    <div class="select-info">
                        <div class="half-width left">
                            <label for="assignActorsText"><?php esc_html_e( "Assign Actor(s)", "oasisworkflow" ) . esc_html__( " Label", "oasisworkflow" ) ?></label>
                        </div>
                        <div class="half-width left">
                            <input type="text" class="regular-text"
                                   name="<?php echo esc_attr( $this->ow_workflow_terminology_option_name ); ?>[assignActorsText]"
                                   value="<?php echo esc_attr( $assign_actors ); ?>"/>
                            <p class="description"
                               id="tagline-description"><?php esc_html_e( "Label for \"Assign Actor(s)\" field.", "oasisworkflow" ); ?></p>
                        </div>
                        <br class="clear">
                    </div>
                    <div class="select-info">
                        <div class="half-width left">
                            <label for="dueDateText"><?php esc_html_e( "Due Date", "oasisworkflow" ) . esc_html__( " Label", "oasisworkflow" ) ?></label>
                        </div>
                        <div class="half-width left">
                            <input type="text" class="regular-text"
                                   name="<?php echo esc_attr( $this->ow_workflow_terminology_option_name ); ?>[dueDateText]"
                                   value="<?php echo esc_attr( $due_date ); ?>"/>
                            <p class="description"
                               id="tagline-description"><?php esc_html_e( "Label for \"Due Date\" field.", "oasisworkflow" ); ?></p>
                        </div>
                        <br class="clear">
                    </div>
                    <div class="select-info">
                        <div class="half-width left">
                            <label for="publishDateText"><?php esc_html_e( "Publish Date", "oasisworkflow" ) . esc_html__( " Label", "oasisworkflow" ) ?></label>
                        </div>
                        <div class="half-width left">
                            <input type="text" class="regular-text"
                                   name="<?php echo esc_attr( $this->ow_workflow_terminology_option_name ); ?>[publishDateText]"
                                   value="<?php echo esc_attr( $publish_date ); ?>"/>
                            <p class="description"
                               id="tagline-description"><?php esc_html_e( "Label for \"Publish Date\" field on the \"Submit to Workflow\" popup.", "oasisworkflow" ); ?></p>
                        </div>
                        <br class="clear">
                    </div>
                    <div class="select-info">
                        <div class="half-width left">
                            <label for="abortWorkflowText"><?php esc_html_e( "Abort Workflow", "oasisworkflow" ) . esc_html__( " Label", "oasisworkflow" ) ?></label>
                        </div>
                        <div class="half-width left">
                            <input type="text" class="regular-text"
                                   name="<?php echo esc_attr( $this->ow_workflow_terminology_option_name ); ?>[abortWorkflowText]"
                                   value="<?php echo esc_attr( $abort_workflow ); ?>"/>
                            <p class="description"
                               id="tagline-description"><?php esc_html_e( "Label for \"Abort Workflow\" button/link.", "oasisworkflow" ); ?></p>
                        </div>
                        <br class="clear">
                    </div>
                    <div class="select-info">
                        <div class="half-width left">
                            <label for="workflowHistoryText"><?php esc_html_e( "Workflow History", "oasisworkflow" ) . esc_html__( " Label", "oasisworkflow" ) ?></label>
                        </div>
                        <div class="half-width left">
                            <input type="text" class="regular-text"
                                   name="<?php echo esc_attr( $this->ow_workflow_terminology_option_name ); ?>[workflowHistoryText]"
                                   value="<?php echo esc_attr( $workflow_history ); ?>"/>
                            <p class="description"
                               id="tagline-description"><?php esc_html_e( "Label for \"Workflow History\" menu.", "oasisworkflow" ); ?></p>
                        </div>
                        <br class="clear">
                    </div>

                    <div class="select-info">
                        <div class="half-width left">
                            <label for="addingTaskPriority"><?php esc_html_e( "Priority", "oasisworkflow" ) . esc_html__( " Label", "oasisworkflow" ) ?></label>
                        </div>
                        <div class="half-width left">
                            <input type="text"
                                   name="<?php echo esc_attr( $this->ow_workflow_terminology_option_name ); ?>[taskPriorityText]"
                                   class="regular-text" value="<?php echo esc_attr( $task_priority ); ?>"/>
                            <p class="description"
                               id="tagline-description"><?php esc_html_e( "Label for \"Priority\" field on the \"Submit to Workflow\" and \"Sign Off\" popup.", "oasisworkflow" ); ?></p>
                        </div>
                        <br class="clear">
                    </div>

					<?php apply_filters( 'owf_workflow_additional_terminology_option', $workflow_terminology_option ); ?>

					<?php submit_button(); ?>
                </div>
            </div>
        </form>
		<?php
	}

}

$ow_workflow_terminology_settings = new OW_Workflow_Terminology_Settings();