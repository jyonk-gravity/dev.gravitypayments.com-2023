<?php
/*
 * class for Tools Services
 *
 * @copyright   Copyright (c) 2017, Nugget Solutions, Inc
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       4.0
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * OW_Tools_Service Class
 *
 * @since 4.0
 */
class OW_Tools_Service {

	public function __construct() {

		// only add_action for AJAX actions
		// empty
	}

	/**
	 * Execute Import/Export For Worklfow and Workflow Settings
	 * @since 4.5
	 */
	public function execute_import_export() {
		// phpcs:ignore
		if ( isset ( $_POST ['ow-export-workflow'] ) && sanitize_text_field( $_POST ["ow-export-workflow"] ) ) {

			check_admin_referer( 'owf_export_workflows', 'owf_export_workflows' );

			// capability check
			if ( ! current_user_can( 'ow_export_import_workflow' ) ) {
				wp_die( esc_html__( 'You are not allowed to export workflows and the settings' ) );
			}

			$this->owf_exports();
		}

		// phpcs:ignore
		if ( isset ( $_POST ['ow-import-workflow'] ) && sanitize_text_field( $_POST ["ow-import-workflow"] ) ) {

			check_admin_referer( 'owf_import_workflows', 'owf_import_workflows' );

			// capability check
			if ( ! current_user_can( 'ow_export_import_workflow' ) ) {
				wp_die( esc_html__( 'You are not allowed to import workflows and the settings' ) );
			}

			$validation = $this->owf_imports();

			// Display Validation Message on the screen
			if ( ! empty( $validation ) ) {
				$message = '<div id="message" class="updated notice notice-success is-dismissible">';
				$message .= '<p>' . implode( "<br>", $validation ) . '</p>'; // phpcs:ignore
				$message .= '</div>';
				echo $message; // phpcs:ignore
			}
		}
	}

	/**
	 * Export Workflow
	 * @since 4.5
	 */
	private function owf_exports() {
		global $wpdb;

		$export_data = array();

		// phpcs:ignore
		$selected_option = isset( $_POST['add_for_export'] ) && ! empty ( $_POST['add_for_export'] ) ? array_map( 'esc_attr', $_POST['add_for_export'] ) : '';

		if ( $selected_option == '' ) {
			add_action( 'admin_notices', array( $this, 'no_option_selected_notice' ) );

			return false;
		}

		foreach ( $selected_option as $option ) {
			if ( $option == "workflows" ) {
				$export_data["workflows"] = array();
				$workflow_data            = $this->get_workflow_export_data();
				if ( ! empty( $workflow_data ) ) {
					$export_data["workflows"] = $workflow_data;
				}
			} // end of workflow option

			if ( $option == "settings" ) {
				$settings                = $this->get_owf_settings();
				$export_data["settings"] = $settings;
			}
		}

		if ( empty( $export_data ) ) {
			add_action( 'admin_notices', array( $this, 'no_export_data_notice' ) );

			return false;
		} else {
			// set headers
			$file_name = 'owf-workflow-export-' . date( 'Y-m-d' ) . '.json'; // phpcs:ignore

			header( "Content-Description: File Transfer" );
			header( "Content-Disposition: attachment; filename={$file_name}" );
			header( "Content-Type: application/json; charset=utf-8" );

			echo $this->owf_json_encode( $export_data ); // phpcs:ignore
			die;
		}
	}

