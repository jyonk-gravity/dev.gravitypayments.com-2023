/**
 * WordPress Dependencies
 */
import { withSelect } from '@wordpress/data';
import { dateI18n } from '@wordpress/date';

export function OWDueDateLabel( { date } ) {
	return dateI18n( 'M d, Y', date ) ;
}

export default withSelect( ( select ) => {
	return {
		date: select( 'plugin/oasis-workflow' ).getDueDate()
	};
} )( OWDueDateLabel );