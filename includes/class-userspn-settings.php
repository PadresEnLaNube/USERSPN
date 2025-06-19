<?php
/**
 * Plugin menus manager.
 *
 * This class defines plugin menus, both in dashboard or in front-end.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    USERSPN
 * @subpackage USERSPN/includes
 * @author     Padres en la Nube <info@padresenlanube.com>
 */
class USERSPN_Settings {
  public function get_options() {
    global $wp_roles;
    $userspn_roles = [];
    foreach ($wp_roles->roles as $role_key => $role_value) {
      if ($role_key != 'administrator') {
        $userspn_roles[$role_key] = $role_value['name'];
      }
    }

    $userspn_options = [];
    $userspn_options['userspn_section_user_popup_start'] = [
      'section' => 'start',
      'label' => __('Profile popup', 'userspn'),
    ];
      $userspn_options['userspn_disabled'] = [
        'id' => 'userspn_disabled',
        'class' => 'userspn-input userspn-width-100-percent',
        'input' => 'input',
        'type' => 'checkbox',
        'label' => __('Disable Profile popup', 'userspn'),
        'description' => __('You can disable the popup access temporarily by checking on this checkbox.', 'userspn'),
      ];
      $userspn_options['userspn_user_name'] = [
        'id' => 'userspn_user_name',
        'class' => 'userspn-input userspn-width-100-percent',
        'input' => 'input',
        'type' => 'checkbox',
        'parent' => 'this',
        'label' => __('Name and surname in profile', 'userspn'),
        'description' => __('If you check this option, the system will ask for the name and surname of the contact based on the WordPress custom database structure.', 'userspn'),
      ];
        $userspn_options['userspn_user_name_compulsory'] = [
          'id' => 'userspn_user_name_compulsory',
          'class' => 'userspn-input userspn-width-100-percent',
          'input' => 'input',
          'type' => 'checkbox',
          'parent' => 'userspn_user_name',
          'parent_option' => 'on',
          'label' => __('Name compulsory', 'userspn'),
        ];
        $userspn_options['userspn_user_surname_compulsory'] = [
          'id' => 'userspn_user_surname_compulsory',
          'class' => 'userspn-input userspn-width-100-percent',
          'input' => 'input',
          'type' => 'checkbox',
          'parent' => 'userspn_user_name',
          'parent_option' => 'on',
          'label' => __('Surname compulsory', 'userspn'),
        ];
      $userspn_options['userspn_user_register_fields'] = [
        'id' => 'userspn_user_register_fields',
        'input' => 'html',
        'html_content' => '[userspn-user-register-fields]',
        'label' => __('Extra fields in the user profile', 'userspn'),
        'description' => __('You can include the fields that will be asked in the profile popup. The base login or registration will include email and password. The fields that you add here will be included below the two base email and password fields.', 'userspn'),
      ];
      $userspn_options['userspn_profile_completion'] = [
        'id' => 'userspn_profile_completion',
        'class' => 'userspn-input userspn-width-100-percent',
        'input' => 'input',
        'type' => 'checkbox',
        'parent' => 'this',
        'label' => __('Show profile completion block', 'userspn'),
        'description' => __('If enabled, a profile completion block will be shown in the profile popup.', 'userspn'),
      ];
      $userspn_options['userspn_profile_completion_fields'] = [
        'id' => 'userspn_profile_completion_fields',
        'class' => 'userspn-input userspn-width-100-percent',
        'input' => 'html_multi',
        'parent' => 'userspn_profile_completion',
        'parent_option' => 'on',
        'label' => __('Profile completion field', 'userspn'),
        'description' => __('Select the page and meta key to track for profile completion.', 'userspn'),
        'html_multi_fields' => [
          $userspn_profile_completion_field_page_id = [
            'id' => 'userspn_profile_completion_field_page_id',
            'class' => 'userspn-input userspn-width-100-percent',
            'input' => 'input',
            'type' => 'number',
            'multiple' => true,
            'label' => __('Page ID', 'userspn'),
            'placeholder' => __('Enter page ID', 'userspn'),
          ],
          $userspn_profile_completion_field_meta_key  = [
            'id' => 'userspn_profile_completion_field_meta_key',
            'class' => 'userspn-input userspn-width-100-percent',
            'input' => 'input',
            'type' => 'text',
            'multiple' => true,
            'label' => __('Meta key', 'userspn'),
            'placeholder' => __('Enter meta key', 'userspn'),
          ]
        ]
      ];
      $userspn_options['userspn_user_register'] = [
        'id' => 'userspn_user_register',
        'class' => 'userspn-input userspn-width-100-percent',
        'input' => 'input',
        'type' => 'checkbox',
        'parent' => 'this',
        'label' => __('Front-end users registration', 'userspn'),
        'description' => __('This option allows users to create their account from the front-end profile popup.', 'userspn'),
      ];
      $userspn_options['userspn_user_image'] = [
        'id' => 'userspn_user_image',
        'class' => 'userspn-input userspn-width-100-percent',
        'input' => 'input',
        'type' => 'checkbox',
        'label' => __('Custom Profile Picture - PFP', 'userspn'),
        'description' => __('This options allow contacts to include a custom Profile Pictures in their profiles.', 'userspn'),
      ];
      $userspn_options['userspn_image_custom'] = [
        'id' => 'userspn_image_custom',
        'class' => 'userspn-input userspn-width-100-percent',
        'input' => 'input',
        'type' => 'checkbox',
        'parent' => 'this',
        'label' => __('Random images in avatar block', 'userspn'),
        'description' => __('This option allow you to include custom images to be shown randomly in the avatar image block.', 'userspn'),
      ];
        $userspn_options['userspn_image_custom_ids'] = [
          'id' => 'userspn_image_custom_ids',
          'class' => 'userspn-input userspn-width-100-percent userspn-pl-20',
          'input' => 'image',
          'multiple' => 'true',
          'parent' => 'userspn_image_custom',
          'parent_option' => 'on',
          'label' => __('Select avatar block images', 'userspn'),
          'placeholder' => __('Select avatar block images', 'userspn'),
          'description' => __('This option allow you to include custom images to be shown randomly in the avatar image block.', 'userspn'),
        ];
      $userspn_options['userspn_user_notifications'] = [
        'id' => 'userspn_user_notifications',
        'class' => 'userspn-input userspn-width-100-percent',
        'input' => 'input',
        'type' => 'checkbox',
        'label' => __('Contact notifications', 'userspn'),
        'description' => __('This option shows a notifications tab in the profile popup that allows users to manage their notifications.', 'userspn'),
      ];
      $userspn_options['userspn_user_files'] = [
        'id' => 'userspn_user_files',
        'class' => 'userspn-input userspn-width-100-percent',
        'input' => 'input',
        'type' => 'checkbox',
        'parent' => 'this',
        'label' => __('Users files', 'userspn'),
        'description' => __('This option allows users to upload and manage their files to the system.', 'userspn') . ' ' . __('Current file max size allowed:', 'userspn') . ' ' . self::userspn_bytes_format(wp_max_upload_size()) . '.',
      ];
        $userspn_options['userspn_user_files_roles'] = [
          'id' => 'userspn_user_files_roles',
          'class' => 'userspn-select userspn-width-100-percent',
          'input' => 'select',
          'options' => $userspn_roles,
          'multiple' => 'true',
          'parent' => 'userspn_user_files',
          'parent_option' => 'on',
          'label' => __('Roles allowed to upload files', 'userspn'),
          'placeholder' => __('Roles allowed to upload files', 'userspn'),
          'description' => __('This option allow only specific roles to have the option to upload files to the system. If this option is empty and "User files" field is on, all the roles will be able to upload files to the system. Administrators will be allways allowed.', 'userspn'),
        ];
      $userspn_options['userspn_user_advanced'] = [
        'id' => 'userspn_user_advanced',
        'class' => 'userspn-input userspn-width-100-percent',
        'input' => 'input',
        'type' => 'checkbox',
        'parent' => 'this',
        'label' => __('Advanced options in profile', 'userspn'),
        'description' => __('This option allows users to do user from the advanced option in the profile popup.', 'userspn'),
      ];
        $userspn_options['userspn_user_change_password'] = [
        'id' => 'userspn_user_change_password',
          'class' => 'userspn-input userspn-width-100-percent',
          'input' => 'input',
          'parent' => 'this userspn_user_advanced',
          'parent_option' => 'on',
          'type' => 'checkbox',
          'label' => __('Change password access', 'userspn'),
          'description' => __('This option allows users to access to the password change integrated function.', 'userspn'),
        ];
        $userspn_options['userspn_user_change_password_wp_defaults'] = [
        'id' => 'userspn_user_change_password_wp_defaults',
          'class' => 'userspn-input userspn-width-100-percent',
          'input' => 'input',
          'parent' => 'userspn_user_change_password',
          'parent_option' => 'on',
          'type' => 'checkbox',
          'label' => __('Use WordPress password recovery system', 'userspn'),
          'description' => __('This option force system to use the password recovery WordPress tool by default. It will generate an unique nonce and will send it to the user to change password safely.', 'userspn'),
        ];
        $userspn_options['userspn_user_remove'] = [
        'id' => 'userspn_user_remove',
          'class' => 'userspn-input userspn-width-100-percent',
          'input' => 'input',
          'type' => 'checkbox',
          'parent' => 'userspn_user_advanced',
          'parent_option' => 'on',
          'label' => __('Front-end users removal', 'userspn'),
          'description' => __('This option allows users to remove their account from the advanced option in the profile popup.', 'userspn'),
        ];
    $userspn_options['userspn_section_user_popup_end'] = [
      'section' => 'end',
    ];
    
    $userspn_options['userspn_section_dashboard_start'] = [
      'section' => 'start',
      'label' => __('Dashboard', 'userspn'),
    ];
      $userspn_options['userspn_admin_page'] = [
        'id' => 'userspn_admin_page',
        'class' => 'userspn-input userspn-width-100-percent',
        'input' => 'input',
        'type' => 'checkbox',
        'parent' => 'this',
        'label' => __('Dashboard main login page', 'userspn'),
        'description' => __('This option will modify the dashboard main login page aspect.', 'userspn') . ' (<a target="_blank" href="' . home_url('wp-login.php') . '">' . home_url('wp-login.php') . '</a>)',
      ];
        $userspn_options['userspn_admin_page_logo'] = [
          'id' => 'userspn_admin_page_logo',
          'class' => 'userspn-input userspn-width-100-percent',
          'input' => 'image',
          'parent' => 'userspn_admin_page',
          'parent_option' => 'on',
          'label' => __('Dashboard main login page logo', 'userspn'),
          'description' => __('This option changes the main login page logo.', 'userspn'),
        ];
        $userspn_options['userspn_admin_page_logo_link_text'] = [
          'id' => 'userspn_admin_page_logo_link_text',
          'class' => 'userspn-input userspn-width-100-percent',
          'input' => 'input',
          'type' => 'text',
          'value' => get_bloginfo('name'),
          'parent' => 'userspn_admin_page',
          'parent_option' => 'on',
          'label' => __('Dashboard main login page logo link text', 'userspn'),
          'placeholder' => __('Dashboard main login page header logo link text', 'userspn'),
          'description' => __('This option changes your main login page header logo link text. By default "Powered by WordPress".', 'userspn'),
        ];
        $userspn_options['userspn_admin_page_logo_link'] = [
          'id' => 'userspn_admin_page_logo_link',
          'class' => 'userspn-input userspn-width-100-percent',
          'input' => 'input',
          'type' => 'url',
          'value' => get_bloginfo('url'),
          'parent' => 'userspn_admin_page',
          'parent_option' => 'on',
          'label' => __('Dashboard main login page logo link', 'userspn'),
          'placeholder' => __('Dashboard main login page logo link', 'userspn'),
          'description' => __('This option changes your main login page logo link. By default https://wordpress.org/.', 'userspn'),
        ];
        $userspn_options['userspn_admin_page_logo_css'] = [
          'id' => 'userspn_admin_page_logo_css',
          'class' => 'userspn-input userspn-width-100-percent',
          'input' => 'textarea',
          'parent' => 'userspn_admin_page',
          'parent_option' => 'on',
          'label' => __('Dashboard main login page CSS', 'userspn'),
          'placeholder' => __('Dashboard main login page CSS', 'userspn'),
        ];
        $userspn_options['userspn_dashboard_logo'] = [
          'id' => 'userspn_dashboard_logo',
          'class' => 'userspn-input userspn-width-100-percent',
          'input' => 'input',
          'type' => 'checkbox',
          'parent' => 'userspn_admin_page',
          'parent_option' => 'on',
          'label' => __('Dashboard main logo', 'userspn'),
          'description' => __('This option will modify also the dashboard main top left logo using the image set above.', 'userspn'),
        ];
        $userspn_options['userspn_dashboard_logo_link'] = [
          'id' => 'userspn_dashboard_logo_link',
          'class' => 'userspn-input userspn-width-100-percent',
          'input' => 'input',
          'type' => 'checkbox',
          'parent' => 'userspn_admin_page',
          'parent_option' => 'on',
          'label' => __('Dashboard main logo link', 'userspn'),
          'description' => __('This option will modify also the dashboard main top left logo link using the link and text set above.', 'userspn'),
        ];
      $userspn_options['userspn_admin_access_removal'] = [
        'id' => 'userspn_admin_access_removal',
        'class' => 'userspn-input userspn-width-100-percent',
        'input' => 'input',
        'type' => 'checkbox',
        'parent' => 'this',
        'label' => __('Disable dashboard access', 'userspn'),
        'description' => __('You can revoke the accesss to certain roles to the administrator dashboard completely.', 'userspn'),
      ];
        $userspn_options['userspn_admin_access_roles'] = [
          'id' => 'userspn_admin_access_roles',
          'class' => 'userspn-select userspn-width-100-percent',
          'input' => 'select',
          'options' => $userspn_roles,
          'multiple' => 'true',
          'parent' => 'userspn_admin_access_removal',
          'parent_option' => 'on',
          'label' => __('Enabled roles', 'userspn'),
          'description' => __('Set up the roles to get dashboard access. This option takes priority over the previous one. If a role appears in both fields, this "Enabled roles" field will be the one taken into account and the access will be provided.', 'userspn'),
        ];
      $userspn_options['userspn_admin_bar_removal'] = [
        'id' => 'userspn_admin_bar_removal',
        'class' => 'userspn-input userspn-width-100-percent',
        'input' => 'input',
        'type' => 'checkbox',
        'parent' => 'this',
        'label' => __('Disable admin bar', 'userspn'),
        'description' => __('You can remove the front-end admin bar by contacts role.', 'userspn'),
      ];
        $userspn_options['userspn_admin_bar_access_roles'] = [
          'id' => 'userspn_admin_bar_access_roles',
          'class' => 'userspn-select userspn-width-100-percent',
          'input' => 'select',
          'options' => $userspn_roles,
          'multiple' => 'true',
          'parent' => 'userspn_admin_bar_removal',
          'parent_option' => 'on',
          'label' => __('Enabled roles', 'userspn'),
          'description' => __('Set up the roles to get the admin bar. This option takes priority over the previous one. If a role appears in both fields, this "Enabled roles" field will be the one taken into account and the administration bar will be displayed.', 'userspn'),
        ];
      $userspn_options['userspn_user_register_fields_dashboard'] = [
        'id' => 'userspn_user_register_fields_dashboard',
        'class' => 'userspn-input userspn-width-100-percent',
        'input' => 'input',
        'type' => 'checkbox',
        'label' => __('Add extra profile fields on admin dashboard', 'userspn'),
        'description' => __('If you check this option, admin dashboard user edition and creation pages will show the added profile extra fields at the bottom. Administrators and roles with access to this dashboard pages will be able to edit or add them on the creation of new contacts.', 'userspn') . '<br>' . esc_url(admin_url('/user-edit.php?user_id=' . get_current_user_id())),
      ];
      $userspn_options['userspn_auto_login'] = [
        'id' => 'userspn_auto_login',
        'class' => 'userspn-input userspn-width-100-percent',
        'input' => 'input',
        'type' => 'checkbox',
        'parent' => 'this',
        'label' => __('Users auto-login', 'userspn'),
        'description' => __('This option will allow administrators to login as any user on the platform with no administrator role. Please use this option with caution and responsibility, always with the consent of the users. It carries privacy and security risks.', 'userspn'),
      ];
    $userspn_options['userspn_section_dashboard_end'] = [
      'section' => 'end',
    ];
    
    $userspn_options['userspn_section_newsletter_start'] = [
      'section' => 'start',
      'label' => __('Newsletter', 'userspn'),
    ];
      $userspn_options['userspn_newsletter'] = [
        'id' => 'userspn_newsletter',
        'class' => 'userspn-input userspn-width-100-percent',
        'input' => 'input',
        'type' => 'checkbox',
        'parent' => 'this',
        'label' => __('Newsletter', 'userspn'),
      ];
        $userspn_options['userspn_newsletter_message'] = [
          'id' => 'userspn_newsletter_message',
          'class' => 'userspn-input userspn-width-100-percent',
          'input' => 'editor',
          'parent' => 'userspn_newsletter',
          'parent_option' => 'on',
          'label' => __('Newsletter message', 'userspn'),
          'description' => __('Create a message that will be shown just above of the Newsletter registration form.', 'userspn'),
        ];
        $userspn_options['userspn_newsletter_exit_popup'] = [
          'id' => 'userspn_newsletter_exit_popup',
          'class' => 'userspn-input userspn-width-100-percent',
          'input' => 'input',
          'type' => 'checkbox',
          'parent' => 'this userspn_newsletter',
          'parent_option' => 'on',
          'label' => __('Newsletter exit popup', 'userspn'),
          'description' => __('When this option is active, the system shows a popup when a contact tries to exit page without completing the newsletter registration form.', 'userspn'),
        ];
          $userspn_options['userspn_newsletter_exit_popup_empty'] = [
            'id' => 'userspn_newsletter_exit_popup_empty',
            'class' => 'userspn-input userspn-width-100-percent',
            'input' => 'input',
            'type' => 'checkbox',
            'parent' => 'userspn_newsletter_exit_popup',
            'parent_option' => 'on',
            'label' => __('Newsletter is already loaded', 'userspn'),
            'description' => __('Check this option if you already have a newsletter form loaded in the page. For example, if you are using the shortcode [userspn-newsletter] in the page footer, you should check this option.', 'userspn'),
          ];
        $userspn_options['userspn_newsletter_activation'] = [
          'id' => 'userspn_newsletter_activation',
          'class' => 'userspn-input userspn-width-100-percent',
          'input' => 'input',
          'type' => 'checkbox',
          'parent' => 'this userspn_newsletter',
          'parent_option' => 'on',
          'label' => __('Newsletter activation required', 'userspn'),
          'description' => __('When this option is active, the system asks contacts to verify their emails via a link sent to their email address as they register in the platform.', 'userspn'),
        ];
          $userspn_options['userspn_newsletter_activation_max'] = [
            'id' => 'userspn_newsletter_activation_max',
            'class' => 'userspn-input userspn-width-100-percent',
            'input' => 'input',
            'type' => 'number',
            'parent' => 'userspn_newsletter_activation',
            'parent_option' => 'on',
            'label' => __('Maximum number of activation emails', 'userspn'),
            'placeholder' => __('Maximum number of activation emails', 'userspn'),
            'description' => __('This option set up the maximum numer of activating emails allowed to be sent from the platform.', 'userspn'),
          ];
    $userspn_options['userspn_section_newsletter_end'] = [
      'section' => 'end',
    ];

    $userspn_options['userspn_submit'] = [
      'id' => 'userspn_submit',
      'input' => 'input',
      'type' => 'submit',
      'value' => __('Save options', 'userspn'),
    ];

    return $userspn_options;
  }

