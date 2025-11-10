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
                        
                        // Additionally write to the original (non-prefixed) key when value is an array
                        // This keeps legacy/unprefixed meta in sync for select-multiple and similar fields
                        if (!empty($original_key) && strpos((string)$original_key, 'userspn_') !== 0 && is_array($userspn_value)) {
                          update_user_meta($user_id, $original_key, $userspn_value);
                        }
                      }
                    }
                  }

                  do_action('userspn_form_save', $user_id, $userspn_key_value, $userspn_form_type, $userspn_form_subtype);
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

                  do_action('userspn_form_save', 0, $userspn_key_value, $userspn_form_type, $userspn_form_subtype);
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
          $plugin_user = new USERSPN_Functions_User();

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
                echo 'userspn_profile_create_security_error';exit;
              }

              $userspn_login = sanitize_title(substr($userspn_email, 0, strpos($userspn_email, '@')) . '-' . bin2hex(openssl_random_pseudo_bytes(4)));
              $user_id = USERSPN_Functions_User::userspn_user_insert($userspn_login, $userspn_password, $userspn_email, '', '', $userspn_login, $userspn_login, $userspn_login, '', ['subscriber'], [
                ['userspn_secret_token' => bin2hex(openssl_random_pseudo_bytes(16))],
              ]);

              if ($user_id) {
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

          if (!empty($userspn_email)) {
            if (email_exists($userspn_email)) {
              $user_id = get_user_by('email', $userspn_email)->ID;
            } else {
              $userspn_login = sanitize_title(substr($userspn_email, 0, strpos($userspn_email, '@')) . '-' . bin2hex(openssl_random_pseudo_bytes(4)));
              $userspn_password = bin2hex(openssl_random_pseudo_bytes(12));
              $user_id = USERSPN_Functions_User::userspn_user_insert($userspn_login, $userspn_password, $userspn_email, '', '', $userspn_login, $userspn_login, $userspn_login, '', ['userspn_newsletter_subscriber'], []);
            }

            if ($user_id) {
              $user_object = new WP_User($user_id);
              if (!in_array('userspn_newsletter_subscriber', (array) $user_object->roles, true)) {
                $user_object->add_role('userspn_newsletter_subscriber');
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
      }

      echo wp_json_encode(['error_key' => '', ]);exit;
    }
  }
}