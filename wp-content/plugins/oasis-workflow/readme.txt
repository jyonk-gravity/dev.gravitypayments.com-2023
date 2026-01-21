=== Oasis Workflow ===
Contributors: nuggetsol
Tags: workflow, work flow, review, assignment, publish, inbox, workflow history, audit, versioning, edit flow, newsroom, custom post status, journalism, auto submit, approval workflow, editorial workflow, notifications, oasis workflow, editorial, revisions, document revision, version control, collaboration, document management, revision scheduling, duplication, clone, revise, revise article
Requires at least: 4.4
Tested up to: 6.9
Stable tag: 6.5.4

Automate your WordPress Editorial Workflow with Oasis Workflow. Simple, intuitive drag and drop workflow builder to streamline your editorial process.

== Description ==

Oasis Workflow is a powerful feature rich plugin designed to automate any editorial workflow process using a simple, intuitive drag and drop interface.

= Typical users/audience for Oasis Workflow =
* WordPress sites with multiple authors that are looking to manage their content review and publication process more efficiently.
* Industries like healthcare, law firms, financial firms, universities, CPA firms, non-profits, news outlets and blogs, that publish articles regularly and need a formal review process.
* Industries with strict auditing requirements where they need to track who made the change, when was the change made and what was changed.

= The plugin provides three simple process/task templates: =
* Assignment - represents task related to content generation.
* Review - represents task related to content review.
* Publish - represents the actual "publish" or "review and publish" task.

**Visual Work flow Designer**
 - Configure your workflow using our easy drag and drop designer interface. See screen shots for more detail.

**Role-Based routing definitions allow you to assign tasks dynamically**
 - By using role-based routing, you can ensure that your process moves forward as quickly as possible without sacrificing accountability.

**Inbox**
 - Users can view their current assignments and sign off their tasks once it's completed.
 
**Custom Statuses**
 - Define custom statuses for your editorial workflow. With custom statuses you can define your own post statuses and assign it to the success or failure path of the workflow.

**Process history lets users retrace their steps**
 - For auditing purposes a record is created every time an article is routed through a workflow process. The process history also captures the comments added by the user during task sign off.

**Reassign - How to pass the buck?**
 - What if you have been assigned a workflow task, but you feel you are not the appropriate person to complete it? No worry, you can use the re-assign feature to pass the task to another person. 

**Due Date and Email reminders** help you to publish your articles on time.

**Out of the box workflow**
- To get you started, the plugin comes with two out of the box workflow - Single Level Review Workflow, and Multi Level Review Workflow. You can modify the workflow to suit your needs.

**If you are looking for additional functionality, check out our "Pro" version - Oasis Workflow Pro: https://www.oasisworkflow.com/pricing-purchase**
**It comes with additional features like,** 

