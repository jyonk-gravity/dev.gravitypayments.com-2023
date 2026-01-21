<?php
$wf_id       = $workflow = false;
$wf_editable = true;
// phpcs:ignore
if ( isset( $_GET['wf_id'] ) && sanitize_text_field( $_GET['wf_id'] ) ) {
	$wf_id            = (int) $_GET['wf_id'];
	$workflow_service = new OW_Workflow_Service();
	$workflow         = $workflow_service->get_workflow_by_id( $wf_id );
	$wf_editable      = $workflow_service->is_workflow_editable( $wf_id ); // check if editable.
}

$title            = $workflow_description = $start_date = $end_date = $workflow_info = ''; // phpcs:ignore
$wf_for_new_posts = $wf_for_revised_posts = 1;
$wf_for_roles     = $wf_for_post_types = array();
if ( $workflow ) {
	$title                = $workflow->name; // phpcs:ignore
	$workflow_description = $workflow->description;
	$start_date           = OW_Utility::instance()->format_date_for_display_and_edit( $workflow->start_date );
	$end_date             = OW_Utility::instance()->format_date_for_display_and_edit( $workflow->end_date );
	$workflow_info        = wp_slash( $workflow->wf_info );
	$additional_info      = @unserialize( $workflow->wf_additional_info ); // phpcs:ignore
	if ( is_array( $additional_info ) && ! empty( $additional_info ) ) {
		$wf_for_new_posts     = $additional_info['wf_for_new_posts'];
		$wf_for_revised_posts = $additional_info['wf_for_revised_posts'];
		if ( array_key_exists( 'wf_for_roles', $additional_info ) ) {
			$wf_for_roles = $additional_info['wf_for_roles'];
		}
		if ( array_key_exists( 'wf_for_post_types', $additional_info ) ) {
			$wf_for_post_types = $additional_info['wf_for_post_types'];
		}
	}
}

// phpcs:ignore
echo "<script type='text/javascript'>
		   wf_structure_data = '{$workflow_info}';
		   wfeditable = '{$wf_editable}' ;
	   </script>";
?>

