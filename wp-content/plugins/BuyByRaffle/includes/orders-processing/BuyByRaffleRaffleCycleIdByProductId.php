<?php
function BuyByRaffleRaffleCycleIdByProductId($product_id) {
    global $wpdb;

    // Table name with WordPress prefix
    $table_name =  $wpdb->prefix.'buybyraffle_product_config';

    // SQL query to retrieve raffle_cycle_id for a given product_id with status "open"
    $sql = $wpdb->prepare("SELECT raffle_cycle_id FROM $table_name WHERE product_id = %d AND status = %s", $product_id, '0');

    // Get the result
    $result = $wpdb->get_var($sql);

    // Return the raffle_cycle_id if a record is found, otherwise return null
    return $result !== null ? $result : null;
}