	/**
	 * Administrator menu.
	 *
	 * @since    1.0.0
	 */
	public function userspn_admin_menu() {
    add_menu_page(__('Users manager', 'userspn'), __('Users manager', 'userspn'), 'administrator', 'userspn_options', [$this, 'userspn_options'], esc_url(USERSPN_URL . 'assets/media/userspn-menu-icon.svg'));

    if(is_admin() && !defined('DOING_AJAX') && !current_user_can('administrator') && get_option('userspn_admin_access_removal') == 'on'){
      $user = new WP_User(get_current_user_id());
      $userspn_admin_access_roles = get_option('userspn_admin_access_roles') ?? [];

      if (!array_intersect($user->roles, $userspn_admin_access_roles)) {
        wp_redirect(esc_url(home_url()));exit;
      }
    }
	}

	public function userspn_options() {
	  ?>
	    <div class="userspn-options userspn-max-width-1000 userspn-margin-auto userspn-mt-50 userspn-mb-50">
        <img src="<?php echo esc_url(USERSPN_URL . 'assets/media/banner-1544x500.png'); ?>" alt="<?php esc_html_e('Plugin main Banner', 'userspn'); ?>" title="<?php esc_html_e('Plugin main Banner', 'userspn'); ?>" class="userspn-width-100-percent userspn-border-radius-20 userspn-mb-30">
        
        <h1 class="userspn-mb-30"><?php esc_html_e('Users manager - PN Settings', 'userspn'); ?></h1>
        <div class="userspn-options-fields userspn-mb-30">
          <form action="" method="post" id="userspn_form" class="userspn-form">
            <?php foreach ($this->get_options() as $userspn_option): ?>
              <?php USERSPN_Forms::userspn_input_wrapper_builder($userspn_option, 'option'); ?>
            <?php endforeach ?>
          </form> 
        </div>
      </div>
	  <?php
	}

