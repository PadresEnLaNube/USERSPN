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

  public static function input_builder($userspn_input, $userspn_type, $userspn_id = 0, $disabled = 0, $userspn_meta_array = 0, $userspn_array_index = 0) {
    // userspn_Forms::input_builder($userspn_input, $userspn_type, $userspn_id = 0, $disabled = 0, $userspn_meta_array = 0, $userspn_array_index = 0)
    if ($userspn_meta_array) {
      switch ($userspn_type) {
        case 'user':
          $user_meta = get_user_meta($userspn_id, $userspn_input['id'], true);

          if (is_array($user_meta) && array_key_exists($userspn_array_index, $user_meta) && !empty($user_meta[$userspn_array_index])) {
            $userspn_value = $user_meta[$userspn_array_index];
          }else{
            if (array_key_exists('value', $userspn_input)) {
              $userspn_value = $userspn_input['value'];
            }else{
              $userspn_value = '';
            }
          }
          break;
        case 'post':
          $post_meta = get_post_meta($userspn_id, $userspn_input['id'], true);

          if (is_array($post_meta) && array_key_exists($userspn_array_index, $post_meta) && !empty($post_meta[$userspn_array_index])) {
            $userspn_value = $post_meta[$userspn_array_index];
          }else{
            if (array_key_exists('value', $userspn_input)) {
              $userspn_value = $userspn_input['value'];
            }else{
              $userspn_value = '';
            }
          }
          break;
        case 'option':
          $option = get_option($userspn_input['id']);

          if (is_array($option) && array_key_exists($userspn_array_index, $option) && !empty($option[$userspn_array_index])) {
            $userspn_value = $option[$userspn_array_index];
          }else{
            if (array_key_exists('value', $userspn_input)) {
              $userspn_value = $userspn_input['value'];
            }else{
              $userspn_value = '';
            }
          }
          break;
      }
    }else{
      switch ($userspn_type) {
        case 'user':
          $user_meta = get_user_meta($userspn_id, $userspn_input['id'], true);

          if ($user_meta != '') {
            $userspn_value = $user_meta;
          }else{
            if (array_key_exists('value', $userspn_input)) {
              $userspn_value = $userspn_input['value'];
            }else{
              $userspn_value = '';
            }
          }
          break;
        case 'post':
          $post_meta = get_post_meta($userspn_id, $userspn_input['id'], true);

          if ($post_meta != '') {
            $userspn_value = $post_meta;
          }else{
            if (array_key_exists('value', $userspn_input)) {
              $userspn_value = $userspn_input['value'];
            }else{
              $userspn_value = '';
            }
          }
          break;
        case 'option':
          $option = get_option($userspn_input['id']);

          if ($option != '') {
            $userspn_value = $option;
          }else{
            if (array_key_exists('value', $userspn_input)) {
              $userspn_value = $userspn_input['value'];
            }else{
              $userspn_value = '';
            }
          }
          break;
      }
    }

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
                      <label for="<?php echo $radio_option['id']; ?>">
                        <?php echo wp_kses_post(wp_specialchars_decode($radio_option['label'])); ?>
                        
                        <input type="<?php echo esc_attr($userspn_input['type']); ?>" id="<?php echo $radio_option['id']; ?>" name="<?php echo $userspn_input['id'] ?>" value="<?php echo $radio_option['value']; ?>" <?php echo $userspn_value == $radio_option['value'] ? 'checked="checked"' : ''; ?> <?php echo ((array_key_exists('required', $userspn_input) && $userspn_input['required'] == 'true') ? 'required' : ''); ?>>

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
                    <i class="material-icons-outlined userspn-input-star">star_outlined</i>
                  <?php endforeach ?>
                </div>

                <input type="number" <?php echo ((array_key_exists('required', $userspn_input) && $userspn_input['required'] == true) ? 'required' : ''); ?> <?php echo ((array_key_exists('disabled', $userspn_input) && $userspn_input['disabled'] == 'true') ? 'disabled' : ''); ?> id="<?php echo esc_attr($userspn_input['id']) . ((array_key_exists('multiple', $userspn_input) && $userspn_input['multiple']) ? '[]' : ''); ?>" name="<?php echo esc_attr($userspn_input['id']) . ((array_key_exists('multiple', $userspn_input) && $userspn_input['multiple']) ? '[]' : ''); ?>" class="userspn-input-hidden-stars <?php echo array_key_exists('class', $userspn_input) ? esc_attr($userspn_input['class']) : ''; ?>" min="1" max="<?php echo esc_attr($userspn_stars) ?>">
              </div>
            <?php
            break;
          case 'submit':
            ?>
              <div class="userspn-text-align-right">
                <input type="submit" value="<?php echo esc_attr($userspn_input['value']); ?>" name="<?php echo esc_attr($userspn_input['id']); ?>" id="<?php echo esc_attr($userspn_input['id']); ?>" name="<?php echo esc_attr($userspn_input['id']); ?>" class="userspn-btn" data-userspn-type="<?php echo esc_attr($userspn_type); ?>" data-userspn-subtype="<?php echo ((array_key_exists('subtype', $userspn_input)) ? esc_attr($userspn_input['subtype']) : ''); ?>" data-userspn-user-id="<?php echo esc_attr($userspn_id); ?>" data-userspn-post-id="<?php echo esc_attr(get_the_ID()); ?>"/><?php echo esc_html(userspn_Data::loader()); ?>
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
              <input type="hidden" id="<?php echo esc_attr($userspn_input['id']); ?>" name="<?php echo esc_attr($userspn_input['id']); ?>" value="<?php echo esc_attr(wp_create_nonce('userspn-nonce')); ?>" <?php echo (array_key_exists('multiple', $userspn_input) && $userspn_input['multiple'] == 'true' ? 'multiple' : ''); ?>>
            <?php
            break;
          case 'password':
            ?>
              <div class="userspn-password-checker">
                <div class="userspn-password-input userspn-position-relative">
                  <input id="<?php echo esc_attr($userspn_input['id']) . ((array_key_exists('multiple', $userspn_input) && $userspn_input['multiple'] == 'true') ? '[]' : ''); ?>" name="<?php echo esc_attr($userspn_input['id']) . ((array_key_exists('multiple', $userspn_input) && $userspn_input['multiple'] == 'true') ? '[]' : ''); ?>" <?php echo (array_key_exists('multiple', $userspn_input) && $userspn_input['multiple'] == 'true' ? 'multiple' : ''); ?> class="userspn-field userspn-password-strength <?php echo array_key_exists('class', $userspn_input) ? esc_attr($userspn_input['class']) : ''; ?>" type="<?php echo esc_attr($userspn_input['type']); ?>" <?php echo ((array_key_exists('required', $userspn_input) && $userspn_input['required'] == 'true') ? 'required' : ''); ?> <?php echo ((array_key_exists('disabled', $userspn_input) && $userspn_input['disabled'] == 'true') ? 'disabled' : ''); ?> value="<?php echo (!empty($userspn_input['button_text']) ? $userspn_input['button_text'] : esc_attr($userspn_value)); ?>" placeholder="<?php echo (array_key_exists('placeholder', $userspn_input) ? esc_attr($userspn_input['placeholder']) : ''); ?>" <?php echo wp_kses_post($userspn_parent_block); ?>/>

                  <a href="#" class="userspn-show-pass userspn-cursor-pointer userspn-display-none-soft">
                    <i class="material-icons-outlined userspn-font-size-20">visibility</i>
                  </a>
                </div>

                <div id="userspn-popover-pass" class="userspn-display-none-soft">
                  <div class="userspn-progress-bar-wrapper">
                    <div class="userspn-password-strength-bar"></div>
                  </div>

                  <h5 class="userspn-mt-20 userspn-mb-10"><?php esc_html_e('Password strength checker', 'userspn'); ?> <i class="material-icons-outlined userspn-cursor-pointer userspn-close-icon userspn-mt-30">close</i></h5>
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
          default:
            ?>
              <input id="<?php echo esc_attr($userspn_input['id']) . ((array_key_exists('multiple', $userspn_input) && $userspn_input['multiple']) ? '[]' : ''); ?>" name="<?php echo esc_attr($userspn_input['id']) . ((array_key_exists('multiple', $userspn_input) && $userspn_input['multiple']) ? '[]' : ''); ?>" <?php echo (array_key_exists('multiple', $userspn_input) && $userspn_input['multiple'] ? 'multiple' : ''); ?> class="userspn-field <?php echo array_key_exists('class', $userspn_input) ? esc_attr($userspn_input['class']) : ''; ?>" type="<?php echo esc_attr($userspn_input['type']); ?>" <?php echo ((array_key_exists('required', $userspn_input) && $userspn_input['required'] == true) ? 'required' : ''); ?> <?php echo (((array_key_exists('disabled', $userspn_input) && $userspn_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?> <?php echo (((array_key_exists('step', $userspn_input) && $userspn_input['step'] != '')) ? 'step="' . esc_attr($userspn_input['step']) . '"' : ''); ?> <?php echo (isset($userspn_input['max']) ? 'max=' . esc_attr($userspn_input['max']) : ''); ?> <?php echo (isset($userspn_input['min']) ? 'min=' . esc_attr($userspn_input['min']) : ''); ?> <?php echo (isset($userspn_input['pattern']) ? 'pattern=' . esc_attr($userspn_input['pattern']) : ''); ?> value="<?php echo (!empty($userspn_input['button_text']) ? esc_html($userspn_input['button_text']) : esc_html($userspn_value)); ?>" placeholder="<?php echo (array_key_exists('placeholder', $userspn_input) ? esc_html($userspn_input['placeholder']) : ''); ?>" <?php echo wp_kses_post($userspn_parent_block); ?>/>
            <?php
            break;
        }
        break;
      case 'select':
        ?>
          <select <?php echo ((array_key_exists('required', $userspn_input) && $userspn_input['required'] == true) ? 'required' : ''); ?> <?php echo (((array_key_exists('disabled', $userspn_input) && $userspn_input['disabled'] == 'true') || $disabled) ? 'disabled' : ''); ?> <?php echo (array_key_exists('multiple', $userspn_input) && $userspn_input['multiple'] ? 'multiple' : ''); ?> id="<?php echo esc_attr($userspn_input['id']) . ((array_key_exists('multiple', $userspn_input) && $userspn_input['multiple']) ? '[]' : ''); ?>" name="<?php echo esc_attr($userspn_input['id']) . ((array_key_exists('multiple', $userspn_input) && $userspn_input['multiple']) ? '[]' : ''); ?>" class="userspn-field <?php echo array_key_exists('class', $userspn_input) ? esc_attr($userspn_input['class']) : ''; ?>" placeholder="<?php echo (array_key_exists('placeholder', $userspn_input) ? esc_attr($userspn_input['placeholder']) : ''); ?>" data-placeholder="<?php echo (array_key_exists('placeholder', $userspn_input) ? esc_attr($userspn_input['placeholder']) : ''); ?>" <?php echo wp_kses_post($userspn_parent_block); ?>>

            <?php if (array_key_exists('multiple', $userspn_input) && $userspn_input['multiple']): ?>
              <?php 
                switch ($userspn_type) {
                  case 'user':
                    $userspn_selected_values = !empty(get_user_meta($userspn_id, $userspn_input['id'], true)) ? get_user_meta($userspn_id, $userspn_input['id'], true) : [];
                    break;
                  case 'post':
                    $userspn_selected_values = !empty(get_post_meta($userspn_id, $userspn_input['id'], true)) ? get_post_meta($userspn_id, $userspn_input['id'], true) : [];
                    break;
                  case 'option':
                    $userspn_selected_values = !empty(get_option($userspn_input['id'])) ? get_option($userspn_input['id']) : [];
                    break;
                }
              ?>
              
              <?php foreach ($userspn_input['options'] as $userspn_input_option_key => $userspn_input_option_value): ?>
                <option value="<?php echo esc_attr($userspn_input_option_key); ?>" <?php echo ((array_key_exists('all_selected', $userspn_input) && $userspn_input['all_selected'] == 'true') || (is_array($userspn_selected_values) && in_array($userspn_input_option_key, $userspn_selected_values)) ? 'selected' : ''); ?>><?php echo esc_html($userspn_input_option_value) ?></option>
              <?php endforeach ?>
            <?php else: ?>
              <option value="" <?php echo $userspn_value == '' ? 'selected' : '';?>><?php esc_html_e('Select an option', 'userspn'); ?></option>
              
              <?php foreach ($userspn_input['options'] as $userspn_input_option_key => $userspn_input_option_value): ?>
                <option value="<?php echo esc_attr($userspn_input_option_key); ?>" <?php echo ((array_key_exists('all_selected', $userspn_input) && $userspn_input['all_selected'] == 'true') || ($userspn_value != '' && $userspn_input_option_key == $userspn_value) ? 'selected' : ''); ?>><?php echo esc_html($userspn_input_option_value); ?></option>
              <?php endforeach ?>
            <?php endif ?>
          </select>
        <?php
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
                      <?php self::input_builder($html_multi_field, $userspn_type, $userspn_id, false, true, $length_index); ?>
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
                    <?php self::input_builder($html_multi_field, $userspn_type); ?>
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
    }
  }

  public static function input_wrapper_builder($input_array, $type, $userspn_id = 0, $disabled = 0, $userspn_format = 'half'){
    // userspn_Forms::input_wrapper_builder($input_array, $type, $userspn_id = 0, $disabled = 0, $userspn_format = 'half')
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
                  <label class="userspn-cursor-pointer userspn-toggle userspn-mb-20 userspn-color-main-0"><?php echo wp_kses_post($input_array['label']); ?></label>
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
        <div class="userspn-input-wrapper <?php echo esc_attr($input_array['id']); ?> <?php echo !empty($input_array['tabs']) ? 'userspn-input-tabbed' : ''; ?> userspn-input-field-<?php echo esc_attr($input_array['input']); ?> <?php echo (!empty($input_array['required']) && $input_array['required'] == true) ? 'userspn-input-field-required' : ''; ?> <?php echo ($disabled) ? 'userspn-input-field-disabled' : ''; ?>">
          <?php if (array_key_exists('label', $input_array) && !empty($input_array['label'])): ?>
            <div class="userspn-display-inline-table <?php echo (($userspn_format == 'half' && !(array_key_exists('type', $input_array) && $input_array['type'] == 'submit')) ? 'userspn-width-40-percent' : 'userspn-width-100-percent'); ?> userspn-tablet-display-block userspn-tablet-width-100-percent userspn-vertical-align-top">
              <div class="userspn-p-10 <?php echo (array_key_exists('parent', $input_array) && !empty($input_array['parent']) && $input_array['parent'] != 'this') ? 'userspn-pl-30' : ''; ?>">
                <label class="userspn-vertical-align-middle userspn-display-block <?php echo (array_key_exists('description', $input_array) && !empty($input_array['description'])) ? 'userspn-toggle' : ''; ?>" for="<?php echo esc_attr($input_array['id']); ?>"><?php echo esc_attr($input_array['label']); ?> <?php echo (array_key_exists('required', $input_array) && !empty($input_array['required']) && $input_array['required'] == true) ? '<span class="userspn-tooltip" title="' . esc_html(__('Required field', 'userspn')) . '">*</span>' : ''; ?><?php echo (array_key_exists('description', $input_array) && !empty($input_array['description'])) ? '<i class="material-icons-outlined userspn-cursor-pointer userspn-float-right">add</i>' : ''; ?></label>

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
              <div class="userspn-input-field"><?php self::input_builder($input_array, $type, $userspn_id, $disabled); ?></div>
            </div>
          </div>
        </div>
      <?php endif ?>
    <?php
  }

  public static function input_editor_builder($input_array) {
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

  public static function sanitizer($value, $node = '', $type = '') {
    switch (strtolower($node)) {
      case 'input':
        switch (strtolower($type)) {
          case 'text':
            return sanitize_text_field($value);
          case 'email':
            return sanitize_email($value);
          case 'url':
            return sanitize_url($value);
          case 'color':
            return sanitize_hex_color($value);
          default:
            return sanitize_text_field($value);
        }
      case 'select':
        switch ($type) {
          case 'select-multiple':
            foreach ($value as $key => $values) {
              $value[$key] = sanitize_key($values);
            }

            return $value;
          default:
            return sanitize_key($value);
        }
      case 'textarea':
        return wp_kses_post($value);
      case 'editor':
        return wp_kses_post($value);
      default:
        return sanitize_text_field($value);
    }
  }
}