	/**
	 * Function - Build up workflow export data
	 * @return array $workflow_data
	 * @global object $wpdb
	 * @since 4.5
	 */
	private function get_workflow_export_data() {
		global $wpdb;
		$workflow_data       = array();
		$ow_workflow_service = new OW_Workflow_Service();
		$all_workflows       = $ow_workflow_service->get_workflow_list();
		foreach ( $all_workflows as $workflow ) {
			$selected_workflows[] = $workflow->ID;
		}

		$valid_workflows = $this->get_valid_workflows( $selected_workflows );

		if ( ! empty( $valid_workflows ) ) {

			$workflows = $ow_workflow_service->get_multiple_workflows_by_id( $valid_workflows );

			foreach ( $workflows as $workflow ) {
				$workflow_id        = $workflow->ID;
				$workflow_name      = $workflow->name;
				$description        = $workflow->description;
				$version            = $workflow->version;
				$parent_id          = $workflow->parent_id;
				$start_date         = $workflow->start_date;
				$end_date           = $workflow->end_date;
				$wf_info            = $workflow->wf_info;
				$is_auto_submit     = $workflow->is_auto_submit;
				$auto_submit_info   = $workflow->auto_submit_info;
				$is_valid           = $workflow->is_valid;
				$create_datetime    = current_time( 'mysql' );
				$update_datetime    = current_time( 'mysql' );
				$wf_additional_info = $workflow->wf_additional_info;

				$steps_table = OW_Utility::instance()->get_workflow_steps_table_name();
				// phpcs:ignore
				$results = $wpdb->get_results( $wpdb->prepare( "SELECT *  FROM $steps_table WHERE `workflow_id` = '%d'", $workflow_id ) );
				$steps   = array();
				if ( $results ) {
					foreach ( $results as $step ) {
						$step_data                     = array();
						$step_data ['step_info']       = $step->step_info;
						$step_data ['process_info']    = $step->process_info;
						$step_data ['workflow_id']     = $step->workflow_id;
						$step_data ['create_datetime'] = current_time( 'mysql' );
						$step_data ['update_datetime'] = current_time( 'mysql' );
						$steps []                      = $step_data;
					}
				}

				// create export data
				$workflow_data [] = array(
					'ID'                 => $workflow_id,
					'name'               => $workflow_name,
					'description'        => $description,
					'wf_info'            => $wf_info,
					'version'            => $version,
					'parent_id'          => $parent_id,
					'start_date'         => $start_date,
					'end_date'           => $end_date,
					'is_auto_submit'     => $is_auto_submit,
					'auto_submit_info'   => $auto_submit_info,
					'is_valid'           => $is_valid,
					'create_datetime'    => $create_datetime,
					'update_datetime'    => $update_datetime,
					'wf_additional_info' => $wf_additional_info,
					'steps'              => $steps
				);
			}
		}

		return $workflow_data;
	}

	/**
	 * Validate selected workflows for the following
	 * 1. Workflows should be valid
	 * 2. Only one version of the workflow should be allowed to export
	 *
	 * @param array $workflow_ids
	 *
	 * @return array $workflow_ids
	 * @global type $wpdb
	 * @since 4.5
	 */
	public function get_valid_workflows( $workflow_ids ) {
		global $wpdb;

		// sanitize the values
		$workflow_ids = array_map( 'intval', $workflow_ids );

		$int_place_holders              = array_fill( 0, count( $workflow_ids ), '%d' );
		$place_holders_for_workflow_ids = implode( ",", $int_place_holders );

		// lets check if any of the selected workflows are invalid
		$sql = "SELECT ID FROM " . OW_Utility::instance()->get_workflows_table_name() . " WHERE ID IN (" . $place_holders_for_workflow_ids . ") AND is_valid != '1'";

		// phpcs:ignore
		$invalid_workflows = $wpdb->get_results( $wpdb->prepare( $sql, $workflow_ids ) );

		if ( $invalid_workflows ) { // looks like we found invalid workflows
			foreach ( $invalid_workflows as $invalid_id ) {
				if ( ( $key = array_search( $invalid_id->ID, $workflow_ids ) ) !== false ) {
					unset( $workflow_ids[ $key ] );
				}
			}
		}

		return $workflow_ids;
	}

