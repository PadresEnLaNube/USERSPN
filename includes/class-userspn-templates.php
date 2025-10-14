<?php
/**
 * Load the plugin templates.
 *
 * Loads the plugin template files getting them from the templates folders inside common, public or admin, depending on access requirements.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    USERSPN
 * @subpackage USERSPN/includes
 * @author     Padres en la Nube <info@padresenlanube.com>
 */
class USERSPN_Templates {
	/**
	 * Load the plugin templates.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_templates() {
		require_once USERSPN_DIR . 'templates/userspn-footer.php';
		require_once USERSPN_DIR . 'templates/userspn-popups.php';
		
		// Load bot analysis popup
		require_once USERSPN_DIR . 'templates/userspn-bot-analysis-popup.php';
	}
}