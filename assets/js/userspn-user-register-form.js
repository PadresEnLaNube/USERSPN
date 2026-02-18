(function($) {
	'use strict';

  // Promise-based reCAPTCHA loader (shared, deduplicates script tags)
  var recaptchaLoadPromise = null;

  function loadRecaptchaScript() {
    if (recaptchaLoadPromise) return recaptchaLoadPromise;

    // Already loaded by another script (e.g. userspn-ajax.js)
    if (typeof grecaptcha !== 'undefined') {
      recaptchaLoadPromise = Promise.resolve();
      return recaptchaLoadPromise;
    }

    recaptchaLoadPromise = new Promise(function(resolve, reject) {
      // Check if the script tag already exists
      var existing = document.querySelector('script[src*="recaptcha/api.js"]');
      if (existing) {
        // Script tag exists but hasn't executed yet - poll for it
        var polls = 0;
        var interval = setInterval(function() {
          if (typeof grecaptcha !== 'undefined') {
            clearInterval(interval);
            resolve();
          } else if (polls++ > 50) { // 5 seconds
            clearInterval(interval);
            reject(new Error('reCAPTCHA script timeout'));
          }
        }, 100);
        return;
      }

      var script = document.createElement('script');
      script.src = 'https://www.google.com/recaptcha/api.js?render=' + userspn_security.recaptcha_site_key;
      script.onload = function() { resolve(); };
      script.onerror = function() { reject(new Error('reCAPTCHA script failed to load')); };
      document.head.appendChild(script);
    });

    return recaptchaLoadPromise;
  }

  $(document).ready(function() {
    var recaptchaEnabled = (typeof userspn_security !== 'undefined' && userspn_security.recaptcha_enabled && userspn_security.recaptcha_site_key);

    // Pre-load reCAPTCHA script if registration form is present
    if (recaptchaEnabled && $('#userspn-user-register-fields').length > 0) {
      loadRecaptchaScript();
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

      // Get reCAPTCHA token then submit
      function getRecaptchaTokenAndSubmit() {
        if (!recaptchaEnabled) {
          submitForm();
          return;
        }

        loadRecaptchaScript().then(function() {
          // Wait for grecaptcha.ready
          if (typeof grecaptcha !== 'undefined') {
            grecaptcha.ready(function() {
              grecaptcha.execute(userspn_security.recaptcha_site_key, {action: 'register'}).then(function(token) {
                data['g-recaptcha-response'] = token;
                submitForm();
              }).catch(function(err) {
                console.error('reCAPTCHA execute error:', err);
                // Submit without token - server will log warning but allow registration
                submitForm();
              });
            });
          } else {
            console.error('reCAPTCHA: grecaptcha undefined after script load');
            submitForm();
          }
        }).catch(function(err) {
          console.error('reCAPTCHA load error:', err);
          // Submit without token - server will log warning but allow registration
          submitForm();
        });
      }

      function submitForm() {
        $.post(ajax_url, data, function(response) {
          console.log('data', data, 'response', response);

          // Try to parse JSON response (security errors now return JSON)
          var parsed = null;
          if (typeof response === 'string') {
            try { parsed = JSON.parse(response); } catch(e) { parsed = null; }
          } else {
            parsed = response;
          }

          if (parsed && parsed.error_key === 'userspn_profile_create_security_error') {
            // Show specific security error message
            var msg = parsed.error_message || userspn_i18n.security_error || 'Security validation failed. Please try again.';
            userspn_get_main_message(msg);
          } else if (response == 'userspn_profile_create_error') {
            userspn_get_main_message(userspn_i18n.an_error_has_occurred);
          }else if (response == 'userspn_profile_create_existing') {
            userspn_get_main_message(userspn_i18n.user_existing);
            $('.userspn-tab-links[data-userspn-id="userspn-tab-login"]').click();

            if (!$('.userspn-profile-wrapper .user-existing').length) {
              $('.userspn-profile-wrapper').prepend('<div class="userspn-alert userspn-alert-warning user-existing">' + userspn_i18n.user_existing + '</div>');
            }

            $('#userspn-login input#user_login').focus();
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

      getRecaptchaTokenAndSubmit();

      delete window['userspn_window_vars'];
      return false;
    });
  });
})(jQuery);
