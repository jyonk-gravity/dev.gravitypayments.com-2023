/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { Button, Modal, PanelRow, Spinner } from '@wordpress/components';

export class MakeRevisionOverlay extends Component {
    render() {
        return (
            <Modal title={this.props.buttonText} onRequestClose={this.props.pageBack}>
                <PanelRow>
                    <p>{__(this.props.revisionOverlayText)}</p>
                </PanelRow>
                <PanelRow>
                    <Button variant="link" onClick={this.props.pageBack}>
                        {__("Cancel", "oasisworkflow")}
                    </Button>
                    <Button
                        type="submit"
                        variant="primary"
                        focus="true"
                        disabled={this.props.revisionButtonDisable}
                        onClick={this.props.checkExistingRevision}
                    >
                        {this.props.buttonText}
                    </Button>
                    {this.props.submitSpinner == "show" ? <Spinner /> : ""}
                </PanelRow>
            </Modal>
        );
    }
}

export default MakeRevisionOverlay;
