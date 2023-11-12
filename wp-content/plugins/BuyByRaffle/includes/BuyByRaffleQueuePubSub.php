<?php

namespace BuyByRaffle;

use Google\Cloud\PubSub\PubSubClient;
use Exception;

class PostToPubSub {
    private $pubSubClient;
    private $configFilePath;
    private $apiUrl = 'https://pubsub.googleapis.com/v1/projects/buybyraffle/topics/draw-engine:publish';

    public function __construct() {
        $this->setEnvironmentConfig();
        $this->pubSubClient = new PubSubClient([
            'keyFilePath' => $this->configFilePath
        ]);
    }

    private function setEnvironmentConfig() {
        // Determine environment and set configuration file path
        if (in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
            $this->configFilePath = 'C:\wamp64\www\wordpress\buybyraffle_dcc92f760bee.json';
        } elseif ($_SERVER['SERVER_ADDR'] === '138.68.91.147') {
            $this->configFilePath = '/home/master/applications/aczbbjzsvv/private_html/buybyraffle_dcc92f760bee.json';
        } else {
            $this->configFilePath = '/home/master/applications/bbqpcmbxkq/private_html/buybyraffle_dcc92f760bee.json';
        }
    }

    private function getBearerToken() {
        // Implement the logic to fetch the bearer token
        // This might involve a request to a Google API or another method based on your setup
        // Return the bearer token or throw an exception if unable to fetch it
    }

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
                error_log('Error in PostToPubSub: ' . $response->get_error_message());
                return false;
            }
    
            $body = wp_remote_retrieve_body($response);
            return json_decode($body, true);
    
        } catch (Exception $e) {
            error_log('Exception in PostToPubSub: ' . $e->getMessage());
            return false;
        }
    }
    
}
