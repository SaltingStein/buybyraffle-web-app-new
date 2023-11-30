<?php
function BuyByRaffleGetHeroIdByBaitId($bait_id) {
    global $wpdb;

    // Table name
    $table_name = $wpdb->prefix.'buybyraffle_bait_hero_association';

    // SQL query to retrieve hero_id for a given bait_id
    $sql = $wpdb->prepare("SELECT hero_id FROM $table_name WHERE bait_id = %d AND status = %s", $bait_id, 'active');

    // Get the result
    $result = $wpdb->get_var($sql);

    // Return the hero_id if a record is found, otherwise return null
    return $result !== null ? $result : null;
}
