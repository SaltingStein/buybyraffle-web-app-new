<?php
/**
 * API functionality for Universal Message Hub
 *
 * This file contains the definitions and registrations of REST API endpoints for the
 * Universal Message Hub plugin. It includes endpoints for sending messages and updating
 * configuration settings.
 */
require_once __DIR__ . '/vendor/autoload.php'; // Adjust the path to your Composer autoload file

use \Firebase\JWT\JWT;
use \Firebase\JWT\JWK; // Only if you're using JWKs
use Google\Cloud\PubSub\PubSubClient;
use Google\Client;
// Include provider class files for message handling
require_once plugin_dir_path(__FILE__) . 'providers/BulkSMSProvider.php';
require_once plugin_dir_path(__FILE__) . 'providers/EmailProvider.php';


// Include other necessary files as needed
/**
 * Registers the Send Message endpoint in the WordPress REST API.
 *
 * This endpoint allows for sending messages via specified message providers.
 */
add_action('rest_api_init', function () {
    register_rest_route('umh/v1', '/send-message', array(
        'methods' => 'POST',
        'callback' => 'umh_send_message',
        'permission_callback' => 'umh_permission_check' // Authentication check for the endpoint
    ));
});

/**
 * Registers the Update Configuration endpoint in the WordPress REST API.
 *
 * This endpoint allows for dynamically updating plugin configuration settings.
 */
add_action('rest_api_init', function () {
    register_rest_route('umh/v1', '/update-config', array(
        'methods' => 'POST',
        'callback' => 'umh_update_config',
        'permission_callback' => 'umh_permission_check' // Reusing the same authentication logic
    ));
});

/**
 * Callback function for the send-message endpoint.
 *
 * Handles the logic to send messages either via SMS or email based on the request parameters.
 *
 * @param WP_REST_Request $request WordPress REST request object.
 * @return WP_REST_Response|WP_Error The response object or WP_Error on failure.
 */
function umh_send_message(WP_REST_Request $request) {
    global $wpdb;
	$data = json_decode($request->get_body(), true);
	
	$message_type = $data['message']['attributes']['type'];
	$program =  $data['message']['attributes']['raffle-draw'];
	$recipient = $data['message']['attributes']['recipient'];	
	$message = $data['message']['attributes']['message'];	
	$message_client_id = isset($data['message']['attributes']['client_id']) ? $data['message']['attributes']['client_id'] : 3;	
	$message_ref = $data['message']['attributes']['message_ref'];	
	$raffle_cycle_id = isset($data['message']['attributes']['raffle_cycle_id']) ? $data['message']['attributes']['raffle_cycle_id'] : null;
	
    // Validate the message client ID
//     if (!get_userdata($message_client_id)) {
// 		 error_log('id: '.$message_client_id);
//         return new WP_Error('invalid_client', 'Invalid message client ID', array('status' => 400));
//     }

    // Determine the provider ID based on the message type
    $provider_id = select_provider($message_type);
    
    if (!$provider_id) {
        return new WP_Error('invalid_provider', 'No suitable provider found', array('status' => 400));
    }
    
    // Initial log entry for the message attempt (status: 1 for processing)
      $log_id = umh_log_message(
        $message_client_id, 
		$program,
        $message_type, 
        $message_ref,  
        $raffle_cycle_id,  
        $pubsub_client_webhook_notified = 0, 
        $recipient, 
        $message, 
        $status = 1, 
        $provider_id
    );
 
    try {
        $provider = ($message_type == 'sms') ? new BulkSMSProvider(get_option('umh_api_token')) : new EmailProvider();
        
        $response = $provider->sendMessage($recipient, $message);
		$recipient = format_phone_number_for_sms($recipient);
        // Check if message was successfully sent and update provider score
        $messageSentSuccessfully = isset($response['success']) && $response['success'];
        update_provider_score($provider_id, $messageSentSuccessfully);

        // Determine the new status based on the response
        $new_status = $messageSentSuccessfully ? 2 : 3; // 2 for sent, 3 for failed

        // Update the log to reflect the outcome
        umh_update_log_status($log_id, $new_status);
		
		if($program == 'raffle-draw'){
			pubsubPublisher($raffle_cycle_id, 'sent');
		}
		return new WP_REST_Response( $response, 200 );

    } catch (Exception $e) {
        // Update provider score as message failed to send
        update_provider_score($provider_id, false);
        // Update the log to indicate an error (status: 3 for failed)
        umh_update_log_status($log_id, 3);
		
		if($program == 'raffle-draw'){
			pubsubPublisher($raffle_cycle_id, 'not sent');
		}
		
		//TODO:Change back to failure perharps
		return new WP_REST_Response( $response, 200 );
        //return new WP_Error('message_send_error', $e->getMessage(), array('status' => 500));
    }
}

