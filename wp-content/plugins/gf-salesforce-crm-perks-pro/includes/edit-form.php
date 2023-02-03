<?php
if( !defined( 'ABSPATH' ) ) exit;


if( !class_exists( 'vxg_salesforce_form' ) ) {
class vxg_salesforce_form extends vxg_salesforce{
    public $refresh_picklists=true;
   
  function __construct() {

  if(is_admin()) {
  
  if($this->is_gravity_page('gf_edit_forms')) {
  add_filter('gform_tooltips', array(&$this, 'tooltips')); //Filter to add a new tooltip
  add_action( "gform_editor_js", array(&$this, "editor_js")); // Now we execute some javascript technicalitites for the field to load correctly
  add_action("gform_field_standard_settings", array(&$this,"use_as_entry_link_settings"), 10, 2);
  add_action('admin_head', array(&$this, 'admin_head'));
  }else if(defined('RG_CURRENT_PAGE') && in_array(RG_CURRENT_PAGE, array("admin-ajax.php"))) {
  // Get the list of fields available for the object type
  add_action('wp_ajax_object_picklist_'.$this->id, array(&$this, 'fields_html_ajax'));
  add_action('wp_ajax_object_area_'.$this->id, array(&$this, 'object_html_ajax'));
  }
  }
$feed=get_option($this->id.'_lists',array()); 
  add_filter("gform_admin_pre_render", array(&$this, 'override_form'), 10);
  add_filter("gform_pre_render", array(&$this, 'override_form'), 10, 2);
  
  }
  /**
  * Replace the Gravity Forms form choices with remotely-pulled Salesforce picklist options.
  * @param  array $form The Gravity Forms array object
  * @param  [type] $ajax [description]
  * @return array       Modified GF array object
  */
  function override_form($form, $ajax = null) {  
    
     
  if( empty( $form ) ) {
  return;
  }

  foreach($form['fields'] as $field) { 
     // if($field->id == 60){
      //var_dump($field->aaa );
     // }
  // If the field has mapping enabled, and the object and field are defined, replace it
  if( !empty($field->crm_map_enabled) && !empty($field->crm_object) && !empty($field->crm_option) && !empty($field->crm_map_type) && $field->crm_map_enabled ==$this->id &&
  $field->crm_map_type == "live"
  ){
  $crm_field=$field->crm_option; //crm field name
  $crm_object=$field->crm_object; 
  $crm_account=$field->crm_account; 
  if($crm_field!=""){ 
  $filter=current_filter();
  if($filter == "gform_admin_pre_render"){
  $this->refresh_picklists=false;     //do not check expiry in admin
  } 
  $fields=$this->get_field_options($crm_account, $crm_object);
 
 //  echo "<textarea>".json_encode($fields)."</textarea>";
  if( isset($fields[$crm_field]['options']) && is_array($fields[$crm_field]['options']) && count($fields[$crm_field]['options'])>0){ 
  $field = $this->apply_picklist_to_field($fields[$crm_field]['options'], $field); 
  }}
 // var_dump($fields,$crm_field); die();
  } //die();
  ///  echo json_encode($remote_field['picklistValues']);
  } 
  return $form;
  }
  /**
  * object picklists
  * 
  * @param mixed $object
  * @param mixed $refresh
  * @return mixed
  */
  private function get_field_options($account,$object,$refresh=false){
      if(empty($object)|| empty($account)){
          return array();
      }
    $data=$this->get_data_object();  
    $info=$data->get_info($account);  
    $feed=array(); $fields=""; $lists=array();
    if(isset($info['meta']) && !empty($info['meta'])){
      $feed=$info['meta'];
      if(isset($feed['lists']) && is_array($feed['lists'])){
       $lists=$feed['lists'];   
      }
      if(isset($feed['lists'][$object]) && is_array($feed['lists'][$object])){
       $fields=$feed['lists'][$object];    
      }  
    } 
    if(!is_array($feed)){
        $feed=array();
    }
       $time=current_time('timestamp');
      $crm_fields=$this->post('fields',$fields); 
     $crm=array();
     if($this->refresh_picklists){ //it is false if in admin and auto checking
      if( !is_array($fields)){
          $refresh=true;
      }    
  if(!isset($fields['fields']) || !is_array($fields['fields'])){
      $refresh=true;
  } 

  if(!$refresh){ //check expiry
$data=array();
if(isset($info['data'])){
  $data=$info['data'];  
}
  $cach_time=(int)$this->post('cache_time',$data);
  if(empty($cach_time)){
      $cach_time=86400;
  }
  $field_time=(int)$this->post('time',$fields);
  if(($cach_time+$field_time)<=$time){ //refresh fields
  $refresh=true;    
  }
  } 
     }

   //  var_dump($cach_time+$field_time.'---------'.$time.'-----'.$cach_time.'-----------'.$field_time); die(); 
  if( $refresh){
 
      $api = $this->get_api($info);
  $crm_fields=$api->get_crm_fields($object,true); 

  $objects=$this->get_objects($info);
if(isset($objects[$object])){
  $lists[$object]=array("time"=>$time,"fields"=>$crm_fields);
 $feed['lists']=$lists;
  $this->update_info(array('meta'=>$feed),$account);
}else{
   $crm_fields=''; 
}
  }
  return is_array($crm_fields) ? $crm_fields : array();  
  }
  /**
  * Modify pick list fields to add live picklists
  * 
  * @param mixed $picklistValues
  * @param mixed $field
  */
  private function apply_picklist_to_field($picklistValues, $field) {
  $choices = $inputs = array();
  $i = 0; //var_dump($field);
  foreach ($picklistValues as $key => $value) {
  $i++; 
  $choices[] = array(
  'text' => $value['label'],
  'value' => $value['value'],
  'isSelected' => floatval(isset($value['default']) && $value['default']=="1"),
  'price' => '',
  );
  
  $inputs[] = array(
  'id' => $field['id'].'.'.$i,
  'label' => $value['value'],
  );
  }
  
  if(!empty($choices)) { $field['choices'] = $choices; }
  switch($field['type']) {
  case 'select':
  case 'multiselect':
  $field['inputs'] = '';
  break;
  case 'radio':
  case 'checkboxes':
 // if(!empty($inputs)) { $field['inputs'] = $inputs; }
  break;
  } //var_dump($field);
  return $field;
  }
  /**
  * Place CSS and JS in Head
  * 
  */
  
  public function admin_head() {
  ?>
  <style type="text/css">
  td.crm_field_cell {
  border-bottom: 1px solid #ccc!important;
  vertical-align:top;
  padding: 4px;
  }
  #crm_map_ui {
  display: none;
  clear: both;
  padding-left: .25em;
  }
  .crm_ajax{
  font-size: 20px;
  margin-left: 8px;
  line-height: 30px;
  }
  #crm_field_group_form ul {
  max-height:200px; overflow-y:auto;
  margin: 0;
  -moz-column-count: 2;
  -moz-column-gap: 10px;
  -webkit-column-count: 2;
  -webkit-column-gap: 10px;
  column-count: 2;
  column-gap: 10px;
  }
     .panel-block input[type=checkbox].crm_map_enabled , .panel-block input[type=radio].crm_map_field , .panel-block input[type=radio].crm_map_type{
        width: auto ;
        height: auto;
        position: relative;
        margin: -2px 3px 0px 0px;
        vertical-align: middle;
        -webkit-clip-path: none;
        clip-path: none;
        -webkit-appearance: auto;
    }
  </style>
  <?php
  }
  
  /**
  * Objects picklist selectbox in gravity forms admin
  * ajax method
  */
  public function fields_html_ajax() {
  
  check_ajax_referer("select_object", "select_object");

  $refresh=$this->post('refresh');
  $object=$this->post('object');
  $id=$this->post('account');
  $refresh = $refresh == "true" ? true : false;
 $str=$this->fields_html($id,$object,$refresh);
  die($str);
  }
  public function object_html_ajax() {
  
  check_ajax_referer("select_object", "select_object");

  $object=$this->post('object');
  $id=$this->post('account');

 $this->object_area($id,$object);
  die();
  }
 public function fields_html($id,$object,$refresh=false){
     if(empty($object) || empty($id) ){
         return '';
     } 
       //getting list of all crm merge variables for the selected contact list
  $fields = $this->get_field_options($id,$object,$refresh); //var_dump($fields); die();
  if(!is_array($fields)){
      $msg= empty($fields) ? esc_html__('Fields Not Found','gravity-forms-salesforce-crm') : $fields;
  $str = '<div class="field_group_'.$this->id.'"><strong>'.__('Error:', 'gravity-forms-salesforce-crm').'</strong> '.wp_kses_post($msg).'</div>';
  ///   $str = str_replace(array("\n", "\t", "\r"), '', str_replace("'", "\'", $str));    
  }else{
  $str = $this->get_field_mapping($fields);
  }
  return $str;
 } 
  /**
  * objects picklist ul in admin
  * 
  * @param mixed $field
  */
  private  function get_picklist_ul($field) {  
  $str = '<ul class="ul-square">';
  foreach($field as $value) {
  if(empty($value['value'])) { continue; }
        $default_html = !empty($value['default']) ?  '<strong class="default"> '.esc_html__('(Default)', 'gravity-forms-salesforce-crm').'</strong>' : '';
        $default = !empty($value['default']) ?  '1' : '0';
  $str .= '<li style="margin:0; padding:0;" data-default="'.$default.'" data-value="'.htmlentities($value['value']).'" data-label="'.htmlentities($value['label']).'">'.htmlentities($value['label']).' '.$default_html.'</li>';
  }
  $str .= '</ul>';
  return $str;
  }
  
  /**
  * Field mapping admin
  * 
  * @param mixed $fields
  */
  
  private  function get_field_mapping( $fields) {
  
  $usedFields = array();
  $str = $custom = $standard = '';
  ////echo json_encode($fields); die();
  //getting list of all fields for the selected form

  if(is_array($fields)) {
  foreach($fields as $field){ 
  if(isset($field['options']) && is_array($field['options']) && count($field['options'])>0){
  $field_desc = '';
  $row = "
  <tr class='crm_radio_tr'>
  <td class='crm_field_cell' style='text-align:center; width:2em'>
  <label for='".$this->id."_map_field_{$field['name']}'>
  <input value='{$field['name']}' type='radio' name='crm_map_field' class='".$this->id."_map_field crm_map_field' id='".$this->id."_map_field_{$field['name']}' />
  </label>
  </td>
  <td class='crm_field_cell'>
  <label for='".$this->id."_map_field_{$field['name']}'><strong>" . stripslashes( $field['label'] )  . "</strong>
  <span class='description' style='display:block'>Field Choices:</span>
  ".$this->get_picklist_ul($field['options'])."
  </label>
  </td>
  </tr>";
  
  $str .= $row;
  }
  } // End foreach merge var.
  } else {
  $str .= '<tr>
  <td class="crm_field_cell" style="vertical-align:top; padding-right:.5em;">
  This object has no Pick List or Multi Pick List fields.
  </td>
  </tr>';
  }
  if($str !=''){
$new_str=apply_filters($this->id.'_before_options','',$field,$fields);
  $str="
  <div id='crm_field_group_form'>".$new_str."
  <table cellpadding='0' cellspacing='0' class='form-table'>
  <thead class='screen-reader-text'>
  <tr>
  <th scope='col' class='crm_col_heading'>" . esc_html__("Pickist Field", 'gravity-forms-salesforce-crm') . "</th>
  <th scope='col' class='crm_col_heading'>" . esc_html__("Form Fields", 'gravity-forms-salesforce-crm') . "</th>
  </tr>
  </thead>
  <tbody>".$str."
  </tbody>
  </table>
  </div>";
  }else{
  $str='<div><b>'.__('This object has no Pick List or Multi Pick List fields','gravity-forms-salesforce-crm').'</b></div>';    
  }
  /// $str = str_replace(array("\n", "\t", "\r"), '', str_replace("'", "\'", $str));
  
  return $str;
  }
  /**
  * Custom JS in Head
  * 
  */
