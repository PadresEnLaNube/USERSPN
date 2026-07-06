<?php
/**
 * Apple OAuth REST API endpoints
 *
 * Handles Apple Sign In callbacks and authentication
 *
 * @link       padresenlanube.com/
 * @since      1.1.33
 * @package    USERSPN
 * @subpackage USERSPN/includes
 * @author     Padres en la Nube <info@padresenlanube.com>
 */
class USERSPN_Apple_OAuth
{
  /**
   * Register REST API routes and AJAX handlers
   */
  public function register_routes()
  {
    // Register REST API endpoint (primary method)
    register_rest_route('userspn/v1', '/apple-callback', [
      'methods' => ['GET', 'POST'],
      'callback' => [$this, 'handle_apple_callback'],
      'permission_callback' => [$this, 'public_permission_check'],
    ]);
  }

  /**
   * Register AJAX handler for callback (fallback method)
   */
  public function register_ajax_callback()
  {
    add_action('wp_ajax_nopriv_userspn_apple_callback', [$this, 'handle_apple_callback_ajax']);
    add_action('wp_ajax_userspn_apple_callback', [$this, 'handle_apple_callback_ajax']);
  }

  /**
   * Handle Apple OAuth callback via AJAX (fallback)
   */
  public function handle_apple_callback_ajax()
  {
    // Apple sends POST data for callback
    $params = array_merge($_GET, $_POST);

    $result = $this->handle_apple_callback_internal($params);

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
   * Handle Apple OAuth callback (REST API wrapper)
   */
  public function handle_apple_callback($request)
  {
    $params = [];
    $params['code'] = $request->get_param('code');
    $params['state'] = $request->get_param('state');
    $params['error'] = $request->get_param('error');
    $params['id_token'] = $request->get_param('id_token');
    $params['user'] = $request->get_param('user');

    return $this->handle_apple_callback_internal($params);
  }

  /**
   * Handle Apple OAuth callback (internal logic)
   */
  private function handle_apple_callback_internal($params)
  {
    // Verificar que Apple Login esté habilitado
    if (get_option('userspn_apple_login_enabled') != 'on') {
      return $this->redirect_with_error('apple_disabled', __('Apple login is not available.', 'userspn'));
    }

    $service_id = get_option('userspn_apple_service_id');
    $team_id = get_option('userspn_apple_team_id');
    $key_id = get_option('userspn_apple_key_id');
    $private_key = get_option('userspn_apple_private_key');

    if (empty($service_id) || empty($team_id) || empty($key_id) || empty($private_key)) {
      return $this->redirect_with_error('not_configured', __('Apple login is not properly configured.', 'userspn'));
    }

    // Obtener parámetros
    $code = isset($params['code']) ? $params['code'] : '';
    $state = isset($params['state']) ? $params['state'] : '';
    $error = isset($params['error']) ? $params['error'] : '';
    $id_token = isset($params['id_token']) ? $params['id_token'] : '';
    $user_data = isset($params['user']) ? $params['user'] : '';

    // Verificar si el usuario canceló
    if (!empty($error)) {
      return $this->redirect_with_error('cancelled', __('Apple login was cancelled.', 'userspn'));
    }

    // Verificar código
    if (empty($code)) {
      return $this->redirect_with_error('no_code', __('No authorization code received.', 'userspn'));
    }

    // Verificar state
    if ($state !== 'userspn_apple_login') {
      return $this->redirect_with_error('invalid_state', __('Invalid state parameter.', 'userspn'));
    }

    // Decodificar información del usuario si está disponible (solo en el primer login)
    $apple_user_info = null;
    if (!empty($user_data)) {
      if (is_string($user_data)) {
        $apple_user_info = json_decode($user_data, true);
      } else {
        $apple_user_info = $user_data;
      }
    }

    // Generar client_secret (JWT firmado)
    $client_secret = $this->generate_apple_client_secret($team_id, $service_id, $key_id, $private_key);

    if (is_wp_error($client_secret)) {
      return $this->redirect_with_error('secret_generation_error', $client_secret->get_error_message());
    }

    // Intercambiar código por token de acceso
    $redirect_uri = admin_url('admin-ajax.php?action=userspn_apple_callback');

    $token_response = wp_remote_post('https://appleid.apple.com/auth/token', [
      'headers' => [
        'Content-Type' => 'application/x-www-form-urlencoded',
      ],
      'body' => [
        'client_id' => $service_id,
        'client_secret' => $client_secret,
        'code' => $code,
        'grant_type' => 'authorization_code',
        'redirect_uri' => $redirect_uri,
      ],
      'timeout' => 30,
    ]);

    if (is_wp_error($token_response)) {
      return $this->redirect_with_error('token_error', __('Error connecting to Apple.', 'userspn'));
    }

    $token_data = json_decode(wp_remote_retrieve_body($token_response), true);

    if (empty($token_data['id_token'])) {
      return $this->redirect_with_error('no_token', __('Error obtaining access token.', 'userspn'));
    }

    // Decodificar el ID token para obtener información del usuario
    $user_info = $this->decode_apple_id_token($token_data['id_token']);

    if (is_wp_error($user_info)) {
      return $this->redirect_with_error('token_decode_error', $user_info->get_error_message());
    }

    // Combinar con datos adicionales del usuario si están disponibles
    if (!empty($apple_user_info)) {
      $user_info = array_merge($user_info, $apple_user_info);
    }

    if (empty($user_info['email'])) {
      return $this->redirect_with_error('no_email', __('No email address received from Apple.', 'userspn'));
    }

    // Procesar usuario
    $result = $this->process_apple_user($user_info);

    if (is_wp_error($result)) {
      return $this->redirect_with_error('processing_error', $result->get_error_message());
    }

    // Redirigir con éxito
    $redirect_url = home_url();
    wp_safe_redirect($redirect_url);
    exit;
  }

  /**
   * Generate Apple client_secret (JWT)
   */
  private function generate_apple_client_secret($team_id, $service_id, $key_id, $private_key)
  {
    // Verificar que OpenSSL esté disponible
    if (!function_exists('openssl_sign')) {
      return new WP_Error('openssl_missing', __('OpenSSL extension is required for Apple Sign In.', 'userspn'));
    }

    $header = [
      'alg' => 'ES256',
      'kid' => $key_id,
    ];

    $body = [
      'iss' => $team_id,
      'iat' => time(),
      'exp' => time() + 86400 * 180, // 180 días
      'aud' => 'https://appleid.apple.com',
      'sub' => $service_id,
    ];

    $header_encoded = $this->base64url_encode(wp_json_encode($header));
    $body_encoded = $this->base64url_encode(wp_json_encode($body));

    $signature_input = $header_encoded . '.' . $body_encoded;

    // Firmar con la clave privada
    $signature = '';
    $success = openssl_sign($signature_input, $signature, $private_key, OPENSSL_ALGO_SHA256);

    if (!$success) {
      return new WP_Error('signing_failed', __('Failed to sign client secret.', 'userspn'));
    }

    $signature_encoded = $this->base64url_encode($signature);

    return $signature_input . '.' . $signature_encoded;
  }

  /**
   * Decode Apple ID token
   */
  private function decode_apple_id_token($id_token)
  {
    $parts = explode('.', $id_token);

    if (count($parts) !== 3) {
      return new WP_Error('invalid_token', __('Invalid ID token format.', 'userspn'));
    }

    $payload = $this->base64url_decode($parts[1]);
    $claims = json_decode($payload, true);

    if (empty($claims)) {
      return new WP_Error('token_decode_failed', __('Failed to decode ID token.', 'userspn'));
    }

    return $claims;
  }

  /**
   * Base64 URL encode
   */
  private function base64url_encode($data)
  {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
  }

  /**
   * Base64 URL decode
   */
  private function base64url_decode($data)
  {
    return base64_decode(strtr($data, '-_', '+/'));
  }

  /**
   * Process Apple user information
   */
  private function process_apple_user($user_info)
  {
    $apple_email = sanitize_email($user_info['email']);
    $apple_id = isset($user_info['sub']) ? sanitize_text_field($user_info['sub']) : '';
    $apple_first_name = isset($user_info['name']['firstName']) ? sanitize_text_field($user_info['name']['firstName']) : '';
    $apple_last_name = isset($user_info['name']['lastName']) ? sanitize_text_field($user_info['name']['lastName']) : '';

    // Verificar si el usuario ya existe por email
    $user = get_user_by('email', $apple_email);

    if ($user) {
      // Usuario existe - hacer login
      // Guardar/actualizar Apple ID si no existe
      if (empty(get_user_meta($user->ID, 'userspn_apple_id', true))) {
        update_user_meta($user->ID, 'userspn_apple_id', $apple_id);
      }

      // Actualizar última conexión con Apple
      update_user_meta($user->ID, 'userspn_apple_last_login', current_time('timestamp'));

      // Login
      wp_set_current_user($user->ID);
      wp_set_auth_cookie($user->ID, true);
      do_action('wp_login', $user->user_login, $user);

      // Resetear flag de advertencia de inactividad
      update_user_meta($user->ID, 'userspn_inactive_warning_sent', 'off');

      return true;
    } else {
      // Usuario no existe - crear nuevo usuario
      $username = sanitize_user(str_replace('@', '_', $apple_email));

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
      $user_id = wp_create_user($username, $password, $apple_email);

      if (is_wp_error($user_id)) {
        return new WP_Error('user_creation_error', __('Error creating user account.', 'userspn'));
      }

      // Actualizar datos del usuario
      $update_data = [
        'ID' => $user_id,
      ];

      if (!empty($apple_first_name)) {
        $update_data['first_name'] = $apple_first_name;
      }

      if (!empty($apple_last_name)) {
        $update_data['last_name'] = $apple_last_name;
      }

      if (!empty($apple_first_name) || !empty($apple_last_name)) {
        $display_name = trim($apple_first_name . ' ' . $apple_last_name);
        if (!empty($display_name)) {
          $update_data['display_name'] = $display_name;
        }
      }

      if (!empty($update_data['first_name']) || !empty($update_data['last_name']) || !empty($update_data['display_name'])) {
        wp_update_user($update_data);
      }

      // Guardar Apple ID y metadatos
      update_user_meta($user_id, 'userspn_apple_id', $apple_id);
      update_user_meta($user_id, 'userspn_apple_last_login', current_time('timestamp'));
      update_user_meta($user_id, 'userspn_registration_method', 'apple');

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
      'userspn_apple_error' => $error_code,
      'userspn_apple_message' => urlencode($error_message),
    ], home_url());

    wp_safe_redirect($redirect_url);
    exit;
  }
}