* [Revise published content and add workflow support to revised content](https://www.oasisworkflow.com/documentation/working-with-workflows/revise-published-content) - Use workflow to edit your published content while keeping the published article online.
* [Auto Submit](https://www.oasisworkflow.com/documentation/working-with-workflows/auto-submit-to-workflow) - Allows you to automatically submit to workflow(s) with certain conditions.
* And add-ons, like "Editorial Contextual Comments", "Editorial Checklist", "Teams", "Front End shortcodes", "Groups"
* And much more.. 

If you want to try out the "Pro" features, send us a message at [Request a Demo Site](https://www.oasisworkflow.com/request-a-demo-site) and we will setup a demo site for you to try out the "Pro" features. As a bonus, we will also add our add-ons to the demo site. 
More details for each feature, screenshots and documentation can be found on [our website](https://www.oasisworkflow.com/).

= Supported languages =
* English
* Spanish
* French
* Italian
* Swedish
* Ukrainian
* Russian
 
= Translators =
* French (fr_FR) - [Baptiste Rieg](http://www.batrieg.com)
* Italian (it_IT) - [Martino Stenta](https://profiles.wordpress.org/molokom)
* Swedish (sv_SE) - Norbert Kustra
* Ukrainian (uk) - Andy Prokopchuk

If you need help setting up the roles, we recommend [Capability Manager Enhanced plugin](https://wordpress.org/plugins/capability-manager-enhanced/ "Capability Manager Enhanced plugin") OR [User Role Editor plugin](http://wordpress.org/extend/plugins/user-role-editor/ "User Role Editor plugin").
Oasis Workflow adds a few custom capabilities to manage your workflows. Check out [Oasis Workflow Custom Capabilities](https://www.oasisworkflow.com/documentation/working-with-workflows/oasis-workflow-custom-capabilities-explained)

= Videos to help you get started with Oasis Workflow =

Creating and Editing a workflow

[youtube https://www.youtube.com/watch?v=JbJJQMMnf5U]

How it works? See the workflow in action.

[youtube https://www.youtube.com/watch?v=_R2uVWQicsM]

Modify a workflow which has posts/pages currently in progress

[youtube https://www.youtube.com/watch?v=mJ2hPsSBGcE]

How to manage published content via workflow - revise published content (applicable to "Pro" version only)

[youtube http://www.youtube.com/watch?v=J4qJG7-F1qQ]

== Installation ==

1. Download the plugin zip file to your desktop
2. Upload the plugin to WordPress
3. Activate Oasis Workflow by going to Workflow Admin --> Settings
4. You are now ready to use Oasis Workflow! Build Your Workflow and start managing your editorial content flow.

== Frequently Asked Questions ==

For [Frequently Asked Questions](https://www.oasisworkflow.com/faq) plus documentation, plugin help, go [here](https://www.oasisworkflow.com/support)

== Screenshots ==

1. Visual Work flow designer - 2 step review process
2. Workflow example - Simple 1 step review process
3. More examples.. of the workflow designer
4. Role-based routing
5. Inbox - all you care about are the tasks assigned to you.
6. Sign off - complete the tasks by signing off and move the article to the next step.
7. Process history lets users retrace their steps
8. Custom statuses


== Changelog ==

= Version 6.5.4 =
* Fixed the PluginSidebar and PluginSidebarMoreMenuItem import issue for WordPress version 6.6+ and lower.
* Fixed "submit to workflow" button duplicate issue by adding two later condition in `ow-sidebar.js` Now, before adding the button code will check if it's already exists or not.
* Added `team_id` to the `$step_details` array in `api_get_step_details` to prevent a 500 error when using team add-ons.
* Fixed "Please select a valid date" issue on submit workflow `/oasis-workflow/js/pages/subpages/submit-workflow.js`.

= Version 6.5.3 =
* Fixed "Please select a valid date" that related with "Due Date" settings options.
* Fixed & Changed publish post check login in block editor now using `isPublishedByStatus`

= Version 6.5.2 =
* Fixed the issue with empty publish dates on submission. Validation is now implemented on both the front end and back end.
* Fixed "submit to workflow" button duplicate issue by adding two later condition in `ow-sidebar.js` Now, before adding the button code will check if it's already exists or not.

= Version 6.5.1 =
* Fixed undefined dateI18n on ow-due-date-label.js.
* Add processSignoffQueue & checkPostSaving to check if post save is done then process oasis action.

= Version 6.5 =
* Replaced Javascript library wp.** with @wordpress/** JavaScript packages.
* Fixed security issue on Reports page.
* Fixed security issue on Inbox page.
* Fixed workflowSubmitWithACF issue by changing it to async/await
* Fixed performance issue on signoff by moving `subscribe()` move into componentDidMount
* Fixed deprecation warning  OW_Action_History::$history_meta & OW_Workflow::$post_count with PHP v8+
* Added filter `owf_get_roles_option_list` and `owf_get_users_option_list_args`
* Fixed `json_encode` error in `review_step_procedure` with PHP v8+.
* Fixed acf validation dependency on block editor js file.
* Fixed "Submit to Workflow" not working in Classic Editor.
* Updated `acf-pro-validator-new.js` to fix ACF validation in Classic Editor when the submit button is triggered.

= Version 6.4 =
* Made the plugin compatible with WP v6.4+
* Removed dependency on lodash and replaced with vanilla js function that placed in src/util.js
* Removed `__experimentalGetSettings` and replaced with getSettings().

= Version 6.3 =
* Fixed mail content "32$s" issue.
* Fixed required ACF field issue on classic and block editor.
* Fixed ACF validation ajax issue.
* Added 'ow_redirect_after_signoff_url' hook.
* Added extra arguments to 'ow_workflow_completed' hook.
* Added hook to rename "post_status" taxonomy slug - 'ow_post_status_slug'.
* Fixed block editor status changed issue with 'wp_insert_post' hook
* Fixed to remove unnecessary enqueue script loading in block editor.
* Fixed delete-status conflict with publish press status delete feature.

= Version 6.2 =
* Added extra parameter to 'owf_workflow_abort' hook called.
* Fixed abort comment not sending to the mail issue.

= Version 6.1 =
* Fixed WordPress Coding Standard/PHPCS.
* Added hook to check if role is applicable - 'ow_is_role_applicable'.
* Added hook for redirection url after signoff 'ow_redirect_after_signoff_url'.
* Added hook for triggering JS after workflow complete 'ow_workflow_completed'.

= Version 6.0 =
* Add feedback reason as required.

= Version 5.9 =
* Fixed due date datepicker.
* Fixed due date set due date settings options.

= Version 5.8 =
* Fixed the redirection issue after abort, signoff etc in block editor
* Fixed issue with <br> showing up in comments.
* Removed babel-cli dependency since it was not needed.

= Version 5.7 =
* Security Fix - Fixed output escaping issue where some of the strings were not properly escaped.
* REST API Security - Added capability security to publicly accessible API endpoints.
* PHP to JS Escaping - Javascript variables printed and defined through PHP variables were properly sanitized and escaped.
* Unprepared SQL Queries - Fixed instances of SQL queries that were not properly escaped and prepared.

= Version 5.6 =
* Made it compatible with WP 5.6.
* Fixed CSS issues.

= Version 5.5 =
* Reverted a change related to custom post types to support Oasis Workflow and Gutenberg Editor.
* Fixed warnings when saving workflow settings post types.

= Version 5.4 =
* Sanitized workflow comments to disallow certain tags.
* Modified custom post types on the fly to support Oasis Workflow and Gutenberg Editor.

= Version 5.3 =
* Fixed bugs related to JQuery attribute and prop checks.

= Version 5.2 =
* Reverted a change related to hiding the Publish button until the "Submit to Workflow" shows up.

= Version 5.1 =
* Hide the Publish button, until the Submit to Workflow button is visible.
* Made it compatible with WP 5.5

= Version 5.0 =
* Fixed CSS issue with the menu item.
* Fixed issues with JED generated language files.

= Version 4.9 =
* Improved the feedback process during plugin deactivation.
* Added single level review workflow as another workflow available out of the box for ease of use.


= Version 4.8 =
* Allow user to provide feedback during plugin deactivation.

= Version 4.7 =
* Added capability check to import/export functionality for additional security.
* Allow meta fields to be edited for Gutenberg Editor.

= Version 4.6 =
* Fixed potential issue with update_option not sanitizing certain input values.
* Fixed deleting of created workflows.

= Version 4.5 =
* Added ability to create multiple workflows.
* Added Export and Import of workflow and various workflow settings.
* Fixed issue with Jetpack Publicize not getting invoked when task is signed off from the post edit page.

= Version 4.4 =
* Fixed front end gutenberg dependency error.
* Deny creating custom statuses that are interfering with the core ones like draft, etc.

= Version 4.3 =
* Fixed roles capability check to allow underscore in post type names.
* Redirect user to either post list page or workflow inbox after workflow submit and sign-off.
* Added loader for each API request and submission.

= Version 4.2 =
* Made it compatible with PHP 5.2 to support regular array declaration instead of square brackets.
* Made it compatible with WP 5.2.1.
* Added a custom role called - Post Submitter. This will give more assignee options when defining the workflow.

= Version 4.1 =
* Display warning message if "Show in Rest" and "custom field support" is not set for Custom Post Type.
* Fixed Assign to all not assigning to all the step users.
* Fixed email service invocation to be compatible with Gutenberg Editor.

= Version 4.0 =
* Made it compatible with Gutenberg Editor

= Version 3.3 =
* Fixed datatype issue.
* Fixed compatibility issue with ACF plugin.

= Version 3.2 =
* Made it compatible with ACF 5.6.10 and above.

= Version 3.1 =
* Fixed validation for ACF required fields before submitting to workflow.

= Version 3.0 =
* Disable the submit button on submit to workflow and sign-off popup until ajax call is completed.
* Enhanced workflow abort to allow users to add comments.

= Version 2.9 =
* Fixed warnings when installed with PHP 7.x.

= Version 2.8 =
* Made it compatible to WordPress 4.8.

= Version 2.7 =
* Fixed issue related to broken permalink during the sign off process.
* Sanitized user input on the settings page to fix security issue.
* Fixed CSS for "submit to workflow", "sign-off" and "reassign" popup.
* Fixed code warnings when displaying workflow submission report.
* Fixed display of custom status on the post edit page.

= Version 2.6 =
* Fixed issue with Jetpack Publicize not getting invoked when task is signed off from the Inbox page.
* Fixed issue with slug/permalink - modified permalink was getting updated with the original one when signing off the task.
* Fixed issue with "Submit to Workflow" redirect not honoring the original URL.
* Fixed issue with duplicate post publish notifications for new post as well as revised post updates.
* Fixed XSS vulnerability for request parameters on the history page.

= Version 2.5 =
* Added a wrapper class for datepicker UI class, so that it doesn't clash with other plugins.
* Redirect to post list page after submit to workflow.
* Fixed status drop down showing "hello" as a option on bulk edit.
* Fixed status drop down not showing custom post statuses.
* Fixed issue with sign off popup not closing in certain cases.
* Fixed issue with due date validator for non-english languages.
* Made it compatible to WordPress 4.7.

= Version 2.4 =
* Added task count on the workflow dashboard widget for quick access to the inbox.
* Added month and year drop down to the date picker for easy navigation.
* Fixed issue with post status not getting updated on submit to workflow.
* Fixed issue with blank publish date.
* Removed logic to hide/show "Save" button when the post is being updated by a user outside the workflow.

= Version 2.3 =
* Changed the Oasis Workflow system related meta keys to start with underscore, so that they are not visible on the UI.
* Added support for Multi-role users.
* Moved "Review Settings" from Workflow Settings page to the "Review" process. This will help to have much better control on the review process.
* Added a filter for get_users_in_step as owf_get_users_in_step()
* Added configuration to show/hide the publish date.
* Performance improvements - We got rid of multiple AJAX calls, so you should see actions like "Submit to Workflow", "Sign off" to perform better.
* Fixed - Show out of of the box statuses on the post edit page.
* Fixed - When "Save Draft" is clicked, the post was getting published.
* Fixed "assign to all" logic to cover a corner case which was causing none of the users to get the assignment.
* Fixed issue with sign off popup not working with certain versions of IE.
* Fixed issue with capability check for custom post types.

= Version 2.2 =
* WE RECOMMEND TAKING A DATABASE BACKUP BEFORE UPGRADING TO THIS VERSION *
* Enhancement - Major Upgrade - Post Status transition moved from "Step Info" to "Connection Info". This will allow for better flexibility in defining post status transitions when moving from one step to the next.
* Enhancement - Allow Post Status change on "Submit to Workflow".
* New Feature - Major change - You can now pre-assign users along with roles during the workflow definition.
* New Feature - Assignee selection on the Workflow Step is changed to a more user friendly drop down using select2.js.
* New Feature - Workflow Summary Dashboard Widget.
* New Feature - Task Priority. You can now assign priority (low, normal, high, urgent) to workflow task during sign off.
* New Feature - Added placeholder for Post Author to the assignment and reminder emails.
* New Feature - Multi-user reassign. If assigned to multiple users for assignment and/or publish process, "Claim" will be shown to those users.
* New Feature - View Workflow description on mouse over of the workflow title.
* When a user is deleted, if there are tasks assigned to the user, the plugin will either delete the user's tasks OR abort the workflow whichever is appropriate.
* Display custom categories - The category field on the inbox page and %category% placeholder will display custom categories too.
* Combined Workflow and Step name into one field on the Inbox page.
* Fixed issue with duplicate nonces creating unnecessary long urls and causing 414 errors in some cases.
* Fixed issue with role ids having spaces.
* Fixed issue with assignment and reminder emails in some cases not working due to line breaks not being parsed correctly.
* Fixed issue with custom post statuses not showing up on "edit" post.
* To read more about the above changes check out - https://www.oasisworkflow.com/oasis-workflow-free-v2-2-released


= Version 2.1 =
* Fixed fatal issue with uninstall caused due to undefined function.
* Removed commented out code and cleaned up JS.
* Fixed PHP warnings on the history page.
* Added Ukranian translation. Thanks to Andy Prokopchuk.

= Version 2.0 =
* WE RECOMMEND TAKING A DATABASE BACKUP BEFORE UPGRADING TO THIS VERSION *
* Major upgrade - refactored the entire code base for better maintenance.
* New Feature - Custom Statuses.
* New Feature - Assign to all. A way to assign to all the users in a given role for a given step. This will hide the user selection on the sign off process and instead assign to all the users in that role.
* Introducing custom capabilities. Find more at - https://www.oasisworkflow.com/documentation/oasis-workflow-custom-capabilities-explained
* Performance improvement - Tweaked SQL queries to increase the performance for Posts Lists page and in general other page loads.
* Added "review settings" - to allow all, one OR more than 50% users to sign off before moving to the next step.


= Version 1.9 =
* Fixed duplicate Post Author appearing in the list of available users.
* Added edit_others_posts and edit_others_pages to the author role by default.

= Version 1.8 =
* Compatibility with WordPress 4.4
* Added support for Advanced Custom Fields (ACF) plugin to invoke ACF validation before "Submit to Workflow" and/or "Sign off".
* Added Javascript hooks to invoke custom functionality before displaying the "Submit to Workflow" and/or "Sign off" popup.
* Changed background color for inbox items which are past due date.

= Version 1.7 =
* End date on the workflows is not required any more. If not specified it will be considered to be valid for ever.
* Merged Workflows and Workflow Admin into one menu option called "Workflows".
* Added Reports - to view current assignments and to view what is/is not in the workflow.
* Added "delete workflow" to delete any unwanted revisions.

= Version 1.6 =
* WE RECOMMEND TAKING A DATABASE BACKUP BEFORE UPGRADING TO THIS VERSION *
* Major multisite related change -
* For a multisite installation, you can now control the workflow configuration and workflows at the site level. The Workflow Admin menu is moved from Network Admin to Site Admin.
* Users can view the history of the post/page from within their inbox with just one click.
* Users can view all the comments posted on the post from within their inbox. This will help the user to easily remember the context for the various comments/changes. 
Since we changed the structure of how we are storing the comments, you will notice the comment date missing on the old comments. Going forward, the comment date will work as usual.  


= Version 1.5 =
* Fixed CSRF and SQL injection security related issues.
* Default the workflow if there is only one applicable workflow.
* Added multi-select and multi-push/pull for the assignee and available users.
* Added more sort options on the inbox.
* Allow to save step even when there are items in the workflow.
* Added a new settings tab for configuring workflow terminology. You can now specify your own terminology for various actions.

= Version 1.4 =
* Fixed abort
* Allow for past publish date when signing off the last task.
* Added new hooks for "submit to workflow", "sign off", "workflow complete".

= Version 1.3 =
* Show Update button for published articles.
* Added "hide upgrade notice" link.
* Fixed menu position to have a unique position.

= Version 1.2 =
* Fixed date format for publish date
* Fixed issue with due date javascript

= Version 1.1 =
* Email Settings - A new tab in the Settings page, to better control how and when emails are sent from Oasis Workflow for task assignments, reminders and post publish.
* Abort Workflow is added to the Inbox page. This will allow the users to abort the workflow from their inbox.
* History Graphic - Show workflow graphic on the post page. Configurable via Workflow Settings page.
* Added "Delete/Purge" History feature
* Added sorting on the Workflow Inbox page. Users can now sort their workflow inbox via post title.
* Added "self review" to the workflows.
* Fixed default ordering on the inbox page.
* Fixed status change issue on "submit to workflow"
* Fixed add_query_arg() and remove_query_arg() usage

= Version 1.0.20 =
* Fixed php error related to date locale (hopefully the last update related to date issues)
* Tested for Wordpress 4.1.1

= Version 1.0.19 =
* Fixed a php error related to missing date on workflow edit.
* Added post types to workflow selection. Now you can choose the post types which should go through the workflow.
* Made the roles drop down to be multi-site compatible. Now you will be able to see roles from all the sites.
* Added a custom role called - Post Author.
* Fixed "clear date" function on submit step popup.
* Fixed Page/Post delete to delete the inbox items related to the deleted post/page

= Version 1.0.18 =
* fixed dd/mm/yyyy format for future publish date

= Version 1.0.17 =
* Made the date formats compatible with Wordpress date formats
* Added a setting for default due date
* bug fixes

= Version 1.0.16 =
* Fixed compatibility issues with Wordpress 4.1
* Added Italian translation
* bug fixes

= Version 1.0.15 =
* Fixed future date issue related to timezones
* Fixed post revision schedule
* Modified the DB to make it easier to add more features

= Version 1.0.14 =
* fixed compatibility issues with Wordpress 4.0
* added missing calendar images
* fixed compatibility issues with Visual Composer Plugin.
* removed "quick edit" from Workflow Inbox
* bug fixes

= Version 1.0.13 =
* Load the JS and CSS scripts only when needed. This helps with compatibility issues with other plugins.
* Allow setting of future publish date on submit to workflow.
* fixed german translations.
* fixed compatibility issues with Wordpress 3.9

= Version 1.0.12 =
* fixed issue with workflow history discrepancies and abort workflow action.
* fixed DB related issues with NULL and NOT NULL.
* fixed multisite issue related to switch and restore blog.

= Version 1.0.11 =
* added german translation files
* fixed the issues with Strict PHP - non static function called in static fashion
* fixed update datetime issue with the workflow
* changed post title to be a simple text in the subject line  

= Version 1.0.10 =
* made publish step a multi-user assignment step with claim process.
* after sign off, the user will be redirected to the inbox page.
* fixed issue with permalink being changed after publish from the inbox page.
* fixed the issue with unnecessary call to post_publish hook.
* fixed to remove a warning message related to mysql_real_escape_string()

= Version 1.0.9 =
* removed a call to wp-load.php to help with performance
* added visual indicator to the first step

= Version 1.0.8 =
* Updated the Inbox menu to display the number of inbox items.
* The plugin will now come with an out of the box workflow when installed for the first time. This will help getting started with the plugin with little or no effort. Simply activate the workflow process from Workflow Admin --&gt; Settings page and you are ready to use the workflow.
* Auto select of user during the sign off process, if there is one and only one user for that given role.
* Due dates are not required/shown unless "reminder emails" are set to be required on the settings page.
* Added French translation files.
* Added Sign off button on the Posts page. This will help to sign off the post/page even when you are not in your inbox.
* Fixed issues related to IE compatibility. The plugin should function well in IE 9 and IE 10.
* Fixed issue with sign off caused due to the addition of  "take over" functionality by core Wordpress.
* We have removed the connection type from the connection settings popup. The plugin defaults to one specific connection type. You might see the workflow visual representation to be a bit awkward. All you have to do is to save the workflow and it will auto-correct the connections.

= Version 1.0.7 =
* Bug fixes.
* minor enhancements

= Version 1.0.6 =
* Internationalization(I18N) and localization (L10N) support added.
* Bug fixes.
* minor enhancements

= Version 1.0.5 =
* Multi site enhancements. Moved the Workflow Admin to Network Admin, so workflows can be shared between all the sites.
* No need to duplicate the workflows for new sites inside a multi site environment.
* Note: 
* 1. Workflows previously created in sub sites except the main site will NOT be available anymore. 
If these workflows are different, they need to be recreated with this upgrade.
* 2. Make sure to complete all the existing workflows for sub sites, to avoid any unexpected behavior. 

= Version 1.0.4 =
* Made the assignment step a multi-user step, where multiple users can be assigned the work however only one can claim it.
* Configuration - Roles who are allowed to publish post without going through a workflow.
* Set "publish" as the success step for the publish step.
* Bug fixes.

= Version 1.0.3 =
* Added an option for admin to detach the post from oasis workflow and go back to normal wordpress behavior.
* Added reminder email AFTER certain due date feature.
* Change the post title placeholder to be a link.
* Bug fixes.

= Version 1.0.2 =
* Made WP 3.5 compatible

= Version 1.0.1 =
* Added Multisite capability.
* Admin can now view another user's inbox and signoff on behave of other users.
* Bug fixes.

= Version 1.0.0 =
* Initial version