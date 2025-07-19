<?php
/**
 * Define the users management functionality.
 *
 * Loads and defines the users management files for this plugin so that it is ready for user creation, edition or removal.
 *  
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    userspn
 * @subpackage userspn/includes
 * @author     Padres en la Nube <info@padresenlanube.com>
 */
class USERSPN_Functions_User {
  public static function userspn_user_is_admin($user_id) {
    // USERSPN_Functions_User::userspn_user_is_admin($user_id)
    return user_can($user_id, 'administrator');
  }

  public static function userspn_user_get_name($user_id) {
    // USERSPN_Functions_User::userspn_user_get_name($user_id)
    if (!empty($user_id)) {
      $user_info = get_userdata($user_id);

      if (!empty($user_info->first_name) && !empty($user_info->last_name)) {
        return $user_info->first_name . ' ' . $user_info->last_name;
      }elseif (!empty($user_info->first_name)) {
        return $user_info->first_name;
      }else if (!empty($user_info->last_name)) {
        return $user_info->last_name;
      }else if (!empty($user_info->user_nicename)){
        return $user_info->user_nicename;
      }else if (!empty($user_info->user_login)){
        return $user_info->user_login;
      } else {
        return $user_info->user_email;
      }
    }
  }

  public static function userspn_user_get_age($user_id) {
    // USERSPN_Functions_User::userspn_user_get_age($user_id)
    $timestamp = get_user_meta($user_id, 'userspn_child_birthdate', true);

    if (!empty($timestamp) && is_string($timestamp)) {
      $timestamp = strtotime($timestamp);

      $year = gmdate('Y', $timestamp);
      $age = gmdate('Y') - $year;

      if(strtotime('+' . $age . ' years', $timestamp) > time()) {
        $age--;
      }

      return $age;
    }

    return false;
  }

  public static function userspn_user_insert($userspn_user_login, $userspn_user_password, $userspn_user_email = '', $userspn_first_name = '', $userspn_last_name = '', $userspn_display_name = '', $userspn_user_nicename = '', $userspn_user_nickname = '', $userspn_user_description = '', $userspn_user_role = [], $userspn_array_usermeta = [/*['userspn_key' => 'userspn_value'], */]) {
    /* $this->userspn_user_insert($userspn_user_login, $userspn_user_password, $userspn_user_email = '', $userspn_first_name = '', $userspn_last_name = '', $userspn_display_name = '', $userspn_user_nicename = '', $userspn_user_nickname = '', $userspn_user_description = '', $userspn_user_role = [], $userspn_array_usermeta = [['userspn_key' => 'userspn_value'], ],); */

    $userspn_user_array = [
      'first_name' => $userspn_first_name,
      'last_name' => $userspn_last_name,
      'display_name' => $userspn_display_name,
      'user_nicename' => $userspn_user_nicename,
      'nickname' => $userspn_user_nickname,
      'description' => $userspn_user_description,
    ];

    if (!empty($userspn_user_email)) {
      if (!email_exists($userspn_user_email)) {
        if (username_exists($userspn_user_login)) {
          $user_id = wp_create_user($userspn_user_email, $userspn_user_password, $userspn_user_email);
        } else {
          $user_id = wp_create_user($userspn_user_login, $userspn_user_password, $userspn_user_email);
        }
      } else {
        $user_id = get_user_by('email', $userspn_user_email)->ID;
      }
    } else {
      if (!username_exists($userspn_user_login)) {
        $user_id = wp_create_user($userspn_user_login, $userspn_user_password);
      } else {
        $user_id = get_user_by('login', $userspn_user_login)->ID;
      }
    }

    if ($user_id && !is_wp_error($user_id)) {
      wp_update_user(array_merge(['ID' => $user_id], $userspn_user_array));
    } else {
      return false;
    }

    $user = new WP_User($user_id);
    if (!empty($userspn_user_role)) {
      foreach ($userspn_user_role as $new_role) {
        $user->add_role($new_role);
      }
    }

    if (!empty($userspn_array_usermeta)) {
      foreach ($userspn_array_usermeta as $userspn_usermeta) {
        foreach ($userspn_usermeta as $meta_key => $meta_value) {
          if ((!empty($meta_value) || !empty(get_user_meta($user_id, $meta_key, true))) && !is_null($meta_value)) {
            update_user_meta($user_id, $meta_key, $meta_value);
          }
        }
      }
    }

    return $user_id;
  }

  public function userspn_user_wp_login($login) {
    $user = get_user_by('login', $login);
    $user_id = $user->ID;
    $current_login_time = get_user_meta($user_id, 'userspn_user_current_login', true);

    if(!empty($current_login_time)){
      update_user_meta($user_id, 'userspn_user_last_login', $current_login_time);
      update_user_meta($user_id, 'userspn_user_current_login', current_time('timestamp'));
    }else {
      update_user_meta($user_id, 'userspn_user_current_login', current_time('timestamp'));
      update_user_meta($user_id, 'userspn_user_last_login', current_time('timestamp'));
    }
  }

  public function userspn_profile_fields($user){
    $user_id = $user->ID;

    $userspn_profile_fields_nonce = [];
    $userspn_profile_fields_nonce['userspn_ajax_nopriv_nonce'] = [
      'id' => 'userspn_ajax_nopriv_nonce',
      'class' => 'userspn-input userspn-width-100-percent',
      'input' => 'input',
      'type' => 'nonce',
    ];
    
    if(get_option('userspn_user_register_fields_dashboard') == 'on'){
      $userspn_user_register_fields = self::userspn_user_register_get_fields([]);
      ?>
        <?php if (!empty($userspn_user_register_fields)): ?>
          <h3><?php esc_html_e('User management - PN Extra profile fields', 'userspn'); ?></h3>

          <?php foreach ($userspn_user_register_fields as $profile_field): ?>
            <table class="form-table">
              <tr>
                <th>
                  <?php if (array_key_exists('label', $profile_field)): ?>
                    <label for="<?php echo esc_attr($profile_field['id']); ?>"><?php echo esc_html($profile_field['label']); ?></label>
                  <?php endif ?>
                </th>
                <td>
                  <?php USERSPN_Forms::userspn_input_builder($profile_field, 'user', $user_id); ?>
                </td>
              </tr>
            </table>
          <?php endforeach ?>

          <?php foreach ($userspn_profile_fields_nonce as $nonce_field): ?>
            <?php USERSPN_Forms::userspn_input_builder($nonce_field, 'user', $user_id); ?>
          <?php endforeach ?>
        <?php endif ?>
      <?php
    }

    if (get_option('userspn_auto_login') == 'on' && current_user_can('administrator')) {
      ?>
        <h3><?php esc_html_e('Auto-login tool', 'userspn'); ?></h3>
        <p><?php esc_html_e('This option will allow you to login the platform as the current user profile. This won´t work for administrator profiles, but any other contact all across the website.', 'userspn'); ?> <?php esc_html_e('As you login with a new account, you will lose access to yours, so it´s better if you open this link in a private window or other browser.', 'userspn'); ?></p>
        <div class="userspn-text-align-center">
          <a class="userspn-btn userspn-btn-mini" target="_blank" href="<?php echo esc_url(self::userspn_link_magic($user_id, home_url())); ?>"><?php esc_html_e('Login as', 'userspn'); ?> <?php echo esc_html(self::userspn_user_get_name($user_id)); ?></a>
        </div>
      <?php
    }
  }

