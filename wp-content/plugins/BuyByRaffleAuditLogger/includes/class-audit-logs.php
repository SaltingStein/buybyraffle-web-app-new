<?php
/**
 * Class Audit_Logs
 * Handles the logging of various user activities..
 */

require_once 'class-db-connection.php';
require_once 'class-session-logs.php';
require_once 'class-content-logs.php';
require_once 'class-order-logs.php';

class Audit_Logs {
    private static $instance;

    public static function get_instance() {
        if (null === static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function init() {
        $session_logs = new Session_Logs();
        $content_logs = new Content_Logs();
        $order_logs = new Order_Logs();
        // Hooks for logging user login, content creation, deletion, and updates
        add_action('wp_login', array($session_logs, 'log_login'), 10, 2);
        add_action('wp_logout', array($session_logs, 'log_logout'));
        add_action('publish_post', array($content_logs, 'log_content_creation'), 10, 2);
        add_action('before_delete_post', array($content_logs, 'log_content_deletion'), 10);
        add_action('post_updated', array($content_logs, 'log_content_update'), 10, 3);

        // Additional hooks for logging WooCommerce order status changes and deletions
        add_action('woocommerce_order_status_changed', array($order_logs, 'log_order_status_change'), 10, 3);
        add_action('woocommerce_before_order_object_save', array($order_logs, 'log_order_deletion'), 10, 2);
    }   

}
