<?php
/**
 * Plugin menus manager.
 *
 * This class defines plugin menus, both in dashboard or in front-end.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    USERSPN
 * @subpackage USERSPN/includes
 * @author     Padres en la Nube <info@padresenlanube.com>
 */
class USERSPN_Notifications {
  public function userspn_notifications_init() {
    if (isset($_GET['userspn_action'])) {
      switch ($_GET['userspn_action']) {
        case 'userspn_newsletter_activation':
          // Validate and sanitize nonce
          if (isset($_GET['userspn_newsletter_activation_nonce']) && 
              wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['userspn_newsletter_activation_nonce'])), 'userspn_newsletter_activation')) {
            
            // Validate and sanitize user ID
            if (isset($_GET['user']) && is_numeric($_GET['user'])) {
              $user_id = intval($_GET['user']);
              
              // Verify user exists
              if (get_user_by('ID', $user_id)) {
                update_user_meta($user_id, 'userspn_newsletter_active', current_time('timestamp'));
                update_user_meta($user_id, 'userspn_notifications', 'on');

                // Send welcome email after activation
                $plugin_user = new USERSPN_Functions_User();
                $plugin_user->userspn_send_newsletter_email($user_id);

                wp_safe_redirect(home_url('?userspn_notice=userspn_newsletter_activation_success'));exit();
              }
            }
          }
          
          // If any validation fails, redirect to error
          wp_safe_redirect(home_url('?userspn_notice=userspn_newsletter_activation_error'));exit();
          break;
      }
    }
  }
  
  public function userspn_wp_body_open() {
    // Validate and sanitize login parameter
    $userspn_login = '';
    if (isset($_GET['userspn_login']) && !empty($_GET['userspn_login'])) {
      $userspn_login = USERSPN_Forms::userspn_sanitizer(wp_unslash($_GET['userspn_login']));
    }
    
    // Validate and sanitize notice parameter
    $userspn_notice = '';
    if (isset($_GET['userspn_notice']) && !empty($_GET['userspn_notice'])) {
      $userspn_notice = USERSPN_Forms::userspn_sanitizer(wp_unslash($_GET['userspn_notice']));
    }

    ?>
      <?php if ((!empty($userspn_login) && !is_user_logged_in()) || !empty($userspn_notice)): ?>
        <?php if (!wp_script_is('userspn-notifications', 'enqueued')): ?>
          <?php wp_enqueue_script('userspn-notifications', USERSPN_URL . 'assets/js/userspn-notifications.js', ['jquery'], USERSPN_VERSION, false, ['in_footer' => true, 'strategy' => 'defer']); ?>
        <?php endif ?>
      <?php endif ?>

      <?php if (!empty($userspn_notice)): ?>
        <div id="userspn-popup-notice" class="userspn-popup userspn-popup-size-small userspn-display-none-soft">
          <?php
            switch ($userspn_notice) {
              case 'userspn_newsletter_activation_success':
                ?>
                  <div class="userspn-popup-content userspn-text-align-center">
                    <div class="userspn-p-30">
                      <p class="userspn-alert userspn-alert-success"><?php esc_html_e('Congratulations! Your email has been activated successfully.', 'userspn'); ?></p>
                    </div>
                  </div>
                <?php
                break;
              case 'userspn_newsletter_activation_error':
                ?>
                  <div class="userspn-popup-content userspn-text-align-center">
                    <div class="userspn-p-30">
                      <p class="userspn-alert userspn-alert-error"><?php esc_html_e('Oppps! We are not able to verify your account. Please try to subscribe again or contact us.', 'userspn'); ?></p>
                    </div>
                  </div>
                <?php
                break;
            }
          ?>
        </div>
      <?php endif ?>
    <?php
  }
  public function userspn_notifications() {
    $user_id = get_current_user_id();

    $userspn_notifications = [];
    $userspn_notifications['userspn_notifications'] = [
      'id' => 'userspn_notifications',
      'class' => 'userspn-input userspn-width-100-percent',
      'input' => 'input',
      'type' => 'checkbox',
      'label' => __('Receive notifications', 'userspn'),
      'description' => __('This option will set if you receive notifications from the system. If you uncheck it you won\'t receive no notifications from the system.', 'userspn'),
    ];

    if (class_exists('Polylang')) {
      $userspn_notifications_language_options = [];
      $languages_list = pll_languages_list(['hide_empty' => false, 'fields' => []]);

      if (!empty($languages_list)) {
        foreach ($languages_list as $language) {
          $userspn_notifications_language_options[$language->slug] = [
            'id' => $language->slug,
            'label' => '<img src="' . $language->flag_url . '" alt="' . $language->name . ' flag" title="' . $language->name . '" class="userspn-mr-20">' . $language->name . ' (' . $language->slug . ')',
            'value' => $language->slug,
          ];
        }
      }

      $userspn_notifications['userspn_lang'] = [
        'id' => 'userspn_lang',
        'class' => 'userspn-input userspn-width-100-percent',
        'input' => 'input',
        'type' => 'radio',
        'radio_options' => $userspn_notifications_language_options,
        'label' => __('Notifications language', 'userspn'),
      ];
    }

    $userspn_notifications['userspn_notifications_submit'] = [
      'id' => 'userspn_notifications_submit',
      'input' => 'input',
      'type' => 'submit',
      'value' => __('Save options', 'userspn'),
    ];

    ob_start();
    ?>
      <form id="userspn-form" class="userspn-mt-30 userspn-form">
        <?php foreach ($userspn_notifications as $notifications_field): ?>
          <?php USERSPN_Forms::userspn_input_wrapper_builder($notifications_field, 'user', esc_html($user_id), 0, 'full'); ?>
        <?php endforeach ?>
      </form>
    <?php
    $userspn_return_string = ob_get_contents(); 
    ob_end_clean(); 
    return $userspn_return_string;
  }
}