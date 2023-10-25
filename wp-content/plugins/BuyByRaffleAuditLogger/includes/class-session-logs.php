<?php
require_once 'class-db-connection.php';

class Session_Logs extends DB_Connection {
    private $sanitized_user_ip;
    /**
     * Logs user login actions.
     *
     * @param string $user_login Username of the logged-in user.
     * @param WP_User $user User object of the logged-in user.
     */
    
    public function log_login($user_login, $user) {
        $session_id = bin2hex(openssl_random_pseudo_bytes(16)); // 32 characters
        
        session_start();
      
        $userId = $user->ID;
        $loginTimestamp = current_time('mysql', 1);
        // Generate a unique session identifier
        $deviceInfo = $_SERVER['HTTP_USER_AGENT'];
        //$session_id = session_id();
        // Get the IP address
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $user_ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $user_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $user_ip = $_SERVER['REMOTE_ADDR'];
        }

        // Validate the IP address
        if (filter_var($user_ip, FILTER_VALIDATE_IP)) {
            // If valid, you can proceed to use $user_ip
            // Optionally sanitize the IP address (usually not necessary if it's already valid)
            $this->sanitized_user_ip = filter_var($user_ip, FILTER_SANITIZE_STRING);

        } else {
            // Handle the invalid IP address case
            // You can log an error or take some other action
            error_log("Invalid IP address: " . $this->sanitized_user_ip." detected at login by " . $userId);
        }
        // Convert the IP address to its binary representation
        $binary_ip = inet_pton($this->sanitized_user_ip);
       
        $sql = "INSERT INTO wp_session_duration_logs (user_id, login_timestamp, session_id, device_info, ip_address) VALUES (:userId, :loginTimestamp, :session_id, :deviceInfo, :ipAddress)";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':loginTimestamp', $loginTimestamp, PDO::PARAM_STR);
            $stmt->bindParam(':session_id', $session_id, PDO::PARAM_STR);
            $stmt->bindParam(':deviceInfo', $deviceInfo, PDO::PARAM_STR);
            $stmt->bindParam(':ipAddress', $binary_ip, PDO::PARAM_LOB);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error inserting log entry: " . $e->getMessage());
        }
    }

    /**
     * Logs user logout actions.
     *
     * @param string $user_login Username of the logged-out user.
     * @param WP_User $user User object of the logged-out user.
     */
    public function log_logout($user_id) {
        error_log("log_logout called with user_id: $user_id");
        try {
            $session_id = $_COOKIE['PHPSESSID'] ?? null;
            if (!$session_id) {
                throw new Exception("Session ID cookie ID - $session_id");
            }
            if (!$user_id) {
                throw new Exception("User ID - $user_id not found.");
            }
    
            $logoutTimestamp = current_time('mysql', 1);
    
            $sql = "SELECT login_timestamp FROM wp_session_duration_logs WHERE user_id = :userId ORDER BY login_timestamp DESC LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':userId', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $loginTimestamp = $stmt->fetchColumn();
    
            if (!$loginTimestamp) {
                throw new Exception("Incorrect login timestamp.");
            }
    
            $sessionDuration = strtotime($logoutTimestamp) - strtotime($loginTimestamp);
    
            $sql = "UPDATE wp_session_duration_logs SET logout_timestamp = :logoutTimestamp, session_duration = :sessionDuration WHERE user_id = :userId AND login_timestamp = :loginTimestamp";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':userId', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':logoutTimestamp', $logoutTimestamp, PDO::PARAM_STR);
            $stmt->bindParam(':sessionDuration', $sessionDuration, PDO::PARAM_INT);
            $stmt->bindParam(':loginTimestamp', $loginTimestamp, PDO::PARAM_STR);
            $stmt->execute();
    
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
        } catch (Exception $e) {
            error_log("General Error: " . $e->getMessage());
        }
    }
}