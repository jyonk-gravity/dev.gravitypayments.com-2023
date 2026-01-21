/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n';
import { Fragment, Component } from '@wordpress/element';

// Import PluginSidebar & PluginSidebarMoreMenuItem from both packages.
import { PluginSidebar as PluginSidebarEditor, PluginSidebarMoreMenuItem as PluginSidebarMoreMenuItemEditor } from '@wordpress/editor';
import { PluginSidebar as PluginSidebarEditPost, PluginSidebarMoreMenuItem as PluginSidebarMoreMenuItemEditPost } from '@wordpress/edit-post';

import { compose } from '@wordpress/compose';
import { PanelBody } from '@wordpress/components';
import { withSelect, withDispatch } from '@wordpress/data';
import { registerPlugin } from '@wordpress/plugins';
import apiFetch from '@wordpress/api-fetch';

/**
 * Internal dependencies
 */
import AbortWorkflow from "./components/ow-abort";
import SubmitToWorkflow from "./components/ow-submit-to-workflow";
import SignoffResponse from "./components/ow-signoff-response";
import ClaimTask from "./components/ow-claim";
import Signoff from "./components/ow-signoff";
import MakeRevision from "./components/ow-make-revision";
import CompareRevision from "./components/ow-compare-revision";
import Reassign from "./components/ow-reassign";
import { getActionHistoryIdFromURL, pluginIcon, OWisEmpty } from "./util";
import CustomPostTypeSupportMessage from "./components/custom-post-type-support";
import EditorialComments from "./components/ow-editorial-comments";


// Function to determine the appropriate components based on the WordPress version
function getComponentsBasedOnVersion(version) {
    if (versionCompare(version, '6.6.1') >= 0) {
        return {
            PluginSidebar: PluginSidebarEditor,
            PluginSidebarMoreMenuItem: PluginSidebarMoreMenuItemEditor
        };
    } else {
        return {
            PluginSidebar: PluginSidebarEditPost,
            PluginSidebarMoreMenuItem: PluginSidebarMoreMenuItemEditPost
        };
    }
}

// Simple version comparison function
function versionCompare(v1, v2) {
    const v1parts = v1.split('.').map(Number);
    const v2parts = v2.split('.').map(Number);

    for (let i = 0; i < v1parts.length; ++i) {
        if (v1parts[i] > v2parts[i]) return 1;
        if (v1parts[i] < v2parts[i]) return -1;
    }

    return 0;
}

/**
 * Use PluginSidebar & PluginSidebarMoreMenuItem from correct packages depending on WordPress version
 * if WordPress version v6.6.1 Or more then get these component from '@wordpress/editor'
 * Othersise get them from '@wordpress/edit-post'
 * Because, using them from '@wordpress/edit-post' is deprecated since WordPress v6.6.1
 */
const { PluginSidebar, PluginSidebarMoreMenuItem } = getComponentsBasedOnVersion(ow_gutenberg_sidebar_vars.wpVersion);


export class OasisWorkflowProComponent extends Component {
    constructor() {
        super(...arguments);

        this.pluginSettings = null;
        this.submitButtonText = "";

        this.state = {
            submitToWorkflowResponse: "",
            signOffResponse: "",
            hideSignoff: false,
            actionHistoryId: getActionHistoryIdFromURL(),
            hideClaimButton: true,
            hideCompareButton: true,
            hidePlugin: true,
            isRedirectModalOpen: false,
            hidePluginMessage: __("Loading...", "oasisworkflow")
        };

        this.showWorkflowSideBar = this.showWorkflowSideBar.bind(this);
    }

