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
      <div class="userspn-newsletter userspn-p-30" id="userspn-newsletter">
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

            <div class="userspn-newsletter-checkbox userspn-mb-20">
              <input type="checkbox" required name="userspn-newsletter-policies" class="userspn-mr-10" id="userspn-newsletter-policies"/><label for="userspn-newsletter-policies"><?php echo wp_kses_post($userspn_checkbox_policies); ?></label><br>
            </div>
          
            <div class="userspn-text-align-right">
              <input type="submit" class="userspn-btn userspn-newsletter-btn" value="<?php echo esc_attr($text_btn); ?>"/><?php echo esc_html(USERSPN_Data::loader()); ?>
            </div>
          </form>
        <?php else: ?>
          <?php if (get_user_meta($user_id, 'userspn_notifications', true) != 'on'): ?>
            <p class="userspn-alert-warning"><?php esc_html_e('You have disabled your email notifications.', 'userspn'); ?> <a href="#" class="userspn-profile-popup-btn" data-userspn-action="notifications"><?php esc_html_e('Activate now', 'userspn'); ?></a></p>
          <?php else: ?>
            <p class="userspn-alert-success"><?php esc_html_e('You are already subscribed to the newsletter', 'userspn'); ?></p>
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
}