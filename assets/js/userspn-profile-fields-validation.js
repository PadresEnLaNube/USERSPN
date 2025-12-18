(function($) {
	'use strict';

  $(document).ready(function() {
    $(window).on('load', function(e) {
      USERSPN_Popups.open($('#userspn-profile-popup'));

      if (!$('.userspn-profile-wrapper .user-fields-required').length) {
        $('.userspn-profile-wrapper').prepend('<div class="userspn-alert userspn-alert-warning user-fields-required">' + userspn_i18n.complete_required_fields + '</div>');
      }

      userspn_get_main_message(userspn_i18n.complete_required_fields);

      // Debug: mostrar en consola qué campos obligatorios faltan por completar.
      // La lista viene directamente desde PHP en "userspn_profile_required_pending".
      try {
        if (typeof userspn_profile_required_pending !== 'undefined' && userspn_profile_required_pending.length) {
          console.log('USERSPN – Campos obligatorios pendientes:', userspn_profile_required_pending);
        } else {
          console.log('USERSPN – userspn_profile_required_pending está vacío o indefinido (no se pudo listar campos pendientes).');
        }
      } catch (err) {
        if (typeof console !== 'undefined' && console.error) {
          console.error('USERSPN – Error mientras se listaban los campos obligatorios pendientes:', err);
        }
      }
    });
  });
})(jQuery);