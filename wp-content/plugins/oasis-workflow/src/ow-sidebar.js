/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n';
import { Fragment, Component } from '@wordpress/element';
import { compose } from '@wordpress/compose';
import { PanelBody } from '@wordpress/components';
import { withSelect, withDispatch } from '@wordpress/data';
import { registerPlugin } from '@wordpress/plugins';
import apiFetch from '@wordpress/api-fetch';

/**
 * Conditionally import PluginSidebar components based on WordPress version
 * WordPress 6.6+ moved these to @wordpress/editor
 * WordPress 6.5 and earlier has them in @wordpress/edit-post
 */

// Get WordPress version and check if it's 6.6 or later
const wpVersion = window.OWBlockEditorVars?.wpVersion || '';

// console.log('Oasis Workflow Pro --- WordPress version: ' + wpVersion);

let PluginSidebar, PluginSidebarMoreMenuItem;

if ( wpVersion.startsWith('6.5') ) {
	// WordPress 6.5 and earlier: import from @wordpress/edit-post
	const editPostComponents = require('@wordpress/edit-post');
	PluginSidebar = editPostComponents.PluginSidebar;
	PluginSidebarMoreMenuItem = editPostComponents.PluginSidebarMoreMenuItem;
} else {
	// WordPress 6.6+: import from @wordpress/editor
	const editorComponents = require('@wordpress/editor');
	PluginSidebar = editorComponents.PluginSidebar;
	PluginSidebarMoreMenuItem = editorComponents.PluginSidebarMoreMenuItem;
}

/**
 * Internal dependencies
 */
import AbortWorkflow from "./components/ow-abort";
import SubmitToWorkflow from "./components/ow-submit-to-workflow";
import SignoffResponse from "./components/ow-signoff-response";
import ClaimTask from "./components/ow-claim";
import Signoff from "./components/ow-signoff";
import CustomPostTypeSupportMessage from "./components/custom-post-type-support";
import WorkflowRevisionFeatureMessage from "./components/workflow-revision-feature-message";
import { getActionHistoryIdFromURL, pluginIcon, OWisEmpty } from "./util";

export class OasisWorkflowProComponent extends Component {

	constructor() {
		super(...arguments);

		this.pluginSettings = null;
		this.submitButtonText = '';

		this.state = {
			submitToWorkflowResponse: '',
			signOffResponse: '',
			hideSignoff: false,
			actionHistoryId: getActionHistoryIdFromURL(),
			hideClaimButton: true,
			hidePlugin: true,
			hidePluginMessage: __('Loading...', 'oasisworkflow')
		}

		this.showWorkflowSideBar = this.showWorkflowSideBar.bind(this);

	}

	componentDidUpdate(prevProps) {
		const changed =
			prevProps.postId !== this.props.postId ||
			prevProps.postStatus !== this.props.postStatus;

		if (changed) {
			if (this.props.postId && this.props.isPublishedByStatus) {
				this.setState({ hidePlugin: true, hidePluginMessage: <WorkflowRevisionFeatureMessage /> });
			} else if (this.state.hidePlugin) {
				this.setState({ hidePlugin: false, hidePluginMessage: '' });
			}
		}
	}

	componentDidMount() {
		let postId = this.props.postId;

		if (postId && this.props.isPublishedByStatus) {
			let hidePluginMessage = <WorkflowRevisionFeatureMessage />
			this.setState({
				hidePlugin: true,
				hidePluginMessage
			});
			return;
		}

		if (this.props.postMeta == undefined) {
			let hidePluginMessage = <CustomPostTypeSupportMessage />
			this.setState({
				hidePlugin: true,
				hidePluginMessage
			});
			return;
		}

		this.props.setIsPostInWorkflow({ "isPostInWorkflow": this.props.postMeta._oasis_is_in_workflow });

		// reset the errors and sucess
		this.setState({
			submitToWorkflowResponse: '',
			signOffResponse: ''
		});

		// check for claim
		if (this.state.actionHistoryId) {
			apiFetch({ path: '/oasis-workflow/v1/workflows/claim/actionHistoryId=' + this.state.actionHistoryId, method: 'GET' }).then(
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

		apiFetch({ path: '/oasis-workflow/v1/workflows/submit/checkRoleCapability/postId=' + this.props.postId + '/postType=' + this.props.postType, method: 'GET' }).then(
			(response) => {
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
								hidePluginMessage: ''
							});
						}
					}, 500);

					if (!response.can_skip_workflow || this.props.postMeta._oasis_original) { // the post is a revision post, then hide Publish
						// hide the Publish button when loading the Workflow section
						[].forEach.bind(document.getElementsByClassName("editor-post-publish-panel__toggle"), function (itm) {
							itm.style.display = "none";
						})();

						// hide the Update button which allows to update a published post
						[].forEach.bind(document.getElementsByClassName("editor-post-publish-button"), function (itm) {
							itm.style.display = "none";
						})();
					}

					// if the URL has action history ID, then make the Oasis Workflow Sidebar as default sidebar to open.
					if (this.state.actionHistoryId) {
						// console.log("activeGeneralSidebarName:" + this.props.activeGeneralSidebarName);
						this.props.onOpenSideBar('oasis-workflow-plugin/ow-workflow-sidebar');
					}

