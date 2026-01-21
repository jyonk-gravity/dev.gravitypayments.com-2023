<?php
/*
 * Settings class for Workflow Terminology settings
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

/*
 * OW_Workflow_Terminology_Settings Class
 *
 * @since 2.0
 */

class OW_Workflow_Terminology_Settings {

	/**
	 *
	 * @var string workflow terminology option name
	 */
	protected $ow_workflow_terminology_option_name = 'oasiswf_custom_workflow_terminology';

	// default values
	protected $ow_workflow_terminology_option_default_value
		= array(
			'submitToWorkflowText'  => "",
			'signOffText'           => "",
			'continueToSubmitText'  => "",
			'continueToSignoffText' => "",
			'assignActorsText'      => "",
			'dueDateText'           => "",
			'publishDateText'       => "",
			'abortWorkflowText'     => "",
			'workflowHistoryText'   => "",
			'makeRevisionText'      => "",
			'taskPriorityText'      => "",
			'duplicatePostText'     => ""
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
		register_setting( 'ow-settings-workflow-terminology', $this->ow_workflow_terminology_option_name,
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
			$workflow_terminology_settings['submitToWorkflowText']
				= sanitize_text_field( $input['submitToWorkflowText'] );
		} else {
			$workflow_terminology_settings['submitToWorkflowText']
				= $this->ow_workflow_terminology_option_default_value['submitToWorkflowText'];
		}

		if ( array_key_exists( 'signOffText', $input ) ) {
			$workflow_terminology_settings['signOffText'] = sanitize_text_field( $input['signOffText'] );
		} else {
			$workflow_terminology_settings['signOffText']
				= $this->ow_workflow_terminology_option_default_value['signOffText'];
		}

		if ( array_key_exists( 'continueToSubmitText', $input ) ) {
			$workflow_terminology_settings['continueToSubmitText']
				= sanitize_text_field( $input['continueToSubmitText'] );
		} else {
			$workflow_terminology_settings['continueToSubmitText']
				= $this->ow_workflow_terminology_option_default_value['continueToSubmitText'];
		}

		if ( array_key_exists( 'continueToSignoffText', $input ) ) {
			$workflow_terminology_settings['continueToSignoffText']
				= sanitize_text_field( $input['continueToSignoffText'] );
		} else {
			$workflow_terminology_settings['continueToSignoffText']
				= $this->ow_workflow_terminology_option_default_value['continueToSignoffText'];
		}

		if ( array_key_exists( 'assignActorsText', $input ) ) {
			$workflow_terminology_settings['assignActorsText'] = sanitize_text_field( $input['assignActorsText'] );
		} else {
			$workflow_terminology_settings['assignActorsText']
				= $this->ow_workflow_terminology_option_default_value['assignActorsText'];
		}

		if ( array_key_exists( 'dueDateText', $input ) ) {
			$workflow_terminology_settings['dueDateText'] = sanitize_text_field( $input['dueDateText'] );
		} else {
			$workflow_terminology_settings['dueDateText']
				= $this->ow_workflow_terminology_option_default_value['dueDateText'];
		}

		if ( array_key_exists( 'publishDateText', $input ) ) {
			$workflow_terminology_settings['publishDateText'] = sanitize_text_field( $input['publishDateText'] );
		} else {
			$workflow_terminology_settings['publishDateText']
				= $this->ow_workflow_terminology_option_default_value['publishDateText'];
		}

		if ( array_key_exists( 'abortWorkflowText', $input ) ) {
			$workflow_terminology_settings['abortWorkflowText'] = sanitize_text_field( $input['abortWorkflowText'] );
		} else {
			$workflow_terminology_settings['abortWorkflowText']
				= $this->ow_workflow_terminology_option_default_value['abortWorkflowText'];
		}

		if ( array_key_exists( 'workflowHistoryText', $input ) ) {
			$workflow_terminology_settings['workflowHistoryText']
				= sanitize_text_field( $input['workflowHistoryText'] );
		} else {
			$workflow_terminology_settings['workflowHistoryText']
				= $this->ow_workflow_terminology_option_default_value['workflowHistoryText'];
		}

		if ( array_key_exists( 'makeRevisionText', $input ) ) {
			$workflow_terminology_settings['makeRevisionText'] = sanitize_text_field( $input['makeRevisionText'] );
		} else {
			$workflow_terminology_settings['makeRevisionText']
				= $this->ow_workflow_terminology_option_default_value['makeRevisionText'];
		}

		if ( array_key_exists( 'taskPriorityText', $input ) ) {
			$workflow_terminology_settings['taskPriorityText'] = sanitize_text_field( $input['taskPriorityText'] );
		} else {
			$workflow_terminology_settings['taskPriorityText']
				= $this->ow_workflow_terminology_option_default_value['taskPriorityText'];
		}

		if ( array_key_exists( 'duplicatePostText', $input ) ) {
			$workflow_terminology_settings['duplicatePostText'] = sanitize_text_field( $input['duplicatePostText'] );
		} else {
			$workflow_terminology_settings['duplicatePostText']
				= $this->ow_workflow_terminology_option_default_value['duplicatePostText'];
		}

		return $workflow_terminology_settings;
	}

