<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * WebAuthn/Passkeys implementation for USERSPN plugin.
 *
 * Handles passkey registration, authentication, and management
 * using native PHP functions (no Composer dependencies).
 * Supports only attestation format "none" (sufficient for passkeys).
 *
 * @link       padresenlanube.com/
 * @since      1.1.61
 * @package    USERSPN
 * @subpackage USERSPN/includes
 * @author     Padres en la Nube <info@padresenlanube.com>
 */

class USERSPN_Passkeys {

    /**
     * Get the RP ID (relying party ID) — the domain name
     */
    private function get_rp_id() {
        return wp_parse_url(home_url(), PHP_URL_HOST);
    }

    /**
     * Get the RP name
     */
    private function get_rp_name() {
        return get_bloginfo('name');
    }

    // ===================== REGISTRATION =====================

    /**
     * Create registration (credential creation) options
     *
     * @param int $user_id
     * @return array|WP_Error
     */
    public function create_registration_options($user_id) {
        $user = get_userdata($user_id);
        if (!$user) {
            return new WP_Error('invalid_user', __('Invalid user.', 'userspn'));
        }

        $challenge = random_bytes(32);
        set_transient('userspn_passkey_challenge_' . $user_id, base64_encode($challenge), 300);

        // Get existing credential IDs to exclude
        $existing = $this->get_user_passkeys($user_id);
        $exclude = [];
        foreach ($existing as $passkey) {
            $exclude[] = [
                'id' => $this->base64url_encode($passkey['credential_id_raw']),
                'type' => 'public-key',
            ];
        }

        $options = [
            'rp' => [
                'name' => $this->get_rp_name(),
                'id' => $this->get_rp_id(),
            ],
            'user' => [
                'id' => $this->base64url_encode(pack('N', $user_id)),
                'name' => $user->user_email,
                'displayName' => $user->display_name ?: $user->user_email,
            ],
            'challenge' => $this->base64url_encode($challenge),
            'pubKeyCredParams' => [
                ['type' => 'public-key', 'alg' => -7], // ES256
            ],
            'timeout' => 60000,
            'attestation' => 'none',
            'authenticatorSelection' => [
                'authenticatorAttachment' => 'platform',
                'residentKey' => 'preferred',
                'userVerification' => 'preferred',
            ],
            'excludeCredentials' => $exclude,
        ];

        return $options;
    }

