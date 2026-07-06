(function($) {
	'use strict';

  $(document).ready(function() {
    $(document).on('click', '.userspn-upload-files-btn', function(e) {
      e.preventDefault();
      var upload_files_btn = $(this);
      
      // Disable button and show loading
      upload_files_btn.prop('disabled', true);
      
      // Find and show the loader
      var loader = upload_files_btn.siblings('.userspn-waiting');
      if (loader.length > 0) {
        loader.fadeIn('slow');
      } else {
        // Try alternative selectors
        var altLoader = upload_files_btn.parent().find('.userspn-waiting');
        if (altLoader.length > 0) {
          altLoader.fadeIn('slow');
        }
      }
      
      var ajaxurl = userspn_ajax.ajax_url;
      var formData = new FormData();
      
      // Check if file is selected
      var fileInput = $('input[type=file]')[0];
      if (!fileInput.files || fileInput.files.length === 0) {
        userspn_get_main_message(userspn_i18n.no_file_selected);
        return;
      }
      
      formData.append('action', 'userspn_ajax');
      formData.append('userspn_ajax_type', 'userspn_profile_image');
      formData.append('userspn_ajax_nonce', userspn_ajax.userspn_ajax_nonce);
      formData.append('userspn_uploaded_file', fileInput.files[0]);
      formData.append('userspn_related_user_id', upload_files_btn.attr('data-userspn-user-id'));

      $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: formData,
        cache: false,
        processData: false,
        contentType: false,
        success: function(response) {
          if (response == 'userspn_profile_image_error') {
            userspn_get_main_message(userspn_i18n.an_error_has_occurred);
          }else{
            userspn_get_main_message('Tu archivo se ha subido');
            // Clear the file input
            $('input[type=file]')[0].value = '';
            // Update avatar without page reload
            userspn_update_avatar_display(upload_files_btn.attr('data-userspn-user-id'));
          }
        },
        error: function(xhr, status, error) {
          userspn_get_main_message(userspn_i18n.an_error_has_occurred);
        },
        complete: function() {
          // Re-enable button and hide loading
          upload_files_btn.prop('disabled', false);
          
          // Find and hide the loader
          var loader = upload_files_btn.siblings('.userspn-waiting');
          if (loader.length > 0) {
            loader.fadeOut('slow');
          } else {
            // Try alternative selectors
            var altLoader = upload_files_btn.parent().find('.userspn-waiting');
            if (altLoader.length > 0) {
              altLoader.fadeOut('slow');
            }
          }
        }
      });
    });
    
    // Handle remove avatar button
    $(document).on('click', '.userspn-remove-avatar-btn', function(e) {
      e.preventDefault();
      var remove_btn = $(this);
      var user_id = remove_btn.attr('data-userspn-user-id');
      
      // Confirm removal
      if (!confirm(userspn_i18n.confirm_remove_avatar)) {
        return;
      }
      
      // Disable button and show loading
      remove_btn.prop('disabled', true);
      
      // Find and show the loader
      var loader = remove_btn.siblings('.userspn-waiting');
      if (loader.length > 0) {
        loader.fadeIn('slow');
      } else {
        var altLoader = remove_btn.parent().find('.userspn-waiting');
        if (altLoader.length > 0) {
          altLoader.fadeIn('slow');
        }
      }
      
      var ajaxurl = userspn_ajax.ajax_url;
      
      $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
          action: 'userspn_ajax',
          userspn_ajax_type: 'userspn_remove_avatar',
          userspn_ajax_nonce: userspn_ajax.userspn_ajax_nonce,
          userspn_user_id: user_id
        },
        success: function(response) {
          // Handle different response formats
          var success = false;
          if (typeof response === 'string') {
            try {
              var parsed_response = JSON.parse(response);
              success = parsed_response && parsed_response.success;
            } catch (e) {
              // Silent error handling
            }
          } else if (response && response.success) {
            success = true;
          }
          
          if (success) {
            userspn_get_main_message('Avatar eliminado');
            
            // Update avatar display to show blank avatar
            userspn_update_avatar_display(user_id);
            
            // Hide the remove button since there's no avatar anymore
            remove_btn.fadeOut('slow');
          } else {
            userspn_get_main_message('Error al eliminar el avatar');
          }
        },
        error: function(xhr, status, error) {
          userspn_get_main_message('Error al eliminar el avatar');
        },
        complete: function() {
          // Re-enable button and hide loading
          remove_btn.prop('disabled', false);
          
          // Find and hide the loader
          var loader = remove_btn.siblings('.userspn-waiting');
          if (loader.length > 0) {
            loader.fadeOut('slow');
          } else {
            var altLoader = remove_btn.parent().find('.userspn-waiting');
            if (altLoader.length > 0) {
              altLoader.fadeOut('slow');
            }
          }
        }
      });
    });
  });

  // Function to update avatar display without page reload
  function userspn_update_avatar_display(user_id) {
    var ajaxurl = userspn_ajax.ajax_url;
    
    console.log('Updating avatar for user:', user_id);
    
    $.ajax({
      url: ajaxurl,
      type: 'POST',
      data: {
        action: 'userspn_ajax',
        userspn_ajax_type: 'userspn_get_avatar_html',
        userspn_ajax_nonce: userspn_ajax.userspn_ajax_nonce,
        userspn_user_id: user_id
      },
      success: function(response) {
        console.log('=== AVATAR UPDATE DEBUG START ===');
        console.log('Raw response:', response);
        console.log('Response type:', typeof response);
        
        var avatar_html = null;
        
        // Handle different response formats
        if (typeof response === 'string') {
          console.log('Response is a string, parsing as JSON...');
          try {
            var parsed_response = JSON.parse(response);
            console.log('Parsed response:', parsed_response);
            console.log('Parsed response type:', typeof parsed_response);
            console.log('Parsed response has html property:', parsed_response.hasOwnProperty('html'));
            console.log('Parsed response.html value:', parsed_response.html);
            console.log('Parsed response.html type:', typeof parsed_response.html);
            
            if (parsed_response && parsed_response.html) {
              avatar_html = parsed_response.html;
              console.log('Extracted HTML from parsed response:', avatar_html);
              console.log('Avatar HTML length:', avatar_html.length);
            } else {
              console.log('No HTML found in parsed response');
              console.log('Available properties:', Object.keys(parsed_response || {}));
            }
          } catch (e) {
            console.log('Failed to parse response as JSON:', e);
            console.log('Treating response as direct HTML string');
            avatar_html = response;
          }
        } else if (response && response.html) {
          console.log('Response is an object with html property');
          avatar_html = response.html;
        } else {
          console.log('Response format not recognized');
        }
        
        console.log('Final avatar_html value:', avatar_html);
        console.log('Final avatar_html type:', typeof avatar_html);
        console.log('Final avatar_html truthy:', !!avatar_html);
        
        if (avatar_html) {
          console.log('Avatar HTML to use:', avatar_html);
          console.log('HTML length:', avatar_html.length);
          console.log('HTML type:', typeof avatar_html);
          
          // Deep analysis of current page elements
          console.log('=== ANALYZING CURRENT PAGE ELEMENTS ===');
          
          // Check all userspn-avatar elements
          var allAvatars = $('.userspn-avatar');
          console.log('Total .userspn-avatar elements found:', allAvatars.length);
          allAvatars.each(function(index) {
            var $avatar = $(this);
            console.log('Avatar ' + index + ':', {
              element: this,
              classes: this.className,
              hasImage: $avatar.find('img').length > 0,
              imageSrc: $avatar.find('img').attr('src'),
              parentElement: this.parentElement,
              parentClasses: this.parentElement ? this.parentElement.className : 'no parent'
            });
          });
          
          // Check specific containers
          var tabImageContainer = $('#userspn-tab-image .userspn-text-align-center');
          console.log('Tab image container found:', tabImageContainer.length);
          if (tabImageContainer.length > 0) {
            console.log('Tab container HTML:', tabImageContainer.html());
            var avatarInTab = tabImageContainer.find('.userspn-avatar');
            console.log('Avatar in tab found:', avatarInTab.length);
            if (avatarInTab.length > 0) {
              console.log('Avatar in tab HTML before:', avatarInTab.html());
              console.log('Replacing avatar in tab...');
              avatarInTab.replaceWith(avatar_html);
              console.log('Avatar in tab HTML after:', tabImageContainer.find('.userspn-avatar').html());
            } else {
              console.log('No avatar found in tab, replacing entire container...');
              tabImageContainer.html(avatar_html);
            }
          }
          
          var popupBtn = $('.userspn-profile-popup-btn');
          console.log('Popup button found:', popupBtn.length);
          if (popupBtn.length > 0) {
            console.log('Popup button HTML:', popupBtn.html());
            var avatarInPopup = popupBtn.find('.userspn-avatar');
            console.log('Avatar in popup found:', avatarInPopup.length);
            if (avatarInPopup.length > 0) {
              console.log('Avatar in popup HTML before:', avatarInPopup.html());
              console.log('Replacing avatar in popup...');
              avatarInPopup.replaceWith(avatar_html);
              console.log('Avatar in popup HTML after:', popupBtn.find('.userspn-avatar').html());
            } else {
              console.log('No avatar found in popup, replacing entire button...');
              popupBtn.html(avatar_html);
            }
          }
          
          // Force update all avatars
          console.log('=== FORCE UPDATING ALL AVATARS ===');
          forceUpdateAllAvatars(user_id, avatar_html);
          
          // Check if updates worked
          console.log('=== CHECKING IF UPDATES WORKED ===');
          var updatedAvatars = $('.userspn-avatar');
          console.log('Total avatars after update:', updatedAvatars.length);
          updatedAvatars.each(function(index) {
            var $avatar = $(this);
            console.log('Updated avatar ' + index + ':', {
              element: this,
              classes: this.className,
              hasImage: $avatar.find('img').length > 0,
              imageSrc: $avatar.find('img').attr('src')
            });
          });
          
          console.log('=== AVATAR UPDATE DEBUG END ===');
        } else {
          console.log('ERROR: No HTML found in response');
          console.log('Response structure:', Object.keys(response || {}));
        }
      },
      error: function(xhr, status, error) {
        console.log('Error updating avatar display:', error);
        console.log('Response:', xhr.responseText);
      }
    });
  }

  // Force update all avatars for a specific user
  function forceUpdateAllAvatars(user_id, avatar_html) {
    console.log('=== FORCE UPDATE FUNCTION START ===');
    console.log('User ID:', user_id);
    console.log('Avatar HTML to replace with:', avatar_html);
    
    var updatedCount = 0;
    
    // Update all .userspn-avatar elements on the page
    var allAvatars = $('.userspn-avatar');
    console.log('Found', allAvatars.length, 'avatar elements to update');
    
    allAvatars.each(function(index) {
      var $avatar = $(this);
      console.log('Processing avatar', index + 1, 'of', allAvatars.length);
      console.log('Avatar element:', this);
      console.log('Avatar classes:', this.className);
      console.log('Avatar HTML before:', $avatar.html());
      
      // Check if this avatar has an image or is blank
      var hasImage = $avatar.find('img').length > 0;
      var isBlank = $avatar.hasClass('userspn-avatar-blank') || $avatar.hasClass('userspn-avatar-empty');
      
      console.log('Avatar has image:', hasImage, 'is blank:', isBlank);
      
      // Replace the avatar element
      console.log('Replacing avatar element...');
      $avatar.replaceWith(avatar_html);
      
      // Check if replacement worked
      var newAvatar = $('.userspn-avatar').eq(index);
      console.log('New avatar HTML after replacement:', newAvatar.html());
      
      updatedCount++;
      console.log('Avatar', index + 1, 'updated successfully');
    });
    
    console.log('Force updated', updatedCount, 'avatar elements');
    console.log('=== FORCE UPDATE FUNCTION END ===');
  }

  // Additional function to update specific avatar elements
  function updateSpecificAvatarElements(avatar_html) {
    console.log('Updating specific avatar elements...');
    
    // Update avatars in profile popup buttons
    $('.userspn-profile-popup-btn .userspn-avatar').each(function() {
      console.log('Updating popup button avatar:', this);
      $(this).replaceWith(avatar_html);
    });
    
    // Update avatars in profile image tab
    $('#userspn-tab-image .userspn-avatar').each(function() {
      console.log('Updating tab image avatar:', this);
      $(this).replaceWith(avatar_html);
    });
    
    // Update any avatar with tooltip
    $('.userspn-avatar.userspn-tooltip').each(function() {
      console.log('Updating tooltip avatar:', this);
      $(this).replaceWith(avatar_html);
    });
    
    console.log('Specific avatar elements updated');
  }
})(jQuery);