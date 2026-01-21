/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { SelectControl } from '@wordpress/components';
import { withSelect } from '@wordpress/data';
import { compose } from '@wordpress/compose';

export class TeamSelectControl extends Component {
    constructor() {
        super(...arguments);
    }

    render() {
        return (
            <SelectControl
                label={__("Assign To Team", "oasisworkflow")}
                value={this.props.value}
                options={this.props.options}
                onChange={this.props.onChange}
            />
        );
    }
}

export default compose([
    withSelect((select) => {
        const { getCurrentPostId } = select("core/editor");
        return {
            postId: getCurrentPostId()
        };
    })
])(TeamSelectControl);
