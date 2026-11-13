<?php
/**
 * X (Twitter) OAuth REST API endpoints
 *
 * Handles X OAuth 2.0 callbacks and authentication
 *
 * @link       padresenlanube.com/
 * @since      1.1.62
 * @package    USERSPN
 * @subpackage USERSPN/includes
 * @author     Padres en la Nube <info@padresenlanube.com>
 */
class USERSPN_X_OAuth
{
  /**
   * Register REST API routes and AJAX handlers
   */
  public function register_routes()
  {
    register_rest_route('userspn/v1', '/x-callback', [
      'methods' => ['GET', 'POST'],
      'callback' => [$this, 'handle_x_callback'],
      'permission_callback' => [$this, 'public_permission_check'],
    ]);
  }

  /**
   * Register AJAX handler for callback (fallback method)
   */
  public function register_ajax_callback()
  {
    add_action('wp_ajax_nopriv_userspn_x_callback', [$this, 'handle_x_callback_ajax']);
    add_action('wp_ajax_userspn_x_callback', [$this, 'handle_x_callback_ajax']);
  }

  /**
   * Handle X OAuth callback via AJAX (fallback)
   */
  public function handle_x_callback_ajax()
  {
    $result = $this->handle_x_callback_internal($_GET);

    if (is_array($result) && isset($result['redirect'])) {
      wp_safe_redirect($result['redirect']);
      exit;
    }
  }

  /**
   * Permission callback - allows public access
   */
  public function public_permission_check()
  {
    return true;
  }

  /**
   * Handle X OAuth callback (REST API wrapper)
   */
  public function handle_x_callback($request)
  {
    $params = [];
    $params['code'] = $request->get_param('code');
    $params['state'] = $request->get_param('state');
    $params['error'] = $request->get_param('error');

    return $this->handle_x_callback_internal($params);
  }

  /**
   * Handle X OAuth callback (internal logic)
   */
  private function handle_x_callback_internal($params)
  {
    if (get_option('userspn_x_login_enabled') != 'on') {
      return $this->redirect_with_error('x_disabled', __('X login is not available.', 'userspn'));
    }

    $client_id = get_option('userspn_x_client_id');
    $client_secret = get_option('userspn_x_client_secret');

    if (empty($client_id) || empty($client_secret)) {
      return $this->redirect_with_error('not_configured', __('X login is not properly configured.', 'userspn'));
    }

    $code = isset($params['code']) ? $params['code'] : '';
    $state = isset($params['state']) ? $params['state'] : '';
    $error = isset($params['error']) ? $params['error'] : '';

    if (!empty($error)) {
      return $this->redirect_with_error('cancelled', __('X login was cancelled.', 'userspn'));
    }

    if (empty($code)) {
      return $this->redirect_with_error('no_code', __('No authorization code received.', 'userspn'));
    }

    if ($state !== 'userspn_x_login') {
      return $this->redirect_with_error('invalid_state', __('Invalid state parameter.', 'userspn'));
    }

    // Exchange code for access token using OAuth 2.0
    $redirect_uri = admin_url('admin-ajax.php?action=userspn_x_callback');

    // Retrieve PKCE code verifier from transient
    $code_verifier = get_transient('userspn_x_code_verifier');
    if (empty($code_verifier)) {
      return $this->redirect_with_error('no_verifier', __('Session expired. Please try again.', 'userspn'));
    }
    delete_transient('userspn_x_code_verifier');

    $token_response = wp_remote_post('https://api.x.com/2/oauth2/token', [
      'headers' => [
        'Content-Type' => 'application/x-www-form-urlencoded',
        'Authorization' => 'Basic ' . base64_encode($client_id . ':' . $client_secret),
      ],
      'body' => [
        'code' => $code,
        'grant_type' => 'authorization_code',
        'redirect_uri' => $redirect_uri,
        'code_verifier' => $code_verifier,
      ],
      'timeout' => 30,
    ]);

    if (is_wp_error($token_response)) {
      return $this->redirect_with_error('token_error', __('Error connecting to X.', 'userspn'));
    }

    $token_data = json_decode(wp_remote_retrieve_body($token_response), true);

    if (empty($token_data['access_token'])) {
      return $this->redirect_with_error('no_token', __('Error obtaining access token.', 'userspn'));
    }

    // Get user info from X API v2
    $user_info_response = wp_remote_get('https://api.x.com/2/users/me?user.fields=id,name,username,profile_image_url', [
      'headers' => [
        'Authorization' => 'Bearer ' . $token_data['access_token'],
      ],
      'timeout' => 30,
    ]);

    if (is_wp_error($user_info_response)) {
      return $this->redirect_with_error('userinfo_error', __('Error getting user information.', 'userspn'));
    }

    $user_response = json_decode(wp_remote_retrieve_body($user_info_response), true);
    $user_info = isset($user_response['data']) ? $user_response['data'] : [];

    if (empty($user_info['id'])) {
      return $this->redirect_with_error('no_user', __('No user information received from X.', 'userspn'));
    }

    $result = $this->process_x_user($user_info);

    if (is_wp_error($result)) {
      return $this->redirect_with_error('processing_error', $result->get_error_message());
    }

    $redirect_url = home_url();
    wp_safe_redirect($redirect_url);
    exit;
  }

