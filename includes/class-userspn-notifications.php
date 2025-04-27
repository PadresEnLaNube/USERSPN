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
  public function userspn_wp_body_open() {
    $userspn_login = !empty($_GET['userspn_login']) ? USERSPN_Forms::sanitizer(wp_unslash($_GET['userspn_login'])) : '';
    $userspn_notice = !empty($_GET['userspn_notice']) ? USERSPN_Forms::sanitizer(wp_unslash($_GET['userspn_notice'])) : '';

    ?>
      <?php if (!empty($userspn_login) && !is_user_logged_in()): ?>
        <?php if (!wp_script_is('userspn-notifications', 'enqueued')): ?>
          <?php wp_enqueue_script('userspn-notifications', USERSPN_URL . 'assets/js/userspn-notifications.js', ['jquery'], USERSPN_VERSION, false, ['in_footer' => true, 'strategy' => 'defer']); ?>
        <?php endif ?>
      <?php endif ?>

      <?php if (!empty($userspn_notice)): ?>
        <?php if (!wp_script_is('userspn-notifications', 'enqueued')): ?>
          <?php wp_enqueue_script('userspn-notifications', USERSPN_URL . 'assets/js/userspn-notifications.js', ['jquery'], USERSPN_VERSION, false, ['in_footer' => true, 'strategy' => 'defer']); ?>
        <?php endif ?>

        <div id="userspn-popup-notice" class="userspn-display-none">
          <div class="userspn-alert userspn-text-align-center userspn-p-30 userspn-z-index-top userspn-bg-color-white">
            <?php
              switch ($userspn_notice) {
                case 'newsletter-activation-success':
                  ?>
                    <div class="userspn-alert-success userspn-text-align-center"><?php esc_html_e('Congratulations! Your email has been activated successfully.', 'userspn'); ?></div>
                  <?php
                  break;
                case 'newsletter-activation-error':
                  ?>
                    <p><?php esc_html_e('Oppps! We are not able to verify your account. Please try to subscribe again or contact us.', 'userspn'); ?></p>
                  <?php
                  break;
              }
            ?>
          </div>
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
          <?php USERSPN_Forms::input_wrapper_builder($notifications_field, 'user', esc_html($user_id), 0, 'full'); ?>
        <?php endforeach ?>
      </form>
    <?php
    $userspn_return_string = ob_get_contents(); 
    ob_end_clean(); 
    return $userspn_return_string;
  }
}