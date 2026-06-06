/**
 * Email Code Login functionality
 * Handles the UI and AJAX for email verification code login
 */

(function ($) {
  'use strict';

  let currentEmail = '';
  let resendTimer = null;
  let resendCountdown = 60;

  $(document).ready(function () {
    initEmailCodeLogin();
  });

  /**
   * Initialize all event handlers
   */
  function initEmailCodeLogin() {
    // Botón principal para mostrar formulario de email
    $(document).on('click', '.userspn-email-code-login-btn', function () {
      showEmailRequestForm();
    });

    // Botón para volver al login tradicional
    $(document).on('click', '.userspn-back-to-login-btn', function () {
      showLoginForm();
    });

    // Enviar solicitud de código
    $(document).on('click', '.userspn-send-code-btn', function () {
      sendCodeRequest();
    });

    // Verificar código
    $(document).on('click', '.userspn-verify-code-btn', function () {
      verifyCode();
    });

    // Reenviar código
    $(document).on('click', '.userspn-resend-code-btn', function () {
      if (!$(this).prop('disabled')) {
        resendCode();
      }
    });

    // Manejo de inputs de código
    setupCodeInputs();

    // Enter key en email input
    $('#userspn-email-code-email').on('keypress', function (e) {
      if (e.which === 13) {
        e.preventDefault();
        sendCodeRequest();
      }
    });
  }

  /**
   * Setup code digit inputs behavior
   */
  function setupCodeInputs() {
    const $codeInputs = $('.userspn-code-digit');

    // Auto-focus siguiente input
    $codeInputs.on('input', function () {
      const $this = $(this);
      let value = $this.val();

      // Solo permitir números
      value = value.replace(/[^0-9]/g, '');
      $this.val(value);

      if (value.length === 1) {
        const index = parseInt($this.data('index'));
        if (index < 5) {
          $('.userspn-code-digit[data-index="' + (index + 1) + '"]').focus();
        }
      }
    });

    // Backspace para regresar al input anterior
    $codeInputs.on('keydown', function (e) {
      const $this = $(this);
      const index = parseInt($this.data('index'));

      if (e.key === 'Backspace' && $this.val() === '' && index > 0) {
        $('.userspn-code-digit[data-index="' + (index - 1) + '"]').focus();
      }

      // Enter para verificar
      if (e.key === 'Enter') {
        e.preventDefault();
        verifyCode();
      }
    });

    // Paste support
    $codeInputs.on('paste', function (e) {
      e.preventDefault();
      const pasteData = e.originalEvent.clipboardData.getData('text');
      const digits = pasteData.replace(/[^0-9]/g, '').split('');

      if (digits.length === 6) {
        digits.forEach((digit, index) => {
          $('.userspn-code-digit[data-index="' + index + '"]').val(digit);
        });
        $('.userspn-code-digit[data-index="5"]').focus();
      }
    });

    // Select all on focus
    $codeInputs.on('focus', function () {
      $(this).select();
    });
  }

  /**
   * Show email request form
   */
  function showEmailRequestForm() {
    $('#userspn-login').find('form').hide();
    $('#userspn-login').find('.userspn-text-align-center').first().hide();
    $('.userspn-email-code-login-btn').parent().hide();
    $('#userspn-email-code-request-form').removeClass('userspn-display-none').show();
    $('#userspn-email-code-verify-form').hide();
    $('#userspn-email-code-email').focus();
  }

  /**
   * Show verify code form
   */
  function showVerifyForm() {
    $('#userspn-email-code-request-form').hide();
    $('#userspn-email-code-verify-form').removeClass('userspn-display-none').show();
    $('.userspn-code-digit[data-index="0"]').focus();
    clearCodeInputs();
    startResendTimer();
  }

  /**
   * Show login form
   */
  function showLoginForm() {
    $('#userspn-email-code-request-form').hide();
    $('#userspn-email-code-verify-form').hide();
    $('#userspn-login').find('form').show();
    $('#userspn-login').find('.userspn-text-align-center').first().show();
    $('.userspn-email-code-login-btn').parent().show();
    currentEmail = '';
    clearCodeInputs();
    $('#userspn-email-code-email').val('');
    stopResendTimer();
  }

  /**
   * Send code request
   */
  function sendCodeRequest() {
    const email = $('#userspn-email-code-email').val().trim();

    if (!email) {
      showMessage('error', 'Please enter your email address.');
      return;
    }

    if (!isValidEmail(email)) {
      showMessage('error', 'Please enter a valid email address.');
      return;
    }

    currentEmail = email;

    const $btn = $('.userspn-send-code-btn');
    $btn.prop('disabled', true).addClass('userspn-loading');

    $.ajax({
      url: userspn_ajax.ajax_url,
      type: 'POST',
      data: {
        action: 'userspn_ajax_nopriv',
        userspn_ajax_nopriv_type: 'userspn_email_code_request',
        userspn_ajax_nopriv_nonce: userspn_ajax.userspn_ajax_nonce,
        userspn_email: email,
      },
      success: function (response) {
        $btn.prop('disabled', false).removeClass('userspn-loading');

        if (typeof response === 'string') {
          try {
            response = JSON.parse(response);
          } catch (e) {
            showMessage('error', 'An error occurred. Please try again.');
            return;
          }
        }

        if (response.success) {
          showMessage('success', response.message);
          setTimeout(function () {
            showVerifyForm();
          }, 1000);
        } else if (response.error_content) {
          showMessage('error', response.error_content);
        } else {
          showMessage('error', 'An error occurred. Please try again.');
        }
      },
      error: function () {
        $btn.prop('disabled', false).removeClass('userspn-loading');
        showMessage('error', 'Connection error. Please try again.');
      },
    });
  }

  /**
   * Verify code
   */
  function verifyCode() {
    const code = getCodeFromInputs();

    if (code.length !== 6) {
      showMessage('error', 'Please enter the complete 6-digit code.');
      return;
    }

    const $btn = $('.userspn-verify-code-btn');
    $btn.prop('disabled', true).addClass('userspn-loading');

    $.ajax({
      url: userspn_ajax.ajax_url,
      type: 'POST',
      data: {
        action: 'userspn_ajax_nopriv',
        userspn_ajax_nopriv_type: 'userspn_email_code_verify',
        userspn_ajax_nopriv_nonce: userspn_ajax.userspn_ajax_nonce,
        userspn_email: currentEmail,
        userspn_code: code,
        redirect_to: window.location.href,
      },
      success: function (response) {
        $btn.prop('disabled', false).removeClass('userspn-loading');

        if (typeof response === 'string') {
          try {
            response = JSON.parse(response);
          } catch (e) {
            showMessage('error', 'An error occurred. Please try again.');
            clearCodeInputs();
            return;
          }
        }

        if (response.success) {
          showMessage('success', response.message);
          setTimeout(function () {
            if (response.redirect_url) {
              window.location.href = response.redirect_url;
            } else {
              window.location.reload();
            }
          }, 500);
        } else if (response.error_content) {
          showMessage('error', response.error_content);
          clearCodeInputs();
          $('.userspn-code-digit[data-index="0"]').focus();
        } else {
          showMessage('error', 'An error occurred. Please try again.');
          clearCodeInputs();
        }
      },
      error: function () {
        $btn.prop('disabled', false).removeClass('userspn-loading');
        showMessage('error', 'Connection error. Please try again.');
        clearCodeInputs();
      },
    });
  }

  /**
   * Resend code
   */
  function resendCode() {
    if (!currentEmail) {
      showMessage('error', 'Email not found. Please start over.');
      return;
    }

    const $btn = $('.userspn-resend-code-btn');
    $btn.prop('disabled', true).addClass('userspn-loading');

    $.ajax({
      url: userspn_ajax.ajax_url,
      type: 'POST',
      data: {
        action: 'userspn_ajax_nopriv',
        userspn_ajax_nopriv_type: 'userspn_email_code_request',
        userspn_ajax_nopriv_nonce: userspn_ajax.userspn_ajax_nonce,
        userspn_email: currentEmail,
      },
      success: function (response) {
        $btn.removeClass('userspn-loading');

        if (typeof response === 'string') {
          try {
            response = JSON.parse(response);
          } catch (e) {
            $btn.prop('disabled', false);
            showMessage('error', 'An error occurred. Please try again.');
            return;
          }
        }

        if (response.success) {
          showMessage('success', 'A new code has been sent to your email.');
          clearCodeInputs();
          startResendTimer();
        } else if (response.error_content) {
          $btn.prop('disabled', false);
          showMessage('error', response.error_content);
        } else {
          $btn.prop('disabled', false);
          showMessage('error', 'An error occurred. Please try again.');
        }
      },
      error: function () {
        $btn.prop('disabled', false).removeClass('userspn-loading');
        showMessage('error', 'Connection error. Please try again.');
      },
    });
  }

  /**
   * Start resend countdown timer
   */
  function startResendTimer() {
    resendCountdown = 60;
    const $btn = $('.userspn-resend-code-btn');
    $btn.prop('disabled', true);

    updateResendButtonText();

    resendTimer = setInterval(function () {
      resendCountdown--;
      updateResendButtonText();

      if (resendCountdown <= 0) {
        stopResendTimer();
        $btn.prop('disabled', false);
        $btn.text($btn.data('original-text') || 'Resend code');
      }
    }, 1000);
  }

  /**
   * Update resend button text with countdown
   */
  function updateResendButtonText() {
    const $btn = $('.userspn-resend-code-btn');
    if (!$btn.data('original-text')) {
      $btn.data('original-text', $btn.text());
    }
    $btn.text('Resend code (' + resendCountdown + 's)');
  }

  /**
   * Stop resend timer
   */
  function stopResendTimer() {
    if (resendTimer) {
      clearInterval(resendTimer);
      resendTimer = null;
    }
  }

  /**
   * Get code from all inputs
   */
  function getCodeFromInputs() {
    let code = '';
    $('.userspn-code-digit').each(function () {
      code += $(this).val();
    });
    return code;
  }

  /**
   * Clear all code inputs
   */
  function clearCodeInputs() {
    $('.userspn-code-digit').val('');
  }

  /**
   * Validate email format
   */
  function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }

  /**
   * Show message to user
   */
  function showMessage(type, message) {
    // Remover mensajes anteriores
    $('.userspn-code-message').remove();

    const messageClass = type === 'success' ? 'userspn-message-success' : 'userspn-message-error';
    const messageHtml = '<div class="userspn-code-message ' + messageClass + '" style="padding: 10px; margin: 10px 0; border-radius: 4px; text-align: center; ' + (type === 'success' ? 'background: #d4edda; color: #155724; border: 1px solid #c3e6cb;' : 'background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;') + '">' + message + '</div>';

    // Insertar mensaje en el formulario activo
    if ($('#userspn-email-code-verify-form').is(':visible')) {
      $('#userspn-email-code-verify-form').prepend(messageHtml);
    } else if ($('#userspn-email-code-request-form').is(':visible')) {
      $('#userspn-email-code-request-form').prepend(messageHtml);
    } else {
      $('#userspn-login').prepend(messageHtml);
    }

    // Auto-remover después de 5 segundos
    setTimeout(function () {
      $('.userspn-code-message').fadeOut(function () {
        $(this).remove();
      });
    }, 5000);
  }
})(jQuery);