	/**
	 * generate the page
	 *
	 * @since 2.0
	 */
	public function add_settings_page() {
		$workflow_terminology_option = get_option( $this->ow_workflow_terminology_option_name );
		$submit_to_workflow          = ! empty( $workflow_terminology_option['submitToWorkflowText'] )
			? $workflow_terminology_option['submitToWorkflowText'] : esc_html__( 'Submit to Workflow', 'oasisworkflow' );
		$sign_off                    = ! empty( $workflow_terminology_option['signOffText'] )
			? $workflow_terminology_option['signOffText'] : esc_html__( 'Sign Off', 'oasisworkflow' );
		$continue_to_submit          = ! empty( $workflow_terminology_option['continueToSubmitText'] )
			? $workflow_terminology_option['continueToSubmitText'] : esc_html__( 'Continue to Submit', 'oasisworkflow' );
		$continue_to_signoff         = ! empty( $workflow_terminology_option['continueToSignoffText'] )
			? $workflow_terminology_option['continueToSignoffText'] : esc_html__( 'Continue to Sign off', 'oasisworkflow' );
		$assign_actors               = ! empty( $workflow_terminology_option['assignActorsText'] )
			? $workflow_terminology_option['assignActorsText'] : esc_html__( 'Assign Actor(s)', 'oasisworkflow' );
		$due_date                    = ! empty( $workflow_terminology_option['dueDateText'] )
			? $workflow_terminology_option['dueDateText'] : esc_html__( 'Due Date', 'oasisworkflow' );
		$publish_date                = ! empty( $workflow_terminology_option['publishDateText'] )
			? $workflow_terminology_option['publishDateText'] : esc_html__( 'Publish Date', 'oasisworkflow' );
		$abort_workflow              = ! empty( $workflow_terminology_option['abortWorkflowText'] )
			? $workflow_terminology_option['abortWorkflowText'] : esc_html__( 'Abort Workflow', 'oasisworkflow' );
		$workflow_history            = ! empty( $workflow_terminology_option['workflowHistoryText'] )
			? $workflow_terminology_option['workflowHistoryText'] : esc_html__( 'Workflow History' );
		$make_revision               = ! empty( $workflow_terminology_option['makeRevisionText'] )
			? $workflow_terminology_option['makeRevisionText'] : esc_html__( 'Make Revision', 'oasisworkflow' );
		$task_priority               = ! empty( $workflow_terminology_option['taskPriorityText'] )
			? $workflow_terminology_option['taskPriorityText'] : esc_html__( 'Priority', 'oasisworkflow' );
		$duplicate_post              = ! empty( $workflow_terminology_option['duplicatePostText'] )
			? $workflow_terminology_option['duplicatePostText'] : esc_html__( 'Duplicate Post', 'oasisworkflow' );
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
                            <label for="submitToWorkflowText"><?php echo esc_html__( "Submit to Workflow", "oasisworkflow" ) .
							                                             esc_html__( " Label", "oasisworkflow" ) ?></label>
                        </div>
                        <div class="half-width left">
                            <input type="text" class="regular-text"
                                   name="<?php echo esc_attr($this->ow_workflow_terminology_option_name); ?>[submitToWorkflowText]"
                                   value="<?php echo esc_attr( $submit_to_workflow ); ?>"/>
                            <p class="description"
                               id="tagline-description"><?php echo esc_html__( "Label for \"Submit to Workflow\" button/link.",
									"oasisworkflow" ); ?></p>
                        </div>
                        <br class="clear">
                    </div>
                    <div class="select-info">
                        <div class="half-width left">
                            <label for="continueToSubmitText"><?php echo esc_html__( "Continue to Submit", "oasisworkflow" ) .
							                                             esc_html__( " Label", "oasisworkflow" ) ?></label>
                        </div>
                        <div class="half-width left">
                            <input type="text" class="regular-text"
                                   name="<?php echo esc_attr($this->ow_workflow_terminology_option_name); ?>[continueToSubmitText]"
                                   value="<?php echo esc_attr( $continue_to_submit ); ?>"/>
                            <p class="description"
                               id="tagline-description"><?php echo esc_html__( "Label for \"Continue to Submit\" button/link.",
									"oasisworkflow" ); ?></p>
                        </div>
                        <br class="clear">
                    </div>
                    <div class="select-info">
                        <div class="half-width left">
                            <label for="signOffText"><?php echo esc_html__( "Sign Off", "oasisworkflow" ) .
							                                    esc_html__( " Label", "oasisworkflow" ) ?></label>
                        </div>
                        <div class="half-width left">
                            <input type="text" class="regular-text"
                                   name="<?php echo esc_attr($this->ow_workflow_terminology_option_name); ?>[signOffText]"
                                   value="<?php echo esc_attr( $sign_off ); ?>"/>
                            <p class="description"
                               id="tagline-description"><?php echo esc_html__( "Label for \"Sign Off\" button/link.",
									"oasisworkflow" ); ?></p>
                        </div>
                        <br class="clear">
                    </div>
                    <div class="select-info">
                        <div class="half-width left">
                            <label for="continueToSignoffText"><?php echo esc_html__( "Continue to Sign off",
				                        "oasisworkflow" ) . esc_html__( " Label", "oasisworkflow" ) ?>
                            </label>
                        </div>
                        <div class="half-width left">
                            <input type="text" class="regular-text"
                                   name="<?php echo esc_attr($this->ow_workflow_terminology_option_name); ?>[continueToSignoffText]"
                                   value="<?php echo esc_attr( $continue_to_signoff ); ?>"/>
                            <p class="description"
                               id="tagline-description"><?php echo esc_html__( "Label for \"Continue to Sign off\" button/link.",
									"oasisworkflow" ); ?></p>
                        </div>
                        <br class="clear">
                    </div>
                    <div class="select-info">
                        <div class="half-width left">
                            <label for="assignActorsText"><?php echo esc_html__( "Assign Actor(s)", "oasisworkflow" ) .
							                                         esc_html__( " Label", "oasisworkflow" ) ?></label>
                        </div>
                        <div class="half-width left">
                            <input type="text" class="regular-text"
                                   name="<?php echo esc_attr($this->ow_workflow_terminology_option_name); ?>[assignActorsText]"
                                   value="<?php echo esc_attr( $assign_actors ); ?>"/>
                            <p class="description"
                               id="tagline-description"><?php echo esc_html__( "Label for \"Assign Actor(s)\" field.",
									"oasisworkflow" ); ?></p>
                        </div>
                        <br class="clear">
                    </div>
                    <div class="select-info">
                        <div class="half-width left">
                            <label for="dueDateText"><?php echo esc_html__( "Due Date", "oasisworkflow" ) .
							                                    esc_html__( " Label", "oasisworkflow" ) ?></label>
                        </div>
                        <div class="half-width left">
                            <input type="text" class="regular-text"
                                   name="<?php echo esc_attr($this->ow_workflow_terminology_option_name); ?>[dueDateText]"
                                   value="<?php echo esc_attr( $due_date ); ?>"/>
                            <p class="description"
                               id="tagline-description"><?php echo esc_html__( "Label for \"Due Date\" field.",
									"oasisworkflow" ); ?></p>
                        </div>
                        <br class="clear">
                    </div>
                    <div class="select-info">
                        <div class="half-width left">
                            <label for="publishDateText"><?php echo esc_html__( "Publish Date", "oasisworkflow" ) .
							                                        esc_html__( " Label", "oasisworkflow" ) ?></label>
                        </div>
                        <div class="half-width left">
                            <input type="text" class="regular-text"
                                   name="<?php echo esc_attr($this->ow_workflow_terminology_option_name); ?>[publishDateText]"
                                   value="<?php echo esc_attr( $publish_date ); ?>"/>
                            <p class="description"
                               id="tagline-description"><?php echo esc_html__( "Label for \"Publish Date\" field on the \"Submit to Workflow\" popup.",
									"oasisworkflow" ); ?></p>
                        </div>
                        <br class="clear">
                    </div>
                    <div class="select-info">
                        <div class="half-width left">
                            <label for="abortWorkflowText"><?php echo esc_html__( "Abort Workflow", "oasisworkflow" ) .
							                                          esc_html__( " Label", "oasisworkflow" ) ?></label>
                        </div>
                        <div class="half-width left">
                            <input type="text" class="regular-text"
                                   name="<?php echo esc_attr($this->ow_workflow_terminology_option_name); ?>[abortWorkflowText]"
                                   value="<?php echo esc_attr( $abort_workflow ); ?>"/>
                            <p class="description"
                               id="tagline-description"><?php echo esc_html__( "Label for \"Abort Workflow\" button/link.",
									"oasisworkflow" ); ?></p>
                        </div>
                        <br class="clear">
                    </div>
                    <div class="select-info">
                        <div class="half-width left">
                            <label for="workflowHistoryText"><?php echo esc_html__( "Workflow History", "oasisworkflow" ) .
							                                            esc_html__( " Label", "oasisworkflow" ) ?></label>
                        </div>
                        <div class="half-width left">
                            <input type="text" class="regular-text"
                                   name="<?php echo esc_attr($this->ow_workflow_terminology_option_name); ?>[workflowHistoryText]"
                                   value="<?php echo esc_attr( $workflow_history ); ?>"/>
                            <p class="description"
                               id="tagline-description"><?php echo esc_html__( "Label for \"Workflow History\" menu.",
									"oasisworkflow" ); ?></p>
                        </div>
                        <br class="clear">
                    </div>
                    <div class="select-info">
                        <div class="half-width left">
                            <label for="makeRevisionText"><?php echo esc_html__( "Make Revision", "oasisworkflow" ) .
							                                         esc_html__( " Label", "oasisworkflow" ) ?></label>
                        </div>
                        <div class="half-width left">
                            <input type="text"
                                   name="<?php echo esc_attr($this->ow_workflow_terminology_option_name); ?>[makeRevisionText]"
                                   value="<?php echo esc_attr( $make_revision ); ?>"/>
                            <p class="description"
                               id="tagline-description"><?php echo esc_html__( "Label for \"Make Revision\" button/link.",
									"oasisworkflow" ); ?></p>
                        </div>
                        <br class="clear">
                    </div>
                    <div class="select-info">
                        <div class="half-width left">
                            <label for="addingTaskPriority"><?php echo esc_html__( "Priority", "oasisworkflow" ) .
							                                           esc_html__( " Label", "oasisworkflow" ) ?></label>
                        </div>
                        <div class="half-width left">
                            <input type="text"
                                   name="<?php echo esc_attr($this->ow_workflow_terminology_option_name); ?>[taskPriorityText]"
                                   value="<?php echo esc_attr( $task_priority ); ?>"/>
                            <p class="description"
                               id="tagline-description"><?php echo esc_html__( "Label for \"Priority\" field on the \"Submit to Workflow\" and \"Sign Off\" popup.",
									"oasisworkflow" ); ?></p>
                        </div>
                        <br class="clear">
                    </div>

                    <div class="select-info">
                        <div class="half-width left">
                            <label for="duplicatePost"><?php echo esc_html__( "Duplicate Post", "oasisworkflow" ) .
							                                      esc_html__( " Label", "oasisworkflow" ) ?></label>
                        </div>
                        <div class="half-width left">
                            <input type="text"
                                   name="<?php echo esc_attr($this->ow_workflow_terminology_option_name); ?>[duplicatePostText]"
                                   value="<?php echo esc_attr( $duplicate_post ); ?>"/>
                            <p class="description"
                               id="tagline-description"><?php echo esc_html__( "Label for \"Duplicate Post\" link.",
									"oasisworkflow" ); ?></p>
                        </div>
                        <br class="clear">
                    </div>

					<?php submit_button(); ?>
                </div>
            </div>
        </form>
		<?php
	}

}

$ow_workflow_terminology_settings = new OW_Workflow_Terminology_Settings();
?>