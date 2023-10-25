<?php
class Schema_Migrations {
    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function create_audit_table() {
        try {
            // SQL statement to create a new table
            $sql = "CREATE TABLE IF NOT EXISTS wp_buybyraffle_audit_logs (
                        id INT NOT NULL AUTO_INCREMENT,
                        session_duration_id INT NOT NULL,
                        user_id INT NOT NULL,
                        user_login VARCHAR(20) NOT NULL,
                        event VARCHAR(100) NOT NULL,
                        timestamp DATETIME NOT NULL,
                        post_id INT,
                        old_status VARCHAR(10),
                        new_status VARCHAR(10),
                        additional_info TEXT,
                        PRIMARY KEY  (id),
                        INDEX(user_id),
                        INDEX(user_login),
                        INDEX(post_id)
                    )";

            // Use exec() because no results are returned
            $this->db->exec($sql);
            
            error_log("Audit table created successfully.");

        }
        catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function create_session_duration_table() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS wp_session_duration_logs (
                        id INT NOT NULL AUTO_INCREMENT,
                        user_id INT NOT NULL,
                        session_id VARCHAR(32) NOT NULL,
                        login_timestamp DATETIME NOT NULL,
                        logout_timestamp DATETIME NOT NULL,
                        session_duration INT NOT NULL,
                        device_info  VARCHAR(100) NOT NULL,
                        ip_address  BINARY(16) NOT NULL,
                        PRIMARY KEY (id),
                        INDEX(user_id),
                        INDEX(ip_address),
                        INDEX(session_id)
                    )";
    
            $this->db->exec($sql);
        }
        catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