	/**
	 * Function - Build oasis workflow settings export data
	 * @since 4.0
	 */
	private function get_owf_settings() {
		// Get workflow settings
		$workflow_settings = array(
			"oasiswf_activate_workflow"             => get_option( "oasiswf_activate_workflow" ),
			"oasiswf_default_due_days"              => get_option( "oasiswf_default_due_days" ),
			"oasiswf_show_wfsettings_on_post_types" => get_option( "oasiswf_show_wfsettings_on_post_types" ),
			"oasiswf_priority_setting"              => get_option( "oasiswf_priority_setting" ),
			"oasiswf_publish_date_setting"          => get_option( "oasiswf_publish_date_setting" )
		);

		$email_settings = array(
			"oasiswf_email_settings"      => get_option( "oasiswf_email_settings" ),
			"oasiswf_reminder_days"       => get_option( "oasiswf_reminder_days" ),
			"oasiswf_reminder_days_after" => get_option( "oasiswf_reminder_days_after" )
		);


		$terminology_settings = array(
			"oasiswf_custom_workflow_terminology" => get_option( "oasiswf_custom_workflow_terminology" )
		);

		$owf_settings = array(
			"workflow_settings"    => $workflow_settings,
			"email_settings"       => $email_settings,
			"terminology_settings" => $terminology_settings
		);

		return $owf_settings;
	}

