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

  $userspn_data = $GLOBALS['userspn_data'];

  if (get_option('userspn_disabled') != 'on') {
    echo do_shortcode('[userspn-profile]');

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