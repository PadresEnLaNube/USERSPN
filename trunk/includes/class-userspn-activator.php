<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    userspn
 * @subpackage userspn/includes
 * @author     Padres en la Nube <info@padresenlanube.com>
 */
class USERSPN_Activator {
	/**
   * Plugin activation functions
   *
   * Functions to be loaded on plugin activation. This actions creates roles, options and post information attached to the plugin.
	 *
	 * @since    1.0.0
	 */
	public static function userspn_activate() {
    add_role('userspn_newsletter_subscriber', 'Newsletter Subscriber', ['read' => true]);

    // Create security logs table
    self::userspn_create_security_logs_table();

    // Flush rewrite rules once on activation; do not defer to footer (would run on
    // next request and can cause 503 on checkout/heavy pages).
    flush_rewrite_rules();

    // Set a flag to redirect to options page after activation
    update_option('userspn_redirect_to_options', true);

    // Create passkeys table
    self::userspn_create_passkeys_table();

    // Store DB version for upgrade path
    update_option('userspn_db_version', '1.1.61');
  }

  /**
   * Create security logs database table
   */
  public static function userspn_create_security_logs_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'userspn_security_logs';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
      id bigint(20) NOT NULL AUTO_INCREMENT,
      user_id bigint(20) DEFAULT 0 NOT NULL,
      created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
      ip_address varchar(45) DEFAULT '' NOT NULL,
      user_agent text NOT NULL,
      event varchar(100) DEFAULT '' NOT NULL,
      message text NOT NULL,
      data longtext NOT NULL,
      email varchar(255) DEFAULT '' NOT NULL,
      resolved tinyint(1) DEFAULT 0 NOT NULL,
      PRIMARY KEY  (id),
      KEY event (event),
      KEY created_at (created_at),
      KEY user_id (user_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }

  /**
   * Create passkeys database table
   */
  public static function userspn_create_passkeys_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'userspn_passkeys';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
      id bigint(20) NOT NULL AUTO_INCREMENT,
      user_id bigint(20) NOT NULL,
      credential_id varbinary(1023) NOT NULL,
      public_key text NOT NULL,
      sign_count bigint(20) DEFAULT 0 NOT NULL,
      device_name varchar(255) DEFAULT '' NOT NULL,
      created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
      last_used_at datetime DEFAULT NULL,
      PRIMARY KEY  (id),
      KEY user_id (user_id),
      UNIQUE KEY credential_id (credential_id(255))
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }
}