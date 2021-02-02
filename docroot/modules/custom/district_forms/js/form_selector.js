(function ($, Drupal) {

  /**
   * Append url param when form is changed.
   */
  Drupal.behaviors.DistrictFormSelector = {
    attach: function (context, settings) {
      var $ctx = $('.js-form-selector', context);

      $ctx.once('form-selector').each(function () {
        $(this).on('change', function (e) {
          $(this).attr('disabled', 'disabled').after('Loading');
          // Remove hash from url if exists.
          var url = document.location.href.replace(location.hash , "" );
          // This is not foolproof, it removes all other url params first.
          url = url.indexOf('?') !== -1 ? url.split('?')[0] : url;
          // Redirect with url param added.
          document.location.href = url + '?selected_form=' + $(this).val() + '#form-selector';
        });
      });
    },

  };

}(jQuery, Drupal));
