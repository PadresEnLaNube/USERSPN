<?php
/**
 * REST API endpoints for USERSPN plugin admin.
 *
 * @link       padresenlanube.com/
 * @since      1.1.61
 * @package    USERSPN
 * @subpackage USERSPN/includes
 * @author     Padres en la Nube <info@padresenlanube.com>
 */

class USERSPN_REST_API {

    const NAMESPACE_V1 = 'userspn/v1';

    public function register_routes() {
        // Users
        register_rest_route(self::NAMESPACE_V1, '/users', [
            'methods' => 'GET',
            'callback' => [$this, 'get_users'],
            'permission_callback' => [$this, 'admin_permission_check'],
            'args' => [
                'page' => ['default' => 1, 'sanitize_callback' => 'absint'],
                'per_page' => ['default' => 20, 'sanitize_callback' => 'absint'],
                'search' => ['default' => '', 'sanitize_callback' => 'sanitize_text_field'],
                'role' => ['default' => '', 'sanitize_callback' => 'sanitize_text_field'],
            ],
        ]);

        register_rest_route(self::NAMESPACE_V1, '/users/(?P<id>\d+)', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_user'],
                'permission_callback' => [$this, 'admin_permission_check'],
            ],
            [
                'methods' => 'PUT',
                'callback' => [$this, 'update_user'],
                'permission_callback' => [$this, 'admin_permission_check'],
            ],
            [
                'methods' => 'DELETE',
                'callback' => [$this, 'delete_user'],
                'permission_callback' => [$this, 'admin_permission_check'],
            ],
        ]);

        // Audit Logs
        register_rest_route(self::NAMESPACE_V1, '/audit-logs', [
            'methods' => 'GET',
            'callback' => [$this, 'get_audit_logs'],
            'permission_callback' => [$this, 'admin_permission_check'],
            'args' => [
                'page' => ['default' => 1, 'sanitize_callback' => 'absint'],
                'per_page' => ['default' => 50, 'sanitize_callback' => 'absint'],
                'event' => ['default' => '', 'sanitize_callback' => 'sanitize_text_field'],
                'user_id' => ['default' => 0, 'sanitize_callback' => 'absint'],
                'date_from' => ['default' => '', 'sanitize_callback' => 'sanitize_text_field'],
                'date_to' => ['default' => '', 'sanitize_callback' => 'sanitize_text_field'],
                'ip' => ['default' => '', 'sanitize_callback' => 'sanitize_text_field'],
            ],
        ]);

        register_rest_route(self::NAMESPACE_V1, '/audit-logs/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_audit_log'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Sessions
        register_rest_route(self::NAMESPACE_V1, '/users/(?P<id>\d+)/sessions', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_user_sessions'],
                'permission_callback' => [$this, 'admin_permission_check'],
            ],
            [
                'methods' => 'DELETE',
                'callback' => [$this, 'revoke_all_user_sessions'],
                'permission_callback' => [$this, 'admin_permission_check'],
            ],
        ]);

        register_rest_route(self::NAMESPACE_V1, '/users/(?P<id>\d+)/sessions/(?P<token>[a-f0-9]+)', [
            'methods' => 'DELETE',
            'callback' => [$this, 'revoke_user_session'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);

        // Settings (read-only)
        register_rest_route(self::NAMESPACE_V1, '/settings', [
            'methods' => 'GET',
            'callback' => [$this, 'get_settings'],
            'permission_callback' => [$this, 'admin_permission_check'],
        ]);
    }

    /**
     * Check admin permissions
     */
    public function admin_permission_check($request) {
        return current_user_can('manage_options');
    }

    // --- Users ---

    public function get_users($request) {
        $args = [
            'number' => min($request['per_page'], 100),
            'paged' => $request['page'],
            'orderby' => 'registered',
            'order' => 'DESC',
        ];

        if (!empty($request['search'])) {
            $args['search'] = '*' . $request['search'] . '*';
            $args['search_columns'] = ['user_login', 'user_email', 'display_name'];
        }

        if (!empty($request['role'])) {
            $args['role'] = $request['role'];
        }

        $query = new WP_User_Query($args);
        $users = [];

        foreach ($query->get_results() as $user) {
            $users[] = $this->format_user($user);
        }

        return new WP_REST_Response([
            'users' => $users,
            'total' => $query->get_total(),
            'pages' => ceil($query->get_total() / $args['number']),
        ], 200);
    }

    public function get_user($request) {
        $user = get_userdata($request['id']);
        if (!$user) {
            return new WP_Error('not_found', __('User not found.', 'userspn'), ['status' => 404]);
        }
        return new WP_REST_Response($this->format_user($user, true), 200);
    }

    public function update_user($request) {
        $user = get_userdata($request['id']);
        if (!$user) {
            return new WP_Error('not_found', __('User not found.', 'userspn'), ['status' => 404]);
        }

        $params = $request->get_json_params();
        $wp_fields = ['first_name', 'last_name', 'display_name', 'user_email'];
        $update_data = ['ID' => $user->ID];

        foreach ($wp_fields as $field) {
            if (isset($params[$field])) {
                $update_data[$field] = sanitize_text_field($params[$field]);
            }
        }

        if (count($update_data) > 1) {
            $result = wp_update_user($update_data);
            if (is_wp_error($result)) {
                return $result;
            }
        }

        // Update userspn meta
        if (!empty($params['meta']) && is_array($params['meta'])) {
            foreach ($params['meta'] as $key => $value) {
                if (strpos($key, 'userspn_') === 0) {
                    update_user_meta($user->ID, sanitize_key($key), sanitize_text_field($value));
                }
            }
        }

        USERSPN_Security::log_audit_event('profile_updated', 'User updated via REST API', [
            'target_user_id' => $user->ID,
        ], get_current_user_id());

        return new WP_REST_Response($this->format_user(get_userdata($user->ID), true), 200);
    }

    public function delete_user($request) {
        $user = get_userdata($request['id']);
        if (!$user) {
            return new WP_Error('not_found', __('User not found.', 'userspn'), ['status' => 404]);
        }

        if (user_can($user->ID, 'administrator')) {
            return new WP_Error('cannot_delete_admin', __('Cannot delete administrator.', 'userspn'), ['status' => 403]);
        }

        require_once(ABSPATH . 'wp-admin/includes/user.php');
        $email = $user->user_email;
        wp_delete_user($user->ID);

        USERSPN_Security::log_audit_event('user_deleted', 'User deleted via REST API', [
            'target_user_id' => $request['id'],
            'email' => $email,
        ], get_current_user_id());

        return new WP_REST_Response(['deleted' => true], 200);
    }

    private function format_user($user, $include_meta = false) {
        $data = [
            'id' => $user->ID,
            'login' => $user->user_login,
            'email' => $user->user_email,
            'display_name' => $user->display_name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'registered' => $user->user_registered,
            'roles' => $user->roles,
        ];

        if ($include_meta) {
            $meta_keys = [
                'userspn_user_last_login', 'userspn_user_current_login',
                'userspn_registration_method', 'userspn_registration_date',
                'userspn_registration_ip', 'userspn_2fa_enabled',
                'userspn_2fa_method', 'userspn_newsletter_active',
                'userspn_bot_status', 'userspn_recaptcha_score',
            ];
            $data['meta'] = [];
            foreach ($meta_keys as $key) {
                $value = get_user_meta($user->ID, $key, true);
                if ($value !== '') {
                    $data['meta'][$key] = $value;
                }
            }
        }

        return $data;
    }

    // --- Audit Logs ---

    public function get_audit_logs($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'userspn_security_logs';

        $where = ['1=1'];
        $values = [];

        if (!empty($request['event'])) {
            $where[] = 'event = %s';
            $values[] = $request['event'];
        }
        if (!empty($request['user_id'])) {
            $where[] = 'user_id = %d';
            $values[] = $request['user_id'];
        }
        if (!empty($request['date_from'])) {
            $where[] = 'created_at >= %s';
            $values[] = $request['date_from'] . ' 00:00:00';
        }
        if (!empty($request['date_to'])) {
            $where[] = 'created_at <= %s';
            $values[] = $request['date_to'] . ' 23:59:59';
        }
        if (!empty($request['ip'])) {
            $where[] = 'ip_address = %s';
            $values[] = $request['ip'];
        }

        $per_page = min($request['per_page'], 100);
        $offset = ($request['page'] - 1) * $per_page;

        $where_sql = implode(' AND ', $where);

        if ( ! empty( $values ) ) {
            $total = $wpdb->get_var(
                $wpdb->prepare(
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $table uses $wpdb->prefix; $where_sql is built from hardcoded column names with prepare() placeholders.
                    "SELECT COUNT(*) FROM $table WHERE $where_sql",
                    $values
                )
            );
            $logs = $wpdb->get_results(
                $wpdb->prepare(
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $table uses $wpdb->prefix; $where_sql is built from hardcoded column names with prepare() placeholders.
                    "SELECT * FROM $table WHERE $where_sql ORDER BY created_at DESC LIMIT %d OFFSET %d",
                    array_merge( $values, [ $per_page, $offset ] )
                )
            );
        } else {
            $total = $wpdb->get_var(
                $wpdb->prepare(
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $table uses $wpdb->prefix; no user input in WHERE clause.
                    "SELECT COUNT(*) FROM $table WHERE 1=1 AND %d = 0",
                    0
                )
            );
            $logs = $wpdb->get_results(
                $wpdb->prepare(
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $table uses $wpdb->prefix; no user input in WHERE clause.
                    "SELECT * FROM $table WHERE 1=1 ORDER BY created_at DESC LIMIT %d OFFSET %d",
                    $per_page,
                    $offset
                )
            );
        }

        $results = [];
        foreach ($logs as $log) {
            $results[] = [
                'id' => intval($log->id),
                'user_id' => intval($log->user_id),
                'created_at' => $log->created_at,
                'ip_address' => $log->ip_address,
                'event' => $log->event,
                'message' => $log->message,
                'data' => json_decode($log->data, true),
                'email' => $log->email,
                'resolved' => (bool) $log->resolved,
            ];
        }

        return new WP_REST_Response([
            'logs' => $results,
            'total' => intval($total),
            'pages' => ceil(intval($total) / $per_page),
        ], 200);
    }

    public function get_audit_log($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'userspn_security_logs';
        $log = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $request['id']));

        if (!$log) {
            return new WP_Error('not_found', __('Log entry not found.', 'userspn'), ['status' => 404]);
        }

        return new WP_REST_Response([
            'id' => intval($log->id),
            'user_id' => intval($log->user_id),
            'created_at' => $log->created_at,
            'ip_address' => $log->ip_address,
            'user_agent' => $log->user_agent,
            'event' => $log->event,
            'message' => $log->message,
            'data' => json_decode($log->data, true),
            'email' => $log->email,
            'resolved' => (bool) $log->resolved,
        ], 200);
    }

    // --- Sessions ---

    public function get_user_sessions($request) {
        $user = get_userdata($request['id']);
        if (!$user) {
            return new WP_Error('not_found', __('User not found.', 'userspn'), ['status' => 404]);
        }

        $sessions = USERSPN_Sessions::get_user_sessions($user->ID);
        return new WP_REST_Response($sessions, 200);
    }

    public function revoke_user_session($request) {
        $user = get_userdata($request['id']);
        if (!$user) {
            return new WP_Error('not_found', __('User not found.', 'userspn'), ['status' => 404]);
        }

        USERSPN_Sessions::revoke_session($user->ID, $request['token']);
        return new WP_REST_Response(['revoked' => true], 200);
    }

    public function revoke_all_user_sessions($request) {
        $user = get_userdata($request['id']);
        if (!$user) {
            return new WP_Error('not_found', __('User not found.', 'userspn'), ['status' => 404]);
        }

        USERSPN_Sessions::revoke_all_sessions($user->ID);
        return new WP_REST_Response(['revoked' => true], 200);
    }

    // --- Settings ---

    /**
     * Sensitive option keys that must never be exposed via the REST API.
     */
    private static $secret_keys = [
        'userspn_google_client_secret',
        'userspn_facebook_app_secret',
        'userspn_github_client_secret',
        'userspn_apple_private_key',
        'userspn_apple_key_id',
        'userspn_x_client_secret',
        'userspn_recaptcha_secret_key',
    ];

    public function get_settings($request) {
        $settings_obj = new USERSPN_Settings();
        $options = $settings_obj->get_options();
        $result = [];

        foreach ($options as $key => $config) {
            if (isset($config['section'])) continue;
            if (isset($config['type']) && in_array($config['type'], ['nonce', 'submit'])) continue;
            // Never expose secrets through the API
            if (in_array($key, self::$secret_keys, true)) continue;

            $value = get_option($key, '');
            if ($value !== '') {
                $result[$key] = $value;
            }
        }

        return new WP_REST_Response($result, 200);
    }
}
