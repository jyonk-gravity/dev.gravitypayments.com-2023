<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


if( !class_exists( 'vxg_salesforce_data' ) ) {

/**
* since 1.0
*/
class vxg_salesforce_data extends vxg_salesforce{
/**
* creates or updates tables
* 
*/
  public  function update_table(){
  global $wpdb;
  
  $wpdb->hide_errors();
  $table_name = $this->get_crm_table_name();
  
  require_once(ABSPATH . '/wp-admin/includes/upgrade.php');
    
  if ( ! empty($wpdb->charset) )
  $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
  if ( ! empty($wpdb->collate) )
  $charset_collate .= " COLLATE $wpdb->collate";
  
  $sql = "CREATE TABLE $table_name (
  id int(11) unsigned not null auto_increment,
  form_id mediumint(8)  null,
  account mediumint(8)  null,
  is_active tinyint(1)  null default 1,
  sort mediumint(8)  null default 0,
  name varchar(240)  null,
  object varchar(200)  null,
  meta longtext,
  data longtext,
  `time` datetime null,
  PRIMARY KEY  (id),
  KEY form_id (form_id)
  )$charset_collate; ";
  
   dbDelta($sql);
   
  $table_name = $this->get_crm_table_name('log');
  
  $sql= "CREATE TABLE $table_name (
  id int(11) unsigned not null auto_increment,
  entry_id int(11) null,
  form_id mediumint(8) not null,
    feed_id int(11) not null,
    parent_id int(11) null,
  crm_id varchar(200) null,
  object varchar(200)  null,
  meta varchar(250)  null,
  link varchar(250)  null,
  event varchar(200)  null,
  data text,
  response text,
  extra text,
  `status` tinyint(1) not null default 1,
  `time` datetime null,
  PRIMARY KEY  (id),
  KEY entry_id (entry_id)
  )$charset_collate;";
  
   dbDelta($sql);
  
  $table_name = $this->get_crm_table_name('accounts');
  
      $sql= "CREATE TABLE $table_name (
   id int(11) unsigned not null auto_increment,
   name varchar(250) not null,
   data longtext,
   meta longtext,
  `status` int(1) not null default 0,
  `time` datetime null,
  `updated` datetime null,
  PRIMARY KEY  (id)
  )$charset_collate;";
  
   dbDelta($sql);
 
  }
  /**
  * Get tables names
  * 
  * @param mixed $table
  */
  public  function get_crm_table_name($table=""){
  global $wpdb;
  if(!empty($table))
  return $wpdb->prefix .  $this->id."_".$table;
  else
  return $wpdb->prefix . $this->id;
  }
  /**
  * get crm feeds
  * 
  */
  public  function get_feeds(){
  global $wpdb;
  $table_name = $this->get_crm_table_name();
  $form_table_name = RGFormsModel::get_form_table_name();
  $sql = "SELECT s.id, s.is_active,s.object,s.name,s.data,s.time, s.form_id, s.meta, f.title as form_title
  FROM $table_name s
  INNER JOIN $form_table_name f ON s.form_id = f.id
  ORDER BY s.sort";
  
  $results = $wpdb->get_results($sql, ARRAY_A);
  
  $count = sizeof($results);
 /* for($i=0; $i<$count; $i++){
  $results[$i]["meta"] = maybe_unserialize($results[$i]["meta"]);
  }*/
  
  return $results;
  }
 /**
 * get log from database
 *  
 */
  public  function get_log(){
  global $wpdb;
  $sql_end=$this->get_log_query();
  $sql_t="select count(s.id) as total $sql_end";
  $result= $wpdb->get_results($sql_t);  
  $items=isset($result[0]->total) ? $result[0]->total : 0;    
  $per_page = 20;
  $start = 0;
  $pages = ceil($items/$per_page);
  if(isset($_GET['page_id']))
  {
  $page=$this->post('page_id');
  $start = $page-1;
  $start = $start*$per_page;
  }
  $start=max($start,0);   
  $sql = "SELECT s.id, s.status,s.object ,s.parent_id, s.link, s.form_id, s.meta as error,s.entry_id,s.crm_id,s.time
  $sql_end
  limit $start , $per_page";

  $results = $wpdb->get_results($sql, ARRAY_A);     
                 
  $page_id=isset($_REQUEST['page_id'])&& $_REQUEST['page_id'] !="" ? $this->post('page_id') : "1";
  $range_min=(int)($per_page*($page_id-1))+1;
  $range_max=(int)($per_page*($page_id-1))+count($results);
  unset($_GET['page_id']);
 $query_h=$this->clean($_GET);$query_h=http_build_query($query_h);
  $page_links = paginate_links( array(
  'base' =>  admin_url("admin.php")."?".$query_h."&%_%" ,
  'format' => 'page_id=%#%',
  'prev_text' =>'&laquo;',
  'next_text' =>'&raquo;',
  'total' => $pages,
  'current' => $page_id,
  'show_all' => false
  ));
  
  return array("min"=>$range_min,"max"=>$range_max,"items"=>$items,"links"=>$page_links,"feeds"=>$results);
  }
  /**
  * get logs query string
  * 
  */
  public function get_log_query(){
  $search="";
  $table_name = $this->get_crm_table_name('log');
  $sql_end="FROM $table_name s";
  // handle search
  $time_key=$this->post('time');
  $time=current_time('timestamp');
  
  $offset = $this->time_offset();
  $start_date=""; $end_date="";
  switch($time_key){
  case"today": $start_date=strtotime('today',$time);  break;
  case"this_week": $start_date=strtotime('last sunday',$time);  break;
  case"last_7": $start_date=strtotime('-7 days',$time);  break;
  case"last_30": $start_date=strtotime('-30 days',$time); break;
  case"this_month": $start_date=strtotime('first day of 0 month',$time);  break;
  case"yesterday": 
  $start_date=strtotime('yesterday',$time);
  $end_date=strtotime('today',$time);  

  break;
  case"last_month": 
  $start_date=strtotime('first day of -1 month',$time); 
  $end_date=strtotime('last day of -1 month',$time); 

  break;
  case"custom":
   
  if(!empty($_GET['start_date'])){
  $start_date=strtotime($this->post('start_date').' 00:00:00');
  }
   if(!empty($_GET['end_date'])){
  $end_date=strtotime($this->post('end_date').' 23:59:59');
   } 
  break;
  }
  
  if($start_date!=""){
      $start_date-=$offset;
  $search.=' and s.time >="'.date('Y-m-d H:i:s',$start_date).'"';   
  }
  if($end_date!=""){
        $end_date-=$offset;
      if($time_key == "yesterday"){
  $search.=' and s.time <"'.date('Y-m-d H:i:s',$end_date).'"';
      }else{
  $search.=' and s.time <="'.date('Y-m-d H:i:s',$end_date).'"';
      }   
  }
  if($this->post('object')!=""){
  $search.=' and object ="'.esc_sql($this->post('object')).'"';   
  }
  if($this->post('status')!=""){
  $status=$this->post('status');
  if($status == "error"){$status="0";}
  $search.=' and status ='.esc_sql($status);   
  }
  if($this->post('search')!=""){
  $search_s=esc_sql($this->post('search'));
  $search.=' and (object like "'.$search_s.'" or crm_id="'.$search_s.'" or entry_id='.$search_s.')';   
  }
  if(isset($_GET['log_id']) && !empty($_GET['log_id'])){
  $log_id=esc_sql($this->post('log_id'));
  $search.=' and id='.$log_id.'';   
  }
  if($this->post('id')!=""){
  $form_id=(int)esc_sql($this->post('id'));
  $search.=' and form_id='.$form_id.'';   
  }
  if($this->post('entry_id')!=""){
  $entry_id=esc_sql($this->post('entry_id'));
  $search.=' and entry_id='.$entry_id.'';   
  }
  if($search!=""){
  $sql_end.=" where ".substr($search,4);
  }
  if($this->post('orderby')!=""){
  $sql_end.=' order by '.esc_sql($this->post('orderby'));   
  if($this->post('order')!="" && in_array($this->post('order'),array("asc","desc"))){
  $sql_end.=' '.$this->post('order'); 
  }
  }else{
  $sql_end.=" order by s.id desc";   
  } 
  return $sql_end;
  }
  /**
  * insert log in database
  * 
  * @param mixed $arr
  */
