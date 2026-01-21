/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n';
import { Component, createRef } from '@wordpress/element';
import { compose } from '@wordpress/compose';
import { PanelBody, PanelRow, TextareaControl, Button, Modal, Spinner } from '@wordpress/components';
import { withSelect, withDispatch, subscribe, select } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';


import { getActionHistoryIdFromURL, getTaskUserFromURL, OWPick, ow_console } from "../util";
// react select
import Select from 'react-select';
import DOMPurify from "dompurify";

export class Reassign extends Component {
    constructor() {
        super(...arguments);

        this.reassign = createRef();

        this.state = {
            assignActorLabel: __("Available Actor(s)", "oasisworkflow"),
            buttonText: __("Reassign", "oasisworkflow"),
            mandatoryComments: "",
            hideButton: true,
            hideForm: false,
            isOpen: false,
            isBusy: false,
            availableAssignees: [],
            selectedAssignees: [],
            actionHistoryId: getActionHistoryIdFromURL(),
            taskUser: getTaskUserFromURL(),
            comments: "",
            noAssigneeMessage: "hide",
            submitSpinner: "hide",
            submitButtonDisable: false,
            isSaving: false,
            signoffQueue: [],
            validationErrors: [],
            successMessage: ""
        };

        this.handleReassign = this.handleReassign.bind(this);
        this.unsubscribe = null;
    }

