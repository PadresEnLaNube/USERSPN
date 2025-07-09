<?php
/**
 * Fired from activate() function.
 *
 * This class defines all post types necessary to run during the plugin's life cycle.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    USERSPN
 * @subpackage USERSPN/includes
 * @author     Padres en la Nube <info@padresenlanube.com>
 */
class USERSPN_Forms {
  /**
   * Plaform forms.
   *
   * @since    1.0.0
   */

  /**
   * Get the current value of a field based on its type and storage
   * 
   * @param string $field_id The field ID
   * @param string $userspn_type The type of field (user, post, option)
   * @param int $userspn_id The ID of the user/post/option
   * @param int $userspn_meta_array Whether the field is part of a meta array
   * @param int $userspn_array_index The index in the meta array
   * @param array $userspn_input The input array containing field configuration
   * @return mixed The current value of the field
   */
  private static function userspn_get_field_value($field_id, $userspn_type, $userspn_id = 0, $userspn_meta_array = 0, $userspn_array_index = 0, $userspn_input = []) {
    $current_value = '';

    if ($userspn_meta_array) {
      switch ($userspn_type) {
        case 'user':
          $meta = get_user_meta($userspn_id, $field_id, true);
          if (is_array($meta) && isset($meta[$userspn_array_index])) {
            $current_value = $meta[$userspn_array_index];
          }
          break;
        case 'post':
          $meta = get_post_meta($userspn_id, $field_id, true);
          if (is_array($meta) && isset($meta[$userspn_array_index])) {
            $current_value = $meta[$userspn_array_index];
          }
          break;
        case 'option':
          $option = get_option($field_id);
          if (is_array($option) && isset($option[$userspn_array_index])) {
            $current_value = $option[$userspn_array_index];
          }
          break;
      }
    } else {
      switch ($userspn_type) {
        case 'user':
          $current_value = get_user_meta($userspn_id, $field_id, true);
          break;
        case 'post':
          $current_value = get_post_meta($userspn_id, $field_id, true);
          break;
        case 'option':
          $current_value = get_option($field_id);
          break;
      }
    }

    // If no value is found and there's a default value in the input config, use it
    if (empty($current_value) && !empty($userspn_input['value'])) {
      $current_value = $userspn_input['value'];
    }

    return $current_value;
  }

