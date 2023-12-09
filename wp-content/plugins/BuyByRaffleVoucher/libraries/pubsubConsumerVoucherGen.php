<?php 
include_once PGS_VOUCHERS . 'libraries/bloomFilter.php'; 
function pubsubConsumerVoucherGen($lastInsertID, $user_email) {
    global $wpdb;
    $wpdb->query('START TRANSACTION');
    error_log('$lastInsertID here: '.$lastInsertID);

    try {
        $batch_table_name = $wpdb->prefix . 'buybyraffle_epin_batches';
        $voucher_table_name = $wpdb->prefix . 'buybyraffle_epin_vouchers';

        // Check if the generation status is zero
        $batch = $wpdb->get_row($wpdb->prepare("SELECT * FROM $batch_table_name WHERE id = %d AND generation_status = 0", $lastInsertID), ARRAY_A);
        //error_log(print_r($batch, true));

        if (!$batch) {
            error_log("Batch with ID $lastInsertID not found or already processed");
            return new WP_REST_Response("Success", 200);
            
        }

        // Set generation status to 1 (processing)
        $wpdb->update($batch_table_name, array('generation_status' => 1), array('id' => $lastInsertID));

        // Generate vouchers
        for ($i = 0; $i < $batch['number_of_pins']; $i++) {
            $voucherPin = createUniqueRaffleTicket(12);
            $wpdb->insert(
                $voucher_table_name,
                array(
                    'voucher_pin' => substr($voucherPin, 0, 15),
                    'balance' => $batch['denomination'],
                    'batch_id' => substr($batch['batch_id'], 0, 15),
                    'active_status' => 0
                ),
                array('%s', '%d', '%s', '%d')
            );
            
            if ($wpdb->last_error) {
                throw new Exception("Failed to insert voucher");
            }
        }

        // Set generation status to 2 (completed)
        $wpdb->update($batch_table_name, array('generation_status' => 2), array('id' => $lastInsertID));
        $wpdb->query('COMMIT');
        // call the send email publish to pubsub here
        pubsubPublishEmailVoucherCsv($lastInsertID, $user_email);
        return new WP_REST_Response("Success", 200);
        //return rest_ensure_response(array('message' => 'Voucher generation completed successfully.'));
    } catch (Exception $e) {
        // Rollback the transaction and reset the generation status to 0
        $wpdb->query('ROLLBACK');
        $wpdb->update($batch_table_name, array('generation_status' => 0), array('id' => $lastInsertID));
        //error_log('Error in voucher generation: ' . $e->getMessage());
        return new WP_Error('voucher_generation_error', 'Error in voucher generation', array('status' => 500));
    }
}



