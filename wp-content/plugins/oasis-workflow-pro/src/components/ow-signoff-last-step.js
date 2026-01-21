/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n';
import { Fragment, Component, createRef } from '@wordpress/element';
import { compose } from '@wordpress/compose';
import { PanelBody, PanelRow, Dropdown, CheckboxControl, TextareaControl, Button, Spinner } from '@wordpress/components';
import { withSelect, select, withDispatch, subscribe } from '@wordpress/data';
import { PostScheduleLabel, PostSchedule } from '@wordpress/editor';
import apiFetch from '@wordpress/api-fetch';
import HelpImage from "../images/help.png";

import DOMPurify from "dompurify";
/**
 * Internal dependencies
 */

import PrePublishChecklist from "./ow-pre-publish-checklist";
import { getActionHistoryIdFromURL, getTaskUserFromURL, ow_console } from "../util";

export class SignoffLastStep extends Component {
    constructor() {
        super(...arguments);

        this.state = {
            signoffButtonText: __("Sign Off", "oasisworkflow"),
            continueToSignoffText: __("Continue to Signoff", "oasisworkflow"),
            comments: "",
            mandatoryComments: "",
            isCommentShow: "",
            actionHistoryId: getActionHistoryIdFromURL(),
            taskUser: getTaskUserFromURL(),
            isImmediatelyChecked: false,
            originalPublishDate: this.props.publishDate,
            selectedChecklist: [],
            validationErrors: [],
            errorType: "",
            byPassWarning: false,
            isSaving: false,
            signoffQueue: [], // Queue to handle signoff requests
            loader: "hide",
            submitSpinner: "hide",
            submitButtonDisable: false
        };
        this.unsubscribe = null;
    }

