<div class="taxonomy_filter_container" id="taxonomy_filter_container">
    <h1><?php _e('Taxonomy Filter', 'ajax-search-pro'); ?></h1>
    <div class="taxonomy_filter_attributes">
        <label class="wd_ff_title_label" wd-disable-on="taxonomy:,select">
            <?php _e('Label', 'ajax-search-pro'); ?>
            <input type="text" attr="label.text" maxlength="60">
            <?php _e('is visible?', 'ajax-search-pro'); ?>
            <input type="checkbox" attr="label.visible">
        </label>
        <label for="taxonomy_filter_display_mode" wd-disable-on="taxonomy:,select">
            <?php _e('Display mode', 'ajax-search-pro'); ?>
            <select attr="display_mode">
                <option value="checkboxes"><?php _e('Checkboxes', 'ajax-search-pro'); ?></option>
                <option value="dropdown"><?php _e('Dropdown', 'ajax-search-pro'); ?></option>
                <option value="dropdownsearch"><?php _e('Dropdown with search', 'ajax-search-pro'); ?></option>
                <option value="multisearch"><?php _e('Multiselect with search', 'ajax-search-pro'); ?></option>
                <option value="radio"><?php _e('Radio', 'ajax-search-pro'); ?></option>
            </select>
        </label>
        <label for="taxonomy_filter_taxonomy">
            <?php _e('Taxonomy', 'ajax-search-pro'); ?>
            <select attr="taxonomy">
                <option value="select" selected="selected" disabled><?php _e('Select a taxonomy', 'ajax-search-pro'); ?></option>
                <?php foreach ( $this->getTaxonomiesList() as $taxonomy => $label ): ?>
                    <option value="<?php echo $taxonomy; ?>"><?php echo $label; ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label for="taxonomy_filter_mode" wd-disable-on="taxonomy:,select">
            <?php _e('Mode', 'ajax-search-pro'); ?>
            <select attr="mode">
                <option value="exclude"><?php _e('Display all, exclude selected', 'ajax-search-pro'); ?></option>
                <option value="include"><?php _e('Exclude all, display selected', 'ajax-search-pro'); ?></option>
            </select>
        </label>
    </div>
    <div class="taxonomy_filter_fields" wd-disable-on="taxonomy:,select">
        <div class="draggablecontainer">
            <ul style='text-align:left;' class="connectedSortable" id="taxonomy_draggable">
                <label class="taxonomy_draggable_search hiddend">
                    <span class="fa fa-search"></span>
                    <input type="text" placeholder="<?php _e('Type term name & press Enter', 'ajax-search-pro'); ?>">
                </label>
                <span>Select a taxonomy to list terms here</span>
            </ul>
        </div>
        <div class="sortablecontainer">
            <ul style='text-align:left;' class="connectedSortable" id="taxonomy_sortable">
                <span class="taxonomy_filter_mode_label_excluded"><?php _e('Excluded terms:', 'ajax-search-pro'); ?></span>
                <span class="taxonomy_filter_mode_label_included"><?php _e('Included terms:', 'ajax-search-pro'); ?></span>
            </ul>
        </div>
    </div>
    <div class="taxonomy_filter_other_attributes" wd-disable-on="taxonomy:,select">
        <fieldset class="taxonomy_filter_layout_options">
            <legend><?php _e('Layout & Functionality Options', 'ajax-search-pro'); ?></legend>
            <div>
                <label>
                    <?php _e('Hide empty terms?', 'ajax-search-pro'); ?>
                    <input type="checkbox" attr="hide_empty">
                </label>
                <p class="wd_ff_desc_left">
                    <?php echo __('Automatically hides terms, that have no posts or any CPT assigned to them.', 'ajax-search-pro'); ?>
                </p>
            </div>
            <div wd-hide-on="display_mode:multisearch">
                <label>
                    <?php _e('Display "select all/one" option', 'ajax-search-pro'); ?>
                    <input type="checkbox" attr="select_all.enabled">
                </label>
                <label wd-disable-on="select_all.enabled:0">
                    <?php _e('text', 'ajax-search-pro'); ?>
                    <input type="text" attr="select_all.text">
                </label>
            </div>
            <div wd-show-on="display_mode:checkboxes">
                <label>
                    <?php _e('Checkboxes default state', 'ajax-search-pro'); ?>
                    <select attr="display_mode_args.checkboxes.default_state">
                        <option value="checked"><?php _e('Checked', 'ajax-search-pro'); ?></option>
                        <option value="unchecked"><?php _e('Un-Checked', 'ajax-search-pro'); ?></option>
                    </select>
                </label>
                <label>
                    <?php _e('Hide (collapse) child terms, where the parent checkbox is unchecked?', 'ajax-search-pro'); ?>
                    <input type="checkbox" attr="display_mode_args.checkboxes.hide_children_on_unchecked">
                </label>
                <p class="wd_ff_desc_left">
                    <?php echo __('Automatically hides the checkbox options, where the parent terms are unchecked.', 'ajax-search-pro'); ?>
                </p>
            </div>
            <div wd-hide-on="display_mode:checkboxes" class="taxonomy_filter_dm">
                <label>
                    <?php _e('Default selected', 'ajax-search-pro'); ?>
                    <input class="taxonomy_filter_search" type="text" placeholder="<?php _e('Search for terms here', 'ajax-search-pro'); ?>">
                    <span class="taxonomy_filter_search_nores hiddend"><?php _e('No results for this phrase!', 'ajax-search-pro'); ?></span>
                    <input type="hidden" attr="display_mode_args.dropdown.default">
                </label>
                <span class="taxonomy_filter_selected hiddend"><span class="fa fa-ban"></span><span class="taxonomy_filter_selected_name"></span></span>
                <div class="taxonomy_filter_search_res">
                    <ul>
                        <li key="-1"><?php _e('Select All/One option', 'ajax-search-pro'); ?></li>
                        <li key="first"><?php _e('First option', 'ajax-search-pro'); ?></li>
                        <li key="last"><?php _e('Last option', 'ajax-search-pro'); ?></li>
                    </ul>
                </div>
            </div>
            <div wd-hide-on="mode:include" class="taxonomy_filter_dm">
                <label>
                    <?php _e('Maintain term hierarchy', 'ajax-search-pro'); ?>
                    <input type="checkbox" attr="maintain_hierarchy">
                </label>
                <p class="wd_ff_desc_left">
                    <?php echo __('Shows child terms hierarchically under their parents with padding. Supports multiple term levels.', 'ajax-search-pro'); ?>
                </p>
                <label>
                    <?php _e('Default term order', 'ajax-search-pro'); ?>
                    <select attr="term_orderby">
                        <option value="name"><?php _e('Name', 'ajax-search-pro'); ?></option>
                        <option value="count"><?php _e('Item count', 'ajax-search-pro'); ?></option>
                        <option value="id"><?php _e('ID', 'ajax-search-pro'); ?></option>
                    </select>
                    <select attr="term_order">
                        <option value="ASC"><?php _e('Ascending', 'ajax-search-pro'); ?></option>
                        <option value="DESC"><?php _e('Descending', 'ajax-search-pro'); ?></option>
                    </select>
                </label>
            </div>
            <div class="taxonomy_filter_dm">
                <label>
                    <?php _e('Allow results with missing terms', 'ajax-search-pro'); ?>
                    <input type="checkbox" attr="allow_empty">
                </label>
                <p class="wd_ff_desc_left">
                    <?php _e('This decides what happens if the posts does not have any terms from the selected taxonomies. For example posts with no categories, when using a category filter.', 'ajax-search-pro'); ?>
                </p>
            </div>
            <div class="taxonomy_filter_dm" wd-show-on="display_mode:checkboxes,multisearch">
                <label>
                    <?php _e('Taxonomy terms checkbox/multiselect connection logic', 'ajax-search-pro'); ?>
                    <select attr="term_logic">
                        <option value="or"><?php _e('At least one selected terms should match', 'ajax-search-pro'); ?></option>
                        <option value="and" selected="selected"><?php _e('All of the selected terms must match, exclude unselected (default)', 'ajax-search-pro'); ?></option>
                        <option value="andex"><?php _e('All of the selected terms must match EXACTLY, but unselected ones are not excluded.', 'ajax-search-pro'); ?></option>
                    </select>
                </label>
            </div>
        </fieldset>
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
    <div class="taxonomy_filter_sample" style="display: none !important;">
        <li class="ui-state-default ui-draggable ui-draggable-handle" term_level="0" data-id="123">
                <span class="wd_sort_visible">
                    <label class="wd_plain">
                        <input type="checkbox" attr="selected" value="content" checked="checked">
                    </label>
                    <a class="fa fa-minus-circle taxonomy_filter_remove"></a>
                </span>
            <input type="hidden" attr="id" value="">
            <input type="hidden" attr="level" value="">
            <span attr="label">Aside</span>
        </li>
    </div>
    <div class="taxonomy_filter_loader hiddend"></div>
</div>