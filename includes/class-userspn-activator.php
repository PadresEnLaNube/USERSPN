<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    userspn
 * @subpackage userspn/includes
 * @author     Padres en la Nube <info@padresenlanube.com>
 */
class USERSPN_Activator {
	/**
   * Plugin activation functions
   *
   * Functions to be loaded on plugin activation. This actions creates roles, options and post information attached to the plugin.
	 *
	 * @since    1.0.0
	 */
	public static function userspn_activate() {
    add_role('userspn_role_manager', esc_html(__('Users manager - WPH', 'userspn')));

    $role_admin = get_role('administrator');
    $userspn_role_manager = get_role('userspn_role_manager');

    $userspn_role_manager->add_cap('upload_files'); 
    $userspn_role_manager->add_cap('read'); 

    foreach (USERSPN_ROLE_CAPABILITIES as $cap_key => $cap_value) {
      $role_admin->add_cap($cap_value); 
      $userspn_role_manager->add_cap($cap_value); 
    }

    update_option('userspn_options_changed', true);
  }
}