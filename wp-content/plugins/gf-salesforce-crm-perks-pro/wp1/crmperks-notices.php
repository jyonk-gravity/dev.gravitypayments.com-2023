<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'vxgf_salesforce_notice' )):
class vxgf_salesforce_notice{
public $plugin_url="https://www.crmperks.com";
public $review_link='https://wordpress.org/support/plugin/gf-salesforce-crmperks/reviews/?filter=5#new-post';
public $option='vxg-sales';
public $slug='gf-salesforce-crm-perks-pro/gf-salesforce-crm-perks-pro.php';

public function __construct(){

add_action( 'add_section_vxg_salesforce', array($this,'tab'),10);
add_action( 'add_section_mapping_vxg_salesforce', array($this,'upgrade_notice'),10);
add_filter( 'plugin_row_meta', array( $this , 'pro_link' ), 10, 2 );

add_action( 'after_plugin_row_'.$this->slug, array( $this, 'plugin_msgs' ) );
add_action( 'wp_ajax_vxg_sales_review_dismiss', array( $this, 'review_dismiss' ) );

if(isset($_GET['page']) && $_GET['page'] == 'gf_edit_forms' && isset($_GET['subview']) && $_GET['subview'] == 'vxg_salesforce' ){
add_filter( 'admin_footer_text', array( $this, 'admin_footer' ), 1, 2 );
add_filter( 'menu_links_vxg_salesforce', array( $this, 'menu_link' ), 60 );
add_filter( 'tab_contents_vxg_salesforce', array( $this, 'tab_pro' ), 60 );
}

}


public function menu_link($links){

    $current_page=isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : '';
    $logs_link=admin_url( "admin.php?page=gf_edit_forms&view=settings&subview=vxg_salesforce&tab=go_pro&id={sanitize_text_field('id')}" );
    $links['go_pro']=array( 
  "title"=>__('Go Premium','gravity-forms-salesforce-crm'),
  "link"=>$logs_link,
   "current"=>$current_page == 'go_pro' ? true : false
  );
  if($current_page == 'go_pro'){
      $links['feed']['current']=false;
  }
  return $links;
}
public function tab_pro($added){
  
  if(!empty($_GET['tab']) && $_GET['tab'] == 'go_pro'){  
$this->notice(); 
 $added=true;
  }
return $added; 
}
public function tab($added){
$this->notice(); 
}
public function upgrade_notice(){
   $plugin_url=$this->plugin_url.'?vx_product='.$this->option;
?>
<div style="clear: both; padding-top: 30px;"></div>
<div class="updated below-h2" style="border: 1px solid  #1192C1; border-left-width: 6px; padding: 20px 12px;">
<h3>Premium Version</h3>
<p><i class="fa fa-check" style="color: #727f30; font-size: 18px; vertical-align: middle;"></i> Salesforce Custom fields.</p>
<p><i class="fa fa-check" style="color: #727f30; font-size: 18px; vertical-align: middle;"></i> Salesforce Phone fields.</p>
<p><i class="fa fa-check" style="color: #727f30; font-size: 18px; vertical-align: middle;"></i> Upload attachments to "Files" section of Salesforce.</p>
<p><i class="fa fa-check" style="color: #727f30; font-size: 18px; vertical-align: middle;"></i> Add Lead/Contact to Campaign.</p>
<p><i class="fa fa-check" style="color: #727f30; font-size: 18px; vertical-align: middle;"></i> Assign Account to Salesforce Contact.</p>
<p><i class="fa fa-check" style="color: #727f30; font-size: 18px; vertical-align: middle;"></i> Assign Object(Contact, Lead etc) Owner in Salesforce.</p>
<p><i class="fa fa-check" style="color: #727f30; font-size: 18px; vertical-align: middle;"></i> Assign One Object(Contact, Lead etc) to other object in Salesforce.</p>
<p>By purchasing the premium version of the plugin you will get access to advanced marketing features and you will get one year of free updates & support</p>
<p>
<a href="<?php echo $plugin_url ?>" target="_blank" class="button-primary button">Go Premium</a>
</p>
</div> 
<?php
$this->review_notice();
}
public function notice(){
$this->upgrade_notice();
    ?>
<div class="updated below-h2" style="border: 1px solid  #1192C1; border-left-width: 6px; padding: 20px 12px;">
<h3>Our Other Free Plugins</h3>
<p><b><a href="https://wordpress.org/plugins/crm-perks-forms/" target="_blank">CRM Perks Forms</a></b> is lightweight and highly optimized contact form builder with Poups and floating buttons.</p>
<p><b><a href="https://wordpress.org/plugins/woo-salesforce-plugin-crm-perks/" target="_blank">WooCommerce Salesforce Plugin</a></b> allows you to quickly integrate WooCommerce Orders with Salesforce CRM.</p>

</div>
<?php

}

