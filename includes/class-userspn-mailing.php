<?php
/**
 * Load the plugin mailing functions.
 *
 * Loads the plugin mailing functions getting email types and auxiliar features.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    USERSPN
 * @subpackage USERSPN/includes
 * @author     Padres en la Nube <info@padresenlanube.com>
 */
class USERSPN_Mailing {
  /**
   * Set email content type.
   *
   * @since    1.0.0
   */
  public function userspn_wp_mail_content_type(){
    return "text/html";
  }

  /**
   * Get activation emails.
   *
   * @since    1.0.0
   */
  public function userspn_get_email_activation($user_id) {
    if (class_exists('Polylang')) {
      if (empty(get_user_meta($user_id, 'userspn_lang', true))) {
        update_user_meta($user_id, 'userspn_lang', pll_current_language());
      }

      $activation_emails = get_posts(['fields' => 'ids', 'numberposts' => -1, 'post_type' => 'mailpn_mail', 'post_status' => 'publish', 'lang' => get_user_meta($user_id, 'userspn_lang', true), 'meta_key' => 'mailpn_type', 'meta_value' => 'email_verify_code', 'orderby' => 'ID', 'order' => 'ASC', ]);
    }else{
      $activation_emails = get_posts(['fields' => 'ids', 'numberposts' => -1, 'post_type' => 'mailpn_mail', 'post_status' => 'publish', 'meta_key' => 'mailpn_type', 'meta_value' => 'email_verify_code', 'orderby' => 'ID', 'order' => 'ASC', ]);
    }

    return $activation_emails;
  }

	/**
	 * Get welcome emails.
	 *
	 * @since    1.0.0
	 */
	public function userspn_get_email_welcome($user_id) {
    $email_welcome_atts = ['fields' => 'ids',
      'numberposts' => -1,
      'post_type' => 'mailpn_mail',
      'post_status' => 'publish', 
      'meta_key' => 'mailpn_type', 
      'meta_value' => 'email_welcome',
    ];

    if (class_exists('Polylang')) {
      $email_welcome_atts['lang'] = pll_current_language('slug');
    }

    $email_welcome = get_posts($email_welcome_atts);

    return $email_welcome;
  }

  /**
	 * Get newsletter emails.
	 *
	 * @since    1.0.0
	 */
  public function userspn_get_email_newsletter_welcome($user_id) {
    $newsletter_welcome_atts = ['fields' => 'ids',
      'numberposts' => -1,
      'post_type' => 'mailpn_mail',
      'post_status' => 'publish', 
      'meta_key' => 'mailpn_type', 
      'meta_value' => 'newsletter_welcome',
    ];

    if (class_exists('Polylang')) {
      $newsletter_welcome_atts['lang'] = pll_current_language('slug');
    }

    $newsletter_welcome = get_posts($newsletter_welcome_atts);

    return $newsletter_welcome;
  }      

  /**
   * Nesletter.
   *
   * @since    1.0.0
   */
  public function userspn_newsletter($atts) {
    // echo do_shortcode('[userspn-newsletter userspn_checkbox_policies="" text_btn="Text"]');
    $a = extract(shortcode_atts([
      'userspn_checkbox_policies' => '',
      'text_btn' => __('Submit', 'userspn'),
    ], $atts));

    $user_id = get_current_user_id();
    $policies_page_id = get_option('wp_page_for_privacy_policy');

    if (empty($userspn_checkbox_policies)) {
      if (!empty($policies_page_id)) {
        $userspn_checkbox_policies = __('I have read and accept', 'userspn') . ' ' . '<a href="' . get_permalink($policies_page_id) . '" target="_blank">' . get_the_title($policies_page_id) . '</a>';
      }else{
        $userspn_checkbox_policies = __('I have read and accept Terms and conditions.', 'userspn');
      }
    }

    ob_start();
    ?>    
      <div class="userspn-newsletter userspn-p-30 userspn-pt-0" id="userspn-newsletter">
        <?php if (!is_user_logged_in()): ?>
          <?php $userspn_newsletter_message = get_option('userspn_newsletter_message'); ?>

          <?php if (!empty($userspn_newsletter_message)): ?>
            <div class="userspn-newsletter-message">
              <?php echo wp_kses_post($userspn_newsletter_message); ?>
            </div>
          <?php endif ?>

          <form action="" method="post" id="userspn-newsletter-form" class="userspn-newsletter-form">
            <label for="userspn-newsletter-email"><?php esc_html_e('Your email', 'userspn'); ?></label><br>
            <input type="email" required name="userspn-newsletter-email" id="userspn-newsletter-email" class="userspn-input userspn-newsletter-email userspn-width-100-percent userspn-mt-10" value="" placeholder="<?php esc_html_e('your@email.com', 'userspn'); ?>">

            <?php if (get_option('userspn_honeypot_enabled') === 'on'): ?>
              <div class="userspn-newsletter-honeypot userspn-display-none-soft" aria-hidden="true">
                <label for="userspn-newsletter-honeypot" class="screen-reader-text"><?php esc_html_e('Leave this field empty', 'userspn'); ?></label>
                <input type="text" name="userspn_honeypot_field" id="userspn-newsletter-honeypot" class="userspn-input userspn-honeypot-field" tabindex="-1" autocomplete="off" value="">
              </div>
            <?php endif; ?>

            <div class="userspn-newsletter-checkbox userspn-mb-20">
              <input type="checkbox" required name="userspn-newsletter-policies" class="userspn-mr-10" id="userspn-newsletter-policies"/><label for="userspn-newsletter-policies"><?php echo wp_kses_post($userspn_checkbox_policies); ?></label><br>
            </div>
          
            <div class="userspn-text-align-right">
              <input type="submit" class="userspn-btn userspn-newsletter-btn" value="<?php echo esc_attr($text_btn); ?>"/><?php echo esc_html(USERSPN_Data::userspn_loader()); ?>
            </div>
          </form>
        <?php else: ?>
          <?php if (get_user_meta($user_id, 'userspn_notifications', true) != 'on'): ?>
            <p class="userspn-alert-warning"><?php esc_html_e('You have disabled your email notifications.', 'userspn'); ?> <a href="#" class="userspn-profile-popup-btn" data-userspn-action="notifications"><?php esc_html_e('Activate now', 'userspn'); ?></a></p>
          <?php else: ?>
            <p class="userspn-alert-success"><?php esc_html_e('You are already subscribed to the newsletter', 'userspn'); ?> <a href="#" class="userspn-profile-popup-btn" data-userspn-action="notifications"><?php esc_html_e('Manage your subscription', 'userspn'); ?></a></p>
          <?php endif ?>
        <?php endif ?>
      </div>
    <?php
    $userspn_return_string = ob_get_contents(); 
    ob_end_clean(); 
    return $userspn_return_string;
  }

