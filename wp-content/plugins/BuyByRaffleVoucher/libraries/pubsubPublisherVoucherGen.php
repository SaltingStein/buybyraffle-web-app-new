<?php 
function pubsubPublisherVoucherGen($lastInsertID, $user_email) {
    // Detect the environment
    $environment = wp_get_environment_type();

    // Define the correct configuration file path based on the environment
    $configFilePath = ''; // Initialize variable
 
    // Determine the correct configuration file path based on the environment
    // switch ($environment) {
    //     case 'local':
    //         $configFilePath = 'C:\wamp64\www\wordpress\buybyraffle-dcc92f760bee.json';
    //         break;
    //     case 'development':
    //             $configFilePath = 'C:\xampp\htdocs\buybyraffle\buybyraffle-dcc92f760bee.json';
    //         break;
    //     case 'staging':
    //         //$configFilePath = '/home/master/applications/aczbbjzsvv/private_html/buybyraffle-dcc92f760bee.json';
    //         $configFilePath = '/var/env/buybyraffle-dcc92f760bee.json';
    //         break;
    //     case 'production':
    //         $configFilePath = '/var/env/buybyraffle-c24c1f61c187.json';
    //         break;    
    //     case 'sandbox':
    //         $configFilePath = '/var/env/buybyraffle-dcc92f760bee.json';
    //         break;
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
        
        return false;
    }

    $response_body = wp_remote_retrieve_body($response);
    $response_code = wp_remote_retrieve_response_code($response);
   
    // Handle response and set the result transient
    if ($response_code == 200) {
        pubsubConsumerVoucherGen($lastInsertID, $user_email);
    } 
    return new WP_REST_Response("Success", 200);
}