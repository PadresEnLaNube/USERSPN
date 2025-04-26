(function($) {
	'use strict';

  $(document).ready(function() {
    if ($('.userspn-password-checker').length) {
      var pass_view_state = false;

      function userspn_pass_check_strength(pass) {
        var strength = 0;
        var password = $('.userspn-password-strength');
        var low_upper_case = password.closest('.userspn-password-checker').find('.low-upper-case i');
        var number = password.closest('.userspn-password-checker').find('.one-number i');
        var special_char = password.closest('.userspn-password-checker').find('.one-special-char i');
        var eight_chars = password.closest('.userspn-password-checker').find('.eight-character i');

        //If pass contains both lower and uppercase characters
        if (pass.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) {
          strength += 1;
          low_upper_case.text('task_alt');
        } else {
          low_upper_case.text('radio_button_unchecked');
        }

        //If it has numbers and characters
        if (pass.match(/([0-9])/)) {
          strength += 1;
          number.text('task_alt');
        } else {
          number.text('radio_button_unchecked');
        }

        //If it has one special character
        if (pass.match(/([!,%,&,@,#,$,^,*,?,_,~,|,¬,+,ç,-,€])/)) {
          strength += 1;
          special_char.text('task_alt');
        } else {
          special_char.text('radio_button_unchecked');
        }

        //If pass is greater than 7
        if (pass.length > 7) {
          strength += 1;
          eight_chars.text('task_alt');
        } else {
          eight_chars.text('radio_button_unchecked');
        }

        // If value is less than 2
        if (strength < 2) {
          $('.userspn-password-strength-bar').removeClass('userspn-progress-bar-warning userspn-progress-bar-success').addClass('userspn-progress-bar-danger').css('width', '10%');
        } else if (strength == 3) {
          $('.userspn-password-strength-bar').removeClass('userspn-progress-bar-success userspn-progress-bar-danger').addClass('userspn-progress-bar-warning').css('width', '60%');
        } else if (strength == 4) {
          $('.userspn-password-strength-bar').removeClass('userspn-progress-bar-warning userspn-progress-bar-danger').addClass('userspn-progress-bar-success').css('width', '100%');
        }
      }

      $(document).on('click', ('.userspn-show-pass'), function(e){
        e.preventDefault();
        var userspn_btn = $('.userspn-show-pass');

        if (pass_view_state) {
          userspn_btn.siblings('#userspn_password').attr('type', 'password');
          userspn_btn.find('i').text('visibility_off');
          var pass_view_state = false;
        }else{
          userspn_btn.siblings('#userspn_password').attr('type', 'text');
          userspn_btn.find('i').text('visibility');
          var pass_view_state = true;
        } 
      });

      $(document).on('keyup', ('.userspn-password-strength'), function(e){
        userspn_pass_check_strength($('.userspn-password-strength').val());

        if (!$('#userspn-popover-pass').is(':visible')) {
          $('#userspn-popover-pass').fadeIn('slow');
        }

        if (!$('.userspn-show-pass').is(':visible')) {
          $('.userspn-show-pass').fadeIn('slow');
        }
      });
    }
    
    $(document).on('mouseover', '.userspn-input-star', function(e){
      if (!$(this).closest('.userspn-input-stars').hasClass('clicked')) {
        $(this).text('star');
        $(this).prevAll('.userspn-input-star').text('star');
      }
    });

    $(document).on('mouseout', '.userspn-input-stars', function(e){
      if (!$(this).hasClass('clicked')) {
        $(this).find('.userspn-input-star').text('star_outlined');
      }
    });

    $(document).on('click', '.userspn-input-star', function(e){
      e.preventDefault();
      $(this).closest('.userspn-input-stars').addClass('clicked');
      $(this).closest('.userspn-input-stars').find('.userspn-input-star').text('star_outlined');
      $(this).text('star');
      $(this).prevAll('.userspn-input-star').text('star');
      $(this).closest('.userspn-input-stars').siblings('.userspn-input-hidden-stars').val($(this).prevAll('.userspn-input-star').length + 1);
    });

    $(document).on('change', '.userspn-input-hidden-stars', function(e){
      $(this).siblings('.userspn-input-stars').find('.userspn-input-star').text('star_outlined');
      $(this).siblings('.userspn-input-stars').find('.userspn-input-star').slice(0, $(this).val()).text('star');
    });

    if ($('.userspn-field[data-userspn-parent]').length) {
      userspn_form_update();

      $(document).on('change', '.userspn-field[data-userspn-parent~="this"]', function(e) {
        userspn_form_update();
      });
    }

    if ($('.userspn-html-multi-group').length) {
      $(document).on('click', '.userspn-html-multi-remove-btn', function(e) {
        e.preventDefault();
        var userspn_users_btn = $(this);

        if (userspn_users_btn.closest('.userspn-html-multi-wrapper').find('.userspn-html-multi-group').length > 1) {
          $(this).closest('.userspn-html-multi-group').remove();
        }else{
          $(this).closest('.userspn-html-multi-group').find('input, select, textarea').val('');
        }
      });

      $(document).on('click', '.userspn-html-multi-add-btn', function(e) {
        e.preventDefault();

        $(this).closest('.userspn-html-multi-wrapper').find('.userspn-html-multi-group:first').clone().insertAfter($(this).closest('.userspn-html-multi-wrapper').find('.userspn-html-multi-group:last'));
        $(this).closest('.userspn-html-multi-wrapper').find('.userspn-html-multi-group:last').find('input, select, textarea').val('');

        $(this).closest('.userspn-html-multi-wrapper').find('.userspn-input-range').each(function(index, element) {
          $(this).siblings('.userspn-input-range-output').html($(this).val());
        });
      });

      $('.userspn-html-multi-wrapper').sortable({handle: '.userspn-multi-sorting'});

      $(document).on('sortstop', '.userspn-html-multi-wrapper', function(event, ui){
        userspn_get_main_message(userspn_i18n.ordered_element);
      });
    }

    if ($('.userspn-input-range').length) {
      $('.userspn-input-range').each(function(index, element) {
        $(this).siblings('.userspn-input-range-output').html($(this).val());
      });

      $(document).on('input', '.userspn-input-range', function(e) {
        $(this).siblings('.userspn-input-range-output').html($(this).val());
      });
    }

    if ($('.userspn-image-btn').length) {
      var image_frame;

      $(document).on('click', '.userspn-image-btn', function(e){
        e.preventDefault();

        if (image_frame){
          image_frame.open();
          return;
        }

        var userspn_input_btn = $(this);
        var userspn_images_block = userspn_input_btn.closest('.userspn-images-block').find('.userspn-images');
        var userspn_images_input = userspn_input_btn.closest('.userspn-images-block').find('.userspn-image-input');

        var image_frame = wp.media({
          title: (userspn_images_block.attr('data-userspn-multiple') == 'true') ? userspn_i18n.select_images : userspn_i18n.select_image,
          library: {
            type: 'image'
          },
          multiple: (userspn_images_block.attr('data-userspn-multiple') == 'true') ? 'true' : 'false',
        });

        image_frame.states.add([
          new wp.media.controller.Library({
            id: 'post-gallery',
            title: (userspn_images_block.attr('data-userspn-multiple') == 'true') ? userspn_i18n.edit_images : userspn_i18n.edit_image,
            priority: 20,
            toolbar: 'main-gallery',
            filterable: 'uploaded',
            library: wp.media.query(image_frame.options.library),
            multiple: (userspn_images_block.attr('data-userspn-multiple') == 'true') ? 'true' : 'false',
            editable: true,
            allowLocalEdits: true,
            displaySettings: true,
            displayUserSettings: true
          })
        ]);

        image_frame.open();

        image_frame.on('select', function() {
          var ids = [];
          var attachments_arr = [];
          var attachments_arr = image_frame.state().get('selection').toJSON();
          userspn_images_block.html('');

          $(attachments_arr).each(function(e){
            var sep = (e != (attachments_arr.length - 1))  ? ',' : '';
            ids += $(this)[0].id + sep;
            userspn_images_block.append('<img src="' + $(this)[0].url + '" class="">');
          });

          userspn_input_btn.text((userspn_images_block.attr('data-userspn-multiple') == 'true') ? userspn_i18n.select_images : userspn_i18n.select_image);
          userspn_images_input.val(ids);
        });
      });
    }

    if ($('.userspn-audio-btn').length) {
      var audio_frame;

      $(document).on('click', '.userspn-audio-btn', function(e){
        e.preventDefault();

        if (audio_frame){
          audio_frame.open();
          return;
        }

        var userspn_input_btn = $(this);
        var userspn_audios_block = userspn_input_btn.closest('.userspn-audios-block').find('.userspn-audios');
        var userspn_audios_input = userspn_input_btn.closest('.userspn-audios-block').find('.userspn-audio-input');

        var audio_frame = wp.media({
          title: (userspn_audios_block.attr('data-userspn-multiple') == 'true') ? userspn_i18n.select_audios : userspn_i18n.select_audio,
          library : {
            type : 'audio'
          },
          multiple: (userspn_audios_block.attr('data-userspn-multiple') == 'true') ? 'true' : 'false',
        });

        audio_frame.states.add([
          new wp.media.controller.Library({
            id: 'post-gallery',
            title: (userspn_audios_block.attr('data-userspn-multiple') == 'true') ? userspn_i18n.select_audios : userspn_i18n.select_audio,
            priority: 20,
            toolbar: 'main-gallery',
            filterable: 'uploaded',
            library: wp.media.query(audio_frame.options.library),
            multiple: (userspn_audios_block.attr('data-userspn-multiple') == 'true') ? 'true' : 'false',
            editable: true,
            allowLocalEdits: true,
            displaySettings: true,
            displayUserSettings: true
          })
        ]);

        audio_frame.open();

        audio_frame.on('select', function() {
          var ids = [];
          var attachments_arr = [];

          attachments_arr = audio_frame.state().get('selection').toJSON();
          userspn_audios_block.html('');

          $(attachments_arr).each(function(e){
            var sep = (e != (attachments_arr.length - 1))  ? ',' : '';
            ids += $(this)[0].id + sep;
            userspn_audios_block.append('<div class="userspn-audio userspn-tooltip" title="' + $(this)[0].title + '"><i class="dashicons dashicons-media-audio"></i></div>');
          });

          $('.userspn-tooltip').tooltipster({maxWidth: 300,delayTouch:[0, 4000], customClass: 'userspn-tooltip'});
          userspn_input_btn.text((userspn_audios_block.attr('data-userspn-multiple') == 'true') ? userspn_i18n.select_audios : userspn_i18n.select_audio);
          userspn_audios_input.val(ids);
        });
      });
    }

    if ($('.userspn-video-btn').length) {
      var video_frame;

      $(document).on('click', '.userspn-video-btn', function(e){
        e.preventDefault();

        if (video_frame){
          video_frame.open();
          return;
        }

        var userspn_input_btn = $(this);
        var userspn_videos_block = userspn_input_btn.closest('.userspn-videos-block').find('.userspn-videos');
        var userspn_videos_input = userspn_input_btn.closest('.userspn-videos-block').find('.userspn-video-input');

        var video_frame = wp.media({
          title: (userspn_videos_block.attr('data-userspn-multiple') == 'true') ? userspn_i18n.select_videos : userspn_i18n.select_video,
          library : {
            type : 'video'
          },
          multiple: (userspn_videos_block.attr('data-userspn-multiple') == 'true') ? 'true' : 'false',
        });

        video_frame.states.add([
          new wp.media.controller.Library({
            id: 'post-gallery',
            title: (userspn_videos_block.attr('data-userspn-multiple') == 'true') ? userspn_i18n.select_videos : userspn_i18n.select_video,
            priority: 20,
            toolbar: 'main-gallery',
            filterable: 'uploaded',
            library: wp.media.query(video_frame.options.library),
            multiple: (userspn_videos_block.attr('data-userspn-multiple') == 'true') ? 'true' : 'false',
            editable: true,
            allowLocalEdits: true,
            displaySettings: true,
            displayUserSettings: true
          })
        ]);

        video_frame.open();

        video_frame.on('select', function() {
          var ids = [];
          var attachments_arr = [];

          attachments_arr = video_frame.state().get('selection').toJSON();
          userspn_videos_block.html('');

          $(attachments_arr).each(function(e){
            var sep = (e != (attachments_arr.length - 1))  ? ',' : '';
            ids += $(this)[0].id + sep;
            userspn_videos_block.append('<div class="userspn-video userspn-tooltip" title="' + $(this)[0].title + '"><i class="dashicons dashicons-media-video"></i></div>');
          });

          $('.userspn-tooltip').tooltipster({maxWidth: 300,delayTouch:[0, 4000], customClass: 'userspn-tooltip'});
          userspn_input_btn.text((userspn_videos_block.attr('data-userspn-multiple') == 'true') ? userspn_i18n.select_videos : userspn_i18n.select_video);
          userspn_videos_input.val(ids);
        });
      });
    }

    if ($('.userspn-file-btn').length) {
      var file_frame;

      $(document).on('click', '.userspn-file-btn', function(e){
        e.preventDefault();

        if (file_frame){
          file_frame.open();
          return;
        }

        var userspn_input_btn = $(this);
        var userspn_files_block = userspn_input_btn.closest('.userspn-files-block').find('.userspn-files');
        var userspn_files_input = userspn_input_btn.closest('.userspn-files-block').find('.userspn-file-input');

        var file_frame = wp.media({
          title: (userspn_files_block.attr('data-userspn-multiple') == 'true') ? userspn_i18n.select_files : userspn_i18n.select_file,
          multiple: (userspn_files_block.attr('data-userspn-multiple') == 'true') ? 'true' : 'false',
        });

        file_frame.states.add([
          new wp.media.controller.Library({
            id: 'post-gallery',
            title: (userspn_files_block.attr('data-userspn-multiple') == 'true') ? userspn_i18n.select_files : userspn_i18n.select_file,
            priority: 20,
            toolbar: 'main-gallery',
            filterable: 'uploaded',
            library: wp.media.query(file_frame.options.library),
            multiple: (userspn_files_block.attr('data-userspn-multiple') == 'true') ? 'true' : 'false',
            editable: true,
            allowLocalEdits: true,
            displaySettings: true,
            displayUserSettings: true
          })
        ]);

        file_frame.open();

        file_frame.on('select', function() {
          var ids = [];
          var attachments_arr = [];

          attachments_arr = file_frame.state().get('selection').toJSON();
          userspn_files_block.html('');

          $(attachments_arr).each(function(e){
            var sep = (e != (attachments_arr.length - 1))  ? ',' : '';
            ids += $(this)[0].id + sep;
            userspn_files_block.append('<embed src="' + $(this)[0].url + '" type="application/pdf" class="userspn-embed-file"/>');
          });

          userspn_input_btn.text((userspn_files_block.attr('data-userspn-multiple') == 'true') ? userspn_i18n.edit_files : userspn_i18n.edit_file);
          userspn_files_input.val(ids);
        });
      });
    }
  });

  $(document).on('click', '.userspn-toggle', function(e) {
    e.preventDefault();
    var userspn_toggle = $(this);

    if (userspn_toggle.find('i').length) {
      if (userspn_toggle.siblings('.userspn-toggle-content').is(':visible')) {
        userspn_toggle.find('i').text('add');
      }else{
        userspn_toggle.find('i').text('clear');
      }
    }

    userspn_toggle.siblings('.userspn-toggle-content').fadeToggle();
  });
})(jQuery);
