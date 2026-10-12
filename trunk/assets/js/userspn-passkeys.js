/**
 * UsersPN Passkeys (WebAuthn) client-side implementation.
 *
 * Handles passkey registration and authentication flows using
 * the Web Authentication API (navigator.credentials).
 *
 * @since 1.1.61
 */
(function($) {
    'use strict';

    // ========== Helpers ==========

    function base64urlToBuffer(base64url) {
        var base64 = base64url.replace(/-/g, '+').replace(/_/g, '/');
        while (base64.length % 4) base64 += '=';
        var binary = atob(base64);
        var bytes = new Uint8Array(binary.length);
        for (var i = 0; i < binary.length; i++) {
            bytes[i] = binary.charCodeAt(i);
        }
        return bytes.buffer;
    }

    function bufferToBase64url(buffer) {
        var bytes = new Uint8Array(buffer);
        var binary = '';
        for (var i = 0; i < bytes.length; i++) {
            binary += String.fromCharCode(bytes[i]);
        }
        return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
    }

    // ========== Registration ==========

    window.userspnRegisterPasskey = function(deviceName) {
        if (!window.PublicKeyCredential) {
            alert(userspnPasskeys.i18n.not_supported || 'Passkeys are not supported in this browser.');
            return Promise.reject('not_supported');
        }

        // Step 1: Get options from server
        return $.ajax({
            url: userspnPasskeys.ajaxUrl,
            type: 'POST',
            data: {
                action: 'userspn_ajax_server',
                userspn_ajax_type: 'userspn_passkey_register_options',
                userspn_ajax_nonce: userspnPasskeys.nonce,
            },
            dataType: 'json'
        }).then(function(response) {
            if (!response.success) {
                throw new Error(response.data || 'Failed to get registration options.');
            }

            var options = response.data;

            // Convert challenge and user.id from base64url to ArrayBuffer
            var publicKey = {
                rp: options.rp,
                user: {
                    id: base64urlToBuffer(options.user.id),
                    name: options.user.name,
                    displayName: options.user.displayName
                },
                challenge: base64urlToBuffer(options.challenge),
                pubKeyCredParams: options.pubKeyCredParams,
                timeout: options.timeout,
                attestation: options.attestation,
                authenticatorSelection: options.authenticatorSelection,
                excludeCredentials: (options.excludeCredentials || []).map(function(cred) {
                    return {
                        id: base64urlToBuffer(cred.id),
                        type: cred.type
                    };
                })
            };

            // Step 2: Create credential via browser API
            return navigator.credentials.create({ publicKey: publicKey });
        }).then(function(credential) {
            // Step 3: Send credential to server for verification
            var credentialData = {
                id: bufferToBase64url(credential.rawId),
                type: credential.type,
                response: {
                    clientDataJSON: bufferToBase64url(credential.response.clientDataJSON),
                    attestationObject: bufferToBase64url(credential.response.attestationObject)
                }
            };

            return $.ajax({
                url: userspnPasskeys.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'userspn_ajax_server',
                    userspn_ajax_type: 'userspn_passkey_register_verify',
                    userspn_ajax_nonce: userspnPasskeys.nonce,
                    credential: JSON.stringify(credentialData),
                    device_name: deviceName || ''
                },
                dataType: 'json'
            });
        }).then(function(response) {
            if (!response.success) {
                throw new Error(response.data || 'Registration failed.');
            }
            return response;
        });
    };

    // ========== Authentication ==========

    window.userspnAuthenticatePasskey = function(email) {
        if (!window.PublicKeyCredential) {
            alert(userspnPasskeys.i18n.not_supported || 'Passkeys are not supported in this browser.');
            return Promise.reject('not_supported');
        }

        var nonce = userspnPasskeys.nonce || '';
        var ajaxAction = 'userspn_ajax_nopriv_server';
        var nonceKey = 'userspn_ajax_nopriv_nonce';
        var typeKey = 'userspn_ajax_nopriv_type';

        // Step 1: Get auth options from server
        var requestData = {
            action: ajaxAction,
        };
        requestData[nonceKey] = nonce;
        requestData[typeKey] = 'userspn_passkey_auth_options';
        if (email) {
            requestData['userspn_email'] = email;
        }

        return $.ajax({
            url: userspnPasskeys.ajaxUrl,
            type: 'POST',
            data: requestData,
            dataType: 'json'
        }).then(function(response) {
            if (response.error) {
                throw new Error(response.error);
            }

            var options = response.options;
            var publicKey = {
                challenge: base64urlToBuffer(options.challenge),
                timeout: options.timeout,
                rpId: options.rpId,
                userVerification: options.userVerification
            };

            if (options.allowCredentials && options.allowCredentials.length) {
                publicKey.allowCredentials = options.allowCredentials.map(function(cred) {
                    return {
                        id: base64urlToBuffer(cred.id),
                        type: cred.type
                    };
                });
            }

            // Step 2: Get credential via browser API
            return navigator.credentials.get({ publicKey: publicKey });
        }).then(function(assertion) {
            // Step 3: Send assertion to server for verification
            var assertionData = {
                id: bufferToBase64url(assertion.rawId),
                type: assertion.type,
                response: {
                    clientDataJSON: bufferToBase64url(assertion.response.clientDataJSON),
                    authenticatorData: bufferToBase64url(assertion.response.authenticatorData),
                    signature: bufferToBase64url(assertion.response.signature),
                    userHandle: assertion.response.userHandle ? bufferToBase64url(assertion.response.userHandle) : null
                }
            };

            var verifyData = {
                action: ajaxAction,
            };
            verifyData[nonceKey] = nonce;
            verifyData[typeKey] = 'userspn_passkey_auth_verify';
            verifyData['credential'] = JSON.stringify(assertionData);

            return $.ajax({
                url: userspnPasskeys.ajaxUrl,
                type: 'POST',
                data: verifyData,
                dataType: 'json'
            });
        }).then(function(response) {
            if (response.error) {
                throw new Error(response.error);
            }
            if (response.redirect_url) {
                window.location.href = response.redirect_url;
            }
            return response;
        });
    };

    // ========== DOM Event Handlers ==========

    $(document).ready(function() {
        // Register passkey button
        $(document).on('click', '.userspn-passkey-register-btn', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var deviceName = prompt(userspnPasskeys.i18n.device_name_prompt || 'Enter a name for this passkey (e.g., "My MacBook"):');
            if (deviceName === null) return;

            $btn.prop('disabled', true).text(userspnPasskeys.i18n.registering || 'Registering...');

            userspnRegisterPasskey(deviceName).then(function() {
                $btn.prop('disabled', false).text(userspnPasskeys.i18n.register || 'Add Passkey');
                // Refresh passkey list if exists
                if (typeof userspnRefreshPasskeyList === 'function') {
                    userspnRefreshPasskeyList();
                }
                alert(userspnPasskeys.i18n.registered || 'Passkey registered successfully!');
            }).catch(function(err) {
                $btn.prop('disabled', false).text(userspnPasskeys.i18n.register || 'Add Passkey');
                console.error('Passkey registration error:', err);
            });
        });

        // Delete passkey button
        $(document).on('click', '.userspn-passkey-delete-btn', function(e) {
            e.preventDefault();
            if (!confirm(userspnPasskeys.i18n.confirm_delete || 'Delete this passkey?')) return;

            var $btn = $(this);
            var passkeyId = $btn.data('passkey-id');

            $.ajax({
                url: userspnPasskeys.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'userspn_ajax_server',
                    userspn_ajax_type: 'userspn_passkey_delete',
                    userspn_ajax_nonce: userspnPasskeys.nonce,
                    passkey_id: passkeyId
                },
                dataType: 'json'
            }).done(function(response) {
                if (response.success) {
                    $btn.closest('.userspn-passkey-item').fadeOut(300, function() { $(this).remove(); });
                }
            });
        });

        // Login with passkey button
        $(document).on('click', '.userspn-passkey-login-btn', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var email = $btn.closest('form').find('[name="userspn_email"]').val() || '';

            $btn.prop('disabled', true);

            userspnAuthenticatePasskey(email).catch(function(err) {
                $btn.prop('disabled', false);
                console.error('Passkey login error:', err);
            });
        });
    });

})(jQuery);
