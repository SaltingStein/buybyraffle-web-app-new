<?php
/**
 * Plugin Name: Nominate Business User
 * Description: A plugin to nominate a user to the Business User role.
 * Version: 1.0
 * Author: SGS Team (Mzer Michael Terungwa)
 */

// Check if WooCommerce is active
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    // Hook for adding admin menus
    add_action('admin_menu', 'nominate_business_user_menu');

    // Action for adding menu
    function nominate_business_user_menu() {
        add_menu_page('Nominate Business User', 'Nominate Business User', 'manage_options', 'nominate_business_user', 'nominate_business_user_page');
    }

    // Function to display the nomination form
    function nominate_business_user_page() {
        // Check if a Business User already exists
        $existing_business_users = get_users(['role' => 'business_user']);
        if (count($existing_business_users) > 0) {
            echo 'A Business User has already been nominated and cannot be replaced.';
            return;
        }

        // Check if form is submitted and handle the nomination logic
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = sanitize_email($_POST['email']);

            // Add or update the new Business User
            $user = get_user_by('email', $email);
            if (!$user) {
                $password = wp_generate_password();
                $user_id = wp_create_user($email, $password, $email);
                $user = get_user_by('ID', $user_id);
            }
            $user->add_role('business_user');

            // Create ledger wallets as user meta
            update_user_meta($user->ID, 'ledger_wallet_hero_product', 0);
            update_user_meta($user->ID, 'ledger_wallet_consolation_prices', 0);
            // ... (other ledger wallets)
        }

        echo '<form method="post">';
        echo '<label for="email">Email: </label>';
        echo '<input type="email" name="email" required>';
        echo '<input type="submit" value="Nominate">';
        echo '</form>';
    }

    // Create the Business User role on plugin activation
    register_activation_hook(__FILE__, 'create_business_user_role');

    function create_business_user_role() {
        $shop_manager_role = get_role('shop_manager');
        if ($shop_manager_role) {
            add_role('business_user', 'Business User', $shop_manager_role->capabilities);
        }
    }
} else {
    // Deactivate the plugin if WooCommerce is not active
    deactivate_plugins(plugin_basename(__FILE__));
    wp_die('This plugin requires WooCommerce to be installed and active.');
}
