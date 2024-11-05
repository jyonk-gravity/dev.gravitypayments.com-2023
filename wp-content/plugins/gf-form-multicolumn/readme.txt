=== Multiple Columns for Gravity Forms ===
Contributors: webholism
Tags: gravityforms, gravity forms, multiple columns, multicolumn, multicolumns, multi column, multi columns, responsive, gravity forms multi column, gravity forms multicolumn, multi row, multirow, multiple rows
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=JHGDAKZ2YLFLN
Requires at least: 4.6
Tested up to: 6.0.1
Requires PHP: 7.3
Stable tag: 4.0.6
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Introduces new form elements into Gravity Forms which allow for simple column creation.

== Description ==

When activated this plugin allows Gravity Forms elements to be split into columns. To implement the columns three new elements (Row Start, Column Break, and Row End) which are introduced into the Gravity Forms administration area in a group labelled as Multiple Columns Fields.

For each form, in its form settings it is possible to enable and disable the plugin's CSS and JS that are used by the plugin. The CSS file is responsible for the layout of the form, and the JS file is used to remove unwanted spacing generated if using conditional logic to hide form elements. It is advised to keep the CSS enabled at all times, but the JS should only be enabled in the case of hidden elements through the use of conditional logic.

** Note: Plugin no longer supports initial column specification using Sections in versions 2 and before. If updating plugin from a version prior to version 3, the form will need to be recreated with the current design definitions.**

**Note: If updating from version 3.0.3 or earlier. If the columns are not displaying as expected, first please visit the Form Settings for each form and check the Enable CSS checkbox.**

