<?php
include ABSPATH . 'wp-content/plugins/BuyByRaffle/includes/orders-processing/BuyByRaffleGetHeroIdByBaitId.php';
include ABSPATH . 'wp-content/plugins/BuyByRaffle/includes/orders-processing/BuyByRaffleRaffleCycleIdByProductId.php';
//  include ABSPATH . 'wp-content/plugins/BuyByRaffle/includes/orders-processing/BuyByRaffleStakeHoldersOrderMetaHandler.php';
include ABSPATH . 'wp-content/plugins/BuyByRaffle/includes/orders-processing/BuyByRaffleSendTicketExternalAPIHandler.php';
include ABSPATH . 'wp-content/plugins/BuyByRaffle/includes/orders-processing/BuyByRaffleRunRaffle.php';
// mv BuyByRaffleGetHeroIdByBaitId.php BuyByRaffleGetHeroIdByBaitId.php

// Hook into the order completion event to run the splitOrderMeta function
add_action('woocommerce_order_status_completed', 'OrderCompletionHandler', 10, 1);

/**
 * OrderCompletionHandler class handles the completion of WooCommerce orders.
 * It processes orders with the 'bait' tag and manages the raffle cycle updates
 * and winner selection.
 */
class OrderCompletionHandler {
    /**
     * Handles the completion of an order.
     * It triggers various processes like splitting order meta, updating accumulated sales,
     * sending notifications, and running raffle logic.
     *
     * @param int $order_id The WooCommerce order ID.
     */
    public function handleOrderCompletion($order_id) {
        // Get the order
        $order = wc_get_order($order_id);

        // Ensure the order is valid
        if (!$order) {
            return;
        }

        // Get the product ID from the order
        $items = $order->get_items();
        if (empty($items)) {
            return; // No items in the order
        }

        // Assume only one product is purchased per order       
        $product = current($items)->get_product();
        $product_id = $product->get_id();
        
        // Check if the product has the 'bait' tag
        $product_tags = wc_get_product_terms($product_id, 'product_tag', array('fields' => 'names'));
        if (in_array('bait', $product_tags)) {
             // Check if BuybyRaffleCommission Model Settings plugin is active
            if (is_plugin_active('buybyraffle-commissions/buybyraffle-commissions.php')) {
                //Split money to stakeholders
                $orderMetaHandler = new BuyByRaffleStakeHoldersOrderMetaHandler();
                $orderMetaHandler->splitOrderMeta($order_id);
            }else{
                // Send email to admin
                $admin_email = get_option('admin_email');
                $subject = 'BuybyRaffleCommission Model Settings Plugin Not Active';
                $message = 'The BuybyRaffleCommission Model Settings plugin is not active on your site. Please activate it to process stakeholders splitting features.';
                wp_mail($admin_email, $subject, $message);
                return 'error processing commission percentages. BBR Commissions plugin must be active';
            }

           

            // Get the raffle configuration from the database
            global $wpdb;
            $table_name_config = $wpdb->prefix.'buybyraffle_product_config';
            $table_name_association = $wpdb->prefix.'buybyraffle_bait_hero_association';
            // Get the hero_id from bait hero association
            $hero_id =  BuyByRaffleGetHeroIdByBaitId($product_id);
            if(empty($hero_id)){
                return "No active hero is available for this bait product";
            }
            else{               
                // Get raffle configuration
                $raffle_config = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT * FROM $table_name_config WHERE product_id = %d AND status = 'open'",
                        $product_id
                    )
                );
                if ($raffle_config) {
                    //Get associated data the order to a raffle cycle id
                    $raffle_cycle_id =  $raffle_config->raffle_cycle_id;
                    if(function_exists('BuyByRaffleRaffleCycleIdByProductId') || function_exists('BuyByRaffleRaffleCycleIdByProductId')){
                        $hero_raffle_cycle_id = BuyByRaffleRaffleCycleIdByProductId($hero_id);
                        $bait_raffle_cycle_id = BuyByRaffleRaffleCycleIdByProductId($product_id);
                    }else{
                        return;
                    }
                    // Get the percentage from the option
                    $percentages = get_option('pgs_commercial_model', array());
                    // Get the percentage for bait product
                    $bait_percentage = isset($percentages['bait_product']) ? $percentages['bait_product'] : 0;
                    // Get the percentage for hero product
                    $hero_percentage = isset($percentages['prize_hero_product']) ? $percentages['prize_hero_product'] : 0;
                    
                    // Update accumulated sales value for hero product
                    $wpdb->update(
                        $table_name_config,
                        array(
                            'accumulated_sales_value' => $raffle_config->accumulated_sales_value + ($order->get_total() * $hero_percentage / 100),
                            'updated_at' => current_time('mysql', 1),
                        ),
                        array('product_id' => $hero_id, 'status' => 'open')
                    );

                    // Update accumulated sales value for bait product
                    $new_accumulated_sales_value = $raffle_config->accumulated_sales_value + ($order->get_total() * $bait_percentage / 100);
                  
                    $wpdb->update(
                        $table_name_config,
                        array(      
                            'accumulated_sales_value' => $new_accumulated_sales_value,
                            'updated_at' => current_time('mysql', 1),
                        ),
                        array('product_id' => $product_id, 'status' => 'open')
                    );

                    //Complete hero raffle if set to complete
                    $hero_product = wc_get_product($hero_id);
                    if ($hero_product) {
                        // Get the price
                        $hero_price = $hero_product->get_price();
                    }
                    if ($raffle_config->accumulated_sales_value >= $hero_price){
                       if(function_exists('BuyByRaffleRunRaffle')){
                            BuyByRaffleRunRaffle($hero_raffle_cycle_id);
                       }
                    }
                    //Run bait raffle if inventory is over
                    $product = wc_get_product($product_id);
                    if ($product) {
                        // Get the stock quantity
                        $stock_quantity = $product->get_stock_quantity();
                    }
                    if($stock_quantity === 0){
                        if(function_exists('BuyByRaffleRunRaffle')){
                            BuyByRaffleRunRaffle($bait_raffle_cycle_id);
                       }
                    }

                    //Generate and save ticket id
                    if (function_exists(createUniqueRaffleTicket())){
                        $ticket_id = createUniqueRaffleTicket(12);
                        update_post_meta($order_id, 'bbr_ticket_id', $ticket_id);
                    } 

                    update_post_meta($order_id, 'hero_raffle_cycle_id', $hero_raffle_cycle_id);
                    update_post_meta($order_id, 'bait_raffle_cycle_id', $bait_raffle_cycle_id);
                    //update_post_meta($order_id, 'draw_type', $raffle_cycle_id);

                    //Gift customer cashtoken
                    // External URL to send the order ID to
                    $external_url = site_url().'wp-json/cashtoken/v2/gifting';

                    // Prepare data to send
                    $data = array(
                        'order_id' => $order_id,
                        // Add any additional data you want to send
                    );

                    // Use wp_remote_post to send data to the external URL
                    $response = wp_remote_post($external_url, array(
                        'body' => json_encode($data),
                        'headers' => array('Content-Type' => 'application/json'),
                    ));

                    // You can check the response if needed
                    if (is_wp_error($response)) {
                        error_log('Error sending order ID: ' . $response->get_error_message());
                    }  

                }
            }    
              
        }
    }

    
}

