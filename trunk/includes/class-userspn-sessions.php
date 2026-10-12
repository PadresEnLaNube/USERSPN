<?php
/**
 * Session management for USERSPN plugin.
 *
 * Provides UI and API for viewing and revoking WordPress user sessions.
 *
 * @link       padresenlanube.com/
 * @since      1.1.61
 * @package    USERSPN
 * @subpackage USERSPN/includes
 * @author     Padres en la Nube <info@padresenlanube.com>
 */

class USERSPN_Sessions {

    /**
     * Enrich session token with extra metadata on login
     *
     * @param string $user_login User login name
     * @param WP_User $user User object
     */
    public function enrich_session_on_login($user_login, $user) {
        if (get_option('userspn_sessions_enabled', 'on') !== 'on') {
            return;
        }

        $manager = WP_Session_Tokens::get_instance($user->ID);
        $token = wp_get_session_token();
        if (empty($token)) {
            return;
        }

        $session = $manager->get($token);
        if (!$session) {
            return;
        }

        // Add device name parsed from UA
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $session['device_name'] = self::parse_device_name($ua);
        $session['login_time'] = time();

        $manager->update($token, $session);
    }

    /**
     * Get all sessions for a user
     *
     * @param int $user_id
     * @return array
     */
    public static function get_user_sessions($user_id) {
        $manager = WP_Session_Tokens::get_instance($user_id);
        $sessions = $manager->get_all();
        $result = [];

        foreach ($sessions as $token_hash => $session) {
            $result[] = [
                'token_hash' => $token_hash,
                'ip' => $session['ip'] ?? '',
                'ua' => $session['ua'] ?? '',
                'login' => $session['login'] ?? 0,
                'expiration' => $session['expiration'] ?? 0,
                'device_name' => $session['device_name'] ?? self::parse_device_name($session['ua'] ?? ''),
                'login_time' => $session['login_time'] ?? ($session['login'] ?? 0),
            ];
        }

        return $result;
    }

    /**
     * Revoke a specific session
     *
     * @param int $user_id
     * @param string $token_hash
     * @return bool
     */
    public static function revoke_session($user_id, $token_hash) {
        $manager = WP_Session_Tokens::get_instance($user_id);
        $manager->destroy($token_hash);

        if (class_exists('USERSPN_Security')) {
            USERSPN_Security::log_audit_event('session_revoked', 'Session revoked', [
                'target_user_id' => $user_id,
                'token_hash' => substr($token_hash, 0, 8) . '...',
            ], get_current_user_id());
        }

        return true;
    }

    /**
     * Revoke all sessions for a user
     *
     * @param int $user_id
     * @param bool $except_current Whether to keep the current session
     * @return bool
     */
    public static function revoke_all_sessions($user_id, $except_current = false) {
        $manager = WP_Session_Tokens::get_instance($user_id);

        if ($except_current && $user_id === get_current_user_id()) {
            $manager->destroy_others(wp_get_session_token());
        } else {
            $manager->destroy_all();
        }

        if (class_exists('USERSPN_Security')) {
            USERSPN_Security::log_audit_event('session_revoked', 'All sessions revoked', [
                'target_user_id' => $user_id,
                'except_current' => $except_current,
            ], get_current_user_id());
        }

        return true;
    }

    /**
     * Parse device name from User Agent string
     *
     * @param string $ua
     * @return string
     */
    public static function parse_device_name($ua) {
        if (empty($ua)) {
            return __('Unknown device', 'userspn');
        }

        $browser = __('Unknown browser', 'userspn');
        $os = __('Unknown OS', 'userspn');

        // Detect browser
        if (preg_match('/Edg[e\/]/', $ua)) {
            $browser = 'Edge';
        } elseif (preg_match('/OPR|Opera/', $ua)) {
            $browser = 'Opera';
        } elseif (preg_match('/Chrome/', $ua)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Safari/', $ua) && !preg_match('/Chrome/', $ua)) {
            $browser = 'Safari';
        } elseif (preg_match('/Firefox/', $ua)) {
            $browser = 'Firefox';
        } elseif (preg_match('/MSIE|Trident/', $ua)) {
            $browser = 'Internet Explorer';
        }

        // Detect OS
        if (preg_match('/Windows/', $ua)) {
            $os = 'Windows';
        } elseif (preg_match('/Macintosh|Mac OS/', $ua)) {
            $os = 'macOS';
        } elseif (preg_match('/Linux/', $ua)) {
            $os = 'Linux';
        } elseif (preg_match('/iPhone/', $ua)) {
            $os = 'iPhone';
        } elseif (preg_match('/iPad/', $ua)) {
            $os = 'iPad';
        } elseif (preg_match('/Android/', $ua)) {
            $os = 'Android';
        }

        return $browser . ' / ' . $os;
    }

    /**
     * Get the current session token hash
     *
     * @return string
     */
    public static function get_current_token_hash() {
        $token = wp_get_session_token();
        if (empty($token)) {
            return '';
        }
        return wp_hash($token);
    }
}
