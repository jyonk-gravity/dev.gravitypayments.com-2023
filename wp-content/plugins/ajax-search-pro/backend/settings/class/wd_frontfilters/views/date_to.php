<div class="date_to_filter_container">
    <h1><?php _e('Before date Filter', 'ajax-search-pro'); ?></h1>
    <div class="date_to_filter_attributes">
        <label class="wd_ff_title_label">
            <?php _e('Label', 'ajax-search-pro'); ?>
            <input type="text" attr="label.text" maxlength="60">
            <?php _e('is visible?', 'ajax-search-pro'); ?>
            <input type="checkbox" attr="label.visible">
        </label>
        <label for="date_to_filter_display_mode">
            <?php _e('Mode', 'ajax-search-pro'); ?>
            <select attr="display_mode" attr="display_mode">
                <option value="relative_date"><?php _e('Relative Date', 'ajax-search-pro'); ?></option>
                <option value="date"><?php _e('Date', 'ajax-search-pro'); ?></option>
            </select>
        </label>
        <div class="date_to_filter_rel_date" wd-show-on="display_mode:relative_date">
            <?php _e('Relative', 'ajax-search-pro'); ?>
            <input attr="relative_date.year" aria-label="Years" type="number" class="threedigit" value=""><?php _e('years', 'ajax-search-pro'); ?>&nbsp;
            <input attr="relative_date.month" aria-label="Months" type="number" class="threedigit" value=""><?php _e('months', 'ajax-search-pro'); ?>&nbsp;
            <input attr="relative_date.day" aria-label="Days before current date" type="number" class="threedigit" value="">&nbsp; <?php _e('days before current date', 'ajax-search-pro'); ?>
            <p class="wd_ff_desc_right">
                <?php _e('It is possible to use negative values as well.', 'ajax-search-pro'); ?>
            </p>
        </div>
        <div class="date_to_filter_date" wd-show-on="display_mode:date">
            <label>
                <?php _e('Date', 'ajax-search-pro'); ?>
                <input type="text" attr="date" value="">
            </label>
            <p class="wd_ff_desc_right">
                <?php _e('Empty value can be used, in that case the placeholder text will appear.', 'ajax-search-pro'); ?>
            </p>
        </div>
        <div class="date_to_filter_placeholder">
            <label>
                <?php _e('Placeholder text', 'ajax-search-pro'); ?>
                <input type="text" attr="placeholder" value="">
            </label>
        </div>
        <div class="date_to_filter_date_format">
            <label>
                <?php _e('Date format', 'ajax-search-pro'); ?>
                <input type="text" attr="date_format" value="">
            </label>
            <p class="wd_ff_desc_right">
                <?php echo sprintf( __('dd/mm/yy is the most popular format, <a href="%s" target="_blank">list of accepted params</a>', 'ajax-search-pro'), 'http://api.jqueryui.com/datepicker/#utility-formatDate' ); ?>
            </p>
        </div>
    </div>
    <div class="date_to_filter_other_attributes">
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