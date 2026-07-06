<?php
/**
 * GitHub OAuth REST API endpoints
 *
 * Handles GitHub OAuth callbacks and authentication
 *
 * @link       padresenlanube.com/
 * @since      1.1.33
 * @package    USERSPN
 * @subpackage USERSPN/includes
 * @author     Padres en la Nube <info@padresenlanube.com>
 */
class USERSPN_GitHub_OAuth
{
  /**
   * Register REST API routes and AJAX handlers
   */
  public function register_routes()
  {
    // Register REST API endpoint (primary method)
    register_rest_route('userspn/v1', '/github-callback', [
      'methods' => ['GET', 'POST'],
      'callback' => [$this, 'handle_github_callback'],
      'permission_callback' => [$this, 'public_permission_check'],
    ]);
  }

  /**
   * Register AJAX handler for callback (fallback method)
   */
  public function register_ajax_callback()
  {
    add_action('wp_ajax_nopriv_userspn_github_callback', [$this, 'handle_github_callback_ajax']);
    add_action('wp_ajax_userspn_github_callback', [$this, 'handle_github_callback_ajax']);
  }

  /**
   * Handle GitHub OAuth callback via AJAX (fallback)
   */
  public function handle_github_callback_ajax()
  {
    // Create a fake request object from GET parameters
    $fake_request = new stdClass();
    $fake_request->params = $_GET;

    $result = $this->handle_github_callback_internal($_GET);

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
   * Handle GitHub OAuth callback (REST API wrapper)
   */
  public function handle_github_callback($request)
  {
    $params = [];
    $params['code'] = $request->get_param('code');
    $params['state'] = $request->get_param('state');
    $params['error'] = $request->get_param('error');

    return $this->handle_github_callback_internal($params);
  }

  /**
   * Handle GitHub OAuth callback (internal logic)
   */
  private function handle_github_callback_internal($params)
  {
    // Verificar que GitHub Login esté habilitado
    if (get_option('userspn_github_login_enabled') != 'on') {
      return $this->redirect_with_error('github_disabled', __('GitHub login is not available.', 'userspn'));
    }

    $client_id = get_option('userspn_github_client_id');
    $client_secret = get_option('userspn_github_client_secret');

    if (empty($client_id) || empty($client_secret)) {
      return $this->redirect_with_error('not_configured', __('GitHub login is not properly configured.', 'userspn'));
    }

    // Obtener parámetros
    $code = isset($params['code']) ? $params['code'] : '';
    $state = isset($params['state']) ? $params['state'] : '';
    $error = isset($params['error']) ? $params['error'] : '';

    // Verificar si el usuario canceló
    if (!empty($error)) {
      return $this->redirect_with_error('cancelled', __('GitHub login was cancelled.', 'userspn'));
    }

    // Verificar código
    if (empty($code)) {
      return $this->redirect_with_error('no_code', __('No authorization code received.', 'userspn'));
    }

    // Verificar state
    if ($state !== 'userspn_github_login') {
      return $this->redirect_with_error('invalid_state', __('Invalid state parameter.', 'userspn'));
    }

    // Intercambiar código por token de acceso
    $redirect_uri = admin_url('admin-ajax.php?action=userspn_github_callback');

    $token_response = wp_remote_post('https://github.com/login/oauth/access_token', [
      'headers' => [
        'Accept' => 'application/json',
      ],
      'body' => [
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'code' => $code,
        'redirect_uri' => $redirect_uri,
      ],
      'timeout' => 30,
    ]);

    if (is_wp_error($token_response)) {
      return $this->redirect_with_error('token_error', __('Error connecting to GitHub.', 'userspn'));
    }

    $token_data = json_decode(wp_remote_retrieve_body($token_response), true);

    if (empty($token_data['access_token'])) {
      return $this->redirect_with_error('no_token', __('Error obtaining access token.', 'userspn'));
    }

    // Obtener información del usuario de GitHub
    $user_info_response = wp_remote_get('https://api.github.com/user', [
      'headers' => [
        'Authorization' => 'Bearer ' . $token_data['access_token'],
        'Accept' => 'application/json',
        'User-Agent' => 'USERSPN WordPress Plugin',
      ],
      'timeout' => 30,
    ]);

    if (is_wp_error($user_info_response)) {
      return $this->redirect_with_error('userinfo_error', __('Error getting user information.', 'userspn'));
    }

    $user_info = json_decode(wp_remote_retrieve_body($user_info_response), true);

    // GitHub puede no proporcionar email en la respuesta principal
    // Necesitamos obtenerlo de un endpoint separado
    $email = isset($user_info['email']) ? $user_info['email'] : '';

    if (empty($email)) {
      // Obtener emails del usuario
      $emails_response = wp_remote_get('https://api.github.com/user/emails', [
        'headers' => [
          'Authorization' => 'Bearer ' . $token_data['access_token'],
          'Accept' => 'application/json',
          'User-Agent' => 'USERSPN WordPress Plugin',
        ],
        'timeout' => 30,
      ]);

      if (!is_wp_error($emails_response)) {
        $emails = json_decode(wp_remote_retrieve_body($emails_response), true);

        // Buscar el email primario y verificado
        if (is_array($emails)) {
          foreach ($emails as $email_data) {
            if (isset($email_data['primary']) && $email_data['primary'] && isset($email_data['verified']) && $email_data['verified']) {
              $email = $email_data['email'];
              break;
            }
          }

          // Si no hay primario verificado, usar el primero verificado
          if (empty($email)) {
            foreach ($emails as $email_data) {
              if (isset($email_data['verified']) && $email_data['verified']) {
                $email = $email_data['email'];
                break;
              }
            }
          }
        }
      }
    }

    if (empty($email)) {
      return $this->redirect_with_error('no_email', __('No email address received from GitHub.', 'userspn'));
    }

    $user_info['email'] = $email;

    // Procesar usuario
    $result = $this->process_github_user($user_info);

    if (is_wp_error($result)) {
      return $this->redirect_with_error('processing_error', $result->get_error_message());
    }

    // Redirigir con éxito
    $redirect_url = home_url();
    wp_safe_redirect($redirect_url);
    exit;
  }

  /**
   * Process GitHub user information
   */
  private function process_github_user($user_info)
  {
    $github_email = sanitize_email($user_info['email']);
    $github_id = isset($user_info['id']) ? sanitize_text_field($user_info['id']) : '';
    $github_name = isset($user_info['name']) ? sanitize_text_field($user_info['name']) : '';
    $github_login = isset($user_info['login']) ? sanitize_text_field($user_info['login']) : '';
    $github_avatar = isset($user_info['avatar_url']) ? esc_url_raw($user_info['avatar_url']) : '';

    // Verificar si el usuario ya existe por email
    $user = get_user_by('email', $github_email);

    if ($user) {
      // Usuario existe - hacer login
      // Guardar/actualizar GitHub ID si no existe
      if (empty(get_user_meta($user->ID, 'userspn_github_id', true))) {
        update_user_meta($user->ID, 'userspn_github_id', $github_id);
      }

      // Actualizar última conexión con GitHub
      update_user_meta($user->ID, 'userspn_github_last_login', current_time('timestamp'));

      // Login
      wp_set_current_user($user->ID);
      wp_set_auth_cookie($user->ID, true);
      do_action('wp_login', $user->user_login, $user);

      // Resetear flag de advertencia de inactividad
      update_user_meta($user->ID, 'userspn_inactive_warning_sent', 'off');

      return true;
    } else {
      // Usuario no existe - crear nuevo usuario
      $username = sanitize_user(!empty($github_login) ? $github_login : str_replace('@', '_', $github_email));

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
      $user_id = wp_create_user($username, $password, $github_email);

      if (is_wp_error($user_id)) {
        return new WP_Error('user_creation_error', __('Error creating user account.', 'userspn'));
      }

      // Actualizar datos del usuario
      $update_data = [
        'ID' => $user_id,
      ];

      if (!empty($github_name)) {
        $update_data['display_name'] = $github_name;
      }

      if (!empty($update_data['display_name'])) {
        wp_update_user($update_data);
      }

      // Guardar GitHub ID y metadatos
      update_user_meta($user_id, 'userspn_github_id', $github_id);
      update_user_meta($user_id, 'userspn_github_last_login', current_time('timestamp'));
      update_user_meta($user_id, 'userspn_registration_method', 'github');

      // Guardar avatar de GitHub si está disponible
      if (!empty($github_avatar)) {
        update_user_meta($user_id, 'userspn_github_avatar', $github_avatar);
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
      'userspn_github_error' => $error_code,
      'userspn_github_message' => urlencode($error_message),
    ], home_url());

    wp_safe_redirect($redirect_url);
    exit;
  }
}
