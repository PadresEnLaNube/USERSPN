<?php
/**
 * Two-Factor Authentication Profile Interface for USERSPN plugin.
 *
 * This class handles the user interface for 2FA in the user profile.
 *
 * @link       padresenlanube.com/
 * @since      1.1.50
 * @package    USERSPN
 * @subpackage USERSPN/includes
 * @author     Padres en la Nube <info@padresenlanube.com>
 */

class USERSPN_2FA_Profile {

    /**
     * Initialize the class and set up hooks
     */
    public function __construct() {
        // Add 2FA section to advanced tab
        add_action('userspn_tab_advanced_content', [$this, 'render_2fa_section'], 10, 1);

        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    /**
     * Enqueue scripts and styles for 2FA interface
     */
    public function enqueue_scripts() {
        if (!USERSPN_2FA::is_2fa_enabled()) {
            return;
        }

        wp_enqueue_script(
            'userspn-2fa',
            USERSPN_URL . 'assets/js/userspn-2fa.js',
            ['jquery'],
            USERSPN_VERSION,
            true
        );

        wp_localize_script('userspn-2fa', 'userspn_2fa_vars', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('userspn-nonce'),
            'strings' => [
                'confirm_disable' => __('Are you sure you want to disable two-factor authentication?', 'userspn'),
                'confirm_regenerate' => __('Are you sure you want to regenerate backup codes? This will invalidate all existing backup codes.', 'userspn'),
                'error' => __('An error occurred. Please try again.', 'userspn'),
                'loading' => __('Loading...', 'userspn'),
                'save_backup_codes' => __('Please save these backup codes in a secure location. You will not be able to see them again.', 'userspn'),
                'invalid_code' => __('Please enter a valid 6-digit code.', 'userspn'),
                'enter_password' => __('Please enter your password.', 'userspn'),
                'copy_codes' => __('Copy codes', 'userspn'),
                'codes_copied' => __('Codes copied to clipboard.', 'userspn'),
                'copy_failed' => __('Could not copy to clipboard.', 'userspn'),
            ],
        ]);

        wp_enqueue_style(
            'userspn-2fa',
            USERSPN_URL . 'assets/css/userspn-2fa.css',
            [],
            USERSPN_VERSION
        );
    }

    /**
     * Render 2FA section in the advanced tab
     *
     * @param int $user_id User ID
     */
    public function render_2fa_section($user_id) {
        // Check if 2FA is globally enabled
        if (!USERSPN_2FA::is_2fa_enabled()) {
            return;
        }

        $is_2fa_user_enabled = USERSPN_2FA::user_has_2fa_enabled($user_id);
        $is_2fa_required = USERSPN_2FA::is_2fa_required_for_user($user_id);
        $is_in_grace_period = USERSPN_2FA::is_in_grace_period($user_id);
        $grace_days_remaining = USERSPN_2FA::get_grace_period_remaining_days($user_id);
        $backup_codes_count = USERSPN_2FA::get_backup_codes_count($user_id);
        $user_2fa_method = get_user_meta($user_id, 'userspn_2fa_method', true);
        $global_2fa_method = USERSPN_2FA::get_2fa_method();

        ?>
        <!-- Two-Factor Authentication Section -->
        <div class="userspn-2fa-section userspn-mt-20">
            <div class="userspn-toggle-wrapper userspn-position-relative userspn-mb-10">
                <a href="#" class="userspn-toggle userspn-width-100-percent userspn-text-decoration-none">
                    <div class="userspn-display-table userspn-width-100-percent">
                        <div class="userspn-display-inline-table userspn-width-80-percent userspn-vertical-align-middle">
                            <label class="userspn-display-block userspn-font-weight-bold">
                                <?php echo esc_html__('Two-Factor Authentication (2FA)', 'userspn'); ?>
                            </label>
                            <small class="userspn-display-block userspn-mt-5">
                                <?php if ($is_2fa_user_enabled): ?>
                                    <span class="userspn-text-success">
                                        <i class="material-icons-outlined userspn-vertical-align-middle userspn-font-size-16">check_circle</i>
                                        <?php echo esc_html__('Enabled', 'userspn'); ?>
                                        <?php if ($user_2fa_method === 'app'): ?>
                                            (<?php echo esc_html__('Authenticator App', 'userspn'); ?>)
                                        <?php else: ?>
                                            (<?php echo esc_html__('Email', 'userspn'); ?>)
                                        <?php endif; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="userspn-text-muted">
                                        <i class="material-icons-outlined userspn-vertical-align-middle userspn-font-size-16">cancel</i>
                                        <?php echo esc_html__('Not enabled', 'userspn'); ?>
                                    </span>
                                    <?php if ($is_2fa_required): ?>
                                        <span class="userspn-text-warning userspn-ml-10">
                                            <i class="material-icons-outlined userspn-vertical-align-middle userspn-font-size-16">warning</i>
                                            <?php echo esc_html__('Required for your role', 'userspn'); ?>
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </small>
                        </div>
                        <div class="userspn-display-inline-table userspn-width-10-percent userspn-vertical-align-middle userspn-text-align-right">
                            <i class="material-icons-outlined userspn-cursor-pointer">add</i>
                        </div>
                    </div>
                </a>

                <div class="userspn-toggle-content userspn-display-none-soft userspn-p-15">
                    <small class="userspn-display-block userspn-mb-15">
                        <?php echo esc_html__('Add an extra layer of security to your account by requiring a verification code in addition to your password.', 'userspn'); ?>
                    </small>

                    <?php if ($is_in_grace_period && !$is_2fa_user_enabled): ?>
                        <div class="userspn-alert userspn-alert-warning userspn-mb-15">
                            <i class="material-icons-outlined userspn-vertical-align-middle">info</i>
                            <?php
                            echo sprintf(
                                esc_html__('You have %d days remaining to set up two-factor authentication.', 'userspn'),
                                $grace_days_remaining
                            );
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!$is_2fa_user_enabled): ?>
                        <!-- Enable 2FA Section -->
                        <div id="userspn-2fa-enable-section">
                            <h4 class="userspn-mb-10"><?php echo esc_html__('Enable Two-Factor Authentication', 'userspn'); ?></h4>