	/**
	 *  This function will return pretty JSON for all PHP versions
	 *
	 * @param    $json (array)
	 *
	 * @return    (string)
	 * @since    4.5
	 */
	public function owf_json_encode( $json ) {

		// PHP at least 5.4
		if ( version_compare( PHP_VERSION, '5.4.0', '>=' ) ) {
			// phpcs:ignore
			return json_encode( $json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
		}

		// PHP less than 5.4
		$json = json_encode( $json ); // phpcs:ignore

		$result      = '';
		$pos         = 0;
		$strLen      = strlen( $json );
		$indentStr   = "    ";
		$newLine     = "\n";
		$prevChar    = '';
		$outOfQuotes = true;

		for ( $i = 0; $i <= $strLen; $i ++ ) {

			// Grab the next character in the string.
			$char = substr( $json, $i, 1 );

			// Are we inside a quoted string?
			if ( $char == '"' && $prevChar != '\\' ) {
				$outOfQuotes = ! $outOfQuotes;

				// If this character is the end of an element,
				// output a new line and indent the next line.
			} else if ( ( $char == '}' || $char == ']' ) && $outOfQuotes ) {
				$result .= $newLine;
				$pos --;
				for ( $j = 0; $j < $pos; $j ++ ) {
					$result .= $indentStr;
				}
			}

			// Add the character to the result string.
			$result .= $char;

			// If this character is ':' adda space after it
			if ( $char == ':' && $outOfQuotes ) {
				$result .= ' ';
			}

			// If the last character was the beginning of an element,
			// output a new line and indent the next line.
			if ( ( $char == ',' || $char == '{' || $char == '[' ) && $outOfQuotes ) {
				$result .= $newLine;
				if ( $char == '{' || $char == '[' ) {
					$pos ++;
				}

				for ( $j = 0; $j < $pos; $j ++ ) {
					$result .= $indentStr;
				}
			}

			$prevChar = $char;
		}

		// return
		return $result;
	}

	/**
	 * Import the JSON export file
	 * @global object $wpdb
	 * @since 4.5
	 */
	private function owf_imports() {
		global $wpdb;

		$validation          = array();
		$workflow_validation = array();
		$settings_validation = array();

		// validate
		if ( empty( $_FILES['import-workflow-filename']['name'] ) ) {
			add_action( 'admin_notices', array( $this, 'no_file_select_notice' ) );

			return false;
		}

		// vars
		$file = $_FILES['import-workflow-filename'];         // phpcs:ignore

		// validate error
		if ( $file['error'] ) {
			add_action( 'admin_notices', array( $this, 'error_uploading_file_notice' ) );

			return false;

		}

		// validate type
		if ( pathinfo( $file['name'], PATHINFO_EXTENSION ) !== 'json' ) {
			add_action( 'admin_notices', array( $this, 'incorrect_file_type_notice' ) );

			return false;
		}

		// read file
		$json = file_get_contents( $file['tmp_name'] );

		// decode json
		$json = json_decode( $json, true );

		// validate json
		if ( empty( $json ) ) {
			add_action( 'admin_notices', array( $this, 'import_file_empty_notice' ) );

			return false;
		}

		foreach ( $json as $key => $values ) {

			// import workflow
			if ( $key == "workflows" ) {
				$replace_workflow_ids = array();

				// Get new inserted workflow ids to replace it with old ids during workflow settings import
				$workflow_data       = $this->import_workflows( $values, $validation );
				$workflow_validation = $workflow_data["validation"];
			}

			// import settings
			if ( $key == "settings" ) {
				$settings_data       = $this->import_settings( $values, $validation );
				$settings_validation = $settings_data["validation"];
			}
		}

		$validations = array_merge( $workflow_validation, $settings_validation );

		return $validations;
	}

	/**
	 * Import workflow
	 *
	 * @param array $workflow_data
	 *
	 * @global object $wpdb
	 * @since 4.5
	 */
	private function import_workflows( $workflow_data, $validation ) {
		global $wpdb;
		// get the table names
		$new_workflow_data    = array();
		$workflow_table       = OW_Utility::instance()->get_workflows_table_name();
		$workflow_steps_table = OW_Utility::instance()->get_workflow_steps_table_name();

		foreach ( $workflow_data as $workflow ) {
			$data = array(
				'name'               => sanitize_text_field( $workflow['name'] ),
				// workflow name
				'description'        => sanitize_text_field( $workflow['description'] ),
				// workflow description
				'wf_info'            => sanitize_text_field( $workflow['wf_info'] ),
				// workflow graphic info
				'version'            => 1,
				// $workflow['version'], reset the workflow version to 1
				'parent_id'          => 0,
				// $workflow['parent_id'], since we are resetting workflow version so parent-id = 0
				'start_date'         => sanitize_text_field( $workflow['start_date'] ),
				//start date of workflow
				'end_date'           => sanitize_text_field( $workflow['end_date'] ),
				// end date of workflow
				'is_auto_submit'     => (int) $workflow['is_auto_submit'],
				//auto submit flag
				'auto_submit_info'   => sanitize_text_field( $workflow['auto_submit_info'] ),
				// auto submit info
				'is_valid'           => (int) $workflow['is_valid'],
				// is valid flag
				'create_datetime'    => sanitize_text_field( $workflow['create_datetime'] ),
				// create date time
				'update_datetime'    => sanitize_text_field( $workflow['update_datetime'] ),
				// update date time
				'wf_additional_info' => sanitize_text_field( $workflow['wf_additional_info'] )
				//additional info, like post types, user roles
			);

			$format = array(
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%s',
				'%s',
				'%d',
				'%s',
				'%d',
				'%s',
				'%s',
				'%s'
			);
			// phpcs:ignore
			$wpdb->insert( $workflow_table, $data, $format );
			$workflow_id = $wpdb->insert_id;

			// Save old ids to replace it during workflow settings import
			$old_workflow_id                       = $workflow['ID'];
			$new_workflow_data[ $old_workflow_id ] = $workflow_id;

			// we need to update the wf_info on the workflow with the new step_ids (fc_dbid)
			$wf_info_decoded = json_decode( $workflow['wf_info'] );
			$wf_steps        = $wf_info_decoded->steps;

			// now let's insert the steps data
			$steps_data = $workflow['steps']; // all step info

			$count       = count( $steps_data );
			$step_format = array(
				'%s',
				'%s',
				'%d',
				'%s',
				'%s'
			);

			foreach ( $steps_data as $step ) {
				// import workflow steps
				$steps = array(
					'step_info'       => $step['step_info'],
					'process_info'    => $step['process_info'],
					'workflow_id'     => $workflow_id, // use the newly inserted workflow_id
					'create_datetime' => $step['create_datetime'],
					'update_datetime' => $step['update_datetime']
				);
				// phpcs:ignore
				$wpdb->insert( $workflow_steps_table, $steps, $step_format );
				$step_id = $wpdb->insert_id; // get the newly created step id

				// We need to update the wf_info (which represents graphical info in the workflow table)
				// with the updated step_id

				// get the step name
				$step_info      = json_decode( $step['step_info'] );
				$step_name_temp = $step_info->step_name;

				foreach ( $wf_steps as $k => $v ) {
					if ( $step_name_temp == $v->fc_label ) { // match the step name with the label name in the graphical info
						$v->fc_dbid = $step_id; // update the fc_dbid with the newly inserted step id
					}
				}
			}

			// update the workflow table with the modified wf_info
			// phpcs:ignore
			$result = $wpdb->update( $workflow_table,
				array(
					'wf_info' => json_encode( $wf_info_decoded )
				),
				array( 'ID' => $workflow_id )
			);

			$validation[] = __( "Imported Workflow - ", "oasisworkflow" ) . $workflow['name'];
		}

		return array( "new_workflow_data" => $new_workflow_data, "validation" => $validation );
	}

	/**
	 * Import Workflow Various Settings
	 *
	 * @param array $settings_data
	 *
	 * @since 4.5
	 */
	public function import_settings( $settings_data, $validation ) {
		// Sanitize incoming data
		$validation = array_map( 'esc_attr', $validation );

		foreach ( $settings_data as $settings_key => $settings ) {
			foreach ( $settings as $key => $setting ) {
				// if value is false don't save
				if ( $setting == false ) {
					continue;
				}
				if ( is_array( $setting ) ) {
					$setting = array_map( 'esc_attr', $setting );
				} else {
					$setting = sanitize_text_field( $setting );
				}
				update_option( $key, $setting );
			}
			$display_settings_name = str_replace( "_", " ", $settings_key );
			$validation[]          = __( "Imported ", "oasisworkflow" ) . $display_settings_name;
		}

		return array( "validation" => $validation );
	}

	/**
	 * Function - API to fetch all workflow settings
	 * @since 4.0
	 */
	public function api_get_plugin_settings( $criteria ) {
		if ( ! wp_verify_nonce( $criteria->get_header( 'x_wp_nonce' ), 'wp_rest' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'oasisworkflow' ) );
		}
		if ( ! current_user_can( 'ow_submit_to_workflow' ) && ! current_user_can( 'ow_sign_off_step' ) ) {
			wp_die( esc_html__( 'You are not allowed to fetch workflow settings.', 'oasisworkflow' ) );
		}

		return $this->get_owf_settings();
	}

