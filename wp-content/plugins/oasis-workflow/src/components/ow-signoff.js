/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n';
import { Fragment, Component, createRef } from '@wordpress/element';
import { compose } from '@wordpress/compose';
import { PanelBody, PanelRow, SelectControl, Dropdown, TextareaControl, CheckboxControl, Button, Spinner } from '@wordpress/components';
import { withSelect, withDispatch, subscribe, select } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import DOMPurify from "dompurify";
import { OWPick } from "../util";

/**
 * Internal dependencies
 */
import TaskPriorities from "./ow-task-priority-select-control";
import OWDueDatePicker from "./ow-due-date-picker";
import OWDueDateLabel from "./ow-due-date-label";
import SignoffLastStep from "./ow-signoff-last-step";
import { getActionHistoryIdFromURL, getTaskUserFromURL, getSignOffActions, getStepAssignees } from "../util";

export class Signoff extends Component {
    constructor() {
        super(...arguments);

        this.signoffPanelRef = createRef();

        this.state = {
            signoffButtonText: __("Sign Off", "oasisworkflow"),
            assignActorLabel: __("Assign Actor(s)", "oasisworkflow"),
            dueDateLabel: __("Due Date", "oasisworkflow"),
            displayDueDate: "",
            actions: [],
            selectedAction: "",
            signoffSteps: [{ label: "", value: "" }],
            selectedStep: "",
            selectedPriority: "2normal",
            assignee: [],
            selectedAssignees: [],
            assignToAll: false,
            actionHistoryId: getActionHistoryIdFromURL(),
            taskUser: getTaskUserFromURL(),
            comments: "",
            isLastStep: false,
            lastStepDecision: "success",
            validationErrors: [],
            redirectingLoader: "hide",
            stepSpinner: "hide",
            assigneeSpinner: "hide",
            submitSpinner: "hide",
            isApiInProgress: false,
            isSaving: false,
            signoffQueue: [],
            isMakeAjaxRequest: false,
            submitButtonDisable: false
        };

        this.unsubscribe = null;
    }

    componentDidMount() {
        let customWorkflowTerminology = this.props.owSettings.terminology_settings.oasiswf_custom_workflow_terminology;
        let workflowSettings = this.props.owSettings.workflow_settings;

        if (customWorkflowTerminology) {
            let signoffButtonText = customWorkflowTerminology.signOffText;
            let assignActorLabel = customWorkflowTerminology.assignActorsText;
            let dueDateLabel = customWorkflowTerminology.dueDateText;
            this.setState({
                signoffButtonText,
                assignActorLabel,
                dueDateLabel
            });
        }

        if (workflowSettings) {
            let displayDueDate = workflowSettings.oasiswf_default_due_days;

            // set the default due date by using the workflow settings
            let dueDate = new Date();
            if (displayDueDate !== "") {
                dueDate.setDate(dueDate.getDate() + parseInt(displayDueDate));
            }
            this.props.setDueDate({ dueDate: dueDate });

            this.setState({
                displayDueDate
            });
        }

        // fetch step action details - essentially, show review actions or assignment/publish actions
        apiFetch({
            path: "/oasis-workflow/v1/workflows/signoff/stepActions/actionHistoryId=" + this.state.actionHistoryId,
            method: "GET"
        }).then(
            (step_decision) => {
                let process = step_decision.process;
                this.setState({
                    actions: getSignOffActions(process)
                });
            },
            (err) => {
                console.log(err);
                return err;
            }
        );

        // Always check if post are saving or autosaving by wp.data.subscribe
        // https://stackoverflow.com/questions/52301472/using-wp-data-subscribe-properly
        // https://redux.js.org/api/store/#subscribelistener
        this.unsubscribe = subscribe(() => {
            this.checkPostSaving();
        });
    }

    checkPostSaving = () => {
        const isSaving = select('core/editor').isSavingPost();
        this.setState({ isSaving });
    };

    componentWillUnmount() {
        // Unsubscribe when the component is unmounted
        if (this.unsubscribe) {
            this.unsubscribe();
        }
    }

