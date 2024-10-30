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
    * Load plugin's styles.
    *
    * @since 0.1
    */
   function load_imdb_connector_styles_and_scripts() {
      $root    = IMDB_CONNECTOR_URL;
      $version = imdb_connector_get_plugin_version();

      /** Default widgets styles */
      wp_enqueue_style("imdb-connector-style-widget", "$root/styles/widgets.css", array(), $version);
   }

   add_action("wp_enqueue_scripts", "load_imdb_connector_styles_and_scripts");

   /**
    * Loads plugin's scripts and styles used on the Dashboard.
    *
    * @since 0.1
    */
   function load_imdb_connector_admin_styles_and_scripts() {
      $root    = IMDB_CONNECTOR_URL;
      $version = imdb_connector_get_plugin_version();
      $screen  = get_current_screen()->base;

      if($screen === "settings_page_imdb-connector") {
         /** Styles */
         wp_enqueue_style(
            "imdb-connector-style-admin",
            "$root/styles/admin.css",
            array(),
            $version
         );

         wp_enqueue_style(
            "imdb-connector-style-font-awesome",
            "$root/fonts/font-awesome/css/font-awesome.min.css",
            array(),
            "4.7.0"
         );

         /** Scripts */
         wp_enqueue_script(
            "imdb-connector-script-admin",
            "$root/scripts/admin.js",
            array("jquery"),
            $version
         );
      }
      elseif($screen === "widgets") {
         wp_enqueue_style(
            "imdb-connector-style-widgets",
            "$root/styles/widgets.css",
            array(),
            $version
         );

         wp_enqueue_style(
            "imdb-connector-style-font-awesome",
            "$root/fonts/font-awesome/css/font-awesome.min.css",
            array(),
            "4.7.0"
         );
      }
   }

   add_action("admin_enqueue_scripts", "load_imdb_connector_admin_styles_and_scripts");

   /**
    * Initializes plugin's setting page.
    *
    * @since 0.1
    */
   function init_admin_settings_page() {
      /** Creates a new page on the admin interface */
      add_options_page(
         __("Settings", "imdb-connector"),
         "IMDb Connector",
         "manage_options",
         "imdb-connector",
         "build_admin_settings_page"
      );
   }

   add_action("admin_menu", "init_admin_settings_page");

   /**
    * Checks if the current date is over the set limit
    * and deletes the cache accordingly.
    *
    * @since 0.4
    *
    * @return bool
    */
   function init_imdb_connector_auto_delete() {
      $setting = imdb_connector_get_setting("auto_delete");

      if($setting !== "off") {
         return false;
      }

      $date_now          = date("Y-m-d H:i:s");
      $date_last_deleted = get_option("imdb_connector_last_deleted_date");
      if(!$date_last_deleted) {
         return update_option("imdb_connector_last_deleted_date", $date_now);
      }

      $difference = date_diff(new DateTime($date_now), new DateTime($date_last_deleted));
      $delete     = false;

      if($setting === "24_hours") {
         if($difference->d || $difference->m || $difference->y) {
            $delete = true;
         }
      }
      elseif($setting === "7_days") {
         if($difference->d >= 7 || $difference->m || $difference->y) {
            $delete = true;
         }
      }
      elseif($setting === "30_days") {
         if($difference->d >= 30 || $difference->m || $difference->y) {
            $delete = true;
         }
      }
      elseif($setting === "3_months") {
         if($difference->m >= 3 || $difference->y) {
            $delete = true;
         }
      }
      elseif($setting === "6_months") {
         if($difference->m >= 6 || $difference->y) {
            $delete = true;
         }
      }

      /** Delete the cache and update the last deleted date */
      if($delete) {
         $class = new IMDb_Connector_Cache();

         if($class->delete_cache()) {
            update_option("imdb_connector_last_deleted_date", $date_now);
         }
      }

      return true;
   }

   add_action("plugins_loaded", "init_imdb_connector_auto_delete");

   /**
    * Adds the shortcode [imdb_movie_detail title="" detail=""]
    */
   function imdb_connector_add_shortcode() {
      if(get_option("imdb_connector_allow_shortcodes")) {
         delete_option("imdb_connector_allow_shortcodes");
      }

      add_shortcode("imdb_movie_detail", "imdb_connector_shortcode_movie_detail");
   }

   add_action("init", "imdb_connector_add_shortcode");

   /**
    * Extends plugin database to be compatible with 1.5.2.
    *
    * @since 1.5.2
    *
    * @return bool
    */
   function imdb_connector_update_database() {
      $current_plugin_version = IMDB_CONNECTOR_VERSION;
      $saved_plugin_version   = (float)get_option("imdb_connector_saved_version");
      $update_successful      = true;

      if($saved_plugin_version) {
         $update_successful = false;
      }
      else {
         global $wpdb;

         $table   = $wpdb->prefix . IMDB_CONNECTOR_DATABASE_TABLE;
         $columns = array(
            "website",
            "ratings",
            "boxoffice",
            "production"
         );

         foreach($columns as $column) {
            $column_length = $wpdb->get_col_length($table, $column);

            if(!$column_length) {
               $result = $wpdb->query("ALTER TABLE $table ADD $column TEXT");

               if(!$result) {
                  $update_successful = false;
               }
            }
         }
      }

      if($update_successful) {
         update_option("imdb_connector_saved_version", $current_plugin_version);
      }

      return $update_successful;
   }

   add_action("plugins_loaded", "imdb_connector_update_database");

   function imdb_connector_init_widget() {
      //register_widget("IMDb_Connector_Widget");
   }

   add_action("widgets_init", "imdb_connector_init_widget");