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

  // Preview profile in settings
  $(document).on('click', '.userspn-preview-profile-btn', function(e) {
    e.preventDefault();
    if (!confirm(userspn_i18n.save_before_preview)) {
      return;
    }
    $('.userspn-profile-popup-btn').show();
  });

  // Override bubble click in settings to add afterClose
  $(document).on('click', '.userspn-profile-popup-btn', function(e) {
    if (!$('.userspn-options').length) return;
    e.preventDefault();
    e.stopImmediatePropagation();
    if (typeof USERSPN_Popups !== 'undefined') {
      USERSPN_Popups.open($('#userspn-profile-popup'), {
        afterClose: function() {
          $('.userspn-profile-popup-btn').hide();
        }
      });
    }
  });

  // Custom bubble position — open drag editor when checkbox is checked
  $(document).on('change', '#userspn_bubble_custom_position', function() {
    if ($(this).is(':checked')) {
      userspn_open_bubble_drag_editor();
      $(this).prop('checked', false);
    }
  });

  function userspn_open_bubble_drag_editor() {
    var $overlay = $('<div class="userspn-bubble-drag-overlay"></div>').css({
      position: 'fixed', top: 0, left: 0, width: '100%', height: '100%',
      background: 'rgba(0,0,0,0.5)', zIndex: 99998
    });

    // Read saved position or default to center
    var savedX = parseInt($('#userspn_bubble_position_x').val(), 10);
    var savedY = parseInt($('#userspn_bubble_position_y').val(), 10);
    var hasSaved = !isNaN(savedX) && !isNaN(savedY) && (savedX || savedY);

    var $bubble = $('.userspn-profile-popup-btn').clone().removeAttr('style').css({
      position: 'fixed',
      top: hasSaved ? savedY + 'px' : '50%',
      left: hasSaved ? savedX + 'px' : '50%',
      transform: hasSaved ? 'none' : 'translate(-50%, -50%)',
      zIndex: 99999, cursor: 'grab', display: 'block'
    }).addClass('userspn-bubble-drag-clone');

    var $topBar = $('<div class="userspn-bubble-drag-topbar"></div>').css({
      position: 'fixed', top: 0, left: 0, width: '100%', zIndex: 99999,
      display: 'flex', alignItems: 'center', justifyContent: 'center',
      gap: '15px', padding: '15px', background: 'rgba(0,0,0,0.7)'
    });

    var $instruction = $('<span></span>').text(userspn_i18n.drag_bubble_instruction).css({
      color: '#fff', fontSize: '14px', fontWeight: 'bold'
    });
    var $confirmBtn = $('<a href="#" class="userspn-btn"></a>').text(userspn_i18n.confirm_position);
    var $cancelBtn = $('<a href="#" class="userspn-btn userspn-btn-transparent"></a>').text(
      userspn_i18n.cancel || 'Cancel'
    ).css({ color: '#fff', borderColor: '#fff' });

    $topBar.append($instruction, $confirmBtn, $cancelBtn);
    $('body').append($overlay, $bubble, $topBar);

    if ($.fn.draggable) {
      $bubble.draggable({
        containment: 'window',
        start: function() { $(this).css({ cursor: 'grabbing', transform: 'none' }); },
        stop: function() { $(this).css('cursor', 'grab'); }
      });
    }

    function cleanup() {
      $overlay.remove();
      $bubble.remove();
      $topBar.remove();
    }

    $confirmBtn.on('click', function(e) {
      e.preventDefault();
      var posX = Math.round(parseInt($bubble.css('left'), 10)) || 0;
      var posY = Math.round(parseInt($bubble.css('top'), 10)) || 0;

      // Update hidden form fields and selector
      $('#userspn_bubble_position_x').val(posX);
      $('#userspn_bubble_position_y').val(posY);
      $('#userspn_bubble_position_type').val('custom').trigger('change');

      cleanup();

      // Trigger the form submit which is handled by userspn-ajax.js
      $('#userspn_form').trigger('submit');
    });

    $cancelBtn.on('click', function(e) {
      e.preventDefault();
      cleanup();
    });

    $overlay.on('click', function(e) {
      if (e.target === this) {
        cleanup();
      }
    });
  }

})(jQuery);
