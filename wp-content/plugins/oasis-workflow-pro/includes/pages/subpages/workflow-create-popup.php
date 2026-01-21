<?php
/*
* Workflow Create Popup
*
* @copyright   Copyright (c) 2016, Nugget Solutions, Inc
* @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
* @since       2.1
*
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}
?>
<div id="new-workflow-create-popup">
    <div class="dialog-title"><strong><?php echo esc_html__( "Create New Workflow", "oasisworkflow" ); ?></strong></div>
    <table>
        <tr class="space-under">
            <td>
                <label><?php echo esc_html__( "Title : ", "oasisworkflow" ); ?></label>
            </td>
            <td>
                <input type="text" id="new-workflow-title" class="workflow-title"/>
            </td>
        </tr>
        <tr class="space-under">
            <td>
                <label><?php echo esc_html__( "Description : ", "oasisworkflow" ); ?></label>
            </td>
            <td>
                <textarea id="new-workflow-description" cols="20" rows="10" class="workflow-description"></textarea>
            </td>
        </tr>
    </table>
    <p class="changed-data-set">
        <input type="button" id="new-wf-save" class="button-primary"
               value="<?php echo esc_attr__( "Save", "oasisworkflow" ); ?>"/>
        <span>&nbsp;</span>
        <a href="javascript:window.history.back()" id="new-wf-cancel"><?php echo esc_html__( "Cancel", "oasisworkflow" ); ?></a>
    </p>
    <br class="clear"/>
</div>	