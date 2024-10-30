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
    * Class IMDb_Connector_Movies
    */
   class IMDb_Connector_Movies {
      /**
       * Returns a movie including all details provided by
       * the unofficial API at omdbapi.com.
       *
       * @param       $id_or_title
       * @param array $options
       *
       * @since 0.1
       *
       * @return array
       */
      public function get_movie($id_or_title, array $options = array()) {
         /** Define default function options */
         $default_options = array(
            "format"         => "array",
            "allow_caching"  => imdb_connector_get_setting("allow_caching"),
            "cache_location" => imdb_connector_get_setting("cache_location"),
            "year"           => 0
         );

         /** Use default option value if option is not set */
         foreach($default_options as $option_name => $default_value) {
            if(!array_key_exists($option_name, $options) || !$options[$option_name]) {
               $options[$option_name] = $default_value;
            }
         }

         /** Define variables */
         $api_key = imdb_connector_get_setting("api_key");
         $api_url = "http://www.omdbapi.com/?apikey=$api_key&";
         $type    = "t";

         /** Check whether $id_or_title is an IMDb ID */
         if(substr($id_or_title, 0, 2) === "tt") {
            $type = "i";
         }

         /** Sanitize $id_or_title to be URL friendly */
         $id_or_title_url = imdb_connector_sanitize_url_title($id_or_title);

         /** Build request API URL */
         $api_url .= $type . "=" . $id_or_title_url;

         /** Determine whether to use the short or the full plot */
         if(imdb_connector_get_setting("plot_type") === "plot_full") {
            $api_url .= "&plot=full";
         }

         if($options["year"]) {
            $api_url .= "&y=" . $options["year"];
         }

         $movie_details = array();

         /** When caching feature has been activated */
         if(imdb_connector_get_setting("allow_caching") !== "off") {
            $file_name            = substr(md5($id_or_title), 0, 8);
            $cache_directory_path = IMDB_CONNECTOR_CACHE_PATH;
            $cache_directory_url  = IMDB_CONNECTOR_CACHE_URL;
            $cache_file_path      = $cache_directory_path . "/" . $file_name . ".tmp";

            /** Display error message if the directory doesn't exist and can't be created automatically */
            if(!wp_mkdir_p($cache_directory_path)) {
               imdb_connector_log_error(sprintf(__("Cache directory \"%s\" could not be created.", "imdb-connector"), $cache_directory_path));

               return array();
            }

            if($options["cache_location"] === "local") {
               /** Get details from cached file if it exists */
               if(file_exists($cache_file_path)) {
                  $data          = file_get_contents($cache_file_path);
                  $movie_details = json_decode($data, true);
               }

               /** Get movie details online and create cache file */
               else {
                  $data = wp_safe_remote_get($api_url);

                  if(is_wp_error($data)) {
                     return array();
                  }

                  $data          = $data["body"];
                  $movie_details = json_decode($data, true);

                  if($movie_details["Response"] === "False") {
                     imdb_connector_log_error(sprintf(__("The requested movie \"%s\" could not be found. Please make sure the movie title is the original movie title and/or check your spelling.", "imdb-connector"), $id_or_title));

                     return array();
                  }

                  $handle        = fopen($cache_file_path, "ab");
                  $movie_details = imdb_connector_sanitize_movie_details($movie_details);

                  fwrite($handle, json_encode($movie_details));
                  fclose($handle);
               }
            }
            elseif($options["cache_location"] === "database") {
               global $wpdb;

               $table = $wpdb->prefix . IMDB_CONNECTOR_DATABASE_TABLE;
               $query = "SELECT * FROM $table ";

               if($type === "i") {
                  $query .= "WHERE imdbid = '" . $id_or_title . "'";
               }
               else {
                  $query .= "WHERE title = '$id_or_title'";
               }

               $movie_details = (array)$wpdb->get_row($query, "ARRAY_A");

               /** Read row and convert serialized strings back to array */
               if($movie_details) {
                  foreach($movie_details as $movie_detail => $value) {
                     if(is_serialized($value)) {
                        $movie_details[$movie_detail] = unserialize($value);
                     }
                  }
               }
               /** Movie doesn't exist in the database, so we add it */
               else {
                  $movie_details = (array)imdb_connector_process_json($api_url);

                  if(!isset($movie_details["response"]) || $movie_details["response"] === "False") {
                     imdb_connector_log_error(sprintf(__("The requested movie \"%s\" could not be found. Please make sure the movie title is the original movie title and/or check your spelling.", "imdb-connector"), $id_or_title));

                     return array();
                  }

                  if(!is_array($movie_details) || !isset($movie_details["title"])) {
                     return array();
                  }

                  /** @var $new_details
                   *
                   * These are new movie details that not all movies
                   * on IMDb have. If they don't have one of these, it used to
                   * break down. Instead, we're now filling it with nothing.
                   *
                   * @added 1.6.0
                   *
                   */
                  $new_details = array(
                     "website",
                     "boxoffice",
                     "production"
                  );

                  foreach($new_details as $new_detail) {
                     if(!isset($movie_details[$new_detail])) {
                        $movie_details[$new_detail] = "";
                     }
                  }

                  $formats = array();
                  $data    = array(
                     "title"      => $movie_details["title"],
                     "imdbid"     => $movie_details["imdbid"],
                     "year"       => $movie_details["year"],
                     "released"   => $movie_details["released"],
                     "runtime"    => serialize($movie_details["runtime"]),
                     "genres"     => serialize($movie_details["genres"]),
                     "writers"    => serialize($movie_details["writers"]),
                     "directors"  => serialize($movie_details["directors"]),
                     "actors"     => serialize($movie_details["actors"]),
                     "languages"  => serialize($movie_details["languages"]),
                     "countries"  => serialize($movie_details["countries"]),
                     "ratings"    => serialize($movie_details["ratings"]),
                     "rated"      => $movie_details["rated"],
                     "poster"     => $movie_details["poster"],
                     "awards"     => $movie_details["awards"],
                     "plot"       => $movie_details["plot"],
                     "metascore"  => $movie_details["metascore"],
                     "imdbrating" => $movie_details["imdbrating"],
                     "imdbvotes"  => $movie_details["imdbvotes"],
                     "type"       => $movie_details["type"],
                     "website"    => $movie_details["website"],
                     "boxoffice"  => $movie_details["boxoffice"],
                     "production" => $movie_details["production"]
                  );

                  foreach($data as $key => $value) {
                     $format = "%s";

                     if(is_int($value)) {
                        $format = "%d";
                     }
                     elseif(is_float($value)) {
                        $format = "%f";
                     }
                     $formats[] = $format;
                  }

                  $wpdb->insert($table, $data, $formats);
               }
            }

            /** Create movie poster if it doesn't exist yet */
            $poster_path = $cache_directory_path . "/" . $file_name . ".jpg";
            $poster_url  = $cache_directory_url . "/" . $file_name . ".jpg";

            if(imdb_connector_get_setting("allow_caching") === "on") {
               if(!file_exists($poster_path) && strpos($movie_details["poster"], "://")) {
                  require_once ABSPATH . "/wp-admin/includes/file.php";

                  $poster_temp_path = download_url($movie_details["poster"]);

                  if(is_wp_error($poster_temp_path)) {
                     imdb_connector_log_error(sprintf(__("The poster of the movie \"%s\" could not be saved on your server.", "imdb-connector"), $id_or_title));
                  }
                  else {
                     copy($poster_temp_path, $poster_path);
                  }
               }

               $movie_details["poster"] = $poster_url;
            }
         }
         /** Get online movie details if cache is deactivated */
         else {
            /** Fetch JSON data */
            $data = wp_remote_get($api_url);

            /** Stop if downloading JSON fails */
            if(is_wp_error($data)) {
               return array();
            }

            /** Specify JSON data and turn it into a proper movie details array */
            $data          = $data["body"];
            $movie_details = json_decode($data, true);
            $movie_details = imdb_connector_sanitize_movie_details($movie_details);
         }

         $movie_details = apply_filters("imdb_connector_movie_details", $movie_details);
         /*$genres        = $movie_details["genres"];
         $genres_count  = count($genres);

         if(isset($genres)) {
            for($round = 0; $round <= $genres_count - 1; $round++) {
               $movie_details["genres"][$round] = __($movie_details["genres"][$round], "imdb-connector");
            }
         }*/

         /** Convert movie details into object if set */
         if($options["format"] === "object") {
            $movie_details = json_decode(json_encode($movie_details));
         }

         return $movie_details;
      }

      /**
       * Returns - if available - a certain movie detail.
       *
       * @param        $id_or_title
       * @param string $detail
       *
       * @since 0.1
       *
       * @return string|array
       */
      public function get_movie_detail($id_or_title, $detail) {
         $movie = imdb_connector_get_movie($id_or_title);
         if(!$movie) {
            return "";
         }

         $deprecated = array(
            "genre",
            "country",
            "language",
            "director",
            "writer"
         );

         if(in_array($detail, $deprecated, false)) {
            $new_detail = $detail . "s";

            if($detail === "country") {
               $new_detail = "countries";
            }

            _deprecated_argument("get_imdb_connector_movie_detail", "0.4", "Use <strong>$new_detail</strong> instead.");

            $detail = $new_detail;
         }
         elseif(!array_key_exists($detail, $movie)) {
            return "";
         }

         return $movie[$detail];
      }

      /**
       * @param array $titles_or_ids
       *
       * @since 0.2
       *
       * @return array|bool
       */
      public function get_movies(array $titles_or_ids) {
         $movies    = array();
         $not_found = array();
         foreach($titles_or_ids as $title_or_id) {
            $movie = imdb_connector_get_movie($title_or_id);
            if(!$movie) {
               $not_found[] = $title_or_id;
               continue;
            }
            $movies[] = $movie;
         }
         /** Display error message if one or more movies could not be found */
         if(count($not_found) >= 1) {
            echo " " . implode(", ", $not_found);
         }

         return $movies;
      }

      /**
       * Searches for movies that contain the set title or ID.
       *
       * @param $id_or_title
       *
       * @since 0.2
       *
       * @return array
       */
      public function search_movie($id_or_title) {
         $api_url = "http://www.omdbapi.com/?s=" . imdb_connector_sanitize_url_title($id_or_title);
         $results = file_get_contents($api_url);
         $results = (array)json_decode($results, true);

         if(array_key_exists("Response", $results) && $results["Response"] === "False") {
            return array();
         }
         $results = imdb_connector_sanitize_movie_details($results);

         return (array)$results["search"];
      }

      /**
       * Searches for movies that contain the set titles or IDs.
       *
       * @param array $ids_or_titles
       *
       * @since    0.2
       *
       * @return array
       */
      public function search_movies(array $ids_or_titles) {
         $results = array();
         foreach($ids_or_titles as $id_or_title) {
            $result = $this->search_movie($id_or_title);
            if(!$result) {
               continue;
            }
            $results[] = $result;
         }

         return $results;
      }

      /**
       * Returns if the set query returns valid movie details.
       *
       * @param $id_or_title
       *
       * @since 0.1
       *
       * @return bool
       */
      public function has_movie($id_or_title) {
         if(!$this->get_movie($id_or_title)) {
            return false;
         }

         return true;
      }

      /**
       * Retrieves all movies cached by IMDb Connector.
       *
       * @param string $cache_location
       * @param string $type
       *
       * @since 0.4
       *
       * @return array
       */
      public function get_cached_movies($cache_location = "all", $type = "array") {
         $movies = array();

         if($cache_location === "all" || $cache_location === "local") {
            foreach(glob(IMDB_CONNECTOR_CACHE_PATH . "/*.tmp") as $file) {
               $movie    = json_decode(file_get_contents($file), true);
               $movies[] = $movie;
            }
         }
         if($cache_location === "all" || $cache_location === "database") {
            global $wpdb;
            $table           = $wpdb->prefix . IMDB_CONNECTOR_DATABASE_TABLE;
            $selected_movies = $wpdb->get_results("SELECT * FROM $table", "ARRAY_A");
            if(!count($selected_movies)) {
               return $movies;
            }

            foreach((array)$selected_movies as $movie_details) {
               $movie = array();

               foreach((array)$movie_details as $movie_detail => $value) {
                  if(is_serialized($value)) {
                     $value = unserialize($value);
                  }

                  $movie[$movie_detail] = $value;
               }
               $movies[] = $movie;
            }
         }
         /** Convert array to stdClass object if set */
         if($type === "object") {
            $movies = json_decode(json_encode($movies));
         }

         return (array)$movies;
      }
   }