<?php
/**
* Plugin Name: WP Gravity Forms Salesforce
* Description: Integrates Gravity Forms with Salesforce allowing form submissions to be automatically sent to your Salesforce account 
* Version: 1.4.5
* Requires at least: 4.7
* Author URI: https://www.crmperks.com
* Plugin URI: https://www.crmperks.com/plugins/gravity-forms-plugins/gravity-forms-salesforce-plugin/
* Author: CRM Perks.
* Text Domain: gravity-forms-salesforce-crm
* Domain Path: /languages/ 
*/
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


if( !class_exists( 'vxg_salesforce' ) ):

class vxg_salesforce {

  
 public  $url = 'https://www.crmperks.com';

  public  $crm_name = 'salesforce';
  public  $id = 'vxg_salesforce';
  public  $domain = 'vxg-sales';
  public  $version = "1.4.5";
  public  $update_id = '30001';
  public  $min_gravityforms_version = '1.3.9';
  public $type = 'vxg_salesforce_pro';
  public  $fields = null;
  public  $data = null;

  private $filter_condition;
  private $plugin_dir= '';
  private $temp= '';
  private $crm_arr= false;
  private $entry;
  private $form;
  private $is_api=true;
  public $notice_js= false;
  public static $title='Gravity Forms Salesforce Plugin';  
  public static $path = ''; 
  public static $slug = '';
  public static $debug_html = '';
  public static $save_key='';  
  public static  $lic_msg = '';
  public static $db_version='';  
  public static $vx_plugins;  
  public static $note;
  public static $feeds_res;    
  public static $gf_status='';    
  public static $plugin='';    
  public static $gf_status_msg='';
   public static $is_pr=true;
  public static $api_timeout;      
  public static $del_files=array();      
    

 public function instance(){
    self::$path=$this->get_base_path(); 
  add_action( 'plugins_loaded', array( $this, 'setup_main' ) );
register_deactivation_hook(__FILE__,array($this,'deactivate'));
register_activation_hook(__FILE__,(array($this,'activate')));


 } 
  
  /**
  * Plugin starting point. Will load appropriate files
  * 
  */
  public  function init(){
 
     
      self::$gf_status= $this->gravity_forms_status();
    if(self::$gf_status !== 1){
  add_action( 'admin_notices', array( $this, 'install_gf_notice' ) );
  $slug=$this->get_slug(); 
add_action( 'after_plugin_row_'.$slug, array( $this, 'install_gf_notice_plugin_row' ) );    
  return;
  } 

    $pro_file=self::$path . 'wp/crmperks-notices.php';
if(file_exists($pro_file)){ 
   include_once($pro_file); 
   self::$is_pr=false;
}else{
$this->plugin_api(true); 
 
self::$is_pr=true;
 $pro_file=self::$path . 'pro/add-ons.php';
if(file_exists($pro_file)){
include_once($pro_file);
} }
       
require_once(self::$path . "includes/crmperks-gf.php");
require_once(self::$path . "includes/plugin-pages.php");  

    if ( class_exists( 'GPDFAPIxx' ) ) {

        /* Get the individual PDF config */
       // $pdf  = GPDFAPI::get_pdf( 1, '5fba18c9c0304' );
      // $pdfs = GPDFAPI::get_entry_pdfs( 789 );
           $pdfs  = GPDFAPI::get_form_pdfs( 1 );
      //$pdf_path = GPDFAPI::create_pdf( 789, '5fba18c9c0304');
       // var_dump($pdfs); die();

        if( true === $pdf['active'] ) {
            //Do something if PDF is active
        } else {
            //Do something else if PDF is inactive
        }
    }
    

  }
   /**
  * install plugin
  * 
  */
  public function setup_main(){
  
  include_once(self::$path. "includes/edit-form.php");
 
        //handling post submission.  gform_after_submission runs after gform_replace_merge_tags
  add_action('gform_entry_created', array($this, 'gf_entry_created_before'), 99, 2); 
  //added via GF API
  add_action("gform_post_add_entry", array($this, 'gf_entry_created_before'), 40, 2);
    //update entry
  add_action('gform_after_update_entry', array($this, 'update_entry'),10,2); 
  //trash , restore entry
  add_action('gform_update_status', array($this, 'entry_status'),10,3);
  
if(self::$is_pr){
//  add_action("gform_post_payment_status", array($this, 'gf_entry_paid'), 10, 2); //$feed,$entry
add_action("gform_post_payment_completed", array($this, 'gf_entry_paid_normal'), 10, 2); //$entry,$pay_info
    
add_action('gform_after_submission', array($this, 'gf_entry_created_after'), 99, 2); 
add_action("gform_post_add_subscription_payment", array($this, 'gf_entry_paid_subscription'), 10, 2); //$entry,$pay_info
}
  
    add_filter("gform_confirmation", array($this, 'confirmation_error'));

        add_filter("gform_custom_merge_tags", array($this, 'add_tags'),10,4);
    add_filter( 'gform_replace_merge_tags', array($this,'replace_tags'), 10, 7 );
 

      if(is_admin()){
add_action('init', array($this,'init'));   
            //loading translations
  load_plugin_textdomain('gravity-forms-salesforce-crm', FALSE,  $this->plugin_dir_name(). '/languages/' );
  
  self::$db_version=get_option($this->type."_version");
  if(self::$db_version != $this->version && current_user_can( 'manage_options' )){
$this->install_plugin();
  update_option($this->type."_version", $this->version);
//    $log_str="Installing ".self::$title."  version=".$this->version;
//  $this->log_msg($log_str);
  }

  } 
  }

