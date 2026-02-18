=== Users manager - PN ===
Contributors: felixmartinez, hamlet237
Donate link: https://padresenlanube.com/
Tags: user management, users, register, login, contacts
Tested up to: 6.8
Stable tag: 1.1.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Streamline user management on your WordPress site with this powerful plugin. Enable custom forms, secure login, and seamless profile management.

== Description ==

This WordPress plugin simplifies user management by providing customizable registration forms, login functionality, and profile editing directly on your website. Perfect for membership sites, e-commerce, and community platforms, it supports advanced features like role-based access control, email verification, and user activity tracking. Its intuitive interface allows easy setup and management, while offering compatibility with popular themes and plugins. Enhance your site's user experience with responsive designs and seamless integrations. Whether for small businesses or large-scale websites, this plugin is your all-in-one solution for efficient user management.

**Core User Management Features:**

* **Frontend User Registration**: Complete user registration system accessible directly from your website's frontend. Users can create accounts without accessing the WordPress admin area, with customizable registration forms that include email, password, and unlimited custom fields.

* **Custom Login System**: Beautiful, responsive login forms that integrate seamlessly with your site design. Includes "Remember Me" functionality and password recovery links, all accessible via shortcodes.

* **Advanced Profile Management**: Comprehensive user profile system with tabbed interface including Profile editing, Image/avatar management, Notifications preferences, Private file management, and Advanced options (password change, account deletion). Users can edit their profiles directly from the frontend with real-time validation.

* **Profile Completion Tracking**: Visual progress indicator showing profile completion percentage. Administrators can configure which fields count toward completion, encouraging users to complete their profiles with helpful links to incomplete sections.

**Powerful Form Builder:**

The plugin includes a sophisticated form builder that allows you to create custom registration and profile forms with a wide variety of field types:

* **Input Fields**: Text, number, email, password, URL, date, time, color, hidden, and nonce fields. Password fields include strength checker with visual feedback and requirements validation.

* **Advanced Input Types**: Range sliders with min/max labels, star rating systems (customizable number of stars), checkbox switches with toggle styling, and radio button groups with custom styling.

* **Media Fields**: Image upload with gallery support (single or multiple), video upload and management, audio file upload, and general file upload with preview.

* **Rich Content Fields**: Textarea for longer text input, WYSIWYG editor (Trumbowyg) for rich text editing, HTML content blocks, and audio recorder with transcription capabilities.

* **Selection Fields**: Single and multiple select dropdowns with search functionality, custom selector component with AJAX search, and tag input system with autocomplete suggestions.

* **Complex Fields**: Multi-field groups (html_multi) that allow creating repeating field sets with drag-and-drop reordering, conditional fields that show/hide based on parent field values, and field sections with collapsible groups.

All form fields support: custom CSS classes, required field validation, placeholder text, help descriptions, default values, conditional display logic, and custom validation rules.

**Security Features:**

* **Google reCAPTCHA v3 Integration**: Invisible bot protection with score-based verification. Configurable threshold settings and automatic suspicious registration detection with email notifications to administrators.

* **Honeypot Protection**: Hidden form fields that trap automated bots without affecting legitimate users.

* **Rate Limiting**: Configurable limits on registration attempts per IP address within specified time windows to prevent abuse.

* **Akismet Integration**: Spam detection for user registrations using WordPress's built-in Akismet service.

* **Bot Analysis Tool**: Advanced analysis system that examines existing users for bot patterns including suspicious email patterns, bot-like usernames, empty profiles, rapid registrations from same IP, suspicious user agents, sequential email patterns, and generic display names. Provides detailed reports with suspicion scores and pattern identification.

* **Security Logging**: Comprehensive logging system that tracks security events, failed registration attempts, suspicious activities, and bot confirmations for administrative review.

**User Profile Features:**

* **Custom Avatar System**: Users can upload custom profile pictures with automatic Gravatar fallback. Includes random avatar generation with color-coded initials for users without images. Supports custom image sets for default avatars.

* **Profile Fields Management**: Administrators can add unlimited custom fields to user profiles through an intuitive interface. Fields can be added, edited, or removed dynamically without code changes. Supports all form field types mentioned above.

* **Profile Validation**: Real-time validation of required profile fields with visual indicators. Users are prompted to complete missing required information before accessing certain features.

* **Auto-Login Tool**: Administrators can log in as any user (except other administrators) for support and testing purposes. Includes secure token-based authentication and automatic session management.

* **Account Management**: Users can change passwords through WordPress's standard password reset system, delete their own accounts with password confirmation, and manage notification preferences.

**Notifications System:**

* **Integrated Notifications**: Full integration with Mail Manager - PN plugin for advanced email notifications. Users can manage their notification preferences directly from their profile.