    componentDidMount() {
        let customWorkflowTerminology = this.props.owSettings.terminology_settings.oasiswf_custom_workflow_terminology;
        let workflowSettings = this.props.owSettings.workflow_settings;
        console.log("workflowSettings", workflowSettings);
        if (customWorkflowTerminology) {
            let signoffButtonText = customWorkflowTerminology.signOffText;
            let continueToSignoffText = customWorkflowTerminology.continueToSignoffText;
            this.setState({
                signoffButtonText,
                continueToSignoffText
            });
        }

        if (workflowSettings) {
            let mandatoryComments = workflowSettings.oasiswf_comments_setting;
            let isCommentShow = workflowSettings.oasiswf_last_step_comment_setting;

            this.setState({
                mandatoryComments,
                isCommentShow
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
        const isSaving = select('core/editor').isSavingPost();
        this.setState({ isSaving });
        // console.log('Is post saving:', isSaving);
    };

    componentWillUnmount() {
        // Unsubscribe when the component is unmounted
        if (this.unsubscribe) {
            this.unsubscribe();
        }
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
            selectedChecklist: options
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
        await this.handleWorkflowCompleteCB(currentRequest);

        // Remove the processed request from the queue
        this.setState((prevState) => ({
            signoffQueue: prevState.signoffQueue.slice(1),
        }), () => {
            // Process the next request in the queue
            this.processSignoffQueue();
        });
    };

    /**
     * The user wants to continue to sign off, so we need to bypass the warnings
     * @param {*} event
     */
    handleContinueToSignoff(event) {
        event.preventDefault(); // Prevent default button behavior

        // Set byPassWarning to true and add the current click event to the signoff queue
        this.setState(
            {
                byPassWarning: true
            },
            () => {
                this.handleWorkflowComplete();
            }
        );
    }

    /**
      * validate sign off
      * @param {Object} data
      */
    async validateSignoff(data) {
        const errors = [];

        if (data.comments === "" && this.state.mandatoryComments === "mandatory" && this.state.isCommentShow === "show") {
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

    /**
     * handle successful completion of workflow
     */
    async handleWorkflowComplete(event) {
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

    async handleWorkflowCompleteCB(event) {
        this.setState({
            submitSpinner: "show",
            submitButtonDisable: true
        });

        let form_data = {
            post_id: this.props.postId,
            history_id: this.state.actionHistoryId,
            immediately: this.state.isImmediatelyChecked,
            comments: this.state.comments,
            task_user: this.state.taskUser,
            publish_datetime: this.props.publishDate,
            pre_publish_checklist: this.state.selectedChecklist,
            by_pass_warning: this.state.byPassWarning
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

        const errors = await this.validateSignoff(form_data);

        if (errors.length > 0) {
            this.setState({
                validationErrors: errors,
                submitSpinner: "hide",
                submitButtonDisable: false,
                errorType: ""
            });

            return;
        }

        this.setState({
            validationErrors: []
        });

        // check if post saving is done
        this.setState({ isSaving: true }, async () => {
            await this.processSignoffQueue();
            ow_console("invokeSignoffAPI isSaving", this.state.isSaving);
            if (!this.state.isSaving) {
                await this.invokeSignoffAPI(form_data);
            }
        });
    }

    // redirect invoke
    redirectTrigger(url) {
        this.setState({
            loader: "show"
        });
        window.location.href = DOMPurify.sanitize(url);
    }

    // page refresh invoke
    refreshTrigger(response) {
        this.props.handleResponse(response);
        this.props.pageRefresh();
    }

    async invokeSignoffAPI(form_data) {
        ow_console("invokeSignoffAPI");
        await apiFetch({
            path: "/oasis-workflow/v1/workflows/signoff/workflowComplete/",
            method: "POST",
            data: form_data
        }).then(
            (submitResponse) => {
                if (submitResponse.success_response == false) {
                    this.setState({
                        validationErrors: submitResponse.validation_error,
                        errorType: submitResponse.error_type
                    });
                } else {

                    // hooking workflow complete for js trigger
                    var evt = new CustomEvent("ow_workflow_completed", { detail: submitResponse });
                    document.dispatchEvent(evt);

                    // Redirect user to inbox page
                    if (submitResponse.redirect_link !== "") {
                        // update post with laststep status.
                        const postUpdate = async () => {
                            await this.props.editPost({ status: submitResponse.new_post_status });
                            await this.props.onSave();
                            this.redirectTrigger(submitResponse.redirect_link);
                        }
                        postUpdate();
                    } else {
                        // update post with laststep status.
                        const postUpdate = async () => {
                            await this.props.editPost({ status: submitResponse.new_post_status });
                            await this.props.onSave();
                            this.refreshTrigger(submitResponse);
                        }
                        postUpdate();
                    }
                }
                return submitResponse;
            },
            (err) => {
                console.log(err);
                return err;
            }
        );
    }

    /**
     * handle cancellation of workflow on the last step
     */
    handleWorkflowCancel(event) {
        let form_data = {
            post_id: this.props.postId,
            history_id: this.state.actionHistoryId,
            comments: this.state.comments,
            task_user: this.state.taskUser
        };

        apiFetch({
            path: "/oasis-workflow/v1/workflows/signoff/workflowCancel/",
            method: "POST",
            data: form_data
        }).then(
            (submitResponse) => {
                this.props.handleResponse(submitResponse);
            },
            (err) => {
                console.log(err);
                return err;
            }
        );
    }

    /**
     * handle immediately checkbox change
     * @param {boolean} checked
     */
    onImmediatelyChange(checked) {
        let currentDate = new Date();
        let newDate = "";
        if (checked) {
            this.setState({
                isImmediatelyChecked: true
            });
            newDate = currentDate; //publish date set to now
        } else {
            this.setState({
                isImmediatelyChecked: false
            });
            newDate = this.state.originalPublishDate; // publish date set to the original date
        }

        this.props.editPost({ date: newDate });
        this.props.onSave();
    }

    render() {
        const { isSaving, postMeta } = this.props;
        const {
            isImmediatelyChecked,
            signoffButtonText,
            isCommentShow,
            continueToSignoffText,
            validationErrors,
            errorType,
            submitSpinner,
            submitButtonDisable,
            loader
        } = this.state;

        if (loader === "show") {
            return (
                <div>
                    <PanelBody>{__("redirecting...", "oasisworkflow")}</PanelBody>
                </div>
            );
        }

        return (
            <div>
                {validationErrors.length !== 0 ? (
                    <div id="owf-error-message" className="notice notice-error is-dismissible">
                        {validationErrors.map((error) => (
                            <p key={error}>{error}</p>
                        ))}
                        {errorType == "warning" ? (
                            <p>
                                <Button variant="secondary" focus="true" onClick={this.handleContinueToSignoff.bind(this)}>
                                    {continueToSignoffText}
                                </Button>
                            </p>
                        ) : (
                            ""
                        )}
                    </div>
                ) : (
                    ""
                )}
                {this.props.stepDecision === "success" ? (
                    <div>
                        <PanelRow>
                            <div id="owf-success-message" className="notice notice-warning is-dismissible">
                                <p>
                                    {__(
                                        "This is the last step in the workflow. Are you sure to complete the workflow?",
                                        "oasisworkflow"
                                    )}
                                </p>
                                {postMeta && postMeta._oasis_original ? (
                                    <p>
                                        {" "}
                                        {__(
                                            "Signing off will copy over the contents of this revised article to the corresponding published/original article. This will happen either immediately or on the scheduled date/time.",
                                            "oasisworkflow"
                                        )}
                                    </p>
                                ) : (
                                    ""
                                )}
                            </div>
                        </PanelRow>
                        {this.props.checklist.length !== 0 ? (
                            <PrePublishChecklist
                                checklist={this.props.checklist}
                                onChange={this.selectPrePublishChecklist.bind(this)}
                            />
                        ) : (
                            ""
                        )}
                        <PanelRow className="edit-post-post-schedule">
                            <label>{__("Publish", "oasisworkflow") + ":"} </label>
                            <Dropdown
                                popoverProps={{ placement: 'bottom-end' }}
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
                        {isCommentShow == "show" ? (
                            <>
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
                            </>
                        ) : (
                            ""
                        )}
                        <PanelRow>
                            <CheckboxControl
                                label={__("Publish Immediately?", "oasisworkflow")}
                                checked={isImmediatelyChecked}
                                onChange={this.onImmediatelyChange.bind(this)}
                            />
                        </PanelRow>
                        <PanelRow>
                            <Button
                                variant="primary"
                                isBusy={isSaving}
                                focus="true"
                                disabled={submitButtonDisable}
                                onClick={this.handleWorkflowComplete.bind(this)}
                            >
                                {signoffButtonText}
                            </Button>
                            <div className="owf-spinner">{submitSpinner == "show" ? <Spinner /> : ""}</div>
                        </PanelRow>
                    </div>
                ) : (
                    <div>
                        <PanelRow>
                            <div id="owf-success-message" className="notice notice-error is-dismissible">
                                <p>
                                    {__(
                                        "There are no further steps defined in the workflow. Do you want to cancel the post/page from the workflow?",
                                        "oasisworkflow"
                                    )}
                                </p>
                            </div>
                        </PanelRow>
                        <PanelRow>
                            <TextareaControl
                                label={__("Comments", "oasisworkflow") + ":"}
                                value={this.state.comments}
                                onChange={(comments) => this.setState({ comments })}
                            />
                        </PanelRow>
                        <PanelRow>
                            <Button
                                variant="primary"
                                isBusy={isSaving}
                                focus="true"
                                onClick={this.handleWorkflowCancel.bind(this)}
                            >
                                {signoffButtonText}
                            </Button>
                        </PanelRow>
                    </div>
                )}
            </div>
        );
    }
}

export default compose([
    withSelect((select) => {
        const { getCurrentPostId, getEditedPostAttribute } = select("core/editor");
        const { getOWSettings } = select("plugin/oasis-workflow");
        return {
            postId: getCurrentPostId(),
            publishDate: getEditedPostAttribute("date"),
            postMeta: getEditedPostAttribute("meta"),
            owSettings: getOWSettings()
        };
    }),
    withDispatch((dispatch) => ({
        onSave: dispatch("core/editor").savePost,
        editPost: dispatch("core/editor").editPost,
        autosave: dispatch("core/editor").autosave,
        pageRefresh: dispatch("core/editor").refreshPost
    }))
])(SignoffLastStep);
