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