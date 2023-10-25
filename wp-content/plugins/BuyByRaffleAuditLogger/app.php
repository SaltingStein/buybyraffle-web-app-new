<?php
/**
 * Plugin Name: User Activity Logger
 * Description: Logs various user actions.
 * Version: 1.0
 */

require_once plugin_dir_path(__FILE__) . 'includes/class-db-connection.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-schema-migrations.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-audit-logs.php';  // Make sure to include this

class activityLogger {
    private $db_connection;
    private $schema_migrations;

    public function __construct() {
        $this->db_connection = new DB_Connection();
        $this->schema_migrations = new Schema_Migrations($this->db_connection->getDb());
    }

    public function activate() {
        $this->schema_migrations->create_audit_table();
        $this->schema_migrations->create_session_duration_table();
    }
}

$activityLogger = new activityLogger();

register_activation_hook(__FILE__, [$activityLogger, 'activate']);

// Initialize the Audit_Logs class
$audit_logs = Audit_Logs::get_instance();
$audit_logs->init();
