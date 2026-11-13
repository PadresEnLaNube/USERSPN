/**
 * Sequential Login Script - USERSPN
 *
 * Handles two-step sequential authentication: Email check -> Password prompt -> Sign In.
 *
 * @package USERSPN
 */

(function ($) {
  'use strict';

  /**
   * Clean URL parameters before redirecting
   */
  function cleanUrlParams(url) {
    try {
      var urlObj = new URL(url, window.location.origin);
      var paramsToRemove = [
        'hostpn_action',
        'hostpn_popup',
        'hostpn_tab',
        'userspn_action',
        'userspn_popup',
        'userspn_tab',
        'hostpn_btn_id',
        'userspn_btn_id'
      ];

      paramsToRemove.forEach(function (param) {
        urlObj.searchParams.delete(param);
      });

      return urlObj.toString();
    } catch (error) {
      return url;
    }
  }

  /**
   * Display alert message within a sequential login container
   */
  function showAlert($container, type, message) {
    var alertClass = 'userspn-alert-error';
    if (type === 'success') {
      alertClass = 'userspn-alert-success';
    } else if (type === 'warning') {
      alertClass = 'userspn-alert-warning';
    }

    var $alert = $container.find('.userspn-seq-alert');
    $alert
      .removeClass('userspn-display-none userspn-alert-error userspn-alert-success userspn-alert-warning')
      .addClass(alertClass)
      .html(message)
      .hide()
      .fadeIn(200);
  }

  /**
   * Clear alert message
   */
  function clearAlert($container) {
    var $alert = $container.find('.userspn-seq-alert');
    $alert.addClass('userspn-display-none').html('').hide();
  }

  /**
   * Get i18n text with fallback
   */
  function getI18n(key, fallback) {
    if (typeof userspn_seq_login_i18n !== 'undefined' && userspn_seq_login_i18n[key]) {
      return userspn_seq_login_i18n[key];
    }
    return fallback;
  }

  /**
   * Get AJAX config
   */
  function getAjaxConfig() {
    var ajaxUrl = '/wp-admin/admin-ajax.php';
    var nonce = '';

    if (typeof userspn_ajax !== 'undefined') {
      ajaxUrl = userspn_ajax.ajax_url || ajaxUrl;
      nonce = userspn_ajax.userspn_ajax_nonce || '';
    }

    return {
      url: ajaxUrl,
      nonce: nonce
    };
  }

  /**
   * Handle Step 1: Check Email / Identifier
   */
  function handleStep1Next($container) {
    var $emailInput = $container.find('.userspn-seq-email-input');
    var identifier = $.trim($emailInput.val());

    if (!identifier) {
      showAlert($container, 'error', getI18n('empty_email', 'Please enter your email or username.'));
      $emailInput.focus();
      return;
    }

    clearAlert($container);

    var $btn = $container.find('.userspn-seq-btn-next');
    var $waiting = $btn.find('.userspn-waiting');
    var $btnText = $btn.find('.userspn-seq-btn-text');

    $btn.prop('disabled', true);
    $waiting.removeClass('userspn-display-none');

    var ajaxConfig = getAjaxConfig();

    $.ajax({
      url: ajaxConfig.url,
      type: 'POST',
      dataType: 'json',
      data: {
        action: 'userspn_ajax_nopriv',
        userspn_ajax_nopriv_type: 'userspn_sequential_login_check_email',
        userspn_ajax_nopriv_nonce: ajaxConfig.nonce,
        userspn_identifier: identifier
      },
      success: function (response) {
        $btn.prop('disabled', false);
        $waiting.addClass('userspn-display-none');

        if (typeof response === 'string') {
          try {
            response = JSON.parse(response);
          } catch (e) {
            showAlert($container, 'error', getI18n('generic_error', 'An error occurred. Please try again.'));
            return;
          }
        }

        if (response.success) {
          // Store resolved user identifier
          var displayIdentifier = response.user_email || response.user_login || identifier;
          $container.data('resolved-identifier', displayIdentifier);
          $container.find('.userspn-seq-current-identifier').text(displayIdentifier);

          // Transition to Step 2
          var $step1 = $container.find('.userspn-seq-step-email');
          var $step2 = $container.find('.userspn-seq-step-password');

          $step1.fadeOut(200, function () {
            $step1.addClass('userspn-display-none');
            $step2.removeClass('userspn-display-none').hide().fadeIn(250, function () {
              var $passInput = $container.find('.userspn-seq-password-input');
              $passInput.focus();
            });
          });
        } else {
          var errorMsg = response.error_content || getI18n('generic_error', 'An error occurred. Please try again.');
          showAlert($container, 'error', errorMsg);
          $emailInput.focus();
        }
      },
      error: function () {
        $btn.prop('disabled', false);
        $waiting.addClass('userspn-display-none');
        showAlert($container, 'error', getI18n('generic_error', 'An error occurred. Please try again.'));
        $emailInput.focus();
      }
    });
  }

  /**
   * Handle Back to Step 1 (Change Account)
   */
  function handleBackToStep1($container) {
    clearAlert($container);

    var $step1 = $container.find('.userspn-seq-step-email');
    var $step2 = $container.find('.userspn-seq-step-password');
    var $passInput = $container.find('.userspn-seq-password-input');
    var $emailInput = $container.find('.userspn-seq-email-input');

    $passInput.val('');

    $step2.fadeOut(200, function () {
      $step2.addClass('userspn-display-none');
      $step1.removeClass('userspn-display-none').hide().fadeIn(250, function () {
        $emailInput.focus().select();
      });
    });
  }

  /**
   * Handle Step 2: Submit Login
   */
  function handleStep2Submit($container) {
    var $emailInput = $container.find('.userspn-seq-email-input');
    var $passInput = $container.find('.userspn-seq-password-input');
    var $rememberInput = $container.find('.userspn-seq-remember-checkbox');

    var identifier = $container.data('resolved-identifier') || $.trim($emailInput.val());
    var password = $passInput.val();
    var remember = $rememberInput.length && $rememberInput.is(':checked') ? 1 : 0;
    var redirectTo = $container.data('redirect-to') || '';

    if (!password) {
      showAlert($container, 'error', getI18n('empty_password', 'Please enter your password.'));
      $passInput.focus();
      return;
    }

    clearAlert($container);

    var $btn = $container.find('.userspn-seq-btn-login');
    var $waiting = $btn.find('.userspn-waiting');

    $btn.prop('disabled', true);
    $waiting.removeClass('userspn-display-none');

    var ajaxConfig = getAjaxConfig();

    $.ajax({
      url: ajaxConfig.url,
      type: 'POST',
      dataType: 'json',
      data: {
        action: 'userspn_ajax_nopriv',
        userspn_ajax_nopriv_type: 'userspn_sequential_login_submit',
        userspn_ajax_nopriv_nonce: ajaxConfig.nonce,
        userspn_identifier: identifier,
        userspn_password: password,
        userspn_remember: remember,
        redirect_to: redirectTo
      },
      success: function (response) {
        if (typeof response === 'string') {
          try {
            response = JSON.parse(response);
          } catch (e) {
            $btn.prop('disabled', false);
            $waiting.addClass('userspn-display-none');
            showAlert($container, 'error', getI18n('generic_error', 'An error occurred. Please try again.'));
            return;
          }
        }

        if (response.success) {
          showAlert($container, 'success', response.message || 'Login successful. Redirecting...');

          var destination = response.redirect_url || redirectTo || window.location.href;
          destination = cleanUrlParams(destination);

          setTimeout(function () {
            window.location.href = destination;
          }, 350);
        } else {
          $btn.prop('disabled', false);
          $waiting.addClass('userspn-display-none');

          var errorMsg = response.error_content || getI18n('generic_error', 'An error occurred. Please try again.');
          showAlert($container, 'error', errorMsg);
          $passInput.focus().select();
        }
      },
      error: function () {
        $btn.prop('disabled', false);
        $waiting.addClass('userspn-display-none');
        showAlert($container, 'error', getI18n('generic_error', 'An error occurred. Please try again.'));
        $passInput.focus().select();
      }
    });
  }

  /**
   * Bind event listeners
   */
  $(document).ready(function () {
    // Step 1: Click "Next / Continue"
    $(document).on('click', '.userspn-seq-btn-next', function (e) {
      e.preventDefault();
      var $container = $(this).closest('.userspn-sequential-login');
      handleStep1Next($container);
    });

    // Step 1: Press Enter in email input
    $(document).on('keypress', '.userspn-seq-email-input', function (e) {
      if (e.which === 13) {
        e.preventDefault();
        var $container = $(this).closest('.userspn-sequential-login');
        handleStep1Next($container);
      }
    });

    // Step 2: Click "Change email / account"
    $(document).on('click', '.userspn-seq-btn-back', function (e) {
      e.preventDefault();
      var $container = $(this).closest('.userspn-sequential-login');
      handleBackToStep1($container);
    });

    // Step 2: Password visibility toggle
    $(document).on('click', '.userspn-seq-toggle-password', function (e) {
      e.preventDefault();
      var $wrapper = $(this).closest('.userspn-seq-password-wrapper');
      var $input = $wrapper.find('.userspn-seq-password-input');
      var $icon = $(this).find('.material-icons-outlined');

      if ($input.attr('type') === 'password') {
        $input.attr('type', 'text');
        $icon.text('visibility_off');
      } else {
        $input.attr('type', 'password');
        $icon.text('visibility');
      }
    });

    // Step 2: Click "Log In"
    $(document).on('click', '.userspn-seq-btn-login', function (e) {
      e.preventDefault();
      var $container = $(this).closest('.userspn-sequential-login');
      handleStep2Submit($container);
    });

    // Step 2: Press Enter in password input
    $(document).on('keypress', '.userspn-seq-password-input', function (e) {
      if (e.which === 13) {
        e.preventDefault();
        var $container = $(this).closest('.userspn-sequential-login');
        handleStep2Submit($container);
      }
    });
  });

})(jQuery);
