<?php
/**
 * Plugin Name: PGS Vouchers
 * Description: A WordPress plugin for generating and managing e-pins for vouchers.
 * Version: 1.0
 * Author: SGS Team <Joseph>
 */

// Load PhpSpreadsheet library for Excel file creation.
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

use Google\Cloud\PubSub\PubSubClient;


/**
 * Define the plugin directory path as a constant for easy access throughout the plugin.
 */
if (!defined('PGS_VOUCHERS')) {
    define('PGS_VOUCHERS', plugin_dir_path(__FILE__));
}

// Include the WooCommerce payment gateway if WooCommerce is active.
function include_pgs_payment_gateway_file() {
    if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        require_once PGS_VOUCHERS . 'payment/wc-payment-gateway-voucher.php';
    } 
}
// Hook to the plugins_loaded action
add_action('plugins_loaded', 'include_pgs_payment_gateway_file');

/**
 * Plugin activation hook.
 * Creates necessary database tables for storing e-pin batches and individual vouchers.
 */
register_activation_hook(__FILE__, 'epin_plugin_activate');
function epin_plugin_activate() {
    // Create tables for batches and vouchers...
    global $wpdb;
    update_option('pgs_voucher_denomination', '200');
    $batch_table_name = $wpdb->prefix . 'epin_batches';
    $voucher_table_name = $wpdb->prefix . 'epin_vouchers';

    $charset_collate = $wpdb->get_charset_collate();

    // Create the batch table
    $batch_table_sql = "CREATE TABLE  IF NOT EXISTS $batch_table_name (
        id INT NOT NULL AUTO_INCREMENT,
        batch_id VARCHAR(36) NOT NULL,
        created_by INT NOT NULL,
        denomination DECIMAL(10, 2) NOT NULL,
        number_of_pins INT NOT NULL,
        status VARCHAR(20) NOT NULL,
        date_created DATETIME NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($batch_table_sql);

    // Create the voucher table
    $voucher_table_sql = "CREATE TABLE  IF NOT EXISTS $voucher_table_name (
        id INT NOT NULL AUTO_INCREMENT,
        voucher_pin VARCHAR(10) NOT NULL,
        batch_id VARCHAR(36) NOT NULL,
        status VARCHAR(20) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    dbDelta($voucher_table_sql);
}

/**
 * Generates a random 10-digit pin for vouchers.
 * 
 * @return string $pin The generated 10-digit random pin.
 */
function generate_random_pin() {
    // Function content remains unchanged...
    $pin = '';
    for ($i = 0; $i < 10; $i++) {
        $pin .= rand(0, 9);
    }
    return $pin;
}

/**
 * Adds a menu item for E-Pin Management in the WordPress admin menu.
 * Ensures the user has the 'manage_options' capability to access this menu item.
 */
function epin_management_menu() {
    add_menu_page('E-Pin Management', 'E-Pin Management', 'manage_options', 'epin-management', 'epin_management_page');
}
add_action('admin_menu', 'epin_management_menu');

/**
 * Renders the custom admin page for the E-Pin Management menu item.
 * This page includes a form for generating new pins.
 */
