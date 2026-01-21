/*
 Copyright (c) 2012  John Goodman  www.unverse.net

 Permission is hereby granted, free of charge, to any person obtaining a copy of this software
 and associated documentation files (the "Software"), to deal in the Software without restriction,
 including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
 and/or sell copies of the Software, and to permit persons to whom the Software
 is furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included
 in all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.

 IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
 DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
 ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE
 OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

/*
 Copyright (c) 2013 Simon Porritt, http://jsplumb.org/

 Permission is hereby granted, free of charge, to any person obtaining
 a copy of this software and associated documentation files (the
 "Software"), to deal in the Software without restriction, including
 without limitation the rights to use, copy, modify, merge, publish,
 distribute, sublicense, and/or sell copies of the Software, and to
 permit persons to whom the Software is furnished to do so, subject to
 the following conditions:

 The above copyright notice and this permission notice shall be
 included in all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

var jQueryCgmp = jQuery.noConflict();
(function (jQuery) {
   jQuery(document).ready(function (jQuery) {
      //-------------first setting------------------------
      var stepNum = 0;
      var wfConn = {}; // action connection object
      var connSet = {"path": "blue"}; //default success connection
      var selectedStep = {};
      var newconnection = false; // For checking 'new connection' or 'edit connection'
      var copystep = {};
      jQuery("#workflow-area").css("height", jQuery(".fc_action").css("height"));
      jQuery(".dropable-area").droppable({
         activeClass: "ui-state-hover",
         hoverClass: "ui-state-active",
         drop: function (event, ui) {
            // if (jQuery(ui.draggable).attr('class') == 'w ui-draggable ui-droppable' ||
            //   jQuery(ui.draggable).attr('class') == 'w ui-draggable ui-draggable-handle ui-droppable') {
            //    return;
            // } //No workflow info box droppable

            if (ui.draggable[0].className.indexOf('w ui-draggable ui-droppable') >= 0 ||
              ui.draggable[0].className.indexOf('w ui-draggable ui-draggable-handle ui-droppable') >= 0) {
               return
            } //No workflow process box droppable

            stepNum = 0;
            var getNum = get_step_id_num(stepNum);
            var lbl = jQuery(ui.draggable).children().html();
            get_step_lbl_inx(lbl);
            var Y = event.originalEvent.pageY - 150;
            var X = event.originalEvent.pageX - 170;
            var p = {
               "fc_addid": "step" + getNum,
               "fc_label": obj_lbl,
               "fc_dbid": "nodefine",
               "fc_process": lbl,
               "fc_position": [Y + "px", X + "px"]
            };
            createStep(p, "new");
            set_step_chaned_status(); // We can know what workflow graphic was changed.
         }
      });

      jQuery("#wfsortable").sortable();
      jQuery("#workflow-info-area").sortable();
      // jQuery('#process-info-div').draggable()
      window.workflowGraphic = {
         init: function () {
            jsPlumb.importDefaults({
               Endpoint: ["Dot", {radius: 2}],
               //overlays:[ ["PlainArrow", {location:1, width:20, length:12} ]],
               connectorStyle: {strokeStyle: "blue", lineWidth: 2},
               HoverPaintStyle: {strokeStyle: "#42a62c", lineWidth: 8},
               paintStyle: {
                  lineWidth: 3,
                  strokeStyle: "blue"
               },
               anchor: "Continuous",
               ConnectionOverlays: [
                  ["Arrow", {
                     location: 1,
                     id: "arrow",
                     length: 0,
                     foldback: 0.8
                  }]
               ],
               maxConnections: -1
            });
         }
      };

      //------------------step creating----------------
      var get_step_id_num = function (cn) {
         if (jQuery("#step" + cn).length > 0) {
            stepNum++;
            get_step_id_num(stepNum);
         }
         return stepNum;
      }

      var lbl_inx = 0;
      var obj_lbl = "";
      var get_step_lbl_inx = function (lbl) {
         var chk = true;
         jQuery(".fc_action .w").each(function () {
            var obj_lbl = jQuery(this).children("label").html();
            if (jQuery.trim(obj_lbl) == jQuery.trim(lbl))
               chk = false;
         });

         if (chk) {
            obj_lbl = lbl;
            lbl_inx = 0;
            return;
         }
         lbl_inx++;
         var temp = lbl.split("-");
         get_step_lbl_inx(temp[0] + "-" + lbl_inx);

      }

      var __createStep = function (objid) {
         //jsPlumb.draggable(jsPlumb.getSelector("#"+objid));
         jsPlumb.draggable(jsPlumb.getSelector("#" + objid), {containment: "parent"});
         jsPlumb.makeTarget(jsPlumb.getSelector("#" + objid), {anchor: "Continuous"});
         jsPlumb.makeSource(jQuery('#ep-' + objid), {
            parent: jQuery('#ep-' + objid).parent(),
            anchor: "Continuous",
            //connectorStyle:{ strokeStyle:"blue", lineWidth:2 },
            maxConnections: -1,
         });
      }
      var createStep = function (param, act) {
         jQuery("#workflow-area").append("<div class='w' id='" + param["fc_addid"] + "'" +
            "' real='" + act + "'" +
            " db-id=" + param["fc_dbid"] +
            " process-name='" + param["fc_process"] + "'>" +
            " <img alt='' src='" + wfPluginUrl + "img/" + param["fc_process"] + ".gif' />" +
            " <label>" + param["fc_label"] + "</label>" +
            "</div>");
         var help_text = drag_drop_jsplumb_vars.stepHelp + " " + drag_drop_jsplumb_vars.connectionHelp;
         jQuery("#" + param["fc_addid"]).append("<div class='ep' id='ep-" + param["fc_addid"] + "'></div>");
         jQuery("#" + param["fc_addid"]).append("<a href='#' title='" + help_text + "' class='tooltip'><span title=''><img class='ep-help' id='ep-help-" + param["fc_addid"] + " alt='' src='" + wfPluginUrl + "img/help.png'/><span></a>");
         jQuery("#" + param["fc_addid"]).css({'top': param["fc_position"][0], 'left': param["fc_position"][1]});
         __createStep(param["fc_addid"]);

         // if the step is newly added to the designer area, then show the step info popup right away
         if (act != 'old') {
            set_selected_step("<div class='w' id='" + param["fc_addid"] + "'" +
               "' real='" + act + "'" +
               " db-id=" + param["fc_dbid"] +
               " process-name='" + param["fc_process"] + "'>" +
               " <img alt='' src='" + wfPluginUrl + "img/" + param["fc_process"] + ".gif' />" +
               " <label>" + param["fc_label"] + "</label>" +
               "</div>");
            jQuery("#stepEdit a").trigger("click");
         }
      };

      //-----------workflow info-----------------

      _get_workflow_info = function () {
         if (!check_created_workflow())
            return false;
         var conn = jsPlumb.getConnections();

         var workflow_datas = {}, steps = {}, conns = {}, error_chk = true;

         jQuery(".fc_action .w").each(function () {
            var iid = jQuery(this).attr('id');

            steps[iid] = {
               "fc_addid": iid,
               "fc_label": jQuery("#" + iid + " label").html(),
               "fc_dbid": jQuery.trim(jQuery("#" + iid).attr("db-id").escapeSpecialChars()),
               "fc_process": jQuery("#" + iid).attr("process-name"),
               "fc_position": [jQuery("#" + iid).css('top'), jQuery("#" + iid).css('left')]
            };
         });

         for (var i = 0; i < conn.length; i++) {
            // connector - StateMachine - specified in jsPlumb lib
            conns[i] = {
               "sourceId": conn[i].sourceId,
               "targetId": conn[i].targetId,
               'post_status': conn[i].getParameter('post_status'),
               "connset": {"connector": "StateMachine", "paintStyle": conn[i].paintStyleInUse}
            };
         }

         workflow_datas["steps"] = steps;
         workflow_datas["conns"] = conns;
         workflow_datas["first_step"] = get_first_step();

         return jQuery.toJSON(workflow_datas);
      }

      get_first_step = function () {
         var i = 0;
         var first_step = [];
         jQuery(".fc_action .w").each(function () {
            if (jQuery(this).attr("first_step") == "yes") {
               first_step.push({
                  step: jQuery(this).attr("id"),
                  post_status: jQuery(this).attr('post_status')
               });
               i++;
            }
         });
         return first_step;
      };

      /**
       * Validate connection path between two steps ie. source and target
       * @return Boolean
       *
       * @since 2.1
       */
      check_created_workflow = function () {
         var conn = jsPlumb.getConnections();
         var temp = {};
         var ttemp = {};
         for (var i = 0; i < conn.length; i++) {
            temp[conn[i].sourceId + '_' + conn[i].targetId] = conn[i].paintStyleInUse.strokeStyle;
         }

         var source, sourceId, target, targetId;
         for (var i = 0; i < conn.length; i++) {
            sourceId = conn[i].sourceId;
            targetId = conn[i].targetId;

            if (temp[targetId + '_' + sourceId] && conn[i].paintStyleInUse.strokeStyle == temp[targetId + '_' + sourceId]) {
               source = jQuery('#' + sourceId).children('label').html();
               target = jQuery('#' + targetId).children('label').html();

               alert(drag_drop_jsplumb_vars.pathBetween + ' ' + source + ' ' + drag_drop_jsplumb_vars.stepAnd + ' ' + target + ' ' + drag_drop_jsplumb_vars.incorrect);
               return false;
            }
         }

         return true;
      }
      //------------workflow painting on load -------------------

      /**
       * Display the workflow steps on workflow design canvas and
       * store the workflow step's and connection data into the @global jsPlumb library
       *
       * @param mixed param
       * @return false if @param is not object | @param is object then it display the UI on canvas
       *
       * @since 2.1
       */
      _graphic_make = function (param) {
         var wfinfo = {};
         wfinfo = jQuery.parseJSON(param);
         if (typeof wfinfo !== 'object') {
            alert('graphic data is bad');
            return false;
         }

         // paint all the steps one by one
         for (var w in wfinfo['steps']) {
            createStep(wfinfo['steps'][w], 'old');
         }

         // paint all the connections one by one
         for (var k in wfinfo['conns']) {
            var stepInfo = wfinfo['conns'][k];
            jsPlumb.connect({
               source: stepInfo['sourceId'],
               target: stepInfo['targetId'],
               parameters: {
                  post_status: stepInfo['post_status']
               }
            }, stepInfo['connset']);
         }

      };

      /**
       * Set first step of workflow in workflow design canvas
       * and also change the background color of step, font color and first step's post status
       * @param object param
       * @return void
       *
       * @since 2.1
       */
      set_first_step = function (param) {
         var wfinfo = {};
         var first_step = [];
         wfinfo = jQuery.parseJSON(param);
         if (wfinfo && wfinfo.first_step) {
            first_step = wfinfo.first_step;
         }
         if (first_step.length) {
            first_step = first_step[0];
            jQuery('.fc_action .w').each(function () {
               var step_id = jQuery(this).attr('id');
               if (first_step.step === step_id) {
                  jQuery(this).attr('first_step', 'yes');
                  jQuery(this).css('background-color', '#99CCFF');
                  jQuery(this).children('label').css('color', '#000000');

                  jQuery(this).attr('post_status', first_step.post_status);
               }
            });
         }
      }

      arr_contains = function (a, obj) {
         for (var i = 0; i < a.length; i++) {
            if (a[i] === obj) {
               return true;
            }
         }
         return false;
      }
      //-----------connection setting---------------

      var edit_conn_setting = function () {
         newconnection = false; // We can know what it isn't new the connection .
      };

      var action_after_load = function () {
         jsPlumb.bind("jsPlumbConnection", function (e) {
//            if ( !wfeditable ) {
//               jsPlumb.detach( e );
//               return;
//            }
            if (!chk_connection(e)) {
               jsPlumb.detach(e);
               return;
            }
            showConnectionDialog(e, connSet);
            wfConn = e;
            newconnection = true; // We can know what it is new the connection .
         });
      };

      chk_connection = function (conn) {
         c = jsPlumb.getConnections();
         var count = 0;
         for (var i = 0; i < c.length; i++) {

            if (conn.sourceId == c[i].sourceId && conn.targetId == c[i].targetId) {
               count++;
            }
         }
         if (count == 2)
            return false;
         return true;
      };

      /**
       * Trigger when save button is clicked on Connection Settings Modal
       *
       * @since 2.1
       */
      jQuery(document).on('click', '#connection-setting-save', function () {
         var pathColor = jQuery('input[name=path-opt]:checked').val();
         var post_status = jQuery('#step-status-select').val();
         if ('' === post_status) {
            alert(drag_drop_jsplumb_vars.postStatusRequired);
            return false;
         }

         jsPlumb.detach(wfConn);
         reconnect(pathColor, post_status);
         connSet = {"path": pathColor};

         edit_conn_setting(); //We can know what it isn't new the connection after saving .
         set_step_chaned_status(); // We can know what workflow graphic was changed.
         jQuery.modal.close();
      });

      var reconnect = function (pColor, post_status) {
         jsPlumb.connect({
            source: wfConn.sourceId,
            target: wfConn.targetId,
            parameters: {
               post_status: post_status
            },
            paintStyle: {
               lineWidth: 3,
               strokeStyle: pColor
            },
            connector: 'StateMachine'	// only one connection style.
         });
      };

      /**
       * When edit link is clicked from connection menu, show the connection settings modal
       *
       * @since 2.1
       */
      jQuery("#connEdit").click(function () {
         connSet = {"path": wfConn.paintStyleInUse.strokeStyle};
         jQuery("#connectionMenu").hide();
         showConnectionDialog(wfConn, connSet);
      });

      /**
       * When delete link is clicked from connection menu, delete the connection
       * between two steps ie connection between source & target
       *
       * @since 2.1
       */
      jQuery("#connDelete").click(function () {
         jsPlumb.detach(wfConn);
         set_step_chaned_status(); // We can know what workflow graphic was changed.
         jQuery("#connectionMenu").hide();
      });

      jQuery(document).on("click", "#connection-setting-cancel, .modalCloseImg", function () {
         if (newconnection)
            jsPlumb.detach(wfConn);
         edit_conn_setting(); //We can know what it isn't new the connection after cancel .
         jQuery.modal.close();
      });


      //-------------step setting-------------------

      set_selected_step = function (obj) {
         selectedStep = obj;
      }

      jQuery("#stepDelete").click(function () {
         var db_id = jQuery(selectedStep).attr("db-id");
         if (db_id == "nodefine") {
            jsPlumb.detachAllConnections(selectedStep);
            jQuery(selectedStep).remove();
            jQuery("#stepMenu").hide();
         } else {
            if (!confirm(drag_drop_jsplumb_vars.removeStep)) {
               jQuery("#stepMenu").hide();
               return;
            }
            jsPlumb.detachAllConnections(selectedStep);
            jQuery(selectedStep).remove();
            set_deleted_step(db_id);  //We save stepid after dedeting.
            jQuery("#stepMenu").hide();
            set_step_chaned_status(); // We can know what workflow graphic was changed.
         }
      });

      jQuery("#stepEdit a").click(function () {
         var g_step_id = jQuery.trim(jQuery(selectedStep).attr("id").escapeSpecialChars());
         var step_dbid = jQuery.trim(jQuery(selectedStep).attr("db-id").escapeSpecialChars());
         var process_name = jQuery(selectedStep).attr("process-name");
         step_edit_data = {
            action: 'load_step_info',
            process_name: process_name,
            step_gp_id: g_step_id,
            step_db_id: step_dbid,
            editable: wfeditable
         };
         jQuery.post(ajaxurl, step_edit_data, function (response) {
            jQuery("#step-info-update").html(response);
            jQuery("#step-info-update").owfmodal({
               containerCss: {
                  padding: 0,
                  width: 650
               },
               onShow: function (dlg) {
//				        jQuery(dlg.container).css('height', 'auto');
//				        jQuery(dlg.container).css("max-height","90%");
//				        jQuery(dlg.wrap).css('overflow', 'hidden'); // or try ;
                  jQuery.modal.update();
               }
            });
            var step_gpid = jQuery("#step_gpid-hi").val();
            var lbl = jQuery(document).find("#" + step_gpid + " label").html();
            jQuery("#step-name").val(lbl);
            var process_name = jQuery(document).find("#" + step_gpid).attr("process-name");
            jQuery("#step-setting-content").css("height", "420px");

            // select first step if first step = yes
            if (jQuery("#" + step_gpid).attr("first_step") == "yes") {
               jQuery('#first_step_check').prop('checked', true);

               // show first step post status dropdown if checkbox is selected
               jQuery('.first-step-post-status').removeClass('owf-hidden');
               jQuery('#first_step_post_status option[value="' + jQuery("#" + step_gpid).attr('post_status') + '"]').prop('selected', 'selected');
            }

            // attach select2 drop down to the available actors select box.
            jQuery("#show_available_actors").select2({
               theme: "classic",
               placeholder: "Select Assignee(s)",
               allowClear: true,
               closeOnSelect: false,
               formatSelection: formatAssigneeSelection //display whether the select option is a role, user or group
            });

            makeWhizzyWig("assignment-email-content", "all");
            makeWhizzyWig("reminder-email-content", "all");
         });
         jQuery("#stepMenu").hide();
         return true;
      });

      step_attached_data_del = function (stepId) {
         return stepId;
      }

      /*
       * step copy
       */
      jQuery("#stepCopy a").click(function () {
         copystep.step_id = jQuery(selectedStep).attr("id");
         copystep.step_dbid = jQuery(selectedStep).attr("db-id");
         copystep.hrf = jQuery(this).attr('alt');
         copystep.process_name = jQuery(selectedStep).attr("process-name");
         jQuery("#stepMenu").hide();
         return false;
      });

      /*
       * step paste
       */
      jQuery("#stepPaste a").click(function (event) {
         //if(ui.draggable.context.className.indexOf("w ui-draggable ui-droppable") >= 0) {return;} //No workflow info box droppable
         if (!copystep.step_id) {
            jQuery("#pasteMenu").hide();
            return false;
         }
         stepNum = 0;
         var getNum = get_step_id_num(stepNum);
         var lbl = copystep.process_name;
         get_step_lbl_inx(lbl);
         var Y = event.originalEvent.pageY - 120;
         var X = event.originalEvent.pageX - 180;

         if (copystep.step_dbid == "nodefine") {
            var p = {
               "fc_addid": "step" + getNum,
               "fc_label": obj_lbl,
               "fc_dbid": "nodefine",
               "fc_process": lbl,
               "fc_position": [Y + "px", X + "px"]
            };
            createStep(p, "new");
            set_step_chaned_status();
         } else {
            jQuery(".paste_loading").css({
               "top": (event.originalEvent.pageY - 20) + "px",
               "left": event.originalEvent.pageX + "px"
            });
            jQuery(".paste_loading").show().addClass("loading");
            copy_step_data = {
               action: 'copy_step',
               copy_step_id: copystep.step_dbid,
               security: jQuery('#owf_workflow_create_nonce').val()
            };
            jQuery.post(ajaxurl, copy_step_data, function (response) {
               jQuery(".paste_loading").removeClass("loading").hide();
               if (response.success) {
                  var p = {
                     "fc_addid": "step" + getNum,
                     "fc_label": obj_lbl,
                     "fc_dbid": response.data,
                     "fc_process": lbl,
                     "fc_position": [Y + "px", X + "px"]
                  };
                  createStep(p, "new");
                  set_step_chaned_status();
               }
            });
         }
         jQuery("#pasteMenu").hide();
      });

      /*
       * clear workflow area
       */
      jQuery("#delete-form").click(function () {
         if (!confirm(drag_drop_jsplumb_vars.clearAllSteps))
            return;
         jQuery(".fc_action .w").each(function () {
            if (jQuery(this).attr("db-id") != "nodefine") {
               set_deleted_step(jQuery(this).attr("db-id")); //We save stepid after dedeting.
            }
            jsPlumb.detachAllConnections(this);
            jQuery(this).remove();
            set_step_chaned_status(); // We can know what workflow graphic was changed.
         });

         return false;
      });
      //--------step data control ------------

      set_step_info_gp = function (gpId, savedata) {
         var stepInfo = {};
         var stepInfoStr = jQuery("#wf_step_data_hi").val();
         stepInfo = jQuery.parseJSON(stepInfoStr);
         if (savedata == "del") {
            stepInfo[gpId] = savedata;
         } else {
            delete stepInfo[gpId];
         }
         jQuery("#wf_step_data_hi").val(jQuery.toJSON(stepInfo));
      };

      //-----------------Menus----------------
      /**
       * Trigger when we do click on connection path between two step steps ie.
       * show connection menu when we right click on path
       *
       * @since 2.1
       */
      jsPlumb.bind('contextmenu', function (c, e) {
         wfConn = c;
         jQuery('#connectionMenu').show().css({
            'left': e.pageX + 'px',
            'top': e.pageY + 'px'
         });
         e.preventDefault();
         jQuery('#stepMenu, #pasteMenu').hide();
         return false;
      });

      /**
       * Trigger when we do right click on step ie review, publish or assignment
       * and shows the stpe menu
       *
       * @since 2.1
       */
      jQuery(document).on('contextmenu', '.w', function (e) {
         jQuery('#stepMenu').show().css({
            'left': e.pageX + 'px',
            'top': e.pageY + 'px'
         });
         set_selected_step(this);
         jQuery('#connectionMenu #pasteMenu').hide();
         return false;
      });

      jQuery(document).on("contextmenu", "#workflow-area", function (e) {
         if (jQuery("#connectionMenu").css("display") != "none")
            return false;
         if (copystep.step_id) {
            jQuery("#stepPaste").css("opacity", 1);
         } else {
            jQuery("#stepPaste").css("opacity", 0.2);
         }
         jQuery("#pasteMenu").show().css({"left": e.pageX + "px", "top": (e.pageY * 1 - 25) + "px"});
         jQuery("#connectionMenu, #stepMenu").hide();
         return false;
      });

      //---------------------------------------
      jsPlumb.bind('ready', function () {
         // chrome fix.
         document.onselectstart = function () {
            return false;
         };
         workflowGraphic.init();
         if (wf_structure_data) {
            _graphic_make(wf_structure_data);
            set_first_step(wf_structure_data);
         }
         action_after_load();
      });

      // Get post status when hover on connection
      jsPlumb.getConnections().forEach(function (connection) {

         connection.bind("mouseenter", function (conn) {
            var parameter = jsPlumb.select(conn).getParameter('post_status');
            var post_status = drag_drop_jsplumb_vars.postStatusLabel + ": " + parameter[0][0];

            conn.addOverlay(["Label", {
               label: post_status,
               location: 0.5,
               id: "connLabel",
               cssClass: "connection-info"
            }]);
         });

         connection.bind("mouseexit", function (conn) {
            conn.removeOverlay("connLabel");
         });
      });

   });
}(jQueryCgmp));

// change the display to show if the selected option is user, group or role.
function formatAssigneeSelection(val) {
   var assign_type = val.id.slice(0, 2);
   switch (assign_type) {
      case 'g@': // user groups
         assign_type = ' (group)';
         break;
      case 'u@': // user
         assign_type = ' (user)';
         break;
      case 'r@': // user roles
         assign_type = ' (role)';
         break;
   }
   return val.text + assign_type;
}