public function editor_js() {
  ?>
  
  <script type='text/javascript'>

  jQuery(document).ready(function($) { 
  // Show the crm settings only on applicable fields
  var enableCrmForFields = ['textarea', 'select', 'checkbox', 'radio', 'multiselect'];
  for (var i=0,len=enableCrmForFields.length; i<len; i++) {
  fieldSettings[enableCrmForFields[i]] += ", .crm_setting";
  }
  // When the field starts to show in the form editor, run this function
  $(document).bind("gform_load_field_settings", function(event, field, form){
  // Reset the fields
  $('.<?php echo esc_attr($this->id) ?>_map_type, .<?php echo esc_attr($this->id) ?>_map_enabled').attr('checked', false);
  $('.<?php echo esc_attr($this->id) ?>_field_list').html('');
   $(".<?php echo esc_attr($this->id) ?>_object").val(""); 
  if(typeof field.crm_map_enabled !== "undefined" && field.crm_map_enabled=="<?php echo esc_attr($this->id) ?>") { ///console.log(field.crm_map_enabled);
  var obj=$("#vx_check_"+field.crm_map_enabled); 
    if(!obj.length){ 
      return;
  }
  /// UpdateFieldChoices(field.type);
  //LoadFieldChoices(field);
  var div=obj.parents(".crm_setting");
  obj.prop('checked',true);
  //crm_checkbox(obj);
  var map_type="once"
  if(field.crm_map_type == "live"){
  map_type="live"; 
  }  
  div.find(".<?php echo esc_attr($this->id) ?>_map_type[value='"+map_type+"']").prop('checked',true).trigger('change'); 
  setTimeout(function(){
  toggle_choices(obj);   
  },1000);     
  
 /// div.find(".<?php echo esc_attr($this->id) ?>_object").val(field.crm_object).trigger('change');
 var account_sel=div.find(".<?php echo esc_attr($this->id) ?>_account");
var crm_object=field.crm_object ? field.crm_object : '';
var crm_account=field.crm_account ? field.crm_account : '';
account_sel.val(crm_account);
 vx_load_account(account_sel,crm_object);
  }
  $('.<?php echo esc_attr($this->id) ?>_map_enabled').trigger('change'); 
  //select object
  
  });
  $('.<?php echo esc_attr($this->id) ?>_map_enabled').unbind('change click').on('click change', function () {
  
  crm_checkbox($(this));
  });
  $('.<?php echo esc_attr($this->id) ?>_map_type').unbind('change click').on('click change', function () {
  var val=$(this).val();
  SetFieldProperty('crm_map_type',val);
  var obj=$(this); 
   toggle_choices(obj);
  });
  
  
  $('.crm_setting').on('click','.<?php echo esc_attr($this->id) ?>_map_field',function(e){
  var div=$(this).parents(".crm_setting");
  var check=div.find(".crm_map_enabled").is(":checked");
  if(!check){
   return;   
  }
  var obj=$(this);
  var field = GetSelectedField();
  var inputType = GetInputType(field);
  var div=$(this).parents('.crm_radio_tr'); 
  SetFieldProperty('crm_option',$(this).val());
  var setting=$(this).parents(".crm_setting");
    var map_enabled="";
  if(setting.find(".crm_map_enabled:checked").length){
      map_enabled=setting.find(".crm_map_enabled:checked").val();
  }
  SetFieldProperty('crm_map_enabled',map_enabled);
    SetFieldProperty('crm_account',setting.find('.sel_account').val());
  SetFieldProperty('crm_object',setting.find('.sel_object').val());
  // We add the Object choices in the list to the field choices.
  field["choices"] = new Array();
  
  div.find('li').each(function() {
  choice = new Choice();
  choice.text = $(this).data('label').toString();
  choice.value = $(this).data('value').toString();
  choice.isSelected = $(this).data('default')*1;

  field["choices"].push(choice);
  });
  // We update the field choices in the field display
  UpdateFieldChoices(field.type);
  LoadFieldChoices(field); 
  toggle_choices(obj);
  })
  //.unbind('change')
  $('.<?php echo esc_attr($this->id) ?>_account').on('change',function(e){
vx_load_account($(this),field.crm_object);
  });

$(document).on('change','.<?php echo esc_attr($this->id) ?>_object',function(e){ 
vx_load_lists($(this),true);
  });
    function crm_checkbox(obj){
  var checked = obj.is(':checked');
  var div = obj.parents('.crm_setting');
  var id=obj.val();
  var val="";
  if(checked === true) {
  div.find('#'+id+'_map_ui').show();
        var map_enabled=div.find(".crm_map_enabled").val();
  var map_type=div.find(".crm_map_type:checked").val();
  var option=div.find(".crm_map_field:checked").val(); 

  var object=div.find(".sel_object").val();
  SetFieldProperty('crm_map_enabled',map_enabled);
  SetFieldProperty('crm_map_type',map_type); 
  if(object){
  SetFieldProperty('crm_object',object);
  } 
  if(option){
  SetFieldProperty('crm_option',option);
  }
  } else {
  div.find('#'+id+'_map_ui').hide();
  if(typeof field.crm_map_enabled == "undefined" || field.crm_map_enabled ==id){
  SetFieldProperty('crm_map_enabled',"");
  SetFieldProperty('crm_map_type',false); 
  SetFieldProperty('crm_object',false); 
  SetFieldProperty('crm_option',false); 
 }
  }
  toggle_choices(obj);

  }
   
  function vx_load_account(elem,object){
   object= object ? object : '';
  var div=elem.parents(".crm_setting");
  var check_box=div.find(".crm_map_enabled");
  var check=check_box.is(":checked");
  if(!check){
   return;   
  }
    var val=elem.val();
  SetFieldProperty('crm_account',val);
//  SetFieldProperty('crm_object',object); 
  var form_id=div.find(".crm_form_id").val();
  var action=elem.attr('id');
  if(!action || action == ""){
  return;
  }
   div.find(".crm_wait_account").css('display','inline-block');
  jQuery.post(ajaxurl,{action:action,select_object:'<?php echo wp_create_nonce("select_object") ?>',account:val,object:object,form_id:form_id},function(res){
  div.find(".<?php echo esc_attr($this->id) ?>_objects").html(res);
  div.find("#<?php echo esc_attr($this->id) ?>_field_group").slideDown(); 
  sel_radio_option();
  ///div.find('._field_list').trigger('load');
  //eval(res);
  div.find(".crm_wait_account").hide();
  toggle_choices(check_box);  
  }) 
  }
  
  function vx_load_lists(elem,refresh){ 
        var div=elem.parents(".crm_setting");

  var val=elem.val(); SetFieldProperty('crm_object',val);
 
  var form_id=div.find(".crm_form_id").val();
  var action=elem.attr('id');
var account=div.find('.<?php echo esc_attr($this->id) ?>_account').val();
   div.find(".crm_wait").css('display','inline-block');
  jQuery.post(ajaxurl,{action:action,select_object:'<?php echo wp_create_nonce("select_object") ?>',object:val,form_id:form_id,account:account,refresh:refresh},function(res){
  div.find(".<?php echo esc_attr($this->id) ?>_field_list").html(res);
  div.find("#<?php echo esc_attr($this->id) ?>_field_group").slideDown();
  ///div.find('._field_list').trigger('load');
sel_radio_option();
  //eval(res);
  div.find(".crm_wait").hide();
//  toggle_choices(check_box);  
  }) 
  }
 function sel_radio_option(){
       if(typeof field.crm_option !="undefined" && field.crm_option!=""){ 
  jQuery("#<?php echo esc_attr($this->id) ?>_map_field_"+field.crm_option).attr('checked',true).trigger('change');
  /* var field = GetSelectedField();
  UpdateFieldChoices(field.type);
  LoadFieldChoices(field);*/   
  }
 }    
  // We disable editing of the choices and remove
  function toggle_choices(obj) { 
  var field = GetSelectedField(); 
  var div=jQuery("#field_"+field.id); 
  var choices=div.find("#field_choices");
  // If it's not yet set, or if you're just populating choices one-time, no disabling.
  if(field.crm_map_enabled && field.crm_map_type && field.crm_map_type == "live") {
  // Disable modifying the choices.
 var setting=obj.parents(".crm_setting");
 if(setting.find(".crm_map_field:checked").length){
  jQuery('.field-choice-input').attr('disabled', true);
  jQuery('.gfield_choice_checkbox').attr('disabled', true);
  // Hide sorting, add, and remove choices images
  jQuery('.gf_insert_field_choice, .gf_delete_field_choice, .field-choice-handle,  .choices_setting input.button').hide();
  jQuery('#field_choice_values_enabled').parent('div').hide();
 }
  } else {
  // Enable modifying the choices.
  jQuery('.field-choice-input').attr('disabled', false);
  jQuery('.gfield_choice_checkbox').attr('disabled', false);
  // Show sorting, add, and remove choices images
  jQuery('.gf_insert_field_choice, .gf_delete_field_choice, .field-choice-handle, .choices_setting input.button').show();
  jQuery('#field_choice_values_enabled').parent('div').show();
  }
  }
  });

  </script>
  <?php
  }
  /**
  * add tooltips
  * 
  * @param mixed $tooltips
  */
  public function tooltips($tooltips){
  $tooltips['vx_map_live_'.$this->id] = sprintf(__('%sUpdate from Salesforce %s If you update a picklist in Salesforce, the modifications will be added to your form without having to edit the field Choices in Gravity Forms. You will not be able to edit the Choices in Gravity Forms, you can only update them in Salesforce. The order of Choices as well as the default values are determined by the Salesforce picklist field settings.', 'gravity-forms-salesforce-crm'), '<h6>','</h6>');
  $tooltips['vx_map_once_'.$this->id] = sprintf(__('%sUpdate from Salesforce %s Field Choices will not be updated live from Salesforce and are editable in Gravity Forms. If you make an edit in Salesforce, it will not be updated in your form.', 'gravity-forms-salesforce-crm'), '<h6>','</h6>');
  return $tooltips;
  }
  /**
  * Add "Use CRM Picklists" checkbox in admin
  * 
  * @param mixed $position
  * @param mixed $form_id
  */
  public function use_as_entry_link_settings($position, $form_id){
  //create settings on position 50 (right after Admin Label)
  if($position === -1){
    $form = RGFormsModel::get_form_meta($form_id);
  $data=$this->get_data_object();
  $accounts =$data->get_accounts();

   ?>
  <li class="use_as_entry_link crm_setting field_setting">
    <input type="hidden" class="crm_form_id" value="<?php echo esc_attr($form_id) ?>">
  <label for="vx_check_<?php echo esc_attr($this->id) ?>">
  <input type="checkbox" autocomplete="off" class="<?php echo esc_attr($this->id) ?>_map_enabled crm_map_enabled" id="vx_check_<?php echo esc_attr($this->id) ?>" name="crm_map_enabled" value="<?php echo esc_attr($this->id) ?>" /> <?php esc_html_e("Enable Salesforce Field Mapping?", 'gravity-forms-salesforce-crm'); ?>
  </label>
  
  <div id="<?php echo esc_attr($this->id) ?>_map_ui" style="display: none;">
  
  <label for="<?php echo esc_attr($this->id) ?>_map_type_live">
  <input type="radio" class="<?php echo esc_attr($this->id) ?>_map_type crm_map_type" id="<?php echo esc_attr($this->id) ?>_map_type_live" name="<?php echo esc_attr($this->id) ?>_map_type" value="live" /> <?php esc_html_e("Live Remote Field Mapping ", 'gravity-forms-salesforce-crm'); gform_tooltip("vx_map_live_".$this->id); ?>
  <span class="howto" style="padding-left:1.25em;"><?php esc_html_e("Field Choices will be synced from Salesforce picklist values.", 'gravity-forms-salesforce-crm'); ?></span>
  </label>
  <label for="<?php echo esc_attr($this->id) ?>_map_type_once">
  <input type="radio" class="<?php echo esc_attr($this->id) ?>_map_type crm_map_type" id="<?php echo esc_attr($this->id) ?>_map_type_once" name="<?php echo esc_attr($this->id) ?>_map_type" value="once" /> <?php esc_html_e("Only Populate Choices ", 'gravity-forms-salesforce-crm'); gform_tooltip("vx_map_once_".$this->id) ?>
  <span class="howto" style="padding-left:1.25em;"><?php esc_html_e("Field Choices will not be updated live and are editable.", 'gravity-forms-salesforce-crm'); ?></span>
  </label>
    <label for="crm_accounts_list" style="width: 100px;" class="inline"><?php esc_html_e("Choose Account", 'gravity-forms-salesforce-crm'); ?></label>
      <select autocomplete="off" id="object_area_<?php echo esc_attr($this->id) ?>" name="<?php echo esc_attr($this->id) ?>_account" class="<?php echo esc_attr($this->id) ?>_account fieldwidth-4 sel_account">
  <option value=""><?php esc_html_e("Select a Salesforce Account", 'gravity-forms-salesforce-crm'); ?></option>
  <?php
  foreach ($accounts as $k=>$v){
      if($v['status'] == "1"){
  ?>
  <option value="<?php echo esc_html($v['id']) ?>"><?php echo esc_html($v['name']) ?></option>
  <?php
      }
  }
  ?>
  </select>
    <span class="fa fa-spinner fa-spin crm_ajax crm_wait_account" style="display: none;"></span>
 <div class="<?php echo esc_attr($this->id) ?>_objects"></div>
  </div>

  </li>
  
  <?php
  } // End if $position === 500
  }
 public function object_area($id,$object){
     $info=$this->get_info($id);
     $lists =$this->get_objects($info);
     ?>
       <label for="crm_object_list" style="width: 100px;" class="inline"><?php esc_html_e("Choose Object", 'gravity-forms-salesforce-crm'); ?></label>
  <?php
  if(!is_array($lists)) {
  echo esc_html__("Could not load Salesforce objects.", 'gravity-forms-salesforce-crm');
  } else { ?>

  <select autocomplete="off" id="object_picklist_<?php echo esc_attr($this->id) ?>" name="crm_object_type" class="<?php echo esc_attr($this->id) ?>_object fieldwidth-4 sel_object">
  <option value=""><?php esc_html_e("Select a Salesforce Object", 'gravity-forms-salesforce-crm'); ?></option>
  <?php
  foreach ($lists as $k=>$v){
      $sel='';
      if($k == $object){
      $sel='selected="selected"';    
      }
  ?>
  <option value="<?php echo esc_html($k) ?>" <?php echo $sel ?>><?php echo esc_html($v) ?></option>
  <?php
  }
  ?>
  </select><span class="fa fa-spinner fa-spin crm_ajax crm_wait" style="display: none;"></span>
  <div class="<?php echo esc_attr($this->id) ?>_field_list">
  <?php
  echo $this->fields_html($id,$object);   
  ?>
  </div>
     <?php
  }
 } 

}
}
new vxg_salesforce_form;
