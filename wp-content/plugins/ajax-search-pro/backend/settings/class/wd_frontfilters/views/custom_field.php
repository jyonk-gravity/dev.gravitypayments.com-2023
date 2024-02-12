<div class="generic_filter_container">
    <h1>Generic Filter</h1>
    <div class="generic_filter_attributes">
        <label class="wd_ff_title_label">
            <?php _e('Label', 'ajax-search-pro'); ?>
            <input type="text" attr="label.text" maxlength="60">
            <?php _e('is visible?', 'ajax-search-pro'); ?>
            <input type="checkbox" attr="label.visible">
        </label>
        <label for="generic_filter_display_mode">
            <?php _e('Display mode', 'ajax-search-pro'); ?>
            <select attr="display_mode">
                <option value="checkboxes"><?php _e('Checkboxes', 'ajax-search-pro'); ?></option>
                <option value="dropdown"><?php _e('Dropdown', 'ajax-search-pro'); ?></option>
                <option value="radio"><?php _e('Radio', 'ajax-search-pro'); ?></option>
                <option value="text"><?php _e('Text', 'ajax-search-pro'); ?></option>
                <option value="hidden"><?php _e('Hidden', 'ajax-search-pro'); ?></option>
                <option value="slider"><?php _e('Slider', 'ajax-search-pro'); ?></option>
                <option value="range"><?php _e('Range slider', 'ajax-search-pro'); ?></option>
                <option value="datepicker"><?php _e('Date picker', 'ajax-search-pro'); ?></option>
            </select>
        </label>
        <fieldset wd-show-on="display_mode:checkboxes,dropdown,radio">
            <label>
                <?php _e('Values', 'ajax-search-pro'); ?>
                <textarea attr="value_display.selects"></textarea>
                <?php echo sprintf( __('One item per line. Use the <strong>{get_values}</strong> variable to get custom field values automatically. 
                       For more info see the 
                       <a target="_blank" href="%s">documentation</a>.', 'ajax-search-pro'), 'https://documentation.ajaxsearchpro.com/frontend-search-settings/custom-field-selectors' );
                ?>
            </label>
            <label>
                <?php _e('Operator', 'ajax-search-pro'); ?>
                <select attr="operator">
                    <optgroup label="<?php _e('Numeric operators', 'ajax-search-pro'); ?>">
                        <option value="eq">EQUALS</option>
                        <option value="neq">NOT EQUALS</option>
                        <option value="lt">LESS THEN</option>
                        <option value="let">LESS OR EQUALS THEN</option>
                        <option value="gt">MORE THEN</option>
                        <option value="get">MORE OR EQUALS THEN</option>
                    </optgroup>
                    <optgroup label="String operators">
                        <option value="elike">EXACTLY LIKE</option>
                        <option value="like" selected="selected">LIKE</option>
                        <option value="not elike">NOT EXACTLY LIKE</option>
                        <option value="not like">NOT LIKE</option>
                    </optgroup>
                </select>
            </label>
        </fieldset>
        <fieldset wd-show-on="display_mode:dropdown">
            <label>
                <?php _e('Multiselect?', 'ajax-search-pro'); ?>
                <input type="checkbox" attr="display_mode_args.dropdown.multi">
            </label>
            <label>
                <?php _e('Searchable?', 'ajax-search-pro'); ?>
                <input type="checkbox" attr="display_mode_args.dropdown.searchable">
                <?php _e('placeholder', 'ajax-search-pro'); ?>
                <input type="text" attr="display_mode_args.dropdown.placeholder">
            </label>
            <label>
                <?php _e('Drop-down values logic', 'ajax-search-pro'); ?>
                <select attr="display_mode_args.dropdown.logic">
                    <option value="OR"><?php _e('OR', 'ajax-search-pro'); ?></option>
                    <option value="AND"><?php _e('AND', 'ajax-search-pro'); ?></option>
                    <option value="ANDSE"><?php _e('AND in separate fields', 'ajax-search-pro'); ?></option>
                </select>
            </label>
        </fieldset>
        <fieldset wd-show-on="display_mode:text,hidden">
            <textarea attr="value_display.text"></textarea>
        </fieldset>
    </div>
    <div class="generic_filter_other_attributes">
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