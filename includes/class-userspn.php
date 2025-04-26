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
			$this->version = '1.0.1';
		}

		$this->plugin_name = 'userspn';

		$this->define_constants();
		$this->load_dependencies();
		$this->set_i18n();
		$this->define_common_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->load_ajax();
		$this->load_ajax_nopriv();
		$this->load_data();
		$this->load_templates();
		$this->load_settings();
		$this->load_shortcodes();
		$this->load_notifications();
	}

	/**
	 * Define the plugin main constants.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_constants() {
		define('USERSPN_DIR', plugin_dir_path(dirname(__FILE__)));
		define('USERSPN_URL', plugin_dir_url(dirname(__FILE__)));
		
		define('USERSPN_ROLE_CAPABILITIES', ['edit_post' => 'edit_userspn_host', 'edit_posts' => 'edit_userspn', 'edit_private_posts' => 'edit_private_userspn', 'edit_published_posts' => 'edit_published_userspn', 'edit_others_posts' => 'edit_other_userspn', 'publish_posts' => 'publish_userspn', 'read_post' => 'read_userspn_host', 'read_private_posts' => 'read_private_userspn', 'delete_post' => 'delete_userspn_host', 'delete_posts' => 'delete_userspn', 'delete_private_posts' => 'delete_private_userspn', 'delete_published_posts' => 'delete_published_userspn', 'delete_others_posts' => 'delete_others_userspn', 'upload_files' => 'upload_files', 'manage_terms' => 'manage_userspn_category', 'edit_terms' => 'edit_userspn_category', 'delete_terms' => 'delete_userspn_category', 'assign_terms' => 'assign_userspn_category', 'manage_options' => 'manage_userspn_options', ]);

		define('USERSPN_KSES', ['div' => ['id' => [], 'class' => [], 'data-userspn-section-id' => [], 'data-userspn-form-type' => [], 'data-userspn-meta' => [], 'data-userspn-input-id' => [], 'data-userspn-input-type' => [], 'data-userspn-meta' => [], ], 'span' => ['id' => [], 'class' => [], ], 'p' => ['id' => [], 'class' => [], ], 'ul' => ['id' => [], 'class' => [], ], 'ol' => ['id' => [], 'class' => [], ], 'li' => ['id' => [], 'class' => [], 'data-userspn-user-id' => [], 'data-userspn-file-id' => [], ], 'small' => ['id' => [], 'class' => [], ], 'a' => ['id' => [], 'class' => [], 'href' => [], 'title' => [], 'target' => [], 'data-fancybox' => [], 'data-src' => [], 'data-userspn-meta' => [], ], 'form' => ['id' => [], 'class' => [], 'action' => [], 'method' => [], ], 'input' => ['name' => [], 'id' => [], 'class' => [], 'type' => [], 'checked' => [], 'multiple' => [], 'disabled' => [], 'value' => [], 'placeholder' => [], 'data-userspn-parent' => [], 'data-userspn-parent-option' => [], 'data-userspn-parent-option' => [], 'data-userspn-type' => [], 'data-userspn-subtype' => [], 'data-userspn-user-id' => [], 'data-userspn-post-id' => [],], 'select' => ['name' => [], 'id' => [], 'class' => [], 'type' => [], 'checked' => [], 'multiple' => [], 'disabled' => [], 'value' => [], 'placeholder' => [], 'data-placeholder' => [], 'data-userspn-parent' => [], 'data-userspn-parent-option' => [], ], 'option' => ['name' => [], 'id' => [], 'class' => [], 'disabled' => [], 'selected' => [], 'value' => [], 'placeholder' => [], ], 'textarea' => ['name' => [], 'id' => [], 'class' => [], 'type' => [], 'multiple' => [], 'disabled' => [], 'value' => [], 'placeholder' => [], 'data-userspn-parent' => [], 'data-userspn-parent-option' => [], ], 'label' => ['id' => [], 'class' => [], 'for' => [], ], 'i' => ['id' => [], 'class' => [], 'title' => [], ], 'br' => [], 'em' => [], 'strong' => [], 'h1' => ['id' => [], 'class' => [], ], 'h2' => ['id' => [], 'class' => [], ], 'h3' => ['id' => [], 'class' => [], ], 'h4' => ['id' => [], 'class' => [], ], 'h5' => ['id' => [], 'class' => [], ], 'h6' => ['id' => [], 'class' => [], ], 'img' => ['id' => [], 'class' => [], 'src' => [], 'alt' => [], 'title' => [], ], ]);
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
	 *
	 * Create an instance of the loader which will be used to register the hooks with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
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
	private function set_i18n() {
		$plugin_i18n = new USERSPN_i18n();

		if (class_exists('Polylang')) {
			$this->loader->add_filter('pll_get_post_types', $plugin_i18n, 'userspn_pll_get_post_types', 10, 2);
    }
	}

	/**
	 * Register all of the hooks related to the main functionalities of the plugin, common to public and admin faces.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_common_hooks() {
		$plugin_common = new USERSPN_Common($this->get_plugin_name(), $this->get_version());
		$this->loader->add_action('wp_enqueue_scripts', $plugin_common, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_common, 'enqueue_scripts');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_common, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_common, 'enqueue_scripts');
		$this->loader->add_filter('body_class', $plugin_common, 'userspn_body_classes');
		
		$plugin_settings = new USERSPN_Settings();
		$this->loader->add_action('after_setup_theme', $plugin_settings, 'userspn_remove_admin_bar');

		$plugin_mailing = new USERSPN_Mailing();
		$this->loader->add_filter('wp_mail_content_type', $plugin_mailing, 'userspn_wp_mail_content_type');
	}

	/**
	 * Register all of the hooks related to the admin area functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new USERSPN_Admin($this->get_plugin_name(), $this->get_version());
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
		
		$plugin_user = new USERSPN_Functions_User();
		$this->loader->add_action('show_user_profile', $plugin_user, 'userspn_profile_fields');
		$this->loader->add_action('edit_user_profile', $plugin_user, 'userspn_profile_fields');
		$this->loader->add_action('user_new_form', $plugin_user, 'userspn_profile_fields');
		$this->loader->add_action('user_register', $plugin_user, 'userspn_profile_save_fields');
		$this->loader->add_action('profile_update', $plugin_user, 'userspn_profile_save_fields');
		$this->loader->add_action('init', $plugin_user, 'userspn_auto_login');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new USERSPN_Public($this->get_plugin_name(), $this->get_version());
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

		$plugin_user = new USERSPN_Functions_User();
		$this->loader->add_action('wp_login', $plugin_user, 'userspn_wp_login');
		$this->loader->add_action('user_register', $plugin_user, 'userspn_user_register');
		
		$this->loader->add_filter('get_avatar', $plugin_user, 'userspn_get_avatar_hook', 10, 5);
		
		$this->loader->add_shortcode('userspn-get-avatar', $plugin_user, 'userspn_get_avatar');
		$this->loader->add_shortcode('userspn-profile-edit', $plugin_user, 'userspn_profile_edit');		
		$this->loader->add_shortcode('userspn-profile-image', $plugin_user, 'userspn_profile_image');	
		$this->loader->add_shortcode('userspn-user-change-password-btn', $plugin_user, 'userspn_user_change_password_btn');		
		$this->loader->add_shortcode('userspn-user-remove-form', $plugin_user, 'userspn_user_remove_form');		


		$plugin_notifications = new USERSPN_Notifications();
		$this->loader->add_shortcode('userspn-notifications', $plugin_notifications, 'userspn_notifications');		
	}


	/**
	 * Load most common data used on the platform.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_data() {
		$plugin_data = new USERSPN_Data();

		if (is_admin()) {
			$this->loader->add_action('init', $plugin_data, 'load_plugin_data');
		}else{
			$this->loader->add_action('wp_footer', $plugin_data, 'load_plugin_data');
		}

		$this->loader->add_action('wp_footer', $plugin_data, 'flush_rewrite_rules');
		$this->loader->add_action('admin_footer', $plugin_data, 'flush_rewrite_rules');
	}

	/**
	 * Register templates.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_templates() {
		if (!defined('DOING_AJAX')) {
			$plugin_templates = new USERSPN_Templates();
			$this->loader->add_action('wp_footer', $plugin_templates, 'load_plugin_templates');
			$this->loader->add_action('admin_footer', $plugin_templates, 'load_plugin_templates');
		}
	}

	/**
	 * Register settings.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_settings() {
		$plugin_settings = new USERSPN_Settings();
		$this->loader->add_action('admin_menu', $plugin_settings, 'userspn_admin_menu');
		$this->loader->add_action('login_enqueue_scripts', $plugin_settings, 'userspn_login_logo');

    if (get_option('userspn_dashboard_logo') == 'on') {
			$this->loader->add_action('wp_before_admin_bar_render', $plugin_settings, 'userspn_wp_before_admin_bar_render');
			$this->loader->add_action('wp_footer', $plugin_settings, 'userspn_wp_before_admin_bar_render');
    }

		$this->loader->add_action('admin_bar_menu', $plugin_settings, 'userspn_admin_bar_wp_menu', 99);
		$this->loader->add_filter('login_headerurl', $plugin_settings, 'userspn_login_headerurl');
		$this->loader->add_filter('login_headertext', $plugin_settings, 'userspn_login_headertext');
		$this->loader->add_action('activated_plugin', $plugin_settings, 'activated_plugin');

		if (get_option('userspn_user_change_password_wp_defaults') == 'on') {
			$this->loader->add_filter('lostpassword_url', $plugin_settings, 'userspn_lostpassword_url', 99, 2);
    }
	}

	/**
	 * Load ajax functions.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_ajax() {
		$plugin_ajax = new USERSPN_Ajax();
		$this->loader->add_action('wp_ajax_userspn_ajax', $plugin_ajax, 'userspn_ajax_server');
	}

	/**
	 * Load no private ajax functions.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_ajax_nopriv() {
		$plugin_ajax_nopriv = new USERSPN_Ajax_Nopriv();
		$this->loader->add_action('wp_ajax_userspn_ajax_nopriv', $plugin_ajax_nopriv, 'userspn_ajax_nopriv_server');
		$this->loader->add_action('wp_ajax_nopriv_userspn_ajax_nopriv', $plugin_ajax_nopriv, 'userspn_ajax_nopriv_server');
	}

	/**
	 * Register shortcodes of the platform.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_shortcodes() {
		$plugin_shortcodes = new USERSPN_Shortcodes();
		$this->loader->add_shortcode('userspn-host', $plugin_shortcodes, 'userspn_host');
		$this->loader->add_shortcode('userspn-test', $plugin_shortcodes, 'userspn_test');
		$this->loader->add_shortcode('userspn-call-to-action', $plugin_shortcodes, 'userspn_call_to_action');

		$plugin_user = new USERSPN_Functions_User();
		$this->loader->add_shortcode('userspn-profile', $plugin_user, 'userspn_profile');
		$this->loader->add_shortcode('userspn-user-register-fields', $plugin_user, 'userspn_user_register_fields');
		$this->loader->add_shortcode('userspn-user-register-form', $plugin_user, 'userspn_user_register_form');
		$this->loader->add_shortcode('userspn-login', $plugin_user, 'userspn_login');
		$this->loader->add_shortcode('userspn-csv-template', $plugin_user, 'userspn_csv_template');
		$this->loader->add_shortcode('userspn-csv-template-upload', $plugin_user, 'userspn_csv_template_upload');
		
		$plugin_attachment = new USERSPN_Functions_Attachment();
		$this->loader->add_shortcode('userspn-user-files', $plugin_attachment, 'userspn_user_files');

		$plugin_mailing = new USERSPN_Mailing();
		$this->loader->add_shortcode('userspn-newsletter', $plugin_mailing, 'userspn_newsletter');
	}

	/**
	 * Cron hooks and functionalities.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_cron() {
		$plugin_cron = new USERSPN_Cron();

		$this->loader->add_action('wp', $plugin_cron, 'cron_schedule');
		$this->loader->add_filter('cron_schedules', $plugin_cron, 'userspn_cron_thirty_minutes_schedule');
		$this->loader->add_action('userspn_cron_daily', $plugin_cron, 'cron_daily');
		$this->loader->add_action('userspn_cron_thirty_minutes', $plugin_cron, 'userspn_cron_thirty_minutes_function');
	}

	/**
	 * Notifications of the platform.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_notifications() {
		$plugin_notifications = new USERSPN_Notifications();
		$this->loader->add_action('wp_body_open', $plugin_notifications, 'userspn_wp_body_open');
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress. Then it flushes the rewrite rules if needed.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    USERSPN_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}