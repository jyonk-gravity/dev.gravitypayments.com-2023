/**
 * WordPress Dependencies
 */
import { __ } from "@wordpress/i18n";
import { Fragment, Component, createRef } from "@wordpress/element";
import {
  PanelBody,
  PanelRow,
  SelectControl,
  Dropdown,
  TextareaControl,
  Button,
  Spinner,
} from "@wordpress/components";
import { PostScheduleLabel, PostSchedule } from "@wordpress/editor";
import { withSelect, withDispatch, subscribe, select } from "@wordpress/data";
import { compose, withSafeTimeout } from "@wordpress/compose";
import { dateI18n, getSettings } from "@wordpress/date";
import apiFetch from "@wordpress/api-fetch";

/**
 * Internal dependencies
 */
import HelpImage from "../images/help.png";
import OWDueDatePicker from "./ow-due-date-picker";
import OWDueDateLabel from "./ow-due-date-label";
import TaskPriorities from "./ow-task-priority-select-control";
import WorkflowSelectControl from "./ow-workflow-select-control";
import TeamSelectControl from "./ow-team-select-control";
import PrePublishChecklist from "./ow-pre-publish-checklist";
import { getStepAssignees, OWPick, ow_console } from "../util";
import DOMPurify from "dompurify";
// react select
import Select from "react-select";

const settings = getSettings();

export class SubmitToWorkflow extends Component {
  constructor() {
    super(...arguments);

    this.submitToWorkflowPanelRef = createRef();

    // Set default first step dropdown state
    let firstSteps = [];
    firstSteps.push({ label: "", value: "" });

    this.state = {
      workflowButtonText: __("Submit to Workflow", "oasisworkflow"),
      assignActorLabel: __("Assign Actor(s)", "oasisworkflow"),
      publishDateLabel: __("Publish Date", "oasisworkflow"),
      dueDateLabel: __("Due Date", "oasisworkflow"),
      continueToSubmitText: __("Continue to Submit", "oasisworkflow"),
      displayPublishDate: "",
      displayDueDate: "",
      mandatoryComments: "",
      selectedWorkflow: "",
      firstSteps: firstSteps,
      selectedFirstStep: "",
      selectedPriority: "2normal",
      assignee: [],
      selectedAssignees: [],
      publishDate: dateI18n(settings.formats.datetimeAbbreviated, new Date()),
      comments: "",
      assignToAll: false,
      validationErrors: [],
      errorType: "",
      byPassWarning: false,
      hideForm: false,
      allTeams: [],
      selectedTeam: "",
      articleChecklist: [],
      selectedChecklist: [],
      redirectingLoader: "hide",
      stepSpinner: "hide",
      assigneeSpinner: "hide",
      submitSpinner: "hide",
      isSaving: false,
      signoffQueue: [],
      submitButtonDisable: true,
    };

    this.unsubscribe = null;
  }

  componentDidMount() {
    let customWorkflowTerminology =
      this.props.owSettings.terminology_settings
        .oasiswf_custom_workflow_terminology;
    let workflowSettings = this.props.owSettings.workflow_settings;

    if (customWorkflowTerminology) {
      let workflowButtonText = customWorkflowTerminology.submitToWorkflowText;
      let assignActorLabel = customWorkflowTerminology.assignActorsText;
      let publishDateLabel = customWorkflowTerminology.publishDateText;
      let dueDateLabel = customWorkflowTerminology.dueDateText;
      let continueToSubmitText = customWorkflowTerminology.continueToSubmitText;
      this.setState({
        workflowButtonText,
        assignActorLabel,
        publishDateLabel,
        dueDateLabel,
        continueToSubmitText,
      });
    }

    if (workflowSettings) {
      let displayPublishDate = workflowSettings.oasiswf_publish_date_setting;
      let displayDueDate = workflowSettings.oasiswf_default_due_days;
      let mandatoryComments = workflowSettings.oasiswf_comments_setting;

      // set the default due date by using the workflow settings
      let dueDate = new Date();
      if (displayDueDate !== "") {
        dueDate.setDate(dueDate.getDate() + parseInt(displayDueDate));
      }
      this.props.setDueDate({ dueDate: dueDate });

      this.setState({
        displayPublishDate,
        displayDueDate,
        mandatoryComments,
      });
    }

    // Always check if post are saving or autosaving by wp.data.subscribe
    // https://stackoverflow.com/questions/52301472/using-wp-data-subscribe-properly
    // https://redux.js.org/api/store/#subscribelistener
    this.unsubscribe = subscribe(() => {
      this.checkPostSaving();
    });
  }

