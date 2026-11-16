<?php
/**
 * GDPR Privacy handler for USERSPN plugin.
 *
 * Integrates with WordPress Tools > Export/Erase Personal Data.
 *
 * @link       padresenlanube.com/
 * @since      1.1.61
 * @package    USERSPN
 * @subpackage USERSPN/includes
 * @author     Padres en la Nube <info@padresenlanube.com>
 */

class USERSPN_Privacy {

    public function __construct() {
        add_filter('wp_privacy_personal_data_exporters', [$this, 'register_exporters']);
        add_filter('wp_privacy_personal_data_erasers', [$this, 'register_erasers']);
    }

    /**
     * Register data exporters
     */
    public function register_exporters($exporters) {
        $exporters['userspn-profile'] = [
            'exporter_friendly_name' => __('UsersPN Profile Data', 'userspn'),
            'callback' => [$this, 'export_user_profile'],
        ];
        $exporters['userspn-login-history'] = [
            'exporter_friendly_name' => __('UsersPN Login History', 'userspn'),
            'callback' => [$this, 'export_user_login_history'],
        ];
        $exporters['userspn-security'] = [
            'exporter_friendly_name' => __('UsersPN Security Data', 'userspn'),
            'callback' => [$this, 'export_user_security'],
        ];
        $exporters['userspn-oauth'] = [
            'exporter_friendly_name' => __('UsersPN OAuth Data', 'userspn'),
            'callback' => [$this, 'export_user_oauth'],
        ];
        return $exporters;
    }

    /**
     * Register data erasers
     */
    public function register_erasers($erasers) {
        $erasers['userspn'] = [
            'eraser_friendly_name' => __('UsersPN Data', 'userspn'),
            'callback' => [$this, 'erase_user_data'],
        ];
        return $erasers;
    }

    /**
     * Export user profile data
     */
    public function export_user_profile($email, $page = 1) {
        $user = get_user_by('email', $email);
        if (!$user) {
            return ['data' => [], 'done' => true];
        }

        $export_items = [];
        $meta_keys = [
            'userspn_child_birthdate' => __('Child Birthdate', 'userspn'),
            'userspn_user_image' => __('Profile Image ID', 'userspn'),
            'userspn_registration_page' => __('Registration Page', 'userspn'),
            'userspn_registration_date' => __('Registration Date', 'userspn'),
            'userspn_registration_method' => __('Registration Method', 'userspn'),
            'userspn_lang' => __('Language', 'userspn'),
            'userspn_newsletter_active' => __('Newsletter Active', 'userspn'),
            'userspn_notifications' => __('Notifications', 'userspn'),
        ];

        $data = [];
        foreach ($meta_keys as $key => $label) {
            $value = get_user_meta($user->ID, $key, true);
            if (!empty($value)) {
                $data[] = ['name' => $label, 'value' => is_array($value) ? wp_json_encode($value) : $value];
            }
        }

        // Include custom registration fields
        $custom_fields = get_option('userspn_user_register_fields', []);
        if (is_array($custom_fields)) {
            foreach ($custom_fields as $field_id => $field_config) {
                $value = get_user_meta($user->ID, $field_id, true);
                if (!empty($value)) {
                    $label = !empty($field_config['label']) ? $field_config['label'] : $field_id;
                    $data[] = ['name' => $label, 'value' => is_array($value) ? wp_json_encode($value) : $value];
                }
            }
        }

        if (!empty($data)) {
            $export_items[] = [
                'group_id' => 'userspn-profile',
                'group_label' => __('UsersPN Profile', 'userspn'),
                'item_id' => 'userspn-profile-' . $user->ID,
                'data' => $data,
            ];
        }

        return ['data' => $export_items, 'done' => true];
    }

    /**
     * Export user login history
     */
    public function export_user_login_history($email, $page = 1) {
        $user = get_user_by('email', $email);
        if (!$user) {
            return ['data' => [], 'done' => true];
        }

        $export_items = [];
        $data = [];

        $last_login = get_user_meta($user->ID, 'userspn_user_last_login', true);
        if (!empty($last_login)) {
            $data[] = ['name' => __('Last Login', 'userspn'), 'value' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $last_login)];
        }

