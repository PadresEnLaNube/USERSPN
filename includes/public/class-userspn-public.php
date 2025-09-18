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
	}

	/**
	 * Add profile icon to navigation menu
	 *
	 * @since    1.0.0
	 */
	public function userspn_add_profile_icon_to_menu($items, $args) {
		// Check if the feature is enabled
		if (get_option('userspn_menu_profile_icon') !== 'on') {
			return $items;
		}

		// Get the selected menu location
		$selected_location = get_option('userspn_menu_profile_icon_location');
		if (empty($selected_location)) {
			return $items;
		}

		// Check if current theme is a block theme
		$is_block_theme = wp_is_block_theme();
		
		if ($is_block_theme) {
			// For block themes, check if this is the selected menu by term_id
			if (isset($args->menu) && $args->menu->term_id != $selected_location) {
				return $items;
			}
		} else {
			// For classic themes, check if this is the selected menu location
			if ($args->theme_location !== $selected_location) {
				return $items;
			}
		}

		// Add profile icon HTML to the end of the menu
		$profile_icon_html = $this->get_profile_icon_html();
		$items .= $profile_icon_html;

		return $items;
	}

	/**
	 * Add profile icon to navigation block (for block themes)
	 *
	 * @since    1.0.0
	 */
	public function userspn_add_profile_icon_to_navigation_block($block_content, $block) {
		// Check if the feature is enabled
		if (get_option('userspn_menu_profile_icon') !== 'on') {
			return $block_content;
		}

		// Only process navigation blocks
		if ($block['blockName'] !== 'core/navigation') {
			return $block_content;
		}

		// Get the selected menu
		$selected_menu = get_option('userspn_menu_profile_icon_location');
		if (empty($selected_menu)) {
			return $block_content;
		}

		// Check if this is the selected menu
		$menu_ref = isset($block['attrs']['ref']) ? $block['attrs']['ref'] : null;
		$menu_id = isset($block['attrs']['menuId']) ? $block['attrs']['menuId'] : null;
		
		// Check both ref and menuId attributes
		if ($menu_ref != $selected_menu && $menu_id != $selected_menu) {
			return $block_content;
		}

		// Add profile icon HTML to the end of the navigation block
		$profile_icon_html = $this->get_profile_icon_html();
		
		// Insert the profile icon before the closing </ul> tag
		// Handle both </ul> and </nav> closing tags
		if (strpos($block_content, '</ul>') !== false) {
			$block_content = str_replace('</ul>', $profile_icon_html . '</ul>', $block_content);
		} elseif (strpos($block_content, '</nav>') !== false) {
			$block_content = str_replace('</nav>', $profile_icon_html . '</nav>', $block_content);
		}

		return $block_content;
	}

	/**
	 * Get profile icon HTML
	 *
	 * @return string
	 */
	private function get_profile_icon_html() {
		$profile_icon_html = '<li class="menu-item userspn-profile-icon">';
		$profile_icon_html .= '<a href="#" class="userspn-profile" data-popup="userspn-user-profile">';
		$profile_icon_html .= '<span class="userspn-profile-icon-text">' . __('Profile', 'userspn') . '</span>';
		$profile_icon_html .= '</a>';
		$profile_icon_html .= '</li>';

		return $profile_icon_html;
	}
}