  public static function userspn_input_builder($userspn_input, $userspn_type, $userspn_id = 0, $disabled = 0, $userspn_meta_array = 0, $userspn_array_index = 0) {
    // Get the current value using the new function
    $userspn_value = self::userspn_get_field_value($userspn_input['id'], $userspn_type, $userspn_id, $userspn_meta_array, $userspn_array_index, $userspn_input);

    $userspn_parent_block = (!empty($userspn_input['parent']) ? 'data-userspn-parent="' . $userspn_input['parent'] . '"' : '') . ' ' . (!empty($userspn_input['parent_option']) ? 'data-userspn-parent-option="' . $userspn_input['parent_option'] . '"' : '');

    switch ($userspn_input['input']) {
      case 'input':        
        switch ($userspn_input['type']) {
          case 'file':
            ?>
              <?php if (empty($userspn_value)): ?>
                <p class="userspn-m-10"><?php esc_html_e('No file found', 'userspn'); ?></p>
              <?php else: ?>
                <p class="userspn-m-10">
                  <a href="<?php echo esc_url(get_post_meta($userspn_id, $userspn_input['id'], true)['url']); ?>" target="_blank"><?php echo esc_html(basename(get_post_meta($userspn_id, $userspn_input['id'], true)['url'])); ?></a>
                </p>
              <?php endif ?>
            <?php
            break;
          case 'checkbox':
            ?>
              <label class="userspn-switch">
                <input id="<?php echo esc_attr($userspn_input['id']) . ((array_key_exists('multiple', $userspn_input) && $userspn_input['multiple']) ? '[]' : ''); ?>" name="<?php echo esc_attr($userspn_input['id']) . ((array_key_exists('multiple', $userspn_input) && $userspn_input['multiple']) ? '[]' : ''); ?>" class="<?php echo array_key_exists('class', $userspn_input) ? esc_attr($userspn_input['class']) : ''; ?> userspn-checkbox userspn-checkbox-switch userspn-field" type="<?php echo esc_attr($userspn_input['type']); ?>" <?php echo $userspn_value == 'on' ? 'checked="checked"' : ''; ?> <?php echo (((array_key_exists('disabled', $userspn_input) && $userspn_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?> <?php echo ((array_key_exists('required', $userspn_input) && $userspn_input['required'] == true) ? 'required' : ''); ?> <?php echo wp_kses_post($userspn_parent_block); ?>>
                <span class="userspn-slider userspn-round"></span>
              </label>
            <?php
            break;
          case 'radio':
            ?>
              <div class="userspn-input-radio-wrapper">
                <?php if (!empty($userspn_input['radio_options'])): ?>
                  <?php foreach ($userspn_input['radio_options'] as $radio_option): ?>
                    <div class="userspn-input-radio-item">
                      <label for="<?php echo esc_attr($radio_option['id']); ?>">
                        <?php echo wp_kses_post(wp_specialchars_decode($radio_option['label'])); ?>
                        
                        <input type="<?php echo esc_attr($userspn_input['type']); ?>"
                          id="<?php echo esc_attr($radio_option['id']); ?>"
                          name="<?php echo esc_attr($userspn_input['id']); ?>"
                          value="<?php echo esc_attr($radio_option['value']); ?>"
                          <?php echo $userspn_value == $radio_option['value'] ? 'checked="checked"' : ''; ?>
                          <?php echo ((array_key_exists('required', $userspn_input) && $userspn_input['required'] == 'true') ? 'required' : ''); ?>>

                        <div class="userspn-radio-control"></div>
                      </label>
                    </div>
                  <?php endforeach ?>
                <?php endif ?>
              </div>
            <?php
            break;
          case 'range':
            ?>
              <div class="userspn-input-range-wrapper">
                <div class="userspn-width-100-percent">
                  <?php if (!empty($userspn_input['userspn_label_min'])): ?>
                    <p class="userspn-input-range-label-min"><?php echo esc_html($userspn_input['userspn_label_min']); ?></p>
                  <?php endif ?>

                  <?php if (!empty($userspn_input['userspn_label_max'])): ?>
                    <p class="userspn-input-range-label-max"><?php echo esc_html($userspn_input['userspn_label_max']); ?></p>
                  <?php endif ?>
                </div>

                <input type="<?php echo esc_attr($userspn_input['type']); ?>" id="<?php echo esc_attr($userspn_input['id']) . ((array_key_exists('multiple', $userspn_input) && $userspn_input['multiple']) ? '[]' : ''); ?>" name="<?php echo esc_attr($userspn_input['id']) . ((array_key_exists('multiple', $userspn_input) && $userspn_input['multiple']) ? '[]' : ''); ?>" class="userspn-input-range <?php echo array_key_exists('class', $userspn_input) ? esc_attr($userspn_input['class']) : ''; ?>" <?php echo ((array_key_exists('required', $userspn_input) && $userspn_input['required'] == true) ? 'required' : ''); ?> <?php echo (((array_key_exists('disabled', $userspn_input) && $userspn_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?> <?php echo (isset($userspn_input['userspn_max']) ? 'max=' . esc_attr($userspn_input['userspn_max']) : ''); ?> <?php echo (isset($userspn_input['userspn_min']) ? 'min=' . esc_attr($userspn_input['userspn_min']) : ''); ?> <?php echo (((array_key_exists('step', $userspn_input) && $userspn_input['step'] != '')) ? 'step="' . esc_attr($userspn_input['step']) . '"' : ''); ?> <?php echo (array_key_exists('multiple', $userspn_input) && $userspn_input['multiple'] ? 'multiple' : ''); ?> value="<?php echo (!empty($userspn_input['button_text']) ? esc_html($userspn_input['button_text']) : esc_html($userspn_value)); ?>"/>
                <h3 class="userspn-input-range-output"></h3>
              </div>
            <?php
            break;
          case 'stars':
            $userspn_stars = !empty($userspn_input['stars_number']) ? $userspn_input['stars_number'] : 5;
            ?>
              <div class="userspn-input-stars-wrapper">
                <div class="userspn-width-100-percent">
                  <?php if (!empty($userspn_input['userspn_label_min'])): ?>
                    <p class="userspn-input-stars-label-min"><?php echo esc_html($userspn_input['userspn_label_min']); ?></p>
                  <?php endif ?>

                  <?php if (!empty($userspn_input['userspn_label_max'])): ?>
                    <p class="userspn-input-stars-label-max"><?php echo esc_html($userspn_input['userspn_label_max']); ?></p>
                  <?php endif ?>
                </div>

                <div class="userspn-input-stars userspn-text-align-center userspn-pt-20">
                  <?php foreach (range(1, $userspn_stars) as $index => $star): ?>
                    <i class="material-icons-outlined userspn-input-star">
                      <?php echo ($index < intval($userspn_value)) ? 'star' : 'star_outlined'; ?>
                    </i>
                  <?php endforeach ?>
                </div>

                <input type="number" <?php echo ((array_key_exists('required', $userspn_input) && $userspn_input['required'] == true) ? 'required' : ''); ?> <?php echo ((array_key_exists('disabled', $userspn_input) && $userspn_input['disabled'] == 'true') ? 'disabled' : ''); ?> id="<?php echo esc_attr($userspn_input['id']) . ((array_key_exists('multiple', $userspn_input) && $userspn_input['multiple']) ? '[]' : ''); ?>" name="<?php echo esc_attr($userspn_input['id']) . ((array_key_exists('multiple', $userspn_input) && $userspn_input['multiple']) ? '[]' : ''); ?>" class="userspn-input-hidden-stars <?php echo array_key_exists('class', $userspn_input) ? esc_attr($userspn_input['class']) : ''; ?>" min="1" max="<?php echo esc_attr($userspn_stars) ?>" value="<?php echo esc_attr($userspn_value); ?>">
              </div>
            <?php
            break;
          case 'submit':
            ?>
              <div class="userspn-text-align-right">
                <input type="submit" value="<?php echo esc_attr($userspn_input['value']); ?>" name="<?php echo esc_attr($userspn_input['id']); ?>" id="<?php echo esc_attr($userspn_input['id']); ?>" name="<?php echo esc_attr($userspn_input['id']); ?>" class="userspn-btn" data-userspn-type="<?php echo esc_attr($userspn_type); ?>" data-userspn-subtype="<?php echo ((array_key_exists('subtype', $userspn_input)) ? esc_attr($userspn_input['subtype']) : ''); ?>" data-userspn-user-id="<?php echo esc_attr($userspn_id); ?>" data-userspn-post-id="<?php echo !empty(get_the_ID()) ? esc_attr(get_the_ID()) : ''; ?>"/><?php esc_html(USERSPN_Data::userspn_loader()); ?>
              </div>
            <?php
            break;
          case 'hidden':
            ?>
              <input type="hidden" id="<?php echo esc_attr($userspn_input['id']); ?>" name="<?php echo esc_attr($userspn_input['id']); ?>" value="<?php echo esc_attr($userspn_value); ?>" <?php echo (array_key_exists('multiple', $userspn_input) && $userspn_input['multiple'] == 'true' ? 'multiple' : ''); ?>>
            <?php
            break;
          case 'nonce':
            ?>
              <input type="hidden" id="<?php echo esc_attr($userspn_input['id']); ?>" name="<?php echo esc_attr($userspn_input['id']); ?>" value="<?php echo esc_attr(wp_create_nonce('userspn-nonce')); ?>">
            <?php
            break;
          case 'password':
            ?>
              <div class="userspn-password-checker">
                <div class="userspn-password-input userspn-position-relative">
                  <input id="<?php echo esc_attr($userspn_input['id']) . ((array_key_exists('multiple', $userspn_input) && $userspn_input['multiple'] == 'true') ? '[]' : ''); ?>" name="<?php echo esc_attr($userspn_input['id']) . ((array_key_exists('multiple', $userspn_input) && $userspn_input['multiple'] == 'true') ? '[]' : ''); ?>" <?php echo (array_key_exists('multiple', $userspn_input) && $userspn_input['multiple'] == 'true' ? 'multiple' : ''); ?> class="userspn-field userspn-password-strength <?php echo array_key_exists('class', $userspn_input) ? esc_attr($userspn_input['class']) : ''; ?>" type="<?php echo esc_attr($userspn_input['type']); ?>" <?php echo ((array_key_exists('required', $userspn_input) && $userspn_input['required'] == 'true') ? 'required' : ''); ?> <?php echo ((array_key_exists('disabled', $userspn_input) && $userspn_input['disabled'] == 'true') ? 'disabled' : ''); ?> value="<?php echo (!empty($userspn_input['button_text']) ? esc_html($userspn_input['button_text']) : esc_attr($userspn_value)); ?>" placeholder="<?php echo (array_key_exists('placeholder', $userspn_input) ? esc_attr($userspn_input['placeholder']) : ''); ?>" <?php echo wp_kses_post($userspn_parent_block); ?>/>

                  <a href="#" class="userspn-show-pass userspn-cursor-pointer userspn-display-none-soft">
                    <i class="material-icons-outlined userspn-font-size-20">visibility</i>
                  </a>
                </div>

                <div id="userspn-popover-pass" class="userspn-display-none-soft">
                  <div class="userspn-progress-bar-wrapper">
                    <div class="userspn-password-strength-bar"></div>
                  </div>

                  <h3 class="userspn-mt-20"><?php esc_html_e('Password strength checker', 'userspn'); ?> <i class="material-icons-outlined userspn-cursor-pointer userspn-close-icon userspn-mt-30">close</i></h3>
                  <ul class="userspn-list-style-none">
                    <li class="low-upper-case">
                      <i class="material-icons-outlined userspn-font-size-20 userspn-vertical-align-middle">radio_button_unchecked</i>
                      <span><?php esc_html_e('Lowercase & Uppercase', 'userspn'); ?></span>
                    </li>
                    <li class="one-number">
                      <i class="material-icons-outlined userspn-font-size-20 userspn-vertical-align-middle">radio_button_unchecked</i>
                      <span><?php esc_html_e('Number (0-9)', 'userspn'); ?></span>
                    </li>
                    <li class="one-special-char">
                      <i class="material-icons-outlined userspn-font-size-20 userspn-vertical-align-middle">radio_button_unchecked</i>
                      <span><?php esc_html_e('Special Character (!@#$%^&*)', 'userspn'); ?></span>
                    </li>
                    <li class="eight-character">
                      <i class="material-icons-outlined userspn-font-size-20 userspn-vertical-align-middle">radio_button_unchecked</i>
                      <span><?php esc_html_e('Atleast 8 Character', 'userspn'); ?></span>
                    </li>
                  </ul>
                </div>
              </div>
            <?php
            break;
          case 'color':
            ?>
              <input id="<?php echo esc_attr($userspn_input['id']) . ((array_key_exists('multiple', $userspn_input) && $userspn_input['multiple']) ? '[]' : ''); ?>" name="<?php echo esc_attr($userspn_input['id']) . ((array_key_exists('multiple', $userspn_input) && $userspn_input['multiple']) ? '[]' : ''); ?>" <?php echo (array_key_exists('multiple', $userspn_input) && $userspn_input['multiple'] ? 'multiple' : ''); ?> class="userspn-field <?php echo array_key_exists('class', $userspn_input) ? esc_attr($userspn_input['class']) : ''; ?>" type="<?php echo esc_attr($userspn_input['type']); ?>" <?php echo ((array_key_exists('required', $userspn_input) && $userspn_input['required'] == true) ? 'required' : ''); ?> <?php echo (((array_key_exists('disabled', $userspn_input) && $userspn_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?> value="<?php echo (!empty($userspn_value) ? esc_attr($userspn_value) : '#000000'); ?>" placeholder="<?php echo (array_key_exists('placeholder', $userspn_input) ? esc_attr($userspn_input['placeholder']) : ''); ?>" <?php echo wp_kses_post($userspn_parent_block); ?>/>
            <?php
            break;
          default:
            ?>
              <input 
                <?php /* ID and name attributes */ ?>
                id="<?php echo esc_attr($userspn_input['id']) . ((array_key_exists('multiple', $userspn_input) && $userspn_input['multiple']) ? '[]' : ''); ?>" 
                name="<?php echo esc_attr($userspn_input['id']) . ((array_key_exists('multiple', $userspn_input) && $userspn_input['multiple']) ? '[]' : ''); ?>"
                
                <?php /* Type and styling */ ?>
                class="userspn-field <?php echo array_key_exists('class', $userspn_input) ? esc_attr($userspn_input['class']) : ''; ?>" 
                type="<?php echo esc_attr($userspn_input['type']); ?>"
                
                <?php /* State attributes */ ?>
                <?php echo ((array_key_exists('required', $userspn_input) && $userspn_input['required'] == true) ? 'required' : ''); ?>
                <?php echo (((array_key_exists('disabled', $userspn_input) && $userspn_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?>
                <?php echo (array_key_exists('multiple', $userspn_input) && $userspn_input['multiple'] ? 'multiple' : ''); ?>
                
                <?php /* Validation and limits */ ?>
                <?php echo (((array_key_exists('step', $userspn_input) && $userspn_input['step'] != '')) ? 'step="' . esc_attr($userspn_input['step']) . '"' : ''); ?>
                <?php echo (isset($userspn_input['max']) ? 'max="' . esc_attr($userspn_input['max']) . '"' : ''); ?>
                <?php echo (isset($userspn_input['min']) ? 'min="' . esc_attr($userspn_input['min']) . '"' : ''); ?>
                <?php echo (isset($userspn_input['maxlength']) ? 'maxlength="' . esc_attr($userspn_input['maxlength']) . '"' : ''); ?>
                <?php echo (isset($userspn_input['pattern']) ? 'pattern="' . esc_attr($userspn_input['pattern']) . '"' : ''); ?>
                
                <?php /* Content attributes */ ?>
                value="<?php echo (!empty($userspn_input['button_text']) ? esc_html($userspn_input['button_text']) : esc_html($userspn_value)); ?>"
                placeholder="<?php echo (array_key_exists('placeholder', $userspn_input) ? esc_html($userspn_input['placeholder']) : ''); ?>"
                
                <?php /* Custom data attributes */ ?>
                <?php echo wp_kses_post($userspn_parent_block); ?>
              />
            <?php
            break;
        }
        break;
      case 'select':
        if (!empty($userspn_input['options']) && is_array($userspn_input['options'])) {
          ?>
          <select 
            id="<?php echo esc_attr($userspn_input['id']); ?>" 
            name="<?php echo esc_attr($userspn_input['id']) . ((array_key_exists('multiple', $userspn_input) && $userspn_input['multiple']) ? '[]' : ''); ?>" 
            class="userspn-field <?php echo array_key_exists('class', $userspn_input) ? esc_attr($userspn_input['class']) : ''; ?>"
            <?php echo (array_key_exists('multiple', $userspn_input) && $userspn_input['multiple']) ? 'multiple' : ''; ?>
            <?php echo ((array_key_exists('required', $userspn_input) && $userspn_input['required'] == true) ? 'required' : ''); ?>
            <?php echo (((array_key_exists('disabled', $userspn_input) && $userspn_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?>
            <?php echo wp_kses_post($userspn_parent_block); ?>
          >
            <?php if (array_key_exists('placeholder', $userspn_input) && !empty($userspn_input['placeholder'])): ?>
              <option value=""><?php echo esc_html($userspn_input['placeholder']); ?></option>
            <?php endif; ?>
            
            <?php 
            $selected_values = array_key_exists('multiple', $userspn_input) && $userspn_input['multiple'] ? 
              (is_array($userspn_value) ? $userspn_value : array()) : 
              array($userspn_value);
            
            foreach ($userspn_input['options'] as $value => $label): 
              $is_selected = in_array($value, $selected_values);
            ?>
              <option 
                value="<?php echo esc_attr($value); ?>"
                <?php echo $is_selected ? 'selected="selected"' : ''; ?>
              >
                <?php echo esc_html($label); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php
        }
        break;
      case 'textarea':
        ?>
          <textarea id="<?php echo esc_attr($userspn_input['id']) . ((array_key_exists('multiple', $userspn_input) && $userspn_input['multiple']) ? '[]' : ''); ?>" name="<?php echo esc_attr($userspn_input['id']) . ((array_key_exists('multiple', $userspn_input) && $userspn_input['multiple']) ? '[]' : ''); ?>" <?php echo wp_kses_post($userspn_parent_block); ?> class="userspn-field <?php echo array_key_exists('class', $userspn_input) ? esc_attr($userspn_input['class']) : ''; ?>" <?php echo ((array_key_exists('required', $userspn_input) && $userspn_input['required'] == true) ? 'required' : ''); ?> <?php echo (((array_key_exists('disabled', $userspn_input) && $userspn_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?> <?php echo (array_key_exists('multiple', $userspn_input) && $userspn_input['multiple'] ? 'multiple' : ''); ?> placeholder="<?php echo (array_key_exists('placeholder', $userspn_input) ? esc_attr($userspn_input['placeholder']) : ''); ?>"><?php echo esc_html($userspn_value); ?></textarea>
        <?php
        break;
      case 'image':
        ?>
          <div class="userspn-field userspn-images-block" <?php echo wp_kses_post($userspn_parent_block); ?> data-userspn-multiple="<?php echo (array_key_exists('multiple', $userspn_input) && $userspn_input['multiple']) ? 'true' : 'false'; ?>">
            <?php if (!empty($userspn_value)): ?>
              <div class="userspn-images">
                <?php foreach (explode(',', $userspn_value) as $userspn_image): ?>
                  <?php echo wp_get_attachment_image($userspn_image, 'medium'); ?>
                <?php endforeach ?>
              </div>

              <div class="userspn-text-align-center userspn-position-relative"><a href="#" class="userspn-btn userspn-btn-mini userspn-image-btn"><?php echo (array_key_exists('multiple', $userspn_input) && $userspn_input['multiple']) ? esc_html(__('Edit images', 'userspn')) : esc_html(__('Edit image', 'userspn')); ?></a></div>
            <?php else: ?>
              <div class="userspn-images"></div>

              <div class="userspn-text-align-center userspn-position-relative"><a href="#" class="userspn-btn userspn-btn-mini userspn-image-btn"><?php echo (array_key_exists('multiple', $userspn_input) && $userspn_input['multiple']) ? esc_html(__('Add images', 'userspn')) : esc_html(__('Add image', 'userspn')); ?></a></div>
            <?php endif ?>

            <input id="<?php echo esc_attr($userspn_input['id']); ?>" name="<?php echo esc_attr($userspn_input['id']); ?>" class="userspn-display-none userspn-image-input" type="text" value="<?php echo esc_attr($userspn_value); ?>"/>
          </div>
        <?php
        break;
      case 'video':
        ?>
        <div class="userspn-field userspn-videos-block" <?php echo wp_kses_post($userspn_parent_block); ?>>
            <?php if (!empty($userspn_value)): ?>
              <div class="userspn-videos">
                <?php foreach (explode(',', $userspn_value) as $userspn_video): ?>
                  <div class="userspn-video userspn-tooltip" title="<?php echo esc_html(get_the_title($userspn_video)); ?>"><i class="dashicons dashicons-media-video"></i></div>
                <?php endforeach ?>
              </div>

              <div class="userspn-text-align-center userspn-position-relative"><a href="#" class="userspn-btn userspn-video-btn"><?php echo (array_key_exists('multiple', $userspn_input) && $userspn_input['multiple']) ? esc_html(__('Edit videos', 'userspn')) : esc_html(__('Edit video', 'userspn')); ?></a></div>
            <?php else: ?>
              <div class="userspn-videos"></div>

              <div class="userspn-text-align-center userspn-position-relative"><a href="#" class="userspn-btn userspn-video-btn"><?php echo (array_key_exists('multiple', $userspn_input) && $userspn_input['multiple']) ? esc_html(__('Add videos', 'userspn')) : esc_html(__('Add video', 'userspn')); ?></a></div>
            <?php endif ?>

            <input id="<?php echo esc_attr($userspn_input['id']); ?>" name="<?php echo esc_attr($userspn_input['id']); ?>" class="userspn-display-none userspn-video-input" type="text" value="<?php echo esc_attr($userspn_value); ?>"/>
          </div>
        <?php
        break;
      case 'audio':
        ?>
          <div class="userspn-field userspn-audios-block" <?php echo wp_kses_post($userspn_parent_block); ?>>
            <?php if (!empty($userspn_value)): ?>
              <div class="userspn-audios">
                <?php foreach (explode(',', $userspn_value) as $userspn_audio): ?>
                  <div class="userspn-audio userspn-tooltip" title="<?php echo esc_html(get_the_title($userspn_audio)); ?>"><i class="dashicons dashicons-media-audio"></i></div>
                <?php endforeach ?>
              </div>

              <div class="userspn-text-align-center userspn-position-relative"><a href="#" class="userspn-btn userspn-btn-mini userspn-audio-btn"><?php echo (array_key_exists('multiple', $userspn_input) && $userspn_input['multiple']) ? esc_html(__('Edit audios', 'userspn')) : esc_html(__('Edit audio', 'userspn')); ?></a></div>
            <?php else: ?>
              <div class="userspn-audios"></div>

              <div class="userspn-text-align-center userspn-position-relative"><a href="#" class="userspn-btn userspn-btn-mini userspn-audio-btn"><?php echo (array_key_exists('multiple', $userspn_input) && $userspn_input['multiple']) ? esc_html(__('Add audios', 'userspn')) : esc_html(__('Add audio', 'userspn')); ?></a></div>
            <?php endif ?>

            <input id="<?php echo esc_attr($userspn_input['id']); ?>" name="<?php echo esc_attr($userspn_input['id']); ?>" class="userspn-display-none userspn-audio-input" type="text" value="<?php echo esc_attr($userspn_value); ?>"/>
          </div>
        <?php
        break;
      case 'file':
        ?>
          <div class="userspn-field userspn-files-block" <?php echo wp_kses_post($userspn_parent_block); ?>>
            <?php if (!empty($userspn_value)): ?>
              <div class="userspn-files userspn-text-align-center">
                <?php foreach (explode(',', $userspn_value) as $userspn_file): ?>
                  <embed src="<?php echo esc_url(wp_get_attachment_url($userspn_file)); ?>" type="application/pdf" class="userspn-embed-file"/>
                <?php endforeach ?>
              </div>

              <div class="userspn-text-align-center userspn-position-relative"><a href="#" class="userspn-btn userspn-btn-mini userspn-file-btn"><?php echo (array_key_exists('multiple', $userspn_input) && $userspn_input['multiple']) ? esc_html(__('Edit files', 'userspn')) : esc_html(__('Edit file', 'userspn')); ?></a></div>
            <?php else: ?>
              <div class="userspn-files"></div>

              <div class="userspn-text-align-center userspn-position-relative"><a href="#" class="userspn-btn userspn-btn-mini userspn-btn-mini userspn-file-btn"><?php echo (array_key_exists('multiple', $userspn_input) && $userspn_input['multiple']) ? esc_html(__('Add files', 'userspn')) : esc_html(__('Add file', 'userspn')); ?></a></div>
            <?php endif ?>

            <input id="<?php echo esc_attr($userspn_input['id']); ?>" name="<?php echo esc_attr($userspn_input['id']); ?>" class="userspn-display-none userspn-file-input userspn-btn-mini" type="text" value="<?php echo esc_attr($userspn_value); ?>"/>
          </div>
        <?php
        break;
      case 'editor':
        ?>
          <div class="userspn-field" <?php echo wp_kses_post($userspn_parent_block); ?>>
            <textarea id="<?php echo esc_attr($userspn_input['id']); ?>" name="<?php echo esc_attr($userspn_input['id']); ?>" class="userspn-input userspn-width-100-percent userspn-wysiwyg"><?php echo ((empty($userspn_value)) ? (array_key_exists('placeholder', $userspn_input) ? esc_attr($userspn_input['placeholder']) : '') : esc_html($userspn_value)); ?></textarea>
          </div>
        <?php
        break;
      case 'html':
        ?>
          <div class="userspn-field" <?php echo wp_kses_post($userspn_parent_block); ?>>
            <?php echo !empty($userspn_input['html_content']) ? wp_kses(do_shortcode($userspn_input['html_content']), USERSPN_KSES) : ''; ?>
          </div>
        <?php
        break;
      case 'html_multi':
        switch ($userspn_type) {
          case 'user':
            $html_multi_fields_length = !empty(get_user_meta($userspn_id, $userspn_input['html_multi_fields'][0]['id'], true)) ? count(get_user_meta($userspn_id, $userspn_input['html_multi_fields'][0]['id'], true)) : 0;
            break;
          case 'post':
            $html_multi_fields_length = !empty(get_post_meta($userspn_id, $userspn_input['html_multi_fields'][0]['id'], true)) ? count(get_post_meta($userspn_id, $userspn_input['html_multi_fields'][0]['id'], true)) : 0;
            break;
          case 'option':
            $html_multi_fields_length = !empty(get_option($userspn_input['html_multi_fields'][0]['id'])) ? count(get_option($userspn_input['html_multi_fields'][0]['id'])) : 0;
        }

        ?>
          <div class="userspn-field userspn-html-multi-wrapper userspn-mb-50" <?php echo wp_kses_post($userspn_parent_block); ?>>
            <?php if ($html_multi_fields_length): ?>
              <?php foreach (range(0, ($html_multi_fields_length - 1)) as $length_index): ?>
                <div class="userspn-html-multi-group userspn-display-table userspn-width-100-percent userspn-mb-30">
                  <div class="userspn-display-inline-table userspn-width-90-percent">
                    <?php foreach ($userspn_input['html_multi_fields'] as $index => $html_multi_field): ?>
                      <label><?php echo esc_html($html_multi_field['label']); ?></label>

                      <?php self::userspn_input_builder($html_multi_field, $userspn_type, $userspn_id, false, true, $length_index); ?>
                    <?php endforeach ?>
                  </div>
                  <div class="userspn-display-inline-table userspn-width-10-percent userspn-text-align-center">
                    <i class="material-icons-outlined userspn-cursor-move userspn-multi-sorting userspn-vertical-align-super userspn-tooltip" title="<?php esc_html_e('Order element', 'userspn'); ?>">drag_handle</i>
                  </div>

                  <div class="userspn-text-align-right">
                    <a href="#" class="userspn-html-multi-remove-btn"><i class="material-icons-outlined userspn-cursor-pointer userspn-tooltip" title="<?php esc_html_e('Remove element', 'userspn'); ?>">remove</i></a>
                  </div>
                </div>
              <?php endforeach ?>
            <?php else: ?>
              <div class="userspn-html-multi-group userspn-mb-50">
                <div class="userspn-display-inline-table userspn-width-90-percent">
                  <?php foreach ($userspn_input['html_multi_fields'] as $html_multi_field): ?>
                    <?php self::userspn_input_builder($html_multi_field, $userspn_type); ?>
                  <?php endforeach ?>
                </div>
                <div class="userspn-display-inline-table userspn-width-10-percent userspn-text-align-center">
                  <i class="material-icons-outlined userspn-cursor-move userspn-multi-sorting userspn-vertical-align-super userspn-tooltip" title="<?php esc_html_e('Order element', 'userspn'); ?>">drag_handle</i>
                </div>

                <div class="userspn-text-align-right">
                  <a href="#" class="userspn-html-multi-remove-btn userspn-tooltip" title="<?php esc_html_e('Remove element', 'userspn'); ?>"><i class="material-icons-outlined userspn-cursor-pointer">remove</i></a>
                </div>
              </div>
            <?php endif ?>

            <div class="userspn-text-align-right">
              <a href="#" class="userspn-html-multi-add-btn userspn-tooltip" title="<?php esc_html_e('Add element', 'userspn'); ?>"><i class="material-icons-outlined userspn-cursor-pointer userspn-font-size-40">add</i></a>
            </div>
          </div>
        <?php
        break;
      case 'audio_recorder':
        // Enqueue CSS and JS files for audio recorder
        wp_enqueue_style('userspn-audio-recorder', USERSPN_URL . 'assets/css/userspn-audio-recorder.css', array(), '1.0.0');
        wp_enqueue_script('userspn-audio-recorder', USERSPN_URL . 'assets/js/userspn-audio-recorder.js', array('jquery'), '1.0.0', true);
        
        // Localize script with AJAX data
        wp_localize_script('userspn-audio-recorder', 'userspn_audio_recorder_vars', array(
          'ajax_url' => admin_url('admin-ajax.php'),
          'ajax_nonce' => wp_create_nonce('userspn_audio_nonce'),
        ));
        
        ?>
          <div class="userspn-audio-recorder-status userspn-display-none-soft">
            <p class="userspn-recording-status"><?php esc_html_e('Ready to record', 'userspn'); ?></p>
          </div>
          
          <div class="userspn-audio-recorder-wrapper">
            <div class="userspn-audio-recorder-controls">
              <div class="userspn-display-table userspn-width-100-percent">
                <div class="userspn-display-inline-table userspn-width-50-percent userspn-tablet-display-block userspn-tablet-width-100-percent userspn-text-align-center userspn-mb-20">
                  <button type="button" class="userspn-btn userspn-btn-primary userspn-start-recording" <?php echo (((array_key_exists('disabled', $userspn_input) && $userspn_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?>>
                    <i class="material-icons-outlined userspn-vertical-align-middle">mic</i>
                    <?php esc_html_e('Start recording', 'userspn'); ?>
                  </button>
                </div>

                <div class="userspn-display-inline-table userspn-width-50-percent userspn-tablet-display-block userspn-tablet-width-100-percent userspn-text-align-center userspn-mb-20">
                  <button type="button" class="userspn-btn userspn-btn-secondary userspn-stop-recording" style="display: none;" <?php echo (((array_key_exists('disabled', $userspn_input) && $userspn_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?>>
                    <i class="material-icons-outlined userspn-vertical-align-middle">stop</i>
                    <?php esc_html_e('Stop recording', 'userspn'); ?>
                  </button>
                </div>
              </div>

              <div class="userspn-display-table userspn-width-100-percent">
                <div class="userspn-display-inline-table userspn-width-50-percent userspn-tablet-display-block userspn-tablet-width-100-percent userspn-text-align-center userspn-mb-20">
                  <button type="button" class="userspn-btn userspn-btn-secondary userspn-play-audio" style="display: none;" <?php echo (((array_key_exists('disabled', $userspn_input) && $userspn_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?>>
                    <i class="material-icons-outlined userspn-vertical-align-middle">play_arrow</i>
                    <?php esc_html_e('Play audio', 'userspn'); ?>
                  </button>
                </div>

                <div class="userspn-display-inline-table userspn-width-50-percent userspn-tablet-display-block userspn-tablet-width-100-percent userspn-text-align-center userspn-mb-20">
                  <button type="button" class="userspn-btn userspn-btn-secondary userspn-stop-audio" style="display: none;" <?php echo (((array_key_exists('disabled', $userspn_input) && $userspn_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?>>
                    <i class="material-icons-outlined userspn-vertical-align-middle">stop</i>
                    <?php esc_html_e('Stop audio', 'userspn'); ?>
                  </button>
                </div>
              </div>
            </div>

            <div class="userspn-audio-recorder-visualizer" style="display: none;">
              <canvas class="userspn-audio-canvas" width="300" height="60"></canvas>
            </div>

            <div class="userspn-audio-recorder-timer" style="display: none;">
              <span class="userspn-recording-time">00:00</span>
            </div>

            <div class="userspn-audio-transcription-controls userspn-display-none-soft userspn-display-table userspn-width-100-percent userspn-mb-20">
              <div class="userspn-display-inline-table userspn-width-50-percent userspn-tablet-display-block userspn-tablet-width-100-percent userspn-text-align-center">
                <button type="button" class="userspn-btn userspn-btn-primary userspn-transcribe-audio" <?php echo (((array_key_exists('disabled', $userspn_input) && $userspn_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?>>
                  <i class="material-icons-outlined userspn-vertical-align-middle">translate</i>
                  <?php esc_html_e('Transcribe Audio', 'userspn'); ?>
                </button>
              </div>

              <div class="userspn-display-inline-table userspn-width-50-percent userspn-tablet-display-block userspn-tablet-width-100-percent userspn-text-align-center">
                <button type="button" class="userspn-btn userspn-btn-secondary userspn-clear-transcription" <?php echo (((array_key_exists('disabled', $userspn_input) && $userspn_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?>>
                  <i class="material-icons-outlined userspn-vertical-align-middle">clear</i>
                  <?php esc_html_e('Clear', 'userspn'); ?>
                </button>
              </div>
            </div>

            <div class="userspn-audio-transcription-loading">
              <?php echo esc_html(USERSPN_Data::userspn_loader()); ?>
            </div>

            <div class="userspn-audio-transcription-result">
              <textarea 
                id="<?php echo esc_attr($userspn_input['id']); ?>" 
                name="<?php echo esc_attr($userspn_input['id']); ?>" 
                class="userspn-field userspn-transcription-textarea <?php echo array_key_exists('class', $userspn_input) ? esc_attr($userspn_input['class']) : ''; ?>" 
                placeholder="<?php echo (array_key_exists('placeholder', $userspn_input) ? esc_attr($userspn_input['placeholder']) : esc_attr__('Transcribed text will appear here...', 'userspn')); ?>"
                <?php echo ((array_key_exists('required', $userspn_input) && $userspn_input['required'] == true) ? 'required' : ''); ?>
                <?php echo (((array_key_exists('disabled', $userspn_input) && $userspn_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?>
                <?php echo wp_kses_post($userspn_parent_block); ?>
                rows="4"
                style="width: 100%; margin-top: 10px;"
              ><?php echo esc_textarea($userspn_value); ?></textarea>
            </div>

            <div class="userspn-audio-transcription-error userspn-display-none-soft">
              <p class="userspn-error-message"></p>
            </div>

            <div class="userspn-audio-transcription-success userspn-display-none-soft">
              <p class="userspn-success-message"></p>
            </div>

            <!-- Hidden input to store audio data -->
            <input type="hidden" 
                  id="<?php echo esc_attr($userspn_input['id']); ?>_audio_data" 
                  name="<?php echo esc_attr($userspn_input['id']); ?>_audio_data" 
                  value="" />
          </div>
        <?php
        break;
    }
  }

  public static function userspn_input_wrapper_builder($input_array, $type, $userspn_id = 0, $disabled = 0, $userspn_format = 'half'){
    ?>
      <?php if (array_key_exists('section', $input_array) && !empty($input_array['section'])): ?>      
        <?php if ($input_array['section'] == 'start'): ?>
          <div class="userspn-toggle-wrapper userspn-section-wrapper userspn-position-relative userspn-mb-30 <?php echo array_key_exists('class', $input_array) ? esc_attr($input_array['class']) : ''; ?>" id="<?php echo array_key_exists('id', $input_array) ? esc_attr($input_array['id']) : ''; ?>">
            <?php if (array_key_exists('description', $input_array) && !empty($input_array['description'])): ?>
              <i class="material-icons-outlined userspn-section-helper userspn-color-main-0 userspn-tooltip" title="<?php echo wp_kses_post($input_array['description']); ?>">help</i>
            <?php endif ?>

            <a href="#" class="userspn-toggle userspn-width-100-percent userspn-text-decoration-none">
              <div class="userspn-display-table userspn-width-100-percent userspn-mb-20">
                <div class="userspn-display-inline-table userspn-width-90-percent">
                  <label class="userspn-cursor-pointer userspn-mb-20 userspn-color-main-0"><?php echo wp_kses_post($input_array['label']); ?></label>
                </div>
                <div class="userspn-display-inline-table userspn-width-10-percent userspn-text-align-right">
                  <i class="material-icons-outlined userspn-cursor-pointer userspn-color-main-0">add</i>
                </div>
              </div>
            </a>

            <div class="userspn-content userspn-pl-10 userspn-toggle-content userspn-mb-20 userspn-display-none-soft">
        <?php elseif ($input_array['section'] == 'end'): ?>
            </div>
          </div>
        <?php endif ?>
      <?php else: ?>
        <div class="userspn-input-wrapper <?php echo esc_attr($input_array['id']); ?> <?php echo !empty($input_array['tabs']) ? 'userspn-input-tabbed' : ''; ?> userspn-input-field-<?php echo esc_attr($input_array['input']); ?> <?php echo (!empty($input_array['required']) && $input_array['required'] == true) ? 'userspn-input-field-required' : ''; ?> <?php echo ($disabled) ? 'userspn-input-field-disabled' : ''; ?> userspn-mb-30">
          <?php if (array_key_exists('label', $input_array) && !empty($input_array['label'])): ?>
            <div class="userspn-display-inline-table <?php echo (($userspn_format == 'half' && !(array_key_exists('type', $input_array) && $input_array['type'] == 'submit')) ? 'userspn-width-40-percent' : 'userspn-width-100-percent'); ?> userspn-tablet-display-block userspn-tablet-width-100-percent userspn-vertical-align-top">
              <div class="userspn-p-10 <?php echo (array_key_exists('parent', $input_array) && !empty($input_array['parent']) && $input_array['parent'] != 'this') ? 'userspn-pl-30' : ''; ?>">
                <label class="userspn-vertical-align-middle userspn-display-block <?php echo (array_key_exists('description', $input_array) && !empty($input_array['description'])) ? 'userspn-toggle' : ''; ?>" for="<?php echo esc_attr($input_array['id']); ?>"><?php echo wp_kses($input_array['label'], USERSPN_KSES); ?> <?php echo (array_key_exists('required', $input_array) && !empty($input_array['required']) && $input_array['required'] == true) ? '<span class="userspn-tooltip" title="' . esc_html(__('Required field', 'userspn')) . '">*</span>' : ''; ?><?php echo (array_key_exists('description', $input_array) && !empty($input_array['description'])) ? '<i class="material-icons-outlined userspn-cursor-pointer userspn-float-right">add</i>' : ''; ?></label>

                <?php if (array_key_exists('description', $input_array) && !empty($input_array['description'])): ?>
                  <div class="userspn-toggle-content userspn-display-none-soft">
                    <small><?php echo wp_kses_post(wp_specialchars_decode($input_array['description'])); ?></small>
                  </div>
                <?php endif ?>
              </div>
            </div>
          <?php endif ?>

          <div class="userspn-display-inline-table <?php echo ((array_key_exists('label', $input_array) && empty($input_array['label'])) ? 'userspn-width-100-percent' : (($userspn_format == 'half' && !(array_key_exists('type', $input_array) && $input_array['type'] == 'submit')) ? 'userspn-width-60-percent' : 'userspn-width-100-percent')); ?> userspn-tablet-display-block userspn-tablet-width-100-percent userspn-vertical-align-top">
            <div class="userspn-p-10 <?php echo (array_key_exists('parent', $input_array) && !empty($input_array['parent']) && $input_array['parent'] != 'this') ? 'userspn-pl-30' : ''; ?>">
              <div class="userspn-input-field"><?php self::userspn_input_builder($input_array, $type, $userspn_id, $disabled); ?></div>
            </div>
          </div>
        </div>
      <?php endif ?>
    <?php
  }

  /**
   * Display wrapper for field values with format control
   * 
   * @param array $input_array The input array containing field configuration
   * @param string $type The type of field (user, post, option)
   * @param int $userspn_id The ID of the user/post/option
   * @param int $userspn_meta_array Whether the field is part of a meta array
   * @param int $userspn_array_index The index in the meta array
   * @param string $userspn_format The display format ('half' or 'full')
   * @return string Formatted HTML output
   */
  public static function userspn_input_display_wrapper($input_array, $type, $userspn_id = 0, $userspn_meta_array = 0, $userspn_array_index = 0, $userspn_format = 'half') {
    ob_start();
    ?>
    <?php if (array_key_exists('section', $input_array) && !empty($input_array['section'])): ?>      
      <?php if ($input_array['section'] == 'start'): ?>
        <div class="userspn-toggle-wrapper userspn-section-wrapper userspn-position-relative userspn-mb-30 <?php echo array_key_exists('class', $input_array) ? esc_attr($input_array['class']) : ''; ?>" id="<?php echo array_key_exists('id', $input_array) ? esc_attr($input_array['id']) : ''; ?>">
          <?php if (array_key_exists('description', $input_array) && !empty($input_array['description'])): ?>
            <i class="material-icons-outlined userspn-section-helper userspn-color-main-0 userspn-tooltip" title="<?php echo wp_kses_post($input_array['description']); ?>">help</i>
          <?php endif ?>

          <a href="#" class="userspn-toggle userspn-width-100-percent userspn-text-decoration-none">
            <div class="userspn-display-table userspn-width-100-percent userspn-mb-20">
              <div class="userspn-display-inline-table userspn-width-90-percent">
                <label class="userspn-cursor-pointer userspn-mb-20 userspn-color-main-0"><?php echo wp_kses($input_array['label'], USERSPN_KSES); ?></label>
              </div>
              <div class="userspn-display-inline-table userspn-width-10-percent userspn-text-align-right">
                <i class="material-icons-outlined userspn-cursor-pointer userspn-color-main-0">add</i>
              </div>
            </div>
          </a>

          <div class="userspn-content userspn-pl-10 userspn-toggle-content userspn-mb-20 userspn-display-none-soft">
      <?php elseif ($input_array['section'] == 'end'): ?>
          </div>
        </div>
      <?php endif ?>
    <?php else: ?>
      <div class="userspn-input-wrapper <?php echo esc_attr($input_array['id']); ?> userspn-input-display-<?php echo esc_attr($input_array['input']); ?> <?php echo (!empty($input_array['required']) && $input_array['required'] == true) ? 'userspn-input-field-required' : ''; ?> userspn-mb-30">
        <?php if (array_key_exists('label', $input_array) && !empty($input_array['label'])): ?>
          <div class="userspn-display-inline-table <?php echo ($userspn_format == 'half' ? 'userspn-width-40-percent' : 'userspn-width-100-percent'); ?> userspn-tablet-display-block userspn-tablet-width-100-percent userspn-vertical-align-top">
            <div class="userspn-p-10 <?php echo (array_key_exists('parent', $input_array) && !empty($input_array['parent']) && $input_array['parent'] != 'this') ? 'userspn-pl-30' : ''; ?>">
              <label class="userspn-vertical-align-middle userspn-display-block <?php echo (array_key_exists('description', $input_array) && !empty($input_array['description'])) ? 'userspn-toggle' : ''; ?>" for="<?php echo esc_attr($input_array['id']); ?>">
                <?php echo wp_kses($input_array['label'], USERSPN_KSES); ?>
                <?php echo (array_key_exists('required', $input_array) && !empty($input_array['required']) && $input_array['required'] == true) ? '<span class="userspn-tooltip" title="' . esc_html(__('Required field', 'userspn')) . '">*</span>' : ''; ?>
                <?php echo (array_key_exists('description', $input_array) && !empty($input_array['description'])) ? '<i class="material-icons-outlined userspn-cursor-pointer userspn-float-right">add</i>' : ''; ?>
              </label>

              <?php if (array_key_exists('description', $input_array) && !empty($input_array['description'])): ?>
                <div class="userspn-toggle-content userspn-display-none-soft">
                  <small><?php echo wp_kses_post(wp_specialchars_decode($input_array['description'])); ?></small>
                </div>
              <?php endif ?>
            </div>
          </div>
        <?php endif; ?>

        <div class="userspn-display-inline-table <?php echo ((array_key_exists('label', $input_array) && empty($input_array['label'])) ? 'userspn-width-100-percent' : ($userspn_format == 'half' ? 'userspn-width-60-percent' : 'userspn-width-100-percent')); ?> userspn-tablet-display-block userspn-tablet-width-100-percent userspn-vertical-align-top">
          <div class="userspn-p-10 <?php echo (array_key_exists('parent', $input_array) && !empty($input_array['parent']) && $input_array['parent'] != 'this') ? 'userspn-pl-30' : ''; ?>">
            <div class="userspn-input-field">
              <?php self::userspn_input_display($input_array, $type, $userspn_id, $userspn_meta_array, $userspn_array_index); ?>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
    <?php
    return ob_get_clean();
  }

  public static function userspn_input_editor_builder($input_array) {
    $post_id = get_the_ID();
    ?>      
      <div class="userspn-toggle-wrapper userspn-mb-10 <?php echo esc_attr($input_array['id']); ?>">
        <a href="#" class="userspn-btn userspn-toggle userspn-text-align-left userspn-width-100-percent"><span class="userspn-input-name-span"><?php echo !empty($input_array['label']) ? esc_html($input_array['label']) : esc_html(__('New field', 'userspn')); ?></span> <i class="material-icons-outlined userspn-float-right userspn-cursor-pointer">add</i></a>

        <div class="userspn-content userspn-toggle-content userspn-mb-20 userspn-pl-20 userspn-display-none-soft">
          <ul id="<?php echo esc_attr($input_array['id']); ?>" class="userspn-input-editor-builder-ul userspn-list-style-none">
            <li>
              <label class="userspn-display-block"><?php esc_html_e('Input name', 'userspn'); ?></label>
              <input type="text" name="userspn-input-name" id="userspn-input-name" class="userspn-input userspn-input-name userspn-input userspn-width-100-percent" value="<?php echo !empty($input_array['label']) ? esc_html($input_array['label']) : esc_html(__('New field', 'userspn')); ?>" placeholder="<?php esc_html_e('Input name', 'userspn'); ?>" <?php echo ($input_array['form_type'] == 'option' && in_array($input_array['id'], ['first_name', 'last_name']) ? 'disabled=""' : '') ?>>
            </li>

            <li>
              <label class="userspn-display-block"><?php esc_html_e('Input type', 'userspn'); ?></label>
              <select class="userspn-select userspn-input-type userspn-select search-disabled userspn-cursor-pointer userspn-vertical-align-top userspn-width-100-percent" <?php echo ($input_array['form_type'] == 'option' && in_array($input_array['id'], ['first_name', 'last_name']) ? 'disabled=""' : '') ?>>
                <option value="" disabled=""><?php esc_html_e('Select type', 'userspn'); ?></option>
                <?php foreach (['input' => esc_html(__('Input', 'userspn')), 'select' => esc_html(__('Select', 'userspn')), 'textarea' => esc_html(__('Textarea', 'userspn')), ] as $global_key => $global_value): ?>
                  <option value="<?php echo esc_attr($global_key); ?>" <?php echo ($input_array['input'] == $global_key) ? 'selected=""' : ''; ?>><?php echo esc_html($global_value); ?></option>
                <?php endforeach ?>
              </select>
            </li>

            <li class="userspn-input-subtype">
              <label class="userspn-display-block"><?php esc_html_e('Input subtype', 'userspn'); ?></label>

              <select class="userspn-select userspn-input-subtype userspn-select search-disabled userspn-cursor-pointer userspn-vertical-align-top userspn-width-100-percent" <?php echo ($input_array['form_type'] == 'option' && in_array($input_array['id'], ['first_name', 'last_name']) ? 'disabled=""' : '') ?>>
                <option value="" disabled=""><?php esc_html_e('Select subtype', 'userspn'); ?></option>
                <?php foreach (['text' => esc_html(__('Text', 'userspn')), 'number' => esc_html(__('Number', 'userspn')), 'date' => esc_html(__('Date', 'userspn')),  'time' => esc_html(__('Time', 'userspn')),'url' => esc_html(__('Url', 'userspn')),  'email' => esc_html(__('Email', 'userspn')), 'password' => esc_html(__('Password', 'userspn')), ] as $global_key => $global_value): ?>
                  <option value="<?php echo esc_attr($global_key); ?>" <?php echo (!empty($input_array['type']) && $input_array['type'] == $global_key) ? 'selected=""' : ''; ?>><?php echo esc_attr($global_value); ?></option>
                <?php endforeach ?>
              </select>
            </li>

            <li class="userspn-select-subtype userspn-display-none-soft">
              <label class="userspn-display-block"><?php esc_html_e('Selector subtype', 'userspn'); ?></label>

              <select class="userspn-select userspn-select-subtype userspn-select search-disabled userspn-cursor-pointer userspn-vertical-align-top userspn-width-100-percent" <?php echo ($input_array['form_type'] == 'option' && in_array($input_array['id'], ['first_name', 'last_name']) ? 'disabled=""' : '') ?>>
                <option value="" disabled=""><?php esc_html_e('Select multiple', 'userspn'); ?></option>
                <?php foreach (['false' => esc_html(__('Simple selector', 'userspn')), 'true' => esc_html(__('Multiple selector', 'userspn')), ] as $global_key => $global_value): ?>
                  <option value="<?php echo esc_attr($global_key); ?>" <?php echo ((!empty($input_array['multiple']) && $input_array['multiple']) == $global_key) ? 'selected=""' : ''; ?>><?php echo esc_html($global_value); ?></option>
                <?php endforeach ?>
              </select>

              <textarea class="userspn-input userspn-select-options userspn-width-100-percent" name="userspn-select-options" class="userspn-input width-100-percent"><?php echo !empty($input_array['options']) ? implode(PHP_EOL, esc_html($input_array['options'])) : esc_html(__('One option per line', 'userspn')); ?></textarea>
            </li>

            <li>
              <label class="userspn-display-block"><?php esc_html_e('Input classes', 'userspn'); ?></label>
              <input type="text" name="userspn-input-class" id="userspn-input-class" class="userspn-input-class userspn-field userspn-input userspn-width-100-percent" value="<?php echo esc_attr($input_array['class']); ?>" placeholder="<?php esc_html_e('Classes separated by spaces', 'userspn'); ?>">
            </li>

            <li>
              <label class="userspn-display-block"><?php esc_html_e('Input required', 'userspn'); ?></label>
              <select class="userspn-input-required userspn-select search-disabled userspn-cursor-pointer userspn-vertical-align-top userspn-width-100-percent" <?php echo ($input_array['form_type'] == 'option' && in_array($input_array['id'], ['first_name', 'last_name']) ? 'disabled=""' : '') ?>>
                <option value="" disabled=""><?php esc_html_e('Select requirement', 'userspn'); ?></option>
                <option value="false" <?php echo $input_array['required'] == 'false' ? 'selected=""' : ''; ?>><?php esc_html_e('Not required', 'userspn'); ?></option>
                <option value="true" <?php echo $input_array['required'] == 'true' ? 'selected=""' : ''; ?>><?php esc_html_e('Required', 'userspn'); ?></option>
              </select>
            </li>
          </ul>

          <div class="userspn-text-align-center">
            <div class="userspn-display-table userspn-width-100-percent">
              <?php if (!in_array($input_array['id'], ['first_name', 'last_name'])): ?>
                <div class="userspn-display-inline-table userspn-width-30-percent userspn-tablet-display-block userspn-tablet-width-100-percent">
                  <a href="#" class="userspn-popup" data-userspn-popup-id="userspn-input-editor-builder-btn-remove-popup-<?php echo esc_attr($input_array['id']); ?>"><?php esc_html_e('Remove field', 'userspn'); ?></a>

                  <div id="userspn-input-editor-builder-btn-remove-popup-<?php echo esc_attr($input_array['id']); ?>" data-userspn-input-id="<?php echo esc_attr($input_array['id']); ?>" data-userspn-input-type="<?php echo array_key_exists('form_type', $input_array) ? esc_attr($input_array['form_type']) : ''; ?>" data-userspn-meta="<?php echo array_key_exists('meta', $input_array) ? esc_attr($input_array['meta']) : ''; ?>" class="userspn-popup userspn-input-editor-builder-btn-remove-popup userspn-display-none-soft">
                    <p><?php esc_html_e('The field will be removed. This action cannot be undone.', 'userspn'); ?></p>
                    <div class="userspn-display-table userspn-width-100-percent">
                      <div class="userspn-display-inline-table userspn-width-30-percent userspn-tablet-display-block userspn-tablet-width-100-percent userspn-text-align-center">
                        <a href="#" class="userspn-popup-close"><?php esc_html_e('Cancel', 'userspn'); ?></a>
                      </div>

                      <div class="userspn-display-inline-table userspn-width-70-percent userspn-tablet-display-block userspn-tablet-width-100-percent userspn-text-align-center">
                        <a href="#" class="userspn-btn userspn-input-editor-builder-btn-remove userspn-pl-50 userspn-pr-50" data-userspn-post-id="<?php echo esc_attr($post_id); ?>"><?php esc_html_e('Remove', 'userspn'); ?></a>
                      </div>
                    </div>

                  </div>
                </div>
                <div class="userspn-display-inline-table userspn-width-70-percent userspn-tablet-display-block userspn-tablet-width-100-percent userspn-text-align-right">
                  <a href="#" class="userspn-btn userspn-input-editor-builder-btn-save userspn-pl-50 userspn-pr-50" data-userspn-post-id="<?php echo esc_attr($post_id); ?>"><?php esc_html_e('Save field', 'userspn'); ?></a>
                </div>
              <?php endif ?>
            </div>
          </div>
        </div>
      </div>
    <?php
  }

  public static function userspn_sanitizer($value, $node = '', $type = '', $field_config = []) {
    // Use the new validation system
    $result = USERSPN_Validation::userspn_validate_and_sanitize($value, $node, $type, $field_config);
    
    // If validation failed, return empty value and log the error
    if (is_wp_error($result)) {
        return '';
    }
    
    return $result;
  }
}