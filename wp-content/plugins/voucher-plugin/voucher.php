<?php
/**
 * Plugin Name: BuyByRaffle Vouchers
 * Description: A WordPress plugin for generating and managing e-pins for vouchers.
 * Version: 4.11
 * Author: SGS Team <Joseph>
 */

// Load PhpSpreadsheet library for Excel file creation.
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

use Google\Cloud\PubSub\PubSubClient;
use Google\Client;

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
    global $wpdb;
    update_option('pgs_voucher_denomination', '200');
    $batch_table_name = $wpdb->prefix . 'buybyraffle_epin_batches';
    $voucher_table_name = $wpdb->prefix . 'buybyraffle_epin_vouchers';
    $charset_collate = $wpdb->get_charset_collate();

    // Create the batch table with comments on columns
    $batch_table_sql = "CREATE TABLE IF NOT EXISTS $batch_table_name (
        id INT NOT NULL AUTO_INCREMENT,
        batch_id VARCHAR(15) NOT NULL COMMENT 'Unique identifier for each batch',
        created_by INT NOT NULL COMMENT 'ID of the user who created the batch',
        denomination DECIMAL(10, 2) NOT NULL COMMENT 'Denomination value of the e-pins',
        number_of_pins INT NOT NULL COMMENT 'Number of e-pins to generate in the batch',
        generation_status TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Generation status: 0-Pending, 1-Processing, 2-Completed',
        active_status TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Active status: 0-Pending, 1-Processing, 2-Completed',
        date_created DATETIME NOT NULL COMMENT 'Date and time when the batch was created',
        PRIMARY KEY (id),
        INDEX (batch_id)
    ) $charset_collate ENGINE=InnoDB;";

    // Create the voucher table with comments on columns
    $voucher_table_sql = "CREATE TABLE IF NOT EXISTS $voucher_table_name (
        id INT NOT NULL AUTO_INCREMENT,
        voucher_pin VARCHAR(15) NOT NULL COMMENT 'Unique pin for each voucher',
        balance SMALLINT(4) NOT NULL DEFAULT 0 COMMENT 'Remaining balance on the voucher',
        batch_id VARCHAR(10) NOT NULL COMMENT 'Batch ID to which the voucher belongs',
        active_status TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Active status: 0-Active, 1-Used, 2-deactivated',
        PRIMARY KEY (id),
        INDEX (voucher_pin)
    ) $charset_collate ENGINE=InnoDB;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($batch_table_sql);
    dbDelta($voucher_table_sql);
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
    // Initialize an array for error messages
    $error_messages = array();
        // Allowed denominations
        $allowed_denominations = array(200, 400, 600, 800, 1000);
        // Check if redirected after form processing 
        if (isset($_GET['epin_published'])) {
            $pubsub_result = get_option('_epin_pubsub_result');
            if ($pubsub_result) {
                if ($pubsub_result === 'success') {
                    echo '<div class="notice notice-success"><p>Vouchers generation request was successfully submitted. Please check your email for the CSV file in 10 seconds to 5 minutes.</p></div>';
                } elseif ($pubsub_result === 'error') {
                    echo '<div class="notice notice-error"><p>Failed to submit voucher generation request. Please try to generate the voucher again.</p></div>';
                }
            }
        }
    
    ?>
    <div class="wrap">
        <h2>E-Pin Management</h2>
        <form method="post" action="">
            <label for="num_pins">Number of Pins to Generate:</label>
            <?php wp_nonce_field('generate_pins_action', 'generate_pins_nonce'); ?>
            <input type="text" id="num_pins" name="num_pins"><br>

			<label for="pin_denomination">Select Denomination:</label>
            <select id="pin_denomination" name="pin_denomination">
                <?php foreach ($allowed_denominations as $denomination): ?>
                    <option value="<?php echo esc_attr($denomination); ?>">
                        <?php echo esc_html($denomination); ?>
                    </option>
                <?php endforeach; ?>
            </select>
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
            $error_messages[] = 'Sorry, your nonce did not verify.';
        }
        
        // Check user capability
        if (!current_user_can('manage_options')) {
            $error_messages[] = 'You do not have sufficient permissions to access this page.';
        }
        
        // Sanitize and validate form input
        $num_pins = isset($_POST['num_pins']) ? intval(sanitize_text_field($_POST['num_pins'])) : 0;
         // Check if num_pins is a positive integer and within the set limit
        $max_pins_limit = 100000; // Define the maximum limit
       
        // Add errors to the error_messages array instead of using wp_die()
        if ($num_pins <= 0 || $num_pins > $max_pins_limit) {
            $error_messages[] = 'The number of pins must be a positive integer and cannot exceed ' . $max_pins_limit . ' at a time.';
        }
        // Validate submitted denomination
        $pin_denomination = isset($_POST['pin_denomination']) ? intval($_POST['pin_denomination']) : 0;
        if (!in_array($pin_denomination, $allowed_denominations)) {
            $error_messages[] = 'Invalid denomination value.';
        }
        
        global $wpdb;
        // If there are no errors, process the form
        if (empty($error_messages)) {
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;
            $user_email = $current_user->user_email;
            $username = $current_user->user_login;
            $batch_table_name = $wpdb->prefix . 'buybyraffle_epin_batches';
            //$voucher_table_name = $wpdb->prefix . 'epin_vouchers';
            //$denomination = floatval(get_option('pgs_voucher_denomination'));
            $date_created = current_time('mysql');
            // Insert a record in the batch table
            $batch_data = array(
                'created_by' => $user_id,
                'number_of_pins' => $num_pins,
                'denomination' => $pin_denomination,
                'generation_status' => 0,
                'active_status' => 0,
                'date_created' => $date_created,
            );
            $wpdb->insert($batch_table_name, $batch_data);
            $lastInsertID = $wpdb->insert_id;
            //Add Batch ID
            $batch_id = 'PGS-'.$lastInsertID; // Concatenate "PGS" with the auto-incremented ID
            // Update the batch with the generated batch ID
            $add_batch_id = $wpdb->update($batch_table_name, array('batch_id' => $batch_id), array('id' => $lastInsertID));
            
            // call the internal function to post to pubsub.
            pubsubPublisherVoucherGen($lastInsertID, $user_email);
            // After processing, redirect back to the same page
            wp_redirect(add_query_arg('epin_published', '1', menu_page_url('epin-management', false)));
            exit;
        }
    }
    // Display error messages if any
    if (!empty($error_messages)) {
        echo '<div class="notice notice-error">';
        foreach ($error_messages as $message) {
            echo '<p>' . esc_html($message) . '</p>';
        }
        echo '</div>';
    }
}

function getBearerToken($configFilePath) {
    try {
        $client = new Client();
        $client->setAuthConfig($configFilePath);
        $client->setScopes(['https://www.googleapis.com/auth/cloud-platform']);

        // Fetch the access token
        $accessToken = $client->fetchAccessTokenWithAssertion();

        // Return the access token
        if (isset($accessToken['access_token'])) {
            return $accessToken['access_token'];
        } else {
            throw new Exception('Failed to fetch access token');
        }
    } catch (Exception $e) {
        // Handle exceptions, such as file not found or invalid credentials
        error_log('Exception in getBearerToken: ' . $e->getMessage());
        throw $e; // Rethrow the exception for the caller to handle
    }
}

include PGS_VOUCHERS . 'libraries/pubsubPublishEmailVoucherCsv.php';
include PGS_VOUCHERS . 'libraries/pubsubPublisherVoucherGen.php'; 
include PGS_VOUCHERS . 'libraries/pubsubConsumerVoucherGen.php'; 

include PGS_VOUCHERS . 'batches-table.php';
include PGS_VOUCHERS . 'apis/get-voucher.php';
include PGS_VOUCHERS . 'apis/redeem-voucher.php'; 
include PGS_VOUCHERS . 'apis/sendvouchersbymail.php'; 