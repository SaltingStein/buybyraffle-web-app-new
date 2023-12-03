<?php 
/**
 * Pay-By-Raffle Transaction Handling for WordPress
 *
 * This script provides functionality for handling Pay-By-Raffle transactions
 * in a WordPress environment.
 *
 * @package PayByRaffle
 */
// Check for the existence of the BuyByRaffle Environment Configurator plugin.
$pluginPath = ABSPATH . 'wp-content/plugins/BuyByRaffleEnvConfig/app.php';
$plugin = 'BuyByRaffleEnvConfig/app.php';
ABSPATH . 'wp-content/plugins/BuyByRaffle/includes/order-processing/BuyByRaffleStakeHoldersOrderMetaHandler.php';
$orderMetaHandler = new BuyByRaffleStakeHoldersOrderMetaHandler();


if (!file_exists($pluginPath) || !is_plugin_active($plugin)) {
    // Send email to admin
    wp_mail(get_bloginfo('admin_email'), 'BuyByRaffle Plugin Alert', 'The BuyByRaffle Environment Configurator plugin is either deactivated or missing.');

    // Add action to display an admin notice
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>The BuyByRaffle Environment Configurator plugin is either deactivated or missing.</p></div>';
    });
}


/**
 * Registers a custom REST API route for handling Pay-By-Raffle transactions.
 *
 * @since 4.11
 * @action rest_api_init Registers the custom route on WordPress REST API initialization.
 */
add_action('rest_api_init', function () {
    register_rest_route('api/v1', '/raffle-transactions', array(
        'methods' => 'POST',
        'callback' => 'handle_raffle_transaction',
        'permission_callback' => function () {
            return current_user_can('manage_options');
        }
    ));
});

/**
 * Handles the Pay-By-Raffle transaction posted by the POS terminal.
 *
 * Processes the transaction data and updates the Pay-By-Raffle system accordingly.
 * This includes prize fund updates, winner selection, and notifications.
 *
 * @since 4.11
 * @param WP_REST_Request $request The request object containing transaction data.
 * @return WP_REST_Response|WP_Error The response object with transaction processing status.
 */
function handle_raffle_transaction(WP_REST_Request $request) {
    // Extract parameters from the request
    $trans_ref = $request->get_param('trans_ref');
    $customer_msisdn = $request->get_param('customer_msisdn');
    $ticket_value = $request->get_param('ticket_value');
    $POS_id = $request->get_param('POS_id');
    $merchant_ID = $request->get_param('merchant_ID');

    // Extract Basic Auth credentials
    $headers = $request->get_headers();
    $auth = isset($headers['authorization'][0]) ? base64_decode(substr($headers['authorization'][0], 6)) : '';
    
    if (!$auth) {
        $response = new WP_REST_Response([
            'message' => 'Authentication credentials were not provided.'
        ]);
        $response->set_status(401);
        return $response;
    }
    
    list($username, $password) = explode(':', $auth);

    $ptsp_user = get_user_by('login', $username);
    if (!$ptsp_user || !wp_check_password($password, $ptsp_user->user_pass, $ptsp_user->ID)) {
        $response = new WP_REST_Response([
            'message' => 'Authentication failed.'
        ]);
        $response->set_status(401);
        return $response;
    }

   
    $merchant_ID = $request->get_param('merchant_ID');
    // Check if the merchant exists and is linked to the PTSP.
    if (!merchant_exists($merchant_ID)) {
        $response = new WP_REST_Response([
            'message' => 'Merchant does not exist.'
        ]);
        $response->set_status(404);
        return $response;
    }
     // Check if PTSP is linked to the merchant
    if (!is_linked_to_merchant($ptsp_user->ID, $merchant_ID)) {
        $response = new WP_REST_Response([
            'message' => 'This PTSP is not linked to the specified merchant.'
        ]);
        $response->set_status(403);
        return $response;
    }

    
    // If cusomer exists, or create new
    // @TODO: clean up the phone number to align with allowed formats
    $customer_ID = ensure_customer_account($request->get_param('customer_msisdn'));

    // Process raffle entry after verifying merchant and PTSP.
    $is_winner = process_raffle_entry($merchant_ID, $customer_ID, $request->get_param('ticket_value'), $request->get_param('POS_id'));
    
    // Prepare the message based on whether the customer won or not
    $message = $is_winner ? 'This is a winning transaction.' : 'Thank you for participating. Better luck next time.';
    
    // Return a success response with the message
    return new WP_REST_Response(array('message' => $message, 'is_winner' => $is_winner), 200);
}

