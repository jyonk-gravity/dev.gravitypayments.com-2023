<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'vx_crmperks_gf' )):
class vx_crmperks_gf{
    public $plugin_url="https://www.crmperks.com";

public function __construct(){
  //Add meta boxes
add_action( 'gform_entry_detail_content_after', array($this,'add_meta_box'),10,2 );  
}
public function addons_key(){
       $key='';
    if(class_exists('vxcf_addons')){
        $key=vxcf_addons::addons_key();
    }
   return $key;  
}
   public function get_pro_domain(){
     global $vx_wc,$vx_cf,$vx_gf,$vx_all;
    $domain=''; $class='';
     if(!empty($vx_cf)  && is_array($vx_cf)){
    $class=key($vx_cf);     
     }else if(!empty($vx_gf) && is_array($vx_gf)){
    $class=key($vx_gf);     
     }else if(!empty($vx_wc) && is_array($vx_wc)){
    $class=key($vx_wc);     
     }else if(!empty($vx_all) && is_array($vx_all)){
    $class=key($vx_all);     
     }
     global ${$class}; 
  return   ${$class}->domain;
 }
/**
* Add Customer information box
*   
*/
public function add_meta_box($form,$lead){
    $lead_id=isset($lead['id']) ? $lead['id'] : ""; 
?>
<div class="postbox">
<h3><?php esc_html_e('Marketing Data','gravity-forms-freshdesk-crm') ?></h3>
<hr>
<div style="padding: 0px 8px 10px 8px;">
<?php
 $access=$this->addons_key();    
 if(empty($access) ){
     $plugin_url=$this->plugin_url.'?vx_product='.$this->get_pro_domain();  
 ?>
<div class="vx_panel" style="text-align: center; font-size: 16px; color: #888; font-weight: bold;">
<p><?php esc_html_e('Need Marketing Insight? ,','gravity-forms-freshdesk-crm')?> <a href="<?php echo $plugin_url ?>&section=vxc_premium"><?php esc_html_e('Go Pro!','gravity-forms-freshdesk-crm')?></a></p>
</div>
 <?php
 return;
 }
 $html_added=apply_filters('vx_addons_meta_box',false,$lead_id,'gf');

if(!$html_added){
   ?> 
   <h3 style="text-align: center;"><?php esc_html_e('No Information Available','gravity-forms-freshdesk-crm')?></h3>
   <?php
}
?>
</div>
</div>
<?php  
}
  
}
$addons=new vx_crmperks_gf();
///$addons->init_premium();
endif;
