<?php
/**
 * Plugin Name: BuyByRaffle
 * Plugin URI: https://saltingsteing.com/
 * Description: An innovative eCommerce platform integrating raffles into the shopping experience.
 * Version: 1.0.0
 * Author: SGS Team
 * Author URI: https://saltingsteing.com/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: buybyraffle
 * Domain Path: /languages
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}


// Define plugin version
define('BUYBYRAFFLE_VERSION', '3.11');

// Include the autoloader
require_once(plugin_dir_path(__FILE__) . 'autoloader.php');
// Add this function outside your class
function enqueue_admin_scripts() {
    $screen = get_current_screen();
    if ($screen->id === 'product') {
        wp_enqueue_script(
            'buybyraffle-custom-script',
            plugin_dir_url(__FILE__) . 'js/scripts.js',
            array('jquery'),
            BUYBYRAFFLE_VERSION,
            true
        );
    }
}
class BuyByRaffle {
    public function __construct() {
        // Set the default timezone
        date_default_timezone_set('Africa/Lagos'); // Set to your desired timezone
        // Add this line to enqueue your script in the admin area
        add_action('admin_enqueue_scripts', 'enqueue_admin_scripts');
        // this runs and creates all required custom Database tables upon plugin activation
        register_activation_hook(__FILE__, array('BuyByRaffleTableInstallerHandler', 'install'));
        new BuyByRaffleProductCustomTabHandler();

        // this will create the BuyByRaffle Product tags with the terms: Bait and Hero.
        new BuyByRaffleProductTagCreateHandler();
        register_activation_hook(__FILE__, array('BuyByRaffleProductTagCreateHandler', 'install'));


        // Initialize the BuyByRaffleHeroProductHandler to handle Hero products
        new BuyByRaffleHeroProductHandler();

        // Instantiate the class to start listening for events. 
        // This updates a raffle to running when a product with the term bait is created.
        
        new BuyByRaffleBaitHeroAssociationHandler();
        // add the winners to the winners table
        new BuyByRaffleWinnerHandler();

        // generate the raffle tickets foolwowing bait sales 
        // and add to the buybyraffle_tickets table
        new BuyByRaffleRaffleTicketHandler();

    }
    
    /**
     * Fetch the current date and time in MySQL compatible format.
     *
     * @return string Current date and time in MySQL date format ('Y-m-d H:i:s').
     */
    public static function current_mysql_date() {
        return date('Y-m-d H:i:s');
    }
}

// Initialize the plugin
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    new BuyByRaffle();
}else {
    // Deactivate the plugin and print an admin notice if WooCommerce is not active
    deactivate_plugins(plugin_basename(__FILE__));
    wp_die(__('This plugin requires WooCommerce to be activated.', 'buybyraffle'));
}