  checkPostSaving = () => {
    const isSaving = select("core/editor").isSavingPost();
    this.setState({ isSaving });
    ow_console("isSaving", isSaving);
    ow_console("stateIsSaving", this.state.isSaving);
  };

  componentWillUnmount() {
    // Unsubscribe when the component is unmounted
    if (this.unsubscribe) {
      this.unsubscribe();
    }
  }

  /**
   * Get First Step of the selected workflow
   * @param {*} workflowId
   */
  getFirstStep(workflowId) {
    let postId = this.props.postId;

    if (workflowId === "") {
      this.setState({
        submitSpinner: "hide",
        submitButtonDisable: true,
      });
      console.error("workflowId is empty");
      return;
    }

    // Set selected workflow
    this.setState({
      selectedWorkflow: workflowId,
      validationErrors: [],
      allTeams: [],
      selectedTeam: "",
      stepSpinner: "show",
      submitSpinner: "show",
      submitButtonDisable: true,
      assigneeSpinner: "show",
    });

    apiFetch({
      path:
        "/oasis-workflow/v1/workflows/submit/firstStep/workflowId=" +
        workflowId +
        "/postId=" +
        postId,
      method: "GET",
    }).then(
      (stepdata) => {
        let firstStepId = stepdata.step_id;
        let firstStepLabel = stepdata.step_label;
        let firstSteps = [];
        let availableAssignees = [];
        let assignToAll = stepdata.assign_to_all === 1 ? true : false;
        let errors = [];
        let customData = stepdata.custom_data;
        let allChecklist = [];
        firstSteps.push({ label: firstStepLabel, value: firstStepId });

        // Set team dropdown
        let allTeams = [];
        let teams = stepdata.teams;

        if (teams !== "") {
          let teamData = teams.map((team) => OWPick(team, ["ID", "name"]));
          if (teamData.length > 1) {
            // we have more than one team, so add a empty option value
            allTeams.push({ label: "", value: "" });
          }
          teamData.map((team) => {
            allTeams.push({ label: team.name, value: team.ID });
          });

          if (allTeams.length == 1) {
            // only one team, then autoselect and call onChange
            this.handleTeamChange(allTeams[0]["value"], null, firstStepId);
          }
        }

        // set the default due date by using the workflow settings
        let dueDate = new Date();
        if (stepdata.due_days) {
          dueDate.setDate(dueDate.getDate() + parseInt(stepdata.due_days));
        }
        this.props.setDueDate({ dueDate: dueDate });

        // If not set team than display step assignee
        if (teams == "") {
          let assignees = stepdata.users;

          // Display Validation Message if no user found for the step
          if (assignees.length === 0) {
            errors.push(
              __("No users found to assign the task.", "oasisworkflow")
            );
            this.setState({
              firstSteps: firstSteps,
              selectedFirstStep: firstStepId,
              validationErrors: errors,
              assignee: [],
            });

            // scroll to the top, so that the user can see the error
            this.submitToWorkflowPanelRef.current.scrollIntoView();

            return;
          }

          // Set and Get Assignees from the util function
          let stepAssignees = getStepAssignees({
            assignees: assignees,
            assignToAll: assignToAll,
          });
          availableAssignees = stepAssignees.availableAssignees;

          this.setState({
            selectedAssignees: stepAssignees.selectedAssignees,
          });
        }

        // Get checklist
        if (customData.length !== 0) {
          let checklistData = customData.map((checklist) =>
            OWPick(checklist, ["condition", "value"])
          );
          checklistData.map((checklist) => {
            allChecklist.push({
              label: checklist.condition,
              value: checklist.value,
            });
          });
        }

        this.setState({
          firstSteps: firstSteps,
          selectedFirstStep: firstStepId,
          assignee: availableAssignees,
          assignToAll: assignToAll,
          allTeams: allTeams,
          articleChecklist: allChecklist,
          stepSpinner: "hide",
          assigneeSpinner: "hide",
        });

        // delay half secon to setState
        setTimeout(() => {
          this.setState({
            submitSpinner: "hide",
            submitButtonDisable: false,
          });
        }, 500);

        return stepdata;
      },
      (err) => {
        console.log(err);
        return err;
      }
    );
  }

  /**
   * handle priority change
   * @param {*} selectedPriority
   */
  handleOnPriorityChange(selectedPriority) {
    this.setState({
      selectedPriority,
    });
  }

