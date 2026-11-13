/**
 * Social Login functionality
 * Handles Google, Meta (Facebook), X, GitHub, and Apple Sign-In
 */

(function ($) {
  'use strict';

  var DEBUG = true;

  function debug(/* ...args */) {
    if (!DEBUG) return;
    var args = Array.prototype.slice.call(arguments);
    args.unshift('[USERSPN Social Login]');
    console.log.apply(console, args);
  }

  $(document).ready(function () {
    debug('Initializing social login...');
    debug('userspn_ajax available:', typeof userspn_ajax !== 'undefined');
    debug('userspn_social_custom_classes available:', typeof userspn_social_custom_classes !== 'undefined');
    if (typeof userspn_social_custom_classes !== 'undefined') {
      debug('Custom classes config:', JSON.stringify(userspn_social_custom_classes));
    }
    initSocialLogin();
  });

  /**
   * Initialize all social login buttons
   */
  function initSocialLogin() {
    // Handle all social login buttons (both icon and full buttons)
    $(document).on('click', '.userspn-social-btn, .userspn-social-icon-btn', function (e) {
      e.preventDefault();
      var provider = $(this).data('provider');
      debug('Built-in button clicked, provider:', provider);
      if (provider) {
        initiateSocialOAuth(provider);
      }
    });

    // Handle custom CSS class triggers for each provider
    var providers = ['google', 'facebook', 'x', 'github', 'apple'];
    if (typeof userspn_social_custom_classes !== 'undefined') {
      for (var i = 0; i < providers.length; i++) {
        (function (provider) {
          var customClass = userspn_social_custom_classes[provider];
          if (customClass) {
            debug('Binding custom class ".' + customClass + '" to provider "' + provider + '"');

            var selector = '.' + customClass;
            var matchCount = $(selector).length;
            debug('Elements found for "' + selector + '":', matchCount);

            $(document).on('click', selector, function (e) {
              e.preventDefault();
              e.stopPropagation();
              debug('Custom class click on ".' + customClass + '" -> provider "' + provider + '"', 'target:', e.target.tagName, 'currentTarget:', e.currentTarget.tagName);
              initiateSocialOAuth(provider);
            });

            // Also bind clicks on child elements (Gutenberg: <div class="custom-class"><a>text</a></div>)
            $(document).on('click', selector + ' a, ' + selector + ' button, ' + selector + ' span', function (e) {
              e.preventDefault();
              e.stopPropagation();
              debug('Custom class CHILD click inside ".' + customClass + '" -> provider "' + provider + '"', 'target:', e.target.tagName);
              initiateSocialOAuth(provider);
            });
          }
        })(providers[i]);
      }
    } else {
      debug('No custom classes defined (userspn_social_custom_classes is undefined)');
    }

    // Check for OAuth callback
    checkForOAuthCallback();

    debug('Initialization complete.');
  }

  /**
   * Initiate OAuth flow for any provider
   */
  function initiateSocialOAuth(provider) {
    debug('Initiating OAuth for provider:', provider);

    if (typeof userspn_ajax === 'undefined') {
      debug('ERROR: userspn_ajax is not defined. Script may not be properly localized.');
      showMessage(provider, 'error', 'Configuration error. Please check plugin setup.');
      return;
    }

    var requestData = {
      action: 'userspn_ajax_nopriv',
      userspn_ajax_nopriv_type: 'userspn_' + provider + '_auth_url',
      userspn_ajax_nopriv_nonce: userspn_ajax.userspn_ajax_nonce,
    };

    debug('AJAX request:', userspn_ajax.ajax_url, requestData);

    // Get auth URL from backend
    $.ajax({
      url: userspn_ajax.ajax_url,
      type: 'POST',
      data: requestData,
      success: function (response) {
        debug('AJAX raw response:', response);

        if (typeof response === 'string') {
          try {
            response = JSON.parse(response);
          } catch (e) {
            debug('ERROR: Could not parse response as JSON:', response);
            showMessage(provider, 'error', 'Error connecting. Please try again.');
            return;
          }
        }

        debug('AJAX parsed response:', JSON.stringify(response));

        if (response.success && response.auth_url) {
          debug('Redirecting to auth URL:', response.auth_url);
          window.location.href = response.auth_url;
        } else if (response.error_content) {
          debug('Server error:', response.error_key, response.error_content);
          showMessage(provider, 'error', response.error_content);
        } else {
          debug('Unexpected response structure:', JSON.stringify(response));
          showMessage(provider, 'error', 'Error connecting. Please try again.');
        }
      },
      error: function (xhr, status, error) {
        debug('AJAX error:', status, error, 'Status code:', xhr.status, 'Response:', xhr.responseText);
        showMessage(provider, 'error', 'Connection error. Please try again.');
      },
    });
  }

  /**
   * Check for OAuth callback in URL
   */
  function checkForOAuthCallback() {
    var urlParams = new URLSearchParams(window.location.search);

    var socialError = urlParams.get('userspn_social_error');
    var socialMessage = urlParams.get('userspn_social_message');

    if (socialError && socialMessage) {
      debug('OAuth callback error:', socialError, decodeURIComponent(socialMessage));
      showMessage('social', 'error', decodeURIComponent(socialMessage));
      cleanUrlParams();
    }
  }

  /**
   * Clean OAuth parameters from URL
   */
  function cleanUrlParams() {
    var url = new URL(window.location);
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
    $('.userspn-social-message').remove();

    var messageClass = type === 'success' ? 'userspn-message-success' : (type === 'info' ? 'userspn-message-info' : 'userspn-message-error');
    var bgColor = type === 'success' ? '#d4edda' : (type === 'info' ? '#d1ecf1' : '#f8d7da');
    var textColor = type === 'success' ? '#155724' : (type === 'info' ? '#0c5460' : '#721c24');
    var borderColor = type === 'success' ? '#c3e6cb' : (type === 'info' ? '#bee5eb' : '#f5c6cb');

    var messageHtml = '<div class="userspn-social-message ' + messageClass + '" style="padding: 10px; margin: 10px 0; border-radius: 4px; text-align: center; background: ' + bgColor + '; color: ' + textColor + '; border: 1px solid ' + borderColor + ';">' + message + '</div>';

    if ($('#userspn-login').length) {
      $('#userspn-login').prepend(messageHtml);
    } else {
      $('body').prepend(messageHtml);
    }

    if (type !== 'info') {
      setTimeout(function () {
        $('.userspn-social-message').fadeOut(function () {
          $(this).remove();
        });
      }, 5000);
    }
  }
})(jQuery);
