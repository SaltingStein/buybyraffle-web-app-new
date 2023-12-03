<?php 
/**
 * Post batchID to Google Cloud Pub/Sub.
 * 
 * @param string $batch_id The batch ID to publish.
 */
function pubsubPublishEmailVoucherCsv($batch_id, $user_email) {
    // Detect the environment
    $environment = wp_get_environment_type();

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

    // Get the Bearer token for authentication
    $bearerToken = getBearerToken($configFilePath);

    // Prepare the message payload
    $payload = [
        'messages' => [
            [
                'attributes' => [
                    'action' => 'sendmail', 
                    'id' => strval($batch_id), 
                    'environment' => strval($environment), 
                    'useremail' => strval($user_email)
                ],
                'data' => base64_encode($batch_id), // Data must be base64 encoded
            ],
        ],
    ];

    // Define the API URL
    $apiUrl = 'https://pubsub.googleapis.com/v1/projects/buybyraffle/topics/sendvouchersbymail:publish';

    // Make the POST request to Google Cloud Pub/Sub
    $response = wp_remote_post($apiUrl, [
        'method' => 'POST',
        'headers' => [
            'Authorization' => 'Bearer ' . $bearerToken,
            'Content-Type' => 'application/json'
        ],
        'body' => json_encode($payload),
        'data_format' => 'body'
    ]);

    // Check for errors in the response
    if (is_wp_error($response)) {
        error_log('Error in publishing to PubSub Topic - sendvouchersbymail: ' . $response->get_error_message());
        return false;
    }
    $response_body = wp_remote_retrieve_body($response);
    error_log('Pub/Sub topic-sendvouchersbymail publish response: ' . $response_body);
}