<?php
class DB_Connection {
    protected $db;

    public function __construct() {
        $this->connectDB();
    }

    private function connectDB() {
        // Database connection logic here
        try {            
            $dsn = 'mysql:host=localhost;dbname=buybyraffle';
            $username = 'root';
            $password = '';                
            
            $this->db = new PDO($dsn, $username, $password);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // Log any errors related to the database connection
            error_log("Connection failed: " . $e->getMessage());
        }
    }

    public function getDb() {
        return $this->db;
    }
}