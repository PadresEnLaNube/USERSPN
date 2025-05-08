(function($) {
	'use strict';

	$(document).on('click', '.userspn-tab-links', function(e){
    e.preventDefault();
    var tab_link = $(this);
    var tab_wrapper = $(this).closest('.userspn-tabs-wrapper');
    
    tab_wrapper.find('.userspn-tab-links').each(function(index, element) {
      $(this).removeClass('active');
      $($(this).attr('data-userspn-id')).addClass('userspn-display-none');
    });

    tab_wrapper.find('.userspn-tab-content').each(function(index, element) {
      $(this).addClass('userspn-display-none');
    });
    
    tab_link.addClass('active');
    tab_wrapper.find('#' + tab_link.attr('data-userspn-id')).removeClass('userspn-display-none');
  });

  $(document).on('click', '.userspn-options-save-btn', function(e){
    e.preventDefault();
    var userspn_btn = $(this);
    userspn_btn.addClass('userspn-link-disabled').siblings('.userspn-waiting').removeClass('userspn-display-none');

    var ajax_url = userspn_ajax.ajax_url;

    var data = {
      action: 'userspn_ajax',
      userspn_ajax_nonce: userspn_ajax.userspn_ajax_nonce,
      userspn_ajax_type: 'userspn_options_save',
      ajax_keys: [],
    };

    if (!(typeof window['userspn_window_vars'] !== 'undefined')) {
      window['userspn_window_vars'] = [];
    }

    $('.userspn-options-fields input:not([type="submit"]), .userspn-options-fields select, .userspn-options-fields textarea').each(function(index, element) {
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
      if ($.parseJSON(response)['error_key'] != '') {
        userspn_get_main_message(userspn_i18n.an_error_has_occurred);
      }else {
        userspn_get_main_message(userspn_i18n.saved_successfully);
      }

      userspn_btn.removeClass('userspn-link-disabled').siblings('.userspn-waiting').addClass('userspn-display-none')
    });

    delete window['userspn_window_vars'];
  });
})(jQuery);