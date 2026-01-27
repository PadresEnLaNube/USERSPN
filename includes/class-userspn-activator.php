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
    add_role('userspn_newsletter_subscriber', 'Newsletter Subscriber', ['read' => true]);

    // Flush rewrite rules once on activation; do not defer to footer (would run on
    // next request and can cause 503 on checkout/heavy pages).
    flush_rewrite_rules();

    // Set a flag to redirect to options page after activation
    update_option('userspn_redirect_to_options', true);
  }
}