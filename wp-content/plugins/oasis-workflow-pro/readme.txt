=== Oasis Workflow Pro ===
Contributors: nuggetsol
Tags: workflow, work flow, review, assignment, publish, inbox, workflow history, audit
Requires at least: 4.4
Tested up to: 6.9
Stable tag: 10.4

Automate your WordPress Editorial Workflow with Oasis Workflow.

== Description ==

Any online publishing organization has one or several Managing Editors responsible for keeping the arrangement of editorial content flowing in an organized fashion.

Oasis Workflow plugin is designed to automate any workflow process using a simple, intuitive graphical user interface (GUI).

The plugin provides three processes:

1. Assignment - represents task related to content generation.

2. Review - represents task related to content review.

3. Publish - represents the actual "publish" task.

**Visual Work flow Designer**
 - Configure your work flow using the easy drag and drop designer interface. See screen shots for more detail.

**Role-Based routing definitions allow you to assign tasks dynamically**
 - By using role-based routing, you can ensure that your process moves forward as quickly as possible without sacrificing accountability.

**Inbox**
 - Users can view their current assignments and sign off their tasks once it's completed.

**Process history lets users retrace their steps**
 - For auditing purposes a record is maintained of all posts that are routed through a workflow process. The process history also captures the comments added by the user when they signed off the particular task.

**Reassign - How to pass the buck?**
 - What if you have been assigned a workflow task, but you feel you are not the appropriate person to complete it? No worry, you can assign the task to another person. 

**Due Date and Email reminders** help you to publish your articles on time.

**Out of the box workflow**
To get you started, the plugin comes with an out of the box workflow. You can also modify the workflow to suit your needs. 