    getSignoffSteps(stepDecision) {
        let postId = this.props.postId;
        let decision = "success";

        // Set selected stepDecision
        this.setState({
            selectedAction: stepDecision,
            stepSpinner: "show"
        });

        if ("complete" === stepDecision) {
            decision = "success";
        }
        if ("unable" === stepDecision) {
            decision = "failure";
        }
        // get next steps depending on the step/task decision
        apiFetch({
            path:
                "/oasis-workflow/v1/workflows/signoff/nextSteps/actionHistoryId=" +
                this.state.actionHistoryId +
                "/decision=" +
                decision +
                "/postId=" +
                postId,
            method: "GET"
        }).then(
            (stepdata) => {
                if (stepdata.steps === "") {
                    // this is the last step, and so, we didn't get any next steps
                    this.setState({
                        isLastStep: true,
                        lastStepDecision: decision,
                        stepSpinner: "hide"
                    });
                } else {
                    this.setState({
                        isLastStep: false,
                        lastStepDecision: "success"
                    });
                    let steps = stepdata.steps.map((step) => OWPick(step, ["step_id", "step_name"]));
                    let signoffSteps = [];

                    // if there is more than one possible next step
                    if (steps.length !== 1) {
                        signoffSteps.push({ label: __("Select Step", "oasisworkflow"), value: "" });
                    }

                    steps.map((step) => {
                        signoffSteps.push({ label: step.step_name, value: step.step_id });
                    });

                    this.setState({
                        signoffSteps: signoffSteps,
                        stepSpinner: "hide"
                    });

                    // if there is only one possible next step, auto select it
                    if (steps.length == 1) {
                        this.getSelectedStepDetails(signoffSteps[0]["value"]);
                        this.setState({
                            selectedStep: signoffSteps[0]["value"]
                        });
                    }
                }
                return stepdata;
            },
            (err) => {
                console.log(err);
                return err;
            }
        );
    }

    /**
     * For the selected step, get other details, like assignee list, assignToAll flag etc
     *
     * @param {Integer} stepId
     */
    getSelectedStepDetails(stepId) {
        let postId = this.props.postId;
        this.setState({
            selectedStep: stepId,
            assigneeSpinner: "show"
        });
        apiFetch({
            path:
                "/oasis-workflow/v1/workflows/signoff/stepDetails/actionHistoryId=" +
                this.state.actionHistoryId +
                "/stepId=" +
                stepId +
                "/postId=" +
                postId,
            method: "GET"
        }).then(
            (stepdata) => {
                let errors = [];
                let availableAssignees = [];
                let assignToAll = stepdata.assign_to_all === 1 ? true : false;
                let assignees = stepdata.users;

                this.props.setDueDate({ dueDate: stepdata.due_date });

                // Display Validation Message if no user found for the step
                if (assignees.length === 0) {
                    errors.push(__("No users found to assign the task.", "oasisworkflow"));
                    this.setState({
                        validationErrors: errors,
                        assignee: []
                    });

                    // scroll to the top, so that the user can see the error
                    this.signoffPanelRef.current.scrollIntoView();
                    return;
                }

                // Set and Get Assignees from the util function
                let stepAssignees = getStepAssignees({ assignees: assignees, assignToAll: assignToAll });
                availableAssignees = stepAssignees.availableAssignees;

                this.setState({
                    assignee: availableAssignees,
                    assignToAll: assignToAll,
                    selectedAssignees: stepAssignees.selectedAssignees,
                    assigneeSpinner: "hide"
                });
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
            selectedPriority
        });
    }

    /**
     * validate sign off
     * @param {Object} data
     */
    async validateSignoff(data) {
        const errors = [];
        let current_date = new Date();
        current_date = moment(current_date).format("YYYY-MM-DD");
        let due_date = moment(data.due_date).format("YYYY-MM-DD");

        if (data.step_id === "") {
            errors.push(__("Please select a step.", "oasisworkflow"));
        }

        if (data.due_date === "") {
            errors.push(__("Please enter a due date.", "oasisworkflow"));
        }

        if (data.due_date !== "" && moment(current_date).isAfter(due_date) == true) {
            errors.push(__("Due date must be greater than the current date.", "oasisworkflow"));
        }

        if (data.assignees.length === 0 && !this.state.assignToAll) {
            errors.push(__("No assigned actor(s).", "oasisworkflow"));
        }

        if( typeof window.acf !== "undefined" ) {
            // Check if ACF field valid or not if they exists
            let ACFCheck = await window.workflowSubmitWithACF();
            if( ACFCheck === 'invalid' ) {
                errors.push(__("Please enter ACF required fields.", "oasisworkflow"));
            }
         }

        return errors;
    }