    componentDidMount() {
        let postId = this.props.postId;

        if (this.props.postMeta == undefined) {
            let hidePluginMessage = <CustomPostTypeSupportMessage />;
            this.setState({
                hidePlugin: true,
                hidePluginMessage
            });
            return;
        }

        // hide the Publish button when loading the Workflow section
        [].forEach.bind(document.getElementsByClassName("editor-post-publish-panel__toggle"), function (itm) {
            itm.style.display = "none";
        })();

        // hide the Update button which allows to update a published post
        [].forEach.bind(document.getElementsByClassName("editor-post-publish-button"), function (itm) {
            itm.style.display = "none";
        })();

        this.props.setIsPostInWorkflow({ isPostInWorkflow: this.props.postMeta._oasis_is_in_workflow });

        // reset the errors and sucess
        this.setState({
            submitToWorkflowResponse: "",
            signOffResponse: ""
        });

        // check for claim
        if (this.state.actionHistoryId) {
            apiFetch({
                path: "/oasis-workflow/v1/workflows/claim/actionHistoryId=" + this.state.actionHistoryId,
                method: "GET"
            }).then(
                (data) => {
                    this.setState({
                        hideClaimButton: data.is_hidden
                    });
                },
                (err) => {
                    console.log(err);
                    return err;
                }
            );
        }

        apiFetch({
            path: "/oasis-workflow/v1/workflows/documentRevision/checkCompareCapability/postId=" + postId,
            method: "GET"
        }).then(
            (revisiondata) => {
                this.setState({
                    hideCompareButton: revisiondata.is_hidden
                });
            },
            (err) => {
                console.log(err);
                return err;
            }
        );

        apiFetch({
            path:
                "/oasis-workflow/v1/workflows/submit/checkRoleCapability/postId=" +
                this.props.postId +
                "/postType=" +
                this.props.postType,
            method: "GET"
        }).then(
            (response) => {
                if (
                    (response.can_skip_workflow && !this.props.postMeta._oasis_original) ||
                    !response.is_role_applicable
                ) {
                    // the post is a revision post, then hide Publish
                    // show the Publish button when loading the Workflow section
                    [].forEach.bind(
                        document.getElementsByClassName("editor-post-publish-panel__toggle"),
                        function (itm) {
                            itm.style.display = "block";
                        }
                    )();

                    // show the Update button which allows to update a published post
                    [].forEach.bind(document.getElementsByClassName("editor-post-publish-button"), function (itm) {
                        itm.style.display = "block";
                    })();
                }

                // revert back and show publish button when "Disable workflows for Revisions" option are checked.
                if (response.disable_workflow_4_revision && response.disable_workflow_4_revision === true) {
                    // show the Publish button when loading the Workflow section
                    [].forEach.bind(
                        document.getElementsByClassName("editor-post-publish-panel__toggle"),
                        function (itm) {
                            itm.style.display = "block";
                        }
                    )();
                }

                if (response.is_role_applicable && !response.can_ow_sign_off_step) {
                    this.setState({
                        hideSignoff: true
                    });
                }

                // Display workflow sidebar as per global settings and applicable role capability
                if (response.is_role_applicable && response.can_submit_to_workflow) {
                    /**
                     * TODO: As of now, we are using setInterval to load the settings,
                     * Let's find a better way to handle this and get rid of the setInterval
                     */
                    var that = this;
                    var id = setInterval(function () {
                        if (!OWisEmpty(that.props.owSettings) && !OWisEmpty(that.props.userCap)) {
                            clearInterval(id);
                            that.setState({
                                hidePlugin: false,
                                hidePluginMessage: ""
                            });

                            // if the URL has action history ID and workflow sidebar is deafult display, then make the Oasis Workflow Sidebar as default sidebar to open.
                            let workflowSettings = that.props.owSettings.workflow_settings;
                            let showWorkflowSidebar = "hide";
                            if (workflowSettings !== undefined && workflowSettings.oasiswf_sidebar_display_setting) {
                                showWorkflowSidebar = workflowSettings.oasiswf_sidebar_display_setting;
                            }
                            if (
                                (that.state.actionHistoryId ||
                                    that.props.isCurrentPostPublished ||
                                    that.props.isCurrentPostScheduled) &&
                                showWorkflowSidebar === "show"
                            ) {
                                // console.log("activeGeneralSidebarName:" + that.props.activeGeneralSidebarName);
                                that.props.onOpenSideBar("oasis-workflow-pro-plugin/ow-workflow-sidebar");
                            }

                            // displays "Submit to Workflow" button on the header section
                            if (
                                !that.state.actionHistoryId &&
                                !that.props.postMeta._oasis_is_in_workflow &&
                                response.can_submit_to_workflow
                            ) {
                                that.submitButtonText = __("Submit to Workflow", "oasisworkflow") + "...";

                                // Check if the submit button div already exists
                                if (document.getElementsByClassName("owf-submit-button-div").length === 0) {
                                    var workflowButtonDiv = document.createElement("div");
                                    workflowButtonDiv.className = "owf-submit-button-div";
                                    workflowButtonDiv.innerHTML = `<button type="button" aria-label="${that.submitButtonText}"
                                        class="owf-submit-button components-button is-primary is-button is-large">
                                        ${that.submitButtonText}</button>`;

                                    var headerBarElement = document.getElementsByClassName("edit-post-header__settings")[0]
                                        || document.getElementsByClassName("editor-header__settings")[0];

                                    if (headerBarElement) {
                                        var headerBarChildElements = headerBarElement.getElementsByTagName("*");
                                        for (var i = 0; i < headerBarChildElements.length; i++) {
                                            if (
                                                typeof headerBarChildElements[i].className.indexOf === "function" &&
                                                headerBarChildElements[i].className.indexOf("editor-post-publish-panel") !== -1
                                            ) {
                                                headerBarChildElements[i].parentNode.insertBefore(
                                                    workflowButtonDiv,
                                                    headerBarChildElements[i].nextSibling
                                                );
                                                var element = document.getElementsByClassName("owf-submit-button")[0];
                                                element.onclick = that.showWorkflowSideBar;
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }, 500);
                } else {
                    let hidePluginMessage = __(
                        "Workflow actions are not available for this post type.",
                        "oasisworkflow"
                    );
                    if (!response.can_submit_to_workflow) {
                        hidePluginMessage = __("You are not allowed to submit to workflow.", "oasisworkflow");
                    }

                    this.setState({
                        hidePlugin: true,
                        hidePluginMessage
                    });
                }
            },
            (err) => {
                let hidePluginMessage = __("You are not allowed to submit to workflow.", "oasisworkflow");

                this.setState({
                    hidePlugin: true,
                    hidePluginMessage
                });
                console.log(err);
                return err;
            }
        );

    }

    // Redirect back if user don't have permission to edit published post
    pageBack() {
        window.history.back();
    }

    /**
     * displays the submit to workflow sidebar
     */
    showWorkflowSideBar() {
        // show the workflow sidebar
        this.props.onOpenSideBar("oasis-workflow-pro-plugin/ow-workflow-sidebar");
    }

    /**
     * handle workflow abort response
     */
    handleWorkflowAbortResponse() {
        this.setState({
            submitToWorkflowResponse: "",
            signOffResponse: ""
        });

        this.props.setIsPostInWorkflow({ isPostInWorkflow: false });
    }

    /**
     * handle Submit to Workflow response
     * @param {*} response
     */
    handleSubmitToWorkflowResponse(response) {
        this.setState({
            submitToWorkflowResponse: response.success_response
        });
        var submitToWorkflowButton = document.getElementsByClassName("owf-submit-button")[0];
        if (submitToWorkflowButton) {
            submitToWorkflowButton.classList.add("owf-guten-hidden");
        }

        this.props.setIsPostInWorkflow({ isPostInWorkflow: response.post_is_in_workflow });
    }

    /**
     * handle Signoff response
     * @param {*} response
     */
    handleSignoffResponse(response) {
        this.setState({
            signOffResponse: response.success_response,
            hideSignoff: true
        });

        this.props.setIsPostInWorkflow({ isPostInWorkflow: response.post_is_in_workflow });
    }

    render() {
        const {
            submitToWorkflowResponse,
            signOffResponse,
            actionHistoryId,
            hideClaimButton,
            hideCompareButton,
            hidePlugin,
            hidePluginMessage,
            hideSignoff
        } = this.state;
        const { isEditorialCommentActive } = this.props;

        return (
            <Fragment>
                <PluginSidebarMoreMenuItem target="ow-workflow-sidebar">
                    {__("Oasis Workflow", "oasisworkflow")}
                </PluginSidebarMoreMenuItem>
                <PluginSidebar name="ow-workflow-sidebar" title={__("Oasis Workflow", "oasisworkflow")}>
                    {hidePlugin ? (
                        <PanelBody>
                            <div id="owf-warning-message" className="notice notice-error">
                                {hidePluginMessage}
                            </div>
                        </PanelBody>
                    ) : (
                        <div>
                            <SignoffResponse response={signOffResponse} />
                            <div>
                                {!hideSignoff && actionHistoryId !== null && hideClaimButton ? (
                                    <Signoff handleResponse={this.handleSignoffResponse.bind(this)} />
                                ) : (
                                    ""
                                )}
                            </div>
                            <SignoffResponse response={submitToWorkflowResponse} />
                            <SubmitToWorkflow handleResponse={this.handleSubmitToWorkflowResponse.bind(this)} />
                            {actionHistoryId !== null ? <Reassign /> : ""}
                            <ClaimTask
                                isHidden={hideClaimButton}
                                actionHistoryId={actionHistoryId}
                                onClaim={() => this.setState((state) => ({ hideClaimButton: !state.hideClaimButton }))}
                            />
                            <AbortWorkflow handleResponse={this.handleWorkflowAbortResponse.bind(this)} />
                            <MakeRevision />
                            <CompareRevision isHidden={hideCompareButton} />
                            {isEditorialCommentActive ? <EditorialComments /> : ""}
                        </div>
                    )}
                </PluginSidebar>
            </Fragment>
        );
    }
}

const HOC = compose([
    withSelect((select) => {
        const {
            getCurrentPostId,
            getCurrentPostType,
            isCurrentPostPublished,
            isCurrentPostScheduled,
            getEditedPostAttribute,
            isSavingPost,
            isAutosavingPost
        } = select("core/editor");
        const editor = select("core/editor");
        const { getActiveGeneralSidebarName } = select("core/edit-post");
        const { getOWSettings, getUserCapabilities, getEditorialCommentsActivation } = select("plugin/oasis-workflow");
        return {
            postId: getCurrentPostId(),
            postType: getCurrentPostType(),
            postMeta: getEditedPostAttribute("meta"),
            isCurrentPostPublished: isCurrentPostPublished(),
            isCurrentPostScheduled: isCurrentPostScheduled(),
            activeGeneralSidebarName: getActiveGeneralSidebarName(),
            owSettings: getOWSettings(),
            userCap: getUserCapabilities(),
            isEditorialCommentActive: getEditorialCommentsActivation(),
            isSavingPost: isSavingPost(),
            isAutosavingPost: isAutosavingPost(),
            owStatus: getEditedPostAttribute('status'),
            editor
        };
    }),
    withDispatch((dispatch) => ({
        onSave: dispatch("core/editor").savePost,
        onOpenSideBar: dispatch("core/edit-post").openGeneralSidebar,
        setIsPostInWorkflow: dispatch("plugin/oasis-workflow").setIsPostInWorkflow
    }))
])(OasisWorkflowProComponent);

// const HOC = withDispatch((dispatch) => ({
// 	onSave: dispatch('core/editor').savePost
// }))(OasisWorkflowProComponent);

registerPlugin("oasis-workflow-pro-plugin", {
    icon: pluginIcon,
    render: HOC
});
