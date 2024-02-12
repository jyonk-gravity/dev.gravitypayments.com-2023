<div id="wd_frontfilters">
    <div id="wd_frontfilters_header">

    </div>
    <div id="wd_frontfilters_body">

        <!-- New Filter popup -->
        <div id="wd_frontfilters_new_filter_container">
            <div id="wd_frontfilters_new_filter">
                <h1>Choose a filter type</h1>
                <div class="wd_frontfilters_2_column">
                    <?php foreach ( $this->getModules() as $module ): ?>
                    <!-- Print module boxes -->
                    <div
                            data-moduletype="<?php echo $module->getType(); ?>"
                            class="wd_frontfilters_new_filter wd_frontfilters_new_<?php echo $module->getType(); ?>">
                        <img src="<?php echo $module->getIcon(); ?>">
                        <span><?php echo $module->getTitle(); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <!-- ///////// -->

        <!-- Module boxes with options popup/editor popup -->
        <div id="wd_frontfilters_modules_container">
            <div id="wd_frontfilters_modules">
                <?php foreach ( $this->getModules() as $module ): ?>
                <div
                        data-moduledefaults="<?php echo $module::getDataEncoded(array()); ?>"
                        data-moduletype="<?php echo $module->getType(); ?>"
                        class="wd_frontfilters_module wd_frontfilters_module_<?php echo $module->getType(); ?>">
                    <?php echo $module->getOutput(); ?>
                </div>
                <?php endforeach; ?>
                <div class="wd_frontfilters_modules_buttons">
                    <input name="wd_frontfilters_cancel" class="wd_frontfilters_btn" type="button" value="Cancel">
                    <input name="wd_frontfilters_save" class="wd_frontfilters_btn wd_frontfilters_btn_blue" type="submit" value="Save Filter">
                    <input name="wd_frontfilters_delete"
                           class="wd_frontfilters_btn wd_frontfilters_btn_red"
                           style="float:right;"
                           type="button" value="DELETE">
                </div>
            </div>
        </div>
        <!-- ///////// -->

        <div id="wd_frontfilters_options">
            <label>
                Columns count
                <select name="wd_frontfilters_columns_count">
                    <option value="auto">Auto</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3" selected="selected">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                    <option value="6">6</option>
                    <option value="7">7</option>
                    <option value="8">8</option>
                </select>
            </label>
            <span class="wd_frontfilters_btn wd_frontfilters_btn_blue">More Options</span>
        </div>
        <div id="wd_frontfilters_more_options">
            More options :)
        </div>
        <div id="wd_frontfilter_columns">
            <div class="wd_frontfilters_column" data-id="1">
                <div class="wd_frontfilters_add_filter">New Filter</div>
            </div>
            <div class="wd_frontfilters_column" data-id="2">
                <div class="wd_frontfilters_add_filter">New Filter</div>
            </div>
            <div class="wd_frontfilters_column" data-id="3">
                <div class="wd_frontfilters_add_filter">New Filter</div>
            </div>
            <div class="wd_frontfilters_column" data-id="4">
                <div class="wd_frontfilters_add_filter">New Filter</div>
            </div>
            <div class="wd_frontfilters_column" data-id="5">
                <div class="wd_frontfilters_add_filter">New Filter</div>
            </div>
            <div class="wd_frontfilters_column" data-id="6">
                <div class="wd_frontfilters_add_filter">New Filter</div>
            </div>
            <div class="wd_frontfilters_column" data-id="7">
                <div class="wd_frontfilters_add_filter">New Filter</div>
            </div>
            <div class="wd_frontfilters_column" data-id="8">
                <div class="wd_frontfilters_add_filter">New Filter</div>
            </div>
        </div>
    </div>
    <div id="wd_frontfilters_footer">

    </div>
    <div id="wd_frontfilters_footer_sample_data" style="display:none !important;">
        <div class="wd_frontfilters_filter"><span class="fa fa-edit"></span><span class="wd_ff_filter_title">Fitler Name</span></div>
    </div>
    <input isparam="1" type="hidden" name="<?php echo $this->name; ?>" value="<?php echo $this->data ?>" />
</div>
<?php
/*
foreach ( $this->getModules() as $module ) {
    print $module->getOutput();
}
*/