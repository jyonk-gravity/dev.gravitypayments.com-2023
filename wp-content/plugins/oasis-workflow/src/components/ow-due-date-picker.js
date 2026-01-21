/**
 * WordPress Dependencies
 */
import { DateTimePicker } from '@wordpress/components';
import { withSelect, withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';

export function OWDueDatePicker( { dueDate, onUpdateDate } ) {
	return (
		<DateTimePicker
			key="ow-date-picker"
			onChange={ onUpdateDate }
			currentDate={ dueDate }
		/>
	)
}

export default compose([
	withSelect((select) => {
		const { getDueDate } = select('plugin/oasis-workflow');
		return {
			dueDate: getDueDate(),
		};
	}),
	withDispatch( ( dispatch ) => {
		return {
			onUpdateDate( dueDate ) {
				dispatch( 'plugin/oasis-workflow' ).setDueDate( { dueDate } );
			},
		};
	} ),
])(OWDueDatePicker);