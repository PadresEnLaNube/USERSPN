<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 *
 * @package    USERSPN
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

global $wpdb;

// Remove plugin options
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'userspn\_%'");

// Remove plugin user meta
$wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'userspn\_%'");

// Remove plugin transients
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_userspn\_%' OR option_name LIKE '_transient_timeout_userspn\_%'");

// Drop plugin custom tables
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}userspn_security_logs");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}userspn_passkeys");

// Remove scheduled cron events
wp_clear_scheduled_hook('userspn_daily_cron');
wp_clear_scheduled_hook('userspn_hourly_cron');
