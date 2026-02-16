<?php
/**
 * Provide a common footer area view for the plugin
 *
 * This file is used to markup the common footer facing aspects of the plugin.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 *
 * @package    USERSPN
 * @subpackage USERSPN/common/templates
 */

  if (!defined('ABSPATH')) exit; // Exit if accessed directly

  // Initialize userspn_data with default values if not already set
  if (!isset($GLOBALS['userspn_data'])) {
    $GLOBALS['userspn_data'] = [
      'user_id' => get_current_user_id(),
      'post_id' => is_admin() ? (!empty($GLOBALS['_REQUEST']['post']) ? $GLOBALS['_REQUEST']['post'] : 0) : get_the_ID()
    ];
  }
  
  $userspn_data = $GLOBALS['userspn_data'];

  // Check if the shortcode has been used on the page
  $shortcode_used = isset($GLOBALS['userspn_profile_shortcode_used']) && $GLOBALS['userspn_profile_shortcode_used'];
  
  if ($shortcode_used) {
    // If shortcode is used on the page, only render the popup markup (hidden) without the button
    // This ensures other buttons can still open the popup, but we don't show duplicate buttons
    $user_id = get_current_user_id();
    if (is_user_logged_in()) {
      // Render only the popup structure for logged in users (without the button)
      if (class_exists('USERSPN_Functions_Attachment')) {
        $functions_attachment = new USERSPN_Functions_Attachment();
      } else {
        $functions_attachment = null;
      }
      ?>
      <div id="userspn-profile-popup" class="userspn-popup userspn-popup-size-medium userspn-display-none-soft">
        <div class="userspn-popup-content">
          <div class="userspn-profile-wrapper" data-user-id="<?php echo esc_attr($user_id); ?>">
            <div class="userspn-tabs-wrapper">
              <div class="userspn-tabs">
                <div class="userspn-tab-links active" data-userspn-id="userspn-tab-edit"><?php esc_html_e('Profile', 'userspn'); ?></div>

                <?php if (get_option('userspn_user_image') == 'on'): ?>
                  <div class="userspn-tab-links" data-userspn-id="userspn-tab-image"><?php esc_html_e('Image', 'userspn'); ?></div>
                <?php endif ?>

                <?php if (get_option('userspn_user_notifications') == 'on'): ?>
                  <?php if (current_user_can('administrator') || class_exists('MAILPN')): ?>
                    <div class="userspn-tab-links" data-userspn-id="userspn-tab-notifications"><?php esc_html_e('Notifications', 'userspn'); ?></div>
                  <?php endif ?>
                <?php endif ?>

                <?php if ($functions_attachment && $functions_attachment->userspn_user_files_allowed($user_id)): ?>
                  <div class="userspn-tab-links" data-userspn-id="userspn-tab-files"><?php esc_html_e('Files', 'userspn'); ?></div>
                <?php endif ?>

                <?php if (get_option('userspn_user_advanced') == 'on'): ?>
                  <div class="userspn-tab-links" data-userspn-id="userspn-tab-advanced"><?php esc_html_e('Advanced', 'userspn'); ?></div>
                <?php endif ?>
              </div>

              <div id="userspn-tab-edit" class="userspn-tab-content">
                <?php echo do_shortcode('[userspn-profile-edit]'); ?>
              </div>

              <?php if (get_option('userspn_user_image') == 'on'): ?>
                <div id="userspn-tab-image" class="userspn-tab-content userspn-display-none">
                  <?php echo do_shortcode('[userspn-profile-image]'); ?>
                </div>
              <?php endif ?>

              <?php if (get_option('userspn_user_notifications') == 'on'): ?>
                <?php if (class_exists('MAILPN')): ?>
                  <div id="userspn-tab-notifications" class="userspn-tab-content userspn-display-none">
                    <?php echo do_shortcode('[userspn-notifications]'); ?>
                  </div>
                <?php elseif (current_user_can('administrator')): ?>
                  <div id="userspn-tab-notifications" class="userspn-tab-content userspn-display-none">
                    <div class="userspn-mt-30 userspn-p-10">
                      <p class="userspn-alert"><?php esc_html_e('Notifications are inactive. Please install and activate Mailing Manager - PN to allow integrated notifications in your platform.', 'userspn'); ?></p>
                      <a href="/wp-admin/plugin-install.php?s=mailpn&tab=search&type=term" class="userspn-btn userspn-btn-mini"><?php esc_html_e('Mailing Manager - PN', 'userspn'); ?></a>
                    </div>
                  </div>
                <?php endif ?>
              <?php endif ?>

              <?php if ($functions_attachment && $functions_attachment->userspn_user_files_allowed($user_id)): ?>
                <div id="userspn-tab-files" class="userspn-tab-content userspn-display-none">
                  <?php echo do_shortcode('[userspn-user-files]'); ?>
                </div>
              <?php endif ?>

              <?php if (get_option('userspn_user_advanced') == 'on'): ?>
                <div id="userspn-tab-advanced" class="userspn-tab-content userspn-display-none">
                  <div class="userspn-display-table userspn-width-100-percent userspn-mt-30 userspn-mb-30 userspn-p-10">
                    <div class="userspn-display-inline-table userspn-width-100-percent">
                      <div class="userspn-toggle-wrapper userspn-position-relative userspn-mb-10">
                        <a href="#" class="userspn-toggle userspn-width-100-percent userspn-text-decoration-none">
                          <div class="userspn-display-table userspn-width-100-percent">
                            <div class="userspn-display-inline-table userspn-width-80-percent userspn-vertical-align-middle">
                              <label class="userspn-display-block"><?php esc_html_e('Disconnect account', 'userspn'); ?></label>
                            </div>

                            <div class="userspn-display-inline-table userspn-width-10-percent userspn-vertical-align-middle userspn-text-align-right">
                              <i class="material-icons-outlined userspn-cursor-pointer userspn-vertical-align-middle userspn-color-main-0">add</i>
                            </div>
                          </div>
                        </a>

                        <div class="userspn-toggle-content userspn-display-none-soft">
                          <small><?php esc_html_e('Disconnect your user from the system. You will need to log in again.', 'userspn'); ?></small>
                        </div>
                      </div>
                    </div>

                    <div class="userspn-display-inline-table userspn-width-100-percent userspn-text-align-right">
                      <a href="<?php echo esc_url(wp_logout_url(get_permalink())); ?>" class="userspn-btn userspn-btn-transparent userspn-btn-mini"><?php esc_html_e('Log out', 'userspn'); ?></a>
                    </div>
                  </div>

                  <?php if (get_option('userspn_user_change_password') == 'on'): ?>
                    <?php echo do_shortcode('[userspn-user-change-password-btn]'); ?>
                  <?php endif ?>

                  <?php if (get_option('userspn_user_remove') == 'on'): ?>
                    <?php echo do_shortcode('[userspn-user-remove-form]'); ?>
                  <?php endif ?>

                  <?php do_action('userspn_tab_advanced_content', $user_id); ?>
                </div>
              <?php endif ?>
            </div>
          </div>
        </div>
      </div>
      <?php
    } else {
      // Render only the popup structure for non-logged in users (without the button)
      ?>
      <div id="userspn-profile-popup" class="userspn-popup userspn-display-none-soft">
        <div class="userspn-popup-content">
          <div class="userspn-profile-wrapper">
            <div class="userspn-tabs-wrapper">
              <div class="userspn-tabs">
                <div class="userspn-tab-links active" data-userspn-id="userspn-tab-login"><?php esc_html_e('Login', 'userspn'); ?></div>

                <?php if (get_option('userspn_user_register') == 'on'): ?>
                  <div class="userspn-tab-links" data-userspn-id="userspn-tab-register"><?php esc_html_e('Register', 'userspn'); ?></div>
                <?php endif ?>
              </div>

              <div id="userspn-tab-login" class="userspn-tab-content">
                <?php echo do_shortcode('[userspn-login]'); ?>
              </div>

              <?php if (get_option('userspn_user_register') == 'on'): ?>
                <div id="userspn-tab-register" class="userspn-tab-content userspn-display-none">
                  <?php echo do_shortcode('[userspn-user-register-form]'); ?>
                </div>
              <?php endif ?>
            </div>
          </div>
        </div>
      </div>
      <?php
    }
  } else {
    // Always render the profile popup markup so buttons can open it, even if disabled
    echo do_shortcode('[userspn-profile]');
  }

  // Only run validation workflows when not disabled
  if (get_option('userspn_disabled') != 'on') {
    if (is_user_logged_in()) {
      $plugin_user = new USERSPN_Functions_User();
      $plugin_user->userspn_profile_fields_validation();
    }
  }
?>

<div id="userspn-main-message" class="userspn-main-message userspn-display-none-soft userspn-z-index-top" style="display:none;" data-user-id="<?php echo esc_attr($userspn_data['user_id']); ?>" data-post-id="<?php echo esc_attr($userspn_data['post_id']); ?>">
  <span id="userspn-main-message-span"></span><i class="material-icons-outlined userspn-vertical-align-bottom userspn-ml-20 userspn-cursor-pointer userspn-color-white userspn-close-icon">close</i>

  <div id="userspn-bar-wrapper">
  	<div id="userspn-bar"></div>
  </div>
</div>