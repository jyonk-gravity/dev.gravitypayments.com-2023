/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { Button, PanelBody, Spinner } from '@wordpress/components';
import { withSelect } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';

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
      }

      this.handleClaim = this.handleClaim.bind(this);
   }

   /**
    * handle form submit for workflow abort
    */
   handleClaim(event) {
      this.setState({
         isBusy: true,
         submitSpinner: "show",
         submitButtonDisable: true
      });

      let postId = this.props.postId

      let form_data = {
         post_id: postId,
         history_id: this.props.actionHistoryId
      };

      apiFetch({ path: '/oasis-workflow/v1/workflows/claim/', method: 'POST', data: form_data }).then(
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
               window.location.href = ow_admin_url + 'post.php?post=' + postId + '&action=edit&oasiswf=' + new_history_id;
            }
         },
         (err) => {
            console.log(err);
            return err;
         }
      );
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
            {responseErrors.length !== 0 ?
               (<div id="owf-error-message" className="notice notice-error is-dismissible">
                  {responseErrors.map(error =>
                     <p key={error}>{error}</p>
                  )}
               </div>) : ""
            }
            {!this.props.isHidden ?
               (
                  <PanelBody>
                     <Button
                        focus="true"
                        isPrimary
                        disabled={submitButtonDisable}
                        onClick={this.handleClaim}
                     >
                        {__('Claim', 'oasisworkflow')}
                     </Button>
                     {submitSpinner == "show" ?
                        (
                           <Spinner />
                        ) : ""
                     }
                  </PanelBody>
               ) : ""
            }
         </div>
      )
   }
}

export default withSelect((select) => {
   const { getCurrentPostId } = select('core/editor');
   return {
      postId: getCurrentPostId()
   };
})(ClaimTask);