<div class="wrap">
    <div id="workflow-edit-icon" class="icon32"><br></div>
	<?php if ( $workflow ) { ?>
        <h2>
            <label id="page_top_lbl">
				<?php echo esc_html( $title ) . " (" . esc_html( $workflow->version ) . ")"; ?>
            </label>
        </h2>
	<?php } ?>
    <form id="wf-form" method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=oasiswf-admin' ) ); ?>">
        <div>
            <div id="validation_error_message" class="updated ow-error-message owf-hidden"></div>
            <span class="description">
                <?php add_thickbox(); ?>
				<?php esc_html_e( 'If you want to view how-to videos on Oasis Workflow ', "oasisworkflow" ); ?>
                <a href="<?php echo esc_url( 'https://www.youtube.com/results?search_query=oasis+workflow+wordpress+plugin' ); ?>"
                   target="_blank">
                    <?php esc_html_e( 'click here.', "oasisworkflow" ); ?>
                </a>
            </span>
            <br class="clear"/>
        </div>
        <div class="fc_action">
            <div id="workflow-info-area">
                <div class="postbox-container" id="process-info-div">
                    <div class="postbox">
                        <div class="handlediv" title="<?php esc_attr_e( 'Click to toggle' ); ?>"><br></div>
                        <h3 style="padding:7px;">
                            <span class="process-lbl">
                                <?php echo esc_html__( 'Processes', "oasisworkflow" ); ?>
                                <a href="#"
                                   title="<?php esc_attr_e( 'Drag and Drop the processes into the Workflow Design Canvas to create new workflow steps.', "oasisworkflow" );
                                   ?>" class="tooltip">
                                    <span title="">
                                        <img src="<?php echo esc_url( OASISWF_URL ) . '/img/help.png'; ?>"
                                             class="help-icon"/></span>
                                </a>
                            </span>
                        </h3>
                        <div class="move-div">
							<?php
							if ( $wf_editable ) {
								echo '<ul id="wfsortable">';
								$fw_process = get_site_option( 'oasiswf_process' ); // list all the processes/steps on the side
								foreach ( $fw_process as $k => $v ) {
									echo "<li class='widget'>
												<div class='widget-wf-process'>" . wp_kses_post( $k ) . "</div>
											 </li>";
								}
								echo '</ul>';
							} else { // steps cannot be added or deleted
								echo "<ul class='wfeditable'><li class='widget wfmessage'><p>";
								echo esc_html__( "Processes are not available, since there are items (post/pages) in the workflow.&nbsp;&nbsp;&nbsp;If you want to edit the workflow,&nbsp;&nbsp; please ", "oasisworkflow" ) . "&nbsp;
											<a href='#' id='save_as_link'>" . esc_html__( "save it as a new version", "oasisworkflow" );
								echo "</a></p></li><ul>";
							}
							?>
                        </div>
                    </div>
                </div>
                <div class="postbox-container">
                    <div class="postbox">
                        <div class="handlediv" title="<?php esc_attr_e( 'Click to toggle' ); ?>"><br></div>
                        <h3 style="padding:7px;">
                            <span class="workflow-lbl"><?php esc_html_e( "Workflow Info", "oasisworkflow" ); ?></span>
                        </h3>
                        <div class="move-div workflow-define-div">
                            <table>
                                <tr>
                                    <td>
                                        <label>
                                            <span class="space bold-label">
                                                <?php esc_html_e( "Title : ", "oasisworkflow" ); ?>
                                            </span>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <input type="text"
                                               id="define-workflow-title"
                                               name="define-workflow-title"
                                               style="width:100%;"
                                               value="<?php echo esc_attr( $title ); ?>"/>
                                    </td>
                                </tr>
                                <tr height="20px;">
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="vertical-align: top;">
                                        <label>
                                            <span class="space bold-label">
                                                <?php esc_html_e( "Description : ", "oasisworkflow" ); ?>
                                            </span>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <textarea id="define-workflow-description"
                                                  name="define-workflow-description"
                                                  class="define-workflow-textarea"
                                                  cols="20"
                                                  rows="10"><?php echo esc_textarea( $workflow_description ); ?></textarea>
                                    </td>
                                </tr>
                            </table>
                            <div class="div-line"></div>
                            <table>
                                <tr>
                                    <td>
                                        <label>
                                            <span class="space bold-label">
                                                <?php esc_html_e( 'Start Date :', 'oasisworkflow' ); ?>
                                                <span class="required-color">*</span>
                                                <a href="#"
                                                   title="<?php esc_attr_e( 'Specify a date from which this workflow will become available for use.', "oasisworkflow" );
                                                   ?>" class="tooltip">
                                                    <span title="">
                                                        <img
                                                                src="<?php echo esc_url( OASISWF_URL ) . '/img/help.png'; ?>"
                                                                class="help-icon"/></span>
                                                </a>
                                            </span>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <input class="date_input"
                                               id="start-date"
                                               name="start-date" readonly
                                               value="<?php echo esc_attr( $start_date ); ?>"/>
										<?php if ( $wf_editable ): ?>
                                            <button class="date-clear"><?php esc_html_e( "clear", "oasisworkflow" ); ?></button>
										<?php endif; ?>
                                    </td>
                                </tr>
                                <tr height="10px;">
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td>
                                        <label>
                                            <span class="space bold-label">
                                                <?php esc_html_e( 'End date :', 'oasisworkflow' ); ?>
                                                <a href="#"
                                                   title="<?php esc_attr_e( 'End date is not required. If not specified, the workflow is valid for ever.
                                     			Specify an end date, if you want to retire the workflow.', "oasisworkflow" ); ?>"
                                                   class="tooltip">
                                                    <span title="">
                                                        <img
                                                                src="<?php echo esc_url( OASISWF_URL ) . '/img/help.png'; ?>"
                                                                class="help-icon"/></span>
                                                </a>
                                            </span>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <input class="date_input"
                                               id="end-date"
                                               name="end-date" readonly
                                               value="<?php echo esc_attr( $end_date ); ?>"/>
                                        <button class="date-clear"><?php esc_html_e( "clear", "oasisworkflow" ); ?></button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

				<?php // Meta Boxes like auto submit, workflow applicable to and etc... ?>
				<?php do_action( 'owf_workflow_info_meta_box', $workflow ); ?>

                <div class="postbox-container">
                    <div class="postbox">
                        <div class="handlediv" title="<?php esc_attr_e( 'Click to toggle' ); ?>"><br></div>
                        <h3 style="padding:7px;">
                            <span class="workflow-lbl"><?php esc_html_e( 'Workflow Applicable To', 'oasisworkflow' ); ?></span>
                        </h3>
                        <div class="move-div workflow-define-div">
                            <div id="workflow_applicable_to" class="owf-hidden">
                                <table>
                                    <tr>
                                        <td>
                                            <span>
                                                <label>
                                                    <input type="checkbox" class="owf-checkbox" name="new_post_workflow"
                                                           value="1" <?php checked( $wf_for_new_posts, 1 ); ?> />
                                                           <?php esc_html_e( 'new posts/pages', 'oasisworkflow' ); ?>
                                                </label><br/>
                                            </span>
                                            <span>
                                                <label>
                                                    <input type="checkbox" class="owf-checkbox"
                                                           name="revised_post_workflow"
                                                           value="1" <?php checked( $wf_for_revised_posts, 1 ); ?> />
                                                           <?php esc_html_e( 'revised posts/pages', 'oasisworkflow' ); ?>
                                                </label>
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                                <div class="div-line"></div>
                            </div>
                            <table>
                                <tr>
                                    <td>
                                        <label>
                                            <span class="space bold-label">
                                                <?php esc_html_e( 'Roles (who can submit to this workflow) :', 'oasisworkflow' ); ?>
                                            </span>
                                            <span class="space">
                                                <?php esc_html_e( ' (applicable to all, if none specified)', 'oasisworkflow' ); ?>
                                            </span>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <select name="wf_for_roles[]" size="6" multiple="multiple">
											<?php OW_Utility::instance()->owf_dropdown_roles_multi( $wf_for_roles ); ?>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                            <div class="div-line"></div>
                            <table>
                                <tr>
                                    <td>
                                        <label>
                                            <span class="space bold-label">
                                                <?php esc_html_e( 'Post Types :', 'oasisworkflow' ); ?>
                                            </span>
                                            <span class="space">
                                                <?php esc_html_e( ' (applicable to all, if none specified)', 'oasisworkflow' ); ?>
                                            </span>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
										<?php OW_Utility::instance()->owf_checkbox_post_types_multi( 'wf_for_post_types[]', $wf_for_post_types ); ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>


            </div>
            <div class="widget-holder dropable-area" id="workflow-area" style="position:relative;">
                <span id="workflow-design-area"><?php esc_html_e( 'Workflow Design Canvas', 'oasisworkflow' ); ?></span>
            </div>
            <br class="clear">
        </div>
        <div class="save-action-div">
			<?php wp_nonce_field( 'owf_workflow_create_nonce', 'owf_workflow_create_nonce' ); ?>
			<?php
			if ( $wf_editable ) {
				if ( current_user_can( 'ow_edit_workflow' ) ) { ?>
                    <input type="button" value="<?php esc_attr_e( "Save", "oasisworkflow" ) ?>"
                           class="button-primary workflow-save-button" data-bname="save">
					<?php
				}
				if ( current_user_can( 'ow_create_workflow' ) ) { ?>
                    <input type="button" value="<?php esc_attr_e( "Copy" ) ?>"
                           class="button-primary workflow-copy-button">
					<?php
				}
				?>
                <span class="save_loading">&nbsp;</span>
                <a href="#" id="delete-form"><?php esc_html_e( "Clear Workflow", "oasisworkflow" ) ?></a>
				<?php
			} else {
				if ( current_user_can( 'ow_create_workflow' ) ) { ?>
                    <input type="button" value="<?php esc_attr_e( "Save as new version", "oasisworkflow" ) ?>"
                           class="button-primary workflow-save-new-version-button">
					<?php
				}
				if ( current_user_can( 'ow_edit_workflow' ) ) { ?>
                    <input type="button" value="<?php esc_attr_e( "Save", "oasisworkflow" ) ?>"
                           class="button-primary workflow-save-button" data-bname="save">
					<?php
				}
				if ( current_user_can( 'ow_create_workflow' ) ) { ?>
                    <input type="button" value="<?php esc_attr_e( "Copy" ) ?>"
                           class="button-primary workflow-copy-button">
					<?php
				}
				?>
                <span class="save_loading">&nbsp;</span>
			<?php } ?>
        </div>
        <br class="clear"/>
        <input type="hidden" id="wf_graphic_data_hi" name="wf_graphic_data_hi"/>
        <input type="hidden" id="wf_id" name="wf_id" value='<?php echo esc_attr( $wf_id ); ?>'/>
        <input type="hidden" id="deleted_step_ids" name="deleted_step_ids"/>
        <input type="hidden" id="first_step" name="first_step" value=""/>
        <input type="hidden" id="wf_validate_result" name="wf_validate_result" value="active"/>
        <input type="hidden" id="save_action" name="save_action" value="workflow_save"/>
    </form>
