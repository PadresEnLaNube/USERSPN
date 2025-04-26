<?php
/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin so that it is ready for translation.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    USERSPN
 * @subpackage USERSPN/includes
 * @author     Padres en la Nube <info@padresenlanube.com>
 */
class USERSPN_i18n {
  /**
   * Load the plugin text domain for translation.
   *
   * @since    1.0.0
   */
  public function load_plugin_textdomain() {
    load_plugin_textdomain(
      'userspn',
      false,
      dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
    );
  }

	/**
	 * Load the plugin translation functions.
	 *
	 * @since    1.0.0
	 */
	public function userspn_pll_get_post_types($post_types, $is_settings) {
    if ($is_settings){
      unset($post_types['userspn_recipe']);
    }else{
      $post_types['userspn_recipe'] = 'userspn_recipe';
    }

    return $post_types;
  }

  public function userspn_timestamp_server_gap() {
    $time = new DateTime(gmdate('Y-m-d H:i:s', time()));
    $current_time = new DateTime(gmdate('Y-m-d H:i:s', current_time('timestamp')));

    $interval = $current_time->diff($time);
    return ((($interval->invert) ? '-' : '+') . $interval->d . ' days ') . ((($interval->invert) ? '-' : '+') . $interval->h . ' hours ') . ((($interval->invert) ? '-' : '+') . $interval->i . ' minutes ') . ((($interval->invert) ? '-' : '+') . $interval->s . ' seconds');
  }
}