* **Newsletter Management**: Newsletter subscription system with email activation links. Users receive welcome emails upon newsletter activation. Supports multi-language notification preferences when Polylang is active.

* **Notification Preferences**: Users can opt-in/opt-out of system notifications with a simple checkbox. Language selection for notifications when multiple languages are available.

**CSV Import/Export:**

* **Bulk User Import**: Import multiple users at once using CSV files. Download customizable CSV templates that include all registered profile fields with proper formatting instructions.

* **Template Generation**: Automatic CSV template generation based on your current form field configuration. Includes example rows showing proper data formats for each field type.

* **Data Validation**: CSV import includes validation for email addresses, role assignments, date formats, and custom field types before user creation.

**Shortcodes:**

The plugin provides numerous shortcodes for easy integration:

* `[userspn-profile]` - Complete user profile interface (popup or inline)
* `[userspn-login]` - Login form
* `[userspn-user-register-form]` - Registration form
* `[userspn-profile-edit]` - Profile editing form
* `[userspn-profile-image]` - Avatar/image management
* `[userspn-get-avatar]` - Display user avatar
* `[userspn-notifications]` - Notification preferences
* `[userspn-user-files]` - Private file management
* `[userspn-csv-template]` - Download CSV template
* `[userspn-csv-template-upload]` - CSV upload interface
* `[userspn-call-to-action]` - Customizable call-to-action blocks
* `[userspn-newsletter]` - Newsletter subscription form

**Gutenberg Blocks:**

* **User Profile Block**: Native Gutenberg block for inserting user profiles directly into pages and posts. Supports inline display mode and integrates seamlessly with the block editor.

**Additional Features:**

* **Multi-language Support**: Full Polylang integration for multi-language sites. Users can select their preferred notification language, and profile fields adapt to the current language context.

* **Responsive Design**: All forms and interfaces are fully responsive and mobile-friendly, ensuring a great experience on all devices.

* **AJAX-Powered**: Forms submit via AJAX without page reloads, providing a smooth user experience with loading indicators and success/error messages.

* **Customizable Styling**: Extensive CSS classes and structure allow for easy theme customization. All components follow consistent naming conventions.

* **WooCommerce Compatibility**: Designed to work seamlessly with WooCommerce, avoiding conflicts during checkout processes.

* **Cron Jobs**: Built-in scheduled tasks for maintenance, cleanup, and automated processes.

* **Developer-Friendly**: Extensive hooks and filters for developers to extend functionality. Clean, well-documented code following WordPress coding standards.

**Technical Highlights:**

* Uses WordPress best practices for security, sanitization, and validation
* Proper nonce verification for all form submissions
* KSES filtering for safe HTML output
* Efficient database queries and caching where appropriate
* Proper script and style enqueuing with dependency management
* Translation-ready with .pot file included
* Compatible with WordPress 3.0.1+ and PHP 7.2+

This plugin transforms WordPress user management from a backend-only task into a comprehensive, user-friendly frontend experience that enhances engagement and simplifies administration.


== Credits ==
This plugin stands on the shoulders of giants

Tooltipster v4.2.8 - A rockin' custom tooltip jQuery plugin
Developed by Caleb Jacob and Louis Ameline
MIT license
https://calebjacob.github.io/tooltipster/
https://github.com/calebjacob/tooltipster/blob/master/dist/js/tooltipster.main.js
https://github.com/calebjacob/tooltipster/blob/master/dist/css/tooltipster.main.css

Owl Carousel v2.3.4
Licensed under: SEE LICENSE IN https://github.com/OwlCarousel2/OwlCarousel2/blob/master/LICENSE
Copyright 2013-2018 David Deutsch
https://owlcarousel2.github.io/OwlCarousel2/
https://github.com/OwlCarousel2/OwlCarousel2/blob/develop/dist/owl.carousel.js

Trumbowyg v2.27.3 - A lightweight WYSIWYG editor
alex-d.github.io/Trumbowyg/
License MIT - Author : Alexandre Demode (Alex-D)
https://github.com/Alex-D/Trumbowyg/blob/develop/src/ui/sass/trumbowyg.scss
https://github.com/Alex-D/Trumbowyg/blob/develop/src/ui/sass/trumbowyg.scss
https://github.com/Alex-D/Trumbowyg/blob/develop/src/trumbowyg.js


== Installation ==

