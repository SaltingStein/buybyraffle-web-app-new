<?php
/*
Plugin Name: WooCommerce Hero Installment Progress Meter
Description: Displays a progress bar for installment payments in WooCommerce.
Version: 1.0
Author: SGS Team
License: GPL2
*/
function display_hero_progress_meter($atts) {
    global $wpdb;

    // Extract attributes
    $attributes = shortcode_atts(array(
        'pid' => 0,
    ), $atts);

    // Get product details by ID
    $product_id = $attributes['pid'];
    $product = wc_get_product($product_id);

    if (!$product) {
        return "No Hero Product has been Configured.";
    }

    $product_price = $product->get_price();

    // Query to get accumulated sales value from your custom table
    $table_name = $wpdb->prefix . 'buybyraffle_product_config';
    $accumulated_sales_value = $wpdb->get_var("SELECT accumulated_sales_value FROM $table_name WHERE product_id = $product_id");

    // If accumulated_sales_value is null, set it to zero
    if (is_null($accumulated_sales_value)) {
        $accumulated_sales_value = 0;
    }

    // Calculate percentage
    $percentage = 0;
    if ($product_price > 0) {
        $percentage = ($accumulated_sales_value / $product_price) * 100;
    }
    $percentage = round($percentage, 2);

    // Generate progress bar HTML
    $progress_bar_html = "
        <div id='pmeter' style='width: 100%; background-color: #ddd;'>
            <div id='pbar' style='width: {$percentage}%; text-align: center; padding: 2px;'>
                {$percentage}%
            </div>
        </div>
    ";

    return $progress_bar_html;
}

add_shortcode('hero_progress_meter', 'display_hero_progress_meter');

function woocommerce_hero_progress_meter_activate() {
    // Code to execute on activation
}

function woocommerce_hero_progress_meter_deactivate() {
    // Code to execute on deactivation
}

register_activation_hook(__FILE__, 'woocommerce_hero_progress_meter_activate');
register_deactivation_hook(__FILE__, 'woocommerce_hero_progress_meter_deactivate');
