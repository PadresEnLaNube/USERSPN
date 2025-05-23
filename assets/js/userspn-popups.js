(function($) {
  'use strict';

  window.USERSPN_Popups = {
    open: function(popup, options = {}) {
      var popupElement = typeof popup === 'string' ? $('#' + popup) : popup;
      
      if (!popupElement.length) {
        return;
      }

      if (typeof options.beforeShow === 'function') {
        options.beforeShow();
      }

      // Show overlay
      $('.userspn-popup-overlay').removeClass('userspn-display-none-soft').fadeIn('fast');

      // Show the popup
      popupElement.addClass('userspn-popup-active').fadeIn('fast');
      document.body.classList.add('userspn-popup-open');

      // Add close button if not present
      if (!popupElement.find('.userspn-popup-close').length) {
        var closeButton = $('<button class="userspn-popup-close-wrapper"><i class="material-icons-outlined">close</i></button>');
        closeButton.on('click', function() {
          USERSPN_Popups.close();
        });
        popupElement.find('.userspn-popup-content').append(closeButton);
      }

      // Store and call callbacks if provided
      if (options.beforeShow) {
        popupElement.data('beforeShow', options.beforeShow);
      }
      if (options.afterClose) {
        popupElement.data('afterClose', options.afterClose);
      }
    },

    close: function() {
      // Hide all popups
      $('.userspn-popup').fadeOut('fast');

      // Hide overlay
      $('.userspn-popup-overlay').fadeOut('fast', function() {
        $(this).addClass('userspn-display-none-soft');
      });

      // Call afterClose callback if exists
      $('.userspn-popup').each(function() {
        const afterClose = $(this).data('afterClose');
        if (typeof afterClose === 'function') {
          afterClose();
          $(this).removeData('afterClose');
        }
      });

      document.body.classList.remove('userspn-popup-open');
    }
  };

  // Initialize popup functionality
  $(document).ready(function() {
    // Close popup when clicking overlay
    $(document).on('click', '.userspn-popup-overlay', function(e) {
      // Only close if the click was directly on the overlay
      if (e.target === this) {
        USERSPN_Popups.close();
      }
    });

    // Prevent clicks inside popup from bubbling up to the overlay
    $(document).on('click', '.userspn-popup', function(e) {
      e.stopPropagation();
    });

    // Close popup when pressing ESC key
    $(document).on('keyup', function(e) {
      if (e.keyCode === 27) { // ESC key
        USERSPN_Popups.close();
      }
    });

    // Close popup when clicking close button
    $(document).on('click', '.userspn-popup-close', function(e) {
      e.preventDefault();
      USERSPN_Popups.close();
    });
  });
})(jQuery); 