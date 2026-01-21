<?php

/*
 * Workflow Import/Export Tool
 *
 * @copyright   Copyright (c) 2018, Nugget Solutions, Inc
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       5.3
 *
 */
?>

<div class="wrap ow-tools">

   <span
           class="required-color"><?php esc_html_e( "Note: Make sure your environment is identical in terms of plugins, roles, custom roles and users otherwise, the import might error out." ); ?></span>

    <form enctype="multipart/form-data" method="post">
        <div id="owf-export">
            <div id="settingstuff">
                <fieldset class="owf_fieldset">
                    <legend><?php esc_html_e( "Export", "oasisworkflow" ); ?></legend>
                    <span
                            class="description"><?php esc_html_e( "Use the download button to export to a .json file which you can then import to another WordPress installation", "oasisworkflow" ); ?></span>
                    <br/>
                    <br/>
                    <label style="display: block;">
                        <input type="checkbox" class="owf-checkbox" name="add_for_export[]" value="workflows"/>
						<?php esc_html_e( "Workflows", "oasisworkflow" ); ?>
                    </label>
                    <label style="display: block;">
                        <input type="checkbox" class="owf-checkbox" name="add_for_export[]" value="settings"/>
						<?php esc_html_e( "Settings (includes all the settings)", "oasisworkflow" ); ?>
                    </label>
                    <br/>
                    <input type="submit" name="ow-export-workflow" id="ow-export-workflow"
                           class="button action"
                           value="<?php esc_attr_e( "Download Export File", "oasisworkflow" ); ?>">
					<?php wp_nonce_field( 'owf_export_workflows', 'owf_export_workflows' ); ?>
                </fieldset>
            </div>
        </div>
        <br class="clearfix"/>
        <!-- Import Workflow -->
        <div id="workflow-import">
            <div id="settingstuff">
                <fieldset class="owf_fieldset">
                    <legend><?php esc_html_e( "Import", "oasisworkflow" ); ?></legend>
                    <span
                            class="description"><?php esc_html_e( "Select the Oasis Workflow JSON file you would like to import.", "oasisworkflow" ); ?></span>
                    <br/>
                    <p>
                        <label
                                for="upload"><?php esc_html_e( 'Choose a file from your computer:', 'oasisworkflow' ); ?></label>
                    </p>
                    <p>
                        <input type="file" id="upload" name="import-workflow-filename" size="50">
                    </p>
                    <br/>
                    <input type="submit" name="ow-import-workflow" id="ow-import-workflow"
                           class="button action" value="<?php esc_attr_e( "Import", "oasisworkflow" ); ?>">
					<?php wp_nonce_field( 'owf_import_workflows', 'owf_import_workflows' ); ?>
                </fieldset>
            </div>
        </div>

    </form>
</div>
        