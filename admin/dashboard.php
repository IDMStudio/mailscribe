<?php
defined('ABSPATH') || exit;

function mailscribe_dashboard_page() {
    global $wpdb;
    $campaigns_table = $wpdb->prefix . 'mailscribe_campaigns';
    $subscribers_table = $wpdb->prefix . 'mailscribe_subscribers';
    $templates_table = $wpdb->prefix . 'mailscribe_templates';

    $total_campaigns = $wpdb->get_var("SELECT COUNT(*) FROM $campaigns_table");
    $total_subscribers = $wpdb->get_var("SELECT COUNT(*) FROM $subscribers_table");
    $total_templates = $wpdb->get_var("SELECT COUNT(*) FROM $templates_table");

    $latest_campaigns = $wpdb->get_results("SELECT id, subject, status, created_at FROM $campaigns_table ORDER BY created_at DESC LIMIT 5");
    ?>

    <div class="wrap">
        <h1>MailScribe Dashboard</h1>

        <div style="display: flex; gap: 20px; margin-bottom: 30px;">
            <div class="postbox" style="flex:1;">
                <h2 class="hndle">Campaigns</h2>
                <div class="inside">
                    <p><strong><?php echo esc_html($total_campaigns); ?></strong> total campaigns</p>
                    <a href="<?php echo admin_url('admin.php?page=mailscribe_campaigns'); ?>" class="button">Manage Campaigns</a>
                </div>
            </div>
            <div class="postbox" style="flex:1;">
                <h2 class="hndle">Subscribers</h2>
                <div class="inside">
                    <p><strong><?php echo esc_html($total_subscribers); ?></strong> total subscribers</p>
                    <a href="<?php echo admin_url('admin.php?page=mailscribe_subscribers'); ?>" class="button">Manage Subscribers</a>
                </div>
            </div>
            <div class="postbox" style="flex:1;">
                <h2 class="hndle">Templates</h2>
                <div class="inside">
                    <p><strong><?php echo esc_html($total_templates); ?></strong> templates created</p>
                    <a href="<?php echo admin_url('admin.php?page=mailscribe_templates'); ?>" class="button">Manage Templates</a>
                </div>
            </div>
        </div>

        <h2>Recent Campaigns</h2>
        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($latest_campaigns): ?>
                    <?php foreach ($latest_campaigns as $campaign): ?>
                        <tr>
                            <td><?php echo esc_html($campaign->subject); ?></td>
                            <td><?php echo esc_html(ucfirst($campaign->status)); ?></td>
                            <td><?php echo esc_html($campaign->created_at); ?></td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=mailscribe_campaigns&edit=' . $campaign->id); ?>">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4">No campaigns found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php } ?>