    /**
     * Verify registration response and store credential
     *
     * @param int $user_id
     * @param array $credential Parsed JSON from navigator.credentials.create
     * @param string $device_name
     * @return true|WP_Error
     */
    public function verify_registration($user_id, $credential, $device_name = '') {
        // Retrieve challenge
        $stored_challenge = get_transient('userspn_passkey_challenge_' . $user_id);
        if (!$stored_challenge) {
            return new WP_Error('expired', __('Registration challenge expired. Please try again.', 'userspn'));
        }
        delete_transient('userspn_passkey_challenge_' . $user_id);
        $challenge = base64_decode($stored_challenge);

        if (empty($credential['id']) || empty($credential['response']['clientDataJSON']) || empty($credential['response']['attestationObject'])) {
            return new WP_Error('invalid', __('Invalid credential data.', 'userspn'));
        }

        // Decode clientDataJSON
        $client_data_json = $this->base64url_decode($credential['response']['clientDataJSON']);
        $client_data = json_decode($client_data_json, true);
        if (!$client_data) {
            return new WP_Error('invalid_client', __('Invalid client data.', 'userspn'));
        }

        // Verify type
        if ($client_data['type'] !== 'webauthn.create') {
            return new WP_Error('invalid_type', __('Invalid ceremony type.', 'userspn'));
        }

        // Verify challenge
        $received_challenge = $this->base64url_decode($client_data['challenge']);
        if (!hash_equals($challenge, $received_challenge)) {
            return new WP_Error('challenge_mismatch', __('Challenge mismatch.', 'userspn'));
        }

        // Verify origin
        $expected_origin = home_url();
        // Strip trailing slash for comparison
        $expected_origin = rtrim($expected_origin, '/');
        $received_origin = rtrim($client_data['origin'] ?? '', '/');
        if ($expected_origin !== $received_origin) {
            return new WP_Error('origin_mismatch', __('Origin mismatch.', 'userspn'));
        }

        // Decode attestationObject (CBOR)
        $attestation_object_raw = $this->base64url_decode($credential['response']['attestationObject']);
        $attestation = $this->cbor_decode($attestation_object_raw);
        if (!$attestation || !isset($attestation['authData'])) {
            return new WP_Error('invalid_attestation', __('Invalid attestation object.', 'userspn'));
        }

        // Parse authenticator data
        $auth_data = $this->parse_authenticator_data($attestation['authData']);
        if (!$auth_data) {
            return new WP_Error('invalid_auth_data', __('Invalid authenticator data.', 'userspn'));
        }

        // Verify RP ID hash
        $expected_rp_id_hash = hash('sha256', $this->get_rp_id(), true);
        if (!hash_equals($expected_rp_id_hash, $auth_data['rp_id_hash'])) {
            return new WP_Error('rp_id_mismatch', __('RP ID mismatch.', 'userspn'));
        }

        // Check user present flag
        if (!($auth_data['flags'] & 0x01)) {
            return new WP_Error('user_not_present', __('User not present.', 'userspn'));
        }

        if (empty($auth_data['credential_id']) || empty($auth_data['public_key_cose'])) {
            return new WP_Error('no_credential', __('No credential in authenticator data.', 'userspn'));
        }

        // Convert COSE key to PEM
        $pem = $this->cose_to_pem($auth_data['public_key_cose']);
        if (!$pem) {
            return new WP_Error('key_error', __('Could not convert public key.', 'userspn'));
        }

        // Store credential
        global $wpdb;
        $table = $wpdb->prefix . 'userspn_passkeys';
        $credential_id = $auth_data['credential_id'];

        $wpdb->insert($table, [
            'user_id' => $user_id,
            'credential_id' => $credential_id,
            'public_key' => $pem,
            'sign_count' => $auth_data['sign_count'],
            'device_name' => !empty($device_name) ? $device_name : __('Passkey', 'userspn'),
            'created_at' => current_time('mysql'),
        ], ['%d', '%s', '%s', '%d', '%s', '%s']);

        if (class_exists('USERSPN_Security')) {
            USERSPN_Security::log_audit_event('passkey_registered', 'Passkey registered', [
                'device_name' => $device_name,
            ], $user_id);
        }

        return true;
    }

    // ===================== AUTHENTICATION =====================

    /**
     * Create authentication (credential get) options
     *
     * @param string $email Optional email to scope to a user
     * @return array|WP_Error
     */
    public function create_authentication_options($email = '') {
        $challenge = random_bytes(32);
        $challenge_key = !empty($email) ? 'userspn_passkey_auth_' . md5($email) : 'userspn_passkey_auth_global';
        set_transient($challenge_key, base64_encode($challenge), 300);

        $allow_credentials = [];

        if (!empty($email)) {
            $user = get_user_by('email', $email);
            if ($user) {
                $passkeys = $this->get_user_passkeys($user->ID);
                foreach ($passkeys as $pk) {
                    $allow_credentials[] = [
                        'id' => $this->base64url_encode($pk['credential_id_raw']),
                        'type' => 'public-key',
                    ];
                }
            }
        }

        $options = [
            'challenge' => $this->base64url_encode($challenge),
            'timeout' => 60000,
            'rpId' => $this->get_rp_id(),
            'userVerification' => 'preferred',
        ];

        if (!empty($allow_credentials)) {
            $options['allowCredentials'] = $allow_credentials;
        }

        return $options;
    }

