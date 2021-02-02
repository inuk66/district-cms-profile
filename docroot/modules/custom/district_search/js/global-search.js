/**
 * @file
 * Contains district_search global search functionality.
 */
(function ($, Drupal) {

  /**
   * District Base global search.
   */
  Drupal.behaviors.districtSearchGlobalSearch = {
    attach: function (context, settings) {
      var $block = $('.js-global-search', context);
      var $button = $block.find('.global-search__toggle');
      var $input = $block.find('.global-search__input');

      // Toggle the search form visibility on button click.
      $button.click(function () {
        // Toggle the active state and update various element attributes.
        $('body').toggleClass('global-search--active');
        // Bring focus to the search input when the form becomes visible.
        if ($('body').hasClass('global-search--active')) {
          $input.focus();
        }
      })

      // Hide search container when clicked outside of container.
      $('html').mouseup(function (e) {
        const container = $('.global-search__toggle');

        // if the target of the click isn't the container nor a descendant of the container.
        if (!container.is(e.target) && container.has(e.target).length === 0) {
          // Remove body class.
          $('body').removeClass('global-search--active');
        }
      });

      // Hide search form when escape key is pressed.
      $input.keyup(function (e) {
        if (e.keyCode === 27) {
          // Remove body class.
          $('body').removeClass('global-search--active');
        }
      });
    }
  };

}(jQuery, Drupal));
