<?php 
/**
 * Class BuyByRaffleTableInstaller
 *
 * This class is responsible for installing the necessary tables
 * for the BuyByRaffle application.
 *
 * @author Terungwa
 */
namespace Sgs\Buybyraffle;
use Exception;
use wpdb;
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
            self::createErrorLogTable($wpdb, $charset_collate);
            self::createCashTokenGiftingLog($wpdb, $charset_collate);
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
        $queued_raffle_table_name = $wpdb->prefix . 'buybyraffle_queued_raffles';
        if ($wpdb->get_var("SHOW TABLES LIKE '$queued_raffle_table_name'") != $queued_raffle_table_name) {
            $queued_raffle_sql = "CREATE TABLE $queued_raffle_table_name (
                `task_id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary key, unique identifier for each queued raffle task',
                `raffle_cycle_id` mediumint NOT NULL COMMENT 'ID of the raffle cycle associated with the task',
                `status` enum('pending','processing','completed','cancelled') COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'pending' COMMENT 'Current status of the queued task (pending, processing, completed, cancelled)',
                `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp of when the queued task was created',
                `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Timestamp of the last update to the queued task',
                PRIMARY KEY (`task_id`),
                UNIQUE KEY `raffle_cycle_id` (`raffle_cycle_id`)
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
                `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
                `bait_id` mediumint NOT NULL,
                `raffle_cycle_id_bait` int DEFAULT NULL,
                `hero_id` mediumint NOT NULL,
                `status` enum('active','deleted','unpublished') COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'active',
                `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
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
        $table_name = $wpdb->prefix . 'buybyraffle_product_config';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $sql = "CREATE TABLE $table_name (
               `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary key, unique identifier for each record',
                `product_id` int NOT NULL COMMENT 'ID of the product',
                `raffle_class_id` smallint NOT NULL COMMENT 'Raffle class ID (1: bait, 2: hero, 3: solo)',
                `raffle_type_id` smallint NOT NULL COMMENT 'Raffle type ID (1: BuyByRaffle, 2: PayByRaffle, 3: TransferByRaffle)',
                `raffle_cycle_id` mediumint NOT NULL COMMENT 'ID of the raffle cycle',
                `accumulated_sales_value` decimal(9,2) DEFAULT NULL COMMENT 'Accumulated sales value for the product',
                `status` enum('0','1','2', '3', '4') COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '0' COMMENT 'Status of the raffle (0=open, 1=invalid, 2=running, 3=closed, 4=redeemed)',
                `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp of when the record was created',
                `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Timestamp of the last update to the record',
                PRIMARY KEY (`id`),
                UNIQUE KEY `raffle_cycle_id` (`raffle_cycle_id`),
                KEY `product_id` (`product_id`),
                KEY `composite_index` (`product_id`, `raffle_type_id`, `raffle_cycle_id`)
            ) $charset_collate;";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }
    
    private static function createErrorLogTable($wpdb, $charset_collate) {
        $table_name = $wpdb->prefix . 'buybyraffle_error_logs';
    
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $sql = "CREATE TABLE $table_name (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id BIGINT UNSIGNED NULL,
                user_action TEXT NULL,
                message TEXT NOT NULL,
                logged_at DATETIME NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;";
    
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

    private static function createCashTokenGiftingLog($wpdb, $charset_collate) {
        $table_name = $wpdb->prefix . 'buybyraffle_cashtoken_gifting_logs';
    
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $sql = "CREATE TABLE $table_name (
                `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique identifier for each log entry',
                `user_id` bigint NOT NULL COMMENT 'ID of the user involved in the cashtoken gifting',
                `order_id` bigint NOT NULL COMMENT 'ID of the order associated with the cashtoken gifting',
                `status` enum('0','1','2','3') NOT NULL DEFAULT '0' COMMENT 'Status of the gifting process (0: pending, 1: processing, 2: completed, 3: failed)',
                `pubsub_message_id` varchar(11) NOT NULL COMMENT 'Message ID related to the pubsub system used',
                `logged_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp of when the log entry was created',
                `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Timestamp of the last update to the log entry',
                PRIMARY KEY (`id`),
                KEY `user_id` (`user_id`),
                KEY `order_id` (`order_id`)
            ) $charset_collate COMMENT='Table for logging cashtoken gifting events';";
            
    
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }
    
    
    
}
    







