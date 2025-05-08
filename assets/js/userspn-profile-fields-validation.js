(function($) {
	'use strict';

  $(document).ready(function() {
    $(window).on('load', function(e) {
      USERSPN_Popups.open($('#userspn-profile-popup'));

      if (!$('.userspn-profile-wrapper .user-fields-required').length) {
        $('.userspn-profile-wrapper').prepend('<div class="userspn-alert userspn-alert-warning user-fields-required">' + userspn_i18n.complete_required_fields + '</div>');
      }

      userspn_get_main_message(userspn_i18n.complete_required_fields);
    });
  });
})(jQuery);