(function($) {
  'use strict';

  $(document).ready(function() {
    if ($('.userspn-password-checker').length) {
      var pass_view_state = false;
      // Caracteres seguros para WP (como en userspn-forms.js)
      var wp_safe_special_chars = '!@#$%^&*()_+-=[]{}|;:,.<>?';
      var wp_safe_chars_regex = new RegExp('^[a-zA-Z0-9' + wp_safe_special_chars.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ']+$');

      function userspn_pass_check_strength(pass) {
        var strength = 0;
        var password = $('.userspn-password-strength');
        var low_upper_case = password.closest('.userspn-password-checker').find('.low-upper-case i');
        var number = password.closest('.userspn-password-checker').find('.one-number i');
        var special_char = password.closest('.userspn-password-checker').find('.one-special-char i');
        var eight_chars = password.closest('.userspn-password-checker').find('.eight-character i');

        // Verificar si la contraseña contiene caracteres no seguros para WordPress (añadido)
        if (pass.length > 0 && !wp_safe_chars_regex.test(pass)) {
          var unsafe_chars = [];
          for (var i = 0; i < pass.length; i++) {
            var ch = pass[i];
            if (!wp_safe_chars_regex.test(ch)) {
              unsafe_chars.push(ch);
            }
          }
          var error_message = 'La contraseña contiene caracteres no permitidos: ' + unsafe_chars.join(', ') + '. Solo se permiten letras, números y estos símbolos: ' + wp_safe_special_chars;
          if (typeof userspn_get_main_message === 'function') {
            userspn_get_main_message(error_message);
          }
          // Resetear indicadores de fortaleza
          low_upper_case.text('radio_button_unchecked');
          number.text('radio_button_unchecked');
          special_char.text('radio_button_unchecked');
          eight_chars.text('radio_button_unchecked');
          $('.userspn-password-strength-bar').removeClass('userspn-progress-bar-warning userspn-progress-bar-success userspn-progress-bar-danger').css('width', '0%');
          return;
        }

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

      $(document).on('click', '.userspn-show-pass', function(e){
        e.preventDefault();
        var userspn_btn = $(this);
        var password_input = userspn_btn.siblings('.userspn-password-strength');

        if (pass_view_state) {
          password_input.attr('type', 'password');
          userspn_btn.find('i').text('visibility');
          pass_view_state = false;
        } else {
          password_input.attr('type', 'text');
          userspn_btn.find('i').text('visibility_off');
          pass_view_state = true;
        }
      });

      $(document).on('keyup', ('.userspn-password-strength'), function(e){
        var password_value = $(this).val();
        // Mostrar mensaje de caracteres no válidos si procede (añadido)
        if (typeof userspn_get_main_message === 'function') {
          if (password_value.length > 0 && !wp_safe_chars_regex.test(password_value)) {
            var unsafe_chars = [];
            for (var i = 0; i < password_value.length; i++) {
              var cc = password_value[i];
              if (!wp_safe_chars_regex.test(cc)) {
                unsafe_chars.push(cc);
              }
            }
            if (unsafe_chars.length > 0) {
              var err = 'Caracteres no permitidos: ' + unsafe_chars.join(', ') + '. Solo se permiten letras, números y estos símbolos: ' + wp_safe_special_chars;
              userspn_get_main_message(err);
            }
          }
        }

        userspn_pass_check_strength(password_value);

        if (!$('#userspn-popover-pass').is(':visible')) {
          $('#userspn-popover-pass, #userspn-popover-pass ul').fadeIn('slow');
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
      e.stopPropagation();
      e.stopImmediatePropagation();

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
        e.stopPropagation();
        e.stopImmediatePropagation();

        var userspn_users_btn = $(this);

        if (userspn_users_btn.closest('.userspn-html-multi-wrapper').find('.userspn-html-multi-group').length > 1) {
          $(this).closest('.userspn-html-multi-group').remove();
        } else {
          $(this).closest('.userspn-html-multi-group').find('input, select, textarea').val('');
        }
      });

      $(document).on('click', '.userspn-html-multi-add-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

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
        e.stopPropagation();
        e.stopImmediatePropagation();

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

          attachments_arr = image_frame.state().get('selection').toJSON();
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
        e.stopPropagation();
        e.stopImmediatePropagation();

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
        e.stopPropagation();
        e.stopImmediatePropagation();

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
        e.stopPropagation();
        e.stopImmediatePropagation();

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

    // Audio recorder functionality (añadido desde userspn-forms.js)
    if ($('.userspn-audio-recorder-wrapper').length) {
      var mediaRecorder;
      var audioChunks = [];
      var audioBlob;
      var audioElement;
      var isRecording = false;
      var recordingTimer;
      var recordingTime = 0;

      var canvas = $('.userspn-audio-canvas');
      if (canvas.length) {
        var canvasElement = canvas[0];
        var canvasContext = canvasElement.getContext('2d');
        var audioContext;
        var analyser;
        var dataArray;
        var animationFrame;
      }

      $(document).on('click', '.userspn-start-recording', function(e) {
        e.preventDefault();
        var btn = $(this);
        var wrapper = btn.closest('.userspn-audio-recorder-wrapper');
        var statusEl = wrapper.find('.userspn-recording-status');
        var timerEl = wrapper.find('.userspn-recording-time');
        var visualizer = wrapper.find('.userspn-audio-recorder-visualizer');
        var timer = wrapper.find('.userspn-audio-recorder-timer');

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
          if (typeof userspn_get_main_message === 'function') {
            userspn_get_main_message('Tu navegador no soporta grabación de audio.');
          }
          return;
        }

        navigator.mediaDevices.getUserMedia({ audio: true })
          .then(function(stream) {
            mediaRecorder = new MediaRecorder(stream);
            audioChunks = [];

            mediaRecorder.ondataavailable = function(event) {
              if (event.data.size > 0) {
                audioChunks.push(event.data);
              }
            };

            mediaRecorder.onstop = function() {
              audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
              var audioUrl = URL.createObjectURL(audioBlob);
              if (audioElement) {
                audioElement.pause();
                audioElement.src = '';
              }
              audioElement = new Audio(audioUrl);

              wrapper.find('.userspn-stop-recording').hide();
              wrapper.find('.userspn-play-audio').show();
              wrapper.find('.userspn-transcribe-audio').show();
              wrapper.find('.userspn-audio-transcription-controls').show();
              statusEl.text('Grabación completada');

              var reader = new FileReader();
              reader.onloadend = function() {
                var base64data = reader.result;
                wrapper.find('input[name$="_audio_data"]').val(base64data);
              };
              reader.readAsDataURL(audioBlob);

              stream.getTracks().forEach(function(track){ track.stop(); });
            };

            mediaRecorder.start();
            isRecording = true;

            btn.hide();
            wrapper.find('.userspn-stop-recording').show();
            wrapper.find('.userspn-play-audio').hide();
            wrapper.find('.userspn-stop-audio').hide();
            wrapper.find('.userspn-audio-transcription-controls').hide();
            statusEl.text('Grabando...');
            visualizer.show();
            timer.show();

            recordingTime = 0;
            recordingTimer = setInterval(function() {
              recordingTime++;
              var minutes = Math.floor(recordingTime / 60);
              var seconds = recordingTime % 60;
              timerEl.text((minutes < 10 ? '0' : '') + minutes + ':' + (seconds < 10 ? '0' : '') + seconds);
            }, 1000);

            if (canvas.length && !audioContext) {
              audioContext = new (window.AudioContext || window.webkitAudioContext)();
              analyser = audioContext.createAnalyser();
              var source = audioContext.createMediaStreamSource(stream);
              source.connect(analyser);
              analyser.fftSize = 256;
              dataArray = new Uint8Array(analyser.frequencyBinCount);

              function draw() {
                if (!isRecording) return;
                animationFrame = requestAnimationFrame(draw);
                analyser.getByteFrequencyData(dataArray);
                canvasContext.fillStyle = 'rgb(200, 200, 200)';
                canvasContext.fillRect(0, 0, canvasElement.width, canvasElement.height);
                var barWidth = (canvasElement.width / dataArray.length) * 2.5;
                var barHeight;
                var x = 0;
                for (var i = 0; i < dataArray.length; i++) {
                  barHeight = dataArray[i] / 2;
                  canvasContext.fillStyle = 'rgb(50, 50, ' + (barHeight + 100) + ')';
                  canvasContext.fillRect(x, canvasElement.height - barHeight, barWidth, barHeight);
                  x += barWidth + 1;
                }
              }
              draw();
            }
          })
          .catch(function(err) {
            if (typeof userspn_get_main_message === 'function') {
              userspn_get_main_message('Error al acceder al micrófono: ' + err.message);
            }
          });
      });

      $(document).on('click', '.userspn-stop-recording', function(e) {
        e.preventDefault();
        var wrapper = $(this).closest('.userspn-audio-recorder-wrapper');
        if (mediaRecorder && isRecording) {
          mediaRecorder.stop();
          isRecording = false;
          clearInterval(recordingTimer);
          if (animationFrame) {
            cancelAnimationFrame(animationFrame);
            animationFrame = null;
          }
          if (audioContext) {
            audioContext.close();
            audioContext = null;
          }
        }
      });

      $(document).on('click', '.userspn-play-audio', function(e) {
        e.preventDefault();
        var wrapper = $(this).closest('.userspn-audio-recorder-wrapper');
        if (audioElement) {
          audioElement.play();
          wrapper.find('.userspn-stop-audio').show();
          wrapper.find('.userspn-play-audio').hide();
        }
      });

      $(document).on('click', '.userspn-stop-audio', function(e) {
        e.preventDefault();
        var wrapper = $(this).closest('.userspn-audio-recorder-wrapper');
        if (audioElement) {
          audioElement.pause();
          audioElement.currentTime = 0;
          wrapper.find('.userspn-stop-audio').hide();
          wrapper.find('.userspn-play-audio').show();
        }
      });

      $(document).on('click', '.userspn-transcribe-audio', function(e) {
        e.preventDefault();
        var wrapper = $(this).closest('.userspn-audio-recorder-wrapper');
        var btn = $(this);
        var loading = wrapper.find('.userspn-audio-transcription-loading');
        var result = wrapper.find('.userspn-audio-transcription-result textarea');
        var error = wrapper.find('.userspn-audio-transcription-error');
        var success = wrapper.find('.userspn-audio-transcription-success');

        if (!audioBlob) {
          if (typeof userspn_get_main_message === 'function') {
            userspn_get_main_message('No hay audio grabado para transcribir.');
          }
          return;
        }

        btn.prop('disabled', true);
        loading.show();
        error.hide();
        success.hide();

        var formData = new FormData();
        formData.append('action', 'userspn_transcribe_audio');
        formData.append('audio_data', audioBlob);
        formData.append('audio_nonce', (typeof userspn_audio_recorder_vars !== 'undefined' ? userspn_audio_recorder_vars.ajax_nonce : ''));

        $.ajax({
          url: (typeof userspn_audio_recorder_vars !== 'undefined' ? userspn_audio_recorder_vars.ajax_url : ''),
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          success: function(response) {
            loading.hide();
            if (response && response.success) {
              result.val(response.data.transcription);
              success.find('.userspn-success-message').text((response.data && response.data.message) ? response.data.message : 'Transcripción completada');
              success.show();
            } else {
              error.find('.userspn-error-message').text((response && response.data) ? response.data : 'Error en la transcripción');
              error.show();
            }
            btn.prop('disabled', false);
          },
          error: function() {
            loading.hide();
            error.find('.userspn-error-message').text('Error al conectar con el servidor');
            error.show();
            btn.prop('disabled', false);
          }
        });
      });

      $(document).on('click', '.userspn-clear-transcription', function(e) {
        e.preventDefault();
        var wrapper = $(this).closest('.userspn-audio-recorder-wrapper');
        wrapper.find('.userspn-audio-transcription-result textarea').val('');
        wrapper.find('.userspn-audio-transcription-error').hide();
        wrapper.find('.userspn-audio-transcription-success').hide();
      });
    }

    // Tags functionality (añadido desde userspn-forms.js)
    if ($('.userspn-tags-wrapper').length) {
      $(document).on('keydown', '.userspn-tags-input', function(e) {
        var input = $(this);
        var wrapper = input.closest('.userspn-tags-wrapper');
        var display = wrapper.find('.userspn-tags-display');
        var hiddenInput = wrapper.find('input[name$="_tags_array"]');
        var tagsArray = [];
        try { tagsArray = JSON.parse(hiddenInput.val() || '[]'); } catch (err) { tagsArray = []; }

        if (e.key === 'Enter' || e.key === ',') {
          e.preventDefault();
          var tagValue = input.val().trim().replace(',', '');
          if (tagValue && tagsArray.indexOf(tagValue) === -1) {
            tagsArray.push(tagValue);
            display.append(
              '<span class="userspn-tag">' +
                escapeHtml(tagValue) +
                '<i class="material-icons-outlined userspn-tag-remove">close</i>' +
              '</span>'
            );
            hiddenInput.val(JSON.stringify(tagsArray));
            input.val('');
          }
        }
      });

      $(document).on('click', '.userspn-tag-remove', function(e) {
        e.preventDefault();
        var tagEl = $(this).closest('.userspn-tag');
        var wrapper = tagEl.closest('.userspn-tags-wrapper');
        var hiddenInput = wrapper.find('input[name$="_tags_array"]');
        var tagsArray = [];
        try { tagsArray = JSON.parse(hiddenInput.val() || '[]'); } catch (err) { tagsArray = []; }
        var tagText = tagEl.clone().children().remove().end().text().trim();
        tagsArray = tagsArray.filter(function(tag) { return tag !== tagText; });
        tagEl.remove();
        hiddenInput.val(JSON.stringify(tagsArray));
      });

      function escapeHtml(text) {
        var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
      }

      $(document).on('focus', '.userspn-tags-input', function() {
        var wrapper = $(this).closest('.userspn-tags-wrapper');
        var suggestions = wrapper.find('.userspn-tags-suggestions');
        // Placeholder para futuras sugerencias
      });
    }

    // CPT SEARCH FUNCTIONALITY
    if (typeof userspn_cpts !== 'undefined') {
      // Initialize search functionality for each CPT
      Object.keys(userspn_cpts).forEach(function(cptKey) {
        var cptName = userspn_cpts[cptKey];
        var searchToggleSelector = '.userspn-' + cptKey + '-search-toggle';
        var searchInputSelector = '.userspn-' + cptKey + '-search-input';
        var searchWrapperSelector = '.userspn-' + cptKey + '-search-wrapper';
        var listSelector = '.userspn-' + cptKey + '-list';
        var listWrapperSelector = '.userspn-' + cptKey + '-list-wrapper';
        var addNewSelector = '.userspn-add-new-cpt';

        // Only initialize if elements exist
        if ($(searchToggleSelector).length) {
          
          // Toggle search input visibility
          $(document).on('click', searchToggleSelector, function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            var searchToggle = $(this);
            var searchInput = searchToggle.siblings(searchInputSelector);
            var searchWrapper = searchToggle.closest(searchWrapperSelector);
            var list = searchToggle.closest(listSelector);
            var listWrapper = list.find(listWrapperSelector);
            var itemsList = listWrapper.find('ul');

            if (searchInput.hasClass('userspn-display-none')) {
              // Show search input
              searchInput.removeClass('userspn-display-none').focus();
              searchToggle.text('close');
              searchWrapper.addClass('userspn-search-active');
            } else {
              // Hide search input and clear filter
              searchInput.addClass('userspn-display-none').val('');
              searchToggle.text('search');
              searchWrapper.removeClass('userspn-search-active');
              
              // Show all items
              itemsList.find('li').show();
            }
          });

          // Filter items on keyup
          $(document).on('keyup', searchInputSelector, function(e) {
            var searchInput = $(this);
            var searchTerm = searchInput.val().toLowerCase().trim();
            var list = searchInput.closest(listSelector);
            var listWrapper = list.find(listWrapperSelector);
            var itemsList = listWrapper.find('ul');
            var items = itemsList.find('li:not(' + addNewSelector + ')');

            if (searchTerm === '') {
              // Show all items when search is empty
              items.show();
            } else {
              // Filter items based on title
              items.each(function() {
                var itemTitle = $(this).find('.userspn-display-inline-table a span').first().text().toLowerCase();
                if (itemTitle.includes(searchTerm)) {
                  $(this).show();
                } else {
                  $(this).hide();
                }
              });
            }

            // Always show the "Add new" item
            itemsList.find(addNewSelector).show();
          });

          // Close search on escape key
          $(document).on('keydown', searchInputSelector, function(e) {
            if (e.keyCode === 27) { // Escape key
              var searchInput = $(this);
              var searchToggle = searchInput.siblings(searchToggleSelector);
              var searchWrapper = searchInput.closest(searchWrapperSelector);
              var list = searchInput.closest(listSelector);
              var listWrapper = list.find(listWrapperSelector);
              var itemsList = listWrapper.find('ul');

              searchInput.addClass('userspn-display-none').val('');
              searchToggle.text('search');
              searchWrapper.removeClass('userspn-search-active');
              
              // Show all items
              itemsList.find('li').show();
            }
          });
                }
      });

      // Single unified click outside handler for all search wrappers
      $(document).on('click', function(e) {
        var clickedInsideSearch = false;
        var activeSearchInput = null;
        var activeSearchToggle = null;
        var activeSearchWrapper = null;
        var activeList = null;
        var activeListWrapper = null;
        var activeItemsList = null;

        // Check if clicked inside any search wrapper
        Object.keys(userspn_cpts).forEach(function(cptKey) {
          var searchWrapperSelector = '.userspn-' + cptKey + '-search-wrapper';
          var searchInputSelector = '.userspn-' + cptKey + '-search-input';
          var searchToggleSelector = '.userspn-' + cptKey + '-search-toggle';
          var listSelector = '.userspn-userspn_' + cptKey + '-list';
          var listWrapperSelector = '.userspn-userspn_' + cptKey + '-list-wrapper';

          if ($(e.target).closest(searchWrapperSelector).length) {
            clickedInsideSearch = true;
          }

          // Find active search input
          var searchInput = $(searchInputSelector + ':not(.userspn-display-none)');
          if (searchInput.length && !activeSearchInput) {
            activeSearchInput = searchInput;
            activeSearchToggle = searchInput.siblings(searchToggleSelector);
            activeSearchWrapper = searchInput.closest(searchWrapperSelector);
            activeList = searchInput.closest(listSelector);
            activeListWrapper = activeList.find(listWrapperSelector);
            activeItemsList = activeListWrapper.find('ul');
          }
        });

        // Close search if clicked outside
        if (!clickedInsideSearch && activeSearchInput) {
          activeSearchInput.addClass('userspn-display-none').val('');
          activeSearchToggle.text('search');
          activeSearchWrapper.removeClass('userspn-search-active');
          
          // Show all items
          activeItemsList.find('li').show();
        }
      });
    }
  });

  $(document).on('click', '.userspn-toggle', function(e) {
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();

    var userspn_toggle = $(this);

    if (userspn_toggle.find('i').length) {
      if (userspn_toggle.siblings('.userspn-toggle-content').is(':visible')) {
        userspn_toggle.find('i').text('add');
      } else {
        userspn_toggle.find('i').text('clear');
      }
    }

    userspn_toggle.siblings('.userspn-toggle-content').fadeToggle();
  });
})(jQuery);
