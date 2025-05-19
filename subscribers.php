<?php
defined('ABSPATH') || exit;

function mailscribe_subscribers_page() {
	if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if (wp_verify_nonce($_REQUEST['_wpnonce'], 'mailscribe_delete_subscriber_' . $id)) {
        global $wpdb;
        $table = $wpdb->prefix . 'mailscribe_subscribers';
        $wpdb->delete($table, ['id' => $id]);

        echo '<div class="notice notice-success is-dismissible"><p>Subscriber deleted successfully.</p></div>';
    } else {
        echo '<div class="notice notice-error"><p>Security check failed. Subscriber was not deleted.</p></div>';
	}
	}
	
	if (
    isset($_GET['action']) && 
    $_GET['action'] === 'toggle_status' && 
    isset($_GET['id']) && 
    is_numeric($_GET['id'])
) {
    $id = intval($_GET['id']);
    
    if (
        isset($_GET['_wpnonce']) &&
        wp_verify_nonce($_GET['_wpnonce'], 'mailscribe_toggle_status_' . $id)
    ) {
        global $wpdb;
        $table = $wpdb->prefix . 'mailscribe_subscribers';

        $current_status = $wpdb->get_var($wpdb->prepare("SELECT status FROM $table WHERE id = %d", $id));

        if ($current_status) {
            $new_status = $current_status === 'subscribed' ? 'unsubscribed' : 'subscribed';

            $wpdb->update($table, ['status' => $new_status], ['id' => $id]);

            echo '<div class="notice notice-success is-dismissible"><p>Status updated to ' . esc_html($new_status) . '.</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>Subscriber not found.</p></div>';
        }
    } else {
        echo '<div class="notice notice-error"><p>Security check failed. Invalid or missing nonce.</p></div>';
    }
}

    global $wpdb;
    $table = $wpdb->prefix . 'mailscribe_subscribers';
    $subscribers = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");

    echo '<div class="wrap">';
    echo '<h1>Subscribers</h1>';
    echo '<a href="' . admin_url('admin.php?page=mailscribe_add_subscriber') . '" class="page-title-action">Add New</a>';
	echo '<div id="mailscribe-toast" class="mailscribe-toast">Status Updated!</div>';
    echo '<table class="widefat fixed striped">';
    echo '<thead><tr>
            <th>Name</th>
            <th>Email</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
          </tr></thead>';
    echo '<tbody>';

    if ($subscribers) {
        foreach ($subscribers as $s) {
            echo '<tr>';
            echo '<td>' . esc_html($s->name) . '</td>';
            echo '<td>' . esc_html($s->email) . '</td>';
			$toggle_url = wp_nonce_url(
    admin_url('admin.php?page=mailscribe_subscribers&action=toggle_status&id=' . $s->id),
    'mailscribe_toggle_status_' . $s->id
);

$is_checked = $s->status === 'subscribed' ? 'checked' : '';

echo '<td>
    <label class="mailscribe-switch">
        <input type="checkbox" class="mailscribe-toggle-status" data-id="' . esc_attr($s->id) . '" ' . ($s->status === 'subscribed' ? 'checked' : '') . '>
        <span class="mailscribe-slider"></span>
    </label>
</td>';

            echo '<td>' . esc_html($s->created_at) . '</td>';
			
			
			$delete_url = wp_nonce_url(
			admin_url('admin.php?page=mailscribe_subscribers&action=delete&id=' . $s->id),
			'mailscribe_delete_subscriber_' . $s->id
			);
			
            echo '<td><a href="' . admin_url('admin.php?page=mailscribe_edit_subscriber&id=' . $s->id) . '">Edit</a> | <a href="' . esc_url($delete_url) . '" onclick="return confirm(\'Are you sure you want to delete this subscriber?\')">Delete</a></td>';
			

			echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="5">No subscribers found.</td></tr>';
    }

    echo '</tbody></table></div>';
}

function mailscribe_add_subscriber_page() {
    if (isset($_POST['mailscribe_add_subscriber'])) {
        mailscribe_save_new_subscriber();
    }

    ?>
    <div class="wrap">
        <h1>Add New Subscriber</h1>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th><label for="name">Name</label></th>
                    <td><input type="text" name="name" id="name" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="email">Email</label></th>
                    <td><input type="email" name="email" id="email" class="regular-text" required></td>
                </tr>
            </table>
            <p>
                <input type="submit" name="mailscribe_add_subscriber" class="button-primary" value="Add Subscriber">
                <a href="<?php echo admin_url('admin.php?page=mailscribe_subscribers'); ?>" class="button">Cancel</a>
            </p>
        </form>
    </div>
    <?php
}

function mailscribe_save_new_subscriber() {
    global $wpdb;
    $table = $wpdb->prefix . 'mailscribe_subscribers';

    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);

    if (!is_email($email)) {
        echo '<div class="notice notice-error"><p>Invalid email address.</p></div>';
        return;
    }

    $existing = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE email = %s", $email));
    if ($existing > 0) {
        echo '<div class="notice notice-warning"><p>This email is already subscribed.</p></div>';
        return;
    }

    $wpdb->insert($table, [
        'name' => $name,
        'email' => $email,
        'status' => 'subscribed',
        'created_at' => current_time('mysql')
    ]);

    echo '<div class="notice notice-success"><p>Subscriber added successfully.</p></div>';
}

function mailscribe_edit_subscriber_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'mailscribe_subscribers';

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if (!$id) {
        echo '<div class="notice notice-error"><p>Invalid subscriber ID.</p></div>';
        return;
    }

    $subscriber = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));

    if (!$subscriber) {
        echo '<div class="notice notice-error"><p>Subscriber not found.</p></div>';
        return;
    }

    if (isset($_POST['mailscribe_update_subscriber'])) {
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $status = sanitize_text_field($_POST['status']);

        $wpdb->update($table, [
            'name' => $name,
            'email' => $email,
            'status' => $status
        ], ['id' => $id]);

        echo '<div class="notice notice-success"><p>Subscriber updated.</p></div>';

        // Refresh data
        $subscriber = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }

    ?>
    <div class="wrap">
        <h1>Edit Subscriber</h1>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th><label for="name">Name</label></th>
                    <td><input type="text" name="name" id="name" class="regular-text" value="<?php echo esc_attr($subscriber->name); ?>" required></td>
                </tr>
                <tr>
                    <th><label for="email">Email</label></th>
                    <td><input type="email" name="email" id="email" class="regular-text" value="<?php echo esc_attr($subscriber->email); ?>" required></td>
                </tr>
                <tr>
                    <th><label for="status">Status</label></th>
                    <td>
                        <select name="status" id="status">
                            <option value="subscribed" <?php selected($subscriber->status, 'subscribed'); ?>>Subscribed</option>
                            <option value="unsubscribed" <?php selected($subscriber->status, 'unsubscribed'); ?>>Unsubscribed</option>
                        </select>
                    </td>
                </tr>
            </table>
            <p>
                <input type="submit" name="mailscribe_update_subscriber" class="button-primary" value="Update Subscriber">
                <a href="<?php echo admin_url('admin.php?page=mailscribe_subscribers'); ?>" class="button">Back</a>
            </p>
        </form>
    </div>
    <?php
}

