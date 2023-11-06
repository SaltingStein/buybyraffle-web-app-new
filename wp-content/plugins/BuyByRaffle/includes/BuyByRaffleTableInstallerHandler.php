<?php 
/**
 * Class BuyByRaffleTableInstaller
 *
 * This class is responsible for installing the necessary tables
 * for the BuyByRaffle application.
 *
 * @author Terungwa
 */
class BuyByRaffleTableInstallerHandler {
    
    /**
     * Install or upgrade the necessary tables for the BuyByRaffle application.
     * Also sets a transient to show an admin notice on production environments
     * if the plugin version stored in the database is outdated.
     *
     * This method is called upon plugin activation.
     *
     * @global wpdb $wpdb WordPress database access object.
     * @throws Exception If there are any issues with table creation or upgrades.
     */
    public static function install() {
        try {
            global $wpdb;
            $installed_ver = get_option("_buybyraffle_version");
            $charset_collate = $wpdb->get_charset_collate();


            $charset_collate = $wpdb->get_charset_collate();

            // Create tables
            // The 'self::' syntax is used to call static methods from within the same class.
            //self::createRaffleTable($wpdb, $charset_collate);
            //self::createTicketTable($wpdb, $charset_collate);
            self::createLogTable($wpdb, $charset_collate);
            self::createQueuedRaffleTable($wpdb, $charset_collate);
            //self::createRaffleWinnersTable($wpdb, $charset_collate);
            self::createHeroProductsTable($wpdb, $charset_collate);
            self::createBaitHeroAssociationTable($wpdb, $charset_collate);

            // Update the database version in the options table
            update_option("_buybyraffle_version", BUYBYRAFFLE_VERSION);

            
        } catch (Exception $e) {
            // Log the exception
            error_log("Caught exception: " . $e->getMessage());
        }
    }

    /**
     * Create Log Table
     *
     * This method creates the log table if it doesn't exist yet.
     * 
     * @param wpdb $wpdb WordPress database access object.
     * @param string $charset_collate The character set and collation for the table.
     */
    private static function createLogTable($wpdb, $charset_collate) {
        // Your code for creating the Log table here
        $log_table_name = $wpdb->prefix . 'buybyraffle_logs';
        if($wpdb->get_var("SHOW TABLES LIKE '$log_table_name'") != $log_table_name) {
            // SQL for creating table
            $log_sql = "CREATE TABLE $log_table_name (
                log_id int UNSIGNED NOT NULL AUTO_INCREMENT,
                ledger_id mediumint(9) NOT NULL,
                order_id mediumint(9) NOT NULL,
                raffle_cycle_id mediumint(9) NOT NULL,
                user_id mediumint(9) NOT NULL,
                draw_type enum('bait', 'hero', 'solo') NOT NULL,
                status text NOT NULL,
                created_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (log_id),
                INDEX idx_logs_userid_raffle_cycle_id (user_id, raffle_cycle_id)
            ) $charset_collate;";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($log_sql);
        }
    }

    /**
     * Create Queued Raffle Table
     *
     * This method creates the queued raffle table if it doesn't exist yet.
     * 
     * @param wpdb $wpdb WordPress database access object.
     * @param string $charset_collate The character set and collation for the table.
     */
    private static function createQueuedRaffleTable($wpdb, $charset_collate) {
        // Your code for creating the Queued Raffle table here
        $queued_raffle_table_name = $wpdb->prefix . 'buybyraffle_queued_raffles';
        if($wpdb->get_var("SHOW TABLES LIKE '$queued_raffle_table_name'") != $queued_raffle_table_name) {
            // SQL for creating table
            $queued_raffle_sql = "CREATE TABLE $queued_raffle_table_name (
                `task_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
                `raffle_cycle_id` mediumint NOT NULL,
                `status` enum('pending','processing','completed','cancelled') COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'pending',
                `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`task_id`)
            ) $charset_collate;";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($queued_raffle_sql);
        }
    }

    /**
     * Create Bait-Hero Association Table
     *
     * This method is responsible for creating a table that handles the association 
     * between bait products and hero products in the BuyByRaffle system. Each bait 
     * product will be associated with a hero product.
     *
     * @param wpdb $wpdb WordPress database access object.
     * @param string $charset_collate The character set and collation for the table.
     */
    private static function createBaitHeroAssociationTable($wpdb, $charset_collate) {
        // Define table name
        $table_name = $wpdb->prefix . 'buybyraffle_bait_hero_association';
        
        // Check if table already exists
        if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            // SQL statement for creating the table
            $sql = "CREATE TABLE $table_name (
                id int UNSIGNED NOT NULL AUTO_INCREMENT,
                bait_id mediumint(9) NOT NULL,
                hero_id mediumint(9) NOT NULL,
                status enum('active', 'deleted', 'unpublished') NOT NULL DEFAULT 'active',
                created_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) $charset_collate;";
            
            // Include WordPress table creation API and create the table
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }
   /**
     * Create Hero Products Table
     *
     * This method creates a table specifically for Hero products. It stores the product ID,
     * the ID of each Hero, and its current status ('open' or 'redeemed').
     *
     * @param wpdb $wpdb WordPress database access object.
     * @param string $charset_collate The character set and collation for the table.
     */
    private static function createHeroProductsTable($wpdb, $charset_collate) {
        $table_name = $wpdb->prefix . 'buybyraffle_hero_products';
        if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $sql = "CREATE TABLE $table_name (
                id int UNSIGNED NOT NULL AUTO_INCREMENT,
                product_id mediumint(9) NOT NULL,
                hero_id mediumint(9) NOT NULL,
                raffle_cycle_id mediumint(9) NOT NULL,
                status enum('open', 'invalid', 'running', 'redeemed') NOT NULL DEFAULT 'open',
                created_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE (product_id),
                UNIQUE (raffle_cycle_id)
            ) $charset_collate;";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }






}
