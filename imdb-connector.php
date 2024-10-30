<?php
   /**
    * Developed by: Kolja Nolte
    * Email: kolja.nolte@gmail.com
    * Website: https://www.koljanolte.com
    * PGP key: https://goo.gl/Bb4Ku2
    *
    * This application is free software: you can redistribute it and/or modify
    * it under the terms of the GNU General Public License as published by
    * the Free Software Foundation, either version 3 of the License, or
    * (at your option) any later version as long as you give credits to the core developer(s).
    * See gnu.org for more.
    *
    * @package IMDb Connector
    */

   /**
    * Plugin Name:      IMDb Connector
    * Plugin URI:       https://www.koljanolte.com/wordpress/plugins/imdb-connector/documentation/
    * Description:      A simple plugin that allows you to easily fetch movie
    *                   and series details from IMDb.com.
    * Version:          1.6.0
    * Author:           Kolja Nolte
    * Author URI:       https://www.koljanolte.com
    * License:          GNU General Public License v2 or later License
    * URI:              http://www.gnu.org/licenses/gpl-2.0.html
    * Text Domain:      imdb-connector
    * Domain Path:      /languages
    */

   /** Prevents this file from being called directly */
   if(!function_exists("add_action")) {
      return;
   }

   $upload_directory = wp_upload_dir();

   /** Enter your key for the API of omdb.com if you chose not to share yours with the community */
   define("IMDB_CONNECTOR_API_KEY", "82145dd6");

   /** Define the absolute path to the plugin directory  */
   define("IMDB_CONNECTOR_PATH", plugin_dir_path(__FILE__));

   /** Define the URL to the plugin directory */
   define("IMDB_CONNECTOR_URL", plugins_url("", __FILE__));

   /** Define the absolute path to the plugins's cache directory */
   define("IMDB_CONNECTOR_CACHE_PATH", $upload_directory["basedir"] . "/imdb-connector");

   /** Define the URL that leads to the cache directory */
   define("IMDB_CONNECTOR_CACHE_URL", $upload_directory["baseurl"] . "/imdb-connector");

   /** Define current plugin version */
   define("IMDB_CONNECTOR_VERSION", "1.6.0");

   /** Table name in WordPress' database to store movie details */
   define("IMDB_CONNECTOR_DATABASE_TABLE", "imdb_connector");

   /** Include plugin files */
   $include_directories = array(
      "admin",
      "classes",
      "includes",
      "widgets"
   );

   /** Loop through the set directories */
   foreach((array)$include_directories as $include_directory) {
      $include_directory = plugin_dir_path(__FILE__) . $include_directory;
      $include_directory = realpath($include_directory);

      /** Skip directory if it's not a valid directory */
      if(!is_dir($include_directory)) {
         continue;
      }

      /** Gather all .php files within the current directory */
      $include_files = glob($include_directory . "/*.php");
      foreach($include_files as $include_file) {
         /** Include current file */
         include_once $include_file;
      }
   }

   /** Execute function when activating plugin */
   register_activation_hook(__FILE__, "imdb_connector_install");

   /** Execute function when deactivating plugin */
   register_deactivation_hook(__FILE__, "imdb_connector_uninstall");

   /**
    * Loads the text domain for localization from languages/ directory.
    *
    * @since 1.0
    */
   function imdb_connector_load_textdomain() {
      load_plugin_textdomain(
         "
         imdb-connector",
         false,
         basename(dirname(__FILE__)) . "/languages"
      );
   }

   add_action("plugins_loaded", "imdb_connector_load_textdomain");