</div>
<?php
// include the file for the connection info setting
include( OASISWF_PATH . 'includes/pages/subpages/connection-info-content.php' );
?>
<ul id="connectionMenu" class="contextMenu">
    <div><?php esc_html_e( 'Conn Menu', 'oasisworkflow' ); ?></div>
    <li class="edit" id="connEdit"><a href="#edit"><?php esc_html_e( "Edit", "oasisworkflow" ) ?></a></li>
    <li class="delete" id="connDelete"><a href="#delete"><?php esc_html_e( "Delete", "oasisworkflow" ) ?></a></li>
    <li class="quit separator" id="connQuit"><a href="#quit"><?php esc_html_e( "Quit", "oasisworkflow" ) ?></a></li>
</ul>
<ul id="stepMenu" class="contextMenu">
    <div><?php esc_html_e( 'Step Menu', 'oasisworkflow' ); ?></div>
    <li class="edit" id="stepEdit">
        <a><?php esc_html_e( "Edit", "oasisworkflow" ) ?></a></li>
	<?php if ( $wf_editable ): ?>
        <li class="copy" id="stepCopy"><a href="#copy"><?php esc_html_e( "Copy" ) ?></a></li>
        <li class="delete" id="stepDelete"><a href="#delete"><?php esc_html_e( "Delete", "oasisworkflow" ) ?></a></li>
	<?php endif; ?>
    <li class="quit separator" id="stepQuit"><a href="#quit"><?php esc_html_e( "Quit", "oasisworkflow" ) ?></a></li>
