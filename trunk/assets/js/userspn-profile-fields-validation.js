(function($) {
	'use strict';

  function userspn_validate_required_fields() {
    if (typeof USERSPN_Popups !== 'undefined' && $('#userspn-profile-popup').length) {
      USERSPN_Popups.open($('#userspn-profile-popup'));

      // Build message with missing field labels
      var message = userspn_i18n.complete_required_fields;
      var detailedMessage = message;

      // Check if debug data is available with pending fields info
      if (typeof userspn_validation_debug !== 'undefined' && userspn_validation_debug.pending_fields && userspn_validation_debug.pending_fields.length > 0) {
        // Create a detailed message with the list of missing fields
        detailedMessage = '<strong>' + userspn_i18n.complete_required_fields + '</strong><br>';
        detailedMessage += '<ul style="margin: 10px 0 0 20px; text-align: left;">';
        userspn_validation_debug.pending_fields.forEach(function(fieldLabel) {
          // Extract just the label part (before the parentheses with field ID)
          var cleanLabel = fieldLabel.replace(/\s*\([^)]*\)$/, '');
          detailedMessage += '<li>' + cleanLabel + '</li>';
        });
        detailedMessage += '</ul>';
      }

      // Remove existing alert if present
      $('.userspn-profile-wrapper .user-fields-required').remove();

      // Add alert to the popup (inside profile wrapper, at the top)
      $('.userspn-profile-wrapper').prepend('<div class="userspn-alert userspn-alert-warning user-fields-required">' + detailedMessage + '</div>');

      // Also show in main message system if available
      if (typeof userspn_get_main_message === 'function') {
        // For the main message, use plain text without HTML
        var plainMessage = message;
        if (typeof userspn_validation_debug !== 'undefined' && userspn_validation_debug.pending_fields && userspn_validation_debug.pending_fields.length > 0) {
          plainMessage += ': ' + userspn_validation_debug.pending_fields.map(function(f) {
            return f.replace(/\s*\([^)]*\)$/, '');
          }).join(', ');
        }
        userspn_get_main_message(plainMessage);
      }
    } else {
      setTimeout(userspn_validate_required_fields, 500);
    }
  }

  if (document.readyState === 'complete') {
    setTimeout(userspn_validate_required_fields, 300);
  } else {
    $(window).on('load', function() {
      setTimeout(userspn_validate_required_fields, 300);
    });
  }
})(jQuery);
