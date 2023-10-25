<?php
/**
 * Class BuyByRaffleHeroProductHandler
 * Handles 'Hero' products in the "BuyByRaffle Product Group" attribute.
 * @author Terungwa
 */
class BuyByRaffleHeroProductHandler {
    /**
     * Constructor
     * Adds WordPress actions and filters.
     * @author Terungwa
     */
    public function __construct() {
        // Remove Hero products from archives
        add_action('pre_get_posts', array($this, 'remove_from_archives_and_search'));

        // Make Hero products non-purchasable
        add_filter('woocommerce_is_purchasable', array($this, 'make_non_purchasable'), 10, 2);

        // Registers the add_hero_product method to the save_post action hook.
        add_action('save_post_product', array($this, 'add_hero_product'), 10, 3);

        // Prevent deletion of Hero products with certain statuses
        add_action('before_delete_post', array($this, 'prevent_hero_deletion'));
    }

    /**
     * Prevent Deletion of Hero Products
     * 
     * @param int $post_id The ID of the post being deleted.
     * @author Terungwa
     */
    public function prevent_hero_deletion($post_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'buybyraffle_hero_products';
        $existing_product = $wpdb->get_var("SELECT status FROM {$table_name} WHERE product_id = $post_id");

        // Prevent deletion if the status is 'running' or 'redeemed'
        if ($existing_product && in_array($existing_product, ['running', 'redeemed'])) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible">';
                echo "<p>You cannot delete this Hero product because its status is either 'running' or 'redeemed'.</p>";
                echo '</div>';
            });
            return; // Exit the function early
        }
    }

    /**
     * Add Hero Product
     *
     * Adds a new entry to the buybyraffle_hero_products table each time a Hero product is created.
     *
     * @param int     $post_ID The ID of the post being saved.
     * @param WP_Post $post    The post object.
     * @param bool    $update  Whether this is an existing post being updated or not.
     * @author Terungwa
     */
    public function add_hero_product($post_ID, $post, $update) {
        try {
            global $wpdb;
            $table_name = $wpdb->prefix . 'buybyraffle_hero_products';
            $terms = get_the_terms($post_ID, 'pa_buybyraffle-product-group');

            if (is_wp_error($terms)) {
                throw new Exception("Error retrieving the 'Hero' term: " . $terms->get_error_message());
            }

            $existing_product = $wpdb->get_var("SELECT status FROM {$table_name} WHERE product_id = $post_ID");

            $is_hero = false;
            if (is_array($terms)) {
                foreach ($terms as $term) {
                    if ($term->slug === 'hero') {
                        $is_hero = true;
                        break;
                    }
                }
            }

            // Prevent Hero tag change if the status is 'running' or 'redeemed'
            if ($existing_product && in_array($existing_product, ['running']) && !$is_hero) {
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-error is-dismissible">';
                    echo '<p>You cannot remove the Hero tag from this product because its status is either "running" or "redeemed".</p>';
                    echo '</div>';
                });
                return; // Exit the function early
            }

            // Get the highest raffle_cycle_id and increment it by 1
            $highest_cycle_id = $wpdb->get_var("SELECT MAX(raffle_cycle_id) FROM {$table_name}");
            $new_cycle_id = is_null($highest_cycle_id) ? 1 : $highest_cycle_id + 1;

            // Insert or update the Hero product in the table
            if ($is_hero) {
                $data = array(
                    'product_id' => $post_ID,
                    'hero_id' => $post_ID,
                    'raffle_cycle_id' => $new_cycle_id,
                    'status' => 'open'
                );
                $format = array('%d', '%d', '%d', '%s');

                if (null === $existing_product) {
                    $wpdb->insert($table_name, $data, $format);
                } else {
                    $wpdb->update($table_name, $data, array('product_id' => $post_ID), $format, array('%d'));
                }
            }

        } catch (Exception $e) {
            error_log("Caught exception in add_hero_product: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
        }
    }


    /**
     * Remove Hero products from archives and search.
     * @param WP_Query $query WordPress Query object.
     */
    public function remove_from_archives_and_search($query) {
        if ($query->is_main_query() && !is_admin()) {
            $tax_query = array(
                array(
                    'taxonomy' => 'product_tag',
                    'field'    => 'slug',
                    'terms'    => 'hero',
                    'operator' => 'NOT IN',
                ),
            );
            $query->set('tax_query', $tax_query);
        }
    }

    /**
     * Make Hero products non-purchasable.
     * @param bool       $purchasable Whether the product is purchasable.
     * @param WC_Product $product     WooCommerce Product object.
     * @return bool
     */
    public function make_non_purchasable($purchasable, $product) {
        $terms = get_the_terms($product->get_id(), 'product_tag');
        if ($terms && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                if ($term->slug === 'hero') {
                    return false;
                }
            }
        }
        return $purchasable;
    }
}
