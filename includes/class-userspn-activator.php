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
    update_option('userspn_options_changed', true);
    
    // Set a flag to redirect to options page after activation
    update_option('userspn_redirect_to_options', true);
  }
}