    processSignoffQueue = async () => {
        // If there are no more requests in the queue, still wait for the post to stop saving.
        if (this.state.signoffQueue.length === 0) {
    
            // Wait until the post is not saving
            const waitForPostToSave = () => {
                return new Promise((resolve) => {
                    const interval = setInterval(() => {
                        if (!this.state.isSaving) {
                            clearInterval(interval);
                            resolve();
                        }
                    }, 100); // Check every 100 milliseconds
                });
            };
    
            await waitForPostToSave();
    
            // Once the post is done saving, return control to the caller function
            return;
        }
    
        const currentRequest = this.state.signoffQueue[0];
    
        // Wait until the post is not saving
        const waitForPostToSave = () => {
            return new Promise((resolve) => {
                const interval = setInterval(() => {
                    if (!this.state.isSaving) {
                        clearInterval(interval);
                        resolve();
                    }
                }, 100); // Check every 100 milliseconds
            });
        };
    
        await waitForPostToSave();
    
        // Proceed to handle workflow completion
        await this.handleSignoffCB(currentRequest);
    
        // Remove the processed request from the queue
        this.setState((prevState) => ({
            signoffQueue: prevState.signoffQueue.slice(1),
        }), () => {
            // Process the next request in the queue
            this.processSignoffQueue();
        });
    };

