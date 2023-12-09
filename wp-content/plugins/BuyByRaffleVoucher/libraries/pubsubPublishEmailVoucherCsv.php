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
    // switch ($environment) {
    //     case 'local':
    //         $configFilePath = 'C:\wamp64\www\wordpress\buybyraffle-dcc92f760bee.json';
    //         break;
    //     case 'development':
    //         $configFilePath = 'C:\xampp\htdocs\buybyraffle\buybyraffle-dcc92f760bee.json';
    //         break;
    //     case 'staging':
    //         $configFilePath = '/var/env/buybyraffle-dcc92f760bee.json';
    //         break;
    //     case 'production':
    //             $configFilePath = '/var/env/buybyraffle-c24c1f61c187.json';
    //             break;
    //     default:
    //         error_log("Unexpected environment type: $environment");
    //         return; // Exit if the environment is not recognized
    // }
    switch ($environment) {
        case 'local':
            $configFilePath = LOCAL_CONFIG_FILE_PATH;
            break;
        case 'development':
            $configFilePath = DEVELOPMENT_CONFIG_FILE_PATH;
            break;
        case 'staging':
            $configFilePath = STAGING_CONFIG_FILE_PATH;
            break;
        case 'production':
            $configFilePath = PRODUCTION_CONFIG_FILE_PATH;
            break;
        default:
            $configFilePath = PRODUCTION_CONFIG_FILE_PATH; // Default to production
            break; // Exit if the environment is not recognized
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
       
        return false;
    }
    $response_body = wp_remote_retrieve_body($response);
}