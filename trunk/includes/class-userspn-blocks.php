<?php
/**
 * Gutenberg blocks registration.
 *
 * This class defines all Gutenberg blocks for the platform.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    USERSPN
 * @subpackage USERSPN/includes
 * @author     Padres en la Nube <info@padresenlanube.com>
 */
class USERSPN_Blocks {
  /**
   * The unique identifier of this plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $plugin_name    The string used to uniquely identify this plugin.
   */
  private $plugin_name;

  /**
   * The current version of the plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $version    The current version of the plugin.
   */
  private $version;

  /**
   * Initialize the class and set its properties.
   *
   * @since    1.0.0
   * @param    string    $plugin_name       The name of the plugin.
   * @param    string    $version    The version of this plugin.
   */
  public function __construct($plugin_name, $version) {
    $this->plugin_name = $plugin_name;
    $this->version = $version;
  }

  /**
   * Register all Gutenberg blocks.
   *
   * @since    1.0.0
   */
  public function userspn_register_blocks() {
    // Check if Gutenberg is active
    if (!function_exists('register_block_type')) {
      return;
    }

    // Register userspn-profile block
    register_block_type('userspn/profile', [
      'render_callback' => [$this, 'userspn_profile_block_render'],
      'attributes' => [],
      'supports' => [
        'html' => false,
      ],
    ]);

    // Register userspn-sequential-login block
    register_block_type('userspn/sequential-login', [
      'render_callback' => [$this, 'userspn_sequential_login_block_render'],
      'attributes' => [
        'redirectPageId' => [
          'type' => 'string',
          'default' => '',
        ],
        'redirectTo' => [
          'type' => 'string',
          'default' => '',
        ],
        'buttonText' => [
          'type' => 'string',
          'default' => '',
        ],
        'loginButtonText' => [
          'type' => 'string',
          'default' => '',
        ],
        'showRemember' => [
          'type' => 'boolean',
          'default' => true,
        ],
        'showLostPassword' => [
          'type' => 'boolean',
          'default' => true,
        ],
      ],
      'supports' => [
        'html' => false,
      ],
    ]);

    // Add filter to ensure block renders on front-end
    add_filter('render_block', [$this, 'userspn_render_block_filter'], 10, 2);
  }

  /**
   * Enqueue block editor assets.
   *
   * @since    1.0.0
   */
  public function userspn_enqueue_block_editor_assets() {
    // Check if we're in the block editor
    if (!function_exists('register_block_type')) {
      return;
    }

    $dependencies = ['wp-blocks', 'wp-element', 'wp-i18n', 'wp-components'];
    if (wp_script_is('wp-block-editor', 'registered')) {
      $dependencies[] = 'wp-block-editor';
    } elseif (wp_script_is('wp-editor', 'registered')) {
      $dependencies[] = 'wp-editor';
    }

    wp_enqueue_style(
      'userspn-sequential-login',
      USERSPN_URL . 'assets/css/userspn-sequential-login.css',
      [],
      $this->version,
      'all'
    );

    wp_enqueue_script(
      'userspn-blocks',
      USERSPN_URL . 'assets/js/blocks/userspn-blocks.js',
      $dependencies,
      $this->version,
      true
    );

    // Get published pages for destination page selector
    $pages = get_pages([
      'post_status' => 'publish',
      'sort_column' => 'post_title',
      'sort_order'  => 'ASC',
    ]);

    $pages_list = [
      [
        'value' => '',
        'label' => __('— Select destination page —', 'userspn'),
      ]
    ];

    if (!empty($pages)) {
      foreach ($pages as $page) {
        $pages_list[] = [
          'value' => (string) $page->ID,
          'label' => !empty($page->post_title) ? $page->post_title : sprintf(__('Page #%d (No title)', 'userspn'), $page->ID),
        ];
      }
    }

    wp_localize_script('userspn-blocks', 'userspnBlocks', [
      'pluginUrl' => USERSPN_URL,
      'previewContent' => $this->userspn_get_block_preview_content(),
      'sequentialLoginPreviewContent' => $this->userspn_get_sequential_login_preview_content(),
      'pages' => $pages_list,
    ]);
  }

