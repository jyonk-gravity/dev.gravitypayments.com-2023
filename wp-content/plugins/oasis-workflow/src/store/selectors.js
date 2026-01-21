/**
 * Returns the due date from the store
 * 
 * @param {Object} state Global application state.
 */
export function getDueDate( state ) {
   const dueDateReducer = state.dueDateReducer;
   return dueDateReducer.dueDate;
}

/**
 * Returns the OW Settings from the store
 * @param {Object} state 
 */
export function getOWSettings( state ) {
   const owSettingsReducer = state.owSettingsReducer;
   return owSettingsReducer.owSettings;
}

/**
 * Returns user custom capabilities from the store
 * @param {Object} state 
 */
export function getUserCapabilities( state ) {
   const userCapabilitiesReducer = state.userCapabilitiesReducer;
   return userCapabilitiesReducer.userCap;
}

/**
 * Returns Post is in workflow from the store
 * @param {Object} state 
 */
export function getPostInWorkflow( state ) {
   const postInWorkflowReducer = state.postInWorkflowReducer;
   return postInWorkflowReducer.isPostInWorkflow;
}