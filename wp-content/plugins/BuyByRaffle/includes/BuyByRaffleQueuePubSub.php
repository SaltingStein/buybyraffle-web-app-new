<?php
namespace Sgs\Buybyraffle;

use Google\Cloud\PubSub\PubSubClient;
use Google\Client;
use Exception;
use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Handles publishing messages to Google Cloud Pub/Sub and exposes a REST API endpoint for the same.
 */
class BuyByRaffleQueuePubSub extends WP_REST_Controller {
    /**
     * Google Cloud Pub/Sub client.
     *
     * @var PubSubClient
     */
    private $pubSubClient;

    /**
     * Path to the configuration file for Google Cloud credentials.
     *
     * @var string
     */
    private $configFilePath;

    /**
     * Google Cloud Pub/Sub API URL.
     *
     * @var string
     */
    private $apiUrl = 'https://pubsub.googleapis.com/v1/projects/buybyraffle/topics/draw-engine:publish';
    private $tableName;
    /**
     * Constructor for the publishToTopic class.
     * Initializes the Pub/Sub client and sets up the REST API endpoint.
     */
    public function __construct() {
        global $wpdb;
        $this->setEnvironmentConfig();
        $this->pubSubClient = new PubSubClient([
            'keyFilePath' => $this->configFilePath
        ]);

        // Set namespace and rest base for the REST API endpoint.
        $this->namespace = 'buybyraffle/v1';
        $this->rest_base = 'publish';
        add_action('rest_api_init', array($this, 'register_routes'), 99);

        $this->tableName = $wpdb->prefix . 'buybyraffle_queued_raffles';
       
    }

    /**
     * Registers the routes for the REST API endpoint.
     */
    public function register_routes() {
        register_rest_route($this->namespace, '/queue', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'handle_queue_request'),
            'permission_callback' => array($this, 'publish_permissions_check')
        ));
    }

    /**
     * Checks if the current user has permission to publish to the topic.
     *
     * @param WP_REST_Request $request The request object.
     * @return bool True if user has permissions, false otherwise.
     */
    public function publish_permissions_check(WP_REST_Request $request) {
       return current_user_can('manage_options');
       //return true;
    }

   /**
     * Sets the configuration file path based on the server environment.
     */
    private function setEnvironmentConfig() {
        // Add comments explaining the logic for different environments
        if (in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
            $this->configFilePath = 'C:\wamp64\www\wordpress\buybyraffle_dcc92f760bee.json';
        } elseif ($_SERVER['SERVER_ADDR'] === '138.68.91.147') {
            $this->configFilePath = '/home/master/applications/aczbbjzsvv/private_html/buybyraffle_dcc92f760bee.json';
        } else {
            $this->configFilePath = '/home/master/applications/bbqpcmbxkq/private_html/buybyraffle_dcc92f760bee.json';
        }
    }

    /**
     * Retrieves the bearer token for authentication with Google Cloud APIs.
     *
     * @return string The bearer token.
     * @throws Exception If unable to fetch the bearer token.
     */
    private function getBearerToken() {
        try {
            // Initialize the Google Client
            $client = new Client();
            $client->setAuthConfig($this->configFilePath);
            $client->setScopes(['https://www.googleapis.com/auth/pubsub']);

            // Fetch the access token
            $accessToken = $client->fetchAccessTokenWithAssertion();

            // Return the access token
            return $accessToken['access_token'];
        } catch (Exception $e) {
            // Handle exceptions, such as file not found or invalid credentials
            error_log('Exception in getBearerToken: ' . $e->getMessage());
            throw $e; // Rethrow the exception for the caller to handle
        }
    }

    /**
     * Handles the actual publishing of messages to Google Cloud Pub/Sub.
     *
     * @param string $topicName The name of the Pub/Sub topic.
     * @param string $queueId The ID of the queue (message identifier).
     * @param array $data The data to be sent.
     * @return array|bool The response from Google Cloud Pub/Sub or false on failure.
     */
    public function publishToTopic($topicName, $queueId, $data) {
        try {
            $bearerToken = $this->getBearerToken();// Determine the environment
            $env = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) ? 'local' : 'remote';
    
            $payload = [
                'messages' => [
                    [
                        'attributes' => [
                            'id' => strval($queueId), // Convert the queue ID to a string.
                            'env' => $env
                        ],
                        'data' => base64_encode(json_encode($data))
                    ]
                ]
            ];
    
            $response = wp_remote_post($this->apiUrl, [
                'method' => 'POST',
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $bearerToken
                ],
                'body' => json_encode($payload),
                'data_format' => 'body'
            ]);
    
            if (is_wp_error($response)) {
                error_log('Error in publishToTopic: ' . $response->get_error_message());
                return false;
            }
    
            $body = wp_remote_retrieve_body($response);
            return json_decode($body, true);
    
        } catch (Exception $e) {
            error_log('Exception in publishToTopic: ' . $e->getMessage());
            return false;
        }
    }

     /**
     * Handles the queue request, logs to database, and publishes to PubSub.
     * 
     * @param WP_REST_Request $request The request object.
     * @return WP_REST_Response|WP_Error
     */
    public function handle_queue_request(WP_REST_Request $request) {
        
        //error_log('handle_queue_request called');
        global $wpdb; // Explain why global $wpdb is used here (access to WP database)
        $params = $request->get_json_params();
        $raffleCycleId = $params['raffle_cycle_id'] ?? 0;

        // Insert into database
        $inserted = $wpdb->insert(
            $this->tableName,
            array(
                'raffle_cycle_id' => $raffleCycleId,
                'status' => 'pending',
                // 'created_date' is automatically set by MySQL
            ),
            array('%d', '%s')
        );

        if ($inserted === false) {
            return new WP_Error('db_insert_error', 'Failed to insert into the database', array('status' => 500));
        }

        $taskId = $wpdb->insert_id;
        $publishResult = $this->publishToTopic('draw-engine', $taskId, ['raffle_cycle_id' => $raffleCycleId]);

        if ($publishResult === false) {
            return new WP_Error('publish_failed', 'Failed to publish to topic', array('status' => 500));
        }

        return new WP_REST_Response(array('message' => 'Queued and published successfully', 'task_id' => $taskId), 200);
    }
}