  /**
   * Handle Team Change
   * @param {*} selectedTeam
   * @param {*} firstStepId
   */
  handleTeamChange(selectedTeam, event, firstStepId = 0) {
    let postId = this.props.postId;

    let stepId = firstStepId;
    if (firstStepId == 0) {
      stepId = this.state.selectedFirstStep;
    }

    this.setState({
      selectedTeam,
      assigneeSpinner: "show",
    });

    apiFetch({
      path:
        "/oasis-workflow/v1/workflows/teams/members/teamId=" +
        selectedTeam +
        "/postId=" +
        postId +
        "/stepId=" +
        stepId,
      method: "GET",
    }).then(
      (teamdata) => {
        let errors = [];
        let assignees = teamdata.users;
        // Display Validation Message if no user found for the selected team
        if (assignees.length === 0) {
          errors.push(teamdata.errorMessage);
          this.setState({
            validationErrors: errors,
            assignee: [],
          });
          // scroll to the top, so that the user can see the error
          this.submitToWorkflowPanelRef.current.scrollIntoView();
          return;
        }

        // Set and Get Assignees from the store function
        let stepAssignees = getStepAssignees({
          assignees: assignees,
          assignToAll: this.state.assignToAll,
        });

        this.setState({
          assignee: stepAssignees.availableAssignees,
          selectedAssignees: stepAssignees.selectedAssignees,
          assigneeSpinner: "hide",
        });
      },
      (err) => {
        console.log(err);
        return err;
      }
    );
  }

  /**
   * Handle selected pre publish checklist
   * @param {*} checklist
   */
  selectPrePublishChecklist(checklist) {
    // Get current state
    const options = this.state.selectedChecklist;
    let index;

    // check if the check box is checked or unchecked
    if (checklist.target.checked) {
      options.push(checklist.target.value);
    } else {
      index = options.indexOf(checklist.target.value);
      options.splice(index, 1);
    }
    this.setState({
      selectedChecklist: options,
    });
  }

  /**
   * validate submit to workflow
   * @param {Object} data
   */
  async validateSubmitToWorkflow(data) {
    const errors = [];
    let current_date = new Date();
    current_date = moment(current_date).format("YYYY-MM-DD");
    let due_date = moment(data.due_date).format("YYYY-MM-DD");

    let selected_workflow = this.state.selectedWorkflow;
    let selected_step = this.state.selectedFirstStep;

    if (selected_workflow === "" || selected_workflow === 0) {
      errors.push(__("Please select a workflow.", "oasisworkflow"));
    }

    if (selected_step === "" || selected_step === 0) {
      errors.push(__("Please select a step.", "oasisworkflow"));
    }

    if (data.due_date === "") {
      errors.push(__("Please enter a due date.", "oasisworkflow"));
    }

    if (
      data.due_date !== "" &&
      moment(current_date).isAfter(due_date) == true
    ) {
      errors.push(
        __("Due date must be greater than the current date.", "oasisworkflow")
      );
    }

    if (data.assignees.length === 0 && !this.state.assignToAll) {
      errors.push(__("No assigned actor(s).", "oasisworkflow"));
    }

    if (data.comments === "" && this.state.mandatoryComments === "mandatory") {
      errors.push(__("Please enter comments.", "oasisworkflow"));
    }

    if (typeof window.acf !== "undefined") {
      // Check if ACF field valid or not if they exists
      let ACFCheck = await window.workflowSubmitWithACF();
      if (ACFCheck === "invalid") {
        errors.push(__("Please enter ACF required fields.", "oasisworkflow"));
      }
    }

    return errors;
  }

  /**
   * The user wants to continue to sign off, so we need to bypass the warnings
   * @param {*} event
   */
  handleContinueToSubmit(event) {
    // call handleWorkflowComplete as callback of setState, so that it's called after the state is set
    this.setState(
      {
        byPassWarning: true,
      },
      () => {
        this.handleSubmitToWorkflow();
      }
    );
  }