// Hook into the order completion event to run the handleOrderCompletion function
add_action('woocommerce_order_status_completed', 'bbr_handle_order_completion', 11, 1);
/**
 * Handles the completion of an order for BuyByRaffle.
 *
 * @param int $order_id The WooCommerce order ID.
 */
function bbr_handle_order_completion($order_id) {
    $orderCompletionHandler = new OrderCompletionHandler();
    $orderCompletionHandler->handleOrderCompletion($order_id);
   
}



// Hook into the order completion event to run the handleOrderCompletion function
add_action('woocommerce_order_status_completed', 'bbr_create_remote_ticket', 12, 1);
//TODO : Move all keys used by BuyByRaffleSendTicketExternalAPIHandler to a secure location
/**
 * Creates a remote ticket in an external system for a completed order.
 *
 * @param int $order_id The WooCommerce order ID.
 */
function bbr_create_remote_ticket($order_id) {
    $order = wc_get_order($order_id);

    // Check if the order object is valid
    if (!$order) {
        return;
    }
    $customer_id = $order->get_customer_id();
    $raffle_cycle_id = get_post_meta($order_id, 'raffle_cycle_id', true);
    $raffle_class_id = get_post_meta($order_id, 'raffle_class_id', true);
    //TODO: Move this too
    $externalApiHandler = new BuyByRaffleSendTicketExternalAPIHandler(getenv('RAFFLE_TICKETS_REMOTE'), getenv('RAFFLE_AUTH_USERNAME'), getenv('RAFFLE_AUTH_PASSWORD'));
    $externalApiHandler->sendData($order_id, $raffle_cycle_id, $customer_id, $raffle_class_id);
}