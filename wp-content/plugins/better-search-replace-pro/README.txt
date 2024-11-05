=== Better Search Replace Pro ===
Contributors: deliciousbrains, mattshaw
Tags: search replace, search and replace, update urls, database, search replace database, update database urls, update live url, better search replace, search&replace
Requires at least: 3.0.1
Tested up to: 6.0.1
Stable tag: trunk
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

A simple plugin for updating URLs or other text in a database.

== Description ==

When moving your WordPress site to a new domain or server, you will likely run into a need to run a search/replace on the database for everything to work correctly. Fortunately, there are several plugins available for this task, however, all have a different approach to a few key features. This plugin consolidates the best features from these plugins, incorporating the following features in a simple, ad-free plugin:

* Serialization support for all tables
* The ability to select specific tables
* The ability to run a "dry run" to see how many fields will be updated
* No server requirements aside from a running installation of WordPress
* WordPress Multisite support

The search/replace functionality is heavily based on interconnect/it's great and open-source Search Replace DB script, modified to use WordPress native database functions to ensure compatibility.

**Supported Languages**

* English
* French
* German
* Spanish

**Want to contribute?**

Feel free to open an issue or submit a pull request on [GitHub](https://github.com/deliciousbrains/better-search-replace/).

== Installation ==

Install Better Search Replace like you would install any other WordPress plugin.

Dashboard Method:

1. Login to your WordPress admin and go to Plugins -> Add New
2. Type "Better Search Replace" in the search bar and select this plugin
3. Click "Install", and then "Activate Plugin"


Upload Method:

1. Unzip the plugin and upload the "better-search-replace" folder to your 'wp-content/plugins' directory
2. Activate the plugin through the Plugins menu in WordPress

== Frequently Asked Questions ==

= Using Better Search Replace =

Once activated, Better Search Replace will add a page under the "Tools" menu page in your WordPress admin.

= Is my host supported? =

Yes! This plugin should be compatible with any host.

= Can I damage my site with this plugin? =

Yes! Entering a wrong search or replace string could damage your database. Because of this, it is always adviseable to have a backup of your database before using this plugin.

= How does this work on WordPress Multisite? =

When running this plugin on a WordPress Multisite installation, it will only be loaded and visible for Network admins. Network admins can go to the dashboard of any subsite to run a search/replace on just the tables for that subsite, or go to the dashboard of the main/base site to run a search/replace on all tables.

= How can I use this plugin when changing URLs? =

If you're moving your site from one server to another and changing the URL of your WordPress installation, the approach below allows you to do so easily without affecting the old site:

1. Backup the database on your current site
2. Install the database on your new host
3. On the new host, define the new site URL in the `wp-config.php` file, as shown [here](http://codex.wordpress.org/Changing_The_Site_URL#Edit_wp-config.php)
4. Log in at your new admin URL and run Better Search Replace on the old site URL for the new site URL
5. Delete the site_url constant you added to `wp-config.php`. You may also need to regenerate your .htaccess by going to Settings -> Permalinks and saving the settings.

More information on moving WordPress can be found [here](http://codex.wordpress.org/Moving_WordPress).

= I get a white screen when using this plugin? =

This is likely an issue with your PHP memory limit. Try temporarily increasing it by defining the memory limit in your `wp-config.php` file as shown [here](http://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP). Alternatively, if you were searching across multiple tables, try searching on fewer tables at a time to load less into memory.

== Screenshots ==

1. The Better Search Replace page added to the "Tools" menu
2. After running a search/replace dry-run.

== Changelog ==

= 1.4.1 - July 25, 2022 =
* Security: Backup and import filenames now contain a random salt
* Security: Selected tables are now confirmed to exist before processing the request

= 1.4 - April 7, 2022 =
* New: Better Search Replace has a brand new user interface
* Improvement: Default capability required to use the plugin has changed from "install_plugins" to "manage_options" for compatibility with DISALLOW_FILE_MODS
* Improvement: License key no longer requires separate activation step after saving
* Fix: Special characters and double-byte strings now display correctly in previews

= 1.3.8 - December 7, 2020 =
* Improvement: WordPress 5.6 and PHP 8 compatible
* Fix: Strings that have been serialized twice showing up as false-positives

= 1.3.7 - February 26, 2019 =
* Fix: Existing strings that match the "Replace with" string are not highlighted in results
* Fix: Some special characters interfering with search/replace
* Fix: Percentage sign not saved correctly in backups
* Fix: Hyphen in table name interfering with backups
* Security: Pass template filenames through `sanitize_file_name()`
* Security: Verify nonce when downloading diagnostic info

= 1.3.6 - January 22, 2018 =
* Security: Store backup files in temp directory (props @Ov3rfly)

= 1.3.5 - January 3, 2018 =
* Fix: Only one table searched on some environments (props @Ov3rfly)

= 1.3.4 - September 14, 2017 =
* Security: Check if data is serialized before unserializing it
* Improvement: Increased size of table select
* Improvement: Updated plugin updater class
* Improvement: Max Results setting added to diagnostic info for easier troubleshooting
* Fix: Profiles with quotes in name not working correctly
* Fix: Updated links on plugin help tab

= 1.3.3 - November 10, 2016 =
* Fix: Outdated links to old website
* Fix: Prevent requests to invalid tabs

= 1.3.2 - June 2, 2016 =
* Fix: CSS not loaded on details page

= 1.3.1 - December 8, 2015 =
* Added: Link to pro support and documentation in help tab
* Tweak: "Back to Overview" link now fixed to top of "View Details" box
* Fix: Bug with case-insensitive searches in serialized objects
* Fix: Bug with early skip due to lack of primary key

= 1.3 - November 24, 2015 =
* Added Gzip support for backups and imports
* Added ability to run a search/replace profile on a backup
* Small bugfixes

= 1.2.7 =
* Small cleanup
* Improved styles and wording for license status
* Updated translations

= 1.2.6 =
* Improved progress bar info and styles
* Small cleanup

= 1.2.5 =
* Fixed issue preventing profiles from loading

= 1.2.4 =
* Added "Settings saved" notice when saving settings
* Fixed bug with wp_magic_quotes interfering with some search strings

= 1.2.3 =
* Fixed bug with searching for backslashes
* Fixed potential bug with getting tables in large multisites
* Fixed potential notice in append_report
* Improved handling of missing primary keys

= 1.2.2 =
* Fixed AJAX conflict with WooCommerce
* Fixed a few issues with translations
* Tweaked "System Info" to use get_locale() instead of WP_LANG constant
* Updated German translation (props @Linus Ziegenhagen)

= 1.2.1 =
* Fixed minor issue with display of progress bar
* Updated translation file

= 1.2 =
* Switched to AJAX bulk processing
* Decreased minimum "Max Page Size" to 1000
* Bugfixes and improvements

= 1.1.1 =
* Decreased default page size for better performance on shared hosting/large DBs
* Misc bugfixes and small cleanup

= 1.1 =
* Initial release of pro version

= 1.0.6 =
* Added table sizes to the database table listing
* Added French translation (props @Jean Philippe)

= 1.0.5 =
* Added support for case-insensitive searches
* Added German translation (props @Linus Ziegenhagen)

= 1.0.4 =
* Potential security fixes

= 1.0.3 =
* Fixed issue with searching for special characters like '\'
* Fixed bug with replacing some objects

= 1.0.2 =
* Fixed untranslateable strings on submit button and submenu page.

= 1.0.1 =
* Fixed issue with loading translations and added Spanish translation (props Eduardo Larequi)
* Fixed bug with reporting timing
* Updated to use "Dry Run" as default
* Added support for WordPress Multisite (see FAQs for more info)

= 1.0 =
* Initial release
