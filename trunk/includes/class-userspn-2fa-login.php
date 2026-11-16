<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Two-Factor Authentication Login Integration for USERSPN plugin.
 *
 * This class handles the integration of 2FA with WordPress login flow.
 *
 * @link       padresenlanube.com/
 * @since      1.1.50
 * @package    USERSPN
 * @subpackage USERSPN/includes
 * @author     Padres en la Nube <info@padresenlanube.com>
 */

class USERSPN_2FA_Login {

    /**
     * Session key for storing user credentials during 2FA verification
     */
    const SESSION_KEY = 'userspn_2fa_pending_user_id';

    /**
     * Initialize the class and set up hooks
     */
    public function __construct() {
        // Start session if not already started (for storing pending user ID)
        add_action('init', [$this, 'maybe_start_session']);

        // Intercept login process
        add_filter('authenticate', [$this, 'intercept_login'], 30, 3);

        // Handle login page display
        add_action('login_form', [$this, 'maybe_show_2fa_form']);
        add_action('login_enqueue_scripts', [$this, 'enqueue_login_scripts']);

        // Handle 2FA verification
        add_action('login_form_userspn_2fa_verify', [$this, 'handle_2fa_verification']);

        // AJAX handlers for login page
        add_action('wp_ajax_nopriv_userspn_2fa_resend_login_code', [$this, 'ajax_resend_login_code']);

        // Handle 2FA cancellation
        add_action('login_init', [$this, 'handle_2fa_cancellation']);
    }

    /**
     * Start session if needed
     */
    public function maybe_start_session() {
        if (!session_id() && !headers_sent()) {
            session_start();
        }
    }

    /**
     * Intercept login after username/password validation
     *
     * @param WP_User|WP_Error|null $user
     * @param string $username
     * @param string $password
     * @return WP_User|WP_Error
     */
    public function intercept_login($user, $username, $password) {
        // If already an error, pass it through
        if (is_wp_error($user)) {
            return $user;
        }

        // If no user object, pass through
        if (!$user instanceof WP_User) {
            return $user;
        }

        // Check if 2FA is globally enabled
        if (!USERSPN_2FA::is_2fa_enabled()) {
            return $user;
        }

        // Check if user has 2FA enabled
        if (!USERSPN_2FA::user_has_2fa_enabled($user->ID)) {
            // Check if 2FA is required for this user
            if (USERSPN_2FA::is_2fa_required_for_user($user->ID)) {
                // Check if in grace period
                if (!USERSPN_2FA::is_in_grace_period($user->ID)) {
                    // 2FA is required but not enabled, and grace period expired
                    return new WP_Error(
                        'userspn_2fa_required',
                        sprintf(
                            __('Two-factor authentication is required for your account. Please contact an administrator or set up 2FA in your profile.', 'userspn')
                        )
                    );
                }
            }
            // User doesn't have 2FA or is in grace period, allow login
            return $user;
        }

        // User has 2FA enabled - store user ID in session and redirect to verification
        if (function_exists('session_regenerate_id')) {
            session_regenerate_id(true);
        }
        $_SESSION[self::SESSION_KEY] = $user->ID;
        $_SESSION['userspn_2fa_username'] = $username;
        $_SESSION['userspn_2fa_attempts'] = 0;

        // Send email code if method is email
        $method = get_user_meta($user->ID, 'userspn_2fa_method', true);
        if ($method === 'email') {
            USERSPN_2FA::send_email_2fa_code($user->ID);
        }

        // Return error to prevent immediate login
        // The error message will be replaced by the 2FA form
        $error = new WP_Error(
            'userspn_2fa_required',
            __('Two-factor authentication required.', 'userspn')
        );

        // Set flag to show 2FA form
        $_SESSION['userspn_show_2fa_form'] = true;

        return $error;
    }

    /**
     * Show 2FA verification form on login page
     */
    public function maybe_show_2fa_form() {
        if (!isset($_SESSION['userspn_show_2fa_form']) || !$_SESSION['userspn_show_2fa_form']) {
            return;
        }

        if (!isset($_SESSION[self::SESSION_KEY])) {
            return;
        }

        $user_id = $_SESSION[self::SESSION_KEY];
        $user = get_userdata($user_id);

        if (!$user) {
            return;
        }

        $method = get_user_meta($user_id, 'userspn_2fa_method', true);

        // Hide the default login form
        ?>
        <style>
            #loginform > p:not(.userspn-2fa-verification),
            #loginform > .user-pass-wrap,
            #loginform > p.forgetmenot,
            #loginform > p.submit {
                display: none !important;
            }
        </style>

