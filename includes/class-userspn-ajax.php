<?php
/**
 * Load the plugin Ajax functions.
 *
 * Load the plugin Ajax functions to be executed in background.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    USERSPN
 * @subpackage USERSPN/includes
 * @author     Padres en la Nube
 */
class USERSPN_Ajax {
  /**
   * Load ajax functions.
   *
   * @since    1.0.0
   */
  public function userspn_ajax_server() {
    if (array_key_exists('userspn_ajax_type', $_POST)) {
      // Always require nonce verification
      if (!array_key_exists('userspn_ajax_nonce', $_POST)) {
        echo wp_json_encode([
          'error_key' => 'userspn_nonce_ajax_error_required',
          'error_content' => esc_html(__('Security check failed: Nonce is required.', 'userspn')),
        ]);

        exit;
      }

      if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['userspn_ajax_nonce'])), 'userspn-nonce')) {
        echo wp_json_encode([
          'error_key' => 'userspn_nonce_ajax_error_invalid',
          'error_content' => esc_html(__('Security check failed: Invalid nonce.', 'userspn')),
        ]);

        exit;
      }

      $userspn_ajax_type = USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_ajax_type']));

      $userspn_ajax_keys = !empty($_POST['userspn_ajax_keys']) ? array_map(function($key) {
        return array(
          'id' => sanitize_key($key['id']),
          'node' => sanitize_key($key['node']),
          'type' => sanitize_key($key['type']),
          'field_config' => !empty($key['field_config']) ? $key['field_config'] : []
        );
      }, wp_unslash($_POST['userspn_ajax_keys'])) : [];

      $userspn_basecpt_id = !empty($_POST['userspn_basecpt_id']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_basecpt_id'])) : 0;
      // Extra commonly used IDs
      $user_id = !empty($_POST['user_id']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['user_id'])) : 0;
      $current_user_id = !empty($_POST['current_user_id']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['current_user_id'])) : 0;
      $post_id = !empty($_POST['post_id']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['post_id'])) : 0;
      $file_id = !empty($_POST['file_id']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['file_id'])) : 0;
      
      $userspn_key_value = [];

      if (!empty($userspn_ajax_keys)) {
        foreach ($userspn_ajax_keys as $userspn_key) {
          if (strpos((string)$userspn_key['id'], '[]') !== false) {
            $userspn_clear_key = str_replace('[]', '', $userspn_key['id']);
            ${$userspn_clear_key} = $userspn_key_value[$userspn_clear_key] = [];

            if (!empty($_POST[$userspn_clear_key])) {
              $unslashed_array = wp_unslash($_POST[$userspn_clear_key]);
              $sanitized_array = array_map(function($value) use ($userspn_key) {
                return USERSPN_Forms::userspn_sanitizer(
                  $value,
                  $userspn_key['node'],
                  $userspn_key['type'],
                  $userspn_key['field_config']
                );
              }, $unslashed_array);
              
              // filter empty entries
              $sanitized_array = array_filter($sanitized_array, function($v) { return $v !== '' && $v !== null; });
              // generic normalization: ints if all numeric, unique, reindex
              $all_numeric = !empty($sanitized_array) && count(array_filter($sanitized_array, 'is_numeric')) === count($sanitized_array);
              if ($all_numeric) {
                $sanitized_array = array_map('intval', $sanitized_array);
              }
              $sanitized_array = array_values(array_unique($sanitized_array));
              ${$userspn_clear_key} = $userspn_key_value[$userspn_clear_key] = $sanitized_array;
            } else {
              // explicit empty array for multiple fields
              ${$userspn_clear_key} = [];
              $userspn_key_value[$userspn_clear_key] = [];
            }
          } else {
            $sanitized_key = sanitize_key($userspn_key['id']);
            $userspn_key_id = !empty($_POST[$sanitized_key]) ? 
              USERSPN_Forms::userspn_sanitizer(
                wp_unslash($_POST[$sanitized_key]), 
                $userspn_key['node'], 
                $userspn_key['type'],
                $userspn_key['field_config']
              ) : '';
            ${$userspn_key['id']} = $userspn_key_value[$userspn_key['id']] = $userspn_key_id;
          }
        }
      }

      switch ($userspn_ajax_type) {
        case 'userspn_options_save':
          if (!empty($userspn_key_value)) {
            foreach ($userspn_key_value as $key => $value) {
              if (!in_array($key, ['action', 'userspn_ajax_type'])) {
                update_option($key, $value);
              }
            }

            update_option('userspn_options_changed', true);
            echo wp_json_encode(['error_key' => '']);
            exit;
          } else {
            echo wp_json_encode(['error_key' => 'userspn_options_save_error']);
            exit;
          }
          break;
        case 'userspn_popup_manager_edit':
          if (!empty($user_id)) {
            global $wp_roles;
            $profile_array = apply_filters('userspn_register_fields', []);
            $user = new WP_User($user_id);
            $user_info = get_userdata($user_id);
            ?>
              <h5 class="userspn-text-align-center userspn-mb-30"><?php esc_html_e('User ID#', 'userspn'); ?><?php echo esc_html($user_id); ?> <?php echo esc_html($user_info->first_name); ?> <?php echo esc_html($user_info->last_name); ?> <?php echo esc_html($user_info->user_email); ?></h5>

              <form id="userspn-manage-form">
                <div class="userspn-input-wrapper">
                  <div class="userspn-display-inline-table userspn-width-100-percent userspn-tablet-display-block userspn-tablet-width-100-percent userspn-vertical-align-top">
                    <div class="userspn-p-10">
                      <label class="userspn-font-size-16 userspn-vertical-align-middle userspn-display-block"><?php esc_html_e('Roles', 'userspn'); ?></label>
                    </div>
                  </div>
                  <div class="userspn-display-inline-table userspn-width-100-percent userspn-tablet-display-block userspn-tablet-width-100-percent userspn-vertical-align-top">
                    <div class="userspn-p-10">
                      <div class="userspn-input-field">
                        <select multiple id="userspn_roles" name="userspn_roles" class="userspn-select userspn-width-100-percent">
                          <?php foreach ($wp_roles->roles as $role_key => $role_value): ?>
                            <option value="<?php echo esc_attr($role_key); ?>" <?php echo (user_can($user_id, $role_key)) ? 'selected=""' : ''; ?>><?php echo esc_html($role_value['name']); ?></option>
                          <?php endforeach ?>
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
                    
                <?php if (!empty($profile_array)): ?>
                  <?php foreach ($profile_array as $profile_field): ?>
                    <?php USERSPN_Forms::userspn_input_wrapper_builder($profile_field, 'user', esc_html($user_id), 0, 'full'); ?>
                  <?php endforeach ?>
                <?php endif ?>

                <div class="userspn-text-align-right">
                  <input type="submit" data-users-user-id="<?php echo esc_attr($user_id); ?>" value="<?php esc_html_e('Update user', 'userspn'); ?>" class="userspn-btn userspn-manage-btn"/><?php echo esc_html(USERSPN_Data::userspn_loader()); ?>
                </div>
              </form>
            <?php
            exit;
          } else {
            echo 'userspn_popup_manager_edit_error';
            exit;
          }
          break;
        case 'userspn_manager_edit':
          if (!empty($user_id) && user_can($current_user_id, 'administrator')) {
            if (!empty($userspn_key_value)) {
              foreach ($userspn_key_value as $key => $value) {
                if ($key == 'userspn_roles') {
                  global $wp_roles;
                  $user = new WP_User($user_id);
                  foreach (array_keys($wp_roles->roles) as $role_key) {
                    if (in_array($role_key, $value)) {
                      $user->add_role($role_key);
                    } else {
                      $user->remove_role($role_key);
                    }
                  }
                } else {
                  update_user_meta($user_id, $key, $value);
                }
              }
            }

            do_action('userspn_manager_edit', $user_id, $userspn_key_value);
            echo 'userspn_manager_edit_success';
            exit;
          } else {
            echo 'userspn_manager_edit_error';
            exit;
          }
          break;
        case 'userspn_popup_manager_remove':
          if (!empty($user_id)) {
            ?>
              <h3 class="userspn-text-align-center"><?php esc_html_e('Remove user', 'userspn'); ?></h3>
          
              <p class="userspn-text-align-center userspn-mb-10"><?php esc_html_e('User ID#', 'userspn'); ?><?php echo esc_html($user_id); ?> (<?php echo esc_html(get_user_by('id', $user_id)->first_name); ?> <?php echo esc_html(get_user_by('id', $user_id)->last_name); ?> <?php echo esc_html(get_user_by('id', $user_id)->user_email); ?>) <?php esc_html_e('will be removed completely from the system. This action cannot be undone. Are you sure?', 'userspn'); ?></p>
              
              <div class="userspn-display-table userspn-width-100-percent">
                <div class="userspn-display-inline-table userspn-width-50-percent userspn-tablet-display-block userspn-tablet-width-100-percent userspn-text-align-center">
                  <a href="#" class="userspn-popup-close userspn-color-main-0 userspn-text-decoration-none"><?php esc_html_e('Cancel', 'userspn'); ?></a>
                </div>

                <div class="userspn-display-inline-table userspn-width-50-percent userspn-tablet-display-block userspn-tablet-width-100-percent userspn-text-align-center">
                  <a href="#" class="userspn-btn userspn-remove" data-userspn-user-id="<?php echo esc_attr($user_id); ?>"><?php esc_html_e('Remove user', 'userspn'); ?></a>
                </div>
              </div>
            <?php
            exit;
          } else {
            echo 'userspn_popup_manager_remove_error';
            exit;
          }
          break;
        case 'userspn_manager_remove':
          if (!empty($user_id) && user_can($current_user_id, 'administrator')) {
            wp_delete_user($user_id);
            echo 'userspn_manager_remove_success';
            exit;
          } else {
            echo 'userspn_manager_remove_error';
            exit;
          }
          break;
        case 'userspn_profile_progress':
          if (!empty($user_id)) {
            $user = get_user_by('id', $user_id);
            if (!$user) {
              wp_send_json_error();
              exit;
            }

            $required_fields = [];
            if (get_option('userspn_user_name') == 'on') {
              $required_fields[] = [
                'id' => 'first_name',
                'name' => __('First Name', 'userspn'),
                'required' => get_option('userspn_user_name_compulsory') == 'on'
              ];
              $required_fields[] = [
                'id' => 'last_name',
                'name' => __('Last Name', 'userspn'),
                'required' => get_option('userspn_user_surname_compulsory') == 'on'
              ];
            }

            $custom_fields = apply_filters('userspn_register_fields', []);
            foreach ($custom_fields as $field) {
              if (!empty($field['required'])) {
                $required_fields[] = [
                  'id' => $field['id'],
                  'name' => $field['label'],
                  'required' => true
                ];
              }
            }

            $completed_fields = 0;
            $fields_status = [];
            foreach ($required_fields as $field) {
              $value = get_user_meta($user_id, $field['id'], true);
              $is_completed = !empty($value);
              if ($is_completed) {
                $completed_fields++;
              }
              $fields_status[] = [
                'name' => $field['name'],
                'completed' => $is_completed
              ];
            }

            $total_fields = count($required_fields);
            $percentage = $total_fields > 0 ? round(($completed_fields / $total_fields) * 100) : 100;

            wp_send_json_success([
              'percentage' => $percentage,
              'fields' => $fields_status
            ]);
            exit;
          }
          wp_send_json_error();
          exit;
          break;
        case 'userspn_input_editor_builder_add':
          $userspn_meta = !empty($_POST['userspn_meta']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_meta'])) : [];

          $userspn_array = [
            'id' => '',
            'class' => '',
            'input' => 'input',
            'type' => 'text',
            'required' => false,
            'label' => '',
            'userspn_meta' => $userspn_meta,
          ];

          USERSPN_Forms::userspn_input_editor_builder($userspn_array);
          exit;
          break;
        case 'userspn_input_editor_builder_save':
          $userspn_input_name = !empty($_POST['userspn_input_name']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_input_name'])) : '';
          $userspn_input_current_id = !empty($_POST['userspn_input_current_id']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_input_current_id'])) : 0;
          $userspn_input_type = !empty($_POST['userspn_input_type']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_input_type'])) : '';
          $userspn_form_type = !empty($_POST['userspn_form_type']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_form_type'])) : '';
          $userspn_input_id = !empty($_POST['userspn_input_id']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_input_id'])) : 0;
          $userspn_input_class = !empty($_POST['userspn_input_class']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_input_class'])) : '';
          $userspn_input_subtype = !empty($_POST['userspn_input_subtype']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_input_subtype'])) : '';
          $userspn_input_subtype = !empty($_POST['userspn_input_subtype']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_input_subtype'])) : '';
          $userspn_input_required = !empty($_POST['userspn_input_required']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_input_required'])) : '';
          $userspn_input_min = !empty($_POST['userspn_input_min']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_input_min'])) : 0;
          $userspn_input_max = !empty($_POST['userspn_input_max']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_input_max'])) : 0;
          $userspn_input_label_min = !empty($_POST['userspn_input_label_min']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_input_label_min'])) : '';
          $userspn_input_label_max = !empty($_POST['userspn_input_label_max']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_input_label_max'])) : '';
          $userspn_select_options = !empty($_POST['userspn_select_options']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_select_options'])) : '';
          $userspn_select_subtype = !empty($_POST['userspn_select_subtype']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_select_subtype'])) : '';
          $userspn_textarea_subtype = !empty($_POST['userspn_textarea_subtype']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_textarea_subtype'])) : '';
          $userspn_meta = !empty($_POST['userspn_meta']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_meta'])) : '';

          $userspn_input_required = ($userspn_input_required == 'true') ? true : false;
          $userspn_input_multiple = ($userspn_form_type == 'table') ? true : false;
          $userspn_select_multiple = ($userspn_select_subtype == 'true') ? true : false;

          if (!empty($userspn_input_name) && !empty($userspn_input_type) && !empty($userspn_form_type) && !empty($userspn_meta)) {
            $userspn_input_id = !empty($userspn_input_current_id) ? $userspn_input_current_id : 'userspn_user_' . str_replace('-', '_', substr(sanitize_title($userspn_input_name), 0, 23));

            switch ($userspn_input_type) {
              case 'input':
                $userspn_meta_value = [
                  'id' => $userspn_input_id,
                  'class' => 'userspn-input userspn-width-100-percent ' . $userspn_input_class,
                  'input' => 'input',
                  'type' => $userspn_input_subtype,
                  'required' => $userspn_input_required,
                  'multiple' => $userspn_input_multiple,
                  'label' => $userspn_input_name,
                  'form_type' => $userspn_form_type,
                  'userspn_meta' => $userspn_meta,
                  'userspn_min' => $userspn_input_min,
                  'userspn_max' => $userspn_input_max,
                  'userspn_label_min' => $userspn_input_label_min,
                  'userspn_label_max' => $userspn_input_label_max,
                ];
                break;
              case 'select':
                $options = [];
                if (!empty($userspn_select_options)) {
                  foreach (explode(PHP_EOL, $userspn_select_options) as $option) {
                    $options[sanitize_title($option)] = $option;
                  }
                }

                $userspn_meta_value = [
                  'id' => $userspn_input_id,
                  'class' => 'userspn-select userspn-width-100-percent ' . $userspn_input_class,
                  'input' => 'select',
                  'options' => $options,
                  'required' => $userspn_input_required,
                  'multiple' => $userspn_select_multiple,
                  'label' => $userspn_input_name,
                  'form_type' => $userspn_form_type,
                  'userspn_meta' => $userspn_meta,
                ];
                break;
              case 'textarea':
                $userspn_meta_value = [
                  'id' => $userspn_input_id,
                  'class' => 'userspn-input userspn-width-100-percent ' . $userspn_input_class,
                  'input' => 'textarea',
                  'multiple' => $userspn_input_multiple,
                  'required' => $userspn_input_required,
                  'label' => $userspn_input_name,
                  'form_type' => $userspn_form_type,
                  'userspn_meta' => $userspn_meta,
                ];
                break;
            }

            switch ($userspn_form_type) {
              case 'option':
                if (empty(get_option('userspn_user_register_fields'))) {
                  update_option('userspn_user_register_fields', [$userspn_input_id => $userspn_meta_value]);
                } else {
                  $userspn_option_new = get_option('userspn_user_register_fields', true);
                  $userspn_option_new[$userspn_input_id] = $userspn_meta_value;
                  update_option('userspn_user_register_fields', $userspn_option_new);
                }
                break;
              case 'forum':
                if (empty(get_post_meta($post_id, $userspn_meta, true))) {
                  update_post_meta($post_id, $userspn_meta, [$userspn_input_id => $userspn_meta_value]);
                } else {
                  $userspn_post_meta_new = get_post_meta($post_id, $userspn_meta, true);
                  $userspn_post_meta_new[$userspn_input_id] = $userspn_meta_value;
                  update_post_meta($post_id, $userspn_meta, $userspn_post_meta_new);
                }
                break;
              case 'table':
                if (empty(get_post_meta($post_id, $userspn_meta, true))) {
                  update_post_meta($post_id, $userspn_meta, [$userspn_input_id => $userspn_meta_value]);
                } else {
                  $userspn_post_meta_new = get_post_meta($post_id, $userspn_meta, true);
                  $userspn_post_meta_new[$userspn_input_id] = $userspn_meta_value;
                  update_post_meta($post_id, $userspn_meta, $userspn_post_meta_new);
                }
                break;
              case 'questionnaire':
                if (empty(get_post_meta($post_id, $userspn_meta, true))) {
                  update_post_meta($post_id, $userspn_meta, [$userspn_input_id => $userspn_meta_value]);
                } else {
                  $userspn_post_meta_new = get_post_meta($post_id, $userspn_meta, true);
                  $userspn_post_meta_new[$userspn_input_id] = $userspn_meta_value;
                  update_post_meta($post_id, $userspn_meta, $userspn_post_meta_new);
                }
                break;
            }

            ob_start();
            ?>
              <div class="userspn-user-register-field userspn-width-100-percent userspn-mb-30 <?php echo esc_attr($userspn_input_id); ?>" id="<?php echo esc_attr($userspn_input_id); ?>">
                <label class="userspn-display-block" for="<?php echo esc_attr($userspn_input_id); ?>"><?php echo esc_html($userspn_input_name); ?></label>
                <?php USERSPN_Forms::userspn_input_builder($userspn_meta_value, 'user'); ?>
              </div>
            <?php
            $userspn_return_string = ob_get_contents();
            ob_end_clean();
            echo wp_json_encode(['error_key' => 0, 'html' => wp_kses($userspn_return_string, USERSPN_KSES), 'field_id' => $userspn_input_id, 'label' => $userspn_input_name, 'type' => $userspn_form_type, 'userspn_meta' => $userspn_meta]);
            exit;
          } else {
            echo wp_json_encode(['error_key' => 'empty']);
            exit;
          }
          break;
        case 'userspn_input_editor_builder_remove':
          $userspn_meta = !empty($_POST['userspn_meta']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_meta'])) : '';
          $userspn_input_id = !empty($_POST['userspn_input_id']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_input_id'])) : 0;
          $userspn_form_type = !empty($_POST['userspn_form_type']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_form_type'])) : '';

          if (!empty($userspn_input_id) && !empty($userspn_form_type)) {
            if ($userspn_form_type == 'option') {
              $userspn_user_register_fields = get_option('userspn_user_register_fields');
              if (!empty($userspn_user_register_fields[$userspn_input_id])) {
                unset($userspn_user_register_fields[$userspn_input_id]);
                update_option('userspn_user_register_fields', $userspn_user_register_fields);
              }
            } else {
              $userspn_user_register_fields = get_post_meta($post_id, $userspn_meta, true);
              if (!empty($userspn_user_register_fields[$userspn_input_id])) {
                unset($userspn_user_register_fields[$userspn_input_id]);
                update_post_meta($post_id, $userspn_meta, $userspn_user_register_fields);
              }
            }

            echo 'userspn_input_editor_builder_remove_success';
            exit;
          } else {
            echo 'userspn_input_editor_builder_remove_error';
            exit;
          }
          break;
        case 'userspn_notifications':
          if (!empty($user_id)) {
            foreach ($userspn_key_value as $key => $value) {
              if (!in_array($key, ['userspn_ajax_type', 'user_id', 'action', 'userspn-notifications-btn'])) {
                update_user_meta($user_id, $key, $value);
              }
            }
            echo 'userspn_notifications_success';
            exit;
          } else {
            echo 'userspn_notifications_error';
            exit;
          }
          break;
        case 'userspn_file_uploaded_share':
          if (!empty($file_id) && !empty($post_id)) {
            $userspn_meta_value = $file_id;
            if (empty(get_post_meta($post_id, 'userspn_file_uploaded_shared', true))) {
              update_post_meta($post_id, 'userspn_file_uploaded_shared', [$userspn_meta_value]);
            } else {
              $userspn_post_meta_new = get_post_meta($post_id, 'userspn_file_uploaded_shared', true);
              if (in_array($userspn_meta_value, $userspn_post_meta_new)) {
                unset($userspn_post_meta_new[array_search($file_id, $userspn_post_meta_new)]);
              } else {
                $userspn_post_meta_new[] = $userspn_meta_value;
              }
              update_post_meta($post_id, 'userspn_file_uploaded_shared', $userspn_post_meta_new);
            }
            echo 'userspn_file_uploaded_share_success';
            exit;
          } else {
            echo 'userspn_file_uploaded_share_error';
            exit;
          }
          break;
        case 'userspn_user_remove':
          $password = !empty($_POST['password']) ? sanitize_text_field(wp_unslash($_POST['password'])) : '';
          if (!empty($user_id) && !empty($password)) {
            $plugin_user = new USERSPN_Functions_User();
            if ($plugin_user->userspn_check_password($user_id, $password)) {
              require_once(ABSPATH . 'wp-admin/includes/user.php');
              if (!user_can($user_id, 'administrator') && $user_id == get_current_user_id()) {
                wp_delete_user($user_id);
                echo 'userspn_user_remove_success';
                exit;
              } else {
                echo 'userspn_user_remove_error';
                exit;
              }
            } else {
              echo 'userspn_user_remove_error_invalid_password';
              exit;
            }
          } else {
            echo 'userspn_user_remove_error_empty';
            exit;
          }
          break;
        case 'userspn_csv_add_contacts':
          if (!empty($user_id)) {
            $userspn_uploaded_file = get_posts(['fields' => 'ids', 'numberposts' => 1, 'post_type' => 'attachment', 'post_status' => ['any'], 'author' => $user_id, 'orderby' => 'ID', 'order' => 'DESC']);
            $plugin_csv = new USERSPN_CSV();
            $plugin_csv->userspn_csv_template_creator(esc_url(wp_get_attachment_url($userspn_uploaded_file[0])));
            echo 'userspn_csv_add_contacts_success';
            exit;
          } else {
            echo 'userspn_csv_add_contacts_error';
            exit;
          }
          break;
        case 'userspn_file_private_remove':
          if (!empty($user_id) && !empty($file_id)) {
            if (get_post($file_id)->post_author == $user_id || current_user_can('administrator')) {
              wp_delete_post($file_id, true);
              echo 'userspn_file_private_remove_success';
              exit;
            } else {
              echo 'userspn_file_private_remove_error';
              exit;
            }
          } else {
            echo 'userspn_file_private_remove_error';
            exit;
          }
          break;
        case 'userspn_user_files':
          if ($_FILES && !empty($post_id)) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            $plugin_attachment = new USERSPN_Functions_Attachment();

            $userspn_uploaded_file = 'userspn_uploaded_file';
            $attach_id = media_handle_upload($userspn_uploaded_file, $post_id);
            update_post_meta($attach_id, 'userspn_user_files', strtotime('now'));
            echo wp_json_encode(['error_key' => '', 'response' => esc_html(__('Your file has been uploaded succesfully.', 'userspn')), 'html' => $plugin_attachment->userspn_get_private_file_uploaded($attach_id)]);
            exit;
          } else {
            echo wp_json_encode(['error_key' => 'empty', 'response' => esc_html(__('Please, select a valid file.', 'userspn'))]);
            exit;
          }
          break;
        case 'userspn_profile_image':
          if ($_FILES) {
            require_once(ABSPATH . 'wp-admin' . '/includes/image.php');
            require_once(ABSPATH . 'wp-admin' . '/includes/file.php');
            require_once(ABSPATH . 'wp-admin' . '/includes/media.php');

            $file_handler = 'userspn_uploaded_file';
            $userspn_related_user_id = !empty($_POST['userspn_related_user_id']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_related_user_id'])) : '';
            $userspn_file_id = media_handle_upload($file_handler, 0);

            if (is_wp_error($userspn_file_id)) {
              echo 'userspn_profile_image_error';
              exit;
            } else {
              update_post_meta($userspn_file_id, 'userspn_related_user_id', $userspn_related_user_id);
              update_user_meta($userspn_related_user_id, 'userspn_user_image', $userspn_file_id);
              ?>
                <div class="userspn-display-table userspn-width-100-percent">
                  <div class="userspn-display-table-cell userspn-width-80-percent userspn-vertical-align-middle userspn-text-align-left">
                    <a href="<?php echo esc_url(wp_get_attachment_url($userspn_file_id)); ?>" download><h6 class="userspn-text-decoration-underline"><?php echo esc_html(get_the_title($userspn_file_id)); ?></h6></a>
                  </div>
                  <div class="userspn-display-table-cell userspn-width-20-percent userspn-text-align-right userspn-vertical-align-middle"></div>
                </div>
              <?php
              exit;
            }
          } else {
            echo 'userspn_profile_image_error';
            exit;
          }
          break;
        case 'userspn_remove_avatar':
          $userspn_user_id = !empty($_POST['userspn_user_id']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_user_id'])) : '';
          if (!empty($userspn_user_id)) {
            $current_avatar_id = get_user_meta($userspn_user_id, 'userspn_user_image', true);
            if (!empty($current_avatar_id)) {
              wp_delete_attachment($current_avatar_id, true);
              delete_user_meta($userspn_user_id, 'userspn_user_image');
              echo wp_json_encode(['success' => true, 'message' => 'Avatar removed successfully']);
              exit;
            } else {
              echo wp_json_encode(['success' => false, 'message' => 'No avatar to remove']);
              exit;
            }
          } else {
            echo wp_json_encode(['success' => false, 'message' => 'Invalid user ID']);
            exit;
          }
          break;
        case 'userspn_get_avatar_html':
          $userspn_user_id = !empty($_POST['userspn_user_id']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_user_id'])) : '';
          if (!empty($userspn_user_id)) {
            $plugin_user = new USERSPN_Functions_User();
            $avatar_html = do_shortcode('[userspn-get-avatar user_id="' . $userspn_user_id . '" size="50"]');
            echo wp_json_encode(['html' => $avatar_html]);
            exit;
          } else {
            echo wp_json_encode(['html' => '']);
            exit;
          }
          break;
        case 'userspn_csv_template_upload':
          if ($_FILES) {
            $userspn_csv_template_upload = !empty($_POST['userspn_csv_template_upload']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_csv_template_upload'])) : 0;
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            $file_handler = 'userspn_csv_template_upload';
            $post_id = 0;
            $userspn_file_id = media_handle_upload($file_handler, $post_id);
            if (empty($userspn_file_id)) {
              echo 'userspn_csv_template_upload_error_empty';
              exit;
            } elseif (is_wp_error($userspn_file_id)) {
              echo 'userspn_csv_template_upload_error';
              exit;
            } else {
              $plugin_user = new USERSPN_Functions_User();
              echo wp_kses($plugin_user->userspn_csv_template_reader(wp_get_attachment_url($userspn_file_id)), USERSPN_KSES);
              exit;
            }
          } else {
            echo 'userspn_csv_template_upload_error';
            exit;
          }
          break;
        case 'userspn_analyze_bots':
          if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
          }
          $limit = !empty($_POST['limit']) ? intval($_POST['limit']) : 100;
          $results = USERSPN_Security::analyze_existing_users_for_bots($limit);
          wp_send_json_success($results);
          break;
        case 'userspn_get_bot_analysis':
          if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
          }
          $results = USERSPN_Security::get_bot_analysis_results();
          wp_send_json_success($results);
          break;
        case 'userspn_mark_user_as_bot':
          if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
          }
          $user_id = !empty($_POST['user_id']) ? intval($_POST['user_id']) : 0;
          if ($user_id) {
            USERSPN_Security::mark_user_as_bot($user_id);
            wp_send_json_success('User marked as bot');
          } else {
            wp_send_json_error('Invalid user ID');
          }
          break;
        case 'userspn_mark_user_as_human':
          if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
          }
          $user_id = !empty($_POST['user_id']) ? intval($_POST['user_id']) : 0;
          if ($user_id) {
            USERSPN_Security::mark_user_as_human($user_id);
            wp_send_json_success('User marked as human');
          } else {
            wp_send_json_error('Invalid user ID');
          }
          break;
        case 'userspn_delete_confirmed_bots':
          if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
          }
          $deleted_count = USERSPN_Security::delete_confirmed_bots();
          wp_send_json_success(['deleted_count' => $deleted_count]);
          break;
        case 'userspn_basecpt_view':
          if (!empty($userspn_basecpt_id)) {
            try {
              $plugin_post_type_userspn = new USERSPN_Post_Type_BaseCPT();
              $basecpt_html = $plugin_post_type_userspn->userspn_basecpt_view(intval($userspn_basecpt_id));
              
              echo wp_json_encode([
                'error_key' => '', 
                'html' => $basecpt_html, 
              ]);
            } catch (Exception $e) {
              error_log('USERSPN Error in userspn_basecpt_view: ' . $e->getMessage());
              echo wp_json_encode([
                'error_key' => 'userspn_basecpt_view_error', 
                'error_content' => esc_html(__('An error occurred while showing the BaseCPT.', 'userspn')), 
              ]);
            }

            exit;
          }else{
            echo wp_json_encode([
              'error_key' => 'userspn_basecpt_view_error', 
              'error_content' => esc_html(__('BaseCPT ID is required.', 'userspn')), 
            ]);

            exit;
          }
          break;
        case 'userspn_basecpt_edit':
          // Check if the BaseCPT exists
          $userspn_basecpt = get_post($userspn_basecpt_id);
          

          if (!empty($userspn_basecpt_id)) {
            $plugin_post_type_userspn = new USERSPN_Post_Type_BaseCPT();
            echo wp_json_encode([
              'error_key' => '', 
              'html' => $plugin_post_type_userspn->userspn_basecpt_edit($userspn_basecpt_id), 
            ]);

            exit;
          }else{
            echo wp_json_encode([
              'error_key' => 'userspn_basecpt_edit_error', 
              'error_content' => esc_html(__('An error occurred while showing the BaseCPT.', 'userspn')), 
            ]);

            exit;
          }
          break;
        case 'userspn_basecpt_new':
          if (!is_user_logged_in()) {
            echo wp_json_encode([
              'error_key' => 'not_logged_in',
              'error_content' => esc_html(__('You must be logged in to create a new asset.', 'userspn')),
            ]);
            exit;
          }

          $plugin_post_type_userspn = new USERSPN_Post_Type_BaseCPT();

          echo wp_json_encode([
            'error_key' => '', 
            'html' => $plugin_post_type_userspn->userspn_basecpt_new($userspn_basecpt_id), 
          ]);

          exit;
          break;
        case 'userspn_basecpt_check':
          if (!empty($userspn_basecpt_id)) {
            $plugin_post_type_userspn = new USERSPN_Post_Type_BaseCPT();
            echo wp_json_encode([
              'error_key' => '', 
              'html' => $plugin_post_type_userspn->userspn_basecpt_check($userspn_basecpt_id), 
            ]);

            exit;
          }else{
            echo wp_json_encode([
              'error_key' => 'userspn_basecpt_check_error', 
              'error_content' => esc_html(__('An error occurred while checking the BaseCPT.', 'userspn')), 
              ]);

            exit;
          }
          break;
        case 'userspn_basecpt_duplicate':
          if (!empty($userspn_basecpt_id)) {
            $plugin_post_type_post = new USERSPN_Functions_Post();
            $plugin_post_type_post->userspn_duplicate_post($userspn_basecpt_id, 'publish');
            
            $plugin_post_type_userspn = new USERSPN_Post_Type_BaseCPT();
            echo wp_json_encode([
              'error_key' => '', 
              'html' => $plugin_post_type_userspn->userspn_basecpt_list(), 
            ]);

            exit;
          }else{
            echo wp_json_encode([
              'error_key' => 'userspn_basecpt_duplicate_error', 
              'error_content' => esc_html(__('An error occurred while duplicating the BaseCPT.', 'userspn')), 
            ]);

            exit;
          }
          break;
        case 'userspn_basecpt_remove':
          if (!empty($userspn_basecpt_id)) {
            wp_delete_post($userspn_basecpt_id, true);

            $plugin_post_type_userspn = new USERSPN_Post_Type_BaseCPT();
            echo wp_json_encode([
              'error_key' => '', 
              'html' => $plugin_post_type_userspn->userspn_basecpt_list(), 
            ]);

            exit;
          }else{
            echo wp_json_encode([
              'error_key' => 'userspn_basecpt_remove_error', 
              'error_content' => esc_html(__('An error occurred while removing the BaseCPT.', 'userspn')), 
            ]);

            exit;
          }
          break;
        case 'userspn_basecpt_share':
          $plugin_post_type_userspn = new USERSPN_Post_Type_BaseCPT();
          echo wp_json_encode([
            'error_key' => '', 
            'html' => $plugin_post_type_userspn->userspn_basecpt_share(), 
          ]);

          exit;
          break;
        case 'userspn_calendar_view':
          $calendar_view = !empty($_POST['calendar_view']) ? sanitize_text_field(wp_unslash($_POST['calendar_view'])) : 'month';
          $calendar_year = !empty($_POST['calendar_year']) ? intval($_POST['calendar_year']) : date('Y');
          $calendar_month = !empty($_POST['calendar_month']) ? intval($_POST['calendar_month']) : date('m');
          $calendar_day = !empty($_POST['calendar_day']) ? intval($_POST['calendar_day']) : date('d');
          
          $plugin_calendar = new USERSPN_Calendar();
          $calendar_html = $plugin_calendar->userspn_calendar_render_view_content($calendar_view, $calendar_year, $calendar_month, $calendar_day);
          
          echo wp_json_encode([
            'error_key' => '', 
            'html' => $calendar_html,
            'view' => $calendar_view,
            'year' => $calendar_year,
            'month' => $calendar_month,
            'day' => $calendar_day
          ]);

          exit;
          break;
      }

      echo wp_json_encode([
        'error_key' => 'userspn_save_error', 
      ]);

      exit;
    }
  }
}