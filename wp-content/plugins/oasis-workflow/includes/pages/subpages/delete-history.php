<div class="info-setting owf-hidden" id="delete-history-div">
    <div class="dialog-title"><strong><?php esc_html_e( "Delete History", "oasisworkflow" ); ?></strong></div>
    <div>
        <div id="delete_history_msg" class="ow-info-message select-info">
			<?php esc_html_e( "Workflow History for posts/pages that are currently active in a workflow will NOT be deleted." ) ?>
        </div>
        <div class="select-info owf-text-info left full-width">
            <label class="half-width">
				<?php esc_html_e( "Delete Workflow History for posts/pages which were last updated : ", "oasisworkflow" ); ?>
            </label>
            <select id="delete-history-range-select">
                <option value="one-month-ago"><?php esc_html_e( "1 Month ago", "oasisworkflow" ); ?></option>
                <option value="three-month-ago"><?php esc_html_e( "3 Months ago", "oasisworkflow" ); ?></option>
                <option value="six-month-ago"><?php esc_html_e( "6 Months ago", "oasisworkflow" ); ?></option>
                <option value="twelve-month-ago"><?php esc_html_e( "12 Months ago", "oasisworkflow" ); ?></option>
                <option value="everything"><?php esc_html_e( "Since the beginning", "oasisworkflow" ); ?></option>
            </select>
            <br class="clear">
        </div>
        <br class="clear">
        <div class="select-info left changed-data-set full-width">
            <input type="button" id="deleteHistoryConfirm" class="button-primary"
                   value="<?php esc_attr_e( "Delete Workflow History", "oasisworkflow" ); ?>"/>
            <span>&nbsp;</span>
            <a href="#" id="deleteHistoryCancel"><?php esc_html_e( "Cancel", "oasisworkflow" ); ?></a>
            <br class="clear">
        </div>
        <br class="clear">
    </div>
</div>