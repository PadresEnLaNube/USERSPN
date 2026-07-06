<?php
/**
 * Platform shortcodes.
 *
 * This class defines all shortcodes of the platform.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    USERSPN
 * @subpackage USERSPN/includes
 * @author     Padres en la Nube <info@padresenlanube.com>
 */
class USERSPN_Shortcodes {
  public function userspn_test($atts) {
    /* echo do_shortcode('[userspn-test]'); */
    $a = extract(shortcode_atts([
      'user_id' => get_current_user_id(),
    ], $atts));
  
    ob_start();
    ?>
      <div class="userspn-shortcode-example">
        Shortcode example
<?php echo esc_html(USERSPN_Data::userspn_loader()); ?>
      </div>
    <?php
    $userspn_return_string = ob_get_contents(); 
    ob_end_clean(); 
    return $userspn_return_string;
  }

	/**
	 * Manage the shortcodes in the platform.
	 *
	 * @since    1.0.0
	 */
  public function userspn_call_to_action($atts) {
    // echo do_shortcode('[userspn-call-to-action userspn_call_to_action_icon="error_outline" userspn_call_to_action_title="' . esc_html(__('Default title', 'userspn')) . '" userspn_call_to_action_content="' . esc_html(__('Default content', 'userspn')) . '" userspn_call_to_action_button_link="#" userspn_call_to_action_button_text="' . esc_html(__('Button text', 'userspn')) . '" userspn_call_to_action_button_class="userspn-class"]');
    $a = extract(shortcode_atts(array(
      'userspn_call_to_action_class' => '',
      'userspn_call_to_action_icon' => '',
      'userspn_call_to_action_title' => '',
      'userspn_call_to_action_content' => '',
      'userspn_call_to_action_button_link' => '#',
      'userspn_call_to_action_button_text' => '',
      'userspn_call_to_action_button_class' => '',
      'userspn_call_to_action_button_data_key' => '',
      'userspn_call_to_action_button_data_value' => '',
      'userspn_call_to_action_button_blank' => 0,
    ), $atts));

    ob_start();
    ?>
      <div class="userspn-call-to-action userspn-text-align-center userspn-pt-30 userspn-pb-50 <?php echo esc_attr($userspn_call_to_action_class); ?>">
        <div class="userspn-call-to-action-icon">
          <i class="material-icons-outlined userspn-font-size-75 userspn-color-main-0"><?php echo esc_attr($userspn_call_to_action_icon); ?></i>
        </div>

        <h4 class="userspn-call-to-action-title userspn-text-align-center userspn-mt-10 userspn-mb-20"><?php echo esc_html($userspn_call_to_action_title); ?></h4>
        
        <?php if (!empty($userspn_call_to_action_content)): ?>
          <p class="userspn-text-align-center"><?php echo esc_html($userspn_call_to_action_content); ?></p>
        <?php endif ?>

        <?php if (!empty($userspn_call_to_action_button_text)): ?>
          <div class="userspn-text-align-center userspn-mt-20">
            <a class="userspn-btn userspn-btn-transparent userspn-margin-auto <?php echo esc_attr($userspn_call_to_action_button_class); ?>" <?php echo ($userspn_call_to_action_button_blank) ? 'target="_blank"' : ''; ?> href="<?php echo esc_url($userspn_call_to_action_button_link); ?>" <?php echo (!empty($userspn_call_to_action_button_data_key) && !empty($userspn_call_to_action_button_data_value)) ? esc_attr($userspn_call_to_action_button_data_key) . '="' . esc_attr($userspn_call_to_action_button_data_value) . '"' : ''; ?>><?php echo esc_html($userspn_call_to_action_button_text); ?></a>
          </div>
        <?php endif ?>
      </div>
    <?php 
    $userspn_return_string = ob_get_contents(); 
    ob_end_clean(); 
    return $userspn_return_string;
  }
}