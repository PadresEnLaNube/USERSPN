/**
 * Social Login functionality
 * Handles Google, Facebook, GitHub, and Apple Sign-In
 */

(function ($) {
  'use strict';

  $(document).ready(function () {
    initSocialLogin();
  });

  /**
   * Initialize all social login buttons
   */
  function initSocialLogin() {
    // Handle all social login buttons (both icon and full buttons)
    $(document).on('click', '.userspn-social-btn, .userspn-social-icon-btn', function (e) {
      e.preventDefault();
      const provider = $(this).data('provider');
      if (provider) {
        initiateSocialOAuth(provider);
      }
    });

    // Check for OAuth callback
    checkForOAuthCallback();
  }

  /**
   * Initiate OAuth flow for any provider
   */
  function initiateSocialOAuth(provider) {
    // Get auth URL from backend
    $.ajax({
      url: userspn_ajax.ajax_url,
      type: 'POST',
      data: {
        action: 'userspn_ajax_nopriv',
        userspn_ajax_nopriv_type: 'userspn_' + provider + '_auth_url',
        userspn_ajax_nopriv_nonce: userspn_ajax.userspn_ajax_nonce,
      },
      success: function (response) {
        if (typeof response === 'string') {
          try {
            response = JSON.parse(response);
          } catch (e) {
            showMessage(provider, 'error', 'Error connecting. Please try again.');
            return;
          }
        }

        if (response.success && response.auth_url) {
          // Redirect to OAuth provider
          window.location.href = response.auth_url;
        } else if (response.error_content) {
          showMessage(provider, 'error', response.error_content);
        } else {
          showMessage(provider, 'error', 'Error connecting. Please try again.');
        }
      },
      error: function () {
        showMessage(provider, 'error', 'Connection error. Please try again.');
      },
    });
  }

  /**
   * Check for OAuth callback in URL
   */
  function checkForOAuthCallback() {
    const urlParams = new URLSearchParams(window.location.search);
    
    // Check for error messages from redirect
    const socialError = urlParams.get('userspn_social_error');
    const socialMessage = urlParams.get('userspn_social_message');

    if (socialError && socialMessage) {
      showMessage('social', 'error', decodeURIComponent(socialMessage));
      cleanUrlParams();
    }
  }

  /**
   * Clean OAuth parameters from URL
   */
  function cleanUrlParams() {
    const url = new URL(window.location);
    url.searchParams.delete('code');
    url.searchParams.delete('state');
    url.searchParams.delete('scope');
    url.searchParams.delete('authuser');
    url.searchParams.delete('prompt');
    url.searchParams.delete('error');
    url.searchParams.delete('error_description');
    url.searchParams.delete('error_reason');
    url.searchParams.delete('userspn_social_error');
    url.searchParams.delete('userspn_social_message');
    window.history.replaceState({}, document.title, url);
  }

  /**
   * Show message to user
   */
  function showMessage(provider, type, message) {
    // Remove previous messages
    $('.userspn-social-message').remove();

    const messageClass = type === 'success' ? 'userspn-message-success' : (type === 'info' ? 'userspn-message-info' : 'userspn-message-error');
    const bgColor = type === 'success' ? '#d4edda' : (type === 'info' ? '#d1ecf1' : '#f8d7da');
    const textColor = type === 'success' ? '#155724' : (type === 'info' ? '#0c5460' : '#721c24');
    const borderColor = type === 'success' ? '#c3e6cb' : (type === 'info' ? '#bee5eb' : '#f5c6cb');

    const messageHtml = '<div class="userspn-social-message ' + messageClass + '" style="padding: 10px; margin: 10px 0; border-radius: 4px; text-align: center; background: ' + bgColor + '; color: ' + textColor + '; border: 1px solid ' + borderColor + ';">' + message + '</div>';

    // Insert message
    if ($('#userspn-login').length) {
      $('#userspn-login').prepend(messageHtml);
    } else {
      $('body').prepend(messageHtml);
    }

    // Auto-remove after 5 seconds
    if (type !== 'info') {
      setTimeout(function () {
        $('.userspn-social-message').fadeOut(function () {
          $(this).remove();
        });
      }, 5000);
    }
  }
})(jQuery);