 public  function plugin_api($start_instance=false){
       if(empty(self::$path)){   self::$path=$this->get_base_path(); }  
     $file=self::$path . "pro/plugin-api.php";
    if(!class_exists('vxcf_plugin_api') && file_exists($file)){   
require_once($file);
}
if(class_exists('vxcf_plugin_api')){
 $slug=$this->get_slug();
 $settings_link=$this->link_to_settings();
 $is_plugin_page=$this->is_crm_page(); 
self::$plugin=new vxcf_plugin_api($this->id,$this->version,$this->type,$this->domain,$this->update_id,self::$title,$slug,self::$path,$settings_link,$is_plugin_page);
if($start_instance){
self::$plugin->instance();
}
} }
  public function install_plugin(){
        $data=$this->get_data_object();
  $data->update_table();
  //add post permissions
  require_once(self::$path . "includes/install.php"); 
  $install=new vxg_install_salesforce();
  $install->create_roles();  
  }
  public function install_gf_notice(){
        $message=self::$gf_status_msg;
  if(!empty($message)){
  $this->display_msg('admin',$message,'gravity'); 
     $this->notice_js=true; 
  
  }
  }
  /**
  * Install Gravity Forms Notice (plugin row)
  * 
  */
  public function install_gf_notice_plugin_row(){
  $message=self::$gf_status_msg;
  if(!empty($message)){
   $this->display_msg('',$message,'gravity');
  } 
  }
  /**
  * display admin notice
  * 
  * @param mixed $type
  * @param mixed $message
  * @param mixed $id
  */
  public function display_msg($type,$message,$id=""){
  //exp 
  global $wp_version;
  $ver=floatval($wp_version);
  if($type == "admin"){
     if($ver<4.2){
  ?>
    <div class="error vx_notice notice" data-id="<?php echo esc_html($id) ?>"><p style="display: table"><span style="display: table-cell; width: 98%"><span class="dashicons dashicons-megaphone"></span> <b><?php esc_html_e('Gravity Forms Salesforce Plugin','gravity-forms-salesforce-crm') ?>. </b><?php echo wp_kses_post($message);?> </span>
<span style="display: table-cell; padding-left: 10px; vertical-align: middle;"><a href="#" class="notice-dismiss" title="<?php esc_html_e('Dismiss Notice','gravity-forms-salesforce-crm') ?>">dismiss</a></span> </p></div>
  <?php
     }else{
  ?>
  <div class="error vx_notice notice is-dismissible" data-id="<?php echo esc_html($id) ?>"><p><span class="dashicons dashicons-megaphone"></span> <b><?php esc_html_e('Gravity Forms Salesforce Plugin','gravity-forms-salesforce-crm') ?>. </b> <?php echo wp_kses_post($message);?> </p>
  </div>    
  <?php
     }
  }else{
  ?>
  <tr class="plugin-update-tr"><td colspan="5" class="plugin-update">
  <style type="text/css"> .vx_msg a{color: #fff; text-decoration: underline;} .vx_msg a:hover{color: #eee} </style>
  <div style="background-color: rgba(224, 224, 224, 0.5);  padding: 9px; margin: 0px 10px 10px 28px "><div style="background-color: #d54d21; padding: 5px 10px; color: #fff" class="vx_msg"> <span class="dashicons dashicons-info"></span> <?php echo wp_kses_post($message) ?>
</div></div></td></tr>
  <?php
  }   
  }
   /**
  * admin_screen_message function.
  * 
  * @param mixed $message
  * @param mixed $level
  */
  public  function screen_msg( $message, $level = 'updated') {
  echo '<div class="'. esc_attr( $level ) .' notice is-dismissible"><p>';
  echo wp_kses_post($message);
  echo '</p></div>';
  } 
public function add_tags( $merge_tags, $form_id, $fields, $element_id ) {
      $data_db=$this->get_data_object(); 
  $feeds=$data_db->get_feed_by_form($form_id,true);
  foreach($feeds as $v){
    $merge_tags[] = array('label' => substr($v['name'],0,20).' Salesforce Link', 'tag' => '{salesforcelink_'.$v['id'].'}');
    $merge_tags[] = array('label' => substr($v['name'],0,20).' Salesforce ID', 'tag' => '{salesforceid_'.$v['id'].'}');
  }
    return $merge_tags;
}
public function replace_tags( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {
 
            
    if(!empty($form['id']) && strpos( $text, '{salesforce' ) !== false ){    
      $data_db=$this->get_data_object(); 
  $feeds=$data_db->get_feed_by_form($form['id'],true);
$tags=array();
  foreach($feeds as $v){
      $id=$v['id'];          
      if( !empty(self::$feeds_res[$id]) ){       
 $link= !empty(self::$feeds_res[$id]['link']) ? self::$feeds_res[$id]['link'] : '#'.self::$feeds_res[$id]['id'];         
   $tags['{salesforcelink_'.$v['id'].'}']=$link;   
   $tags['{salesforceid_'.$v['id'].'}']=self::$feeds_res[$id]['id'];   
      }
  }
 $text = str_replace( array_keys($tags), array_values($tags), $text );
    } //var_dump($text); //die();  
     return $text;
}


/**
* Gravity forms status
* 
*/
  public  function gravity_forms_status() {
  
  $installed = 0;
  if(!class_exists('RGForms')) {
  if(file_exists(WP_PLUGIN_DIR.'/gravityforms/gravityforms.php')) {
  $installed=2;   
  }
  }else{
  $installed=1;
  if(!$this->is_gravityforms_supported()){
  $installed=3;   
  }      
  }
  if($installed !=1){
    if($installed === 0){ // not found
  $message = sprintf(__("%sGravity Forms%s is required. %sPurchase it today!%s", 'gravity-forms-salesforce-crm'), "<a href='http://www.gravityforms.com/'>", "</a>", "<a href='http://www.gravityforms.com/'>", "</a>");   
  }else if($installed === 2){ // not active
  $message = sprintf(__('Gravity Forms is installed but not active. %sActivate Gravity Forms%s to use the Gravity Forms Salesforce Plugin','gravity-forms-salesforce-crm'), '<strong><a href="'.wp_nonce_url(admin_url('plugins.php?action=activate&plugin=gravityforms/gravityforms.php'), 'activate-plugin_gravityforms/gravityforms.php').'">', '</a></strong>');  
  } else if($installed === 3){ // not supported
  $message = sprintf(__("A higher version of %sGravity Forms%s is required. %sPurchase it today!%s", 'gravity-forms-salesforce-crm'), "<a href='http://www.gravityforms.com/'>", "</a>", "<a href='http://www.gravityforms.com/'>", "</a>");
  }  
  self::$gf_status_msg=$message;
  }
  return $installed;   
  }
  /**
  * display error to admin only in form front
  * 
  * @param mixed $confirmation
  * @param mixed $form
  * @param mixed $lead
  * @param mixed $ajax
  */
  public  function confirmation_error($confirmation, $form = '', $lead = '', $ajax ='' ) {
  if(current_user_can('administrator') && !empty($_REQUEST['VXGSalesforceError'])) { 
  if(is_array($_REQUEST['VXGSalesforceError'])){ $_REQUEST['VXGSalesforceError']=json_encode($_REQUEST['VXGSalesforceError']); }
  $confirmation .= sprintf(__('%sThe entry was not added to Salesforce because: %s. %sYou are only being shown this because you are an administrator. Other users will not see this message.%s', 'gravity-forms-salesforce-crm'), '<div class="error" style="text-align:center; color:#790000; font-size:14px; line-height:1.5em; margin-bottom:16px;background-color:#FFDFDF; margin-bottom:6px!important; padding:6px 6px 4px 6px!important; border:1px dotted #C89797">','<strong>'.esc_html($_REQUEST['VXGSalesforceError']). '</strong>', '<br /><em>', '</em></div>');
  }
  return $confirmation;
  }
  
  /**
  * Returns true if the current page is an Feed pages. Returns false if not
  * 
  * @param mixed $page
  */
  public  function is_crm_page($page=""){
  if(empty($page)) {
  $page = $this->post("page");
  }
  if(isset($_GET['subview'])){
   $page = $this->post("subview");   
  }
  return $page == $this->id;
  } 
  /**
  * Called when entry is manually updated in the Single Entry view of Gravity Forms.
  * 
  * @param mixed $form
  * @param mixed $entry_id
  */
  public  function manual_export( $form, $entry_id = NULL ) {

  global $plugin_page;

  // Is this the Gravity Forms entries page?
  if(false === ($this->is_gravity_page('gf_entries') && rgget("view") == 'entry' && (rgget('lid') || !rgblank(rgget('pos'))))) {
  return;
  }
  $entry=array();
  // Both admin_init and gforms_after_update_entry will have this set
  if( empty( $_POST['gforms_save_entry'] ) || empty( $_POST['action'] ) ) { return; }
  
  // Different checks since admin_init runs in both cases but we need to wait for entry update
  $current_hook = current_filter(); 

  if( $current_hook == 'admin_init' && empty( $_POST[$this->id.'_send'] ) ) { return; } 
  if( $current_hook == 'gform_after_update_entry' && empty( $_POST[$this->id.'_update'] ) ) { return; }
  
  // Verify authenticity of request
  check_admin_referer('gforms_save_entry', 'gforms_save_entry');
  
  // For admin_init hook, get the entry ID from the URL
  if(empty($entry_id)) {
  $entry_id = rgget('lid');
  $form_id = rgget('id');
  
  // fetch alternative entry id: look for gf list details when using pagination
  if(empty($entry_id)) {
  $entry_id=$this->get_entry_id($form_id);
  }
  $form = RGFormsModel::get_form_meta($form_id);
  }
  $entry=$this->get_gf_entry($entry_id);
  if(!current_user_can($this->id."_send_to_crm")){ 
         return;  
       }
  // Export the entry
  $push=$this->push($entry, $form,"",true); 
    if(!empty($push['msg'])){
  $this->screen_msg($push['msg'],$push['class']);  
  }
  // Don't send twice.
  unset($_POST[$this->id.'_update']);
  unset($_POST[$this->id.'_send']);
  }
  /**
  * get entry id 
  * 
  * @param mixed $form_id
  */
public function get_entry_id($form_id){
    $entry_id='';
  $position = rgget('pos');
  $paging = array('offset' => $position, 'page_size' => 1);
  
  $entries = GFAPI::get_entries($form_id, array(), null, $paging);
  
  if(!empty($entries)) { 
  // pluck first entry to use id from, should always only be one
  $entry = array_shift($entries);
  $entry_id = $entry['id'];
  } 
  
  return $entry_id;
}
  /**
  * web2lead fields
  *  
  * @param mixed $module
  * @param mixed $map
  */
  public function web_fields($module,$map){
  ////////////////////////////
  $web['Lead']='{"1":{"label":"First Name","max":"40","name":"first_name","type":"text"},"2":{"label":"Last Name","max":"80","name":"last_name","type":"text","req":"true"},"3":{"label":"Email","max":"80","name":"email","type":"text","req":"true"},"4":{"label":"Company","max":"40","name":"company","type":"text"},"5":{"label":"City","max":"40","name":"city","type":"text"},"6":{"label":"State/Province","max":"20","name":"state","type":"text"},"7":{"label":"Salutation","name":"salutation","type":"select"},"8":{"label":"Title","max":"40","name":"title","type":"text"},"9":{"label":"Website","max":"80","name":"URL","type":"text"},"10":{"label":"Phone","max":"40","name":"phone","type":"text"},"11":{"label":"Mobile","max":"40","name":"mobile","type":"text"},"12":{"label":"Fax","max":"40","name":"fax","type":"text"},"13":{"label":"Address","name":"street","type":"select"},"14":{"label":"Zip","max":"20","name":"zip","type":"text"},"15":{"label":"Country","max":"40","name":"country","type":"text"},"16":{"label":"Description","name":"description","type":"select"},"17":{"label":"Lead Source","name":"lead_source","type":"select"},"18":{"label":"Industry","name":"industry","type":"select"},"19":{"label":"Rating","name":"rating","type":"select"},"20":{"label":"Annual Revenue","name":"revenue","type":"text"},"21":{"label":"Employees","name":"employees","type":"text"},"22":{"label":"Email Opt Out","name":"emailOptOut","type":"checkbox"},"23":{"label":"Fax Opt Out","name":"faxOptOut","type":"checkbox"},"24":{"label":"Do Not Call","name":"doNotCall","type":"checkbox"}}';
  $web['Case']='{"1":{"label":"Contact Name","max":"80","name":"name","type":"text"},"2":{"label":"Email","max":"80","name":"email","type":"text"},"3":{"label":"Phone","max":"40","name":"phone","type":"text"},"4":{"label":"Subject","max":"80","name":"subject","type":"text"},"5":{"label":"Description","name":"description","type":"select"},"6":{"label":"Company","max":"80","name":"company","type":"text"},"7":{"label":"Type","name":"type","type":"select"},"8":{"label":"Status","name":"status","type":"select"},"9":{"label":"Case Reason","name":"reason","type":"select"},"10":{"label":"Priority","name":"priority","type":"select"}}'; 
  //////////////////
  if(isset($web[$module])){
  $fields_arr=json_decode($web[$module],true);
  $fields=array();
  $phone=array('phone','mobile','fax');

  foreach($fields_arr as $k=>$v){
  if(!vxg_salesforce::$is_pr && in_array($v['name'],$phone)){ continue; }
  $fields[$v['name']]=$v;    
  }
  foreach($map as $k=>$v){
  if(isset($v['name_c']))
  $fields[$k]=$v;   
  }
  }

  return $fields;
  }  
 

  /**
  * settings link
  * 
  * @param mixed $escaped
  */
  public  function link_to_settings( $escaped = true ) {
  
  $url = admin_url('admin.php?page=gf_settings&subview='.$this->id);
  
  return  $url;
  }

  /**
  * Get CRM info
  * 
  */
  public function get_info($id){
$data=$this->get_data_object();
      $info=$data->get_account($id);
  $info_arr=$data=array();  $meta=array(); 
if(is_array($info)){
if(!empty($info['data'])){ 

    $info['data']=trim($info['data']);  
    if(strpos($info['data'],'{') !== 0){
        $info['data']=$this->de_crypt($info['data']);
    }
  $info_arr=json_decode($info['data'],true);
if(!is_array($info_arr)){
  $info_arr=array();
}
}
$info_arr['time']=$info['time']; 
$info_arr['id']=$info['id']; 
 $info['data']=$info_arr;
if(!empty($info['meta'])){ 
  $meta=json_decode($info['meta'],true); 
}
$info['meta']=is_array($meta) ? $meta : array();   

if(!empty($info['time'])){ 
$info['time']=strtotime($info['time']); 
}
}
  return $info;    
  }
  /**
  * update account
  * 
  * @param mixed $data
  * @param mixed $id
  */
  public function update_info($data,$id) {

if(empty($id)){
    return;
}

 $time = current_time( 'mysql' ,1);

  $sql=array('updated'=>$time);
  if(is_array($data)){

  
    if(isset($data['meta'])){
  $sql['meta']= json_encode($data['meta']);    
  }
  if( isset($data['data']) && is_array($data['data'])){
      $_data=$this->get_data_object();
     $acount=$_data->get_account($id);
     if(empty($acount['time'])){
  $sql['time']= $time;      
  } 
  $sql['status']='2';
  if(isset($data['data']['class'])){
  $sql['status']= $data['data']['class'] == 'updated' ? '1' : '2'; 
  }
  if(isset($data['data']['meta'])){
      unset($data['data']['meta']);
  }
  if(isset($data['data']['status'])){
      unset($data['data']['status']);
  }
  if(isset($data['data']['name'])){
     $sql['name']=$data['data']['name']; 
  // unset($data['data']['name']);
  }else if(isset($_GET['id'])){
      $sql['name']="Account #".$this->post('id');  
  }
  
    $enc_str=json_encode($data['data']);
 // $enc_str=$this->en_crypt($enc_str);
  $sql['data']=$enc_str;
  }
  } 


 $data=$this->get_data_object();
$result = $data->update_account($sql,$id);

return $result;
}

  /**
  * gravity forms field values, modify check boxes etc
  * 
  * @param mixed $entry
  * @param mixed $form
  * @param mixed $gf_field_id
  * @param mixed $crm_field_id
  * @param mixed $custom
  */
  public  function verify_field_val($entry,$form,$gf_field_id,$crm_field_id="",$custom=""){
  $value=false;
/*  if(empty($field)){
      return $value;
  }*/

  if(isset($entry[$gf_field_id])){   
  $value=maybe_unserialize($entry[$gf_field_id]);
  if(in_array($gf_field_id,array('date_created','payment_date'))){
      $value=strtotime($value);
      if(!$this->is_api){ //convert utc to local for web2lead
       $offset=get_option('gmt_offset');
     $offset=$offset*3600;    
     $value+=$offset;
    $format=get_option( 'date_format' ).' '.get_option( 'time_format' );
     $value=date($format, $value); 
      }else{
     $value=date('c', $value);      
      }  
  }
  if(is_numeric($gf_field_id)){
  $field = RGFormsModel::get_field($form, $gf_field_id);
  if(isset($field->type) && in_array($field->type,array('option','product')) ){
        $found=strpos($value,'|');
      if($found){
      if(!empty($custom)){ //it is empty with process_tags function
    $value=substr($value,$found+1);
      }else{
    $value=substr($value,0,$found);
      }     
      }  
  }
  if( (isset($field->storageType) && $field->storageType == 'json') || $field->type == 'fileupload' ){
   $value_temp=json_decode($value,1);   if(!empty($value_temp)){ $value=$value_temp; } 
  }
    if(in_array($field->type, array('survey','quiz')) && !empty($field->choices)){
      $val_temp=explode(':',$value);
      if(count($val_temp) > 1){ $value=$val_temp[1]; }
    foreach($field->choices  as $v){
        if($v['value'] == $value){
   $value=!empty($v['score']) ? $v['score'] :  $v['text'];
   break;         
        }
    }  
  }
   if($field->type == 'date' && !empty($value) ){
  
    $formats=array('mdy'=>'m/d/Y','dmy'=>'d/M/Y','dmy_dash'=>'d-m-Y','dmy_dot'=>'d.m.Y','ymd_slash'=>'Y/m/d','ymd_dash'=>'Y-m-d','ymd_dot'=>'Y.m.d');
    $date_formate=$field->dateFormat;
    if($date_formate == 'mdy' && $this->is_api){ //do not convert with web2lead
      $date_formate='dmy'; 
        $temp_date=explode('/',$value); 
        if(count($temp_date)>2){
      $value=$temp_date[1].'-'.$temp_date[0].'-'.$temp_date[2]; 
       
        }   
    }
 
    if( !empty($date_formate) && isset($formats[$date_formate])){
   $value=date($formats[$date_formate],strtotime($value));     
    }
   
   }else if($field->type == 'list' && is_array($value)){
       $v_temp=array();
       foreach($value as $v){
           if(is_array($v)){
           $v=trim(implode(', ',array_values($v)));    
           }
        $v_temp[]=$v;   
       }
     $value=trim(implode(" - \n",$v_temp));  
   } }

  $val=true;
  }else{ //check if full address
      if($gf_field_id=='entry_url'){
  $value=add_query_arg(array('page'=>'gf_entries','view'=>'entry','lid'=>$entry['id'],'id'=>$entry['form_id']), admin_url('admin.php'));
   $val=true;
    }else if($gf_field_id=="form_title"){
  $value=$form['title'];
  $val=true;
  }else if(strpos($gf_field_id,'gravitypdf:') === 0){
          if ( class_exists( 'GPDFAPI' ) ) {
              $pdf_id=explode(":",$gf_field_id);
              if(!empty($pdf_id[1])){
      $value = GPDFAPI::create_pdf( $entry['id'], $pdf_id[1]);
      if ( is_wp_error( $value ) || !is_file( $value ) ) {
      $value='';    
      }else{ self::$del_files[]=$value; }
      
              }
          }
  $val=true;
  }else{
   $field = RGFormsModel::get_field($form, $gf_field_id);
   if(!empty($field)){
   if(isset($field->type) && $field->type == "address"){
  $address_type="";
  if($crm_field_id!=""){
  $address_type=isset($custom[$crm_field_id]['type']) && $custom[$crm_field_id]['type'] == "address" ? "json" :  "";
  }
  $value=$this->get_address($entry,$gf_field_id,$address_type);  
  $val=true;
   }else if(is_numeric($gf_field_id)){
  // This is for checkboxes
  $elements = array();
    foreach($entry as $key => $val_e) {
      if(is_numeric($key) && floor($key) == floor($gf_field_id) && !empty($val_e)) { 
          $elements[] = htmlspecialchars($val_e);
      }}
  if(count($elements)>0){
  $value=$elements;   $val=true;
  }        
  
  }
   }else if(isset($_REQUEST[$gf_field_id])){ 
    $value=$this->post($gf_field_id);   
   }
  }
  }
  if($value && is_array($value)){
 // $value=implode(", ",$value);
  }
  return $value;        
  }
  /**
  * filter enteries
  * 
  * @param mixed $feed
  * @param mixed $entry
  * @param mixed $form
  */
  public  function check_filter($feed,$entry,$form){
  $filters=$this->post('filters',$feed);
  $final=$this->filter_condition=null;
  if(is_array($filters)){
   $time=current_time('timestamp'); 
   foreach($filters as $filter_s){
  $check=null; $and=null;  $and_c=array();
  if(is_array($filter_s)){
  foreach($filter_s as $filter){
  $field=$filter['field'];
  $fval=$filter['value'];
  $val=$this->verify_field_val($entry,$form,$field);
   if(is_array($val)){ $val=implode(' ',$val); }
  switch($filter['op']){
  case"is": $check=$fval == $val;     break;
  case"is_not": $check=$fval != $val;     break;
  case"contains": $check=preg_match('/'.$fval.'/i', $val) > 0;     break;
  case"not_contains": $check=preg_match('/'.$fval.'/i', $val) === 0;     break;
  case"is_in": $check=strpos($fval,$val) !==false;     break;
  case"not_in": $check=strpos($fval,$val) ===false;     break;
  case"starts": $check=strpos($val,$fval) === 0;     break;
  case"not_starts": $check=strpos($val,$fval) !== 0;     break;
  case"ends": $check=(strrpos($val,$fval)+strlen($fval)) == strlen($val);   break;
  case"not_ends": $check=(strrpos($val,$fval)+strlen($fval)) != strlen($val);  break;
  case"less": $check=(float)$val<(float)$fval; break;
  case"greater": $check=(float)$val>(float)$fval;  break;
  case"less_date": $check=strtotime($val,$time) < strtotime($fval,$time);  break;
  case"greater_date": $check=strtotime($val,$time) > strtotime($fval,$time);  break;
  case"equal_date": $check=strtotime($val,$time) == strtotime($fval,$time);  break;
  case"empty": $check=$val == "";  break;
  case"not_empty": $check=$val != "";  break;
  }
  //if($field == ''){ $check=true;} //user did not select any field
  $and_c[]=array("check"=>$check,"field_val"=>$fval,"input"=>$val,"field"=>$field,"op"=>$filter['op']);
  if($check !== null){
  if($and !== null){
  $and=$and && $check;    
  }else{
  $and=$check;    
  }   
  }  
  } //end and loop filter
  }
  if($and !== null){
  if($final !== null){
  $final=$final || $and;  
  }else{
  $final=$and;
  }    
  }
    $this->filter_condition[]=$and_c;
  } // end or loop
  }
//  var_dump($final); die();
  return $final === null ? true : $final;
  }
  
  /**
  * get address components
  *  
  * @param mixed $entry
  * @param mixed $field_id
  * @param mixed $type
  */
  private  function get_address($entry, $field_id,$type=""){
  $street_value = str_replace("  ", " ", trim($entry[$field_id . ".1"]));
  $street2_value = str_replace("  ", " ", trim($entry[$field_id . ".2"]));
  $city_value = str_replace("  ", " ", trim($entry[$field_id . ".3"]));
  $state_value = str_replace("  ", " ", trim($entry[$field_id . ".4"]));
  $zip_value = trim($entry[$field_id . ".5"]);
  if(method_exists('GF_Field_Address','get_country_code')){
  $field_c=new GF_Field_Address();
  $country_value=$field_c->get_country_code(trim($entry[$field_id . ".6"]));
  }else{
  $country_value = GFCommon::get_country_code(trim($entry[$field_id . ".6"]));       
  }
  $country =trim($entry[$field_id . ".6"]);
  $address = $street_value;
  $address .= !empty($address) && !empty($street2_value) ? "  $street2_value" : $street2_value;
  if($type =="json"){
  $arr=array("street"=>$address,"city"=>$city_value,"state"=>$state_value,"zip"=>$zip_value,"country"=>$country);
  return json_encode($arr);
  }
  $address .= !empty($address) && (!empty($city_value) || !empty($state_value)) ? "  $city_value" : $city_value;
  $address .= !empty($address) && !empty($city_value) && !empty($state_value) ? "  $state_value" : $state_value;
  $address .= !empty($address) && !empty($zip_value) ? "  $zip_value" : $zip_value;
  $address .= !empty($address) && !empty($country_value) ? "  $country_value" : $country_value;
  
  return $address;
  }
  /**
  * if gravity forms page
  * 
  * @param mixed $page
  */
  public  function is_gravity_page($page = array()){
  if(!class_exists('RGForms')) { return false; }
  $current_page = trim(strtolower(RGForms::get("page")));
  if(empty($page)) {
  $gf_pages = array("gf_edit_forms","gf_new_form","gf_entries","gf_settings","gf_export","gf_help");
  } else {
  $gf_pages = is_array($page) ? $page : array($page);
  }
  
  return in_array($current_page, $gf_pages);
  }
  /**
  * Add checkbox to entry info - option to send entry to crm
  * 
  * @param mixed $form_id
  * @param mixed $lead
  */
  public  function entry_info_send_checkbox( $form_id, $lead ) {
  
  // If this entry's form isn't connected to crm, don't show the checkbox
  if(!$this->show_send_to_crm_button() ) { return; }
  
  // If this is not the Edit screen, get outta here.
  if(empty($_POST["screen_mode"]) || $_POST["screen_mode"] === 'view') { return; }
  
   if(!current_user_can($this->id."_send_to_crm")){return; }
  
  if( apply_filters( $this->id.'_show_manual_export_button', true ) ) {
  printf('<input type="checkbox" name="'.$this->id.'_update" id="'.$this->id.'_update" value="1" /><label for="'.$this->id.'_update" title="%s">%s</label><br /><br />', esc_html__('Create or update this entry in Salesforce. The fields will be mapped according to the form feed settings.', 'gravity-forms-salesforce-crm'), esc_html__('Send to Salesforce', 'gravity-forms-salesforce-crm'));
  } else {
  echo '<input type="hidden" name="'.$this->id.'_update" id="'.$this->id.'_update" value="1" />';
  }
  }
  /**
  * Add button to entry info - option to send entry to crm
  * 
  * @param mixed $button
  */
  public  function entry_info_send_button( $button = '' ) {
  // If this entry's form isn't connected to crm, don't show the button
  if(!$this->show_send_to_crm_button()) { return $button; }
if(!current_user_can($this->id."_send_to_crm")){return; }
  // Is this the view or the edit screen?
  $mode = empty($_POST["screen_mode"]) ? "view" : $this->post("screen_mode");
  if($mode === 'view') {
            $margin="";
      if(defined("vx_btn")){
      $margin="margin-top: 5px;";    
      }else{define('vx_btn','true');}
  $button.= '<input type="submit" class="button button-large button-secondary alignright" name="'.$this->id.'_send" style="margin-left:5px; '.$margin.'" title="'.__('Create or update this entry in Salesforce. The fields will be mapped according to the form feed settings.','gravity-forms-salesforce-crm').'" value="'.__('Send to Salesforce', 'gravity-forms-salesforce-crm').'" onclick="jQuery(\'#action\').val(\'send_to_crm\')" />';
  //logs button

      $entry_id=$this->post('lid');
      $form_id = rgget('id');
      if(empty($entry_id)){
          $entry_id=$this->get_entry_id($form_id);
      }
      $log_url=admin_url( 'admin.php?page=gf_edit_forms&view=settings&subview='.$this->id.'&tab=log&id='.$_GET['id'].'&entry_id='.$entry_id);  
    $button.= '<a class="button button-large button-secondary alignright" style="margin-left:5px; margin-top:5px; " title="'.__('Go to Salesforce Logs','gravity-forms-salesforce-crm').'" href="'.$log_url.'">'.__('Salesforce Logs','gravity-forms-salesforce-crm').'</a>';
  
  } 
  return $button;
  }
  /**
  * Whether to show the Entry "Send to CRM" button or not
  *
  * If the entry's form has been mapped to CRM feed, show the Send to CRM button. Otherwise, don't.
  *
  * @return boolean True: Show the button; False: don't show the button.
  */
  public  function show_send_to_crm_button() {
  
  $form_id = rgget('id');
  
  return $this->has_feed($form_id);
  }
  /**
  * Does the current form have a feed assigned to it?
  * @param  INT      $form_id Form ID
  * @return boolean
  */
  function has_feed($form_id) {
  $data=$this->get_data_object();
  $feeds = $data->get_feed_by_form( $form_id , true);
  
  return !empty($feeds);
  }
  
  /**
  * Add note to GF Entry
  * @param int $id   Entry ID
  * @param string $note Note text
  */
  private function add_note($id, $note) {
  
  RGFormsModel::add_note($id, 0, esc_html__('Gravity Forms Salesforce Plugin','gravity-forms-salesforce-crm'), $note);
  }
  
  /**
  * if gravity forms installed and supported
  * 
  */
  private  function is_gravityforms_supported(){
  if(class_exists("GFCommon")){
  $is_correct_version = version_compare(GFCommon::$version, $this->min_gravityforms_version, ">=");
  return $is_correct_version;
  }
  else{
  return false;
  }
  }
  /**
  * uninstall plugin
  * 
  */
  public  function uninstall(){
  //droping all tables
 require_once(self::$path . "includes/install.php"); 
  $install=new vxg_install_salesforce();
    do_action('uninstall_vx_plugin_'.$install->id);
  $install->remove_data();
  }
    /**
  * email validation
  * 
  * @param mixed $email
  */
  public function is_valid_email($email){
         if(function_exists('filter_var')){
      if(filter_var($email, FILTER_VALIDATE_EMAIL)){
      return true;    
      }
       }else{
       if(strpos($email,"@")>1){
      return true;       
       }    
       }
   return false;    
  }
  /**
  * deactivate
  * 
  * @param mixed $action
  */
  public function deactivate($action="deactivate"){ 
  do_action('plugin_status_'.$this->type,$action);
  }
  /**
  * activate plugin
  * 
  */
  public function activate(){ 
$this->plugin_api(true);
$this->install_plugin();
do_action('plugin_status_'.$this->type,'activate');  
  }
    /**
  * Send CURL Request
  * 
  * @param mixed $body
  * @param mixed $path
  * @param mixed $method
  */
  public function request($path="",$method='POST',$body="",$head=array()) {

        $args = array(
            'body' => $body,
            'headers'=> $head,
            'method' => strtoupper($method), // GET, POST, PUT, DELETE, etc.
            'sslverify' => false,
            'timeout' => 20,
        );

       $response = wp_remote_request($path, $args);

        if(is_wp_error($response)) { 
            $this->error_msg= $response->get_error_message();
            return false;
        } else if(isset($response['response']['code']) && $response['response']['code'] != 200 && $response['response']['code'] != 404) {
            $this->error_msg = strip_tags($response['body']);
            return false;
        } else if(!$response) {
            return false;
        }
   $result=wp_remote_retrieve_body($response);
        return $result;
    }

  /**
  * Adds feed tooltips to the list of tooltips
  * 
  * @param mixed $tooltips
  */
  public  function tooltips($tooltips){
  $crm_tooltips = array(
    'vx_feed_name' => '<h6>' . esc_html__('Feed Name', 'gravity-forms-salesforce-crm') . '</h6>' . esc_html__('Enter feed name of your choice.', 'gravity-forms-salesforce-crm'),
  'vx_sel_object' => '<h6>' .__('Salesforce Object', 'gravity-forms-salesforce-crm') . '</h6>' . esc_html__('Select the Object to Create when a Form is Submitted.', 'gravity-forms-salesforce-crm'),
   'vx_sel_account' => '<h6>' .__('Salesforce Account', 'gravity-forms-salesforce-crm') . '</h6>' . esc_html__('Select the Salesforce account you would like to export entries to.', 'gravity-forms-salesforce-crm'),
  'vx_sel_form' => '<h6>' . esc_html__('Gravity Form', 'gravity-forms-salesforce-crm') . '</h6>' . esc_html__('Select the Gravity Form you would like to integrate with Salesforce. Contacts generated by this form will be automatically added to your Salesforce account.', 'gravity-forms-salesforce-crm'),
  
  'vx_map_fields' => '<h6>' . esc_html__('Map Standard Fields', 'gravity-forms-salesforce-crm') . '</h6>' . esc_html__('Associate your Salesforce fields to the appropriate Gravity Form fields.', 'gravity-forms-salesforce-crm'),
  
  'vx_optin_condition' => '<h6>' . esc_html__('Opt-In Condition', 'gravity-forms-salesforce-crm') . '</h6>' . esc_html__('When the opt-in condition is enabled, form submissions will only be exported to Salesforce when the condition is met. When disabled all form submissions will be exported.', 'gravity-forms-salesforce-crm'),
  
  'vx_manual_export' => '<h6>' . esc_html__('Manual Export', 'gravity-forms-salesforce-crm') . '</h6>' . esc_html__('If you do not want all entries sent to Salesforce, but only specific, approved entries, check this box. To manually send an entry to Salesforce, go to Entries, choose the entry you would like to send to Salesforce, and then click the "Send to Salesforce" button.', 'gravity-forms-salesforce-crm'),
  
    'vx_entry_notes' => '<h6>' . esc_html__('Entry Notes', 'gravity-forms-salesforce-crm') . '</h6>' . esc_html__('Enable this option if you want to synchronize Gravity Forms entry notes to Salesforce Object notes. For example , when you add a note to a Gravity Forms entry, it will be added to the Salesforce Object selected in the feed.', 'gravity-forms-salesforce-crm'),
    
      'vx_primary_key' => '<h6>' . esc_html__('Primary Key', 'gravity-forms-salesforce-crm') . '</h6>' . esc_html__('Which field should be used to update existing objects?', 'gravity-forms-salesforce-crm'),
      
    
      'vx_web' => '<h6>' . esc_html__('Web to Lead', 'gravity-forms-salesforce-crm') . '</h6>' . sprintf(__('Web-to-Lead is available for all Salesforce Editions. If you are not sure if your Salesforce Edition supports the API, you should use Web-to-Lead. Editions that do not support the Salesforce API: %s 1: Personal Edition %s 2: Group Edition %s 3: Professional Edition %s Note: You can purchase API access for a Professional Edition', 'gravity-forms-salesforce-crm'),'<br/>','<br/>','<br/>','<br/>'),
      
  'vx_api' => '<h6>' . esc_html__('Salesforce API', 'gravity-forms-salesforce-crm') . '</h6>' .sprintf(__('The API features are more powerful than Web-to-Lead. You can create different object types, as well as other advanced features. If you have any of the following Salesforce Editions, you can use the included API Add-on: %s 1: Enterprise Edition %s 2: Unlimited Edition %s 3: Developer Edition %s 4: Professional Edition - Requires API Upgrade', 'gravity-forms-salesforce-crm'),'<br/>','<br/>','<br/>','<br/>','<br/>'),
  

  'vx_custom_app'=>'<h6>' . esc_html__('Custom App', 'gravity-forms-salesforce-crm') . '</h6>' .__('This option is for advanced users who want to override default Salesforce App.','gravity-forms-salesforce-crm'),
  
  
  'vx_camps'=>'<h6>' . esc_html__('Salesforce Campaigns', 'gravity-forms-salesforce-crm') . '</h6>' .__('Get Campaigns and Status list from salesforce.','gravity-forms-salesforce-crm'),
  
  'vx_sel_price_book'=>__('Which Pricebook should be searched for product','gravity-forms-salesforce-crm'),
  
  'vx_sel_camp'=>'<h6>' . esc_html__('Select Campaign', 'gravity-forms-salesforce-crm') . '</h6>' .__('Which Campaign should be assigned to this object.','gravity-forms-salesforce-crm'),
  
  'vx_sel_status'=>'<h6>' . esc_html__('Campaign Status', 'gravity-forms-salesforce-crm') . '</h6>' .__('What should be Member Status.','gravity-forms-salesforce-crm'),
  
  'vx_pro_desc'=>'<h6>' . esc_html__('Price Book', 'gravity-forms-salesforce-crm') . '</h6>' .__('A new product will be created in selected Pricebook. You can add a description for new products created by this plugin.','gravity-forms-salesforce-crm'),
  
   'vx_assign_account'=>'<h6>' . esc_html__('Salesforce Account', 'gravity-forms-salesforce-crm') . '</h6>' .__('Enable this option if you want to assign an account this object.','gravity-forms-salesforce-crm'),
   
   'vx_sel_account'=>'<h6>' . esc_html__('Select Account', 'gravity-forms-salesforce-crm') . '</h6>' .__('Object created by this feed will be assigned to the selected Account.','gravity-forms-salesforce-crm'),
   
      'vx_assign_contract'=>'<h6>' . esc_html__('Assign Contract', 'gravity-forms-salesforce-crm') . '</h6>' .__('Enable this option , if you want to assign a Contract to this object','gravity-forms-salesforce-crm'),
      
   'vx_sel_contract'=>'<h6>' . esc_html__('Select Contact', 'gravity-forms-salesforce-crm') . '</h6>' .__('Select Contract feed. Contract created by this feed will be assigned to this object','gravity-forms-salesforce-crm'),
   
   'vx_camp_check'=>'<h6>' . esc_html__('Add to Campaign', 'gravity-forms-salesforce-crm') . '</h6>' .__('If enabled, Lead/Contact will be added to selected Campaign','gravity-forms-salesforce-crm'),
   'vx_owner_check'=>'<h6>' . esc_html__('Assign Owner', 'gravity-forms-salesforce-crm') . '</h6>' .__('Enable this option if you want to assign another object owner.','gravity-forms-salesforce-crm'),
   
   'vx_owners'=>'<h6>' . esc_html__('Salesforce Users', 'gravity-forms-salesforce-crm') . '</h6>' .__('Get Users list from Salesforce','gravity-forms-salesforce-crm'),
   
   'vx_order_notes'=>'<h6>' . esc_html__('Entry Notes', 'gravity-forms-salesforce-crm') . '</h6>' .__('Enable this option if you want to synchronize Entry notes to Salesforce Object notes. For example, when you add a note to a Entry, it will be added to the Salesforce Object selected in the feed.','gravity-forms-salesforce-crm'),
  
   'vx_sel_owner'=>'<h6>' . esc_html__('Select Owner', 'gravity-forms-salesforce-crm') . '</h6>' .__('Select a user as a owner of this object','gravity-forms-salesforce-crm'),
   
      'vx_entry_note'=>'<h6>' . esc_html__('Entry Note', 'gravity-forms-salesforce-crm') . '</h6>' .__('Check this option if you want to send more data as CRM entry note.', 'gravity-forms-salesforce-crm'),
   'vx_note_fields'=>'<h6>' . esc_html__('Note Fields', 'gravity-forms-salesforce-crm') . '</h6>' .__('Select fields which you want to send as a note', 'gravity-forms-salesforce-crm'),
   'vx_disable_note'=>'<h6>' . esc_html__('Note Fields', 'gravity-forms-salesforce-crm') . '</h6>' .__('Enable this option if you want to add note only for new CRM entry', 'gravity-forms-salesforce-crm')
    
    );
  return  array_merge($tooltips,$crm_tooltips);
  }
 
  /**
  * Formates User Informations and submitted form to string
  * This string is sent to email and salesforce
  * @param  array $info User informations 
  * @param  bool $is_html If HTML needed or not 
  * @return string formated string
  */
  public  function format_user_info($info,$is_html=false){
  $str=""; $file="";
  if($is_html){
  if(file_exists(self::$path."templates/email.php")){    
  ob_start();
  include_once(self::$path."templates/email.php");
  $file= ob_get_contents(); // data is now in here
  ob_end_clean();
  }
  if(trim($file) == "")
  $is_html=false;
  }
  if(isset($info['info']) && is_array($info['info'])){
  if($is_html){
  if(isset($info['info_title'])){
  $str.='<tr><td style="font-family: Helvetica, Arial, sans-serif;background-color: #C35050; height: 36px; color: #fff; font-size: 24px; padding: 0px 10px">'.$info['info_title'].'</td></tr>'."\n";
  }
  if(is_array($info['info']) && count($info['info'])>0){
  $str.='<tr><td style="padding: 10px;"><table border="0" cellpadding="0" cellspacing="0" width="100%;"><tbody>';      
  foreach($info['info'] as $f_k=>$f_val){
  $str.='<tr><td style="padding-top: 10px;color: #303030;font-family: Helvetica;font-size: 13px;line-height: 150%;text-align: right; font-weight: bold; width: 28%; padding-right: 10px;">'.$f_k.'</td><td style="padding-top: 10px;color: #303030;font-family: Helvetica;font-size: 13px;line-height: 150%;text-align: left; word-break:break-all;">'.$f_val.'</td></tr>'."\n";      
  }
  $str.="</table></td></tr>";             
  }
  }else{
  if(isset($info['title']))
  $str.="\n".$info['title']."\n";    
  foreach($info['info'] as $f_k=>$f_val){
  $str.=$f_k." : ".$f_val."\n";      
  }
  }
  }
  if($is_html){
  $str=str_replace(array("{title}","{msg}","{sf_contents}"),array($info['title'],$info['msg'],$str),$file);
  }
  return $str;   
  }
 

  /**
  * if plugin user is valid
  * 
  * @param mixed $update
  */
  
  public function is_valid_user($update){
  return is_array($update) && isset($update['user']['user']) && $update['user']['user']!=""&& isset($update['user']['expires']);
  }     
  /**
  * Get variable from array
  *  
  * @param mixed $key
  * @param mixed $arr
  */
  public function post($key, $arr="") {
  if(is_array($arr)){
  return isset($arr[$key])  ? $arr[$key] : "";
  }
  //clean when getting extrenals
  return isset($_REQUEST[$key]) ? $this->clean($_REQUEST[$key],$key) : "";
  }
public function clean($var,$key=''){
    if ( is_array( $var ) ) {
$a=array();
    foreach($var as $k=>$v){
  $a[$k]=$this->clean($v,$k);    
    }
  return $a;  
    }else {
     $var=wp_unslash($var);   
  if(in_array($key,array('note_val','value'))){
 $var=sanitize_textarea_field($var);      
  }else{
  $var=sanitize_text_field($var);    
  }      
return  $var;
    }
}

/**
  * Get WP Encryption key
  * @return string Encryption key
  */
  public static  function get_key(){
  $k='Wezj%+l-x.4fNzx%hJ]FORKT5Ay1w,iczS=DZrp~H+ve2@1YnS;;g?_VTTWX~-|t';
  if(defined('AUTH_KEY')){
  $k=AUTH_KEY;
  }
  return substr($k,0,30);        
  }
  /**
  * check if other version of this plugin exists
  * 
  */
  public function other_plugin_version(){ 
  $status=0;
  if(class_exists('vxg_salesforce_wp')){
      $status=1;
  }else if( file_exists(WP_PLUGIN_DIR.'/gravity-forms-salesforce-crm/gravity-forms-salesforce-crm.php')) {
  $status=2;
  } 
  return $status;
  } 
    /**
  * Get time Offset 
  * 
  */
  public function time_offset(){
 $offset = (int) get_option('gmt_offset');
  return $offset*3600;
  } 
  /**
  * Decrypts Values
  * @param array $info Salesforce encrypted API info 
  * @return array API settings
  */
  public static function de_crypt($info){
  $info=trim($info);
  if($info == "")
  return '';
  $str=base64_decode($info);
  $key=self::get_key();
      $decrypted_string='';
     if(function_exists("openssl_encrypt") && strpos($str,':')!==false ) {
$method='AES-256-CBC';
$arr = explode(':', $str);
 if(isset($arr[1]) && $arr[1]!=""){
 $decrypted_string=openssl_decrypt($arr[0],$method,$key,false, base64_decode($arr[1]));     
 }
 }else{
     $decrypted_string=$str;
 }
  return $decrypted_string;
  }   
  /**
  * Encrypts Values
  * @param  string $str 
  * @return string Encrypted Value
  */
  public static function en_crypt($str){
  $str=trim($str);
  if($str == "")
  return '';
  $key=self::get_key();
if(function_exists("openssl_encrypt")) {
$method='AES-256-CBC';
$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
$enc_str=openssl_encrypt($str,$method, $key,false,$iv);
$enc_str.=":".base64_encode($iv);
  }else{
      $enc_str=$str;
  }
  $enc_str=base64_encode($enc_str);
  return $enc_str;
  }
  
  /**
  * Get variable from array
  *  
  * @param mixed $key
  * @param mixed $key2
  * @param mixed $arr
  */
  public function post2($key,$key2, $arr="") {
  if(is_array($arr) && isset($arr[$key]) && is_array($arr[$key])){
  return isset($arr[$key][$key2])  ? $arr[$key][$key2] : "";
  }
  return isset($_REQUEST[$key][$key2]) && is_array($_REQUEST[$key]) ? $this->clean($_REQUEST[$key][$key2]) : "";
  }
  /**
  * Get variable from array
  *  
  * @param mixed $key
  * @param mixed $key2
  * @param mixed $arr
  */
  public function post3($key,$key2,$key3, $arr="") {
  if(is_array($arr)){
  return isset($arr[$key][$key2][$key3])  ? $arr[$key][$key2][$key3] : "";
  }
  return isset($_REQUEST[$key][$key2][$key3]) ? $this->clean($_REQUEST[$key][$key2][$key3]) : "";
  }
  /**
  * get base url
  * 
  */
  public function get_base_url(){
  return plugin_dir_url(__FILE__);
  }
    /**
  * get plugin direcotry name
  * 
  */
  public function plugin_dir_name(){
  if(!empty($this->plugin_dir)){
  return $this->plugin_dir;
  }
  if(empty(self::$path)){
  self::$path=$this->get_base_path(); 
  }
  $this->plugin_dir=basename(self::$path);
  return $this->plugin_dir;
  }
  /**
  * get plugin slug
  *  
  */
  public function get_slug(){
  return plugin_basename(__FILE__);
  }
public function do_actions(){
     if(!is_object(self::$plugin) ){ $this->plugin_api(); }
      if(is_object(self::$plugin) && method_exists(self::$plugin,'valid_addons')){
       return self::$plugin->valid_addons();  
      }
    
   return false;   
  }
  /**
  * Returns the physical path of the plugin's root folder
  * 
  */
  public function get_base_path(){
  return plugin_dir_path(__FILE__);
  }
 /**
  * Writes an error message to the Gravity Forms log.
  * 
  */
  public function log_msg($message,$type=""){
        if (class_exists("GFLogging")) {
            GFLogging::include_logger();
            $slug=$this->plugin_dir_name();
            $log_type=KLogger::DEBUG;
            if($type == "error"){
            $log_type=KLogger::ERROR;   
            }
            GFLogging::log_message($slug, $message,$log_type);
        }
    }
    /**
  * get api object
  * 
  * @param mixed $settings
  * @return vxg_api_zoho
  */
  public  function get_api($crm=""){
  $api = false;
  if(!class_exists("vxg_salesforce_api"))
  require_once($this->get_base_path()."api/api.php");
     
  $api= new vxg_salesforce_api($crm);
  
  return $api;
  }
  /**
  * get gravity forms entry
  * 
  */
  public function get_gf_entry($entry_id){
      $entry=array();
  // Fetch entry (use new GF API from version 1.8)
  if( class_exists( 'GFAPI' ) && !empty( $entry_id ) ) {
  $entry = GFAPI::get_entry( $entry_id );
  } elseif( class_exists( 'RGFormsModel' ) && !empty( $entry_id ) ) {
  $entry = RGFormsModel::get_lead( $entry_id );
  }
  return $entry;
  }
    /**
  * get data object
  * 
  */
  public function get_data_object(){
  require_once(self::$path . "includes/data.php");     
  if(!is_object($this->data))
  $this->data=new vxg_salesforce_data();
  return $this->data;
  }
    public function gf_entry_created_before($entry, $form){
      $this->gf_entry_created($entry, $form);  
    }
    public function gf_entry_created_after($entry, $form){
      $this->gf_entry_created($entry, $form,'after_submit');  
    }
    /**
    * Send entry to crm on update
    * 
    * @param mixed $form
    * @param mixed $lead_id
    */
public function update_entry($form,$lead_id){ //with after hook update_entry($form,$lead_id){

    $meta=get_option($this->type.'_settings',array());
      if(!empty($meta['update']) || isset($_POST[$this->id.'_update']) ){
  $entry=$this->get_gf_entry($lead_id);
 
    $push=$this->push($entry,$form,'update');
        if(!empty($push['msg']) && is_admin()){
  $this->screen_msg($push['msg'],$push['class']);  
  }
}

}
   public function entry_status($id,$status,$old){
             $meta=get_option($this->type.'_settings',array());
         $option = '';
             if($status == 'active'){
              $option= 'restore';   
             }else if($status == 'trash'){
                 $option='delete';
             }
         
      if( !empty($option) && !empty($meta[$option])){
        //  $option= $option == 'restore' ? '' : $option;
        $entry=$this->get_gf_entry($id);
        $form=array();
        if(!empty($entry['form_id'])){
        $form = RGFormsModel::get_form_meta($entry['form_id']);    
        }
       $this->push($entry,$form,$option); 
      } 
  
  }
  /**
  * gravity forms entry created
  * 
  * @param mixed $entry
  * @param mixed $form
  */
  public function gf_entry_created( $entry, $form, $event='submit'){

      if(is_array($entry) && isset($entry['status']) && $entry['status'] == 'active' && empty($entry['partial_entry_percent'])){
      
        $entry_id=$this->post('id',$entry);
        if($this->do_actions()){
     do_action('vx_addons_save_entry',$entry_id,$entry,'gf',$form);   
        }
      $this->push($entry,$form,$event,false);     
      }
  }
  
  public function gf_entry_paid_normal($entry,$pay_info){
     $this->gf_entry_paid($entry,$pay_info); 
  }  
  public function gf_entry_paid_subscription($entry,$pay_info){
     $this->gf_entry_paid($entry,$pay_info,'subscription_paid'); 
  }
   public function gf_entry_paid($entry,$pay_info,$event='paid'){
       //vx_log(json_encode($entry));
    // if($entry['payment_status'] == 'Paid'){
        $entry_id=$this->post('id',$entry);
        $form=array('id'=>$entry['form_id'],'title'=>'form id '.$entry['form_id']);
        if(class_exists('GFAPI')){ $form = GFAPI::get_form( $entry['form_id'] ); }
   
        if($this->do_actions()){
     do_action('vx_addons_save_entry',$entry_id,$entry,'gf',$form);   
        }
      $this->push($entry,$form,'paid',false);
   //  }     
  }

  /**
  * push form data to crm
  * 
  * @param mixed $entry
  * @param mixed $form
  * @param mixed $is_admin
  */
  public  function push($entry, $form,$event="",$is_admin=false,$log=""){ 

     $data_db=$this->get_data_object(); 
     $log_id='';   $feeds_meta=array();
   if(!empty($log)){
          if(isset($log['id'])){
       $log_id=$log['id'];
       }
       $log_feed=$data_db->get_feed($log['feed_id']);
   if(!empty($log_feed)){
       $feeds_meta=array($log_feed);
   }
   }else{   
  //get feeds of a form
  $feeds=$data_db->get_feed_by_form($entry['form_id'],true);
 
  if(is_array($feeds) && count($feeds)>0){
  $k=1000; $e=2000; $i=1;
    foreach($feeds as $feed){
          $data=isset($feed['data']) ? json_decode($feed['data'],true) : array(); 
  $meta=isset($feed['meta']) ? json_decode($feed['meta'],true) : array();
  $feed['meta']=$meta;
  $feed['data']=$data;
$object=$this->post('object',$feed); 
 if(!empty($data['contract_check']) || !empty($data['account_check'])|| !empty($data['contact_check'])){
  if($object == 'Order'){
     $feeds_meta[$e++]=$feed; 
  }else{
  $feeds_meta[$k++]=$feed; 
  }
 }else{
     $feeds_meta[$i++]=$feed; 
 }
    }
       ksort($feeds_meta); 
  // 
  }
   }
      $form_id=0;
 if(isset($form['id'])){
    $form_id=$form['id']; 
 }

  $entry_id=$this->post('id',$entry);
  if(isset($entry['__vx_id'])){
   $entry_id=$entry['__vx_id'];   
  }else{
$entry=apply_filters('vx_crm_post_fields',$entry,$entry_id,'gf',$form); 
  }
//var_dump($id); die();
   $n=0;
   $screen_msg_class="updated"; $notice="";
  if(is_array($feeds_meta) && count($feeds_meta)>0){
  while($feed=current($feeds_meta)){
      next($feeds_meta);
    $n++;
        $temp=array();
  $force_send=false;
      $post_comment=true;
      $screen_msg="";
      $parent_id=0;
                   if(isset($entry['__vx_parent_id'])){
  $parent_id=$entry['__vx_parent_id'];  
}
  $object=$this->post('object',$feed);   
  $data=$feed['data']; 
  $meta=$feed['meta'];  
  $account=$this->post('account',$feed);

  $info=$this->get_info($account); 
$info_data=array();
if(isset($info['data'])){
    $info_data=$info['data'];
}
$api_type=$this->post('api',$info_data);
if($api_type == 'web'){
    $this->is_api=false;
}

if(!empty($object) && in_array($event,array('restore','update','delete','add_note','delete_note'))){

$search_object=$object;
if(in_array($event,array('add_note','delete_note')) && !empty($log)){

   if($event == 'add_note'){
        $note=json_decode($log['data'],true);
        if(!empty($note['Title']['value'])){
               self::$note=array('id'=>$log['parent_id']);
      self::$note['title']=$note['Title']['value'];
      self::$note['body']=$note['Body']['value'];
        }
   } 
}
   if($event == 'delete_note' && !empty(self::$note)){
         $parent_id=self::$note['id'];
   }
 
    if(in_array($event,array('delete_note','add_note'))){
        //check feed
    $order_notes=$this->post('entry_notes',$data); //if notes sync not enabled in feed return

    if( empty($order_notes)){
        continue;
    }
  
         //change main object to Note
         $feed['related_object']=$object;
        $object=$feed['object']=!empty($meta['note_list']) ? 'ContentNote' : 'Note';   
 }
 if($event == 'delete_note'){
//when deleting note search note object 
     $search_object='Note';
 }
 $_data=$this->get_data_object();
$feed_log=$_data->get_feed_log($feed['id'],$entry_id,$search_object,$parent_id); 
if(!is_array($feed_log)){ $feed_log=array('status'=>0); }
 if($event == 'restore' && $feed_log['status'] != 5) { // only allow successfully deleted records
     continue;
 }
  if( in_array($event,array('update','delete') ) && !in_array($feed_log['status'],array(1,2) )  ){ // only allow successfully sent records
     continue;
 }

if(empty($feed_log['crm_id']) || empty($feed_log['object']) || $feed_log['object'] != $search_object){
    
   continue; 
}
if($event !='restore'){
 $feed['crm_id']=$feed_log['crm_id'];
    unset($feed['primary_key']);
}
   $feed['event']=$event;  
// add note and save related extra info
 if( $event == 'add_note' && !empty(self::$note)){
         $temp=array('Title'=>array('value'=>self::$note['title']),'Body'=>array('value'=>self::$note['body']),'ParentId'=>array('value'=> $feed['crm_id'])); 

$parent_id=self::$note['id']; 
$object_link=$feed_log['crm_id'];
 $feed['note_object_link']='<a href="'.$feed_log['link'].'" target="_blank">'.$feed_log['crm_id'].'</a>';
 } 
 // delete not and save extra info
 if( $event == 'delete_note'){
     
     $feed_log_arr= json_decode($feed_log['extra'],true);
     if(isset($feed_log_arr['note_object_link'])){
         $feed['note_object_link']=$feed_log_arr['note_object_link'];
     }
  $temp=array('ParentId'=>array('value'=> $feed['crm_id']));     
 }
 //delete object
 if( $event == 'delete'){
   $temp=array('Id'=>array('value'=> $feed['crm_id']));      
 }
//
  if(!in_array($event , array('update','restore') )){ 
     //do not apply filters when adding note , deleting note , entry etc
      $force_send=true;   
  } 
   if($event == 'restore'){ // send as new entry
    unset($feed['crm_id']);   
   }
        //do not post comment in al other cases 
     $post_comment=false; 

 } 
// var_dump(self::$note,$object,$feed['note_object'],$feed['object'],$feed['crm_id'],$feed['event'],$temp,$force_send); 
 //not submitted by admin
 $feed_event=$this->post('manual_export',$data);
 if(!$is_admin){
  if($event == 'submit' && $feed_event != ''){ //if manual export is yes
  continue;   
  } 
    if($event == 'after_submit' && $feed_event != '4'){ 
  continue;   
  } 
  if($event == 'subscription_paid' && $feed_event != '3'){ 
  continue;   
  } 
    if($event == 'paid' && $feed_event != '2'){ // only process paid event, if set in feed
  continue;   
  } 
 }

if(!$force_send && isset($data['map']) && is_array($data['map']) && count($data['map'])>0){

         if($api_type =="web"){
  $meta['fields']=$this->web_fields($object,$data['map']);  
  }
 
$custom= isset($meta['fields']) && is_array($meta['fields']) ? $meta['fields'] : array();
$skip_feed=false;
  foreach($data['map'] as $k=>$v){ 

  $value=false; 
  if(!empty($v)){ //if value not empty
  $field_type=$this->post('type',$v); 
    if( !empty($field_type) ){ //custom value
  $value=trim($this->post('value',$v)); 
  $value=$this->process_tags($entry,$form,$value);
  if($field_type == 'value_date'){
   $value=date('d/m/Y',strtotime($value));   
  }else if($field_type == 'value_time'){
   $value=date('d/m/Y',strtotime($value));   
  }  
  

  }else{ //general field
  $field=$this->post('field',$v);
  if($field !=""){
      if(strpos($field,'_vx_feed-') !== false){ 
      $temp_feed_id=substr($field,9);
       if(isset(self::$feeds_res[$temp_feed_id])){
        $value= !empty(self::$feeds_res[$temp_feed_id]['id']) ? self::$feeds_res[$temp_feed_id]['id'] : ''; 
       }else if($n < count($feeds_meta) ){ // it is not last element
        $feeds_meta[]=$feed; $skip_feed=true;
        break;   
       } 
}else{
$value=$this->verify_field_val($entry,$form,$field,$k,$custom);
  if($value == ''){ $value=false;  }
$gf_field= RGFormsModel::get_field($form, $field);
if(!empty($gf_field) && in_array($gf_field->type,array('checkbox')) && !empty($gf_field->choices) && count($gf_field->choices)< 2 ){
if(!empty($value)){ $value='1'; }  //single checkbox or acceptance field , salesforce web2lead accepts only 1 for checkbox field
  } 
}
  }
  
  }

  if($value!== false){ 
  if(isset($custom[$k]['name'])){
  $temp[$k]=array('value'=>$value,'label'=>$custom[$k]['label']);    
  }else  if(isset($custom[$k]['name_c'])){
  $temp[$custom[$k]['name_c']]=array('value'=>$value,'label'=>$custom[$k]['name_c']);    
  }
      }
  }
  }

  if($skip_feed){ continue; }

 if($object == 'Order'){
     $temp['Status']=array('value'=>'Draft','label'=>'Draft');
 }
   //change owner id
  if(isset($data['owner']) && !empty($data['user'])){
   $temp['OwnerId']=array('value'=>apply_filters('vx_assigned_user_id',$data['user'],$this->id,$feed['id'],$entry,$form),'label'=>'Owner ID');   
  }

  //add account or contract

    if(!empty($data['contract_check']) && !empty($data['object_contract'])){
     $contract_feed=$data['object_contract']; 
       if( isset(self::$feeds_res[$contract_feed]) ){

   $contract_res=self::$feeds_res[$contract_feed];
  /////
  if(!empty($contract_res['id'])){
   $temp['ContractId']=array('value'=> $contract_res['id'],'label'=>'Contract ID');   
  }else{ //if empty continue
      continue;
  }    
   }
    } 
    if(!empty($data['account_check']) && !empty($data['object_account'])){
     $account_feed=$data['object_account']; 
   if( isset(self::$feeds_res[$account_feed]) ){

   $account_res=self::$feeds_res[$account_feed];
  /////
  if(!empty($account_res['id'])){
   $temp['AccountId']=array('value'=> $account_res['id'],'label'=>'Account ID');   
  }else{ //if empty continue
      continue;
  }    
   }  

  }
if(!empty($data['contact_check']) && !empty($data['contact_feed'])){ 
     $contact_feed=$data['contact_feed']; 
       if( isset(self::$feeds_res[$contact_feed]) ){

   $contact_res=self::$feeds_res[$contact_feed];
  /////
  if(!empty($contact_res['id'])){
   $temp['contact_id']=$contact_res['id'];   
  }else{ //if empty continue
    //  continue;
  }  
   }
}
  //add note 
   if(!empty($data['note_check']) ){
          $entry_note=''; $entry_note_title='';
if(!empty($data['note_fields']) && is_array($data['note_fields'])){
          $data['note_val']='{'.implode("}\n{",$data['note_fields'])."}\n";
}
if(!empty($data['note_val'])){
    $entry_note=$data['note_val'];
           $pos=strpos($entry_note,'?');
               if($pos > 0){ 
              $entry_note=substr($entry_note,$pos+1);     
               }else{    
            $pos=20;  
           } 
           $entry_note_title=substr($entry_note,0,$pos);
           $entry_note_title=$this->process_tags($entry,$form,$entry_note_title);
           $entry_note=$this->process_tags($entry,$form,$entry_note);
          if(!empty($entry_note)){
    $entry_note=str_replace("'", "", $entry_note);
    $entry_note=esc_html($entry_note);    
     $feed['__vx_entry_note']=array('Title'=>$entry_note_title,'Body'=>$entry_note);      
          }
}
  }
}
 
$no_filter=true;    
         
    if(isset($_REQUEST['bulk_action']) && $_REQUEST['bulk_action'] =="send_to_crm_bulk_force" && !empty($log_id)){
  $force_send=true;
  }
 
$temp=apply_filters($this->id.'_post_data', $temp ,$entry); 
  if(!$force_send && $this->post('optin_enabled',$data) == "1"){ //apply filters if not sending by force and optin is enabled
  $no_filter=$this->check_filter($data,$entry,$form); 
  $res=array("status"=>"4","extra"=>array("filter"=>$this->filter_condition),"data"=>$temp);  
  }

//var_dump($temp); die();
 
 $feed_id=$this->post('id',$feed);
  if($no_filter){ //get $res if no filter , other wise use filtered $res
  $api=$this->get_api($info);
  $feed_arr=$feed;
  if(is_array($meta) && is_array($data)){
      $feed_arr=array_merge($meta,$data,$feed);
  }
  $res=$api->push_object($feed['object'],$temp,$feed_arr);
$res=apply_filters($this->id . '_response', $res, $entry, $object);

  if(!empty($res['id'])){
    $entry['_vx_feed-'.$feed_id]= $res['id']; 
      if($object == 'Contact'){
   $entry['sf_contact_id']=$res['id'];    
  } }
  }

    
  self::$feeds_res[$feed_id]=$res; 
  $status=$res['status'];  $error=""; 
  $id=$this->post('id',$res);
  $added=false;
  if($api_type != 'web' && !empty($id)){
  $added=true;    
  }else if($api_type == 'web' && !empty($status)){
   $added=true;     
  }
  if( $added){ 
      $id=$res['id'];
      $action=$this->post('action',$res);
      if($action == "Added"){
          if(empty($res['link'])){
  $msg=sprintf(__('Successfully Added to Salesforce (%s) with ID # %s .', 'gravity-forms-salesforce-crm'),$feed['object'],$res['id']);
          }else{
  $msg=sprintf(__('Successfully Added to Salesforce (%s) with ID # %s . View entry at %s', 'gravity-forms-salesforce-crm'),$feed['object'],$res['id'],$res['link']);
          }
  $screen_msg=__( 'Entry added in Salesforce', 'gravity-forms-salesforce-crm');
      }else{
            if(empty($res['link'])){
  $msg=sprintf(__('Successfully Updated to Salesforce (%s) with ID # %s . View entry at %s', 'gravity-forms-salesforce-crm'),$feed['object'],$res['id'],$res['link']);   
            }else{
  $msg=sprintf(__('Successfully Updated to Salesforce (%s) with ID # %s .', 'gravity-forms-salesforce-crm'),$feed['object'],$res['id']);   
            }
     $screen_msg=__( 'Entry updated in Salesforce', 'gravity-forms-salesforce-crm');
      }
   
  
  }else if($this->post('status',$res) == 4){
  $screen_msg=$msg=__( 'Entry filtered', 'gravity-forms-salesforce-crm');    
  }else if($this->post('status',$res) == 6){ 
      $status='2';   $screen_msg=$error=$res['error'];
  }else{
  $status=0; $screen_msg_class="error";
  $screen_msg=__('Errors when adding to Salesforce. Entry not sent! Check the Entry Notes below for more details.' , 'gravity-forms-salesforce-crm' );
  if($log_id!=""){
      //message for  bulk actions in logs
  $screen_msg=__('Errors when adding to Salesforce. Entry not sent' , 'gravity-forms-salesforce-crm' );    
  }
  $msg=sprintf(__('Error while creating %s', 'gravity-forms-salesforce-crm'),$feed['object']);
  if($this->post('error',$res)!=""){
      $error= is_array($res['error']) ? json_encode($res['error']) : $res['error'];
  $msg.=" ($error)";
  
  $_REQUEST['VXGSalesforceError']=$msg; //front end form error for admin only
  }   
  if(!$is_admin){
      $info_data['msg']=$msg;
$this->send_error_email($info_data,$entry,$form);
  }    
  } 
  //insert log
  $arr=array("object"=>$feed["object"],"form_id"=>$form_id,"status"=>$status,"entry_id"=>$entry_id,"crm_id"=>$id,"meta"=>$error,"time"=>date('Y-m-d H:i:s'),"data"=>$this->post('data',$res),"response"=>$this->post('response',$res),"extra"=>$this->post('extra',$res),"feed_id"=>$this->post('id',$feed),"link"=>$this->post('link',$res),'parent_id'=>$parent_id,'event'=>$event);

  $settings=get_option($this->type.'_settings',array());
  if($this->post('disable_log',$settings) !="yes"){ 
   $insert_id=$data_db->insert_log($arr,$log_id); 
  } 
    if(!empty($insert_id)){ // 
          $log_url=admin_url( 'admin.php?page=gf_edit_forms&view=settings&subview='.$this->id.'&tab=log&log_id='.$insert_id.'&id='.$form_id);   
  $log_link=' <a href="'.$log_url.'" class="vx_log_link" data-id="'.$insert_id.'">'.__('View Detail','gravity-forms-salesforce-crm')."</a>";
 $screen_msg.=$log_link;
    }
    if($post_comment){
  //insert entry comment 

//  $this->add_note($entry["id"], $msg);
    } 
    
    if($notice!=""){
  $notice.='<br/>';
  } 
  $notice.='<b>'.$object.': </b>'.$screen_msg;  
   
  }
  }

  if(!empty(self::$del_files)){
   foreach(self::$del_files as $k=>$v){
   if(!empty($v) && file_exists($v)){
       unlink($v); unset(self::$del_files[$k]);
   }    
   } 
}
  return array("msg"=>$notice,"class"=>$screen_msg_class);
  }
public function process_tags($entry,$form,$value,$crm_field_id='',$custom=''){
  //starts with { and ends } , any char in brackets except {
  preg_match_all('/\{[^\{]+\}/',$value,$matches);
  if(!empty($matches[0])){
      $vals=array();
   foreach($matches[0] as $m){
       $m=trim($m,'{}');
       $val_cust=$this->verify_field_val($entry,$form,$m,$crm_field_id,$custom);
       if(is_array($val_cust)){ $val_cust=trim(implode(' ',$val_cust)); }   
    $vals['{'.$m.'}']=$val_cust;  
   }
   
  $value=str_replace(array_keys($vals),array_values($vals),$value);
  }
  return $value;
}  
  /**
  * Send error email
  * 
  * @param mixed $info
  * @param mixed $entry
  * @param mixed $form
  */
  public function send_error_email($info,$entry,$form){
        if( trim($this->post('error_email',$info))!=""){
  $subject="Error While Posting to Salesforce";
  $entry_link=add_query_arg(array('page' => 'gf_entries','view'=>'entry', 'id' => $entry['form_id'],'lid'=>$entry['id']), admin_url('admin.php'));  
  $page_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; 
  
  $detail=array(
  "Time"=>date('d/M/y H:i:s',current_time('timestamp')),
  "Page URL"=>'<a href="'.$page_url.'" style="word-break:break-all;">'.$page_url.'</a>',
  "Entry ID"=>'<a href="'.$entry_link.'" target="_blank" style="word-break:break-all;">'.$entry_link.'</a>'
  );
  if(isset($form['title'])){
    $detail["Form Name"]=$form['title'];
  $detail["Form Id"]=$form['id'];
  }
    $email_info=array("msg"=>$info['msg'],"title"=>__('Salesforce','gravity-forms-salesforce-crm')." Error","info_title"=>"More Detail","info"=>$detail);
  $email_body=$this->format_user_info($email_info,true);

  $error_emails=explode(",",$info['error_email']); 
  $headers = array('Content-Type: text/html; charset=UTF-8');
  foreach($error_emails as $email)   
  wp_mail(trim($email),$subject, $email_body,$headers);
  }
  }
  /**
  * Get Objects from local options or from salesforce
  *     
  * @param mixed $check_option
  * @return array
  */
  public function get_objects($info="",$refresh=false){
    
      $data=$this->post('data',$info);
         $api_type=$this->post('api',$data);
         $web_objects=array("Lead"=>"Lead","Case"=>"Case");
  if($api_type == "web"){
  return $web_objects;
  }
    if(empty($info)){
     $option=get_option($this->id.'_meta',array());
    
     return !empty($option['objects']) ? $option['objects'] : '';  
  }
   $objects=array();      
   $meta=$this->post('meta',$info);  

   if(! isset($meta['objects'])){
    $refresh=true;   
   }else{
     $objects=$meta['objects'];  
   } 
  //get objects from salesforce
 if($refresh){
  $api=$this->get_api($info); 
  $objects=$api->get_crm_objects(); 

  if(is_array($objects)){
  $option=get_option($this->id.'_meta',array());
  
    $option_objects=array_merge($objects,$web_objects);
  if(!empty($option['objects']) && is_array($option['objects'])){
   $option_objects=array_merge($option_objects,$option['objects']);   
  }
  
  $option['objects']=$option_objects;
  update_option($this->id.'_meta',$option); //save objects for logs search option
  $meta["objects"]=$objects;
  $this->update_info(array("meta"=>$meta),$info['id']);
  }
 }  
  return $objects;    
 }

    /**
  * check if user conected to crm
  *     
  * @param mixed $settings
  */
  public function api_is_valid($info="") {
     if(!empty($info['data']['api']) && $info['data']['api'] == 'web'){
         return true;
     }
      
  if(isset($info['data'])  && is_array($info['data']) && isset($info['data']['access_token']) && !empty($info['data']['access_token'])){ 
  return true;
  }else{
  return false;}       
  }
}

endif;
$vxg_salesforce=new vxg_salesforce();
$vxg_salesforce->instance();
$vx_gf['vxg_salesforce']='vxg_salesforce';
