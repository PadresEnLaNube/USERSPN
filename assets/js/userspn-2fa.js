/**
 * USERSPN Two-Factor Authentication (2FA) JavaScript
 *
 * Handles all 2FA interactions in the user profile
 *
 * @package USERSPN
 * @since 1.1.50
 */

(function($) {
    'use strict';

    var USERSPN_2FA = {
        /**
         * Initialize 2FA functionality
         */
        init: function() {
            this.bindEvents();
            this.updateMethodDisplay();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Method selector
            $(document).on('change', 'input[name="userspn_2fa_method_choice"]', this.handleMethodChange.bind(this));

            // Generate QR code
            $(document).on('click', '#userspn-2fa-generate-qr', this.generateQRCode.bind(this));

            // Enable 2FA with app
            $(document).on('click', '#userspn-2fa-enable-app', this.enableTOTP.bind(this));

            // Enable 2FA with email
            $(document).on('click', '#userspn-2fa-enable-email', this.enableEmail.bind(this));

            // Disable 2FA buttons
            $(document).on('click', '#userspn-2fa-disable-btn', this.showDisableConfirm.bind(this));
            $(document).on('click', '#userspn-2fa-disable-confirm-btn', this.disable2FA.bind(this));
            $(document).on('click', '#userspn-2fa-disable-cancel-btn', this.hideDisableConfirm.bind(this));

            // Backup codes
            $(document).on('click', '#userspn-2fa-show-backup-codes', this.regenerateBackupCodes.bind(this));
            $(document).on('click', '#userspn-2fa-copy-backup-codes', this.copyBackupCodes.bind(this));

            // Enter key on verification code inputs
            $(document).on('keypress', '#userspn-2fa-verify-code-app', function(e) {
                if (e.which === 13) {
                    $('#userspn-2fa-enable-app').click();
                }
            });
        },

        /**
         * Update method display based on selection
         */
        updateMethodDisplay: function() {
            var selectedMethod = $('input[name="userspn_2fa_method_choice"]:checked').val();

            $('.userspn-2fa-setup-method').addClass('userspn-display-none');

            if (selectedMethod === 'app') {
                $('#userspn-2fa-app-setup').removeClass('userspn-display-none');
            } else if (selectedMethod === 'email') {
                $('#userspn-2fa-email-setup').removeClass('userspn-display-none');
            }
        },

        /**
         * Handle method change
         */
        handleMethodChange: function() {
            this.updateMethodDisplay();
            // Reset QR code if switching away from app
            if ($('input[name="userspn_2fa_method_choice"]:checked').val() !== 'app') {
                this.resetQRCode();
            }
        },

        /**
         * Reset QR code display
         */
        resetQRCode: function() {
            $('#userspn-2fa-qr-code').html(
                '<button type="button" id="userspn-2fa-generate-qr" class="userspn-btn userspn-btn-primary">' +
                userspn_2fa_vars.strings.loading +
                '</button>'
            );
            $('#userspn-2fa-secret-display').addClass('userspn-display-none');
            $('#userspn-2fa-verify-code-app').val('');
        },

        /**
         * Generate QR code for authenticator app
         */
        generateQRCode: function(e) {
            e.preventDefault();
            var self = this;

            var $btn = $('#userspn-2fa-generate-qr');
            var originalText = $btn.text();
            $btn.prop('disabled', true).text(userspn_2fa_vars.strings.loading);

            $.ajax({
                url: userspn_2fa_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'userspn_ajax_server',
                    userspn_ajax_type: 'userspn_2fa_generate_secret',
                    userspn_ajax_nonce: userspn_2fa_vars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#userspn-2fa-qr-code').html(
                            '<img src="' + response.qr_code_url + '" alt="QR Code" class="userspn-2fa-qr-image">'
                        );
                        $('#userspn-2fa-secret-code').text(response.secret);
                        $('#userspn-2fa-secret-display').removeClass('userspn-display-none');
                    } else {
                        self.showMessage('error', response.error || userspn_2fa_vars.strings.error);
                        $btn.prop('disabled', false).text(originalText);
                    }
                },
                error: function() {
                    self.showMessage('error', userspn_2fa_vars.strings.error);
                    $btn.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Enable TOTP (authenticator app) 2FA
         */
        enableTOTP: function(e) {
            e.preventDefault();
            var self = this;

            var code = $('#userspn-2fa-verify-code-app').val().trim();

            if (!code || code.length !== 6 || !/^\d{6}$/.test(code)) {
                self.showMessage('error', userspn_2fa_vars.strings.invalid_code || 'Invalid code format.');
                return;
            }

            var $btn = $('#userspn-2fa-enable-app');
            var originalText = $btn.text();
            $btn.prop('disabled', true).text(userspn_2fa_vars.strings.loading);

            $.ajax({
                url: userspn_2fa_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'userspn_ajax_server',
                    userspn_ajax_type: 'userspn_2fa_enable_totp',
                    userspn_ajax_nonce: userspn_2fa_vars.nonce,
                    code: code
                },
                success: function(response) {
                    if (response.success) {
                        self.showMessage('success', response.message);
                        self.showBackupCodes(response.backup_codes);

                        // Reload page after showing backup codes
                        setTimeout(function() {
                            location.reload();
                        }, 5000);
                    } else {
                        self.showMessage('error', response.error || userspn_2fa_vars.strings.error);
                        $btn.prop('disabled', false).text(originalText);
                    }
                },
                error: function() {
                    self.showMessage('error', userspn_2fa_vars.strings.error);
                    $btn.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Enable email 2FA
         */
        enableEmail: function(e) {
            e.preventDefault();
            var self = this;

            var $btn = $('#userspn-2fa-enable-email');
            var originalText = $btn.text();
            $btn.prop('disabled', true).text(userspn_2fa_vars.strings.loading);

            $.ajax({
                url: userspn_2fa_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'userspn_ajax_server',
                    userspn_ajax_type: 'userspn_2fa_enable_email',
                    userspn_ajax_nonce: userspn_2fa_vars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.showMessage('success', response.message);
                        self.showBackupCodes(response.backup_codes);

                        // Reload page after showing backup codes
                        setTimeout(function() {
                            location.reload();
                        }, 5000);
                    } else {
                        self.showMessage('error', response.error || userspn_2fa_vars.strings.error);
                        $btn.prop('disabled', false).text(originalText);
                    }
                },
                error: function() {
                    self.showMessage('error', userspn_2fa_vars.strings.error);
                    $btn.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Show disable confirmation
         */
        showDisableConfirm: function(e) {
            e.preventDefault();
            $('#userspn-2fa-disable-confirm').removeClass('userspn-display-none');
            $('#userspn-2fa-disable-btn').addClass('userspn-display-none');
        },

        /**
         * Hide disable confirmation
         */
        hideDisableConfirm: function(e) {
            e.preventDefault();
            $('#userspn-2fa-disable-confirm').addClass('userspn-display-none');
            $('#userspn-2fa-disable-btn').removeClass('userspn-display-none');
            $('#userspn-2fa-disable-password').val('');
        },

        /**
         * Disable 2FA
         */
        disable2FA: function(e) {
            e.preventDefault();
            var self = this;

            var password = $('#userspn-2fa-disable-password').val();

            if (!password) {
                self.showMessage('error', userspn_2fa_vars.strings.enter_password);
                return;
            }

            if (!confirm(userspn_2fa_vars.strings.confirm_disable)) {
                return;
            }

            var $btn = $('#userspn-2fa-disable-confirm-btn');
            var originalText = $btn.text();
            $btn.prop('disabled', true).text(userspn_2fa_vars.strings.loading);

            $.ajax({
                url: userspn_2fa_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'userspn_ajax_server',
                    userspn_ajax_type: 'userspn_2fa_disable',
                    userspn_ajax_nonce: userspn_2fa_vars.nonce,
                    password: password
                },
                success: function(response) {
                    if (response.success) {
                        self.showMessage('success', response.message);

                        // Reload page
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        self.showMessage('error', response.error || userspn_2fa_vars.strings.error);
                        $btn.prop('disabled', false).text(originalText);
                    }
                },
                error: function() {
                    self.showMessage('error', userspn_2fa_vars.strings.error);
                    $btn.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Regenerate backup codes
         */
        regenerateBackupCodes: function(e) {
            e.preventDefault();
            var self = this;

            if (!confirm(userspn_2fa_vars.strings.confirm_regenerate)) {
                return;
            }

            var $btn = $('#userspn-2fa-show-backup-codes');
            var originalText = $btn.html();
            $btn.prop('disabled', true).html('<i class="material-icons-outlined userspn-vertical-align-middle">autorenew</i> ' + userspn_2fa_vars.strings.loading);

            $.ajax({
                url: userspn_2fa_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'userspn_ajax_server',
                    userspn_ajax_type: 'userspn_2fa_generate_backup_codes',
                    userspn_ajax_nonce: userspn_2fa_vars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.showMessage('success', response.message);
                        self.displayBackupCodes(response.backup_codes);
                        $('#userspn-2fa-backup-count').text(response.backup_codes.length);
                        $btn.prop('disabled', false).html(originalText);
                    } else {
                        self.showMessage('error', response.error || userspn_2fa_vars.strings.error);
                        $btn.prop('disabled', false).html(originalText);
                    }
                },
                error: function() {
                    self.showMessage('error', userspn_2fa_vars.strings.error);
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        },

        /**
         * Display backup codes
         */
        displayBackupCodes: function(codes) {
            var html = '<div class="userspn-2fa-backup-codes-grid">';
            codes.forEach(function(code) {
                html += '<code class="userspn-2fa-backup-code">' + code + '</code>';
            });
            html += '</div>';

            $('#userspn-2fa-backup-codes-list').html(html);
            $('#userspn-2fa-backup-codes-display').removeClass('userspn-display-none');
        },

        /**
         * Show backup codes after enabling 2FA
         */
        showBackupCodes: function(codes) {
            var html = '<div class="userspn-alert userspn-alert-warning userspn-mt-15">';
            html += '<p class="userspn-font-weight-bold userspn-mb-10">' + userspn_2fa_vars.strings.save_backup_codes + '</p>';
            html += '<div class="userspn-2fa-backup-codes-grid">';
            codes.forEach(function(code) {
                html += '<code class="userspn-2fa-backup-code">' + code + '</code>';
            });
            html += '</div>';
            html += '<button type="button" class="userspn-btn userspn-btn-mini userspn-btn-primary userspn-mt-10" onclick="USERSPN_2FA.copyBackupCodesFromAlert(this);">';
            html += '<i class="material-icons-outlined userspn-vertical-align-middle userspn-font-size-16">content_copy</i> ';
            html += userspn_2fa_vars.strings.copy_codes;
            html += '</button>';
            html += '</div>';

            $('#userspn-2fa-messages').html(html);
        },

        /**
         * Copy backup codes to clipboard
         */
        copyBackupCodes: function(e) {
            e.preventDefault();
            var codes = [];

            $('#userspn-2fa-backup-codes-list .userspn-2fa-backup-code').each(function() {
                codes.push($(this).text());
            });

            this.copyToClipboard(codes.join('\n'));
        },

        /**
         * Copy backup codes from alert
         */
        copyBackupCodesFromAlert: function(btn) {
            var codes = [];

            $(btn).closest('.userspn-alert').find('.userspn-2fa-backup-code').each(function() {
                codes.push($(this).text());
            });

            this.copyToClipboard(codes.join('\n'));
        },

        /**
         * Copy text to clipboard
         */
        copyToClipboard: function(text) {
            var self = this;

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function() {
                    self.showMessage('success', userspn_2fa_vars.strings.codes_copied);
                }, function() {
                    self.fallbackCopyToClipboard(text);
                });
            } else {
                this.fallbackCopyToClipboard(text);
            }
        },

        /**
         * Fallback copy to clipboard method
         */
        fallbackCopyToClipboard: function(text) {
            var self = this;
            var textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.top = '0';
            textArea.style.left = '0';
            textArea.style.width = '2em';
            textArea.style.height = '2em';
            textArea.style.padding = '0';
            textArea.style.border = 'none';
            textArea.style.outline = 'none';
            textArea.style.boxShadow = 'none';
            textArea.style.background = 'transparent';

            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                var successful = document.execCommand('copy');
                if (successful) {
                    self.showMessage('success', userspn_2fa_vars.strings.codes_copied);
                } else {
                    self.showMessage('error', userspn_2fa_vars.strings.copy_failed);
                }
            } catch (err) {
                self.showMessage('error', userspn_2fa_vars.strings.copy_failed);
            }

            document.body.removeChild(textArea);
        },

        /**
         * Show message
         */
        showMessage: function(type, message) {
            var alertClass = type === 'success' ? 'userspn-alert-success' : 'userspn-alert-danger';
            var icon = type === 'success' ? 'check_circle' : 'error';

            var html = '<div class="userspn-alert ' + alertClass + '">';
            html += '<i class="material-icons-outlined userspn-vertical-align-middle">' + icon + '</i> ';
            html += message;
            html += '</div>';

            $('#userspn-2fa-messages').html(html);

            // Auto-hide success messages after 5 seconds
            if (type === 'success') {
                setTimeout(function() {
                    $('#userspn-2fa-messages').fadeOut(function() {
                        $(this).html('').show();
                    });
                }, 5000);
            }
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        USERSPN_2FA.init();
    });

    // Expose to global scope for inline onclick handlers
    window.USERSPN_2FA = USERSPN_2FA;

})(jQuery);
