<?php 
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'vxcf_addons' )):
class vxcf_addons{
    public $plugin_update=false;
    public $version='1.0';
  public static $url='https://www.crmperks.com';

  public $plugin_domain='';
    public $is_addon=false;
    public $id='vx-addons';
    public $addons=array();
    public $force_updates=false;
    
public function __construct(){ 
global $pagenow;
add_action("admin_menu",array($this,"admin_menu"),999); 
//add_action('crmperks_menu_links_end',array($this,'addons_link'),999); 
//add_action("admin_menu",array($this,"main_menu"),70); 
add_filter('install_plugin_complete_actions', array($this, 'manage_addons_action'), 10, 2);
add_filter('update_plugin_complete_actions', array($this, 'manage_addons_action'), 10, 2);
add_filter( 'plugins_api', array( $this, 'plugin_info' ), 11, 3 );
add_filter( 'site_transient_update_plugins', array( $this, 'add_plugins_info' ), 99 );
if($pagenow == "plugins.php"){
add_action( 'admin_notices', array( $this, 'show_addons_link' ),99 ); 
}
 
if(defined('DOING_AJAX')) {
add_action('wp_ajax_vx_manage_addons', array($this, 'manage_addons_ajax'));
        }
$this->setup(); 

}

public function setup(){
    
  if(isset($_GET['action']) && current_user_can( 'vx_crmperks_edit_addons' )){
     $plugin="";
       if(isset($_GET['plugin']) && in_array($_GET['action'] , array("activate","deactivate","upgrade-plugin"))){
     $plugin=$_GET['plugin'];     
      }else if($_GET['action'] == "delete-selected" && isset($_GET['checked'][0])){
     $plugin=$_GET['checked'][0];     
      }
    if(!empty($plugin)){
    $plugin=urldecode($plugin);
    $p_arr=explode("/",$plugin);
     $addons=$this->get_addons();
     if(isset($addons[$p_arr[0]])){
   update_option('vx_addons_actions',$_GET['action'],false);      
     }   
    }   
  } 
  if(isset($_REQUEST['vx_action']) && $_REQUEST['vx_action'] == "get_addons" && current_user_can( 'vx_crmperks_edit_addons' )){
     $this->force_updates=true; 
  } 
}
public function show_addons_link(){
    $action=get_option('vx_addons_actions','');
if(!empty($action) && !isset($_GET['action'])){
  ?>
   <div class="updated notice is-dismissible"><p><?php echo sprintf(__('Go to %sCRM Perks%s Page to manage Add-ons','contact-form-entries'),'<a href="'.$this->get_link('settings').'"><b>','</b></a>');?> </p>
  </div>  
  <?php
  update_option('vx_addons_actions','',false);   
}
}
public function main_menu(){
               $page_title =__('CRM Perks','contact-form-entries');
        $menu_title = __('CRM Perks','contact-form-entries');
        $capability = 'vx_crmperks_view_addons';
        $function   = array( $this, 'vx_addons');
     
add_menu_page($page_title, $menu_title, $capability, $this->id,$function,'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxNy4wLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+DQo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4Ig0KCSB3aWR0aD0iMjU2cHgiIGhlaWdodD0iMjk0LjUxM3B4IiB2aWV3Qm94PSIwIDAgMjU2IDI5NC41MTMiIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDI1NiAyOTQuNTEzIiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxwYXRoIGZpbGw9IiNGMkYyRjIiIGQ9Ik0yMzguMzQ1LDIxMS45MTJsLTExMS4xNjUsNjMuMjM0TDE2LjgzNSwyMTAuNDkxbDAuODIxLTEyNy44ODlsMTExLjE2NS02My4yMzRsMTEwLjM0NSw2NC42NTUNCglMMjM4LjM0NSwyMTEuOTEyeiBNMTc4LjYyMSwxODEuNTA3Yy0zLDEuMTQzLTQuOTY5LDIuMTk4LTguMDAxLDMuMTY0Yy0zLjAzMiwwLjk2OC01Ljg1OSwxLjgyNS04Ljk3NywyLjU3MQ0KCWMtMy4xMjEsMC43NDgtNi4xMTYsMS4zMTgtOS4yMzUsMS43MTRjLTMuMTIxLDAuMzk2LTYuMDI0LDAuNTkzLTguODM2LDAuNTkzYy01LjgwMSwwLTExLjA0My0wLjk0My0xNS43ODktMi44MzQNCgljLTQuNzQ2LTEuODg5LTguNzk3LTQuNjE0LTEyLjE3OS04LjE3NGMtMy4zODQtMy41Ni01Ljk5MS03Ljg4OC03LjgzNi0xMi45ODZjLTEuODQ2LTUuMDk2LTIuNzY1LTEwLjg1NC0yLjc2NS0xNy4yNzENCgljMC02LjE1MSwwLjg1OS0xMS43OTksMi41NzMtMTYuOTQxczQuMTk3LTkuNTEzLDcuNDUtMTMuMTE4YzMuMjUtMy42MDMsNy4yNTEtNi40MTUsMTEuOTk4LTguNDM4DQoJYzQuNzQ2LTIuMDIxLDEwLjEwNi0zLjAzMiwxNi4wODQtMy4wMzJjNS43MTIsMCwxMS4wODEsMC43NDgsMTcuNDU1LDIuMjQxYzYuMzcyLDEuNDk2LDExLjA1OSwzLjYwNSwxNy4wNTksNi4zMjhWODQuMDgNCgljLTUtMS40MDUtOS40MTMtMi41NzEtMTUuMzQ1LTMuNDk0cy0xMi45MTMtMS4zODQtMjEuNjE0LTEuMzg0Yy0xMC42MzUsMC0yMC4xMTQsMS42Ny0yOC43Nyw1LjAxDQoJYy04LjY1OCwzLjM0MS0xNS45NzksOC4wODctMjIuMTMsMTQuMjM4Yy02LjE1Myw2LjE1My0xMC44OCwxMy41NzktMTQuMjYyLDIyLjI4Yy0zLjM4NCw4LjcwMS01LjA1NSwxOC40MTQtNS4wNTUsMjkuMTM2DQoJYzAsMTEuMDc0LDEuNTcsMjAuODMsNC42OTEsMjkuMjY4YzMuMTE5LDguNDM4LDcuNjI5LDE1LjUxMywxMy41MTgsMjEuMjI2YzUuODg3LDUuNzE0LDEzLjA1NCwxMC4wMiwyMS40OTIsMTIuOTINCglzMTcuOTc0LDQuMzUxLDI4LjYxLDQuMzUxYzcuMzgzLDAsMTMuOTU1LTAuNjE2LDIwLjcyNC0xLjg0NmM2Ljc2Ny0xLjIzLDEyLjE0Mi0zLjExOSwxOS4xNDItNS42NjlWMTgxLjUwN3oiLz4NCjwvc3ZnPg0K',999);

} 
public function addons_link($tabs){
?><a href="<?php echo admin_url('options-general.php?page=vx-manage-addons'); ?>" class="nav-tab">Addons</a><?php

}
public function admin_menu(){ 
           $page_title =__('CRM Perks Add-ons','contact-form-entries');
        $menu_title = __('CRM Perks Add-ons','contact-form-entries');
        $capability = 'vx_crmperks_view_addons';
        $function   = array( $this, 'vx_addons');
//add_menu_page($page_title, $menu_title, $capability, $this->id,$function,'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxNy4wLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+DQo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4Ig0KCSB3aWR0aD0iMjU2cHgiIGhlaWdodD0iMjk0LjUxM3B4IiB2aWV3Qm94PSIwIDAgMjU2IDI5NC41MTMiIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDI1NiAyOTQuNTEzIiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxwYXRoIGZpbGw9IiNGMkYyRjIiIGQ9Ik0yMzguMzQ1LDIxMS45MTJsLTExMS4xNjUsNjMuMjM0TDE2LjgzNSwyMTAuNDkxbDAuODIxLTEyNy44ODlsMTExLjE2NS02My4yMzRsMTEwLjM0NSw2NC42NTUNCglMMjM4LjM0NSwyMTEuOTEyeiBNMTc4LjYyMSwxODEuNTA3Yy0zLDEuMTQzLTQuOTY5LDIuMTk4LTguMDAxLDMuMTY0Yy0zLjAzMiwwLjk2OC01Ljg1OSwxLjgyNS04Ljk3NywyLjU3MQ0KCWMtMy4xMjEsMC43NDgtNi4xMTYsMS4zMTgtOS4yMzUsMS43MTRjLTMuMTIxLDAuMzk2LTYuMDI0LDAuNTkzLTguODM2LDAuNTkzYy01LjgwMSwwLTExLjA0My0wLjk0My0xNS43ODktMi44MzQNCgljLTQuNzQ2LTEuODg5LTguNzk3LTQuNjE0LTEyLjE3OS04LjE3NGMtMy4zODQtMy41Ni01Ljk5MS03Ljg4OC03LjgzNi0xMi45ODZjLTEuODQ2LTUuMDk2LTIuNzY1LTEwLjg1NC0yLjc2NS0xNy4yNzENCgljMC02LjE1MSwwLjg1OS0xMS43OTksMi41NzMtMTYuOTQxczQuMTk3LTkuNTEzLDcuNDUtMTMuMTE4YzMuMjUtMy42MDMsNy4yNTEtNi40MTUsMTEuOTk4LTguNDM4DQoJYzQuNzQ2LTIuMDIxLDEwLjEwNi0zLjAzMiwxNi4wODQtMy4wMzJjNS43MTIsMCwxMS4wODEsMC43NDgsMTcuNDU1LDIuMjQxYzYuMzcyLDEuNDk2LDExLjA1OSwzLjYwNSwxNy4wNTksNi4zMjhWODQuMDgNCgljLTUtMS40MDUtOS40MTMtMi41NzEtMTUuMzQ1LTMuNDk0cy0xMi45MTMtMS4zODQtMjEuNjE0LTEuMzg0Yy0xMC42MzUsMC0yMC4xMTQsMS42Ny0yOC43Nyw1LjAxDQoJYy04LjY1OCwzLjM0MS0xNS45NzksOC4wODctMjIuMTMsMTQuMjM4Yy02LjE1Myw2LjE1My0xMC44OCwxMy41NzktMTQuMjYyLDIyLjI4Yy0zLjM4NCw4LjcwMS01LjA1NSwxOC40MTQtNS4wNTUsMjkuMTM2DQoJYzAsMTEuMDc0LDEuNTcsMjAuODMsNC42OTEsMjkuMjY4YzMuMTE5LDguNDM4LDcuNjI5LDE1LjUxMywxMy41MTgsMjEuMjI2YzUuODg3LDUuNzE0LDEzLjA1NCwxMC4wMiwyMS40OTIsMTIuOTINCglzMTcuOTc0LDQuMzUxLDI4LjYxLDQuMzUxYzcuMzgzLDAsMTMuOTU1LTAuNjE2LDIwLjcyNC0xLjg0NmM2Ljc2Ny0xLjIzLDEyLjE0Mi0zLjExOSwxOS4xNDItNS42NjlWMTgxLjUwN3oiLz4NCjwvc3ZnPg0K');
       $menu_title = __('Manage Add-ons','contact-form-entries');
//add_submenu_page($this->id,$menu_title,$menu_title,$capability,$this->id,$function);
$function   = array( $this, 'vx_addons');
                global $admin_page_hooks;
       // var_dump($GLOBALS['admin_page_hooks'],$admin_page_hooks); die('-------------');
     $menu_id='vx-addons'; $addon_menu='vx-manage-addons';    
//if(!empty($admin_page_hooks[$menu_id])){
// add_submenu_page($menu_id,$page_title,$page_title,$capability,$addon_menu,array( $this,'mapping_page'));
//     }else{
 add_options_page( $page_title,$page_title,$capability,$addon_menu,$function);
 //add_utility_page($page_title, $page_title,$capability, $addon_menu,$function);
 //    }
     

}
  /**
  * plugin base url
  * 
  */
  public static function get_base_url(){
  return plugin_dir_url( dirname(__FILE__) );
  }
public static function get_pro_domain(){
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
  * Go Premium Plugin Page
  * 
  */
public static function premium_page($html='',$domain=''){ 
global $current_section; 
$domain=self::get_pro_domain();
$plugin_url=self::$url;
if(!empty($domain)){
$plugin_url.='?vx_product='.$domain;  
}
$url=self::get_base_url(); 
?>
<style type="text/css">
    .vx_row{
padding: 40px 10px;
margin: 0px 0px;
border-bottom: 0px solid #ddd;
        font-family: 'Open Sans', sans-serif; /*"Raleway",*/
    }
    .vx_row h1{
        text-align: center;
     font-weight: 300;
        font-size: 35px;
  
        line-height: normal;
        display: inline-block;
        width: 100%;
 
    }
    .vx_row:nth-child(even){

    }
    .vx_row:nth-child(odd){
      background: #fff;
    }
    .vx_row .vx_row_title img{
        display: table-cell;
        vertical-align: middle;
        width: auto;
        margin-right: 15px;
    }
    .vx_row h2{
        display: inline-block;
        vertical-align: middle;
        padding: 0;
        font-size: 24px;
    }



    .vx_row p{
        font-size: 13px;
        margin: 25px 0;
    }


    .vx_row_inner:after{
        display: block;
        clear: both;
        content: '';
    }
    .vx_row_inner .col-1,
    .vx_row_inner .col-2{
        float: left;
        box-sizing: border-box;
        padding: 0 15px;
    }
    .vx_row_inner .col-1 img{
        width: 100%;
        margin-bottom: 15px;
    }
    .vx_row_inner .col-1{
        width: 50%;
    }
    .vx_row_inner .col-2{
        width: 50%;
    }


    @media (max-width: 700px){
        .wrap{
            margin-right: 0;
        }
        .vx_row{
            margin: 0;
        }
        .vx_row_inner .col-1,
        .vx_row_inner .col-2{
            width: 100%;
            padding: 0 15px;
            float: none;
        }
    }

    .col-1 img{
-webkit-box-shadow: 0px 0px 11px -2px rgba(110,110,110,1);
-moz-box-shadow: 0px 0px 11px -2px rgba(110,110,110,1);
box-shadow: 0px 0px 11px -2px rgba(110,110,110,1);
    }
    .gform_tab_content{
        overflow: visible !important;
    }
</style>
<div class="updated" style="border-left-color: #1192C1; margin: 30px 20px 30px 0px">
<h2>Premium Version</h2>
<p>By purchasing the premium version of the plugin you will get access to advanced marketing features and you will get one year of free updates & support</p>
<p>
<a href="<?php echo $plugin_url ?>" target="_blank" class="button-primary button">Go Premium</a>
</p>
</div>
<div style="margin-left: -20px">

    <div class="vx_row " style="padding-top: 4px;">
<?php if(empty($html)){ ?><h1>Premium Features</h1> <?php }else{ echo $html; } ?>
        <div class="vx_row_inner" style="margin-top: 20px;">
            <div class="col-1">
                <img src="<?php echo $url ?>images/premium/marketing-data.png" alt="Premium Features" />
            </div>
            <div class="col-2">
                <div class="vx_row_title">
                    <h2>Google Analytics</h2>
                </div>
                <p>Google Analytics Standard Parameters (utm_source, utm_medium, utm_term, utm_content, utm_campaign) of a lead are tracked automatically. You can track custom parameters too
                </p>
        <p><a href="<?php echo $plugin_url ?>" target="_blank" class="button-primary button">Go Premium</a></p>
            </div>
        </div>
    </div>
    <div class="vx_row  clear">
        <div class="vx_row_inner">
            <div class="col-2">
                <div class="vx_row_title">
                    <h2>Geolocation Tracking</h2>
                </div>
                <p>Geolocation of each customer includes City , State , Zip Code, Country and Geo Coordinates.This location is displayed on Google Map.
            <br>For Google Map you can set your own API key from Google console</p>
       <p><a href="<?php echo $plugin_url ?>" target="_blank" class="button-primary button">Go Premium</a></p>
                   
            </div>
            <div class="col-1">
                <img src="<?php echo $url  ?>images/premium/location-map.png" alt="Geolocation Tracking" />
            </div>
        </div>
    </div>
    <div class="vx_row  clear">
        <div class="vx_row_inner">
            <div class="col-1">
                <img src="<?php echo $url ?>images/premium/analytics.png" alt="Browsing History" />
            </div>
            <div class="col-2">
                <div class="vx_row_title">
                    <h2>Capture Leads</h2>
                </div>
                <p>Plugins collects visitor information like IP, ISP, City and pages browsed etc.You can convert anonymous web visitors to potential leads.</p>
       <p><a href="<?php echo $plugin_url ?>" target="_blank" class="button-primary button">Go Premium</a></p>
            </div>
        </div>
    </div>
        <div class="vx_row  clear">
        <div class="vx_row_inner">
            <div class="col-2">
                <div class="vx_row_title">
                    <h2>Lead Scoring</h2>
                </div>
    <p>You can set a score to each page.We show aggregate score along with browsing history. Lead score is basically "interest score" of a customer</p>
           <p><a href="<?php echo $plugin_url ?>" target="_blank" class="button-primary button">Go Premium</a></p>
            </div>
            <div class="col-1">
                <img src="<?php echo $url ?>images/premium/lead-scoring.png" alt="Lead Scoring" />
            </div>
        </div>
    </div>
        <div class="vx_row  clear">
        <div class="vx_row_inner">
            <div class="col-1">
                <img src="<?php echo $url ?>images/premium/customer-platform.png" alt="Customer Platform" />
            </div>
            <div class="col-2">
                <div class="vx_row_title">
                    <h2>Customer Platform</h2>
                </div>
                <p>You can track customer's Browser , Operating System , Landing Page , Referer and IP Address
                </p>
              <p><a href="<?php echo $plugin_url ?>" target="_blank" class="button-primary button">Go Premium</a></p>         
            </div>
        </div>
    </div>
            <div class="vx_row  clear">
        <div class="vx_row_inner">
            <div class="col-2">
                <div class="vx_row_title">
                    <h2>Fields Mapping</h2>
                </div>
    <p>You can send all marketing data of a lead to your CRM. We have 20+ CRM Plugins</p>
    <p><a href="<?php echo $plugin_url ?>" target="_blank" class="button-primary button">Go Premium</a></p>
            </div>
            <div class="col-1">
                <img src="<?php echo $url ?>images/premium/fields-mapping.png" alt="Fields Mapping" />
            </div>
        </div>
    </div>
    <div class="vx_row  clear">
        <div class="vx_row_inner">
            <div class="col-1">
                <img src="<?php echo $url ?>images/premium/email-lookup.png" alt="Email Lookup" />
            </div>
            <div class="col-2">
                <div class="vx_row_title">
                    <h2>Email Lookup</h2>
                </div>
                <p>Lookup lead's email using email lookup apis.We support all googd email lookup apis like Fullcontact , Towerdata and pipl.com API</p>
             <p><a href="<?php echo $plugin_url ?>" target="_blank" class="button-primary button">Go Premium</a></p>          
            </div>
        </div>
    </div>
            <div class="vx_row  clear">
        <div class="vx_row_inner">
            <div class="col-2">
                <div class="vx_row_title">
                    <h2>Phone Lookup</h2>
                </div>
    <p>Verify lead's phone number and get detailed information about phone number using phone lookup apis, We support many good phone lookup apis like everyoneapi , whitepages api , twilio api and numverify api.</p>
           <p><a href="<?php echo $plugin_url ?>" target="_blank" class="button-primary button">Go Premium</a></p>
            </div>
            <div class="col-1">
                <img src="<?php echo $url ?>images/premium/phone-lookup.png" alt="Phone Lookup" />
            </div>
        </div>
    </div>
        <div class="vx_row  clear">
        <div class="vx_row_inner">
            <div class="col-1">
                <img src="<?php echo $url ?>images/premium/weather-api.png" alt="Weather Information" />
            </div>
            <div class="col-2">
                <div class="vx_row_title">
                    <h2>Weather Information</h2>
                </div>
                <p>You can get weather information of a lead's geolocation using openweathermap API.</p>
                       <p><a href="<?php echo $plugin_url ?>" target="_blank" class="button-primary button">Go Premium</a></p>
            </div>
        </div>
    </div>
    <div class="vx_row  clear">
        <div class="vx_row_inner">
            <div class="col-2">
                <div class="vx_row_title">
                    <h2>Export Entries</h2>
                </div>
    <p>You can export Woocommerce Orders , Gravity Forms and Contact Form entries to any crm in bulk.</p>
           <p><a href="<?php echo $plugin_url ?>" target="_blank" class="button-primary button">Go Premium</a></p>
            </div>
            <div class="col-1">
                <img src="<?php echo $url ?>images/premium/export-entries.png" alt="Export Entries" />
            </div>
        </div>
    </div>
<div class="vx_row  clear">
        <div class="vx_row_inner">
            <div class="col-1">
                <img src="<?php echo $url ?>images/premium/notification.png" alt="Notifications" />
            </div>
            <div class="col-2">
                <div class="vx_row_title">
                    <h2>Notifications</h2>
                </div>
                <p>Sends a notification (sms/call/email/browser push notification) for new entry.</p>
                       <p><a href="<?php echo $plugin_url ?>" target="_blank" class="button-primary button">Go Premium</a></p>
            </div>
        </div>
    </div>
            <div class="vx_row  clear">
        <div class="vx_row_inner">
            <div class="col-2">
                <div class="vx_row_title">
                    <h2>User Assignment</h2>
                </div>
    <p>Override object(lead, contact etc) assignment or object owner in any CRM plugin feed. You can set assignment rules.like assign one by one to all agents.</p>
       <p><a href="<?php echo $plugin_url ?>" target="_blank" class="button-primary button">Go Premium</a></p>    
            </div>
            <div class="col-1">
                <img src="<?php echo $url ?>images/premium/user-assignment.png" alt="User Assignment" />
            </div>
        </div>
    </div>
  <div class="vx_row  clear">
        <div class="vx_row_inner">
            <div class="col-1">
                <img src="<?php echo $url ?>images/premium/entries-feeds.png" alt="Entries Feeds" />
            </div>
            <div class="col-2">
                <div class="vx_row_title">
                    <h2>Incoming Webhooks</h2>
                </div>
                <p>Creates a entry in Contact Forms Entries Plugin from posted data.</p>
                       <p><a href="<?php echo $plugin_url ?>" target="_blank" class="button-primary button">Go Premium</a></p>
            </div>
        </div>
    </div>
            <div class="vx_row  clear">
        <div class="vx_row_inner">
            <div class="col-2">
                <div class="vx_row_title">
                    <h2>Outgoing Webhooks - Zapier</h2>
                </div>
    <p>Send Contact Form Entries to Zapier or any web hook.</p>
           <p><a href="<?php echo $plugin_url ?>" target="_blank" class="button-primary button">Go Premium</a></p>
            </div>
            <div class="col-1">
                <img src="<?php echo $url ?>images/premium/zapier.png" alt="Zapier" />
            </div>
        </div>
    </div>  
    </div> 
<?php  
}

public function vx_addons(){

$url=self::get_base_url();
$addons=$this->check_updates();
if(!empty($addons['addons'])){
    $this->addons=$addons['addons'];
}
//check_updates($refresh=false);
//$addons='';
$access=self::addons_key(); 
//$access='';
 if(empty($access)){
 self::premium_page();   
 }else{
 //var_dump($addons); die();
 $install_addon=current_user_can( 'vx_crmperks_edit_addons' );
 
   ?>
 <style type="text/css">
  .vx_box{
    width: 310px;
    display: block;
    position: relative;
    margin: 10px 14px 20px;
    border: 0px solid #bbb;
    box-shadow: 2px 2px 6px 0 #e9ebee;
    border-radius: 0px; 
    float: left; overflow: hidden;
  }
   .vx_box .vx_addon_img{
       vertical-align: bottom; width: 100%;
   }
  .vx_box_head{
      background: #f0f0f0;
      padding: 10px;
      font-weight: bold;
      font-size: 16px;
     /* border-top-left-radius: 2px;  
      border-top-right-radius: 2px;*/
      border: 1px solid #ccc;
       border-top-width: 0;
       border-bottom-width: 0;  
  }
  .vx_box_info{
      background: #fff;
      padding: 7px 10px 0px 10px;
       border: 1px solid #ccc;
       border-top-width: 0;
       border-bottom-width: 0;
  }
  .vx_box_desc{
    height: 66px;
    padding: 5px 0px;  
  }
  .vx_box_contents{
      padding: 7px;
      background: #fff;
      border: 1px solid #ccc;
      border-top-width: 0;
      min-height: 156px;
  }
  .vx_box_status{
      background: #ddd;
      padding: 5px;
      font-size: 14px;
      font-weight: bold;
      text-align: center;
      margin-bottom: 10px;
  }
  .vx_color1{
    color: #447700;
    border: 1px solid #89b84a;
    background: #e2f2cc;  
  } 
   .vx_color2{
    color: #777;
    border: 1px solid #d7d7d7;
    background: #f0f0f0;  
  }
    .vx_color0{
    color: #be9c21;
    border: 1px solid #eecb51;
    background: #fff6d6;  
  }
  .vx_box_footer{
      margin: 5px 0px;
  }
  .vx_alert{
      border: 0px solid #BD2121; position: absolute; top: 0;
      padding: 8px; 
      margin: 4px 4px; 
      color:#BD2121;
      font-weight: bold;
/*      background: rgba(169, 0, 0, 0.06);*/
      background: #fff;
  }
  </style>
 <script type="text/javascript">
  jQuery(document).ready(function($){
      $('#vx_addon_cats_h2 a').click(function(e){
          e.preventDefault();
          $('.nav-tab-active').removeClass('nav-tab-active');
          $(this).addClass('nav-tab-active');
         var clas=$(this).attr('data-class');
         if(clas){
        $('.vx_box').hide();     
        $('.'+clas).show();     
         }else{
         $('.vx_box').show();     
         } 
      })
     $(document).on('click', '.vx_box_footer_link',function(e){
         e.preventDefault();
     var button=$(this);
     var text=button.text();
     button.attr('disabled','disabled');
     button.text('<?php _e('Wait...','contact-form-entries') ?>');
     var box=button.parents(".vx_box");
     var url=button.attr('href');
     $.post(ajaxurl,{action:'vx_manage_addons',vx_nonce:'<?php echo wp_create_nonce('vx_nonce'); ?>',url:url},function(res){
     button.text(text);
      button.removeAttr('disabled');
     var re=$.parseJSON(res);
     if(re.status && re.status == "ok"){
     box.html(re.html);    
     }else{
         alert(re.msg);
     }    
     })    
     }) 
  })
  </script>
  <div class="wrap" data-class="<?php echo $this->id ?>"> 
     <h2><?php _e('Manage Add-ons','contact-form-entries'); ?></h2>
  <p><?php _e('You can activate, deactivate and delete add-ons here.','contact-form-entries'); ?> </p>
  <?php
      if(!empty($addons['cats'])){
  ?>
<h2 class="nav-tab-wrapper" id="vx_addon_cats_h2" style="margin-bottom: 20px;">
<?php
    foreach($addons['cats'] as $k=>$v){
       $active= $k == '' ? 'nav-tab-active' : '';
 echo '<a href="#" class="nav-tab '.$active.'" data-class="'.$k.'">'.$v.'</a>';       
 
    }
?></h2>
  <?php
      } 
   //   $addons=json_decode($json,true);
      if(isset($addons['addons']) && is_array($addons['addons']) && count($addons['addons'])>0){
    foreach($addons['addons'] as $slug=>$plugin){ ///var_dump($slug);
    $cats=!empty($plugin['cats']) ? $plugin['cats'] : '';
        ?>
<div class="vx_box <?php echo $cats; ?>">
        <?php 
$status=$this->plugin_status($plugin['class'],$slug);
 $this->addon_box($plugin,$status,$install_addon);
 ?>
 </div>
 <?php
    }
}else{
 $refresh=admin_url( 'admin.php?page='.$this->id."&vx_action=get_addons");   
    ?>
<div style="text-align: center; padding-top: 50px;">  <form method="post"><button name="vx_action" class="button button-hero" value="get_addons"><?php _e('Refresh Add-ons List','contact-form-entries') ?></button></form>
  
</div>
    <?php
}
?>
</div>
<?php
 }
}
public static function addons_key(){
    $keys=array(); 

if(class_exists('vxcf_plugin_api') && !empty(vxcf_plugin_api::$lics)  ){
  $users=array();
    if(empty(vxcf_plugin_api::$updates['users']) ){
    vxcf_plugin_api::$updates=get_option("cfx_plugin_updates",array());    
    }
    if(is_array(vxcf_plugin_api::$updates['users'])){
    foreach(vxcf_plugin_api::$updates['users'] as $user){
        if(!empty($user['addons_access']) && !empty($user['user']) && !empty($user['plugins']) && is_array($user['plugins']) ){ 
            $skus=array_keys(vxcf_plugin_api::$lics);
        $common_keys=array_intersect($user['plugins'],$skus);
        if(!empty($common_keys)){    
   $keys[]=$user['user'];
        }         
        }
    } }  
}
   return $keys;  
}
public function get_addons(){
    $addons=$this->check_updates();
    
    return !empty($addons['addons']) ? $addons['addons'] : array();
}
  /**
  * Check for plugin updates.
  * The results are stored in the DB option .
  *
  * @return array|null
  */
public function check_updates($refresh=false){   

  $update =$this->get_updates();  
  $time=current_time('timestamp',1);   
  if(!$refresh){
      
  $last_check=(int)$this->post('time',$update);
  $timeout=3600*96; 
  if($time>($last_check+$timeout)){ 
  $refresh=true;   
  }    }

  $user_key=array();
if($this->force_updates || $refresh){        
$user_key=self::addons_key();
}
  if($refresh && empty($user_key)){     
      $refresh=false;
  }
             
  if($this->force_updates || $refresh){
    global $vx_addons;
      $this->force_updates=false;
  $info=array();
  $info['vx_host']=$_SERVER['HTTP_HOST'];
  $info['vx_key']=$user_key;
  $info['vx_sku']='gen_addons'; 
  $info['vx_action']='addon_updates'; 
  $info['vx_url']=get_site_url(); 
  $info['vx_log']="true"; 
  $info['vx_user_agent']=$_SERVER['SERVER_SOFTWARE']; 

  $vx_json=$this->request(self::$url,'POST',$info);
  $vx_arr=json_decode($vx_json,true);  
  $updates=get_option('cfx_plugin_updates',array());
   $addons=array();
   if(!empty($updates['addons'])){
       $addons=$updates['addons'];
   } 
   $vx_arr['time']=$time;
  if(is_array($vx_arr)){
  if(!is_array($updates)){ $updates=array(); }
  if(!empty($vx_arr['error']) && $vx_arr['error'] == 'yes' ){
  $vx_arr=array();
  }
  $addons=$vx_arr;
  }   
  $addons['time']=$time;
  $updates['addons']=$addons;
  $this->plugin_update=$addons;
  update_option('cfx_plugin_updates',$updates,false);
  } 
   
  return  is_array($update) ? $update : array();   
  }

public function addon_box($plugin,$status,$install_addon){ 
    $status_msg=array(__('Not Installed','contact-form-entries'),__('Active','contact-form-entries'),__('Disabled','contact-form-entries'));
    if(!empty($plugin['banners']['low'])){
    ?>
    <img src="<?php echo $plugin['banners']['low']; ?>" class="vx_addon_img" />
      <?php
    } 
      if($status == 1 && isset($plugin['require']) && isset($this->addons[$plugin['require']]) ){
            $addon=$this->addons[$plugin['require']];
         if(isset($addon['class']) && !class_exists($addon['class'])){
       
  ?>
  <div class="vx_alert"><?php echo sprintf(__('This Plugin requires %s plugin','contact-form-entries'),"<i>".$addon['name']."</i>") ?></div>
  <?php
      }}
           $new_version=false; $addon_version=self::addon_ver($plugin['class']);
if($status == 1 && !empty($addon_version) && version_compare($addon_version,$plugin['version'],"<") ){
     $new_version=true;      
  ?>
  <div class="vx_alert"><?php echo sprintf(__('New Version %s is available.','contact-form-entries'),"<i>".$plugin['version']."</i>") ?></div>
  <?php
 }
  ?>
  <div class="vx_box_head"><?php echo $plugin['name'] ?></div>
  <div class="vx_box_info"><?php _e('Updated :','contact-form-entries'); ?> <i><?php echo $plugin['last_updated'] ?></i> &nbsp;&nbsp;&nbsp; <?php _e('Version :','contact-form-entries'); ?>  <i><?php echo $plugin['version'] ?></i></div>
  <div class="vx_box_contents">
  <div class="vx_box_desc"><?php echo $plugin['sections']['Description'] ?></div>
  <div class="vx_box_status vx_color<?php echo $status ?>"><?php echo $status_msg[$status]; ?></div>

 <div class="vx_box_footer">
  <?php 

      if($status== 0 && $install_addon){
      $install=$this->get_link('install',$plugin['plugin']);    
  ?>
  <a href="<?php echo $install ?>" class="button button-primary"><?php _e('Install','contact-form-entries'); ?></a>
  <a href="<?php echo $plugin['package']; ?>" class="button button-primary"><?php _e('Download','contact-form-entries'); ?></a>
  <?php
      }else  if($status== 2 && $install_addon ){
      $activate=$this->get_link('activate',$plugin['plugin']);
      $delete=$this->get_link('delete',$plugin['plugin']);         
  ?>
    <a href="<?php echo $activate ?>" class="button button-primary vx_box_footer_link"><?php _e('Activate','contact-form-entries'); ?></a>
     <a href="<?php echo $delete ?>" class="button"><?php _e("Delete",'contact-form-entries'); ?></a> 
  <?php
      }else if($status== 1 ){ 
if($install_addon){
 $deactivate=$this->get_link('deactivate',$plugin['plugin']);  
  ?>
    <a href="<?php echo  $deactivate ?>" class="button button-primary vx_box_footer_link"><?php _e("Deactivate",'contact-form-entries'); ?></a>
  <?php
  }
  global ${$plugin['class']};
  $cl=${$plugin['class']};
if(!is_object($cl) && class_exists($plugin['class'])){
 $cl=new $plugin['class'];
} 
 if(isset($cl->page)){
    $page_name=$cl->page;
 $settings_link=admin_url('admin.php?page='.$cl->page);
  ?>
      <a href="<?php echo  $settings_link ?>" class="button button-secondary"><?php _e("Go to Settings",'contact-form-entries'); ?></a>   
     <?php
 }
      }
if($new_version && $install_addon){
$install=$this->get_link('upgrade',$plugin['plugin']);    
?>
<a href="<?php echo $install ?>" class="button button-primary"><?php _e('Upgrade','contact-form-entries'); ?></a>
    <?php 
   } 
  ?>
  </div>
  </div>
    <?php
}

public static function addon_ver($class){
 $ver='';
 if(property_exists($class,'version')){
  $c=new ReflectionProperty($class, 'version'); 
  $ver=$c->getValue();  
 }
 return $ver;
}
public function plugin_status($class,$slug,$force=false){
          $status=0;
        if(class_exists($class)){
      $status=1;
  }
  if($force){
   $status=0;
   if(is_plugin_active($slug)){   
   $status=1;   }
  }
   if(empty($status) && file_exists(WP_PLUGIN_DIR.'/'.$slug)) {
  $status=2;
  }
  return $status;
}
public function add_plugins_info($update){ 

  $addons=$this->get_addons();

  if(isset($addons) && is_array($addons)){
      foreach($addons as $k=>$v){ 
     if(isset($v['class']) && class_exists($v['class'])){ 
         $installed_version=self::addon_ver($v['class']);
     if(is_object($update) && !isset($update->response[$v['plugin']])){
  $update->response[$v['plugin']] = new stdClass();
     }
  if (version_compare($v['version'], $installed_version, '>') ){ 
unset($v['sections']);
      $update->response[$v['plugin']] = (object) $v;
         }else{
 unset($update->response[$v['plugin']]);            
         }
     }     
      } //var_dump($update);

  }

return $update;
}

    /**
  * Get plugin updates.
  * The results are stored in the DB option .
  *
  * @return array|null
  */
public function get_updates(){    
  if($this->plugin_update === false){
$updates=get_option('cfx_plugin_updates',array());
  $this->plugin_update=!empty($updates['addons']) ? $updates['addons'] : array();
  }
  return $this->plugin_update; 
}
    /**
  * Send Request
  * 
  * @param mixed $body
  * @param mixed $path
  * @param mixed $method
  */
  public function request($path="",$method='POST',$body="",$head=array()) { 
  
  
  if($path=="")
  $path = $this->url;
  
  $args = array(
  'body' => $body,
  'headers'=> $head,
  'method' => strtoupper($method), // GET, POST, PUT, DELETE, etc.
  'sslverify' => false,
  'timeout' => 20,
  );
  
  $response = wp_remote_request($path, $args);
  
  if(is_wp_error($response)) { 
  $this->errorMsg = $response->get_error_message();
  return false;
  } else if(isset($response['response']['code']) && $response['response']['code'] != 200 && $response['response']['code'] != 404) {
  $this->errorMsg = strip_tags($response['body']);
  return false;
  } else if(!$response) {
  return false;
  }
  $result=wp_remote_retrieve_body($response);
  return $result;
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
  return isset($_REQUEST[$key]) ? $_REQUEST[$key] : "";
  }
/**
     * Provides download package when installing and information on the "View version x.x details" page.
     *
     * @uses api_request()
     *
     * @param mixed $_data
     * @param string $_action
     * @param object $_args
     * @return object $_data
     */
public function plugin_info( $data, $action = '', $args = null ) {

$slug = isset( $args->slug ) ? $args->slug : $this->post( 'plugin' );    

    $addons=$this->get_addons(); 
    $plugin=urldecode($slug);
    $slug_arr=explode("/",$plugin);
 
    if(isset($slug_arr[0]) && isset($addons[$slug_arr[0]])){
     $data=(object)$addons[$slug_arr[0]];
     if(isset($data->package)){
     $data->download_link=$data->package;    
     }
     $this->is_addon=true;   
    }         
        // don't allow other plugins to override the $request this function returns, several plugins use the 'plugins_api'
        // filter incorrectly and return a hard 'false' rather than returning the $_data object when they do not need to modify
        // the request which results in our customized $request being overwritten (WPMU Dev Dashboard v3.3.2 is one example)
    //    remove_all_filters( 'plugins_api' );
        
        // remove all the filters causes an infinite loop so add one dummy function so the loop can break itself
    //    add_filter( 'plugins_api', create_function( '$_data', 'return $_data;' ) );
        
        // needed for testing on local
  //      add_filter( 'http_request_args', array( $this, 'allow_unsecure_urls_on_localhost' ) );
        
        return $data;
}
public function manage_addons_action( $actions, $plugin_file ) {

        $action=$this->post('action');
        $plugin=$this->post('plugin');
        if(in_array($action,array('install-plugin','upgrade-plugin')) && $this->is_addon){
            $actions['plugins_page'] = '<a href="'.$this->get_link('settings').'">' . __('Back to CRM Perks Page', 'contact-form-entries') . '</a>';      
        }

        return $actions;
    }    
/**
* get links
* 
* @param mixed $type
* @param mixed $plugin_file
*/
public function get_link($type, $plugin_file="") {


        switch($type) {

        case 'activate':
            return wp_nonce_url( admin_url("plugins.php?action=activate&plugin=$plugin_file"), "activate-plugin_{$plugin_file}");

        case 'deactivate':
            return wp_nonce_url( admin_url("plugins.php?action=deactivate&plugin=$plugin_file"), "deactivate-plugin_{$plugin_file}");

        case 'uninstall':
            return wp_nonce_url( admin_url("plugins.php?action=uninstall&plugin=$plugin_file" ), "uninstall-plugin_{$plugin_file}" );

        case 'delete':
            $page = "plugins.php?action=delete-selected&checked[0]=$plugin_file&vx_plugin=1";
            $url = is_multisite() ? network_admin_url( $page . '&blog_id=' . get_current_blog_id() ) : admin_url( $page );
            return wp_nonce_url( $url, "bulk-plugins" );

        case 'install':
            $page = "update.php?action=install-plugin&plugin=$plugin_file&vx_plugin=1";
            // @TODO: might not need to pass blog ID anymore since we no longer are sending to network page for install
            $url = is_multisite() ? admin_url( $page . '&blog_id=' . get_current_blog_id() ) : admin_url($page);
            return wp_nonce_url( $url, "install-plugin_$plugin_file");

        case 'upgrade':
            $page = "update.php?action=upgrade-plugin&plugin={$plugin_file}&vx_plugin=1";
            $url = is_multisite() ? network_admin_url( $page . '&blog_id=' . get_current_blog_id() ) : admin_url($page);
            return wp_nonce_url( $url, "upgrade-plugin_{$plugin_file}");
        case'settings':
        return admin_url( 'admin.php?page='.$this->id);
        }
    }
    
public function manage_addons_ajax() {
    if(!current_user_can("vx_crmperks_edit_addons")){
    die(json_encode(array("status"=>"error","msg"=>__('Access Denied', 'contact-form-entries'))));
  }
check_ajax_referer("vx_nonce","vx_nonce"); 
        $request = parse_url($this->post('url'));
        parse_str($this->post('query',$request), $request);
      $error="";  
  //   if ( ! current_user_can('activate_plugins') )
  //   $error=__('You do not have sufficient permissions to manage plugins for this site.', 'contact-form-entries');
        
        if(empty($request)) {
        $error=__('Add-on name not specified.', 'contact-form-entries');
        }
  if(!empty($error)){
        $res=array("status"=>"error","msg"=>$error);
  die(json_encode($res));  
  } 
        $action = $this->post('action',$request);
        $plugin = $this->post( 'plugin',$request);
        $_REQUEST['_wpnonce'] = $this->post('_wpnonce',$request);

        switch($action) {
        case 'deactivate':
                check_admin_referer('deactivate-plugin_' . $plugin);
                
                deactivate_plugins( $plugin, false, is_network_admin() );

                if ( ! is_network_admin() ){
                    update_option( 'recently_activated', array( $plugin => time() ) + (array) get_option( 'recently_activated' ) );
                }
        
                break;
                
            case 'activate':
            
                check_admin_referer('activate-plugin_' . $plugin);
                
                $result = activate_plugin($plugin, null, is_network_admin() );
     
        if ( is_wp_error( $result ) ) {
                    if ( 'unexpected_output' == $result->get_error_code() ) {
                        $error = $result->get_error_data();
                    } else {
                        $error = $result;
                    }
        }      
                if (empty($error) && ! is_network_admin() ) {

                    $recent = (array) get_option( 'recently_activated' );
                    unset( $recent[ $plugin ] );
                    update_option( 'recently_activated', $recent );

                }
                break;

            case 'uninstall':
                check_admin_referer( 'uninstall-plugin_' . $plugin );

                deactivate_plugins( $plugin, true );

                $result = delete_plugins( array( $plugin ) );
if(!$result){
    $error= __('ERROR', 'contact-form-entries');
}
                break;
        }
      $plugins=$this->get_addons(); 
          $plugin=urldecode($plugin);
    $p_arr=explode("/",$plugin); 
    ///  var_dump($plugins,$plugin); die();
      if(!isset($plugins[$p_arr[0]]['class'])){
          $error= __('Plugin Not Found', 'contact-form-entries'); 
      }
 
    if(!empty($error)){
        $res=array("status"=>"error","msg"=>$error);
    }else{         
 $status=$this->plugin_status($plugins[$p_arr[0]]['class'],$plugin,true);

 $install_addon=current_user_can( 'vx_crmperks_edit_addons' );
ob_start();
 $this->addon_box($plugins[$p_arr[0]],$status,$install_addon);
$html=ob_get_clean(); 
    $res=array("status"=>"ok","html"=>$html);
    }
die(json_encode($res));
    } 
}
$vxcf_addons=new vxcf_addons();
endif;

