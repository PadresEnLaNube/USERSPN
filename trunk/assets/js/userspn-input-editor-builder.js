(function($) {
	'use strict';

  function userspn_input_update(){
    $('select.userspn-input-type').each(function(index, element) {
      $(this).closest('.userspn-input-editor-builder-ul').find('li.userspn-input-subtype,li.userspn-select-subtype,li.userspn-textarea-subtype').fadeOut();

      if($(this).val() != ''){
        $(this).closest('.userspn-input-editor-builder-ul').find('li.userspn-' + $(this).val() + '-subtype').fadeIn('slow');
      }
    });
  }

  $(document).on('click', '.userspn-input-editor-builder-btn-save', function(e) {
    e.preventDefault();
    var userspn_btn = $(this);
    var userspn_ul = userspn_btn.closest('.userspn-toggle-content').find('.userspn-input-editor-builder-ul');
    userspn_btn.addClass('userspn-link-disabled');

    var ajax_url = userspn_ajax.ajax_url;
    var data = {
      action: 'userspn_ajax',
      userspn_ajax_type: 'userspn_input_editor_builder_save',
      userspn_ajax_nonce: userspn_ajax.userspn_ajax_nonce,
      userspn_input_current_id: userspn_ul.attr('id'),
      userspn_input_name: userspn_ul.find('input.userspn-input-name').val(),
      userspn_input_class: userspn_ul.find('input.userspn-input-class').val(),
      userspn_input_type: userspn_ul.find('select.userspn-input-type').val(),
      userspn_input_subtype: userspn_ul.find('select.userspn-input-subtype').val(),
      userspn_select_subtype: userspn_ul.find('select.userspn-select-subtype').val(),
      userspn_textarea_subtype: userspn_ul.find('textarea.userspn-textarea-subtype').val(),
      userspn_select_options: userspn_ul.find('textarea.userspn-select-options').val(),
      userspn_input_required: userspn_ul.find('select.userspn-input-required').val(),
      userspn_meta: userspn_btn.closest('.userspn-user-register-fields-wrapper').attr('data-userspn-meta'),
      userspn_form_type: userspn_btn.closest('.userspn-user-register-fields-wrapper').attr('data-userspn-form-type'),
      post_id: userspn_btn.attr('data-userspn-post-id'),
    };

    $.post(ajax_url, data, function(response) {
      var parsed = $.parseJSON(response);
      if (parsed['error_key'] && parsed['error_key'] !== 0) {
        userspn_get_main_message(userspn_i18n.field_provide);
      }else {
        var userspn_input_id = parsed['field_id'];
        var userspn_input_type = parsed['type'];
        var userspn_meta = parsed['userspn_meta'];
        var userspn_input_name = parsed['label'];

        if (!$('.userspn-user-register-field#' + userspn_input_id).length) {
          $('.userspn-user-register-fields').append(parsed['html']);

          var userspn_input_name = $('input.userspn-input-name[value="' + userspn_input_name + '"]');
          var userspn_toggle_wrapper = userspn_input_name.closest('.userspn-toggle-wrapper');

          userspn_input_name.closest('.userspn-input-editor-builder-ul').attr('id', userspn_input_id);
          userspn_toggle_wrapper.addClass(userspn_input_id);
          userspn_toggle_wrapper.find('.userspn-input-editor-builder-btn-remove-popup').attr('data-userspn-input-id', userspn_input_id);
          userspn_toggle_wrapper.find('.userspn-input-editor-builder-btn-remove-popup').attr('data-userspn-input-type', userspn_input_type);
          userspn_toggle_wrapper.find('.userspn-input-editor-builder-btn-remove-popup').attr('data-userspn-meta', userspn_meta);
          userspn_toggle_wrapper.find('.userspn-input-editor-builder-btn-remove-popup').attr('id', 'userspn-input-editor-builder-btn-remove-popup-' + userspn_input_id);
          userspn_toggle_wrapper.find('a.userspn-popup-open').attr('data-userspn-popup-id', 'userspn-input-editor-builder-btn-remove-popup-' + userspn_input_id);
        }else{
          $('.userspn-user-register-field#' + userspn_input_id).after(parsed['html']);
          $('.userspn-user-register-field.' + userspn_input_id + ':first').remove();
        }
      
        if ($('.userspn-user-register-fields-empty').length) {
          $('.userspn-user-register-fields-empty').remove();
        }

        userspn_get_main_message(userspn_i18n.field_saved);
      }

      userspn_btn.removeClass('userspn-link-disabled');
    });
  });

  $(document).on('blur', 'input.userspn-input-name', function(e) {
    $(this).closest('.userspn-toggle-wrapper').find('.userspn-input-name-span').text($(this).val());
    $(this).attr('value', $(this).val());
  });

  $(document).on('click', '.userspn-input-editor-builder-btn-remove', function(e) {
    e.preventDefault();
    var userspn_btn = $(this);
    userspn_btn.addClass('userspn-link-disabled');
    var userspn_input_id = userspn_btn.closest('.userspn-input-editor-builder-btn-remove-popup').attr('data-userspn-input-id');
    var userspn_meta = userspn_btn.closest('.userspn-input-editor-builder-btn-remove-popup').attr('data-userspn-meta');

    var ajax_url = userspn_ajax.ajax_url;
    var data = {
      action: 'userspn_ajax',
      userspn_ajax_type: 'userspn_input_editor_builder_remove',
      userspn_ajax_nonce: userspn_ajax.userspn_ajax_nonce,
      userspn_form_type: userspn_btn.closest('.userspn-input-editor-builder-btn-remove-popup').attr('data-userspn-input-type'),
      userspn_input_id: userspn_input_id,
      userspn_meta: userspn_meta,
      post_id: userspn_btn.attr('data-userspn-post-id'),
    };

    $.post(ajax_url, data, function(response) {
      if (response.indexOf('userspn_input_editor_builder_remove_success') != -1) {
        USERSPN_Popups.close();
        $('.userspn-user-register-field.' + userspn_input_id).fadeOut('fast').remove();
        $('.userspn-toggle-wrapper.' + userspn_input_id).fadeOut('fast').remove();
        userspn_get_main_message(userspn_i18n.field_removed);
      }else {
        userspn_get_main_message(userspn_i18n.an_error_has_occurred);
      }

      userspn_btn.removeClass('userspn-link-disabled');
    });
  });

  $(document).on('change', 'select.userspn-input-type', function(e) {
    userspn_input_update();
  });
  
  userspn_input_update();
})(jQuery);