<?php
/*
Plugin Name: PGS Vouchers
Description: A plugin for generating and managing e-pins.
Version: 1.0
Author: Your Name
*/
if(!function_exists('wp_get_current_user')) {
    include(ABSPATH . "wp-includes/pluggable.php"); 
}

require_once plugin_dir_path(__FILE__) . 'PhpSpreadsheet/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;


if (!defined('PGS_VOUCHERS')) {
    define('PGS_VOUCHERS', plugin_dir_path(__FILE__));
}
// Activation hook to create custom tables
register_activation_hook(__FILE__, 'epin_plugin_activate');

function epin_plugin_activate() {
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
// Function to generate a random 10-digit pin
function generate_random_pin() {
    $pin = '';
    for ($i = 0; $i < 10; $i++) {
        $pin .= rand(0, 9);
    }
    return $pin;
}

// Add a menu item in the admin menu
function epin_management_menu() {
    add_menu_page('E-Pin Management', 'E-Pin Management', 'read_post', 'epin-management', 'epin_management_page');
}
add_action('admin_menu', 'epin_management_menu');

//Initialise user id
add_action('init', 'my_custom_function');

function my_custom_function() {
    $user_id = get_current_user_id();
    // Use the user ID as needed.
}

// Create the custom admin page
function epin_management_page() {
   
    ?>
    <div class="wrap">
        <h2>E-Pin Management</h2>
        <form method="post" action="">
            <label for="num_pins">Number of Pins to Generate:</label>
            <input type="text" id="num_pins" name="num_pins"><br>
            <br>
            <!-- <label for="denomination">Denomination:</label>
            <input type="text" id="denomination" disabled value="200" name="denomination"><br>
            <br> -->
            <input type="submit" name="generate_pins" value="Generate Pins">
        </form>
    </div>
    <?php
}

// Process the form submission
if (isset($_POST['generate_pins'])) {
    global $wpdb;
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $batch_table_name = $wpdb->prefix . 'epin_batches';
    $voucher_table_name = $wpdb->prefix . 'epin_vouchers';
    $num_pins = intval($_POST['num_pins']);
    $denomination = floatval(get_option('pgs_voucher_denomination'));
    
    // Insert a record in the batch table
    $batch_data = array(
        'created_by' => $user_id,
        'number_of_pins' => $num_pins,
        'denomination' => $denomination,
        'status' => 'active',
        'date_created' => current_time('mysql'),
    );
    $wpdb->insert($batch_table_name, $batch_data);
    //Add Batch ID
    $batch_id = 'PGS-' . $wpdb->insert_id; // Concatenate "PGS" with the auto-incremented ID

    // Update the batch with the generated batch ID
    $add_batch_id = $wpdb->update($batch_table_name, array('batch_id' => $batch_id), array('id' => $wpdb->insert_id));
    // if($add_batch_id){
    //     echo "<script> alert('Successfully generated. Check your mail for the pins file.');</script>";
    // }
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
   
    // Create an Excel file with the generated pins
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'E-Pins');
    $row = 2;   
    foreach ($pins as $pin) {
        $sheet->setCellValue('A' . $row, $pin);
        $row++;
    }

    $excelWriter = new Xls($spreadsheet);
    $file = wp_upload_dir()['path'] . '/generated_pins.xls';
    //$file = '\wp-content\uploads\2023\10\generated_pins.xls';
    $excelWriter->save($file);

    $current_user = get_current_user();
    $to = "dajooe@gmail.com";
    $subject = 'Generated E-Pins';
    $message = 'Attached is your generated E-Pins.';
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'From: Your name <info@address.com>' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $headers = 'Content-Type: text/html; charset=UTF-8';
    $attachments = array($file);
    //wp_mail($to, $subject, $message, $headers, $file);
}
 // Use a library like PHPExcel to create Excel files
 include PGS_VOUCHERS . 'batches-table.php';
 include PGS_VOUCHERS . 'apis/get-voucher.php';
 include PGS_VOUCHERS . 'apis/redeem-voucher.php';
 