You can find the complete list of features on the [support](http://oasisworkflow.com) site.

**Supported languages**
 - English
 - Spanish
 - French
 
**Translators**
* French (fr_FR) - [Baptiste Rieg](http://www.batrieg.com)

**If you need help setting up the roles, we recommend the [User Role Editor plugin](http://wordpress.org/extend/plugins/user-role-editor/ "User Role Editor plugin").**

Videos to help you get started with Oasis Workflow:

[youtube http://www.youtube.com/watch?v=PPBJns2p-zU]

[youtube http://www.youtube.com/watch?v=SuOCBf_mLpc]

== Installation ==

1. Download the plugin zip file to your desktop
2. Upload the plugin to WordPress
3. Activate your license by going to Workflow Admin --> Settings --> License Settings
4. Activate Oasis Workflow by going to Workflow Admin --> Settings --> Workflow Settings 
5. You are now ready to use Oasis Workflow! Build Your Workflow and start managing your editorial content flow.

== Frequently Asked Questions ==

For [Frequently Asked Questions](http://oasisworkflow.com/faq) plus documentation, plugin help, go [here](http://oasisworkflow.com)

== Screenshots ==

1. Visual Work flow designer
2. Role-based routing
3. Inbox
4. Sign off
5. Process history


== Changelog ==

= Version 10.4 =
* Fixed & Update: plugin version constant ( OASISWF_VERSION and OASISWF_DB_VERSION ) value was incorrect on last update v10.3

= Version 10.3 =
* Fixed & Update: "Permanently delete the revision immediately after the workflow is completed" with using admin_enqueue_scripts istead owf_workflow_complete hook.
* Fixed duplicate URL issue during redirection after workflow completion from the inbox.
* Fixed & Updated: Frontend sign-off and form submission now overwrite default form field styles coming from the theme or plugins.
* Added new hooks inside `OW_Process_Flow()->submit_post_to_step_internal`: `owf_before_step_sign_off`, `owf_review_step_signoff`, `owf_individual_signoff_approved` and `owf_assignment_step_signoff`
* Changes `oasis-workflow/v1/usercap` permission_callback to `OW_Utility::instance()->can_use_workflows()`
* Added elementor_make_revision_overlay to show "Make Revision" modal on Elementor editor screen when post is published and don't have `owf_skip_workflow` caps.
* Changes trigger console.error when post/page save failed due to permission issue in block editor.
* Added `team_id` to the `$step_details` array in `api_get_step_details` to prevent a 500 error when using team add-ons.

= Version 10.2 = 
* Fixed "submit to workflow" button duplicate issue by adding two later condition in `ow-sidebar.js` Now, before adding the button code will check if it's already exists or not.
* Fixed undefined variable `user_specified_publish_date` issue in Elementor frontend editor view and frontend inbox.
* Added %post_id% & {post_id} into Workflow email placeholders.
* Added new option to "Document Revision" called "Permanently delete the revision immediately after the workflow is completed" using `owf_workflow_complete` hook
* Added new option to "Document Revision" called "Force to show the preview URL on the revision"
* Added new option to "Document Revision" called "Allow title update on revision"
* Added new option to "Workflow Settings" called "Display comment box for the last step of workflow"
* Added language file generate by npm command


= Version 10.1 =
* Updated to ensure compatibility with Elementor's "Editor Top Bar" feature.
* Added a filter to allow new filters in the inbox. The filter hooks owf_filter_inbox_items_where_clause and owf_inbox_parameters, and the action hook owf_inbox_filter_lists.
* Fixed "submit to workflow" button will be disabled until the form has fully loaded or a workflow option is selected.
* Added a filter to disabled specific email with subject Or user_id `oasiswf_allow_email_send`

= Version 10.0 =
* Fixed v9.9 infinite loading issue
* Update/Improve processSignoffQueue condition.
* Fixed OW_API declaring keyword issue.

= Version 9.9 =
* Fixed security issue on Reports page.
* Fixed security issue on Inbox page.
* Added `OW_Revision_Service()->owf_get_hidden_revisions` to delete revisions immediately.
* Fixed `copy_post_addslashes_deep` error for PHP v8+.
* Fixed `json_encode` error in `review_step_procedure` for PHP v8+.
* Added a new method called `change_workflow_status_to_complete_internal_cb`.
* Fixed "Submit to Workflow" not working in Classic Editor.
* Updated `acf-pro-validator-new.js` to fix ACF validation in Classic Editor when the submit button is triggered.
* Fixed "Submit to Workflow" button not showing in the block editor's top right toolbar.
* Fixed deprecated warning & conflict using PluginSidebar & PluginSidebarMoreMenuItem for WP v6.6.1 and less in ow-sidebar.js
* Updated Button props isPrimary, isLink, isSecondary with variant props https://github.com/WordPress/gutenberg/blob/trunk/packages/components/CHANGELOG.md#deprecations-10


= Version 9.8 =
* Replaced Javascript library wp.** with @wordpress/** JavaScript packages.
* Added filter to ignore taxonomies on revision called `owf_unset_revisions_taxonomies`
* Added filter to ignore taxonomies on duplicate called `owf_unset_duplications_taxonomies`
* Added filter to show duplicate button by custom status using `owf_allow_duplicate_by_status`
* Added hook for to inbox page top tablenav actions `owf_inbox_tablenav_actions_before` and `owf_inbox_tablenav_actions_after`
* Fixed workflowSubmitWithACF issue by changing it to async/await
* Fixed performance issue on signoff by moving `subscribe()` move into componentDidMount
* Fixed deprecation warning  OW_Action_History::$history_meta, OW_Email_Settings::$assignment_email_intervals & OW_Workflow::$post_count
* Added filter `owf_get_roles_option_list` and `owf_get_users_option_list_args`
* Fixed acf validation dependency on block editor js file.

= Version 9.7 =
* Fixed multiple ajax request on block editor.
* Fixed wp.data.subscribe stop firing after first time to avoid sending multiple requests.
* Fixed same item showing multiple inside inbox.
* Added try catch for `api_submit_to_step` & `submit_post_to_step` to avoid multiple request.
* Added transient check for `api_submit_to_step` & `submit_post_to_step` to avoid multiple request at the same time.
* Added `get_review_action_by_actor_with_history` method inside OW_History_Service class
* Fixed Workflow Settings causing error if there's not value for checkbox fields
* Made the plugin compatible with WP v6.4+
* Removed dependency on lodash and replaced with vanilla js function that placed in src/util.js
* Removed `__experimentalGetSettings` and replaced with getSettings().

= Version 9.6 =
* Added `oasiswf_custom_placeholders_list` filter hook handle custom placeholder with extra parameters
* Added `ow_restrict_edit_for_non_assignees` filter hook to control non-assigned user's right to save on class editor.
* Fixed Elementor edit page js variable not defined error on console.
* Fixed import issue with assignees.
* Fixed import array_map issue.
* Added `owf_export_data` hook to filter export data
* Added `owf_import_settings_items` hook to filtering export settings data.
* Added `sanitize_array` new method to `OW_Utility` to sanitize multidimensional array.
* Changed condition for `get_current_revision` add extra condition to check if revision post even exists or not.
* Changed split import function.
* Made `OW_Workflow_Service->delete_workflow_steps` from private to public.
* Fixed async issue if post saving are delaying for some reason with wp.data.subscribe.


= Version 9.5 =
* Updated NPM packages and dependencies
* Fix Elementor is_built_with_elementor due to deprecation.
* Fixed `get_unsubmitted_posts` query.
* Fixed `owf_submit_to_workflow_pre` filter on classic editor.
* Added `owf_post_status_domain` filter for add post_status domain.

= Version 9.4 =
* Fixed block editor status changed issue with 'wp_insert_post' hook
* Fixed to remove unnecessary enqueue script loading in block editor.
* Modified to get acf active or not in block editor.
* Fixed revision button condition in classic editor.
* Added extra condition if not in workflow for `ow_revision_update_slug`
* Added hook to change inbox default orderby `owf_default_inbox_items_orderby`
* Added hook to add or change inbox default order `owf_default_inbox_items_order`
* Added hook to add or change inbox default orderby clauses `owf_filter_inbox_items_order_by`
* Added 'post_date' in inbox default orderby clauses list.
* Fixed post status term conflict with others taxonomy - `ow_post_status_slug`

= Version 9.3 =
* Added extra parameter to 'owf_workflow_abort' hook called.
* Fixed abort comment not sending to the mail issue.
* Updated workflow list page default value as 'active' instead 'all'
* Added new filter hook 'ow_workflow_list_default' so user can change workflow list page default list.
* Added missing 'upgrade_database_63' method.

= Version 9.2 =
* Fixed cc & bcc error on workflow step settings.
* Fixed uninstall/delete plugin issue.
* Fixed required ACF field issue on classic and block editor.
* Fixed ACF validation ajax issue.
* Added extra arguments to 'ow_redirect_after_signoff_url' hook.
* Added extra arguments to 'ow_workflow_completed' hook.
* Added hook to rename "post_status" taxonomy slug - 'ow_post_status_slug'.

= Version 9.1 =
* Fixed WordPress Coding Standard/PHPCS.
* Added hook to check if role is applicable - 'ow_is_role_applicable'.
* Added hook for redirection url after workflow complete 'ow_redirect_after_signoff_url'.
* Added hook for triggering JS after workflow complete 'ow_workflow_completed'.
* Added CC & BCC recipients options for assignment and reminder email in workflow step settings.
* Fixed issue with editing of Custom Statuses.

= Version 9.0 =
* Fixed some translation issue.
* Updating plugin to be compatible with PHP 8.x

= Version 8.9 =
* Fixed Due Date field issue in block editor/gutenberg.
* Add nudge option to inbox.

= Version 8.8 =
* Fixed Reusable blocks post type supports parameters.
* Add cc and bcc for assignment email.
* Changed cc and bcc value requirement. Now you can pass email address or user id.
* Fixed escaping and translation issues.
* Updated languages files.

= Version 8.7 =
* Fixed issue with browser popup related to "Are you sure you want to leave without saving" showing up during reassign and sign off.
* Fixed CSS issue with Inbox All/Mine.
* Fixed inbox popup issue when team are used.
* Set workflow sidebar as default.

= Version 8.6 =
* Fixed issue with <br> showing up in comments.
* Changed drop down for task assignment to be more intuitive.
* Added more functionality to the Elementor integration. Now you can reassign and abort from Elementor UI.
* Fixed issue with "Save Draft" causing to loose the workflow history ID.

= Version 8.5 =
* Changed temp upload location to upload folder to be compatible with WP VIP Platform.
* Changed some queries to make it more efficient.
* Fixed issue with Revision Compare UI/CSS.
* Fixed missing image.

= Version 8.4 =
* Security Fix - Fixed output escaping issue where some of the strings were not properly escaped.
* REST API Security - Added capability security to publicly accessible API endpoints.
* PHP to JS Escaping - Javascript variables printed and defined through PHP variables were properly sanitized and escaped.
* Unprepared SQL Queries - Fixed instances of SQL queries that were not properly escaped and prepared.
* Added filters to allow for assignment and reminder email custom content.
* Fixed issue with Priority field not getting set from the previous step in Gutenberg Editor.
* Fixed issue with Teams drop down, not showing all team users.

= Version 8.3 =
* Fixed german localization text.
* Made it compatible with WP 5.6.

= Version 8.2 =
* Added support for "Checklist Warning" feature.
* Fixed issue with Applicable Post Types checkboxes checked automatically.

= Version 8.1 =
* Removed Browse Revisions link from Posts List Page for performance reasons.
* Fixed issue with display due date in Gutenberg.

= Version 8.0 =
* Added new filters/actions to allow for customization.
* Fixed issue with due date settings.

= Version 7.9 =
* Hide the Publish button, until the Submit to Workflow button is visible.
* Added a new hook to allow hiding/showing of "Submit to Workflow" based on post attributes.
* Sanitized workflow comments to disallow certain tags.
* Fixed permalink issue for revision post without suffix/prefix.
* Fixed overlap of External User Id with WP User Id.

= Version 7.8 =
* Fixed bugs related to JQuery attribute and prop checks.

= Version 7.7 =
* Reverted a change related to hiding the Publish button until the "Submit to Workflow" shows up.

= Version 7.6 =
* Hide the Publish button, until the Submit to Workflow button is visible.
* Made it compatible with WP 5.5
* Fixed issues with unicode characters in the content.

= Version 7.5 =
* Merged all the text-domain to a single type.
* Show/Hide the Action options depending on the step connections.

= Version 7.4 =
* Added help text for Oasis Workflow sidebar.
* Added German translation file.
* Fixed issue with a user/usermeta query to account for the case where users DB is not in the same DB as the posts.
* Improved Elementor integration by adding support for Teams and "Edit with Elementor".
* Fixed an issue with conditional check for ow_make_revision and ow_make_revision_others.

= Version 7.3 =
* Added config to toggle workflow comments to be mandatory. By default the comments will not be required.
* Integrated with Elementor. Now, the user can submit to workflow and sign off from within Elementor UI.

= Version 7.2 =
* Display and link unclaimed task count on dashboard widget.
* Enhanced Settings page to manage External Users (non-WordPress users) for email notifications.
* Enhanced email notifications by adding CC and BCC settings.
* Added capability check to import/export functionality for additional security.
* Allow meta fields to be edited for Gutenberg Editor.
* Fixed issue with Jetpack Publicize not getting invoked when task is signed off from the post edit page.
* Fixed potential issue with update_option not sanitizing certain input values.
* Fixed errors while copying revision post meta values to original post.

= Version 7.1 =
* Fixed caching issues.

= Version 7.0 =
* Allow user to reassign from classic editor publish metabox.
* Deny creating custom statuses that are interfering with the core ones like draft, etc.
* Fixed claim when user opens post via assignment email.

= Version 6.9 =
* Added Inbox row action "Claim and Edit".
* Allow user to filter Inbox assignments.
* Hide the no-action activities by default.
* Enhanced Reports by adding "Task By Due Date" report.
* Fixed workflow history post filter with hide/show of no-action activities.
* Fixed displaying of Reassign popup if no user are available.
* Fixed issue with post parent still pointing to the revision post in case of attachments.

 = Version 6.8 =
* Made it compatible with Wordpress Version 5.3.
* Fixed the issue with post status getting updated before the review step was complete.

= Version 6.7 =
* Allow user to reassign from oasis workflow gutenberg sidebar.
* Abort post from workflow if user published the post midway of workflow process.
* Redirect user to either post list page or workflow inbox after workflow submit and sign-off.
* Added loader for each API request and submission.
* Fixed undefine URLSearchParams in IE.

= Version 6.6 =
* Fixed issue with workflow history unclaimed activities transient.
* Fixed display of "Make Revision" button if there is only one applicable revision workflow.

= Version 6.5 =
* Allow user to save and continue to work on the workflow editor without closing the page.
* Allow user to hide and show workflow history unclaimed activities.
* Display error message on the page if task is already claimed by another user.
* Fixed localization issues with workflow process button names.
* Display applicable post types and roles that can submit to workflow as per global settings.
* Modified fc_action database table definition to be compatible with Maria DB.
* Fixed "revision already exist" modal to close after action is performed.

= Version 6.4 =
* Fixed check of role participating in workflow to consider multiple roles.
* Change hook name that support custom fields on last sign off step.
* Allow users to sign-off from Inbox page even when they don't have edit_others_posts capability.
* Fixed "View" link from Inbox page to show up if the user has the right capabilities.

= Version 6.3 =
* Fixed roles capability check to allow underscore in post type names.
* Enhanced auto submit to search by taxonomy and terms.
* Enhanced signoff action to allow user to add custom action names at step info setup.
* Added functionality to support custom fields on last sign off step.
* Added workflow settings to display workflow sidebar as default for gutenberg editor.
* Hide owf_schedulerev from the list of allow post statuses on step transition.

= Version 6.2 =
* Fixed roles capability check to consider multiple roles.
* Fixed revision process for custom post type.
* Added hook to filter users for assignment and reminder emails.
* Added custom cron intervals for Email digest.

= Version 6.1 =
* Fixed roles capability check.
* Fixed Assign to all not working in step assignment with Gutenberg.
* Display warning message if "Show in Rest" and "custom field support" is not set for Custom Post Type.
* Added hooks for custom header and values to the workflow history download csv file.
* Fixed email service invocation to be compatible with Gutenberg Editor.

= Version 6.0 =
* Made it compatible with Gutenberg Editor.
* Added hook "oasiswf_emails_placeholders" and "oasiswf_emails_placeholders_handler" for emails settings to support custom placeholders.
* Fixed index issue for user roles in step_signoff_popup_setup() and check_is_role_applicable()

= Version 5.8 =
* Fixed revision post to save page builder elements postmeta like unyson sections.
* Add a custom role called - Post Submitter.
* Enhanced applicable roles logic, so that only applicable roles and post types participate in workflows.

= Version 5.7 =
* Enhanced publish step to setup post status after workflow completion.
* Fixed validation check of ACF for multisite setup.
* Fixed datatype issue.

= Version 5.6 =
* Made it compatible with ACF 5.6.10 and above.

= Version 5.5 =
* Fixed workflow submission with and without ACF plugin enabled.
* Modified code in Submit and Sign off Workflow to be IE 11 compatible.

= Version 5.4 =
* Enhanced Import of workflow to display importing details with success and errors.
* Enhanced workflow abort to allow users to add comments.
* Added new filter for setting up custom auto submit intervals - "owf_auto_submit_custom_interval".
* Added hook - owf_publish_past to allow for past publish date.
* Fixed validation for ACF required fields before submitting to workflow. Made it compatible with v5.x free version.
* Fixed issue with $_POST unset during revision process.
* Fixed article order issue with page hierarchy.

= Version 5.3 =
* Enhanced Export and Import of workflow( now includes teams and/or groups ) and various workflow settings.
* Added system information tab to the Tools menu. This will allow us to better serve our customers when analyzing an issue.
* Added "Duplicate Post" along with "Make Revision". "Duplicate Post" will simply duplicate the post.
* Disable the submit button on submit to workflow and sign-off popup until ajax call is completed.
* Removed assignment notification after claim process.
* FIXED: Fixed warnings when installed with PHP 7.x.
* FIXED: Auto submit to honor workflow applicable post types.
* FIXED: Issue with oasiswf_custom_placeholders_handler to check for whole word instead of partial text.
* FIXED: Removed assignment notification in case of self assignment.
* FIXED: Issue with private property on OW_Admin_Post causing a PHP fatal error.
* FIXED: Issue with translation when a post link is included in the assignment email.

= Version 5.2 =
* FIXED: Issue with WordPress revisions not getting created if content is not changed.
* FIXED: Issue with publish date/time to honor the site date/time.
* FIXED: Issue with Make Revision Overlay not showing up due to the owf_skip_workflow filter.
* Added a check to validate existence of "free" Oasis Workflow plugin.

= Version 5.1 =
* Added new filter - owf_skip_workflow, to define custom criteria for skipping the workflow on a post by post basis.
* Changed the "Assignment Emails" to be off by default. This is particularly useful, when you are setting up the plugin for the first time and wouldn't like emails to go out to real users.
* Change the "Revision Compare" tool to be more like the out of the box compare.
* Added a new link called "Update Published Post", to update published post with the "copy-of", when the revision workflow is aborted.
* FIXED: default functions for email subject.
* FIXED: Display row action "Make Revision" link as issue with Duplicate Post.
* FIXED: In some cases, the published email notification was going to incorrect user.
* FIXED: Compatibility issues with popup plugins and Make Revision functionality.
* FIXED: Issue with revision copy - non-html content inside <> brackets was being stripped.
* FIXED: ACF Pro Validation with Workflow support.

= Version 5.0 =
* Sort assignee list by name during submit to workflow, sign-off and reassign.
* Added a new filter called "owf_unset_postmeta", to unset any post meta attributes during the revision process.
* Modified Workflow Dashboard/Widget CSS to have a better UX.
* Added "Teams" to reporting, so that user can know which team the post is assigned to.
* Refactored "assignee" list code to be simple and efficient.
* Added "%post_submitter%" placeholder to assignment and reminder emails.
* FIXED: Viewing "404 page not found" issue with scheduled revision.
* FIXED: redirect of page conflicting with WP Security Audit Log plugin.
* FIXED: issue with revision post permalink if prefix and suffix are empty.
* FIXED: {post_title} link for Revised Post Publish Notification email.
* FIXED: Sign off and Submit to Workflow popup to show only the required fields.
* FIXED: Delete postmeta fields when the revision of the post is deleted.
* FIXED: Warnings for workflow settings while creating multisite.

= Version 4.9 =
* Auto Submit Enhancements - Read the updated documentation at - https://www.oasisworkflow.com/documentation/working-with-workflows/auto-submit-to-workflow
* Added filter for redirecting a user to a custom url after submitting post to workflow.
* Added filter to remove sign-off date from assignment/reminder emails.
* Fixed issue with PSN plugin to trigger email during custom post status transition for revision of post.
* Fixed issue with permalink breaking during post status update.
* Added security fixes by sanitizing user input.
* Removed "sslverify => false" on wp_remote_post calls to fix security issues.
* Fixed the CSS to display submit to workflow, sign-off, reassign popups without needing to scroll.
* Fixed display of custom status on the post edit page.
* Fixed revision check to ignore posts sitting in trash.
* Fixed issue with "Reassign" when using with Teams add-on.

= Version 4.8 =
* Enhanced Reports by adding more sorting options on the "Current Assignments" and "Workflow Submissions" report.
* Improved performance of the reports by optimizing the queries and refactoring the display logic.
* Improved performance of the history page by optimizing the queries.
* Cached query results to avoid duplicate queries for a given request.
* Fixed assignee list logic for reassign feature.
* Enhanced Post revision history by adding "copy-of" revisions to the published post. This will further help in audit process. This is an optional feature and can be turned on from "Document Revision" Settings page.
* Allow users to update the publish date(change a deadline) during the workflow. Users will "publish_posts" and/or "publish_pages" can change the deadline during workflow.
* Fixed an issue with reminder email - delete reminder email once the user has completed his/her review assignment.

= Version 4.7 =
* Removed wp_email related filters, since they were overriding all the emails and not just workflow related emails.
* Added translation support for some un-translated strings.
* Fixed an issue related to users with custom roles not showing up in the assignee list.

= Version 4.6 =
* New Feature - Digest Email - combines all assignment/reminder emails into one single message and hence reduce the number of emails your users get from the workflow system.
* New Feature - Externalized the various system emails, so that you can configure the subject, content and recipients.
* Added a hook for Pre Claim Validation. This will allow developers to implement custom validation on task claim.
* Fixed issue with Jetpack Publicize not getting invoked when task is signed off from the Inbox page.
* Fixed issue with slug/permalink - modified permalink was getting updated with the original one when signing off the task.
* Fixed issue with "Submit to Workflow" redirect not honoring the original URL.
* Fixed issue with duplicate post publish notifications for new post as well as revised post updates.


= Version 4.5 =
* Enhanced Dashboard Widget to show the number to current tasks.
* New Feature - Allow workflow designer to define default due date on a per step basis.
* New Feature: Allow Administrator to set Workflow Inbox as the landing page for selected roles.
* Fixed status drop down showing "hello" as a option on bulk edit.
* Fixed status drop down not showing custom post statuses.
* Fixed issue with sign off popup not closing in certain cases.
* Added jquery-ui CSS overrides for the calendar widget.
* Fixed the editorial comments count issue, when working with contextual comments add-on.
* Fixed issue with due date validator for non-english languages.
* Changed the URL for post/page from $post->guid to use get_permalink().
* Redirect to the post/page list page after submit to workflow.
* Made is compatible with WP 4.7

= Version 4.4 =
* Added a new setting under Workflow tab, to specify the roles that will participate in the workflow.
* The above, fixes performance issue during workflow step create/update by only listing the users for roles specified on the Workflow Settings tab.
* Added a new setting to show/hide the publish date on "Submit to Workflow". If hidden, the publish date will be set to the current date.
* When publishing, "Immediately" will be checked by default if the publish date is not in future.
* Added filter for adding/removing columns from the inbox page.
* Added month and year drop down to the date picker for easy navigation.
* Fixed an issue with "Claim" when post was claimed from the post edit page.
* Fixed issue related to capability/user role for auto submit. Now auto submit will run as an administrator.
* Fixed auto submit for "team" submission.
* Fixed validation related to due dates specified for a date later than 1st Jan 2017.
* Fixed issue with post status not getting updated on submit to workflow.

= Version 4.3 =
* Fixed issue with last step of workflow not doing editorial checklist validation.
* Fixed issue with immediate and scheduled update of revision related to timezone settings.
* Fixed issue with sign off popup not working with certain versions of IE.
* Fixed issue with capability check for custom post types.
* Fixed "assign to all" logic to cover a corner case which was causing none of the users to get the assignment.
* Added hook to delete contextual comments when a post is deleted.
* Fixed the loader icon to show/hide the closest one, when multiple "make revision" buttons are displayed.
* Fixed a compatibility issue with PODS plugin for "Make Revision" process.
* Fixed issue with contextual comments not showing up after reassign action.
* Added a confirmation dialog when deleting a workflow.
* Fixed issue with plugin upgrade message showing up multiple times in a multi-site setup.
* Added filter to add/remove actions from Inbox page.

= Version 4.2 =
* Performance improvements - We got rid of multiple AJAX calls, so you should see actions like "Submit to Workflow", "Sign off" to perform better.
* Removed the 1-minute intentional delay during revision update. Now Revision updates will be immediate or scheduled.
* Reduced dependency on WP-Cron for revision updates. Only Scheduled updates will use wp-cron.
* Fixed issue with HTML tags (if present in post title) where getting stripped during revision create.
* Fixed XSS vulnerability for request parameters.
* Enhancement - Mouse Over on Connection Info - This will allow you to quickly see the post status transition from one step to another.
* Fixed date format and display issues related to non-English dates.
* Fixed issues related to special characters like [,],(,) for post revision suffix and prefix.
* Modified publish step, to allow for publish dates in the past.

= Version 4.1 =
* Changed the Oasis Workflow system related meta keys to start with underscore, so that they are not visible on the UI.
* Added support for Multi-role users.
* Fixed "Submitted By" being blank on Auto Submit.
* Moved "Review Settings" from Workflow Settings page to the "Review" process. This will help to have much better control on the review process.
* Fixed - Show out of of the box statuses on the post edit page.
* Fixed - When "Save Draft" is clicked, the post was getting published.
* Changed logic to remove suffix/prefix from the revision. Instead of using substring, now we are using preg_replace.
* Fixed View link from within Inbox page. It was throwing a 404 error.
* Added a filter for get_users_in_step as owf_get_users_in_step()

= Version 4.0 =
* WE RECOMMEND TAKING A DATABASE BACKUP BEFORE UPGRADING TO THIS VERSION *
* Enhancement - Major Upgrade - Post Status transition moved from "Step Info" to "Connection Info". This will allow for better flexibility in defining post status transitions when moving from one step to the next.
* Enhancement - Allow Post Status change on "Submit to Workflow". 
* New Feature - Workflow Summary Dashboard Widget.
* Fixed issue with custom post statuses not showing up on "edit" post.
* Fixed "current revision by" not showing up in revision compare.

= Version 3.9 =
* Fixed revision trash and restore to reconnect with the original article so that the revision is never orphaned.
* Revision Compare - added option to either do a raw HTML compare or text/content compare.
* Fixed the Inbox page from scrolling back to the top, when working on a task which is not displayed in the above-the-fold list.
* Fixed custom fields issue - when deleted from the revision, should also get deleted from the original during revision publish. 
* Removed dependency on PHP Sessions due to intermittent support across several server environments.

= Version 3.8 =
* New Feature - Major change - You can now pre-assign users along with roles during the workflow definition.
* New Feature - Assignee selection on the Workflow Step is changed to a more user friendly drop down using select2.js.
* New Feature - You can now sort and filter workflows by various parameters like start date, end date, title, number of posts/pages in the workflow.
* New Feature - View Workflow description on mouse over of the workflow title.
* New Feature - Display custom categories - The category field on the inbox page and %category% placeholder will show custom categories.
* Fixed Export of Workflows not working for non-English locales.
* Fixed issue with make revision not working when the article contains some special characters.
* Added help icon on workflow step to help new users on how to edit a step.
* Added "Priority" to Workflow Terminology settings page, so that you can call it the way you want it to be.
* When a user is deleted, if there are tasks assigned to the user, the plugin will either delete the user's tasks OR abort the workflow whichever is appropriate.
* "Assign to all" will be checked by default for new step definitions.
* You can choose to send the "submitter" a notification when a post/page is submitted to a workflow.
* Performance improvement - Tweaked SQL queries to increase the performance for Posts Lists page and in general other page loads.

= Version 3.7 =
* New Feature - Task Priority. You can now assign priority (low, normal, high, urgent) to workflow task during sign off.
* New Feature - Added placeholder for Post Author to the assignment and reminder emails.
* New Feature - Added custom filter - ow_workflow_menu_position, to change "Workflows" menu position in the Admin UI.
* New Feature - Multi-user reassign. If assigned to multiple users for assignment and/or publish process, "Claim" will be shown to those users.
* Fixed issue with duplicate nonces creating unnecessary long urls and causing 414 errors in some cases.
* Fixed issue with role ids having spaces. 
* Fixed issue with assignment and reminder emails in some cases not working due to line breaks not being parsed correctly.
* Combined Workflow and Step name into one field on the Inbox page.
* Read more about this release at -  https://www.oasisworkflow.com/oasis-workflow-pro-v3-7-released

= Version 3.6 = 
* New Feature - Custom Statuses.
* New Feature - Assign to all. A way to assign to all the users in a given role for a given step. This will hide the user selection on the sign off process and instead assign to all the users in that role.
* New Feature - Two new custom capabilities - "ow submit to workflow and "ow sign off from step" - This will allow you to control who can/cannot submit and who can/cannot sign off.
* Fixed issue with revision updates not happening in certain environments, caused due to race condition between copy of the revised contents and revision delete.
* Fixed issue with publish emails being sent multiple times.
* Fixed issue with revision update emails not being sent at all.

= Version 3.5 =
* Added Export/Import Workflows to easily copy workflows between environments or sites.
* Fixed issue with WP error "You are not allowed to edit this post" on revision update.
* Added custom capability check to multi abort action.
* Fixed duplicate Post Author appearing in the list of available users.
* Updated the plugin updater to fix duplicate upgrade message in multi-site environments.
* Added Dutch locale files.

= Version 3.4 =
* Introducing custom capabilities. Find more at - https://www.oasisworkflow.com/documentation/oasis-workflow-custom-capabilities-explained
* Removed all the role based settings and now they are controlled using custom capabilities.
* Added "Make Revision" overlay to prevent users from accidentally updating the published version and then realizing that they need to revise first and then make changes.
* Revision Compare will also show the taxonomy compare. Also, added support for our new add-on which will display comparison for ACF attributes.
* Fixed issue with comments showing up twice in email.

= Version 3.3 =
* Added support for Advanced Custom Fields (ACF) plugin to invoke ACF validation before "Submit to Workflow" and/or "Sign off".
* Added Javascript hooks to invoke custom functionality before displaying the "Submit to Workflow" and/or "Sign off" popup.
* Changed background color for inbox items which are past due date.
* Escaped the workflow step names.
* Change menu permissions to allow anyone with "edit_theme_options" to be able to create/edit workflows/teams.
* Change the Workflow Designer to be more user friendly with help text and contextual help.

= Version 3.2 =
* Fixed step assignment and reminder message to retain WYSIWYG formatting.
* Compare Revision will now open into a new window allowing the post to be saved automatically before compare.
* Fixed the issue with "No users found for the given role" poppping up on unrelated pages.

= Version 3.1 =
* Fixed sign off comments formatting for email and UI.
* Added a config under Document Revision Settings page to control the roles that can "revise" articles.

= Version 3.0 =
* WE RECOMMEND TAKING A DATABASE BACKUP BEFORE UPGRADING TO THIS VERSION *
* YOU WILL BE REQUIRED TO RE-ACTIVATE THE PLUGIN AFTER UPGRADE, SINCE WE HAVE CHANGED THE BASE PLUGIN FILE NAME *
* Code refactored for better maintenance and ease of extension.
* Major multisite related change -
* For a multisite installation, you can now control the workflow configuration and workflows at the site level. The Workflow Admin menu is moved from Network Admin to Site Admin.
* Merged Workflows and Workflow Admin into one menu option called "Workflows".
* Fixed issue related to hiding "Update" button if the revision process is not activated. If the revision process is not activated, the Publish section will behave OOTB.
* End date on the workflows is not required any more. If not specified it will be considered to be valid for ever.

= Version 2.7 =
* Fixed CSRF and SQL injection security related issues.
* Users can view the history of the post/page from within their inbox with just one click.
* Users can view all the comments posted on the post from within their inbox. This will help the user to easily remember the context for the various comments/changes. 
* Added a new settings tab for configuring workflow terminology. You can now specify your own terminology for various actions.
* Made is compatible with WP 4.3
* Added email config to send notification email to the author when the workflow is aborted.
* Minor UI changes on the step info setup popup.

= Version 2.6 =
* Fixed CSRF and SQL injection security related issues.
* Default the workflow if there is only one applicable workflow.
* Added multi-select and multi-push/pull for the assignee and available users.
* Added comments to reassign action.
* Added more sort options on the inbox.

= Version 2.5 =
* Fixed auto submit to look into allowed post types only.
* Fixed abort and multi-abort functionality and reporting.
* Allow for past publish date when signing off the last task.
* Removed self review when working with teams.
* Fixed CSS for popups.

= Version 2.4 =
* Fixed issue with menu position.
* Fixed issue with emails sent to all instead of assignees when a user updates an article outside the workflow.
* Fixed issue with revision updating the original published date/time.
* Fixed issue with plugin updater.
* Fixed pagination on all the screens.

= Version 2.3 =
* Added email alert to inform the current assignees if anyone changed the article outside the workflow.
* Added role based access to "delete history".
* Fixed display for WP 4.2 compatibility.
* Changed number of posts per Inbox page to 40.
* Fixed date format to support other date formats.
* Fixed email to support multi-byte characters.
* Fixed security issue related to potential XSS attack caused due to add_query_arg() and remove_query_arg() WP functions.

= Version 2.2 =
* Added more options to configure your workflow. Users can now specify the "roles" and "post types" applicable to a specific workflow
* Allow privately published articles to be revised using "make revision" oasis workflow feature.
* Reports are now available to all users and not just admins.
* Added sorting on the Workflow Inbox page. Users can now sort their workflow inbox via post title.
* Added config to "compare revision" button. You can hide the "compare" button, if not required. 
* Fixed "revision compare" classname conflict with other plugins
* Fixed status change issue on "submit to workflow"
* Fixed reports to show all post types including custom post types
* Fixed default ordering on the inbox page.
* Fixed self review issue with "teams" add on.

= Version 2.1 =
* Enhanced Auto Submit functionality to allow for keyword search in tags and categories apart from the post title.
* Added configuration in auto submit, to allow to control the keyword search
* Added "Delete Copy" config to delete the "Copy of" (revision) after the revision has been copied to the original article.
* Changed "revision exists" popup to provide a link to the revision.
* Added "self review" to the workflows
* Added new hooks for "submit to workflow", "sign off", "workflow complete".
* Added "Abort Workflow" option to the workflow inbox page.
* Added "Email Settings" tab for better control of emails
* Added revision compare tool. This will help to compare contents of the revision to the original article.
* Fixed issue when "review" being the last step, workflow history was not showing "workflow completed".

= Version 2.0.2 =
* Added configuration to control which roles can/cannot abort the workflows.
* Made the roles drop down to be multi-site compatible. Now you will be able to see roles from all the sites.
* Fixed issue with teams-addon preventing the weblinks dialog to appear.
* Fixed formatting issues on submit workflow popup.
* Fixed "clear date" function on submit step popup.

= Version 2.0.1 =
* fixed dd/mm/yyyy format for future publish date

= Version 2.0.0 =
* Added "Delete/Purge" History feature
* Made the date formats to be in sync with the Wordpress Date/Time Settings
* bug fixes

= Version 1.0.11 =
* Fixed "workflow copy" and "save as new version"
* Added a null check for teams
* Fixed cron and is_plugin_active issue

= Version 1.0.10 =
* Made Auto Submit compatible with Teams Add on
* Fixed compatibility issues with Wordpress 4.1
* Fixed publish email notification after revision is published.
* Added Italian translation
* bug fixes

= Version 1.0.9 =
* Fixed self-review enabled due to "oasis workflow teams" add-on
* Fixed future date issue related to timezones
* Added "scheduledrev" post status, to display scheduled revisions until the revisions are copied over to the original article
* Fixed issue with "publish" button showing up, when the item is still in the workflow.
* Fixed post revision schedule.

= Version 1.0.8 =
* Added configuration to revise children articles, by default its off.
* View other user's inbox - configuration
* Added intelligence to decision making on review step.
* Default Due Date configuration.
* Make Revision available for scheduled articles.
* Specify a date/time for the revision to update the original/published article (scheduling a revision update)
* Allow only one active revision.
* Turn on/off revision functionality.
* Introducing Oasis Workflow Teams Add-on (more details on the site)
* Made the plugin compatible with Visual Composer plugin - http://vc.wpbakery.com/
* Made the plugin compatible with WPML - http://wpml.org/
* Add a custom role called - Post Author.
* Fixed Reassign to show all the users applicable to the given step.
* Moved "Reports" under Workflows menu option.
* UI related fixes.

= Version 1.0.7 =
* fixed compatibility issues with Wordpress 4.0
* added missing calendar images
* removed "quick edit" from Workflow Inbox
* removed history graphic from post page for compatibility reasons

= Version 1.0.6 =
* Load the JS and CSS scripts only when needed. This helps with compatibility issues with other plugins.
* Allow setting of future publish date on submit to workflow.
* Allow setting a workflow for "New" and/or "Revised" posts/pages.
* Multi-Abort functionality.
* Added additional placeholders for email.
* Workflow Process can now be selectively applied to certain post types.
* Email author when the post is published.
* fixed german translations.
* fixed compatibility issues with Wordpress 3.9

= Version 1.0.5 =
* fixed issue with workflow history discrepancies and abort workflow action.
* fixed meta copy function for revise action.
* fixed DB related issues with NULL and NOT NULL.
* fixed multisite issue related to switch and restore blog.

= Version 1.0.4 =
* major fixes for supporting "workflow for published content".
* added two new hooks for updating published content via workflow.
* added german translation files
* fixed the issues with Strict PHP - non static function called in static fashion
* fixed update datetime issue with the workflow
* changed post title to be a simple text in the subject line   

= Version 1.0.3 =
* fixed the "make revision" functionality
* fixed to remove a warning message related to mysql_real_escape_string() 

= Version 1.0.2 =
* Added workflow support for updating published content.
* made publish step a multi-user assignment step with claim process.
* fixed issue with permalink being changed after publish from the inbox page.
* fixed the issue with unnecessary call to post_publish hook.
* after sign off, the user will be redirected to the inbox page.

= Version 1.0.1 =
* Added copy workflow and copy step functionality.
* Visual indication of the first step on the workflow in light blue color.

= Version 1.0.0 =
* Initial Pro version