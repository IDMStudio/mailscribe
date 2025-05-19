<?php
defined('ABSPATH') || exit;

add_action('admin_menu', 'mailscribe_templates_menu');

function mailscribe_templates_menu() {
    add_submenu_page(
        'mailscribe',
        'Email Templates',
        'Templates',
        'manage_options',
        'mailscribe_templates',
        'mailscribe_templates_page'
    );
}

function mailscribe_templates_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'mailscribe_templates';

    if (isset($_POST['save_template'])) {
        $name = sanitize_text_field($_POST['template_name']);
        $content = wp_kses_post($_POST['template_content']);

        $wpdb->insert($table, [
            'name' => $name,
            'content' => $content,
            'created_at' => current_time('mysql')
        ]);

        if ($wpdb->last_error) {
            echo '<div class="notice notice-error"><p>' . $wpdb->last_error . '</p></div>';
        } else {
            echo '<div class="notice notice-success is-dismissible"><p>Template saved successfully!</p></div>';
        }
    }

    $templates = $wpdb->get_results("SELECT * FROM $table");

    echo '<div class="wrap"><h1>Email Templates</h1>';

    echo '<form method="post">
        <label for="template_name">Template Name:</label><br>
        <input type="text" name="template_name" id="template_name" required><br><br>
        <label for="template_content">Content:</label><br>
        <textarea name="template_content" id="template_content" rows="10" required></textarea><br><br>
        <input type="submit" name="save_template" value="Save Template">
    </form><hr>';

    echo '<h2>Saved Templates</h2>';
    if ($templates) {
        echo '<ul>';
        foreach ($templates as $template) {
            echo '<li><strong>' . esc_html($template->name) . '</strong> - ' . esc_html($template->created_at) . '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>No templates found.</p>';
    }

    echo '</div>';
}