					// displays "Submit to Workflow" button on the header section
					if (!this.state.actionHistoryId && !this.props.postMeta._oasis_is_in_workflow && response.can_submit_to_workflow) {
						this.submitButtonText = __("Submit to Workflow", 'oasisworkflow') + "...";

						// Check if the submit button div already exists
						if (document.getElementsByClassName("owf-submit-button-div").length === 0) {
							var workflowButtonDiv = document.createElement("div");
							workflowButtonDiv.className = "owf-submit-button-div";
							workflowButtonDiv.innerHTML = `<button type="button" aria-label="${this.submitButtonText}"
								class="owf-submit-button components-button is-primary is-button is-large">
								${this.submitButtonText}</button>`;
					
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
										element.onclick = this.showWorkflowSideBar;
										break;
									}
								}
							}
						}
					}
				} else {
					let hidePluginMessage = __("Workflow actions are not available for this post type.", 'oasisworkflow');
					if (!response.can_submit_to_workflow) {
						hidePluginMessage = __("You are not allowed to submit to workflow.", 'oasisworkflow');
					}

					this.setState({
						hidePlugin: true,
						hidePluginMessage
					});
				}
			},
			(err) => {
				console.log(err);
				return err;
			}
		);
	}

	/**
	 * displays the submit to workflow sidebar
	 */
	showWorkflowSideBar() {
		// show the workflow sidebar
		this.props.onOpenSideBar('oasis-workflow-plugin/ow-workflow-sidebar');
	}

	/**
	 * handle workflow abort response
	 */
	handleWorkflowAbortResponse() {

		this.setState({
			submitToWorkflowResponse: '',
			signOffResponse: ''
		});

		this.props.setIsPostInWorkflow({ "isPostInWorkflow": false });
	}

	/**
	 * handle Submit to Workflow response
	 * @param {*} response 
	 */
	handleSubmitToWorkflowResponse(response) {
		this.setState({
			submitToWorkflowResponse: response.success_response
		});
		var submitToWorkflowButton = document.getElementsByClassName('owf-submit-button')[0];
		submitToWorkflowButton.classList.add('owf-guten-hidden');

		this.props.setIsPostInWorkflow({ "isPostInWorkflow": response.post_is_in_workflow });
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

		this.props.setIsPostInWorkflow({ "isPostInWorkflow": response.post_is_in_workflow });
	}

	render() {
		const { submitToWorkflowResponse, signOffResponse, actionHistoryId, hideClaimButton, hideCompareButton, hidePlugin, hidePluginMessage, hideSignoff } = this.state;
		return (
			<Fragment >
				<PluginSidebarMoreMenuItem
					target="ow-workflow-sidebar"
				>
					{__('Oasis Workflow', 'oasisworkflow')}
				</PluginSidebarMoreMenuItem>
				<PluginSidebar
					name="ow-workflow-sidebar"
					title={__('Oasis Workflow', 'oasisworkflow')}
				>
					{hidePlugin ?
						(
							<PanelBody>
								<div id="owf-warning-message" className="notice notice-error">
									{hidePluginMessage}
								</div>
							</PanelBody>
						) :
						<div>
							<SignoffResponse response={signOffResponse} />
							<div>
								{(!hideSignoff && actionHistoryId !== null && hideClaimButton) ?
									<Signoff handleResponse={this.handleSignoffResponse.bind(this)} />
									: ""
								}
							</div>
							<SignoffResponse response={submitToWorkflowResponse} />
							<SubmitToWorkflow handleResponse={this.handleSubmitToWorkflowResponse.bind(this)} />
							<ClaimTask
								isHidden={hideClaimButton}
								actionHistoryId={actionHistoryId}
								onClaim={() => this.setState((state) => ({ hideClaimButton: !state.hideClaimButton }))}
							/>
							<AbortWorkflow handleResponse={this.handleWorkflowAbortResponse.bind(this)} />
						</div>
					}
				</PluginSidebar>
			</Fragment>
		);
	}
}

const HOC = compose([
	withSelect((select) => {
		const { getCurrentPostId,
			getCurrentPostType,
			isCurrentPostPublished,
			isCurrentPostScheduled,
			getEditedPostAttribute } = select('core/editor');
		const { getActiveGeneralSidebarName } = select('core/edit-post');
		const { getOWSettings, getUserCapabilities } = select('plugin/oasis-workflow');
		const status = getEditedPostAttribute('status');
		return {
			postId: getCurrentPostId(),
			postType: getCurrentPostType(),
			postMeta: getEditedPostAttribute('meta'),
			postStatus: status,
			isPublishedByStatus: ['publish', 'private', 'future'].includes(status),
			isCurrentPostPublished: isCurrentPostPublished(),
			isCurrentPostScheduled: isCurrentPostScheduled(),
			activeGeneralSidebarName: getActiveGeneralSidebarName(),
			owSettings: getOWSettings(),
			userCap: getUserCapabilities()
		};

	}),
	withDispatch((dispatch) => ({
		onSave: dispatch('core/editor').savePost,
		onOpenSideBar: dispatch('core/edit-post').openGeneralSidebar,
		setIsPostInWorkflow: dispatch('plugin/oasis-workflow').setIsPostInWorkflow
	}))
])(OasisWorkflowProComponent);

// const HOC = withDispatch((dispatch) => ({
// 	onSave: dispatch('core/editor').savePost
// }))(OasisWorkflowProComponent);

registerPlugin('oasis-workflow-plugin', {
	icon: pluginIcon,
	render: HOC
});