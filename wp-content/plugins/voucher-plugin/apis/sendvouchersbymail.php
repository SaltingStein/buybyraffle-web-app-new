<?php 
// Include necessary namespaces or classes
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;

add_action('rest_api_init', function () {
    register_rest_route('buybyraffle/v1', '/sendvouchersbymail/', array(
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

    $algorithms = ['RS256']; // Algorithms used by Google

    try {
        $decoded = JWT::decode($jwt, JWK::parseKeySet($keys), $algorithms);

        // Perform additional claim checks
        $expected_issuer = 'https://accounts.google.com'; // Default issuer for Google tokens
        $expected_audience = 'https://buybyraffle.com/wp-json/buybyraffle/v1/sendvouchersbymail';
        $expected_email = 'buybyraffle-db@buybyraffle.iam.gserviceaccount.com';

        if ($decoded->iss !== $expected_issuer) {
            return false; // Issuer did not match
        }

        if ($decoded->aud !== $expected_audience) {
            return false; // Audience did not match
        }

        if (isset($decoded->email) && $decoded->email !== $expected_email) {
            return false; // Email claim did not match
        }

        if (isset($decoded->exp) && time() >= $decoded->exp) {
            return false; // Token is expired
        }

        return true; // JWT is valid
    } catch (Exception $e) {
        // JWT validation failed
        return false;
    }
}

function process_batch_id($request) {
    global $wpdb;

    $batch_id = $request->get_param('batch_id'); // Get the batch ID from the request

    if (!$batch_id) {
        return new WP_Error('no_batch_id', 'No batch ID provided', array('status' => 400));
    }

    $batch_table_name = $wpdb->prefix . 'epin_batches';
    $voucher_table_name = $wpdb->prefix . 'epin_vouchers';

    // Query to fetch batch and voucher data
    $query = $wpdb->prepare(
        "SELECT b.*, v.voucher_pin, v.status AS voucher_status 
         FROM $batch_table_name b 
         LEFT JOIN $voucher_table_name v ON b.batch_id = v.batch_id 
         WHERE b.batch_id = %s",
         $batch_id
    );

    $results = $wpdb->get_results($query, ARRAY_A);

    if (empty($results)) {
        return new WP_Error('no_data', 'No data found for the given batch ID', array('status' => 404));
    }

    $csv_file_path = generate_csv_and_get_path($results, $batch_id);

    send_email_with_attachment($csv_file_path, $batch_id);

    // Optionally clean up by deleting the CSV file
    unlink($csv_file_path);

    return rest_ensure_response(array('message' => 'Process completed successfully.'));
}

function generate_csv_and_get_path($data, $batch_id) {
    $csv_data = fopen('php://temp/maxmemory:'. (5*1024*1024), 'r+');

    // Define the CSV header
    fputcsv($csv_data, array_keys($data[0]));
    
    // Add the data rows
    foreach ($data as $row) {
        fputcsv($csv_data, $row);
    }

    // Rewind the stream and read contents
    rewind($csv_data);
    $csv_contents = stream_get_contents($csv_data);
    fclose($csv_data);

    // Define file path based on environment
    $environment = wp_get_environment_type();
    $file_path = WP_CONTENT_DIR . '/uploads/' . $batch_id . '.csv';

    if ($environment === 'staging' || $environment === 'production') {
        $file_path = "/home/master/applications/{$environment}/private_html/{$batch_id}.csv";
    }

    // Save CSV content to a file and return its path
    file_put_contents($file_path, $csv_contents);
    return $file_path;
}

function send_email_with_attachment($file_path, $batch_id) {
    $to = 'mzermichael4@gmail.com';
    $subject = 'ePIN Data';
    $body = 'Find attached the ePIN data with batchID: '.$batch_id;
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $attachments = array($file_path);

    wp_mail($to, $subject, $body, $headers, $attachments);
}
