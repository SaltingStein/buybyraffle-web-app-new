<?php
/*
Plugin Name: Universal Message Hub
Description: OmniMessenger with multi-channel messaging capabilities
Version: 2.0
Author: SGS Team
*/
// Include the api.php file
// Get the directory path of the current plugin file

if (!defined('UMH_PLUGIN_DIR')) {
   define('UMH_PLUGIN_DIR', plugin_dir_path(__FILE__));

}
require_once UMH_PLUGIN_DIR . '/api.php';
require_once UMH_PLUGIN_DIR . 'admin/admin-settings.php';
function umh_activate_plugin() {
    global $wpdb;
	umh_create_message_provider_role();
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $charset_collate = $wpdb->get_charset_collate();

    // SQL for creating the wp_umh_messages table
    $sql_messages = "CREATE TABLE {$wpdb->prefix}umh_messages (
		id INT NOT NULL AUTO_INCREMENT,
		message_client INT NOT NULL COMMENT 'Client ID or identifier',
		message_ref VARCHAR(20) NOT NULL COMMENT 'Unique message reference sent by client',
		raffle_cycle_id VARCHAR(20) NOT NULL COMMENT 'Unique raffle cycle reference sent by client',
		type VARCHAR(5) NOT NULL COMMENT 'sms or email',
		program VARCHAR(12) NOT NULL  COMMENT 'user registration, order, raffle-draw',
		recipient VARCHAR(15) NOT NULL,
		content TEXT NOT NULL,
		pubsub_client_webhook_notified TINYINT NOT NULL DEFAULT 0 COMMENT '0: not notified, 1: notified',
		status TINYINT NOT NULL COMMENT '0: pending, 1: processing, 2: sent, 3: failed',
		provider_id INT NOT NULL COMMENT 'Provider ID reference WordPress user ID of the provider in use',
		created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
		sent_at DATETIME NULL,
		PRIMARY KEY (id),
		UNIQUE KEY message_client_ref (message_client, message_ref),
		UNIQUE KEY raffle_cycle_id (raffle_cycle_id)
	) $charset_collate;";

    // SQL for creating the wp_umh_providers table
    $sql_providers = "CREATE TABLE {$wpdb->prefix}umh_providers (
        id INT NOT NULL AUTO_INCREMENT,
        user_id INT NOT NULL,
		service_type varchar(5) NOT NULL COMMENT 'Service may be sms or email',
        priority_score INT NOT NULL DEFAULT 0 COMMENT 'Priority score for providers',
        api_key TEXT NULL COMMENT 'API key for the provider',
        active TINYINT NOT NULL DEFAULT 1 COMMENT 'Provider status (0 for inactive, 1 for active)',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        INDEX active (active),
        INDEX priority_score (priority_score)
    ) $charset_collate;";
    
    

    // SQL for creating the wp_umh_error_logs table
    $sql_error_logs = "CREATE TABLE {$wpdb->prefix}umh_error_logs (
        id BIGINT NOT NULL AUTO_INCREMENT,
        error_message TEXT NOT NULL,
        error_context TEXT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    

    // Run the SQL commands
    dbDelta($sql_error_logs);
    dbDelta($sql_messages);
    dbDelta($sql_providers);
    if ($wpdb->last_error) {
        error_log("Error creating providers table: " . $wpdb->last_error);
    }

}

register_activation_hook(__FILE__, 'umh_activate_plugin');
function umh_register_provider_on_user_creation($user_id) {
    $user_info = get_userdata($user_id);
    $roles = $user_info->roles;

    // Check if the user has the 'message_provider' role
    if (in_array('notification_service_provider', $roles)) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'umh_providers';

        // Insert the new provider into the umh_providers table
        $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                // Set other fields as needed, like 'status'
                'status' => 1 // Assuming 1 means active
            ),
            array('%d', '%d')
        );
    }
}

add_action('user_register', 'umh_register_provider_on_user_creation');
function umh_update_provider_status_on_user_deletion($user_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'umh_providers';

    // Get user data to check the roles
    $user_info = get_userdata($user_id);
    if (!$user_info) {
        return; // User data not found
    }

    $roles = $user_info->roles;

    // Check if the user had the 'message_provider' role
    if (in_array('notification_service_provider', $roles)) {
        // Update the active status in the umh_providers table
        $wpdb->update(
            $table_name,
            array('active' => 0), // Set active to 0 (inactive)
            array('user_id' => $user_id), // Where clause
            array('%d'), // Data format
            array('%d')  // Where format
        );
    }
}

add_action('deleted_user', 'umh_update_provider_status_on_user_deletion');
add_action('set_user_role', 'umh_update_provider_status_on_user_deletion', 10, 3);

function umh_log_error($message, $context = '') {
    global $wpdb;
    $wpdb->insert(
        "{$wpdb->prefix}umh_error_logs",
        array('error_message' => $message, 'error_context' => json_encode($context)),
        array('%s', '%s')
    );
}
// Function to create the message provider role
function umh_create_message_provider_role() {
    // Get the subscriber role
    $subscriber = get_role('subscriber');

    // Add a new role with subscriber capabilities
    add_role('notification_service_provider', 'Notification service provider', $subscriber->capabilities);
}