    /**
     * Verify authentication response
     *
     * @param array $credential Parsed JSON from navigator.credentials.get
     * @return array|WP_Error ['user_id' => int]
     */
    public function verify_authentication($credential) {
        if (empty($credential['id']) || empty($credential['response']['clientDataJSON']) || empty($credential['response']['authenticatorData']) || empty($credential['response']['signature'])) {
            return new WP_Error('invalid', __('Invalid credential data.', 'userspn'));
        }

        $credential_id_raw = $this->base64url_decode($credential['id']);

        // Find the credential in DB
        global $wpdb;
        $table = $wpdb->prefix . 'userspn_passkeys';
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE credential_id = %s", $credential_id_raw));
        if (!$row) {
            return new WP_Error('unknown_credential', __('Unknown passkey.', 'userspn'));
        }

        $user_id = intval($row->user_id);

        // Retrieve challenge
        $user = get_userdata($user_id);
        $email = $user ? $user->user_email : '';
        $challenge_key = !empty($email) ? 'userspn_passkey_auth_' . md5($email) : 'userspn_passkey_auth_global';
        $stored_challenge = get_transient($challenge_key);

        // Also try global key
        if (!$stored_challenge) {
            $stored_challenge = get_transient('userspn_passkey_auth_global');
            $challenge_key = 'userspn_passkey_auth_global';
        }

        if (!$stored_challenge) {
            return new WP_Error('expired', __('Authentication challenge expired.', 'userspn'));
        }
        delete_transient($challenge_key);
        $challenge = base64_decode($stored_challenge);

        // Decode clientDataJSON
        $client_data_json = $this->base64url_decode($credential['response']['clientDataJSON']);
        $client_data = json_decode($client_data_json, true);

        if (!$client_data || $client_data['type'] !== 'webauthn.get') {
            return new WP_Error('invalid_type', __('Invalid ceremony type.', 'userspn'));
        }

        // Verify challenge
        $received_challenge = $this->base64url_decode($client_data['challenge']);
        if (!hash_equals($challenge, $received_challenge)) {
            return new WP_Error('challenge_mismatch', __('Challenge mismatch.', 'userspn'));
        }

        // Verify origin
        $expected_origin = rtrim(home_url(), '/');
        $received_origin = rtrim($client_data['origin'] ?? '', '/');
        if ($expected_origin !== $received_origin) {
            return new WP_Error('origin_mismatch', __('Origin mismatch.', 'userspn'));
        }

        // Parse authenticator data
        $auth_data_raw = $this->base64url_decode($credential['response']['authenticatorData']);
        $auth_data = $this->parse_authenticator_data($auth_data_raw, false);
        if (!$auth_data) {
            return new WP_Error('invalid_auth_data', __('Invalid authenticator data.', 'userspn'));
        }

        // Verify RP ID hash
        $expected_rp_id_hash = hash('sha256', $this->get_rp_id(), true);
        if (!hash_equals($expected_rp_id_hash, $auth_data['rp_id_hash'])) {
            return new WP_Error('rp_id_mismatch', __('RP ID mismatch.', 'userspn'));
        }

        // Check user present
        if (!($auth_data['flags'] & 0x01)) {
            return new WP_Error('user_not_present', __('User not present.', 'userspn'));
        }

        // Verify signature
        $client_data_hash = hash('sha256', $client_data_json, true);
        $signed_data = $auth_data_raw . $client_data_hash;
        $signature = $this->base64url_decode($credential['response']['signature']);

        $pem = $row->public_key;
        $key = openssl_pkey_get_public($pem);
        if (!$key) {
            return new WP_Error('key_error', __('Could not load public key.', 'userspn'));
        }

        // DER-decode the signature for openssl_verify (WebAuthn uses raw r||s, OpenSSL wants DER)
        $der_signature = $this->raw_to_der_signature($signature);
        $valid = openssl_verify($signed_data, $der_signature ?: $signature, $key, OPENSSL_ALGO_SHA256);

        if ($valid !== 1) {
            return new WP_Error('signature_invalid', __('Invalid signature.', 'userspn'));
        }

        // Update sign count
        $new_count = $auth_data['sign_count'];
        if ($new_count > 0 && $new_count <= intval($row->sign_count)) {
            // Possible cloned authenticator
            return new WP_Error('cloned', __('Possible cloned authenticator detected.', 'userspn'));
        }

        $wpdb->update($table, [
            'sign_count' => $new_count,
            'last_used_at' => current_time('mysql'),
        ], ['id' => $row->id], ['%d', '%s'], ['%d']);

        return ['user_id' => $user_id];
    }