  /**
  * display plgin messages
  * 
  * @param mixed $type
  */
public function plugin_msgs($type=""){
    $plugin_url=$this->plugin_url.'?vx_product='.$this->option;
    $message=__('This plugin has Premium add-ons and many powerful features.','crm-perks-forms');
    $message.=' <a href="'.$plugin_url.'" target="_blank" style="font-color: #fff; font-weight: bold;">'.__('Go Premium','crm-perks-forms').'</a>';
?>
  <tr class="plugin-update-tr"><td colspan="5" class="plugin-update">
  <style type="text/css"> .vx_msg a{color: #fff; text-decoration: underline;} .vx_msg a:hover{color: #eee} </style>
  <div style="background-color: rgba(224, 224, 224, 0.5);  padding: 5px; margin: 0px 10px 10px 28px "><div style="background-color: #d54d21; padding: 5px 10px; color: #fff" class="vx_msg"> <span class="dashicons dashicons-info"></span> <?php echo wp_kses_post($message) ?>
</div></div></td></tr>
<?php 
  }
public function pro_link($links,$file){
    if($file == $this->slug){
    $url=$this->plugin_url.'?vx_product='.$this->option;
        $links[]='<a href="'.$url.'"><b>Go Premium</b></a>';
    } 
   return $links; 
}

public function review_dismiss() {
    $install_time=get_option($this->option."_install_data");
    if(!is_array($install_time)){ $install_time =array(); }
$install_time['review_closed']='true';
update_option($this->option."_install_data",$install_time,false);
die();
}
public function admin_footer($text) {

$text=sprintf(__( 'if you enjoy using %sGravity Forms Salesforce%s, please %s leave us a %s rating%s. A %shuge%s thank you in advance.','crm-perks-forms'),'<b>','</b>','<a href="'.$this->review_link.'" target="_blank" rel="noopener noreferrer">','&#9733;&#9733;&#9733;&#9733;&#9733;','</a>','<b>','</b>');
 return $text;
}
public function review_notice() { 
 $install_time=get_option($this->option."_install_data");
   if(!is_array($install_time)){ $install_time =array(); }
   if(empty($install_time['time'])){
       $install_time['time']=current_time( 'timestamp' , 1 );
      update_option($this->option."_install_data",$install_time,false); 
   }
    $time=current_time( 'timestamp' , 1 )-(3600*12);
  // $install_time['review_closed']='';
 if(!empty($install_time) && is_array($install_time) && !empty($install_time['time']) && empty($install_time['review_closed'])){
   $time_i=(int)$install_time['time'];
    if($time > $time_i){ 
        ?>
        <div class="notice notice-info is-dismissible vxcf-review-notice" style="margin: 14px 0 -4px 0">
  <p><?php echo sprintf(__( 'You\'ve been using %sGravity Forms Salesforce%s for some time now; we hope you love it!.%s If you do, please %s leave us a %s rating on WordPress.org%s to help us spread the word and boost our motivation.','contact-form-entries'),'<b>','</b>','<br/>','<a href="'.$this->review_link.'" target="_blank" rel="noopener noreferrer">','&#9733;&#9733;&#9733;&#9733;&#9733;','</a>'); ?></p>
    <p><a href="<?php echo $this->review_link ?>"  target="_blank" rel="noopener noreferrer" class="vxcf_close_notice_a"><?php esc_html_e('Yes, you deserve it','crm-perks-forms') ?></a> | <a href="#" id="vxcf_close_notice_a" class="vxcf_close_notice_a"><?php esc_html_e('Dismiss this notice','crm-perks-forms'); ?></a></p>
        </div>
        <script type="text/javascript">
            jQuery( document ).ready( function ( $ ) {
           $( document ).on( 'click', '.vxcf-review-notice .vxcf_close_notice_a', function ( e ) {
                      // e.preventDefault(); 
                       $('.vxcf-review-notice .notice-dismiss').click();
 //$.ajax({ type: "POST", url: ajaxurl, async : false, data: {action:"vxcf_form_review_dismiss"} });          
        $.post( ajaxurl, { action: 'vxg_sales_review_dismiss' } );
                });
            });
        </script>
        <?php
    } }
} 

}
new vxgf_salesforce_notice();
endif;
