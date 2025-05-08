(function($) {
    'use strict';
  
    window.USERSPN_Popups = {
      activePopups: [],
      
      open: function(popup, options = {}) {
        var popupElement = typeof popup === 'string' ? $('#' + popup) : popup;
        
        if (!popupElement.length) {
          return;
        }
  
        if (typeof options.beforeShow === 'function') {
          options.beforeShow();
        }
  
        // Create unique ID if not exists
        if (!popupElement.attr('id')) {
          popupElement.attr('id', 'userspn-popup-' + Date.now());
        }
  
        // Add to active popups stack
        this.activePopups.push(popupElement);
  
        // Update z-index based on stack position
        this.updateZIndex();
  
        // Show overlay if this is the first popup
        if (this.activePopups.length === 1) {
          $('.userspn-popup-overlay').removeClass('userspn-display-none-soft').fadeIn('fast');
        }
  
        // Show the popup
        popupElement.addClass('userspn-popup-active').fadeIn('fast');
        document.body.classList.add('userspn-popup-open');
  
        // Add close button if not present
        if (!popupElement.find('.userspn-popup-close').length) {
          var closeButton = $('<button class="userspn-popup-close-wrapper"><i class="material-icons-outlined">close</i></button>');
          closeButton.on('click', function() {
            USERSPN_Popups.close(popupElement);
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
  
      close: function(popupElement = null) {
        if (popupElement) {
          // Close specific popup
          popupElement.fadeOut('fast', () => {
            popupElement.removeClass('userspn-popup-active');
            this.activePopups = this.activePopups.filter(p => p.attr('id') !== popupElement.attr('id'));
            
            // Call afterClose callback if exists
            const afterClose = popupElement.data('afterClose');
            if (typeof afterClose === 'function') {
              afterClose();
              popupElement.removeData('afterClose');
            }
  
            // Update z-index for remaining popups
            this.updateZIndex();
  
            // Hide overlay if no more popups
            if (this.activePopups.length === 0) {
              $('.userspn-popup-overlay').fadeOut('fast', function() {
                $(this).addClass('userspn-display-none-soft');
              });
              document.body.classList.remove('userspn-popup-open');
            }
          });
        } else {
          // Close all popups
          this.activePopups.forEach(popup => {
            popup.fadeOut('fast');
            popup.removeClass('userspn-popup-active');
            
            const afterClose = popup.data('afterClose');
            if (typeof afterClose === 'function') {
              afterClose();
              popup.removeData('afterClose');
            }
          });
  
          this.activePopups = [];
          $('.userspn-popup-overlay').fadeOut('fast', function() {
            $(this).addClass('userspn-display-none-soft');
          });
          document.body.classList.remove('userspn-popup-open');
        }
      },
  
      updateZIndex: function() {
        const baseZIndex = 9999;
        this.activePopups.forEach((popup, index) => {
          popup.css('z-index', baseZIndex + index);
        });
      }
    };
  
    // Initialize popup functionality
    $(document).ready(function() {
      // Close popup when clicking outside popup content
      $(document).on('click', '.userspn-popup', function(e) {
        if ($(e.target).hasClass('userspn-popup')) {
          USERSPN_Popups.close($(this));
        }
      });
      
      // Close popup when clicking overlay
      $(document).on('click', '.userspn-popup-overlay', function(e) {
        USERSPN_Popups.close();
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
        USERSPN_Popups.close($(this).closest('.userspn-popup'));
      });
    });
  })(jQuery); 