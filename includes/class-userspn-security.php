<?php
/**
 * Security handler for USERSPN plugin.
 *
 * This class handles security validations including reCAPTCHA, Akismet, honeypot, and rate limiting.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    USERSPN
 * @subpackage USERSPN/includes
 * @author     Padres en la Nube <info@padresenlanube.com>
 */

class USERSPN_Security {

    /**
     * Verify Google reCAPTCHA v3 token
     *
     * @param string $token The reCAPTCHA token
     * @param string $action The action name
     * @return array|WP_Error Array with success status and score, or WP_Error if invalid
     */
    public static function verify_recaptcha($token, $action = 'register') {
        $secret_key = get_option('userspn_recaptcha_secret_key');
        $threshold = floatval(get_option('userspn_recaptcha_threshold', 0.5));
        
        if (empty($secret_key) || empty($token)) {
            return new WP_Error('recaptcha_missing', __('reCAPTCHA verification failed: Missing credentials or token.', 'userspn'));
        }

        $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
            'body' => [
                'secret' => $secret_key,
                'response' => $token,
                'remoteip' => $_SERVER['REMOTE_ADDR']
            ]
        ]);

        if (is_wp_error($response)) {
            return new WP_Error('recaptcha_request_failed', __('reCAPTCHA verification failed: Unable to connect to Google.', 'userspn'));
        }

        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);

        if (!$result['success']) {
            return new WP_Error('recaptcha_verification_failed', __('reCAPTCHA verification failed: Invalid token.', 'userspn'));
        }

        if ($result['action'] !== $action) {
            return new WP_Error('recaptcha_action_mismatch', __('reCAPTCHA verification failed: Action mismatch.', 'userspn'));
        }

        // Return result with score information instead of blocking
        return [
            'success' => true,
            'score' => floatval($result['score']),
            'threshold' => $threshold,
            'is_suspicious' => $result['score'] < $threshold
        ];
    }

    /**
     * Check if Akismet is available and verify content
     *
     * @param array $data User registration data
     * @return bool|WP_Error True if not spam, WP_Error if spam
     */
    public static function verify_akismet($data) {
        // Check if Akismet plugin is active
        if (!function_exists('akismet_http_post')) {
            return new WP_Error('akismet_not_available', __('Akismet plugin is not active.', 'userspn'));
        }

        // Get Akismet API key
        $akismet_key = get_option('wordpress_api_key');
        if (empty($akismet_key)) {
            return new WP_Error('akismet_no_key', __('Akismet API key not found.', 'userspn'));
        }

        // Prepare data for Akismet
        $akismet_data = [
            'blog' => get_option('home'),
            'user_ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'referrer' => $_SERVER['HTTP_REFERER'] ?? '',
            'permalink' => get_permalink(),
            'comment_type' => 'registration',
            'comment_author' => $data['first_name'] . ' ' . $data['last_name'],
            'comment_author_email' => $data['email'],
            'comment_content' => $data['description'] ?? '',
        ];

        $response = akismet_http_post(build_query($akismet_data), 'comment-check', $akismet_key);
        
        if ($response[1] === 'true') {
            return new WP_Error('akismet_spam', __('Registration blocked: Content appears to be spam.', 'userspn'));
        }

        return true;
    }

    /**
     * Check honeypot field
     *
     * @param array $post_data POST data
     * @return bool|WP_Error True if valid, WP_Error if bot detected
     */
    public static function verify_honeypot($post_data) {
        $honeypot_field = 'userspn_honeypot_field';
        
        if (isset($post_data[$honeypot_field]) && !empty($post_data[$honeypot_field])) {
            return new WP_Error('honeypot_triggered', __('Registration blocked: Bot detected by honeypot.', 'userspn'));
        }

        return true;
    }

    /**
     * Check rate limiting
     *
     * @param string $ip_address IP address
     * @return bool|WP_Error True if within limits, WP_Error if rate limited
     */
    public static function check_rate_limit($ip_address) {
        $max_attempts = intval(get_option('userspn_rate_limit_attempts', 5));
        $window_hours = intval(get_option('userspn_rate_limit_window', 1));
        
        if ($max_attempts <= 0) {
            return true; // Rate limiting disabled
        }

        $cache_key = 'userspn_rate_limit_' . md5($ip_address);
        $attempts = get_transient($cache_key);
        
        if ($attempts === false) {
            $attempts = 0;
        }

        if ($attempts >= $max_attempts) {
            return new WP_Error('rate_limit_exceeded', sprintf(__('Registration blocked: Too many attempts. Please try again in %d hour(s).', 'userspn'), $window_hours));
        }

        // Increment attempts
        set_transient($cache_key, $attempts + 1, $window_hours * HOUR_IN_SECONDS);

        return true;
    }

    /**
     * Perform comprehensive security validation
     *
     * @param array $post_data POST data from registration form
     * @param array $user_data User data array
     * @return bool|WP_Error True if all validations pass, WP_Error if any fail
     */
    public static function validate_registration_security($post_data, $user_data) {
        $ip_address = $_SERVER['REMOTE_ADDR'];
        
        // Check rate limiting first
        if (get_option('userspn_rate_limiting_enabled') === 'on') {
            $rate_limit_result = self::check_rate_limit($ip_address);
            if (is_wp_error($rate_limit_result)) {
                return $rate_limit_result;
            }
        }

        // Check honeypot
        if (get_option('userspn_honeypot_enabled') === 'on') {
            $honeypot_result = self::verify_honeypot($post_data);
            if (is_wp_error($honeypot_result)) {
                return $honeypot_result;
            }
        }

        // Check reCAPTCHA
        if (get_option('userspn_recaptcha_enabled') === 'on') {
            $recaptcha_token = $post_data['g-recaptcha-response'] ?? '';
            $recaptcha_result = self::verify_recaptcha($recaptcha_token, 'register');
            if (is_wp_error($recaptcha_result)) {
                return $recaptcha_result;
            }
        }

        // Check Akismet
        if (get_option('userspn_akismet_enabled') === 'on') {
            $akismet_result = self::verify_akismet($user_data);
            if (is_wp_error($akismet_result)) {
                return $akismet_result;
            }
        }

        return true;
    }

    /**
     * Get user's IP address
     *
     * @return string IP address
     */
    public static function get_user_ip() {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Log security events
     *
     * @param string $event Event type
     * @param string $message Event message
     * @param array $data Additional data
     */
    public static function log_security_event($event, $message, $data = []) {
        $log_data = [
            'timestamp' => current_time('mysql'),
            'ip_address' => self::get_user_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'event' => $event,
            'message' => $message,
            'data' => $data
        ];

        // Store in WordPress options (you might want to use a proper logging system)
        $security_logs = get_option('userspn_security_logs', []);
        $security_logs[] = $log_data;
        
        // Keep only last 100 entries
        if (count($security_logs) > 100) {
            $security_logs = array_slice($security_logs, -100);
        }
        
        update_option('userspn_security_logs', $security_logs);
    }

    /**
     * Analyze existing users for bot patterns
     *
     * @param int $limit Number of users to analyze
     * @return array Analysis results
     */
    public static function analyze_existing_users_for_bots($limit = 100) {
        global $wpdb;
        
        $results = [
            'total_analyzed' => 0,
            'suspicious_users' => [],
            'bot_patterns' => [],
            'analysis_summary' => []
        ];

        // Get users with basic info
        $users = get_users([
            'number' => $limit,
            'orderby' => 'registered',
            'order' => 'DESC',
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => 'userspn_bot_analysis_status',
                    'compare' => 'NOT EXISTS'
                ],
                [
                    'key' => 'userspn_bot_analysis_status',
                    'value' => 'pending',
                    'compare' => '='
                ]
            ]
        ]);

        $results['total_analyzed'] = count($users);

        foreach ($users as $user) {
            $suspicion_score = 0;
            $bot_patterns = [];
            $user_data = [
                'id' => $user->ID,
                'login' => $user->user_login,
                'email' => $user->user_email,
                'registered' => $user->user_registered,
                'display_name' => $user->display_name,
                'first_name' => get_user_meta($user->ID, 'first_name', true),
                'last_name' => get_user_meta($user->ID, 'last_name', true),
                'description' => get_user_meta($user->ID, 'description', true),
                'suspicion_score' => 0,
                'bot_patterns' => []
            ];

            // Pattern 1: Suspicious email patterns
            if (self::is_suspicious_email($user->user_email)) {
                // Lower score for common email providers
                if (preg_match('/@(gmail|yahoo|hotmail)\.com$/', $user->user_email)) {
                    $suspicion_score += 10;
                    $bot_patterns[] = 'Common email provider';
                } else {
                    $suspicion_score += 30;
                    $bot_patterns[] = 'Suspicious email pattern';
                }
            }

            // Pattern 2: Generic or bot-like usernames
            if (self::is_bot_username($user->user_login)) {
                $suspicion_score += 25;
                $bot_patterns[] = 'Bot-like username';
            }

            // Pattern 3: Empty or generic profile data
            if (self::has_empty_profile($user_data)) {
                $suspicion_score += 20;
                $bot_patterns[] = 'Empty profile data';
            }

            // Pattern 4: Rapid registration (multiple users from same IP)
            $ip_registrations = self::count_registrations_from_ip($user->ID);
            if ($ip_registrations > 3) {
                $suspicion_score += 35;
                $bot_patterns[] = "Multiple registrations from same IP ({$ip_registrations})";
            }

            // Pattern 5: Suspicious user agent patterns
            $user_agent = get_user_meta($user->ID, 'userspn_registration_user_agent', true);
            if ($user_agent && self::is_suspicious_user_agent($user_agent)) {
                $suspicion_score += 25;
                $bot_patterns[] = 'Suspicious user agent';
            }

            // Pattern 6: No activity since registration
            if (self::has_no_activity($user->ID)) {
                $suspicion_score += 15;
                $bot_patterns[] = 'No activity since registration';
            }

            // Pattern 7: Sequential or pattern-based emails
            if (self::is_sequential_email($user->user_email)) {
                $suspicion_score += 40;
                $bot_patterns[] = 'Sequential email pattern';
            }
            
            // Pattern 8: Generic display names
            if (in_array(strtolower($user->display_name), ['user', 'admin', 'test', 'demo', 'guest', 'anonymous', 'unknown'])) {
                $suspicion_score += 20;
                $bot_patterns[] = 'Generic display name';
            }
            
            // Pattern 9: Very recent registration (potential bot)
            $registration_time = strtotime($user->user_registered);
            $current_time = current_time('timestamp');
            $days_since_registration = ($current_time - $registration_time) / (24 * 60 * 60);
            
            if ($days_since_registration < 1) {
                $suspicion_score += 15;
                $bot_patterns[] = 'Very recent registration';
            }

            $user_data['suspicion_score'] = $suspicion_score;
            $user_data['bot_patterns'] = $bot_patterns;

            // Always add user to results for comprehensive analysis
            $results['suspicious_users'][] = $user_data;
            $results['bot_patterns'] = array_merge($results['bot_patterns'], $bot_patterns);

            // Update analysis status
            update_user_meta($user->ID, 'userspn_bot_analysis_status', 'analyzed');
            update_user_meta($user->ID, 'userspn_bot_suspicion_score', $suspicion_score);
            update_user_meta($user->ID, 'userspn_bot_patterns', $bot_patterns);
        }

        // Generate summary
        $high_suspicion_users = array_filter($results['suspicious_users'], function($user) {
            return $user['suspicion_score'] >= 30;
        });
        
        $results['analysis_summary'] = [
            'total_users' => $results['total_analyzed'],
            'suspicious_count' => count($high_suspicion_users),
            'suspicion_rate' => $results['total_analyzed'] > 0 ? round((count($high_suspicion_users) / $results['total_analyzed']) * 100, 2) : 0,
            'common_patterns' => array_count_values($results['bot_patterns']),
            'analysis_date' => current_time('mysql'),
            'all_users' => $results['suspicious_users'] // Include all users for charts
        ];

        // Store analysis results
        update_option('userspn_last_bot_analysis', $results);

        return $results;
    }

    /**
     * Check if email has suspicious patterns
     */
    private static function is_suspicious_email($email) {
        $suspicious_patterns = [
            '/^[a-z]+\d+@/', // letter+number pattern
            '/^\d+[a-z]+@/', // number+letter pattern
            '/@(tempmail|10minutemail|guerrillamail|mailinator|temp-mail|throwaway)\./', // temp email services
            '/^[a-z]{1,3}\d{1,3}@/', // very short patterns
            '/^(test|demo|example|sample|fake|dummy)\d*@/', // test patterns
            '/^[a-z]{2,4}\d{2,4}@/', // short letter+number combinations
            '/@(gmail|yahoo|hotmail)\.com$/', // common free email providers (lower suspicion)
        ];

        foreach ($suspicious_patterns as $pattern) {
            if (preg_match($pattern, $email)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if username looks bot-like
     */
    private static function is_bot_username($username) {
        $bot_patterns = [
            '/^[a-z]+\d+$/', // letter+number pattern
            '/^\d+[a-z]+$/', // number+letter pattern
            '/^user\d+$/', // user123 pattern
            '/^test\d+$/', // test123 pattern
            '/^bot\d+$/', // bot123 pattern
            '/^[a-z]{1,3}\d{1,3}$/', // very short patterns
            '/^[a-z]+\d+[a-z]*$/', // abc123def
            '/^\d+[a-z]+\d*$/', // 123abc456
            '/^(admin|guest|demo|temp|spam|fake|dummy)\d*$/', // common bot prefixes
            '/^[a-z]{2,4}\d{2,4}$/', // ab12, xyz1234
        ];

        foreach ($bot_patterns as $pattern) {
            if (preg_match($pattern, $username)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has empty profile
     */
    private static function has_empty_profile($user_data) {
        $empty_fields = 0;
        $total_fields = 0;

        $fields_to_check = ['first_name', 'last_name', 'description', 'display_name'];
        
        foreach ($fields_to_check as $field) {
            $total_fields++;
            if (empty($user_data[$field])) {
                $empty_fields++;
            }
        }

        return ($empty_fields / $total_fields) >= 0.75; // 75% empty fields
    }

    /**
     * Count registrations from same IP
     */
    private static function count_registrations_from_ip($user_id) {
        global $wpdb;
        
        $registration_ip = get_user_meta($user_id, 'userspn_registration_ip', true);
        if (!$registration_ip) {
            return 0;
        }

        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->usermeta} 
             WHERE meta_key = 'userspn_registration_ip' 
             AND meta_value = %s",
            $registration_ip
        ));

        return intval($count);
    }

    /**
     * Check if user agent is suspicious
     */
    private static function is_suspicious_user_agent($user_agent) {
        $suspicious_patterns = [
            '/bot/i',
            '/crawler/i',
            '/spider/i',
            '/scraper/i',
            '/curl/i',
            '/wget/i',
            '/python/i',
            '/java/i',
            '/php/i'
        ];

        foreach ($suspicious_patterns as $pattern) {
            if (preg_match($pattern, $user_agent)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has no activity
     */
    private static function has_no_activity($user_id) {
        global $wpdb;
        
        // Check for comments, posts, or other activity
        $comment_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->comments} WHERE user_id = %d",
            $user_id
        ));

        $post_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_author = %d",
            $user_id
        ));

        return ($comment_count == 0 && $post_count == 0);
    }

    /**
     * Check if email follows sequential pattern
     */
    private static function is_sequential_email($email) {
        $username = substr($email, 0, strpos($email, '@'));
        
        // Check for sequential numbers
        if (preg_match('/\d{3,}/', $username)) {
            return true;
        }

        // Check for repeated patterns
        if (preg_match('/(.)\1{2,}/', $username)) {
            return true;
        }

        return false;
    }

    /**
     * Get bot analysis results
     */
    public static function get_bot_analysis_results() {
        return get_option('userspn_last_bot_analysis', []);
    }

    /**
     * Mark user as confirmed bot
     */
    public static function mark_user_as_bot($user_id) {
        update_user_meta($user_id, 'userspn_bot_status', 'confirmed_bot');
        update_user_meta($user_id, 'userspn_bot_confirmed_date', current_time('mysql'));
        
        // Log the action
        self::log_security_event('bot_confirmed', "User {$user_id} marked as confirmed bot", [
            'user_id' => $user_id,
            'action' => 'mark_as_bot'
        ]);
    }

    /**
     * Mark user as confirmed human
     */
    public static function mark_user_as_human($user_id) {
        update_user_meta($user_id, 'userspn_bot_status', 'confirmed_human');
        update_user_meta($user_id, 'userspn_bot_confirmed_date', current_time('mysql'));
        
        // Log the action
        self::log_security_event('human_confirmed', "User {$user_id} marked as confirmed human", [
            'user_id' => $user_id,
            'action' => 'mark_as_human'
        ]);
    }

    /**
     * Delete confirmed bot users
     */
    public static function delete_confirmed_bots() {
        global $wpdb;
        
        $bot_users = $wpdb->get_results(
            "SELECT user_id FROM {$wpdb->usermeta} 
             WHERE meta_key = 'userspn_bot_status' 
             AND meta_value = 'confirmed_bot'"
        );

        $deleted_count = 0;
        foreach ($bot_users as $bot_user) {
            if (wp_delete_user($bot_user->user_id)) {
                $deleted_count++;
            }
        }

        // Log the action
        self::log_security_event('bots_deleted', "Deleted {$deleted_count} confirmed bot users", [
            'deleted_count' => $deleted_count
        ]);

        return $deleted_count;
    }

    /**
     * Send suspicious registration notification email
     *
     * @param int $user_id User ID
     * @param array $recaptcha_data reCAPTCHA data
     * @param array $user_data User registration data
     * @return bool True if email sent successfully
     */
    public static function send_suspicious_registration_notification($user_id, $recaptcha_data, $user_data) {
        $admin_email = get_option('admin_email');
        if (empty($admin_email)) {
            return false;
        }

        $user = get_user_by('ID', $user_id);
        if (!$user) {
            return false;
        }

        $site_name = get_bloginfo('name');
        $site_url = get_site_url();
        
        $subject = sprintf(__('[%s] Posible registro fraudulento detectado', 'userspn'), $site_name);
        
        // Create delete user URL with nonce
        $delete_nonce = wp_create_nonce('userspn_delete_suspicious_user_' . $user_id);
        $delete_url = add_query_arg([
            'action' => 'userspn_delete_suspicious_user',
            'user_id' => $user_id,
            'nonce' => $delete_nonce
        ], admin_url('admin-ajax.php'));

        $message = sprintf(
            __("Se ha detectado un posible registro fraudulento en %s:\n\n", 'userspn') .
            __("Usuario: %s\n", 'userspn') .
            __("Email: %s\n", 'userspn') .
            __("Score reCAPTCHA: %.2f (Umbral: %.2f)\n", 'userspn') .
            __("IP: %s\n", 'userspn') .
            __("User Agent: %s\n", 'userspn') .
            __("Fecha: %s\n\n", 'userspn') .
            __("El usuario ha sido registrado pero marcado como sospechoso.\n\n", 'userspn') .
            __("Para borrar este usuario directamente, haz clic en el siguiente enlace:\n", 'userspn') .
            "%s\n\n" .
            __("Si no haces nada, el usuario podrá acceder normalmente a la web.", 'userspn'),
            $site_name,
            $user->user_login,
            $user->user_email,
            $recaptcha_data['score'],
            $recaptcha_data['threshold'],
            $user_data['ip'] ?? 'Desconocida',
            $user_data['user_agent'] ?? 'Desconocido',
            current_time('mysql'),
            $delete_url
        );

        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . $site_name . ' <' . $admin_email . '>'
        ];

        return wp_mail($admin_email, $subject, $message, $headers);
    }

    /**
     * Handle suspicious user deletion via email link
     *
     * @return void
     */
    public static function handle_suspicious_user_deletion() {
        if (!isset($_GET['action']) || $_GET['action'] !== 'userspn_delete_suspicious_user') {
            return;
        }

        if (!isset($_GET['user_id']) || !isset($_GET['nonce'])) {
            wp_die(__('Parámetros inválidos.', 'userspn'));
        }

        $user_id = intval($_GET['user_id']);
        $nonce = sanitize_text_field($_GET['nonce']);

        if (!wp_verify_nonce($nonce, 'userspn_delete_suspicious_user_' . $user_id)) {
            wp_die(__('Verificación de seguridad fallida.', 'userspn'));
        }

        $user = get_user_by('ID', $user_id);
        if (!$user) {
            wp_die(__('Usuario no encontrado.', 'userspn'));
        }

        // Check if user is marked as suspicious
        $is_suspicious = get_user_meta($user_id, 'userspn_recaptcha_suspicious', true);
        if (!$is_suspicious) {
            wp_die(__('Este usuario no está marcado como sospechoso.', 'userspn'));
        }

        // Delete the user
        if (wp_delete_user($user_id)) {
            // Log the action
            self::log_security_event('suspicious_user_deleted', 'Suspicious user deleted via email link', [
                'user_id' => $user_id,
                'user_email' => $user->user_email,
                'deleted_by' => 'admin_email'
            ]);

            wp_die(
                sprintf(__('Usuario %s eliminado correctamente.', 'userspn'), $user->user_login),
                __('Usuario eliminado', 'userspn'),
                ['response' => 200]
            );
        } else {
            wp_die(__('Error al eliminar el usuario.', 'userspn'));
        }
    }
}