  /**
   * Process X user information
   */
  private function process_x_user($user_info)
  {
    $x_id = sanitize_text_field($user_info['id']);
    $x_username = isset($user_info['username']) ? sanitize_text_field($user_info['username']) : '';
    $x_name = isset($user_info['name']) ? sanitize_text_field($user_info['name']) : '';
    $x_picture = isset($user_info['profile_image_url']) ? esc_url_raw($user_info['profile_image_url']) : '';

    // Try to find existing user by X ID
    $existing_users = get_users([
      'meta_key' => 'userspn_x_id',
      'meta_value' => $x_id,
      'number' => 1,
    ]);

    if (!empty($existing_users)) {
      $user = $existing_users[0];
      update_user_meta($user->ID, 'userspn_x_last_login', current_time('timestamp'));

      wp_set_current_user($user->ID);
      wp_set_auth_cookie($user->ID, true);
      do_action('wp_login', $user->user_login, $user);

      update_user_meta($user->ID, 'userspn_inactive_warning_sent', 'off');

      return true;
    }

    // Create new user (X does not provide email via OAuth 2.0 by default)
    $username = !empty($x_username) ? sanitize_user($x_username) : 'x_user_' . $x_id;

    $base_username = $username;
    $counter = 1;
    while (username_exists($username)) {
      $username = $base_username . '_' . $counter;
      $counter++;
    }

    $password = wp_generate_password(16, true, true);
    $email = $username . '@x-oauth.local';

    // Ensure email is unique
    $base_email = $email;
    $counter = 1;
    while (email_exists($email)) {
      $email = $username . '_' . $counter . '@x-oauth.local';
      $counter++;
    }

    $user_id = wp_create_user($username, $password, $email);

    if (is_wp_error($user_id)) {
      return new WP_Error('user_creation_error', __('Error creating user account.', 'userspn'));
    }

    $update_data = [
      'ID' => $user_id,
    ];

    if (!empty($x_name)) {
      $update_data['display_name'] = $x_name;
      $name_parts = explode(' ', $x_name, 2);
      $update_data['first_name'] = $name_parts[0];
      if (isset($name_parts[1])) {
        $update_data['last_name'] = $name_parts[1];
      }
    }

    if (count($update_data) > 1) {
      wp_update_user($update_data);
    }

    update_user_meta($user_id, 'userspn_x_id', $x_id);
    update_user_meta($user_id, 'userspn_x_username', $x_username);
    update_user_meta($user_id, 'userspn_x_last_login', current_time('timestamp'));
    update_user_meta($user_id, 'userspn_registration_method', 'x');

    if (!empty($x_picture)) {
      update_user_meta($user_id, 'userspn_x_picture', $x_picture);
    }

    do_action('user_register', $user_id);

    $new_user = get_user_by('id', $user_id);
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id, true);
    do_action('wp_login', $new_user->user_login, $new_user);

    return true;
  }

  /**
   * Redirect with error message
   */
  private function redirect_with_error($error_code, $error_message)
  {
    $redirect_url = add_query_arg([
      'userspn_social_error' => $error_code,
      'userspn_social_message' => urlencode($error_message),
    ], home_url());

    wp_safe_redirect($redirect_url);
    exit;
  }
}
