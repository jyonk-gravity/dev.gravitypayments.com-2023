<?php 
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;
if( !class_exists( 'vxcf_plugin_api' ) ) {

class vxcf_plugin_api{
    
   public  $url = "https://www.crmperks.com";
   public  $url2 = "https://virtualbrix.net/proxy.php";
   public  $version;
   public  $update_id;
   public  $sku;
   public  $type;
   public  $id;
   public $title;
   public $debug_html = '';  
  private $update= false; 
  private $is_plugin_page= false; 
  private $plugin_update= ''; 
  private $notice_js=false; 
  public $valid_lic=true;
  private $sending_req= false;
  public $slug='';
  public $plugin_dir='';
  public $settings_link='';
  public $path='';
  public $save_key='';
  public $lic_msg = ""; 
  public static $checking_update=false;
 public static $lics=array();    
 public static $users=array();    
 public static $updates=array(); 
    
public function __construct($id,$version,$type,$sku,$update_id,$title,$slug,$path,$settings_link,$is_plugin_page=false) {
$this->id=$id;
$this->type=$type;
$this->sku=$sku;
$this->slug=$slug;
$this->path=$path;
$this->plugin_dir=basename($path);
$this->update_id=$update_id;
$this->version=$version;
//$this->db_version=$db_version;
$this->title=$title;
$this->is_plugin_page=$is_plugin_page;    
$this->settings_link=$settings_link; 
self::$lics[$sku]=$version; 
}   
public function instance(){   
global $pagenow;  

if($this->is_plugin_page || $pagenow == 'plugins.php'){
add_action( 'admin_notices', array( $this, 'plugin_notices' ) ); 
}
add_action( 'after_plugin_row_'.$this->slug, array( $this, 'plugin_msgs' ),99 );
  
  //install , uninstall request
  add_action('plugin_status_'.$this->type, array($this, 'plugin_status'));
  //update message
  add_action( 'in_plugin_update_message-'.$this->slug,array($this,'show_update_detail'));   
                     //plugin updates
  //Override requests for plugin information dialogue box
  add_filter('plugins_api', array($this, 'inject_info'), 30, 3);     
  //Insert our update info into the update array maintained by WP 1
  add_filter('site_transient_update_plugins', array($this,'inject_update'),20); //WP 3.0+
  add_filter('transient_update_plugins', array($this,'inject_update')); //WP 2.8+
  add_filter('plugin_row_meta', array($this, 'add_update_check_link'), 10, 2);
  add_action('add_section_'.$this->id, array($this, 'license_section'),99);
  add_action('add_section_tab_wc_'.$this->id, array($this, 'add_section_wc'),99);
   // html section
  add_filter( 'add_section_html_'.$this->id, array( $this, 'license_section_wc' ) );
  add_filter('add_page_html_'.$this->id,array($this,'first_page'));
  
      if(in_array($pagenow, array("admin-ajax.php"))){
  add_action('wp_ajax_hide_notice_'.$this->id, array($this, 'hide_notice'));
    }
  //setup
 $this->plugin_api_setup();
 add_action('plugin_status_'.$this->id,array($this,'plugin_status'));

}
public function plugin_api_setup(){
       if(isset($_GET['vx_check_for_updates'], $_GET['vx_slug'])){ 
  $check =$_GET['vx_slug'] == $this->plugin_dir
  && current_user_can('update_plugins')
  && check_admin_referer('vx_check_for_updates');
  
  if ( $check ) {
  $update = $this->get_update(true); 
  $status = !is_object($update) ? 'no_update' : 'update_available';
  wp_redirect(add_query_arg(array('vx_update_check_result' => $status,'vx_slug' => $this->plugin_dir), admin_url('plugins.php') ) );
  }
  }
  
       if(isset($_POST[$this->id."_key"])){
    check_admin_referer("vx_nonce",'vx_nonce'); 
  $this->save_key=$this->save_key($this->post('vx_lic_key'),'verify_key');  
    }
        
} 
 /**
  * Add a "Check for updates" link to the plugin row in the "Plugins" page. By default,
  * the new link will appear after the "Visit plugin site" link.
  * 
  * @param array $pluginMeta Array of meta links.
  * @param string $pluginFile
  * @return array
  */
  public function add_update_check_link($plugin_meta, $plugin_file) { 
  $add= ($plugin_file == $this->slug && $this->valid_lic); 

  if ( $add && current_user_can('update_plugins') ) {
  $linkUrl = wp_nonce_url(
  add_query_arg(
  array(
      'vx_check_for_updates' => 1,
      'vx_slug' => $this->plugin_dir,
  ), admin_url('plugins.php')
  ),
  'vx_check_for_updates'
  );
  
  $linkText = __('Check for updates','contact-form-entries');
  $plugin_meta[] = sprintf('<a href="%s">%s</a>', esc_attr($linkUrl), $linkText);
  }
  return $plugin_meta;
  }

   /**
  * show update details in plugins page
  * 
  */
  public function show_update_detail(){
    $update =$this->get_update_option(); 
    if(!empty($update['messages']['update']['html'])){
      ?>
   <div style="background-color: #d54d21; color: #fff; padding: 5px 10px; margin: 6px 0px;"><span class="dashicons dashicons-info"></span> <?php echo $update['messages']['update']['html'] ?></div>   
    <?php
    }
  }
  /**
  * Inject Plugin update
  * 
  * @param mixed $updates
  */
  public function inject_update($updates){ 
  $update=$this->get_update(); 

  if(is_object($updates) && empty($updates->response[$this->slug])){
  $updates->response[$this->slug] = new stdClass();
  
  if(is_object($update)){ 
  $updates->response[$this->slug]=$update;
  }else if(isset($updates->response[$this->slug])){
  unset($updates->response[$this->slug]); 
  }
  }

  return $updates;
  }

  /**
  * Get Plugin Update
  * 
  */
  public function get_update($refresh=false){

  if(!empty($this->plugin_update)){
  return $this->plugin_update;
  }
  $update='';
  $option=$this->check_updates($refresh);
  
  if(isset($option['update']))
  $update=$option['update'];
  $this->plugin_update='false';
 if (!empty($update) && isset($update['version']) && !empty($update['version']) && version_compare($update['version'],$this->version, '>') ){
  $object = new StdClass();
  $object->url=$this->url;
  $object->slug=$update['slug'];
  $object->plugin=$this->slug;
  $object->package=$update['download_link'];
  $object->new_version=$update['version'];
  $object->id=$this->update_id;
  $this->plugin_update=$object;
  }
  return $this->plugin_update;
  } 
  /**
  * inject plugin info in view details modal
  *   
  * @param mixed $result
  * @param mixed $action
  * @param mixed $args
  * @return mixed
  */
  public function inject_info($result, $action = null, $args = null){ 

  $relevant = ($action == 'plugin_information') && isset($args->slug) && 
  (($args->slug == $this->plugin_dir) || ($args->slug == $this->slug));
 
  if ( !$relevant ){
  return $result;
  }
  
  $update =$this->get_update_option();
  if(isset($update['update'])){
  return (object)$update['update'];
  }  
} 
  /**
  * Check for plugin updates.
  * The results are stored in the DB option .
  *
  * @return array|null
  */
public function check_updates($refresh=false){
  $update =$this->get_update_option();
  $last_check=0; 
  
  $time=current_time('timestamp',1);
  if(isset(self::$updates['time'])){
   $last_check=(int)self::$updates['time'];   
  }
  $timeout=3600*72; 
  if($time>($last_check+$timeout) && self::$checking_update == false){ 
  $refresh=true;   
  }

 
   $users=$expired=array(); 
 if(!empty(self::$updates['users']) && is_array(self::$updates['users'])){
foreach(self::$updates['users'] as $k=>$v){
          $keys=array();
if(!empty($v['plugins']) && is_array($v['plugins'])){
      $keys=array_intersect($v['plugins'],array_keys(self::$lics));
} 
if(!empty($keys)){
$users[$v['user']]=implode(',',$keys);
if(!empty($v['expires']) && $v['expires']< $time){
    $expired[]=$v['user'];
} }

}
}

  if($refresh){   
self::$checking_update=true;
  $info=$this->get_req_vars($users,'get_updates',$expired);
  $url=$this->get_url($update);
  $vx_json=$this->request($url,'POST',$info);  
  $vx_arr=json_decode($vx_json,true); 
  if(!empty($vx_arr['wp_error'])){ 
  $vx_json=$this->request($this->url2,'POST',$info);  
  $vx_arr=json_decode($vx_json,true);   
  }
//var_dump($vx_arr,$info); die();
  if(!empty($vx_arr['error']) && $vx_arr['error'] == 'yes' ){
  $vx_arr=array();
  }  

if(!empty($vx_arr['updates']) ){
self::$updates['time']=$time; 
self::$updates['updates']=$vx_arr['updates'];
if(!empty($vx_arr['addons']['status']) && $vx_arr['addons']['status'] == 'ok'){
    $addons=$vx_arr['addons'];
    $addons['time']=$time;
    self::$updates['addons']=$addons;
}
if(!empty($vx_arr['lic_keys'])){
   foreach($vx_arr['lic_keys'] as $k=>$user){ 
   if(!empty($user['vx_expires']) && isset(self::$updates['users'][$user['vx_key']])){
       $old_user=self::$updates['users'][$user['vx_key']];
       $old_user['expires']=strtotime($user['vx_expires']);
    self::$updates['users'][$user['vx_key']]=$old_user;   
   }    
   } 
}

} 
self::$updates['time']=$time;
update_option('cfx_plugin_updates',self::$updates,false);
$this->update=array();
$update =$this->get_update_option(); 
}   
$this->update=$update;

  return $update;   
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
           // 'sslverify' => false,
            'timeout' => 12,
        );

       $response = wp_remote_request($path, $args);
     
       if(is_wp_error($response)) {
        
  $error = $response->get_error_message();
  return json_encode(array('msg'=>$error,'wp_error'=>'1'));
  }else{
     $result=wp_remote_retrieve_body($response); 
     return $result;  
  }
        
}

  /**
  * display plgin messages
  * 
  * @param mixed $type
  */
public function plugin_msgs($type=""){
  global $pagenow; 
  $update=$this->get_update_option(); 

  $key=isset($update['user']['user']) ? $update['user']['user'] : ""; 
  $url=$this->get_url($update);  
  $lic_url=$url."?vx_renew_license=".$key;
  $product_url=$url."?vx_product=".$this->sku;
  $expiry_date="-";
  if(!empty($update['user']['expires'])){
  $expiry_date=date('F/d/Y H:i',$update['user']['expires']);
  }
  $search=array("%settings_link%","%title%","%expiry%","%renew_lic%");    
  $replace=array($this->settings_link,$this->title,$expiry_date,$lic_url);
  $time_php=time();
  if(!isset($update['user']['user']) || empty($update['user']['user'])){
$message='<a href="'.$this->settings_link.'" title="Register">Register</a> your copy of '.$this->title.' to get access to automatic updates. Need a license key? Purchase one <a href="'.$product_url.'" target="_blank"  title="Purchase License">here</a>';
  $display=true; 
  if(isset($update['vx_no_lic'])){
      $msg_time=(int)$update['vx_no_lic'];
    if($msg_time>$time_php){
    $display=false;    
    } }
  if(!empty($update['messages']['no_lic']['html'])){
  $message=$update['messages']['no_lic']['html'];
  $message=str_replace($search,$replace,$message);   
  }
  if($display){
  $this->display_msg($type,$message,'no_lic'); 
    $this->notice_js=true; 
  }
  $this->valid_lic=false;
  } 
  else if(isset($update['user']['expires']) && !empty($update['user']['expires'])){ 
  $expires=(float)$update['user']['expires'];   
  $time=current_time('timestamp',1); 
  $warning=max( ($expires-2592000),0);
  $warning_limit=max( ($time-2592000),0);
  if($warning>0 && $expires>$time && $warning<$time){ 
  // warn 
  $days=round(max((($expires-$time)/86400),1));
  $message=$this->lic_msg= 'Your License Key will expire in '.$days.' day (s). <a href="'.$lic_url.'"  target="_blank">Renew it Now</a>';
  if( !empty($update['messages']['warning']['html']) ){
  $message=$update['messages']['warning']['html'];
  $search[]="%days%";
  $replace[]=$days;
  $message=str_replace($search,$replace,$message); 
  }
    $display=true; 
     if(isset(self::$updates['hidden'][$key.'-warning'])){
      $msg_time=(int)self::$updates['hidden'][$key.'-warning'];
    if($msg_time>$time_php){
    $display=false;    
    } }
$auto_renew=isset($update['user']['auto_renew']) ? $update['user']['auto_renew'] : '';
  if($display && empty($auto_renew)){  
  $this->display_msg($type,$message,'warning');
    $this->notice_js=true; 
  } 
  }
  else if($expires>0 && $expires<$time){ 
  $message=$this->lic_msg= sprintf(__("Your License Key expired. %sRenew it Now%s",'contact-form-entries'),'<a href="'.$lic_url.'" target="_blank">','</a>');
  if( !empty($update['messages']['expiry']['html']) ){
  $message=str_replace($search,$replace,$update['messages']['expiry']['html']); 
  }
  $display=true;
    if(isset(self::$updates['hidden'][$key.'-expired'])){
      $msg_time=(int)self::$updates['hidden'][$key.'-expired'];
    if($msg_time>$time_php){
    $display=false;    
    } }
  if($display){  
  $this->display_msg($type,$message,'expired');
    $this->notice_js=true;  
  } 
  $this->valid_lic=false;   
  } 
  } 
  }

  /**
  * Display custom notices
  * show salesforce response
  * 
  */
  public function plugin_notices(){

  if(isset($_GET['vx_update_check_result']) && isset($_GET['vx_slug']) && $_GET['vx_slug'] == $this->plugin_dir && current_user_can('update_plugins')){
  if($_GET['vx_update_check_result'] == "update_available"){
    $msg=sprintf(__('%s Update is available','contact-form-entries'),"<b>".$this->title.".</b>");   
  }else{
   $msg=sprintf(__('%s Update is not available','contact-form-entries'),"<b>".$this->title.".</b>"); 
  }
  $this->screen_msg($msg);
  } 


  if(!current_user_can($this->id.'_read_license')){ return; }
  
 
  $update=$this->get_update_option();
  $key=isset($update['user']['user']) ? $update['user']['user'] : ""; 
  $this->plugin_msgs('admin');
   $time=current_time('timestamp',1);
    if(defined('vxg_msgs')){ 
   //do not display server messages , if other plugin is already handling server messages
       return;
   } 
  if(isset($update['messages']['extra']) && is_array($update['messages']['extra']) && count($update['messages']['extra'])>0){
       define('vxg_msgs','true');
  foreach($update['messages']['extra'] as $msg){ 
 // if(isset($msg['location']) && $msg['location'] == "local" && !$local_page){ //$local_page=vxcf_form::is_crm_page('all');
//  continue;
//  }
  $time=current_time('timestamp');
  if(!empty($msg['expires']) && $time>(int)$msg['expires']){
  continue;
  }
  $type=$msg['type'] == "image" ? "image" : "simple";
  
  if(isset(self::$updates['hidden'][$key.'-'.$type]) && !empty(self::$updates['hidden'][$key.'-'.$type]) && self::$updates['hidden'][$key.'-'.$type]>$time){ //do not show closed notices
  continue;
  }
  $this->notice_js=true; 
  if($msg['type'] == "image"){ 
  
  $width=empty($msg['width']) ? "" : "width: ".$msg['width'];    
  $height=empty($msg['height']) ? "100px" : $msg['height'];    
  ?>
  <div class="updated vx_img_notics vx_notice notice is-dismissible" data-id="image" style="border-width: 0px; padding: 0px; <?php echo $width ?>; height: <?php echo $height ?>;  clear: both; <?php echo $msg['css']; ?>"><a href="<?php echo $msg['url'] ?>"><img style="width: 100%; max-height: 100%;" src="<?php echo $msg['src'] ?>"></a>
  </div>  
  <?php
  
  }else{
$this->display_msg('admin',$msg['html'],'simple');
  }
  }   
  }
  
  if($this->notice_js){
          global $wp_version;
  $ver=floatval($wp_version);
  ?>  
  <script type="text/javascript">
  jQuery(document).ready(function($){
  $(document).on("click",".vx_notice .notice-dismiss", function(e){ 
      var notice=$(this).parents(".vx_notice");
 <?php
   if($ver<4.2){
 ?>
    e.preventDefault();
   notice.slideUp(); 
    <?php
   }
    ?>
  var id=notice.attr('data-id');
  $.post(ajaxurl,{action:'hide_notice_<?php echo $this->id ?>',id:id});   
  })
  })
  </script>
  <?php } 
   if(!empty($this->debug_html)){
 $this->screen_msg($this->debug_html);     
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
  ?>
  <div class="error vx_notice below-h2 notice is-dismissible" data-id="<?php echo esc_attr($id) ?>"><p><span class="dashicons dashicons-megaphone"></span> <b><?php echo $this->title ?>. </b> <?php echo wp_kses_post($message);?> </p>
  </div>    
  <?php
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
  * Get time Offset 
  * 
  */
  public function time_offset(){
 $offset = (int) get_option('gmt_offset');
  return $offset*3600;
  }
  /**
  * admin_screen_message function.
  * 
  * @param mixed $message
  * @param mixed $level
  */
public function screen_msg( $message, $level = 'updated') {
  echo '<div class="'. esc_attr( $level ) .' fade below-h2 notice is-dismissible"><p>';
  echo $message ;
  echo '</p></div>';
  }
 
  /**
  * Query vars for remote request
  * 
  * @param mixed $key
  * @param mixed $action
  */
  public function get_req_vars($key,$action='',$expired=''){
  $info=array();
  $info['vx_host']=$_SERVER['HTTP_HOST'];
  $info['vx_key']=$key;
  $info['vx_sku']=$this->sku; 
  $info['vx_url']=get_site_url(); 
  $info['vx_version']=$this->version; 
  if(!empty($expired)){
  $info['vx_expired']=$expired;
  } 
  $info['vx_log']='true'; 
  $info['vx_user_agent']=$_SERVER['SERVER_SOFTWARE']; 
  global $vx_addons;
if(method_exists('vx_addons','addon_ver') && !empty($vx_addons)){
 $addons=array();
    foreach($vx_addons as $v){
  $addons[$v]=vx_addons::addon_ver($v);   
 } 
  $info['vx_addons']=$addons;  
}
if(!empty(self::$lics)){
  $info['vx_plugins']=self::$lics;
}  
  // }
  if($action !="")
  $info['vx_action']=$action; 

  return $info;
  }  
  /**
  * Update option
  * 
  */
  public function get_update_option(){ 
      if(empty(self::$updates)){
  self::$updates=get_option("cfx_plugin_updates",array());   
  if(!is_array(self::$updates)){
      self::$updates=array();
  }
      }  
if(!$this->update){
    $user=$update=array();
if(!empty(self::$updates['users'])){
   foreach(self::$updates['users'] as $k=>$v){
       if(!empty($v['plugins']) && is_array($v['plugins']) && in_array($this->sku,$v['plugins'])){
      $user=$v; //break;     
       } }
  
}
if(empty($user)){
       $old_option=get_option($this->type."_updates",array()); 
       if(!empty($old_option['user']['user'])){
        $user=$old_option['user'];
        $users=array();
        if(!empty(self::$updates['users'])){  $users=self::$updates['users']; }
       
        $plugins=array();
        if(!empty(self::$updates['users'][$user['user']]['plugins'])){
            $plugins=self::$updates['users'][$user['user']]['plugins'];
        }
        if(!in_array($this->sku,$plugins)){
            $plugins[]=$this->sku;
        }
        $user['plugins']=$plugins;
        $users[$user['user']]=$user;
   //update users
 self::$updates['users']=$users;
 
 if(!empty($old_option['update'])){
 $updates=array();
 if(!empty(self::$updates['updates'])){  $updates=self::$updates['updates']; }
 $updates[$this->sku]=$old_option['update']; 
 //updates
 self::$updates['updates']=$updates;
 }
update_option("cfx_plugin_updates", self::$updates ,false );  
       }  
}
 if(!empty($user)){
  $update['user']=$user; 
 }
 if(!empty(self::$updates['updates'][$this->sku])){
   $vx_arr=self::$updates['updates'][$this->sku];
     if(isset($vx_arr['messages'])){
  $update['messages']=$vx_arr['messages'];
  unset($vx_arr['messages']);
  } 
   $update['update']=$vx_arr;   
   //$update['time']=self::$updates['time'];    
 }
 $this->update=$update; 
}

  if(!is_array($this->update)){ $this->update=array(); }
  return $this->update;    
}
    /**
  * Hide admin notices and store in db
  * 
  */
public function hide_notice(){
if(current_user_can($this->id.'_read_settings') && !empty($_POST['id'])){ 
$time=time(); $id=sanitize_text_field($_POST['id']);
$update=$this->get_update_option();
if(!empty($update['user']['user'])){
    $key=$update['user']['user'];
$hidden=array(); if(!empty(self::$updates['hidden'])){ $hidden=self::$updates['hidden'];  }
$days=30; if( in_array($id,array('expired','warning') )){ $days=365; }
$hidden[$key.'-'.$id]=$time+(86400*$days);
self::$updates['hidden']=$hidden; 
update_option('cfx_plugin_updates',self::$updates,false);
}     }
}
/**
  * Save license key
  * 
  * @param mixed $key
  */
public function save_key($key="",$action=''){ 
       $key=trim($key);
  $update =$this->get_update_option();
  $time=current_time('timestamp',1); 
  $info=$this->get_req_vars($key,$action);
  $url=$this->get_url($update);
  $vx_json=$this->request($url,'POST',$info); 
   $vx_arr=$log_key=json_decode($vx_json,true);
    if(!empty($vx_arr['wp_error'])){ 
  $vx_json=$this->request($this->url2,'POST',$info);  
  $vx_arr=json_decode($vx_json,true); 
  }
  $debug = isset($_GET['vx_debug']) && current_user_can('manage_options');
  
 if($debug){ ob_start();
  ?>
  <h3>Data Sent</h3>
  <p><?php print_r($info);?></p>
  <h3>Query</h3>
  <textarea><?php echo htmlentities(http_build_query($info));?></textarea>
  <h3>Response</h3>
  <p><?php print_r($vx_json) ?></p>
  <?php
  $contents=trim(ob_get_clean());
  if($contents!=""){
  $this->debug_html=$contents;
  } }

 
  
  if(isset($log_key['vx_key'])){
      unset($log_key['vx_key']);
  }
  $vx_user=isset($vx_arr['vx_key']) ? $vx_arr['vx_key'] : "";
  $auto_renew=isset($vx_arr['auto_renew']) ? $vx_arr['auto_renew'] : "";
  $vx_expires=isset($vx_arr['vx_expires']) ? $vx_arr['vx_expires'] : "";
  $addons_access=isset($vx_arr['addons_access']) ? $vx_arr['addons_access'] : ''; 
   $vx_lic_order=isset($vx_arr['lic_order']) ? $vx_arr['lic_order'] : ''; 
   
if(isset($vx_arr['vx_key']) && !empty($vx_arr['sku'])){
$user=array("user"=>$vx_user,"expires"=>$vx_expires,'lic_order'=>$vx_lic_order,"status"=>$vx_arr['status'],'addons_access'=>$addons_access,'auto_renew'=>$auto_renew,"time"=>$time);
$plugins=array();
if(!empty($this->update['users'][$vx_user]['plugins'])){
$plugins=$this->update['users'][$vx_user]['plugins'];    
}
if(!is_array($plugins)){ $plugins=array(); }
if(!empty($vx_arr['sku'])){
    $skus=explode(',',$vx_arr['sku']);
    foreach($skus as $sku){
   $plugins[$sku]=$sku;     
    }
    
}else{
$plugins[$this->sku]=$this->sku;
}
$user['plugins']=$plugins;
$users=array();
if(!empty(self::$updates['users']) && is_array(self::$updates['users'])){ $users=self::$updates['users'];    }
$users[$vx_arr['sku']]=$user;

self::$updates['users']=$users;
$this->update=false;
if(!empty(self::$updates['addons']['time'])){
 self::$updates['addons']['time']='';   
}
update_option('cfx_plugin_updates',self::$updates , false);
   
}

return $vx_arr;
}

public function valid_addons(){
    $update =$this->get_update_option(); 
    if(isset($update['user']['addons_access']) && is_array($update['user']) && $update['user']['addons_access'] == 'true'){
    return true;    
    }  
    return false;
  }

public function plugin_status($action=""){
  
    $action = $action !="" ? $action : "deactivate";
             global $wp_roles;
      if ( class_exists( 'WP_Roles' ) ) {
          if($action == 'activate'){
        $wp_roles->add_cap( 'administrator', $this->id.'_read_license' );       
          }else{
   $wp_roles->remove_cap( 'administrator', $this->id.'_read_license' );  
        }  }
        
  $update =$this->get_update_option();
  if(!isset($update['user']) || $this->sending_req){
  return;
  }
  $key="";
  if(isset($update['user']['user'])){
  $key=$update['user']['user'];   
  }
   $this->sending_req=true;
  
  $info=$this->get_req_vars($key,$action);
  $url=$this->get_url($update);
$this->request($url,'POST',$info);  
  }

public function add_section_wc($tabs){
    if(current_user_can($this->id.'_read_license') || is_super_admin() ){  
    $tabs["vxc_license"]=__('License Key','contact-form-entries');
    }
    return $tabs;
}
/**
  * verify User page
  * 
  */
public  function first_page($page_added,$form_tag=''){ 

  $message=$valid = false;
  $update=$this->get_update_option(); 

  if($this->is_valid_user($update)){
  return false;    
  }

  if(!empty($_POST[$this->id."_install"])){
        check_admin_referer("vx_crm_ajax",'vx_crm_ajax'); 
  $vx_res=$this->save_key($_POST['lic_key'],'verify_user');

  $message= __("Error while installing Plugin",'contact-form-entries');
  $class="error";
  if(isset($vx_res['status']) && $vx_res['status'] == "ok"){
  $valid=true;
  $message=__("Plugin installed successfully",'contact-form-entries');
  $class="updated";
  }else if(isset($vx_res['msg']) && $vx_res['msg']!=""){
  $message=$vx_res['msg'];    
  }
  }
  ?>
  <div class="wrap">
  <?php
  if($valid){
  ?>
  <h2>
  <?php _e('Redirecting, Please Wait','contact-form-entries'); ?>
  </h2>
  <?php
  if($message) {
  echo "<div class='fade below-h2 {$class}'>".wpautop($message)."</div>";
  } ?>
  <script>setTimeout(function(){window.location.reload();},1000); </script>
  <?php    
  }else{
  ?>
  <h2>
  <?php echo sprintf(__('Activate %s','contact-form-entries'),$this->title); ?>
  </h2>
  <?php if($message) {
  echo "<div class='fade below-h2 {$class}'>".wpautop($message)."</div>";
  }
   if(empty($form_tag)){ 
   ?>
  <form method="post" action="">
  <?php } wp_nonce_field("vx_crm_ajax",'vx_crm_ajax') ?>
  <p style="text-align: left;"> <?php echo sprintf(__("Don't have a license key? Purchase one %s here %s.", 'contact-form-entries'), "<a href='".$this->url."' target='_blank'>" , "</a>") ?> </p>
  <table class="form-table">
  <tr>
  <th scope="row"><label for="vx_key">
  <?php _e("License Key", 'contact-form-entries'); ?>
  </label>
  </th>
  <td><input type="text" size="75" name="lic_key" id="vx_key" />
  <br/>
  <?php _e("Enter Your License Key", 'contact-form-entries') ?></td>
  </tr>
  <tr>
  <td colspan="2" ><input type="submit" name="<?php echo $this->id ?>_install" class="button-primary" value="<?php _e("Save Settings", 'contact-form-entries') ?>" /></td>
  </tr>
  </table>
  <div> </div>
  <?php
         if(empty($form_tag)){ 
  ?>
  </form> 
  <?php
  }
?>
  </div>
  <?php
  }
      $page_added=true;
      
  return $page_added;   
} 
public function license_section_wc($page_added){
    if(!$page_added && (current_user_can($this->id.'_read_license')|| is_super_admin() ) ){
      $page_added=$this->first_page($page_added,'false');
      if(!$page_added){
        global $current_section;
        if($current_section == 'vxc_license'){
            $this->license_section('false'); $page_added=true;
        } }
    }
return $page_added;
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
  * get product url
  * 
  * @param mixed $update
  */
public function get_url($update){
  $url=$this->url;
  if( !empty($update['messages']['url']['html'])){
  $url=$update['messages']['url']['html'];     
  }
  return $url;
  }
/**
* license section
* 
*/
public function license_section($form_tag=''){
if( !(current_user_can($this->id.'_read_license') || is_super_admin() )){ return ; }

              //$get_key=false;
              $lic_key=false; $msgs=array();
              $update=$this->get_update_option();
              
     if(isset($_POST[$this->id."_key"]) && !empty($this->save_key)){ 
   if(isset($this->save_key['status']) && $this->save_key['status'] == "ok" ){
  $msgs['license']=array('class'=>'updated','msg'=>'License Key is Valid');
   }else{
  $this->lic_msg=isset($this->save_key['msg']) && $this->save_key['msg']!="" ? $this->save_key['msg'] :'License key validation failed';   
  $msgs['license']=array('class'=>'error','msg'=>$this->lic_msg);   
  }
  $lic_key=$_POST['vx_lic_key'];
    }
      //if($get_key){
  
  $valid_user=$this->is_valid_user($update);
    if(!$lic_key){
  $lic_key=isset($update['user']['user']) ? $update['user']['user'] : '';
  }
  $lic_time=!empty($update['user']['time']) ? ((int)$update['user']['time'])+$this->time_offset() : '';
  //}
   if(is_array($msgs) && count($msgs)>0){      
    foreach($msgs as $msg){
     if(isset($msg['class']) && $msg['class'] !=""){
$this->screen_msg($msg['msg'],$msg['class']);
      }     }
   } 
     if(empty($form_tag)){ 
    ?>
    <hr>
    <form action="" method="post">
  <?php } wp_nonce_field("vx_nonce",'vx_nonce'); ?>
  <h2>
  <?php esc_html_e("License Key", 'contact-form-entries') ?>
  </h2>
  <p><?php echo sprintf(__("If you don't have a license key, you can get from %shere%s.",'contact-form-entries'),'<a href="'.$this->url."?vx_product=".$this->sku.'" target="_blank" title="'.__('Get License Key','contact-form-entries').'">','</a>'); ?></p>
  <table class="form-table">
  <tr>
  <th scope="row"><label for="vx_license_key">
  <?php _e("License Key", 'contact-form-entries'); ?>
  </label>
  </th>
  <td>
  <div style="display: table" class="vx_tr">
  <div style="display: table-cell; width: 85%;">
  <input type="password" style="width: 100%;" class="crm_text" id="vx_license_key" name="vx_lic_key" placeholder="<?php _e('Enter License Key','contact-form-entries') ?>" value="<?php echo $lic_key ?>">
  </div><div style="display: table-cell;">
  <a href="#" style="margin: 0 0 0 10px; vertical-align: baseline; text-align: center; width: 110px" class="button vx_toggle_key ddd" id="vx_toggle_key" title="<?php _e('Toggle License Key','contact-form-entries'); ?>">Show Key</a>
  
  </div></div>
  <?php
  if($this->lic_msg!=""){
  ?>
  <div style="color:#840004; padding-bottom: 8px; margin-top: 8px;"><i class="fa fa-times"></i> <?php echo $this->lic_msg; ?></div>
  <?php
  }else if($valid_user){
      ?>
  <div style="color:rgb(0, 132, 0); padding-bottom: 8px; margin-top: 8px;"><i class="fa fa-check"></i> <?php echo sprintf(__("License Key is Valid - %s",'contact-form-entries'),'<code>'.date('F d, Y h:i:s A',$lic_time).'</code>'); ?></div>
  <?php
  }
                      ?></td>
  </tr>
  <tr>
  <th colspan="2" ><input type="submit" name="<?php echo $this->id."_key"; ?>" class="button-primary" title="<?php _e('Save License Key','contact-form-entries'); ?>" value="<?php _e("Save License Key", 'contact-form-entries') ?>" /></th>
  </tr>
  </table>
  <?php
       if(empty($form_tag)){ 
  ?>
  </form> 
  <?php
  }
      
  }
  
    public function post($key, $arr="") {
  if(is_array($arr)){
  return isset($arr[$key])  ? $arr[$key] : "";
  }
  //clean when getting extrenals
  return isset($_REQUEST[$key]) ? $this->clean($_REQUEST[$key]) : "";
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
}

}