function ensure_customer_account($customer_msisdn) {
    $user_id = username_exists($customer_msisdn);
    if (!$user_id) {
        // Customer doesn't exist, so create a new user with a generic display name
        $generic_display_name = 'User' . substr(md5($customer_msisdn), 0, 6); // Example: User4f3d2a
        $user_id = wp_create_user($customer_msisdn, wp_generate_password());

        // Update user display name to something generic
        wp_update_user([
            'ID' => $user_id,
            'display_name' => $generic_display_name,
        ]);

        // Add mobile number as user meta
        update_user_meta($user_id, 'mobile_number', $customer_msisdn);

        // Send welcome SMS - integrate with your SMS gateway here
        //@TODO Implement post to pubsub in this function
        send_welcome_sms($customer_msisdn);
    }

    // Return the user ID
    return $user_id;
}
/**
 * Processes a raffle entry for a given transaction.
 *
 * This function selects a random product for the merchant involved in the transaction,
 * updates the accumulated sales value for the product, and determines if the customer
 * has won the raffle. Depending on the outcome, it enqueues order processing tasks.
 *
 * @param int    $merchant_ID The ID of the merchant.
 * @param int    $customer_ID The ID of the customer.
 * @param float  $ticket_value The value of the ticket purchased by the customer.
 * @param string $POS_id       The identifier of the POS terminal.
 * @return bool| null  Returns true if the customer is the winner, false otherwise.
 */

 function process_raffle_entry($merchant_ID, $customer_ID, $ticket_value, $POS_id) {
    
    // Select a random product for the merchant
    $product_id = select_random_product($merchant_ID);
    if (is_wp_error($product_id)) {
        // Handle the error, e.g., log it or return a response
        error_log($product_id->get_error_message());
        return;
    }

    // Update the accumulated sales value for the product
    $is_winner = update_accumulated_sales($customer_ID, $product_id, $ticket_value);
    // Check if the customer is the winner
    // Enqueue order processing regardless of winning status
    enqueue_order_processing($merchant_ID, $customer_ID, $product_id, $ticket_value, $is_winner);

    return $is_winner;
}

/**
 * Updates the accumulated sales value for a specific product in a raffle cycle.
 *
 * This function handles updating the accumulated sales value for a product based on ticket sales.
 * It uses a specified percentage from the 'pgs_commission_model' option to calculate the
 * contribution of each ticket to the accumulated sales. When the accumulated sales value reaches
 * or exceeds the product's price (threshold), it triggers the winner selection process and
 * resets the accumulated sales value for a new raffle cycle.
 *
 * @since 4.11
 * @global wpdb $wpdb WordPress database abstraction object.
 * @param int   $product_id   The ID of the product for which the accumulated sales are being updated.
 * @param float $ticket_value The value of the ticket being added to the accumulated sales.
 * 
 * @return bool True if the update is successful, false if an error occurs.
 * 
 * @throws Exception If no raffle cycle is found for the given product ID or if any database error occurs.
 */
function update_accumulated_sales($customer_ID, $product_id, $ticket_value) {
    // Global WordPress database object
    global $wpdb;
    $table_name = $wpdb->prefix . 'buybyraffle_product_config';

    // Retrieve commission model percentages
    $percentages = get_option('pgs_commission_model', []);
    $product_percentage = isset($percentages['bait_product']) ? floatval($percentages['bait_product']) : 0;

    // Calculate the ticket contribution to the accumulated sales
    $contribution = ($ticket_value * $product_percentage) / 100;

    // Start a database transaction
    $wpdb->query('START TRANSACTION');

    try {
        // Retrieve the latest raffle cycle entry for the specified product
         // Retrieve the latest raffle cycle entry for the specified product
         $latest_cycle = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE product_id = %d AND status IN ('0', '2') ORDER BY id DESC LIMIT 1",
                $product_id
            )
        );

        // Throw an exception if no cycle entry is found
        if (!$latest_cycle) {
            throw new Exception("No raffle cycle found for product ID: {$product_id}");
        }

        // Add the ticket contribution to the existing accumulated sales value
        $new_accumulated_value = $latest_cycle->accumulated_sales_value + $contribution;
        $threshold_value = get_post_meta($product_id, '_price', true); // Retrieve product's price

        // Check if accumulated sales value meets/exceeds the threshold
        if ($new_accumulated_value >= $threshold_value) {
            // Update the raffle cycle status to 'closed'
            $wpdb->update(
                $table_name,
                array('status' => '3'),
                array('id' => $latest_cycle->id)
            );

            // Resetting accumulated sales for a new cycle
            $new_accumulated_value = 0;

            // Create a new raffle cycle entry for the product
            //@TODO: call the draw engine to create a new raffle cycle entry
            // Create a new raffle cycle entry for the same product
              $wpdb->insert(
                $table_name,
                array(
                    'product_id' => $product_id,
                    'raffle_class_id' => $latest_cycle->raffle_class_id,
                    'raffle_type_id' => $latest_cycle->raffle_type_id,
                    'accumulated_sales_value' => $new_accumulated_value,
                    'status' => 'open', // Assuming 'open' is a valid status
                    // Add other fields as necessary
                )
            );
            // Commit the transaction on success
            $wpdb->query('COMMIT');
            return true;
        }
        // at this point there was no winner for this transaction and raffle cycle
        // Update the accumulated sales value in the database
        $wpdb->update(
            $table_name,
            array('accumulated_sales_value' => $new_accumulated_value),
            array('id' => $latest_cycle->id)
        );

        // Commit the transaction on success
        $wpdb->query('COMMIT');
       
        return false;
    } catch (Exception $e) {
        // Rollback the transaction on error
        $wpdb->query('ROLLBACK');
        error_log('Error in update_accumulated_sales: ' . $e->getMessage());
        return false;
    }
}
/**
 * Selects a random product associated with a given merchant.
 *
 * @param int $merchant_ID The merchant's user ID.
 * @return int|WP_Error|WP_REST_Response The ID of the selected product or WP_Error if no products are found.
 */
