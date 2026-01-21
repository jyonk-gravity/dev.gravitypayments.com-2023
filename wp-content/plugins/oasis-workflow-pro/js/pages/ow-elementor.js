var jQueryOWElementor = jQuery.noConflict();

(function (jQuery) {
  window.onload = (event) => {
    let elementor_top_bar = jQuery("#elementor-editor-wrapper-v2");

    // Function to handle cloning and adding new menu items
    function addMenuItem(
      targetElm,
      iconClass,
      text,
      id,
      extraClasses = [],
      isClaim = false
    ) {
      if (
        "submit-workflow" !== id &&
        !elementor_top_bar
          .find(".MuiButtonGroup-firstButton")
          .hasClass("owf-elementor-hidden")
      ) {
        elementor_top_bar
          .find(".MuiButtonGroup-firstButton")
          .addClass("owf-elementor-hidden");
      }

      let hrElm = targetElm.next("hr").clone();
      let targetClone = targetElm.clone();

      targetClone
        .find(".MuiListItemIcon-root")
        .html(`<i class="elementor-icon ${iconClass}" aria-hidden="true"></i>`);
      targetClone
        .find(".MuiListItemText-root")
        .find("span")
        .addClass("elementor-title")
        .text(text);

      if (isClaim) {
        targetClone
          .find(".MuiListItemText-root")
          .find("span")
          .addClass("elementor-title")
          .addClass("claim")
          .attr("id", `elementor-panel-title-${id}`)
          .prop("actionid", owf_elementor_vars.current_history_id)
          .prop("userid", owf_elementor_vars.current_user_id);
      }

      targetClone.addClass("elementor-panel-footer-sub-menu-item");

      // Add classes from the extraClasses array
      if (extraClasses.length > 0) {
        extraClasses.forEach((className) => {
          targetClone.addClass(className);
        });
      }

      targetClone.attr("id", `elementor-panel-footer-sub-menu-item-${id}`);

      targetElm.next("hr").after(targetClone).after(hrElm);
    }

    if (elementor_top_bar.length === 0) {
      //If post is in workflow and any user like editor edit the post
      if (elementor_is_in_workflow === "true") {
        jQuery("#elementor-panel-saver-button-publish").addClass(
          "owf-elementor-hidden"
        );
      }

      // Display submit to workflow button
      if (owf_process === "submit") {
        jQuery("#elementor-panel-saver-button-publish").addClass(
          "owf-elementor-hidden"
        );
        jQuery("#elementor-panel-footer-sub-menu-item-save-template").after(
          '<div id="elementor-panel-footer-sub-menu-item-submit-workflow" class="elementor-panel-footer-sub-menu-item"><input type="hidden" id="hi_is_team" name="hi_is_team" /><i class="elementor-icon eicon-folder" aria-hidden="true"></i><span class="elementor-title">' +
            owf_submit_workflow_vars.submitToWorkflowButton +
            "</span></div>"
        );
      }

      // Display sign-off button
      if (
        typeof owf_reassign !== "undefined" &&
        owf_reassign == "reassign-task"
      ) {
        if (
          !jQuery("#elementor-panel-saver-button-publish").hasClass(
            "owf-elementor-hidden"
          )
        ) {
          jQuery("#elementor-panel-saver-button-publish").addClass(
            "owf-elementor-hidden"
          );
        }

        jQuery("#elementor-panel-footer-sub-menu-item-save-template").after(
          '<div id="elementor-panel-footer-sub-menu-item-reassign-workflow" class="elementor-panel-footer-sub-menu-item ow-elmentor-tool reassign"><i class="elementor-icon eicon-preferences" aria-hidden="true"></i><span class="elementor-title">' +
            owf_submit_step_vars.reassign +
            "</span></div>"
        );
      }

      if (typeof owf_abort !== "undefined" && owf_abort == "abort-workflow") {
        if (
          !jQuery("#elementor-panel-saver-button-publish").hasClass(
            "owf-elementor-hidden"
          )
        ) {
          jQuery("#elementor-panel-saver-button-publish").addClass(
            "owf-elementor-hidden"
          );
        }
        jQuery("#elementor-panel-footer-sub-menu-item-save-template").after(
          '<div id="elementor-panel-footer-sub-menu-item-abort-workflow" class="elementor-panel-footer-sub-menu-item ow-elmentor-tool abort_workflow"><i class="elementor-icon eicon-trash-o" aria-hidden="true"></i><span class="elementor-title">' +
            owf_submit_step_vars.abortButton +
            "</span></div>"
        );
      }

      if (typeof owf_claim !== "undefined" && owf_claim == "claim-task") {
        if (
          !jQuery("#elementor-panel-saver-button-publish").hasClass(
            "owf-elementor-hidden"
          )
        ) {
          jQuery("#elementor-panel-saver-button-publish").addClass(
            "owf-elementor-hidden"
          );
        }

        jQuery("#elementor-panel-footer-sub-menu-item-save-template").after(
          '<div id="elementor-panel-footer-sub-menu-item-claim-workflow" actionid="' +
            owf_elementor_vars.current_history_id +
            '" userid="' +
            owf_elementor_vars.current_user_id +
            '" class="elementor-panel-footer-sub-menu-item ow-elmentor-tool claim_workflow claim"><i class="elementor-icon eicon-sign-out" aria-hidden="true"></i><span class="elementor-title">' +
            owf_submit_step_vars.claimButton +
            "</span></div>"
        );
      }

      if (owf_process === "sign-off") {
        if (
          !jQuery("#elementor-panel-saver-button-publish").hasClass(
            "owf-elementor-hidden"
          )
        ) {
          jQuery("#elementor-panel-saver-button-publish").addClass(
            "owf-elementor-hidden"
          );
        }
        jQuery("#elementor-panel-footer-sub-menu-item-save-template").after(
          '<div id="elementor-panel-footer-sub-menu-item-signoff-workflow" class="elementor-panel-footer-sub-menu-item"><i class="elementor-icon  eicon-sign-out" aria-hidden="true"></i><span class="elementor-title">' +
            owf_submit_step_vars.signOffButton +
            "</span></div>"
        );
      }
    } else {
      console.log("elm owf_elementor_vars", owf_elementor_vars);

      //If post is in workflow and any user like editor edit the post
      if (elementor_is_in_workflow === "true") {
        elementor_top_bar
          .find(".MuiButtonGroup-firstButton")
          .addClass("owf-elementor-hidden");
        console.log("loadedddddddd!!!");
      }

      // Display Oasis actions button in elementor top bar when it's clicked.
      elementor_top_bar
        .find(".MuiButtonGroup-lastButton")
        .on("click", function () {
          setTimeout(function () {
            let elementor_top_dropdown = jQuery("#document-save-options");
            let menuItemList = elementor_top_dropdown.find(".MuiMenu-list");
            let targetElm = menuItemList.find(".MuiButtonBase-root").eq(1);

            // Display submit to workflow button
            if (
              typeof owf_process !== "undefined" &&
              owf_process === "submit" &&
              typeof owf_submit_workflow_vars !== "undefined"
            ) {
              console.log("owf_process", owf_process);
              addMenuItem(
                targetElm,
                "eicon-folder",
                owf_submit_workflow_vars.submitToWorkflowButton,
                "submit-workflow"
              );
            }

            if (
              typeof owf_reassign !== "undefined" &&
              owf_reassign == "reassign-task"
            ) {
              console.log("owf_reassign", owf_reassign);
              addMenuItem(
                targetElm,
                "eicon-preferences",
                owf_submit_step_vars.reassign,
                "reassign-workflow",
                ["ow-elmentor-tool", "reassign"]
              );
            }

            if (typeof owf_claim !== "undefined" && owf_claim == "claim-task") {
              console.log("owf_claim", owf_claim);
              console.log("----------ddddd---------");
              addMenuItem(
                targetElm,
                "eicon-sign-out",
                owf_submit_step_vars.claimButton,
                "claim-task",
                ["ow-elmentor-tool", "claim_task"],
                true
              );
            }

            if (
              typeof owf_abort !== "undefined" &&
              owf_abort == "abort-workflow"
            ) {
              console.log("owf_abort", owf_abort);
              addMenuItem(
                targetElm,
                "eicon-trash-o",
                owf_submit_step_vars.abortButton,
                "abort-workflow",
                ["ow-elmentor-tool", "abort_workflow"]
              );
            }

            if (
              typeof owf_process !== "undefined" &&
              owf_process === "sign-off"
            ) {
              console.log("owf_process", owf_process);
              addMenuItem(
                targetElm,
                "eicon-sign-out",
                owf_submit_step_vars.signOffButton,
                "signoff-workflow"
              );
            }
          }, 50);
        });
    }
  };
})(jQueryOWElementor);
