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

   function imdb_connector_ajax_delete_cache() {
      if(!isset($_POST["nonce"]) || !wp_verify_nonce($_POST["nonce"], "imdb_connector_delete_cache")) {

         wp_die();
      }

      $class_cache  = new IMDb_Connector_Cache();
      $class_movies = new IMDb_Connector_Movies();

      if($class_cache->delete_cache()) {
         wp_send_json_success(
            array(
               "cached_movies" => count($class_movies->get_cached_movies())
            )
         );
      }

      wp_die();
   }

   add_action("wp_ajax_imdb_connector_delete_cache", "imdb_connector_ajax_delete_cache");

   add_action("wp_ajax_nopriv_imdb_connector_delete_cache", "imdb_connector_ajax_delete_cache");