(function ($, Drupal) {

  Drupal.behaviors.districtMenuMainMenu = {

    /**
     * Navigation help text. Used for screen reader announcements and dialog.
     */
    helpText: function () {
      var text = 'Use the keyboard arrow keys to easily traverse the main navigation.';
      text += ' Press the escape key at any time to jump to the top level parent link.';
      return text;
    },

    /**
     * Attach all functionality here.
     */
    attach: function (context, settings) {
      var behavior = this;
      $('.js-menu-extras-main', context).once('district-menu-main-menu').each(function () {
        behavior.initNavigationHelp(this);
        behavior.initNavigationKeys(this)
      });
    },

    /**
     * Map key press to navigation behaviors.
     */
    initNavigationKeys: function (context) {
      var behavior = this;
      $('.menu-level-0 > .menu-item', context).keydown(function (e) {
        var $current = $(this);
        var $target = $(e.target);

        switch (e.keyCode) {
          case 37: // left
            e.preventDefault();
            behavior.navigateLeft($current, $target);
            break;

          case 38: // up
            e.preventDefault();
            behavior.navigateUp($current, $target);
            break;

          case 39: // right
            e.preventDefault();
            behavior.navigateRight($current, $target);
            break;

          case 40: // down
            e.preventDefault();
            behavior.navigateDown($current, $target);
            break;

          case 27: // escape
            e.preventDefault();
            behavior.navigateEscape($current, $target);
            break;
        }
      });
    },

    /**
     * Generate navigation help elements and functionality.
     */
    initNavigationHelp: function (context) {
      var behavior = this;
      // Create hidden dialog elements.
      var $hidden = $('<div class="hidden"></div>').appendTo('body');
      var $helpDialogEl = $('<div id="nav-help-dialog"</div>')
        .text(this.helpText()).appendTo($hidden);
      // Create help menu item. Only accessible via keyboard.
      var $help = $('<li class="menu-item menu-item--nav-help"><a href="#" role="button" tabindex="0" aria-label="Keyboard navigation help" class="nav-help sr-only">?</a></li>');
      $help.prependTo($('.menu-level-0', context));
      $help = $('.menu-level-0', context).find('.nav-help');
      // Show help link when focused.
      $help.focus(function () {
        $(this).removeClass('sr-only');
      });
      // Hide help link when not focused.
      $help.blur(function () {
        $(this).addClass('sr-only');
      });
      // Create dialog object.
      var helpDialog = Drupal.dialog($helpDialogEl, {
        title: 'Main navigation help',
        modal: true,
        close: function ( event, ui ) {
          $help.focus()
        }
      })
      // Trigger dialog when link is clicked/keyboard enter button.
      $help.click(function () {
        if (!helpDialog.open) {
          Drupal.announce(behavior.helpText())
          helpDialog.showModal();
        }
        else {
          helpDialog.close();
        }
      })
      // Hide dialog when navigating away from help link.
      $help.keydown('keydown', function (e) {
        if (e.keyCode == 13) {
          return;
        }
       if (helpDialog.open) {
          helpDialog.close();
        }
      });
    },

    /**
     * Check if target link is a top level menu item.
     */
    isTopLevelLink: function ($current, $target) {
      return $target.is($current.find('> a'));
    },

    /**
     * Check if target link is a second level menu item.
     */
    isSecondLevelLink: function ($current, $target) {
      return $target.is($current.find('.menu-level-1 > .menu-item > a'));
    },

    /**
     * Check if target link is a third level menu item.
     */
    isThirdLevelLink: function ($current, $target) {
      return $target.is($current.find('.menu-level-2 > .menu-item > a'));
    },

    /**
     * Keyboard arrow left behavior.
     */
    navigateLeft: function ($current, $target) {
      if (this.isTopLevelLink($current, $target)) {
        $current.prev('.menu-item').find(' > a').focus();
      }
      else if (this.isSecondLevelLink($current, $target)) {
        $target.parent('.menu-item').prev('.menu-item').find(' > a').focus();
      }
      else if (this.isThirdLevelLink($current, $target)) {
        $target.parents('.menu-level-1 > .menu-item')
          .prev('.menu-item').find(' > a').focus();
      }
    },

    /**
     * Keyboard arrow right behavior.
     */
    navigateRight: function ($current, $target) {
      if (this.isTopLevelLink($current, $target)) {
        // If last top level menu item. try to find the next focusable element.
        if ($current.is(':last-of-type')) {
          $current.parents('.menu--main').next().find('a, button, input').first().focus()
        }
        else {
          $current.next('.menu-item').find(' > a').focus();
        }
      }
      else if (this.isSecondLevelLink($current, $target)) {
        $target.parent('.menu-item').next('.menu-item').find(' > a').focus();
      }
      else if (this.isThirdLevelLink($current, $target)) {
        $target.parents('.menu-level-1 > .menu-item')
          .next('.menu-item').find(' > a').focus();
      }
    },

    /**
     * Keyboard arrow up behavior.
     */
    navigateUp: function ($current, $target) {
      if (this.isSecondLevelLink($current, $target)) {
        $current.find('> a').focus();
      }
      else if (this.isThirdLevelLink($current, $target)) {
        $target.parents('.menu-level-1 > .menu-item')
          .find(' > a').focus();
      }
    },

    /**
     * Keyboard arrow down behavior.
     */
    navigateDown: function ($current, $target) {
      if (this.isTopLevelLink($current, $target)) {
        $current.find('.menu-level-1 a').first().focus();
      }
      else if (this.isSecondLevelLink($current, $target)) {
        $target.next().find('.menu-level-2 a').first().focus();
      }
      else {
        $target.parent('.menu-item').next('.menu-item').find(' > a').focus();
      }
    },

    /**
     * Keyboard escape key behavior.
     */
    navigateEscape: function ($current, $target) {
      $current.find('> a').focus();
    }
  };

}(jQuery, Drupal));
