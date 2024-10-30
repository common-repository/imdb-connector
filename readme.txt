=== IMDb Connector ===
Contributors:        thaikolja
Donate link:         https://www.paypal.me/koljanolte/10
Tags:                imdb, imdb connector, imdb database, movie, movies, movie details, movie database
Requires at least:   3.0
Tested up to:        4.9.1
Requires PHP:        5.3
Stable tag:          trunk
License:             GNU General Public License v2 or later
License URI:         http://www.gnu.org/licenses/gpl-2.0.html

A simple plugin that allows you to display and use movie details from IMDb.com easily.

== Description ==

**IMDb Connector** is a simple plugin that lets you easily access the [IMDb.com](http://www.imdb.com) database through the API provided by [omdbapi.com](http://www.omdbapi.com) and get details for specific movies and series. The details can be cached in your database or on your web server to speed up your website.

The plugin comes with the following features:

* [**PHP functions**](https://thaikolja.gitbooks.io/imdb-connector/references/php-functions.html) that allow theme and plugin developers to parse information for a particular movie easily,
* [**shortcodes**](https://thaikolja.gitbooks.io/imdb-connector/references/shortcodes.html) which you can use to display movie details inside posts, pages and custom post types,
* a [**settings page**](https://thaikolja.gitbooks.io/imdb-connector/usage/settings.html) that lets you (de)activate features and customise the way IMDb Connector works,
* and a [**widget**](http://codex.wordpress.org/Widgets_API) that lets you display the movie details within your sidebar.

**IMPORTANT:** The API provided by omdbapi.com has gone private and requires an API key. Luckily, this can be obtained for free within a few minutes on [omdbapi.com's website](http://www.omdbapi.com/apikey.aspx).

**For a more detailed description of how IMDb Connector works and what you are able to do with it (and how), please refer to the [official documentation](https://www.koljanolte.com/wordpress/plugins/imdb-connector/documentation/).**

== Installation ==

= How to install =

1. Install IMDb Connector either through WordPress' native plugin installer found under *Plugins > Install* or copy the *imdb-connector* folder into the */wp-content/plugins/* directory of your WordPress installation.
2. Activate the plugin in the plugin section of your admin interface.
3. Go to *Settings > IMDb Connector* to customise the plugin as desired.

= How to get an API key =

In order to get an API key, visit [omdbapi.com's website](http://www.omdbapi.com/) and click on the top menu item named "[API Key](http://www.omdbapi.com/apikey.aspx)". Tick "FREE" and fill out the form. Within a few minutes, you should receive an API key which you must insert into the right field on the settings page on IMDb Connector (*Settings > IMDb Connector*).

== Screenshots ==

1. The plugin's settings page.
2. The standard widget displayed in a sidebar.
3. The widget configuration on the admin interface.

== Changelog ==

= 1.6.0 =
* Removed annoying donate banner that would not close.
* Improved auto cache delete and added options (every 24 hours, 7 days, 30 days, 3 months, 6 months).

= 1.5.2 =
* Added shortcode maker on the plugin's settings page.
* Added field to use personal API key.
* Added compatibility with WordPress 4.8.2.
* Added donate button to setting's page to purchase API key.
* Added option to enable/disable error logging ("debugging").
* Changed local cache location from the plugin's directory to `uploads/imdb-connector/`.
* Removed custom database table name.
* Removed auto database table creation switch.
* Errors will now be added to the debug.log file if `WP_DEBUG` and `WP_DEBUG_LOG` is activated.
* Thanks for the donations from Flavia B., Wayne F. and Mike L.

= 1.5.1 =
* Temporary hotfix for API usage.
* Added donation announcement.
* Compatibility with WordPress 4.8.

= 1.5.0 =
* Added administration option to choose between short and full movie plot.
* Compatibility with WordPress 4.5.2
* Updated Font Awesome to 4.6.3.

= 1.4.2 =
* Compatibility with WordPress 4.4.1.

= 1.4.1 =
* Changed table format for "released" movie detail from integer to string, so it no longer returns just the year number but the actual date (YYY-MM-DD). **Note:** To apply the change, you must drop the whole *imdb_connector* table in your MySQL database. Thanks to [selse](https://wordpress.org/support/profile/wwwp) for pointing this out.
* Updated translations.

= 1.4.0 =
* Added shortcode detail "poster_url" to display movie's poster URL. Please see the ["Shortcodes" area in the official documentation](http://www.koljanolte.com/wordpress/plugins/imdb-connector/#Shortcodes) for more information.
* Moved functions to classes `IMDb_Connector_Movies` and `IMDb_Connector_Cache`.
* Updated translations.
* PHP 7 support.
* Cleaned up code.
* Minor cosmetic changes.

= 1.3.4 =
* Removed use of deprecated function in movie widget (thanks to [MajorFusion](https://wordpress.org/support/profile/majorfusion)).

= 1.3.3 =
* [Extended shortcodes](https://wordpress.org/support/topic/movies-tagline-runtime-format-poster-embed-shortcode) which now accepts several more attributes to let users customise the output even more individually. Please see the ["Shortcodes" area in the official documentation](http://www.koljanolte.com/wordpress/plugins/imdb-connector/#Shortcodes) for an overview of all available attributes.
* Added compatibility for WordPress 4.3 that has just been released.
* Added "Reset Settings" button to the settings page.
* Fixed bug resulting in an error when activating IMDb Connector.

= 1.3.2 =
* Changed several function names to be deprecated to make it more organised. All functions now start with `imdb_connector_*`
* Cleaned up and optimised main.js.
* Small face lifting on the settings page.
* Removed debug mode.

= 1.3.1 =
* Fixed bug resulting in corrupt JSON file provided by omdbapi.com, making it unable for the plugin to work (thanks to [jcandsv](https://wordpress.org/support/profile/jcandsv)).
* Added Font Awesome icons to plugin's settings page.
* Small code improvements.

= 1.3.0 =
* Added more shortcode parameters and details. From now on you can display the runtime either as "runtime-minutes", "runtime-hours" or as "runtime-timestamp".
* Re-programmed some sections.
* Updated translations.

= 1.2.1 =
* WordPress 4.2.3 compatibility.
* Updated translations.

= 1.2.0 =
* Fixed bug.

= 1.1.0 =
* WordPress 4.2.2 compatibility.

= 1.1 =
* Added compatibility with WordPress 4.2.1.
* Updated translations.

= 1.0 =
* Stable release.
* Code cleanup and other small optimizations.
* Updated [documentation](www.koljanolte.com/wordpress/plugins/imdb-connector/).
* Updated translations.
* Updated screenshots.

= 0.6.2 =
* Fixed bug with newly added movies that do not contain all values.

= 0.6.1 =
* [Fixed small bug](https://wordpress.org/support/topic/php-connector-not-working).
* Updated/added translations.

= 0.6 =
* Fixed bug with PHP version below 5.2.
* Cleaned up code.

= 0.5 =
* Added plugin installer icon.
* Code rearrangements.
* Updated translations.

= 0.4.3 =
* Added "imdbrating" field.
* Updated translations.

= 0.4.2 =
* Added function PHP function `imdb_connector_get_cached_movies()`.
* Added [nonce protection for "delete cache" script](http://codex.wordpress.org/WordPress_Nonces) to prevent misuse.
* Added nonce protection for settings page.
* Updated translations.

= 0.4.1 =
* Fixed shortcode movie details with multiple values in it.

= 0.4 =
* MySQL cache is now stored in a separate table.
* Added feature to select the table name the cache data is being stored.
* Added feature to delete the cache after a certain time automatically.
* Added feature allowing admins to chose what cached files and settings IMDb Connector should keep after disabling the plugin.
* Added "type" movie detail that returns the type (documentary, series, movie, ...) of the movie.
* Renamed movie details "genre", "country", "language", "writer" and "director" to plural names.
* Updated translations.

= 0.3 =
* Added option to chose if the movie detail cache should be stored locally on in MySQL.
* Added an option to the settings page that defines whether the movie poster should be cached or not.
* Added "format" option array to imdb_get_connector_movie() function that defines whether the output should be an "array" or "object".
* Added translations and updated existing ones.
* The movie details "genre", "director", "writer", "actors", "country" and "language" are split up in arrays.
* The movie detail "runtime" is now an array containing "timestamp", "minutes" and "hours".
* Removed "Use default widgets style" from the settings page.

= 0.2 =
* Added "Delete cache" function on settings page.
* Added several PHP functions, e.g. search_imdb_connector_movies().
* Added debug mode to display errors and warnings.
* Added several translations and updated existing ones.
* Fixed "headers already sent" bug on plugin activation.
* Fixed bug that prevented translations from being loaded.
* Fixed [bug](https://wordpress.org/support/topic/imdb-connector-dont-import-some-movies-informations) when a string run through `wptexturize()` is used for the IMDb title ([thanks to 7movies](https://wordpress.org/support/profile/7movies)).
* Changed `get_imdb_*` functions to `imdb_get_connector_*` to avoid conflicts with other plugins.
* Updated documentation.
* Rebuild movie widget.
* Restructured plugin files.

= 0.1.2 =
* Hotfix.

= 0.1 =
* Initial release.

== Upgrade Notice ==

= 1.6.0 =
**Good news:** API keys are still needed, but you can get them **for free**. [Click here](http://www.omdbapi.com/apikey.aspx).

= 1.5.1 =
IMDb Connector is working again. But [your support](https://wordpress.org/plugins/imdb-connector/#installation) is still needed to keep the plugin running.

= 1.5.0 =
Added administration option to choose between short and full movie plot.

= 1.4.2 =
Compatibility with WordPress 4.4.1.

= 1.4.1 =
Changed table format for "released" movie detail from integer to string, so it no longer returns just the year number but the actual date (YYY-MM-DD). **Note:** To apply the change, you must drop the whole *imdb_connector* table in your MySQL database.

= 1.4.0 =
Moved functions to classes `IMDb_Connector_Movies` and `IMDb_Connector_Cache` and added shortcode "poster_url".

= 1.3.4 =
Fixed movie widget.

= 1.3.3 =
The shortcodes have been extended and accept now several more attributes to let users customise the output individually. For a full list of available attributes and examples, please see the ["Shortcodes" section in the official documentation](http://www.koljanolte.com/wordpress/plugins/imdb-connector/#Shortcodes).

= 1.3.2 =
**IMPORTANT**: In this version most functions are now deprecated, meaning that they still work under their old name, but you should change them to the new one if you use them independently in your blog. Every IMDb Connector function starts with `imdb_connector_*`. If you experience any problems, please report them either in the [support forum](https://wordpress.org/support/plugin/imdb-connector) or directly via [e-mail](mailto:kolja.nolte@gmail.com), so I can fix it as soon as possible.

= 1.3.1 =
Important hotfix.

= 1.3.0 =
New shortcode detail parameters: "runtime-minutes", "runtime-hours" and "runtime-timestamp".

= 1.2.1 =
Translations update.

= 1.2.0 =
Bug fixes.

= 1.1.0 =
WordPress 4.2.2 compatibility.

= 1.1 =
Added compatibility with WordPress 4.2.1.

= 1.0 =
Stable release with small optimizations.

= 0.6.2 =
Fix for details of newly added movies.

= 0.6.1 =
Minor bug fixes and updated/new translations.

= 0.6 =
Bug fix for PHP version < 5.3.

= 0.5 =
Cosmetic fixes; plugin installer icon.

= 0.4.3 =
Added "imdbrating" field.

= 0.4.2 =
Security fix, translations updates.

= 0.4.1 =
Shortcode fix.

= 0.4 =
Major update with many new functions (auto delete, MySQL caching, deactivation actions), bug fixes and corrections.

= 0.3 =
**IMPORTANT:** The array key names have been renamed and partly reformatted. Please see "PHP functions" section in the [official documentation](http://www.koljanolte.com/wordpress/plugins/imdb-connector/) for the new structure.

= 0.2 =
Major update with many bug fixes and new features and functions. See changelog for more information.

= 0.1.2 =
Implemented hotfix.

= 0.1 =
This is the first release of IMDb Connector.