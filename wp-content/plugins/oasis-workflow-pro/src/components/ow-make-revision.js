/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { PanelBody, PanelRow, Button, Spinner } from '@wordpress/components';
import { withSelect, withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';
import apiFetch from '@wordpress/api-fetch';
import DOMPurify from "dompurify";

/**
 * Internal dependencies
 */
import MakeRevisionOverlay from "./ow-make-revision-overlay";
import MakeRevisionExistOverlay from "./ow-make-revision-exist-overlay";

export class MakeRevision extends Component {
    constructor() {
        super(...arguments);
        this.state = {
            revisionButtonText: __("Make Revision", "oasisworkflow"),
            showButton: false,
            showRevisionOverlay: false,
            revisionOverlayText: __(
                "You may not make changes to this published content. You must first make a revision and then submit your changes for approval.",
                "oasisworkflow"
            ),
            revisionPostId: null,
            revisionExist: false,
            adminURL: "",
            revisionCancelDisable: false,
            revisionNoDisable: false,
            revisionOkDisable: false,
            revisionButtonDisable: false,
            submitSpinner: "hide"
        };
    }

    checkExistingRevision(event) {
        // event.preventDefault();
        this.setState({
            submitSpinner: "show",
            revisionButtonDisable: true
        });
        let postId = this.props.postId;
        apiFetch({
            path: "/oasis-workflow/v1/workflows/documentRevision/checkExistingRevision/postId=" + postId,
            method: "GET"
        }).then(
            (revisionData) => {
                // Revision not exist so create new
                let ow_admin_url = revisionData.url;
                if (!revisionData.revisionExist) {
                    this.savePostAsDraft();
                } else {
                    // Revision exist
                    this.setState({
                        revisionPostId: revisionData.revision_post_id,
                        revisionExist: true,
                        submitSpinner: "hide",
                        adminURL: ow_admin_url
                    });
                }
                return revisionData;
            },
            (err) => {
                console.log(err);
                return err;
            }
        );
    }

    // Redirect back if user don't have permission to edit published post
    pageBack() {
        window.history.back();
    }

    // Redirect to existing revision
    takeToExistingRevision() {
        this.setState({
            revisionCancelDisable: true,
            revisionNoDisable: true,
            revisionOkDisable: true,
            submitSpinner: "show"
        });
        window.location.href = DOMPurify.sanitize(this.state.adminURL + "post.php?action=edit&post=" + this.state.revisionPostId);
    }

    // Create new revision
    savePostAsDraft() {
        let formData = {
            post_id: this.props.postId
        };
        apiFetch({ path: "/oasis-workflow/v1/workflows/documentRevision/", method: "POST", data: formData }).then(
            (submitResponse) => {
                window.location.href = DOMPurify.sanitize(submitResponse.revision_post_url);
            },
            (err) => {
                console.log(err);
                return err;
            }
        );
    }

    // Delete and create new revision if revision already exist
    deleteRevision(event) {
        this.setState({
            revisionCancelDisable: true,
            revisionNoDisable: true,
            revisionOkDisable: true,
            submitSpinner: "show"
        });

        // event.preventDefault();
        apiFetch({
            path: "/oasis-workflow/v1/workflows/documentRevision/postId=" + this.state.revisionPostId,
            method: "DELETE"
        }).then(
            (revisionData) => {
                // If existing revision is deleted than create new revision
                if (revisionData.status === "success") {
                    this.savePostAsDraft();
                }
                return revisionData;
            },
            (err) => {
                return err;
            }
        );
    }

    // Exit overlay
    existOverlay() {
        this.setState({
            revisionExist: false
        });
    }

    componentDidMount() {
        let postId = this.props.postId;

        let customWorkflowTerminology = this.props.owSettings.terminology_settings.oasiswf_custom_workflow_terminology;
        let revisionSettings = this.props.owSettings.document_revision_settings;

        if (customWorkflowTerminology) {
            let revisionButtonText = customWorkflowTerminology.makeRevisionText;
            this.setState({
                revisionButtonText
            });
        }

        if (revisionSettings) {
            let revisionOverlayText = revisionSettings.oasiswf_revise_post_make_revision_overlay;
            this.setState({
                revisionOverlayText
            });
        }

        apiFetch({
            path: "/oasis-workflow/v1/workflows/documentRevision/checkRevisionCapability/postId=" + postId,
            method: "GET"
        }).then(
            (revisiondata) => {
                this.setState({
                    showButton: revisiondata.showButton,
                    showRevisionOverlay: revisiondata.showOverlay
                });
                return revisiondata;
            },
            (err) => {
                return err;
            }
        );
    }

    render() {
        const {
            showButton,
            showRevisionOverlay,
            revisionExist,
            revisionButtonText,
            revisionOverlayText,
            revisionCancelDisable,
            revisionNoDisable,
            revisionOkDisable,
            submitSpinner,
            revisionButtonDisable
        } = this.state;
        const { isCurrentPostPublished, isCurrentPostScheduled } = this.props;

        if (!isCurrentPostPublished && !isCurrentPostScheduled) {
            return "";
        }

        return (
            <div>
                {showButton ? (
                    <PanelBody>
                        <PanelRow>
                            <Button variant="primary" focus="true" onClick={this.checkExistingRevision.bind(this)}>
                                {revisionButtonText}
                            </Button>
                            <div className="owf-spinner">{submitSpinner == "show" ? <Spinner /> : ""}</div>
                        </PanelRow>
                    </PanelBody>
                ) : (
                    ""
                )}
                {showRevisionOverlay && (
                    <MakeRevisionOverlay
                        pageBack={this.pageBack.bind(this)}
                        checkExistingRevision={this.checkExistingRevision.bind(this)}
                        revisionOverlayText={revisionOverlayText}
                        buttonText={revisionButtonText}
                        revisionButtonDisable={revisionButtonDisable}
                        submitSpinner={submitSpinner}
                    />
                )}
                {revisionExist && (
                    <MakeRevisionExistOverlay
                        revisionExist={this.existOverlay.bind(this)}
                        existingRevision={this.takeToExistingRevision.bind(this)}
                        deleteRevision={this.deleteRevision.bind(this)}
                        buttonText={revisionButtonText}
                        revisionCancelDisable={revisionCancelDisable}
                        revisionNoDisable={revisionNoDisable}
                        revisionOkDisable={revisionOkDisable}
                        submitSpinner={submitSpinner}
                    />
                )}
            </div>
        );
    }
}

export default compose([
    withSelect((select) => {
        const { getCurrentPostId, isCurrentPostPublished, isCurrentPostScheduled } = select("core/editor");
        const { getOWSettings } = select("plugin/oasis-workflow");
        return {
            postId: getCurrentPostId(),
            isCurrentPostPublished: isCurrentPostPublished(),
            isCurrentPostScheduled: isCurrentPostScheduled(),
            owSettings: getOWSettings()
        };
    }),
    withDispatch((dispatch) => ({
        onSave: dispatch("core/editor").savePost,
        autosave: dispatch("core/editor").autosave
    }))
])(MakeRevision);
