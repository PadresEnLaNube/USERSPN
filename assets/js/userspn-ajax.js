(function($) {
	'use strict';

  $(document).ready(function() {
    var userspnRecaptchaLoaderPromise = null;

    function userspnLoadRecaptchaScript() {
      if (typeof userspn_security === 'undefined' || !userspn_security.recaptcha_enabled || !userspn_security.recaptcha_site_key) {
        return null;
      }

      if (userspnRecaptchaLoaderPromise) {
        return userspnRecaptchaLoaderPromise;
      }

      if (typeof grecaptcha !== 'undefined') {
        userspnRecaptchaLoaderPromise = Promise.resolve();
        return userspnRecaptchaLoaderPromise;
      }

      userspnRecaptchaLoaderPromise = new Promise(function(resolve, reject) {
        var script = document.createElement('script');
        script.src = 'https://www.google.com/recaptcha/api.js?render=' + userspn_security.recaptcha_site_key;
        script.async = true;
        script.defer = true;
        script.onload = function() {
          resolve();
        };
        script.onerror = function() {
          reject();
        };
        document.head.appendChild(script);
      });

      return userspnRecaptchaLoaderPromise;
    }

    if ($('#userspn-newsletter-form').length > 0) {
      userspnLoadRecaptchaScript();
    }
    $(document).on('submit', '.userspn-form', function(e){
      var userspn_form = $(this);
      var userspn_btn = userspn_form.find('input[type="submit"]');
      userspn_btn.addClass('userspn-link-disabled').siblings('.userspn-waiting').removeClass('userspn-display-none');

      var ajax_url = userspn_ajax.ajax_url;
      var data = {
        action: 'userspn_ajax_nopriv',
        userspn_ajax_nopriv_type: 'userspn_form_save',
        userspn_ajax_nopriv_nonce: userspn_ajax.userspn_ajax_nonce,
        userspn_form_id: userspn_form.attr('id'),
        userspn_form_type: userspn_btn.attr('data-userspn-type'),
        userspn_form_subtype: userspn_btn.attr('data-userspn-subtype'),
        userspn_form_user_id: userspn_btn.attr('data-userspn-user-id'),
        userspn_form_post_id: userspn_btn.attr('data-userspn-post-id'),
        ajax_keys: [],
      };

      if (!(typeof window['userspn_window_vars'] !== 'undefined')) {
        window['userspn_window_vars'] = [];
      }

      $(userspn_form.find('input:not([type="submit"]), select, textarea')).each(function(index, element) {
        if ($(this).parents('.userspn-html-multi-group').length) {
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
            data[element.name] = $(element).val();
          }
        }

        data.ajax_keys.push({
          id: element.name,
          node: element.nodeName,
          type: element.type,
        });
      });

      $.post(ajax_url, data, function(response) {
        console.log(data);console.log(response);
        
        if (response == 'userspn_profile_edit_success') {
          userspn_get_main_message(userspn_i18n.profile_updated);
          $(document).trigger('userspn_profile_updated');
        }else if (response == 'userspn_form_save_error_unlogged') {
          userspn_get_main_message(userspn_i18n.user_unlogged);

          if (!$('.userspn-profile-wrapper .user-unlogged').length) {
            $('.userspn-profile-wrapper').prepend('<div class="userspn-alert userspn-alert-warning user-unlogged">' + userspn_i18n.user_unlogged + '</div>');
          }

          USERSPN_Popups.open($('#userspn-profile-popup'));
          $('#userspn-login input#user_login').focus();
        }else if (response == 'userspn_form_save_error') {
          userspn_get_main_message(userspn_i18n.an_error_has_occurred);
        }else {
          userspn_get_main_message(userspn_i18n.saved_successfully);
        }

        userspn_btn.removeClass('userspn-link-disabled').siblings('.userspn-waiting').fadeOut('fast');
      });

      delete window['userspn_window_vars'];
      return false;
    });

    $(document).on('click', '.userspn-input-editor-builder-btn-add', function(e) {
      e.preventDefault();
      var userspn_btn = $(this);
      var userspn_ul = userspn_btn.closest('.userspn-toggle-content').find('.userspn-input-editor-builder-ul');
      userspn_btn.addClass('userspn-link-disabled');

      var ajax_url = userspn_ajax.ajax_url;
      var data = {
        action: 'userspn_ajax',
        userspn_ajax_type: 'userspn_input_editor_builder_add',
        userspn_ajax_nonce: userspn_ajax.userspn_ajax_nonce,
        userspn_meta: userspn_btn.attr('data-userspn-meta'),
      };

      $.post(ajax_url, data, function(response) {
        $('.userspn-user-register-fields-edition').append(response);
        userspn_btn.removeClass('userspn-link-disabled');

        $('.userspn-select').each(function(index) {
          if ($(this).attr('multiple') == 'true') {
            // For a multiple select
            $(this).USERSPN_Selector({
              multiple: true,
              searchable: true,
              placeholder: userspn_i18n.select_options,
            });
          }else{
            // For a single select
            $(this).USERSPN_Selector();
          }
        });
      });
    });

    $(document).on('submit', '#userspn-newsletter-form', function(e) {
      var userspn_form = $(this);
      var userspn_btn = userspn_form.find('.userspn-newsletter-btn');
      userspn_btn.addClass('userspn-link-disabled').siblings('.userspn-waiting').fadeIn('slow');

      var ajax_url = userspn_ajax.ajax_url;
      var data = {
        action: 'userspn_ajax_nopriv',
        userspn_ajax_nopriv_type: 'userspn_newsletter',
        userspn_ajax_nopriv_nonce: userspn_ajax.userspn_ajax_nonce,
        userspn_email: userspn_form.find('#userspn-newsletter-email').val(),
      };

      var honeypotField = userspn_form.find('input[name="userspn_honeypot_field"]');
      if (honeypotField.length) {
        data['userspn_honeypot_field'] = honeypotField.val();
      }

      var recaptchaEnabled = (typeof userspn_security !== 'undefined' && userspn_security.recaptcha_enabled && userspn_security.recaptcha_site_key);

      if (recaptchaEnabled) {
        userspnLoadRecaptchaScript();
      }

      function submitNewsletter() {
        $.post(ajax_url, data, function(response) {
          console.log(data);console.log(response);
          if (response == 'userspn_newsletter_success_activation_sent') {
            userspn_get_main_message(userspn_i18n.activation_email);
            $('.userspn-newsletter').html('<div class="userspn-alert-warning"><p>' + userspn_i18n.email_sent + '</p></div>');
          }else if (response == 'userspn_newsletter_success') {
            userspn_get_main_message(userspn_i18n.newsletter_subscribed);
            $('.userspn-newsletter').html('<div class="userspn-alert-success"><p>' + userspn_i18n.newsletter_subscribed + '</p></div>');
          }else if(response == 'userspn_newsletter_error_exceeded') {
            userspn_get_main_message(userspn_i18n.email_too_many);
            $('.userspn-newsletter').html('<div class="userspn-alert-error"><p>' + userspn_i18n.email_too_many + '</p></div>');
          }else if(response == 'userspn_newsletter_security_error') {
            userspn_get_main_message(userspn_i18n.security_error || userspn_i18n.an_error_has_occurred);
          }else if(response == 'userspn_newsletter_error') {
            userspn_get_main_message(userspn_i18n.an_error_has_occurred);
          }else{
            userspn_get_main_message(userspn_i18n.an_error_has_occurred);
          }

          USERSPN_Popups.close();
          userspn_btn.removeClass('userspn-link-disabled').siblings('.userspn-waiting').fadeOut('slow');
        });
      }

      function executeRecaptchaAndSubmit() {
        if (recaptchaEnabled && typeof grecaptcha !== 'undefined') {
          grecaptcha.ready(function() {
            grecaptcha.execute(userspn_security.recaptcha_site_key, {action: 'newsletter'}).then(function(token) {
              data['g-recaptcha-response'] = token;
              submitNewsletter();
            }).catch(function() {
              submitNewsletter();
            });
          });
        } else {
          submitNewsletter();
        }
      }

      if (recaptchaEnabled && typeof grecaptcha === 'undefined' && userspnRecaptchaLoaderPromise) {
        userspnRecaptchaLoaderPromise.then(function() {
          executeRecaptchaAndSubmit();
        }).catch(function() {
          submitNewsletter();
        });
      } else {
        executeRecaptchaAndSubmit();
      }

      return false;
    });
  });
})(jQuery);