    // ===================== MANAGEMENT =====================

    /**
     * Get all passkeys for a user
     */
    public function get_user_passkeys($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'userspn_passkeys';
        $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE user_id = %d ORDER BY created_at DESC", $user_id), ARRAY_A);

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'id' => intval($row['id']),
                'device_name' => $row['device_name'],
                'created_at' => $row['created_at'],
                'last_used_at' => $row['last_used_at'],
                'credential_id_raw' => $row['credential_id'],
            ];
        }
        return $result;
    }

    /**
     * Delete a passkey
     */
    public function delete_passkey($user_id, $passkey_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'userspn_passkeys';

        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d AND user_id = %d", $passkey_id, $user_id));
        if (!$row) {
            return new WP_Error('not_found', __('Passkey not found.', 'userspn'));
        }

        $wpdb->delete($table, ['id' => $passkey_id, 'user_id' => $user_id], ['%d', '%d']);

        if (class_exists('USERSPN_Security')) {
            USERSPN_Security::log_audit_event('passkey_deleted', 'Passkey deleted', [
                'device_name' => $row->device_name,
            ], $user_id);
        }

        return true;
    }

    // ===================== HELPERS =====================

    /**
     * Parse authenticator data bytes
     *
     * @param string $data Raw authenticator data
     * @param bool $expect_credential Whether to expect attested credential data
     * @return array|false
     */
    private function parse_authenticator_data($data, $expect_credential = true) {
        if (strlen($data) < 37) {
            return false;
        }

        $rp_id_hash = substr($data, 0, 32);
        $flags = ord($data[32]);
        $sign_count = unpack('N', substr($data, 33, 4))[1];

        $result = [
            'rp_id_hash' => $rp_id_hash,
            'flags' => $flags,
            'sign_count' => $sign_count,
            'credential_id' => null,
            'public_key_cose' => null,
        ];

        // AT (attested credential data) flag
        if ($flags & 0x40) {
            if (strlen($data) < 55) {
                return $expect_credential ? false : $result;
            }

            // AAGUID (16 bytes)
            $offset = 37;
            $offset += 16; // skip AAGUID

            // Credential ID length (2 bytes big-endian)
            $cred_id_len = unpack('n', substr($data, $offset, 2))[1];
            $offset += 2;

            // Credential ID
            $credential_id = substr($data, $offset, $cred_id_len);
            $offset += $cred_id_len;

            $result['credential_id'] = $credential_id;

            // The rest is the COSE public key (CBOR-encoded)
            $cose_key_cbor = substr($data, $offset);
            $result['public_key_cose'] = $this->cbor_decode($cose_key_cbor);
        }

        return $result;
    }

    /**
     * Convert a COSE ES256 key to PEM format
     *
     * @param array $cose_key Decoded COSE key map
     * @return string|false PEM public key
     */
    private function cose_to_pem($cose_key) {
        if (!is_array($cose_key)) {
            return false;
        }

        // COSE key map: 1=kty, 3=alg, -1=crv, -2=x, -3=y
        $kty = $cose_key[1] ?? null;
        $alg = $cose_key[3] ?? null;
        $x = $cose_key[-2] ?? null;
        $y = $cose_key[-3] ?? null;

        // Only support EC2 (kty=2) with ES256 (alg=-7)
        if ($kty !== 2 || $alg !== -7 || !$x || !$y) {
            return false;
        }

        // Ensure x and y are 32 bytes
        $x = str_pad($x, 32, "\0", STR_PAD_LEFT);
        $y = str_pad($y, 32, "\0", STR_PAD_LEFT);

        // Uncompressed EC point: 0x04 || x || y
        $point = "\x04" . $x . $y;

        // DER-encode as SubjectPublicKeyInfo for P-256
        // OID 1.2.840.10045.2.1 (ecPublicKey) + OID 1.2.840.10045.3.1.7 (prime256v1)
        $der = "\x30\x59\x30\x13\x06\x07\x2a\x86\x48\xce\x3d\x02\x01\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07\x03\x42\x00" . $point;

        $pem = "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($der), 64, "\n") . "-----END PUBLIC KEY-----\n";

        // Verify the key is valid
        $key = openssl_pkey_get_public($pem);
        if (!$key) {
            return false;
        }

        return $pem;
    }

    /**
     * Convert raw ECDSA signature (r||s) to DER format
     */
    private function raw_to_der_signature($raw) {
        if (strlen($raw) !== 64) {
            return false;
        }

        $r = substr($raw, 0, 32);
        $s = substr($raw, 32, 32);

        // Remove leading zeros, then add 0x00 if high bit set
        $r = ltrim($r, "\0");
        $s = ltrim($s, "\0");

        if (ord($r[0]) & 0x80) $r = "\0" . $r;
        if (ord($s[0]) & 0x80) $s = "\0" . $s;

        $r_der = "\x02" . chr(strlen($r)) . $r;
        $s_der = "\x02" . chr(strlen($s)) . $s;

        $seq = $r_der . $s_der;
        return "\x30" . chr(strlen($seq)) . $seq;
    }

    /**
     * Base64URL encode
     */
    private function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64URL decode
     */
    private function base64url_decode($data) {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', (4 - strlen($data) % 4) % 4));
    }

    /**
     * Minimal CBOR decoder sufficient for WebAuthn attestation objects and COSE keys.
     * Supports: unsigned ints, negative ints, byte strings, text strings, arrays, maps, simple values, tags.
     *
     * @param string $data Raw CBOR bytes
     * @return mixed
     */
    private function cbor_decode($data) {
        $offset = 0;
        return $this->cbor_decode_item($data, $offset);
    }

    private function cbor_decode_item($data, &$offset) {
        if ($offset >= strlen($data)) {
            return null;
        }

        $byte = ord($data[$offset]);
        $major = ($byte & 0xe0) >> 5;
        $additional = $byte & 0x1f;
        $offset++;

        $value = $this->cbor_decode_length($data, $offset, $additional);

        switch ($major) {
            case 0: // unsigned int
                return $value;
            case 1: // negative int
                return -1 - $value;
            case 2: // byte string
                $result = substr($data, $offset, $value);
                $offset += $value;
                return $result;
            case 3: // text string
                $result = substr($data, $offset, $value);
                $offset += $value;
                return $result;
            case 4: // array
                $arr = [];
                for ($i = 0; $i < $value; $i++) {
                    $arr[] = $this->cbor_decode_item($data, $offset);
                }
                return $arr;
            case 5: // map
                $map = [];
                for ($i = 0; $i < $value; $i++) {
                    $key = $this->cbor_decode_item($data, $offset);
                    $val = $this->cbor_decode_item($data, $offset);
                    $map[$key] = $val;
                }
                return $map;
            case 6: // tag (skip the tag number, return the tagged value)
                return $this->cbor_decode_item($data, $offset);
            case 7: // simple/float
                if ($additional === 20) return false;
                if ($additional === 21) return true;
                if ($additional === 22) return null;
                return $value;
        }

        return null;
    }

    private function cbor_decode_length($data, &$offset, $additional) {
        if ($additional < 24) {
            return $additional;
        }
        if ($additional === 24) {
            $val = ord($data[$offset]);
            $offset++;
            return $val;
        }
        if ($additional === 25) {
            $val = unpack('n', substr($data, $offset, 2))[1];
            $offset += 2;
            return $val;
        }
        if ($additional === 26) {
            $val = unpack('N', substr($data, $offset, 4))[1];
            $offset += 4;
            return $val;
        }
        if ($additional === 27) {
            $high = unpack('N', substr($data, $offset, 4))[1];
            $low = unpack('N', substr($data, $offset + 4, 4))[1];
            $offset += 8;
            return ($high << 32) | $low;
        }
        return 0;
    }
}