                            <div class="userspn-2fa-method-selector userspn-mb-15">
                                <label class="userspn-display-block userspn-mb-5">
                                    <?php echo esc_html__('Choose your 2FA method:', 'userspn'); ?>
                                </label>

                                <?php if ($global_2fa_method === 'app' || !$is_2fa_required): ?>
                                    <label class="userspn-radio-label userspn-display-block userspn-mb-10">
                                        <input type="radio" name="userspn_2fa_method_choice" value="app" <?php echo ($global_2fa_method === 'app') ? 'checked' : ''; ?>>
                                        <span><?php echo esc_html__('Authenticator App (Google Authenticator, Authy, etc.)', 'userspn'); ?></span>
                                    </label>
                                <?php endif; ?>

                                <?php if ($global_2fa_method === 'email' || !$is_2fa_required): ?>
                                    <label class="userspn-radio-label userspn-display-block">
                                        <input type="radio" name="userspn_2fa_method_choice" value="email" <?php echo ($global_2fa_method === 'email') ? 'checked' : ''; ?>>
                                        <span><?php echo esc_html__('Email verification code', 'userspn'); ?></span>
                                    </label>
                                <?php endif; ?>
                            </div>

                            <!-- Authenticator App Setup -->
                            <div id="userspn-2fa-app-setup" class="userspn-2fa-setup-method userspn-display-none">
                                <div class="userspn-2fa-qr-section userspn-text-align-center userspn-mb-15">
                                    <p class="userspn-mb-10">
                                        <?php echo esc_html__('Scan this QR code with your authenticator app:', 'userspn'); ?>
                                    </p>
                                    <div id="userspn-2fa-qr-code" class="userspn-mb-10">
                                        <button type="button" id="userspn-2fa-generate-qr" class="userspn-btn userspn-btn-primary">
                                            <?php echo esc_html__('Generate QR Code', 'userspn'); ?>
                                        </button>
                                    </div>
                                    <div id="userspn-2fa-secret-display" class="userspn-display-none userspn-mb-10">
                                        <small>
                                            <?php echo esc_html__('Or enter this code manually:', 'userspn'); ?>
                                            <br>
                                            <code id="userspn-2fa-secret-code" class="userspn-font-weight-bold"></code>
                                        </small>
                                    </div>
                                </div>

                                <div class="userspn-2fa-verify-section">
                                    <label class="userspn-display-block userspn-mb-5">
                                        <?php echo esc_html__('Enter the verification code from your app:', 'userspn'); ?>
                                    </label>
                                    <input
                                        type="text"
                                        id="userspn-2fa-verify-code-app"
                                        class="userspn-input userspn-width-100-percent userspn-mb-10"
                                        placeholder="000000"
                                        maxlength="6"
                                        pattern="[0-9]{6}"
                                    >
                                    <button type="button" id="userspn-2fa-enable-app" class="userspn-btn userspn-btn-success userspn-width-100-percent">
                                        <?php echo esc_html__('Enable 2FA', 'userspn'); ?>
                                    </button>
                                </div>
                            </div>

