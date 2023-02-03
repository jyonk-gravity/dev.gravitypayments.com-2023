<?php
if ( ! defined( 'ABSPATH' ) ) {
     exit; 
 }                                            
 ?> 
<link rel='stylesheet' href='<?php echo $sel2_css ?>' type='text/css' media='all' />
<script type='text/javascript' src='<?php echo $sel2_js ?>'></script>
  <style type="text/css">
  label span.howto { cursor: default; }
  
  .vx_required{color:red; font-weight: normal;}
  .vx_contents *{
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  }
    .vx_div{
  padding: 10px 0px 0px 0px;
  }
  .vx_head{
  font-size: 14px;
  background: #f4f4f4;
  font-weight: bold;
  border: 1px solid #e4e4e4;
    -moz-user-select: none;
  -webkit-user-select: none;
  -ms-user-select: none;
  }
      .vx_head:hover{

  background: #f0f0f0;
  }
    .vx_group{
      border: 1px dashed #ccc;
      border-top-width: 0px ;
      padding: 14px;
        background: #fff;
  }
  .vx_row{
      padding: 10px 0px;
  }
  .vx_col1{float:left; width: 25%; padding-right: 20px; font-weight: bold;}
  .vx_col2{float:left; width: 75%; padding-right: 20px;}
  @media screen and (max-width: 782px) {
  .vx_col1{float:none; width: 100%;}
  .vx_col2{float:none; width: 100%}    
  }
  
  .alert_danger {
  background: #ca5952;
  padding: 5px;
  font-size: 12px;
  font-weight: bold;
  color: #fff;
  text-align: center;
  margin-top: 10px;
  }
  .crm_sel{
  min-width: 220px;
  }

  .vx_wrapper{
  border: 0px solid #e5e5e5;
  margin: 20px auto;
  width: 100%;

  }
  
  .vx_contents{
  padding-top: 20px;
  }
  .vx_heading{
  font-size: 18px;
  padding: 10px 20px;
  border-bottom: 1px dashed #ccc;
  }
  /*********custom fields***************/
  .vx_filter_div{
  border: 1px solid #eee;
  padding: 10px;
  background: #f3f3f3; 
  border-radius: 4px;  
  }
  .vx_filter_field{
  float: left; 
  }
  .vx_filter_field1{
  width: 32%;
  }
  .vx_filter_field2{
  width: 30%;
  }
  .vx_filter_field3{
  width: 30%;
  }
  .vx_filter_field4{
  width: 8%;
  }
  .vx_filter_field select{
  width:90%; display: block; 
  }
  .vx_btn_div{
  padding: 10px 0px;
  }
  .vx_filter_label{
  padding: 3px; 
  }
  .vxc_filter_text{
  max-width: 98%;
  width: 96%;
  }
  .vx_trash_or{
  color: #D20000;
  margin-left: 10px;
  }
  
  .vx_trash_or:hover{
  color: #C24B4B;
  }
  .vx_icons{
  font-size: 16px;
  vertical-align: middle;
  cursor: pointer;
  }
  .vx_icons-s{
  font-size: 12px;
  vertical-align: middle;  
  }
  .vx_icons-h{
  font-size: 16px;
  line-height: 28px;
  vertical-align: middle; 
  cursor: pointer; 
  }
  .vx_icons:hover , .vx_icons-h:hover{
  color: #888;
  }
  .reg_proc{
      display: none;
  }
