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
class USERSPN_Cron {
  /**
   * Set the plugin schedule for Cron execution
   *
   * @since       1.0.0
   */
  public function cron_schedule() {
    if (!wp_next_scheduled('userspn_cron_daily')){
      wp_schedule_event(time(), 'daily', 'userspn_cron_daily');
    }

    if (!wp_next_scheduled('userspn_cron_thirty_minutes')){
      wp_schedule_event(time(), 'userspn_thirty_minutes', 'userspn_cron_thirty_minutes');
    }
  }

  public function userspn_cron_thirty_minutes_schedule($schedules) {
    $schedules['userspn_thirty_minutes'] = array(
      'interval' => 1800,
      'display' => esc_html(__('Every 30 minutes', 'userspn')), 
    );
    return $schedules;
  }

  /**
   * Set the plugin cron daily functions to be executed
   *
   * @since       1.0.0
   */
  public function userspn_cron_daily() {
    // Check if deletion of inactive accounts is enabled
    $delete_inactive_enabled = get_option('userspn_newsletter_activation_delete_inactive') === 'on';
    $delete_days = intval(get_option('userspn_newsletter_activation_delete_days', 5));
    
    // Delete inactive users if option is enabled
    if ($delete_inactive_enabled && $delete_days > 0) {
      require_once(ABSPATH . 'wp-admin/includes/user.php');
      
      $cutoff_date = date('Y-m-d H:i:s', strtotime('-' . $delete_days . ' days', current_time('timestamp')));
      
      $args_delete = array(
        'meta_query' => array(
          array(
            'key' => 'userspn_newsletter_active',
            'compare' => 'NOT EXISTS',
          ),
        ),
        'role__in' => array('userspn_newsletter_subscriber'),
        'date_query' => array(
          array(
            'before' => $cutoff_date,
            'column' => 'user_registered',
          ),
        ),
        'fields' => array('ID'),
        'number' => 500, // Limit of users per execution
      );
      
      $users_to_delete = get_users($args_delete);
      if (!empty($users_to_delete)) {
        foreach ($users_to_delete as $user) {
          // Use wp_delete_user to properly delete user and all associated data
          wp_delete_user($user->ID);
        }
      }
    }
    
    // Delete inactive users (based on last login)
    $inactive_deletion_enabled = get_option('userspn_inactive_deletion_enabled') === 'on';
    $inactive_deletion_days = intval(get_option('userspn_inactive_deletion_days', 365));

    if ($inactive_deletion_enabled && $inactive_deletion_days > 0) {
      require_once(ABSPATH . 'wp-admin/includes/user.php');

      $warning_enabled = get_option('userspn_inactive_deletion_warning_enabled') === 'on';
      $warning_days = intval(get_option('userspn_inactive_deletion_warning_days', 30));

      $deletion_cutoff = current_time('timestamp') - ($inactive_deletion_days * DAY_IN_SECONDS);
      $warning_cutoff = current_time('timestamp') - (($inactive_deletion_days - $warning_days) * DAY_IN_SECONDS);

      // Get all non-admin users with last login before the deletion cutoff
      $args_inactive = [
        'meta_query' => [
          [
            'key' => 'userspn_user_last_login',
            'value' => $deletion_cutoff,
            'compare' => '<=',
            'type' => 'NUMERIC',
          ],
        ],
        'role__not_in' => ['administrator'],
        'fields' => ['ID'],
        'number' => 500,
      ];

      $users_to_delete = get_users($args_inactive);
      if (!empty($users_to_delete)) {
        foreach ($users_to_delete as $user) {
          if (!user_can($user->ID, 'administrator')) {
            wp_delete_user($user->ID);
          }
        }
      }

      // Send warning emails to users approaching the deletion threshold
      if ($warning_enabled && $warning_days > 0 && $warning_days < $inactive_deletion_days) {
        $args_warning = [
          'meta_query' => [
            [
              'key' => 'userspn_user_last_login',
              'value' => $warning_cutoff,
              'compare' => '<=',
              'type' => 'NUMERIC',
            ],
            [
              'key' => 'userspn_user_last_login',
              'value' => $deletion_cutoff,
              'compare' => '>',
              'type' => 'NUMERIC',
            ],
            [
              'key' => 'userspn_inactive_warning_sent',
              'compare' => 'NOT EXISTS',
            ],
          ],
          'role__not_in' => ['administrator'],
          'fields' => ['ID', 'user_email'],
          'number' => 500,
        ];

        $users_to_warn = get_users($args_warning);
        if (!empty($users_to_warn)) {
          $warning_method = get_option('userspn_inactive_warning_method', 'default');

          foreach ($users_to_warn as $user) {
            if (user_can($user->ID, 'administrator')) {
              continue;
            }

            $last_login = intval(get_user_meta($user->ID, 'userspn_user_last_login', true));
            $deletion_timestamp = $last_login + ($inactive_deletion_days * DAY_IN_SECONDS);
            $days_remaining = max(1, round(($deletion_timestamp - current_time('timestamp')) / DAY_IN_SECONDS));
            $deletion_date = date_i18n(get_option('date_format'), $deletion_timestamp);
            $user_name = USERSPN_Functions_User::userspn_user_get_name($user->ID);
            $site_name = get_bloginfo('name');
            $login_url = wp_login_url();

            $placeholders = [
              '[site_name]' => $site_name,
              '[user_name]' => $user_name,
              '[days_remaining]' => $days_remaining,
              '[deletion_date]' => $deletion_date,
              '[login_url]' => $login_url,
            ];

            $email_sent = false;

            if ($warning_method === 'mailpn' && (class_exists('MAILPN') || defined('MAILPN_VERSION'))) {
              $template_id = get_option('userspn_inactive_warning_mailpn_template', '');
              if (!empty($template_id)) {
                $email_sent = do_shortcode('[mailpn-sender mailpn_user_to="' . intval($user->ID) . '" mailpn_id="' . intval($template_id) . '"]');
              }
            } elseif ($warning_method === 'custom') {
              $subject = get_option('userspn_inactive_warning_subject', '');
              $message = get_option('userspn_inactive_warning_message', '');

              if (empty($subject)) {
                $subject = 'Your account will be deleted due to inactivity';
              }
              if (empty($message)) {
                $message = 'Hello [user_name], your account at [site_name] will be deleted on [deletion_date] due to inactivity. Please log in to keep your account active.';
              }

              $subject = str_replace(array_keys($placeholders), array_values($placeholders), $subject);
              $message = str_replace(array_keys($placeholders), array_values($placeholders), $message);

              $headers = ['Content-Type: text/html; charset=UTF-8'];
              $message = nl2br(esc_html($message));
              $email_sent = wp_mail($user->user_email, $subject, $message, $headers);
            } else {
              // Default built-in template
              $subject = str_replace(
                array_keys($placeholders),
                array_values($placeholders),
                __('Your account at [site_name] will be deleted due to inactivity', 'userspn')
              );
              $message = str_replace(
                array_keys($placeholders),
                array_values($placeholders),
                __('Hello [user_name], your account at [site_name] will be deleted on [deletion_date] due to inactivity ([days_remaining] days remaining). Please log in to keep your account active: [login_url]', 'userspn')
              );

              $headers = ['Content-Type: text/html; charset=UTF-8'];
              $message = nl2br(esc_html($message));
              $email_sent = wp_mail($user->user_email, $subject, $message, $headers);
            }

            if ($email_sent) {
              update_user_meta($user->ID, 'userspn_inactive_warning_sent', current_time('timestamp'));
            }
          }
        }
      }
    }

    // Find users who have not activated their newsletter subscription
    $args = array(
      'meta_query' => array(
        array(
          'key' => 'userspn_newsletter_active',
          'compare' => 'NOT EXISTS',
        ),
        array(
          'key' => 'userspn_newsletter_activation_sent',
          'compare' => 'EXISTS',
        ),
      ),
      'role__in' => array('userspn_newsletter_subscriber'),
      'fields' => array('ID', 'user_email'),
      'number' => 500, // Limit of users per execution
    );
    $users = get_users($args);
    if (!empty($users)) {
      $mailing = new USERSPN_Mailing();
      foreach ($users as $user) {
        $user_id = $user->ID;
        $user_email = $user->user_email;
        // Resend activation email (the function already respects the retry limit)
        $mailing->userspn_send_newsletter_activation_email($user_id, $user_email);
      }
    }
  }

  public function userspn_cron_thirty_minutes_function() {
    /* REMOVE CSV TEMP FILES */
    $userspn_csv_removal = get_option('userspn_csv_removal');
    
    if (!empty($userspn_csv_removal)) {
      foreach ($userspn_csv_removal as $file) {
        wp_delete_file($file);
        unset($userspn_csv_removal[array_search($file, $userspn_csv_removal)]);
        update_option('userspn_csv_removal', $userspn_csv_removal);
      }
    }
  }
}