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

   /** Prevents this file from being called directly */
   if(!function_exists("add_action")) {
      return;
   }

   /**
    * Retrieves the plugin's version from the main file.
    *
    * @param string $main_file_name
    *
    * @since 1.3.1
    *
    * @return int
    */
   function imdb_connector_get_plugin_version($main_file_name = "imdb-connector.php") {
      $main_file_path = "";

      $main_file_path .= IMDB_CONNECTOR_PATH;
      $main_file_path .= $main_file_name;

      if(!file_exists($main_file_path)) {
         return 0;
      }

      $handle       = fopen($main_file_path, "rb");
      $file_content = fread($handle, 999);

      preg_match("'Version\:(.*)[\r|\n]'", $file_content, $version);

      if(isset($version[1])) {
         $version = $version[1];
      }
      else {
         return false;
      }

      return trim($version);
   }

   /**
    * Defines the plugin's settings and their default values.
    *
    * @since    0.1
    *
    * @return array
    */
   function imdb_connector_get_default_settings() {
      /** Build the settings and their default values */
      $settings = array(
         "imdb_connector_api_key"              => IMDB_CONNECTOR_API_KEY,
         "imdb_connector_allow_caching"        => "on",
         "imdb_connector_cache_location"       => "database",
         "imdb_connector_auto_delete"          => "off",
         "imdb_connector_deactivation_actions" => array(),
         "imdb_connector_plot_type"            => "plot_short",
         "imdb_connector_debug_mode"           => "on"
      );

      return $settings;
   }

   /**
    * Returns plugin's settings names and their default values.
    *
    * @param $setting
    *
    * @since 0.1
    *
    * @return bool
    */
   function imdb_connector_get_default_setting($setting) {
      if(false === strpos($setting, "imdb_connector_")) {
         $setting = "imdb_connector_" . $setting;
      }

      $settings = imdb_connector_get_default_settings();

      if(!array_key_exists($setting, $settings)) {
         return false;
      }

      return $settings[$setting];
   }

   /**
    * Returns plugin's settings names and their set values; uses default value if not set.
    *
    * @return array
    *
    * @since 0.2
    */
   function imdb_connector_get_settings() {
      $settings = array();

      foreach(imdb_connector_get_default_settings() as $setting => $default_value) {
         $option = get_option($setting);
         $value  = $option;

         if(!$option) {
            $value = $default_value;
         }
         if(!is_array($value) && false !== strpos($value, "%imdb_connector_path%")) {
            $value = str_replace("%imdb_connector_path%", IMDB_CONNECTOR_PATH, $value);
         }

         if($setting === "imdb_connector_api_key" && !$option) {
            $value = IMDB_CONNECTOR_API_KEY;
         }

         $settings[$setting] = $value;
      }

      return $settings;
   }

   /**
    * Returns a specific plugin setting; uses default value if not set.
    *
    * @param $setting
    *
    * @since 1.3.0
    *
    * @return bool|mixed|string
    */
   function imdb_connector_get_setting($setting) {
      $setting  = "imdb_connector_" . $setting;
      $settings = imdb_connector_get_settings();

      /** Use default value if setting is not set */
      if(!$settings[$setting] || $settings[$setting] === "") {
         $setting = imdb_connector_get_default_setting($setting);
      }
      else {
         $setting = $settings[$setting];
      }

      return $setting;
   }

   /**
    * @param bool $default_settings
    * @param bool $overwrite
    *
    * @return bool|false|int
    */
   function imdb_connector_install($default_settings = true, $overwrite = false) {
      $option_name = "imdb_connector_api_key_alert";

      if(get_option($option_name)) {
         delete_option($option_name);
      }

      /** Uses update_option() to create the default options  */
      if($default_settings) {
         foreach(imdb_connector_get_default_settings() as $setting_name => $default_value) {
            if($overwrite || get_option($setting_name) === "") {
               update_option($setting_name, $default_value);
            }
         }
      }

      global $wpdb;

      $table = $wpdb->prefix . IMDB_CONNECTOR_DATABASE_TABLE;

      if($overwrite || !$wpdb->get_var("SHOW TABLES LIKE '$table'")) {
         $query = "DROP TABLE IF EXISTS `$table`;";

         $wpdb->query($query);
      }
      else {
         return false;
      }

      /** Create plugin table */
      $query = "
            CREATE TABLE IF NOT EXISTS `$table` (
               `ID`        bigint(20)  NOT NULL AUTO_INCREMENT,
               `title`     text        NOT NULL,
               `imdbid`    text        NOT NULL,
               `year`      bigint(4)   NOT NULL,
               `rated`     text        NOT NULL,
               `released`  text        NOT NULL,
               `runtime`   text        NOT NULL,
               `genres`    text        NOT NULL,
               `directors` text        NOT NULL,
               `writers`   text        NOT NULL,
               `actors`    text        NOT NULL,
               `languages` text        NOT NULL,
               `countries` text        NOT NULL,
               `plot`      longtext    NOT NULL,
               `awards`    text        NOT NULL,
               `poster`    text        NOT NULL,
               `metascore` text        NOT NULL,
               `imdbvotes` text        NOT NULL,
               `imdbrating`text        NOT NULL,
               `type`      text        NOT NULL,
               `website`   text        NOT NULL,
               `ratings`   text        NOT NULL,
               `boxoffice` text        NOT NULL,
               `production`text        NOT NULL,
               PRIMARY KEY (`ID`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ";

      return $wpdb->query($query);
   }

   /**
    * Removes generated cache files.
    *
    * @since  0.4
    *
    * @return bool
    */
   function imdb_connector_uninstall() {
      $option = imdb_connector_get_setting("deactivation_actions");

      /** Delete MySQL table */
      if(in_array("database", $option, false)) {
         global $wpdb;
         $table = $wpdb->prefix . IMDB_CONNECTOR_DATABASE_TABLE;
         $wpdb->query("DROP TABLE $table");
      }

      /** Delete cached posters */
      if(in_array("posters", $option, false)) {
         $posters = glob(IMDB_CONNECTOR_CACHE_PATH . "/*.jpg");
         foreach($posters as $poster) {
            unlink($poster);
         }
      }

      /** Delete cached movie details in cache directory */
      if(in_array("movie_details", $option, false)) {
         $movie_details = glob(IMDB_CONNECTOR_CACHE_PATH . "/*.tmp");
         foreach($movie_details as $movie_detail) {
            unlink($movie_detail);
         }
      }

      /** Deletes plugin settings */
      if(in_array("settings", $option, false)) {
         foreach(imdb_connector_get_settings() as $setting_name => $setting_value) {
            delete_option($setting_name);
         }
      }

      return true;
   }

   /**
    * Checks if option has a specific value and makes HTML input checked/unchecked.
    *
    * @param        $setting
    * @param        $check_value
    *
    * @param string $type
    *
    * @since 0.1
    */
   function imdb_connector_check_setting($setting, $check_value, $type = "checked") {
      if(imdb_connector_get_setting($setting) === $check_value) {
         echo " $type=\"$type\"";
      }
   }