**Support this plugin by purchasing [Gravity Forms](https://rocketgenius.pxf.io/multicolumns) now, using the affiliate link.  No extra cost to you, and a big help towards supporting this plugin.**

== Installation ==

Upload ...
1. In your WordPress admin panel, go to <em>Plugins > New Plugin</em>, search for “Multiple Columns for Gravity Forms”, find the “Multiple Columns for Gravity Forms” plugin and click “<em>Install now</em>”.
2. Alternatively download the zip file, unzip, and upload the gf-form-multicolumn folder (and files) to your plugins directory, which usually is /wp-content/plugins/.
... and activate.
3. Activate the plugin through your <em>Plugins</em> area.

Once installed, the following steps will help provide guidance to introduce column elements into a form:
4. Create a new row of separated columns by selecting the Row Start button in the Multiple Columns Field box on the right-hand side of the Gravity Forms form page.
5. Add the fields that you want in the column.
6. Create a Column Break to separate to the next column, or Row End to end the column division and the field row.
7. Repeat 4-6 as necessary.
8. Click the Update button to save the changes.

CSS and JS included with the plugin can be toggled on and off in the Form Settings for the individual form.

9. In Settings -> Form Settings once a form has been selected, visit the section titled Multiple Columns.  Here there
are new options related to the Multiple Columns for Gravity Forms plugin.

**Note: If updating from version 3.0.3 or earlier. If the columns are not displaying, please visit the Form Settings for each form and check the Enable CSS checkbox.**

== Frequently Asked Questions ==

= I have updated from an earlier version, where have my columns gone? =

For each Gravity Form that you had prior to update, please visit the Form Settings for each and check the Enable CSS checkbox.

= How many columns can I make? =

We’ve tested 2 up to 10 columns. Theoretically you can have more, although this will depend on your theme and the amount of screen space you have.

= Could you give an example of how I would create 3 columns on a single row? =

Add a Row Start field. Add the field/s that are to be contained in the first column. Add a Column Break field. Add the field/s that are to be contained in the second column. Add a Column Break field. Add the field/s that are to be contained in the third column. Add a Row End field.

= Could you give an example of how I would create 1 row with 2 columns, then a second row with 3 columns? =

Add a Row Start field. Add the field/s that are to be contained in the first column. Add a Column Break field. Add the field/s that are to be contained in the second column. Add a Row End field Add a Row Start field. Add the field/s that are to be contained in the first column. Add a Column Break field. Add the field/s that are to be contained  in the second column. Add a Column Break field. Add the field/s that are to be contained in the third column. Add a Row End field.

= Is it possible to disable the default CSS and the JS from the plugin? =

It is possible to stop the loading of these files from within the settings of the individual form. It must be noted that much of the column splitting functionality is defined by the inclusion of the CSS stylesheet. The JS file includes functionality to maintain layout integrity (removes spacing that is generated by hidden fields) when using conditional logic to hide field elements in a form.

= How do I ensure that a new row will occur at the end of my columns? =

The Row End field will default to ending a given row.

= I am not seeing columns on the front end, after adding Multiple Column elements to my form =

Check the form settings, and ensure that the Enable CSS checkbox has been checked, and save the form.  If the Enable CSS checkbox is checked, please uncheck it, save the form, recheck it, and resave.  Then try again.

= Can I use this plugin with multisite? =

Yes.

== Screenshots ==

1. New Gravity Form with Multiple Columns Fields floating panel (collapsed).
2. Multiple Columns Fields floating panel close up (expanded).
3. Form showing new fields (Row Start, Column Break, Row End) added to form.
4. Example of a completed form, composed of Multiple Columns Fields and generic Gravity Form fields.
5. Gravity Forms - Form - Settings -> Form Settings showing the Enable CSS and Enable JS checkboxes.

== Changelog ==

= 4.0.6 =

**If upgrading from a previous version of this plugin it may be necessary to activate the CSS for each form that uses the multiple column functionality. This is done by going to the form -> Form Settings -> Check the box beside Load CSS Stylesheet -> Click Update Form Settings.**

Fix: Rollback of GFMC-73 issue as this appeared to cause issues with removed layout elements.

= 4.0.5 =

**If upgrading from a previous version of this plugin it may be necessary to activate the CSS for each form that uses the multiple column functionality. This is done by going to the form -> Form Settings -> Check the box beside Load CSS Stylesheet -> Click Update Form Settings.**

Fix: How to remove muticolumn fields from Entries. (GFMC-73)
Fix: PHP error (Required parameter). (GFMC-76)

= 4.0.4 =

**If upgrading from a previous version of this plugin it may be necessary to activate the CSS for each form that uses the multiple column functionality. This is done by going to the form -> Form Settings -> Check the box beside Load CSS Stylesheet -> Click Update Form Settings.**

Fix: CSS class names on MCGF elements. (GFMC-74)

= 4.0.3 =

**If upgrading from a previous version of this plugin it may be necessary to activate the CSS for each form that uses the multiple column functionality. This is done by going to the form -> Form Settings -> Check the box beside Load CSS Stylesheet -> Click Update Form Settings.**

Fix: Divide by Zero error. (GFMC-69)
Fix: Uninstall Function Conflict. (GFMC-60)

= 4.0.2 =

**If upgrading from a previous version of this plugin it may be necessary to activate the CSS for each form that uses the multiple column functionality. This is done by going to the form -> Form Settings -> Check the box beside Load CSS Stylesheet -> Click Update Form Settings.**

Fix: An error of type E_PARSE on update, related to PHP version site is running on. (GFMC-71)

= 4.0.1 =

Fix: Not working if Gravity Forms is Must-Use plugin. (GFMC-65)
Fix: Undefined offset PHP notice. (GFMC-67)

= 4.0.0 =

Fix: Issue due to CSS conflict when displaying default form and form with GFMC elements. (GFMC-58)
Fix: JavaScript functionality issue for hiding elements because of conditional logic. (GFMC-64)

Alteration: Plugin no longer supports initial column specification using Sections in versions 2 and before. If updating plugin from a version prior to version 3, the form will need to be recreated with the current design definitions.

= 3.2.1 =

Fix: Warning: Division by zero message corrected with the use of a check for zero. (GFMC-57)

= 3.2.0 =

Fix: Resolution of issue when conditional logic JS enabled - Uncaught TypeError: e[n].target.parentElement is null On Page with Conditional Logic Elements (GFMC-44)
Fix: composer.json file included at root of the plugin which was causing issues when installing or updating the plugin with WP-CLI. (GFMC-49)
Fix: Restructure of layout for Gravity Forms 2.5 as this uses divs when not in legacy enabled mode. (GFMC-55)

= 3.1.5 =

Fix: Update button in administrator fixed when Gravity Forms -> Settings in On state (GFMC-43)

= 3.1.4 =

Fix: Page count added to row count causing division by zero issue (GFMC-30)

= 3.1.3 =

Fix: Multipage form layout broken (GFMC-29)

= 3.1.2 =

Fix: Problem with form deactivation on save.

= 3.1.1 =

Fix: Inaccurate set of files uploaded.

= 3.1.0 =

Fix: IE11 CSS styling that was not correctly aligning columns.
Improvement: Form settings modified which allows the addition of a .js file that hides and shows the wrapper list element around conditional logic elements.
Improvement: Form settings modified which allows the plugin CSS file to be enabled and disabled for inclusion.
Improvement: Included possibility to allow for CSS classes to be defined for columns
Improvement: Removes form entries added when the plugin is uninstalled.
Improvement: Form validation when saving to reduce unequal row start to row end implementations.
Improvement: Introduced functionality that will also provide appropriate layout on AJAX generated forms.
Alteration: Changed Column Start and Column End to Row Start and Row End respectively.

= 3.0.3 =

Fix: Removed echo commands as these were causing update issues from within Gutenberg pages. Improvement: Altered CSS to be more specific with class naming implementation.

= 3.0.2 =

Restructured way that columns and rows are added to forms; native UI buttons are now integrated into the Gravity Forms interface. Resolved a few issues that had been highlighted in previous versions: Displaying multiple forms on a single page Correct error handling when form id not present in shortcode * CSS enhancements to align list elements

= 3.0.1 =

The same as 2.1.1, due to previous inaccurate upload.

= 3.0.0 =

Problematic upload. Ignore this version.

= 2.1.1 =

This version removed code that had been used for testing multisite in 2.1.0.

= 2.1.0 =

Allow admins to activate or deactivate on individual network sites. New CSS style introduced to remove spacing around the first column of a row. Plugin name changed to align with Wordpress recommendations.

= 2.0.1 =

Update to work with PHP version < 5.4.

= 2.0.0 =

Introduced new feature to allow for multiple rows. Individual rows will split the columns they contain evenly.

= 1.0.1 =

Altered details related to the supporting files. No functional alterations. Upgrade optional.

= 1.0.0 =

Initial Release. Trumpets sound!

== Upgrade Notice ==

= 4.0.0 =

Plugin no longer supports initial column specification using Sections in versions 2 and before. If updating plugin from a version prior to version 3, the form will need to be recreated with the current design definitions.

= 3.1.0 =

CSS fix for IE11. Allows admins to enable and disable the inclusion of CSS for layout, and JS for hidden conditional logic elements. Tooltips added to enhance clarity. CSS classes can now be added to column output to provide greater ease of customisation. Form row start and row end validation.  Layout styling persists in AJAX forms.

== Credits ==

A big thank you goes to K. Woodberry, T. Kaufenberg, J. Wright, D. Donnelly, A. Sharma, L. Hanbury-Pickett for identifying issues, and assisting in finding solutions to them. :)
