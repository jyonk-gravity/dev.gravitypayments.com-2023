<?php
/*
 * Delete workflow confirmation
 *
 * @copyright   Copyright (c) 2016, Nugget Solutions, Inc
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       4.3
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

?>

<div class="info-setting owf-hidden" id="delete-workflow-submit-div">
    <div class="dialog-title"><strong><?php echo esc_html__( "Confirm Workflow Delete", "oasisworkflow" ); ?></strong></div>
    <div>
        <div class="select-part">
            <p>
				<?php echo esc_html__( "Do you really want to delete the workflow?", "oasisworkflow" ); ?>
            </p>
            <div class="ow-btn-group changed-data-set">
                <input class="button delete-workflow button-primary" type="button"
                       value="<?php echo esc_attr__( "Delete", "oasisworkflow" ); ?>"/>
                <span>&nbsp;</span>
                <div class="btn-spacer"></div>
                <input class="button delete-workflow-cancel" type="button"
                       value="<?php echo esc_attr__( 'Cancel', 'oasisworkflow' ); ?>"
                />
            </div>
        </div>
    </div>
</div>