  /**
   * Check is pos still saving or not on every 100 milliseconds if not saving then call handleWorkflowCompleteCB
   */
  processSignoffQueue = async () => {
    // If there are no more requests in the queue, still wait for the post to stop saving.
    if (this.state.signoffQueue.length === 0) {
      ow_console("Queue is empty. Waiting for post to stop saving...");

      // Wait until the post is not saving
      const waitForPostToSave = () => {
        return new Promise((resolve) => {
          const interval = setInterval(() => {
            ow_console("check isSaving", this.state.isSaving);
            if (!this.state.isSaving) {
              clearInterval(interval);
              resolve();
            }
          }, 100); // Check every 100 milliseconds
        });
      };

      await waitForPostToSave();

      // Once the post is done saving, return control to the caller function
      ow_console("Post is no longer saving. Returning control...");
      return;
    }

    const currentRequest = this.state.signoffQueue[0];

    // Wait until the post is not saving
    const waitForPostToSave = () => {
      return new Promise((resolve) => {
        const interval = setInterval(() => {
          if (!this.state.isSaving) {
            ow_console("--- isSaving ---", this.state.isSaving);
            clearInterval(interval);
            resolve();
          }
        }, 100); // Check every 100 milliseconds
      });
    };

    await waitForPostToSave();

    // Proceed to handle workflow completion
    await this.handleSubmitToWorkflowCB(currentRequest);

    // Remove the processed request from the queue
    this.setState(
      (prevState) => ({
        signoffQueue: prevState.signoffQueue.slice(1),
      }),
      () => {
        // Process the next request in the queue
        this.processSignoffQueue();
      }
    );
  };

  async handleSubmitToWorkflow(event) {
    event.preventDefault();

    ow_console("handleSubmitToWorkflow trigger");

    // hide errors each click
    this.setState({
      validationErrors: [],
    });

    // Set byPassWarning to true and add the current click event to the signoff queue
    this.setState(
      (prevState) => ({
        signoffQueue: [...prevState.signoffQueue, event], // Queue the click event
      }),
      () => {
        this.processSignoffQueue(); // Start processing the queue
      }
    );
  }

  // Submit to workflow - form submit
  async handleSubmitToWorkflowCB(event) {
    // event.preventDefault();
    this.setState({
      submitSpinner: "show",
      submitButtonDisable: true,
    });

    // Ensure the post has required fields before saving
    const title = this.props.getEditedPostAttribute("title");
    const content = this.props.getEditedPostAttribute("content");

    if (!title && !content) {
      this.setState({
        validationErrors: [
          __("Please enter a title or content.", "oasisworkflow"),
        ],
        submitSpinner: "hide",
        submitButtonDisable: false,
        errorType: "",
      });
      console.error("title and content are empty");
      // scroll to the top, so that the user can see the error
      this.submitToWorkflowPanelRef.current.scrollIntoView();
      return;
    }

    this.setState({
      validationErrors: [],
    });

    let form_data = {
      post_id: this.props.postId,
      step_id: this.state.selectedFirstStep,
      priority: this.state.selectedPriority,
      assignees: this.state.selectedAssignees.map((assignee) => assignee.value),
      due_date: this.props.dueDate,
      publish_date: this.props.publishDate,
      comments: this.state.comments,
      team_id: this.state.selectedTeam,
      assign_to_all: this.state.assignToAll,
      pre_publish_checklist: this.state.selectedChecklist,
      by_pass_warning: this.state.byPassWarning,
    };

    try {
      // save the post
      await this.props.onSave();
      ow_console("onSave Done!", this.state.isSaving);
    } catch (error) {
      if (error.code === "rest_forbidden_context") {
        console.error("Permission error:", error.message);
        // Handle the permission error, e.g., show a user-friendly message
      } else {
        console.error("An unexpected error occurred:", error);
      }
    }

    const errors = await this.validateSubmitToWorkflow(form_data);

    if (errors.length > 0) {
      this.setState({
        validationErrors: errors,
        submitSpinner: "hide",
        submitButtonDisable: false,
        errorType: "",
      });
      // scroll to the top, so that the user can see the error
      this.submitToWorkflowPanelRef.current.scrollIntoView();
      return;
    }

    this.setState({
      validationErrors: [],
    });

    this.setState({ isSaving: true }, async () => {
      await this.processSignoffQueue();
      ow_console("invokeSubmitToWorkflowAPI isSaving", this.state.isSaving);
      if (!this.state.isSaving) {
        await this.invokeSubmitToWorkflowAPI(form_data);
      }
    });
  }