/*******fields boxes****************/
.crm_panel * {
  -webkit-box-sizing: border-box; /* Safari 3.0 - 5.0, Chrome 1 - 9, Android 2.1 - 3.x */
  -moz-box-sizing: border-box;    /* Firefox 1 - 28 */
  box-sizing: border-box;  
}
.crm_panel_100{
margin: 10px 0;
}
.crm_panel_50{
    width: 48%;
    margin: 1%;
    min-width: 300px;
    float: left;
}
.crm_panel_head{
    background: linear-gradient(to bottom, rgba(255, 255, 255, 1) 0%, rgba(229, 229, 229, 1) 100%) repeat scroll 0 0 rgba(0, 0, 0, 0);
    border: 1px solid #ddd;  
}
.crm_panel_head2{
    background:  #f4f4f4;
    border: 1px solid #e8e8e8; 
  
      -moz-user-select: none;
  -webkit-user-select: none;
  -ms-user-select: none; 
}
.crm_panel_head2:hover{
    background: #f0f0f0; 
}
.crm_panel_head2 .crm_head_text{
     font-weight: bold; 
}
.crm_head_div{
 float: left;
 width: 80%;  padding: 8px 20px;   
}
.crm_panel_content{
    border: 1px solid #e8e8e8;
    border-top: 0px;
    display: block;
    padding: 12px;
    background: #fff;
    overflow: auto;
}
.crm-block-content{
height: 200px;
overflow: auto;
}
.crm_btn_div{
 float: right;
 width:20%;  padding: 8px 20px; 
 text-align: right;
}
.vx_action_btn:hover{
    color: #333;
}
 .vx_action_btn{
     color: #777; cursor: pointer;
     vertical-align: middle;
     font-size: 16px;
 }
 .vx_remove_btn{
   margin-right: 7px;  
 }
.vx_input_100{
width: 100%;
}
.crm_clear{
    clear: both;
}
 .entry_row {
 margin: 7px auto;   
}
.entry_col1 {
    float: left;
    width: 25%;
    padding: 0px 7px;
    text-align: left;
}
 .entry_col2 {
    float: left;
    width: 75%;
    padding-left: 7px;
}
.vx_margin{
margin-top: 10px;
}
.vx_red{
color: #E31230;
}
.vx_label{
    font-weight: bold;
}
.vx_error{
    background: #ca5952;
    padding: 10px;
    font-size: 14px;
    margin: 1% 2%;
    color: #fff;
}
.crm_panel .vx_error{
    margin: 0;
}
.vx_wrap .subsubsub{
margin-top: 0px;
margin-left: 2px;
}
.vx_fields_footer{
    padding: 10px 0px;
    background: #f1f1f1;
}
.vxc_field_value{
    margin-bottom: 7px;
}
.vxcf_options_row .howto{
    display: none;
}
  </style>
  <div class="vx_wrap"> <img alt="<?php esc_html_e("Salesforce Feeds", 'gravity-forms-salesforce-crm') ?>" title="<?php esc_html_e("Salesforce Feeds", 'gravity-forms-salesforce-crm') ?>" src="<?php echo $this->get_base_url()?>images/<?php echo $this->crm_name ?>-crm-logo.png?ver=1" style="float:left; margin:0px 7px 10px 0;" height="46" />
  <h2 style="margin-bottom: 12px"><?php esc_html_e("Salesforce Feeds", 'gravity-forms-salesforce-crm'); ?>
  <?php
      if($fid != $config['id']){
  ?>
  <a class="add-new-h2" href="<?php echo esc_url($new_feed_link)?>"><?php esc_html_e("Add New", 'gravity-forms-salesforce-crm') ?></a>
  <?php
      }
      if(!empty($fid)){
  ?>
  <a class="add-new-h2" href="<?php echo esc_url($feeds_link)?>"><?php esc_html_e("Back to Feeds", 'gravity-forms-salesforce-crm') ?></a>
  <?php
      }
  ?>
  </h2>
  <div class="clear"></div>
  <ul class="subsubsub" style="margin-top:0;">
    <?php
      foreach($menu_links as $k=>$link){
          ?>
       <li>
       <?php
       if($k!="settings"){
          echo " | ";   
        }
       ?>
        <a href="<?php echo esc_url($link['link']) ?>" title="<?php echo esc_attr($link['title'])?>" <?php if($link['current']){echo 'class="current"';} ?>><?php echo esc_html($link['title'])?></a> 
       </li>     
          <?php
      }
  ?>
  </ul>
  <div class="clear"></div>
  <?php
      //
  $feed_name=$this->post('name',$feed);
