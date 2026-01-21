/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { withSelect } from '@wordpress/data';
import { compose } from '@wordpress/compose';
import { PanelRow } from '@wordpress/components';

export class PrePublishChecklist extends Component {
    constructor() {
        super(...arguments);

        this.state = {};
    }

    render() {
        return (
            <div>
                <h2>{__("Pre Publish Checklist ", "oasisworkflow") + ":"}</h2>
                {this.props.checklist.map((item) => (
                    <PanelRow key={item.value}>
                        <label>
                            <input type="checkbox" name="" onChange={this.props.onChange} value={item.value} />
                            {item.label}
                        </label>
                    </PanelRow>
                ))}
            </div>
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
])(PrePublishChecklist);
