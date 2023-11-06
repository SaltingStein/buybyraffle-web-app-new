<?php 
/**
 * Handles the database operations using PDO for the Raffle system.
 *
 * This class is responsible for establishing a connection to the database
 * and executing queries, particularly for the Raffle system. It retrieves
 * the necessary database credentials from WordPress options.
 */
class BuyByRaffleCycleHandler {
    /**
     * @var PDO|null The PDO instance for database connections.
     */
    private $pdo;

    /**
     * @var string The database host.
     */
    private $host;

    /**
     * @var string The database name.
     */
    private $dbname;

    /**
     * @var string The database user.
     */
    private $user;

    /**
     * @var string The database password.
     */
    private $pass;

    /**
     * @var int The database port.
     */
    private $port;

    /**
     * The constructor retrieves database credentials and establishes a connection.
     */
    public function __construct() {}
    /**
     * Lazily create and return a PDO connection.
     *
     * @return PDO
     */
    private function getPDO() {
        if ($this->pdo === null) {
            try {
                // Set the credentials as class properties.
                $this->host = get_option('_databaseHost');
                $this->dbname = get_option('_databaseName');
                $this->user = get_option('_databaseUser');
                $this->pass = get_option('_databasePassword');
                $this->port = get_option('_databasePort', 3306); // Use default port if not specified.

                $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset=utf8mb4";
                $this->pdo = new PDO($dsn, $this->user, $this->pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                // Handle exception, log it, etc.
                error_log($e->getMessage());
                // Depending on the application's needs, re-throw the exception or handle it.
                // For example, you could set $this->pdo to false to indicate a failed connection.
                $this->pdo = false;
            }
        }
        return $this->pdo;
    }

    
    /**
     * Inserts data into the specified table and returns the last inserted ID.
     *
     * @param string $table The name of the table where data should be inserted.
     * @param array $data An associative array of column => value pairs to be inserted.
     * @return string The ID of the last inserted row.
     * @throws Exception If the insert operation fails.
     */
   
     public function insertRaffle($table, $data) {
        try {
            $pdo = $this->getPDO();
            if ($pdo === false) {
                // Handle the case where the PDO object is not set.
                throw new Exception("Unable to establish database connection.");
            }
            
            $columns = implode(", ", array_keys($data));
            $placeholders = ":" . implode(", :", array_keys($data));
            $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
            $stmt = $pdo->prepare($sql);
    
            foreach ($data as $key => $value) {
                $stmt->bindValue(":{$key}", $value);
            }
    
            $stmt->execute();
            return $pdo->lastInsertId();
    
        } catch (PDOException $e) {
            // Handle PDO-specific errors
            error_log("PDOException in insertRaffle: " . $e->getMessage());
            // Depending on your application's needs, re-throw the exception or return false/null.
            throw $e;
        } catch (Exception $e) {
            // Handle all other exceptions
            error_log("Exception in insertRaffle: " . $e->getMessage());
            // Depending on your application's needs, re-throw the exception or return false/null.
            throw $e;
        }
    }
    
}

