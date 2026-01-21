/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { PanelBody, PanelRow, Button } from '@wordpress/components';
import { compose } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';
import DOMPurify from "dompurify";

import { OWTrim, OWisEmpty } from "../util";

/**
 * Internal dependencies
 */

export class SignoffResponse extends Component {
	constructor() {
      super(...arguments);
   }

	/**
	 * handle redirect to the list page
	 */
	handleRedirectToListPage() {
		if (this.props.postType === 'post') {
			window.location.href = "edit.php";
		} else {
			window.location.href = "edit.php?post_type=" + this.props.postType;
		}
	}

	/**
	 * handle redirect to inbox page
	 */
	handleRedirectToInboxPage() {
		window.location.href = "admin.php?page=oasiswf-inbox";
	}   

   render() {
      const { response } = this.props;

      if (OWisEmpty(OWTrim(response))) {
         return "";
      }

      return(
         <div>
            <PanelBody>
               <div id="owf-success-message" className="notice notice-success is-dismissible">
                  {this.props.response}
               </div>
            </PanelBody>
            <PanelBody>   
               <PanelRow>
                  <p className="post-publish-panel__postpublish-subheader">
                     <strong>{ __( 'What’s next?', 'oasisworkflow' ) }</strong>
                  </p>
               </PanelRow>													
               <PanelRow>
                  <Button
                     focus = "true"
                     onClick = { this.handleRedirectToListPage.bind(this) }
                     isLink
                  >
                     { __( 'Take me to List page', 'oasisworkflow' ) }         
                  </Button>
               </PanelRow>													
               <PanelRow>
                  <Button
                     focus = "true"
                     onClick = { this.handleRedirectToInboxPage.bind(this) }
                     isLink
                  >
                     { __( 'Take me to Workflow Inbox', 'oasisworkflow' ) }         
                  </Button>																									
               </PanelRow>	
            </PanelBody>   
         </div>   
      )
   }

}

export default compose([
	withSelect((select) => {
		const { getCurrentPostType } = select('core/editor');
		return {
			postType: getCurrentPostType()
		};
		
	})
])(SignoffResponse);