(function($) {
	'use strict';

  $(document).ready(function() {
    $(document).on('submit', '.userspn-form', function(e){
      var userspn_form = $(this);
      var userspn_btn = userspn_form.find('input[type="submit"]');
      userspn_btn.addClass('userspn-link-disabled').siblings('.userspn-waiting').removeClass('userspn-display-none');

      var ajax_url = userspn_ajax.ajax_url;
      var data = {
        action: 'userspn_ajax_nopriv',
        ajax_nonce: userspn_ajax.ajax_nonce,
        userspn_ajax_nopriv_type: 'userspn_form_save',
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
        if ($.parseJSON(response)['error_key'] == 'userspn_form_save_error_unlogged') {
          userspn_get_main_message(userspn_i18n.user_unlogged);

          if (!$('.userspn-profile-wrapper .user-unlogged').length) {
            $('.userspn-profile-wrapper').prepend('<div class="userspn-alert userspn-alert-warning user-unlogged">' + userspn_i18n.user_unlogged + '</div>');
          }

          $.fancybox.open($('#userspn-profile-popup'), {touch: false});
          $('#userspn-login input#user_login').focus();
        }else if ($.parseJSON(response)['error_key'] == 'userspn_form_save_error') {
          userspn_get_main_message(userspn_i18n.an_error_has_occurred);
        }else {
          userspn_get_main_message(userspn_i18n.saved_successfully);
        }

        userspn_btn.removeClass('userspn-link-disabled').siblings('.userspn-waiting').addClass('userspn-display-none')
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
        userspn_meta: userspn_btn.attr('data-userspn-meta'),
      };

      $.post(ajax_url, data, function(response) {
        $('.userspn-user-register-fields-edition').append(response);
        userspn_btn.removeClass('userspn-link-disabled');

        $('.userspn-select').each(function(index) {
          if ($(this).hasClass('search-disabled')) {
            $(this).select2({minimumResultsForSearch: -1});
          }else{
            $(this).select2();
          }
        });
      });
    });

    $(document).on('submit', '#userspn-newsletter-form', function(e) {
      var userspn_btn = $(this).find('.userspn-newsletter-btn');
      userspn_btn.addClass('userspn-link-disabled').siblings('.userspn-waiting').fadeIn('slow');

      var ajax_url = userspn_ajax.ajax_url;
      var data = {
        action: 'userspn_ajax_nopriv',
        userspn_ajax_nopriv_type: 'userspn_newsletter',
        userspn_email: userspn_btn.closest('.userspn-newsletter-form').find('#userspn-newsletter-email').val(),
      };

      $.post(ajax_url, data, function(response) {
        if (response == 'userspn_newsletter_success_activation_sent') {
          userspn_get_main_message(userspn_i18n.activation_email);
          $('.userspn-newsletter').html('<div class="userspn-alert-warning"><p>' + userspn_i18n.email_sent + '</p></div>');
        }else if (response == 'userspn_newsletter_success') {
          userspn_get_main_message(userspn_i18n.newsletter_subscribed);
          $('.userspn-newsletter').html('<div class="userspn-alert-success"><p>' + userspn_i18n.newsletter_subscribed + '</p></div>');
        }else if(response == 'userspn_newsletter_error_exceeded') {
          userspn_get_main_message(userspn_i18n.email_too_many);
          $('.userspn-newsletter').html('<div class="userspn-alert-error"><p>' + userspn_i18n.email_too_many + '</p></div>');
        }else if(response == 'userspn_newsletter_error') {
          userspn_get_main_message(userspn_i18n.an_error_has_occurred);
        }else{
          userspn_get_main_message(userspn_i18n.an_error_has_occurred);
        }

        $.fancybox.close();
        userspn_btn.removeClass('userspn-link-disabled').siblings('.userspn-waiting').fadeOut('slow');
      });

      return false;
    });
  });
})(jQuery);
