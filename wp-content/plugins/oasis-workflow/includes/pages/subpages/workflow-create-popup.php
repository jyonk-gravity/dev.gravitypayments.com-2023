<div id="new-workflow-create-popup">
    <div class="dialog-title"><strong><?php esc_html_e( "Create New Workflow", "oasisworkflow" ); ?></strong></div>
    <table>
        <tr class="space-under">
            <td>
                <label><?php esc_html_e( "Title : ", "oasisworkflow" ); ?></label>
            </td>
            <td>
                <input type="text" id="new-workflow-title" class="workflow-title"/>
            </td>
        </tr>
        <tr class="space-under">
            <td>
                <label><?php esc_html_e( "Description : ", "oasisworkflow" ); ?></label>
            </td>
            <td>
                <textarea id="new-workflow-description" cols="20" rows="10" class="workflow-description"></textarea>
            </td>
        </tr>
    </table>
    <p class="changed-data-set">
        <input type="button" id="new-wf-save" class="button-primary"
               value="<?php esc_attr_e( "Save", "oasisworkflow" ); ?>"/>
        <span>&nbsp;</span>
        <a href="javascript:window.history.back()"
           id="new-wf-cancel"><?php esc_html_e( "Cancel", "oasisworkflow" ); ?></a>
    </p>
    <br class="clear"/>
</div>	