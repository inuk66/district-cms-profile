(function ($, Drupal) {

  /**
   * Add content to Spydus blocks via JSON lookup.
   *
   * See SpydusRemoteContentBlock
   *
   * This also deals with issues in Spydus API not indicating when images
   * are missing or broken.
   */
  Drupal.behaviors.spydusRemoteContentBlock = {
    attach: function (context, settings) {
      var $ctx = $('.spydus-remote-content', context);
      var self = Drupal.behaviors.spydusRemoteContentBlock;

      // For each Spydus block.
      $ctx.once('spydus-content').each(function () {
        var $el = $(this);
        var request = $.getJSON($el.data('src'));
        var limit = parseInt($el.data('limit'));

        // On successful data get.
        request.done(function (data) {
          // Remove loader.
          $el.empty();
          // Test image is valid, if so add item.
          // TODO: Consider adding items without image (needs styling).
          $(data.items).each(function (idx, item) {
            // This is annoying, dead images are returned as one 1px width
            // images, so we must load them then check their size before adding.
            var img = new Image();
            img.src = item.media;
            img.onload = function () {
              if (this.width > 5) {
                self.addItem($el, item, limit);
              }
            }
          });

          // Add a More items button.
          $('<a>', {href: data.url})
            .addClass('button button--secondary')
            .html('View more')
            .insertBefore($el);
        });

        // Remove block on error.
        request.fail(function () {
          $ctx.closest('.block').remove();
        });
      });
    },

    /**
     * Add an item to the container.
     *
     * @param $ctx
     *   Container.
     * @param item
     *   Item to add.
     * @param limit
     *   Limit of items to add.
     */
    addItem($el, item, limit) {
      // Respect limit of items.
      if ($('> div', $el).length >= limit) {
        return;
      }

      // Add Item.
      var $img = $('<img>', {src: item.media, alt: item.title}),
        $link = $('<a>', {href: item.url, title: item.title})
          .addClass('js-img-to-bg')
          .css('background-image', 'url(' + item.media + ')')
          .append($img),
        $item = $('<div>').addClass('item').append($link);
      $el.append($item)
    }
  };

}(jQuery, Drupal));
