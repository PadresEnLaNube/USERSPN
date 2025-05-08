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
?>
<div class="userspn-popup-overlay userspn-display-none-soft"></div>

<div id="userspn-newsletter-exit-popup" class="userspn-popup userspn-newsletter-exit-popup userspn-popup-size-medium userspn-display-none-soft">
  <div class="userspn-popup-content">
    <?php echo do_shortcode('[userspn-newsletter]'); ?>
  </div>
</div>
