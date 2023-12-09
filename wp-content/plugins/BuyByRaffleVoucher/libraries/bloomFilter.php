<?php 
function generateRaffleTicket($length = 12) {
    $characters = '0123456789'; // Excluded 0, 1, O, I, l, Z
    $ticket = '';
    try {
        for ($i = 0; $i < $length; $i++) {
            $index = random_int(0, strlen($characters) - 1);
            $ticket .= $characters[$index];
        }
    } catch (Exception $e) {
        error_log('Error generating random alphanumeric string: ' . $e->getMessage());
        return null;
    }
    return $ticket;
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

