<?php
/**
 * Facebook OAuth REST API endpoints
 *
 * Handles Facebook OAuth callbacks and authentication
 *
 * @link       padresenlanube.com/
 * @since      1.1.33
 * @package    USERSPN
 * @subpackage USERSPN/includes
 * @author     Padres en la Nube <info@padresenlanube.com>
 */
class USERSPN_Facebook_OAuth
{
  /**
   * Register REST API routes and AJAX handlers
   */
  public function register_routes()
  {
    // Register REST API endpoint (primary method)
    register_rest_route('userspn/v1', '/facebook-callback', [
      'methods' => ['GET', 'POST'],
      'callback' => [$this, 'handle_facebook_callback'],
      'permission_callback' => [$this, 'public_permission_check'],
    ]);
  }

  /**
   * Register AJAX handler for callback (fallback method)
   */
  public function register_ajax_callback()
  {
    add_action('wp_ajax_nopriv_userspn_facebook_callback', [$this, 'handle_facebook_callback_ajax']);
    add_action('wp_ajax_userspn_facebook_callback', [$this, 'handle_facebook_callback_ajax']);
  }

  /**
   * Handle Facebook OAuth callback via AJAX (fallback)
   */
  public function handle_facebook_callback_ajax()
  {
    // Create a fake request object from GET parameters
    $fake_request = new stdClass();
    $fake_request->params = $_GET;

    $result = $this->handle_facebook_callback_internal($_GET);

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
   * Handle Facebook OAuth callback (REST API wrapper)
   */
  public function handle_facebook_callback($request)
  {
    $params = [];
    $params['code'] = $request->get_param('code');
    $params['state'] = $request->get_param('state');
    $params['error'] = $request->get_param('error');

    return $this->handle_facebook_callback_internal($params);
  }

  /**
   * Handle Facebook OAuth callback (internal logic)
   */
  private function handle_facebook_callback_internal($params)
  {
    // Verificar que Facebook Login esté habilitado
    if (get_option('userspn_facebook_login_enabled') != 'on') {
      return $this->redirect_with_error('facebook_disabled', __('Facebook login is not available.', 'userspn'));
    }

    $app_id = get_option('userspn_facebook_app_id');
    $app_secret = get_option('userspn_facebook_app_secret');

    if (empty($app_id) || empty($app_secret)) {
      return $this->redirect_with_error('not_configured', __('Facebook login is not properly configured.', 'userspn'));
    }

    // Obtener parámetros
    $code = isset($params['code']) ? $params['code'] : '';
    $state = isset($params['state']) ? $params['state'] : '';
    $error = isset($params['error']) ? $params['error'] : '';

    // Verificar si el usuario canceló
    if (!empty($error)) {
      return $this->redirect_with_error('cancelled', __('Facebook login was cancelled.', 'userspn'));
    }

    // Verificar código
    if (empty($code)) {
      return $this->redirect_with_error('no_code', __('No authorization code received.', 'userspn'));
    }

    // Verificar state
    if ($state !== 'userspn_facebook_login') {
      return $this->redirect_with_error('invalid_state', __('Invalid state parameter.', 'userspn'));
    }

    // Intercambiar código por token de acceso
    $redirect_uri = admin_url('admin-ajax.php?action=userspn_facebook_callback');

    $token_response = wp_remote_get(add_query_arg([
      'client_id' => $app_id,
      'client_secret' => $app_secret,
      'code' => $code,
      'redirect_uri' => $redirect_uri,
    ], 'https://graph.facebook.com/v18.0/oauth/access_token'), [
      'timeout' => 30,
    ]);

    if (is_wp_error($token_response)) {
      return $this->redirect_with_error('token_error', __('Error connecting to Facebook.', 'userspn'));
    }

    $token_data = json_decode(wp_remote_retrieve_body($token_response), true);

    if (empty($token_data['access_token'])) {
      return $this->redirect_with_error('no_token', __('Error obtaining access token.', 'userspn'));
    }

    // Obtener información del usuario de Facebook
    $user_info_response = wp_remote_get(add_query_arg([
      'fields' => 'id,name,email,first_name,last_name,picture',
      'access_token' => $token_data['access_token'],
    ], 'https://graph.facebook.com/v18.0/me'), [
      'timeout' => 30,
    ]);

    if (is_wp_error($user_info_response)) {
      return $this->redirect_with_error('userinfo_error', __('Error getting user information.', 'userspn'));
    }

    $user_info = json_decode(wp_remote_retrieve_body($user_info_response), true);

    if (empty($user_info['email'])) {
      return $this->redirect_with_error('no_email', __('No email address received from Facebook.', 'userspn'));
    }

    // Procesar usuario
    $result = $this->process_facebook_user($user_info);

    if (is_wp_error($result)) {
      return $this->redirect_with_error('processing_error', $result->get_error_message());
    }

    // Redirigir con éxito
    $redirect_url = home_url();
    wp_safe_redirect($redirect_url);
    exit;
  }

  /**
   * Process Facebook user information
   */
  private function process_facebook_user($user_info)
  {
    $facebook_email = sanitize_email($user_info['email']);
    $facebook_id = isset($user_info['id']) ? sanitize_text_field($user_info['id']) : '';
    $facebook_name = isset($user_info['name']) ? sanitize_text_field($user_info['name']) : '';
    $facebook_first_name = isset($user_info['first_name']) ? sanitize_text_field($user_info['first_name']) : '';
    $facebook_last_name = isset($user_info['last_name']) ? sanitize_text_field($user_info['last_name']) : '';
    $facebook_picture = isset($user_info['picture']['data']['url']) ? esc_url_raw($user_info['picture']['data']['url']) : '';

    // Verificar si el usuario ya existe por email
    $user = get_user_by('email', $facebook_email);

    if ($user) {
      // Usuario existe - hacer login
      // Guardar/actualizar Facebook ID si no existe
      if (empty(get_user_meta($user->ID, 'userspn_facebook_id', true))) {
        update_user_meta($user->ID, 'userspn_facebook_id', $facebook_id);
      }

      // Actualizar última conexión con Facebook
      update_user_meta($user->ID, 'userspn_facebook_last_login', current_time('timestamp'));

      // Login
      wp_set_current_user($user->ID);
      wp_set_auth_cookie($user->ID, true);
      do_action('wp_login', $user->user_login, $user);

      // Resetear flag de advertencia de inactividad
      update_user_meta($user->ID, 'userspn_inactive_warning_sent', 'off');

      return true;
    } else {
      // Usuario no existe - crear nuevo usuario
      $username = sanitize_user(str_replace('@', '_', $facebook_email));

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
      $user_id = wp_create_user($username, $password, $facebook_email);

      if (is_wp_error($user_id)) {
        return new WP_Error('user_creation_error', __('Error creating user account.', 'userspn'));
      }

      // Actualizar datos del usuario
      $update_data = [
        'ID' => $user_id,
      ];

      if (!empty($facebook_first_name)) {
        $update_data['first_name'] = $facebook_first_name;
      }

      if (!empty($facebook_last_name)) {
        $update_data['last_name'] = $facebook_last_name;
      }

      if (!empty($facebook_name)) {
        $update_data['display_name'] = $facebook_name;
      }

      if (!empty($update_data['first_name']) || !empty($update_data['last_name']) || !empty($update_data['display_name'])) {
        wp_update_user($update_data);
      }

      // Guardar Facebook ID y metadatos
      update_user_meta($user_id, 'userspn_facebook_id', $facebook_id);
      update_user_meta($user_id, 'userspn_facebook_last_login', current_time('timestamp'));
      update_user_meta($user_id, 'userspn_registration_method', 'facebook');

      // Guardar foto de perfil de Facebook si está disponible
      if (!empty($facebook_picture)) {
        update_user_meta($user_id, 'userspn_facebook_picture', $facebook_picture);
      }

      // Ejecutar hook de registro (esto ejecutará userspn_user_register)
      do_action('user_register', $user_id);

      // Login automático
      $new_user = get_user_by('id', $user_id);
      wp_set_current_user($user_id);
      wp_set_auth_cookie($user_id, true);
      do_action('wp_login', $new_user->user_login, $new_user);

      return true;
    }
  }

  /**
   * Redirect with error message
   */
  private function redirect_with_error($error_code, $error_message)
  {
    $redirect_url = add_query_arg([
      'userspn_facebook_error' => $error_code,
      'userspn_facebook_message' => urlencode($error_message),
    ], home_url());

    wp_safe_redirect($redirect_url);
    exit;
  }
}