function select_random_product($merchant_ID) {
    $args = array(
        'post_type'      => 'product', // Replace with your custom post type if not using WooCommerce
        'posts_per_page' => -1,        // Retrieve all products
        'post_status'    => 'publish', // Ensure only published products are retrieved
        'meta_query'     => array(
            array(
                'key'   => 'associated_merchant_id', // Replace with the actual meta key or taxonomy if different
                'value' => $merchant_ID,
            ),
        ),
        'fields'         => 'ids',     // Retrieve only the IDs for performance
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        // Select a random product ID from the results
        $random_key = array_rand($query->posts, 1);
        return $query->posts[$random_key];
    } else {
        // Return an error if no products are found
        return new WP_Error('no_products', 'No products found for this merchant.');
    }
}
/**
 * Enqueues order processing tasks for Pub/Sub consumer execution.
 *
 * This function stores the necessary data for order processing in a transient,
 * and then publishes a message to a Pub/Sub topic. The Pub/Sub consumer will use
 * this data to create a new order and add required metadata.
 *
 * @param int    $merchant_ID The ID of the merchant.
 * @param int    $customer_ID The ID of the customer.
 * @param int    $product_id  The ID of the product involved in the transaction.
 * @param float  $ticket_value The value of the ticket purchased by the customer.
 * @param bool   $is_winner   Indicates whether the customer is the winner of the raffle.
 */
function enqueue_order_processing($merchant_ID, $customer_ID, $product_id, $ticket_value, $is_winner) {
    // Create a unique transient key
    $transient_key = 'order_processing_' . md5(uniqid('', true));

    // Prepare the data to be stored in the transient
    $data = [
        'merchant_id' => $merchant_ID,
        'customer_id' => $customer_ID,
        'product_id' => $product_id,
        'ticket_value' => $ticket_value,
        'is_winner' => $is_winner
    ];

    define( 'HOUR_IN_SECONDS', 3 * 60 * 60 ); // 3 hours

    // Store data in a transient for a limited period (e.g., 3 hour)
    set_transient($transient_key, $data, HOUR_IN_SECONDS);

    // Publish message to Pub/Sub for processing
    try {
        $pubSub = new Google\Cloud\PubSub\PubSubClient(['projectId' => 'your-google-cloud-project-id']);
        $topic = $pubSub->topic('order_processing');

        // Encode data as JSON and publish
        $topic->publish(['data' => json_encode(['transient_key' => $transient_key])]);

    } catch (Exception $e) {
        error_log('Error publishing to Pub/Sub: ' . $e->getMessage());
    }
}

/**
 * Checks if the specified merchant exists and has the 'paybyraffle_merchants' role.
 *
 * @param int $merchant_ID The merchant's user ID.
 * @return bool True if the merchant exists and has the correct role, false otherwise.
 */
function merchant_exists($merchant_ID) {
    // Retrieve user data by WordPress user ID
    $user = get_userdata($merchant_ID);

    // Check if user exists and has the 'paybyraffle_merchants' role
    if ($user && in_array('paybyraffle_merchants', (array) $user->roles)) {
        return true; // Merchant exists
    }

    return false; // Merchant does not exist
}
/**
 * Checks if a PTSP user is linked to a specific merchant.
 *
 * @param int $ptsp_user_id The PTSP user's ID.
 * @param int $merchant_ID The merchant's ID to check the linkage.
 * @return bool True if linked, false otherwise.
 */
function is_linked_to_merchant($ptsp_user_id, $merchant_ID) {
    $linked_ptsp_id = get_user_meta($merchant_ID, 'linked_ptsp_id', true);

    return $linked_ptsp_id == $merchant_ID;
}



function send_welcome_sms($mobile_number) {
    // Your Google Cloud project ID and Pub/Sub topic name
    $projectId = 'your-google-cloud-project-id';
    $topicName = 'notification';

    // Message payload
    $messageData = [
        'phoneNumber' => $mobile_number,
        'message' => 'Welcome to the Pay-By-Raffle service! Your account has been created.'
    ];

    // Publish the message to the Pub/Sub topic
    try {
        $pubSub = new Google\Cloud\PubSub\PubSubClient(['projectId' => $projectId]);
        $topic = $pubSub->topic($topicName);

        // Encode message data as JSON
        $data = json_encode($messageData);
        $topic->publish(['data' => $data]);

        // You can add additional logging or actions here if needed
    } catch (Exception $e) {
        // Handle exceptions, like issues with Pub/Sub client
        error_log('Error publishing to Pub/Sub: ' . $e->getMessage());
    }
}

function send_notifications($type, $recipient_details, $message) {
    // Send out the notifications
}

function log_transaction($transaction_details) {
    // Log the transaction details
}




