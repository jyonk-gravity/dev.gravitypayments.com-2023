/**
 * WordPress dependencies
 */
import { combineReducers } from '@wordpress/data';

export const DEFAULT_DUE_DATE = { dueDate : new Date() } // set current date as due date

const DEFAULT_OW_SETTINGS = {
   owSettings : {}
};

const DEFAULT_USER_SETTINGS = {
   userCap : {}
}

const DEFAULT_POST_IN_WORKFLOW = {
   isPostInWorkflow : false
}

/**
 * Reducer keeping track of due date
 * @param {Object} state Previous state.
 * @param {Object} action Action object.
 */
export function dueDateReducer( state = DEFAULT_DUE_DATE, action) {
   switch (action.type) {
      case 'SET_DUE_DATE': 
         return {
            ...state,
            dueDate: action.dueDate
         };
   }

   return state;
}

export function owSettingsReducer( state = DEFAULT_OW_SETTINGS, action) {
   switch (action.type) {
      case 'SET_OW_SETTINGS': 
         return {
            ...state,
            owSettings: action.owSettings
         };
   }

   return state;   
}

export function userCapabilitiesReducer( state = DEFAULT_USER_SETTINGS, action) {
   switch (action.type) {
      case 'SET_USER_CAPABILITIES': 
         return {
            ...state,
            userCap: action.userCap
         };
   }

   return state;   
}

/**
 * Reducer keeping track of is post in workflow
 * @param {Object} state Previous state.
 * @param {Object} action Action object.
 */
export function postInWorkflowReducer( state = DEFAULT_POST_IN_WORKFLOW, action) {
   switch (action.type) {
      case 'SET_POST_IS_IN_WORKFLOW': 
         return {
            ...state,
            isPostInWorkflow: action.isPostInWorkflow
         };
   }

   return state;
}

export default combineReducers( {
   dueDateReducer,
   owSettingsReducer,
   userCapabilitiesReducer,
   postInWorkflowReducer
} );