	public function no_option_selected_notice() {
		$export_error = OW_Utility::instance()->admin_notice( array(
			'type'    => 'error',
			'message' => esc_html__( 'Please select at least one option to export.', 'oasisworkflow' )
		) );
		echo $export_error; // phpcs:ignore
	}

	public function no_export_data_notice() {
		$export_error = OW_Utility::instance()->admin_notice( array(
			'type'    => 'error',
			'message' => esc_html__( 'There is no data to export.', 'oasisworkflow' )
		) );
		echo $export_error; // phpcs:ignore
	}

	public function no_file_select_notice() {
		$export_error = OW_Utility::instance()->admin_notice( array(
			'type'    => 'error',
			'message' => esc_html__( 'No file selected.', 'oasisworkflow' )
		) );
		echo $export_error; // phpcs:ignore
	}

	public function error_uploading_file_notice() {
		$export_error = OW_Utility::instance()->admin_notice( array(
			'type'    => 'error',
			'message' => esc_html__( 'Error uploading file. Please try again.', 'oasisworkflow' )
		) );
		echo $export_error; // phpcs:ignore
	}

	public function incorrect_file_type_notice() {
		$export_error = OW_Utility::instance()->admin_notice( array(
			'type'    => 'error',
			'message' => esc_html__( 'Incorrect file type', 'oasisworkflow' )
		) );
		echo $export_error; // phpcs:ignore
	}

	public function import_file_empty_notice() {
		$export_error = OW_Utility::instance()->admin_notice( array(
			'type'    => 'error',
			'message' => esc_html__( 'Import file empty', 'oasisworkflow' )
		) );
		echo $export_error; // phpcs:ignore
	}

	public function data_imported_successfully_notice() {
		$export_error = OW_Utility::instance()->admin_notice( array(
			'type'    => 'update',
			'message' => esc_html__( 'Data imported successfully', 'oasisworkflow' )
		) );
		echo $export_error; // phpcs:ignore
	}

}

$ow_tools_service = new OW_Tools_Service();
add_action( 'admin_init', array( $ow_tools_service, 'execute_import_export' ) );