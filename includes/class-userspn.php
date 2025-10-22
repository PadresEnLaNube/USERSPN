<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current version of the plugin.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    USERSPN
 * @subpackage USERSPN/includes
 * @author     Padres en la Nube <info@padresenlanube.com>
 */

class USERSPN {
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      USERSPN_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin. Load the dependencies, define the locale, and set the hooks for the admin area and the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if (defined('USERSPN_VERSION')) {
			$this->version = USERSPN_VERSION;
		} else {
			$this->version = '1.0.13';
		}

		$this->plugin_name = 'userspn';

		$this->userspn_load_dependencies();
		$this->userspn_set_i18n();
		$this->userspn_define_common_hooks();
		$this->userspn_define_admin_hooks();
		$this->userspn_define_public_hooks();
		$this->userspn_load_ajax();
		$this->userspn_load_ajax_nopriv();
		$this->userspn_load_data();
		$this->userspn_load_templates();
		$this->userspn_load_settings();
		$this->userspn_load_shortcodes();
		$this->userspn_load_notifications();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 * - USERSPN_Loader. Orchestrates the hooks of the plugin.
	 * - USERSPN_i18n. Defines internationalization functionality.
	 * - USERSPN_Common. Defines hooks used accross both, admin and public side.
	 * - USERSPN_Admin. Defines all hooks for the admin area.
	 * - USERSPN_Public. Defines all hooks for the public side of the site.
	 * - USERSPN_Taxonomies_Host. Defines Host taxonomies.
	 * - USERSPN_Templates. Load plugin templates.
	 * - USERSPN_Mailing. Load plugin mailing functions.
	 * - USERSPN_Notifications. Load plugin notifications functions.
	 * - USERSPN_CSV. Load plugin CSV functions.
	 * - USERSPN_Data. Load main usefull data.
	 * - USERSPN_Functions_Post. Posts management functions.
	 * - USERSPN_Functions_User. Users management functions.
	 * - USERSPN_Functions_Attachment. Attachments management functions.
	 * - USERSPN_Functions_Settings. Define settings.
	 * - USERSPN_Functions_Forms. Forms management functions.
	 * - USERSPN_Functions_Ajax. Ajax functions.
	 * - USERSPN_Functions_Ajax_Nopriv. Ajax No Private functions.
	 * - USERSPN_Functions_Shortcodes. Define all shortcodes for the platform.
	 * - USERSPN_Validation. Validation functions.
	 *
	 * Create an instance of the loader which will be used to register the hooks with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function userspn_load_dependencies() {
		/**
		 * The class responsible for orchestrating the actions and filters of the core plugin.
		 */
		require_once USERSPN_DIR . 'includes/class-userspn-loader.php';

		/**
		 * The class responsible for defining internationalization functionality of the plugin.
		 */
		require_once USERSPN_DIR . 'includes/class-userspn-i18n.php';

		/**
		 * The class responsible for defining all actions that occur both in the admin area and in the public-facing side of the site.
		 */
		require_once USERSPN_DIR . 'includes/class-userspn-common.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once USERSPN_DIR . 'includes/admin/class-userspn-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing side of the site.
		 */
		require_once USERSPN_DIR . 'includes/public/class-userspn-public.php';

		/**
		 * The class responsible for plugin templates.
		 */
		require_once USERSPN_DIR . 'includes/class-userspn-templates.php';

		/**
		 * The class responsible for plugin mailing functions.
		 */
		require_once USERSPN_DIR . 'includes/class-userspn-mailing.php';

		/**
		 * The class responsible for plugin notifications functions.
		 */
		require_once USERSPN_DIR . 'includes/class-userspn-notifications.php';

		/**
		 * The class responsible for plugin csv functions.
		 */
		require_once USERSPN_DIR . 'includes/class-userspn-csv.php';

		/**
		 * The class getting key data of the platform.
		 */
		require_once USERSPN_DIR . 'includes/class-userspn-data.php';

		/**
		 * The class defining posts management functions.
		 */
		require_once USERSPN_DIR . 'includes/class-userspn-functions-post.php';

		/**
		 * The class defining users management functions.
		 */
		require_once USERSPN_DIR . 'includes/class-userspn-functions-user.php';

		/**
		 * The class defining attahcments management functions.
		 */
		require_once USERSPN_DIR . 'includes/class-userspn-functions-attachment.php';

		/**
		 * The class defining settings.
		 */
		require_once USERSPN_DIR . 'includes/class-userspn-settings.php';

		/**
		 * The class defining form management.
		 */
		require_once USERSPN_DIR . 'includes/class-userspn-forms.php';

		/**
		 * The class defining ajax functions.
		 */
		require_once USERSPN_DIR . 'includes/class-userspn-ajax.php';

		/**
		 * The class defining no private ajax functions.
		 */
		require_once USERSPN_DIR . 'includes/class-userspn-ajax-nopriv.php';

		/**
		 * The class defining cron.
		 */
		require_once USERSPN_DIR . 'includes/class-userspn-cron.php';

		/**
		 * The class defining shortcodes.
		 */
		require_once USERSPN_DIR . 'includes/class-userspn-shortcodes.php';

		/**
		 * The class responsible for popups functionality.
		 */
		require_once USERSPN_DIR . 'includes/class-userspn-popups.php';

		/**
		 * The class responsible for selector functionality.
		 */
		require_once USERSPN_DIR . 'includes/class-userspn-selector.php';

		/**
		 * The class responsible for validation functions.
		 */
		require_once USERSPN_DIR . 'includes/class-userspn-validation.php';

		/**
		 * The class responsible for security functions.
		 */
		require_once USERSPN_DIR . 'includes/class-userspn-security.php';

		$this->loader = new USERSPN_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the USERSPN_i18n class in order to set the domain and to register the hook with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function userspn_set_i18n() {
		$plugin_i18n = new USERSPN_i18n();

		$this->loader->userspn_add_action('after_setup_theme', $plugin_i18n, 'userspn_load_plugin_textdomain');

		if (class_exists('Polylang')) {
			$this->loader->userspn_add_filter('pll_get_post_types', $plugin_i18n, 'userspn_pll_get_post_types', 10, 2);
    	}
	}

	/**
	 * Register all of the hooks related to the main functionalities of the plugin, common to public and admin faces.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function userspn_define_common_hooks() {
		$plugin_common = new USERSPN_Common($this->userspn_get_plugin_name(), $this->userspn_get_version());
		$this->loader->userspn_add_action('wp_enqueue_scripts', $plugin_common, 'userspn_enqueue_styles');
		$this->loader->userspn_add_action('wp_enqueue_scripts', $plugin_common, 'userspn_enqueue_scripts');
		$this->loader->userspn_add_action('admin_enqueue_scripts', $plugin_common, 'userspn_enqueue_styles');
		$this->loader->userspn_add_action('admin_enqueue_scripts', $plugin_common, 'userspn_enqueue_scripts');
		$this->loader->userspn_add_filter('body_class', $plugin_common, 'userspn_body_classes');
		
		$plugin_settings = new USERSPN_Settings();
		$this->loader->userspn_add_action('init', $plugin_settings, 'userspn_remove_admin_bar');
		$this->loader->userspn_add_filter('show_admin_bar', $plugin_settings, 'userspn_show_admin_bar_filter');
		$this->loader->userspn_add_action('wp', $plugin_settings, 'userspn_remove_admin_bar');

		$plugin_mailing = new USERSPN_Mailing();
		$this->loader->userspn_add_filter('wp_mail_content_type', $plugin_mailing, 'userspn_wp_mail_content_type');
		
		// Add redirect hook for plugin activation
		$this->loader->userspn_add_action('admin_init', $this, 'userspn_redirect_to_options');
	}

	/**
	 * Register all of the hooks related to the admin area functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function userspn_define_admin_hooks() {
		$plugin_admin = new USERSPN_Admin($this->userspn_get_plugin_name(), $this->userspn_get_version());
		$this->loader->userspn_add_action('admin_enqueue_scripts', $plugin_admin, 'userspn_enqueue_styles');
		$this->loader->userspn_add_action('admin_enqueue_scripts', $plugin_admin, 'userspn_enqueue_scripts');
		
		$plugin_user = new USERSPN_Functions_User();
		$this->loader->userspn_add_action('show_user_profile', $plugin_user, 'userspn_profile_fields');
		$this->loader->userspn_add_action('edit_user_profile', $plugin_user, 'userspn_profile_fields');
		$this->loader->userspn_add_action('user_new_form', $plugin_user, 'userspn_profile_fields');
		$this->loader->userspn_add_action('user_register', $plugin_user, 'userspn_profile_save_fields');
		$this->loader->userspn_add_action('profile_update', $plugin_user, 'userspn_profile_save_fields');
		$this->loader->userspn_add_action('admin_init', $plugin_user, 'userspn_auto_login');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function userspn_define_public_hooks() {
		$plugin_public = new USERSPN_Public($this->userspn_get_plugin_name(), $this->userspn_get_version());
		$this->loader->userspn_add_action('wp_enqueue_scripts', $plugin_public, 'userspn_enqueue_styles');
		$this->loader->userspn_add_action('wp_enqueue_scripts', $plugin_public, 'userspn_enqueue_scripts');
		$this->loader->userspn_add_filter('wp_nav_menu_items', $plugin_public, 'userspn_add_profile_icon_to_menu', 10, 2);
		
		// Only register the render_block filter if the method exists
		if (method_exists($plugin_public, 'userspn_add_profile_icon_to_navigation_block')) {
			$this->loader->userspn_add_filter('render_block', $plugin_public, 'userspn_add_profile_icon_to_navigation_block', 10, 2);
		}

		$plugin_user = new USERSPN_Functions_User();
		$this->loader->userspn_add_action('wp', $plugin_user, 'userspn_auto_login');
		$this->loader->userspn_add_action('wp_login', $plugin_user, 'userspn_user_wp_login');
		$this->loader->userspn_add_action('user_register', $plugin_user, 'userspn_user_register');
		
		$this->loader->userspn_add_filter('get_avatar', $plugin_user, 'userspn_get_avatar_hook', 10, 5);
		
		$this->loader->userspn_add_shortcode('userspn-get-avatar', $plugin_user, 'userspn_get_avatar');
		$this->loader->userspn_add_shortcode('userspn-profile-edit', $plugin_user, 'userspn_profile_edit');		
		$this->loader->userspn_add_shortcode('userspn-profile-image', $plugin_user, 'userspn_profile_image');	
		$this->loader->userspn_add_shortcode('userspn-user-change-password-btn', $plugin_user, 'userspn_user_change_password_btn');		
		$this->loader->userspn_add_shortcode('userspn-user-remove-form', $plugin_user, 'userspn_user_remove_form');		

		$plugin_notifications = new USERSPN_Notifications();
		$this->loader->userspn_add_shortcode('userspn-notifications', $plugin_notifications, 'userspn_notifications');		
		$this->loader->userspn_add_action('wp_loaded', $plugin_notifications, 'userspn_notifications_init');
	}


	/**
	 * Load most common data used on the platform.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function userspn_load_data() {
		$plugin_data = new USERSPN_Data();

		if (is_admin()) {
			$this->loader->userspn_add_action('init', $plugin_data, 'userspn_load_plugin_data');
		}else{
			$this->loader->userspn_add_action('wp_footer', $plugin_data, 'userspn_load_plugin_data');
		}

		$this->loader->userspn_add_action('wp_footer', $plugin_data, 'userspn_flush_rewrite_rules');
		$this->loader->userspn_add_action('admin_footer', $plugin_data, 'userspn_flush_rewrite_rules');
	}

	/**
	 * Register templates.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function userspn_load_templates() {
		if (!defined('DOING_AJAX')) {
			$plugin_templates = new USERSPN_Templates();
			$this->loader->userspn_add_action('wp_footer', $plugin_templates, 'load_plugin_templates');
			$this->loader->userspn_add_action('admin_footer', $plugin_templates, 'load_plugin_templates');
		}
	}

	/**
	 * Register settings.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function userspn_load_settings() {
		$plugin_settings = new USERSPN_Settings();
		$this->loader->userspn_add_action('admin_menu', $plugin_settings, 'userspn_admin_menu');
		$this->loader->userspn_add_action('login_enqueue_scripts', $plugin_settings, 'userspn_login_logo');

    if (get_option('userspn_dashboard_logo') == 'on') {
			$this->loader->userspn_add_action('wp_before_admin_bar_render', $plugin_settings, 'userspn_wp_before_admin_bar_render');
			$this->loader->userspn_add_action('wp_footer', $plugin_settings, 'userspn_wp_before_admin_bar_render');
    }

		$this->loader->userspn_add_action('admin_bar_menu', $plugin_settings, 'userspn_admin_bar_wp_menu', 99);
		$this->loader->userspn_add_filter('login_headerurl', $plugin_settings, 'userspn_login_headerurl');
		$this->loader->	userspn_add_filter('login_headertext', $plugin_settings, 'userspn_login_headertext');
		$this->loader->userspn_add_action('activated_plugin', $plugin_settings, 'userspn_activated_plugin');
		add_filter('plugin_action_links_' . plugin_basename(USERSPN_DIR . 'userspn.php'), 'userspn_add_settings_link_standalone');

		if (get_option('userspn_user_change_password_wp_defaults') == 'on') {
			$this->loader->userspn_add_filter('lostpassword_url', $plugin_settings, 'userspn_lostpassword_url', 99, 2);
			$this->loader->userspn_add_action('login_form_lostpassword', $plugin_settings, 'userspn_add_nonce_to_lostpassword_form');
		}
	}

	/**
	 * Load ajax functions.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function userspn_load_ajax() {
		$plugin_ajax = new USERSPN_Ajax();
		$this->loader->userspn_add_action('wp_ajax_userspn_ajax', $plugin_ajax, 'userspn_ajax_server');
	}

	/**
	 * Load no private ajax functions.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function userspn_load_ajax_nopriv() {
		$plugin_ajax_nopriv = new USERSPN_Ajax_Nopriv();
		$this->loader->userspn_add_action('wp_ajax_userspn_ajax_nopriv', $plugin_ajax_nopriv, 'userspn_ajax_nopriv_server');
		$this->loader->userspn_add_action('wp_ajax_nopriv_userspn_ajax_nopriv', $plugin_ajax_nopriv, 'userspn_ajax_nopriv_server');
		
		// Add hook for suspicious user deletion
		$this->loader->userspn_add_action('init', 'USERSPN_Security', 'handle_suspicious_user_deletion');
	}

	/**
	 * Register shortcodes of the platform.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function userspn_load_shortcodes() {
		$plugin_shortcodes = new USERSPN_Shortcodes();
		$this->loader->userspn_add_shortcode('userspn-host', $plugin_shortcodes, 'userspn_host');
		$this->loader->userspn_add_shortcode('userspn-test', $plugin_shortcodes, 'userspn_test');
		$this->loader->userspn_add_shortcode('userspn-call-to-action', $plugin_shortcodes, 'userspn_call_to_action');

		$plugin_user = new USERSPN_Functions_User();
		$this->loader->userspn_add_shortcode('userspn-profile', $plugin_user, 'userspn_profile');
		$this->loader->userspn_add_shortcode('userspn-user-register-fields', $plugin_user, 'userspn_user_register_fields');
		$this->loader->userspn_add_shortcode('userspn-user-register-form', $plugin_user, 'userspn_user_register_form');
		$this->loader->userspn_add_shortcode('userspn-login', $plugin_user, 'userspn_login');
		$this->loader->userspn_add_shortcode('userspn-csv-template', $plugin_user, 'userspn_csv_template');
		$this->loader->userspn_add_shortcode('userspn-csv-template-upload', $plugin_user, 'userspn_csv_template_upload');
		
		$plugin_attachment = new USERSPN_Functions_Attachment();
		$this->loader->userspn_add_shortcode('userspn-user-files', $plugin_attachment, 'userspn_user_files');

		$plugin_mailing = new USERSPN_Mailing();
		$this->loader->userspn_add_shortcode('userspn-newsletter', $plugin_mailing, 'userspn_newsletter');
	}

	/**
	 * Cron hooks and functionalities.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function userspn_load_cron() {
		$plugin_cron = new USERSPN_Cron();

		$this->loader->userspn_add_action('wp', $plugin_cron, 'cron_schedule');
		$this->loader->userspn_add_filter('cron_schedules', $plugin_cron, 'userspn_cron_thirty_minutes_schedule');
		$this->loader->userspn_add_action('userspn_cron_daily', $plugin_cron, 'userspn_cron_daily');
		$this->loader->userspn_add_action('userspn_cron_thirty_minutes', $plugin_cron, 'userspn_cron_thirty_minutes_function');
	}

	/**
	 * Notifications of the platform.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function userspn_load_notifications() {
		$plugin_notifications = new USERSPN_Notifications();
		$this->loader->userspn_add_action('wp_body_open', $plugin_notifications, 'userspn_wp_body_open');
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress. Then it flushes the rewrite rules if needed.
	 *
	 * @since    1.0.0
	 */
	public function userspn_run() {
		$this->loader->userspn_run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function userspn_get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    USERSPN_Loader    Orchestrates the hooks of the plugin.
	 */
	public function userspn_get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function userspn_get_version() {
		return $this->version;
	}

	/**
	 * Redirect to options page after plugin activation
	 *
	 * @since    1.0.0
	 */
	public function userspn_redirect_to_options() {
		// Check if we should redirect (only for administrators)
		if (!current_user_can('administrator')) {
			return;
		}
		
		// Check if the redirect flag is set
		if (get_option('userspn_redirect_to_options', false)) {
			// Remove the flag to prevent infinite redirects
			delete_option('userspn_redirect_to_options');
			
			// Only redirect if we're not already on the options page
			if (!isset($_GET['page']) || $_GET['page'] !== 'userspn_options') {
				wp_safe_redirect(admin_url('admin.php?page=userspn_options'));
				exit;
			}
		}
	}
}