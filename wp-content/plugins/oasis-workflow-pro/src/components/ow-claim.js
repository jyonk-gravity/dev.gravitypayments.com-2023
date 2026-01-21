/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { Button, PanelBody, PanelRow, Spinner } from '@wordpress/components';
import { withSelect } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';

import DOMPurify from "dompurify";

export class ClaimTask extends Component {
    constructor() {
        super(...arguments);

        this.state = {
            isBusy: false,
            success: [],
            responseErrors: [],
            hideClaim: false,
            submitSpinner: "hide",
            submitButtonDisable: false
        };

        this.handleClaim = this.handleClaim.bind(this);
    }

    /**
     * handle form submit for workflow abort
     */
    handleClaim(event) {
        // event.preventDefault();
        this.setState({
            isBusy: true,
            submitSpinner: "show",
            submitButtonDisable: true
        });

        let postId = this.props.postId;

        let form_data = {
            post_id: postId,
            history_id: this.props.actionHistoryId
        };

        apiFetch({ path: "/oasis-workflow/v1/workflows/claim/", method: "POST", data: form_data }).then(
            (claimResponse) => {
                let errors = [];
                if (claimResponse.isError === "true") {
                    errors.push(claimResponse.errorMessage);
                    this.setState({
                        responseErrors: errors,
                        hideClaim: true
                    });
                    return;
                }

                if (claimResponse.isError === "false") {
                    let new_history_id = claimResponse.new_history_id;
                    let ow_admin_url = claimResponse.url;
                    window.location.href = DOMPurify.sanitize(
                        ow_admin_url + "post.php?post=" + postId + "&action=edit&oasiswf=" + new_history_id
                    );
                }
            },
            (err) => {
                console.log(err);
                return err;
            }
        );
    }

    /**
     * handle redirect to the list page
     */
    handleRedirectToListPage() {
        if (this.props.postType === "post") {
            window.location.href = "edit.php";
        } else {
            window.location.href = DOMPurify.sanitize("edit.php?post_type=" + this.props.postType);
        }
    }

    /**
     * handle redirect to inbox page
     */
    handleRedirectToInboxPage() {
        window.location.href = DOMPurify.sanitize("admin.php?page=oasiswf-inbox");
    }

    componentDidMount() {
        // reset to default state
        this.setState({
            isBusy: false
        });
    }

    render() {
        const { responseErrors, hideClaim, submitSpinner, submitButtonDisable } = this.state;
        return (
            <div>
                {responseErrors.length !== 0 ? (
                    <div>
                        <PanelBody>
                            <div id="owf-error-message" className="notice notice-error is-dismissible">
                                {responseErrors.map((error) => (
                                    <p key={error}>{error}</p>
                                ))}
                            </div>
                        </PanelBody>
                        <PanelBody>
                            <PanelRow>
                                <p className="post-publish-panel__postpublish-subheader">
                                    <strong>{__("Other Actions", "oasisworkflow")}</strong>
                                </p>
                            </PanelRow>
                            <PanelRow>
                                <Button focus="true" onClick={this.handleRedirectToListPage.bind(this)} variant="link">
                                    {__("Take me to List page", "oasisworkflow")}
                                </Button>
                            </PanelRow>
                            <PanelRow>
                                <Button focus="true" onClick={this.handleRedirectToInboxPage.bind(this)} variant="link">
                                    {__("Take me to Workflow Inbox", "oasisworkflow")}
                                </Button>
                            </PanelRow>
                        </PanelBody>
                    </div>
                ) : (
                    ""
                )}
                {!this.props.isHidden && hideClaim == false ? (
                    <PanelBody>
                        <Button focus="true" variant="primary" disabled={submitButtonDisable} onClick={this.handleClaim}>
                            {__("Claim", "oasisworkflow")}
                        </Button>
                        {submitSpinner == "show" ? <Spinner /> : ""}
                    </PanelBody>
                ) : (
                    ""
                )}
            </div>
        );
    }
}

export default withSelect((select) => {
    const { getCurrentPostId } = select("core/editor");
    return {
        postId: getCurrentPostId()
    };
})(ClaimTask);
