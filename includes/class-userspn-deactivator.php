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
	public static function deactivate() {
		if (get_option('userspn_options_remove') == 'on') {
      remove_role('userspn_role_manager');

      $userspn_recipe = get_posts(['fields' => 'ids', 'numberposts' => -1, 'post_type' => 'userspn_recipe', 'post_status' => 'any', ]);

      if (!empty($userspn_recipe)) {
        foreach ($userspn_recipe as $post_id) {
          wp_delete_post($post_id, true);
        }
      }
    }

    update_option('userspn_options_changed', true);
	}
}