    /**
      * handle form submit for sign off
      */
    async handleSignoff(event) {
        event.preventDefault();

        // hide errors each click
        this.setState({
            validationErrors: []
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

    /**
     * handle form submit for sign off
     */
    async handleSignoffCB(event) {
        this.setState({
            submitSpinner: "show",
            submitButtonDisable: true
        });

        let form_data = {
            post_id: this.props.postId,
            step_id: this.state.selectedStep,
            decision: this.state.selectedAction,
            priority: this.state.selectedPriority,
            assignees: this.state.selectedAssignees,
            due_date: this.props.dueDate,
            comments: this.state.comments,
            history_id: this.state.actionHistoryId,
            hideSignOff: false,
            task_user: this.state.taskUser
        };

        // save the post
        try {
            // save the post
            await this.props.onSave();
         } catch (error) {
            if (error.code === 'rest_forbidden_context') {
                console.error('Permission error:', error.message);
            } else {
                console.error('An unexpected error occurred:', error);
            }
        }

        const errors = await this.validateSignoff(form_data);

        if (errors.length > 0) {
            this.setState({
                validationErrors: errors,
                submitSpinner: "hide",
                submitButtonDisable: false
            });

            // scroll to the top, so that the user can see the error
            this.signoffPanelRef.current.scrollIntoView();

            return;
        }

        this.setState({
            validationErrors: []
        });

        // check if post saving is done
        this.setState({ isSaving: true }, async () => {
            await this.processSignoffQueue();
            if (!this.state.isSaving) {
                await this.invokeSignoffAPI(form_data);
            }
        });
    }

    async invokeSignoffAPI(form_data) {

        // wait 100ms to proced to next steps.
        await new Promise(resolve => setTimeout(resolve, 100));

        if (this.state.isMakeAjaxRequest) {
            console.log("invokeSignoffAPI request already running.");
            return; // If an AJAX request is in progress, do nothing
        }

        await apiFetch({ path: "/oasis-workflow/v1/workflows/signoff/", method: "POST", data: form_data }).then(
            (submitResponse) => {
                if (submitResponse.new_action_history_id != this.state.actionHistoryId) {
                    this.setState({
                        hideSignOff: true,
                        redirectingLoader: "show"
                    });
                }
                // Redirect user to inbox page
                if (submitResponse.redirect_link !== "") {
                    window.location.href = DOMPurify.sanitize(submitResponse.redirect_link);
                } else {
                    this.props.handleResponse(submitResponse);
                }

                this.setState({
                    isApiInProgress: true,
                    isMakeAjaxRequest: true
                });
            },
            (err) => {
                console.log(err);
                this.setState({
                    isApiInProgress: true,
                    isMakeAjaxRequest: true
                });
                return err;
            }
        );
    }

    render() {
        const { isSaving, isPostInWorkflow } = this.props;
        const {
            validationErrors,
            isLastStep,
            lastStepDecision,
            hideSignOff,
            signoffButtonText,
            assignActorLabel,
            dueDateLabel,
            displayDueDate,
            redirectingLoader,
            stepSpinner,
            assigneeSpinner,
            submitSpinner,
            submitButtonDisable
        } = this.state;

        if (hideSignOff && redirectingLoader === "show") {
            return (
                <div>
                    <PanelBody>{__("redirecting...", "oasisworkflow")}</PanelBody>
                </div>
            );
        }

        // post is not in workflow anymore, so return empty
        if (!isPostInWorkflow || hideSignOff) {
            return "";
        }

        return (
            <PanelBody ref={this.signoffPanelRef} initialOpen={true} title={signoffButtonText}>
                <form className="reusable-block-edit-panel">
                    {validationErrors.length !== 0 ? (
                        <div id="owf-error-message" className="notice notice-error is-dismissible">
                            {validationErrors.map((error) => (
                                <p key={error}>{error}</p>
                            ))}
                        </div>
                    ) : (
                        ""
                    )}
                    <SelectControl
                        label={__("Action", "oasisworkflow") + ":"}
                        value={this.state.selectedAction}
                        options={this.state.actions}
                        onChange={this.getSignoffSteps.bind(this)}
                    />
                    {isLastStep ? (
                        <SignoffLastStep stepDecision={lastStepDecision} handleResponse={this.props.handleResponse} />
                    ) : (
                        <div>
                            <div className="owf-spinner">{stepSpinner == "show" ? <Spinner /> : ""}</div>
                            <SelectControl
                                label={__("Step", "oasisworkflow") + ":"}
                                value={this.state.selectedStep}
                                options={this.state.signoffSteps}
                                onChange={this.getSelectedStepDetails.bind(this)}
                            />
                            <TaskPriorities
                                value={this.state.selectedPriority}
                                onChange={this.handleOnPriorityChange.bind(this)}
                            />
                            <div>
                                <div className="owf-spinner">
                                    {assigneeSpinner == "show" && this.state.assignToAll == false ? <Spinner /> : ""}
                                </div>
                                {!this.state.assignToAll ? (
                                    <SelectControl
                                        multiple
                                        className="ow-multi-select"
                                        label={assignActorLabel + ":"}
                                        value={this.state.selectedAssignees}
                                        options={this.state.assignee}
                                        onChange={(selectedAssignees) => this.setState({ selectedAssignees })}
                                    />
                                ) : (
                                    ""
                                )}
                            </div>
                            {displayDueDate !== "" ? (
                                <PanelRow className="edit-post-post-schedule">
                                    <label>{dueDateLabel + ":"} </label>
                                    <Dropdown
                                        popoverProps={{ placement: 'bottom-end' }}
                                        contentClassName="edit-post-post-schedule__dialog owduedatepicker-dropdown"
                                        renderToggle={({ onToggle, isOpen }) => (
                                            <Fragment>
                                                <Button
                                                    type="button"
                                                    onClick={onToggle}
                                                    aria-expanded={isOpen}
                                                    aria-live="polite"
                                                    isLink
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
                                <TextareaControl
                                    label={__("Comments", "oasisworkflow") + ":"}
                                    value={this.state.comments}
                                    onChange={(comments) => this.setState({ comments })}
                                />
                            </PanelRow>
                            <PanelRow>
                                <Button
                                    isPrimary
                                    isBusy={isSaving}
                                    focus="true"
                                    disabled={submitButtonDisable}
                                    onClick={this.handleSignoff.bind(this)}
                                >
                                    {signoffButtonText}
                                </Button>
                                <div className="owf-spinner">{submitSpinner == "show" ? <Spinner /> : ""}</div>
                            </PanelRow>
                        </div>
                    )}
                </form>
            </PanelBody>
        );
    }
}

export default compose([
    withSelect((select) => {
        const { 
            getCurrentPostId, 
            getEditedPostAttribute
        } = select("core/editor");
        const { getDueDate, getOWSettings, getPostInWorkflow } = select("plugin/oasis-workflow");
        return {
            postId: getCurrentPostId(),
            postMeta: getEditedPostAttribute("meta"),
            dueDate: getDueDate(),
            owSettings: getOWSettings(),
            isPostInWorkflow: getPostInWorkflow()
        };
    }),
    withDispatch((dispatch) => ({
        onSave: dispatch("core/editor").savePost,
        setDueDate: dispatch("plugin/oasis-workflow").setDueDate
    }))
])(Signoff);
