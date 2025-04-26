(function($) {
	'use strict';

  $(document).ready(function() {
    $(document).on('click', '.userspn-upload-private-files-btn', function(e) {
      e.preventDefault();
      var userspn_btn = $(this);
      userspn_btn.addClass('userspn-link-disabled').siblings('.userspn-waiting').removeClass('userspn-display-none-soft');

      var ajax_url = userspn_ajax.ajax_url;
      var formData = new FormData();
      formData.append('action', 'userspn_ajax');
      formData.append('userspn_ajax_type', 'userspn_user_files');
      formData.append('userspn_uploaded_file', $('input#userspn-user-file-private[type=file]')[0].files[0]);
      formData.append('post_id', userspn_btn.attr('data-userspn-post-id'));
      formData.append('user_id', userspn_btn.attr('data-userspn-user-id'));

      $.ajax({
        url: ajax_url,
        type: 'POST',
        data: formData,
        cache: false,
        processData: false,
        contentType: false,
        success:function(data) {
          userspn_get_main_message($.parseJSON(data)['response']);
          $('#userspn-user-file-private').val('');

          if (!data['error_key']) {
            $('.userspn-file-private-upload-list').append($.parseJSON(data)['html']);
          }
        },
        error:function(data) {
          userspn_get_main_message(userspn_i18n.an_error_has_occurred);
        },
      });

      userspn_btn.removeClass('userspn-link-disabled').siblings('.userspn-waiting').addClass('userspn-display-none-soft')
    });

    $(document).on('click', '.userspn-file-private-remove-btn', function(e) {
      e.preventDefault();
      var userspn_btn = $(this);
      userspn_btn.addClass('userspn-link-disabled').siblings('.userspn-waiting').removeClass('userspn-display-none-soft');

      var ajax_url = userspn_ajax.ajax_url;
      var data = {
        action: 'userspn_ajax',
        userspn_ajax_type: 'userspn_file_private_remove',
        file_id: userspn_btn.closest('.userspn-file-private').attr('data-userspn-file-id'),
        user_id: userspn_btn.closest('.userspn-file-private').attr('data-userspn-user-id'),
      };

      $.post(ajax_url, data, function(response) {
        console.log('data');console.log(data);console.log('response');console.log(response);
        if (response == 'userspn_file_private_remove_error') {
          userspn_get_main_message(userspn_i18n.an_error_has_occurred);
        }else {
          userspn_btn.closest('li.userspn-file-private[data-userspn-file-id="' + data.file_id + '"]').fadeOut('slow');

          userspn_get_main_message(userspn_i18n.file_removed);
        }

        userspn_btn.removeClass('userspn-link-disabled').siblings('.userspn-waiting').fadeOut('fast');
      });
    });
  });
})(jQuery);