1. Upload `userspn.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= How do I install the Users manager - PN plugin? =

To install the Users manager - PN plugin, you can either upload the plugin files to the /wp-content/plugins/userspn directory, or install the plugin through the WordPress plugins screen directly. After uploading, activate the plugin through the 'Plugins' screen in WordPress.

= Can I customize the look and feel of my recipe listings? =

Yes, you can customize the appearance of your recipe listings by modifying the CSS styles provided in the plugin. Additionally, you can enqueue your own custom styles to override the default plugin styles.

= Where can I find the uncompressed source code for the plugin's JavaScript and CSS files? =

You can find the uncompressed source code for the JavaScript and CSS files in the src directory of the plugin. You can also visit our GitHub repository for the complete source code.

= How do I add a new recipe to my site? =

To add a new recipe, go to the 'Host' section in the WordPress dashboard and click on 'Add New'. Fill in the required details for your recipe, including the title, ingredients, steps, and any other custom fields provided by the plugin. Once you're done, click 'Publish' to make the recipe live on your site.

= Can I use this plugin with any WordPress theme? =

Yes, the Users manager - PN plugin is designed to be compatible with any WordPress theme. However, some themes may require additional customization to ensure the plugin's styles integrate seamlessly.

= Is the plugin translation-ready? =

Yes, the Users manager - PN plugin is fully translation-ready. You can use translation plugins such as Loco Translate to translate the plugin into your desired language.

= How do I update the plugin? =

You can update the plugin through the WordPress plugins screen just like any other plugin. When a new version is available, you will see an update notification, and you can click 'Update Now' to install the latest version.

= How do I backup my recipes before updating the plugin? =

To backup your recipes, you can export your posts and custom post types from the WordPress Tools > Export menu. Choose the 'Host' post type and download the export file. You can import this file later if needed.

= How do I add ratings and reviews to my recipes? =

The plugin don't include a built-in ratings and reviews system yet. You can integrate third-party plugins that offer these features or customize the plugin to include them.

= How do I optimize my recipes for SEO? =

To optimize your recipes for SEO, ensure that you use relevant keywords in your recipe titles, descriptions, and content. You can also use SEO plugins like Yoast SEO to further enhance your recipe posts' search engine visibility.

= How do I get support for the Users manager - PN plugin? =

For support, you can visit the plugin's support forum on the WordPress.org website or contact the plugin author directly through our contact information info@padresenlanube.com.

= Is the plugin compatible with the latest version of WordPress? =

The Users manager - PN plugin is tested with the latest version of WordPress. However, it is always a good practice to check for any compatibility issues before updating WordPress or the plugin.

= How do I uninstall the plugin? =

To uninstall the plugin, go to the 'Plugins' screen in WordPress, find the Users manager - PN plugin, and click 'Deactivate'. After deactivating, you can click 'Delete' to remove the plugin and its files from your site. Note that this will not delete your recipes, but you should back up your data before uninstalling any plugin.


== Developers ==

=== Architecture Overview ===

The plugin follows WordPress coding standards and uses an object-oriented architecture. The core class (`USERSPN`) orchestrates functionality through a loader system (`USERSPN_Loader`) that manages actions, filters, and shortcodes.

* Main plugin file: `userspn.php` - Constants, activation/deactivation hooks, initialization
* Core class: `includes/class-userspn.php` - Loads dependencies and registers hooks
* Modular classes: Each feature is encapsulated in its own class file (Forms, Security, Validation, CSV, etc.)

=== Available Hooks ===

**Actions:**
* `userspn_user_register` - Fires after user registration
* `userspn_user_wp_login` - Fires on login
* `userspn_cron_daily` - Daily scheduled task
* `userspn_cron_thirty_minutes` - 30-minute scheduled task

**Filters:**
* `userspn_get_avatar` - Filter avatar output
* `userspn_body_classes` - Add custom body classes
* `userspn_show_admin_bar` - Control admin bar visibility
* `userspn_lostpassword_url` - Customize lost password URL

**AJAX Endpoints:**
* `wp_ajax_userspn_ajax` - Authenticated requests
* `wp_ajax_nopriv_userspn_ajax_nopriv` - Non-authenticated requests

=== Support ===

For developer support, bug reports, or feature requests:
* Email: info@padresenlanube.com
* Website: https://padresenlanube.com/


== Screenshots ==

1. Grid Host portfolio front-end view.
2. Recipe details front-end page. It includes the ingredients list view, steps and suggestions.
3. Interactive view of the recipe steps with time ticking.
4. Dashboard recipes list view.
5. Dashboard recipe edition page including meta fields.


== Changelog ==

= 1.1.5 =

WP references swapped to PN
wordpress-heroes.com to padresenlanube.com
New prefixes in CSS root styles
Popup CSS styles centralized
New ajax loader based on CSS completely
mail_userspn_init to MAILPN slug
Forms style classes fixes
'true' strings to boolean in forms arrays
Notifications finally managed by Mail Manager - PN
CSS and JS loaded correctly with localize and enqueue functions
Dashboard logo options fixed
New version 1.0.0 updated in main
Ready for last version release XD
README.txt and main php file changed in Tag 1.0.0

= 1.0.0 =

Hello world!