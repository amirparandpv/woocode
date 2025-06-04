<?php

function display_notification_message() {
    $notification_message = get_option('notification_message', '');
    if (!empty($notification_message)) {
        return '<div class="notification-message">' . esc_html($notification_message) . '</div>';
    } else {
        return '<div class="notification-message">پیامی تنظیم نشده است.</div>';
    }
}
add_shortcode('notification_message', 'display_notification_message');


?>
