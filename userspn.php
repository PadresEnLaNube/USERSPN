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
 * Version:           1.1.9
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
define('USERSPN_VERSION', '1.1.9');
define('USERSPN_DIR', plugin_dir_path(__FILE__));
define('USERSPN_URL', plugin_dir_url(__FILE__));

/**
 * Plugin KSES allowed HTML elements and attributes
 */
define('USERSPN_KSES', [
	// Basic text elements
	'p' => ['id' => [], 'class' => []],
	'span' => ['id' => [], 'class' => []],
	'small' => ['id' => [], 'class' => []],
	'em' => [],
	'strong' => [],
	'br' => [],

	// Headings
	'h1' => ['id' => [], 'class' => []],
	'h2' => ['id' => [], 'class' => []],
	'h3' => ['id' => [], 'class' => []],
	'h4' => ['id' => [], 'class' => []],
	'h5' => ['id' => [], 'class' => []],
	'h6' => ['id' => [], 'class' => []],

	// Lists
	'ul' => ['id' => [], 'class' => []],
	'ol' => ['id' => [], 'class' => []],
	'li' => ['id' => [], 'class' => []],

	// Links and media
	'a' => [
		'id' => [],
		'class' => [],
		'href' => [],
		'title' => [],
		'target' => [],
		'data-userspn-meta' => [],
		'data-userspn-popup-id' => [],
		'data-userspn-post-id' => []
	],
	'img' => [
		'id' => [],
		'class' => [],
		'src' => [],
		'alt' => [],
		'title' => []
	],
	'i' => ['id' => [], 'class' => [], 'title' => []],

	// Forms and inputs
	'form' => [
		'id' => [],
		'class' => [],
		'action' => [],
		'method' => []
	],
	'input' => [
		'name' => [],
		'id' => [],
		'class' => [],
		'type' => [],
		'checked' => [],
		'multiple' => [],
		'disabled' => [],
		'value' => [],
		'placeholder' => [],
		'data-userspn-parent' => [],
		'data-userspn-parent-option' => [],
		'data-userspn-type' => [],
		'data-userspn-subtype' => [],
		'data-userspn-user-id' => [],
		'data-userspn-post-id' => []
	],
	'select' => [
		'name' => [],
		'id' => [],
		'class' => [],
		'type' => [],
		'checked' => [],
		'multiple' => [],
		'disabled' => [],
		'value' => [],
		'placeholder' => [],
		'data-placeholder' => [],
		'data-userspn-parent' => [],
		'data-userspn-parent-option' => []
	],
	'option' => [
		'name' => [],
		'id' => [],
		'class' => [],
		'disabled' => [],
		'selected' => [],
		'value' => [],
		'placeholder' => []
	],
	'textarea' => [
		'name' => [],
		'id' => [],
		'class' => [],
		'type' => [],
		'multiple' => [],
		'disabled' => [],
		'value' => [],
		'placeholder' => [],
		'data-userspn-parent' => [],
		'data-userspn-parent-option' => []
	],
	'label' => [
		'id' => [],
		'class' => [],
		'for' => []
	],

	// Container elements
	'div' => [
		'id' => [],
		'class' => [],
		'data-userspn-section-id' => [],
		'data-userspn-form-type' => [],
		'data-userspn-meta' => [],
		'data-userspn-input-id' => [],
		'data-userspn-input-type' => []
	]
]);

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-userspn-activator.php
 */
function userspn_activate()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-userspn-activator.php';
	USERSPN_Activator::userspn_activate();
}
register_activation_hook(__FILE__, 'userspn_activate');

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-userspn-deactivator.php
 */
function userspn_deactivate()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-userspn-deactivator.php';
	USERSPN_Deactivator::userspn_deactivate();
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
function userspn_run()
{
	$plugin = new USERSPN();
	$plugin->userspn_run();
}

// Initialize the plugin on init hook instead of plugins_loaded to ensure WooCommerce functions are available
add_action('init', 'userspn_run', 0);