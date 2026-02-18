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
class USERSPN_Settings
{
  public function get_options()
  {
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
    $userspn_options['userspn_bubble_position_type'] = [
      'id' => 'userspn_bubble_position_type',
      'class' => 'userspn-select userspn-width-100-percent',
      'input' => 'select',
      'value' => get_option('userspn_bubble_position_type', 'default'),
      'parent' => 'this',
      'label' => __('Profile bubble position', 'userspn'),
      'description' => __('Choose where to display the profile bubble.', 'userspn'),
      'options' => [
        'default' => __('Default (right side)', 'userspn'),
        'menu' => __('In menu', 'userspn'),
        'custom' => __('Custom position', 'userspn'),
      ],
    ];
    $userspn_options['userspn_menu_profile_icon_location'] = [
      'id' => 'userspn_menu_profile_icon_location',
      'class' => 'userspn-select userspn-width-100-percent',
      'input' => 'select',
      'options' => $this->get_available_menus(),
      'parent' => 'userspn_bubble_position_type',
      'parent_option' => 'menu',
      'label' => __('Select menu location', 'userspn'),
      'description' => __('Choose the navigation menu where you want to add the profile icon.', 'userspn'),
    ];
    $userspn_options['userspn_bubble_position_x'] = [
      'id' => 'userspn_bubble_position_x',
      'class' => 'userspn-input',
      'input' => 'input',
      'type' => 'hidden',
      'value' => get_option('userspn_bubble_position_x', ''),
      'parent' => 'userspn_bubble_position_type',
      'parent_option' => 'custom',
    ];
    $userspn_options['userspn_bubble_position_y'] = [
      'id' => 'userspn_bubble_position_y',
      'class' => 'userspn-input',
      'input' => 'input',
      'type' => 'hidden',
      'value' => get_option('userspn_bubble_position_y', ''),
      'parent' => 'userspn_bubble_position_type',
      'parent_option' => 'custom',
    ];
    $userspn_options['userspn_bubble_custom_position'] = [
      'id' => 'userspn_bubble_custom_position',
      'class' => 'userspn-input userspn-width-100-percent',
      'input' => 'input',
      'type' => 'checkbox',
      'parent' => 'userspn_bubble_position_type',
      'parent_option' => 'custom',
      'label' => __('Set custom position', 'userspn'),
      'description' => __('Check to open the position editor. Drag the bubble to the desired location and confirm.', 'userspn'),
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
        $userspn_profile_completion_field_meta_key = [
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
    $userspn_options['userspn_newsletter_activation_delete_inactive'] = [
      'id' => 'userspn_newsletter_activation_delete_inactive',
      'class' => 'userspn-input userspn-width-100-percent',
      'input' => 'input',
      'type' => 'checkbox',
      'parent' => 'this userspn_newsletter',
      'parent_option' => 'on',
      'label' => __('Eliminar cuentas no activadas', 'userspn'),
      'description' => __('Cuando esta opción está activa, el sistema eliminará automáticamente las cuentas de usuarios que se hayan registrado en la newsletter pero no hayan activado su cuenta después de un número determinado de días.', 'userspn'),
    ];
    $userspn_options['userspn_newsletter_activation_delete_days'] = [
      'id' => 'userspn_newsletter_activation_delete_days',
      'class' => 'userspn-input userspn-width-100-percent',
      'input' => 'input',
      'type' => 'number',
      'parent' => 'userspn_newsletter_activation_delete_inactive',
      'parent_option' => 'on',
      'label' => __('Días para eliminar cuenta no activada', 'userspn'),
      'placeholder' => '5',
      'value' => '5',
      'min' => '1',
      'description' => __('Número de días que deben pasar desde el registro para que se elimine la cuenta si no ha sido activada. Por defecto: 5 días.', 'userspn'),
    ];
    $userspn_options['userspn_section_newsletter_end'] = [
      'section' => 'end',
    ];

    $userspn_options['userspn_section_security_start'] = [
      'section' => 'start',
      'label' => __('Security Settings', 'userspn'),
    ];
    $userspn_options['userspn_strong_password'] = [
      'id' => 'userspn_strong_password',
      'class' => 'userspn-input userspn-width-100-percent',
      'input' => 'input',
      'type' => 'checkbox',
      'parent' => 'this',
      'label' => __('Enable strong password', 'userspn'),
      'description' => __('Enable strong password check for user registration forms.', 'userspn'),
    ];
    $userspn_options['userspn_recaptcha_enabled'] = [
      'id' => 'userspn_recaptcha_enabled',
      'class' => 'userspn-input userspn-width-100-percent',
      'input' => 'input',
      'type' => 'checkbox',
      'parent' => 'this',
      'label' => __('Enable Google reCAPTCHA v3', 'userspn'),
      'description' => __('Enable Google reCAPTCHA v3 protection for user registration forms.', 'userspn'),
    ];
    $userspn_options['userspn_recaptcha_site_key'] = [
      'id' => 'userspn_recaptcha_site_key',
      'class' => 'userspn-input userspn-width-100-percent',
      'input' => 'input',
      'type' => 'text',
      'parent' => 'userspn_recaptcha_enabled',
      'parent_option' => 'on',
      'label' => __('reCAPTCHA Site Key', 'userspn'),
      'placeholder' => __('Enter your reCAPTCHA Site Key', 'userspn'),
      'description' => __('Get your Site Key from Google reCAPTCHA console.', 'userspn'),
    ];
    $userspn_options['userspn_recaptcha_secret_key'] = [
      'id' => 'userspn_recaptcha_secret_key',
      'class' => 'userspn-input userspn-width-100-percent',
      'input' => 'input',
      'type' => 'password',
      'parent' => 'userspn_recaptcha_enabled',
      'parent_option' => 'on',
      'label' => __('reCAPTCHA Secret Key', 'userspn'),
      'placeholder' => __('Enter your reCAPTCHA Secret Key', 'userspn'),
      'description' => __('Get your Secret Key from Google reCAPTCHA console.', 'userspn'),
    ];
    $userspn_options['userspn_recaptcha_threshold'] = [
      'id' => 'userspn_recaptcha_threshold',
      'class' => 'userspn-input userspn-width-100-percent',
      'input' => 'input',
      'type' => 'number',
      'parent' => 'userspn_recaptcha_enabled',
      'parent_option' => 'on',
      'label' => __('reCAPTCHA Score Threshold', 'userspn'),
      'placeholder' => '0.5',
      'min' => '0.1',
      'max' => '1.0',
      'step' => '0.1',
      'description' => __('Score threshold for blocking (0.1 = very strict, 1.0 = very permissive). Recommended: 0.5', 'userspn'),
    ];
    $userspn_options['userspn_recaptcha_block_suspicious'] = [
      'id' => 'userspn_recaptcha_block_suspicious',
      'class' => 'userspn-input userspn-width-100-percent',
      'input' => 'input',
      'type' => 'checkbox',
      'parent' => 'userspn_recaptcha_enabled',
      'parent_option' => 'on',
      'label' => __('Block Suspicious Scores', 'userspn'),
      'description' => __('When enabled, registrations with reCAPTCHA scores below the threshold will be blocked. When disabled, they will be logged but allowed.', 'userspn'),
    ];
    $akismet_active = function_exists('akismet_http_post');
    $userspn_options['userspn_akismet_enabled'] = [
      'id' => 'userspn_akismet_enabled',
      'class' => 'userspn-input userspn-width-100-percent',
      'input' => 'input',
      'type' => 'checkbox',
      'parent' => 'this',
      'label' => __('Enable Akismet Protection', 'userspn'),
      'description' => $akismet_active
        ? __('Enable Akismet spam protection for user registration (requires Akismet plugin).', 'userspn')
        : __('Akismet plugin is not installed or not active. Install and activate Akismet to use this feature.', 'userspn'),
      'disabled' => !$akismet_active,
    ];
    $userspn_options['userspn_honeypot_enabled'] = [
      'id' => 'userspn_honeypot_enabled',
      'class' => 'userspn-input userspn-width-100-percent',
      'input' => 'input',
      'type' => 'checkbox',
      'label' => __('Enable Honeypot Protection', 'userspn'),
      'description' => __('Add hidden honeypot field to catch bots.', 'userspn'),
    ];
    $userspn_options['userspn_rate_limiting_enabled'] = [
      'id' => 'userspn_rate_limiting_enabled',
      'class' => 'userspn-input userspn-width-100-percent',
      'input' => 'input',
      'type' => 'checkbox',
      'parent' => 'this',
      'label' => __('Enable Rate Limiting', 'userspn'),
      'description' => __('Limit registration attempts per IP address.', 'userspn'),
    ];
    $userspn_options['userspn_rate_limit_attempts'] = [
      'id' => 'userspn_rate_limit_attempts',
      'class' => 'userspn-input userspn-width-100-percent',
      'input' => 'input',
      'type' => 'number',
      'parent' => 'userspn_rate_limiting_enabled',
      'parent_option' => 'on',
      'label' => __('Max Registration Attempts', 'userspn'),
      'placeholder' => '5',
      'min' => '1',
      'max' => '50',
      'description' => __('Maximum registration attempts per IP per hour.', 'userspn'),
    ];
    $userspn_options['userspn_rate_limit_window'] = [
      'id' => 'userspn_rate_limit_window',
      'class' => 'userspn-input userspn-width-100-percent',
      'input' => 'input',
      'type' => 'number',
      'parent' => 'userspn_rate_limiting_enabled',
      'parent_option' => 'on',
      'label' => __('Rate Limit Window (hours)', 'userspn'),
      'placeholder' => '1',
      'min' => '1',
      'max' => '24',
      'description' => __('Time window in hours for rate limiting.', 'userspn'),
    ];
    $userspn_options['userspn_newsletter_rate_limit_attempts'] = [
      'id' => 'userspn_newsletter_rate_limit_attempts',
      'class' => 'userspn-input userspn-width-100-percent',
      'input' => 'input',
      'type' => 'number',
      'parent' => 'userspn_rate_limiting_enabled',
      'parent_option' => 'on',
      'label' => __('Newsletter Max Attempts', 'userspn'),
      'placeholder' => '3',
      'min' => '1',
      'description' => __('Maximum newsletter subscription attempts per IP (more strict than general rate limit). Leave empty to use general rate limit.', 'userspn'),
    ];
    $userspn_options['userspn_newsletter_rate_limit_window'] = [
      'id' => 'userspn_newsletter_rate_limit_window',
      'class' => 'userspn-input userspn-width-100-percent',
      'input' => 'input',
      'type' => 'number',
      'parent' => 'userspn_rate_limiting_enabled',
      'parent_option' => 'on',
      'label' => __('Newsletter Rate Limit Window (hours)', 'userspn'),
      'placeholder' => '1',
      'min' => '1',
      'description' => __('Time window in hours for newsletter rate limiting. Leave empty to use general rate limit.', 'userspn'),
    ];
    $userspn_options['userspn_bot_analysis_button'] = [
      'id' => 'userspn_bot_analysis_button',
      'class' => 'userspn-btn userspn-btn-primary',
      'input' => 'input',
      'type' => 'button',
      'label' => __('Bot Analysis Tool', 'userspn'),
      'value' => __('Analyze Existing Users for Bots', 'userspn'),
      'onclick' => 'userspn_open_bot_analysis_popup()',
      'description' => __('This tool analyzes existing users to identify potential bots based on various patterns and behaviors such as suspicious email patterns, bot-like usernames, empty profiles, multiple registrations from same IP, and lack of activity.', 'userspn'),
    ];
    $userspn_options['userspn_section_security_end'] = [
      'section' => 'end',
    ];

    $userspn_options['userspn_section_design_start'] = [
      'section' => 'start',
      'label' => __('Design', 'userspn'),
    ];
    $userspn_options['userspn_color_main'] = [
      'id' => 'userspn_color_main',
      'class' => 'userspn-input userspn-width-100-percent',
      'input' => 'input',
      'type' => 'color',
      'value' => get_option('userspn_color_main', '#00aa44'),
      'label' => __('Main Color', 'userspn'),
      'description' => __('Main color used throughout the plugin interface.', 'userspn'),
    ];
    $userspn_options['userspn_bg_color_main'] = [
      'id' => 'userspn_bg_color_main',
      'class' => 'userspn-input userspn-width-100-percent',
      'input' => 'input',
      'type' => 'color',
      'value' => get_option('userspn_bg_color_main', '#00aa44'),
      'label' => __('Main Background Color', 'userspn'),
      'description' => __('Main background color used throughout the plugin interface.', 'userspn'),
    ];
    $userspn_options['userspn_border_color_main'] = [
      'id' => 'userspn_border_color_main',
      'class' => 'userspn-input userspn-width-100-percent',
      'input' => 'input',
      'type' => 'color',
      'value' => get_option('userspn_border_color_main', '#00aa44'),
      'label' => __('Main Border Color', 'userspn'),
      'description' => __('Main border color used throughout the plugin interface.', 'userspn'),
    ];
    $userspn_options['userspn_color_main_alt'] = [
      'id' => 'userspn_color_main_alt',
      'class' => 'userspn-input userspn-width-100-percent',
      'input' => 'input',
      'type' => 'color',
      'value' => get_option('userspn_color_main_alt', '#232323'),
      'label' => __('Alternative Main Color', 'userspn'),
      'description' => __('Alternative main color used throughout the plugin interface.', 'userspn'),
    ];
    $userspn_options['userspn_bg_color_main_alt'] = [
      'id' => 'userspn_bg_color_main_alt',
      'class' => 'userspn-input userspn-width-100-percent',
      'input' => 'input',
      'type' => 'color',
      'value' => get_option('userspn_bg_color_main_alt', '#232323'),
      'label' => __('Alternative Main Background Color', 'userspn'),
      'description' => __('Alternative main background color used throughout the plugin interface.', 'userspn'),
    ];
    $userspn_options['userspn_border_color_main_alt'] = [
      'id' => 'userspn_border_color_main_alt',
      'class' => 'userspn-input userspn-width-100-percent',
      'input' => 'input',
      'type' => 'color',
      'value' => get_option('userspn_border_color_main_alt', '#232323'),
      'label' => __('Alternative Main Border Color', 'userspn'),
      'description' => __('Alternative main border color used throughout the plugin interface.', 'userspn'),
    ];
    $userspn_options['userspn_color_main_blue'] = [
      'id' => 'userspn_color_main_blue',
      'class' => 'userspn-input userspn-width-100-percent',
      'input' => 'input',
      'type' => 'color',
      'value' => get_option('userspn_color_main_blue', '#6e6eff'),
      'label' => __('Main Blue Color', 'userspn'),
      'description' => __('Main blue color used throughout the plugin interface.', 'userspn'),
    ];
    $userspn_options['userspn_section_design_end'] = [
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
  public function userspn_admin_menu()
  {
    add_menu_page(__('Users manager', 'userspn'), __('Users manager', 'userspn'), 'administrator', 'userspn_options', [$this, 'userspn_options'], esc_url(USERSPN_URL . 'assets/media/userspn-menu-icon.svg'));

    // Add Dashboard menu as submenu of Users manager
    add_submenu_page(
      'userspn_options',
      __('Statistics', 'userspn'),
      __('Statistics', 'userspn'),
      'administrator',
      'userspn_dashboard',
      [$this, 'userspn_dashboard_page']
    );

    // Add Security Logs submenu
    add_submenu_page(
      'userspn_options',
      __('Security Logs', 'userspn'),
      __('Security Logs', 'userspn'),
      'administrator',
      'userspn_security_logs',
      [$this, 'userspn_security_logs_page']
    );

    if (is_admin() && !defined('DOING_AJAX') && !current_user_can('administrator') && get_option('userspn_admin_access_removal') == 'on') {
      $user = new WP_User(get_current_user_id());
      $userspn_admin_access_roles = get_option('userspn_admin_access_roles') ?? [];

      if (!array_intersect($user->roles, $userspn_admin_access_roles)) {
        wp_redirect(esc_url(home_url()));
        exit;
      }
    }
  }

  public function userspn_options()
  {
    $user_id = get_current_user_id();
    ?>
    <div class="userspn-options userspn-max-width-1000 userspn-margin-auto userspn-mt-50 userspn-mb-50">
      <img src="<?php echo esc_url(USERSPN_URL . 'assets/media/banner-1544x500.png'); ?>"
        alt="<?php esc_html_e('Plugin main Banner', 'userspn'); ?>"
        title="<?php esc_html_e('Plugin main Banner', 'userspn'); ?>"
        class="userspn-width-100-percent userspn-border-radius-20 userspn-mb-30">

      <h1 class="userspn-mb-30"><?php esc_html_e('Users manager - PN Settings', 'userspn'); ?></h1>
      <div class="userspn-options-fields userspn-mb-30">
        <form action="" method="post" id="userspn_form" class="userspn-form">
          <?php foreach ($this->get_options() as $userspn_option): ?>
            <?php USERSPN_Forms::userspn_input_wrapper_builder($userspn_option, 'option'); ?>
          <?php endforeach ?>
        </form>
        <a href="#" class="userspn-btn userspn-preview-profile-btn userspn-mt-20">
          <?php esc_html_e('Preview profile', 'userspn'); ?>
        </a>
      </div>
    </div>

    <?php if (is_user_logged_in()): ?>
      <a href="#" class="userspn-profile-popup-btn" style="display:none;">
        <?php echo do_shortcode('[userspn-get-avatar user_id="' . $user_id . '" size="50"]'); ?>
      </a>

      <?php
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
                </div>
              <?php endif ?>
            </div>
          </div>
        </div>
      </div>
    <?php endif ?>
    <?php
  }

  public function userspn_dashboard_page()
  {
    // Load scripts and styles
    wp_enqueue_style('userspn-admin', USERSPN_URL . 'assets/css/admin/userspn-admin.css', [], USERSPN_VERSION);
    wp_enqueue_script('userspn-popups', USERSPN_URL . 'assets/js/userspn-popups.js', ['jquery'], USERSPN_VERSION, true);

    // Get analytics data
    $users_last_week = $this->get_userspn_users_last_week();
    $newsletter_last_week = $this->get_userspn_newsletter_last_week();
    $last_logins = $this->get_userspn_last_logins();

    ?>
    <div class="userspn-dashboard userspn-max-width-1000 userspn-margin-auto userspn-mt-50 userspn-mb-50">
      <h1 class="userspn-mb-30"><?php esc_html_e('Dashboard de Analíticas', 'userspn'); ?></h1>
      <div class="userspn-dashboard-widgets" style="display:flex;gap:30px;margin-bottom:30px;">
        <div class="userspn-dashboard-widget userspn-bg-primary" data-popup="userspn-users-week"
          style="flex:1;cursor:pointer;">
          <div class="userspn-dashboard-widget-title"><?php esc_html_e('Usuarios nuevos (7 días)', 'userspn'); ?></div>
          <div class="userspn-dashboard-widget-value" style="font-size:2em;font-weight:bold;">
            <?php echo esc_html($users_last_week['count']); ?></div>
        </div>
        <div class="userspn-dashboard-widget userspn-bg-secondary" data-popup="userspn-newsletter-week"
          style="flex:1;cursor:pointer;">
          <div class="userspn-dashboard-widget-title"><?php esc_html_e('Newsletter (7 días)', 'userspn'); ?></div>
          <div class="userspn-dashboard-widget-value" style="font-size:2em;font-weight:bold;">
            <?php echo esc_html($newsletter_last_week['count']); ?></div>
        </div>
        <div class="userspn-dashboard-widget userspn-bg-accent" data-popup="userspn-last-logins"
          style="flex:1;cursor:pointer;">
          <div class="userspn-dashboard-widget-title"><?php esc_html_e('Últimos accesos', 'userspn'); ?></div>
          <div class="userspn-dashboard-widget-value" style="font-size:2em;font-weight:bold;">
            <?php echo esc_html($last_logins['count']); ?></div>
        </div>
      </div>
      <!-- Hidden popups -->
      <div id="userspn-popup-userspn-users-week" class="userspn-popup userspn-popup-size-medium userspn-display-none-soft">
        <div class="userspn-popup-content">
          <div class="userspn-p-30">
            <h2><?php esc_html_e('Usuarios nuevos en la última semana', 'userspn'); ?></h2>
            <?php echo $users_last_week['html']; ?>
          </div>
        </div>
      </div>
      <div id="userspn-popup-userspn-newsletter-week"
        class="userspn-popup userspn-popup-size-medium userspn-display-none-soft">
        <div class="userspn-popup-content">
          <div class="userspn-p-30">
            <h2><?php esc_html_e('Altas en newsletter en la última semana', 'userspn'); ?></h2>
            <?php echo $newsletter_last_week['html']; ?>
          </div>
        </div>
      </div>
      <div id="userspn-popup-userspn-last-logins" class="userspn-popup userspn-popup-size-medium userspn-display-none-soft">
        <div class="userspn-popup-content">
          <div class="userspn-p-30">
            <h2><?php esc_html_e('Últimos accesos', 'userspn'); ?></h2>
            <?php echo $last_logins['html']; ?>
          </div>
        </div>
      </div>
    </div>
    <script>
      jQuery(function ($) {
        $('.userspn-dashboard-widget').on('click', function () {
          var popup = $(this).data('popup');
          USERSPN_Popups.open('userspn-popup-' + popup);
        });
      });
    </script>
    <?php
  }

  public function userspn_security_logs_page()
  {
    global $wpdb;
    $table_name = $wpdb->prefix . 'userspn_security_logs';

    // Check if table exists
    $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));

    wp_enqueue_style('userspn-admin', USERSPN_URL . 'assets/css/admin/userspn-admin.css', [], USERSPN_VERSION);

    // Get logs and stats
    $logs = [];
    $stats = ['total' => 0, 'open' => 0, 'blocked' => 0, 'success' => 0];
    if ($table_exists) {
      // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
      $logs = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 500");
      foreach ($logs as $log) {
        $stats['total']++;
        if (!$log->resolved) { $stats['open']++; }
        if (strpos($log->event, 'blocked') !== false || strpos($log->event, 'error') !== false || strpos($log->event, 'failed') !== false) { $stats['blocked']++; }
        if (strpos($log->event, 'success') !== false) { $stats['success']++; }
      }
    }

    // Event config: icon, color, label
    $event_config = [
      'registration_success'    => ['dashicons-yes-alt',    '#00a32a', __('Success', 'userspn')],
      'registration_blocked'    => ['dashicons-shield',     '#d63638', __('Blocked', 'userspn')],
      'registration_failed'     => ['dashicons-warning',    '#dba617', __('Failed', 'userspn')],
      'recaptcha_token_missing' => ['dashicons-info-outline','#72aee6', __('Info', 'userspn')],
      'recaptcha_suspicious'    => ['dashicons-visibility', '#d63638', __('Suspicious', 'userspn')],
      'suspicious_registration' => ['dashicons-visibility', '#d63638', __('Suspicious', 'userspn')],
      'suspicious_email_pattern'=> ['dashicons-email',      '#dba617', __('Warning', 'userspn')],
      'wp_create_user_error'    => ['dashicons-dismiss',    '#d63638', __('Error', 'userspn')],
      'rate_limit_exceeded'     => ['dashicons-clock',      '#dba617', __('Rate limit', 'userspn')],
    ];
    $default_event = ['dashicons-marker', '#787c82', __('Event', 'userspn')];

    ?>
    <style>
      .userspn-sl-wrap { max-width: 1200px; margin: 30px auto; padding: 0 20px; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
      .userspn-sl-header { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; }
      .userspn-sl-header h1 { margin: 0; font-size: 23px; font-weight: 600; color: #1d2327; }
      .userspn-sl-header .dashicons { font-size: 28px; width: 28px; height: 28px; color: #3f51b5; }

      .userspn-sl-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
      .userspn-sl-stat { border-radius: 12px; padding: 24px 20px; text-align: center; transition: all 0.3s ease; color: #fff; border: none; }
      .userspn-sl-stat:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,0.18); }
      .userspn-sl-stat-total { background: linear-gradient(135deg, #3f51b5 0%, #5c6bc0 100%); }
      .userspn-sl-stat-open { background: linear-gradient(135deg, #c62828 0%, #e53935 100%); }
      .userspn-sl-stat-blocked { background: linear-gradient(135deg, #e65100 0%, #f57c00 100%); }
      .userspn-sl-stat-success { background: linear-gradient(135deg, #2e7d32 0%, #43a047 100%); }
      .userspn-sl-stat-value { font-size: 42px; font-weight: 800; line-height: 1; }
      .userspn-sl-stat-label { font-size: 13px; text-transform: uppercase; letter-spacing: 0.6px; margin-top: 8px; opacity: 0.85; }

      .userspn-sl-toolbar { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 12px 16px; }
      .userspn-sl-toolbar .dashicons { color: #787c82; }
      .userspn-sl-filter { border: 1px solid #ddd; border-radius: 4px; padding: 4px 8px; font-size: 13px; }

      .userspn-sl-table-wrap { background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; }
      .userspn-sl-table { width: 100%; border-collapse: collapse; font-size: 13px; }
      .userspn-sl-table thead th { background: #f6f7f7; padding: 10px 14px; text-align: left; font-weight: 600; color: #1d2327; border-bottom: 1px solid #e0e0e0; font-size: 12px; text-transform: uppercase; letter-spacing: 0.3px; white-space: nowrap; }
      .userspn-sl-table tbody tr { border-bottom: 1px solid #f0f0f1; transition: background 0.15s; }
      .userspn-sl-table tbody tr:last-child { border-bottom: none; }
      .userspn-sl-table tbody tr:hover { background: #f6f7f7; }
      .userspn-sl-table tbody td { padding: 12px 14px; vertical-align: top; color: #50575e; }

      .userspn-sl-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; white-space: nowrap; line-height: 1.5; }
      .userspn-sl-badge .dashicons { font-size: 14px; width: 14px; height: 14px; }

      .userspn-sl-event-name { display: block; font-size: 11px; color: #787c82; margin-top: 2px; font-family: monospace; }

      .userspn-sl-email { font-weight: 500; color: #2271b1; }
      .userspn-sl-ip { font-family: monospace; font-size: 12px; color: #787c82; }
      .userspn-sl-date { white-space: nowrap; font-size: 12px; }
      .userspn-sl-date-time { display: block; color: #787c82; font-size: 11px; }

      .userspn-sl-msg { max-width: 260px; line-height: 1.4; }
      .userspn-sl-data-toggle { background: none; border: 1px solid #ddd; border-radius: 4px; padding: 2px 8px; font-size: 11px; color: #2271b1; cursor: pointer; display: inline-flex; align-items: center; gap: 3px; }
      .userspn-sl-data-toggle:hover { background: #f0f6fc; border-color: #2271b1; }
      .userspn-sl-data-toggle .dashicons { font-size: 14px; width: 14px; height: 14px; transition: transform 0.2s; }
      .userspn-sl-data-toggle.open .dashicons { transform: rotate(180deg); }
      .userspn-sl-data-content { display: none; margin-top: 8px; background: #f6f7f7; border-radius: 4px; padding: 8px 10px; font-family: monospace; font-size: 11px; line-height: 1.6; white-space: pre-wrap; word-break: break-all; max-width: 300px; }
      .userspn-sl-data-content.open { display: block; }

      .userspn-sl-status-open { display: inline-flex; align-items: center; gap: 4px; color: #d63638; font-weight: 600; font-size: 12px; }
      .userspn-sl-status-open::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: #d63638; display: inline-block; }
      .userspn-sl-status-resolved { display: inline-flex; align-items: center; gap: 4px; color: #00a32a; font-weight: 600; font-size: 12px; }
      .userspn-sl-status-resolved::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: #00a32a; display: inline-block; }

      .userspn-sl-btn-resolve { background: #f6f7f7; border: 1px solid #ddd; border-radius: 4px; padding: 4px 12px; font-size: 12px; cursor: pointer; color: #2271b1; transition: all 0.15s; display: inline-flex; align-items: center; gap: 4px; }
      .userspn-sl-btn-resolve:hover { background: #2271b1; color: #fff; border-color: #2271b1; }
      .userspn-sl-btn-resolve .dashicons { font-size: 16px; width: 16px; height: 16px; }

      .userspn-sl-btn-danger { background: #fff; border: 1px solid #d63638; border-radius: 4px; padding: 6px 14px; font-size: 13px; cursor: pointer; color: #d63638; transition: all 0.15s; display: inline-flex; align-items: center; gap: 6px; }
      .userspn-sl-btn-danger:hover { background: #d63638; color: #fff; }
      .userspn-sl-btn-danger .dashicons { font-size: 16px; width: 16px; height: 16px; }

      .userspn-sl-empty { text-align: center; padding: 60px 20px; color: #787c82; }
      .userspn-sl-empty .dashicons { font-size: 48px; width: 48px; height: 48px; color: #ddd; display: block; margin: 0 auto 12px; }

      @media (max-width: 782px) {
        .userspn-sl-stats { grid-template-columns: repeat(2, 1fr); }
        .userspn-sl-toolbar { flex-wrap: wrap; }
        .userspn-sl-toolbar .userspn-sl-filter { flex: 1 1 100%; }
        .userspn-sl-toolbar .userspn-sl-btn-danger { flex: 1 1 100%; justify-content: center; }
        .userspn-sl-table-wrap { overflow-x: auto; }
      }
    </style>

    <div class="userspn-sl-wrap">
      <div class="userspn-sl-header">
        <span class="dashicons dashicons-shield-alt"></span>
        <h1><?php esc_html_e('Security Logs', 'userspn'); ?></h1>
      </div>

      <?php if (!$table_exists): ?>
        <div style="background:#fef0f0;border:1px solid #d63638;border-radius:8px;padding:16px;color:#d63638;">
          <span class="dashicons dashicons-warning" style="margin-right:6px;"></span>
          <?php esc_html_e('Security logs table not found. Please deactivate and reactivate the plugin to create it.', 'userspn'); ?>
        </div>
        <?php return; ?>
      <?php endif; ?>

      <!-- Stats widgets -->
      <div class="userspn-sl-stats">
        <div class="userspn-sl-stat userspn-sl-stat-total">
          <div class="userspn-sl-stat-value"><?php echo esc_html($stats['total']); ?></div>
          <div class="userspn-sl-stat-label"><?php esc_html_e('Total events', 'userspn'); ?></div>
        </div>
        <div class="userspn-sl-stat userspn-sl-stat-open">
          <div class="userspn-sl-stat-value"><?php echo esc_html($stats['open']); ?></div>
          <div class="userspn-sl-stat-label"><?php esc_html_e('Open', 'userspn'); ?></div>
        </div>
        <div class="userspn-sl-stat userspn-sl-stat-blocked">
          <div class="userspn-sl-stat-value"><?php echo esc_html($stats['blocked']); ?></div>
          <div class="userspn-sl-stat-label"><?php esc_html_e('Blocked / Errors', 'userspn'); ?></div>
        </div>
        <div class="userspn-sl-stat userspn-sl-stat-success">
          <div class="userspn-sl-stat-value"><?php echo esc_html($stats['success']); ?></div>
          <div class="userspn-sl-stat-label"><?php esc_html_e('Successful', 'userspn'); ?></div>
        </div>
      </div>

      <!-- Toolbar -->
      <div class="userspn-sl-toolbar">
        <select id="userspn-sl-filter-event" class="userspn-sl-filter">
          <option value=""><?php esc_html_e('All events', 'userspn'); ?></option>
          <?php
          $unique_events = [];
          foreach ($logs as $log) {
            if (!in_array($log->event, $unique_events, true)) {
              $unique_events[] = $log->event;
            }
          }
          foreach ($unique_events as $evt): ?>
            <option value="<?php echo esc_attr($evt); ?>"><?php echo esc_html($evt); ?></option>
          <?php endforeach; ?>
        </select>

        <select id="userspn-sl-filter-status" class="userspn-sl-filter">
          <option value=""><?php esc_html_e('All statuses', 'userspn'); ?></option>
          <option value="open"><?php esc_html_e('Open', 'userspn'); ?></option>
          <option value="resolved"><?php esc_html_e('Resolved', 'userspn'); ?></option>
        </select>

        <div style="flex:1;"></div>

        <button type="button" id="userspn-logs-delete-old" class="userspn-sl-btn-danger">
          <span class="dashicons dashicons-trash"></span>
          <?php esc_html_e('Delete logs older than 30 days', 'userspn'); ?>
        </button>
      </div>

      <!-- Table -->
      <div class="userspn-sl-table-wrap">
        <table class="userspn-sl-table" id="userspn-security-logs-table">
          <thead>
            <tr>
              <th><?php esc_html_e('Date', 'userspn'); ?></th>
              <th><?php esc_html_e('Event', 'userspn'); ?></th>
              <th><?php esc_html_e('Email', 'userspn'); ?></th>
              <th><?php esc_html_e('IP', 'userspn'); ?></th>
              <th><?php esc_html_e('Message', 'userspn'); ?></th>
              <th><?php esc_html_e('Details', 'userspn'); ?></th>
              <th><?php esc_html_e('Status', 'userspn'); ?></th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($logs)): ?>
              <?php foreach ($logs as $log):
                $evt = isset($event_config[$log->event]) ? $event_config[$log->event] : $default_event;
                $badge_bg = $evt[1] . '15';
              ?>
                <tr id="userspn-log-row-<?php echo esc_attr($log->id); ?>"
                    data-event="<?php echo esc_attr($log->event); ?>"
                    data-status="<?php echo $log->resolved ? 'resolved' : 'open'; ?>">
                  <td class="userspn-sl-date">
                    <?php
                      $dt = strtotime($log->created_at);
                      echo esc_html(wp_date('M j, Y', $dt));
                    ?>
                    <span class="userspn-sl-date-time"><?php echo esc_html(wp_date('H:i:s', $dt)); ?></span>
                  </td>
                  <td>
                    <span class="userspn-sl-badge" style="background:<?php echo esc_attr($badge_bg); ?>;color:<?php echo esc_attr($evt[1]); ?>;">
                      <span class="dashicons <?php echo esc_attr($evt[0]); ?>"></span>
                      <?php echo esc_html($evt[2]); ?>
                    </span>
                    <span class="userspn-sl-event-name"><?php echo esc_html($log->event); ?></span>
                  </td>
                  <td>
                    <?php if (!empty($log->email)): ?>
                      <span class="userspn-sl-email"><?php echo esc_html($log->email); ?></span>
                    <?php else: ?>
                      <span style="color:#ccc;">&mdash;</span>
                    <?php endif; ?>
                  </td>
                  <td><span class="userspn-sl-ip"><?php echo esc_html($log->ip_address); ?></span></td>
                  <td class="userspn-sl-msg"><?php echo esc_html($log->message); ?></td>
                  <td>
                    <?php
                      $decoded = json_decode($log->data, true);
                      if (is_array($decoded) && !empty($decoded)):
                    ?>
                      <button type="button" class="userspn-sl-data-toggle" data-target="userspn-sl-data-<?php echo esc_attr($log->id); ?>">
                        <span class="dashicons dashicons-arrow-down-alt2"></span>
                        <?php echo esc_html(count($decoded)); ?> <?php esc_html_e('fields', 'userspn'); ?>
                      </button>
                      <div class="userspn-sl-data-content" id="userspn-sl-data-<?php echo esc_attr($log->id); ?>"><?php
                        foreach ($decoded as $k => $v) {
                          $val = is_array($v) ? wp_json_encode($v) : $v;
                          echo esc_html($k) . ': ' . esc_html($val) . "\n";
                        }
                      ?></div>
                    <?php else: ?>
                      <span style="color:#ccc;">&mdash;</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($log->resolved): ?>
                      <span class="userspn-sl-status-resolved"><?php esc_html_e('Resolved', 'userspn'); ?></span>
                    <?php else: ?>
                      <span class="userspn-sl-status-open"><?php esc_html_e('Open', 'userspn'); ?></span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if (!$log->resolved): ?>
                      <button type="button" class="userspn-sl-btn-resolve userspn-log-resolve" data-id="<?php echo esc_attr($log->id); ?>">
                        <span class="dashicons dashicons-saved"></span>
                        <?php esc_html_e('Resolve', 'userspn'); ?>
                      </button>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="8">
                  <div class="userspn-sl-empty">
                    <span class="dashicons dashicons-shield-alt"></span>
                    <p><?php esc_html_e('No security logs found. All clear!', 'userspn'); ?></p>
                  </div>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <script>
    jQuery(function($) {
      // Toggle data details
      $(document).on('click', '.userspn-sl-data-toggle', function() {
        var target = $('#' + $(this).data('target'));
        $(this).toggleClass('open');
        target.toggleClass('open');
      });

      // Filter by event
      $('#userspn-sl-filter-event, #userspn-sl-filter-status').on('change', function() {
        var eventFilter = $('#userspn-sl-filter-event').val();
        var statusFilter = $('#userspn-sl-filter-status').val();
        $('#userspn-security-logs-table tbody tr').each(function() {
          var row = $(this);
          var showEvent = !eventFilter || row.data('event') === eventFilter;
          var showStatus = !statusFilter || row.data('status') === statusFilter;
          row.toggle(showEvent && showStatus);
        });
      });

      // Resolve a log entry
      $(document).on('click', '.userspn-log-resolve', function() {
        var btn = $(this);
        var logId = btn.data('id');
        btn.prop('disabled', true).find('.dashicons').removeClass('dashicons-saved').addClass('dashicons-update');

        $.post(ajaxurl, {
          action: 'userspn_ajax',
          userspn_ajax_type: 'userspn_security_log_resolve',
          userspn_ajax_nonce: '<?php echo esc_js(wp_create_nonce('userspn-nonce')); ?>',
          log_id: logId
        }, function(response) {
          try {
            var data = typeof response === 'string' ? JSON.parse(response) : response;
            if (data.error_key === '') {
              var row = $('#userspn-log-row-' + logId);
              row.data('status', 'resolved');
              row.find('.userspn-sl-status-open').replaceWith('<span class="userspn-sl-status-resolved"><?php echo esc_js(__('Resolved', 'userspn')); ?></span>');
              btn.fadeOut(200, function() { $(this).remove(); });
            } else {
              btn.prop('disabled', false).find('.dashicons').removeClass('dashicons-update').addClass('dashicons-saved');
            }
          } catch(e) {
            btn.prop('disabled', false).find('.dashicons').removeClass('dashicons-update').addClass('dashicons-saved');
          }
        });
      });

      // Delete old logs
      $('#userspn-logs-delete-old').on('click', function() {
        if (!confirm('<?php echo esc_js(__('Are you sure you want to delete logs older than 30 days?', 'userspn')); ?>')) return;

        var btn = $(this);
        btn.prop('disabled', true);

        $.post(ajaxurl, {
          action: 'userspn_ajax',
          userspn_ajax_type: 'userspn_security_log_delete_old',
          userspn_ajax_nonce: '<?php echo esc_js(wp_create_nonce('userspn-nonce')); ?>',
          days: 30
        }, function(response) {
          try {
            var data = typeof response === 'string' ? JSON.parse(response) : response;
            if (data.error_key === '') {
              location.reload();
            } else {
              btn.prop('disabled', false);
            }
          } catch(e) {
            btn.prop('disabled', false);
          }
        });
      });
    });
    </script>
    <?php
  }

  public function get_userspn_users_last_week()
  {
    $args = [
      'date_query' => [
        [
          'after' => '1 week ago',
        ],
      ],
      'fields' => ['ID', 'user_login', 'user_email', 'user_registered'],
    ];
    $users = get_users($args);
    $html = '<ul style="list-style:none;padding:0;">';
    if (!empty($users)) {
      foreach ($users as $user) {
        $html .= '<li style="padding:10px;border-bottom:1px solid #eee;">' . esc_html($user->user_login) . ' (' . esc_html($user->user_email) . ') - ' . esc_html(date('Y-m-d H:i', strtotime($user->user_registered))) . '</li>';
      }
    } else {
      $html .= '<li style="padding:10px;color:#666;">' . esc_html__('No hay usuarios nuevos en la última semana', 'userspn') . '</li>';
    }
    $html .= '</ul>';
    return [
      'count' => count($users),
      'html' => $html,
    ];
  }

  public function get_userspn_newsletter_last_week()
  {
    $args = [
      'meta_key' => 'userspn_newsletter_active',
      'meta_value' => '',
      'meta_compare' => '!=',
      'date_query' => [
        [
          'after' => '1 week ago',
          'column' => 'user_registered',
        ],
      ],
      'fields' => ['ID', 'user_login', 'user_email', 'user_registered'],
      'role__in' => ['userspn_newsletter_subscriber'],
    ];
    $users = get_users($args);
    $html = '<ul style="list-style:none;padding:0;">';
    if (!empty($users)) {
      foreach ($users as $user) {
        $html .= '<li style="padding:10px;border-bottom:1px solid #eee;">' . esc_html($user->user_login) . ' (' . esc_html($user->user_email) . ') - ' . esc_html(date('Y-m-d H:i', strtotime($user->user_registered))) . '</li>';
      }
    } else {
      $html .= '<li style="padding:10px;color:#666;">' . esc_html__('No hay altas en newsletter en la última semana', 'userspn') . '</li>';
    }
    $html .= '</ul>';
    return [
      'count' => count($users),
      'html' => $html,
    ];
  }

  public function get_userspn_last_logins()
  {
    $args = [
      'meta_key' => 'userspn_user_last_login',
      'orderby' => 'meta_value_num',
      'order' => 'DESC',
      'number' => 10,
      'fields' => ['ID', 'user_login', 'user_email'],
    ];
    $users = get_users($args);
    $html = '<ul style="list-style:none;padding:0;">';
    if (!empty($users)) {
      foreach ($users as $user) {
        $last_login = get_user_meta($user->ID, 'userspn_user_last_login', true);
        if (!empty($last_login)) {
          $html .= '<li style="padding:10px;border-bottom:1px solid #eee;">' . esc_html($user->user_login) . ' (' . esc_html($user->user_email) . ') - ' . date('Y-m-d H:i', intval($last_login)) . '</li>';
        }
      }
    } else {
      $html .= '<li style="padding:10px;color:#666;">' . esc_html__('No hay registros de últimos accesos', 'userspn') . '</li>';
    }
    $html .= '</ul>';
    return [
      'count' => count($users),
      'html' => $html,
    ];
  }

  public function userspn_bytes_format($size, $precision = 2)
  {
    $base = log($size, 1024);
    $suffixes = ['B', 'KB', 'MB', 'GB', 'TB'];

    return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
  }

  public function userspn_login_headerurl()
  {
    if (!empty(get_option('userspn_admin_page_logo_link'))) {
      return get_option('userspn_admin_page_logo_link');
    }
  }

  public function userspn_login_headertext()
  {
    if (!empty(get_option('userspn_admin_page_logo_link_text'))) {
      return __('Powered by', 'userspn') . ' ' . get_option('userspn_admin_page_logo_link_text');
    }
  }

  public function userspn_login_logo()
  {
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

  public function userspn_wp_before_admin_bar_render()
  {
    // Don't run if admin bar is disabled for current user
    if (!$this->userspn_should_show_admin_bar()) {
      return;
    }

    if (get_option('userspn_dashboard_logo') == 'on') {
      $userspn_admin_bar_render_css = '#wpadminbar #wp-admin-bar-wp-logo{background-color:#ffffff;}#wpadminbar #wp-admin-bar-wp-logo > .ab-item {padding:0 7px;background-image:url(' . esc_url(wp_get_attachment_image_src(get_option('userspn_admin_page_logo'), 'large')[0]) . ')!important;background-size:70%;background-color:#ffffff;background-position:center;background-repeat:no-repeat;opacity:0.8;} #wpadminbar #wp-admin-bar-wp-logo > .ab-item .ab-icon:before {content:" ";top:2px;}#wpadminbar #wp-admin-bar-wp-logo{background-color:#ffffff;}';

      wp_register_style('userspn-admin-bar-render-css', false, [], USERSPN_VERSION);
      wp_enqueue_style('userspn-admin-bar-render-css');
      wp_add_inline_style('userspn-admin-bar-render-css', $userspn_admin_bar_render_css);
    }
  }

  public function userspn_admin_bar_wp_menu($wp_admin_bar)
  {
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

  public function userspn_lostpassword_url($lostpassword_url, $redirect)
  {
    static $filtering = false;
    if ($filtering) {
      return $lostpassword_url;
    }
    $filtering = true;
    $nonce = wp_create_nonce('userspn-nonce');
    $result = add_query_arg('userspn_ajax_nopriv_nonce', $nonce, $lostpassword_url);
    $filtering = false;
    return $result;
  }

  public function userspn_add_nonce_to_lostpassword_form()
  {
    // Skip adding nonce to lost password form
    return;
  }

  /**
   * Check if admin bar should be shown for current user
   */
  private function userspn_should_show_admin_bar()
  {
    // Only run on front-end
    if (is_admin()) {
      return true;
    }

    // Check if admin bar removal is enabled
    if (get_option('userspn_admin_bar_removal') != 'on') {
      return true;
    }

    // Always allow administrators
    if (current_user_can('administrator')) {
      return true;
    }

    // Get current user and their roles
    $user = wp_get_current_user();
    if (!$user || !$user->exists()) {
      return false;
    }

    // Get allowed roles for admin bar
    $userspn_admin_bar_access_roles = get_option('userspn_admin_bar_access_roles') ?? [];

    // If no specific roles are set, hide admin bar for all non-administrators
    if (empty($userspn_admin_bar_access_roles)) {
      return false;
    }

    // Check if user has any of the allowed roles
    $user_roles = $user->roles;
    if (!is_array($user_roles)) {
      $user_roles = [];
    }

    return !empty(array_intersect($user_roles, $userspn_admin_bar_access_roles));
  }

  public function userspn_remove_admin_bar()
  {
    if (!$this->userspn_should_show_admin_bar()) {
      show_admin_bar(false);
    }
  }

  /**
   * Filter to control admin bar visibility
   */
  public function userspn_show_admin_bar_filter($show)
  {
    return $this->userspn_should_show_admin_bar() ? $show : false;
  }

  public function userspn_activated_plugin($plugin)
  {
    if ($plugin == 'userspn/userspn.php') {
      wp_redirect(esc_url(admin_url('admin.php?page=userspn_options')));
      exit();
    }
  }

  /**
   * Add Settings link to plugin actions
   */
  public function userspn_add_settings_link($links)
  {
    $settings_link = '<a href="' . admin_url('admin.php?page=userspn_options') . '">' . __('Settings', 'userspn') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
  }

  /**
   * Get available navigation menus
   *
   * @return array
   */
  private function get_available_menus()
  {
    $menus = [];

    // Check if current theme is a block theme
    $is_block_theme = wp_is_block_theme();

    if ($is_block_theme) {
      // For block themes, get all navigation menus
      $nav_menus = wp_get_nav_menus();
      if (!empty($nav_menus)) {
        foreach ($nav_menus as $menu) {
          $menus[$menu->term_id] = $menu->name;
        }
      }
    } else {
      // For classic themes, use registered nav menus
      $registered_menus = get_registered_nav_menus();

      if (!empty($registered_menus)) {
        foreach ($registered_menus as $location => $description) {
          $menus[$location] = $description;
        }
      }

      // Add theme locations if no registered menus
      if (empty($menus)) {
        $theme_locations = get_nav_menu_locations();
        if (!empty($theme_locations)) {
          foreach ($theme_locations as $location => $menu_id) {
            $menu = wp_get_nav_menu_object($menu_id);
            if ($menu) {
              $menus[$location] = $menu->name;
            }
          }
        }
      }
    }

    // If still empty, add default options
    if (empty($menus)) {
      if ($is_block_theme) {
        $menus['default'] = __('Default Menu', 'userspn');
      } else {
        $menus['primary'] = __('Primary Menu', 'userspn');
        $menus['secondary'] = __('Secondary Menu', 'userspn');
        $menus['footer'] = __('Footer Menu', 'userspn');
      }
    }

    return $menus;
  }
}

/**
 * Add Settings link to plugin actions - standalone function
 */
function userspn_add_settings_link_standalone($links)
{
  $settings_link = '<a href="' . admin_url('admin.php?page=userspn_options') . '">' . __('Settings', 'userspn') . '</a>';
  array_unshift($links, $settings_link);
  return $links;
}