        <div class="userspn-2fa-verification">
            <p class="userspn-2fa-info">
                <strong><?php echo esc_html($user->display_name); ?></strong><br>
                <small><?php echo esc_html($user->user_email); ?></small>
            </p>

            <?php if ($method === 'email'): ?>
                <p class="message">
                    <i class="dashicons dashicons-email" style="vertical-align: middle;"></i>
                    <?php echo esc_html__('A verification code has been sent to your email address.', 'userspn'); ?>
                </p>
            <?php else: ?>
                <p class="message">
                    <i class="dashicons dashicons-smartphone" style="vertical-align: middle;"></i>
                    <?php echo esc_html__('Enter the verification code from your authenticator app.', 'userspn'); ?>
                </p>
            <?php endif; ?>

            <p>
                <label for="userspn_2fa_code">
                    <?php echo esc_html__('Verification Code', 'userspn'); ?>
                </label>
                <input
                    type="text"
                    name="userspn_2fa_code"
                    id="userspn_2fa_code"
                    class="input"
                    value=""
                    size="20"
                    maxlength="<?php echo $method === 'email' ? '6' : '8'; ?>"
                    autocomplete="off"
                    placeholder="<?php echo $method === 'email' ? '000000' : 'XXXX-XXXX'; ?>"
                    style="text-align: center; font-size: 18px; letter-spacing: 2px; font-family: monospace;"
                    autofocus
                />
            </p>

            <p class="submit">
                <input
                    type="submit"
                    name="wp-submit"
                    id="wp-submit"
                    class="button button-primary button-large"
                    value="<?php echo esc_attr__('Verify', 'userspn'); ?>"
                />
            </p>

            <p class="userspn-2fa-options">
                <?php if ($method === 'email'): ?>
                    <a href="#" id="userspn-2fa-resend-code" class="userspn-2fa-link">
                        <i class="dashicons dashicons-email-alt" style="vertical-align: middle;"></i>
                        <?php echo esc_html__('Resend code', 'userspn'); ?>
                    </a>
                    <br>
                <?php endif; ?>
                <a href="#" id="userspn-2fa-use-backup" class="userspn-2fa-link">
                    <i class="dashicons dashicons-backup" style="vertical-align: middle;"></i>
                    <?php echo esc_html__('Use backup code', 'userspn'); ?>
                </a>
                <br>
                <a href="<?php echo esc_url( add_query_arg( 'userspn_2fa_cancel', '1', wp_login_url() ) ); ?>" class="userspn-2fa-link">
                    <i class="dashicons dashicons-arrow-left-alt" style="vertical-align: middle;"></i>
                    <?php echo esc_html__('Back to login', 'userspn'); ?>
                </a>
            </p>

            <div id="userspn-2fa-backup-section" style="display: none;">
                <p>
                    <label for="userspn_2fa_backup_code">
                        <?php echo esc_html__('Backup Code', 'userspn'); ?>
                    </label>
                    <input
                        type="text"
                        name="userspn_2fa_backup_code"
                        id="userspn_2fa_backup_code"
                        class="input"
                        value=""
                        size="20"
                        maxlength="9"
                        autocomplete="off"
                        placeholder="XXXX-XXXX"
                        style="text-align: center; font-size: 18px; letter-spacing: 2px; font-family: monospace;"
                    />
                </p>
                <p class="submit">
                    <input
                        type="submit"
                        name="wp-submit-backup"
                        id="wp-submit-backup"
                        class="button button-primary button-large"
                        value="<?php echo esc_attr__('Verify Backup Code', 'userspn'); ?>"
                    />
                </p>
                <p>
                    <a href="#" id="userspn-2fa-cancel-backup" class="userspn-2fa-link">
                        <?php echo esc_html__('Cancel', 'userspn'); ?>
                    </a>
                </p>
            </div>

