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
    * Builds the plugin's settings page.
    */
   function build_admin_settings_page() {
      $saved               = false;
      $external_plugin_url = "https://www.koljanolte.com/plugins/imdb-connector";

      /** Save settings */
      if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["save_settings_nonce"]) && wp_verify_nonce($_POST["save_settings_nonce"], "save_settings")) {

         foreach(imdb_connector_get_settings() as $setting => $value) {
            $field = str_replace("imdb_connector_", "", $setting);

            /** Skip setting that hasn't been set */
            if(!isset($_POST[$field])) {
               continue;
            }

            update_option($setting, $_POST[$field]);
         }

         $saved = true;
      }
      elseif(isset($_GET["action"], $_GET["nonce"]) && $_GET["action"] === "reset_settings" && wp_verify_nonce($_GET["nonce"], "reset_settings")) {
         foreach(imdb_connector_get_default_settings() as $setting => $default_value) {
            update_option($setting, $default_value);
         }
         ?>
         <div class="updated">
            <p><?php _e("All settings have been successfully resetted to default.", "imdb-connector"); ?></p>
         </div>
         <?php
      }

      ?>
      <div class="wrap" id="imdb-connector-settings">
         <h2>
            <i class="fa fa-film"></i>
            <?php echo "IMDb Connector &raquo; " . get_admin_page_title(); ?>
         </h2>
         <?php if($saved) { ?>
            <div class="updated settings-error">
               <p><?php _e("The settings have been successfully saved.", "imdb-connector"); ?></p>
            </div>
         <?php } ?>
         <form method="post"
            action="<?php echo admin_url(); ?>options-general.php?page=imdb-connector"
            id="settings-form">
            <table class="form-table">
               <tbody>
                  <tr id="row-donate">
                     <th>
                        <label for="donate-button">
                           <i class="fa fa-dollar"></i>
                           <?php _e("Donate", "imdb-connector"); ?>
                        </label>
                     </th>
                     <td>
                        <a href="https://www.paypal.me/thaikolja/" id="donate-button"
                           target="_blank">
                           <button class="button button-large button-primary" type="button">
                              <i class="fa fa-paypal"></i>
                              <?php _e("Donate via PayPal", "imdb-connector"); ?>
                           </button>
                        </a>
                        <span class="optional">
                           (<?php _e("optional", "imdb-connector"); ?>)
                        </span>
                        <p class="description">
                           <?php
                              echo sprintf(
                                 __("", "imdb-connector"),
                                 "https://www.paypal.me/thaikolja/"
                              );
                           ?>
                        </p>
                     </td>
                  </tr>
                  <tr id="api-key-row">
                     <th>
                        <label for="api-key">
                           <i class="fa fa-key"></i>
                           <?php _e("API key", "imdb-connector"); ?>
                        </label>
                     </th>
                     <td>
                        <?php
                           $api_key = imdb_connector_get_setting("api_key");

                           if($api_key === IMDB_CONNECTOR_API_KEY) {
                              $api_key = IMDB_CONNECTOR_API_KEY;
                           }
                        ?>
                        <input type="hidden" id="default-api-key"
                           value="<?php echo IMDB_CONNECTOR_API_KEY; ?>">
                        <input id="api-key" name="api_key"
                           placeholder="<?php _e("Enter own API key...", "imdb-connector"); ?>"
                           value="<?php echo $api_key; ?>">
                        <span class="optional">
                               <a href="https://thaikolja.gitbooks.io/imdb-connector/content/usage/api-key.html#how-do-i-get-a-new-api-key" target="_blank" class="button">
                                   <?php _e("How to get your free API key", "imdb-connector"); ?>
                               </a>
                        </span>
                        <p class="description">
                           <?php _e("IMDb Connector requires an API key to work. A default key has already been implemented in this version, and IMDb Connector should work just fine. If it does not, please get your free private API key and enter it in the input field above.", "imdb-connector"); ?>
                        </p>
                     </td>
                  </tr>
                  <tr id="allow-caching-row">
                     <?php
                        $cache_path         = IMDB_CONNECTOR_CACHE_PATH;
                        $invalid_cache_path = "";

                        if(!@wp_mkdir_p($cache_path) && !is_dir($cache_path)) {
                           $invalid_cache_path = ' disabled="disabled"';
                        }
                     ?>
                     <th>
                        <label for="allow-caching-on">
                           <i class="fa fa-files-o"></i>
                           <?php _e("Caching", "imdb-connector"); ?>
                        </label>
                     </th>
                     <td>
                        <input type="radio" name="allow_caching" id="allow-caching-on"
                           class="first"
                           value="on"<?php imdb_connector_check_setting("allow_caching", "on");
                           echo $invalid_cache_path; ?> />
                        <label for="allow-caching-on">
                           <?php echo __("Movie details and posters", "imdb-connector"); ?>
                        </label>
                        <input type="radio" name="allow_caching"
                           id="allow-caching-only-movie-details"
                           value="on_no_poster"<?php imdb_connector_check_setting("allow_caching", "on_no_poster"); ?> />
                        <label for="allow-caching-only-movie-details">
                           <?php echo __("Only movie details", "imdb-connector"); ?>
                        </label>
                        <input type="radio" name="allow_caching" id="allow_caching_off"
                           value="off"<?php imdb_connector_check_setting("allow_caching", "off");
                           echo $invalid_cache_path; ?> />
                        <label for="allow_caching_off"><?php _e("Off", "imdb-connector"); ?></label>
                        <p id="delete-cache-container">
                           <?php
                              $cached_movies       = imdb_connector_get_cached_movies();
                              $cached_movies_count = count($cached_movies);
                              $value               = sprintf(__("Delete cached movies (%s)", "imdb-connector"), $cached_movies_count);

                              wp_nonce_field("imdb_connector_delete_cache", "delete_cache_nonce");
                           ?>

                           <button type="button" id="delete-cache"
                              class="button" <?php disabled($cached_movies_count, 0); ?>>
                              <i class="fa fa-trash"></i>
                              <span id="cached-movies-number">
                                 <?php echo $value; ?>
                              </span>
                           </button>

                           <img src="<?php echo IMDB_CONNECTOR_URL; ?>/images/loading.gif"
                              alt="<?php _e("Loading...", "imdb-connector"); ?>"
                              id="delete-cache-loading-icon"
                              hidden/>
                           <span class="message success" hidden>
                              <i class="fa fa-check"></i>
                              <?php _e("All cached movies have been deleted.", "imdb-connector"); ?>
                           </span>
                           <span class="message error" hidden>
                              <i class="fa fa-times"></i>
                              <?php
                                 echo sprintf(__("The cache could not be deleted. Please refer to the %s.", "imdb-connector"), "<a href=\"$external_plugin_url/documentation/#faq\" target=\"_blank\">FAQ</a>");
                              ?>
                           </span>
                        </p>

                        <p class="description"><?php _e("Allows IMDb Connector to cache movie details and posters locally for faster access (recommended).", "imdb-connector"); ?></p>
                        <?php if($invalid_cache_path) { ?>
                           <div id="invalid-directory">
                              <p class="message error">
                                 <?php
                                    _e("Caching has been disabled because the following directory does not exist<br />and could not be created:", "imdb-connector");
                                 ?>
                              </p>
                              <code><?php echo imdb_connector_get_cache_url(); ?></code>
                           </div>
                        <?php } ?>
                     </td>
                  </tr>
                  <tr>
                     <th>
                        <label for="cache-location-local">
                           <i class="fa fa-hdd-o"></i>
                           <?php _e("Cache location", "imdb-connector"); ?>
                        </label>
                     </th>
                     <td>
                        <input type="radio" name="cache_location" id="cache-location-local"
                           class="first"
                           value="local"<?php imdb_connector_check_setting("cache_location", "local"); ?> />
                        <label for="cache-location-local"><?php _e("As files on the server", "imdb-connector"); ?></label>
                        <input type="radio" name="cache_location" id="cache-location-database"
                           value="database"<?php imdb_connector_check_setting("cache_location", "database"); ?> />
                        <label for="cache-location-database"><?php _e("In MySQL database (recommended)", "imdb-connector"); ?></label>

                        <p class="description">
                           <?php
                              echo sprintf(__('Defines where IMDb Connector stores the cached information. Movie posters are always stored in the plugin\'s <a href="%s" target="_blank">cache directory</a>.', "imdb_conector"), imdb_connector_get_cache_url());
                           ?>
                        </p>
                     </td>
                  </tr>
                  <tr>
                     <th>
                        <label for="plot-type">
                           <i class="fa fa-book"></i>
                           <?php _e("Plot type", "imdb-connector"); ?>
                        </label>
                     </th>
                     <td>
                        <select name="plot_type" id="plot-type">
                           <?php
                              $options = array(
                                 array(
                                    "label" => __("Short plot", "imdb-connector"),
                                    "value" => "plot_short"
                                 ),
                                 array(
                                    "label" => __("Full plot", "imdb-connector"),
                                    "value" => "plot_full"
                                 )
                              );

                              foreach($options as $option) {
                                 $selected = "";
                                 if(imdb_connector_get_setting("plot_type") === $option["value"]) {
                                    $selected = " selected";
                                 }
                                 echo '<option value="' . $option["value"] . '" ' . $selected . ' >' . $option["label"] . '</option>';
                              }
                           ?>
                        </select>

                        <p class="description">
                           <?php _e("Determines whether the <code>plot</code> variable should store the short or the full plot provided.", "imdb-connector"); ?>
                        </p>
                     </td>
                  </tr>
                  <tr id="row-shortcode-generator">
                     <th>
                        <i class="fa fa-code"></i>
                        <?php _e("Shortcodes", "imdb-connector"); ?>
                     </th>
                     <td>
                        <?php
                           echo sprintf(__("To easily use %s in your posts, you can generate them here:", "imdb-connector"), '<a href="https://codex.wordpress.org/Shortcode" target="_blank">Shortcodes</a>');
                        ?>
                        <div class="shortcode-generator">
                           <table class="form-table">
                              <tr>
                                 <td>
                                    <input placeholder="<?php _e("Enter movie title or IMDb ID...", "imdb-connector"); ?>"
                                       class="widefat" id="shortcode-generator-title"/>
                                 </td>
                                 <td>
                                    <label for="shortcode-generator-title">
                                       <?php _e("Enter the movie title or IMDb ID of the movie.", "imdb-connector"); ?>
                                    </label>
                                 </td>
                              </tr>
                              <tr>
                                 <td>
                                    <?php
                                       $available_movie_details = array(
                                          "Title"             => "title",
                                          "IMDb ID"           => "imdbid",
                                          "Year"              => "year",
                                          "Released"          => "released",
                                          "Runtime (minutes)" => "runtime",
                                          "Runtime (hours)"   => "runtime-hours",
                                          "Genres"            => "genres",
                                          "Writers"           => "writers",
                                          "Directors"         => "directors",
                                          "Actors"            => "actors",
                                          "Languages"         => "languages",
                                          "Countries"         => "countries",
                                          "Rating"            => "rating",
                                          "Poster URL"        => "poster",
                                          "Poster image"      => "poster_image",
                                          "Awards"            => "awards",
                                          "Plot"              => "plot",
                                          "Film type"         => "type",
                                          "Metascore"         => "metascore",
                                          "IMDb.com rating"   => "imdbrating",
                                          "IMDb.com votes"    => "imdbvotes"
                                       );
                                    ?>
                                    <select id="shortcode-generator-detail" class="widefat">
                                       <option value="">
                                          -
                                          <?php _e("Select a movie detail", "imdb-connector"); ?>
                                          -
                                       </option>
                                       <?php
                                          foreach($available_movie_details as $movie_detail => $shortcode_detail) {
                                             ?>
                                             <option value="<?php echo $shortcode_detail; ?>"><?php echo $movie_detail; ?></option>
                                             <?php
                                          }
                                       ?>
                                    </select>
                                 </td>
                                 <td>
                                    <label for="shortcode-generator-detail">
                                       <?php _e("Movie detail you would like to display.", "imdb-connector"); ?>
                                    </label>
                                 </td>
                              </tr>
                              <tr>
                                 <td>
                                    <button type="button" class="button"
                                       id="generate-shortcode">
                                       <i class="fa fa-code"></i>
                                       <?php _e("Generate shortcode", "imdb-connector"); ?>
                                    </button>
                                 </td>
                              </tr>
                           </table>
                           <div class="shortcode-preview">
                              <h4>
                                 <?php _e("Copy the following shortcode and insert it in your post:", "imdb-connector"); ?>
                              </h4>
                              <p>
                                 <code>
                                    [imdb_movie_detail title="<span
                                       class="preview-title"></span>" detail="<span
                                       class="preview-detail"></span>"]
                                 </code>
                              </p>
                           </div>
                        </div>
                     </td>
                  </tr>
                  <tr id="row-auto-delete">
                     <th>
                        <label for="auto-delete">
                           <i class="fa fa-trash"></i>
                           <?php _e("Auto delete", "imdb-connector"); ?>
                        </label>
                     </th>
                     <td>
                        <label for="auto-delete"><?php echo sprintf(__("Deletes cache %s", "imdb-connector"), ""); ?></label>
                        <select name="auto_delete" id="auto-delete">
                           <?php
                              $options = array(
                                 array(
                                    "label" => __("never", "imdb-connector"),
                                    "value" => "off"
                                 ),
                                 array(
                                    "label" => sprintf(__("every %s hours", "imdb_connector"), 24),
                                    "value" => "24_hours"
                                 ),
                                 array(
                                    "label" => sprintf(__("every %s days", "imdb_connector"), 7),
                                    "value" => "7_days"
                                 ),
                                 array(
                                    "label" => sprintf(__("every %s days", "imdb_connector"), 30),
                                    "value" => "30_days"
                                 ),
                                 array(
                                    "label" => sprintf(__("every %s months", "imdb_connector"), 3),
                                    "value" => "3_months"
                                 ),
                                 array(
                                    "label" => sprintf(__("every %s months", "imdb_connector"), 6),
                                    "value" => "6_months"
                                 )
                              );

                              foreach($options as $option) {
                                 $selected = "";
                                 if(imdb_connector_get_setting("auto_delete") === $option["value"]) {
                                    $selected = " selected";
                                 }
                                 echo '<option value="' . $option["value"] . '" ' . $selected . ' >' . $option["label"] . '</option>';
                              }
                           ?>
                        </select>

                        <p class="description"><?php _e("Automatically deletes all cached files (database and files) to keep the movie data up to date.", "imdb-connector"); ?></p>
                     </td>
                  </tr>
                  <tr id="row-deactivation-actions">
                     <th>
                        <label for="deactivation-actions">
                           <i class="fa fa-power-off"></i>
                           <?php _e("Deactivation actions", "imdb-connector"); ?>
                        </label>
                     </th>
                     <td>
                        <?php
                           $checkboxes = array(
                              array(
                                 "label" => __("Delete MySQL cache", "imdb-connector"),
                                 "value" => "database"
                              ),
                              array(
                                 "label" => __("Delete cached movie detail files", "imdb-connector"),
                                 "value" => "movie_details"
                              ),
                              array(
                                 "label" => __("Delete cached poster files", "imdb-connector"),
                                 "value" => "posters"
                              ),
                              array(
                                 "label" => __("Delete plugin settings", "imdb-connector"),
                                 "value" => "settings"
                              )
                           );
                           foreach($checkboxes as $checkbox) {
                              $checked = "";
                              if(in_array($checkbox["value"], imdb_connector_get_setting("deactivation_actions"), false)) {
                                 $checked = ' checked="checked"';
                              }
                              echo '<p><input type="checkbox" name="deactivation_actions[]" id="deactivation-action-' . $checkbox["value"] . '" value="' . $checkbox["value"] . '" ' . $checked . ' /><label for="deactivation-action-' . $checkbox["value"] . '">' . $checkbox["label"] . '<label></p>';
                           }
                        ?>
                        <p class="description"><?php _e("Actions being executed when you deactivate IMDb Connector.", "imdb-connector"); ?></p>
                     </td>
                  </tr>
                  <tr id="row-log-errors">
                     <th>
                        <label for="debug-mode">
                           <i class="fa fa-bug"></i>
                           <?php _e("Log errors", "imdb_connector"); ?>
                        </label>
                     </th>
                     <td>
                        <?php
                           $debug_mode_setting_value = imdb_connector_get_setting("debug_mode");
                           $debug_log_url            = WP_CONTENT_URL . "/debug.log";
                        ?>
                        <input type="hidden" name="debug_mode" value="off">
                        <input type="checkbox" name="debug_mode" id="debug-mode"
                           value="on" <?php checked("on", $debug_mode_setting_value); ?>>
                        <label for="debug-mode">
                           <?php echo sprintf(__("Report errors in %s", "imdb_connector"), "<a href=\"$debug_log_url\" target=\"_blank\">debug.log</a>"); ?>
                        </label>
                        <p class="description">
                           <?php
                              echo sprintf(__("If activated, IMDb Connector will add all errors it might encounter to the debug file located under <code>/wp-content/debug.log</code>. You must have <code>WP_DEBUG</code> and <code>WP_DEBUG_LOG</code> constants activated for this. <a href=\"%s\" target=\"_blank\">Click here to learn how</a>.", "imdb_connector"), "https://codex.wordpress.org/Debugging_in_WordPress");
                           ?>
                        </p>
                     </td>
                  </tr>
               </tbody>
            </table>
            <div class="submit-area">
               <?php wp_nonce_field("save_settings", "save_settings_nonce"); ?>
               <input type="hidden" name="saved" value="true"/>
               <button class="button-primary">
                  <i class="fa fa-floppy-o"></i>
                  <?php _e("Save Changes", "imdb-connector"); ?>
               </button>

               <a href="<?php echo wp_nonce_url(get_admin_url() . "options-general.php?page=imdb-connector&action=reset_settings", "reset_settings", "nonce"); ?>"
                  class="button" id="reset-button">
                  <i class="fa fa-trash"></i>
                  <?php _e("Reset to Default Settings", "imdb-connector"); ?>
               </a>
            </div>
            <p>
               <small>
                  <i class="fa fa-bug"></i>
                  <?php _e('Found an error? Help to make IMDb Connector better by <a href="https://wordpress.org/support/plugin/imdb-connector#new-post" target="_blank">quickly reporting the bug</a>.', "imdb-connector"); ?>
               </small>
            </p>
         </form>
         <span id="reset-settings-label"
            hidden><?php _e("Do you really want to reset all settings wit the default values?", "imdb-connector"); ?></span>
      </div>
      <?php
   }