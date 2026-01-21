/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { Button, Modal, PanelRow, Spinner } from '@wordpress/components';

export class MakeRevisionExistOverlay extends Component {
    render() {
        return (
            <Modal title={this.props.buttonText} onRequestClose={this.props.revisionExist}>
                <PanelRow>
                    <p>
                        {__(
                            "An active revision already exists for this article. Do you want to delete the existing revised article and create a new revision?",
                            "oasisworkflow"
                        )}
                    </p>
                </PanelRow>
                <PanelRow>
                    <Button variant="link" disabled={this.props.revisionCancelDisable} onClick={this.props.revisionExist}>
                        {__("Cancel", "oasisworkflow")}
                    </Button>
                    <Button
                        type="submit"
                        variant="primary"
                        focus="true"
                        disabled={this.props.revisionNoDisable}
                        onClick={this.props.existingRevision}
                    >
                        {__("No, take me to the revision", "oasisworkflow")}
                    </Button>
                    <Button
                        type="submit"
                        variant="primary"
                        focus="true"
                        disabled={this.props.revisionOkDisable}
                        onClick={this.props.deleteRevision}
                    >
                        {__("Yes, delete it and create new one", "oasisworkflow")}
                    </Button>
                    {this.props.submitSpinner == "show" ? <Spinner /> : ""}
                </PanelRow>
            </Modal>
        );
    }
}

export default MakeRevisionExistOverlay;
