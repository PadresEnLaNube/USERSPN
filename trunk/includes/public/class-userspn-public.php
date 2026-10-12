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
			
			// Determine redirect settings
			$redirect_to_page = false;
			$redirect_url = '';
			if (!is_user_logged_in() && get_option('userspn_profile_redirect_page') === 'on') {
				$redirect_page_id = get_option('userspn_profile_redirect_page_id');
				if (!empty($redirect_page_id)) {
					$permalink = get_permalink($redirect_page_id);
					if ($permalink) {
						$redirect_to_page = true;
						$redirect_url = $permalink;
					}
				}
			}

			// Pass data to JavaScript
			wp_localize_script('userspn-profile-menu', 'userspnProfileMenu', [
				'enabled' => true,
				'selectedMenu' => get_option('userspn_menu_profile_icon_location'),
				'redirectToPage' => $redirect_to_page,
				'redirectUrl' => $redirect_url,
			]);
			
		}
	}

	/**
	 * Enqueue email code login scripts
	 *
	 * @since    1.1.31
	 */
	public function userspn_enqueue_email_code_login_scripts() {
		wp_enqueue_script(
			$this->plugin_name . '-email-code-login',
			USERSPN_URL . 'assets/js/userspn-email-code-login.js',
			array('jquery'),
			$this->version,
			false
		);
	}

	/**
	 * Enqueue social login scripts
	 *
	 * @since    1.1.33
	 */
	public function userspn_enqueue_social_login_scripts() {
		wp_enqueue_script(
			$this->plugin_name . '-social-login',
			USERSPN_URL . 'assets/js/userspn-social-login.js',
			array('jquery'),
			$this->version,
			false
		);
	}

	/**
	 * Enqueue passkeys scripts
	 *
	 * @since    1.1.61
	 */
	public function userspn_enqueue_passkeys_scripts() {
		wp_enqueue_script(
			$this->plugin_name . '-passkeys',
			USERSPN_URL . 'assets/js/userspn-passkeys.js',
			array('jquery'),
			$this->version,
			true
		);

		wp_localize_script($this->plugin_name . '-passkeys', 'userspnPasskeys', [
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('userspn-nonce'),
			'i18n' => [
				'not_supported' => __('Passkeys are not supported in this browser.', 'userspn'),
				'device_name_prompt' => __('Enter a name for this passkey (e.g., "My MacBook"):', 'userspn'),
				'registering' => __('Registering...', 'userspn'),
				'register' => __('Add Passkey', 'userspn'),
				'registered' => __('Passkey registered successfully!', 'userspn'),
				'confirm_delete' => __('Delete this passkey?', 'userspn'),
			],
		]);
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