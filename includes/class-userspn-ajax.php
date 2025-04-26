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
 * @author     Padres en la Nube <info@padresenlanube.com>
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
      if (!array_key_exists('ajax_nonce', $_POST)) {
        echo wp_json_encode([
          'error_key' => 'userspn_nonce_error',
          'error_content' => esc_html(__('Security check failed: Nonce is required.', 'userspn'))
        ]);

        exit();
      }

      if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ajax_nonce'])), 'userspn-nonce')) {
        echo wp_json_encode([
          'error_key' => 'userspn_nonce_error',
          'error_content' => esc_html(__('Security check failed: Invalid nonce.', 'userspn'))
        ]);
        
        exit();
      }

  		$userspn_ajax_type = USERSPN_Forms::sanitizer(wp_unslash($_POST['userspn_ajax_type']));
      $ajax_keys = !empty($_POST['ajax_keys']) ? wp_unslash($_POST['ajax_keys']) : [];
      $key_value = [];
      $user_id = !empty($_POST['user_id']) ? wp_unslash($_POST['user_id']) : 0;
      $current_user_id = !empty($_POST['current_user_id']) ? USERSPN_Forms::sanitizer(wp_unslash($_POST['current_user_id'])) : 0;
      $post_id = !empty($_POST['post_id']) ? USERSPN_Forms::sanitizer(wp_unslash($_POST['post_id'])) : 0;
      $file_id = !empty($_POST['file_id']) ? USERSPN_Forms::sanitizer(wp_unslash($_POST['file_id'])) : 0;

      if (!empty($ajax_keys)) {
        foreach ($ajax_keys as $key) {
          if (strpos($key['id'], '[]') !== false) {
            $clear_key = str_replace('[]', '', $key['id']);
            ${$clear_key} = $key_value[$clear_key] = [];

            if (!empty($_POST[$clear_key])) {
              foreach (wp_unslash($_POST[$clear_key]) as $multi_key => $multi_value) {
                $final_value = !empty($_POST[$clear_key][$multi_key]) ? USERSPN_Forms::sanitizer(wp_unslash($_POST[$clear_key][$multi_key]), $key['node'], $key['type']) : '';
                ${$clear_key}[$multi_key] = $key_value[$clear_key][$multi_key] = $final_value;
              }
            }else{
              ${$clear_key} = '';
              $key_value[$clear_key][$multi_key] = '';
            }
          }else{
            $key_id = !empty($_POST[$key['id']]) ? USERSPN_Forms::sanitizer(wp_unslash($_POST[$key['id']]), $key['node'], $key['type']) : '';
            ${$key['id']} = $key_value[$key['id']] = $key_id;
          }
        }
      }

      switch ($userspn_ajax_type) {
        case 'userspn_options_save':
          if (!empty($key_value)) {
            foreach ($key_value as $key => $value) {
              if (!in_array($key, ['action', 'userspn_ajax_type'])) {
                update_option($key, $value);
              }
            }

            update_option('userspn_options_changed', true);
            echo wp_json_encode(['error_key' => '', ]);exit();
          }else{
            echo wp_json_encode(['error_key' => 'userspn_options_save_error', ]);exit();
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
                    <?php USERSPN_Forms::input_wrapper_builder($profile_field, 'user', esc_html($user_id), 0, 'full'); ?>
                  <?php endforeach ?>
                <?php endif ?>

                <div class="userspn-text-align-right">
                  <input type="submit" data-users-user-id="<?php echo esc_attr($user_id); ?>" value="<?php esc_html_e('Update user', 'userspn'); ?>" class="userspn-btn userspn-manage-btn"/><?php echo esc_html(USERSPN_Data::loader()); ?>
                </div>
              </form>
            <?php
            exit();
          }else{
            echo 'userspn_popup_manager_edit_error';exit();
          }
          break;
        case 'userspn_manager_edit':
          if (!empty($user_id) && user_can($current_user_id, 'administrator')) {
            if (!empty($key_value)) {
              foreach ($key_value as $key => $value) {
                if ($key == 'userspn_roles') {
                  global $wp_roles;
                  $user = new WP_User($user_id);

                  foreach (array_keys($wp_roles->roles) as $role_key) {
                    if (in_array($role_key, $value)) {
                      $user->add_role($role_key);
                    }else{
                      $user->remove_role($role_key);
                    }
                  }                
                }else{
                  update_user_meta($user_id, $key, $value);
                }
              }
            }

            do_action('userspn_manager_edit', $user_id, $key_value);
            echo 'userspn_manager_edit_success';exit();
          }else{
            echo 'userspn_manager_edit_error';exit();
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
            exit();
          }else{
            echo 'userspn_popup_manager_remove_error';exit();
          }
          break;
        case 'userspn_manager_remove':
          if (!empty($user_id) && user_can($current_user_id, 'administrator')) {
            wp_delete_user($user_id);

            echo 'userspn_manager_remove_success';exit();
          }else{
            echo 'userspn_manager_remove_error';exit();
          }
          break;
        case 'userspn_input_editor_builder_add':
          $userspn_meta = !empty($_POST['userspn_meta']) ? USERSPN_Forms::sanitizer(wp_unslash($_POST['userspn_meta'])) : [];

          $userspn_array = [
            'id' => '',
            'class' => '',
            'input' => 'input',
            'type' => 'text',
            'required' => false,
            'label' => '',
            'userspn_meta' => $userspn_meta,
          ];

          USERSPN_Forms::input_editor_builder($userspn_array);exit();
          break;
        case 'userspn_input_editor_builder_save':
          $userspn_input_name = !empty($_POST['userspn_input_name']) ? USERSPN_Forms::sanitizer(wp_unslash($_POST['userspn_input_name'])) : '';
          $userspn_input_current_id = !empty($_POST['userspn_input_current_id']) ? USERSPN_Forms::sanitizer(wp_unslash($_POST['userspn_input_current_id'])) : 0;
          $userspn_input_type = !empty($_POST['userspn_input_type']) ? USERSPN_Forms::sanitizer(wp_unslash($_POST['userspn_input_type'])) : '';
          $userspn_form_type = !empty($_POST['userspn_form_type']) ? USERSPN_Forms::sanitizer(wp_unslash($_POST['userspn_form_type'])) : '';
          $userspn_input_id = !empty($_POST['userspn_input_id']) ? USERSPN_Forms::sanitizer(wp_unslash($_POST['userspn_input_id'])) : 0;
          $userspn_input_class = !empty($_POST['userspn_input_class']) ? USERSPN_Forms::sanitizer(wp_unslash($_POST['userspn_input_class'])) : '';
          $userspn_input_subtype = !empty($_POST['userspn_input_subtype']) ? USERSPN_Forms::sanitizer(wp_unslash($_POST['userspn_input_subtype'])) : '';
          $userspn_input_subtype = !empty($_POST['userspn_input_subtype']) ? USERSPN_Forms::sanitizer(wp_unslash($_POST['userspn_input_subtype'])) : '';
          $userspn_input_required = !empty($_POST['userspn_input_required']) ? USERSPN_Forms::sanitizer(wp_unslash($_POST['userspn_input_required'])) : '';
          $userspn_input_min = !empty($_POST['userspn_input_min']) ? USERSPN_Forms::sanitizer(wp_unslash($_POST['userspn_input_min'])) : 0;
          $userspn_input_max = !empty($_POST['userspn_input_max']) ? USERSPN_Forms::sanitizer(wp_unslash($_POST['userspn_input_max'])) : 0;
          $userspn_input_label_min = !empty($_POST['userspn_input_label_min']) ? USERSPN_Forms::sanitizer(wp_unslash($_POST['userspn_input_label_min'])) : '';
          $userspn_input_label_max = !empty($_POST['userspn_input_label_max']) ? USERSPN_Forms::sanitizer(wp_unslash($_POST['userspn_input_label_max'])) : '';
          $userspn_select_options = !empty($_POST['userspn_select_options']) ? $_POST['userspn_select_options'] : '';
          $userspn_select_subtype = !empty($_POST['userspn_select_subtype']) ? USERSPN_Forms::sanitizer(wp_unslash($_POST['userspn_select_subtype'])) : '';
          $userspn_textarea_subtype = !empty($_POST['userspn_textarea_subtype']) ? USERSPN_Forms::sanitizer(wp_unslash($_POST['userspn_textarea_subtype'])) : '';
          $userspn_meta = !empty($_POST['userspn_meta']) ? USERSPN_Forms::sanitizer(wp_unslash($_POST['userspn_meta'])) : '';

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
                if(empty(get_option('userspn_user_register_fields'))) {
                  update_option('userspn_user_register_fields', [$userspn_input_id => $userspn_meta_value]);
                }else{
                  $userspn_option_new = get_option('userspn_user_register_fields', true);
                  $userspn_option_new[$userspn_input_id] = $userspn_meta_value;
                  update_option('userspn_user_register_fields', $userspn_option_new);
                }

                break;
              case 'forum':
                if(empty(get_post_meta($post_id, $userspn_meta, true))) {
                  update_post_meta($post_id, $userspn_meta, [$userspn_input_id => $userspn_meta_value]);
                }else{
                  $userspn_post_meta_new = get_post_meta($post_id, $userspn_meta, true);
                  $userspn_post_meta_new[$userspn_input_id] = $userspn_meta_value;
                  update_post_meta($post_id, $userspn_meta, $userspn_post_meta_new);
                }

                break;
              case 'table':
                if(empty(get_post_meta($post_id, $userspn_meta, true))) {
                  update_post_meta($post_id, $userspn_meta, [$userspn_input_id => $userspn_meta_value]);
                }else{
                  $userspn_post_meta_new = get_post_meta($post_id, $userspn_meta, true);
                  $userspn_post_meta_new[$userspn_input_id] = $userspn_meta_value;
                  update_post_meta($post_id, $userspn_meta, $userspn_post_meta_new);
                }

                break;
              case 'questionnaire':
                if(empty(get_post_meta($post_id, $userspn_meta, true))) {
                  update_post_meta($post_id, $userspn_meta, [$userspn_input_id => $userspn_meta_value]);
                }else{
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
                <?php USERSPN_Forms::input_builder($userspn_meta_value, 'user'); ?>
              </div>
            <?php
            $userspn_return_string = ob_get_contents(); 
            ob_end_clean(); 
            echo wp_json_encode(['error_key' => 0, 'html' => wp_kses($userspn_return_string, USERSPN_KSES), 'field_id' => $userspn_input_id, 'label' => $userspn_input_name, 'type' => $userspn_form_type, 'userspn_meta' => $userspn_meta, ]);exit();
          }else{
            echo wp_json_encode(['error_key' => 'empty', ]);exit();
          }
          break;
        case 'userspn_input_editor_builder_remove':
          $userspn_meta = !empty($_POST['userspn_meta']) ? USERSPN_Forms::sanitizer(wp_unslash($_POST['userspn_meta'])) : '';
          $userspn_input_id = !empty($_POST['userspn_input_id']) ? USERSPN_Forms::sanitizer(wp_unslash($_POST['userspn_input_id'])) : 0;
          $userspn_form_type = !empty($_POST['userspn_form_type']) ? USERSPN_Forms::sanitizer(wp_unslash($_POST['userspn_form_type'])) : '';

          if (!empty($userspn_input_id) && !empty($userspn_form_type)) {
            if ($userspn_form_type == 'option') {
              $userspn_user_register_fields = get_option('userspn_user_register_fields');

              if(!empty($userspn_user_register_fields[$userspn_input_id])) {
                unset($userspn_user_register_fields[$userspn_input_id]);
                update_option('userspn_user_register_fields', $userspn_user_register_fields);
              }
            }else{
              $userspn_user_register_fields = get_post_meta($post_id, $userspn_meta, true);

              if(!empty($userspn_user_register_fields[$userspn_input_id])) {
                unset($userspn_user_register_fields[$userspn_input_id]);
                update_post_meta($post_id, $userspn_meta, $userspn_user_register_fields);
              }
            }

            echo 'userspn_input_editor_builder_remove_success';exit();
          }else{
            echo 'userspn_input_editor_builder_remove_error';exit();
          }
          break;
        case 'userspn_profile_edit':
          if (!empty($user_id)) {
            foreach ($key_value as $key => $value) {
              if (!in_array($key, ['userspn_ajax_type', 'user_id', 'action'])) {
                update_user_meta($user_id, $key, $value);
              }
            }

            do_action('userspn_profile_edit', $user_id, $key_value);
            echo 'userspn_profile_edit_success';exit();
          }else{
            echo 'userspn_profile_edit_error';exit();
          }
          break;
        case 'userspn_notifications':
          if (!empty($user_id)) {
            foreach ($key_value as $key => $value) {
              if (!in_array($key, ['userspn_ajax_type', 'user_id', 'action', 'userspn-notifications-btn'])) {
                update_user_meta($user_id, $key, $value);
              }
            }

            echo 'userspn_notifications_success';exit();
          }else{
            echo 'userspn_notifications_error';exit();
          }
          break;
        case 'userspn_file_uploaded_share':
          if (!empty($file_id) && !empty($post_id)) {
            $userspn_meta_value = $file_id;
            if(empty(get_post_meta($post_id, 'userspn_file_uploaded_shared', true))) {
              update_post_meta($post_id, 'userspn_file_uploaded_shared', [$userspn_meta_value]);
            }else{
              $userspn_post_meta_new = get_post_meta($post_id, 'userspn_file_uploaded_shared', true);

              if (in_array($userspn_meta_value, $userspn_post_meta_new)) {
                unset($userspn_post_meta_new[array_search($file_id, $userspn_post_meta_new)]);
              }else{
                $userspn_post_meta_new[] = $userspn_meta_value;
              }

              update_post_meta($post_id, 'userspn_file_uploaded_shared', $userspn_post_meta_new);
            }
              
            echo 'userspn_file_uploaded_share_success';exit();
          }else{
            echo 'userspn_file_uploaded_share_error';exit();
          }
          break;
        case 'userspn_user_remove':
          $password = !empty($_POST['password']) ? wp_unslash($_POST['password']) : '';

          if (!empty($user_id) && !empty($password)) {
            $plugin_user = new USERSPN_Functions_User();

            if ($plugin_user->userspn_check_password($user_id, $password)) {
              require_once(ABSPATH . 'wp-admin/includes/user.php');

              if (!user_can($user_id, 'administrator') && $user_id == get_current_user_id()) {
                wp_delete_user($user_id);
                echo 'userspn_user_remove_success';exit();
              }else{
                echo 'userspn_user_remove_error';exit();
              }
            }else{
              echo 'userspn_user_remove_error_invalid_password';exit();
            }
          }else{
            echo 'userspn_user_remove_error_empty';exit();
          }
          break;
        case 'userspn_options_save':
          if (!empty($key_value)) {
            foreach ($key_value as $key => $value) {
              if (!in_array($key, ['action', 'userspn_ajax_type'])) {
                update_option($key, $value);
              }
            }

            update_option('userspn_options_changed', true);
            echo 'userspn_options_save_success';exit();
          }else{
            echo 'userspn_options_save_error';exit();
          }
          break;
        case 'userspn_csv_add_contacts':
          if (!empty($user_id)) {
            $userspn_uploaded_file = get_posts(['fields' => 'ids', 'numberposts' => 1, 'post_type' => 'attachment', 'post_status' => ['any'], 'author' => $user_id, 'orderby' => 'ID', 'order' => 'DESC', ]);

            $plugin_csv = new USERSPN_CSV();
            $plugin_csv->userspn_csv_template_creator(esc_url(wp_get_attachment_url($userspn_uploaded_file[0])));

            echo 'userspn_csv_add_contacts_success';exit();
          }else{
            echo 'userspn_csv_add_contacts_error';exit();
          }
          break;
        case 'userspn_file_private_remove':
          if (!empty($user_id) && !empty($file_id)) {
            if (get_post($file_id)->post_author == $user_id || current_user_can('administrator')) {
              wp_delete_post($file_id, true);
              echo 'userspn_file_private_remove_success';exit();
            }else{
              echo 'userspn_file_private_remove_error';exit();
            }
          }else{
            echo 'userspn_file_private_remove_error';exit();
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
            
            echo wp_json_encode(['error_key' => '', 'response' => esc_html(__('Your file has been uploaded succesfully.', 'userspn')), 'html' => $plugin_attachment->userspn_get_private_file_uploaded($attach_id), ]);exit();
          }else{
            echo wp_json_encode(['error_key' => 'empty', 'response' => esc_html(__('Please, select a valid file.', 'userspn')), ]);exit();
          }

          break;
        case 'userspn_profile_image':
          if ($_FILES) {
            require_once(ABSPATH . 'wp-admin' . '/includes/image.php');
            require_once(ABSPATH . 'wp-admin' . '/includes/file.php');
            require_once(ABSPATH . 'wp-admin' . '/includes/media.php');

            $file_handler = 'userspn_uploaded_file';
            $userspn_file_id = media_handle_upload($file_handler, $pid);

            if (is_wp_error($userspn_file_id)) {
              echo 'userspn_profile_image_error';exit();
            }else{
              $userspn_related_user_id = !empty($_POST['userspn_related_user_id']) ? USERSPN_Forms::sanitizer(wp_unslash($_POST['userspn_related_user_id'])) : '';

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
              exit();
            }
          }else{
            echo 'userspn_profile_image_error';exit();
          }
          break;
        case 'userspn_csv_template_upload':
          if ($_FILES) {
            $userspn_csv_template_upload = !empty($_POST['userspn_csv_template_upload']) ? USERSPN_Forms::sanitizer(wp_unslash($_POST['userspn_csv_template_upload'])) : 0;
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            
            $file_handler = 'userspn_csv_template_upload';
            $post_id = 0;
            $userspn_file_id = media_handle_upload($file_handler, $post_id);
          
            if (empty($userspn_file_id)) {
              echo 'userspn_csv_template_upload_error_empty';exit();
            }elseif (is_wp_error($userspn_file_id)) {
              echo 'userspn_csv_template_upload_error';exit();
            }else{
              $plugin_user = new USERSPN_Functions_User();
              echo $plugin_user->userspn_csv_template_reader(wp_get_attachment_url($userspn_file_id));exit();
            }
          }else{
            echo 'userspn_csv_template_upload_error';exit();
          }
          break;
      }

      echo wp_json_encode(['error_key' => 'userspn_save_error', ]);exit();
    }
	}
}