  public function userspn_newsletter_activation_btn($user_id){
    ob_start();

    $url = add_query_arg([
        'userspn_action' => 'userspn_newsletter_activation',
        'user' => $user_id,
      ],
      home_url()
    );
    ?>
      <a href="<?php echo esc_url(wp_nonce_url(html_entity_decode($url), 'userspn_newsletter_activation', 'userspn_newsletter_activation_nonce')); ?>"><?php esc_html_e('Activate your account', 'userspn'); ?></a>
    <?php
    $userspn_return_string = ob_get_contents(); 
    ob_end_clean(); 
    return $userspn_return_string;
  }

    public function userspn_send_newsletter_activation_email($user_id, $userspn_email) {
        $max_emails_number = (!empty(get_option('userspn_newsletter_activation_max')) ? get_option('userspn_newsletter_activation_max') : 5);
        $activation_sent = get_user_meta($user_id, 'userspn_newsletter_activation_sent', true);

        // Check if 24 hours have passed since the last email was sent
        if (!empty($activation_sent) && is_array($activation_sent) && count($activation_sent) > 0) {
            $last_sent = end($activation_sent);
            if ((current_time('timestamp') - $last_sent) < 86400) {
                return 'userspn_newsletter_error_time_limit';
            }
        }

        if (empty($activation_sent) || (is_array($activation_sent) && count($activation_sent) < $max_emails_number)) {
            $userspn_meta_value = current_time('timestamp');
            if (empty($activation_sent)) {
                update_user_meta($user_id, 'userspn_newsletter_activation_sent', [$userspn_meta_value]);
            } else {
                $activation_sent[] = $userspn_meta_value;
                update_user_meta($user_id, 'userspn_newsletter_activation_sent', $activation_sent);
            }
            $plugin_mailing = $this;
            $activation_emails = $plugin_mailing->userspn_get_email_activation($user_id);
            if (class_exists('MAILPN')) {
                if (!empty($activation_emails)) {
                    foreach ($activation_emails as $email_id) {
                        do_shortcode('[mailpn-sender mailpn_type="email_verify_code" mailpn_user_to="' . $user_id . '" mailpn_subject="' . get_the_title($email_id) . '" mailpn_id="' . $email_id . '"]');
                    }
                } else {
                    do_shortcode('[mailpn-sender mailpn_type="email_verify_code" mailpn_user_to="' . $user_id . '" mailpn_subject="✅' . __('Activate your subscription', 'userspn') . '"]' . __('You have just subscribed to our newsletter.', 'userspn') . '<br><br>' . __('Please confirm your email address clicking in the link.', 'userspn') . '<br>' . $plugin_mailing->userspn_newsletter_activation_btn($user_id) . '[/mailpn-sender]');
                }
            } else {
                wp_mail($userspn_email, __('Activate your subscription', 'userspn'), __('Hello', 'userspn') . ' ' . $userspn_email . '.<br>' . __('You have just subscribed to our newsletter.', 'userspn') . '<br><br>' . __('Please confirm your email address clicking in the link.', 'userspn') . '<br>' . $plugin_mailing->userspn_newsletter_activation_btn($user_id));
            }
            return 'userspn_newsletter_success_activation_sent';
        } else {
            return 'userspn_newsletter_error_exceeded';
        }
    }
}