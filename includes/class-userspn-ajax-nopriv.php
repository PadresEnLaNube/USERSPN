<?php
/**
 * Load the plugin no private Ajax functions.
 *
 * Load the plugin no private Ajax functions to be executed in background.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    USERSPN
 * @subpackage USERSPN/includes
 * @author     Padres en la Nube
 */
class USERSPN_Ajax_Nopriv {
  /**
   * Load the plugin templates.
   *
   * @since    1.0.0
   */
  public function userspn_ajax_nopriv_server() {
    if (array_key_exists('userspn_ajax_nopriv_type', $_POST)) {
      if (!array_key_exists('userspn_ajax_nopriv_nonce', $_POST)) {
        echo wp_json_encode([
          'error_key' => 'userspn_nonce_ajax_nopriv_error_required',
          'error_content' => esc_html(__('Security check failed: Nonce is required.', 'userspn')),
        ]);

        exit;
      }

      if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['userspn_ajax_nopriv_nonce'])), 'userspn-nonce')) {
        echo wp_json_encode([
          'error_key' => 'userspn_nonce_ajax_nopriv_error_invalid',
          'error_content' => esc_html(__('Security check failed: Invalid nonce.', 'userspn')),
        ]);

        exit;
      }

      $userspn_ajax_nopriv_type = USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_ajax_nopriv_type']));
      
      $userspn_ajax_keys = !empty($_POST['userspn_ajax_keys']) ? array_map(function($key) {
        $sanitized_key = wp_unslash($key);
        return array(
          'id' => sanitize_key($sanitized_key['id']),
          'node' => sanitize_key($sanitized_key['node']),
          'type' => sanitize_key($sanitized_key['type']),
          // keep original truthiness (can be true/false or 'true'/'false')
          'multiple' => isset($sanitized_key['multiple']) ? $sanitized_key['multiple'] : ''
        );
      }, wp_unslash($_POST['userspn_ajax_keys'])) : [];
      // Backwards compatibility: accept 'ajax_keys' if 'userspn_ajax_keys' not present
      if (empty($userspn_ajax_keys) && !empty($_POST['ajax_keys'])) {
        $userspn_ajax_keys = array_map(function($key) {
          $sanitized_key = wp_unslash($key);
          return array(
            'id' => sanitize_key($sanitized_key['id']),
            'node' => sanitize_key($sanitized_key['node']),
            'type' => sanitize_key($sanitized_key['type']),
            'multiple' => isset($sanitized_key['multiple']) ? $sanitized_key['multiple'] : ''
          );
        }, wp_unslash($_POST['ajax_keys']));
      }

      $userspn_key_value = [];

      if (!empty($userspn_ajax_keys)) {
        foreach ($userspn_ajax_keys as $userspn_key) {
          // Robust detection of multiple-value fields
          $raw_id = isset($userspn_key['id']) ? $userspn_key['id'] : '';
          $clear_key = str_replace('[]', '', $raw_id);
          
          // DEBUG: Log key processing for select-multiple
          $is_select_multiple = ($userspn_key['node'] === 'SELECT' || $userspn_key['node'] === 'select') && 
                                ($userspn_key['type'] === 'select-multiple');
          
          // Debug logs removed
          
          // For select-multiple fields, check both key formats (with and without [])
          // This is necessary because jQuery POST may preserve [] in key names
          $posted_value = null;
          
          if ($is_select_multiple) {
            // Check both with [] and without [] for select multiple fields
            if (isset($_POST[$raw_id]) && is_array($_POST[$raw_id])) {
              $posted_value = wp_unslash($_POST[$raw_id]);
            } elseif (isset($_POST[$clear_key]) && is_array($_POST[$clear_key])) {
              $posted_value = wp_unslash($_POST[$clear_key]);
            }
          } else {
            // For non-select-multiple fields, check only the clear key
            // This ensures html_multi fields are not affected
            $posted_value = isset($_POST[$clear_key]) ? wp_unslash($_POST[$clear_key]) : null;
          }
          
          $is_multiple_field = (
            $userspn_key['multiple'] === 'true' ||
            $userspn_key['multiple'] === true ||
            $userspn_key['multiple'] === 1 ||
            $userspn_key['type'] === 'select-multiple' ||
            is_array($posted_value)
          );

          if ($is_multiple_field) {
            $userspn_clear_key = $clear_key;
            ${$userspn_clear_key} = $userspn_key_value[$userspn_clear_key] = [];

            if (!empty($posted_value)) {
              $unslashed_array = $posted_value;
              if (!is_array($unslashed_array)) {
                $unslashed_array = array($unslashed_array);
              }

              // Special handling: for select[multiple], sanitize the full array at once
              if ($is_select_multiple) {
                $sanitized_array = USERSPN_Forms::userspn_sanitizer(
                  $unslashed_array,
                  'select',
                  'select-multiple',
                  $userspn_key['field_config'] ?? []
                );
                if (!is_array($sanitized_array)) {
                  $sanitized_array = [];
                }
              } else {
                $sanitized_array = array_map(function($value) use ($userspn_key) {
                  return USERSPN_Forms::userspn_sanitizer(
                    $value,
                    $userspn_key['node'],
                    $userspn_key['type'],
                    $userspn_key['field_config'] ?? []
                  );
                }, $unslashed_array);
              }

              // Keep only non-empty values
              $sanitized_array = array_filter($sanitized_array, function($v) { return $v !== '' && $v !== null; });
              
              // Normalize: cast to int if all numeric, unique, and reindex
              $all_numeric = !empty($sanitized_array) && count(array_filter($sanitized_array, 'is_numeric')) === count($sanitized_array);
              if ($all_numeric) {
                $sanitized_array = array_map('intval', $sanitized_array);
              }
              $sanitized_array = array_values(array_unique($sanitized_array));

              ${$userspn_clear_key} = $userspn_key_value[$userspn_clear_key] = $sanitized_array;
            } else {
              // Explicitly store empty array for multiple fields with no selection
              ${$userspn_clear_key} = [];
              $userspn_key_value[$userspn_clear_key] = [];
            }
          } else {
            $sanitized_key = sanitize_key($userspn_key['id']);
            $unslashed_value = !empty($_POST[$sanitized_key]) ? wp_unslash($_POST[$sanitized_key]) : '';
            
            $userspn_key_id = !empty($unslashed_value) ? 
              USERSPN_Forms::userspn_sanitizer(
                $unslashed_value, 
                $userspn_key['node'], 
                $userspn_key['type'],
                $userspn_key['field_config'] ?? [],
              ) : '';
            
              ${$userspn_key['id']} = $userspn_key_value[$userspn_key['id']] = $userspn_key_id;
          }
        }
      }

      switch ($userspn_ajax_nopriv_type) {
        case 'userspn_lostpassword':
          if (!wp_verify_nonce($_POST['userspn_ajax_nopriv_nonce'], 'userspn-nonce')) {
            wp_send_json_error([
              'error_key' => 'invalid_nonce',
              'error_content' => __('Security check failed. Please try again.', 'userspn')
            ]);
          }
          wp_send_json_success([
            'message' => __('Security check passed. Proceeding with password reset...', 'userspn')
          ]);
          break;
        case 'userspn_form_save':
          $userspn_form_type = !empty($_POST['userspn_form_type']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_form_type'])) : '';

          if (!empty($userspn_key_value) && !empty($userspn_form_type)) {
            $userspn_form_id = !empty($_POST['userspn_form_id']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_form_id'])) : 0;
            $userspn_form_subtype = !empty($_POST['userspn_form_subtype']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_form_subtype'])) : '';
            $user_id = !empty($_POST['userspn_form_user_id']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_form_user_id'])) : 0;
            $post_id = !empty($_POST['userspn_form_post_id']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_form_post_id'])) : 0;
            $post_type = !empty($_POST['userspn_form_post_type']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_form_post_type'])) : '';

            if (($userspn_form_type == 'user' && empty($user_id) && !in_array($userspn_form_subtype, ['user_alt_new'])) || ($userspn_form_type == 'post' && (empty($post_id) && !(!empty($userspn_form_subtype) && in_array($userspn_form_subtype, ['post_new', 'post_edit'])))) || ($userspn_form_type == 'option' && !is_user_logged_in())) {
              session_start();

              $_SESSION['userspn_form'] = [];
              $_SESSION['userspn_form'][$userspn_form_id] = [];
              $_SESSION['userspn_form'][$userspn_form_id]['form_type'] = $userspn_form_type;
              $_SESSION['userspn_form'][$userspn_form_id]['values'] = $userspn_key_value;

              if (!empty($post_id)) {
                $_SESSION['userspn_form'][$userspn_form_id]['post_id'] = $post_id;
              }

              echo wp_json_encode(['error_key' => 'userspn_form_save_error_unlogged', ]);exit;
            }else{
              switch ($userspn_form_type) {
                case 'user':
                  if (!in_array($userspn_form_subtype, ['user_alt_new'])) {
                    if (empty($user_id)) {
                      if (USERSPN_Functions_User::userspn_user_is_admin(get_current_user_id())) {
                        $user_login = !empty($_POST['user_login']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['user_login'])) : 0;
                        $user_password = !empty($_POST['user_password']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['user_password'])) : 0;
                        $user_email = !empty($_POST['user_email']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['user_email'])) : 0;

                        $user_id = USERSPN_Functions_User::userspn_user_insert($user_login, $user_password, $user_email);
                      }
                    }

                    if (!empty($user_id)) {
                      // Authorization: only the account owner or an admin may update user meta
                      if (!is_user_logged_in() || (intval($user_id) !== get_current_user_id() && !USERSPN_Functions_User::userspn_user_is_admin(get_current_user_id()))) {
                        echo wp_json_encode([
                          'error_key' => 'userspn_form_save_error_unauthorized',
                          'error_content' => esc_html(__('You are not authorized to perform this action.', 'userspn')),
                        ]);
                        exit;
                      }

                      foreach ($userspn_key_value as $userspn_key => $userspn_value) {
                        // Skip action and ajax type keys
                        if (in_array($userspn_key, ['action', 'userspn_ajax_nopriv_type'])) {
                          continue;
                        }

                        // Ensure option name is prefixed with userspn_
                        // Special case: if key is just 'userspn', don't add prefix as it's already the main option
                        $original_key = $userspn_key;
                        if ($userspn_key !== 'userspn' && strpos((string)$userspn_key, 'userspn_') !== 0) {
                          $userspn_key = 'userspn_' . $userspn_key;
                        } else {
                          // Key already has correct prefix
                        }

                        update_user_meta($user_id, $userspn_key, $userspn_value);
                        
                        // Additionally write to the original (non-prefixed) key
                        // This keeps standard WP fields (first_name, last_name, etc.) and legacy meta in sync
                        if (!empty($original_key) && strpos((string)$original_key, 'userspn_') !== 0) {
                          update_user_meta($user_id, $original_key, $userspn_value);
                        }
                      }
                    }
                  }

                  do_action('userspn_form_save', $user_id, $userspn_key_value, $userspn_form_type, $userspn_form_subtype, '');
                  break;
                case 'post':
                  if (empty($userspn_form_subtype) || in_array($userspn_form_subtype, ['post_new', 'post_edit'])) {
                    if (empty($post_id)) {
                      // Allow any logged-in user to create a new post
                      if (is_user_logged_in()) {
                        $post_functions = new TASKSPN_Functions_Post();
                        $title = !empty($_POST[$post_type . '_title']) ? TASKSPN_Forms::taskspn_sanitizer(wp_unslash($_POST[$post_type . '_title'])) : '';
                        $description = !empty($_POST[$post_type . '_description']) ? TASKSPN_Forms::taskspn_sanitizer(wp_unslash($_POST[$post_type . '_description'])) : '';
                        
                        $post_id = $post_functions->taskspn_insert_post($title, $description, '', sanitize_title($title), $post_type, 'publish', get_current_user_id());
                      }
                    }

                    if (!empty($post_id)) {
                      // Authorization: only logged-in users who own the post or are admins may update post meta
                      if (!is_user_logged_in()) {
                        echo wp_json_encode([
                          'error_key' => 'userspn_form_save_error_unauthorized',
                          'error_content' => esc_html(__('You are not authorized to perform this action.', 'userspn')),
                        ]);
                        exit;
                      }

                      $post_author_id = intval(get_post_field('post_author', $post_id));
                      if (get_current_user_id() !== $post_author_id && !USERSPN_Functions_User::userspn_user_is_admin(get_current_user_id())) {
                        echo wp_json_encode([
                          'error_key' => 'userspn_form_save_error_unauthorized',
                          'error_content' => esc_html(__('You are not authorized to perform this action.', 'userspn')),
                        ]);
                        exit;
                      }

                      foreach ($userspn_key_value as $userspn_key => $userspn_value) {
                        if ($userspn_key == $post_type . '_title') {
                          wp_update_post([
                            'ID' => $post_id,
                            'post_title' => esc_html($userspn_value),
                          ]);
                        }

                        if ($userspn_key == $post_type . '_description') {
                          wp_update_post([
                            'ID' => $post_id,
                            'post_content' => esc_html($userspn_value),
                          ]);
                        }

                        // Skip action and ajax type keys
                        if (in_array($userspn_key, ['action', 'userspn_ajax_nopriv_type'])) {
                          continue;
                        }

                        // Ensure option name is prefixed with userspn_
                        // Special case: if key is just 'userspn', don't add prefix as it's already the main option
                        if ($userspn_key !== 'userspn' && strpos((string)$userspn_key, 'userspn_') !== 0) {
                          $userspn_key = 'userspn_' . $userspn_key;
                        } else {
                          // Key already has correct prefix
                        }

                        // Generic normalization for any multiple field saved as array
                        if (is_array($userspn_value)) {
                          $values = array_filter($userspn_value, function($v) { return $v !== '' && $v !== null; });
                          $all_numeric = !empty($values) && count(array_filter($values, 'is_numeric')) === count($values);
                          if ($all_numeric) {
                            $values = array_map('intval', $values);
                          }
                          $userspn_value = array_values(array_unique($values));
                        }

                        update_post_meta($post_id, $userspn_key, $userspn_value);
                      }
                    }
                  }

                  do_action('userspn_form_save', $post_id, $userspn_key_value, $userspn_form_type, $userspn_form_subtype, $post_type);
                  break;
                case 'option':
                  if (USERSPN_Functions_User::userspn_user_is_admin(get_current_user_id())) {
                    $userspn_settings = new USERSPN_Settings();
                    $userspn_options = $userspn_settings->get_options();
                    $userspn_allowed_options = array_keys($userspn_options);

                    // First, add html_multi field IDs to allowed options temporarily
                    foreach ($userspn_options as $option_key => $option_config) {
                      if (isset($option_config['input']) && $option_config['input'] === 'html_multi' && 
                          isset($option_config['html_multi_fields']) && is_array($option_config['html_multi_fields'])) {
                        foreach ($option_config['html_multi_fields'] as $multi_field) {
                          if (isset($multi_field['id'])) {
                            $userspn_allowed_options[] = $multi_field['id'];
                          }
                        }
                      }
                    }

                    // Process remaining individual fields
                    foreach ($userspn_key_value as $userspn_key => $userspn_value) {
                      // Skip action and ajax type keys
                      if (in_array($userspn_key, ['action', 'userspn_ajax_nopriv_type'])) {
                        continue;
                      }

                      // Ensure option name is prefixed with userspn_
                      // Special case: if key is just 'userspn', don't add prefix as it's already the main option
                      if ($userspn_key !== 'userspn' && strpos((string)$userspn_key, 'userspn_') !== 0) {
                        $userspn_key = 'userspn_' . $userspn_key;
                      } else {
                        // Key already has correct prefix
                      }

                      // Only update if option is in allowed options list
                      if (in_array($userspn_key, $userspn_allowed_options)) {
                        update_option($userspn_key, $userspn_value);
                      }
                    }
                  }

                  do_action('userspn_form_save', 0, $userspn_key_value, $userspn_form_type, $userspn_form_subtype, '');
                  break;
              }

              $popup_close = in_array($userspn_form_subtype, ['post_new', 'post_edit', 'user_alt_new']) ? true : '';
              $update_list = in_array($userspn_form_subtype, ['post_new', 'post_edit', 'user_alt_new']) ? true : '';
              $check = in_array($userspn_form_subtype, ['post_check', 'post_uncheck']) ? $userspn_form_subtype : '';
              
              if ($update_list && !empty($post_type)) {
                switch ($post_type) {
                  case 'userspn_basecpt':
                    $plugin_post_type_basecpt = new USERSPN_Post_Type_BaseCPT();
                    // Return the full wrapper so the search/add toolbar persists
                    $update_html = $plugin_post_type_basecpt->userspn_basecpt_list_wrapper();
                    break;
                }
              }else{
                $update_html = '';
              }

              echo wp_json_encode(['error_key' => '', 'popup_close' => $popup_close, 'update_list' => $update_list, 'update_html' => $update_html, 'check' => $check]);exit;
            }
          }else{
            echo wp_json_encode(['error_key' => 'userspn_form_save_error', ]);exit;
          }
          break;
        case 'userspn_profile_create':
          $userspn_email = !empty($_POST['userspn_email']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_email'])) : '';
          $userspn_password = !empty($_POST['userspn_password']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_password'])) : '';
          $extended = get_option('userspn_extended_registration', 'on');
          $plugin_user = new USERSPN_Functions_User();

          // Simple registration: only email required
          if ($extended !== 'on') {
            if (empty($userspn_email)) {
              echo 'userspn_profile_create_error';exit;
            }

            if (email_exists($userspn_email)) {
              echo 'userspn_profile_create_existing';exit;
            }

            $user_data = [
              'email' => $userspn_email,
              'first_name' => '',
              'last_name' => '',
              'description' => '',
              'ip' => USERSPN_Security::get_user_ip(),
              'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ];

            $security_result = USERSPN_Security::validate_registration_security($_POST, $user_data);
            if (is_wp_error($security_result)) {
              USERSPN_Security::log_security_event('registration_blocked', $security_result->get_error_message(), [
                'email' => $userspn_email,
                'ip' => USERSPN_Security::get_user_ip()
              ]);
              echo wp_json_encode([
                'error_key' => 'userspn_profile_create_security_error',
                'error_message' => $security_result->get_error_message(),
                'error_code' => $security_result->get_error_code(),
              ]);
              exit;
            }

            $userspn_generated_password = wp_generate_password(24, true, true);
            $userspn_login = USERSPN_Functions_User::generate_unique_login($userspn_email);
            $user_id = USERSPN_Functions_User::userspn_user_insert($userspn_login, $userspn_generated_password, $userspn_email, '', '', $userspn_login, $userspn_login, $userspn_login, '', ['subscriber'], [
              ['userspn_secret_token' => bin2hex(openssl_random_pseudo_bytes(16))],
            ]);

            if (!$user_id) {
              $wp_error_msg = '';
              if (USERSPN_Functions_User::$last_insert_error && is_wp_error(USERSPN_Functions_User::$last_insert_error)) {
                $wp_error_msg = USERSPN_Functions_User::$last_insert_error->get_error_message();
              }
              USERSPN_Security::log_security_event('registration_failed', 'User creation failed: ' . ($wp_error_msg ?: 'unknown error'), [
                'email' => $userspn_email,
                'login' => $userspn_login,
                'ip' => USERSPN_Security::get_user_ip(),
                'wp_error' => $wp_error_msg,
              ]);
              echo 'userspn_profile_create_error';exit;
            }

            update_user_meta($user_id, 'userspn_registration_ip', USERSPN_Security::get_user_ip());
            update_user_meta($user_id, 'userspn_registration_user_agent', $_SERVER['HTTP_USER_AGENT'] ?? '');

            if (get_option('userspn_recaptcha_enabled') === 'on') {
              $recaptcha_token = $_POST['g-recaptcha-response'] ?? '';
              if (!empty($recaptcha_token)) {
                $recaptcha_result = USERSPN_Security::verify_recaptcha($recaptcha_token, 'register');
                if (!is_wp_error($recaptcha_result)) {
                  update_user_meta($user_id, 'userspn_recaptcha_score', $recaptcha_result['score']);
                  update_user_meta($user_id, 'userspn_recaptcha_threshold', $recaptcha_result['threshold']);
                  update_user_meta($user_id, 'userspn_recaptcha_timestamp', current_time('timestamp'));

                  if ($recaptcha_result['is_suspicious']) {
                    update_user_meta($user_id, 'userspn_recaptcha_suspicious', true);
                    USERSPN_Security::send_suspicious_registration_notification($user_id, $recaptcha_result, $user_data);
                    USERSPN_Security::log_security_event('suspicious_registration', 'Suspicious user registration detected', [
                      'user_id' => $user_id,
                      'email' => $userspn_email,
                      'score' => $recaptcha_result['score'],
                      'threshold' => $recaptcha_result['threshold'],
                      'ip' => USERSPN_Security::get_user_ip()
                    ]);
                  } else {
                    update_user_meta($user_id, 'userspn_recaptcha_suspicious', false);
                  }
                }
              }
            }

            wp_new_user_notification($user_id, null, 'user');

            USERSPN_Security::log_security_event('registration_success', 'Simple registration completed successfully', [
              'user_id' => $user_id,
              'email' => $userspn_email,
              'ip' => USERSPN_Security::get_user_ip()
            ]);

            do_action('userspn_profile_create', $user_id, $userspn_key_value);
            echo 'userspn_profile_create_simple_success';exit;
          }

          // Extended registration: email + password + optional fields
          if (!empty($userspn_email) && !empty($userspn_password)) {
            if (email_exists($userspn_email)) {
              echo 'userspn_profile_create_existing';exit;
            } else {
              $user_data = [
                'email' => $userspn_email,
                'first_name' => $userspn_key_value['first_name'] ?? '',
                'last_name' => $userspn_key_value['last_name'] ?? '',
                'description' => $userspn_key_value['description'] ?? '',
                'ip' => USERSPN_Security::get_user_ip(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
              ];

              $security_result = USERSPN_Security::validate_registration_security($_POST, $user_data);
              if (is_wp_error($security_result)) {
                USERSPN_Security::log_security_event('registration_blocked', $security_result->get_error_message(), [
                  'email' => $userspn_email,
                  'ip' => USERSPN_Security::get_user_ip()
                ]);
                echo wp_json_encode([
                  'error_key' => 'userspn_profile_create_security_error',
                  'error_message' => $security_result->get_error_message(),
                  'error_code' => $security_result->get_error_code(),
                ]);
                exit;
              }

              $userspn_login = USERSPN_Functions_User::generate_unique_login($userspn_email);
              $user_id = USERSPN_Functions_User::userspn_user_insert($userspn_login, $userspn_password, $userspn_email, '', '', $userspn_login, $userspn_login, $userspn_login, '', ['subscriber'], [
                ['userspn_secret_token' => bin2hex(openssl_random_pseudo_bytes(16))],
              ]);

              if (!$user_id) {
                $wp_error_msg = '';
                if (USERSPN_Functions_User::$last_insert_error && is_wp_error(USERSPN_Functions_User::$last_insert_error)) {
                  $wp_error_msg = USERSPN_Functions_User::$last_insert_error->get_error_message();
                }
                USERSPN_Security::log_security_event('registration_failed', 'User creation failed: ' . ($wp_error_msg ?: 'unknown error'), [
                  'email' => $userspn_email,
                  'login' => $userspn_login,
                  'password_length' => strlen($userspn_password),
                  'ip' => USERSPN_Security::get_user_ip(),
                  'wp_error' => $wp_error_msg,
                ]);
                echo 'userspn_profile_create_error';exit;
              }

              update_user_meta($user_id, 'userspn_registration_ip', USERSPN_Security::get_user_ip());
              update_user_meta($user_id, 'userspn_registration_user_agent', $_SERVER['HTTP_USER_AGENT'] ?? '');

              if (get_option('userspn_recaptcha_enabled') === 'on') {
                $recaptcha_token = $_POST['g-recaptcha-response'] ?? '';
                if (!empty($recaptcha_token)) {
                  $recaptcha_result = USERSPN_Security::verify_recaptcha($recaptcha_token, 'register');
                  if (!is_wp_error($recaptcha_result)) {
                    update_user_meta($user_id, 'userspn_recaptcha_score', $recaptcha_result['score']);
                    update_user_meta($user_id, 'userspn_recaptcha_threshold', $recaptcha_result['threshold']);
                    update_user_meta($user_id, 'userspn_recaptcha_timestamp', current_time('timestamp'));

                    if ($recaptcha_result['is_suspicious']) {
                      update_user_meta($user_id, 'userspn_recaptcha_suspicious', true);
                      USERSPN_Security::send_suspicious_registration_notification($user_id, $recaptcha_result, $user_data);
                      USERSPN_Security::log_security_event('suspicious_registration', 'Suspicious user registration detected', [
                        'user_id' => $user_id,
                        'email' => $userspn_email,
                        'score' => $recaptcha_result['score'],
                        'threshold' => $recaptcha_result['threshold'],
                        'ip' => USERSPN_Security::get_user_ip()
                      ]);
                    } else {
                      update_user_meta($user_id, 'userspn_recaptcha_suspicious', false);
                    }
                  }
                }
              }

              foreach ($userspn_key_value as $key => $value) {
                if (!in_array($key, ['action', 'userspn_ajax_nopriv', 'userspn_ajax_nopriv_type', 'userspn_email', 'userspn_password', 'g-recaptcha-response', 'userspn_honeypot_field'])) {
                  update_user_meta($user_id, $key, $value);
                }
              }

              USERSPN_Security::log_security_event('registration_success', 'User registration completed successfully', [
                'user_id' => $user_id,
                'email' => $userspn_email,
                'ip' => USERSPN_Security::get_user_ip()
              ]);

              do_action('userspn_profile_create', $user_id, $userspn_key_value);
              echo 'userspn_profile_create_success';exit;
            }
          } else {
            echo 'userspn_profile_create_error';exit;
          }
          break;
        case 'userspn_newsletter':
          $plugin_user = new USERSPN_Functions_User();
          $userspn_email = !empty($_POST['userspn_email']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_email'])) : '';
          $newsletter_recaptcha_result = null;
          $ip_address = USERSPN_Security::get_user_ip();

          if (!empty($userspn_email)) {
            // Validate email format first
            if (!is_email($userspn_email)) {
              USERSPN_Security::log_security_event('newsletter_invalid_email', 'Invalid email format', [
                'email' => $userspn_email,
              ]);
              echo 'userspn_newsletter_error';exit;
            }

            // Rate limiting for newsletter (more strict)
            if (get_option('userspn_rate_limiting_enabled') === 'on') {
              $rate_limit_result = USERSPN_Security::check_rate_limit($ip_address, 'newsletter');
              if (is_wp_error($rate_limit_result)) {
                USERSPN_Security::log_security_event('newsletter_rate_limit_blocked', $rate_limit_result->get_error_message(), [
                  'email' => $userspn_email,
                  'ip' => $ip_address,
                ]);
                echo 'userspn_newsletter_security_error';exit;
              }
            }

            // Validate suspicious email patterns
            $email_validation = USERSPN_Security::validate_email_suspicious($userspn_email);
            if (is_wp_error($email_validation)) {
              // Block temp emails and sequential patterns
              if (in_array($email_validation->get_error_code(), ['suspicious_email_temp', 'suspicious_email_sequential'])) {
                USERSPN_Security::log_security_event('newsletter_email_blocked', $email_validation->get_error_message(), [
                  'email' => $userspn_email,
                  'ip' => $ip_address,
                ]);
                echo 'userspn_newsletter_security_error';exit;
              }
            }

            // Honeypot validation
            if (get_option('userspn_honeypot_enabled') === 'on') {
              $honeypot_validation = USERSPN_Security::verify_honeypot($_POST);
              if (is_wp_error($honeypot_validation)) {
                USERSPN_Security::log_security_event('newsletter_honeypot_blocked', $honeypot_validation->get_error_message(), [
                  'email' => $userspn_email,
                  'ip' => $ip_address,
                ]);
                echo 'userspn_newsletter_security_error';exit;
              }
            }

            // reCAPTCHA validation
            if (get_option('userspn_recaptcha_enabled') === 'on') {
              $recaptcha_token = isset($_POST['g-recaptcha-response']) ? sanitize_text_field(wp_unslash($_POST['g-recaptcha-response'])) : '';
              if (empty($recaptcha_token)) {
                USERSPN_Security::log_security_event('newsletter_recaptcha_missing', 'reCAPTCHA token missing', [
                  'email' => $userspn_email,
                  'ip' => $ip_address,
                ]);
                echo 'userspn_newsletter_security_error';exit;
              }
              
              $recaptcha_validation = USERSPN_Security::verify_recaptcha($recaptcha_token, 'newsletter');
              if (is_wp_error($recaptcha_validation)) {
                USERSPN_Security::log_security_event('newsletter_recaptcha_blocked', $recaptcha_validation->get_error_message(), [
                  'email' => $userspn_email,
                  'ip' => $ip_address,
                ]);
                echo 'userspn_newsletter_security_error';exit;
              }
              
              $newsletter_recaptcha_result = $recaptcha_validation;
              
              // Block suspicious reCAPTCHA scores
              if ($recaptcha_validation['is_suspicious']) {
                $block_suspicious = get_option('userspn_recaptcha_block_suspicious', 'on');
                if ($block_suspicious === 'on') {
                  USERSPN_Security::log_security_event('newsletter_recaptcha_suspicious_blocked', 'Suspicious reCAPTCHA score blocked', [
                    'email' => $userspn_email,
                    'ip' => $ip_address,
                    'score' => $recaptcha_validation['score'],
                    'threshold' => $recaptcha_validation['threshold'],
                  ]);
                  echo 'userspn_newsletter_security_error';exit;
                }
              }
            }

            // Akismet validation
            if (get_option('userspn_akismet_enabled') === 'on') {
              $akismet_data = [
                'email' => $userspn_email,
                'comment_type' => 'newsletter',
                'comment_content' => '',
              ];
              $akismet_validation = USERSPN_Security::verify_akismet($akismet_data);
              if (is_wp_error($akismet_validation)) {
                USERSPN_Security::log_security_event('newsletter_akismet_blocked', $akismet_validation->get_error_message(), [
                  'email' => $userspn_email,
                  'ip' => $ip_address,
                ]);
                echo 'userspn_newsletter_security_error';exit;
              }
            }

            if (email_exists($userspn_email)) {
              $user_id = get_user_by('email', $userspn_email)->ID;
            } else {
              $userspn_login = USERSPN_Functions_User::generate_unique_login($userspn_email);
              $userspn_password = bin2hex(openssl_random_pseudo_bytes(12));
              $user_id = USERSPN_Functions_User::userspn_user_insert($userspn_login, $userspn_password, $userspn_email, '', '', $userspn_login, $userspn_login, $userspn_login, '', ['userspn_newsletter_subscriber'], []);
            }

            if ($user_id) {
              $user_object = new WP_User($user_id);
              if (!in_array('userspn_newsletter_subscriber', (array) $user_object->roles, true)) {
                $user_object->add_role('userspn_newsletter_subscriber');
              }

              // Store registration IP and user agent for bot analysis
              update_user_meta($user_id, 'userspn_registration_ip', $ip_address);
              update_user_meta($user_id, 'userspn_registration_user_agent', $_SERVER['HTTP_USER_AGENT'] ?? '');
              update_user_meta($user_id, 'userspn_newsletter_registration_timestamp', current_time('timestamp'));

              if (!is_null($newsletter_recaptcha_result)) {
                update_user_meta($user_id, 'userspn_newsletter_recaptcha_score', $newsletter_recaptcha_result['score']);
                update_user_meta($user_id, 'userspn_newsletter_recaptcha_threshold', $newsletter_recaptcha_result['threshold']);
                update_user_meta($user_id, 'userspn_newsletter_recaptcha_timestamp', current_time('timestamp'));
                update_user_meta($user_id, 'userspn_newsletter_recaptcha_suspicious', $newsletter_recaptcha_result['is_suspicious'] ? '1' : '0');

                if ($newsletter_recaptcha_result['is_suspicious']) {
                  USERSPN_Security::log_security_event('newsletter_suspicious', 'Suspicious newsletter subscription detected', [
                    'user_id' => $user_id,
                    'email' => $userspn_email,
                    'score' => $newsletter_recaptcha_result['score'],
                    'threshold' => $newsletter_recaptcha_result['threshold'],
                    'ip' => $ip_address,
                  ]);
                }
              }
            }

            if (get_option('userspn_newsletter_activation') == 'on') {
              $plugin_mailing = new USERSPN_Mailing();
              $result = $plugin_mailing->userspn_send_newsletter_activation_email($user_id, $userspn_email);
              echo $result;exit;
            } else {
              update_user_meta($user_id, 'userspn_newsletter_active', current_time('timestamp'));
              update_user_meta($user_id, 'userspn_notifications', 'on');

              if (class_exists('MAILPN_Mailing')) {
                $mailpn_plugin = new MAILPN_Mailing();
                $userspn_mailing_plugin = new USERSPN_Mailing();
                $registration_emails = $userspn_mailing_plugin->userspn_get_email_newsletter_welcome($user_id);

                if (!empty($registration_emails)) {
                  foreach ($registration_emails as $mail_id) {
                    $users_to = $mailpn_plugin->mailpn_get_users_to($mail_id);
                    if (in_array($user_id, $users_to)) {
                      do_shortcode('[mailpn-sender mailpn_type="newsletter_welcome" mailpn_user_to="' . $user_id . '" mailpn_subject="' . get_the_title($mail_id) . '" mailpn_id="' . $mail_id . '"]');
                    }
                  }
                }
              } else {
                $subject = __('Welcome to our newsletter', 'userspn');
                $body = __('Hello', 'userspn') . ' ' . esc_html($userspn_email) . ".<br>" . __('You have been subscribed to our newsletter. Welcome aboard!', 'userspn');
                wp_mail($userspn_email, $subject, $body);
              }

              echo 'userspn_newsletter_success';exit;
            }
          } else {
            echo 'userspn_newsletter_error';exit;
          }
          break;

        case 'userspn_email_code_request':
          // Verificar que la funcionalidad esté habilitada
          if (get_option('userspn_email_code_login_enabled') != 'on') {
            echo wp_json_encode([
              'error_key' => 'userspn_code_disabled',
              'error_content' => esc_html(__('This login method is not available.', 'userspn')),
            ]);
            exit;
          }

          // Obtener y validar email
          $userspn_email = isset($_POST['userspn_email']) ? sanitize_email(wp_unslash($_POST['userspn_email'])) : '';

          if (empty($userspn_email) || !is_email($userspn_email)) {
            echo wp_json_encode([
              'error_key' => 'userspn_code_invalid_email',
              'error_content' => esc_html(__('Please enter a valid email address.', 'userspn')),
            ]);
            exit;
          }

          // Verificar que el usuario exista (sin revelar si existe o no)
          $user = get_user_by('email', $userspn_email);

          if (!$user) {
            // Mensaje genérico para no revelar si el usuario existe
            echo wp_json_encode([
              'success' => true,
              'message' => esc_html(__('If the email exists, a verification code has been sent.', 'userspn')),
            ]);
            exit;
          }

          $user_id = $user->ID;

          // Rate limiting: verificar último envío
          $last_sent = get_user_meta($user_id, 'userspn_login_code_last_sent', true);
          $current_time = current_time('timestamp');

          if ($last_sent && ($current_time - $last_sent) < 60) {
            $wait_time = 60 - ($current_time - $last_sent);
            echo wp_json_encode([
              'error_key' => 'userspn_code_rate_limit',
              'error_content' => sprintf(
                esc_html(__('Please wait %d seconds before requesting a new code.', 'userspn')),
                $wait_time
              ),
            ]);
            exit;
          }

          // Generar código de 6 dígitos
          $code = sprintf('%06d', mt_rand(0, 999999));
          $expiry = $current_time + 900; // 15 minutos

          // Guardar en user meta
          update_user_meta($user_id, 'userspn_login_code', $code);
          update_user_meta($user_id, 'userspn_login_code_expiry', $expiry);
          update_user_meta($user_id, 'userspn_login_code_attempts', 0);
          update_user_meta($user_id, 'userspn_login_code_last_sent', $current_time);

          // Enviar email con código
          $userspn_mailing = new USERSPN_Mailing();
          $userspn_mailing->userspn_send_login_code_email($user_id, $code);

          echo wp_json_encode([
            'success' => true,
            'message' => esc_html(__('A verification code has been sent to your email.', 'userspn')),
          ]);
          exit;

        case 'userspn_email_code_verify':
          // Verificar que la funcionalidad esté habilitada
          if (get_option('userspn_email_code_login_enabled') != 'on') {
            echo wp_json_encode([
              'error_key' => 'userspn_code_disabled',
              'error_content' => esc_html(__('This login method is not available.', 'userspn')),
            ]);
            exit;
          }

          // Obtener y validar datos
          $userspn_email = isset($_POST['userspn_email']) ? sanitize_email(wp_unslash($_POST['userspn_email'])) : '';
          $userspn_code = isset($_POST['userspn_code']) ? sanitize_text_field(wp_unslash($_POST['userspn_code'])) : '';

          if (empty($userspn_email) || !is_email($userspn_email)) {
            echo wp_json_encode([
              'error_key' => 'userspn_code_invalid_email',
              'error_content' => esc_html(__('Invalid email address.', 'userspn')),
            ]);
            exit;
          }

          if (empty($userspn_code) || strlen($userspn_code) != 6 || !ctype_digit($userspn_code)) {
            echo wp_json_encode([
              'error_key' => 'userspn_code_invalid_format',
              'error_content' => esc_html(__('Invalid code format.', 'userspn')),
            ]);
            exit;
          }

          // Verificar que el usuario exista
          $user = get_user_by('email', $userspn_email);

          if (!$user) {
            echo wp_json_encode([
              'error_key' => 'userspn_code_invalid',
              'error_content' => esc_html(__('Invalid verification code.', 'userspn')),
            ]);
            exit;
          }

          $user_id = $user->ID;

          // Obtener datos del código
          $stored_code = get_user_meta($user_id, 'userspn_login_code', true);
          $expiry = get_user_meta($user_id, 'userspn_login_code_expiry', true);
          $attempts = (int) get_user_meta($user_id, 'userspn_login_code_attempts', true);

          // Verificar intentos máximos
          if ($attempts >= 5) {
            // Limpiar código por seguridad
            delete_user_meta($user_id, 'userspn_login_code');
            delete_user_meta($user_id, 'userspn_login_code_expiry');
            delete_user_meta($user_id, 'userspn_login_code_attempts');
            delete_user_meta($user_id, 'userspn_login_code_last_sent');

            echo wp_json_encode([
              'error_key' => 'userspn_code_max_attempts',
              'error_content' => esc_html(__('Too many failed attempts. Please request a new code.', 'userspn')),
            ]);
            exit;
          }

          // Verificar que el código exista
          if (empty($stored_code)) {
            echo wp_json_encode([
              'error_key' => 'userspn_code_not_found',
              'error_content' => esc_html(__('No verification code found. Please request a new one.', 'userspn')),
            ]);
            exit;
          }

          // Verificar expiración
          $current_time = current_time('timestamp');
          if ($current_time > $expiry) {
            // Limpiar código expirado
            delete_user_meta($user_id, 'userspn_login_code');
            delete_user_meta($user_id, 'userspn_login_code_expiry');
            delete_user_meta($user_id, 'userspn_login_code_attempts');

            echo wp_json_encode([
              'error_key' => 'userspn_code_expired',
              'error_content' => esc_html(__('The verification code has expired. Please request a new one.', 'userspn')),
            ]);
            exit;
          }

          // Verificar que el código coincida
          if ($userspn_code !== $stored_code) {
            // Incrementar intentos fallidos
            update_user_meta($user_id, 'userspn_login_code_attempts', $attempts + 1);

            echo wp_json_encode([
              'error_key' => 'userspn_code_incorrect',
              'error_content' => esc_html(__('Incorrect verification code.', 'userspn')),
              'attempts_remaining' => 5 - ($attempts + 1),
            ]);
            exit;
          }

          // Código correcto - hacer login
          wp_set_current_user($user_id);
          wp_set_auth_cookie($user_id, true);
          do_action('wp_login', $user->user_login, $user);

          // Limpiar todas las user meta relacionadas con el código
          delete_user_meta($user_id, 'userspn_login_code');
          delete_user_meta($user_id, 'userspn_login_code_expiry');
          delete_user_meta($user_id, 'userspn_login_code_attempts');
          delete_user_meta($user_id, 'userspn_login_code_last_sent');

          // Resetear flag de advertencia de inactividad si existe
          update_user_meta($user_id, 'userspn_inactive_warning_sent', 'off');

          // Determinar URL de redirección
          $redirect_url = home_url();
          if (isset($_POST['redirect_to']) && !empty($_POST['redirect_to'])) {
            $redirect_url = esc_url_raw(wp_unslash($_POST['redirect_to']));
          }

          echo wp_json_encode([
            'success' => true,
            'message' => esc_html(__('Login successful!', 'userspn')),
            'redirect_url' => $redirect_url,
          ]);
          exit;

        case 'userspn_google_auth_url':
          // Verificar que Google Login esté habilitado
          if (get_option('userspn_google_login_enabled') != 'on') {
            echo wp_json_encode([
              'error_key' => 'userspn_google_disabled',
              'error_content' => esc_html(__('Google login is not available.', 'userspn')),
            ]);
            exit;
          }

          $client_id = get_option('userspn_google_client_id');
          $client_secret = get_option('userspn_google_client_secret');

          if (empty($client_id) || empty($client_secret)) {
            echo wp_json_encode([
              'error_key' => 'userspn_google_not_configured',
              'error_content' => esc_html(__('Google login is not properly configured.', 'userspn')),
            ]);
            exit;
          }

          // Generar URL de autorización de Google
          // Usar admin-ajax.php como redirect URI (más compatible que REST API)
          $redirect_uri = admin_url('admin-ajax.php?action=userspn_google_callback');
          $state = 'userspn_google_login';
          $scope = 'email profile';

          $auth_url = add_query_arg([
            'client_id' => $client_id,
            'redirect_uri' => $redirect_uri,
            'response_type' => 'code',
            'scope' => $scope,
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account',
          ], 'https://accounts.google.com/o/oauth2/v2/auth');

          echo wp_json_encode([
            'success' => true,
            'auth_url' => $auth_url,
          ]);
          exit;

        case 'userspn_facebook_auth_url':
          // Verificar que Facebook Login esté habilitado
          if (get_option('userspn_facebook_login_enabled') != 'on') {
            echo wp_json_encode([
              'error_key' => 'userspn_facebook_disabled',
              'error_content' => esc_html(__('Facebook login is not available.', 'userspn')),
            ]);
            exit;
          }

          $app_id = get_option('userspn_facebook_app_id');
          $app_secret = get_option('userspn_facebook_app_secret');

          if (empty($app_id) || empty($app_secret)) {
            echo wp_json_encode([
              'error_key' => 'userspn_facebook_not_configured',
              'error_content' => esc_html(__('Facebook login is not properly configured.', 'userspn')),
            ]);
            exit;
          }

          // Generar URL de autorización de Facebook
          $redirect_uri = admin_url('admin-ajax.php?action=userspn_facebook_callback');
          $state = 'userspn_facebook_login';
          $scope = 'email,public_profile';

          $auth_url = add_query_arg([
            'client_id' => $app_id,
            'redirect_uri' => $redirect_uri,
            'state' => $state,
            'scope' => $scope,
            'response_type' => 'code',
          ], 'https://www.facebook.com/v18.0/dialog/oauth');

          echo wp_json_encode([
            'success' => true,
            'auth_url' => $auth_url,
          ]);
          exit;

        case 'userspn_github_auth_url':
          // Verificar que GitHub Login esté habilitado
          if (get_option('userspn_github_login_enabled') != 'on') {
            echo wp_json_encode([
              'error_key' => 'userspn_github_disabled',
              'error_content' => esc_html(__('GitHub login is not available.', 'userspn')),
            ]);
            exit;
          }

          $client_id = get_option('userspn_github_client_id');
          $client_secret = get_option('userspn_github_client_secret');

          if (empty($client_id) || empty($client_secret)) {
            echo wp_json_encode([
              'error_key' => 'userspn_github_not_configured',
              'error_content' => esc_html(__('GitHub login is not properly configured.', 'userspn')),
            ]);
            exit;
          }

          // Generar URL de autorización de GitHub
          $redirect_uri = admin_url('admin-ajax.php?action=userspn_github_callback');
          $state = 'userspn_github_login';
          $scope = 'user:email';

          $auth_url = add_query_arg([
            'client_id' => $client_id,
            'redirect_uri' => $redirect_uri,
            'state' => $state,
            'scope' => $scope,
          ], 'https://github.com/login/oauth/authorize');

          echo wp_json_encode([
            'success' => true,
            'auth_url' => $auth_url,
          ]);
          exit;

        case 'userspn_apple_auth_url':
          // Verificar que Apple Login esté habilitado
          if (get_option('userspn_apple_login_enabled') != 'on') {
            echo wp_json_encode([
              'error_key' => 'userspn_apple_disabled',
              'error_content' => esc_html(__('Apple login is not available.', 'userspn')),
            ]);
            exit;
          }

          $service_id = get_option('userspn_apple_service_id');
          $team_id = get_option('userspn_apple_team_id');
          $key_id = get_option('userspn_apple_key_id');
          $private_key = get_option('userspn_apple_private_key');

          if (empty($service_id) || empty($team_id) || empty($key_id) || empty($private_key)) {
            echo wp_json_encode([
              'error_key' => 'userspn_apple_not_configured',
              'error_content' => esc_html(__('Apple login is not properly configured.', 'userspn')),
            ]);
            exit;
          }

          // Generar URL de autorización de Apple
          $redirect_uri = admin_url('admin-ajax.php?action=userspn_apple_callback');
          $state = 'userspn_apple_login';
          $scope = 'email name';

          $auth_url = add_query_arg([
            'client_id' => $service_id,
            'redirect_uri' => $redirect_uri,
            'response_type' => 'code',
            'state' => $state,
            'scope' => $scope,
            'response_mode' => 'form_post',
          ], 'https://appleid.apple.com/auth/authorize');

          echo wp_json_encode([
            'success' => true,
            'auth_url' => $auth_url,
          ]);
          exit;

        case 'userspn_google_callback':
          // Verificar que Google Login esté habilitado
          if (get_option('userspn_google_login_enabled') != 'on') {
            echo wp_json_encode([
              'error_key' => 'userspn_google_disabled',
              'error_content' => esc_html(__('Google login is not available.', 'userspn')),
            ]);
            exit;
          }

          $client_id = get_option('userspn_google_client_id');
          $client_secret = get_option('userspn_google_client_secret');

          if (empty($client_id) || empty($client_secret)) {
            echo wp_json_encode([
              'error_key' => 'userspn_google_not_configured',
              'error_content' => esc_html(__('Google login is not properly configured.', 'userspn')),
            ]);
            exit;
          }

          // Obtener código de autorización
          $code = isset($_POST['code']) ? sanitize_text_field(wp_unslash($_POST['code'])) : '';

          if (empty($code)) {
            echo wp_json_encode([
              'error_key' => 'userspn_google_no_code',
              'error_content' => esc_html(__('No authorization code received.', 'userspn')),
            ]);
            exit;
          }

          // Intercambiar código por token de acceso
          $redirect_uri = home_url('/wp-json/userspn/v1/google-callback');

          $token_response = wp_remote_post('https://oauth2.googleapis.com/token', [
            'body' => [
              'code' => $code,
              'client_id' => $client_id,
              'client_secret' => $client_secret,
              'redirect_uri' => $redirect_uri,
              'grant_type' => 'authorization_code',
            ],
            'timeout' => 30,
          ]);

          if (is_wp_error($token_response)) {
            echo wp_json_encode([
              'error_key' => 'userspn_google_token_error',
              'error_content' => esc_html(__('Error connecting to Google.', 'userspn')),
            ]);
            exit;
          }

          $token_data = json_decode(wp_remote_retrieve_body($token_response), true);

          if (empty($token_data['access_token'])) {
            echo wp_json_encode([
              'error_key' => 'userspn_google_no_token',
              'error_content' => esc_html(__('Error obtaining access token.', 'userspn')),
            ]);
            exit;
          }

          // Obtener información del usuario de Google
          $user_info_response = wp_remote_get('https://www.googleapis.com/oauth2/v2/userinfo', [
            'headers' => [
              'Authorization' => 'Bearer ' . $token_data['access_token'],
            ],
            'timeout' => 30,
          ]);

          if (is_wp_error($user_info_response)) {
            echo wp_json_encode([
              'error_key' => 'userspn_google_userinfo_error',
              'error_content' => esc_html(__('Error getting user information.', 'userspn')),
            ]);
            exit;
          }

          $user_info = json_decode(wp_remote_retrieve_body($user_info_response), true);

          if (empty($user_info['email'])) {
            echo wp_json_encode([
              'error_key' => 'userspn_google_no_email',
              'error_content' => esc_html(__('No email address received from Google.', 'userspn')),
            ]);
            exit;
          }

          $google_email = sanitize_email($user_info['email']);
          $google_id = isset($user_info['id']) ? sanitize_text_field($user_info['id']) : '';
          $google_name = isset($user_info['name']) ? sanitize_text_field($user_info['name']) : '';
          $google_picture = isset($user_info['picture']) ? esc_url_raw($user_info['picture']) : '';

          // Verificar si el usuario ya existe por email
          $user = get_user_by('email', $google_email);

          if ($user) {
            // Usuario existe - hacer login
            // Guardar/actualizar Google ID si no existe
            if (empty(get_user_meta($user->ID, 'userspn_google_id', true))) {
              update_user_meta($user->ID, 'userspn_google_id', $google_id);
            }

            // Actualizar última conexión con Google
            update_user_meta($user->ID, 'userspn_google_last_login', current_time('timestamp'));

            // Login
            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID, true);
            do_action('wp_login', $user->user_login, $user);

            // Resetear flag de advertencia de inactividad
            update_user_meta($user->ID, 'userspn_inactive_warning_sent', 'off');

            echo wp_json_encode([
              'success' => true,
              'message' => esc_html(__('Login successful!', 'userspn')),
              'redirect_url' => home_url(),
            ]);
            exit;
          } else {
            // Usuario no existe - crear nuevo usuario
            $username = sanitize_user(str_replace('@', '_', $google_email));

            // Asegurar que el username sea único
            $base_username = $username;
            $counter = 1;
            while (username_exists($username)) {
              $username = $base_username . '_' . $counter;
              $counter++;
            }

            // Generar password aleatorio
            $password = wp_generate_password(16, true, true);

            // Crear usuario
            $user_id = wp_create_user($username, $password, $google_email);

            if (is_wp_error($user_id)) {
              echo wp_json_encode([
                'error_key' => 'userspn_user_creation_error',
                'error_content' => esc_html(__('Error creating user account.', 'userspn')),
              ]);
              exit;
            }

            // Actualizar datos del usuario
            if (!empty($google_name)) {
              $name_parts = explode(' ', $google_name, 2);
              wp_update_user([
                'ID' => $user_id,
                'first_name' => $name_parts[0],
                'last_name' => isset($name_parts[1]) ? $name_parts[1] : '',
                'display_name' => $google_name,
              ]);
            }

            // Guardar Google ID y metadatos
            update_user_meta($user_id, 'userspn_google_id', $google_id);
            update_user_meta($user_id, 'userspn_google_last_login', current_time('timestamp'));
            update_user_meta($user_id, 'userspn_registration_method', 'google');

            // Guardar foto de perfil de Google si está disponible
            if (!empty($google_picture)) {
              update_user_meta($user_id, 'userspn_google_picture', $google_picture);
            }

            // Ejecutar hook de registro (esto ejecutará userspn_user_register)
            do_action('user_register', $user_id);

            // Login automático
            wp_set_current_user($user_id);
            wp_set_auth_cookie($user_id, true);
            do_action('wp_login', $username, get_user_by('id', $user_id));

            echo wp_json_encode([
              'success' => true,
              'message' => esc_html(__('Account created successfully!', 'userspn')),
              'redirect_url' => home_url(),
            ]);
            exit;
          }
      }

      echo wp_json_encode(['error_key' => '', ]);exit;
    }
  }
}