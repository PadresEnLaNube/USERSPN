<?php
/**
 * Provide a common footer area view for the plugin
 *
 * This file is used to markup the common footer facing aspects of the plugin.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 *
 * @package    USERSPN
 * @subpackage USERSPN/common/templates
 */

  if (!defined('ABSPATH')) exit; // Exit if accessed directly

  // Initialize userspn_data with default values if not already set
  if (!isset($GLOBALS['userspn_data'])) {
    $GLOBALS['userspn_data'] = [
      'user_id' => get_current_user_id(),
      'post_id' => is_admin() ? (!empty($GLOBALS['_REQUEST']['post']) ? $GLOBALS['_REQUEST']['post'] : 0) : get_the_ID()
    ];
  }
  
  $userspn_data = $GLOBALS['userspn_data'];

  // Check if the shortcode has been used on the page
  $shortcode_used = isset($GLOBALS['userspn_profile_shortcode_used']) && $GLOBALS['userspn_profile_shortcode_used'];
  
  if ($shortcode_used) {
    // If shortcode is used on the page, only render the popup markup (hidden) without the button
    // This ensures other buttons can still open the popup, but we don't show duplicate buttons
    $user_id = get_current_user_id();
    if (is_user_logged_in()) {
      // Render only the popup structure for logged in users (without the button)
      if (class_exists('USERSPN_Functions_Attachment')) {
        $functions_attachment = new USERSPN_Functions_Attachment();
      } else {
        $functions_attachment = null;
      }
      ?>
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

                <?php if ($functions_attachment && $functions_attachment->userspn_user_files_allowed($user_id)): ?>
                  <div class="userspn-tab-links" data-userspn-id="userspn-tab-files"><?php esc_html_e('Files', 'userspn'); ?></div>
                <?php endif ?>

                <?php if (get_option('userspn_sessions_enabled', 'on') === 'on'): ?>
                  <div class="userspn-tab-links" data-userspn-id="userspn-tab-sessions"><?php esc_html_e('Sessions', 'userspn'); ?></div>
                <?php endif ?>

                <?php if (get_option('userspn_passkeys_enabled') === 'on'): ?>
                  <div class="userspn-tab-links" data-userspn-id="userspn-tab-passkeys"><?php esc_html_e('Passkeys', 'userspn'); ?></div>
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

              <?php if ($functions_attachment && $functions_attachment->userspn_user_files_allowed($user_id)): ?>
                <div id="userspn-tab-files" class="userspn-tab-content userspn-display-none">
                  <?php echo do_shortcode('[userspn-user-files]'); ?>
                </div>
              <?php endif ?>

              <?php if (get_option('userspn_sessions_enabled', 'on') === 'on'): ?>
                <div id="userspn-tab-sessions" class="userspn-tab-content userspn-display-none">
                  <div class="userspn-p-10 userspn-mt-30">
                    <h4 style="margin:0 0 15px;"><?php esc_html_e('Active Sessions', 'userspn'); ?></h4>
                    <div id="userspn-sessions-list" style="min-height:60px;">
                      <p style="color:#787c82;"><?php esc_html_e('Loading sessions...', 'userspn'); ?></p>
                    </div>
                    <div style="margin-top:15px;">
                      <button type="button" class="userspn-btn userspn-btn-mini userspn-btn-transparent userspn-revoke-all-sessions-btn"><?php esc_html_e('Close all other sessions', 'userspn'); ?></button>
                    </div>
                  </div>
                  <script>
                    jQuery(function($){
                      function loadSessions(){
                        $.post(userspn.ajax_url || '<?php echo esc_js(admin_url('admin-ajax.php')); ?>', {
                          action: 'userspn_ajax_server',
                          userspn_ajax_type: 'userspn_get_user_sessions',
                          userspn_ajax_nonce: '<?php echo esc_js(wp_create_nonce('userspn-nonce')); ?>'
                        }, function(r){
                          if(!r.success) return;
                          var html='', s=r.data.sessions, cur=r.data.current_token_hash;
                          if(!s.length){ html='<p style="color:#787c82;"><?php echo esc_js(__('No active sessions.', 'userspn')); ?></p>'; }
                          for(var i=0;i<s.length;i++){
                            var isCurrent = s[i].token_hash===cur;
                            var exp = s[i].expiration ? new Date(s[i].expiration*1000).toLocaleString() : '';
                            html+='<div class="userspn-session-item" style="display:flex;align-items:center;justify-content:space-between;padding:10px;border:1px solid #e0e0e0;border-radius:6px;margin-bottom:8px;">';
                            html+='<div><strong>'+$('<span>').text(s[i].device_name).html()+'</strong>'+(isCurrent?' <span style="color:#00a32a;font-size:11px;">(<?php echo esc_js(__('current', 'userspn')); ?>)</span>':'');
                            html+='<br><span style="font-size:12px;color:#787c82;">IP: '+$('<span>').text(s[i].ip).html()+' &middot; '+$('<span>').text(exp).html()+'</span></div>';
                            if(!isCurrent){
                              html+='<button type="button" class="userspn-btn userspn-btn-mini userspn-btn-transparent userspn-revoke-session-btn" data-token="'+s[i].token_hash+'" style="white-space:nowrap;"><?php echo esc_js(__('Revoke', 'userspn')); ?></button>';
                            }
                            html+='</div>';
                          }
                          $('#userspn-sessions-list').html(html);
                        },'json');
                      }
                      loadSessions();
                      $(document).on('click','.userspn-revoke-session-btn',function(){
                        var $b=$(this),t=$b.data('token');
                        $b.prop('disabled',true);
                        $.post(userspn.ajax_url || '<?php echo esc_js(admin_url('admin-ajax.php')); ?>', {
                          action:'userspn_ajax_server', userspn_ajax_type:'userspn_revoke_session',
                          userspn_ajax_nonce:'<?php echo esc_js(wp_create_nonce('userspn-nonce')); ?>',
                          token_hash:t
                        },function(){ loadSessions(); },'json');
                      });
                      $(document).on('click','.userspn-revoke-all-sessions-btn',function(){
                        var $b=$(this); $b.prop('disabled',true);
                        $.post(userspn.ajax_url || '<?php echo esc_js(admin_url('admin-ajax.php')); ?>', {
                          action:'userspn_ajax_server', userspn_ajax_type:'userspn_revoke_all_sessions',
                          userspn_ajax_nonce:'<?php echo esc_js(wp_create_nonce('userspn-nonce')); ?>',
                          except_current:'true'
                        },function(){ $b.prop('disabled',false); loadSessions(); },'json');
                      });
                    });
                  </script>
                </div>
              <?php endif ?>

              <?php if (get_option('userspn_passkeys_enabled') === 'on'): ?>
                <div id="userspn-tab-passkeys" class="userspn-tab-content userspn-display-none">
                  <div class="userspn-p-10 userspn-mt-30">
                    <h4 style="margin:0 0 15px;"><?php esc_html_e('Passkeys', 'userspn'); ?></h4>
                    <p style="font-size:13px;color:#787c82;margin-bottom:15px;"><?php esc_html_e('Passkeys let you sign in using your fingerprint, face, or device PIN instead of a password.', 'userspn'); ?></p>
                    <div id="userspn-passkeys-list" style="min-height:40px;">
                      <p style="color:#787c82;"><?php esc_html_e('Loading...', 'userspn'); ?></p>
                    </div>
                    <div style="margin-top:15px;">
                      <button type="button" class="userspn-btn userspn-btn-mini userspn-passkey-register-btn"><?php esc_html_e('Add Passkey', 'userspn'); ?></button>
                    </div>
                  </div>
                  <script>
                    window.userspnRefreshPasskeyList = function(){
                      jQuery.post(userspn.ajax_url || '<?php echo esc_js(admin_url('admin-ajax.php')); ?>', {
                        action:'userspn_ajax_server', userspn_ajax_type:'userspn_passkey_get_list',
                        userspn_ajax_nonce:'<?php echo esc_js(wp_create_nonce('userspn-nonce')); ?>'
                      },function(r){
                        if(!r.success) return;
                        var $=jQuery, html='', list=r.data;
                        if(!list.length){ html='<p style="color:#787c82;"><?php echo esc_js(__('No passkeys registered.', 'userspn')); ?></p>'; }
                        for(var i=0;i<list.length;i++){
                          html+='<div class="userspn-passkey-item" style="display:flex;align-items:center;justify-content:space-between;padding:10px;border:1px solid #e0e0e0;border-radius:6px;margin-bottom:8px;">';
                          html+='<div><strong>'+$('<span>').text(list[i].device_name).html()+'</strong>';
                          html+='<br><span style="font-size:12px;color:#787c82;"><?php echo esc_js(__('Added:', 'userspn')); ?> '+$('<span>').text(list[i].created_at).html();
                          if(list[i].last_used_at) html+=' &middot; <?php echo esc_js(__('Last used:', 'userspn')); ?> '+$('<span>').text(list[i].last_used_at).html();
                          html+='</span></div>';
                          html+='<button type="button" class="userspn-btn userspn-btn-mini userspn-btn-transparent userspn-passkey-delete-btn" data-passkey-id="'+list[i].id+'"><?php echo esc_js(__('Delete', 'userspn')); ?></button>';
                          html+='</div>';
                        }
                        jQuery('#userspn-passkeys-list').html(html);
                      },'json');
                    };
                    jQuery(function(){ userspnRefreshPasskeyList(); });
                  </script>
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

                  <?php do_action('userspn_tab_advanced_content', $user_id); ?>
                </div>
              <?php endif ?>
            </div>
          </div>
        </div>
      </div>
      <?php
    } else {
      // Render only the popup structure for non-logged in users (without the button)
      ?>
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

                <?php if (get_option('userspn_passkeys_enabled') === 'on'): ?>
                  <div style="text-align:center;margin:10px 0;">
                    <div style="display:flex;align-items:center;gap:10px;margin:15px 0;">
                      <hr style="flex:1;border:none;border-top:1px solid #e0e0e0;">
                      <span style="font-size:12px;color:#787c82;"><?php esc_html_e('or', 'userspn'); ?></span>
                      <hr style="flex:1;border:none;border-top:1px solid #e0e0e0;">
                    </div>
                    <button type="button" class="userspn-btn userspn-btn-mini userspn-btn-transparent userspn-passkey-login-btn" style="width:100%;">
                      <span class="dashicons dashicons-admin-network" style="font-size:16px;width:16px;height:16px;vertical-align:middle;margin-right:4px;"></span>
                      <?php esc_html_e('Sign in with Passkey', 'userspn'); ?>
                    </button>
                  </div>
                <?php endif; ?>
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
      <?php
    }
  } else {
    // Always render the profile popup markup so buttons can open it, even if disabled
    echo do_shortcode('[userspn-profile]');
  }

  // Only run validation workflows when not disabled
  if (get_option('userspn_disabled') != 'on') {
    if (is_user_logged_in()) {
      $plugin_user = new USERSPN_Functions_User();
      $plugin_user->userspn_profile_fields_validation();
    }
  }
?>

<div id="userspn-main-message" class="userspn-main-message userspn-display-none-soft userspn-z-index-top" style="display:none;" data-user-id="<?php echo esc_attr($userspn_data['user_id']); ?>" data-post-id="<?php echo esc_attr($userspn_data['post_id']); ?>">
  <span id="userspn-main-message-span"></span><i class="material-icons-outlined userspn-vertical-align-bottom userspn-ml-20 userspn-cursor-pointer userspn-color-white userspn-close-icon">close</i>

  <div id="userspn-bar-wrapper">
  	<div id="userspn-bar"></div>
  </div>
</div>