  /**
   * Get preview content for the user profile block in the block editor.
   *
   * @since    1.0.0
   * @return   string   Preview HTML content.
   */
  private function userspn_get_block_preview_content() {
    $content = __('This block displays the user profile content directly on the page. If the user is logged in, it shows the profile editing form with tabs for Profile, Image, Notifications, and Advanced options. If the user is not logged in, it shows login and registration forms.', 'userspn');

    $shortcode = sprintf(
      '[userspn-call-to-action userspn_call_to_action_icon="account_circle" userspn_call_to_action_title="%s" userspn_call_to_action_content="%s"]',
      esc_attr(__('User Profile Block', 'userspn')),
      esc_attr($content)
    );

    return do_shortcode($shortcode);
  }

  /**
   * Get preview content for the sequential login block in the block editor.
   *
   * @since    1.1.71
   * @return   string   Preview HTML content.
   */
  private function userspn_get_sequential_login_preview_content() {
    $content = __('Two-step sequential login with stacked email and password fields.', 'userspn');

    $shortcode = sprintf(
      '[userspn-call-to-action userspn_call_to_action_icon="lock" userspn_call_to_action_title="%s" userspn_call_to_action_content="%s"]',
      esc_attr(__('Sequential Login Block', 'userspn')),
      esc_attr($content)
    );

    return do_shortcode($shortcode);
  }

  /**
   * Render callback for userspn-profile block.
   *
   * @since    1.0.0
   * @param    array    $attributes    Block attributes.
   * @param    string   $content       Block content (empty for dynamic blocks).
   * @return   string   Block HTML output.
   */
  public function userspn_profile_block_render($attributes, $content = '') {
    // Block always renders inline
    $shortcode = '[userspn-profile inline="true"]';
    return do_shortcode($shortcode);
  }

  /**
   * Render callback for userspn/sequential-login block.
   *
   * @since    1.1.71
   * @param    array    $attributes    Block attributes.
   * @param    string   $content       Block content.
   * @return   string   Block HTML output.
   */
  public function userspn_sequential_login_block_render($attributes = [], $content = '') {
    $redirect_page_id = !empty($attributes['redirectPageId']) ? ' redirect_page_id="' . esc_attr($attributes['redirectPageId']) . '"' : '';
    $redirect_to = !empty($attributes['redirectTo']) ? ' redirect_to="' . esc_url($attributes['redirectTo']) . '"' : '';
    $button_text = !empty($attributes['buttonText']) ? ' button_text="' . esc_attr($attributes['buttonText']) . '"' : '';
    $login_button_text = !empty($attributes['loginButtonText']) ? ' login_button_text="' . esc_attr($attributes['loginButtonText']) . '"' : '';
    $show_remember = isset($attributes['showRemember']) ? ' show_remember="' . ($attributes['showRemember'] ? 'true' : 'false') . '"' : '';
    $show_lost_password = isset($attributes['showLostPassword']) ? ' show_lost_password="' . ($attributes['showLostPassword'] ? 'true' : 'false') . '"' : '';

    $shortcode = sprintf(
      '[userspn-sequential-login%s%s%s%s%s%s]',
      $redirect_page_id,
      $redirect_to,
      $button_text,
      $login_button_text,
      $show_remember,
      $show_lost_password
    );

    return do_shortcode($shortcode);
  }

  /**
   * Filter to ensure block renders correctly on front-end.
   *
   * @since    1.0.0
   * @param    string   $block_content The block content.
   * @param    array    $block         The full block, including name and attributes.
   * @return   string   Block HTML output.
   */
  public function userspn_render_block_filter($block_content, $block) {
    // Process profile block
    if (isset($block['blockName']) && $block['blockName'] === 'userspn/profile') {
      if (empty($block_content)) {
        return $this->userspn_profile_block_render([]);
      }
    }

    // Process sequential login block
    if (isset($block['blockName']) && $block['blockName'] === 'userspn/sequential-login') {
      if (empty($block_content)) {
        return $this->userspn_sequential_login_block_render($block['attrs'] ?? []);
      }
    }

    return $block_content;
  }
}

