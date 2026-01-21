/**
 * Sets the due date
 * 
 * @param {Object} data 
 */
export function setDueDate( data ){
   return {
      type : 'SET_DUE_DATE',
      dueDate : data.dueDate
   };
}

/**
 * Fetches OW Settings
 * @param {*} path 
 */
export function fetchOWSettings( path ) {
   return {
      type: 'FETCH_OW_SETTINGS',
      path
   }
}

/**
 * Sets OW Settings
 * @param {*} owSettings 
 */
export function setOWSettings(owSettings) {
   return {
      type: 'SET_OW_SETTINGS',
      owSettings
   }
}

/**
 * Fetches custom capabilities
 */
export function fetchUserCapabilities( path ) {
   return {
      type: 'FETCH_USER_CAPABILITIES',
      path
   }   
}

/**
 * Sets User Capabilities
 * @param {*} userCap 
 */
export function setUserCapabilities(userCap) {
   return {
      type: 'SET_USER_CAPABILITIES',
      userCap
   }
}

/**
 * Sets post is in workflow or not.
 * @param {*} data 
 */
export function setIsPostInWorkflow( data ){
   let isInWorkflow = false;
   if (data.isPostInWorkflow == '1') {
      isInWorkflow = true;
   }
   return {
      type : 'SET_POST_IS_IN_WORKFLOW',
      isPostInWorkflow : isInWorkflow
   };
}