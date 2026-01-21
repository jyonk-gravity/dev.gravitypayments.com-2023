/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { compose } from '@wordpress/compose';
import { PanelBody, Button, Modal, PanelRow, TextareaControl, Spinner } from '@wordpress/components';
import { withSelect, withDispatch } from'@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import DOMPurify from "dompurify";

 export class AbortWorkflow extends Component {
 
    constructor() {
       super(...arguments);
 
       this.state = {
          buttonText: __("Abort Workflow", 'oasisworkflow'),
          hideButton: true,
          isOpen: false,
          isBusy: false,
          comments: '',
          success: [],
          submitSpinner: "hide",
          submitButtonDisable: false
       }
 
       this.handleWorkflowAbort = this.handleWorkflowAbort.bind(this);
    }
 
    /**
     * Show the Workflow Abort modal dialog
     */
    showAbortDialog() {
       this.setState({
          isOpen: true
       })
    }
 
    /**
     * handle form submit for workflow abort
     */
    handleWorkflowAbort(event) {
       this.setState({
          isBusy: true,
          submitButtonDisable: true,
          submitSpinner: "show"
       });
 
       let form_data = {
          post_id: this.props.postId,
          comments: this.state.comments
       };
 
       apiFetch({ path: '/oasis-workflow/v1/workflows/abort/', method: 'POST', data: form_data }).then(
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
          let buttonText = this.state.buttonText;
          if (customWorkflowTerminology) {
             buttonText = owSettings.terminology_settings.oasiswf_custom_workflow_terminology.abortWorkflowText;
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
       const { hideButton, isOpen, success, buttonText, submitButtonDisable, submitSpinner } = this.state;
       const { isPostInWorkflow } = this.props;
 
       // if post is NOT in workflow, then do not show abort button
       if (hideButton || !isPostInWorkflow) {
          return "";
       }
 
       return (
          <PanelBody>
             <Button
                focus="true"
                onClick={this.showAbortDialog.bind(this)}
                isLink
             >
                {buttonText}
             </Button>
             {isOpen && (
                <Modal
                   title={buttonText}
                   onRequestClose={() => this.setState({ isOpen: false })}>
                   {success.length !== 0 ?
                      (
                         <div>
                            <div id="owf-success-message" className="notice notice-success is-dismissible">
                               {success.map(message => <p key={message}>{message}</p>)}
                            </div>
                            <PanelRow>
                               <Button
                                  isPrimary
                                  focus="true"
                                  onClick={this.handleClose.bind(this)}
                               >
                                  {__('Close', 'oasisworkflow')}
                               </Button>
                            </PanelRow>
                         </div>
                      ) :
                      <form className="reusable-block-edit-panel" onSubmit={this.handleWorkflowAbort}>
                         <TextareaControl
                            label={__('Comments', 'oasisworkflow') + ':'}
                            value={this.state.comments}
                            onChange={(comments) => this.setState({ comments })}
                         />
                         <PanelRow>
                            <Button isLink onClick={() => this.setState({ isOpen: false })}>
                               {__('Cancel', 'oasisworkflow')}
                            </Button>
                            <Button
                               type="submit"
                               isBusy={this.state.isBusy}
                               isPrimary
                               disabled={submitButtonDisable}
                               focus="true"
                            >
                               {buttonText}
                            </Button>
                            {submitSpinner == "show" ?
                               (
                                  <Spinner />
                               ) : ""
                            }
                         </PanelRow>
                      </form>
                   }
                </Modal>
             )}
          </PanelBody>
       )
    }
 }
 
 export default compose([
    withSelect((select) => {
       const { getCurrentPostId, getEditedPostAttribute } = select('core/editor');
       const { getOWSettings, getUserCapabilities, getPostInWorkflow } = select('plugin/oasis-workflow');
       return {
          postId: getCurrentPostId(),
          postMeta: getEditedPostAttribute('meta'),
          owSettings: getOWSettings(),
          userCap: getUserCapabilities(),
          isPostInWorkflow: getPostInWorkflow()
       };
    }),
    withDispatch((dispatch) => ({
       onSave: dispatch('core/editor').savePost
    }))
 ])(AbortWorkflow);