(function($) {
  'use strict';

  const USERSPN_ProfileProgress = {
    init: function() {
      this.calculateProgress();
      this.bindEvents();
    },

    bindEvents: function() {
      // Recalculate progress when profile is updated
      $(document).on('userspn_profile_updated', () => {
        this.calculateProgress();
      });
    },

    calculateProgress: function() {
      const user_id = $('.userspn-profile-wrapper').data('user-id');
      if (!user_id) return;

      $.ajax({
        url: userspn_ajax.ajax_url,
        type: 'POST',
        data: {
          action: 'userspn_ajax',
          userspn_ajax_nonce: userspn_ajax.userspn_ajax_nonce,
          userspn_ajax_type: 'userspn_profile_progress',
          user_id: user_id
        },
        success: (response) => {
          if (response.success) {
            this.updateProgressBar(response.data);
          }
        }
      });
    },

    updateProgressBar: function(data) {
      const { percentage, fields } = data;
      
      // Update progress bar
      $('#userspn-profile-progress-bar').css('width', percentage + '%');
      $('#userspn-profile-progress-percentage').text(percentage + '%');

      // Update fields list
      const fieldsContainer = $('#userspn-profile-progress-fields');
      fieldsContainer.empty();

      fields.forEach(field => {
        const fieldHtml = `
          <div class="field-item ${field.completed ? 'completed' : ''}">
            <span class="field-icon material-icons-outlined">
              ${field.completed ? 'check' : 'radio_button_unchecked'}
            </span>
            <span class="field-name">${field.name}</span>
            <span class="field-status">
              ${field.completed ? userspn_i18n.completed : userspn_i18n.pending}
            </span>
          </div>
        `;
        fieldsContainer.append(fieldHtml);
      });
    }
  };

  $(document).ready(function() {
    USERSPN_ProfileProgress.init();
  });

})(jQuery); 