if(empty($feed_name)){
    $feed_name="Feed #".$fid;
}
  //ensures valid credentials were entered in the settings page
  if(!empty($account) && !$this->api_is_valid($info)) {
  ?>
  <div class="error" id="message" style="margin-top:20px;"><?php echo wpautop(sprintf(__("We are unable to login to Salesforce with the provided API token. Please make sure they are valid in the %sSettings Page%s", 'gravity-forms-salesforce-crm'), "<a href='".$this->link_to_settings()."'>", "</a>")); ?></div>
  <?php
  // return;
  } 
  ?>
  <div class="vx_wrapper">
  <div class="vx_heading"><?php echo sprintf(__('Edit Salesforce Feed # %d','gravity-forms-salesforce-crm') ,$fid) ?></div>
  <div class="vx_contents">

  <form method="post" action="">
  <input type="hidden" name="id" id="vx_id" value="<?php echo esc_attr($fid) ?>"/>
  <?php wp_nonce_field("vx_nonce") ?>
    <div class="vx_div">
      <div class="vx_head">
<div class="crm_head_div"> <?php esc_html_e('1. Enter Feed name',  'gravity-forms-salesforce-crm' ); ?></div>
<div class="crm_btn_div" title="<?php esc_html_e('Expand / Collapse','gravity-forms-salesforce-crm') ?>"><i class="fa crm_toggle_btn vx_action_btn fa-minus"></i></div>
<div class="crm_clear"></div> 
  </div>
  <div class="vx_group">
  <div class="vx_row">
  <div class="vx_col1">
  <label for="vx_name" class="left_header"><?php esc_html_e("Feed Name", 'gravity-forms-salesforce-crm'); ?>
  <?php gform_tooltip("vx_feed_name") ?>
 </label>
  </div>
  <div class="vx_col2">
  <input type="text" id="vx_name" class="vx_input_100" name="name" autocomplete="off" value="<?php echo esc_html($feed_name);  ?>" >
  </div>
  <div class="clear"></div>
  </div>
  </div>
  </div>
  <div class="vx_div">
        <div class="vx_head">
