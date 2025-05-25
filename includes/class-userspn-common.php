<?php
/**
 * The-global functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to enqueue the-global stylesheet and JavaScript.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    USERSPN
 * @subpackage USERSPN/includes
 * @author     Padres en la Nube <info@padresenlanube.com>
 */
class USERSPN_Common {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		if (!wp_style_is($this->plugin_name . '-material-icons-outlined', 'enqueued')) {
			wp_enqueue_style($this->plugin_name . '-material-icons-outlined', USERSPN_URL . 'assets/css/material-icons-outlined.min.css', [], $this->version, 'all');
		}

		if (!wp_style_is($this->plugin_name . '-trumbowyg', 'enqueued')) {
			wp_enqueue_style($this->plugin_name . '-trumbowyg', USERSPN_URL . 'assets/css/trumbowyg.min.css', [], $this->version, 'all');
		}

		if (!wp_style_is($this->plugin_name . '-selector', 'enqueued')) {
			wp_enqueue_style($this->plugin_name . '-selector', USERSPN_URL . 'assets/css/userspn-selector.css', [], $this->version, 'all');
    	}

		if (!wp_style_is($this->plugin_name . '-popups', 'enqueued')) {
			wp_enqueue_style($this->plugin_name . '-popups', USERSPN_URL . 'assets/css/userspn-popups.css', [], $this->version, 'all');
		}

		if (!wp_style_is($this->plugin_name . '-tooltipster', 'enqueued')) {
			wp_enqueue_style($this->plugin_name . '-tooltipster', USERSPN_URL . 'assets/css/tooltipster.min.css', [], $this->version, 'all');
		}

		if (!wp_style_is($this->plugin_name . '-owl', 'enqueued')) {
			wp_enqueue_style($this->plugin_name . '-owl', USERSPN_URL . 'assets/css/owl.min.css', [], $this->version, 'all');
		}

		if (!wp_style_is($this->plugin_name . '-datatables', 'enqueued')) {
			wp_enqueue_style($this->plugin_name . '-datatables', USERSPN_URL . 'assets/css/datatables.min.css', [], $this->version, 'all');
		}

