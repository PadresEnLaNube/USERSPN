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
 * @author     Padres en la Nube <info@padresenlanube.com>
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
					'error_content' => esc_html(__('Security check failed: Nonce is required.', 'userspn'))
				]);
				exit();
			}

			$nonce_verified = wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['userspn_ajax_nopriv_nonce'])), 'userspn-nonce');

			if (!$nonce_verified) {
				echo wp_json_encode([
					'error_key' => 'userspn_nonce_ajax_nopriv_error_invalid',
					'error_content' => esc_html(__('Security check failed: Invalid nonce.', 'userspn'))
				]);
				exit();
			}

			$userspn_ajax_nopriv_type = USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_ajax_nopriv_type']));

			$ajax_keys = !empty($_POST['ajax_keys']) ? wp_unslash($_POST['ajax_keys']) : [];
			$key_value = [];

			if (!empty($ajax_keys)) {
				foreach ($ajax_keys as $key) {
					if (strpos($key['id'], '[]') !== false) {
						$clear_key = str_replace('[]', '', $key['id']);
						${$clear_key} = $key_value[$clear_key] = [];

						if (!empty($_POST[$clear_key])) {
							foreach (wp_unslash($_POST[$clear_key]) as $multi_key => $multi_value) {
								$final_value = !empty($_POST[$clear_key][$multi_key]) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST[$clear_key][$multi_key]), $key['node'], $key['type']) : '';
								${$clear_key}[$multi_key] = $key_value[$clear_key][$multi_key] = $final_value;
							}
						}else{
							${$clear_key} = '';
							$key_value[$clear_key][$multi_key] = '';
						}
					}else{
						$key_id = !empty($_POST[$key['id']]) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST[$key['id']]), $key['node'], $key['type']) : '';
						${$key['id']} = $key_value[$key['id']] = $key_id;
					}
				}
			}

			switch ($userspn_ajax_nopriv_type) {
				case 'userspn_lostpassword':
					if (!wp_verify_nonce($_POST['userspn_ajax_nopriv_nonce'], 'userspn-nonce')) {
						wp_send_json_error(array(
							'error_key' => 'invalid_nonce',
							'error_content' => __('Security check failed. Please try again.', 'userspn')
						));
					}
					wp_send_json_success(array(
						'message' => __('Security check passed. Proceeding with password reset...', 'userspn')
					));
					break;
				case 'userspn_form_save':
					$userspn_form_type = !empty($_POST['userspn_form_type']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_form_type'])) : '';

					if (!empty($key_value) && !empty($userspn_form_type)) {
						$userspn_form_id = !empty($_POST['userspn_form_id']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_form_id'])) : '';
						$userspn_form_subtype = !empty($_POST['userspn_form_subtype']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_form_subtype'])) : '';
						$user_id = !empty($_POST['userspn_form_user_id']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_form_user_id'])) : '';
						$post_id = !empty($_POST['userspn_form_post_id']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_form_post_id'])) : '';

						if (($userspn_form_type == 'user' && empty($user_id)) || ($userspn_form_type == 'post' && (empty($post_id) && !(!empty($userspn_form_subtype) && in_array($userspn_form_subtype, ['post_new', 'post_edit'])))) || ($userspn_form_type == 'option' && !is_user_logged_in())) {
							session_start();

							$_SESSION['userspn_form'] = [];
							$_SESSION['userspn_form'][$userspn_form_id] = [];
							$_SESSION['userspn_form'][$userspn_form_id]['form_type'] = $userspn_form_type;
							$_SESSION['userspn_form'][$userspn_form_id]['values'] = $key_value;

							if (!empty($post_id)) {
								$_SESSION['userspn_form'][$userspn_form_id]['post_id'] = $post_id;
							}

							echo wp_json_encode(['error_key' => 'userspn_form_save_error_unlogged', ]);exit();
						}else{
							switch ($userspn_form_type) {
								case 'user':
									if (empty($user_id)) {
										if (USERSPN_Functions_User::userspn_user_is_admin(get_current_user_id())) {
											$user_login = !empty($_POST['user_login']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['user_login'])) : '';
											$user_password = !empty($_POST['user_password']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['user_password'])) : '';
											$user_email = !empty($_POST['user_email']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['user_email'])) : '';

											$user_id = USERSPN_Functions_User::userspn_user_insert($user_login, $user_password, $user_email);
										}
									}

									foreach ($key_value as $key => $value) {
										update_user_meta($user_id, $key, $value);
									}

									update_user_meta($user_id, 'userspn_user_updated', strtotime('now'));
									update_user_meta($user_id, 'dev_first_key_value', $key_value);
									do_action('userspn_form_save', $user_id, $key_value, $userspn_form_type, $userspn_form_subtype);
									do_action('userspn_profile_edit', $user_id, $key_value);
									break;
								case 'post':
									if (empty($userspn_form_subtype) || !in_array($userspn_form_subtype, ['post_new', 'post_edit'])) {
										if (empty($post_id)) {
											if (USERSPN_Functions_User::userspn_user_is_admin(get_current_user_id())) {
												$post_id = USERSPN_Functions_Post::userspn_insert_post($title, '', '', sanitize_title($title), $post_type, 'publish', get_current_user_id());
											}
										}

										foreach ($key_value as $key => $value) {
											update_post_meta($post_id, $key, $value);
										}
									}

									do_action('userspn_form_save', $post_id, $key_value, $userspn_form_type, $userspn_form_subtype);
									break;
								case 'option':
									if (USERSPN_Functions_User::userspn_user_is_admin(get_current_user_id())) {
										foreach ($key_value as $key => $value) {
											update_option($key, $value);
										}
									}

									do_action('userspn_form_save', 0, $key_value, $userspn_form_type, $userspn_form_subtype);
									break;
							}

							if ($userspn_form_type == 'option') {
								update_option('userspn_form_changed', true);
							}

							$popup = in_array($userspn_form_subtype, ['post_new', 'post_edit']) ? 'close' : '';
							$update = in_array($userspn_form_subtype, ['post_new', 'post_edit']) ? 'list' : '';

							echo wp_json_encode(['error_key' => '', 'popup' => $popup, 'update' => $update]);exit();
						}
					}else{
						echo wp_json_encode(['error_key' => 'userspn_form_save_error', ]);exit();
					}
					break;
				case 'userspn_profile_create':
					$userspn_email = !empty($_POST['userspn_email']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_email'])) : '';
					$userspn_password = !empty($_POST['userspn_password']) ? USERSPN_Forms::userspn_sanitizer(wp_unslash($_POST['userspn_password'])) : '';
					$plugin_user = new USERSPN_Functions_User();

					if (!empty($userspn_email) && !empty($userspn_password)) {
						if (email_exists($userspn_email)) {
							echo 'userspn_profile_create_existing';exit();
						}else{
							// Prepare user data for security validation
							$user_data = [
								'email' => $userspn_email,
								'first_name' => $key_value['first_name'] ?? '',
								'last_name' => $key_value['last_name'] ?? '',
								'description' => $key_value['description'] ?? ''
							];

							// Perform security validation
							$security_result = USERSPN_Security::validate_registration_security($_POST, $user_data);
							if (is_wp_error($security_result)) {
								// Log security event
								USERSPN_Security::log_security_event('registration_blocked', $security_result->get_error_message(), [
									'email' => $userspn_email,
									'ip' => USERSPN_Security::get_user_ip()
								]);
								echo 'userspn_profile_create_security_error';exit();
							}

							$userspn_login = sanitize_title(substr($userspn_email, 0, strpos($userspn_email, '@')) . '-' . bin2hex(openssl_random_pseudo_bytes(4)));
							$user_id = USERSPN_Functions_User::userspn_user_insert($userspn_login, $userspn_password, $userspn_email, '', '', $userspn_login, $userspn_login, $userspn_login, '', ['subscriber'], [
								['userspn_secret_token' => bin2hex(openssl_random_pseudo_bytes(16))],
							]);

							// Store registration IP and user agent for bot analysis
							if ($user_id) {
								update_user_meta($user_id, 'userspn_registration_ip', USERSPN_Security::get_user_ip());
								update_user_meta($user_id, 'userspn_registration_user_agent', $_SERVER['HTTP_USER_AGENT'] ?? '');
							}

							foreach ($key_value as $key => $value) {
								if (!in_array($key, ['action', 'userspn_ajax_nopriv', 'userspn_ajax_nopriv_type', 'userspn_email', 'userspn_password', 'g-recaptcha-response', 'userspn_honeypot_field'])) {
									update_user_meta($user_id, $key, $value);
								}
							}

							// Log successful registration
							USERSPN_Security::log_security_event('registration_success', 'User registration completed successfully', [
								'user_id' => $user_id,
								'email' => $userspn_email,
								'ip' => USERSPN_Security::get_user_ip()
							]);

							do_action('userspn_profile_create', $user_id, $key_value);
							echo 'userspn_profile_create_success';exit();
						}
					}else{
						echo 'userspn_profile_create_error';exit();
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

						// Ensure the subscriber role is present for existing users
						if ($user_id) {
							$user_object = new WP_User($user_id);
							if (!in_array('userspn_newsletter_subscriber', (array) $user_object->roles, true)) {
								$user_object->add_role('userspn_newsletter_subscriber');
							}
						}

						if (get_option('userspn_newsletter_activation') == 'on') {
							$plugin_mailing = new USERSPN_Mailing();
							$result = $plugin_mailing->userspn_send_newsletter_activation_email($user_id, $userspn_email);
							echo $result;exit();
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
								// Fallback: send a simple welcome email when MAILPN is not available
								$subject = __('Welcome to our newsletter', 'userspn');
								$body = __('Hello', 'userspn') . ' ' . esc_html($userspn_email) . ".<br>" . __('You have been subscribed to our newsletter. Welcome aboard!', 'userspn');
								wp_mail($userspn_email, $subject, $body);
							}

							echo 'userspn_newsletter_success';exit();
						}
					} else {
						echo 'userspn_newsletter_error';exit();
					}
				break;
			}

			echo wp_json_encode(['error_key' => '', ]);exit();
		}
	}
}