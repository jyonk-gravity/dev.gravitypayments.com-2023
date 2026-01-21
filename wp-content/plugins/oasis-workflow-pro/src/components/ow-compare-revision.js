/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { Button, PanelBody } from '@wordpress/components';
import { withSelect, withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';
import apiFetch from '@wordpress/api-fetch';

export class CompareRevision extends Component {
    constructor() {
        super(...arguments);
    }

    displayRevisionCompare(event) {
        // event.preventDefault();
        this.props.onSave();
        apiFetch({ path: "/oasis-workflow/v1/workflows/documentRevision/compare", method: "GET" }).then(
            (revisionData) => {
                let revision_id = this.props.postId;
                // open a popup window with 90% height and width. We will use this window to show the revision compare results.
                let h = (screen.height * 90) / 100;
                let w = (screen.width * 90) / 100;

                // link to the revision compare page
                let revisionLink =
                    revisionData.absoluteURL +
                    "post.php?page=oasiswf-revision&revision=" +
                    revision_id +
                    "&_nonce=" +
                    revisionData.nonce;

                let compareWindow = window.open(
                    "about:blank",
                    "Revision_Compare",
                    "height=" + h + ",width=" + w + ",scrollbars=yes"
                );
                let link = `${revisionData.revisionPrepareMessage} <a href='${revisionLink}'>${revisionData.clickHereText}</a>`;
                compareWindow.document.write(link);
                let delay = 2000; //2 seconds, just to give the save-post a chance to save the post before it's used for compare
                setTimeout(function () {
                    compareWindow.location.assign(revisionLink);
                    return false;
                }, delay);
            }
        );
    }

    render() {
        return (
            <div>
                {!this.props.isHidden ? (
                    <PanelBody>
                        <Button focus="true" variant="link" onClick={this.displayRevisionCompare.bind(this)}>
                            {__("Compare With Original", "oasisworkflow")}
                        </Button>
                    </PanelBody>
                ) : (
                    ""
                )}
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
    }),
    withDispatch((dispatch) => ({
        onSave: dispatch("core/editor").savePost
    }))
])(CompareRevision);
