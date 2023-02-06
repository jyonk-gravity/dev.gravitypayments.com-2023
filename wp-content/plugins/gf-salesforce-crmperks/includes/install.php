<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


if( !class_exists( 'vxg_install_salesforce' ) ):

class vxg_install_salesforce extends vxg_salesforce{
      public static $sending_req=false;
public function get_roles(){
      $roles=array(
      $this->id."_read_feeds",
      $this->id."_edit_feeds",
      $this->id."_read_logs" , 
      $this->id."_export_logs" , 
      $this->id."_read_settings" , 
      $this->id."_edit_settings" , 
      $this->id."_send_to_crm" , 
      $this->id."_read_license", 
      $this->id."_uninstall"
      );
      return $roles;

}
public function create_roles(){
      global $wp_roles;
      if ( ! class_exists( 'WP_Roles' ) ) {
            return;
        }
$roles=$this->get_roles();
foreach($roles as $role){
  $wp_roles->add_cap( 'administrator', $role );
}
$wp_roles->add_cap( 'administrator', 'vx_crmperks_view_addons' );
$wp_roles->add_cap( 'administrator', 'vx_crmperks_edit_addons' );
}

public function remove_roles(){
      global $wp_roles;
      if ( ! class_exists( 'WP_Roles' ) ) {
            return;
        }
$roles=$this->get_roles();
foreach($roles as $role){
  $wp_roles->remove_cap( 'administrator', $role );
}
}
public function remove_data(){
    global $wpdb;

  //delete options
  delete_option($this->type."_version"); 
  delete_option($this->type."_updates");
  delete_option($this->type."_settings");
     $other_version=$this->other_plugin_version(); 
    if(empty($other_version)){ //if other version not found
  delete_option($this->id."_crm");
  delete_option($this->id."_meta");
  $this->deactivate('uninstall'); 
    $data=$this->get_data_object();
  $data->drop_tables();
  $this->remove_roles();
  }

  $this->deactivate_plugin();
}
public function deactivate_plugin(){
        $slug=$this->get_slug();
          //deactivate 
  deactivate_plugins($slug); 
    update_option('recently_activated', array($slug => time()) + (array)get_option('recently_activated'));
}

}

endif;
