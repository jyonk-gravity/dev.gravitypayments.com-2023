<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if(!class_exists('vxg_salesforce_api')){
    
class vxg_salesforce_api extends vxg_salesforce{
  
public $info=array();
  public $error= "";
  public $timeout=30;
  public $api_version='v57.0';
  public $api_res='';
  
function __construct($info) { 
        if(isset($info['data'])){
  $this->info= $info['data'];
      }
if(!empty(self::$api_timeout)){
    $this->timeout=self::$api_timeout;
}

  }
  
  /**
  * Get New Access Token from salesforce
  * @param  array $form_id Form Id
  * @param  array $info (optional) Salesforce Credentials of a form
  * @param  array $posted_form (optional) Form submitted by the user,In case of API error this form will be sent to email
  * @return array  Salesforce API Access Informations
  */
public function get_token($info=""){
  if(!is_array($info)){
  $info=$this->info;
  }
  if(!isset($info['refresh_token']) || empty($info['refresh_token'])){
   return $info;   
  }
  $client=$this->client_info(); 
  ////////it is oauth    
  $body=array("client_id"=>$client['client_id'],"client_secret"=>$client['client_secret'],"redirect_uri"=>$client['call_back'],"grant_type"=>"refresh_token","refresh_token"=>$info['refresh_token']);
     $env='login';
      if( !empty($info['env'])){
       $env='test';  
      }
  $res=$this->post_sales('token',"https://$env.salesforce.com/services/oauth2/token","post",$body);

  $re=json_decode($res,true); 
  if(isset($re['access_token']) && $re['access_token'] !=""){ 
  $info["access_token"]=$re['access_token'];
  $info["instance_url"]=$re['instance_url'];
  $info["issued_at"]=$re['issued_at'];
//  $info["org_id"]=$re['id'];
  $info["class"]='updated';
  $token=$info;
  }else{
  $info['error']=isset($re['error_description']) ? $re['error_description'] : '';
  $info['access_token']="";
   $info["class"]='error';
  $token=array(array('errorCode'=>'406','message'=>$re['error_description']));

  }
  $info["valid_api"]=current_time('timestamp')+86400; //api validity check
  //update salesforce info 
  //got new token , so update it in db
  $this->update_info( array("data"=> $info),$info['id']); 
  return $info; 
  }
public function handle_code(){
      $info=$this->info;
      $id=$info['id'];

        $client=$this->client_info();
  $log_str=$res=""; $token=array();
  if(isset($_REQUEST['code'])){
  $code=$this->post('code');   
  if(!empty($code)){
      $env='login';
      if(!empty($_REQUEST['vx_env']) || !empty($info['env'])){
       $env='test'; $info['env']='test';  
      }
  $body=array("client_id"=>$client['client_id'],"client_secret"=>$client['client_secret'],"redirect_uri"=>$client['call_back'],"grant_type"=>"authorization_code","code"=>$code);
  $res=$this->post_sales("token","https://$env.salesforce.com/services/oauth2/token","post",$body);
  
  $log_str="Getting access token from code";
   $token=json_decode($res,true); 
   if(!isset($token['access_token'])){
      $log_str.=" =".$res; 
   }
  }
  if(isset($_REQUEST['error'])){
   $token['error_description']=$this->post('error_description');   
  }
  }else{  
  //revoke token on user request

  if(isset($info['instance_url']) && $info['instance_url']!="")
  $res=$this->request($info['instance_url']."/services/oauth2/revoke?token=".$info['refresh_token'],"get","");  
  $log_str="Access token Revoked on Request";
  }
 
  $info['instance_url']=$this->post('instance_url',$token);
  $info['access_token']=$this->post('access_token',$token);
  $info['client_id']=$client['client_id'];
  $info['_id']=$this->post('id',$token);
  $info['refresh_token']=$this->post('refresh_token',$token);
  $info['issued_at']=time();
  $info['signature']=$this->post('signature',$token);
  $info['sales_token_time']=current_time('timestamp');
  $info['error']=$this->post('error_description',$token);
  $info['api']="api";
  $info["class"]='error';
  if(!empty($info['access_token'])){
  $info["class"]='updated';
  }
  $this->info=$info;
 // $info=$this->validate_api($info);
  $this->update_info( array('data'=> $info) , $id); 
  return $info;
}
  /**
  * Posts data to salesforce, Get New access token on expiration message from salesforce
  * @param  string $path salesforce path 
  * @param  string $method CURL method 
  * @param  array $body (optional) if you want to post data
  * @return array Salesforce Response array
  */
  public  function post_sales_arr($path,$method,$body=""){
  $info=$this->info;    
  $get_token=false; $error=array(array( 'errorCode'=>'2005' , 'message'=>__('No Access to Salesforce API - 2005','gravity-forms-salesforce-crm'))); 
if(!isset($info['instance_url']) || empty($info['instance_url'])){
    return $error;
}
  $url=$info['instance_url'];
  $dev_key=$info['access_token'];
  $head=array(); 
  if(!empty($body) && is_array($body)){ 
  if(isset($body['disable_rules'])){
  $head['Sforce-Auto-Assign']='false'; 
  unset($body['disable_rules']);   
  }
  if($method == 'post'){
  $head['Sforce-Duplicate-Rule-Header'] = 'allowSave=true';    
  }
  $body=json_encode($body);

  }
if(!empty($dev_key)){
  $sales_res=$this->post_sales($dev_key,$url.$path,$method,$body,$head); 
  $sales_response=json_decode($sales_res,true); 
}else{
  $get_token=true;    
}
  if(isset($sales_response[0]['errorCode']) && $sales_response[0]['errorCode'] == "INVALID_SESSION_ID"){ 
  $get_token=true;         
  }

  if($get_token){ 
  ////////////try to get new token
  $token=$this->get_token();     
  if(!empty($token['access_token'])){
  $dev_key=$token['access_token'];     
  $url=$token['instance_url'];
  $sales_res=$this->post_sales($dev_key,$url.$path,$method,$body,$head);
  $sales_response=json_decode($sales_res,true);  
  }else{
      return $error;
  } }
  $this->api_res=$sales_res; 
  return $sales_response;   
  }
  /**
  * Posts data to salesforce
  * @param  string $dev_key Slesforce Access Token 
  * @param  string $path Salesforce Path 
  * @param  string $method CURL method 
  * @param  string $body (optional) if you want to post data 
  * @return string Salesforce Response JSON
  */
  public function post_sales($dev_key,$path,$method,$body="",$head=''){
  
  if($dev_key == 'token'){
  $header=array('content-type'=>'application/x-www-form-urlencoded');   
  }else{
  $header=array("Authorization"=>' Bearer ' . $dev_key,'content-type'=>'application/json');     
  if(!empty($head) && is_array($head)){ $header=array_merge($header,$head);  }
  
  }
  if(is_array($body)&& count($body)>0)
  { $body=http_build_query($body);
  }
  if($method != "get"){
$header['content-length']= !empty($body) ? strlen($body) : 0;
  }   
  $response = wp_remote_post( $path, array(
  'method' => strtoupper($method),
  'timeout' => $this->timeout,
  'headers' => $header,
  'body' => $body
  )
  );
    
  return !is_wp_error($response) && isset($response['body']) ? $response['body'] : "";
  }
  /**
  * Get Salesforce Client Information
  * @param  array $info (optional) Salesforce Client Information Saved in Database
  * @return array Salesforce Client Information
  */
  public function client_info(){
      $info=$this->info;
  $client_id= "3MVG9A2kN3Bn17hv8jZKWJ31Px1IqJczU2PfHT4_qS9Fr61h7m5R4PhRELnDAWu.aa_rbBirpGMRR56AFa4kg";
  $client_secret="7441227697513084813";
  $call_back="https://www.crmperks.com/sf_auth/";
  //custom app
  if(is_array($info)){
      if($this->post('custom_app',$info) == "yes" && $this->post('app_id',$info) !="" && $this->post('app_secret',$info) !="" && $this->post('app_url',$info) !=""){
     $client_id=$this->post('app_id',$info);     
     $client_secret=$this->post('app_secret',$info);     
     $call_back=$this->post('app_url',$info);     
      }
  }
  return array("client_id"=>$client_id,"client_secret"=>$client_secret,"call_back"=>$call_back);
  }
  
  /**
  * Get fields from salesforce
  * @param  string $form_id Form Id
  * @param  array $form (optional) Form Settings 
  * @param  array $request (optional) custom array or $_REQUEST 
  * @return array Salesforce fields
  */
  public function get_crm_fields($object,$is_options=false){ 

$sales_response=$this->post_sales_arr('/services/data/'.$this->api_version.'/sobjects/'.ucfirst($object)."/describe","get",""); 
//var_dump($sales_response);
  ///seprating fields
  if(isset($sales_response['fields']) && is_array($sales_response['fields'])){
      
      if(isset($this->id) && $this->id == 'vxc_sales' && in_array($object,array("Order",'Opportunity','Quote'))){
         $line_object=$object.'LineItem';
          if($object == 'Order'){ $line_object='OrderItem';  }
       $item_res=$this->post_sales_arr('/services/data/'.$this->api_version.'/sobjects/'.$line_object."/describe","get",""); 

       if(!empty($item_res['fields'])){ 
           foreach($item_res['fields'] as $v){
               if(isset($v['name']) && !in_array($v['name'],array('OrderId','PricebookEntryId','Id','Quantity','UnitPrice'))){ //
                 $v['is_item']='1';
                 if(isset($v['defaultedOnCreate'])){
                   $v['defaultedOnCreate']='1';  
                 }
                 if(isset($v['custom'])){
                   $v['custom']=false;  
                 }
                 $v['name']='vxline_'.$v['name'];
                 $v['label']='Line Item - '.$v['label'];
                   $sales_response['fields'][]=$v;  
               }
           }
       }   
      }
  $field_info=array();
  foreach($sales_response['fields'] as $k=>$field){ 
  
        if( (isset($field['createable']) && $field['createable'] ==true) || $field['name'] == 'Id' || (isset($field['custom']) && $field['custom'] ==true) ){
        
          $required=""; 
  if( !empty($field['nameField']) || (!empty($field['createable']) && empty($field['nillable']) && empty($field['defaultedOnCreate']) )  ){ // && !in_array($field['name'],array('Quantity','UnitPrice'))
  $required="true";   
  } 
  $type=$field['type'];
  if($type == 'reference' && !empty($field['referenceTo']) && is_array($field['referenceTo'])){
   $type=reset($field['referenceTo']);   
  }
  $field_arr=array('name'=>$field['name'],"type"=>$type);
  $field_arr['label']=$field['label']; 
  $field_arr['req']=$required;
  $field_arr["maxlength"]=$field['length'];
  $field_arr["custom"]=$field['custom']; 
  $field_name=$field['name'];
  if(isset($field['is_item'])){   
  $field_arr["is_item"]='1';  
  }
         if(isset($field['picklistValues']) && is_array($field['picklistValues']) && count($field['picklistValues'])>0){
         $field_arr['options']=$field['picklistValues'];
             $egs=array();
         foreach($field['picklistValues'] as $op){
         $egs[]=$op['value'].'='.$op['label'];    
         }
            $field_arr['eg']=implode(', ',array_slice($egs,0,30));
          }
    
          
      if($is_options ){
          if(!empty($field_arr['options'])){
       $field_info[$field_name]=$field_arr;
          } 
      }else{
  
  $field_info[$field_name]=$field_arr;  
  } }
      
  } 
  if(isset($field_info['Id'])){
     $id=$field_info['Id'];
     unset($field_info['Id']);
   $field_info['Id']=$id;   
  }
   if(in_array($object,array("Lead",'Contact'))){
  $field_info['vx_camp_id']=array('name'=>'vx_camp_id',"type"=>'string','label'=>'Campaign Id','custom'=>true);
  }
  $field_info['vx_list_files']=array('name'=>'vx_list_files',"type"=>'files','label'=>'Files - Related List','custom'=>true);
  $field_info['vx_list_files2']=array('name'=>'vx_list_files2',"type"=>'files','label'=>'Files 2 - Related List','custom'=>true);
  $field_info['vx_list_files3']=array('name'=>'vx_list_files3',"type"=>'files','label'=>'Files 3 - Related List','custom'=>true);
  $field_info['vx_list_files4']=array('name'=>'vx_list_files4',"type"=>'files','label'=>'Files 4 - Related List','custom'=>true);
  $field_info['vx_list_files5']=array('name'=>'vx_list_files5',"type"=>'files','label'=>'Files 5 - Related List','custom'=>true);
  $field_info['vx_list_files6']=array('name'=>'vx_list_files6',"type"=>'files','label'=>'Files 6 - Related List','custom'=>true);
  $field_info['vx_list_files7']=array('name'=>'vx_list_files7',"type"=>'files','label'=>'Files 7 - Related List','custom'=>true);
  $field_info['vx_list_files8']=array('name'=>'vx_list_files8',"type"=>'files','label'=>'Files 8 - Related List','custom'=>true);
  $field_info['vx_list_files9']=array('name'=>'vx_list_files9',"type"=>'files','label'=>'Files 9 - Related List','custom'=>true);
  $field_info['vx_list_files10']=array('name'=>'vx_list_files10',"type"=>'files','label'=>'Files 10 - Related List','custom'=>true);
  if(in_array($object,array("Order",'Opportunity','Quote'))){
  $field_info['vx_ship_total']=array('name'=>'vx_ship_total',"type"=>'number','label'=>'Shipping Total','custom'=>true);
  $field_info['vx_ship_entry']=array('name'=>'vx_ship_entry',"type"=>'text','label'=>'Price Book ID Entry of Shipping Item','custom'=>true);
  }
  return $field_info;
  }
  $msg=__("No Fields Found",'gravity-forms-salesforce-crm');
  if(isset($sales_response[0]['errorCode'])){
  $msg=$sales_response[0]['message'];    
  }
  if(isset($sales_response['error'])){
  $msg=$sales_response['error'];    
  } 

  return $msg;
  }
    
  /**
  * Get campaigns from salesforce
  * @return array Salesforce campaigns
  */
  public function get_campaigns(){ 

$q="SELECT Name,Id FROM Campaign";
  $query='/services/data/'.$this->api_version.'/query?q='.urlencode($q);
  $sales_response=$this->post_sales_arr($query,"get");
  $field_info=__('No Campaigns Found','gravity-forms-salesforce-crm');
  if(!empty($sales_response['records'])){
  $field_info=array();
  foreach($sales_response['records'] as $k=>$field){
  $field_info[$field['Id']]=$field['Name'];     
  }
  }
    if(isset($sales_response[0]['errorCode'])){
   $field_info=$sales_response[0]['message'];   
  }
  return $field_info;
}
  /**
  * Get users from salesforce
  * @return array Salesforce users
  */
  public function get_users(){ 
       $q='SELECT email , name , id from User ORDER BY name';
  $sales_response=$this->post_sales_arr('/services/data/'.$this->api_version.'/query?q='.urlencode($q) ,"get","");
  ///seprating fields
  $field_info=__('No Users Found','gravity-forms-salesforce-crm');
  if(isset($sales_response['records']) && is_array($sales_response['records'])){
  $field_info=array();
  foreach($sales_response['records'] as $k=>$field){
  $field_info[$field['Id']]=$field['Name'].' ( '.$field['Email'].' )';     
  }
  $q="SELECT Id,Name FROM GROUP WHERE TYPE='Queue'";
$query='/services/data/'.$this->api_version.'/query?q='.urlencode($q);
$sales_response=$this->post_sales_arr($query,"get");
  if(isset($sales_response['records']) && is_array($sales_response['records'])){
  foreach($sales_response['records'] as $k=>$field){
  $field_info[$field['Id']]=$field['Name'].' (Queue)';     
  }
  }
  }
    if(isset($sales_response[0]['errorCode'])){
   $field_info=$sales_response[0]['message'];   
  } 
  return $field_info;
}
  /**
  * Get users from salesforce
  * @return array Salesforce users
  */
  public function get_price_books(){ 
$q= "SELECT Id,Name,Description,IsStandard from Pricebook2 Limit 3000";
  $sales_response=$this->post_sales_arr('/services/data/'.$this->api_version.'/query?q='.urlencode($q) ,"get","");
  //var_dump($sales_response['records']); die();
  ///seprating fields
  $field_info=__('No Price Book Found','gravity-forms-salesforce-crm');
  if(isset($sales_response['records']) && is_array($sales_response['records'])){
  $field_info=$sd=array();
  foreach($sales_response['records'] as $k=>$field){
if($field['IsStandard']){
    $sd[$field['Id']]=$field['Name'];  
}else{
  $field_info[$field['Id']]=$field['Name'];
}     
  }
$field_info=array_merge($sd,$field_info);  
  }
    if(isset($sales_response[0]['errorCode'])){
   $field_info=$sales_response[0]['message'];   
  } 
  return $field_info;
}
/**
* campaign member status list
* 
*/
  public function get_member_status(){ 
  $sales_response=$this->post_sales_arr('/services/data/'.$this->api_version.'/sobjects/CampaignMember/describe',"get","");
  $field_info=__('Status List Not Found','gravity-forms-salesforce-crm');
  if(isset($sales_response['fields']) && is_array($sales_response['fields'])){
  $field_info=array();
  foreach($sales_response['fields'] as $field){
      if(isset($field['name']) && $field['name'] == "Status" && isset($field['picklistValues']) && is_array($field['picklistValues'])){
       foreach($field['picklistValues'] as $k=>$v){
       if(isset($v['value'])){ 
         $field_info[$v['value']]=$v['label'];  
       }     
       }
       break;  
      }  
  }
  }
    if(isset($sales_response['errorCode'])){
   $field_info=$sales_response['message'];   
  }
  return $field_info;
}
  /**
  * Get Objects from salesforce
  * @return array
  */
  public function get_crm_objects(){

  $sales_res=$this->post_sales_arr('/services/data/'.$this->api_version.'/sobjects/',"get","");

  $fields=array();
  if(isset($sales_res['sobjects'])){
  foreach($sales_res['sobjects'] as $object){
  if($object['createable'] == true && $object['layoutable'] == true){
  $fields[$object['name']]=$object['label'];  
  }    
  }
  return $fields;
  }
  $msg="No Objects Found";
  if(isset($sales_res[0]['errorCode'])){
  $msg=$sales_res[0]['message'];    
  }
  return $msg;
  }
  /**
  * Send data to Salesforce using wp_remote_post()
  *
  * @filter gf_salesforce_salesforce_debug_email Disable debug emails (even if you have debugging enabled) by returning false.
  * @filter gf_salesforce_salesforce_debug_email_address Modify the email address Salesforce sends debug information to
  * @param  array  $post  Data to send to Salesforce
  * @param  boolean $test Is this just testing the OID configuration and not actually sendinghelpful data?
  * @return array|false         If the Salesforce server returns a non-standard code, an empty array is returned. If there is an error, `false` is returned. Otherwise, the `wp_remote_request` results array is returned.
  */
public function post_web($post,$info,$object='Lead', $test = false) {
  global $wp_version;
  // Web-to-Lead uses `oid` and Web to Case uses `orgid`
  switch($object) {
  case 'Case':
  $post['orgid'] = $this->post('org_id',$info);
  break;
  case 'Lead':
  $post['oid'] =$this->post('org_id',$info);
  break;
  }
//var_dump($post); die();
  // We need an Org ID to post to Salesforce successfully.
  if(empty($post['oid']) && empty($post['orgid'])) {
  
  return NULL;
  }
$header=array(
  'user-agent' => 'Woocommerce Salesforce Plugin plugin - WordPress/'.$wp_version.'; '.get_bloginfo('url')
  );
//in web2lead first name and lasty name and email should be unique  
// $header['Content-Type']='application/x-www-form-urlencoded'; 
//var_dump($post); die('--------');

if(!empty($post) && is_array($post)){
$files=$body=array();
foreach($post as $k=>$v){
    if(is_array($v)){
        foreach($v as $vv){
     $body[]=urlencode($k).'='.urlencode($vv);       
        }
    }else{
  $body[]=urlencode($k).'='.urlencode($v);       
    }
}

$post=implode('&',$body);
}

//$post=http_build_query($post);
  // Set SSL verify to false because of server issues.
  $args = array(
  'body'      => $post,
  'headers'   => $header,
  'timeout' => $this->timeout,
 // 'sslverify' => false
  );
  
  // Use test/www subdomain based on whether this is a test or live
  $sub =$test ? 'test' : 'webto' ;
  $url='https://'.$sub.'.salesforce.com/';
  $org_url=$this->post('org_url',$info);
  if(!empty($org_url)){
      $url=trailingslashit($org_url);
  }
  // Use (test|www) subdomain and WebTo(Lead|Case) based on setting
  $url =$url.sprintf('servlet/servlet.WebTo%s?encoding=UTF-8', $object);
  // POST the data to Salesforce
  $result = wp_remote_post($url, $args);

///var_dump($result,$url,$post); die();
  // There was an error
  if(is_wp_error( $result )) {
 // return NULL;
  }
  $done=array('entry created'=>'TRUE');
  // Find out what the response code is
  $code = wp_remote_retrieve_response_code( $result );
  // Salesforce should ALWAYS return 200, even if there's an error.
  // Otherwise, their server may be down.
  if( intval( $code ) !== 200) {
  return NULL;
  }
  // If `is-processed` isn't set, then there's no error.
  elseif(!isset($result['headers']['is-processed'])) {
  return $done;
  }
  // If `is-processed` is "true", then there's no error.
  else if ($result['headers']['is-processed'] === "true") {
  return $done;
  }
  // But if there's the word "Exception", there's an error.
  /*  else if(strpos($result['headers']['is-processed'], 'Exception')) {
  return NULL;
  }*/
  return NULL;
  }
  
public function verify_files($files,$old=array()){
        if(!is_array($files)){
        $files_temp=json_decode($files,true);
     if(is_array($files_temp)){
    $files=$files_temp;     
     }else if(!empty($files)){ //&& filter_var($files,FILTER_VALIDATE_URL)
      $files=array_map('trim',explode(',',$files));   
     }else{
      $files=array();    
     }   
    }
    if(is_array($files) && is_array($old) && !empty($old)){
   $files=array_merge($old,$files);     
    }
  return $files;  
}  
     /**
  * Posts object to salesforce, Creates/Updates Object or add to object feed
  * @param  array $entry_id Needed to update salesforce response
  * @return array Salesforce Response and Object URL
  */
public function push_object($object,$temp_fields,$meta){  

    //$pdf  = GPDFAPI::get_pdf( 1, '5fba18c9c0304' ); $pdf  = GPDFAPI::get_entry_pdfs( 789 ); var_dump($pdf); die(); 
//$res=$this->get_entry('Lead','00Q0H00001sbljWUAQ');
//$res=$this->post_sales_arr('/services/data/v39.0/sobjects/RecordType/describe','get','');
//var_dump($temp_fields,$meta); die();
  $fields_info=array(); $fields=array(); $extra=array();
  $id=""; $error=""; $action=""; $link=""; $search=$search_response=$status=""; 
  $files=array();
  $debug = isset($_REQUEST['vx_debug']) && current_user_can('manage_options');
  if(is_array($temp_fields)){
  foreach($temp_fields as $k=>$v){
  if($k == 'Id'){
      $id=$v['value']; unset($meta['primary_key']);
  }else{
      $fields[$k]=$v['value'];
  }   
  } } 

    $event=$this->post('event',$meta);
  if(isset($this->info['api']) && $this->info['api'] == "web"){ 
 
  if($this->post('debug_email',$this->info) !=""){
   $fields['debug']="0";   //1 send notice for all including success , 0 sends only failure notices
   $fields['debugEmail']=$this->post('debug_email',$this->info);   
  } 
    //associate lead and campaign
  if($this->post('add_to_camp',$meta) == "1" && in_array($object,array("Lead"))){ 
    $fields['Campaign_ID']=$this->post('web_camp_id',$meta); 
    $fields['member_status']=$this->post('web_mem_status',$meta); 
  } 

  $is_sandbox= !empty($this->info['env']) ? true : false;

  $sales_response=$this->post_web($fields,$this->info,$object,$is_sandbox); 
    
  $status="3"; $action="Added";
  if(empty($sales_response)){ $status=""; $error=sprintf(__('Error While Posting to Salesforce %s'),' (Web2Lead)'); }

  }
  else{
  $fields_info=isset($meta['fields']) && is_array($meta['fields']) ? $meta['fields'] : array();
  if($event!='add_note'){

  //remove related list fields
  $files=array();
  for($i=1; $i<11; $i++){
$field_n='vx_list_files';
if($i>1){ $field_n.=$i; }
  if(isset($fields[$field_n])){
    $files=$this->verify_files($fields[$field_n],$files);
    unset($fields[$field_n]);  
  }
} 
if(!empty($fields_info)){
    foreach($fields_info as $k=>$v){
        if(!empty($v['is_item']) && isset($meta['map'][$k])){
        $meta['item_fields'][$k]=$meta['map'][$k];
        if(isset($fields[$k])){ unset($fields[$k]); }    
        }
    } 
}


$fields=$this->clean_sf_fields($fields,$fields_info);

if(!empty($meta['owner'])){
    $fields['disable_rules']=1;
}  
}
$camp_id='';
if(isset($fields['vx_camp_id'])){
$camp_id=$fields['vx_camp_id'];
unset($fields['vx_camp_id']);   
}

  if($debug){ ob_start();}
  //check primary key
  $search=array(); $search2=array();
  if( !empty($meta['primary_key']) || !empty($meta['primary_key_custom'])){    

  if(!empty($meta['primary_key_custom'])){
      $meta['primary_key']=$meta['primary_key_custom'];
  }
  $search=$this->get_search_val($meta['primary_key'],$fields,$fields_info); 
  //var_dump($search); die();
  $search=apply_filters('crm_perks_salesforce_search',$search,$fields);
  if( !empty($meta['primary_key2']) ){
  $search2=$this->get_search_val($meta['primary_key2'],$fields,$fields_info);
  } 
  if(!empty($search) || !empty($search2)){
    //  $search=array('FirstName'=>esc_sql("+~'john@"));
    // $search=array('Phone'=>esc_sql("(810) 476-3056"));
    
  //if primary key option is not empty and primary key field value is not empty , then check search object
  $search_response=$sales_response=$this->search_in_sf($object,$search,$search2); 
 //var_dump($search_response,$search,$search2); die();
  if($debug){
  ?>
  <pre>
  <h3>Search field</h3>
  <p><?php print_r($search) ?></p>
  <h3>Search term</h3>
  <p><?php print_r($search2) ?></p>
  <h3>Search response</h3>
  <p><?php print_r($sales_response) ?></p>
  </pre>    
  <?php
  }
     
      $extra["Search"]=$search;
      if(!empty($search2)){
      $extra["Search2"]=$search2;
      }
      $extra["response"]=!empty($search_response) ? $search_response : $this->api_res;
  
  if(isset($sales_response[0]['Id'])&& $sales_response[0]['Id']!=""){
   if($object == 'Lead'){
       foreach($sales_response as $v){
           $is_con=isset($v['IsConverted']) && $v['IsConverted'] == true ? true: false;
         if( ! $is_con ){
             $id=$v['Id']; break;
         }
       }
   }else{
  if(is_array($search_response) && count($search_response)>10){
       $search_response=array_slice($search_response,0,10);   //count($search_response)-10 
      }   
  //object found, update old object or add to feed
  $id=$sales_response[0]['Id'];  //count($sales_response)-1
  $extra["response"]=$search_response;
  }}
  
  if(isset($sales_response[0]['errorCode'])){
  $error=$sales_response[0]['message'];
  }
  }
  $sales_response='';
  }
  if(!empty($meta['crm_id'])){
   $id=$meta['crm_id'];   
  } 
     if(in_array($event,array('delete_note','add_note'))){    
  if(isset($meta['related_object'])){
    $extra['Note Object']= $meta['related_object'];
  }
  if(isset($meta['note_object_link'])){
    $extra['note_object_link']=$meta['note_object_link'];
  }
}
 $entry_exists=$sent=false;
//$fields['AccountId']='0016A00000hzWNGQA2';
//$fields['OpportunityId']='0066A00000CH9Q6QAL';
if(!empty($fields['vx_ship_entry'])){
   $meta['vx_ship_entry']=$fields['vx_ship_entry'];
   $meta['vx_ship_total']=$fields['vx_ship_total'];
   unset($fields['vx_ship_entry']);
   unset($fields['vx_ship_total']);

}


$line_items=array();
if(isset($this->id) && $this->id == 'vxc_sales' && !empty($meta['order_items']) && (in_array($object,array('Opportunity','Quote')) || (!empty($id) && $object=='Order'))){
$items=$this->get_items($meta); 
if(!empty($items['price_book'])){
$fields['Pricebook2Id']=$items['price_book'];
if(!empty($items['items'])){
$line_items=$items['items'];
}
}
if(!empty($items['extra'])){
    $extra=array_merge($items['extra'],$extra);
}        
}
//var_dump($line_items,$items); die();
$sales_response='';
  $post_data=json_encode($fields);
  //if($error ==""){
  if($id == ""){
  $action="Added";
if(empty($meta['new_entry'])){
    $sent=true;
if( isset($this->id) && $this->id == 'vxc_sales' &&  $object == "Order"){
   $order_res=$this->add_order($fields,$meta);   
  $sales_response=$order_res['res'];
  if(is_array($order_res['extra'])){
  $extra=array_merge($extra, $order_res['extra']);
  }   
}else{  
  //create new lead
$sales_response=$this->post_sales_arr('/services/data/'.$this->api_version.'/sobjects/'.$object,"post",$fields);
  }
  if(isset($sales_response['id'])){
  $id=$sales_response['id'];
  $status="1";
  } }else{ $status="6";
      $error='Record not found in CRM';
  }
}else{ 
$entry_exists=true;
  if($event == 'add_note'){     
  $sales_response=$this->post_note($fields,$meta);

    if(isset($sales_response['id'])){
  $id=$sales_response['id'];
  $status="1";
    }  
  }
  else if(in_array($event,array('delete','delete_note'))){
     
  $action="Deleted";
  $sales_response=$this->post_sales_arr('/services/data/'.$this->api_version.'/sobjects/'.$object."/".$id,"DELETE");
    if(empty($sales_response)){ $status="5"; } 
}else{     
  $action="Updated";
  //update old object
   if(empty($meta['update'])){
         if($object == 'CampaignMember'){
       unset($fields['ContactId']);
       unset($fields['CampaignId']);
         } if($object == 'PricebookEntry'){
       unset($fields['Product2Id']);
       unset($fields['Pricebook2Id']);
         }
      //   $fields['Custom_time_type__c']='12:00+00';
        
  $sales_response=$this->post_sales_arr('/services/data/'.$this->api_version.'/sobjects/'.$object."/".$id,"PATCH",$fields);

   if(empty($sales_response)){ $status="2";  $sent=true; } 
 }else{
   $status="2";  
 }
  }
  }
$json='[{"quantity":3,"PricebookEntryId":"01u3s000007wSaSAAU","UnitPrice":265.2,"custom_text__c":"xxxx"},{"quantity":4,"PricebookEntryId":"01u3s000007wSaSAAU","UnitPrice":265.2,"custom_text__c":"xxxx"},{"quantity":1,"PricebookEntryId":"01u3s000007wSaIAAU","UnitPrice":200,"custom_text__c":"xxxx"}]';
//$line_items=json_decode($json,1);  
//echo json_encode($line_items); var_dump($extra); die();
if(!empty($line_items)){
 $k=1; $old_lines=$old_keys=array();
 $line_object=$object;
 if($object != 'Order'){ $line_object.='Line'; }
 if($status == '2'){
 $q='SELECT ID, '.$object.'Id,PricebookEntryId,Quantity,UnitPrice from '.$line_object.'Item where '.$object."Id='".$id."'";
 $path='/services/data/'.$this->api_version.'/query?q='.urlencode($q);
 $res=$this->post_sales_arr($path,'GET'); 
 $extra['Old Items']=$res;  
 if(!empty($res['records'])){
  foreach($res['records'] as $kk=>$vv){
  $old_lines[$vv['Id']]=$vv;   
  $old_keys[$vv['Id']]=$vv['PricebookEntryId'];   
  }   
 }   
 }

    foreach($line_items as $item_id=>$item){
       // unset($item['PricebookEntryId']);
       // unset($item['Product2Id']);  {"quantity":7,"UnitPrice":"27.00"}
       if(empty($item['quantity'])){continue; }
       $old_k=array_search($item['PricebookEntryId'],$old_keys); //var_dump($old_k);
       if(!empty($item['PricebookEntryId']) && $old_k !== false ){
         $old_item=$old_lines[$old_k]; unset($old_lines[$old_k]);unset($old_keys[$old_k]); //var_dump($old_item);
        // if($old_item['quantity'] != $item['quantity']){    
        $item_patch=array('quantity'=>$item['quantity'],'UnitPrice'=>$item['UnitPrice']);     
 $item_res=$this->post_sales_arr('/services/data/'.$this->api_version.'/sobjects/'.$line_object."Item/".$old_item['Id'],"PATCH",$item_patch);  
 $extra['Item Patch '.$old_item['Id']]=$item_patch;  
 $extra['Item Response '.$old_item['Id']]=$item_res;    
       //  }   
       }else{
        $item[$object.'id']=$id;
  $item_res=$this->post_sales_arr('/services/data/'.$this->api_version.'/sobjects/'.$line_object."Item","POST",$item);  
 $extra['Item Post '.$k]=$item;  
 $extra['Item Response '.$k]=$item_res;
       }  
 $k++;
    }
     foreach($old_lines as $item){
       $extra['Item Del '.$item['Id']]=$this->post_sales_arr('/services/data/'.$this->api_version.'/sobjects/'.$line_object."Item/".$item['Id'],"DELETE");
     }
  $fields['lines']=$line_items;  
} //var_dump($extra); die();
if(!empty($id)){
    if(is_array($files) ){
        foreach($files as $k=>$file){ $k++;
        $filer=rtrim($file,'/'); 
         $file_name=substr($filer,strrpos($filer,'/')+1);  
            if(strpos($file,'/pdf/') !== false){
    $file_name.='.pdf';    
    }     
    $post=array('Title'=>$file_name); 
         if( filter_var($file, FILTER_VALIDATE_URL) && strpos($file,'/gravity_forms/') !== false) { //!ini_get('allow_url_fopen')
      $upload_dir=wp_upload_dir();
       $file=str_replace($upload_dir['baseurl'],$upload_dir['basedir'],$file); 
    }
  $c=file_get_contents($file);
  
  $post['VersionData']=base64_encode($c);
  $post['PathOnClient']=$file_name;
  $extra['Uploading File '.$k]=$file;
  $post=json_encode($post);
  $file_res=$this->post_sales_arr('/services/data/'.$this->api_version.'/sobjects/ContentVersion','post',$post);
  $extra['Uploaded File '.$k]=$file_res;
  if(!empty($file_res['id'])){ 
    $file_res=$this->post_sales_arr('/services/data/'.$this->api_version.'/sobjects/ContentVersion/'.$file_res['id'],'get','');
    if(!empty($file_res['ContentDocumentId'])){
       $post=array('ContentDocumentId'=>$file_res['ContentDocumentId'],'LinkedEntityId'=>$id,'ShareType'=>'V','Visibility'=>'AllUsers');  
       $post=json_encode($post);
    $link_res=$this->post_sales_arr('/services/data/'.$this->api_version.'/sobjects/ContentDocumentLink','post',$post);
    $extra['Linked File '.$k]=$link_res;
    }  
  }
        }
    }
}
if($this->post('add_to_camp',$meta) == "1"){
    $camp_id=$this->post('campaign',$meta);   
    if($this->post('camp_type',$meta) != ""){
    $camp_id=$this->post('campaign_id',$meta);    
    }   
}
  //associate lead and campaign
  if( !empty($camp_id) && $id !="" && in_array($object,array("Lead","Contact"))){

  $camp_post=array($object."Id"=>$id,"CampaignId"=>$camp_id,"Status"=>$this->post('member_status',$meta));  
  $extra['camp_post']=$camp_post;
  $camp_post=json_encode($camp_post);
$camp_res=$this->post_sales_arr('/services/data/'.$this->api_version.'/sobjects/CampaignMember',"post",$camp_post); 
if(isset($camp_res[0]['errorCode']) && $camp_res[0]['errorCode'] == 'DUPLICATE_VALUE'){
   $camp_search=array($object."Id"=>$id,"CampaignId"=>$camp_id);
    $camp_search_res=$this->search_in_sf('CampaignMember',$camp_search);
    $extra['camp_search']=$camp_search_res;
    if(!empty($camp_search_res[0]['Id'])){
        $camp_status=isset($camp_search_res[0]['Status']) ? $camp_search_res[0]['Status'] : '';
        $camp_post=array("Status"=>$this->post('member_status',$meta));
        $extra['camp_status']=$camp_post; 
        if($camp_status != $camp_post['Status']){
  $camp_post=json_encode($camp_post);
$camp_update=$this->post_sales_arr('/services/data/'.$this->api_version.'/sobjects/CampaignMember/'.$camp_search_res[0]['Id'],"PATCH",$camp_post);  
$extra['camp_update']=$camp_update;     
    } }
}
$extra['camp_post']=$camp_post; 
$extra['camp_res']=$camp_res; 
  }
  }
  if($id !="")
  {
  $link=$this->info['instance_url']."/".$id;
  }
  if(isset($sales_response[0]['errorCode'])){
  $error=$sales_response[0]['message'];
  $sales_response=$sales_response[0]; $id='';
  }
  if($debug){
  ?>
  <pre>
  <h3>Salesforce Information</h3>
  <p><?php print_r($this->info) ?></p>
  <h3>Data Sent</h3>
  <p><?php echo json_encode($fields) ?></p>
  <h3>Salesforce response</h3>
  <p><?php print_r($sales_response) ?></p>
  <h3>Object</h3>
  <p><?php print_r($object."--------".$action) ?></p>
  </pre>    
  <?php
  $contents=trim(ob_get_clean());
  if($contents!=""){
  update_option($this->id."_debug",$contents);   
  }
  }
  
         //add entry note
 if(!empty($status) && !empty($meta['__vx_entry_note']) && !empty($id)){
 $disable_note=$this->post('disable_entry_note',$meta); 
   if(!($entry_exists && !empty($disable_note))){  
       $entry_note=$meta['__vx_entry_note'];
 $note_temp=array('Title'=>$entry_note['Title'],'Body'=>$entry_note['Body'],'ParentId'=>$id); 
  $note_response=$this->post_note($note_temp,$meta);

  $extra['Note Title']=$entry_note['Title'];
  $extra['Note Body']=$entry_note['Body'];
  $extra['Note Response']=$note_response;
 
   }  
 }


  return array("error"=>$error,"id"=>$id,"link"=>$link,"action"=>$action,"status"=>$status,"data"=>$fields,"response"=>$sales_response,"extra"=>$extra);
  }

public function get_search_val($field,$fields,$fields_info){
   $search=array(); 
   if(strpos($field,'Product2Id+') !== false ){
      if(!empty($fields['Product2Id'])){
          $search['Product2Id']=  $fields['Product2Id'];
      }
      if(!empty($fields['Pricebook2Id'])){
          $search['Pricebook2Id']= $fields['Pricebook2Id'];
      }
     
  }else if(strpos($field,'+') !== false){
      $search_arr=array_map('trim',explode('+',$field)); 
      foreach($search_arr as $field){
       if(isset($fields[$field])){
      $search[$field]= $fields[$field];     
       }   
      } 
  }else if(isset($fields[$field]) && $fields[$field] !=''){
      $val=$fields[$field]; 
     $search=array( $field=>$val ); 
  }
  foreach($search as $field=>$val){
         if(isset($fields_info[$field]['type'])){
          $type=$fields_info[$field]['type'];
          if( $type == 'phone'){
        // $val=preg_replace( '/[^0-9]/', '', $val );
          }else if( in_array($type,array('date','datetime'))){
              $search[$field]=array('val'=>$val,'type'=>$type);
          }
      }
  } 
return $search;   
}  
public function post_note($post,$meta,$id=''){
$note_object=!empty($meta['note_list']) ? 'ContentNote' : 'Note'; 
 $object='';
 if(!empty($meta['object'])){
  $object= $meta['object'];  
 }
 if($object == 'Case'){
$note_object='CaseComment';
if(isset($post['Title'])){
   unset($post['Title']); 
}
if(isset($post['Body'])){
  $post['CommentBody']=$post['Body'];
  unset($post['Body']); 
}
$meta['note_list']='';
}else{
     if(!empty($meta['note_list'])){
     $note_body=!empty($post['Body']) ? $post['Body'] : '';
      $note_body_arr=explode("\n",$note_body);
      $note_body='<p>'.implode('</p><p>',$note_body_arr).'</p>';   
     $post['Content']=base64_encode($note_body);
     unset($post['Body']);
      if(!empty($post['ParentId'])){
  $id=$post['ParentId'];
  unset($post['ParentId']);   
 }
     } 
 }
 
$post_data=json_encode($post);  
 $sales_response=$this->post_sales_arr('/services/data/'.$this->api_version.'/sobjects/'.$note_object,"POST",$post_data);
    if(isset($sales_response['id']) && !empty($meta['note_list']) && !empty($id)){
$arr=array('ContentDocumentId'=>$sales_response['id'],'LinkedEntityId'=>$id,'ShareType'=>'V','Visibility'=>'AllUsers');        
$post_data=json_encode($arr);
$sales_response['linkedTo']=$this->post_sales_arr('/services/data/'.$this->api_version.'/sobjects/ContentDocumentLink',"POST",$post_data);      
    }
return $sales_response;
}

/**
  * Create Order and Order items
  * 
  * @param mixed $post
  * @param mixed $meta
  */
public function add_order($post, $meta){ 

    if( !empty($meta['order_items'])){
     $items=$this->get_items($meta);
     $order_items=!empty($items['items']) ? $items['items'] : array();

     
    $sales_response=array();  $extra=array();

    
if(!empty($items['extra'])){
    $extra=$items['extra'];
}

    if(is_array($order_items) && count($order_items)>0 && !empty($items['price_book'])){


     $post['Pricebook2Id']=$items['price_book'];
     $order_items=array_map(function($v){$v['attributes']=array('type'=>'OrderItem'); unset($v['Product2Id']); return $v;},$order_items);
     $post['OrderItems']=array('records'=>$order_items);
     $extra['Order Items']=$order_items;
     }
    }     
     if(!(is_array($sales_response) && isset($sales_response[0]['errorCode']) ) ){ 
      $path='/services/data/'.$this->api_version.'/commerce/sale/order/';
               //create order
     $att_order=array('attributes'=>array('type'=>'Order'));
     $post=is_array($post) ? $post : array();
     $post=array_merge($att_order,$post);
         if(empty($post['Status'])){
       $post['Status']='Draft';  
     }
$disable=false;
     if(isset($post['disable_rules'])){
         $disable=true;
  unset($post['disable_rules']);   
  }
      $post_json=array("order"=>array($post));   
 if($disable){
  $post_json['disable_rules']='1';   
 }
       $sales_response=$this->post_sales_arr($path,'POST',$post_json);

       if(isset($sales_response['records'][0]) && is_array($sales_response['records'][0]) && isset($sales_response['records'][0]['Id'])){
           $sales_response=array("id"=>$sales_response['records'][0]['Id']);
       }
     }

       return array('res'=>$sales_response,'extra'=>$extra);
  }
public function get_items($meta){
    $items=$this->get_wc_items($meta); 
    $order_items=array(); $extra=array();  
    $price_book=''; $k=0;
    if(!empty($items)){
        foreach($items as $item_id=>$item){ 
            $sku=$item['sku'];
            $price_book_id="";
       $k++;
      $path='/services/data/'.$this->api_version.'/query';
    $q="SELECT Id,UnitPrice,ProductCode,Pricebook2Id,Product2Id from PricebookEntry where ProductCode='".$sku."' ";
    if(!empty($meta['price_book'])){
        $price_book=$meta['price_book'];
    $q.="and Pricebook2Id='".$meta['price_book']."'";
    }  if(!empty($meta['map']['CurrencyIsoCode']['value'])){
    $q.=" and CurrencyIsoCode='".$meta['map']['CurrencyIsoCode']['value']."'";
    }
    $q.="  order by Id DESC Limit 1"; 
  $path.='?q='.urlencode($q);
    $sales_response=$this->post_sales_arr($path,'GET');   
        $extra['Search Product '.$k]=array('ProductCode'=>$sku,'Pricebook2Id'=>$price_book);
        $extra['Search Result '.$k]=$sales_response;
     if(isset($sales_response['records']) && is_array($sales_response['records']) && isset($sales_response['records'][0])){
      $price_book_id=$sales_response['records'][0]['Id'];   
      $price_book=$sales_response['records'][0]['Pricebook2Id'];   
      $product_id=$sales_response['records'][0]['Product2Id'];   
     }else{
     $res=$this->search_in_sf('Product2',array('ProductCode'=>$sku) );
  //   var_dump($res);
       $product_id=''; 
       if(!empty($res[0]['Id'])){
       $product_id=$res[0]['Id'];    
       }else{
       //create product in sf
             $path='/services/data/'.$this->api_version.'/sobjects/Product2';
         $sf_pro=array('IsActive'=>true,'ProductCode'=>$sku,'Name'=>$item['name']);
         if(!empty($meta['pro_desc'])){
             $sf_pro['Description']=$meta['pro_desc'];
         }
       $sf_pro_json=json_encode($sf_pro);
       $sales_response=$this->post_sales_arr($path,'POST',$sf_pro_json);
       $product_id=$sales_response['id'];
       $extra['Create Product '.$k]=$sf_pro;
       $extra['Product Result '.$k]=$sales_response; 
   if(!empty($product_id) && !empty($meta['standard_book']) && $meta['standard_book']!=$price_book){
        $path='/services/data/'.$this->api_version.'/sobjects/PricebookEntry'; 
       $sf_entry=array('IsActive'=>true,'Product2Id'=>$product_id,'Pricebook2Id'=>$meta['standard_book'],'UnitPrice'=>$item['unit_price']);
         $sf_entry_json=json_encode($sf_entry);
       $sales_response=$this->post_sales_arr($path,'POST',$sf_entry_json);
       $extra['Add StandardBook '.$k]=$sf_entry;
       $extra['StandardBook Redult '.$k]=$sales_response; 
      // if($meta['standard_book'] == $price_book){ $price_book=''; }
    }
} 
       if(!empty($product_id) && !empty($price_book) ){
        //add to price book
        $path='/services/data/'.$this->api_version.'/sobjects/PricebookEntry'; 
       $sf_entry=array('IsActive'=>true,'Product2Id'=>$product_id,'Pricebook2Id'=>$price_book,'UnitPrice'=>$item['unit_price']);
         $sf_entry_json=json_encode($sf_entry);
       $sales_response=$this->post_sales_arr($path,'POST',$sf_entry_json);
       $extra['Add PriceBook '.$k]=$sf_entry;
       $extra['PriceBook Redult '.$k]=$sales_response;  
       if(is_array($sales_response) && isset($sales_response['id'])){
           $price_book_id=$sales_response['id'];
       }  
       }    
     }
  //var_dump($sales_response,$q); die();  die('-------------');  
        if(!empty($price_book_id)){
         //add as order item
       $order_item=array('quantity'=>$item['qty'],'PricebookEntryId'=>$price_book_id,'UnitPrice'=>$item['cost']); //,'Product2Id'=>$product_id
       
           if(!empty($meta['item_price']) ){
          if($meta['item_price'] == 'cost'){
       $order_item['UnitPrice']=floatval($item['cost_woo']); 
      }else if($meta['item_price'] == 'cost_tax'){
       $order_item['UnitPrice']=floatval($item['cost'])+floatval($item['tax']); 
      }
     }
     
    if(!empty($item['fields']) && is_array($meta['fields'])){
        $item['fields']=$this->clean_sf_fields($item['fields'],$meta['fields']);
        foreach($item['fields'] as $k=>$v){
            $order_item[substr($k,7)]=$v;
        }
    }

       $order_items[]=$order_item; 
        } 
              
        }
       if(!empty($meta['vx_ship_entry'])){ 
           $order_items[]=array('quantity'=>1,'PricebookEntryId'=>$meta['vx_ship_entry'],'UnitPrice'=>$meta['vx_ship_total']);
       } 
    }
    
  return array('items'=>$order_items,'price_book'=>$price_book,'extra'=>$extra);   
}
public function get_wc_items($meta){

      $_order=self::$_order;
    //  $fees=$_order->get_shipping_total();
    //  $fees=$_order-> get_total_discount();
    //  $fees=$_order-> get_total_tax();
     $items=$_order->get_items();  
     $products=array();  $order_items=array(); 
if(is_array($items) && count($items)>0 ){
foreach($items as $item_id=>$item){
$sku=$desc=$name=''; $qty=$unit_price=$tax=$total=$cost=$cost_woo=$p_id=$var_id=0;
if(method_exists($item,'get_product')){
  // $p_id=$v->get_product_id();  
   $product=$item->get_product();
   if(!$product){ continue; } //product deleted but exists in line items of old order

   $total=(int)$item->get_total();
    if(method_exists($_order,'get_item_total')){
  $cost=$_order->get_item_total($item,false,true); //including woo coupon discuont
  $cost_woo=$_order->get_item_subtotal($item, false, true); // does not include coupon discounts
    }
   $qty = $item->get_quantity();
   $tax = $item->get_total_tax();
  if(!empty($tax) && !empty($qty)){
       $tax=floatval($tax)/$qty;
   }
   $desc=$product->get_short_description();
   $title=$product->get_title();
   $sku=$product->get_sku();     
   $unit_price=$product->get_price(); 
   $p_id=$product->get_parent_id();
   $var_id=$product->get_id(); 
   if(empty($p_id)){
       $p_id=$var_id;
   }else{
           $product_simple=wc_get_product($p_id);
           if($product_simple){
         $parent_sku=$product_simple->get_sku(); 
         if($parent_sku == $sku){
            // $sku.='-'.$var_id;
         }

              // append variation names ,  $item->get_name() does not support more than 3 variation names
          $attrs=$product_simple->get_attributes(); //$item->get_formatted_meta_data( '' )
            $var_info=array(); 
             if(is_array($attrs) && count($attrs)>0){
                 foreach($attrs as $attr_key=>$attr_val){
                     if(!is_object($attr_val)){
                    // $att_name=wc_attribute_label($attr_key,$product);
                     $term = get_term_by( 'slug', $attr_val, $attr_key );
                 if ( taxonomy_exists( $attr_key ) ) {
                $term = get_term_by( 'slug', $attr_val, $attr_key );
                if ( ! is_wp_error( $term ) && is_object( $term ) && $term->name ) {
                    $attr_val = $term->name;
                }    
            }
            if(!empty($attr_val)){
            $var_info[]=$attr_val;
            }    
                 } }
             }

          if(!empty($var_info)){
          $title.=' '.implode(', ',$var_info);    
          } 
           }
   }
   $name=$item->get_name();
 //  $p_id=$product->get_id();
   if(empty($total)){ $unit_price=0; } 
          
}else{ //version_compare( WC_VERSION, '3.0.0', '<' )  , is_array($item) both work
   $p_id=$var_id= !empty($item['variation_id']) ? $item['variation_id'] : $item['product_id'];
        $line_desc=array();
        if(!isset($products[$p_id])){
            try{
        $product=new WC_Product($p_id);
            }catch(Exception $e){
               // echo $e->getMessage();
            }
        }else{
         $product=$products[$p_id];   
        }
       if(!$product){ continue; }  
        $qty=$item['qty'];
        $products[$p_id]=$product;
        $sku=$product->get_sku(); 
        if(empty($sku) && !empty($item['product_id'])){ 
            //if variable product is empty , get simple product sku
            
            $product_simple=new WC_Product($item['product_id']);
            $sku=$product_simple->get_sku(); 
        }
        $unit_price=$product->get_price();
        $title=$product->get_title();
        $desc=$product->get_short_description();
        $p_id=$item['product_id'];
        $name=$item['name'];
 }
  $temp=array('sku'=>$sku,'unit_price'=>$unit_price,'title'=>$title,'qty'=>$qty,'tax'=>$tax,'total'=>$total,'desc'=>$desc,'p_id'=>$p_id,'var_id'=>$var_id,'name'=>$name,'cost'=>$cost,'cost_woo'=>$cost_woo,'fields'=>array());
  
     if(!empty($meta['item_fields'])){
        foreach($meta['item_fields'] as $k=>$v){
        if(isset($v['type'])){
            if($v['type'] == 'value'){
                $temp['fields'][$k]=$this->process_tags($v['value'],$item);
        }else{
         $temp['fields'][$k]=$this->get_field_val($v,$item);   
        }    }
        }   
       }
          if(method_exists($product,'get_stock_quantity')){
   $temp['stock']=$product->get_stock_quantity();
} 

     $order_items[$item_id]=$temp;     
      }
} 
   return $order_items;       
}
  /**
  * Cleans salesrforce fields
  * formates date and checkboxes
  * @param  array $fixed fields to post
  * @param  array $fields_info fields info
  * @return array Salesforce fields
  */
public function clean_sf_fields($fixed,$fields_info){ 
  $sf_fields=array();
  if(is_array($fixed)){  
foreach($fixed as $field_key=>$field_val){ 
  //convert date to salesforce compatible format
  if(isset($fields_info[$field_key])){
 $type=$fields_info[$field_key]['type'];
 if(in_array($type,array('date','datetime')) && empty($field_val)){
  continue;   
 }
   if(in_array($type, array("datetime",'date') ) ){
     
     $date_val=strtotime(str_replace(array("/"),"-",$field_val));
     if( $type == "date"  ){
        if(strpos($field_val,'+00:00') !== false){
              $offset=get_option('gmt_offset');
     $offset=$offset*3600; 
$date_val+= $offset;  //convert utc datetime to local timezone for getting exatct date
        } 
        
  $field_val=date('Y-m-d',$date_val);  
  }else{ 
    $offset=get_option('gmt_offset');
     $offset=$offset*3600; 
     if(strpos($field_val,'+') === false || strpos($field_val,'-') === false){ // convert to utc if no timezone(+) does not exist with time string
     $date_val-= $offset;   
     }  
  $field_val=date('c',$date_val); 
  }
  }else if($type == "boolean"){
  $field_val=empty($field_val) || in_array($field_val,array('no','No')) ? 0 : 1 ; 
  }else if( in_array($type, array('Currency','currency'))){
  $field_val=filter_var( $field_val, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ); 
  }else if($fields_info[$field_key]['type'] == "multipicklist"){
      if(is_array($field_val)){
       $field_val=html_entity_decode(implode(';',$field_val));   
      }
  }else if(in_array($type,array('string','url')) && !empty($fields_info[$field_key]['maxlength'])){
      $field_len=$fields_info[$field_key]['maxlength'];
      if(is_string($field_val) && strlen($field_val)> $field_len){
        $field_val=trim(substr($field_val,0,$field_len-1));  
      }
  } 
  if(is_array($field_val)){ 
      $field_val=implode(', ',$field_val);
  }
  $sf_fields[$field_key]=$field_val;      
  }   
}
  }
 //var_dump($sf_fields); die();   
  return $sf_fields;    
  }
  /**
  * Formates Salesforce success or error response into message string
  * @param  array $sales_res Slesforce response 
  * @return string formated string
  */
public function search_in_sf($sales_object,$search,$search2=''){
  $sales_response=array(); 
   /*
   $val='(810) 476-63056';
   $field_type='phone';
  if(in_array($field_type,array("email","phone"))){
    //reomve saleforce reserved characters from key value
  $clean_key=""; $key_val=str_split($val);
  foreach($key_val as $v){
  if(in_array($v,array("?","&","|","!","{","}","[","]","(",")","^","~","*",":",'\\','"',"'","+","-")))
  $v='\\'.$v;
  $clean_key.=$v;    
  }
  $q="FIND {".$clean_key."} IN ".strtoupper($field_type)." FIELDS RETURNING ".$sales_object."(Id)"; 
  $query='/services/data/'.$this->api_version.'/search?q='.urlencode($q);
  $sales_response=$this->post_sales_arr($query,"get");
    if(isset($sales_response['searchRecords'])){
  $sales_response=$sales_response['searchRecords'];
  } }
  */

  $where=array();
  if(!empty($search)){
      $where[]=$search; 
  }
    if(!empty($search2)){
        $where[]=$search2;
  }
  if(!empty($where)){
     $where2=array(); 
    foreach($where as $search){
          $temp=array();
      foreach($search as $k=>$v){
          $type='';
          if(is_array($v)){
              $type=$v['type'];
              $v=$v['val'];
          } 
          if(in_array($type,array('date','datetime'))){
         $v=esc_sql($v);
          }else{
          $v="'".esc_sql($v)."'";
          }
          $temp[]=$k." = ".$v; 
      }
    $where2[]=' ( '.implode(' AND ',$temp).' ) '; 
    }  
  $sel='Id';
  if($sales_object == 'Lead'){
      $sel.=' ,IsConverted ';
  }if($sales_object == 'CampaignMember'){
      $sel.=' ,Status ';
  }  
  $q="SELECT $sel FROM $sales_object WHERE ".implode(' OR ',$where2);   
  $query='/services/data/'.$this->api_version.'/query?q='.urlencode($q);
  $sales_response=$this->post_sales_arr($query,"get");
 //var_dump($sales_response,$q,$where2); die('-------------');
  if(isset($sales_response['records'])){
  $sales_response=$sales_response['records'];
  }
  }
  return $sales_response;   
  } 
public function get_entry($object,$id){
  return $this->post_sales_arr('/services/data/'.$this->api_version.'/sobjects/'.$object.'/'.$id,"get");     
  }
public function create_fields_section($fields){
$arr=array(); 
if(!isset($fields['object'])){
        $objects=array(''=>'Select Object');
    $objects_sf=$this->get_crm_objects(); //var_dump($objects,$this->info);
    if(is_array($objects_sf)){
    $objects=array_merge($objects,$objects_sf);
    }
 $arr['gen_sel']['object']=array('label'=>__('Select Object','gravity-forms-salesforce-crm'),'options'=>$objects,'is_ajax'=>true,'req'=>true);   
}else if(isset($fields['fields']) && !empty($fields['object'])){
    // filter fields
    $crm_fields=$this->get_crm_fields($fields['object']); 
    if(!is_array($crm_fields)){
        $crm_fields=array();
    }
    $add_fields=array();
    if(is_array($fields['fields']) && count($fields['fields'])>0){
        foreach($fields['fields'] as $k=>$v){
           $found=false;
                foreach($crm_fields as $crm_key=>$val){
                    if(strpos($crm_key,$k)!== false){
                        $found=true; break;
                }
            }
         //   echo $found.'---------'.$k.'============'.$crm_key.'<hr>';
         if(!$found){
       $add_fields[$k]=$v;      
         }   
        }
    }
 $arr['fields']=$add_fields;   
}

return $arr;  
}
 
 
}
}
?>