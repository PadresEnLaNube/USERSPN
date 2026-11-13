<?php
/**
 * Two-Factor Authentication handler for USERSPN plugin.
 *
 * This class handles 2FA functionality including TOTP generation, verification,
 * QR code generation, backup codes, and email-based 2FA.
 *
 * @link       padresenlanube.com/
 * @since      1.1.50
 * @package    USERSPN
 * @subpackage USERSPN/includes
 * @author     Padres en la Nube <info@padresenlanube.com>
 */

class USERSPN_2FA {

    /**
     * Check if 2FA is enabled globally
     *
     * @return bool
     */
    public static function is_2fa_enabled() {
        return get_option('userspn_2fa_enabled') === 'on';
    }

    /**
     * Get the configured 2FA method (app or email)
     *
     * @return string
     */
    public static function get_2fa_method() {
        return get_option('userspn_2fa_method', 'app');
    }

    /**
     * Check if 2FA is required for a specific user based on their role
     *
     * @param int $user_id User ID
     * @return bool
     */
    public static function is_2fa_required_for_user($user_id) {
        if (!self::is_2fa_enabled()) {
            return false;
        }

        // Check if role-based enforcement is enabled
        $force_roles = get_option('userspn_2fa_force_roles');
        if ($force_roles !== 'on') {
            // 2FA is optional for all users
            return false;
        }

        // Get selected roles that require 2FA
        $required_roles = get_option('userspn_2fa_roles', []);
        if (!is_array($required_roles)) {
            $required_roles = $required_roles ? [$required_roles] : [];
        }

        if (empty($required_roles)) {
            return false;
        }

        // Get user roles
        $user = get_userdata($user_id);
        if (!$user) {
            return false;
        }

        // Check if user has any of the required roles
        $user_roles = $user->roles;
        foreach ($user_roles as $role) {
            if (in_array($role, $required_roles)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has 2FA enabled on their account
     *
     * @param int $user_id User ID
     * @return bool
     */
    public static function user_has_2fa_enabled($user_id) {
        return get_user_meta($user_id, 'userspn_2fa_enabled', true) === 'yes';
    }

    /**
     * Generate a random secret for TOTP
     *
     * @return string Base32 encoded secret
     */
    public static function generate_secret() {
        $secret = '';
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; // Base32 alphabet
        $length = 16;

        for ($i = 0; $i < $length; $i++) {
            $secret .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $secret;
    }

    /**
     * Generate TOTP code from secret
     *
     * @param string $secret Base32 encoded secret
     * @param int $time_step Current time step (default: current time / 30)
     * @return string 6-digit code
     */
    public static function generate_totp($secret, $time_step = null) {
        if ($time_step === null) {
            $time_step = floor(time() / 30);
        }

        $secret = self::base32_decode($secret);
        $time = pack('N*', 0) . pack('N*', $time_step);
        $hash = hash_hmac('sha1', $time, $secret, true);
        $offset = ord($hash[19]) & 0xf;

        $code = (
            ((ord($hash[$offset + 0]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        ) % 1000000;

        return str_pad($code, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Verify TOTP code
     *
     * @param string $secret Base32 encoded secret
     * @param string $code User-provided code
     * @param int $discrepancy Time window to check (default: 1 = 30 seconds before/after)
     * @return bool
     */
    public static function verify_totp($secret, $code, $discrepancy = 1) {
        $current_time_step = floor(time() / 30);

        // Check current time and time steps before/after to account for clock drift
        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $generated_code = self::generate_totp($secret, $current_time_step + $i);
            if (hash_equals($generated_code, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Base32 decode
     *
     * @param string $data Base32 encoded string
     * @return string
     */
    private static function base32_decode($data) {
        $data = strtoupper($data);
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $output = '';
        $v = 0;
        $vbits = 0;

        for ($i = 0, $j = strlen($data); $i < $j; $i++) {
            $v <<= 5;
            $v += stripos($alphabet, $data[$i]);
            $vbits += 5;

            while ($vbits >= 8) {
                $vbits -= 8;
                $output .= chr($v >> $vbits);
                $v &= ((1 << $vbits) - 1);
            }
        }

        return $output;
    }

    /**
     * Get QR code data URL for TOTP setup
     *
     * @param int $user_id User ID
     * @param string $secret Base32 encoded secret
     * @return string QR code data URL
     */
    public static function get_qr_code_url($user_id, $secret) {
        $user = get_userdata($user_id);
        if (!$user) {
            return '';
        }

        $site_name = get_bloginfo('name');
        $label = rawurlencode($site_name) . ':' . rawurlencode($user->user_email);
        $otpauth_url = 'otpauth://totp/' . $label . '?secret=' . $secret . '&issuer=' . rawurlencode($site_name);

        // Use Google Charts API to generate QR code
        $qr_code_url = 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=' . urlencode($otpauth_url);

        return $qr_code_url;
    }

    /**
     * Generate backup codes for user
     *
     * @param int $count Number of backup codes to generate
     * @return array Array of backup codes
     */
    public static function generate_backup_codes($count = 10) {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            // Generate 8-character alphanumeric code
            $code = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8));
            // Format as XXXX-XXXX
            $codes[] = substr($code, 0, 4) . '-' . substr($code, 4, 4);
        }

        return $codes;
    }

    /**
     * Save backup codes for user
     *
     * @param int $user_id User ID
     * @param array $codes Array of backup codes
     * @return bool
     */
    public static function save_backup_codes($user_id, $codes) {
        // Hash codes before storing
        $hashed_codes = array_map(function($code) {
            return wp_hash_password($code);
        }, $codes);

        return update_user_meta($user_id, 'userspn_2fa_backup_codes', $hashed_codes);
    }

    /**
     * Verify and consume a backup code
     *
     * @param int $user_id User ID
     * @param string $code Backup code to verify
     * @return bool
     */
    public static function verify_backup_code($user_id, $code) {
        $stored_codes = get_user_meta($user_id, 'userspn_2fa_backup_codes', true);

        if (!is_array($stored_codes) || empty($stored_codes)) {
            return false;
        }

        // Remove any formatting characters
        $code = strtoupper(str_replace(['-', ' '], '', $code));

        foreach ($stored_codes as $index => $hashed_code) {
            if (wp_check_password($code, $hashed_code)) {
                // Remove used code
                unset($stored_codes[$index]);
                $remaining_codes = array_values($stored_codes);
                update_user_meta($user_id, 'userspn_2fa_backup_codes', $remaining_codes);

                // Send notification email about backup code usage
                $userspn_mailing = new USERSPN_Mailing();
                $userspn_mailing->userspn_send_2fa_backup_code_used_email($user_id, count($remaining_codes));

                return true;
            }
        }

        return false;
    }

    /**
     * Get remaining backup codes count
     *
     * @param int $user_id User ID
     * @return int
     */
    public static function get_backup_codes_count($user_id) {
        $codes = get_user_meta($user_id, 'userspn_2fa_backup_codes', true);
        return is_array($codes) ? count($codes) : 0;
    }

    /**
     * Enable 2FA for user with TOTP
     *
     * @param int $user_id User ID
     * @param string $secret Base32 encoded secret
     * @return bool
     */
    public static function enable_totp_for_user($user_id, $secret) {
        update_user_meta($user_id, 'userspn_2fa_enabled', 'yes');
        update_user_meta($user_id, 'userspn_2fa_method', 'app');
        update_user_meta($user_id, 'userspn_2fa_secret', $secret);
        update_user_meta($user_id, 'userspn_2fa_enabled_at', current_time('timestamp'));

        // Generate and save backup codes
        $backup_codes = self::generate_backup_codes();
        self::save_backup_codes($user_id, $backup_codes);

        if (class_exists('USERSPN_Security')) {
            USERSPN_Security::log_audit_event('2fa_enabled', '2FA (TOTP) enabled', [
                'method' => 'app',
            ], $user_id);
        }

        return true;
    }

    /**
     * Enable 2FA for user with email
     *
     * @param int $user_id User ID
     * @return bool
     */
    public static function enable_email_2fa_for_user($user_id) {
        update_user_meta($user_id, 'userspn_2fa_enabled', 'yes');
        update_user_meta($user_id, 'userspn_2fa_method', 'email');
        update_user_meta($user_id, 'userspn_2fa_enabled_at', current_time('timestamp'));

        // Generate and save backup codes
        $backup_codes = self::generate_backup_codes();
        self::save_backup_codes($user_id, $backup_codes);

        if (class_exists('USERSPN_Security')) {
            USERSPN_Security::log_audit_event('2fa_enabled', '2FA (email) enabled', [
                'method' => 'email',
            ], $user_id);
        }

        return true;
    }

    /**
     * Disable 2FA for user
     *
     * @param int $user_id User ID
     * @return bool
     */
    public static function disable_2fa_for_user($user_id) {
        $method = get_user_meta($user_id, 'userspn_2fa_method', true);

        delete_user_meta($user_id, 'userspn_2fa_enabled');
        delete_user_meta($user_id, 'userspn_2fa_method');
        delete_user_meta($user_id, 'userspn_2fa_secret');
        delete_user_meta($user_id, 'userspn_2fa_backup_codes');
        delete_user_meta($user_id, 'userspn_2fa_enabled_at');
        delete_user_meta($user_id, 'userspn_2fa_email_code');
        delete_user_meta($user_id, 'userspn_2fa_email_code_expiry');
        delete_user_meta($user_id, 'userspn_2fa_grace_period_start');

        if (class_exists('USERSPN_Security')) {
            USERSPN_Security::log_audit_event('2fa_disabled', '2FA disabled', [
                'previous_method' => $method,
            ], $user_id);
        }

        return true;
    }

    /**
     * Generate and send email verification code for 2FA
     *
     * @param int $user_id User ID
     * @return bool|WP_Error
     */
    public static function send_email_2fa_code($user_id) {
        $user = get_userdata($user_id);
        if (!$user) {
            return new WP_Error('invalid_user', __('Invalid user.', 'userspn'));
        }

        // Rate limiting: verificar último envío
        $last_sent = get_user_meta($user_id, 'userspn_2fa_email_code_last_sent', true);
        $current_time = current_time('timestamp');

        if ($last_sent && ($current_time - $last_sent) < 60) {
            $wait_time = 60 - ($current_time - $last_sent);
            return new WP_Error('rate_limit', sprintf(
                // translators: %d is the number of seconds to wait before requesting a new code.
                __('Please wait %d seconds before requesting a new code.', 'userspn'),
                $wait_time
            ));
        }

        // Generate 6-digit code
        $code = sprintf('%06d', wp_rand(0, 999999));
        $expiry = $current_time + 600; // 10 minutes

        // Save to user meta
        update_user_meta($user_id, 'userspn_2fa_email_code', wp_hash_password($code));
        update_user_meta($user_id, 'userspn_2fa_email_code_expiry', $expiry);
        update_user_meta($user_id, 'userspn_2fa_email_code_last_sent', $current_time);

        // Send email
        $site_name = get_bloginfo('name');
        // translators: %s is the site name.
        $subject = sprintf(__('[%s] Two-Factor Authentication Code', 'userspn'), $site_name);
        $message = sprintf(
            // translators: %s is the user's display name.
            __('Hello %s,', 'userspn') . "\n\n" .
            // translators: %s is the two-factor authentication code.
            __('Your two-factor authentication code is: %s', 'userspn') . "\n\n" .
            __('This code will expire in 10 minutes.', 'userspn') . "\n\n" .
            __('If you did not request this code, please ignore this email or contact support if you have concerns.', 'userspn') . "\n\n" .
            __('Best regards,', 'userspn') . "\n" .
            '%s',
            $user->display_name,
            $code,
            $site_name
        );

        $sent = wp_mail($user->user_email, $subject, $message);

        if (!$sent) {
            return new WP_Error('email_failed', __('Failed to send verification code.', 'userspn'));
        }

        return true;
    }

    /**
     * Verify email 2FA code
     *
     * @param int $user_id User ID
     * @param string $code User-provided code
     * @return bool
     */
    public static function verify_email_2fa_code($user_id, $code) {
        $stored_code = get_user_meta($user_id, 'userspn_2fa_email_code', true);
        $expiry = get_user_meta($user_id, 'userspn_2fa_email_code_expiry', true);

        if (empty($stored_code) || empty($expiry)) {
            return false;
        }

        // Check expiry
        if (current_time('timestamp') > $expiry) {
            // Clean up expired code
            delete_user_meta($user_id, 'userspn_2fa_email_code');
            delete_user_meta($user_id, 'userspn_2fa_email_code_expiry');
            return false;
        }

        // Verify code
        if (wp_check_password($code, $stored_code)) {
            // Clean up used code
            delete_user_meta($user_id, 'userspn_2fa_email_code');
            delete_user_meta($user_id, 'userspn_2fa_email_code_expiry');
            return true;
        }

        return false;
    }

    /**
     * Verify 2FA code (auto-detects method)
     *
     * @param int $user_id User ID
     * @param string $code Code to verify
     * @return bool
     */
    public static function verify_2fa_code($user_id, $code) {
        $method = get_user_meta($user_id, 'userspn_2fa_method', true);

        // Try backup code first
        if (self::verify_backup_code($user_id, $code)) {
            return true;
        }

        // Verify based on method
        if ($method === 'email') {
            return self::verify_email_2fa_code($user_id, $code);
        } else {
            // TOTP
            $secret = get_user_meta($user_id, 'userspn_2fa_secret', true);
            if (empty($secret)) {
                return false;
            }
            return self::verify_totp($secret, $code);
        }
    }

    /**
     * Check if user is in grace period for 2FA setup
     *
     * @param int $user_id User ID
     * @return bool
     */
    public static function is_in_grace_period($user_id) {
        if (!self::is_2fa_required_for_user($user_id)) {
            return false;
        }

        if (self::user_has_2fa_enabled($user_id)) {
            return false;
        }

        $grace_period_days = intval(get_option('userspn_2fa_grace_period', 7));
        if ($grace_period_days <= 0) {
            return false; // No grace period
        }

        $grace_start = get_user_meta($user_id, 'userspn_2fa_grace_period_start', true);
        if (empty($grace_start)) {
            // First time checking - set grace period start
            $grace_start = current_time('timestamp');
            update_user_meta($user_id, 'userspn_2fa_grace_period_start', $grace_start);
        }

        $grace_end = $grace_start + ($grace_period_days * DAY_IN_SECONDS);
        return current_time('timestamp') < $grace_end;
    }

    /**
     * Get remaining days in grace period
     *
     * @param int $user_id User ID
     * @return int Days remaining, or 0 if not in grace period
     */
    public static function get_grace_period_remaining_days($user_id) {
        if (!self::is_in_grace_period($user_id)) {
            return 0;
        }

        $grace_period_days = intval(get_option('userspn_2fa_grace_period', 7));
        $grace_start = get_user_meta($user_id, 'userspn_2fa_grace_period_start', true);

        if (empty($grace_start)) {
            return $grace_period_days;
        }

        $grace_end = $grace_start + ($grace_period_days * DAY_IN_SECONDS);
        $remaining_seconds = $grace_end - current_time('timestamp');

        return max(0, ceil($remaining_seconds / DAY_IN_SECONDS));
    }
}
