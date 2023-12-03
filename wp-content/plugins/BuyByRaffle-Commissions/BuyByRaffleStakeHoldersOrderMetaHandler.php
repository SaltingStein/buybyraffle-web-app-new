<?php
class BuyByRaffleStakeHoldersOrderMetaHandler {
    
    public function splitOrderMeta($order_id) {
        // Get the order
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return; // Exit if the order is not valid
        }
        
        // Get the percentages from the option
        $percentages = get_option('pgs_commercial_model', array());
        
        // If the option is not set, use default values
        if (empty($percentages)) {
           return;
        }
        
        // Calculate and set order metas based on percentages
        foreach ($percentages as $meta_key => $percentage) {
            $amount = $order->get_total() * ($percentage / 100);
            update_post_meta($order_id, $meta_key, $amount);
        }
    }
}