public  function insert_log($arr,$log_id=""){ 
    global $wpdb;
  if(!is_array($arr) || count($arr) == 0)
  return;
 // $wpdb->show_errors();
  $table_name = $table_name = $this->get_crm_table_name('log');
  $sql_arr=array();
  foreach($arr as $k=>$v){
      
      $v=is_array($v) ? json_encode($v) : $v;
      
      if(in_array($k,array('status','entry_id','feed_id'))){
       $v=floatval($v);   
      } 
     
      if(empty($v) && in_array($k,array('crm_id'))){
       $v='';   
      }
        if($k == 'meta' && strlen($v) > 250){
      $v=substr($v,0,250);    
      }
   $sql_arr[$k]= $v; 
    
  }
  $log_id=(int)$log_id;
  $res=false;
  if(!empty($log_id)){
       // update
   $res=$wpdb->update($table_name,$sql_arr,array("id"=>$log_id));   
  }else{ 
   $res=$wpdb->insert($table_name,$sql_arr);
   $log_id=$wpdb->insert_id;   
  }

  return $log_id; 
  }
    /**
  * clear logs
  * 
  */
  public  function clear_logs(){ 
  global $wpdb;
  $table_name = $this->get_crm_table_name('log');
  // update
  return $wpdb->query("truncate table `".$table_name."`");
  }
  /**
  * delete feed
  * 
  * @param mixed $id
  */
  public  function delete_feed($id){
  global $wpdb;
  $table_name = $this->get_crm_table_name();
  $wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE id=%s", $id));
  }  
  /**
  * delete log entries
  * 
  * @param mixed $id
  */
  public  function delete_log($log_ids){
  global $wpdb;
  $table=$this->get_crm_table_name('log');
         $count=0; 
  foreach($log_ids  as $id){
  $del=$wpdb->delete($table,array('id'=>$id),array( '%d' ));  
  if($del){$count++;}
  }  
  return $count;
  }
  /**
  * get form
  * 
  * @param mixed $form_id
  * @param mixed $only_active
  */
  public  function get_feed_by_form($form_id, $only_active = false){
            if(empty($form_id)){
          return array();
      }
  global $wpdb;
  $table_name = $this->get_crm_table_name();
  $active_clause = $only_active ? " AND is_active=1" : "";
  $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE form_id=%d $active_clause ORDER BY sort", $form_id);
  $results = $wpdb->get_results($sql, ARRAY_A);
  if(empty($results))
  return array();
  
  return $results;
  }
   /**
  * get object feeds
  * 
  * @param mixed $form_id
  * @param mixed $only_active
  */
  public  function get_object_feeds($form_id, $account , $object='',$skip=''){
  global $wpdb;
  $table_name = $this->get_crm_table_name();
  $vars=array($skip,$form_id, $account);
  $sql="SELECT * FROM $table_name WHERE id!=%d and form_id=%s and account=%d and is_active=1";
  if(!empty($object)){
      $sql.=' and object = %s';
      $vars[]=$object;
  }
  $sql.=' ORDER BY sort';
 $sql = $wpdb->prepare($sql,$vars);
  $results = $wpdb->get_results($sql, ARRAY_A);
  
  return $results;
  }
     /**
 * Get log of order and feed
 * 
 * @param mixed $feed_id
 * @param mixed $order_id
 */
 public function get_feed_log($feed_id,$order_id,$object,$parent_id=""){
          global $wpdb;
 $table = $this->get_crm_table_name('log');
  $sql= $wpdb->prepare('SELECT * FROM '.$table.' where entry_id = %d and feed_id = %d and crm_id!="" and object=%s  and parent_id=%d order by id desc limit 1',$order_id,$feed_id,$object,$parent_id);
$results = $wpdb->get_row( $sql ,ARRAY_A );


return $results;
 } 
  /**
  * get  single feed entry
  * 
  * @param mixed $id
  */
  public  function get_feed($id){ 
  global $wpdb;
  $table= $this->get_crm_table_name();
  if((string)$id =='new_form'){
  $results = $wpdb->get_results( 'SELECT * FROM '.$table.' where `time` is null limit 1',ARRAY_A );
  
  if(count($results) == 0){
  $wpdb->insert($table,array("is_active"=>"1"));
  return array("id"=>$wpdb->insert_id,"is_active"=>1);
  }else{
  return $results[0];   
  }     
  }
  $results = $wpdb->get_results( 'SELECT * FROM '.$table.' where id='.$id.' limit 1',ARRAY_A );
  if(count($results) == 0){
  return array();
  }
  $feed=$results[0];
  $fields=json_decode($feed['data'],true);
  $meta=json_decode($feed['meta'],true);
  $feed['meta']=is_array($meta) ? $meta : array();
  $feed['data']=is_array($fields) ? $fields : array();
  return $feed;
  }
    /**
  * get log by id
  * 
  * @param mixed $id
  */
  public function get_log_by_id($log_id){
              global $wpdb;
  $table= $this->get_crm_table_name('log');

  $sql = $wpdb->prepare("SELECT * FROM $table WHERE id=%d limit 1", $log_id);
  $log = $wpdb->get_row($sql, ARRAY_A);
return $log;
  }
      /**
  * get log by id
  * 
  * @param mixed $id
  */
  public function get_log_by_lead($lead_id,$parent_logs=true,$limit=1){
              global $wpdb;
  $table= $this->get_crm_table_name('log');
$sql="SELECT * FROM $table WHERE ";
if($parent_logs){
    $sql.='parent_id=0 and ';
}
$sql.='entry_id=%d order by id DESC limit %d';
  $sql = $wpdb->prepare($sql, $lead_id,$limit);
  $log = $wpdb->get_row($sql, ARRAY_A);
return $log;
  }
  /**
  * update feed
  * 
  * @param mixed $arr
  * @param mixed $id
  */
  public  function update_feed($arr,$id){ 
  global $wpdb;
  if(!is_array($arr) || count($arr) == 0)
  return;
  foreach($arr as $k=>$v){
  if(is_array($v)){
  $arr[$k]=json_encode($v);    
  }  
  }
  $table_name = $this->get_crm_table_name();
  // update
//  $wpdb->show_errors();
  return $wpdb->update($table_name,$arr, array('id' => $id));
  }
  
  /**
  * fields sorting order
  * 
  * @param mixed $data
  */
  public  function update_feed_order($data){
  global $wpdb;
  $table_name = $this->get_crm_table_name();
  
  if(!empty($data)) {
  foreach($data as $order=>$id) {
  $u=$wpdb->update($table_name,
  array('sort' => $order),
  array('id' => $id),
  array('%d'),
  array('%d')
  );
  ///                var_dump($u);
  }
  }
  
  return true;
  }
          /**
     * Get New Settings Id
     * @return int Settings id
     */