		wp_enqueue_style($this->plugin_name, USERSPN_URL . 'assets/css/userspn.css', [], $this->version, 'all');
		wp_enqueue_style('userspn-profile-completion', USERSPN_URL . 'assets/css/userspn-profile-completion.css', [], $this->version, 'all');
	}

	/**
	 * Register the JavaScript.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		if(!wp_script_is('jquery-ui-sortable', 'enqueued')) {
				wp_enqueue_script('jquery-ui-sortable');
		}

		if(!wp_script_is($this->plugin_name . '-selector', 'enqueued')) {
			wp_enqueue_script($this->plugin_name . '-selector', USERSPN_URL . 'assets/js/userspn-selector.js', ['jquery'], $this->version, false, ['in_footer' => true, 'strategy' => 'defer']);
		}

		if(!wp_script_is($this->plugin_name . '-trumbowyg', 'enqueued')) {
			wp_enqueue_script($this->plugin_name . '-trumbowyg', USERSPN_URL . 'assets/js/trumbowyg.min.js', ['jquery'], $this->version, false, ['in_footer' => true, 'strategy' => 'defer']);
		}

		if(!wp_script_is($this->plugin_name . '-popups', 'enqueued')) {
			wp_enqueue_script($this->plugin_name . '-popups', USERSPN_URL . 'assets/js/userspn-popups.js', ['jquery'], $this->version, false, ['in_footer' => true, 'strategy' => 'defer']);
		}

		if(!wp_script_is($this->plugin_name . '-tooltipster', 'enqueued')) {
			wp_enqueue_script($this->plugin_name . '-tooltipster', USERSPN_URL . 'assets/js/tooltipster.min.js', ['jquery'], $this->version, false, ['in_footer' => true, 'strategy' => 'defer']);
		}

		if(!wp_script_is($this->plugin_name . '-owl', 'enqueued')) {
			wp_enqueue_script($this->plugin_name . '-owl', USERSPN_URL . 'assets/js/owl.min.js', ['jquery'], $this->version, false, ['in_footer' => true, 'strategy' => 'defer']);
		}

		if(!wp_script_is($this->plugin_name . '-datatables', 'enqueued')) {
			wp_enqueue_script($this->plugin_name . '-datatables', USERSPN_URL . 'assets/js/datatables.min.js', ['jquery'], $this->version, false, ['in_footer' => true, 'strategy' => 'defer']);
		}

		wp_enqueue_script($this->plugin_name, USERSPN_URL . 'assets/js/userspn.js', ['jquery'], $this->version, false);
		wp_enqueue_script($this->plugin_name . '-ajax', USERSPN_URL . 'assets/js/userspn-ajax.js', ['jquery'], $this->version, false, ['in_footer' => true, 'strategy' => 'defer']);
		wp_enqueue_script($this->plugin_name . '-aux', USERSPN_URL . 'assets/js/userspn-aux.js', ['jquery'], $this->version, false, ['in_footer' => true, 'strategy' => 'defer']);
		wp_enqueue_script($this->plugin_name . '-forms', USERSPN_URL . 'assets/js/userspn-forms.js', ['jquery'], $this->version, false, ['in_footer' => true, 'strategy' => 'defer']);
		wp_enqueue_script($this->plugin_name . '-input-editor-builder', USERSPN_URL . 'assets/js/userspn-input-editor-builder.js', ['jquery'], $this->version, false, ['in_footer' => true, 'strategy' => 'defer']);
		wp_enqueue_script($this->plugin_name . '-profile-progress', USERSPN_URL . 'assets/js/userspn-profile-progress.js', ['jquery'], $this->version, false, ['in_footer' => true, 'strategy' => 'defer']);

		wp_localize_script($this->plugin_name, 'userspn_ajax', [
			'ajax_url' => admin_url('admin-ajax.php'),
			'userspn_ajax_nonce' => wp_create_nonce('userspn-nonce'),
		]);

		if (class_exists('MAILPN')) {
			wp_localize_script($this->plugin_name, 'mailpn_ajax', [
				'mailpn_class' => class_exists('MAILPN_Ajax') ? true : false,
				'ajax_url' => admin_url('admin-ajax.php'),
				'mailpn_ajax_nonce' => wp_create_nonce('mailpn-nonce'),
			]);
		}

		wp_localize_script($this->plugin_name, 'userspn_path', [
			'main' => USERSPN_URL,
			'assets' => USERSPN_URL . 'assets/',
			'css' => USERSPN_URL . 'assets/css/',
			'js' => USERSPN_URL . 'assets/js/',
			'media' => USERSPN_URL . 'assets/media/',
		]);

		wp_localize_script($this->plugin_name, 'userspn_newsletter', [
			'exit_popup' => (get_option('userspn_newsletter_exit_popup') == 'on') ? true : false,
			'is_user_logged_in' => is_user_logged_in(),
			'exit_popup_empty' => (get_option('userspn_newsletter_exit_popup_empty') == 'on') ? true : false,
		]);

		wp_localize_script($this->plugin_name, 'userspn_trumbowyg', [
			'path' => USERSPN_URL . 'assets/media/trumbowyg-icons.svg',
		]);

		wp_localize_script($this->plugin_name, 'userspn_i18n', [
			'complete_required_fields' => __('Please complete all required fields', 'userspn'),
			'completed' => __('Completed', 'userspn'),
			'pending' => __('Pending', 'userspn'),
			'get_locale' => get_locale(),
			'an_error_has_occurred' => esc_html(__('An error has occurred. Please try again in a few minutes.', 'userspn')),
			'user_unlogged' => esc_html(__('Please create a new user or login to save the information.', 'userspn')),
			'saved_successfully' => esc_html(__('Saved successfully', 'userspn')),
			'edit_image' => esc_html(__('Edit image', 'userspn')),
			'edit_images' => esc_html(__('Edit images', 'userspn')),
			'select_image' => esc_html(__('Select image', 'userspn')),
			'select_images' => esc_html(__('Select images', 'userspn')),
			'edit_video' => esc_html(__('Edit video', 'userspn')),
			'edit_videos' => esc_html(__('Edit videos', 'userspn')),
			'select_video' => esc_html(__('Select video', 'userspn')),
			'select_videos' => esc_html(__('Select videos', 'userspn')),
			'edit_audio' => esc_html(__('Edit audio', 'userspn')),
			'edit_audios' => esc_html(__('Edit audios', 'userspn')),
			'select_audio' => esc_html(__('Select audio', 'userspn')),
			'select_audios' => esc_html(__('Select audios', 'userspn')),
			'edit_file' => esc_html(__('Edit file', 'userspn')),
			'edit_files' => esc_html(__('Edit files', 'userspn')),
			'select_file' => esc_html(__('Select file', 'userspn')),
			'select_files' => esc_html(__('Select files', 'userspn')),
			'ordered_element' => esc_html(__('Ordered element', 'userspn')),
			'password_not_correct' => esc_html(__('The password is not correct. Please try again', 'userspn')),
			'include_password' => esc_html(__('Please, include your account password to complete the operation', 'userspn')),
			'user_removed' => esc_html(__('The user has been successfully removed from the system. Reloading...', 'userspn')),
			'user_existing' => esc_html(__('Existing user. Please login.', 'userspn')),
			'user_created' => esc_html(__('User created. Please login.', 'userspn')),
			'file_uploaded' => esc_html(__('The file has been uploaded successfully. Reloading page...', 'userspn')),
			'file_removed' => esc_html(__('The file has been removed.', 'userspn')),
			'field_removed' => esc_html(__('The field has been removed.', 'userspn')),
			'field_saved' => esc_html(__('The field has been saved successfully.', 'userspn')),
			'field_provide' => esc_html(__('Please provide at least field name and type.', 'userspn')),
			'activation_email' => esc_html(__('We have sent you an activation email. Please check your inbox or spam folder.', 'userspn')),
			'email_too_many' => esc_html(__('Your email has received too many emails. Please contact us.', 'userspn')),
			'newsletter_subscribed' => esc_html(__('Congratulations! You have been successfully subscribed to our newsletter.', 'userspn')),
			'email_sent' => esc_html(__('We have sent you an email. Please check your inbox or spam folder.', 'userspn')),
		]);
			
		$userspn_login = !empty($_GET['userspn_login']) ? USERSPN_Forms::sanitizer(wp_unslash($_GET['userspn_login'])) : '';
		$userspn_notice = !empty($_GET['userspn_notice']) ? USERSPN_Forms::sanitizer(wp_unslash($_GET['userspn_notice'])) : '';

		wp_localize_script($this->plugin_name, 'userspn_get', [
			'userspn_login' => $userspn_login,
			'userspn_notice' => $userspn_notice,
		]);

		// Initialize popups
		USERSPN_Popups::instance();

		// Initialize selectors
		USERSPN_Selector::instance();
	}

  	public function userspn_body_classes($classes) {
		$classes[] = 'userspn-body';

		if (!is_user_logged_in()) {
			$classes[] = 'userspn-body-unlogged';
		}else{
		$classes[] = 'userspn-body-logged-in';
		
		$user = new WP_User(get_current_user_id());
		foreach ($user->roles as $role) {
			$classes[] = 'userspn-body-' . $role;
		}
		}

	  return $classes;
  }
}