function update_provider_score($provider_id, $success) {
    global $wpdb;

    // Adjust these values as needed
    $score_increment = $success ? 1 : -1;

    $wpdb->query($wpdb->prepare(
        "UPDATE {$wpdb->prefix}umh_providers 
         SET priority_score = priority_score + %d 
         WHERE user_id = %d",
        $score_increment,
        $provider_id
    ));
}
function select_provider($message_type) {
    global $wpdb;

    $provider_row = $wpdb->get_row($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}umh_providers 
         WHERE service_type = %s 
         ORDER BY priority_score DESC 
         LIMIT 1",
        $message_type
    ));

    if ($provider_row) {
        return $provider_row->id;
    } else {
        return null;
    }
}

function umh_log_message(
    $message_client, 
    $program,
    $message_type, 
    $message_ref, 
    $raffle_cycle_id, 
    $pubsub_client_webhook_notified, 
    $recipient, 
    $content, 
    $status, 
    $provider_id
) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'umh_messages';

    $current_time = current_time('mysql');

    $wpdb->insert(
        $table_name,
        array(
            'message_client' => $message_client,
			'program' => $program,
            'type' => $message_type,
            'message_ref' => $message_ref,
            'raffle_cycle_id' => $raffle_cycle_id, 
            'pubsub_client_webhook_notified' => $pubsub_client_webhook_notified, 
            'recipient' => $recipient,
            'content' => $content,
            'status' => $status,
            'provider_id' => $provider_id,
            'created_at' => $current_time,
            'sent_at' => $status == 2 ? $current_time : null // Assuming status 2 means 'sent'
        ),
        array('%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%d', '%s', '%s')
    );

    if ($wpdb->last_error) {
        error_log("Database error in umh_log_message: " . $wpdb->last_error);
    }

    //error_log("log ID: " . $wpdb->insert_id);
    return $wpdb->insert_id;
}



function umh_update_log_status($log_id, $new_status) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'umh_messages';

    $wpdb->update(
        $table_name,
        array('status' => $new_status),
        array('id' => $log_id),
        array('%d'), // New status format
        array('%d')  // ID format
    );
}
/**
 * Callback function for the update-config endpoint.
 *
 * Updates plugin configuration settings based on the provided request parameters.
 *
 * @param WP_REST_Request $request WordPress REST request object.
 * @return WP_REST_Response|WP_Error The response object or WP_Error on failure.
 */
function umh_update_config(WP_REST_Request $request) {
    $api_token = $request->get_param('api_token');
    if (isset($api_token)) {
        // Update the API token option after sanitizing the input
        update_option('umh_api_token', sanitize_text_field($api_token));
        return rest_ensure_response(['message' => 'Configuration updated']);
    }
    // Return an error if the API token is not provided
    return new WP_Error('invalid_data', 'Invalid configuration data', array('status' => 400));
}

/**
 * Authentication check for the REST API endpoints.
 *
 * Implement your logic here to determine if the request is authenticated.
 * Return true if the request is allowed, false otherwise.
 *
 * @param WP_REST_Request $request WordPress REST request object.
 * @return bool Whether the request is permitted.
 */
function umh_verify_jwt($auth_header) {
	
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
        $expected_issuer = expected_issuer;
        $expected_audience = expected_audience;
        $expected_email = expected_email;

        if ($decoded->iss !== $expected_issuer ||
            $decoded->aud !== $expected_audience ||
            (isset($decoded->email) && $decoded->email !== $expected_email) ||
            (isset($decoded->exp) && time() >= $decoded->exp)) {
			
            return false;
        }
        return true; // JWT is valid
    } catch (Exception $e) {
        // Handle exceptions, such as invalid token
        error_log('JWT validation error: ' . $e->getMessage());
        umh_log_error('JWT validation error: ' . $e->getMessage());
        return false;
    }
}


/**
 * Permission check for REST API endpoints.
 *
 * Determines the authentication method from the authorization header and
 * processes the request accordingly. It supports both JWT Bearer Token and
 * Basic Auth based on WordPress user capabilities.
 *
 * @param WP_REST_Request $request WordPress REST request object.
 * @return bool True if the request is authenticated, false otherwise.
 */
function umh_permission_check(WP_REST_Request $request) {
    $auth_header = $request->get_header('authorization');
    if (!$auth_header) {
        // Log and return false if no authorization header is present
        umh_log_error('Authorization header missing.');
        return false;
    }

    if (strpos(strtolower($auth_header), 'bearer ') === 0) {
        // Handle JWT Bearer Token authentication
        return umh_verify_jwt($auth_header);
    } else if (strpos(strtolower($auth_header), 'basic ') === 0) {
        // Handle Basic Auth authentication using WordPress capabilities
        return umh_verify_basic_auth($auth_header);
    } else {
        // Log and return false if the authentication method is not supported
        umh_log_error('Unsupported authentication method.');
        return false;
    }
}

