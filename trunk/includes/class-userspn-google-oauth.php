<?php
/**
 * Google OAuth REST API endpoints
 *
 * Handles Google OAuth callbacks and authentication
 *
 * @link       padresenlanube.com/
 * @since      1.1.33
 * @package    USERSPN
 * @subpackage USERSPN/includes
 * @author     Padres en la Nube <info@padresenlanube.com>
 */
class USERSPN_Google_OAuth
{
  /**
   * Register REST API routes and AJAX handlers
   */
  public function register_routes()
  {
    // Register REST API endpoint (primary method)
    register_rest_route('userspn/v1', '/google-callback', [
      'methods' => ['GET', 'POST'],
      'callback' => [$this, 'handle_google_callback'],
      'permission_callback' => [$this, 'public_permission_check'],
    ]);
  }

  /**
   * Register AJAX handler for callback (fallback method)
   */
  public function register_ajax_callback()
  {
    add_action('wp_ajax_nopriv_userspn_google_callback', [$this, 'handle_google_callback_ajax']);
    add_action('wp_ajax_userspn_google_callback', [$this, 'handle_google_callback_ajax']);
  }

  /**
   * Handle Google OAuth callback via AJAX (fallback)
   */
  public function handle_google_callback_ajax()
  {
    // Create a fake request object from GET parameters
    $fake_request = new stdClass();
    $fake_request->params = $_GET;

    $result = $this->handle_google_callback_internal($_GET);

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
   * Handle Google OAuth callback (REST API wrapper)
   */
  public function handle_google_callback($request)
  {
    $params = [];
    $params['code'] = $request->get_param('code');
    $params['state'] = $request->get_param('state');
    $params['error'] = $request->get_param('error');

    return $this->handle_google_callback_internal($params);
  }

  /**
   * Handle Google OAuth callback (internal logic)
   */
  private function handle_google_callback_internal($params)
  {
    // Verificar que Google Login esté habilitado
    if (get_option('userspn_google_login_enabled') != 'on') {
      return $this->redirect_with_error('google_disabled', __('Google login is not available.', 'userspn'));
    }

    $client_id = get_option('userspn_google_client_id');
    $client_secret = get_option('userspn_google_client_secret');

    if (empty($client_id) || empty($client_secret)) {
      return $this->redirect_with_error('not_configured', __('Google login is not properly configured.', 'userspn'));
    }

    // Obtener parámetros
    $code = isset($params['code']) ? $params['code'] : '';
    $state = isset($params['state']) ? $params['state'] : '';
    $error = isset($params['error']) ? $params['error'] : '';

    // Verificar si el usuario canceló
    if (!empty($error)) {
      return $this->redirect_with_error('cancelled', __('Google login was cancelled.', 'userspn'));
    }

    // Verificar código
    if (empty($code)) {
      return $this->redirect_with_error('no_code', __('No authorization code received.', 'userspn'));
    }

    // Verificar state
    if ($state !== 'userspn_google_login') {
      return $this->redirect_with_error('invalid_state', __('Invalid state parameter.', 'userspn'));
    }

    // Intercambiar código por token de acceso
    // Usar admin-ajax.php (debe coincidir con la URL enviada a Google)
    $redirect_uri = admin_url('admin-ajax.php?action=userspn_google_callback');

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
      return $this->redirect_with_error('token_error', __('Error connecting to Google.', 'userspn'));
    }

    $token_data = json_decode(wp_remote_retrieve_body($token_response), true);

    if (empty($token_data['access_token'])) {
      return $this->redirect_with_error('no_token', __('Error obtaining access token.', 'userspn'));
    }

    // Obtener información del usuario de Google
    $user_info_response = wp_remote_get('https://www.googleapis.com/oauth2/v2/userinfo', [
      'headers' => [
        'Authorization' => 'Bearer ' . $token_data['access_token'],
      ],
      'timeout' => 30,
    ]);

    if (is_wp_error($user_info_response)) {
      return $this->redirect_with_error('userinfo_error', __('Error getting user information.', 'userspn'));
    }

    $user_info = json_decode(wp_remote_retrieve_body($user_info_response), true);

    if (empty($user_info['email'])) {
      return $this->redirect_with_error('no_email', __('No email address received from Google.', 'userspn'));
    }

    // Procesar usuario
    $result = $this->process_google_user($user_info);

    if (is_wp_error($result)) {
      return $this->redirect_with_error('processing_error', $result->get_error_message());
    }

    // Redirigir con éxito
    $redirect_url = home_url();
    wp_safe_redirect($redirect_url);
    exit;
  }

  /**
   * Process Google user information
   */
  private function process_google_user($user_info)
  {
    $google_email = sanitize_email($user_info['email']);
    $google_id = isset($user_info['id']) ? sanitize_text_field($user_info['id']) : '';
    $google_name = isset($user_info['name']) ? sanitize_text_field($user_info['name']) : '';
    $google_picture = isset($user_info['picture']) ? esc_url_raw($user_info['picture']) : '';
    $google_given_name = isset($user_info['given_name']) ? sanitize_text_field($user_info['given_name']) : '';
    $google_family_name = isset($user_info['family_name']) ? sanitize_text_field($user_info['family_name']) : '';

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

      return true;
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
        return new WP_Error('user_creation_error', __('Error creating user account.', 'userspn'));
      }

      // Actualizar datos del usuario
      $update_data = [
        'ID' => $user_id,
      ];

      if (!empty($google_given_name)) {
        $update_data['first_name'] = $google_given_name;
      }

      if (!empty($google_family_name)) {
        $update_data['last_name'] = $google_family_name;
      }

      if (!empty($google_name)) {
        $update_data['display_name'] = $google_name;
      }

      if (!empty($update_data['first_name']) || !empty($update_data['last_name']) || !empty($update_data['display_name'])) {
        wp_update_user($update_data);
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
      'userspn_google_error' => $error_code,
      'userspn_google_message' => urlencode($error_message),
    ], home_url());

    wp_safe_redirect($redirect_url);
    exit;
  }
}
