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
  public function cron_daily() {
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