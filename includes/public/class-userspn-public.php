<?php

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to enqueue the public-facing stylesheet and JavaScript.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    USERSPN
 * @subpackage USERSPN/public
 * @author     Padres en la Nube <info@padresenlanube.com>
 */
class USERSPN_Public {
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function userspn_enqueue_styles() {
		wp_enqueue_style($this->plugin_name . '-public', USERSPN_URL . 'assets/css/public/userspn-public.css', [], $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function userspn_enqueue_scripts() {
		wp_enqueue_script($this->plugin_name . '-public', USERSPN_URL . 'assets/js/public/userspn-public.js', ['jquery'], $this->version, false);
		
		// Enqueue profile menu script if feature is enabled
		if (get_option('userspn_bubble_position_type', 'default') === 'menu') {
			wp_enqueue_script('userspn-profile-menu', USERSPN_URL . 'assets/js/userspn-profile-menu.js', ['jquery'], $this->version, true);
			
			// Pass data to JavaScript
			wp_localize_script('userspn-profile-menu', 'userspnProfileMenu', [
				'enabled' => true,
				'selectedMenu' => get_option('userspn_menu_profile_icon_location')
			]);
			
		}
	}

	/**
	 * Add profile icon container to navigation menu
	 * This method is kept for compatibility but JavaScript handles the actual work
	 *
	 * @since    1.0.0
	 */
	public function userspn_add_profile_icon_to_menu($items, $args) {
		// JavaScript handles everything now, so we just return the items unchanged
		return $items;
	}

	/**
	 * Add profile icon container to navigation block (for block themes)
	 * Injects the container server-side so JavaScript can copy the profile button into it.
	 *
	 * @since    1.0.0
	 */
	public function userspn_add_profile_icon_to_navigation_block($block_content, $block) {
		static $already_added = false;

		// Only process the first navigation block
		if ($already_added) {
			return $block_content;
		}

		// Only process navigation blocks
		if (!isset($block['blockName']) || $block['blockName'] !== 'core/navigation') {
			return $block_content;
		}

		// Check if the feature is enabled
		if (get_option('userspn_bubble_position_type', 'default') !== 'menu') {
			return $block_content;
		}

		$already_added = true;

		// Find the last </ul> before </nav> to inject the container as the last navigation item
		$nav_close_pos = strrpos($block_content, '</nav>');
		if ($nav_close_pos === false) {
			return $block_content;
		}

		$before_nav = substr($block_content, 0, $nav_close_pos);
		$last_ul_pos = strrpos($before_nav, '</ul>');

		if ($last_ul_pos !== false) {
			// Navigation uses <ul>/<li> structure - insert <li> before closing </ul>
			$container = '<li class="wp-block-navigation-item menu-item userspn-profile-container" id="userspn-profile-menu-container"></li>';
			$block_content = substr_replace($block_content, $container, $last_ul_pos, 0);
		}

		return $block_content;
	}

	/**
	 * Get profile container HTML (where existing profile element will be moved)
	 *
	 * @return string
	 */
	private function get_profile_container_html() {
		$profile_container_html = '<li class="menu-item userspn-profile-container" id="userspn-profile-menu-container">';
		$profile_container_html .= '</li>';

		return $profile_container_html;
	}
}