<?php
defined('ABSPATH') || exit;

function mailscribe_campaigns_page() {
    global $wpdb;
    $campaigns_table = $wpdb->prefix . 'mailscribe_campaigns';
    $templates_table = $wpdb->prefix . 'mailscribe_templates';

    // Handle deletion
    if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
        $id = intval($_GET['delete']);
        $wpdb->delete($campaigns_table, ['id' => $id]);
        echo '<div class="notice notice-success"><p>Campaign deleted successfully.</p></div>';
    }

    // Handle form submission (Create or Update)
    if (isset($_POST['mailscribe_save_campaign'])) {
        $subject = sanitize_text_field($_POST['subject']);
        $content = wp_kses_post($_POST['content']);
        $template_id = intval($_POST['template_id']);
        $status = sanitize_text_field($_POST['status']);

        if (isset($_POST['campaign_id']) && $_POST['campaign_id']) {
            // Update existing campaign
            $wpdb->update($campaigns_table, [
                'subject' => $subject,
                'content' => $content,
                'template_id' => $template_id,
                'status' => $status,
            ], ['id' => intval($_POST['campaign_id'])]);
        } else {
            // Insert new campaign
            $wpdb->insert($campaigns_table, [
                'subject' => $subject,
                'content' => $content,
                'template_id' => $template_id,
                'status' => $status,
                'created_at' => current_time('mysql')
            ]);
        }
        echo '<div class="notice notice-success"><p>Campaign saved successfully.</p></div>';
    }

    // Edit view
    if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
        $id = intval($_GET['edit']);
        $campaign = $wpdb->get_row("SELECT * FROM $campaigns_table WHERE id = $id");
        $subject = $campaign->subject;
        $content = $campaign->content;
        $template_id = $campaign->template_id;
        $status = $campaign->status;
    } else {
        $id = 0;
        $subject = '';
        $content = '';
        $template_id = 0;
        $status = 'draft';
    }

    $templates = $wpdb->get_results("SELECT id, name_
