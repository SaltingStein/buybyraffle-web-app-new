<?php
/**
 * Class BuyByRaffleBaitHeroAssociationHandler
 *
 * Handles bait-hero associations and updates raffle statuses.
 *
 * @author Terungwa
 */
class BuyByRaffleBaitHeroAssociationHandler {
    /**
     * Constructor
     *
     * Registers the methods to the appropriate WordPress hooks.
     */
    public function __construct() {
        add_action('save_post', array($this, 'associate_baits_with_hero'), 10, 3);
        add_action('before_delete_post', array($this, 'remove_bait_hero_association'));
        add_action('transition_post_status', array($this, 'handle_unpublish'), 10, 3);
    }

    /**
     * Update Bait-Hero Association
     *
     * This method is called whenever a post is saved. It checks if the post is of type 'product',
     * and updates the bait-hero association accordingly.
     *
     * @param int     $post_ID The ID of the post being saved.
     * @param WP_Post $post    The post object.
     * @param bool    $update  Whether this is an existing post being updated or not.
     */
    public function associate_baits_with_hero($post_ID, $post, $update) {
        try {
            if ('product' !== $post->post_type) {
                return;
            }
            if ('publish' !== $post->post_status) {
                
                throw new Exception("Product ID: $post_ID was autosaved by wordpress.");
                //return;
            }
            
            // Check if the product is a bait product
            if ($this->is_bait_product($post_ID)) {
                //throw new Exception("Product ID: $post_ID is not a bait product, and therefore cant be added to the wp_buybyraffle_bait_hero_association table.");
                 // Validate the input field when "bait" is selected
            
                $hero_product_id = $_POST['hero_product_id'] ?? '';
                if (empty($hero_product_id)) {
                    // Add a WordPress admin notice
                    add_action('admin_notices', function() {
                        echo '<div class="notice notice-error is-dismissible">';
                        echo '<p>Error: You must fill in the Hero Product ID field when selecting the "bait" attribute.</p>';
                        echo '</div>';
                    });
                    
                    // Throw an exception to halt the save process
                    throw new Exception('Hero Product ID must be filled when "bait" attribute is selected.');
                }
            
            }
            global $wpdb;

            // Assuming the associated hero product ID is stored in post meta with key 'associated_hero_id'
            $hero_id = get_post_meta($post_ID, 'hero_product_id', true);
            if (empty($hero_id)) {
                throw new Exception("Hero product ID is not set for this bait product: $post_ID");
            }
            // Check if the hero product status is 'open'
            //$hero_status = $wpdb->get_var("SELECT status FROM wp_buybyraffle_hero_products WHERE hero_id = $hero_id");
            $hero_status = $wpdb->get_var("SELECT status FROM wp_buybyraffle_hero_products WHERE hero_id = $hero_id AND status = 'open'");

            if ($hero_status === null) {
                // Log or handle the case where hero product is not found
                throw new Exception("No hero product was associated to this bait product or you attempted to use one that is not open for association.");
            } 

            // Check if an association already exists
            $existing_association = $wpdb->get_var("SELECT id FROM wp_buybyraffle_bait_hero_association WHERE bait_id = $post_ID AND hero_id = $hero_id");

            if (null === $existing_association) {
                // Insert new association
                $wpdb->insert(
                    'wp_buybyraffle_bait_hero_association',
                    array(
                        'bait_id' => $post_ID,
                        'hero_id' => $hero_id,
                        'updated_date' => current_time('mysql')
                    ),
                    array('%d', '%d', '%s')
                );
            } else {
                // Update existing association
                $wpdb->update(
                    'wp_buybyraffle_bait_hero_association',
                    array('updated_date' => current_time('mysql')),
                    array('id' => $existing_association),
                    array('%s'),
                    array('%d')
                );
            }
        } catch (Exception $e) {
            error_log("Caught exception: " . $e->getMessage());
        }
    }


    /**
     * Remove Bait-Hero Association
     *
     * Removes the bait-hero association when a bait or hero product is deleted.
     *
     * @param int $post_ID The ID of the post being deleted.
     */
    public function remove_bait_hero_association($post_ID) {
        global $wpdb;
        $post_type = get_post_type($post_ID);
        if ('product' === $post_type) {
            // Update the status of the bait-hero association for this bait product to 'inactive' or 'deleted'
            $wpdb->update(
                'wp_buybyraffle_bait_hero_association',
                array('status' => 'deleted'),  // or 'deleted', depending on your status taxonomy
                array('bait_id' => $post_ID),
                array('%s'),  // value type
                array('%d')   // where type
            );
        }
    }

   /**
     * Handle Unpublish Event
     *
     * Called when a product is unpublished, to update the status of any stale bait-hero associations to 'inactive'.
     *
     * @param string  $new_status New post status.
     * @param string  $old_status Old post status.
     * @param WP_Post $post       Post object.
     */
    public function handle_unpublish($new_status, $old_status, $post) {
        global $wpdb;
        if ($post->post_type !== 'product') return;

        if ($new_status !== 'publish' && $old_status === 'publish') {
            // Update the status of the bait-hero association for this bait product to 'inactive'
            $wpdb->update(
                'wp_buybyraffle_bait_hero_association',
                array('status' => 'unpublished'),  // or 'deleted', depending on your status taxonomy
                array('bait_id' => $post->ID),
                array('%s'),  // value type
                array('%d')   // where type
            );
        }
    }


    /**
     * Check if a Product is a Bait Product
     *
     * This internal method checks if a given product ID represents a bait product.
     *
     * @param int $product_id The ID of the product to check.
     * @return bool True if the product is a bait product, false otherwise.
     */
    private function is_bait_product($product_id) {
        try {
            $terms = get_the_terms($product_id, 'pa_buybyraffle-product-group');
            if (is_array($terms) && !is_wp_error($terms)) {
                foreach ($terms as $term) {
                    if ('bait' === $term->slug) {
                        return true;
                    }
                }
            }
            return false;
        } catch (Exception $e) {
            error_log("Caught exception in is_bait_product: " . $e->getMessage());
            throw $e;  // Re-throw the exception if needed
        }
    }
}

