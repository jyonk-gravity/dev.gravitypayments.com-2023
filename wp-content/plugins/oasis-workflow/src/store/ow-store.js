/**
 * WordPress Dependencies
 */
import { createReduxStore, register } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';

/**
 * Internal dependencies
 */
import reducer from "./reducer";
import * as selectors from "./selectors";
import * as actions from "./actions";

/**
 * Module Constants
 */
const MODULE_KEY = 'plugin/oasis-workflow';

const store = createReduxStore( MODULE_KEY, {
	reducer,
    selectors,
    actions,
    controls: {
		FETCH_OW_SETTINGS(action) {
            return apiFetch({ path: action.path });
        },
        FETCH_USER_CAPABILITIES(action) {
            return apiFetch({ path: action.path });
        }
   },
   resolvers: {
        *getOWSettings() {
            const path = "/oasis-workflow/v1/settings";
            const owSettings = yield actions.fetchOWSettings(path);
            return actions.setOWSettings(owSettings);
        },
        *getUserCapabilities() {
            const path = "/oasis-workflow/v1/usercap";
            const userCap = yield actions.fetchUserCapabilities(path);
            return actions.setUserCapabilities(userCap);
        }
    }
} );

register(store);