  public function userspn_bytes_format($size, $precision = 2) { 
    $base = log($size, 1024);
    $suffixes = ['B', 'KB', 'MB', 'GB', 'TB'];   

    return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
  }

  public function userspn_login_headerurl() {
    if (!empty(get_option('userspn_admin_page_logo_link'))) {
      return get_option('userspn_admin_page_logo_link');
    }
  }

  public function userspn_login_headertext() {
    if (!empty(get_option('userspn_admin_page_logo_link_text'))) {
      return __('Powered by', 'userspn') . ' ' . get_option('userspn_admin_page_logo_link_text');
    }
  }

  public function userspn_login_logo() {
    $userspn_admin_page_logo = get_option('userspn_admin_page_logo');
    if (!empty($userspn_admin_page_logo)) {
      $userspn_admin_page_logo_base_css = '#login h1 a{background-image:url(' . esc_url(wp_get_attachment_image_src($userspn_admin_page_logo, 'large')[0]) . ');width:' . esc_url(wp_get_attachment_image_src($userspn_admin_page_logo, 'large')[1]) . 'px;height:' . esc_url(wp_get_attachment_image_src($userspn_admin_page_logo, 'large')[2]) . 'px;background-repeat:no-repeat;max-width:300px;max-height:300px;background-size:contain;background-position:center center;}';

      wp_register_style('userspn-admin-page-logo-base-css', false, [], USERSPN_VERSION);
      wp_enqueue_style('userspn-admin-page-logo-base-css');
      wp_add_inline_style('userspn-admin-page-logo-base-css', $userspn_admin_page_logo_base_css);
    }

    $userspn_admin_page_logo_css = get_option('userspn_admin_page_logo_css');
    if (!empty($userspn_admin_page_logo_css)) {
      wp_register_style('userspn-admin-page-logo-css', false, [], USERSPN_VERSION);
      wp_enqueue_style('userspn-admin-page-logo-css');
      wp_add_inline_style('userspn-admin-page-logo-css', $userspn_admin_page_logo_css);
    }
  }