<div class="crm_head_div"> <?php esc_html_e('2. Select Salesforce Account.', 'gravity-forms-salesforce-crm'); ?></div>
<div class="crm_btn_div" title="<?php esc_html_e('Expand / Collapse','gravity-forms-salesforce-crm') ?>"><i class="fa crm_toggle_btn vx_action_btn fa-minus"></i></div>
<div class="crm_clear"></div> 
  </div>

  <div class="vx_group">
    <div class="vx_row">
  <div class="vx_col1">
  <label for="vx_account" class="left_header"><?php esc_html_e("Salesforce Account", 'gravity-forms-salesforce-crm'); ?>
  <?php gform_tooltip("vx_sel_account") ?>
 </label>
  </div>
  <div class="vx_col2">
  <select id="vx_account" class="load_object crm_sel" name="account" autocomplete="off">
  <option value=""><?php esc_html_e("Select a Salesforce Account", 'gravity-forms-salesforce-crm'); ?></option>
  <?php
  $this->account=$account=$this->post('account',$feed); 
  $account_found=false;
  foreach ($accounts as $k=>$v){
  $sel='';
  if($account == $v['id']){
  $sel='selected="selected"';
  $account_found=true;
  }   
  ?>
  <option value="<?php echo esc_attr($v['id']) ?>" <?php echo $sel; ?>><?php echo esc_html($v['name']) ?></option>
  <?php
  }
  ?>
  </select>

  </div>
  <div class="clear"></div>
  </div>
  </div>
  </div>

    <div id="crm_ajax_div_object" style="display:none; text-align: center; line-height: 100px;"><i> <?php esc_html_e('Loading, Please Wait...','gravity-forms-salesforce-crm'); ?></i></div>

  <div id="crm_object_div" style="<?php if(empty($account)) {echo 'display:none';} ?>">
  
    <?php
    
    if(!empty($account)){
  $this->field_map_object($account,$feed,$info);
    }
  ?>
  </div>


  </form>
  </div>
  </div>
  </div>

  
  <script type="text/javascript">
    var vx_crm_ajax='<?php echo wp_create_nonce("vx_crm_ajax") ?>';
    var vx_form_id='<?php echo esc_html($form_id); ?>';
        var vx_ajax=false;
  jQuery(document).ready(function($) {
      
  $(document).on("change",".vxc_field_type",function(e){ 
  e.preventDefault(); 
  var div=$(this).parents('.crm_panel');
  var val=$(this).val();
  if(val && $.inArray(val,['value_date','value_time']) > -1){ val='value'; }
  div.find('.vxc_fields').hide();
  div.find('.vxc_field_'+val).show();
  if(val){
      var input=div.find('.vxc_field_input');
      if(!input.val()){
    var option=div.find('.vxc_field_option').val();
    if(option){    input.val("{"+option+"}");  }     
      }
  }
  if(div.find('.vxcf_options_row').length){
      if(!val){
     div.find('.vxc_field_option').show();
      }else{ 
     div.find('.vxc_field_option').hide(); 
      }
  }
  });
  
    $(document).on("click",".vx_refresh_btn",function(e){
   var check=$(this);
      if(check.is(':checked')){
       var box=check.parents('.vx_group');
       box.find('.vx_refresh_data').trigger('click');   
      }   
  });



  //toggle boxes
  $(document).on("click",".crm_toggle_btn",function(e){
    e.preventDefault();
    var btn=jQuery(this);
    if(btn.hasClass("vx_btn_inner")){
    var panel=btn.parents(".crm_panel");
    var div=panel.find(".crm_panel_content");    
    }else{
    var panel=btn.parents(".vx_div");
    var div=panel.find(".vx_group");      
    }
   
 div.slideToggle('fast',function(){
  if(div.is(":visible")){
 btn.removeClass('fa-plus');     
 btn.addClass('fa-minus');     
  }else{
      btn.addClass('fa-plus');     
 btn.removeClass('fa-minus');     
  }   
 });
});
$(document).on("dblclick",".vx_head,.crm_panel_head2",function(e){
    e.preventDefault();
    $(this).find('.crm_toggle_btn').trigger('click');
});
//post validation

  $("form").submit(function(e){
 if($(".vx_required").length){

   $(".vx_required").each(function(){ 
   var value=""; 
       if($(this).hasClass('vx_req_parent')){
       var panel=$(this).parents(".vx_div"); 
    var attach_error=field_div=group=panel.find('.vx_group'); 
    var head=panel.find('.vx_head'); 
    var input=group.find(".crm_toggle_check");
    if(input.is(":checked")){
             var input=group.find("select");
          value=input.val();  
    }

       }else{
    var panel=$(this).parents(".crm_panel");
    var parent=panel.parents(".vx_div");
    var field_div=panel.find(".crm_panel_content");
    var group=parent.find('.vx_group'); 
    var head=parent.find('.vx_head'); 
    var attach_error=panel.find(".vx_margin");
    
        var field=panel.find(".vxc_field_type").val();
    var row=panel.find(".vxc_field_"+field);
    var input=row.find(":input"); 
        value=input.val(); 
       }
    
    
        panel.find(".vx_entry_error").remove();
    if(value == ""){
     e.preventDefault();

     if(!group.is(":visible")){ 
         head.find(".crm_toggle_btn").trigger('click');
         setTimeout(function(){ input.focus();},500);
     }else{
       input.focus();   
     }  
          if(field_div.is(":hidden")){
       panel.find(".crm_toggle_btn").trigger('click');
     }
    attach_error.append('<div class="entry_row vx_entry_error"><div class="vx_error"><i class="fa fa-warning"></i> <?php esc_html_e('This is a required field','gravity-forms-salesforce-crm') ?></div></div>');
    return false;
    }  
   })  
 }
  })
       
  $(document).on("click",".crm_toggle_check",function(e){
      var id=$(this).attr('id');
  var div=$("#"+id+"_div");   
  if(this.checked){
  div.show();
  if(this.id == 'crm_note'){
     add_note_sel();
  }
  }else{
  div.hide();
  }  
  })

  $(document).on("click","#vx_refresh_fields",function(e){
  e.preventDefault();
  load_fields(true);   
  });
  $(document).on("click","#vx_refresh_objects",function(e){
  e.preventDefault();
  var button=$(this);
  var select=$("#vx_module");
  var account=$("#vx_account").val();
  var object=select.val(); 
  button_state_vx('ajax',button);
  $.post(ajaxurl,{action:'get_objects_<?php echo esc_attr($this->id) ?>',vx_crm_ajax:vx_crm_ajax,object:object,account:account},function(res){
   button_state_vx('ok',button);  
  if(res!=""){
   select.html(res);
  }
  })
  });
  $(document).on("change",".load_form",function(e){
  e.preventDefault();
  load_fields(false);  
  });
  $(document).on("change",".load_object",function(e){
  load_object();  
  });
 
  $(document).on("click",".vx_remove_btn",function(e){ 
  e.preventDefault(); 
    if(!confirm('<?php esc_html_e('Are you sure to remove ?','gravity-forms-salesforce-crm') ?>')){
     return;   
    }
  var temp=$(this).parents(".crm_panel");
  temp.find('.crm-desc-name').removeClass('crm-desc-name');

  mark_del(temp);
  update_fields_sel_vx();
  });
    $(document).on("click","#toggle_camp",function(e){
      e.preventDefault();  
  var btn=$(this);
  var ok=btn.find(".reg_ok");
  var proc=btn.find(".reg_proc");
  var sel=$("#crm_sel_camp");
  var input=$("#crm_camp_id");
  var camp_type=$("#crm_camp_type");
  if(ok.is(":visible")){
      //
  ok.hide();
  proc.show();
   input.show();   
   sel.hide();   
   camp_type.val('input');   
  }else{
        button_state_vx("ok",btn);
     input.hide();   
   sel.show();   
   camp_type.val('');  
     ok.show();
  proc.hide();  
  }  
  });
  
  $(document).on("click",".vx_refresh_data",function(e){
  e.preventDefault();  
  var btn=$(this);
  var action=$(this).data('id');
  var account=$("#vx_account").val();
  button_state_vx("ajax",btn);
  $.post(ajaxurl,{action:'refresh_data_<?php echo esc_attr($this->id) ?>',vx_crm_ajax:vx_crm_ajax,vx_action:action,account:account},function(res){
  var re=$.parseJSON(res);
  button_state_vx("ok",btn);  
  if(re.status){
 if(re.status == "ok"){
  $.each(re.data,function(k,v){
   if($("#"+k).length){
   $("#"+k).html(v);    
   }   
  })   
 }else{
  if(re.error && re.error!=""){
      alert(re.error);
  }   
 }
  }   

  });   
  });  
 
 $(document).on("change",".vxc_field_option",function(e){
    var col=$(this).parents('.entry_col2');
    var val=$(this).val(); 
    var input=col.find('.vxc_field_input');
    if(input.is(':visible')){ 
     var input_val=input.val();
 
     input_val+=' {'+val+'}';
     input_val=$.trim(input_val);
     input.val(input_val);
    }    
 });   
  //
jQuery('.crm_panel').each(function(){
var panel=$(this);
 verify_options(panel);   
});
function verify_options(panel){
    var name=$.trim(panel.find('.crm-desc-name').text());
if(name && typeof crm_fields != 'undefined' && crm_fields[name].options){
var row=panel.find('.entry_row2');
var type=panel.find('.vxc_field_type').val();
var input=row.find('.vxc_field_input');
var val=input.val();
var str='<select name="'+input.attr('name')+'" id="'+input.attr('id')+'" class="vxc_field_input vx_input_100">';
str+='<option value="">Select any option</option>';

jQuery.each(crm_fields[name].options,function(k,v){
    if(v.hasOwnProperty('value')){
     if(v.hasOwnProperty('name')){
      k=v.name;   
     }
    v=v.value;    
    }
str+='<option value="'+k+'">'+v+'</option>';    
});
str+='</select>';    
input.replaceWith(str);
input=row.find('.vxc_field_input');
input.val(val);

row.addClass('vxcf_options_row');
if(type){ row.find('.vxc_field_option').hide(); }
}
}
  $(document).on("click","#xv_add_custom_field",function(e){ 
  var temp=$("#vx_field_temp .vx_fields").clone();
  var field_name_select=$('#vx_add_fields_select');
  if(field_name_select.length){
    var field_name=id=field_name_select.val();
    if(field_name == '' || crm_fields[field_name] == ''){
     alert('<?php esc_html_e('Please Select Field Name','gravity-forms-salesforce-crm') ?>');
     return;   
    }  
 var field=crm_fields[field_name];
if(field.type){
 temp.find('.crm-desc-type').text(field.type);
}else{
    temp.find('.crm-desc-type-div').remove();
}
//
if(field.name){
 temp.find('.crm-desc-name').text(field.name);
}else{
    temp.find('.crm-desc-name-div').remove();
}

if(field.eg){
 temp.find('.crm-eg').text(field.eg);
}else{
    temp.find('.crm-eg-div').remove();
}
//
if(field.maxlength){
 temp.find('.crm-desc-len').text(field.maxlength);
}else{
    temp.find('.crm-desc-len-div').remove();
}
//
if(field.label){
 temp.find('.crm_text_label').text(field.label);
}
  }else{
  
  var id=rand();
  }
  temp.find(":input").each(function(){
  var name=$(this).attr('name');
  if(name){
  $(this).attr('name','meta[map]['+id+']['+name+']'); 
  }  
  });
  verify_options(temp); 
  $("#vx_field_temp").before(temp);
  update_fields_sel_vx();
  $(this).blur();
  });
  //
  $(document).on("click",".vx_add_or",function(e){ 
  e.preventDefault(); 
  var par=$(this).parent(".vx_btn_div");   
  var div=$("#vx_filter_temp");
  var temp=div.find(".vx_filter_or").clone();
  var par_id=rand();
  temp.attr('data-id',par_id);
  var id=rand();
  temp.find(":input").each(function(){
  var name=$(this).attr('name');
  if(name)
  $(this).attr('name','meta[filters]['+par_id+']['+id+']['+name+']');   
  });
  temp.find(".vx_filter_label_and").remove();
  temp.find(".vx_filter_field4").remove();
  par.before(temp);
  });
  $(document).on("click",".vx_trash_or",function(e){ 
  e.preventDefault(); 
  var temp=$(this).parents(".vx_filter_or");
  mark_del(temp);
  });
  $(document).on("click",".vx_trash_and",function(e){ 
  e.preventDefault(); 
  var temp=$(this).parents(".vx_filter_and");
  mark_del(temp);
  });
  $(document).on("click",".vx_add_and",function(e){ 
  e.preventDefault(); 
  var par=$(this).parent(".vx_btn_div");   
  var div=$("#vx_filter_temp");
  var temp=div.find(".vx_filter_and").clone();
  var par_id=$(this).parents(".vx_filter_or").attr('data-id');
  var id=rand();
  temp.find(":input").each(function(){
  var name=$(this).attr('name');
  if(name)
  $(this).attr('name','meta[filters]['+par_id+']['+id+']['+name+']');   
  })
  par.before(temp);
  });
  
   $(document).on("change",".optin_select_filter",function(e){
    e.preventDefault();
 filter_value_field($(this));
});
$('.optin_select_filter').each(function(){
filter_value_field($(this));   
});

  function filter_value_field(elem){
     var val=elem.val();
     var op=$('#cfx_op_'+val).clone();
     var div=$(elem).parents('.vx_filter_and');
if(op.length){
var text= div.find('.vxc_filter_text');
 div.find('.cfx_op').remove();
   text.hide();  
   div.find('.cfx_op_sel option').each(function(){
     var option=$(this);
     if($.inArray(option.attr('value'),['','not_is','empty','not_empty'])==-1 ){
         option.attr('disabled','disabled');
     }  
   }); 
   var text_input=div.find('.vxc_filter_text');
   op.attr('name',text_input.attr('name'));
   op.val(text_input.val());
   text.after(op);
}else{
 div.find('.cfx_op').remove(); 
 div.find('.vxc_filter_text').show(); 
 div.find('.cfx_op_sel option').removeAttr('disabled'); 
}
 }
  function add_note_sel(){
    jQuery('#crm_note_fields').select2({ placeholder: '<?php esc_html_e('Select Field','gravity-forms-salesforce-crm') ?>'});
} 
  function mark_del(obj){
  obj.css({'opacity':'.5'});
  obj.fadeOut(500,function(){
  $(this).remove();
  });
  }
  function rand(){
  return Math.round(Math.random()*1000000000);
  }
  function update_fields_sel_vx(){
    if(!jQuery('#vx_add_fields_select').length){
        return;
    }
var fields_boxes=[];
    jQuery('.crm-desc-name').each(function(){
   var val= jQuery.trim(jQuery(this).text());
   if(val){
       fields_boxes.push(val);
   } 
}); 
var str='';
if(crm_fields){ 
jQuery.each(crm_fields , function(k,v){
var disable='';
    if(jQuery.inArray(k,fields_boxes) > -1){
disable='disabled="disabled"';
    }
 str+='<option value="'+k+'" '+disable+'>'+v.label+'</option>';
//}   
})
}
jQuery('#vx_add_fields_select').html(str);
jQuery('#vx_add_fields_select').val('');
jQuery('#vx_add_fields_select').trigger('change');
}
  function load_fields(refresh){
        if(vx_ajax && vx_ajax.abort){
    vx_ajax.abort();  
  }   
  var module=$("#vx_module").val();
  var account=$("#vx_account").val();
  var id=$("#vx_id").val();
  ///var primary=$("#crm_primary_field");
  var ajax=$("#crm_ajax_div");
  var fields=$("#crm_field_group");
  ///var fields_div=$("#fields_div");
  //if(form_id == "" || module == "")
  //return;
  ajax.show();
  fields.hide();
  var button=$("#vx_refresh_fields");
  button_state_vx('ajax',button);
  vx_ajax=$.post(ajaxurl,{action:'get_field_map_<?php echo esc_attr($this->id) ?>',form_id:vx_form_id,account:account,module:module,id:id,vx_crm_ajax:vx_crm_ajax,refresh:refresh},function(res){
  fields.html(res);   
  //   primary.html(re.fields);
  fields.slideDown(); button_state_vx('ok',button);  
start_tooltip(); 
  ajax.hide();
    jQuery('.crm_panel').each(function(){
var panel=$(this);
 verify_options(panel);   
});
  }) 
  }
  function load_object(){
        if(vx_ajax && vx_ajax.abort){
    vx_ajax.abort();  
  }    
  var account=$("#vx_account").val();
  var id=$("#vx_id").val();
  ///var primary=$("#crm_primary_field");
  var ajax=$("#crm_ajax_div_object");
  var fields=$("#crm_object_div");
  ///var fields_div=$("#fields_div");
  //if(form_id == "" || module == "")
  //return;
  ajax.show();
  fields.hide();

  vx_ajax=$.post(ajaxurl,{action:'get_field_map_object_<?php echo esc_attr($this->id) ?>',form_id:vx_form_id,account:account,id:id,vx_crm_ajax:vx_crm_ajax},function(res){
  fields.html(res);   
  //   primary.html(re.fields);
  fields.slideDown();   
start_tooltip();
  ajax.hide();

  }) 
  }
  function start_tooltip(){
        jQuery( ".gf_tooltip" ).tooltip({
  show: 500,
  hide: 1000,
  content: function () {
  return jQuery(this).prop('title');
  }
  }); 
vx_select();
  }
  vx_select();
  function vx_select(){
      $('#vx_add_fields_select').select2({ placeholder: '<?php esc_html_e('Select Field','gravity-forms-salesforce-crm') ?>'});
      
      add_note_sel();
  }
  $(document).on("click","#load_objects",function(e){
  e.preventDefault();
  var id=$("#vx_id").val();
  var ajax=$("#crm_ajax_div");
  var fields=$("#vx_module");
  var fields_div=$("#crm_field_group");
  ajax.show();
  fields_div.hide();
  $.post(ajaxurl,{action:'get_objects_list_<?php echo esc_attr($this->id) ?>',id:id},function(res){
  fields.html(res);    
  ajax.hide();
  })    
  });
    function button_state_vx(state,button){
var ok=button.find('.reg_ok');
var proc=button.find('.reg_proc');
     if(state == "ajax"){
          button.attr({'disabled':'disabled'});
ok.hide();
proc.show();
     }else{
         button.removeAttr('disabled');
   ok.show();
proc.hide();      
     }
}
  });

  

  </script>