</ul>
<?php if ( $wf_editable ): ?>
    <ul id="pasteMenu" class="contextMenu">
        <li class="paste" id="stepPaste"><a href="#paste"><?php esc_html_e( "Paste" ) ?></a></li>
        <li class="quit separator" id="stepQuit"><a href="#quit"><?php esc_html_e( "Quit" ) ?></a></li>
    </ul>
<?php endif; ?>
<span class="paste_loading">&nbsp;&nbsp;&nbsp;</span>

<?php
// include the file for the workflow create popup
include( OASISWF_PATH . 'includes/pages/subpages/workflow-create-popup.php' );
?>

<?php
// include the file for the workflow copy popup
include( OASISWF_PATH . 'includes/pages/subpages/workflow-copy-popup.php' );
?>

<div id="step-info-update" class="owf-hidden"></div>
<?php do_action( 'owf_after_workflow_create_page', $wf_id ); ?>

<script type="text/javascript">
    jQuery(document).ready(function () {
        // loading workflow create modal
        if (!jQuery('#wf_id').val()) {
            if (navigator.appName == 'Netscape') {
                show_workflow_create_modal()
            } else {
                setTimeout('show_workflow_create_modal()', 500)
            }
        }
    })

    // FIXED: To prevent step menu appear away from steps ie. review, publish and etc...
    jQuery('#wpbody').css({'position': 'inherit'})

    function call_modal(param) {
        jQuery('.contextMenu').hide()
        jQuery('#' + param).owfmodal()
    }

    function show_workflow_create_modal() {
        jQuery('#new-workflow-create-popup').owfmodal()
        jQuery('.modalCloseImg').hide()
    }
</script>