            <input type="hidden" name="action" value="userspn_2fa_verify">
            <input type="hidden" name="userspn_2fa_nonce" value="<?php echo esc_attr( wp_create_nonce('userspn_2fa_verify') ); ?>">
        </div>
        <?php
    }

    /**
     * Enqueue scripts for login page
     */
    public function enqueue_login_scripts() {
        if (!isset($_SESSION['userspn_show_2fa_form']) || !$_SESSION['userspn_show_2fa_form']) {
            return;
        }

        ?>
        <style>
            .userspn-2fa-verification {
                margin-top: 20px;
            }
            .userspn-2fa-info {
                text-align: center;
                padding: 15px;
                background: #f0f0f1;
                border-radius: 4px;
                margin-bottom: 20px;
            }
            .userspn-2fa-info strong {
                font-size: 16px;
            }
            .userspn-2fa-info small {
                color: #646970;
            }
            .userspn-2fa-options {
                text-align: center;
                margin-top: 15px;
            }
            .userspn-2fa-link {
                text-decoration: none;
                color: #2271b1;
                font-size: 13px;
            }
            .userspn-2fa-link:hover {
                color: #135e96;
            }
            .userspn-2fa-link .dashicons {
                font-size: 16px;
                width: 16px;
                height: 16px;
            }
            #userspn_2fa_code::-webkit-inner-spin-button,
            #userspn_2fa_code::-webkit-outer-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }
            #userspn_2fa_code[type=number] {
                -moz-appearance: textfield;
            }
        </style>

        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function() {
                var useBackupBtn = document.getElementById('userspn-2fa-use-backup');
                var cancelBackupBtn = document.getElementById('userspn-2fa-cancel-backup');
                var backupSection = document.getElementById('userspn-2fa-backup-section');
                var normalInput = document.getElementById('userspn_2fa_code');
                var normalSubmit = document.getElementById('wp-submit');
                var resendBtn = document.getElementById('userspn-2fa-resend-code');

                if (useBackupBtn) {
                    useBackupBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        normalInput.closest('p').style.display = 'none';
                        normalSubmit.closest('p').style.display = 'none';
                        backupSection.style.display = 'block';
                        document.getElementById('userspn_2fa_backup_code').focus();
                    });
                }

                if (cancelBackupBtn) {
                    cancelBackupBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        backupSection.style.display = 'none';
                        normalInput.closest('p').style.display = 'block';
                        normalSubmit.closest('p').style.display = 'block';
                        normalInput.focus();
                    });
                }

                if (resendBtn) {
                    resendBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        var btn = this;
                        var originalText = btn.innerHTML;
                        btn.innerHTML = '<i class="dashicons dashicons-update spin" style="vertical-align: middle;"></i> Enviando...';
                        btn.style.pointerEvents = 'none';

                        fetch('<?php echo esc_url( admin_url('admin-ajax.php') ); ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: new URLSearchParams({
                                action: 'userspn_2fa_resend_login_code',
                                user_id: '<?php echo esc_attr( isset($_SESSION[self::SESSION_KEY]) ? $_SESSION[self::SESSION_KEY] : 0 ); ?>',
                                nonce: '<?php echo esc_attr( wp_create_nonce('userspn_2fa_resend') ); ?>'
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('<?php echo esc_js(__('A new code has been sent to your email.', 'userspn')); ?>');
                            } else {
                                alert(data.data || '<?php echo esc_js(__('Failed to send code. Please try again.', 'userspn')); ?>');
                            }
                            btn.innerHTML = originalText;
                            btn.style.pointerEvents = '';
                        })
                        .catch(error => {
                            alert('<?php echo esc_js(__('An error occurred. Please try again.', 'userspn')); ?>');
                            btn.innerHTML = originalText;
                            btn.style.pointerEvents = '';
                        });
                    });
                }

                // Auto-format backup code input
                var backupInput = document.getElementById('userspn_2fa_backup_code');
                if (backupInput) {
                    backupInput.addEventListener('input', function(e) {
                        var value = e.target.value.replace(/[^A-Za-z0-9]/g, '').toUpperCase();
                        if (value.length > 4) {
                            value = value.substring(0, 4) + '-' + value.substring(4, 8);
                        }
                        e.target.value = value;
                    });
                }

                // Handle form submission
                var loginForm = document.getElementById('loginform');
                if (loginForm) {
                    loginForm.addEventListener('submit', function(e) {
                        var backupInput = document.getElementById('userspn_2fa_backup_code');
                        var normalInput = document.getElementById('userspn_2fa_code');

                        // If backup section is visible, use backup code
                        if (backupSection.style.display !== 'none') {
                            normalInput.value = backupInput.value;
                        }
                    });
                }
            });
        </script>
        <?php
    }

    /**
     * Handle 2FA verification submission
     */
    public function handle_2fa_verification() {
        // Check if this is a 2FA verification request
        if (!isset($_POST['action']) || $_POST['action'] !== 'userspn_2fa_verify') {
            return;
        }

        // Verify nonce
        if (!isset($_POST['userspn_2fa_nonce']) || !wp_verify_nonce($_POST['userspn_2fa_nonce'], 'userspn_2fa_verify')) {
            wp_die( esc_html__('Security check failed.', 'userspn') );
        }

        // Check if user is in session
        if (!isset($_SESSION[self::SESSION_KEY])) {
            wp_redirect(wp_login_url());
            exit;
        }

        $user_id = $_SESSION[self::SESSION_KEY];
        $user = get_userdata($user_id);

        if (!$user) {
            wp_redirect(wp_login_url());
            exit;
        }

        // Rate limiting: max 5 attempts
        $attempts = isset($_SESSION['userspn_2fa_attempts']) ? (int)$_SESSION['userspn_2fa_attempts'] : 0;
        if ($attempts >= 5) {
            // Clear session and force re-login
            unset($_SESSION[self::SESSION_KEY]);
            unset($_SESSION['userspn_2fa_username']);
            unset($_SESSION['userspn_show_2fa_form']);
            unset($_SESSION['userspn_2fa_attempts']);
            $this->login_error(__('Too many failed attempts. Please log in again.', 'userspn'));
            return;
        }

        // Get verification code
        $code = isset($_POST['userspn_2fa_code']) ? sanitize_text_field($_POST['userspn_2fa_code']) : '';

        if (empty($code)) {
            $this->login_error(__('Please enter a verification code.', 'userspn'));
            return;
        }

        // Verify the code
        $verified = USERSPN_2FA::verify_2fa_code($user_id, $code);

        if (!$verified) {
            $_SESSION['userspn_2fa_attempts'] = $attempts + 1;
            $this->login_error(__('Invalid verification code. Please try again.', 'userspn'));
            return;
        }

        // Code verified - complete the login
        $this->complete_login($user);
    }

    /**
     * Complete the login process
     *
     * @param WP_User $user
     */
    private function complete_login($user) {
        // Clear session data
        unset($_SESSION[self::SESSION_KEY]);
        unset($_SESSION['userspn_2fa_username']);
        unset($_SESSION['userspn_show_2fa_form']);
        unset($_SESSION['userspn_2fa_attempts']);

        // Regenerate session ID after successful authentication
        if (function_exists('session_regenerate_id')) {
            session_regenerate_id(true);
        }

        // Set auth cookie
        wp_set_auth_cookie($user->ID, true);

        // Fire login action
        do_action('wp_login', $user->user_login, $user);

        // Determine redirect URL
        $redirect_to = isset($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : admin_url();
        $redirect_to = apply_filters('login_redirect', $redirect_to, '', $user);

        // Redirect
        wp_safe_redirect($redirect_to);
        exit;
    }

    /**
     * Show login error
     *
     * @param string $message
     */
    private function login_error($message) {
        $login_url = wp_login_url();
        $login_url = add_query_arg('userspn_2fa_error', urlencode($message), $login_url);

        wp_redirect($login_url);
        exit;
    }

    /**
     * AJAX handler to resend login code
     */
    public function ajax_resend_login_code() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'userspn_2fa_resend')) {
            wp_send_json_error(__('Security check failed.', 'userspn'));
        }

        // Check if user is in session
        if (!isset($_SESSION[self::SESSION_KEY])) {
            wp_send_json_error(__('Session expired. Please login again.', 'userspn'));
        }

        $user_id = $_SESSION[self::SESSION_KEY];
        $user = get_userdata($user_id);

        if (!$user) {
            wp_send_json_error(__('User not found.', 'userspn'));
        }

        // Check if method is email
        $method = get_user_meta($user_id, 'userspn_2fa_method', true);
        if ($method !== 'email') {
            wp_send_json_error(__('This method does not support resending codes.', 'userspn'));
        }

        // Send new code
        $result = USERSPN_2FA::send_email_2fa_code($user_id);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success(__('A new verification code has been sent to your email.', 'userspn'));
    }

    /**
     * Handle 2FA cancellation
     */
    public function handle_2fa_cancellation() {
        if (isset($_GET['userspn_2fa_cancel']) && $_GET['userspn_2fa_cancel'] == '1') {
            // Clear session data
            unset($_SESSION[self::SESSION_KEY]);
            unset($_SESSION['userspn_2fa_username']);
            unset($_SESSION['userspn_2fa_password']);
            unset($_SESSION['userspn_show_2fa_form']);

            // Redirect to login page
            wp_redirect(wp_login_url());
            exit;
        }
    }
}

// Initialize the class
new USERSPN_2FA_Login();
