<?php 
//this is found in the sendvouchersbyemail.php
// Include necessary namespaces or classes
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
// Include the pubsubConsumerVoucherGen function from the library
require_once __DIR__ . '/../libraries/pubsubConsumerVoucherGen.php';

add_action('rest_api_init', function () {
    register_rest_route('buybyraffle/v1', '/sendvouchersbymail', array(
        'methods' => 'POST',
        'callback' => 'process_batch_id',
        'permission_callback' => 'verify_pubsub_jwt'
    ));
});

function verify_pubsub_jwt($request) {
    $auth_header = $request->get_header('authorization');
    if (!$auth_header) {
        return false;
    }

    list($token_type, $jwt) = explode(' ', $auth_header, 2);
    if (strtolower($token_type) !== 'bearer' || !$jwt) {
        return false;
    }

    // URL to get Google's public keys
    $google_keys_url = 'https://www.googleapis.com/oauth2/v3/certs';

    // Fetch Google's public keys
    $keys_json = file_get_contents($google_keys_url);
    $keys = json_decode($keys_json, true);
    $key_set = JWK::parseKeySet($keys);

    try {
        // Decode JWT header to get the 'kid'
        $tks = explode('.', $jwt);
        if (count($tks) != 3) {
            throw new Exception('Wrong number of segments');
        }
        list($headb64, $bodyb64, $cryptob64) = $tks;
        $header = json_decode(JWT::urlsafeB64Decode($headb64));
        $kid = $header->kid ?? null;
        if (!$kid || !isset($key_set[$kid])) {
            throw new Exception('Unable to find a key for KID: ' . $kid);
        }

        $decoded = JWT::decode($jwt, $key_set[$kid], $algo);
        $algo = ['RS256'];
        // Perform additional claim checks
        $expected_issuer = 'https://accounts.google.com';
        $expected_audience = 'https://buybyraffle.com/wp-json/buybyraffle/v1/sendvouchersbymail';
        $expected_email = 'buybyraffle-db@buybyraffle.iam.gserviceaccount.com';

        if ($decoded->iss !== $expected_issuer ||
            $decoded->aud !== $expected_audience ||
            (isset($decoded->email) && $decoded->email !== $expected_email) ||
            (isset($decoded->exp) && time() >= $decoded->exp)) {
            return false;
        }
        //error_log("correct");
        return true; // JWT is valid
    } catch (Exception $e) {
        error_log('JWT validation failed: ' . $e->getMessage());
        return false;
    }
}


function process_batch_id($request) {
    global $wpdb;
    // Decode the JSON payload
    $data = json_decode($request->get_body(), true);
    
    // Extract batch_id from the nested structure
    $batch_id = isset($data['message']['attributes']['id']) ? $data['message']['attributes']['id'] : null;
	$user_email = isset($data['message']['attributes']['useremail']) ? $data['message']['attributes']['useremail'] : null;
    $action = $data['message']['attributes']['action'] ?? null;

    if (!$batch_id) {
        return new WP_Error('no_batch_id', 'No batch ID provided', array('status' => 400));
    }
    if ($action == 'generatevouchers') {
        // Call the voucher generation function
        return pubsubConsumerVoucherGen($batch_id, $user_email);
    } elseif ($action == 'sendmail') {
        $batch_table_name = $wpdb->prefix . 'buybyraffle_epin_batches';
        $voucher_table_name = $wpdb->prefix . 'buybyraffle_epin_vouchers';
        // Query to fetch batch and voucher data
        $query = $wpdb->prepare(
            "SELECT b.number_of_pins, b.denomination, v.voucher_pin , v.active_status 
            FROM $batch_table_name b 
            LEFT JOIN $voucher_table_name v ON b.batch_id = v.batch_id 
            WHERE b.id = %s",
            $batch_id
        );

        $results = $wpdb->get_results($query, ARRAY_A);
        // Extract number_of_pins for the email body
        $number_of_pins = $results[0]['number_of_pins'] ?? 0;

        error_log('Query Results: ' . print_r($results, true));
        if (empty($results)) {
            return new WP_Error('no_data', 'No data found for the given batch ID', array('status' => 404));
        }

        $csv_file_path = generate_csv_and_get_path($results, $batch_id);

        send_email_with_attachment($csv_file_path, $batch_id, $user_email, $number_of_pins);

        // Optionally clean up by deleting the CSV file
        unlink($csv_file_path);

        $response = rest_ensure_response(array('message' => 'Process completed successfully.'));
    } else {
        return new WP_Error('invalid_action', 'Invalid action specified', array('status' => 400));
    }
    // Check for WP_Error and return a REST response
    if (is_wp_error($response)) {
        error_log($response->get_error_message());
        return new WP_REST_Response($response, 400);
    }

    // Return a successful REST response
    return new WP_REST_Response($response, 200);
}

function generate_csv_and_get_path($data, $batch_id) {
    $csv_data = fopen('php://temp/maxmemory:'. (5*1024*1024), 'r+');

       // Define the CSV header (excluding 'number_of_pins')
       fputcsv($csv_data, array('Denomination', 'Voucher Pin', 'Active Status'));
    
       // Add the data rows (excluding 'number_of_pins')
       foreach ($data as $row) {
           fputcsv($csv_data, array($row['denomination'], $row['voucher_pin'], $row['active_status']));
       }

    // Rewind the stream and read contents
    rewind($csv_data);
    $csv_contents = stream_get_contents($csv_data);
    fclose($csv_data);

    // Define file path based on environment
    $environment = wp_get_environment_type();

    if ($environment === 'staging') {
        $file_path = "/home/master/applications/aczbbjzsvv/private_html/{$batch_id}.csv";
    }elseif ($environment === 'production') {
        $file_path = "/home/master/applications/bbqpcmbxkq/private_html/{$batch_id}.csv";
    }else{
		$file_path = WP_CONTENT_DIR . '/uploads/' . $batch_id . '.csv';
	}

    // Save CSV content to a file and return its path
    file_put_contents($file_path, $csv_contents);
    return $file_path;
}

function send_email_with_attachment($file_path, $batch_id, $user_email, $number_of_pins) {
    
		$to = $user_email;
        // Email subject and body
        $subject = 'ePIN Data';
        $body = 'Find attached '. $number_of_pins.' ePIN data with batchID: ' . $batch_id;
        $headers = array('Content-Type: text/html; charset=UTF-8');
        $attachments = array($file_path);

        // Send the email
        wp_mail($to, $subject, $body, $headers, $attachments);
    
}
