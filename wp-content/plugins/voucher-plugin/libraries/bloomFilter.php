<?php 
function generateRaffleTicket($length = 12) {
    try {
        // Generate a string of random bytes and convert to a hex string
        return bin2hex(random_bytes($length));
    } catch (Exception $e) {
        // Log and handle the exception if random_bytes fails
        error_log('Error generating random bytes: ' . $e->getMessage());
        return null;
    }
}

function isValueUnique($tableName, $columnName, $value) {
    global $wpdb;
    // Prepare and execute a query to count matching records
    $query = $wpdb->prepare("SELECT COUNT(*) FROM `$tableName` WHERE `$columnName` = %s", $value);
    return ($wpdb->get_var($query) == 0); // Return true if unique, false otherwise
}

function createUniqueRaffleTicket($length = 12) {
    //do {
        $raffleTicket = generateRaffleTicket($length);
    //} while (!isValueUnique($tableName, $columnName, $raffleTicket));
    return $raffleTicket;
}

