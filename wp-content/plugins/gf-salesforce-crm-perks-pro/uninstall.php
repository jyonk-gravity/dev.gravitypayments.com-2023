<?php
/**
 * Uninstall
 */
 if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}
$path=plugin_dir_path(__FILE__);
include_once($path . "gf-salesforce-crm-perks-pro.php");
 include_once($path . "includes/install.php");
 
   $install=new vxg_install_salesforce();
          $settings=get_option($install->type.'_settings',array());
if(!empty($settings['plugin_data'])){
  $install->remove_data();
}

