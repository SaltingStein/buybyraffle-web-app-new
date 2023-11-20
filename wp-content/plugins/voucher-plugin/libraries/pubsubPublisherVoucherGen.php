<?php 
function pubsubPublisherVoucherGen($lastInsertID, $user_email) {
    // Detect the environment
    $environment = wp_get_environment_type();

    // Define the correct configuration file path based on the environment
    $configFilePath = ''; // Initialize variable
 
    // Determine the correct configuration file path based on the environment
    switch ($environment) {
        case 'local':
            $configFilePath = 'C:\wamp64\www\wordpress\buybyraffle-dcc92f760bee.json';
            break;
        case 'development':
                $configFilePath = 'C:\xampp\htdocs\buybyraffle\buybyraffle-dcc92f760bee.json';
            break;
        case 'staging':
            $configFilePath = '/home/master/applications/aczbbjzsvv/private_html/buybyraffle-dcc92f760bee.json';
            break;
        case 'production':
            $configFilePath = '/home/master/applications/bbqpcmbxkq/private_html/buybyraffle-dcc92f760bee.json';
            break;
        default:
            error_log("Unexpected environment type: $environment");
            return; // Exit if the environment is not recognized
    }

    // Assuming the getBearerToken function exists and fetches a valid bearer token
    $bearerToken = getBearerToken($configFilePath);

    // Prepare the message payload with the batch ID and email
    $messageData = [
        'id' => strval($lastInsertID), // Convert to string
        'user_email' => strval($user_email),
        'environment' => strval($environment)
    ];

    $payload = [
        'messages' => [
            [
                'attributes' => [
                    'action' => 'generatevouchers', 
                    'id' => strval($lastInsertID), // Convert to string
                    'environment' => $environment,
                    'user_email' => $user_email
                ],
                'data' => base64_encode(json_encode($messageData)),
            ],
        ],
    ];

    // Define the Pub/Sub API URL for your project and topic
    //$apiUrl = 'https://pubsub.googleapis.com/v1/projects/buybyraffle/topics/generatevoucherpins:publish';
    $apiUrl = 'https://pubsub.googleapis.com/v1/projects/buybyraffle/topics/sendvouchersbymail:publish';

    // Make the POST request to Google Cloud Pub/Sub
    $response = wp_remote_post($apiUrl, [
        'headers' => [
            'Authorization' => 'Bearer ' . $bearerToken,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ],
        'body' => json_encode($payload),
        'method' => 'POST',
        'data_format' => 'body',
        'timeout' => 65 // Set an appropriate timeout for the request
    ]);

    // Check for a successful response
    if (is_wp_error($response)) {
        error_log('Error publishing to Pub/Sub topic - generatevoucherpins: ' . $response->get_error_message());
        update_option('_epin_pubsub_result', 'error');
        return false;
    }

    $response_body = wp_remote_retrieve_body($response);
    $response_code = wp_remote_retrieve_response_code($response);
   
    // Handle response and set the result transient
    if ($response_code == 200) {
        update_option('_epin_pubsub_result', 'success');
        pubsubConsumerVoucherGen($lastInsertID, $user_email);
        error_log('Successfully published to Pub/Sub topic-generatevoucherpins. Response: ' . $response_body);
    } else {
        update_option('_epin_pubsub_result', 'error');
        error_log('Failed to publish to Pub/Sub topic-generatevoucherpins. HTTP Status Code: ' . $response_code . '. Response: ' . $response_body);
    }
    return new WP_REST_Response("Success", 200);
}