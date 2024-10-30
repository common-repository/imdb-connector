/*
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

jQuery(document).ready(function () {
   /** Defining global variables */
   var updatedSelector = jQuery(".updated");
   var deleteCache     = jQuery("#delete-cache");

   /** Only apply scripts if we're on the IMDb Connector settings page */
   if (jQuery("#imdb-connector-settings").length < 1) {
      return false;
   }

   jQuery("#reset-button").click(function () {
      if (!confirm(jQuery("#reset-settings-label").text())) {
         return false;
      }
   });

   /**
    * Fade out updated message after 5 seconds.
    */
   if (updatedSelector.length >= 1) {
      setTimeout(
         function () {
            updatedSelector.fadeOut("slow");
         },
         5000
      );
   }

   /**
    * Delete cache function.
    */
   deleteCache.click(function () {
      var deleteCacheContainer    = jQuery("#delete-cache-container");
      var deleteCacheButton       = deleteCacheContainer.find("#delete-cache");
      var deleteCacheButtonNumber = deleteCacheButton.find("#cached-movies-number");
      var deleteCacheLoadingIcon  = deleteCacheContainer.find("#delete-cache-loading-icon");
      var deleteCacheMessage      = deleteCacheContainer.find(".message");
      var deleteCacheNonce        = deleteCacheContainer.find("#delete_cache_nonce").attr("value");

      deleteCacheMessage.hide();
      deleteCacheLoadingIcon.show();
      deleteCacheButton.attr(
         "disabled",
         "true"
      );

      jQuery.ajax({
                     url: ajaxurl, method: "post", data: {
            action: "imdb_connector_delete_cache", nonce: deleteCacheNonce
         }, success:      function (response) {
            if (response.success === true) {
               deleteCacheButtonNumber.text(function () {
                  return deleteCacheButtonNumber.text().replace(
                     /\d+/,
                     response.data.cached_movies
                  );
               });

               deleteCacheLoadingIcon.hide();
               deleteCacheContainer.find(".message.success").fadeIn();
               deleteCacheButton.removeAttr("disabled");
            } else {
               deleteCacheLoadingIcon.hide();
               deleteCacheContainer.find(".message.error").fadeIn();
               deleteCacheButton.removeAttr("disabled");
            }

            setTimeout(
               function () {
                  deleteCacheMessage.fadeOut();
               },
               5000
            );

            console.log(response);
         }
                  });
   });

   /**
    * Generates the shortcode according to the entered movie title/IMDb ID
    * and the movie detail that is supposed to be displayed.
    */
   jQuery("#generate-shortcode").click(function () {
      var container = jQuery("#row-shortcode-generator");
      var title     = container.find("#shortcode-generator-title").attr("value");
      var detail    = container.find("#shortcode-generator-detail option:selected").attr("value");

      container.find(".preview-title").text(title);
      container.find(".preview-detail").text(detail);
   });

   jQuery("#reset-default-api-key").click(function () {
      var default_api_key       = jQuery("#default-api-key").attr("value");
      var current_api_key_input = jQuery("#api-key");

      current_api_key_input.attr(
         "value",
         default_api_key
      );
   });
});