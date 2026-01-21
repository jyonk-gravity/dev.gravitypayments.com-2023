/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n';
import { Component, createRef } from '@wordpress/element';
import { compose } from '@wordpress/compose';
import { PanelBody, Button, Modal, PanelRow, TextareaControl, Spinner } from '@wordpress/components';
import { withSelect, withDispatch } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';

import DOMPurify from "dompurify";

export class AbortWorkflow extends Component {
    constructor() {
        super(...arguments);

        this.abort = createRef();

        this.state = {
            buttonText: __("Abort Workflow", "oasisworkflow"),
            hideButton: true,
            isOpen: false,
            isBusy: false,
            mandatoryComments: "",
            comments: "",
            success: [],
            submitSpinner: "hide",
            submitButtonDisable: false,
            validationErrors: []
        };

        this.handleWorkflowAbort = this.handleWorkflowAbort.bind(this);
    }

    /**
     * Show the Workflow Abort modal dialog
     */
    showAbortDialog() {
        this.setState({
            isOpen: true
        });
    }

    /**
     * handle form submit for workflow abort
     */
    handleWorkflowAbort(event) {
        // prevent default redirection.
        event.preventDefault();

        this.setState({
            isBusy: true,
            submitButtonDisable: true,
            submitSpinner: "show"
        });

        let form_data = {
            post_id: this.props.postId,
            comments: this.state.comments
        };

        const errors = this.validateAbort(form_data);

        if (errors.length > 0) {
            event.preventDefault();
            this.setState({
                validationErrors: errors,
                submitSpinner: "hide",
                submitButtonDisable: false,
                isBusy: false
            });
            // scroll to the top, so that the user can see the error
            this.abort.current.scrollIntoView();
            return;
        }

        this.setState({
            validationErrors: []
        });

        apiFetch({ path: "/oasis-workflow/v1/workflows/abort/", method: "POST", data: form_data }).then(
            (submitResponse) => {
                const success = [];
                success.push(submitResponse.success_response);
                this.setState({
                    success: success,
                    isBusy: false
                });

                if (submitResponse.redirect_link !== "") {
                    window.location.href = DOMPurify.sanitize(submitResponse.redirect_link);
                }
            },
            (err) => {
                console.log(err);
                return err;
            }
        );
    }

    validateAbort(data) {
        const errors = [];

        if (data.comments === "" && this.state.mandatoryComments === "mandatory") {
            errors.push(__("Please enter comments.", "oasisworkflow"));
        }

        return errors;
    }

    /**
     * handle popup close
     */
     async handleClose() {
        this.setState({ isOpen: false });

        await this.props.onSave();

        // update that the post is not in workflow anymore
        this.props.handleResponse();
    }

    componentDidMount() {
        const { userCap, owSettings } = this.props;
        if (userCap.user_can.ow_abort_workflow) {
            let customWorkflowTerminology = owSettings.terminology_settings.oasiswf_custom_workflow_terminology;
            let workflowSettings = this.props.owSettings.workflow_settings;
            let buttonText = this.state.buttonText;

            if (customWorkflowTerminology) {
                buttonText = owSettings.terminology_settings.oasiswf_custom_workflow_terminology.abortWorkflowText;
            }

            if (workflowSettings) {
                let mandatoryComments = workflowSettings.oasiswf_comments_setting;
                this.setState({
                    mandatoryComments
                });
            }

            this.setState({
                buttonText,
                hideButton: false,
                success: [],
                isBusy: false
            });
        }
    }

    render() {
        const {
            hideButton,
            isOpen,
            success,
            buttonText,
            submitButtonDisable,
            submitSpinner,
            validationErrors
        } = this.state;
        const { isPostInWorkflow, isCurrentPostPublished } = this.props;

        // if post is NOT in workflow, then do not show abort button
        if (hideButton || !isPostInWorkflow || isCurrentPostPublished) {
            return "";
        }

        return (
            <PanelBody>
                <Button focus="true" onClick={this.showAbortDialog.bind(this)} variant="link">
                    {buttonText}
                </Button>
                {isOpen && (
                    <Modal title={buttonText} onRequestClose={() => this.setState({ isOpen: false })}>
                        {validationErrors.length !== 0 ? (
                            <div id="owf-error-message" className="notice notice-error is-dismissible">
                                {validationErrors.map((error) => (
                                    <p key={error}>{error}</p>
                                ))}
                            </div>
                        ) : (
                            ""
                        )}
                        {success.length !== 0 ? (
                            <div>
                                <div id="owf-success-message" className="notice notice-success is-dismissible">
                                    {success.map((message) => (
                                        <p key={message}>{message}</p>
                                    ))}
                                </div>
                                <PanelRow>
                                    <Button variant="primary" focus="true" onClick={this.handleClose.bind(this)}>
                                        {__("Close", "oasisworkflow")}
                                    </Button>
                                </PanelRow>
                            </div>
                        ) : (
                            <form className="reusable-block-edit-panel" onSubmit={this.handleWorkflowAbort}>
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
        const { getCurrentPostId, getEditedPostAttribute, isCurrentPostPublished } = select("core/editor");
        const { getOWSettings, getUserCapabilities, getPostInWorkflow } = select("plugin/oasis-workflow");
        return {
            postId: getCurrentPostId(),
            postMeta: getEditedPostAttribute("meta"),
            owSettings: getOWSettings(),
            userCap: getUserCapabilities(),
            isPostInWorkflow: getPostInWorkflow(),
            isCurrentPostPublished: isCurrentPostPublished()
        };
    }),
    withDispatch((dispatch) => ({
        onSave: dispatch("core/editor").savePost
    }))
])(AbortWorkflow);
