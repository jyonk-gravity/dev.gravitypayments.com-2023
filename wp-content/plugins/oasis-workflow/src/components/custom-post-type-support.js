/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'

const CustomPostTypeSupportMessage = props => (
   <div>
      {__( 'If this is a custom post type, check this page: ', 'oasisworkflow' ) }  
		<a target="_blank" href="https://www.oasisworkflow.com/documentation/working-with-workflows/custom-post-type-support">custom post type support</a>
		{__(' for more information on how to make custom post type work with Gutenberg Editor.', 'oasisworkflow' )}
   </div>
);

export default CustomPostTypeSupportMessage;