  public function userspn_link_magic($user_id, $base_url) {
     // $this->userspn_link_magic($user_id, home_url()); 
    $user_info = get_userdata($user_id);
    $user_login = $user_info->user_login;
    $secret_token = get_user_meta($user_id, 'userspn_secret_token', true);

    $url = add_query_arg([
        'userspn_auto_login' => 1,
        'userspn_user_id' => $user_id,
        'userspn_login_username' => $user_login,
        'userspn_secret_token' => $secret_token,
        'action' => 'userspn_nonce_action',
        'user'   => $user_id,
      ],
      $base_url,
    );

    $action_user_url = wp_nonce_url($url, 'userspn_nonce_action', 'userspn_nonce');

    return $action_user_url;
  }

  public function userspn_auto_login() {
    // Solo procesar si tenemos los parámetros necesarios
    if (!isset($_GET['userspn_auto_login']) || get_option('userspn_auto_login') !== 'on') {
      return;
    }

    $login_username = !empty($_GET['userspn_login_username']) ? sanitize_text_field(wp_unslash($_GET['userspn_login_username'])) : '';
    $user_id = !empty($_GET['userspn_user_id']) ? intval($_GET['userspn_user_id']) : 0;
    $secret_token = get_user_meta($user_id, 'userspn_secret_token', true);
    $userspn_secret_token = !empty($_GET['userspn_secret_token']) ? sanitize_text_field(wp_unslash($_GET['userspn_secret_token'])) : '';
    
    // Validación de seguridad usando el token secreto
    if (empty($secret_token) || $secret_token !== $userspn_secret_token || user_can($user_id, 'administrator')) {
      return;
    }

    // Realizar el autologin
    wp_set_current_user($user_id, $login_username);
    wp_set_auth_cookie($user_id);
    
    // Redireccionar sin parámetros
    $request_uri = !empty($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : esc_url(home_url());
    wp_redirect(strtok($request_uri, '?'));
    exit;
  }

  public function userspn_profile_fields_validation() {
    $user_id = get_current_user_id();
    $userspn_fields = self::userspn_user_register_get_fields([]);
    $fields_required_pending = [];
  
    if (!is_admin() && !empty($userspn_fields)) {
      foreach ($userspn_fields as $field) {
        if (!empty($field['required']) && $field['required'] && empty(get_user_meta($user_id, $field['id'], true))) {
          $fields_required_pending[] = $field;
        }
      }
    }

    ?>
      <?php if (!empty($fields_required_pending)): ?>
        <?php if (!wp_script_is('userspn-profile-fields-validation', 'enqueued')): ?>
          <?php wp_enqueue_script('userspn-profile-fields-validation', USERSPN_URL . 'assets/js/userspn-profile-fields-validation.js', ['jquery'], USERSPN_VERSION, false, ['in_footer' => true, 'strategy' => 'defer']); ?>
        <?php endif ?>
      <?php endif ?>
    <?php
  }

  public function userspn_user_register($user_id) {
    update_user_meta($user_id, 'userspn_secret_token', bin2hex(openssl_random_pseudo_bytes(16)));
    update_user_meta($user_id, 'userspn_notifications', 'on');

    if (class_exists('Polylang')) {
      update_user_meta($user_id, 'userspn_lang', pll_current_language());
    }
  }

  /**
   * Log email events to database
   * 
   * @param string $event_type Type of event (email_sent_success, email_send_failed, etc.)
   * @param int $user_id User ID (0 for system events)
   * @param string $message Log message
   * @param array $additional_data Additional data to store
   */
  private function userspn_log_email_event($event_type, $user_id, $message, $additional_data = []) {
    $logs = get_option('userspn_email_logs', []);
    
    $log_entry = [
      'timestamp' => current_time('timestamp'),
      'event_type' => $event_type,
      'user_id' => $user_id,
      'message' => $message,
      'additional_data' => $additional_data
    ];
    
    // Add to logs array (keep only last 1000 entries)
    $logs[] = $log_entry;
    if (count($logs) > 1000) {
      $logs = array_slice($logs, -1000);
    }
    
    update_option('userspn_email_logs', $logs, false);
  }

  /**
   * Get email logs with optional filtering
   * 
   * @param string $event_type Optional event type filter
   * @param int $limit Number of entries to return (default 50)
   * @return array Log entries
   */
  public function userspn_get_email_logs($event_type = '', $limit = 50) {
    $logs = get_option('userspn_email_logs', []);
    
    if (!empty($event_type)) {
      $logs = array_filter($logs, function($log) use ($event_type) {
        return $log['event_type'] === $event_type;
      });
    }
    
    // Sort by timestamp (newest first) and limit
    usort($logs, function($a, $b) {
      return $b['timestamp'] - $a['timestamp'];
    });
    
    return array_slice($logs, 0, $limit);
  }

  /**
   * Clear email logs
   */
  public function userspn_clear_email_logs() {
    delete_option('userspn_email_logs');
  }

  public function userspn_send_newsletter_email($user_id) {
    $user_info = get_userdata($user_id);
    
    if (!$user_info) {
      return false;
    }
    
    $user_roles = $user_info->roles;

    if (class_exists('MAILPN') && in_array('userspn_newsletter_subscriber', $user_roles)) {
      $userspn_mailing = new USERSPN_Mailing();
      $userspn_emails_newsletter = $userspn_mailing->userspn_get_email_newsletter_welcome($user_id);
      if (!empty($userspn_emails_newsletter)) {
        foreach ($userspn_emails_newsletter as $mail_id) {
          do_shortcode('[mailpn-sender mailpn_type="newsletter_welcome" mailpn_user_to="' . $user_id . '" mailpn_subject="' . get_the_title($mail_id) . '" mailpn_id="' . $mail_id . '"]');
        }

        return true;
      }
    }
    
    return false;
  }

  public function userspn_user_register_fields($atts) {
    // echo do_shortcode('[userspn-user-register-fields]');
    $a = extract(shortcode_atts([
      'userspn_form_type' => 'option',
      'userspn_meta' => 'userspn_forum_form_fields',
      'post_id' => 0,
    ], $atts));

    $post_id = get_the_ID();
    $userspn_user_register_fields = ($userspn_form_type == 'option') ? get_option('userspn_user_register_fields') : get_post_meta($post_id, $userspn_meta, true);

    ob_start();
    ?>
      <div class="userspn-user-register-fields-wrapper" data-userspn-form-type="<?php echo esc_attr($userspn_form_type); ?>" data-userspn-meta="<?php echo esc_attr($userspn_meta); ?>">        
        <div class="userspn-display-inline-table <?php echo ($userspn_form_type == 'option') ? 'userspn-width-100-percent' : 'userspn-width-50-percent'; ?>  userspn-tablet-display-block userspn-tablet-width-100-percent">
          <div class="userspn-user-register-fields-edition userspn-mb-50">
            <?php if (!empty($userspn_user_register_fields)): ?>
              <?php foreach ($userspn_user_register_fields as $userspn_user_register_field): ?>
                <?php USERSPN_Forms::userspn_input_builder($userspn_user_register_field, 'user'); ?>
              <?php endforeach ?>
            <?php endif ?>
          </div>

          <div class="userspn-text-align-right">
            <div class="userspn-display-table userspn-width-100-percent">
              <div class="userspn-display-inline-table userspn-width-50-percent userspn-tablet-display-block userspn-tablet-width-100-percent userspn-text-align-center">
                <a href="#" class="userspn-popup" data-userspn-popup-id="userspn-user-register-fields-viewer"><?php esc_html_e('Current form', 'userspn'); ?></a>
              </div>
              <div class="userspn-display-inline-table userspn-width-50-percent userspn-tablet-display-block userspn-tablet-width-100-percent userspn-text-align-center">
                <a href="#" class="userspn-btn userspn-input-editor-builder-btn-add userspn-pl-50 userspn-pr-50" data-userspn-meta="<?php echo esc_attr($userspn_meta); ?>"><?php esc_html_e('Add new field', 'userspn'); ?></a>
              </div>
            </div>
          </div>
        </div>

        <div id="userspn-user-register-fields-viewer" class="userspn-user-register-fields userspn-display-none-soft">
          <?php if (!empty($userspn_user_register_fields)): ?>
            <?php foreach ($userspn_user_register_fields as $userspn_user_register_field): ?>
              <?php USERSPN_Forms::userspn_input_wrapper_builder($userspn_user_register_field, 'user', 0, 0, 'full'); ?>
            <?php endforeach ?>
          <?php else: ?>
          <p class="userspn-user-register-fields-empty"><?php esc_html_e('You have not registered any field yet', 'userspn'); ?></p>
          <?php endif ?>
        </div>
      </div>
    <?php
    $userspn_return_string = ob_get_contents(); 
    ob_end_clean(); 
    return $userspn_return_string;
  }

  public function userspn_user_register_get_fields($base_fields) {
    $userspn_user_register_base_fields = [];

    if (get_option('userspn_user_name') == 'on') {
      $userspn_user_register_base_fields['first_name'] = [
        'id' => 'first_name',
        'class' => 'userspn-input userspn-width-100-percent',
        'input' => 'input',
        'type' => 'text',
        'required' => (get_option('userspn_user_name_compulsory')) ? true : false,
        'label' => __('Name', 'userspn'),
        'placeholder' => __('Name', 'userspn'),
      ];
      $userspn_user_register_base_fields['last_name'] = [
        'id' => 'last_name',
        'class' => 'userspn-input userspn-width-100-percent',
        'input' => 'input',
        'type' => 'text',
        'required' => (get_option('userspn_user_surname_compulsory')) ? true : false,
        'label' => __('Surname', 'userspn'),
        'placeholder' => __('Surname', 'userspn'),
      ];
    }

    $userspn_user_register_fields_filtered = apply_filters('userspn_register_fields', $base_fields);
    $userspn_user_option_register_fields = !empty(get_option('userspn_user_register_fields')) ? get_option('userspn_user_register_fields') : [];

    $userspn_user_register_fields = array_merge($userspn_user_register_base_fields, $userspn_user_register_fields_filtered, $userspn_user_option_register_fields);

    return $userspn_user_register_fields;
  }

  public function userspn_profile() {
    // echo do_shortcode('[userspn-profile]');
    $user_id = get_current_user_id();
    $functions_attachment = new USERSPN_Functions_Attachment();

    ob_start();
    ?>
      <?php if (!is_admin()): ?>
        <div class="userspn-profile">
          <?php if (is_user_logged_in()): ?>
            <a href="#" class="userspn-text-align-right userspn-profile-popup-btn"><?php echo do_shortcode('[userspn-get-avatar user_id="' . $user_id . '" size="50"]'); ?></a>

            <div id="userspn-profile-popup" class="userspn-popup userspn-popup-size-medium userspn-display-none-soft">
              <div class="userspn-popup-content">
                <div class="userspn-profile-wrapper" data-user-id="<?php echo esc_attr($user_id); ?>">
                  <div class="userspn-tabs-wrapper">
                    <div class="userspn-tabs">
                      <div class="userspn-tab-links active" data-userspn-id="userspn-tab-edit"><?php esc_html_e('Profile', 'userspn'); ?></div>

                      <?php if (get_option('userspn_user_image') == 'on'): ?>
                        <div class="userspn-tab-links" data-userspn-id="userspn-tab-image"><?php esc_html_e('Image', 'userspn'); ?></div>
                      <?php endif ?>

                      <?php if (get_option('userspn_user_notifications') == 'on'): ?>
                        <?php if (current_user_can('administrator') || class_exists('MAILPN')): ?>
                          <div class="userspn-tab-links" data-userspn-id="userspn-tab-notifications"><?php esc_html_e('Notifications', 'userspn'); ?></div>
                        <?php endif ?>
                      <?php endif ?>

                      <?php if ($functions_attachment->userspn_user_files_allowed($user_id)): ?>
                        <div class="userspn-tab-links" data-userspn-id="userspn-tab-files"><?php esc_html_e('Files', 'userspn'); ?></div>
                      <?php endif ?>

                      <?php if (get_option('userspn_user_advanced') == 'on'): ?>
                        <div class="userspn-tab-links" data-userspn-id="userspn-tab-advanced"><?php esc_html_e('Advanced', 'userspn'); ?></div>
                      <?php endif ?>
                    </div>

                    <div id="userspn-tab-edit" class="userspn-tab-content">
                      <?php echo do_shortcode('[userspn-profile-edit]'); ?>
                    </div>

                    <?php if (get_option('userspn_user_image') == 'on'): ?>
                      <div id="userspn-tab-image" class="userspn-tab-content userspn-display-none">
                        <?php echo do_shortcode('[userspn-profile-image]'); ?>
                      </div>
                    <?php endif ?>

                    <?php if (get_option('userspn_user_notifications') == 'on'): ?>
                      <?php if (class_exists('MAILPN')): ?>
                        <div id="userspn-tab-notifications" class="userspn-tab-content userspn-display-none">
                          <?php echo do_shortcode('[userspn-notifications]'); ?>
                        </div>
                      <?php elseif (current_user_can('administrator')): ?>
                        <div id="userspn-tab-notifications" class="userspn-tab-content userspn-display-none">
                          <div class="userspn-mt-30 userspn-p-10">
                            <p class="userspn-alert"><?php esc_html_e('Notifications are inactive. Please install and activate Mailing Manager - PN to allow integrated notifications in your platform.', 'userspn'); ?></p>
                            <a href="/wp-admin/plugin-install.php?s=mailpn&tab=search&type=term" class="userspn-btn userspn-btn-mini"><?php esc_html_e('Mailing Manager - PN', 'userspn'); ?></a>
                          </div>
                        </div>
                      <?php endif ?>
                    <?php endif ?>

                    <?php if ($functions_attachment->userspn_user_files_allowed($user_id)): ?>
                      <div id="userspn-tab-files" class="userspn-tab-content userspn-display-none">
                        <?php echo do_shortcode('[userspn-user-files]'); ?>
                      </div>
                    <?php endif ?>

                    <?php if (get_option('userspn_user_advanced') == 'on'): ?>
                      <div id="userspn-tab-advanced" class="userspn-tab-content userspn-display-none">
                        <div class="userspn-display-table userspn-width-100-percent userspn-mt-30 userspn-mb-30 userspn-p-10">
                          <div class="userspn-display-inline-table userspn-width-100-percent">
                            <div class="userspn-toggle-wrapper userspn-position-relative userspn-mb-10">
                              <a href="#" class="userspn-toggle userspn-width-100-percent userspn-text-decoration-none">
                                <div class="userspn-display-table userspn-width-100-percent">
                                  <div class="userspn-display-inline-table userspn-width-80-percent userspn-vertical-align-middle">
                                    <label class="userspn-display-block"><?php esc_html_e('Disconnect account', 'userspn'); ?></label>
                                  </div>

                                  <div class="userspn-display-inline-table userspn-width-10-percent userspn-vertical-align-middle userspn-text-align-right">
                                    <i class="material-icons-outlined userspn-cursor-pointer userspn-vertical-align-middle userspn-color-main-0">add</i>
                                  </div>
                                </div>
                              </a>

                              <div class="userspn-toggle-content userspn-display-none-soft">
                                <small><?php esc_html_e('Disconnect your user from the system. You will need to log in again.', 'userspn'); ?></small>
                              </div>
                            </div>
                          </div>

                          <div class="userspn-display-inline-table userspn-width-100-percent userspn-text-align-right">
                            <a href="<?php echo esc_url(wp_logout_url(get_permalink())); ?>" class="userspn-btn userspn-btn-transparent userspn-btn-mini"><?php esc_html_e('Log out', 'userspn'); ?></a>
                          </div>
                        </div>

                        <?php if (get_option('userspn_user_change_password') == 'on'): ?>
                          <?php echo do_shortcode('[userspn-user-change-password-btn]'); ?>
                        <?php endif ?>

                        <?php if (get_option('userspn_user_remove') == 'on'): ?>
                          <?php echo do_shortcode('[userspn-user-remove-form]'); ?>
                        <?php endif ?>
                      </div>
                    <?php endif ?>
                  </div>
                </div>
              </div>
            </div>
          <?php else: ?>
            <?php if (get_option('userspn_image_custom') == 'on'): ?>
              <?php $custom_ids = get_option('userspn_image_custom_ids'); ?>

              <?php if (!empty($custom_ids)): ?>
                <?php $custom_ids = explode(',', $custom_ids); ?>
                <a href="#" class="userspn-profile-popup-btn userspn-tooltip-left" title="<?php esc_html_e('Your profile', 'userspn'); ?>"><?php echo wp_get_attachment_image($custom_ids[array_rand($custom_ids)], [50, 50], false, ['class' => 'userspn-border-radius-50-percent userspn-m-10 userspn-display-block']); ?></a>
              <?php else: ?>
                <a href="#" class="userspn-profile-popup-btn userspn-tooltip-left" title="<?php esc_html_e('Your profile', 'userspn'); ?>"><i class="material-icons-outlined userspn-profile-icon userspn-color-main-0 userspn-vertical-align-middle userspn-font-size-50">account_circle</i></a>
              <?php endif ?>
            <?php else: ?>
              <a href="#" class="userspn-profile-popup-btn userspn-tooltip-left" aria-label="<?php esc_html_e('Your profile link button', 'userspn'); ?>" title="<?php esc_html_e('Your profile', 'userspn'); ?>"><i class="material-icons-outlined userspn-profile-icon userspn-color-main-0 userspn-vertical-align-middle userspn-font-size-50">account_circle</i></a>
            <?php endif ?>

            <div id="userspn-profile-popup" class="userspn-popup userspn-display-none-soft">
              <div class="userspn-popup-content">
                <div class="userspn-profile-wrapper">
                  <div class="userspn-tabs-wrapper">
                    <div class="userspn-tabs">
                      <div class="userspn-tab-links active" data-userspn-id="userspn-tab-login"><?php esc_html_e('Login', 'userspn'); ?></div>

                      <?php if (get_option('userspn_user_register') == 'on'): ?>
                        <div class="userspn-tab-links" data-userspn-id="userspn-tab-register"><?php esc_html_e('Register', 'userspn'); ?></div>
                      <?php endif ?>
                    </div>

                    <div id="userspn-tab-login" class="userspn-tab-content">
                      <?php echo do_shortcode('[userspn-login]'); ?>
                    </div>

                    <?php if (get_option('userspn_user_register') == 'on'): ?>
                      <div id="userspn-tab-register" class="userspn-tab-content userspn-display-none">
                        <?php echo do_shortcode('[userspn-user-register-form]'); ?>
                      </div>
                    <?php endif ?>
                  </div>
                </div>
              </div>
            </div>
          <?php endif ?>
        </div>
      <?php endif ?>
    <?php
    $userspn_return_string = ob_get_contents(); 
    ob_end_clean(); 
    return $userspn_return_string;
  }

  public function userspn_profile_edit() {
    /* echo do_shortcode('[userspn-profile-edit]'); */

    // add_filter('userspn_register_fields', [$this, 'userspn_userspn_register_fields'], 10, 2);
    // public function userspn_userspn_register_fields($register_fields) {
      // $register_fields['first_name'] = [
      //   'id' => 'first_name',
      //   'class' => 'userspn-input userspn-width-100-percent',
      //   'input' => 'input',
      //   'type' => 'text',
      //   'required' => true,
      //   'label' => esc_html(__('Name', 'userspn')),
      //   'placeholder' => esc_html(__('Name', 'userspn')),
      // ];
      // 
      // return $register_fields;
    // }

    $post_id = get_the_ID();
    $user_id = get_current_user_id();
    $userspn_fields = self::userspn_user_register_get_fields([]);
    $functions_attachment = new USERSPN_Functions_Attachment();
    
    // Calculate profile completion
    $total_fields = 0;
    $completed_fields = 0;
    $incomplete_fields = [];
    $all_fields = [];
    
    // Get profile completion fields configuration
    $page_ids = get_option('userspn_profile_completion_field_page_id', []);
    $meta_keys = get_option('userspn_profile_completion_field_meta_key', []);
    
    if (!empty($page_ids) && !empty($meta_keys)) {
      foreach ($page_ids as $index => $page_id) {
        if (!empty($meta_keys[$index])) {
          $meta_key = $meta_keys[$index];
          $total_fields++;
          
          $value = get_user_meta($user_id, $meta_key, true);
          $is_completed = !empty($value);
          
          if ($is_completed) {
            $completed_fields++;
          } else {
            $incomplete_fields[] = [
              'meta_key' => $meta_key,
              'page_id' => $page_id,
              'page_link' => !empty($page_id) ? get_permalink($page_id) : ''
            ];
          }
          
          // Store all fields for detailed view
          $all_fields[] = [
            'meta_key' => $meta_key,
            'page_id' => $page_id,
            'page_link' => !empty($page_id) ? get_permalink($page_id) : '',
            'page_title' => !empty($page_id) ? get_the_title($page_id) : '',
            'is_completed' => $is_completed
          ];
        }
      }
    }
    
    // Calculate completion percentage
    $completion_percentage = $total_fields > 0 ? round(($completed_fields / $total_fields) * 100) : 0;

    ob_start();
    ?>
      <?php if ($total_fields > 0 && get_option('userspn_profile_completion') == 'on' && $completion_percentage < 100): ?>
        <div class="userspn-profile-completion userspn-mt-20 userspn-mb-30">
          <div class="userspn-profile-completion-header userspn-mb-10">
            <div class="userspn-display-table userspn-width-100-percent">
              <div class="userspn-display-inline-table userspn-width-80-percent">
                <h4><?php esc_html_e('Profile Completion', 'userspn'); ?></h4>
              </div>
              <div class="userspn-display-inline-table userspn-width-20-percent userspn-text-align-right">
                <span class="userspn-profile-completion-percentage"><?php echo esc_html($completion_percentage); ?>%</span>
              </div>
            </div>
          </div>
          
          <div class="userspn-profile-completion-bar">
            <div class="userspn-profile-completion-progress" style="width: <?php echo esc_attr($completion_percentage); ?>%"></div>
          </div>
          
          <div class="userspn-profile-completion-stats userspn-mt-10">
            <?php
            /* translators: 1: Number of completed fields, 2: Total number of fields */
            ?>
            <small><?php echo sprintf(esc_html__('%1$d of %2$d fields completed', 'userspn'), esc_html($completed_fields), esc_html($total_fields)); ?></small>
          </div>
          
          <div class="userspn-toggle-wrapper userspn-position-relative userspn-mt-10">
            <a href="#" class="userspn-toggle userspn-width-100-percent userspn-text-decoration-none">
              <div class="userspn-display-table userspn-width-100-percent">
                <div class="userspn-display-inline-table userspn-width-80-percent userspn-vertical-align-middle">
                  <small class="userspn-display-block"><?php esc_html_e('View details', 'userspn'); ?></small>
                </div>
                <div class="userspn-display-inline-table userspn-width-20-percent userspn-vertical-align-middle userspn-text-align-right">
                  <i class="material-icons-outlined userspn-cursor-pointer userspn-vertical-align-middle">add</i>
                </div>
              </div>
            </a>
            
            <div class="userspn-toggle-content userspn-display-none-soft">
              <div class="userspn-profile-completion-details userspn-mt-10">
                <ul class="userspn-profile-completion-fields-list">
                  <?php foreach ($all_fields as $field): ?>
                    <li class="userspn-profile-completion-field-item userspn-list-style-none <?php echo $field['is_completed'] ? 'completed' : 'incomplete'; ?>">
                      <div class="userspn-display-table userspn-width-100-percent">
                        <div class="userspn-display-inline-table userspn-width-90-percent userspn-vertical-align-middle">
                          <?php if (!empty($field['page_link'])): ?>
                            <a class="userspn-text-decoration-none" href="<?php echo esc_url($field['page_link']); ?>">
                              <?php echo esc_html($field['page_title']); ?>
                            </a>
                          <?php else: ?>
                            <?php echo esc_html($field['meta_key']); ?>
                          <?php endif; ?>
                        </div>

                        <div class="userspn-display-inline-table userspn-width-10-percent userspn-vertical-align-middle userspn-text-align-right">
                          <a class="userspn-text-decoration-none" href="<?php echo esc_url($field['page_link']); ?>">
                            <i class="material-icons-outlined userspn-vertical-align-middle">
                              <?php echo $field['is_completed'] ? 'check' : 'radio_button_unchecked'; ?>
                            </i>
                          </a>
                        </div>
                      </div>
                      </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
          </div>
        </div>
      <?php endif ?>

      <form id="userspn-profile-edit" class="userspn-mt-30 userspn-form">
        <?php if (!empty($userspn_base_fields)): ?>
          <?php foreach ($userspn_base_fields as $userspn_base_field): ?>
            <?php USERSPN_Forms::userspn_input_wrapper_builder($userspn_base_field, 'user', $user_id, 0, 'full'); ?>
          <?php endforeach ?>
        <?php endif ?>

        <?php if (!empty($userspn_fields)): ?>
          <?php foreach ($userspn_fields as $userspn_user_register_field): ?>
            <?php USERSPN_Forms::userspn_input_wrapper_builder($userspn_user_register_field, 'user', esc_html($user_id), 0, 'full'); ?>
          <?php endforeach ?>
        <?php endif ?>

        <?php if (!empty($userspn_base_fields) || !empty($userspn_fields)): ?>
          <div class="userspn-text-align-right userspn-mt-30 userspn-mb-50">
            <input type="submit" value="<?php esc_html_e('Update profile', 'userspn'); ?>" name="userspn-profile-edit-btn" id="userspn-profile-edit-btn" class="userspn-btn" data-userspn-type="user" data-userspn-user-id="<?php echo esc_attr($user_id); ?>" data-userspn-post-id="<?php echo esc_attr($post_id); ?>"/><?php echo esc_html(USERSPN_Data::userspn_loader()); ?>
          </div>
        <?php else: ?>
          <p class="userspn-alert"><?php esc_html_e('There are no extra fields in the profile', 'userspn'); ?></p>
        <?php endif ?>
      </form>
    <?php
    $userspn_return_string = ob_get_contents(); 
    ob_end_clean(); 
    return $userspn_return_string;
  }

  public function userspn_profile_image() {
    ?>
    <form action="" method="post">
      <div class="userspn-mt-30 userspn-p-10">
        <label><?php esc_html_e('User avatar (recommended square images)', 'userspn'); ?></label>

        <div class="userspn-text-align-center userspn-mt-30 userspn-mb-30">
          <?php echo do_shortcode('[userspn-get-avatar user_id="' . get_current_user_id() . '" size="50"]'); ?>
        </div>

        <input type="file" id="userspn-user-file" class="width-100-percent border-radius-20">

        <div class="userspn-width-100-percent userspn-text-align-right userspn-mt-30 userspn-mb-30">
          <?php if (!wp_script_is('userspn-user-profile-image', 'enqueued')): ?>
            <?php wp_enqueue_script('userspn-user-profile-image', USERSPN_URL . 'assets/js/userspn-user-profile-image.js', ['jquery'], USERSPN_VERSION, false, ['in_footer' => true, 'strategy' => 'defer']); ?>
          <?php endif ?>

          <input type="submit" name="userspn-upload-files-btn" value="<?php esc_html_e('Upload avatar', 'userspn'); ?>" class="userspn-upload-files-btn userspn-btn" data-userspn-user-id="<?php echo esc_attr(get_current_user_id()); ?>"><?php echo esc_html(USERSPN_Data::userspn_loader()); ?>
        </div>
      </div>
    </form>
    <?php
  }

  public function userspn_validate_gravatar($user_id) {
    /* self::userspn_validate_gravatar($user_id); */
    $hash = md5(strtolower(trim(get_userdata($user_id)->user_email)));
    $uri = 'https://www.gravatar.com/avatar/' . $hash . '?d=404';
    $headers = @get_headers($uri);

    if (!preg_match("|200|", $headers[0])) {
        $has_valid_avatar = FALSE;
    } else {
        $has_valid_avatar = TRUE;
    }

    return $has_valid_avatar;
  }

  public function userspn_get_color_random($username) {
    /* self::userspn_get_color_random($username); */
    $col_min_avg = 64;
    $col_max_avg = 192;
    $col_step = 16;

    $range = $col_max_avg - $col_min_avg;
    $factor = $range / 256;
    $offset = $col_min_avg;

    $base_hash = substr(md5($username), 0, 6);
    $b_R = hexdec(substr($base_hash, 0, 2));
    $b_G = hexdec(substr($base_hash, 2, 2));
    $b_B = hexdec(substr($base_hash, 4, 2));

    $f_R = floor((floor($b_R * $factor) + $offset) / $col_step) * $col_step;
    $f_G = floor((floor($b_G * $factor) + $offset) / $col_step) * $col_step;
    $f_B = floor((floor($b_B * $factor) + $offset) / $col_step) * $col_step;

    return sprintf('#%02x%02x%02x', $f_R, $f_G, $f_B);
  }

  public function userspn_get_avatar($atts) {
    /* echo do_shortcode('[userspn-get-avatar user_id="1" size="36"]'); */
    $a = extract(shortcode_atts([
      'user_id' => get_current_user_id(),
      'size' => '36',
    ], $atts));

    ob_start();

    $avatar_params = apply_filters('userspn_get_avatar', [
      'user_identity' => self::userspn_user_get_name($user_id),
      'tooltip' => self::userspn_user_get_name($user_id),
      'html_extra' => '',
    ], $user_id);

    $random = bin2hex(openssl_random_pseudo_bytes(4));
    $user_image = get_user_meta($user_id, 'userspn_user_image', true);

    ?>
    <div class="userspn-avatar <?php echo (!empty($user_image) && !empty(wp_get_attachment_image_src($user_image, 'full')[0])) || self::userspn_validate_gravatar($user_id) ? 'userspn-avatar-image' : 'userspn-avatar-empty'; ?> userspn-position-relative userspn-display-inline-block userspn-vertical-align-middle userspn-tooltip" data-tooltip-content="#userspn-avatar-<?php echo esc_attr($random); ?>">
      <?php if (!empty($avatar_params['html_extra'])): ?>
        <?php echo wp_kses($avatar_params['html_extra'], USERSPN_KSES); ?>
      <?php endif ?>

      <?php if ((!empty($user_image) && !empty(wp_get_attachment_image_src($user_image, 'thumbnail'[0])))): ?>
        <img alt="<?php esc_html_e('Profile picture', 'userspn'); ?>" description="<?php esc_html_e('Profile picture of', 'userspn'); ?> <?php echo esc_attr(self::userspn_user_get_name($user_id)); ?>" src="<?php echo esc_url(wp_get_attachment_image_src($user_image, 'thumbnail')[0]); ?>" class="avatar avatar-<?php echo esc_attr($size); ?> photo userspn-border-radius-50-percent userspn-m-10" height="<?php echo esc_attr($size); ?>" width="<?php echo esc_attr($size); ?>">
      <?php elseif (self::userspn_validate_gravatar($user_id)): ?>
        <?php if (!empty(get_avatar($user_id))): ?>
          <?php echo get_avatar($user_id, $size, '', '', ['class' => 'userspn-border-radius-50-percent userspn-m-10']); ?>
        <?php else: ?>
          <div class="userspn-avatar-blank userspn-m-10 userspn-text-align-center userspn-display-inline-block userspn-vertical-align-middle" style="background-color:<?php echo esc_attr(self::userspn_get_color_random($avatar_params['user_identity'])); ?>;">
            <span class="userspn-avatar-first-char userspn-color-white userspn-font-size-25 userspn-line-height-50 userspn-text-transform-uppercase userspn-vertical-align-top"><?php echo esc_html($avatar_params['user_identity'][0]); ?></span>
          </div>
        <?php endif ?>
      <?php else: ?>
        <div class="userspn-avatar-blank userspn-m-10 userspn-text-align-center userspn-display-inline-block userspn-vertical-align-middle" style="background-color:<?php echo esc_attr(self::userspn_get_color_random($avatar_params['user_identity'])); ?>;">
          <span class="userspn-avatar-first-char userspn-color-white userspn-font-size-25 userspn-line-height-50 userspn-text-transform-uppercase userspn-vertical-align-top"><?php echo esc_html($avatar_params['user_identity'][0]); ?></span>
        </div>
      <?php endif ?>
    </div>

    <div class="tooltip_templates userspn-display-none-soft">
      <span id="userspn-avatar-<?php echo esc_attr($random); ?>">
        <?php echo esc_html($avatar_params['tooltip']); ?>
      </span>
    </div>
    <?php

    $userspn_return_string = ob_get_contents(); 
    ob_end_clean();
    return $userspn_return_string;
  }

  public function userspn_get_avatar_hook($avatar, $id_or_email, $size, $default, $alt) {
    if(is_numeric($id_or_email)) {
      $id = (int) $id_or_email;
      $user = get_user_by('id', $id);
    }elseif(is_object($id_or_email)) {
      if (! empty($id_or_email->user_id)) {
        $id = (int) $id_or_email->user_id;
        $user = get_user_by('id', $id);
      }
    }else{
      $user = get_user_by('email', $id_or_email); 
    }

    if(!empty($user) && $user && is_object($user)) {
      if($user->data->ID == '1') {
        if (!empty(get_user_meta($user->ID, 'userspn_user_image', true))) {
          $avatar = wp_get_attachment_image(get_user_meta($user->ID, 'userspn_user_image', true), [$size, $size], false, ['class' => 'userspn-border-radius-50-percent userspn-m-10 userspn-display-block']);
        }
      }
    }

    return $avatar;
  }

  public function userspn_user_remove_form() {
    // echo do_shortcode('[userspn-user-remove-form]');
    $user_id = get_current_user_id();

    ob_start();
    ?>
      <div class="userspn-display-table userspn-width-100-percent userspn-mt-30 userspn-mb-30 userspn-p-10">
        <div class="userspn-display-inline-table userspn-width-100-percent">
          <div class="userspn-toggle-wrapper userspn-position-relative userspn-mb-10">
            <a href="#" class="userspn-toggle userspn-width-100-percent userspn-text-decoration-none">
              <div class="userspn-display-table userspn-width-100-percent">
                <div class="userspn-display-inline-table userspn-width-80-percent userspn-vertical-align-middle">
                  <label class="userspn-display-block"><?php esc_html_e('Remove user', 'userspn'); ?></label>
                </div>

                <div class="userspn-display-inline-table userspn-width-10-percent userspn-vertical-align-middle userspn-text-align-right">
                  <i class="material-icons-outlined userspn-cursor-pointer userspn-vertical-align-middle userspn-color-main-0">add</i>
                </div>
              </div>
            </a>

            <div class="userspn-toggle-content userspn-display-none-soft">
              <small><?php esc_html_e('Remove your user from the system. This action cannot be undone.', 'userspn'); ?></small>
            </div>
          </div>
        </div>

        <div class="userspn-display-inline-table userspn-width-100-percent userspn-text-align-right">
          <a href="#" class="userspn-popup-open userspn-btn userspn-btn-transparent userspn-btn-mini" data-userspn-popup-id="userspn-user-remove-popup"><?php esc_html_e('Remove user', 'userspn'); ?></a>
        </div>

        <div id="userspn-user-remove-popup" class="userspn-popup userspn-display-none-soft">
          <div class="userspn-popup-content">
            <div class="userspn-p-30">
              <div class="userspn-width-100-percent userspn-text-align-center">
                <i class="material-icons-outlined userspn-color-main-0 userspn-font-size-75">report_problem</i>
              </div>

              <h2 class="userspn-mt-10 userspn-mb-10 userspn-text-align-center"><?php esc_html_e('Account deletion!', 'userspn'); ?></h2>
              <p class="userspn-alert userspn-mb-30"><?php esc_html_e('Your account removal will be permanent. This action cannot be undone!', 'userspn'); ?></p>

              <label class="userspn-display-block" for="userspn_password"><?php esc_html_e('Include your password to confirm deletion', 'userspn'); ?></label>

              <input type="password" name="userspn_password" id="userspn_password" class="userspn-input userspn-width-100-percent" placeholder="<?php esc_html_e('Password', 'userspn'); ?>">

              <div class="userspn-width-100-percent userspn-text-align-right">
                <a href="#" class="userspn-btn userspn-user-remove-btn" data-userspn-user-id="<?php echo esc_attr(get_current_user_id()); ?>"><?php esc_html_e('Remove user', 'userspn'); ?></a><?php echo esc_html(USERSPN_Data::userspn_loader()); ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php
    $userspn_return_string = ob_get_contents(); 
    ob_end_clean(); 
    return $userspn_return_string;
  }

  public function userspn_check_password($user_id, $password){
    $user = get_user_by('id', $user_id);

    if ($user && wp_check_password($password, $user->data->user_pass, $user->ID)) {
      return true;
    }

    return false;
  }

  public function userspn_user_change_password_btn() {
    // echo do_shortcode('[userspn-user-change-password-btn]');
    ob_start();
    ?>
      <div class="userspn-display-table userspn-width-100-percent userspn-mt-30 userspn-mb-30 userspn-p-10">
        <div class="userspn-display-inline-table userspn-width-100-percent">
          <div class="userspn-toggle-wrapper userspn-position-relative userspn-mb-10">
            <a href="#" class="userspn-toggle userspn-width-100-percent userspn-text-decoration-none">
              <div class="userspn-display-table userspn-width-100-percent">
                <div class="userspn-display-inline-table userspn-width-80-percent userspn-vertical-align-middle">
                  <label class="userspn-display-block"><?php esc_html_e('Change password', 'userspn'); ?></label>
                </div>

                <div class="userspn-display-inline-table userspn-width-10-percent userspn-vertical-align-middle userspn-text-align-right">
                  <i class="material-icons-outlined userspn-cursor-pointer userspn-vertical-align-middle userspn-color-main-0">add</i>
                </div>
              </div>
            </a>

            <div class="userspn-toggle-content userspn-display-none-soft">
              <small><?php esc_html_e('Change your profile access password. The process will send you an email to your account to secure the process.', 'userspn'); ?></small>
            </div>
          </div>
        </div>

        <div class="userspn-display-inline-table userspn-width-100-percent userspn-text-align-right">
          <a href="<?php echo esc_url(wp_lostpassword_url(home_url())); ?>" class="userspn-btn userspn-btn-transparent userspn-btn-mini"><?php esc_html_e('Change password', 'userspn'); ?></a>
        </div>
      </div>
    <?php
    $userspn_return_string = ob_get_contents(); 
    ob_end_clean(); 
    return $userspn_return_string;
  }

  public function userspn_login($atts) {
    /* echo do_shortcode('[userspn-login]'); */
    ob_start();
    
    if (!is_user_logged_in()) {
    ?>
      <div id="userspn-login" class="userspn-login userspn-p-20 userspn-margin-auto userspn-mb-30 userspn-position-relative">
        <div class="userspn-text-align-center userspn-mb-30">
          <a href="<?php echo esc_url(home_url()); ?>"><?php echo wp_get_attachment_image(310,  'full'); ?></a>
        </div>
        <?php echo wp_login_form(['echo' => false, 'label_remember' => __('Remember Me', 'userspn'), 'label_log_in' => __('Log In', 'userspn'),]);?>

        <div class="userspn-text-align-center userspn-mt-10 userspn-pb-30 userspn-font-size-12">
          <a href="<?php echo esc_url(wp_lostpassword_url(home_url())); ?>"><p class="font-weight-bold"><?php esc_html_e('Forgot your password?', 'userspn'); ?></p></a>
        </div>
      </div>
    <?php
    }
    
    $userspn_return_string = ob_get_contents(); 
    ob_end_clean(); 
    return $userspn_return_string;
  }

  public function userspn_user_register_form() {
    $userspn_user_register_base_fields = [];
    $userspn_user_register_base_fields['userspn_email'] = [
      'id' => 'userspn_email',
      'class' => 'userspn-input userspn-width-100-percent',
      'input' => 'input',
      'type' => 'email',
      'required' => 'true',
      'label' => __('Email', 'userspn'),
      'placeholder' => __('Email', 'userspn'),
    ];
    $userspn_user_register_base_fields['userspn_password'] = [
      'id' => 'userspn_password',
      'class' => 'userspn-input userspn-width-100-percent',
      'input' => 'input',
      'type' => 'password',
      'required' => 'true',
      'label' => __('Password', 'userspn'),
      'placeholder' => __('Password', 'userspn'),
    ];


    $userspn_user_register_fields = self::userspn_user_register_get_fields($userspn_user_register_base_fields);

    ob_start();
    ?>
      <?php if (!is_user_logged_in()): ?>
        <?php if (!wp_script_is('userspn-user-register-form', 'enqueued')): ?>
          <?php wp_enqueue_script('userspn-user-register-form', USERSPN_URL . 'assets/js/userspn-user-register-form.js', ['jquery'], USERSPN_VERSION, false, ['in_footer' => true, 'strategy' => 'defer']); ?>
        <?php endif ?>

        <form id="userspn-user-register-fields" class="userspn-mt-30">
          <?php foreach ($userspn_user_register_fields as $userspn_user_register_field): ?>
            <?php USERSPN_Forms::userspn_input_wrapper_builder($userspn_user_register_field, 'user', 0, 0, 'full'); ?>
          <?php endforeach ?>

          <div class="userspn-text-align-right userspn-mt-30 userspn-mb-30">
            <input type="submit" value="<?php esc_html_e('Create user', 'userspn'); ?>" name="userspn-user-registration-btn" id="userspn-user-registration-btn" class="userspn-btn userspn-btn"/><?php echo esc_html(USERSPN_Data::userspn_loader()); ?>
          </div>
        </form>
      <?php else: ?>
        <?php echo do_shortcode('[userspn-call-to-action userspn_call_to_action_icon="emoji_people" userspn_call_to_action_title="' . esc_html(__('You are registered', 'userspn')) . '" userspn_call_to_action_content="' . esc_html(__('You are already registered and logged in the system. So you cannot create a new user. Please close your session to register a new account.', 'userspn')) . '"]'); ?>
      <?php endif ?>
    <?php
    $userspn_return_string = ob_get_contents(); 
    ob_end_clean(); 
    return $userspn_return_string;
  }

  public function userspn_profile_save_fields($user_id){
    // No ejecutar durante el checkout de WooCommerce
    if (function_exists('is_checkout') && is_checkout()) {
      return;
    }
    
    // Skip validation if this is a password reset request (check both POST and GET parameters)
    if (isset($_GET['action']) && ($_GET['action'] === 'resetpassword' ||  $_GET['action'] === 'lostpassword')) {
      return true;
    }
    
    // Always require nonce verification
    if (!array_key_exists('userspn_ajax_nopriv_nonce', $_POST)) {
      echo wp_json_encode([
        'error_key' => 'userspn_profile_ajax_nopriv_nonce_error_required',
        'error_content' => esc_html(__('Security check failed: Nonce is required.', 'userspn')),
      ]);

      exit();
    }

    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['userspn_ajax_nopriv_nonce'])), 'userspn-nonce')) {
      echo wp_json_encode([
        'error_key' => 'userspn_profile_ajax_nopriv_nonce_error_invalid',
        'error_content' => esc_html(__('Security check failed: Invalid nonce.', 'userspn')),
      ]);

      exit();
    }

    if(!current_user_can('manage_options') || get_option('userspn_user_register_fields_dashboard') != 'on'){
      return false;
    }

    $userspn_user_register_fields = self::userspn_user_register_get_fields([]);

    foreach ($userspn_user_register_fields as $userspn_user_register_field) {
      if (array_key_exists($userspn_user_register_field['id'], $_POST)) {
        $field_value = sanitize_text_field(wp_unslash($_POST[$userspn_user_register_field['id']]));
        update_user_meta($user_id, USERSPN_Forms::userspn_sanitizer(wp_unslash($userspn_user_register_field['id'])), $field_value);
      }
    }
  }

  public function userspn_csv_template($file_name, $header, $body_rows) {
    /* echo do_shortcode('[userspn-csv-template]'); */
    $file_name = esc_html(__('Contacts upload template', 'userspn'));
    $header = [esc_html(__('Required', 'userspn')) . ' ' . esc_html(__('Email', 'userspn')), esc_html(__('Role', 'userspn')) . ' ' . 'Please include EXACTLY one of the values shown in the example row separated by the symbol |.'];
    $body_rows = [[esc_html(__('email@test.com', 'userspn')), '[administrator|editor|author|subscriber' . (class_exists('vle_init') ? '|vle_admin|vle_master_trainer|vle_leader|vle_participant' : '') . ']']];

    $userspn_register_fields = self::userspn_user_register_get_fields([]);

    if (!empty($userspn_register_fields)) {
      foreach ($userspn_register_fields as $profile_field) {
        $header_label = ((array_key_exists('required', $profile_field) && $profile_field['required']) ? esc_html(__('Required', 'userspn')) . ': ' : '') . $profile_field['label'];

        switch ($profile_field['input']) {
          case 'input':
            switch ($profile_field['type']) {
              case 'date':
                $header_label .= ' - ' . esc_html(__('Please include data in YEAR-MONTH-DAY format as today is shown in the example.', 'userspn'));
              break;
            }

            break;
          case 'select':
            $header_label .= ' - ' . esc_html(__('Please include EXACTLY one of the values shown in the example row separated by the symbol |.', 'userspn'));
            break;
        }

        $header[] = $header_label; 

        switch ($profile_field['input']) {
          case 'input':
            switch ($profile_field['type']) {
              case 'text':
                $body_rows[0][] = esc_html(__('Text for', 'userspn')) . ' ' . $profile_field['label']; 
                break;
              case 'number':
                $body_rows[0][] = esc_html(__('Number for', 'userspn')) . ' ' . $profile_field['label']; 
                break;
              case 'date':
                $body_rows[0][] = gmdate('Y-m-d'); 
                break;
              case 'checkbox':
                $body_rows[0][] = '[on|emtpy]'; 
                break;
              case 'checkbox':
                $body_rows[0][] = esc_html(__('Include "on" for checked of leave the cell empty for unchecked', 'userspn')) . ' ' . $profile_field['label']; 
                break;
              default:
                $body_rows[0][] = esc_html(__('Text for', 'userspn')) . ' ' . $profile_field['label']; 
                break;
            }

            break;
          case 'select':
            $body_rows[0][] = '[' . implode('|', array_keys($profile_field['options'])) . ']'; 
            break;
        }
      }
    }

    $path = wp_upload_dir();
    $file_path = $path['path'] . '/' . $file_name . '.csv';
    
    // Use WP_Filesystem instead of direct PHP filesystem calls
    global $wp_filesystem;
    if (empty($wp_filesystem)) {
      require_once(ABSPATH . '/wp-admin/includes/file.php');
      WP_Filesystem();
    }
    
    $csv_content = '';
    $csv_content .= $this->array_to_csv_line($header);
    
    foreach($body_rows as $body_row) {
      $csv_content .= $this->array_to_csv_line($body_row);
    }
    
    $wp_filesystem->put_contents($file_path, $csv_content);
    
    ob_start();
    ?>    
      <a href="<?php echo esc_url($path['url'] . '/' . $file_name . '.csv'); ?>" class="userspn-csv-template-btn userspn-btn userspn-btn-transparent userspn-btn-mini"><?php esc_html_e('Download CSV template', 'userspn'); ?></a>
    <?php
    $userspn_return_string = ob_get_contents();
    ob_end_clean();
    return $userspn_return_string;
  }

  /**
   * Helper function to convert array to CSV line
   */
  private function array_to_csv_line($array) {
    // Use pure PHP implementation without direct filesystem calls
    $output = '';
    
    foreach ($array as $index => $field) {
      // Escape double quotes and wrap in quotes if needed
      $field = str_replace('"', '""', $field);
      if (strpos($field, ',') !== false || strpos($field, '"') !== false || strpos($field, "\n") !== false) {
        $field = '"' . $field . '"';
      }
      
      $output .= $field;
      
      // Add comma separator except for the last element
      if ($index < count($array) - 1) {
        $output .= ',';
      }
    }
    
    return $output . "\n";
  }

  public function userspn_csv_template_upload() {
    /* echo do_shortcode('[userspn-csv-template-upload]'); */
    ?>
      <div class="userspn-p-10">
        <div class="userspn-mb-50">
          <h4><?php esc_html_e('01 - Download a CSV template with the platform specifications.', 'userspn'); ?></h4>
          <div class="userspn-text-align-center"><?php echo do_shortcode('[userspn-csv-template]'); ?></div>
        </div>

        <form action="" method="post">
          <h4><?php esc_html_e('02 - Upload the CSV with the new contacts information.', 'userspn'); ?></h4>
          <input type="file" id="userspn-csv-template-file-upload" class="userspn-display-block userspn-csv-template-upload-form" value="<?php esc_html_e('Upload', 'userspn'); ?>"/>
          
          <div class="userspn-text-align-center userspn-mt-30">
            <input type="submit" name="<?php esc_html_e('Upload', 'userspn'); ?>" class="userspn-csv-template-upload-btn userspn-btn userspn-btn-transparent userspn-btn-mini"/>
          </div>
        </form>
      </div>

      <div id="userspn-csv-template-table" class="userspn-display-none-soft"></div>
    <?php
  }

  public function userspn_csv_template_reader($userspn_file) {
    $row = 0;
    $array = [];

    // Use WP_Filesystem instead of direct PHP filesystem calls
    global $wp_filesystem;
    if (empty($wp_filesystem)) {
      require_once(ABSPATH . '/wp-admin/includes/file.php');
      WP_Filesystem();
    }
    
    $file_content = $wp_filesystem->get_contents($userspn_file);
    
    if ($file_content !== false) {
      $lines = explode("\n", $file_content);
      
      foreach ($lines as $line) {
        if (empty(trim($line))) {
          continue;
        }
        
        $data = str_getcsv($line, ',');
        $num = count($data);
        
        for ($c = 0; $c < $num; $c++) {
          $array[$row][] = $data[$c] . "<br/>\n";
        }
        
        $row++;
      }
    }

    if (!empty($array)) {
      ob_start();
      ?>
        <div class="userspn-text-align-center userspn-mt-30 userspn-mb-50">
          <h4><?php esc_html_e('Add contacts', 'userspn'); ?></h4>
          <p><?php esc_html_e('Are you sure to process this contacts and add them to the system?', 'userspn'); ?></p>

          <div class="userspn-display-table userspn-width-100-percent">
            <div class="userspn-display-inline-table userspn-width-50-percent userspn-tablet-display-block userspn-tablet-width-100-percent userspn-text-align-center">
              <a href="#" class="userspn-popup-close"><?php esc_html_e('Cancel', 'userspn'); ?></a>
            </div>

            <div class="userspn-display-inline-table userspn-width-50-percent userspn-tablet-display-block userspn-tablet-width-100-percent userspn-text-align-center">
              <a href="#" class="userspn-btn userspn-csv-add-contacts"><?php esc_html_e('Add contacts', 'userspn'); ?></a><img class="userspn-waiting userspn-display-none-soft" src="<?php echo esc_url(plugin_dir_url(__FILE__) . 'assets/ajax-loader.gif'); ?>" alt="<?php esc_html_e('Loading...', 'userspn'); ?>"/>
            </div>
          </div>
        </div>

        <div class="userspn-mobile-scrollable userspn-mb-50">
          <table class="userspn-data-table">
            <thead>
              <tr>
                <?php foreach ($array[0] as $header_cell): ?>
                  <th><?php echo esc_html($header_cell); ?></th>
                <?php endforeach ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($array as $index => $body_row): ?>
                <?php if ($index > 0): ?>
                  <tr>
                    <?php foreach ($body_row as $body_cell): ?>
                      <td><?php echo esc_html($body_cell); ?></td>
                    <?php endforeach ?>
                  </tr>
                <?php endif ?>
              <?php endforeach ?>
            </tbody>
          </table>
        </div>
      <?php
      $userspn_return_string = ob_get_contents(); 
      ob_end_clean(); 
      return $userspn_return_string;
    }
  }
}