  public function userspn_wp_before_admin_bar_render() {
    if (get_option('userspn_dashboard_logo') == 'on') {
      $userspn_admin_bar_render_css = '#wpadminbar #wp-admin-bar-wp-logo{background-color:#ffffff;}#wpadminbar #wp-admin-bar-wp-logo > .ab-item {padding:0 7px;background-image:url(' . esc_url(wp_get_attachment_image_src(get_option('userspn_admin_page_logo'), 'large')[0]) . ')!important;background-size:70%;background-color:#ffffff;background-position:center;background-repeat:no-repeat;opacity:0.8;} #wpadminbar #wp-admin-bar-wp-logo > .ab-item .ab-icon:before {content:" ";top:2px;}#wpadminbar #wp-admin-bar-wp-logo{background-color:#ffffff;}';

      wp_register_style('userspn-admin-bar-render-css', false, [], USERSPN_VERSION);
      wp_enqueue_style('userspn-admin-bar-render-css');
      wp_add_inline_style('userspn-admin-bar-render-css', $userspn_admin_bar_render_css);
    }
  }

  public function userspn_admin_bar_wp_menu($wp_admin_bar) {
    if (get_option('userspn_dashboard_logo_link') == 'on') {
      $wp_logo_node = $wp_admin_bar->get_node('wp-logo');

      $wp_logo_node->href = get_option('userspn_admin_page_logo_link');
      $wp_logo_node->meta['title'] = get_option('userspn_admin_page_logo_link_text');

      $wp_admin_bar->add_node($wp_logo_node);

      $wp_admin_bar->remove_menu('about');
      $wp_admin_bar->remove_menu('contribute');
      $wp_admin_bar->remove_menu('wporg');
      $wp_admin_bar->remove_menu('documentation');
      $wp_admin_bar->remove_menu('learn');
      $wp_admin_bar->remove_menu('support-forums');
      $wp_admin_bar->remove_menu('feedback');
    }
  }

  public function userspn_lostpassword_url($lostpassword_url, $redirect) {
    $nonce = wp_create_nonce('userspn-nonce');
    return esc_url(add_query_arg('userspn_ajax_nopriv_nonce', $nonce, home_url('wp-login.php?action=lostpassword')));
  }

  public function userspn_add_nonce_to_lostpassword_form() {
    // Skip adding nonce to lost password form
    return;
  }

  public function userspn_remove_admin_bar() {
    if(!is_admin() && !current_user_can('administrator') && get_option('userspn_admin_bar_removal') == 'on'){
      $user = new WP_User(get_current_user_id());
      $userspn_admin_bar_access_roles = get_option('userspn_admin_bar_access_roles') ?? [];

      if (!array_intersect($user->roles, $userspn_admin_bar_access_roles)) {
        show_admin_bar(false);
        return false;
      }
    }
  }

  public function userspn_activated_plugin($plugin) {
    if($plugin == 'userspn/userspn.php') {
      wp_redirect(esc_url(admin_url('admin.php?page=userspn_options')));exit();
    }
  }
}