<?php
if ( ! defined( 'ABSPATH' ) ) {
     exit;
 }                                            

  $form_id=$this->post('form_id',$feed);    
  $object=$this->post('object',$feed); 
   $forms = RGFormsModel::get_forms();   

  ?>

  <div class="vx_div">
        <div class="vx_head">
<div class="crm_head_div"> <?php esc_html_e('3. Select the Object to create when a form is submitted.', 'gravity-forms-salesforce-crm'); ?></div>
<div class="crm_btn_div" title="<?php esc_html_e('Expand / Collapse','gravity-forms-salesforce-crm') ?>"><i class="fa crm_toggle_btn vx_action_btn fa-minus"></i></div>
<div class="crm_clear"></div> 
  </div>

  <div class="vx_group">
    <div class="vx_row">
  <div class="vx_col1">
  <label for="vx_module" class="left_header"><?php esc_html_e("Salesforce Object", 'gravity-forms-salesforce-crm'); ?>
  <?php gform_tooltip("vx_sel_object") ?>
 </label>
  </div>
  <div class="vx_col2">
  <select id="vx_module" class="load_form crm_sel" name="object" autocomplete="off">
  <option value=""><?php esc_html_e("Select a Salesforce Object", 'gravity-forms-salesforce-crm'); ?></option>
  <?php
  
  foreach ($objects as $k=>$v){
  $sel=$feed['object'] == $k ? 'selected="selected"' : "";

  ?>
  <option value="<?php echo esc_attr($k) ?>" <?php echo $sel; ?>><?php echo esc_html($v) ?></option>
  <?php
  }
  ?>
  </select>
    <span style="margin-left: 10px;"></span>
      <button class="button" id="vx_refresh_objects" title="<?php esc_html_e('Refresh Objects','gravity-forms-salesforce-crm'); ?>">
  <span class="reg_ok"><i class="fa fa-refresh"></i> <?php esc_html_e('Refresh Objects','gravity-forms-salesforce-crm') ?></span>
  <span class="reg_proc"><i class="fa fa-refresh fa-spin"></i> <?php esc_html_e('Refreshing...','gravity-forms-salesforce-crm') ?></span>
  </button>
  <button class="button" id="vx_refresh_fields" title="<?php esc_html_e('Refresh Fields','gravity-forms-salesforce-crm'); ?>">
  <span class="reg_ok"><i class="fa fa-refresh"></i> <?php esc_html_e('Refresh Fields','gravity-forms-salesforce-crm') ?></span>
  <span class="reg_proc"><i class="fa fa-refresh fa-spin"></i> <?php esc_html_e('Refreshing...','gravity-forms-salesforce-crm') ?></span>
  </button>
  </div>
  <div class="clear"></div>
  </div>
  </div>
  </div>

  <div id="crm_ajax_div" style="display:none; text-align: center; line-height: 100px;"><i> <?php esc_html_e('Loading, Please Wait...','gravity-forms-salesforce-crm'); ?></i></div>
  <div id="crm_err_div" class="alert_danger" style="display:none;"></div>
  <div id="crm_field_group" style="<?php if($object == "" || $form_id == "") {echo 'display:none';} ?>">
  <?php 
  if(!empty($object) && !empty($form_id)){
  $this->get_field_mapping($feed,$info);
  }
  ?>
  </div>

