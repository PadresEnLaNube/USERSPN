(function($) {
	'use strict';

  $(document).ready(function() {
    // Load reCAPTCHA if enabled
    if (typeof userspn_security !== 'undefined' && userspn_security.recaptcha_enabled && userspn_security.recaptcha_site_key) {
      var script = document.createElement('script');
      script.src = 'https://www.google.com/recaptcha/api.js?render=' + userspn_security.recaptcha_site_key;
      script.async = true;
      script.defer = true;
      document.head.appendChild(script);
    }

    $(document).on('submit', '#userspn-user-register-fields', function(e) {
      var userspn_form = $(this);
      var userspn_btn = userspn_form.find('input[type="submit"]');
      userspn_btn.addClass('userspn-link-disabled').siblings('.userspn-waiting').removeClass('userspn-display-none-soft');

      var ajax_url = userspn_ajax.ajax_url;
      var data = {
        action: 'userspn_ajax_nopriv',
        userspn_ajax_nopriv_type: 'userspn_profile_create',
        userspn_ajax_nopriv_nonce: userspn_ajax.userspn_ajax_nonce,
        ajax_keys: [],
      };

      // Add honeypot field if enabled
      if (typeof userspn_security !== 'undefined' && userspn_security.honeypot_enabled) {
        data['userspn_honeypot_field'] = '';
      }

      if (!(typeof window['userspn_window_vars'] !== 'undefined')) {
        window['userspn_window_vars'] = [];
      }

      $('#userspn-user-register-fields input:not([type="submit"]), #userspn-user-register-fields select, #userspn-user-register-fields textarea').each(function(index, element) {
        
        if ($(this).attr('multiple') && $(this).parents('.userspn-html-multi-group').length) {
          if (!(typeof window['userspn_window_vars']['form_field_' + element.name] !== 'undefined')) {
            window['userspn_window_vars']['form_field_' + element.name] = [];
          }

          window['userspn_window_vars']['form_field_' + element.name].push($(element).val());

          data[element.name] = window['userspn_window_vars']['form_field_' + element.name];
        }else{
          if ($(this).is(':checkbox')) {
            if ($(this).is(':checked')) {
              data[element.name] = $(element).val();
            }else{
              data[element.name] = '';
            }
          }else if ($(this).is(':radio')) {
            if ($(this).is(':checked')) {
              data[element.name] = $(element).val();
            }
          }else{
            // Only set the value if it's not already set or if the current value is not empty
            if (!data.hasOwnProperty(element.name) || data[element.name] === '' || data[element.name] === null) {
              data[element.name] = $(element).val();
            }
          }
        }

        data.ajax_keys.push({
          id: element.name,
          node: element.nodeName,
          type: element.type,
        });
      });

      // Handle reCAPTCHA if enabled
      if (typeof userspn_security !== 'undefined' && userspn_security.recaptcha_enabled && userspn_security.recaptcha_site_key && typeof grecaptcha !== 'undefined') {
        grecaptcha.ready(function() {
          grecaptcha.execute(userspn_security.recaptcha_site_key, {action: 'register'}).then(function(token) {
            data['g-recaptcha-response'] = token;
            submitForm();
          });
        });
      } else {
        submitForm();
      }

      function submitForm() {
        $.post(ajax_url, data, function(response) {
          console.log('data');console.log(data);console.log('response');console.log(response);
          if (response == 'userspn_profile_create_error') {
            userspn_get_main_message(userspn_i18n.an_error_has_occurred);
          }else if (response == 'userspn_profile_create_existing') {
            userspn_get_main_message(userspn_i18n.user_existing);
            $('.userspn-tab-links[data-userspn-id="userspn-tab-login"]').click();

            if (!$('.userspn-profile-wrapper .user-existing').length) {
              $('.userspn-profile-wrapper').prepend('<div class="userspn-alert userspn-alert-warning user-existing">' + userspn_i18n.user_existing + '</div>');
            }

            $('#userspn-login input#user_login').focus();
          }else if (response == 'userspn_profile_create_security_error') {
            userspn_get_main_message(userspn_i18n.security_error || 'Security validation failed. Please try again.');
          }else {
            $('.userspn-tab-links[data-userspn-id="userspn-tab-login"]').click();

            setTimeout(function() {
              userspn_get_main_message(userspn_i18n.user_created);

              if (!$('.userspn-profile-wrapper .user-created').length) {
                 if ($('.userspn-profile-wrapper .user-unlogged').length) {
                  $('.userspn-profile-wrapper .user-unlogged').fadeOut('slow');
                 }

                $('.userspn-profile-wrapper').prepend('<div class="userspn-alert userspn-alert-warning user-created">' + userspn_i18n.user_created + '</div>');
              }

              $('#userspn-login input#user_login').focus();
            }, 1000);
          }

          userspn_btn.removeClass('userspn-link-disabled').siblings('.userspn-waiting').fadeOut('fast');
        });
      }

      delete window['userspn_window_vars'];
      return false;
    });
  });
})(jQuery);