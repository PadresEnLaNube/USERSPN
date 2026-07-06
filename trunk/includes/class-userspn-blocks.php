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

    $dependencies = ['wp-blocks', 'wp-element', 'wp-i18n'];

    wp_enqueue_script(
      'userspn-blocks',
      USERSPN_URL . 'assets/js/blocks/userspn-blocks.js',
      $dependencies,
      $this->version,
      true
    );

    wp_localize_script('userspn-blocks', 'userspnBlocks', [
      'pluginUrl' => USERSPN_URL,
      'previewContent' => $this->userspn_get_block_preview_content(),
    ]);
  }

  /**
   * Get preview content for the block editor.
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
   * Filter to ensure block renders correctly on front-end.
   *
   * @since    1.0.0
   * @param    string   $block_content The block content.
   * @param    array    $block         The full block, including name and attributes.
   * @return   string   Block HTML output.
   */
  public function userspn_render_block_filter($block_content, $block) {
    // Only process our block
    if (isset($block['blockName']) && $block['blockName'] === 'userspn/profile') {
      // If content is empty, use render callback (always inline)
      if (empty($block_content)) {
        return $this->userspn_profile_block_render([]);
      }
    }
    return $block_content;
  }
}