function epin_management_page() {
    // Check user capability and display the form...
    // The form includes a nonce field for security.
    
    ?>
    <div class="wrap">
        <h2>E-Pin Management</h2>
        <form method="post" action="">
            <label for="num_pins">Number of Pins to Generate:</label>
            <?php wp_nonce_field('generate_pins_action', 'generate_pins_nonce'); ?>
            <input type="text" id="num_pins" name="num_pins"><br>
            <br>
            <!-- <label for="denomination">Denomination:</label>
            <input type="text" id="denomination" disabled value="200" name="denomination"><br>
            <br> -->
            <input type="submit" name="generate_pins" value="Generate Pins">
        </form>
    </div>
    <?php


    /**
     * Processes the form submission for generating pins.
     * Validates nonce and user capability before generating pins.
     * Sanitizes form inputs and inserts new pins into the database.
     * Also generates an Excel file with the new pins and possibly send email...
     */
    if (isset($_POST['generate_pins'])) {
            // Check the nonce value for security
            if (!isset($_POST['generate_pins_nonce']) || !wp_verify_nonce($_POST['generate_pins_nonce'], 'generate_pins_action')) {
                wp_die(__('Sorry, your nonce did not verify.', 'text-domain'));
            }
        
            // Check user capability
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }
        
            // Sanitize and validate form input
            $num_pins = isset($_POST['num_pins']) ? intval(sanitize_text_field($_POST['num_pins'])) : 0;
            global $wpdb;
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;
            $username = $current_user->user_login;
            $batch_table_name = $wpdb->prefix . 'epin_batches';
            $voucher_table_name = $wpdb->prefix . 'epin_vouchers';
            $denomination = floatval(get_option('pgs_voucher_denomination'));
            $date_created = current_time('mysql');
            // Insert a record in the batch table
            $batch_data = array(
                'created_by' => $user_id,
                'number_of_pins' => $num_pins,
                'denomination' => $denomination,
                'status' => 'active',
                'date_created' => $date_created,
            );
            $wpdb->insert($batch_table_name, $batch_data);
            //Add Batch ID
            $batch_id = 'PGS-' . $wpdb->insert_id; // Concatenate "PGS" with the auto-incremented ID

            // Update the batch with the generated batch ID
            $add_batch_id = $wpdb->update($batch_table_name, array('batch_id' => $batch_id), array('id' => $wpdb->insert_id));
            // Generate e-pins and insert them into the voucher table
            $pins = array();
            for ($i = 0; $i < $num_pins; $i++) {
                $pin = generate_random_pin(); // Implement this function to generate a random 10-digit pin
                $pins[] = $pin;
                $voucher_data = array(
                    'voucher_pin' => $pin,
                    'batch_id' => $batch_id,
                    'status' => 'active',
                );
                $wpdb->insert($voucher_table_name, $voucher_data);
            }

            // post  $batch_id to pubsub topic voucherPins, 
            $pubSub = new PubSubClient([
                'projectId' => 'buybyraffle', // Replace with your Google Cloud project ID
            ]);

            // Reference an existing topic
            $topic = $pubSub->topic('sendvouchersbymail');

            // Detect the environment using wp_get_environment_type()
            $environment = wp_get_environment_type();

            // Prepare the attributes for the message
            $attributes = [
                'batch_id' => $batch_id,
                'environment' => $environment // This will be 'local', 'staging', 'development', 'production', or any custom value set in WP_ENVIRONMENT_TYPE
            ];

            // Prepare the data to be published
            // Assuming you want to encode the batch_id as the data
            $encodedData = base64_encode($batch_id);

            // Prepare the message payload
            $messagePayload = [
                'messages' => [
                    [
                        'attributes' => $attributes,
                        'data' => $encodedData,
                    ],
                ],
            ];

            // Publish the message
            try {
                // Attempt to publish the message
                $topic->publish($messagePayload);
            
                // If successful, send a success email to the admin
                $admin_email = get_option('admin_email');
                $subject = 'Pub/Sub Publish Successful';
                $message = 'Au user successfully generated voucher pins. They shall received their file shortly.';
                wp_mail($admin_email, $subject, $message);
            } catch (Exception $e) {
                // If there is an error, send a failure email to the admin
                $admin_email = get_option('admin_email');
                $subject = 'Pub/Sub Publish Failed';
                $message = 'There was an error publishing the voucher file to the queue: ' . $e->getMessage();
                wp_mail($admin_email, $subject, $message);
            }
    }
} 
// Use a library like PHPExcel to create Excel files
include PGS_VOUCHERS . 'batches-table.php';
include PGS_VOUCHERS . 'apis/get-voucher.php';
include PGS_VOUCHERS . 'apis/redeem-voucher.php'; 