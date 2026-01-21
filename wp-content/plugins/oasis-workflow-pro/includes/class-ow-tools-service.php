<?php
/*
 * Class for Tools Services
 *
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       5.3
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
class OW_Tools_Service {

	public function __construct() {

	}

	/**
	 * Execute Import/Export For Worklfow, Team, Group and Workflow Settings
	 *
	 * @since 5.3
	 */
	public function execute_import_export() {
		if ( isset ( $_POST ['ow-export-workflow'] ) && sanitize_text_field( $_POST ["ow-export-workflow"] ) ) { // phpcs:ignore

			check_admin_referer( 'owf_export_workflows', 'owf_export_workflows' );

			// capability check
			if ( ! current_user_can( 'ow_export_import_workflow' ) ) {
				wp_die( esc_html__( 'You are not allowed to export workflows and settings' ) );
			}

			OW_Utility::instance()->logger( "OWF execute export" );

			$this->owf_exports();

			OW_Utility::instance()->logger( "OWF after export" );
		}

		if ( isset ( $_POST ['ow-import-workflow'] ) && sanitize_text_field( $_POST ["ow-import-workflow"] ) ) {

			check_admin_referer( 'owf_import_workflows', 'owf_import_workflows' );

			// capability check
			if ( ! current_user_can( 'ow_export_import_workflow' ) ) {
				wp_die( esc_html__( 'You are not allowed to import workflows and the settings' ) );
			}

			OW_Utility::instance()->logger( "OWF execute import" );

			$validation = $this->owf_imports();

			// Display Validation Message on the screen
			if ( ! empty( $validation ) ) {
				$message = '<div id="message" class="updated notice notice-success is-dismissible">';
				$message .= '<p>' . esc_html( implode( "<br>", $validation ) ) . '</p>';
				$message .= '</div>';
				echo wp_kses( $message, array(
					'div' => array(
						'id'    => array(),
						'class' => array(),
					),
					'p'   => array()
				) );
			}

			OW_Utility::instance()->logger( "OWF after import" );
		}
	}

	/**
	 * Export Workflow
	 *
	 * @since 5.3
	 */
	private function owf_exports() {
		check_admin_referer( 'owf_export_workflows', 'owf_export_workflows' );

		$export_data = array();

		// phpcs:ignore
		$selected_option = isset( $_POST['add_for_export'] ) && ! empty ( $_POST['add_for_export'] )
			? array_map( 'esc_attr', $_POST['add_for_export'] ) : ''; // phpcs:ignore

		if ( $selected_option == '' ) {
			add_action( 'admin_notices', array( $this, 'no_option_selected_notice' ) );

			return false;
		}

		foreach ( $selected_option as $option ) {
			if ( $option == "workflows" ) {
				// Fetch data for team export
				if ( is_plugin_active( 'ow-teams/ow-teams.php' ) ) {
					$export_data["teams"] = array();
					$team_data            = $this->get_team_export_data();
					if ( ! empty( $team_data ) ) {
						$export_data["teams"] = $team_data;
					}
					OW_Utility::instance()->logger( "OWF fetched team export data" );
				} // end of team

				// Fetch data for group export
				if ( is_plugin_active( 'ow-groups/ow-groups.php' ) ) {
					$export_data["groups"] = array();
					$group_data            = $this->get_group_export_data();
					if ( ! empty( $group_data ) ) {
						$export_data["groups"] = $group_data;
					}
					OW_Utility::instance()->logger( "OWF fetched group export data" );
				} // end of group

				$export_data["workflows"] = array();
				$workflow_data            = $this->get_workflow_export_data();
				if ( ! empty( $workflow_data ) ) {
					$export_data["workflows"] = $workflow_data;
					OW_Utility::instance()->logger( "OWF fetched workflow export data" );
				}

			} // end of workflow option

			if ( $option == "settings" ) {
				$settings                = $this->get_owf_settings();
				$export_data["settings"] = $settings;
				OW_Utility::instance()->logger( "OWF fetched settings" );
			}
		}

		$export_data = apply_filters( 'owf_export_data', $export_data, $selected_option );

		if ( empty( $export_data ) ) {
			add_action( 'admin_notices', array( $this, 'no_export_data_notice' ) );

			return false;
		} else {
			// set headers
			$file_name = 'owf-workflow-export-' . gmdate( 'Y-m-d' ) . '.json';

			header( "Content-Description: File Transfer" );
			header( "Content-Disposition: attachment; filename={$file_name}" );
			header( "Content-Type: application/json; charset=utf-8" );

			echo $this->owf_json_encode( $export_data ); // phpcs:ignore
			die;
		}
	}

	/**
	 * Function - Build up team export data
	 *
	 * @return array $team_data
	 * @global object $wpdb
	 * @since 5.3
	 */
	private function get_team_export_data() {
		$ow_teams_service = new OW_Teams_Service();
		$all_teams        = $ow_teams_service->get_all_teams();
		$old_team         = 0;
		$teams            = array();
		$team_data        = array();
		$team_members     = array();

		foreach ( $all_teams as $team ) {
			$team_id = $team->team_id;
			if ( $old_team !== $team_id ) {
				$teams[ $team_id ] = array(
					"name"                 => $team->name,
					"description"          => $team->description,
					"associated_workflows" => json_decode( $team->associated_workflows )
				);
			}
			if ( $old_team === 0 || $old_team === $team_id ) {
				$user_id                    = $team->user_id;
				$user_role                  = $team->role;
				$user_display_name          = OW_Utility::instance()->get_user_name( $user_id );
				$team_members[ $team_id ][] = array(
					"user_id"   => $user_id,
					"user_name" => $user_display_name,
					"role_name" => $user_role
				);
			}
			$old_team = $team_id;
		}

		// Create export data
		foreach ( $teams as $team_id => $team ) {
			$members = array();
			if ( array_key_exists( $team_id, $team_members ) ) {
				$members = $team_members[ $team_id ];
			}
			$team_data[] = array(
				"ID"                   => $team_id,
				"name"                 => $team["name"],
				"description"          => $team["description"],
				"associated_workflows" => array( "-1" ),
				"team_members"         => $members
			);
			OW_Utility::instance()->logger( "OWF exporting Team ID: " . $team_id );
		}

		return $team_data;
	}

	/**
	 * Function - Build up group export data
	 *
	 * @return array $group_data
	 * @global object $wpdb
	 * @since 5.3
	 */
	private function get_group_export_data() {
		$ow_groups_service = new OW_Groups_Service();
		$all_groups        = $ow_groups_service->get_all_groups();
		$old_group         = 0;
		$groups            = array();
		$group_data        = array();
		$group_members     = array();
		foreach ( $all_groups as $group ) {
			$group_id = $group->group_id;
			if ( $old_group !== $group_id ) {
				$groups[ $group_id ] = array(
					"name"        => $group->name,
					"description" => $group->description
				);
			}
			if ( $old_group === 0 || $old_group === $group_id ) {
				$user_id           = $group->user_id;
				$user_role         = $group->role;
				$user_display_name = OW_Utility::instance()->get_user_name( $user_id );

				$group_members[ $group_id ][] = array(
					"user_id"   => $user_id,
					"user_name" => $user_display_name,
					"role_name" => $user_role
				);
			}
			$old_group = $group_id;
		}

		// Create export data
		foreach ( $groups as $group_id => $group ) {
			$members = array();
			if ( array_key_exists( $group_id, $group_members ) ) {
				$members = $group_members[ $group_id ];
			}
			$group_data[] = array(
				"ID"            => $group_id,
				"name"          => $group["name"],
				"description"   => $group["description"],
				"group_members" => $members
			);
			OW_Utility::instance()->logger( "OWF exporting Group ID: " . $group_id );
		}

		return $group_data;
	}

	/**
	 * Function - Build up workflow export data
	 *
	 * @return array $workflow_data
	 * @global object $wpdb
	 * @since 5.3
	 */
	private function get_workflow_export_data() {
		global $wpdb;
		$workflow_data       = array();
		$ow_workflow_service = new OW_Workflow_Service();
		$all_workflows       = $ow_workflow_service->get_workflow_list();
		$selected_workflows  = array();
		foreach ( $all_workflows as $workflow ) {
			$selected_workflows[] = $workflow->ID;
		}

		$valid_workflows = $this->get_valid_workflows( $selected_workflows );

		OW_Utility::instance()->logger( "OWF valid workflows for export: " . print_r( $valid_workflows, true ) );

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
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$results = $wpdb->get_results( $wpdb->prepare( "SELECT *  FROM $steps_table WHERE `workflow_id` = '%d'",
					$workflow_id ) );

				$steps = array();
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
			OW_Utility::instance()->logger( "OWF exporting Workflow ID: " . $workflow_id );
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
	 * @since 5.3
	 */
	public function get_valid_workflows( $workflow_ids ) {
		global $wpdb;

		// sanitize the values
		$workflow_ids = array_map( 'intval', $workflow_ids );

		$int_place_holders = array_fill( 0, count( $workflow_ids ), '%d' );

		$place_holders_for_workflow_ids = implode( ",", $int_place_holders );

		// lets check if any of the selected workflows are invalid
		$sql = "SELECT ID FROM " . OW_Utility::instance()->get_workflows_table_name() . " WHERE ID IN (" .
		       $place_holders_for_workflow_ids . ") AND is_valid != '1'";

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
	 *
	 * @since 5.3
	 */
	private function get_owf_settings() {
		// Get workflow settings
		$workflow_settings = array(
			"oasiswf_activate_workflow"             => get_option( "oasiswf_activate_workflow" ),
			"oasiswf_default_due_days"              => get_option( "oasiswf_default_due_days" ),
			"oasiswf_show_wfsettings_on_post_types" => get_option( "oasiswf_show_wfsettings_on_post_types" ),
			"oasiswf_priority_setting"              => get_option( "oasiswf_priority_setting" ),
			"oasiswf_publish_date_setting"          => get_option( "oasiswf_publish_date_setting" ),
			"oasiswf_comments_setting"              => get_option( "oasiswf_comments_setting" ),
			"oasiswf_sidebar_display_setting"       => get_option( "oasiswf_sidebar_display_setting" ),
			"oasiswf_participating_roles_setting"   => get_option( "oasiswf_participating_roles_setting" ),
			"oasiswf_login_redirect_roles_setting"  => get_option( "oasiswf_login_redirect_roles_setting" ),
			"oasiswf_last_step_comment_setting"     => get_option( "oasiswf_last_step_comment_setting" ),
			"oasiswf_auto_delete_history_setting"   => get_option( "oasiswf_auto_delete_history_setting" )
		);

		$email_settings = array(
			"oasiswf_email_settings"                     => get_option( "oasiswf_email_settings" ),
			"oasiswf_reminder_days"                      => get_option( "oasiswf_reminder_days" ),
			"oasiswf_reminder_days_after"                => get_option( "oasiswf_reminder_days_after" ),
			"oasiswf_post_publish_email_settings"        => get_option( "oasiswf_post_publish_email_settings" ),
			"oasiswf_revised_post_email_settings"        => get_option( "oasiswf_revised_post_email_settings" ),
			"oasiswf_unauthorized_update_email_settings" => get_option( "oasiswf_unauthorized_update_email_settings" ),
			"oasiswf_task_claim_email_settings"          => get_option( "oasiswf_task_claim_email_settings" ),
			"oasiswf_post_submit_email_settings"         => get_option( "oasiswf_post_submit_email_settings" ),
			"oasiswf_workflow_abort_email_settings"      => get_option( "oasiswf_workflow_abort_email_settings" )
		);

		$auto_submit_settings = array(
			"oasiswf_auto_submit_settings" => get_option( "oasiswf_auto_submit_settings" )
		);

		$document_revision_settings = array(
			"oasiswf_doc_revision_title_prefix"            => get_option( "oasiswf_doc_revision_title_prefix" ),
			"oasiswf_doc_revision_title_suffix"            => get_option( "oasiswf_doc_revision_title_suffix" ),
			"oasiswf_copy_children_on_revision"            => get_option( "oasiswf_copy_children_on_revision" ),
			"oasiswf_delete_revision_on_copy"              => get_option( "oasiswf_delete_revision_on_copy" ),
			"oasiswf_activate_revision_process"            => get_option( "oasiswf_activate_revision_process" ),
			"oasiswf_hide_compare_button"                  => get_option( "oasiswf_hide_compare_button" ),
			"oasiswf_disable_workflow_4_revision"  		   => get_option( "oasiswf_disable_workflow_4_revision" ),
			"oasiswf_revise_post_make_revision_overlay"    => get_option( "oasiswf_revise_post_make_revision_overlay" ),
			"oasiswf_preserve_revision_of_revised_article" => get_option( "oasiswf_preserve_revision_of_revised_article" )
		);

		$terminology_settings = array(
			"oasiswf_custom_workflow_terminology" => get_option( "oasiswf_custom_workflow_terminology" )
		);

		$owf_settings = array(
			"workflow_settings"          => $workflow_settings,
			"email_settings"             => $email_settings,
			"auto_submit_settings"       => $auto_submit_settings,
			"document_revision_settings" => $document_revision_settings,
			"terminology_settings"       => $terminology_settings
		);

		$owf_settings = apply_filters( 'owf_import_settings_items', $owf_settings );

		return $owf_settings;
	}

	/**
	 * This function will return pretty JSON for all PHP versions
	 *
	 * @param $json
	 *
	 * @return false|string
	 */
	public function owf_json_encode( $json ) {

		// PHP at least 5.4
		if ( version_compare( PHP_VERSION, '5.4.0', '>=' ) ) {

			return wp_json_encode( $json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );

		}

		// PHP less than 5.4
		$json = wp_json_encode( $json );

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
	 *
	 * @return array|bool
	 */
	private function owf_imports() {
		global $wpdb;

		// validate
		if ( empty( $_FILES['import-workflow-filename']['name'] ) ) {
			add_action( 'admin_notices', array( $this, 'no_file_select_notice' ) );

			return false;
		}

		// vars
		$file = isset( $_FILES['import-workflow-filename'] ) ? $_FILES['import-workflow-filename'] : ''; // phpcs:ignore

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

		$validations = $this->import_process($json);

		return $validations;
	}

	/**
	 * Process import
	 *
	 * @param object $json
	 * @return array
	 */
	public function import_process($json) {
		$validation          = array();
		$team_validation     = array();
		$group_validation    = array();
		$workflow_validation = array();
		$settings_validation = array();

		foreach ( $json as $key => $values ) {

			// import team if plugin is active
			if ( $key == "teams" && is_plugin_active( 'ow-teams/ow-teams.php' ) ) {
				// Get new inserted team ids to replace it with old ids
				$team_data       = $this->import_teams( $values, $validation );
				$team_validation = $team_data["validation"];
			}

			// import group if plugin is active
			if ( $key == "groups" && is_plugin_active( 'ow-groups/ow-groups.php' ) ) {
				// Get new inserted group ids to replace it with old ids during workflow import
				$group_data       = $this->import_groups( $values, $validation );
				$group_validation = $group_data["validation"];
			}

			// import workflow
			if ( $key == "workflows" ) {

				$replace_team_ids  = array();
				$replace_group_ids = array();

				if ( isset( $team_data["new_team_data"] ) && ! empty( $team_data["new_team_data"] ) ) {
					$replace_team_ids = $team_data["new_team_data"];
				}

				if ( isset( $group_data["new_group_data"] ) && ! empty( $group_data["new_group_data"] ) ) {
					$replace_group_ids = $group_data["new_group_data"];
				}

				// Get new inserted workflow ids to replace it with old ids during workflow settings import
				$workflow_data       = $this->import_workflows( $values, $replace_team_ids, $replace_group_ids,
					$validation );
				$workflow_validation = $workflow_data["validation"];
			}

			// import settings
			if ( $key == "settings" ) {
				$replace_workflow_ids = array();
				if ( isset( $workflow_data["new_workflow_data"] ) && ! empty( $workflow_data["new_workflow_data"] ) ) {
					$replace_workflow_ids = $workflow_data["new_workflow_data"];
				}
				$settings_data       = $this->import_settings( $values, $replace_workflow_ids, $validation );
				$settings_validation = $settings_data["validation"];
			}
		}

		$validations = array_merge( $team_validation, $group_validation, $workflow_validation, $settings_validation );

		return $validations;
	}

	/**
	 * Import Team
	 *
	 * @param $team_data
	 * @param $validation
	 *
	 * @return array
	 */
	private function import_teams( $team_data, $validation ) {
		global $wpdb;
		// get the table names
		$teams_table        = OW_Teams_Utility::instance()->get_teams_table_name();
		$team_members_table = OW_Teams_Utility::instance()->get_teams_members_table_name();

		$available_user_roles = $this->get_available_user_roles();
		$new_team_data        = [];

		foreach ( $team_data as $team ) {
			$data = array(
				'name'                 => sanitize_text_field( $team['name'] ),
				'description'          => sanitize_text_field( $team['description'] ),
				'associated_workflows' => wp_json_encode( $team['associated_workflows'] ),
				'create_datetime'      => current_time( 'mysql' )
			);

			$team_id = OW_Utility::instance()->insert_to_table( $teams_table, $data );

			$validation[] = esc_html__( "Imported Team - ", "oasisworkflow" ) . $team['name'];

			// Save old ids to replace it during workflow import
			$old_team_id                   = $team['ID'];
			$new_team_data[ $old_team_id ] = $team_id;

			if ( $team_id && ! empty( $team["team_members"] ) ) {
				foreach ( $team["team_members"] as $member ) {

					$user_id   = $member["user_id"];
					$user_name = $member["user_name"];
					$user_role = $member["role_name"];

					// Fetch target system user name
					$target_system_user_name = OW_Utility::instance()->get_user_name( $user_id );

					// Check discrepancies
					if ( in_array( $user_role, $available_user_roles ) ) {
						// compare user name by user Id in the target system
						if ( $target_system_user_name !== $user_name ) {
							$validation[] = '<span class="indented-label required-color" >' .
							                esc_html__( "Error - ", "oasisworkflow" ) . '"' . $user_name . '"' .
							                esc_html__( " not available in the target system.", "oasisworkflow" ) .
							                '</span>';
							continue;
						}
					} else {
						$validation[] = '<span class="indented-label required-color" >' .
						                esc_html__( "Error - ", "oasisworkflow" ) . '"' . $user_role . '"' .
						                esc_html__( " not available in the target system.", "oasisworkflow" ) .
						                '</span>';
						continue;
					}

					$team_member_data = array(
						'team_id'         => $team_id,
						'user_id'         => $member["user_id"],
						'role_name'       => $member["role_name"],
						'create_datetime' => current_time( 'mysql' )
					);
					OW_Utility::instance()->insert_to_table( $team_members_table, $team_member_data );
				}
			}
			OW_Utility::instance()->logger( "OWF imported old Team ID: " . $old_team_id . " to new Team ID: " .
			                                $team_id );
		}

		return array( "new_team_data" => $new_team_data, "validation" => $validation );
	}

	private function get_available_user_roles() {
		$user_roles     = array();
		$editable_roles = get_editable_roles();
		foreach ( $editable_roles as $role => $details ) {
			$user_roles[] = $role;
		}

		return $user_roles;
	}

	/**
	 * Import group
	 *
	 * @param $group_data
	 * @param $validation
	 *
	 * @return array
	 */
	public function import_groups( $group_data, $validation ) {
		global $wpdb;
		// get the table names
		$groups_table        = OW_Groups_Plugin_Utility::instance()->get_groups_table_name();
		$group_members_table = OW_Groups_Plugin_Utility::instance()->get_groups_members_table_name();

		$available_user_roles = $this->get_available_user_roles();
		$new_group_data       = '';
		foreach ( $group_data as $group ) {
			$data = array(
				'name'            => sanitize_text_field( $group['name'] ),
				'description'     => sanitize_text_field( $group['description'] ),
				'create_datetime' => current_time( 'mysql' )
			);

			$group_id = OW_Utility::instance()->insert_to_table( $groups_table, $data );

			$validation[] = esc_html__( "Imported Group - ", "oasisworkflow" ) . $group['name'];

			// Save old ids to replace it during workflow import
			$old_group_id = $group['ID'];

			$new_group_data[ $old_group_id ] = $group_id;

			if ( $group_id && ! empty( $group["group_members"] ) ) {

				foreach ( $group["group_members"] as $member ) {

					$user_id   = $member["user_id"];
					$user_name = $member["user_name"];
					$user_role = $member["role_name"];

					// Fetch target system user name
					$target_system_user_name = OW_Utility::instance()->get_user_name( $user_id );

					// Check discrepancies
					if ( in_array( $user_role, $available_user_roles ) ) {
						// compare user name by user Id in the target system
						if ( $target_system_user_name !== $user_name ) {
							$validation[] = '<span class="indented-label required-color" >' .
							                esc_html__( "Error - ", "oasisworkflow" ) . '"' . $user_name . '"' .
							                esc_html__( " not available in the target system.", "oasisworkflow" ) .
							                '</span>';
							continue;
						}
					} else {
						$validation[] = '<span class="indented-label required-color" >' .
						                esc_html__( "Error - ", "oasisworkflow" ) . '"' . $user_role . '"' .
						                esc_html__( " not available in the target system.", "oasisworkflow" ) .
						                '</span>';
						continue;
					}

					$group_member_data = array(
						'group_id'        => $group_id,
						'user_id'         => $member["user_id"],
						'role_name'       => $member["role_name"],
						'create_datetime' => current_time( 'mysql' )
					);
					OW_Utility::instance()->insert_to_table( $group_members_table, $group_member_data );
				}
			}
			OW_Utility::instance()->logger( "OWF imported old Group ID: " . $old_group_id . " to new Group ID: " .
			                                $group_id );
		}

		return array( "new_team_data" => $new_group_data, "validation" => $validation );
	}

	/**
	 * Import workflow
	 *
	 * @param $workflow_data
	 * @param $replace_team_ids
	 * @param $replace_group_ids
	 * @param $validation
	 *
	 * @return array
	 */
	private function import_workflows( $workflow_data, $replace_team_ids, $replace_group_ids, $validation ) {
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
			$wpdb->insert( $workflow_table, $data, $format );
			$workflow_id = $wpdb->insert_id;

			// Save old ids to replace it during workflow settings import
			$old_workflow_id = $workflow['ID'];

			$new_workflow_data[ $old_workflow_id ] = $workflow_id;

			// we need to update the wf_info on the workflow with the new step_ids (fc_dbid)
			$wf_info_decoded = json_decode( $workflow['wf_info'] );
			$wf_steps        = $wf_info_decoded->steps;

			// now let's insert the steps data
			$steps_data = $workflow['steps']; // all step info

			$step_format = array(
				'%s',
				'%s',
				'%d',
				'%s',
				'%s'
			);

			foreach ( $steps_data as $step ) {

				// Code to replace group ids with the new ids
				$stepinfo            = json_decode( $step['step_info'] );
				$step_assignee_group = "";
				if ( isset( $stepinfo->task_assignee->groups ) ) :
					$step_assignee_group = $stepinfo->task_assignee->groups;
				endif;

				if ( ! empty( $step_assignee_group ) ) {
					foreach ( $step_assignee_group as $key => $value ) {
						if ( array_key_exists( $value, $replace_group_ids ) ) {
							$step_assignee_group[ $key ] = $replace_group_ids[ $value ];
						}
					}
					$stepinfo->task_assignee->groups = $step_assignee_group;

					$step['step_info'] = wp_json_encode( $stepinfo );
				}

				// import workflow steps
				$steps = array(
					'step_info'       => $step['step_info'],
					'process_info'    => $step['process_info'],
					'workflow_id'     => $workflow_id, // use the newly inserted workflow_id
					'create_datetime' => $step['create_datetime'],
					'update_datetime' => $step['update_datetime']
				);
				$wpdb->insert( $workflow_steps_table, $steps, $step_format );
				$step_id = $wpdb->insert_id; // get the newly created step id

				// We need to update the wf_info (which represents graphical info in the workflow table)
				// with the updated step_id

				// get the step name
				$step_info      = json_decode( $step['step_info'] );
				$step_name_temp = $step_info->step_name;

				foreach ( $wf_steps as $k => $v ) {
					if ( $step_name_temp ==
					     $v->fc_label ) { // match the step name with the label name in the graphical info
						$v->fc_dbid = $step_id; // update the fc_dbid with the newly inserted step id
					}
				}
			}

			// update the workflow table with the modified wf_info
			$wpdb->update( $workflow_table,
				array(
					'wf_info' => wp_json_encode( $wf_info_decoded )
				),
				array( 'ID' => $workflow_id )
			);

			$validation[] = esc_html__( "Imported Workflow - ", "oasisworkflow" ) . $workflow['name'];

			OW_Utility::instance()->logger( "OWF imported old Workflow ID: " . $old_workflow_id .
			                                " to new Workflow ID: " . $workflow_id );
		}

		return array( "new_workflow_data" => $new_workflow_data, "validation" => $validation );
	}

	/**
	 * Import Workflow Various Settings
	 *
	 * @param $settings_data
	 * @param $replace_workflow_ids
	 * @param $validation
	 *
	 * @return array
	 */
	public function import_settings( $settings_data, $replace_workflow_ids, $validation ) {
		// Sanitize incoming data
		$replace_workflow_ids = array_map( 'intval', $replace_workflow_ids );
		$validation           = array_map( 'esc_attr', $validation );

		foreach ( $settings_data as $settings_key => $settings ) {
			foreach ( $settings as $key => $setting ) {
				// if value is false don't save
				if ( $setting == false ) {
					continue;
				}

				// Santitizing
				if ( is_array( $setting ) ) {
					$setting = OW_Utility::instance()->sanitize_array( $setting );
				} else {
					$setting = sanitize_text_field( $setting );
				}

				if ( $key == "oasiswf_participating_roles_setting" ) {
					// We need to pass only roles for saving the settings
					foreach ( $setting as $role => $display_name ) {
						$roles[] = $role;
					}
					update_option( $key, $roles );
				} else if ( $key == "oasiswf_auto_submit_settings" ) {
					// Code to replace workflow id for auto submit assigned workflows
					$assigned_workflows         = array();
					$replaced_assigned_workflow = array();
					if ( isset( $setting['auto_submit_workflows'] ) ) {
						$assigned_workflows = $setting['auto_submit_workflows'];
					}
					if ( ! empty( $assigned_workflows ) ) {
						foreach ( $assigned_workflows as $workflow_id => $keywords ) {
							if ( array_key_exists( $workflow_id, $replace_workflow_ids ) ) {
								$new_workflow_id                                = $replace_workflow_ids[ $workflow_id ];
								$replaced_assigned_workflow[ $new_workflow_id ] = $keywords;
							} else {
								$replaced_assigned_workflow[ $workflow_id ] = $keywords;
							}
						}
						$setting['auto_submit_workflows'] = $replaced_assigned_workflow;
						update_option( $key, $setting );
					}
				} else {
					update_option( $key, $setting );
				}
			}
			$dispaly_settings_name = str_replace( "_", " ", $settings_key );
			$validation[]          = esc_html__( "Imported ", "oasisworkflow" ) . $dispaly_settings_name;
		}

		return array( "validation" => $validation );
	}

	/**
	 * Build System Information String
	 *
	 * @return string
	 * @global object $wpdb
	 * @since 5.3
	 */
	public function get_owf_system_info() {
		global $wpdb;

		// Get theme info
		$theme_data   = wp_get_theme();
		$theme        = $theme_data->Name . ' ' . $theme_data->Version;
		$parent_theme = $theme_data->Template;
		if ( ! empty( $parent_theme ) ) {
			$parent_theme_data = wp_get_theme( $parent_theme );
			$parent_theme      = $parent_theme_data->Name . ' ' . $parent_theme_data->Version;
		}

		$server_name = isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( $_SERVER['SERVER_NAME'] ) : "";
		$host        = 'DBH: ' . DB_HOST . ', SRV: ' . $server_name;

		$return = '### Begin System Info ###' . "\n\n";

		// Start with the basics...
		$return .= '-- Site Info' . "\n\n";
		$return .= 'Site URL:                 ' . site_url() . "\n";
		$return .= 'Home URL:                 ' . home_url() . "\n";
		$return .= 'Multisite:                ' . ( is_multisite() ? 'Yes' : 'No' ) . "\n";

		// Can we determine the site's host?
		if ( $host ) {
			$return .= "\n" . '-- Hosting Provider' . "\n\n";
			$return .= 'Host:                     ' . $host . "\n";
		}

		$locale = get_locale();

		// WordPress configuration
		$return .= "\n" . '-- WordPress Configuration' . "\n\n";
		$return .= 'Version:                  ' . get_bloginfo( 'version' ) . "\n";
		$return .= 'Language:                 ' . ( ! empty( $locale ) ? $locale : 'en_US' ) . "\n";
		$return .= 'Permalink Structure:      ' .
		           ( get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : 'Default' ) . "\n";
		$return .= 'Active Theme:             ' . $theme . "\n";
		if ( $parent_theme !== $theme ) {
			$return .= 'Parent Theme:             ' . $parent_theme . "\n";
		}
		$return .= 'Show On Front:            ' . get_option( 'show_on_front' ) . "\n";

		// Only show page specs if frontpage is set to 'page'
		if ( get_option( 'show_on_front' ) == 'page' ) {
			$front_page_id = get_option( 'page_on_front' );
			$blog_page_id  = get_option( 'page_for_posts' );

			$return .= 'Page On Front:            ' .
			           ( $front_page_id != 0 ? get_the_title( $front_page_id ) . ' (#' . $front_page_id . ')'
				           : 'Unset' ) . "\n";
			$return .= 'Page For Posts:           ' .
			           ( $blog_page_id != 0 ? get_the_title( $blog_page_id ) . ' (#' . $blog_page_id . ')' : 'Unset' ) .
			           "\n";
		}

		$return .= 'ABSPATH:                  ' . ABSPATH . "\n";
		$return .= 'WP_DEBUG:                 ' .
		           ( defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' : 'Disabled' : 'Not set' ) . "\n";
		$return .= 'Memory Limit:             ' . WP_MEMORY_LIMIT . "\n";
		$return .= 'Registered Post Stati:    ' . implode( ', ', get_post_stati() ) . "\n";

		// Get plugins that have an update
		$updates = get_plugin_updates();

		// Must-use plugins
		// NOTE: MU plugins can't show updates!
		$muplugins = get_mu_plugins();
		if ( count( $muplugins ) > 0 ) {
			$return .= "\n" . '-- Must-Use Plugins' . "\n\n";

			foreach ( $muplugins as $plugin => $plugin_data ) {
				$return .= $plugin_data['Name'] . ': ' . $plugin_data['Version'] . "\n";
			}
		}

		// WordPress active plugins
		$return .= "\n" . '-- WordPress Active Plugins' . "\n\n";

		$plugins        = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );

		foreach ( $plugins as $plugin_path => $plugin ) {
			if ( ! in_array( $plugin_path, $active_plugins ) ) {
				continue;
			}

			$update = ( array_key_exists( $plugin_path, $updates ) ) ?
				' (needs update - ' . $updates[ $plugin_path ]->update->new_version . ')' : '';
			$return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
		}

		// WordPress inactive plugins
		$return .= "\n" . '-- WordPress Inactive Plugins' . "\n\n";

		foreach ( $plugins as $plugin_path => $plugin ) {
			if ( in_array( $plugin_path, $active_plugins ) ) {
				continue;
			}

			$update = ( array_key_exists( $plugin_path, $updates ) ) ?
				' (needs update - ' . $updates[ $plugin_path ]->update->new_version . ')' : '';
			$return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
		}

		if ( is_multisite() ) {
			// WordPress Multisite active plugins
			$return .= "\n" . '-- Network Active Plugins' . "\n\n";

			$plugins        = wp_get_active_network_plugins();
			$active_plugins = get_site_option( 'active_sitewide_plugins', array() );

			foreach ( $plugins as $plugin_path ) {
				$plugin_base = plugin_basename( $plugin_path );

				if ( ! array_key_exists( $plugin_base, $active_plugins ) ) {
					continue;
				}

				$update = ( array_key_exists( $plugin_path, $updates ) ) ?
					' (needs update - ' . $updates[ $plugin_path ]->update->new_version . ')' : '';
				$plugin = get_plugin_data( $plugin_path );
				$return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
			}
		}

		// Server configuration (really just versioning)
		$server_software = isset( $_SERVER['SERVER_SOFTWARE'] )
			? sanitize_text_field( $_SERVER['SERVER_SOFTWARE'] )
			: "";
		$return          .= "\n" . '-- Webserver Configuration' . "\n\n";
		$return          .= 'PHP Version:              ' . PHP_VERSION . "\n";
		$return          .= 'MySQL Version:            ' . $wpdb->db_version() . "\n";
		$return          .= 'Webserver Info:           ' . $server_software . "\n";

		// PHP configs... now we're getting to the important stuff
		$return .= "\n" . '-- PHP Configuration' . "\n\n";
		$return .= 'Memory Limit:             ' . ini_get( 'memory_limit' ) . "\n";
		$return .= 'Upload Max Size:          ' . ini_get( 'upload_max_filesize' ) . "\n";
		$return .= 'Post Max Size:            ' . ini_get( 'post_max_size' ) . "\n";
		$return .= 'Upload Max Filesize:      ' . ini_get( 'upload_max_filesize' ) . "\n";
		$return .= 'Time Limit:               ' . ini_get( 'max_execution_time' ) . "\n";
		$return .= 'Max Input Vars:           ' . ini_get( 'max_input_vars' ) . "\n";
		$return .= 'Display Errors:           ' .
		           ( ini_get( 'display_errors' ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A' ) . "\n";

		// PHP extensions and such
		$return .= "\n" . '-- PHP Extensions' . "\n\n";

		$return .= 'cURL:             ' . ( function_exists( 'curl_init' ) ? 'Supported' : 'Not Supported' ) .
		           "\n";
		$return .= 'fsockopen:        ' . ( function_exists( 'fsockopen' ) ? 'Supported' : 'Not Supported' ) .
		           "\n";
		$return .= 'SOAP Client:      ' . ( class_exists( 'SoapClient' ) ? 'Installed' : 'Not Installed' ) .
		           "\n";
		$return .= 'Suhosin:          ' . ( extension_loaded( 'suhosin' ) ? 'Installed' : 'Not Installed' ) .
		           "\n";

		// Session stuff
		$return .= "\n" . '-- Session Configuration' . "\n\n";
		$return .= 'Session:                  ' . ( isset( $_SESSION ) ? 'Enabled' : 'Disabled' ) . "\n";
		// The rest of this is only relevant is session is enabled
		if ( isset( $_SESSION ) ) {
			$return .= 'Session Name:             ' . esc_html( ini_get( 'session.name' ) ) . "\n";
			$return .= 'Cookie Path:              ' . esc_html( ini_get( 'session.cookie_path' ) ) . "\n";
			$return .= 'Save Path:                ' . esc_html( ini_get( 'session.save_path' ) ) . "\n";
			$return .= 'Use Cookies:              ' . ( ini_get( 'session.use_cookies' ) ? 'On' : 'Off' ) . "\n";
			$return .= 'Use Only Cookies:         ' . ( ini_get( 'session.use_only_cookies' ) ? 'On' : 'Off' ) . "\n";
		}

		$return .= "\n" . '### End System Info ###';

		return $return;

	}

	public function no_option_selected_notice() {
		$class   = 'notice notice-error';
		$message = esc_html__( 'Please select at least one option to export.', 'oasisworkflow' );

		$this->display_message( $class, $message );
	}

	private function display_message( $class, $message ) {
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}

	public function no_export_data_notice() {
		$class   = 'notice notice-error';
		$message = esc_html__( 'There is no data to export.', 'oasisworkflow' );

		$this->display_message( $class, $message );
	}

	public function no_file_select_notice() {
		$class   = 'notice notice-error';
		$message = esc_html__( 'No file selected.', 'oasisworkflow' );

		$this->display_message( $class, $message );

	}

	public function error_uploading_file_notice() {
		$class   = 'notice notice-error';
		$message = esc_html__( 'Error uploading file. Please try again.', 'oasisworkflow' );

		$this->display_message( $class, $message );
	}

	public function incorrect_file_type_notice() {
		$class   = 'notice notice-error';
		$message = esc_html__( 'Incorrect file type.', 'oasisworkflow' );

		$this->display_message( $class, $message );
	}

	public function import_file_empty_notice() {
		$class   = 'notice notice-error';
		$message = esc_html__( 'Import file is empty.', 'oasisworkflow' );

		$this->display_message( $class, $message );
	}

	public function data_imported_successfully_notice() {
		$class   = 'notice notice-info';
		$message = esc_html__( 'Data imported successfully.', 'oasisworkflow' );

		$this->display_message( $class, $message );
	}

	/**
	 * Function - API to fetch all workflow settings
	 *
	 * @since 6.0
	 */
	public function api_get_plugin_settings( $data ) {
		if ( ! wp_verify_nonce( $data->get_header( 'x_wp_nonce' ), 'wp_rest' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'oasisworkflow' ) );
		}

		if ( ! current_user_can( 'ow_submit_to_workflow' ) && ! current_user_can( 'ow_sign_off_step' ) ) {
			// Replaced wp_die to WP_Error to prevent 500 internal server error.
			return new WP_Error( 'owf_rest_cannot_submit',
				esc_html__( 'You are not allowed to fetch workflow settings.', 'oasisworkflow' ),
				array( 'status' => '403' ) );
		}

		return $this->get_owf_settings();
	}

	/**
	 * Function - API to check editorial comment add-on activation
	 *
	 * @since 6.7
	 */
	public function api_check_is_active_editorial_comments( $data ) {
		if ( ! wp_verify_nonce( $data->get_header( 'x_wp_nonce' ), 'wp_rest' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'oasisworkflow' ) );
		}

		if ( ! current_user_can( 'ow_submit_to_workflow' ) && ! current_user_can( 'ow_sign_off_step' ) ) {
			// Replaced wp_die to WP_Error to prevent 500 internal server error.
			return new WP_Error( 'owf_rest_editorial_comments',
				esc_html__( 'You are not allowed to view editorial comments.', 'oasisworkflow' ),
				array( 'status' => '403' ) );
		}

		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		if ( is_plugin_active( 'ow-editorial-comments/ow-editorial-comments.php' ) ||
		     is_plugin_active_for_network( 'ow-editorial-comments/ow-editorial-comments.php' ) == 1 ) {
			return true;
		}

		return false;
	}

}

$ow_tools_service = new OW_Tools_Service();
add_action( 'admin_init', array( $ow_tools_service, 'execute_import_export' ) );

