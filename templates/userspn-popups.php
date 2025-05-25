<?php
/**
 * Provide common popups for the plugin
 *
 * This file is used to markup the common popups of the plugin.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 *
 * @package    userspn
 * @subpackage userspn/common/templates
 */

  if (!defined('ABSPATH')) exit; // Exit if accessed directly
  global $post;
?>
<div class="userspn-popup-overlay userspn-display-none-soft"></div>

<?php if (get_option('userspn_newsletter_exit_popup') == 'on' && !has_shortcode($post->post_content, 'userspn-newsletter')) : ?>
  <div id="userspn-newsletter-exit-popup" class="userspn-popup userspn-newsletter-exit-popup userspn-popup-size-medium userspn-display-none-soft">
    <div class="userspn-popup-content">
      <div class="userspn-p-30">
        <?php if (get_option('userspn_newsletter_exit_popup_empty') != 'on') : ?>
          <?php echo do_shortcode('[userspn-newsletter]'); ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php endif; ?>