    componentDidMount() {
        let customWorkflowTerminology = this.props.owSettings.terminology_settings.oasiswf_custom_workflow_terminology;
        let workflowSettings = this.props.owSettings.workflow_settings;

        if (customWorkflowTerminology) {
            let assignActorLabel = customWorkflowTerminology.assignActorsText;
            this.setState({
                assignActorLabel
            });
        }

        if (workflowSettings) {
            let mandatoryComments = workflowSettings.oasiswf_comments_setting;
            this.setState({
                mandatoryComments
            });
        }

        const { userCap } = this.props;
        if (userCap.user_can.ow_reassign_task) {
            this.setState({
                hideButton: false,
                isBusy: false
            });
        }

        // fetch assignees
        apiFetch({
            path:
                "/oasis-workflow/v1/workflows/reassign/assignees/actionHistoryId=" +
                this.state.actionHistoryId +
                "/taskUser=" +
                this.state.taskUser,
            method: "GET"
        }).then(
            (results) => {
                if (results.assignee_count !== 0) {
                    let availableAssignees = [];
                    let assignees = results.user_info;
                    let assigneeData = assignees.map((users) => OWPick(users, ["ID", "name"]));
                    assigneeData.map((users) => {
                        availableAssignees.push({ label: users.name, value: users.ID });
                    });
                    this.setState({
                        availableAssignees
                    });
                } else {
                    this.setState({
                        noAssigneeMessage: "show",
                        hideForm: true
                    });
                }
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

    componentWillUnmount() {
        // Unsubscribe when the component is unmounted
        if (this.unsubscribe) {
            this.unsubscribe();
        }
    }

    checkPostSaving = () => {
        const isSaving = select('core/editor').isSavingPost();
        this.setState({ isSaving });
    };

    /**
     * Show the Reassign modal dialog
     */
    showReassignModal() {
        this.setState({
            isOpen: true
        });
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
        await this.handleReassignCB(currentRequest);

        // Remove the processed request from the queue
        this.setState((prevState) => ({
            signoffQueue: prevState.signoffQueue.slice(1),
        }), () => {
            // Process the next request in the queue
            this.processSignoffQueue();
        });
    };

    /**
     * handle form submit for reassign
     */
    async handleReassign(event) {
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
     * handle form submit for reassign
     */
    async handleReassignCB(event) {

        event.preventDefault();

        this.setState({
            isBusy: true,
            submitSpinner: "show",
            submitButtonDisable: true
        });

        let form_data = {
            post_id: this.props.postId,
            task_user: this.state.taskUser,
            history_id: this.state.actionHistoryId,
            assignees: this.state.selectedAssignees.map(assignee => assignee.value),
            comments: this.state.comments
        };

        try {
            // save the post
            await this.props.onSave();
        } catch (error) {
            if (error.code === 'rest_forbidden_context') {
                console.error('Permission error:', error.message);
                // Handle the permission error, e.g., show a user-friendly message
            } else {
                console.error('An unexpected error occurred:', error);
            }
        }

        const errors = await this.validateReassign(form_data);

        if (errors.length > 0) {
            event.preventDefault();
            this.setState({
                validationErrors: errors,
                submitSpinner: "hide",
                submitButtonDisable: false,
                isBusy: false
            });
            // scroll to the top, so that the user can see the error
            this.reassign.current.scrollIntoView();
            return;
        }

        this.setState({
            validationErrors: []
        });

        // check if post saving is done
        this.setState({ isSaving: true }, async () => {
            await this.processSignoffQueue();
            ow_console("invokeRessignAPI isSaving", this.state.isSaving);
            if (!this.state.isSaving) {
                await this.invokeRessignAPI(form_data);
            }
        });

    }

    async invokeRessignAPI(form_data) {

        // wait 100ms to proced to next steps.
        await new Promise(resolve => setTimeout(resolve, 100));

        await apiFetch({ path: "/oasis-workflow/v1/workflows/reassign/", method: "POST", data: form_data }).then(
            (submitResponse) => {
                if (submitResponse.isError == true) {
                    this.setState({
                        validationErrors: submitResponse.errorResponse,
                        submitSpinner: "hide",
                        submitButtonDisable: false
                    });
                    // scroll to the top, so that the user can see the error
                    this.reassign.current.scrollIntoView();
                } else {
                    this.setState({
                        successMessage: submitResponse.successResponse,
                        hideForm: true
                    });

                    // Redirect user to inbox page
                    if (submitResponse.redirect_link !== "") {
                        window.location.href = DOMPurify.sanitize(submitResponse.redirect_link);
                    }
                }
            },
            (err) => {
                console.log(err);
                return err;
            }
        );
    }

    async validateReassign(data) {
        const errors = [];

        if (data.assignees.length === 0) {
            errors.push(__("No assigned actor(s).", "oasisworkflow"));
        }

        if (data.comments === "" && this.state.mandatoryComments === "mandatory") {
            errors.push(__("Please enter comments.", "oasisworkflow"));
        }

        if (typeof window.acf !== "undefined") {
            // Check if ACF field valid or not if they exists
            let ACFCheck = await window.workflowSubmitWithACF();
            if (ACFCheck === 'invalid') {
                errors.push(__("Please enter ACF required fields.", "oasisworkflow"));
            }
        }

        return errors;
    }

    render() {
        const {
            hideButton,
            buttonText,
            isOpen,
            assignActorLabel,
            noAssigneeMessage,
            submitSpinner,
            submitButtonDisable,
            validationErrors,
            hideForm,
            successMessage
        } = this.state;
        const { isPostInWorkflow, isCurrentPostPublished } = this.props;

        // if post is NOT in workflow, then do not show abort button
        if (hideButton || !isPostInWorkflow || isCurrentPostPublished) {
            return "";
        }

        return (
            <PanelBody>
                <Button focus="true" onClick={this.showReassignModal.bind(this)} variant="link">
                    {buttonText}
                </Button>
                {isOpen && (
                    <Modal
                        ref={this.reassign}
                        title={buttonText}
                        onRequestClose={() => this.setState({ isOpen: false })}
                    >
                        {validationErrors.length !== 0 ? (
                            <div id="owf-error-message" className="notice notice-error is-dismissible">
                                {validationErrors.map((error) => (
                                    <p key={error}>{error}</p>
                                ))}
                            </div>
                        ) : (
                            ""
                        )}
                        {noAssigneeMessage == "show" ? (
                            <div id="owf-error-message" className="notice notice-error is-dismissible">
                                {__("No users found to reassign", "oasisworkflow")}
                            </div>
                        ) : (
                            ""
                        )}
                        {successMessage !== "" ? (
                            <div id="owf-success-message" className="notice notice-success is-dismissible">
                                {<p key={successMessage}>{successMessage}</p>}
                            </div>
                        ) : (
                            ""
                        )}
                        {!hideForm && (
                            <form className="reusable-block-edit-panel owf-reassign" onSubmit={this.handleReassign}>
                                <div className="ow-select2-wrap">
                                    <label htmlFor={'assignActor'}>{assignActorLabel + ":"}</label>
                                    <Select
                                        inputId="assignActor"
                                        className="ow-select2"
                                        value={this.props.value}
                                        options={this.state.availableAssignees}
                                        onChange={(selectedAssignees) => this.setState({ selectedAssignees })}
                                        isMulti='true'
                                    />
                                    <span>{__("select actor(s) to reassign the task", "oasisworkflow")}</span>
                                </div>
                                <TextareaControl
                                    label={__("Comments", "oasisworkflow") + ":"}
                                    value={this.state.comments}
                                    onChange={(comments) => this.setState({ comments })}
                                />
                                <PanelRow>
                                    <Button variant="link" onClick={() => this.setState({ isOpen: false })}>
                                        {__("Cancel", "oasisworkflow")}
                                    </Button>
                                    <Button
                                        type="submit"
                                        isBusy={this.state.isBusy}
                                        variant="primary"
                                        disabled={submitButtonDisable}
                                        focus="true"
                                    >
                                        {buttonText}
                                    </Button>
                                    {submitSpinner == "show" ? <Spinner /> : ""}
                                </PanelRow>
                            </form>
                        )}
                    </Modal>
                )}
            </PanelBody>
        );
    }
}

export default compose([
    withSelect((select) => {
        const { getCurrentPostId, isCurrentPostPublished } = select("core/editor");
        const { getUserCapabilities, getPostInWorkflow, getOWSettings } = select("plugin/oasis-workflow");
        return {
            postId: getCurrentPostId(),
            userCap: getUserCapabilities(),
            isPostInWorkflow: getPostInWorkflow(),
            owSettings: getOWSettings(),
            isCurrentPostPublished: isCurrentPostPublished()
        };
    }),
    withDispatch((dispatch) => ({
        onSave: dispatch("core/editor").savePost
    }))
])(Reassign);
