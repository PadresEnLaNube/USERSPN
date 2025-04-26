<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin admin area. This file also includes all of the dependencies used by the plugin, registers the activation and deactivation functions, and defines a function that starts the plugin.
 *
 * @link              padresenlanube.com/
 * @since             1.0.0
 * @package           USERSPN
 *
 * @wordpress-plugin
 * Plugin Name:       Users manager - PN
 * Plugin URI:        https://padresenlanube.com/plugins/userspn/
 * Description:       Streamline user management on your WordPress site with this powerful plugin. Enable custom registration forms, secure logins, and seamless profile management for your users.
 * Version:           1.0.1
 * Requires at least: 3.0.1
 * Requires PHP:      7.2
 * Author:            Padres en la Nube
 * Author URI:        https://padresenlanube.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       userspn
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('USERSPN_VERSION', '1.0.1');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-userspn-activator.php
 */
function userspn_activate() {
	require_once plugin_dir_path(__FILE__) . 'includes/class-userspn-activator.php';
	USERSPN_Activator::activate();
}
register_activation_hook(__FILE__, 'userspn_activate');

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-userspn-deactivator.php
 */
function userspn_deactivate() {
	require_once plugin_dir_path(__FILE__) . 'includes/class-userspn-deactivator.php';
	USERSPN_Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'userspn_deactivate');

/**
 * The core plugin class that is used to define internationalization, admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-userspn.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks, then kicking off the plugin from this point in the file does not affect the page life cycle.
 *
 * @since    1.0.0
 */
function userspn_run() {
	$plugin = new USERSPN();
	$plugin->run();

	require_once plugin_dir_path(__FILE__) . 'includes/class-userspn-activator.php';
	USERSPN_Activator::activate();
}

userspn_run();