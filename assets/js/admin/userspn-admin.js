(function($) {
	'use strict';

	// Hide bubble by default on settings page
	$(function() {
		if ($('.userspn-options').length) {
			$('.userspn-profile-popup-btn').hide();
		}
	});

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

  // Preview profile in settings
  $(document).on('click', '.userspn-preview-profile-btn', function(e) {
    e.preventDefault();
    if (!confirm(userspn_i18n.save_before_preview)) {
      return;
    }
    $('.userspn-profile-popup-btn').show();
  });

  // Override bubble click in settings to add afterClose
  $(document).on('click', '.userspn-options .userspn-profile-popup-btn, body.userspn-settings-preview .userspn-profile-popup-btn', function(e) {
    if (!$('.userspn-options').length) return;
    e.preventDefault();
    e.stopImmediatePropagation();
    USERSPN_Popups.open($('#userspn-profile-popup'), {
      afterClose: function() {
        $('.userspn-profile-popup-btn').hide();
      }
    });
  });

  // Drag-and-drop bubble positioning
  $(document).on('change', '#userspn_bubble_custom_position', function() {
    if ($(this).val() === 'on') {
      var $bubble = $('.userspn-profile-popup-btn');
      $bubble.show().css({ position: 'fixed', zIndex: 99999 });
      if ($.fn.draggable) {
        $bubble.draggable({ containment: 'window' });
      }
      if (!$('.userspn-confirm-bubble-position').length) {
        $(this).closest('.userspn-input-wrapper').append(
          '<a href="#" class="userspn-btn userspn-btn-mini userspn-confirm-bubble-position userspn-mt-10">' +
          userspn_i18n.confirm_position + '</a>'
        );
      }
    } else {
      $('.userspn-profile-popup-btn').hide().css({ position: '', zIndex: '', left: '', top: '' });
      if ($.fn.draggable && $('.userspn-profile-popup-btn').draggable('instance')) {
        $('.userspn-profile-popup-btn').draggable('destroy');
      }
      $('.userspn-confirm-bubble-position').remove();
    }
  });

  // AJAX save bubble position
  $(document).on('click', '.userspn-confirm-bubble-position', function(e) {
    e.preventDefault();
    var $bubble = $('.userspn-profile-popup-btn');
    var posX = Math.round(parseInt($bubble.css('left'), 10)) || 0;
    var posY = Math.round(parseInt($bubble.css('top'), 10)) || 0;

    $.post(userspn_ajax.ajax_url, {
      action: 'userspn_ajax',
      userspn_ajax_nonce: userspn_ajax.userspn_ajax_nonce,
      userspn_ajax_type: 'userspn_options_save',
      userspn_bubble_position_x: posX,
      userspn_bubble_position_y: posY,
      ajax_keys: [
        { id: 'userspn_bubble_position_x', node: 'INPUT', type: 'hidden' },
        { id: 'userspn_bubble_position_y', node: 'INPUT', type: 'hidden' }
      ]
    }, function() {
      userspn_get_main_message(userspn_i18n.confirm_bubble_position);
      $bubble.hide().css({ position: '', zIndex: '', left: '', top: '' });
      if ($.fn.draggable && $bubble.draggable('instance')) {
        $bubble.draggable('destroy');
      }
      $('.userspn-confirm-bubble-position').remove();
    });
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
