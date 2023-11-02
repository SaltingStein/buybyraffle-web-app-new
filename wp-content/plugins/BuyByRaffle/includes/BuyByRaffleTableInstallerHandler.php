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
     * Install Method
     *
     * This method installs or upgrades tables as necessary.
     * It also updates the plugin version in the options table.
     *
     * @throws Exception If there are any issues, an Exception will be thrown and caught.
     */
    public static function install() {
        try {
            global $wpdb;
            $installed_ver = get_option("_buybyraffle_version");
            $charset_collate = $wpdb->get_charset_collate();

            if ($installed_ver != BUYBYRAFFLE_VERSION) {
                $charset_collate = $wpdb->get_charset_collate();

                // Create tables
                // The 'self::' syntax is used to call static methods from within the same class.
                self::createRaffleTable($wpdb, $charset_collate);
                self::createTicketTable($wpdb, $charset_collate);
                self::createLogTable($wpdb, $charset_collate);
                self::createQueuedRaffleTable($wpdb, $charset_collate);
                self::createRaffleWinnersTable($wpdb, $charset_collate);
                self::createHeroProductsTable($wpdb, $charset_collate);
                self::createBaitHeroAssociationTable($wpdb, $charset_collate);

                // Update the database version in the options table
                update_option("_buybyraffle_version", BUYBYRAFFLE_VERSION);
            } else {
                // Update version number if needed
                update_option('_buybyraffle_version', BUYBYRAFFLE_VERSION);
            }
        } catch (Exception $e) {
            // Log the exception
            error_log("Caught exception: " . $e->getMessage());
        }
    }
    
    /**
     * Create Raffle Table
     *
     * This method creates the raffle table if it doesn't exist yet.
     * This table is used for storing information about raffles.
     * 
     * @param wpdb $wpdb WordPress database access object.
     * @param string $charset_collate The character set and collation for the table.
     */
    private static function createRaffleTable($wpdb, $charset_collate) {
        $raffle_table_name = $wpdb->prefix . 'buybyraffle_raffles';
        
        if($wpdb->get_var("SHOW TABLES LIKE '$raffle_table_name'") != $raffle_table_name) {
            // SQL for creating table
            $raffle_sql = "CREATE TABLE $raffle_table_name (
                raffle_id mediumint(9) NOT NULL AUTO_INCREMENT,
                status enum('pending', 'ongoing', 'completed', 'stopped') NOT NULL DEFAULT 'pending',
                raffle_class enum('BuyByRaffle', 'PayByRaffle', 'CashTransferByRaffle') NOT NULL DEFAULT 'BuyByRaffle',
                draw_type enum('primary', 'secondary') NOT NULL DEFAULT 'primary',
                notes varchar(100) NOT NULL DEFAULT 'New entry',
                created_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (raffle_id)
            ) $charset_collate;";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($raffle_sql);

        }
    }    
    /**
     * Create Ticket Table
     *
     * This method creates the ticket table if it doesn't exist yet.
     * 
     * @param wpdb $wpdb WordPress database access object.
     * @param string $charset_collate The character set and collation for the table.
     */
    private static function createTicketTable($wpdb, $charset_collate) {
        // Your code for creating the Ticket table here
        $ticket_table_name = $wpdb->prefix . 'buybyraffle_tickets';
        if($wpdb->get_var("SHOW TABLES LIKE '$ticket_table_name'") != $ticket_table_name) {
            // SQL for creating table
            $ticket_sql = "CREATE TABLE $ticket_table_name (
                `tid` int UNSIGNED NOT NULL AUTO_INCREMENT,
                `ticket_id` int UNSIGNED NOT NULL COMMENT 'This is the customers order ID',
                `raffle_cycle_id` int UNSIGNED NOT NULL COMMENT 'This is the raffle identifier ID',
                `product_id` mediumint NOT NULL COMMENT 'This is the product ID',
                `user_id` mediumint NOT NULL,
                `draw_type` enum('primary','secondary') COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'primary',
                `status` tinyint NOT NULL DEFAULT 1 COMMENT '0: order reverted, 1: completed, 2: drawn in raffle',
                `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`tid`),
                UNIQUE KEY `unique_ticketid_userid` (`ticket_id`, `user_id`)  COMMENT 'Unique constraint',
                KEY `idx_tickets_userid_raffleid` (`user_id`,`product_id`),
                KEY `ticket_id` (`ticket_id`)
            ) $charset_collate;";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($ticket_sql);
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
                log_id mediumint(9) NOT NULL AUTO_INCREMENT,
                ledger_id mediumint(9) NOT NULL,
                order_id mediumint(9) NOT NULL,
                raffle_cycle_id mediumint(9) NOT NULL,
                user_id mediumint(9) NOT NULL,
                draw_type enum('primary', 'secondary') NOT NULL DEFAULT 'primary',
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
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                raffle_cycle_id mediumint(9) NOT NULL,
                status enum('waiting', 'processing', 'completed') NOT NULL DEFAULT 'waiting',
                created_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) $charset_collate;";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($queued_raffle_sql);
        }
    }

    /**
     * Create Raffle Winners Table
     *
     * This method creates the raffle winners table if it doesn't exist yet.
     * It includes fields for the win ID, raffle ID, user ID, product ID, and type of product.
     * It also sets up foreign key relationships to other related tables.
     *
     * @param wpdb $wpdb WordPress database access object.
     * @param string $charset_collate The character set and collation for the table.
     */
    private static function createRaffleWinnersTable($wpdb, $charset_collate) {
        // Define table name
        $raffle_winners = $wpdb->prefix . 'buybyraffle_raffle_winners';

        // Check if table already exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$raffle_winners'") != $raffle_winners) {
            // SQL statement for creating table
            $winners_raffle_sql = "CREATE TABLE $raffle_winners (
                win_id int UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id INT,
                product_id int UNSIGNED NOT NULL COMMENT 'This is the product ID',
                raffle_cycle_id mediumint(9) NOT NULL,
                product_type ENUM('Hero', 'Bait'),
                notice_sent ENUM('0', '1'),
                draw_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (win_id),
                INDEX idx_raffle_cycle_id (raffle_cycle_id),
                INDEX idx_user_id (user_id)
            ) $charset_collate;";
            
            // Include WordPress table creation script and run SQL
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($winners_raffle_sql);
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
                id mediumint(9) NOT NULL AUTO_INCREMENT,
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
                id mediumint(9) NOT NULL AUTO_INCREMENT,
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