  async invokeSubmitToWorkflowAPI(form_data) {
    ow_console("inside invokeSubmitToWorkflowAPI");
    // wait 100ms to proced to next steps.
    await new Promise((resolve) => setTimeout(resolve, 100));

    ow_console("before submit api called");
    await apiFetch({
      path: "/oasis-workflow/v1/workflows/submit/",
      method: "POST",
      data: form_data,
    }).then(
      (submitResponse) => {
        if (submitResponse.success_response == false) {
          this.setState({
            validationErrors: submitResponse.validation_error,
            errorType: submitResponse.error_type,
            submitSpinner: "hide",
            submitButtonDisable: false,
          });
          // scroll to the top, so that the user can see the error
          this.submitToWorkflowPanelRef.current.scrollIntoView();
        } else {
          this.setState({
            hideForm: true,
            redirectingLoader: "show",
          });
          // Handle redirect if owf_redirect_after_workflow_submit hook is implemented
          if (submitResponse.redirect_link !== "") {
            window.location.href = DOMPurify.sanitize(
              submitResponse.redirect_link
            );
          } else {
            this.props.handleResponse(submitResponse);
          }
        }
      },
      (err) => {
        console.log(err);
        return err;
      }
    );
  }

  render() {
    const {
      isSaving,
      isCurrentPostPublished,
      isCurrentPostScheduled,
      postStatus,
      isPostInWorkflow,
      postMeta,
    } = this.props;
    const {
      validationErrors,
      errorType,
      hideForm,
      workflowButtonText,
      continueToSubmitText,
      assignActorLabel,
      publishDateLabel,
      dueDateLabel,
      displayPublishDate,
      displayDueDate,
      articleChecklist,
      redirectingLoader,
      stepSpinner,
      assigneeSpinner,
      submitSpinner,
      submitButtonDisable,
    } = this.state;

    if (hideForm && redirectingLoader === "show") {
      return (
        <div>
          <PanelBody>{__("redirecting...", "oasisworkflow")}</PanelBody>
        </div>
      );
    }

    if (
      (postMeta && postMeta._oasis_is_in_workflow == "1") ||
      isPostInWorkflow || // post is in another workflow
      hideForm ||
      isCurrentPostPublished || // a new post is published
      isCurrentPostScheduled || // a new post is scheduled
      postStatus == "owf_scheduledrev" || // a revision post is scheduled, so do not show submit to workflow
      postStatus == "usedrev"
    ) {
      // a revision post is published, so do not show submit to workflow
      return "";
    }

    const style = {
      control: (base) => ({
        ...base,
        borderColor: "black",
        "&:hover": { borderColor: "black" }, // border style on hover
      }),
    };

    return (
      <PanelBody
        ref={this.submitToWorkflowPanelRef}
        initialOpen={true}
        title={workflowButtonText}
      >
        <form className="reusable-block-edit-panel">
          {validationErrors.length !== 0 ? (
            <div
              id="owf-error-message"
              className="notice notice-error is-dismissible"
            >
              {validationErrors.map((error) => (
                <p key={error}>{error}</p>
              ))}
              {errorType == "warning" ? (
                <p>
                  <Button
                    variant="secondary"
                    focus="true"
                    onClick={this.handleContinueToSubmit.bind(this)}
                  >
                    {continueToSubmitText}
                  </Button>
                </p>
              ) : (
                ""
              )}
            </div>
          ) : (
            ""
          )}
          <WorkflowSelectControl
            value={this.state.selectedWorkflow}
            onChange={this.getFirstStep.bind(this)}
          />
          <div className="owf-spinner">
            {stepSpinner == "show" ? <Spinner /> : ""}
          </div>
          <label>
            {__("Step", "oasisworkflow") + ": "}
            <a
              href="#"
              title={__(
                "Your action will push the Post/Article to the below listed next step.",
                "oasisworkflow"
              )}
              className="tooltip"
            >
              <span title="">
                <img src={HelpImage} className="help-icon" />
              </span>
            </a>
          </label>
          <SelectControl
            value={this.state.selectedFirstStep}
            options={this.state.firstSteps}
            onChange={(selectedFirstStep) =>
              this.setState({ selectedFirstStep })
            }
          />
          <TaskPriorities
            value={this.state.selectedPriority}
            onChange={this.handleOnPriorityChange.bind(this)}
          />
          {this.state.allTeams.length !== 0 ? (
            <TeamSelectControl
              value={this.state.selectedTeam}
              options={this.state.allTeams}
              onChange={this.handleTeamChange.bind(this)}
            />
          ) : (
            ""
          )}
          <div className="owf-spinner">
            {assigneeSpinner == "show" && this.state.assignToAll == false ? (
              <Spinner />
            ) : (
              ""
            )}
          </div>
          {!this.state.assignToAll && this.state.selectedWorkflow !== "" ? (
            <div>
              <label htmlFor={"assignActor"}>{assignActorLabel + ":"}</label>
              <Select
                inputId="assignActor"
                styles={style}
                className="ow-select2"
                value={this.state.selectedAssignees}
                options={this.state.assignee}
                onChange={(selectedAssignees) =>
                  this.setState({ selectedAssignees })
                }
                isMulti="true"
              />
            </div>
          ) : (
            ""
          )}

          {articleChecklist.length !== 0 ? (
            <PrePublishChecklist
              checklist={articleChecklist}
              onChange={this.selectPrePublishChecklist.bind(this)}
            />
          ) : (
            ""
          )}

          {displayPublishDate == "" ? (
            <PanelRow className="edit-post-post-schedule">
              <label>{publishDateLabel + ":"} </label>
              <Dropdown
                popoverProps={{ placement: "bottom-end" }}
                contentClassName="edit-post-post-schedule__dialog"
                renderToggle={({ onToggle, isOpen }) => (
                  <Fragment>
                    <Button
                      type="button"
                      onClick={onToggle}
                      aria-expanded={isOpen}
                      aria-live="polite"
                      variant="link"
                    >
                      <PostScheduleLabel />
                    </Button>
                  </Fragment>
                )}
                renderContent={() => <PostSchedule />}
              />
            </PanelRow>
          ) : (
            ""
          )}

          {displayDueDate !== "" ? (
            <PanelRow className="edit-post-post-schedule">
              <label>{dueDateLabel + ":"} </label>
              <Dropdown
                popoverProps={{ placement: "bottom-end" }}
                contentClassName="edit-post-post-schedule__dialog owduedatepicker-dropdown"
                renderToggle={({ onToggle, isOpen }) => (
                  <Fragment>
                    <Button
                      type="button"
                      onClick={onToggle}
                      aria-expanded={isOpen}
                      aria-live="polite"
                      variant="link"
                    >
                      <OWDueDateLabel />
                    </Button>
                  </Fragment>
                )}
                renderContent={() => <OWDueDatePicker />}
              />
            </PanelRow>
          ) : (
            ""
          )}
          <PanelRow>
            <label>
              {__("Comments", "oasisworkflow") + ": "}
              <a
                href="#"
                title={__(
                  "The comments will be visible throughout the workflow.",
                  "oasisworkflow"
                )}
                className="tooltip"
              >
                <span title="">
                  <img src={HelpImage} className="help-icon" />
                </span>
              </a>
            </label>
          </PanelRow>
          <PanelRow className="panel-without-label">
            <TextareaControl
              value={this.state.comments}
              onChange={(comments) => this.setState({ comments })}
            />
          </PanelRow>
          <PanelRow>
            <Button
              variant="primary"
              isBusy={isSaving}
              focus="true"
              disabled={submitButtonDisable}
              onClick={this.handleSubmitToWorkflow.bind(this)}
            >
              {workflowButtonText}
            </Button>
            <div className="owf-spinner">
              {submitSpinner == "show" ? <Spinner /> : ""}
            </div>
          </PanelRow>
        </form>
      </PanelBody>
    );
  }
}

export default compose([
  withSelect((select) => {
    const {
      getCurrentPostId,
      getEditedPostAttribute,
      getPostSaveError,
      isCurrentPostPublished,
      isCurrentPostScheduled,
      getCurrentPost,
    } = select("core/editor");
    const { getDueDate, getOWSettings, getPostInWorkflow, getassignees } =
      select("plugin/oasis-workflow");
    const { status, type } = getCurrentPost();
    return {
      postId: getCurrentPostId(),
      postId: getCurrentPostId(),
      getEditedPostAttribute,
      isCurrentPostPublished: isCurrentPostPublished(),
      isCurrentPostScheduled: isCurrentPostScheduled(),
      publishDate: getEditedPostAttribute("date"),
      dueDate: getDueDate(),
      getPostSaveError,
      owSettings: getOWSettings(),
      postStatus: status,
      isPostInWorkflow: getPostInWorkflow(),
    };
  }),
  withDispatch((dispatch) => ({
    onSave: dispatch("core/editor").savePost,
    autosave: dispatch("core/editor").autosave,
    setDueDate: dispatch("plugin/oasis-workflow").setDueDate,
  })),
  withSafeTimeout,
])(SubmitToWorkflow);