                            <!-- Email Setup -->
                            <div id="userspn-2fa-email-setup" class="userspn-2fa-setup-method userspn-display-none">
                                <p class="userspn-mb-10">
                                    <?php echo esc_html__('When you log in, a verification code will be sent to your email address.', 'userspn'); ?>
                                </p>
                                <button type="button" id="userspn-2fa-enable-email" class="userspn-btn userspn-btn-success userspn-width-100-percent">
                                    <?php echo esc_html__('Enable Email 2FA', 'userspn'); ?>
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- 2FA Enabled - Management Section -->
                        <div id="userspn-2fa-manage-section">
                            <div class="userspn-2fa-status userspn-mb-15">
                                <p class="userspn-mb-10">
                                    <strong><?php echo esc_html__('Status:', 'userspn'); ?></strong>
                                    <span class="userspn-text-success">
                                        <?php echo esc_html__('Active', 'userspn'); ?>
                                    </span>
                                </p>
                                <p class="userspn-mb-10">
                                    <strong><?php echo esc_html__('Method:', 'userspn'); ?></strong>
                                    <?php if ($user_2fa_method === 'app'): ?>
                                        <?php echo esc_html__('Authenticator App', 'userspn'); ?>
                                    <?php else: ?>
                                        <?php echo esc_html__('Email verification code', 'userspn'); ?>
                                    <?php endif; ?>
                                </p>
                                <p class="userspn-mb-15">
                                    <strong><?php echo esc_html__('Backup codes remaining:', 'userspn'); ?></strong>
                                    <span id="userspn-2fa-backup-count"><?php echo esc_html($backup_codes_count); ?></span>
                                </p>
                            </div>

                            <!-- Backup Codes Section -->
                            <div class="userspn-2fa-backup-section userspn-mb-15">
                                <button type="button" id="userspn-2fa-show-backup-codes" class="userspn-btn userspn-btn-secondary userspn-width-100-percent userspn-mb-10">
                                    <i class="material-icons-outlined userspn-vertical-align-middle">vpn_key</i>
                                    <?php echo esc_html__('Regenerate Backup Codes', 'userspn'); ?>
                                </button>
                                <small class="userspn-display-block userspn-text-muted">
                                    <?php echo esc_html__('Backup codes can be used if you lose access to your authenticator app or email.', 'userspn'); ?>
                                </small>

                                <div id="userspn-2fa-backup-codes-display" class="userspn-display-none userspn-mt-15 userspn-p-15 userspn-bg-light userspn-border-radius-5">
                                    <p class="userspn-font-weight-bold userspn-mb-10">
                                        <?php echo esc_html__('Your backup codes:', 'userspn'); ?>
                                    </p>
                                    <div id="userspn-2fa-backup-codes-list" class="userspn-mb-10"></div>
                                    <button type="button" id="userspn-2fa-copy-backup-codes" class="userspn-btn userspn-btn-mini userspn-btn-primary">
                                        <i class="material-icons-outlined userspn-vertical-align-middle userspn-font-size-16">content_copy</i>
                                        <?php echo esc_html__('Copy codes', 'userspn'); ?>
                                    </button>
                                </div>
                            </div>

                            <!-- Disable 2FA -->
                            <?php if (!$is_2fa_required): ?>
                                <div class="userspn-2fa-disable-section">
                                    <button type="button" id="userspn-2fa-disable-btn" class="userspn-btn userspn-btn-danger userspn-width-100-percent">
                                        <i class="material-icons-outlined userspn-vertical-align-middle">block</i>
                                        <?php echo esc_html__('Disable Two-Factor Authentication', 'userspn'); ?>
                                    </button>

                                    <div id="userspn-2fa-disable-confirm" class="userspn-display-none userspn-mt-15">
                                        <label class="userspn-display-block userspn-mb-5">
                                            <?php echo esc_html__('Enter your password to confirm:', 'userspn'); ?>
                                        </label>
                                        <input
                                            type="password"
                                            id="userspn-2fa-disable-password"
                                            class="userspn-input userspn-width-100-percent userspn-mb-10"
                                            placeholder="<?php echo esc_attr__('Password', 'userspn'); ?>"
                                        >
                                        <button type="button" id="userspn-2fa-disable-confirm-btn" class="userspn-btn userspn-btn-danger userspn-mr-5">
                                            <?php echo esc_html__('Confirm Disable', 'userspn'); ?>
                                        </button>
                                        <button type="button" id="userspn-2fa-disable-cancel-btn" class="userspn-btn userspn-btn-secondary">
                                            <?php echo esc_html__('Cancel', 'userspn'); ?>
                                        </button>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="userspn-alert userspn-alert-info">
                                    <i class="material-icons-outlined userspn-vertical-align-middle">info</i>
                                    <?php echo esc_html__('Two-factor authentication is required for your role and cannot be disabled.', 'userspn'); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Messages Container -->
                    <div id="userspn-2fa-messages" class="userspn-mt-15"></div>
                </div>
            </div>
        </div>
        <?php
    }
}

// Initialize the class
new USERSPN_2FA_Profile();
