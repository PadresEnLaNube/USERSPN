<?php

/**
 * Fired during plugin deactivation
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 *
 * @package    USERSPN
 * @subpackage USERSPN/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    USERSPN
 * @subpackage USERSPN/includes
 * @author     Padres en la Nube <info@padresenlanube.com>
 */
class USERSPN_Deactivator {

	/**
	 * Plugin deactivation functions
	 *
	 * Functions to be loaded on plugin deactivation. This actions remove roles, options and post information attached to the plugin.
	 *
	 * @since    1.0.0
	 */
	public static function userspn_deactivate() {
		if (get_option('userspn_options_remove') == 'on') {
			remove_role('userspn_newsletter_subscriber');
		}

		// Flush rewrite rules on deactivation so other plugins (e.g. WooCommerce) rules stay correct.
		flush_rewrite_rules();
	}
}