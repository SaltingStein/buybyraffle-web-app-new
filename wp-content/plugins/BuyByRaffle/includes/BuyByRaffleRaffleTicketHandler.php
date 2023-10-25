<?php

/**
 * Class BuyByRaffleRaffleTicketHandler
 *
 * This class is responsible for managing ticket entries in the `buybyraffle_tickets` table.
 * It hooks into WooCommerce's order completion action to add new raffle tickets for bait products purchase.
 *
 * @author Terungwa
 */
class BuyByRaffleRaffleTicketHandler {

    /**
     * Constructor
     *
     * Registers the methods to the appropriate WooCommerce hooks.
     */
    public function __construct() {
        add_action('woocommerce_order_status_completed', array($this, 'update_buybyraffle_tickets'), 10, 1);
    }

    public function update_buybyraffle_tickets($order_id) {
        global $wpdb;

        try {
            $order = wc_get_order($order_id);
            if (!$order) {
                throw new Exception("Order with ID {$order_id} not found.");
            }
    
            $user_id = $order->get_user_id(); // Get the customer user ID
            // Check if an entry with the same ticket_id and user_id already exists
            $existing_entry = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}buybyraffle_tickets WHERE ticket_id = %d AND user_id = %d",
                       $order_id,
                       $user_id
                )
            );
    
            if ($existing_entry) {
                error_log("Duplicate entry detected for ticket_id: {$order_id} and user_id: {$user_id}. Skipping insert.");
                return;
            }
            // Loop through all the products in the order
            foreach ($order->get_items() as $item_id => $item_data) {
                $product = $item_data->get_product();
                $product_id = $product->get_id(); // Get the product ID
                // Check if the product is a bait product
                if ($this->is_bait_product($product_id)) {
                    // Prepare data
                    $data = array(
                        'ticket_id' => $order_id,
                        'raffle_id' => $product_id,
                        'user_id' => $user_id,
                        'draw_type' => 'primary', // Set this according to your logic
                        'created_date' => current_time('mysql'),
                        'updated_date' => current_time('mysql')
                    );
    
                    // Insert into table
                    $insert_result = $wpdb->insert(
                        $wpdb->prefix . 'buybyraffle_tickets',
                        $data,
                        array('%d', '%d', '%d', '%s', '%s', '%s')
                    );
    
                    if (!$insert_result) {
                        throw new Exception("Failed to insert ticket for Order ID {$order_id} and Product ID {$product_id}.");
                    }
                }else{
                    
                    throw new Exception("Product ID {$product} is not a bait product.");
                }
            }
        } catch (Exception $e) {
            // Log the exception for debugging
            error_log("Caught exception in update_buybyraffle_tickets: " . $e->getMessage());
        }
    }
    


    /**
     * Check if a Product is a Bait Product
     *
     * This internal method checks if a given product ID represents a bait product.
     *
     * @author Terungwa
     * @param int $product_id The ID of the product to check.
     * @return bool True if the product is a bait product, false otherwise.
     */
    private function is_bait_product($product_id) {
        // Check if the product has the 'bait' tag
        if (has_term('bait', 'product_tag', $product_id)) {
            return true;
        }
        return false;
    }
    


}
