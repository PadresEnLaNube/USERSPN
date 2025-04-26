(function($) {
	'use strict';

  $(document).ready(function() {
    $(document).on('click', '.userspn-upload-files-btn', function(e) {
      e.preventDefault();
      var upload_files_btn = $(this);
      
      upload_files_btn.siblings('.userspn-waiting').fadeIn('slow');
      
      var ajaxurl = userspn_ajax.ajax_url;
      var formData = new FormData();
      
      formData.append('action', 'userspn_ajax');
      formData.append('userspn_ajax_type', 'userspn_profile_image');
      formData.append('userspn_uploaded_file', $('input[type=file]')[0].files[0]);
      formData.append('userspn_related_user_id', upload_files_btn.attr('data-userspn-user-id'));

      $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: formData,
        cache: false,
        processData: false,
        contentType: false,
        success: function(response) {
          console.log(response);
          if (response == 'userspn_profile_image_error') {
            userspn_get_main_message(userspn_i18n.an_error_has_occurred);
          }else{
            userspn_get_main_message(userspn_i18n.file_uploaded);
            document.location.reload(true);
          }
        }
      });
    });
  });
})(jQuery);