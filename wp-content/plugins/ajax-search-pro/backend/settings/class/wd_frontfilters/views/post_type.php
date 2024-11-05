<div class="post_type_filter_container">
    <h1>Content Type Filter</h1>
    <div class="post_type_filter_attributes">
        <label class="wd_ff_title_label">
            <?php _e('Label', 'ajax-search-pro'); ?>
            <input type="text" attr="label.text" maxlength="60">
            <?php _e('is visible?', 'ajax-search-pro'); ?>
            <input type="checkbox" attr="label.visible">
        </label>
        <label for="post_type_filter_display_mode">
            <?php _e('Display mode', 'ajax-search-pro'); ?>
            <select attr="display_mode">
                <option value="checkboxes"><?php _e('Checkboxes', 'ajax-search-pro'); ?></option>
                <option value="dropdown"><?php _e('Dropdown', 'ajax-search-pro'); ?></option>
                <option value="radio"><?php _e('Radio', 'ajax-search-pro'); ?></option>
            </select>
        </label>
    </div>
    <div class="post_type_filter_fields">
        <div class="draggablecontainer">
            <ul style='text-align:left;' class="connectedSortable" id="post_type_draggable">
                <?php foreach ( $this::getData( array(), true )['choices'] as $field => $choice ): ?>
                <li class="ui-state-default ui-draggable ui-draggable-handle" data-field="<?php echo $field; ?>">
                    <span class="wd_drag_visible"><?php echo $choice; ?></span>
                    <span class="wd_sort_visible">
                        <label><?php echo __($choice, 'ajax_search_pro'); ?></label>
                        <input type="text" attr="label" maxlength="100" value="<?php echo $choice; ?>">
                        <label class="wd_plain">
                            <?php _e('Checked?', 'ajax-search-pro'); ?> <input type="checkbox" attr="selected" value="content" checked="checked">
                        </label>
                        <a class="fa fa-minus-circle post_type_filter_remove"></a>
                    </span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="sortablecontainer">
            <ul style='text-align:left;' class="connectedSortable" id="post_type_sortable">
            </ul>
        </div>
    </div>
    <div class="post_type_filter_other_attributes">
        <fieldset>
            <legend><?php _e('Visibility', 'ajax-search-pro'); ?></legend>
            <label>
                <?php _e('Desktop', 'ajax-search-pro'); ?>
                <input type="checkbox" attr="visibility.desktop">
            </label>
            <label>
                <?php _e('Tablet', 'ajax-search-pro'); ?>
                <input type="checkbox" attr="visibility.tablet">
            </label>
            <label>
                <?php _e('Mobile', 'ajax-search-pro'); ?>
                <input type="checkbox" attr="visibility.mobile">
            </label>
        </fieldset>
        <fieldset>
            <legend><?php _e('Neccessity', 'ajax-search-pro'); ?></legend>
            <label>
                <?php _e('Is at least one selection required?', 'ajax-search-pro'); ?>
                <input type="checkbox" attr="required">
            </label>
            <label wd-disable-on="required:0">
                <?php _e('Text:', 'ajax-search-pro'); ?>
                <input type="text" attr="required_text">
            </label>
        </fieldset>
    </div>
    <div style="clear:both"></div>
</div>