/**
 * Verifies Basic Auth using WordPress capabilities.
 *
 * Checks if the current user has the 'manage_options' capability which
 * typically implies an administrator role.
 *
 * @param string $auth_header The authorization header from the request.
 * @return bool True if the user has 'manage_options' capability, false otherwise.
 */
function umh_verify_basic_auth($auth_header) {
    // Basic Auth is valid if the current user can manage options
    return current_user_can('manage_options');
}
function format_phone_number_for_sms($phone_number) {
    // Remove any non-numeric characters (e.g., +, -, spaces)
    $numeric_phone_number = preg_replace('/\D/', '', $phone_number);

    // Check and format the phone number
    if (substr($numeric_phone_number, 0, 4) === '2340') {
        // If number starts with '2340' (e.g., 23408067899876), remove the leading '0'
        return '234' . substr($numeric_phone_number, 4);
    } elseif (substr($numeric_phone_number, 0, 1) === '0') {
        // If number starts with '0' (e.g., 08067899876), replace it with '234'
        return '234' . substr($numeric_phone_number, 1);
    } elseif (substr($numeric_phone_number, 0, 3) !== '234') {
        // If number does not start with '234', add '234' at the beginning
        return '234' . $numeric_phone_number;
    }

    // If the number is already in the correct format, return as is
    return $numeric_phone_number;
}
function pubsubPublisher($raffle_cycle_id, $status) {
	global $wpdb;
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
    //     default:
    //         $configFilePath = '/var/env/buybyraffle-c24c1f61c187.json';
    //         break; // Exit if the environment is not recognized
    // }
    // Determine the environment-specific configuration file path
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
    $bearerToken = xtractBearerToken($configFilePath);

    // Prepare the message payload with the batch ID and email
    $messageData = [
        'raffle_cycle_id' => strval($raffle_cycle_id), // Convert to string
        'environment' => strval($environment),
		'status' =>strval($status)
    ];

    $payload = [
        'messages' => [
            [
                'attributes' => [
                    'raffle_cycle_id' => strval($raffle_cycle_id), // Convert to string
                    'environment' => $environment,
					'status' =>strval($status)
                ],
                'data' => base64_encode(json_encode($messageData)),
            ],
        ],
    ];

    // Define the Pub/Sub API URL for your project and topic
    $apiUrl = 'https://pubsub.googleapis.com/v1/projects/buybyraffle/topics/raffle-draw-result-notification:publish';
	

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
        if($environment == 'staging' || $environment == 'local' || $environment == 'development') {
            error_log('Error publishing to Pub/Sub topic - generatevoucherpins: ' . $response->get_error_message());
        }
        return false;
    }

    $response_body = wp_remote_retrieve_body($response);
    $response_code = wp_remote_retrieve_response_code($response);
   error_log(print_r($response_body, true));
   error_log(print_r($response_code, true));
    // Handle response and set the result transient
    if ($response_code == 200) {
		// Update records based on raffle_cycle_id
		$result = $wpdb->update(
			"{$wpdb->prefix}umh_messages",
			array('pubsub_client_webhook_notified' => 1), // Set pubsub_client_webhook_notified to 1
			array('raffle_cycle_id' => $raffle_cycle_id)  // Where raffle_cycle_id matches
		);

		if ($result === false) {
			// Update failed, log or handle the error
			error_log("Database error in updating pubsub_client_webhook_notified: " . $wpdb->last_error);
			
		} else if ($result === 0) {
			// No rows were updated, which might indicate that no matching record was found
			error_log("No records updated for raffle_cycle_id: " . $raffle_cycle_id);
			
		} else {
			// Update was successful
			error_log($result);
		}
	}

}
function xtractBearerToken($configFilePath) {
    try {
        $client = new Client();
        $client->setAuthConfig($configFilePath);
        $client->setScopes(['https://www.googleapis.com/auth/cloud-platform']);

        // Fetch the access token
        $accessToken = $client->fetchAccessTokenWithAssertion();

        // Return the access token
        if (isset($accessToken['access_token'])) {
            return $accessToken['access_token'];
        } else {
            throw new Exception('Failed to fetch access token');
        }
    } catch (Exception $e) {
        // Handle exceptions, such as file not found or invalid credentials
        error_log('Exception in getBearerToken: ' . $e->getMessage());
        throw $e; // Rethrow the exception for the caller to handle
    }
}
