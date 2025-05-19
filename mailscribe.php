<?php
/*
Plugin Name: MailScribe
Description: A full-featured email marketing plugin for WordPress – newsletter creation, automation, and subscriber management.
Version: 1.0
Author: IDM
*/

defined('ABSPATH') || exit;

define('MAILSCRIBE_VERSION', '1.0');
define('MAILSCRIBE_PATH', plugin_dir_path(__FILE__));
define('MAILSCRIBE_URL', plugin_dir_url(__FILE__));

require_once MAILSCRIBE_PATH . 'includes/functions.php';
require_once MAILSCRIBE_PATH . 'includes/class-subscriber.php';
require_once MAILSCRIBE_PATH . 'includes/class-campaign.php';
require_once MAILSCRIBE_PATH . 'includes/class-mailer.php';

if (is_admin()) {
    require_once MAILSCRIBE_PATH . 'admin/dashboard.php';
    require_once MAILSCRIBE_PATH . 'admin/subscribers.php';
    require_once MAILSCRIBE_PATH . 'admin/campaigns.php';
    require_once MAILSCRIBE_PATH . 'admin/settings.php';
    require_once MAILSCRIBE_PATH . 'admin/templates.php';
}

register_activation_hook(__FILE__, 'mailscribe_activate');
register_deactivation_hook(__FILE__, 'mailscribe_deactivate');

function mailscribe_activate() {
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $subscriber_table = $wpdb->prefix . 'mailscribe_subscribers';
    $campaign_table = $wpdb->prefix . 'mailscribe_campaigns';
    $template_table = $wpdb->prefix . 'mailscribe_templates';

    $sql = "
        CREATE TABLE $subscriber_table (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            name VARCHAR(100),
            status VARCHAR(20) DEFAULT 'subscribed',
            list_id INT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;

        CREATE TABLE $campaign_table (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            subject VARCHAR(255) NOT NULL,
            content LONGTEXT NOT NULL,
            list_id INT,
            template_id BIGINT(20),
            status VARCHAR(20) DEFAULT 'draft',
            scheduled_at DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;

        CREATE TABLE $template_table (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            content LONGTEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;
    ";

    dbDelta($sql);
}

function mailscribe_deactivate() {
    // Cleanup if needed
}
