(function($) {
	'use strict';

  $(document).ready(function() {
    $(window).on('load', function(e) {
      if (userspn_get.userspn_notice) {
        USERSPN_Popups.open($('#userspn-popup-notice'));
      };

      if (userspn_get.userspn_login) {
        /* https://domain.com/?userspn_login=register */
        
        userspn_tab = userspn_get.userspn_login;
        USERSPN_Popups.open($('#userspn-profile-popup'));

        if (typeof userspn_tab !== 'undefined') {
          $('.userspn-tab-links[data-userspn-id="userspn-tab-' + userspn_tab + '"]').click();
          $('#userspn-' + userspn_tab + ' input#userspn_email').focus();
        }else{
          $('.userspn-tab-links[data-userspn-id="userspn-tab-login"]').click();
          $('#userspn-login input#user_login').focus();
        }
      };
    });
  });
})(jQuery);