        $current_login = get_user_meta($user->ID, 'userspn_user_current_login', true);
        if (!empty($current_login)) {
            $data[] = ['name' => __('Current Login', 'userspn'), 'value' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $current_login)];
        }

        // Export audit log entries for this user
        global $wpdb;
        $table_name = $wpdb->prefix . 'userspn_security_logs';
        $per_page = 100;
        $offset = ($page - 1) * $per_page;

        $logs = $wpdb->get_results($wpdb->prepare(
            "SELECT created_at, event, message, ip_address FROM $table_name WHERE user_id = %d OR email = %s ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $user->ID, $email, $per_page, $offset
        ));

        foreach ($logs as $log) {
            $data[] = [
                'name' => sprintf('%s - %s', $log->created_at, $log->event),
                'value' => sprintf('%s (IP: %s)', $log->message, $log->ip_address),
            ];
        }

        if (!empty($data)) {
            $export_items[] = [
                'group_id' => 'userspn-login-history',
                'group_label' => __('UsersPN Login History', 'userspn'),
                'item_id' => 'userspn-login-history-' . $user->ID,
                'data' => $data,
            ];
        }

        $done = count($logs) < $per_page;
        return ['data' => $export_items, 'done' => $done];
    }

    /**
     * Export user security data
     */
    public function export_user_security($email, $page = 1) {
        $user = get_user_by('email', $email);
        if (!$user) {
            return ['data' => [], 'done' => true];
        }

        $export_items = [];
        $meta_keys = [
            'userspn_2fa_enabled' => __('2FA Enabled', 'userspn'),
            'userspn_2fa_method' => __('2FA Method', 'userspn'),
            'userspn_2fa_enabled_at' => __('2FA Enabled At', 'userspn'),
            'userspn_registration_ip' => __('Registration IP', 'userspn'),
            'userspn_registration_user_agent' => __('Registration User Agent', 'userspn'),
            'userspn_recaptcha_score' => __('reCAPTCHA Score', 'userspn'),
            'userspn_bot_status' => __('Bot Status', 'userspn'),
        ];

        $data = [];
        foreach ($meta_keys as $key => $label) {
            $value = get_user_meta($user->ID, $key, true);
            if (!empty($value)) {
                $data[] = ['name' => $label, 'value' => $value];
            }
        }

        if (!empty($data)) {
            $export_items[] = [
                'group_id' => 'userspn-security',
                'group_label' => __('UsersPN Security', 'userspn'),
                'item_id' => 'userspn-security-' . $user->ID,
                'data' => $data,
            ];
        }

        return ['data' => $export_items, 'done' => true];
    }

    /**
     * Export user OAuth data
     */
    public function export_user_oauth($email, $page = 1) {
        $user = get_user_by('email', $email);
        if (!$user) {
            return ['data' => [], 'done' => true];
        }

        $export_items = [];
        $providers = ['google', 'facebook', 'github', 'apple', 'x'];
        $data = [];

        foreach ($providers as $provider) {
            $id = get_user_meta($user->ID, 'userspn_' . $provider . '_id', true);
            if (!empty($id)) {
                $data[] = ['name' => ucfirst($provider) . ' ID', 'value' => $id];
            }
            $last_login = get_user_meta($user->ID, 'userspn_' . $provider . '_last_login', true);
            if (!empty($last_login)) {
                $data[] = ['name' => ucfirst($provider) . ' ' . __('Last Login', 'userspn'), 'value' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $last_login)];
            }
        }

        if (!empty($data)) {
            $export_items[] = [
                'group_id' => 'userspn-oauth',
                'group_label' => __('UsersPN OAuth Connections', 'userspn'),
                'item_id' => 'userspn-oauth-' . $user->ID,
                'data' => $data,
            ];
        }

        return ['data' => $export_items, 'done' => true];
    }

    /**
     * Erase user data
     */
    public function erase_user_data($email, $page = 1) {
        $user = get_user_by('email', $email);
        if (!$user) {
            return ['items_removed' => 0, 'items_retained' => 0, 'messages' => [], 'done' => true];
        }

        $items_removed = 0;
        $messages = [];

        // Delete avatar/uploaded image
        $avatar_id = get_user_meta($user->ID, 'userspn_user_image', true);
        if (!empty($avatar_id)) {
            wp_delete_attachment(intval($avatar_id), true);
            $items_removed++;
        }

        // Delete all userspn_* user meta
        global $wpdb;
        $meta_keys = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT meta_key FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key LIKE %s",
            $user->ID, 'userspn_%'
        ));

        foreach ($meta_keys as $key) {
            delete_user_meta($user->ID, $key);
            $items_removed++;
        }

        // Delete passkey credentials
        $passkeys_table = $wpdb->prefix . 'userspn_passkeys';
        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $passkeys_table));
        if ($table_exists) {
            $deleted = $wpdb->delete($passkeys_table, ['user_id' => $user->ID], ['%d']);
            if ($deleted) {
                $items_removed += $deleted;
            }
        }

        // Anonymize security log entries
        $logs_table = $wpdb->prefix . 'userspn_security_logs';
        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $logs_table));
        if ($table_exists) {
            $anonymized = $wpdb->update(
                $logs_table,
                [
                    'email' => wp_hash($email),
                    'ip_address' => '0.0.0.0',
                    'user_agent' => '',
                ],
                ['user_id' => $user->ID],
                ['%s', '%s', '%s'],
                ['%d']
            );
            // Also anonymize by email
            $wpdb->update(
                $logs_table,
                [
                    'email' => wp_hash($email),
                    'ip_address' => '0.0.0.0',
                    'user_agent' => '',
                ],
                ['email' => $email],
                ['%s', '%s', '%s'],
                ['%s']
            );
            if ($anonymized) {
                $items_removed += $anonymized;
                $messages[] = __('Security log entries anonymized.', 'userspn');
            }
        }

        return [
            'items_removed' => $items_removed,
            'items_retained' => 0,
            'messages' => $messages,
            'done' => true,
        ];
    }
}
