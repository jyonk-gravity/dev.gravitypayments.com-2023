<?php

/*
 * Oasis Workflow feedback class
 *
 * @copyright   Copyright (c) 2020, Nugget Solutions, Inc
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       4.8
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * OW_Feedback Class
 *
 * @since 4.8
 */
class OW_Feedback {
	public function __construct() {
		add_action( 'wp_ajax_submit_deactivation_feedback', array( $this, 'submit_deactivation_feedback' ) );
		add_action( 'admin_footer', array( $this, 'deactivate_oasis_workflow' ) );
	}

	/**
	 * AJAX function - submit feedback
	 * @since 4.8
	 */
	public function submit_deactivation_feedback() {

		// nonce check
		check_ajax_referer( 'owf_feedback_ajax_nonce', 'security' );

		$selected_feedback = 'no_reason_provided';
		$feedback_thoughts = '';
		$feedback_email    = get_option( "admin_email" );
		if ( empty( $feedback_email ) ) {
			$user           = wp_get_current_user();
			$feedback_email = $user->user_email;
		}

		// sanitize incoming data
		if ( isset ( $_POST ['feedback'] ) ) {
			$selected_feedback = sanitize_text_field( $_POST['feedback'] );
		}

		if ( isset ( $_POST ['thoughts'] ) ) {
			$feedback_thoughts = sanitize_textarea_field( $_POST['thoughts'] );
		}

		if ( isset ( $_POST ['email'] ) ) {
			$feedback_email = sanitize_text_field( $_POST['email'] );
		}

		$url = 'https://hooks.zapier.com/hooks/catch/780193/o9lcjq9/';

		//Input Data to be created
		$data = array(
			'input_1' => $selected_feedback,
			'input_2' => $feedback_thoughts,
			'input_3' => $feedback_email
		);

		//Send request
		$response = wp_remote_post( $url,
			array(
				'method'  => 'POST',
				'body'    => json_encode( $data ), // phpcs:ignore
				'headers' => array( 'Content-type' => 'application/json' )
			)
		);

		// Check the response code.
		if ( wp_remote_retrieve_response_code( $response ) == 200 ) {
			wp_send_json_success();
		}

		if ( wp_remote_retrieve_response_code( $response ) != 200 || ( empty( wp_remote_retrieve_body( $response ) ) ) ) {
			// If not a 200, HTTP request failed.
			OW_Utility::instance()->logger( "There was an error attempting to access the API." );
			wp_send_json_error();
		}
	}

	/**
	 * Create feedback modal popup
	 * @global global $pagenow
	 * @since 4.8
	 */
	public function deactivate_oasis_workflow() {
		global $pagenow;
		if ( 'plugins.php' != $pagenow ) {
			return;
		}
		$reasons = $this->get_deactivation_reasons();
		?>
       <div class="info-setting-feedback owf-hidden" id="owf_deactivate_feedback">
          <div class="feedback-dialog-title">
             <strong><?php esc_html_e( "If you have a moment, please let us know why you are deactivating:", "oasisworkflow" ); ?></strong>
          </div>
          <div class="owf-reason-required"><?php esc_html_e('Please select a reason for deactivation.', 'oasisworkflow'); ?></div>
          <div id="owf-feedback-contents">
             <ul>
				 <?php foreach ( $reasons as $reason ) { ?>
                    <li><label><input type="radio" name="selected-reason" class="selected-reason"
                                      value="<?php echo esc_attr( $reason['id'] ); ?>"><?php echo esc_html( $reason['text'] ); ?>
                       </label></li>
				 <?php } ?>
             </ul>
          </div>
          <div class="list-section-heading">
             <label><?php esc_html_e( "If you don’t mind sharing, what could we do to improve?", "oasisworkflow" ); ?></label>
          </div>
          <textarea placeholder="<?php esc_attr_e( 'Enter your thoughts here..', 'oasisworkflow' ) ?>" cols="60"
                    rows="2" class="feedback-thoughts"></textarea>

          <div class="ow-email-section">
             <label><?php esc_html_e( "Email", "oasisworkflow" ); ?></label>
			  <?php
			  $email = get_option( "admin_email" );
			  if ( empty( $email ) ) {
				  $user  = wp_get_current_user();
				  $email = $user->user_email;
			  }
			  ?>
             <input type="email" class="ow-feedback-email"
                    placeholder="<?php esc_attr_e( 'your email address', 'oasisworkflow' ); ?>" required
                    value="<?php echo esc_attr( $email ); ?>"/>
          </div>

          <input type="hidden" name="owf_feedback_ajax_nonce" id="owf_feedback_ajax_nonce"
                 value="<?php echo esc_attr( wp_create_nonce( 'owf_feedback_ajax_nonce' ) ); ?>"/>
          <div class="select-info left changed-data-set full-width btn-submit-feedback-group">
             <span>&nbsp;</span>
             <input type="button" id="owf-feedback-save" class="button-primary"
                    value="<?php esc_attr_e( 'Submit and Deactivate', 'oasisworkflow' ); ?>"/>
             <a href="#" id="owf-deactivate"><?php esc_html_e( "Skip and Deactivate", "oasisworkflow" ); ?></a>
          </div>
       </div>
		<?php
		// enqueue required js and css files
		wp_enqueue_style( 'owf-oasis-workflow-css', OASISWF_URL . 'css/pages/oasis-workflow.css', false, OASISWF_VERSION, 'all' );
		wp_enqueue_style( 'owf-modal-css', OASISWF_URL . 'css/lib/modal/simple-modal.css', false, OASISWF_VERSION, 'all' );
		wp_enqueue_script( 'jquery-simplemodal', OASISWF_URL . 'js/lib/modal/jquery.simplemodal.js', '', '1.4.6', true );
		wp_enqueue_script( 'owf_deactivate_feedback', OASISWF_URL . 'js/pages/ow-feedback.js', array( 'jquery' ), OASISWF_VERSION, true );
	}

	private function get_deactivation_reasons() {
		$reasons = array(
			array(
				'id'   => 'difficult-to-create-workflows',
				'text' => esc_html__( "It was difficult to create new workflows.", "oasisworkflow" )
			),
			array(
				'id'   => 'difficult-to-use-workflow',
				'text' => esc_html__( "It was difficult to use the workflows for review process.", "oasisworkflow" )
			),
			array(
				'id'   => 'not-using-enough',
				'text' => esc_html__( "Wasn't using the plugin frequently enough.", "oasisworkflow" )
			),
			array(
				'id'   => 'have-pro-version',
				'text' => esc_html__( "I have Oasis Workflow Pro.", "oasisworkflow" )
			),
			array(
				'id'   => 'switching-plugin',
				'text' => esc_html__( "I’m switching to another plugin.", "oasisworkflow" )
			),
			array(
				'id'   => 'temporary-deactivation',
				'text' => esc_html__( "It's a temporary deactivation.", "oasisworkflow" )
			)
		);

		return $reasons;
	}

}

// construct an instance so that the actions get loaded
$ow_feedback = new OW_Feedback();