public function get_new_account() {
global $wpdb;
 $table= $this->get_crm_table_name('accounts');
$results = $wpdb->get_results( 'SELECT * FROM '.$table.' where status=9 limit 1',ARRAY_A );
$id=0; 
if(count($results) == 0){
    $wpdb->insert($table,array("status"=>"9"));
    $id=$wpdb->insert_id;
}else{
$id=$results[0]['id'];   
}     
return $id;
}
/**
* delete account
* 
* @param mixed $id
*/
public function del_account($id) {
global $wpdb;
 $table= $this->get_crm_table_name('accounts');
$res=$wpdb->delete( $table, array('id'=>$id) , array('%d'));
return $res;
}
/**
* get account by id
* 
* @param mixed $id
*/
public function get_account($id) {
global $wpdb; $id=(int)$id;
 $table= $this->get_crm_table_name('accounts');
$res=$wpdb->get_row( 'SELECT * FROM '.$table.' where id='.$id.' limit 1',ARRAY_A );
return $res;
}
/**
* update account
* 
* @param mixed $id
*/
public function update_account($sql, $id) {
global $wpdb;
 $table= $this->get_crm_table_name('accounts');
$res=$wpdb->update( $table, $sql,array('id'=>$id));
return $res;
}

      /**
     * Get all accounts
     */
public function get_accounts($verified=false) {
global $wpdb;
 $table= $this->get_crm_table_name('accounts');
 $sql='SELECT * FROM '.$table.' where';
 if($verified){
 $sql.=' status =1';
 }else{
     $sql.=' status !=9';
 }
 $sql.=' limit 100';

 $results = $wpdb->get_results( $sql ,ARRAY_A );

  return $results;   
}
  /**
  * drop tables
  * 
  */
  public  function drop_tables(){
  global $wpdb;
  $wpdb->query("DROP TABLE IF EXISTS " . $this->get_crm_table_name());
  $wpdb->query("DROP TABLE IF EXISTS " . $this->get_crm_table_name('log'));
  $wpdb->query("DROP TABLE IF EXISTS " . $this->get_crm_table_name('accounts'));
  delete_option($this->type."_version");
  }
}
}
?>