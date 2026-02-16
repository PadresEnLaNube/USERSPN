(function($) {
	'use strict';

  function userspn_validate_required_fields() {
    if (typeof USERSPN_Popups !== 'undefined' && $('#userspn-profile-popup').length) {
      USERSPN_Popups.open($('#userspn-profile-popup'));

      if (!$('.userspn-profile-wrapper .user-fields-required').length) {
        $('.userspn-profile-wrapper').prepend('<div class="userspn-alert userspn-alert-warning user-fields-required">' + userspn_i18n.complete_required_fields + '</div>');
      }

      if (typeof userspn_get_main_message === 'function') {
        userspn_get_main_message(userspn_i18n.complete_required_fields);
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
